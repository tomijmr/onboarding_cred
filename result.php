<?php

$servername = "localhost";
$username = "root";
$password = ""; 
$dbname = "gestconsultasbcra";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// Validar params

if (!isset($_GET['cuit']) || !isset($_GET['telefono'])) {
    die("Debe ingresar CUIT/CUIL/CDI y teléfono.");
}

$cuit = preg_replace('/[^0-9]/', '', $_GET['cuit']);
$telefono = htmlspecialchars($_GET['telefono']);

if (strlen($cuit) != 11) {
    die("El CUIT/CUIL debe tener 11 dígitos.");
}

//call api bcra
$url = "https://api.bcra.gob.ar/CentralDeDeudores/v1.0/Deudas/" . $cuit;
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
$response = curl_exec($ch);
if (curl_errno($ch)) {
    die("Error en la conexión cURL: " . curl_error($ch));
}
curl_close($ch);

$data = json_decode($response, true);
if (!$data) {
    die("Error al decodificar la respuesta de la API. Respuesta cruda:<br><pre>$response</pre>");
}

//sys cfg
$config = $conn->query("SELECT * FROM configuracion WHERE id=1")->fetch_assoc();
$tasaMensual = $config['tasa_interes'] / 100;
$baseMax = $config['monto_maximo'];
$cuotasDisponibles = explode(',', $config['cuotas_permitidas']);

// funcion de scoring
function evaluar_credito($data, $baseMax) {
    if (!isset($data['results']['periodos'][0]['entidades'])) {
        return ["estado" => "No disponible", "monto" => 0];
    }

    $entidades = $data['results']['periodos'][0]['entidades'];
    $peorSituacion = 1;
    $deudaTotal = 0;
    $diasAtrasoMax = 0;
    $flagCritico = false;

    foreach ($entidades as $e) {
        $situacion = $e['situacion'];
        $monto = $e['monto'] * 1000;
        $dias = $e['diasAtrasoPago'];
        $deudaTotal += $monto;
        if ($situacion > $peorSituacion) $peorSituacion = $situacion;
        if ($dias > $diasAtrasoMax) $diasAtrasoMax = $dias;
        if ($e['situacionJuridica'] || $e['procesoJud']) {
            $flagCritico = true;
        }
    }

    $factores = [1 => 1.0, 2 => 0.6, 3 => 0.3, 4 => 0.0, 5 => 0.0];
    if ($peorSituacion >= 4 || $flagCritico) {
        return ["estado" => "Rechazado", "monto" => 0];
    }
    if ($diasAtrasoMax > 30) {
        return ["estado" => "Rechazado", "monto" => 0];
    }

    $montoMax = max(0, ($baseMax - $deudaTotal) * $factores[$peorSituacion]);

    return [
        "estado" => ($montoMax > 0) ? "Aprobado" : "Rechazado",
        "monto" => round($montoMax, 2)
    ];
}

// Eval
$evaluacion = ($data['status'] == 200) ? evaluar_credito($data, $baseMax) : null;

// save en db
$denominacion = ($data && isset($data['status']) && $data['status'] == 200)
    ? $conn->real_escape_string($data['results']['denominacion'])
    : null;

$estadoCredito = $evaluacion ? $evaluacion['estado'] : null;
$montoCredito = $evaluacion ? $evaluacion['monto'] : null;

$sql = "INSERT INTO consultas (cuit, telefono, denominacion, estado_credito, monto_credito) 
        VALUES ('$cuit', '$telefono', " 
        . ($denominacion ? "'$denominacion'" : "NULL") . ", "
        . ($estadoCredito ? "'$estadoCredito'" : "NULL") . ", "
        . ($montoCredito !== null ? $montoCredito : "NULL") . ")";
$conn->query($sql);

// simulador de cuotas
function calcular_plan($monto, $meses, $tasaMensual) {
    $cuota = ($tasaMensual == 0)
        ? $monto / $meses
        : $monto * ($tasaMensual * pow(1 + $tasaMensual, $meses)) / (pow(1 + $tasaMensual, $meses) - 1);

    $saldo = $monto;
    $detalle = [];

    for ($i = 1; $i <= $meses; $i++) {
        $interes = $saldo * $tasaMensual;
        $capital = $cuota - $interes;
        $saldo -= $capital;
        $detalle[] = [
            "n" => $i,
            "cuota" => round($cuota, 2),
            "interes" => round($interes, 2),
            "capital" => round($capital, 2),
            "saldo" => max(0, round($saldo, 2))
        ];
    }

    return ["meses" => $meses, "cuota" => round($cuota, 2), "detalle" => $detalle];
}

$planes = [];
if ($evaluacion && $evaluacion['estado'] == "Aprobado" && isset($_GET['monto'])) {
    $montoSolicitado = floatval($_GET['monto']);
    if ($montoSolicitado > 0 && $montoSolicitado <= $evaluacion['monto']) {
        foreach ($cuotasDisponibles as $n) {
            $n = intval($n);
            if ($n > 0) {
                $planes[] = calcular_plan($montoSolicitado, $n, $tasaMensual);
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Resultados BCRA</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f9f9f9; padding: 20px; }
        .card { background: #fff; padding: 20px; border-radius: 12px; box-shadow: 0 4px 8px rgba(0,0,0,0.1); margin-bottom: 20px; }
        h2 { color: #333; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { padding: 10px; border: 1px solid #ddd; text-align: left; }
        th { background: #007BFF; color: white; }
        .aprobado { color: green; font-weight: bold; }
        .rechazado { color: red; font-weight: bold; }
        .detalle { display: none; margin-top: 10px; }
        button { padding: 5px 10px; margin: 5px; cursor: pointer; }
    </style>
    <script>
        function toggleDetalle(id) {
            var div = document.getElementById(id);
            div.style.display = (div.style.display === "none") ? "block" : "none";
        }
    </script>
</head>
<body>
    <div class="card">
            <form action="dashboard.php" method="get">
        <button type="submit">Inicio</button>
        </form>
        <h2>Resultados para CUIT/CUIL: <?php echo htmlspecialchars($cuit); ?></h2>
        <p><strong>Teléfono ingresado:</strong> <?php echo htmlspecialchars($telefono); ?></p>

        <?php if ($data && isset($data['status']) && $data['status'] == 200): ?>
            <p><strong>Denominación:</strong> <?php echo $data['results']['denominacion']; ?></p>

           
            <h3>Evaluación Crediticia</h3>
            <?php if ($evaluacion): ?>
                <p>
                    Estado: 
                    <span class="<?php echo strtolower($evaluacion['estado']); ?>">
                        <?php echo $evaluacion['estado']; ?>
                    </span>
                </p>
                <p>Monto máximo sugerido: <strong>$<?php echo number_format($evaluacion['monto'], 2, ',', '.'); ?></strong></p>
            <?php endif; ?>

           
            <?php if ($evaluacion && $evaluacion['estado'] == "Aprobado"): ?>
                <h3>Simular Préstamo</h3>
                <form method="get" action="">
                    <input type="hidden" name="cuit" value="<?php echo htmlspecialchars($cuit); ?>">
                    <input type="hidden" name="telefono" value="<?php echo htmlspecialchars($telefono); ?>">
                    <input type="number" name="monto" min="1" max="<?php echo $evaluacion['monto']; ?>" placeholder="Ingrese monto a simular">
                    <button type="submit">Simular</button>
                </form>

                <?php if (!empty($planes)): ?>
                    <h4>Resultados de la Simulación (tasa <?php echo $config['tasa_interes']; ?>% mensual)</h4>
                    <table>
                        <tr>
                            <th>Plan</th>
                            <th>Cuota Promedio</th>
                            <th>Detalle</th>
                        </tr>
                        <?php foreach ($planes as $i => $p): ?>
                            <tr>
                                <td><?php echo $p['meses']; ?> cuotas</td>
                                <td>$<?php echo number_format($p['cuota'], 2, ',', '.'); ?></td>
                                <td><button type="button" onclick="toggleDetalle('detalle<?php echo $i; ?>')">Ver detalle</button></td>
                            </tr>
                            <tr id="detalle<?php echo $i; ?>" class="detalle">
                                <td colspan="3">
                                    <table>
                                        <tr>
                                            <th>Mes</th>
                                            <th>Cuota</th>
                                            <th>Interés</th>
                                            <th>Capital</th>
                                            <th>Saldo</th>
                                        </tr>
                                        <?php foreach ($p['detalle'] as $d): ?>
                                            <tr>
                                                <td><?php echo $d['n']; ?></td>
                                                <td>$<?php echo number_format($d['cuota'], 2, ',', '.'); ?></td>
                                                <td>$<?php echo number_format($d['interes'], 2, ',', '.'); ?></td>
                                                <td>$<?php echo number_format($d['capital'], 2, ',', '.'); ?></td>
                                                <td>$<?php echo number_format($d['saldo'], 2, ',', '.'); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </table>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </table>
                <?php endif; ?>
            <?php endif; ?>

        <?php else: ?>
            <p style="color:red;">Error en la consulta:</p>
            <pre><?php print_r($data); ?></pre>
        <?php endif; ?>
    </div>
</body>
</html>

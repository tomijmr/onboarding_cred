<?php
// result.php
if (!isset($_GET['cuit'])) {
    die("No se ingresó un CUIT/CUIL válido.");
}

$cuit = preg_replace('/[^0-9]/', '', $_GET['cuit']); // solo números

if (strlen($cuit) != 11) {
    die("El CUIT/CUIL debe tener 11 dígitos.");
}

// URL del endpoint del BCRA
$url = "https://api.bcra.gob.ar/CentralDeDeudores/v1.0/Deudas/" . $cuit;

// Inicializar cURL
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

// Desactivar validación SSL (solo para pruebas locales)
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

$response = curl_exec($ch);

if (curl_errno($ch)) {
    die("Error en la conexión cURL: " . curl_error($ch));
}

curl_close($ch);

// Decodificar JSON
$data = json_decode($response, true);

// Si no se pudo decodificar el JSON
if (!$data) {
    die("Error al decodificar la respuesta de la API. Respuesta cruda:<br><pre>$response</pre>");
}

// =====================
// Función de scoring
// =====================
function evaluar_credito($data) {
    if (!isset($data['results']['periodos'][0]['entidades'])) {
        return ["estado" => "No disponible", "monto" => 0];
    }

    $entidades = $data['results']['periodos'][0]['entidades'];

    // Tomamos la peor situación de todas las entidades
    $peorSituacion = 1;
    $deudaTotal = 0;
    $diasAtrasoMax = 0;
    $flagCritico = false;

    foreach ($entidades as $e) {
        $situacion = $e['situacion'];
        $monto = $e['monto'] * 1000; // convertir a pesos
        $dias = $e['diasAtrasoPago'];

        $deudaTotal += $monto;
        if ($situacion > $peorSituacion) $peorSituacion = $situacion;
        if ($dias > $diasAtrasoMax) $diasAtrasoMax = $dias;

        if ($e['situacionJuridica'] || $e['procesoJud']) {
            $flagCritico = true;
        }
    }

    // Factores según situación
    $factores = [
        1 => 1.0,
        2 => 0.6,
        3 => 0.3,
        4 => 0.0,
        5 => 0.0
    ];

    // Reglas de aprobación
    if ($peorSituacion >= 4 || $flagCritico) {
        return ["estado" => "Rechazado", "monto" => 0];
    }

    if ($diasAtrasoMax > 30) {
        return ["estado" => "Rechazado", "monto" => 0];
    }

    // Monto base máximo definido en $100.000
    $baseMax = 6000000;
    $montoMax = max(0, ($baseMax - $deudaTotal) * $factores[$peorSituacion]);

    return [
        "estado" => ($montoMax > 0) ? "Aprobado" : "Rechazado",
        "monto" => round($montoMax, 2)
    ];
}

// Evaluar crédito
$evaluacion = ($data['status'] == 200) ? evaluar_credito($data) : null;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Resultados BCRA</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f9f9f9;
            padding: 20px;
        }
        .card {
            background: #fff;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        h2 { color: #333; }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        th, td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: left;
        }
        th {
            background: #007BFF;
            color: white;
        }
        pre {
            background: #eee;
            padding: 10px;
            border-radius: 6px;
        }
        .aprobado { color: green; font-weight: bold; }
        .rechazado { color: red; font-weight: bold; }
        
        button {
            background: #007BFF;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
        }
        button:hover {
            background: #0056b3;
        }
    </style>
</head>
<body>
    <div class="card">
        <h2>Resultados para CUIT/CUIL: <?php echo htmlspecialchars($cuit); ?></h2>
        <?php if ($data && isset($data['status']) && $data['status'] == 200): ?>
            <p><strong>Denominación:</strong> <?php echo $data['results']['denominacion']; ?></p>
            <?php foreach ($data['results']['periodos'] as $periodo): ?>
                <h3>Período: <?php echo $periodo['periodo']; ?></h3>
                <table>
                    <tr>
                        <th>Entidad</th>
                        <th>Situación</th>
                        <th>Monto (miles $)</th>
                        <th>Días Atraso</th>
                    </tr>
                    <?php foreach ($periodo['entidades'] as $entidad): ?>
                        <tr>
                            <td><?php echo $entidad['entidad']; ?></td>
                            <td><?php echo $entidad['situacion']; ?></td>
                            <td><?php echo $entidad['monto']; ?></td>
                            <td><?php echo $entidad['diasAtrasoPago']; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            <?php endforeach; ?>

            <!-- Bloque de evaluación crediticia -->
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

        <?php else: ?>
            <p style="color:red;">Error en la consulta:</p>
            <pre><?php print_r($data); ?></pre>
        <?php endif; ?>
        <a href="dashboard.php"><button type="submit">Volver</button></a>
    </div>
</body>
</html>

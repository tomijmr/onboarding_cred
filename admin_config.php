<?php
$servername = "localhost";
$username = "root";
$password = ""; 
$dbname = "gestconsultasbcra";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// save changg
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tasa = floatval($_POST['tasa_interes']);
    $monto = floatval($_POST['monto_maximo']);
    $cuotas = $conn->real_escape_string($_POST['cuotas_permitidas']);

    $sql = "UPDATE configuracion 
            SET tasa_interes=$tasa, monto_maximo=$monto, cuotas_permitidas='$cuotas'
            WHERE id=1";
    $conn->query($sql);
    echo "<p style='color:green;'>Configuración actualizada correctamente</p>";
}

// get cfg actual
$config = $conn->query("SELECT * FROM configuracion WHERE id=1")->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Administración - Configuración del Sistema</title>

    
    <style>
        body { font-family: Arial, sans-serif; background: #f4f6f8; padding: 20px; }
        .container { background: #fff; padding: 20px; border-radius: 12px; box-shadow: 0 4px 8px rgba(0,0,0,0.1); width: 500px; margin: auto; }
        h2 { text-align: center; }
        label { display: block; margin-top: 15px; }
        input, textarea { width: 100%; padding: 8px; margin-top: 5px; }
        button { margin-top: 20px; padding: 10px; width: 100%; background: #007BFF; color: white; border: none; border-radius: 8px; cursor: pointer; }
        button:hover { background: #0056b3; }
    </style>
</head>
<body>
<div class="container">
    <h2>Configuración del Sistema</h2>
            <form action="dashboard.php" method="get">
        <button type="submit">Inicio</button>
        </form>
    <form method="post">
        <label>Tasa de interés mensual (%)</label>
        <input type="number" step="0.01" name="tasa_interes" value="<?php echo $config['tasa_interes']; ?>" required>

        <label>Monto máximo de crédito ($)</label>
        <input type="number" step="0.01" name="monto_maximo" value="<?php echo $config['monto_maximo']; ?>" required>

        <label>Cuotas permitidas (separadas por comas)</label>
        <input type="text" name="cuotas_permitidas" value="<?php echo $config['cuotas_permitidas']; ?>" required>

        <button type="submit">Guardar Cambios</button>
    </form>
</div>
</body>
</html>

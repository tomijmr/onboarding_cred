<?php
$servername = "localhost";
$username = "root";
$password = ""; 
$dbname = "gestconsultasbcra";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// filtersss
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
$fecha = isset($_GET['fecha']) ? $conn->real_escape_string($_GET['fecha']) : '';

$sql = "SELECT * FROM consultas WHERE 1=1";

if ($search != '') {
    $sql .= " AND (cuit LIKE '%$search%' OR telefono LIKE '%$search%' OR denominacion LIKE '%$search%')";
}
if ($fecha != '') {
    $sql .= " AND DATE(fecha_consulta) = '$fecha'";
}

$sql .= " ORDER BY fecha_consulta DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Administración de Consultas BCRA</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f6f8; padding: 20px; }
        .container { background: #fff; padding: 20px; border-radius: 12px; box-shadow: 0 4px 8px rgba(0,0,0,0.1); }
        h2 { color: #333; }
        form { margin-bottom: 20px; }
        input, button { padding: 8px; font-size: 14px; margin-right: 10px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { padding: 10px; border: 1px solid #ddd; text-align: center; }
        th { background: #007BFF; color: white; }
        .aprobado { color: green; font-weight: bold; }
        .rechazado { color: red; font-weight: bold; }
    </style>
</head>
<body>
<div class="container">
    <h2>Panel de Administración - Consultas BCRA</h2>

    <form action="dashboard.php" method="get">
        <button type="submit">Inicio</button>
        </form>

    <form method="get" action="">
        <input type="text" name="search" placeholder="Buscar por CUIT, Teléfono o Nombre" value="<?php echo htmlspecialchars($search); ?>">
        <input type="date" name="fecha" value="<?php echo htmlspecialchars($fecha); ?>">
        <button type="submit">Filtrar</button>
        <a href="admin_consultas.php"><button type="button">Limpiar</button></a>
        <!-- export button -->
        <a href="export_consultas.php?search=<?php echo urlencode($search); ?>&fecha=<?php echo urlencode($fecha); ?>">
            <button type="button">Exportar a CSV</button>
        </a>
    </form>

    <table>
        <tr>
            <th>ID</th>
            <th>CUIT/CUIL</th>
            <th>Teléfono</th>
            <th>Denominación</th>
            <th>Estado Crédito</th>
            <th>Monto Crédito</th>
            <th>Fecha Consulta</th>
        </tr>
        <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $row['id']; ?></td>
                    <td><?php echo $row['cuit']; ?></td>
                    <td><?php echo $row['telefono']; ?></td>
                    <td><?php echo $row['denominacion']; ?></td>
                    <td class="<?php echo strtolower($row['estado_credito']); ?>">
                        <?php echo $row['estado_credito'] ? $row['estado_credito'] : '-'; ?>
                    </td>
                    <td>
                        <?php 
                        echo $row['monto_credito'] !== null 
                            ? "$" . number_format($row['monto_credito'], 2, ',', '.') 
                            : "-"; 
                        ?>
                    </td>
                    <td><?php echo $row['fecha_consulta']; ?></td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="7">No se encontraron resultados.</td></tr>
        <?php endif; ?>
    </table>
</div>
</body>
</html>

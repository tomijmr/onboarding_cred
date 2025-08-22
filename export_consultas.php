<?php

$servername = "localhost";
$username = "root";
$password = ""; 
$dbname = "gestconsultasbcra";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// filters
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

// gen csv
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=consultas_bcra.csv');
$output = fopen('php://output', 'w');

// Encabezados
fputcsv($output, ['ID', 'CUIT/CUIL', 'Teléfono', 'Denominación', 'Estado Crédito', 'Monto Crédito', 'Fecha Consulta']);

// Filas
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        fputcsv($output, [
            $row['id'],
            $row['cuit'],
            $row['telefono'],
            $row['denominacion'],
            $row['estado_credito'],
            $row['monto_credito'],
            $row['fecha_consulta']
        ]);
    }
}
fclose($output);
exit;
?>

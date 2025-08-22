<?php
$servername = "localhost";
$username = "root";
$password = "SI42dakize"; 
$dbname = "c2611613_bcra_g";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

?>
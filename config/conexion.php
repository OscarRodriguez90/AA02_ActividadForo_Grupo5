<?php
$servername = "localhost";
$dbusername = "root";
$dbpassword = "";
$dbname = "foro_completo";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8mb4", $dbusername, $dbpassword);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Error en la conexión con la base de datos: " . $e->getMessage();
    die();
}
?>
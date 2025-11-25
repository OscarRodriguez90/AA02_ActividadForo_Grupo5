<?php 
$servername = "localhost:3306";
$dbusername = "root";
$dbpassword = "";
$dbname = "db_foro";

try { 
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $dbusername, $dbpassword); 
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); 
} catch (PDOException $e) { 
    echo "Error en la conexión a la base de datos: " . $e->getMessage(); 
    die(); 
} 
?>

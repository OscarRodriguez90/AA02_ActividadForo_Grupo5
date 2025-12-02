<?php
session_start();
require_once '../config/conexion.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../view/login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $id_pregunta = $_POST['id'];
    $titulo      = $_POST['title'];
    $contenido   = $_POST['description'];
    
    $id_usuario_actual = $_SESSION['user_id'];

    try {
        $sqlCheck = "SELECT id_autor FROM tbl_publicaciones WHERE id = :id";
        $stmtCheck = $conn->prepare($sqlCheck);
        $stmtCheck->execute([':id' => $id_pregunta]);
        $row = $stmtCheck->fetch(PDO::FETCH_ASSOC);

        if (!$row || $row['id_autor'] != $id_usuario_actual) {
            die("No tienes permiso.");
        }

        $sqlUpdate = "UPDATE tbl_publicaciones 
                      SET titulo = :titulo, contenido = :contenido 
                      WHERE id = :id";
        
        $stmtUpdate = $conn->prepare($sqlUpdate);
        $stmtUpdate->execute([
            ':titulo' => $titulo,
            ':contenido' => $contenido,
            ':id' => $id_pregunta
        ]);

        header("Location: ../pregunta.php?id=" . $id_pregunta);
        exit;

    } catch (PDOException $e) {
        echo "Error al editar: " . $e->getMessage();
    }
}
?>
<?php
session_start();
require_once '../config/conexion.php';

if (!isset($_GET['id'])) {
    header('Location: ../index.php');
    exit;
}

$id_pregunta = $_GET['id'];
$id_usuario_actual = $_SESSION['user_id'] ?? 1;

try {
    $conn->beginTransaction();

    $sqlCheck = "SELECT id_autor FROM tbl_publicaciones WHERE id = :id";
    $stmtCheck = $conn->prepare($sqlCheck);
    $stmtCheck->execute([':id' => $id_pregunta]);
    $publicacion = $stmtCheck->fetch(PDO::FETCH_ASSOC);

    if (!$publicacion || $publicacion['id_autor'] != $id_usuario_actual) {
        die("Error: No tienes permiso o la pregunta no existe.");
    }

    $ids_a_borrar = [$id_pregunta];

    $sqlResp = "SELECT id FROM tbl_publicaciones WHERE id_padre = :id";
    $stmtResp = $conn->prepare($sqlResp);
    $stmtResp->execute([':id' => $id_pregunta]);
    $respuestas = $stmtResp->fetchAll(PDO::FETCH_COLUMN);

    $todos_los_ids = array_merge($ids_a_borrar, $respuestas);
    
    $ids_string = implode(',', array_map('intval', $todos_los_ids));
    if (!empty($ids_string)) {
        $sqlFiles = "SELECT ruta_archivo FROM tbl_archivos WHERE id_publicacion IN ($ids_string)";
        $stmtFiles = $conn->query($sqlFiles);
        $archivos = $stmtFiles->fetchAll(PDO::FETCH_ASSOC);

        foreach ($archivos as $archivo) {
            $rutaFisica = '../' . $archivo['ruta_archivo'];
            if (file_exists($rutaFisica)) {
                unlink($rutaFisica);
            }
        }

        $conn->exec("DELETE FROM tbl_archivos WHERE id_publicacion IN ($ids_string)");
    }

    $sqlDelResp = "DELETE FROM tbl_publicaciones WHERE id_padre = :id";
    $stmtDelResp = $conn->prepare($sqlDelResp);
    $stmtDelResp->execute([':id' => $id_pregunta]);

    $sqlDelPreg = "DELETE FROM tbl_publicaciones WHERE id = :id";
    $stmtDelPreg = $conn->prepare($sqlDelPreg);
    $stmtDelPreg->execute([':id' => $id_pregunta]);

    $conn->commit();

    header('Location: ../index.php?msg=deleted');
    exit;

} catch (PDOException $e) {
    $conn->rollBack();
    echo "Error al eliminar: " . $e->getMessage();
}
?>
<?php
session_start();
require_once '../config/conexion.php';

// 1. Verificar Autenticación
if (!isset($_SESSION['user_id'])) {
    // Si no está logueado, redirigir al login
    header("Location: ../view/login.php");
    exit;
}

// 2. Procesar la Solicitud POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $publicacion_id = $_POST['id'] ?? null;
    
    // Obtener la URL de retorno o usar index.php por defecto
    $redirect_url = $_POST['redirect'] ?? '../index.php';

    if (!$publicacion_id) {
        header("Location: " . $redirect_url);
        exit;
    }

    try {
        // 3. Verificar si ya existe el Like (Toggle)
        $stmt = $conn->prepare("SELECT id FROM tbl_likes WHERE id_usuario = :uid AND id_publicacion = :pid");
        $stmt->execute([':uid' => $user_id, ':pid' => $publicacion_id]);
        $existing = $stmt->fetch();

        if ($existing) {
            // YA EXISTE -> QUITAR LIKE (UNLIKE)
            $stmt = $conn->prepare("DELETE FROM tbl_likes WHERE id = :id");
            $stmt->execute([':id' => $existing['id']]);
        } else {
            // NO EXISTE -> PONER LIKE
            $stmt = $conn->prepare("INSERT INTO tbl_likes (id_usuario, id_publicacion) VALUES (:uid, :pid)");
            $stmt->execute([':uid' => $user_id, ':pid' => $publicacion_id]);
        }

        // Redirigir de vuelta
        header("Location: " . $redirect_url);
        exit;

    } catch (PDOException $e) {
        // En caso de error, redirigir con mensaje (opcional)
        header("Location: " . $redirect_url . "?error=db");
        exit;
    }
} else {
    // Si no es POST, redirigir al index
    header("Location: ../index.php");
    exit;
}
?>

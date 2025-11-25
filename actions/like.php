<?php
session_start();
require_once '../config/conexion.php';

// 1. Verificar Autenticación
// Si el usuario no está logueado, no puede dar like. Lo redirigimos con un error.
if (!isset($_SESSION['user_id'])) {
    header("Location: ../questions.php?error=auth");
    exit;
}

// 2. Procesar la Solicitud POST
// Solo aceptamos peticiones POST para modificar datos (seguridad)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    
    // Recibimos qué tipo de elemento es ('question' o 'answer') y su ID
    $type = $_POST['type'] ?? null; 
    $id = $_POST['id'] ?? null;

    // Validación básica de datos
    if (!$type || !$id) {
        header("Location: ../questions.php?error=missing_data");
        exit;
    }

    try {
        // 3. Determinar Columnas Afectadas
        // La tabla 'likes' tiene columnas separadas para question_id y answer_id.
        // Dependiendo del tipo, decidimos en qué columna buscar/insertar.
        if ($type === 'question') {
            $col_id = 'question_id';
        } elseif ($type === 'answer') {
            $col_id = 'answer_id';
        } else {
            throw new Exception("Invalid type");
        }

        // 4. Verificar si ya existe el Like (Lógica de Toggle)
        // Consultamos si este usuario ya dio like a este elemento específico.
        // Nota: Usamos interpolación de variable ($col_id) para el nombre de columna, 
        // pero los valores (:uid, :id) van por parámetros preparados para evitar inyección SQL.
        $stmt = $conn->prepare("SELECT id FROM likes WHERE user_id = :uid AND $col_id = :id");
        $stmt->execute([':uid' => $user_id, ':id' => $id]);
        $existing = $stmt->fetch();

        if ($existing) {
            // CASO A: YA EXISTE -> QUITAR LIKE (UNLIKE)
            // Si encontramos un registro, lo borramos.
            $stmt = $conn->prepare("DELETE FROM likes WHERE id = :id");
            $stmt->execute([':id' => $existing['id']]);
            $action = 'removed';
        } else {
            // CASO B: NO EXISTE -> PONER LIKE
            // Si no hay registro, insertamos uno nuevo.
            // La restricción CHECK en la base de datos asegura que solo una de las dos columnas (question_id/answer_id) tenga valor.
            $stmt = $conn->prepare("INSERT INTO likes (user_id, $col_id) VALUES (:uid, :id)");
            $stmt->execute([':uid' => $user_id, ':id' => $id]);
            $action = 'added';
        }

        // Redirigimos de vuelta a questions.php
        header("Location: ../questions.php");
        exit;

    } catch (PDOException $e) {
        // Si algo falla, mostramos error
        header("Location: ../questions.php?error=db&msg=" . urlencode($e->getMessage()));
        exit;
    }
}
?>

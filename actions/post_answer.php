<?php
session_start();
require_once '../config/conexion.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $question_id = $_POST['question_id'] ?? null;
    $content     = $_POST['content'] ?? '';
    
    $user_id = 1; 
    
    if (!$question_id || empty(trim($content))) {
        die("Error: Faltan datos obligatorios.");
    }

    if (strlen($content) > 500) {
        die("Error: La respuesta no puede superar los 500 caracteres.");
    }

    try {
        $conn->beginTransaction();

        $sql = "INSERT INTO tbl_publicaciones (id_autor, contenido, id_padre, titulo) 
                VALUES (:user_id, :content, :parent_id, NULL)";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':user_id'   => $user_id,
            ':content'   => $content,
            ':parent_id' => $question_id
        ]);

        $answer_id = $conn->lastInsertId();

        if (isset($_FILES['files']) && !empty($_FILES['files']['name'][0])) {
            
            $uploadDir = '../uploads/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

            $totalFiles = count($_FILES['files']['name']);
            
            for ($i = 0; $i < $totalFiles; $i++) {
                if ($_FILES['files']['error'][$i] === UPLOAD_ERR_OK) {
                    
                    $fileName = basename($_FILES['files']['name'][$i]);
                    $targetFilePath = $uploadDir . time() . '_' . $fileName;
                    
                    if (move_uploaded_file($_FILES['files']['tmp_name'][$i], $targetFilePath)) {
                        
                        $dbPath = 'uploads/' . time() . '_' . $fileName;
                        
                        $sqlFile = "INSERT INTO tbl_archivos (ruta_archivo, id_publicacion) 
                                    VALUES (:ruta, :pub_id)";
                        $stmtFile = $conn->prepare($sqlFile);
                        $stmtFile->execute([
                            ':ruta'   => $dbPath,
                            ':pub_id' => $answer_id
                        ]);
                    }
                }
            }
        }

        $conn->commit();

        header("Location: ../pregunta.php?id=" . $question_id);
        exit;

    } catch (Exception $e) {
        $conn->rollBack();
        echo "Error al publicar respuesta: " . $e->getMessage();
    }

} else {
    header('Location: ../index.php');
    exit;
}
?>
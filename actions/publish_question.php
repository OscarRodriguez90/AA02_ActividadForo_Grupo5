<?php

require_once '../config/conexion.php'; 

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    $titulo = $_POST['title'];
    $contenido = $_POST['description'];
    
    $id_autor = 1; 

    try {
        $conn->beginTransaction();

        $sql = "INSERT INTO tbl_publicaciones (id_autor, titulo, contenido, id_padre) 
                VALUES (:autor, :titulo, :contenido, NULL)";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':autor' => $id_autor,
            ':titulo' => $titulo,
            ':contenido' => $contenido
        ]);

        $id_publicacion = $conn->lastInsertId();

        if (isset($_FILES['files']) && !empty($_FILES['files']['name'][0])) {
            
            $uploadDir = '../uploads/';
            
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $totalFiles = count($_FILES['files']['name']);
            
            for ($i = 0; $i < $totalFiles; $i++) {
                if ($_FILES['files']['error'][$i] === UPLOAD_ERR_OK) {
                    
                    $fileName = basename($_FILES['files']['name'][$i]);
                    $timestamp = time();
                    $targetFilePath = $uploadDir . $timestamp . '_' . $fileName;
                    
                    if (move_uploaded_file($_FILES['files']['tmp_name'][$i], $targetFilePath)) {
                        
                        $dbPath = 'uploads/' . $timestamp . '_' . $fileName;
                        
                        $sqlFile = "INSERT INTO tbl_archivos (ruta_archivo, id_publicacion) 
                                    VALUES (:ruta, :pub_id)";
                        $stmtFile = $conn->prepare($sqlFile);
                        $stmtFile->execute([
                            ':ruta' => $dbPath,
                            ':pub_id' => $id_publicacion
                        ]);
                    }
                }
            }
        }

        $conn->commit();

        header('Location: ../index.php');
        exit;

    } catch (Exception $e) {
        $conn->rollBack();
        echo "Error al publicar: " . $e->getMessage();
    }

} else {
    header('Location: ../crear_pregunta.php');
    exit;
}
?>
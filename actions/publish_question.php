<?php
// actions/publish_question.php

require_once '../config/conexion.php'; 

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // Recogemos datos del formulario (los names del HTML siguen siendo title y description)
    $titulo = $_POST['title'];
    $contenido = $_POST['description'];
    
    // USUARIO SIMULADO (Cámbialo por $_SESSION['id_usuario'] cuando tengas login)
    // Nota: Asegúrate de tener un usuario creado en tbl_usuarios con id=1
    $id_autor = 1; 

    try {
        $conn->beginTransaction();

        // 1. INSERTAR EN TBL_PUBLICACIONES
        // id_padre va como NULL porque es una Pregunta principal
        $sql = "INSERT INTO tbl_publicaciones (id_autor, titulo, contenido, id_padre) 
                VALUES (:autor, :titulo, :contenido, NULL)";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':autor' => $id_autor,
            ':titulo' => $titulo,
            ':contenido' => $contenido
        ]);

        // Recuperamos el ID generado para esta publicación
        $id_publicacion = $conn->lastInsertId();

        // 2. PROCESAR ARCHIVOS (EL RETO)
        // Verificamos si hay archivos y si el primero no está vacío
        if (isset($_FILES['files']) && !empty($_FILES['files']['name'][0])) {
            
            $uploadDir = '../uploads/';
            
            // Crear carpeta si no existe
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $totalFiles = count($_FILES['files']['name']);
            
            for ($i = 0; $i < $totalFiles; $i++) {
                // Verificamos que no hubo error en la subida temporal
                if ($_FILES['files']['error'][$i] === UPLOAD_ERR_OK) {
                    
                    $fileName = basename($_FILES['files']['name'][$i]);
                    // Nombre único: tiempo_nombre
                    $targetFilePath = $uploadDir . time() . '_' . $fileName;
                    
                    if (move_uploaded_file($_FILES['files']['tmp_name'][$i], $targetFilePath)) {
                        
                        // Ruta limpia para la BD
                        $dbPath = 'uploads/' . time() . '_' . $fileName;
                        
                        // Insertamos en la nueva tabla tbl_archivos
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

        // Redirección al index
        header('Location: ../index.php');
        exit;

    } catch (Exception $e) {
        $conn->rollBack();
        echo "Error al publicar: " . $e->getMessage();
    }

} else {
    // Si entran sin enviar formulario
    header('Location: ../crear_pregunta.php');
    exit;
}
?>
<?php
/**
 * PERFIL ACTIONS
 * Lógica de negocio para la gestión de perfiles de usuario
 */

session_start();
require_once __DIR__ . '/../config/conexion.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: view/login.php");
    exit;
}

$my_id = $_SESSION['user_id'];
$message = '';
$error = '';

// --------------------------------------------------------------------------------
// VERIFICAR QUE SE RECIBIÓ UN ID VÁLIDO
// --------------------------------------------------------------------------------
// --------------------------------------------------------------------------------
// VERIFICAR QUE SE RECIBIÓ UN ID VÁLIDO
// --------------------------------------------------------------------------------
// Si no hay ID, mostrar mi propio perfil
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $profile_id = $my_id;
} else {
    $profile_id = (int)$_GET['id'];
}

// Permitir ver el propio perfil para testeo
// if ($profile_id === $my_id) {
//     header('Location: ./friends.php');
//     exit;
// }

// --------------------------------------------------------------------------------
// ACCIÓN: ENVIAR SOLICITUD DE AMISTAD
// --------------------------------------------------------------------------------
if (isset($_POST['send_friend_request'])) {
    try {
        // Verificar si ya existe relación
        $stmt = $conn->prepare("
            SELECT id FROM tbl_amistades 
            WHERE (id_usuario1 = :u1 AND id_usuario2 = :u2) 
               OR (id_usuario1 = :u2 AND id_usuario2 = :u1)
        ");
        $stmt->bindParam(':u1', $my_id);
        $stmt->bindParam(':u2', $profile_id);
        $stmt->execute();
        
        if (!$stmt->fetch()) {
            // No existe relación, crear solicitud
            $stmt = $conn->prepare("
                INSERT INTO tbl_amistades (id_usuario1, id_usuario2, estado) 
                VALUES (:u1, :u2, 'pendiente')
            ");
            $stmt->bindParam(':u1', $my_id);
            $stmt->bindParam(':u2', $profile_id);
            $stmt->execute();
            $message = "Solicitud de amistad enviada correctamente.";
        } else {
            $error = "Ya existe una relación con este usuario.";
        }
    } catch (PDOException $e) {
        $error = "Error al enviar solicitud: " . $e->getMessage();
    }
}

// --------------------------------------------------------------------------------
// FUNCIÓN: OBTENER INFORMACIÓN DEL USUARIO
// --------------------------------------------------------------------------------
function getUserInfo($conn, $user_id) {
    try {
        $stmt = $conn->prepare("
            SELECT id, username as nombre_usuario, CONCAT(nombre, ' ', apellidos) as nombre_real, email 
            FROM tbl_usuarios 
            WHERE id = :id
        ");
        $stmt->bindParam(':id', $user_id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return null;
    }
}

// --------------------------------------------------------------------------------
// FUNCIÓN: VERIFICAR ESTADO DE AMISTAD
// --------------------------------------------------------------------------------
function getFriendshipStatus($conn, $my_id, $profile_id) {
    try {
        $stmt = $conn->prepare("
            SELECT id, id_usuario1, id_usuario2, estado 
            FROM tbl_amistades 
            WHERE (id_usuario1 = :me AND id_usuario2 = :them) 
               OR (id_usuario1 = :them AND id_usuario2 = :me)
        ");
        $stmt->bindParam(':me', $my_id);
        $stmt->bindParam(':them', $profile_id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return null;
    }
}

// --------------------------------------------------------------------------------
// FUNCIÓN: OBTENER ESTADÍSTICAS DEL USUARIO
// --------------------------------------------------------------------------------
function getUserStats($conn, $user_id) {
    $stats = [
        'publicaciones' => 0,
        'respuestas' => 0,
        'likes_recibidos' => 0
    ];
    
    try {
        // Contar preguntas (publicaciones sin id_padre)
        $stmt = $conn->prepare("
            SELECT COUNT(*) as total 
            FROM tbl_publicaciones 
            WHERE id_autor = :id AND id_padre IS NULL
        ");
        $stmt->bindParam(':id', $user_id);
        $stmt->execute();
        $stats['publicaciones'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        // Contar respuestas (publicaciones con id_padre)
        $stmt = $conn->prepare("
            SELECT COUNT(*) as total 
            FROM tbl_publicaciones 
            WHERE id_autor = :id AND id_padre IS NOT NULL
        ");
        $stmt->bindParam(':id', $user_id);
        $stmt->execute();
        $stats['respuestas'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        // Contar likes recibidos en sus publicaciones
        $stmt = $conn->prepare("
            SELECT COUNT(*) as total 
            FROM tbl_likes l 
            JOIN tbl_publicaciones p ON l.id_publicacion = p.id 
            WHERE p.id_autor = :id
        ");
        $stmt->bindParam(':id', $user_id);
        $stmt->execute();
        $stats['likes_recibidos'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
    } catch (PDOException $e) {
        // Mantener valores por defecto en caso de error
    }
    
    return $stats;
}

// --------------------------------------------------------------------------------
// FUNCIÓN: OBTENER ÚLTIMAS PUBLICACIONES DEL USUARIO
// --------------------------------------------------------------------------------
function getUserPosts($conn, $user_id, $viewer_id, $limit = 5) {
    try {
        $stmt = $conn->prepare("
            SELECT p.id, p.titulo, p.contenido, p.fecha, p.id_padre,
                   COUNT(DISTINCT l.id) as num_likes,
                   COUNT(DISTINCT r.id) as num_respuestas,
                   (SELECT COUNT(*) FROM tbl_likes WHERE id_publicacion = p.id AND id_usuario = :viewer_id) as user_liked
            FROM tbl_publicaciones p
            LEFT JOIN tbl_likes l ON p.id = l.id_publicacion
            LEFT JOIN tbl_publicaciones r ON p.id = r.id_padre
            WHERE p.id_autor = :id AND p.id_padre IS NULL
            GROUP BY p.id
            ORDER BY p.fecha DESC
            LIMIT :limit
        ");
        $stmt->bindParam(':id', $user_id);
        $stmt->bindParam(':viewer_id', $viewer_id);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return [];
    }
}

// --------------------------------------------------------------------------------
// EJECUTAR LÓGICA Y OBTENER DATOS
// --------------------------------------------------------------------------------

// Obtener información del usuario
$user = getUserInfo($conn, $profile_id);

// Si no existe el usuario, redirigir
if (!$user) {
    header('Location: friends.php');
    exit;
}

// Verificar estado de amistad
$friendship = getFriendshipStatus($conn, $my_id, $profile_id);
$friendship_status = $friendship ? $friendship['estado'] : null;
$friendship_id = $friendship ? $friendship['id'] : null;
$am_i_sender = $friendship ? ($friendship['id_usuario1'] == $my_id) : false;

// Obtener estadísticas
$user_stats = getUserStats($conn, $profile_id);

// Obtener publicaciones
$user_posts = getUserPosts($conn, $profile_id, $my_id, 5);

// Los datos están listos para ser usados en la vista
?>

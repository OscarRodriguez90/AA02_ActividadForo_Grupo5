<?php
session_start();
require '../config/conexion.php';
require '../proc/validaciones.php';

$errores=[];

if($_SERVER['REQUEST_METHOD']==='POST'){
    $usuario_o_email = trim($_POST['usuario_o_email']);
    $password = $_POST['password'];

    $errores = validarLogin($usuario_o_email,$password);

    if(empty($errores)){
        $stmt=$conn->prepare("SELECT * FROM tbl_usuarios WHERE username=:u OR email=:u");
        $stmt->execute(['u'=>$usuario_o_email]);
        $usuario = $stmt->fetch();

        if($usuario && password_verify($password,$usuario['password'])){
            $_SESSION['user_id']=$usuario['id'];
            $_SESSION['username']=$usuario['username'];
            header("Location: ../index.php");
            exit();
        } else $errores[]="Usuario o contraseña incorrectos.";
    }
}
?>

<link rel="stylesheet" href="../estilos/style.css">

<form method="post" action="login.php">
    <input type="text" name="usuario_o_email" placeholder="Usuario o correo electrónico"><br>
    <input type="password" name="password" placeholder="Contraseña"><br>
    <button type="submit">Iniciar sesión</button>
</form>

<?php if(!empty($errores)){ foreach($errores as $e) echo "<p style='color:red;'>$e</p>"; } ?>

<p>¿No tienes cuenta? <a href="register.php">Regístrate aquí</a></p>

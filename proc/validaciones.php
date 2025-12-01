<?php
require '../config/conexion.php';

function validarRegistro($username, $nombre, $apellidos, $email, $fecha_nacimiento, $genero, $password, $confirmar_password, $conn) {
    $errores = [];

    if(empty($username)) $errores[]="El nombre de usuario es obligatorio.";
    elseif(strlen($username)<3) $errores[]="El nombre de usuario debe tener al menos 3 caracteres.";
    elseif(strlen($username)>50) $errores[]="El nombre de usuario no puede superar los 50 caracteres.";

    if(empty($nombre)) $errores[]="El nombre es obligatorio.";
    elseif(strlen($nombre)<2) $errores[]="El nombre debe tener al menos 2 caracteres.";
    elseif(strlen($nombre)>50) $errores[]="El nombre no puede superar los 50 caracteres.";
    elseif(!preg_match("/^[a-zA-ZáéíóúÁÉÍÓÚñÑüÜ\s]+$/u", $nombre)) $errores[]="El nombre solo puede contener letras.";

    if(empty($apellidos)) $errores[]="Los apellidos son obligatorios.";
    elseif(strlen($apellidos)<2) $errores[]="Los apellidos deben tener al menos 2 caracteres.";
    elseif(strlen($apellidos)>100) $errores[]="Los apellidos no pueden superar los 100 caracteres.";
    elseif(!preg_match("/^[a-zA-ZáéíóúÁÉÍÓÚñÑüÜ\s]+$/u", $apellidos)) $errores[]="Los apellidos solo pueden contener letras.";

    if(empty($email)) $errores[]="El correo electrónico es obligatorio.";
    elseif(!filter_var($email,FILTER_VALIDATE_EMAIL)) $errores[]="Correo con formato inválido.";

    if(empty($fecha_nacimiento)) $errores[]="La fecha de nacimiento es obligatoria.";
    else {
        $fecha = strtotime($fecha_nacimiento);
        if(!$fecha) $errores[]="Formato de fecha no válido.";
        else {
            $hoy = strtotime(date('Y-m-d'));
            if($fecha>$hoy) $errores[]="No puedes poner una fecha futura.";
            list($ano,$mes,$dia) = explode("-",$fecha_nacimiento);
            if($mes<1||$mes>12) $errores[]="El mes debe ser entre 1 y 12.";
            if($dia<1||$dia>31) $errores[]="El día debe ser entre 1 y 31.";
            $edad=(int)((time()-$fecha)/(60*60*24*365));
            if($edad<16) $errores[]="Debes tener al menos 16 años.";
        }
    }

    $valores = ["hombre","mujer","otro"];
    if(empty($genero)) $errores[]="Debe seleccionar un género.";
    elseif(!in_array($genero,$valores)) $errores[]="El género seleccionado no es válido.";

    if(empty($password)) $errores[]="La contraseña es obligatoria.";
    elseif(strlen($password)<8) $errores[]="Debe tener al menos 8 caracteres.";

    $tieneNumero = preg_match('/[0-9]/',$password);
    $tieneMayus = preg_match('/[A-Z]/',$password);
    $tieneMinus = preg_match('/[a-z]/',$password);
    $tieneSimbolo = preg_match('/[\W]/',$password);

    if(!empty($password)){
        if(!$tieneMayus) $errores[]="Debe contener al menos una letra mayúscula.";
        if(!$tieneMinus) $errores[]="Debe contener al menos una letra minúscula.";
        if(!$tieneNumero) $errores[]="Debe contener al menos un número.";
        if(!$tieneSimbolo) $errores[]="Debe contener al menos un símbolo.";
    }

    if($password!==$confirmar_password) $errores[]="Las contraseñas no coinciden.";

    if(!empty($username)&&!empty($email)){
        $stmtU=$conn->prepare("SELECT id FROM tbl_usuarios WHERE username=:usuario");
        $stmtU->execute(['usuario'=>$username]);
        if($stmtU->fetch()) $errores[]="Este username ya está en uso.";

        $stmtE=$conn->prepare("SELECT id FROM tbl_usuarios WHERE email=:email");
        $stmtE->execute(['email'=>$email]);
        if($stmtE->fetch()) $errores[]="Esta dirección de correo electrónico ya está en uso.";
    }

    return $errores;
}

function validarLogin($usuario_o_email, $password) {
    $errores = [];
    
    // Validar que el campo de usuario o email no esté vacío
    if(empty($usuario_o_email)) {
        $errores[] = "Introduce tu usuario o correo electrónico.";
    } else {
        // Limpiar espacios en blanco
        $usuario_o_email = trim($usuario_o_email);
        
        // Validar longitud mínima (3 caracteres para username)
        if(strlen($usuario_o_email) < 3) {
            $errores[] = "El usuario o correo debe tener al menos 3 caracteres.";
        }
        
        // Validar longitud máxima
        if(strlen($usuario_o_email) > 100) {
            $errores[] = "El usuario o correo no puede superar los 100 caracteres.";
        }
        
        // Si contiene @, validar que sea un email válido
        if(strpos($usuario_o_email, '@') !== false) {
            if(!filter_var($usuario_o_email, FILTER_VALIDATE_EMAIL)) {
                $errores[] = "El formato del correo electrónico no es válido.";
            }
        } else {
            // Si no contiene @, es un username - validar caracteres permitidos
            if(!preg_match("/^[a-zA-Z0-9_.-]+$/", $usuario_o_email)) {
                $errores[] = "El nombre de usuario solo puede contener letras, números, guiones y puntos.";
            }
        }
    }
    
    // Validar que el campo de contraseña no esté vacío
    if(empty($password)) {
        $errores[] = "Introduce tu contraseña.";
    } else {
        // Validar longitud mínima de contraseña
        if(strlen($password) < 8) {
            $errores[] = "La contraseña debe tener al menos 8 caracteres.";
        }
        
        // Validar longitud máxima de contraseña
        if(strlen($password) > 255) {
            $errores[] = "La contraseña no puede superar los 255 caracteres.";
        }
    }
    
    return $errores;
}

function validarEdicionPerfil($username, $nombre, $apellidos, $email, $fecha_nacimiento, $genero, $user_id, $conn) {
    $errores = [];

    // Validar que ningún campo esté vacío
    if(empty($username)) $errores[]="El nombre de usuario es obligatorio.";
    if(empty($nombre)) $errores[]="El nombre es obligatorio.";
    if(empty($apellidos)) $errores[]="Los apellidos son obligatorios.";
    if(empty($email)) $errores[]="El correo electrónico es obligatorio.";
    if(empty($fecha_nacimiento)) $errores[]="La fecha de nacimiento es obligatoria.";
    if(empty($genero)) $errores[]="Debe seleccionar un género.";

    // Validar username: al menos 3 caracteres y no superar los 50 caracteres
    if(!empty($username)) {
        if(strlen($username) < 3) $errores[]="El nombre de usuario debe tener al menos 3 caracteres.";
        elseif(strlen($username) > 50) $errores[]="El nombre de usuario no puede superar los 50 caracteres.";
    }

    // Validar nombre: al menos 2 caracteres y no superar los 50 caracteres
    if(!empty($nombre)) {
        if(strlen($nombre) < 2) $errores[]="El nombre debe tener al menos 2 caracteres.";
        elseif(strlen($nombre) > 50) $errores[]="El nombre no puede superar los 50 caracteres.";
        elseif(!preg_match("/^[a-zA-ZáéíóúÁÉÍÓÚñÑüÜ\s]+$/u", $nombre)) $errores[]="El nombre solo puede contener letras.";
    }

    // Validar apellidos: al menos 2 caracteres y no superar los 50 caracteres
    if(!empty($apellidos)) {
        if(strlen($apellidos) < 2) $errores[]="Los apellidos deben tener al menos 2 caracteres.";
        elseif(strlen($apellidos) > 50) $errores[]="Los apellidos no pueden superar los 50 caracteres.";
        elseif(!preg_match("/^[a-zA-ZáéíóúÁÉÍÓÚñÑüÜ\s]+$/u", $apellidos)) $errores[]="Los apellidos solo pueden contener letras.";
    }

    // Validar formato del correo electrónico
    if(!empty($email)) {
        if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errores[]="El formato del correo electrónico no es válido.";
        }
    }

    // Validar formato de fecha y que no sea futura
    if(!empty($fecha_nacimiento)) {
        $fecha = strtotime($fecha_nacimiento);
        if(!$fecha) {
            $errores[]="El formato de la fecha no es válido.";
        } else {
            $hoy = strtotime(date('Y-m-d'));
            if($fecha > $hoy) {
                $errores[]="No se pueden introducir fechas futuras.";
            }
            // Validar que el mes y día sean válidos
            list($ano, $mes, $dia) = explode("-", $fecha_nacimiento);
            if($mes < 1 || $mes > 12) $errores[]="El mes debe ser entre 1 y 12.";
            if($dia < 1 || $dia > 31) $errores[]="El día debe ser entre 1 y 31.";
        }
    }

    // Validar que se ha seleccionado algún género
    $valores_genero = ["hombre", "mujer", "otro"];
    if(!empty($genero)) {
        if(!in_array($genero, $valores_genero)) {
            $errores[]="El género seleccionado no es válido.";
        }
    }

    // Validar que el username no esté en uso por otro usuario
    if(!empty($username) && !empty($user_id)) {
        $stmtU = $conn->prepare("SELECT id FROM tbl_usuarios WHERE username = :username AND id != :user_id");
        $stmtU->execute(['username' => $username, 'user_id' => $user_id]);
        if($stmtU->fetch()) {
            $errores[]="Este nombre de usuario ya está en uso por otro usuario.";
        }
    }

    return $errores;
}
?>

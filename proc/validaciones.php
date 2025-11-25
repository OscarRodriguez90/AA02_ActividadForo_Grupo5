<?php
require '../conexion/conexion.php';

function validarRegistro($username, $nombre, $apellidos, $email, $fecha_nacimiento, $genero, $password, $confirmar_password, $conn) {
    $errores = [];

    if(empty($username)) $errores[]="El nombre de usuario es obligatorio.";
    elseif(strlen($username)<3) $errores[]="El nombre de usuario debe tener al menos 3 caracteres.";
    elseif(strlen($username)>50) $errores[]="El nombre de usuario no puede superar los 50 caracteres.";

    if(empty($nombre)) $errores[]="El nombre es obligatorio.";
    elseif(strlen($nombre)<2) $errores[]="El nombre debe tener al menos 2 caracteres.";
    elseif(strlen($nombre)>50) $errores[]="El nombre no puede superar los 50 caracteres.";

    if(empty($apellidos)) $errores[]="Los apellidos son obligatorios.";
    elseif(strlen($apellidos)<2) $errores[]="Los apellidos deben tener al menos 2 caracteres.";
    elseif(strlen($apellidos)>100) $errores[]="Los apellidos no pueden superar los 100 caracteres.";

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

function validarLogin($usuario_o_email,$password){
    $errores=[];
    if(empty($usuario_o_email)) $errores[]="Introduce tu usuario o correo electrónico.";
    if(empty($password)) $errores[]="Introduce tu contraseña.";
    return $errores;
}
?>

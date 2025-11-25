-- Crear base de datos
CREATE DATABASE db_foro;
USE db_foro;

-- =====================================
-- CREACIÓN DE TABLA tbl_usuarios
-- =====================================

CREATE TABLE IF NOT EXISTS tbl_usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    nombre VARCHAR(50) NOT NULL,
    apellidos VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    fecha_nacimiento DATE NOT NULL,
    genero ENUM('hombre','mujer','otro') NOT NULL,
    password VARCHAR(255) NOT NULL
) ENGINE=InnoDB;

-- =====================================
-- DATOS DE EJEMPLO
-- =====================================

INSERT INTO tbl_usuarios (username, nombre, apellidos, email, fecha_nacimiento, genero, password) VALUES
('ejemplo', 'Ejemplo', 'Ejemplo', 'ejemplo@mail.com', '2000-01-01', 'hombre', 'HASH_BCRYPT_AQUI');

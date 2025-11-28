-- Crear base de datos
CREATE DATABASE IF NOT EXISTS db_foro;
USE db_foro;

-- =====================================
-- CREACIÓN DE TABLAS
-- =====================================

-- Tabla de usuarios
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

-- Tabla de publicaciones (preguntas o respuestas)
CREATE TABLE IF NOT EXISTS tbl_publicaciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_autor INT NOT NULL,
    titulo VARCHAR(255) DEFAULT NULL,   -- solo tendrá título si es pregunta
    contenido TEXT NOT NULL,
    id_padre INT DEFAULT NULL,          -- si es NULL = pregunta principal / si NO = respuesta
    fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)ENGINE=InnoDB;

-- Tabla de etiquetas
CREATE TABLE IF NOT EXISTS tbl_etiquetas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL UNIQUE
)ENGINE=InnoDB;

-- Tabla de asociación publicación-etiqueta
CREATE TABLE IF NOT EXISTS tbl_publicacion_etiqueta (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_publicacion INT NOT NULL,
    id_etiqueta INT NOT NULL
)ENGINE=InnoDB;

-- Tabla de likes (1 like por usuario y publicación)
CREATE TABLE IF NOT EXISTS tbl_likes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    id_publicacion INT NOT NULL,
    fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE (id_usuario, id_publicacion)
)ENGINE=InnoDB;

-- Tabla de amistades entre usuarios
CREATE TABLE IF NOT EXISTS tbl_amistades (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario1 INT NOT NULL,
    id_usuario2 INT NOT NULL,
    estado ENUM('pendiente','aceptada','denegada') DEFAULT 'pendiente'
)ENGINE=InnoDB;

-- Tabla de mensajes privados entre amigos
CREATE TABLE IF NOT EXISTS tbl_mensajes_privados (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_emisor INT NOT NULL,
    id_receptor INT NOT NULL,
    mensaje TEXT NOT NULL,
    fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS tbl_archivos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ruta_archivo VARCHAR(300) NOT NULL,
    id_publicacion INT NOT NULL
) ENGINE=InnoDB;

-- =====================================
-- RELACIONES (FOREIGN KEYS)
-- =====================================

-- Relaciones publicaciones
ALTER TABLE tbl_publicaciones
    ADD CONSTRAINT fk_publicaciones_autor
    FOREIGN KEY (id_autor) REFERENCES tbl_usuarios(id);

ALTER TABLE tbl_publicaciones
    ADD CONSTRAINT fk_publicaciones_padre
    FOREIGN KEY (id_padre) REFERENCES tbl_publicaciones(id);

-- Relaciones publicación-etiqueta
ALTER TABLE tbl_publicacion_etiqueta
    ADD CONSTRAINT fk_pubeti_publicacion
    FOREIGN KEY (id_publicacion) REFERENCES tbl_publicaciones(id);

ALTER TABLE tbl_publicacion_etiqueta
    ADD CONSTRAINT fk_pubeti_etiqueta
    FOREIGN KEY (id_etiqueta) REFERENCES tbl_etiquetas(id);

-- Relaciones likes
ALTER TABLE tbl_likes
    ADD CONSTRAINT fk_likes_usuario
    FOREIGN KEY (id_usuario) REFERENCES tbl_usuarios(id);

ALTER TABLE tbl_likes
    ADD CONSTRAINT fk_likes_publicacion
    FOREIGN KEY (id_publicacion) REFERENCES tbl_publicaciones(id);

-- Relaciones amistades (ambos lados)
ALTER TABLE tbl_amistades
    ADD CONSTRAINT fk_amistades_usuario1
    FOREIGN KEY (id_usuario1) REFERENCES tbl_usuarios(id);

ALTER TABLE tbl_amistades
    ADD CONSTRAINT fk_amistades_usuario2
    FOREIGN KEY (id_usuario2) REFERENCES tbl_usuarios(id);

-- Relaciones mensajes privados
ALTER TABLE tbl_mensajes_privados
    ADD CONSTRAINT fk_mensajes_privados_emisor
    FOREIGN KEY (id_emisor) REFERENCES tbl_usuarios(id);

ALTER TABLE tbl_mensajes_privados
    ADD CONSTRAINT fk_mensajes_privados_receptor
    FOREIGN KEY (id_receptor) REFERENCES tbl_usuarios(id);

ALTER TABLE tbl_archivos
    ADD CONSTRAINT fk_archivos_publicacion
    FOREIGN KEY (id_publicacion) REFERENCES tbl_publicaciones(id);



-- =====================================
-- DATOS DE EJEMPLO
-- =====================================

INSERT INTO tbl_usuarios (nombre_usuario, nombre_real, email, contraseña) VALUES
('admin', 'Administrador del foro', 'admin@foro.com', 'HASH_BCRYPT_AQUI');

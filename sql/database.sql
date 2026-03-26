CREATE DATABASE IF NOT EXISTS ticketpro_db
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE ticketpro_db;

CREATE TABLE IF NOT EXISTS roles (
  id_rol INT AUTO_INCREMENT PRIMARY KEY,
  nombre_rol VARCHAR(50) NOT NULL UNIQUE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS usuarios (
  id_usuario INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(100) NOT NULL,
  apellidos VARCHAR(150) NOT NULL,
  email VARCHAR(150) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  telefono VARCHAR(30) NULL,
  empresa VARCHAR(150) NOT NULL,
  activo TINYINT(1) NOT NULL DEFAULT 1,
  rol_id INT NOT NULL,
  fecha_creacion TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_usuarios_roles
    FOREIGN KEY (rol_id) REFERENCES roles(id_rol)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS categorias (
  id_categoria INT AUTO_INCREMENT PRIMARY KEY,
  nombre_categoria VARCHAR(100) NOT NULL UNIQUE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS estados (
  id_estado INT AUTO_INCREMENT PRIMARY KEY,
  nombre_estado VARCHAR(50) NOT NULL UNIQUE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS prioridades (
  id_prioridad INT AUTO_INCREMENT PRIMARY KEY,
  nombre_prioridad VARCHAR(50) NOT NULL UNIQUE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS incidencias (
  id_incidencia INT AUTO_INCREMENT PRIMARY KEY,
  codigo VARCHAR(20) NULL UNIQUE,
  titulo VARCHAR(150) NOT NULL,
  descripcion TEXT NOT NULL,
  cliente_id INT NOT NULL,
  tecnico_id INT NULL,
  categoria_id INT NOT NULL,
  estado_id INT NOT NULL,
  prioridad_id INT NOT NULL,
  fecha_creacion TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_incidencias_cliente
    FOREIGN KEY (cliente_id) REFERENCES usuarios(id_usuario),
  CONSTRAINT fk_incidencias_tecnico
    FOREIGN KEY (tecnico_id) REFERENCES usuarios(id_usuario),
  CONSTRAINT fk_incidencias_categoria
    FOREIGN KEY (categoria_id) REFERENCES categorias(id_categoria),
  CONSTRAINT fk_incidencias_estado
    FOREIGN KEY (estado_id) REFERENCES estados(id_estado),
  CONSTRAINT fk_incidencias_prioridad
    FOREIGN KEY (prioridad_id) REFERENCES prioridades(id_prioridad)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS comentarios (
  id_comentario INT AUTO_INCREMENT PRIMARY KEY,
  incidencia_id INT NOT NULL,
  usuario_id INT NOT NULL,
  comentario TEXT NOT NULL,
  fecha_creacion TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_comentarios_incidencia
    FOREIGN KEY (incidencia_id) REFERENCES incidencias(id_incidencia),
  CONSTRAINT fk_comentarios_usuario
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id_usuario)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS historial_acciones (
  id_historial INT AUTO_INCREMENT PRIMARY KEY,
  incidencia_id INT NOT NULL,
  usuario_id INT NOT NULL,
  accion VARCHAR(100) NOT NULL,
  descripcion TEXT NOT NULL,
  fecha_creacion TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_historial_incidencia
    FOREIGN KEY (incidencia_id) REFERENCES incidencias(id_incidencia),
  CONSTRAINT fk_historial_usuario
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id_usuario)
) ENGINE=InnoDB;

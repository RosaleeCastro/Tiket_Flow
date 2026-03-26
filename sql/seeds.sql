USE ticketpro_db;

INSERT INTO roles (id_rol, nombre_rol) VALUES
  (1, 'admin'),
  (2, 'tecnico'),
  (3, 'cliente')
ON DUPLICATE KEY UPDATE nombre_rol = VALUES(nombre_rol);

INSERT INTO categorias (nombre_categoria) VALUES
  ('Hardware'),
  ('Software'),
  ('Red'),
  ('Acceso')
ON DUPLICATE KEY UPDATE nombre_categoria = VALUES(nombre_categoria);

INSERT INTO estados (id_estado, nombre_estado) VALUES
  (1, 'abierta'),
  (2, 'en_proceso'),
  (3, 'resuelta'),
  (4, 'cerrada')
ON DUPLICATE KEY UPDATE nombre_estado = VALUES(nombre_estado);

INSERT INTO prioridades (id_prioridad, nombre_prioridad) VALUES
  (1, 'baja'),
  (2, 'media'),
  (3, 'alta'),
  (4, 'critica')
ON DUPLICATE KEY UPDATE nombre_prioridad = VALUES(nombre_prioridad);

INSERT INTO usuarios (
  nombre,
  apellidos,
  email,
  password,
  telefono,
  empresa,
  activo,
  rol_id
) VALUES (
  'Administrador',
  'Principal',
  'admin@ticketflow.local',
  'admin123',
  '600000000',
  'Ticket Flow',
  1,
  1
)
ON DUPLICATE KEY UPDATE email = VALUES(email);

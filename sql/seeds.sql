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

INSERT INTO usuarios (
  nombre,
  apellidos,
  email,
  password,
  telefono,
  empresa,
  activo,
  rol_id
) VALUES
  ('Lucia', 'Gomez', 'tecnico1@ticketflow.local', 'tec123', '611111111', 'Ticket Flow', 1, 2),
  ('Marcos', 'Ruiz', 'tecnico2@ticketflow.local', 'tec123', '622222222', 'Ticket Flow', 1, 2),
  ('Ana', 'Lopez', 'cliente1@acme.local', 'cli123', '633333333', 'Acme', 1, 3),
  ('David', 'Martin', 'cliente2@globex.local', 'cli123', '644444444', 'Globex', 1, 3),
  ('Sofia', 'Diaz', 'cliente3@initech.local', 'cli123', '655555555', 'Initech', 1, 3)
ON DUPLICATE KEY UPDATE email = VALUES(email);

INSERT INTO incidencias (
  codigo,
  titulo,
  descripcion,
  cliente_id,
  tecnico_id,
  categoria_id,
  estado_id,
  prioridad_id
)
SELECT
  d.codigo,
  d.titulo,
  d.descripcion,
  c.id_usuario AS cliente_id,
  t.id_usuario AS tecnico_id,
  cat.id_categoria AS categoria_id,
  e.id_estado AS estado_id,
  p.id_prioridad AS prioridad_id
FROM (
  SELECT 'INC-1001' AS codigo, 'No enciende el equipo' AS titulo, 'El portatil no enciende desde esta manana' AS descripcion, 'cliente1@acme.local' AS cliente_email, 'tecnico1@ticketflow.local' AS tecnico_email, 'Hardware' AS categoria, 'abierta' AS estado, 'alta' AS prioridad
  UNION ALL
  SELECT 'INC-1002', 'Error al abrir ERP', 'La aplicacion ERP se cierra al iniciar sesion', 'cliente1@acme.local', 'tecnico2@ticketflow.local', 'Software', 'en_proceso', 'media'
  UNION ALL
  SELECT 'INC-1003', 'Sin conexion de red', 'No hay acceso a internet en el puesto de ventas', 'cliente2@globex.local', 'tecnico1@ticketflow.local', 'Red', 'abierta', 'critica'
  UNION ALL
  SELECT 'INC-1004', 'Bloqueo de usuario AD', 'Usuario bloqueado tras varios intentos fallidos', 'cliente2@globex.local', 'tecnico2@ticketflow.local', 'Acceso', 'resuelta', 'alta'
  UNION ALL
  SELECT 'INC-1005', 'Impresora no responde', 'La impresora de recepcion no imprime documentos', 'cliente3@initech.local', 'tecnico1@ticketflow.local', 'Hardware', 'en_proceso', 'media'
  UNION ALL
  SELECT 'INC-1006', 'VPN intermitente', 'La conexion VPN se corta cada pocos minutos', 'cliente3@initech.local', 'tecnico2@ticketflow.local', 'Red', 'cerrada', 'baja'
) d
INNER JOIN usuarios c ON c.email = d.cliente_email
INNER JOIN usuarios t ON t.email = d.tecnico_email
INNER JOIN categorias cat ON cat.nombre_categoria = d.categoria
INNER JOIN estados e ON e.nombre_estado = d.estado
INNER JOIN prioridades p ON p.nombre_prioridad = d.prioridad
ON DUPLICATE KEY UPDATE
  titulo = VALUES(titulo),
  descripcion = VALUES(descripcion),
  tecnico_id = VALUES(tecnico_id),
  categoria_id = VALUES(categoria_id),
  estado_id = VALUES(estado_id),
  prioridad_id = VALUES(prioridad_id);

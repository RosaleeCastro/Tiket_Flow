<?php
  require_once __DIR__ . '/../includes/auth.php';
  require_once __DIR__ . '/../config/database.php';

  //Solo puede entrar un técnico autenticado

  requireRole('tecnico');

  //verificamos conexión
  if(!isset($conn) || ($conn instanceof mysqli)){
    die('Error : la conexion $conn no está disponible en config/database.php');
  }

  //Usuario actual
  $usuario = currentUser();
  $tecnicoId =(int)($usuario['id']?? 0);

  if($tecnicoId <= 0){
    die('Error : no se pudo identificar al técnico autenticado.');
  }

  /*
  |--------------------------------------
  |Consulta de incidencias asignadas al técnico
  |-----------------------------------------------   
  */

  $sql = "SELECT 
          i.id_inicidencia,
          i.codigo,
          i.titulo,
          i.fecha_creacion,
          c.nombre_categoria,
          c.nombre_estado,
          p.nombre_prioridad,
          CONCAT(u.nombre, '', u.apellidos) AS cliente_nombre 
          FROM inidencias i 
          INNER JOIN categorias c "

?>
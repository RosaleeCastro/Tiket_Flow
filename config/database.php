<?php
//trabajo con xammp, sql en phpadmin 
$host ="localhost";
$dbname ="ticketpro_db";
$user = "root";
$pass = "";

  //Crear la conexión con MySQL usando mysqli 
  
  $conexion = new mysqli($host, $user, $pass, $dbname);

  //verificar si hubo error de conexión

  if($conexion->connect_error){
    die("Error de conexion : ". $conexion->connect_error);
  }

  // Establecer el juego de caracteres a utf8mb4
  $conexion->set_charset("utf8mb4");
?>
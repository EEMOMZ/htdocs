<?php
$host = "localhost:3307";
$usuario = "root";        
$contrasena = "";         
$bd = "kingdom";

// Crear conexión
$conn = new mysqli($host, $usuario, $contrasena, $bd);

// Verificar conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Opcional: establecer codificación UTF-8
$conn->set_charset("utf8");
?>
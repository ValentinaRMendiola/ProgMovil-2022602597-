<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Conexión a la base de datos
$conexion = new mysqli("localhost", "root", "usbw", "mibase");

if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

// Consulta
$sql = "SELECT titulo, autor, descripcion, fecha_publicacion, url_imagen, url_pdf FROM libros";
$resultado = $conexion->query($sql);

// Verifica si hay resultados
$libros = [];

if ($resultado && $resultado->num_rows > 0) {
    while ($fila = $resultado->fetch_assoc()) {
        $libros[] = $fila;
    }
}

// Devolver JSON
header('Content-Type: application/json');
echo json_encode($libros);

$conexion->close();
?>

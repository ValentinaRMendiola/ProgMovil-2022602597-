<?php
$host = "localhost";
$user = "root";
$password = "usbw"; // Tu contraseña para phpMyAdmin
$dbname = "mibase";

$mysqli = new mysqli($host, $user, $password, $dbname);

if ($mysqli->connect_error) {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => "Error de conexión a MySQL",
        "error_detail" => $mysqli->connect_error
    ]);
    exit;
}
?>

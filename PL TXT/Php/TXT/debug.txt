<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json; charset=utf-8');

$input = file_get_contents("php://input");

if (!$input) {
    echo json_encode(["status" => "error", "message" => "No se recibió ningún dato en el cuerpo"]);
    exit;
}

$data = json_decode($input, true);

if ($data === null) {
    echo json_encode(["status" => "error", "message" => "JSON inválido recibido: $input"]);
    exit;
}

echo json_encode(["status" => "ok", "recibido" => $data]);

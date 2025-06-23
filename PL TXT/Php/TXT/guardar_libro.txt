<?php 
// Mostrar errores para depuración
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");

// Leer el cuerpo JSON
$raw = file_get_contents("php://input");
$data = json_decode($raw, true);

// Validar JSON
if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    echo json_encode([
        "status" => "error",
        "message" => "JSON inválido",
        "error_detail" => json_last_error_msg()
    ]);
    exit;
}

// Validar campos requeridos
$required = [
    "id", "titulo", "autor", "descripcion",
    "fecha_publicacion", "fecha_modificacion",
    "eliminado", "url_imagen", "url_pdf", "genero"
];

foreach ($required as $key) {
    if (!isset($data[$key])) {
        http_response_code(400);
        echo json_encode([
            "status" => "error",
            "message" => "Falta el campo '$key'"
        ]);
        exit;
    }
}

// Sanitizar y validar tipos y contenido lógico
$id = trim($data["id"]);
$titulo = trim($data["titulo"]);
$autor = trim($data["autor"]);
$descripcion = trim($data["descripcion"]);
$fecha_publicacion = trim($data["fecha_publicacion"]);
$fecha_modificacion = trim($data["fecha_modificacion"]);
$eliminado = intval($data["eliminado"]);
$url_imagen = trim($data["url_imagen"]);
$url_pdf = trim($data["url_pdf"]);
$genero = trim($data["genero"]);

// Validaciones lógicas
if (strlen($id) !== 32) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "El id debe ser una cadena de 32 caracteres hexadecimales"]);
    exit;
}

if (empty($titulo)) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "El título no puede estar vacío"]);
    exit;
}

if (empty($autor)) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "El autor no puede estar vacío"]);
    exit;
}

if (empty($descripcion)) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "La descripción no puede estar vacía"]);
    exit;
}

if (empty($genero)) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "El género no puede estar vacío"]);
    exit;
}

// Validar formato fecha_publicacion (YYYY-MM-DD)
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha_publicacion)) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Fecha de publicación no tiene formato válido (YYYY-MM-DD)"]);
    exit;
}

// Validar formato fecha_modificacion (YYYY-MM-DD HH:MM:SS)
if (!preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $fecha_modificacion)) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Fecha de modificación no tiene formato válido (YYYY-MM-DD HH:MM:SS)"]);
    exit;
}

if ($eliminado !== 0 && $eliminado !== 1) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "El campo 'eliminado' debe ser 0 o 1"]);
    exit;
}

// Validar que url_imagen y url_pdf sean URLs válidas (opcional, solo estructura)
if (!filter_var($url_imagen, FILTER_VALIDATE_URL)) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "La URL de la imagen no es válida"]);
    exit;
}

if (!filter_var($url_pdf, FILTER_VALIDATE_URL)) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "La URL del PDF no es válida"]);
    exit;
}

// Incluir archivo de conexión
require_once "conexion.php";

// -- VALIDAR DUPLICADOS (titulo + autor + fecha_publicacion) --
$dup_check_sql = "SELECT COUNT(*) FROM libros WHERE titulo = ? AND autor = ? AND fecha_publicacion = ? AND id != ?";
$stmt_dup = $mysqli->prepare($dup_check_sql);
if (!$stmt_dup) {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => "Error al preparar la consulta de duplicados",
        "error_detail" => $mysqli->error
    ]);
    exit;
}

$stmt_dup->bind_param("ssss", $titulo, $autor, $fecha_publicacion, $id);
$stmt_dup->execute();
$stmt_dup->bind_result($count);
$stmt_dup->fetch();
$stmt_dup->close();

if ($count > 0) {
    http_response_code(409); // Conflict
    echo json_encode([
        "status" => "error",
        "message" => "Ya existe un libro con el mismo título, autor y fecha de publicación"
    ]);
    exit;
}

// Consulta UPSERT: Insertar o actualizar si ya existe id
$sql = "INSERT INTO libros (
            id, titulo, autor, descripcion,
            fecha_publicacion, fecha_modificacion,
            eliminado, url_imagen, url_pdf, genero
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE
            titulo = VALUES(titulo),
            autor = VALUES(autor),
            descripcion = VALUES(descripcion),
            fecha_publicacion = VALUES(fecha_publicacion),
            fecha_modificacion = VALUES(fecha_modificacion),
            eliminado = VALUES(eliminado),
            url_imagen = VALUES(url_imagen),
            url_pdf = VALUES(url_pdf),
            genero = VALUES(genero)";

$stmt = $mysqli->prepare($sql);

if (!$stmt) {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => "Error al preparar la consulta",
        "error_detail" => $mysqli->error
    ]);
    exit;
}

$stmt->bind_param(
    "ssssssisss",
    $id,
    $titulo,
    $autor,
    $descripcion,
    $fecha_publicacion,
    $fecha_modificacion,
    $eliminado,
    $url_imagen,
    $url_pdf,
    $genero
);

if ($stmt->execute()) {
    echo json_encode([
        "status" => "success",
        "message" => "Libro insertado o actualizado correctamente"
    ]);
} else {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => "Error al insertar o actualizar el libro",
        "error_detail" => $stmt->error
    ]);
}

$stmt->close();
$mysqli->close();


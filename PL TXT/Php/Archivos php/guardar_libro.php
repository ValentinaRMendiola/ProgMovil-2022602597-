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

// Validar campos requeridos (AGREGADO: genero)
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
$genero = trim($data["genero"]); // AGREGADO

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

// Verificar si ya existe un libro con el mismo titulo, autor y fecha_publicacion
$sql_duplicate_check = "SELECT COUNT(*) FROM libros WHERE titulo = ? AND autor = ? AND fecha_publicacion = ?";
$stmt_check = $mysqli->prepare($sql_duplicate_check);
$stmt_check->bind_param("sss", $titulo, $autor, $fecha_publicacion);
$stmt_check->execute();
$stmt_check->bind_result($count);
$stmt_check->fetch();
$stmt_check->close();

if ($count > 0) {
    http_response_code(409); // Conflicto
    echo json_encode([
        "status" => "conflict",
        "message" => "Ya existe un libro con el mismo título, autor y fecha de publicación"
    ]);
    $mysqli->close();
    exit;
}

// Insertar el libro (AGREGADO: genero)
$sql = "INSERT INTO libros (
            id, titulo, autor, descripcion,
            fecha_publicacion, fecha_modificacion,
            eliminado, url_imagen, url_pdf, genero
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

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

// AGREGADO: genero
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
        "message" => "Libro insertado correctamente"
    ]);
} else {
    if ($stmt->errno === 1062) { // Error de duplicado en MySQL
        http_response_code(409);
        echo json_encode([
            "status" => "conflict",
            "message" => "Libro duplicado"
        ]);
    } else {
        http_response_code(500);
        echo json_encode([
            "status" => "error",
            "message" => "Error al insertar el libro",
            "error_detail" => $stmt->error
        ]);
    }
}

$stmt->close();
$mysqli->close();
?>

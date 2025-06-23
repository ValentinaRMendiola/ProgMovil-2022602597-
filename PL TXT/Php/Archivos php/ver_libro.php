<?php
// ver_libro.php - devuelve lista de libros en JSON para Retrofit

header('Content-Type: application/json; charset=utf-8');

$host = 'localhost';
$db   = 'mibase';
$user = 'root';
$pass = 'usbw';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);

    // Consulta con el nuevo campo genero
    $stmt = $pdo->query("SELECT 
        id AS Id,
        titulo AS Titulo,
        autor AS Autor,
        descripcion AS Descripcion,
        fecha_publicacion AS Fecha_publicacion,
        url_imagen AS UrlImagen,
        url_pdf AS UrlPdf,
        genero AS Genero
        FROM libros");

    $libros = $stmt->fetchAll();

    echo json_encode($libros, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        "error" => "Error en la conexión o consulta a la base de datos",
        "message" => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
    exit;
}
?>



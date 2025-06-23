<?php
// filtrar_libros.php - filtra libros por título, autor y género

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

    // Leer parámetros GET (si no existen, serán string vacío)
    $titulo = isset($_GET['titulo']) ? $_GET['titulo'] : '';
    $autor  = isset($_GET['autor'])  ? $_GET['autor']  : '';
    $genero = isset($_GET['genero']) ? $_GET['genero'] : '';

    // Consulta preparada con LIKE para filtros, usando COALESCE para género
    $sql = "SELECT 
                id AS Id,
                titulo AS Titulo,
                autor AS Autor,
                descripcion AS Descripcion,
                fecha_publicacion AS Fecha_publicacion,
                url_imagen AS UrlImagen,
                url_pdf AS UrlPdf,
                genero AS Genero
            FROM libros
            WHERE titulo LIKE :titulo
              AND autor LIKE :autor
              AND COALESCE(genero, '') LIKE :genero";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':titulo' => "%$titulo%",
        ':autor'  => "%$autor%",
        ':genero' => "%$genero%"
    ]);

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


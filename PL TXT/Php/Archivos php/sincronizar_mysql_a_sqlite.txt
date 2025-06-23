<?php
// Config MySQL
$mysql_host = 'localhost';
$mysql_user = 'root';
$mysql_pass = 'usbw';
$mysql_db   = 'mibase';

// Archivo SQLite
$sqlite_file = __DIR__ . '/mibase.db';

// Conexión MySQL
$mysqli = new mysqli($mysql_host, $mysql_user, $mysql_pass, $mysql_db);
if ($mysqli->connect_error) {
    die("Error MySQL: " . $mysqli->connect_error);
}

// Abrir SQLite
if (!class_exists('SQLite3')) {
    die("Error: SQLite3 no habilitado");
}
$sqlite = new SQLite3($sqlite_file);

// Crear tabla en SQLite si no existe (igual estructura)
$sqlite->exec("CREATE TABLE IF NOT EXISTS libros (
    id TEXT PRIMARY KEY,
    titulo TEXT,
    autor TEXT,
    descripcion TEXT,
    fecha_publicacion TEXT,
    fecha_modificacion TEXT,
    eliminado INTEGER,
    url_imagen TEXT,
    url_pdf TEXT
)");

// Leer todos los libros en MySQL
$result = $mysqli->query("SELECT * FROM libros");
if (!$result) {
    die("Error al obtener datos de MySQL: " . $mysqli->error);
}

// Preparar statement SQLite para inserción/reemplazo
$stmt = $sqlite->prepare("INSERT OR REPLACE INTO libros (
    id, titulo, autor, descripcion, fecha_publicacion,
    fecha_modificacion, eliminado, url_imagen, url_pdf
) VALUES (:id, :titulo, :autor, :descripcion, :fecha_publicacion,
    :fecha_modificacion, :eliminado, :url_imagen, :url_pdf)");

while ($row = $result->fetch_assoc()) {
    $stmt->bindValue(':id', $row['id']);
    $stmt->bindValue(':titulo', $row['titulo']);
    $stmt->bindValue(':autor', $row['autor']);
    $stmt->bindValue(':descripcion', $row['descripcion']);
    $stmt->bindValue(':fecha_publicacion', $row['fecha_publicacion']);
    $stmt->bindValue(':fecha_modificacion', $row['fecha_modificacion']);
    $stmt->bindValue(':eliminado', $row['eliminado'], SQLITE3_INTEGER);
    $stmt->bindValue(':url_imagen', $row['url_imagen']);
    $stmt->bindValue(':url_pdf', $row['url_pdf']);
    $stmt->execute();
}

echo "Sincronización completada desde MySQL a SQLite.";

$mysqli->close();
$sqlite->close();


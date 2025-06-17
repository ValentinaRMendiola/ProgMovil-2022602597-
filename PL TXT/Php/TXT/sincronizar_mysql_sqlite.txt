<?php
// Mostrar errores
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Conexión a MySQL
$mysqli = new mysqli("localhost", "root", "usbw", "mibase");
if ($mysqli->connect_error) {
    die("Error MySQL: " . $mysqli->connect_error);
}

// Conexión a SQLite
$sqlite = new SQLite3('mibase.db');

// Crear tabla en SQLite si no existe
$sqlite->exec("
    CREATE TABLE IF NOT EXISTS libros (
        id TEXT PRIMARY KEY,
        titulo TEXT,
        autor TEXT,
        descripcion TEXT,
        fecha_publicacion TEXT,
        fecha_modificacion TEXT,
        eliminado INTEGER,
        url_imagen TEXT,
        url_pdf TEXT
    )
");

// Obtener registros desde MySQL
$result = $mysqli->query("SELECT * FROM libros");

while ($row = $result->fetch_assoc()) {
    $stmt = $sqlite->prepare("
        INSERT OR REPLACE INTO libros (
            id, titulo, autor, descripcion,
            fecha_publicacion, fecha_modificacion,
            eliminado, url_imagen, url_pdf
        ) VALUES (
            :id, :titulo, :autor, :descripcion,
            :fecha_publicacion, :fecha_modificacion,
            :eliminado, :url_imagen, :url_pdf
        )
    ");
    foreach ($row as $key => $value) {
        $stmt->bindValue(":$key", $value, SQLITE3_TEXT);
    }
    $stmt->execute();
}

echo "Sincronización completada.";
?>


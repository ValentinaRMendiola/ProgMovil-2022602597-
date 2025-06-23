<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Configuración MySQL
$mysqli = new mysqli("localhost", "root", "usbw", "mibase");
if ($mysqli->connect_error) {
    die("Error conexión MySQL: " . $mysqli->connect_error);
}

// Cambia aquí la ruta y nombre correcto de tu archivo SQLite
$sqlite_file = 'mibase.db'; // o 'mibase' si es sin extensión

if (!file_exists($sqlite_file)) {
    die("Archivo SQLite no encontrado: $sqlite_file");
}

$sqlite = new SQLite3($sqlite_file);
if (!$sqlite) {
    die("No se pudo abrir la base SQLite");
}

// Comprobar si tabla existe en SQLite
$tableCheck = $sqlite->query("SELECT name FROM sqlite_master WHERE type='table' AND name='libros'");
if (!$tableCheck || !$tableCheck->fetchArray()) {
    die("La tabla 'libros' no existe en la base SQLite.");
}

// Consultar MySQL
$resultMySQL = $mysqli->query("SELECT * FROM libros");
if (!$resultMySQL) {
    die("Error en consulta MySQL: " . $mysqli->error);
}

// Consultar SQLite
$resultSQLite = $sqlite->query("SELECT * FROM libros");
if (!$resultSQLite) {
    die("Error en consulta SQLite: " . $sqlite->lastErrorMsg());
}

echo "<h1>Registros en MySQL</h1>";
echo "<table border='1'><tr><th>id</th><th>titulo</th></tr>";
while ($row = $resultMySQL->fetch_assoc()) {
    echo "<tr><td>" . htmlspecialchars($row['id']) . "</td><td>" . htmlspecialchars($row['titulo']) . "</td></tr>";
}
echo "</table>";

echo "<h1>Registros en SQLite</h1>";
echo "<table border='1'><tr><th>id</th><th>titulo</th></tr>";
while ($row = $resultSQLite->fetchArray(SQLITE3_ASSOC)) {
    echo "<tr><td>" . htmlspecialchars($row['id']) . "</td><td>" . htmlspecialchars($row['titulo']) . "</td></tr>";
}
echo "</table>";

$mysqli->close();
$sqlite->close();
?>


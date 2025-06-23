<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Configuración MySQL
$mysqli = new mysqli("localhost", "root", "usbw", "mibase");
if ($mysqli->connect_errno) {
    die("Error de conexión MySQL: " . $mysqli->connect_error);
}

// Configuración SQLite
$sqlite = new SQLite3("mibase.db");

// Leer todos los registros de MySQL en un arreglo asociativo id => datos
$result_mysql = $mysqli->query("SELECT * FROM libros");
$mysql_data = [];
while ($row = $result_mysql->fetch_assoc()) {
    $mysql_data[$row['id']] = $row;
}

// Leer todos los registros de SQLite en un arreglo asociativo id => datos
$result_sqlite = $sqlite->query("SELECT * FROM libros");
$sqlite_data = [];
while ($row = $result_sqlite->fetchArray(SQLITE3_ASSOC)) {
    $sqlite_data[$row['id']] = $row;
}

// Comparar registros
$only_in_mysql = [];
$only_in_sqlite = [];
$different_records = [];

foreach ($mysql_data as $id => $mysql_row) {
    if (!isset($sqlite_data[$id])) {
        // No existe en SQLite
        $only_in_mysql[$id] = $mysql_row;
    } else {
        // Existe en ambas, comparar campos
        $sqlite_row = $sqlite_data[$id];
        foreach ($mysql_row as $key => $value) {
            if (!array_key_exists($key, $sqlite_row) || $sqlite_row[$key] != $value) {
                $different_records[$id] = [
                    "mysql" => $mysql_row,
                    "sqlite" => $sqlite_row
                ];
                break;
            }
        }
        // Ya comparado, remover para no duplicar en la siguiente búsqueda
        unset($sqlite_data[$id]);
    }
}

// Los que quedan en $sqlite_data no están en MySQL
$only_in_sqlite = $sqlite_data;

// Mostrar resultados
echo "<h2>Registros sólo en MySQL:</h2>";
if (empty($only_in_mysql)) {
    echo "<p>Ninguno.</p>";
} else {
    foreach ($only_in_mysql as $id => $row) {
        echo "<p>ID: $id - Título: " . htmlspecialchars($row['titulo']) . "</p>";
    }
}

echo "<h2>Registros sólo en SQLite:</h2>";
if (empty($only_in_sqlite)) {
    echo "<p>Ninguno.</p>";
} else {
    foreach ($only_in_sqlite as $id => $row) {
        echo "<p>ID: $id - Título: " . htmlspecialchars($row['titulo']) . "</p>";
    }
}

echo "<h2>Registros con diferencias:</h2>";
if (empty($different_records)) {
    echo "<p>Ninguno.</p>";
} else {
    foreach ($different_records as $id => $diff) {
        echo "<h3>ID: $id</h3>";
        echo "<strong>MySQL:</strong><br>";
        foreach ($diff['mysql'] as $k => $v) {
            echo htmlspecialchars("$k: $v") . "<br>";
        }
        echo "<strong>SQLite:</strong><br>";
        foreach ($diff['sqlite'] as $k => $v) {
            echo htmlspecialchars("$k: $v") . "<br>";
        }
        echo "<hr>";
    }
}

$mysqli->close();
$sqlite->close();
?>

<?php
// Configuración conexión MySQL
$mysql_host = 'localhost';
$mysql_user = 'root';
$mysql_pass = 'usbw';
$mysql_db = 'mibase';

// Archivo SQLite
$sqlite_file = __DIR__ . '/mibase.db';  // Ajusta si tienes otra ruta o nombre

// Abrir conexión MySQL
$mysqli = new mysqli($mysql_host, $mysql_user, $mysql_pass, $mysql_db);
if ($mysqli->connect_error) {
    die("Error conexión MySQL: " . $mysqli->connect_error);
}

// Abrir conexión SQLite
if (!class_exists('SQLite3')) {
    die("Error: SQLite3 no está habilitado en PHP.");
}
$sqlite = new SQLite3($sqlite_file);

// Leer datos MySQL
$resMySQL = $mysqli->query("SELECT * FROM libros");
$mysqlData = [];
while ($row = $resMySQL->fetch_assoc()) {
    $mysqlData[$row['id']] = $row;
}

// Leer datos SQLite
$resSQLite = $sqlite->query("SELECT * FROM libros");
$sqliteData = [];
while ($row = $resSQLite->fetchArray(SQLITE3_ASSOC)) {
    $sqliteData[$row['id']] = $row;
}

// Cerrar conexiones
$mysqli->close();
$sqlite->close();

// Comparar
$soloMySQL = [];
$soloSQLite = [];
$diferencias = [];

foreach ($mysqlData as $id => $mysqlRow) {
    if (!isset($sqliteData[$id])) {
        $soloMySQL[$id] = $mysqlRow;
    } else {
        // Existe en ambos, revisar diferencias campo a campo
        $sqliteRow = $sqliteData[$id];
        $diffCampos = [];
        foreach ($mysqlRow as $campo => $valorMySQL) {
            // Normalizar valores para comparación (trim y lowercase para cadenas)
            $valMy = is_string($valorMySQL) ? trim(strtolower($valorMySQL)) : $valorMySQL;
            $valSq = isset($sqliteRow[$campo]) ? (is_string($sqliteRow[$campo]) ? trim(strtolower($sqliteRow[$campo])) : $sqliteRow[$campo]) : null;
            if ($valMy !== $valSq) {
                $diffCampos[$campo] = [
                    'mysql' => $valorMySQL,
                    'sqlite' => $sqliteRow[$campo] ?? null
                ];
            }
        }
        if (!empty($diffCampos)) {
            $diferencias[$id] = $diffCampos;
        }
        // Quitar del listado SQLite para encontrar los soloSQLite
        unset($sqliteData[$id]);
    }
}

// Lo que queda en $sqliteData son solo en SQLite
$soloSQLite = $sqliteData;

// Función para imprimir tabla
function imprimirTabla($titulo, $datos, $campos = ['id', 'titulo']) {
    echo "<h2>$titulo</h2>";
    if (empty($datos)) {
        echo "<p>Ninguno.</p>";
        return;
    }
    echo "<table border='1' cellpadding='5' cellspacing='0'>";
    echo "<tr>";
    foreach ($campos as $c) {
        echo "<th>" . htmlspecialchars($c) . "</th>";
    }
    echo "</tr>";
    foreach ($datos as $fila) {
        echo "<tr>";
        foreach ($campos as $c) {
            echo "<td>" . htmlspecialchars($fila[$c] ?? '') . "</td>";
        }
        echo "</tr>";
    }
    echo "</table>";
}

// Función para imprimir diferencias detalladas
function imprimirDiferencias($diferencias) {
    echo "<h2>Registros con diferencias</h2>";
    if (empty($diferencias)) {
        echo "<p>Ninguno.</p>";
        return;
    }
    foreach ($diferencias as $id => $camposDiff) {
        echo "<h3>ID: " . htmlspecialchars($id) . "</h3>";
        echo "<table border='1' cellpadding='5' cellspacing='0'>";
        echo "<tr><th>Campo</th><th>MySQL</th><th>SQLite</th></tr>";
        foreach ($camposDiff as $campo => $valores) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($campo) . "</td>";
            echo "<td>" . htmlspecialchars($valores['mysql']) . "</td>";
            echo "<td>" . htmlspecialchars($valores['sqlite']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <title>Verificación Sincronización MySQL - SQLite</title>
</head>
<body>
    <h1>Verificación Sincronización MySQL - SQLite</h1>

    <?php
    imprimirTabla("Registros sólo en MySQL", $soloMySQL);
    imprimirTabla("Registros sólo en SQLite", $soloSQLite);
    imprimirDiferencias($diferencias);
    ?>
</body>
</html>

<?php
// Abrir base SQLite
$db = new SQLite3('mibase.db');

// Consulta para obtener datos
$results = $db->query('SELECT * FROM libros');

echo "<h2>Datos en SQLite</h2>";
echo "<table border='1' cellpadding='5'><tr>
<th>id</th><th>titulo</th><th>autor</th><th>descripcion</th><th>fecha_publicacion</th>
<th>fecha_modificacion</th><th>eliminado</th><th>url_imagen</th><th>url_pdf</th>
</tr>";

while ($row = $results->fetchArray(SQLITE3_ASSOC)) {
    echo "<tr>";
    foreach ($row as $col) {
        echo "<td>" . htmlspecialchars($col) . "</td>";
    }
    echo "</tr>";
}
echo "</table>";
?>

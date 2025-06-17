<?php
// eliminar_libro.php
header('Content-Type: application/json; charset=utf-8');

$host = 'localhost';
$db = 'mibase';
$user = 'root';
$pass = 'usbw';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

try {
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    // Obtener el ID por query string (?id=)
    if (!isset($_GET['id']) || empty($_GET['id'])) {
        echo json_encode(['success' => false, 'error' => 'ID no proporcionado']);
        exit;
    }

    $id = $_GET['id'];

    // Eliminar de MySQL
    $stmt = $pdo->prepare("DELETE FROM libros WHERE id = ?");
    $stmt->execute([$id]);

    if ($stmt->rowCount() > 0) {
        // También eliminar de SQLite local
        $sqlite_file = __DIR__ . '/mibase.db';
        if (class_exists('SQLite3')) {
            $sqlite = new SQLite3($sqlite_file);
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

            $stmtSqlite = $sqlite->prepare("DELETE FROM libros WHERE id = :id");
            $stmtSqlite->bindValue(':id', $id);
            $stmtSqlite->execute();
            $sqlite->close();
        }

        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'No se encontró el libro con ese ID']);
    }

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>

<?php
// modificar_libro.php
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

    $rawInput = file_get_contents("php://input");
    $input = json_decode($rawInput, true);

    if ($input === null) {
        echo json_encode([
            'success' => false,
            'error' => 'JSON inválido o no recibido',
            'raw_input' => $rawInput
        ]);
        exit;
    }

    if (!isset($input['Id'])) {
        echo json_encode(['success' => false, 'error' => 'ID no proporcionado']);
        exit;
    }

    // Preparar y ejecutar la consulta UPDATE incluyendo genero
    $stmt = $pdo->prepare("UPDATE libros SET Titulo = ?, Autor = ?, Descripcion = ?, Fecha_publicacion = ?, genero = ? WHERE id = ?");
    $stmt->execute([
        $input['Titulo'] ?? '',
        $input['Autor'] ?? '',
        $input['Descripcion'] ?? '',
        $input['Fecha_publicacion'] ?? null,
        $input['Genero'] ?? '',
        $input['Id']
    ]);

    echo json_encode(['success' => true]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>

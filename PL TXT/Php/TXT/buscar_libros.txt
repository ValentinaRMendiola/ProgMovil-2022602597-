<?php
// leer POST JSON
$data = json_decode(file_get_contents('php://input'), true);
$titulo = $data['titulo'] ?? '';
$autor = $data['autor'] ?? '';
$genero = $data['genero'] ?? '';

// conectar a DB y hacer SELECT con LIKE en esos campos
// Ejemplo usando PDO y filtrando solo los que no estén eliminados

$stmt = $pdo->prepare("SELECT * FROM libros WHERE eliminado = 0 
    AND titulo LIKE :titulo 
    AND autor LIKE :autor 
    AND genero LIKE :genero");

$stmt->execute([
    ':titulo' => "%$titulo%",
    ':autor' => "%$autor%",
    ':genero' => "%$genero%"
]);

$resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo json_encode($resultados);
?>

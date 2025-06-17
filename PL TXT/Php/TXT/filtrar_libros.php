<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");

$servername = "localhost";
$username = "root";
$password = "usbw";  // o el que uses
$dbname = "mibase";  // asegúrate que este sea el nombre correcto

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(["error" => "Conexión fallida: " . $conn->connect_error]);
    exit();
}

$input = json_decode(file_get_contents("php://input"), true);

$titulo = isset($input["titulo"]) ? $conn->real_escape_string($input["titulo"]) : "";
$autor = isset($input["autor"]) ? $conn->real_escape_string($input["autor"]) : "";
$genero = isset($input["genero"]) ? $conn->real_escape_string($input["genero"]) : "";

// Construir consulta con filtros opcionales
$query = "SELECT * FROM libros WHERE eliminado = 0";
if (!empty($titulo)) {
    $query .= " AND titulo LIKE '%$titulo%'";
}
if (!empty($autor)) {
    $query .= " AND autor LIKE '%$autor%'";
}
if (!empty($genero)) {
    $query .= " AND genero LIKE '%$genero%'";
}

$result = $conn->query($query);

$libros = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $libros[] = [
            "id" => $row["id"],
            "titulo" => $row["titulo"],
            "autor" => $row["autor"],
            "descripcion" => $row["descripcion"],
            "fecha_publicacion" => $row["fecha_publicacion"],
            "genero" => $row["genero"],
            // Puedes agregar más campos si los necesitas
        ];
    }
}

echo json_encode($libros);
$conn->close();
?>

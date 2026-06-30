<?php ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once 'conexion.php';

$metodo = $_SERVER['REQUEST_METHOD'];

if ($metodo === 'GET') {
    $usuarioId = isset($_GET['usuario_id']) ? intval($_GET['usuario_id']) : 0;
    
    $consulta = $conexion->prepare("SELECT planta_id FROM favoritos WHERE usuario_id = ?");
    $consulta->bind_param("i", $usuarioId);
    $consulta->execute();
    $resultado = $consulta->get_result();
    $favoritos = [];
    while ($fila = $resultado->fetch_assoc()) {
        $favoritos[] = intval($fila['planta_id']);
    }
    echo json_encode(["exito" => true, "favoritos" => $favoritos]);
}

if ($metodo === 'POST') {
    $datos = json_decode(file_get_contents("php://input"), true);
$usuarioId = isset($datos['usuario_id']) ? intval($datos['usuario_id']) : 0;
$plantaId  = isset($datos['planta_id'])  ? intval($datos['planta_id'])  : 0;

    if (!$usuarioId || !$plantaId) {
        echo json_encode(["exito" => false, "mensaje" => "Faltan datos"]);
        exit();
    }

    $existe = $conexion->prepare("SELECT id FROM favoritos WHERE usuario_id = ? AND planta_id = ?");
    $existe->bind_param("ii", $usuarioId, $plantaId);
    $existe->execute();
    $existe->store_result();

    if ($existe->num_rows > 0) {
        $del = $conexion->prepare("DELETE FROM favoritos WHERE usuario_id = ? AND planta_id = ?");
        $del->bind_param("ii", $usuarioId, $plantaId);
        $del->execute();
        echo json_encode(["exito" => true, "accion" => "eliminado"]);
    } else {
        $ins = $conexion->prepare("INSERT INTO favoritos (usuario_id, planta_id) VALUES (?, ?)");

if (!$ins) {
    die($conexion->error);
}

$ins->bind_param("ii", $usuarioId, $plantaId);

if (!$ins->execute()) {
    die($ins->error);
}

echo json_encode(["exito" => true, "accion" => "agregado"]);
    }
}

$conexion->close();
?>

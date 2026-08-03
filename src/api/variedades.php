<?php
require_once 'conexion.php';

$plantaId = isset($_GET['planta_id']) ? intval($_GET['planta_id']) : 0;

if (!$plantaId) {
    echo json_encode(["exito" => false, "mensaje" => "Falta planta_id"]);
    exit();
}

$consulta = $conexion->prepare(
    "SELECT * FROM variedades WHERE planta_id = ? ORDER BY nombre ASC"
);
$consulta->bind_param("i", $plantaId);
$consulta->execute();
$resultado = $consulta->get_result();

$variedades = [];
while ($fila = $resultado->fetch_assoc()) {
    $fila['id']        = intval($fila['id']);
    $fila['planta_id'] = intval($fila['planta_id']);
    $fila['dias_riego'] = intval($fila['dias_riego']);
    $variedades[]      = $fila;
}

echo json_encode(["exito" => true, "variedades" => $variedades]);
$conexion->close();
?>
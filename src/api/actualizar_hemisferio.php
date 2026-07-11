<?php
require_once 'conexion.php';

$input = file_get_contents("php://input");
$datos = null;
if (!empty($input)) $datos = json_decode($input, true);
if (empty($datos))  { parse_str($input, $datos); }
if (empty($datos))  $datos = $_POST;

$id = $datos['usuario_id'] ?? 0;
$hemisferio = $datos['hemisferio'] ?? '';

if (!$id || !in_array($hemisferio, ['norte', 'sur'])) {
    echo json_encode(["exito" => false]);
    exit();
}

$stmt = $conexion->prepare("UPDATE usuarios SET hemisferio = ? WHERE id = ?");
$stmt->bind_param("si", $hemisferio, $id);

if ($stmt->execute()) {
    echo json_encode(["exito" => true]);
} else {
    echo json_encode(["exito" => false]);
}

$conexion->close();
?>
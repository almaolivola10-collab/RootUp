<?php
// riegos.php — Gestionar riegos
require_once 'conexion.php';

$metodo = $_SERVER['REQUEST_METHOD'];

if ($metodo === 'GET') {
    $usuarioId = $_GET['usuario_id'] ?? 0;
    $consulta  = $conexion->prepare("SELECT planta_id, fecha_riego FROM riegos WHERE usuario_id = ? ORDER BY fecha_riego DESC");
    $consulta->bind_param("i", $usuarioId);
    $consulta->execute();
    $resultado = $consulta->get_result();
    $riegos    = [];
    while ($fila = $resultado->fetch_assoc()) $riegos[] = $fila;
    echo json_encode(["exito" => true, "riegos" => $riegos]);
}

if ($metodo === 'POST') {
    $datos      = json_decode(file_get_contents("php://input"), true);
    $usuarioId  = $datos['usuario_id']  ?? 0;
    $plantaId   = $datos['planta_id']   ?? 0;
    $fechaRiego = $datos['fecha_riego'] ?? date('Y-m-d');

    $ins = $conexion->prepare("INSERT INTO riegos (usuario_id, planta_id, fecha_riego) VALUES (?, ?, ?)");
    $ins->bind_param("iis", $usuarioId, $plantaId, $fechaRiego);

    if ($ins->execute()) {
        echo json_encode(["exito" => true, "mensaje" => "Riego registrado"]);
    } else {
        echo json_encode(["exito" => false, "mensaje" => "Error al registrar"]);
    }
}
$conexion->close();
?>

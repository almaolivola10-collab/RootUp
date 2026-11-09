<?php
// obtener_lectura.php — Última lectura del SmartPlant de un usuario
// Uso: obtener_lectura.php?usuario_id=1

require_once 'conexion.php';

$usuarioId = intval($_GET['usuario_id'] ?? 0);

if ($usuarioId <= 0) {
    echo json_encode(["exito" => false, "mensaje" => "Falta usuario_id"]);
    exit();
}

$stmt = $conexion->prepare(
    "SELECT luz, humedad, temperatura, fecha
     FROM lecturas
     WHERE usuario_id = ?
     ORDER BY fecha DESC, id DESC
     LIMIT 1"
);
$stmt->bind_param("i", $usuarioId);
$stmt->execute();
$lectura = $stmt->get_result()->fetch_assoc();   // null si no tiene lecturas
$stmt->close();

echo json_encode(["exito" => true, "lectura" => $lectura]);

$conexion->close();
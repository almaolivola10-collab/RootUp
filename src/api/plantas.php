<?php
require_once 'conexion.php';

// Traer todas las plantas
$consulta  = $conexion->query("SELECT * FROM plantas ORDER BY nombre ASC");
$plantas   = [];

while ($fila = $consulta->fetch_assoc()) {
    // Convertir tipos numéricos correctamente
    $fila['id']        = intval($fila['id']);
    $fila['dias_riego'] = intval($fila['dias_riego']);
    $fila['destacada'] = (bool) $fila['destacada'];
    $plantas[]         = $fila;
}

echo json_encode(["exito" => true, "plantas" => $plantas]);
$conexion->close();
?>
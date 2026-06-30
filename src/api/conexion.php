<?php
// conexion.php — Conecta con MySQL
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

$conexion = new mysqli(
    "sql300.infinityfree.com",
    "if0_42285904",
    "RootUp2026",
    "if0_42285904_rootup"
);

if ($conexion->connect_error) {
    echo json_encode(["exito" => false, "mensaje" => "Error de conexión: " . $conexion->connect_error]);
    exit();
}

$conexion->set_charset("utf8mb4");
?>
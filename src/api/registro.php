<?php
// registro.php — Registrar usuario nuevo
require_once 'conexion.php';

$datos    = json_decode(file_get_contents("php://input"), true);
$nombre   = $datos['nombre']   ?? '';
$email    = $datos['email']    ?? '';
$password = $datos['password'] ?? '';

if (empty($nombre) || empty($email) || empty($password)) {
    echo json_encode(["exito" => false, "mensaje" => "Todos los campos son obligatorios"]);
    exit();
}

// Verificar si el email ya existe
$existe = $conexion->prepare("SELECT id FROM usuarios WHERE email = ?");
$existe->bind_param("s", $email);
$existe->execute();
$existe->store_result();

if ($existe->num_rows > 0) {
    echo json_encode(["exito" => false, "mensaje" => "Ya existe una cuenta con ese email"]);
    exit();
}

// Encriptar contraseña y guardar usuario
$pass = password_hash($password, PASSWORD_DEFAULT);
$ins  = $conexion->prepare("INSERT INTO usuarios (nombre, email, password) VALUES (?, ?, ?)");
$ins->bind_param("sss", $nombre, $email, $pass);

if ($ins->execute()) {
    echo json_encode([
        "exito"   => true,
        "mensaje" => "Usuario registrado correctamente",
        "usuario" => ["id" => $conexion->insert_id, "nombre" => $nombre, "email" => $email]
    ]);
} else {
    echo json_encode(["exito" => false, "mensaje" => "Error al registrar"]);
}
$conexion->close();
?>

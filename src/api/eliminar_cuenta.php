<?php
// eliminar_cuenta.php — Elimina la cuenta del usuario y todos sus datos personales
require_once 'conexion.php';

$metodo = $_SERVER['REQUEST_METHOD'];

if ($metodo !== 'POST') {
    echo json_encode(["exito" => false, "mensaje" => "Método no permitido"]);
    exit();
}

$datos     = json_decode(file_get_contents("php://input"), true);
$usuarioId = $datos['usuario_id'] ?? 0;
$password  = $datos['password']  ?? '';

if (empty($usuarioId) || empty($password)) {
    echo json_encode(["exito" => false, "mensaje" => "Faltan datos"]);
    exit();
}

// Verificar que la contraseña sea correcta antes de borrar nada
$consulta = $conexion->prepare("SELECT password FROM usuarios WHERE id = ?");
$consulta->bind_param("i", $usuarioId);
$consulta->execute();
$resultado = $consulta->get_result();

if ($resultado->num_rows === 0) {
    echo json_encode(["exito" => false, "mensaje" => "Usuario no encontrado"]);
    exit();
}

$usuario = $resultado->fetch_assoc();

if (!password_verify($password, $usuario['password'])) {
    echo json_encode(["exito" => false, "mensaje" => "Contraseña incorrecta"]);
    exit();
}

// Borrar explícitamente por si esta tabla no tiene ON DELETE CASCADE configurado
$delToken = $conexion->prepare("DELETE FROM recuperacion_password WHERE usuario_id = ?");
$delToken->bind_param("i", $usuarioId);
$delToken->execute();

// Borrar el usuario. favoritos, notificaciones, riegos y mis_plantas
// se borran solos por el ON DELETE CASCADE de la base de datos.
$delUsuario = $conexion->prepare("DELETE FROM usuarios WHERE id = ?");
$delUsuario->bind_param("i", $usuarioId);

if ($delUsuario->execute()) {
    echo json_encode(["exito" => true, "mensaje" => "Cuenta eliminada correctamente"]);
} else {
    echo json_encode(["exito" => false, "mensaje" => "Error al eliminar la cuenta"]);
}

$conexion->close();
?>
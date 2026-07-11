<?php
// login.php — Iniciar sesión
require_once 'conexion.php';

$input = file_get_contents("php://input");
$datos = null;
if (!empty($input)) $datos = json_decode($input, true);
if (empty($datos))  { parse_str($input, $datos); }
if (empty($datos))  $datos = $_POST;
$email    = $datos['email']    ?? '';
$password = $datos['password'] ?? '';

if (empty($email) || empty($password)) {
    echo json_encode(["exito" => false, "mensaje" => "Email y contraseña son obligatorios"]);
    exit();
}

$consulta = $conexion->prepare("SELECT id, nombre, email, password FROM usuarios WHERE email = ?");
$consulta->bind_param("s", $email);
$consulta->execute();
$resultado = $consulta->get_result();

if ($resultado->num_rows === 0) {
    echo json_encode(["exito" => false, "mensaje" => "Email o contraseña incorrectos"]);
    exit();
}

$usuario = $resultado->fetch_assoc();

if (!password_verify($password, $usuario['password'])) {
    echo json_encode(["exito" => false, "mensaje" => "Email o contraseña incorrectos"]);
    exit();
}

echo json_encode([
    "exito"   => true,
    "mensaje" => "Login exitoso",
    "usuario" => ["id" => $usuario['id'], "nombre" => $usuario['nombre'], "email" => $usuario['email']]
]);

$conexion->close();
?>

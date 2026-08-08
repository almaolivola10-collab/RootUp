<?php
require_once 'conexion.php';

$input = file_get_contents("php://input");
$datos = null;
if (!empty($input)) $datos = json_decode($input, true);
if (empty($datos))  { parse_str($input, $datos); }
if (empty($datos))  $datos = $_POST;

$accion = isset($datos['accion']) ? $datos['accion'] : '';

// ── PASO 1: Solicitar recuperación ──────────────────
if ($accion === 'solicitar') {
    $email = isset($datos['email']) ? trim($datos['email']) : '';

    if (empty($email)) {
        echo json_encode(["exito" => false, "mensaje" => "Ingresá tu email"]);
        exit();
    }

    $consulta = $conexion->prepare("SELECT id, nombre FROM usuarios WHERE email = ?");
    $consulta->bind_param("s", $email);
    $consulta->execute();
    $resultado = $consulta->get_result();

    if ($resultado->num_rows === 0) {
        echo json_encode(["exito" => false, "mensaje" => "No existe una cuenta con ese email"]);
        exit();
    }

    $usuario    = $resultado->fetch_assoc();
    $token      = bin2hex(random_bytes(32));
    $expiracion = date('Y-m-d H:i:s', strtotime('+1 hour'));

    $ins = $conexion->prepare(
        "INSERT INTO recuperacion_password (usuario_id, token, expiracion) VALUES (?, ?, ?)"
    );
    $ins->bind_param("iss", $usuario['id'], $token, $expiracion);
    $ins->execute();

    echo json_encode([
        "exito"  => true,
        "nombre" => $usuario['nombre'],
        "token"  => $token,
        "email"  => $email
    ]);
}

// ── PASO 2: Verificar token ──────────────────────────
if ($accion === 'verificar') {
    $token = isset($datos['token']) ? trim($datos['token']) : '';

    $consulta = $conexion->prepare(
        "SELECT id FROM recuperacion_password
         WHERE token = ? AND expiracion > NOW() AND usado = 0"
    );
    $consulta->bind_param("s", $token);
    $consulta->execute();
    $resultado = $consulta->get_result();

    if ($resultado->num_rows === 0) {
        echo json_encode(["exito" => false, "mensaje" => "El link expiró o ya fue usado"]);
    } else {
        echo json_encode(["exito" => true]);
    }
}

// ── PASO 3: Cambiar contraseña ───────────────────────
if ($accion === 'cambiar') {
    $token    = isset($datos['token'])    ? trim($datos['token'])    : '';
    $password = isset($datos['password']) ? trim($datos['password']) : '';

    if (empty($token) || empty($password)) {
        echo json_encode(["exito" => false, "mensaje" => "Faltan datos"]);
        exit();
    }

    if (strlen($password) < 6) {
        echo json_encode(["exito" => false, "mensaje" => "La contraseña debe tener al menos 6 caracteres"]);
        exit();
    }

    $consulta = $conexion->prepare(
        "SELECT id, usuario_id FROM recuperacion_password
         WHERE token = ? AND expiracion > NOW() AND usado = 0"
    );
    $consulta->bind_param("s", $token);
    $consulta->execute();
    $resultado = $consulta->get_result();

    if ($resultado->num_rows === 0) {
        echo json_encode(["exito" => false, "mensaje" => "El link expiró o ya fue usado"]);
        exit();
    }

    $rec  = $resultado->fetch_assoc();
    $pass = password_hash($password, PASSWORD_DEFAULT);

    $upd = $conexion->prepare("UPDATE usuarios SET password = ? WHERE id = ?");
    $upd->bind_param("si", $pass, $rec['usuario_id']);
    $upd->execute();

    $used = $conexion->prepare("UPDATE recuperacion_password SET usado = 1 WHERE id = ?");
    $used->bind_param("i", $rec['id']);
    $used->execute();

    echo json_encode(["exito" => true, "mensaje" => "Contraseña actualizada correctamente"]);
}

$conexion->close();
?>
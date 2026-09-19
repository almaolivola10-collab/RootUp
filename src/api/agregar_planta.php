<?php
// agregar_planta.php — Panel de administrador: carga manual de plantas

require_once 'conexion.php';

$body = json_decode(file_get_contents('php://input'), true);

if (!$body || empty($body['usuario_id'])) {
    echo json_encode(["exito" => false, "mensaje" => "Falta usuario_id"]);
    exit();
}

// ── Verificar que el usuario sea admin ──
$usuarioId = intval($body['usuario_id']);
$check = $conexion->prepare("SELECT es_admin FROM usuarios WHERE id = ?");
$check->bind_param("i", $usuarioId);
$check->execute();
$resultado = $check->get_result();

if ($resultado->num_rows === 0 || !$resultado->fetch_assoc()['es_admin']) {
    echo json_encode(["exito" => false, "mensaje" => "No tenés permisos de administrador"]);
    exit();
}
$check->close();

// ── Validar campos obligatorios ──
if (empty($body['nombre']) || empty($body['categoria'])) {
    echo json_encode(["exito" => false, "mensaje" => "Nombre y categoría son obligatorios"]);
    exit();
}

// ── Insertar la planta ──
$stmt = $conexion->prepare(
    "INSERT INTO plantas
        (nombre, cientifico, emoji, categoria, tags, dias_riego, luz,
         meses_siembra_sur, meses_siembra_norte, dificultad,
         descripcion, cuidados, curiosidad, imagen, destacada, advertencia)
     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
);

$nombre       = $body['nombre'];
$cientifico   = $body['cientifico']         ?? null;
$emoji        = $body['emoji']              ?? '🌱';
$categoria    = $body['categoria'];
$tags         = $body['tags']               ?? null;
$diasRiego    = isset($body['dias_riego']) && $body['dias_riego'] !== '' ? intval($body['dias_riego']) : null;
$luz          = $body['luz']                ?? null;
$mesesSur     = $body['meses_siembra_sur']  ?? null;
$mesesNorte   = $body['meses_siembra_norte'] ?? null;
$dificultad   = $body['dificultad']         ?? 'Fácil';
$descripcion  = $body['descripcion']        ?? null;
$cuidados     = $body['cuidados']           ?? null;
$curiosidad   = $body['curiosidad']         ?? null;
$imagen       = $body['imagen']             ?? null;
$destacada    = isset($body['destacada']) ? intval($body['destacada']) : 0;
$advertencia  = $body['advertencia']        ?? null;

$stmt->bind_param(
    "sssssissssssssis",
    $nombre, $cientifico, $emoji, $categoria, $tags, $diasRiego, $luz,
    $mesesSur, $mesesNorte, $dificultad, $descripcion, $cuidados,
    $curiosidad, $imagen, $destacada, $advertencia
);

if ($stmt->execute()) {
    echo json_encode(["exito" => true, "id" => $conexion->insert_id]);
} else {
    echo json_encode(["exito" => false, "mensaje" => $stmt->error]);
}

$stmt->close();
$conexion->close();
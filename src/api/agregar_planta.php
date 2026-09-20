<?php
// agregar_planta.php — Panel de administrador: carga manual de plantas
// Ahora trabaja con las tablas normalizadas: luz_necesaria, categorias +
// plantas_categorias, tags + planta_tags. Solo usuarios con es_admin = 1.

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

// ── Resolver luz_id a partir del nombre elegido en el select ──
$luzId = null;
if (!empty($body['luz'])) {
    $stmt = $conexion->prepare("SELECT id FROM luz_necesaria WHERE nombre = ?");
    $stmt->bind_param("s", $body['luz']);
    $stmt->execute();
    $fila = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if ($fila) $luzId = $fila['id'];
}

// ── Insertar la planta (ya sin categoria/tags/luz como texto) ──
$stmt = $conexion->prepare(
    "INSERT INTO plantas
        (nombre, cientifico, emoji, luz_id, dias_riego,
         meses_siembra_sur, meses_siembra_norte, dificultad,
         descripcion, cuidados, curiosidad, imagen, destacada, advertencia)
     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
);

$nombre       = $body['nombre'];
$cientifico   = $body['cientifico']          ?? null;
$emoji        = $body['emoji']               ?? '🌱';
$diasRiego    = isset($body['dias_riego']) && $body['dias_riego'] !== '' ? intval($body['dias_riego']) : null;
$mesesSur     = $body['meses_siembra_sur']   ?? null;
$mesesNorte   = $body['meses_siembra_norte'] ?? null;
$dificultad   = $body['dificultad']          ?? 'Fácil';
$descripcion  = $body['descripcion']         ?? null;
$cuidados     = $body['cuidados']            ?? null;
$curiosidad   = $body['curiosidad']          ?? null;
$imagen       = $body['imagen']              ?? null;
$destacada    = isset($body['destacada']) ? intval($body['destacada']) : 0;
$advertencia  = $body['advertencia']         ?? null;

$stmt->bind_param(
    "sssiisssssssis",
    $nombre, $cientifico, $emoji, $luzId, $diasRiego,
    $mesesSur, $mesesNorte, $dificultad, $descripcion, $cuidados,
    $curiosidad, $imagen, $destacada, $advertencia
);

if (!$stmt->execute()) {
    echo json_encode(["exito" => false, "mensaje" => $stmt->error]);
    exit();
}

$plantaId = $conexion->insert_id;
$stmt->close();

// ── Relacionar categorías (plantas_categorias) ──
// body['categoria'] llega como string separado por coma, ej: "interior,huerta"
foreach (explode(',', $body['categoria']) as $nombreCat) {
    $nombreCat = trim($nombreCat);
    if ($nombreCat === '') continue;

    $stmt = $conexion->prepare("SELECT id FROM categorias WHERE LOWER(nombre) = LOWER(?)");
    $stmt->bind_param("s", $nombreCat);
    $stmt->execute();
    $fila = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($fila) {
        $stmt = $conexion->prepare("INSERT IGNORE INTO plantas_categorias (planta_id, categoria_id) VALUES (?, ?)");
        $stmt->bind_param("ii", $plantaId, $fila['id']);
        $stmt->execute();
        $stmt->close();
    }
}

// ── Relacionar tags (planta_tags), creando el tag si no existe ──
if (!empty($body['tags'])) {
    foreach (explode(',', $body['tags']) as $nombreTag) {
        $nombreTag = trim($nombreTag);
        if ($nombreTag === '') continue;

        $stmt = $conexion->prepare("SELECT id FROM tags WHERE nombre = ?");
        $stmt->bind_param("s", $nombreTag);
        $stmt->execute();
        $fila = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($fila) {
            $tagId = $fila['id'];
        } else {
            $stmt = $conexion->prepare("INSERT INTO tags (nombre) VALUES (?)");
            $stmt->bind_param("s", $nombreTag);
            $stmt->execute();
            $tagId = $conexion->insert_id;
            $stmt->close();
        }

        $stmt = $conexion->prepare("INSERT IGNORE INTO planta_tags (planta_id, tag_id) VALUES (?, ?)");
        $stmt->bind_param("ii", $plantaId, $tagId);
        $stmt->execute();
        $stmt->close();
    }
}

echo json_encode(["exito" => true, "id" => $plantaId]);
$conexion->close();
<?php
// perfil.php — Datos de cuenta + estadísticas para la pantalla de Perfil
// Uso: perfil.php?usuario_id=1

require_once 'conexion.php';

$usuarioId = intval($_GET['usuario_id'] ?? 0);

if ($usuarioId <= 0) {
    echo json_encode(["exito" => false, "mensaje" => "Falta usuario_id"]);
    exit();
}

// ── Datos de la cuenta ──
$stmt = $conexion->prepare("SELECT nombre, email, hemisferio, fecha_registro FROM usuarios WHERE id = ?");
$stmt->bind_param("i", $usuarioId);
$stmt->execute();
$usuario = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$usuario) {
    echo json_encode(["exito" => false, "mensaje" => "Usuario no encontrado"]);
    exit();
}

// ── Contadores simples ──
function contarFilas($conexion, $tabla, $usuarioId) {
    $stmt = $conexion->prepare("SELECT COUNT(*) AS total FROM $tabla WHERE usuario_id = ?");
    $stmt->bind_param("i", $usuarioId);
    $stmt->execute();
    $total = $stmt->get_result()->fetch_assoc()['total'];
    $stmt->close();
    return intval($total);
}

$totalPlantas   = contarFilas($conexion, 'mis_plantas', $usuarioId);
$totalFavoritas = contarFilas($conexion, 'favoritos', $usuarioId);
$totalRiegos    = contarFilas($conexion, 'riegos', $usuarioId);

// ── Categoría más frecuente entre sus plantas propias ──
// ACTUALIZADO: ahora usa las tablas normalizadas (plantas_categorias + categorias)
// en vez de la columna de texto plantas.categoria, que ya no existe.
$stmt = $conexion->prepare(
    "SELECT c.nombre AS categoria
     FROM mis_plantas mp
     JOIN plantas_categorias pc ON pc.planta_id = mp.planta_id
     JOIN categorias c          ON c.id = pc.categoria_id
     WHERE mp.usuario_id = ?"
);
$stmt->bind_param("i", $usuarioId);
$stmt->execute();
$resultado = $stmt->get_result();

$conteo = [];
while ($fila = $resultado->fetch_assoc()) {
    $c = trim($fila['categoria']);
    if ($c === '') continue;
    $clave = mb_strtolower($c);
    if (!isset($conteo[$clave])) $conteo[$clave] = ['nombre' => $c, 'total' => 0];
    $conteo[$clave]['total']++;
}
$stmt->close();

$categoriaTop = null;
$max = 0;
foreach ($conteo as $c) {
    if ($c['total'] > $max) { $max = $c['total']; $categoriaTop = $c['nombre']; }
}

echo json_encode([
    "exito" => true,
    "usuario" => [
        "nombre"         => $usuario['nombre'],
        "email"          => $usuario['email'],
        "hemisferio"     => $usuario['hemisferio'],
        "fecha_registro" => $usuario['fecha_registro'],
    ],
    "stats" => [
        "total_plantas"        => $totalPlantas,
        "total_favoritas"      => $totalFavoritas,
        "total_riegos"         => $totalRiegos,
        "categorias_distintas" => count($conteo),
        "categoria_top"        => $categoriaTop,
    ]
]);

$conexion->close();
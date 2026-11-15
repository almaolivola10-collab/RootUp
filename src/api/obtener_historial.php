<?php
// obtener_historial.php — Lecturas del SmartPlant para el gráfico
// Uso: obtener_historial.php?usuario_id=1&horas=6   (horas: 1, 6 o 24)

require_once 'conexion.php';

$usuarioId = intval($_GET['usuario_id'] ?? 0);
$horas     = intval($_GET['horas'] ?? 6);

if ($usuarioId <= 0) {
    echo json_encode(["exito" => false, "mensaje" => "Falta usuario_id"]);
    exit();
}
if (!in_array($horas, [1, 6, 24])) $horas = 6;

// 1) Fecha de la última lectura de este usuario
$stmt = $conexion->prepare("SELECT MAX(fecha) AS ultima FROM lecturas WHERE usuario_id = ?");
$stmt->bind_param("i", $usuarioId);
$stmt->execute();
$ultima = $stmt->get_result()->fetch_assoc()['ultima'];
$stmt->close();

if (!$ultima) {
    echo json_encode(["exito" => true, "horas" => $horas, "puntos" => []]);
    exit();
}

// 2) La ventana se cuenta hacia atrás desde esa última lectura
$desde = date('Y-m-d H:i:s', strtotime($ultima) - $horas * 3600);

$stmt = $conexion->prepare(
    "SELECT luz, humedad, temperatura, fecha
     FROM lecturas
     WHERE usuario_id = ? AND fecha >= ?
     ORDER BY fecha ASC, id ASC"
);
$stmt->bind_param("is", $usuarioId, $desde);
$stmt->execute();
$resultado = $stmt->get_result();

$filas = [];
while ($f = $resultado->fetch_assoc()) {
    $filas[] = $f;
}
$stmt->close();

// 3) Reducimos a ~120 puntos para que el gráfico sea liviano
$total = count($filas);
$paso  = max(1, (int) ceil($total / 120));
$puntos = [];
foreach ($filas as $i => $f) {
    if ($i % $paso === 0 || $i === $total - 1) {
        $puntos[] = [
            "luz"         => floatval($f['luz']),
            "humedad"     => floatval($f['humedad']),
            "temperatura" => floatval($f['temperatura']),
            "fecha"       => $f['fecha'],
        ];
    }
}

echo json_encode(["exito" => true, "horas" => $horas, "puntos" => $puntos]);

$conexion->close();
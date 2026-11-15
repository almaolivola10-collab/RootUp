<?php
// obtener_historial.php — Lecturas del SmartPlant para el gráfico

require_once 'conexion.php';

$usuarioId = intval($_GET['usuario_id'] ?? 0);
$horas     = intval($_GET['horas'] ?? 6);

if ($usuarioId <= 0) {
    echo json_encode(["exito" => false, "mensaje" => "Falta usuario_id"]);
    exit();
}
if (!in_array($horas, [1, 6, 24])) $horas = 6;

// La ventana se cuenta desde la última lectura (no depende de la hora del servidor)
$stmt = $conexion->prepare(
    "SELECT luz, humedad, temperatura, fecha
     FROM lecturas
     WHERE usuario_id = ?
       AND fecha >= (SELECT MAX(fecha) FROM lecturas WHERE usuario_id = ?) - INTERVAL ? HOUR
     ORDER BY fecha ASC, id ASC"
);
$stmt->bind_param("iii", $usuarioId, $usuarioId, $horas);
$stmt->execute();
$filas = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Reducimos a ~120 puntos para que el gráfico sea liviano
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
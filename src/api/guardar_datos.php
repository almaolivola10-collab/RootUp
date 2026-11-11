<?php
require_once 'conexion.php';
$conexion->query("SET time_zone = '-03:00'");

if (isset($_POST['usuario_id']) && isset($_POST['luz']) && isset($_POST['humedad']) && isset($_POST['temperatura'])) {

    if (!is_numeric($_POST['usuario_id']) || !is_numeric($_POST['luz']) || !is_numeric($_POST['humedad']) || !is_numeric($_POST['temperatura'])) {
        die("Datos inválidos");
    }

    $usuarioId   = intval($_POST['usuario_id']);
    $luz         = $_POST['luz'];
    $humedad     = $_POST['humedad'];
    $temperatura = $_POST['temperatura'];

    $stmt = $conexion->prepare("INSERT INTO lecturas (usuario_id, luz, humedad, temperatura, fecha) VALUES (?, ?, ?, ?, NOW())");
    $stmt->bind_param("iddd", $usuarioId, $luz, $humedad, $temperatura);

    if ($stmt->execute()) {
        echo "Nuevo registro guardado con éxito";
    } else {
        echo "Error al guardar";
    }
    $stmt->close();
} else {
    echo "Faltan datos por enviar.";
}

$conexion->close();
?>
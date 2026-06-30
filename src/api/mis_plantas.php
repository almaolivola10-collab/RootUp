<?php
// mis_plantas.php — Gestionar plantas propias del usuario
require_once 'conexion.php';

$metodo = $_SERVER['REQUEST_METHOD'];

// ── TRAER PLANTAS DEL USUARIO ────────────────────────
if ($metodo === 'GET') {
    $usuarioId = $_GET['usuario_id'] ?? 0;

    $consulta = $conexion->prepare(
        "SELECT id, planta_id, nombre_personalizado, ultimo_riego, notas
         FROM mis_plantas
         WHERE usuario_id = ?
         ORDER BY id DESC"
    );
    $consulta->bind_param("i", $usuarioId);
    $consulta->execute();
    $resultado = $consulta->get_result();

    $plantas = [];
    while ($fila = $resultado->fetch_assoc()) {
        $plantas[] = $fila;
    }

    echo json_encode(["exito" => true, "plantas" => $plantas]);
}

// ── AGREGAR O ELIMINAR PLANTA ────────────────────────
if ($metodo === 'POST') {
    $datos  = json_decode(file_get_contents("php://input"), true);
    $accion = $datos['accion'] ?? '';

    // Agregar planta
    if ($accion === 'agregar') {
        $usuarioId          = $datos['usuario_id']          ?? 0;
        $plantaId           = $datos['planta_id']           ?? 0;
        $nombrePersonalizado = $datos['nombre_personalizado'] ?? '';
        $ultimoRiego        = $datos['ultimo_riego']        ?? date('Y-m-d');
        $notas              = $datos['notas']               ?? '';

        $ins = $conexion->prepare(
            "INSERT INTO mis_plantas (usuario_id, planta_id, nombre_personalizado, ultimo_riego, notas)
             VALUES (?, ?, ?, ?, ?)"
        );
        $ins->bind_param("iisss", $usuarioId, $plantaId, $nombrePersonalizado, $ultimoRiego, $notas);

        if ($ins->execute()) {
            echo json_encode(["exito" => true, "id" => $conexion->insert_id]);
        } else {
            echo json_encode(["exito" => false, "mensaje" => "Error al guardar"]);
        }
    }

    // Eliminar planta
    if ($accion === 'eliminar') {
        $id  = $datos['id'] ?? 0;
        $del = $conexion->prepare("DELETE FROM mis_plantas WHERE id = ?");
        $del->bind_param("i", $id);

        if ($del->execute()) {
            echo json_encode(["exito" => true]);
        } else {
            echo json_encode(["exito" => false, "mensaje" => "Error al eliminar"]);
        }
    }

    // Actualizar ultimo riego
    if ($accion === 'actualizar_riego') {
        $id          = $datos['id']          ?? 0;
        $ultimoRiego = $datos['ultimo_riego'] ?? date('Y-m-d');

        $upd = $conexion->prepare("UPDATE mis_plantas SET ultimo_riego = ? WHERE id = ?");
        $upd->bind_param("si", $ultimoRiego, $id);

        if ($upd->execute()) {
            echo json_encode(["exito" => true]);
        } else {
            echo json_encode(["exito" => false, "mensaje" => "Error al actualizar"]);
        }
    }
}

$conexion->close();
?>

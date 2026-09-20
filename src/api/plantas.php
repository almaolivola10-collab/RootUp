<?php
require_once 'conexion.php';
$sql = "
    SELECT
        p.id, p.nombre, p.cientifico, p.emoji, p.dias_riego,
        p.meses_siembra_sur, p.meses_siembra_norte, p.dificultad,
        p.descripcion, p.cuidados, p.curiosidad, p.imagen,
        p.destacada, p.fecha_creacion, p.advertencia,
        GROUP_CONCAT(DISTINCT c.nombre ORDER BY c.nombre SEPARATOR ',') AS categoria,
        GROUP_CONCAT(DISTINCT t.nombre ORDER BY t.nombre SEPARATOR ',') AS tags,
        l.nombre AS luz
    FROM plantas p
    LEFT JOIN plantas_categorias pc ON pc.planta_id = p.id
    LEFT JOIN categorias c          ON c.id = pc.categoria_id
    LEFT JOIN planta_tags pt        ON pt.planta_id = p.id
    LEFT JOIN tags t                ON t.id = pt.tag_id
    LEFT JOIN luz_necesaria l       ON l.id = p.luz_id
    GROUP BY p.id
    ORDER BY p.nombre ASC
";

$consulta = $conexion->query($sql);
$plantas  = [];

while ($fila = $consulta->fetch_assoc()) {
    $fila['id']         = intval($fila['id']);
    $fila['dias_riego'] = intval($fila['dias_riego']);
    $fila['destacada']  = (bool) $fila['destacada'];
    $plantas[]          = $fila;
}

echo json_encode(["exito" => true, "plantas" => $plantas]);
$conexion->close();
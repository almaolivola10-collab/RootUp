<?php
// Configuración de la base de datos en InfinityFree
$servername = "sql300.infinityfree.com";
$username   = "if0_42285904";
$password   = "RootUp2026";
$dbname     = "if0_42285904_rootup";

// Crear conexión
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar conexión
if ($conn->connect_error) {
    die("Conexión fallida");
}

// Verificar que lleguen los tres datos por POST
if (isset($_POST['luz']) && isset($_POST['humedad']) && isset($_POST['temperatura'])) {

    // Validar que sean números
    if (!is_numeric($_POST['luz']) || !is_numeric($_POST['humedad']) || !is_numeric($_POST['temperatura'])) {
        die("Datos inválidos");
    }

    $luz         = $_POST['luz'];
    $humedad     = $_POST['humedad'];
    $temperatura = $_POST['temperatura'];

    // Consulta preparada: los ? se rellenan de forma segura
    $stmt = $conn->prepare("INSERT INTO lecturas (luz, humedad, temperatura, fecha) VALUES (?, ?, ?, NOW())");
    $stmt->bind_param("ddd", $luz, $humedad, $temperatura);

    if ($stmt->execute()) {
        echo "Nuevo registro guardado con éxito";
    } else {
        echo "Error al guardar";
    }

    $stmt->close();
} else {
    echo "Faltan datos por enviar.";
}

$conn->close();
?>
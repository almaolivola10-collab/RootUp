<?php
// Configuración de tu base de datos en InfinityFree
$servername = "sql300.infinityfree.com"; 
$username = "if0_42285904";          
$password = "RootUp2026";       
$dbname = "if0_42285904_rootup";         

// Crear conexión
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Verificar si se recibieron los datos por POST
if (isset($_POST['luz']) && isset($_POST['humedad']) && isset($_POST['temperatura'])) {
    $luz = $_POST['luz'];
    $humedad = $_POST['humedad'];
    $temperatura = $_POST['temperatura'];

    // Insertar en tu tabla SQL (asegúrate de que tu tabla tenga columnas: luz, humedad, temperatura, fecha)
    $sql = "INSERT INTO lecturas (luz, humedad, temperatura, fecha) VALUES ('$luz', '$humedad', '$temperatura', NOW())";

    if ($conn->query($sql) === TRUE) {
        echo "Nuevo registro guardado con éxito";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
} else {
    echo "Faltan datos por enviar.";
}

$conn->close();
?>
<?php
session_start();

// Incluir el autoloader de Composer
require 'vendor/autoload.php';

// Importar la clase phpMQTT del espacio de nombres Bluerhinos
use Bluerhinos\phpMQTT;

$server = 'broker.hivemq.com'; // Broker MQTT público de HiveMQ
$port = 1883; // Puerto TCP
$client_id = 'PHP_LockMyLock_' . uniqid(); // ID del cliente único para la conexión MQTT

$mqtt = new phpMQTT($server, $port, $client_id);

if (!$mqtt->connect(true, NULL, $username, $password)) {
    echo "No se pudo conectar al servidor MQTT\n";
    exit(1);
}

echo "Conectado al servidor MQTT\n";

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['user_id'])) {
    // Si no ha iniciado sesión, redirigir al usuario al formulario de inicio de sesión
    header("Location: login.php");
    exit;
}

// Establecer la conexión con la base de datos
$servername = "localhost"; // Cambiar si es necesario
$username = "root"; // Cambiar al nombre de usuario de la base de datos
$password = ""; // Cambiar a la contraseña de la base de datos
$dbname = "lockappdb";

$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar la conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Obtener el UserID del usuario actual desde la sesión
$userID = $_SESSION['user_id'];

// Obtener los datos del formulario
$currentPIN = $_POST['CurrentPIN']; // Recuperar el valor del PIN actual del formulario
$newPIN = $_POST['NewPIN']; // Recuperar el nuevo PIN del formulario

// Verificar la contraseña actual
$sql = "SELECT LockKey FROM locks WHERE UserID = $userID"; // Suponiendo que la tabla de cerraduras tiene un campo UserID para asociarla con el usuario
$result = $conn->query($sql);

// Verificar la longitud y el formato del nuevo PIN
if (strlen($newPIN) != 6 || !ctype_digit($newPIN)) {
    echo "<script>";
    echo "alert('El PIN debe tener exactamente 6 dígitos y contener solo números.');";
    echo "window.location.href = '../HTML/perfil.php';";
    echo "</script>";
    exit;
}

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $actualLockKey = $row["LockKey"];
    if ($actualLockKey != $currentPIN) {
        echo "<script>";
        echo 'alert("Contraseña Actual Incorrecta.");';
        echo 'window.location.href = "../HTML/perfil.php";'; 
        echo "</script>";
        exit;
    }
} else {
    echo "No se encontró la cerradura asociada al usuario.";
    exit;
}

// Verificar si se proporcionó un nuevo PIN y actualizar la cerradura
if (!empty($newPIN)) {
    $sql = "UPDATE locks SET LockKey = '$newPIN' WHERE UserID = $userID";

    if ($conn->query($sql) === true) {
        $mqtt->publish('esp32/pin', $newPIN, 0);
        echo "<script>";
        echo 'alert("Datos de Cerradura Actualizados Correctamente");';
        echo 'window.location.href = "../HTML/perfil.php";'; 
        echo "</script>";
    } else {
        echo "Error al actualizar la cerradura: " . $conn->error;
    }
} else {
    echo "No se proporcionó un nuevo PIN.";
}

$conn->close();
$mqtt->close();
?>

<?php

// Incluir el autoloader de Composer
require 'vendor/autoload.php';

// Importar la clase phpMQTT del espacio de nombres Bluerhinos
use Bluerhinos\phpMQTT;

$server = 'broker.hivemq.com'; // Broker MQTT público de HiveMQ
$port = 1883; // Puerto TCP
$client_id = 'PHP_LockMyLock_' . uniqid(); // ID del cliente único para la conexión MQTT

$mqtt = new phpMQTT($server, $port, $client_id);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Recibir datos del formulario
    $SSDI = htmlspecialchars($_POST['ssdi']);
    $PasswordWifi = htmlspecialchars($_POST['claveWifi']);
    $message = $SSDI . ":" . $PasswordWifi;

    // Validar los datos
    if (empty($SSDI) || empty($PasswordWifi)) {
        echo "<script>alert('Todos los campos son obligatorios.'); window.history.back();</script>";
    } else {
        // Verificar si la conexión MQTT se estableció correctamente
        if ($mqtt->connect(true, NULL, NULL, NULL, NULL)) {
            // Generar el código JavaScript para mostrar un alert con los datos recibidos
            echo "<script>alert('Los datos se enviaron correctamente.'); </script>";
            // Publicar el mensaje
            $mqtt->publish('esp32/network', $message, 0);
            // Cerrar la conexión
            $mqtt->close();
        } else {
            // Manejar el caso en que no se puede conectar al servidor MQTT
            echo "<script>alert('Error: No se pudo conectar al servidor MQTT.'); window.history.back();</script>";
        }
    }
} else {
    echo "<script>alert('Método de solicitud no permitido.'); window.history.back();</script>";
}

?>

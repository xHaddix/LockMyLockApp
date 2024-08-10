<?php

require('phpMQTT.php'); // Asegúrate de que la ruta es correcta

$server = 'broker.hivemq.com'; // Broker MQTT público de HiveMQ
$port = 1883; // Puerto TCP
$client_id = 'PHP_LockMyLock_' . uniqid(); // ID del cliente único para la conexión MQTT

$mqtt = new phpMQTT($server, $port, $client_id);

if (!$mqtt->connect(true, NULL, $username, $password)) {
    echo "No se pudo conectar al servidor MQTT\n";
    exit(1);
}

echo "Conectado al servidor MQTT\n";

// Función de callback para manejar mensajes recibidos
function procMsg($topic, $msg) {
    echo "Mensaje recibido en el tema: $topic\nMensaje: $msg\n";
    logMessage("Mensaje recibido en el tema: $topic, Mensaje: $msg");
}

// Función para registrar mensajes en un archivo de log
function logMessage($message) {
    $logFile = 'mqtt_log.txt';
    $currentDateTime = date('Y-m-d H:i:s');
    file_put_contents($logFile, "$currentDateTime - $message\n", FILE_APPEND);
}

// Función para verificar la suscripción
function verifySubscription($mqtt, $topics) {
    echo "Verificando suscripción...\n";
    $mqtt->subscribe($topics, 0);
    echo "Suscripción verificada\n";
    logMessage("Suscripción verificada para los temas: " . implode(", ", array_keys($topics)));
}

// Función para publicar mensajes y verificar
function publishMessage($mqtt, $topic, $message) {
    if ($mqtt->publish($topic, $message, 0)) {
        echo "Mensaje publicado en el tema: $topic\n";
        logMessage("Mensaje publicado en el tema: $topic, Mensaje: $message");
    } else {
        echo "Error al publicar en el tema: $topic\n";
        logMessage("Error al publicar en el tema: $topic");
    }
}

// Suscribirse al tema
$topics['esp32/entry'] = array('qos' => 0, 'function' => 'procMsg');
verifySubscription($mqtt, $topics);

// Publicar mensajes en temas
publishMessage($mqtt, 'esp32/pin', '123456');
publishMessage($mqtt, 'esp32/temporal', '654321');
publishMessage($mqtt, 'esp32/network', 'Wokwi-GUEST:password');

// Mantener la suscripción activa y escuchar mensajes durante 30 segundos
$time = time();
while ($mqtt->proc() && (time() - $time < 30)) {
    // Esperar y escuchar mensajes
}

$mqtt->close();
echo "Conexión MQTT cerrada\n";
logMessage("Conexión MQTT cerrada");

?>
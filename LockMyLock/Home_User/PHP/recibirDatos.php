<?php
session_start();

// Incluir el autoloader de Composer
require '..\App\Home_User\HTML\vendor\autoload.php';

// Importar la clase phpMQTT del espacio de nombres Bluerhinos
use Bluerhinos\phpMQTT;

$server = 'broker.hivemq.com'; // Broker MQTT público de HiveMQ
$port = 1883; // Puerto TCP
$client_id = 'PHP_LockMyLock_' . uniqid(); // ID del cliente único para la conexión MQTT

$mqtt = new phpMQTT($server, $port, $client_id);

if (!$mqtt->connect(true, NULL, NULL, NULL)) {
    echo "No se pudo conectar al servidor MQTT\n";
    exit(1);
}

echo "Conectado al servidor MQTT\n";

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['user_id'])) {
    // Si no ha iniciado sesión, redirigir al usuario al formulario de inicio de sesión
    //header("Location: login.php");
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



$topics['esp32/entry'] = array("qos"=>0, "function"=>"procmsg");
$mqtt->subscribe($topics,0);

while($mqtt->proc()){
}
$mqtt->close();

function procmsg($topic,$msg){
    global $conn;
    // Obtener el UserID del usuario actual desde la sesión
    $userID = $_SESSION['user_id'];
    // Obtener la fecha y hora actual
    $accessTime = date('Y-m-d H:i:s');
    echo $msg;


    // Determinar el valor de AccessResult según el mensaje recibido
    $accessResult = "";
    if ($msg == "PIN Principal" || $msg == "PIN Temporal") {
        $accessResult = "Exitoso";
    } else if ($msg == "Bloqueo") {
        $accessResult = "No Exitoso";
    } else {
        // Valor predeterminado si el mensaje no coincide con ninguno de los anteriores
        $accessResult = "Desconocido";
    }
    
    // Procesar y almacenar los datos en la base de datos
    $sql = "INSERT INTO accesslogs (UserID, LockID, lockType, AccessTime, AccessResult) 
            VALUES ('$userID', '1', '$msg', '$accessTime', '$accessResult')";
    
    if ($conn->query($sql) === TRUE) {
        echo "New record created successfully";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}

$conn->close();
?>
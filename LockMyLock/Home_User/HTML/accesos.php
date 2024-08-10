<?php

session_start();

// Incluir el autoloader de Composer
require 'vendor/autoload.php';

// Importar la clase phpMQTT del espacio de nombres Bluerhinos
use Bluerhinos\phpMQTT;

// Establecer la conexión con la base de datos
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "lockappdb";

$conn = new mysqli($servername, $username, $password, $dbname);


$server = 'broker.hivemq.com'; // Broker MQTT público de HiveMQ
$port = 1883; // Puerto TCP
$client_id = 'PHP_LockMyLock_' . uniqid(); // ID del cliente único para la conexión MQTT

$mqtt = new phpMQTT($server, $port, $client_id);

if(!$mqtt->connect(true, NULL, NULL, NULL)){
	exit(1);
}

// Verificar la conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Consultar el historial de accesos
$sql = "SELECT * FROM accesslogs";
$result = $conn->query($sql);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../CSS/accesos.css">
    <title>LockMyLock</title>
</head>
<body>

    <div class="sidebar">
        <ul>
            <li>
                <a href="#" class="logo">
                <span class="icon"><img src="../IMG/icon.png" ion-icon name="lock-open-outline"></ion-icon></span>
                    <span class="text">Lock My Lock</span>
                </a>
            </li>
            <li>
                <a href="../HTML/home_user.html" class="nav-link" data-page="inicio.html">
                    <span class="icon"><ion-icon name="home-outline"></ion-icon></span>
                    <span class="text">Inicio</span>
                </a>
            </li>
            <li>
                <a href="../HTML/perfil.php" class="nav-link" data-page="perfil.html">
                    <span class="icon"><ion-icon name="person-outline"></ion-icon></span>
                    <span class="text">Perfil</span>
                </a>
            </li>
            <li>
                <a href="../HTML/accesos.php" class="nav-link" data-page="#">
                    <span class="icon"><ion-icon name="sync-outline"></ion-icon></ion-icon></span>
                    <span class="text">Accesos</span>
                </a>
            </li>
            <li>
                <a href="../HTML/pin_temporal.php" class="nav-link" data-page="#">
                    <span class="icon"><ion-icon name="timer-outline"></ion-icon></span>
                    <span class="text">Pin Temporal</span>
                </a>
            </li>
            <li>
                <a href="../HTML/wifi.html" class="nav-link" data-page="#">
                    <span class="icon"><ion-icon name="wifi-outline"></ion-icon></ion-icon></span>
                    <span class="text">WI-FI</span>
                </a>
            </li>
            <li>
                <a href="../HTML/nosotros.html" class="nav-link" data-page="#">
                    <span class="icon"><ion-icon name="information-circle-outline"></ion-icon></ion-icon></span>
                    <span class="text">Nosotros</span>
                </a>
            </li>
            <li>
                <a href="/app/Login/HTML/login.html" class="nav-link" data-page="#">
                    <span class="icon"><ion-icon name="log-out-outline"></ion-icon></span>
                    <span class="text">Log-Out</span>
                </a>
            </li>
        </ul>
    </div>

    <div id="content-container"></div>

    <div class="contenedorTablas">
            <h2>Historial de Accesos</h2>
            <p>Aquí se mostrarán las veces que has abierto tu cerradura en el último mes. Podrás saber la fecha, la hora y con qué tipo de PIN se abrió.</p>
            <table>
                <thead>
                    <tr>
                        <th></th> <!-- Espacio para imágenes -->
                        <th>Tipo de PIN</th>
                        <th>Fecha y Hora</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                
                

                </tbody>
            </table>
            <span class="pages"></span> <!-- Aquí se generarán los números de página -->
            <button class="previous" onclick="previousPage()">◄ Anterior</button>
            <button class="next" onclick="nextPage()">Siguiente ►</button> <!-- Botón "Siguiente" -->

        </div>
    </div>

    <script type="text/javascript">
        var totalRecords = <?php echo $result->num_rows; ?>; // Total de registros en la base de datos
        var rows = <?php echo json_encode($result->fetch_all(MYSQLI_ASSOC)); ?>; // Obtener todas las filas de la consulta
    </script>
    <script src="../JS/pageAccesos.js"></script>


<script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
<script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>

</body>
</html>
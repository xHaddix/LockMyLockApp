<?php
session_start();

// Incluir el autoloader de Composer
require 'vendor/autoload.php';

// Importar la clase phpMQTT del espacio de nombres Bluerhinos
use Bluerhinos\phpMQTT;

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "lockappdb";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

$server = 'broker.hivemq.com'; // Broker MQTT público de HiveMQ
$port = 1883; // Puerto TCP
$client_id = 'PHP_LockMyLock_' . uniqid(); // ID del cliente único para la conexión MQTT

$mqtt = new phpMQTT($server, $port, $client_id);

// Función para generar un PIN de 6 dígitos aleatorios
function generateRandomPin() {
    return rand(100000, 999999);
}

// Si se presiona el botón "Deshabilitar PIN Actual"
if (isset($_POST['disable_pin'])) {
    $userID = $_SESSION['user_id'];
    $sql_disable_pin = "UPDATE locks SET TemporaryCode = NULL, TemporaryCodeCreatedAt = NULL, TemporaryCodeExpiresAt = NULL WHERE UserID = $userID";

    if ($conn->query($sql_disable_pin) === TRUE) {
        if ($mqtt->connect(true, NULL, NULL, NULL, NULL)) {
            // Publicar el mensaje
            $mqtt->publish('esp32/temporal', ' * ', 0);
            // Cerrar la conexión
            $mqtt->close();
        } else {
            // Manejar el caso en que no se puede conectar al servidor MQTT
            echo "<script>alert('Error: No se pudo conectar al servidor MQTT.'); window.history.back();</script>";
        }
        echo "<script>alert('PIN temporal deshabilitado correctamente.');</script>";
        echo "<script>window.location.href = '../HTML/pin_temporal.php';</script>";
        exit;
    } else {
        echo "Error al deshabilitar el PIN temporal: " . $conn->error;
    }
}

// Si se presiona el botón "Generar PIN Temporal"
if (isset($_POST['generate_pin'])) {
    $duration = intval($_POST['duration']);

    if ($duration <= 0) {
        echo "<script>alert('Duración inválida.');</script>";
        echo "<script>window.location.href = '../HTML/pin_temporal.php';</script>";
        exit;
    }

    $userID = $_SESSION['user_id'];
    $sql_check_pin = "SELECT TemporaryCode, LockKey, TemporaryCodeCreatedAt FROM locks WHERE UserID = $userID";
    $result_check_pin = $conn->query($sql_check_pin);

    if ($result_check_pin && $result_check_pin->num_rows > 0) {
        $row = $result_check_pin->fetch_assoc();
        $existing_pin = $row['TemporaryCode'];
        $mainCode = $row['LockKey'];
        $pin_created_at = $row['TemporaryCodeCreatedAt'];

        if (!empty($existing_pin)) {
            $now = new DateTime();
            $created_at = new DateTime($pin_created_at);
            $interval = $now->diff($created_at);

            if ($interval->h < 24) {
                echo "<script>alert('Ya tienes un Pin Temporal Creado.');</script>";
                echo "<script>window.location.href = '../HTML/pin_temporal.php';</script>";
                exit;
            } else {
                $sql_expire_pin = "UPDATE locks SET TemporaryCode = NULL, TemporaryCodeCreatedAt = NULL, TemporaryCodeExpiresAt = NULL WHERE UserID = $userID";
                $conn->query($sql_expire_pin);
            }
        }
    } else {
        echo "<script>alert('No has registrado tu cerradura.');</script>";
                echo "<script>window.location.href = '../HTML/pin_temporal.php';</script>";
                exit;
    }

    $pin = generateRandomPin();
    if ($pin === $mainCode) {
        echo "<script>alert('El PIN temporal no puede ser igual al código principal.');</script>";
        echo "<script>window.location.href = '../HTML/pin_temporal.php';</script>";
        exit;
    }

    date_default_timezone_set('America/Bogota');
    $expiry_time = date('Y-m-d H:i:s', strtotime("+$duration hours"));
    
    $sql_lockid = "SELECT LockID FROM locks WHERE UserID = $userID";
    $result_lockid = $conn->query($sql_lockid);

    if ($result_lockid && $result_lockid->num_rows > 0) {
        $row = $result_lockid->fetch_assoc();
        $lockID = $row['LockID'];

        $sql = "UPDATE locks SET TemporaryCode='$pin', TemporaryCodeCreatedAt=NOW(), TemporaryCodeExpiresAt='$expiry_time' WHERE LockID=$lockID";

        if ($conn->query($sql) === TRUE) {
            if ($mqtt->connect(true, NULL, NULL, NULL, NULL)) {
                // Publicar el mensaje
                $mqtt->publish('esp32/temporal', $pin, 0);
                // Cerrar la conexión
                $mqtt->close();
            } else {
                // Manejar el caso en que no se puede conectar al servidor MQTT
                echo "<script>alert('Error: No se pudo conectar al servidor MQTT.'); window.history.back();</script>";
            }
            echo "<script>";
            echo "alert('Pin Generado Correctamente: $pin');";
            echo 'window.location.href = "../HTML/pin_temporal.php";'; 
            echo "</script>";
        } else {
            echo "Error al actualizar el PIN: " . $conn->error;
        }
    } else {
        echo "No se encontró un LockID asociado al UserID.";
    }

    $conn->close();
}

$userID = $_SESSION['user_id'];
$sql_get_pin = "SELECT TemporaryCode, TemporaryCodeExpiresAt FROM locks WHERE UserID = $userID";
$result_get_pin = $conn->query($sql_get_pin);

$pin_temporal = "";
$expiry_time = "";
if ($result_get_pin && $result_get_pin->num_rows > 0) {
    $row = $result_get_pin->fetch_assoc();
    $pin_temporal = $row['TemporaryCode'];
    $expiry_time = $row['TemporaryCodeExpiresAt'];
}

$conn->close();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../CSS/pin.css">
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

<h2>Crear PIN Temporal</h2>

<form id="pin-form" action="" method="POST">
            <div class="password">
                <?php if (!empty($pin_temporal)): ?>
                    <?php $pin_digits = str_split($pin_temporal); ?>
                    <?php foreach ($pin_digits as $digit): ?>
                        <input maxlength="1" class="input" name="pin[]" type="text" value="<?php echo $digit; ?>" readonly />
                    <?php endforeach; ?>
                <?php else: ?>
                    <input maxlength="1" placeholder='0' class="input" name="pin[]" type="text" readonly />
                    <input maxlength="1" placeholder='0' class="input" name="pin[]" type="text" readonly />
                    <input maxlength="1" placeholder='0' class="input" name="pin[]" type="text" readonly />
                    <input maxlength="1" placeholder='0' class="input" name="pin[]" type="text" readonly />
                    <input maxlength="1" placeholder='0' class="input" name="pin[]" type="text" readonly />
                    <input maxlength="1" placeholder='0' class="input" name="pin[]" type="text" readonly />
                <?php endif; ?>
            </div>
            <h3>¡Puedes generar un PIN temporal desde una hasta 24 horas!</h3>
            <label class="label_clock" for="duration">¿Cuánto tiempo (en horas) deseas que dure tu pin temporal? </label>
            <input class="clock" type="number" id="duration" name="duration" min="1" max="24" placeholder="1 - 24">
            <button type="submit" name="generate_pin" class="btnPinTemporal">Generar PIN Temporal</button>
            <button type="submit" id="btnDisablePin" name="disable_pin" class="btnDeshabilitarPinActual">Deshabilitar PIN Actual</button>
        </form>

<?php if (!empty($pin_temporal) && !empty($expiry_time)): ?>
    <h3>Tiempo restante para el PIN temporal: <span id="countdown"></span></h3>
    <script>
        function startCountdown(expiryTime) {
            var countdownElement = document.getElementById('countdown');
            var expiryDate = new Date(expiryTime).getTime();

            var x = setInterval(function() {
                var now = new Date().getTime();
                var distance = expiryDate - now;
                var btnDisablePin = document.getElementById('btnDisablePin');

                var hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                var seconds = Math.floor((distance % (1000 * 60)) / 1000);

                countdownElement.innerHTML = hours + "h " + minutes + "m " + seconds + "s ";

                if (distance < 0) {
                    clearInterval(x);
                    countdownElement.innerHTML = "EXPIRED";
                    btnDisablePin.click();
                }
            }, 1000);
        }

        document.addEventListener('DOMContentLoaded', function() {
            var expiryTime = '<?php echo date('Y-m-d\TH:i:s', strtotime($expiry_time)); ?>';
            startCountdown(expiryTime);
        });
    </script>
<?php endif; ?>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
<script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
</body>
</html>
<?php
session_start();
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

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['user_id'])) {
    // Si no ha iniciado sesión, redirigir al usuario al formulario de inicio de sesión
    header("Location: login.php");
    exit;
}

// Obtener el UserID del usuario actual desde la sesión
$userID = $_SESSION['user_id'];

// Obtener datos del usuario para llenar los campos del formulario
$sql = "SELECT Username, Email, phoneNumberUser FROM users WHERE UserID = $userID";
$result = $conn->query($sql);

if ($result === false) {
    die("Error al ejecutar la consulta: " . $conn->error);
}

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $nombre = $row["Username"];
        $correo = $row["Email"];
        $telefono = $row["phoneNumberUser"];
    }
} else {
    echo "No se encontraron resultados.";
}

$conn->close();


?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../CSS/perfil.css">
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


    <div class="contenedor">
    <form action="../PHP/actualizarDatos.php" method="POST">
        <div class="contenedorIzquierdo">
            <h2>Perfil</h2>
            <label for="nombre">Nombre Completo</label>
            <input type="text" id="nombre" name="Username" value="<?php echo $nombre; ?>" placeholder="Nombre Completo" required>
            <label for="email">Correo Electrónico</label>
            <input type="email" id="email" name="email" value="<?php echo $correo; ?>" placeholder="Correo Electrónico" required>
            <label for="telefono">Teléfono</label>
            <input type="text" id="telefono" name="phoneNumberUser" value="<?php echo $telefono; ?>" placeholder="Teléfono" disabled required>
            <label class="material-checkbox">
                <input id="checkbox" type="checkbox">
                <span class="checkmark"></span>
                Cambiar Contraseña
            </label>
            <label for="contrasena">Contraseña</label>
            <input type="password" id="contrasena" name="password" placeholder="Nueva Contraseña" disabled required>
            <button type="submit" id="bottone1">Guardar Cambios Perfil</button>
        </div>
    </form>

    <div class="contenedorDerecho">
    <h2>Configurar Cerradura</h2>


    <form action="../PHP/registrarCerradura.php" method="POST">
    <label for="serialCode">Código Serial</label>
    <input type="text" id="serialCode" name="SerialCode" placeholder="Código Serial">
    <button class="bottone3" type="submit">Registrar Cerradura</button>
</form>


    <form action="../PHP/actualizarDatosCerradura.php" method="POST">
    
        <label for="pin_actual">PIN Actual de Acceso</label>
        <input type="password" id="pin_actual" name="CurrentPIN" placeholder="PIN Actual" required>
        
        <label class="material-checkbox">
            <input id="checkboxPIN" type="checkbox" onchange="togglePINInput()">
            <span class="checkmark"></span>
            Cambiar Pin de Acceso
        </label>
        <label for="pin">Nuevo PIN de Acceso</label>
        <input type="password" id="pin" name="NewPIN" placeholder="Nuevo PIN" disabled>

        <p>Si tu cerradura está bloqueada, debes cambiar el PIN de acceso.</p>
        <button type="submit" id="bottone2">Guardar Cambios Cerradura</button>
    </div>
</form>
</div>


</div>

    
    <div class="barra1"></div>

    <script src="../JS/scritpCheckbox.js"></script>
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
</body>
</html>

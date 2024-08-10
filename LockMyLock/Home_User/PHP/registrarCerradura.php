<?php
session_start();

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

// Obtener el código serial enviado desde el formulario
$serialCode = $_POST['SerialCode'];

// Verificar si el código serial es válido consultando la tabla `lock_serials`
$sql_serial = "SELECT * FROM lock_serials WHERE SerialCode = '$serialCode'";
$result_serial = $conn->query($sql_serial);

if ($result_serial->num_rows > 0) {
    // El código serial es válido

    // Verificar si el usuario ya tiene una entrada en la tabla `locks`
    $sql_locks = "SELECT * FROM locks WHERE UserID = $userID";
    $result_locks = $conn->query($sql_locks);

    if ($result_locks->num_rows == 0) {
        // El usuario no tiene una entrada en la tabla `locks`, crear una nueva entrada con el código serial enviado
        try {
            $sql_insert = "INSERT INTO locks (LockCode, LockKey, UserID) VALUES ('$serialCode', '000000', $userID)";
            $conn->query($sql_insert);
            echo "<script>alert('Cerradura registrada exitosamente. Su PIN predeterminado es 000000.'); window.location.href = '../HTML/perfil.php';</script>";
            exit;
        } catch (mysqli_sql_exception $exception) {
            // Error al crear la entrada
            echo "<script>alert('Este código ya se encuentra en uso.'); window.location.href = '../HTML/perfil.php';</script>";
            exit;
        }
    } else {
        // El usuario ya tiene una entrada en la tabla `locks`
        echo "<script>alert('El usuario ya tiene una cerradura registrada.'); window.location.href = '../HTML/perfil.php';</script>";
        exit;
    }
} else {
    // El código serial no es válido
    echo "<script>alert('El código serial no es válido.'); window.location.href = '../HTML/perfil.php';</script>";
    exit;
}

// Cerrar la conexión
$conn->close();
?>

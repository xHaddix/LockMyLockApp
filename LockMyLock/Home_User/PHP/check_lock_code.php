<?php
// Datos de conexión a la base de datos
$servername = "nombre_del_servidor";
$username = "nombre_de_usuario";
$password = "contraseña";
$dbname = "nombre_de_la_base_de_datos";

// Intentar establecer la conexión
try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    // Establecer el modo de errores de PDO a excepciones
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Verificar si se ha enviado el formulario
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Simular la validación del código de la cerradura (aquí iría tu lógica de validación)
        $lock_code = "1234"; // Supongamos que el código de la cerradura válido es "1234"

        // Verificar si el código de la cerradura enviado coincide con el código válido
        if ($_POST["lock-code"] === $lock_code) {
            // Iniciar sesión (esto es solo un ejemplo, asegúrate de utilizar un sistema de autenticación seguro en tu aplicación)
            session_start();
            // Redirigir al usuario a la página de configuración
            header("Location: configuration.php");
            exit;
        } else {
            // Si el código de la cerradura no es válido, mostrar un mensaje de error
            echo "El código de la cerradura es incorrecto. Por favor, inténtalo de nuevo.";
        }
    }
}
catch(PDOException $e) {
    // Si hay un error en la conexión, mostrar el mensaje de error
    echo "Error de conexión: " . $e->getMessage();
}

// Cerrar la conexión
$conn = null;
?>

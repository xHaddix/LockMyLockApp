<?php
// Establecer conexión con la base de datos
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "lockappdb";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    // Establecer el modo de errores de PDO a excepciones
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Verificar si se ha enviado el formulario de inicio de sesión
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $email = $_POST['email'];
        $password = $_POST['password'];

        // Consultar la base de datos para verificar el usuario
        $stmt = $conn->prepare("SELECT * FROM Users WHERE Email = :email AND Password = :password");
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password', $password);
        $stmt->execute();
        $user = $stmt->fetch();

        if ($user) {
            // Iniciar sesión y redirigir al usuario a la página home_user.html
            session_start();
            $_SESSION['user_id'] = $user['UserID'];
            $resultado = shell_exec('python3 ..\App\Home_User\PHP\recibirDatosPy.py ' . escapeshellarg($user_id) . ' 2>&1');
            echo $resultado;
            header("Location: /APP/Home_User/HTML/home_user.html");
            exit;
        } else {
            // Si las credenciales son incorrectas, mostrar un mensaje de error y redirigir al formulario de inicio de sesión
            echo "<script>";
            echo "alert('Correo electrónico o contraseña incorrectos.');";
            echo "window.location.href = '../HTML/login.html';";
            echo "</script>";
            exit;
        }
    }
} catch(PDOException $e) {
    // Si hay un error en la conexión, mostrar el mensaje de error
    echo "Error de conexión: " . $e->getMessage();
}

// Cerrar la conexión
$conn = null;
?>

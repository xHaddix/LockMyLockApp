<?php
// Configuración de la conexión a la base de datos
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "lockappdb";

// Crear la conexión
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar la conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Recibe los datos del formulario
    $email = $_POST["email"];
    $name = $_POST["name"];
    $newPassword = $_POST["new-password"];
    $oldPassword = $_POST["old-password"];
    
    // Validar longitud del nombre y la contraseña
    if (strlen($name) < 8) {
        echo "<script>";
        echo "alert('El nombre debe tener al menos 8 caracteres.');";
        echo "window.location.href = '../HTML/home_user.html';";
        echo "</script>";
        exit;
    } elseif (strlen($newPassword) < 8 || strlen($newPassword) > 15) {
        echo "<script>";
        echo "alert('La contraseña debe tener entre 8 y 15 caracteres.');";
        echo "window.location.href = '../HTML/home_user.html';";
        echo "</script>";
        exit;
    } elseif (preg_match('/[0-9]/', $name)) {
        echo "<script>";
        echo "alert('El nombre no puede contener números.');";
        echo "window.location.href = '../HTML/home_user.html';";
        echo "</script>";
        exit;
    } else {
        // Obtener la contraseña actual del usuario desde la base de datos
        $sql = "SELECT Password FROM users WHERE Email='$email'";
        $result = $conn->query($sql);
        
        if ($result->num_rows > 0) {
            // Existe un usuario con el email proporcionado
            $row = $result->fetch_assoc();
            $oldPasswordFromDatabase = $row["Password"];
            
            // Verificar si la contraseña anterior ingresada coincide con la contraseña actual en la base de datos
            if ($oldPassword == $oldPasswordFromDatabase) {
                // Actualizar los datos del usuario en la base de datos
                $sql_update = "UPDATE users SET Username='$name', Password='$newPassword' WHERE Email='$email'";
                
                if ($conn->query($sql_update) === TRUE) {
                    echo "<script>";
                    echo "alert('Datos actualizados correctamente.');";
                    echo "window.location.href = '../HTML/home_user.html';";
                    echo "</script>";
                } else {
                    echo "Error al actualizar los datos: " . $conn->error;
                }
            } else {
                echo "<script>";
                echo "alert('Contraseña no coincide con la contraseña actual.');";
                echo "window.location.href = '../HTML/home_user.html';";
                echo "</script>";
                exit;
            }
        } else {
            echo "<script>";
            echo "alert('No se encontró ningún email con el usuario proporcionado.');";
            echo "window.location.href = '../HTML/home_user.html';";
            echo "</script>";
            exit;
        }
    }
}

// Cerrar la conexión
$conn->close();
?>


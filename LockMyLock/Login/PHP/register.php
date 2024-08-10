<?php

// Verificar si se envió el formulario de registro
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Conectar a la base de datos (reemplaza 'localhost', 'root', '', 'lockappdb' según tu configuración)
    $conn = new mysqli("localhost", "root", "", "lockappdb");

    // Verificar la conexión
    if ($conn->connect_error) {
        die("Error de conexión: " . $conn->connect_error);
    }

    // Obtener los datos del formulario y limpiarlos para evitar inyección SQL
    $username = mysqli_real_escape_string($conn, $_POST["username"]);
    $email = mysqli_real_escape_string($conn, $_POST["email"]);
    $password = mysqli_real_escape_string($conn, $_POST["password"]); 
    $phoneNumberUser = mysqli_real_escape_string($conn,$_POST["phoneNumberUser"]);

    // Validar que el nombre de usuario no contenga números
    if (preg_match('/[0-9]/', $username)) {
        echo "<script>";
        echo "alert('El nombre no puede contener números');";
        echo "window.location.href = '../HTML/login.html';";
        echo "</script>";
        exit;
    }

    if(strlen($phoneNumberUser < 8)){
        echo "<script>";
        echo "alert('El número no puede tener menos de 8 digitos');";
        echo "window.location.href = '../HTML/login.html';";
        echo "</script>";
        exit;
    }

 if (!preg_match('/^\d+$/', $phoneNumberUser)) {
    echo "<script>";
    echo "alert('El número solo puede contener dígitos.');";
    echo "window.location.href = '../HTML/login.html';";
    echo "</script>";
    exit;
}



    // Validar longitud del nombre de usuario
    if (strlen($username) < 8 || strlen($username) > 100) {
        echo "<script>";
        echo "alert('El nombre no puede tener menos de 8 carácteres.');";
        echo "window.location.href = '../HTML/login.html';";
        echo "</script>";
        exit;
    }

    // Validar longitud de la contraseña
    if (strlen($password) < 8 || strlen($password) > 15) {
        echo "<script>";
        echo "alert('La contraseña debe tener entre 8 y 15 caracteres.');";
        echo "window.location.href = '../HTML/login.html';";
        echo "</script>";
        exit;
    }

    $check_user_query = "SELECT * FROM Users WHERE phoneNumberUser = ? OR email = ?";
    $check_user_statement = $conn->prepare($check_user_query);
    $check_user_statement->bind_param("ss", $phoneNumberUser, $emailUser);
    $check_user_statement->execute();
    $check_user_result = $check_user_statement->get_result();
    
    if ($check_user_result->num_rows > 0) {
        echo "<script>";
        echo "alert('El número o correo ya se encuentra registrado.');";
        echo "window.location.href = '../HTML/login.html';";
        echo "</script>";
        exit; // Detener la ejecución del script
    }
    

    // Insertar los datos del usuario en la base de datos
    $sql = "INSERT INTO Users (Username, Email, Password, phoneNumberUser) VALUES ('$username', '$email', '$password', '$phoneNumberUser')";
    if ($conn->query($sql) === TRUE) {
        echo "<script>";
        echo "alert('Registro Exitoso');";
        echo "window.location.href = '../HTML/login.html';";
        echo "</script>";
    } else {
        echo "<script>";
        echo "alert('Error al registrar usuario. Por favor, intenta de nuevo más tarde.');";
        // echo "window.location.href = '../HTML/login.html';";
        echo "</script>" . $conn->error;
    }

    // Cerrar la conexión
    $conn->close();
}
?>

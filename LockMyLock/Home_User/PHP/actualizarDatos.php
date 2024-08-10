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

// Obtener los datos del formulario
$nombre = $_POST['Username'];
$correo = $_POST['email'];
$contrasena = $_POST['password'];

// Verificar si el correo ya está registrado en la base de datos
$check_email_query = "SELECT * FROM users WHERE Email = ? AND UserID != ?";
$check_email_statement = $conn->prepare($check_email_query);
$check_email_statement->bind_param("si", $correo, $userID);
$check_email_statement->execute();
$check_email_result = $check_email_statement->get_result();

if ($check_email_result->num_rows > 0) {
    echo "<script>";
    echo "alert('El correo ingresado ya está registrado.');";
    echo "window.location.href = '../HTML/perfil.php';";
    echo "</script>";
    exit;
}

if (preg_match('/[0-9]/', $nombre)) {
    echo "<script>";
    echo "alert('El nombre no puede contener números');";
    echo "window.location.href = '../HTML/perfil.php';";
    echo "</script>";
    exit;
}


// Validar longitud del nombre de usuario
if (strlen($nombre) < 8 || strlen($nombre) > 100) {
    echo "<script>";
    echo "alert('El nombre no puede tener menos de 8 carácteres.');";
    echo "window.location.href = '../HTML/perfil.php';";
    echo "</script>";
    exit;
}

// Validar longitud de la contraseña
if (strlen($contrasena) < 8 || strlen($contrasena) > 15) {
    echo "<script>";
    echo "alert('La contraseña debe tener entre 8 y 15 caracteres.');";
    echo "window.location.href = '../HTML/perfil.php';";
    echo "</script>";
    exit;
}

// Actualizar los datos del usuario en la base de datos
$sql = "UPDATE users SET Username = '$nombre', Email = '$correo', Password = '$contrasena' WHERE UserID = $userID";

if ($conn->query($sql) === true) {
    echo "<script>";
    echo 'alert("Datos Actualizados Correctamente");';
    echo 'window.location.href = "../HTML/perfil.php";'; // Reemplaza "nombre_de_tu_pagina.php" con la URL de la página a la que quieres redirigir
    echo "</script>";
    

} else {
    echo "Error al actualizar datos: " . $conn->error;
}

$conn->close();
?>

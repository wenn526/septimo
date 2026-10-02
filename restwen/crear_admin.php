<?php
/* Abrilo UNA vez desde el navegador para crear el admin y después BORRALO. */
require 'config/db.php';

$email = 'admin@restwen.com';
$pass = 'admin2007'; // cambiala
$hash = password_hash($pass, PASSWORD_DEFAULT);

if (mysqli_num_rows(mysqli_query($conn, "SELECT 1 FROM usuarios WHERE email = '$email'")) > 0) {
    mysqli_query($conn, "UPDATE usuarios SET password = '$hash', rol = 'admin' WHERE email = '$email'");
} else {
    mysqli_query($conn, "INSERT INTO usuarios (nombre, email, password, rol) VALUES ('Administrador', '$email', '$hash', 'admin')");
}

// El admin también figura como empleado (puesto admin)
$u = mysqli_fetch_assoc(mysqli_query($conn, "SELECT id_usuario, id_empleado FROM usuarios WHERE email = '$email'"));
if (!$u['id_empleado']) {
    mysqli_query($conn, "INSERT INTO empleados (nombre, apellido, puesto) VALUES ('Administrador', '', 'admin')");
    mysqli_query($conn, 'UPDATE usuarios SET id_empleado = ' . mysqli_insert_id($conn) . ' WHERE id_usuario = ' . $u['id_usuario']);
}
echo "Listo. Email: $email / Password: $pass";

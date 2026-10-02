<?php
/**
 * Conexión a la base de datos con mysqli (procedural).
 * Ajustá estos datos según tu servidor local (XAMPP / WAMP / etc.)
 */
$DB_HOST = 'localhost';
$DB_USER = 'root';
$DB_PASS = '';
$DB_NAME = 'restwen';

$conn = mysqli_connect($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);

if (!$conn) {
    die('Error de conexión a la base de datos: ' . mysqli_connect_error());
}

mysqli_set_charset($conn, 'utf8mb4');

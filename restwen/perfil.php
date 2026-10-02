<?php
$base = '';
require 'includes/init.php';
requerirRol(['cliente']);

$u = mysqli_fetch_assoc(mysqli_query($conn, 'SELECT nombre, email, fecha_registro FROM usuarios WHERE id_usuario = ' . (int)$_SESSION['id_usuario']));

$titulo = 'Mi perfil';
$activo = 'perfil';
require 'includes/header.php';
?>

<div class="tarjeta form-caja">
    <h1>Mi perfil</h1>
    <p><strong>Nombre:</strong> <?= h($u['nombre']) ?></p>
    <p><strong>Email:</strong> <?= h($u['email']) ?></p>
    <p><strong>Miembro desde:</strong> <?= date('d/m/Y', strtotime($u['fecha_registro'])) ?></p>
</div>

<?php require 'includes/footer.php'; ?>

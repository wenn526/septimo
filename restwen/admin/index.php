<?php
$base = '../';
require $base . 'includes/init.php';
requerirRol(['admin']);

$titulo = 'Panel Admin';
$activo = 'admin';
require $base . 'includes/header.php';
?>

<h1>Panel de administración</h1>
<p>Gestioná el contenido del restaurante.</p>

<div class="panel-links">
    <a href="platos.php">🍽️ Platos y menú</a>
    <a href="mesas.php">🪑 Mesas</a>
    <a href="empleados.php">👤 Empleados</a>
    <a href="usuarios.php">🔐 Usuarios</a>
</div>

<?php require $base . 'includes/footer.php'; ?>

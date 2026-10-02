<?php
// La página define antes: $base ('' en la raíz, '../' en subcarpetas), $titulo y $activo
$base = $base ?? '';
$activo = $activo ?? '';

// Menú de cada rol: [sección activa, link, texto]
$menus = [
    'visita'  => [['inicio', 'index.php', 'Inicio'], ['menu', 'index.php#menu', 'Menú'], ['reservas', 'index.php#reservas', 'Reservas']],
    'cliente' => [['inicio', 'index.php', 'Inicio'], ['perfil', 'perfil.php', 'Mi perfil']],
    'mozo'    => [['pedidos', 'mozo/pedidos.php', 'Pedidos']],
    'cocina'  => [['cocina', 'cocina/pedidos.php', 'Cocina']],
    'barra'   => [['mesas', 'barra/mesas.php', 'Mesas'], ['historial', 'barra/historial.php', 'Historial']],
    'admin'   => [['pedidos', 'mozo/pedidos.php', 'Pedidos'], ['cocina', 'cocina/pedidos.php', 'Cocina'], ['mesas', 'barra/mesas.php', 'Mesas'],
                  ['historial', 'barra/historial.php', 'Historial'], ['admin', 'admin/index.php', 'Panel Admin']],
];
$menu = $menus[rolActual() ?? 'visita'] ?? [];
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= $titulo ?? 'Restaurante' ?></title>
<link rel="stylesheet" href="<?= $base ?>css/style.css">
</head>
<body>
<header class="topbar">
    <a class="logo" href="<?= $base . inicioDe(rolActual()) ?>">RESTAURANTE</a>

    <nav class="nav-principal">
        <?php foreach ($menu as [$clave, $url, $texto]): ?>
            <a href="<?= $base . $url ?>" class="<?= $activo === $clave ? 'activo' : '' ?>"><?= $texto ?></a>
        <?php endforeach; ?>
    </nav>

    <div class="acciones-sesion">
    <?php if (estaLogueado()): ?>
        <span class="usuario-actual"><?= h($_SESSION['nombre']) ?> (<?= h(rolActual()) ?>)</span>
        <a class="btn btn-outline" href="<?= $base ?>logout.php">Cerrar sesión</a>
    <?php else: ?>
        <a class="btn btn-outline" href="<?= $base ?>login.php">Iniciar sesión</a>
    <?php endif; ?>
    </div>
</header>

<?php if (($_GET['error'] ?? '') === 'acceso'): ?>
    <div class="alerta alerta-error">Necesitás iniciar sesión para ver esa página.</div>
<?php endif; ?>

<main class="contenido">

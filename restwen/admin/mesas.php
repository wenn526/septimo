<?php
$base = '../';
require $base . 'includes/init.php';
requerirRol(['admin']);

$error = '';
if (isset($_GET['eliminar'])) {
    mysqli_query($conn, 'DELETE FROM mesas WHERE id_mesa = ' . (int)$_GET['eliminar']);
    header('Location: mesas.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $numero = (int)$_POST['numero'];
    $capacidad = (int)$_POST['capacidad'];
    $zona = limpiar($_POST['zona']);
    $id = (int)$_POST['id_mesa'];

    if ($numero <= 0 || $capacidad <= 0 || $zona === '') {
        $error = 'Completá todos los campos correctamente.';
    } else {
        mysqli_query($conn, $id
            ? "UPDATE mesas SET numero=$numero, capacidad=$capacidad, zona='$zona' WHERE id_mesa=$id"
            : "INSERT INTO mesas (numero, capacidad, zona, estado, personas) VALUES ($numero, $capacidad, '$zona', 'libre', 0)");
        header('Location: mesas.php');
        exit;
    }
}

$e = isset($_GET['editar']) ? mysqli_fetch_assoc(mysqli_query($conn, 'SELECT * FROM mesas WHERE id_mesa = ' . (int)$_GET['editar'])) : null;
$mesas = mysqli_query($conn, 'SELECT * FROM mesas ORDER BY zona, numero');

$titulo = 'Gestionar mesas';
$activo = 'admin';
require $base . 'includes/header.php';
?>

<a href="index.php" class="volver">&larr; Volver al panel</a>
<h1>Gestionar mesas</h1>
<?php if ($error): ?><div class="alerta alerta-error"><?= $error ?></div><?php endif; ?>

<div class="tarjeta">
    <h3><?= $e ? 'Editar mesa' : 'Nueva mesa' ?></h3>
    <form method="POST" class="form-fila">
        <input type="hidden" name="id_mesa" value="<?= $e['id_mesa'] ?? '' ?>">
        <div><label>Número</label><input type="number" name="numero" required value="<?= h($e['numero'] ?? '') ?>"></div>
        <div><label>Capacidad</label><input type="number" name="capacidad" required value="<?= h($e['capacidad'] ?? '') ?>"></div>
        <div><label>Zona</label><input type="text" name="zona" required value="<?= h($e['zona'] ?? 'Salón principal') ?>"></div>
        <button class="btn"><?= $e ? 'Guardar cambios' : 'Agregar mesa' ?></button>
        <?php if ($e): ?><a href="mesas.php" class="btn btn-outline">Cancelar</a><?php endif; ?>
    </form>
</div>

<table class="tabla-admin">
    <tr><th>Número</th><th>Zona</th><th>Capacidad</th><th>Estado</th><th>Acciones</th></tr>
    <?php while ($m = mysqli_fetch_assoc($mesas)): ?>
    <tr>
        <td><?= $m['numero'] ?></td>
        <td><?= h($m['zona']) ?></td>
        <td><?= $m['capacidad'] ?></td>
        <td><?= badge($m['estado']) ?></td>
        <td class="acciones-fila">
            <a class="btn btn-outline btn-chico" href="?editar=<?= $m['id_mesa'] ?>">Editar</a>
            <a class="btn btn-rojo btn-chico" href="?eliminar=<?= $m['id_mesa'] ?>" onclick="return confirm('¿Eliminar esta mesa?');">Eliminar</a>
        </td>
    </tr>
    <?php endwhile; ?>
</table>

<?php require $base . 'includes/footer.php'; ?>

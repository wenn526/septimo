<?php
$base = '../';
require $base . 'includes/init.php';
requerirRol(['admin']);

$error = '';
if (isset($_GET['eliminar'])) {
    mysqli_query($conn, 'DELETE FROM platos WHERE id_plato = ' . (int)$_GET['eliminar']);
    header('Location: platos.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = limpiar($_POST['nombre']);
    $precio = (float)$_POST['precio'];
    $cat = limpiar($_POST['categoria']);
    $id = (int)$_POST['id_plato'];

    if ($nombre === '' || $precio <= 0) {
        $error = 'Completá todos los campos correctamente.';
    } else {
        mysqli_query($conn, $id
            ? "UPDATE platos SET nombre='$nombre', precio=$precio, categoria='$cat' WHERE id_plato=$id"
            : "INSERT INTO platos (nombre, precio, categoria) VALUES ('$nombre', $precio, '$cat')");
        header('Location: platos.php');
        exit;
    }
}

$e = isset($_GET['editar']) ? mysqli_fetch_assoc(mysqli_query($conn, 'SELECT * FROM platos WHERE id_plato = ' . (int)$_GET['editar'])) : null;
$platos = mysqli_query($conn, 'SELECT * FROM platos ORDER BY categoria, nombre');

$titulo = 'Gestionar platos';
$activo = 'admin';
require $base . 'includes/header.php';
?>

<a href="index.php" class="volver">&larr; Volver al panel</a>
<h1>Gestionar platos</h1>
<?php if ($error): ?><div class="alerta alerta-error"><?= $error ?></div><?php endif; ?>

<div class="tarjeta">
    <h3><?= $e ? 'Editar plato' : 'Nuevo plato' ?></h3>
    <form method="POST" class="form-fila">
        <input type="hidden" name="id_plato" value="<?= $e['id_plato'] ?? '' ?>">
        <div><label>Nombre</label><input type="text" name="nombre" required value="<?= h($e['nombre'] ?? '') ?>"></div>
        <div><label>Precio</label><input type="number" step="0.01" name="precio" required value="<?= h($e['precio'] ?? '') ?>"></div>
        <div>
            <label>Categoría</label>
            <select name="categoria">
                <?php foreach (['principal', 'guarnicion', 'postre', 'bebida'] as $c): ?>
                    <option <?= ($e['categoria'] ?? '') === $c ? 'selected' : '' ?>><?= $c ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <button class="btn"><?= $e ? 'Guardar cambios' : 'Agregar plato' ?></button>
        <?php if ($e): ?><a href="platos.php" class="btn btn-outline">Cancelar</a><?php endif; ?>
    </form>
</div>

<table class="tabla-admin">
    <tr><th>Nombre</th><th>Categoría</th><th>Precio</th><th>Acciones</th></tr>
    <?php while ($p = mysqli_fetch_assoc($platos)): ?>
    <tr>
        <td><?= h($p['nombre']) ?></td>
        <td><?= h($p['categoria']) ?></td>
        <td><?= formatearPrecio($p['precio']) ?></td>
        <td class="acciones-fila">
            <a class="btn btn-outline btn-chico" href="?editar=<?= $p['id_plato'] ?>">Editar</a>
            <a class="btn btn-rojo btn-chico" href="?eliminar=<?= $p['id_plato'] ?>" onclick="return confirm('¿Eliminar este plato?');">Eliminar</a>
        </td>
    </tr>
    <?php endwhile; ?>
</table>

<?php require $base . 'includes/footer.php'; ?>

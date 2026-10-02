<?php
$base = '../';
require $base . 'includes/init.php';
requerirRol(['admin']);

$yo = (int)$_SESSION['id_usuario'];
$error = '';

// Dueño de la cuenta de un empleado (para no tocar al propio admin)
function cuentaDe($id)
{
    global $conn;
    return mysqli_fetch_assoc(mysqli_query($conn, "SELECT id_usuario FROM usuarios WHERE id_empleado = $id"));
}

// Quitar del personal: la cuenta vuelve a ser de cliente
if (isset($_GET['quitar'])) {
    $id = (int)$_GET['quitar'];
    $cuenta = cuentaDe($id);
    if (!$cuenta || $cuenta['id_usuario'] != $yo) {
        mysqli_query($conn, "UPDATE usuarios SET rol = 'cliente', id_empleado = NULL WHERE id_empleado = $id");
        mysqli_query($conn, "DELETE FROM empleados WHERE id_empleado = $id");
    }
    header('Location: empleados.php');
    exit;
}

// Cambiar nombre o puesto (el rol de la cuenta cambia con el puesto)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)$_POST['id_empleado'];
    $nombre = limpiar($_POST['nombre']);
    $apellido = limpiar($_POST['apellido']);
    $puesto = $_POST['puesto'];
    $cuenta = cuentaDe($id);

    if ($nombre === '' || !in_array($puesto, PUESTOS)) {
        $error = 'Completá el nombre y elegí un puesto.';
    } elseif (!$cuenta || $cuenta['id_usuario'] != $yo) {
        mysqli_query($conn, "UPDATE empleados SET nombre = '$nombre', apellido = '$apellido', puesto = '$puesto' WHERE id_empleado = $id");
        mysqli_query($conn, "UPDATE usuarios SET rol = '$puesto' WHERE id_empleado = $id");
        header('Location: empleados.php');
        exit;
    }
}

$empleados = mysqli_query($conn, 'SELECT e.*, u.id_usuario, u.email FROM empleados e
                                  LEFT JOIN usuarios u ON u.id_empleado = e.id_empleado
                                  ORDER BY e.puesto, e.apellido');

$titulo = 'Gestionar empleados';
$activo = 'admin';
require $base . 'includes/header.php';
?>

<a href="index.php" class="volver">&larr; Volver al panel</a>
<h1>Gestionar empleados</h1>
<p>Para sumar un empleado, andá a <a href="usuarios.php"><u>Usuarios</u></a> y elegí su puesto. Acá podés cambiar su puesto (que es también su rol en el sistema) o quitarlo del personal.</p>
<?php if ($error): ?><div class="alerta alerta-error"><?= $error ?></div><?php endif; ?>

<table class="tabla-admin">
    <tr><th>Empleado</th><th>Email</th><th></th></tr>
    <?php while ($e = mysqli_fetch_assoc($empleados)): $soyYo = $e['id_usuario'] == $yo; ?>
    <tr>
        <td>
            <?php if ($soyYo): ?>
                <?= h($e['nombre'] . ' ' . $e['apellido']) ?> — <?= h($e['puesto']) ?>
            <?php else: ?>
                <form method="POST" class="form-fila">
                    <input type="hidden" name="id_empleado" value="<?= $e['id_empleado'] ?>">
                    <input type="text" name="nombre" value="<?= h($e['nombre']) ?>" required>
                    <input type="text" name="apellido" value="<?= h($e['apellido']) ?>">
                    <select name="puesto">
                        <?php foreach (PUESTOS as $p): ?>
                            <option <?= $e['puesto'] === $p ? 'selected' : '' ?>><?= $p ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button class="btn btn-chico">Guardar</button>
                </form>
            <?php endif; ?>
        </td>
        <td><?= h($e['email'] ?? '—') ?></td>
        <td>
            <?php if ($soyYo): ?>
                <small>(vos)</small>
            <?php else: ?>
                <a class="btn btn-rojo btn-chico" href="?quitar=<?= $e['id_empleado'] ?>" onclick="return confirm('¿Quitar del personal? La cuenta vuelve a ser de cliente.');">Quitar</a>
            <?php endif; ?>
        </td>
    </tr>
    <?php endwhile; ?>
</table>

<?php require $base . 'includes/footer.php'; ?>

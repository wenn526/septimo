<?php
$base = '../';
require $base . 'includes/init.php';
requerirRol(['admin']);

// Borrar una cuenta de cliente
if (isset($_GET['eliminar'])) {
    mysqli_query($conn, 'DELETE FROM usuarios WHERE rol = "cliente" AND id_usuario = ' . (int)$_GET['eliminar']);
    header('Location: usuarios.php');
    exit;
}

// Convertir un cliente en empleado: se crea el empleado y la cuenta toma su puesto como rol
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)$_POST['id_usuario'];
    $puesto = $_POST['puesto'];
    $u = mysqli_fetch_assoc(mysqli_query($conn, "SELECT nombre FROM usuarios WHERE rol = 'cliente' AND id_usuario = $id"));

    if ($u && in_array($puesto, PUESTOS)) {
        $partes = explode(' ', $u['nombre'], 2);
        $nombre = limpiar($partes[0]);
        $apellido = limpiar($partes[1] ?? '');
        mysqli_query($conn, "INSERT INTO empleados (nombre, apellido, puesto) VALUES ('$nombre', '$apellido', '$puesto')");
        $idEmpleado = mysqli_insert_id($conn);
        mysqli_query($conn, "UPDATE usuarios SET rol = '$puesto', id_empleado = $idEmpleado WHERE id_usuario = $id");
    }
    header('Location: usuarios.php');
    exit;
}

$clientes = mysqli_query($conn, "SELECT id_usuario, nombre, email, fecha_registro FROM usuarios WHERE rol = 'cliente' ORDER BY nombre");

$titulo = 'Gestionar usuarios';
$activo = 'admin';
require $base . 'includes/header.php';
?>

<a href="index.php" class="volver">&larr; Volver al panel</a>
<h1>Gestionar usuarios</h1>
<p>Acá están los clientes registrados. Para sumar a alguien al personal, elegí su puesto y "Guardar": pasa a la lista de <a href="empleados.php"><u>empleados</u></a>.</p>

<table class="tabla-admin">
    <tr><th>Nombre</th><th>Email</th><th>Registro</th><th>Acciones</th></tr>
    <?php while ($u = mysqli_fetch_assoc($clientes)): ?>
    <tr>
        <td><?= h($u['nombre']) ?></td>
        <td><?= h($u['email']) ?></td>
        <td><?= date('d/m/Y', strtotime($u['fecha_registro'])) ?></td>
        <td>
            <form method="POST" class="form-fila">
                <input type="hidden" name="id_usuario" value="<?= $u['id_usuario'] ?>">
                <select name="puesto">
                    <option>cliente</option>
                    <?php foreach (PUESTOS as $p): ?><option><?= $p ?></option><?php endforeach; ?>
                </select>
                <button class="btn btn-chico">Guardar</button>
                <a class="btn btn-rojo btn-chico" href="?eliminar=<?= $u['id_usuario'] ?>" onclick="return confirm('¿Eliminar esta cuenta?');">Eliminar</a>
            </form>
        </td>
    </tr>
    <?php endwhile; ?>
</table>

<?php require $base . 'includes/footer.php'; ?>

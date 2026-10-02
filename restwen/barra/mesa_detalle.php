<?php
$base = '../';
require $base . 'includes/init.php';
requerirRol(['barra']);

$idMesa = (int)($_GET['id'] ?? 0);
$mesa = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM mesas WHERE id_mesa = $idMesa"));
if (!$mesa) { header('Location: mesas.php'); exit; }

$edicion = isset($_GET['editar']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idDetalle = (int)($_POST['id_detalle'] ?? 0);

    switch ($_POST['accion']) {
        case 'quitar':
            mysqli_query($conn, "DELETE FROM detalle_pedido WHERE id_detalle = $idDetalle");
            break;
        case 'cantidad':
            mysqli_query($conn, 'UPDATE detalle_pedido SET cantidad = ' . max(1, (int)$_POST['cantidad']) . " WHERE id_detalle = $idDetalle");
            break;
        case 'agregar':
            agregarPlato($idMesa, (int)$_POST['id_plato'], max(1, (int)$_POST['cantidad']));
            break;
        case 'pagar':
            mysqli_query($conn, "UPDATE mesas SET estado = 'pagada', personas = 0 WHERE id_mesa = $idMesa");
            mysqli_query($conn, "UPDATE pedidos SET estado = 'pagado' WHERE id_mesa = $idMesa AND estado != 'pagado'");
            break;
        case 'liberar':
            mysqli_query($conn, "UPDATE mesas SET estado = 'libre', nota_especial = NULL WHERE id_mesa = $idMesa");
            break;
        case 'nota':
            $nota = limpiar($_POST['nota_especial']);
            $nota = $nota === '' ? 'NULL' : "'$nota'";
            mysqli_query($conn, "UPDATE mesas SET nota_especial = $nota, personas = " . (int)$_POST['personas'] . " WHERE id_mesa = $idMesa");
            break;
    }
    header('Location: mesa_detalle.php?id=' . $idMesa . ($edicion ? '&editar=1' : ''));
    exit;
}

$items = mysqli_query($conn, 'SELECT d.id_detalle, d.cantidad, d.estado, pl.nombre, pl.precio
                              FROM detalle_pedido d INNER JOIN platos pl ON pl.id_plato = d.id_plato
                              WHERE d.id_pedido = ' . pedidoActivo($idMesa));
$platos = mysqli_query($conn, 'SELECT id_plato, nombre FROM platos ORDER BY categoria, nombre');

$titulo = 'Mesa ' . $mesa['numero'];
$activo = 'mesas';
require $base . 'includes/header.php';
?>

<a href="mesas.php" class="volver">&larr; Volver a las mesas</a>

<div class="cabecera-mesa">
    <h1>Mesa <?= $mesa['numero'] ?></h1>
    <?= badge($mesa['estado']) ?>
    <form method="POST" style="margin-left:auto;">
        <?php if ($mesa['estado'] === 'pagada'): ?>
            <input type="hidden" name="accion" value="liberar">
            <button class="btn btn-secundario">Liberar mesa</button>
        <?php else: ?>
            <input type="hidden" name="accion" value="pagar">
            <button class="btn">Marcar como pagada</button>
        <?php endif; ?>
    </form>
</div>

<div class="info-mesa">
    <span><?= h($mesa['zona']) ?></span>
    <span>Capacidad: <?= $mesa['capacidad'] ?></span>
</div>

<div class="tarjeta nota-especial">
    <form method="POST" class="form-fila">
        <input type="hidden" name="accion" value="nota">
        <div><label>⚠ Nota especial</label><input type="text" name="nota_especial" value="<?= h($mesa['nota_especial']) ?>" placeholder="Sin notas"></div>
        <div><label>Personas</label><input type="number" name="personas" value="<?= (int)$mesa['personas'] ?>" min="0" style="width:70px;"></div>
        <button class="btn btn-chico">Guardar</button>
    </form>
</div>

<h2>Platos de la mesa</h2>

<?php if (mysqli_num_rows($items) === 0): ?>
    <p>Todavía no hay platos cargados para esta mesa.</p>
<?php else: $total = 0; ?>
<table class="tabla-platos">
    <tr><th>Plato</th><th>Cant.</th><th>Precio</th><th>Estado</th><?= $edicion ? '<th></th>' : '' ?></tr>
    <?php while ($i = mysqli_fetch_assoc($items)): $total += $i['cantidad'] * $i['precio']; ?>
    <tr>
        <td><?= h($i['nombre']) ?></td>
        <td>
            <?php if ($edicion): ?>
                <form method="POST" class="form-fila">
                    <input type="hidden" name="accion" value="cantidad">
                    <input type="hidden" name="id_detalle" value="<?= $i['id_detalle'] ?>">
                    <input type="number" name="cantidad" value="<?= $i['cantidad'] ?>" min="1" style="width:60px;">
                    <button class="btn btn-chico">OK</button>
                </form>
            <?php else: ?>
                <?= $i['cantidad'] ?>
            <?php endif; ?>
        </td>
        <td><?= formatearPrecio($i['cantidad'] * $i['precio']) ?></td>
        <td><?= badge($i['estado']) ?></td>
        <?php if ($edicion): ?>
        <td>
            <form method="POST" onsubmit="return confirm('¿Quitar este plato?');">
                <input type="hidden" name="accion" value="quitar">
                <input type="hidden" name="id_detalle" value="<?= $i['id_detalle'] ?>">
                <button class="btn btn-chico btn-rojo">Quitar</button>
            </form>
        </td>
        <?php endif; ?>
    </tr>
    <?php endwhile; ?>
</table>
<p><strong>Total: <?= formatearPrecio($total) ?></strong></p>
<?php endif; ?>

<div class="tarjeta">
    <h3>+ Agregar plato</h3>
    <form method="POST" class="form-fila">
        <input type="hidden" name="accion" value="agregar">
        <div>
            <label>Plato</label>
            <select name="id_plato" required>
                <?php while ($p = mysqli_fetch_assoc($platos)): ?>
                    <option value="<?= $p['id_plato'] ?>"><?= h($p['nombre']) ?></option>
                <?php endwhile; ?>
            </select>
        </div>
        <div>
            <label>Cantidad</label>
            <input type="number" name="cantidad" value="1" min="1" style="width:70px;">
        </div>
        <button class="btn">Agregar</button>
    </form>
</div>

<?php if ($edicion): ?>
    <a href="mesa_detalle.php?id=<?= $idMesa ?>" class="btn btn-outline">Cerrar edición</a>
<?php else: ?>
    <a href="mesa_detalle.php?id=<?= $idMesa ?>&editar=1" class="btn btn-outline">✎ Editar orden</a>
<?php endif; ?>

<?php require $base . 'includes/footer.php'; ?>

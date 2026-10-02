<?php
$base = '../';
require $base . 'includes/init.php';
requerirRol(['mozo']);

// Marcar un pedido como entregado (o deshacerlo)
if (isset($_POST['id_pedido'])) {
    $id = (int)$_POST['id_pedido'];
    $estado = $_POST['entregado'] ? 'entregado' : 'listo';
    $plato = $_POST['entregado'] ? 'servido' : 'en preparacion';
    mysqli_query($conn, "UPDATE pedidos SET estado = '$estado' WHERE id_pedido = $id AND estado != 'pagado'");
    mysqli_query($conn, "UPDATE detalle_pedido SET estado = '$plato' WHERE id_pedido = $id");
    header('Location: pedidos.php');
    exit;
}

$pedidos = mysqli_query($conn, "SELECT p.id_pedido, p.estado, m.numero, m.zona, m.nota_especial
                                FROM pedidos p INNER JOIN mesas m ON m.id_mesa = p.id_mesa
                                WHERE p.estado IN ('listo', 'entregado')
                                ORDER BY p.estado = 'entregado', p.fecha");

$titulo = 'Pedidos';
$activo = 'pedidos';
require $base . 'includes/header.php';
?>

<h1>Pedidos</h1>

<div class="grid-pedidos">
<?php if (mysqli_num_rows($pedidos) === 0): ?>
    <p>No hay pedidos listos para entregar.</p>
<?php endif; ?>

<?php while ($p = mysqli_fetch_assoc($pedidos)): $entregado = $p['estado'] === 'entregado'; ?>
    <div class="tarjeta pedido-card">
        <div class="cabecera-pedido">
            <h2>Mesa <?= $p['numero'] ?></h2>
            <?= badge($p['estado']) ?>
        </div>
        <small><?= h($p['zona']) ?></small>
        <?php if ($p['nota_especial']): ?><p><small>⚠ <?= h($p['nota_especial']) ?></small></p><?php endif; ?>

        <table>
        <?php
        $det = mysqli_query($conn, 'SELECT pl.nombre, d.cantidad FROM detalle_pedido d
                                    INNER JOIN platos pl ON pl.id_plato = d.id_plato
                                    WHERE d.id_pedido = ' . $p['id_pedido']);
        while ($d = mysqli_fetch_assoc($det)):
        ?>
            <tr><td><?= h($d['nombre']) ?></td><td style="text-align:right;">x<?= $d['cantidad'] ?></td></tr>
        <?php endwhile; ?>
        </table>

        <form method="POST">
            <input type="hidden" name="id_pedido" value="<?= $p['id_pedido'] ?>">
            <input type="hidden" name="entregado" value="<?= $entregado ? 0 : 1 ?>">
            <button class="btn <?= $entregado ? 'btn-outline' : '' ?>"><?= $entregado ? 'Quitar entregado' : 'Marcar como entregado' ?></button>
        </form>
    </div>
<?php endwhile; ?>
</div>

<?php require $base . 'includes/footer.php'; ?>

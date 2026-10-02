<?php
$base = '../';
require $base . 'includes/init.php';
requerirRol(['cocina']);

// La cocina avisa que el pedido está listo
if (isset($_POST['id_pedido'])) {
    mysqli_query($conn, "UPDATE pedidos SET estado = 'listo' WHERE estado = 'en preparacion' AND id_pedido = " . (int)$_POST['id_pedido']);
    header('Location: pedidos.php');
    exit;
}

$pedidos = mysqli_query($conn, "SELECT p.id_pedido, m.numero, m.nota_especial
                                FROM pedidos p INNER JOIN mesas m ON m.id_mesa = p.id_mesa
                                WHERE p.estado = 'en preparacion' ORDER BY p.fecha");

$titulo = 'Cocina';
$activo = 'cocina';
require $base . 'includes/header.php';
?>

<h1>Cocina</h1>

<div class="grid-pedidos">
<?php if (mysqli_num_rows($pedidos) === 0): ?>
    <p>No hay pedidos para preparar.</p>
<?php endif; ?>

<?php while ($p = mysqli_fetch_assoc($pedidos)): ?>
    <div class="tarjeta pedido-card">
        <div class="cabecera-pedido">
            <h2>Mesa <?= $p['numero'] ?></h2>
        </div>
        <?php if ($p['nota_especial']): ?><p><small>⚠ <?= h($p['nota_especial']) ?></small></p><?php endif; ?>

        <table>
        <?php
        $det = mysqli_query($conn, "SELECT pl.nombre, d.cantidad FROM detalle_pedido d
                                    INNER JOIN platos pl ON pl.id_plato = d.id_plato
                                    WHERE d.estado = 'en preparacion' AND d.id_pedido = " . $p['id_pedido']);
        while ($d = mysqli_fetch_assoc($det)):
        ?>
            <tr><td><?= h($d['nombre']) ?></td><td style="text-align:right;">x<?= $d['cantidad'] ?></td></tr>
        <?php endwhile; ?>
        </table>

        <form method="POST">
            <input type="hidden" name="id_pedido" value="<?= $p['id_pedido'] ?>">
            <button class="btn">Marcar como listo</button>
        </form>
    </div>
<?php endwhile; ?>
</div>

<?php require $base . 'includes/footer.php'; ?>

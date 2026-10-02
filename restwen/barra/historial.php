<?php
$base = '../';
require $base . 'includes/init.php';
requerirRol(['barra']);

$pedidos = mysqli_query($conn, "SELECT p.fecha, p.estado, m.numero,
        (SELECT SUM(d.cantidad * pl.precio) FROM detalle_pedido d INNER JOIN platos pl ON pl.id_plato = d.id_plato WHERE d.id_pedido = p.id_pedido) AS total
        FROM pedidos p INNER JOIN mesas m ON m.id_mesa = p.id_mesa
        WHERE p.estado IN ('entregado', 'pagado')
        ORDER BY p.fecha DESC LIMIT 100");

$titulo = 'Historial';
$activo = 'historial';
require $base . 'includes/header.php';
?>

<h1>Historial de pedidos</h1>

<?php if (mysqli_num_rows($pedidos) === 0): ?><p>Todavía no hay pedidos finalizados.</p><?php endif; ?>

<?php while ($p = mysqli_fetch_assoc($pedidos)): ?>
    <div class="tarjeta historial-item">
        <div>
            <strong>Mesa <?= $p['numero'] ?></strong>
            <div><small><?= date('d/m/Y H:i', strtotime($p['fecha'])) ?></small></div>
        </div>
        <span><?= formatearPrecio($p['total'] ?? 0) ?></span>
        <?= badge($p['estado']) ?>
    </div>
<?php endwhile; ?>

<?php require $base . 'includes/footer.php'; ?>

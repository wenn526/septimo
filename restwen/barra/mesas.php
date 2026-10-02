<?php
$base = '../';
require $base . 'includes/init.php';
requerirRol(['barra']);

$res = mysqli_query($conn, 'SELECT id_mesa, numero, capacidad, estado, zona FROM mesas ORDER BY zona, numero');
$zonas = [];
$cont = ['ocupada' => 0, 'libre' => 0, 'pagada' => 0];
while ($m = mysqli_fetch_assoc($res)) {
    $zonas[$m['zona']][] = $m;
    $cont[$m['estado']]++;
}

$titulo = 'Mesas';
$activo = 'mesas';
require $base . 'includes/header.php';
?>

<h1>Mesas</h1>

<div class="leyenda">
    <span><span class="punto punto-ocupada"></span> Ocupada</span>
    <span><span class="punto punto-libre"></span> Libre</span>
    <span><span class="punto punto-pagada"></span> Pagada</span>
</div>

<?php foreach ($zonas as $zona => $mesas): ?>
    <h2 class="zona-titulo"><?= h($zona) ?></h2>
    <div class="grid-mesas">
        <?php foreach ($mesas as $m): ?>
            <a href="mesa_detalle.php?id=<?= $m['id_mesa'] ?>" class="mesa-tile <?= $m['estado'] ?>">
                <span><?= $m['numero'] ?></span>
                <small>👥 <?= $m['capacidad'] ?></small>
            </a>
        <?php endforeach; ?>
    </div>
<?php endforeach; ?>

<div class="resumen-mesas">
    <span>Total: <?= array_sum($cont) ?></span>
    <span>Ocupadas: <?= $cont['ocupada'] ?></span>
    <span>Libres: <?= $cont['libre'] ?></span>
    <span>Pagadas: <?= $cont['pagada'] ?></span>
</div>

<?php require $base . 'includes/footer.php'; ?>

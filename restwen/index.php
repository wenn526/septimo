<?php
$base = '';
require 'includes/init.php';

// El personal tiene su propia pantalla
if (estaLogueado() && inicioDe(rolActual()) !== 'index.php') {
    header('Location: ' . inicioDe(rolActual()));
    exit;
}

$yo = (int)($_SESSION['id_usuario'] ?? 0);

// Último pedido de este cliente. Si todavía no lo pagó, sigue usando la misma mesa.
$ultimo = $yo ? mysqli_fetch_assoc(mysqli_query($conn, "SELECT p.id_pedido, p.id_mesa, p.estado, m.numero, m.zona
        FROM pedidos p INNER JOIN mesas m ON m.id_mesa = p.id_mesa
        WHERE p.id_usuario = $yo ORDER BY p.fecha DESC, p.id_pedido DESC LIMIT 1")) : null;
$miMesa = ($ultimo && $ultimo['estado'] !== 'pagado') ? $ultimo : null;

// El cliente envía su pedido
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $yo) {
    $idMesa = $miMesa ? (int)$miMesa['id_mesa'] : (int)$_POST['mesa'];
    $pedido = array_filter(array_map('intval', $_POST['cant'] ?? []), fn($c) => $c > 0);
    $mesaOk = $miMesa || mysqli_num_rows(mysqli_query($conn, "SELECT 1 FROM mesas WHERE id_mesa = $idMesa AND estado = 'libre'")) > 0;

    if ($mesaOk && $pedido) {
        foreach ($pedido as $idPlato => $cant) agregarPlato($idMesa, (int)$idPlato, $cant, $yo);
        header('Location: index.php?enviado=1');
        exit;
    }
    $error = 'Elegí una mesa libre y al menos un plato.';
}

$mensajes = [
    'en preparacion' => 'La cocina está preparando tu pedido.',
    'listo'          => 'Tu pedido está listo, el mozo te lo lleva enseguida.',
    'entregado'      => '¡Buen provecho! Cuando quieras pedí la cuenta en la barra.',
    'pagado'         => 'Pagado. ¡Gracias por venir!',
];

$platos = [];
$res = mysqli_query($conn, 'SELECT * FROM platos ORDER BY nombre');
while ($p = mysqli_fetch_assoc($res)) $platos[$p['categoria']][] = $p;
$mesas = mysqli_query($conn, "SELECT id_mesa, numero, zona FROM mesas WHERE estado = 'libre' ORDER BY zona, numero");

$titulo = 'Restaurante - Menú';
$activo = 'inicio';
require 'includes/header.php';
?>

<section class="hero">
    <h1>BIENVENIDOS A NUESTRO RESTAURANTE</h1>
    <p>Cocina de autor, ingredientes frescos y buena mesa.</p>
</section>

<?php if (isset($_GET['enviado'])): ?><div class="alerta alerta-ok">¡Pedido enviado!</div><?php endif; ?>
<?php if (isset($error)): ?><div class="alerta alerta-error"><?= $error ?></div><?php endif; ?>

<?php if ($ultimo): ?>
<div class="tarjeta">
    <h2>Tu pedido — Mesa <?= $ultimo['numero'] ?> <?= badge($ultimo['estado']) ?></h2>
    <p><?= $mensajes[$ultimo['estado']] ?? '' ?></p>
    <?php
    $detalle = mysqli_query($conn, 'SELECT pl.nombre, d.cantidad FROM detalle_pedido d
                                    INNER JOIN platos pl ON pl.id_plato = d.id_plato
                                    WHERE d.id_pedido = ' . $ultimo['id_pedido']);
    while ($d = mysqli_fetch_assoc($detalle)):
    ?>
        <div class="fila-plato"><span><?= h($d['nombre']) ?></span><span>x<?= $d['cantidad'] ?></span></div>
    <?php endwhile; ?>
    <small>Para ver si cambió el estado, recargá la página.</small>
</div>
<?php endif; ?>

<section id="menu">
    <h1 style="text-align:center;">Menú</h1>
    <?php if (!$yo): ?>
        <p style="text-align:center;"><a href="login.php"><u>Iniciá sesión</u></a> para pedir desde tu mesa.</p>
    <?php endif; ?>

    <form method="POST">
    <?php foreach (['principal', 'guarnicion', 'postre', 'bebida'] as $cat): ?>
        <?php if (empty($platos[$cat])) continue; ?>
        <div class="tarjeta categoria-menu">
            <h2><?= $cat ?></h2>
            <?php foreach ($platos[$cat] as $p): ?>
                <div class="fila-plato">
                    <span><?= h($p['nombre']) ?></span>
                    <span class="precio"><?= formatearPrecio($p['precio']) ?></span>
                    <?php if ($yo): ?>
                    <span class="cant">
                        <button type="button" class="btn btn-chico" onclick="cambiar(this, -1)">−</button>
                        <input type="text" name="cant[<?= $p['id_plato'] ?>]" value="0" data-precio="<?= $p['precio'] ?>" readonly>
                        <button type="button" class="btn btn-chico" onclick="cambiar(this, 1)">+</button>
                    </span>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endforeach; ?>

    <?php if ($yo): ?>
        <div class="tarjeta form-fila">
            <div>
                <label>Tu mesa</label>
                <?php if ($miMesa): ?>
                    <strong>Mesa <?= $miMesa['numero'] ?> (<?= h($miMesa['zona']) ?>)</strong>
                <?php else: ?>
                    <select name="mesa" required>
                        <option value="">Elegí una mesa libre...</option>
                        <?php while ($m = mysqli_fetch_assoc($mesas)): ?>
                            <option value="<?= $m['id_mesa'] ?>">Mesa <?= $m['numero'] ?> (<?= h($m['zona']) ?>)</option>
                        <?php endwhile; ?>
                    </select>
                    <?php if (mysqli_num_rows($mesas) === 0): ?><small>No hay mesas libres por ahora.</small><?php endif; ?>
                <?php endif; ?>
            </div>
            <strong>Total: <span id="total">$0</span></strong>
            <button class="btn"><?= $miMesa ? 'Pedir algo más' : 'Enviar pedido' ?></button>
        </div>
    <?php endif; ?>
    </form>
</section>

<section id="reservas" class="tarjeta" style="text-align:center;">
    <h2>Reservas</h2>
    <p>Contactanos al número <strong>2262555605</strong></p>
</section>

<script>
function cambiar(btn, n) {
    var fila = btn.parentNode.querySelector('input');
    fila.value = Math.max(0, fila.value * 1 + n);
    var total = 0;
    document.querySelectorAll('.cant input').forEach(function (i) { total += i.value * i.dataset.precio; });
    document.getElementById('total').textContent = '$' + total.toLocaleString('es-AR');
}
</script>

<?php require 'includes/footer.php'; ?>

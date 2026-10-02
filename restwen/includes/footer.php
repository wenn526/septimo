</main>

<?php if (count($menu) > 1): ?>
<nav class="nav-inferior-movil">
    <?php foreach ($menu as [$clave, $url, $texto]): ?>
        <a href="<?= $base . $url ?>" class="<?= $activo === $clave ? 'activo' : '' ?>"><?= $texto ?></a>
    <?php endforeach; ?>
</nav>
<?php endif; ?>

<footer class="pie">
    <p>&copy; <?= date('Y') ?> Restaurante — Reservas: 2262555605</p>
</footer>
</body>
</html>

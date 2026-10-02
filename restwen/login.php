<?php
$base = '';
require 'includes/init.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = limpiar($_POST['email']);
    $u = mysqli_fetch_assoc(mysqli_query($conn, "SELECT id_usuario, nombre, password, rol FROM usuarios WHERE email = '$email'"));

    if ($u && password_verify($_POST['password'], $u['password'])) {
        session_regenerate_id(true);
        $_SESSION['id_usuario'] = $u['id_usuario'];
        $_SESSION['nombre'] = $u['nombre'];
        $_SESSION['rol'] = $u['rol'];
        header('Location: ' . inicioDe($u['rol']));
        exit;
    }
    $error = 'Email o contraseña incorrectos.';
}

$titulo = 'Iniciar sesión';
require 'includes/header.php';
?>

<div class="tarjeta form-caja">
    <h1>Iniciar sesión</h1>
    <?php if ($error): ?><div class="alerta alerta-error"><?= $error ?></div><?php endif; ?>

    <form method="POST">
        <label>Email</label>
        <input type="email" name="email" required>
        <label>Contraseña</label>
        <input type="password" name="password" required>
        <button class="btn">Ingresar</button>
    </form>
    <p>¿No tenés cuenta? <a href="registro.php"><u>Registrate</u></a></p>
</div>

<?php require 'includes/footer.php'; ?>

<?php
$base = '';
require 'includes/init.php';

$error = $exito = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = limpiar($_POST['nombre']);
    $email = limpiar($_POST['email']);
    $pass = $_POST['password'];

    if ($nombre === '' || $email === '') {
        $error = 'Completá todos los campos.';
    } elseif (strlen($pass) < 6) {
        $error = 'La contraseña debe tener al menos 6 caracteres.';
    } elseif ($pass !== $_POST['password2']) {
        $error = 'Las contraseñas no coinciden.';
    } elseif (mysqli_num_rows(mysqli_query($conn, "SELECT 1 FROM usuarios WHERE email = '$email'")) > 0) {
        $error = 'Ese email ya está registrado.';
    } else {
        $hash = password_hash($pass, PASSWORD_DEFAULT);
        mysqli_query($conn, "INSERT INTO usuarios (nombre, email, password, rol) VALUES ('$nombre', '$email', '$hash', 'cliente')");
        $exito = 'Cuenta creada. Ya podés iniciar sesión.';
    }
}

$titulo = 'Registro';
require 'includes/header.php';
?>

<div class="tarjeta form-caja">
    <h1>Crear cuenta</h1>
    <?php if ($error): ?><div class="alerta alerta-error"><?= $error ?></div><?php endif; ?>
    <?php if ($exito): ?><div class="alerta alerta-ok"><?= $exito ?> <a href="login.php"><u>Ingresar</u></a></div><?php endif; ?>

    <form method="POST">
        <label>Nombre completo</label>
        <input type="text" name="nombre" required>
        <label>Email</label>
        <input type="email" name="email" required>
        <label>Contraseña</label>
        <input type="password" name="password" required minlength="6">
        <label>Repetir contraseña</label>
        <input type="password" name="password2" required minlength="6">
        <button class="btn">Registrarme</button>
    </form>
    <p>¿Ya tenés cuenta? <a href="login.php"><u>Iniciá sesión</u></a></p>
</div>

<?php require 'includes/footer.php'; ?>

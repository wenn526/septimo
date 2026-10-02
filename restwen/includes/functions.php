<?php
// Funciones que se usan en todo el proyecto

function limpiar($v)
{
    global $conn;
    return mysqli_real_escape_string($conn, trim($v));
}

// Escapa texto para mostrarlo en pantalla
function h($v) { return htmlspecialchars($v ?? ''); }

function estaLogueado() { return isset($_SESSION['id_usuario']); }
function rolActual() { return $_SESSION['rol'] ?? null; }

// Puestos (= roles del personal)
const PUESTOS = ['mozo', 'barra', 'cocina', 'admin'];

// Página principal de cada rol
function inicioDe($rol)
{
    $inicios = ['mozo' => 'mozo/pedidos.php', 'barra' => 'barra/mesas.php', 'cocina' => 'cocina/pedidos.php', 'admin' => 'admin/index.php'];
    return $inicios[$rol] ?? 'index.php';
}

// El admin entra a todo. Los demás solo a los roles indicados.
function requerirRol($roles)
{
    global $base;
    if (!estaLogueado()) {
        header('Location: ' . $base . 'login.php?error=acceso');
        exit;
    }
    if (rolActual() !== 'admin' && !in_array(rolActual(), $roles)) {
        header('Location: ' . $base . inicioDe(rolActual()));
        exit;
    }
}

function formatearPrecio($n) { return '$' . number_format($n, 0, ',', '.'); }

// Etiqueta de color para cualquier estado (mesa, pedido o plato)
function badge($estado)
{
    $clases = ['en preparacion' => 'preparacion', 'servido' => 'listo'];
    return '<span class="badge badge-' . ($clases[$estado] ?? $estado) . '">' . ucfirst($estado) . '</span>';
}

// Id del pedido abierto de una mesa (0 si no hay)
function pedidoActivo($idMesa)
{
    global $conn;
    $r = mysqli_fetch_assoc(mysqli_query($conn, "SELECT id_pedido FROM pedidos WHERE id_mesa = $idMesa AND estado != 'pagado' ORDER BY fecha DESC LIMIT 1"));
    return $r['id_pedido'] ?? 0;
}

// Agrega un plato al pedido de la mesa (lo crea si no existe)
function agregarPlato($idMesa, $idPlato, $cantidad, $idUsuario = 0)
{
    global $conn;
    $idPedido = pedidoActivo($idMesa);
    if (!$idPedido) {
        mysqli_query($conn, "INSERT INTO pedidos (id_mesa, estado, id_usuario) VALUES ($idMesa, 'en preparacion', " . ($idUsuario ?: 'NULL') . ")");
        $idPedido = mysqli_insert_id($conn);
        mysqli_query($conn, "UPDATE mesas SET estado = 'ocupada' WHERE id_mesa = $idMesa");
    } else {
        mysqli_query($conn, "UPDATE pedidos SET estado = 'en preparacion' WHERE id_pedido = $idPedido");
    }
    mysqli_query($conn, "INSERT INTO detalle_pedido (id_pedido, id_plato, cantidad, estado) VALUES ($idPedido, $idPlato, $cantidad, 'en preparacion')");
}

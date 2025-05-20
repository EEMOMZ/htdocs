<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit;
}

// Eliminar artículo del carrito
if (isset($_POST['eliminar']) && isset($_POST['indice'])) {
    $indice = (int)$_POST['indice'];
    if (isset($_SESSION['carrito'][$indice])) {
        array_splice($_SESSION['carrito'], $indice, 1);
    }
}

$carrito = isset($_SESSION['carrito']) ? $_SESSION['carrito'] : [];
$total = 0;
foreach ($carrito as $item) {
    $total += $item['precio'];
}
require_once "conexion.php";

if (isset($_POST['pagar']) && !empty($_SESSION['carrito'])) {
    $usuario = $_SESSION['usuario'];
    $fecha = date('Y-m-d H:i:s');
    require_once "conexion.php";
    $ok = true;

    foreach ($_SESSION['carrito'] as $item) {
        $referencia_producto = $item['referencia'];
        $precio_comprado = $item['precio'];
        $sql = "INSERT INTO compras (usuario, referencia_producto, fecha, precio_comprado) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sisd", $usuario, $referencia_producto, $fecha, $precio_comprado);
        if (!$stmt->execute()) {
            $ok = false;
        }
        $stmt->close();
    }

    if ($ok) {
        // Guardar los datos del último pedido para el recibo
        $_SESSION['ultimo_pedido'] = [
            'usuario' => $usuario,
            'fecha' => $fecha,
            'productos' => $_SESSION['carrito']
        ];
        $_SESSION['mensaje_pedido'] = "¡Compra realizada con éxito!";
        $_SESSION['carrito'] = [];
        header("Location: carrito.php");
        exit;
    } else {
        $mensaje = "Error al registrar la compra. Intenta de nuevo.";
    }
}



// Nueva lógica para descargar el XML del último pedido
if (isset($_POST['descargar_xml']) && isset($_SESSION['ultimo_pedido'])) {
    $pedido = $_SESSION['ultimo_pedido'];
    $xml = new SimpleXMLElement('<pedido/>');
    $xml->addChild('usuario', htmlspecialchars($pedido['usuario']));
    $xml->addChild('fecha', $pedido['fecha']);
    $productosXml = $xml->addChild('productos');
    foreach ($pedido['productos'] as $item) {
        $prod = $productosXml->addChild('producto');
        $prod->addChild('nombre', htmlspecialchars($item['nombre']));
        $prod->addChild('tipo', htmlspecialchars($item['tipo']));
        $prod->addChild('juego', htmlspecialchars($item['juego']));
        $prod->addChild('precio', number_format($item['precio'], 2, '.', ''));
        $prod->addChild('referencia', $item['referencia']);
    }
    $xmlString = $xml->asXML();

    header('Content-Type: application/xml');
    header('Content-Disposition: attachment; filename="pedido_' . date('Ymd_His') . '.xml"');
    echo $xmlString;
    unset($_SESSION['ultimo_pedido']); 
    exit;
}

if (isset($_SESSION['mensaje_pedido'])) {
    $mensaje = $_SESSION['mensaje_pedido'];
    unset($_SESSION['mensaje_pedido']);
}
$mostrarRecibo = isset($_SESSION['ultimo_pedido']);

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Carrito de compras - TCG Kingdom</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="carrito.css">
    <style>
        .table-dark {
            --bs-table-bg: #232826;
            --bs-table-striped-bg: #232826;
            --bs-table-hover-bg: #232826;
            color: #aeea00;
        }
        .table th, .table td {
            vertical-align: middle;
        }
        .btn-lima {
            background: linear-gradient(90deg, #aeea00 60%, #8fd400 100%);
            color: #181c1b;
            border: none;
        }
        .btn-lima:hover {
            background: linear-gradient(90deg, #c6ff4a 60%, #aeea00 100%);
            color: #232826;
        }
    </style>
</head>
<body>
    <div class="header-top">
        <span class="usuario">Usuario: <?php echo htmlspecialchars($_SESSION['usuario']); ?></span>
        <div class="centro-tienda">
            <img src="ImagenesProductos/Logotienda.png" alt="Logo TCG Kingdom" class="logo-tienda">
            <span class="nombre-tienda">TCG KINGDOM</span>
        </div>
        <div class="header-botones">
            <form action="tienda.php" method="get" style="display:inline;">
                <button type="submit" class="btn btn-lima ms-2">Volver a la tienda</button>
            </form>
            <form action="logout.php" method="post" style="display:inline;">
                <button type="submit" class="btn btn-lima ms-2">Cerrar sesión</button>
            </form>
        </div>
    </div>
    <h1 class="text-center my-4" style="color:#aeea00;">Carrito de compras</h1>

    <div class="container my-4">
        <div class="card bg-dark shadow-lg p-4" style="max-width:800px; margin:0 auto; border:1px solid #aeea00;">
            <?php if (!empty($mensaje)): ?>
                <div class="alert alert-success text-center mb-4">
                    <?php echo $mensaje; ?>
                </div>
            <?php endif; ?>

            <?php if ($mostrarRecibo): ?>
                <form method="post" class="d-flex justify-content-end mb-3">
                    <button type="submit" name="descargar_xml" class="btn btn-outline-light btn-lg" style="border:1px solid #aeea00; color:#aeea00;">Descargar Recibo</button>
                </form>
            <?php endif; ?>

            <?php if (empty($carrito)): ?>
                <div class="alert alert-warning text-center">El carrito está vacío.</div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-dark table-hover align-middle rounded-3 overflow-hidden">
                        <thead>
                            <tr>
                                <th>Producto</th>
                                <th>Tipo</th>
                                <th>Juego</th>
                                <th>Precio</th>
                                <th>Eliminar</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($carrito as $i => $item): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($item['nombre']); ?></td>
                                <td><?php echo htmlspecialchars($item['tipo']); ?></td>
                                <td><?php echo htmlspecialchars($item['juego']); ?></td>
                                <td>$<?php echo number_format($item['precio'], 2); ?></td>
                                <td>
                                    <form method="post" class="d-inline">
                                        <input type="hidden" name="indice" value="<?php echo $i; ?>">
                                        <button type="submit" name="eliminar" class="btn btn-danger btn-sm">Eliminar</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="d-flex justify-content-end align-items-center mt-3">
                    <h3 class="mb-0" style="color:#aeea00;">Total: $<?php echo number_format($total, 2); ?></h3>
                </div>
                <form method="post" class="d-flex justify-content-end gap-2 mt-3">
                    <button type="submit" name="pagar" class="btn btn-lima btn-lg">Pagar</button>
                </form>
            <?php endif; ?>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<style>
/* Logo de la tienda en la cabecera */
.logo-tienda {
    height: 48px;
    width: auto;
    margin-right: 18px;
    margin-left: 4px;
    vertical-align: middle;
    display: block;
}
.header-top {
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.nombre-tienda {
    display: flex;
    align-items: center;
    font-size: 1.35em;
    font-weight: bold;
    color: #aeea00;
    letter-spacing: 2px;
    font-family: 'Segoe UI', Arial, sans-serif;
    text-shadow: 0 2px 8px #23282688;
    flex: 1;
    text-align: left;
}
</style>
<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit;
}
require_once "conexion.php";

// Inicializar carrito si no existe
if (!isset($_SESSION['carrito'])) {
    $_SESSION['carrito'] = [];
}

// Añadir producto al carrito
if (isset($_POST['agregar'])) {
    $ref = $_POST['referencia'];
    $sql = "SELECT * FROM productos WHERE referencia = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $ref);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($prod = $res->fetch_assoc()) {
        $_SESSION['carrito'][] = $prod;
    }
    $stmt->close();
}

// Filtros
$juegos = ['Pokemon', 'MTG', 'Yu-Gi-Oh'];
$tipos = ['Mazo', 'Sobre', 'Bundle'];

$filtro_juego = isset($_GET['juego']) ? $_GET['juego'] : '';
$filtro_tipo = isset($_GET['tipo']) ? $_GET['tipo'] : '';

// Consulta con filtros
$sql = "SELECT * FROM productos WHERE 1";
$params = [];
$types = "";

if ($filtro_juego && in_array($filtro_juego, $juegos)) {
    $sql .= " AND juego = ?";
    $params[] = $filtro_juego;
    $types .= "s";
}
if ($filtro_tipo && in_array($filtro_tipo, $tipos)) {
    $sql .= " AND tipo = ?";
    $params[] = $filtro_tipo;
    $types .= "s";
}

$stmt = $conn->prepare($sql);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$res = $stmt->get_result();
$productos = [];
while ($row = $res->fetch_assoc()) {
    $productos[] = $row;
}
$stmt->close();
$conn->close();

// Calcular total del carrito
$total = 0;
foreach ($_SESSION['carrito'] as $item) {
    $total += $item['precio'];
}
?>

<?php
$seccion = isset($_GET['seccion']) ? $_GET['seccion'] : '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>TCG Kingdom - Tienda</title>
    <!-- Bootstrap primero -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- CSS después -->
    <link rel="stylesheet" href="tienda.css">
    <style>
        .card {
            background: var(--gris-oscuro) !important;
            border: 1px solid var(--lima) !important;
            color: #e6ffe6 !important;
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
            <form action="carrito.php" method="get" style="display:inline;">
                <button type="submit" class="nav-btn">Carrito (<?php echo count($_SESSION['carrito']); ?>)</button>
            </form>
            <form action="logout.php" method="post" style="display:inline;">
                <button type="submit" class="nav-btn">Cerrar sesión</button>
            </form>
        </div>
    </div>
    <div class="header-bottom">
        <form action="tienda.php" method="get" style="display:inline;">
            <button type="submit" class="nav-btn">Productos</button>
        </form>
        <form action="tienda.php" method="get" style="display:inline;">
            <input type="hidden" name="seccion" value="quienes_somos">
            <button type="submit" class="nav-btn">Quiénes somos</button>
        </form>
        <form action="tienda.php" method="get" style="display:inline;">
            <input type="hidden" name="seccion" value="faq">
            <button type="submit" class="nav-btn">FAQ</button>
        </form>
    </div>

<?php
if ($seccion === 'quienes_somos') {
?>
    <h1 class="text-center mb-4" style="color: var(--lima);">¿QUIÉNES SOMOS?</h1>
    <div class="container my-5 quienes-somos">
        <div class="bg-dark rounded-4 shadow p-4" style="max-width: 700px; margin: 0 auto; color: #e6ffe6; border: 1px solid var(--lima);">
            <p>
                En TCG Kingdom creemos que los juegos de cartas coleccionables no son solo un pasatiempo, sino una forma de conectar personas, contar historias y vivir aventuras. Somos una empresa asturiana nacida del entusiasmo de un grupo de amigos que, entre partidas caseras de Magic: The Gathering, Pokémon y Yu-Gi-Oh!, decidieron dar un paso más allá y crear algo propio: un espacio donde todo el mundo pudiera descubrir este universo sin barreras ni complicaciones.

                Nuestro equipo está formado por jugadores de toda la vida, coleccionistas apasionados y frikis orgullosos que conocen de primera mano lo que significa abrir un sobre, montar un mazo o preparar una quedada para jugar el finde. Queríamos llevar esa experiencia a más gente, con una tienda clara, cercana, sin líos técnicos ni precios inflados, donde tanto novatos como veteranos se sintieran bienvenidos.

                Desde nuestra base en Asturias, trabajamos cada día para ofrecer un catálogo actualizado con lo mejor de Magic, Pokémon y Yu-Gi-Oh!, incluyendo mazos preconstruidos, sobres, bundles, accesorios y más. Además, apostamos por una atención personalizada: si no sabes por dónde empezar, te ayudamos; si tienes dudas sobre qué producto te conviene, te asesoramos; si ya estás metido hasta el cuello en el vicio, te seguimos el ritmo.

                Pero TCG Kingdom es más que una tienda. Es un proyecto comunitario. Queremos ser un punto de encuentro para jugadores, organizar eventos, torneos, quedadas y, por supuesto, seguir compartiendo nuestra afición como lo hacíamos desde el principio: con pasión, risas y muchas cartas encima de la mesa.

                Porque jugar debería ser fácil, divertido y para todos. Y si es con colegas, mejor.
                Bienvenido a TCG Kingdom, tu nuevo reino del cartón.
            </p>
        </div>
    </div>
</body>
</html>
<?php
    exit;
}
?>

<?php
if ($seccion === 'faq') {
?>
    <h1 class="text-center mb-4" style="color: var(--lima);">FAQ</h1>
    <div class="container my-5 faq-section">
        <div class="bg-dark rounded-4 shadow p-4" style="max-width: 700px; margin: 0 auto; color: #e6ffe6; border: 1px solid var(--lima);">
            <ol class="list-group list-group-numbered">
                <li class="list-group-item bg-dark text-light border-0 mb-3">
                    <strong>¿Qué métodos de pago aceptáis?</strong><br>
                    Aceptamos tarjetas de crédito y débito (Visa, MasterCard, Maestro), PayPal y Bizum. También puedes pagar por transferencia bancaria, aunque el pedido no se procesará hasta que se confirme el ingreso.
                </li>
                <li class="list-group-item bg-dark text-light border-0 mb-3">
                    <strong>¿Hacéis envíos a toda España?</strong><br>
                    Sí, realizamos envíos a todo el territorio nacional, incluidas Islas Canarias, Baleares, Ceuta y Melilla. También podemos hacer envíos internacionales a varios países de Europa. Escríbenos si quieres confirmar disponibilidad para tu país.
                </li>
                <li class="list-group-item bg-dark text-light border-0 mb-3">
                    <strong>¿Cuánto tarda en llegar mi pedido?</strong><br>
                    Los pedidos se preparan en un plazo de 24-48 horas laborables. Una vez enviado, el plazo de entrega es de 2 a 4 días laborables para la Península. Para envíos a islas o internacionales, el plazo puede variar entre 4 y 10 días.
                </li>
                <li class="list-group-item bg-dark text-light border-0 mb-3">
                    <strong>¿Cuál es el coste del envío?</strong><br>
                    El envío estándar cuesta 4,90 € para la Península. Para Baleares, Canarias, Ceuta y Melilla, el coste varía según el peso.<br>
                    ¡Envío gratis para pedidos superiores a 50 € en la Península!
                </li>
                <li class="list-group-item bg-dark text-light border-0 mb-3">
                    <strong>¿Puedo recoger mi pedido en persona?</strong><br>
                    De momento no disponemos de tienda física, pero estamos trabajando para abrir un punto de recogida local en Asturias. ¡Os mantendremos informados!
                </li>
                <li class="list-group-item bg-dark text-light border-0 mb-3">
                    <strong>¿Puedo devolver un producto si no estoy satisfecho?</strong><br>
                    Sí, aceptamos devoluciones dentro de los 14 días naturales desde la recepción del pedido, siempre que el producto esté sin abrir y en perfecto estado. Solo tienes que escribirnos y te explicamos el proceso.
                </li>
                <li class="list-group-item bg-dark text-light border-0 mb-3">
                    <strong>¿Vendéis productos en preventa?</strong><br>
                    Sí, solemos ofrecer productos en preventa con unos días o semanas de antelación. Si compras un artículo en preventa, lo enviaremos tan pronto como esté disponible. Las fechas de salida pueden cambiar según el distribuidor.
                </li>
                <li class="list-group-item bg-dark text-light border-0 mb-3">
                    <strong>¿Los productos son originales?</strong><br>
                    Por supuesto. Todos nuestros productos son 100 % oficiales y adquiridos directamente a distribuidores autorizados. Nada de cartas falsas ni reediciones raras.
                </li>
                <li class="list-group-item bg-dark text-light border-0 mb-3">
                    <strong>¿Tenéis descuentos para pedidos grandes o grupos?</strong><br>
                    Si formas parte de una tienda, asociación o simplemente hacéis pedidos grandes entre varios, contáctanos por email y te preparamos una propuesta personalizada.
                </li>
                <li class="list-group-item bg-dark text-light border-0 mb-3">
                    <strong>¿Puedo regalar una tarjeta o hacer un pedido como regalo?</strong><br>
                    Sí, ofrecemos tarjetas regalo digitales y puedes dejar una nota personalizada en tu pedido si es para alguien especial. ¡Nos encargamos de que quede bonito!
                </li>
            </ol>
        </div>
    </div>
</body>
</html>
<?php
    exit;
}
?>

<h1 class="text-center my-4" style="color: var(--lima);">Bienvenido a TCG Kingdom, el Reino de las Cartas</h1>
<div class="filtros card shadow mb-4 p-3" style="max-width: 500px; margin: 0 auto; background: var(--gris-oscuro); border: 1px solid var(--lima);">
    <form method="get" action="tienda.php" class="row g-3">
        <div class="col-12">
            <label for="juego" class="form-label" style="color: var(--lima);">Juego:</label>
            <select name="juego" id="juego" class="form-select bg-dark text-light border-0" style="background:#2d332f;">
                <option value="">Todos</option>
                <?php foreach ($juegos as $j): ?>
                    <option value="<?php echo $j; ?>" <?php if ($filtro_juego == $j) echo 'selected'; ?>><?php echo $j; ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-12">
            <label for="tipo" class="form-label" style="color: var(--lima);">Tipo de producto:</label>
            <select name="tipo" id="tipo" class="form-select bg-dark text-light border-0" style="background:#2d332f;">
                <option value="">Todos</option>
                <?php foreach ($tipos as $t): ?>
                    <option value="<?php echo $t; ?>" <?php if ($filtro_tipo == $t) echo 'selected'; ?>><?php echo $t; ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-12 d-grid">
            <button type="submit" class="btn btn-success" style="background: linear-gradient(90deg, var(--lima) 60%, #8fd400 100%); color: var(--gris-mas-oscuro); border:none;">Aplicar filtros</button>
        </div>
    </form>
</div>
<div>
    <?php if (empty($productos)): ?>
        <p>No hay productos disponibles con los filtros seleccionados.</p>
    <?php else: ?>
        <?php foreach ($productos as $prod): ?>
            <div class="producto">
                <img src="<?php echo htmlspecialchars($prod['imagen']); ?>" alt="<?php echo htmlspecialchars($prod['nombre']); ?>" style="max-width:100%;max-height:140px;display:block;margin:0 auto 10px auto;border-radius:8px;">
                <strong><?php echo htmlspecialchars($prod['nombre']); ?></strong><br>
                Juego: <?php echo htmlspecialchars($prod['juego']); ?><br>
                Tipo: <?php echo htmlspecialchars($prod['tipo']); ?><br>
                Precio: $<?php echo number_format($prod['precio'], 2); ?><br>
                <form method="post">
                    <input type="hidden" name="referencia" value="<?php echo $prod['referencia']; ?>">
                    <button type="submit" name="agregar">Añadir al carrito</button>
                </form>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
</body>
</html>
<?php
session_start();
$mensaje = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    require_once "conexion.php"; // Incluye la conexión

    $usuario = $_POST['usuario'];
    $contrasena = $_POST['contrasena'];

    if (empty($usuario) || empty($contrasena)) {
        $mensaje = "Por favor, completa todos los campos.";
    } else {
        $sql = "SELECT contrasena FROM usuarios WHERE usuario = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $usuario);
        $stmt->execute();
        $resultado = $stmt->get_result();

        if ($resultado->num_rows === 1) {
            $fila = $resultado->fetch_assoc();
            if (password_verify($contrasena, $fila['contrasena'])) {
                $_SESSION['usuario'] = $usuario;
                $_SESSION['carrito'] = []; // Vacía el carrito al iniciar sesión con otro usuario
                header("Location: tienda.php");
                exit;
            } else {
                $mensaje = "Contraseña incorrecta.";
            }
        } else {
            $mensaje = "El usuario no existe.";
        }
    }

    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>TCG Kingdom - Login</title>
    <!-- Bootstrap-->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="login.css">
</head>
<body>
    <div class="container d-flex justify-content-center align-items-center min-vh-100">
        <div class="login-container shadow-lg">
            <h2 class="bienvenida mb-4 text-center">Bienvenido a <span style="color:#bfff00;">TCG KINGDOM</span></h2>
            <?php if (!empty($mensaje)) echo "<div class='alert alert-danger text-center py-2'>$mensaje</div>"; ?>
            <form method="POST" action="" class="form-login w-100">
                <div class="mb-3">
                    <label class="form-label">Usuario:</label>
                    <input type="text" name="usuario" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Contraseña:</label>
                    <input type="password" name="contrasena" class="form-control" required>
                </div>
                <div class="d-flex gap-2 mt-3">
                    <button type="submit" class="btn btn-success flex-fill" style="background: linear-gradient(90deg, #bfff00 60%, #6aff00 100%); color: #181c1b; border:none;">Entrar</button>
                    <a href="registro.php" class="btn btn-outline-light flex-fill" style="border:1px solid #bfff00; color:#bfff00;">Registrarse</a>
                </div>
            </form>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
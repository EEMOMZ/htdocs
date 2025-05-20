<?php
session_start();
$mensaje = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    require_once "conexion.php";

    // Recoger datos del formulario
    $usuario = trim($_POST['usuario']);
    $contrasena = $_POST['contrasena'];
    $nombre = trim($_POST['nombre']);
    $apellidos = trim($_POST['apellidos']);
    $correo = trim($_POST['correo']);
    $fecha_nacimiento = $_POST['fecha_nacimiento'];
    $genero = $_POST['genero'];

    // Validación básica
    if (
        empty($usuario) || empty($contrasena) || empty($nombre) ||
        empty($apellidos) || empty($correo) || empty($fecha_nacimiento) || empty($genero)
    ) {
        $mensaje = "Por favor, completa todos los campos.";
    } else {
        // Comprobar si el usuario ya existe
        $sql = "SELECT usuario FROM usuarios WHERE usuario = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $usuario);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $mensaje = "El nombre de usuario ya está registrado.";
        } else {
            // Insertar en usuarios
            $hash = password_hash($contrasena, PASSWORD_DEFAULT);
            $sql1 = "INSERT INTO usuarios (usuario, contrasena) VALUES (?, ?)";
            $stmt1 = $conn->prepare($sql1);
            $stmt1->bind_param("ss", $usuario, $hash);

            // Insertar en clientes
            $sql2 = "INSERT INTO clientes (usuario, nombre, apellidos, correo, fecha_nacimiento, genero) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt2 = $conn->prepare($sql2);
            $stmt2->bind_param("ssssss", $usuario, $nombre, $apellidos, $correo, $fecha_nacimiento, $genero);

            if ($stmt1->execute() && $stmt2->execute()) {
                $mensaje = "Registro exitoso. Ahora puedes iniciar sesión.";
            } else {
                $mensaje = "Error al registrar. Intenta de nuevo.";
            }
            $stmt1->close();
            $stmt2->close();
        }
        $stmt->close();
    }
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>TCG Kingdom - Registro</title>
    <!-- Bootstrap -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="registro.css">
</head>
<body>
    <div class="container d-flex justify-content-center align-items-center min-vh-100">
        <div class="registro-container shadow-lg">
            <h2 class="titulo-registro text-center mb-4">Registro en TCG KINGDOM</h2>
            <?php if (!empty($mensaje)) echo "<div class='alert alert-info text-center py-2'>$mensaje</div>"; ?>
            <form method="POST" action="" class="form-registro w-100">
                <div class="mb-3">
                    <label class="form-label">Usuario:</label>
                    <input type="text" name="usuario" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Nombre:</label>
                    <input type="text" name="nombre" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Apellidos:</label>
                    <input type="text" name="apellidos" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Email:</label>
                    <input type="email" name="correo" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Contraseña:</label>
                    <input type="password" name="contrasena" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Fecha de nacimiento:</label>
                    <input type="date" name="fecha_nacimiento" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Género:</label>
                    <select name="genero" class="form-select" required>
                        <option value="">Selecciona...</option>
                        <option value="Hombre">Hombre</option>
                        <option value="Mujer">Mujer</option>
                        <option value="Otro">Otro</option>
                        <option value="Prefiero no decirlo">Prefiero no decirlo</option>
                    </select>
                </div>
                <div class="d-flex gap-2 mt-3">
                    <button type="submit" class="btn btn-success flex-fill" style="background: linear-gradient(90deg, #aeea00 60%, #8fd400 100%); color: #181c1b; border:none;">Registrarse</button>
                    <a href="login.php" class="btn btn-outline-light flex-fill" style="border:1px solid #aeea00; color:#aeea00;">Volver</a>
                </div>
            </form>
        </div>
    </div>
    <!-- Bootstrap -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
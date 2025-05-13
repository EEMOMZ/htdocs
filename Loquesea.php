<?php
// Incluir la conexión a la base de datos
include 'C:\xampp\htdocs\conexion.php';

// Manejar la inserción de datos
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
    $nombre = $_POST['nombre'];
    $apellidos = $_POST['apellidos'];
    $correo = $_POST['correo'];
    $fecha_nacimiento = $_POST['fecha_nacimiento'];
    $genero = $_POST['genero'];

    $sql = "INSERT INTO usuarios (nombre, apellidos, correo, fecha_nacimiento, genero) 
            VALUES ('$nombre', '$apellidos', '$correo', '$fecha_nacimiento', '$genero')";

    if ($conn->query($sql) === TRUE) {
        echo "<p>Datos insertados correctamente.</p>";
    } else {
        echo "<p>Error al insertar datos: " . $conn->error . "</p>";
    }
}

//Comeme el manjar
// Manejar la descarga del XML
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['download_xml'])) {
    header('Content-Type: text/xml');
    header('Content-Disposition: attachment; filename="usuarios.xml"');

    $sql = "SELECT * FROM usuarios";
    $result = $conn->query($sql);

    $xml = new SimpleXMLElement('<usuarios/>');

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $usuario = $xml->addChild('usuario');
            $usuario->addChild('id', $row['id']);
            $usuario->addChild('nombre', $row['nombre']);
            $usuario->addChild('apellidos', $row['apellidos']);
            $usuario->addChild('correo', $row['correo']);
            $usuario->addChild('fecha_nacimiento', $row['fecha_nacimiento']);
            $usuario->addChild('genero', $row['genero']);
        }
    }

    echo $xml->asXML();
    exit;
}

// Obtener los datos de la tabla
$sql = "SELECT * FROM usuarios";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formulario de Usuarios</title>
</head>
<body>
    <h1>Formulario de Usuarios</h1>
    <form method="POST" action="">
        <label for="nombre">Nombre:</label>
        <input type="text" id="nombre" name="nombre" required><br><br>

        <label for="apellidos">Apellidos:</label>
        <input type="text" id="apellidos" name="apellidos" required><br><br>

        <label for="correo">Correo:</label>
        <input type="email" id="correo" name="correo" required><br><br>

        <label for="fecha_nacimiento">Fecha de Nacimiento:</label>
        <input type="date" id="fecha_nacimiento" name="fecha_nacimiento" required><br><br>

        <label for="genero">Género:</label>
        <select id="genero" name="genero" required>
            <option value="Masculino">Masculino</option>
            <option value="Femenino">Femenino</option>
            <option value="Otro">Otro</option>
        </select><br><br>

        <button type="submit" name="submit">Enviar</button>
    </form>

    <form method="POST" action="">
        <button type="submit" name="download_xml">Descargar XML</button>
    </form>

    <h2>Lista de Usuarios</h2>
    <table border="1">
        <tr>
            <th>ID</th>
            <th>Nombre</th>
            <th>Apellidos</th>
            <th>Correo</th>
            <th>Fecha de Nacimiento</th>
            <th>Género</th>
        </tr>
        <?php
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "<tr>
                        <td>{$row['id']}</td>
                        <td>{$row['nombre']}</td>
                        <td>{$row['apellidos']}</td>
                        <td>{$row['correo']}</td>
                        <td>{$row['fecha_nacimiento']}</td>
                        <td>{$row['genero']}</td>
                      </tr>";
            }
        } else {
            echo "<tr><td colspan='6'>No hay usuarios registrados.</td></tr>";
        }
        ?>
    </table>
</body>
</html>
<?php
$con = mysqli_connect("localhost", "php", "", "cae");

if (!$con) {
    die("Error de conexión: " . mysqli_connect_error());
}

$mensaje = "";
$tipo = "";
$registros = false;

// Recoger y guardar los datos enviados por el formulario
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nombre = $_POST["nombre"] ?? "";
    $apellidos = $_POST["apellidos"] ?? "";
    $dni = $_POST["dni"] ?? "";
    $f_nac = $_POST["f_nac"] ?? "";
    $tlf = $_POST["tlf"] ?? "";
    $email = $_POST["email"] ?? "";
    $profesion = $_POST["profesion"] ?? "";
    $jornadaParcial = $_POST["jornadaParcial"] ?? "";
    $idiomas = "";

    if (isset($_POST["idioma_ingles"])) {
        $idiomas = "Ingles";
    }
    if (isset($_POST["idioma_euskara"])) {
        if ($idiomas !== "") {
            $idiomas .= ", ";
        }
        $idiomas .= "Euskara";
    }

    if ($nombre === "" || $apellidos === "" || $dni === "" || $f_nac === "" ||
        $tlf === "" || $email === "" || $profesion === "" || $jornadaParcial === "") {
        $mensaje = "Comprueba que todos los campos obligatorios sean correctos.";
    } elseif ($profesion !== "soldadura" && $profesion !== "informatica" &&
        $profesion !== "asistencia-sociosanitaria") {
        $mensaje = "La profesión seleccionada no es válida.";
    } elseif ($jornadaParcial !== "0" && $jornadaParcial !== "1") {
        $mensaje = "Selecciona un tipo de jornada válido.";
    } else {
        $sql_insert = "INSERT INTO solicitud (nombre, apellidos, dni, f_nac, tlf, email, profesion, jornadaParcial, idiomas)
        VALUES ('$nombre', '$apellidos', '$dni', '$f_nac', '$tlf', '$email', '$profesion', $jornadaParcial, '$idiomas')";

        if (mysqli_query($con, $sql_insert)) {
            $mensaje = "Solicitud registrada correctamente.";
        } else {
            $mensaje = "Error al insertar la solicitud: " . mysqli_error($con);
        }
    }
} elseif (isset($_GET["tipo"])) {
    // Elegir las solicitudes que se mostrarán según el botón pulsado
    if ($_GET["tipo"] === "soldadura") {
        $tipo = "Soldadura";
        $profesion = "soldadura";
    } elseif ($_GET["tipo"] === "informatica") {
        $tipo = "Informática";
        $profesion = "informatica";
    } elseif ($_GET["tipo"] === "socio") {
        $tipo = "Asistencia Sociosanitaria";
        $profesion = "asistencia-sociosanitaria";
    } else {
        $mensaje = "El ámbito solicitado no es válido.";
    }

    if ($tipo !== "") {
        $registros = mysqli_query(
            $con,
            "SELECT nombre, apellidos, dni, f_nac, tlf, email, profesion, jornadaParcial, idiomas
            FROM solicitud WHERE profesion = '$profesion'"
        );

        if (!$registros) {
            $mensaje = "Error al consultar solicitudes: " . mysqli_error($con);
        }
    }
} else {
    $mensaje = "Selecciona un ámbito o envía una solicitud desde el formulario.";
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Solicitudes de empleo</title>
    <link rel="stylesheet" href="../estilos/estilos.css">
</head>
<body>
    <h1>Centro de Ayuda al Empleo</h1>

    <?php
    if ($tipo !== "") {
        echo "<h2>Solicitudes de $tipo</h2>";
    }

    if ($mensaje !== "") {
        echo "<p>" . htmlspecialchars($mensaje, ENT_QUOTES, "UTF-8") . "</p>";
    }

    if ($registros) {
        if (mysqli_num_rows($registros) === 0) {
            echo "<p>No hay solicitudes para este ámbito.</p>";
        } else {
            echo "<table>";
            echo "<tr>";
            echo "<th>Nombre</th>";
            echo "<th>Apellidos</th>";
            echo "<th>DNI</th>";
            echo "<th>Fecha de nacimiento</th>";
            echo "<th>Teléfono</th>";
            echo "<th>Email</th>";
            echo "<th>Profesión</th>";
            echo "<th>Jornada parcial</th>";
            echo "<th>Idiomas</th>";
            echo "</tr>";

            while ($reg = mysqli_fetch_array($registros)) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($reg["nombre"], ENT_QUOTES, "UTF-8") . "</td>";
                echo "<td>" . htmlspecialchars($reg["apellidos"], ENT_QUOTES, "UTF-8") . "</td>";
                echo "<td>" . htmlspecialchars($reg["dni"], ENT_QUOTES, "UTF-8") . "</td>";
                echo "<td>" . htmlspecialchars($reg["f_nac"], ENT_QUOTES, "UTF-8") . "</td>";
                echo "<td>" . htmlspecialchars($reg["tlf"], ENT_QUOTES, "UTF-8") . "</td>";
                echo "<td>" . htmlspecialchars($reg["email"], ENT_QUOTES, "UTF-8") . "</td>";
                echo "<td>" . htmlspecialchars($reg["profesion"], ENT_QUOTES, "UTF-8") . "</td>";
                echo "<td>" . ($reg["jornadaParcial"] ? "Sí" : "No") . "</td>";
                echo "<td>" . htmlspecialchars($reg["idiomas"], ENT_QUOTES, "UTF-8") . "</td>";
                echo "</tr>";
            }

            echo "</table>";
        }
    }
    ?>

    <button type="button" onclick="location.href='../html/index.html'">Volver al formulario</button>
</body>
</html>
<?php
// Cerrar conexión
mysqli_close($con);
?>

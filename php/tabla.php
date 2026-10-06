<?php
// Conectar con la base de datos
$con = mysqli_connect("localhost", "php", "", "cae");

// Comprobar conexión
if (!$con) {
    die("Error de conexión: " . mysqli_connect_error());
}

// Variables para mostrar mensajes y resultados
$mensaje = "";
$tipo = "";
$registros = false;

// Comprobar si la petición es post, Post -> envio formulario / Get -> consulta de profesiones
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Recoger los datos del formulario
    $nombre = $_POST["nombre"] ?? "";
    $apellidos = $_POST["apellidos"] ?? "";
    $dni = $_POST["dni"] ?? "";
    $f_nac = $_POST["f_nac"] ?? "";
    $tlf = $_POST["tlf"] ?? "";
    $email = $_POST["email"] ?? "";
    $profesion = $_POST["profesion"] ?? "";
    $jornadaParcial = $_POST["jornadaParcial"] ?? "";

    // Cadena para guardar los idiomas
    $idiomas = "";

    // Añadir inglés si la casilla está marcada
    if (isset($_POST["idioma_ingles"])) {
        $idiomas = "Ingles";
    }

    // Añadir euskera y separarlo de inglés si ya estaba seleccionado
    if (isset($_POST["idioma_euskara"])) {
        if ($idiomas != "") {
            $idiomas .= ", ";
        }
        $idiomas .= "Euskara";
    }

    // Comprobar campos obligatorios
    if ($nombre == "" || $apellidos == "" || $dni == "" || $f_nac == "" ||
        $tlf == "" || $email == "" || $profesion == "" || $jornadaParcial == "") {
        $mensaje = "Comprueba que todos los campos obligatorios sean correctos.";
    // Aceptar únicamente las profesiones disponibles
    } elseif ($profesion != "soldadura" && $profesion != "informatica" &&
        $profesion != "asistencia-sociosanitaria") {
        $mensaje = "La profesión seleccionada no es válida.";
    // Aceptar únicamente los valores jornada parcial y completa
    } elseif ($jornadaParcial != "0" && $jornadaParcial != "1") {
        $mensaje = "Selecciona un tipo de jornada válido.";
    } else {
        // Preparar la insert
        $sql_insert = "INSERT INTO solicitud (nombre, apellidos, dni, f_nac, tlf, email, profesion, jornadaParcial, idiomas)
        VALUES ('$nombre', '$apellidos', '$dni', '$f_nac', '$tlf', '$email', '$profesion', $jornadaParcial, '$idiomas')";

        // Ejecutar la insert
        if (mysqli_query($con, $sql_insert)) {
            $mensaje = "Solicitud registrada correctamente.";
        } else {
            $mensaje = "Error al insertar la solicitud: " . mysqli_error($con);
        }
    }
// Si no es post, comprobar get (consultas de profesiones)
} elseif (isset($_GET["tipo"])) {
    // Mapear el tipo recibido a su nombre visible y valor de BD
    if ($_GET["tipo"] == "soldadura") {
        $tipo = "Soldadura"; // tipo para los textos
        $profesion = "soldadura"; // profesion para la bd
    } elseif ($_GET["tipo"] == "informatica") {
        $tipo = "Informática";
        $profesion = "informatica";
    } elseif ($_GET["tipo"] == "socio") {
        $tipo = "Asistencia Sociosanitaria";
        $profesion = "asistencia-sociosanitaria";
    } else {
        $mensaje = "El ámbito solicitado no es válido.";
    }

    // Filtrar los resultados
    if ($tipo != "") {
        $registros = mysqli_query(
            $con,
            "SELECT nombre, apellidos, dni, f_nac, tlf, email, profesion, jornadaParcial, idiomas
            FROM solicitud WHERE profesion = '$profesion'"
        );

        // Guardar un mensaje de error si la consulta falla
        if (!$registros) {
            $mensaje = "Error al consultar solicitudes: " . mysqli_error($con);
        }
    }
} else {
    // Informar cuando no se ha enviado el formulario ni elegido un ámbito
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
    // Mostrar el nombre del ámbito consultado
    if ($tipo != "") {
        echo "<h2>Solicitudes de $tipo</h2>";
    }

    // Mostrar los mensajes de resultado
    if ($mensaje != "") {
        echo "<p>" . $mensaje . "</p>";
    }

    // Mostrar la tabla, comprobando el resultado de mysql
    if ($registros) {
        // Informar si la consulta no ha encontrado datos
        if (mysqli_num_rows($registros) == 0) {
            echo "<p>No hay solicitudes para este ámbito.</p>";
        } else {
            // Crear la tabla y su fila de encabezados.
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

            // Recorrer los resultados y printearlos
            while ($reg = mysqli_fetch_array($registros)) {
                echo "<tr>";
                echo "<td>" . $reg["nombre"] . "</td>";
                echo "<td>" . $reg["apellidos"] . "</td>";
                echo "<td>" . $reg["dni"] . "</td>";
                echo "<td>" . $reg["f_nac"] . "</td>";
                echo "<td>" . $reg["tlf"] . "</td>";
                echo "<td>" . $reg["email"] . "</td>";
                echo "<td>" . $reg["profesion"] . "</td>";
                echo "<td>" . ($reg["jornadaParcial"] ? "Sí" : "No") . "</td>";
                echo "<td>" . $reg["idiomas"] . "</td>";
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
    // Cerrar la conexión
    mysqli_close($con);
?>

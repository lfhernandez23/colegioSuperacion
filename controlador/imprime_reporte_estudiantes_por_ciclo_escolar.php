<?php
ob_start();

if (htmlspecialchars($_GET["jornada"] === "diario-matutino")) {
    $imprime_jornada = "Plan Diario - Matutino";
} else if (htmlspecialchars($_GET["jornada"] === "diario-vespertino")) {
    $imprime_jornada = "Plan Diario - Vespertino";
} else if (htmlspecialchars($_GET["jornada"] === "fin-de-semana")) {
    $imprime_jornada = "Plan Fin de Semana - Sabatino";
}

if (htmlspecialchars($_GET["nivel"] === "preprimaria")) {
    $imprime_nivel = "Pre-Primaria";
} else if (htmlspecialchars($_GET["nivel"] === "primaria")) {
    $imprime_nivel = "Primaria";
} else if (htmlspecialchars($_GET["nivel"] === "basico")) {
    $imprime_nivel = "Básico";
} else if (htmlspecialchars($_GET["nivel"] === "basicopormadurez")) {
    $imprime_nivel = "Básico por Madurez";
} else if (htmlspecialchars($_GET["nivel"] === "diversificado1")) {
    $imprime_nivel = "Perito Contador con Orientacion en Computación";
} else if (htmlspecialchars($_GET["nivel"] === "diversificado2")) {
    $imprime_nivel = "Perito en Administración de Empresa";
} else if (htmlspecialchars($_GET["nivel"] === "diversificado3")) {
    $imprime_nivel = "Bachiller en Computación con Orientacion Comercial";
} else if (htmlspecialchars($_GET["nivel"] === "diversificado4")) {
    $imprime_nivel = "Bachiller en Ciencias y Letra por Madurez";
}


if (htmlspecialchars($_GET["grado"] === "1grado")) {
    $imprime_grado = "1er Grado";
} else if (htmlspecialchars($_GET["grado"] === "2grado")) {
    $imprime_grado = "2do Grado";
} else if (htmlspecialchars($_GET["grado"] === "3grado")) {
    $imprime_grado = "3er Grado";
} else if (htmlspecialchars($_GET["grado"] === "4grado")) {
    $imprime_grado = "4to Grado";
} else if (htmlspecialchars($_GET["grado"] === "5grado")) {
    $imprime_grado = "5to Grado";
} else if (htmlspecialchars($_GET["grado"] === "6grado")) {
    $imprime_grado = "6to Grado";
}

$imprime_ciclo_escolar = htmlspecialchars($_GET["ciclo_escolar"]);


?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Estudiantes</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 10px; /* Reducir el tamaño de la fuente global */
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9px; /* Reducir tamaño de texto dentro de la tabla */
        }

        table,
        th,
        td {
            border: 1px solid black;
        }

        th,
        td {
            padding: 5px; /* Reducir padding para hacer las celdas más compactas */
            text-align: center;
        }

        th {
            background-color: #f2f2f2;
            font-size: 10px; /* Mantener el encabezado un poco más grande */
        }

        td {
            font-size: 9px; /* Reducir texto en las celdas */
        }
    </style>
</head>

<body>
    <h2>Listado de Estudiantes</h2>
    <p><strong> Ciclo Escolar: </strong> <?php echo htmlspecialchars($imprime_ciclo_escolar); ?></p>
    <table>
        <tr>
            <th>N°</th>
            <th>Carnet</th>
            <th>Código</th>
            <th>Apellido</th>
            <th>Nombre</th>
            <th>Jornada</th>
            <th>Nivel</th>
            <th>Grado</th>
        </tr>
        <?php
        include_once "../controlador/conexion_bd.php";

        // Obtener parámetros de la URL
        $jornada = $_GET["jornada"] ?? "";
        $nivel = $_GET["nivel"] ?? "";
        $grado = $_GET["grado"] ?? "";
        $ciclo_escolar = $_GET["ciclo_escolar"] ?? "";

        // Consulta SQL para obtener todos los estudiantes del ciclo escolar
        $consulta = "SELECT * FROM registro_estudiantes WHERE ciclo_escolar = ? ORDER BY jornada ASC, nivel ASC, grado ASC, apellido ASC, nombre ASC";
        $sentencia = $conn->prepare($consulta);

        if ($sentencia) {
            // Vincular solo el ciclo escolar
            $sentencia->bind_param("i", $ciclo_escolar);

            $sentencia->execute();

            // Obtener resultados
            $resultados = $sentencia->get_result();

            // Mostrar los datos de los estudiantes encontrados en la consulta
            if ($resultados && $resultados->num_rows > 0) {
                $contador_inscritos = 0;
                function traducirJornada($jornada)
                {
                    switch ($jornada) {
                        case "diario-matutino":
                            return "Plan Diario - Matutino";
                        case "diario-vespertino":
                            return "Plan Diario - Vespertino";
                        case "fin-de-semana":
                            return "Plan Fin de Semana - Sabatino";
                        default:
                            return $jornada;
                    }
                }

                function traducirNivel($nivel)
                {
                    switch ($nivel) {
                        case "preprimaria":
                            return "Pre-Primaria";
                        case "primaria":
                            return "Primaria";
                        case "basico":
                            return "Básico";
                        case "basicopormadurez":
                            return "Básico por Madurez";
                        case "diversificado1":
                            return "Perito Contador con Orientación en Computación";
                        case "diversificado2":
                            return "Perito en Administración de Empresa";
                        case "diversificado3":
                            return "Bachiller en Computación con Orientación Comercial";
                        case "diversificado4":
                            return "Bachiller en Ciencias y Letras por Madurez";
                        default:
                            return $nivel;
                    }
                }

                function traducirGrado($grado)
                {
                    switch ($grado) {
                        case "1grado":
                            return "1er Grado";
                        case "2grado":
                            return "2do Grado";
                        case "3grado":
                            return "3er Grado";
                        case "4grado":
                            return "4to Grado";
                        case "5grado":
                            return "5to Grado";
                        case "6grado":
                            return "6to Grado";
                        default:
                            return $grado;
                    }
                }

                while ($fila = $resultados->fetch_assoc()) {
                    $contador_inscritos++;
                    echo "<tr>";
                    echo "<td>$contador_inscritos</td>";
                    echo "<td>" . $fila["carnet"] . "</td>";
                    echo "<td>" . $fila["codigo"] . "</td>";
                    echo "<td>" . $fila["apellido"] . "</td>";
                    echo "<td>" . $fila["nombre"] . "</td>";
                    echo "<td>" . traducirJornada($fila["jornada"]) . "</td>";
                    echo "<td>" . traducirNivel($fila["nivel"]) . "</td>";
                    echo "<td>" . traducirGrado($fila["grado"]) . "</td>";

                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='5'>No se encontraron estudiantes para los criterios de búsqueda seleccionados.</td></tr>";
            }

            // Cerrar la sentencia
            $sentencia->close();
        } else {
            echo "<tr><td colspan='5'>Error en la preparación de la consulta: " . $conn->error . "</td></tr>";
        }

        // Cerrar la conexión
        $conn->close();
        ?>
    </table>
    <?php
    if (isset($contador_inscritos)) {
        echo "<br>Estudiantes inscritos: " . $contador_inscritos;
    }
    ?>
</body>

</html>

<?php
$html = ob_get_clean();

require_once "../library/dompdf/autoload.inc.php";

use Dompdf\Dompdf;

$dompdf = new Dompdf();

$dompdf->loadHtml($html);

// Definimos el tamaño del papel
$dompdf->setPaper('A4', 'landscape');

// Renderizamos el PDF
$dompdf->render();

// Descargamos el PDF
$dompdf->stream('reporte_estudiantes.pdf', array("Attachment" => false));
?>
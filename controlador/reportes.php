<?php
ob_start();

if( htmlspecialchars($_GET["jornada"] === "diario-matutino")) {
    $imprime_jornada = "Plan Diario - Matutino";
} else if ( htmlspecialchars($_GET["jornada"] === "diario-vespertino")) {
    $imprime_jornada = "Plan Diario - Vespertino";
} else if ( htmlspecialchars($_GET["jornada"] === "fin-de-semana")) {
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
        table {
            width: 100%;
            border-collapse: collapse;
        }
        table, th, td {
            border: 1px solid black;
        }
        th, td {
            padding: 8px;
            text-align: center;
        }
        th {
            background-color: #f2f2f2;
        }
    </style>
</head>

<body>
    <h2>Listado de Estudiantes</h2>
    <p><strong>Jornada: </strong> <?php echo htmlspecialchars($imprime_jornada); ?><strong>     Nivel: </strong> <?php echo htmlspecialchars($imprime_nivel); ?> <br><strong>      Grado: </strong> <?php echo htmlspecialchars($imprime_grado); ?> <strong> Ciclo Escolar: </strong> <?php echo htmlspecialchars($imprime_ciclo_escolar); ?></p>
    <table>
        <tr>
            <th>N°</th>
            <th>Carnet</th>
            <th>Código</th>
            <th>Apellido</th>
            <th>Nombre</th>            
        </tr>
        <?php
        include_once "../controlador/conexion_bd.php";

        // Obtener parámetros de la URL
        $jornada = $_GET["jornada"] ?? "";
        $nivel = $_GET["nivel"] ?? "";
        $grado = $_GET["grado"] ?? "";        
        $ciclo_escolar = $_GET["ciclo_escolar"] ?? "";

        // Consulta SQL para obtener la información de los estudiantes según los parámetros de la URL
        $consulta = "SELECT * FROM registro_estudiantes WHERE jornada = ? AND nivel = ? AND grado = ?  AND ciclo_escolar = ? ORDER BY apellido ASC";
        $sentencia = $conn->prepare($consulta);

        if ($sentencia) {
            // Vincular los parámetros y ejecutar la consulta
            $sentencia->bind_param("sssi", $jornada, $nivel, $grado, $ciclo_escolar);
            $sentencia->execute();

            // Obtener resultados
            $resultados = $sentencia->get_result();

            // Mostrar los datos de los estudiantes encontrados en la consulta
            if ($resultados && $resultados->num_rows > 0) {
                $contador_inscritos = 0;
                while ($fila = $resultados->fetch_assoc()) {
                    $contador_inscritos++;
                    echo "<tr>";
                    echo "<td>$contador_inscritos</td>";
                    echo "<td>" . $fila["carnet"] . "</td>";
                    echo "<td>" . $fila["codigo"] . "</td>";
                    echo "<td>" . $fila["apellido"] . "</td>"; 
                    echo "<td>" . $fila["nombre"] . "</td>";                                       
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
$dompdf->setPaper('A4', 'portrait');

// Renderizamos el PDF
$dompdf->render();

// Descargamos el PDF
$dompdf->stream('reporte_estudiantes.pdf', array("Attachment" => false));
?>

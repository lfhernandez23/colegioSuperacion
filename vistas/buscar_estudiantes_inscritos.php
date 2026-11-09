<?php require_once __DIR__ . '/../controlador/auth.php'; colegio_requerir_sesion(); ?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/inscripcionstyle.css" />
    <link rel="stylesheet" href="../css/historialstyle.css" />
    <link rel="stylesheet" href="../css/navstyle.css" />
    <link rel="stylesheet" href="../css/reportesstyle.css" />
    <title>Formulario de Búsqueda</title>
  <link rel="stylesheet" href="../css/ui-standard.css">
</head>

<body>
<?php include './header.php'; ?>

    <div class="container">
        <h2>Historial de Estudiantes Inscritos por Grado</h2>
        <h2>Formulario de Búsqueda</h2>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">

            <label for="jornada">Jornada</label>
            <select id="jornada" name="jornada" required onchange="actualizarNivel()">
                <option value="diario-matutino" selected>Plan Diario - Matutino</option>
                <option value="diario-vespertino">Plan Diario - Vespertino</option>
                <option value="fin-de-semana">Plan Fin de Semana - Sabatino</option>
            </select>

            <label for="nivel">Nivel Asignado</label>
            <select id="nivel" name="nivel" required>
                <!-- Opciones inicialmente vacías -->
            </select>

            <label for="grado">Grado Asignado</label>
            <select id="grado" name="grado" required>
                <!-- Opciones inicialmente vacías -->
            </select>

            <label for="ciclo_escolar">Ciclo Escolar:</label>
            <input type="text" id="ciclo_escolar" name="ciclo_escolar" required><br><br>
            <button type="submit" name="buscar">Buscar</button>
        </form>

        <?php
        $contador_inscritos = 0; // Inicializa el contador de inscritos

        if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["buscar"])) {
            $jornada = $_POST["jornada"];
            $nivel = $_POST["nivel"];
            $grado = $_POST["grado"];
            $ciclo_escolar = $_POST["ciclo_escolar"];

            include_once "../controlador/conexion_bd.php";

            // Consulta SQL para obtener la información de los estudiantes según la jornada, grado y año seleccionados
            $consulta = "SELECT * FROM registro_estudiantes WHERE jornada = ? AND nivel = ? AND grado = ?  AND ciclo_escolar = ? ORDER BY apellido ASC";

            $sentencia = $conn->prepare($consulta);

            if ($sentencia) {
                // Vincular los parámetros y ejecutar la consulta
                $sentencia->bind_param("sssi", $jornada, $nivel, $grado, $ciclo_escolar); // Corregido el tipo de parámetro para nivel
                $sentencia->execute();

                // Obtener resultados
                $resultados = $sentencia->get_result();

                // Cerrar la sentencia
                $sentencia->close();
            } else {
                die("Error en la preparación de la consulta: " . $conn->error);
            }

            // Cerrar la conexión
            $conn->close();

            // Mostrar la tabla con la información de los estudiantes
            if (isset($resultados) && $resultados->num_rows > 0) {
                echo "<h2>Información de Estudiantes</h2>";
                if (isset($_POST["buscar"])) {
                    $jornada = $_POST["jornada"];
                    $nivel = $_POST["nivel"];
                    $grado = $_POST["grado"];
                    $ciclo_escolar = $_POST["ciclo_escolar"];
                    echo "<a href='../controlador/reportes.php?jornada=" . urlencode($jornada) . "&nivel=" . urlencode($nivel)  . "&grado=" . urlencode($grado) . "&ciclo_escolar=" . urlencode($ciclo_escolar) . "'>Imprimir Reporte</a>";
                    echo "<br>";
                    echo "<br>";
                }

                echo "<table>";
                echo "<tr><th>N°</th><th>Carnet</th><th>Código</th><th>Nombre</th><th>Apellido</th><th>Jornada</th><th>Nivel</th><th>Grado</th></tr>";
                while ($fila = $resultados->fetch_assoc()) {
                    // Incrementa el contador de inscritos en cada iteración
                    $contador_inscritos++;
                    echo "<tr>";
                    echo "<td>$contador_inscritos</td>";
                    echo "<td>" . $fila["carnet"] . "</td>";
                    echo "<td>" . $fila["codigo"] . "</td>";
                    echo "<td>" . $fila["nombre"] . "</td>";
                    echo "<td>" . $fila["apellido"] . "</td>";

                    if ($fila["jornada"] === "diario-matutino") {
                        $fila["jornada"] = "Plan Diario - Matutino";
                    } else if ($fila["jornada"] === "diario-vespertino") {
                        $fila["jornada"] = "Plan Diario - Vespertino";
                    } else if ($fila["jornada"] === "fin-de-semana") {
                        $fila["jornada"] = "Plan Fin de Semana - Sabatino";
                    }
                    echo "<td>" . $fila["jornada"] . "</td>";

                    if ($fila["nivel"] === "preprimaria") {
                        $fila["nivel"] = "Pre-Primaria";
                    } else if ($fila["nivel"] === "primaria") {
                        $fila["nivel"] = "Primaria";
                    } else if ($fila["nivel"] === "basico") {
                        $fila["nivel"] = "Básico";
                    } else if ($fila["nivel"] === "basicopormadurez") {
                        $fila["nivel"] = "Básico por Madurez";
                    } else if ($fila["nivel"] === "diversificado1") {
                        $fila["nivel"] = "Perito Contador con Orientacion en Computación";
                    } else if ($fila["nivel"] === "diversificado2") {
                        $fila["nivel"] = "Perito en Administración de Empresa";
                    } else if ($fila["nivel"] === "diversificado3") {
                        $fila["nivel"] = "Bachiller en Computación con Orientacion Comercial";
                    } else if ($fila["nivel"] === "diversificado4") {
                        $fila["nivel"] = "Bachiller en Ciencias y Letra por Madurez";
                    }
                    echo "<td>" . $fila["nivel"] . "</td>";

                    if ($fila["grado"] === "1grado") {
                        $fila["grado"] = "1er Grado";
                    } else if ($fila["grado"] === "2grado") {
                        $fila["grado"] = "2do Grado";
                    } else if ($fila["grado"] === "3grado") {
                        $fila["grado"] = "3er Grado";
                    } else if ($fila["grado"] === "4grado") {
                        $fila["grado"] = "4to Grado";
                    } else if ($fila["grado"] === "5grado") {
                        $fila["grado"] = "5to Grado";
                    } else if ($fila["grado"] === "6grado") {
                        $fila["grado"] = "6to Grado";
                    }
                    echo "<td>" . $fila["grado"] . "</td>";
                    // Calcula la cantidad total de estudiantes y muestreala en esta celda            
                    echo "</tr>";
                }
                echo "</table>";
                echo "<br>";
                echo "Estudiantes inscritos: " . $contador_inscritos;
            } else {
                echo "<br>";
                echo "No se encontraron estudiantes para los criterios de búsqueda seleccionados.";
            }
        }
        ?>


    </div>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            actualizarNivel(); // Llamar a la función una vez que se cargue la página

            // Asegúrate de actualizar el grado cuando el nivel cambie
            document.getElementById("nivel").addEventListener("change", actualizarGrado);
        });

        function actualizarNivel() {
            var jornada = document.getElementById("jornada").value;
            var nivelSelect = document.getElementById("nivel");

            // Eliminar opciones anteriores
            nivelSelect.innerHTML = "";

            // Añadir opciones según la jornada seleccionada
            if (jornada === "diario-matutino") {
                agregarOpcionNivel("- Seleccione una Opcion - ", "noselecciono", nivelSelect);
                agregarOpcionNivel("Pre-Primaria", "preprimaria", nivelSelect);
                agregarOpcionNivel("Primaria", "primaria", nivelSelect);
                agregarOpcionNivel("Básico", "basico", nivelSelect);
                agregarOpcionNivel("Bachiller en Computación con Orientacion Comercial", "diversificado3", nivelSelect);
                agregarOpcionNivel("Perito Contador con Orientacion en Computación", "diversificado1", nivelSelect);
                agregarOpcionNivel("Perito en Administración de Empresa", "diversificado2", nivelSelect);
            } else if (jornada === "diario-vespertino") {
                agregarOpcionNivel("- Seleccione una Opcion - ", "noselecciono", nivelSelect);
                agregarOpcionNivel("Pre-Primaria", "preprimaria", nivelSelect);
                agregarOpcionNivel("Primaria", "primaria", nivelSelect);
                agregarOpcionNivel("Básico", "basico", nivelSelect);
                agregarOpcionNivel("Bachiller en Computación con Orientacion Comercial", "diversificado3", nivelSelect);
                agregarOpcionNivel("Perito Contador con Orientacion en Computación", "diversificado1", nivelSelect);
                agregarOpcionNivel("Perito en Administración de Empresa", "diversificado2", nivelSelect);
            } else if (jornada === "fin-de-semana") {
                agregarOpcionNivel("- Seleccione una Opcion - ", "noselecciono", nivelSelect);
                agregarOpcionNivel("Básico", "basico", nivelSelect);
                agregarOpcionNivel("Básico por Madurez", "basicopormadurez", nivelSelect);
                agregarOpcionNivel("Bachiller en Ciencias y Letra por Madurez", "diversificado4", nivelSelect);
                agregarOpcionNivel("Perito Contador con Orientacion en Computación", "diversificado1", nivelSelect);
            }
        }

        function agregarOpcionNivel(texto, valor, select) {
            var opcion = document.createElement("option");
            opcion.text = texto;
            opcion.value = valor;
            select.add(opcion);
        }

        function actualizarGrado() {
            var nivel = document.getElementById("nivel").value;
            var gradoSelect = document.getElementById("grado");

            // Eliminar opciones anteriores
            gradoSelect.innerHTML = "";

            // Ajuste de niveles por grado  
            if (nivel === "preprimaria") {
                agregarOpcionGrado("1er Grado", "1grado", gradoSelect);
                agregarOpcionGrado("2do Grado", "2grado", gradoSelect);
                agregarOpcionGrado("3er Grado", "3grado", gradoSelect);
            } else if (nivel === "primaria") {
                agregarOpcionGrado("1er Grado", "1grado", gradoSelect);
                agregarOpcionGrado("2do Grado", "2grado", gradoSelect);
                agregarOpcionGrado("3er Grado", "3grado", gradoSelect);
                agregarOpcionGrado("4to Grado", "4grado", gradoSelect);
                agregarOpcionGrado("5to Grado", "5grado", gradoSelect);
                agregarOpcionGrado("6to Grado", "6grado", gradoSelect);
            } else if (nivel === "basico") {
                agregarOpcionGrado("1er Grado", "1grado", gradoSelect);
                agregarOpcionGrado("2do Grado", "2grado", gradoSelect);
                agregarOpcionGrado("3er Grado", "3grado", gradoSelect);
            } else if (nivel === "basicopormadurez") {
                agregarOpcionGrado("1er Grado", "1grado", gradoSelect);
                agregarOpcionGrado("2do Grado", "2grado", gradoSelect);
            } else if (nivel === "diversificado1") {
                agregarOpcionGrado("4to Grado", "4grado", gradoSelect);
                agregarOpcionGrado("5to Grado", "5grado", gradoSelect);
                agregarOpcionGrado("6to Grado", "6grado", gradoSelect);
            } else if (nivel === "diversificado2") {
                agregarOpcionGrado("4to Grado", "4grado", gradoSelect);
                agregarOpcionGrado("5to Grado", "5grado", gradoSelect);
                agregarOpcionGrado("6to Grado", "6grado", gradoSelect);
            } else if (nivel === "diversificado3") {
                agregarOpcionGrado("4to Grado", "4grado", gradoSelect);
                agregarOpcionGrado("5to Grado", "5grado", gradoSelect);
            } else if (nivel === "diversificado4") {
                agregarOpcionGrado("4to Grado", "4grado", gradoSelect);;
            }
        }

        function agregarOpcionGrado(texto, valor, select) {
            var opcion = document.createElement("option");
            opcion.text = texto;
            opcion.value = valor;
            select.add(opcion); // Corrige el error tipográfico aquí
        }
    </script>
</body>

</html>
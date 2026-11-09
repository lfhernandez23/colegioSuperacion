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
    <title>Listado de Estudiantes</title>
    <style>
        .search-form {
            max-width: 400px;
            /* Ancho máximo reducido */
            margin: 20px auto;
            /* Centrado horizontal y separación vertical */
            padding: 15px;
            border: 1px solid #ccc;
            border-radius: 8px;
            background-color: #f9f9f9;
            font-size: 14px;
            /* Fuente un poco más pequeña */
        }

        .search-form label,
        .search-form select,
        .search-form input,
        .search-form button {
            display: block;
            width: 100%;
            margin-bottom: 10px;
        }

        .search-form button {
            padding: 8px;
            background-color: #4CAF50;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .search-form button:hover {
            background-color: #45a049;
        }
    </style>

  <link rel="stylesheet" href="../css/ui-standard.css">
</head>

<body>
<?php include './header.php'; ?>


    <br><br><br><br><br><br>
    <h2 class="page-title">Listado de Estudiantes</h2>

    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" class="search-form">
        <div class="form-group">
            <label for="jornada">Jornada</label>
            <select id="jornada" name="jornada" required onchange="actualizarNivel()">
                <option value="diario-matutino" selected>Plan Diario - Matutino</option>
                <option value="diario-vespertino">Plan Diario - Vespertino</option>
                <option value="fin-de-semana">Plan Fin de Semana - Sabatino</option>
            </select>
        </div>

        <div class="form-group">
            <label for="nivel">Nivel Asignado</label>
            <select id="nivel" name="nivel" required>
                <!-- Opciones iniciales vacías -->
            </select>
        </div>

        <div class="form-group">
            <label for="grado">Grado Asignado</label>
            <select id="grado" name="grado" required>
                <!-- Opciones iniciales vacías -->
            </select>
        </div>

        <div class="form-group">
            <label for="ciclo_escolar">Ciclo Escolar:</label>
            <input type="text" id="ciclo_escolar" name="ciclo_escolar" required>
        </div>

        <button type="submit" name="buscar" class="search-button">Buscar</button>
    </form>

    <?php
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["buscar"])) {
        $jornada = $_POST["jornada"];
        $nivel = $_POST["nivel"];
        $grado = $_POST["grado"];
        $ciclo_escolar = $_POST["ciclo_escolar"];


        include_once "../controlador/conexion_bd.php";

        // Consulta SQL para obtener la información de los estudiantes
        $consulta = "SELECT * FROM registro_estudiantes WHERE jornada = ? AND nivel = ? AND grado = ? AND ciclo_escolar = ? ORDER BY nombre ASC";
        $sentencia = $conn->prepare($consulta);

        if ($sentencia) {
            // Vincular los parámetros y ejecutar la consulta
            $sentencia->bind_param("ssss", $jornada, $nivel, $grado, $ciclo_escolar);
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

        // Mapeo de valores de Jornada
        $mapa_jornada = [
            "diario-matutino" => "Plan Diario - Matutino",
            "diario-vespertino" => "Plan Diario - Vespertino",
            "fin-de-semana" => "Plan Fin de Semana - Sabatino"
        ];

        // Mapeo de valores de Nivel
        $mapa_nivel = [
            "preprimaria" => "Pre-Primaria",
            "primaria" => "Primaria",
            "basico" => "Básico",
            "basicopormadurez" => "Básico por Madurez",
            "diversificado1" => "Perito Contador con Orientación en Computación",
            "diversificado2" => "Perito en Administración de Empresas",
            "diversificado3" => "Bachiller en Computación con Orientación Comercial",
            "diversificado4" => "Bachiller en Ciencias y Letras por Madurez"
        ];

        // Mapeo de valores de Grado
        $mapa_grado = [
            "1grado" => "1er Grado",
            "2grado" => "2do Grado",
            "3grado" => "3er Grado",
            "4grado" => "4to Grado",
            "5grado" => "5to Grado",
            "6grado" => "6to Grado"
        ];

        // Aplicar transformación
        $jornada = $mapa_jornada[$jornada] ?? $jornada;
        $nivel = $mapa_nivel[$nivel] ?? $nivel;
        $grado = $mapa_grado[$grado] ?? $grado;

        // Mostrar la tabla con la información de los estudiantes
        if ($resultados && $resultados->num_rows > 0) {
            ob_start(); // Iniciar el almacenamiento en búfer de salida
            echo "<div style='width: 100%; text-align: center;'>";  // Contenedor centrado
            echo "<h2 class='students-title'>Información de Estudiantes</h2>";
            echo "Colegio Privado Mixto Superación - Boleta de Calificaciones  $ciclo_escolar";
            echo "<br><br>";
            echo "<strong>Nombre del Catedrático: </strong> ___________________________________________ &nbsp;&nbsp;&nbsp;&nbsp; <strong>Curso: </strong> ___________________________________________ <br><br>";
            echo "<strong>Jornada:</strong> $jornada &nbsp;&nbsp;&nbsp;&nbsp; <strong>Nivel:</strong> $nivel &nbsp;&nbsp;&nbsp;&nbsp; <strong>Grado:</strong> $grado <br><br>";
            echo "<table class='students-table' style='width: 80%; border-collapse: collapse; text-align: center; margin: 0 auto; border: 1px solid black;'>";

            // Encabezados de la tabla
            echo "<tr style='background-color: #f2f2f2;'>
                    <th colspan='3' style='border: 1px solid black; padding: 10px;'>Información de Estudiantes</th>
                    <th colspan='4' style='border: 1px solid black; padding: 10px;'>I BIMESTRE</th>
                    <th colspan='4' style='border: 1px solid black; padding: 10px;'>II BIMESTRE</th>
                    <th colspan='4' style='border: 1px solid black; padding: 10px;'>III BIMESTRE</th>
                    <th colspan='4' style='border: 1px solid black; padding: 10px;'>IV BIMESTRE</th>
                    <th colspan='3' style='border: 1px solid black; padding: 10px;'>Rec</th>
                  </tr>";

            // Subencabezados de bimestres
            echo "<tr>
           <th style='border: 1px solid black; padding: 10px;'>No.</th>
           <th style='border: 1px solid black; padding: 10px;'>No. Carnet</th>
           <th style='border: 1px solid black; padding: 10px;'>Estudiante</th>
           <th style='border: 1px solid black; padding: 10px;'>A</th><th style='border: 1px solid black; padding: 10px;'>P</th><th style='border: 1px solid black; padding: 10px;'>C</th><th style='border: 1px solid black; padding: 10px;'>T</th>
           <th style='border: 1px solid black; padding: 10px;'>A</th><th style='border: 1px solid black; padding: 10px;'>P</th><th style='border: 1px solid black; padding: 10px;'>C</th><th style='border: 1px solid black; padding: 10px;'>T</th>
           <th style='border: 1px solid black; padding: 10px;'>A</th><th style='border: 1px solid black; padding: 10px;'>P</th><th style='border: 1px solid black; padding: 10px;'>C</th><th style='border: 1px solid black; padding: 10px;'>T</th>
           <th style='border: 1px solid black; padding: 10px;'>A</th><th style='border: 1px solid black; padding: 10px;'>P</th><th style='border: 1px solid black; padding: 10px;'>C</th><th style='border: 1px solid black; padding: 10px;'>T</th>
           <th style='border: 1px solid black; padding: 10px;'>P</th>
           <th style='border: 1px solid black; padding: 10px;'>Rem</th>
           <th style='border: 1px solid black; padding: 10px;'>1er.</th>
         </tr>
         <tbody>";
            $contador_estudiantes = 1;
            while ($row = $resultados->fetch_assoc()) {
                echo "<tr>
                        <td style='border: 1px solid black; padding: 8px;'>$contador_estudiantes</td>
                        <td style='border: 1px solid black; padding: 8px;'>{$row['carnet']}</td>
                        <td style='border: 1px solid black; padding: 8px;'>{$row['nombre']} {$row['apellido']}</td>";
                for ($i = 0; $i < 19; $i++) {
                    echo "<td style='border: 1px solid black; padding: 8px;'></td>";
                }
                echo "</tr>";
                $contador_estudiantes++;
            }

            echo "</tbody></table><br><br><br><br></div>";
            $html_content = ob_get_clean();

            echo $html_content;
            echo "<form action='../controlador/imprimir_listado_para_docentes.php' method='post'>";
            echo "<input type='hidden' name='html_table' value='" . htmlspecialchars($html_content) . "'>";
            echo "<button type='submit' style='padding: 15px 20px; width: 50%; font-size: 14px; background-color: #4CAF50; color: white; border: none; border-radius: 4px; cursor: pointer; display: block; margin: 20px auto;'>Imprimir PDF</button>";
            echo "<br><br><br>";
            echo "</form>";
        } else {
            echo "<div style='text-align: center; margin-top: 50px; font-size: 18px; color: white; background-color:rgb(255, 0, 0); padding: 20px; border: 1px solid #ddd; border-radius: 8px; width: 60%; margin-left: auto; margin-right: auto; box-shadow: 0 2px 6px rgba(0,0,0,0.1);'>
        No hay estudiantes inscritos según el filtro seleccionado
      </div>";
        }
    }
    ?>

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
                agregarOpcionNivel("Bachiller en Computación con Orientación Comercial", "diversificado3", nivelSelect);
                agregarOpcionNivel("Perito Contador con Orientación en Computación", "diversificado1", nivelSelect);
                agregarOpcionNivel("Perito en Administración de Empresa", "diversificado2", nivelSelect);
            } else if (jornada === "diario-vespertino") {
                agregarOpcionNivel("- Seleccione una Opcion - ", "noselecciono", nivelSelect);
                agregarOpcionNivel("Pre-Primaria", "preprimaria", nivelSelect);
                agregarOpcionNivel("Primaria", "primaria", nivelSelect);
                agregarOpcionNivel("Básico", "basico", nivelSelect);
                agregarOpcionNivel("Bachiller en Computación con Orientación Comercial", "diversificado3", nivelSelect);
                agregarOpcionNivel("Perito Contador con Orientación en Computación", "diversificado1", nivelSelect);
                agregarOpcionNivel("Perito en Administración de Empresa", "diversificado2", nivelSelect);
            } else if (jornada === "fin-de-semana") {
                agregarOpcionNivel("- Seleccione una Opcion - ", "noselecciono", nivelSelect);
                agregarOpcionNivel("Básico", "basico", nivelSelect);
                agregarOpcionNivel("Básico por Madurez", "basicopormadurez", nivelSelect);
                agregarOpcionNivel("Bachiller en Ciencias y Letras por Madurez", "diversificado4", nivelSelect);
                agregarOpcionNivel("Perito Contador con Orientación en Computación", "diversificado1", nivelSelect);
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
                agregarOpcionGrado("6to Grado", "6grado", gradoSelect);
            } else if (nivel === "diversificado4") {
                agregarOpcionGrado("4to Grado", "4grado", gradoSelect);
            }
        }

        function agregarOpcionGrado(texto, valor, select) {
            var opcion = document.createElement("option");
            opcion.text = texto;
            opcion.value = valor;
            select.add(opcion);
        }
    </script>

</body>

</html>
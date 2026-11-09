<?php
// Incluir la conexión a la base de datos una sola vez al principio si es posible.
// Si header.php ya incluye la conexión, este include podría no ser necesario aquí.
include_once "../controlador/conexion_bd.php";
include_once "./header.php";


// --- Inicialización de variables ---
$jornada_sel = '';
$nivel_sel = '';
$grado_sel = '';
$ciclo_sel = '';
$resultados = null;
$html_resultados = ''; // Variable para almacenar el HTML de la tabla
$error_msg = '';       // Variable para mensajes de error o informativos

// --- Mapeos (se pueden definir aquí o incluir desde otro archivo) ---
$mapa_jornada = [
    "diario-matutino" => "Plan Diario - Matutino",
    "diario-vespertino" => "Plan Diario - Vespertino",
    "fin-de-semana" => "Plan Fin de Semana - Sabatino"
];
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
$mapa_grado = [
    "1grado" => "1er Grado",
    "2grado" => "2do Grado",
    "3grado" => "3er Grado",
    "4grado" => "4to Grado",
    "5grado" => "5to Grado",
    "6grado" => "6to Grado"
];

// --- Procesamiento del formulario ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["buscar"])) {
    // Guardar selecciones originales
    $jornada_sel = $_POST["jornada"];
    $nivel_sel = $_POST["nivel"];
    $grado_sel = $_POST["grado"];
    $ciclo_sel = $_POST["ciclo_escolar"];

    // Consulta SQL (usando las selecciones originales)
    // Se seleccionan las columnas necesarias explícitamente
    $consulta = "SELECT id, carnet, nombre, apellido, jornada, nivel, grado, ciclo_escolar
                 FROM registro_estudiantes
                 WHERE jornada = ? AND nivel = ? AND grado = ? AND ciclo_escolar = ?
                 ORDER BY apellido, nombre ASC"; // Ordenar por apellido y nombre

    $sentencia = $conn->prepare($consulta);

    if ($sentencia) {
        $sentencia->bind_param("ssss", $jornada_sel, $nivel_sel, $grado_sel, $ciclo_sel);
        if ($sentencia->execute()) {
            $resultados = $sentencia->get_result();
        } else {
            $error_msg = "Error al ejecutar la consulta: " . htmlspecialchars($sentencia->error);
            // Considerar registrar el error detallado en un log del servidor
        }
        $sentencia->close();
    } else {
        $error_msg = "Error en la preparación de la consulta: " . htmlspecialchars($conn->error);
        // Considerar registrar el error detallado en un log del servidor
    }

    // --- Generación del HTML de resultados (si los hay) ---
    if ($resultados && $resultados->num_rows > 0) {
        // Obtener descripciones para mostrar
        $jornada_desc = $mapa_jornada[$jornada_sel] ?? $jornada_sel;
        $nivel_desc = $mapa_nivel[$nivel_sel] ?? $nivel_sel;
        $grado_desc = $mapa_grado[$grado_sel] ?? $grado_sel;

        // Comienza el almacenamiento en búfer de salida para la tabla
        ob_start();
?>
        <div style='width: 100%; text-align: center;'>
            <h2 class='students-title'>Información de Estudiantes</h2>
            <p>
                <strong>Jornada:</strong> <?= htmlspecialchars($jornada_desc) ?> &nbsp;&nbsp;
                <strong>Nivel:</strong> <?= htmlspecialchars($nivel_desc) ?> &nbsp;&nbsp;
                <strong>Grado:</strong> <?= htmlspecialchars($grado_desc) ?> &nbsp;&nbsp;
                <strong>Ciclo Escolar:</strong> <?= htmlspecialchars($ciclo_sel) ?>
            </p>

            <form action="../controlador/imprimir_todas_las_boletas_de_calificacion.php" method="post" target="_blank" style="display: inline-block; margin-bottom: 15px;">
                <input type="hidden" name="jornada" value="<?= htmlspecialchars($jornada_sel) ?>">
                <input type="hidden" name="nivel" value="<?= htmlspecialchars($nivel_sel) ?>">
                <input type="hidden" name="grado" value="<?= htmlspecialchars($grado_sel) ?>">
                <input type="hidden" name="ciclo_escolar" value="<?= htmlspecialchars($ciclo_sel) ?>">
                <button type="submit" class="print-all-button" style="padding: 10px 20px; background-color: #007bff; color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 14px;">
                    Imprimir Todas las Boletas
                </button>
            </form>


            <table class='students-table' style='width: 80%; border-collapse: collapse; text-align: center; margin: 0 auto; border: 1px solid black;'>
                <thead>
                    <tr style='background-color: #f2f2f2;'>
                        <th colspan='3' style='border: 1px solid black; padding: 5px;'>Información de Estudiantes</th>
                        <th colspan='2' style='border: 1px solid black; padding: 5px;'>Acciones</th>
                    </tr>
                    <tr>
                        <th style='border: 1px solid black; padding: 5px; width: 30px;'>No.</th>
                        <th style='border: 1px solid black; padding: 5px; width: 80px;'>No. Carnet</th>
                        <th style='border: 1px solid black; padding: 5px; width: 180px;'>Estudiante</th>
                        <th style='border: 1px solid black; padding: 5px; width: 120px;'>Ver Calificaciones</th>
                        <th style='border: 1px solid black; padding: 5px; width: 120px;'>Asignar Calificaciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $contador_estudiantes = 1;
                    while ($row = $resultados->fetch_assoc()) {
                        // Construir parámetros para los enlaces (usar id si es PK fiable para calificaciones)
                        $params_ver = [
                            'id_estudiante' => $row['id'], // Preferible usar ID si existe y es la clave en calificaciones
                            'nombre' => $row['nombre'],
                            'apellido' => $row['apellido'],
                            'carnet' => $row['carnet'],
                            'grado' => $row['grado'], // Pasar todos los datos necesarios para la boleta
                            'nivel' => $row['nivel'],
                            'jornada' => $row['jornada'],
                            'ciclo_escolar' => $row['ciclo_escolar']
                        ];
                        $params_asignar = [
                            'carnet' => $row['carnet'], // Asignar podría necesitar carnet y ciclo
                            'ciclo_escolar' => $row['ciclo_escolar']
                        ];
                    ?>
                        <tr>
                            <td style='border: 1px solid black; padding: 5px; text-align: center;'><?= $contador_estudiantes++ ?></td>
                            <td style='border: 1px solid black; padding: 5px;'><?= htmlspecialchars($row['carnet']) ?></td>
                            <td style='border: 1px solid black; padding: 5px; text-align: left;'><?= htmlspecialchars($row['apellido'] . ', ' . $row['nombre']) ?></td>
                            <td style='border: 1px solid black; padding: 5px; text-align: center;'>
                                <a href='impresion_de_calificaciones_por_estudiante.php?<?= http_build_query($params_ver) ?>' target="_blank"
                                    style='background-color: #3498db; color: white; padding: 10px 20px; width: 150px; display: inline-block; text-align: center; border-radius: 4px; text-decoration: none; font-size: 14px;'>
                                    Ver Notas
                                </a>
                            </td>
                            <td style='border: 1px solid black; padding: 5px; text-align: center;'>
                                <a href='asignacion_de_calificaciones_a_estudiante.php?<?= http_build_query($params_asignar) ?>' class='asignarNotaBtn'
                                    style='background-color: #2ecc71; color: white; padding: 10px 20px; width: 150px; display: inline-block; text-align: center; border-radius: 4px; text-decoration: none; font-size: 14px;'>
                                    Asignar Nota
                                </a>
                            </td>
                        </tr>
                    <?php } // Fin del while 
                    ?>
                </tbody>
            </table>
            <br><br>
        </div>
<?php
        $html_resultados = ob_get_clean(); // Captura el HTML de la tabla generado
    } elseif (empty($error_msg)) {
        // Si no hubo resultados Y no hubo error de consulta, mostrar mensaje "No hay estudiantes"
        $error_msg = "<div style='text-align: center; margin-top: 50px; font-size: 18px; color: white; background-color:rgb(255, 0, 0); padding: 20px; border: 1px solid #ddd; border-radius: 8px; width: 60%; margin-left: auto; margin-right: auto; box-shadow: 0 2px 6px rgba(0,0,0,0.1);'>
            No hay estudiantes inscritos según el filtro seleccionado.
        </div>";
    }
    // Si hubo un error de consulta, $error_msg ya tendrá el mensaje correspondiente.

    // Cerrar conexión después de usarla
    if ($conn) {
        $conn->close();
    }
} // Fin del if ($_SERVER["REQUEST_METHOD"] == "POST")
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Listado de Estudiantes</title>
    <link rel="stylesheet" href="../css/inscripcionstyle.css" />
    <link rel="stylesheet" href="../css/historialstyle.css" />
    <link rel="stylesheet" href="../css/navstyle.css" />
    <link rel="stylesheet" href="../css/reportesstyle.css" />
    <style>
        .search-form {
            max-width: 400px;
            margin: 20px auto;
            padding: 15px;
            border: 1px solid #ccc;
            border-radius: 8px;
            background-color: #f9f9f9;
            font-size: 14px;
        }

        .search-form label,
        .search-form select,
        .search-form input,
        .search-form button {
            display: block;
            width: 100%;
            margin-bottom: 10px;
            box-sizing: border-box;
            /* Añadido para mejor control del ancho */
        }

        .search-form select,
        .search-form input {
            padding: 8px;
            /* Añadido padding para mejor apariencia */
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        .search-form button {
            padding: 10px;
            /* Ligeramente más padding */
            background-color: #4CAF50;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            /* Asegurar tamaño de fuente */
        }

        .search-form button:hover {
            background-color: #45a049;
        }

        /* Estilos para el mensaje de error/no resultados */
        .message-box {
            text-align: center;
            margin-top: 20px;
            /* Reducido margen superior */
            font-size: 16px;
            /* Ligeramente reducido */
            color: #333;
            /* Color más estándar */
            background-color: #f8d7da;
            /* Fondo rojo claro para errores */
            border: 1px solid #f5c6cb;
            color: #721c24;
            padding: 15px;
            border-radius: 8px;
            width: 70%;
            /* Ancho ajustado */
            margin-left: auto;
            margin-right: auto;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .message-box.info {
            /* Estilo alternativo para "no resultados" */
            background-color: #fff3cd;
            border-color: #ffeeba;
            color: #856404;
        }
    </style>
  <link rel="stylesheet" href="../css/ui-standard.css">
</head>

<body>
    <?php include './header.php'; // Incluir el encabezado común 
    ?>

    <br><br><br><br><br><br>
    <h2 class="page-title">Listado de Estudiantes</h2>

    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" class="search-form">
        <div class="form-group">
            <label for="jornada">Jornada</label>
            <select id="jornada" name="jornada" required onchange="actualizarNivel()">
                <option value="diario-matutino" <?= ($jornada_sel === 'diario-matutino') ? 'selected' : '' ?>>Plan Diario - Matutino</option>
                <option value="diario-vespertino" <?= ($jornada_sel === 'diario-vespertino') ? 'selected' : '' ?>>Plan Diario - Vespertino</option>
                <option value="fin-de-semana" <?= ($jornada_sel === 'fin-de-semana') ? 'selected' : '' ?>>Plan Fin de Semana - Sabatino</option>
            </select>
        </div>

        <div class="form-group">
            <label for="nivel">Nivel Asignado</label>
            <select id="nivel" name="nivel" required>
            </select>
        </div>

        <div class="form-group">
            <label for="grado">Grado Asignado</label>
            <select id="grado" name="grado" required>
            </select>
        </div>

        <div class="form-group">
            <label for="ciclo_escolar">Ciclo Escolar:</label>
            <input type="text" id="ciclo_escolar" name="ciclo_escolar" required value="<?= htmlspecialchars($ciclo_sel) ?>">
        </div>

        <button type="submit" name="buscar" class="search-button">Buscar</button>
    </form>

    <?php
    // Mostrar mensaje de error o la tabla de resultados
    if (!empty($error_msg)) {
        // Usar clase CSS para estilizar el mensaje
        echo "<div class='message-box'>" . $error_msg . "</div>";
    } elseif (!empty($html_resultados)) {
        echo $html_resultados; // Imprime la tabla capturada previamente
    }
    ?>

    <script>
        // Pasar valores seleccionados desde PHP a JavaScript
        const selectedJornada = '<?= htmlspecialchars($jornada_sel) ?>'; // Aunque el select de jornada ya lo maneja PHP, puede ser útil
        const selectedNivel = '<?= htmlspecialchars($nivel_sel) ?>';
        const selectedGrado = '<?= htmlspecialchars($grado_sel) ?>';

        document.addEventListener("DOMContentLoaded", function() {
            actualizarNivel(); // Llenar Nivel al cargar, basándose en la jornada seleccionada (por PHP o default)

            // Asegúrate de actualizar el grado cuando el nivel cambie dinámicamente
            document.getElementById("nivel").addEventListener("change", actualizarGrado);
        });

        function actualizarNivel() {
            var jornada = document.getElementById("jornada").value;
            var nivelSelect = document.getElementById("nivel");
            nivelSelect.innerHTML = ""; // Limpiar opciones anteriores

            // Añadir opciones según la jornada seleccionada (lógica sin cambios)
            if (jornada === "diario-matutino") {
                agregarOpcionNivel("- Seleccione Nivel -", "", nivelSelect);
                agregarOpcionNivel("Pre-Primaria", "preprimaria", nivelSelect);
                agregarOpcionNivel("Primaria", "primaria", nivelSelect);
                agregarOpcionNivel("Básico", "basico", nivelSelect);
                agregarOpcionNivel("Bachiller en Computación con Orientación Comercial", "diversificado3", nivelSelect);
                agregarOpcionNivel("Perito Contador con Orientación en Computación", "diversificado1", nivelSelect);
                agregarOpcionNivel("Perito en Administración de Empresa", "diversificado2", nivelSelect);
            } else if (jornada === "diario-vespertino") {
                agregarOpcionNivel("- Seleccione Nivel -", "", nivelSelect);
                agregarOpcionNivel("Pre-Primaria", "preprimaria", nivelSelect);
                agregarOpcionNivel("Primaria", "primaria", nivelSelect);
                agregarOpcionNivel("Básico", "basico", nivelSelect);
                agregarOpcionNivel("Bachiller en Computación con Orientación Comercial", "diversificado3", nivelSelect);
                agregarOpcionNivel("Perito Contador con Orientación en Computación", "diversificado1", nivelSelect);
                agregarOpcionNivel("Perito en Administración de Empresa", "diversificado2", nivelSelect);
            } else if (jornada === "fin-de-semana") {
                agregarOpcionNivel("- Seleccione Nivel -", "", nivelSelect);
                agregarOpcionNivel("Básico", "basico", nivelSelect);
                agregarOpcionNivel("Básico por Madurez", "basicopormadurez", nivelSelect);
                agregarOpcionNivel("Bachiller en Ciencias y Letras por Madurez", "diversificado4", nivelSelect);
                agregarOpcionNivel("Perito Contador con Orientación en Computación", "diversificado1", nivelSelect);
            }

            // Intentar restaurar la selección de Nivel si existe
            if (selectedNivel) {
                nivelSelect.value = selectedNivel;
            }

            // Llamar a actualizarGrado para llenar las opciones y restaurar selección si es necesario
            actualizarGrado();
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
            gradoSelect.innerHTML = ""; // Limpiar opciones anteriores

            // Añadir opción por defecto
            agregarOpcionGrado("- Seleccione Grado -", "", gradoSelect);

            // Ajuste de niveles por grado (lógica sin cambios)
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
            } else if (nivel === "diversificado1" || nivel === "diversificado2" || nivel === "diversificado3") {
                agregarOpcionGrado("4to Grado", "4grado", gradoSelect);
                agregarOpcionGrado("5to Grado", "5grado", gradoSelect);
                agregarOpcionGrado("6to Grado", "6grado", gradoSelect);
            } else if (nivel === "diversificado4") {
                agregarOpcionGrado("4to Grado", "4grado", gradoSelect);
            }

            // Intentar restaurar la selección de Grado si existe
            if (selectedGrado) {
                gradoSelect.value = selectedGrado;
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
<?php
// Incluir la conexión a la base de datos y el header.
include_once "../controlador/conexion_bd.php";
include_once "./header.php"; // Assuming header.php is in the same directory as this script

// --- Inicialización de variables ---
$jornada_sel = '';
$nivel_sel = '';
$grado_sel = '';
$ciclo_sel = '';
$resultados = null;
$html_resultados = ''; // Variable para almacenar el HTML de la tabla
$error_msg = '';       // Variable para mensajes de error o informativos

// --- Mapeos (from asignacion_de_calificaciones_a_estudiantes_por_grupos.php) ---
$mapa_jornada = [
    "diario-matutino" => "Plan Diario - Matutino",
    "diario-vespertino" => "Plan Diario - Vespertino",
    "fin-de-semana" => "Plan Fin de Semana - Sabatino"
]; //
$mapa_nivel = [
    "preprimaria" => "Pre-Primaria",
    "primaria" => "Primaria",
    "basico" => "Básico",
    "basicopormadurez" => "Básico por Madurez",
    "diversificado1" => "Perito Contador con Orientación en Computación",
    "diversificado2" => "Perito en Administración de Empresas",
    "diversificado3" => "Bachiller en Computación con Orientación Comercial",
    "diversificado4" => "Bachiller en Ciencias y Letras por Madurez"
]; //
$mapa_grado = [
    "1grado" => "1er Grado",
    "2grado" => "2do Grado",
    "3grado" => "3er Grado",
    "4grado" => "4to Grado",
    "5grado" => "5to Grado",
    "6grado" => "6to Grado"
]; //

// --- Procesamiento del formulario ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["buscar"])) {
    // Guardar selecciones originales
    $jornada_sel = $_POST["jornada"]; //
    $nivel_sel = $_POST["nivel"]; //
    $grado_sel = $_POST["grado"]; //
    $ciclo_sel = $_POST["ciclo_escolar"]; //

    // Consulta SQL: Modificada para incluir la existencia del carnet
    // Se seleccionan las columnas necesarias explícitamente
    $consulta = "SELECT re.id, re.carnet, re.nombre, re.apellido, re.jornada, re.nivel, re.grado, re.ciclo_escolar,
                        (SELECT COUNT(*) FROM carnet_estudiante ce WHERE ce.carnet = re.carnet) > 0 AS carnet_existe
                 FROM registro_estudiantes re
                 WHERE re.jornada = ? AND re.nivel = ? AND re.grado = ? AND re.ciclo_escolar = ?
                 ORDER BY re.apellido, re.nombre ASC"; //

    $sentencia = $conn->prepare($consulta);

    if ($sentencia) {
        $sentencia->bind_param("ssss", $jornada_sel, $nivel_sel, $grado_sel, $ciclo_sel); //
        if ($sentencia->execute()) {
            $resultados = $sentencia->get_result(); //
        } else {
            $error_msg = "Error al ejecutar la consulta: " . htmlspecialchars($sentencia->error); //
        }
        $sentencia->close(); //
    } else {
        $error_msg = "Error en la preparación de la consulta: " . htmlspecialchars($conn->error); //
    }

    // --- Generación del HTML de resultados (si los hay) ---
    if ($resultados && $resultados->num_rows > 0) {
        // Obtener descripciones para mostrar
        $jornada_desc = $mapa_jornada[$jornada_sel] ?? $jornada_sel; //
        $nivel_desc = $mapa_nivel[$nivel_sel] ?? $nivel_sel; //
        $grado_desc = $mapa_grado[$grado_sel] ?? $grado_sel; //

        // Comienza el almacenamiento en búfer de salida para la tabla
        ob_start();
?>
        <div style='width: 100%; text-align: center;'>
            <h2 class='students-title'>Estudiantes Encontrados</h2>
            <p>
                <strong>Jornada:</strong> <?= htmlspecialchars($jornada_desc) ?> &nbsp;&nbsp;
                <strong>Nivel:</strong> <?= htmlspecialchars($nivel_desc) ?> &nbsp;&nbsp;
                <strong>Grado:</strong> <?= htmlspecialchars($grado_desc) ?> &nbsp;&nbsp;
                <strong>Ciclo Escolar:</strong> <?= htmlspecialchars($ciclo_sel) ?>
            </p>

            <table class='students-table' style='width: 90%; border-collapse: collapse; text-align: center; margin: 20px auto; border: 1px solid black;'>
                <thead>
                    <tr style='background-color: #f2f2f2;'>
                        <th style='border: 1px solid black; padding: 8px; width: 30px;'>No.</th>
                        <th style='border: 1px solid black; padding: 8px; width: 100px;'>No. Carnet</th>
                        <th style='border: 1px solid black; padding: 8px;'>Estudiante</th>
                        <th colspan='2' style='border: 1px solid black; padding: 8px; width: 280px;'>Acciones del Carnet</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $contador_estudiantes = 1; //
                    while ($row = $resultados->fetch_assoc()) { //
                        $url_carnet_estudiante = urlencode($row['carnet']);
                        $url_ciclo_escolar_sel = urlencode($ciclo_sel); // Usar el ciclo escolar de la búsqueda

                        $link_crear_actualizar = "crear_carnet_estudiante.php?carnet={$url_carnet_estudiante}&ciclo_escolar={$url_ciclo_escolar_sel}&buscar=true";
                        $link_imprimir = "buscar_carnet_estudiante.php?carnet={$url_carnet_estudiante}&ciclo_escolar={$url_ciclo_escolar_sel}&accion=mostrar";
                        
                        $texto_accion_principal = $row['carnet_existe'] ? "Actualizar Carnet" : "Crear Carnet";
                    ?>
                        <tr>
                            <td style='border: 1px solid black; padding: 8px; text-align: center;'><?= $contador_estudiantes++ ?></td>
                            <td style='border: 1px solid black; padding: 8px;'><?= htmlspecialchars($row['carnet']) ?></td>
                            <td style='border: 1px solid black; padding: 8px; text-align: left;'><?= htmlspecialchars($row['apellido'] . ', ' . $row['nombre']) ?></td>
                            <td style='border: 1px solid black; padding: 8px; text-align: center;'>
                                <a href='<?= $link_crear_actualizar ?>'
                                    style='background-color: #5bc0de; color: white; padding: 8px 15px; display: inline-block; text-align: center; border-radius: 4px; text-decoration: none; font-size: 14px;'>
                                    <i class="fas fa-id-card"></i> <?= $texto_accion_principal ?>
                                </a>
                            </td>
                            <td style='border: 1px solid black; padding: 8px; text-align: center;'>
                                <?php if ($row['carnet_existe']): ?>
                                <a href='<?= $link_imprimir ?>' target="_blank"
                                    style='background-color: #f0ad4e; color: white; padding: 8px 15px; display: inline-block; text-align: center; border-radius: 4px; text-decoration: none; font-size: 14px;'>
                                    <i class="fas fa-print"></i> Imprimir Carnet
                                </a>
                                <?php else: echo "-"; endif; ?>
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
        $error_msg = "<div style='text-align: center; margin-top: 50px; font-size: 18px; color: white; background-color:rgb(255, 0, 0); padding: 20px; border: 1px solid #ddd; border-radius: 8px; width: 60%; margin-left: auto; margin-right: auto; box-shadow: 0 2px 6px rgba(0,0,0,0.1);'>
            No hay estudiantes inscritos según el filtro seleccionado.
        </div>"; //
    }

    if ($conn) {
        $conn->close(); //
    }
} // Fin del if ($_SERVER["REQUEST_METHOD"] == "POST")
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Búsqueda de Estudiantes para Gestión de Carnets</title>
    <link rel="stylesheet" href="../css/inscripcionstyle.css" /> <link rel="stylesheet" href="../css/navstyle.css" /> <link rel="stylesheet" href="../css/reportesstyle.css" /> <style>
        /* Estilos del search-form y message-box mantenidos del script base */
        .search-form {
            max-width: 450px; /* Un poco más de ancho */
            margin: 20px auto;
            padding: 20px; /* Más padding */
            border: 1px solid #ccc;
            border-radius: 8px;
            background-color: #f9f9f9;
            font-size: 14px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .search-form label {
             font-weight: bold; /* Etiquetas en negrita */
             margin-top: 10px; /* Espacio arriba de la etiqueta */
        }

        .search-form label,
        .search-form select,
        .search-form input,
        .search-form button {
            display: block;
            width: 100%;
            margin-bottom: 12px; /* Espacio entre elementos */
            box-sizing: border-box;
        }

        .search-form select,
        .search-form input[type="text"] { /* Especificar input text */
            padding: 10px; /* Padding consistente */
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        .search-form button {
            padding: 12px; /* Padding para el botón */
            background-color: #007bff; /* Azul primario de Bootstrap */
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px; /* Fuente más grande para el botón */
            transition: background-color 0.2s ease-in-out;
        }

        .search-form button:hover {
            background-color: #0056b3; /* Azul más oscuro al pasar el mouse */
        }

        .message-box { /* Estilo de mensaje de error/info */
            text-align: center;
            margin-top: 20px;
            font-size: 16px;
            color: #333;
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
            padding: 15px;
            border-radius: 8px;
            width: 70%;
            margin-left: auto;
            margin-right: auto;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .students-title {
            margin-bottom: 10px;
        }
        /* Iconos de Font Awesome (si se usan) */
        @import url("https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css");
    </style>
</head>

<body>
    <?php // include './header.php'; // El header ya está incluido al inicio del script 
    ?>

    <br><br><br><br><br><br>
    <h2 class="page-title" style="text-align:center;">Gestión de Carnets Estudiantiles por Grupo</h2>

    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" class="search-form">
        <div class="form-group">
            <label for="jornada">Jornada</label>
            <select id="jornada" name="jornada" required onchange="actualizarNivel()">
                <option value="">-- Seleccione Jornada --</option>
                <option value="diario-matutino" <?= ($jornada_sel === 'diario-matutino') ? 'selected' : '' ?>>Plan Diario - Matutino</option>
                <option value="diario-vespertino" <?= ($jornada_sel === 'diario-vespertino') ? 'selected' : '' ?>>Plan Diario - Vespertino</option>
                <option value="fin-de-semana" <?= ($jornada_sel === 'fin-de-semana') ? 'selected' : '' ?>>Plan Fin de Semana - Sabatino</option>
            </select> </div>

        <div class="form-group">
            <label for="nivel">Nivel Asignado</label>
            <select id="nivel" name="nivel" required>
                 <option value="">-- Seleccione Nivel --</option>
            </select> </div>

        <div class="form-group">
            <label for="grado">Grado Asignado</label>
            <select id="grado" name="grado" required>
                 <option value="">-- Seleccione Grado --</option>
            </select> </div>

        <div class="form-group">
            <label for="ciclo_escolar">Ciclo Escolar:</label>
            <input type="text" id="ciclo_escolar" name="ciclo_escolar" required value="<?= htmlspecialchars($ciclo_sel) ?>" placeholder="Ej: 2024"> </div>

        <button type="submit" name="buscar" class="search-button">Buscar Estudiantes</button>
    </form>

    <?php
    // Mostrar mensaje de error o la tabla de resultados
    if (!empty($error_msg) && empty($html_resultados)) { // Solo mostrar error_msg si no hay resultados que mostrar
        echo "<div class='message-box'>" . $error_msg . "</div>"; //
    } elseif (!empty($html_resultados)) {
        echo $html_resultados; // Imprime la tabla capturada previamente
    }
    ?>

    <script>
        // Pasar valores seleccionados desde PHP a JavaScript
        const selectedJornada = '<?= htmlspecialchars($jornada_sel) ?>';
        const selectedNivel = '<?= htmlspecialchars($nivel_sel) ?>'; //
        const selectedGrado = '<?= htmlspecialchars($grado_sel) ?>'; //

        document.addEventListener("DOMContentLoaded", function() {
            actualizarNivel(); // Llenar Nivel al cargar
            document.getElementById("nivel").addEventListener("change", actualizarGrado); //
        });

        function actualizarNivel() {
            var jornada = document.getElementById("jornada").value; //
            var nivelSelect = document.getElementById("nivel"); //
            nivelSelect.innerHTML = ""; // Limpiar opciones anteriores
            agregarOpcionNivel("-- Seleccione Nivel --", "", nivelSelect); // Opción por defecto

            if (jornada === "diario-matutino" || jornada === "diario-vespertino") { //
                agregarOpcionNivel("Pre-Primaria", "preprimaria", nivelSelect); //
                agregarOpcionNivel("Primaria", "primaria", nivelSelect); //
                agregarOpcionNivel("Básico", "basico", nivelSelect); //
                agregarOpcionNivel("Bachiller en Computación con Orientación Comercial", "diversificado3", nivelSelect); //
                agregarOpcionNivel("Perito Contador con Orientación en Computación", "diversificado1", nivelSelect); //
                agregarOpcionNivel("Perito en Administración de Empresa", "diversificado2", nivelSelect); //
            } else if (jornada === "fin-de-semana") { //
                agregarOpcionNivel("Básico", "basico", nivelSelect); //
                agregarOpcionNivel("Básico por Madurez", "basicopormadurez", nivelSelect); //
                agregarOpcionNivel("Bachiller en Ciencias y Letras por Madurez", "diversificado4", nivelSelect); //
                agregarOpcionNivel("Perito Contador con Orientación en Computación", "diversificado1", nivelSelect); //
            }

            if (selectedNivel) {
                nivelSelect.value = selectedNivel; //
            }
            actualizarGrado(); //
        }

        function agregarOpcionNivel(texto, valor, select) {
            var opcion = document.createElement("option"); //
            opcion.text = texto; //
            opcion.value = valor; //
            select.add(opcion); //
        }

        function actualizarGrado() {
            var nivel = document.getElementById("nivel").value; //
            var gradoSelect = document.getElementById("grado"); //
            gradoSelect.innerHTML = ""; // Limpiar opciones anteriores
            agregarOpcionGrado("-- Seleccione Grado --", "", gradoSelect); //

            if (nivel === "preprimaria") { //
                agregarOpcionGrado("1er Grado", "1grado", gradoSelect); //
                agregarOpcionGrado("2do Grado", "2grado", gradoSelect); //
                agregarOpcionGrado("3er Grado", "3grado", gradoSelect); //
            } else if (nivel === "primaria") { //
                agregarOpcionGrado("1er Grado", "1grado", gradoSelect); //
                agregarOpcionGrado("2do Grado", "2grado", gradoSelect); //
                agregarOpcionGrado("3er Grado", "3grado", gradoSelect); //
                agregarOpcionGrado("4to Grado", "4grado", gradoSelect); //
                agregarOpcionGrado("5to Grado", "5grado", gradoSelect); //
                agregarOpcionGrado("6to Grado", "6grado", gradoSelect); //
            } else if (nivel === "basico") { //
                agregarOpcionGrado("1er Grado", "1grado", gradoSelect); //
                agregarOpcionGrado("2do Grado", "2grado", gradoSelect); //
                agregarOpcionGrado("3er Grado", "3grado", gradoSelect); //
            } else if (nivel === "basicopormadurez") { //
                agregarOpcionGrado("1er Grado", "1grado", gradoSelect); //
                agregarOpcionGrado("2do Grado", "2grado", gradoSelect); //
            } else if (nivel === "diversificado1" || nivel === "diversificado2" || nivel === "diversificado3") { //
                agregarOpcionGrado("4to Grado", "4grado", gradoSelect); //
                agregarOpcionGrado("5to Grado", "5grado", gradoSelect); //
                agregarOpcionGrado("6to Grado", "6grado", gradoSelect); //
            } else if (nivel === "diversificado4") { //
                agregarOpcionGrado("4to Grado", "4grado", gradoSelect); //
            }

            if (selectedGrado) {
                gradoSelect.value = selectedGrado; //
            }
        }

        function agregarOpcionGrado(texto, valor, select) {
            var opcion = document.createElement("option"); //
            opcion.text = texto; //
            opcion.value = valor; //
            select.add(opcion); //
        }
    </script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/js/all.min.js"></script>
</body>
</html>
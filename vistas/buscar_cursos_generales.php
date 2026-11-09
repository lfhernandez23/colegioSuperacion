<?php
// Capturar la URL actual con sus parámetros GET para mantener el estado al buscar
$parametrosGet = $_SERVER['QUERY_STRING']; // Ej: jornada=mañana&nivel=primaria&grado=1grado
$urlConGet = basename($_SERVER['PHP_SELF']) . ($parametrosGet ? '?' . $parametrosGet : '');

// --- MANEJO DE PETICIONES AJAX (Eliminar, Editar y Crear) ---

// Acción Crear (AJAX) - Debe ir ANTES de la lógica de búsqueda normal
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["accion"]) && $_POST["accion"] == "crear") {
    header("Content-Type: application/json");
    $response = ["success" => false, "message" => "Error desconocido al crear"];

    // Recuperar y validar datos del formulario de creación
    $jornada = $_POST["jornada_crear"] ?? null;
    $nivel = $_POST["nivel_crear"] ?? null;
    $grado = $_POST["grado_crear"] ?? null;
    $nombreNuevoCurso = trim($_POST["nombre_curso_nuevo"] ?? '');

    // Validaciones básicas
    if (empty($jornada) || $jornada == 'noselecciono') {
        $response["message"] = "La jornada no es válida.";
        echo json_encode($response);
        exit();
    }
    if (empty($nivel) || $nivel == 'noselecciono') {
        $response["message"] = "El nivel no es válido.";
        echo json_encode($response);
        exit();
    }
    if (empty($grado)) { // Grado puede no tener 'noselecciono', verificar solo si está vacío
        $response["message"] = "El grado no es válido.";
        echo json_encode($response);
        exit();
    }
    if (empty($nombreNuevoCurso)) {
        $response["message"] = "El nombre del nuevo curso no puede estar vacío.";
        echo json_encode($response);
        exit();
    }

    include_once "../controlador/conexion_bd.php"; // Asegúrate que la ruta es correcta

    if ($conn->connect_error) {
        $response["message"] = "Error de conexión a la base de datos.";
        echo json_encode($response);
        exit();
    }

    // Verificar si ya existe un curso con el mismo nombre para la misma combinación (Opcional pero recomendado)
    $verificar = "SELECT id FROM cursos WHERE curso = ? AND jornada = ? AND nivel = ? AND grado = ?";
    $stmtVerif = $conn->prepare($verificar);
    if ($stmtVerif) {
        $stmtVerif->bind_param("ssss", $nombreNuevoCurso, $jornada, $nivel, $grado);
        $stmtVerif->execute();
        $stmtVerif->store_result();
        if ($stmtVerif->num_rows > 0) {
            $response["message"] = "Ya existe un curso con este nombre para la combinación seleccionada.";
            $stmtVerif->close();
            $conn->close();
            echo json_encode($response);
            exit();
        }
        $stmtVerif->close();
    } else {
        $response["message"] = "Error al preparar verificación.";
        $conn->close();
        echo json_encode($response);
        exit();
    }


    // Insertar el nuevo curso
    $insertar = "INSERT INTO cursos (curso, jornada, nivel, grado) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($insertar);

    if ($stmt) {
        $stmt->bind_param("ssss", $nombreNuevoCurso, $jornada, $nivel, $grado);
        if ($stmt->execute()) {
            $nuevoId = $conn->insert_id; // Obtener el ID del curso recién insertado
            if ($nuevoId) {
                $response = [
                    "success" => true,
                    "message" => "Curso creado con éxito.",
                    "nuevo_curso" => [ // Devolver datos del nuevo curso para añadir a la tabla
                        "id" => $nuevoId,
                        "nombre" => $nombreNuevoCurso,
                        "jornada" => $jornada, // Podrían ser útiles si necesitas la info en JS
                        "nivel" => $nivel,
                        "grado" => $grado
                    ]
                ];
            } else {
                $response["message"] = "El curso fue creado pero no se pudo obtener su ID.";
            }
        } else {
            // Considera loggear el error real: $stmt->error
            $response["message"] = "Error al ejecutar la inserción: " . $stmt->error; // Mostrar error específico puede ayudar a depurar
        }
        $stmt->close();
    } else {
        // Considera loggear el error real: $conn->error
        $response["message"] = "Error al preparar la consulta de inserción.";
    }
    $conn->close();
    echo json_encode($response);
    exit(); // Termina la ejecución después de manejar AJAX
}


// Acción Eliminar (AJAX)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["accion"]) && $_POST["accion"] == "eliminar") {
    header("Content-Type: application/json");
    $response = ["success" => false, "message" => "Error desconocido al eliminar"];

    $idCurso = $_POST["id_curso"] ?? null;

    if (!$idCurso || !filter_var($idCurso, FILTER_VALIDATE_INT)) {
        $response["message"] = "ID de curso no válido.";
        echo json_encode($response);
        exit();
    }

    include_once "../controlador/conexion_bd.php"; // Asegúrate que la ruta es correcta

    if ($conn->connect_error) {
        $response["message"] = "Error de conexión a la base de datos.";
        echo json_encode($response);
        exit();
    }

    $eliminar = "DELETE FROM cursos WHERE id = ?";
    $stmt = $conn->prepare($eliminar);

    if ($stmt) {
        $stmt->bind_param("i", $idCurso);
        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                $response = ["success" => true, "message" => "Curso eliminado correctamente."];
            } else {
                $response["message"] = "No se encontró el curso o ya fue eliminado.";
            }
        } else {
            $response["message"] = "Error al ejecutar la eliminación.";
        }
        $stmt->close();
    } else {
        $response["message"] = "Error al preparar la consulta de eliminación.";
    }
    $conn->close();
    echo json_encode($response);
    exit();
}

// Acción Editar (AJAX)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["accion"]) && $_POST["accion"] == "editar") {
    header("Content-Type: application/json");
    $response = ["success" => false, "message" => "Error desconocido al editar"];

    $idCurso = $_POST["id_curso"] ?? null;
    $nuevoNombre = trim($_POST["nombre_curso"] ?? '');

    if (!$idCurso || !filter_var($idCurso, FILTER_VALIDATE_INT)) {
        $response["message"] = "ID de curso no válido.";
        echo json_encode($response);
        exit();
    }
    if (empty($nuevoNombre)) {
        $response["message"] = "El nombre del curso no puede estar vacío.";
        echo json_encode($response);
        exit();
    }

    include_once "../controlador/conexion_bd.php"; // Asegúrate que la ruta es correcta

    if ($conn->connect_error) {
        $response["message"] = "Error de conexión a la base de datos.";
        echo json_encode($response);
        exit();
    }

    $editar = "UPDATE cursos SET curso = ? WHERE id = ?";
    $stmt = $conn->prepare($editar);

    if ($stmt) {
        $stmt->bind_param("si", $nuevoNombre, $idCurso);
        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                $response = [
                    "success" => true,
                    "message" => "Curso actualizado con éxito.",
                    "id_curso" => $idCurso,
                    "nombre" => $nuevoNombre
                ];
            } else {
                $response["message"] = "No se realizaron cambios o el curso no existe.";
            }
        } else {
            $response["message"] = "Error al ejecutar la actualización.";
        }
        $stmt->close();
    } else {
        $response["message"] = "Error al preparar la consulta de actualización.";
    }
    $conn->close();
    echo json_encode($response);
    exit();
}

// --- LÓGICA PARA MOSTRAR LA PÁGINA Y RESULTADOS (SI NO ES AJAX de edición/eliminación/creación) ---

$jornadaSeleccionada = '';
$nivelSeleccionado = '';
$gradoSeleccionado = '';
$resultados = null; // Inicializar resultados
$mostrarResultados = false; // Flag para saber si se realizó una búsqueda

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["buscar"])) {
    $jornadaSeleccionada = $_POST["jornada"];
    $nivelSeleccionado = $_POST["nivel"];
    $gradoSeleccionado = $_POST["grado"];
    $mostrarResultados = true; // Se intentó buscar

    if ($jornadaSeleccionada != "noselecciono" && $nivelSeleccionado != "noselecciono" && !empty($gradoSeleccionado)) {
        include_once "../controlador/conexion_bd.php";
        if ($conn->connect_error) {
            die("Error de conexión: " . $conn->connect_error);
        }
        $consulta = "SELECT id, curso FROM cursos WHERE jornada = ? AND nivel = ? AND grado = ?";
        $sentencia = $conn->prepare($consulta);
        if ($sentencia) {
            $sentencia->bind_param("sss", $jornadaSeleccionada, $nivelSeleccionado, $gradoSeleccionado);
            $sentencia->execute();
            $resultados = $sentencia->get_result();
            $sentencia->close();
        } else {
            die("Error en la preparación de la consulta: " . $conn->error);
        }
        $conn->close();
    } else {
        $mensajeErrorBusqueda = "Por favor, seleccione jornada, nivel y grado válidos para buscar.";
        $resultados = null; // Asegurarse que no hay resultados si la selección no es válida
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/inscripcionstyle.css" />
    <link rel="stylesheet" href="../css/historialstyle.css" />
    <link rel="stylesheet" href="../css/navstyle.css" />
    <link rel="stylesheet" href="../css/reportesstyle.css" />
    <title>Búsqueda y Gestión de Cursos</title>
    <style>
        /* --- Estilos existentes --- */
        /* Estilos para el Modal (simplificados) */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.5);
            padding-top: 60px;
            align-items: center;
            justify-content: center;
        }

        .modal-content {
            background-color: #fefefe;
            margin: auto;
            padding: 25px;
            border: 1px solid #888;
            width: 90%;
            max-width: 450px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            position: relative;
        }

        .cerrar {
            color: #aaa;
            position: absolute;
            top: 10px;
            right: 15px;
            font-size: 28px;
            font-weight: bold;
            line-height: 1;
            cursor: pointer;
        }

        .cerrar:hover,
        .cerrar:focus {
            color: black;
            text-decoration: none;
        }

        .modal h2 {
            margin-top: 0;
            margin-bottom: 15px;
            text-align: center;
        }

        .modal label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }

        .modal input[type="text"],
        .modal input[type="hidden"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }

        .modal button {
            background-color: #007bff;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-right: 10px;
        }

        .modal button[type="button"] {
            background-color: #6c757d;
        }

        .modal button:hover {
            opacity: 0.9;
        }

        /* Estilos generales y tabla (simplificados) */
        .container {
            max-width: 95%;
            margin: 30px auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th,
        td {
            padding: 10px 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }

        tbody tr:hover {
            background-color: #f9f9f9;
        }

        /* Solo para tbody */
        .acciones-btn {
            padding: 6px 10px;
            border: none;
            border-radius: 4px;
            color: white;
            cursor: pointer;
            margin-right: 5px;
            font-size: 0.9em;
        }

        .btn-editar {
            background-color: #007bff;
        }

        .btn-eliminar {
            background-color: #dc3545;
        }

        /* Estilos para el formulario de búsqueda */
        #form-busqueda {
            margin-bottom: 20px;
            padding: 15px;
            background-color: #f9f9f9;
            border: 1px solid #eee;
            border-radius: 5px;
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            align-items: flex-end;
        }

        #form-busqueda div {
            flex: 1 1 200px;
            min-width: 150px;
        }

        #form-busqueda label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }

        #form-busqueda select,
        #form-busqueda button {
            width: 100%;
            padding: 10px;
            border-radius: 4px;
            border: 1px solid #ccc;
            box-sizing: border-box;
        }

        #form-busqueda button {
            background-color: #28a745;
            color: white;
            cursor: pointer;
            flex-basis: 100px;
            flex-grow: 0;
            align-self: flex-end;
        }

        #form-busqueda button:hover {
            background-color: #218838;
        }

        .mensaje-error {
            color: red;
            margin-top: 10px;
            width: 100%;
        }

        .no-cursos {
            margin-top: 20px;
            font-style: italic;
            color: #666;
        }

        /* --- Nuevos Estilos para Crear Curso --- */
        #area-crear-curso {
            margin-top: 30px;
            padding: 20px;
            border: 1px solid #e0e0e0;
            border-radius: 5px;
            background-color: #f5f5f5;
            display: none;
            /* Oculto por defecto */
        }

        #area-crear-curso h3 {
            margin-top: 0;
            margin-bottom: 15px;
            text-align: center;
            color: #333;
        }

        #form-crear-curso label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }

        #form-crear-curso input[type="text"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }

        #form-crear-curso .botones-crear {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-top: 10px;
        }

        #form-crear-curso button {
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
        }

        #form-crear-curso button[type="submit"] {
            background-color: #28a745;
            /* Verde */
            color: white;
        }

        #form-crear-curso button[type="button"] {
            background-color: #6c757d;
            /* Gris */
            color: white;
        }

        #mensaje-crear {
            margin-top: 15px;
            text-align: center;
            font-weight: bold;
        }

        #mensaje-crear.success {
            color: green;
        }

        #mensaje-crear.error {
            color: red;
        }

        #btn-mostrar-crear {
            margin-top: 20px;
            padding: 10px 15px;
            background-color: #17a2b8;
            /* Cian */
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            display: none;
            /* Oculto hasta que se haga una búsqueda válida */
        }

        #btn-mostrar-crear:hover {
            background-color: #138496;
        }

        /* Dentro de tu etiqueta <style> o archivo CSS */

        #resultados-area {
            text-align: center;
            /* Centra los elementos inline o inline-block dentro de este div */
        }

        /* Ajustes opcionales para el botón si es necesario (manteniendo estilos previos) */
        #btn-mostrar-crear {
            margin-top: 20px;
            /* Mantiene el margen superior */
            padding: 10px 15px;
            background-color: #17a2b8;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            display: none;
            /* Mantiene el estado inicial oculto */
            /* Importante: asegúrate que display sea 'inline-block' o 'block' cuando se muestre con JS para que text-align funcione correctamente */
        }

        /* Asegúrate que la tabla (si existe) no herede el text-align si no lo deseas */
        #tabla-cursos {
            text-align: left;
            /* O el alineado que tuviera por defecto, usualmente 'left' */
        }

        .no-cursos {
            text-align: left;
            /* O el alineado deseado para el mensaje */
        }
    </style>
  <link rel="stylesheet" href="../css/ui-standard.css">
</head>

<body>
    <?php
    // Incluir header si existe y la ruta es correcta
    if (file_exists('./header.php')) {
        include './header.php';
    } else if (file_exists('../includes/header.php')) {
        include '../includes/header.php';
    }
    ?>

    <div class="container">
        <br><br><br>
        <h2>Listado y Búsqueda de Cursos</h2>

        <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post" id="form-busqueda">
            <div>
                <label for="jornada">Jornada</label>
                <select id="jornada" name="jornada" required></select>
            </div>
            <div>
                <label for="nivel">Nivel Académico</label>
                <select id="nivel" name="nivel" required></select>
            </div>
            <div>
                <label for="grado">Grado</label>
                <select id="grado" name="grado" required></select>
            </div>
            <button type="submit" name="buscar">Buscar Cursos</button>
            <?php if (isset($mensajeErrorBusqueda)): ?>
                <p class="mensaje-error"><?php echo $mensajeErrorBusqueda; ?></p>
            <?php endif; ?>
        </form>

        <div id="resultados-area">
            <?php if ($mostrarResultados): // Solo mostrar algo si se hizo POST de búsqueda 
            ?>
                <?php if (isset($resultados) && $resultados->num_rows > 0): ?>
                    <table id="tabla-cursos">
                        <thead>
                            <tr>
                                <th>N°</th>
                                <th>Nombre del Curso</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $contador = 1; ?>
                            <?php while ($fila = $resultados->fetch_assoc()): ?>
                                <tr data-id-curso="<?php echo $fila['id']; ?>">
                                    <td><?php echo $contador++; ?></td>
                                    <td class="nombre-curso"><?php echo htmlspecialchars($fila["curso"]); ?></td>
                                    <td>
                                        <button class='acciones-btn btn-editar' data-id='<?php echo $fila['id']; ?>' data-nombre='<?php echo htmlspecialchars($fila['curso'], ENT_QUOTES); ?>'>Editar</button>
                                        <button class='acciones-btn btn-eliminar' data-id='<?php echo $fila['id']; ?>'>Eliminar</button>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php elseif (isset($resultados)): // Se buscó pero no hubo resultados 
                ?>
                    <p class="no-cursos" id="msg-no-cursos">No se encontraron cursos para los criterios seleccionados.</p>
                <?php endif; ?>
                <?php if (isset($resultados)) $resultados->free(); ?>

                <button id="btn-mostrar-crear" type="button">Crear Nuevo Curso para esta Selección</button>

            <?php endif; ?>
        </div>
        <div id="area-crear-curso">
            <h3>Crear Nuevo Curso</h3>
            <form id="form-crear-curso" onsubmit="crearNuevoCurso(event)">
                <input type="hidden" id="jornada_crear" name="jornada_crear" value="<?php echo htmlspecialchars($jornadaSeleccionada); ?>">
                <input type="hidden" id="nivel_crear" name="nivel_crear" value="<?php echo htmlspecialchars($nivelSeleccionado); ?>">
                <input type="hidden" id="grado_crear" name="grado_crear" value="<?php echo htmlspecialchars($gradoSeleccionado); ?>">
                <input type="hidden" name="accion" value="crear">

                <label for="nombre_curso_nuevo">Nombre del Nuevo Curso:</label>
                <input type="text" id="nombre_curso_nuevo" name="nombre_curso_nuevo" required>

                <div id="mensaje-crear"></div>
                <div class="botones-crear">
                    <button type="submit">Guardar Curso</button>
                    <button type="button" onclick="cancelarCreacion()">Cancelar</button>
                </div>
            </form>
        </div>

    </div>
    <div id="modal-editar" class="modal">
        <div class="modal-content">
            <span class="cerrar" onclick="cerrarModal()">&times;</span>
            <h2>Editar Nombre del Curso</h2>
            <form id="form-editar-curso" onsubmit="guardarCambiosCurso(event)">
                <input type="hidden" id="edit-id-curso" name="id_curso">
                <label for="edit-nombre-curso">Nuevo Nombre</label>
                <input type="text" id="edit-nombre-curso" name="nombre_curso" required>
                <input type="hidden" name="accion" value="editar">
                <button type="submit">Guardar Cambios</button>
                <button type="button" onclick="cerrarModal()">Cancelar</button>
            </form>
            <div id="mensaje-modal" style="color: red; margin-top: 10px;"></div>
        </div>
    </div>

    <script>
        // --- Variables globales y Selectores ---
        const jornadaSelect = document.getElementById('jornada');
        const nivelSelect = document.getElementById('nivel');
        const gradoSelect = document.getElementById('grado');
        const modalEditar = document.getElementById('modal-editar');
        const formEditarCurso = document.getElementById('form-editar-curso');
        const inputIdCurso = document.getElementById('edit-id-curso');
        const inputNombreCurso = document.getElementById('edit-nombre-curso');
        const mensajeModal = document.getElementById('mensaje-modal');
        const resultadosArea = document.getElementById('resultados-area');
        const btnMostrarCrear = document.getElementById('btn-mostrar-crear');
        const areaCrearCurso = document.getElementById('area-crear-curso');
        const formCrearCurso = document.getElementById('form-crear-curso');
        const inputNombreNuevo = document.getElementById('nombre_curso_nuevo');
        const mensajeCrear = document.getElementById('mensaje-crear');
        const inputJornadaCrear = document.getElementById('jornada_crear');
        const inputNivelCrear = document.getElementById('nivel_crear');
        const inputGradoCrear = document.getElementById('grado_crear');


        // --- Opciones para Selects (igual que antes) ---
        const opciones = {
            jornadas: [{
                value: 'noselecciono',
                text: '- Seleccione Jornada -'
            }, {
                value: 'diario-matutino',
                text: 'Plan Diario - Matutino'
            }, {
                value: 'diario-vespertino',
                text: 'Plan Diario - Vespertino'
            }, {
                value: 'fin-de-semana',
                text: 'Plan Fin de Semana - Sabatino'
            }],
            niveles: {
                'noselecciono': [{
                    value: 'noselecciono',
                    text: '- Seleccione Nivel -'
                }],
                'diario-matutino': [{
                    value: 'noselecciono',
                    text: '- Seleccione Nivel -'
                }, {
                    value: 'preprimaria',
                    text: 'Pre-Primaria'
                }, {
                    value: 'primaria',
                    text: 'Primaria'
                }, {
                    value: 'basico',
                    text: 'Básico'
                }, {
                    value: 'diversificado3',
                    text: 'Bachiller en Computación'
                }, {
                    value: 'diversificado1',
                    text: 'Perito Contador'
                }, {
                    value: 'diversificado2',
                    text: 'Perito en Administración'
                }],
                'diario-vespertino': [{
                    value: 'noselecciono',
                    text: '- Seleccione Nivel -'
                }, {
                    value: 'preprimaria',
                    text: 'Pre-Primaria'
                }, {
                    value: 'primaria',
                    text: 'Primaria'
                }, {
                    value: 'basico',
                    text: 'Básico'
                }, {
                    value: 'diversificado3',
                    text: 'Bachiller en Computación'
                }, {
                    value: 'diversificado1',
                    text: 'Perito Contador'
                }, {
                    value: 'diversificado2',
                    text: 'Perito en Administración'
                }],
                'fin-de-semana': [{
                    value: 'noselecciono',
                    text: '- Seleccione Nivel -'
                }, {
                    value: 'basico',
                    text: 'Básico Regular'
                }, {
                    value: 'basicopormadurez',
                    text: 'Básico por Madurez'
                }, {
                    value: 'diversificado4',
                    text: 'Bachiller por Madurez'
                }, {
                    value: 'diversificado1',
                    text: 'Perito Contador Fin de Semana'
                }]
            },
            grados: {
                'noselecciono': [{
                    value: '',
                    text: '- Seleccione Grado -'
                }],
                'preprimaria': [{
                    value: '1grado',
                    text: 'Párvulos I'
                }, {
                    value: '2grado',
                    text: 'Párvulos II'
                }, {
                    value: '3grado',
                    text: 'Párvulos III'
                }],
                'primaria': [{
                    value: '1grado',
                    text: 'Primero'
                }, {
                    value: '2grado',
                    text: 'Segundo'
                }, {
                    value: '3grado',
                    text: 'Tercero'
                }, {
                    value: '4grado',
                    text: 'Cuarto'
                }, {
                    value: '5grado',
                    text: 'Quinto'
                }, {
                    value: '6grado',
                    text: 'Sexto'
                }],
                'basico': [{
                    value: '1grado',
                    text: 'Primero'
                }, {
                    value: '2grado',
                    text: 'Segundo'
                }, {
                    value: '3grado',
                    text: 'Tercero'
                }],
                'basicopormadurez': [{
                    value: '1grado',
                    text: 'Etapa I'
                }, {
                    value: '2grado',
                    text: 'Etapa II'
                }],
                'diversificado1': [{
                    value: '4grado',
                    text: 'Cuarto'
                }, {
                    value: '5grado',
                    text: 'Quinto'
                }, {
                    value: '6grado',
                    text: 'Sexto'
                }],
                'diversificado2': [{
                    value: '4grado',
                    text: 'Cuarto'
                }, {
                    value: '5grado',
                    text: 'Quinto'
                }, {
                    value: '6grado',
                    text: 'Sexto'
                }],
                'diversificado3': [{
                    value: '4grado',
                    text: 'Cuarto'
                }, {
                    value: '5grado',
                    text: 'Quinto'
                }],
                'diversificado4': [{
                    value: '4grado',
                    text: 'Etapa Única'
                }]
            }
        };

        // --- Funciones para Selects (igual que antes) ---
        function llenarSelect(selectElement, optionsArray, selectedValue = null) {
            selectElement.innerHTML = '';
            optionsArray.forEach(opt => {
                const option = document.createElement('option');
                option.value = opt.value;
                option.textContent = opt.text;
                option.disabled = (opt.value === 'noselecciono' || opt.value === '');
                if (opt.value === selectedValue) {
                    option.selected = true;
                } else if (!selectedValue && (opt.value === 'noselecciono' || opt.value === '')) {
                    option.selected = true; // Seleccionar por defecto "Seleccione..." si no hay valor previo
                }
                selectElement.appendChild(option);
            });
            if (selectedValue) {
                selectElement.dispatchEvent(new Event('change'));
            }
        }

        function actualizarNiveles() {
            const jornada = jornadaSelect.value;
            const nivelActualSeleccionado = nivelSelect.value; // Guardar valor actual antes de limpiar
            const nivelesDisponibles = opciones.niveles[jornada] || opciones.niveles['noselecciono'];
            // Preservar la selección solo si el nivel actual existe en las nuevas opciones
            const preservarNivel = nivelesDisponibles.some(n => n.value === nivelActualSeleccionado) ? nivelActualSeleccionado : 'noselecciono';
            llenarSelect(nivelSelect, nivelesDisponibles, preservarNivel);
            actualizarGrados(); // Siempre actualizar grados al cambiar jornada
            actualizarVisibilidadBotonCrear(); // Actualizar visibilidad del botón Crear
        }

        function actualizarGrados() {
            const nivel = nivelSelect.value;
            const gradoActualSeleccionado = gradoSelect.value; // Guardar valor actual
            const gradosDisponibles = opciones.grados[nivel] || opciones.grados['noselecciono'];
            // Preservar selección
            const preservarGrado = gradosDisponibles.some(g => g.value === gradoActualSeleccionado) ? gradoActualSeleccionado : '';
            llenarSelect(gradoSelect, gradosDisponibles, preservarGrado);
            actualizarVisibilidadBotonCrear(); // Actualizar visibilidad del botón Crear
        }

        // --- Funciones para el Modal de Edición (igual que antes) ---
        function abrirModal(id, nombre) {
            inputIdCurso.value = id;
            inputNombreCurso.value = nombre;
            mensajeModal.textContent = '';
            modalEditar.style.display = 'flex';
            inputNombreCurso.focus();
        }

        function cerrarModal() {
            modalEditar.style.display = 'none';
            formEditarCurso.reset();
            mensajeModal.textContent = '';
        }

        function guardarCambiosCurso(event) {
            event.preventDefault();
            mensajeModal.textContent = '';
            const formData = new FormData(formEditarCurso);
            const nuevoNombre = formData.get('nombre_curso').trim();
            if (!nuevoNombre) {
                mensajeModal.textContent = 'El nombre no puede estar vacío.';
                return;
            }
            const submitButton = formEditarCurso.querySelector('button[type="submit"]');
            submitButton.disabled = true;
            submitButton.textContent = 'Guardando...';

            fetch(window.location.pathname, {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const fila = document.querySelector(`tr[data-id-curso="${data.id_curso}"]`);
                        if (fila) {
                            const celdaNombre = fila.querySelector('.nombre-curso');
                            const botonEditar = fila.querySelector('.btn-editar');
                            if (celdaNombre) celdaNombre.textContent = data.nombre;
                            if (botonEditar) botonEditar.dataset.nombre = data.nombre;
                        }
                        cerrarModal();
                    } else {
                        mensajeModal.textContent = data.message || 'Error desconocido.';
                    }
                })
                .catch(error => {
                    console.error('Error fetch:', error);
                    mensajeModal.textContent = 'Error de conexión.';
                })
                .finally(() => {
                    submitButton.disabled = false;
                    submitButton.textContent = 'Guardar Cambios';
                });
        }
        // --- Función para Eliminar Curso (igual que antes) ---
        function eliminarCurso(idCurso, botonEliminar) {
            if (!confirm('¿Seguro de eliminar este curso?')) return;
            const formData = new FormData();
            formData.append('accion', 'eliminar');
            formData.append('id_curso', idCurso);
            const originalText = botonEliminar.textContent;
            botonEliminar.disabled = true;
            botonEliminar.textContent = 'Eliminando...';

            fetch(window.location.pathname, {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const fila = botonEliminar.closest('tr');
                        if (fila) fila.remove();
                        // Re-enumerar filas si es necesario
                        renumerarFilas();
                    } else {
                        alert(data.message || 'Error al eliminar.');
                        botonEliminar.disabled = false;
                        botonEliminar.textContent = originalText;
                    }
                })
                .catch(error => {
                    console.error('Error fetch:', error);
                    alert('Error de conexión.');
                    botonEliminar.disabled = false;
                    botonEliminar.textContent = originalText;
                });
        }

        // --- NUEVAS Funciones para Crear Curso ---
        function actualizarVisibilidadBotonCrear() {
            const jornadaValida = jornadaSelect.value && jornadaSelect.value !== 'noselecciono';
            const nivelValido = nivelSelect.value && nivelSelect.value !== 'noselecciono';
            const gradoValido = gradoSelect.value && gradoSelect.value !== ''; // Grado usa '' como valor no válido

            // Mostrar botón solo si se realizó una búsqueda (flag de PHP) Y las selecciones son válidas
            const mostrarBoton = <?php echo json_encode($mostrarResultados); ?> && jornadaValida && nivelValido && gradoValido;

            if (btnMostrarCrear) { // Asegurarse que el botón existe (se renderiza condicionalmente en PHP)
                btnMostrarCrear.style.display = mostrarBoton ? 'inline-block' : 'none';
                if (!mostrarBoton) {
                    areaCrearCurso.style.display = 'none'; // Ocultar form si el botón se oculta
                }
            }
            // Actualizar valores ocultos en el form de creación
            inputJornadaCrear.value = jornadaSelect.value;
            inputNivelCrear.value = nivelSelect.value;
            inputGradoCrear.value = gradoSelect.value;

        }

        function mostrarFormularioCrear() {
            areaCrearCurso.style.display = 'block';
            inputNombreNuevo.value = ''; // Limpiar campo
            mensajeCrear.textContent = ''; // Limpiar mensaje
            mensajeCrear.className = ''; // Resetear clase de mensaje
            inputNombreNuevo.focus();
        }

        function cancelarCreacion() {
            areaCrearCurso.style.display = 'none';
            mensajeCrear.textContent = '';
            mensajeCrear.className = '';
        }

        function crearNuevoCurso(event) {
            event.preventDefault();
            mensajeCrear.textContent = '';
            mensajeCrear.className = '';

            const formData = new FormData(formCrearCurso);
            const nombreNuevo = formData.get('nombre_curso_nuevo').trim();

            if (!nombreNuevo) {
                mensajeCrear.textContent = 'El nombre del curso no puede estar vacío.';
                mensajeCrear.className = 'error';
                return;
            }
            // Validar que los campos ocultos tengan valores válidos (doble chequeo)
            if (!formData.get('jornada_crear') || formData.get('jornada_crear') === 'noselecciono' ||
                !formData.get('nivel_crear') || formData.get('nivel_crear') === 'noselecciono' ||
                !formData.get('grado_crear') || formData.get('grado_crear') === '') {
                mensajeCrear.textContent = 'La selección de Jornada/Nivel/Grado no es válida.';
                mensajeCrear.className = 'error';
                return;
            }


            const submitButton = formCrearCurso.querySelector('button[type="submit"]');
            submitButton.disabled = true;
            submitButton.textContent = 'Guardando...';

            fetch(window.location.pathname, {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.nuevo_curso) {
                        // Éxito: Añadir fila a la tabla
                        agregarFilaCurso(data.nuevo_curso);
                        cancelarCreacion(); // Ocultar formulario
                        // Opcional: mostrar mensaje global de éxito brevemente
                        // alert(data.message);
                    } else {
                        // Error: Mostrar mensaje en el área de creación
                        mensajeCrear.textContent = data.message || 'Error desconocido al crear.';
                        mensajeCrear.className = 'error';
                    }
                })
                .catch(error => {
                    console.error('Error fetch crear:', error);
                    mensajeCrear.textContent = 'Error de conexión al intentar crear.';
                    mensajeCrear.className = 'error';
                })
                .finally(() => {
                    submitButton.disabled = false;
                    submitButton.textContent = 'Guardar Curso';
                });
        }

        function agregarFilaCurso(curso) {
            let tabla = document.getElementById('tabla-cursos');
            let tbody;

            // Si la tabla no existe, crearla (caso: no había resultados antes)
            if (!tabla) {
                resultadosArea.innerHTML = `
                <table id="tabla-cursos">
                    <thead>
                        <tr>
                            <th>N°</th>
                            <th>Nombre del Curso</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            `;
                // Re-seleccionar la tabla y el tbody recién creados
                tabla = document.getElementById('tabla-cursos');
                // Asegurarse de que el botón de crear siga visible si se creó la tabla
                if (btnMostrarCrear) btnMostrarCrear.style.display = 'inline-block';
            }

            tbody = tabla.querySelector('tbody');

            // Remover mensaje "No se encontraron cursos" si existe
            const msgNoCursos = document.getElementById('msg-no-cursos');
            if (msgNoCursos) {
                msgNoCursos.remove();
            }


            const nuevaFila = document.createElement('tr');
            nuevaFila.dataset.idCurso = curso.id;

            const contadorActual = tbody.rows.length + 1;

            nuevaFila.innerHTML = `
            <td>${contadorActual}</td>
            <td class="nombre-curso">${escapeHTML(curso.nombre)}</td>
            <td>
                <button class='acciones-btn btn-editar' data-id='${curso.id}' data-nombre='${escapeHTML(curso.nombre)}'>Editar</button>
                <button class='acciones-btn btn-eliminar' data-id='${curso.id}'>Eliminar</button>
            </td>
        `;
            tbody.appendChild(nuevaFila);
        }

        // Función para re-enumerar filas después de eliminar
        function renumerarFilas() {
            const tabla = document.getElementById('tabla-cursos');
            if (tabla) {
                const tbody = tabla.querySelector('tbody');
                if (tbody) {
                    const filas = tbody.querySelectorAll('tr');
                    filas.forEach((fila, index) => {
                        const celdaNumero = fila.querySelector('td:first-child');
                        if (celdaNumero) {
                            celdaNumero.textContent = index + 1;
                        }
                    });
                    // Si no quedan filas, mostrar mensaje de no cursos y ocultar botón crear
                    if (filas.length === 0) {
                        const msg = document.createElement('p');
                        msg.className = 'no-cursos';
                        msg.id = 'msg-no-cursos';
                        msg.textContent = 'No hay cursos para la selección actual.';
                        // Insertar mensaje después de la tabla (o donde estuviera originalmente)
                        tabla.parentNode.insertBefore(msg, tabla.nextSibling);
                        tabla.remove(); // Eliminar la tabla vacía
                        if (btnMostrarCrear) btnMostrarCrear.style.display = 'inline-block'; //Mantener botón crear visible
                    }
                }
            }
        }

        // Helper para escapar HTML antes de insertarlo en la tabla
        function escapeHTML(str) {
            const div = document.createElement('div');
            div.textContent = str;
            return div.innerHTML;
        }

        // --- EVENT LISTENERS ---
        document.addEventListener('DOMContentLoaded', function() {
            const jornadaPHP = '<?php echo $jornadaSeleccionada; ?>';
            const nivelPHP = '<?php echo $nivelSeleccionado; ?>';
            const gradoPHP = '<?php echo $gradoSeleccionado; ?>';

            // Llenado inicial de selects y preservación de valores PHP
            llenarSelect(jornadaSelect, opciones.jornadas, jornadaPHP);
            actualizarNiveles(); // Carga niveles basado en jornadaPHP
            if (nivelPHP && jornadaPHP !== 'noselecciono') {
                nivelSelect.value = nivelPHP; // Forzar valor si existe en opciones
                nivelSelect.dispatchEvent(new Event('change')); // Disparar para cargar grados
            }
            actualizarGrados(); // Carga grados basado en nivelPHP
            if (gradoPHP && nivelPHP !== 'noselecciono') {
                gradoSelect.value = gradoPHP; // Forzar valor si existe en opciones
                gradoSelect.dispatchEvent(new Event('change')); // Disparar para actualizar botón crear
            }

            // Listeners para cambios en selects
            jornadaSelect.addEventListener('change', actualizarNiveles);
            nivelSelect.addEventListener('change', actualizarGrados);
            gradoSelect.addEventListener('change', actualizarVisibilidadBotonCrear); // Añadido


            // Listener para botones de la tabla (Editar/Eliminar) - Delegación
            const tablaCursos = document.getElementById('tabla-cursos');
            if (tablaCursos) {
                tablaCursos.parentNode.addEventListener('click', function(event) { // Escuchar en el contenedor por si la tabla se recrea
                    const target = event.target;
                    if (target.classList.contains('btn-editar')) {
                        const id = target.dataset.id;
                        const nombre = target.dataset.nombre;
                        abrirModal(id, nombre);
                    } else if (target.classList.contains('btn-eliminar')) {
                        const id = target.dataset.id;
                        eliminarCurso(id, target);
                    }
                });
            }

            // Listener para botón "Crear Nuevo Curso"
            if (btnMostrarCrear) {
                btnMostrarCrear.addEventListener('click', mostrarFormularioCrear);
            }

            // Cerrar modal si se hace clic fuera
            window.addEventListener('click', function(event) {
                if (event.target == modalEditar) {
                    cerrarModal();
                }
            });

            // Llamada inicial para mostrar/ocultar botón crear basado en la carga inicial
            actualizarVisibilidadBotonCrear();

        }); // Fin DOMContentLoaded
    </script>

</body>

</html>
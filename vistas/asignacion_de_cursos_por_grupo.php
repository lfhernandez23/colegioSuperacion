<?php require_once __DIR__ . '/../controlador/auth.php'; colegio_requerir_sesion(); ?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/inscripcionstyle.css" />
    <link rel="stylesheet" href="../css/navstyle.css" />
    <title>Asignar Cursos por Grupo</title>
  <link rel="stylesheet" href="../css/ui-standard.css">
</head>

<body>

<?php include './header.php'; ?>

    <br><br><br>

    <div class="container">
        <h2>Asignar Cursos por Grupo</h2>

        <form action="" method="get">

            <label for="jornada">Jornada</label>
            <select id="jornada" name="jornada" required onchange="actualizarNivel()">
                <option value="noselecciono" selected>- Selecione una Opcion</option>
                <option value="diario-matutino">Plan Diario - Matutino</option>
                <option value="diario-vespertino">Plan Diario - Vespertino</option>
                <option value="fin-de-semana">Plan Fin de Semana - Sabatino</option>
            </select>

            <label for="nivel">Nivel Asignado</label>
            <select id="nivel" name="nivel" required>
                </select>

            <label for="grado">Grado Asignado</label>
            <select id="grado" name="grado" required>
                </select>

            <label for="ciclo_escolar">Ciclo Escolar</label>
            <input type="text" id="ciclo_escolar" name="ciclo_escolar" required />
            <button type="submit" name="buscar">Buscar</button>
            <br>
        </form>

        <?php
        if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET["buscar"])) {
            include_once "../controlador/conexion_bd.php";

            $ciclo_escolar = $_GET['ciclo_escolar'];
            $jornada = $_GET['jornada'];
            $nivel = $_GET['nivel'];
            $grado = $_GET['grado'];

            // Obtener los estudiantes del mismo grupo
            $consultaEstudiantes = "SELECT id, carnet FROM registro_estudiantes WHERE ciclo_escolar = ? AND jornada = ? AND nivel = ? AND grado = ?";
            $stmt = $conn->prepare($consultaEstudiantes);
            $stmt->bind_param("ssss", $ciclo_escolar, $jornada, $nivel, $grado);
            $stmt->execute();
            $resultadoEstudiantes = $stmt->get_result();

            if ($resultadoEstudiantes->num_rows > 0) {
                echo '<form action="" method="get">';
                echo '<h3>Cursos Disponibles</h3>';
                echo '<p>Selecciona los cursos a asignar:</p>';
                echo '<table border="1" style="width: 100%; text-align: left; border-collapse: collapse;">';
                echo '<thead><tr><th>Seleccionar</th><th>Nombre del Curso</th></tr></thead>';
                echo '<tbody>';

                $consultaCursos = "SELECT id, curso FROM cursos WHERE jornada = ? AND nivel = ? AND grado = ?";
                $stmtCursos = $conn->prepare($consultaCursos);
                $stmtCursos->bind_param("sss", $jornada, $nivel, $grado);
                $stmtCursos->execute();
                $resultadoCursos = $stmtCursos->get_result();

                while ($curso = $resultadoCursos->fetch_assoc()) {
                    echo '<tr>';
                    echo '<td style="padding: 10px;"><input type="checkbox" name="cursos[]" value="' . $curso['id'] . '"></td>';
                    echo '<td style="padding: 10px;">' . htmlspecialchars($curso['curso']) . '</td>';
                    echo '</tr>';
                }

                echo '</tbody></table>';

                echo '<input type="hidden" name="ciclo_escolar" value="' . htmlspecialchars($ciclo_escolar) . '" />';
                echo '<input type="hidden" name="jornada" value="' . htmlspecialchars($jornada) . '" />';
                echo '<input type="hidden" name="nivel" value="' . htmlspecialchars($nivel) . '" />';
                echo '<input type="hidden" name="grado" value="' . htmlspecialchars($grado) . '" />';
                echo '<div id="hiddenInputsContainer"></div>';
                echo '<br><button type="submit" name="asignar_cursos">Asignar Cursos</button>';
                echo '</form>';
            } else {
                echo "No se encontraron estudiantes en este grupo.";
            }
        }

        if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET["asignar_cursos"])) {
            include_once "../controlador/conexion_bd.php";

            $ciclo_escolar = $_GET['ciclo_escolar'];
$jornada = $_GET['jornada'];
$nivel = $_GET['nivel'];
$grado = $_GET['grado'];
$cursos_seleccionados = isset($_GET['cursos']) ? $_GET['cursos'] : [];

        if (empty($cursos_seleccionados)) {
            echo "Debes seleccionar al menos un curso.";
            exit();
        }

    // Obtener los estudiantes del grupo
    $consultaEstudiantes = "SELECT id, carnet FROM registro_estudiantes WHERE ciclo_escolar = ? AND jornada = ? AND nivel = ? AND grado = ?";
    $stmt = $conn->prepare($consultaEstudiantes);
    $stmt->bind_param("ssss", $ciclo_escolar, $jornada, $nivel, $grado);
    $stmt->execute();
    $resultadoEstudiantes = $stmt->get_result();
    
    if ($resultadoEstudiantes->num_rows > 0) {
        $consultaInsertar = "INSERT INTO asignacion_cursos (id_estudiante, carnet, id_curso, ciclo_escolar) VALUES (?, ?, ?, ?)";
        $stmtInsertar = $conn->prepare($consultaInsertar);
    
        $total_estudiantes = 0;
        $total_cursos = count($cursos_seleccionados);
    
        while ($estudiante = $resultadoEstudiantes->fetch_assoc()) {
            $id_estudiante = $estudiante['id'];
            $carnet = $estudiante['carnet'];
    
            // Obtener los cursos ya asignados al estudiante
            $consultaCursosAsignados = "SELECT id_curso FROM asignacion_cursos WHERE id_estudiante = ? AND ciclo_escolar = ?";
            $stmtCursosAsignados = $conn->prepare($consultaCursosAsignados);
            $stmtCursosAsignados->bind_param("is", $id_estudiante, $ciclo_escolar);
            $stmtCursosAsignados->execute();
            $resultadoCursosAsignados = $stmtCursosAsignados->get_result();
    
            $cursos_asignados = [];
            while ($fila = $resultadoCursosAsignados->fetch_assoc()) {
                $cursos_asignados[] = $fila['id_curso'];
            }
    
            // Insertar solo los cursos que aún no estén asignados
            foreach ($cursos_seleccionados as $id_curso) {
                if (!in_array($id_curso, $cursos_asignados)) {
                    $stmtInsertar->bind_param("isis", $id_estudiante, $carnet, $id_curso, $ciclo_escolar);
                    $stmtInsertar->execute();
                }
            }
    
            $total_estudiantes++;
        }
    
        echo "Se asignaron $total_cursos cursos a $total_estudiantes estudiantes.";
    } else {
        echo "No se encontraron estudiantes en este grupo.";
    }
    
} 
?>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
    let checkboxes = document.querySelectorAll("input[name='cursos[]']");
    let selectedCourses = [];

    checkboxes.forEach(checkbox => {
        checkbox.addEventListener("change", function () {
            let courseId = this.value;

            if (this.checked) {
                // Agregar curso con número de orden
                selectedCourses.push(courseId);
            } else {
                // Remover curso y reorganizar orden
                selectedCourses = selectedCourses.filter(id => id !== courseId);
            }

            updateHiddenInputs();
            updateCheckboxDataOrder();
        });
    });

    function updateCheckboxDataOrder() {
        checkboxes.forEach(checkbox => {
            checkbox.removeAttribute("data-order"); // Resetear orden
        });

        selectedCourses.forEach((courseId, index) => {
            let checkbox = document.querySelector(`input[name='cursos[]'][value='${courseId}']`);
            if (checkbox) {
                checkbox.setAttribute("data-order", index + 1);
            }
        });
    }

    function updateHiddenInputs() {
        let container = document.getElementById("hiddenInputsContainer");
        container.innerHTML = ""; // Limpiar inputs previos

        selectedCourses.forEach((courseId, index) => {
            let input = document.createElement("input");
            input.type = "hidden";
            input.name = "ordered_courses[]";
            input.value = `${courseId}-${index + 1}`;
            container.appendChild(input);
        });
    }
});
</script>




<script>
    document.addEventListener("DOMContentLoaded", function () {
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
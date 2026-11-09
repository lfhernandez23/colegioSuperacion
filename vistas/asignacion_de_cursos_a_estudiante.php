<?php require_once __DIR__ . '/../controlador/auth.php'; colegio_requerir_sesion(); ?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/inscripcionstyle.css" />
    <link rel="stylesheet" href="../css/navstyle.css" />
    <title>Buscar y Editar Inscripción</title>
  <link rel="stylesheet" href="../css/ui-standard.css">
</head>

<body>

<?php include './header.php'; ?>
    <br><br><br>
    <div class="container">
        <h2>Buscar y Asignar Cursos a Estudiante</h2>

        <form action="" method="get">
            <label for="carnet">Número de Carnet</label>
            <input type="text" id="carnet" name="carnet" value="<?php echo isset($_GET['carnet']) ? $_GET['carnet'] : ''; ?>" required />

            <label for="ciclo_escolar">Ciclo Escolar Inscrito</label>
            <input type="text" id="ciclo_escolar" name="ciclo_escolar" value="<?php echo isset($_GET['ciclo_escolar']) ? $_GET['ciclo_escolar'] : ''; ?>" required />

            <button type="submit" name="buscar">Buscar</button>
        </form>
        <?php
        if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET["asignar_cursos"])) {
            include_once "../controlador/conexion_bd.php";
        
            // Recibir los datos del formulario
            $carnet = $_GET['carnet'];
            $ciclo_escolar = $_GET['ciclo_escolar'];
            $cursos_seleccionados = isset($_GET['cursos']) ? $_GET['cursos'] : [];
        
            // Verificar si hay cursos seleccionados
            if (empty($cursos_seleccionados)) {
                echo "Debes seleccionar al menos un curso.";
                exit();
            }
        
            // Obtener el ID del estudiante basado en el carnet
            $consultaEstudiante = "SELECT id FROM registro_estudiantes WHERE carnet = ? and ciclo_escolar = ?";
            $stmt = $conn->prepare($consultaEstudiante);
            $stmt->bind_param("ss", $carnet, $ciclo_escolar);
            $stmt->execute();
            $resultadoEstudiante = $stmt->get_result();
        
            if ($resultadoEstudiante->num_rows > 0) {
                $estudiante = $resultadoEstudiante->fetch_assoc();
                $id_estudiante = $estudiante['id'];
        
                // Preparar consulta de inserción
                $consultaInsertar = "INSERT INTO asignacion_cursos (id_estudiante, carnet, id_curso, ciclo_escolar) VALUES (?, ?, ?, ?)";
                $stmtInsertar = $conn->prepare($consultaInsertar);
        
                foreach ($cursos_seleccionados as $id_curso) {
                    // Insertar cada curso seleccionado
                    $stmtInsertar->bind_param("isis", $id_estudiante, $carnet, $id_curso, $ciclo_escolar, );
                    $stmtInsertar->execute();
                }
        
                echo "Cursos asignados con éxito.";
            } else {
                echo "No se encontró un estudiante con el carnet proporcionado.";
            }
        
            // Cerrar conexiones
            $stmt->close();
            $stmtInsertar->close();
            $conn->close();
        }        
        ?>
        <?php
        if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET["buscar"])) {
            $carnet = $_GET["carnet"];
            $ciclo_escolar = $_GET["ciclo_escolar"];

            include_once "../controlador/conexion_bd.php";

            $consulta = "SELECT * FROM registro_estudiantes WHERE carnet LIKE CONCAT('%', ?, '%') AND ciclo_escolar = ?";
            $sentencia = $conn->prepare($consulta);

            if ($sentencia) {
                $sentencia->bind_param("ss", $carnet, $ciclo_escolar);
                $sentencia->execute();
                $resultados = $sentencia->get_result();
                $sentencia->close();
            } else {
                die("Error en la consulta: " . $conn->error);
            }

            if ($resultados->num_rows > 0) {
                while ($resultados_array = $resultados->fetch_assoc()) {
                    $codigo = $resultados_array['codigo'];
                    $nombres = $resultados_array['nombre'];
                    $apellidos = $resultados_array['apellido'];
                    $jornada = $resultados_array['jornada'];
                    $grado = $resultados_array['grado'];
                    $nivel = $resultados_array['nivel'];
                    $ciclo_escolar = $resultados_array['ciclo_escolar'];
        ?>
                    <br>
                    <form id="editarForm" action="../vistas/asignacion_de_cursos_a_estudiante.php" method="get">
                        <!-- Campos ocultos con información del estudiante -->
                        <input type="hidden" name="carnet" value="<?php echo htmlspecialchars($carnet); ?>" />
                        <input type="hidden" name="codigo" value="<?php echo htmlspecialchars($codigo); ?>" />
                        <input type="hidden" name="nombres" value="<?php echo htmlspecialchars($nombres); ?>" />
                        <input type="hidden" name="apellidos" value="<?php echo htmlspecialchars($apellidos); ?>" />
                        <input type="hidden" name="jornada" value="<?php echo htmlspecialchars($jornada); ?>" />
                        <input type="hidden" name="nivel" value="<?php echo htmlspecialchars($nivel); ?>" />
                        <input type="hidden" name="grado" value="<?php echo htmlspecialchars($grado); ?>" />
                        <input type="hidden" name="ciclo_escolar" value="<?php echo htmlspecialchars($ciclo_escolar); ?>" />

                        <h3>Cursos Disponibles</h3>
                        <p>Haz click en la caja del curso que deseas asignar:</p>
                        <table border="1" style="width: 100%; text-align: left; border-collapse: collapse;">
                            <thead>
                                <tr>
                                    <th style="padding: 10px;">Seleccionar</th>
                                    <th style="padding: 10px;">Nombre del Curso</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                include_once "../controlador/conexion_bd.php";

                                $consultaCursos = "SELECT id, curso FROM cursos WHERE jornada = ? AND nivel = ? AND grado = ?";
                                $stmt = $conn->prepare($consultaCursos);

                                if ($stmt) {
                                    $stmt->bind_param("sss", $jornada, $nivel, $grado);
                                    $stmt->execute();
                                    $resultadoCursos = $stmt->get_result();

                                    while ($curso = $resultadoCursos->fetch_assoc()) {
                                        echo "<tr>";
                                        echo '<td style="padding: 10px;"><input type="checkbox" name="cursos[]" value="' . $curso['id'] . '"></td>';
                                        echo '<td style="padding: 10px;">' . htmlspecialchars($curso['curso']) . '</td>';
                                        echo "</tr>";
                                    }
                                    $stmt->close();
                                }
                                ?>
                            </tbody>
                        </table>

                        <br>
                        <button type="submit" name="asignar_cursos" style="width: 600px;">Asignar Cursos</button>
                    </form>

        <?php
                }
            } else {
                echo "No se encontraron resultados para el carnet $carnet en el ciclo escolar $ciclo_escolar.";
            }
        }
        ?>
    </div>
</body>

</html>
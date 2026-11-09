<?php require_once __DIR__ . '/../controlador/auth.php'; colegio_requerir_sesion(); ?>
<?php
include_once "../controlador/conexion_bd.php";

$nombre_completo = "";
$ciclo_escolar = "";
$resultadoCursos = null;
$id_estudiante = null;

if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET["carnet"]) && isset($_GET["ciclo_escolar"])) {
    $carnet = $_GET["carnet"];
    $ciclo_escolar = $_GET["ciclo_escolar"];

    $consultaEstudiante = "SELECT id, nombre, apellido, carnet, jornada, grado, nivel FROM registro_estudiantes WHERE carnet = ? AND ciclo_escolar = ?";
    $stmt = $conn->prepare($consultaEstudiante);
    $stmt->bind_param("ss", $carnet, $ciclo_escolar);
    $stmt->execute();
    $resultadoEstudiante = $stmt->get_result();

    if ($resultadoEstudiante->num_rows > 0) {
        $estudiante = $resultadoEstudiante->fetch_assoc();
        $id_estudiante = $estudiante['id'];
        $nombre_completo = $estudiante['nombre'] . ' ' . $estudiante['apellido'];
        $carnet = $estudiante['carnet'];
        $jornada = $estudiante['jornada'];
        $nivel = $estudiante['nivel'];
        $grado = $estudiante['grado'];

        $consultaCursos = "SELECT c.id AS id_curso, c.curso, 
                                  COALESCE(cal.bimestre_1, 0) AS bimestre_1, 
                                  COALESCE(cal.bimestre_2, 0) AS bimestre_2, 
                                  COALESCE(cal.bimestre_3, 0) AS bimestre_3, 
                                  COALESCE(cal.bimestre_4, 0) AS bimestre_4
                            FROM asignacion_cursos ac
                            JOIN cursos c ON ac.id_curso = c.id
                            LEFT JOIN calificaciones cal 
                                ON ac.id_estudiante = cal.id_estudiante 
                                AND ac.id_curso = cal.id_curso 
                                AND ac.ciclo_escolar = cal.ciclo_escolar
                            WHERE ac.id_estudiante = ? AND ac.ciclo_escolar = ?";
        $stmt = $conn->prepare($consultaCursos);
        $stmt->bind_param("is", $id_estudiante, $ciclo_escolar);
        $stmt->execute();
        $resultadoCursos = $stmt->get_result();
    } else {
        echo "<p style='color:red; text-align:center;'>No se encontró el estudiante.</p>";
    }
}

function calcularPromedio($b1, $b2, $b3, $b4) {
    return ($b1 + $b2 + $b3 + $b4) / 4;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Calificaciones</title>
    <link rel="stylesheet" href="../css/navstyle.css" />
    <link rel="stylesheet" href="../css/inscripcionstyle.css">
    <link rel="stylesheet" href="../css/reportesstyle.css">
  <link rel="stylesheet" href="../css/ui-standard.css">
</head>
<body>
<?php include './header.php'; ?>

    <br>
    <br>
    <br>
    <br>
    <div class="container">
        <h2>Búsqueda de Estudiante</h2>
        <form method="GET" action="">
            <label for="carnet">Carnet del estudiante:</label>
            <input type="text" id="carnet" name="carnet" required>
            <label for="ciclo_escolar">Ciclo Escolar:</label>
            <input type="text" id="ciclo_escolar" name="ciclo_escolar" required>
            <button type="submit">Buscar</button>
        </form>

        <?php if (!empty($nombre_completo)): ?>
            <h2>Calificaciones del Estudiante</h2>
            <p><strong>Carnet:</strong> <?php echo htmlspecialchars($carnet); ?></p>
            <p><strong>Estudiante:</strong> <?php echo htmlspecialchars($nombre_completo); ?></p>
            <p><strong>Ciclo Escolar:</strong> <?php echo htmlspecialchars($ciclo_escolar); ?></p>
            <form action="../controlador/guardar_calificaciones.php" method="POST">
                <input type="hidden" name="id_estudiante" value="<?php echo htmlspecialchars($id_estudiante); ?>">
                <input type="hidden" name="ciclo_escolar" value="<?php echo htmlspecialchars($ciclo_escolar); ?>">

                <table border="1" style="width: 100%; text-align: center; border-collapse: collapse;">
                    <thead>
                        <tr>
                            <th>Curso</th>
                            <th>Bimestre 1</th>
                            <th>Bimestre 2</th>
                            <th>Bimestre 3</th>
                            <th>Bimestre 4</th>
                            <th>Promedio</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($curso = $resultadoCursos->fetch_assoc()): ?>
                            <?php $promedio = calcularPromedio($curso['bimestre_1'], $curso['bimestre_2'], $curso['bimestre_3'], $curso['bimestre_4']); ?>
                            <tr>
                                <td><?php echo htmlspecialchars($curso['curso']); ?></td>
                                <input type="hidden" name="id_curso[]" value="<?php echo htmlspecialchars($curso['id_curso']); ?>">
                                <td><input type="number" name="bimestre_1[]" value="<?php echo htmlspecialchars($curso['bimestre_1']); ?>" step="0.01" min="0" max="100"></td>
                                <td><input type="number" name="bimestre_2[]" value="<?php echo htmlspecialchars($curso['bimestre_2']); ?>" step="0.01" min="0" max="100"></td>
                                <td><input type="number" name="bimestre_3[]" value="<?php echo htmlspecialchars($curso['bimestre_3']); ?>" step="0.01" min="0" max="100"></td>
                                <td><input type="number" name="bimestre_4[]" value="<?php echo htmlspecialchars($curso['bimestre_4']); ?>" step="0.01" min="0" max="100"></td>
                                <td><?php echo number_format($promedio, 2); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                <br>
                <button type="submit">Guardar Calificaciones</button>
                <a href="../controlador/imprimir_boleta_de_calificacion.php?id_estudiante=<?php echo urlencode($id_estudiante); ?>&nombre=<?php echo urlencode($estudiante['nombre']); ?>&apellido=<?php echo urlencode($estudiante['apellido']); ?>&carnet=<?php echo urlencode($estudiante['carnet']); ?>&grado=<?php echo urlencode($grado); ?>&nivel=<?php echo urlencode($nivel); ?>&jornada=<?php echo urlencode($jornada); ?>&ciclo_escolar=<?php echo urlencode($ciclo_escolar); ?>">
                <button style="background-color: #4CAF50; color: white; padding: 10px 0; border: none; border-radius: 4px; cursor: pointer; font-size: 16px; width: 100%;" type="button">Imprimir Boleta de Calificaciones</button>
                </form>
        <?php endif; ?>
    </div>
</body>
</html>


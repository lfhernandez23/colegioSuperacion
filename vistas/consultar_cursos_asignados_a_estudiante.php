<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/inscripcionstyle.css" />
    <link rel="stylesheet" href="../css/navstyle.css" />
    <title>Consultar y Eliminar Cursos Asignados</title>
</head>

<body>

<?php include './header.php'; ?>

    <br><br><br>

    <div class="container">
        <h2>Consultar y Eliminar Cursos Asignados</h2>

        <form action="" method="get">
            <label for="carnet">Número de Carnet</label>
            <input type="text" id="carnet" name="carnet" value="<?php echo isset($_GET['carnet']) ? $_GET['carnet'] : ''; ?>" required />

            <label for="ciclo_escolar">Ciclo Escolar</label>
            <input type="text" id="ciclo_escolar" name="ciclo_escolar" value="<?php echo isset($_GET['ciclo_escolar']) ? $_GET['ciclo_escolar'] : ''; ?>" required />

            <button type="submit" name="buscar">Consultar Cursos</button>
        </form>

        <?php
        include_once "../controlador/conexion_bd.php";

        if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET["buscar"])) {
            $carnet = $_GET["carnet"];
            $ciclo_escolar = $_GET["ciclo_escolar"];

            // Consulta para obtener los cursos asignados al estudiante
            $consulta = "SELECT ac.id, c.curso 
                         FROM asignacion_cursos ac
                         INNER JOIN cursos c ON ac.id_curso = c.id
                         INNER JOIN registro_estudiantes re ON ac.id_estudiante = re.id
                         WHERE re.carnet = ? AND ac.ciclo_escolar = ?";
            $stmt = $conn->prepare($consulta);

            if ($stmt) {
                $stmt->bind_param("ss", $carnet, $ciclo_escolar);
                $stmt->execute();
                $resultado = $stmt->get_result();
                $stmt->close();
            } else {
                die("Error en la consulta: " . $conn->error);
            }

            if ($resultado->num_rows > 0) {
                echo "<h3>Cursos Asignados</h3>";
                echo "<table border='1' style='width: 100%; text-align: left; border-collapse: collapse;'>";
                echo "<thead><tr>
                        <th style='padding: 10px;'>Nombre del Curso</th>
                        <th style='padding: 10px;'>Acción</th>
                    </tr></thead>";
                echo "<tbody>";

                while ($curso = $resultado->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td style='padding: 10px;'>" . htmlspecialchars($curso['curso']) . "</td>";
                    echo "<td style='padding: 10px;'>
                            <form action='' method='post' style='display:inline;'>
                                <input type='hidden' name='id_asignacion' value='" . $curso['id'] . "' />
                                <input type='hidden' name='carnet' value='" . $carnet . "' />
                                <input type='hidden' name='ciclo_escolar' value='" . $ciclo_escolar . "' />
                                <button type='submit' name='eliminar' style='background-color: red;' onclick='return confirm(\"¿Estás seguro de eliminar este curso?\")'>Eliminar</button>
                            </form>
                        </td>";
                    echo "</tr>";
                }

                echo "</tbody></table>";

                // Botón para redirigir a la asignación de cursos
                echo "<br>";
                echo "<a href='asignacion_de_cursos_a_estudiante.php?carnet=$carnet&ciclo_escolar=$ciclo_escolar' style='display:block; width: 100%; text-align:center; background-color:#28a745; color:white; padding:10px; text-decoration:none;'>Asignar más cursos</a>";
            } else {
                echo "<p>No se encontraron cursos asignados para el carnet $carnet en el ciclo escolar $ciclo_escolar.</p>";
                echo "<br>";
                echo "<a href='asignacion_de_cursos_a_estudiante.php?carnet=$carnet&ciclo_escolar=$ciclo_escolar' style='display:block; width: 100%; text-align:center; background-color:#28a745; color:white; padding:10px; text-decoration:none;'>Asignar más cursos</a>";
            }
        }

        // Lógica para eliminar curso asignado
        if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["eliminar"])) {
            $id_asignacion = $_POST["id_asignacion"];
            $carnet = $_POST["carnet"];
            $ciclo_escolar = $_POST["ciclo_escolar"];

            $consultaEliminar = "DELETE FROM asignacion_cursos WHERE id = ?";
            $stmtEliminar = $conn->prepare($consultaEliminar);

            if ($stmtEliminar) {
                $stmtEliminar->bind_param("i", $id_asignacion);
                if ($stmtEliminar->execute()) {
                    echo "<script>alert('Curso eliminado con éxito.'); window.location.href='?carnet=$carnet&ciclo_escolar=$ciclo_escolar&buscar=';</script>";
                } else {
                    echo "<p>Error al eliminar el curso.</p>";
                }
                $stmtEliminar->close();
            } else {
                die("Error en la consulta de eliminación: " . $conn->error);
            }
        }

        $conn->close();
        ?>
    </div>

</body>

</html>
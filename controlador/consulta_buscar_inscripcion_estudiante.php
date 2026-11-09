<?php require_once __DIR__ . '/auth.php'; colegio_requerir_sesion(); ?>
<?php
    require_once __DIR__ . "/conexion_bd.php";

    // Inicializar $resultados y otras variables
    $resultados = [];
    $sexo = $fechaNacimiento = $direccionVivienda = $nombrePadre = $nombreMadre = $fechaInscripcion = $infoAdicional = $telAlumno = $telEncargado = $emailEncargado = $jornada = '';

    // Verificar si se hizo una solicitud POST
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Obtener los datos del formulario
        $opcion = (string) ($_POST["opcion_busqueda"] ?? "");
        $criterio = trim((string) ($_POST["buscar"] ?? ""));

        // Solo se permite un catálogo de columnas, nunca SQL de entrada del usuario.
        $columnas = ['codigo' => 'codigo', 'carnet' => 'carnet', 'nombre' => 'nombre'];
        if (!isset($columnas[$opcion]) || $criterio === '') {
            http_response_code(400);
            echo '<p>Criterio de búsqueda inválido.</p>';
            exit;
        }

        $sql = "SELECT * FROM registro_estudiantes WHERE {$columnas[$opcion]} LIKE ? ORDER BY apellido, nombre LIMIT 50";
        $stmt = $conn->prepare($sql);
        $buscar = '%' . $criterio . '%';
        $stmt->bind_param('s', $buscar);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();

        // Verificar si se encontraron resultados
        if ($result->num_rows > 0) {
            // Obtener el primer resultado encontrado (puedes ajustar según tus necesidades)
            $resultados = $result->fetch_assoc();
            $sexo = $resultados['sexo'];
            $fechaNacimiento = $resultados['fecha_nacimiento'];
            $direccionVivienda = $resultados['direccion_vivienda'];
            $nombrePadre = $resultados['nombre_padre'];
            $nombreMadre = $resultados['nombre_madre'];
            $fechaInscripcion = $resultados['fecha_inscripcion'];
            $infoAdicional = $resultados['info_adicional'];
            $telAlumno = $resultados['tel_alumno'];
            $telEncargado = $resultados['tel_encargado'];
            $emailEncargado = $resultados['email_encargado'];
            $jornada = $resultados['jornada'];

            ?>
            <br>
            <form id="editarForm" action="../vistas/buscar_estudiante.php" method="post">
                <!-- Campos del formulario -->

                <!-- Resto de campos... -->

                <button type="submit">Guardar Cambios</button>
            </form>
            <?php
        } else {
            echo "<p>No se encontraron resultados.</p>";
        }
    }

    // Cerrar la conexión al finalizar el script o cuando no la necesites más
    $conn->close();
?>

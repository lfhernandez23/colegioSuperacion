<?php
    // Conexión a la BD colegio_superacion
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "colegio_superacion";

    $conn = new mysqli($servername, $username, $password, $dbname);

    // Verificar la conexión
    if ($conn->connect_error) {
        die("Conexión fallida: " . $conn->connect_error);
    }

    // Inicializar $resultados y otras variables
    $resultados = [];
    $sexo = $fechaNacimiento = $direccionVivienda = $nombrePadre = $nombreMadre = $fechaInscripcion = $infoAdicional = $telAlumno = $telEncargado = $emailEncargado = $jornada = '';

    // Verificar si se hizo una solicitud POST
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Obtener los datos del formulario
        $opcion = $_POST["opcion_busqueda"];
        $criterio = $_POST["buscar"];

        // Escapar las variables para evitar inyección de SQL
        $opcion = mysqli_real_escape_string($conn, $opcion);
        $criterio = mysqli_real_escape_string($conn, $criterio);

        // Construir la consulta según el criterio de búsqueda
        if ($opcion === 'codigo') {
            $sql = "SELECT * FROM registro_estudiantes WHERE codigo LIKE '%$criterio%'";
        } elseif ($opcion === 'carnet') {
            $sql = "SELECT * FROM registro_estudiantes WHERE carnet LIKE '%$criterio%'";
        } elseif ($opcion === 'nombre') {
            $sql = "SELECT * FROM registro_estudiantes WHERE nombres LIKE '%$criterio%'";
        } else {
            // Otras opciones de búsqueda según tus necesidades
            echo "<p>Opción de búsqueda no válida.</p>";
            exit; // Detener la ejecución del script
        }

        // Ejecutar la consulta
        $result = $conn->query($sql);

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
            <form id="editarForm" action="../vistas/editar_estudiante.php" method="post">
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

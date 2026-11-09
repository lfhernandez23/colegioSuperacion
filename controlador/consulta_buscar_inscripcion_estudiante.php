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

        // Mostrar todas las coincidencias (máximo 50); nunca alterar datos desde esta consulta.
        if ($result->num_rows > 0) {
            echo '<h2>Resultados de búsqueda</h2>';
            echo '<table><thead><tr><th>Carné</th><th>Nombre</th><th>Ciclo</th><th>Acción</th></tr></thead><tbody>';
            while ($fila = $result->fetch_assoc()) {
                $carnet = (string) $fila['carnet'];
                $ciclo = (string) $fila['ciclo_escolar'];
                $nombre = trim((string) $fila['nombre'] . ' ' . (string) $fila['apellido']);
                $url = '../vistas/buscar_estudiante.php?' . http_build_query([
                    'buscar' => '1',
                    'carnet' => $carnet,
                    'ciclo_escolar' => $ciclo,
                ]);
                echo '<tr><td>' . htmlspecialchars($carnet, ENT_QUOTES, 'UTF-8')
                    . '</td><td>' . htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8')
                    . '</td><td>' . htmlspecialchars($ciclo, ENT_QUOTES, 'UTF-8')
                    . '</td><td><a href="' . htmlspecialchars($url, ENT_QUOTES, 'UTF-8')
                    . '">Consultar inscripción</a></td></tr>';
            }
            echo '</tbody></table>';
        } else {
            echo '<p>No se encontraron resultados.</p>';
        }
    }

    // Cerrar la conexión al finalizar el script o cuando no la necesites más
    $conn->close();
?>

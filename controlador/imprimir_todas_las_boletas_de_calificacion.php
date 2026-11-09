<?php
// Archivo: imprimir_todas_boletas.php

// Iniciar buffer para manejo de salida y errores antes de enviar PDF
ob_start();

// --- Includes Necesarios ---
require_once "../controlador/conexion_bd.php"; // Conexión a la BD
require_once "../library/dompdf/autoload.inc.php"; // Autoloader de Dompdf
include_once "../controlador/funcion_generar_boleta.php";

use Dompdf\Dompdf;
use Dompdf\Options;

// --- Configuración Inicial ---
date_default_timezone_set("America/Guatemala"); // Asegurar timezone

// --- Validar y Obtener Parámetros de Filtro (desde POST) ---
if ($_SERVER["REQUEST_METHOD"] != "POST") {
    die("Error: Acceso no permitido."); // O redirigir a la página de búsqueda
}

$jornada = $_POST["jornada"] ?? '';
$nivel = $_POST["nivel"] ?? '';
$grado = $_POST["grado"] ?? '';
$ciclo_escolar = $_POST["ciclo_escolar"] ?? '';

// Validar que los filtros necesarios no estén vacíos
if (empty($jornada) || empty($nivel) || empty($grado) || empty($ciclo_escolar)) {
    die("Error: Faltan parámetros de filtro para generar las boletas.");
}

// --- Obtener Lista de Estudiantes (misma consulta que en la página de búsqueda) ---
// Asegúrate que la conexión $conn esté disponible desde conexion_bd.php
if (!$conn) {
      die("Error fatal: No se pudo establecer la conexión con la base de datos.");
}

$consulta_estudiantes = "SELECT id, carnet, nombre, apellido, jornada, nivel, grado, ciclo_escolar
                         FROM registro_estudiantes
                         WHERE jornada = ? AND nivel = ? AND grado = ? AND ciclo_escolar = ?
                         ORDER BY apellido, nombre ASC"; // Importante ordenar consistentemente

$stmt_estudiantes = $conn->prepare($consulta_estudiantes);
$html_total = ""; // Variable para acumular el HTML de todas las boletas

if ($stmt_estudiantes) {
    $stmt_estudiantes->bind_param("ssss", $jornada, $nivel, $grado, $ciclo_escolar);
    if ($stmt_estudiantes->execute()) {
        $resultados_estudiantes = $stmt_estudiantes->get_result();

        if ($resultados_estudiantes->num_rows > 0) {
            // --- Iterar y Generar HTML para cada Boleta ---
            // Ya no se necesita $primera_boleta si no hay saltos de página forzados entre boletas
            while ($estudiante = $resultados_estudiantes->fetch_assoc()) {
                // Llamar a la función refactorizada para obtener el HTML de una boleta
                $html_boleta_individual = generarHtmlBoletaEstudiante($conn, $estudiante);

                if (!empty($html_boleta_individual)) {
                    // Simplemente añadir el HTML de la boleta.
                    // Dompdf manejará los saltos de página automáticamente cuando el contenido llene la página.
                    $html_total .= $html_boleta_individual;
                } else {
                    // Registrar que la boleta para este estudiante no se pudo generar
                    error_log("No se pudo generar la boleta para el estudiante ID: " . $estudiante['id'] . " Ciclo: " . $ciclo_escolar);
                }
            }
        } else {
            // No se encontraron estudiantes, mostrar mensaje y salir
            ob_end_clean(); // Limpiar buffer antes de mostrar mensaje
            echo "<!DOCTYPE html><html><head><title>Sin Resultados</title></head><body>";
            echo "<h1>No se encontraron estudiantes</h1>";
            echo "<p>No hay estudiantes que coincidan con los criterios de búsqueda seleccionados:</p>";
            echo "<ul>";
            echo "<li>Jornada: " . htmlspecialchars($jornada) . "</li>";
            echo "<li>Nivel: " . htmlspecialchars($nivel) . "</li>";
            echo "<li>Grado: " . htmlspecialchars($grado) . "</li>";
            echo "<li>Ciclo Escolar: " . htmlspecialchars($ciclo_escolar) . "</li>";
            echo "</ul>";
            echo "<p><a href='asignacion_de_calificaciones_a_estudiantes_por_grupos.php'>Volver a la búsqueda</a></p>";
            echo "</body></html>";
            exit;
        }
    } else {
        die("Error al ejecutar la consulta de estudiantes: " . htmlspecialchars($stmt_estudiantes->error));
    }
    $stmt_estudiantes->close();
} else {
    die("Error al preparar la consulta de estudiantes: " . htmlspecialchars($conn->error));
}

// Cerrar la conexión principal después de usarla
$conn->close();

// --- Generar el PDF Combinado ---
if (!empty($html_total)) {
    try {
        // Configurar Dompdf (similar a tu script original, pero considera habilitar recursos remotos si usas URLs para imágenes)
        $options = new Options();
        $options->set('isRemoteEnabled', true); // ¡IMPORTANTE si usas URLs http://localhost/ para imágenes!
        $options->set('defaultFont', 'Arial'); // O la fuente que prefieras y tengas instalada

        $dompdf = new Dompdf($options);
        
        // Es buena idea envolver todo el $html_total en una estructura básica de HTML si aún no lo está.
        // Sin embargo, si generarHtmlBoletaEstudiante ya genera bloques de div o similar, puede no ser estrictamente necesario.
        // Si $html_boleta_individual son fragmentos de HTML (ej. <div>...</div>), esto está bien.
        $dompdf->loadHtml($html_total);

        // Establecer tamaño y orientación del papel (igual que en tu script original)
        // [0, 0, 396, 612] es aprox. Media Carta (5.5" x 8.5" a 72 ppi). Verifica si es el tamaño deseado.
        // Podrías usar 'letter', 'A4', etc.
        $dompdf->setPaper([0, 0, 396, 612], "portrait");

        // Renderizar el PDF
        $dompdf->render();

        // Limpiar cualquier salida previa del buffer antes de enviar el PDF
        ob_end_clean();

        // Enviar el PDF al navegador para descarga
        // Crear un nombre de archivo descriptivo
        $nombre_archivo_base = "boletas_" . preg_replace('/[^A-Za-z0-9_\-]/', '', "{$jornada}_{$nivel}_{$grado}_{$ciclo_escolar}");
        $nombre_archivo_pdf = $nombre_archivo_base . ".pdf";

        // Attachment true = descargar, false = mostrar en navegador (si es compatible)
        $dompdf->stream($nombre_archivo_pdf, ["Attachment" => true]);
        exit; // Terminar script después de enviar el PDF

    } catch (Exception $e) {
        ob_end_clean(); // Limpiar buffer
        error_log("Error generando PDF con Dompdf: " . $e->getMessage());
        die("Error al generar el PDF combinado. Consulte el log del servidor para más detalles. Mensaje: " . htmlspecialchars($e->getMessage()));
    }

} else {
    // Si $html_total está vacío después del bucle (quizás todas las boletas individuales fallaron)
    ob_end_clean(); // Limpiar buffer
    echo "<!DOCTYPE html><html><head><title>Error</title></head><body>";
    echo "<h1>Error al Generar Boletas</h1>";
    echo "<p>No se pudo generar el contenido HTML para ninguna boleta con los criterios seleccionados. Verifique los logs del servidor.</p>";
    echo "<p><a href='asignacion_de_calificaciones_a_estudiantes_por_grupos.php'>Volver a la búsqueda</a></p>";
    echo "</body></html>";
    exit;
}

?>
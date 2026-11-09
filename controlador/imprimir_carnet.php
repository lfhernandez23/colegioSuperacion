<?php
// imprimir_carnet.php V4.3 - Generate PDF based on HTML Display Styles

// Error Reporting for Debugging (Consider removing/lowering in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 1. Includes and Namespaces
include_once "../controlador/conexion_bd.php"; // Ensure this path is correct and $conn is established
require_once "../library/dompdf/autoload.inc.php"; // Ensure this path is correct

use Dompdf\Dompdf;
use Dompdf\Options;

// --- Helper function to get Image Data URI (Robust version) ---
function getImageDataUri($path)
{
    // Get the directory of the current script
    $baseDir = __DIR__;
    // Handle paths starting with ../ relative to the script directory
    if (strpos($path, '../') === 0) {
        $absolutePath = realpath($baseDir . '/' . $path);
    } else {
        // Handle absolute paths or paths relative to the server root/document root
        // Using realpath ensures the path is absolute and resolves symlinks etc.
        // Checking path relative to baseDir first provides a fallback for paths that might seem absolute but aren't
        $absolutePath = realpath($path) ?: realpath($baseDir . '/' . $path);
    }

    // error_log("Attempting image: " . ($absolutePath ?: $path)); // Uncomment for path debugging

    // Check if path is resolved, file exists, and is readable
    if (!$absolutePath || !file_exists($absolutePath) || !is_readable($absolutePath)) {
        error_log("Image Warning: Cannot find or read image at resolved path: " . ($absolutePath ?: $path));
        // Return a minimal transparent pixel Data URI for a fallback image
        return 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUAAAAAIAAAACCAQAAADoXwTtAAAADklEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=';
    }

    try {
        // Get image data safely
        $imageData = @file_get_contents($absolutePath);
        if ($imageData === false) { throw new Exception("Failed to get contents."); }

        // Determine MIME type
        $type = mime_content_type($absolutePath);
        if ($type === false) {
            // Fallback to checking extension if mime_content_type fails
            $ext = strtolower(pathinfo($absolutePath, PATHINFO_EXTENSION));
            $types = [
                'png' => 'image/png',
                'jpg' => 'image/jpeg',
                'jpeg' => 'image/jpeg',
                'gif' => 'image/gif',
                'svg' => 'image/svg+xml'
            ];
            if (!isset($types[$ext])) { throw new Exception("Unsupported extension fallback."); }
            $type = $types[$ext];
        }

        // Encode to Base64
        $base64 = base64_encode($imageData);

        // Return Data URI
        return 'data:' . $type . ';base64,' . $base64;

    } catch (Exception $e) {
        error_log("Image Exception for {$path} -> {$absolutePath}: " . $e->getMessage());
        // Return a minimal transparent pixel Data URI on error
        return 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUAAAAAIAAAACCAQAAADoXwTtAAAADklEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=';
    }
}

// --- Función para limpiar datos (Pass $conn explicitly) ---
function limpiar_dato($dato, $conn)
{
    $cleaned_dato = trim($dato ?? '');
    // Only escape using mysqli_real_escape_string if connection is valid
    if ($conn && $conn instanceof mysqli) {
        // htmlspecialchars is applied later when building HTML
        return mysqli_real_escape_string($conn, $cleaned_dato);
    } else {
        // No DB connection, just trim
         return $cleaned_dato;
    }
}
// --- Fin Función limpiar_dato ---

// Database connection check
// Use $conn directly here as it should be included by conexion_bd.php
if (!isset($conn) || !($conn instanceof mysqli) || $conn->connect_error) {
     $error_msg = "Error Crítico: La conexión a la base de datos no es válida o falló.";
     // Ensure no output has been sent before sending headers
     if (!headers_sent()) {
         header('Content-Type: text/html; charset=UTF-8');
     }
     echo "<p style='color:red;'>{$error_msg}</p>";
     error_log("Critical DB Connection Error in imprimir_carnet.php: " . ($conn->connect_error ?? 'Connection object not set'));
     // Attempt to close if $conn is an object but connection failed
     if (isset($conn) && $conn instanceof mysqli) {
         $conn->close();
     }
     exit;
}


// Verificar si se reciben los datos del carnet
if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET["carnet"]) && isset($_GET["ciclo_escolar"])) {
    // Clean for DB query
    $carnet_id_db = limpiar_dato($_GET["carnet"], $conn);
    $ciclo_escolar_db = limpiar_dato($_GET["ciclo_escolar"], $conn);

    // Use the query to fetch data
    // Added COALESCE to handle potential NULL dates gracefully
    // Select all fields needed for display
    $consulta = "SELECT carnet, nombre_completo, grado, nivel, foto_estudiante, COALESCE(fecha_creacion, '') as fecha_creacion, COALESCE(fecha_vencimiento, '') as fecha_vencimiento
                 FROM carnet_estudiante
                 WHERE carnet = ? AND (fecha_creacion LIKE ? OR SUBSTRING(fecha_creacion, 1, 4) = ?)"; // Assuming fecha_creacion contains year
    $sentencia = $conn->prepare($consulta);

    if ($sentencia === false) {
        error_log("DB Prepare Error: " . $conn->error);
         if (!headers_sent()) { header('Content-Type: text/html; charset=UTF-8'); }
        echo "<p style='color:red;'>Error al preparar la consulta: (" . $conn->errno . ") " . htmlspecialchars($conn->error) . "</p>";
        $conn->close();
        exit;
    }

    $ciclo_like = "%" . $ciclo_escolar_db . "%";
    // Use the cleaned variables for binding
    $sentencia->bind_param("sss", $carnet_id_db, $ciclo_like, $ciclo_escolar_db); // Bind ciclo_escolar twice for LIKE and SUBSTRING check
    $sentencia->execute();
    $resultados = $sentencia->get_result();

    if ($resultados->num_rows > 0) {
        $datos_carnet = $resultados->fetch_assoc();

        // --- INICIO: Lógica de Generación de PDF con Dompdf ---
        try {
            // 1. Prepare data and images as Base64 Data URIs
            // Paths relative to the script file (../controlador/imprimir_carnet.php)
            $logoPath = '../img/log_colegio.png';
            $firmaPath = '../img/Firma_Sello.png';

            $logoColegioUri = getImageDataUri($logoPath);
            $firmaSelloUri = getImageDataUri($firmaPath);

            // Student Photo
            $rutaFotoEstudiante = $datos_carnet['foto_estudiante'] ?? null;
            $fotoEstudianteUri = null;
            if (!empty($rutaFotoEstudiante) && is_string($rutaFotoEstudiante)) {
                 // Assuming foto_estudiante in DB is like 'uploads/photos/student.jpg'
                 // Need to adjust path based on script location (../controlador/imprimir_carnet.php)
                 // If $rutaFotoEstudiante is like 'uploads/photos/student.jpg',
                 // then relative to THIS script, it's still '../uploads/photos/student.jpg'
                 // The getImageDataUri function handles the realpath resolution.
                 // Just ensure the path passed to it is correct relative to this script's folder.
                 // Let's assume the database path is already correct relative to the project root.
                 // If the script is in /controlador, and images are in /uploads, '../' is needed.
                 // If the DB path starts with /, remove it before adding ../
                 $pathFotoForHelper = '../' . ltrim($rutaFotoEstudiante, '/');
                 $fotoEstudianteUri = getImageDataUri($pathFotoForHelper);
            }

             // HTML for the photo or the placeholder
             // Used inline styles for placeholder that work better with Dompdf's rendering model
             $placeholderDiv = '<div class="photo photo-placeholder" style="border: 0.5pt dashed #999; background-color: #f0f0f0; color: #555; text-align: center; font-size: 6pt; box-sizing: border-box; height: 85.05pt; width: 56.7pt; border-radius: 4pt; line-height: 1.2; display: flex; align-items: center; justify-content: center;"><span>Foto no<br>disponible</span></div>';

             // Check if the URI is valid (starts with data:image and is substantial)
             $validPhotoUri = $fotoEstudianteUri && (strpos($fotoEstudianteUri, 'data:image') === 0) && strlen($fotoEstudianteUri) > 150;
             $fotoHtml = $validPhotoUri
                 ? '<img src="' . $fotoEstudianteUri . '" alt="Foto Estudiante" class="photo">'
                 : $placeholderDiv;


            // Formatear fecha de vencimiento
            $fecha_vencimiento_format = 'N/A';
            if (!empty($datos_carnet['fecha_vencimiento'])) {
                try {
                    $fecha_vencimiento_obj = DateTime::createFromFormat('Y-m-d', $datos_carnet['fecha_vencimiento']);
                    $fecha_vencimiento_format = $fecha_vencimiento_obj ? $fecha_vencimiento_obj->format('d/m/Y') : 'Fecha inválida';
                } catch (Exception $e) {
                    error_log("Date formatting error: " . $e->getMessage());
                    $fecha_vencimiento_format = 'Error fecha';
                }
            }

            // Clean data for HTML output using htmlspecialchars
            // Use the data fetched from the database, not the raw GET data again
            $carnet_no_html = htmlspecialchars($datos_carnet['carnet'] ?? 'N/A', ENT_QUOTES, 'UTF-8');
            $grado_html = htmlspecialchars($datos_carnet['grado'] ?? '', ENT_QUOTES, 'UTF-8');
            $nivel_html = htmlspecialchars($datos_carnet['nivel'] ?? 'N/A', ENT_QUOTES, 'UTF-8');
            $carrera_nivel_html = (!empty($grado_html) ? $grado_html . " - " : "") . $nivel_html;
            $nombre_completo_html = htmlspecialchars($datos_carnet['nombre_completo'] ?? 'N/A', ENT_QUOTES, 'UTF-8');
            $fecha_vencimiento_html = htmlspecialchars($fecha_vencimiento_format, ENT_QUOTES, 'UTF-8');
            // Escape the ciclo_escolar used in the filename as well
            $ciclo_escolar_html = htmlspecialchars($ciclo_escolar_db, ENT_QUOTES, 'UTF-8');


            // 2. Define the HTML and CSS for the carnet (Adapted from your display HTML/CSS)
            // Re-implementing layout using Dompdf-friendlier techniques (absolute positioning, points)
            // while trying to match the visual structure of your browser view.
            $html_pdf = <<<HTML
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Carnet Estudiante - {$carnet_no_html}</title>
<style>
    @page {
        size: 241pt 156pt; /* 8.5cm x 5.5cm (1pt = 1/72 inch, 1 inch = 2.54 cm) */
        margin: 0;
    }
    body {
        margin: 0; padding: 0; font-family: Helvetica, Arial, sans-serif; /* Dompdf standard font */
        font-size: 7.5pt; /* Base 10px -> ~7.5pt */
        line-height: 1.1;
        box-sizing: border-box;
    }
    .carnet-page { /* Equivalent to .carnet-card */
        width: 241pt; /* Match @page size */
        height: 156pt; /* Match @page size */
        box-sizing: border-box;
        padding: 6pt; /* 8px -> ~6pt */
        border: 0.75pt solid #ddd; /* 1px -> ~0.75pt */
        border-radius: 6pt; /* 8px -> ~6pt */
        position: relative; /* Needed for absolute positioning inside */
        overflow: hidden; /* Hide anything outside padding/border */
        page-break-after: always; /* Force a new page for each carnet side */
        /* Simulate background-image and opacity */
        background-color: rgba(255, 255, 255, 0.80); /* Background color */
        /* background-blend-mode is not supported. */
    }
    .carnet-page:last-child { page-break-after: auto; } /* No break after the very last page */

    /* Background Logo - Use absolute positioning as background-image with blend-mode is unreliable */
    .background-logo-container {
        position: absolute; top: 0; left: 0; width: 100%; height: 100%;
        z-index: 0; text-align: center; /* Center the image within the container */
    }
    .background-logo-container img {
        width: 96.4pt; /* Approx 40% of 241pt */
        height: auto;
        opacity: 0.15; /* Simulate blend mode effect */
        /* Position approx in the center */
        margin-top: 43.68pt; /* Approx 28% of 156pt to push it down */
    }

    .content-wrapper { position: relative; z-index: 1; height: 100%; }

    /* --- Front Styles (based on your HTML structure) --- */
    .front .carnet-header {
        position: relative; /* Container for logo and text */
        height: 38pt; /* Approx 50px */
        margin-bottom: 3pt;
    }
     /* Logo position adapted from your HTML but using absolute/point units */
    .front .logo-img {
        position: absolute;
        top: 0pt;
        left: 0pt; /* Aligned left within padding */
        width: 38pt; /* 50px -> ~38pt */
        height: auto;
    }
    /* School text container positioned next to logo */
    .front .school-text-container {
         position: absolute;
         top: 4pt; /* Adjusted vertical position */
         left: 45pt; /* Space for logo (38pt) + gap */
         right: 0; /* Extends to the right edge of the padding */
         text-align: center;
    }
    .front .school-name { /* "Colegio Privado Mixto" */
        font-size: 9pt; /* 12px -> ~9pt */
        font-weight: bold;
        color: black;
        line-height: 1.1;
        display: block;
    }
    .front .school-emphasis { /* "SUPERACIÓN" */
        display: block;
        font-size: 10.5pt; /* 14px -> ~10.5pt */
        font-weight: 600;
        color: #800000; /* Maroon */
        margin-top: 2pt; /* Reduced margin */
    }


    .front .carnet-content {
        position: relative; /* Container for info and photo */
        /* Calculated height based on page size and other elements */
        height: 83pt; /* Approx remaining space */
        margin-top: 5pt;
    }

    /* Info container positioned on the left */
    .front .info-container {
        position: absolute;
        top: 0;
        left: 0;
        width: 60%; /* Keep width proportion */
        font-size: 7.5pt; /* 10px -> ~7.5pt */
    }

    .info {
        margin: 1.5pt 0; /* 2px -> ~1.5pt */
        text-align: left; /* Default text align */
        word-wrap: break-word;
        white-space: normal;
        line-height: 1.2; /* Match browser view line height */
    }

    .red-text { color: red; font-weight: 600; }
    .blue-text { color: blue; font-weight: 600; }
    strong { font-weight: bold; }


    /* Photo container positioned on the right */
    .front .photo-container {
        position: absolute;
        top: 0;
        right: 0; /* Align right within padding */
        width: 35%; /* Proportion */
        text-align: right; /* Ensure image/placeholder is right-aligned if container is wider */
    }
    .front .photo {
        width: 56.7pt; /* 2cm */
        height: 85.05pt; /* 3cm */
        object-fit: cover; /* May not be fully supported */
        border-radius: 4pt; /* 5px -> ~4pt */
        border: 0.75pt solid #ccc;
        display: inline-block; /* Needed for dimensions */
        vertical-align: top; /* Align to top of photo container */
        background-color: #eee;
    }
    /* Placeholder styles are set inline via PHP for reliability */


    .front .carnet-footer-front {
        position: absolute; bottom: 1pt; left: 0; width: 100%;
        text-align: center;
        font-size: 7.5pt; /* 10px */
        font-weight: bold;
    }

    /* --- Back Styles (based on your HTML structure) --- */
    .back .content-wrapper {
         padding: 5pt; /* Adjusted padding for back side */
         text-align: center;
    }

    .back .info {
         font-size: 7.5pt; /* 10px */
         line-height: 1.2;
         margin: 3pt 0;
         text-align: center;
    }

    .back .legal-text { /* Smaller text justified */
         font-size: 7pt;
         text-align: justify;
         margin: 4pt 4pt;
         line-height: 1.3; /* Slightly more space for readability */
    }

    .back .firma-sello-container {
         width: 100%;
         text-align: center; /* Center the image */
         margin-top: 4pt;
         min-height: 40pt; /* Reserve space */
    }

    .back .firma-sello { /* The seal image */
        width: 113pt; /* 150px -> ~113pt */
        height: auto;
        display: inline-block;
        max-height: 45pt; /* Cap height */
    }

    .back .final-note { /* Italic note at bottom */
        font-size: 7pt;
        font-style: italic;
        margin-top: 5pt;
    }

</style>
</head>
<body>
    <div class="carnet-page front">
        <div class="background-logo-container">
             <img src="{$logoColegioUri}" alt="">
        </div>
        <div class="content-wrapper">
            <div class="carnet-header">
                <img src="{$logoColegioUri}" alt="Logo Colegio" class="logo-img">
                <div class="school-text-container">
                    <span class="school-name">Colegio Privado Mixto</span>
                    <span class="school-emphasis">SUPERACIÓN</span>
                </div>
            </div>
            <div class="carnet-content">
                <div class="info-container">
                    <p class="info red-text"><strong>CARNET N°:</strong> {$carnet_no_html}</p>
                    <p class="info blue-text"><strong>CARRERA:</strong> {$carrera_nivel_html}</p>
                    <p class="info blue-text"><strong>NOMBRE:</strong> {$nombre_completo_html}</p>
                </div>
                <div class="photo-container">
                    {$fotoHtml} {/* Contains <img> or placeholder <div> */}
                </div>
            </div>
            <p class="carnet-footer-front">Es válido hasta la fecha indicada al reverso del mismo</p>
        </div>
    </div>

    <div class="carnet-page back">
        <div class="background-logo-container">
             <img src="{$logoColegioUri}" alt="">
         </div>
        <div class="content-wrapper">
            <p class="info"><strong>Vence el {$fecha_vencimiento_html}</strong></p>

            <p class="info legal-text">Suplicamos a las autoridades civiles y militares guardar al portador las consideraciones correspondientes que la ley establece, según los artículos 5° y 6° de la Constitución de Guatemala.</p>

            <p class="info"><strong>Atentamente</strong></p>
            <div class="firma-sello-container">
                 <img src="{$firmaSelloUri}" alt="Firma y Sello" class="firma-sello">
            </div>
            <p class="info final-note">Este carnet consta de dos portadas, en una de ellas se encuentra la fotografía del alumno con el sello de la institución.</p>
        </div>
    </div>
</body>
</html>
HTML;

            // 3. Configurar opciones de Dompdf
            $options = new Options();
            $options->set('isRemoteEnabled', false); // Still use Data URIs
            $options->set('defaultFont', 'Helvetica'); // Keep standard font
            $options->set('isHtml5ParserEnabled', true);
            $options->set('dpi', 96); // Crucial for consistent sizing from px/cm

            // 4. Crear instancia de Dompdf
            $dompdf = new Dompdf($options);

            // 5. Cargar el HTML
            $dompdf->loadHtml($html_pdf);

            // 6. Establecer tamaño del papel (using the calculated points)
            // Custom size: width, height in points (1pt = 1/72 inch)
            $dompdf->setPaper([0, 0, 241, 156]); // 8.5cm x 5.5cm -> ~241pt x ~156pt

            // 7. Renderizar el HTML a PDF
            $dompdf->render();

            // 8. Output the generated PDF to the browser
            // 'Attachment' => false displays the PDF in the browser
            $filename = "carnet_" . $carnet_id_db . "_" . $ciclo_escolar_db . ".pdf"; // Use cleaned DB values for filename
            $dompdf->stream($filename, array("Attachment" => false));

            // Exit after streaming the PDF
            exit;


        } catch (\Throwable $e) { // Catch all errors/exceptions
            error_log("Dompdf Critical Error: " . $e->getMessage() . " on line " . $e->getLine() . " in " . $e->getFile()); // Log detailed error
            // Display user-friendly error
            if (!headers_sent()) {
                 header('Content-Type: text/html; charset=UTF-8');
                 echo "<!DOCTYPE html><html><head><title>Error PDF</title></head><body>";
                 echo "<h1>Error Crítico al Generar el Carnet PDF</h1>";
                 echo "<p style='color:red;'>Ocurrió un error inesperado al generar el PDF. Por favor, contacte al administrador.</p>";
                 // Optional: Display debug info ONLY IN DEVELOPMENT
                 // echo "<p>Detalles del error: " . htmlspecialchars($e->getMessage()) . "</p>";
                 // echo "<p>Archivo: " . htmlspecialchars($e->getFile()) . " (Línea: " . $e->getLine() . ")</p>";
                 echo "</body></html>";
            } else {
                 echo "<p style='color:red;'>Ocurrió un error después de que el contenido comenzó a enviarse. El PDF no se pudo generar completamente.</p>";
            }
            exit;
        }
        // --- FIN: Lógica de Generación de PDF con Dompdf ---

    } else {
        // Ensure headers are not sent before displaying error
        if (!headers_sent()) {
             header('Content-Type: text/html; charset=UTF-8');
             echo "<p style='color:orange;'>No se encontraron datos del carnet para el ID '<strong>" . htmlspecialchars($carnet_id_db) . "</strong>' y ciclo '<strong>" . htmlspecialchars($ciclo_escolar_db) . "</strong>'. Verifique los datos ingresados.</p>";
        } else {
             echo "<p style='color:orange;'>No se encontraron datos del carnet. Intente de nuevo.</p>";
        }
    }

    // Cerrar sentencia y conexión if not exited earlier
    if (isset($sentencia) && $sentencia instanceof mysqli_stmt) {
        $sentencia->close();
    }
    // The DB connection should be closed if it's valid and not already closed
    if (isset($conn) && $conn instanceof mysqli && !$conn->connect_error) {
         $conn->close();
    }


} else {
    // Ensure headers are not sent before displaying error
    if (!headers_sent()) {
         header('Content-Type: text/html; charset=UTF-8');
         echo "<p style='color:red;'>Error: No se proporcionaron los datos necesarios (carnet y ciclo_escolar) en la solicitud GET.</p>";
    } else {
         echo "<p style='color:red;'>Error en la solicitud: faltan parámetros.</p>";
    }

    // Close DB connection if it was successfully established
    if (isset($conn) && $conn instanceof mysqli && !$conn->connect_error) {
        $conn->close();
    }
}
?>
<?php require_once __DIR__ . '/auth.php'; colegio_requerir_sesion(); ?>
<?php
require_once "../library/dompdf/autoload.inc.php";

use Dompdf\Dompdf;
use Dompdf\Options;

// Configuración de DomPDF
$options = new Options();
$options->set('defaultFont', 'Arial');

$dompdf = new Dompdf($options);

// Recibir los datos enviados
if (isset($_POST['html_table'])) {
    $html_content = $_POST['html_table'];

    // Estilo para mejorar la apariencia en PDF
$html = "<style>
            table { 
                width: 100%; 
                border-collapse: collapse; 
                text-align: center; 
                font-size: 14px; /* Aumenta el tamaño de la fuente */
            }
            th, td { 
                border: 1px solid black; 
                padding: 20px; /* Aumenta el tamaño de las celdas */
                width: 10px; /* Define un ancho específico para las celdas */
            }
            th { 
                background-color: #f2f2f2; 
            }
            /* Ajustar el ancho de columnas específicas, si es necesario */
            th:nth-child(1), td:nth-child(1) { width: 5px; }  /* Ajusta el ancho de la primera columna */
            th:nth-child(2), td:nth-child(2) { width: 50px; } /* Ajusta el ancho de la segunda columna */
            th:nth-child(3), td:nth-child(3) { width: 210px; } /* Ajusta el ancho de la tercera columna */
            th:nth-child(4), td:nth-child(4)   { width: 16px; } /* Ajusta el ancho de la tercera columna */
            th:nth-child(5), td:nth-child(5)   { width: 16px; } /* Ajusta el ancho de la tercera columna */
            th:nth-child(6), td:nth-child(6)   { width: 16px; } /* Ajusta el ancho de la tercera columna */
            th:nth-child(7), td:nth-child(7)   { width: 16px; } /* Ajusta el ancho de la tercera columna */
            th:nth-child(8), td:nth-child(8)   { width: 16px; } /* Ajusta el ancho de la tercera columna */
            th:nth-child(9), td:nth-child(9)   { width: 16px; } /* Ajusta el ancho de la tercera columna */
            th:nth-child(10), td:nth-child(10) { width: 16px; } /* Ajusta el ancho de la tercera columna */
            th:nth-child(11), td:nth-child(11) { width: 16px; } /* Ajusta el ancho de la tercera columna */
            th:nth-child(12), td:nth-child(12) { width: 16px; } /* Ajusta el ancho de la tercera columna */
            th:nth-child(13), td:nth-child(13) { width: 16px; } /* Ajusta el ancho de la tercera columna */
            th:nth-child(14), td:nth-child(14) { width: 16px; } /* Ajusta el ancho de la tercera columna */
            th:nth-child(15), td:nth-child(15) { width: 16px; } /* Ajusta el ancho de la tercera columna */
            th:nth-child(16), td:nth-child(16) { width: 16px; } /* Ajusta el ancho de la tercera columna */
            th:nth-child(17), td:nth-child(17) { width: 16px; } /* Ajusta el ancho de la tercera columna */
            th:nth-child(18), td:nth-child(18) { width: 16px; } /* Ajusta el ancho de la tercera columna */
            th:nth-child(19), td:nth-child(19) { width: 16px; } /* Ajusta el ancho de la tercera columna */
    3
        </style>";


    $html .= $html_content;

    // Cargar HTML en DomPDF
    $dompdf->loadHtml($html);

    // Configurar el tamaño del papel y la orientación
    $dompdf->setPaper('A4', 'landscape');

    // Renderizar el PDF
    $dompdf->render();

    // Enviar el PDF al navegador
    $dompdf->stream("listado_estudiantes.pdf", ["Attachment" => false]);
} else {
    echo "No se recibieron datos para imprimir.";
}
?>

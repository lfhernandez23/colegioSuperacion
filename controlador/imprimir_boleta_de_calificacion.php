<?php require_once __DIR__ . '/auth.php'; colegio_requerir_sesion(); ?>
<?php

ob_start();
require_once "../controlador/conexion_bd.php";
require_once "../library/dompdf/autoload.inc.php";

use Dompdf\Dompdf;

// Obtener los datos del estudiante desde GET
$id_estudiante = $_GET["id_estudiante"] ?? "";
$nombre = $_GET["nombre"] ?? "";
$apellido = $_GET["apellido"] ?? "";
$carnet = $_GET["carnet"] ?? "";
$grado = $_GET["grado"] ?? "";
$nivel = $_GET["nivel"] ?? "";
$jornada = $_GET["jornada"] ?? "";
$ciclo_escolar = $_GET["ciclo_escolar"] ?? "";

// Obtener la fecha actual para la impresión
date_default_timezone_set("America/Guatemala");
$fecha_impresion = date("d-m-Y");

// Ruta de la imagen y conversión a base64
$ruta_imagen = "http://localhost/colegio_superacion/img/log_colegio.png";
$imagen_base64 = "data:image/png;base64," . base64_encode(file_get_contents($ruta_imagen));

// Ruta de la imagen de firmas y conversión a base64
$ruta_imagen_firmas = "http://localhost/colegio_superacion/img/espacio_firmas.png";
$imagen_base64_firmas = "data:image/png;base64," . base64_encode(file_get_contents($ruta_imagen_firmas));

// Definir nombres completos
$jornada_nombres = [
    "diario-matutino" => "Plan Diario - Matutino",
    "diario-vespertino" => "Plan Diario - Vespertino",
    "fin-de-semana" => "Plan Fin de Semana - Sabatino"
];
$imprime_jornada = $jornada_nombres[$jornada] ?? "Desconocido";

$nivel_nombres = [
    "preprimaria" => "Pre-Primaria",
    "primaria" => "Primaria",
    "basico" => "Básico",
    "basicopormadurez" => "Básico por Madurez",
    "diversificado1" => "Perito Contador con Orientación en Computación",
    "diversificado2" => "Perito en Administración de Empresa",
    "diversificado3" => "Bachiller en Computación con Orientación Comercial",
    "diversificado4" => "Bachiller en Ciencias y Letras por Madurez"
];
$imprime_nivel = $nivel_nombres[$nivel] ?? "Desconocido";

$grado_nombres = [
    "1grado" => "1er Grado",
    "2grado" => "2do Grado",
    "3grado" => "3er Grado",
    "4grado" => "4to Grado",
    "5grado" => "5to Grado",
    "6grado" => "6to Grado"
];
$imprime_grado = $grado_nombres[$grado] ?? "Desconocido";

if (empty($id_estudiante) || empty($ciclo_escolar)) {
    die("Error: No se han recibido los datos necesarios.");
}

$query = "SELECT c.curso, cal.bimestre_1, cal.bimestre_2, cal.bimestre_3, cal.bimestre_4
            FROM asignacion_cursos ac
            JOIN cursos c ON ac.id_curso = c.id
            LEFT JOIN calificaciones cal 
                ON ac.id_estudiante = cal.id_estudiante 
                AND ac.id_curso = cal.id_curso 
                AND ac.ciclo_escolar = cal.ciclo_escolar
            WHERE ac.id_estudiante = ? AND ac.ciclo_escolar = ?";

$stmt = $conn->prepare($query);
$stmt->bind_param("is", $id_estudiante, $ciclo_escolar);
$stmt->execute();
$result = $stmt->get_result();

$calificaciones = "";
while ($row = $result->fetch_assoc()) {
    // Asignar color según la nota
    $bimestres = [
        number_format($row['bimestre_1'] ?? 0.00, 2, '.', ''),
        number_format($row['bimestre_2'] ?? 0.00, 2, '.', ''),
        number_format($row['bimestre_3'] ?? 0.00, 2, '.', ''),
        number_format($row['bimestre_4'] ?? 0.00, 2, '.', '')
    ];

    // Aplicar color a cada bimestre
    $bimestre_1_color = ($bimestres[0] >= 60) ? 'green' : 'red';
    $bimestre_2_color = ($bimestres[1] >= 60) ? 'green' : 'red';
    $bimestre_3_color = ($bimestres[2] >= 60) ? 'green' : 'red';
    $bimestre_4_color = ($bimestres[3] >= 60) ? 'green' : 'red';

    // Calcular promedio y estado final
    $suma = array_sum($bimestres);
    $promedio = number_format($suma / 4, 2);
    $estado = $promedio >= 60 ? "Promueve" : "No Promueve";
    $estado_color = ($promedio >= 60) ? 'green' : 'red';

    $calificaciones .= "<tr>
                            <td>{$row['curso']}</td>
                            <td style='color: {$bimestre_1_color};'>{$bimestres[0]}</td>
                            <td style='color: {$bimestre_2_color};'>{$bimestres[1]}</td>
                            <td style='color: {$bimestre_3_color};'>{$bimestres[2]}</td>
                            <td style='color: {$bimestre_4_color};'>{$bimestres[3]}</td>
                            <td style='color: {$estado_color}; font-weight: bold;'>{$promedio}</td>
                            <td style='color: {$estado_color}; font-weight: bold;'>{$estado}</td>
                        </tr>";
}

$html = "
<!DOCTYPE html>
<html lang='es'>
<head>
    <meta charset='UTF-8'>
    <title>Boleta de Calificaciones</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 8px; margin-top: -50px; }
        table { width: 100%; border-collapse: collapse; font-size: 8px; margin-top: 5px; }
        th, td { border: 1px solid black; padding: 1px; text-align: center; word-wrap: break-word; }
        th { background-color: #f2f2f2; }
        th:nth-child(1), td:nth-child(1) {
            width: 150px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        img.logo {
            width: 50px;
            position: absolute;
            top: -45px;
            left: 10px;
        }
.firma-img {
    width: 60%;
    display: block;
    margin-left: 100px;
    margin-top: 10px;
}


    </style>
</head>
<body>
    <img class='logo' src='{$imagen_base64}' alt='Logo Colegio'>
    <h2 style='text-align: center;'>Colegio Privado Mixto Superación</h2>
    <h4 style='text-align: center;'>Boleta de Calificaciones - Ciclo Escolar {$ciclo_escolar}</h4>
    <br>
    <p><strong>Fecha de impresión:</strong> {$fecha_impresion}</p>
    <p><strong>Estudiante:</strong> {$nombre} {$apellido} <strong>Carnet:</strong> {$carnet}</p>
    <p><strong>Jornada:</strong> {$imprime_jornada} - <strong>Nivel:</strong> {$imprime_nivel} - <strong>Grado:</strong> {$imprime_grado}</p>
    
    <table>
        <tr>
            <th>Curso</th>
            <th>Bimestre 1</th>
            <th>Bimestre 2</th>
            <th>Bimestre 3</th>
            <th>Bimestre 4</th>
            <th>Promedio</th>
            <th>Estado</th>
        </tr>
        {$calificaciones}
</table>
<img class='firma-img' src='{$imagen_base64_firmas}' alt='Firma y sello'>


</body>
</html>
";


ob_end_clean();
$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper([0, 0, 396, 612], "portrait");
$dompdf->render();
$nombre_archivo = preg_replace('/[^A-Za-z0-9_\-]/', '', "{$nombre}_{$apellido}_{$ciclo_escolar}");
$dompdf->stream("{$nombre_archivo}_boleta_calificaciones.pdf", ["Attachment" => true]);

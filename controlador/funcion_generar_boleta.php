<?php
// Archivo: funciones_generar_boleta.php

/**
 * Genera el código HTML para la boleta de calificaciones de un estudiante.
 *
 * @param mysqli $conn La conexión a la base de datos.
 * @param array $estudiante Array asociativo con los datos del estudiante (id, nombre, apellido, carnet, grado, nivel, jornada, ciclo_escolar).
 * @return string El código HTML de la boleta o una cadena vacía en caso de error.
 */
function generarHtmlBoletaEstudiante(mysqli $conn, array $estudiante): string
{
    // Extraer datos del estudiante
    $id_estudiante = $estudiante["id"] ?? "";
    $nombre = $estudiante["nombre"] ?? "";
    $apellido = $estudiante["apellido"] ?? "";
    $carnet = $estudiante["carnet"] ?? "";
    $grado = $estudiante["grado"] ?? "";
    $nivel = $estudiante["nivel"] ?? "";
    $jornada = $estudiante["jornada"] ?? "";
    $ciclo_escolar = $estudiante["ciclo_escolar"] ?? "";

    // Validar datos esenciales
    if (empty($id_estudiante) || empty($ciclo_escolar)) {
        error_log("Datos insuficientes para generar boleta. Estudiante ID: " . ($id_estudiante ?: 'N/A') . ", Ciclo: " . ($ciclo_escolar ?: 'N/A'));
        return ""; // Retorna vacío si faltan datos clave
    }

    // --- Mapeos de Nombres Descriptivos ---
    $jornada_nombres = [
        "diario-matutino" => "Plan Diario - Matutino",
        "diario-vespertino" => "Plan Diario - Vespertino",
        "fin-de-semana" => "Plan Fin de Semana - Sabatino"
    ];
    $imprime_jornada = $jornada_nombres[$jornada] ?? htmlspecialchars($jornada); // Usar htmlspecialchars por si el valor original no está en el mapa

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
    $imprime_nivel = $nivel_nombres[$nivel] ?? htmlspecialchars($nivel);

    $grado_nombres = [
        "1grado" => "1er Grado",
        "2grado" => "2do Grado",
        "3grado" => "3er Grado",
        "4grado" => "4to Grado",
        "5grado" => "5to Grado",
        "6grado" => "6to Grado"
    ];
    $imprime_grado = $grado_nombres[$grado] ?? htmlspecialchars($grado);

    // --- Obtener Calificaciones (misma consulta que en tu script original) ---
    $query = "SELECT c.curso, cal.bimestre_1, cal.bimestre_2, cal.bimestre_3, cal.bimestre_4
                FROM asignacion_cursos ac
                JOIN cursos c ON ac.id_curso = c.id
                LEFT JOIN calificaciones cal
                    ON ac.id_estudiante = cal.id_estudiante
                    AND ac.id_curso = cal.id_curso
                    AND ac.ciclo_escolar = cal.ciclo_escolar
                WHERE ac.id_estudiante = ? AND ac.ciclo_escolar = ?";

    $stmt_cal = $conn->prepare($query);
    if (!$stmt_cal) {
        error_log("Error preparando consulta de calificaciones: " . $conn->error);
        return ""; // Retorna vacío en caso de error
    }
    $stmt_cal->bind_param("is", $id_estudiante, $ciclo_escolar);
    $stmt_cal->execute();
    $result_cal = $stmt_cal->get_result();

    $calificaciones = ""; // Variable para las filas de la tabla
    while ($row = $result_cal->fetch_assoc()) {
        // Lógica para formatear notas, calcular promedio, estado y colores (igual que en tu script original)
        $bimestres = [
            number_format($row['bimestre_1'] ?? 0.00, 2, '.', ''),
            number_format($row['bimestre_2'] ?? 0.00, 2, '.', ''),
            number_format($row['bimestre_3'] ?? 0.00, 2, '.', ''),
            number_format($row['bimestre_4'] ?? 0.00, 2, '.', '')
        ];
        $bimestre_1_color = ($bimestres[0] >= 60) ? 'green' : 'red';
        $bimestre_2_color = ($bimestres[1] >= 60) ? 'green' : 'red';
        $bimestre_3_color = ($bimestres[2] >= 60) ? 'green' : 'red';
        $bimestre_4_color = ($bimestres[3] >= 60) ? 'green' : 'red';

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
    $stmt_cal->close();

    if (empty($calificaciones)) {
        // Puedes decidir qué poner aquí si no hay calificaciones,
        // el script original simplemente dejaría la tabla vacía.
        $calificaciones = "<tr><td colspan='7' style='text-align: center;'>No hay calificaciones registradas.</td></tr>";
    }

    // --- Preparar Imágenes (sin cambios) ---
    $imagen_base64 = '';
    $ruta_imagen = "http://localhost/colegio_superacion/img/log_colegio.png";
    if ($img_content = @file_get_contents($ruta_imagen)) {
        $imagen_base64 = "data:image/png;base64," . base64_encode($img_content);
    } else {
        error_log("No se pudo cargar la imagen del logo: " . $ruta_imagen);
    }

    $imagen_base64_firmas = '';
    $ruta_imagen_firmas = "http://localhost/colegio_superacion/img/espacio_firmas.png";
    if ($firmas_content = @file_get_contents($ruta_imagen_firmas)) {
        $imagen_base64_firmas = "data:image/png;base64," . base64_encode($firmas_content);
    } else {
        error_log("No se pudo cargar la imagen de firmas: " . $ruta_imagen_firmas);
    }

    // --- Fecha de Impresión (sin cambios) ---
    $fecha_impresion = date("d-m-Y");

    // --- Generar HTML de la Boleta Completa ---
    // Usamos la misma estructura y estilos de tu script original
    // NOTA: Los estilos CSS están incluidos directamente. Si son complejos, podrían dar problemas en Dompdf.
    $html_boleta = "
    <!DOCTYPE html>
    <html lang='es'>
    <head>
        <meta charset='UTF-8'>
        <title>Boleta de Calificaciones</title> <style>
            /* Estilos CSS copiados exactamente del script original */
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
                top: -45px; /* Valor original */
                left: 10px; /* Valor original */
            }
           .firma-img {
               width: 60%;
               display: block;
               margin-left: 100px; /* Valor original */
               margin-top: 10px; /* Valor original */
           }
        </style>
    </head>
    <body>
        " . ($imagen_base64 ? "<img class='logo' src='{$imagen_base64}' alt='Logo Colegio'>" : "") . "
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
            {$calificaciones} </table>
        " . ($imagen_base64_firmas ? "<img class='firma-img' src='{$imagen_base64_firmas}' alt='Firma y sello'>" : "") . "
    </body>
    </html>
    ";

    return $html_boleta;
}

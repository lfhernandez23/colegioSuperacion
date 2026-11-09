<?php
ob_start();

// Formatear la fecha en el formato día-mes-año
$fecha_pago_formateada_nacimiento = date("d-m-Y", strtotime($_GET['fecha_nacimiento']));
$fecha_pago_formateada = date("d-m-Y", strtotime($_GET['fecha_inscripcion']));

// Verificar el sexo del estudiante
$imprime_sexo = ($_GET['sexo_estudiante'] === "femenino") ? "Femenino" : "Masculino";

// Verificar la jornada
switch ($_GET["jornada"]) {
    case 'diario-matutino':
        $imprime_jornada = "Plan Diario - Matutino";
        break;
    case 'diario-vespertino':
        $imprime_jornada = "Plan Diario - Vespertino";
        break;
    case 'fin-de-semana':
        $imprime_jornada = "Plan Fin de Semana - Sabatino";
        break;
    default:
        $imprime_jornada = "No especificado";
        break;
}

//Verificar Tipo Inscripcion


switch ($_GET['tipo_inscripcion']) {
    case 'ordinaria':
        $imprime_tipo_inscripcion = "Ordinaria";
        break;
    case 'extraordinaria':
        $imprime_tipo_inscripcion = "Extraordinaria";
        break;
}


// Verificar el grado
switch ($_GET["nivel"]) {
    case 'preprimaria':
        $imprime_nivel = "Pre-Primaria";
        break;
    case 'primaria':
        $imprime_nivel = "Primaria";
        break;
    case 'basico':
        $imprime_nivel = "Básico";
        break;
    case 'basicopormadurez':
        $imprime_nivel = "Básico por Madurez";
        break;
    case 'diversificado1':
        $imprime_nivel = "Perito Contador con Orientacion en Computación";
        break;
    case 'diversificado2':
        $imprime_nivel = "Perito en Administración de Empresa";
        break;
    case 'diversificado3':
        $$imprime_nivel = "Bachiller en Computación con Orientacion Comercial";
        break;
    case 'diversificado4':
        $imprime_nivel = "Bachiller en Ciencias y Letra por Madurez";
        break;
    default:
        $imprime_nivel = "No especificado";
        break;
}

// Verificar el nivel
switch ($_GET["grado"]) {
    case '1grado':
        $imprime_grado = "1er Grado";
        break;
    case '2grado':
        $imprime_grado = "2do Grado";
        break;
    case '3grado':
        $imprime_grado = "3er Grado";
        break;
    case '4grado':
        $imprime_grado = "4to Grado";
        break;
    case '5grado':
        $imprime_grado = "5to Grado";
        break;
    case '6grado':
        $imprime_grado = "6to Grado";
        break;
    default:
        $imprime_grado = "No especificado";
        break;
}

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Imprimir Inscripción</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            size: letter;
            width: 8.5in;
            height: 11in;
        }

        .container {
            padding: 20px;
            display: flex;
            justify-content: space-between;
            /* Espacio entre las columnas */
        }        

        h2 {
            text-align: center;
            margin-bottom: -10px;
        }

        p {
            margin-bottom: 5px;
            background-color: #0000;
            /* Color de fondo para los párrafos */
            padding: 8px;
            /* Espacio alrededor del contenido del párrafo */
            border-radius: 5px;
            /* Bordes redondeados */
            font-size: 14px;
            /* Reducir el tamaño de la fuente */
        }

        .field-label {
            font-weight: bold;
        }

        .field-value {
            margin-left: 10px;
        }
    </style>
</head>

<body>
    <div class="container">
        <h2>Datos de Inscripción</h2>        
            <p><span class="field-label">Carnet:</span> <span class="field-value"><?php echo $_GET['carnet']; ?></span></p>
            <p><span class="field-label">Código:</span> <span class="field-value"><?php echo $_GET['codigo']; ?></span></p>
            <p><span class="field-label">Nombres:</span> <span class="field-value"><?php echo $_GET['nombres']; ?></span></p>
            <p><span class="field-label">Apellidos:</span> <span class="field-value"><?php echo $_GET['apellidos']; ?></span></p>
            <p><span class="field-label">Sexo:</span> <span class="field-value"><?php echo $imprime_sexo; ?></span></p>
            <p><span class="field-label">Fecha de Nacimiento:</span> <span class="field-value"><?php echo $fecha_pago_formateada_nacimiento; ?></span></p>        
            <p><span class="field-label">Jornada:</span> <span class="field-value"><?php echo $imprime_jornada; ?></span></p>            
            <p><span class="field-label">Nivel:</span> <span class="field-value"><?php echo $imprime_nivel; ?></span></p>
            <p><span class="field-label">Grado:</span> <span class="field-value"><?php echo $imprime_grado; ?></span></p>
            <p><span class="field-label">Tipo de Sangre:</span> <span class="field-value"><?php echo $_GET['tipo_sangre']; ?></span></p>
            <p><span class="field-label">Dirección de Vivienda:</span> <span class="field-value"><?php echo $_GET['direccion_vivienda']; ?></span></p>
            <p><span class="field-label">Nombre del Padre:</span> <span class="field-value"><?php echo $_GET['nombre_padre']; ?></span></p>        
            <p><span class="field-label">Nombre de la Madre:</span> <span class="field-value"><?php echo $_GET['nombre_madre']; ?></span></p>
            <p><span class="field-label">Fecha de Inscripción:</span> <span class="field-value"><?php echo $fecha_pago_formateada; ?></span></p>
            <p><span class="field-label">Información Adicional:</span> <span class="field-value"><?php echo $_GET['info_adicional']; ?></span></p>
            <p><span class="field-label">Teléfono del Alumno:</span> <span class="field-value"><?php echo $_GET['tel_alumno']; ?></span></p>
            <p><span class="field-label">Teléfono del Encargado:</span> <span class="field-value"><?php echo $_GET['tel_encargado']; ?></span></p>
            <p><span class="field-label">Correo Electrónico del Encargado:</span> <span class="field-value"><?php echo $_GET['email_encargado']; ?></span></p>
            <p><span class="field-label">Nombre del Tutor:</span> <span class="field-value"><?php echo $_GET['nombre_tutor']; ?></span></p>
            <p><span class="field-label">Tipo de Inscripción:</span> <span class="field-value"><?php echo $imprime_tipo_inscripcion; ?></span></p>        
    </div>
</body>

</html>


<?php
$html = ob_get_clean();

require_once "../library/dompdf/autoload.inc.php";

use Dompdf\Dompdf;

$dompdf = new Dompdf();

$dompdf->loadHtml($html);

// Definimos el tamaño del papel
$dompdf->setPaper('A4', 'portrait');

// Renderizamos el PDF
$dompdf->render();

// Descargamos el PDF
$dompdf->stream('boleta_inscripcion_estudiante.pdf', array("Attachment" => false));
?>
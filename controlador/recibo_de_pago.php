<?php require_once __DIR__ . '/auth.php'; colegio_requerir_sesion(); ?>
<?php
ob_start();

include_once "../controlador/conexion_bd.php";

// Definir variables para el mes y tipo de pago
$imprime_mes_pago = "";
$imprime_tipo_pago = "";
$imprime_jornada = "";
$imprime_grado = "";
$imprime_nivel = "";

$mes_pago = $_GET["mes_pago"] ?? "";
$tipo_pago = $_GET["tipo_pago"] ?? "";
$monto = $_GET["monto"] ?? "";
$fecha_pago = $_GET["fecha_pago"] ?? "";
$id_pago = $_GET["id_pago"] ?? "";
$serie_recibo = $_GET["serie_recibo"] ?? "";
$num_autorizacion = $_GET["num_autorizacion"] ?? "";
$observacion_adicional = $_GET["observacion_adicional"] ?? "";
$nombre = $_GET["nombre"] ?? "";
$apellido = $_GET["apellido"] ?? "";
$carnet = $_GET["carnet"] ?? "";

if(empty($observacion_adicional)){
    $observacion_adicional = "Sin observaciones adicionales";
}

// Convertir mes de pago a nombre completo
switch ($mes_pago) {
    case "enero":
        $imprime_mes_pago = "Enero";
        break;
    case "febrero":
        $imprime_mes_pago = "Febrero";
        break;
    case "marzo":
        $imprime_mes_pago = "Marzo";
        break;
    case "abril":
        $imprime_mes_pago = "Abril";
        break;
    case "mayo":
        $imprime_mes_pago = "Mayo";
        break;
    case "junio":
        $imprime_mes_pago = "Junio";
        break;
    case "julio":
        $imprime_mes_pago = "Julio";
        break;
    case "agosto":
        $imprime_mes_pago = "Agosto";
        break;
    case "septiembre":
        $imprime_mes_pago = "Septiembre";
        break;
    case "octubre":
        $imprime_mes_pago = "Octubre";
        break;
    case "noviembre":
        $imprime_mes_pago = "Noviembre";
        break;
    case "diciembre":
        $imprime_mes_pago = "Diciembre";
        break;
    default:  
        $imprime_mes_pago = "XXXXXXXXXX";
        break;
}

// Convertir tipo de pago a nombre completo
switch ($tipo_pago) {
    case "mensualidad":
        $imprime_tipo_pago = "Mensualidad";
        break;
    case "inscripcion":
        $imprime_tipo_pago = "Inscripción";
        break;
    case "otros":
        $imprime_tipo_pago = "Otros";
        break;
}

// Convertir jornada a nombre completo
$jornada = $_GET["jornada"] ?? "";
switch ($jornada) {
    case "diario-matutino":
        $imprime_jornada = "Plan Diario - Matutino";
        break;
    case "diario-vespertino":
        $imprime_jornada = "Plan Diario - Vespertino";
        break;
    case "fin-de-semana":
        $imprime_jornada = "Plan Fin de Semana - Sabatino";
        break;
}

// Convertir nivel a nombre completo
$nivel = $_GET["nivel"] ?? "";
switch ($nivel) {
    case "preprimaria":
        $imprime_nivel = "Pre-Primaria";
        break;
    case "primaria":
        $imprime_nivel = "Primaria";
        break;
    case "basico":
        $imprime_nivel = "Básico";
        break;
    case "basicopormadurez":
        $imprime_nivel = "Básico por Madurez";
        break;
    case "diversificado1":
        $imprime_nivel = "Perito Contador con Orientacion en Computación";
        break;
    case "diversificado2":
        $imprime_nivel = "Perito en Administración de Empresa";
        break;
    case "diversificado3":
        $imprime_nivel = "Bachiller en Computación con Orientacion Comercial";
        break;
    case "diversificado4":
        $imprime_nivel = "Bachiller en Ciencias y Letra por Madurez";
        break;
}

// Convertir grado a nombre completo
$grado = $_GET["grado"] ?? "";
switch ($grado) {
    case "1grado":
        $imprime_grado = "1er Grado";
        break;
    case "2grado":
        $imprime_grado = "2do Grado";
        break;
    case "3grado":
        $imprime_grado = "3er Grado";
        break;
    case "4grado":
        $imprime_grado = "4to Grado";
        break;
    case "5grado":
        $imprime_grado = "5to Grado";
        break;
    case "6grado":
        $imprime_grado = "6to Grado";
        break;
}

// Formatear la fecha en el formato día-mes-año
$fecha_pago_formateada = date("d-m-Y", strtotime($fecha_pago));

// Ruta de la imagen y conversión a base64
$ruta_imagen = "http://localhost/colegio_superacion/img/log_colegio.png";
$imagen_base64 = "data:image/png;base64," . base64_encode(file_get_contents($ruta_imagen));

$ruta_imagen_firma = "http://localhost/colegio_superacion/img/Firma_Sello.png";
$imagen_base642 = "data:image/png;base64," . base64_encode(file_get_contents($ruta_imagen_firma));

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recibo de Pagos</title>
    <style>
    body {
        font-family: Arial, sans-serif;
        margin: 0;
        padding: 0;
    }

    .receipt-container {
        width: 100%;
        margin: 20px auto;
        border: 1px solid #000;
        padding: 10px;
        display: flex;
        align-items: center; /* Alinea verticalmente */
    }

    .header img {
        max-width: 100px;
        margin-top: -8px;
        margin-right: 20px; /* Espacio entre el logo y el contenido */
    }

    .content {
        margin-top: -150px;
        text-align: center;
    }

    .receipt-details {
        margin-top: 20px;
    }

    .payment-description {
        margin-top: 20px;
    }

    .total-payment {
        margin-top: 5px;
    }

    .institution-address {
        margin-top: -150px;
        text-align: center;
    }
    </style>
</head>
<body>
    <div class="receipt-container">
        <div class="header">
            <!-- Utilizar la URL completa en el atributo src -->
            <img src="<?php echo $imagen_base64; ?>" alt="Logo de la institución">            
        </div>
        <div class="institution-address">
                <p>COLEGIO PRIVADO MIXTO</p>
                <p style="margin-top: -20px; text-align: center;"><strong><u>"SUPERACIÓN"</u></strong></p>
                <p style=" margin-top: -30px; text-align: center; ">______________________________<p>                
                <p style=" margin-top: -90px; text-align: center;">San José La Máquina, Suchitepéquez.<p>                
                <p style=" margin-top: -100px; text-align: center;"><strong>TELÉFONO: 7927-2430 / 3574-6025.</strong><p>                
            </div>
        <br><br><br><br><br>        
        <div class="content">       
            <br>
            <h4 style="display: block; margin-top: -120px;  text-align: right;">Recibo de Pago Serie <?php echo htmlspecialchars($serie_recibo . " - " . $id_pago) ; ?> </h4>            
            <div class="receipt-details">
                <p style="display: block; margin-top: -20px; margin-right: 450px;"><strong>Fecha de Pago</strong> 
                <p style="display: block; margin-top: -18px; margin-right: 460px;"><strong> <?php echo htmlspecialchars($fecha_pago_formateada); ?></strong></p>                                
            </div>
            <div class="payment-description">
                <h4 style="display: block; margin-top: -30px; margin-right: 0px;">Descripción del Pago:</h4>
                <p style="display: block; margin-top: 0px; text-align: left;"> <strong>Recibí del Estudiante: </strong> <u><?php echo htmlspecialchars($nombre . " " . $apellido); ?></u> &nbsp;&nbsp;<strong>Carnet:</strong>&nbsp; <u><?php echo htmlspecialchars($carnet); ?></u></p>
                <p style="display: block; text-align: left;"><strong>Para el Grado: </strong><u><?php echo htmlspecialchars($imprime_jornada); ?>  <?php echo htmlspecialchars($imprime_nivel); ?>  <?php echo htmlspecialchars($imprime_grado); ?> para el Periodo Académico en Curso</u></p>
                <p style="display: block; margin-top: 0px; text-align: left;;"><strong>Mes que Cancela: </strong><u><?php echo htmlspecialchars($imprime_mes_pago); ?></u>    <strong>   Por Concepto de:</strong> <u><?php echo htmlspecialchars($imprime_tipo_pago); ?></u> </p>
                <p style="display: block; margin-top: 0px; text-align: left;;"><strong>Observacion: </strong><u><?php echo htmlspecialchars($observacion_adicional); ?></u></p>
            </div>
            <div class="total-payment">
                <p style="display: block; margin-top: 0px; text-align: left;""><strong>Total Cancelado: </strong>Q. <u><?php echo htmlspecialchars($monto); ?></u></p>                
            </div>
            <p style="display: block; margin-top: -50px; margin-bottom: -50px; margin-right: -450px;"><strong>Autorización N°: <?php echo htmlspecialchars($num_autorizacion); ?></strong></p>       
            <br>
            <img style="max-width: 240px; margin-bottom: -25px;" src="<?php echo $imagen_base642; ?>" alt="Firma y Sello">
            <p style="display: block; margin-bottom: -10px; margin-right: 0px;">P.C Mariana I. Bartolón de Flores</p>           
        </div>
    </div>
</body>
</html>

<?php
$html = ob_get_clean();

require_once "../library/dompdf/autoload.inc.php";

use Dompdf\Dompdf;

$dompdf = new Dompdf();

$dompdf->loadHtml($html);

$width = 21; // Ancho en centímetros
$height = 29.7; // Alto en centímetros
$orientation = 'portrait'; // 'portrait' o 'landscape'

// Establecer el tamaño del papel
$dompdf->setPaper($width, $height, $orientation);

// Renderizamos el PDF
$dompdf->render();

// Limpiar el nombre y apellido para que sean válidos en el nombre del archivo
$nombre_archivo = preg_replace('/[^A-Za-z0-9_\-]/', '', $nombre . "_" . $apellido);

// Descargamos el PDF
$dompdf->stream($nombre_archivo . '_recibo_pago.pdf', array("Attachment" => true));

?>

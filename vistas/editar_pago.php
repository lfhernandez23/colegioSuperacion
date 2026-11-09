<?php require_once __DIR__ . '/../controlador/auth.php'; colegio_requerir_sesion(); ?><?php
include_once "../controlador/conexion_bd.php";

// Definir variables para el mes y tipo de pago

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
$codigo = $_GET["codigo"] ?? "";
$jornada = $_GET["jornada"] ?? "";
$nivel = $_GET["nivel"] ?? "";
$grado = $_GET["grado"] ?? "";
$ciclo_escolar = $_GET["ciclo_escolar"] ?? "";


// Formatear la fecha en el formato día-mes-año
$fecha_pago_formateada = date("d-m-Y", strtotime($fecha_pago));
?>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/inscripcionstyle.css" />
    <link rel="stylesheet" href="../css/navstyle.css" />
    <title>Buscar y Editar Inscripcion</title>
  <link rel="stylesheet" href="../css/ui-standard.css">
</head>

<body>

<?php include './header.php'; ?>
    <br><br><br>
    <div class="container">
        <h2>Buscar y Editar Pago</h2>

        <form id="editarForm" action="../controlador/consulta_actualiza_pago.php" method="post">

            <label for="nombre">Nombre </label>
            <input type="text" name="nombre" id="nombre" value="<?php echo $nombre; ?>" readonly />

            <label for="apellido">Apellido </label>
            <input type="text" name="apellido" id="apellido" value="<?php echo $apellido; ?>" readonly />

            <label for="carnet">Carnet del Estudiante </label>
            <input type="text" id="carnet" name="carnet" value="<?php echo $carnet; ?>" readonly />

            <label for="codigo">Código del Estudiante </label>
            <input type="text" id="codigo" name="codigo" value="<?php echo $codigo; ?>" readonly />

            <label for="jornada">Jornada Asignada </label>
            <select id="jornada" name="jornada" readonly>
                <option value="diario-matutino" <?php echo ($jornada === 'diario-matutino') ? 'selected' : ''; ?>>Plan Diario - Matutino</option>
                <option value="diario-vespertino" <?php echo ($jornada === 'diario-vespertino') ? 'selected' : ''; ?>>Plan Diario - Vespertino</option>
                <option value="fin-de-semana" <?php echo ($jornada === 'fin-de-semana') ? 'selected' : ''; ?>>Plan Fin de Semana - Sabatino</option>
            </select>

            <label for="nivel">Nivel Asignado </label>
            <select id="nivel" name="nivel" readonly>
                <option value="preprimaria" <?php echo ($nivel === 'preprimaria') ? 'selected' : ''; ?>>Pre-Primaria</option>
                <option value="primaria" <?php echo ($nivel === 'primaria') ? 'selected' : ''; ?>>Primaria</option>
                <option value="basico" <?php echo ($nivel === 'basico') ? 'selected' : ''; ?>>Básico</option>
                <option value="basicopormadurez" <?php echo ($nivel === 'basicopormadurez') ? 'selected' : ''; ?>>Básico por Madurez</option>
                <option value="diversificado1" <?php echo ($nivel === 'diversificado1') ? 'selected' : ''; ?>>Perito Contador con Orientacion en Computación</option>
                <option value="diversificado2" <?php echo ($nivel === 'diversificado2') ? 'selected' : ''; ?>>Perito en Administración de Empresa</option>
                <option value="diversificado3" <?php echo ($nivel === 'diversificado3') ? 'selected' : ''; ?>>Bachiller en Computación con Orientacion Comercial</option>
                <option value="diversificado4" <?php echo ($nivel === 'diversificado4') ? 'selected' : ''; ?>>Bachiller en Ciencias y Letra por Madurez</option>
            </select>

            <label for="grado">Grado Asignado</label>
            <select id="grado" name="grado" readonly>
                <option value="1grado" <?php echo ($grado === '1grado') ? 'selected' : ''; ?>>1er Grado</option>
                <option value="2grado" <?php echo ($grado === '2grado') ? 'selected' : ''; ?>>2do Grado</option>
                <option value="3grado" <?php echo ($grado === '3grado') ? 'selected' : ''; ?>>3er Grado</option>
                <option value="4grado" <?php echo ($grado === '4grado') ? 'selected' : ''; ?>>4to Grado</option>
                <option value="5grado" <?php echo ($grado === '5grado') ? 'selected' : ''; ?>>5to Grado</option>
                <option value="6grado" <?php echo ($grado === '6grado') ? 'selected' : ''; ?>>6to Grado</option>
            </select>

            <label for="ciclo_escolar">Ciclo Escolar </label>
            <input type="text" id="ciclo_escolar" name="ciclo_escolar" value="<?php echo $ciclo_escolar; ?>" readonly />

            <label for="id_pago">Serie de Recibo A </label>
            <input type="text" id="id_pago" name="id_pago" value="<?php echo $id_pago; ?>" readonly />

            <label for="num_autorizacion">Número de Autorización </label>
            <input type="text" id="num_autorizacion" name="num_autorizacion" value="<?php echo $num_autorizacion; ?>" readonly />

            <label for="fecha_pago">Fecha de Pago </label>
            <input type="date" id="fecha_pago" name="fecha_pago" value="<?php echo $fecha_pago; ?>" readonly />            

            <label for="mes_pago">Mes de Pago</label>
            <select id="mes_pago" name="mes_pago">
                <option value="enero" <?php echo ($mes_pago === 'enero') ?  'selected' : ''; ?>>Enero</option>
                <option value="febrero" <?php echo ($mes_pago === 'febrero') ?  'selected' : ''; ?>>Febrero</option>
                <option value="marzo" <?php echo ($mes_pago === 'marzo') ?  'selected' : ''; ?>>Marzo</option>
                <option value="abril" <?php echo ($mes_pago === 'abril') ?  'selected' : ''; ?>>Abril</option>
                <option value="mayo" <?php echo ($mes_pago === 'mayo') ?  'selected' : ''; ?>>Mayo</option>
                <option value="junio" <?php echo ($mes_pago === 'junio') ?  'selected' : ''; ?>>Junio</option>
                <option value="julio" <?php echo ($mes_pago === 'julio') ?  'selected' : ''; ?>>Julio</option>
                <option value="agosto" <?php echo ($mes_pago === 'agosto') ?  'selected' : ''; ?>>Agosto</option>
                <option value="septiembre" <?php echo ($mes_pago === 'septiembre') ?  'selected' : ''; ?>>Septiembre</option>
                <option value="octubre" <?php echo ($mes_pago === 'octubre') ?  'selected' : ''; ?>>Octubre</option>
                <option value="noviembre" <?php echo ($mes_pago === 'noviembre') ?  'selected' : ''; ?>>Noviembre</option>
                <option value="diciembre" <?php echo ($mes_pago === 'diciembre') ?  'selected' : ''; ?>>Diciembre</option>
            </select>

            <label for="tipo_pago">Tipo de Pago </label>
            <select id="tipo_pago" name="tipo_pago">
                <option value="inscripcion" <?php echo ($tipo_pago === 'inscripcion') ? 'selected' : ''; ?>>Inscripcion</option>
                <option value="mensualidad" <?php echo ($tipo_pago === 'mensualidad') ? 'selected' : ''; ?>>Mensualidad</option>
                <option value="otros" <?php echo ($tipo_pago === 'otros') ? 'selected' : ''; ?>>Otros</option>
            </select>

            <label for="monto">Total Cancelado </label>
            <input type="number" id="monto" name="monto" step="0.01" value="<?php echo $monto; ?>" />

            <label for="observacion_adicional">Observacion Adicional </label>
            <input type="text" id="observacion_adicional" name="observacion_adicional" value="<?php echo $observacion_adicional; ?>" />

            <button type="submit" name="actualizar_pago" style="width: 600px;">Actualizar Pago</button>
        </form>
    </div>
</body>

</html>
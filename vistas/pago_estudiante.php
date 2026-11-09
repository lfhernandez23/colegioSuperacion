<?php require_once __DIR__ . '/../controlador/auth.php'; colegio_requerir_sesion(); ?>
<?php
function generarCodigoUnico($longitud = 8)
{
    // Caracteres permitidos para el código
    $caracteres = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $longitud_caracteres = strlen($caracteres);
    $codigo = '';

    // Generar el código aleatorio
    for ($i = 0; $i < $longitud; $i++) {
        $codigo .= $caracteres[rand(0, $longitud_caracteres - 1)];
    }

    return $codigo;
}

// Inicializar las variables
$serie_recibo = "A";
$num_autorizacion = generarCodigoUnico();
$codigo = "";
$carnet = "";
$nombre = "";
$apellido = "";
$jornada = "";
$grado = "";
$nivel = "";
$ciclo_escolar = "";


// Verificar si se han enviado datos por POST
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["buscar"])) {
    // Obtener el número de carnet enviado por el formulario
    $carnet = $_POST["carnet"];
    $ciclo_escolar = $_POST["ciclo_escolar"];

    include_once "../controlador/conexion_bd.php";

    // Preparar la consulta SQL
    $consulta = "SELECT * FROM registro_estudiantes WHERE carnet = ? AND ciclo_escolar = ?";
    $sentencia = $conn->prepare($consulta);

    if ($sentencia) {
        $sentencia->bind_param("ss", $carnet, $ciclo_escolar);

        $sentencia->execute();

        // Obtener resultados
        $resultados = $sentencia->get_result();

        // Cerrar la sentencia
        $sentencia->close();
    } else {
        die("Error en la preparación de la consulta: " . $conn->error);
    }

    // Cerrar la conexión
    $conn->close();

    // Mostrar resultados si están disponibles
    if (isset($resultados) && $resultados->num_rows > 0) {
        // Mostrar formulario de edición
        while ($resultados_array = $resultados->fetch_assoc()) {
            $carnet = isset($resultados_array['carnet']) ? $resultados_array['carnet'] : '';
            $codigo = isset($resultados_array['codigo']) ? $resultados_array['codigo'] : '';
            $nombre = isset($resultados_array['nombre']) ? $resultados_array['nombre'] : '';
            $apellido = isset($resultados_array['apellido']) ? $resultados_array['apellido'] : '';
            $jornada = isset($resultados_array['jornada']) ? $resultados_array['jornada'] : '';
            $nivel = isset($resultados_array['nivel']) ? $resultados_array['nivel'] : '';
            $grado = isset($resultados_array['grado']) ? $resultados_array['grado'] : '';
            $ciclo_escolar = isset($resultados_array['ciclo_escolar']) ? $resultados_array['ciclo_escolar'] : '';
        }
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["registrar_pago"])) {
    // Aquí debes colocar el código para procesar el registro del pago con los datos del formulario            
    $carnet = $_POST["carnet"];
    $codigo = $_POST["codigo"];
    $ciclo_escolar = $_POST["ciclo_escolar"];
    $tipo_pago = $_POST["tipo_pago"];
    $monto = $_POST["monto"];
    $mes_pago = $_POST["mes_pago"];
    $observacion_adicional = isset($_POST["observacion_adicional"]) ? $_POST["observacion_adicional"] : '';

    include_once "../controlador/conexion_bd.php";

    // Establecer la zona horaria a Guatemala
    date_default_timezone_set('America/Guatemala');

    // Obtener la fecha actual
    $fecha_pago = date("Y-m-d");

    // Generar un código único para num_autorizacion
    $num_autorizacion = generarCodigoUnico();

    // Generar serie_recibo aleatoria
    $serie_recibo = 'A';

    // Preparar la consulta SQL para la inserción
    $sql_insertar = "INSERT INTO control_pagos (serie_recibo, num_autorizacion, codigo, carnet, ciclo_escolar, tipo_pago, monto, fecha_pago, mes_pago, observacion_adicional) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    // Preparar la sentencia de inserción
    $stmt_insertar = $conn->prepare($sql_insertar);

    // Vincular parámetros para la inserción
    $stmt_insertar->bind_param("ssssssssss", $serie_recibo, $num_autorizacion, $codigo, $carnet, $ciclo_escolar, $tipo_pago, $monto, $fecha_pago, $mes_pago, $observacion_adicional);

    // Ejecutar la sentencia de inserción
    if ($stmt_insertar->execute()) {
        $mensaje = "Pago Exitoso. Número de Código: $codigo Número de Carnet: $carnet";
        echo "<script>
                alert('$mensaje');
              </script>";

        // Obtener los datos del estudiante y el historial de pagos
        include_once "../controlador/conexion_bd.php";

        // Preparar la consulta SQL para obtener el historial de pagos
        $consulta_pagos = "SELECT cp.serie_recibo, cp.num_autorizacion, cp.id_pago, cp.carnet, cp.codigo, cp.tipo_pago, cp.monto, cp.fecha_pago, cp.mes_pago, cp.observacion_adicional, re.nombre, re.apellido, re.jornada, re.grado, re.nivel, re.ciclo_escolar
                           FROM control_pagos cp
                           INNER JOIN registro_estudiantes re ON cp.carnet = re.carnet AND cp.ciclo_escolar = re.ciclo_escolar
                           WHERE cp.carnet = ? AND re.ciclo_escolar = ? AND cp.num_autorizacion = ?";

        $sentencia_pagos = $conn->prepare($consulta_pagos);
        $sentencia_pagos->bind_param("sss", $carnet, $ciclo_escolar, $num_autorizacion);
        $sentencia_pagos->execute();
        $resultados_pagos = $sentencia_pagos->get_result();

        if ($fila = $resultados_pagos->fetch_assoc()) {
            $url_params = http_build_query($fila);
            $url = "../controlador/recibo_de_pago.php?$url_params";
            echo "<script>
                    window.location.href='$url';
                    setTimeout(function(){
                        window.location.href='../vistas/pago_estudiante.php';
                    }, 3000); // Redirigir después de 2 segundos (2000 milisegundos)
                    </script>";
        } else {
            echo "<script>alert('Error al obtener el historial de pagos');</script>";
        }

        $sentencia_pagos->close();
    } else {
        $error = "Error al procesar el pago: " . $stmt_insertar->error;
        echo "<script>alert('$error');</script>";
    }

    // Cerrar la sentencia de inserción
    $stmt_insertar->close();

    // Cerrar la conexión
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/inscripcionstyle.css" />
    <link rel="stylesheet" href="../css/navstyle.css" />
    <title>Registro de Pagos</title>
  <link rel="stylesheet" href="../css/ui-standard.css">
</head>

<body>

<?php include './header.php'; ?>


    <br><br><br>
    <div class="container">
        <h2>Registrar Pago</h2>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">

            <label for="carnet">Número de Carnet</label>
            <input type="text" id="carnet" name="carnet" value="<?php echo $carnet; ?>" required />

            <label for="ciclo_escolar">Ciclo Escolar Inscrito</label>
            <input type="text" id="ciclo_escolar" name="ciclo_escolar" value="<?php echo $ciclo_escolar; ?>" required />

            <button type="submit" name="buscar">Buscar</button>

            <label for="codigo">Código Personal del Estudiante</label>
            <input type="text" id="codigo" name="codigo" value="<?php echo $codigo; ?>" readonly />

            <label for="nombres">Nombre del Estudiante</label>
            <input type="text" id="nombres" name="nombres" value="<?php echo $nombre; ?>" readonly />

            <label for="apellidos">Apellido del Estudiante </label>
            <input type="text" id="apellidos" name="apellidos" value="<?php echo $apellido; ?>" readonly />

            <label for="jornada">Jornada </label>
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

            <label for="mes_pago">Mes de Pago</label>
            <select id="mes_pago" name="mes_pago">
                <option value="enero" selected>Enero</option>
                <option value="febrero">Febrero</option>
                <option value="marzo">Marzo</option>
                <option value="abril">Abril</option>
                <option value="mayo">Mayo</option>
                <option value="junio">Junio</option>
                <option value="julio">Julio</option>
                <option value="agosto">Agosto</option>
                <option value="septiembre">Septiembre</option>
                <option value="octubre">Octubre</option>
                <option value="noviembre">Noviembre</option>
                <option value="diciembre">Diciembre</option>
                <option value=""></option>
            </select>

            <label for="tipo_pago">Tipo de Pago</label>
            <select id="tipo_pago" name="tipo_pago" required>
                <option value="mensualidad" selected>Mensualidad</option>
                <option value="inscripcion">Inscripción</option>
                <option value="otros">Otros</option>
            </select>

            <label for="monto">Monto (en quetzales)</label>
            <input type="number" id="monto" name="monto" step="0.01" />

            <label for="observacion_adicional">Observaciones Adicionales </label>
            <input type="text" id="observacion_adicional" name="observacion_adicional" />

            <button type="submit" name="registrar_pago">Registrar Pago</button>
        </form>
    </div>

    <script src="../js/codigo_aleatorio.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Obtener referencia al campo de tipo de pago
            var tipoPago = document.getElementById("tipo_pago");
            // Obtener referencia al campo de selección de mes
            var mesPago = document.getElementById("mes_pago");

            // Función para habilitar o deshabilitar el campo de selección de mes
            function actualizarCampoMes() {
                if (tipoPago.value === "inscripcion" || tipoPago.value === "otros") {
                    mesPago.value = ""; // Establecer el valor del campo mes_pago a una cadena vacía
                } else {
                    mesPago.disabled = false;
                }
            }

            // Llamar a la función al cargar la página para establecer el estado inicial del campo de selección de mes
            actualizarCampoMes();

            // Agregar un event listener al campo de tipo de pago para detectar cambios
            tipoPago.addEventListener("change", function() {
                // Actualizar el estado del campo de selección de mes cuando cambie el tipo de pago
                actualizarCampoMes();
            });
        });
    </script>
</body>

</html>
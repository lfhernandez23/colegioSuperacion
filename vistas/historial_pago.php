<?php require_once __DIR__ . '/../controlador/auth.php'; colegio_requerir_sesion(); ?>
<?php
// Inicializar las variables
$codigo = "";
$carnet = "";
$nombre = "";
$apellido = "";
$grado = "";
$jornada = "";
$ciclo_escolar = "";

$show_history_on_load = false;
$resultados_pagos = null;
$base_total_pendiente = 0; // Initialize base_total_pendiente

// Verificar redirección después de eliminación para auto-mostrar historial
if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET["carnet_rs"]) && isset($_GET["ciclo_rs"])) {
    $carnet_from_redirect = $_GET["carnet_rs"];
    $ciclo_from_redirect = $_GET["ciclo_rs"];

    require "../controlador/conexion_bd.php"; // CORRECCIÓN AQUÍ
    $consulta_details = "SELECT * FROM registro_estudiantes WHERE carnet = ? AND ciclo_escolar = ?";
    $sentencia_details = $conn->prepare($consulta_details);

    if ($sentencia_details) {
        $sentencia_details->bind_param("ss", $carnet_from_redirect, $ciclo_from_redirect);
        $sentencia_details->execute();
        $resultados_details_get = $sentencia_details->get_result();
        if ($resultados_details_get->num_rows > 0) {
            $details_array = $resultados_details_get->fetch_assoc();
            $codigo = $details_array['codigo'] ?? '';
            $carnet = $details_array['carnet'] ?? '';
            $nombre = $details_array['nombre'] ?? '';
            $apellido = $details_array['apellido'] ?? '';
            $grado = $details_array['grado'] ?? '';
            $jornada = $details_array['jornada'] ?? '';
            $ciclo_escolar = $details_array['ciclo_escolar'] ?? '';
            
            // Prepare to show history
            $_POST["carnet"] = $carnet;
            $_POST["ciclo_escolar_historial"] = $ciclo_escolar;
            $show_history_on_load = true;
        }
        $sentencia_details->close();
    } else {
        error_log("Error preparando consulta de detalles de estudiante en redirección: " . $conn->error);
    }
    $conn->close(); // Cerrar la conexión de este bloque
}


// Verificar si se han enviado datos por POST para buscar estudiante
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["buscar_estudiante"])) {
    $buscar = $_POST["buscar"];
    $ciclo_escolar_busqueda = $_POST["ciclo_escolar"];

    require "../controlador/conexion_bd.php";

    $consulta = "SELECT * FROM registro_estudiantes WHERE carnet = ? AND ciclo_escolar = ?";
    $sentencia = $conn->prepare($consulta);

    if ($sentencia) {
        $sentencia->bind_param("ss", $buscar, $ciclo_escolar_busqueda);
        $sentencia->execute();
        $resultados_busqueda = $sentencia->get_result();
        
        if ($resultados_busqueda->num_rows > 0) {
            $resultados_array = $resultados_busqueda->fetch_assoc();
            $codigo = $resultados_array['codigo'] ?? '';
            $carnet = $resultados_array['carnet'] ?? '';
            $nombre = $resultados_array['nombre'] ?? '';
            $apellido = $resultados_array['apellido'] ?? '';
            $grado = $resultados_array['grado'] ?? '';
            $jornada = $resultados_array['jornada'] ?? '';
            $ciclo_escolar = $resultados_array['ciclo_escolar'] ?? '';
        } else {
            $codigo = ""; $carnet = ""; $nombre = ""; $apellido = ""; $grado = ""; $jornada = ""; $ciclo_escolar = "";
            echo "<script>alert('Estudiante no encontrado para el carnet y ciclo escolar proporcionados.');</script>";
        }
        $sentencia->close();
    } else {
        // Consider user-friendly error message here instead of die()
        error_log("Error en la preparación de la consulta de búsqueda de estudiante: " . $conn->error);
        echo "<script>alert('Error al buscar estudiante. Intente más tarde.');</script>";
    }
    $conn->close();
}


// Verificar si se ha enviado el formulario para mostrar el historial de pagos
// O si $show_history_on_load es true (desde redirección)
if (($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["mostrar_historial"])) || $show_history_on_load) {
    $carnet_para_historial = "";
    $ciclo_escolar_para_historial = "";

    if ($show_history_on_load) {
        $carnet_para_historial = $carnet; // Already set from GET block
        $ciclo_escolar_para_historial = $ciclo_escolar; // Already set from GET block
    } else {
        $carnet_para_historial = $_POST["carnet"];
        $ciclo_escolar_para_historial = $_POST["ciclo_escolar_historial"];
    }

    if (!empty($carnet_para_historial) && !empty($ciclo_escolar_para_historial)) {
        require "../controlador/conexion_bd.php";

        $consulta_pagos = "SELECT cp.serie_recibo, cp.num_autorizacion, cp.id_pago, cp.carnet, cp.codigo, cp.tipo_pago, cp.monto, cp.fecha_pago, cp.mes_pago, cp.observacion_adicional, re.nombre, re.apellido, re.jornada, re.grado AS grado, re.nivel, re.ciclo_escolar
        FROM control_pagos cp
        INNER JOIN registro_estudiantes re ON cp.carnet = re.carnet AND cp.ciclo_escolar = re.ciclo_escolar
        WHERE cp.carnet = ? AND re.ciclo_escolar = ?";
        
        $sentencia_pagos = $conn->prepare($consulta_pagos);

        if ($sentencia_pagos) {
            $sentencia_pagos->bind_param("ss", $carnet_para_historial, $ciclo_escolar_para_historial);
            $sentencia_pagos->execute();
            $resultados_pagos = $sentencia_pagos->get_result(); // This will be used to display table
            $sentencia_pagos->close();
        } else {
            error_log("Error en la preparación de la consulta de pagos: " . $conn->error);
            echo "<script>alert('Error al obtener historial de pagos. Intente más tarde.');</script>";
        }
        $conn->close();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/inscripcionstyle.css" />
    <link rel="stylesheet" href="../css/historialstyle.css" />
    <link rel="stylesheet" href="../css/navstyle.css" />
    <title>Buscar y Editar Historial de Pago</title>
    <style>
        .container { width: 95%; margin: 0 auto; max-width: 2400px; }
        table { border-collapse: separate; border-spacing: 10px; width: 100%; font-size: 0.9em; }
        th, td { padding: 8px; max-width: 180px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; border: 1px solid #ddd; }
        th { background-color: #f2f2f2; }
        td button, td a button { padding: 5px 8px; font-size: 0.9em; margin: 2px; }
        /* Style for the message when no records are found via AJAX */
        #no-records-message-js { margin-top: 20px; padding: 15px; background-color: #f8f9fa; border: 1px solid #ced4da; border-radius: 5px; text-align: center; }
    </style>
  <link rel="stylesheet" href="../css/ui-standard.css">
</head>
<body>
<?php include './header.php'; ?>

    <br><br><br><br><br><br><br>
    <div class="container">
    <h2>Historial de Pago</h2>

        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <label for="buscar">Número de Carnet </label>
            <input type="text" id="buscar" name="buscar" required value="<?php echo htmlspecialchars($carnet); /* Populate if carnet is already known */ ?>" />

            <label for="ciclo_escolar">Ciclo Escolar Inscrito</label>
            <input type="text" id="ciclo_escolar" name="ciclo_escolar" required value="<?php echo htmlspecialchars($ciclo_escolar); ?>" />

            <button type="submit" name="buscar_estudiante">Buscar</button>
            <hr>
            <label for="carnet_display">Número de Carnet</label>
            <input type="text" id="carnet_display" name="carnet_display" value="<?php echo htmlspecialchars($carnet); ?>" readonly />

            <label for="ciclo_escolar_result">Ciclo Escolar Inscrito </label>
            <input type="text" id="ciclo_escolar_result" name="ciclo_escolar_result" value="<?php echo htmlspecialchars($ciclo_escolar); ?>" readonly />

            <label for="codigo">Código Personal </label>
            <input type="text" id="codigo" name="codigo" value="<?php echo htmlspecialchars($codigo); ?>" readonly />

            <label for="nombre">Nombre</label>
            <input type="text" id="nombre" name="nombre" value="<?php echo htmlspecialchars($nombre); ?>" readonly />

            <label for="apellido">Apellido</label>
            <input type="text" id="apellido" name="apellido" value="<?php echo htmlspecialchars($apellido); ?>" readonly />
        </form>

        <?php if (!empty($carnet)) : ?>
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" style="margin-top: 10px;">
                <input type="hidden" name="carnet" value="<?php echo htmlspecialchars($carnet); ?>">
                <input type="hidden" name="ciclo_escolar_historial" value="<?php echo htmlspecialchars($ciclo_escolar); ?>">
                <button type="submit" name="mostrar_historial">Mostrar Historial de Pagos</button>
            </form>
        <?php endif; ?>

        <div id="historial-container"> <?php if (isset($resultados_pagos) && $resultados_pagos->num_rows > 0) : ?>
            <h3>Historial de Pagos del Estudiante: <?php echo htmlspecialchars($nombre) . " " . htmlspecialchars($apellido); ?></h3>
            <table id="tabla_historial_pagos">
                    <thead>
                        <tr>
                            <th>N° Recibo</th>
                            <th>N° Autorización</th>
                            <th>Carnet</th>
                            <th>Fecha</th>
                            <th>Tipo de Pago</th>
                            <th>Monto Pagado</th>
                            <th>Mes Pagado</th>
                            <th>Observaciones</th>
                            <th colspan="3">Acciones</th> </tr>
                    </thead>
                    <tbody>
                        <?php
                        $total_cancelado = 0;
                        // $base_total_pendiente = 0; // Already initialized above

                        $all_rows = [];
                        while($r = $resultados_pagos->fetch_assoc()){
                            $all_rows[] = $r;
                        }

                        if (!empty($all_rows)) {
                            $primer_pago = $all_rows[0];
                            if ($primer_pago["nivel"] == "preprimaria" || $primer_pago["nivel"] == "primaria" || $primer_pago["nivel"] == "basico") {
                                $base_total_pendiente = 2200;
                            } else if (($primer_pago["nivel"] == "diversificado1" || $primer_pago["nivel"] == "diversificado2" || $primer_pago["nivel"] == "diversificado3" || $primer_pago["nivel"] == "diversificado4") && $primer_pago["jornada"] == "fin-de-semana") {
                                $base_total_pendiente = 2200;
                            } else if ($primer_pago["nivel"] == "diversificado1" || $primer_pago["nivel"] == "diversificado2" || $primer_pago["nivel"] == "diversificado3" || $primer_pago["nivel"] == "diversificado4") {
                                $base_total_pendiente = 2750;
                            } else if ($primer_pago["nivel"] == "basicopormadurez") {
                                $base_total_pendiente = 1650;
                            }
                        }

                        foreach ($all_rows as $fila) {
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($fila["serie_recibo"]) . " " . htmlspecialchars($fila["id_pago"]) . "</td>";
                            echo "<td>" . htmlspecialchars($fila["num_autorizacion"]) . "</td>";
                            echo "<td>" . htmlspecialchars($fila["carnet"]) . "</td>";
                            echo "<td>" . htmlspecialchars($fila["fecha_pago"]) . "</td>";
                            echo "<td>" . htmlspecialchars($fila["tipo_pago"]) . "</td>";
                            echo "<td>Q " . htmlspecialchars(number_format($fila["monto"], 2)) . "</td>"; // Cell index 5 for monto
                            echo "<td>" . htmlspecialchars($fila["mes_pago"]) . "</td>";
                            echo "<td>" . htmlspecialchars($fila["observacion_adicional"]) . "</td>";
                            
                            echo "<td><a href=\"../controlador/recibo_de_pago.php?fecha_pago=" . urlencode($fila["fecha_pago"]) . "&monto=" . urlencode($fila["monto"]) . "&mes_pago=" . urlencode($fila["mes_pago"]) . "&tipo_pago=" . urlencode($fila["tipo_pago"]) . "&id_pago=" . urlencode($fila["id_pago"]) . "&serie_recibo=" . urlencode($fila["serie_recibo"]) . "&num_autorizacion=" . urlencode($fila["num_autorizacion"]) . "&observacion_adicional=" . urlencode($fila["observacion_adicional"])  . "&nombre=" . urlencode($fila["nombre"])  . "&apellido=" . urlencode($fila["apellido"])  . "&jornada=" . urlencode($fila["jornada"])  . "&nivel=" . urlencode($fila["nivel"])  . "&grado=" . urlencode($fila["grado"]) . "&carnet=" . urlencode($fila["carnet"]) . "\"><button type=\"button\">Imprimir</button></a></td>";
                            
                            echo "<td><a href=\"./editar_pago.php?fecha_pago=" . urlencode($fila["fecha_pago"]) . "&monto=" . urlencode($fila["monto"]) . "&mes_pago=" . urlencode($fila["mes_pago"]) . "&tipo_pago=" . urlencode($fila["tipo_pago"]) . "&id_pago=" . urlencode($fila["id_pago"]) . "&serie_recibo=" . urlencode($fila["serie_recibo"]) . "&num_autorizacion=" . urlencode($fila["num_autorizacion"]) . "&observacion_adicional=" . urlencode($fila["observacion_adicional"])  . "&nombre=" . urlencode($fila["nombre"])  . "&apellido=" . urlencode($fila["apellido"])  . "&jornada=" . urlencode($fila["jornada"])  . "&nivel=" . urlencode($fila["nivel"])  . "&grado=" . urlencode($fila["grado"])  . "&carnet=" . urlencode($fila["carnet"]) . "&codigo=" . urlencode($fila["codigo"]) . "&ciclo_escolar=" . urlencode($fila["ciclo_escolar"]) . "\"><button type=\"button\" style=\"background-color: orange;\">Editar</button></a></td>";
                            
                            //MODIFIED FORM for AJAX
                            echo "<td>
                                    <form class=\"form-eliminar-pago\" action=\"./eliminar_pago.php\" method=\"post\" style=\"display:inline;\">
                                        <input type=\"hidden\" name=\"id_pago\" value=\"" . htmlspecialchars($fila['id_pago']) . "\">
                                        <input type=\"hidden\" name=\"serie_recibo\" value=\"" . htmlspecialchars($fila['serie_recibo']) . "\">
                                        <input type=\"hidden\" name=\"carnet_orig\" value=\"" . htmlspecialchars($carnet_para_historial) . "\">
                                        <input type=\"hidden\" name=\"ciclo_orig\" value=\"" . htmlspecialchars($ciclo_escolar_para_historial) . "\">
                                        <button type=\"submit\" name=\"eliminar_recibo\" style=\"background-color: red; color:white;\">Eliminar</button>
                                    </form>
                                  </td>";
                            $total_cancelado += $fila["monto"];
                            echo "</tr>";
                        }
                        ?>
                    </tbody>
                    <tfoot>
                        <?php
                        // Store base_total_pendiente for JS access if payments exist
                        if (!empty($all_rows)) {
                            echo "<tr style='display:none;'><td colspan='11'><span id='base_total_pendiente_data' data-base-total='" . htmlspecialchars($base_total_pendiente) . "'></span></td></tr>";
                        }
                        ?>
                        <tr> <td colspan="11"><hr></td> </tr>
                        <tr> <td colspan="7" style="text-align:right;"><strong>Total Cancelado:</strong></td> <td><strong id="total_cancelado_valor"><?php echo "Q " . number_format($total_cancelado, 2); ?></strong></td> <td colspan="3"></td> </tr>
                        <tr> <td colspan="7" style="text-align:right;"><strong>Monto Total del Ciclo:</strong></td> <td><strong id="monto_total_ciclo_valor"><?php echo "Q " . number_format($base_total_pendiente, 2); ?></strong></td> <td colspan="3"></td> </tr>
                        <tr> <td colspan="7" style="text-align:right;"><strong>Saldo Pendiente:</strong></td> <td><strong id="saldo_pendiente_valor"> <?php $saldo_final_pendiente = $base_total_pendiente - $total_cancelado; echo "Q " . number_format($saldo_final_pendiente, 2); ?> </strong></td> <td colspan="3"></td> </tr>
                        <tr> <td colspan="11"><hr></td> </tr>
                        <tr> <td colspan="7" style="text-align:right;"><strong>Estado de Pago:</strong></td> <td colspan="4" id="estado_pago_celda"> <?php if ($saldo_final_pendiente <= 0) { echo "<p id='estado_pago_texto' style='background-color: green; color:white; border-radius: 10px; padding: 10px; text-align:center;'>Pago Completo.</p>"; } else { echo "<p id='estado_pago_texto' style='background-color: red; color:white; border-radius: 10px; padding: 10px; text-align:center;'>Pago Pendiente.</p>"; } ?> </td> </tr>
                    </tfoot>
            </table>
        <?php elseif (($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["mostrar_historial"])) || $show_history_on_load) : ?>
            <p id="no-records-message-php">No se encontraron registros de pagos para este estudiante en el ciclo escolar indicado.</p>
        <?php endif; ?>
        </div> </div>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const buscarForm = document.querySelector('form[action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>"]');
        const buscarInput = document.getElementById('buscar');
        const cicloInput = document.getElementById('ciclo_escolar');

        if (buscarForm && buscarInput && cicloInput) {
            buscarForm.addEventListener('submit', function(event) {
                if (event.submitter && event.submitter.name === 'buscar_estudiante') {
                    if (buscarInput.value.trim() === '' || cicloInput.value.trim() === '') {
                        alert('Por favor, ingrese el número de carnet y el ciclo escolar para buscar.');
                        event.preventDefault();
                    }
                }
            });
        }

        const formsEliminar = document.querySelectorAll('.form-eliminar-pago');
        formsEliminar.forEach(form => {
            form.addEventListener('submit', function(event) {
                event.preventDefault(); // Prevent traditional form submission

                const serieRecibo = this.querySelector('input[name="serie_recibo"]').value;
                const idPagoInput = this.querySelector('input[name="id_pago"]').value;
                const idReciboCompleto = serieRecibo + " " + idPagoInput;

                if (!confirm('¿Está seguro de que desea eliminar este recibo N° ' + idReciboCompleto + '?')) {
                    return;
                }

                const formData = new FormData(this);
                const currentRow = this.closest('tr');
                const montoCell = currentRow.cells[5]; // Monto Pagado is in the 6th cell (index 5)
                let montoEliminado = 0;
                if (montoCell) {
                     montoEliminado = parseFloat(montoCell.textContent.replace(/Q\s*|,/g, ''));
                }


                fetch(this.action, { // this.action is "./eliminar_pago.php"
                    method: 'POST',
                    body: formData
                })
                .then(response => {
                    if (!response.ok && response.type === 'opaque') { // Opaque response often means redirect without CORS, or error
                        // Cannot read response.url for opaque responses directly if it's a cross-origin redirect that fetch can't fully follow
                        // However, for same-origin redirects, response.url should be the final URL.
                        // If eliminar_pago.php redirects, fetch follows it.
                        // We rely on the fact that it redirects back to a page whose URL contains the status.
                        // This part can be tricky if there are CORS issues or complex redirect chains.
                        // Assuming same-origin and fetch follows the redirect and response.url is readable:
                         return response.url; // Or handle as potential error if url not available
                    }
                    return response.url; // The final URL after all redirects
                })
                .then(finalUrl => {
                    const urlParams = new URLSearchParams(new URL(finalUrl).search);
                    const status = urlParams.get('status');
                    let message = '';
                    let success = false;

                    switch (status) {
                        case 'deleted_ok':
                            success = true;
                            message = 'Recibo (ID: ' + formData.get('id_pago') + ', Serie: ' + formData.get('serie_recibo') + ') eliminado correctamente.';
                            if (currentRow) {
                                currentRow.remove();
                            }
                            actualizarTotales(montoEliminado);
                            verificarTablaVacia();
                            break;
                        case 'not_found':
                            message = 'No se encontró el recibo para eliminar o ya había sido eliminado.';
                            break;
                        case 'delete_execution_error':
                            message = 'Error al ejecutar la eliminación del recibo.';
                            break;
                        case 'delete_prepare_error':
                            message = 'Error al preparar la consulta de eliminación.';
                            break;
                        case 'invalid_id':
                            message = 'Error: El ID del pago no es válido.';
                            break;
                        case 'missing_params':
                            message = 'Información insuficiente para eliminar el recibo (faltan ID o Serie).';
                            break;
                        case 'invalid_request_method':
                            message = 'Acceso no permitido (método de solicitud incorrecto).';
                            break;
                        default:
                            message = 'Ocurrió un error inesperado (' + (status || 'no status') + ') durante la eliminación. URL final: ' + finalUrl;
                            break;
                    }
                    alert(message);

                })
                .catch(error => {
                    console.error('Error en la solicitud AJAX:', error);
                    alert('Error de conexión al intentar eliminar el recibo.');
                });
            });
        });

        function actualizarTotales(montoRestar) {
            const totalCanceladoStrong = document.getElementById('total_cancelado_valor');
            const montoTotalCicloStrong = document.getElementById('monto_total_ciclo_valor'); // For display, value comes from data-attribute
            const saldoPendienteStrong = document.getElementById('saldo_pendiente_valor');
            const estadoPagoTextoP = document.getElementById('estado_pago_texto');
            const baseTotalDataElem = document.getElementById('base_total_pendiente_data');

            if (!totalCanceladoStrong || !montoTotalCicloStrong || !saldoPendienteStrong || !estadoPagoTextoP || !baseTotalDataElem) {
                console.warn("Algunos elementos de totales no fueron encontrados para actualizar.");
                return;
            }

            let montoTotalCiclo = parseFloat(baseTotalDataElem.dataset.baseTotal);
            if (isNaN(montoTotalCiclo)) montoTotalCiclo = 0;


            let totalCanceladoActual = parseFloat(totalCanceladoStrong.textContent.replace(/Q\s*|,/g, ''));
            if (isNaN(totalCanceladoActual)) totalCanceladoActual = 0;

            let nuevoTotalCancelado = totalCanceladoActual - montoRestar;
            totalCanceladoStrong.textContent = 'Q ' + nuevoTotalCancelado.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');

            let nuevoSaldoPendiente = montoTotalCiclo - nuevoTotalCancelado;
            saldoPendienteStrong.textContent = 'Q ' + nuevoSaldoPendiente.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');

            if (nuevoSaldoPendiente <= 0) {
                estadoPagoTextoP.textContent = 'Pago Completo.';
                estadoPagoTextoP.style.backgroundColor = 'green';
            } else {
                estadoPagoTextoP.textContent = 'Pago Pendiente.';
                estadoPagoTextoP.style.backgroundColor = 'red';
            }
        }

        function verificarTablaVacia() {
            const tablaHistorial = document.getElementById('tabla_historial_pagos');
            const tbody = tablaHistorial ? tablaHistorial.querySelector('tbody') : null;
            const historialContainer = document.getElementById('historial-container');

            // Remove any existing JS-generated no-records message first
            const existingJsMessage = document.getElementById('no-records-message-js');
            if (existingJsMessage) {
                existingJsMessage.remove();
            }
            // Also hide PHP message if it exists (though ideally it wouldn't if table was there)
             const phpMessage = document.getElementById('no-records-message-php');
            if (phpMessage) {
                phpMessage.style.display = 'none';
            }


            if (tablaHistorial && tbody && tbody.children.length === 0) {
                tablaHistorial.style.display = 'none'; // Hide the entire table (thead, tbody, tfoot)
                if (historialContainer) {
                    const noRecordsMessage = document.createElement('p');
                    noRecordsMessage.id = 'no-records-message-js';
                    noRecordsMessage.textContent = 'No se encontraron registros de pagos para este estudiante en el ciclo escolar indicado.';
                    historialContainer.appendChild(noRecordsMessage);
                }
            } else if (tablaHistorial) {
                tablaHistorial.style.display = ''; // Ensure table is visible if rows exist
            }
        }
        // Initial check in case the page loads with an empty table (e.g., after all records deleted by other means and reloaded)
        // This is more for robustness; primary trigger is after AJAX delete.
        // verificarTablaVacia(); // Commenting out, PHP handles initial empty state. JS handles it after deletion.
    });
    </script>
</body>
</html>
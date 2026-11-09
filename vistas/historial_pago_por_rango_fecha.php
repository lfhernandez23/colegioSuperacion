<?php require_once __DIR__ . '/../controlador/auth.php'; colegio_requerir_sesion(); ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/inscripcionstyle.css" />
    <link rel="stylesheet" href="../css/historialstyle.css" />
    <link rel="stylesheet" href="../css/navstyle.css" />
    <link rel="stylesheet" href="../css/reportesstyle.css" />
    <title>Pagos por Rango de Fechas</title>
     <style>
        .container {
            max-width: 95%; margin: 50px auto; background-color: #fff; padding: 20px;
            border: 1px solid #ddd; border-radius: 8px; box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            overflow-x: auto;
        }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td {
            padding: 8px; text-align: center; white-space: nowrap;
            overflow: hidden; text-overflow: ellipsis; border: 1px solid #ddd;
        }
        th { font-weight: bold; background-color: #f2f2f2; }
        .actions-cell button, .actions-cell a > button {
            padding: 5px 10px; font-size: 0.9em; margin: 2px; border: none;
            border-radius: 4px; color: white; cursor: pointer; text-decoration: none;
        }
        .actions-cell .btn-imprimir { background-color: #007bff; }
        .actions-cell .btn-editar { background-color: #ffc107; color: #212529; }
        .actions-cell .btn-eliminar { background-color: #dc3545; }
        .no-records-message-js {
            text-align: center; margin-top: 20px; padding: 15px;
            background-color: #f8f9fa; border: 1px solid #ced4da; border-radius: 5px;
        }
         h2, h3 { text-align: center; }
    </style>
  <link rel="stylesheet" href="../css/ui-standard.css">
</head>
<body>
<?php include './header.php'; ?>

    <div class="container">
        <br><br><br>
        <h2>Historial de Pagos por Rango de Fechas</h2>
        <h3>Formulario de Búsqueda</h3>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <label for="fecha_inicio">Fecha de Inicio</label>
            <input type="date" id="fecha_inicio" name="fecha_inicio" required value="<?php echo isset($_POST['fecha_inicio']) ? htmlspecialchars($_POST['fecha_inicio']) : ''; ?>"><br><br>
            <label for="fecha_fin">Fecha de Fin</label>
            <input type="date" id="fecha_fin" name="fecha_fin" required value="<?php echo isset($_POST['fecha_fin']) ? htmlspecialchars($_POST['fecha_fin']) : ''; ?>"><br><br>
            <button type="submit" name="buscar">Buscar</button>
        </form>

        <div id="historial-container-rango-fecha">
        <?php
        if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["fecha_inicio"]) && isset($_POST["fecha_fin"])) {
            $fecha_inicio = $_POST["fecha_inicio"];
            $fecha_fin = $_POST["fecha_fin"];

            include_once "../controlador/conexion_bd.php";

            $consulta = "
            SELECT 
                cp.serie_recibo, cp.num_autorizacion, cp.id_pago, cp.carnet, cp.codigo, 
                cp.tipo_pago, cp.monto, cp.fecha_pago, cp.mes_pago, cp.observacion_adicional, 
                re.nombre, re.apellido, re.jornada, re.grado, re.nivel, re.ciclo_escolar
            FROM control_pagos cp
            LEFT JOIN registro_estudiantes re ON re.id = (
                SELECT MIN(re2.id) FROM registro_estudiantes re2
                WHERE re2.carnet = cp.carnet AND re2.ciclo_escolar = cp.ciclo_escolar
            )
            WHERE cp.fecha_pago BETWEEN ? AND ?
            ORDER BY cp.fecha_pago ASC, cp.id_pago ASC";
            
            $sentencia = $conn->prepare($consulta);
            $resultados = null;

            if ($sentencia) {
                $sentencia->bind_param("ss", $fecha_inicio, $fecha_fin);
                $sentencia->execute();
                $resultados = $sentencia->get_result();
                $sentencia->close();
            } else {
                echo "<p class='no-records-message-js'>Error en la preparación de la consulta: " . htmlspecialchars($conn->error) . "</p>";
            }
            $conn->close();

            if (isset($resultados) && $resultados->num_rows > 0) {
                echo "<h3>Historial de Pagos desde " . htmlspecialchars($fecha_inicio) . " hasta " . htmlspecialchars($fecha_fin) . "</h3>";
                echo "<table id='tabla_pagos_rango_fecha'>";
                echo "<thead><tr><th>N° Recibo</th><th>N° Autorización</th><th>Fecha</th><th>Tipo de Pago</th><th>Monto Pagado</th><th>Mes Pagado</th><th>Observaciones</th><th>Carnet</th><th>Código</th><th>Nombre</th><th>Apellido</th><th>Acciones</th></tr></thead>";
                echo "<tbody>";
                
                $total_dinero = 0;
                while ($fila = $resultados->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($fila["serie_recibo"] . " " . $fila["id_pago"]) . "</td>";
                    echo "<td>" . htmlspecialchars($fila["num_autorizacion"]) . "</td>";
                    echo "<td>" . htmlspecialchars($fila["fecha_pago"]) . "</td>";
                    echo "<td>" . htmlspecialchars($fila["tipo_pago"]) . "</td>";
                    echo "<td class='monto-pago'>" . htmlspecialchars("Q " . number_format($fila["monto"], 2)) . "</td>";
                    echo "<td>" . htmlspecialchars($fila["mes_pago"]) . "</td>";
                    echo "<td>" . htmlspecialchars($fila["observacion_adicional"]) . "</td>";
                    echo "<td>" . htmlspecialchars($fila["carnet"]) . "</td>";
                    echo "<td>" . htmlspecialchars($fila["codigo"]) . "</td>";
                    echo "<td>" . htmlspecialchars($fila["nombre"]) . "</td>";
                    echo "<td>" . htmlspecialchars($fila["apellido"]) . "</td>";
                    echo "<td class='actions-cell'>";
                    // Imprimir
                    // Imprimir (Corrección para incluir carnet en el reporte por rango)
echo "<a href=\"../controlador/recibo_de_pago.php?" . http_build_query(array_merge(
    array_intersect_key($fila, array_flip(['fecha_pago', 'monto', 'mes_pago', 'tipo_pago', 'id_pago', 'serie_recibo', 'num_autorizacion', 'observacion_adicional', 'nombre', 'apellido', 'jornada', 'nivel', 'grado'])),
    ['carnet' => $fila['carnet']]
)) . "\"><button type=\"button\" class='btn-imprimir'>Imprimir</button></a>";
                    // Editar
                    echo "<a href=\"/colegio_superacion/vistas/editar_pago.php?" . http_build_query($fila) . "\"><button type=\"button\" class='btn-editar'>Editar</button></a>";
                    // Eliminar Form
                    echo "<form class=\"form-eliminar-pago\" action=\"./eliminar_pago.php\" method=\"post\" style=\"display:inline-block;\">";
                    echo "<input type=\"hidden\" name=\"id_pago\" value=\"" . htmlspecialchars($fila['id_pago']) . "\">";
                    echo "<input type=\"hidden\" name=\"serie_recibo\" value=\"" . htmlspecialchars($fila['serie_recibo']) . "\">";
                    echo "<button type=\"submit\" class='btn-eliminar'>Eliminar</button>";
                    echo "</form>";
                    echo "</td>";                  
                    echo "</tr>";
                    $total_dinero += $fila["monto"];
                }
                echo "</tbody></table>";
                echo "<h3 id=\"total_pagos_rango_fecha\">Total de Pagos: Q " . number_format($total_dinero, 2) . "</h3>";
            } else {
                echo "<p class='no-records-message-js'>No se encontraron pagos para el rango de fechas especificado.</p>";
            }
        }
        ?>
        </div>
    </div>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const formsEliminar = document.querySelectorAll('.form-eliminar-pago');
        formsEliminar.forEach(form => {
            form.addEventListener('submit', function(event) {
                event.preventDefault();
                const serieRecibo = this.querySelector('input[name="serie_recibo"]').value;
                const idPagoInput = this.querySelector('input[name="id_pago"]').value;
                if (!confirm('¿Está seguro de que desea eliminar el recibo N° ' + serieRecibo + idPagoInput + '?')) {
                    return;
                }

                const formData = new FormData(this);
                const currentRow = this.closest('tr');
                const montoCell = currentRow.querySelector('.monto-pago');
                let montoEliminado = 0;
                if (montoCell) {
                    montoEliminado = parseFloat(montoCell.textContent.replace(/Q\s*|,/g, ''));
                }

                fetch(this.action, { method: 'POST', body: formData })
                .then(response => response.url)
                .then(finalUrl => {
                    const urlParams = new URLSearchParams(new URL(finalUrl).search);
                    const status = urlParams.get('status');
                    let message = '';
                    if (status === 'deleted_ok') {
                        message = 'Recibo eliminado correctamente.';
                        if (currentRow) currentRow.remove();
                        actualizarTotalPagos(montoEliminado, 'total_pagos_rango_fecha', "Total de Pagos: Q ");
                        verificarTablaVacia('tabla_pagos_rango_fecha', 'historial-container-rango-fecha', 'total_pagos_rango_fecha', "No se encontraron pagos para el rango de fechas especificado.");
                    } else {
                         message = 'Error al eliminar recibo. Status: ' + (status || 'desconocido');
                    }
                    alert(message);
                })
                .catch(error => {
                    console.error('Error AJAX:', error);
                    alert('Error de conexión al eliminar.');
                });
            });
        });

        function actualizarTotalPagos(montoRestar, totalElementId, labelPrefix) {
            const totalElement = document.getElementById(totalElementId);
            if (!totalElement) return;
            let currentTotalText = totalElement.textContent.replace(labelPrefix, '').replace(/,/g, '');
            let currentTotal = parseFloat(currentTotalText);
            if (isNaN(currentTotal)) currentTotal = 0;
            let nuevoTotal = currentTotal - montoRestar;
            totalElement.textContent = labelPrefix + nuevoTotal.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
        }

        function verificarTablaVacia(tableId, containerId, totalElementId, noRecordsText) {
            const tabla = document.getElementById(tableId);
            const tbody = tabla ? tabla.querySelector('tbody') : null;
            const container = document.getElementById(containerId);
            const totalDisplay = document.getElementById(totalElementId);

            const existingJsMessage = container.querySelector('.no-records-message-js');
            if (existingJsMessage) existingJsMessage.remove();

            if (tabla && tbody && tbody.children.length === 0) {
                tabla.style.display = 'none';
                if (totalDisplay) totalDisplay.style.display = 'none';
                if (container) {
                    const noRecordsMessage = document.createElement('p');
                    noRecordsMessage.className = 'no-records-message-js';
                    noRecordsMessage.textContent = noRecordsText;
                    container.appendChild(noRecordsMessage);
                }
            } else if (tabla) {
                tabla.style.display = '';
                if (totalDisplay) totalDisplay.style.display = '';
            }
        }
    });
    </script>
</body>
</html>
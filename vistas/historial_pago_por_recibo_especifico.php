<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/inscripcionstyle.css" />
    <link rel="stylesheet" href="../css/historialstyle.css" />
    <link rel="stylesheet" href="../css/navstyle.css" />
    <link rel="stylesheet" href="../css/reportesstyle.css" />
    <title>Pagos por Recibo Específico</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; background-color: #f4f4f4; }
        .container {
            max-width: 95%; margin: 20px auto; background-color: #fff; padding: 20px;
            border: 1px solid #ddd; border-radius: 8px; box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            overflow-x: auto;
        }
        h2, h3 { text-align: center; color: #333; }
        form { display: flex; flex-direction: column; align-items: center; margin-bottom: 20px; }
        form label { margin-bottom: 5px; font-weight: bold; }
        .input-group { display: flex; align-items: center; margin-bottom: 10px; }
        .input-group strong { margin-right: 5px; font-size: 1.1em; }
        form input[type="number"] { padding: 10px; border-radius: 5px; border: 1px solid #ddd; }
        form button {
            padding: 10px 15px; background-color: #5cb85c; color: white; cursor: pointer;
            border-radius: 5px; border: 1px solid #ddd; transition: background-color 0.3s ease;
        }
        form button:hover { background-color: #4cae4c; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td {
            padding: 10px; text-align: center; border-bottom: 1px solid #ddd;
            white-space: nowrap; overflow: hidden; text-overflow: ellipsis; border: 1px solid #ddd;
        }
        th { background-color: #f2f2f2; font-weight: bold; }
        tr:hover { background-color: #f9f9f9; }
        .actions-cell button, .actions-cell a > button {
            padding: 8px 12px; margin-right: 5px; border: none; border-radius: 4px;
            cursor: pointer; font-size: 0.9em; color:white; text-decoration:none;
        }
        .actions-cell .btn-imprimir { background-color: #007bff; }
        .actions-cell .btn-editar { background-color: #ffc107; color: #212529;}
        .actions-cell .btn-eliminar { background-color: #e74c3c; }
        .total-pagos { text-align: right; margin-top: 20px; font-size: 1.2em; font-weight: bold; }
        .no-records-message-js {
            text-align: center; margin-top: 20px; padding: 15px;
            background-color: #f8f9fa; border: 1px solid #ced4da; border-radius: 5px;
        }
    </style>
</head>
<body>
    <?php include './header.php'; ?>

    <div class="container">
        <br><br><br>
        <h2>Historial de Pagos por Recibo Específico</h2>
        <h3>Formulario de Búsqueda</h3>
        <form id="form-buscar-recibo" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div>
                <label for="recibo_numero">Número de Recibo a Buscar:</label>
                <div class="input-group">
                    <strong>A</strong>
                    <input type="number" id="recibo_numero" name="recibo_numero" required min="1" value="<?php echo isset($_POST['recibo_numero']) ? htmlspecialchars($_POST['recibo_numero']) : ''; ?>">
                </div>
            </div>
            <button type="submit" name="buscar">Buscar</button>
        </form>

        <div id="historial-container-recibo-especifico">
        <?php
        if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["recibo_numero"])) {
            $recibo_numero_val = filter_input(INPUT_POST, "recibo_numero", FILTER_VALIDATE_INT);
            $serie_recibo_val = 'A'; // Serie es fija 'A'
            $resultados = null;

            if ($recibo_numero_val === false || $recibo_numero_val <= 0) {
                 echo "<p class='no-records-message-js'>Error: El número de recibo debe ser un entero positivo.</p>";
            } else {
                include_once "../controlador/conexion_bd.php";

                $consulta = "
                SELECT
                    cp.serie_recibo, cp.num_autorizacion, cp.id_pago, cp.carnet, cp.codigo, 
                    cp.tipo_pago, cp.monto, cp.fecha_pago, cp.mes_pago, cp.observacion_adicional, 
                    re.nombre, re.apellido, re.jornada, re.grado, re.nivel, re.ciclo_escolar
                FROM control_pagos cp
                INNER JOIN (
                    SELECT carnet, MAX(ciclo_escolar) AS ciclo_escolar
                    FROM registro_estudiantes GROUP BY carnet
                ) AS re_max ON cp.carnet = re_max.carnet
                INNER JOIN registro_estudiantes re ON cp.carnet = re.carnet AND re.ciclo_escolar = re_max.ciclo_escolar
                WHERE cp.id_pago = ? AND cp.serie_recibo = ?";

                $sentencia = $conn->prepare($consulta);

                if ($sentencia) {
                    $sentencia->bind_param("is", $recibo_numero_val, $serie_recibo_val);
                    $sentencia->execute();
                    $resultados = $sentencia->get_result();
                    $sentencia->close();
                } else {
                    echo "<p class='no-records-message-js'>Error en la preparación de la consulta: " . htmlspecialchars($conn->error) . "</p>";
                }
                $conn->close();

                if (isset($resultados) && $resultados->num_rows > 0) {
                    echo "<h3>Detalle del Recibo " . htmlspecialchars($serie_recibo_val . $recibo_numero_val) . "</h3>";
                    echo "<table id='tabla_pago_recibo_especifico'>";
                    echo "<thead>";
                    echo "<tr><th>N° Recibo</th><th>N° Autorización</th><th>Fecha</th><th>Tipo de Pago</th><th>Monto Pagado</th><th>Mes Pagado</th><th>Observaciones</th><th>Carnet</th><th>Código</th><th>Nombre</th><th>Apellido</th><th>Acciones</th></tr>";
                    echo "</thead>";
                    echo "<tbody>";
                    
                    $total_dinero = 0;
                    while ($fila = $resultados->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($fila["serie_recibo"] . $fila["id_pago"]) . "</td>";
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
                        // Imprimir con carnet forzado en el query
echo "<a href=\"../controlador/recibo_de_pago.php?" . http_build_query(array_merge(
    array_intersect_key($fila, array_flip(['fecha_pago', 'monto', 'mes_pago', 'tipo_pago', 'id_pago', 'serie_recibo', 'num_autorizacion', 'observacion_adicional', 'nombre', 'apellido', 'jornada', 'nivel', 'grado'])),
    ['carnet' => $fila['carnet']] 
)) . "\"><button type=\"button\" class='btn-imprimir'>Imprimir</button></a>";
                        // Editar
                        echo "<a href=\"/colegio_superacion/vistas/editar_pago.php?" . http_build_query($fila) . "\"><button type=\"button\" class='btn-editar'>Editar</button></a>";
                        // Eliminar Form (Replaced existing synchronous form)
                        echo "<form class=\"form-eliminar-pago\" action=\"./eliminar_pago.php\" method=\"post\" style=\"display:inline-block;\">";
                        echo "<input type=\"hidden\" name=\"id_pago\" value=\"" . htmlspecialchars($fila['id_pago']) . "\">";
                        echo "<input type=\"hidden\" name=\"serie_recibo\" value=\"" . htmlspecialchars($fila['serie_recibo']) . "\">";
                        echo "<button type=\"submit\" class='btn-eliminar'>Eliminar</button>";
                        echo "</form>";
                        echo "</td>";
                        echo "</tr>";
                        $total_dinero += $fila["monto"]; // Should only be one row, but sum anyway
                    }
                    echo "</tbody>";
                    echo "</table>";
                    echo "<div class=\"total-pagos\" id=\"monto_recibo_especifico\">Monto del Recibo: Q " . htmlspecialchars(number_format($total_dinero, 2)) . "</div>";
                } else {
                    echo "<p class='no-records-message-js'>No se encontraron pagos para el recibo " . htmlspecialchars($serie_recibo_val . $recibo_numero_val) . ".</p>";
                }
            }
        }
        ?>
        </div>
    </div>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Since there's only one potential form if a record is found, 
        // we can re-query for it if the search results are dynamically loaded (though here it's page reload)
        const bindDeleteListeners = () => {
            const formsEliminar = document.querySelectorAll('#historial-container-recibo-especifico .form-eliminar-pago');
            formsEliminar.forEach(form => {
                // Prevent multiple bindings if called multiple times (not strictly necessary with page reload)
                if (form.dataset.listenerAttached) return;
                form.dataset.listenerAttached = 'true';

                form.addEventListener('submit', function(event) {
                    event.preventDefault();
                    const serieRecibo = this.querySelector('input[name="serie_recibo"]').value;
                    const idPagoInput = this.querySelector('input[name="id_pago"]').value;
                    if (!confirm('¿Está seguro de que desea eliminar el recibo N° ' + serieRecibo + idPagoInput + '?')) {
                        return;
                    }

                    const formData = new FormData(this);
                    // No need to update total for a single receipt view, it will just disappear
                    
                    fetch(this.action, { method: 'POST', body: formData })
                    .then(response => response.url)
                    .then(finalUrl => {
                        const urlParams = new URLSearchParams(new URL(finalUrl).search);
                        const status = urlParams.get('status');
                        let message = '';
                        if (status === 'deleted_ok') {
                            message = 'Recibo eliminado correctamente.';
                            // For single receipt view, just clear the container or show "deleted" message
                            const container = document.getElementById('historial-container-recibo-especifico');
                            if (container) {
                                container.innerHTML = "<p class='no-records-message-js'>El recibo ha sido eliminado. Realice una nueva búsqueda si lo desea.</p>";
                            }
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
        };
        
        bindDeleteListeners(); // Call on initial load

        // If you were to load results via AJAX, you would call bindDeleteListeners() again in the success callback.
        // For this PHP-based form submission, it's simpler.
    });
    </script>
</body>
</html>
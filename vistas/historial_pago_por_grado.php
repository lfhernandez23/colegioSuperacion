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
    <title>Historial de Pagos por Grado</title>
    <style>
        /* Estilo para el contenedor principal */
        .container {
            max-width: 95%;
            margin: 50px auto;
            background-color: #fff;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            overflow-x: auto;
        }

        /* Estilo para la tabla */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px; /* Added margin for spacing */
        }

        th,
        td {
            padding: 8px; /* Consistent padding */
            text-align: center;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            border: 1px solid #ddd; /* Added border to all cells for clarity */
        }

        th {
            font-weight: bold;
            background-color: #f2f2f2; /* Light background for headers */
        }

        tfoot td { /* This was in original CSS, but there's no tfoot in current HTML */
            font-weight: bold;
        }

        /* Styles for action buttons */
        .actions-cell button, .actions-cell a button {
            padding: 5px 10px;
            font-size: 0.9em;
            margin: 2px;
            border: none;
            border-radius: 4px;
            color: white;
            cursor: pointer;
        }
        .actions-cell .btn-imprimir { background-color: #007bff; } /* Blue */
        .actions-cell .btn-editar { background-color: #ffc107; color: #212529;} /* Orange */
        .actions-cell .btn-eliminar { background-color: #dc3545; } /* Red */

        .no-records-message-js {
            text-align: center;
            margin-top: 20px;
            padding: 15px;
            background-color: #f8f9fa;
            border: 1px solid #ced4da;
            border-radius: 5px;
        }
    </style>
  <link rel="stylesheet" href="../css/ui-standard.css">
</head>

<body>
<?php include './header.php'; ?>

    <div class="container">
        <br>
        <br>
        <br>
        <h2>Historial de Pagos por Grado</h2>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <label for="jornada">Jornada</label>
            <select id="jornada" name="jornada" required onchange="actualizarNivel()">
                <option value="" selected>- Seleccione una Opción -</option>
                <option value="diario-matutino">Plan Diario - Matutino</option>
                <option value="diario-vespertino">Plan Diario - Vespertino</option>
                <option value="fin-de-semana">Plan Fin de Semana - Sabatino</option>
            </select>

            <label for="nivel">Nivel Asignado</label>
            <select id="nivel" name="nivel" required onchange="actualizarGrado()">
                <option value="" selected>- Seleccione una Jornada Primero -</option>
            </select>

            <label for="grado">Grado Asignado</label>
            <select id="grado" name="grado" required>
                 <option value="" selected>- Seleccione un Nivel Primero -</option>
            </select>
            <label for="ciclo_escolar">Ciclo Escolar:</label>
            <input type="text" id="ciclo_escolar" name="ciclo_escolar" required value="<?php echo date('Y'); ?>"><br><br>
            <button type="submit" name="buscar">Buscar</button>
        </form>

        <div id="historial-grado-container">
        <?php
        if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["buscar"])) {
            $jornada_val = $_POST["jornada"];
            $grado_val = $_POST["grado"];
            $nivel_val = $_POST["nivel"];
            $ciclo_escolar_val = $_POST["ciclo_escolar"];

            include_once "../controlador/conexion_bd.php";

            $consulta = "SELECT 
                            cp.serie_recibo, 
                            cp.num_autorizacion, 
                            cp.id_pago, 
                            cp.carnet, 
                            cp.codigo, 
                            cp.tipo_pago, 
                            cp.monto, 
                            cp.fecha_pago, 
                            cp.mes_pago, 
                            cp.observacion_adicional, 
                            re.nombre, 
                            re.apellido, 
                            re.jornada, 
                            re.grado, 
                            re.nivel, 
                            re.ciclo_escolar 
                        FROM 
                            control_pagos cp
                        INNER JOIN registro_estudiantes re ON re.id = (
                            SELECT MIN(re2.id) FROM registro_estudiantes re2
                            WHERE re2.carnet = cp.carnet AND re2.ciclo_escolar = cp.ciclo_escolar
                        ) 
                        WHERE 
                            re.jornada = ? AND 
                            re.grado = ? AND 
                            re.nivel = ? AND 
                            re.ciclo_escolar = ? 
                        ORDER BY
                            re.apellido ASC, re.nombre ASC, cp.fecha_pago ASC, cp.id_pago ASC"; // Added cp.ciclo_escolar to JOIN

            $sentencia = $conn->prepare($consulta);

            if ($sentencia) {
                $sentencia->bind_param("ssss", $jornada_val, $grado_val, $nivel_val, $ciclo_escolar_val); // Assuming ciclo_escolar is string like YYYY
                $sentencia->execute();
                $resultados = $sentencia->get_result();
                $sentencia->close();
            } else {
                echo "<p>Error en la preparación de la consulta: " . htmlspecialchars($conn->error) . "</p>";
                $resultados = null; // Ensure $resultados is defined
            }
            $conn->close();

            if (isset($resultados) && $resultados->num_rows > 0) {
                // Mapping for display text (can be moved to a helper function or array for larger sets)
                $jornada_display = $jornada_val;
                if ($jornada_val === "diario-matutino") $jornada_display = "Plan Diario - Matutino";
                else if ($jornada_val === "diario-vespertino") $jornada_display = "Plan Diario - Vespertino";
                else if ($jornada_val === "fin-de-semana") $jornada_display = "Plan Fin de Semana - Sabatino";

                $nivel_display = $nivel_val;
                if ($nivel_val === "preprimaria") $nivel_display = "Pre-Primaria";
                else if ($nivel_val === "primaria") $nivel_display = "Primaria";
                else if ($nivel_val === "basico") $nivel_display = "Básico";
                else if ($nivel_val === "basicopormadurez") $nivel_display = "Básico por Madurez";
                else if ($nivel_val === "diversificado1") $nivel_display = "Perito Contador con Orientación en Computación";
                else if ($nivel_val === "diversificado2") $nivel_display = "Perito en Administración de Empresa";
                else if ($nivel_val === "diversificado3") $nivel_display = "Bachiller en Computación con Orientación Comercial";
                else if ($nivel_val === "diversificado4") $nivel_display = "Bachiller en Ciencias y Letras por Madurez";

                $grado_display = $grado_val;
                if ($grado_val === "1grado") $grado_display = "1er Grado";
                else if ($grado_val === "2grado") $grado_display = "2do Grado";
                else if ($grado_val === "3grado") $grado_display = "3er Grado";
                else if ($grado_val === "4grado") $grado_display = "4to Grado";
                else if ($grado_val === "5grado") $grado_display = "5to Grado";
                else if ($grado_val === "6grado") $grado_display = "6to Grado";

                echo "<h3>Resultados de Búsqueda</h3>";
                echo "<p><strong>Jornada:</strong> " . htmlspecialchars($jornada_display) . 
                     " | <strong>Nivel:</strong> " . htmlspecialchars($nivel_display) . 
                     " | <strong>Grado:</strong> " . htmlspecialchars($grado_display) . 
                     " | <strong>Ciclo Escolar:</strong> " . htmlspecialchars($ciclo_escolar_val) . "</p>";

                echo "<table id='tabla_pagos_grado'>";
                echo "<thead><tr><th>N° Recibo</th><th>N° Autorización</th><th>Fecha</th><th>Tipo de Pago</th><th>Monto Pagado</th><th>Mes Pagado</th><th>Observaciones</th><th>Carnet</th><th>Código</th><th>Nombre</th><th>Apellido</th><th>Acciones</th></tr></thead>";
                echo "<tbody>";
                
                $total_dinero = 0;
                while ($fila = $resultados->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($fila["serie_recibo"] . " " . $fila["id_pago"]) . "</td>";
                    echo "<td>" . htmlspecialchars($fila["num_autorizacion"]) . "</td>";
                    echo "<td>" . htmlspecialchars($fila["fecha_pago"]) . "</td>";
                    echo "<td>" . htmlspecialchars($fila["tipo_pago"]) . "</td>";
                    echo "<td class='monto-pago'>" . htmlspecialchars("Q " . number_format($fila["monto"], 2)) . "</td>"; // Added class for JS
                    echo "<td>" . htmlspecialchars($fila["mes_pago"]) . "</td>";
                    echo "<td>" . htmlspecialchars($fila["observacion_adicional"]) . "</td>";
                    echo "<td>" . htmlspecialchars($fila["carnet"]) . "</td>";
                    echo "<td>" . htmlspecialchars($fila["codigo"]) . "</td>";
                    echo "<td>" . htmlspecialchars($fila["nombre"]) . "</td>";
                    echo "<td>" . htmlspecialchars($fila["apellido"]) . "</td>";
                    
                    echo "<td class='actions-cell'>";
                    // Imprimir
            // Imprimir (Línea 168 aprox.)
echo "<a href=\"../controlador/recibo_de_pago.php?" . http_build_query([
    'fecha_pago' => $fila["fecha_pago"], 
    'monto' => $fila["monto"], 
    'mes_pago' => $fila["mes_pago"],
    'tipo_pago' => $fila["tipo_pago"], 
    'id_pago' => $fila["id_pago"], 
    'serie_recibo' => $fila["serie_recibo"],
    'num_autorizacion' => $fila["num_autorizacion"], 
    'observacion_adicional' => $fila["observacion_adicional"],
    'nombre' => $fila["nombre"], 
    'apellido' => $fila["apellido"], 
    'jornada' => $fila["jornada"],
    'nivel' => $fila["nivel"], 
    'grado' => $fila["grado"],
    'carnet' => $fila["carnet"] // <--- AGREGAR ESTA LÍNEA
]) . "\"><button type=\"button\" class='btn-imprimir'>Imprimir</button></a>";
                    
                    // Editar
                    echo "<a href=\"/colegio_superacion/vistas/editar_pago.php?" . http_build_query([
                        'fecha_pago' => $fila["fecha_pago"], 'monto' => $fila["monto"], 'mes_pago' => $fila["mes_pago"],
                        'tipo_pago' => $fila["tipo_pago"], 'id_pago' => $fila["id_pago"], 'serie_recibo' => $fila["serie_recibo"],
                        'num_autorizacion' => $fila["num_autorizacion"], 'observacion_adicional' => $fila["observacion_adicional"],
                        'nombre' => $fila["nombre"], 'apellido' => $fila["apellido"], 'jornada' => $fila["jornada"],
                        'nivel' => $fila["nivel"], 'grado' => $fila["grado"], 'carnet' => $fila["carnet"],
                        'codigo' => $fila["codigo"], 'ciclo_escolar' => $fila["ciclo_escolar"]
                    ]) . "\"><button type=\"button\" class='btn-editar'>Editar</button></a>";

                    // Eliminar Form
                    echo "<form class=\"form-eliminar-pago-grado\" action=\"./eliminar_pago.php\" method=\"post\" style=\"display:inline-block;\">";
                    echo "<input type=\"hidden\" name=\"id_pago\" value=\"" . htmlspecialchars($fila['id_pago']) . "\">";
                    echo "<input type=\"hidden\" name=\"serie_recibo\" value=\"" . htmlspecialchars($fila['serie_recibo']) . "\">";
                    echo "<button type=\"submit\" class='btn-eliminar'>Eliminar</button>";
                    echo "</form>";
                    echo "</td>";                  
                    echo "</tr>";
                    
                    $total_dinero += $fila["monto"];
                }
                echo "</tbody></table>";
                echo "<br>";
                echo "<h3 id=\"total_pagos_grado\">Total de Pagos Recaudados: Q " . number_format($total_dinero, 2) . "</h3>";

            } else {
                echo "<p style='text-align:center; margin-top:20px;'>No se encontraron pagos para los criterios de búsqueda seleccionados.</p>";
            }
        }
        ?>
        </div> </div>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            actualizarNivel(); 

            document.getElementById("jornada").addEventListener("change", actualizarNivel); // Ensure nivel updates on jornada change
            document.getElementById("nivel").addEventListener("change", actualizarGrado);

            const formsEliminarGrado = document.querySelectorAll('.form-eliminar-pago-grado');
            formsEliminarGrado.forEach(form => {
                form.addEventListener('submit', function(event) {
                    event.preventDefault(); 

                    const serieRecibo = this.querySelector('input[name="serie_recibo"]').value;
                    const idPagoInput = this.querySelector('input[name="id_pago"]').value;
                    const idReciboCompleto = serieRecibo + " " + idPagoInput;

                    if (!confirm('¿Está seguro de que desea eliminar este recibo N° ' + idReciboCompleto + '?')) {
                        return;
                    }

                    const formData = new FormData(this);
                    const currentRow = this.closest('tr');
                    const montoCell = currentRow.querySelector('.monto-pago'); // Use class to find monto cell
                    let montoEliminado = 0;
                    if (montoCell) {
                        montoEliminado = parseFloat(montoCell.textContent.replace(/Q\s*|,/g, ''));
                    }

                    fetch(this.action, { // action is "./eliminar_pago.php"
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.url) // Get final URL after redirect
                    .then(finalUrl => {
                        const urlParams = new URLSearchParams(new URL(finalUrl).search);
                        const status = urlParams.get('status');
                        let message = '';

                        if (status === 'deleted_ok') {
                            message = 'Recibo eliminado correctamente.';
                            if (currentRow) {
                                currentRow.remove();
                            }
                            actualizarTotalPagosGrado(montoEliminado);
                            verificarTablaGradoVacia();
                        } else if (status === 'not_found') {
                            message = 'No se encontró el recibo para eliminar o ya había sido eliminado.';
                        } else if (status === 'delete_execution_error' || status === 'delete_prepare_error') {
                            message = 'Error durante la eliminación del recibo en el servidor.';
                        } else if (status === 'invalid_id' || status === 'missing_params') {
                            message = 'Error: Información para eliminación inválida o incompleta.';
                        } else {
                            message = 'Ocurrió un error inesperado durante la eliminación. Status: ' + (status || 'N/A');
                        }
                        alert(message);
                    })
                    .catch(error => {
                        console.error('Error en la solicitud AJAX:', error);
                        alert('Error de conexión al intentar eliminar el recibo.');
                    });
                });
            });

        });

        function actualizarTotalPagosGrado(montoRestar) {
            const totalPagosGradoElem = document.getElementById('total_pagos_grado');
            if (!totalPagosGradoElem) return;

            let currentTotalText = totalPagosGradoElem.textContent.replace(/Total de Pagos Recaudados: Q\s*|,/g, '');
            let currentTotal = parseFloat(currentTotalText);
            if (isNaN(currentTotal)) currentTotal = 0;

            let nuevoTotal = currentTotal - montoRestar;
            totalPagosGradoElem.textContent = "Total de Pagos Recaudados: Q " + nuevoTotal.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
        }

        function verificarTablaGradoVacia() {
            const tabla = document.getElementById('tabla_pagos_grado');
            const tbody = tabla ? tabla.querySelector('tbody') : null;
            const container = document.getElementById('historial-grado-container');
            const totalDisplay = document.getElementById('total_pagos_grado');

            const existingJsMessage = container.querySelector('.no-records-message-js');
            if (existingJsMessage) {
                existingJsMessage.remove();
            }

            if (tabla && tbody && tbody.children.length === 0) {
                tabla.style.display = 'none';
                if (totalDisplay) totalDisplay.style.display = 'none';

                if (container) {
                    const noRecordsMessage = document.createElement('p');
                    noRecordsMessage.className = 'no-records-message-js';
                    noRecordsMessage.textContent = 'No se encontraron pagos para los criterios de búsqueda seleccionados, o todos han sido eliminados.';
                    container.appendChild(noRecordsMessage);
                }
            } else if (tabla) {
                tabla.style.display = '';
                 if (totalDisplay) totalDisplay.style.display = '';
            }
        }
        
        function actualizarNivel() {
            var jornada = document.getElementById("jornada").value;
            var nivelSelect = document.getElementById("nivel");
            nivelSelect.innerHTML = "<option value='' selected>- Seleccione una Opción -</option>"; // Reset with default

            var opcionesNivel = {};
            if (jornada === "diario-matutino" || jornada === "diario-vespertino") {
                opcionesNivel = {
                    "preprimaria": "Pre-Primaria",
                    "primaria": "Primaria",
                    "basico": "Básico",
                    "diversificado3": "Bachiller en Computación con Orientacion Comercial",
                    "diversificado1": "Perito Contador con Orientacion en Computación",
                    "diversificado2": "Perito en Administración de Empresa"
                };
            } else if (jornada === "fin-de-semana") {
                opcionesNivel = {
                    "basico": "Básico",
                    "basicopormadurez": "Básico por Madurez",
                    "diversificado4": "Bachiller en Ciencias y Letras por Madurez",
                    "diversificado1": "Perito Contador con Orientacion en Computación"
                };
            }

            for (var valor in opcionesNivel) {
                if (opcionesNivel.hasOwnProperty(valor)) {
                    agregarOpcion(nivelSelect, opcionesNivel[valor], valor);
                }
            }
            actualizarGrado(); // Update grados based on new nivel list
        }

        function actualizarGrado() {
            var nivel = document.getElementById("nivel").value;
            var gradoSelect = document.getElementById("grado");
            gradoSelect.innerHTML = "<option value='' selected>- Seleccione una Opción -</option>"; // Reset with default

            var opcionesGrado = {};
            if (nivel === "preprimaria" || nivel === "basico") {
                opcionesGrado = {"1grado": "1er Grado", "2grado": "2do Grado", "3grado": "3er Grado"};
            } else if (nivel === "primaria") {
                opcionesGrado = {"1grado": "1er Grado", "2grado": "2do Grado", "3grado": "3er Grado", "4grado": "4to Grado", "5grado": "5to Grado", "6grado": "6to Grado"};
            } else if (nivel === "basicopormadurez") {
                opcionesGrado = {"1grado": "1er Grado", "2grado": "2do Grado"};
            } else if (nivel === "diversificado1" || nivel === "diversificado2") { // Perito Contador, Perito Admin
                opcionesGrado = {"4grado": "4to Grado", "5grado": "5to Grado", "6grado": "6to Grado"};
            } else if (nivel === "diversificado3") { // Bach. Computación
                opcionesGrado = {"4grado": "4to Grado", "5grado": "5to Grado"};
            } else if (nivel === "diversificado4") { // Bach. Madurez
                opcionesGrado = {"4grado": "4to Grado"};
            }
            
            for (var valor in opcionesGrado) {
                if (opcionesGrado.hasOwnProperty(valor)) {
                     agregarOpcion(gradoSelect, opcionesGrado[valor], valor);
                }
            }
        }

        function agregarOpcion(selectElement, texto, valor) {
            var opcion = document.createElement("option");
            opcion.text = texto;
            opcion.value = valor;
            selectElement.add(opcion);
        }
    </script>
</body>
</html>
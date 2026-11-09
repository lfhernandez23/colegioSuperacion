<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/inscripcionstyle.css" />
    <link rel="stylesheet" href="../css/navstyle.css" />
    <title>Buscar y Editar Inscripción</title>
    <style>
        /* Estilos para el modal */
        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgb(0, 0, 0);
            background-color: rgba(0, 0, 0, 0.4);
            padding-top: 60px;
        }

        .modal-content {
            background-color: #fefefe;
            margin: 5% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
        }

        .modal-header {
            font-size: 20px;
            font-weight: bold;
        }

        .modal-footer {
            text-align: center;
        }

        .btn {
            padding: 10px 20px;
            margin: 10px;
            cursor: pointer;
            border: none;
            background-color: #45a049;
            color: white;
            font-size: 16px;
        }

        .btn-cancel {
            background-color: red;
        }

        .btn-confirm {
            background-color: green;
        }
    </style>
  <link rel="stylesheet" href="../css/ui-standard.css">
</head>

<body>

<?php include './header.php'; ?>

    <br><br><br>
    <div class="container">
        <h2>Buscar y Editar Inscripción</h2>

        <form action="" method="get">
            <label for="carnet">Número de Carnet</label>
            <input type="text" id="carnet" name="carnet" value="<?php echo isset($carnet) ? $carnet : ''; ?>" required />

            <label for="ciclo_escolar">Ciclo Escolar Inscrito</label>
            <input type="text" id="ciclo_escolar" name="ciclo_escolar" value="<?php echo isset($ciclo_escolar) ? $ciclo_escolar : ''; ?>" required />

            <button type="submit" name="buscar">Buscar</button>
        </form>

        <?php
        // Verificar si se han enviado datos por GET
        if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET["buscar"])) {
            // Obtener el carnet y el ciclo escolar desde el formulario
            $carnet = $_GET["carnet"];
            $ciclo_escolar = $_GET["ciclo_escolar"];

            include_once "../controlador/conexion_bd.php";
            // Preparar la consulta SQL
            $consulta = "SELECT * FROM registro_estudiantes WHERE carnet LIKE CONCAT('%', ?, '%') AND ciclo_escolar = ?";

            // Preparar y ejecutar la consulta
            $sentencia = $conn->prepare($consulta);

            if ($sentencia) {
                // Vincular los parámetros y ejecutar la consulta
                $sentencia->bind_param("ss", $carnet, $ciclo_escolar);
                $sentencia->execute();

                // Obtener resultados
                $resultados = $sentencia->get_result();

                // Cerrar la sentencia
                $sentencia->close();
            } else {
                die("Error en la preparación de la consulta: " . $conexion->error);
            }

            // Cerrar la conexión
            $conn->close();

            // Mostrar resultados si están disponibles
            if (isset($resultados) && $resultados->num_rows > 0) {
                // Mostrar formulario de edición
                while ($resultados_array = $resultados->fetch_assoc()) {
                    $carnet = $carnet = isset($resultados_array['carnet']) ? $resultados_array['carnet'] : '';
                    $codigo = isset($resultados_array['codigo']) ? $resultados_array['codigo'] : '';
                    $nombres = isset($resultados_array['nombre']) ? $resultados_array['nombre'] : '';
                    $apellidos = isset($resultados_array['apellido']) ? $resultados_array['apellido'] : '';
                    $sexo_estudiante = isset($resultados_array['sexo']) ? $resultados_array['sexo'] : '';
                    $fecha_nacimiento = isset($resultados_array['fecha_nacimiento']) ? $resultados_array['fecha_nacimiento'] : '';
                    $jornada = isset($resultados_array['jornada']) ? $resultados_array['jornada'] : '';
                    $grado = isset($resultados_array['grado']) ? $resultados_array['grado'] : '';
                    $nivel = isset($resultados_array['nivel']) ? $resultados_array['nivel'] : '';
                    $ciclo_escolar = isset($resultados_array['ciclo_escolar']) ? $resultados_array['ciclo_escolar'] : '';
                    $tipo_sangre = isset($resultados_array['tipo_sangre']) ? $resultados_array['tipo_sangre'] : '';
                    $direccion_vivienda = isset($resultados_array['direccion_vivienda']) ? $resultados_array['direccion_vivienda'] : '';
                    $nombre_padre = isset($resultados_array['nombre_padre']) ? $resultados_array['nombre_padre'] : '';
                    $nombre_madre = isset($resultados_array['nombre_madre']) ? $resultados_array['nombre_madre'] : '';
                    $fecha_inscripcion = isset($resultados_array['fecha_inscripcion']) ? $resultados_array['fecha_inscripcion'] : '';
                    $info_adicional = isset($resultados_array['info_adicional']) ? $resultados_array['info_adicional'] : '';
                    $tel_alumno = isset($resultados_array['tel_alumno']) ? $resultados_array['tel_alumno'] : '';
                    $tel_encargado = isset($resultados_array['tel_encargado']) ? $resultados_array['tel_encargado'] : '';
                    $email_encargado = isset($resultados_array['email_encargado']) ? $resultados_array['email_encargado'] : '';
                    $nombre_tutor = isset($resultados_array['nombre_tutor']) ? $resultados_array['nombre_tutor'] : '';
                    $tipo_inscripcion = isset($resultados_array['tipo_inscripcion']) ? $resultados_array['tipo_inscripcion'] : '';
        ?>
                    <br>
                    <form id="editarForm" action="../controlador/consulta_actualiza_estudiante.php" method="post">

                        <label for="carnet">Número de Carnet</label>
                        <input type="text" id="carnet" name="carnet" value="<?php echo $carnet; ?>" required />

                        <label for="codigo">Código Personal del Estudiante </label>
                        <input type="text" id="codigo" name="codigo" value="<?php echo $codigo; ?>" />

                        <label for="nombres">Nombre del Estudiante</label>
                        <input type="text" id="nombres" name="nombres" value="<?php echo $nombres; ?>" required />

                        <label for="apellidos">Apellido del Estudiante </label>
                        <input type="text" id="apellidos" name="apellidos" value="<?php echo $apellidos; ?>" required />

                        <label for="sexo">Sexo </label>
                        <select id="sexo" name="sexo" required>
                            <option value="femenino" <?php echo ($sexo_estudiante === 'femenino') ? 'selected' : ''; ?>>Femenino</option>
                            <option value="masculino" <?php echo ($sexo_estudiante === 'masculino') ? 'selected' : ''; ?>>Masculino</option>
                        </select>

                        <label for="fecha-nacimiento">Fecha de Nacimiento </label>
                        <input type="date" id="fecha-nacimiento" name="fecha-nacimiento" value="<?php echo $fecha_nacimiento; ?>" required />

                        <label for="jornada">Jornada </label>
                        <select id="jornada" name="jornada" required>
                            <option value="diario-matutino" <?php echo ($jornada === 'diario-matutino') ? 'selected' : ''; ?>>Plan Diario - Matutino</option>
                            <option value="diario-vespertino" <?php echo ($jornada === 'diario-vespertino') ? 'selected' : ''; ?>>Plan Diario - Vespertino</option>
                            <option value="fin-de-semana" <?php echo ($jornada === 'fin-de-semana') ? 'selected' : ''; ?>>Plan Fin de Semana - Sabatino</option>
                        </select>
                        <script>
                            actualizargrado();
                        </script>
                        <label for="nivel">Nivel Asignado </label>
                        <select id="nivel" name="nivel" required>
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
                        <select id="grado" name="grado" required>
                            <option value="1grado" <?php echo ($grado === '1grado') ? 'selected' : ''; ?>>1er Grado</option>
                            <option value="2grado" <?php echo ($grado === '2grado') ? 'selected' : ''; ?>>2do Grado</option>
                            <option value="3grado" <?php echo ($grado === '3grado') ? 'selected' : ''; ?>>3er Grado</option>
                            <option value="4grado" <?php echo ($grado === '4grado') ? 'selected' : ''; ?>>4to Grado</option>
                            <option value="5grado" <?php echo ($grado === '5grado') ? 'selected' : ''; ?>>5to Grado</option>
                            <option value="6grado" <?php echo ($grado === '6grado') ? 'selected' : ''; ?>>6to Grado</option>
                        </select>

                        <label for="ciclo_escolar">Para el Ciclo Escolar </label>
                        <input type="text" id="ciclo_escolar" name="ciclo_escolar" value="<?php echo $ciclo_escolar; ?>" required />

                        <label for="tipo-sangre">Tipo de Sangre: </label>
                        <select id="tipo-sangre" name="tipo-sangre" required>
                            <option value="desconocido" <?php echo ($tipo_sangre === 'desconocido') ? 'selected' : ''; ?>>No brinda información</option>
                            <option value="tipoAB+" <?php echo ($tipo_sangre === 'tipoAB+') ? 'selected' : ''; ?>>AB+</option>
                            <option value="tipoAB-" <?php echo ($tipo_sangre === 'tipoAB-') ? 'selected' : ''; ?>>AB-</option>
                            <option value="tipoA+" <?php echo ($tipo_sangre === 'tipoA+') ? 'selected' : ''; ?>>A+</option>
                            <option value="tipoA-" <?php echo ($tipo_sangre === 'tipoA-') ? 'selected' : ''; ?>>A-</option>
                            <option value="tipoB+" <?php echo ($tipo_sangre === 'tipoB+') ? 'selected' : ''; ?>>B+</option>
                            <option value="tipoB-" <?php echo ($tipo_sangre === 'tipoB-') ? 'selected' : ''; ?>>B-</option>
                            <option value="tipoO+" <?php echo ($tipo_sangre === 'tipoO+') ? 'selected' : ''; ?>>O+</option>
                            <option value="tipoO-" <?php echo ($tipo_sangre === 'tipoO-') ? 'selected' : ''; ?>>O-</option>
                        </select>

                        <label for="direccion-vivienda">Dirección de Vivienda </label>
                        <input type="text" name="direccion-vivienda" id="direccion-vivienda" value="<?php echo $direccion_vivienda; ?>" required />

                        <label for="nombre-padre">Nombre del Padre </label>
                        <input type="text" name="nombre-padre" id="nombre-padre" value="<?php echo $nombre_padre; ?>" required />

                        <label for="nombre-madre">Nombre de la Madre </label>
                        <input type="text" name="nombre-madre" id="nombre-madre" value="<?php echo $nombre_madre; ?>" required />

                        <label for="fecha_inscripcion">Fecha de Inscripción </label>
                        <input type="date" id="fecha_inscripcion" name="fecha_inscripcion" value="<?php echo $fecha_inscripcion; ?>" readonly />

                        <label for="info-adicional">Observaciones Adicionales </label>
                        <input type="text" id="info-adicional" name="info-adicional" value="<?php echo $info_adicional; ?>" />

                        <label for="tel-alumno">Teléfono del Alumno </label>
                        <input type="text" id="tel-alumno" name="tel-alumno" value="<?php echo $tel_alumno; ?>" />

                        <label for="tel-encargado">Teléfono del Encargado </label>
                        <input type="text" id="tel-encargado" name="tel-encargado" value="<?php echo $tel_encargado; ?>" />

                        <label for="email-encargado">Correo Electrónico del Encargado </label>
                        <input type="email" id="email-encargado" name="email-encargado" value="<?php echo $email_encargado; ?>" />

                        <label for="nombre-tutor">Nombre del Tutor (Si aplica): </label>
                        <input type="text" id="nombre-tutor" name="nombre-tutor" value="<?php echo $nombre_tutor; ?>" />

                        <label for="tipo-inscripcion">Tipo de Inscripción: </label>
                        <select id="tipo-inscripcion" name="tipo-inscripcion" required>
                            <option value="ordinaria" <?php echo ($tipo_inscripcion === 'ordinaria') ? 'selected' : ''; ?>>Ordinaria</option>
                            <option value="extraordinaria" <?php echo ($tipo_inscripcion === 'extraordinaria') ? 'selected' : ''; ?>>Extraordinaria</option>
                        </select>

                        <button type="button" id="editButton" class="btn btn-confirm">Actualizar Inscripción</button>
                        <?php echo '<td><a href="../controlador/imprime_inscripcion.php?carnet=' . urlencode($carnet) . '&codigo=' . urlencode($codigo) . '&nombres=' . urlencode($nombres) . '&apellidos=' . urlencode($apellidos) . '&sexo_estudiante=' . urlencode($sexo_estudiante) . '&fecha_nacimiento=' . urlencode($fecha_nacimiento) . '&jornada=' . urlencode($jornada) . '&grado=' . urlencode($grado) . '&nivel=' . urlencode($nivel) . '&tipo_sangre=' . urlencode($tipo_sangre) . '&direccion_vivienda=' . urlencode($direccion_vivienda) . '&nombre_padre=' . urlencode($nombre_padre) . '&nombre_madre=' . urlencode($nombre_madre) . '&fecha_inscripcion=' . urlencode($fecha_inscripcion) . '&info_adicional=' . urlencode($info_adicional) . '&tel_alumno=' . urlencode($tel_alumno) . '&tel_encargado=' . urlencode($tel_encargado) . '&email_encargado=' . urlencode($email_encargado) . '&nombre_tutor=' . urlencode($nombre_tutor) . '&tipo_inscripcion=' . urlencode($tipo_inscripcion) . '"><button type="button" style="width: 600px;">Imprimir Inscripción</button></a></td>'; ?>

                    </form>
                    <!-- Modal structure -->
                    <div id="myModal" class="modal">
                        <div class="modal-content">
                            <span class="close">&times;</span>
                            <div class="modal-header">
                                <h2>Confirmar Edición</h2>
                            </div>
                            <div class="modal-footer">
                                <button id="confirmBtn" class="btn btn-confirm">Confirmar</button>
                                <button class="btn btn-cancel">Cancelar</button>
                            </div>
                        </div>
                    </div>


                    <script>
                        // Get the modal and buttons
                        var modal = document.getElementById("myModal");
                        var btn = document.getElementById("editButton"); // Assuming you have an 'editButton' to trigger the modal
                        var span = document.getElementsByClassName("close")[0]; // Assuming you have a close button in the modal
                        var confirmBtn = document.getElementById("confirmBtn");

                        // When the user clicks the button, open the modal
                        btn.onclick = function() {
                            modal.style.display = "block";
                        }

                        // When the user clicks on <span> (x), close the modal
                        span.onclick = function() {
                            modal.style.display = "none";
                        }

                        // When the user clicks the confirm button, submit the form
                        confirmBtn.onclick = function() {
                            document.getElementById("editarForm").submit(); // Submitting the form when confirmed
                            modal.style.display = "none"; // Close the modal
                        }

                        // When the user clicks anywhere outside the modal, close it
                        window.onclick = function(event) {
                            if (event.target == modal) {
                                modal.style.display = "none";
                            }
                        }
                    </script>
        <?php
                }
            } else {
                // Mostrar mensaje si no se encontraron resultados
                echo "No se encontraron resultados para el carnet $carnet en el ciclo escolar $ciclo_escolar.";
            }
        }
        ?>
    </div>
</body>

</html>
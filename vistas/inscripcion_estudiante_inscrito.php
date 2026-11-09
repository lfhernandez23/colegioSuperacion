<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/inscripcionstyle.css" />
    <link rel="stylesheet" href="../css/navstyle.css" />
    <title>Formulario de Inscripción en Otro Ciclo</title>
  <link rel="stylesheet" href="../css/ui-standard.css">
</head>

<body>

<?php include './header.php'; ?>

    <br><br><br>
    <div class="container">
        <h2>Formulario de Inscripción en Otro Ciclo</h2>
        <form action="" method="get">
            <label for="carnet">Número de Carnet</label>
            <input type="text" id="carnet" name="carnet" value="<?php echo isset($carnet) ? $carnet : ''; ?>" required />

            <label for="ciclo_escolar">Ciclo Escolar Anterior del Estudiante</label>
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
                    $tipo_sangre = isset($resultados_array['tipo_sangre']) ? $resultados_array['tipo_sangre'] : '';
                    $direccion_vivienda = isset($resultados_array['direccion_vivienda']) ? $resultados_array['direccion_vivienda'] : '';
                    $nombre_padre = isset($resultados_array['nombre_padre']) ? $resultados_array['nombre_padre'] : '';
                    $nombre_madre = isset($resultados_array['nombre_madre']) ? $resultados_array['nombre_madre'] : '';
                    $fecha_inscripcion = isset($resultados_array['fecha_inscripcion']) ? $resultados_array['fecha_inscripcion'] : '';                    
                    $tel_alumno = isset($resultados_array['tel_alumno']) ? $resultados_array['tel_alumno'] : '';
                    $tel_encargado = isset($resultados_array['tel_encargado']) ? $resultados_array['tel_encargado'] : '';
                    $email_encargado = isset($resultados_array['email_encargado']) ? $resultados_array['email_encargado'] : '';
                    $nombre_tutor = isset($resultados_array['nombre_tutor']) ? $resultados_array['nombre_tutor'] : '';
                    $tipo_inscripcion = isset($resultados_array['tipo_inscripcion']) ? $resultados_array['tipo_inscripcion'] : '';
        ?>
                    <br>
                    <form id="inscriptionForm" action="../controlador/consulta_inscripcion_estudiante.php" method="post">

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

                        <label for="jornada">Jornada</label>
                        <select id="jornada" name="jornada" required onchange="actualizarNivel()">
                            <option value="noselecciono" selected>- Selecione una Opcion</option>
                            <option value="diario-matutino">Plan Diario - Matutino</option>
                            <option value="diario-vespertino">Plan Diario - Vespertino</option>
                            <option value="fin-de-semana">Plan Fin de Semana - Sabatino</option>
                        </select>

                        <label for="nivel">Nivel Asignado</label>
                        <select id="nivel" name="nivel" required>
                            <!-- Opciones inicialmente vacías -->
                        </select>

                        <label for="grado">Grado Asignado</label>
                        <select id="grado" name="grado" required>
                            <!-- Opciones inicialmente vacías -->
                        </select>

                        <label for="ciclo_escolar">Para el Ciclo Escolar </label>
                        <input type="text" id="ciclo_escolar" name="ciclo_escolar" required />


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
                        <input type="date" id="fecha_inscripcion" name="fecha_inscripcion" value="<?php echo $fecha_inscripcion; ?>" />

                        <label for="info-adicional">Observaciones Adicionales </label>
                        <input type="text" id="info-adicional" name="info-adicional" />

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

                        <button type="submit">Enviar Inscripción</button>
                        <button type="button" style="background-color: red; color: white; border: none; padding: 10px 20px; cursor: pointer;" onclick="window.location.href='../vistas/registro_estudiante.php'">Cancelar Inscripción</button>
                    </form>
        <?php
                }
            } else {
                // Mostrar mensaje si no se encontraron resultados
                echo "No se encontraron resultados para el carnet $carnet en el ciclo escolar $ciclo_escolar.";
            }
        }
        ?>
    </div>
    <script src="../js/obtener_fecha.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            actualizarNivel(); // Llamar a la función una vez que se cargue la página

            // Asegúrate de actualizar el grado cuando el nivel cambie
            document.getElementById("nivel").addEventListener("change", actualizarGrado);
        });

        function actualizarNivel() {
            var jornada = document.getElementById("jornada").value;
            var nivelSelect = document.getElementById("nivel");

            // Eliminar opciones anteriores
            nivelSelect.innerHTML = "";

            // Añadir opciones según la jornada seleccionada
            if (jornada === "diario-matutino") {
                agregarOpcionNivel("- Seleccione una Opcion - ", "noselecciono", nivelSelect);
                agregarOpcionNivel("Pre-Primaria", "preprimaria", nivelSelect);
                agregarOpcionNivel("Primaria", "primaria", nivelSelect);
                agregarOpcionNivel("Básico", "basico", nivelSelect);
                agregarOpcionNivel("Bachiller en Computación con Orientacion Comercial", "diversificado3", nivelSelect);
                agregarOpcionNivel("Perito Contador con Orientacion en Computación", "diversificado1", nivelSelect);
                agregarOpcionNivel("Perito en Administración de Empresa", "diversificado2", nivelSelect);
            } else if (jornada === "diario-vespertino") {
                agregarOpcionNivel("- Seleccione una Opcion - ", "noselecciono", nivelSelect);
                agregarOpcionNivel("Pre-Primaria", "preprimaria", nivelSelect);
                agregarOpcionNivel("Primaria", "primaria", nivelSelect);
                agregarOpcionNivel("Básico", "basico", nivelSelect);
                agregarOpcionNivel("Bachiller en Computación con Orientacion Comercial", "diversificado3", nivelSelect);
                agregarOpcionNivel("Perito Contador con Orientacion en Computación", "diversificado1", nivelSelect);
                agregarOpcionNivel("Perito en Administración de Empresa", "diversificado2", nivelSelect);
            } else if (jornada === "fin-de-semana") {
                agregarOpcionNivel("- Seleccione una Opcion - ", "noselecciono", nivelSelect);
                agregarOpcionNivel("Básico", "basico", nivelSelect);
                agregarOpcionNivel("Básico por Madurez", "basicopormadurez", nivelSelect);
                agregarOpcionNivel("Bachiller en Ciencias y Letra por Madurez", "diversificado4", nivelSelect);
                agregarOpcionNivel("Perito Contador con Orientacion en Computación", "diversificado1", nivelSelect);
            }
        }

        function agregarOpcionNivel(texto, valor, select) {
            var opcion = document.createElement("option");
            opcion.text = texto;
            opcion.value = valor;
            select.add(opcion);
        }

        function actualizarGrado() {
            var nivel = document.getElementById("nivel").value;
            var gradoSelect = document.getElementById("grado");

            // Eliminar opciones anteriores
            gradoSelect.innerHTML = "";

            // Ajuste de niveles por grado  
            if (nivel === "preprimaria") {
                agregarOpcionGrado("1er Grado", "1grado", gradoSelect);
                agregarOpcionGrado("2do Grado", "2grado", gradoSelect);
                agregarOpcionGrado("3er Grado", "3grado", gradoSelect);
            } else if (nivel === "primaria") {
                agregarOpcionGrado("1er Grado", "1grado", gradoSelect);
                agregarOpcionGrado("2do Grado", "2grado", gradoSelect);
                agregarOpcionGrado("3er Grado", "3grado", gradoSelect);
                agregarOpcionGrado("4to Grado", "4grado", gradoSelect);
                agregarOpcionGrado("5to Grado", "5grado", gradoSelect);
                agregarOpcionGrado("6to Grado", "6grado", gradoSelect);
            } else if (nivel === "basico") {
                agregarOpcionGrado("1er Grado", "1grado", gradoSelect);
                agregarOpcionGrado("2do Grado", "2grado", gradoSelect);
                agregarOpcionGrado("3er Grado", "3grado", gradoSelect);
            } else if (nivel === "basicopormadurez") {
                agregarOpcionGrado("1er Grado", "1grado", gradoSelect);
                agregarOpcionGrado("2do Grado", "2grado", gradoSelect);
            } else if (nivel === "diversificado1") {
                agregarOpcionGrado("4to Grado", "4grado", gradoSelect);
                agregarOpcionGrado("5to Grado", "5grado", gradoSelect);
                agregarOpcionGrado("6to Grado", "6grado", gradoSelect);
            } else if (nivel === "diversificado2") {
                agregarOpcionGrado("4to Grado", "4grado", gradoSelect);
                agregarOpcionGrado("5to Grado", "5grado", gradoSelect);
                agregarOpcionGrado("6to Grado", "6grado", gradoSelect);
            } else if (nivel === "diversificado3") {
                agregarOpcionGrado("4to Grado", "4grado", gradoSelect);
                agregarOpcionGrado("5to Grado", "5grado", gradoSelect);
            } else if (nivel === "diversificado4") {
                agregarOpcionGrado("4to Grado", "4grado", gradoSelect);;
            }
        }

        function agregarOpcionGrado(texto, valor, select) {
            var opcion = document.createElement("option");
            opcion.text = texto;
            opcion.value = valor;
            select.add(opcion); // Corrige el error tipográfico aquí
        }
    </script>
</body>

</html>
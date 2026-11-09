<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/inscripcionstyle.css" />
    <link rel="stylesheet" href="../css/navstyle.css" />
    <title>Formulario de Creación de Cursos</title>
</head>

<body>

<?php include './header.php'; ?>

    <br><br><br>
    <div class="container">
        <?php
        $jornada = isset($_GET['jornada']) ? $_GET['jornada'] : '';
        $nivel = isset($_GET['nivel']) ? $_GET['nivel'] : '';
        $grado = isset($_GET['grado']) ? $_GET['grado'] : '';
        ?>


        <h2>Formulario de Creación de Cursos</h2>
        <form id="inscriptionForm" action="../controlador/consulta_crea_nuevo_curso.php" method="post">

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
                <option value="preprimaria" <?php echo ($nivel === 'preprimaria' || isset($_GET['nivel']) && $_GET['nivel'] === 'preprimaria') ? 'selected' : ''; ?>>Pre-Primaria</option>
                <option value="primaria" <?php echo ($nivel === 'primaria' || isset($_GET['nivel']) && $_GET['nivel'] === 'primaria') ? 'selected' : ''; ?>>Primaria</option>
                <option value="basico" <?php echo ($nivel === 'basico' || isset($_GET['nivel']) && $_GET['nivel'] === 'basico') ? 'selected' : ''; ?>>Básico</option>
                <option value="basicopormadurez" <?php echo ($nivel === 'basicopormadurez' || isset($_GET['nivel']) && $_GET['nivel'] === 'basicopormadurez') ? 'selected' : ''; ?>>Básico por Madurez</option>
                <option value="diversificado1" <?php echo ($nivel === 'diversificado1' || isset($_GET['nivel']) && $_GET['nivel'] === 'diversificado1') ? 'selected' : ''; ?>>Perito Contador con Orientación en Computación</option>
                <option value="diversificado2" <?php echo ($nivel === 'diversificado2' || isset($_GET['nivel']) && $_GET['nivel'] === 'diversificado2') ? 'selected' : ''; ?>>Perito en Administración de Empresa</option>
                <option value="diversificado3" <?php echo ($nivel === 'diversificado3' || isset($_GET['nivel']) && $_GET['nivel'] === 'diversificado3') ? 'selected' : ''; ?>>Bachiller en Computación con Orientación Comercial</option>
                <option value="diversificado4" <?php echo ($nivel === 'diversificado4' || isset($_GET['nivel']) && $_GET['nivel'] === 'diversificado4') ? 'selected' : ''; ?>>Bachiller en Ciencias y Letra por Madurez</option>
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

            <label for="curso">Nombre del Curso</label>
            <input type="text" id="curso" name="curso" />

            <!-- Botón de Enviar Formulario -->
            <button type="submit">Crear Curso Nuevo</button>
        </form>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Actualizamos el nivel tan pronto como la página cargue
            actualizarNivel();

            // Asegurémonos de que el grado se actualice cuando el nivel cambie
            document.getElementById("nivel").addEventListener("change", actualizarGrado);
        });

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
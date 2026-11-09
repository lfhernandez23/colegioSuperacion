<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/inscripcionstyle.css" />
    <link rel="stylesheet" href="../css/navstyle.css" />
    <title>Formulario de Creación de Cursos</title>
  <link rel="stylesheet" href="../css/ui-standard.css">
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

    <script defer src="../js/selectores-academicos.js"></script>
</body>

</html>
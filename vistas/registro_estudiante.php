<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="../css/inscripcionstyle.css" />
  <link rel="stylesheet" href="../css/navstyle.css" />
  <title>Formulario de Inscripcion</title>
</head>

<body>

<?php include './header.php'; ?>


  <br><br><br>
  <div class="container">
    <h2>Formulario de Inscripción</h2>
    <form id="inscriptionForm" action="../controlador/consulta_inscripcion_estudiante.php" method="post">
      <!-- Campos del formulario -->
      <!-- Botón que abre el modal -->
      <button id="btnAbrirModal" type="button">Buscar Estudiante Ya Inscrito</button>

      <!-- Modal de confirmación -->
      <div id="modalConfirmacion" class="modal" style="display: none;">
        <div class="modal-content">
          <h2>¿Está seguro de que quiere inscribir al estudiante en otro ciclo?</h2>
          <button id="confirmarInscripcion" type="button">Sí, estoy seguro</button>
          <button id="cancelarInscripcion" type="button">Cancelar</button>
        </div>
      </div>

      <script>
        // Obtener elementos del DOM
        var modalConfirmacion = document.getElementById("modalConfirmacion");
        var btnAbrirModal = document.getElementById("btnAbrirModal");
        var btnConfirmar = document.getElementById("confirmarInscripcion");
        var btnCancelar = document.getElementById("cancelarInscripcion");

        // Abrir el modal al hacer clic en el botón
        btnAbrirModal.onclick = function() {
          modalConfirmacion.style.display = "block";
        }

        // Redirigir al hacer clic en "Sí, estoy seguro"
        btnConfirmar.onclick = function() {
          window.location.href = '../vistas/inscripcion_estudiante_inscrito.php';
        }

        // Cerrar el modal al hacer clic en "Cancelar"
        btnCancelar.onclick = function() {
          modalConfirmacion.style.display = "none";
        }

        // Cerrar el modal si se hace clic fuera del contenido del modal
        window.onclick = function(event) {
          if (event.target == modalConfirmacion) {
            modalConfirmacion.style.display = "none";
          }
        }
      </script>

      <style>
        /* Estilos del modal */
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
        }

        .modal-content {
          background-color: white;
          margin: 15% auto;
          padding: 20px;
          border: 1px solid #888;
          width: 300px;
          text-align: center;
        }

        button {
          padding: 10px 20px;
          margin: 5px;
        }

        /* Estilo para el botón de cancelar */
        #cancelarInscripcion {
          background-color: red;
          color: white;
          border: none;
          cursor: pointer;
        }

        /* Otros botones, como el de confirmar */
        #confirmarInscripcion {
          background-color: green;
          color: white;
          border: none;
          cursor: pointer;
        }
      </style>



      <label for="carnet">Número de Carnet </label>
      <input type="text" id="carnet" name="carnet" required />

      <label for="codigo">Código Personal del Estudiante </label>
      <input type="text" id="codigo" name="codigo" />

      <label for="nombres">Nombre del Estudiante </label>
      <input type="text" id="nombres" name="nombres" required />

      <label for="apellidos">Apellido del Estudiante </label>
      <input type="text" id="apellidos" name="apellidos" required />

      <label for="sexo">Sexo </label>
      <select id="sexo" name="sexo" required>
        <option value="femenino">Femenino</option>
        <option value="masculino">Masculino</option>
      </select>

      <label for="fecha-nacimiento">Fecha de Nacimiento </label>
      <input type="date" id="fecha-nacimiento" name="fecha-nacimiento" required />

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
      <select id="tipo-sangre" name="tipo-sangre">
        <option value="desconocido">No brinda información</option>
        <option value="tipoAB+">AB+</option>
        <option value="tipoAB-">AB-</option>
        <option value="tipoA+">A+</option>
        <option value="tipoA-">A-</option>
        <option value="tipoB+">B+</option>
        <option value="tipoB-">B-</option>
        <option value="tipoO+">O+</option>
        <option value="tipoO-">O-</option>
      </select>

      <label for="direccion-vivienda">Direccion de Vivienda </label>
      <input type="text" name="direccion-vivienda" id="direccion-vivienda" required />

      <label for="nombre-padre">Nombre del Padre </label>
      <input type="text" name="nombre-padre" id="nombre-padre" required />

      <label for="nombre-madre">Nombre de la Madre </label>
      <input type="text" name="nombre-madre" id="nombre-madre" required />

      <label for="fecha_inscripcion">Fecha de Inscripción </label>
      <input type="date" id="fecha_inscripcion" name="fecha_inscripcion" readonly />

      <label for="info-adicional">Observaciones Adicionales </label>
      <input type="text" id="info-adicional" name="info-adicional" />

      <label for="tel-alumno">Teléfono del Alumno </label>
      <input type="text" id="tel-alumno" name="tel-alumno" />

      <label for="tel-encargado">Télefono del Encargado </label>
      <input type="text" id="tel-encargado" name="tel-encargado" />

      <label for="email-encargado">Correo Electrónico del Encargado </label>
      <input type="email" id="email-encargado" name="email-encargado" />

      <label for="nombre-tutor">Nombre del Tutor (Si aplicará): </label>
      <input type="text" id="nombre-tutor" name="nombre-tutor" />

      <label for="tipo-inscripcion">Tipo de Inscripción: </label>
      <select id="tipo-inscripcion" name="tipo-inscripcion">
        <option value="ordinaria">Ordinaria</option>
        <option value="extraordinaria">Extraordinaria</option>
      </select>

      <!-- Botón de Enviar Formulario -->
      <button type="submit">Enviar Inscripción</button>
    </form>
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
document.addEventListener("DOMContentLoaded", function () {
  // Obtener la fecha actual
  var fechaActual = new Date();

  // Obtener la zona horaria local del sistema
  var zonaHorariaLocal = fechaActual.getTimezoneOffset();

  // Ajustar la fecha a la zona horaria local
  fechaActual.setMinutes(fechaActual.getMinutes() - zonaHorariaLocal);

  // Formatear la fecha como "YYYY-MM-DD" para asignarla al campo de entrada de fecha
  var formatoFecha = fechaActual.toISOString().slice(0, 10);

  // Asignar la fecha al campo de entrada de fecha
  let etiquetaFecha = document.getElementById("fecha_inscripcion");
  etiquetaFecha.value = formatoFecha;
});

let botonLimpiar = document.getElementById("limpiar-formulario");
botonLimpiar.addEventListener("click", limpiarFormulario);

function limpiarFormulario() {
  let inputCarnet = document.getElementById("carnet");
  let inputCodigo = document.getElementById("codigo");
  let inputNombre = document.getElementById("nombres");
  let inputApellido = document.getElementById("apellidos");
  let inputSexo = document.getElementById("sexo");
  let inputFechaNacimiento = document.getElementById("fecha-nacimiento");
  let inputJornada = document.getElementById("jornada");
  let inputGrado = document.getElementById("grado");
  let inputNivel = document.getElementById("nivel");
  let inputTipoSangre = document.getElementById("tipo-sangre");
  let inputDireccionVivienda = document.getElementById("direccion-vivienda");
  let inputNombrePadre = document.getElementById("nombre-padre");
  let inputNombreMadre = document.getElementById("nombre-madre");
  let inputFechaInscripcion = document.getElementById("fecha_inscripcion");
  let inputInfoAdicional = document.getElementById("info-adicional");
  let inputTelAlumno = document.getElementById("tel-alumno");
  let inputTelEncargado = document.getElementById("tel-encargado");
  let inputEmailEncargado = document.getElementById("email-encargado");
  let inputNombreTutor = document.getElementById("nombre-tutor");
  let inputTipoInscripcion = document.getElementById("tipo-inscripcion");

  inputCarnet.value = "";
  inputCodigo.value = "";
  inputNombre.value = "";
  inputApellido.value = "";
  inputSexo.value = "Femenino";
  inputFechaNacimiento.value = inputJornada.value = "Plan Diario - Matutino";
  inputGrado.value = "Pre-Primaria";
  inputNivel.value = "1er Nivel";
  inputTipoSangre.value = "No brinda información";
  inputDireccionVivienda.value = "";
  inputNombrePadre.value = "";
  inputNombreMadre.value = "";
  inputFechaInscripcion.value = etiquetaFecha;
  inputInfoAdicional.value = "";
  inputTelAlumno.value = "";
  inputTelEncargado.value = "";
  inputEmailEncargado.value = "";
  inputNombreTutor.value = "";
  inputTipoInscripcion.value = "Ordinaria";
}

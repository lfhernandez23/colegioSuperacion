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


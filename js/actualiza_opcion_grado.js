document.addEventListener("DOMContentLoaded", function () {
  actualizarGrado(); // Llamar a la función una vez que se cargue la página
  actualizarNivel(); // Llamar a la función una vez que se cargue la página

  // Asegúrate de actualizar el nivel cuando el grado cambie
  document.getElementById("grado").addEventListener("change", actualizarNivel);
});

function actualizarGrado() {
  var jornada = document.getElementById("jornada").value;
  var gradoSelect = document.getElementById("grado");

  // Eliminar opciones anteriores
  gradoSelect.innerHTML = "";

  // Añadir opciones según la jornada seleccionada
  if (jornada === "diario-matutino") {
    agregarOpcionGrado("Selecciona una Opcion", "noselecciono", gradoSelect);
    agregarOpcionGrado("Pre-Primaria", "preprimaria", gradoSelect);
    agregarOpcionGrado("Primaria", "primaria", gradoSelect);
    agregarOpcionGrado("Básico", "basico", gradoSelect);
    agregarOpcionGrado("Bachiller en Computación con Orientacion Comercial", "diversificado3", gradoSelect);
    agregarOpcionGrado("Perito Contador con Orientacion en Computación", "diversificado1", gradoSelect);    
    agregarOpcionGrado("Perito en Administración de Empresa", "diversificado2", gradoSelect);
  } else if (jornada === "diario-vespertino") {
    agregarOpcionGrado("Selecciona una Opcion", "noselecciono", gradoSelect);
    agregarOpcionGrado("Pre-Primaria", "preprimaria", gradoSelect);
    agregarOpcionGrado("Primaria", "primaria", gradoSelect);
    agregarOpcionGrado("Básico", "basico", gradoSelect);
    agregarOpcionGrado("Bachiller en Computación con Orientacion Comercial", "diversificado3", gradoSelect);
    agregarOpcionGrado("Perito Contador con Orientacion en Computación", "diversificado1", gradoSelect);    
    agregarOpcionGrado("Perito en Administración de Empresa", "diversificado2", gradoSelect);
  }
    else if (jornada === "fin-de-semana") {
      agregarOpcionGrado("Selecciona una Opcion", "noselecciono", gradoSelect);
      agregarOpcionGrado("Básico", "basico", gradoSelect);
      agregarOpcionGrado("Básico por Madurez", "basicopormadurez", gradoSelect);
      agregarOpcionGrado("Bachiller en Ciencias y Letra por Madurez", "diversificado4", gradoSelect);
      agregarOpcionGrado("Perito Contador con Orientacion en Computación", "diversificado1", gradoSelect);          
    }
}

function agregarOpcionGrado(texto, valor, select) {
  var opcion = document.createElement("option");
  opcion.text = texto;
  opcion.value = valor;
  select.add(opcion);
}

function actualizarNivel() {
  var grado = document.getElementById("grado").value;
  var nivelSelect = document.getElementById("nivel");

  
  if (!nivelSelect) return; // Salir si no existe el elemento nivelSelect

  // Eliminar opciones anteriores
  nivelSelect.innerHTML = "";

  // Ajuste de niveles por grado  
  if (grado === "preprimaria") {
    agregarOpcionNivel("Selecciona una Opcion", "noselecciono", nivelSelect);
    agregarOpcionNivel("1er Nivel", "nivel1", nivelSelect);
    agregarOpcionNivel("2do Nivel", "nivel2", nivelSelect);
    agregarOpcionNivel("3er Nivel", "nivel3", nivelSelect);
    // Agregar más opciones según sea necesario
  } else if (grado === "primaria"){
    agregarOpcionNivel("Selecciona una Opcion", "noselecciono", nivelSelect);
    agregarOpcionNivel("1er Nivel", "nivel1", nivelSelect);
    agregarOpcionNivel("2do Nivel", "nivel2", nivelSelect);
    agregarOpcionNivel("3er Nivel", "nivel3", nivelSelect);
    agregarOpcionNivel("4to Nivel", "nivel4", nivelSelect);
    agregarOpcionNivel("5to Nivel", "nivel5", nivelSelect);
    agregarOpcionNivel("6to Nivel", "nivel6", nivelSelect);
  } else if (grado === "basico"){
    agregarOpcionNivel("Selecciona una Opcion", "noselecciono", nivelSelect);
    agregarOpcionNivel("1er Nivel", "nivel1", nivelSelect);
    agregarOpcionNivel("2do Nivel", "nivel2", nivelSelect);
    agregarOpcionNivel("3er Nivel", "nivel3", nivelSelect);
  } else if (grado === "basicopormadurez"){
    agregarOpcionNivel("Selecciona una Opcion", "noselecciono", nivelSelect);
    agregarOpcionNivel("1er Nivel", "nivel1", nivelSelect);
    agregarOpcionNivel("2do Nivel", "nivel2", nivelSelect);    
  } else if (grado === "diversificado1"){
    agregarOpcionNivel("Selecciona una Opcion", "noselecciono", nivelSelect);
    agregarOpcionNivel("4to Nivel", "nivel4", nivelSelect);
    agregarOpcionNivel("5to Nivel", "nivel5", nivelSelect);  
    agregarOpcionNivel("6to Nivel", "nivel6", nivelSelect); 
  }  else if (grado === "diversificado2"){
    agregarOpcionNivel("Selecciona una Opcion", "noselecciono", nivelSelect);
    agregarOpcionNivel("4to Nivel", "nivel4", nivelSelect);
    agregarOpcionNivel("5to Nivel", "nivel5", nivelSelect);  
    agregarOpcionNivel("6to Nivel", "nivel6", nivelSelect);    
  }  else if (grado === "diversificado3"){
    agregarOpcionNivel("Selecciona una Opcion", "noselecciono", nivelSelect);
    agregarOpcionNivel("4to Nivel", "nivel4", nivelSelect);
    agregarOpcionNivel("5to Nivel", "nivel5", nivelSelect);             
  } else if (grado === "diversificado4"){
    agregarOpcionNivel("Selecciona una Opcion", "noselecciono", nivelSelect);
    agregarOpcionNivel("4to Nivel", "nivel4", nivelSelect);    
  }
  
  // Añadir más condiciones según otros grados si es necesario
}

function agregarOpcionNivel(texto, valor, select) {
  var opcion = document.createElement("option");
  opcion.text = texto;
  opcion.value = valor;
  select.add(opcion); // Corrige el error tipográfico aquí
}

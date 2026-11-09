
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

  document.getElementById("jornada").onchange = function() {
    actualizarGrado();
  };
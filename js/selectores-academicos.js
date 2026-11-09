/* Selectores compartidos Jornada -> Nivel -> Grado.
   Mantiene los valores de formulario usados por los controladores PHP. */
(() => {
  "use strict";

  const levels = [
    ["preprimaria", "Pre-Primaria"],
    ["primaria", "Primaria"],
    ["basico", "Básico"],
    ["diversificado3", "Bachiller en Computación con Orientación Comercial"],
    ["diversificado1", "Perito Contador con Orientación en Computación"],
    ["diversificado2", "Perito en Administración de Empresa"]
  ];
  const weekendLevels = [
    ["basico", "Básico"],
    ["basicopormadurez", "Básico por Madurez"],
    ["diversificado4", "Bachiller en Ciencias y Letras por Madurez"],
    ["diversificado1", "Perito Contador con Orientación en Computación"]
  ];
  const grades = {
    preprimaria: [1, 2, 3],
    primaria: [1, 2, 3, 4, 5, 6],
    basico: [1, 2, 3],
    basicopormadurez: [1, 2],
    diversificado1: [4, 5, 6],
    diversificado2: [4, 5, 6],
    diversificado3: [4, 5],
    diversificado4: [4]
  };

  function renderOptions(select, choices, previousValue, placeholder) {
    select.replaceChildren();
    select.add(new Option(placeholder, "", true, false));
    choices.forEach(([value, label]) => select.add(new Option(label, value)));
    select.value = choices.some(([value]) => value === previousValue) ? previousValue : "";
  }

  document.addEventListener("DOMContentLoaded", () => {
    const form = document.getElementById("inscriptionForm");
    if (!form) return;
    const jornada = form.querySelector("#jornada");
    const nivel = form.querySelector("#nivel");
    const grado = form.querySelector("#grado");
    if (!jornada || !nivel || !grado) return;

    function refreshGrade(preserve = true) {
      const previous = preserve ? grado.value : "";
      const allowed = grades[nivel.value] || [];
      renderOptions(grado, allowed.map((number) => [
        number + "grado",
        number + "° Grado"
      ]), previous, "Seleccione un grado");
    }

    function refreshLevel(preserve = true) {
      const previous = preserve ? nivel.value : "";
      const available = jornada.value === "fin-de-semana" ? weekendLevels :
        (jornada.value === "diario-matutino" || jornada.value === "diario-vespertino" ? levels : []);
      renderOptions(nivel, available, previous, "Seleccione un nivel");
      refreshGrade(preserve);
    }

    jornada.addEventListener("change", () => refreshLevel(false));
    nivel.addEventListener("change", () => refreshGrade(false));
    refreshLevel(true);
  });
})();

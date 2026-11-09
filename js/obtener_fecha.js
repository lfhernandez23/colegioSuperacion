/* Establece la fecha local solamente en formularios que aún no tienen fecha. */
document.addEventListener("DOMContentLoaded", () => {
  const field = document.getElementById("fecha_inscripcion");
  if (!field || field.value) return;
  const today = new Date();
  const y = today.getFullYear();
  const m = String(today.getMonth() + 1).padStart(2, "0");
  const d = String(today.getDate()).padStart(2, "0");
  field.value = `${y}-${m}-${d}`;
});

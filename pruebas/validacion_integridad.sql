-- Consultas de auditoría de datos para ejecutar en una COPIA de colegio_superacion.
-- Solo SELECT: no modifica información.
-- Nota: estas consultas señalan datos que deben revisarse, no autorizan borrados.
USE colegio_superacion;

-- 1) Registros por tabla
SELECT 'registro_estudiantes' AS tabla, COUNT(*) AS registros FROM registro_estudiantes
UNION ALL SELECT 'control_pagos', COUNT(*) FROM control_pagos
UNION ALL SELECT 'cursos', COUNT(*) FROM cursos
UNION ALL SELECT 'asignacion_cursos', COUNT(*) FROM asignacion_cursos
UNION ALL SELECT 'calificaciones', COUNT(*) FROM calificaciones
UNION ALL SELECT 'carnet_estudiante', COUNT(*) FROM carnet_estudiante
UNION ALL SELECT 'usuarios', COUNT(*) FROM usuarios;

-- 2) Carné + ciclo repetidos. No deduplicar automáticamente.
SELECT carnet, ciclo_escolar, COUNT(*) AS cantidad
FROM registro_estudiantes
GROUP BY carnet, ciclo_escolar HAVING COUNT(*) > 1;

-- 3) Pagos sin coincidencia de inscripción
SELECT COUNT(*) AS pagos_sin_inscripcion
FROM control_pagos cp WHERE NOT EXISTS (
  SELECT 1 FROM registro_estudiantes re
  WHERE re.carnet = cp.carnet AND re.ciclo_escolar = cp.ciclo_escolar
);

-- 4) Asignaciones y notas con referencias ausentes (no deberían existir si se respetan las FK)
SELECT COUNT(*) AS asignaciones_sin_estudiante
FROM asignacion_cursos ac LEFT JOIN registro_estudiantes re ON ac.id_estudiante=re.id WHERE re.id IS NULL;
SELECT COUNT(*) AS asignaciones_sin_curso
FROM asignacion_cursos ac LEFT JOIN cursos c ON ac.id_curso=c.id WHERE c.id IS NULL;
SELECT COUNT(*) AS notas_sin_estudiante
FROM calificaciones cal LEFT JOIN registro_estudiantes re ON cal.id_estudiante=re.id WHERE re.id IS NULL;
SELECT COUNT(*) AS notas_sin_curso
FROM calificaciones cal LEFT JOIN cursos c ON cal.id_curso=c.id WHERE c.id IS NULL;

-- 5) Notas registradas sin la asignación de curso correspondiente
SELECT COUNT(*) AS notas_sin_asignacion
FROM calificaciones cal WHERE NOT EXISTS (
  SELECT 1 FROM asignacion_cursos ac
  WHERE ac.id_estudiante = cal.id_estudiante
    AND ac.id_curso = cal.id_curso
    AND ac.ciclo_escolar = cal.ciclo_escolar
);

-- 6) Asignaciones sin notas (pueden ser legítimas)
SELECT COUNT(*) AS asignaciones_sin_notas
FROM asignacion_cursos ac WHERE NOT EXISTS (
  SELECT 1 FROM calificaciones cal
  WHERE cal.id_estudiante = ac.id_estudiante
    AND cal.id_curso = ac.id_curso
    AND cal.ciclo_escolar = ac.ciclo_escolar
);

-- 7) Consulta representativa de historial: agrupar por pago y evitar duplicados del JOIN
SELECT cp.id_pago, cp.serie_recibo, cp.carnet, cp.ciclo_escolar,
       cp.monto, cp.fecha_pago
FROM control_pagos cp
WHERE EXISTS (SELECT 1 FROM registro_estudiantes re
              WHERE re.carnet = cp.carnet
                AND re.ciclo_escolar = cp.ciclo_escolar)
ORDER BY cp.id_pago DESC LIMIT 20;

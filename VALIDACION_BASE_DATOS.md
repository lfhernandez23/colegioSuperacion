# Validación de compatibilidad con la base de datos

## Alcance
Se analizó el respaldo `colegio_superacion (19).sql` generado el 5 de noviembre de 2026 en MariaDB 10.4.32. Para evitar modificaciones a información real se cargaron las 8 tablas y sus registros en una base SQLite temporal en memoria, con adaptación de tipos y de la columna calculada de promedio. **No se ejecutó MariaDB, una instancia PHP con MySQLi ni pruebas HTTP de extremo a extremo.** El archivo original de respaldo permanece intacto.

## Registros importados para análisis
| Tabla | Registros |
|---|---:|
| registro_estudiantes | 1,115 |
| control_pagos | 5,315 |
| cursos | 459 |
| asignacion_cursos | 4,445 |
| calificaciones | 3,882 |
| carnet_estudiante | 46 |
| seguridadcontrolacceso | 2 |
| usuarios | 1 |

## Hallazgos
- Hay 3 combinaciones duplicadas de `carnet + ciclo_escolar` en `registro_estudiantes`. El esquema **no** posee una restricción UNIQUE sobre esos campos; una consulta por carné y ciclo puede devolver más de una inscripción.
- Hay 46 pagos sin inscripción coincidente por carné y ciclo. Su tratamiento es una decisión del negocio: pueden ser pagos históricos, datos incompletos o inconsistencias.
- Hay 10 calificaciones sin asignación de curso coincidente. Otras 573 asignaciones no tienen calificación; estas últimas pueden ser normales.
- Las relaciones de `id_estudiante` e `id_curso` en asignaciones y calificaciones no tienen referencias huérfanas en el respaldo.
- Los valores de `jornada`, `nivel` y `grado` de las tablas `registro_estudiantes` y `cursos` coinciden con el catálogo de `js/selectores-academicos.js`.
- El JOIN entre `control_pagos` y `registro_estudiantes` por carné+ciclo multiplica filas de pago cuando existe una inscripción duplicada. Se detectaron 14 filas adicionales debidas a estas combinaciones. Revisar las consultas de reporte e historial.
- El respaldo tiene una cuenta en `usuarios` cuya contraseña no utiliza un hash adaptativo reconocido (bcrypt o Argon2). El inicio de sesión actual compara texto directamente y no establece una sesión PHP. **Riesgo alto de acceso indebido.**
- El repositorio público contiene un dump SQL con datos personales y el archivo de conexión. Se recomienda restringir acceso al respaldo, retirar datos reales del historial público y rotar credenciales si estuvieron expuestas.

## Correcciones publicadas en esta rama
- Sistema de estilos y menú reutilizable para las pantallas administrativas.
- Selector común de jornada/nivel/grado, incluidos alta de estudiante, reinscripción y alta de curso.
- Preservación de la fecha de inscripción precargada.
- Corrección de una referencia a una sentencia inexistente en el controlador de creación de cursos.
- Eliminación del JOIN innecesario en la actualización de pagos, añadiendo ciclo escolar al filtro para mayor precisión.

## Pruebas pendientes antes de fusionar
1. Configurar una instancia aislada de MariaDB/MySQL con PHP y extensión MySQLi, importar el dump en una base desechable y verificar `SHOW CREATE TABLE` para las 8 tablas.
2. Iniciar sesión, cerrar sesión, intentar abrir vistas sin iniciar sesión y comprobar que se deniegue el acceso.
3. Crear inscripción, reinscripción, buscar y editar estudiante con datos de prueba, verificando fecha y opciones académicas.
4. Crear curso, asignar al estudiante, registrar/editar notas y generar PDF.
5. Crear, editar, consultar y eliminar pagos de prueba; comprobar que no se duplican resultados y que un recibo de otro ciclo no se modifica.
6. Revisar menús y formularios en móvil, tableta y escritorio, con teclado y lector de pantalla.
7. Resolver los hallazgos de seguridad antes de exponer el sistema a Internet.

Las consultas solo de lectura están disponibles en `pruebas/validacion_integridad.sql`. Ninguna inconsistencia se corrigió o eliminó directamente de la base original.

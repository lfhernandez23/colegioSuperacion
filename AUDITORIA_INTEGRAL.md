# Auditoría integral del sistema Colegio Superación

## Estado
**En revisión / no aprobado para producción.** Esta rama contiene la primera normalización del frontend y una segunda fase de correcciones de lógica y seguridad. El PR anterior (#1) estaba cerrado **sin haberse fusionado** cuando comenzó esta revisión; por eso el nuevo PR se dirige a `main` e incluye ambos grupos de cambios.

## Correcciones efectuadas
1. **Autenticación:** se inició una sesión PHP auténtica al validar el usuario. Se regeneran identificadores de sesión, se usa cookie HttpOnly con SameSite=Lax y se dispone de cierre de sesión.
2. **Migración de contraseñas:** contraseñas heredadas en texto plano se sustituyen por `password_hash()` al iniciar sesión correctamente. El hash ya existente se comprueba con `password_verify()`. Antes de ensayar este cambio, respaldar la tabla `usuarios` porque el inicio de sesión modifica el hash.
3. **Acceso:** vistas, consultas y controladores protegidos requieren sesión autenticada; los documentos PDF requieren la misma validación.
4. **Configuración de BD:** conexión con `utf8mb4` y variables `COLEGIO_DB_HOST`, `COLEGIO_DB_USER`, `COLEGIO_DB_PASSWORD`, `COLEGIO_DB_NAME`, `COLEGIO_DB_PORT`; los valores predeterminados son compatibles con la instalación típica local de XAMPP.
5. **Edición de estudiantes:** actualización por el identificador único `id`, no por la combinación de carné y fecha, que puede no ser única.
6. **Reportes de pagos:** se vincula cada recibo a una sola inscripción del **mismo ciclo escolar** cuando existen inscripciones duplicadas. Los pagos sin inscripción se conservan en consultas generales cuando corresponde, en lugar de borrarlos.
7. **Asignación de cursos:** insertar una asignación repetida no produce una segunda asignación; respeta la clave única existente.
8. **Búsqueda heredada:** el campo de nombre correcto es `nombre` y la consulta usa parámetros preparados, con resultados limitados a 50 filas y HTML escapado.
9. **Documentos:** la impresión de inscripción lee la información desde la base por `id_estudiante`, evitando enviar nombres, teléfonos, familiares y otra información personal por la URL; se corrigió una variable de nivel mal escrita.
10. **Eliminación de pagos:** se exige coincidencia de ID y serie del recibo; se ha cambiado el retorno por defecto a un historial existente y limitado a rutas internas.
11. **Frontend:** estilos compartidos, navegación accesible y selectores académicos centralizados para formularios principales; no se alteraron los nombres de campos usados por MySQL.
12. **Calidad:** flujo de GitHub Actions para lint de sintaxis PHP 8.2 y JavaScript.

## Hallazgos en el respaldo SQL (5 de noviembre de 2026)
Datos detectados en la copia proporcionada, **no necesariamente idénticos a la base instalada actualmente en XAMPP**:
- 1,115 inscripciones, 5,315 pagos, 459 cursos, 4,445 asignaciones y 3,882 calificaciones.
- 3 combinaciones de carné+ciclo con más de una inscripción.
- 46 pagos sin inscripción correspondiente por carné+ciclo.
- 10 calificaciones sin asignación de curso correspondiente.
- La clave primaria de estudiantes es `id`, no `carnet`; por tanto, los reportes deben evitar unir solo por carné.
- Las tablas `asignacion_cursos` y `calificaciones` tienen claves únicas y relaciones foráneas; añadir otras restricciones sin depurar datos puede fallar.

**No se han eliminado ni combinado registros reales.**

## Riesgos todavía pendientes
- El archivo SQL con datos personales forma parte del repositorio público y de su historial. El `.gitignore` añadido solo evita **futuros** respaldos; no elimina los archivos ya versionados ni el historial. Restringir el repositorio, retirar el respaldo del historial mediante procedimiento supervisado y rotar contraseñas que se hayan expuesto.
- **CSRF y autorización por roles:** una sesión por sí sola no garantiza autorización granular ni tokens CSRF para operaciones de alta, edición y eliminación.
- **Revisión completa de HTML/JS:** existen formularios y scripts heredados con lógica repetida, atributos inline y validaciones inconsistentes, especialmente búsquedas por grupos, reportes y carnés.
- **PDF y reportes heredados:** además de la inscripción, deben revisarse los restantes documentos para comprobar que sus parámetros y permisos estén bien definidos.
- **Duplicados reales:** no debe imponerse un índice UNIQUE sobre `registro_estudiantes(carnet,ciclo_escolar)` hasta resolver manualmente las tres combinaciones duplicadas del respaldo.
- **Eliminar cursos:** el esquema utiliza `ON DELETE CASCADE`, de modo que borrar un curso puede borrar asignaciones y calificaciones relacionadas. Requiere política de negocio y confirmación previa.
- **Pruebas en MariaDB / XAMPP:** no ejecutadas desde este entorno; las comprobaciones SQLite del respaldo no sustituyen a MariaDB ni prueban la aplicación web.

## Procedimiento de validación local (XAMPP)
1. Realizar un respaldo privado del proyecto y la BD `colegio_superacion`.
2. Crear una **segunda** base aislada para pruebas e importar el respaldo; cambiar la configuración de la aplicación de prueba para usar esa base.
3. Desde el proyecto local, ejecutar `git fetch origin` y crear una rama local de prueba desde `origin/refactor/auditoria-integral-xampp` (sin sobreescribir cambios locales).
4. En terminal, ejecutar `C:\\xampp\\php\\php.exe pruebas\\validar_xampp.php` (Windows) o `/opt/lampp/bin/php pruebas/validar_xampp.php` (Linux).
5. Ejecutar lint con PHP sobre todos los archivos modificados; revisar la ejecución del workflow en GitHub Actions.
6. En navegador, probar acceso sin sesión, login antiguo, hash migrado, logout, inscripción nueva, reinscripción, edición por ID, consulta de estudiantes, creación/asignación de cursos, notas, pagos (alta/edición/eliminación) y todos los PDF.
7. Verificar que cada recibo aparece exactamente una vez y corresponde al ciclo escolar de su pago; comparar totales contra SQL.
8. Revisar versión móvil y escritorio y perfiles de autorización.
9. Documentar resultados y aprobar fusión solo después de pruebas satisfactorias.

## Criterio de aceptación
**No fusionar hasta comprobar:** ninguna consulta que multiplique recibos; ningún cambio no intencional en datos; autenticación y autorización funcionales; formularios y PDF correctos; controles CSRF aplicados; sintaxis PHP/JS limpia; y respaldo SQL personal retirado del repositorio público o acceso restringido.

El resultado de esta auditoría es **parcial y verificable**, no una afirmación de que todo el repositorio está corregido al 100 %.

# Perfil, observatorio y administración institucional

Fecha: 13 de noviembre de 2026. Base de seguimiento: `9a6709572e7d33dd2f094544f317c1bca2ed9675`.
Referencia main: `830e77c0cd3a38a2660857517cb70ff56f953cff`.

## Estado y alcance real

Implementación para revisión en una rama derivada de `feature/modulo-seguimiento`. No se ejecutaron migraciones ni se desplegó la aplicación. No se modifican cuentas reales. No se importa automáticamente cartografía ni se supone una API externa operativa.

### Formulario

Entrada autenticada `seguimiento-egresados/mi-espacio`, enlazada desde el perfil personal Ayllu. Confirmación de programa y año, correo de contacto habitual (no cambia la credencial de login), datos de contacto, residencia, empleos simultáneos e históricos. Las fechas de fin concluyen episodios; omitir un empleo nunca lo elimina. La fuente de empleo sigue siendo `egresado_episodios_laborales`. Se conserva compatibilidad con `egresado_actualizaciones`, cuya última fila verificada alimenta el laboratorio previo.

Preguntas condicionales de docencia: modalidad, nivel/ciclo, característica institucional, organización del aula, área, registro EIB, forma de atención, escenario y lenguas. RM 389-2024-MINEDU: fortalecimiento 1–2; revitalización 3–5. No se infiere EIB del idioma del egresado. La presentación Ayllu conserva el saludo voluntario aprobado.

Comunidad: localidad y comunidad separadas, tipo campesina/nativa/no/no sé, nombre; vínculos voluntarios (origen, residencia, trabajo, colaboración), actividades, fecha, influencia de la formación y necesidades. La declaración no acredita identidad étnica ni propiedad territorial. No se calcula retorno causal sin antecedentes.

Preferencias de encuentros: lugares propuestos, disponibilidad laboral, fines de semana y disponibilidad variable. Se guardan como preferencias, no como promesa de asistencia ni rutas calculadas.

Borrador explícito en servidor, con opción de recuperar al regresar. Sin PII en almacenamiento persistente del navegador. Porcentaje de completitud del núcleo separado de fecha de confirmación; no penaliza bienestar, contribuciones o encuentros voluntarios. Solicitud de apoyo genera un caso con responsable, estado y fecha; el titular consulta el estado sin mostrar notas internas.

La campaña pública mantiene su seguridad previa por token/cuarentena; corrige valores por defecto, programa/año omitidos del payload y separación de residencia. Tras confirmar conduce a Mi espacio mediante el login institucional, sin convertir una invitación limitada en una sesión de comunidad. La versión ampliada exige identidad autenticada.

### Observatorio

`seguimiento-egresados/observatorio-conectado`: capas de residencia y trabajo, puntos agrupados y calor. Cuenta personas únicas por lugar, no filas. Solo usa coordenadas de lugares catalogados, no pines no verificados ni centroides distritales simulados. Oculta grupos menores a cinco y no devuelve nombres, DNI, domicilios ni relatos. Acceso reutiliza el permiso existente de analítica. Recarga cada minuto y manualmente; no es un sistema de streaming. La cobertura visible es de perfiles versionados confirmados, no todo el padrón histórico.

El laboratorio anterior deja de asignar Cusco a valores desconocidos, selecciona última actualización verificada y no identifica ruralidad laboral mediante procedencia familiar. El mapa ampliado consulta todos los episodios vigentes, mientras el resumen histórico conserva su estructura de un empleo representativo por persona.

### Catálogo territorial

Tablas institucionales `departamentos/provincias/distritos` abastecen sugerencias encadenadas; el formulario conserva entrada manual para información que aún no está catalogada. Los identificadores internos no se convierten artificialmente en UBIGEO. El catálogo geográfico `seguimiento_lugares` mantiene fuente, código original, tipo, UBIGEO, coordenadas y fecha de fuente. Los códigos de INEI y MINEDU conservan sus espacios de nombres.

Importar CSV UTF-8 normalizado con:

```
php artisan seguimiento:importar-lugares /ruta/catalogo.csv
```

Cabecera: `fuente,codigo,tipo,nombre,departamento,provincia,distrito,ubigeo,latitud,longitud,fecha_fuente`.
Tipos: `localidad,comunidad,escuela,sede`. Coordenadas WGS84 decimales; siempre el par completo o ambas vacías. Preservar ceros iniciales de códigos. Validar archivos INEI, MINEDU y MIDAGRI antes de normalizar. El importador valida todo antes de escribir y es idempotente por fuente/código/tipo.

Fuentes de referencia: INEI IDE (https://ide.inei.gob.pe/), SIGMED (https://sigmed.minedu.gob.pe/descargas/), SICAR (https://georural.midagri.gob.pe/sicar/). El visor antiguo precensal INEI 2017 no acredita actualización ni jurisdicción vigente.

## Jerarquía recuperada de main

| Elemento de main | Aplicación en seguimiento |
|---|---|
| `User` + `HasRoles`, guard web | Misma identidad, roles y permisos Spatie; sin otra tabla de usuarios |
| `AdminController` y `admin.usuarios._form_roles` | Misma pantalla para delegar permisos funcionales; solo Super Admin los cambia |
| `super-admin` y `admin` | Gestión institucional; no se crean roles nuevos de Dirección o Coordinación |
| `adminB` | Administración de Bolsa; deja de implicar acceso general a expedientes o laboratorio |
| `docente`, `tutor` | Conservan panel original; habilitación funcional más asignación vigente para seguimiento |
| `gestor-titulacion`, `secretaria` | Se recupera la segregación por áreas de main, no se convierten en coordinadores de egresados |
| `condicion = Egresado` | Identifica egresados sin crear obligatoriamente otro rol institucional |
| `LoginController` y redirect central | Se recupera el orden de destinos de main; ningún rol auxiliar de seguimiento secuestra el panel institucional |

`AreasTitulacion` y `AreaTitulacion` se recuperan de main con defensa adicional para quitar campos RD de solicitudes de otras áreas. Se registra el middleware sobre el grupo existente; las escrituras sin clasificación quedan solo para Super Admin, como en main. Las vistas antiguas de titulación aún pueden presentar controles que el servidor rechaza por área: revisar visualmente ese módulo antes de publicar. No se ha fusionado main completo ni su sistema actualizado de notificaciones.

Los roles históricos `coordinacion-empleabilidad`, `direccion`, `bienestar` y `seguimiento-egresados` no se borran ni reasignan. La coordinación operativa se expresa mediante permisos explícitos existentes, no por un nuevo nombre de cargo. `seguimiento-egresados` se conserva como compatibilidad de habilitación operativa anterior. La delegación directa se registra en el log institucional; no se sustituye el auditor de roles.

## Verificación y activación

- Análisis sintáctico de PHP 8.2 mediante php-parser y comprobación `node --check`.
- Pruebas de interfaz con Node/jsdom: ausencia de selección de empleo por defecto, preguntas condicionales, porcentaje sin opcionales y error de servidor sin falso éxito.
- Pruebas PHPUnit añadidas para validación EIB, borrador incompleto, coordenadas desconocidas y jerarquía Admin/Bolsa.
- **PHP, Composer, vendor y una base de ensayo no están disponibles en este entorno: PHPUnit, Blade y migraciones no se han ejecutado.**

Antes del despliegue: instalar dependencias del repositorio, preparar base de ensayo y respaldo, ejecutar `php artisan migrate --pretend`, migraciones aditivas, pruebas de seguimiento y nuevas pruebas unitarias; comprobar cuentas con combinaciones de roles, asignaciones, expiración y revocación. Probar CRUD de titulación por Secretaría/Investigación y login. Las migraciones nuevas no eliminan datos al revertir; retirar el código deja intactas las tablas y asignaciones para evitar pérdida de historia.

Pendiente de integración externa: catálogos oficiales reales normalizados, resolución asistida de comunidades y locales escolares, servicio de tiempos de viaje, comparación de sedes e isócronas. No se ofrecen tiempos simulados ni se declara lista la recomendación automática de reuniones. También quedan fuera de este cambio las notificaciones automáticas de bolsa/ayllus y la creación de chats por promoción: pertenecen al desarrollo comunitario posterior.


## Ampliación FID/PPD
Consultar `16-fid-ppd-transversal.md` para reglas, filtros, comparaciones y nuevas pruebas. El inventario `17-estado-paneles.md` identifica qué paneles siguen pendientes. Se ejecutaron reglas puras con PHP 8.2 WebAssembly; sigue pendiente la validación de Laravel, Blade, SQL y migraciones.

## Entrega de paneles
El inventario 17 se actualizó: los paneles institucionales, la primera confirmación, la entrevista sobre el formulario ampliado, el centro de solicitudes y los cortes de informes ya tienen implementación. Sigue pendiente el ensayo completo de Laravel/Blade/MySQL; no confundir las pruebas aisladas con validación de producción.

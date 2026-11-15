# Pukllasunchis — reglas de trabajo

Responder siempre en español.

Mapa del proyecto (rutas, roles, módulos, servicios, app móvil): [`_notes/arquitectura.md`](_notes/arquitectura.md). Leerlo al empezar a trabajar en una zona nueva y actualizarlo si se agrega o mueve un módulo, servicio o rol.

## Antes de cambiar código

- Leer el código afectado y **buscar todos los lugares que lo usan** (llamadas, tests, factories, vistas, rutas, config) antes de elegir la solución. Una corrección se hace **una vez, en la causa raíz** del código compartido, no en cada lugar que falla.
- Elegir la solución más pequeña que resuelva todo, en este orden: ¿hace falta? → ¿ya existe en el proyecto (servicios en `app/Services`, modelos, helpers)? → ¿lo da Laravel/PHP o Flutter/Dart? → ¿una dependencia ya instalada? → recién ahí, código nuevo mínimo.
- Nunca recortar: validación en los puntos de entrada (requests, API), manejo de errores que pueda perder datos, seguridad/permisos ni accesibilidad.
- La lógica con ramas o riesgo (permisos, plazos, pagos, estados de titulación) lleva al menos un test pequeño.

## Atajos conscientes (`shortcut:`)

Cuando se toma un atajo a propósito (duplicación, límite conocido, solución provisional), dejar un comentario en el código:

```
// shortcut: <qué se simplificó / su límite>, <cuándo o con qué conviene resolverlo>
```

Para ver la deuda pendiente: `grep -rn "shortcut:" app routes database tests FlutterPuklla/lib`. Al terminar una tarea, mencionar los `shortcut:` nuevos o los que se resolvieron.

## Restricción del proyecto

La API `/api/v1` (app móvil) es **aditiva**: no se modifican los controladores web ni `routes/web.php` / `routes/admin.php`. Si para reutilizar habría que tocar la web, se replica en la API con un `shortcut:` y se coordina con el equipo de la web.

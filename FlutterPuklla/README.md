# FlutterPuklla

App móvil de Pukllasunchis (alumnos y docentes). Consume la API `/api/v1` del
backend Laravel del mismo repo padre (`../` = `Pukllasunchis/`). No reemplaza
la web: ambas coexisten sobre la misma base de datos.

## Stack

- Estado: Riverpod (`flutter_riverpod`)
- Rutas: `go_router`
- HTTP: `dio`
- Token seguro: `flutter_secure_storage`
- Abrir archivos externos (imágenes/PDF de comunicados): `url_launcher` —
  añadido en la tarea "Comunicados", no estaba en la lista original de
  decisiones técnicas; es el paquete oficial y mínimo del equipo de Flutter
  para esto, no hay alternativa sin dependencia dentro del stack decidido.

## Estado del scaffolding

Este proyecto fue creado a mano (`pubspec.yaml` + `lib/`), **sin** correr
`flutter create` — el CLI de Flutter está bloqueado en la máquina de
desarrollo actual por una regla de antivirus/EDR que impide que `cmd.exe`
invoque `powershell.exe` (necesario internamente en cada ejecución de
`flutter.bat`). Pendiente una vez resuelto:

```
flutter create . --project-name flutter_puklla --platforms=android,ios
flutter pub get
```

Esto genera `android/`, `ios/` (y cualquier otra plataforma que se agregue)
sin tocar `lib/` ni `pubspec.yaml` existentes.

## Correr en local

El backend Laravel corre en `127.0.0.1:8001` (`php artisan serve`). Por
defecto la app usa esa URL (ver `lib/core/env/env.dart`); para apuntar a otro
entorno:

```
flutter run --dart-define=API_BASE_URL=http://127.0.0.1:8001/api/v1
```

## Estructura (por feature)

```
lib/
  core/            # env, cliente dio, almacenamiento seguro del token
  routing/         # go_router
  features/
    auth/          # login, token, redirect
    home/          # dashboard
    cursos_especiales/
    calificaciones/
    comunicados/
```

Cada feature: `data/` (modelos + repos que llaman a la API), `application/`
(providers/controllers de Riverpod), `presentation/` (pantallas/widgets).

Ver `../_notes/mobile-app-plan.md` para el checklist completo de la Fase 2 y
`../_notes/api-v1-endpoints.md` para el contrato de la API.

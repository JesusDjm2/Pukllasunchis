---
name: puklla-mobile-intranet
description: Guía de arquitectura, diseño institucional, patrones Riverpod, tema claro/oscuro y reglas de negocio para la app móvil Flutter de la Intranet Universitaria Pukllasunchis.
---

# Puklla Mobile Intranet — Guía de Arquitectura y Desarrollo

Esta skill estandariza el desarrollo, arquitectura, sistema de diseño y reglas de negocio para la aplicación móvil **FlutterPuklla** de la Escuela de Educación Superior Pedagógica Pukllasunchis.

---

## 1. Arquitectura y Stack Tecnológico

El proyecto móvil se ubica en `FlutterPuklla/` y se conecta al backend Laravel (`routes/api.php`):

- **Framework:** Flutter 3.24+ (Dart 3.x con null-safety estricto y pattern matching).
- **Gestión de Estado:** `flutter_riverpod` (v2.6+). Usar `StateNotifierProvider` para flujos mutables (ej. autenticación) y `FutureProvider` para consultas API asíncronas con caché y refresco.
- **Navegación:** `go_router` (v14+) declarativo con control centralizado de redirecciones según sesión y roles.
- **Cliente HTTP:** `dio` con interceptor para Bearer token provisto por `TokenStorage` (`flutter_secure_storage`).
- **Control de Versiones:** Desarrollar **exclusivamente en ramas de trabajo asignadas** (ej. `feature/web-docente`). **NUNCA** fusionar ni hacer commit directo en `main`.

---

## 2. Sistema de Diseño Institucional y Hojas de Estilo Centralizadas

Para garantizar que todos los dashboards (Estudiante, Docente, Tutor, etc.) compartan una estética nivel Senior y sean fáciles de mantener, **los estilos no deben definirse directamente en las vistas, sino centralizarse en hojas de estilos reutilizables**:

### A. Paleta de Colores (`AppColors` en `lib/core/theme/app_colors.dart`)
- **Oro Institucional:** `AppColors.gold` (`#CD9244`), `AppColors.goldHover` (`#B9823B`).
- **Petróleo / Teal Institucional:** `AppColors.teal` (`#33445A`), `AppColors.tealDark` (`#1B2A4A` / `#253449`).
- **Superficie y Fondos Claros:** `AppColors.surface` (`#FFFFFF`), `AppColors.background` (`#F8FAFC`).
- **Superficie y Fondos Oscuros:** `AppColors.darkSurface` (`#1E293B`), `AppColors.darkBackground` (`#0F172A`).
- **Semáforo Académico:** Éxito/Aprobado (`#059669`), Advertencia/Pendiente (`#D97706`), Peligro/Desaprobado (`#DC2626`).

### B. Hoja de Estilos de Dashboards (`lib/core/theme/puklla_dashboard_styles.dart`)
Toda pantalla de dashboard debe consumir estos helpers y componentes:
- `PukllaCardStyles.cardDecoration(context)`: Tarjeta institucional con borde sutil, elevación suave y adaptación automática claro/oscuro.
- `PukllaMetricStatCard`: Tarjetas de estadísticas (Cursos, Promedio, Aprobados, Pendientes).
- `PukllaBentoGrid`: Módulos de acceso rápido con icono temático, título y subtítulo.
- `PukllaSectionHeader`: Títulos de sección consistentes con botón de acción lateral.
- `PukllaAvatar`: Renderizado de imagen con aro institucional y fallback automático a iniciales.

### C. Tema Claro y Oscuro Dinámico
- Soportar ambos modos mediante `AppTheme.light` y `AppTheme.dark`.
- Permitir la alternancia fluida de tema a través de `themeModeProvider`.
- Garantizar contraste mínimo WCAG AA en textos y pestañas (`TabBar`).

---

## 3. Reglas de Negocio Académico

1. **Escala de Calificación:**
   - La institución utiliza la escala **vigesimal (0 a 20)**.
   - La nota mínima aprobatoria es **11**. Notas $\ge 11$ se marcan con distintivo verde de aprobado; notas $< 11$ en rojo de desaprobado.
   - Cada curso cuenta con `Parcial 1`, `Parcial 2` y `Promedio final`, evaluando además `Nivel de logro` y `Valoración`.
2. **Sincronización de Matrícula y Período Activo:**
   - La matrícula está ligada al `PeriodoActual`. Si el alumno no registra matrícula y el formulario está abierto, debe notificarse proactivamente (popup modal y botón directo al formulario `/matricula/formulario`).
   - Si la matrícula está confirmada, la tarjeta del período ofrece acceso directo a sus calificaciones y descarga del horario en PDF.
3. **Fotos de Perfil y CORS:**
   - Las imágenes se sirven a través del endpoint `/api/v1/foto/estudiante/{archivo}` con cabeceras `Access-Control-Allow-Origin: *` para compatibilidad completa en web y dispositivos móviles.

---

## 4. Estándar de Testing

- Todas las pantallas y widgets deben verificarse mediante pruebas automatizadas con `flutter test`.
- Sobrescribir siempre `tokenStorageProvider` con una implementación en memoria (`_FakeTokenStorage`) para evitar llamadas al plugin nativo en entornos de test.
- Definir tamaños de vista físicos en tests que evalúen scroll: `tester.view.physicalSize = const Size(1080, 2400)`.

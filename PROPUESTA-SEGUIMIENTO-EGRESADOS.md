# Sistema de Seguimiento de Egresados (EESP Pukllasunchis)
## Sistematización de Requerimientos y Propuesta de Implementación Técnica

> **Proyecto**: Seguimiento de Egresados: Titulación, Trayectoria Laboral y Práctica Profesional (24 meses).  
> **Líder de Proyecto**: Pavel Chaparro Acurio  
> **Institución**: EESP Pukllasunchis  
> **Rama**: `seguimientoesp`  
> **Fecha**: Octubre 2026  

---

## PARTE I: Requerimientos Institucionales Acordados

### 1. El Problema Institucional
La escuela dispone de formularios, listas de promociones, registros académicos y resultados de encuestas. Sin una relación estable entre esas fuentes, cada campaña obliga a volver a reunir datos y resulta difícil reconstruir qué cambió en la vida profesional de una misma persona. Una cifra general de empleo tampoco explica si el trabajo es educativo, si permite ejercer como docente, qué condiciones ofrece o qué respuesta podría organizar Pukllasunchis.

El sistema debe producir conocimiento útil para actuar: acompañar la titulación, reconocer dificultades de inserción, fortalecer prácticas pedagógicas, organizar formación continua y conectar a quienes pueden aprender entre sí. También debe permitir evaluar si esas respuestas llegan a las personas y les resultan útiles.

### 2. Alcance Delimitado
- **Punto de partida**: Se apoya en lo existente en la plataforma: *Permanencia, Abandono, Retorno, Egreso y Titulación*. No se reconstruye otro sistema de matrícula.
- **Ventana temporal**: Seguimiento formal durante 2 años (hitos: Egreso/M0, M6, M12, M18 y M24).
- **Extensión**: Comunidad de aprendizaje voluntaria posterior a los 2 años.
- **Población inicial**: Programas de **Educación Inicial** y **Educación Primaria EIB**.

### 3. Las Tres Funciones Conectadas
1. **Seguimiento individual**: Ficha única por persona con actualizaciones históricas periódicas (sin sobreescribir ni duplicar).
2. **Inteligencia institucional**: Matrices de cruce, filtros multidimensionales y análisis territorial (mapas) para toma de decisiones.
3. **Comunidad de aprendizaje y acción institucional**: Transformar solicitudes de apoyo en **Casos** con responsable y evaluación; red de contactos y saberes compartibles.

### 4. Recogida Periódica y Canales de Acceso
- **2 campañas anuales** planificadas.
- **Canal 1 (WhatsApp / Magic Link)**: Enlace firmado temporal para actualización autónoma desde celular.
- **Canal 2 (Intranet Institucional)**: Acceso permanente con cuenta institucional `@pukllavirtual.edu.pe`.
- **Canal 3 (Encuestadores Telefónicos)**: Asignación temporal de lotes de egresados pendientes con registro de llamadas e intentos. Bloqueo de concurrencia si el egresado ya completó la ficha.
- **Calidad del dato**: Toda actualización conserva fecha, campaña, canal, digitador y procedencia. La no respuesta es un estado de cobertura, nunca desempleo asumido.

### 5. Ficha Común Breve y Progresiva (4 Pasos)
- **Paso 1: Contacto**: Celular, WhatsApp, correo, residencia (UBIGEO) y canal preferido.
- **Paso 2: Titulación y Empleo**: Situación actual, institución, cargo, sector, contrato y estabilidad. Pregunta de cambio: *¿Desde cuándo ocurrió?*.
- **Paso 3: Práctica Profesional**: Trabajo educativo, docencia directa, contexto (EIB, multigrado), satisfacción y dificultades en aula.
- **Paso 4: Aprendizaje y Apoyo**: Necesidades formativas, saberes que puede compartir y solicitudes de acompañamiento.

### 6. Roles Acumulables de la Intranet
- `egresado`: Consulta y actualización de su ficha; red de aprendizaje.
- `encuestador-seguimiento`: Temporal; formulario y contactos de casos asignados en campaña.
- `coordinacion-empleabilidad`: Monitoreo general, mapas, gestión de casos de apoyo.
- `formacion-continua`: Detección de demandas formativas y facilitadores.
- `secretaria-academica`: Verificación y contrastación de fechas e hitos de egreso/titulación.
- `direccion`: Tableros estratégicos, cruces multidimensionales y reportes.

---

## PARTE II: Especificación de Arquitectura Técnica (Laravel 10)

### 1. Modelo de Datos Relacional

```
[users] 1 ──── ∞ [egresado_actualizaciones]
   │                       │
   │                       ├──── 1 [egresado_practica_profesional]
   │                       ├──── 1 [egresado_red_aprendizaje]
   │                       └──── ∞ [egresado_episodios_laborales]
   │
   ├──── ∞ [egresado_casos] ──── ∞ [egresado_caso_acciones]
   │
   └──── ∞ [encuestador_asignaciones] ──── ∞ [encuestador_llamadas]
```

#### Catálogo Territorial (`ubigeos`)
Normalización a 6 dígitos (código INEI) con coordenadas (latitud/longitud) para diferenciar:
- `residencia_ubigeo_codigo`
- `trabajo_ubigeo_codigo`

#### Episodios Laborales (`egresado_episodios_laborales`)
- Distinción booleana obligatoria:
  - `es_empleo_educativo`
  - `es_docencia_directa`
- Atributos de estabilidad: `sector` (público, privado, ONG/comunitario), `tipo_contrato` (nombrado, contratado anual, locación, sin contrato), fechas inicio/fin y bandera `es_actual`.

#### Del Hallazgo a la Acción (`egresado_casos` y `egresado_caso_acciones`)
- Toda solicitud de acompañamiento en titulación, inserción o práctica docente se convierte en un caso con un `responsable_user_id`, estado (`abierto`, `en_atencion`, `derivado`, `cerrado_resuelto`), bitácora de gestiones y evaluación final del resultado.

### 2. Canales y Autenticación
- **Magic Links firmados**:
  ```php
  URL::temporarySignedRoute(
      'seguimiento.ficha.publica',
      now()->addDays(7),
      ['token' => $egresado->token_campana]
  );
  ```
- **Panel del Encuestador**:
  - Filtro por lote asignado.
  - Candado optimista para evitar sobreescritura si el egresado está respondiendo en línea.

### 3. Métricas con Denominadores Transparentes
- **Tasa de Cobertura**:
  $$\text{Cobertura} = \frac{\text{Actualizaciones válidas en campaña}}{\text{Población total elegible}}$$
- **Tasa de Ocupación Docente**: Se publica con doble regla:
  - Denominador A: Sobre el total de personas con respuesta válida.
  - Denominador B: Sobre el universo total de la promoción (mostrando el remanente como *cobertura pendiente*).

---

## PARTE III: Plan de Fases de Construcción

1. **Fase 1: Fundaciones de Datos**: Migraciones del módulo `SeguimientoEgresado`, seeder de UBIGEO y nuevos roles Spatie.
2. **Fase 2: Ficha y Motor de Recogida**: Controladores Blade responsivos para móviles, generación de enlaces WhatsApp y panel de encuestadores.
3. **Fase 3: Módulo de Casos e Intervención**: Flujo de derivación operativa, asignación de responsables y seguimiento de compromisos.
4. **Fase 4: Tablero Analítico y Cartografía**: Matriz multidimensional y mapas en Leaflet.js para residencia vs. centros laborales.

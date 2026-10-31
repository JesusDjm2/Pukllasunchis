# PUKLLA EN RED — PANEL DOCENTE: MIS EGRESADOS ASIGNADOS
## Acompañamiento Individual, Registro de Llamadas con 11 Estados y Actualización de Fichas

**Institución:** Escuela de Educación Superior Pedagógica Pukllasunchis — Cusco, Perú  
**Módulo:** Puklla en Red — Seguimiento, Acompañamiento y Desarrollo Profesional de Egresados  
**Ruta:** `/seguimiento-egresados/mis-asignaciones`  
**Controlador:** `App\Http\Controllers\SeguimientoCampanaController@misAsignaciones`  

---

## 1. INTEGRACIÓN CON EL DASHBOARD DOCENTE EXISTENTE

Cuando un docente de Pukllasunchis tiene asignaciones activas en una campaña:
- En su panel docente habitual (`/docentes/{id}`), aparece un card destacado con el distintivo granate de Puklla en Red y una insignia verde indicando la cantidad de egresados asignados (ej. `81 egresados asignados`).
- El botón **«Mis Egresados Asignados»** conduce directamente a su espacio de trabajo personalizado.

---

## 2. LOS 9 INDICADORES PERSONALES DEL DOCENTE

El panel docente muestra 9 tarjetas resumen donde cada una explica exactamente qué mide:

1. **Egresados Asignados:** Total de egresados asignados al docente en la campaña activa.
2. **Pendientes de Contactar:** Personas con las que aún no se ha entablado una conversación efectiva.
3. **Intentos de Llamada:** Número total de marcaciones registradas (incluye números que no contestan o timbran ocupado).
4. **Personas Efectivamente Contactadas:** Cantidad de personas físicas únicas con las que se conversó.
5. **Actualizaciones Completadas:** Fichas con formulario espejo completo (empleo, titulación, residencia).
6. **Actualizaciones Parciales:** Entrevistas donde el egresado proporcionó datos básicos pero falta concluir la ficha.
7. **Confirmaciones Sin Cambios:** Egresados que ratificaron la vigencia de su situación anterior.
8. **Entrevistas Reprogramadas:** Casos coordinados para una fecha y hora posterior.
9. **Casos Pendientes de Atención:** Egresados derivados a Bienestar o Coordinación para soporte especializado.

---

## 3. LISTA DE TRABAJO Y PROTOCOLO DE LLAMADAS

La tabla de trabajo presenta los datos necesarios para la gestión:
- Nombre y correo institucional del egresado.
- Programa académico y cohorte de egreso.
- Número de contacto disponible mediante enlace `tel:`.
- Estado del seguimiento y fecha/resultado del último intento.
- Próxima acción o fecha reprogramada.

> [!NOTE]
> **Enlace Telefónico No Automático:** El enlace `tel:` abre la aplicación de llamadas del dispositivo del docente pero **no registra automáticamente una llamada completada**. El docente debe registrar manualmente el resultado tras dialogar con el egresado.

---

## 4. LOS 11 ESTADOS OFICIALES DEL SEGUIMIENTO

El modal de llamadas estandariza la comunicación en 11 categorías institucionales:

### Intentos sin contacto:
1. `Por contactar`
2. `Intento sin respuesta` (No contesta, apagado, casilla de voz)
3. `Número equivocado` (Inexistente o pertenece a otra persona)

### Contactos efectivos:
4. `Contacto efectivo` (Comunicación lograda y diálogo establecido)
5. `Entrevista programada` (Se fijó fecha y hora para la entrevista)
6. `Actualización parcial` (Información básica recibida)
7. `Actualización completa` (Formulario espejo llenado en su totalidad)
8. `Confirmación sin cambios` (Ratificación de la situación actual)
9. `Rechazo de participación` (Egresado manifiesta no desear participar)

### Gestión especial:
10. `Requiere verificación` (Datos contradictorios que requieren contraste)
11. `Requiere derivación` (Apertura automática de caso institucional para Bienestar o Tutoría)

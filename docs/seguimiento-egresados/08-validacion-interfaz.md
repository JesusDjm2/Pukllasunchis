# PUKLLA EN RED — Sistema de Seguimiento, Acompañamiento y Desarrollo Profesional de Egresados
## 08. Evidencias de Validación de Interfaz de Usuario — Escritorio y Móvil

**Institución:** Escuela de Educación Superior Pedagógica Pukllasunchis  
**Proyecto:** Puklla en Red — Intranet Institucional  
**Documento de Auditoría:** Certificación de Experiencia de Usuario y Pruebas de Interfaz (Fase 1B)  
**Servidor de Prueba:** `http://127.0.0.1:8000`  
**Rutas Verificadas:**
1. Escritorio / Intranet: `http://127.0.0.1:8000/seguimiento-egresados`
2. Móvil / WhatsApp: `http://127.0.0.1:8000/seguimiento-egresados/campana-publica`

---

### 1. Pruebas en Entorno de Escritorio (Desktop View - 1280px / 1440px)

#### 1.1. Inspección y Renderizado de Componentes
El panel de seguimiento institucional carga fluidamente integrando los datos de Laravel y las capas reactivas de JavaScript institucional:

```html
<!-- Inyección de datos verificada en código fuente renderizado -->
<script>
    const INSTITUTIONAL_EGRESADOS = [...]; // 300 egresados canónicos
    const ACTUALIZACIONES_DB = {...};     // Actualizaciones vigentes
    const EPISODIOS_DB = [...];           // Historial longitudinal
    const ROUTES = {
        actualizar: "http://127.0.0.1:8000/seguimiento-egresados/actualizar",
        cerrarEpisodio: "http://127.0.0.1:8000/seguimiento-egresados/episodios",
        ...
    };
</script>
```

#### 1.2. Tarjeta de Confirmación Rápida Semestral (R02)
- **Ubicación:** Encabezado del formulario ágil de actualización (`actualizacionAgil`).
- **Diseño Visual:** Tarjeta con fondo gradiente menta institucional (`#f0fdf4` a `#ecfdf5`), borde de acento verde esmeralda (`#86efac`) y botón con sombra y texto claro:
  ```html
  <div class="panel" style="background: linear-gradient(135deg, #f0fdf4 0%, #ecfdf5 100%); border: 1.5px solid #86efac; border-radius: 12px; padding: 16px; margin-bottom: 20px;">
      <h3 style="color:#065f46; font-size:15px; margin:0;">
          <i class="fas fa-bolt" style="color:#10b981;"></i> Confirmación Rápida Semestral «Sin cambios» (R02)
      </h3>
      <button class="btn green" type="button" onclick="APP.confirmarSinCambios()">
          ⚡ Confirmar situación vigente sin cambios
      </button>
  </div>
  ```
- **Comportamiento Probado:**
  1. Al presionar el botón, se captura el microchequeo visible (satisfacción, demandas formativas y necesidad de acompañamiento).
  2. Se envía la petición asíncrona a `ROUTES.actualizar` con `sin_cambios: true`.
  3. Al recibir la respuesta exitosa del servidor, se despliega una alerta verde inline:
     `✓ Situación confirmada con éxito (R02): Situación actual confirmada exitosamente. No se han duplicado registros laborales. Fecha de verificación: 2026-10-30 · Empleos activos vigentes: 1`
  4. La vista añade el evento a la línea de tiempo histórica del egresado sin recargar la página.

#### 1.3. Panel de Trayectoria Laboral Longitudinal (R03)
- **Ubicación:** Bloque 02 de la ficha ágil y pestaña `Mi ficha`.
- **Estructura Renderizada:**
  - **Empleos Activos:** Presentados en tarjetas blancas con borde, destacando nombre de la institución en negrita, cargo, fecha de inicio, dedicación, indicador `Docencia directa` y botón rojo de acción:
    ```html
    <button type="button" class="btn sm danger" onclick="APP.cerrarEpisodioModal(37)">
        <i class="fas fa-stop-circle mr-1"></i> Cerrar empleo
    </button>
    ```
  - **Historial de Empleos Anteriores:** Agrupados dentro de un elemento interactivo nativo `<details><summary>`, permitiendo desplegar la lista de empleos cerrados con su periodo exacto (`fecha_inicio` a `fecha_fin`) y motivo de cambio registrado.

#### 1.4. Acción de Cierre Controlado de Empleo
- Al hacer clic en `Cerrar empleo`:
  1. El sistema solicita la fecha de finalización mediante diálogo interactivo, sugiriendo la fecha del día pero permitiendo dejar el campo en blanco si la fecha exacta no se recuerda.
  2. Solicita el motivo de cambio o culminación de contrato.
  3. Llama a `/seguimiento-egresados/episodios/{id}/cerrar`.
  4. El servidor actualiza el registro en la base de datos sin alterar la fecha de inicio.
  5. La interfaz conmuta el estado del empleo a inactivo inmediatamente.

---

### 2. Pruebas en Entorno Móvil (Mobile View - 375px / 414px / 768px)

#### 2.1. Formulario Público de Campaña WhatsApp (`campana_publica.blade.php`)
- **Adaptabilidad Responsiva:**
  - Viewport optimizado: `<meta name="viewport" content="width=device-width, initial-scale=1.0">`.
  - Tarjeta central (`.pk-card`) con bordes redondeados (`18px`) y relleno adaptado para pantallas táctiles.
  - Campos con altura mínima de 44px para facilitar la interacción dactilar en teléfonos inteligentes.

#### 2.2. Confirmación Rápida Móvil
- **Ubicación:** En la cabecera del formulario de campaña pública.
- **Implementación:**
  ```html
  <div style="background:#f0fdf4; border:1px solid #86efac; border-radius:12px; padding:12px 14px; margin-bottom:1.5rem; display:flex; align-items:center; gap:10px;">
      <input type="checkbox" id="sin_cambios_public" name="sin_cambios" value="1" style="width:18px; height:18px; accent-color:#10b981;">
      <label for="sin_cambios_public" style="font-size:0.86rem; color:#065f46; font-weight:600;">
          ⚡ <strong>Mi situación sigue vigente:</strong> Reconfirmar mis datos laborales y académicos sin cambios para este semestre.
      </label>
  </div>
  ```
- **Flujo en 2 Pasos:**
  1. El egresado ingresa su DNI y nombre.
  2. Marca la casilla de reconfirmación y presiona el botón `Enviar actualización de datos`.
  3. El servidor vincula la respuesta al `user_id` correspondiente sin alterar los episodios activos y responde con mensaje de felicitación personalizado: `¡Datos actualizados con éxito! Muchas gracias, colega.`

---

### 3. Verificación de Seguridad y Privacidad en el Navegador

#### 3.1. Auditoría de `localStorage`
- **Requisito Rector:** *«No almacenes datos personales sensibles de manera persistente en localStorage.»*
- **Verificación Técnica Ejecutada:**
  ```bash
  grep -rn "localStorage" resources/views/seguimiento_egresados/
  # Código de salida: 1 (Sin coincidencias)
  ```
- **Dictamen:** **100% Conforme**. Los datos de los egresados solo viajan en la memoria transitoria de la sesión (`state`) y se persisten de manera protegida en el motor MySQL del servidor a través de endpoints seguros protegidos con token CSRF.

#### 3.2. Protección de Cookies y Sesión
- Sesión protegida mediante `HttpOnly`, `SameSite=Lax` y validación obligatoria de `csrf-token` en cada petición POST.
- Peticiones anónimas sin token son interceptadas por el middleware `VerifyCsrfToken` con código HTTP 419.

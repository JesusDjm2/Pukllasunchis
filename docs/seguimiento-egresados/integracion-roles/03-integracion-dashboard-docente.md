# PUKLLA EN RED — Integración del Dashboard Docente
## Documento 03: Experiencia de Usuario, Navegación y Protección en Backend

**Institución:** Escuela de Educación Superior Pedagógica Pukllasunchis (Cusco, Perú)  
**Proyecto:** Puklla en Red — Seguimiento, Acompañamiento y Desarrollo Profesional de Egresados  
**Repositorio:** `JesusDjm2/Pukllasunchis`  
**Rama:** `sistemadeseguimientodeegresados`  

---

### 1. OBJETIVO DE LA INTEGRACIÓN

Garantizar que un docente de la EESP Pukllasunchis que reciba el rol `seguimiento-egresados` pueda visualizar y acceder al módulo **Puklla en Red** directamente desde su entorno natural de trabajo (el dashboard docente institucional), respetando el sistema de diseño existente (`pk-sidebar.css`, SB Admin 2, tipografía Nunito/Inter y colores institucionales), sin necesidad de cambiar de cuenta ni de iniciar sesión en un sistema externo.

---

### 2. PUNTOS DE INTERVENCIÓN EN LAS VISTAS

Se intervinieron dos archivos clave de la interfaz docente:

#### 2.1. Menú Lateral Institucional (`resources/views/layouts/docente.blade.php`):
En el bloque de "Mis funciones" del sidebar colapsable/expandible, se sustituyó la verificación genérica de ruta por una comprobación estricta de autorización:

```php
$esSeguimientoSb = ($usuarioSb?->hasRole('seguimiento-egresados') 
    || $usuarioSb?->can('seguimiento-egresados.ver-dashboard') 
    || $usuarioSb?->hasAnyRole(['admin', 'super-admin', 'coordinacion-empleabilidad'])) 
    && Route::has('seguimiento-egresados.index');
```

Y en el marcado del menú (líneas 453–461):
```blade
@if ($esSeguimientoSb)
    <li class="nav-item">
        <a class="nav-link pk-sb-link {{ request()->routeIs('seguimiento-egresados.*', 'seguimiento.*') ? 'is-activo' : '' }}" 
           href="{{ route('seguimiento-egresados.index') }}" 
           title="Seguimiento de Egresados — Puklla en Red">
            <span class="pk-sb-icono"><i class="fas fa-network-wired"></i></span>
            <span class="pk-sb-texto">Seguimiento de Egresados — Puklla en Red</span>
        </a>
    </li>
@endif
```

#### 2.2. Tarjeta Contextual en el Dashboard Principal (`resources/views/docentes/show.blade.php`):
Justo debajo del encabezado principal (hero con saludo al docente y selector FID/PPD), se incorporó una tarjeta destacada con borde granate institucional (`#832128`) que solo se renderiza si el docente posee el rol o permiso correspondiente:

```blade
@if (auth()->user()?->hasRole('seguimiento-egresados') || auth()->user()?->can('seguimiento-egresados.ver-dashboard'))
    <div class="card docente-ui-card mb-3 mb-md-4" style="border-left: 4px solid #832128; background: #fffdfd;">
        <div class="card-body p-3 d-flex flex-wrap align-items-center justify-content-between" style="gap:.5rem .75rem;">
            <div class="d-flex align-items-center" style="gap: .75rem;">
                <div style="width: 40px; height: 40px; border-radius: 8px; background: #fce8e9; color: #832128; display: grid; place-items: center; font-size: 1.1rem;">
                    <i class="fas fa-network-wired"></i>
                </div>
                <div>
                    <span class="font-weight-bold d-block" style="color: #832128; font-size: 0.95rem;">
                        Seguimiento de Egresados — Puklla en Red
                    </span>
                    <small class="text-muted">
                        Tienes asignado el rol institucional para registro de entrevistas, seguimiento y acompañamiento de egresados.
                    </small>
                </div>
            </div>
            <div>
                <a href="{{ route('seguimiento-egresados.index') }}" class="btn btn-sm text-white font-weight-bold" style="background: #832128; border-radius: 20px; padding: 0.4rem 1.1rem;">
                    <i class="fas fa-external-link-alt mr-1"></i> Ingresar a Puklla en Red
                </a>
            </div>
        </div>
    </div>
@endif
```

---

### 3. COMPORTAMIENTO DINÁMICO Y PRUEBAS VISUALES

El comportamiento del dashboard responde a los siguientes estados funcionales:

| Estado del Docente | Visualización en Menú Lateral | Visualización en Dashboard Principal | Acceso a Rutas `/seguimiento-egresados` |
|:---|:---:|:---:|:---:|
| **Docente sin rol** | Oculto | Oculto | Redirección o HTTP 403 en endpoints sensibles |
| **Docente con `seguimiento-egresados`** | **Visible** ("Seguimiento de Egresados — Puklla en Red") | **Visible** (Tarjeta granate institucional con botón de ingreso) | **Autorizado** (exclusivamente para egresados asignados) |
| **Rol revocado por Super-Admin** | Desaparece instantáneamente | Desaparece instantáneamente | HTTP 403 Forbidden inmediato |

---

### 4. PROTECCIÓN INTEGRAL EN EL BACKEND

No se confía en la simple ocultación de botones en las plantillas Blade. Cada petición HTTP hacia el módulo está verificada en el servidor:

1. **`SeguimientoEgresadoController@index`**: Identifica al docente autenticado. Si tiene `seguimiento-egresados` y no es personal de gestión directiva, filtra automáticamente la consulta a los egresados asignados en sus grupos activos:
   ```php
   elseif (($authUser->hasRole('seguimiento-egresados') || $this->esEncuestador($authUser)) && !$this->esPersonalGestion($authUser)) {
       $gruposActivos = GrupoEntrevistador::where('estado', 'activo')->get();
       $asignadosIds = $gruposActivos->filter(fn ($g) => in_array($authUser->id, (array)$g->entrevistadores))
           ->flatMap(fn ($g) => (array)$g->egresados_ids)
           ->unique()->toArray();
       $egresadosQuery->whereIn('id', $asignadosIds);
   }
   ```
2. **`SeguimientoEgresadoController@obtenerEpisodios` y `@guardarActualizacion`**: Verifican mediante `SeguimientoAutorizacionService::puedeConsultarEgresado()` que el docente tenga asignado al egresado correspondiente antes de devolver o modificar cualquier registro.
3. **`SeguimientoLaboratorioController@index` y `@exportar`**: Bloquean el acceso analítico con HTTP 403 a cualquier docente con `seguimiento-egresados` que no cuente con el permiso explícito `seguimiento-egresados.ver-analitica`.

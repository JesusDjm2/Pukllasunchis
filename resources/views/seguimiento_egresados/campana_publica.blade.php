<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Campaña de Actualización de Egresados · EESP Pukllasunchis</title>
    <link rel="icon" href="{{ asset('admin/img/favicon.png') }}" type="image/x-icon">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        :root {
            --pk-primary: #10b981;
            --pk-primary-dark: #059669;
            --pk-gold: #f59e0b;
            --pk-dark: #1e293b;
            --pk-card-bg: #ffffff;
            --pk-page-bg: #f8fafc;
            --pk-border: #e2e8f0;
            --pk-text-main: #334155;
            --pk-text-muted: #64748b;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
        }

        body {
            background-color: var(--pk-page-bg);
            color: var(--pk-text-main);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            line-height: 1.5;
        }

        /* Top Header */
        .pk-header {
            background: linear-gradient(135deg, #134e4a 0%, #064e3b 100%);
            color: #ffffff;
            padding: 2.2rem 1.2rem;
            text-align: center;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            position: relative;
        }

        .pk-header img {
            max-height: 52px;
            margin-bottom: 0.85rem;
            filter: drop-shadow(0 2px 4px rgba(0,0,0,0.2));
        }

        .pk-header .camp-chip {
            display: inline-block;
            background: rgba(255, 255, 255, 0.18);
            backdrop-filter: blur(8px);
            padding: 0.35rem 0.9rem;
            border-radius: 999px;
            font-size: 0.8rem;
            font-weight: 700;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            color: #ecfdf5;
            margin-bottom: 0.75rem;
            border: 1px solid rgba(255, 255, 255, 0.25);
        }

        .pk-header h1 {
            font-size: 1.65rem;
            font-weight: 800;
            letter-spacing: -0.02em;
            margin-bottom: 0.4rem;
        }

        .pk-header p {
            font-size: 0.92rem;
            color: #cbd5e1;
            max-width: 580px;
            margin: 0 auto;
        }

        /* Container */
        .pk-main {
            flex: 1;
            max-width: 680px;
            width: 100%;
            margin: -1.2rem auto 2.5rem;
            padding: 0 1rem;
            position: relative;
            z-index: 2;
        }

        .pk-card {
            background: var(--pk-card-bg);
            border-radius: 18px;
            padding: 1.8rem 1.6rem;
            box-shadow: 0 10px 30px rgba(15, 23, 42, 0.07);
            border: 1px solid var(--pk-border);
        }

        .section-title {
            display: flex;
            align-items: center;
            gap: 0.65rem;
            font-size: 1.05rem;
            font-weight: 700;
            color: #0f172a;
            margin-top: 1.5rem;
            margin-bottom: 1.1rem;
            padding-bottom: 0.6rem;
            border-bottom: 2px solid #f1f5f9;
        }

        .section-title:first-of-type {
            margin-top: 0;
        }

        .section-title .step-badge {
            width: 26px;
            height: 26px;
            border-radius: 50%;
            background: var(--pk-primary);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.82rem;
            font-weight: 800;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }

        .col-full {
            grid-column: 1 / -1;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 0.35rem;
        }

        .form-group label {
            font-size: 0.84rem;
            font-weight: 600;
            color: #475569;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            padding: 0.72rem 0.9rem;
            border: 1.5px solid var(--pk-border);
            border-radius: 10px;
            font-size: 0.92rem;
            color: #1e293b;
            background: #ffffff;
            transition: all 0.2s ease;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--pk-primary);
            box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.15);
        }

        .radio-pills {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
        }

        .radio-pill-item {
            flex: 1 1 45%;
        }

        .radio-pill-item input {
            display: none;
        }

        .radio-pill-item label {
            display: block;
            padding: 0.65rem 0.85rem;
            background: #f8fafc;
            border: 1.5px solid var(--pk-border);
            border-radius: 10px;
            text-align: center;
            font-size: 0.85rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .radio-pill-item input:checked + label {
            background: #ecfdf5;
            border-color: var(--pk-primary);
            color: var(--pk-primary-dark);
            font-weight: 700;
        }

        .submit-btn {
            width: 100%;
            background: linear-gradient(135deg, var(--pk-primary) 0%, var(--pk-primary-dark) 100%);
            color: #ffffff;
            border: none;
            padding: 0.95rem 1.5rem;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 700;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            box-shadow: 0 4px 14px rgba(16, 185, 129, 0.35);
            transition: transform 0.15s ease, box-shadow 0.15s ease;
            margin-top: 1.8rem;
        }

        .submit-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(16, 185, 129, 0.45);
        }

        .submit-btn:active {
            transform: translateY(1px);
        }

        /* Success Card */
        .success-box {
            display: none;
            text-align: center;
            padding: 2.5rem 1.5rem;
        }

        .success-icon {
            width: 72px;
            height: 72px;
            border-radius: 50%;
            background: #dcfce7;
            color: var(--pk-primary-dark);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.2rem;
            margin: 0 auto 1.2rem;
        }

        .success-box h2 {
            font-size: 1.5rem;
            font-weight: 800;
            color: #064e3b;
            margin-bottom: 0.6rem;
        }

        .success-box p {
            color: #64748b;
            font-size: 0.95rem;
            max-width: 480px;
            margin: 0 auto 1.8rem;
        }

        /* Footer */
        .pk-footer {
            text-align: center;
            padding: 1.5rem;
            font-size: 0.8rem;
            color: #94a3b8;
        }

        @media (max-width: 640px) {
            .form-grid {
                grid-template-columns: 1fr;
            }
            .radio-pill-item {
                flex: 1 1 100%;
            }
            .pk-header {
                padding: 1.8rem 1rem;
            }
            .pk-header h1 {
                font-size: 1.35rem;
            }
        }
    </style>
</head>
<body>

    <header class="pk-header">
        <img src="{{ asset('admin/img/Logo-Pukllasunchis-blanco.png') }}" alt="EESP Pukllasunchis">
        <div>
            <span class="camp-chip"><i class="fab fa-whatsapp mr-1"></i> Campaña Semestral 2026-II · Egresados</span>
        </div>
        <h1>Actualización de Trayectoria Profesional</h1>
        <p>Tu respuesta nos ayuda a coordinar capacitaciones continuas, apoyo en titulación y convocatorias docentes.</p>
    </header>

    <main class="pk-main">
        <div class="pk-card">
            <!-- Confirmación Rápida R02 para egresados que ya participaron -->
            <div style="background:#f0fdf4; border:1px solid #86efac; border-radius:12px; padding:12px 14px; margin-bottom:1.5rem; display:flex; align-items:center; gap:10px;">
                <input type="checkbox" id="sin_cambios_public" name="sin_cambios" value="1" style="width:18px; height:18px; accent-color:#10b981; cursor:pointer;">
                <label for="sin_cambios_public" style="font-size:0.86rem; color:#065f46; cursor:pointer; font-weight:600; margin:0;">
                    ⚡ <strong>Mi situación sigue vigente:</strong> Reconfirmar mis datos laborales y académicos sin cambios para este semestre.
                </label>
            </div>

            <!-- Formulario -->
            <form id="camp-form">
                <!-- Paso 1: Datos Personales -->
                <div class="section-title">
                    <span class="step-badge">1</span>
                    <span>Tus datos de contacto</span>
                </div>

                <div class="form-grid">
                    <div class="form-group">
                        <label for="dni">DNI / Documento *</label>
                        <input type="text" id="dni" name="dni" placeholder="Ej. 70891234" required maxlength="12">
                    </div>

                    <div class="form-group">
                        <label for="nombre">Nombres y Apellidos completos *</label>
                        <input type="text" id="nombre" name="nombre" placeholder="Ej. Lucía Quispe Huamán" required>
                    </div>

                    <div class="form-group">
                        <label for="programa">Programa de formación *</label>
                        <select id="programa" name="programa" required>
                            <option value="">-- Selecciona --</option>
                            <option value="Educación Inicial">Educación Inicial</option>
                            <option value="Educación Primaria EIB">Educación Primaria EIB</option>
                            <option value="Educación Primaria">Educación Primaria</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="egreso">Año / Semestre de egreso</label>
                        <select id="egreso" name="egreso">
                            <option value="2025-II">2025-II</option>
                            <option value="2025-I">2025-I</option>
                            <option value="2024-II">2024-II</option>
                            <option value="2024-I">2024-I</option>
                            <option value="2023-II">2023-II</option>
                            <option value="2023-I">2023-I</option>
                            <option value="2022-II">2022-II</option>
                            <option value="2021-II">2021-II</option>
                            <option value="Anterior">Anterior a 2021</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="telefono">WhatsApp / Celular principal *</label>
                        <input type="tel" id="telefono" name="telefono" placeholder="Ej. 984 123 456" required>
                    </div>

                    <div class="form-group">
                        <label for="correo">Correo electrónico</label>
                        <input type="email" id="correo" name="correo" placeholder="ejemplo@correo.com">
                    </div>

                    <div class="form-group col-full">
                        <label for="residencia">Departamento / Provincia / Distrito donde vives actualmente</label>
                        <input type="text" id="residencia" name="residencia" placeholder="Ej. Cusco, Canchis, Sicuani">
                    </div>
                </div>

                <!-- Paso 2: Situación Académica -->
                <div class="section-title">
                    <span class="step-badge">2</span>
                    <span>Grados y Titulación</span>
                </div>

                <div class="form-grid">
                    <div class="form-group">
                        <label for="titulo_bach">Grado de Bachiller</label>
                        <select id="titulo_bach" name="titulo_bach">
                            <option value="Obtenido">Obtenido / Registrado</option>
                            <option value="En trámite">En trámite</option>
                            <option value="Pendiente">Pendiente</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="titulo_lic">Título Profesional Pedagógico</label>
                        <select id="titulo_lic" name="titulo_lic">
                            <option value="Titulado">Titulada / Licenciada</option>
                            <option value="Tesis en desarrollo">Tesis en desarrollo</option>
                            <option value="En sustentación">En proceso de sustentación</option>
                            <option value="Pendiente">Aún no iniciado</option>
                        </select>
                    </div>

                    <div class="form-group col-full">
                        <label for="apoyo_titulacion">¿Te gustaría recibir asesoría o acompañamiento de Pukllasunchis para titularte?</label>
                        <select id="apoyo_titulacion" name="apoyo_titulacion">
                            <option value="Sí, requiero asesoría en tesis">Sí, requiero asesoría en tesis o plan de trabajo</option>
                            <option value="Sí, información sobre trámites">Sí, información sobre requisitos y trámites</option>
                            <option value="No por el momento">No por el momento / Ya estoy titulado</option>
                        </select>
                    </div>
                </div>

                <!-- Paso 3: Situación Laboral -->
                <div class="section-title">
                    <span class="step-badge">3</span>
                    <span>Práctica profesional y empleo</span>
                </div>

                <div class="form-grid">
                    <div class="form-group col-full">
                        <label>¿Te encuentras trabajando actualmente? *</label>
                        <div class="radio-pills">
                            <div class="radio-pill-item">
                                <input type="radio" id="trab_si_doc" name="trabaja" value="Trabaja" checked>
                                <label for="trab_si_doc"><i class="fas fa-chalkboard-teacher mr-1"></i> Sí, en docencia o aula</label>
                            </div>
                            <div class="radio-pill-item">
                                <input type="radio" id="trab_si_edu" name="trabaja" value="Trabaja en gestión">
                                <label for="trab_si_edu"><i class="fas fa-school mr-1"></i> Sí, gestión o apoyo educativo</label>
                            </div>
                            <div class="radio-pill-item">
                                <input type="radio" id="trab_si_otro" name="trabaja" value="Trabaja otro sector">
                                <label for="trab_si_otro"><i class="fas fa-briefcase mr-1"></i> Sí, en otro rubro no educativo</label>
                            </div>
                            <div class="radio-pill-item">
                                <input type="radio" id="trab_no" name="trabaja" value="No trabaja">
                                <label for="trab_no"><i class="fas fa-search mr-1"></i> Buscando empleo / No labora</label>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="institucion">Nombre de la I.E. o centro laboral</label>
                        <input type="text" id="institucion" name="institucion" placeholder="Ej. I.E. 50012 / Colegio ...">
                    </div>

                    <div class="form-group">
                        <label for="cargo">Cargo o función</label>
                        <input type="text" id="cargo" name="cargo" placeholder="Ej. Docente de aula, Auxiliar, Director...">
                    </div>

                    <div class="form-group">
                        <label for="contrato">Tipo de contrato / Régimen</label>
                        <select id="contrato" name="contrato">
                            <option value="Contrato docente MINEDU">Contrato docente MINEDU (Concurso)</option>
                            <option value="Nombrado">Nombrado / Carrera Pública Magisterial</option>
                            <option value="Privado / Particular">Colegio privado / particular</option>
                            <option value="Servicios / Temporal">Locación de servicios / Suplencia</option>
                            <option value="No aplica">No aplica</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="trab_lugar">Lugar de trabajo (Distrito, Provincia)</label>
                        <input type="text" id="trab_lugar" name="trab_lugar" placeholder="Ej. San Jerónimo, Cusco">
                    </div>
                </div>

                <!-- Paso 4: Demandas Formativas -->
                <div class="section-title">
                    <span class="step-badge">4</span>
                    <span>Formación continua y comunidad</span>
                </div>

                <div class="form-grid">
                    <div class="form-group col-full">
                        <label for="temas">¿En qué temas te gustaría recibir talleres o diplomados de Pukllasunchis?</label>
                        <select id="temas" name="temas">
                            <option value="Planificación curricular y evaluación formativa">Planificación curricular y evaluación formativa</option>
                            <option value="Lengua Quechua y Didáctica EIB">Lengua Quechua y Didáctica EIB</option>
                            <option value="Pedagogía lúdica y educación infantil">Pedagogía lúdica y educación infantil</option>
                            <option value="Aulas multigrado y ruralidad">Aulas multigrado y ruralidad</option>
                            <option value="Investigación acción y titulación">Investigación acción y titulación</option>
                            <option value="Uso de tecnologías e IA en educación">Uso de tecnologías e IA en educación</option>
                        </select>
                    </div>

                    <div class="form-group col-full">
                        <label for="compartir">¿Te interesaría participar como expositor/a o compartir tus experiencias pedagógicas?</label>
                        <select id="compartir" name="compartir">
                            <option value="Sí, me gustaría compartir mi experiencia">Sí, me gustaría compartir mi experiencia</option>
                            <option value="Tal vez en el futuro">Tal vez en el futuro</option>
                            <option value="No por ahora">No por ahora</option>
                        </select>
                    </div>

                    <div class="form-group col-full">
                        <label for="necesidades">Mensaje, sugerencia o consulta para Pukllasunchis</label>
                        <textarea id="necesidades" name="necesidades" rows="3" placeholder="Cuéntanos tus inquietudes o requerimientos específicos..."></textarea>
                    </div>
                </div>

                <button type="submit" class="submit-btn" id="btn-submit">
                    <i class="fas fa-paper-plane"></i>
                    <span>Enviar actualización de datos</span>
                </button>
            </form>

            <!-- Mensaje de éxito -->
            <div class="success-box" id="success-box">
                <div class="success-icon">
                    <i class="fas fa-check"></i>
                </div>
                <h2>¡Datos actualizados con éxito!</h2>
                <p>Muchas gracias, <strong id="success-nombre">egresada/o</strong>. Tu información ha sido guardada directamente en el Sistema Institucional de EESP Pukllasunchis para la Campaña 2026-II.</p>
                <div style="margin-top: 1.5rem;">
                    <a href="https://pukllasunchis.edu.pe" target="_blank" style="display:inline-block;padding:0.75rem 1.4rem;background:#10b981;color:#fff;border-radius:10px;text-decoration:none;font-weight:700;">
                        <i class="fas fa-globe mr-1"></i> Ir a la página web institucional
                    </a>
                </div>
            </div>
        </div>
    </main>

    <footer class="pk-footer">
        <p>© {{ date('Y') }} Escuela de Educación Superior Pedagógica Pukllasunchis · Cusco, Perú</p>
        <p style="font-size: 0.72rem; margin-top: 4px;">Unidad de Bienestar, Empleabilidad y Seguimiento al Egresado</p>
    </footer>

    <script>
        const form = document.getElementById('camp-form');
        const submitBtn = document.getElementById('btn-submit');
        const successBox = document.getElementById('success-box');
        const successNombre = document.getElementById('success-nombre');
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        form.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Guardando datos...';

            const isSinCambios = document.getElementById('sin_cambios_public')?.checked || false;
            const payload = {
                id: 'CAMP-' + (document.getElementById('dni').value.trim() || 'PUBLIC'),
                nombre: document.getElementById('nombre').value.trim(),
                dni: document.getElementById('dni').value.trim(),
                sin_cambios: isSinCambios,
                cambio_empleo: isSinCambios ? 'Sin cambios' : 'Primer empleo',
                canal: 'campana_whatsapp_publica',
                telefono: document.getElementById('telefono').value.trim(),
                whatsapp: document.getElementById('telefono').value.trim(),
                correo: document.getElementById('correo').value.trim(),
                direccion_contacto: document.getElementById('residencia').value.trim(),
                res_distrito: document.getElementById('residencia').value.trim(),
                titulo_bach: document.getElementById('titulo_bach').value,
                titulo_lic: document.getElementById('titulo_lic').value,
                apoyo_titulacion: document.getElementById('apoyo_titulacion').value,
                trabaja: document.querySelector('input[name="trabaja"]:checked')?.value || 'Trabaja',
                institucion: document.getElementById('institucion').value.trim(),
                cargo: document.getElementById('cargo').value.trim(),
                contrato: document.getElementById('contrato').value,
                trab_distrito: document.getElementById('trab_lugar').value.trim(),
                temas: document.getElementById('temas').value,
                puedo_compartir: document.getElementById('compartir').value,
                necesidades: document.getElementById('necesidades').value.trim()
            };

            try {
                const response = await fetch("{{ route('seguimiento-egresados.actualizar') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(payload)
                });

                const data = await response.json();

                if (response.ok && data.success) {
                    form.style.display = 'none';
                    successNombre.textContent = payload.nombre || 'estimada/o colega';
                    successBox.style.display = 'block';
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                } else {
                    alert('Error al guardar la información: ' + (data.message || 'Por favor revisa tus datos e intenta nuevamente.'));
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = '<i class="fas fa-paper-plane"></i> <span>Enviar actualización de datos</span>';
                }
            } catch (err) {
                console.error(err);
                alert('No se pudo conectar con el servidor institucional. Por favor verifica tu conexión.');
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fas fa-paper-plane"></i> <span>Enviar actualización de datos</span>';
            }
        });
    </script>
</body>
</html>
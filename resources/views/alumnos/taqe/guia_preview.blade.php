@extends('layouts.alumno')
@section('titulo', "Investigaciones — Guía Visual")
@section('contenido')
    @include('alumnos.partials.ui')
    @include('taqe._estilos')

    <div class="al-ui">
        {{-- ── Hero del Dashboard ── --}}
        <section class="al-hero">
            <div class="al-hero-fila">
                <div class="al-hero-texto">
                    <div class="al-kicker"><i class="fas fa-flask"></i> Investigaciones</div>
                    <h1>Mis grupos de investigación</h1>
                    <p class="al-hero-sub">Forma tu equipo, sube las evidencias de tu proceso y presenta tu trabajo final.</p>
                    @if ($modo === 'grupo')
                        <div class="al-chips">
                            <span class="al-chip"><i class="fas fa-users"></i> 1 grupo</span>
                            <span class="al-chip"><i class="fas fa-images"></i> 3 evidencias</span>
                            <span class="al-chip"><i class="fas fa-spinner"></i> En proceso</span>
                        </div>
                    @endif
                </div>
                <div class="al-hero-acciones">
                    <button type="button" class="al-btn al-btn-oro">
                        <i class="fas fa-plus"></i> {{ $modo === 'grupo' ? 'Nuevo grupo' : 'Crear mi primer grupo' }}
                    </button>
                    <a href="javascript:void(0)" class="al-btn al-btn-claro"><i class="fas fa-arrow-left"></i> Volver</a>
                </div>
            </div>
        </section>

        {{-- ── Invitación pendiente (visible en modo inicio para ilustrar) ── --}}
        @if ($modo === 'inicio')
            <div class="tq-invitacion" style="margin-bottom: 16px;">
                <span class="tq-invitacion-icono"><i class="fas fa-envelope-open-text"></i></span>
                <div class="tq-invitacion-texto">
                    <div class="tq-invitacion-titulo">Te invitaron al grupo <strong>Los Yachaykuna</strong></div>
                    <small>Práctica e Investigación II · Invitación de Anyi Kely Condori Castillo</small>
                </div>
                <div class="tq-invitacion-acciones">
                    <button type="button" class="al-btn al-btn-petroleo al-btn-sm" style="background: #0e9467; border-color: #0e9467; color: white;">
                        <i class="fas fa-check"></i> Aceptar
                    </button>
                    <button type="button" class="al-btn al-btn-borde al-btn-sm">
                        <i class="fas fa-times"></i> Rechazar
                    </button>
                </div>
            </div>
        @endif

        {{-- ── Proyecto Integrador del Ciclo II ── --}}
        <div class="al-card" style="margin-bottom: 16px;">
            <div class="tq-franja">
                <span class="tq-franja-icono"><i class="fas fa-project-diagram"></i></span>
                <div class="tq-franja-texto">
                    <small>Tu Proyecto Integrador · Ciclo II</small>
                    <strong>Escuela y Territorio: Diagnóstico Socio-Cultural.</strong>
                    <span><i class="fas fa-box-open mr-1" style="color: var(--al-oro);"></i>Mapa parlante y tríptico descriptivo del territorio sagrado de una Institución Educativa</span>
                </div>
                <div class="tq-franja-acciones">
                    <button type="button" class="al-btn al-btn-petroleo al-btn-sm">
                        <i class="fas fa-book-open"></i> Ver proyecto
                    </button>
                    <button type="button" class="al-btn al-btn-borde al-btn-sm">
                        <i class="fas fa-route"></i> ¿Cómo funciona?
                    </button>
                </div>
            </div>
        </div>

        @if ($modo === 'inicio')
            {{-- ── Estado inicial sin grupo formado ── --}}
            <div class="al-card">
                <div class="al-vacio py-5" style="text-align: center; padding: 40px 20px;">
                    <i class="fas fa-flask" style="font-size: 2.8rem; color: #b87c28; margin-bottom: 12px; display: inline-block;"></i>
                    <div class="font-weight-bold mb-1" style="color: #1e293b; font-size: 1.15rem;">Aún no perteneces a ningún grupo de Investigaciones este periodo</div>
                    <div class="small mb-3" style="color: #64748b; font-size: 0.92rem;">Crea uno para el curso de <strong>Práctica e Investigación II</strong> y suma a tus compañeros; ellos aceptan la invitación desde aquí mismo.</div>
                    <button type="button" class="al-btn al-btn-oro" style="padding: 10px 22px; font-size: 0.95rem;">
                        <i class="fas fa-plus"></i> Crear mi primer grupo de Investigaciones
                    </button>
                </div>
            </div>
        @elseif ($modo === 'crear')
            {{-- ── Fondo del dashboard con el modal abierto encima ── --}}
            <div class="al-card">
                <div class="al-vacio py-5" style="text-align: center; padding: 40px 20px; opacity: 0.4;">
                    <i class="fas fa-flask" style="font-size: 2.8rem; color: #b87c28; margin-bottom: 12px; display: inline-block;"></i>
                    <div class="font-weight-bold mb-1" style="color: #1e293b; font-size: 1.15rem;">Aún no perteneces a ningún grupo de Investigaciones este periodo</div>
                </div>
            </div>

            {{-- Modal visible forzado para la captura --}}
            <div class="modal fade show" id="modalCrearGrupo" tabindex="-1" role="dialog" style="display: block; position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: 1050; overflow-x: hidden; overflow-y: auto;">
                <div class="modal-dialog modal-dialog-centered" role="document" style="max-width: 580px; margin: 2rem auto;">
                    <div class="modal-content tq-modal" style="box-shadow: 0 15px 35px rgba(0,0,0,0.3); border: 2px solid #b87c28; border-radius: 10px;">
                        <div class="modal-header" style="background: #33445a; color: white; border-top-left-radius: 8px; border-top-right-radius: 8px;">
                            <h5 class="modal-title" style="font-weight: 700; font-size: 1.1rem; color: white;">
                                <i class="fas fa-flask mr-2" style="color: #f2c88e;"></i> Crear grupo de Investigaciones
                            </h5>
                            <button type="button" class="close text-white" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
                        </div>
                        <div class="modal-body" style="padding: 20px 24px; background: #ffffff;">
                            <p class="tq-nota mb-3" style="background: #f8fafc; padding: 10px 14px; border-left: 3px solid #b87c28; border-radius: 4px; font-size: 0.88rem; color: #475569;">
                                Elige el curso, ponle un nombre a tu grupo y suma a tus compañeros: ellos deberán aceptar para quedar integrados.
                            </p>
                            <div class="form-group mb-3">
                                <label class="tq-modal-etiqueta" style="font-weight: 700; color: #33445a; font-size: 0.88rem;">Curso articulador</label>
                                <select class="form-control" style="border: 1.5px solid #cbd5e1; font-weight: 600;">
                                    <option selected>Práctica e Investigación II (Ciclo II)</option>
                                </select>
                            </div>
                            <div class="form-group mb-3">
                                <label class="tq-modal-etiqueta" style="font-weight: 700; color: #33445a; font-size: 0.88rem;">Nombre del grupo</label>
                                <input type="text" class="form-control" value="Los Yachaykuna" style="border: 1.5px solid #cbd5e1; font-weight: 600; color: #1e293b;">
                            </div>
                            <div class="form-group mb-3">
                                <label class="tq-modal-etiqueta d-flex justify-content-between" style="font-weight: 700; color: #33445a; font-size: 0.88rem;">
                                    <span>Invitar compañeros de clase</span>
                                    <span class="text-muted font-weight-normal small">2 seleccionados</span>
                                </label>
                                <div style="max-height: 175px; overflow-y: auto; border: 1.5px solid #cbd5e1; border-radius: 6px; padding: 8px 12px; background: #f8fafc;">
                                    <div class="custom-control custom-checkbox py-1" style="border-bottom: 1px solid #e2e8f0;">
                                        <input type="checkbox" class="custom-control-input" id="c1" checked>
                                        <label class="custom-control-label font-weight-bold" for="c1" style="color: #1e293b; font-size: 0.88rem;">
                                            Anyi Kely Condori Castillo <span class="badge badge-light text-muted font-weight-normal">Ciclo II</span>
                                        </label>
                                    </div>
                                    <div class="custom-control custom-checkbox py-1" style="border-bottom: 1px solid #e2e8f0;">
                                        <input type="checkbox" class="custom-control-input" id="c2" checked>
                                        <label class="custom-control-label font-weight-bold" for="c2" style="color: #1e293b; font-size: 0.88rem;">
                                            Gladis Vivas Qquecho <span class="badge badge-light text-muted font-weight-normal">Ciclo II</span>
                                        </label>
                                    </div>
                                    <div class="custom-control custom-checkbox py-1" style="border-bottom: 1px solid #e2e8f0;">
                                        <input type="checkbox" class="custom-control-input" id="c3">
                                        <label class="custom-control-label" for="c3" style="color: #475569; font-size: 0.88rem;">
                                            Diana Jazzmin Salizar Cevallos <span class="badge badge-light text-muted font-weight-normal">Ciclo II</span>
                                        </label>
                                    </div>
                                    <div class="custom-control custom-checkbox py-1">
                                        <input type="checkbox" class="custom-control-input" id="c4">
                                        <label class="custom-control-label" for="c4" style="color: #475569; font-size: 0.88rem;">
                                            Isha Lucero Torres Jaquehua <span class="badge badge-light text-muted font-weight-normal">Ciclo II</span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer" style="background: #f1f5f9; border-top: 1px solid #cbd5e1; justify-content: space-between;">
                            <button type="button" class="al-btn al-btn-borde">Cancelar</button>
                            <button type="button" class="al-btn al-btn-oro" style="padding: 8px 20px;">
                                <i class="fas fa-check mr-1"></i> Crear grupo e invitar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-backdrop fade show" style="opacity: 0.6; z-index: 1040;"></div>
        @elseif ($modo === 'grupo')
            {{-- ── Estado con grupo activo: cabecera, pasos, equipo y las dos columnas ── --}}
            <article class="al-card tq-grupo" style="border: 1px solid #cbd5e1; box-shadow: 0 4px 12px rgba(0,0,0,0.06);">
                {{-- Cabecera del Grupo --}}
                <div class="al-card-cab" style="display: flex; align-items: center; justify-content: space-between; border-bottom: 1.5px solid #f1f5f9; padding-bottom: 12px; margin-bottom: 14px;">
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <span class="al-card-cab-icono" style="background: #33445a; color: #f2c88e; width: 44px; height: 44px; display: inline-flex; align-items: center; justify-content: center; border-radius: 8px; font-size: 1.3rem;">
                            <i class="fas fa-flask"></i>
                        </span>
                        <div>
                            <h2 style="font-size: 1.35rem; font-weight: 800; color: #1e293b; margin: 0;">Los Yachaykuna</h2>
                            <p style="font-size: 0.9rem; color: #64748b; margin: 1px 0 0 0;">Práctica e Investigación II · Ciclo II</p>
                            <div class="tq-proyecto" style="font-size: 0.82rem; color: #b87c28; font-weight: 600; margin-top: 2px;">
                                <i class="fas fa-project-diagram mr-1"></i> Escuela y Territorio: Diagnóstico Socio-Cultural.
                            </div>
                        </div>
                    </div>
                    <div>
                        <span class="al-badge is-info" style="background: #e0f2fe; color: #0369a1; padding: 6px 12px; border-radius: 20px; font-weight: 700; font-size: 0.85rem;">
                            <i class="fas fa-spinner mr-1 fa-spin"></i> En proceso
                        </span>
                    </div>
                </div>

                {{-- Barra de 3 pasos --}}
                <div class="tq-pasos" style="display: flex; align-items: center; justify-content: space-between; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px 20px; margin-bottom: 16px;">
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <span style="background: #0e9467; color: white; width: 24px; height: 24px; border-radius: 12px; display: inline-flex; align-items: center; justify-content: center; font-size: 0.8rem; font-weight: bold;"><i class="fas fa-check"></i></span>
                        <strong style="color: #0e9467; font-size: 0.9rem;">1. Equipo</strong>
                        <span class="badge badge-success ml-1" style="font-size: 0.72rem;">Completado</span>
                    </div>
                    <i class="fas fa-chevron-right text-muted" style="font-size: 0.8rem;"></i>
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <span style="background: #0e9467; color: white; width: 24px; height: 24px; border-radius: 12px; display: inline-flex; align-items: center; justify-content: center; font-size: 0.8rem; font-weight: bold;"><i class="fas fa-check"></i></span>
                        <strong style="color: #0e9467; font-size: 0.9rem;">2. Evidencias</strong>
                        <span class="badge badge-success ml-1" style="font-size: 0.72rem;">3 subidas</span>
                    </div>
                    <i class="fas fa-chevron-right text-muted" style="font-size: 0.8rem;"></i>
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <span style="background: #b87c28; color: white; width: 24px; height: 24px; border-radius: 12px; display: inline-flex; align-items: center; justify-content: center; font-size: 0.8rem; font-weight: bold;">3</span>
                        <strong style="color: #b87c28; font-size: 0.9rem;">3. Trabajo final</strong>
                        <span class="badge badge-warning text-white ml-1" style="background: #b87c28; font-size: 0.72rem;">Listo para entregar</span>
                    </div>
                </div>

                {{-- Sección Equipo --}}
                <section class="tq-equipo" style="margin-bottom: 20px;">
                    <div class="tq-equipo-cab" style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px;">
                        <div class="tq-bloque-titulo" style="font-weight: 700; color: #33445a; font-size: 0.95rem;">
                            <i class="fas fa-users mr-1 text-muted"></i> Equipo · 3 integrantes
                        </div>
                        <div class="tq-grupo-acciones" style="display: flex; gap: 8px;">
                            <button type="button" class="al-btn al-btn-borde al-btn-sm" style="font-size: 0.8rem; padding: 4px 10px;">
                                <i class="fas fa-user-cog mr-1"></i> Integrantes
                            </button>
                            <button type="button" class="al-btn al-btn-borde al-btn-sm" style="font-size: 0.8rem; padding: 4px 10px;">
                                <i class="fas fa-life-ring mr-1 text-danger"></i> Pedir ayuda
                            </button>
                            <button type="button" class="al-btn al-btn-sm tq-btn-salir" style="font-size: 0.8rem; padding: 4px 10px; color: #dc2626; border: 1px solid #fecaca; background: #fff5f5;">
                                <i class="fas fa-door-open mr-1"></i> Salir
                            </button>
                        </div>
                    </div>
                    <div class="tq-equipo-lista" style="display: flex; flex-wrap: wrap; gap: 10px;">
                        <div style="background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 6px; padding: 6px 12px; display: inline-flex; align-items: center; gap: 8px;">
                            <span style="width: 28px; height: 28px; background: #33445a; color: white; border-radius: 14px; display: inline-flex; align-items: center; justify-content: center; font-size: 0.75rem; font-weight: bold;">VC</span>
                            <span style="font-size: 0.85rem; font-weight: 700; color: #1e293b;">Valery Milena Ccapcha Blas</span>
                            <span class="badge badge-warning" style="background: #f2c88e; color: #7c2d12; font-size: 0.7rem; font-weight: bold;">Organizador/a</span>
                        </div>
                        <div style="background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 6px; padding: 6px 12px; display: inline-flex; align-items: center; gap: 8px;">
                            <span style="width: 28px; height: 28px; background: #0e9467; color: white; border-radius: 14px; display: inline-flex; align-items: center; justify-content: center; font-size: 0.75rem; font-weight: bold;">AC</span>
                            <span style="font-size: 0.85rem; font-weight: 600; color: #1e293b;">Anyi Kely Condori Castillo</span>
                            <span class="badge badge-success" style="font-size: 0.7rem;">Aceptado</span>
                        </div>
                        <div style="background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 6px; padding: 6px 12px; display: inline-flex; align-items: center; gap: 8px;">
                            <span style="width: 28px; height: 28px; background: #0e9467; color: white; border-radius: 14px; display: inline-flex; align-items: center; justify-content: center; font-size: 0.75rem; font-weight: bold;">GV</span>
                            <span style="font-size: 0.85rem; font-weight: 600; color: #1e293b;">Gladis Vivas Qquecho</span>
                            <span class="badge badge-success" style="font-size: 0.7rem;">Aceptado</span>
                        </div>
                    </div>
                </section>

                {{-- Las dos columnas: Evidencias del Proceso (izq) | Trabajo Final (der) --}}
                <div class="row">
                    {{-- COLUMNA IZQUIERDA: EVIDENCIAS --}}
                    <div class="col-lg-6 mb-4">
                        <section style="background: #f8fafc; border: 1.5px solid #cbd5e1; border-radius: 8px; padding: 14px; height: 100%; display: flex; flex-direction: column;">
                            <div style="display: flex; align-items: center; justify-content: space-between; border-bottom: 2px solid #e2e8f0; padding-bottom: 8px; margin-bottom: 8px;">
                                <h3 style="font-size: 1.02rem; font-weight: 800; color: #33445a; margin: 0;">
                                    <i class="fas fa-images mr-1" style="color: #0e9467;"></i> Evidencias del proceso
                                </h3>
                                <span class="badge badge-secondary" style="background: #33445a;">3 archivos</span>
                            </div>
                            <div style="font-size: 0.8rem; color: #64748b; margin-bottom: 12px;">
                                Fotos, audios, documentos o enlaces de campo. Pasa al trabajo final con <strong>↑ Pasar</strong>.
                            </div>

                            {{-- Lista de archivos de evidencias --}}
                            <div style="display: flex; flex-direction: column; gap: 8px; margin-bottom: 14px; flex-grow: 1;">
                                {{-- Evidencia 1: Audio --}}
                                <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 6px; padding: 8px 10px; display: flex; align-items: center; justify-content: space-between;">
                                    <div style="display: flex; align-items: center; gap: 10px; min-width: 0;">
                                        <i class="fas fa-file-audio" style="font-size: 1.4rem; color: #8b5cf6;"></i>
                                        <div style="min-width: 0;">
                                            <div style="font-size: 0.86rem; font-weight: 700; color: #1e293b; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                                Entrevista_sabio_comunidad_Huilloc.mp3
                                            </div>
                                            <div style="font-size: 0.72rem; color: #64748b;">Audio · 4.8 MB · Por Valery Ccapcha</div>
                                        </div>
                                    </div>
                                    <div style="display: flex; gap: 4px;">
                                        <button type="button" class="btn btn-outline-success btn-sm" style="font-size: 0.74rem; padding: 2px 7px; font-weight: 700;" title="Pasar al trabajo final">
                                            <i class="fas fa-arrow-up mr-1"></i> Pasar
                                        </button>
                                        <button type="button" class="btn btn-light btn-sm" style="font-size: 0.74rem; padding: 2px 7px;">
                                            <i class="fas fa-download"></i>
                                        </button>
                                    </div>
                                </div>

                                {{-- Evidencia 2: Foto --}}
                                <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 6px; padding: 8px 10px; display: flex; align-items: center; justify-content: space-between;">
                                    <div style="display: flex; align-items: center; gap: 10px; min-width: 0;">
                                        <i class="fas fa-file-image" style="font-size: 1.4rem; color: #0e9467;"></i>
                                        <div style="min-width: 0;">
                                            <div style="font-size: 0.86rem; font-weight: 700; color: #1e293b; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                                Registro_fotografico_IE_entorno.jpg
                                            </div>
                                            <div style="font-size: 0.72rem; color: #64748b;">Imagen · 2.1 MB · Por Anyi Condori</div>
                                        </div>
                                    </div>
                                    <div style="display: flex; gap: 4px;">
                                        <button type="button" class="btn btn-outline-success btn-sm" style="font-size: 0.74rem; padding: 2px 7px; font-weight: 700;" title="Pasar al trabajo final">
                                            <i class="fas fa-arrow-up mr-1"></i> Pasar
                                        </button>
                                        <button type="button" class="btn btn-light btn-sm" style="font-size: 0.74rem; padding: 2px 7px;">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                </div>

                                {{-- Evidencia 3: PDF --}}
                                <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 6px; padding: 8px 10px; display: flex; align-items: center; justify-content: space-between;">
                                    <div style="display: flex; align-items: center; gap: 10px; min-width: 0;">
                                        <i class="fas fa-file-pdf" style="font-size: 1.4rem; color: #dc2626;"></i>
                                        <div style="min-width: 0;">
                                            <div style="font-size: 0.86rem; font-weight: 700; color: #1e293b; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                                Guia_observacion_territorio.pdf
                                            </div>
                                            <div style="font-size: 0.72rem; color: #64748b;">Documento · 850 KB · Por Gladis Vivas</div>
                                        </div>
                                    </div>
                                    <div style="display: flex; gap: 4px;">
                                        <button type="button" class="btn btn-outline-success btn-sm" style="font-size: 0.74rem; padding: 2px 7px; font-weight: 700;" title="Pasar al trabajo final">
                                            <i class="fas fa-arrow-up mr-1"></i> Pasar
                                        </button>
                                        <button type="button" class="btn btn-light btn-sm" style="font-size: 0.74rem; padding: 2px 7px;">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            {{-- Subir evidencia --}}
                            <div style="background: #ffffff; border: 1.5px dashed #cbd5e1; border-radius: 6px; padding: 10px;">
                                <div style="font-weight: 700; font-size: 0.82rem; color: #33445a; margin-bottom: 6px;">
                                    <i class="fas fa-cloud-upload-alt mr-1"></i> Subir nueva evidencia
                                </div>
                                <div class="input-group input-group-sm mb-2">
                                    <div class="custom-file">
                                        <input type="file" class="custom-file-input" id="customFile1">
                                        <label class="custom-file-label" for="customFile1">Seleccionar archivo...</label>
                                    </div>
                                </div>
                                <button type="button" class="btn btn-sm btn-block" style="background: #33445a; color: white; font-weight: 600; font-size: 0.8rem;">
                                    <i class="fas fa-plus mr-1"></i> Agregar a evidencias
                                </button>
                            </div>
                        </section>
                    </div>

                    {{-- COLUMNA DERECHA: TRABAJO FINAL --}}
                    <div class="col-lg-6 mb-4">
                        <section style="background: #fffdfa; border: 2px solid #f2c88e; border-radius: 8px; padding: 14px; height: 100%; display: flex; flex-direction: column;">
                            <div style="display: flex; align-items: center; justify-content: space-between; border-bottom: 2px solid #fbdcbe; padding-bottom: 8px; margin-bottom: 8px;">
                                <h3 style="font-size: 1.02rem; font-weight: 800; color: #b87c28; margin: 0;">
                                    <i class="fas fa-flag-checkered mr-1"></i> Trabajo final para la feria
                                </h3>
                                <span class="badge badge-warning text-white" style="background: #b87c28;">2 productos</span>
                            </div>
                            <div style="font-size: 0.8rem; color: #7c2d12; margin-bottom: 12px;">
                                Los productos evaluables para la feria de investigación de tu Ciclo II:
                            </div>

                            {{-- Lista de archivos finales --}}
                            <div style="display: flex; flex-direction: column; gap: 8px; margin-bottom: 14px; flex-grow: 1;">
                                {{-- Producto Final 1: Mapa Parlante --}}
                                <div style="background: #ffffff; border: 1.5px solid #86efac; border-radius: 6px; padding: 8px 10px; display: flex; align-items: center; justify-content: space-between;">
                                    <div style="display: flex; align-items: center; gap: 10px; min-width: 0;">
                                        <i class="fas fa-file-pdf" style="font-size: 1.4rem; color: #dc2626;"></i>
                                        <div style="min-width: 0;">
                                            <div style="font-size: 0.86rem; font-weight: 700; color: #1e293b; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                                Mapa_parlante_territorio_sagrado_IE.pdf
                                            </div>
                                            <div style="font-size: 0.72rem; color: #0e9467; font-weight: 600;">★ Producto Final 1 · PDF 3.4 MB</div>
                                        </div>
                                    </div>
                                    <div>
                                        <button type="button" class="btn btn-light btn-sm" style="font-size: 0.74rem; padding: 2px 8px;">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                </div>

                                {{-- Producto Final 2: Tríptico Descriptivo --}}
                                <div style="background: #ffffff; border: 1.5px solid #86efac; border-radius: 6px; padding: 8px 10px; display: flex; align-items: center; justify-content: space-between;">
                                    <div style="display: flex; align-items: center; gap: 10px; min-width: 0;">
                                        <i class="fas fa-external-link-alt" style="font-size: 1.3rem; color: #2563eb;"></i>
                                        <div style="min-width: 0;">
                                            <div style="font-size: 0.86rem; font-weight: 700; color: #1e293b; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                                Triptico_diagnostico_sociocultural_canva
                                            </div>
                                            <div style="font-size: 0.72rem; color: #0e9467; font-weight: 600;">★ Producto Final 2 · Enlace web interactivo</div>
                                        </div>
                                    </div>
                                    <div>
                                        <button type="button" class="btn btn-light btn-sm" style="font-size: 0.74rem; padding: 2px 8px;">
                                            <i class="fas fa-external-link-alt"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            {{-- Caja Dorada de Entrega Oficial --}}
                            <div style="background: linear-gradient(135deg, #fffbeb, #fef3c7); border: 2px solid #b87c28; border-radius: 8px; padding: 12px; text-align: center;">
                                <div style="font-size: 0.84rem; color: #92400e; font-weight: 700; margin-bottom: 8px;">
                                    <i class="fas fa-award mr-1" style="color: #b87c28;"></i> ¿Listos para la Feria de Investigación?
                                </div>
                                <button type="button" class="btn btn-block" style="background: #b87c28; color: white; font-weight: 800; font-size: 0.95rem; padding: 10px; border-radius: 6px; box-shadow: 0 4px 10px rgba(184, 124, 40, 0.35);">
                                    <i class="fas fa-flag-checkered mr-2"></i> Entregar trabajo final
                                </button>
                                <div style="font-size: 0.72rem; color: #b45309; margin-top: 6px;">
                                    Acción del organizador(a). Podrán reabrir la entrega si necesitan corregir antes de la fecha límite.
                                </div>
                            </div>
                        </section>
                    </div>
                </div>
            </article>
        @endif
    </div>
@endsection

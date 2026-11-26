{{--
    Borrador automático en localStorage para formularios de calificación (FID y PPD).
    Evita perder notas digitadas si se cierra la pestaña, falla la red o caduca la sesión.
    Parámetros:
      - $draftKey (string): clave única por docente y curso.
      - $formSelector (string, opcional): selector CSS del/los formulario(s) a respaldar.
--}}
@php
    $draftFormSelector = $formSelector ?? 'form[method="POST"]';
@endphp
<script>
    (function() {
        var DRAFT_KEY = @json($draftKey);
        var FORM_SELECTOR = @json($draftFormSelector);
        var SAVE_SUCCESS = @json(Session::has('success'));
        var HAS_VALIDATION_ERRORS = @json($errors->any());
        var IGNORED_NAMES = { '_token': true, '_method': true, 'curso_id': true, 'docente_id': true, 'competencias[]': true };

        if (!window.localStorage) return;

        // Si el servidor confirmó guardado exitoso, limpiar cualquier borrador pendiente.
        if (SAVE_SUCCESS) {
            try { localStorage.removeItem(DRAFT_KEY); } catch (e) {}
        }

        document.addEventListener('DOMContentLoaded', function() {
            var forms = Array.prototype.slice.call(document.querySelectorAll(FORM_SELECTOR)).filter(function(f) {
                return f.id !== 'logout-form-docente' && !f.classList.contains('d-none');
            });
            if (!forms.length) return;

            var isDirty = false;
            var isSubmitting = false;
            var saveTimer = null;

            // Indicador discreto junto al primer botón de guardar
            var statusEl = document.createElement('span');
            statusEl.className = 'small text-muted ml-2 align-self-center';
            statusEl.style.fontWeight = '500';
            var savebar = forms[0].querySelector('.docente-cal-savebar');
            if (savebar) savebar.appendChild(statusEl);

            function getTrackableFields() {
                var list = [];
                forms.forEach(function(form) {
                    var els = form.querySelectorAll('input[name], select[name], textarea[name]');
                    els.forEach(function(el) {
                        var name = el.getAttribute('name');
                        if (!name || IGNORED_NAMES[name]) return;
                        if (el.type === 'hidden' || el.readOnly || el.disabled) return;
                        list.push(el);
                    });
                });
                return list;
            }

            function formatTime(ts) {
                var d = new Date(ts);
                return d.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
            }

            function saveDraft() {
                var payload = { updatedAt: Date.now(), fields: {} };
                getTrackableFields().forEach(function(el) {
                    var name = el.getAttribute('name');
                    if (el.type === 'checkbox' || el.type === 'radio') {
                        if (el.checked) payload.fields[name] = el.value;
                    } else {
                        payload.fields[name] = el.value;
                    }
                });
                try {
                    localStorage.setItem(DRAFT_KEY, JSON.stringify(payload));
                    isDirty = true;
                    statusEl.textContent = 'Borrador local guardado (' + formatTime(payload.updatedAt) + ')';
                } catch (e) {
                    // Ignorar si localStorage está lleno o bloqueado
                }
            }

            function scheduleSave() {
                isDirty = true;
                clearTimeout(saveTimer);
                saveTimer = setTimeout(saveDraft, 400);
            }

            // Restaurar borrador si existe y no venimos de un guardado exitoso ni de errores de validación (donde ya actúa old())
            if (!SAVE_SUCCESS && !HAS_VALIDATION_ERRORS) {
                try {
                    var raw = localStorage.getItem(DRAFT_KEY);
                    if (raw) {
                        var parsed = JSON.parse(raw);
                        var fields = parsed && parsed.fields ? parsed.fields : null;
                        if (fields) {
                            var restoredCount = 0;
                            getTrackableFields().forEach(function(el) {
                                var name = el.getAttribute('name');
                                if (!Object.prototype.hasOwnProperty.call(fields, name)) return;
                                var savedVal = fields[name];
                                if (el.type === 'checkbox' || el.type === 'radio') {
                                    var shouldCheck = (el.value === savedVal);
                                    if (el.checked !== shouldCheck) {
                                        el.checked = shouldCheck;
                                        el.dispatchEvent(new Event('change', { bubbles: true }));
                                        restoredCount++;
                                    }
                                } else if (String(el.value) !== String(savedVal)) {
                                    el.value = savedVal;
                                    el.dispatchEvent(new Event('input', { bubbles: true }));
                                    el.dispatchEvent(new Event('change', { bubbles: true }));
                                    restoredCount++;
                                }
                            });

                            if (restoredCount > 0) {
                                isDirty = true;
                                var banner = document.createElement('div');
                                banner.className = 'alert alert-info alert-dismissible fade show shadow-sm';
                                banner.setAttribute('role', 'alert');
                                banner.innerHTML =
                                    '<i class="fas fa-history mr-1"></i> ' +
                                    '<strong>Borrador local restaurado:</strong> se recuperaron ' + restoredCount +
                                    ' campo(s) sin guardar de las ' + formatTime(parsed.updatedAt || Date.now()) +
                                    '. Revisa y presiona <strong>Guardar/Actualizar</strong> para confirmar en el sistema. ' +
                                    '<button type="button" class="btn btn-sm btn-outline-secondary ml-2 py-0" data-discard-draft>Descartar borrador</button>' +
                                    '<button type="button" class="close" data-dismiss="alert" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>';
                                var container = document.querySelector('.docente-ui-page');
                                if (container) {
                                    container.insertBefore(banner, container.children[1] || null);
                                    var discardBtn = banner.querySelector('[data-discard-draft]');
                                    if (discardBtn) {
                                        discardBtn.addEventListener('click', function() {
                                            try { localStorage.removeItem(DRAFT_KEY); } catch (e) {}
                                            isDirty = false;
                                            window.location.reload();
                                        });
                                    }
                                }
                                statusEl.textContent = 'Borrador restaurado (' + restoredCount + ' cambios)';
                            } else {
                                localStorage.removeItem(DRAFT_KEY);
                            }
                        }
                    }
                } catch (e) {}
            }

            forms.forEach(function(form) {
                form.addEventListener('input', scheduleSave);
                form.addEventListener('change', scheduleSave);
                form.addEventListener('submit', function() {
                    isSubmitting = true;
                    saveDraft();
                });
            });

            window.addEventListener('beforeunload', function(e) {
                if (isDirty && !isSubmitting) {
                    e.preventDefault();
                    e.returnValue = '';
                }
            });
        });
    })();
</script>

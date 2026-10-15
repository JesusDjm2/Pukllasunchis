    <script>
        // ── Alertas del dashboard (SweetAlert2, con el diseño de las tarjetas) ──
        var docenteSwalToast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 4200,
            timerProgressBar: true,
            customClass: { popup: 'docente-swal-toast' },
            didOpen: function (toast) {
                toast.addEventListener('mouseenter', Swal.stopTimer);
                toast.addEventListener('mouseleave', Swal.resumeTimer);
            },
        });

        function docenteConfirmar(opciones) {
            return Swal.fire({
                title: opciones.title,
                text: opciones.text,
                icon: opciones.icon || 'warning',
                showCancelButton: true,
                confirmButtonText: opciones.confirmText || 'Sí, continuar',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: opciones.confirmColor || '#4e73df',
                cancelButtonColor: '#858796',
                reverseButtons: true,
                customClass: { popup: 'docente-swal-popup' },
            });
        }

        @if (session('success'))
            docenteSwalToast.fire({ icon: 'success', title: @json(session('success')) });
        @endif
        @if (session('error'))
            docenteSwalToast.fire({ icon: 'error', title: @json(session('error')) });
        @endif
        @if ($errors->has('silabo'))
            Swal.fire({
                icon: 'error',
                title: 'Atención, {{ $primerNombre }}',
                text: @json($errors->first('silabo')),
                confirmButtonColor: '#4e73df',
                customClass: { popup: 'docente-swal-popup' },
            });
        @endif

        function updateFileName(input, elementId) {
            var fileNameDisplay = document.getElementById(elementId);
            if (!fileNameDisplay) {
                return;
            }
            if (input.files.length > 0) {
                fileNameDisplay.textContent = input.files[0].name;
            } else {
                fileNameDisplay.textContent = '';
            }
        }

        function reusarSilabo(cursoId) {
            var select = document.getElementById('reuse-select-' + cursoId);
            if (!select || !select.value) return;

            docenteConfirmar({
                title: '¿Reusar este sílabo?',
                text: 'Se copiará como base para el periodo actual. Podrás ajustarlo antes de darlo por terminado.',
                icon: 'question',
                confirmText: 'Sí, reusar',
            }).then(function (resultado) {
                if (!resultado.isConfirmed) return;

                var form = document.createElement('form');
                form.method = 'POST';
                form.action = '{{ url('/silabos') }}/' + select.value + '/reuse';

                var csrf = document.createElement('input');
                csrf.type = 'hidden';
                csrf.name = '_token';
                csrf.value = '{{ csrf_token() }}';
                form.appendChild(csrf);

                document.body.appendChild(form);
                form.submit();
            });
        }

        function confirmarEliminarClassroom(formId) {
            docenteConfirmar({
                title: '¿Eliminar estos campos?',
                text: 'Se borrará el enlace de Classroom y el código de este curso.',
                icon: 'warning',
                confirmText: 'Sí, eliminar',
                confirmColor: '#e74a3b',
            }).then(function (resultado) {
                if (!resultado.isConfirmed) return;

                var form = document.getElementById(formId);
                if (!form) return;

                var campoDelete = document.createElement('input');
                campoDelete.type = 'hidden';
                campoDelete.name = 'delete';
                campoDelete.value = 'true';
                form.appendChild(campoDelete);
                form.submit();
            });
        }

        function habilitarEdicionClassroom(cursoId) {
            var form = document.getElementById('classroom-form-' + cursoId);
            if (!form) return;

            form.querySelectorAll('.docente-classroom-input').forEach(function (input) {
                input.dataset.original = input.value;
                input.readOnly = false;
            });
            form.querySelectorAll('#classroom-actions-edit-' + cursoId + ' button').forEach(function (btn) {
                btn.disabled = false;
            });

            document.getElementById('classroom-actions-view-' + cursoId).classList.add('d-none');
            document.getElementById('classroom-actions-edit-' + cursoId).classList.remove('d-none');

            var primero = form.querySelector('.docente-classroom-input');
            if (primero) {
                primero.focus();
                primero.select();
            }
        }

        function cancelarEdicionClassroom(cursoId) {
            var form = document.getElementById('classroom-form-' + cursoId);
            if (!form) return;

            form.querySelectorAll('.docente-classroom-input').forEach(function (input) {
                if (input.dataset.original !== undefined) {
                    input.value = input.dataset.original;
                }
                input.readOnly = true;
            });
            form.querySelectorAll('#classroom-actions-edit-' + cursoId + ' button').forEach(function (btn) {
                btn.disabled = true;
            });

            document.getElementById('classroom-actions-edit-' + cursoId).classList.add('d-none');
            document.getElementById('classroom-actions-view-' + cursoId).classList.remove('d-none');
        }

        function mostrarTabla(tipo) {
            var tablaPPD = document.getElementById('tablappd');
            var tablaFID = document.getElementById('tablafid');
            var btnFid = document.getElementById('btn-tab-fid');
            var btnPpd = document.getElementById('btn-tab-ppd');
            if (!tablaPPD || !tablaFID || !btnFid || !btnPpd) {
                return;
            }
            if (tipo === 'ppd') {
                if (window.calificarCrossfade) {
                    window.calificarCrossfade(tablaPPD, [tablaFID]);
                } else {
                    tablaPPD.style.display = 'block';
                    tablaFID.style.display = 'none';
                }
                btnPpd.classList.add('active');
                btnFid.classList.remove('active');
                btnPpd.setAttribute('aria-pressed', 'true');
                btnFid.setAttribute('aria-pressed', 'false');
            } else if (tipo === 'fid') {
                if (window.calificarCrossfade) {
                    window.calificarCrossfade(tablaFID, [tablaPPD]);
                } else {
                    tablaFID.style.display = 'block';
                    tablaPPD.style.display = 'none';
                }
                btnFid.classList.add('active');
                btnPpd.classList.remove('active');
                btnFid.setAttribute('aria-pressed', 'true');
                btnPpd.setAttribute('aria-pressed', 'false');
            }
        }

        $(document).ready(function() {
            $('#confirmDeleteModal').on('show.bs.modal', function(e) {
                var button = $(e.relatedTarget);
                var url = button.data('href');
                $(this).find('#confirm-delete').data('href', url);
            });

            $('#confirm-delete').on('click', function() {
                var url = $(this).data('href');
                if (!url) {
                    return;
                }
                var form = $('<form>', {
                    method: 'POST',
                    action: url
                }).append(
                    $('<input>', {
                        name: '_token',
                        value: $('meta[name="csrf-token"]').attr('content'),
                        type: 'hidden'
                    })
                ).append(
                    $('<input>', {
                        name: '_method',
                        value: 'DELETE',
                        type: 'hidden'
                    })
                );
                $('body').append(form);
                form.submit();
            });
        });
    </script>

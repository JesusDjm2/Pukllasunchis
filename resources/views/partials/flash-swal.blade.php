{{--
    Mensajes flash de sesión vía el sistema propio de alertas (PkAlert, sobre
    SweetAlert2) en vez del clásico <div class="alert ...">. Opt-in: se incluye
    explícitamente en cada vista que quiera adoptarlo (no está en los layouts
    para no duplicar mensajes en las vistas que aún no migraron su bloque
    manual @if (session('success'))...@endif).

    Requiere que el layout cargue SweetAlert2 + public/js/puklla-alert.js
    (ya lo hacen: admin, superadmin, docente, alumno, profesionalizacion).
--}}
@if (session('success') || session('error'))
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                if (typeof PkAlert === 'undefined') {
                    return;
                }
                @if (session('success'))
                    PkAlert.toast(@json(session('success')), 'success');
                @endif
                @if (session('error'))
                    PkAlert.toast(@json(session('error')), 'error');
                @endif
            });
        </script>
    @endpush
@endif

{{-- GSAP + animaciones sutiles, cargado solo por las vistas de docentes.calificaciones.* --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js"></script>
<script src="{{ asset('admin/js/calificar-animations.js') }}?v={{ filemtime(public_path('admin/js/calificar-animations.js')) }}"></script>

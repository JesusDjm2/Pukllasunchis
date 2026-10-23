{{-- Visor de fotos (popup) para elementos con data-foto="url". Ver public/js/pk-foto.js. --}}
@once
    <link rel="stylesheet" href="{{ asset('css/pk-foto.css') }}?v={{ filemtime(public_path('css/pk-foto.css')) }}">
    <script src="{{ asset('js/pk-foto.js') }}?v={{ filemtime(public_path('js/pk-foto.js')) }}" defer></script>
@endonce

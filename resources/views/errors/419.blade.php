@extends('errors::minimal')
@section('title', __('Sesión expirada'))
@section('code', '419')
@section('message')
    <div class="container text-center mt-5">
        <div class="card shadow-lg p-4 border-0" style="max-width: 500px; margin:auto;">
            <div class="card-body">
                <h1 class="display-4 text-danger fw-bold mb-3">
                    ¡Ups! 😅
                </h1>
                <p class="text-secondary fs-5 mb-4">
                    Tu sesión ha expirado por inactividad o el formulario ya no es válido.
                </p>
                <div class="d-grid gap-2 d-sm-flex justify-content-sm-center">

                    {{-- <a href="{{ url('/') }}"
                        style="padding: 0.5em; background:#d59d52; border-radius: 3px; color:#fff; text-decoration:none">
                        Ir al inicio
                    </a> --}}
                    <form action="{{ route('logout') }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit"
                            style="padding: 0.5em 1em; background:#d59d52; border:none; border-radius:3px; color:#fff; cursor:pointer;">
                            Ir al inicio
                        </button>
                    </form>
                </div>
                <p class="text-muted mt-4 small">
                    Si el problema persiste, intenta iniciar sesión nuevamente o contactar con soporte.
                </p>
            </div>
        </div>
    </div>
@endsection

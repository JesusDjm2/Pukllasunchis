@extends('errors::minimal')

@section('title', '404 - Página no encontrada')
@section('code', '404')

@section('message')
    <div class="text-center">
        <h2 class="fw-semibold mb-4">Página no encontrada</h2>

        <p class="text-light fs-5 mb-4">
            Ups... parece que esta página no existe o fue movida.
        </p>

        <div class="d-flex flex-column flex-sm-row justify-content-center gap-3">
            {{-- Botón: Volver al inicio --}}
            <a href="{{ url('/') }}"
                style="padding: 0.5em 1em; background:#d59d52; border:none; border-radius:8px;text-decoration:none; color:#fff;">
                Ir al inicio
            </a>

            {{-- Botón: Volver atrás --}}
            <button onclick="history.back()" class="btn btn-outline-light btn-lg px-4 fw-semibold">
                Volver atrás
            </button>
        </div>

        <p class="mt-5 text-light small">
            Si crees que esto es un error, contáctanos en:
            <a href="https://wa.me/51984529158/?text=Buen%20día,%20me%20gustaría%20más%20información%20por%20favor."
                target="_blank" class="text-warning text-decoration-none fw-bold">
                +51 984 529 158
            </a>
        </p>
    </div>
@endsection

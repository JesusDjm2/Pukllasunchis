@extends('layouts.bolsa')
@section('titulo')
    <title>Editar Usuario {{ $admin->name }}</title>
@endsection
@section('contenido')
    <div class="container-fluid">
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Editar datos de Usuario: <small class="text-primary">{{ $admin->apellidos }}
                    {{ $admin->name }}</small></h1>
            <a href="javascript:window.history.back();" class="d-none d-sm-inline-block btn btn-sm btn-danger shadow-sm">
                Volver
            </a>
        </div>
        <div class="row">
            <div class="col-lg-12">
                @if (session('success'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                @endif
                <form id="editForm" method="POST" action="{{ route('trabajo.update', $admin->id) }}" class="mt-3">
                    @csrf
                    @method('PUT')
                    @role('admin')
                        @include('bolsa.form-edit')
                    @endrole
                    @role('adminB')
                        @include('bolsa.form-edit')
                    @endrole
                    @role('alumnoB')
                        dfds
                    @endrole
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var roleSelect = document.getElementById('role');
            var adminFields = document.getElementById('admin-fields');

            roleSelect.addEventListener('change', function() {
                adminFields.style.display = this.value === 'alumno' ? 'block' : 'none';
            });
            if (roleSelect.value === 'alumno') {
                adminFields.style.display = 'block';
            }
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var programaSelector = document.getElementById('programa_id');
            var cicloSelector = document.getElementById('ciclo_id');
            programaSelector.addEventListener('change', function() {
                var programaId = programaSelector.value;
                cicloSelector.innerHTML = '<option disabled selected>Seleccionar Ciclo</option>';
                if (programaId) {
                    fetch('/obtener-ciclos/' + programaId)
                        .then(response => response.json())
                        .then(data => {
                            data.forEach(ciclo => {
                                var option = document.createElement('option');
                                option.value = ciclo.id;
                                option.text = ciclo.nombre;
                                cicloSelector.appendChild(option);
                            });
                        })
                        .catch(error => console.error('Error:', error));
                }
            });
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var roleSelect = document.getElementById('role');
            var adminFields = document.getElementById('admin-bolsa');
            roleSelect.addEventListener('change', function() {
                adminFields.style.display = this.value === 'alumno' ? 'block' : 'none';
            });
            if (roleSelect.value === 'alumno') {
                adminFields.style.display = 'block';
            }
        });
    </script>
@endsection

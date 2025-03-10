@extends('layouts.admin')

@section('contenido')
    <div class="container-fluid bg-white">
        <div class="d-sm-flex align-items-center justify-content-between mb-4 pt-3 pb-2">
            <h3 class="mb-0 font-weight-bold text-primary">Crear nuevo Enfoque</h3>
            <a href="{{ route('enfoques.index') }}" class="d-none d-sm-inline-block btn btn-sm btn-danger shadow-sm">
                Volver
            </a>
        </div> 

        <form action="{{ route('enfoques.store') }}" method="POST" autocomplete="off">
            @csrf
            <div class="row">                
                <div class="col-lg-12 mb-3">
                    <label for="nombre">Nombre del Enfoque:</label>
                    <input type="text" name="nombre" class="form-control form-control-sm" required>
                </div>
                <div class="col-lg-12 mb-3">
                    <label for="descripcion">Descripción:</label>
                    <textarea name="descripcion" class="form-control form-control-sm" required></textarea>
                </div>
                <div class="col-lg-12 mb-3">
                    <label for="observables">Observables:</label>
                    <textarea name="observables" class="form-control form-control-sm" required></textarea>
                </div>
                <div class="col-lg-12 mb-3">
                    <label for="concretas">Concretas:</label>
                    <textarea name="concretas" class="form-control form-control-sm" required></textarea>
                </div>
            </div>
            <button type="submit" class="mt-3 mb-3 btn btn-sm btn-primary">Guardar Enfoque</button>
        </form>
        
        {{-- <script>
            document.addEventListener('DOMContentLoaded', function() {
                var programaSelector = document.getElementById('programa_id');
                var cicloSelector = document.getElementById('ciclo_id');
                var cursoSelector = document.getElementById('curso_id');
        
                programaSelector.addEventListener('change', function() {
                    var programaId = programaSelector.value;
                    cicloSelector.innerHTML = '<option disabled selected>Seleccionar Ciclo</option>';
                    cursoSelector.innerHTML = '<option disabled selected>Seleccionar Curso</option>';
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
        
                cicloSelector.addEventListener('change', function() {
                    var cicloId = cicloSelector.value;
                    cursoSelector.innerHTML = '<option disabled selected>Seleccionar Curso</option>';
                    if (cicloId) {
                        fetch('/obtener-cursos/' + cicloId)
                            .then(response => response.json())
                            .then(data => {
                                data.forEach(curso => {
                                    var option = document.createElement('option');
                                    option.value = curso.id;
                                    option.text = curso.nombre;
                                    cursoSelector.appendChild(option);
                                });
                            })
                            .catch(error => console.error('Error:', error));
                    }
                });
            });
        </script> --}}
        
    </div>
@endsection

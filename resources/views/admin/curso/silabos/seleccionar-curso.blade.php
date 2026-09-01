@php $layout = auth()->user()?->hasRole('super-admin') ? 'layouts.superadmin' : 'layouts.admin'; @endphp
@extends($layout)

@section('contenido')
    <div class="container-fluid bg-white">
        <div class="d-sm-flex align-items-center justify-content-between mb-4 pt-3 pb-2"
            style="border-bottom: 1px dashed #80808078">
            <div>
                <h4 class="mb-0 font-weight-bold" style="color:#1e293b;">
                    <i class="fas fa-file-alt mr-2" style="color:#4e73df;"></i>Crear sílabo
                </h4>
                <small class="text-muted">Elige el curso para el que se registrará el sílabo</small>
            </div>
            <a href="{{ route('silabos.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-arrow-left mr-1"></i> Volver
            </a>
        </div>

        <div class="row justify-content-center">
            <div class="col-12 col-lg-7">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <form action="{{ route('silabos.create') }}" method="GET" autocomplete="off">
                            <div class="row">
                                <div class="col-lg-6 mb-3">
                                    <label for="programa_id">Programa:</label>
                                    <select id="programa_id" name="programa_id" class="form-control form-control-sm" required>
                                        <option selected disabled value="">Elegir Programa</option>
                                        @foreach ($programas as $programa)
                                            <option value="{{ $programa->id }}">{{ $programa->nombre }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-lg-6 mb-3">
                                    <label for="ciclo_id">Ciclo:</label>
                                    <select id="ciclo_id" name="ciclo_id" class="form-control form-control-sm" required disabled>
                                        <option selected disabled value="">Elegir Ciclo</option>
                                    </select>
                                </div>
                                <div class="col-lg-12 mb-3">
                                    <label for="curso_id">Curso:</label>
                                    <select id="curso_id" name="curso_id" class="form-control form-control-sm" required disabled>
                                        <option selected disabled value="">Elegir Curso</option>
                                    </select>
                                </div>
                            </div>
                            <div class="d-flex justify-content-end">
                                <a href="{{ route('silabos.index') }}" class="btn btn-outline-secondary mr-2">Cancelar</a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-arrow-right mr-1"></i> Continuar
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('programa_id').addEventListener('change', function() {
            var programaId = this.value;
            var selectCiclo = document.getElementById('ciclo_id');
            var selectCurso = document.getElementById('curso_id');
            selectCurso.innerHTML = '<option selected disabled value="">Elegir Curso</option>';
            selectCurso.disabled = true;

            if (!programaId) {
                selectCiclo.innerHTML = '<option selected disabled value="">Elegir Ciclo</option>';
                selectCiclo.disabled = true;
                return;
            }

            fetch('/obtener-ciclos/' + programaId)
                .then(response => response.json())
                .then(data => {
                    selectCiclo.innerHTML = '<option selected disabled value="">Elegir Ciclo</option>';
                    data.forEach(function (ciclo) {
                        var option = document.createElement('option');
                        option.value = ciclo.id;
                        option.text = ciclo.nombre;
                        selectCiclo.appendChild(option);
                    });
                    selectCiclo.disabled = false;
                });
        });

        document.getElementById('ciclo_id').addEventListener('change', function() {
            var cicloId = this.value;
            var selectCurso = document.getElementById('curso_id');

            if (!cicloId) {
                selectCurso.innerHTML = '<option selected disabled value="">Elegir Curso</option>';
                selectCurso.disabled = true;
                return;
            }

            fetch('/obtener-cursos/' + cicloId)
                .then(response => response.json())
                .then(data => {
                    data.sort((a, b) => a.nombre.localeCompare(b.nombre));
                    selectCurso.innerHTML = '<option selected disabled value="">Elegir Curso</option>';
                    data.forEach(function (curso) {
                        var option = document.createElement('option');
                        option.value = curso.id;
                        option.text = curso.nombre;
                        selectCurso.appendChild(option);
                    });
                    selectCurso.disabled = false;
                });
        });
    </script>
@endsection

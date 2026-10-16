{{-- Datos tal cual vienen de los archivos importados (secretaría, expedientes, PPD).
     Lo usan los pendientes de la lista y la ficha de quien ya está registrado
     (sus filas importadas no se muestran como otra persona). Espera $filas. --}}
@foreach ($filas as $fila)
    <div class="border rounded p-2 mb-2 tit-fila-importada">
        <div class="small font-weight-bold mb-1">
            <span class="badge badge-light border">{{ $fila['origen'] }}</span>
            <span class="text-muted">{{ $fila['archivo'] }} — importado {{ $fila['fecha'] }}</span>
        </div>
        <div class="row">
            @foreach ($fila['datos'] as $campo => $valor)
                <div class="col-md-4 small mb-1">
                    <strong>{{ $campo }}:</strong> {{ \Illuminate\Support\Str::limit($valor, 80) }}
                </div>
            @endforeach
        </div>
    </div>
@endforeach
@once
    <style>
        .tit-fila-importada { background: #fff; }
        .dark-mode .tit-fila-importada, .dim-mode .tit-fila-importada { background: rgba(255, 255, 255, .03); border-color: rgba(255, 255, 255, .12) !important; }
    </style>
@endonce

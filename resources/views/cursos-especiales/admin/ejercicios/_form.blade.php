{{-- Partial para create y edit de ejercicio --}}
@push('styles')
<style>
.cea-field-group { background:#fafbff; border:1px solid #e9ecef; border-radius:.75rem; padding:1.25rem 1.25rem 1rem; margin-bottom:1.25rem; }
.cea-field-group-title { font-size:.72rem; font-weight:800; text-transform:uppercase; letter-spacing:.07em; color:#4e73df; margin-bottom:.85rem; display:flex; align-items:center; gap:.4rem; }
.cea-opciones-container { border:1px solid #e9ecef; border-radius:.6rem; overflow:hidden; margin-bottom:.75rem; }
.cea-opcion-row {
    display:flex; align-items:center; gap:.5rem;
    padding:.5rem .75rem; border-bottom:1px solid #f0f0f0;
    background:#fff;
}
.cea-opcion-row:last-child { border-bottom:none; }
.cea-opcion-num {
    width:24px; height:24px; border-radius:50%;
    background:#e8f0ff; color:#4e73df; font-size:.72rem; font-weight:800;
    display:flex; align-items:center; justify-content:center; flex-shrink:0;
}
.cea-opcion-input { border:none; background:transparent; flex:1; font-size:.88rem; outline:none; }
.cea-add-opcion {
    display:inline-flex; align-items:center; gap:.4rem;
    background:#e8f0ff; color:#4e73df; border:none; border-radius:.5rem;
    padding:.35rem .85rem; font-size:.8rem; font-weight:700; cursor:pointer;
    transition:background .15s;
}
.cea-add-opcion:hover { background:#d0e0ff; }
.cea-remove-opcion { background:none; border:none; color:#e74a3b; cursor:pointer; font-size:.8rem; padding:.1rem; }
</style>
@endpush

{{-- Tipo --}}
<div class="cea-field-group">
    <div class="cea-field-group-title"><i class="fas fa-list"></i> Tipo de ejercicio</div>
    <div class="form-group mb-0">
        <select name="tipo" id="tipo-ejercicio" class="form-control">
            @foreach (['multiple' => 'Opción múltiple', 'completar' => 'Completar el espacio', 'emparejar' => 'Emparejar'] as $v => $l)
                <option value="{{ $v }}" {{ old('tipo', $ejercicio->tipo ?? 'multiple') === $v ? 'selected' : '' }}>{{ $l }}</option>
            @endforeach
        </select>
        <small class="form-text text-muted">La corrección automática funciona para todos los tipos.</small>
    </div>
</div>

{{-- Pregunta --}}
<div class="cea-field-group">
    <div class="cea-field-group-title"><i class="fas fa-question-circle"></i> Pregunta</div>
    <div class="form-group mb-0">
        <textarea name="pregunta" class="form-control @error('pregunta') is-invalid @enderror"
                  rows="3" required
                  placeholder="Ej: ¿Cómo se dice 'Buenos días' en Quechua?">{{ old('pregunta', $ejercicio->pregunta ?? '') }}</textarea>
        @error('pregunta') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
</div>

{{-- Opciones (solo múltiple) --}}
<div class="cea-field-group" id="bloque-opciones">
    <div class="cea-field-group-title"><i class="fas fa-list-ul"></i> Opciones de respuesta</div>
    <p class="small text-muted mb-2">Escribe cada opción. La respuesta correcta debe coincidir exactamente con una de estas opciones.</p>
    <div class="cea-opciones-container" id="opciones-container">
        @php
            $opcionesActuales = old('opciones_raw')
                ? explode("\n", old('opciones_raw'))
                : (isset($ejercicio) && $ejercicio->opciones ? $ejercicio->opciones : ['', '', '', '']);
        @endphp
        @foreach ($opcionesActuales as $i => $op)
            <div class="cea-opcion-row">
                <span class="cea-opcion-num">{{ chr(65 + $i) }}</span>
                <input type="text" class="cea-opcion-input opcion-input" value="{{ $op }}" placeholder="Opción {{ chr(65 + $i) }}">
                <button type="button" class="cea-remove-opcion" onclick="removeOpcion(this)" title="Eliminar">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        @endforeach
    </div>
    <input type="hidden" name="opciones_raw" id="opciones-raw-hidden">
    <button type="button" class="cea-add-opcion" onclick="addOpcion()">
        <i class="fas fa-plus"></i> Agregar opción
    </button>
</div>

{{-- Respuesta correcta --}}
<div class="cea-field-group">
    <div class="cea-field-group-title"><i class="fas fa-check-double"></i> Respuesta correcta</div>
    <div class="form-group mb-0">
        <input type="text" name="respuesta_correcta"
               class="form-control @error('respuesta_correcta') is-invalid @enderror"
               value="{{ old('respuesta_correcta', $ejercicio->respuesta_correcta ?? '') }}"
               placeholder="Escribe la respuesta correcta exactamente como aparece en las opciones" required>
        @error('respuesta_correcta') <div class="invalid-feedback">{{ $message }}</div> @enderror
        <small class="form-text text-muted">La comparación ignora mayúsculas/minúsculas y espacios al inicio/fin.</small>
    </div>
</div>

{{-- Metadatos --}}
<div class="cea-field-group">
    <div class="cea-field-group-title"><i class="fas fa-sliders-h"></i> Metadatos</div>
    <div class="row">
        <div class="col-md-4">
            <label class="font-weight-bold small">Puntaje máximo</label>
            <input type="number" name="puntaje_max" class="form-control" min="1"
                   value="{{ old('puntaje_max', $ejercicio->puntaje_max ?? 1) }}">
        </div>
        <div class="col-md-4">
            <label class="font-weight-bold small">Orden</label>
            <input type="number" name="orden" class="form-control" min="0"
                   value="{{ old('orden', $ejercicio->orden ?? 0) }}">
        </div>
    </div>
</div>

@push('scripts')
<script>
function addOpcion() {
    const container = document.getElementById('opciones-container');
    const rows = container.querySelectorAll('.cea-opcion-row');
    const letra = String.fromCharCode(65 + rows.length);
    const div = document.createElement('div');
    div.className = 'cea-opcion-row';
    div.innerHTML = `
        <span class="cea-opcion-num">${letra}</span>
        <input type="text" class="cea-opcion-input opcion-input" placeholder="Opción ${letra}">
        <button type="button" class="cea-remove-opcion" onclick="removeOpcion(this)">
            <i class="fas fa-times"></i>
        </button>`;
    container.appendChild(div);
    renumberOpciones();
}
function removeOpcion(btn) {
    const rows = document.querySelectorAll('.cea-opcion-row');
    if (rows.length <= 2) return;
    btn.closest('.cea-opcion-row').remove();
    renumberOpciones();
}
function renumberOpciones() {
    document.querySelectorAll('.cea-opcion-row').forEach((row, i) => {
        const num = row.querySelector('.cea-opcion-num');
        const inp = row.querySelector('.cea-opcion-input');
        const letra = String.fromCharCode(65 + i);
        if (num) num.textContent = letra;
        if (inp) inp.placeholder = 'Opción ' + letra;
    });
}

// Sync opciones to hidden input before submit
document.addEventListener('submit', function (e) {
    const inputs = document.querySelectorAll('.opcion-input');
    const vals = Array.from(inputs).map(i => i.value.trim()).filter(v => v !== '');
    const hidden = document.getElementById('opciones-raw-hidden');
    if (hidden) hidden.value = vals.join('\n');
}, true);

// Show/hide opciones for type multiple
const tipoSel = document.getElementById('tipo-ejercicio');
function toggleTipoUI() {
    const bloqueOp = document.getElementById('bloque-opciones');
    if (bloqueOp) bloqueOp.style.display = tipoSel.value === 'multiple' ? '' : 'none';
}
if (tipoSel) {
    tipoSel.addEventListener('change', toggleTipoUI);
    toggleTipoUI();
}
</script>
@endpush

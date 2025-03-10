<div class="row">
    <!-- DNI (PDF) -->
    <div class="form-group col-lg-4 mb-3">
        <label for="dni_pdf">DNI (PDF): <span class="text-danger">*</span></label>
        <input type="file" class="form-control form-control-sm @error('dni_pdf') is-invalid @enderror" id="dni_pdf"
            name="dni_pdf" accept=".pdf" {{ $postulante->dni_pdf ? '' : 'required' }}>
        @error('dni_pdf')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
        @if ($postulante->dni_pdf)
            <div class="mt-2">
                <a href="{{ asset( $postulante->dni_pdf) }}" target="_blank" class="btn btn-link">Ver PDF</a>
            </div>
        @else
            <small class="text-muted">No tiene ningún archivo guardado.</small>
        @endif
    </div>    
    
    <!-- Voucher de Pago (JPG, PNG o PDF) -->
    <div class="form-group col-lg-4 mb-3">
        <label for="voucher_pago">Voucher de Pago (JPG, PNG o PDF): <span class="text-danger">*</span></label>
        <input type="file" class="form-control form-control-sm @error('voucher_pago') is-invalid @enderror"
            id="voucher_pago" name="voucher_pago" accept=".jpg,.jpeg,.png,.pdf" {{ $postulante->voucher_pago ? '' : 'required' }}>
        @error('voucher_pago')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
        @if ($postulante->voucher_pago)
            @php $ext = pathinfo($postulante->voucher_pago, PATHINFO_EXTENSION); @endphp
            @if (in_array($ext, ['jpg', 'jpeg', 'png']))
                <div class="mt-2">
                    <img src="{{ asset($postulante->voucher_pago) }}" alt="Voucher de Pago" class="img-thumbnail" width="100">
                </div>
            @else
                <div class="mt-2">
                    <a href="{{ asset($postulante->voucher_pago) }}" target="_blank" class="btn btn-link">Ver PDF</a>
                </div>
            @endif
        @else
            <small class="text-muted">No tiene ningún archivo guardado.</small>
        @endif
    </div>

    <!-- Foto (JPG o PNG) -->
    <div class="form-group col-lg-4 mb-3">
        <label for="foto">Foto (JPG o PNG): <span class="text-danger">*</span></label>
        <input type="file" class="form-control form-control-sm @error('foto') is-invalid @enderror" id="foto"
            name="foto" accept=".jpg,.jpeg,.png" {{ $postulante->foto ? '' : 'required' }}>
        @error('foto')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
        @if ($postulante->foto)
            <div class="mt-2">
                <img src="{{ asset($postulante->foto) }}" alt="Foto" class="img-thumbnail" width="100">
            </div>
        @else
            <small class="text-muted">No tiene ninguna foto guardada.</small>
        @endif
    </div>

    <!-- Partida de Nacimiento (PDF) -->
    <div class="form-group col-lg-4 mb-3">
        <label for="partida_nacimiento_pdf">Partida de Nacimiento (PDF):</label>
        <input type="file" class="form-control form-control-sm @error('partida_nacimiento_pdf') is-invalid @enderror"
            id="partida_nacimiento_pdf" name="partida_nacimiento_pdf" accept=".pdf">
        @error('partida_nacimiento_pdf')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
        @if ($postulante->partida_nacimiento_pdf)
            <div class="mt-2">
                <a href="{{ asset($postulante->partida_nacimiento_pdf) }}" target="_blank" class="btn btn-link">Ver PDF</a>
            </div>
        @else
            <small class="text-muted">No tiene ningún archivo guardado.</small>
        @endif
    </div>
    
    <div class="form-group col-lg-4 mb-3">
        <label for="certificado_secundaria_pdf">Certificado de Secundaria (PDF):</label>
        <input type="file"
            class="form-control form-control-sm @error('certificado_secundaria_pdf') is-invalid @enderror"
            id="certificado_secundaria_pdf" name="certificado_secundaria_pdf" accept=".pdf">
        @error('certificado_secundaria_pdf')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
        @if ($postulante->certificado_secundaria_pdf)
            <div class="mt-2">
                <a href="{{ asset($postulante->certificado_secundaria_pdf) }}" target="_blank" class="btn btn-link">Ver PDF</a>
            </div>
        @else
            <small class="text-muted">No tiene ningún archivo guardado.</small>
        @endif
    </div>
    
    {{-- <div class="form-group col-lg-4 mb-3">
        <label for="declaracion_jurada_salud_pdf">Declaración Jurada de Salud (PDF):</label>
        <input type="file"
            class="form-control form-control-sm @error('declaracion_jurada_salud_pdf') is-invalid @enderror"
            id="declaracion_jurada_salud_pdf" name="declaracion_jurada_salud_pdf" accept=".pdf">
        @error('declaracion_jurada_salud_pdf')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
        @if ($postulante->declaracion_jurada_salud_pdf)
            <div class="mt-2">
                <a href="{{ asset($postulante->declaracion_jurada_salud_pdf) }}" target="_blank" class="btn btn-link">Ver PDF</a>
            </div>
        @else
            <small class="text-muted">No tiene ningún archivo guardado.</small>
        @endif
    </div> --}}

    {{-- <div class="form-group col-lg-4 mb-3">
        <label for="declaracion_jurada_documentos_pdf">Declaración Jurada de Documentos (PDF):</label>
        <input type="file"
            class="form-control form-control-sm @error('declaracion_jurada_documentos_pdf') is-invalid @enderror"
            id="declaracion_jurada_documentos_pdf" name="declaracion_jurada_documentos_pdf" accept=".pdf">
        @error('declaracion_jurada_documentos_pdf')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
        @if ($postulante->declaracion_jurada_documentos_pdf)
            <div class="mt-2">
                <a href="{{ asset($postulante->declaracion_jurada_documentos_pdf) }}" target="_blank" class="btn btn-link">Ver PDF</a>
            </div>
        @else
            <small class="text-muted">No tiene ningún archivo guardado.</small>
        @endif
    </div> --}}

    {{-- <div class="form-group col-lg-4 mb-3">
        <label for="declaracion_jurada_conectividad_pdf">Declaración Jurada de Conectividad (PDF):</label>
        <input type="file"
            class="form-control form-control-sm @error('declaracion_jurada_conectividad_pdf') is-invalid @enderror"
            id="declaracion_jurada_conectividad_pdf" name="declaracion_jurada_conectividad_pdf" accept=".pdf">
        @error('declaracion_jurada_conectividad_pdf')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
        @if ($postulante->declaracion_jurada_conectividad_pdf)
            <div class="mt-2">
                <a href="{{ asset($postulante->declaracion_jurada_conectividad_pdf) }}" target="_blank" class="btn btn-link">Ver PDF</a>
            </div>
        @else
            <small class="text-muted">No tiene ningún archivo guardado.</small>
        @endif
    </div> --}}
</div>

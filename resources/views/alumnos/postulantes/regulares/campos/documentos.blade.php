<div class="row">
    <div class="form-group col-lg-4 mb-3">
        <label for="dni_pdf">DNI (PDF): <span class="text-danger">*</span></label>
        <input type="file" class="form-control form-control-sm @error('dni_pdf') is-invalid @enderror" id="dni_pdf"
            name="dni_pdf" accept=".pdf" required>
        @error('dni_pdf')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>    
    <div class="form-group col-lg-4 mb-3">
        <label for="voucher_pago">Voucher de Pago (JPG, PNG o PDF): <span class="text-danger">*</span></label>
        <input type="file" class="form-control form-control-sm @error('voucher_pago') is-invalid @enderror"
            id="voucher_pago" name="voucher_pago" accept=".jpg,.jpeg,.png,.pdf" required>
        @error('voucher_pago')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="form-group col-lg-4 mb-3">
        <label for="foto">Foto (JPG o PNG): <span class="text-danger">*</span></label>
        <input type="file" class="form-control form-control-sm @error('foto') is-invalid @enderror" id="foto"
            name="foto" accept=".jpg,.jpeg,.png" required>
        @error('foto')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="form-group col-lg-4 mb-3">
        <label for="partida_nacimiento_pdf">Partida de Nacimiento (PDF):</label>
        <input type="file" class="form-control form-control-sm @error('partida_nacimiento_pdf') is-invalid @enderror"
            id="partida_nacimiento_pdf" name="partida_nacimiento_pdf" accept=".pdf">
        @error('partida_nacimiento_pdf')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    
    <div class="form-group col-lg-4 mb-3">
        <label for="certificado_secundaria_pdf">Certificado de Secundaria (PDF):</label>
        <input type="file"
            class="form-control form-control-sm @error('certificado_secundaria_pdf') is-invalid @enderror"
            id="certificado_secundaria_pdf" name="certificado_secundaria_pdf" accept=".pdf">
        @error('certificado_secundaria_pdf')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    
    {{-- <div class="form-group col-lg-4 mb-3">
        <label for="declaracion_jurada_salud_pdf">Declaración Jurada de Salud (PDF):</label>
        <input type="file"
            class="form-control form-control-sm @error('declaracion_jurada_salud_pdf') is-invalid @enderror"
            id="declaracion_jurada_salud_pdf" name="declaracion_jurada_salud_pdf" accept=".pdf">
        @error('declaracion_jurada_salud_pdf')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div> --}}
    {{-- <div class="form-group col-lg-4 mb-3">
        <label for="declaracion_jurada_documentos_pdf">Declaración Jurada de Documentos (PDF):</label>
        <input type="file"
            class="form-control form-control-sm @error('declaracion_jurada_documentos_pdf') is-invalid @enderror"
            id="declaracion_jurada_documentos_pdf" name="declaracion_jurada_documentos_pdf" accept=".pdf">
        @error('declaracion_jurada_documentos_pdf')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div> --}}
    {{-- <div class="form-group col-lg-4 mb-3">
        <label for="declaracion_jurada_conectividad_pdf">Declaración Jurada de Conectividad (PDF):</label>
        <input type="file"
            class="form-control form-control-sm @error('declaracion_jurada_conectividad_pdf') is-invalid @enderror"
            id="declaracion_jurada_conectividad_pdf" name="declaracion_jurada_conectividad_pdf" accept=".pdf">
        @error('declaracion_jurada_conectividad_pdf')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div> --}}
    
    <div class="form-group col-lg-12 mb-3 mt-2">
        <div class="form-check">
            <input class="form-check-input @error('declaracion_veracidad') is-invalid @enderror" type="checkbox"
                id="declaracion_veracidad" name="declaracion_veracidad" value="1"
                {{ old('declaracion_veracidad') ? 'checked' : '' }} required>
            <label class="form-check-label" for="declaracion_veracidad">
                Declaro bajo juramento que la información registrada expresa la verdad. <span
                    class="text-danger">*</span>
            </label>
            @error('declaracion_veracidad')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
</div>

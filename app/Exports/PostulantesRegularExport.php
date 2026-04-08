<?php

namespace App\Exports;

use App\Models\PostulantesRegular;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCustomCsvSettings;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Events\BeforeExport;

class PostulantesRegularExport implements FromCollection, ShouldAutoSize, WithCustomCsvSettings, WithHeadings, WithMapping
{
    public function collection()
    {
        // Filtrar solo los postulantes que NO tienen admin_fids_id (no están relacionados con AdminFid)
        return PostulantesRegular::whereNull('admin_fids_id')->select(
            'email',
            'programa',
            'contacto',
            'estudio_beca',
            'apellidos',
            'nombres',
            'dni',
            'genero',
            'etnicoidad', // NUEVO CAMPO AGREGADO
            'direccion',
            'numero',
            'fecha_nacimiento',
            'lugar_nacimiento',
            'distrito_nacimiento',
            'provincia_nacimiento',
            'departamento_nacimiento',
            'colegio',
            'codigo_colegio',
            'gestion_colegio',
            'direccion_colegio',
            'distrito_colegio',
            'provincia_colegio',
            'departamento_colegio',
            'ano_termino_colegio',
            'promedio_colegio',
            'lengua_1',
            'lengua_2',
            'nivel_quechua_hablado', // NUEVO CAMPO AGREGADO
            'nivel_quechua_escrito',  // NUEVO CAMPO AGREGADO
            'estado_civil',
            'num_hijos',
            'trabajas',
            'donde_trabajas',
            'cargo_trabajas',
            'describe_eespp',
            'observaciones',
            // Archivos adjuntos
            'dni_pdf',
            'partida_nacimiento_pdf',
            'certificado_secundaria_pdf',
            'foto',
            'voucher_pago',
            'constancia' // NUEVO CAMPO AGREGADO
        )->get();
    }

    public function headings(): array
    {
        return [
            ['Lista de inscritos en FID 2025 - Pendientes de admisión'],
            [ // Encabezados de columnas
                'Nombres',
                'Email',
                'DNI',
                'Programa',
                '¿Cómo te enteraste?',
                'Número',
                'Beca',
                'Género',
                'Etnicidad', // NUEVO ENCABEZADO
                'Dirección',
                'Fecha de Nacimiento',
                'Lugar de Nacimiento',
                'Distrito de Nacimiento',
                'Provincia de Nacimiento',
                'Departamento de Nacimiento',
                'Colegio',
                'Código Colegio',
                'Gestión Colegio',
                'Dirección Colegio',
                'Distrito Colegio',
                'Provincia Colegio',
                'Departamento Colegio',
                'Año que terminó colegio',
                'Promedio',
                'Lengua Materna',
                'Segunda Lengua',
                'Nivel Quechua Hablado', // NUEVO ENCABEZADO
                'Nivel Quechua Escrito',  // NUEVO ENCABEZADO
                'Estado Civil',
                'Número de Hijos',
                'Trabaja',
                '¿Dónde Trabaja?',
                'Cargo',
                'Concepto de EESPP',
                'Observaciones',
                // Archivos adjuntos
                'DNI PDF',
                'Partida de Nacimiento PDF',
                'Certificado de Secundaria PDF',
                'Foto',
                'Voucher de Pago',
                'Constancia', // NUEVO ENCABEZADO
            ],
        ];
    }

    public function map($postulante): array
    {
        return [
            "{$postulante->apellidos}, {$postulante->nombres}",
            $postulante->email,
            $postulante->dni,
            $this->formatearPrograma($postulante->programa),
            $postulante->contacto,
            $postulante->numero,
            $postulante->estudio_beca ? 'Sí' : 'No',
            $postulante->genero ? 'Masculino' : 'Femenino',
            $postulante->etnicoidad ?? 'No especificado', // NUEVO CAMPO MAPEADO
            $postulante->direccion,
            $postulante->fecha_nacimiento ? $postulante->fecha_nacimiento->format('d/m/Y') : '',
            $postulante->lugar_nacimiento,
            $postulante->distrito_nacimiento,
            $postulante->provincia_nacimiento,
            $postulante->departamento_nacimiento,
            $postulante->colegio,
            $postulante->codigo_colegio,
            ucfirst($postulante->gestion_colegio),
            $postulante->direccion_colegio,
            $postulante->distrito_colegio,
            $postulante->provincia_colegio,
            $postulante->departamento_colegio,
            $postulante->ano_termino_colegio,
            $postulante->promedio_colegio,
            $postulante->lengua_1,
            $postulante->lengua_2 ?? '-',
            $this->formatearNivelQuechua($postulante->nivel_quechua_hablado), // NUEVO CAMPO MAPEADO
            $this->formatearNivelQuechua($postulante->nivel_quechua_escrito),  // NUEVO CAMPO MAPEADO
            ucfirst($postulante->estado_civil ?? 'No especificado'),
            (int) $postulante->num_hijos,
            $postulante->trabajas ? 'Sí' : 'No',
            $postulante->donde_trabajas ?? '-',
            $postulante->cargo_trabajas ?? '-',
            $postulante->describe_eespp,
            $postulante->observaciones ?? '-',
            // Archivos adjuntos
            $this->generateFileLink($postulante->dni_pdf),
            $this->generateFileLink($postulante->partida_nacimiento_pdf),
            $this->generateFileLink($postulante->certificado_secundaria_pdf),
            $this->generateFileLink($postulante->foto),
            $this->generateFileLink($postulante->voucher_pago),
            $this->generateFileLink($postulante->constancia), // NUEVO CAMPO MAPEADO
        ];
    }

    // Función auxiliar para formatear el programa
    private function formatearPrograma($programa)
    {
        if ($programa === 'Educación Inicial') {
            return 'INI';
        } elseif ($programa === 'Educación Primaria EIB') {
            return 'EIB';
        }

        return $programa;
    }

    // Función auxiliar para formatear nivel de quechua
    private function formatearNivelQuechua($nivel)
    {
        if (is_null($nivel) || $nivel === '') {
            return 'No especificado';
        }

        $niveles = [
            'basico' => 'Básico',
            'intermedio' => 'Intermedio',
            'avanzado' => 'Avanzado',
            'nativo' => 'Nativo',
        ];

        return $niveles[$nivel] ?? ucfirst($nivel);
    }

    // Generar links para los archivos adjuntos
    private function generateFileLink($filePath)
    {
        return $filePath ? asset($filePath) : 'Sin archivo adjunto';
    }

    // Configurar delimitador de CSV para evitar problemas con comas
    public function getCsvSettings(): array
    {
        return [
            'delimiter' => ';',
            'encoding' => 'UTF-8',
        ];
    }

    public function registerEvents(): array
    {
        return [
            BeforeExport::class => function (BeforeExport $event) {
                $event->writer->getDelegate()->setPreCalculateFormulas(false);
                $event->writer->getDelegate()->getProperties()->setCreator('Sistema');
                // Agregar BOM para evitar problemas con tildes
                $event->writer->getDelegate()->getActiveSheet()
                    ->setCellValue('A1', "\xEF\xBB\xBF".'Lista de inscritos en FID 2025 - Pendientes de admisión');
            },
        ];
    }
}

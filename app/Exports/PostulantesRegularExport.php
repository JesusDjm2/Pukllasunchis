<?php

namespace App\Exports;

use App\Models\PostulantesRegular;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithCustomCsvSettings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Events\BeforeExport;

class PostulantesRegularExport implements FromCollection, WithHeadings, WithCustomCsvSettings, WithMapping, ShouldAutoSize
{
    public function collection()
    {
        return PostulantesRegular::select(
            'email', 'programa','contacto', 'estudio_beca', 'apellidos', 'nombres', 'dni',
            'genero', 'direccion', 'numero', 'fecha_nacimiento', 'lugar_nacimiento',
            'distrito_nacimiento', 'provincia_nacimiento', 'departamento_nacimiento',
            'colegio', 'codigo_colegio', 'gestion_colegio', 'direccion_colegio',
            'distrito_colegio', 'provincia_colegio', 'departamento_colegio', 
            'ano_termino_colegio', 'promedio_colegio', 'lengua_1', 'lengua_2',
            'estado_civil', 'num_hijos', 'trabajas', 'donde_trabajas', 
            'cargo_trabajas', 'describe_eespp', 'observaciones',

            // Archivos adjuntos
            'dni_pdf', 'partida_nacimiento_pdf', 'certificado_secundaria_pdf', 'foto', 'voucher_pago'
        )->get();
    }

    public function headings(): array
    {
        return [
            ['Lista de inscritos en FID 2025'], // Encabezado Principal
            [ // Encabezados de columnas
                'Nombres', 'Email', 'DNI', 'Programa','Como te enteraste de nosotros', 'Numero', 'Beca', 
                'Genero', 'Direccion',  'Fecha de Nacimiento', 'Lugar de Nacimiento',
                'Distrito de Nacimiento', 'Provincia de Nacimiento', 'Departamento de Nacimiento',
                'Colegio', 'Codigo Colegio', 'Gestion Colegio', 'Direccion Colegio',
                'Distrito Colegio', 'Provincia Colegio', 'Departamento Colegio',
                'Año que termino colegio', 'Promedio', 'Lengua Materna', 'Segunda Lengua',
                'Estado Civil', 'Numero de Hijos', 'Trabaja', 'Donde Trabaja',
                'Cargo', 'Concepto de EESPP',

                // Encabezados de archivos adjuntos
                'DNI PDF', 'Partida de Nacimiento PDF', 'Certificado de Secundaria PDF', 'Foto',
                'Declaración de Salud PDF', 'Voucher de Pago'
            ]
        ];
    }

    public function map($postulante): array
    {
        return [
            "{$postulante->apellidos}, {$postulante->nombres}",
            $postulante->email,
            $postulante->dni,
            ($postulante->programa === 'Educación Inicial') ? 'INI' : 'EIB',
            $postulante->contacto,
            $postulante->numero,
            $postulante->estudio_beca ? 'Si' : 'No',
            
            /* $postulante->programa, */
                        
            /* $postulante->apellidos,
            $postulante->nombres, */            
            $postulante->genero ? 'Masculino' : 'Femenino',
            $postulante->direccion,            
            /* $postulante->fecha_nacimiento, */
            ($postulante->fecha_nacimiento)->format('d/m/Y'),
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
            ucfirst($postulante->estado_civil ?? 'No especificado'),
            /* $postulante->num_hijos ?? 0, */
            (int) $postulante->num_hijos,
            $postulante->trabajas ? 'Si' : 'No',
            $postulante->donde_trabajas ?? '-',
            $postulante->cargo_trabajas ?? '-',
            $postulante->describe_eespp,

            // Generar enlaces de descarga para archivos adjuntos
            $this->generateFileLink($postulante->dni_pdf),
            $this->generateFileLink($postulante->partida_nacimiento_pdf),
            $this->generateFileLink($postulante->certificado_secundaria_pdf),
            $this->generateFileLink($postulante->foto),
            $this->generateFileLink($postulante->voucher_pago)
        ];
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
                $event->writer->getDelegate()->getProperties()->setCreator("Sistema");
                // Agregar BOM para evitar problemas con tildes
                $event->writer->getDelegate()->getActiveSheet()
                    ->setCellValue('A1', "\xEF\xBB\xBF" . 'Lista de inscritos en FID 2025');
            },
        ];
    }
}




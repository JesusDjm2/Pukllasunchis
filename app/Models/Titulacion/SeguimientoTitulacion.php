<?php

namespace App\Models\Titulacion;

use App\Models\Titulacion\Concerns\Auditable;
use App\Models\User;
use App\Services\Titulacion\AvanceTitulacion;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SeguimientoTitulacion extends Model
{
    use Auditable;
    use SoftDeletes;

    protected $table = 'seguimiento_titulacion';

    protected $fillable = [
        'user_id', 'estado_titulacion_id', 'numero_expediente', 'grupo_id', 'periodo_estudios', 'fecha_inicio', 'fecha_fin', 'observaciones',
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function estadoTitulacion()
    {
        return $this->belongsTo(EstadoTitulacion::class, 'estado_titulacion_id');
    }

    /** PTI vigente: el de número más alto (ver ptis()). */
    public function pti()
    {
        return $this->hasOne(Pti::class, 'seguimiento_id')->ofMany('numero', 'max');
    }

    /** Historial: si un PTI vence se registra otro (Bachiller o Licenciatura). */
    public function ptis()
    {
        return $this->hasMany(Pti::class, 'seguimiento_id')->orderBy('numero');
    }

    public function tesis()
    {
        return $this->hasOne(Tesis::class, 'seguimiento_id');
    }

    public function actas()
    {
        return $this->hasMany(Acta::class, 'seguimiento_id');
    }

    public function resoluciones()
    {
        return $this->hasMany(Resolucion::class, 'seguimiento_id');
    }

    public function documentos()
    {
        return $this->hasMany(Documento::class, 'seguimiento_id');
    }

    public function avisosPti()
    {
        return $this->hasMany(AvisoPti::class, 'seguimiento_id')->latest();
    }

    /** Asesores y jurado del TI y la Tesis (ver Participante). */
    public function participantes()
    {
        return $this->hasMany(Participante::class, 'seguimiento_id')->orderBy('id');
    }

    /** Integrantes que iniciaron juntos el PTI (mismo grupo_id), sin esta persona. */
    public function companerosDeGrupo()
    {
        return $this->hasMany(self::class, 'grupo_id', 'grupo_id')->where('id', '!=', $this->id);
    }

    /**
     * Pone estado_titulacion_id según los datos registrados (ver AvanceTitulacion)
     * y alinea pti.estado con su RD (con RD = aprobado), salvo que el PTI esté
     * marcado como vencido a mano. Lo disparan solos los modelos hijos
     * (RecalculaEstadoSeguimiento); el guard evita el bucle que causaría el
     * propio guardado del PTI.
     */
    public function recalcularEstado(): void
    {
        static $enCurso = [];
        if (isset($enCurso[$this->id])) {
            return;
        }
        $enCurso[$this->id] = true;

        try {
            $this->load(['pti', 'ptis', 'tesis', 'actas', 'resoluciones']);
            $avance = new AvanceTitulacion($this);

            if ($this->pti && $this->pti->estado !== 'vencido') {
                $estadoPti = $avance->hitos['pti_aprobado']['logrado'] ? 'aprobado' : 'pendiente';
                if ($this->pti->estado !== $estadoPti) {
                    $this->pti->update(['estado' => $estadoPti]);
                }
            }

            $estadoId = EstadoTitulacion::idPorClave($avance->estadoClave());
            if ($estadoId && $this->estado_titulacion_id !== $estadoId) {
                $this->update(['estado_titulacion_id' => $estadoId]);
            }
        } finally {
            unset($enCurso[$this->id]);
        }
    }

    public function avance(): AvanceTitulacion
    {
        return new AvanceTitulacion($this);
    }

    public function creadoPor()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function actualizadoPor()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Solicitudes de ayuda de un grupo Taq'e al Investigador: pensadas para
     * los dos casos reales que se identificaron (cambiar de organizador
     * cuando no hay uno disponible/cooperando, o disolver el grupo), más un
     * motivo libre "otro" para lo que no se haya previsto. Cualquier
     * integrante ACEPTADO puede enviarla (no solo el organizador — si el
     * problema es justamente que el organizador no coopera, no tendría
     * sentido exigir que sea él quien pida ayuda).
     */
    public function up(): void
    {
        Schema::create('taqe_solicitudes_ayuda', function (Blueprint $table) {
            $table->id();
            // Nullable + nullOnDelete (no cascade): si la solicitud termina en
            // eliminar el grupo (uno de los motivos previstos), el registro de
            // la solicitud debe sobrevivir para poder marcarse "atendida" y
            // quedar como historial — por eso también se guarda grupo_nombre
            // aparte, ya que la relación puede quedar en null.
            $table->foreignId('taqe_grupo_id')->nullable()->constrained('taqe_grupos')->nullOnDelete();
            $table->string('grupo_nombre');
            $table->foreignId('alumno_id')->constrained('alumnos');
            $table->string('motivo'); // cambiar_organizador | disolver_grupo | otro
            $table->text('mensaje')->nullable();
            $table->string('estado')->default('pendiente'); // pendiente | atendido
            $table->foreignId('atendido_por_user_id')->nullable()->constrained('users');
            $table->timestamp('atendido_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('taqe_solicitudes_ayuda');
    }
};

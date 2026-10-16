<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('seguimiento_titulacion', function (Blueprint $table) {
            // Formato normalizado "0001-T/PUK" (FID) o "0013-T/PUK-PPD" (PPD);
            // ver App\Services\Titulacion\Expediente.
            $table->string('numero_expediente', 40)->nullable()->after('estado_titulacion_id');
            $table->index('numero_expediente');
        });
    }

    public function down(): void
    {
        Schema::table('seguimiento_titulacion', function (Blueprint $table) {
            $table->dropIndex(['numero_expediente']);
            $table->dropColumn('numero_expediente');
        });
    }
};

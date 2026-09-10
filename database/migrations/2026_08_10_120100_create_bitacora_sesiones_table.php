<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bitacora_sesiones', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->index();
            $table->string('email_snapshot');
            $table->string('rol_snapshot')->nullable();
            $table->dateTime('login_at')->index();
            $table->dateTime('logout_at')->nullable();
            $table->enum('logout_tipo', ['manual', 'expirado'])->nullable();
            $table->timestamps();

            $table->index(['logout_at', 'login_at']);
            $table->index(['user_id', 'logout_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bitacora_sesiones');
    }
};

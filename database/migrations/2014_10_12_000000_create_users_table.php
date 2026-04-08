<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('apellidos');
            $table->string('dni')->unique();
            $table->string('condicion')->nullable();
            $table->string('pendiente', 500)->nullable();
            $table->string('perfil')->nullable();
            $table->boolean('beca')->nullable();
            $table->string('email')->unique();
            $table->boolean('genero');
            $table->string('etnicoidad')->nullable();
            // Nuevos campos solicitados
            $table->string('estadoCivil')->nullable();
            $table->date('fecha_nacimiento')->nullable();
            $table->integer('edad')->nullable()->unsigned();
            $table->boolean('hijos')->nullable()->default(false);
            $table->string('lengua_1', 100)->nullable();
            $table->string('lengua_2', 100)->nullable();
            $table->string('domicilio', 255)->nullable();
            $table->string('telefono', 20)->nullable();
            $table->string('dni_adjunto', 255)->nullable();
            //Nuevo campo de fotografía
            $table->string('foto')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};

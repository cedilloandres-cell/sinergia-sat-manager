<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabla de tenants (instalaciones). En fase 1 (mono-empresa) tendrá una sola
 * fila. Se incluye desde el día uno para que "tenant_id" exista en el resto
 * del esquema y la migración a multi-tenant (fase de producto comercial) no
 * requiera reescribir tablas existentes.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenants', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('ruc', 13)->nullable(); // RUC del taller (Ecuador)
            $table->string('logo_path')->nullable();
            $table->json('branding')->nullable(); // colores, encabezados, pie de página
            $table->json('settings')->nullable(); // config avanzada configurable sin programar
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenants');
    }
};

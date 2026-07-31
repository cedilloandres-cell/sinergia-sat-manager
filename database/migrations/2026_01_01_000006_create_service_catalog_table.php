<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_catalog', function (Blueprint $table) {
            $table->id();
            $table->uuid('tenant_id');
            $table->string('name'); // "Instalación Windows", "Cambio SSD"...
            $table->string('category')->nullable(); // Informática, CCTV, Línea blanca...
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2)->default(0);
            $table->integer('estimated_minutes')->nullable();
            $table->json('dynamic_fields_schema')->nullable(); // campos propios por categoría
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants');
            $table->index(['tenant_id', 'category']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_catalog');
    }
};

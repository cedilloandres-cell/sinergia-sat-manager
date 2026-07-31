<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('equipment', function (Blueprint $table) {
            $table->id();
            $table->uuid('tenant_id');
            $table->foreignId('client_id')->constrained('clients')->cascadeOnDelete();
            $table->string('category'); // Computadora, Cámara, Refrigeradora, etc.
            $table->string('brand')->nullable();
            $table->string('model')->nullable();
            $table->string('serial_number')->nullable();
            $table->text('accessories_received')->nullable(); // checklist de recepción
            $table->json('condition_photos')->nullable(); // fotos del estado inicial
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants');
            $table->index(['tenant_id', 'client_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('equipment');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * EL CORAZÓN DEL SISTEMA.
 *
 * Cada foto, nota de voz transcrita, cambio de estado o material agregado
 * es un evento con timestamp. El "informe técnico" no se redacta aparte:
 * es una vista renderizada (ver TechnicalReport) construida a partir de
 * estos eventos. Esto es lo que hace real la filosofía del producto:
 * "mientras reparas, el sistema documenta" — nada se transcribe dos veces.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('work_order_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('work_order_id')->constrained('work_orders')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('type', [
                'foto', 'video', 'audio', 'nota_texto', 'nota_voz_transcrita',
                'material_agregado', 'mano_obra_agregada', 'cambio_estado',
                'firma', 'ubicacion',
            ]);
            $table->text('content')->nullable(); // texto, transcripción, descripción
            $table->foreignId('media_attachment_id')->nullable()->constrained('media_attachments')->nullOnDelete();
            $table->json('metadata')->nullable(); // GPS, duración de audio, etc.
            $table->timestamp('occurred_at');
            $table->timestamps();

            $table->index(['work_order_id', 'occurred_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('work_order_events');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Documento generado (o en edición) a partir de work_order_events.
 * "content_html" es lo que el editor visual tipo Word modifica antes de
 * exportar a PDF. "generated_html" guarda la última versión auto-construida
 * por el sistema, para poder comparar/regenerar sin perder ediciones manuales.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('technical_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('work_order_id')->constrained('work_orders')->cascadeOnDelete();
            $table->longText('generated_html')->nullable();
            $table->longText('content_html')->nullable(); // versión editada por el técnico
            $table->enum('status', ['borrador', 'revisado', 'enviado'])->default('borrador');
            $table->string('pdf_path')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();

            $table->index('work_order_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('technical_reports');
    }
};

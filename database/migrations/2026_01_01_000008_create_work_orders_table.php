<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Núcleo del sistema. El "codigo" se genera automáticamente
 * (ej. ST-2026-000001) en el modelo WorkOrder::booted().
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('work_orders', function (Blueprint $table) {
            $table->id();
            $table->uuid('tenant_id');
            $table->string('code')->unique(); // ST-2026-000001
            $table->foreignId('client_id')->constrained('clients');
            $table->foreignId('equipment_id')->nullable()->constrained('equipment')->nullOnDelete();
            $table->foreignId('assigned_technician_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('status', [
                'pendiente', 'en_proceso', 'esperando_repuesto',
                'terminado', 'entregado', 'cancelado',
            ])->default('pendiente');
            $table->enum('priority', ['baja', 'normal', 'alta', 'urgente'])->default('normal');
            $table->text('reported_issue')->nullable(); // falla reportada por el cliente
            $table->decimal('lat', 10, 7)->nullable(); // GPS opcional
            $table->decimal('lng', 10, 7)->nullable();
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('tenant_id')->references('id')->on('tenants');
            $table->index(['tenant_id', 'status']);
            $table->index(['tenant_id', 'assigned_technician_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('work_orders');
    }
};

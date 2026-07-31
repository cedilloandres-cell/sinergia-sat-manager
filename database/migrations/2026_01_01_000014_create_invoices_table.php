<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Campos específicos para facturación electrónica autorizada por el SRI
 * (Ecuador). No es un módulo genérico: requiere clave de acceso, XML firmado
 * y estado de autorización — ver README para el flujo de integración.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->uuid('tenant_id');
            $table->string('code')->unique(); // secuencial interno
            $table->foreignId('client_id')->constrained('clients');
            $table->foreignId('work_order_id')->nullable()->constrained('work_orders')->nullOnDelete();
            $table->string('sri_access_key', 49)->nullable(); // clave de acceso de 49 dígitos
            $table->enum('sri_status', ['pendiente', 'autorizada', 'rechazada', 'contingencia'])->default('pendiente');
            $table->longText('sri_xml')->nullable();
            $table->string('sri_authorization_number')->nullable();
            $table->decimal('subtotal', 10, 2)->default(0);
            $table->decimal('tax', 10, 2)->default(0);
            $table->decimal('total', 10, 2)->default(0);
            $table->timestamp('issued_at')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};

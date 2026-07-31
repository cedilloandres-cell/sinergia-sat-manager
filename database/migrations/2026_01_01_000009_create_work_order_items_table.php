<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Line items de servicios y materiales usados en una orden. Guarda el precio
 * AL MOMENTO de uso (no referencia en vivo al catálogo) para no alterar
 * facturas históricas si el catálogo cambia de precio después.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('work_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('work_order_id')->constrained('work_orders')->cascadeOnDelete();
            $table->enum('type', ['servicio', 'material', 'mano_de_obra']);
            $table->foreignId('service_catalog_id')->nullable()->constrained('service_catalog')->nullOnDelete();
            $table->foreignId('material_catalog_id')->nullable()->constrained('material_catalog')->nullOnDelete();
            $table->string('description');
            $table->decimal('quantity', 10, 2)->default(1);
            $table->decimal('unit_price', 10, 2)->default(0);
            $table->decimal('subtotal', 10, 2)->default(0);
            $table->timestamps();

            $table->index('work_order_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('work_order_items');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('material_catalog', function (Blueprint $table) {
            $table->id();
            $table->uuid('tenant_id');
            $table->string('sku')->nullable();
            $table->string('name');
            $table->string('category')->nullable();
            $table->decimal('cost_price', 10, 2)->default(0); // costo de compra
            $table->decimal('sale_price', 10, 2)->default(0); // precio al cliente
            $table->integer('stock_quantity')->default(0);
            $table->integer('min_stock_alert')->default(0);
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants');
            $table->index(['tenant_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('material_catalog');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabla polimórfica de archivos. Los binarios NUNCA se guardan aquí ni en
 * PostgreSQL: solo se guarda la referencia al bucket S3-compatible
 * (S3 / Cloudflare R2 / Backblaze B2) definido en config/filesystems.php.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('media_attachments', function (Blueprint $table) {
            $table->id();
            $table->uuid('tenant_id');
            $table->morphs('attachable'); // work_orders, equipment, etc.
            $table->enum('type', ['foto', 'video', 'audio', 'firma', 'documento']);
            $table->string('disk')->default('s3'); // driver de filesystems.php
            $table->string('path'); // ruta dentro del bucket
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('size_bytes')->nullable();
            $table->decimal('lat', 10, 7)->nullable();
            $table->decimal('lng', 10, 7)->nullable();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media_attachments');
    }
};

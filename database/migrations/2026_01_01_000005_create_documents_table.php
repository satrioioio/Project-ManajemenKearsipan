<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->foreignId('project_phase_id')->nullable()->constrained('project_phases')->nullOnDelete();
            $table->foreignId('classification_id')->nullable()->constrained('classifications')->nullOnDelete();
            $table->foreignId('parent_document_id')->nullable()->constrained('documents')->nullOnDelete();
            $table->string('nomor_dokumen');
            $table->string('judul');
            $table->string('jenis')->default('kontrak');
            $table->string('versi')->default('1.0');
            $table->text('deskripsi')->nullable();
            $table->date('tanggal_dokumen');
            $table->date('valid_from');
            $table->date('valid_until')->nullable();
            $table->date('retention_date')->nullable();
            $table->string('lifecycle_status')->default('active');
            $table->string('security_level')->default('terbatas');
            $table->string('file_path')->nullable();
            $table->string('file_name')->nullable();
            $table->unsignedBigInteger('file_size')->nullable();
            $table->string('mime_type')->nullable();
            $table->string('checksum_sha256', 64)->nullable();
            $table->boolean('is_locked')->default(false);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['project_id', 'nomor_dokumen']);
            $table->index(['project_id', 'jenis']);
            $table->index(['jenis', 'lifecycle_status']);
            $table->index('retention_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};

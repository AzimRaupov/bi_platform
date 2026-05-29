<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('uploaded_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies');
            $table->string('original_nam',500);
            $table->string('file_path',500);
            $table->enum('file_type',['pdf','doc','docx','excel','txt','ppt','pptx','sql'])->default('txt');
            $table->bigInteger('file_size')->default(0);
            $table->enum('status',['pending','queued','processing','extracted','etl_processing','ready','failed'])->default('pending');
            $table->text('error_message')->nullable();
            $table->dateTime('processed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('uploaded_files');
    }
};

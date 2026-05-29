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
        Schema::create('extracted_data', function (Blueprint $table) {
            $table->id();
            $table->foreignId('file_id')->constrained('uploaded_files');
            $table->foreignId('company_id')->constrained('companies');
            $table->foreignId('message_id')->constrained('ai_chat_messages');
            $table->string('document_type')->nullable();
            $table->string('json_path', 500)->nullable();
            $table->dateTime('extracted_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('extracted_data');
    }
};

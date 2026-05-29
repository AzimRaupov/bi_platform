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
        Schema::create('ai_chat_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chat_id')->constrained('ai_chats');
            $table->text('message')->nullable();
            $table->text('answer')->nullable();
            $table->foreignId('file_id')->nullable()->constrained('uploaded_files');
            $table->integer('tokens_used')->default(0);
            $table->json('tool_results')->nullable();
            $table->enum('status', ['unread', 'send', 'analyze', 'generate'])->default('unread');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_chat_messages');
    }
};

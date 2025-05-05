<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('type'); // Przechowuje np. "App\Notifications\NewBookAdded"
            $table->morphs('notifiable'); // Opcjonalne dla polimorficznych powiązań
            $table->json('data'); // Dane z metody toDatabase()
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'read_at']); // Optymalizacja zapytań
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};

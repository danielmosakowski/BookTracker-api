<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('reading_progresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_book_id')->constrained('user_books')->onDelete('cascade');
            $table->integer('current_page')->unsigned()->default(0);
            $table->timestamp('updated_at')->nullable(); // Zastępuje timestamps()
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reading_progresses');
    }
};

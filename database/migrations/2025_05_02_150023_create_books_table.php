<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('books', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->foreignId('author_id')->constrained()->onDelete('cascade');
            $table->foreignId('genre_id')->constrained()->onDelete('cascade');
            $table->string('isbn')->unique()->nullable();
            $table->text('description')->nullable();
            $table->string('cover_image')->nullable();
            $table->year('published_year')->nullable();
            $table->integer('total_pages')->unsigned()->nullable();
            $table->timestamps();

            $table->index('title'); // Dodane dla szybkiego wyszukiwania
            $table->index('published_year');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('books');
    }
};

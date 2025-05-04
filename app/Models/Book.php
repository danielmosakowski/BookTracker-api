<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Book extends Model
{
    use HasFactory;

    // Relacja: książka należy do autora
    public function author(): BelongsTo
    {
        return $this->belongsTo(Author::class);
    }

    // Relacja: książka należy do gatunku
    public function genre(): BelongsTo
    {
        return $this->belongsTo(Genre::class);
    }

    // Relacja: książka ma wiele ocen
    public function bookRating(): HasMany
    {
        return $this->hasMany(BookRating::class);
    }

    // Relacja: książka jest częścią kolekcji książek użytkowników
    public function userBook(): HasMany
    {
        return $this->hasMany(UserBook::class);
    }

}

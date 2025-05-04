<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;



class UserBook extends Model
{

    use HasFactory;

    // Relacja: kolekcja należy do użytkownika
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Relacja: kolekcja należy do książki
    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    // Relacja: kolekcja ma wiele postępów w czytaniu
    public function readingProgress(): HasMany
    {
        return $this->hasMany(ReadingProgress::class);
    }

    // Relacja: kolekcja ma wiele ocen
    public function bookRating(): HasMany
    {
        return $this->hasMany(BookRating::class);
    }

}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Genre extends Model
{
    /** @use HasFactory<\Database\Factories\GenreFactory> */
    use HasFactory;

    // Relacja: gatunek ma wiele książek
    public function book(): HasMany
    {
        return $this->hasMany(Book::class);
    }

    // Relacja: gatunek ma wiele kolekcji użytkowników
    public function userGenre(): HasMany
    {
        return $this->hasMany(UserGenre::class);
    }

}

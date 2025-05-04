<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserGenre extends Model
{
    use HasFactory;

    // Relacja: kolekcja należy do użytkownika
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Relacja: kolekcja należy do gatunku
    public function genre(): BelongsTo
    {
        return $this->belongsTo(Genre::class);
    }

}

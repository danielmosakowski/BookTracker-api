<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookRating extends Model
{

    use HasFactory;

    // Relacja: ocena należy do użytkownika
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Relacja: ocena należy do książki
    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

}

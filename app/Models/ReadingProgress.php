<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReadingProgress extends Model
{

    use HasFactory;

    // Relacja: postęp należy do kolekcji książek użytkownika
    public function userBook(): BelongsTo
    {
        return $this->belongsTo(UserBook::class);
    }

}

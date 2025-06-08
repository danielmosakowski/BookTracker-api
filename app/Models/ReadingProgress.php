<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReadingProgress extends Model
{
    use HasFactory;

    protected $table = 'reading_progresses'; // Dodaj tę linię

    protected $fillable = [
        'current_page',
        'user_book_id'
    ];

    public function userBook(): BelongsTo
    {
        return $this->belongsTo(UserBook::class, 'user_book_id');
    }
}

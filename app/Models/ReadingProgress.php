<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


class ReadingProgress extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'book_id', 'status', 'progress_percentage',
    ];

    // Relacje
    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

}

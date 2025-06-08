<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserChallenge extends Model
{
    use HasFactory;

    protected $table = 'user_challenges';

    protected $fillable = [
        'user_id',
        'challenge_id',
        'completed_books',
        'is_completed'
    ];

    protected $casts = [
        'is_completed' => 'boolean',
    ];

    /**
     * Relacja do użytkownika
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relacja do wyzwania
     */
    public function challenge()
    {
        return $this->belongsTo(Challenge::class);
    }

    /**
     * Aktualizuje postęp użytkownika w wyzwaniu
     */
    public function updateProgress(int $completedBooks): void
    {
        $this->completed_books = $completedBooks;
        $this->is_completed = $completedBooks >= $this->challenge->target_books;
        $this->save();
    }

    /**
     * Sprawdza czy wyzwanie jest zakończone
     */
    public function isCompleted(): bool
    {
        return $this->is_completed;
    }
}

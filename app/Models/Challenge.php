<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Challenge extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'target_books',
        'start_date',
        'end_date'
    ];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_challenges')
            ->withPivot(['completed_books', 'is_completed'])
            ->withTimestamps();
    }
}

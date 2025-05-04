<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Notifications\Notifiable;

/**
 * @property \Illuminate\Database\Eloquent\Collection|UserBook[] $userBooks
 * @property \Illuminate\Database\Eloquent\Collection|UserGenre[] $userGenres
 */

class User extends Model
{

    use HasFactory, Notifiable;

    // Relacja: użytkownik ma wiele książek w swojej kolekcji
    public function userBook(): HasMany
    {
        return $this->hasMany(UserBook::class);
    }

    // Relacja: użytkownik ma wiele ulubionych gatunków
    public function userGenre(): HasMany
    {
        return $this->hasMany(UserGenre::class);
    }

}

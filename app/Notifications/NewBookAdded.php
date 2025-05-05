<?php

namespace App\Notifications;

use App\Models\Book;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class NewBookAdded extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Book $book
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'message' => "Nowa książka w Twoim ulubionym gatunku: {$this->book->title}",
            'book_id' => $this->book->id,
            'book_title' => $this->book->title,
            'genre' => $this->book->genre->name,
            'icon' => '📚', // Emoji dla lepszej wizualizacji
            'url' => route('books.show', $this->book->id)
        ];
    }
}

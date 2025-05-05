<?php

namespace App\Notifications;

use App\Models\Book;
use App\Models\BookRating;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class NewCommentOnBook extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Book $book,
        public BookRating $comment
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'message' => "Nowy komentarz do książki: {$this->book->title}",
            'book_id' => $this->book->id,
            'comment_id' => $this->comment->id,
            'author_name' => $this->comment->user->name,
            'rating' => $this->comment->rating,
            'icon' => '💬',
            'url' => route('books.show', $this->book->id) . '#comment-' . $this->comment->id
        ];
    }
}

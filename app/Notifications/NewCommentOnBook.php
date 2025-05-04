<?php

namespace App\Notifications;

use App\Models\Book;
use App\Models\BookRating;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewCommentOnBook extends Notification
{
    use Queueable;

    protected Book $book;
    protected BookRating $bookRating;


    /**
     * Create a new notification instance.
     */
    public function __construct(Book $book, BookRating $bookRating)
    {
        $this->book = $book;
        $this->bookRating = $bookRating;

    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the mail representation of the notification.
     */
//    public function toMail(object $notifiable): MailMessage
//    {
//        return (new MailMessage)
//            ->line('The introduction to the notification.')
//            ->action('Notification Action', url('/'))
//            ->line('Thank you for using our application!');
//    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */

    public function toDatabase($notifiable): array
    {
        return [
            'message' => "Nowy komentarz do książki: {$this->book->title}",
            'book_id' => $this->book->id,
            'comment_id' => $this->bookRating->id,
        ];
    }

    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}

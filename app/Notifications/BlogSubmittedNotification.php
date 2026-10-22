<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BlogSubmittedNotification extends Notification
{
    use Queueable;

    public $blog;

    public function __construct($blog)
    {
        $this->blog = $blog;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'blog_id' => $this->blog->id,
            'title' => $this->blog->title,
            'student_name' => $this->blog->student->fname . ' ' . $this->blog->student->lname,
            'message' => 'New blog submitted for review: ' . $this->blog->title,
            'link' => route('blogs.review', $this->blog->id),
        ];
    }
}

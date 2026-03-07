<?php

namespace App\Mail;

use App\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TaskReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public Task $task;
    public string $reminderType;

    public function __construct(Task $task, string $reminderType = 'custom')
    {
        $this->task = $task;
        $this->reminderType = $reminderType;
    }

    public function envelope(): Envelope
    {
        $subject = $this->reminderType === 'due'
            ? 'Task Due Reminder: ' . $this->task->title
            : 'Task Reminder: ' . $this->task->title;

        return new Envelope(subject: $subject);
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.task-reminder',
        );
    }
}

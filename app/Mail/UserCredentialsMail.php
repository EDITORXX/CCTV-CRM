<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class UserCredentialsMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $userName;
    public string $userEmail;
    public string $userPassword;
    public string $loginUrl;

    public function __construct(string $name, string $email, string $password, string $loginUrl)
    {
        $this->userName = $name;
        $this->userEmail = $email;
        $this->userPassword = $password;
        $this->loginUrl = $loginUrl;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your Login Credentials',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.user-credentials',
        );
    }
}

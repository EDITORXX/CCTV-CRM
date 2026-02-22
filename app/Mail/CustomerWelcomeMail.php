<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CustomerWelcomeMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $customerName;
    public string $email;
    public string $password;
    public string $loginUrl;
    public string $companyName;

    public function __construct(string $customerName, string $email, string $password, string $companyName)
    {
        $this->customerName = $customerName;
        $this->email = $email;
        $this->password = $password;
        $this->loginUrl = url('/login');
        $this->companyName = $companyName;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your Account Has Been Created - ' . $this->companyName,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.customer-welcome',
        );
    }
}

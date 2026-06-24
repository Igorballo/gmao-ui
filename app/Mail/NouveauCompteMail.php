<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NouveauCompteMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public string $motDePasse,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Vos identifiants de connexion — GMAO+',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.nouveau-compte',
        );
    }
}

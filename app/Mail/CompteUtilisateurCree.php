<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CompteUtilisateurCree extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $utilisateur,
        public string $motDePasse,
        public string $role
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '🏥 SNGP — Vos identifiants de connexion',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.compte-utilisateur',
        );
    }
}

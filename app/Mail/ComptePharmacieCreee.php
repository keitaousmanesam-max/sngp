<?php

namespace App\Mail;

use App\Models\Pharmacie;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ComptePharmacieCreee extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Pharmacie $pharmacie,
        public User $admin,
        public string $motDePasse
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '🏥 SNGP — Vos identifiants de connexion — ' . $this->pharmacie->nom,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.compte-pharmacie',
        );
    }
}
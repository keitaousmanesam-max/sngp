<?php

namespace App\Mail;

use App\Models\Vente;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RecuVente extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Vente $vente) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Votre reçu — ' . $this->vente->numero_vente . ' — SNGP',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.recu-vente',
        );
    }
}

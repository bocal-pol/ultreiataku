<?php

declare(strict_types=1);

namespace App\Modules\Pilgrimage\Mail;

use App\Modules\Pilgrimage\Models\Trip;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * ULTREIA-32 — Mail d'invitation trilingue fr/nl/de à rejoindre un Trip.
 *
 * Le sujet et le corps sont rédigés dans la locale du destinataire.
 * La locale est passée en paramètre ; défaut fr.
 */
class TripInvitationMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public string $joinUrl;

    /** @var string */
    public $locale;

    /** @var array<string, string> */
    private array $subjects = [
        'fr' => 'Invitation à rejoindre un pèlerinage sur Ultreiataku',
        'nl' => 'Uitnodiging om een bedevaart te vervoegen op Ultreiataku',
        'de' => 'Einladung zur Teilnahme an einer Pilgerreise auf Ultreiataku',
    ];

    public function __construct(
        public readonly Trip $trip,
        string $locale = 'fr',
    ) {
        $this->locale = in_array($locale, ['fr', 'nl', 'de'], true) ? $locale : 'fr';

        $frontendUrl = rtrim((string) config('app.frontend_url', config('app.url')), '/');
        $this->joinUrl = "{$frontendUrl}/trips/join/{$this->trip->invite_token}";
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->subjects[$this->locale] ?? $this->subjects['fr'],
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.trips.invitation',
            with: [
                'trip' => $this->trip,
                'joinUrl' => $this->joinUrl,
                'locale' => $this->locale,
                'tripName' => $this->trip->name,
                'organizerName' => $this->trip->organizer?->display_name ?? 'Un pèlerin',
            ],
        );
    }
}

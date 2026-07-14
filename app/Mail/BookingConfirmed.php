<?php

namespace App\Mail;

use App\Models\EmailTemplate;
use App\Models\Reservation;
use App\Models\Restaurant;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BookingConfirmed extends Mailable
{
    use Queueable, SerializesModels;

    protected Reservation $reservation;

    protected Restaurant $restaurant;

    public function __construct(Reservation $reservation)
    {
        $this->reservation = $reservation;
        /** @var Restaurant $restaurant */
        $restaurant = $reservation->restaurant;
        $this->restaurant = $restaurant;
    }

    public function envelope(): Envelope
    {
        /** @var EmailTemplate|null $template */
        $template = EmailTemplate::query()
            ->where('restaurant_id', $this->restaurant->id)
            ->where('key', 'confirm')
            ->first();

        $subject = $template
            ? $this->replaceTokens($template->subject)
            : "Your booking at {$this->restaurant->name} is confirmed";

        return new Envelope(
            subject: $subject,
        );
    }

    public function content(): Content
    {
        /** @var EmailTemplate|null $template */
        $template = EmailTemplate::query()
            ->where('restaurant_id', $this->restaurant->id)
            ->where('key', 'confirm')
            ->first();

        $body = $template
            ? $this->replaceTokens($template->body)
            : "Dear {$this->reservation->guest_name},\n\nYour booking for {$this->reservation->party_size} guests on {$this->reservation->date} at {$this->reservation->time} has been confirmed.\n\nBooking Reference: {$this->reservation->public_ref}\n\nWe look forward to welcoming you!\n\n{$this->restaurant->name} Team";

        return new Content(
            view: 'emails.booking-confirmed',
            with: ['body' => $body],
        );
    }

    private function replaceTokens(string $text): string
    {
        $tokens = [
            '{{guest_name}}' => $this->reservation->guest_name,
            '{{party_size}}' => (string) $this->reservation->party_size,
            '{{date}}' => $this->reservation->date,
            '{{time}}' => $this->reservation->time,
            '{{booking_id}}' => $this->reservation->public_ref,
            '{{restaurant_name}}' => $this->restaurant->name,
        ];

        return str_replace(array_keys($tokens), array_values($tokens), $text);
    }
}

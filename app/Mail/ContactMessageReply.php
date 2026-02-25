<?php

namespace App\Mail;

use App\Models\ContactMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ContactMessageReply extends Mailable
{
    use Queueable, SerializesModels;

    public ContactMessage $contactMessage;
    public string $replyText;

    /**
     * Create a new message instance.
     */
    public function __construct(ContactMessage $contactMessage, string $replyText)
    {
        $this->contactMessage = $contactMessage;
        $this->replyText = $replyText;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Respuesta a tu consulta - Punjab Idiomas',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.contact-reply',
            with: [
                'name' => $this->contactMessage->name,
                'originalMessage' => $this->contactMessage->message,
                'replyText' => $this->replyText,
            ]
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}

<?php

namespace App\Mail;

use App\Models\App;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class TeamMemberInvitation extends Mailable
{
    use Queueable, SerializesModels;

    public $app;
    public $email;
    public $appId;
    public $firstName;
    public $verificationUrl;

    /**
     * Create a new message instance.
     *
     * @param string $email
     * @param string|null $firstName
     * @param string $appId
     * @param string $verificationUrl
     */
    public function __construct(string $email, ?string $firstName, string $appId, string $verificationUrl)
    {
        $this->email = $email;
        $this->appId = $appId;
        $this->firstName = $firstName;
        $this->verificationUrl = $verificationUrl;
        $this->app = App::findOrFail($appId);
    }

    /**
     * Get the message envelope.
     *
     * @return Envelope
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "You’ve Been Invited to Join {$this->app->name}!",
        );
    }

    /**
     * Get the message content definition.
     *
     * @return Content
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.team-member-invitation',
            with: [
                'email' => $this->email,
                'firstName' => $this->firstName,
                'appName' => $this->app->name,
                'verificationUrl' => $this->verificationUrl,
            ],
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

<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CampaignTestMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    /**
     * @param  array{subject:string, html:string, sender_name:string|null, sender_email:string}  $payload
     */
    public function __construct(private readonly array $payload)
    {
    }

    public function build(): self
    {
        return $this->subject($this->payload['subject'])
            ->from($this->payload['sender_email'], $this->payload['sender_name'])
            ->html($this->payload['html']);
    }
}

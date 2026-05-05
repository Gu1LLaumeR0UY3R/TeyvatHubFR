<?php

namespace App\Mail;

use App\Models\Survey;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SurveyResultsMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Survey $survey,
        public readonly array  $aggregated,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Résultats du questionnaire : ' . $this->survey->article->title,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.survey-results',
        );
    }
}

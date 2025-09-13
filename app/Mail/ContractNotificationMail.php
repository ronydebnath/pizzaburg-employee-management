<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Queue\SerializesModels;
use App\Models\EmploymentContract;
use App\Models\OnboardingInvite;

class ContractNotificationMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public EmploymentContract $contract;
    public OnboardingInvite $invite;
    public string $type;

    /**
     * Create a new message instance.
     */
    public function __construct(EmploymentContract $contract, string $type = 'sent')
    {
        $this->contract = $contract;
        $this->invite = $contract->onboardingInvite;
        $this->type = $type;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $subject = match($this->type) {
            'sent' => 'Employment Contract Ready for Signature - ' . $this->contract->contract_number,
            'signed' => 'Employment Contract Signed - ' . $this->contract->contract_number,
            'completed' => 'Employment Contract Completed - ' . $this->contract->contract_number,
            default => 'Employment Contract Update - ' . $this->contract->contract_number,
        };

        return new Envelope(
            subject: $subject,
            from: config('mail.from.address', 'noreply@pizzaburg.com'),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        $view = match($this->type) {
            'sent' => 'emails.contract.sent',
            'signed' => 'emails.contract.signed',
            'completed' => 'emails.contract.completed',
            default => 'emails.contract.update',
        };

        return new Content(
            view: $view,
            with: [
                'contract' => $this->contract,
                'invite' => $this->invite,
                'employeeName' => $this->invite->full_name,
                'contractUrl' => route('contract.show', $this->invite->token),
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
        $attachments = [];

        // Attach signed PDF if contract is completed
        if ($this->type === 'completed' && $this->contract->signed_pdf_path) {
            $attachments[] = Attachment::fromStorage($this->contract->signed_pdf_path)
                ->as('Employment_Contract_' . $this->contract->contract_number . '.pdf')
                ->withMime('application/pdf');
        }

        return $attachments;
    }
}

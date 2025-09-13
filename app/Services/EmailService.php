<?php

namespace App\Services;

use App\Models\EmploymentContract;
use App\Models\OnboardingInvite;
use App\Mail\ContractNotificationMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class EmailService
{
    /**
     * Send contract notification email
     */
    public function sendContractNotification(EmploymentContract $contract, string $type = 'sent'): bool
    {
        try {
            $invite = $contract->onboardingInvite;
            
            if (!$invite || !$invite->email) {
                Log::warning('Cannot send contract email: No valid email address', [
                    'contract_id' => $contract->id,
                    'invite_id' => $invite?->id,
                ]);
                return false;
            }

            Mail::to($invite->email)->send(new ContractNotificationMail($contract, $type));

            Log::info('Contract notification email sent', [
                'contract_id' => $contract->id,
                'contract_number' => $contract->contract_number,
                'email' => $invite->email,
                'type' => $type,
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error('Failed to send contract notification email', [
                'contract_id' => $contract->id,
                'contract_number' => $contract->contract_number,
                'email' => $invite->email ?? 'unknown',
                'type' => $type,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Send contract sent notification
     */
    public function sendContractSentNotification(EmploymentContract $contract): bool
    {
        return $this->sendContractNotification($contract, 'sent');
    }

    /**
     * Send contract signed notification
     */
    public function sendContractSignedNotification(EmploymentContract $contract): bool
    {
        return $this->sendContractNotification($contract, 'signed');
    }

    /**
     * Send contract completed notification with PDF attachment
     */
    public function sendContractCompletedNotification(EmploymentContract $contract): bool
    {
        return $this->sendContractNotification($contract, 'completed');
    }

    /**
     * Send onboarding invitation email
     */
    public function sendOnboardingInvitation(OnboardingInvite $invite): bool
    {
        try {
            if (!$invite->email) {
                Log::warning('Cannot send onboarding invitation: No email address', [
                    'invite_id' => $invite->id,
                ]);
                return false;
            }

            // TODO: Create OnboardingInvitationMail class
            // For now, we'll use a simple notification
            $kycUrl = route('kyc.show', $invite->token);
            
            $subject = 'Welcome to Pizzaburg - Complete Your Onboarding';
            $message = "
                Hello {$invite->full_name},
                
                Welcome to Pizzaburg! We're excited to have you join our team.
                
                Please complete your onboarding process by clicking the link below:
                {$kycUrl}
                
                This link will expire on {$invite->expires_at->format('M d, Y')}.
                
                Best regards,
                Pizzaburg HR Team
            ";

            Mail::raw($message, function ($mail) use ($invite, $subject) {
                $mail->to($invite->email)
                     ->subject($subject);
            });

            Log::info('Onboarding invitation email sent', [
                'invite_id' => $invite->id,
                'email' => $invite->email,
                'token' => $invite->token,
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error('Failed to send onboarding invitation email', [
                'invite_id' => $invite->id,
                'email' => $invite->email,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }
}

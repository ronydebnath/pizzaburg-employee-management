<?php

namespace App\Http\Controllers;

use App\Models\EmploymentContract;
use App\Models\OnboardingInvite;
use App\Services\ContractGenerationService;
use App\Services\EmailService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class ContractController extends Controller
{
    protected ContractGenerationService $contractService;
    protected EmailService $emailService;

    public function __construct(ContractGenerationService $contractService, EmailService $emailService)
    {
        $this->contractService = $contractService;
        $this->emailService = $emailService;
    }

    /**
     * Display contract for signing
     */
    public function show(string $token)
    {
        try {
            // Find the onboarding invite by token
            $invite = OnboardingInvite::where('token', $token)
                ->where('status', 'sent')
                ->where('expires_at', '>', now())
                ->first();

            if (!$invite) {
                return view('contracts.expired', [
                    'message' => 'This invitation link has expired or is invalid.'
                ]);
            }

            // Get or create the employment contract
            $contract = EmploymentContract::firstOrCreate(
                ['onboarding_invite_id' => $invite->id],
                [
                    'template_key' => $invite->position->contract_template_key ?? 'default',
                    'status' => 'draft',
                    'contract_data' => $this->prepareContractData($invite),
                ]
            );

            // Generate contract PDF if not exists
            if (!$contract->signed_pdf_path) {
                $this->contractService->generateContract($contract);
            }

            return view('contracts.show', [
                'contract' => $contract,
                'invite' => $invite,
            ]);

        } catch (\Exception $e) {
            Log::error('Contract display error: ' . $e->getMessage(), [
                'token' => $token,
                'error' => $e->getTraceAsString(),
            ]);

            return view('contracts.error', [
                'message' => 'An error occurred while loading the contract. Please try again later.'
            ]);
        }
    }

    /**
     * Accept contract (click-wrap)
     */
    public function accept(Request $request, string $token): JsonResponse
    {
        try {
            $request->validate([
                'signature' => 'required|string',
                'ip_address' => 'required|ip',
                'user_agent' => 'required|string',
                'accepted_terms' => 'required|boolean|accepted',
            ]);

            // Find the onboarding invite
            $invite = OnboardingInvite::where('token', $token)
                ->where('status', 'sent')
                ->where('expires_at', '>', now())
                ->first();

            if (!$invite) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid or expired invitation link.'
                ], 400);
            }

            // Get the contract
            $contract = EmploymentContract::where('onboarding_invite_id', $invite->id)->first();

            if (!$contract) {
                return response()->json([
                    'success' => false,
                    'message' => 'Contract not found.'
                ], 404);
            }

            if ($contract->isSigned()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Contract has already been signed.'
                ], 400);
            }

            // Save signature image
            $signaturePath = $this->saveSignature($request->signature, $contract);

            // Update contract with signature and acceptance details
            $contract->update([
                'signature_file_path' => $signaturePath,
                'signed_at' => now(),
                'status' => 'signed',
            ]);

            // Generate final signed PDF
            $signedPdfPath = $this->contractService->generateSignedContract($contract);

            // Mark contract as completed
            $contract->update(['status' => 'completed', 'completed_at' => now()]);

            // Mark invite as completed
            $invite->update(['status' => 'completed']);

            // Send signed notification email
            $this->emailService->sendContractSignedNotification($contract);

            // Send completed notification with PDF attachment
            $this->emailService->sendContractCompletedNotification($contract);

            // Log the contract acceptance
            Log::info('Contract accepted', [
                'contract_id' => $contract->id,
                'contract_number' => $contract->contract_number,
                'invite_id' => $invite->id,
                'ip_address' => $request->ip_address,
                'user_agent' => $request->user_agent,
                'signed_at' => $contract->signed_at,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Contract accepted successfully!',
                'contract_number' => $contract->contract_number,
                'signed_pdf_url' => $contract->signed_pdf_url,
            ]);

        } catch (\Exception $e) {
            Log::error('Contract acceptance error: ' . $e->getMessage(), [
                'token' => $token,
                'error' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while accepting the contract. Please try again.'
            ], 500);
        }
    }

    /**
     * Download signed contract PDF
     */
    public function download(string $token)
    {
        try {
            $invite = OnboardingInvite::where('token', $token)->first();

            if (!$invite) {
                abort(404, 'Contract not found');
            }

            $contract = EmploymentContract::where('onboarding_invite_id', $invite->id)->first();

            if (!$contract || !$contract->signed_pdf_path) {
                abort(404, 'Signed contract not found');
            }

            if (!Storage::exists($contract->signed_pdf_path)) {
                abort(404, 'Contract file not found');
            }

            return Storage::download($contract->signed_pdf_path, "contract-{$contract->contract_number}.pdf");

        } catch (\Exception $e) {
            Log::error('Contract download error: ' . $e->getMessage(), [
                'token' => $token,
                'error' => $e->getTraceAsString(),
            ]);

            abort(500, 'Error downloading contract');
        }
    }

    /**
     * Prepare contract data from onboarding invite
     */
    private function prepareContractData(OnboardingInvite $invite): array
    {
        return [
            'employee_name' => $invite->full_name,
            'employee_email' => $invite->email,
            'employee_phone' => $invite->phone,
            'branch_name' => $invite->branch->name,
            'branch_address' => $invite->branch->address,
            'position_name' => $invite->position->name,
            'position_grade' => $invite->position->grade,
            'start_date' => $invite->joining_date->format('M d, Y'),
            'salary' => $invite->position->salary ?? 'As per company policy',
            'generated_date' => now()->format('M d, Y'),
        ];
    }

    /**
     * Save signature image from base64 data
     */
    private function saveSignature(string $signatureData, EmploymentContract $contract): string
    {
        // Remove data URL prefix if present
        if (strpos($signatureData, 'data:image') === 0) {
            $signatureData = substr($signatureData, strpos($signatureData, ',') + 1);
        }

        // Decode base64 data
        $imageData = base64_decode($signatureData);

        if ($imageData === false) {
            throw new \Exception('Invalid signature data');
        }

        // Generate filename
        $filename = 'signatures/' . $contract->contract_number . '_signature.png';

        // Save to storage
        Storage::put($filename, $imageData);

        return $filename;
    }
}

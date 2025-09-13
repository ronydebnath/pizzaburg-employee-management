<?php

namespace App\Services;

use App\Models\EmploymentContract;
use App\Models\OnboardingInvite;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class ContractGenerationService
{
    /**
     * Generate employment contract PDF
     */
    public function generateContract(EmploymentContract $contract): ?string
    {
        try {
            // Get the onboarding invite data
            $invite = $contract->onboardingInvite;
            
            // Prepare contract data
            $contractData = array_merge([
                'contract_number' => $contract->contract_number,
                'employee_name' => $invite->full_name,
                'employee_email' => $invite->email,
                'employee_phone' => $invite->phone,
                'branch_name' => $invite->branch->name,
                'branch_address' => $invite->branch->address,
                'position_name' => $invite->position->name,
                'position_grade' => $invite->position->grade,
                'generated_date' => now()->format('M d, Y'),
                'effective_date' => now()->addDays(7)->format('M d, Y'), // Start date
            ], $contract->contract_data ?? []);
            
            // Generate PDF content
            $pdfContent = $this->generatePdfContent($contractData);
            
            // Save PDF to storage
            $pdfPath = $this->savePdf($contract, $pdfContent);
            
            Log::info('Contract PDF generated', [
                'contract_id' => $contract->id,
                'contract_number' => $contract->contract_number,
                'pdf_path' => $pdfPath,
            ]);
            
            return $pdfPath;
        } catch (\Exception $e) {
            Log::error('Failed to generate contract PDF', [
                'contract_id' => $contract->id,
                'error' => $e->getMessage(),
            ]);
            
            return null;
        }
    }

    /**
     * Generate signed contract PDF with embedded signature
     */
    public function generateSignedContract(EmploymentContract $contract): ?string
    {
        try {
            if (!$contract->signature_file_path) {
                throw new \Exception('Signature file not found');
            }
            
            // Get the onboarding invite data
            $invite = $contract->onboardingInvite;
            
            // Prepare contract data with signature
            $contractData = array_merge([
                'contract_number' => $contract->contract_number,
                'employee_name' => $invite->full_name,
                'employee_email' => $invite->email,
                'employee_phone' => $invite->phone,
                'branch_name' => $invite->branch->name,
                'branch_address' => $invite->branch->address,
                'position_name' => $invite->position->name,
                'position_grade' => $invite->position->grade,
                'generated_date' => now()->format('M d, Y'),
                'signed_date' => $contract->signed_at->format('M d, Y'),
                'signature_image' => $contract->signature_file_path,
            ], $contract->contract_data ?? []);
            
            // Generate signed PDF content
            $pdfContent = $this->generateSignedPdfContent($contractData);
            
            // Save signed PDF to storage
            $pdfPath = $this->saveSignedPdf($contract, $pdfContent);
            
            // Mark contract as completed
            $contract->markAsCompleted($pdfPath);
            
            Log::info('Signed contract PDF generated', [
                'contract_id' => $contract->id,
                'contract_number' => $contract->contract_number,
                'signed_pdf_path' => $pdfPath,
            ]);
            
            return $pdfPath;
        } catch (\Exception $e) {
            Log::error('Failed to generate signed contract PDF', [
                'contract_id' => $contract->id,
                'error' => $e->getMessage(),
            ]);
            
            return null;
        }
    }

    /**
     * Generate PDF content (placeholder - would use a PDF library like DomPDF or TCPDF)
     */
    private function generatePdfContent(array $data): string
    {
        // TODO: Implement actual PDF generation using DomPDF, TCPDF, or similar
        // For now, return HTML content that would be converted to PDF
        
        $html = "
        <!DOCTYPE html>
        <html>
        <head>
            <title>Employment Contract - {$data['contract_number']}</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 40px; }
                .header { text-align: center; margin-bottom: 30px; }
                .section { margin-bottom: 20px; }
                .signature-section { margin-top: 50px; }
            </style>
        </head>
        <body>
            <div class='header'>
                <h1>PIZZABURG EMPLOYMENT CONTRACT</h1>
                <h2>Contract Number: {$data['contract_number']}</h2>
            </div>
            
            <div class='section'>
                <h3>Employee Information</h3>
                <p><strong>Name:</strong> {$data['employee_name']}</p>
                <p><strong>Email:</strong> {$data['employee_email']}</p>
                <p><strong>Phone:</strong> {$data['employee_phone']}</p>
            </div>
            
            <div class='section'>
                <h3>Position Details</h3>
                <p><strong>Position:</strong> {$data['position_name']}</p>
                <p><strong>Grade:</strong> {$data['position_grade']}</p>
                <p><strong>Branch:</strong> {$data['branch_name']}</p>
                <p><strong>Branch Address:</strong> {$data['branch_address']}</p>
            </div>
            
            <div class='section'>
                <h3>Contract Terms</h3>
                <p><strong>Effective Date:</strong> {$data['effective_date']}</p>
                <p><strong>Generated Date:</strong> {$data['generated_date']}</p>
            </div>
            
            <div class='signature-section'>
                <h3>Signatures</h3>
                <p>Employee Signature: _________________________ Date: ___________</p>
                <p>HR Representative: _________________________ Date: ___________</p>
            </div>
        </body>
        </html>";
        
        return $html;
    }

    /**
     * Generate signed PDF content with embedded signature
     */
    private function generateSignedPdfContent(array $data): string
    {
        // TODO: Implement actual PDF generation with embedded signature image
        // For now, return HTML content that would be converted to PDF
        
        $html = "
        <!DOCTYPE html>
        <html>
        <head>
            <title>Signed Employment Contract - {$data['contract_number']}</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 40px; }
                .header { text-align: center; margin-bottom: 30px; }
                .section { margin-bottom: 20px; }
                .signature-section { margin-top: 50px; }
                .signature-image { max-width: 200px; max-height: 100px; }
            </style>
        </head>
        <body>
            <div class='header'>
                <h1>PIZZABURG EMPLOYMENT CONTRACT</h1>
                <h2>Contract Number: {$data['contract_number']}</h2>
                <p><em>This contract has been digitally signed</em></p>
            </div>
            
            <div class='section'>
                <h3>Employee Information</h3>
                <p><strong>Name:</strong> {$data['employee_name']}</p>
                <p><strong>Email:</strong> {$data['employee_email']}</p>
                <p><strong>Phone:</strong> {$data['employee_phone']}</p>
            </div>
            
            <div class='section'>
                <h3>Position Details</h3>
                <p><strong>Position:</strong> {$data['position_name']}</p>
                <p><strong>Grade:</strong> {$data['position_grade']}</p>
                <p><strong>Branch:</strong> {$data['branch_name']}</p>
                <p><strong>Branch Address:</strong> {$data['branch_address']}</p>
            </div>
            
            <div class='section'>
                <h3>Contract Terms</h3>
                <p><strong>Effective Date:</strong> {$data['effective_date']}</p>
                <p><strong>Generated Date:</strong> {$data['generated_date']}</p>
                <p><strong>Signed Date:</strong> {$data['signed_date']}</p>
            </div>
            
            <div class='signature-section'>
                <h3>Digital Signatures</h3>
                <p><strong>Employee Signature:</strong></p>
                <img src='{$data['signature_image']}' class='signature-image' alt='Employee Signature' />
                <p><strong>Signed Date:</strong> {$data['signed_date']}</p>
            </div>
        </body>
        </html>";
        
        return $html;
    }

    /**
     * Save PDF to storage
     */
    private function savePdf(EmploymentContract $contract, string $content): string
    {
        $filename = "contracts/{$contract->contract_number}.pdf";
        
        // TODO: Convert HTML to PDF and save
        // For now, save as HTML file
        Storage::put($filename, $content);
        
        return $filename;
    }

    /**
     * Save signed PDF to storage
     */
    private function saveSignedPdf(EmploymentContract $contract, string $content): string
    {
        $filename = "contracts/signed/{$contract->contract_number}_signed.pdf";
        
        // TODO: Convert HTML to PDF and save
        // For now, save as HTML file
        Storage::put($filename, $content);
        
        return $filename;
    }
}

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employment Contract - {{ $contract->contract_number }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.0.0/dist/signature_pad.umd.min.js"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body class="bg-gray-50">
    <div class="min-h-screen py-8">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
                <div class="px-6 py-4 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900">Employment Contract</h1>
                            <p class="text-sm text-gray-600 mt-1">Contract #{{ $contract->contract_number }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm text-gray-600">Generated: {{ $contract->created_at ? $contract->created_at->format('M d, Y') : 'N/A' }}</p>
                            <p class="text-sm text-gray-600">Status: 
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    @if($contract->status === 'draft') bg-yellow-100 text-yellow-800
                                    @elseif($contract->status === 'sent') bg-blue-100 text-blue-800
                                    @elseif($contract->status === 'signed') bg-green-100 text-green-800
                                    @else bg-gray-100 text-gray-800 @endif">
                                    {{ ucfirst($contract->status) }}
                                </span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            @if($contract->status === 'signed')
                <!-- Already Signed -->
                <div class="bg-green-50 border border-green-200 rounded-lg p-6 mb-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-8 w-8 text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-lg font-medium text-green-800">Contract Already Signed</h3>
                            <p class="text-green-700 mt-1">This contract was signed on {{ $contract->signed_at ? $contract->signed_at->format('M d, Y \a\t g:i A') : 'N/A' }}.</p>
                            <div class="mt-4">
                                <a href="{{ route('contract.download', $invite->token) }}" 
                                   class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    Download Signed Contract
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <!-- Contract Content -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-900">Contract Details</h2>
                    </div>
                    <div class="px-6 py-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wide">Employee Information</h3>
                                <dl class="mt-2 space-y-1">
                                    <div>
                                        <dt class="text-sm font-medium text-gray-900">Name:</dt>
                                        <dd class="text-sm text-gray-600">{{ $invite->full_name }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-900">Email:</dt>
                                        <dd class="text-sm text-gray-600">{{ $invite->email }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-900">Phone:</dt>
                                        <dd class="text-sm text-gray-600">{{ $invite->phone }}</dd>
                                    </div>
                                </dl>
                            </div>
                            <div>
                                <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wide">Position Details</h3>
                                <dl class="mt-2 space-y-1">
                                    <div>
                                        <dt class="text-sm font-medium text-gray-900">Position:</dt>
                                        <dd class="text-sm text-gray-600">{{ $invite->position->name }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-900">Grade:</dt>
                                        <dd class="text-sm text-gray-600">{{ $invite->position->grade }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-900">Branch:</dt>
                                        <dd class="text-sm text-gray-600">{{ $invite->branch->name }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-900">Start Date:</dt>
                                        <dd class="text-sm text-gray-600">
                                            @if($employeeProfile && $employeeProfile->joining_date)
                                                {{ $employeeProfile->joining_date->format('M d, Y') }}
                                            @elseif($employeeProfile && $employeeProfile->effective_from)
                                                {{ $employeeProfile->effective_from->format('M d, Y') }}
                                            @else
                                                {{ now()->format('M d, Y') }}
                                            @endif
                                        </dd>
                                    </div>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Contract Terms -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-900">Terms and Conditions</h2>
                    </div>
                    <div class="px-6 py-4">
                        <div class="prose max-w-none">
                            <p class="text-gray-700 leading-relaxed">
                                This employment contract outlines the terms and conditions of your employment with our company. 
                                By signing this contract, you agree to the following terms:
                            </p>
                            <ul class="mt-4 space-y-2 text-gray-700">
                                <li class="flex items-start">
                                    <span class="flex-shrink-0 h-5 w-5 text-green-500 mt-0.5">•</span>
                                    <span class="ml-2">You will commence employment on 
                                        @if($employeeProfile && $employeeProfile->joining_date)
                                            {{ $employeeProfile->joining_date->format('M d, Y') }}
                                        @elseif($employeeProfile && $employeeProfile->effective_from)
                                            {{ $employeeProfile->effective_from->format('M d, Y') }}
                                        @else
                                            {{ now()->format('M d, Y') }}
                                        @endif
                                    </span>
                                </li>
                                <li class="flex items-start">
                                    <span class="flex-shrink-0 h-5 w-5 text-green-500 mt-0.5">•</span>
                                    <span class="ml-2">Your position will be {{ $invite->position->name }} at {{ $invite->branch->name }}</span>
                                </li>
                                <li class="flex items-start">
                                    <span class="flex-shrink-0 h-5 w-5 text-green-500 mt-0.5">•</span>
                                    <span class="ml-2">You agree to comply with all company policies and procedures</span>
                                </li>
                                <li class="flex items-start">
                                    <span class="flex-shrink-0 h-5 w-5 text-green-500 mt-0.5">•</span>
                                    <span class="ml-2">This contract is subject to a probationary period as outlined in company policy</span>
                                </li>
                                <li class="flex items-start">
                                    <span class="flex-shrink-0 h-5 w-5 text-green-500 mt-0.5">•</span>
                                    <span class="ml-2">Either party may terminate this contract with appropriate notice as per labor law</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Signature Section -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-900">Digital Signature</h2>
                        <p class="text-sm text-gray-600 mt-1">Please sign below to accept this employment contract</p>
                    </div>
                    <div class="px-6 py-4">
                        <!-- Signature Pad -->
                        <div class="border-2 border-dashed border-gray-300 rounded-lg p-4 mb-4">
                            <canvas id="signaturePad" width="600" height="200" class="w-full border border-gray-200 rounded"></canvas>
                        </div>
                        
                        <!-- Signature Controls -->
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex space-x-2">
                                <button type="button" id="clearSignature" 
                                        class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                    Clear Signature
                                </button>
                            </div>
                            <div class="text-sm text-gray-500">
                                Sign with your mouse or touch device
                            </div>
                        </div>

                        <!-- Terms Acceptance -->
                        <div class="mb-6">
                            <label class="flex items-start">
                                <input type="checkbox" id="acceptTerms" 
                                       class="mt-1 h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                <span class="ml-2 text-sm text-gray-700">
                                    I have read and agree to the terms and conditions of this employment contract. 
                                    I understand that by signing this contract, I am entering into a legally binding agreement.
                                </span>
                            </label>
                        </div>

                        <!-- Submit Button -->
                        <div class="flex items-center justify-end">
                            <button type="button" id="submitContract" 
                                    class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
                                    disabled>
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                </svg>
                                Accept & Sign Contract
                            </button>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Success Modal -->
    <div id="successModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3 text-center">
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-green-100">
                    <svg class="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mt-4">Contract Accepted!</h3>
                <div class="mt-2 px-7 py-3">
                    <p class="text-sm text-gray-500">
                        Your employment contract has been successfully signed and accepted. 
                        You will receive a copy via email shortly.
                    </p>
                </div>
                <div class="items-center px-4 py-3">
                    <button id="downloadContract" 
                            class="px-4 py-2 bg-blue-500 text-white text-base font-medium rounded-md w-full shadow-sm hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-300">
                        Download Contract
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const canvas = document.getElementById('signaturePad');
            const signaturePad = new SignaturePad(canvas, {
                backgroundColor: 'rgba(255, 255, 255, 0)',
                penColor: 'rgb(0, 0, 0)'
            });

            // Clear signature button
            document.getElementById('clearSignature').addEventListener('click', function() {
                signaturePad.clear();
                updateSubmitButton();
            });

            // Terms acceptance checkbox
            document.getElementById('acceptTerms').addEventListener('change', function() {
                updateSubmitButton();
            });

            // Signature pad events
            signaturePad.addEventListener('beginStroke', function() {
                updateSubmitButton();
            });

            signaturePad.addEventListener('endStroke', function() {
                updateSubmitButton();
            });

            // Submit contract
            document.getElementById('submitContract').addEventListener('click', function() {
                if (!signaturePad.isEmpty() && document.getElementById('acceptTerms').checked) {
                    submitContract();
                }
            });

            // Download contract button in modal
            document.getElementById('downloadContract').addEventListener('click', function() {
                window.location.href = '{{ route("contract.download", $invite->token) }}';
            });

            function updateSubmitButton() {
                const hasSignature = !signaturePad.isEmpty();
                const termsAccepted = document.getElementById('acceptTerms').checked;
                const submitButton = document.getElementById('submitContract');
                
                submitButton.disabled = !hasSignature || !termsAccepted;
            }

            function submitContract() {
                const submitButton = document.getElementById('submitContract');
                const originalText = submitButton.innerHTML;
                
                // Show loading state
                submitButton.disabled = true;
                submitButton.innerHTML = `
                    <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Processing...
                `;

                // Get signature data
                const signatureData = signaturePad.toDataURL();
                
                // Prepare form data
                const formData = new FormData();
                formData.append('signature', signatureData);
                formData.append('ip_address', '{{ request()->ip() }}');
                formData.append('user_agent', navigator.userAgent);
                formData.append('accepted_terms', '1');
                formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));

                // Submit contract
                fetch('{{ route("contract.accept", $invite->token) }}', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Show success modal
                        document.getElementById('successModal').classList.remove('hidden');
                    } else {
                        alert('Error: ' + data.message);
                        submitButton.disabled = false;
                        submitButton.innerHTML = originalText;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while submitting the contract. Please try again.');
                    submitButton.disabled = false;
                    submitButton.innerHTML = originalText;
                });
            }

            // Resize signature pad on window resize
            function resizeCanvas() {
                const ratio = Math.max(window.devicePixelRatio || 1, 1);
                canvas.width = canvas.offsetWidth * ratio;
                canvas.height = canvas.offsetHeight * ratio;
                canvas.getContext('2d').scale(ratio, ratio);
                signaturePad.clear();
            }

            window.addEventListener('resize', resizeCanvas);
            resizeCanvas();
        });
    </script>
</body>
</html>

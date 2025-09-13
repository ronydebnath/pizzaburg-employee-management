<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Identity Verification - {{ $verification->verification_id }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body class="bg-gray-50">
    <div class="min-h-screen py-8">
        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
                <div class="px-6 py-4 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900">Identity Verification</h1>
                            <p class="text-sm text-gray-600 mt-1">Verification ID: {{ $verification->verification_id }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm text-gray-600">Status: 
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    @if($verification->status === 'pending') bg-yellow-100 text-yellow-800
                                    @elseif($verification->status === 'processing') bg-blue-100 text-blue-800
                                    @elseif($verification->status === 'approved') bg-green-100 text-green-800
                                    @elseif($verification->status === 'rejected') bg-red-100 text-red-800
                                    @else bg-gray-100 text-gray-800 @endif">
                                    {{ ucfirst($verification->status) }}
                                </span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            @if($verification->status === 'approved')
                <!-- Already Verified -->
                <div class="bg-green-50 border border-green-200 rounded-lg p-6 mb-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-8 w-8 text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-lg font-medium text-green-800">Identity Verified Successfully</h3>
                            <p class="text-green-700 mt-1">Your identity was verified on {{ $verification->verified_at->format('M d, Y \a\t g:i A') }}.</p>
                            <div class="mt-4">
                                <a href="{{ route('contract.show', $invite->token) }}" 
                                   class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                    Continue to Contract
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @elseif($verification->status === 'rejected')
                <!-- Verification Rejected -->
                <div class="bg-red-50 border border-red-200 rounded-lg p-6 mb-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-8 w-8 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-lg font-medium text-red-800">Verification Failed</h3>
                            <p class="text-red-700 mt-1">{{ $verification->rejection_reason }}</p>
                            <div class="mt-4">
                                <button onclick="retryVerification()" 
                                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                    Try Again
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <!-- Profile Information Form -->
                <form id="kycForm" class="space-y-6">
                    <!-- Personal Information -->
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h2 class="text-lg font-semibold text-gray-900">Personal Information</h2>
                            <p class="text-sm text-gray-600 mt-1">Please provide your personal details</p>
                        </div>
                        <div class="px-6 py-4 space-y-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="first_name" class="block text-sm font-medium text-gray-700">First Name *</label>
                                    <input type="text" id="first_name" name="first_name" required
                                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                           value="{{ $verification->first_name ?? $invite->first_name }}">
                                </div>
                                <div>
                                    <label for="last_name" class="block text-sm font-medium text-gray-700">Last Name *</label>
                                    <input type="text" id="last_name" name="last_name" required
                                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                           value="{{ $verification->last_name ?? $invite->last_name }}">
                                </div>
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="date_of_birth" class="block text-sm font-medium text-gray-700">Date of Birth *</label>
                                    <input type="date" id="date_of_birth" name="date_of_birth" required
                                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                           value="{{ $verification->date_of_birth }}"
                                           max="{{ date('Y-m-d') }}">
                                </div>
                                <div>
                                    <label for="national_id" class="block text-sm font-medium text-gray-700">National ID *</label>
                                    <input type="text" id="national_id" name="national_id" required
                                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                           value="{{ $verification->national_id }}">
                                </div>
                            </div>
                            
                            <div>
                                <label for="address" class="block text-sm font-medium text-gray-700">Address *</label>
                                <textarea id="address" name="address" rows="3" required
                                          class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">{{ $verification->address }}</textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Emergency Contact -->
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h2 class="text-lg font-semibold text-gray-900">Emergency Contact</h2>
                        </div>
                        <div class="px-6 py-4 space-y-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="emergency_contact_name" class="block text-sm font-medium text-gray-700">Contact Name *</label>
                                    <input type="text" id="emergency_contact_name" name="emergency_contact_name" required
                                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                           value="{{ $verification->emergency_contact_name }}">
                                </div>
                                <div>
                                    <label for="emergency_contact_phone" class="block text-sm font-medium text-gray-700">Contact Phone *</label>
                                    <input type="tel" id="emergency_contact_phone" name="emergency_contact_phone" required
                                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                           value="{{ $verification->emergency_contact_phone }}">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Photo Upload Section -->
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h2 class="text-lg font-semibold text-gray-900">Profile Photo</h2>
                            <p class="text-sm text-gray-600 mt-1">Upload your profile photo</p>
                        </div>
                        <div class="px-6 py-4">
                            <!-- Photo Upload Options -->
                            <div class="space-y-4">
                                <!-- File Upload -->
                                <div>
                                    <label for="profile_photo" class="block text-sm font-medium text-gray-700">Upload Photo</label>
                                    <input type="file" id="profile_photo" name="profile_photo" accept="image/*" 
                                           class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                                </div>
                                
                                <!-- OR Divider -->
                            </div>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                        <div class="px-6 py-4">
                            <div class="flex items-center justify-end">
                                <button type="submit" id="submitKyc" 
                                        class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    Submit Information
                                </button>
                            </div>
                        </div>
                    </div>
                </form>

                <!-- Processing Status -->
                <div id="processingStatus" class="hidden bg-blue-50 border border-blue-200 rounded-lg p-6 mb-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="animate-spin h-8 w-8 text-blue-400" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-lg font-medium text-blue-800">Processing Verification</h3>
                            <p class="text-blue-700 mt-1">Please wait while we verify your identity...</p>
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
                <h3 class="text-lg font-medium text-gray-900 mt-4">Information Submitted!</h3>
                <div class="mt-2 px-7 py-3">
                    <p class="text-sm text-gray-500">
                        Your information has been submitted successfully. HR will review your details and contact you for the next steps.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <script>

        document.addEventListener('DOMContentLoaded', function() {
            const processingStatus = document.getElementById('processingStatus');

            // Form submission
            document.getElementById('kycForm').addEventListener('submit', function(e) {
                e.preventDefault();
                submitKycForm();
            });

            function submitKycForm() {
                processingStatus.classList.remove('hidden');
                
                const formData = new FormData();
                
                // Add form fields
                formData.append('first_name', document.getElementById('first_name').value);
                formData.append('last_name', document.getElementById('last_name').value);
                formData.append('date_of_birth', document.getElementById('date_of_birth').value);
                formData.append('national_id', document.getElementById('national_id').value);
                formData.append('address', document.getElementById('address').value);
                formData.append('emergency_contact_name', document.getElementById('emergency_contact_name').value);
                formData.append('emergency_contact_phone', document.getElementById('emergency_contact_phone').value);
                formData.append('verification_id', '{{ $verification->verification_id }}');
                formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
                
                // Add profile photo (file upload only)
                const profilePhoto = document.getElementById('profile_photo').files[0];
                if (profilePhoto) {
                    formData.append('profile_photo', profilePhoto);
                }

                fetch('{{ route("kyc.verify", $invite->token) }}', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                    }
                })
                .then(response => response.json())
                .then(data => {
                    processingStatus.classList.add('hidden');
                    
                    if (data.success) {
                        document.getElementById('successModal').classList.remove('hidden');
                    } else {
                        alert('Submission failed: ' + data.message);
                    }
                })
                .catch(error => {
                    processingStatus.classList.add('hidden');
                    console.error('Error:', error);
                    alert('An error occurred during submission. Please try again.');
                });
            }

            // Clean up camera stream when page unloads
            window.addEventListener('beforeunload', function() {
                if (stream) {
                    stream.getTracks().forEach(track => track.stop());
                }
            });
        });

        function retryVerification() {
            fetch('{{ route("kyc.retry", $invite->token) }}', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.reload();
                } else {
                    alert('Error creating new verification session: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred. Please try again.');
            });
        }
    </script>
</body>
</html>

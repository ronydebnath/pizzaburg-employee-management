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
                <!-- Verification Instructions -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-900">Selfie Liveness Verification</h2>
                    </div>
                    <div class="px-6 py-4">
                        <div class="mb-6">
                            <h3 class="text-sm font-medium text-gray-900 mb-3">Please follow these instructions:</h3>
                            <ul class="text-sm text-gray-600 space-y-2">
                                <li class="flex items-start">
                                    <span class="flex-shrink-0 h-5 w-5 text-blue-500 mt-0.5">•</span>
                                    <span class="ml-2">Ensure you have good lighting on your face</span>
                                </li>
                                <li class="flex items-start">
                                    <span class="flex-shrink-0 h-5 w-5 text-blue-500 mt-0.5">•</span>
                                    <span class="ml-2">Look directly at the camera</span>
                                </li>
                                <li class="flex items-start">
                                    <span class="flex-shrink-0 h-5 w-5 text-blue-500 mt-0.5">•</span>
                                    <span class="ml-2">Remove any glasses, hats, or face coverings</span>
                                </li>
                                <li class="flex items-start">
                                    <span class="flex-shrink-0 h-5 w-5 text-blue-500 mt-0.5">•</span>
                                    <span class="ml-2">Keep your face centered in the frame</span>
                                </li>
                                <li class="flex items-start">
                                    <span class="flex-shrink-0 h-5 w-5 text-blue-500 mt-0.5">•</span>
                                    <span class="ml-2">Click "Take Selfie" when ready</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Camera Section -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-900">Take Your Selfie</h2>
                    </div>
                    <div class="px-6 py-4">
                        <!-- Camera Preview -->
                        <div class="relative mb-4">
                            <video id="video" width="400" height="300" class="w-full max-w-md mx-auto border border-gray-300 rounded-lg" autoplay muted></video>
                            <canvas id="canvas" width="400" height="300" class="hidden"></canvas>
                        </div>

                        <!-- Camera Controls -->
                        <div class="flex flex-col items-center space-y-4">
                            <div class="flex space-x-4">
                                <button id="startCamera" 
                                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                    Start Camera
                                </button>
                                
                                <button id="takeSelfie" 
                                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 disabled:opacity-50 disabled:cursor-not-allowed"
                                        disabled>
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                    Take Selfie
                                </button>
                            </div>

                            <!-- Selfie Preview -->
                            <div id="selfiePreview" class="hidden">
                                <img id="selfieImage" class="w-64 h-48 object-cover border border-gray-300 rounded-lg" alt="Captured selfie">
                                <div class="mt-4 flex space-x-4">
                                    <button id="retakeSelfie" 
                                            class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                        Retake
                                    </button>
                                    <button id="submitSelfie" 
                                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                        Submit for Verification
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

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
                <h3 class="text-lg font-medium text-gray-900 mt-4">Verification Successful!</h3>
                <div class="mt-2 px-7 py-3">
                    <p class="text-sm text-gray-500">
                        Your identity has been successfully verified. You can now proceed to the next step.
                    </p>
                </div>
                <div class="items-center px-4 py-3">
                    <a href="{{ route('contract.show', $invite->token) }}" 
                       class="px-4 py-2 bg-blue-500 text-white text-base font-medium rounded-md w-full shadow-sm hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-300">
                        Continue to Contract
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script>
        let stream = null;
        let capturedImage = null;

        document.addEventListener('DOMContentLoaded', function() {
            const video = document.getElementById('video');
            const canvas = document.getElementById('canvas');
            const startCameraBtn = document.getElementById('startCamera');
            const takeSelfieBtn = document.getElementById('takeSelfie');
            const retakeSelfieBtn = document.getElementById('retakeSelfie');
            const submitSelfieBtn = document.getElementById('submitSelfie');
            const selfiePreview = document.getElementById('selfiePreview');
            const selfieImage = document.getElementById('selfieImage');
            const processingStatus = document.getElementById('processingStatus');

            // Start camera
            startCameraBtn.addEventListener('click', async function() {
                try {
                    stream = await navigator.mediaDevices.getUserMedia({ 
                        video: { 
                            width: 400, 
                            height: 300,
                            facingMode: 'user' // Front camera
                        } 
                    });
                    video.srcObject = stream;
                    takeSelfieBtn.disabled = false;
                    startCameraBtn.textContent = 'Camera Active';
                    startCameraBtn.disabled = true;
                } catch (error) {
                    alert('Error accessing camera: ' + error.message);
                }
            });

            // Take selfie
            takeSelfieBtn.addEventListener('click', function() {
                const context = canvas.getContext('2d');
                context.drawImage(video, 0, 0, 400, 300);
                capturedImage = canvas.toDataURL('image/jpeg', 0.8);
                
                selfieImage.src = capturedImage;
                selfiePreview.classList.remove('hidden');
                video.style.display = 'none';
            });

            // Retake selfie
            retakeSelfieBtn.addEventListener('click', function() {
                selfiePreview.classList.add('hidden');
                video.style.display = 'block';
                capturedImage = null;
            });

            // Submit selfie
            submitSelfieBtn.addEventListener('click', function() {
                if (capturedImage) {
                    submitVerification(capturedImage);
                }
            });

            function submitVerification(selfieData) {
                processingStatus.classList.remove('hidden');
                
                const formData = new FormData();
                formData.append('selfie', selfieData);
                formData.append('verification_id', '{{ $verification->verification_id }}');
                formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));

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
                        alert('Verification failed: ' + data.message);
                        // Reset for retry
                        selfiePreview.classList.add('hidden');
                        video.style.display = 'block';
                        capturedImage = null;
                    }
                })
                .catch(error => {
                    processingStatus.classList.add('hidden');
                    console.error('Error:', error);
                    alert('An error occurred during verification. Please try again.');
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

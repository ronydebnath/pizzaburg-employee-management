<x-filament-panels::page>
    <div class="space-y-6">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="mb-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-2">Account Settings</h2>
                <p class="text-gray-600">Manage your account information, profile details, and system preferences.</p>
            </div>

            {{ $this->form }}
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="mb-4">
                <h3 class="text-lg font-medium text-gray-900 mb-2">Quick Actions</h3>
                <p class="text-gray-600 text-sm">Common settings and account management tasks.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="p-4 border border-gray-200 rounded-lg hover:border-blue-300 transition-colors">
                    <div class="flex items-center space-x-3">
                        <div class="flex-shrink-0">
                            <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h4 class="text-sm font-medium text-gray-900">Change Password</h4>
                            <p class="text-sm text-gray-500">Update your account password</p>
                        </div>
                        <div class="flex-shrink-0">
                            <a href="{{ route('filament.portal.pages.change-password') }}" 
                               class="text-blue-600 hover:text-blue-500 text-sm font-medium">
                                Go →
                            </a>
                        </div>
                    </div>
                </div>

                <div class="p-4 border border-gray-200 rounded-lg hover:border-green-300 transition-colors">
                    <div class="flex items-center space-x-3">
                        <div class="flex-shrink-0">
                            <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h4 class="text-sm font-medium text-gray-900">View Contracts</h4>
                            <p class="text-sm text-gray-500">Access your employment contracts</p>
                        </div>
                        <div class="flex-shrink-0">
                            <a href="{{ route('filament.portal.pages.dashboard') }}" 
                               class="text-green-600 hover:text-green-500 text-sm font-medium">
                                View →
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="mb-4">
                <h3 class="text-lg font-medium text-gray-900 mb-2">Account Information</h3>
                <p class="text-gray-600 text-sm">Your current account details and status.</p>
            </div>

            <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                <div>
                    <dt class="text-sm font-medium text-gray-500">Account Status</dt>
                    <dd class="mt-1 text-sm text-gray-900">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                            Active
                        </span>
                    </dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Member Since</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ Auth::user()->created_at->format('M d, Y') }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Branch</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ Auth::user()->branch?->name ?? 'Not assigned' }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Role</dt>
                    <dd class="mt-1 text-sm text-gray-900 capitalize">{{ Auth::user()->role }}</dd>
                </div>
            </dl>
        </div>
    </div>
</x-filament-panels::page>

<x-filament-panels::page>
    <div class="space-y-6">
        <x-filament::section heading="Employment Contract">
            @if($contract)
                <dl class="grid gap-4 sm:grid-cols-2 text-sm">
                    <div>
                        <dt class="text-gray-500">Contract Number</dt>
                        <dd class="font-medium text-gray-900">{{ $contract->contract_number }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Status</dt>
                        <dd>
                            <x-filament::badge :color="match($contract->status) {
                                'draft' => 'gray',
                                'sent' => 'info',
                                'signed' => 'warning',
                                'completed' => 'success',
                                default => 'gray',
                            }">{{ ucfirst($contract->status) }}</x-filament::badge>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Generated</dt>
                        <dd class="font-medium text-gray-900">{{ optional($contract->created_at)->format('M d, Y h:i a') ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Signed At</dt>
                        <dd class="font-medium text-gray-900">{{ optional($contract->signed_at)->format('M d, Y h:i a') ?? '—' }}</dd>
                    </div>
                </dl>

                <div class="mt-6 flex flex-col gap-3 sm:flex-row">
                    @if($downloadUrl)
                        <x-filament::button tag="a" color="primary" href="{{ $downloadUrl }}" icon="heroicon-o-arrow-down-tray" target="_blank">
                            Download Signed Contract
                        </x-filament::button>
                    @endif

                    @if($contract->signed_pdf_url)
                        <x-filament::button tag="a" color="secondary" href="{{ $contract->signed_pdf_url }}" target="_blank" icon="heroicon-o-eye">
                            View Signed PDF
                        </x-filament::button>
                    @elseif($contract->contract_file_path)
                        <x-filament::button tag="a" color="secondary" href="{{ $contract->contract_file_path ? Storage::url($contract->contract_file_path) : '#' }}" target="_blank" icon="heroicon-o-eye">
                            View Generated Contract
                        </x-filament::button>
                    @endif
                </div>
            @else
                <p class="text-sm text-gray-500">No employment contract is currently associated with your account. Please contact HR if you believe this is a mistake.</p>
            @endif
        </x-filament::section>
    </div>
</x-filament-panels::page>

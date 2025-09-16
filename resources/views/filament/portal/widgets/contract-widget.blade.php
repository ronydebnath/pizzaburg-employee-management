<x-filament-widgets::widget>
    <x-filament::section heading="Employment Contract">
        @if(isset($contract) && $contract)
            <div class="text-sm space-y-1">
                <div>Status: <strong>{{ ucfirst($contract->status) }}</strong></div>
                <div>Number: {{ $contract->contract_number }}</div>
                @if($contract->signed_pdf_path)
                    <a class="text-amber-400 font-semibold" href="{{ Storage::url($contract->signed_pdf_path) }}" target="_blank">Download signed PDF</a>
                @elseif($contract->contract_file_path)
                    <a class="text-amber-400 font-semibold" href="{{ Storage::url($contract->contract_file_path) }}" target="_blank">View contract</a>
                @else
                    <div class="text-gray-400">No file available yet.</div>
                @endif
            </div>
        @else
            <div class="text-gray-400">No contract available.</div>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>



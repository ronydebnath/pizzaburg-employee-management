<x-filament-widgets::widget>
    <x-filament::section heading="Profile">
        @if(isset($profile) && $profile)
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-sm">
                <div>
                    <div class="text-gray-400">Name</div>
                    <div class="font-medium">{{ $profile->full_name }}</div>
                </div>
                <div>
                    <div class="text-gray-400">Email</div>
                    <div class="font-medium">{{ $user->email }}</div>
                </div>
                <div>
                    <div class="text-gray-400">Branch</div>
                    <div class="font-medium">{{ $profile->branch?->name ?? '—' }}</div>
                </div>
                <div>
                    <div class="text-gray-400">Position</div>
                    <div class="font-medium">{{ $profile->position?->name ?? '—' }}</div>
                </div>
                <div>
                    <div class="text-gray-400">Employee ID</div>
                    <div class="font-medium">{{ $profile->employee_id ?? '—' }}</div>
                </div>
                <div>
                    <div class="text-gray-400">Joining Date</div>
                    <div class="font-medium">{{ optional($profile->joining_date)->toDateString() ?? '—' }}</div>
                </div>
            </div>
        @else
            <div class="text-gray-400">No profile found.</div>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>



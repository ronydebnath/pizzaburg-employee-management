<?php

namespace App\Filament\Pages\Portal;

use App\Models\EmploymentContract;
use App\Models\OnboardingInvite;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;

class Contracts extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationLabel = 'My Contract';
    protected static ?string $navigationGroup = 'Self Service';
    protected static ?int $navigationSort = 20;

    protected static string $view = 'filament.portal.pages.contracts';

    public ?EmploymentContract $contract = null;

    public ?string $downloadUrl = null;

    public function mount(): void
    {
        $user = Auth::user();

        $invite = OnboardingInvite::where('email', $user->email)
            ->latest('id')
            ->first();

        if (!$invite) {
            return;
        }

        $this->contract = EmploymentContract::where('onboarding_invite_id', $invite->id)
            ->latest('id')
            ->first();

        if ($this->contract) {
            $this->downloadUrl = route('contract.download', $invite->token);
        }
    }

    protected function getViewData(): array
    {
        return [
            'contract' => $this->contract,
            'downloadUrl' => $this->downloadUrl,
        ];
    }
}

<?php

namespace App\Filament\Pages\Portal;

use App\Models\EmployeeProfile;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;

class UpdateProfile extends Page implements Forms\Contracts\HasForms
{
    use Forms\Concerns\InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-user-circle';
    protected static ?string $navigationLabel = 'Update Profile';
    protected static ?string $title = 'Update Profile';
    protected static string $view = 'filament.portal.pages.update-profile';
    protected static bool $shouldRegisterNavigation = true;

    public ?int $profile_id = null;
    /**
     * FileUpload component may provide an array payload depending on drivers/options,
     * so allow array|string|null here to avoid type errors during hydration.
     */
    public array|string|null $profile_image_path = null;

    public function mount(): void
    {
        $profile = EmployeeProfile::where('user_id', Auth::id())->first();
        if ($profile) {
            $this->profile_id = $profile->id;
            $this->form->fill([
                'profile_image_path' => $profile->profile_image_path ?? null,
            ]);
        }
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Profile Photo')
                    ->schema([
                        Forms\Components\FileUpload::make('profile_image_path')
                            ->image()
                            ->directory('private/profile-photos')
                            ->preserveFilenames()
                            ->imageEditor()
                            ->downloadable()
                            ->previewable(true),
                    ])
            ]);
    }

    public function save(): void
    {
        $data = $this->form->getState();

        $profile = EmployeeProfile::firstOrCreate(
            ['user_id' => Auth::id()],
            ['branch_id' => Auth::user()->branch_id]
        );

        $image = $data['profile_image_path'] ?? null;
        if (is_array($image)) {
            $image = $image['path'] ?? ($image[0]['path'] ?? null);
        }

        $profile->update([
            'profile_image_path' => $image ?? $profile->profile_image_path,
        ]);

        Notification::make()
            ->title('Profile updated')
            ->success()
            ->send();
    }
}



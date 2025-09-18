<?php

namespace App\Filament\Pages\Admin;

use App\Models\EmployeeProfile;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class Settings extends Page implements Forms\Contracts\HasForms
{
    use Forms\Concerns\InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected static ?string $navigationLabel = 'Settings';
    protected static ?string $title = 'Settings';
    protected static string $view = 'filament.admin.pages.settings';
    protected static ?string $navigationGroup = 'Settings';
    protected static ?int $navigationSort = 100;

    public array|string|null $profile_image_path = null;

    public function mount(): void
    {
        $user = Auth::user();
        $profile = EmployeeProfile::where('user_id', $user->id)->first();

        $this->form->fill([
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'profile_image_path' => $profile?->profile_image_path,
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Tabs::make('Settings')
                    ->tabs([
                        Forms\Components\Tabs\Tab::make('General')
                            ->icon('heroicon-o-user')
                            ->schema([
                                Forms\Components\Section::make('Profile Information')
                                    ->schema([
                                        Forms\Components\TextInput::make('name')->required()->maxLength(255),
                                        Forms\Components\TextInput::make('email')->email()->required()->maxLength(255),
                                        Forms\Components\TextInput::make('phone')->tel()->maxLength(20),
                                    ])->columns(2),
                            ]),

                        Forms\Components\Tabs\Tab::make('Profile')
                            ->icon('heroicon-o-user-circle')
                            ->schema([
                                Forms\Components\Section::make('Profile Photo')
                                    ->schema([
                                        Forms\Components\FileUpload::make('profile_image_path')
                                            ->image()
                                            ->directory('private/profile-photos')
                                            ->preserveFilenames()
                                            ->imageEditor()
                                            ->downloadable()
                                            ->previewable(true)
                                            ->columnSpanFull(),
                                    ]),
                            ]),

                        Forms\Components\Tabs\Tab::make('HR Signature')
                            ->icon('heroicon-o-pencil-square')
                            ->schema([
                                Forms\Components\Section::make('HR Representative Signature')
                                    ->schema([
                                        Forms\Components\FileUpload::make('hr_signature')
                                            ->label('HR Signature Image')
                                            ->image()
                                            ->directory('hr-signatures')
                                            ->filename('hr-signature')
                                            ->preserveFilenames(false)
                                            ->acceptedFileTypes(['image/png', 'image/jpeg', 'image/jpg'])
                                            ->maxSize(2048)
                                            ->imageEditor()
                                            ->previewable(true)
                                            ->downloadable()
                                            ->afterStateUpdated(function ($state) {
                                                if ($state) {
                                                    $this->saveHrSignature($state);
                                                }
                                            })
                                            ->columnSpanFull(),

                                        Forms\Components\Placeholder::make('current_signature_info')
                                            ->label('Current Signature Status')
                                            ->content(function () {
                                                $hasSignature = Storage::exists('hr-signatures/hr-signature.png');
                                                return $hasSignature
                                                    ? '✅ Signature uploaded and ready for contracts'
                                                    : '❌ No signature uploaded yet';
                                            })
                                            ->columnSpanFull(),
                                    ]),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    protected function getActions(): array
    {
        return [
            Forms\Components\Actions\Action::make('save')
                ->label('Save Changes')
                ->submit('save'),
        ];
    }

    public function save(): void
    {
        $data = $this->form->getState();

        $user = Auth::user();
        $user->update([
            'name' => $data['name'] ?? $user->name,
            'email' => $data['email'] ?? $user->email,
            'phone' => $data['phone'] ?? $user->phone,
        ]);

        $profile = EmployeeProfile::firstOrCreate(
            ['user_id' => $user->id],
            ['branch_id' => $user->branch_id]
        );

        $image = $data['profile_image_path'] ?? null;
        if (is_array($image)) {
            $image = $image['path'] ?? ($image[0]['path'] ?? null);
        }

        if ($image) {
            $profile->update(['profile_image_path' => $image]);
        }

        Notification::make()
            ->title('Settings saved successfully')
            ->success()
            ->send();
    }

    private function saveHrSignature($signatureData): void
    {
        if (is_array($signatureData)) {
            $signatureData = $signatureData[0]['path'] ?? null;
        }

        if ($signatureData) {
            if (!Storage::exists('hr-signatures')) {
                Storage::makeDirectory('hr-signatures');
            }

            $sourcePath = $signatureData;
            $targetPath = 'hr-signatures/hr-signature.png';

            if (Storage::exists($sourcePath)) {
                Storage::copy($sourcePath, $targetPath);

                Notification::make()
                    ->title('HR signature updated successfully')
                    ->success()
                    ->send();
            }
        }
    }
}



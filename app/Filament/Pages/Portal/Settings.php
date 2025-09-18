<?php

namespace App\Filament\Pages\Portal;

use App\Models\EmployeeProfile;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class Settings extends Page implements Forms\Contracts\HasForms
{
    use Forms\Concerns\InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected static ?string $navigationLabel = 'Settings';
    protected static ?string $title = 'Settings';
    protected static string $view = 'filament.portal.pages.settings';
    protected static ?string $navigationGroup = 'Account';
    protected static ?int $navigationSort = 10;

    public ?int $profile_id = null;
    public array|string|null $profile_image_path = null;

    public function mount(): void
    {
        $user = Auth::user();
        $profile = EmployeeProfile::where('user_id', $user->id)->first();
        
        if ($profile) {
            $this->profile_id = $profile->id;
            $this->form->fill([
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'profile_image_path' => $profile->profile_image_path ?? null,
            ]);
        }
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Tabs::make('Settings')
                    ->tabs([
                        Forms\Components\Tabs\Tab::make('Profile Information')
                            ->icon('heroicon-o-user')
                            ->schema([
                                Forms\Components\Section::make('Personal Information')
                                    ->description('Update your personal details')
                                    ->schema([
                                        Forms\Components\TextInput::make('name')
                                            ->required()
                                            ->maxLength(255),
                                        Forms\Components\TextInput::make('email')
                                            ->email()
                                            ->required()
                                            ->maxLength(255),
                                        Forms\Components\TextInput::make('phone')
                                            ->tel()
                                            ->maxLength(20),
                                    ])
                                    ->columns(2),

                                Forms\Components\Section::make('Profile Photo')
                                    ->description('Upload and manage your profile picture')
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
                                    ->description('Manage the HR signature that appears on employment contracts')
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
                                            ->helperText('Upload a PNG or JPEG image of the HR representative signature. Recommended size: 200x100 pixels with transparent background.')
                                            ->afterStateUpdated(function ($state) {
                                                // Automatically save when file is uploaded
                                                if ($state) {
                                                    $this->saveHrSignature($state);
                                                }
                                            })
                                            ->columnSpanFull(),

                                        Forms\Components\Placeholder::make('current_signature_info')
                                            ->label('Current Signature Status')
                                            ->content(function () {
                                                $hasSignature = Storage::exists('hr-signatures/hr-signature.png');
                                                $status = $hasSignature ? '✅ Signature uploaded' : '❌ No signature uploaded';
                                                $message = $hasSignature 
                                                    ? 'HR signature is available for use in contracts.'
                                                    : 'Please upload an HR signature image to use in employment contracts.';
                                                
                                                return "<div class='p-4 rounded-lg border " . 
                                                       ($hasSignature ? 'bg-green-50 border-green-200' : 'bg-yellow-50 border-yellow-200') . 
                                                       "'><p class='font-medium " . 
                                                       ($hasSignature ? 'text-green-800' : 'text-yellow-800') . 
                                                       "'>{$status}</p><p class='text-sm " . 
                                                       ($hasSignature ? 'text-green-600' : 'text-yellow-600') . 
                                                       "'>{$message}</p></div>";
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
                ->submit('save')
                ->keyBindings(['mod+s']),
        ];
    }

    public function save(): void
    {
        $data = $this->form->getState();

        // Update user information
        $user = Auth::user();
        $user->update([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
        ]);

        // Update or create employee profile
        $profile = EmployeeProfile::firstOrCreate(
            ['user_id' => $user->id],
            ['branch_id' => $user->branch_id]
        );

        $image = $data['profile_image_path'] ?? null;
        if (is_array($image)) {
            $image = $image['path'] ?? ($image[0]['path'] ?? null);
        }

        $profile->update([
            'profile_image_path' => $image ?? $profile->profile_image_path,
        ]);

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
            // Ensure the hr-signatures directory exists
            if (!Storage::exists('hr-signatures')) {
                Storage::makeDirectory('hr-signatures');
            }

            // Copy the uploaded file to the expected location
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

<?php

namespace App\Filament\Pages\Admin;

use App\Models\EmployeeProfile;
use Filament\Actions;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;

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
    public array|string|null $hr_signature = null;
    public ?string $name = null;
    public ?string $email = null;
    public ?string $phone = null;

    public function mount(): void
    {
        $user = Auth::user();
        $profile = EmployeeProfile::where('user_id', $user->id)->first();

        $this->name = $user->name;
        $this->email = $user->email;
        $this->phone = $user->phone;
        $this->profile_image_path = $profile?->profile_image_path;
        $this->hr_signature = Storage::exists('hr-signatures/hr-signature.png') ? 'hr-signatures/hr-signature.png' : null;

        $this->form->fill([
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'profile_image_path' => $this->profile_image_path,
            'hr_signature' => $this->hr_signature,
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

                                                if (!$hasSignature) {
                                                    return new \Illuminate\Support\HtmlString('<div class="text-sm text-gray-600">❌ No signature uploaded yet</div>');
                                                }

                                                $url = Storage::url('hr-signatures/hr-signature.png');
                                                $timestamp = Storage::lastModified('hr-signatures/hr-signature.png');
                                                $updatedAt = $timestamp ? Carbon::createFromTimestamp($timestamp) : null;

                                                return new \Illuminate\Support\HtmlString(
                                                    '<div class="space-y-3">'
                                                    . '<div class="text-sm text-emerald-600">✅ Signature uploaded and ready for contracts</div>'
                                                    . '<div><img src="' . e($url) . '" alt="HR Signature" class="max-h-24 border rounded-md bg-white"></div>'
                                                    . '<div class="text-xs text-gray-500">' . ($updatedAt ? 'Last updated ' . $updatedAt->diffForHumans() : '') . '</div>'
                                                    . '</div>'
                                                );
                                            })
                                            ->columnSpanFull(),
                                    ]),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    protected function getFormActions(): array
    {
        return [
            Actions\Action::make('save')
                ->label('Save Changes')
                ->submit('save')
                ->color('success'),
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
            [
                'branch_id' => $user->branch_id,
                'first_name' => Str::of($user->name)->before(' ')->value() ?? $user->name,
                'last_name' => Str::of($user->name)->after(' ')->value(),
                'employee_id' => sprintf('EMP-%06d', $user->id),
                'joining_date' => $user->created_at?->toDateString() ?? now()->toDateString(),
                'effective_from' => $user->created_at?->toDateString() ?? now()->toDateString(),
            ]
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

            $targetPath = 'hr-signatures/hr-signature.png';

            if (Storage::exists($signatureData)) {
                Storage::put($targetPath, Storage::get($signatureData));
                Storage::delete($signatureData);
            }

            foreach (Storage::files('hr-signatures') as $file) {
                if ($file !== $targetPath) {
                    Storage::delete($file);
                }
            }

            Notification::make()
                ->title('HR signature updated successfully')
                ->success()
                ->send();
        }
    }
}

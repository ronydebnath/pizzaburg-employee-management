<?php

namespace App\Filament\Pages\Portal;

use App\Models\EmployeeProfile;
use App\Models\OnboardingInvite;
use App\Services\KycService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class UpdateProfile extends Page implements Forms\Contracts\HasForms
{
    use Forms\Concerns\InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-user-circle';
    protected static ?string $navigationLabel = 'Update Profile';
    protected static ?string $title = 'Update Profile';
    protected static string $view = 'filament.portal.pages.update-profile';
    protected static bool $shouldRegisterNavigation = true;
    protected static ?string $navigationGroup = 'Self Service';
    protected static ?int $navigationSort = 10;

    public ?int $profile_id = null;

    public array|string|null $profile_image_path = null;

    public array|string|null $national_id_photo = null;

    protected ?EmployeeProfile $profileRecord = null;

    public array $data = [];

    public function mount(): void
    {
        $this->profileRecord = EmployeeProfile::with(['branch', 'position'])
            ->where('user_id', Auth::id())
            ->first();

        if ($this->profileRecord) {
            $profile = $this->profileRecord;
            $meta = $profile->meta ?? [];

            $this->profile_id = $profile->id;
            $this->form->fill([
                'profile_image_path' => $profile->profile_image_path ?? null,
                'national_id_photo' => Arr::get($meta, 'national_id_photo_path'),
                'first_name' => $profile->first_name ?? Str::of($profile->user?->name)->before(' ')->value() ?? Auth::user()->name,
                'last_name' => $profile->last_name ?? Str::of($profile->user?->name)->after(' ')->value(),
                'branch_name' => $profile->branch?->name ?? Auth::user()->branch?->name,
                'position_name' => $profile->position?->name,
                'date_of_birth' => optional($profile->date_of_birth)->format('Y-m-d'),
                'joining_date' => optional($profile->joining_date)->format('Y-m-d'),
                'emergency_contact_name' => Arr::get($meta, 'emergency_contact_name'),
                'emergency_contact_phone' => Arr::get($meta, 'emergency_contact_phone'),
                'national_id' => Arr::get($meta, 'national_id'),
                'address' => Arr::get($meta, 'address'),
            ]);
        } else {
            $user = Auth::user();
            $this->form->fill([
                'first_name' => $user->name,
                'branch_name' => $user->branch?->name,
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
                            ->disk('private')
                            ->directory('private/profile-photos')
                            ->preserveFilenames()
                            ->imageEditor()
                            ->downloadable()
                            ->previewable(true),
                        Forms\Components\FileUpload::make('national_id_photo')
                            ->label('National ID Image')
                            ->image()
                            ->disk('private')
                            ->directory('kyc/documents')
                            ->helperText('Upload the front side of your National ID if it has changed.'),
                    ])
                    ->collapsible(),

                Forms\Components\Section::make('Personal Information')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('first_name')
                                    ->label('First Name')
                                    ->maxLength(255)
                                    ->required(),
                                Forms\Components\TextInput::make('last_name')
                                    ->label('Last Name')
                                    ->maxLength(255)
                                    ->nullable(),
                                Forms\Components\TextInput::make('national_id')
                                    ->label('National ID')
                                    ->maxLength(255)
                                    ->nullable(),
                                Forms\Components\DatePicker::make('date_of_birth')
                                    ->label('Date of Birth')
                                    ->native(false)
                                    ->nullable(),
                            ]),

                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('branch_name')
                                    ->label('Branch')
                                    ->disabled(),
                                Forms\Components\TextInput::make('position_name')
                                    ->label('Position')
                                    ->disabled(),
                                Forms\Components\DatePicker::make('joining_date')
                                    ->label('Joining Date')
                                    ->native(false)
                                    ->disabled(),
                            ]),

                        Forms\Components\Textarea::make('address')
                            ->label('Address')
                            ->rows(3)
                            ->nullable(),

                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('emergency_contact_name')
                                    ->label('Emergency Contact Name')
                                    ->maxLength(255)
                                    ->nullable(),
                                Forms\Components\TextInput::make('emergency_contact_phone')
                                    ->label('Emergency Contact Phone')
                                    ->maxLength(50)
                                    ->nullable(),
                            ]),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        $profile = $this->profileRecord ?? EmployeeProfile::firstOrCreate(
            ['user_id' => Auth::id()],
            ['branch_id' => Auth::user()->branch_id]
        );

        $profileImagePath = $this->extractFilePath($data['profile_image_path'] ?? null) ?? $profile->profile_image_path;
        $nationalIdPath = $this->extractFilePath($data['national_id_photo'] ?? null) ?? Arr::get($profile->meta ?? [], 'national_id_photo_path');

        $invite = OnboardingInvite::where('email', Auth::user()->email)
            ->latest('id')
            ->first();

        if (!$invite) {
            Notification::make()
                ->title('Unable to submit changes')
                ->body('No onboarding record was found for your account. Please contact HR for assistance.')
                ->danger()
                ->send();

            return;
        }

        $kycService = app(KycService::class);
        $verification = $kycService->createVerification($invite, 'profile_update', 'employee_portal');

        $verification->update([
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'] ?? null,
            'date_of_birth' => $data['date_of_birth'] ?? null,
            'national_id' => $data['national_id'] ?? null,
            'address' => $data['address'] ?? null,
            'emergency_contact_name' => $data['emergency_contact_name'] ?? null,
            'emergency_contact_phone' => $data['emergency_contact_phone'] ?? null,
            'profile_image_path' => $profileImagePath,
            'document_image_path' => $nationalIdPath,
            'status' => 'pending_hr_review',
            'verification_data' => array_filter([
                'submitted_by' => Auth::user()->name,
                'submitted_at' => now()->toISOString(),
                'submission_method' => 'employee_portal',
                'previous_profile_id' => $profile->id,
            ]),
        ]);

        Notification::make()
            ->title('Profile update submitted')
            ->body('Your changes have been sent to HR for review. You will be notified once they are approved.')
            ->success()
            ->send();
    }

    private function extractFilePath(mixed $value): ?string
    {
        if (is_string($value)) {
            return $value;
        }

        if (is_array($value)) {
            return $value['path'] ?? ($value[0]['path'] ?? null);
        }

        return null;
    }
}

<?php

namespace App\Filament\Pages\Admin;

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
    protected static string $view = 'filament.admin.pages.update-profile';
    protected static bool $shouldRegisterNavigation = true;
    protected static ?string $navigationGroup = 'Settings';
    protected static ?int $navigationSort = 1;

    public ?int $profile_id = null;
    public ?string $first_name = null;
    public ?string $last_name = null;
    public ?string $employee_id = null;
    public ?string $joining_date = null;
    public ?string $effective_from = null;
    public ?string $date_of_birth = null;
    public ?int $position_id = null;
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
                'first_name' => $profile->first_name,
                'last_name' => $profile->last_name,
                'employee_id' => $profile->employee_id,
                'joining_date' => $profile->joining_date?->format('Y-m-d'),
                'effective_from' => $profile->effective_from?->format('Y-m-d'),
                'date_of_birth' => $profile->date_of_birth?->format('Y-m-d'),
                'position_id' => $profile->position_id,
                'profile_image_path' => $profile->profile_image_path ?? null,
            ]);
        }
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Personal Information')
                    ->schema([
                        Forms\Components\TextInput::make('first_name')
                            ->label('First Name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('last_name')
                            ->label('Last Name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('employee_id')
                            ->label('Employee ID')
                            ->required()
                            ->unique(EmployeeProfile::class, 'employee_id', ignoreRecord: true)
                            ->maxLength(255),
                        Forms\Components\DatePicker::make('date_of_birth')
                            ->label('Date of Birth')
                            ->native(false),
                    ])->columns(2),

                Forms\Components\Section::make('Employment Information')
                    ->schema([
                        Forms\Components\Select::make('position_id')
                            ->label('Position')
                            ->options(\App\Models\Position::all()->pluck('name', 'id'))
                            ->searchable()
                            ->preload(),
                        Forms\Components\DatePicker::make('joining_date')
                            ->label('Joining Date')
                            ->required()
                            ->native(false),
                        Forms\Components\DatePicker::make('effective_from')
                            ->label('Effective From')
                            ->required()
                            ->native(false),
                    ])->columns(2),

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
            [
                'branch_id' => Auth::user()->branch_id,
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'employee_id' => $data['employee_id'],
                'joining_date' => $data['joining_date'],
                'effective_from' => $data['effective_from'],
            ]
        );

        $image = $data['profile_image_path'] ?? null;
        if (is_array($image)) {
            $image = $image['path'] ?? ($image[0]['path'] ?? null);
        }

        $profile->update([
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'employee_id' => $data['employee_id'],
            'joining_date' => $data['joining_date'],
            'effective_from' => $data['effective_from'],
            'date_of_birth' => $data['date_of_birth'],
            'position_id' => $data['position_id'],
            'profile_image_path' => $image ?? $profile->profile_image_path,
        ]);

        Notification::make()
            ->title('Profile updated')
            ->success()
            ->send();
    }
}

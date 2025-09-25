<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OnboardingInviteResource\Pages;
use App\Models\OnboardingInvite;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class OnboardingInviteResource extends Resource
{
    protected static ?string $model = OnboardingInvite::class;

    protected static ?string $navigationIcon = 'heroicon-o-envelope-open';
    
    protected static ?string $navigationGroup = 'Employee Onboarding';
    
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Employee Information')
                    ->schema([
                        Forms\Components\Select::make('branch_id')
                            ->relationship('branch', 'name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set) {
                                // Clear position selection when branch changes
                                $set('position_id', null);
                            }),
                        Forms\Components\Select::make('position_id')
                            ->options(function (callable $get) {
                                $branchId = $get('branch_id');
                                if (!$branchId) {
                                    return [];
                                }
                                
                                return \App\Models\Position::where('branch_id', $branchId)
                                    ->where('is_active', true)
                                    ->get()
                                    ->mapWithKeys(function ($position) {
                                        return [$position->id => $position->name . ' (' . $position->grade . ')'];
                                    });
                            })
                            ->required()
                            ->searchable()
                            ->preload()
                            ->disabled(fn (callable $get) => !$get('branch_id')),
                        Forms\Components\TextInput::make('first_name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('last_name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('email')
                            ->email()
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('phone')
                            ->tel()
                            ->required()
                            ->maxLength(255),
                    ])
                    ->columns(2),
                
                Forms\Components\Section::make('Invitation Details')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->options([
                                'pending' => 'Pending',
                                'sent' => 'Sent',
                                'completed' => 'Completed',
                                'expired' => 'Expired',
                            ])
                            ->required()
                            ->default('pending'),
                        Forms\Components\DateTimePicker::make('expires_at')
                            ->required()
                            ->default(now()->addDays(7)),
                        Forms\Components\KeyValue::make('invitation_data')
                            ->helperText('Additional invitation details as key-value pairs'),
                    ])
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('full_name')
                    ->label('Name')
                    ->searchable(['first_name', 'last_name'])
                    ->sortable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('phone')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('branch.name')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('info'),
                Tables\Columns\TextColumn::make('position.name')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('success'),
                Tables\Columns\TextColumn::make('status')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'gray',
                        'sent' => 'warning',
                        'completed' => 'success',
                        'expired' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('kyc_status')
                    ->label('KYC Status')
                    ->getStateUsing(function ($record) {
                        $kyc = $record->kycVerifications()->latest()->first();
                        return $kyc ? $kyc->status : 'Not Started';
                    })
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'pending_hr_review' => 'info',
                        'approved' => 'success',
                        'rejected' => 'danger',
                        'failed' => 'danger',
                        'Not Started' => 'gray',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('expires_at')
                    ->dateTime()
                    ->sortable()
                    ->color(fn ($record) => $record->isExpired() ? 'danger' : 'success'),
                Tables\Columns\TextColumn::make('sent_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('completed_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('branch_id')
                    ->relationship('branch', 'name')
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('position_id')
                    ->relationship('position', 'name')
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'sent' => 'Sent',
                        'completed' => 'Completed',
                        'expired' => 'Expired',
                    ]),
                Tables\Filters\Filter::make('expired')
                    ->query(fn (Builder $query): Builder => $query->where('expires_at', '<', now()))
                    ->label('Expired Invitations'),
            ])
            ->actions([
                Tables\Actions\Action::make('send_invitation')
                    ->label('Send Invitation')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Send Onboarding Invitation')
                    ->modalDescription('Send invitation to employee for KYC completion?')
                    ->visible(fn ($record) => $record->status === 'pending')
                    ->action(function ($record) {
                        // Create KYC verification record for employee self-filling
                        $record->kycVerifications()->create([
                            'verification_id' => 'KYC-' . strtoupper(\Illuminate\Support\Str::random(12)),
                            'provider' => 'internal',
                            'status' => 'pending',
                            'type' => 'selfie_liveness',
                            'expires_at' => now()->addHours(24),
                        ]);
                        
                        // Send invitation email
                        $emailService = app(\App\Services\EmailService::class);
                        $emailService->sendOnboardingInvitation($record);
                        
                        $record->update([
                            'status' => 'sent',
                            'sent_at' => now(),
                        ]);
                        
                        \Filament\Notifications\Notification::make()
                            ->title('Invitation sent successfully')
                            ->body('Employee will receive KYC link via email.')
                            ->success()
                            ->send();
                    }),
                Tables\Actions\Action::make('fill_kyc')
                    ->label('Fill KYC (HR)')
                    ->icon('heroicon-o-user-plus')
                    ->color('warning')
                    ->modalHeading('Fill KYC Information for Employee')
                    ->modalDescription('Complete the KYC form on behalf of the employee. First name and last name are pre-filled from the invitation data.')
                    ->fillForm(function (OnboardingInvite $record): array {
                        return [
                            'first_name' => $record->first_name,
                            'last_name' => $record->last_name,
                        ];
                    })
                    ->form([
                        Forms\Components\Section::make('Employee Information')
                            ->schema([
                                Forms\Components\Placeholder::make('employee_info')
                                    ->label('Employee Details')
                                    ->content(function (OnboardingInvite $record) {
                                        return new \Illuminate\Support\HtmlString(
                                            '<div class="bg-gray-50 p-3 rounded-lg text-sm">' .
                                            '<p><strong>Name:</strong> ' . $record->full_name . '</p>' .
                                            '<p><strong>Email:</strong> ' . $record->email . '</p>' .
                                            '<p><strong>Phone:</strong> ' . $record->phone . '</p>' .
                                            '<p><strong>Position:</strong> ' . $record->position->name . '</p>' .
                                            '<p><strong>Branch:</strong> ' . $record->branch->name . '</p>' .
                                            '</div>'
                                        );
                                    }),
                            ]),
                        
                        Forms\Components\Section::make('Personal Information')
                            ->schema([
                                Forms\Components\TextInput::make('first_name')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('last_name')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\DatePicker::make('date_of_birth')
                                    ->required(),
                                Forms\Components\TextInput::make('national_id')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\Textarea::make('address')
                                    ->required()
                                    ->rows(3),
                            ])
                            ->columns(2),
                        
                        Forms\Components\Section::make('Emergency Contact')
                            ->schema([
                                Forms\Components\TextInput::make('emergency_contact_name')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('emergency_contact_phone')
                                    ->required()
                                    ->maxLength(20),
                            ])
                            ->columns(2),
                        
                        Forms\Components\Section::make('Identity Documents')
                            ->schema([
                                Forms\Components\FileUpload::make('profile_photo')
                                    ->label('Upload Profile Photo')
                                    ->image()
                                    ->disk('private')
                                    ->directory('kyc/profiles')
                                    ->visibility('private'),
                                Forms\Components\FileUpload::make('national_id_photo')
                                    ->label('Upload National ID')
                                    ->image()
                                    ->disk('private')
                                    ->directory('kyc/documents')
                                    ->visibility('private')
                                    ->helperText('Upload a clear image of the employee\'s national ID (front side).'),
                            ])
                            ->columns(2),
                    ])
                    ->action(function (OnboardingInvite $record, array $data) {
                        $extractFilePath = function ($value) {
                            if (is_array($value)) {
                                return $value['path'] ?? ($value[0]['path'] ?? null);
                            }

                            return $value;
                        };

                        $profilePhotoPath = $extractFilePath($data['profile_photo'] ?? null);
                        $nationalIdPhotoPath = $extractFilePath($data['national_id_photo'] ?? null);

                        // Create KYC verification record
                        $kycVerification = $record->kycVerifications()->create([
                            'verification_id' => 'KYC-' . strtoupper(\Illuminate\Support\Str::random(12)),
                            'provider' => 'hr_admin',
                            'status' => 'approved',
                            'type' => 'selfie_liveness',
                            'first_name' => $data['first_name'],
                            'last_name' => $data['last_name'],
                            'date_of_birth' => $data['date_of_birth'],
                            'national_id' => $data['national_id'],
                            'address' => $data['address'],
                            'emergency_contact_name' => $data['emergency_contact_name'],
                            'emergency_contact_phone' => $data['emergency_contact_phone'],
                            'profile_image_path' => $profilePhotoPath,
                            'document_image_path' => $nationalIdPhotoPath,
                            'verified_at' => now(),
                            'verification_data' => [
                                'filled_by' => Auth::user()?->name ?? 'HR Admin',
                                'filled_at' => now()->toISOString(),
                                'method' => 'hr_admin_form',
                            ],
                        ]);
                        
                        // Create employee profile
                        $user = \App\Models\User::updateOrCreate(
                            ['email' => $record->email],
                            [
                                'name' => $data['first_name'] . ' ' . $data['last_name'],
                                'phone' => $record->phone,
                                'branch_id' => $record->branch_id,
                                'role' => 'employee',
                                'status' => 'active',
                            ]
                        );

                        $generatedEmployeeId = sprintf('EMP-%06d', $user->id);

                        \App\Models\EmployeeProfile::updateOrCreate(
                            ['user_id' => $user->id],
                            [
                                'branch_id' => $record->branch_id,
                                'position_id' => $record->position_id,
                                'first_name' => $data['first_name'],
                                'last_name' => $data['last_name'],
                                'date_of_birth' => $data['date_of_birth'],
                                'employee_id' => $generatedEmployeeId,
                                'joining_date' => now()->toDateString(),
                                'effective_from' => now()->toDateString(),
                                'meta' => [
                                    'national_id' => $data['national_id'],
                                    'address' => $data['address'],
                                'emergency_contact_name' => $data['emergency_contact_name'],
                                'emergency_contact_phone' => $data['emergency_contact_phone'],
                                'kyc_verification_id' => $kycVerification->id,
                                'filled_by_hr' => true,
                                'national_id_photo_path' => $nationalIdPhotoPath,
                            ],
                            ]
                        );
                        
                        // Mark invite as completed
                        $record->update(['status' => 'completed']);
                        
                        \Filament\Notifications\Notification::make()
                            ->title('KYC filled successfully')
                            ->body('Employee profile created and onboarding completed.')
                            ->success()
                            ->send();
                    })
                    ->visible(fn ($record) => $record->status === 'pending' || $record->status === 'sent'),
                
                Tables\Actions\Action::make('view_progress')
                    ->label('View Progress')
                    ->icon('heroicon-o-eye')
                    ->color('info')
                    ->url(fn ($record) => route('filament.admin.resources.onboarding-invites.view', $record))
                    ->visible(fn ($record) => $record->status === 'sent'),
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOnboardingInvites::route('/'),
            'create' => Pages\CreateOnboardingInvite::route('/create'),
            'view' => Pages\ViewOnboardingInvite::route('/{record}'),
            'edit' => Pages\EditOnboardingInvite::route('/{record}/edit'),
        ];
    }
}

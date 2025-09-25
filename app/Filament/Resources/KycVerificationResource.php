<?php

namespace App\Filament\Resources;

use App\Filament\Resources\KycVerificationResource\Pages;
use App\Filament\Resources\KycVerificationResource\RelationManagers;
use App\Models\KycVerification;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class KycVerificationResource extends Resource
{
    protected static ?string $model = KycVerification::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-circle';
    
    protected static ?string $navigationGroup = 'Employee Onboarding';
    
    protected static ?int $navigationSort = 2;
    
    protected static ?string $navigationLabel = 'KYC Verifications';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Verification Information')
                    ->schema([
                        Forms\Components\TextInput::make('verification_id')
                            ->disabled()
                            ->dehydrated(false),
                        
                        Forms\Components\Select::make('status')
                            ->options([
                                'pending' => 'Pending',
                                'processing' => 'Processing',
                                'pending_hr_review' => 'Pending HR Review',
                                'approved' => 'Approved',
                                'rejected' => 'Rejected',
                                'failed' => 'Failed',
                            ])
                            ->required(),
                        
                        Forms\Components\TextInput::make('provider')
                            ->disabled()
                            ->dehydrated(false),
                        
                        Forms\Components\TextInput::make('type')
                            ->disabled()
                            ->dehydrated(false),
                    ])
                    ->columns(2),
                
                Forms\Components\Section::make('Employee Information')
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
                
                Forms\Components\Section::make('Images')
                    ->schema([
                        Forms\Components\FileUpload::make('profile_image_path')
                            ->label('Profile Image')
                            ->image()
                            ->directory('kyc/profiles')
                            ->visibility('private'),
                        Forms\Components\FileUpload::make('document_image_path')
                            ->label('National ID Image')
                            ->image()
                            ->directory('kyc/documents')
                            ->visibility('private')
                            ->helperText('Upload a clear photo of the candidate\'s national ID.'),

                    ])
                    ->columns(1),
                
                Forms\Components\Section::make('Review Notes')
                    ->schema([
                        Forms\Components\Textarea::make('rejection_reason')
                            ->label('Rejection Reason')
                            ->rows(3)
                            ->helperText('Required if status is rejected'),
                    ])
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('verification_id')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('gray'),
                
                Tables\Columns\TextColumn::make('full_name')
                    ->label('Employee Name')
                    ->searchable(['first_name', 'last_name'])
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('onboardingInvite.email')
                    ->label('Email')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('onboardingInvite.phone')
                    ->label('Phone')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'gray',
                        'processing' => 'blue',
                        'pending_hr_review' => 'warning',
                        'approved' => 'success',
                        'rejected' => 'danger',
                        'failed' => 'danger',
                    }),
                
                Tables\Columns\TextColumn::make('national_id')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                Tables\Columns\TextColumn::make('verified_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'processing' => 'Processing',
                        'pending_hr_review' => 'Pending HR Review',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                        'failed' => 'Failed',
                    ]),
                
                Tables\Filters\Filter::make('pending_hr_review')
                    ->query(fn (Builder $query): Builder => $query->where('status', 'pending_hr_review'))
                    ->label('Pending HR Review'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('approve')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Approve KYC Verification')
                    ->modalDescription('Are you sure you want to approve this KYC verification?')
                    ->action(function (KycVerification $record) {
                        $record->markAsApproved([
                            'approved_by' => Auth::user()?->name ?? 'HR Admin',
                            'approved_at' => now()->toISOString(),
                        ]);
                        
                        // Create employee profile from KYC data and send welcome email
                        static::createEmployeeProfile($record);
                    })
                    ->visible(fn (KycVerification $record): bool => $record->status === 'pending_hr_review'),
                
                Tables\Actions\Action::make('reject')
                    ->icon('heroicon-o-x-mark')
                    ->color('danger')
                    ->form([
                        Forms\Components\Textarea::make('rejection_reason')
                            ->label('Rejection Reason')
                            ->required()
                            ->rows(3),
                    ])
                    ->action(function (KycVerification $record, array $data) {
                        $record->markAsRejected($data['rejection_reason'], [
                            'rejected_by' => Auth::user()?->name ?? 'HR Admin',
                            'rejected_at' => now()->toISOString(),
                        ]);
                    })
                    ->visible(fn (KycVerification $record): bool => $record->status === 'pending_hr_review'),
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
            'index' => Pages\ListKycVerifications::route('/'),
            'create' => Pages\CreateKycVerification::route('/create'),
            'view' => Pages\ViewKycVerification::route('/{record}'),
            'edit' => Pages\EditKycVerification::route('/{record}/edit'),
        ];
    }
    
    /**
     * Create employee profile from approved KYC verification
     */
    private static function createEmployeeProfile(KycVerification $verification): void
    {
        $invite = $verification->onboardingInvite;
        
        // Generate a random temporary password
        $temporaryPassword = \Illuminate\Support\Str::random(12);
        
        // Create or update user account
        $user = \App\Models\User::updateOrCreate(
            ['email' => $invite->email],
            [
                'name' => $verification->full_name,
                'phone' => $invite->phone,
                'branch_id' => $invite->branch_id,
                'role' => 'employee',
                'status' => 'active',
                'password' => bcrypt($temporaryPassword),
                'must_change_password' => true,
            ]
        );
        
        // Create employee profile
        $joiningDate = now()->toDateString(); // Set joining date to today
        
        // Generate a deterministic, unique employee_id based on user id
        $generatedEmployeeId = sprintf('EMP-%06d', $user->id);
        
        \App\Models\EmployeeProfile::updateOrCreate(
            ['user_id' => $user->id],
            [
                'branch_id' => $invite->branch_id,
                'position_id' => $invite->position_id,
                'first_name' => $verification->first_name,
                'last_name' => $verification->last_name,
                'date_of_birth' => $verification->date_of_birth,
                'employee_id' => $generatedEmployeeId,
                'joining_date' => $joiningDate,
                'effective_from' => $joiningDate,
                'meta' => [
                    'national_id' => $verification->national_id,
                    'address' => $verification->address,
                    'emergency_contact_name' => $verification->emergency_contact_name,
                    'emergency_contact_phone' => $verification->emergency_contact_phone,
                    'kyc_verification_id' => $verification->id,
                ],
            ]
        );
        
        // Mark onboarding invite as completed
        $invite->update(['status' => 'completed']);
        
        // Send welcome email with temporary password
        $emailService = app(\App\Services\EmailService::class);
        $emailService->sendWelcomeEmailWithPassword($user, $temporaryPassword);
    }
}

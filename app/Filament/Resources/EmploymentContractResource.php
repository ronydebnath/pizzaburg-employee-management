<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EmploymentContractResource\Pages;
use App\Filament\Resources\EmploymentContractResource\RelationManagers;
use App\Models\EmploymentContract;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class EmploymentContractResource extends Resource
{
    protected static ?string $model = EmploymentContract::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    
    protected static ?string $navigationGroup = 'Employee Onboarding';
    
    protected static ?int $navigationSort = 3;
    
    protected static ?string $navigationLabel = 'Employment Contracts';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Contract Information')
                    ->schema([
                        Forms\Components\TextInput::make('contract_number')
                            ->label('Contract Number')
                            ->disabled()
                            ->dehydrated(false)
                            ->placeholder('Auto-generated'),
                        
                        Forms\Components\Select::make('onboarding_invite_id')
                            ->label('Employee')
                            ->relationship('onboardingInvite', 'email')
                            ->getOptionLabelFromRecordUsing(fn ($record) => $record->full_name . ' - ' . $record->email)
                            ->searchable(['first_name', 'last_name', 'email'])
                            ->preload()
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set) {
                                if ($state) {
                                    $invite = \App\Models\OnboardingInvite::find($state);
                                    if ($invite && $invite->position) {
                                        // Auto-select template based on position
                                        $template = \App\Models\ContractTemplate::where('key', $invite->position->contract_template_key)
                                            ->where('is_active', true)
                                            ->first();
                                        if ($template) {
                                            $set('template_key', $template->key);
                                        } else {
                                            // Fallback to default template if position template not found
                                            $defaultTemplate = \App\Models\ContractTemplate::where('key', 'standard_employment')
                                                ->where('is_active', true)
                                                ->first();
                                            if ($defaultTemplate) {
                                                $set('template_key', $defaultTemplate->key);
                                            }
                                        }
                                    }
                                } else {
                                    $set('template_key', '');
                                }
                            }),
                        
                        Forms\Components\TextInput::make('template_key')
                            ->label('Template Key')
                            ->disabled()
                            ->dehydrated(true)
                            ->default(''),
                        
                        Forms\Components\Placeholder::make('employee_info')
                            ->label('Employee Information')
                            ->content(function (callable $get) {
                                $inviteId = $get('onboarding_invite_id');
                                if (!$inviteId) {
                                    return 'Select an employee to view their information';
                                }
                                
                                $invite = \App\Models\OnboardingInvite::with(['branch', 'position'])->find($inviteId);
                                if (!$invite) {
                                    return 'Employee not found';
                                }
                                
                                return new \Illuminate\Support\HtmlString(
                                    '<div class="bg-gray-50 p-3 rounded-lg text-sm">' .
                                    '<p><strong>Name:</strong> ' . $invite->full_name . '</p>' .
                                    '<p><strong>Email:</strong> ' . $invite->email . '</p>' .
                                    '<p><strong>Phone:</strong> ' . $invite->phone . '</p>' .
                                    '<p><strong>Position:</strong> ' . ($invite->position->name ?? 'Not set') . '</p>' .
                                    '<p><strong>Branch:</strong> ' . ($invite->branch->name ?? 'Not set') . '</p>' .
                                    '<p><strong>Joining Date:</strong> ' . now()->format('M d, Y') . '</p>' .
                                    '</div>'
                                );
                            })
                            ->visible(fn (callable $get) => !empty($get('onboarding_invite_id'))),
                        
                        Forms\Cwomponents\Select::make('status')
                            ->options([
                                'draft' => 'Draft',
                                'sent' => 'Sent',
                                'signed' => 'Signed',
                                'completed' => 'Completed',
                            ])
                            ->required(),
                    ])
                    ->columns(2),
                
                Forms\Components\Section::make('Contract Template')
                    ->schema([
                        Forms\Components\Placeholder::make('template_content')
                            ->label('Template Preview')
                            ->content(function (callable $get) {
                                $templateKey = $get('template_key');
                                if (!$templateKey) {
                                    return 'Select an employee to view the contract template';
                                }
                                
                                $template = \App\Models\ContractTemplate::where('key', $templateKey)->first();
                                if (!$template) {
                                    return 'Template not found';
                                }
                                
                                return new \Illuminate\Support\HtmlString(
                                    '<div class="bg-gray-50 p-3 rounded-lg text-sm max-h-40 overflow-y-auto">' .
                                    '<h4 class="font-semibold mb-2">' . $template->name . '</h4>' .
                                    '<div class="text-gray-700">' . \Illuminate\Support\Str::limit(strip_tags($template->content), 300) . '</div>' .
                                    '</div>'
                                );
                            })
                            ->visible(fn (callable $get) => !empty($get('template_key'))),
                    ])
                    ->collapsible(),
                
                Forms\Components\Section::make('Contract Data')
                    ->schema([
                        Forms\Components\KeyValue::make('contract_data')
                            ->label('Contract Variables')
                            ->keyLabel('Variable')
                            ->valueLabel('Value')
                            ->addActionLabel('Add Variable')
                            ->default(function (callable $get) {
                                $inviteId = $get('onboarding_invite_id');
                                if (!$inviteId) {
                                    return [];
                                }
                                
                                $invite = \App\Models\OnboardingInvite::with(['branch', 'position'])->find($inviteId);
                                if (!$invite) {
                                    return [];
                                }
                                
                                // Auto-populate with employee data
                                return [
                                    'employee_name' => $invite->full_name,
                                    'employee_email' => $invite->email,
                                    'employee_phone' => $invite->phone,
                                    'branch_name' => $invite->branch->name ?? 'Not set',
                                    'branch_address' => $invite->branch->address ?? 'N/A',
                                    'position_name' => $invite->position->name ?? 'Not set',
                                    'position_grade' => $invite->position->grade ?? 'N/A',
                                    'start_date' => now()->format('M d, Y'),
                                    'generated_date' => now()->format('M d, Y'),
                                    'salary' => $invite->position->salary ?? 'As per company policy',
                                ];
                            }),
                    ])
                    ->collapsible(),
                
                Forms\Components\Section::make('Files')
                    ->schema([
                        Forms\Components\FileUpload::make('signature_file_path')
                            ->label('Signature File')
                            ->image()
                            ->directory('signatures')
                            ->visibility('private'),
                        
                        Forms\Components\FileUpload::make('signed_pdf_path')
                            ->label('Signed PDF')
                            ->acceptedFileTypes(['application/pdf'])
                            ->directory('contracts/signed')
                            ->visibility('private'),
                    ])
                    ->columns(2),
                
                Forms\Components\Section::make('Timestamps')
                    ->schema([
                        Forms\Components\DateTimePicker::make('sent_at')
                            ->disabled()
                            ->dehydrated(false),
                        
                        Forms\Components\DateTimePicker::make('signed_at')
                            ->disabled()
                            ->dehydrated(false),
                        
                        Forms\Components\DateTimePicker::make('completed_at')
                            ->disabled()
                            ->dehydrated(false),
                    ])
                    ->columns(3)
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('contract_number')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('gray'),
                
                Tables\Columns\TextColumn::make('onboardingInvite.full_name')
                    ->label('Employee Name')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('onboardingInvite.email')
                    ->label('Email')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('onboardingInvite.position.name')
                    ->label('Position')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'gray',
                        'sent' => 'blue',
                        'signed' => 'warning',
                        'completed' => 'success',
                    }),
                
                Tables\Columns\TextColumn::make('template_key')
                    ->badge()
                    ->color('info'),
                
                Tables\Columns\TextColumn::make('sent_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                Tables\Columns\TextColumn::make('signed_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'sent' => 'Sent',
                        'signed' => 'Signed',
                        'completed' => 'Completed',
                    ]),
                
                Tables\Filters\Filter::make('draft')
                    ->query(fn (Builder $query): Builder => $query->where('status', 'draft'))
                    ->label('Draft Contracts'),
                
                Tables\Filters\Filter::make('sent')
                    ->query(fn (Builder $query): Builder => $query->where('status', 'sent'))
                    ->label('Sent Contracts'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('generate_contract')
                    ->icon('heroicon-o-document')
                    ->color('info')
                    ->label(fn (EmploymentContract $record): string => 
                        $record->contract_file_path ? 'View Contract' : 'Generate Contract'
                    )
                    ->requiresConfirmation(fn (EmploymentContract $record): bool => !$record->contract_file_path)
                    ->modalHeading(fn (EmploymentContract $record): string => 
                        $record->contract_file_path ? 'View Contract' : 'Generate Contract'
                    )
                    ->modalDescription(fn (EmploymentContract $record): string => 
                        $record->contract_file_path 
                            ? 'View the generated contract PDF' 
                            : 'Generate contract PDF from template and send to employee?'
                    )
                    ->action(function (EmploymentContract $record) {
                        if (!$record->contract_file_path) {
                            $contractService = app(\App\Services\ContractGenerationService::class);
                            $contractService->generateContract($record);
                            $record->markAsSent();
                        }
                    })
                    ->url(fn (EmploymentContract $record): ?string => 
                        $record->contract_file_path ? route('contract.download', $record->onboardingInvite->token) : null
                    )
                    ->openUrlInNewTab(fn (EmploymentContract $record): bool => (bool) $record->contract_file_path)
                    ->visible(fn (EmploymentContract $record): bool => 
                        $record->status === 'draft' || $record->contract_file_path
                    ),
                
                Tables\Actions\Action::make('send_contract')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('success')
                    ->label('Send Contract')
                    ->requiresConfirmation()
                    ->modalHeading('Send Contract')
                    ->modalDescription('Send contract to employee for signing?')
                    ->action(function (EmploymentContract $record) {
                        try {
                            // Send contract notification email first
                            $emailService = app(\App\Services\EmailService::class);
                            $emailSent = $emailService->sendContractSentNotification($record);
                            
                            if ($emailSent) {
                                // Mark as sent only if email was sent successfully
                                $record->markAsSent();
                                
                                \Filament\Notifications\Notification::make()
                                    ->title('Contract Sent Successfully')
                                    ->body('The contract has been sent to ' . $record->onboardingInvite->email)
                                    ->success()
                                    ->send();
                            } else {
                                \Filament\Notifications\Notification::make()
                                    ->title('Failed to Send Contract')
                                    ->body('There was an error sending the contract email. Please try again.')
                                    ->danger()
                                    ->send();
                            }
                        } catch (\Exception $e) {
                            \Filament\Notifications\Notification::make()
                                ->title('Error')
                                ->body('An error occurred: ' . $e->getMessage())
                                ->danger()
                                ->send();
                        }
                    })
                    ->visible(fn (EmploymentContract $record): bool => 
                        $record->contract_file_path && in_array($record->status, ['draft', 'sent'])
                    ),
                
                Tables\Actions\Action::make('download_contract')
                    ->icon('heroicon-o-arrow-down')
                    ->color('gray')
                    ->url(fn (EmploymentContract $record): string => route('contract.download', $record->onboardingInvite->token))
                    ->openUrlInNewTab()
                    ->visible(fn (EmploymentContract $record): bool => $record->status === 'completed'),
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
            'index' => Pages\ListEmploymentContracts::route('/'),
            'create' => Pages\CreateEmploymentContract::route('/create'),
            'edit' => Pages\EditEmploymentContract::route('/{record}/edit'),
        ];
    }
}

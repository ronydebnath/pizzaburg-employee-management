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
    
    protected static ?string $navigationGroup = 'Contract Management';
    
    protected static ?int $navigationSort = 2;
    
    protected static ?string $navigationLabel = 'Employment Contracts';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Contract Information')
                    ->schema([
                        Forms\Components\TextInput::make('contract_number')
                            ->disabled()
                            ->dehydrated(false),
                        
                        Forms\Components\Select::make('onboarding_invite_id')
                            ->relationship('onboardingInvite', 'email')
                            ->searchable()
                            ->preload()
                            ->required(),
                        
                        Forms\Components\TextInput::make('template_key')
                            ->disabled()
                            ->dehydrated(false),
                        
                        Forms\Components\Select::make('status')
                            ->options([
                                'draft' => 'Draft',
                                'sent' => 'Sent',
                                'signed' => 'Signed',
                                'completed' => 'Completed',
                            ])
                            ->required(),
                    ])
                    ->columns(2),
                
                Forms\Components\Section::make('Contract Data')
                    ->schema([
                        Forms\Components\KeyValue::make('contract_data')
                            ->label('Contract Variables')
                            ->keyLabel('Variable')
                            ->valueLabel('Value')
                            ->addActionLabel('Add Variable'),
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
                    ->requiresConfirmation()
                    ->modalHeading('Generate Contract')
                    ->modalDescription('Generate contract PDF from template and send to employee?')
                    ->action(function (EmploymentContract $record) {
                        $contractService = app(\App\Services\ContractGenerationService::class);
                        $contractService->generateContract($record);
                        $record->markAsSent();
                    })
                    ->visible(fn (EmploymentContract $record): bool => $record->status === 'draft'),
                
                Tables\Actions\Action::make('send_contract')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Send Contract')
                    ->modalDescription('Send contract to employee for signing?')
                    ->action(function (EmploymentContract $record) {
                        $record->markAsSent();
                        
                        // Send contract notification email
                        $emailService = app(\App\Services\EmailService::class);
                        $emailService->sendContractSentNotification($record);
                    })
                    ->visible(fn (EmploymentContract $record): bool => $record->status === 'draft'),
                
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

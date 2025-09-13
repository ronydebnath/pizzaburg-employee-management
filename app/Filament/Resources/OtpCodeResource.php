<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OtpCodeResource\Pages;
use App\Models\OtpCode;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class OtpCodeResource extends Resource
{
    protected static ?string $model = OtpCode::class;

    protected static ?string $navigationIcon = 'heroicon-o-shield-check';
    
    protected static ?string $navigationGroup = 'Security';
    
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('OTP Information')
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload(),
                        Forms\Components\TextInput::make('phone')
                            ->tel()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('email')
                            ->email()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('code')
                            ->required()
                            ->maxLength(6)
                            ->disabled(),
                        Forms\Components\Select::make('type')
                            ->options([
                                'login' => 'Login',
                                'verification' => 'Verification',
                                'recovery' => 'Recovery',
                            ])
                            ->required(),
                        Forms\Components\Select::make('purpose')
                            ->options([
                                'onboarding' => 'Onboarding',
                                'login' => 'Login',
                                'password_reset' => 'Password Reset',
                            ])
                            ->required(),
                    ])
                    ->columns(2),
                
                Forms\Components\Section::make('Status & Security')
                    ->schema([
                        Forms\Components\DateTimePicker::make('expires_at')
                            ->required(),
                        Forms\Components\DateTimePicker::make('used_at')
                            ->disabled(),
                        Forms\Components\TextInput::make('attempts')
                            ->numeric()
                            ->disabled(),
                        Forms\Components\TextInput::make('max_attempts')
                            ->numeric()
                            ->required(),
                        Forms\Components\TextInput::make('ip_address')
                            ->disabled(),
                        Forms\Components\Textarea::make('user_agent')
                            ->disabled()
                            ->rows(2),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('phone')
                    ->searchable()
                    ->sortable()
                    ->formatStateUsing(fn ($state) => $state ? substr($state, 0, 2) . str_repeat('*', strlen($state) - 4) . substr($state, -2) : ''),
                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->sortable()
                    ->formatStateUsing(function ($state) {
                        if (!$state) return '';
                        $parts = explode('@', $state);
                        if (count($parts) === 2) {
                            $username = $parts[0];
                            $domain = $parts[1];
                            $maskedUsername = substr($username, 0, 1) . str_repeat('*', strlen($username) - 2) . substr($username, -1);
                            return $maskedUsername . '@' . $domain;
                        }
                        return $state;
                    }),
                Tables\Columns\TextColumn::make('code')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('primary'),
                Tables\Columns\TextColumn::make('type')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'login' => 'success',
                        'verification' => 'info',
                        'recovery' => 'warning',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('purpose')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('secondary'),
                Tables\Columns\IconColumn::make('is_expired')
                    ->label('Expired')
                    ->boolean()
                    ->getStateUsing(fn ($record) => $record->isExpired())
                    ->color(fn ($state) => $state ? 'danger' : 'success'),
                Tables\Columns\IconColumn::make('is_used')
                    ->label('Used')
                    ->boolean()
                    ->getStateUsing(fn ($record) => $record->isUsed())
                    ->color(fn ($state) => $state ? 'success' : 'gray'),
                Tables\Columns\TextColumn::make('attempts')
                    ->sortable()
                    ->badge()
                    ->color(fn ($state, $record) => $state >= $record->max_attempts ? 'danger' : 'warning'),
                Tables\Columns\TextColumn::make('expires_at')
                    ->dateTime()
                    ->sortable()
                    ->color(fn ($record) => $record->isExpired() ? 'danger' : 'success'),
                Tables\Columns\TextColumn::make('used_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'login' => 'Login',
                        'verification' => 'Verification',
                        'recovery' => 'Recovery',
                    ]),
                Tables\Filters\SelectFilter::make('purpose')
                    ->options([
                        'onboarding' => 'Onboarding',
                        'login' => 'Login',
                        'password_reset' => 'Password Reset',
                    ]),
                Tables\Filters\TernaryFilter::make('is_expired')
                    ->label('Expired')
                    ->boolean()
                    ->trueLabel('Expired OTPs only')
                    ->falseLabel('Active OTPs only')
                    ->native(false),
                Tables\Filters\TernaryFilter::make('is_used')
                    ->label('Used')
                    ->boolean()
                    ->trueLabel('Used OTPs only')
                    ->falseLabel('Unused OTPs only')
                    ->native(false),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\Action::make('invalidate')
                    ->label('Invalidate')
                    ->icon('heroicon-o-x-mark')
                    ->color('danger')
                    ->visible(fn ($record) => !$record->isUsed() && !$record->isExpired())
                    ->action(function ($record) {
                        $record->update(['expires_at' => now()->subMinute()]);
                    })
                    ->requiresConfirmation(),
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
            'index' => Pages\ListOtpCodes::route('/'),
            'view' => Pages\ViewOtpCode::route('/{record}'),
        ];
    }
}

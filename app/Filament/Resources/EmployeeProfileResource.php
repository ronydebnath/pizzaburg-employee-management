<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EmployeeProfileResource\Pages;
use App\Filament\Resources\EmployeeProfileResource\RelationManagers;
use App\Models\EmployeeProfile;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class EmployeeProfileResource extends Resource
{
    protected static ?string $model = EmployeeProfile::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';
    
    protected static ?string $navigationGroup = 'Employees';
    
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
                            ->preload(),
                        Forms\Components\Select::make('user_id')
                            ->relationship('user', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),
                        Forms\Components\TextInput::make('employee_id')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255)
                            ->helperText('Unique employee ID'),
                        Forms\Components\TextInput::make('first_name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('last_name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\DatePicker::make('date_of_birth')
                            ->displayFormat('d/m/Y'),
                    ])
                    ->columns(2),
                
                Forms\Components\Section::make('Employment Details')
                    ->schema([
                        Forms\Components\Select::make('position_id')
                            ->relationship('position', 'name')
                            ->searchable()
                            ->preload()
                            ->helperText('Current position'),
                        Forms\Components\DatePicker::make('joining_date')
                            ->required()
                            ->displayFormat('d/m/Y'),
                        Forms\Components\DatePicker::make('effective_from')
                            ->required()
                            ->displayFormat('d/m/Y')
                            ->helperText('When this profile becomes effective'),
                        Forms\Components\DatePicker::make('effective_to')
                            ->displayFormat('d/m/Y')
                            ->helperText('When this profile expires (leave blank for indefinite)'),
                    ])
                    ->columns(2),
                
                Forms\Components\Section::make('Additional Information')
                    ->schema([
                        Forms\Components\KeyValue::make('meta')
                            ->helperText('Additional metadata as key-value pairs'),
                        Forms\Components\Textarea::make('pii_encrypted')
                            ->label('Encrypted PII Data')
                            ->helperText('Sensitive personal information (encrypted)')
                            ->rows(3),
                    ])
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('employee_id')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('primary'),
                Tables\Columns\TextColumn::make('full_name')
                    ->label('Name')
                    ->searchable(['first_name', 'last_name'])
                    ->sortable(),
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
                Tables\Columns\TextColumn::make('position.grade')
                    ->label('Grade')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('warning'),
                Tables\Columns\TextColumn::make('user.email')
                    ->label('Email')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('joining_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('effective_from')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('effective_to')
                    ->date()
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
                Tables\Filters\Filter::make('active')
                    ->query(fn (Builder $query): Builder => $query->where(function ($q) {
                        $q->whereNull('effective_to')
                          ->orWhere('effective_to', '>', now());
                    }))
                    ->label('Active Employees Only'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('employee_id');
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
            'index' => Pages\ListEmployeeProfiles::route('/'),
            'create' => Pages\CreateEmployeeProfile::route('/create'),
            'edit' => Pages\EditEmployeeProfile::route('/{record}/edit'),
        ];
    }
}

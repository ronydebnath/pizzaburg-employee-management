<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ContractTemplateResource\Pages;
use App\Filament\Resources\ContractTemplateResource\RelationManagers;
use App\Models\ContractTemplate;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ContractTemplateResource extends Resource
{
    protected static ?string $model = ContractTemplate::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    
    protected static ?string $navigationGroup = 'Contract Management';
    
    protected static ?int $navigationSort = 1;
    
    protected static ?string $navigationLabel = 'Contract Templates';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Template Information')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('e.g., Standard Employment Contract'),
                        
                        Forms\Components\TextInput::make('key')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255)
                            ->placeholder('e.g., standard_employment')
                            ->helperText('Unique key for referencing this template'),
                        
                        Forms\Components\Textarea::make('description')
                            ->maxLength(1000)
                            ->placeholder('Brief description of this contract template'),
                        
                        Forms\Components\TextInput::make('version')
                            ->default('1.0')
                            ->maxLength(50)
                            ->placeholder('e.g., 1.0, 2.1'),
                    ])
                    ->columns(2),
                
                Forms\Components\Section::make('Template Content')
                    ->schema([
                        Forms\Components\RichEditor::make('content')
                            ->required()
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'strike',
                                'link',
                                'bulletList',
                                'orderedList',
                                'h2',
                                'h3',
                                'blockquote',
                                'codeBlock',
                            ])
                            ->placeholder('Write your contract template here...')
                            ->helperText('Use variables like {{employee_name}}, {{branch_name}}, etc. See available variables below.'),
                        
                        Forms\Components\Placeholder::make('available_variables')
                            ->label('Available Variables')
                            ->content(function () {
                                $variables = (new ContractTemplate())->getAvailableVariables();
                                $html = '<div class="grid grid-cols-2 gap-2 text-sm">';
                                foreach ($variables as $key => $description) {
                                    $html .= "<div><code class='bg-sky-100 text-black-800 px-1 rounded'>{{$key}}</code> - {$description}</div>";

                                }
                                $html .= '</div>';
                                return new \Illuminate\Support\HtmlString($html);
                            }),
                    ]),
                
                Forms\Components\Section::make('Settings')
                    ->schema([
                        Forms\Components\Toggle::make('is_active')
                            ->default(true)
                            ->label('Active')
                            ->helperText('Only active templates can be used for new contracts'),
                    ])
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('key')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('gray'),
                
                Tables\Columns\TextColumn::make('version')
                    ->badge()
                    ->color('blue'),
                
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->label('Active'),
                
                Tables\Columns\TextColumn::make('positions_count')
                    ->counts('positions')
                    ->label('Positions')
                    ->badge()
                    ->color('green'),
                
                Tables\Columns\TextColumn::make('employment_contracts_count')
                    ->counts('employmentContracts')
                    ->label('Contracts')
                    ->badge()
                    ->color('purple'),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active Status'),
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
            'index' => Pages\ListContractTemplates::route('/'),
            'create' => Pages\CreateContractTemplate::route('/create'),
            'edit' => Pages\EditContractTemplate::route('/{record}/edit'),
        ];
    }
}

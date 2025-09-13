<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SmsSettingResource\Pages;
use App\Models\SmsSetting;
use App\Services\SmsService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;

class SmsSettingResource extends Resource
{
    protected static ?string $model = SmsSetting::class;

    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-right';
    
    protected static ?string $navigationGroup = 'Settings';
    
    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'SMS Gateways';

    protected static ?string $modelLabel = 'SMS Gateway';

    protected static ?string $pluralModelLabel = 'SMS Gateways';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Provider Information')
                    ->schema([
                        Forms\Components\TextInput::make('provider_name')
                            ->required()
                            ->maxLength(255)
                            ->label('Provider Name')
                            ->placeholder('e.g., SSL Wireless, MimSMS, Alpha SMS'),
                        Forms\Components\Select::make('provider_class')
                            ->required()
                            ->label('Provider Class')
                            ->options([
                                'Xenon\LaravelBDSms\Provider\Ssl' => 'SSL Wireless',
                                'Xenon\LaravelBDSms\Provider\MimSms' => 'MimSMS',
                                'Xenon\LaravelBDSms\Provider\Alpha' => 'Alpha SMS',
                                'Xenon\LaravelBDSms\Provider\Banglalink' => 'Banglalink',
                                'Xenon\LaravelBDSms\Provider\BoomCast' => 'BoomCast',
                                'Xenon\LaravelBDSms\Provider\BulkSmsBD' => 'BulkSMS BD',
                                'Xenon\LaravelBDSms\Provider\DianaHost' => 'Diana Host',
                                'Xenon\LaravelBDSms\Provider\DianaSms' => 'Diana SMS',
                                'Xenon\LaravelBDSms\Provider\Esms' => 'ESMS',
                                'Xenon\LaravelBDSms\Provider\Grameenphone' => 'Grameenphone',
                                'Xenon\LaravelBDSms\Provider\Infobip' => 'Infobip',
                                'Xenon\LaravelBDSms\Provider\Lpeek' => 'Lpeek',
                                'Xenon\LaravelBDSms\Provider\MDL' => 'MDL',
                                'Xenon\LaravelBDSms\Provider\Metronet' => 'Metronet',
                                'Xenon\LaravelBDSms\Provider\Mobireach' => 'Mobireach',
                                'Xenon\LaravelBDSms\Provider\Muthofun' => 'Muthofun',
                                'Xenon\LaravelBDSms\Provider\NovocomBd' => 'Novocom BD',
                                'Xenon\LaravelBDSms\Provider\Onnorokom' => 'Onnorokom SMS',
                                'Xenon\LaravelBDSms\Provider\QuickSms' => 'Quick SMS',
                                'Xenon\LaravelBDSms\Provider\SendMySms' => 'SendMySMS',
                                'Xenon\LaravelBDSms\Provider\SmartLabSms' => 'SmartLab SMS',
                                'Xenon\LaravelBDSms\Provider\Sms4BD' => 'SMS4BD',
                                'Xenon\LaravelBDSms\Provider\SmsBangladesh' => 'SMS Bangladesh',
                                'Xenon\LaravelBDSms\Provider\SmsinBD' => 'SMSinBD',
                                'Xenon\LaravelBDSms\Provider\SmsNet24' => 'SMS Net24',
                                'Xenon\LaravelBDSms\Provider\SmsNetBD' => 'SMS Net BD',
                                'Xenon\LaravelBDSms\Provider\SmsQ' => 'SMS Q',
                                'Xenon\LaravelBDSms\Provider\SongBird' => 'SongBird',
                                'Xenon\LaravelBDSms\Provider\Tense' => 'Tense',
                                'Xenon\LaravelBDSms\Provider\TruboSms' => 'TruboSMS',
                                'Xenon\LaravelBDSms\Provider\Twenty4BulkSms' => '24 Bulk SMS',
                                'Xenon\LaravelBDSms\Provider\TwentyFourBulkSmsBD' => '24 Bulk SMS BD',
                                'Xenon\LaravelBDSms\Provider\Viatech' => 'Viatech',
                                'Xenon\LaravelBDSms\Provider\WinText' => 'WinText',
                                'Xenon\LaravelBDSms\Provider\ZamanIt' => 'Zaman IT',
                                'Xenon\LaravelBDSms\Provider\CustomGateway' => 'Custom Gateway',
                            ])
                            ->searchable()
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set) {
                                // Clear credentials when provider changes
                                $set('credentials', []);
                            }),
                        Forms\Components\Textarea::make('description')
                            ->label('Description')
                            ->placeholder('Brief description of this SMS provider')
                            ->rows(2),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Status')
                    ->schema([
                        Forms\Components\Toggle::make('is_active')
                            ->label('Active')
                            ->default(true)
                            ->helperText('Enable this provider for sending SMS'),
                        Forms\Components\Toggle::make('is_default')
                            ->label('Default Provider')
                            ->helperText('Set as the default SMS provider (only one can be default)')
                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                if ($state && !$get('is_active')) {
                                    $set('is_active', true);
                                }
                            }),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Provider Credentials')
                    ->schema([
                        Forms\Components\Repeater::make('credentials')
                            ->label('Credentials')
                            ->schema([
                                Forms\Components\TextInput::make('key')
                                    ->label('Key')
                                    ->required()
                                    ->placeholder('e.g., api_token, username, password'),
                                Forms\Components\TextInput::make('value')
                                    ->label('Value')
                                    ->required()
                                    ->password()
                                    ->placeholder('Enter the credential value'),
                            ])
                            ->defaultItems(1)
                            ->addActionLabel('Add Credential')
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string => $state['key'] ?? null)
                            ->helperText('Add the required credentials for your selected SMS provider. Check the provider documentation for required fields.'),
                    ])
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('provider_name')
                    ->searchable()
                    ->sortable()
                    ->label('Provider Name'),
                Tables\Columns\TextColumn::make('provider_class')
                    ->searchable()
                    ->sortable()
                    ->label('Provider Class')
                    ->formatStateUsing(fn (string $state): string => class_basename($state)),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->label('Active')
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_default')
                    ->boolean()
                    ->label('Default')
                    ->sortable(),
                Tables\Columns\TextColumn::make('credentials')
                    ->label('Credentials Count')
                    ->formatStateUsing(function ($state): string {
                        if (is_string($state)) {
                            $state = json_decode($state, true) ?? [];
                        }
                        return count($state ?? []) . ' configured';
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active Status'),
                Tables\Filters\TernaryFilter::make('is_default')
                    ->label('Default Status'),
            ])
            ->actions([
                Tables\Actions\Action::make('test_provider')
                    ->label('Test')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('info')
                    ->form([
                        Forms\Components\TextInput::make('test_mobile')
                            ->label('Test Mobile Number')
                            ->placeholder('01700000000')
                            ->default('01700000000')
                            ->required()
                            ->tel(),
                    ])
                    ->action(function (SmsSetting $record, array $data) {
                        $smsService = app(SmsService::class);
                        $result = $smsService->testProvider($record->provider_name, $data['test_mobile']);
                        
                        if ($result['success']) {
                            Notification::make()
                                ->title('Test SMS sent successfully')
                                ->body("Test SMS sent to {$data['test_mobile']} using {$record->provider_name}")
                                ->success()
                                ->send();
                        } else {
                            Notification::make()
                                ->title('Test SMS failed')
                                ->body("Error: {$result['error']}")
                                ->danger()
                                ->send();
                        }
                    })
                    ->visible(function (SmsSetting $record): bool {
                        $credentials = $record->credentials;
                        if (is_string($credentials)) {
                            $credentials = json_decode($credentials, true) ?? [];
                        }
                        return $record->is_active && !empty($credentials);
                    }),
                Tables\Actions\Action::make('set_default')
                    ->label('Set as Default')
                    ->icon('heroicon-o-star')
                    ->color('warning')
                    ->action(function (SmsSetting $record) {
                        if (!$record->is_active) {
                            Notification::make()
                                ->title('Cannot set inactive provider as default')
                                ->body('Please activate the provider first.')
                                ->warning()
                                ->send();
                            return;
                        }
                        
                        $record->setAsDefault();
                        
                        Notification::make()
                            ->title('Default provider updated')
                            ->body("{$record->provider_name} is now the default SMS provider.")
                            ->success()
                            ->send();
                    })
                    ->visible(fn (SmsSetting $record): bool => !$record->is_default),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('is_default', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSmsSettings::route('/'),
            'create' => Pages\CreateSmsSetting::route('/create'),
            'edit' => Pages\EditSmsSetting::route('/{record}/edit'),
        ];
    }
}

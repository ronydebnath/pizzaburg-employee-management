<?php

namespace App\Filament\Resources\KycVerificationResource\Pages;

use App\Filament\Resources\KycVerificationResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists;
use Filament\Infolists\Infolist;

class ViewKycVerification extends ViewRecord
{
    protected static string $resource = KycVerificationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Verification Information')
                    ->schema([
                        Infolists\Components\TextEntry::make('verification_id')
                            ->label('Verification ID')
                            ->badge()
                            ->color('info'),
                        
                        Infolists\Components\TextEntry::make('status')
                            ->label('Status')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'pending' => 'warning',
                                'pending_hr_review' => 'info',
                                'approved' => 'success',
                                'rejected' => 'danger',
                                'failed' => 'danger',
                                default => 'gray',
                            }),
                        
                        Infolists\Components\TextEntry::make('type')
                            ->label('Type'),
                        
                        Infolists\Components\TextEntry::make('provider')
                            ->label('Provider'),
                        
                        Infolists\Components\TextEntry::make('created_at')
                            ->label('Created At')
                            ->dateTime(),
                        
                        Infolists\Components\TextEntry::make('verified_at')
                            ->label('Verified At')
                            ->dateTime()
                            ->placeholder('Not verified yet'),
                    ])
                    ->columns(2),
                
                Infolists\Components\Section::make('Employee Information')
                    ->schema([
                        Infolists\Components\TextEntry::make('full_name')
                            ->label('Full Name'),
                        
                        Infolists\Components\TextEntry::make('date_of_birth')
                            ->label('Date of Birth')
                            ->date(),
                        
                        Infolists\Components\TextEntry::make('national_id')
                            ->label('National ID'),
                        
                        Infolists\Components\TextEntry::make('address')
                            ->label('Address')
                            ->columnSpanFull(),
                        
                        Infolists\Components\TextEntry::make('emergency_contact_name')
                            ->label('Emergency Contact Name'),
                        
                        Infolists\Components\TextEntry::make('emergency_contact_phone')
                            ->label('Emergency Contact Phone'),
                    ])
                    ->columns(2),
                
                Infolists\Components\Section::make('Images')
                    ->schema([
                        Infolists\Components\ImageEntry::make('profile_image_path')
                            ->label('Profile Image')
                            ->disk('private')
                            ->height(200)
                            ->placeholder('No profile image uploaded')
                            ->extraAttributes(['class' => 'rounded-lg cursor-pointer'])
                            ->url(fn ($record) => $record->profile_image_path ? route('private.file', ['path' => $record->profile_image_path]) : null)
                            ->openUrlInNewTab(),
                        
                    ])
                    ->columns(1),
                
                Infolists\Components\Section::make('Verification Data')
                    ->schema([
                        Infolists\Components\KeyValueEntry::make('verification_data')
                            ->label('Verification Data')
                            ->placeholder('No additional data'),
                    ])
                    ->collapsible(),
                
                Infolists\Components\Section::make('Result Data')
                    ->schema([
                        Infolists\Components\KeyValueEntry::make('result_data')
                            ->label('Result Data')
                            ->placeholder('No result data'),
                    ])
                    ->collapsible(),
                
                Infolists\Components\Section::make('Rejection Information')
                    ->schema([
                        Infolists\Components\TextEntry::make('rejection_reason')
                            ->label('Rejection Reason')
                            ->placeholder('Not rejected'),
                    ])
                    ->visible(fn ($record) => $record->status === 'rejected')
                    ->collapsible(),
            ]);
    }
}

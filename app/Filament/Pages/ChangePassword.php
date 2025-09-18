<?php

namespace App\Filament\Pages;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class ChangePassword extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-key';

    protected static string $view = 'filament.pages.change-password';
    
    protected static ?string $title = 'Change Password';
    
    protected static ?string $navigationLabel = 'Change Password';
    
    protected static bool $shouldRegisterNavigation = false;

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Change Your Password')
                    ->description('You must change your password before accessing the portal.')
                    ->schema([
                        Forms\Components\TextInput::make('current_password')
                            ->label('Current Password')
                            ->password()
                            ->required()
                            ->rule('current_password'),
                        
                        Forms\Components\TextInput::make('password')
                            ->label('New Password')
                            ->password()
                            ->required()
                            ->minLength(8)
                            ->same('password_confirmation'),
                        
                        Forms\Components\TextInput::make('password_confirmation')
                            ->label('Confirm New Password')
                            ->password()
                            ->required()
                            ->minLength(8),
                    ])
                    ->columns(1),
            ])
            ->statePath('data');
    }

    protected function getActions(): array
    {
        return [
            Action::make('save')
                ->label('Change Password')
                ->submit('save')
                ->color('success'),
        ];
    }

    public function save(): void
    {
        $data = $this->form->getState();

        // Update the user's password
        Auth::user()->update([
            'password' => Hash::make($data['password']),
            'must_change_password' => false,
        ]);

        Notification::make()
            ->title('Password changed successfully!')
            ->success()
            ->send();

        // Redirect to dashboard
        $this->redirect(route('filament.portal.pages.dashboard'));
    }
}
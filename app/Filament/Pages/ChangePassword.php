<?php

namespace App\Filament\Pages;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class ChangePassword extends Page implements Forms\Contracts\HasForms
{
    use Forms\Concerns\InteractsWithForms;

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
                            ->autocomplete('current-password'),
                        
                        Forms\Components\TextInput::make('password')
                            ->label('New Password')
                            ->password()
                            ->required()
                            ->minLength(8)
                            ->autocomplete('new-password')
                            ->helperText('Password must be at least 8 characters.'),
                        
                        Forms\Components\TextInput::make('password_confirmation')
                            ->label('Confirm New Password')
                            ->password()
                            ->required()
                            ->same('password')
                            ->autocomplete('new-password')
                            ->dehydrated(false),
                    ])
                    ->columns(1),
            ])
            ->statePath('data');
    }

    protected function getActions(): array
    {
        return [];
    }

    protected function getFormActions(): array
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
        try {
            $data = $this->form->getState();

            // Log the attempt for debugging
            \Illuminate\Support\Facades\Log::info('Password change attempt', [
                'user_id' => Auth::id(),
                'has_current_password' => !empty($data['current_password']),
                'has_new_password' => !empty($data['password']),
            ]);

            // Verify current password
            if (!Hash::check($data['current_password'], Auth::user()->password)) {
                Notification::make()
                    ->title('Invalid current password')
                    ->body('Please enter your correct current password.')
                    ->danger()
                    ->send();
                return;
            }

            // Update the user's password
            $user = Auth::user();
            $user->update([
                'password' => Hash::make($data['password']),
                'must_change_password' => false,
            ]);

            \Illuminate\Support\Facades\Log::info('Password changed successfully', [
                'user_id' => $user->id,
            ]);

            Notification::make()
                ->title('Password changed successfully!')
                ->body('You can now access the employee portal.')
                ->success()
                ->send();

            // Redirect to dashboard
            $this->redirect(route('filament.portal.pages.dashboard'));
            
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Password change error', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            Notification::make()
                ->title('Error changing password')
                ->body('An error occurred while changing your password. Please try again.')
                ->danger()
                ->send();
        }
    }
}
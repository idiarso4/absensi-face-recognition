<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Facades\Hash;

class Profile extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string $resource = UserResource::class;

    protected static string $view = 'filament.resources.user-resource.pages.profile';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill(auth()->user()->toArray());
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->label('Nama Lengkap'),

                TextInput::make('email')
                    ->email()
                    ->required()
                    ->maxLength(255)
                    ->label('Email')
                    ->unique(ignoreRecord: true),

                FileUpload::make('image')
                    ->image()
                    ->directory('profiles')
                    ->label('Foto Profil'),

                TextInput::make('current_password')
                    ->password()
                    ->label('Password Saat Ini')
                    ->requiredWith('password'),

                TextInput::make('password')
                    ->password()
                    ->label('Password Baru')
                    ->minLength(8)
                    ->same('password_confirmation'),

                TextInput::make('password_confirmation')
                    ->password()
                    ->label('Konfirmasi Password Baru'),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        $user = auth()->user();

        // Verify current password if changing password
        if (!empty($data['password'])) {
            if (!Hash::check($data['current_password'], $user->password)) {
                Notification::make()
                    ->title('Password saat ini salah')
                    ->danger()
                    ->send();
                return;
            }
        }

        $user->update([
            'name' => $data['name'],
            'email' => $data['email'],
            'image' => $data['image'] ?? $user->image,
            'password' => !empty($data['password']) ? Hash::make($data['password']) : $user->password,
        ]);

        Notification::make()
            ->title('Profil berhasil diperbarui')
            ->success()
            ->send();
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('Simpan Perubahan')
                ->action('save'),
        ];
    }
}

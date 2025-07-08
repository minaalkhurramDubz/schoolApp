<?php

namespace App\Filament\Pages;

use App\Models\User;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;

class Login extends Page
{
    use InteractsWithForms;

    protected static string $view = 'filament.pages.login';

    public ?array $data = [];

    public function mount(): void
    {
        // Handle magic link login
        if (request()->hasValidSignature() && request()->has('email')) {
            $user = User::where('email', request('email'))->first();

            if ($user) {
                Auth::login($user);

                Notification::make()
                    ->success()
                    ->title('Login Successful')
                    ->send();

                $this->redirect(filament()->getUrl());
            } else {
                Notification::make()
                    ->danger()
                    ->title('User not found.')
                    ->send();
            }
        }
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('email')
                    ->label('Your Email')
                    ->email()
                    ->required(),
            ])
            ->statePath('data');
    }

    public function submit(): void
    {
        $email = $this->data['email'] ?? null;

        if (! $email) {
            Notification::make()
                ->danger()
                ->title('Please enter your email.')
                ->send();

            return;
        }

        // user being added to db
        $user = User::firstOrCreate(
            ['email' => $email],
            ['name' => $email, 'password' => bcrypt(str()->random(16))]

        );

        $url = URL::temporarySignedRoute(
            'filament.auth.pages.login',
            now()->addMinutes(15),
            ['email' => $user->email]
        );

        Mail::raw(
            "Click to login: {$url}",
            fn ($message) => $message
                ->to($user->email)
                ->subject('Your Magic Login Link')
        );

        Notification::make()
            ->success()
            ->title('Check your email for the login link!')
            ->send();
    }
}

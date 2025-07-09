<?php

namespace App\Filament\Pages;

use App\Models\User;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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

                // checking the schools and redirecting the url
                $this->sessionCheckAndRedirect($user);

                //  $this->redirect(filament()->getUrl());
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
            'filament.auth.auth.login',
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

    private function sessionCheckAndRedirect(User $user): void
    {
        // ✅ Check how many schools this user belongs to:
        $schools = DB::table('school_user')
            ->where('user_id', $user->id)
            ->pluck('school_id');

        if ($schools->count() === 1) {
            // Only one school → save it to session
            session(['active_school_id' => $schools->first()]);

            $this->redirect(filament()->getUrl());
        } elseif ($schools->count() > 1) {
            // More than one → redirect them to choose
            $this->redirect(route('filament.auth.pages.choose-school'));
        } else {
            // No schools at all
            Notification::make()
                ->danger()
                ->title('No schools assigned to your account.')
                ->send();

            Auth::logout();
            $this->redirect(route('filament.auth.auth.login'));
        }
    }
}

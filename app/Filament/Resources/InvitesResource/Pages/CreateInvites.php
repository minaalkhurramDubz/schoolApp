<?php

namespace App\Filament\Resources\InvitesResource\Pages;

use App\Filament\Resources\InvitesResource;
use App\Models\Invite;
use App\Models\User;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Str;

class CreateInvites extends CreateRecord
{
    protected static string $resource = InvitesResource::class;

    protected function handleRecordCreation(array $data): Invite
    {
        // Create user
        $user = User::firstOrCreate(
            ['email' => $data['email']],
            [
                'name' => $data['name'],
                'password' => bcrypt(Str::random(16)),
            ]
        );

        // Assign role
        $user->syncRoles([$data['role']]);

        // Attach to school
        $user->schools()->syncWithoutDetaching([
            $data['school_id'] => ['role' => $data['role']],
        ]);

        // Generate a unique token for the magic link
        $token = Str::uuid()->toString();

        $invite = Invite::create([
            ...$data,
            'user_id' => $user->id,
            'invited_by' => auth()->id(),
            'token' => $token,
            'expires_at' => now()->addMinutes(15),
        ]);

        // Generate signed URL for magic login
        $url = URL::temporarySignedRoute(
            'filament.auth.auth.login',
            now()->addMinutes(15),
            [
                'email' => $user->email,
                'invite_token' => $token,
            ]
        );

        Mail::raw(
            "Click to login: {$url}",
            fn ($message) => $message
                ->to($user->email)
                ->subject('Your Magic Login Link')
        );

        return $invite;
    }
}

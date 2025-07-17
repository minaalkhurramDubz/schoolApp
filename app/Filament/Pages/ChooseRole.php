<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;

class ChooseRole extends Page
{
    protected static ?string $navigationIcon = null;

    protected static string $view = 'filament.pages.choose-role';

    public $roles = [];

    public $schoolId;

    public function mount($school)
    {
        $this->schoolId = $school;

        $user = auth()->user();

        // Find all roles user has in this school
        $roles = DB::table('school_user')
            ->where('user_id', $user->id)
            ->where('school_id', $school)
            ->pluck('role')
            ->toArray();

        $this->roles = $roles;
    }


}

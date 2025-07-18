<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class ChooseSchool extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.choose-school';

    protected static ?string $slug = 'choose-school';

    public function mount(): void
    {
        $user = auth()->user();

        $this->schools = DB::table('schools')
            ->join('school_user', 'schools.id', '=', 'school_user.school_id')
            ->where('school_user.user_id', $user->id)
            ->select('schools.id', 'schools.name')
            ->get();
    }

    public function getViewData(): array
    {
        return [
            'schools' => $this->schools,
        ];
    }
public function setSchool($schoolId)
{
    session(['selected_school_id' => $schoolId]);
    Session::save(); // Force session save

 return redirect()->to('/admin/choose-role/' . $schoolId);

}

}

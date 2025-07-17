<?php

namespace App\Filament\Resources\SchoolResource\Pages;

use App\Filament\Resources\SchoolResource;
use App\Models\Course;
use App\Models\School;
use App\Models\SchoolClass;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Facades\DB;

class SchoolDashboards extends Page
{
    protected static string $resource = SchoolResource::class;

    protected static string $view = 'filament.resources.school-resource.pages.school-dashboards';

    public int $studentCount = 0;
    public int $teacherCount = 0;
    public int $courseCount = 0;
    public int $classCount = 0;
    public string $schoolPlan = 'N/A';

    public School $record;

    public function mount(): void
{
        $this->studentCount = DB::table('school_user')
            ->where('school_id', $this->record->id)
            ->where('role', 'student')
            ->count();

        $this->teacherCount = DB::table('school_user')
            ->where('school_id', $this->record->id)
            ->where('role', 'teacher')
            ->count();

        $this->courseCount = Course::where('school_id', $this->record->id)->count();
        $this->classCount = SchoolClass::where('school_id', $this->record->id)->count();
    }

    public function getTitle(): string
    {
        return "Dashboard - {$this->record->name}";
    }
}

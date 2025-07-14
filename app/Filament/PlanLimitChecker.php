<?php

namespace App\Filament;

use App\Models\School;
use Filament\Notifications\Notification;

class PlanLimitChecker
{
    public static function checkLimit(School $school, string $type)
    {
        $current = match ($type) {
            'teachers' => $school->users()->whereHas('roles', fn ($q) => $q->where('name', 'teacher'))->count(),
            'students' => $school->users()->whereHas('roles', fn ($q) => $q->where('name', 'student'))->count(),
            'classes' => $school->classes()->count(),
            'courses' => $school->courses()->count(),
        };

        $limit = match ($type) {
            'teachers' => $school->plan->max_teachers,
            'students' => $school->plan->max_students,
            'classes' => $school->plan->max_classes,
            'courses' => $school->plan->max_courses,
        };

        if ($current >= $limit) {
            Notification::make()
                ->title('Plan Limit Reached')
                ->body('You have reached the limit for this plan ')
                ->warning()
                ->send();

            throw \Illuminate\Validation\ValidationException::withMessages([
                'name' => "You have reached the maximum number of {$type} for your plan.",
            ]);
        }
    }
}

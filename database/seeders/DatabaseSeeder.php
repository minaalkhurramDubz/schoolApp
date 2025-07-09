<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Plan;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create Spatie roles
        foreach (['owner', 'admin', 'teacher', 'student'] as $role) {
            Role::firstOrCreate(['name' => $role]);
        }

        // Create Plans
        $basicPlan = Plan::firstOrCreate([
            'name' => 'Basic Plan',
        ], [
            'max_schools' => 1,
            'max_classes' => 100,
            'max_teachers' => 200,
            'max_students' => 1000,
            'max_courses' => 500,
        ]);

        $proPlan = Plan::firstOrCreate([
            'name' => 'Pro',
        ], [
            'max_schools' => 2,
            'max_classes' => 100,
            'max_teachers' => 200,
            'max_students' => 2000,
            'max_courses' => 500,
        ]);

        // Create the single system admin
        $systemAdmin = User::firstOrCreate([
            'email' => 'minaalkhurram318@gmail.com',
        ], [
            'name' => 'System Admin',
            'password' => bcrypt('password'),
        ]);
        $systemAdmin->assignRole('admin');

        // Create the single owner user
        $owner = User::firstOrCreate([
            'email' => 'minaal.khurram@dubizzlelabs.com',
        ], [
            'name' => 'Minaal Khurram',
            'password' => bcrypt('password'),
            'plan_id' => $proPlan->id,
        ]);
        $owner->assignRole('owner');

        // Create multiple schools owned by the same owner
        for ($i = 1; $i <= 2; $i++) {
            $school = School::create([
                'name' => "School $i",
                'slug' => Str::slug("School $i"),
                'plan_id' => $i === 1 ? $basicPlan->id : $proPlan->id,
            ]);

            // Attach the owner as owner of the school
            $school->users()->attach($owner->id, ['role' => 'owner']);

            // Create Teachers
            for ($t = 1; $t <= 2; $t++) {
                $teacher = User::create([
                    'name' => "Teacher{$t}_School{$i}",
                    'email' => "teacher{$t}_school{$i}@example.com",
                    'password' => bcrypt('password'),
                ]);
                $teacher->assignRole('teacher');
                $school->users()->attach($teacher->id, ['role' => 'teacher']);
            }

            // Create Students
            for ($s = 1; $s <= 3; $s++) {
                $student = User::create([
                    'name' => "Student{$s}_School{$i}",
                    'email' => "student{$s}_school{$i}@example.com",
                    'password' => bcrypt('password'),
                ]);
                $student->assignRole('student');
                $school->users()->attach($student->id, ['role' => 'student']);
            }

            // Create Courses for this school
            for ($c = 1; $c <= 3; $c++) {
                Course::create([
                    'name' => "Course{$c}_School{$i}",
                    'slug' => Str::slug("Course{$c}_School{$i}"),
                    'school_id' => $school->id,
                ]);
            }

            // Create Classes
            $classIds = [];
            for ($cl = 1; $cl <= 2; $cl++) {
                $class = SchoolClass::create([
                    'name' => "Class{$cl}_School{$i}",
                    'slug' => Str::slug("Class{$cl}_School{$i}"),
                    'school_id' => $school->id,
                ]);
                $classIds[] = $class->id;
            }

            // Attach all courses to all classes for now
            $courses = Course::where('school_id', $school->id)->get();
            foreach ($classIds as $classId) {
                foreach ($courses as $course) {
                    \DB::table('class_course')->insertOrIgnore([
                        'class_id' => $classId,
                        'course_id' => $course->id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }

    }
}

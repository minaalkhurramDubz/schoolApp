<?php

namespace Database\Seeders;

use App\Models\Plan;
use App\Models\School;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // create the roles for spatie
        foreach (['owner', 'admin', 'teacher', 'student'] as $role) {

            Role::firstOrCreate(['name' => $role]);
        }

        // seeding the plans table
        $plan = Plan::create([
            'name' => 'Basic Plan',
            'max_schools' => 10,
            'max_classes' => 100,
            'max_teachers' => 200,
            'max_students' => 1000,
            'max_courses' => 500,
        ]);

        Plan::create([
    'name' => 'Pro',

    'max_schools' => 5,
    'max_classes' => 100,
    'max_teachers' => 200,
    'max_students' => 2000,
    'max_courses' => 500,
]);

        // creating the owner
        // Create owner
        $owner = User::firstOrCreate([
            'name' => 'Owner User',
            'email' => 'owner@example.com',
            'password' => bcrypt('password'), // adjust if no password login
        ]);

        // assign the role of owner to the user

        $owner->assignRole('owner');

        // seeding the school db
        $school = School::create([
            'name' => 'My School',
            'slug' => 'my-school',
            'plan_id' => $plan->id,
        ]);
        // Attach owner to school
        $school->users()->attach($owner->id, ['role' => 'owner']);

    }
}

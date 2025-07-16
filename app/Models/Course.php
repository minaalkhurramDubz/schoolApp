<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'school_id',
    ];

    public function teachers()
    {
        return $this->belongsToMany(User::class, 'course_user')
            ->wherePivot('role', 'teacher')
            ->withTimestamps();
    }

    public function students()
    {
        return $this->belongsToMany(User::class, 'course_user')
            ->wherePivot('role', 'student')
            ->withTimestamps();
    }

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function classes()
    {
        return $this->belongsToMany(
            SchoolClass::class,
            'class_course',
            'course_id',
            'class_id'
        );
    }

    /**
     * A course can have many students (through classes).
     */
    // public function students()
    // {
    //     return $this->hasManyThrough(
    //         User::class,
    //         'class_course', // pivot from course → class
    //         'course_id',    // FK on class_course
    //         'id',           // PK on users
    //         'id',           // PK on courses
    //         null            // no pivot to user directly
    //     )->wherePivot('role', 'student');
    // }

    public function users()
    {
        return $this->belongsToMany(User::class, 'course_user')
            ->withPivot('role');
    }
}

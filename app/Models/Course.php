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
    ];

    /**
     * A course can belong to many classes.
     */
    public function classes()
    {
        return $this->belongsToMany(SchoolClass::class, 'class_course', 'course_id', 'class_id')
            ->withTimestamps();
    }

    /**
     * A course can have many students (through classes).
     */
    public function students()
    {
        return $this->hasManyThrough(
            User::class,
            'class_course', // pivot from course → class
            'course_id',    // FK on class_course
            'id',           // PK on users
            'id',           // PK on courses
            null            // no pivot to user directly
        )->wherePivot('role', 'student');
    }
}

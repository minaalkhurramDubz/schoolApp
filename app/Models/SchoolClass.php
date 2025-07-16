<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SchoolClass extends Model
{
    use HasFactory;

    protected $table = 'classes';

    protected $fillable = [
        'name',
        'slug',
        'school_id',
    ];

    /**
     * A class belongs to a single school.
     */
    public function school()
    {
        return $this->belongsTo(School::class);
    }

    /**
     * A class can have many students (users with “student” role).
     */
    public function students()
    {
        return $this->belongsToMany(User::class, 'class_user', 'class_id', 'user_id')
            ->withPivot('role')
            ->wherePivot('role', 'student')
            ->withTimestamps();
    }

    /**
     * A class can have many teachers.
     */
    public function teachers()
    {
        return $this->belongsToMany(User::class, 'class_user', 'class_id', 'user_id')
            ->withPivot('role')
            ->wherePivot('role', 'teacher')
            ->withTimestamps();
    }

    /**
     * A class can offer many courses.
     */
    public function courses()
    {
        return $this->belongsToMany(Course::class, 'class_course', 'class_id', 'course_id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Traits\HasRoles;

class Plan extends Model
{
    //
    use HasRoles;

    // the rows, attirbutes of plan table
    protected $fillable = [
        'name',
        'max_admins',
        'max_schools',
        'max_classes',
        'max_teachers',
        'max_students',
        'max_courses',
    ];

    // this rs links plans to owners
    public function owners()
    {
        return $this->hasMany(User::class, 'plan_id');
    }
    protected static function booted()
{
    static::retrieved(function (Course $course) {
        $course->teachers = $course->teachers()->pluck('id')->toArray();
    });
}

}

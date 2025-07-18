<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Traits\HasRoles;

class School extends Model
{
    //

    use HasRoles;

    protected $fillable = [
        'name',
        'slug',
    ];

    public function users()
    {
        return $this->belongsToMany(User::class, 'school_user')
            ->withPivot('role')
            ->withTimestamps();
    }

    public function plan()
    {
        return $this->belongsTo(\App\Models\Plan::class);
    }

    public function classes()
    {
        return $this->hasMany(SchoolClass::class); // assuming your model is SchoolClass
    }

    public function courses()
    {
        return $this->hasMany(Course::class);
    }
}

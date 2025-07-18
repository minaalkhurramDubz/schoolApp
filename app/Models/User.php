<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, HasRoles, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function schools() 
    {
        return $this->belongsToMany(School::class, 'school_user')
            ->withPivot('role')
            ->withTimestamps();
    }

    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    public function teachingClasses()
    {
        return $this->belongsToMany(SchoolClass::class, 'class_user', 'user_id', 'class_id')
            ->withPivot('role')
            ->wherePivot('role', 'teacher')
            ->withTimestamps();
    }

    public function attendingClasses()
    {
        return $this->belongsToMany(SchoolClass::class, 'class_user', 'user_id', 'class_id')
            ->withPivot('role')
            ->wherePivot('role', 'student')
            ->withTimestamps();
    }

    

}

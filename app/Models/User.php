<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'role',
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
    
    /**
     * Get courses created by this teacher
     */
    public function teacherCourses()
    {
        return $this->hasMany(Course::class);
    }
    
    /**
     * Get courses enrolled by this student
     */
    public function enrolledCourses()
    {
        return $this->belongsToMany(Course::class, 'student_course')
            ->withPivot('enrolled_at', 'completed_at', 'progress_percentage')
            ->withTimestamps();
    }
    
    /**
     * Get lesson progress for this student
     */
    public function lessonProgress()
    {
        return $this->hasMany(LessonProgress::class);
    }
    
    /**
     * Check if user is a teacher
     */
    public function isTeacher()
    {
        return $this->role === 'teacher';
    }
    
    /**
     * Check if user is a student
     */
    public function isStudent()
    {
        return $this->role === 'student';
    }
}

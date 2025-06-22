<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    
    protected $fillable = [
        'user_id',
        'title',
        'description',
        'status',
    ];
    
    // Get teacher of the course
    public function teacher()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    
    // Get lessons
    public function lessons()
    {
        return $this->hasMany(Lesson::class)->orderBy('order');
    }
    
    // Get students enrolled in the course
    public function enrolledStudents()
    {
        return $this->belongsToMany(User::class, 'student_course')
            ->withPivot('enrolled_at', 'completed_at', 'progress_percentage')
            ->withTimestamps();
    }
    
    //Get course lessons
    public function publishedLessons()
    {
        return $this->hasMany(Lesson::class)
            ->orderBy('order');
    }
}

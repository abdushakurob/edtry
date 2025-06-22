<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lesson extends Model
{
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'course_id',
        'title',
        'content',
        'order',
        'status',
    ];
    
    /**
     * Get the course this lesson belongs to
     */
    public function course()
    {
        return $this->belongsTo(Course::class);
    }
    
    /**
     * Get progress records for this lesson
     */
    public function progress()
    {
        return $this->hasMany(LessonProgress::class);
    }
    
    /**
     * Get teacher/author through the course relationship
     */
    public function teacher()
    {
        return $this->hasOneThrough(User::class, Course::class, 'id', 'id', 'course_id', 'user_id');
    }
    
    /**
     * Get the next lesson in course sequence
     */
    public function nextLesson()
    {
        return Lesson::where('course_id', $this->course_id)
            ->where('order', '>', $this->order)
            ->orderBy('order')
            ->first();
    }
    
    /**
     * Get the previous lesson in course sequence
     */
    public function previousLesson()
    {
        return Lesson::where('course_id', $this->course_id)
            ->where('order', '<', $this->order)
            ->orderBy('order', 'desc')
            ->first();
    }
}

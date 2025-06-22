<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LessonProgress extends Model
{
    /**
     * The table associated with the model.
     */
    protected $table = 'student_progress';
    
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'user_id',
        'lesson_id',
        'completed',
        'completed_at',
        'time_spent_seconds',
    ];
    
    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'completed' => 'boolean',
        'completed_at' => 'datetime',
        'time_spent_seconds' => 'integer',
    ];
    
    /**
     * Get the student
     */
    public function student()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    
    /**
     * Get the lesson
     */
    public function lesson()
    {
        return $this->belongsTo(Lesson::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CourseEnrollment extends Model
{
   
    protected $table = 'student_course';
    
  
    protected $fillable = [
        'user_id',
        'course_id',
        'enrolled_at',
        'completed_at',
        'progress_percentage',
    ];
    
   
    protected $casts = [
        'enrolled_at' => 'datetime',
        'completed_at' => 'datetime',
        'progress_percentage' => 'float',
    ];
    
 
    public function student()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
   
    public function course()
    {
        return $this->belongsTo(Course::class);
    }
}

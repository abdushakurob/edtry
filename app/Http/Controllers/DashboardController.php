<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Lesson;
use App\Models\LessonProgress;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $user = Auth::user();
        if ($user->role === 'teacher') {
            return view('teacher.dashboard');
        }
        return view('student.dashboard');
    }

    public function courses()
    {
        $user = Auth::user();
        if ($user->role === 'teacher') {
            return view('teacher.courses');
        }
        return view('student.my-courses');
    }

    public function createCourse()
    {
        return view('teacher.course-create');
    }

    public function lessons()
    {
        return view('student.dashboard');
    }

    public function viewLesson($id)
    {
        
        return view('student.lesson');
    }

    public function exploreCourses()
    {
        return view('student.explore');
    }
    
    
    
    public function allCourses()
    {
        return view('student.my-courses');
    }


    public function getEnrolledCourses()
    {
        $user = Auth::user();
        $courses = $user->enrolledCourses()
            ->with(['lessons' => function($query) {
                $query->orderBy('order');
            }])
            ->withCount(['lessons'])
            ->get();
            
        foreach ($courses as $course) {
            $lessonIds = $course->lessons->pluck('id')->toArray();
            $completedLessonsCount = LessonProgress::where('user_id', $user->id)
                ->whereIn('lesson_id', $lessonIds)
                ->where('completed', true)
                ->count();
                
            $course->completed_lessons_count = $completedLessonsCount;
        }
        
        return response()->json($courses);
    }
    
    public function getCourseDetails($id)
    {
        $user = Auth::user();
        $course = Course::with(['lessons' => function($query) {
                $query->orderBy('order');
            }])
            ->findOrFail($id);
            
        $isEnrolled = $user->enrolledCourses()->where('course_id', $id)->exists();
        
        if (!$isEnrolled) {
            return response()->json(['error' => 'You are not enrolled in this course'], 403);
        }
        
        foreach ($course->lessons as $lesson) {
            $progress = LessonProgress::where('user_id', $user->id)
                ->where('lesson_id', $lesson->id)
                ->first();
                
            $lesson->progress = $progress;
        }
        
        return response()->json($course);
    }
    
    public function completeLesson(Request $request, $id)
    {
        $user = Auth::user();
        $lesson = Lesson::findOrFail($id);
        
        $progress = LessonProgress::firstOrCreate(
            ['user_id' => $user->id, 'lesson_id' => $id],
            ['completed' => false, 'time_spent_seconds' => 0]
        );
        
        $progress->completed = true;
        $progress->completed_at = now();
        $progress->time_spent_seconds = $request->input('timeSpent', $progress->time_spent_seconds);
        $progress->save();
       
        $courseId = $lesson->course_id;
        $totalLessons = Lesson::where('course_id', $courseId)
            ->count();
            
        $completedLessons = LessonProgress::where('user_id', $user->id)
            ->whereHas('lesson', function($query) use ($courseId) {
                $query->where('course_id', $courseId);
            })
            ->where('completed', true)
            ->count();
            
        $progressPercentage = ($totalLessons > 0) ? ($completedLessons / $totalLessons) * 100 : 0;
        
        $enrollment = $user->enrolledCourses()->where('course_id', $courseId)->first()->pivot;
        $enrollment->progress_percentage = $progressPercentage;
 
        if ($progressPercentage >= 100 && !$enrollment->completed_at) {
            $enrollment->completed_at = now();
        }
        
        $enrollment->save();
        
        return response()->json([
            'success' => true,
            'progress' => $progress,
            'courseProgress' => $progressPercentage
        ]);
    }

    public function getAvailableCourses()
    {
        $courses = Course::where('status', 'published')
            ->withCount(['lessons'])
            ->withCount('enrolledStudents as enrollment_count')
            ->get();
            
        foreach ($courses as $course) {
            if (stripos($course->title, 'web') !== false) {
                $course->category = 'web-dev';
            } elseif (stripos($course->title, 'javascript') !== false || 
                      stripos($course->title, 'react') !== false ||
                      stripos($course->title, 'programming') !== false) {
                $course->category = 'programming';
            } elseif (stripos($course->title, 'design') !== false) {
                $course->category = 'design';
            } else {
                $course->category = 'other';
            }
            
           
            $course->featured = $course->enrollment_count > 50;
            
            $isEnrolled = Auth::user()
                ->enrolledCourses()
                ->where('course_id', $course->id)
                ->exists();
                
            $course->is_enrolled = $isEnrolled;
        }
        
        return response()->json($courses);
    }
    
    public function enrollInCourse($id)
    {
        $user = Auth::user();
        $course = Course::findOrFail($id);
        
        if ($user->enrolledCourses()->where('course_id', $id)->exists()) {
            return response()->json([
                'message' => 'You are already enrolled in this course'
            ], 422);
        }
        
        $user->enrolledCourses()->attach($course->id, [
            'enrolled_at' => now(),
            'progress_percentage' => 0,
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Successfully enrolled in the course'
        ]);
    }
    
    public function getLessonDetails($id)
    {
        $user = Auth::user();
        $lesson = Lesson::with('course')->findOrFail($id);
        
        $isEnrolled = $user->enrolledCourses()->where('course_id', $lesson->course_id)->exists();
        
        if (!$isEnrolled) {
            return response()->json(['error' => 'You are not enrolled in this course'], 403);
        }
        
        $progress = LessonProgress::where('user_id', $user->id)
            ->where('lesson_id', $id)
            ->first();
            
        $lesson->progress = $progress;
        
        return response()->json($lesson);
    }
    


    
    public function getTeacherCourses()
    {
        $user = Auth::user();
        $courses = $user->teacherCourses()
            ->withCount(['lessons'])
            ->withCount('enrolledStudents as students_count')
            ->orderBy('created_at', 'desc')
            ->get();
            
        $uniqueStudentIds = [];
        foreach ($courses as $course) {
            $studentIds = $course->enrolledStudents()->where('role', 'student')->pluck('users.id')->toArray();
            $uniqueStudentIds = array_merge($uniqueStudentIds, $studentIds);
        }
        $uniqueStudentCount = count(array_unique($uniqueStudentIds));
        
        return response()->json([
            'courses' => $courses,
            'unique_student_count' => $uniqueStudentCount
        ]);
    }
    
    public function createNewCourse(Request $request)
    {
        $user = Auth::user();
        
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            // 'thumbnail' => 'nullable|string|url',
            'status' => 'sometimes|in:published',
            'lessons' => 'nullable|array',
            'lessons.*.title' => 'sometimes|required|string|max:255',
            'lessons.*.content' => 'sometimes|required|string',
            'lessons.*.status' => 'sometimes|in:published',
            'lessons.*.order' => 'sometimes|required|integer|min:1',
        ]);
        
        $course = new Course();
        $course->user_id = $user->id;
        $course->title = $request->title;
        $course->description = $request->description;
        $course->status = 'published';
        $course->save();
        
        if ($request->has('lessons') && is_array($request->lessons)) {
            foreach ($request->lessons as $lessonData) {
                $lesson = new Lesson();
                $lesson->course_id = $course->id;
                $lesson->title = $lessonData['title'];
                $lesson->content = $lessonData['content'];
                $lesson->status = 'published';
                $lesson->order = $lessonData['order'] ?? 1;
                $lesson->save();
            }
        }
        // Send for chunking 
        {
            $course->load(['lessons' => function ($query) {
            $query->orderBy('order');
        }]);
            foreach ($course->lessons as $lesson) {
                try {
                    Http::timeout(5)->withHeaders([
                        'X-Internal-API-Key' => env('EDTRY_INTERNAL_API_KEY'),
                    ])->post(env('CHUNK_URL' ) . '/chunk', [
                        'course_id' => $course->id,
                        'lesson_id' => $lesson->id,
                        'lesson_title' => $lesson->title,
                        'lesson_content' => $lesson->content,
                        'type' => 'created',
                    ]);
                } catch (\Exception $e) {
                    \Log::error("Chunking failed on course creation for lesson {$lesson->id}: " . $e->getMessage());
                }
            }
        }

        
        return response()->json($course);
    }
    
    public function getTeacherCourseDetails($id)
    {
        $user = Auth::user();
        $course = Course::with(['lessons' => function($query) {
                $query->orderBy('order');
            }])
            ->where('user_id', $user->id)
            ->findOrFail($id);
            
        return response()->json($course);
    }
    
    public function updateCourse(Request $request, $id)
    {
        $user = Auth::user();
        $course = Course::where('user_id', $user->id)->findOrFail($id);
        
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            // 'thumbnail' => 'nullable|string',
            'status' => 'sometimes|in:published',
            'lessons' => 'nullable|array',
            'lessons.*.id' => 'nullable|integer|exists:lessons,id',
            'lessons.*.title' => 'required|string|max:255',
            'lessons.*.content' => 'required|string',
            'lessons.*.status' => 'sometimes|in:published',
            'lessons.*.order' => 'required|integer|min:1',
        ]);
        
        $course->title = $request->title;
        $course->description = $request->description;
        // $course->thumbnail = $request->thumbnail;
        $course->status = 'published';
        $course->save();
        
        if ($request->has('lessons')) {
            $existingLessonIds = $course->lessons()->pluck('id')->toArray();
            $updatedLessonIds = [];
            
            foreach ($request->lessons as $lessonData) {
                if (isset($lessonData['id'])) {
                    $lesson = Lesson::where('course_id', $course->id)
                        ->where('id', $lessonData['id'])
                        ->first();
                        
                    if ($lesson) {
                        $lesson->title = $lessonData['title'];
                        $lesson->content = $lessonData['content'];
                        $lesson->status = 'published';
                        $lesson->order = $lessonData['order'];
                        $lesson->save();
                        
                        $updatedLessonIds[] = $lesson->id;
                    }
                } else {
                    $lesson = new Lesson();
                    $lesson->course_id = $course->id;
                    $lesson->title = $lessonData['title'];
                    $lesson->content = $lessonData['content'];
                    $lesson->status = 'published';
                    $lesson->order = $lessonData['order'];
                    $lesson->save();
                    
                    $updatedLessonIds[] = $lesson->id;
                }
            }
            
            $lessonsToDelete = array_diff($existingLessonIds, $updatedLessonIds);
            if (!empty($lessonsToDelete)) {
                Lesson::whereIn('id', $lessonsToDelete)->delete();
            }
            // Send for chunking 
            {
                $course->load(['lessons' => function ($query) {
                    $query->orderBy('order');
                }]);

                foreach ($course->lessons as $lesson) {
                    try {
                        Http::timeout(5)->withHeaders([
                        'X-Internal-API-Key' => env('EDTRY_INTERNAL_API_KEY'),
                    ])->post(env('CHUNK_URL' ) . '/chunk', [
                            'course_id' => $course->id,
                            'lesson_id' => $lesson->id,
                            'lesson_title' => $lesson->title,
                            'lesson_content' => $lesson->content,
                            'type' => 'updated',
                        ]);
                    } catch (\Exception $e) {
                        \Log::error("Chunking failed on course update for lesson {$lesson->id}: " . $e->getMessage());
                    }
                }
            }

        }
        
        $updatedCourse = Course::with(['lessons' => function($query) {
                $query->orderBy('order');
            }])
            ->findOrFail($course->id);
            
        return response()->json($updatedCourse);
    }
        
    public function deleteCourse($id)
    {
        $user = Auth::user();
        $course = Course::where('user_id', $user->id)->findOrFail($id);
        
        // Fetch lessons before deletion
        $lessons = $course->lessons()->get();
        
       
        $course->lessons()->delete();
        $course->delete();
        
        // Send chunk deletion notification
        foreach ($lessons as $lesson) {
            try {
                Http::timeout(5)->post('/api/chuncked', [
                    'course_id' => $course->id,
                    'lesson_id' => $lesson->id,
                    'title' => $lesson->title,
                    'text' => $lesson->content,
                    'type' => 'deleted',
                    'chunks' => []
                ]);
            } catch (\Exception $e) {
                \Log::error("Chunking failed on course deletion for lesson {$lesson->id}: " . $e->getMessage());
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Course deleted successfully'
        ]);
    }

    public function getCourseStudents($id)
    {
        $user = Auth::user();
        $course = Course::where('user_id', $user->id)->findOrFail($id);
        
        $students = $course->enrolledStudents()
            ->with(['lessonProgress' => function($query) use ($course) {
                $query->whereHas('lesson', function($q) use ($course) {
                    $q->where('course_id', $course->id);
                });
            }])
            ->orderBy('name')
            ->get();
            
        return response()->json($students);
    }
    
    public function viewCourseDetails($id)
    {
        $user = Auth::user();
        
        if ($user->role === 'teacher') {
            $course = Course::where('id', $id)
                ->where('user_id', $user->id)
                ->first();
                
            if (!$course) {
                return redirect('/dashboard/courses')->with('error', 'Course not found or you do not have permission to access it');
            }
            
            return view('teacher.course-detail');
        } else if ($user->role === 'student') {
            $isEnrolled = $user->enrolledCourses()->where('course_id', $id)->exists();
            $isPublic = Course::where('id', $id)->where('status', 'published')->exists();
            
            if (!$isEnrolled && !$isPublic) {
                return redirect('/dashboard/courses')->with('error', 'You are not enrolled in this course');
            }
            
            return view('student.course');
        }
        
        return redirect('/dashboard');
    }
}
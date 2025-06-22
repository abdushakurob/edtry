<template>
  <div>
    <Sidebar :links="links" :user="user"/>
    <div class="md:pl-64">
      <div class="container mx-auto py-8 px-4">
        <button 
          @click="navigateTo('/dashboard/courses')"
          class="mb-6 flex items-center text-gray-600 hover:text-accent transition-colors"
        >
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
          </svg>
          Back to courses
        </button>
        
        <div v-if="isLoading" class="flex justify-center py-16">
          <svg class="animate-spin h-8 w-8 text-accent" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
        </div>
        
        <div v-else-if="course">
          <div class="flex flex-col md:flex-row md:justify-between md:items-center mb-6">
            <div>
              <h1 class="text-2xl md:text-3xl font-bold text-gray-800">{{ isNewCourse ? 'Create New Course' : 'Edit Course' }}</h1>
              <p v-if="!isNewCourse" class="text-gray-600 mt-1">Last updated: {{ formatDate(course.updated_at) }}</p>
            </div>
            
            <div class="mt-4 md:mt-0 flex items-center gap-3">
              <span class="text-sm font-medium text-green-700">Published</span>
            </div>

              <button 
                @click="saveCourse"
                :disabled="isSaving"
                class="flex items-center justify-center bg-accent hover:bg-highlight disabled:opacity-70 disabled:cursor-not-allowed text-white font-medium py-2 px-4 rounded transition-colors"
              >
                <svg v-if="isSaving" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                {{ isSaving ? 'Saving...' : 'Save Course' }}
              </button>
            </div>
          </div>
          <div class="mb-6">
            <div class="border-b border-gray-200">
              <nav class="-mb-px flex space-x-6">
                <button 
                  @click="activeTab = 'details'"
                  :class="activeTab === 'details' 
                    ? 'border-accent text-accent' 
                    : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                  class="py-3 px-1 border-b-2 font-medium text-sm"
                >
                  Course Details
                </button>
                <button 
                  @click="activeTab = 'lessons'"
                  :class="activeTab === 'lessons' 
                    ? 'border-accent text-accent' 
                    : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                  class="py-3 px-1 border-b-2 font-medium text-sm"
                >
                  Lessons
                </button>
                <button 
                  v-if="!isNewCourse"
                  @click="activeTab = 'students'"
                  :class="activeTab === 'students' 
                    ? 'border-accent text-accent' 
                    : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                  class="py-3 px-1 border-b-2 font-medium text-sm"
                >
                  Students
                </button>
              </nav>
            </div>
          </div>
          
          <div>
            <div v-if="activeTab === 'details'" class="space-y-6">
              <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Basic Information</h2>
                
                <div class="space-y-4">
                  <div>
                    <label for="title" class="block text-sm font-medium text-gray-700 mb-1">Course Title*</label>
                    <input 
                      type="text" 
                      id="title" 
                      v-model="course.title" 
                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-accent"
                      placeholder="Enter course title"
                    />
                    <p v-if="errors.title" class="mt-1 text-sm text-red-600">{{ errors.title[0] }}</p>
                  </div>
                  
                  <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Course Description*</label>
                    <textarea 
                      id="description" 
                      v-model="course.description" 
                      rows="4"
                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-accent"
                      placeholder="Enter course description"
                    ></textarea>
                    <p v-if="errors.description" class="mt-1 text-sm text-red-600">{{ errors.description[0] }}</p>
                  </div>
                  
                </div>
              </div>
            </div>
            
            <div v-else-if="activeTab === 'lessons'">
              <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex justify-between items-center mb-6">
                  <h2 class="text-lg font-semibold text-gray-800">Course Lessons</h2>
                 
                </div>
                <div v-if="course.lessons && course.lessons.length > 0">
                  <div class="space-y-3">
                    <div v-for="(lesson, index) in course.lessons" :key="index">
                      <div :key="index" class="border border-gray-200 rounded-md mb-4 overflow-hidden">
                        <div class="flex items-center justify-between bg-gray-50 px-4 py-3">
                          <div class="flex items-center">
                            <h3 class="text-base font-medium">Lesson {{ index + 1 }}{{ lesson.is_new ? ' (New)' : '' }}</h3>
                          </div>
                          <div class="flex items-center gap-2">
                            <button 
                              @click="toggleLessonExpand(index)"
                              class="text-gray-500 hover:text-gray-700"
                            >
                              <svg v-if="lesson.expanded" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                              </svg>
                              <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                              </svg>
                            </button>
                            <button 
                              @click="removeLesson(index)"
                              class="text-red-500 hover:text-red-700"
                              title="Remove lesson"
                            >
                              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                              </svg>
                            </button>
                          </div>
                        </div>
                        
                        <div v-if="lesson.expanded" class="p-4 border-t border-gray-200">
                          <div class="space-y-4">
                            <div>
                              <label :for="'lesson-title-' + index" class="block text-sm font-medium text-gray-700 mb-1">Lesson Title*</label>
                              <input 
                                :id="'lesson-title-' + index" 
                                v-model="lesson.title" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-accent"
                                placeholder="Enter lesson title"
                              />
                            </div>
                            
                            <div>
                              <label :for="'lesson-content-' + index" class="block text-sm font-medium text-gray-700 mb-1">Lesson Content*</label>
                              <textarea 
                                :id="'lesson-content-' + index" 
                                v-model="lesson.content" 
                                rows="6"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-accent"
                                placeholder="Enter lesson content (plain text only)"
                              ></textarea>
                            </div>
                            
                            <div class="flex items-center">
                              <!-- <span class="text-sm font-medium text-green-700">Published</span> -->
                            </div>
                             <button 
                              @click="addNewLesson"
                              class="flex items-center justify-center bg-accent hover:bg-highlight text-white font-medium py-2 px-3 rounded-md text-sm transition-colors"
                            >
                              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                              </svg>
                              Add Lesson
                            </button>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                
                <div v-else-if="!course.lessons || course.lessons.length === 0" class="text-center py-8">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                  </svg>
                  <h3 class="mt-4 text-lg font-medium text-gray-800">No lessons yet</h3>
                  <p class="mt-2 text-gray-600">Start adding lessons to your course</p>
                  <button 
                    @click="addNewLesson"
                    class="mt-4 bg-accent hover:bg-highlight text-white font-medium py-2 px-4 rounded transition-colors"
                  >
                    Add First Lesson
                  </button>
                </div>
              </div>
            </div>
            
            <div v-else-if="activeTab === 'students'">
              <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Enrolled Students</h2>
                
                <div v-if="isLoadingStudents" class="flex justify-center py-8">
                  <svg class="animate-spin h-6 w-6 text-accent" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                </div>
                
                <div v-else>
                  <div v-if="courseStudents.length === 0" class="text-center py-8">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    <h3 class="mt-4 text-lg font-medium text-gray-800">No students enrolled yet</h3>
                    <p class="mt-2 text-gray-600">Students will appear here once they enroll in your course</p>
                  </div>
                  
                  <div v-else class="overflow-x-auto">
                    <table class="min-w-full">
                      <thead>
                        <tr class="text-left text-xs text-gray-500 uppercase tracking-wider border-b border-gray-200">
                          <th class="py-3 px-4">Student</th>
                          <th class="py-3 px-4">Progress</th>
                          <th class="py-3 px-4">Enrolled Date</th>
                          <th class="py-3 px-4">Completion</th>
                        </tr>
                      </thead>
                      <tbody class="divide-y divide-gray-200">
                        <tr v-for="(student, index) in courseStudents" :key="index" class="hover:bg-gray-50">
                          <td class="py-3 px-4">
                            <div class="flex items-center">
                              <div class="text-sm font-medium text-gray-900">{{ student.name }}</div>
                            </div>
                          </td>
                          <td class="py-3 px-4">
                            <div class="relative w-full h-2 bg-gray-200 rounded-full overflow-hidden">
                              <div 
                                class="absolute h-full bg-accent" 
                                :style="`width: ${student.pivot.progress_percentage}%`"
                              ></div>
                            </div>
                            <span class="text-xs text-gray-500">{{ Math.round(student.pivot.progress_percentage) }}%</span>
                          </td>
                          <td class="py-3 px-4 text-sm text-gray-500">
                            {{ formatDate(student.pivot.enrolled_at) }}
                          </td>
                          <td class="py-3 px-4 text-sm">
                            <span v-if="student.pivot.completed_at" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                              Completed
                            </span>
                            <span v-else class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                              In Progress
                            </span>
                          </td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      
      <div v-if="!isLoading && !course" class="bg-white rounded-lg shadow-md p-8 text-center">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <h3 class="mt-4 text-lg font-medium text-gray-800">Course not found</h3>
        <p class="mt-2 text-gray-600">The course you're looking for doesn't exist or you don't have permission to access it</p>
        <a 
          href="/dashboard/courses"
          class="mt-4 inline-block bg-accent hover:bg-highlight text-white font-medium py-2 px-4 rounded transition-colors"
        >
          Go to My Courses
        </a>
      </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import Sidebar from '@/Components/dashboard/Sidebar.vue'

const links = [
  { name: 'Dashboard', href: '/dashboard' },
  { name: 'Courses', href: '/dashboard/courses' },
]

const user = ref(null)
const course = ref({
  title: '',
  description: '',
  status: 'published',
  lessons: []
})
const courseStudents = ref([])
const errors = ref({})
const isLoading = ref(true)
const isLoadingStudents = ref(false)
const isSaving = ref(false)
const activeTab = ref('details')

const isNewCourse = computed(() => {
  return !course.value?.id
})

const courseId = window.location.pathname.split('/').pop()
const isCreateMode = courseId === 'create'

const fetchCourse = async () => {
  if (isCreateMode) {
    course.value = {
      title: '',
      description: '',
          status: 'published',
      lessons: []
    }
    isLoading.value = false
    return
  }
  
  try {
    isLoading.value = true
    const response = await axios.get(`/api/teacher/courses/${courseId}`)
    course.value = response.data
    
    if (course.value.lessons) {
      course.value.lessons.forEach(lesson => {
        lesson.expanded = false
      })
    } else {
      course.value.lessons = []
    }
    
  } catch (error) {
    console.error('Failed to fetch course:', error)
    
    course.value = {
      title: '',
      description: '',
      status: 'published',
      lessons: []
    }
  } finally {
    isLoading.value = false
  }
}

const fetchCourseStudents = async () => {
  if (isCreateMode) return
  
  try {
    isLoadingStudents.value = true
    const response = await axios.get(`/api/teacher/courses/${courseId}/students`)
    courseStudents.value = response.data
  } catch (error) {
    console.error('Failed to fetch course students:', error)
    courseStudents.value = []
  } finally {
    isLoadingStudents.value = false
  }
}

const navigateTo = (path) => {
  window.location = path
}

const formatDate = (dateString) => {
  if (!dateString) return 'N/A'
  const date = new Date(dateString)
  return new Intl.DateTimeFormat('en-US', { 
    year: 'numeric', 
    month: 'short', 
    day: 'numeric' 
  }).format(date)
}

const saveCourse = async () => {
  const validationErrors = {}
  if (!course.value.title) {
    validationErrors.title = ['Course title is required']
  }
  if (!course.value.description) {
    validationErrors.description = ['Course description is required']
  }
  
  if (course.value.lessons && course.value.lessons.length > 0) {
    course.value.lessons.forEach((lesson, index) => {
      if (!lesson.title) {
        if (!validationErrors.lessons) validationErrors.lessons = []
        validationErrors.lessons.push(`Lesson ${index + 1} title is required`)
      }
      if (!lesson.content) {
        if (!validationErrors.lessons) validationErrors.lessons = []
        validationErrors.lessons.push(`Lesson ${index + 1} content is required`)
      }
    })
  }
  
  if (Object.keys(validationErrors).length > 0) {
    errors.value = validationErrors
    window.scrollTo({ top: 0, behavior: 'smooth' })
    return
  }
  
  try {
    isSaving.value = true
    
    if (course.value.lessons && course.value.lessons.length > 0) {
      course.value.lessons.forEach((lesson, index) => {
        lesson.order = index + 1
      })
    }
    
    let response
    
    if (isCreateMode) {
      response = await axios.post('/api/teacher/courses', course.value)
    } else {
      response = await axios.put(`/api/teacher/courses/${courseId}`, course.value)
    }
    
    if (response.data.id) {
      if (isCreateMode) {
        navigateTo(`/dashboard/courses/${response.data.id}`)
      } else {
        course.value = response.data
        if (course.value.lessons) {
          course.value.lessons.forEach(lesson => {
            lesson.expanded = false
          })
        }
        
        errors.value = {}
      }
    }
  } catch (error) {
    console.error('Failed to save course:', error)
    
    if (error.response && error.response.data && error.response.data.errors) {
      errors.value = error.response.data.errors
    } else {
      errors.value = { general: ['An error occurred while saving the course'] }
    }
    
    window.scrollTo({ top: 0, behavior: 'smooth' })
  } finally {
    isSaving.value = false
  }
}

const addNewLesson = () => {
  const newLesson = {
    title: '',
    content: '',
    status: 'published',
    order: (course.value.lessons?.length || 0) + 1,
    expanded: true,
    is_new: true
  }
  
  if (!course.value.lessons) {
    course.value.lessons = []
  }
  
  course.value.lessons.push(newLesson)
  
  setTimeout(() => {
    const lessonElements = document.querySelectorAll('.border.border-gray-200.rounded-md')
    if (lessonElements.length > 0) {
      lessonElements[lessonElements.length - 1].scrollIntoView({ behavior: 'smooth' })
    }
  }, 100)
}

const toggleLessonExpand = (index) => {
  if (course.value.lessons[index]) {
    course.value.lessons[index].expanded = !course.value.lessons[index].expanded
  }
}

const removeLesson = (index) => {
  if (confirm(`Are you sure you want to remove this lesson? This action cannot be undone.`)) {
    course.value.lessons.splice(index, 1)
    
    course.value.lessons.forEach((lesson, idx) => {
      lesson.order = idx + 1
    })
  }
}

const fetchUserData = async () => {
  try {
    const response = await fetch('/api/user')
    if (response.ok) {
      const data = await response.json()
      user.value = data
    }
  } catch (error) {
    console.error('Failed to fetch user data:', error)
  }
}

onMounted(() => {
  if (window.Laravel && window.Laravel.user) {
    user.value = window.Laravel.user
  } else {
    fetchUserData()
  }
  
  fetchCourse()
  
  if (!isCreateMode) {
    fetchCourseStudents()
  }
})
</script>

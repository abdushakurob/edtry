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
        
        <div class="flex flex-col md:flex-row md:justify-between md:items-center mb-6">
          <div>
            <h1 class="text-2xl md:text-3xl font-bold text-gray-800">Create New Course</h1>
            <p class="text-gray-600 mt-1">Fill in the details to create your new course</p>
          </div>
          
          <div class="mt-4 md:mt-0">
            <button 
              @click="saveCourse"
              :disabled="isSaving"
              class="flex items-center justify-center bg-accent hover:bg-highlight disabled:opacity-70 disabled:cursor-not-allowed text-white font-medium py-2 px-4 rounded transition-colors"
            >
              <svg v-if="isSaving" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              {{ isSaving ? 'Creating...' : 'Create Course' }}
            </button>
          </div>
        </div>
        
        
        <div v-if="Object.keys(errors).length > 0" class="mb-6 bg-red-50 border border-red-200 rounded-md p-4">
          <div class="flex items-start">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-red-400 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <div class="ml-3">
              <h3 class="text-sm font-medium text-red-800">Please fix the following errors:</h3>
              <div class="mt-2 text-sm text-red-700">
                <ul class="list-disc list-inside">
                  <li v-for="(errorValue, field) in errors" :key="field">
                    <template v-if="Array.isArray(errorValue) && typeof errorValue[0] === 'string'">
                      {{ errorValue[0] }}
                    </template>
                    <template v-else-if="Array.isArray(errorValue)">
                      <span v-for="(error, i) in errorValue" :key="i" class="block ml-4">- {{ error }}</span>
                    </template>
                  </li>
                </ul>
              </div>
            </div>
          </div>
        </div>
        
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
          <h2 class="text-lg font-semibold text-gray-800 mb-4">Course Information</h2>
          
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
            </div>
            
          </div>
        </div>
        
        
        <div class="bg-white rounded-lg shadow-md p-6">
          <div class="flex justify-between items-center mb-4">
            <h2 class="text-lg font-semibold text-gray-800">Course Lessons</h2>
            
          </div>
          
          <div v-if="lessons.length > 0" class="space-y-6">
            <div v-for="(lesson, index) in lessons" :key="index" class="border border-gray-200 rounded-md p-4">
              <div class="flex justify-between items-center mb-3">
                <h3 class="text-md font-medium">Lesson {{ index + 1 }}</h3>
                <button 
                  v-if="lessons.length > 1"
                  @click="removeLesson(index)" 
                  class="text-red-500 hover:text-red-700"
                  title="Remove lesson"
                >
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                  </svg>
                </button>
              </div>
              
              <div class="space-y-4">
                <div>
                  <label :for="'lessonTitle-' + index" class="block text-sm font-medium text-gray-700 mb-1">Lesson Title*</label>
                  <input 
                    type="text" 
                    :id="'lessonTitle-' + index" 
                    v-model="lesson.title" 
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-accent"
                    placeholder="Enter lesson title"
                  />
                </div>
                
                <div>
                  <label :for="'lessonContent-' + index" class="block text-sm font-medium text-gray-700 mb-1">Lesson Content*</label>
                  <textarea 
                    :id="'lessonContent-' + index" 
                    v-model="lesson.content" 
                    rows="6"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-accent"
                    placeholder="Enter lesson content (plain text only)"
                  ></textarea>
                </div>
                
                <div class="flex items-center">
                  <span class="text-sm font-medium text-green-700">Lesson will be published with the course</span>
                </div>
              </div>
            </div>
          </div>
          
          <div v-else class="text-center py-6">
            <p class="text-gray-600">You need to add at least one lesson to your course</p>
            <button 
              @click="addNewLesson" 
              class="mt-4 bg-accent hover:bg-highlight text-white px-4 py-2 rounded-md"
            >
              Add First Lesson
            </button>
          </div>
        </div>
        
        
        <div class="mt-6 flex justify-end">
          <button 
            @click="saveCourse"
            :disabled="isSaving"
            class="flex items-center justify-center bg-accent hover:bg-highlight disabled:opacity-70 disabled:cursor-not-allowed text-white font-medium py-2 px-4 rounded transition-colors"
          >
            <svg v-if="isSaving" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            {{ isSaving ? 'Creating...' : 'Create Course' }}
          </button>

          <button 
              @click="addNewLesson"
              class="text-sm flex items-center ml-5 bg-accent hover:bg-highlight text-white px-3 py-1 rounded-md"
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
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import Sidebar from '@/Components/dashboard/Sidebar.vue'

const links = [
  { name: 'Dashboard', href: '/dashboard' },
  { name: 'Courses', href: '/dashboard/all-courses' },
]

const user = ref(null)
const course = ref({
  title: '',
  description: '',
  status: 'published',
})
const lessons = ref([
  {
    title: '',
    content: '',
    status: 'published',
    order: 1
  }
])
const errors = ref({})
const isSaving = ref(false)

const navigateTo = (path) => {
  window.location = path
}

const addNewLesson = () => {
  lessons.value.push({
    title: '',
    content: '',
    status: 'published',
    order: lessons.value.length + 1
  })
}

const removeLesson = (index) => {
  if (lessons.value.length > 1) {
    lessons.value.splice(index, 1)
    
    // Update order of remaining lessons
    lessons.value.forEach((lesson, idx) => {
      lesson.order = idx + 1
    })
  }
}

const saveCourse = async () => {
  const validationErrors = {}
  
  if (!course.value.title) {
    validationErrors.title = ['Course title is required']
  }
  
  if (!course.value.description) {
    validationErrors.description = ['Course description is required']
  }
  
  if (lessons.value.length === 0) {
    validationErrors.lessons = ['At least one lesson is required']
  } else {
    lessons.value.forEach((lesson, index) => {
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
    
    course.value.status = 'published' 
    
    const courseData = {
      ...course.value,
      lessons: lessons.value.map((lesson, index) => ({
        title: lesson.title,
        content: lesson.content,
        status: 'published',
        order: index + 1
      }))
    }
    
    const response = await axios.post('/api/teacher/courses', courseData)
    
    if (response.data.id) {
      navigateTo(`/dashboard/courses/`)
    }
  } catch (error) {
    console.error('Failed to create course:', error)
    
    if (error.response && error.response.data && error.response.data.errors) {
      errors.value = error.response.data.errors
    } else {
      errors.value = { general: ['An error occurred while creating the course'] }
    }
    
    window.scrollTo({ top: 0, behavior: 'smooth' })
  } finally {
    isSaving.value = false
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
})
</script>

<template>
  <div>
    <Sidebar :links="links" :user="user"/>
    <div class="md:pl-64">
      <div class="container mx-auto py-8 px-4">
        <div class="mb-8 flex flex-col md:flex-row md:justify-between md:items-start">
          <div>
            <h2 class="text-2xl text-left md:text-3xl font-bold text-accent">Welcome, {{ user?.name || 'Teacher' }}!</h2>
            <p class="text-gray-600 mt-1">Manage your courses and track student progress</p>
          </div>
          
          <div class="mt-4 md:mt-0 flex gap-3">
            <button 
              @click="navigateTo('/dashboard/courses/create')" 
              class="flex items-center justify-center bg-accent hover:bg-highlight text-white font-medium py-2 px-3 rounded text-sm transition-colors"
            >
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
              </svg>
              New Course
            </button>
            
            <button 
              @click="navigateTo('/dashboard/courses')" 
              class="flex items-center justify-center bg-gray-100 hover:bg-gray-200 text-gray-800 font-medium py-2 px-3 rounded text-sm transition-colors"
            >
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
              </svg>
              All Courses
            </button>
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
          <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
              <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                </svg>
              </div>
              <div class="ml-4">
                <h2 class="font-semibold text-gray-800">Total Courses</h2>
                <div class="text-2xl font-bold text-gray-900">{{ teacherCourses.length }}</div>
              </div>
            </div>
          </div>

          <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
              <div class="p-3 rounded-full bg-green-100 text-green-600">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
              </div>
              <div class="ml-4">
                <h2 class="font-semibold text-gray-800">Unique Students</h2>
                <div class="text-2xl font-bold text-gray-900">{{ uniqueStudentCount }}</div>

              </div>
            </div>
          </div>

          <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
              <div class="p-3 rounded-full bg-purple-100 text-purple-600">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                </svg>
              </div>
              <div class="ml-4">
                <h2 class="font-semibold text-gray-800">Published Courses</h2>
                <div class="text-2xl font-bold text-gray-900">{{ publishedCoursesCount }}</div>
              </div>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-lg shadow-md overflow-hidden mb-8">
          <div class="p-6 border-b border-gray-200 flex justify-between items-center">
            <h2 class="text-xl font-semibold text-gray-800">My Courses</h2>
            <a href="/dashboard/courses" class="text-accent hover:text-highlight text-sm font-medium">View all</a>
          </div>

          <div v-if="isLoading" class="flex justify-center py-8">
            <svg class="animate-spin h-8 w-8 text-accent" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
          </div>

          <div v-else-if="teacherCourses.length === 0" class="p-8 text-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            <h3 class="mt-4 text-lg font-medium text-gray-800">No courses yet</h3>
            <p class="mt-2 text-gray-600">Get started by creating your first course</p>
            <button 
              @click="navigateTo('/dashboard/courses/create')"
              class="mt-4 bg-accent hover:bg-highlight text-white font-medium py-2 px-4 rounded transition-colors"
            >
              Create Course
            </button>
          </div>

          <div v-else class="divide-y divide-gray-200">
            <div v-for="course in recentCourses" :key="course.id" class="p-6 hover:bg-gray-50 transition-colors">
              <div class="flex justify-between items-center">
                <div>
                  <h3 class="text-lg font-semibold text-gray-800">{{ course.title }}</h3>
                  <p class="text-sm text-gray-600 mt-1 line-clamp-1">{{ course.description }}</p>
                  <div class="flex items-center gap-4 mt-2">
                    <span class="inline-flex items-center text-sm text-gray-500">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                      </svg>
                      {{ course.lessons_count || 0 }} Lessons
                    </span>
                    <span class="inline-flex items-center text-sm text-gray-500">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                      </svg>
                      {{ course.students_count || 0 }} Students
                    </span>
                    <span 
                      class="px-2 py-1 rounded text-xs font-medium bg-green-100 text-green-800"
                    >
                      Published
                    </span>
                  </div>
                </div>
                <button 
                  @click="navigateTo(`/dashboard/courses/${course.id}`)"
                  class="flex items-center text-sm font-medium text-accent hover:text-highlight"
                >
                  View
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                  </svg>
                </button>
              </div>
            </div>
            
            <div v-if="teacherCourses.length > 3" class="p-4 text-center">
              <a href="/dashboard/courses" class="text-accent hover:text-highlight font-medium">
                View all {{ teacherCourses.length }} courses
              </a>
            </div>
          </div>
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
  { name: 'Courses', href: '/dashboard/courses' },
]

const user = ref(null)
const teacherCourses = ref([])
const isLoading = ref(true)
const recentCourses = computed(() => {
  return teacherCourses.value.slice(0, 3)
})

const publishedCoursesCount = computed(() => {
  return teacherCourses.value.length 
})

const totalEnrollments = computed(() => {
  return teacherCourses.value.reduce((sum, course) => sum + (course.students_count || 0), 0)
})
const navigateTo = (path) => {
  window.location = path
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

const uniqueStudentCount = ref(0)
const fetchTeacherCourses = async () => {
  try {
    isLoading.value = true
    const response = await axios.get('/api/teacher/courses')
    teacherCourses.value = response.data.courses
    uniqueStudentCount.value = response.data.unique_student_count
  } catch (error) {
    console.error('Failed to fetch teacher courses:', error)
    teacherCourses.value = []
    uniqueStudentCount.value = 0
  } finally {
    isLoading.value = false
  }
}

onMounted(() => {
  if (window.Laravel && window.Laravel.user) {
    user.value = window.Laravel.user
  } else {
    fetchUserData()
  }
  
  fetchTeacherCourses()
})
</script>

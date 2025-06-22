<template>
  <div>
    <Sidebar :links="links" :user="user"/>
    <div class="md:pl-64">
      <div class="container mx-auto py-8 px-4">
        <div v-if="isLoading" class="flex justify-center py-8">
          <svg class="animate-spin h-8 w-8 text-accent" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
        </div>

        <template v-else-if="course">
          <div class="mb-8">
            <div class="flex items-center mb-4">
              <button @click="goBack" class="text-gray-600 hover:text-accent mr-3">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M9.707 14.707a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 1.414L7.414 9H15a1 1 0 110 2H7.414l2.293 2.293a1 1 0 010 1.414z" clip-rule="evenodd" />
                </svg>
              </button>
              <h1 class="text-2xl md:text-3xl font-bold text-gray-800">{{ course.title }}</h1>
            </div>
            <p class="text-gray-600 mt-1">{{ course.description }}</p>
            
            <div class="mt-6">
              <div class="flex justify-between items-center mb-1">
                <span class="text-sm text-gray-600">Your Progress</span>
                <span class="text-sm font-medium text-accent">{{ progressPercentage }}%</span>
              </div>
              <div class="w-full bg-gray-200 rounded-full h-2.5">
                <div 
                  class="bg-accent h-2.5 rounded-full" 
                  :style="`width: ${progressPercentage}%`"
                ></div>
              </div>
            </div>
          </div>

          <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="p-6 border-b border-gray-200">
              <h2 class="text-xl font-semibold text-gray-800">
                Course Content
              </h2>
              <p class="text-gray-600 text-sm mt-1">
                {{ course.lessons_count || course.lessons.length }} lessons
              </p>
            </div>
            
            <ul class="divide-y divide-gray-200">
              <li v-for="lesson in course.lessons" :key="lesson.id" class="p-5 hover:bg-gray-50">
                <button
                  @click="openLesson(lesson)"
                  class="w-full flex items-center justify-between text-left"
                >
                  <div class="flex items-center">
                    <div class="mr-4">
                      <div v-if="isLessonCompleted(lesson)" class="h-6 w-6 rounded-full bg-green-100 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-green-600" viewBox="0 0 20 20" fill="currentColor">
                          <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                        </svg>
                      </div>
                      <div v-else class="h-6 w-6 rounded-full border-2 border-gray-300"></div>
                    </div>
                    
                    <div>
                      <h3 class="font-medium text-gray-800">{{ lesson.title }}</h3>
                      <p class="text-sm text-gray-600 line-clamp-1">{{ lesson.description }}</p>
                    </div>
                  </div>
                  
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                  </svg>
                </button>
              </li>
            </ul>
            
            <div v-if="course.lessons.length === 0" class="p-8 text-center">
              <p class="text-gray-600">No lessons available for this course yet.</p>
            </div>
          </div>
        </template>

        <div v-else-if="error" class="bg-white rounded-lg shadow-md p-8 text-center">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <h3 class="mt-4 text-lg font-medium text-gray-800">Error Loading Course</h3>
          <p class="mt-2 text-gray-600">{{ error }}</p>
          <AppButton variant="primary" class="mt-4" @click="fetchCourseDetails">Try Again</AppButton>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import Sidebar from '@/Components/dashboard/Sidebar.vue'
import AppButton from '@/Components/common/Button.vue'
import { ref, computed, onMounted } from 'vue'

const links = [
  { name: 'Dashboard', href: '/dashboard' },
  { name: 'My Courses', href: '/dashboard/my-courses' },
  { name: 'Explore', href: '/dashboard/explore' }
]

const user = ref(null)
const course = ref(null)
const isLoading = ref(true)
const error = ref(null)

const courseId = window.location.pathname.split('/').pop()

const progressPercentage = computed(() => {
  if (!course.value || !course.value.lessons || course.value.lessons.length === 0) return 0
  
  const completedCount = course.value.lessons.filter(lesson => 
    lesson.progress && lesson.progress.completed
  ).length
  
  return Math.round((completedCount / course.value.lessons.length) * 100)
})

const fetchCourseDetails = async () => {
  try {
    isLoading.value = true
    error.value = null
    const response = await axios.get(`/api/student/courses/${courseId}`)
    course.value = response.data
  } catch (err) {
    console.error('Failed to fetch course:', err)
    error.value = err.response?.data?.error || 'Failed to load course details'
  } finally {
    isLoading.value = false
  }
}

const isLessonCompleted = (lesson) => {
  return lesson.progress && lesson.progress.completed
}

const openLesson = (lesson) => {
  window.location.href = `/dashboard/lessons/${lesson.id}`
}

const goBack = () => {
  window.history.back()
}

onMounted(() => {
  if (window.Laravel && window.Laravel.user) {
    user.value = window.Laravel.user
  }
  
  fetchCourseDetails()
})
</script>

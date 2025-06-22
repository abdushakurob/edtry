<template>
  <div>
    <Sidebar :links="links" :user="user"/>
    <div class="md:pl-64">
      <div class="container mx-auto py-8 px-4">
        <div class="mb-8 flex flex-col md:flex-row md:justify-between md:items-center">
          <div>
            <h1 class="text-2xl md:text-3xl font-bold text-gray-800">Hi, {{ user?.name || 'Student' }}!</h1>
            <p class="text-gray-600 mt-1">Welcome back to your learning journey</p>
          </div>
          <div class="mt-3 md:mt-0">
            <AppButton variant="primary" @click="$event => window.location.href='/dashboard/explore'">
              Explore new courses
              <template #icon>
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                </svg>
              </template>
            </AppButton>
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
          <StatCard 
            title="Enrolled Courses" 
            :value="enrolledCoursesCount" 
            color="blue"
          >
            <template #icon>
              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
              </svg>
            </template>
          </StatCard>
          
          <StatCard 
            title="Completed Courses" 
            :value="completedCoursesCount" 
            color="green"
          >
            <template #icon>
              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </template>
          </StatCard>
          
          <StatCard 
            title="Overall Progress" 
            :value="averageProgress" 
            suffix="%"
            color="purple"
          >
            <template #icon>
              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 8v8m-4-5v5m-4-2v2m-2 4h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
              </svg>
            </template>
          </StatCard>
        </div>

        <div class="mb-8">
          <div class="flex justify-between items-center mb-6">
            <h2 class="text-xl font-semibold text-gray-800">My Courses</h2>
            <a href="/dashboard/my-courses" class="text-accent hover:text-highlight text-sm font-medium">View all</a>
          </div>
          
          <div v-if="isLoading" class="flex justify-center py-8">
            <svg class="animate-spin h-8 w-8 text-accent" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
          </div>
          
          <div v-else-if="enrolledCourses.length === 0" class="bg-white rounded-lg shadow-md p-8 text-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            <h3 class="mt-4 text-lg font-medium text-gray-800">No courses enrolled yet</h3>
            <p class="mt-2 text-gray-600">Explore available courses and start learning today.</p>
            <AppButton variant="primary" class="mt-4" @click="$event => window.location.href='/dashboard/explore'">Browse courses</AppButton>
          </div>
          
          <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <CourseCard 
              v-for="course in enrolledCourses" 
              :key="course.id" 
              :course="course"
            />
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import Sidebar from '@/Components/dashboard/Sidebar.vue'
import AppButton from '@/Components/common/Button.vue'
import StatCard from '@/Components/student/StatCard.vue'
import CourseCard from '@/Components/student/CourseCard.vue'
import { ref, computed, onMounted } from 'vue'

const links = [
  { name: 'Dashboard', href: '/dashboard' },
  { name: 'My Courses', href: '/dashboard/my-courses' },
  { name: 'Explore', href: '/dashboard/explore' }
]

const user = ref(null)
const enrolledCourses = ref([])
const isLoading = ref(true)

const enrolledCoursesCount = computed(() => enrolledCourses.value.length)

const completedCoursesCount = computed(() => 
  enrolledCourses.value.filter(course => 
    course.pivot && course.pivot.progress_percentage === 100
  ).length
)

const averageProgress = computed(() => {
  if (enrolledCourses.value.length === 0) return 0
  
  const totalProgress = enrolledCourses.value.reduce((sum, course) => 
    sum + (course.pivot ? course.pivot.progress_percentage : 0), 0
  )
  
  return Math.round(totalProgress / enrolledCourses.value.length)
})

const fetchEnrolledCourses = async () => {
  try {
    isLoading.value = true
    const response = await axios.get('/api/student/courses')
    enrolledCourses.value = response.data
  } catch (error) {
    console.error('Failed to fetch courses:', error)
    enrolledCourses.value = []
  } finally {
    isLoading.value = false
  }
}

onMounted(() => {
  if (window.Laravel && window.Laravel.user) {
    user.value = window.Laravel.user
  }
  
  fetchEnrolledCourses()
})


</script>
<style scoped>
</style>

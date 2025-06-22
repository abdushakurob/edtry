<template>
  <div>
    <Sidebar :links="links" :user="user"/>
    <div class="md:pl-64">
      <div class="container mx-auto py-8 px-4">
        <div class="mb-8">
          <h1 class="text-2xl md:text-3xl font-bold text-gray-800">My Courses</h1>
          <p class="text-gray-600 mt-1">All your enrolled courses in one place</p>
        </div>

        <div class="mb-6 flex flex-col md:flex-row md:justify-between md:items-center gap-4">
          <div class="flex items-center">
            <div class="relative">
              <input 
                type="text" 
                v-model="searchQuery" 
                placeholder="Search your courses..." 
                class="w-full md:w-80 px-4 py-2 pl-10 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-accent"
              >
              <!-- <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400 absolute left-3 top-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
              </svg> -->
            </div>
          </div>

          <div class="flex gap-3">
            <select 
              v-model="sortOption" 
              class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-accent bg-white"
            >
              <option value="name">Course Name</option>
              <option value="recent">Recently Updated</option>
              <option value="progress">Progress</option>
            </select>
          </div>
        </div>
        
        <div v-if="isLoading" class="flex justify-center py-8">
          <svg class="animate-spin h-8 w-8 text-accent" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
        </div>
        
        <div v-else-if="filteredCourses.length === 0" class="bg-white rounded-lg shadow-md p-8 text-center">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
          </svg>
          <h3 class="mt-4 text-lg font-medium text-gray-800">
            {{ searchQuery ? 'No matching courses found' : 'No courses enrolled yet' }}
          </h3>
          <p class="mt-2 text-gray-600">
            {{ searchQuery ? 'Try adjusting your search terms' : 'Explore available courses and start learning today.' }}
          </p>
          <AppButton v-if="!searchQuery" variant="primary" class="mt-4" @click="$event => window.location.href='/dashboard/explore'">
            Browse courses
          </AppButton>
        </div>
        
        <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <CourseCard 
            v-for="course in filteredCourses" 
            :key="course.id" 
            :course="course"
          />
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import Sidebar from '@/Components/dashboard/Sidebar.vue'
import AppButton from '@/Components/common/Button.vue'
import CourseCard from '@/Components/student/CourseCard.vue'
import { ref, computed, onMounted, watch } from 'vue'

const links = [
  { name: 'Dashboard', href: '/dashboard' },
  { name: 'My Courses', href: '/dashboard/my-courses' },
  { name: 'Explore', href: '/dashboard/explore' }
]

// Initialize data
const user = ref(null)
const enrolledCourses = ref([])
const isLoading = ref(true)
const searchQuery = ref('')
const sortOption = ref('name')

const filteredCourses = computed(() => {
  let filtered = enrolledCourses.value
  
  if (searchQuery.value.trim()) {
    const query = searchQuery.value.trim().toLowerCase()
    filtered = filtered.filter(course => 
      course.title.toLowerCase().includes(query) || 
      course.description.toLowerCase().includes(query)
    )
  }
  
  return [...filtered].sort((a, b) => {
    if (sortOption.value === 'name') {
      return a.title.localeCompare(b.title)
    } else if (sortOption.value === 'recent') {
      return new Date(b.updated_at || b.created_at) - new Date(a.updated_at || a.created_at)
    } else if (sortOption.value === 'progress') {
      return b.pivot.progress_percentage - a.pivot.progress_percentage
    }
    return 0
  })
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

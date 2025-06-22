<template>
  <div>
    <Sidebar :links="links" :user="user"/>
    <div class="md:pl-64">
      <div class="container mx-auto py-8 px-4">
        <div class="mb-6 flex flex-col md:flex-row md:justify-between md:items-center">
          <div>
            <h1 class="text-2xl md:text-3xl font-bold text-gray-800">My Courses</h1>
            <p class="text-gray-600 mt-1">Manage and organize your courses</p>
          </div>
          
          <div class="mt-4 md:mt-0">
            <button 
              @click="navigateTo('/dashboard/courses/create')" 
              class="flex items-center justify-center bg-accent hover:bg-highlight text-white font-medium py-2 px-4 rounded transition-colors"
            >
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
              </svg>
              Create New Course
            </button>
          </div>
        </div>
        
        <div class="bg-white rounded-lg shadow-sm p-4 mb-6 flex flex-wrap gap-4 items-center">
          <div class="flex-grow">
            <input 
              type="text" 
              v-model="searchQuery"
              placeholder="Search courses..."
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-accent"
            />
          </div>
          
          <div class="flex items-center gap-2">
            <label class="text-sm text-gray-700">Status:</label>
            <select 
              v-model="statusFilter"
              class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-accent"
            >
              <option value="all">All</option>
              <option value="published">Published</option>
              <option value="draft">Draft</option>
            </select>
          </div>
          
          <div class="flex items-center gap-2">
            <label class="text-sm text-gray-700">Sort by:</label>
            <select 
              v-model="sortBy"
              class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-accent"
            >
              <option value="newest">Newest</option>
              <option value="oldest">Oldest</option>
              <option value="title">Title (A-Z)</option>
              <option value="students">Most Students</option>
            </select>
          </div>
        </div>
        
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
          <div v-if="isLoading" class="flex justify-center py-16">
            <svg class="animate-spin h-8 w-8 text-accent" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
          </div>
          
          <div v-else-if="filteredCourses.length === 0" class="p-12 text-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            <h3 class="mt-4 text-lg font-medium text-gray-800">No courses found</h3>
            <p v-if="hasFiltersApplied" class="mt-2 text-gray-600">Try adjusting your search or filters</p>
            <p v-else class="mt-2 text-gray-600">Get started by creating your first course</p>
            
            <button 
              v-if="!hasFiltersApplied"
              @click="navigateTo('/dashboard/courses/create')"
              class="mt-4 bg-accent hover:bg-highlight text-white font-medium py-2 px-4 rounded transition-colors"
            >
              Create Course
            </button>
            
            <button 
              v-else
              @click="clearFilters"
              class="mt-4 bg-gray-100 hover:bg-gray-200 text-gray-800 font-medium py-2 px-4 rounded transition-colors"
            >
              Clear Filters
            </button>
          </div>
          
          <div v-else>
            <div class="hidden md:flex bg-gray-50 px-6 py-3 text-sm font-medium text-gray-500 border-b border-gray-200">
              <div class="w-6/12">Course</div>
              <div class="w-2/12 text-center">Lessons</div>
              <div class="w-2/12 text-center">Students</div>
              <div class="w-2/12 text-right">Actions</div>
            </div>
            
            <div class="divide-y divide-gray-200">
              <div v-for="course in filteredCourses" :key="course.id" class="hover:bg-gray-50 transition-colors">
                <div class="md:hidden p-4">
                  <h3 class="text-lg font-semibold text-gray-800">{{ course.title }}</h3>
                  <p class="text-sm text-gray-600 mt-1 line-clamp-2">{{ course.description }}</p>
                  <div class="flex flex-wrap items-center gap-3 mt-3">
                    <span 
                      class="px-2 py-1 rounded text-xs font-medium bg-green-100 text-green-800"
                    >
                      Published
                    </span>
                    <span class="text-sm text-gray-500">
                      {{ course.lessons_count || 0 }} Lessons
                    </span>
                    <span class="text-sm text-gray-500">
                      {{ course.students_count || 0 }} Students
                    </span>
                  </div>
                  <div class="mt-4 flex gap-2">
                    <button 
                      @click="$event => window.location.href = `/dashboard/courses/${course.id}`"
                      class="px-3 py-1 text-xs bg-accent text-white rounded hover:bg-highlight transition-colors"
                    >
                      Edit
                    </button>
                    <button 
                      @click="confirmDeleteCourse(course)"
                      class="px-3 py-1 text-xs bg-red-100 text-red-800 rounded hover:bg-red-200 transition-colors"
                    >
                      Delete
                    </button>
                  </div>
                </div>
                
                <div class="hidden md:flex px-6 py-4 items-center">
                  <div class="w-6/12">
                    <div class="flex items-start">
                      <div>
                        <h3 class="text-base font-semibold text-gray-800">{{ course.title }}</h3>
                        <p class="text-sm text-gray-600 line-clamp-1">{{ course.description }}</p>                    <span 
                      class="mt-1 inline-block px-2 py-1 rounded text-xs font-medium bg-green-100 text-green-800"
                    >
                      Published
                    </span>
                      </div>
                    </div>
                  </div>
                  <div class="w-2/12 text-center">
                    <span class="text-sm font-medium">{{ course.lessons_count || 0 }}</span>
                  </div>
                  <div class="w-2/12 text-center">
                    <span class="text-sm font-medium">{{ course.students_count || 0 }}</span>
                  </div>
                  <div class="w-2/12 text-right flex justify-end gap-2">
                    <button 
                      @click="navigateTo(`/dashboard/courses/${course.id}`)"
                      class="p-2 text-accent hover:text-highlight transition-colors"
                      title="Edit course"
                    >
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                      </svg>
                    </button>
                    <button 
                      @click="confirmDeleteCourse(course)"
                      class="p-2 text-red-500 hover:text-red-700 transition-colors"
                      title="Delete course"
                    >
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                      </svg>
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    
    
    <div v-if="showDeleteModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 px-4">
      <div class="bg-white rounded-lg shadow-xl max-w-md w-full p-6">
        <h3 class="text-lg font-semibold text-gray-800">Delete Course</h3>
        <p class="mt-2 text-gray-600">
          Are you sure you want to delete the course "{{ courseToDelete?.title }}"? This action cannot be undone.
        </p>
        <div class="mt-6 flex justify-end gap-3">
          <button 
            @click="showDeleteModal = false"
            class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-800 rounded font-medium transition-colors"
          >
            Cancel
          </button>
          <button 
            @click="deleteCourse"
            class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded font-medium transition-colors"
          >
            Delete
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
  { name: 'Courses', href: '/dashboard/courses' },
]

const user = ref(null)
const courses = ref([])
const isLoading = ref(true)
const searchQuery = ref('')
const statusFilter = ref('all')
const sortBy = ref('newest')
const showDeleteModal = ref(false)
const courseToDelete = ref(null)
const filteredCourses = computed(() => {
  let filtered = courses.value
  
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase()
    filtered = filtered.filter(course => 
      course.title.toLowerCase().includes(query) || 
      course.description.toLowerCase().includes(query)
    )
  }
  
  if (statusFilter.value !== 'all') {
    filtered = filtered.filter(course => course.status === statusFilter.value)
  }
  return filtered.sort((a, b) => {
    switch (sortBy.value) {
      case 'newest':
        return new Date(b.created_at) - new Date(a.created_at)
      case 'oldest':
        return new Date(a.created_at) - new Date(b.created_at)
      case 'title':
        return a.title.localeCompare(b.title)
      case 'students':
        return (b.students_count || 0) - (a.students_count || 0)
      default:
        return 0
    }
  })
})

const hasFiltersApplied = computed(() => {
  return searchQuery.value !== '' || statusFilter.value !== 'all'
})

const navigateTo = (path) => {
  window.location = path
}

const fetchTeacherCourses = async () => {
  try {
    isLoading.value = true
    const response = await axios.get('/api/teacher/courses')
    courses.value = response.data.courses
  } catch (error) {
    console.error('Failed to fetch teacher courses:', error)
    courses.value = []
  } finally {
    isLoading.value = false
  }
}

const clearFilters = () => {
  searchQuery.value = ''
  statusFilter.value = 'all'
}

const confirmDeleteCourse = (course) => {
  courseToDelete.value = course
  showDeleteModal.value = true
}

const deleteCourse = async () => {
  if (!courseToDelete.value) return
  
  try {
    const response = await axios.delete(`/api/teacher/courses/${courseToDelete.value.id}`)
    if (response.status === 200) {
      courses.value = courses.value.filter(c => c.id !== courseToDelete.value.id)
      showDeleteModal.value = false
      courseToDelete.value = null
    }
  } catch (error) {
    console.error('Failed to delete course:', error)
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
  
  fetchTeacherCourses()
})
</script>

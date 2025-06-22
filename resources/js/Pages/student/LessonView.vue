<template>
  <div>
    <Sidebar :links="links" :user="user"/>
    <div class="fixed bottom-20 right-6 z-50">
      <AiChat 
        buttonText="Ask AI" 
        :loading="isChatLoading"
        :timeout="100000"
        @sendChat="handleChatMessage" 
        @error="handleChatError"
      />
    </div>
    <div class="md:pl-64">
      <div class="container mx-auto py-8 px-4">
        <div v-if="isLoading" class="flex justify-center py-8">
          <svg class="animate-spin h-8 w-8 text-accent" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
        </div>

        <template v-else-if="lesson">
          <div class="bg-white rounded-lg shadow-md p-4 mb-6 flex flex-col md:flex-row md:items-center md:justify-between">
            <div class="flex items-center">
              <button @click="goBackToCourse" class="text-gray-600 hover:text-accent mr-3">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M9.707 14.707a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 1.414L7.414 9H15a1 1 0 110 2H7.414l2.293 2.293a1 1 0 010 1.414z" clip-rule="evenodd" />
                </svg>
              </button>
              <div>
                <h2 class="text-xl font-bold text-gray-800">{{ course.title }}</h2>
                <p class="text-sm text-gray-600">Lesson {{ lessonIndex + 1 }} of {{ totalLessons }}</p>
              </div>
            </div>
            
            <div class="mt-3 md:mt-0 flex space-x-2">
              <button 
                v-if="prevLesson"
                @click="navigateToLesson(prevLesson.id)" 
                class="flex items-center px-3 py-1 border border-gray-300 rounded-md text-sm text-gray-700 hover:bg-gray-50"
              >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                </svg>
                Previous
              </button>
              
              <button 
                v-if="nextLesson"
                @click="navigateToLesson(nextLesson.id)" 
                class="flex items-center px-3 py-1 bg-accent text-white rounded-md text-sm hover:bg-highlight"
              >
                Next
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-1" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                </svg>
              </button>
            </div>
          </div>
          
          <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="p-6 border-b border-gray-200">
              <h1 class="text-2xl font-bold text-gray-800">{{ lesson.title }}</h1>
            </div>
            <div class="p-6">
              <div class="prose max-w-none whitespace-pre-wrap">{{ lesson.content }}</div>
            </div>
          </div>
          
          <div class="mt-6 flex justify-between items-center">
            <div>
              <span v-if="lessonCompleted" class="inline-flex items-center text-sm text-green-700">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
                Completed
              </span>
            </div>
            
            <div>
              <AppButton 
                v-if="!lessonCompleted" 
                variant="primary" 
                @click="markLessonAsComplete"
                :disabled="isMarkingComplete"
              >
                <span v-if="isMarkingComplete">Marking as complete...</span>
                <span v-else>Mark as Complete</span>
              </AppButton>
            </div>
          </div>
        </template>

        <div v-else-if="error" class="bg-white rounded-lg shadow-md p-8 text-center">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <h3 class="mt-4 text-lg font-medium text-gray-800">Error Loading Lesson</h3>
          <p class="mt-2 text-gray-600">{{ error }}</p>
          <AppButton variant="primary" class="mt-4" @click="fetchLessonData">Try Again</AppButton>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import Sidebar from '@/Components/dashboard/Sidebar.vue'
import AppButton from '@/Components/common/Button.vue'
import AiChat from '@/Components/common/AiChat.vue'
import { ref, computed, onMounted } from 'vue'

const links = [
  { name: 'Dashboard', href: '/dashboard' },
  { name: 'My Courses', href: '/dashboard/my-courses' },
  { name: 'Explore', href: '/dashboard/explore' }
]

const user = ref(null)
const lesson = ref(null)
const course = ref(null)
const isLoading = ref(true)
const error = ref(null)
const nextLesson = ref(null)
const prevLesson = ref(null)
const lessonCompleted = ref(false)
const isMarkingComplete = ref(false)
const isChatLoading = ref(false)

const lessonId = window.location.pathname.split('/').pop()
const lessonIndex = computed(() => {
  if (!lesson.value || !course.value || !course.value.lessons) return 0
  return course.value.lessons.findIndex(l => l.id === lesson.value.id)
})

const totalLessons = computed(() => {
  if (!course.value || !course.value.lessons) return 0
  return course.value.lessons.length
})

const fetchLessonData = async () => {
  try {
    isLoading.value = true
    error.value = null
    
    const lessonResponse = await axios.get(`/api/student/lessons/${lessonId}`)
    lesson.value = lessonResponse.data
    
    const courseResponse = await axios.get(`/api/student/courses/${lesson.value.course_id}`)
    course.value = courseResponse.data
    
    const currentIndex = course.value.lessons.findIndex(l => l.id === lesson.value.id)
    if (currentIndex > 0) {
      prevLesson.value = course.value.lessons[currentIndex - 1]
    }
    
    if (currentIndex < course.value.lessons.length - 1) {
      nextLesson.value = course.value.lessons[currentIndex + 1]
    }
    
    lessonCompleted.value = lesson.value.progress && lesson.value.progress.completed
    
    startTimeTracking()
  } catch (err) {
    console.error('Failed to fetch lesson:', err)
    error.value = err.response?.data?.error || 'Failed to load lesson'
  } finally {
    isLoading.value = false
  }
}

let startTime = null
let timeSpent = 0

//track time
const startTimeTracking = () => {
  startTime = new Date()
  window.addEventListener('beforeunload', updateTimeSpent)
}

const updateTimeSpent = () => {
  if (!startTime) return
  
  const endTime = new Date()
  timeSpent = Math.round((endTime - startTime) / 1000) 
}

const markLessonAsComplete = async () => {
  try {
    isMarkingComplete.value = true
    updateTimeSpent() 
    
    const response = await axios.post(`/api/student/lessons/${lessonId}/complete`, {
      timeSpent: timeSpent
    })
    
    lessonCompleted.value = true
    
    if (nextLesson.value && window.confirm('Lesson completed! Would you like to proceed to the next lesson?')) {
      navigateToLesson(nextLesson.value.id)
    }
  } catch (error) {
    console.error('Failed to mark lesson as complete:', error)
    alert('Failed to mark lesson as complete. Please try again.')
  } finally {
    isMarkingComplete.value = false
  }
}

const handleChatMessage = async (message, callback) => {
  try {
    isChatLoading.value = true
    const response = await axios.post('/ask/ai', {
      query: message,
      lesson_id: parseInt(lessonId, 10),
      course_id: parseInt(course.value.id, 10)
    })
    
    let content = ''
    if (response.data.status === 'success' && response.data.answer?.choices?.[0]?.message?.content) {
      content = response.data.answer.choices[0].message.content
    } else if (response.data.status === 'success' && response.data.answer?.content) {
      content = response.data.answer.content
    } else {
      throw new Error(response.data.message || 'Failed to get response')
    }

    
    content = content
      // Remove thinking process of Deepseek LLM
      .replace(/<think>[\s\S]*?<\/think>/, '')
      // Convert markdown bold to HTML bold tags
      .replace(/\*\*(.*?)\*\*/g, '<bold>$1</bold>')
      .replace(/\*(.*?)\*/g, '<bold>$1</bold>')
      .trim()
    callback(content)
  } catch (error) {
    console.error('Chat error:', error)
    callback({ error: 'Sorry, I encountered an error. Please try again.' })
  } finally {
    isChatLoading.value = false
  }
}

const handleChatError = (error) => {
  console.error('Chat component error:', error)
}

const navigateToLesson = (id) => {
  window.location.href = `/dashboard/lessons/${id}`
}

const goBackToCourse = () => {
  window.location.href = `/dashboard/courses/${course.value.id}`
}

onMounted(() => {
  if (window.Laravel && window.Laravel.user) {
    user.value = window.Laravel.user
  }
  
  fetchLessonData()
})
</script>

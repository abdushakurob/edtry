<template>
  <div class="bg-white rounded-lg shadow-md overflow-hidden border border-gray-100 hover:shadow-lg transition-shadow duration-300">
    <div class="p-5">
      <h3 class="text-lg font-semibold text-gray-800 mb-2">{{ course.title }}</h3>
      <p class="text-sm text-gray-600 mb-4 line-clamp-2">{{ course.description }}</p>
      
      <div class="flex flex-col gap-2">
        <div class="flex justify-between items-center text-sm">
          <span class="text-accent font-medium">
            {{ course.lessons_count || 0 }} lessons
          </span>
          <span class="bg-accent bg-opacity-10 text-primary px-2 py-1 rounded-full text-xs font-medium">
            {{ course.category }}
          </span>
        </div>
      </div>
    </div>
    <div class="bg-gray-50 px-5 py-3 border-t border-gray-100">
      <button 
        v-if="course.is_enrolled"
        @click="viewCourse"
        class="w-full bg-primary text-amber-600 font-medium py-2 px-4 rounded"
      >
        Enrolled - View Course
      </button>
      <button 
        v-else
        @click="$emit('enroll', course.id)"
        class="w-full bg-accent hover:bg-highlight text-white font-medium py-2 px-4 rounded transition-colors"
      >
        Enroll Now
      </button>
    </div>
  </div>
</template>

<script setup>
const props = defineProps({
  course: {
    type: Object,
    required: true,
  }
})

defineEmits(['enroll'])

const viewCourse = () => {
  window.location.href = `/dashboard/courses/${props.course.id}`
}
</script>

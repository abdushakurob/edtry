<template>
  <div class="bg-white rounded-lg shadow-md overflow-hidden border border-gray-100 hover:shadow-lg transition-shadow duration-300">
    <div class="p-5">
      <h3 class="text-lg font-semibold text-gray-800 mb-2">{{ course.title }}</h3>
      <p class="text-sm text-gray-600 mb-4 line-clamp-2">{{ course.description }}</p>
      
      <div class="flex flex-col gap-2">
        <div class="w-full bg-gray-200 rounded-full h-2.5">
          <div 
            class="bg-accent h-2.5 rounded-full" 
            :style="{ width: `${course.pivot.progress_percentage}%` }"
          ></div>
        </div>
        <div class="flex justify-between items-center text-sm">
          <span class="text-gray-600">Progress: {{ Math.round(course.pivot.progress_percentage) }}%</span>
          <span class="text-accent font-medium">
            {{ course.completed_lessons_count || 0 }}/{{ course.lessons_count || 0 }} lessons
          </span>
        </div>
      </div>
    </div>
    <div class="bg-gray-50 px-5 py-3 border-t border-gray-100">
      <a 
        :href="`/dashboard/courses/${course.id}`" 
        class="text-accent hover:text-highlight text-sm font-medium flex items-center"
      >
        Continue Learning
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-1" viewBox="0 0 20 20" fill="currentColor">
          <path fill-rule="evenodd" d="M12.293 5.293a1 1 0 011.414 0l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-2.293-2.293a1 1 0 010-1.414z" clip-rule="evenodd" />
        </svg>
      </a>
    </div>
  </div>
</template>

<script setup>
defineProps({
  course: {
    type: Object,
    required: true,
  }
})
</script>

<template>
  <div class="bg-white rounded-lg shadow-md p-5 border border-gray-100">
    <div class="flex items-center">
      <div class="rounded-full p-3" :class="`bg-${color}-100`">
        <slot name="icon">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" :class="`text-${color}-600`" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
          </svg>
        </slot>
      </div>
      <div class="ml-4">
        <h3 class="text-gray-500 text-sm font-medium">{{ title }}</h3>
        <div class="flex items-center">
          <span class="text-2xl font-semibold text-gray-800">{{ value }}</span>
          <span v-if="suffix" class="ml-1 text-sm text-gray-600">{{ suffix }}</span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  title: {
    type: String,
    required: true
  },
  value: {
    type: [String, Number],
    required: true
  },
  suffix: {
    type: String,
    default: ''
  },
  color: {
    type: String,
    default: 'blue'
  }
})
</script>

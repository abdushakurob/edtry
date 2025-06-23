<template>
  <div class="flex flex-col h-full">
    <div class="p-5 mb-6">
      <h2 class="text-2xl font-bold text-highlight">Edtry</h2>

    </div>
    <nav class="flex-1 space-y-2 px-3">
      <div 
        v-for="item in links" 
        :key="item.name" 
        class="relative"
      >
        <AppButton
  variant="ghost"
  class="w-full justify-start text-left py-2 px-3 text-sm rounded-md font-medium transition-colors"
  :class="{
    'bg-accent text-black font-semibold border-l-4 border-accent shadow-sm': isActive(item.href),
    'text-gray-700 hover:bg-primary': !isActive(item.href)
  }"
  @click="navigateTo(item.href)"
>
  {{ item.name }}
</AppButton>
      </div>
    </nav>
    <div class="border-t border-gray-200 p-4 mt-auto">
      <div class="flex items-center mb-4 px-2">
        <div class="w-8 h-8 rounded-full bg-accent/20 flex items-center justify-center text-accent font-bold mr-2">
          {{ user?.name?.charAt(0) || 'U' }}
        </div>
        <div>
          <p class="text-sm font-medium">{{ user?.name || 'User' }}</p>
        <p class="text-xs text-gray-500">{{ user?.role }}</p>
        </div>
      </div>
      <AppButton variant="outline" class="w-full justify-center gap-2" @click="$emit('logout')">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
        </svg>
        Logout
      </AppButton>
    </div>
  </div>
</template>

<script setup>
import AppButton from '../common/Button.vue'

const props = defineProps({
  links: {
    type: Array,
    required: true,
    default: () => []
  },
  user: {
    type: Object,
    default: () => ({
      name: 'User'
    })
  }
})

const emit = defineEmits(['logout'])

const navigateTo = (href) => {
  window.location.href = href
}

const isActive = (href) => {
  return window.location.pathname === href
}
</script>

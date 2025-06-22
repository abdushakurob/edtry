<template>
  <div>
    <div class="md:hidden p-4 flex justify-between items-center border-b border-secondary/30 bg-white shadow-sm">
      <h2 class="text-xl font-bold text-highlight">Edtry</h2>
      <button 
        @click="isOpen = !isOpen" 
        class="p-2 rounded-md hover:bg-gray-100 transition-colors duration-200"
      >
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
        </svg>
      </button>
    </div>

    <aside
      class="hidden md:flex w-64 h-screen fixed left-0 top-0 border-r border-gray-200 flex-col bg-white z-30 shadow-sm"
    >
      <SidebarContent :links="links" :user="user" @logout="logout" />
    </aside>


    <transition name="slide">
      <aside
        v-if="isOpen"
        class="md:hidden fixed inset-0 z-40 bg-black/30"
        @click.self="isOpen = false"
      >
        <div class="w-64 h-full bg-white shadow-lg transform transition-transform">
          <div class="p-4 flex justify-between items-center border-b border-gray-200">
            <h1 class="text-xl font-bold text-highlight">Edtry</h1>
            <button 
              @click="isOpen = false"
              class="p-2 rounded-md hover:bg-gray-100 transition-colors duration-200"
            >
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>
          <div class="p-4">
            <SidebarContent :links="links" :user="user" @logout="logout" />
          </div>
        </div>
      </aside>
    </transition>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import SidebarContent from './SidebarContent.vue'

const isOpen = ref(false)

const props = defineProps({
  links: {
    type: Array,
    required: true,
    default: () => []
  },
  user: {
    type: Object,
    default: () => null
  }
})

const logout = () => {
  axios.post('/logout')
    .then(() => {
      window.location.href = '/login'
    })
    .catch(error => {
      console.error('Logout failed:', error)
    })
}
</script>

<style scoped>
.slide-enter-active,
.slide-leave-active {
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}
.slide-enter-from {
  opacity: 0;
}
.slide-enter-from .w-64 {
  transform: translateX(-100%);
}
.slide-enter-to {
  opacity: 1;
}
.slide-enter-to .w-64 {
  transform: translateX(0);
}
.slide-leave-from {
  opacity: 1;
}
.slide-leave-from .w-64 {
  transform: translateX(0);
}
.slide-leave-to {
  opacity: 0;
}
.slide-leave-to .w-64 {
  transform: translateX(-100%);
}

nav .hover\:bg-gray-100:hover {
  background-color: rgba(243, 244, 246, 0.8);
}

nav .border-l-4 {
  transition: all 0.2s ease;
}
</style>

<template>
  <div class="inline-block">
    <button 
      @click="toggleChat"
      :class="`
        ${open ? 'bg-highlight' : 'bg-accent hover:bg-highlight'} 
        text-white font-medium py-2 px-4 rounded-md transition-colors flex items-center gap-2
        shadow-lg hover:shadow-xl
      `"
    >
      <span>{{ buttonText }}</span>
      <svg v-if="loading" class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
      </svg>
    </button>

    <div v-if="open" class="fixed inset-0 bg-primary/50 overflow-y-auto h-full w-full z-50">
  <div class="fixed bottom-25 right-4 opacity-100 p-5 border w-96 shadow-lg rounded-md bg-white">
    <div class="flex flex-col h-96">
      <div class="flex justify-between items-center mb-4">
        <h3 class="text-lg font-medium text-gray-900">AI Assistant</h3>
        <button @click="toggleChat" class="text-gray-400 hover:text-gray-500">
          <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>

      <div class="flex-1 overflow-y-auto mb-4 space-y-4">
        <div v-for="(message, index) in chatHistory" :key="index" 
          :class="`${message.sender === 'ai' ? 'bg-gray-100' : 'bg-accent bg-opacity-10'} p-3 rounded-lg`">
          <p :class="`${message.sender === 'ai' ? 'text-gray-800' : 'text-gray-900'} text-sm`" v-if="!message.hasHtml">
            {{ message.text }}
          </p>
          <p :class="`${message.sender === 'ai' ? 'text-gray-800' : 'text-gray-900'} text-sm ai-message`" v-else v-html="message.text"></p>
        </div>
        <div v-if="thinking" class="bg-gray-100 p-3 rounded-lg">
          <p class="text-gray-800 text-sm">Thinking...</p>
        </div>
      </div>

      <div class="mt-auto">
        <form @submit.prevent="sendMessage" class="flex gap-2">
          <input 
            type="text" 
            v-model="message" 
            placeholder="Type your message..."
            class="flex-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-accent"
          />
          <button 
            type="submit"
            :disabled="!message.trim() || thinking"
            class="bg-accent hover:bg-highlight disabled:opacity-50 disabled:cursor-not-allowed text-white px-4 py-2 rounded-md transition-colors"
          >
            Send
          </button>
        </form>
      </div>
    </div>
  </div>
</div>
  </div>
</template>

<style scoped>
.ai-message :deep(bold) {
  font-weight: 700;
}
</style>

<script setup>
import { ref } from 'vue'

const props = defineProps({
  buttonText: {
    type: String,
    default: 'AI Chat'
  },
  loading: {
    type: Boolean,
    default: false
  },
  timeout: {
    type: Number,
    default: 30000 
  }
})

const emit = defineEmits(['getChat', 'sendChat', 'error'])

const open = ref(false)
const message = ref('')
const thinking = ref(false)
const chatHistory = ref([])
const messageHasHtml = (text) => /<[a-z][\s\S]*>/i.test(text)
const timeoutId = ref(null)

const toggleChat = () => {
  open.value = !open.value
  if (open.value) {
    emit('getChat', handleResponse)
  }
}

const handleTimeout = () => {
  thinking.value = false
  chatHistory.value.push({
    sender: 'ai',
    text: 'Sorry, the request timed out. Please try again.',
    hasHtml: false
  })
  emit('error', { type: 'timeout', message: 'Request timed out' })
}

const clearPendingTimeout = () => {
  if (timeoutId.value) {
    clearTimeout(timeoutId.value)
    timeoutId.value = null
  }
}

const sendMessage = async () => {
  if (!message.value.trim() || thinking.value) return

  const userMessage = message.value.trim()
  chatHistory.value.push({
    sender: 'user',
    text: userMessage,
    hasHtml: false
  })
  
  message.value = ''
  thinking.value = true
  
  // Set timeout
  clearPendingTimeout()
  timeoutId.value = setTimeout(handleTimeout, props.timeout)

  try {
    emit('sendChat', userMessage, handleResponse)
  } catch (error) {
    clearPendingTimeout()
    thinking.value = false
    chatHistory.value.push({
      sender: 'ai',
      text: 'Sorry, an error occurred. Please try again.'
    })
    emit('error', { type: 'error', message: error.message })
  }
}

const handleResponse = (response) => {
  clearPendingTimeout()
  thinking.value = false
  
  if (response?.error) {
    chatHistory.value.push({
      sender: 'ai',
      text: response.error,
      hasHtml: false
    })
    emit('error', { type: 'api', message: response.error })
    return
  }

  if (response) {
    const hasHtml = messageHasHtml(response)
    chatHistory.value.push({
      sender: 'ai',
      text: response,
      hasHtml
    })
  }
}
</script>

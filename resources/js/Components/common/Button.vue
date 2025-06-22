<template>
  <button
    :class="[
      baseClasses,
      variantClasses,
      $attrs.class || ''
    ]"
    :type="type"
    v-bind="$attrs"
    @click="$emit('click')"
  >
    <slot name="icon"></slot>
    <slot></slot>
  </button>
</template>



<script setup>
import { computed } from 'vue'

const props = defineProps({
  variant: {
    type: String,
    default: 'primary',
    validator: (value) => ['primary', 'secondary', 'outline', 'ghost'].includes(value)
  },
  type: {
    type: String,
    default: 'button'
  }
})

defineEmits(['click'])

const baseClasses = 'px-4 py-2 rounded-lg transition-all duration-200 flex items-center gap-2'

const variantClasses = computed(() => {
  switch (props.variant) {
    case 'primary': return 'bg-accent hover:bg-highlight text-white'
    case 'secondary': return 'bg-secondary hover:bg-accent text-gray-800'
    case 'outline': return 'border-2 border-accent hover:bg-accent/10 text-accent'
    case 'ghost': return 'bg-transparent hover:bg-accent/10 text-gray-700'
    default: return ''
  }
})
</script>

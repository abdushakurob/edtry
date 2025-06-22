<template>
    <input
            :type="type"
            :id="id"
            :name="name"
            :value="modelValue"
            @input="$emit('update:modelValue', $event.target.value)"
            :disabled="disabled"
            :placeholder="placeholder"
            :class="inputClass"
            :required ="required"
        >
</template>

<script setup>
defineProps({
    type: {
        type: String,
        default: 'text'
    },
    id: {
        type: String,
        required: true
    },
    name: {
        type: String,
        required: true
    },
    modelValue: {
        type: [String, Number],
        default: ''
    },
    disabled: {
        type: Boolean,
        default: false
    },
    placeholder: {
        type: String,
        default: ''
    },
    required: {
        type: Boolean,
        default: false
    },
    inputClass: {
        type: String,
    }
});

defineEmits(['update:modelValue']);
 
</script>

<style>

</style>
<template>
<div class="flex flex-col items-center justify-center min-h-screen bg-gray-50">
    <div class="w-full max-w-md bg-white p-8 rounded-xl shadow-lg">
      <h1 class="text-2xl font-bold mb-2 text-center">Sign Up</h1>
      <p class="text-center text-gray-600 mb-6">
        Create your account to start learning and teaching.
      </p>
      <form class="space-y-5" @submit.prevent="handleSignup">
        <div>
          <label for="name" class="block text-gray-700 font-semibold mb-2">Name</label>
          <Input 
            placeholder="Enter your name"
            type="text"
            id="name"
            name="name"
            required
            v-model="name"
          />
        </div>
        <div>
          <label for="email" class="block text-gray-700 font-semibold mb-2">Email</label>
          <Input 
            placeholder="Enter your email"
            type="email"
            id="email"
            name="email"
            required
            v-model="email"
          />
        </div>
        <div>
          <label for="role" class="block text-gray-700 font-semibold mb-2">Role</label>
          <select id="role" name="role" v-model="role" required class="w-full px-3 py-2 border border-accent ounded focus:outline-none focus:ring-2 focus:ring-accent">
            <option value="">Select your role</option>
            <option value="student">Student</option>
            <option value="teacher">Teacher</option>
          </select>
        </div>
        <div>
          <label for="password" class="block text-gray-700 font-semibold mb-2">Password</label>
          <Input 
            placeholder="Enter your password"
            type="password"
            id="password"
            name="password"
            v-model="password"
            required
          />
        </div>
        <div>
          <label for="password_confirm" class="block text-gray-700 font-semibold mb-2">Confirm Password</label>
          <Input 
            placeholder="Confirm your password"
            type="password"
            id="password_confirm"
            name="password_confirm"
            v-model="password_confirm"
            required
          />
        </div>
        <button
          class="w-full bg-accent text-white font-semibold py-2 px-4 rounded hover:bg-secondary transition"
          type="submit"
        >
          Create Account
        </button>
      </form>
      <div class="mt-6 space-y-2 text-center text-sm text-gray-600">
        <p>
          Already have an account?
          <a href="/login" class="text-accent hover:underline">Login here</a>.
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
import Input from '../Components/ui/Input.vue'
import { ref } from 'vue'

const name = ref('')
const email = ref('')
const role = ref('')
const password = ref('')
const password_confirm = ref('')

function handleSignup() {
  if (!name.value || !email.value || !role.value || !password.value || !password_confirm.value) {
    alert('Please fill in all fields.')
    return
  }
  if (password.value !== password_confirm.value) {
    alert('Passwords do not match.')
    return
  }
  axios.post('/signup', {
    name: name.value,
    email: email.value,
    role: role.value,
    password: password.value,
    password_confirmation: password_confirm.value
  }).then(response => {
    console.log('Signup successful:', response)
    alert('Signup successful!.')
    window.location.href = '/dashboard'
  }).catch(error => {
    console.error('Signup error:', error)
    alert(`An error occurred during signup. Please try again. ${error.response?.data?.message}`)
  })
  alert('Signup successful!')
}
</script>

<style>
.label {
    @reference block mb-2
}
</style>




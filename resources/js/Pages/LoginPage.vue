<template>
<div class="flex flex-col items-center justify-center min-h-screen bg-gray-50">
    <div class="w-full max-w-md bg-white p-8 rounded-xl shadow-lg">
        <h1>Login</h1>
        <p class="text-center mb-6">
            Welcome to the login page. Please enter your credentials to continue.
        </p>
        <form class="space-y-5" @submit.prevent="handleLogin">
            <div>
                <label for="email" class="label">Email:</label>
                <Input 
                    placeholder="Enter your email"
                    type="text"
                    id="email"
                    name="email"
                    required
                    v-model="email"
                />
            </div>
            <div>
                <label for="password" class="label">Password:</label>
                <Input 
                    placeholder="Enter your password"
                    type="password"
                    id="password"
                    name="password"
                    v-model="password"
                    required
                     />
            </div>
            <button
                class="w-full py-2 button px-4 rounded relative"
                type="submit"
                @onsubmit="handleLogin"
                :disabled="isLoading"
            >
                <span v-if="!isLoading">Login</span>
                <span v-else class="flex items-center justify-center">
                    <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Loading...
                </span>
            </button>
        </form>
        <div class="mt-6 space-y-2 text-center text-sm text-gray-600">
            <p>
                If you don't have an account,
                <a href="/signup" class="hover:underline">register here</a>.
            </p>
            <!-- <p>
                Forgot your password?
                <a href="/reset-password" class=" hover:underline">Reset it here</a>.
            </p> -->
        </div>
    </div>
</div>
</template>

<script setup>
import Input from '../Components/ui/Input.vue'
import {ref} from 'vue';
import axios from 'axios';

const email = ref('');
const password = ref('');
const isLoading = ref(false);

function handleLogin() {
    isLoading.value = true;
    axios.post('/login', {
        email: email.value,
        password: password.value
    })
    .then(() => {
        window.location.href = '/dashboard';
    })
    .catch(error => {
        console.error(error);
        alert('Login failed. Please check your credentials and try again.');
    })
    .finally(() => {
        isLoading.value = false;
    });
}

</script>

<style scoped>
.label {
    display: block;
    margin-bottom: 0.5rem;
}
</style>
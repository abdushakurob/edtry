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
                class="w-full py-2 button px-4 rounded"
                type="submit"
                @onsubmit="handleLogin"
            >
                Login
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

function handleLogin() {
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
    });
}

</script>

<style>
.label {
    @reference block mb-2
}
</style>
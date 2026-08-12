<script setup lang="ts">
import axios from 'axios';
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { useAuth } from '@/composables/useAuth';
import { useToast } from '@/composables/useToast';

const router = useRouter();
const { login } = useAuth();
const toast = useToast();

const email = ref('');
const password = ref('');
const loading = ref(false);
const errors = ref<Record<string, string[]>>({});

async function handleSubmit() {
    loading.value = true;
    errors.value = {};

    try {
        await login({ email: email.value, password: password.value });
        toast.success('Login realizado com sucesso!');
        router.push({ name: 'pessoas.index' });
    } catch (err) {
        if (axios.isAxiosError(err) && err.response?.status === 422) {
            errors.value = err.response.data.errors ?? {};
        } else {
            toast.error('Não foi possível fazer login. Tente novamente.');
        }
    } finally {
        loading.value = false;
    }
}
</script>

<template>
    <div class="flex min-h-screen items-center justify-center bg-gray-50 px-4">
        <div class="w-full max-w-sm rounded-xl bg-white p-8 shadow-md">
            <h1 class="mb-1 text-2xl font-bold text-gray-900">
                Apresenta.me
            </h1>
            <p class="mb-6 text-sm text-gray-500">
                Entre para gerenciar o cadastro de pessoas
            </p>

            <form class="space-y-4" @submit.prevent="handleSubmit">
                <div>
                    <label
                        for="email"
                        class="mb-1 block text-sm font-medium text-gray-700"
                    >
                        E-mail
                    </label>
                    <input
                        id="email"
                        v-model="email"
                        type="email"
                        autocomplete="email"
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 focus:outline-none"
                        :class="{ 'border-red-500': errors.email }"
                    />
                    <p v-if="errors.email" class="mt-1 text-xs text-red-600">
                        {{ errors.email[0] }}
                    </p>
                </div>

                <div>
                    <label
                        for="password"
                        class="mb-1 block text-sm font-medium text-gray-700"
                    >
                        Senha
                    </label>
                    <input
                        id="password"
                        v-model="password"
                        type="password"
                        autocomplete="current-password"
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 focus:outline-none"
                        :class="{ 'border-red-500': errors.password }"
                    />
                    <p
                        v-if="errors.password"
                        class="mt-1 text-xs text-red-600"
                    >
                        {{ errors.password[0] }}
                    </p>
                </div>

                <button
                    type="submit"
                    :disabled="loading"
                    class="w-full rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-indigo-700 disabled:cursor-not-allowed disabled:opacity-60"
                >
                    {{ loading ? 'Entrando...' : 'Entrar' }}
                </button>
            </form>
        </div>
    </div>
</template>
import { computed, ref } from 'vue';
import api from '@/lib/api';
import type { LoginCredentials, LoginResponse, User } from '@/types';

const user = ref<User | null>(
    JSON.parse(localStorage.getItem('auth_user') ?? 'null'),
);
const token = ref<string | null>(localStorage.getItem('auth_token'));

export function useAuth() {
    const isAuthenticated = computed(() => !!token.value);

    async function login(credentials: LoginCredentials) {
        const { data } = await api.post<LoginResponse>('/login', credentials);

        token.value = data.token;
        user.value = data.user;

        localStorage.setItem('auth_token', data.token);
        localStorage.setItem('auth_user', JSON.stringify(data.user));
    }

    async function logout() {
        try {
            await api.post('/logout');
        } finally {
            token.value = null;
            user.value = null;
            localStorage.removeItem('auth_token');
            localStorage.removeItem('auth_user');
        }
    }

    return {
        user,
        isAuthenticated,
        login,
        logout,
    };
}
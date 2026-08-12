import { createRouter, createWebHistory } from 'vue-router';
import { useAuth } from '@/composables/useAuth';

const router = createRouter({
    history: createWebHistory(),
    routes: [
        {
            path: '/login',
            name: 'login',
            component: () => import('@/views/Login.vue'),
            meta: { guestOnly: true },
        },
        {
            path: '/',
            redirect: { name: 'pessoas.index' },
        },
        {
            path: '/pessoas',
            name: 'pessoas.index',
            component: () => import('@/views/pessoas/PessoasIndex.vue'),
            meta: { requiresAuth: true },
        },
        {
            path: '/pessoas/nova',
            name: 'pessoas.create',
            component: () => import('@/views/pessoas/PessoaForm.vue'),
            meta: { requiresAuth: true },
        },
        {
            path: '/pessoas/:id/editar',
            name: 'pessoas.edit',
            component: () => import('@/views/pessoas/PessoaForm.vue'),
            meta: { requiresAuth: true },
            props: true,
        },
        {
            path: '/:pathMatch(.*)*',
            redirect: { name: 'pessoas.index' },
        },
    ],
});

router.beforeEach((to) => {
    const { isAuthenticated } = useAuth();

    if (to.meta.requiresAuth && !isAuthenticated.value) {
        return { name: 'login' };
    }

    if (to.meta.guestOnly && isAuthenticated.value) {
        return { name: 'pessoas.index' };
    }
});

export default router;
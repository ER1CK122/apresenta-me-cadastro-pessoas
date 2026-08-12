<script setup lang="ts">
import { useToast } from '@/composables/useToast';

const { toasts } = useToast();
</script>

<template>
    <div class="fixed top-4 right-4 z-50 flex flex-col gap-2">
        <TransitionGroup name="toast">
            <div
                v-for="toast in toasts"
                :key="toast.id"
                class="min-w-[280px] rounded-lg px-4 py-3 text-sm font-medium shadow-lg"
                :class="{
                    'bg-green-600 text-white': toast.type === 'success',
                    'bg-red-600 text-white': toast.type === 'error',
                }"
            >
                {{ toast.text }}
            </div>
        </TransitionGroup>
    </div>
</template>

<style scoped>
.toast-enter-active,
.toast-leave-active {
    transition: all 0.25s ease;
}
.toast-enter-from {
    opacity: 0;
    transform: translateX(20px);
}
.toast-leave-to {
    opacity: 0;
    transform: translateX(20px);
}
</style>
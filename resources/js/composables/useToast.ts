import { ref } from 'vue';

export interface ToastMessage {
    id: number;
    type: 'success' | 'error';
    text: string;
}

const toasts = ref<ToastMessage[]>([]);
let nextId = 0;

export function useToast() {
    function push(type: ToastMessage['type'], text: string) {
        const id = nextId++;
        toasts.value.push({ id, type, text });

        setTimeout(() => {
            toasts.value = toasts.value.filter((t) => t.id !== id);
        }, 4000);
    }

    function success(text: string) {
        push('success', text);
    }

    function error(text: string) {
        push('error', text);
    }

    return { toasts, success, error };
}
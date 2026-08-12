<script setup lang="ts">
import axios from 'axios';
import { computed, onMounted, ref } from 'vue';
import { useRoute, useRouter, RouterLink } from 'vue-router';
import { useToast } from '@/composables/useToast';
import api from '@/lib/api';
import type { Pessoa, PessoaFormData } from '@/types';

const route = useRoute();
const router = useRouter();
const toast = useToast();

const pessoaId = computed(() => {
    const id = route.params.id;

    return id ? Number(id) : null;
});

const isEdicao = computed(() => pessoaId.value !== null);

const form = ref<PessoaFormData>({
    nome: '',
    cpf: '',
    tipo: '',
    telefone: '',
    email: '',
});

const loading = ref(false);
const carregando = ref(isEdicao.value);
const errors = ref<Record<string, string[]>>({});

function aplicarMascaraCpf(valor: string): string {
    const digits = valor.replace(/\D/g, '').slice(0, 11);

    return digits
        .replace(/(\d{3})(\d)/, '$1.$2')
        .replace(/(\d{3})(\d)/, '$1.$2')
        .replace(/(\d{3})(\d{1,2})$/, '$1-$2');
}

function handleCpfInput(event: Event) {
    const target = event.target as HTMLInputElement;
    form.value.cpf = aplicarMascaraCpf(target.value);
}

async function carregarPessoa() {
    if (!pessoaId.value) {
return;
}

    try {
        const { data } = await api.get<{ data: Pessoa }>(
            `/pessoas/${pessoaId.value}`,
        );

        form.value = {
            nome: data.data.nome,
            cpf: aplicarMascaraCpf(data.data.cpf),
            tipo: data.data.tipo,
            telefone: data.data.telefone,
            email: data.data.email,
        };
    } catch {
        toast.error('Não foi possível carregar os dados desta pessoa.');
        router.push({ name: 'pessoas.index' });
    } finally {
        carregando.value = false;
    }
}

async function handleSubmit() {
    loading.value = true;
    errors.value = {};

    try {
        if (isEdicao.value) {
            await api.put(`/pessoas/${pessoaId.value}`, form.value);
            toast.success('Pessoa atualizada com sucesso.');
        } else {
            await api.post('/pessoas', form.value);
            toast.success('Pessoa cadastrada com sucesso.');
        }

        router.push({ name: 'pessoas.index' });
    } catch (err) {
        if (axios.isAxiosError(err) && err.response?.status === 422) {
            errors.value = err.response.data.errors ?? {};
            toast.error('Verifique os campos destacados.');
        } else {
            toast.error('Não foi possível salvar. Tente novamente.');
        }
    } finally {
        loading.value = false;
    }
}

onMounted(() => {
    if (isEdicao.value) {
        carregarPessoa();
    }
});
</script>

<template>
    <div class="min-h-screen bg-gray-50">
        <header class="border-b border-gray-200 bg-white">
            <div class="mx-auto flex max-w-6xl items-center justify-between px-6 py-4">
                <h1 class="text-lg font-bold text-gray-900">Apresenta.me</h1>
                <RouterLink
                    :to="{ name: 'pessoas.index' }"
                    class="text-sm font-medium text-gray-500 hover:text-gray-800"
                >
                    Voltar
                </RouterLink>
            </div>
        </header>

        <main class="mx-auto max-w-2xl px-6 py-8">
            <h2 class="mb-6 text-xl font-semibold text-gray-900">
                {{ isEdicao ? 'Editar pessoa' : 'Nova pessoa' }}
            </h2>

            <div
                v-if="carregando"
                class="rounded-xl border border-gray-200 bg-white p-8 text-center text-gray-400"
            >
                Carregando...
            </div>

            <form
                v-else
                class="space-y-5 rounded-xl border border-gray-200 bg-white p-6"
                @submit.prevent="handleSubmit"
            >
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">
                        Nome
                    </label>
                    <input
                        v-model="form.nome"
                        type="text"
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 focus:outline-none"
                        :class="{ 'border-red-500': errors.nome }"
                    />
                    <p v-if="errors.nome" class="mt-1 text-xs text-red-600">
                        {{ errors.nome[0] }}
                    </p>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700">
                            CPF
                        </label>
                        <input
                            :value="form.cpf"
                            type="text"
                            placeholder="000.000.000-00"
                            maxlength="14"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 focus:outline-none"
                            :class="{ 'border-red-500': errors.cpf }"
                            @input="handleCpfInput"
                        />
                        <p v-if="errors.cpf" class="mt-1 text-xs text-red-600">
                            {{ errors.cpf[0] }}
                        </p>
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700">
                            Tipo
                        </label>
                        <select
                            v-model="form.tipo"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 focus:outline-none"
                            :class="{ 'border-red-500': errors.tipo }"
                        >
                            <option value="" disabled>Selecione</option>
                            <option value="fisica">Pessoa física</option>
                            <option value="juridica">Pessoa jurídica</option>
                        </select>
                        <p v-if="errors.tipo" class="mt-1 text-xs text-red-600">
                            {{ errors.tipo[0] }}
                        </p>
                    </div>
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">
                        Telefone
                    </label>
                    <input
                        v-model="form.telefone"
                        type="text"
                        placeholder="(00) 00000-0000"
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 focus:outline-none"
                        :class="{ 'border-red-500': errors.telefone }"
                    />
                    <p v-if="errors.telefone" class="mt-1 text-xs text-red-600">
                        {{ errors.telefone[0] }}
                    </p>
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">
                        E-mail
                    </label>
                    <input
                        v-model="form.email"
                        type="email"
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 focus:outline-none"
                        :class="{ 'border-red-500': errors.email }"
                    />
                    <p v-if="errors.email" class="mt-1 text-xs text-red-600">
                        {{ errors.email[0] }}
                    </p>
                </div>

                <div class="flex justify-end gap-3 pt-2">
                    <RouterLink
                        :to="{ name: 'pessoas.index' }"
                        class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100"
                    >
                        Cancelar
                    </RouterLink>
                    <button
                        type="submit"
                        :disabled="loading"
                        class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700 disabled:opacity-60"
                    >
                        {{ loading ? 'Salvando...' : 'Salvar' }}
                    </button>
                </div>
            </form>
        </main>
    </div>
</template>
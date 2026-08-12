<script setup lang="ts">
import { onMounted, ref } from 'vue';
import { RouterLink, useRouter } from 'vue-router';
import { useAuth } from '@/composables/useAuth';
import { useToast } from '@/composables/useToast';
import api from '@/lib/api';
import type { Pessoa, PaginatedResponse } from '@/types';

const router = useRouter();
const { logout } = useAuth();
const toast = useToast();

const pessoas = ref<Pessoa[]>([]);
const loading = ref(true);
const busca = ref('');
const tipoFiltro = ref('');
const currentPage = ref(1);
const lastPage = ref(1);
const total = ref(0);

const pessoaParaExcluir = ref<Pessoa | null>(null);
const excluindo = ref(false);

async function carregarPessoas(page = 1) {
    loading.value = true;

    try {
        const { data } = await api.get<PaginatedResponse<Pessoa>>('/pessoas', {
            params: {
                page,
                busca: busca.value || undefined,
                tipo: tipoFiltro.value || undefined,
            },
        });

        pessoas.value = data.data;
        currentPage.value = data.meta.current_page;
        lastPage.value = data.meta.last_page;
        total.value = data.meta.total;
    } catch {
        toast.error('Não foi possível carregar a listagem de pessoas.');
    } finally {
        loading.value = false;
    }
}

function aplicarFiltros() {
    carregarPessoas(1);
}

function confirmarExclusao(pessoa: Pessoa) {
    pessoaParaExcluir.value = pessoa;
}

function cancelarExclusao() {
    pessoaParaExcluir.value = null;
}

async function excluirPessoa() {
    if (!pessoaParaExcluir.value) {
return;
}

    excluindo.value = true;

    try {
        await api.delete(`/pessoas/${pessoaParaExcluir.value.id}`);
        toast.success('Pessoa excluída com sucesso.');
        pessoaParaExcluir.value = null;
        await carregarPessoas(currentPage.value);
    } catch {
        toast.error('Não foi possível excluir esta pessoa.');
    } finally {
        excluindo.value = false;
    }
}

async function handleLogout() {
    await logout();
    router.push({ name: 'login' });
}

onMounted(() => carregarPessoas());
</script>

<template>
    <div class="min-h-screen bg-gray-50">
        <header class="border-b border-gray-200 bg-white">
            <div class="mx-auto flex max-w-6xl items-center justify-between px-6 py-4">
                <h1 class="text-lg font-bold text-gray-900">Apresenta.me</h1>
                <button
                    class="text-sm font-medium text-gray-500 hover:text-gray-800"
                    @click="handleLogout"
                >
                    Sair
                </button>
            </div>
        </header>

        <main class="mx-auto max-w-6xl px-6 py-8">
            <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
                <h2 class="text-xl font-semibold text-gray-900">
                    Pessoas cadastradas
                </h2>
                <RouterLink
                    :to="{ name: 'pessoas.create' }"
                    class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700"
                >
                    + Nova pessoa
                </RouterLink>
            </div>

            <div class="mb-4 flex flex-wrap gap-3">
                <input
                    v-model="busca"
                    type="text"
                    placeholder="Buscar por nome, e-mail ou CPF..."
                    class="w-full max-w-xs rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 focus:outline-none"
                    @keyup.enter="aplicarFiltros"
                />
                <select
                    v-model="tipoFiltro"
                    class="rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 focus:outline-none"
                    @change="aplicarFiltros"
                >
                    <option value="">Todos os tipos</option>
                    <option value="fisica">Pessoa física</option>
                    <option value="juridica">Pessoa jurídica</option>
                </select>
                <button
                    class="rounded-lg bg-gray-800 px-4 py-2 text-sm font-medium text-white hover:bg-gray-900"
                    @click="aplicarFiltros"
                >
                    Filtrar
                </button>
            </div>

            <div class="overflow-hidden rounded-xl border border-gray-200 bg-white">
                <table class="w-full text-left text-sm">
                    <thead class="bg-gray-50 text-xs tracking-wide text-gray-500 uppercase">
                        <tr>
                            <th class="px-4 py-3">Nome</th>
                            <th class="px-4 py-3">CPF</th>
                            <th class="px-4 py-3">Tipo</th>
                            <th class="px-4 py-3">Telefone</th>
                            <th class="px-4 py-3">E-mail</th>
                            <th class="px-4 py-3 text-right">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-if="loading">
                            <td colspan="6" class="px-4 py-8 text-center text-gray-400">
                                Carregando...
                            </td>
                        </tr>
                        <tr v-else-if="pessoas.length === 0">
                            <td colspan="6" class="px-4 py-8 text-center text-gray-400">
                                Nenhuma pessoa encontrada.
                            </td>
                        </tr>
                        <tr
                            v-for="pessoa in pessoas"
                            v-else
                            :key="pessoa.id"
                            class="hover:bg-gray-50"
                        >
                            <td class="px-4 py-3 font-medium text-gray-900">
                                {{ pessoa.nome }}
                            </td>
                            <td class="px-4 py-3 text-gray-600">
                                {{ pessoa.cpf_formatado }}
                            </td>
                            <td class="px-4 py-3">
                                <span
                                    class="rounded-full px-2 py-0.5 text-xs font-medium"
                                    :class="
                                        pessoa.tipo === 'fisica'
                                            ? 'bg-blue-100 text-blue-700'
                                            : 'bg-purple-100 text-purple-700'
                                    "
                                >
                                    {{ pessoa.tipo === 'fisica' ? 'Física' : 'Jurídica' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-gray-600">
                                {{ pessoa.telefone }}
                            </td>
                            <td class="px-4 py-3 text-gray-600">
                                {{ pessoa.email }}
                            </td>
                            <td class="px-4 py-3 text-right">
                                <RouterLink
                                    :to="{ name: 'pessoas.edit', params: { id: pessoa.id } }"
                                    class="mr-3 font-medium text-indigo-600 hover:text-indigo-800"
                                >
                                    Editar
                                </RouterLink>
                                <button
                                    class="font-medium text-red-600 hover:text-red-800"
                                    @click="confirmarExclusao(pessoa)"
                                >
                                    Excluir
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div
                v-if="!loading && lastPage > 1"
                class="mt-4 flex items-center justify-between text-sm text-gray-600"
            >
                <span>{{ total }} pessoa(s) no total</span>
                <div class="flex gap-2">
                    <button
                        :disabled="currentPage === 1"
                        class="rounded-lg border border-gray-300 px-3 py-1.5 disabled:cursor-not-allowed disabled:opacity-40"
                        @click="carregarPessoas(currentPage - 1)"
                    >
                        Anterior
                    </button>
                    <span class="px-2 py-1.5">
                        Página {{ currentPage }} de {{ lastPage }}
                    </span>
                    <button
                        :disabled="currentPage === lastPage"
                        class="rounded-lg border border-gray-300 px-3 py-1.5 disabled:cursor-not-allowed disabled:opacity-40"
                        @click="carregarPessoas(currentPage + 1)"
                    >
                        Próxima
                    </button>
                </div>
            </div>
        </main>

        <!-- Modal de confirmação de exclusão -->
        <div
            v-if="pessoaParaExcluir"
            class="fixed inset-0 z-40 flex items-center justify-center bg-black/40 px-4"
        >
            <div class="w-full max-w-sm rounded-xl bg-white p-6 shadow-xl">
                <h3 class="mb-2 text-lg font-semibold text-gray-900">
                    Excluir pessoa
                </h3>
                <p class="mb-6 text-sm text-gray-600">
                    Tem certeza que deseja excluir
                    <strong>{{ pessoaParaExcluir.nome }}</strong>? Esta ação não pode
                    ser desfeita.
                </p>
                <div class="flex justify-end gap-3">
                    <button
                        class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100"
                        @click="cancelarExclusao"
                    >
                        Cancelar
                    </button>
                    <button
                        :disabled="excluindo"
                        class="rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700 disabled:opacity-60"
                        @click="excluirPessoa"
                    >
                        {{ excluindo ? 'Excluindo...' : 'Excluir' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
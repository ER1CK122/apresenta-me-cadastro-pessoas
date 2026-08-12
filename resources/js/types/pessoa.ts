export type TipoPessoa = 'fisica' | 'juridica';

export interface Pessoa {
    id: number;
    nome: string;
    cpf: string;
    cpf_formatado: string;
    tipo: TipoPessoa;
    telefone: string;
    email: string;
    created_at: string;
    updated_at: string;
}

export interface PessoaFormData {
    nome: string;
    cpf: string;
    tipo: TipoPessoa | '';
    telefone: string;
    email: string;
}

export interface PaginatedResponse<T> {
    data: T[];
    meta: {
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
        from: number | null;
        to: number | null;
    };
    links: {
        first: string | null;
        last: string | null;
        prev: string | null;
        next: string | null;
    };
}
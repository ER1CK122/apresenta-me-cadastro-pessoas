<?php

namespace App\Http\Requests;

use App\Rules\CpfValido;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePessoaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $pessoaId = $this->route('pessoa')->id;

        return [
            'nome' => ['required', 'string', 'max:255'],
            'cpf' => ['required', 'string', new CpfValido, Rule::unique('pessoas', 'cpf')->ignore($pessoaId)],
            'tipo' => ['required', Rule::in(['fisica', 'juridica'])],
            'telefone' => ['required', 'string', 'max:20'],
            'email' => ['required', 'email', 'max:255', Rule::unique('pessoas', 'email')->ignore($pessoaId)],
        ];
    }

    public function messages(): array
    {
        return [
            'nome.required' => 'O nome é obrigatório.',
            'cpf.required' => 'O CPF é obrigatório.',
            'cpf.unique' => 'Este CPF já está cadastrado.',
            'tipo.required' => 'O tipo é obrigatório.',
            'tipo.in' => 'O tipo deve ser "fisica" ou "juridica".',
            'telefone.required' => 'O telefone é obrigatório.',
            'email.required' => 'O e-mail é obrigatório.',
            'email.email' => 'Informe um e-mail válido.',
            'email.unique' => 'Este e-mail já está cadastrado.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'cpf' => preg_replace('/[^0-9]/', '', (string) $this->cpf),
        ]);
    }
}
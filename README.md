# Apresenta.me — Teste Técnico Full-Stack

Sistema de manutenção de cadastro de pessoas, desenvolvido como teste prático para a vaga de Programador Full-Stack na Apresenta.me.

## Stack técnica

- **Backend:** Laravel 11 (API REST) + Laravel Sanctum (autenticação por token)
- **Frontend:** Vue 3 (Composition API + `<script setup>`) + TypeScript + Vue Router
- **Estilização:** Tailwind CSS v4
- **Banco de dados:** MySQL / MariaDB

## Arquitetura

O projeto é uma **SPA desacoplada**: o Laravel funciona exclusivamente como API REST (`routes/api.php`), sem views Blade além da shell que carrega o Vue. O Vue Router controla toda a navegação no client-side, e a comunicação com o backend é feita via Axios com autenticação por token Bearer (Sanctum).

Essa escolha foi feita porque o desafio pede explicitamente uma "API REST com suporte a autenticação por token", e essa arquitetura deixa o backend reutilizável por qualquer outro client no futuro (app mobile, outro frontend, etc), além de deixar claras as responsabilidades entre as camadas.

## Requisitos

- PHP 8.2+
- Composer
- Node.js 18+ e npm
- MySQL ou MariaDB

## Instalação

Clone o repositório e instale as dependências:

```bash
composer install
npm install
```

Copie o arquivo de ambiente e gere a chave da aplicação:

```bash
cp .env.example .env
php artisan key:generate
```

### Banco de dados

Crie o banco no MySQL/MariaDB:

```sql
CREATE DATABASE apresenta_me_teste CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Ajuste as credenciais no `.env` (`DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`) e rode as migrations com o seeder de usuário:

```bash
php artisan migrate --seed
```

O seeder cria um usuário de teste para login:

- **E-mail:** `admin@apresentame.com`
- **Senha:** `senha123`

## Executando o projeto

Em dois terminais separados:

```bash
php artisan serve
```

```bash
npm run dev
```

Acesse `http://127.0.0.1:8000` no navegador.

## Funcionalidades

### Autenticação
- Login via token (Laravel Sanctum)
- Rotas protegidas no frontend (guard de navegação) e no backend (middleware `auth:sanctum`)
- Logout com revogação do token

### CRUD de Pessoas
- Listagem paginada, com busca (nome, e-mail, telefone ou CPF) e filtro por tipo
- Cadastro e edição em formulário único, com máscara de CPF em tempo real
- Exclusão com modal de confirmação
- Validação de CPF com verificação real do dígito verificador (não apenas formato)
- Unicidade de e-mail e CPF, validada também na edição (ignorando o próprio registro)
- Feedback visual via toasts de sucesso/erro e mensagens de validação por campo

## Decisões técnicas

**Autenticação por token (Sanctum), não por sessão.** Como o frontend é uma SPA desacoplada consumindo uma API REST, o token Bearer é a abordagem mais direta e escalável — evita a complexidade extra de configurar cookies stateful/CORS para SPA no mesmo domínio, e deixa a API pronta para ser consumida por outros clientes.

**Validação de CPF com dígito verificador real.** Em vez de validar apenas o formato (11 dígitos com máscara), a regra customizada (`app/Rules/CpfValido.php`) recalcula os dígitos verificadores segundo o algoritmo oficial da Receita Federal, e rejeita sequências repetidas (ex: `111.111.111-11`), que matematicamente passariam no cálculo mas não são CPFs válidos.

**CPF armazenado sem máscara no banco.** O `FormRequest` remove a formatação (`prepareForValidation()`) antes de validar e persistir, guardando apenas os dígitos. A máscara é aplicada em tempo real no frontend (digitação) e reconstituída na exibição via `PessoaResource` (campo `cpf_formatado`). Isso simplifica buscas e evita inconsistência de formatação no banco.

**Form Requests dedicados para criação e edição.** `StorePessoaRequest` e `UpdatePessoaRequest` são separados porque a regra de unicidade de CPF/e-mail precisa ignorar o próprio registro na edição (`Rule::unique(...)->ignore($id)`), o que exigiria lógica condicional se fossem unificados.

**Estado de autenticação sem Pinia/Vuex.** O composable `useAuth` guarda o estado (`user`, `token`) em `ref`s no escopo do módulo, fora da função exportada — isso cria um estado compartilhado entre todos os componentes que importam o composable, funcionando como uma store minimalista. Para o escopo deste projeto, isso evita a dependência extra de uma lib de state management.

**Interceptors do Axios centralizados.** O client HTTP (`resources/js/lib/api.ts`) injeta o token automaticamente em toda requisição e trata respostas `401` redirecionando para o login, evitando repetir essa lógica em cada chamada de API.

## Estrutura de pastas (frontend)

```
resources/js/
├── components/       # Componentes reutilizáveis (ex: ToastContainer)
├── composables/       # useAuth, useToast
├── lib/                # Cliente Axios configurado
├── router/            # Vue Router e guards de navegação
├── types/              # Tipos TypeScript (User, Pessoa, respostas paginadas)
└── views/
    ├── Login.vue
    └── pessoas/
        ├── PessoasIndex.vue
        └── PessoaForm.vue
```

## Possíveis melhorias futuras

- Testes automatizados (Feature tests do Laravel para o CRUD e autenticação; testes unitários para a regra de validação de CPF)
- Refresh token / expiração configurável do token Sanctum
- Exportação da listagem (CSV/PDF)
- Internacionalização (i18n) caso o produto precise suportar outros idiomas

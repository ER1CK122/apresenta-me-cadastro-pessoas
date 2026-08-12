<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePessoaRequest;
use App\Http\Requests\UpdatePessoaRequest;
use App\Http\Resources\PessoaResource;
use App\Models\Pessoa;
use Illuminate\Http\Request;

class PessoaController extends Controller
{
    public function index(Request $request)
    {
        $query = Pessoa::query();

        if ($busca = $request->query('busca')) {
            $query->where(function ($q) use ($busca) {
                $q->where('nome', 'like', "%{$busca}%")
                    ->orWhere('email', 'like', "%{$busca}%")
                    ->orWhere('telefone', 'like', "%{$busca}%");

                $cpfDigits = preg_replace('/[^0-9]/', '', $busca);

                if ($cpfDigits !== '') {
                    $q->orWhere('cpf', 'like', "%{$cpfDigits}%");
                }
            });
        }

        if ($tipo = $request->query('tipo')) {
            $query->where('tipo', $tipo);
        }

        $pessoas = $query->orderBy('nome')->paginate(10);

        return PessoaResource::collection($pessoas);
    }

    public function store(StorePessoaRequest $request)
    {
        $pessoa = Pessoa::create($request->validated());

        return (new PessoaResource($pessoa))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Pessoa $pessoa)
    {
        return new PessoaResource($pessoa);
    }

    public function update(UpdatePessoaRequest $request, Pessoa $pessoa)
    {
        $pessoa->update($request->validated());

        return new PessoaResource($pessoa);
    }

    public function destroy(Pessoa $pessoa)
    {
        $pessoa->delete();

        return response()->json(null, 204);
    }
}
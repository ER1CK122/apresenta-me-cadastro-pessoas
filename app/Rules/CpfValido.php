<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class CpfValido implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $cpf = preg_replace('/[^0-9]/', '', (string) $value);

        if (! $this->isValid($cpf)) {
            $fail('O :attribute informado não é válido.');
        }
    }

    private function isValid(string $cpf): bool
    {
        if (strlen($cpf) !== 11) {
            return false;
        }

        // Rejeita sequências repetidas (ex: 111.111.111-11), que passam
        // no cálculo do dígito verificador mas não são CPFs válidos
        if (preg_match('/^(\d)\1{10}$/', $cpf)) {
            return false;
        }

        for ($t = 9; $t < 11; $t++) {
            $soma = 0;
            for ($i = 0; $i < $t; $i++) {
                $soma += (int) $cpf[$i] * (($t + 1) - $i);
            }
            $digito = ((10 * $soma) % 11) % 10;

            if ((int) $cpf[$t] !== $digito) {
                return false;
            }
        }

        return true;
    }
}
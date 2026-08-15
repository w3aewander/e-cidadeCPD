<?php

namespace App\Domain\ProcessoEletronico\Requests;

use App\Http\Requests\DBFormRequest;

class GetImoveisRequest extends DBFormRequest
{
    public function rules()
    {
        return [
            'cpf_cnpj' => ['required', 'string', 'max:14', 'min:11', 'cpf_cnpj']
        ];
    }

    public function messages()
    {
        return [
            'cpf_cnpj.required'   => 'O campo CPF/CNPJ é invalido',
            'cpf_cnpj.max'        => 'O tamanho do campo CPF/CNPJ deve ser menor que 15 caracteres',
            'cpf_cnpj.min'        => 'O tamanho do campo CPF/CNPJ deve ser maior que 10 caracteres',
            'cpf_cnpj.validation' => 'O campo CPF/CNPJ é invalido'
        ];
    }
}

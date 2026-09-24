<?php

namespace App\Domain\ProcessoEletronico\Requests;

use App\Http\Requests\DBFormRequest;
use Illuminate\Validation\Rule;

class GetTiposDebitosRequest extends DBFormRequest
{
    public function rules()
    {
        return [
            'tipo'  => ['required', 'string', 'max:1', 'min:1', Rule::in(['M', 'C', 'I'])],
            'value' => ['required', 'string', 'max:11', 'min:1', 'contribuinte']
        ];
    }

    public function messages()
    {
        return [
            'contribuinte'        => 'O contribuinte não consta na base de dados',
            'cpf_cnpj.required'   => 'O campo CPF/CNPJ é invalido',
            'cpf_cnpj.max'        => 'O tamanho do campo CPF/CNPJ deve ser menor que 15 caracteres',
            'cpf_cnpj.min'        => 'O tamanho do campo CPF/CNPJ deve ser maior que 10 caracteres',
            'cpf_cnpj.validation' => 'O campo CPF/CNPJ é invalido'
        ];
    }
}

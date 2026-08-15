<?php

namespace App\Domain\Patrimonial\PNCP\Requests;

use App\Http\Requests\DBFormRequest;

/**
 *@property $anoCompra
 *@property $tituloDocumento
 *@property $tipoDocumento
 *@property $unidadeCompradora
 *@property $instrumentoConvocatorio
 *@property $modalidade
 *@property $modoDisputa
 *@property $numeroCompra
 *@property $numeroProcesso
 *@property $objetoCompra
 *@property $informacaoComplementar
 *@property $amparoLegal
 *@property $cnpj
 */
class InclusaoItemPCARequest extends DBFormRequest
{
    public function rules()
    {
        return [
            'codigoPca' => 'integer|required',
        ];
    }
}

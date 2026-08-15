<?php

namespace App\Domain\Patrimonial\Ouvidoria\Controller\ProcessoEletronico;

use App\Domain\Core\Base\Http\Response\DBJsonResponse;
use App\Domain\Patrimonial\Ouvidoria\Services\FornecedorService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class FornecedorController extends Controller
{

    /**
     * @param Request $request
     * @return DBJsonResponse
     * @throws \Exception
     *
     * @api {get} patrimonial/ouvidoria/atendimento/fornecedor/notas Buscar Notas
     * @apiName BuscarNotas
     * @apiGroup Processo Eletronico-Fornecedor
     * @apiBody {String} cpf_cnpj   CPF ou CNPJ do fornecedor.
     * @apiHeaderExample {json} Request Header:
     *  { "Authorization": "Bearer token",
     *    "Content-Type": "application/json",
     *    "Accept": "application/json"
     *  }
     *
     * @apiSuccess {Json} data Dados da nota
     * @apiSuccess {String} message Messagem da requisicao
     * @apiSuccess {Boolean} error Se teve erro no retorno da api
     *
     * @apiSuccessExample {json} Response
     *     HTTP/1.1 200 OK
     *     {
     *       "error":false,
     *       "data": [{
     *           nota: {
     *                "empenho_codigo": 109731,
     *               "empenho_numero": "67",
     *               "codigo": 127627,
     *               "numero": "222",
     *               "data": "2023-05-31",
     *               "data_inclusao": "2023-05-31",
     *               "data_servidor": "2023-05-31",
     *               "valor": "100",
     *               "valor_liquidado": "100",
     *               "valor_anulado": "0",
     *               "valor_pago": "100",
     *               "anulado": false,
     *               "exercicio": 2023,
     *               "retencao": "10",
     *               "ordem_pagamento": 151470
     *          },
     *          historico: [
     *                {
     *                       "descricao": "PAGAMENTO",
     *                       "valor": "10",
     *                       "data": "2023-05-31"
     *                   },
     *                 ...
     *          ]
     *       },
     *       ...
     *       ],
     *       "message": ""
     *     }
     */
    public function notas(Request $request)
    {
        $this->validate($request, [
            'cpf_cnpj' => 'required'
        ]);

        $notas = FornecedorService::notasByCpfCnpj($request->get("cpf_cnpj"));
        $notasResponse = [];
        foreach ($notas as $nota) {
            $notaAux["nota"] = $nota;
            $notaAux["historico"] = FornecedorService::notasHistorico($nota->ordem_pagamento);
            $notasResponse[] = $notaAux;
        }

        return new DBJsonResponse($notasResponse);
    }

    /**
     * @param Request $request
     * @return DBJsonResponse
     * @throws \Exception
     *
     * @api {get} patrimonial/ouvidoria/atendimento/fornecedor/empenhos Buscar Empenhos
     * @apiName BuscarEmpenhos
     * @apiGroup Processo Eletronico-Fornecedor
     * @apiBody {String} cpf_cnpj  CPF ou CNPJ do fornecedor.
     *
     * @apiHeaderExample {json} Request Header
     *  { "Authorization": "Bearer token" ,
     *    "Content-Type": "application/json",
     *    "Accept": "application/json"
     * }
     *
     * @apiSuccess {Json} data Dados do empenho
     * @apiSuccess {String} message Messagem da requisicao
     * @apiSuccess {Boolean} error Se teve erro no retorno da api
     *
     * @apiSuccessExample {json} Response
     *     HTTP/1.1 200 OK
     *     {
     *       "error":false,
     *       "data": [
     *        {
     *           "empenho_codigo": 109918,
     *           "empenho_numero": "81",
     *           "empenho_exercicio": 2023,
     *           "data_emissao": "2023-06-23",
     *           "cpf_cnpj": "00000000000",
     *           "valor": "11",
     *           "valor_anulado": "0",
     *           "valor_liquidado": "0",
     *           "valor_pago": "0",
     *           "saldo_liquidado": "0",
     *           "saldo": "11",
     *           "observacao": "alguma observacao",
     *           "licitacao": null,
     *           "contrato": null
     *       },
     *       ...
     *       ],
     *       "message": ""
     *     }
     *
     */
    public function empenhos(Request $request)
    {
        $this->validate($request, [
            'cpf_cnpj' => 'required'
        ]);

        $empenhos = FornecedorService::empenhos($request->get("cpf_cnpj"));
        return new DBJsonResponse($empenhos);
    }
}

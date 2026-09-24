<?php

namespace App\Domain\Patrimonial\PNCP\Services;

use App\Domain\Patrimonial\Material\Models\MaterialUnidade;

use App\Domain\Patrimonial\PNCP\Clients\PlanoContratacoesClient;
use App\Domain\Patrimonial\PNCP\Exceptions\CompraEditalAvisoExcpetion;
use App\Domain\Patrimonial\PNCP\Models\ComprasPncp;
use Illuminate\Http\Request;

class PlanoContratacoesService
{
    /**
     * @var PlanoContratacoesClient
     */
    private $http;

    public function __construct()
    {
        $this->http = new PlanoContratacoesClient();
    }

    /**
     * @return array
     */
    public function buscarUnidades()
    {
        return MaterialUnidade::all(['m61_codmatunid', 'm61_descr'])->toArray();
    }

    /**
     * @param Request $request
     * @return string[]
     * @throws \Exception
     */
    public function incluir(Request $request)
    {
        $dados = (object)[
            'codigoUnidade' => $request->codigoUnidade,
            'anoPca' => $request->anoPca,
            'itensPlano' => (json_decode(stripslashes(utf8_encode($request->itensPlano)))),
        ];
        $link = "https://pncp.gov.br/app/pca/{$request->documento}/";
        try {
            $response = $this->http->incluir($request->documento, $dados);
            $response = explode('/', $response);
            return [
                'link' => $link . $response[8] . '/' . $response[9]
            ];
        } catch (CompraEditalAvisoExcpetion $e) {
            throw new \Exception($e->getErros());
        }
    }
}

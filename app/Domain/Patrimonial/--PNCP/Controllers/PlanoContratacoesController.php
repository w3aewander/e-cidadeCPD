<?php

namespace App\Domain\Patrimonial\PNCP\Controllers;

use App\Domain\Core\Base\Http\Response\DBJsonResponse;
use App\Domain\Patrimonial\PNCP\Enum\ElementosDespesaEnum;
use App\Domain\Patrimonial\PNCP\Services\PlanoContratacoesService;
use Illuminate\Http\Request;

class PlanoContratacoesController
{
    /**
     * @param PlanoContratacoesService $service
     * @return DBJsonResponse
     */
    public function buscarUnidades(PlanoContratacoesService $service)
    {
        $response = $service->buscarUnidades();
        return new DBJsonResponse($response);
    }

    /**
     * @param Request $request
     * @param PlanoContratacoesService $service
     * @return DBJsonResponse
     * @throws \Exception
     */
    public function incluir(Request $request, PlanoContratacoesService $service)
    {
        $response = $service->incluir($request);
        return new DBJsonResponse($response);
    }

    /**
     * @param Request $request
     * @return DBJsonResponse
     * @throws \ReflectionException
     */
    public function buscarElementoDespesa(Request $request)
    {
        $elementoDespesa = ElementosDespesaEnum::getElementoDespesa($request->categoriaItem);
        return new DBJsonResponse($elementoDespesa);
    }
}

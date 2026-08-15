<?php

namespace App\Domain\Tributario\ISSQN\Controller\InscricaoMunicipal;

use App\Http\Controllers\Controller;
use App\Domain\Core\Base\Http\Response\DBJsonResponse;
use App\Domain\Tributario\ISSQN\Requests\InscricaoMunicipal\BuscarInscricoesMunicipaisRequest;
use App\Domain\Tributario\ISSQN\Services\InscricaoMunicipal\InscricaoMunicipalService;

class InscricaoMunicipalController extends Controller
{
    private $service;

    /**
     * Construtor da classe
     *
     * @return Void
     */
    public function __construct(InscricaoMunicipalService $ordemServicoService)
    {
        $this->service = $ordemServicoService;
    }

    /**
     * Busca uma lista de inscricoes municipais de acordo com os parametros passados
     *
     * @return \Illuminate\Http\Response
     */
    public function getInscricoes(BuscarInscricoesMunicipaisRequest $request)
    {
        $resultados = $this->service->getInscricoes($request);

        return new DBJsonResponse($resultados);
    }

    /**
     * Metodo para buscar as labels para a tabela do frontend
     */
    public function getLabelsInscricoes()
    {
        $resultados = $this->service->getLabelsInscricoes();

        return new DBJsonResponse($resultados);
    }
}

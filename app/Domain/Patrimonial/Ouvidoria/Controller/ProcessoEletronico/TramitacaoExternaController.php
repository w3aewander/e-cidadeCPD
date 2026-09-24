<?php

namespace App\Domain\Patrimonial\Ouvidoria\Controller\ProcessoEletronico;

use App\Domain\Core\Base\Http\Response\DBJsonResponse;
use App\Domain\Patrimonial\Ouvidoria\Services\DocumentoTramitacaoExternaService;
use App\Domain\Patrimonial\Ouvidoria\Services\TramitacaoExternaService;
use App\Domain\Patrimonial\Protocolo\Model\Processo\Processo;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

class TramitacaoExternaController extends Controller
{

    /**
     * @param $codigo
     * @param $cpf_cnpj
     * @return DBJsonResponse
     */
    public function getProcessoComTramitacoes($codigo, $cpf_cnpj)
    {
        $processo = Processo::where("p58_codproc_crypt", "=", $codigo)->first();
        if (!$processo) {
            return new DBJsonResponse("Processo não encontrado!", 404);
        }
        try {
            $processoTramitacao = TramitacaoExternaService::getProcessoTramitacao($processo->p58_codproc, $cpf_cnpj);
            return new DBJsonResponse($processoTramitacao);
        } catch (\Exception $e) {
            Log::error($e->getMessage(), $e->getTrace());
            return new DBJsonResponse([], "Processo  não encontrado!", 404);
        }
    }

    public function documentoTipos($cpf_cnpj)
    {
        try {
            $tiposDocumentos = DocumentoTramitacaoExternaService::tiposDocumentos($cpf_cnpj);
            return new DBJsonResponse($tiposDocumentos);
        } catch (\Exception $e) {
            Log::error($e->getMessage(), $e->getTrace());
            return new DBJsonResponse([], "Tipos de documento não encontrado!", 404);
        }
    }

    public function listaDocumentos(Request $request)
    {
        try {
            $documentos = DocumentoTramitacaoExternaService::listaDocumentosTipo(
                $request->get("cpf-cnpj"),
                $request->get("tipo"),
                $request->get("perPage"),
                $request->get("filtro")
            );
            return new DBJsonResponse($documentos);
        } catch (\Exception $e) {
            Log::error($e->getMessage(), $e->getTrace());
            return new DBJsonResponse([], "Tipos de documento não encontrado!", 404);
        }
    }
}

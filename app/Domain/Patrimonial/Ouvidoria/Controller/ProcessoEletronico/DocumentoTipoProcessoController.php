<?php

namespace App\Domain\Patrimonial\Ouvidoria\Controller\ProcessoEletronico;

use App\Domain\Core\Base\Http\Response\DBJsonResponse;
use App\Domain\Patrimonial\Ouvidoria\Services\DocumentoTipoProcessoService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DocumentoTipoProcessoController extends Controller
{

    private $documentoTipoProcessoService;

    public function __construct(DocumentoTipoProcessoService $documentoTipoProcessoService)
    {
        $this->documentoTipoProcessoService = $documentoTipoProcessoService;
    }

    public function getDocumentosPorTipo(Request $request)
    {
        $documentos = null;

        try {
            $documentos = $this->documentoTipoProcessoService->getDocumentos($request->get("tipo"));
            return new DBJsonResponse($documentos);
        } catch (\Exception $e) {
            Log::error($e->getMessage(), $e->getTrace());
            return new DBJsonResponse([], "Erro ao buscar localidade!", 404);
        }
    }
}

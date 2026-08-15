<?php

namespace App\Domain\Educacao\MatriculaOnline\Controllers;

use App\Domain\Core\Base\Http\Response\DBJsonResponse;
use App\Domain\Educacao\CentralMatriculas\Helpers\ConfiguracaoCentralHelper;
use App\Domain\Educacao\MatriculaOnline\Enums\TiposDocumentosEnum;
use App\Domain\Educacao\MatriculaOnline\Models\Documento;
use App\Domain\Educacao\MatriculaOnline\Resources\DocumentoResource;
use App\Domain\Educacao\MatriculaOnline\Services\DocumentosService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DocumentosController extends Controller
{
    protected $helper;
    public function __construct(ConfiguracaoCentralHelper $helper)
    {
        $this->helper = $helper;
    }

    public function salvar(Request $request)
    {
        $dados = $request->all();
        $id = array_remove($dados, 'tipo');
        $documento = $this->helper->documentos()->update($dados, $id);
        return new DBJsonResponse($documento);
    }

    public function index()
    {
        $tipos = collect(TiposDocumentosEnum::toArrayWithNames())->map(function ($tipo) {
            $tp = (object) $tipo;
            $tp->url = env('CENTRAL_MATRICULAS_URL');
            switch ($tp->value) {
                case TiposDocumentosEnum::LEGISLACAO:
                    $tp->url .= '/documentos/legislacao.pdf';
                    break;
                case TiposDocumentosEnum::TERMOS_USO:
                    $tp->url .= '/documentos/termos_uso.pdf';
                    break;
                case TiposDocumentosEnum::LISTA_ESPERA:
                    $tp->url .= '/documentos/lista_espera.pdf';
                    break;
                case TiposDocumentosEnum::MANUAL:
                    $tp->url .= '/documentos/manual.pdf';
                    break;
            }
            return $tp;
        });

        return new DBJsonResponse($tipos);
    }
}

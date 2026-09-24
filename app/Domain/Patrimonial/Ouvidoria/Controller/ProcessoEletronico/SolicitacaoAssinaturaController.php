<?php

namespace App\Domain\Patrimonial\Ouvidoria\Controller\ProcessoEletronico;

use App\Domain\Core\Base\Http\Response\DBJsonResponse;
use App\Domain\Patrimonial\Ouvidoria\Services\SolicitacaoAssinaturaService;
use App\Domain\Patrimonial\Protocolo\Model\DocumentoSolicitacaoAssinatura;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SolicitacaoAssinaturaController extends Controller
{

    public function index(Request $request)
    {
        $this->validate($request, [
            'codigoProcesso' => 'required',
            'codigoDespacho' => 'required|integer'
        ]);

        return new DBJsonResponse(
            SolicitacaoAssinaturaService::listarDocumentos(
                $request->get("codigoProcesso"),
                $request->get("codigoDespacho")
            )
        );
    }

    public function store(Request $request)
    {

        $this->validate($request, [
            'documentos.*.documento_id' => 'required|numeric',
            'documentos.*.cgm_assinante' => 'required|numeric'
        ]);

        SolicitacaoAssinaturaService::solicitarMuitasAssinaturas(
            $request->get("documentos"),
            auth()->user()->usuarioCgm->cgmlogin
        );

        return new DBJsonResponse([], "Solicitação efetuada com sucesso!");
    }

    /**
     * @throws \BusinessException
     * @throws \Exception
     */
    public function destroy(DocumentoSolicitacaoAssinatura $solicitacao_assinatura)
    {
        if (!empty($solicitacao_assinatura->data_assinatura)) {
            throw new \BusinessException(
                "Não é possível cancelar pois o documento já foi assinado!"
            );
        }

        if (!$solicitacao_assinatura->delete()) {
            throw new \BusinessException(
                "Não foi possível cancelar a solicitação!"
            );
        }

        return new DBJsonResponse([], "Solicitação cancelada com sucesso!");
    }

    public function registrarAssinatura(DocumentoSolicitacaoAssinatura $solicitacao_assinatura, Request $request)
    {
        $this->validate($request, ["data_assinatura" => "required"]);
        $solicitacao_assinatura->setDataAssinatura($request->get("data_assinatura"));
        $solicitacao_assinatura->update();
        return new DBJsonResponse([], "Atualizado com sucesso!");
    }

    /**
     * @throws \BusinessException
     */
    public function solitacoesAssinaturaCpfCnpj($cpf_cnpj)
    {
        return new DBJsonResponse(SolicitacaoAssinaturaService::solitacoesAssinaturaCpfCnpj($cpf_cnpj));
    }
}

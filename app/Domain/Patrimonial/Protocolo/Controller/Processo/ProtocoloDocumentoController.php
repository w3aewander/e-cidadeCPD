<?php

namespace App\Domain\Patrimonial\Protocolo\Controller\Processo;

use App\Domain\Core\Base\Http\Response\DBJsonResponse;
use App\Domain\Patrimonial\Protocolo\Model\Processo\Processo;
use App\Domain\Patrimonial\Protocolo\Model\Processo\Transferencia;
use App\Domain\Patrimonial\Protocolo\Services\CapaDoDocumento;
use App\Domain\Patrimonial\Protocolo\Services\ProtocoloDocumentoService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ProtocoloDocumentoController extends Controller
{

    /**
     * @throws \Exception
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'titular' => 'required|numeric',
            'requerente' => 'required',
            'assunto' => 'required|numeric',
            'observacao' => 'required',
            'instituicao_transferencia' => 'required|numeric',
            'departamento_transferencia' => 'required|numeric',
        ]);

        $protocoloDocumentoService = new ProtocoloDocumentoService();
        $protocoloDocumentoService->gerarProcesso(
            $request->get("assunto"),
            $request->get("titular"),
            $request->get("requerente"),
            $request->get("observacao")
        )->transferir(
            $request->get("departamento_transferencia"),
            auth()->user()->id,
            $request->get("usuario_transferir")
        )->criarCapar()->anexarArquivos($request->get("anexos"));

        $response["processo"] = $protocoloDocumentoService->getProcesso();
        $response["anexos"] = $protocoloDocumentoService->getAnexos();
        return new DBJsonResponse($response);
    }

    /**
     * @throws \Exception
     */
    public function layoutCapaPreview(Request $request)
    {
        $this->validate($request, [
            'titular' => 'required|numeric',
            'requerente' => 'required',
            'assunto' => 'required|numeric',
            'observacao' => 'required',
            'instituicao_transferencia' => 'required|numeric',
            'departamento_transferencia' => 'required|numeric',
        ]);
        $processo = new Processo();
        $processo->setData(date('Y-m-d'));
        $processo->setDepartamento(db_getsession("DB_coddepto"));
        $processo->setInstituicao(db_getsession("DB_instit"));
        $processo->setCgm($request->get("titular"));
        $processo->setNumero("XXXXXXX");
        $processo->setAno(db_getsession("DB_anousu"));
        $processo->setRequerente($request->get("requerente"));
        $processo->setObservacao($request->get("observacao"));
        $processo->setCodigo($request->get("assunto"));
        $transferencia = new Transferencia();
        $transferencia->setUsuario($request->get("usuario_transferir"));
        $transferencia->setDepartamentoRecebimento($request->get("departamento_transferencia"));
        $capaDoDocumento = new CapaDoDocumento(
            $processo,
            $transferencia
        );
        $capaDoDocumento->render();
    }
}

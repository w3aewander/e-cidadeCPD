<?php

namespace App\Domain\Patrimonial\Protocolo\Controller\Processo;

use App\Domain\Configuracao\Helpers\StorageHelper;
use App\Domain\Patrimonial\Protocolo\Model\Cgm;
use App\Domain\Patrimonial\Protocolo\Model\Mensageria;
use App\Domain\Patrimonial\Protocolo\Services\MensageriaService;
use ECidade\Patrimonial\Protocolo\Servicos\AndamentoProcessoService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Domain\Core\Base\Http\Response\DBJsonResponse;
use App\Domain\Patrimonial\Protocolo\Model\Processo\Processo;
use App\Domain\Patrimonial\Protocolo\Repository\Processo\ProcessoRepository;
use Illuminate\Support\Facades\Validator;

class ProcessoController extends Controller
{
    /**
     * Construtor da classe
     *
     * @return void
     */
    public function __construct(ProcessoRepository $processoRepository)
    {
        $this->repository = $processoRepository;
    }


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return new DBJsonResponse($this->repository->findAll());
    }



    public function rotulosPesquisa()
    {
        return new DBJsonResponse($this->repository->rotulosPesquisa());
    }

    /**
     * @param Request $request
     * @return DBJsonResponse
     * @throws Exception
     */
    public function getProcesso(Request $request)
    {
        try {
            $porPagina = $request->porPagina ? $request->porPagina : 10;
            $p58_codproc = $request->p58_codproc ? $request->p58_codproc : "";
            $p58_dtproc = $request->p58_dtproc ? $request->p58_dtproc : "";
            $p58_requer = $request->p58_requer ? $request->p58_requer : "";
            $p58_obs = $request->p58_obs ? $request->p58_obs : "";
            $p58_ano = $request->p58_ano ? $request->p58_ano : "";

            $processo = $this->repository->getByParams(
                $p58_dtproc,
                $p58_codproc,
                $p58_requer,
                $p58_obs,
                $p58_ano,
                $porPagina
            )->toArray();
            return new DBJsonResponse($processo);
        } catch (\Exception $e) {
            return new DBJsonResponse([], $e->getMessage(), 400);
        }
    }

    public function getMensagens(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'processo' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $mensageriaService = new MensageriaService();
        $mensagens = $mensageriaService->getMensagensProcesso($request->all());
        return new DBJsonResponse($mensagens);
    }

    /**
     * @throws \Exception
     */
    public function sendMensagem(Request $request)
    {
        $mensageriaService = new MensageriaService();
        return $mensageriaService->saveMensagem($request->all());
    }

    public function criarDespacho(Request $request)
    {
        $mensagem = json_decode($request->get('mensagem'));
        $despachoAnexos = collect();

        if (isset($mensagem->documentos)) {
            foreach ($mensagem->documentos as $anexo) {
                $caminhoArquivo = StorageHelper::downloadArquivo($anexo->p124_codigo_storage);
                $anexo = (object)[
                    'caminho' => $caminhoArquivo,
                    'descricao' => $anexo->p124_nome_documento,
                ];
                $despachoAnexos->push($anexo);
            }
        }

        $parametros = (object)[
            'despachoInterno' => $mensagem->p123_mensagem,
            'codigoProcesso' => $mensagem->p123_processo,
            'despachoPublico' => $request->get('publico') ? 't' : 'false',
            'despachoAnexos' => json_encode($despachoAnexos),
            'origemMensagem' => true
        ];

        $andamentoProcesso = new AndamentoProcessoService($parametros);
        $retorno = $andamentoProcesso->despachar();

        if ($retorno != null) {
            return new DBJsonResponse(
                ['mensagem' => 'Sucesso ao inserir despacho.', 'codigoDespacho' => $retorno->codigo, 'erro' => false]
            );
        } else {
            return new DBJsonResponse(['mensagem' => 'Erro ao criar despacho.', 'erro' => true]);
        }
    }

    public function vincularDespachoMensagem(Request $request)
    {
        $codigoDespacho = (int) $request->get('codigoDespacho');
        $mensageriaService = new MensageriaService();
        $retorno = $mensageriaService->vincularDespachoMensagem($codigoDespacho, $request->get('idMensagem'));

        if ($retorno) {
            return new DBJsonResponse(['mensagem' => 'Sucesso ao inserir e vincular despacho.', 'erro' => false]);
        } else {
            return new DBJsonResponse(['mensagem' => 'Erro ao inserir e vincular despacho.', 'erro' => true]);
        }
    }

    public function getCgmByCpfCnpj(Request $request)
    {
        $cpfcnpj = (String) $request->get('cpfcnpj');
        $cgm = Cgm::where('z01_cgccpf', '=', $cpfcnpj)->first();

        if (empty($cgm)) {
            return new DBJsonResponse(['mensagem' => 'Não foi encontrado CGM para esse CPF/CNPJ', 'erro' => true]);
        }

        return new DBJsonResponse(['mensagem' => 'Dados encontrados', 'cgm' => $cgm->z01_numcgm,'erro' => false]);
    }
}

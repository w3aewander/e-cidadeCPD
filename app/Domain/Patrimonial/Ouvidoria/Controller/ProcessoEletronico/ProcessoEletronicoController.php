<?php


namespace App\Domain\Patrimonial\Ouvidoria\Controller\ProcessoEletronico;

use App\Domain\Configuracao\Helpers\StorageHelper;
use App\Domain\Core\Base\Http\Response\DBJsonResponse;
use App\Domain\Patrimonial\Ouvidoria\Model\TipoProcessoDepartamento;
use App\Domain\Patrimonial\Ouvidoria\Services\ProcessoEletronicoService;
use App\Domain\Patrimonial\Ouvidoria\Services\RecadastramentoService;
use App\Domain\Patrimonial\Protocolo\Services\MensageriaService;
use App\Http\Controllers\Controller;
use ECidade\Patrimonial\Ouvidoria\Externa\WebService\ProcessoEletronico\Solicitacao;
use ECidade\Patrimonial\Protocolo\TipoProcesso\Repository\TipoProcesso as TipoProcessoRepository;
use Exception;
use Illuminate\Http\Request;

class ProcessoEletronicoController extends Controller
{
    private $processoEletronicoService;

    public function __construct(ProcessoEletronicoService $processoEletronicoService)
    {
        $this->processoEletronicoService = $processoEletronicoService;
    }

    public function mensagens(Request $request)
    {
        $this->validate($request, [
            'codigoProcesso' => 'required',
        ]);
        return new DBJsonResponse($this->processoEletronicoService->getMensagens($request->all()));
    }

    public function saveVisualizacao($p78_sequencial)
    {
        return new DBJsonResponse($this->processoEletronicoService->saveVisualizacao($p78_sequencial));
    }


    public function menu(Request $request)
    {
        $this->validate($request, [
            'formas_reclamacao' => 'required'
        ]);

        return new DBJsonResponse($this->processoEletronicoService->getMenu(
            $request->get('formas_reclamacao'),
            $request->get("cpf_cnpj")
        ));
    }

    public function solicitacaoDeAtendimento(Request $request)
    {
        try {
            $this->validate($request, [
                'metadados' => 'required',
                'tipoprocesso' => 'required|integer',
                'requerente_nome' => 'required',
                'client_atendimento_id' => 'required|integer'
            ]);

            $tipoProcesso = TipoProcessoRepository::getInstancia()->getByCodigo($request->get('tipoprocesso'));

            if (empty($tipoProcesso)) {
                throw new \Exception("Tipo de processo não encontrado!");
            }

            $tipoProcessoDepartamento = TipoProcessoDepartamento::where(
                "p41_tipoproc",
                $request->get('tipoprocesso')
            )->first();

            if (!$tipoProcessoDepartamento) {
                throw new \Exception("Departamento não encontrado!");
            }

            if ($tipoProcesso->isIdentificado() and
                (empty($request->get('requerente_cpf'))
                 or empty($request->get('requerente_nome'))
                )
            ) {
                throw new \Exception(
                    "Erro não foi possível identificar o requerente!"
                );
            }

            $metadados = $request->input('metadados');
            if (!empty($request->get('anexos'))) {
                $arquivos = $this->uploadAnexosJsontToStorage($request->get('anexos'));
                $metadados = $this->addAnexosNoJson($request->input('metadados'), $arquivos);
            }

            $solicitacao = new Solicitacao();
            $solicitacao->setMetadados($metadados);
            $solicitacao->setCodigoDepartamento($tipoProcessoDepartamento->getDepartamentoCodigo());
            $solicitacao->setTipoProcesso($tipoProcesso->getCodigo());
            $solicitacao->setRequerenteNome($request->get('requerente_nome'));
            $solicitacao->setRequerenteCpf($request->get('requerente_cpf'));
            $solicitacao->setCodigoAtendimentoAnterior($request->get('codigo_atendimento_anterior'));
            $solicitacao->setClientAPPAtendimentoID($request->get('client_atendimento_id'));
            $response = (array)$solicitacao->salvar();

            if (!$response["sucesso"]) {
                foreach ($arquivos as $arquivo) {
                    StorageHelper::deleteArquivo($arquivo["id"]);
                }
            }

            return new DBJsonResponse($response);
        } catch (Exception $ex) {
            return new DBJsonResponse([], $ex->getMessage(), 400);
        }
    }

    public function consultaServidor(Request $request)
    {
        $this->validate($request, ['cpf' => 'required']);
        $recadastramento = new RecadastramentoService();
        return new DBJsonResponse(
            collect($recadastramento->getDadosServidorCpf($request->get('cpf')))->first(),
            ""
        );
    }

    public function consultarDependentesServidor(Request $request)
    {
        $this->validate($request, ['cpf' => 'required']);
        $recadastramento = new RecadastramentoService();
        return new DBJsonResponse(
            $recadastramento->getDependentesServidorCpf($request->get('cpf')),
            ""
        );
    }

    public function verificaServidorPossuiPermissaoRecadastramento($cpf, $tipoProcesso)
    {
        $recadastramento = new RecadastramentoService();
        return new DBJsonResponse(
            collect(
                $recadastramento->verificaServidorPossuiPermissaoRecadastramento($cpf, $tipoProcesso)
            )->first()
        );
    }

    public function consultaAtendimentoPorId($id)
    {
        return new DBJsonResponse($this->processoEletronicoService->getAtendimentoPorId($id));
    }

    public function consultaAtendimentosPorCpfCnpj(Request $request)
    {
        return new DBJsonResponse($this->processoEletronicoService->getAtendimentoPorCpfCnpj(
            $request->get("cpf-cnpj"),
            $request->get("perPage"),
            $request->get("filtro")
        ));
    }

    public function detalheProcesso($codigoProcesso)
    {
        return new DBJsonResponse(
            $this->processoEletronicoService->getDetalheProcesso($codigoProcesso)
        );
    }

    /**
     * @throws Exception
     */
    public function mensagemOuvidoria(Request $request)
    {
        $this->validate($request, [
            'processo' => 'required',
            'mensagem' => 'required'
        ]);

        $mensageriaService = new MensageriaService();
        return $mensageriaService->saveMensagem($request->all());
    }

    public function personasCpfCnpj($cpfCnpj)
    {
        return new DBJsonResponse($this->processoEletronicoService->personas($cpfCnpj));
    }

    public function formulario($tipoProcesso)
    {
        return new DBJsonResponse($this->processoEletronicoService->formulario($tipoProcesso));
    }

    public function stringJsonToArray($metadados)
    {
        $json_sem_barras = str_replace('\\', '', $metadados);
        $json_sem_barras = utf8_encode($json_sem_barras);
        return json_decode($json_sem_barras, true);
    }

    public function saveBase64ToFile($base64Data, $fileName)
    {
        $fileData = base64_decode($base64Data);
        $tmpPath = base_path('tmp');
        $filePath = $tmpPath . '/' . $fileName;
        file_put_contents($filePath, $fileData);
        return $filePath;
    }

    public function uploadAnexosJsontToStorage($anexosJson)
    {
        $arquivos = $this->stringJsonToArray($anexosJson);

        foreach ($arquivos as &$arquivo) {
            $base64Array = explode(',', $arquivo["conteudo"]);
            $base64Content = $base64Array[1];
            $documento = StorageHelper::uploadArquivo(
                $this->saveBase64ToFile($base64Content, $arquivo["descricao"]),
                null,
                null
            );
            $arquivo["id"] = $documento->id;
        }
        return $arquivos;
    }

    public function addAnexosNoJson($metadados, $arquivos)
    {
        $jsonArray = $this->stringJsonToArray($metadados);
        foreach ($jsonArray["secoes"] as &$secoes) {
            if ($secoes["nome"] === "anexos") {
                foreach ($arquivos as $arquivo) {
                    $secoes["resposta"][] = array(
                        'codigo' => $arquivo["id"],
                        'descricao' => $arquivo["descricao"],
                        'nome' => $arquivo["nome"]
                    );
                }
            }
        }

        return json_encode($jsonArray);
    }
}

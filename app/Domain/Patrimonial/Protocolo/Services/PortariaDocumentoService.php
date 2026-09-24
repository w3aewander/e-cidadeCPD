<?php

namespace App\Domain\Patrimonial\Protocolo\Services;

use App\Domain\Configuracao\Helpers\StorageHelper;
use App\Domain\Configuracao\Usuario\Models\Usuario;
use App\Domain\Patrimonial\Protocolo\Model\AtividadeExecucao;
use App\Domain\Patrimonial\Protocolo\Model\DocumentoAndamento;
use App\Domain\Patrimonial\Protocolo\Model\Processo\Processo;
use App\Domain\Patrimonial\Protocolo\Model\TipoProcesso;
use cl_portaria;
use DBPessoal;
use ECidade\RecursosHumanos\RH\Portaria\PortariaPDF;
use Exception;
use Portaria;

class PortariaDocumentoService extends DocumentoAndamentoService
{
    /**
     * @var Portaria
     */
    protected $portaria;
    /**
     * @var TipoProcesso
     */
    protected $tipoProcesso;

    /**
     * @param Portaria $portaria
     * @throws Exception
     */
    public function __construct(Portaria $portaria)
    {
        parent::__construct();
        $this->portaria = $portaria;

        $codigoRelatorio = $this->portaria->getCodigoModeloRelatorioIndividual();
        if (empty($codigoRelatorio)) {
            $mensagemErro = sprintf(
                "%s %s",
                "Não foi configurado Modelo de Documento Individual",
                "para o Tipo de Portaria {$this->portaria->getPortariatipo()}"
            );
            throw new Exception($mensagemErro);
        }

        $this->tipoProcesso = TipoProcesso::query()->relatorio($codigoRelatorio)->tipoDocumento(7)->first();
        if (is_null($this->tipoProcesso)) {
            throw new Exception("Tipo de processo não configurado para Documento Portaria");
        }
        $documento = DocumentoAndamento::where('p116_codigo_origem', $this->portaria->getSequencial())->first();
        if (!is_null($documento)) {
            $this->setDocumento($documento);
        }
    }

    /**
     * @param $arquivo
     * @param $uuid
     * @return Processo
     * @throws Exception
     *
     * Gerar processo para Andamento do Documento e Assinaturas
     */
    public function gerar($arquivo, $uuid, $flagAlteracao = null)
    {
        $descricao = "Portaria {$this->portaria->getNumeroportaria()}/{$this->portaria->getAnousu()}";
        if ($flagAlteracao != null) {
            $this->criarNovoDocumentoAndamento($this->portaria->getSequencial(), $descricao, $uuid, $flagAlteracao);
        } else {
            $this->processo = $this->gerarProcessoDocumento($this->tipoProcesso, $descricao);
            $this->criarNovoDocumentoAndamento($this->portaria->getSequencial(), $descricao, $uuid);
        }
        $this->buscarUsuariosPermissoes();
        $this->vincularDocumento($arquivo, "Gerado");
        $this->salvarDocumentoAndamento();
        $this->salvarMovimentacao();
        return $this->processo;
    }

    /**
     * @return object
     * @throws Exception
     */
    public function montarObjetoTela()
    {
        $this->documento->atividadeAtual;
        $this->documento->proximaAtividade;
        $documento = $this->documento->toArray();
        $documento['consulta'] = (object)[
//            'name' => 'db_iframe_pesquisaempenho',
//            'funcao' => "func_empempenho001.php?e60_numemp={$this->empenho->getNumero()}",
//            'label' => 'Dados do Empenho',
        ];

        $documento['descricao_extra'] = $this->portaria->getServidor()->getCgm()->getNome();
        $documento['documento_estorage'] = $this->documento->processoDocumento->p01_documento;
        $documento['isDevolvido'] = $this->isDevolvido();

        $linhasDetalhes = [];
        $linhasDetalhes[0] = [
            [
                "label" => 'Matrícula:',
                "valor" => $this->portaria->getServidor()->getMatricula()
            ]
        ];
        $linhasDetalhes[1] = [
            [
                "label" => 'Nome:',
                "valor" => $this->portaria->getServidor()->getCgm()->getNome()
            ]
        ];
        $linhasDetalhes[2] = [
            [
                "label" => 'Tipo:',
                "valor" => $this->portaria->getAssentamento()->getInstanciaTipoAssentamento()->getDescricao()
            ]
        ];
        $linhasDetalhes[3] = [
            [
                "label" => 'Amparo Legal: ',
                "valor" => $this->portaria->getAmparolegal()
            ]
        ];
        $linhasDetalhes[4] = [
            [
                "label" => 'Observação: ',
                "valor" => $this->portaria->getAssentamento()->getHistorico()
            ]
        ];

        $documento['andamento'] = $this->getAndamento();
        $documento['detalhes'] = $linhasDetalhes;

        return (object)$documento;
    }

    /**
     * @return void
     * @throws Exception
     */
    public function buscarUsuariosPermissoes()
    {
        $matricula = $this->portaria->getServidor()->getMatricula();
        $iAnoFolha    = DBPessoal::getAnoFolha();

        $daoRhpessoal = new \cl_rhpessoal();
        $sqlOrgao = $daoRhpessoal->sqlLotacaoOrgao($matricula, 'rh26_orgao as orgao, rh26_unidade as unidade');
        $rsOrgao = db_query($sqlOrgao);
        if (!$rsOrgao || pg_num_rows($rsOrgao) == 0) {
            throw new Exception("Não foi possível buscar Orgão da Lotação da Matrícula {$matricula}");
        }
        $dadosLotacao = pg_fetch_object($rsOrgao);

        $usuario = new Usuario();
        $usuario->join('db_usupermemp', 'db_usuarios.id_usuario', '=', 'db_usupermemp.db21_id_usuario')
            ->join('db_permemp', 'db_permemp.db20_codperm', '=', 'db_usupermemp.db21_codperm')
            ->join('orctiporec', 'orctiporec.o15_codigo', '=', 'db_permemp.db20_codigo')
            ->join('db_permemp_atividadesexecucao', 'db69_codperm', 'db20_codperm')
            ->where('db69_tipoprocesso', '=', $this->tipoProcesso->p51_codigo)
            ->where('db20_anousu', '=', $iAnoFolha)
            ->where('db20_orgao', '=', $dadosLotacao->orgao)
            ->whereIn("db20_unidade", [$dadosLotacao->unidade, 0])
            ->distinct()
            ->get(['db_usuarios.*', 'db69_atividadesexecucao'])
            ->map(function ($usuarioPermitido) {
                $atividadeExecucao = AtividadeExecucao::find($usuarioPermitido->db69_atividadesexecucao);
                $this->adicionarUsuario($usuarioPermitido, $atividadeExecucao);
            });
    }

    /**
     * @return int|\stdclass
     * @throws Exception
     */
    public function gerarArquivo($codigoRelatorio, $filtros, $flagAlteracao)
    {
        $daoPortaria = new cl_portaria();
        $rsAssentamento = db_query($daoPortaria->sql_query_assentamento_servidor($this->portaria->getSequencial()));
        if (!$rsAssentamento) {
            throw new Exception("Erro ao buscar Assentamento do Servidor");
        }
        $dadosAssentamento = pg_fetch_assoc($rsAssentamento);

        $uuid = \Ramsey\Uuid\Uuid::uuid4();

        $portariaPdf = new PortariaPDF($codigoRelatorio, $filtros);
        $arquivoPdf = $portariaPdf->emitir($uuid);

        $metadata = (object)[
            "tipo_documento" => "Portaria",
            "numero" => $dadosAssentamento['h31_numero'] . "/" . $dadosAssentamento['h31_anousu'],
            "matricula_servidor" => $dadosAssentamento['h16_regist'],
            "id_assentamento" => $dadosAssentamento['h16_codigo'],
            "qrcode" => $uuid,
        ];
        $arquivo = StorageHelper::uploadArquivo($arquivoPdf, [], false, $metadata);

        $this->gerar($arquivo, $uuid, $flagAlteracao);
        return [$arquivo->id, $arquivoPdf];
    }
}

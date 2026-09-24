<?php

namespace App\Domain\Patrimonial\Protocolo\Services;

use App\Domain\Patrimonial\Protocolo\Model\Processo\AndamentoProcessoInterno;
use App\Domain\Patrimonial\Protocolo\Repository\Processo\ProcessoAndamentoInternoRepository;
use App\Domain\Patrimonial\Protocolo\Repository\Processo\ProcessoTransferenciaAndamentoRepository;
use App\Domain\Patrimonial\Protocolo\Repository\Processo\ProcessoTransferenciaRepository;
use App\Domain\Patrimonial\Protocolo\Repository\Processo\ArquivamentoAndamentoRepository;
use App\Domain\Patrimonial\Protocolo\Repository\Processo\ArquivamentoProcessoRepository;
use App\Domain\Patrimonial\Protocolo\Repository\Processo\ProcessoAndamentoRepository;
use App\Domain\Patrimonial\Protocolo\Repository\Processo\ProcessoOuvidoriaRepository;
use App\Domain\Patrimonial\Protocolo\Repository\Processo\ProcessoDocumentoRepository;
use App\Domain\Patrimonial\Protocolo\Repository\Processo\AndamentoPadraoRepository;
use App\Domain\Patrimonial\Protocolo\Repository\Processo\TransferenciaRepository;
use App\Domain\Patrimonial\Protocolo\Repository\Processo\ArquivamentoRepository;
use App\Domain\Patrimonial\Protocolo\Repository\Processo\ProcessoRepository;
use App\Domain\Patrimonial\Protocolo\Model\Processo\ProcessoTransferenciaAndamento;
use App\Domain\Patrimonial\Protocolo\Model\Processo\ProcessoTransferencia;
use App\Domain\Patrimonial\Protocolo\Model\Processo\ArquivamentoAndamento;
use App\Domain\Patrimonial\Protocolo\Model\Processo\ArquivamentoProcesso;
use App\Domain\Patrimonial\Protocolo\Model\Processo\ProcessoAndamento;
use App\Domain\Patrimonial\Protocolo\Model\Processo\ProcessoDocumento;
use App\Domain\Patrimonial\Protocolo\Model\Processo\ProcessoOuvidoria;
use App\Domain\Patrimonial\Protocolo\Model\Processo\Transferencia;
use App\Domain\Patrimonial\Protocolo\Model\Processo\Arquivamento;
use App\Domain\Patrimonial\Protocolo\Model\Processo\Processo;
use ECidade\Lib\Session\DefaultSession;
use App\Domain\Configuracao\Helpers\StorageHelper;
use ECidade\Patrimonial\Protocolo\Servicos\AndamentoProcessoService;
use Exception;
use \processoProtocolo;
use ProcessoDocumento as ProcessoDocumentoLegacy;

/**
 * Classe ProcessoService
 * Faz os tramites referentes ao processo
 */
class ProcessoService
{
    /**
     * @var ProcessoRepository
     */
    private $processoRepository;
    /**
     * @var ArquivamentoRepository
     */
    private $arquivamentoRepository;
    /**
     * @var TransferenciaRepository
     */
    private $transferenciaRepository;
    /**
     * @var AndamentoPadraoRepository
     */
    private $andamentoPadraoRepository;
    /**
     * @var ProcessoDocumentoRepository
     */
    private $processoDocumentoRepository;
    /**
     * @var ProcessoAndamentoRepository
     */
    private $processoAndamentoRepository;
    /**
     * @var ProcessoOuvidoriaRepository
     */
    private $processoOuvidoriaRepository;
    /**
     * @var ArquivamentoProcessoRepository
     */
    private $arquivamentoProcessoRepository;
    /**
     * @var ArquivamentoAndamentoRepository
     */
    private $arquivamentoAndamentoRepository;
    /**
     * @var ProcessoTransferenciaRepository
     */
    private $processoTransferenciaRepository;
    /**
     * @var ProcessoTransferenciaAndamentoRepository
     */
    private $processoTransferenciaAndamentoRepository;

    public $isLegacy = true;
    /**
     * @var ProcessoAndamentoInternoRepository
     */
    private $processoAndamentoInternoRepository;
    /**
     * @var AndamentoProcessoService
     */
    private $andamentoProcesoService;

    /**
     * Construtor da classe
     */
    public function __construct()
    {
        $this->processoRepository = new ProcessoRepository();
        $this->arquivamentoRepository
                                  = new ArquivamentoRepository();
        $this->transferenciaRepository
                                  = new TransferenciaRepository();
        $this->andamentoPadraoRepository
                                  = new AndamentoPadraoRepository();
        $this->processoDocumentoRepository
                                  = new ProcessoDocumentoRepository();
        $this->processoAndamentoRepository
                                  = new ProcessoAndamentoRepository();
        $this->processoOuvidoriaRepository
                                  = new ProcessoOuvidoriaRepository();
        $this->arquivamentoProcessoRepository
                                  = new ArquivamentoProcessoRepository();
        $this->arquivamentoAndamentoRepository
                                  = new ArquivamentoAndamentoRepository();
        $this->processoTransferenciaRepository
                                  = new ProcessoTransferenciaRepository();
        $this->processoTransferenciaAndamentoRepository
                                  = new ProcessoTransferenciaAndamentoRepository();
        $this->processoAndamentoInternoRepository
                                  = new ProcessoAndamentoInternoRepository();
        $this->andamentoProcesoService = new AndamentoProcessoService(new \stdClass());
    }

    /**
     * @return AndamentoProcessoService
     */
    public function getAndamentoProcesoService()
    {
        return $this->andamentoProcesoService;
    }

    /**
     * Função que salva um documento
     *
     * @param  ProcessoDocumento  $processoDocumento
     *
     * @throws Exception
     */
    public function salvarDocumento(ProcessoDocumento $processoDocumento)
    {
        return $this->processoDocumentoRepository->persist($processoDocumento);
    }

    /**
     * Função que vincula um processo a um atendimento da ouvidoria
     *
     * @param  ProcessoOuvidoria  $processoOuvidoria
     *
     * @throws Exception
     */
    public function salvarProcessoOuvidoria(ProcessoOuvidoria $processoOuvidoria)
    {
        $this->processoOuvidoriaRepository->persist($processoOuvidoria);
    }

    /**
     * Função que salva um processo
     *
     * @param  Processo  $processo
     *
     * @return Processo
     * @throws Exception
     */
    public function salvarProcesso(
        Processo $processo,
        $siglaDoDocumentoNaNumeracao = false
    ) {
        return $this->processoRepository->persist(
            $processo,
            $siglaDoDocumentoNaNumeracao
        );
    }


    /**
     * Retorna o departamento do andamento padrao
     *
     * @param  Processo  $processo
     * @param  boolean  $proximoDepartamento
     *
     * @return integer $departamento
     * @throws Exception
     */
    public function getDepartamentoAndamentoPadrao(
        Processo $processo,
        $proximoDepartamento = true
    ) {
        $posicaoAtual
                 = $this->processoTransferenciaRepository->getPosicaoAtualProcesso(
                     $processo
                 );
        $posicao = ($proximoDepartamento) ? $posicaoAtual + 1 : $posicaoAtual;
        $departamento
                 = $this->andamentoPadraoRepository->getDepartamentoAndamentoPadrao(
                     $processo->getCodigo(),
                     $posicao
                 );

        if (! $departamento) {
            throw new Exception('Departamento padrão não encontrado!');
        }

        return $departamento;
    }

    /**
     * Função que transfere um processo
     *
     * @param  Processo  $processo
     * @param  int  $departamentoRecebimento
     * @param  int  $departamento
     * @param  int  $usuario
     * @param  int  $usuarioRecebimento
     *
     * @return Transferencia $transferencia
     * @throws Exception
     */
    public function transferir(
        Processo $processo,
        $departamentoRecebimento,
        $departamento,
        $usuario,
        $usuarioRecebimento = 0
    ) {
        $transferencia         = new Transferencia();
        $processoTransferencia = new ProcessoTransferencia();
        $data                  = DefaultSession::getInstance()->get(
            DefaultSession::DB_DATAUSU
        );

        $transferencia->setUsuario($usuario);
        $transferencia->setDepartamento($departamento);
        $transferencia->setUsuarioRecebimento($usuarioRecebimento);
        $transferencia->setDepartamentoRecebimento($departamentoRecebimento);
        $transferencia->setHora(date("H:i"));
        $transferencia->setData(date('Y-m-d', $data));

        $this->transferenciaRepository->persist($transferencia);

        $processoTransferencia->setCodigoTransferencia(
            $transferencia->getCodigo()
        );
        $processoTransferencia->setCodigoProcesso(
            $processo->getCodigoProcesso()
        );

        $this->processoTransferenciaRepository->persist($processoTransferencia);

        return $transferencia;
    }

    /**
     * Função que recebe um processo
     *
     * @param  Processo  $processo
     * @param  Transferencia  $transferencia
     * @param  string  $despacho
     *
     * @return ProcessoAndamento
     * @throws Exception
     */
    public function receber(
        Processo $processo,
        Transferencia $transferencia,
        $despacho
    ) {
        $processoAndamento              = new ProcessoAndamento();
        $usuarioRecebimento             = $transferencia->getUsuarioRecebimento(
        );
        $processoTransferenciaAndamento = new ProcessoTransferenciaAndamento();

        $processoAndamento->setProcesso($processo->getCodigoProcesso());
        $processoAndamento->setUsuario(
            $usuarioRecebimento != 0
                ? $usuarioRecebimento
                : db_getsession(
                    "DB_id_usuario"
                )
        );
        $processoAndamento->setData($transferencia->getData());
        $processoAndamento->setDespacho($despacho);
        $processoAndamento->setDepartamento(
            $transferencia->getDepartamentoRecebimento()
        );
        $processoAndamento->setPublico($processo->getPublico());
        $processoAndamento->setHora($transferencia->getHora());

        $this->processoAndamentoRepository->persist($processoAndamento);

        $processoTransferenciaAndamento->setCodigoAndamento(
            $processoAndamento->getCodigo()
        );
        $processoTransferenciaAndamento->setCodigoTransferencia(
            $transferencia->getCodigo()
        );

        $this->processoTransferenciaAndamentoRepository->persist(
            $processoTransferenciaAndamento
        );

        $processo->setCodigoAndamento($processoAndamento->getCodigo());

        if ($this->isLegacy) {
            $this->salvarProcesso($processo);
        } else {
            $processo->saveOrFail();
        }

        return $processoAndamento;
    }

    /**
     * @param  Processo  $processo
     * @param  Transferencia  $transferencia
     * @param $despacho
     *
     * @return ProcessoAndamento
     * @throws Exception
     */
    public function andamentoSemReceber(
        Processo $processo,
        Transferencia $transferencia,
        $despacho
    ) {
        $processoAndamento  = new ProcessoAndamento();
        $usuarioRecebimento = $transferencia->getUsuarioRecebimento();
        $processoAndamento->setProcesso($processo->getCodigoProcesso());
        $processoAndamento->setUsuario(
            $usuarioRecebimento != 0 ? $usuarioRecebimento
                : $transferencia->getUsuario()
        );
        $processoAndamento->setData($transferencia->getData());
        $processoAndamento->setDespacho($despacho);
        $processoAndamento->setDepartamento(
            $transferencia->getDepartamentoRecebimento()
        );
        $processoAndamento->setPublico($processo->getPublico());
        $processoAndamento->setHora($transferencia->getHora());
        $processoAndamento = $this->processoAndamentoRepository->persist(
            $processoAndamento
        );
        $processo->setCodigoAndamento($processoAndamento->getCodigo());
        $this->salvarProcesso($processo);

        return $processoAndamento;
    }


    /**
     * @param  ProcessoAndamento  $andamento
     * @param $publico
     * @param $tipoDespacho
     *
     * @return AndamentoProcessoInterno
     * @throws Exception
     */
    public function despachar(
        ProcessoAndamento $andamento,
        $despacho,
        $publico = false,
        $tipoDespacho = 1
    ) {
        $processoAndamentoInterno = new AndamentoProcessoInterno();
        $processoAndamentoInterno->setCodigoAndamento($andamento->getCodigo());
        $processoAndamentoInterno->setCodigoUsuario(
            db_getsession("DB_id_usuario")
        );
        $processoAndamentoInterno->setData(date("Y-m-d"));
        $processoAndamentoInterno->setHora(date("H:i"));
        $processoAndamentoInterno->setPublico($publico);
        $processoAndamentoInterno->setTransint(false);
        $processoAndamentoInterno->setTipoDespacho($tipoDespacho);
        $processoAndamentoInterno->setDespacho($despacho);

        return $this->processoAndamentoInternoRepository->persist(
            $processoAndamentoInterno
        );
    }


    /**
     * @throws \DBException
     * @throws \ParameterException
     * @throws Exception
     */
    public function salvarPdfDespacho(
        Processo $processo,
        AndamentoProcessoInterno $andamentoProcessoInterno
    ) {
        $mostrarPDF              = true;
        $codproc                 = $processo->getCodigoProcesso();
        $iCodigoAndamentoDepacho = $processo->getCodigoAndamento();
        $codprocandamint         = $andamentoProcessoInterno->getSequencial();
        $caminho                 = "";

        require_once("pro2_despachointer002.php");

        $storageConfig = StorageHelper::getStorageConfig();
        $allowed       = array();

        if (! empty($storageConfig->client_id_ouvidoria)) {
            $allowed[] = $storageConfig->client_id_ouvidoria;
        }

        $metadata                     = new \stdClass();
        $metadata->tipo_documento     = "processo";
        $metadata->numero_do_processo = $processo->getNumero()."/"
                                        .$processo->getAno();
        $metadata->requerente         = $processo->getRequerente();
        $rsProcessoOuvidoria          = db_query(
            "
                    SELECT
                    *
                    FROM processoouvidoria
                    INNER JOIN ouvidoriaatendimento
                    ON processoouvidoria.ov09_ouvidoriaatendimento = ouvidoriaatendimento.ov01_sequencial
                    WHERE
                    ov09_protprocesso = {$processo->getCodigoProcesso()}
         "
        );

        $atendimentoOuvidoria = pg_fetch_object($rsProcessoOuvidoria);

        if (! empty($atendimentoOuvidoria)) {
            $numeroAtendimento            = $atendimentoOuvidoria->ov01_numero;
            $numeroAtendimento            .= "/"
                                             .$atendimentoOuvidoria->ov01_anousu;
            $metadata->numero_atendimento = $numeroAtendimento;
            $metadata->data_hora          = $andamentoProcessoInterno->getData()
                                            ." "
                                            .$andamentoProcessoInterno->getHora(
                                            );
        }

        $usuarioSistema                     = new \UsuarioSistema(
            db_getsession("DB_id_usuario")
        );
        $metadata->codigo_usuario_aprovacao = $usuarioSistema->getCodigo();
        $metadata->login_usuario_aprovacao  = $usuarioSistema->getLogin();
        $metadata->codigoDespacho
                                            = $andamentoProcessoInterno->getSequencial(
                                            );
        $processoProtocolo                  = new processoProtocolo(
            $processo->getCodigoProcesso()
        );

        $ordem             = ProcessoDocumentoLegacy::getLastOrdemProcesso(
            $codproc
        ) + 1;
        $processoDocumento = new ProcessoDocumentoLegacy(null);

        $processoDocumento->setProcandamint(
            $andamentoProcessoInterno->getSequencial()
        );
        $processoDocumento->setDescricao("Despacho do processo {$codproc}");
        $processoDocumento->setProcessoProtocolo($processoProtocolo);
        $processoDocumento->setUsuario($usuarioSistema);
        $processoDocumento->setCaminhoArquivo($caminho);
        $processoDocumento->setStorage(true);
        $processoDocumento->setOrdem($ordem);
        $processoDocumento->setNomeDocumento(
            "Despacho do processo {$codproc}.pdf"
        );
        $processoDocumento->setOID(
            StorageHelper::uploadArquivo($caminho, $allowed, true, $metadata)
        );

        $processoDocumento->salvar();
        return true;
    }


    /**
     * Função que arquiva um processo
     *
     * @param  Processo  $processo
     * @param $motivo
     * @param  ProcessoAndamento|null  $processoAndamento
     *
     * @throws Exception
     */
    public function arquivar(
        Processo $processo,
        $motivo,
        ProcessoAndamento $processoAndamento = null
    ) {
        $defaultSession = DefaultSession::getInstance();
        $data           = $defaultSession->get(DefaultSession::DB_DATAUSU);
        $usuario        = $defaultSession->get(DefaultSession::DB_ID_USUARIO);
        $departamento   = $defaultSession->get(DefaultSession::DB_CODDEPTO);
        $codigoProcesso = $processo->getCodigoProcesso();

        $arquivamento          = new Arquivamento();
        $arquivamentoProcesso  = new ArquivamentoProcesso();
        $arquivamentoAndamento = new ArquivamentoAndamento();

        $arquivamento->setProcesso($codigoProcesso);
        $arquivamento->setData(date('Y-m-d', $data));
        $arquivamento->setHistorico($motivo);
        $arquivamento->setUsuario($usuario);
        $arquivamento->setDepartamento($departamento);

        $this->arquivamentoRepository->persist($arquivamento);

        $arquivamentoProcesso->setArquivamento($arquivamento->getCodigo());
        $arquivamentoProcesso->setProcesso($codigoProcesso);

        $this->arquivamentoProcessoRepository->persist($arquivamentoProcesso);

        if (empty($processoAndamento)) {
            $transferencia     = $this->transferir(
                $processo,
                $departamento,
                $departamento,
                $usuario
            );
            $processoAndamento = $this->receber(
                $processo,
                $transferencia,
                $motivo
            );
        }

        $arquivamentoAndamento->setArquivamento($arquivamento->getCodigo());
        $arquivamentoAndamento->setAndamento($processoAndamento->getCodigo());
        $arquivamentoAndamento->setArquivado(true);

        $this->arquivamentoAndamentoRepository->persist($arquivamentoAndamento);
    }

    /**
     * @param  Processo  $processo
     * @param  Transferencia|null  $transferencia
     *
     * @return ProcessoDocumento
     * @throws \ParameterException
     */
    public function criarCapa(
        Processo $processo,
        Transferencia $transferencia = null
    ) {
        $this->iniciarLaravel();
        db_putsession('DB_itemmenu_acessado', 6865);
        db_putsession('DB_acessado', 6865);
        $nomeArquivo     = "capa_processo_{$processo->getCodigoProcesso()}.pdf";
        $capaDoDocumento = new CapaDoDocumento(
            $processo,
            $transferencia,
            $nomeArquivo
        );
        $caminho         = $capaDoDocumento->saveFile();
        $storageConfig   = StorageHelper::getStorageConfig();
        $allowed         = array();

        if (isset($storageConfig->client_id_ouvidoria)
            && ! empty($storageConfig->client_id_ouvidoria)
        ) {
            $allowed[] = $storageConfig->client_id_ouvidoria;
        }

        $metadata                     = new \stdClass();
        $metadata->tipo_documento     = "processo";
        $metadata->numero_do_processo = $processo->getNumero()."/"
                                        .$processo->getAno();
        $metadata->requerente         = $processo->getRequerente();
        $metadata->data_hora          = $processo->getData()." "
                                        .$processo->getHora();
        $rsProcessoOuvidoria          = db_query(
            "
                    SELECT
                    *
                    FROM
                          processoouvidoria
                    INNER JOIN ouvidoriaatendimento
                    ON processoouvidoria.ov09_ouvidoriaatendimento = ouvidoriaatendimento.ov01_sequencial
                    WHERE
                    ov09_protprocesso = {$processo->getCodigoProcesso()}
         "
        );

        $processoOuvidoria = pg_fetch_object($rsProcessoOuvidoria);

        if (! empty($processoOuvidoria)) {
            $numeroAtendimento            = $processoOuvidoria->ov01_numero;
            $numeroAtendimento            .= "/"
                                             .$processoOuvidoria->ov01_anousu;
            $metadata->numero_atendimento = $numeroAtendimento;
        }

        $metadata->codigo_usuario_aprovacao = $processo->getUsuario();
        $metadata->login_usuario_aprovacao  = DefaultSession::getInstance()
                                                            ->get(
                                                                DefaultSession::DB_LOGIN
                                                            );

        $processoDocumento = new ProcessoDocumento();
        $processoDocumento->setProcesso($processo->getCodigoProcesso());
        $processoDocumento->setDescricao('Capa do Processo');
        $processoDocumento->setData($processo->getData());
        $processoDocumento->setUsuario(
            DefaultSession::getInstance()->get(DefaultSession::DB_ID_USUARIO)
        );
        $processoDocumento->setStorage(true);
        $processoDocumento->setOrdem(1);
        $processoDocumento->setNomeDocumento(
            'Capa do Processo.pdf'
        );
        $processoDocumento->setDocumento(
            StorageHelper::uploadArquivo($caminho, $allowed, true, $metadata)
        );

        return $this->salvarDocumento($processoDocumento);
    }

    private function iniciarLaravel()
    {
        $app = require_once ECIDADE_PATH.'bootstrap/app.php';
        // App já iniciado
        if ($app === true) {
            return;
        }
        $kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
        $kernel->bootstrap();
    }
}

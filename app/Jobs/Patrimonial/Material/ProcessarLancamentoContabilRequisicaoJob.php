<?php

namespace App\Jobs\Patrimonial\Material;

use App\Domain\Patrimonial\Material\Services\ProcessarLancamentoQueueService;
use BusinessException;
use ECidade\Lib\Session\DatabaseSession;
use ECidade\Lib\Session\DefaultSession;
use EventoContabil;
use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use LancamentoAuxiliarMovimentacaoEstoque;
use SingletonRegraDocumentoContabil;

class ProcessarLancamentoContabilRequisicaoJob implements \Illuminate\Contracts\Queue\ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 10;
    private $requisicaoMaterial;
    private $codigoMaterial;
    private $codigoLancamentoMaterial;
    private $valorAtendido;
    private $session;

    private $queueService;

    private $queuedJob;

    public function __construct($dadosRequisicao)
    {
        $this->requisicaoMaterial = $dadosRequisicao->requisicaoMaterial;
        $this->codigoMaterial = $dadosRequisicao->codigoMaterial;
        $this->codigoLancamentoMaterial = $dadosRequisicao->codigoLancamentoMaterial;
        $this->valorAtendido = $dadosRequisicao->valorAtendido;
        $this->session = $dadosRequisicao->session;

        // cria um batch/lote de jobs, que nesse caso eh somente 1
        $this->queueService = ProcessarLancamentoQueueService::newBatch($dadosRequisicao);

        // cadastra o job na lista de controle, para poder contabilizar posteriormente
        $this->queuedJob = $this->queueService->next();
    }

    public function handle()
    {
        try {
            require_once(modification("classes/materialestoque.model.php"));
            $_SESSION = $this->session;
            DefaultSession::getInstance()->addFromRequest($this->session);
            DatabaseSession::getInstance()->addSessionToDatabase();
            $oMaterialEstoque = new \materialEstoque($this->codigoMaterial);
            if (empty($this->valorAtendido) || $this->valorAtendido == 0) {
                throw new BusinessException("Valor do lancamento não informado ou igual a 0 !");
            }

            $dtLancamento = date("Y-m-d", db_getsession("DB_datausu"));

            $oEventoContabil = new EventoContabil(400, db_getsession("DB_anousu"));
            $aLancamentos = $oEventoContabil->getEventoContabilLancamento();
            if (count($aLancamentos) == 0) {
                $sMensagem = "Não existe lançamentos para o evento 400 - {$oEventoContabil->getDescricaoDocumento()}";
                throw new BusinessException($sMensagem);
            }

            $iCodigoHistorico = $aLancamentos[0]->getHistorico();
            $oLancamentoAuxiliarEstoque = new LancamentoAuxiliarMovimentacaoEstoque();
            $oLancamentoAuxiliarEstoque->setCodigoMovimentacaoEstoque($this->codigoLancamentoMaterial);
            $oLancamentoAuxiliarEstoque->setValorTotal($this->valorAtendido);
            $sHistoricoLancamento = sprintf(
                "Lançamento contábil referente a atendimento da requisição %s, material %s.",
                $this->requisicaoMaterial,
                $oMaterialEstoque->getcodMater()
            );

            $oLancamentoAuxiliarEstoque->setObservacaoHistorico($sHistoricoLancamento);
            $oLancamentoAuxiliarEstoque->setHistorico($iCodigoHistorico);
            $oLancamentoAuxiliarEstoque->setMaterial($oMaterialEstoque);
            $oLancamentoAuxiliarEstoque->setSaida(true);
            if ($oMaterialEstoque->getGrupo() != null) {
                $oLancamentoAuxiliarEstoque->setContaPcasp($oMaterialEstoque->getGrupo()->getConta());
            }
            $this->executarLancamentosContabeis(400, $oLancamentoAuxiliarEstoque, $dtLancamento);

            //informa que o processamento foi realizado com sucesso
            $this->queueService->markAsSuccess();

            // remove o job da lista de controle de jobs
            $this->queueService->terminate($this->queuedJob);
        } catch (\Exception $e) {
            // pode utilizar de outra forma, simplesmente sem fazer try catch
            // assim ele tenta toda hora, até atingir o limite definido em $tries
            if ($this->attempts() >= $this->tries) {
                throw $e;
            }
            
            // tenta denovo depois de um tempo
            // numero da tentativa * 10 minutos
            $this->release($this->attempts() * 600);
        }
    }

    protected function executarLancamentosContabeis(
        $iCodigoDocumento,
        LancamentoAuxiliarMovimentacaoEstoque $oLancamentoAuxiliar,
        $dtLancamento
    ) {

        $oDocumentoContabil = SingletonRegraDocumentoContabil::getDocumento($iCodigoDocumento);
        $iCodigoDocumentoExecutar = $oDocumentoContabil->getCodigoDocumento();
        $oEventoContabil = new EventoContabil($iCodigoDocumentoExecutar, db_getsession("DB_anousu"));
        if ($iCodigoDocumentoExecutar == 401) {
            $oLancamentoAuxiliar->setSaida(false);
        } else {
            $oLancamentoAuxiliar->setSaida(true);
        }

        $oEventoContabil->executaLancamento($oLancamentoAuxiliar, $dtLancamento);
    }

    public function failed(\Exception $e)
    {
        // remove o job da lista de controle de jobs
        $this->queueService->terminate($this->queuedJob);

        // pode notificar o usuário ou qualquer outro tipo de notificação
        $this->queueService->markAsFailed($e);
    }
}

<?php

namespace App\Jobs\RecursosHumanos\RH;

use App\Domain\Core\Base\Http\Response\DBJsonResponse;
use App\Domain\RecursosHumanos\RH\ConcessaoDireitos\Controllers\ProcessamentoConcessaoController;
use App\Domain\RecursosHumanos\RH\ConcessaoDireitos\Models\AssentConfig;
use App\Domain\RecursosHumanos\RH\ConcessaoDireitos\Models\ProcessConcessaoContagem;
use App\Domain\RecursosHumanos\RH\ConcessaoDireitos\Providers\Concessao;
use App\Domain\RecursosHumanos\RH\ConcessaoDireitos\Providers\ProcessConcessaoCountProviders;
use App\Domain\RecursosHumanos\RH\ConcessaoDireitos\Providers\Rhpessoal as ProvidersRhpessoal;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class ProcessamentoConcessao implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    private $processamentoConcessao;

    /**
     * @var int
     */
    private $inst;

    /**
     * @var int
     */
    private $rh500_sequencial;

    /**
     * @var int
     */
    private $assentconfig;

    /**
     * @var String
     */
    private $data_processamento;

    /**
     * @var Date
     */
    private $data;


    public function __construct($inst, $rh500_sequencial, $data_processamento, $data, $assentconfig)
    {
        $this->data = $data;
        $this->assentconfig = $assentconfig;
        $this->inst = $inst;
        $this->rh500_sequencial = $rh500_sequencial;
        $this->data_processamento = $data_processamento;
        $this->processamentoConcessao = new ProcessamentoConcessaoController();
    }

    /**
     * Execute the job. ProvidersRhpessoal
     *
     * @return void
     */
    public function handle()
    {
        if (ProcessConcessaoCountProviders::verificarFila()) {
            return;
        }

        try {
            $matriculas = ProvidersRhpessoal::matriculas(
                $this->inst,
                $this->data,
                $this->assentconfig,
                null
            );
            //define data limite
            $assentconfig = AssentConfig::where('rh500_sequencial', $this->rh500_sequencial)->first();
            $data_processamento = $this->data_processamento;
            if (strtotime($data_processamento) > strtotime($assentconfig->rh500_datalimite)) {
                $data_processamento = $assentconfig->rh500_datalimite;
            }
            // busca assentamento de inicio
            //$assentamentoInicio = Concessao::assentamentoInicio($this->rh500_sequencial);
            $assents_envolvidos = Concessao::todosAssentamntosEnvolvidos($this->rh500_sequencial);
            $periodos = Concessao::periodos($this->rh500_sequencial);

            ProcessConcessaoCountProviders::create(count($matriculas));
        } catch (Exception $th) {
            ProcessConcessaoCountProviders::salvarLogErro(
                null,
                'Matricula - ERRO ao iniciar Job - ' . $th->getMessage()
            );
            return;
        }

        foreach ($matriculas as $key => $value) {
            try {
                $this->processamentoConcessao->processamento(
                    $value->rh01_regist,
                    $this->inst,
                    $this->rh500_sequencial,
                    $this->data_processamento,
                    $assents_envolvidos,
                    $periodos
                );
                ProcessConcessaoCountProviders::setFila();
            } catch (Exception $th) {
                ProcessConcessaoCountProviders::salvarLogErro(
                    $value->rh01_regist,
                    'Matricula - ERRO ao Calcular - ' . $th->getMessage()
                );
                ProcessConcessaoCountProviders::setFila();
                continue;
            }
        }
        ProcessConcessaoCountProviders::finalizar();
    }
}

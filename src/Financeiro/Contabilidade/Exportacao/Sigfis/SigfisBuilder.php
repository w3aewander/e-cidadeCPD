<?php

namespace ECidade\Financeiro\Contabilidade\Exportacao\Sigfis;

use App\Domain\Financeiro\Contabilidade\Models\SigfisUnidadeGestoraModel;
use App\Domain\Financeiro\Contabilidade\Services\SigfisUnidadeGestoraService;
use DateTime;
use Exception;

class SigfisBuilder
{
    /**
     * Lista dos nomes dos arquivos a serem processados
     *
     * @var string[]
     */
    public $nomeArquivos;

    /**
     * Periodo a ser filtrados na geracao dos dados dos arquivos
     *
     * @var string[]
     * @example : ['2023-01-01', '2023-01-31']
     */
    public $periodo;

    /**
     * Ano da sessao do usuario
     *
     * @var string
     */
    public $anousu;

    /**
     * Instituicao da sessao do usuario
     *
     * @var int
     */
    public $instit;

    /**
     * Unidade gestora da instituicao a ser passado para os arquivos
     *
     * @var SigfisUnidadeGestoraModel
     */
    public $unidadeGestora;

    /**
     * Lista dos paths gerados pelos os arquivos
     *
     * @var object[]
     */
    public $listaArquivos = [];

    /**
     * Lista dos nomes dos arquivos nao processados por erro
     *
     * @var string[]
     */
    public $builderErrors = [];

    /**
     * Versao dos arquivos a serem gerados
     *
     * @var string
     */
    public $version;

    /**
     * Construct
     *
     * @param string[] $arquivos Array com os nome dos arquivos a serem processados.
     * @param int $mes Mes para montar o intervalo de data
     */
    public function __construct($nomeArquivos, $mes, $anoexe = null)
    {   
        $this->anousu = db_getsession('DB_anousu');
        $this->instit = db_getsession('DB_instit');
        $this->unidadeGestora = SigfisUnidadeGestoraService::getUnidadeGestora($this->instit);
        $this->nomeArquivos = $nomeArquivos;
        //$this->version = '2024'; // $this->version = $this->anousu;
        //$this->version = in_array($this->anousu, ['2024', '2026']) ? $this->anousu : '2024';
        $anoLayout = !empty($anoexe) ? $anoexe : $this->anousu;
        
        $this->version = in_array($anoLayout, ['2024', '2026']) ? $anoLayout : '2024';

        $this->buildPeriodo($mes);
    }

    /**
     * Monta intervalo de data do mes do parametro
     *
     * @param int $mes
     * @return string[]
     */
    private function buildPeriodo($mes)
    {
        $mes = ($mes >= 1 && $mes <= 12) ? $mes : 1;
        $date  = new DateTime("{$this->anousu}-{$mes}-01");

        $dateInicial = $date->format('Y-m-d');
        $dateFinal   = $date->format('Y-m-t');

        $this->periodo = [$dateInicial, $dateFinal];
    }

    /**
     * intera sobre os objetos de Arquivos salvando a lista gerada em
     * listaArquivos
     *
     * @return object[]
     */
    public function buildListaArquivos()
    {
        foreach ($this->nomeArquivos as $nomeArquivo) {
            $arquivo = SigfisFactory::create($this->version, $nomeArquivo);
            $arquivo->setDataInicial($this->periodo[0]);
            $arquivo->setDataFinal($this->periodo[1]);
            $arquivo->setUnidadeGestora($this->unidadeGestora);
            $arquivo->setAnousu($this->anousu);

            try {
                foreach ($arquivo->gerarLista() as $item) {
                    $this->listaArquivos[] = $item;
                }
            } catch (Exception $e) {
                $arquivo->addLog($e->getMessage());
                $this->builderErrors[] = $arquivo->getNomeArquivo();
            } finally {
                $this->listaArquivos[] = $arquivo->gerarListaLogs();
            }
        }

        return $this->listaArquivos;
    }
}

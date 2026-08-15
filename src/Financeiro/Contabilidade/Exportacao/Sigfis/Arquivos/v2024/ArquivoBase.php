<?php

namespace ECidade\Financeiro\Contabilidade\Exportacao\Sigfis\Arquivos\v2024;

use App\Domain\Financeiro\Contabilidade\Models\SigfisUnidadeGestoraModel;
use ECidade\Financeiro\Contabilidade\Exportacao\Sigfis\Common\Exporters\CsvExport;
use ECidade\Financeiro\Contabilidade\Exportacao\Sigfis\Common\Exporters\XMLExport;
use ECidade\Financeiro\Contabilidade\Exportacao\Sigfis\Common\Logger;
use ECidade\Financeiro\Contabilidade\Exportacao\Sigfis\Interfaces\ArquivoInterface;
use ECidade\Financeiro\Contabilidade\Exportacao\Sigfis\Xsd\XSDFactory;

abstract class ArquivoBase implements ArquivoInterface
{
    protected $iAnoUsu;
    protected $dtDataInicial;
    protected $dtDataFinal;
    protected $sNomeArquivo;
    protected $iCodigoLayout;
    protected $sCodigoTribunal;
    protected $aDados = [];
    protected $oLogger;
    protected $oDocumento;
    protected $sArquivo;
    protected $sXsd;
    protected $aListaArquivos = [];
    protected $csv;

    /**
     * Versao do arquivo
     *
     * @var string
     */
    protected $version;

    /**
     * @var string
     */
    protected $competencia;

    /**
     * @var integer
     */
    protected $instit;

    /**
     * SigfisUnidadeGestoraModel
     *
     * @var SigfisUnidadeGestoraModel
     */
    protected $unidadeGestora;

    /**
     * Construtor
     *
     * @param string $version Versao do arquivo
     */
    public function __construct($version)
    {
        $this->iAnoUsu = db_getsession('DB_anousu');
        $this->instit = db_getsession('DB_instit');

        $this->setVersion($version);
        $this->setLogger("tmp/Remessa{$this->getNomeArquivo()}.log");
        $this->setXsd(XSDFactory::get(
            $this->getVersion(),
            $this->getNomeArquivo()
        ));
    }

    /**
     *
     * Retorna um array de com os dados do Arquivo
     * @return array
     */
    public function getDados()
    {
        return $this->aDados;
    }

    /**
     *
     * Seta a data Inicial
     * @param String $sDataInicial
     */
    public function setDataInicial($sDataInicial)
    {
        $this->dtDataInicial = $sDataInicial;
    }

    /**
     * Seta anousu
     *
     * @param int $iAnoUso
     * @return void
     */
    public function setAnousu($iAnoUso)
    {
        $this->iAnoUsu = $iAnoUso;
    }

    /**
     * Seta versao do arquivo
     *
     * @param string $version
     * @return void
     */
    public function setVersion($version)
    {
        $this->version = $version;
    }

    /**
     * Retorna a versao do arquivo
     *
     * @return string
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     *
     * Seta a data Final
     * @param String $sDataFinal
     */
    public function setDataFinal($sDataFinal)
    {
        $this->dtDataFinal = $sDataFinal;
        $data = explode('-', $this->dtDataFinal);
        $this->competencia = "{$data[0]}$data[1]";
    }

    /**
     * Retorna o Código do Nome do Arquivo
     */
    public function getNomeArquivo()
    {

        return $this->sNomeArquivo;
    }

    /**
     * Retorna o Código do Layout
     */
    public function setCodigoLayout($iCodigoLayout)
    {

        $this->iCodigoLayout = $iCodigoLayout;
    }

    /**
     * Retorna o Código do Layout
     */
    public function getCodigoLayout()
    {

        return $this->iCodigoLayout;
    }

    /**
     * Seta arquivo log
     *
     * @param string $path
     * @return void
     */
    public function setLogger($path = 'tmp/SIGFIS.log')
    {
        $this->oLogger = new Logger($path);
    }

    /**
     * Seta a Unidade Gestora
     *
     * @param SigfisUnidadeGestoraModel $ug
     * @return void
     */
    public function setUnidadeGestora(SigfisUnidadeGestoraModel $ug)
    {
        $this->unidadeGestora = $ug;
        $this->sCodigoTribunal = $this->unidadeGestora->c179_codigo;
    }

    /**
     * Retorna o Código do tribunal
     * @return string
     */
    public function getCodigoTribunal()
    {
        return $this->unidadeGestora->c179_codigo;
    }

    public function isUgResponsavelFolha()
    {
        return $this->unidadeGestora->c179_responsavelfolha;
    }

    /**
     * Retorna o código da unidade gestora da folha
     * @return string
     */
    public function getUgResponsavelFolha()
    {
        if ($this->unidadeGestora->c179_responsavelfolha) {
            return $this->getCodigoTribunal();
        }
        return $this->unidadeGestora->c179_codigofolha;
    }

    public function pegaCgmOrdenador(){
        return $this->unidadeGestora->c179_cgmordenadordespesa;
    }

    /**
     * Adiciona um registro de log no arquivo
     * @param string $sLog
     */
    public function addLog($sLog)
    {
        $this->oLogger->log($sLog);
    }

    /**
     *
     * Formata valor para o formato esperado no sigfis
     * @param numeric $nValor
     * @return string
     */
    protected function formataValor($nValor)
    {
        $nValor = number_format($nValor, 2, "", "");
        return $nValor;
    }

    /**
     * Seta o Nome do Arquivo
     */
    public function setArquivo($sArquivo)
    {
        $this->sArquivo = $sArquivo;
    }

    /**
     * Retorna nome do arquivo
     *
     * @return string
     */
    public function getArquivo()
    {
        return $this->sArquivo;
    }

    /**
     *
     * Formata uma data para o padrao do sigfis
     * @param String $dtData
     * @param String $sParam1
     * @param String $sParam2
     */
    protected function formataData($dtData, $sParam1 = '', $sParam2 = '-')
    {
        $sDataFormatada = implode($sParam1, array_reverse(explode($sParam2, $dtData)));
        return $sDataFormatada;
    }

    /**
     * Exporta dados xml e adiciona na
     * lista de aqruivos o arquivo gerado
     *
     * @return void
     */
    public function gerarXML()
    {
        if (empty($this->aDados)) {
            return false;
        }

        $name = "Remessa{$this->getNomeArquivo()}";
        $prefix = '.xml';
        $path = 'tmp/' . $name . $prefix;

        $xmlExport = new XMLExport($this->aDados, $name, $this->sXsd, $path);
        $xmlExport->buildXML();

        if ($xmlExport->errors) {
            $this->addLog("XSD Reports:");
            foreach ($xmlExport->errors as $iIndice => $oErro) {
                $aMensagem[] = $iIndice + 1 . ' - ' . $oErro->message;
            }

            $this->addLog(implode("", $aMensagem));
        }

        $this->addListaArquivos($name . $prefix, $path);
    }

    /**
     * Exporta dados xml e adiciona na
     * lista de aqruivos o arquivo gerado
     *
     * @return void
     */
    public function gerarCSV()
    {
        if (empty($this->aDados)) {
            return false;
        }

        $name = "Remessa{$this->getNomeArquivo()}";
        $prefix = '.csv';
        $path = 'tmp/' . $name . $prefix;

        $csvExport = new CsvExport($this->aDados, $path);
        $csvExport->buildCSV();

        $this->addListaArquivos($name . $prefix, $path);
    }

    /**
     * Seta caminho do XSD do arquivo
     *
     * @param string $sXsd
     * @return void
     */
    public function setXsd($sXsd)
    {
        $this->sXsd = $sXsd;
    }

    /**
     * Retorna lista dos path dos arquivos gerados
     *
     * @return array
     */
    public function getListaArquivos()
    {
        return $this->aListaArquivos;
    }

    /**
     * Adiciona lista de arquivo
     *
     * @param string $name
     * @param string $path
     * @return void
     */
    public function addListaArquivos($name, $path)
    {
        $this->aListaArquivos[] = (object)['name' => $name, 'path' => $path];
    }

    /**
     * Retorna instancia do gerenciador de logs
     *
     * @return Logger
     */
    public function getLogger()
    {
        return $this->oLogger;
    }

    /**
     * Adiciona na lista function
     *
     * @return array
     */
    public function gerarListaLogs()
    {
        return [
            'name' => 'Remessa' . $this->getNomeArquivo() . '.log',
            'path' => $this->getLogger()->getPath()
        ];
    }

    /**
     * Bunddle para gerar lista de arquivos processados
     *
     * @return array
     */
    public function gerarLista()
    {
        $this->gerarDados();

        if (!empty($this->aDados)) {
            $this->gerarXML();
            $this->gerarCSV();
        }

        return $this->aListaArquivos;
    }

    public function deparaUnidadeGestora($instituicao){
        
        if($instituicao == 1){
            $xcgm = 203557;
            $dadosug["ug"] = 700;
        }elseif($instituicao == 20){
            $xcgm = 160105;
            $dadosug["ug"] = 707;
        }elseif($instituicao == 25){
            $xcgm = 189981;
            $dadosug["ug"] = 706;
        }elseif($instituicao == 30){
            $xcgm = 134639;
            $dadosug["ug"] = 704;
        }elseif($instituicao == 35){
            $xcgm = 200001;
            $dadosug["ug"] = 702;
        }elseif($instituicao == 45){
            $xcgm = 112906;
            $dadosug["ug"] = 703;
        }elseif($instituicao == 50){
            $xcgm = 208711;
            $dadosug["ug"] = 723;
        }elseif($instituicao == 55){
            $xcgm = 137574;
            $dadosug["ug"] = 725;
        }elseif($instituicao == 60){
            $xcgm = 117883;
            $dadosug["ug"] = 724;
        }elseif($instituicao == 65){
            $xcgm = 144830;
            $dadosug["ug"] = 1299;
        }elseif($instituicao == 70){
            $xcgm = 189573;
            $dadosug["ug"] = 1300;
        }elseif($instituicao == 75){
            $xcgm = 205519;
            $dadosug["ug"] = 709;
        }elseif($instituicao == 80){
            $xcgm = 193481;
            $dadosug["ug"] = 1301;
        }elseif($instituicao == 85){
            $xcgm = 205320;
            $dadosug["ug"] = 727;
        }elseif($instituicao == 90){
            $xcgm = 144830;
            $dadosug["ug"] = 8312;
        }elseif($instituicao == 96){
            $xcgm = 161054;
            $dadosug["ug"] = 9603;
        }



        $sql = pg_query("SELECT z01_cgccpf FROM cgm WHERE z01_numcgm = {$xcgm}");
        $resultado = pg_fetch_all($sql);
        $resultado = $resultado[0]["z01_cgccpf"];

        
        $dadosug["cpf"] = $resultado;
        return $dadosug;


    }
}

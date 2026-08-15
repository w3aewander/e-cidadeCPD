<?php

namespace ECidade\Financeiro\Contabilidade\Exportacao\Sigfis\Arquivos\v2024;

use ECidade\Financeiro\Contabilidade\Calculo\Despesa;
use ECidade\Financeiro\Contabilidade\Exportacao\Sigfis\Xsd\XSDFactory;
use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Database\Query\JoinClause;
use stdClass;

class ArquivoEmpenhoInstrumentoPrevio extends ArquivoBase
{
    protected $sNomeArquivo = 'EmpenhoInstrumentoPrevio';
    const LICITACAO = '1';
    const DISPENSA = '2';
    const INEXIGIBILIDADE = '3';
    const ATO_ADESAO_REGISTRO_PRECO = '4';
    const INEXISTENTE_JUSTIFICATIVA = '99';

    public function buscaCamposAuxiliares($empenho){
        $sql2 = pg_query("SELECT * FROM empenhoauxsigfis WHERE seqempenho = {$empenho}");
        $resultado = pg_fetch_all($sql2);
        $aux = $resultado[0];

        if(!$aux){
            $sql1 = pg_query("SELECT e61_autori FROM empempaut WHERE e61_numemp = {$empenho}");
            $autorizacao = pg_fetch_all($sql1);
            $autorizacao = $autorizacao[0]["e61_autori"];

            $sql = pg_query("SELECT * FROM empenhoauxsigfis WHERE noautorizacaoempenho = {$autorizacao}");
            $resultado = pg_fetch_all($sql);
            return $resultado[0];    
        }else{
            return $aux;
        }        
    }

    public function testa($var){
        echo "<pre>";
        print_r($var);
        echo "</pre>";
    }

    public function gerarDados()
    {
        $campos = [
            'e60_numemp', 'e60_codemp', 'e60_anousu','e60_codcom',
            'o58_orgao', 'o58_unidade'
        ];

        $empenhos = DB::table('empempenho')
            ->select($campos)
            ->join('orcdotacao', function (JoinClause $join) {
                $join->on('e60_anousu', 'o58_anousu')
                    ->on('e60_coddot', 'o58_coddot');
            })
            ->where('e60_instit', $this->instit)
            ->whereBetween('e60_emiss', [$this->dtDataInicial, $this->dtDataFinal])
            ->get();

        if ($empenhos->isEmpty()) {
            throw new \Exception('Não foi encontrardo nenhum empenho para o arquivo de Remessa ');
        }

        $obj = new stdClass();
        $obj->EmpenhosInstrumentosPrevios = [];

        foreach ($empenhos as $empenho) {
            //if($empenho->e60_codemp != 1199){continue;}
            $dadosextras = $this->buscaCamposAuxiliares($empenho->e60_numemp);
            /*if($empenho->e60_codemp == "601"){
                $this->testa($dadosextras); die("Confere");
            }*/
            
            
            
            $numeroatojuridico = $dadosextras["noatoju"];
            $codunidadeatoju = $dadosextras["ugaj"];
            $codinstprev = $dadosextras["tipro"];
            $codunidadeinstprev = $dadosextras["ugip"];
            //$codunidadeinstprev = $dadosextras["ugaj"];
            if(($dadosextras["tipro"] == 99 || $dadosextras["tajuo"] == 99 || $dadosextras["tipro"] == 0 || $dadosextras["tajuo"] == 0) && !$dadosextras["noinpr"]){continue;}
            $data = (object)[
                'Identificador' => $empenho->e60_numemp,
                'CodigoUnidadeGestora' => $this->getUgResponsavelFolha(),//$this->sCodigoTribunal,
                'Competencia' => $this->competencia,
                'NumeroAtoJuridicoTCE' => $dadosextras["noinpr"], //$numeroatojuridico,  //null,
                //'NumeroAtoJuridicoTCE' => $numeroatojuridico,  //null,
                'CodigoUnidadeGestoraAtoJuridico' => $codunidadeinstprev,//null,
                'NumeroEmpenho' => $empenho->e60_codemp,
                'AnoEmpenho' => $empenho->e60_anousu,
                'CodigoUnidadeOrcamentaria' => $empenho->o58_unidade,
                'CodigoOrgao' => $empenho->o58_orgao,
                'CodigoTipoInstrumentoPrevio' => $codinstprev//$this->deParaCodigoInstrumentoPrevio($empenho->e60_codcom)
            ];

            $obj->EmpenhosInstrumentosPrevios[] = (object)['EmpenhoInstrumentoPrevio' => $data];
        }

        $this->aDados = $obj;
    }

    public function deParaCodigoInstrumentoPrevio($e60_codcom)
    {
        
        /**
         * Valores de compra da pctipocompra (pc50_codcom)
         */
        $licitacoes = [
            '1','2','3','4','10','12','14','16','17','19','20','21',
            '22','23','24','25','26'
        ];
        $dispensas = ['5','11','18','27'];
        $inexigibilidades = ['13'];
        $atosAdesao = [''];
        $inexistentes = ['7','8','9','15'];
        if (in_array($e60_codcom, $licitacoes)) {
            return static::LICITACAO;
        } elseif (in_array($e60_codcom, $dispensas)) {
            return static::DISPENSA;
        } elseif (in_array($e60_codcom, $inexigibilidades)) {
            return static::INEXIGIBILIDADE;
        } elseif (in_array($e60_codcom, $atosAdesao)) {
            return static::ATO_ADESAO_REGISTRO_PRECO;
        } elseif (in_array($e60_codcom, $inexistentes)) {
            return static::INEXISTENTE_JUSTIFICATIVA;
        }
    }
}

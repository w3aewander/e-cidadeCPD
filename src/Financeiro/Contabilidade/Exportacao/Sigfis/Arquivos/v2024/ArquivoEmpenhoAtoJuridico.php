<?php

namespace ECidade\Financeiro\Contabilidade\Exportacao\Sigfis\Arquivos\v2024;

use ECidade\Financeiro\Contabilidade\Exportacao\Sigfis\Xsd\XSDFactory;
use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Database\Query\JoinClause;

class ArquivoEmpenhoAtoJuridico extends ArquivoBase
{
    protected $sNomeArquivo  = 'EmpenhoAtoJuridico';
    protected $iAnoUsu;
    protected $instit;

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

    /**
    * Busca os dados para gerar o Arquivo de Unidade Orçamentária
    */
    public function gerarDados()
    {
        /*$campos = [
            'e60_numemp', 'e60_codemp', 'e60_anousu', 'o58_orgao', 'o58_unidade', 'e166_tipoatojuridico'
        ];*/
        $campos = [
            'e60_numemp', 'e60_codemp', 'e60_anousu', 'o58_orgao', 'o58_unidade'
        ];

        $empenhos = DB::table('empempenho')
            ->select($campos)
            //->join('emptipoatojuridico', 'e166_empempenho', '=', 'e60_numemp')
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

        $obj = new \stdClass();
        $obj->EmpenhosAtosJuridicos = [];

        //$dadosug = $this->deparaUnidadeGestora($this->instit);


        //Pelo sequencial do empenho e60_numemp, buscar o tipo do ato jurídico na tabela nova

        foreach ($empenhos as $empenho) {
            $dadosextras = $this->buscaCamposAuxiliares($empenho->e60_numemp);

            /*if($empenho->e60_numemp == 954704){
                echo "<pre>";
                print_r($dadosextras);
                echo "<pre>";
                die("Boi");
            }*/
            
            $numeroatojuridico = $dadosextras["noatoju"];
            $codunidadeatoju = $dadosextras["ugaj"];
            $codtipoatoju = $dadosextras["tajuo"];

            //if($dadosextras["tipro"] == 99 || $dadosextras["tajuo"] == 99 || $dadosextras["tipro"] == 0 || $dadosextras["tajuo"] == 0){continue;}
            if(($dadosextras["tipro"] == 99 || $dadosextras["tajuo"] == 99 || $dadosextras["tipro"] == 0 || $dadosextras["tajuo"] == 0) && !$dadosextras["noatoju"]){continue;}
            
            $dadosEmpenhoAtoJuridico = (object)[
                'Identificador' => $empenho->e60_numemp,
                'CodigoUnidadeGestora' => $this->sCodigoTribunal,
                'Competencia' => $this->competencia,
                'CodigoOrgao' => $empenho->o58_orgao,
                'CodigoUnidadeOrcamentaria' => $empenho->o58_unidade,
                'NumeroAtoJuridicoTCE' => $numeroatojuridico,//1,
                'CodigoUnidadeGestoraAtoJuridico' => $codunidadeatoju,//$this->sCodigoTribunal,
                'NumeroEmpenho' => $empenho->e60_codemp,
                'AnoEmpenho' => $empenho->e60_anousu,
                'CodigoTipoAtoJuridico' => $codtipoatoju//$empenho->e166_tipoatojuridico
            ];

            $obj->EmpenhosAtosJuridicos[] = (object)['EmpenhoAtoJuridico' => $dadosEmpenhoAtoJuridico];
        }

        $this->aDados =  $obj;
    }
}


/*
jaip - Justificativa da Ausência de Instrumento Prévio
jaaj - Justificativa da Ausência de Ato Jurídico

tipro - Tipo de Instrumento Prévio   
noinpr - Nº Instrumento Prévio

tajuo - Tipo do Ato Jurídico
noatoju - Nº do Ato Jurídico

*/
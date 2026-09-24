<?php

namespace ECidade\Financeiro\Contabilidade\Exportacao\Sigfis\Arquivos\v2024;


use ECidade\Financeiro\Contabilidade\Exportacao\Sigfis\Common\Helper;
use db_utils;

class ArquivoAlteracaoOrcamentariaReceita extends ArquivoBase
{

    protected $sNomeArquivo  = 'AlteracaoOrcamentariaReceita';
    /**
    * Busca os dados para gerar o Arquivo de Unidade Orçamentária
    */

    public function testa($var){
        echo "<pre>";
        print_r($var);
        echo "</pre>";
    }

    public function checavalor($codproj){
        $sql = pg_query("SELECT distinct o47_codsup, o39_codproj, o39_numero as decreto, o58_orgao, o47_anousu, case when o47_valor > 0 then o47_valor end as suplementado, case when o47_valor < 0 then o47_valor *-1 end as reduzido, o46_data, o49_data from orcsuplem inner join orcsuplemtipo on o48_tiposup = orcsuplem.o46_tiposup inner join orcsuplemval on o47_codsup=o46_codsup left outer join orcsuplemlan on o49_codsup = o47_codsup inner join orcprojeto on o39_codproj = orcsuplem.o46_codlei left join orcprojetoorcprojetolei on o39_codproj = o139_orcprojeto inner join orclei on o45_codlei = orcprojeto.o39_codlei left join orcsuplemretif on o48_retificado = orcprojeto.o39_codproj inner join orcdotacao on o58_coddot =orcsuplemval.o47_coddot and o58_anousu=orcsuplemval.o47_anousu inner join orcelemento on o58_codele = o56_codele and o56_anousu = o58_anousu inner join orctiporec on o58_codigo = o15_codigo inner join complementofonterecurso on o15_complemento = o200_sequencial where ( o39_usalimite is true or o139_orcprojeto is not null) and o48_retificado is null AND o39_codproj = {$codproj} AND o49_data BETWEEN '{$this->dtDataInicial}' AND '{$this->dtDataFinal}' ORDER BY o39_codproj");
        $resultado = pg_fetch_all($sql);
        $vt = 0;
        foreach ($resultado as $r) {
            $vt += $r["suplementado"];
        }
        return $vt;

    }

    public function buscaSuplementacoes(){
        $sql = pg_query("SELECT distinct o47_codsup, o49_data, o45_numlei as lei, o45_descr, o39_codproj, o39_numero as decreto, o39_descr, o46_tiposup, o48_descr, o47_coddot, o58_orgao, o47_anousu, case when o47_valor > 0 then o47_valor end as suplementado, case when o47_valor < 0 then o47_valor *-1 end as reduzido, o58_codigo, o39_usalimite, o139_orcprojeto, o56_elemento from orcsuplem inner join orcsuplemtipo on o48_tiposup = orcsuplem.o46_tiposup inner join orcsuplemval on o47_codsup=o46_codsup left outer join orcsuplemlan on o49_codsup = o47_codsup inner join orcprojeto on o39_codproj = orcsuplem.o46_codlei left join orcprojetoorcprojetolei on o39_codproj = o139_orcprojeto inner join orclei on o45_codlei = orcprojeto.o39_codlei left join orcsuplemretif on o48_retificado = orcprojeto.o39_codproj inner join orcdotacao on o58_coddot =orcsuplemval.o47_coddot and o58_anousu=orcsuplemval.o47_anousu and o58_instit in ({$this->instit}) inner join orcelemento on o58_codele = o56_codele and o56_anousu = o58_anousu inner join orctiporec on o58_codigo = o15_codigo inner join complementofonterecurso on o15_complemento = o200_sequencial where ( o39_usalimite is true or o139_orcprojeto is not null) and o48_retificado is null and o46_tiposup in (1004) and 1=1 and orcsuplemlan.o49_data >= '{$this->dtDataInicial}' and orcsuplemlan.o49_data <= '{$this->dtDataFinal}' and o49_codsup is not null");
        $resultado = pg_fetch_all($sql);
        return $resultado;
    }

    function guardaAdicionais(){
        $sql = pg_query("SELECT o57_fonte, substr(o57_fonte,1,1)::int4 as classe, substr(o57_fonte,2,1)::int4 as grupo, substr(o57_fonte,3,1)::int4 as subgrupo, substr(o57_fonte,4,1)::int4 as elemento, substr(o57_fonte,5,1)::int4 as subelemento, substr(o57_fonte,6,2)::int4 as item, substr(o57_fonte,8,1)::int4 as subitem, substr(o57_fonte,9,1)::int4 as subitem1, substr(o57_fonte,10,2)::int4 as desdobramento1, substr(o57_fonte,12,2)::int4 as desdobramento2, substr(o57_fonte,14,2)::int4 as desdobramento3, o70_codrec, o70_concarpeculiar, o70_codigo, gestao, cast(coalesce(nullif(substr(fc_receitasaldo, 3,12),''),'0') as float8) as saldo_inicial, cast(coalesce(nullif(substr(fc_receitasaldo,16,12),''),'0') as float8) as saldo_prevadic_acum, cast(coalesce(nullif(substr(fc_receitasaldo,29,12),''),'0') as float8) as saldo_inicial_prevadic, cast(coalesce(nullif(substr(fc_receitasaldo,42,12),''),'0') as float8) as saldo_anterior, cast(coalesce(nullif(substr(fc_receitasaldo,55,12),''),'0') as float8) as saldo_arrecadado, cast(coalesce(nullif(substr(fc_receitasaldo,68,12),''),'0') as float8) as saldo_a_arrecadar, cast(coalesce(nullif(substr(fc_receitasaldo,81,12),''),'0') as float8) as saldo_arrecadado_acumulado, cast(coalesce(nullif(substr(fc_receitasaldo,94,12),''),'0') as float8) as saldo_prev_anterior from(select o70_anousu, o70_codrec, o70_codfon, o70_codigo, o70_valor, o70_reclan, o70_instit, o70_concarpeculiar, o57_codfon, o57_anousu, o57_fonte, o57_descr, o57_finali, fc_receitasaldo(2024,o70_codrec,3,'{$this->dtDataInicial}','{$this->dtDataFinal}'), gestao from orcreceita d inner join orcfontes e on d.o70_codfon = e.o57_codfon and e.o57_anousu = d.o70_anousu inner join orctiporec on d.o70_codigo = orctiporec.o15_codigo inner join fonterecurso on orctiporec.o15_codigo = fonterecurso.orctiporec_id and exercicio = d.o70_anousu where o70_anousu = 2024 and o70_instit in ({$this->instit}) order by o57_fonte ) as x WHERE CAST(COALESCE(NULLIF(substr(fc_receitasaldo, 81, 12), ''), '0') AS float8) <> '0' OR CAST(COALESCE(NULLIF(substr(fc_receitasaldo, 15, 12), ''), '0') AS float8) <> '0' OR CAST(COALESCE(nullif(substr(fc_receitasaldo, 68, 12), ''), '0') AS float8) <> '0' OR CAST(COALESCE(nullif(substr(fc_receitasaldo,55,12),''),'0') AS float8) <> '0'");
        $resultado = pg_fetch_all($sql);
        return $resultado;
    }

    function buscaMais($codproj){
        $sql = pg_query("SELECT o39_codproj, o46_codsup, o46_tiposup, o48_descr, o57_descr, o57_fonte, o85_codsup, o85_codrec, o85_anousu, o85_valor from orcprojeto inner join orcsuplem on o46_codlei = orcprojeto.o39_codproj inner join orcsuplemrec on o85_codsup = orcsuplem.o46_codsup inner join orcreceita on o70_codrec = orcsuplemrec.o85_codrec and o70_anousu = orcsuplemrec.o85_anousu inner join orcfontes on o57_codfon = orcreceita.o70_codfon and o57_anousu = orcsuplemrec.o85_anousu inner join orcsuplemtipo on o48_tiposup = orcsuplem.o46_tiposup and orcsuplemtipo.o48_arrecadmaior > 0 where o39_codproj={$codproj}");
        $resultado = pg_fetch_all($sql);
        return $resultado[0];
    }



    public function gerarDados(){   
        $fontes50 = array(
            200 => 1500,
            6000 => 1500,
            6001 => 1600,
            6002 => 1600,
            6003 => 1600,
            6004 => 1600,
            6005 => 1600,
            6012 => 1601,
            6021 => 1602,
            6031 => 1603,
            6032 => 1659,
            6041 => 1600,
            6051 => 1659,
            6211 => 1621,
            6212 => 1621,
            6213 => 1621,
            6214 => 1621,
            6215 => 1621,
            6216 => 1621,
            6217 => 1621,
            6218 => 1621,
            6219 => 1621,
            6311 => 1631,
            6312 => 1631,
            6351 => 1635,
            6591 => 1659,
            6592 => 1659,
            6593 => 1659,
            6594 => 1659,
            6595 => 1659,
            6596 => 1659,
            6597 => 1659
        );

        $fontes0 = array(
            5 => 1569,
            28 => 1550,
            200 => 1500,
            201 => 1501, 
            202 => 1501, 
            203 => 1501, 
            204 => 1501, 
            205 => 1501, 
            206 => 1501, 
            207 => 1501, 
            208 => 1501, 
            209 => 1501, 
            210 => 1501, 
            212 => 1501, 
            225 => 1501, 
            219 => 1704, 
            8 => 1704, 
            219 => 1705, 
            8 => 1705, 
            211 => 1700, 
            211 => 1701, 
            211 => 1702, 
            211 => 1703, 
            6 => 1750, 
            173 => 1751, 
            24 => 1708, 
            92 => 1700, 
            220 => 1749, 
            160 => 1749, 
            98 => 1701, 
            21 => 1701, 
            97 => 1755, 
            97 => 1756, 
            215 => 1801, 
            216 => 1800, 
            80 => 1501, 
            50 => 1799, 
            213 => 1754, 
            224 => 1899, 
            214 => 1802, 
            178 => 1752, 
            117 => 1701,
            23 => 1540,
            11 => 1551,
            45 => 1553,
            34 => 1569,
            164 => 1660,
            148 => 1700,
            22 => 1700,
            105 => 1700,
            18 => 1700,
            44 => 1700,
            14 => 1700,
            124 => 1700,
            158 => 1700,
            151 => 1700,
            6212 => 1621,
            171 => 1701,
            170 => 1700,
            12 => 1552,
            134 => 6002,
            167 => 1569,
            16 => 1569,
            147 => 1660,
            62 => 1660,
            102 => 1700,
            6002 => 1600
        );


        if($this->competencia == 202404 || $this->competencia == 202405 || $this->competencia == 202406 || $this->competencia == 202407 || $this->competencia == 202408 || $this->competencia == 202409 || $this->competencia == 202410 || $this->competencia == 202411 || $this->competencia == 202412){
        
            //Alterado        
            $novosdados = $this->buscaSuplementacoes();
            $guardado = $this->guardaAdicionais();
            //$this->testa($novosdados); die("Veja");

            $dados = array();        
            foreach ($novosdados as $item) {            
                $codsup = $item['o47_codsup'];
            
                if (!isset($dados[$codsup])) {                
                    $dados[$codsup] = array(
                        'suplementado' => 0,
                        'reduzido' => 0,
                        'o49_data' => $item['o49_data'],
                        'lei' => $item['lei'] . " - " . trim($item["o45_descr"]),
                        'o39_codproj' => $item['o39_codproj'],
                        'decreto' => $item['decreto'] . " - ". trim($item["o39_descr"]),
                        'o46_tiposup' => $item['o46_tiposup'],
                        'o48_descr' => $item['o48_descr'],
                        'o47_coddot' => $item['o47_coddot'],
                        'o58_orgao' => $item['o58_orgao'],
                        'o47_anousu' => $item['o47_anousu'],
                        'o58_codigo' => $item['o58_codigo'],
                        'o39_usalimite' => $item['o39_usalimite'],
                        'o139_orcprojeto' => $item['o139_orcprojeto'],
                        'o56_elemento' => $item["o56_elemento"],
                    );
                }    
                $dados[$codsup]['suplementado'] += $item['suplementado'];
                $dados[$codsup]['reduzido'] += $item['reduzido'];
            }
            $dados = array_values($dados);

            if (count($dados) > 0) {
                if (empty($this->sCodigoTribunal)) {
                    throw new \Exception("O código do tribunal deve ser informado para geração do arquivo");
                }

                $RemessaAlteracaoOrcamentaria = new \stdClass();
                $alteracaoOrcamentariaReceita = array();

                for ($i = 0; $i < count($dados); $i++) {                
                    $identificador = $i+1;                
                    $valorReceitaAgrupada = $dados[$i]["suplementado"];                
                    
                    if(empty($valorReceitaAgrupada)){
                        $dadosvalor = $this->checavalor($dados["o39_codproj"]);
                        $valorReceitaAgrupada = $dadosvalor;
                    }

                    if($this->instit == 50){
                        if($fontes50[$dados[$i]["o58_codigo"]]){
                            $dados[$i]["o58_codigo"] = $fontes50[$dados[$i]["o58_codigo"]];
                        }
                    }else{
                        if($fontes0[$dados[$i]["o58_codigo"]]){
                            $dados[$i]["o58_codigo"] = $fontes50[$dados[$i]["o58_codigo"]];
                        }    
                    }

                    if($dados[$i]["o58_codigo"] == 17235040){
                        $dados[$i]["o58_codigo"] = 17235001;
                    }


                    $dextra = $this->buscaMais($dados[$i]["o39_codproj"]);
                    $nfonte = substr($dextra["o57_fonte"], 1, 8);
                            
                    $oDadosAlteracaoOrcamentaria = new \stdClass();
                    $oDadosAlteracaoOrcamentaria->Identificador = $identificador;
                    $oDadosAlteracaoOrcamentaria->CodigoUnidadeGestora = $this->sCodigoTribunal;
                    $oDadosAlteracaoOrcamentaria->Competencia = $this->competencia;
                    $oDadosAlteracaoOrcamentaria->DataAlteracao =$dados[$i]["o49_data"];
                    $oDadosAlteracaoOrcamentaria->CodigoItemReceita = $nfonte;
                    $oDadosAlteracaoOrcamentaria->TipoAtualizacao = 1;
                    $oDadosAlteracaoOrcamentaria->Valor = $valorReceitaAgrupada;
                    $lei = Helper::convertAndLimit($dados[$i]["lei"], 250);
                    $oDadosAlteracaoOrcamentaria->Lei = $lei;
                    $decreto =  Helper::convertAndLimit($dados[$i]["decreto"], 250);
                    $oDadosAlteracaoOrcamentaria->Decreto = $decreto;
                    $oDadosAlteracaoOrcamentaria->FonteRecursos = $dados[$i]["o58_codigo"];
                    $oDadosAlteracaoOrcamentaria->Deducao = 2;

                    $alteracaoOrcamentariaReceita[] =  (object) [
                        'AlteracaoOrcamentariaReceita' => $oDadosAlteracaoOrcamentaria
                    ];
                }

                $RemessaAlteracaoOrcamentaria->AlteracoesOrcamentariasReceitas = $alteracaoOrcamentariaReceita;
                $this->aDados= $RemessaAlteracaoOrcamentaria;
            }
        } else {
        
          //original
            $daoOrcSuplem  = new \cl_orcsuplem;
            $sCampos  = " distinct o46_codsup,o39_data as data_alteracao,o39_codproj,";
            $sCampos .= "o45_numlei || ' - ' || o45_descr as lei,";
            $sCampos .= "o39_codproj || ' - ' || o39_descr as decreto,";
            $sCampos .= "'1' as tipo_atualizacao,codigo_siconfi,";
            $sCampos .= "substr(planoreceita.conta,1,8) as codigo_item_receita,";
            $sCampos .= "RANK() OVER (ORDER BY substr(planoreceita.conta,1,8),codigo_siconfi) AS grupo_receita,";
            $sCampos .= " case when substr(o57_fonte,1,1) = '4' then 1
                           when substr(o57_fonte,1,1) = '9' and substr(o57_fonte,1,3) <> '917' then 2
                           when  substr(o57_fonte,1,3) = '917' then 3 else 0 end as deducao";

            $sWhere         = "o46_instit = ".$this->instit;
            $sWhere        .= " and o39_data between '{$this->dtDataInicial}' and '{$this->dtDataFinal}'";

            $sSqlOrcSuplem = $daoOrcSuplem->sql_query_suplementacoes_receita_modificado(null, $sCampos, null, $sWhere, null);
            $rsOrcSuplem   = db_query($sSqlOrcSuplem);
            //var_dump($sSqlOrcSuplem); die("Consulta");

            if (pg_num_rows($rsOrcSuplem) > 0) {            
                if (empty($this->sCodigoTribunal)) {
                    throw new \Exception("O código do tribunal deve ser informado para geração do arquivo");
                }

                $RemessaAlteracaoOrcamentaria = new \stdClass();
                $alteracaoOrcamentariaReceita = array();

                for ($i = 0; $i < pg_num_rows($rsOrcSuplem); $i++) {
                    $oDadosQuery = db_utils::fieldsMemory($rsOrcSuplem, $i);
                    $identificador = date("m", db_getsession("DB_datausu"));
                    $identificador .= $oDadosQuery->grupo_receita;
                    $identificador .= $oDadosQuery->deducao;                
                
                    $valorReceitaAgrupada = $this->getValorAgrupadoReceitaSigfis(
                        $oDadosQuery->codigo_item_receita,
                        $oDadosQuery->codigo_siconfi,
                        $oDadosQuery->deducao
                    );
                
                    if(empty($valorReceitaAgrupada)){
                        $dadosvalor = $this->checavalor($oDadosQuery->o39_codproj);
                        $valorReceitaAgrupada = $dadosvalor;
                    }
                            
                    $oDadosAlteracaoOrcamentaria = new \stdClass();
                    $oDadosAlteracaoOrcamentaria->Identificador = $identificador;
                    $oDadosAlteracaoOrcamentaria->CodigoUnidadeGestora = $this->sCodigoTribunal;
                    $oDadosAlteracaoOrcamentaria->Competencia = $this->competencia;
                    $oDadosAlteracaoOrcamentaria->DataAlteracao =$oDadosQuery->data_alteracao ;
                    $oDadosAlteracaoOrcamentaria->CodigoItemReceita = $oDadosQuery->codigo_item_receita;
                    $oDadosAlteracaoOrcamentaria->TipoAtualizacao = $oDadosQuery->tipo_atualizacao;
                    $oDadosAlteracaoOrcamentaria->Valor = $valorReceitaAgrupada;
                    $lei = Helper::convertAndLimit($oDadosQuery->lei, 250);
                    $oDadosAlteracaoOrcamentaria->Lei = $lei;
                    $decreto =  Helper::convertAndLimit($oDadosQuery->decreto, 250);
                    $oDadosAlteracaoOrcamentaria->Decreto = $decreto;
                    $oDadosAlteracaoOrcamentaria->FonteRecursos = $oDadosQuery->codigo_siconfi;
                    $oDadosAlteracaoOrcamentaria->Deducao = $oDadosQuery->deducao;

                    $alteracaoOrcamentariaReceita[] =  (object) [
                        'AlteracaoOrcamentariaReceita' => $oDadosAlteracaoOrcamentaria
                    ];
                }

                $RemessaAlteracaoOrcamentaria->AlteracoesOrcamentariasReceitas = $alteracaoOrcamentariaReceita;
                $this->aDados= $RemessaAlteracaoOrcamentaria;
            }
        }//else original
    
    }//chave da função


    public function getValorAgrupadoReceitaSigfis($codigoItemReceita, $codigoSiconfi, $deducao)
    {

        $daoOrcSuplem  = new \cl_orcsuplem;

        $campos  = "sum(o85_valor) as valor";
        
        $where  = "substr(planoreceita.conta,1,8) = '$codigoItemReceita'";
        
        if ($deducao == '1') {
            $where  .= " and substr(o57_fonte,1,1) = '4'";
        } elseif ($deducao == '2') {
            $where  .= " and substr(o57_fonte,1,1) = '9' and substr(o57_fonte,1,3) <> '917'";
        } elseif ($deducao == '3') {
            $where  .= " and substr(o57_fonte,1,3) = '917'";
        }

        $where .= " and codigo_siconfi = '$codigoSiconfi'";
        $where .= " and o46_instit = ".$this->instit;
        $where .= " and o39_data between '{$this->dtDataInicial}' and '{$this->dtDataFinal}'";

        $sSqlOrcSuplem = $daoOrcSuplem->sql_query_suplementacoes_receita_modificado(null, $campos, null, $where, null);
        //$sSqlOrcSuplem = $daoOrcSuplem->sql_query_suplementacoes_receita(null, $campos, null, $where, null);
                
        $rsOrcSuplem   = db_query($sSqlOrcSuplem);        

        $valor = 0;
        if (pg_num_rows($rsOrcSuplem) > 0) {
            $valor = db_utils::fieldsMemory($rsOrcSuplem, 0)->valor;
        }
        return $valor;
    }
}

<?php


namespace ECidade\Financeiro\Contabilidade\Exportacao\Sigfis\Arquivos\v2024;

use db_utils;

class ArquivoReceitaArrecadada extends ArquivoBase
{
    protected $sNomeArquivo  = 'ReceitaArrecadada';

    /**
    * Busca os dados para gerar o Arquivo de Unidade Orçamentária
    */

    public function testa($var){
        echo "<pre>";
        print_r($var);
        echo "</pre>";
    }

    public function gerarDados()
    {   

        $fvinculo = array(
11130111 => 11130311,
11130311 => 11130311,
11130311 => 11130311,
11130311 => 11130311,
11130311 => 11130311,
11130341 => 11130341,
11130341 => 11130341,
11130341 => 11130341,
11130341 => 11130341,
11130341 => 11130341,
11130341 => 11130341,
11130341 => 11130341,
11130341 => 11130341,
11180111 => 11125001,
11180111 => 11125001,
11180113 => 11125001,
11180113 => 11125001,
11180141 => 11125201,
11180231 => 11145111,
11180231 => 11145111,
11190113 => 11125001,
11210111 => 11210101,
11210111 => 11210101,
11210111 => 11210101,
11210111 => 11210101,
11210111 => 11210101,
11210111 => 11210101,
11220111 => 11220101,
11220111 => 11220101,
11220111 => 11220101,
11220111 => 11220101,
11220111 => 11220101,
11220111 => 11220101,
11220111 => 11220101,
11220111 => 11220101,
12415001 => 12415001,
12415001 => 12415001,
13100121 => 13110111,
13210011 => 13110111,
13210011 => 13210101,
13210011 => 13210101,
13210011 => 13210101,
13210011 => 13210101,
13210011 => 13210101,
13210011 => 13210101,
13210011 => 13210101,
13210011 => 13110111,
13210011 => 13210101,
13210011 => 13210101,
13210011 => 13210101,
13210011 => 13210101,
13210011 => 13210101,
13210011 => 13210101,
13210011 => 13210101,
13210011 => 13110111,
13210011 => 13210101,
13210011 => 13210101,
13210011 => 13210101,
13210011 => 13210101,
13210011 => 13210101,
13210011 => 13210101,
13210011 => 13210101,
13210011 => 13110111,
13210011 => 13210101,
13210011 => 13210101,
13210011 => 13210101,
13210011 => 13210101,
13210011 => 13210101,
13210011 => 13210101,
13210011 => 13210101,
13210011 => 13110111,
13210011 => 13210101,
13210011 => 13210101,
13210011 => 13210101,
13210011 => 13210101,
13210011 => 13210101,
13210011 => 13210101,
13210011 => 13210101,
13210011 => 13210101,
13210011 => 13210101,
13210011 => 13210101,
13210011 => 13210101,
13210011 => 13210101,
13210011 => 13210101,
13210011 => 13210101,
13210011 => 13210101,
13210011 => 13210101,
13210011 => 13210101,
13210011 => 13210101,
13210011 => 13210101,
13210011 => 13210101,
13210011 => 13210101,
13210011 => 13210101,
13210011 => 13210101,
13210011 => 13210101,
13210101 => 13210101,
13210101 => 13210101,
13210101 => 13210101,
13210101 => 13210101,
13210101 => 13210101,
13210101 => 13210101,
13210101 => 13210101,
13210101 => 13210101,
13210101 => 13210101,
13210101 => 13210101,
13210101 => 13210101,
13250115 => 13110111,
13250141 => 13210101,
13250199 => 13210101,
13250199 => 13210101,
13250199 => 13210101,
13250199 => 13210101,
13250199 => 13210101,
13250199 => 13210101,
13250234 => 13210101,
16100111 => 16110101,
16100111 => 16110101,
16100111 => 16110101,
17149901 => 17149901,
17179901 => 17179901,
17179901 => 17179901,
17179901 => 17179901,
17179901 => 17179901,
17179901 => 17179901,
17179901 => 17179901,
17180121 => 17115111,
17180151 => 17115111,
17180171 => 77115201,
17180221 => 17125101,
17180231 => 17125211,
17180261 => 17125241,
17180531 => 17145201,
17180541 => 17145301,
17189911 => 17129901,
17199901 => 17199901,
17213501 => 17145001,
17225303 => 17225301,
17229900 => 17249901,
17280111 => 17215001,
17280121 => 17215101,
17280131 => 17215201,
17281091 => 17299901,
19100111 => 13999901,
19219911 => 19219901,
19229911 => 19229901,
19329901 => 11125004,
19909911 => 13999901,
19909911 => 13999901,
19909921 => 13999901,
24115130 => 24115031,
24125021 => 17149901,
24149901 => 17196001,
29999010 => 21199901,
29999010 => 21199901,
17180121 => 17115111,
17180151 => 17115111,
17280111 => 17215001,
17280121 => 17215101,
17280131 => 17215201,
16100411 => 16110101,
17180911 => 17515001,
19229909 => 19229901,
13220011 => 13220101,
13999901 => 19999921,
19909912 => 19999922,
19909913 => 19999923,
33904013 => 33904014,
33903499 => 33903400,
33904007 => 33904099,
17181211 => 17165001,
13900011 => 13900000,
46907108 => 46907101,
46907109 => 46907101,
13900011 => 13999901,
15110101 => 15000011,
12180111 => 12150111,
12180112 => 12150112,
12180121 => 12150121,
12180131 => 12150131,
12180311 => 12150211,
13210043 => 13210401,
13210044 => 13210401,
13210045 => 13210401,
19219911 => 19219901,
19229911 => 19229901,
19900311 => 19990301,
19900315 => 19990301,
72180311 => 72150211,
72180312 => 72150212,
72180411 => 72155111,
13250213 => 13210101,
13250218 => 13210101,
72180412 => 7215511,
17280711 => 17295101,
17280711 => 17295101,
13250102 => 13210101,
13250299 => 13210101,
17235020 => 17235001,
19909901 => 19999911,
17235010 => 17235001,
13250212 => 13210101,
13999901 => 19999921,
15110101 => 15110101,
13210021 => 13210101,
15000011 => 15110101,
13109911 => 13110111,
17235030 => 17235001,
17175010 => 17175001,
17235040 => 17235001,
13900011 => 13999901,
17180542 => 17145301,
17213599 => 17145101,
17235060 => 17235001,
19189901 => 19229901,
13250155 => 13210101
);
        
        //echo "<pre>";
        //print_r($aReceitasVinculadas);
        //echo "</pre>";
        //die("Confere");


        $daoConlancam = new \cl_conlancam;

        $campos = " distinct substr(planoreceita.conta,1,8) as codigo_item_receita,";
        $campos .= "codigo_siconfi,o15_complemento as complemento,c70_anousu,";
        $campos .= "RANK() OVER (ORDER BY substr(planoreceita.conta,1,8),codigo_siconfi) AS grupo_receita,";
        $campos .= "extract(YEAR from c70_data) || to_char(c70_data,'MM')  as competencia,";
        $campos .= " case when substr(o57_fonte, 1, 1) = '4' then 1
                           when substr(o57_fonte, 1, 1) = '9' and substr(o57_fonte, 1, 3) <> '917' then 2
                           when substr(o57_fonte, 1, 3) = '917' then 3 else 0 end as deducao";

        
        $where = " o70_instit = " .$this->instit;
        $where .= " and c70_anousu = {$this->iAnoUsu}";
        $where .= " and c70_data between '{$this->dtDataInicial}' and '{$this->dtDataFinal}'";
        $where .= " and c53_tipo in (100,101)";

        

        //$sSqlConlancam = $daoConlancam->sql_query_receita_planoreceita_modificado($campos, null, $where);
        
        $sSqlConlancam = "SELECT distinct SUBSTRING(o57_fonte FROM 2 FOR 8) AS o57_fonte_grupo, c53_tipo, substr(planoreceita.conta,1,8) as codigo_item_receita,codigo_siconfi,o15_complemento as complemento,c70_anousu,RANK() OVER (ORDER BY substr(planoreceita.conta,1,8),codigo_siconfi) AS grupo_receita,extract(YEAR from c70_data) || to_char(c70_data,'MM') as competencia, case when substr(o57_fonte, 1, 1) = '4' then 1 when substr(o57_fonte, 1, 1) = '9' and substr(o57_fonte, 1, 3) <> '917' then 2 when substr(o57_fonte, 1, 3) = '917' then 3 else 0 end as deducao, sum(c70_valor) as vt from conlancam inner join conlancamrec on c74_codlan = c70_codlan inner join conlancamdoc on c71_codlan = c70_codlan inner join conhistdoc on c53_coddoc = c71_coddoc inner join orcreceita on o70_codrec = c74_codrec and o70_anousu = c74_anousu inner join orcfontes on o57_codfon = o70_codfon and o57_anousu = o70_anousu inner join conplanoorcamento on c60_codcon = o57_codfon and c60_anousu = o57_anousu left join planoreceitaconplanoorcamento on conplanoorcamento_codigo = c60_codigo left join planoreceita on planoreceita.id = planoreceita_id and planoreceita.exercicio = c70_anousu and planoreceita.uniao = 't' inner join orctiporec on o15_codigo = o70_codigo inner join fonterecurso on orctiporec_id = o15_codigo and fonterecurso.exercicio = c74_anousu where o70_instit = {$this->instit} and c70_anousu = {$this->iAnoUsu} and c70_data between '{$this->dtDataInicial}' and '{$this->dtDataFinal}' and c53_tipo in (100,101) GROUP BY SUBSTRING(o57_fonte FROM 2 FOR 8), c53_tipo,
    substr(planoreceita.conta,1,8),
    codigo_siconfi,
    o15_complemento,
    c70_anousu,
    EXTRACT(YEAR FROM c70_data) || TO_CHAR(c70_data,'MM'),
    CASE 
        WHEN substr(o57_fonte, 1, 1) = '4' THEN 1 
        WHEN substr(o57_fonte, 1, 1) = '9' AND substr(o57_fonte, 1, 3) <> '917' THEN 2 
        WHEN substr(o57_fonte, 1, 3) = '917' THEN 3 
        ELSE 0 
    END";
        
        $rsConlancam   = db_query($sSqlConlancam);
        
        
        if (pg_num_rows($rsConlancam) > 0) {
            if (empty($this->sCodigoTribunal)) {
                throw new \Exception("O código do tribunal deve ser informado para geração do arquivo");
            }
            
            $RemessaReceitaArrecadada = new \stdClass();
            $receitaArrecadada = array();

            $valorArrecadado = 0;
            $ix = 1;
            
            if($this->instit == 80){
                $xxx = pg_fetch_all($rsConlancam);

                $dados = array();
                $dadosfinais = array();
            
                foreach ($xxx as $item) {
                    $grupo = $item['o57_fonte_grupo'];
                    if (!isset($dados[$grupo])) {
                        $dados[$grupo] = array(
                            'tipo_100' => 0,
                            'tipo_101' => 0,
                            'c70_anousu' => $item['c70_anousu'],
                            'competencia' => $item['competencia'],
                            'grupo_receita' => $item['grupo_receita'],
                            'codigo_item_receita' => $item['codigo_item_receita'],
                            'codigo_siconfi' => $item['codigo_siconfi'],
                            'complemento' => $item['complemento'],
                            'deducao' => $item['deducao']
                        );
                    }
                    if ($item['c53_tipo'] == 100) {
                        $dados[$grupo]['tipo_100'] += $item['vt'];
                    } elseif ($item['c53_tipo'] == 101) {
                        $dados[$grupo]['tipo_101'] += $item['vt'];
                    }
                }

                foreach ($dados as $grupo => $linha) {
                    $dadosfinais[] = array(
                        'o57_fonte_grupo' => $grupo,
                        'c53_tipo' => 100,
                        'codigo_item_receita' => $linha['codigo_item_receita'],
                        'codigo_siconfi' => $linha['codigo_siconfi'],
                        'complemento' => $linha['complemento'],
                        'c70_anousu' => $linha['c70_anousu'],
                        'grupo_receita' => $linha['grupo_receita'],
                        'competencia' => $linha['competencia'],
                        'deducao' => $linha['deducao'],
                        'vt' => $linha['tipo_100'] - $linha['tipo_101']
                    );
                }
            
                foreach ($dadosfinais as $oDadosQuery) {
                    $identificador = $ix;                
                    $estorno = "";
                    if (in_array($oDadosQuery["deducao"], [2,3]) && $oDadosQuery["c53_tipo"] == '100') {
                        $estorno = 'Sim';
                    } elseif ($oDadosQuery["deducao"] == 1 && $oDadosQuery["c53_tipo"] == '101') {
                        $estorno = 'Sim';
                    } else {
                        $estorno = 'Nao';
                    }
                    $oDadosReceitaArrecadada = new \stdClass();
                    $complemento = $oDadosQuery["complemento"];
                    if ($oDadosQuery["complemento"] == '0') {
                        $complemento = '0000';
                    }                
                    if($fvinculo[$oDadosQuery["o57_fonte_grupo"]]){
                        $oDadosQuery["o57_fonte_grupo"] = $fvinculo[$oDadosQuery["o57_fonte_grupo"]];
                    }                
                    $oDadosReceitaArrecadada->Identificador = $identificador;
                    $oDadosReceitaArrecadada->CodigoUnidadeGestora = $this->sCodigoTribunal;
                    $oDadosReceitaArrecadada->Competencia = $oDadosQuery["competencia"];
                    $oDadosReceitaArrecadada->CodigoItemReceita = $oDadosQuery["o57_fonte_grupo"];//$oDadosQuery->codigo_item_receita ;
                    $oDadosReceitaArrecadada->FonteRecursos = $oDadosQuery["codigo_siconfi"];
                    $oDadosReceitaArrecadada->Valor = abs($oDadosQuery["vt"]); //abs($valorArrecadado);
                    $oDadosReceitaArrecadada->Deducao = $oDadosQuery["deducao"];
                    $oDadosReceitaArrecadada->Estorno = $estorno;
                    $oDadosReceitaArrecadada->CodigoAcompanhamentoExecucaoOrcamentaria = $complemento;

                    $receitaArrecadada[] =  (object) ['ReceitaArrecadada' => $oDadosReceitaArrecadada];
                }
            }else{                

                for ($i = 0; $i < pg_num_rows($rsConlancam); $i++) {
                $oDadosQuery = db_utils::fieldsMemory($rsConlancam, $i);                

                $identificador = $ix;                
                $estorno = "";
                if (in_array($oDadosQuery->deducao, [2,3]) && $oDadosQuery->c53_tipo == '100') {
                    $estorno = 'Sim';
                } elseif ($oDadosQuery->deducao == 1 && $oDadosQuery->c53_tipo == '101') {
                    $estorno = 'Sim';
                } else {
                    $estorno = 'Nao';
                }

                $oDadosReceitaArrecadada = new \stdClass();

                $complemento = $oDadosQuery->complemento;

                if ($oDadosQuery->complemento == '0') {
                    $complemento = '0000';
                }

                
                if($fvinculo[$oDadosQuery->o57_fonte_grupo]){
                    $oDadosQuery->o57_fonte_grupo = $fvinculo[$oDadosQuery->o57_fonte_grupo];
                }
                
                $oDadosReceitaArrecadada->Identificador = $identificador;
                $oDadosReceitaArrecadada->CodigoUnidadeGestora = $this->sCodigoTribunal;
                $oDadosReceitaArrecadada->Competencia = $oDadosQuery->competencia;
                $oDadosReceitaArrecadada->CodigoItemReceita = $oDadosQuery->o57_fonte_grupo;//$oDadosQuery->codigo_item_receita ;
                $oDadosReceitaArrecadada->FonteRecursos = $oDadosQuery->codigo_siconfi;
                $oDadosReceitaArrecadada->Valor = abs($oDadosQuery->vt); //abs($valorArrecadado);
                $oDadosReceitaArrecadada->Deducao = $oDadosQuery->deducao;
                $oDadosReceitaArrecadada->Estorno = $estorno;
                $oDadosReceitaArrecadada->CodigoAcompanhamentoExecucaoOrcamentaria = $complemento;

                $receitaArrecadada[] =  (object) ['ReceitaArrecadada' => $oDadosReceitaArrecadada];
                $ix++;
            }
            }           
            

            $resultado = array();
            //$this->testa($receitaArrecadada); die("Confere");
            foreach($receitaArrecadada as $objeto) {
                $codigoUnidadeGestora = $objeto->ReceitaArrecadada->CodigoUnidadeGestora;
                $codigoItemReceita = $objeto->ReceitaArrecadada->CodigoItemReceita;
                $fonteRecursos = $objeto->ReceitaArrecadada->FonteRecursos;
                $deducao = $objeto->ReceitaArrecadada->Deducao;
                $estorno = $objeto->ReceitaArrecadada->Estorno;
                
                $chave = "{$codigoUnidadeGestora}_{$codigoItemReceita}_{$fonteRecursos}_{$deducao}_{$estorno}";

                if (!isset($resultado[$chave])) {                    
                    $resultado[$chave] = new \stdClass();
                    $resultado[$chave]->ReceitaArrecadada = new \stdClass();        
                    $resultado[$chave]->ReceitaArrecadada->Identificador = $objeto->ReceitaArrecadada->Identificador;
                    $resultado[$chave]->ReceitaArrecadada->CodigoUnidadeGestora = $codigoUnidadeGestora;
                    $resultado[$chave]->ReceitaArrecadada->Competencia = $objeto->ReceitaArrecadada->Competencia;
                    $resultado[$chave]->ReceitaArrecadada->CodigoItemReceita = $codigoItemReceita;
                    $resultado[$chave]->ReceitaArrecadada->FonteRecursos = $fonteRecursos;
                    $resultado[$chave]->ReceitaArrecadada->Valor = $objeto->ReceitaArrecadada->Valor;
                    $resultado[$chave]->ReceitaArrecadada->Deducao = $deducao;
                    $resultado[$chave]->ReceitaArrecadada->Estorno = $estorno;
                    $resultado[$chave]->ReceitaArrecadada->CodigoAcompanhamentoExecucaoOrcamentaria = $objeto->ReceitaArrecadada->CodigoAcompanhamentoExecucaoOrcamentaria;
                } else {
                    $resultado[$chave]->ReceitaArrecadada->Valor += $objeto->ReceitaArrecadada->Valor;
                }
            }

            $receitaArrecadadaFinal = array();
            foreach ($resultado as $item) {
                $receitaArrecadadaFinal[] = $item;
            }
            
            $RemessaReceitaArrecadada->ReceitasArrecadadas = $receitaArrecadadaFinal;
            //die("Parou aqui");
            $this->aDados= $RemessaReceitaArrecadada;
        }
    }
}

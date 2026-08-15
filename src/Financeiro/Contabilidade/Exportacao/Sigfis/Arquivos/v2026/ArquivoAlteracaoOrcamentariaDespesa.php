<?php

namespace ECidade\Financeiro\Contabilidade\Exportacao\Sigfis\Arquivos\v2024;

use db_utils;
use ECidade\Financeiro\Contabilidade\Exportacao\Sigfis\Common\Helper;

class ArquivoAlteracaoOrcamentariaDespesa extends ArquivoBase
{

    protected $sNomeArquivo  = 'AlteracaoOrcamentariaDespesa';

    const SUPLEMENTACAO_POR_REDUCAO = '1001';
    const SUPERAVIT_FINANCEIRO = '1003';
    const ARRECADACAO_A_MAIOR = '1004';
    const CREDITOS_ESPECIAIS_POR_REDUCAO = '1006';
    const CREDITOS_ESPECIAIS_POR_SUPERAVIT_FINANCEIRO = '1008';
    const CREDITO_ESPECIAL_ARRECADACAO_A_MAIOR = '1009';
    const CREDITOS_ESPECIAIS_POR_AUXILIOS_E_CONVENIOS = '1010';
    const CREDITOS_EXTRAORDINARIOS  = '1011';
    const REABERTURA_DE_CREDITOS_ESPECIAIS = '1012';
    const REABERTURA_DE_CREDITOS_EXTRAORDINARIOS = '1013';
    const TRANSFERENCIA_DE_RECURSOS = '1014';
    const REMANEJAMENTO_DE_RECURSOS = '1015';
    const TRANSPOSICAO_DE_RECURSOS = '1016';
    const OPERACAO_DE_CREDITO = '1002';
    const CREDITOS_ESPECIAIS_POR_OPERACAO_DE_CREDITO = '1007';
    const AUXILIOS_A_CONVENIOS = '1005';
    const REDUCAO_DO_ORÇAMENTO = '1050';

    /**
    * Busca os dados para gerar o Arquivo de Unidade Orçamentária
    */

    public function buscaSuplementacoes(){
        $sql = pg_query("SELECT distinct o47_codsup, o49_data as data_instrumento, o45_numlei as lei, o39_codproj, o39_numero as numero_instrumento, o46_tiposup, o48_descr, o47_coddot, o58_orgao, o47_anousu, case when o47_valor > 0 then o47_valor end as suplementado, case when o47_valor < 0 then o47_valor *-1 end as reduzido, o58_codigo, o39_usalimite, o139_orcprojeto, o58_unidade, o39_anousu as ano_alteracao, '1' as tipo_atualizacao, o45_datafim as data_lei_autorizativa, o48_tiposup, o58_funcao, o58_subfuncao, o58_programa, o55_tipo, o55_projativ, c60_estrut, codigo_siconfi, o47_valor as valor from orcsuplem inner join orcsuplemtipo on o48_tiposup = orcsuplem.o46_tiposup inner join orcsuplemval on o47_codsup=o46_codsup left outer join orcsuplemlan on o49_codsup = o47_codsup inner join orcprojeto on o39_codproj = orcsuplem.o46_codlei left join orcprojetoorcprojetolei on o39_codproj = o139_orcprojeto inner join orclei on o45_codlei = orcprojeto.o39_codlei left join orcsuplemretif on o48_retificado = orcprojeto.o39_codproj inner join orcdotacao on o58_coddot =orcsuplemval.o47_coddot and o58_anousu=orcsuplemval.o47_anousu and o58_instit in ({$this->instit}) inner join orcelemento on o58_codele = o56_codele and o56_anousu = o58_anousu inner join orctiporec on o58_codigo = o15_codigo inner join complementofonterecurso on o15_complemento = o200_sequencial inner join orcprojativ on o55_projativ = o58_projativ and o55_anousu = o58_anousu inner join conplanoorcamento on conplanoorcamento.c60_anousu = o58_anousu and conplanoorcamento.c60_codcon = o58_codele inner join fonterecurso on fonterecurso.orctiporec_id = o15_codigo and fonterecurso.exercicio = o58_anousu where ( o39_usalimite is true or o139_orcprojeto is not null) and o48_retificado is null and o46_tiposup in (1001,1002,1003,1004,1005,1006,1007,1008,1009,1010,1011,1012,1013,1014,1015,1016,1050) and 1=1 and orcsuplemlan.o49_data >= '{$this->dtDataInicial}' and orcsuplemlan.o49_data <= '{$this->dtDataFinal}' and o49_codsup is not null");
        $resultado = pg_fetch_all($sql);
        return $resultado;
    }

    public function testa($var){
        echo "<pre>";
        print_r($var);
        echo "</pre>";
    }

    public function gerarDados(){           

        $daoOrcSuplem  = new \cl_orcsuplem;

        $sCampos  = " distinct o46_codsup,o47_valor as valor,";
        //$sCampos .= "RANK() OVER (PARTITION BY o55_projativ ORDER BY o46_codsup,";
        //$sCampos .= "codigo_siconfi,substr(planodespesa.conta,1,6)) AS grupo_despesa,";
        $sCampos .= " o45_descr as lei,o45_datafim as data_lei_autorizativa,";
        $sCampos .= "o39_numero as numero_instrumento,o39_anousu as ano_alteracao,o49_data as data_instrumento,";
        $sCampos .= "'1' as tipo_atualizacao,o15_codigo,o48_tiposup,";
        //$sCampos .= "substr(planodespesa.conta,1,6) as natureza_despesa,";
        $sCampos .= "o58_programa,o58_funcao,o58_subfuncao,o55_projativ,";
        $sCampos .= "o55_tipo,o58_orgao,o58_unidade,codigo_siconfi, c60_estrut";

        $sWhere  = "o46_instit = ".db_getsession('DB_instit');
        $sWhere .= " and o49_data between '{$this->dtDataInicial}' and '{$this->dtDataFinal}'";

        $orderBy = "o55_projativ";
        //$sSqlOrcSuplem = $daoOrcSuplem->sql_query_suplementacoes_despesa(null, $sCampos, $orderBy, $sWhere);
        $sSqlOrcSuplem = $daoOrcSuplem->sql_query_suplementacoes_despesaSigfis(null, $sCampos, $orderBy, $sWhere);

        $rsOrcSuplem   = db_query($sSqlOrcSuplem);
        //var_dump($sSqlOrcSuplem); die("Confere");

        if(($this->instit == 30 || $this->instit == 45 || $this->instit == 75 || $this->instit == 85 || $this->instit == 90) && pg_num_rows($rsOrcSuplem) == 0){
            $inst = db_getsession("DB_instit");
                $novosql = "SELECT distinct o47_codsup, o49_data as data_instrumento, o45_numlei as lei, o39_codproj, o39_numero as numero_instrumento, o46_tiposup, o48_descr, o47_coddot, o58_orgao, o47_anousu, case when o47_valor > 0 then o47_valor end as suplementado, case when o47_valor < 0 then o47_valor *-1 end as reduzido, o58_codigo, o39_usalimite, o139_orcprojeto, o58_unidade, o39_anousu as ano_alteracao, '1' as tipo_atualizacao, o45_datafim as data_lei_autorizativa, o48_tiposup, o58_funcao, o58_subfuncao, o58_programa, o55_tipo, o55_projativ, c60_estrut, codigo_siconfi, o47_valor as valor from orcsuplem inner join orcsuplemtipo on o48_tiposup = orcsuplem.o46_tiposup inner join orcsuplemval on o47_codsup=o46_codsup left outer join orcsuplemlan on o49_codsup = o47_codsup inner join orcprojeto on o39_codproj = orcsuplem.o46_codlei left join orcprojetoorcprojetolei on o39_codproj = o139_orcprojeto inner join orclei on o45_codlei = orcprojeto.o39_codlei left join orcsuplemretif on o48_retificado = orcprojeto.o39_codproj inner join orcdotacao on o58_coddot =orcsuplemval.o47_coddot and o58_anousu=orcsuplemval.o47_anousu and o58_instit in ({$inst}) inner join orcelemento on o58_codele = o56_codele and o56_anousu = o58_anousu inner join orctiporec on o58_codigo = o15_codigo inner join complementofonterecurso on o15_complemento = o200_sequencial inner join orcprojativ on o55_projativ = o58_projativ and o55_anousu = o58_anousu inner join conplanoorcamento on conplanoorcamento.c60_anousu = o58_anousu and conplanoorcamento.c60_codcon = o58_codele inner join fonterecurso on fonterecurso.orctiporec_id = o15_codigo and fonterecurso.exercicio = o58_anousu where ( o39_usalimite is true or o139_orcprojeto is not null) and o48_retificado is null and o46_tiposup in (1001,1002,1003,1004,1005,1006,1007,1008,1009,1010,1011,1012,1013,1014,1015,1016,1050) and 1=1 and orcsuplemlan.o49_data >= '{$this->dtDataInicial}' and orcsuplemlan.o49_data <= '{$this->dtDataFinal}' and o49_codsup is not null";
                    $novosdados = pg_query($novosql);
                    $rsOrcSuplem = $novosdados;
            }
                    

        
        //$nuevo = $this->buscaSuplementacoes();
        //var_dump($nuevo);
        //die("Testa");
        if(db_getsession("DB_instit") == 50){
            $fonteselemento = array(
            //337270 => 337239,
            //337270 => 337299,
            337270 => 337170,
            319009 => 339008,
            319034 => 339034
        );
            if (pg_num_rows($rsOrcSuplem) > 0){
            
            if (empty($this->sCodigoTribunal)) {
                throw new \Exception("O código do tribunal deve ser informado para geração do arquivo");
            }

            $RemessaAlteracaoOrcamentariaDespesa = new \stdClass();
            $alteracaoOrcamentariaDespesa = array();
            $ix = 1;
            $guarda = array();
            $guardavalor = array();

            for ($i = 0; $i < pg_num_rows($rsOrcSuplem); $i++) {
                $oDadosQuery = db_utils::fieldsMemory($rsOrcSuplem, $i);


                //if(abs($oDadosQuery->valor) != "360000" && abs($oDadosQuery->valor) != "45000"){continue;}

                $anoInstrumento = explode("-", $oDadosQuery->data_instrumento)[0];
                $mesInstrumento = explode("-", $oDadosQuery->data_instrumento)[1];
                $competencia = $anoInstrumento.$mesInstrumento;
                
                $identificador = $mesInstrumento;
                $identificador .= substr($oDadosQuery->ano_alteracao, 2, 2);
                $identificador .= $oDadosQuery->o55_projativ;
                $identificador .= $oDadosQuery->grupo_despesa;

                $deParaSuplemTipo = $this->deParaSuplemTipo($oDadosQuery->o48_tiposup);

                if($fontessubelemento[substr($oDadosQuery->c60_estrut, 1, 6)]){
                    $oDadosQuery->c60_estrut = $fontessubelemento[substr($oDadosQuery->c60_estrut, 1, 6)];
                }

                $conta = substr($oDadosQuery->c60_estrut, 1, 6);
                if($fonteselemento[$conta]){
                    $conta = $fonteselemento[$conta];
                }else{
                    $conta = substr($oDadosQuery->c60_estrut, 1, 6);
                }


                $noinstrumento = str_replace(".", "", $oDadosQuery->numero_instrumento);
                $codfunc = (strlen($oDadosQuery->o58_funcao) == 1) ? "0" . $oDadosQuery->o58_funcao : $oDadosQuery->o58_funcao;
                $codsubfunc = (strlen($oDadosQuery->o58_subfuncao) == 2) ? "0" . $oDadosQuery->o58_subfuncao : $oDadosQuery->o58_subfuncao;
                $codacao = (strlen($oDadosQuery->o55_projativ) == 3) ? "0".$oDadosQuery->o55_projativ : $oDadosQuery->o55_projativ;

                $chave = "c" . $oDadosQuery->o58_orgao . $oDadosQuery->o58_unidade . $noinstrumento . $oDadosQuery->tipo_atualizacao . $deParaSuplemTipo->tipoalteracao . $deParaSuplemTipo->fonteabertura . $codfunc . $codsubfunc . $oDadosQuery->o58_programa . $oDadosQuery->o55_tipo . $codacao . $conta . "c";
                //$chave = string($chave);
                
                //if($chave != "5011865712110122110126514339039"){continue;}

                
                /*
                if(in_array($chave, $guarda)){
                    $guardavalor[$chave] += abs($oDadosQuery->valor);
                    continue;
                }else{
                    $xvalor = abs($oDadosQuery->valor);
                }
                array_push($guarda, $chave);
                */
                /*
                var_dump($chave);
                var_dump(abs($oDadosQuery->valor)); echo "<br>";
                if(in_array($chave, $guardavalor)){
                    $guardavalor[$chave] += abs($oDadosQuery->valor);
                }else{
                    $guardavalor[$chave] = abs($oDadosQuery->valor);
                }

                if(in_array($chave, $guarda)){
                    $guardavalor[$chave] += abs($oDadosQuery->valor);
                    continue;
                }else{
                    $xvalor = abs($oDadosQuery->valor);
                }
                array_push($guarda, $chave);
                */

                
                if(array_key_exists($chave, $guardavalor)){                    
                    //var_dump($chave);
                    //var_dump(abs($oDadosQuery->valor)); echo "<br>";
                    $xvalor = abs($oDadosQuery->valor);
                    $xvalor = (float)$xvalor;
                    $guardavalor[$chave] += $xvalor;
                }else{
                    //var_dump($chave);
                    //var_dump(abs($oDadosQuery->valor)); echo "<br>";
                    $xvalor = abs($oDadosQuery->valor);
                    $xvalor = (float)$xvalor;
                    $guardavalor[$chave] = $xvalor;
                }

                if(in_array($chave, $guarda)){
                    continue;
                }
                array_push($guarda, $chave);

                

                
                //var_dump($guardavalor);
                //var_dump($guardavalor[$chave]); echo "<br>";

                $oDadosAlteracaoOrcamentariaDespesa = new \stdClass();
                $oDadosAlteracaoOrcamentariaDespesa->Identificador = $ix; //$identificador;
                $oDadosAlteracaoOrcamentariaDespesa->CodigoOrgao = $oDadosQuery->o58_orgao;
                $oDadosAlteracaoOrcamentariaDespesa->CodigoUnidadeOrcamentaria = $oDadosQuery->o58_unidade;
                $oDadosAlteracaoOrcamentariaDespesa->Competencia = $competencia;
                $oDadosAlteracaoOrcamentariaDespesa->CodigoUnidadeGestora = $this->sCodigoTribunal;
                $oDadosAlteracaoOrcamentariaDespesa->NumeroInstrumentoAlteracao = $oDadosQuery->numero_instrumento;
                $oDadosAlteracaoOrcamentariaDespesa->AnoAlteracao = $oDadosQuery->ano_alteracao;
                $oDadosAlteracaoOrcamentariaDespesa->TipoInstrumentoAlteracao = $oDadosQuery->tipo_atualizacao;
                $oDadosAlteracaoOrcamentariaDespesa->DataInstrumentoAlteracao =$oDadosQuery->data_instrumento;
                $oDadosAlteracaoOrcamentariaDespesa->DataPublicacao =$oDadosQuery->data_instrumento;
                $lei = Helper::convertAndLimit($oDadosQuery->lei, 250);
                $oDadosAlteracaoOrcamentariaDespesa->NumeroLeiAutorizativa = $lei;
                $oDadosAlteracaoOrcamentariaDespesa->DataLeiAutorizativa = $oDadosQuery->data_lei_autorizativa;
                $oDadosAlteracaoOrcamentariaDespesa->TipoAlteracao = $deParaSuplemTipo->tipoalteracao;
                $oDadosAlteracaoOrcamentariaDespesa->FonteAbertura = $deParaSuplemTipo->fonteabertura;
                $oDadosAlteracaoOrcamentariaDespesa->CodigoFuncao = (strlen($oDadosQuery->o58_funcao) == 1) ? "0" . $oDadosQuery->o58_funcao : $oDadosQuery->o58_funcao;
                $oDadosAlteracaoOrcamentariaDespesa->CodigoSubFuncao = (strlen($oDadosQuery->o58_subfuncao) == 2) ? "0" . $oDadosQuery->o58_subfuncao : $oDadosQuery->o58_subfuncao;
                $oDadosAlteracaoOrcamentariaDespesa->CodigoPrograma = $oDadosQuery->o58_programa;
                $oDadosAlteracaoOrcamentariaDespesa->TipoAcao = $oDadosQuery->o55_tipo;
                $oDadosAlteracaoOrcamentariaDespesa->CodigoAcao =  (strlen($oDadosQuery->o55_projativ) == 3 && $oDadosQuery->o55_projativ != 300) ? "0".$oDadosQuery->o55_projativ : $oDadosQuery->o55_projativ;
                $oDadosAlteracaoOrcamentariaDespesa->NaturezaDespesa = $conta; //substr($oDadosQuery->c60_estrut, 1, 6);
                $oDadosAlteracaoOrcamentariaDespesa->FonteRecurso = $oDadosQuery->codigo_siconfi;
                $oDadosAlteracaoOrcamentariaDespesa->ValorAlteracao = $chave; //($guardavalor[$chave]) ? $guardavalor[$chave] : $xvalor; //abs($oDadosQuery->valor);
                $oDadosAlteracaoOrcamentariaDespesa->CodigoCreditoAdicional = $deParaSuplemTipo->creditoadicional;

                $alteracaoOrcamentariaDespesa[] =  (object) [
                    'AlteracaoOrcamentariaDespesa' => $oDadosAlteracaoOrcamentariaDespesa
                ];
                $ix++;                
            }

            //$this->testa($alteracaoOrcamentariaDespesa);
            //die("Mostra");

            
            foreach ($alteracaoOrcamentariaDespesa as $linha) {
                $linha->AlteracaoOrcamentariaDespesa->ValorAlteracao = $guardavalor[$linha->AlteracaoOrcamentariaDespesa->ValorAlteracao];
            }
            

            //$this->testa($guardavalor);
            //die("confere");

            $RemessaAlteracaoOrcamentariaDespesa->AlteracoesOrcamentariasDespesas = $alteracaoOrcamentariaDespesa;
            $this->aDados= $RemessaAlteracaoOrcamentariaDespesa;
        }

        }else{
            if (pg_num_rows($rsOrcSuplem) > 0) {            
            
                if (empty($this->sCodigoTribunal)) {
                    throw new \Exception("O código do tribunal deve ser informado para geração do arquivo");
                }

                $fontessubelemento = array(            
                    319036 => 339036
                );

            $RemessaAlteracaoOrcamentariaDespesa = new \stdClass();
            $alteracaoOrcamentariaDespesa = array();
            $ix = 1;
            for ($i = 0; $i < pg_num_rows($rsOrcSuplem); $i++) {
                $oDadosQuery = db_utils::fieldsMemory($rsOrcSuplem, $i);

                $anoInstrumento = explode("-", $oDadosQuery->data_instrumento)[0];
                $mesInstrumento = explode("-", $oDadosQuery->data_instrumento)[1];
                $competencia = $anoInstrumento.$mesInstrumento;

                
                $identificador = $mesInstrumento;
                $identificador .= substr($oDadosQuery->ano_alteracao, 2, 2);
                $identificador .= $oDadosQuery->o55_projativ;
                $identificador .= $oDadosQuery->grupo_despesa;

                $deParaSuplemTipo = $this->deParaSuplemTipo($oDadosQuery->o48_tiposup);

                if($fontessubelemento[substr($oDadosQuery->c60_estrut, 1, 6)]){
                    $natdespesa = $fontessubelemento[substr($oDadosQuery->c60_estrut, 1, 6)];
                    //$oDadosQuery->c60_estrut = $fontessubelemento[substr($oDadosQuery->c60_estrut, 1, 6)];
                }else{
                    $natdespesa = substr($oDadosQuery->c60_estrut, 1, 6);
                }


                $oDadosAlteracaoOrcamentariaDespesa = new \stdClass();
                $oDadosAlteracaoOrcamentariaDespesa->Identificador = $ix; //$identificador;
                $oDadosAlteracaoOrcamentariaDespesa->CodigoOrgao = $oDadosQuery->o58_orgao;
                $oDadosAlteracaoOrcamentariaDespesa->CodigoUnidadeOrcamentaria =$oDadosQuery->o58_unidade;
                $oDadosAlteracaoOrcamentariaDespesa->Competencia = $competencia;
                $oDadosAlteracaoOrcamentariaDespesa->CodigoUnidadeGestora = $this->sCodigoTribunal;
                $oDadosAlteracaoOrcamentariaDespesa->NumeroInstrumentoAlteracao = $oDadosQuery->numero_instrumento;
                $oDadosAlteracaoOrcamentariaDespesa->AnoAlteracao = $oDadosQuery->ano_alteracao;
                $oDadosAlteracaoOrcamentariaDespesa->TipoInstrumentoAlteracao = $oDadosQuery->tipo_atualizacao;
                $oDadosAlteracaoOrcamentariaDespesa->DataInstrumentoAlteracao =$oDadosQuery->data_instrumento;
                $oDadosAlteracaoOrcamentariaDespesa->DataPublicacao =$oDadosQuery->data_instrumento;
                $lei = Helper::convertAndLimit($oDadosQuery->lei, 250);
                $oDadosAlteracaoOrcamentariaDespesa->NumeroLeiAutorizativa = $lei;
                $oDadosAlteracaoOrcamentariaDespesa->DataLeiAutorizativa = $oDadosQuery->data_lei_autorizativa;
                $oDadosAlteracaoOrcamentariaDespesa->TipoAlteracao = $deParaSuplemTipo->tipoalteracao;
                $oDadosAlteracaoOrcamentariaDespesa->FonteAbertura = $deParaSuplemTipo->fonteabertura;
                $oDadosAlteracaoOrcamentariaDespesa->CodigoFuncao = (strlen($oDadosQuery->o58_funcao) == 1) ? "0" . $oDadosQuery->o58_funcao : $oDadosQuery->o58_funcao;
                $oDadosAlteracaoOrcamentariaDespesa->CodigoSubFuncao = (strlen($oDadosQuery->o58_subfuncao) == 2) ? "0" . $oDadosQuery->o58_subfuncao : $oDadosQuery->o58_subfuncao;
                $oDadosAlteracaoOrcamentariaDespesa->CodigoPrograma = $oDadosQuery->o58_programa;
                $oDadosAlteracaoOrcamentariaDespesa->TipoAcao = $oDadosQuery->o55_tipo;
                $oDadosAlteracaoOrcamentariaDespesa->CodigoAcao =  (strlen($oDadosQuery->o55_projativ) == 3 && $oDadosQuery->o55_projativ != 300) ? "0".$oDadosQuery->o55_projativ : $oDadosQuery->o55_projativ;
                $oDadosAlteracaoOrcamentariaDespesa->NaturezaDespesa = $natdespesa;
                $oDadosAlteracaoOrcamentariaDespesa->FonteRecurso = $oDadosQuery->codigo_siconfi;
                $oDadosAlteracaoOrcamentariaDespesa->ValorAlteracao = abs($oDadosQuery->valor);
                $oDadosAlteracaoOrcamentariaDespesa->CodigoCreditoAdicional = $deParaSuplemTipo->creditoadicional;

                $alteracaoOrcamentariaDespesa[] =  (object) [
                    'AlteracaoOrcamentariaDespesa' => $oDadosAlteracaoOrcamentariaDespesa
                ];
                $ix++;
            }

            $RemessaAlteracaoOrcamentariaDespesa->AlteracoesOrcamentariasDespesas = $alteracaoOrcamentariaDespesa;
            $this->aDados= $RemessaAlteracaoOrcamentariaDespesa;
        }
        }
        
    }

    public function deParaSuplemTipo($tipoSuplementacao)
    {

        switch ($tipoSuplementacao) {
            case static::SUPLEMENTACAO_POR_REDUCAO:
                return (object) [
                    'tipoalteracao' => '2',
                    'fonteabertura' => '1',
                    'creditoadicional' => '1'
                ];

            case static::SUPERAVIT_FINANCEIRO:
                return (object) [
                    'tipoalteracao' => '2',
                    'fonteabertura' => '3',
                    'creditoadicional' => '1'
                ];
            case static::ARRECADACAO_A_MAIOR:
                return (object) [
                    'tipoalteracao' => '2',
                    'fonteabertura' => '2',
                    'creditoadicional' => '1'
                ];
            case static::CREDITOS_ESPECIAIS_POR_REDUCAO:
                return (object) [
                    'tipoalteracao' => '3',
                    'fonteabertura' => '1',
                    'creditoadicional' => '2'
                ];
            case static::CREDITOS_ESPECIAIS_POR_SUPERAVIT_FINANCEIRO:
                return (object) [
                    'tipoalteracao' => '3',
                    'fonteabertura' => '3',
                    'creditoadicional' => '2'
                ];
            case static::CREDITO_ESPECIAL_ARRECADACAO_A_MAIOR:
                return (object) [
                    'tipoalteracao' => '3',
                    'fonteabertura' => '2',
                    'creditoadicional' => '2'
                ];
            case static::CREDITOS_ESPECIAIS_POR_AUXILIOS_E_CONVENIOS:
                return (object) [
                    'tipoalteracao' => '3',
                    'fonteabertura' => '4',
                    'creditoadicional' => '2'
                ];
            case static::CREDITOS_EXTRAORDINARIOS:
                return (object) [
                    'tipoalteracao' => '4',
                    'fonteabertura' => '1',
                    'creditoadicional' => '3'
                ];
            case static::REABERTURA_DE_CREDITOS_ESPECIAIS:
                return (object) [
                    'tipoalteracao' => '3',
                    'fonteabertura' => '3',
                    'creditoadicional' => '2'
                ];
            case static::REABERTURA_DE_CREDITOS_EXTRAORDINARIOS:
                return (object) [
                    'tipoalteracao' => '4',
                    'fonteabertura' => '3',
                    'creditoadicional' => '3'
                ];
            case static::TRANSFERENCIA_DE_RECURSOS:
                return (object) [
                    'tipoalteracao' => '2',
                    'fonteabertura' => '8',
                    'creditoadicional' => '1'
                ];
            case static::REMANEJAMENTO_DE_RECURSOS:
                return (object) [
                    'tipoalteracao' => '2',
                    'fonteabertura' => '7',
                    'creditoadicional' => '2'
                ];
            case static::TRANSPOSICAO_DE_RECURSOS:
                return (object) [
                    'tipoalteracao' => '2',
                    'fonteabertura' => '6',
                    'creditoadicional' => '1'
                ];
            case static::OPERACAO_DE_CREDITO:
                return (object) [
                    'tipoalteracao' => '2',
                    'fonteabertura' => '5',
                    'creditoadicional' => '1'
                ];
            case static::CREDITOS_ESPECIAIS_POR_OPERACAO_DE_CREDITO:
                return (object) [
                    'tipoalteracao' => '3',
                    'fonteabertura' => '5',
                    'creditoadicional' => '2'
                ];
            case static::AUXILIOS_A_CONVENIOS:
                return (object) [
                    'tipoalteracao' => '2',
                    'fonteabertura' => '4',
                    'creditoadicional' => '1'
                ];
            case static::REDUCAO_DO_ORÇAMENTO:
                return (object) [
                    'tipoalteracao' => '2',
                    'fonteabertura' => '1',
                    'creditoadicional' => '1'
                ];
        }
    }
}

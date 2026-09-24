<?php

namespace ECidade\Financeiro\Contabilidade\Exportacao\Sigfis\Arquivos\v2024;

use ECidade\Financeiro\Contabilidade\Exportacao\Sigfis\Arquivos\v2024\ArquivoBase;
use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Database\Query\JoinClause;
use stdClass;

class ArquivoLiquidacaoRestosPagarDocumentoDiverso extends ArquivoBase
{
    protected $sNomeArquivo = 'LiquidacaoRestosPagarDocumentoDiverso';

    public function testa($var){
        echo "<pre>";
        print_r($var);
        echo "</pre>";
    }

    public function voltaLiquidacaoes(){
        $inst = $this->instit;
        $di = $this->dtDataInicial;
        $df = $this->dtDataFinal;
        $ano = $this->iAnoUsu;
        
        $sql = pg_query("SELECT empempenho.e60_numemp::integer as e60_numemp, e60_resumo, e60_destin, e60_codemp, e60_emiss, e60_numcgm, z01_nome, z01_cgccpf, z01_munic, e60_vlremp, e60_vlranu, e60_vlrliq, e63_codhist, e40_descr, e60_vlrpag, e60_anousu, e60_coddot, o58_coddot, o58_orgao, o40_orgao, o40_descr, o58_unidade, o41_descr, o15_codigo, o15_descr, fc_estruturaldotacao(e60_anousu,e60_coddot) as dl_estrutural, e60_codcom, pc50_descr, sum(c70_valor) as c70_valor, c70_data, c70_codlan, c53_tipo, c53_descr, (select o56_elemento||'-'||o56_descr from orcelemento where o56_codele = c67_codele and o56_anousu = c70_anousu) as desdobramento, c72_complem, z01_incest, z01_cgccpf, c66_codnota, e91_numemp from empempenho inner join conlancamemp on c75_numemp = empempenho.e60_numemp inner join conlancam on c70_codlan = c75_codlan left join conlancamnota on c66_codlan = c70_codlan inner join conlancamdoc on c71_codlan = c70_codlan inner join conhistdoc on c53_coddoc = c71_coddoc inner join cgm on cgm.z01_numcgm = empempenho.e60_numcgm inner join db_config on db_config.codigo = empempenho.e60_instit inner join orcdotacao on orcdotacao.o58_anousu = empempenho.e60_anousu and orcdotacao.o58_coddot = empempenho.e60_coddot and orcdotacao.o58_instit = empempenho.e60_instit inner join emptipo on emptipo.e41_codtipo = empempenho.e60_codtipo inner join db_config as a on a.codigo = orcdotacao.o58_instit INNER JOIN origemcomplementorecurso ON origemcomplementorecurso.o206_numero = empempenho.e60_numemp AND origemcomplementorecurso.o206_origem = 1 INNER JOIN orctiporec ON orctiporec.o15_codigo = origemcomplementorecurso.o206_recurso inner join orcfuncao on orcfuncao.o52_funcao = orcdotacao.o58_funcao inner join orcsubfuncao on orcsubfuncao.o53_subfuncao = orcdotacao.o58_subfuncao inner join orcprograma on orcprograma.o54_anousu = orcdotacao.o58_anousu and orcprograma.o54_programa = orcdotacao.o58_programa inner join orcelemento on orcelemento.o56_codele = orcdotacao.o58_codele and orcdotacao.o58_anousu = orcelemento.o56_anousu inner join orcprojativ on orcprojativ.o55_anousu = orcdotacao.o58_anousu and orcprojativ.o55_projativ = orcdotacao.o58_projativ inner join orcorgao on orcorgao.o40_anousu = orcdotacao.o58_anousu and orcorgao.o40_orgao = orcdotacao.o58_orgao inner join orcunidade on orcunidade.o41_anousu = orcdotacao.o58_anousu and orcunidade.o41_orgao = orcdotacao.o58_orgao and orcunidade.o41_unidade = orcdotacao.o58_unidade left join empemphist on empemphist.e63_numemp = empempenho.e60_numemp left join emphist on emphist.e40_codhist = empemphist.e63_codhist inner join pctipocompra on pctipocompra.pc50_codcom = empempenho.e60_codcom left join empresto on e60_numemp = e91_numemp and e60_anousu = e91_anousu left join conlancamcompl on c72_codlan = c70_codlan inner join conlancamele on c67_codlan = c70_codlan where c53_tipo in (20,21) and e60_anousu < {$ano} and c70_data between '{$di}' and '{$df}' and 1=1 and e60_instit in ({$inst}) group by e60_numemp, e60_resumo, e60_destin, e60_codemp, e60_emiss, e60_numcgm, z01_nome, z01_cgccpf, z01_munic, e60_vlremp, e60_vlranu, e60_vlrliq, e63_codhist, e40_descr, e60_vlrpag, e60_anousu, e60_coddot, o58_coddot, o58_orgao, o40_orgao, o40_descr, o58_unidade, o41_descr, o15_codigo, o15_descr, e60_codcom, pc50_descr, c70_data, c70_codlan, c53_tipo, c53_descr, desdobramento, c72_complem, z01_incest, z01_cgccpf, c66_codnota, e91_numemp order by e60_numemp, c70_codlan");
        $resultado = array();

        while ($linha = pg_fetch_object($sql)) {
            $resultado[] = $linha;
        }
        
        return $resultado;
    }

    public function buscaCodord2($codlan){
        $di = $this->dtDataInicial;
        $df = $this->dtDataFinal;

        $sql = pg_query("SELECT c70_codlan, c70_data, c70_valor, c71_coddoc, c53_descr, c80_codord, c75_numemp, c76_numcgm, z01_nome, e69_numero, e69_codnota, c72_complem, c73_coddot, c74_codrec, c70_anousu, c53_tipo, c67_codele from conlancam inner join conlancamdoc on c71_codlan = c70_codlan inner join conhistdoc on c71_coddoc = c53_coddoc left join conlancamord on c70_codlan = c80_codlan left join conlancamemp on c75_codlan = c70_codlan left join conlancamcgm on c70_codlan = c76_codlan left join cgm on z01_numcgm = c76_numcgm left join conlancamnota on c70_codlan = c66_codlan left join empnota on c66_codnota = e69_codnota left join conlancamcompl on c70_codlan = c72_codlan left join conlancamdot on c73_codlan = c70_codlan and c73_anousu = c70_anousu left join conlancamele on c67_codlan = c70_codlan left join conlancamrec on c74_codlan = c70_codlan and c74_anousu = c70_anousu where c70_codlan = {$codlan}");
        $resultado = pg_fetch_all($sql);
    
        return $resultado[0]["c80_codord"];
    }

    public function buscaDadosOrdem($codord){
        $sql = pg_query("SELECT * FROM pagordem WHERE e50_codord = {$codord}");
        $resultado = pg_fetch_all($sql);
        return $resultado[0];
    }

    public function buscaDadosEmpNota($codnota, $seqempenho){
        $sql = pg_query("SELECT * FROM empnota WHERE e69_numemp = {$seqempenho} AND e69_codnota = {$codnota}");
        $resultado = pg_fetch_all($sql);
        return $resultado[0];
    }

    public function validaTipo($codnota){
        $sql = pg_query("SELECT e177_codigo, e177_descr FROM sigfistipodocliquidacao INNER JOIN empnotasigfistipodocliquidacao ON e178_sigfistipodocliquidacao = e177_sequencial WHERE e178_empnota = {$codnota}");
        $resultado = pg_fetch_all($sql);
        return ($resultado[0]["e177_codigo"] == 3) ? true : false;
    }

    public function voltaValor($seqempenho){
        $di = $this->dtDataInicial;
        $df = $this->dtDataFinal;
        $sql = pg_query("SELECT c70_codlan, c70_data, c53_descr, case when c127_conlancam is not null then 'Sim' else 'Não' end as dl_Lançamento_Retenção, c70_valor, c82_reduz, c60_descr, c72_complem, e69_numero as dl_Nota_Fiscal, e50_codord, e50_data, nomeinstabrev as dl_Ente from conlancamemp inner join conlancam on c70_codlan = c75_codlan inner join empempenho on c75_numemp = e60_numemp inner join conlancamordem on conlancamordem.c03_codlan = conlancam.c70_codlan left outer join conlancampag on c82_codlan = c70_codlan inner join conlancamdoc on c71_codlan = c70_codlan inner join conhistdoc on c53_coddoc = c71_coddoc left join conlancamcompl on c72_codlan =c70_codlan left join conlancamnota on c66_codlan =c70_codlan left join conlancamord on c80_codlan =c70_codlan left join empnota on c66_codnota = e69_codnota left join conplanoreduz on c61_reduz = conlancampag.c82_reduz and c61_anousu=c70_anousu left join conplano on c60_codcon = conplanoreduz.c61_codcon and c60_anousu=c61_anousu left join pagordem on e50_codord = c80_codord inner join conlancaminstit on c02_codlan = c70_codlan inner join db_config on c02_instit = db_config.codigo left join conlancamretencao on c127_conlancam = c70_codlan where c75_numemp = {$seqempenho} AND c70_data between '{$di}' AND '{$df}' AND c71_coddoc = 33 order by c75_data, c03_ordem, c75_codlan");
        $resultado = pg_fetch_all($sql);
        return ($resultado[0]["c70_valor"]) ? $resultado[0]["c70_valor"] : 0;
    }

    public function tabelaSigfis($tipo){
        switch ($tipo) {
            case '4':
                return 1;            
            case '5':
                return 2;
        }
    }


    public function gerarDados()
    {   
        $anoexercicio = db_getsession("DB_anousu");

        $campos = [
            'e60_numemp',
            'e60_codemp',
            'e60_anousu',
            'o58_orgao',
            'o58_unidade',
            'e69_codnota',
            'e69_anousu',
            'e60_vlrliq',
            'e50_obs',
            'e50_codord',
            'e69_anousu',
            'e69_numero',
            'e69_serienota',
            'e69_dtnota',
            'e69_dtrecebe',
            'e178_sigfistipodocliquidacao',
            'z01_nome',
            'z01_cgccpf',
            'e50_data',
            'e178_sequencial',
        ];
        $empenhosx = DB::table('empresto')
            ->select($campos)
            ->join('empempenho', 'e60_numemp', 'e91_numemp')
            ->join('empnota', 'e69_numemp', 'e60_numemp')
            ->join('empnotaele', 'e70_codnota', 'e69_codnota')
            ->join('pagordem', 'e50_numemp', 'e60_numemp')
            ->join('pagordemnota', function (JoinClause $join) {
                $join->on('e71_codnota', 'e69_codnota')
                    ->on('e71_codord', 'e50_codord');
            })
            ->join('empnotasigfistipodocliquidacao', 'e178_empnota', 'e69_codnota')
            ->join('orcdotacao', function (JoinClause $join) {
                $join->on('o58_anousu', 'e60_anousu')
                    ->on('o58_coddot', 'e60_coddot');
            })
            ->join('cgm', 'z01_numcgm', 'e60_numcgm')
            ->where('e60_instit', $this->instit)
            ->whereIn('e178_sigfistipodocliquidacao', [4, 5])
            ->whereBetween('e69_dtinclusao', [$this->dtDataInicial, $this->dtDataFinal])
            ->where('e60_vlrliq', '!=', 0)
            ->orderBy('e60_numemp')
            ->get();
            //$sql_with_bindings = str_replace_array('?', $empenhos->getBindings(), $empenhos->toSql());
            //$sql_with_bindings = str_replace("\"", "", $sql_with_bindings);
            //var_dump($sql_with_bindings); die("confere");
            $empenhos = $this->voltaLiquidacaoes();
        if(count($empenhos) == 0){
            throw new \Exception('Não foi encontrado nenhum empenho de Liquidação de Empenho - Documento Diverso.');
        }

        $obj = new stdClass();
        $obj->LiquidacoesRestosPagarDocumentosDiversos = [];
        $xi = 1;

        //echo "<pre>";
        //print_r($empenhos);
        //echo "</pre>";
        //die("Confere");

        $guardaempenho = array();
        foreach ($empenhos as $empenho) {
            //if($empenho->e60_codemp != 1811){continue;}
            if($empenho->e60_anousu == $anoexercicio){continue;}
            //$this->testa($empenho);
            $vt = $this->validaTipo($empenho->c66_codnota);            
            if(!$vt){continue;}

            $codord = $this->buscaCodord2($empenho->c70_codlan);
            $dadosordem = $this->buscaDadosOrdem($codord);
            $dadosempnota = $this->buscaDadosEmpNota($empenho->c66_codnota, $empenho->e60_numemp);

            $chave = $empenho->e60_codemp . $empenho->c66_codnota;
            if(in_array($chave, $guardaempenho)){
                continue;
            }
            array_push($guardaempenho, $chave);
            
            $atestadores = DB::table('empnotaatestador')
                ->select(['z01_numcgm', 'z01_nome', 'z01_cgccpf'])
                ->join('cgm', 'z01_numcgm', 'e169_numcgm')
                ->where('e169_empnota', $empenho->c66_codnota)
                ->limit(1)
                ->get()
                ->map(function ($atestador) {
                    $cpf = $atestador->z01_cgccpf;

                    if(strlen($cpf) > 11){
                        $cpf = "81885520700";
                    }
                    return (object)[
                        'Identificador' => $atestador->z01_numcgm,
                        'Nome' => utf8_encode(trim($atestador->z01_nome)),
                        'CPF' => $cpf, //$atestador->z01_cgccpf,
                    ];
                })->toArray();


            if(empty($atestadores)){
                $atestadores = new stdClass();
                $atestadores->Identificador = NULL;
                $atestadores->Nome = NULL;
                $atestadores->CPF = NULL;                
                //$tipo = $empenho->e178_sigfistipodocliquidacao == 4 ? 1 : 2;
                $tipo = 1; //$this->tabelaSigfis($empenho->e178_sigfistipodocliquidacao);
                $natureza = strlen($empenho->z01_cgccpf) == 11 ? 2 : 1;

                $valorRPP = round($empenho->e60_vlrliq - $empenho->e60_vlrpag, 2);
                //$novovalor = $this->voltaValor($empenho->e60_numemp);
                $novovalor = $empenho->c70_valor;
                /*if($xi == 12){
                    $novovalor = $this->voltaValor($empenho->e60_numemp);
                    var_dump($novovalor);
                }*/

                if(!$novovalor || $novovalor == null){
                    $novovalor = "0";
                }
                if($novovalor == 0){continue;}
                //$this->testa($dadosordem); die("Dados 1");

                $data = (object)[
                    //'TipoCru' => $empenho->e178_sigfistipodocliquidacao,
                    //'SeqCru' => $empenho->e178_sequencial,
                    'Identificador' => $xi,//$empenho->e69_codnota,
                    'CodigoUnidadeGestora' => $this->sCodigoTribunal,
                    'NumeroEmpenho' => $empenho->e60_codemp,
                    'AnoEmpenho' => $empenho->e60_anousu,
                    'Competencia' => $this->competencia,
                    'AnoLiquidacaoRestosPagar' => substr($dadosordem["e50_data"], 0, 4),
                    'NumeroLiquidacaoRestosPagar' => $dadosordem["e50_codord"],
                    'Tipo' => $tipo,
                    'NumeroDocumento' => $dadosempnota["e69_numero"],
                    'DataEmissao' => $dadosempnota["e69_dtnota"],
                    //'Valor' => $empenho->e60_vlrliq,
                    //'Valor' => $valorRPP,
                    'Valor' => $novovalor,
                    'CpfCnpjNifEmitente' => $empenho->z01_cgccpf,
                    'NaturezaEmitente' => $natureza,
                    'Nome' => utf8_encode($empenho->z01_nome),
                    'DataAtestacao' => $dadosempnota["e69_dtrecebe"],
                    'CodigoOrgao' => $empenho->o58_orgao,
                    'CodigoUnidadeOrcamentaria' => $empenho->o58_unidade,
                    //'Atestadores' => (object)['Atestador' => $atestadores],
                    'Atestadores' => NULL,
                ];
            }else{
                //$tipo = $empenho->e178_sigfistipodocliquidacao == 4 ? 1 : 2;
                $tipo = 1; //$this->tabelaSigfis($empenho->e178_sigfistipodocliquidacao);
                $natureza = strlen($empenho->z01_cgccpf) == 11 ? 2 : 1;
                $novovalor = $this->voltaValor($empenho->e60_numemp);
                if(!$novovalor || $novovalor == null){
                    $novovalor = "0";
                }
                if($novovalor == 0){continue;}
                

                $data = (object)[
                    //'TipoCru' => $empenho->e178_sigfistipodocliquidacao,
                    //'SeqCru' => $empenho->e178_sequencial,
                    'Identificador' => $xi,//$empenho->e69_codnota,
                    'CodigoUnidadeGestora' => $this->sCodigoTribunal,
                    'NumeroEmpenho' => $empenho->e60_codemp,
                    'AnoEmpenho' => $empenho->e60_anousu,
                    'Competencia' => $this->competencia,
                    'AnoLiquidacaoRestosPagar' => substr($dadosordem["e50_data"], 0, 4),
                    'NumeroLiquidacaoRestosPagar' => $dadosordem["e50_codord"],
                    'Tipo' => $tipo,
                    'NumeroDocumento' => $dadosempnota["e69_numero"],
                    'DataEmissao' => $dadosempnota["e69_dtnota"],
                    //'Valor' => $empenho->e60_vlrliq,
                    //'Valor' => $valorRPP,
                    'Valor' => $novovalor,
                    'CpfCnpjNifEmitente' => $empenho->z01_cgccpf,
                    'NaturezaEmitente' => $natureza,
                    'Nome' => utf8_encode($empenho->z01_nome),
                    'DataAtestacao' => $dadosempnota["e69_dtrecebe"],
                    'CodigoOrgao' => $empenho->o58_orgao,
                    'CodigoUnidadeOrcamentaria' => $empenho->o58_unidade,
                    'Atestadores' => (object)['Atestador' => $atestadores],                
                ];
            }
            
            
            /*
            $tipo = $empenho->e178_sigfistipodocliquidacao == 4 ? 1 : 2;
            $natureza = strlen($empenho->z01_cgccpf) == 11 ? 2 : 1;

            $data = (object)[
                'Identificador' => $empenho->e69_codnota,
                'CodigoUnidadeGestora' => $this->sCodigoTribunal,
                'NumeroEmpenho' => $empenho->e60_codemp,
                'AnoEmpenho' => $empenho->e60_anousu,
                'Competencia' => $this->competencia,
                'AnoLiquidacaoRestosPagar' => substr($empenho->e50_data, 0, 4),
                'NumeroLiquidacaoRestosPagar' => $empenho->e69_codnota,
                'Tipo' => $tipo,
                'NumeroDocumento' => $empenho->e69_numero,
                'DataEmissao' => $empenho->e69_dtnota,
                'Valor' => $empenho->e60_vlrliq,
                'CpfCnpjNifEmitente' => $empenho->z01_cgccpf,
                'NaturezaEmitente' => $natureza,
                'Nome' => utf8_encode($empenho->z01_nome),
                'DataAtestacao' => $empenho->e69_dtrecebe,
                'CodigoOrgao' => $empenho->o58_orgao,
                'CodigoUnidadeOrcamentaria' => $empenho->o58_unidade,
                //'Atestadores' => (object)['Atestador' => $atestadores],
                'Atestadores' => NULL,
            ];
            */
            $xi++;
            
            /*if($data->NumeroDocumento != "S/N"){
                //$this->testa($data);    
                echo "UPDATE empnotasigfistipodocliquidacao SET e178_sigfistipodocliquidacao = 2 WHERE e178_sequencial = {$data->SeqCru}"; echo "<br>";
            }*/

/*
if(
$data->Identificador == 3 || $data->Identificador == 4 || $data->Identificador == 5 || $data->Identificador == 6 || $data->Identificador == 7 || $data->Identificador == 8 || $data->Identificador == 9 || $data->Identificador == 10 || $data->Identificador == 11 || $data->Identificador == 12 || $data->Identificador == 13 || $data->Identificador == 14 || $data->Identificador == 15 || $data->Identificador == 16 || $data->Identificador == 17 || $data->Identificador == 18 || $data->Identificador == 19 || $data->Identificador == 20 || $data->Identificador == 21 || $data->Identificador == 22 || $data->Identificador == 23 || $data->Identificador == 24 || $data->Identificador == 25 || $data->Identificador == 26 || $data->Identificador == 27 || $data->Identificador == 28 || $data->Identificador == 29 || $data->Identificador == 30 || $data->Identificador == 31 || $data->Identificador == 32 || $data->Identificador == 33 || $data->Identificador == 34 || $data->Identificador == 35 || $data->Identificador == 36 || $data->Identificador == 37 || $data->Identificador == 38 || $data->Identificador == 39 || $data->Identificador == 40 || $data->Identificador == 41 || $data->Identificador == 42 || $data->Identificador == 43 || $data->Identificador == 44 || $data->Identificador == 45 || $data->Identificador == 46 || $data->Identificador == 47 || $data->Identificador == 48 || $data->Identificador == 49
){
    //echo $data->SeqCru . " - " . $data->TipoCru; echo "<br>";
    echo "UPDATE empnotasigfistipodocliquidacao SET e178_sigfistipodocliquidacao = 2 WHERE e178_sequencial = {$data->SeqCru}"; echo "<br>";
}
 */
            
            $obj->LiquidacoesRestosPagarDocumentosDiversos[] = (object) [
                'LiquidacaoRestosPagarDocumentoDiverso' => $data
            ];
        }

           
        //die("Maoeoe");
        $this->aDados = $obj;
    }
}

<?php

namespace ECidade\Financeiro\Contabilidade\Exportacao\Sigfis\Arquivos\v2024;

use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Database\Query\JoinClause;
use ECidade\Financeiro\Contabilidade\Exportacao\Sigfis\Common\Helper;
use stdClass;

class ArquivoAnulacaoPagamentoDeEmpenho extends ArquivoBase
{
    protected $sNomeArquivo  = 'AnulacaoPagamentoDeEmpenho';

    public function testa($var){
        echo "<pre>";
        print_r($var);
        echo "</pre>";
    }
    
    //==================================================================================================================
    //Busca só os empenhos
    public function retornaEmpenhosPagos(){
        $inst = $this->instit;
        $di = $this->dtDataInicial;
        $df = $this->dtDataFinal;
        $ano = $this->iAnoUsu;

        $sql = pg_query("SELECT DISTINCT e60_numemp, e60_codemp FROM coremp inner join empempenho on e60_numemp = k12_empen and e60_instit = {$inst} inner join orcdotacao on e60_coddot = o58_coddot and e60_anousu = o58_anousu inner join pagordem on e50_codord = k12_codord left join pagordemconta on e50_codord = e49_codord inner join corrente on corrente.k12_id = coremp.k12_id and corrente.k12_data = coremp.k12_data and corrente.k12_autent = coremp.k12_autent inner join cgm on cgm.z01_numcgm = e60_numcgm left join cgm cgmordem on cgmordem.z01_numcgm = e49_numcgm inner join saltes on saltes.k13_conta = corrente.k12_conta left join corgrupocorrente on k105_id = corrente.k12_id and k105_data = corrente.k12_data and k105_autent = corrente.k12_autent left join corgrupotipo on k106_sequencial = k105_corgrupotipo where coremp.k12_data between '{$di}' and '{$df}' AND e60_anousu = {$ano} AND k12_valor < 0 order by e60_numemp");

        $resultado = array();

        while ($linha = pg_fetch_object($sql)) {
            $resultado[] = $linha;
        }
        return $resultado;
    }

    //Depois joga o empenho aqui
    public function retornaDadosDoEmpenhoPago($seqempenho){
        $inst = $this->instit;
        $di = $this->dtDataInicial;
        $df = $this->dtDataFinal;
        $ano = $this->iAnoUsu;

        $sql = pg_query("SELECT coremp.k12_empen, e60_numemp, e60_codemp, case when e49_numcgm is null then e60_numcgm else e49_numcgm end as e60_numcgm, k12_codord as e50_codord, case when e49_numcgm is null then cgm.z01_nome else cgmordem.z01_nome end as z01_nome, k12_valor, k12_cheque, e60_anousu, coremp.k12_autent, coremp.k12_data, k13_conta, k13_descr, k106_sequencial, e60_coddot from coremp inner join empempenho on e60_numemp = k12_empen and e60_instit = {$inst} inner join orcdotacao on e60_coddot = o58_coddot and e60_anousu = o58_anousu inner join pagordem on e50_codord = k12_codord left join pagordemconta on e50_codord = e49_codord inner join corrente on corrente.k12_id = coremp.k12_id and corrente.k12_data = coremp.k12_data and corrente.k12_autent = coremp.k12_autent inner join cgm on cgm.z01_numcgm = e60_numcgm left join cgm cgmordem on cgmordem.z01_numcgm = e49_numcgm inner join saltes on saltes.k13_conta = corrente.k12_conta left join corgrupocorrente on k105_id = corrente.k12_id and k105_data = corrente.k12_data and k105_autent = corrente.k12_autent left join corgrupotipo on k106_sequencial = k105_corgrupotipo where e60_numemp = {$seqempenho} and coremp.k12_data between '{$di}' and '{$df}' AND e60_anousu = {$ano} AND k12_valor < 0 order by e60_codemp, k12_data, k13_conta");

        $resultado = array();

        while($linha = pg_fetch_object($sql)){
            $resultado[] = $linha;
        }
        return $resultado;
    }    

    public function retornaNF($codord){
        $sql = pg_query("SELECT e69_numero, e69_codnota, e69_anousu from pagordemnota inner join pagordem on pagordem.e50_codord = pagordemnota.e71_codord inner join empnota on empnota.e69_codnota = pagordemnota.e71_codnota inner join empempenho on empempenho.e60_numemp = pagordem.e50_numemp inner join db_usuarios on db_usuarios.id_usuario = empnota.e69_id_usuario inner join empempenho as a on a.e60_numemp = empnota.e69_numemp where pagordemnota.e71_codord = {$codord}");
        $resultado = pg_fetch_all($sql);
        return $resultado[0];
    }

    public function retornaDadosBancarios($codconta){
        $sql = pg_query("SELECT distinct c63_banco, c63_agencia, c63_conta from saltes join conplanoreduz on conplanoreduz.c61_reduz = saltes.k13_reduz and c61_anousu= 2025 join conplanoexe on conplanoexe.c62_reduz = conplanoreduz.c61_reduz and c61_anousu=c62_anousu join conplano on conplanoreduz.c61_codcon = conplano.c60_codcon and c61_anousu=c60_anousu left join conplanoconta on conplanoconta.c63_codcon = conplanoreduz.c61_codcon and conplanoconta.c63_anousu = conplanoreduz.c61_anousu and conplanoconta.c63_reduz = conplanoreduz.c61_reduz left join empagetipo on empagetipo.e83_conta = saltes.k13_conta join orctiporec on o15_codigo = c61_codigo join fonterecurso on orctiporec_id = o15_codigo and exercicio = c61_anousu WHERE k13_conta = {$codconta}");
        $resultado = pg_fetch_all($sql);
        return $resultado[0];
    }

    public function retornaDadosDotacao($ano, $coddot){
        $inst = $this->instit;        
        $sql = pg_query("SELECT o58_orgao, o58_unidade FROM orcdotacao WHERE o58_anousu = {$ano} AND o58_coddot = {$coddot} AND o58_instit = {$inst}");
        $resultado = pg_fetch_all($sql);
        return $resultado[0];
    }

    public function buscaCPFordenador($cgm){
        $sql = pg_query("SELECT z01_cgccpf FROM cgm WHERE z01_numcgm = {$cgm}");
        $resultado = pg_fetch_all($sql);
        return $resultado[0]["z01_cgccpf"];
    }

    public function buscaJustificativa($seqempenho, $codnota){
        $inst = $this->instit;
        $di = $this->dtDataInicial;
        $df = $this->dtDataFinal;
        $ano = $this->iAnoUsu;

        $sql = pg_query("SELECT o58_orgao, o58_unidade, e60_numemp, e60_codemp, e60_anousu, e69_codnota, e69_anousu, e69_numero, c70_codlan, c70_data, c70_valor, c72_complem, z01_nome, z01_cgccpf as cpf from empempenho inner join empnota on e69_numemp = e60_numemp inner join orcdotacao on o58_anousu = e60_anousu and o58_coddot = e60_coddot inner join pagordemnota on e71_codnota = e69_codnota inner join pagordem on e50_codord = e71_codord inner join conlancamord on c80_codord = e71_codord inner join conlancam on c70_codlan = c80_codlan inner join conlancamdoc on c71_codlan = c70_codlan inner join conlancamcompl on c72_codlan = c70_codlan inner join lancamentoscontabeislog as log on codlan = c70_codlan and tipo_movimento = 1 inner join db_usuacgm on db_usuacgm.id_usuario = log.id_usuario inner join cgm as cgmusu on cgmusu.z01_numcgm = cgmlogin where c71_coddoc in (6) and e60_instit = {$inst} and c70_data between '{$di}' and '{$df}' AND e60_numemp = {$seqempenho} AND e69_codnota = {$codnota} order by e60_numemp asc");
        $resultado = pg_fetch_all($sql);
        return $resultado[0]["c72_complem"];

    }



public function todosOsEmpenhos(){
    $inst = $this->instit;
    $di = $this->dtDataInicial;
    $df = $this->dtDataFinal;
    $ano = $this->iAnoUsu;

    $sql = pg_query("SELECT empempenho.e60_numemp::integer as e60_numemp, e60_resumo, e60_destin, e60_codemp, e60_emiss, e60_numcgm, z01_nome, z01_cgccpf, z01_munic, e60_vlremp, e60_vlranu, e60_vlrliq, e63_codhist, e40_descr, e60_vlrpag, e60_anousu, e60_coddot, o58_coddot, o58_orgao, o40_orgao, o40_descr, o58_unidade, o41_descr, o15_codigo, o15_descr, fc_estruturaldotacao(e60_anousu,e60_coddot) as dl_estrutural, e60_codcom, pc50_descr, sum(c70_valor) as c70_valor, c70_data, c70_codlan, c53_tipo, c53_descr, (select o56_elemento||'-'||o56_descr from orcelemento where o56_codele = c67_codele and o56_anousu = c70_anousu) as desdobramento, c72_complem, z01_incest, z01_cgccpf, c66_codnota, e91_numemp, c53_coddoc from empempenho inner join conlancamemp on c75_numemp = empempenho.e60_numemp inner join conlancam on c70_codlan = c75_codlan left join conlancamnota on c66_codlan = c70_codlan inner join conlancamdoc on c71_codlan = c70_codlan inner join conhistdoc on c53_coddoc = c71_coddoc inner join cgm on cgm.z01_numcgm = empempenho.e60_numcgm inner join db_config on db_config.codigo = empempenho.e60_instit inner join orcdotacao on orcdotacao.o58_anousu = empempenho.e60_anousu and orcdotacao.o58_coddot = empempenho.e60_coddot and orcdotacao.o58_instit = empempenho.e60_instit inner join emptipo on emptipo.e41_codtipo = empempenho.e60_codtipo inner join db_config as a on a.codigo = orcdotacao.o58_instit INNER JOIN origemcomplementorecurso ON origemcomplementorecurso.o206_numero = empempenho.e60_numemp AND origemcomplementorecurso.o206_origem = 1 INNER JOIN orctiporec ON orctiporec.o15_codigo = origemcomplementorecurso.o206_recurso inner join orcfuncao on orcfuncao.o52_funcao = orcdotacao.o58_funcao inner join orcsubfuncao on orcsubfuncao.o53_subfuncao = orcdotacao.o58_subfuncao inner join orcprograma on orcprograma.o54_anousu = orcdotacao.o58_anousu and orcprograma.o54_programa = orcdotacao.o58_programa inner join orcelemento on orcelemento.o56_codele = orcdotacao.o58_codele and orcdotacao.o58_anousu = orcelemento.o56_anousu inner join orcprojativ on orcprojativ.o55_anousu = orcdotacao.o58_anousu and orcprojativ.o55_projativ = orcdotacao.o58_projativ inner join orcorgao on orcorgao.o40_anousu = orcdotacao.o58_anousu and orcorgao.o40_orgao = orcdotacao.o58_orgao inner join orcunidade on orcunidade.o41_anousu = orcdotacao.o58_anousu and orcunidade.o41_orgao = orcdotacao.o58_orgao and orcunidade.o41_unidade = orcdotacao.o58_unidade left join empemphist on empemphist.e63_numemp = empempenho.e60_numemp left join emphist on emphist.e40_codhist = empemphist.e63_codhist inner join pctipocompra on pctipocompra.pc50_codcom = empempenho.e60_codcom left join empresto on e60_numemp = e91_numemp and e60_anousu = e91_anousu left join conlancamcompl on c72_codlan = c70_codlan inner join conlancamele on c67_codlan = c70_codlan where c53_tipo in (31) and e60_numemp not in (select e91_numemp from empresto where e91_anousu = {$ano}) and c53_coddoc not in(31,32,33,34,35,36,37,38,1007) and c70_data between '{$di}' and '{$df}' and 1=1 and e60_instit in ({$inst}) AND c53_coddoc in(6) group by e60_numemp, e60_resumo, e60_destin, e60_codemp, e60_emiss, e60_numcgm, z01_nome, z01_cgccpf, z01_munic, e60_vlremp, e60_vlranu, e60_vlrliq, e63_codhist, e40_descr, e60_vlrpag, e60_anousu, e60_coddot, o58_coddot, o58_orgao, o40_orgao, o40_descr, o58_unidade, o41_descr, o15_codigo, o15_descr, e60_codcom, pc50_descr, c70_data, c70_codlan, c53_tipo, c53_descr, desdobramento, c72_complem, z01_incest, z01_cgccpf, c66_codnota, e91_numemp, c53_coddoc order by e60_numemp, c70_codlan");
    
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

public function todosNotaFiscal($seqempenho, $codlan, $data, $valor){
    $inst = $this->instit;
    $sql = pg_query("SELECT c71_coddoc, o58_orgao, o58_unidade, e60_numemp, e60_codemp, e60_anousu, e69_codnota, e69_anousu, e69_numero, c70_codlan, c70_data, c70_valor, c72_complem, z01_nome, z01_cgccpf as cpf from empempenho inner join empnota on e69_numemp = e60_numemp inner join orcdotacao on o58_anousu = e60_anousu and o58_coddot = e60_coddot inner join pagordemnota on e71_codnota = e69_codnota inner join pagordem on e50_codord = e71_codord inner join conlancamord on c80_codord = e71_codord inner join conlancam on c70_codlan = c80_codlan inner join conlancamdoc on c71_codlan = c70_codlan inner join conlancamcompl on c72_codlan = c70_codlan inner join lancamentoscontabeislog as log on codlan = c70_codlan and tipo_movimento = 1 inner join db_usuacgm on db_usuacgm.id_usuario = log.id_usuario inner join cgm as cgmusu on cgmusu.z01_numcgm = cgmlogin where e60_numemp = {$seqempenho} AND e60_instit = {$inst} and c70_data = '{$data}' AND c70_valor = {$valor} order by e60_numemp asc");
    $resultado = pg_fetch_all($sql);
    return $resultado[0];
}






//=================================================================================================================================    
    

    public function gerarDados(){
        $campos = [
            'o58_orgao',
            'o58_unidade',
            'e60_numemp',
            'e60_codemp',
            'e60_anousu',
            'e69_codnota',
            'e69_anousu',
            'e69_numero',
            'c70_codlan',
            'c70_data',
            'c70_valor',
            'c72_complem',
            'z01_nome',
            'z01_cgccpf as cpf'
        ];
        //$empenhos = [];
        /*
        $empenhos = DB::table('empempenho')
            ->select($campos)
            ->join('empnota', 'e69_numemp', 'e60_numemp')
            ->join('orcdotacao', function (JoinClause $join) {
                $join->on('o58_anousu', 'e60_anousu')
                    ->on('o58_coddot', 'e60_coddot');
            })
            ->join('pagordemnota', 'e71_codnota', 'e69_codnota')
            ->join('pagordem', 'e50_codord', 'e71_codord')
            ->join('conlancamord', 'c80_codord', 'e71_codord')
            ->join('conlancam', 'c70_codlan', 'c80_codlan')
            ->join('conlancamdoc', 'c71_codlan', 'c70_codlan')
            ->join('conlancamcompl', 'c72_codlan', 'c70_codlan')
            ->join('lancamentoscontabeislog AS log', function (JoinClause $join) {
                $join->on('codlan', 'c70_codlan')
                    ->where('tipo_movimento', 1);
            })
            ->join('db_usuacgm', 'db_usuacgm.id_usuario', 'log.id_usuario')
            ->join('cgm AS cgmusu', 'cgmusu.z01_numcgm', 'cgmlogin')
            ->whereIn('c71_coddoc', [6])
            ->where('e60_instit', $this->instit)
            ->whereBetween('c70_data', [$this->dtDataInicial, $this->dtDataFinal])
            ->orderBy('e60_numemp')
            ->get();
        
            //$sql_with_bindings = str_replace_array('?', $empenhos->getBindings(), $empenhos->toSql());
            //$sql_with_bindings = str_replace("\"", "", $sql_with_bindings);
            //var_dump($sql_with_bindings); die("confere");
            */

        $obj = new \stdClass();
        $obj->AnulacoesDePagamentosDeEmpenhos = [];

        $ugs = $this->pegaCgmOrdenador();
        $ugs = $this->buscaCPFordenador($ugs);

        //$empenhospagos = $this->retornaEmpenhosPagos();
        $empenhos = $this->todosOsEmpenhos();

        if(count($empenhos) == 0){
            throw new \Exception(sprintf(
                'Não foi encontrado nenhum registro para o arquivo da Remessa de %s',
                'Pagamento de Empenho - Anulação.'
            ));
        }
        //$this->testa($empenhospagos); die("Confere");

        $lista = array();
        /*foreach ($empenhospagos as $empenho){
            $dados = $this->retornaDadosDoEmpenhoPago($empenho->e60_numemp);            

            foreach ($dados as $dado){
                $item = new stdClass();
                $dadosnf = $this->retornaNF($dado->e50_codord);
                $numero_nf = $dadosnf["e69_numero"];
                $seq_nf = $dadosnf["e69_codnota"];
                $anonf = $dadosnf["e69_anousu"];

                $item->k12_empen = $dado->k12_empen;
                $item->e60_numemp = $dado->e60_numemp;
                $item->e60_codemp = $dado->e60_codemp;
                $item->e60_numcgm = $dado->e60_numcgm;
                $item->e50_codord = $dado->e50_codord;
                $item->z01_nome = $dado->z01_nome;
                $item->k12_valor = $dado->k12_valor;
                $item->k12_cheque = $dado->k12_cheque;
                $item->e60_anousu = $dado->e60_anousu;
                $item->k12_autent = $dado->k12_autent;
                $item->k12_data = $dado->k12_data;
                $item->k13_conta = $dado->k13_conta;
                $item->k13_descr = $dado->k13_descr;
                $item->k106_sequencial = $dado->k106_sequencial;
                $item->coddot = $dado->e60_coddot;
                $item->nf = $numero_nf;
                $item->seqnf = $seq_nf;
                $item->anonf = $anonf;
                

                $lista[] = $item;
            }
        }*/
        /*
        577785
        SELECT * FROM conlancamord INNER JOIN conlancam ON c80_codlan = c70_codlan WHERE c80_codord = 577785 AND c70_data = '2025-06-23' AND c70_valor = 9899.78;
        3945608
        3946836
        SELECT * FROM conlanacam WHERE c70_codlan in(3939937, 3945160, 3945608, 3946836);

        e50_obs PAGAMENTO PASEP COMPETENCIA JUNHO 202
        */

        $i = 1;
        $ix = 1;
        foreach ($empenhos as $empenho) {
            //if($empenho->e60_codemp != 53){continue;}
        //foreach ($lista as $empenho) {            
            $numeroAnulacao = $i++;

            $codord = $this->buscaCodord2($empenho->c70_codlan);
            //$dadosnf = $this->retornaNF($codord);
            //$numero_nf = $dadosnf["e69_numero"];
            //$seq_nf = $dadosnf["e69_codnota"];
            //$anonf = $dadosnf["e69_anousu"];
            $nfdados = $this->todosNotaFiscal($empenho->e60_numemp, $empenho->c70_codlan, $empenho->c70_data, $empenho->c70_valor);
            $notafiscal = $nfdados["e69_codnota"];
            $numeronota = $nfdados["e69_numero"];
            
            if(empty($numeronota)){
                $numeronota = "S/N";
            }
            $anonotafiscal = $nfdados["e69_anousu"];

            /*
            if($seq_nf == 490101){
                var_dump($empenho->c70_codlan); echo "<br>";
                var_dump($empenho->e60_numemp, $empenho->c70_codlan, $empenho->c70_data, $empenho->c70_valor);
                $this->testa($nfdados);
                die("Confere");
            }
            */



            $dadosdotacao = $this->retornaDadosDotacao($empenho->e60_anousu, $empenho->o58_coddot);
            $justificativa = $this->buscaJustificativa($empenho->e60_numemp, $notafiscal);
            

            $dadosAnulacaoPagamentoDeEmpenho = (object)[
                'Identificador' => $ix,
                'CodigoUnidadeGestora' => $this->sCodigoTribunal,
                'NumeroEmpenho' => $empenho->e60_codemp,
                'AnoEmpenho' =>  $empenho->e60_anousu,
                'Competencia' => $this->competencia,                
                'NumeroNotaPagamento' => $notafiscal,//$numeronota,
                'AnoNotaPagamento' => $anonotafiscal,
                'NumeroAnulacaoPagamento' => $numeroAnulacao,
                'Tipo' => 2,
                'DataAnulacao' => $empenho->c70_data,
                'CpfResponsavel' => $ugs,
                'Justificativa' => ($justificativa) ? $justificativa : "Lançamento", //Helper::convertAndLimit(utf8_decode($empenho->c72_complem), 4000),
                'Valor' => $empenho->c70_valor,
                'CodigoOrgao' =>  $dadosdotacao["o58_orgao"],
                'CodigoUnidadeOrcamentaria' =>  $dadosdotacao["o58_unidade"]
            ];

            $obj->AnulacoesDePagamentosDeEmpenhos[] = (object)[
                'AnulacaoPagamentoDeEmpenho' => $dadosAnulacaoPagamentoDeEmpenho
            ];
            $ix++;
        }

        $this->aDados =  $obj;
    }
}

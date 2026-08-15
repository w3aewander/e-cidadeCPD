<?php

namespace ECidade\Financeiro\Contabilidade\Exportacao\Sigfis\Arquivos\v2024;

use ECidade\Financeiro\Contabilidade\Exportacao\Sigfis\Xsd\XSDFactory;
use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Database\Query\JoinClause;
use stdClass;


class ArquivoLiquidacaoDeEmpenho extends ArquivoBase
{
    protected $sNomeArquivo  = 'LiquidacaoDeEmpenho';

    public function testa($var){
        echo "<pre>";
        print_r($var);
        echo "</pre>";
    }

    public function voltaRetencoes($codord){
        $di = $this->dtDataInicial;
        $df = $this->dtDataFinal;

        $sql = pg_query("SELECT distinct e48_cgm, tabrec.*, retencaotiporec.*, retencaoreceitas.*, e27_empagemov, e27_principal, retencaoreceitasadicionais.*, tiposerviconotafiscal.e18_descricao, retencaoreceitasprodutorrural.*, emptiposervicoobra.* from retencaoreceitas inner join retencaotiporec on retencaotiporec.e21_sequencial = retencaoreceitas.e23_retencaotiporec inner join retencaopagordem on retencaopagordem.e20_sequencial = retencaoreceitas.e23_retencaopagordem inner join tabrec on tabrec.k02_codigo = retencaotiporec.e21_receita inner join retencaotipocalc on retencaotipocalc.e32_sequencial = retencaotiporec.e21_retencaotipocalc inner join pagordem on pagordem.e50_codord = retencaopagordem.e20_pagordem inner join pagordemnota on pagordem.e50_codord = pagordemnota.e71_codord inner join empnota on pagordemnota.e71_codnota = empnota.e69_codnota inner join retencaoempagemov on e23_sequencial = e27_retencaoreceitas left join empagemovslips on e27_empagemov = k107_empagemov left join slipempagemovslips on k107_sequencial = k108_empagemovslips left join retencaoreceitasadicionais on e23_sequencial = e19_retencaoreceitas left join tiposerviconotafiscal on e19_tiposerviconotafiscal = e18_sequencial left join retencaotiporeccgm on e48_retencaotiporec = retencaotiporec.e21_sequencial left join retencaoreceitasprodutorrural on e23_sequencial = e158_retencaoreceitas left join emptiposervicoobra on empnota.e69_numemp = e154_numemp where e20_pagordem = {$codord} and e23_ativo = true and e71_anulado = false and e27_principal is true AND e23_dtcalculo between '{$di}' AND '{$df}' order by e21_sequencial");
        $resultado = pg_fetch_all($sql);
        return $resultado;
    }

    public function voltaLiquidacaoes(){
        $inst = $this->instit;
        $di = $this->dtDataInicial;
        $df = $this->dtDataFinal;
        $ano = $this->iAnoUsu;
        //c53_coddoc not in (31,32,33,34,35,36,37,38,1007)
        //c53_coddoc in (3, 23, 412)
        $sql = pg_query("SELECT empempenho.e60_numemp::integer as e60_numemp, e60_resumo, e60_destin, e60_codemp, e60_emiss, e60_numcgm, z01_nome, z01_cgccpf, z01_munic, e60_vlremp, e60_vlranu, e60_vlrliq, e63_codhist, e40_descr, e60_vlrpag, e60_anousu, e60_coddot, o58_coddot, o58_orgao, o40_orgao, o40_descr, o58_unidade, o41_descr, o15_codigo, o15_descr, fc_estruturaldotacao(e60_anousu,e60_coddot) as dl_estrutural, e60_codcom, pc50_descr, sum(c70_valor) as c70_valor, c70_data, c70_codlan, c53_tipo, c53_descr, (select o56_elemento||'-'||o56_descr from orcelemento where o56_codele = c67_codele and o56_anousu = c70_anousu) as desdobramento, c72_complem, z01_incest, z01_cgccpf, c66_codnota, e91_numemp from empempenho inner join conlancamemp on c75_numemp = empempenho.e60_numemp inner join conlancam on c70_codlan = c75_codlan left join conlancamnota on c66_codlan = c70_codlan inner join conlancamdoc on c71_codlan = c70_codlan inner join conhistdoc on c53_coddoc = c71_coddoc inner join cgm on cgm.z01_numcgm = empempenho.e60_numcgm inner join db_config on db_config.codigo = empempenho.e60_instit inner join orcdotacao on orcdotacao.o58_anousu = empempenho.e60_anousu and orcdotacao.o58_coddot = empempenho.e60_coddot and orcdotacao.o58_instit = empempenho.e60_instit inner join emptipo on emptipo.e41_codtipo = empempenho.e60_codtipo inner join db_config as a on a.codigo = orcdotacao.o58_instit INNER JOIN origemcomplementorecurso ON origemcomplementorecurso.o206_numero = empempenho.e60_numemp AND origemcomplementorecurso.o206_origem = 1 INNER JOIN orctiporec ON orctiporec.o15_codigo = origemcomplementorecurso.o206_recurso inner join orcfuncao on orcfuncao.o52_funcao = orcdotacao.o58_funcao inner join orcsubfuncao on orcsubfuncao.o53_subfuncao = orcdotacao.o58_subfuncao inner join orcprograma on orcprograma.o54_anousu = orcdotacao.o58_anousu and orcprograma.o54_programa = orcdotacao.o58_programa inner join orcelemento on orcelemento.o56_codele = orcdotacao.o58_codele and orcdotacao.o58_anousu = orcelemento.o56_anousu inner join orcprojativ on orcprojativ.o55_anousu = orcdotacao.o58_anousu and orcprojativ.o55_projativ = orcdotacao.o58_projativ inner join orcorgao on orcorgao.o40_anousu = orcdotacao.o58_anousu and orcorgao.o40_orgao = orcdotacao.o58_orgao inner join orcunidade on orcunidade.o41_anousu = orcdotacao.o58_anousu and orcunidade.o41_orgao = orcdotacao.o58_orgao and orcunidade.o41_unidade = orcdotacao.o58_unidade left join empemphist on empemphist.e63_numemp = empempenho.e60_numemp left join emphist on emphist.e40_codhist = empemphist.e63_codhist inner join pctipocompra on pctipocompra.pc50_codcom = empempenho.e60_codcom left join empresto on e60_numemp = e91_numemp and e60_anousu = e91_anousu left join conlancamcompl on c72_codlan = c70_codlan inner join conlancamele on c67_codlan = c70_codlan where c53_tipo in (20) and e60_numemp not in (select e91_numemp from empresto where e91_anousu = {$ano}) and c53_coddoc in (3, 23,204, 206, 412) and c70_data between '{$di}' and '{$df}' and 1=1 and e60_instit in ({$inst}) group by e60_numemp, e60_resumo, e60_destin, e60_codemp, e60_emiss, e60_numcgm, z01_nome, z01_cgccpf, z01_munic, e60_vlremp, e60_vlranu, e60_vlrliq, e63_codhist, e40_descr, e60_vlrpag, e60_anousu, e60_coddot, o58_coddot, o58_orgao, o40_orgao, o40_descr, o58_unidade, o41_descr, o15_codigo, o15_descr, e60_codcom, pc50_descr, c70_data, c70_codlan, c53_tipo, c53_descr, desdobramento, c72_complem, z01_incest, z01_cgccpf, c66_codnota, e91_numemp order by e60_numemp, c70_codlan");
        $resultado = array();

        while ($linha = pg_fetch_object($sql)) {
            $resultado[] = $linha;
        }
        
        return $resultado;
    }

    public function buscaNotaDoEmpenhao2($seqempenho){
    $di = $this->dtDataInicial;
    $df = $this->dtDataFinal;
    $sql = pg_query("SELECT e69_codnota, c70_codlan, c70_data, e60_coddot, c53_coddoc, c53_descr, case when c127_conlancam is not null then 'Sim' else 'Não' end as dl_Lançamento_Retenção, c70_valor, c82_reduz, c60_descr, c72_complem, e69_numero, e50_codord, e50_data, nomeinstabrev as dl_Ente from conlancamemp inner join conlancam on c70_codlan = c75_codlan inner join empempenho on c75_numemp = e60_numemp inner join conlancamordem on conlancamordem.c03_codlan = conlancam.c70_codlan left outer join conlancampag on c82_codlan = c70_codlan inner join conlancamdoc on c71_codlan = c70_codlan inner join conhistdoc on c53_coddoc = c71_coddoc left join conlancamcompl on c72_codlan =c70_codlan left join conlancamnota on c66_codlan =c70_codlan left join conlancamord on c80_codlan =c70_codlan left join empnota on c66_codnota = e69_codnota left join conplanoreduz on c61_reduz = conlancampag.c82_reduz and c61_anousu=c70_anousu left join conplano on c60_codcon = conplanoreduz.c61_codcon and c60_anousu=c61_anousu left join pagordem on e50_codord = c80_codord inner join conlancaminstit on c02_codlan = c70_codlan inner join db_config on c02_instit = db_config.codigo left join conlancamretencao on c127_conlancam = c70_codlan where c75_numemp = {$seqempenho} AND c70_data BETWEEN '{$di}' AND '{$df}' AND c53_coddoc IN(3) order by c75_data, c03_ordem, c75_codlan");
    $resultado = pg_fetch_all($sql);
    return $resultado[0];
    //return ($resultado[0]["e69_numero"]) ? $resultado[0]["e69_numero"] : false;
}

public function buscaCodord($coddot, $codlan){
    $di = $this->dtDataInicial;
    $df = $this->dtDataFinal;

    $sql = pg_query("SELECT c70_codlan, c70_data, c70_valor, c71_coddoc, c53_descr, c80_codord, c75_numemp, c76_numcgm, z01_nome, e69_numero, e69_codnota, c72_complem, c73_coddot, c74_codrec, c70_anousu, c53_tipo, c67_codele from conlancam inner join conlancamdoc on c71_codlan = c70_codlan inner join conhistdoc on c71_coddoc = c53_coddoc left join conlancamord on c70_codlan = c80_codlan left join conlancamemp on c75_codlan = c70_codlan left join conlancamcgm on c70_codlan = c76_codlan left join cgm on z01_numcgm = c76_numcgm left join conlancamnota on c70_codlan = c66_codlan left join empnota on c66_codnota = e69_codnota left join conlancamcompl on c70_codlan = c72_codlan left join conlancamdot on c73_codlan = c70_codlan and c73_anousu = c70_anousu left join conlancamele on c67_codlan = c70_codlan left join conlancamrec on c74_codlan = c70_codlan and c74_anousu = c70_anousu where c73_coddot = {$coddot} AND c70_codlan = {$codlan} AND c70_data between '{$di}' AND '{$df}' AND c71_coddoc = 3");
    $resultado = pg_fetch_all($sql);
    
    return $resultado[0]["c80_codord"];
}

public function retornaTipo($codnota){
    $sql = pg_query("SELECT e177_codigo, e177_descr FROM sigfistipodocliquidacao INNER JOIN empnotasigfistipodocliquidacao ON e178_sigfistipodocliquidacao = e177_sequencial WHERE e178_empnota = {$codnota}");
    $resultado = pg_fetch_all($sql);
    return $resultado[0]["e177_codigo"];
}

public function buscaCPFordenador($cgm){
    $sql = pg_query("SELECT z01_cgccpf FROM cgm WHERE z01_numcgm = {$cgm}");
    $resultado = pg_fetch_all($sql);
    return $resultado[0]["z01_cgccpf"];
}

public function buscaDadosCredorRetencao($cgm){
    $sql = pg_query("SELECT z01_nome, z01_cgccpf FROM cgm WHERE z01_numcgm = {$cgm}");
    $resultado = pg_fetch_all($sql);
    return $resultado[0];
}

public function voltaContaContabil($coddot, $seqempenho){
    $di = $this->dtDataInicial;
    $df = $this->dtDataFinal;

    $sql = pg_query("SELECT c70_codlan, c70_data as data, c70_valor as valor, c71_coddoc as documento, c53_descr as descricaoevento, c80_codord as ordempagamento, c75_numemp as empenho, c76_numcgm as cgm, z01_nome as nome, e69_numero as notafiscal, e69_codnota as codigonotafiscal, c72_complem as complemento, c73_coddot as dotacao, c74_codrec as receita, c70_anousu as anolancamento, c53_tipo as tipoevento, c67_codele as codigoelemento from conlancam inner join conlancamdoc on c71_codlan = c70_codlan inner join conhistdoc on c71_coddoc = c53_coddoc left join conlancamord on c70_codlan = c80_codlan left join conlancamemp on c75_codlan = c70_codlan left join conlancamcgm on c70_codlan = c76_codlan left join cgm on z01_numcgm = c76_numcgm left join conlancamnota on c70_codlan = c66_codlan left join empnota on c66_codnota = e69_codnota left join conlancamcompl on c70_codlan = c72_codlan left join conlancamdot on c73_codlan = c70_codlan and c73_anousu = c70_anousu left join conlancamele on c67_codlan = c70_codlan left join conlancamrec on c74_codlan = c70_codlan and c74_anousu = c70_anousu where c73_coddot = {$coddot} AND c71_coddoc = 6002 AND c75_numemp = {$seqempenho} AND c70_data between '{$di}' AND '{$df}'");
    $codlan = pg_fetch_all($sql);
    $codlan = $codlan[0]["c70_codlan"];

    if(empty($codlan)){
        $sql = pg_query("SELECT c70_codlan, c70_data as data, c70_valor as valor, c71_coddoc as documento, c53_descr as descricaoevento, c80_codord as ordempagamento, c75_numemp as empenho, c76_numcgm as cgm, z01_nome as nome, e69_numero as notafiscal, e69_codnota as codigonotafiscal, c72_complem as complemento, c73_coddot as dotacao, c74_codrec as receita, c70_anousu as anolancamento, c53_tipo as tipoevento, c67_codele as codigoelemento from conlancam inner join conlancamdoc on c71_codlan = c70_codlan inner join conhistdoc on c71_coddoc = c53_coddoc left join conlancamord on c70_codlan = c80_codlan left join conlancamemp on c75_codlan = c70_codlan left join conlancamcgm on c70_codlan = c76_codlan left join cgm on z01_numcgm = c76_numcgm left join conlancamnota on c70_codlan = c66_codlan left join empnota on c66_codnota = e69_codnota left join conlancamcompl on c70_codlan = c72_codlan left join conlancamdot on c73_codlan = c70_codlan and c73_anousu = c70_anousu left join conlancamele on c67_codlan = c70_codlan left join conlancamrec on c74_codlan = c70_codlan and c74_anousu = c70_anousu where c73_coddot = {$coddot} AND c71_coddoc = 6004 AND c75_numemp = {$seqempenho} AND c70_data between '{$di}' AND '{$df}'");
    $codlan = pg_fetch_all($sql);
    $codlan = $codlan[0]["c70_codlan"];
    }

    $sql2 = pg_query("SELECT c69_debito as contadebito, c1.c60_descr as descricaodebito, c1.c60_estrut as estruturaldebito, c69_credito as contacredito, c2.c60_descr as descricaocredito, c2.c60_estrut as estruturalcredito, c69_valor as valor, c69_ordem as ordem, fc_atributo_conta_corrente({$codlan}, c69_debito, 'D') as atributos_cc_debito, fc_atributo_conta_corrente({$codlan}, c69_credito, 'C') as atributos_cc_credito from conlancam inner join conlancamval on c70_codlan = c69_codlan inner join conplanoreduz red1 on red1.c61_reduz = conlancamval.c69_debito and red1.c61_anousu = conlancamval.c69_anousu inner join conplano c1 on c1.c60_codcon = red1.c61_codcon and c1.c60_anousu = red1.c61_anousu inner join conplanoreduz red2 on red2.c61_reduz = conlancamval.c69_credito and red2.c61_anousu = conlancamval.c69_anousu inner join conplano c2 on c2.c60_codcon = red2.c61_codcon and c2.c60_anousu = red2.c61_anousu where c70_codlan={$codlan} order by c69_ordem, c69_sequen");
    $resultado = pg_fetch_all($sql2);
    return $resultado[0]["estruturalcredito"];
}

public function buscaCodord2($codlan){
    $di = $this->dtDataInicial;
    $df = $this->dtDataFinal;

    $sql = pg_query("SELECT c70_codlan, c70_data, c70_valor, c71_coddoc, c53_descr, c80_codord, c75_numemp, c76_numcgm, z01_nome, e69_numero, e69_codnota, c72_complem, c73_coddot, c74_codrec, c70_anousu, c53_tipo, c67_codele from conlancam inner join conlancamdoc on c71_codlan = c70_codlan inner join conhistdoc on c71_coddoc = c53_coddoc left join conlancamord on c70_codlan = c80_codlan left join conlancamemp on c75_codlan = c70_codlan left join conlancamcgm on c70_codlan = c76_codlan left join cgm on z01_numcgm = c76_numcgm left join conlancamnota on c70_codlan = c66_codlan left join empnota on c66_codnota = e69_codnota left join conlancamcompl on c70_codlan = c72_codlan left join conlancamdot on c73_codlan = c70_codlan and c73_anousu = c70_anousu left join conlancamele on c67_codlan = c70_codlan left join conlancamrec on c74_codlan = c70_codlan and c74_anousu = c70_anousu where c70_codlan = {$codlan}");
    $resultado = pg_fetch_all($sql);
    
    return $resultado[0]["c80_codord"];
}

public function buscaNaturezaDespesa($seqempenho){
    $sql = pg_query("SELECT e64_codele, o56_elemento, o56_descr from empelemento inner join empempenho on empempenho.e60_numemp = empelemento.e64_numemp inner join orcelemento on orcelemento.o56_codele = empelemento.e64_codele and orcelemento.o56_anousu = empempenho.e60_anousu inner join cgm on cgm.z01_numcgm = empempenho.e60_numcgm inner join db_config on db_config.codigo = empempenho.e60_instit inner join orcdotacao on orcdotacao.o58_anousu = empempenho.e60_anousu and orcdotacao.o58_coddot = empempenho.e60_coddot inner join emptipo on emptipo.e41_codtipo = empempenho.e60_codtipo where empelemento.e64_numemp = {$seqempenho} order by e64_codele");
    $resultado = pg_fetch_all($sql);
    return substr($resultado[0]["o56_elemento"], 1, 8);
}


    public function gerarDados(){
        $ugs = $this->pegaCgmOrdenador();
        $ugs = $this->buscaCPFordenador($ugs);

        $fontessubelemento = array(            
            31900805 => 33900805,
            31900901 => 33900856,
            33901899 => 33901801,
            33904022 => 33904014,
            33901414 => 33901401,
            33903006 => 33903007,
            33903696 => 33903699,
            44905212 => 44905208,
            33909218 => 33901801,
            44905206 => 44905208,
            33903937 => 33903936,
            33903981 => 33903999,
            33903645 => 33903645,
            33904712 => 33904706,
            33903607 => 33903607,
            31909199 => 31909291,
            33903101 => 33903101,
            46907106 => 46907101,
            46907104 => 46907101,
            46907301 => 46907399,
            32902104 => 32902101,
            32902106 => 32902101,
            33901499 => 33901401,
            33904713 => 33904705,
            33909299 => 33901801,
            46907101 => 46907101,
            33903004 => 33903001,
            33903096 => 33903094,
            33904002 => 33904001,
            33903058 => 33903024,
            44905235 => 44905299,
            33909600 => 33909601,
            33904004 => 33904001,
            33903054 => 33903024,
            33904011 => 33904012,
            33904003 => 33904012,
            33903022 => 33903021,
            33903992 => 33903990,
            33903057 => 33903024,
            44905234 => 44905299,
            33904801 => 33904899,
            33903966 => 33903906,
            33904603 => 33904601,
            33904602 => 33904601,
            33903055 => 33903024,
            33903056 => 33903024,
            44905237 => 44905299,
            33904013 => 33904014,
            33903499 => 33903400,
            33904007 => 33904099,
            13220011 => 13220101,
            13999901 => 19999921,
            13999901 => 19999921,
            19909912 => 19999922,
            19909912 => 19999922,
            19909913 => 19999923,
            46907105 => 46907101,
            33904715 => 33904705,
            46907110 => 46907101,
            46907111 => 46907101,
            46907112 => 46907101,
            33903401 => 33903400,
            46907108 => 46907101,
            46907109 => 46907101,
            13900011 => 13999901,
            33903400 => 33903401,
            44904005 => 44904099,
            33904501 => 33904599,
            33903054 => 33903099,
            33903003 => 33903001,
            33903058 => 33903024,
            33903004 => 33903001,
            44905210 => 44905208,
            33903914 => 33903913,
            44905204 => 44905208,
            33903906 => 33903906,
            33903401 => 33903499,
            33903946 => 33903906,
            33903499 => 33903401,
            44905238 => 44905299,
            44905238 => 44905299,
            44905238 => 44905299,
            33904017 => 33904099,
            33903400 => 33903401,
            33904023 => 33904099,
            33903400 => 33903401,
            33904017 => 33904099,
            33904007 => 33904099,
            33904013 => 33904014,
            33904017 => 33904099,
            33904013 => 33904014,
            33904021 => 33904099,
            44905239 => 44905299,
            44905204 => 44905299,
            44905226 => 44905299,
            45906109 => 45906199,
            44905233 => 44905208,
            45906109 => 45909261,
            44906100 => 44906101,
            31909106 => 31909126,
            33903659 => 33903649,
            44905230 => 44905299,
            33504103 => 33504199,
            33904600 => 33904601,
            31903400 => 31900499,
            44905251 => 44905242,
            44905230 => 44905299,
            33903050 => 33903099,
            44905241 => 44905299,
            44905228 => 44905299,
            44906107 => 44906103,
            44717000 => 44717001,
            33717000 => 33717001,
            44905192 => 44905103,
            17235040 => 17235001,
            33727000 => 33729901,
            33904800 => 33904899,
            33723900 => 33723901,
            32902105 => 32902101,
            33903929 => 33903923,
            46907107 => 46907101,
            33909205 => 33909299,
            44905233 => 44905208,
            33903926 => 33903999,
            33904716 => 33903936,
            33903938 => 33903936,
            33903938 => 33903936
        );        

        $campos = [
            'e60_numemp', 'e60_codemp', 'e60_anousu', 'e50_codord',
            'e69_codnota', 'e69_dtinclusao', 'e70_valor','e177_codigo','o58_orgao','o58_unidade',
            'z01_cgccpf as cpf', 'c60_estrut'
        ];

        $empenhos = DB::table('empempenho')
            ->select($campos)
            ->join('empnota', 'empnota.e69_numemp', 'empempenho.e60_numemp')
            ->join('empnotaele', 'e70_codnota', 'e69_codnota')
            ->join('pagordem', 'e50_numemp', 'e69_numemp')
            ->join('pagordemnota', function (JoinClause $join) {
                $join->on('e71_codnota', 'e69_codnota')
                    ->on('e71_codord', 'e50_codord');
            })
            ->join('db_usuacgm', 'id_usuario', 'e50_id_usuario')
            ->join('cgm', 'z01_numcgm', 'cgmlogin')
            //@todo - remover  LEFT JOIN TEMPORARIO POR FALTA DE REGISTROS. Apenas para teste
            ->leftJoin(
                'empnotasigfistipodocliquidacao',
                'empnotasigfistipodocliquidacao.e178_empnota',
                'empnota.e69_codnota'
            )
            ->leftJoin(
                'sigfistipodocliquidacao',
                'sigfistipodocliquidacao.e177_sequencial',
                'empnotasigfistipodocliquidacao.e178_sigfistipodocliquidacao'
            )
            ->join('orcdotacao', function ($join) {
                $join->on('e60_anousu', 'o58_anousu')
                    ->on('e60_coddot', 'o58_coddot');
            })
            ->join('empelemento', 'empelemento.e64_numemp', 'empempenho.e60_numemp')
            ->join('conplanoorcamento', function ($join) {
                $join->on('conplanoorcamento.c60_codcon', 'empelemento.e64_codele');
                $join->on('conplanoorcamento.c60_anousu', 'empempenho.e60_anousu');
            })
            /*->join(
                'planodespesaconplanoorcamento',
                'planodespesaconplanoorcamento.conplanoorcamento_codigo',
                'conplanoorcamento.c60_codigo'
            )
            ->join('planodespesa', function (JoinClause $join) {
                $join->on('planodespesa.id', 'planodespesa_id')
                    ->on('exercicio', 'e60_anousu')
                    ->where('uniao', '=', 't');
            })*/
            ->where('e60_instit', $this->instit)
            ->where('e60_anousu', $this->iAnoUsu)
            ->whereBetween('e69_dtinclusao', [$this->dtDataInicial, $this->dtDataFinal])
            ->get();

            //$sql_with_bindings = str_replace_array('?', $empenhos->getBindings(), $empenhos->toSql());            
            //$sql_with_bindings = str_replace('"', '', $sql_with_bindings);
            //var_dump($sql_with_bindings);
            //die("Confere");


            $empenhos = $this->voltaLiquidacaoes();
            //$this->testa($empenhos); die("Confere");
        //if ($empenhos->isEmpty()) {
        if(count($empenhos) == 0){
            throw new \Exception('Nao foi encontrardo nenhum empenho para o arquivo de Remessa ');
        }

        $obj = new \stdClass();
        $obj->LiquidacoesDeEmpenho = [];
        $guarda = array();
        $ix = 1;
        foreach ($empenhos as $empenho) {
            //if($empenho->e60_codemp != 156){continue;}
            $notafiscal = "";
            $numerofiscal = "";
            if(!$notafiscal){
                $dadosnotafiscal = $this->buscaNotaDoEmpenhao2($empenho->e60_numemp);
                $notafiscal = $dadosnotafiscal["e69_codnota"];
                $numerofiscal = $dadosnotafiscal["e69_numero"];
            }            
            
            if(!$numerofiscal){
                $numerofiscal = "S/N";
            }

            if($notafiscal == 451825){continue;}            

            
            $codord = $this->buscaCodord2($empenho->c70_codlan);
            $tipodoc = $this->retornaTipo($empenho->c66_codnota);
            //if($codord != 575089){continue;}

            /*if(empty($tipodoc)){
                echo $empenho->e60_numemp . " - " . $empenho->c66_codnota; echo "<br>";
                continue;
            }*/

            /*
            $chave = $empenho->e60_codemp.$empenho->e60_anousu.$empenho->o58_unidade.$empenho->o58_orgao.$notafiscal;
            if(in_array($chave, $guarda)){
                continue;
            }
            array_push($guarda, $chave);
            */
            

            $empenho->natureza_despesa = substr($empenho->c60_estrut, 1, 8);            
            $verificaretencoes = $this->voltaRetencoes($codord);
            
            
            if($verificaretencoes){
                //$this->testa($verificaretencoes); die("Confere");
                /*
                $dadoscredor = $this->buscaDadosCredorRetencao($verificaretencoes[0]["e48_cgm"]);
                $nomec = trim($dadoscredor["z01_nome"]);
                $cpfc = trim($dadoscredor["z01_cgccpf"]);

                if((empty($cpfc) && empty($nomec)) && $this->instit == 50){
                    $nomec = "FUNDO MUNICIPAL DE SAUDE / SMS";
                    $cpfc = "39563911000162";
                }

                $tipoc = (strlen($cpfc) == 11) ? 2 : 1;
                $contacontabil = $this->voltaContaContabil($empenho->e60_coddot, $empenho->e60_numemp);
                $contacontabil = substr($contacontabil, 0, 9);

                if($contacontabil == 411120430){$contacontabil = 228810104;}
                if($contacontabil == 411180211){$contacontabil = 228810108;}
                if($contacontabil == 411180211){$contacontabil = 228810108;}
                if($contacontabil == 499619901){$contacontabil = 228810105;}
                */
                
                //$this->testa($contacontabil);
                //var_dump($empenho->e60_coddot, $empenho->e60_numemp);
                //die("Confere");
                
                //$consignacaoRetencoes = new stdClass();
                $consignacaoRetencoes = [];
                $indice = 0;
                $xconsignacao = array();
                foreach ($verificaretencoes as $retencao) {

                    $dadoscredor = $this->buscaDadosCredorRetencao($retencao["e48_cgm"]);
                    $nomec = trim($dadoscredor["z01_nome"]);
                    $cpfc = trim($dadoscredor["z01_cgccpf"]);

                    if((empty($cpfc) && empty($nomec)) && $this->instit == 50){
                        $nomec = "FUNDO MUNICIPAL DE SAUDE / SMS";
                        $cpfc = "39563911000162";
                    }

                    $tipoc = (strlen($cpfc) == 11) ? 2 : 1;
                    $contacontabil = $this->voltaContaContabil($empenho->e60_coddot, $empenho->e60_numemp);
                    $contacontabil = substr($contacontabil, 0, 9);
                    /*if($retencao["e23_sequencial"] == 166201){
                        var_dump($contacontabil);
                        die("Confere");
                    }*/

                    if($contacontabil == 411120430){$contacontabil = 228810104;}
                    if($contacontabil == 411180211){$contacontabil = 228810108;}
                    if($contacontabil == 411180211){$contacontabil = 228810108;}
                    if($contacontabil == 499619901){$contacontabil = 228810105;}
                    if($contacontabil == 218810303){$contacontabil = 218810301;}
                    if($contacontabil == 111112002){$contacontabil = 111110200;}
                    if($contacontabil == 111111903){$contacontabil = 111111900;}
                    if($contacontabil == 228810119){$contacontabil = 218810102;}
                    if($contacontabil == 218819901){$contacontabil = 218810199;}
                    
                    if($retencao["e21_tiporet"] == 2){
                        $contacontabil = 218810115;
                    }

                    if($retencao["e21_tiporet"] == 3){
                        $contacontabil = 218810105;
                    }

                    if($retencao["e21_tiporet"] == 5 || $retencao["e21_tiporet"] == 8){
                        $contacontabil = 218810199;
                    }

                    if($retencao["e21_tiporet"] == 6){
                        $contacontabil = 218810104;
                    }

                    if($retencao["e21_tiporet"] == 7){
                        $contacontabil = 218810108;
                    }


                    $consignacaoRetencoes[] = (object)[
                        'Identificador' => $retencao["e23_sequencial"],
                        'Valor' => $retencao["e23_valorretencao"],
                        'TipoConsignacaoRetencao' => $retencao["e21_tiporet"],
                        'ContaContabil' => $contacontabil,
                        'CpfCnpjCredor' => $cpfc,
                        'NaturezaCredor' => $tipoc,
                        'NomeCredor' => trim(utf8_encode($nomec))
                    ];

                     

                }
                $consignacaoRetencoesXML = [];
                foreach ($consignacaoRetencoes as $retencao) {
                    $consignacaoRetencoesXML[] = [
                        'ConsignacaoRetencao' => (array) $retencao
                    ];
                }
                
            }else{
                $consignacaoRetencoesXML = null;
            }
            

            $responsavelLiquidacao = new stdClass();
            $responsavelLiquidacao->Responsavel = [];
            $responsavelLiquidacao->Responsavel[] = (object) [
                'Identificador' => $ix,
                'CPF' => $ugs
            ];
            
            $zexplode = explode(".", $empenho->dl_estrutural);
            $zexplode = $zexplode[6];
            $empenho->natureza_despesa = substr($zexplode, 1, 8);

            $empenho->natureza_despesa = $this->buscaNaturezaDespesa($empenho->e60_numemp);
            
            
            if($fontessubelemento[$empenho->natureza_despesa]){
                $empenho->natureza_despesa2 = $fontessubelemento[$empenho->natureza_despesa];
            }else{
                $empenho->natureza_despesa2 = $empenho->natureza_despesa;
            }

            $subElementos = new stdClass();
            $subElementos->LiquidacaoDeEmpenhoSubElemento = [];
            $subElementos->LiquidacaoDeEmpenhoSubElemento[] = (object) [
                'Identificador' => $empenho->c70_codlan,
                'Valor' => $empenho->c70_valor,
                'NaturezaDespesa' => $empenho->natureza_despesa2
            ];

            $dadosLiquidacaoEmpenho = (object)[                
                'Identificador' => $ix,
                'CodigoUnidadeGestora' => $this->sCodigoTribunal,
                'Competencia' => $this->competencia,
                'NumeroEmpenho' => $empenho->e60_codemp,
                'NumeroLiquidacaoEmpenho' =>  $codord,
                'DataLiquidacao' => $empenho->c70_data,
                'ValorBrutoLiquidacao' => $empenho->c70_valor,
                'ResponsaveisLiquidacao' => $responsavelLiquidacao,
                'TipoDocumento' => (!empty($tipodoc)) ? $tipodoc : 1,
                'Ano' => $this->iAnoUsu,
                'AnoEmpenho' => $empenho->e60_anousu,                
                'CodigoOrgao' => $empenho->o58_orgao,
                'CodigoUnidadeOrcamentaria' => $empenho->o58_unidade,
                'SubElemento' => $subElementos,
                //'ConsignacoesRetencoes' => $consignacaoRetencoes,
                'ConsignacoesRetencoes' => $consignacaoRetencoesXML

                
            ];

            $obj->LiquidacoesDeEmpenho[] = (object)['LiquidacaoDeEmpenho' => $dadosLiquidacaoEmpenho];
            $ix++;
        }
        //die("Para");
        
        
        $this->aDados =  $obj;
    }
}

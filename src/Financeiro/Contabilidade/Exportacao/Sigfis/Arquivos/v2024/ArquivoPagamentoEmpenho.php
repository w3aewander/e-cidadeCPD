<?php

namespace ECidade\Financeiro\Contabilidade\Exportacao\Sigfis\Arquivos\v2024;


use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Database\Query\JoinClause;
use stdClass;

class ArquivoPagamentoEmpenho extends ArquivoBase
{
    protected $sNomeArquivo = 'PagamentoDeEmpenho';

    public function testa($var){
        echo "<pre>";
        print_r($var);
        echo "</pre>";
    }

    public function buscaDados($codempenho, $codnota){
        $inst = $this->instit;
        $di = $this->dtDataInicial;
        $df = $this->dtDataFinal;
        $ano = $this->iAnoUsu;        
        
        $sql = pg_query("SELECT e60_numemp, e60_codemp, e60_anousu, e69_codnota, e69_numero, c70_codlan, c70_data, c70_valor, c70_anousu, c72_complem, e69_anousu, z01_nome, z01_cgccpf, db90_codban, db89_codagencia, db89_digito, db83_conta, db83_dvconta from empempenho inner join empnota on e69_numemp = e60_numemp inner join pagordemnota on e71_codnota = e69_codnota inner join conlancamemp on c75_numemp = e60_numemp inner join conlancam on c75_codlan = c70_codlan inner join conlancamdoc on c71_codlan = c70_codlan inner join conlancamcompl on c72_codlan = c70_codlan inner join conlancamord on c80_codlan = c70_codlan and c80_codord = e71_codord inner join lancamentoscontabeislog as log on codlan = c70_codlan and tipo_movimento = 1 inner join db_usuacgm on db_usuacgm.id_usuario = log.id_usuario inner join cgm as cgmusu on cgmusu.z01_numcgm = cgmlogin inner join conlancampag on c82_codlan = c70_codlan inner join contabilidade.conplanoreduz on c61_reduz = c82_reduz and c61_anousu = c82_anousu inner join contabilidade.conplanocontabancaria on c56_reduz = c61_reduz and c56_anousu = c61_anousu inner join configuracoes.contabancaria on c56_contabancaria = db83_sequencial inner join configuracoes.bancoagencia on db89_sequencial = db83_bancoagencia inner join configuracoes.db_bancos on db90_codban = db89_db_bancos where c71_coddoc in (5) and e60_anousu = {$ano} and e60_instit = {$inst} and c70_data between '{$di}' and '{$df}' AND e60_codemp = '{$codempenho}' AND e69_codnota = '{$codnota}' order by e60_numemp asc");
        $resultado = pg_fetch_all($sql);
        return $resultado;
    }

    public function buscaDados2($codempenho){
        $inst = $this->instit;
        $di = $this->dtDataInicial;
        $df = $this->dtDataFinal;
        $ano = $this->iAnoUsu;        
        
        $sql = pg_query("SELECT e60_numemp, e60_codemp, e60_anousu, e69_codnota, e69_numero, c70_codlan, c70_data, c70_valor, c70_anousu, c72_complem, e69_anousu, z01_nome, z01_cgccpf, db90_codban, db89_codagencia, db89_digito, db83_conta, db83_dvconta from empempenho inner join empnota on e69_numemp = e60_numemp inner join pagordemnota on e71_codnota = e69_codnota inner join conlancamemp on c75_numemp = e60_numemp inner join conlancam on c75_codlan = c70_codlan inner join conlancamdoc on c71_codlan = c70_codlan inner join conlancamcompl on c72_codlan = c70_codlan inner join conlancamord on c80_codlan = c70_codlan and c80_codord = e71_codord inner join lancamentoscontabeislog as log on codlan = c70_codlan and tipo_movimento = 1 inner join db_usuacgm on db_usuacgm.id_usuario = log.id_usuario inner join cgm as cgmusu on cgmusu.z01_numcgm = cgmlogin inner join conlancampag on c82_codlan = c70_codlan inner join contabilidade.conplanoreduz on c61_reduz = c82_reduz and c61_anousu = c82_anousu inner join contabilidade.conplanocontabancaria on c56_reduz = c61_reduz and c56_anousu = c61_anousu inner join configuracoes.contabancaria on c56_contabancaria = db83_sequencial inner join configuracoes.bancoagencia on db89_sequencial = db83_bancoagencia inner join configuracoes.db_bancos on db90_codban = db89_db_bancos where c71_coddoc in (5) and e60_anousu = {$ano} and e60_instit = {$inst} and c70_data between '{$di}' and '{$df}' AND e60_codemp = '{$codempenho}' order by e60_numemp asc");
        $resultado = pg_fetch_all($sql);
        return $resultado;
    }

    public function verificaAnulacao($codnota, $codlan){
    $sql1 = pg_query("SELECT c80_codord, c71_coddoc, c70_codlan, c70_valor from empempenho inner join empnota on e69_numemp = e60_numemp inner join orcdotacao on o58_anousu = e60_anousu and o58_coddot = e60_coddot inner join conlancamemp on c75_numemp = e60_numemp inner join conlancam on c75_codlan = c70_codlan inner join conlancamdoc on c71_codlan = c70_codlan inner join conlancamcompl on c72_codlan = c70_codlan inner join conlancamnota on c66_codlan = c70_codlan and c66_codnota = e69_codnota inner join lancamentoscontabeislog as log on codlan = c70_codlan and tipo_movimento = 1 inner join db_usuacgm on db_usuacgm.id_usuario = log.id_usuario inner join cgm as cgmusu on cgmusu.z01_numcgm = cgmlogin INNER JOIN conlancamord ON c80_codlan = c70_codlan where e69_codnota = {$codnota} AND c70_codlan = {$codlan} order by e60_numemp asc");
    $r1 = pg_fetch_all($sql1);
    $codord = $r1[0]["c80_codord"];

    $sql2 = pg_query("SELECT c80_codord, c71_coddoc, c70_codlan, c70_valor from empempenho inner join empnota on e69_numemp = e60_numemp inner join orcdotacao on o58_anousu = e60_anousu and o58_coddot = e60_coddot inner join conlancamemp on c75_numemp = e60_numemp inner join conlancam on c75_codlan = c70_codlan inner join conlancamdoc on c71_codlan = c70_codlan inner join conlancamcompl on c72_codlan = c70_codlan inner join conlancamnota on c66_codlan = c70_codlan and c66_codnota = e69_codnota inner join lancamentoscontabeislog as log on codlan = c70_codlan and tipo_movimento = 1 inner join db_usuacgm on db_usuacgm.id_usuario = log.id_usuario inner join cgm as cgmusu on cgmusu.z01_numcgm = cgmlogin INNER JOIN conlancamord ON c80_codlan = c70_codlan where e69_codnota = {$codnota} AND c80_codord = {$codord} AND c71_coddoc = 205 order by e60_numemp asc");
    $resultado = pg_fetch_all($sql2);
    return $resultado;
}

public function buscaValores($seqemp, $codnota){
    $sql = pg_query("SELECT e69_codnota, e69_numero, e69_dtnota, e69_dtinclusao, e69_dtservidor, e70_valor, e70_vlrliq, e70_vlranu, e53_vlrpag from empnota inner join empnotaele on e70_codnota = e69_codnota left join pagordemnota on e70_codnota = e71_codnota and e71_anulado is false left join pagordemele on e71_codord = e53_codord where e69_numemp = {$seqemp} AND e69_codnota = {$codnota} order by e69_dtnota");
    $resultado = pg_fetch_all($sql);
    return $resultado[0];
}

public function retornaCodlan($seqemp, $numnota, $codord){
    $sql = pg_query("SELECT c53_tipo, e50_codord, c70_codlan, c70_data, c53_descr, case when c127_conlancam is not null then 'Sim' else 'Não' end as dl_Lançamento_Retenção, c70_valor, c82_reduz, c60_descr, c72_complem, e69_numero as dl_Nota_Fiscal, e50_codord, e50_data, nomeinstabrev as dl_Ente from conlancamemp inner join conlancam on c70_codlan = c75_codlan inner join empempenho on c75_numemp = e60_numemp inner join conlancamordem on conlancamordem.c03_codlan = conlancam.c70_codlan left outer join conlancampag on c82_codlan = c70_codlan inner join conlancamdoc on c71_codlan = c70_codlan inner join conhistdoc on c53_coddoc = c71_coddoc left join conlancamcompl on c72_codlan =c70_codlan left join conlancamnota on c66_codlan =c70_codlan left join conlancamord on c80_codlan =c70_codlan left join empnota on c66_codnota = e69_codnota left join conplanoreduz on c61_reduz = conlancampag.c82_reduz and c61_anousu=c70_anousu left join conplano on c60_codcon = conplanoreduz.c61_codcon and c60_anousu=c61_anousu left join pagordem on e50_codord = c80_codord inner join conlancaminstit on c02_codlan = c70_codlan inner join db_config on c02_instit = db_config.codigo left join conlancamretencao on c127_conlancam = c70_codlan where c75_numemp = {$seqemp} AND e69_numero = '{$numnota}' AND e50_codord = {$codord} order by c75_data, c03_ordem, c75_codlan");
    $resultado = pg_fetch_all($sql);
    return $resultado[0]["c70_codlan"];    
}



public function buscaEmpenhosPagos(){
    $inst = $this->instit;
    $di = $this->dtDataInicial;
    $df = $this->dtDataFinal;
    $ano = $this->iAnoUsu;
    $sql = pg_query("SELECT empempenho.e60_numemp::integer as e60_numemp, e60_resumo, e60_destin, e60_codemp, e60_emiss, e60_numcgm, z01_nome, z01_cgccpf, z01_munic, e60_vlremp, e60_vlranu, e60_vlrliq, e63_codhist, e40_descr, e60_vlrpag, e60_anousu, e60_coddot, o58_coddot, o58_orgao, o40_orgao, o40_descr, o58_unidade, o41_descr, o15_codigo, o15_descr, fc_estruturaldotacao(e60_anousu,e60_coddot) as dl_estrutural, e60_codcom, pc50_descr, sum(c70_valor) as c70_valor, c70_data, c70_codlan, c53_tipo, c53_descr, (select o56_elemento||'-'||o56_descr from orcelemento where o56_codele = c67_codele and o56_anousu = c70_anousu) as desdobramento, c72_complem, z01_incest, z01_cgccpf, c66_codnota, e91_numemp from empempenho inner join conlancamemp on c75_numemp = empempenho.e60_numemp inner join conlancam on c70_codlan = c75_codlan left join conlancamnota on c66_codlan = c70_codlan inner join conlancamdoc on c71_codlan = c70_codlan inner join conhistdoc on c53_coddoc = c71_coddoc inner join cgm on cgm.z01_numcgm = empempenho.e60_numcgm inner join db_config on db_config.codigo = empempenho.e60_instit inner join orcdotacao on orcdotacao.o58_anousu = empempenho.e60_anousu and orcdotacao.o58_coddot = empempenho.e60_coddot and orcdotacao.o58_instit = empempenho.e60_instit inner join emptipo on emptipo.e41_codtipo = empempenho.e60_codtipo inner join db_config as a on a.codigo = orcdotacao.o58_instit INNER JOIN origemcomplementorecurso ON origemcomplementorecurso.o206_numero = empempenho.e60_numemp AND origemcomplementorecurso.o206_origem = 1 INNER JOIN orctiporec ON orctiporec.o15_codigo = origemcomplementorecurso.o206_recurso inner join orcfuncao on orcfuncao.o52_funcao = orcdotacao.o58_funcao inner join orcsubfuncao on orcsubfuncao.o53_subfuncao = orcdotacao.o58_subfuncao inner join orcprograma on orcprograma.o54_anousu = orcdotacao.o58_anousu and orcprograma.o54_programa = orcdotacao.o58_programa inner join orcelemento on orcelemento.o56_codele = orcdotacao.o58_codele and orcdotacao.o58_anousu = orcelemento.o56_anousu inner join orcprojativ on orcprojativ.o55_anousu = orcdotacao.o58_anousu and orcprojativ.o55_projativ = orcdotacao.o58_projativ inner join orcorgao on orcorgao.o40_anousu = orcdotacao.o58_anousu and orcorgao.o40_orgao = orcdotacao.o58_orgao inner join orcunidade on orcunidade.o41_anousu = orcdotacao.o58_anousu and orcunidade.o41_orgao = orcdotacao.o58_orgao and orcunidade.o41_unidade = orcdotacao.o58_unidade left join empemphist on empemphist.e63_numemp = empempenho.e60_numemp left join emphist on emphist.e40_codhist = empemphist.e63_codhist inner join pctipocompra on pctipocompra.pc50_codcom = empempenho.e60_codcom left join empresto on e60_numemp = e91_numemp and e60_anousu = e91_anousu left join conlancamcompl on c72_codlan = c70_codlan inner join conlancamele on c67_codlan = c70_codlan where c53_tipo in (30) and e60_numemp not in (select e91_numemp from empresto where e91_anousu = {$ano}) and c53_coddoc in(5, 35, 37) and c70_data between '{$di}' and '{$df}' and 1=1 and e60_instit in ({$inst}) group by e60_numemp, e60_resumo, e60_destin, e60_codemp, e60_emiss, e60_numcgm, z01_nome, z01_cgccpf, z01_munic, e60_vlremp, e60_vlranu, e60_vlrliq, e63_codhist, e40_descr, e60_vlrpag, e60_anousu, e60_coddot, o58_coddot, o58_orgao, o40_orgao, o40_descr, o58_unidade, o41_descr, o15_codigo, o15_descr, e60_codcom, pc50_descr, c70_data, c70_codlan, c53_tipo, c53_descr, desdobramento, c72_complem, z01_incest, z01_cgccpf, c66_codnota, e91_numemp order by e60_numemp, c70_codlan");

    
    $resultado = array();

    while ($linha = pg_fetch_object($sql)) {
        $resultado[] = $linha;
    }
        
    return $resultado;
}


public function buscaEmpenhosPagosPorEmpenho($seqempenho){
    $inst = $this->instit;
    $di = $this->dtDataInicial;
    $df = $this->dtDataFinal;
    $ano = $this->iAnoUsu;

    $sql = pg_query("SELECT empempenho.e60_numemp::integer as e60_numemp, e60_resumo, e60_destin, e60_codemp, e60_emiss, e60_numcgm, z01_nome, z01_cgccpf, z01_munic, e60_vlremp, e60_vlranu, e60_vlrliq, e63_codhist, e40_descr, e60_vlrpag, e60_anousu, e60_coddot, o58_coddot, o58_orgao, o40_orgao, o40_descr, o58_unidade, o41_descr, o15_codigo, o15_descr, fc_estruturaldotacao(e60_anousu,e60_coddot) as dl_estrutural, e60_codcom, pc50_descr, sum(c70_valor) as c70_valor, c70_data, c70_codlan, c53_tipo, c53_descr, (select o56_elemento||'-'||o56_descr from orcelemento where o56_codele = c67_codele and o56_anousu = c70_anousu) as desdobramento, c72_complem, z01_incest, z01_cgccpf, c66_codnota, e91_numemp, c70_anousu, c53_coddoc from empempenho inner join conlancamemp on c75_numemp = empempenho.e60_numemp inner join conlancam on c70_codlan = c75_codlan left join conlancamnota on c66_codlan = c70_codlan inner join conlancamdoc on c71_codlan = c70_codlan inner join conhistdoc on c53_coddoc = c71_coddoc inner join cgm on cgm.z01_numcgm = empempenho.e60_numcgm inner join db_config on db_config.codigo = empempenho.e60_instit inner join orcdotacao on orcdotacao.o58_anousu = empempenho.e60_anousu and orcdotacao.o58_coddot = empempenho.e60_coddot and orcdotacao.o58_instit = empempenho.e60_instit inner join emptipo on emptipo.e41_codtipo = empempenho.e60_codtipo inner join db_config as a on a.codigo = orcdotacao.o58_instit INNER JOIN origemcomplementorecurso ON origemcomplementorecurso.o206_numero = empempenho.e60_numemp AND origemcomplementorecurso.o206_origem = 1 INNER JOIN orctiporec ON orctiporec.o15_codigo = origemcomplementorecurso.o206_recurso inner join orcfuncao on orcfuncao.o52_funcao = orcdotacao.o58_funcao inner join orcsubfuncao on orcsubfuncao.o53_subfuncao = orcdotacao.o58_subfuncao inner join orcprograma on orcprograma.o54_anousu = orcdotacao.o58_anousu and orcprograma.o54_programa = orcdotacao.o58_programa inner join orcelemento on orcelemento.o56_codele = orcdotacao.o58_codele and orcdotacao.o58_anousu = orcelemento.o56_anousu inner join orcprojativ on orcprojativ.o55_anousu = orcdotacao.o58_anousu and orcprojativ.o55_projativ = orcdotacao.o58_projativ inner join orcorgao on orcorgao.o40_anousu = orcdotacao.o58_anousu and orcorgao.o40_orgao = orcdotacao.o58_orgao inner join orcunidade on orcunidade.o41_anousu = orcdotacao.o58_anousu and orcunidade.o41_orgao = orcdotacao.o58_orgao and orcunidade.o41_unidade = orcdotacao.o58_unidade left join empemphist on empemphist.e63_numemp = empempenho.e60_numemp left join emphist on emphist.e40_codhist = empemphist.e63_codhist inner join pctipocompra on pctipocompra.pc50_codcom = empempenho.e60_codcom left join empresto on e60_numemp = e91_numemp and e60_anousu = e91_anousu left join conlancamcompl on c72_codlan = c70_codlan inner join conlancamele on c67_codlan = c70_codlan where c53_tipo in (30) and e60_numemp not in (select e91_numemp from empresto where e91_anousu = {$ano}) and c53_coddoc in(5, 35, 37) and c70_data between '{$di}' and '{$df}' and 1=1 and e60_instit in ({$inst}) AND e60_numemp = {$seqempenho} group by e60_numemp, e60_resumo, e60_destin, e60_codemp, e60_emiss, e60_numcgm, z01_nome, z01_cgccpf, z01_munic, e60_vlremp, e60_vlranu, e60_vlrliq, e63_codhist, e40_descr, e60_vlrpag, e60_anousu, e60_coddot, o58_coddot, o58_orgao, o40_orgao, o40_descr, o58_unidade, o41_descr, o15_codigo, o15_descr, e60_codcom, pc50_descr, c70_data, c70_codlan, c53_tipo, c53_descr, desdobramento, c72_complem, z01_incest, z01_cgccpf, c66_codnota, e91_numemp, c70_anousu, c53_coddoc order by e60_numemp, c70_codlan");
        
    $resultado = array();

    while ($linha = pg_fetch_object($sql)) {
        $resultado[] = $linha;
    }
    return $resultado;
}

public function buscaEmpenhosPagosPorEmpenho2($seqempenho, $codlan){
    $inst = $this->instit;
    $di = $this->dtDataInicial;
    $df = $this->dtDataFinal;
    $ano = $this->iAnoUsu;

    $sql = pg_query("SELECT empempenho.e60_numemp::integer as e60_numemp, e60_resumo, e60_destin, e60_codemp, e60_emiss, e60_numcgm, z01_nome, z01_cgccpf, z01_munic, e60_vlremp, e60_vlranu, e60_vlrliq, e63_codhist, e40_descr, e60_vlrpag, e60_anousu, e60_coddot, o58_coddot, o58_orgao, o40_orgao, o40_descr, o58_unidade, o41_descr, o15_codigo, o15_descr, fc_estruturaldotacao(e60_anousu,e60_coddot) as dl_estrutural, e60_codcom, pc50_descr, sum(c70_valor) as c70_valor, c70_data, c70_codlan, c53_tipo, c53_descr, (select o56_elemento||'-'||o56_descr from orcelemento where o56_codele = c67_codele and o56_anousu = c70_anousu) as desdobramento, c72_complem, z01_incest, z01_cgccpf, c66_codnota, e91_numemp, c70_anousu, c53_coddoc from empempenho inner join conlancamemp on c75_numemp = empempenho.e60_numemp inner join conlancam on c70_codlan = c75_codlan left join conlancamnota on c66_codlan = c70_codlan inner join conlancamdoc on c71_codlan = c70_codlan inner join conhistdoc on c53_coddoc = c71_coddoc inner join cgm on cgm.z01_numcgm = empempenho.e60_numcgm inner join db_config on db_config.codigo = empempenho.e60_instit inner join orcdotacao on orcdotacao.o58_anousu = empempenho.e60_anousu and orcdotacao.o58_coddot = empempenho.e60_coddot and orcdotacao.o58_instit = empempenho.e60_instit inner join emptipo on emptipo.e41_codtipo = empempenho.e60_codtipo inner join db_config as a on a.codigo = orcdotacao.o58_instit INNER JOIN origemcomplementorecurso ON origemcomplementorecurso.o206_numero = empempenho.e60_numemp AND origemcomplementorecurso.o206_origem = 1 INNER JOIN orctiporec ON orctiporec.o15_codigo = origemcomplementorecurso.o206_recurso inner join orcfuncao on orcfuncao.o52_funcao = orcdotacao.o58_funcao inner join orcsubfuncao on orcsubfuncao.o53_subfuncao = orcdotacao.o58_subfuncao inner join orcprograma on orcprograma.o54_anousu = orcdotacao.o58_anousu and orcprograma.o54_programa = orcdotacao.o58_programa inner join orcelemento on orcelemento.o56_codele = orcdotacao.o58_codele and orcdotacao.o58_anousu = orcelemento.o56_anousu inner join orcprojativ on orcprojativ.o55_anousu = orcdotacao.o58_anousu and orcprojativ.o55_projativ = orcdotacao.o58_projativ inner join orcorgao on orcorgao.o40_anousu = orcdotacao.o58_anousu and orcorgao.o40_orgao = orcdotacao.o58_orgao inner join orcunidade on orcunidade.o41_anousu = orcdotacao.o58_anousu and orcunidade.o41_orgao = orcdotacao.o58_orgao and orcunidade.o41_unidade = orcdotacao.o58_unidade left join empemphist on empemphist.e63_numemp = empempenho.e60_numemp left join emphist on emphist.e40_codhist = empemphist.e63_codhist inner join pctipocompra on pctipocompra.pc50_codcom = empempenho.e60_codcom left join empresto on e60_numemp = e91_numemp and e60_anousu = e91_anousu left join conlancamcompl on c72_codlan = c70_codlan inner join conlancamele on c67_codlan = c70_codlan where c53_tipo in (30) and e60_numemp not in (select e91_numemp from empresto where e91_anousu = {$ano}) and c53_coddoc not in(31,32,33,34,35,36,37,38,1007) and c70_data between '{$di}' and '{$df}' and 1=1 and e60_instit in ({$inst}) AND e60_numemp = {$seqempenho} AND c70_codlan = {$codlan} group by e60_numemp, e60_resumo, e60_destin, e60_codemp, e60_emiss, e60_numcgm, z01_nome, z01_cgccpf, z01_munic, e60_vlremp, e60_vlranu, e60_vlrliq, e63_codhist, e40_descr, e60_vlrpag, e60_anousu, e60_coddot, o58_coddot, o58_orgao, o40_orgao, o40_descr, o58_unidade, o41_descr, o15_codigo, o15_descr, e60_codcom, pc50_descr, c70_data, c70_codlan, c53_tipo, c53_descr, desdobramento, c72_complem, z01_incest, z01_cgccpf, c66_codnota, e91_numemp, c70_anousu, c53_coddoc order by e60_numemp, c70_codlan");
        
    $resultado = array();

    while ($linha = pg_fetch_object($sql)) {
        $resultado[] = $linha;
    }
        
    return $resultado;
}
//677287
public function buscaEmpenhao($coddot){
    $di = $this->dtDataInicial;
    $df = $this->dtDataFinal;

    $sql = pg_query("SELECT c70_codlan, c70_data, c70_valor, c71_coddoc, c53_descr, c80_codord, c75_numemp, c76_numcgm, z01_nome, e69_numero, e69_codnota, c72_complem, c73_coddot, c74_codrec, c70_anousu, c53_tipo, c67_codele from conlancam inner join conlancamdoc on c71_codlan = c70_codlan inner join conhistdoc on c71_coddoc = c53_coddoc left join conlancamord on c70_codlan = c80_codlan left join conlancamemp on c75_codlan = c70_codlan left join conlancamcgm on c70_codlan = c76_codlan left join cgm on z01_numcgm = c76_numcgm left join conlancamnota on c70_codlan = c66_codlan left join empnota on c66_codnota = e69_codnota left join conlancamcompl on c70_codlan = c72_codlan left join conlancamdot on c73_codlan = c70_codlan and c73_anousu = c70_anousu left join conlancamele on c67_codlan = c70_codlan left join conlancamrec on c74_codlan = c70_codlan and c74_anousu = c70_anousu where c73_coddot = {$coddot} AND c70_data between '{$di}' AND '{$df}' AND c71_coddoc = 3");
    $resultado = pg_fetch_all($sql);
    return $resultado[0];
}

public function buscaEmpenhao2($seqempenho){
    $di = $this->dtDataInicial;
    $df = $this->dtDataFinal;
    $sql = pg_query("SELECT c70_codlan, c70_data, e60_coddot, c53_coddoc, c53_descr, case when c127_conlancam is not null then 'Sim' else 'Não' end as dl_Lançamento_Retenção, c70_valor, c82_reduz, c60_descr, c72_complem, e69_numero as dl_Nota_Fiscal, e50_codord, e50_data, nomeinstabrev as dl_Ente from conlancamemp inner join conlancam on c70_codlan = c75_codlan inner join empempenho on c75_numemp = e60_numemp inner join conlancamordem on conlancamordem.c03_codlan = conlancam.c70_codlan left outer join conlancampag on c82_codlan = c70_codlan inner join conlancamdoc on c71_codlan = c70_codlan inner join conhistdoc on c53_coddoc = c71_coddoc left join conlancamcompl on c72_codlan =c70_codlan left join conlancamnota on c66_codlan =c70_codlan left join conlancamord on c80_codlan =c70_codlan left join empnota on c66_codnota = e69_codnota left join conplanoreduz on c61_reduz = conlancampag.c82_reduz and c61_anousu=c70_anousu left join conplano on c60_codcon = conplanoreduz.c61_codcon and c60_anousu=c61_anousu left join pagordem on e50_codord = c80_codord inner join conlancaminstit on c02_codlan = c70_codlan inner join db_config on c02_instit = db_config.codigo left join conlancamretencao on c127_conlancam = c70_codlan where c75_numemp = {$seqempenho} AND c70_data BETWEEN '{$di}' AND '{$df}' AND c53_coddoc IN(5,35,37) order by c75_data, c03_ordem, c75_codlan");
    $resultado = pg_fetch_all($sql);
    return $resultado[0];
}

public function buscaCodord($coddot){
    $di = $this->dtDataInicial;
    $df = $this->dtDataFinal;

    $sql = pg_query("SELECT c70_codlan, c70_data, c70_valor, c71_coddoc, c53_descr, c80_codord, c75_numemp, c76_numcgm, z01_nome, e69_numero, e69_codnota, c72_complem, c73_coddot, c74_codrec, c70_anousu, c53_tipo, c67_codele from conlancam inner join conlancamdoc on c71_codlan = c70_codlan inner join conhistdoc on c71_coddoc = c53_coddoc left join conlancamord on c70_codlan = c80_codlan left join conlancamemp on c75_codlan = c70_codlan left join conlancamcgm on c70_codlan = c76_codlan left join cgm on z01_numcgm = c76_numcgm left join conlancamnota on c70_codlan = c66_codlan left join empnota on c66_codnota = e69_codnota left join conlancamcompl on c70_codlan = c72_codlan left join conlancamdot on c73_codlan = c70_codlan and c73_anousu = c70_anousu left join conlancamele on c67_codlan = c70_codlan left join conlancamrec on c74_codlan = c70_codlan and c74_anousu = c70_anousu where c73_coddot = {$coddot} AND c70_data between '{$di}' AND '{$df}' AND c71_coddoc = 3");
    $resultado = pg_fetch_all($sql);
    
    return $resultado[0]["c80_codord"];
}




public function buscaNotaDoEmpenhao2($seqempenho){
    $di = $this->dtDataInicial;
    $df = $this->dtDataFinal;
    $sql = pg_query("SELECT c70_codlan, c70_data, e60_coddot, c53_coddoc, c53_descr, case when c127_conlancam is not null then 'Sim' else 'Não' end as dl_Lançamento_Retenção, c70_valor, c82_reduz, c60_descr, c72_complem, e69_numero, e50_codord, e50_data, nomeinstabrev as dl_Ente from conlancamemp inner join conlancam on c70_codlan = c75_codlan inner join empempenho on c75_numemp = e60_numemp inner join conlancamordem on conlancamordem.c03_codlan = conlancam.c70_codlan left outer join conlancampag on c82_codlan = c70_codlan inner join conlancamdoc on c71_codlan = c70_codlan inner join conhistdoc on c53_coddoc = c71_coddoc left join conlancamcompl on c72_codlan =c70_codlan left join conlancamnota on c66_codlan =c70_codlan left join conlancamord on c80_codlan =c70_codlan left join empnota on c66_codnota = e69_codnota left join conplanoreduz on c61_reduz = conlancampag.c82_reduz and c61_anousu=c70_anousu left join conplano on c60_codcon = conplanoreduz.c61_codcon and c60_anousu=c61_anousu left join pagordem on e50_codord = c80_codord inner join conlancaminstit on c02_codlan = c70_codlan inner join db_config on c02_instit = db_config.codigo left join conlancamretencao on c127_conlancam = c70_codlan where c75_numemp = {$seqempenho} AND c70_data BETWEEN '{$di}' AND '{$df}' AND c53_coddoc IN(3) order by c75_data, c03_ordem, c75_codlan");
    $resultado = pg_fetch_all($sql);
    return ($resultado[0]["e69_numero"]) ? $resultado[0]["e69_numero"] : false;
}



public function buscaCPFordenador($cgm){
        $sql = pg_query("SELECT z01_cgccpf FROM cgm WHERE z01_numcgm = {$cgm}");
        $resultado = pg_fetch_all($sql);
        return $resultado[0]["z01_cgccpf"];
    }

    public function buscaNF($seqempenho){
        $inst = $this->instit;
        $di = $this->dtDataInicial;
        $df = $this->dtDataFinal;

        $sql = pg_query("SELECT c71_coddoc, o58_orgao, o58_unidade, e60_numemp, e60_codemp, e60_anousu, e69_codnota, e69_anousu, e69_numero, c70_codlan, c70_data, c70_valor, c72_complem, z01_nome, z01_cgccpf as cpf from empempenho inner join empnota on e69_numemp = e60_numemp inner join orcdotacao on o58_anousu = e60_anousu and o58_coddot = e60_coddot inner join pagordemnota on e71_codnota = e69_codnota inner join pagordem on e50_codord = e71_codord inner join conlancamord on c80_codord = e71_codord inner join conlancam on c70_codlan = c80_codlan inner join conlancamdoc on c71_codlan = c70_codlan inner join conlancamcompl on c72_codlan = c70_codlan inner join lancamentoscontabeislog as log on codlan = c70_codlan and tipo_movimento = 1 inner join db_usuacgm on db_usuacgm.id_usuario = log.id_usuario inner join cgm as cgmusu on cgmusu.z01_numcgm = cgmlogin where e60_numemp = {$seqempenho} and c71_coddoc = 5 AND e60_instit = {$inst} and c70_data between '{$di}' and '{$df}' order by e60_numemp asc");
        $resultado = pg_fetch_all($sql);
        return $resultado[0]["e69_numero"];
    }

//==================================================================================================================
    //Busca só os empenhos
    public function retornaEmpenhosPagos(){
        $inst = $this->instit;
        $di = $this->dtDataInicial;
        $df = $this->dtDataFinal;
        $ano = $this->iAnoUsu;

        $sql = pg_query("SELECT DISTINCT e60_numemp, e60_codemp FROM coremp inner join empempenho on e60_numemp = k12_empen and e60_instit = {$inst} inner join orcdotacao on e60_coddot = o58_coddot and e60_anousu = o58_anousu inner join pagordem on e50_codord = k12_codord left join pagordemconta on e50_codord = e49_codord inner join corrente on corrente.k12_id = coremp.k12_id and corrente.k12_data = coremp.k12_data and corrente.k12_autent = coremp.k12_autent inner join cgm on cgm.z01_numcgm = e60_numcgm left join cgm cgmordem on cgmordem.z01_numcgm = e49_numcgm inner join saltes on saltes.k13_conta = corrente.k12_conta left join corgrupocorrente on k105_id = corrente.k12_id and k105_data = corrente.k12_data and k105_autent = corrente.k12_autent left join corgrupotipo on k106_sequencial = k105_corgrupotipo where coremp.k12_data between '{$di}' and '{$df}' AND e60_anousu = {$ano} order by e60_numemp");

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

        $sql = pg_query("SELECT coremp.k12_empen, e60_numemp, e60_codemp, case when e49_numcgm is null then e60_numcgm else e49_numcgm end as e60_numcgm, k12_codord as e50_codord, case when e49_numcgm is null then cgm.z01_nome else cgmordem.z01_nome end as z01_nome, k12_valor, k12_cheque, e60_anousu, coremp.k12_autent, coremp.k12_data, k13_conta, k13_descr, k106_sequencial, e60_coddot from coremp inner join empempenho on e60_numemp = k12_empen and e60_instit = {$inst} inner join orcdotacao on e60_coddot = o58_coddot and e60_anousu = o58_anousu inner join pagordem on e50_codord = k12_codord left join pagordemconta on e50_codord = e49_codord inner join corrente on corrente.k12_id = coremp.k12_id and corrente.k12_data = coremp.k12_data and corrente.k12_autent = coremp.k12_autent inner join cgm on cgm.z01_numcgm = e60_numcgm left join cgm cgmordem on cgmordem.z01_numcgm = e49_numcgm inner join saltes on saltes.k13_conta = corrente.k12_conta left join corgrupocorrente on k105_id = corrente.k12_id and k105_data = corrente.k12_data and k105_autent = corrente.k12_autent left join corgrupotipo on k106_sequencial = k105_corgrupotipo where e60_numemp = {$seqempenho} and coremp.k12_data between '{$di}' and '{$df}' AND e60_anousu = {$ano} AND k12_valor > 0 order by e60_codemp, k12_data, k13_conta");

        $resultado = array();

        while($linha = pg_fetch_object($sql)){
            $resultado[] = $linha;
        }
        return $resultado;
    }

    public function retornaNF($codord){
        $sql = pg_query("SELECT e69_numero, e69_codnota from pagordemnota inner join pagordem on pagordem.e50_codord = pagordemnota.e71_codord inner join empnota on empnota.e69_codnota = pagordemnota.e71_codnota inner join empempenho on empempenho.e60_numemp = pagordem.e50_numemp inner join db_usuarios on db_usuarios.id_usuario = empnota.e69_id_usuario inner join empempenho as a on a.e60_numemp = empnota.e69_numemp where pagordemnota.e71_codord = {$codord}");
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
//=================================================================================================================================    
//=================================================================================================================================    
//=================================================================================================================================    




public function todosOsEmpenhos(){
    $inst = $this->instit;
    $di = $this->dtDataInicial;
    $df = $this->dtDataFinal;
    $ano = $this->iAnoUsu;

    $sql = pg_query("SELECT empempenho.e60_numemp::integer as e60_numemp, e60_resumo, e60_destin, e60_codemp, e60_emiss, e60_numcgm, z01_nome, z01_cgccpf, z01_munic, e60_vlremp, e60_vlranu, e60_vlrliq, e63_codhist, e40_descr, e60_vlrpag, e60_anousu, e60_coddot, o58_coddot, o58_orgao, o40_orgao, o40_descr, o58_unidade, o41_descr, o15_codigo, o15_descr, fc_estruturaldotacao(e60_anousu,e60_coddot) as dl_estrutural, e60_codcom, pc50_descr, sum(c70_valor) as c70_valor, c70_data, c70_codlan, c53_tipo, c53_descr, (select o56_elemento||'-'||o56_descr from orcelemento where o56_codele = c67_codele and o56_anousu = c70_anousu) as desdobramento, c72_complem, z01_incest, z01_cgccpf, c66_codnota, e91_numemp, c70_anousu, c53_coddoc from empempenho inner join conlancamemp on c75_numemp = empempenho.e60_numemp inner join conlancam on c70_codlan = c75_codlan left join conlancamnota on c66_codlan = c70_codlan inner join conlancamdoc on c71_codlan = c70_codlan inner join conhistdoc on c53_coddoc = c71_coddoc inner join cgm on cgm.z01_numcgm = empempenho.e60_numcgm inner join db_config on db_config.codigo = empempenho.e60_instit inner join orcdotacao on orcdotacao.o58_anousu = empempenho.e60_anousu and orcdotacao.o58_coddot = empempenho.e60_coddot and orcdotacao.o58_instit = empempenho.e60_instit inner join emptipo on emptipo.e41_codtipo = empempenho.e60_codtipo inner join db_config as a on a.codigo = orcdotacao.o58_instit INNER JOIN origemcomplementorecurso ON origemcomplementorecurso.o206_numero = empempenho.e60_numemp AND origemcomplementorecurso.o206_origem = 1 INNER JOIN orctiporec ON orctiporec.o15_codigo = origemcomplementorecurso.o206_recurso inner join orcfuncao on orcfuncao.o52_funcao = orcdotacao.o58_funcao inner join orcsubfuncao on orcsubfuncao.o53_subfuncao = orcdotacao.o58_subfuncao inner join orcprograma on orcprograma.o54_anousu = orcdotacao.o58_anousu and orcprograma.o54_programa = orcdotacao.o58_programa inner join orcelemento on orcelemento.o56_codele = orcdotacao.o58_codele and orcdotacao.o58_anousu = orcelemento.o56_anousu inner join orcprojativ on orcprojativ.o55_anousu = orcdotacao.o58_anousu and orcprojativ.o55_projativ = orcdotacao.o58_projativ inner join orcorgao on orcorgao.o40_anousu = orcdotacao.o58_anousu and orcorgao.o40_orgao = orcdotacao.o58_orgao inner join orcunidade on orcunidade.o41_anousu = orcdotacao.o58_anousu and orcunidade.o41_orgao = orcdotacao.o58_orgao and orcunidade.o41_unidade = orcdotacao.o58_unidade left join empemphist on empemphist.e63_numemp = empempenho.e60_numemp left join emphist on emphist.e40_codhist = empemphist.e63_codhist inner join pctipocompra on pctipocompra.pc50_codcom = empempenho.e60_codcom left join empresto on e60_numemp = e91_numemp and e60_anousu = e91_anousu left join conlancamcompl on c72_codlan = c70_codlan inner join conlancamele on c67_codlan = c70_codlan where c53_tipo in (30,31) and e60_numemp not in (select e91_numemp from empresto where e91_anousu = {$ano}) and c53_coddoc = 5 and c70_data between '{$di}' and '{$df}' and 1=1 and e60_instit = {$inst} group by e60_numemp, e60_resumo, e60_destin, e60_codemp, e60_emiss, e60_numcgm, z01_nome, z01_cgccpf, z01_munic, e60_vlremp, e60_vlranu, e60_vlrliq, e63_codhist, e40_descr, e60_vlrpag, e60_anousu, e60_coddot, o58_coddot, o58_orgao, o40_orgao, o40_descr, o58_unidade, o41_descr, o15_codigo, o15_descr, e60_codcom, pc50_descr, c70_data, c70_codlan, c53_tipo, c53_descr, desdobramento, c72_complem, z01_incest, z01_cgccpf, c66_codnota, e91_numemp, c70_anousu, c53_coddoc order by e60_numemp, c70_codlan");
    
    $resultado = array();
    while ($linha = pg_fetch_object($sql)) {
        $resultado[] = $linha;
    }        
    return $resultado;
}

public function todosNotaFiscal($seqempenho, $codlan, $data, $valor){
    $inst = $this->instit;
    $sql = pg_query("SELECT c71_coddoc, o58_orgao, o58_unidade, e60_numemp, e60_codemp, e60_anousu, e69_codnota, e69_anousu, e69_numero, c70_codlan, c70_data, c70_valor, c72_complem, z01_nome, z01_cgccpf as cpf from empempenho inner join empnota on e69_numemp = e60_numemp inner join orcdotacao on o58_anousu = e60_anousu and o58_coddot = e60_coddot inner join pagordemnota on e71_codnota = e69_codnota inner join pagordem on e50_codord = e71_codord inner join conlancamord on c80_codord = e71_codord inner join conlancam on c70_codlan = c80_codlan inner join conlancamdoc on c71_codlan = c70_codlan inner join conlancamcompl on c72_codlan = c70_codlan inner join lancamentoscontabeislog as log on codlan = c70_codlan and tipo_movimento = 1 inner join db_usuacgm on db_usuacgm.id_usuario = log.id_usuario inner join cgm as cgmusu on cgmusu.z01_numcgm = cgmlogin where e60_numemp = {$seqempenho} AND e60_instit = {$inst} and c70_data = '{$data}' AND c70_valor = {$valor} order by e60_numemp asc");
    $resultado = pg_fetch_all($sql);
    return $resultado[0];
}

/*public function buscaTipoRet($data, $seqempenho, $valor, $codord){
    $inst = $this->instit;
    $sql = pg_query("SELECT e21_tiporet, e23_sequencial, empempenho.e60_codemp, empempenho.e60_anousu, orcdotacao.o58_orgao, orcdotacao.o58_unidade, empnota.e69_numero, k12_data, e32_sequencial, e23_valorretencao, cgm.z01_nome, cgm.z01_cgccpf as cnpjcredor, cgmusu.z01_cgccpf, empnota.e69_codnota, empnota.e69_anousu, db89_db_bancos, db89_codagencia, db83_conta, e50_codord, e60_numemp, e69_codnota from retencaoreceitas inner join retencaotiporec on e21_sequencial = e23_retencaotiporec inner join retencaotipocalc on e32_sequencial = e21_retencaotipocalc inner join retencaotiporeccgm on e48_retencaotiporec = e21_sequencial inner join retencaopagordem on e23_retencaopagordem = e20_sequencial inner join retencaoempagemov on e27_retencaoreceitas = e23_sequencial inner join empagemov on e81_codmov = e27_empagemov inner join pagordem on e50_codord = e20_pagordem inner join pagordemnota on e71_codord = e50_codord inner join empempenho on e60_numemp = e50_numemp inner join orcdotacao on o58_anousu = e60_anousu and o58_coddot = e60_coddot inner join empnota on e69_codnota = e71_codnota inner join db_usuacgm on db_usuacgm.id_usuario = e50_id_usuario inner join cgm as cgmusu on cgmlogin = cgmusu.z01_numcgm inner join cgm on cgm.z01_numcgm = empempenho.e60_numcgm left join empagemovslips on k107_retencao = e23_sequencial left join slipempagemovslips on k108_empagemovslips = k107_sequencial left join slip on k17_codigo = k108_slip left join empageformacgm on e28_numcgm = empempenho.e60_numcgm left join empagetipo on e83_codtipo = empageformacgm.e28_empagetipo inner join saltes on saltes.k13_conta = coalesce(slip.k17_credito, e83_conta) inner join conplanocontabancaria on c56_reduz = saltes.k13_reduz and c56_anousu = e60_anousu inner join contabancaria on contabancaria.db83_sequencial = conplanocontabancaria.c56_contabancaria inner join bancoagencia on bancoagencia.db89_sequencial = contabancaria.db83_bancoagencia inner join retencaocorgrupocorrente on e47_retencaoreceita = e23_sequencial inner join corgrupocorrente on k105_sequencial = e47_corgrupocorrente inner join corrente on k105_sequencial = e47_corgrupocorrente and k105_id = k12_id and k105_autent = k12_autent and k105_data = k12_data where e23_ativo is true and e60_instit = {$inst} and k12_estorn is false and k12_data = '{$data}' AND e60_numemp = {$seqempenho} AND e50_codord = {$codord} AND e23_valorretencao = {$valor} order by e60_numemp asc");
    $resultado = pg_fetch_all($sql);
    return $resultado[0];
}*/

public function buscaCodord2($codlan){
    $di = $this->dtDataInicial;
    $df = $this->dtDataFinal;

    $sql = pg_query("SELECT c70_codlan, c70_data, c70_valor, c71_coddoc, c53_descr, c80_codord, c75_numemp, c76_numcgm, z01_nome, e69_numero, e69_codnota, c72_complem, c73_coddot, c74_codrec, c70_anousu, c53_tipo, c67_codele from conlancam inner join conlancamdoc on c71_codlan = c70_codlan inner join conhistdoc on c71_coddoc = c53_coddoc left join conlancamord on c70_codlan = c80_codlan left join conlancamemp on c75_codlan = c70_codlan left join conlancamcgm on c70_codlan = c76_codlan left join cgm on z01_numcgm = c76_numcgm left join conlancamnota on c70_codlan = c66_codlan left join empnota on c66_codnota = e69_codnota left join conlancamcompl on c70_codlan = c72_codlan left join conlancamdot on c73_codlan = c70_codlan and c73_anousu = c70_anousu left join conlancamele on c67_codlan = c70_codlan left join conlancamrec on c74_codlan = c70_codlan and c74_anousu = c70_anousu where c70_codlan = {$codlan}");
    $resultado = pg_fetch_all($sql);
    
    return $resultado[0]["c80_codord"];
}

public function buscaaconta($codlan){
    $sql = pg_query("SELECT e60_numemp, e69_codnota, e69_numero, c70_codlan, c70_data, c70_valor, db90_codban, db89_codagencia, db89_digito, db83_conta, db83_dvconta from empempenho inner join empnota on e69_numemp = e60_numemp inner join pagordemnota on e71_codnota = e69_codnota inner join conlancamemp on c75_numemp = e60_numemp inner join conlancam on c75_codlan = c70_codlan inner join conlancamdoc on c71_codlan = c70_codlan inner join conlancamcompl on c72_codlan = c70_codlan inner join conlancamord on c80_codlan = c70_codlan and c80_codord = e71_codord inner join lancamentoscontabeislog as log on codlan = c70_codlan and tipo_movimento = 1 inner join db_usuacgm on db_usuacgm.id_usuario = log.id_usuario inner join cgm as cgmusu on cgmusu.z01_numcgm = cgmlogin inner join conlancampag on c82_codlan = c70_codlan inner join contabilidade.conplanoreduz on c61_reduz = c82_reduz and c61_anousu = c82_anousu inner join contabilidade.conplanocontabancaria on c56_reduz = c61_reduz and c56_anousu = c61_anousu inner join configuracoes.contabancaria on c56_contabancaria = db83_sequencial inner join configuracoes.bancoagencia on db89_sequencial = db83_bancoagencia inner join configuracoes.db_bancos on db90_codban = db89_db_bancos where c70_codlan = {$codlan}");
    $resultado = pg_fetch_all($sql);
    return $resultado[0];
}

public function buscaTipoRet($data, $seqempenho, $valor, $codord){
    $inst = $this->instit;
    $sql = pg_query("SELECT e21_tiporet, e23_sequencial, empempenho.e60_codemp, empempenho.e60_anousu, orcdotacao.o58_orgao, orcdotacao.o58_unidade, empnota.e69_numero, k12_data, e32_sequencial, e23_valorretencao, cgm.z01_nome, cgm.z01_cgccpf as cnpjcredor, cgmusu.z01_cgccpf, empnota.e69_codnota, empnota.e69_anousu, db89_db_bancos, db89_codagencia, db83_conta, e50_codord, e60_numemp, e69_codnota from retencaoreceitas inner join retencaotiporec on e21_sequencial = e23_retencaotiporec inner join retencaotipocalc on e32_sequencial = e21_retencaotipocalc inner join retencaotiporeccgm on e48_retencaotiporec = e21_sequencial inner join retencaopagordem on e23_retencaopagordem = e20_sequencial inner join retencaoempagemov on e27_retencaoreceitas = e23_sequencial inner join empagemov on e81_codmov = e27_empagemov inner join pagordem on e50_codord = e20_pagordem inner join pagordemnota on e71_codord = e50_codord inner join empempenho on e60_numemp = e50_numemp inner join orcdotacao on o58_anousu = e60_anousu and o58_coddot = e60_coddot inner join empnota on e69_codnota = e71_codnota inner join db_usuacgm on db_usuacgm.id_usuario = e50_id_usuario inner join cgm as cgmusu on cgmlogin = cgmusu.z01_numcgm inner join cgm on cgm.z01_numcgm = empempenho.e60_numcgm left join empagemovslips on k107_retencao = e23_sequencial left join slipempagemovslips on k108_empagemovslips = k107_sequencial left join slip on k17_codigo = k108_slip left join empageformacgm on e28_numcgm = empempenho.e60_numcgm left join empagetipo on e83_codtipo = empageformacgm.e28_empagetipo inner join saltes on saltes.k13_conta = coalesce(slip.k17_credito, e83_conta) inner join conplanocontabancaria on c56_reduz = saltes.k13_reduz and c56_anousu = e60_anousu inner join contabancaria on contabancaria.db83_sequencial = conplanocontabancaria.c56_contabancaria inner join bancoagencia on bancoagencia.db89_sequencial = contabancaria.db83_bancoagencia inner join retencaocorgrupocorrente on e47_retencaoreceita = e23_sequencial inner join corgrupocorrente on k105_sequencial = e47_corgrupocorrente inner join corrente on k105_sequencial = e47_corgrupocorrente and k105_id = k12_id and k105_autent = k12_autent and k105_data = k12_data where e23_ativo is true and e60_instit = {$inst} and k12_estorn is false and k12_data = '{$data}' AND e60_numemp = {$seqempenho} AND e50_codord = {$codord} AND e23_valorretencao = {$valor} order by e60_numemp asc");
    $resultado = pg_fetch_all($sql);
    return $resultado[0];
}

public function buscaContaVaiz($seqempenho){
    $inst = $this->instit;
    $di = $this->dtDataInicial;
    $df = $this->dtDataFinal;

    $sql = pg_query("SELECT e21_tiporet, e23_sequencial, empempenho.e60_codemp, empempenho.e60_anousu, orcdotacao.o58_orgao, orcdotacao.o58_unidade, empnota.e69_numero, k12_data, e32_sequencial, e23_valorretencao, cgm.z01_nome, cgm.z01_cgccpf as cnpjcredor, cgmusu.z01_cgccpf, empnota.e69_codnota, empnota.e69_anousu, db89_db_bancos, db89_codagencia, db83_conta, e50_codord, e60_numemp, e69_codnota from retencaoreceitas inner join retencaotiporec on e21_sequencial = e23_retencaotiporec inner join retencaotipocalc on e32_sequencial = e21_retencaotipocalc inner join retencaotiporeccgm on e48_retencaotiporec = e21_sequencial inner join retencaopagordem on e23_retencaopagordem = e20_sequencial inner join retencaoempagemov on e27_retencaoreceitas = e23_sequencial inner join empagemov on e81_codmov = e27_empagemov inner join pagordem on e50_codord = e20_pagordem inner join pagordemnota on e71_codord = e50_codord inner join empempenho on e60_numemp = e50_numemp inner join orcdotacao on o58_anousu = e60_anousu and o58_coddot = e60_coddot inner join empnota on e69_codnota = e71_codnota inner join db_usuacgm on db_usuacgm.id_usuario = e50_id_usuario inner join cgm as cgmusu on cgmlogin = cgmusu.z01_numcgm inner join cgm on cgm.z01_numcgm = empempenho.e60_numcgm left join empagemovslips on k107_retencao = e23_sequencial left join slipempagemovslips on k108_empagemovslips = k107_sequencial left join slip on k17_codigo = k108_slip left join empageformacgm on e28_numcgm = empempenho.e60_numcgm left join empagetipo on e83_codtipo = empageformacgm.e28_empagetipo inner join saltes on saltes.k13_conta = coalesce(slip.k17_credito, e83_conta) inner join conplanocontabancaria on c56_reduz = saltes.k13_reduz and c56_anousu = e60_anousu inner join contabancaria on contabancaria.db83_sequencial = conplanocontabancaria.c56_contabancaria inner join bancoagencia on bancoagencia.db89_sequencial = contabancaria.db83_bancoagencia inner join retencaocorgrupocorrente on e47_retencaoreceita = e23_sequencial inner join corgrupocorrente on k105_sequencial = e47_corgrupocorrente inner join corrente on k105_sequencial = e47_corgrupocorrente and k105_id = k12_id and k105_autent = k12_autent and k105_data = k12_data where e23_ativo is true and e60_instit = {$inst} and k12_estorn is false and k12_data between '{$di}' AND '{$df}' AND e60_numemp = {$seqempenho} AND db89_db_bancos is not null AND db89_codagencia is not null AND db83_conta is not null order by e60_numemp asc LIMIT 1");
    $resultado = pg_fetch_all($sql);
    return $resultado[0];
}


//====================================================================================================================================
//====================================================================================================================================
//====================================================================================================================================

    public function gerarDados(){   
        $empenhos = $this->todosOsEmpenhos();

        if(count($empenhos) == 0){throw new \Exception(sprintf('Não foi encontrado nenhum registro para o arquivo da Remessa de %s', 'Pagamento de Empenho'));}
        
        $ugs = $this->pegaCgmOrdenador();
        $ugs = $this->buscaCPFordenador($ugs);
        
         
        $obj = new stdClass();
        $obj->PagamentosDeEmpenho = [];
        
        $ix = 1;
        $guarda = array();
        $guardaliquidacao = array();
        $indice = 0;

        
        
        foreach ($empenhos as $empenho) {
            //if($empenho->e60_codemp != '300'){continue;}
            
            $codord = $this->buscaCodord2($empenho->c70_codlan);
            $nfdados = $this->todosNotaFiscal($empenho->e60_numemp, $empenho->c70_codlan, $empenho->c70_data, $empenho->c70_valor);
            $notafiscal = $nfdados["e69_codnota"];
            $numeronota = $nfdados["e69_numero"];
            if(empty($numeronota)){
                $numeronota = "S/N";
            }
            $dadosretencao = $this->buscaTipoRet($empenho->c70_data, $empenho->e60_numemp,$empenho->c70_valor, $codord);
            
            //$chave = $empenho->e60_numemp . $notafiscal . $empenho->c70_data . $codord;
            $chave = $empenho->e60_numemp . $notafiscal . $empenho->c70_data;

            //Buscar as OPs da mesma nota de pagamento
            //op > nota de pagamento
            
            if(in_array($chave, $guarda)){
                $guardaliquidacao[$indice]["e60_numemp"] = $empenho->e60_numemp;
                $guardaliquidacao[$indice]["c70_codlan"] = $empenho->c70_codlan;
                $guardaliquidacao[$indice]["c70_data"] = $empenho->c70_data;
                $guardaliquidacao[$indice]["c70_valor"] = $empenho->c70_valor;
                $guardaliquidacao[$indice]["c70_anousu"] = $empenho->c70_anousu;
                $guardaliquidacao[$indice]["c70_codlan"] = $empenho->c70_codlan;
                $guardaliquidacao[$indice]["o58_orgao"] = $empenho->o58_orgao;
                $guardaliquidacao[$indice]["o58_unidade"] = $empenho->o58_unidade;
                $guardaliquidacao[$indice]["c53_coddoc"] = $empenho->c53_coddoc;
                $guardaliquidacao[$indice]["xcodord"] = $codord;
                $guardaliquidacao[$indice]["nf"] = $notafiscal;
                $guardaliquidacao[$indice]["nnf"] = $numeronota;
                $guardaliquidacao[$indice]["banco"] = $dadosretencao["db89_db_bancos"];
                $guardaliquidacao[$indice]["agencia"] = $dadosretencao["db89_codagencia"];
                $guardaliquidacao[$indice]["conta"] = $dadosretencao["db83_conta"];
                $indice++;
                continue;
            }else{
                $guardaliquidacao[$indice]["e60_numemp"] = $empenho->e60_numemp;
                $guardaliquidacao[$indice]["c70_codlan"] = $empenho->c70_codlan;
                $guardaliquidacao[$indice]["c70_data"] = $empenho->c70_data;
                $guardaliquidacao[$indice]["c70_valor"] = $empenho->c70_valor;
                $guardaliquidacao[$indice]["c70_anousu"] = $empenho->c70_anousu;
                $guardaliquidacao[$indice]["c70_codlan"] = $empenho->c70_codlan;
                $guardaliquidacao[$indice]["o58_orgao"] = $empenho->o58_orgao;
                $guardaliquidacao[$indice]["o58_unidade"] = $empenho->o58_unidade;
                $guardaliquidacao[$indice]["c53_coddoc"] = $empenho->c53_coddoc;
                $guardaliquidacao[$indice]["xcodord"] = $codord;
                $guardaliquidacao[$indice]["nf"] = $notafiscal;
                $guardaliquidacao[$indice]["nnf"] = $numeronota;
                $guardaliquidacao[$indice]["banco"] = $dadosretencao["db89_db_bancos"];
                $guardaliquidacao[$indice]["agencia"] = $dadosretencao["db89_codagencia"];
                $guardaliquidacao[$indice]["conta"] = $dadosretencao["db83_conta"];
                $indice++;
            }
            array_push($guarda, $chave);
        }
        
        //$this->testa($guardaliquidacao); die("Confere");
        $guarda2 = array();
        foreach ($empenhos as $empenho) {
            //if($empenho->e60_codemp != '300'){continue;}

            $codord = $this->buscaCodord2($empenho->c70_codlan);
            $nfdados = $this->todosNotaFiscal($empenho->e60_numemp, $empenho->c70_codlan, $empenho->c70_data, $empenho->c70_valor);
            $notafiscal = $nfdados["e69_codnota"];
            $numeronota = $nfdados["e69_numero"];
            if(empty($numeronota)){
                $numeronota = "S/N";
            }
            
            //$chave2 = $empenho->e60_numemp . $notafiscal . $empenho->c70_data . $codord;
            $chave2 = $empenho->e60_numemp . $notafiscal . $empenho->c70_data;
            if(in_array($chave2, $guarda2)){
                continue;
            }
            array_push($guarda2, $chave2);
            
            $xpagamentos = array();
            foreach ($guardaliquidacao as $gl){                
                //$chaveinterna = $gl["e60_numemp"] . $gl["nf"] . $gl["c70_data"] . $gl["xcodord"];
                $chaveinterna = $gl["e60_numemp"] . $gl["nf"] . $gl["c70_data"];
                
                if($chave2 == $chaveinterna){
                    array_push($xpagamentos, $gl);
                }
            }
            
            $totalempenho = 0;//$empenho->c70_valor;

            foreach ($xpagamentos as $pagamento) {
                $totalempenho += $pagamento["c70_valor"];
            }

            $data = (object)[
                "Identificador" => $ix,
                "CodigoUnidadeGestora" => $this->sCodigoTribunal,
                "Competencia" => $this->competencia,
                "NumeroEmpenho" => $empenho->e60_codemp,
                "Ano" => $empenho->e60_anousu,
                //"NumeroNotaPagamentoEmpenho" => $numeronota
                "NumeroNotaPagamentoEmpenho" => $notafiscal,
                "DataPagamento" => $empenho->c70_data,
                "ValorPago" => $totalempenho,
                "CpfResponsavel" => $ugs,
                "LiquidacoesPagamento" => []
            ];

            $xpagamentos2 = array();
            foreach ($xpagamentos as $pagamento){
                $codord = $this->buscaCodord2($pagamento["c70_codlan"]);
                $dadosretencao = $this->buscaTipoRet($pagamento["c70_data"], $pagamento["e60_numemp"],$pagamento["c70_valor"], $codord);

                $xpagamentos2[$indice]["Identificador"] = $pagamento["c70_codlan"]; //$indice;
                $xpagamentos2[$indice]['NumeroLiquidacaoEmpenho'] = $codord;//$pagamento->e50_codord;//$pagamento->c70_codlan;
                $xpagamentos2[$indice]['AnoLiquidacaoEmpenho'] = $pagamento["c70_anousu"];
                $xpagamentos2[$indice]['CodigoOrgao'] = $pagamento["o58_orgao"];
                $xpagamentos2[$indice]['CodigoUnidadeOrcamentaria'] = $pagamento["o58_unidade"];
                $xpagamentos2[$indice]['ValorPagamentoLiquidacao'] = $pagamento["c70_valor"];
                
                $oiaaconta = $this->buscaaconta($pagamento["c70_codlan"]);

                if(empty($oiaaconta)){
                    if(empty($dadosretencao["db89_db_bancos"])){
                        $contavazia = $this->buscaContaVaiz($pagamento["e60_numemp"]);
                        $dadosretencao["db89_db_bancos"] = $contavazia["db89_db_bancos"];
                        $dadosretencao["db89_codagencia"] = $contavazia["db89_codagencia"];
                        $dadosretencao["db83_conta"] = $contavazia["db83_conta"];
                    }
                    $xconta = (object)[
                    'Identificador' => $pagamento["c70_codlan"], //$indice,
                    'ValorContaPagadora' => $pagamento["c70_valor"],
                    'Banco' => $dadosretencao["db89_db_bancos"],//$oiaaconta["db90_codban"],
                    'Agencia' => $dadosretencao["db89_codagencia"],//$oiaaconta["db89_codagencia"],
                    'ContaBancaria' => $dadosretencao["db83_conta"]//$oiaaconta["db83_conta"]
                ];

                }else{
                    $xconta = (object)[
                    'Identificador' => $pagamento["c70_codlan"], //$indice,
                    'ValorContaPagadora' => $pagamento["c70_valor"],
                    'Banco' => $oiaaconta["db90_codban"],
                    'Agencia' => $oiaaconta["db89_codagencia"],
                    'ContaBancaria' => $oiaaconta["db83_conta"]
                ];

                }
                
                /*
                $xconta = (object)[
                    'Identificador' => $pagamento["c70_codlan"], //$indice,
                    'ValorContaPagadora' => $pagamento["c70_valor"],
                    'Banco' => $dadosretencao["db89_db_bancos"],//$oiaaconta["db90_codban"],
                    'Agencia' => $dadosretencao["db89_codagencia"],//$oiaaconta["db89_codagencia"],
                    'ContaBancaria' => $dadosretencao["db83_conta"]//$oiaaconta["db83_conta"]
                ];
                */

                $xpagamentos2[$indice]["ContasPagadoras"] = (object)['ContaPagadora' => $xconta];
                $indice++;
            }

            foreach($xpagamentos2 as $liquidacao){
                $data->LiquidacoesPagamento[] = (object) ['LiquidacaoPagamento' => $liquidacao];
            }
            
            $obj->PagamentosDeEmpenho[] = (object)['PagamentoDeEmpenho' => $data];
            $ix++;
        }// fim do foreach
        
        $this->aDados = $obj;
        
    }

    private function liquidacao2($codnota, $pagamento, $codlan){
        $campos = [
            'o58_orgao',
            'o58_unidade',
            'e69_codnota',
            'e69_anousu',
            'c70_codlan',
            'c70_data',
            'c70_valor',
            'c70_anousu',
        ];
        return DB::table('empempenho')
            ->select($campos)
            ->join('empnota', 'e69_numemp', 'e60_numemp')
            ->join('orcdotacao', function (JoinClause $join) {
                $join->on('o58_anousu', 'e60_anousu')
                    ->on('o58_coddot', 'e60_coddot');
            })
            ->join('conlancamemp', 'c75_numemp', 'e60_numemp')
            ->join('conlancam', 'c75_codlan', 'c70_codlan')
            ->join('conlancamdoc', 'c71_codlan', 'c70_codlan')
            ->join('conlancamcompl', 'c72_codlan', 'c70_codlan')
            ->join('conlancamnota', function (JoinClause $join) {
                $join->on('c66_codlan', 'c70_codlan')
                    ->on('c66_codnota', 'e69_codnota');
            })
            ->join('lancamentoscontabeislog AS log', function (JoinClause $join) {
                $join->on('codlan', 'c70_codlan')
                    ->where('tipo_movimento', 1);
            })
            ->join('db_usuacgm', 'db_usuacgm.id_usuario', 'log.id_usuario')
            ->join('cgm AS cgmusu', 'cgmusu.z01_numcgm', 'cgmlogin')
            ->whereIn('c71_coddoc', [3, 23, 84, 202, 204, 206, 412, 306, 310, 502, 506])
            ->where('e69_codnota', $codnota)
            ->where('c70_codlan', $codlan)
            ->orderBy('e60_numemp')
            //$sql_with_bindings = str_replace_array('?', $empenhos->getBindings(), $empenhos->toSql());
            //$sql_with_bindings = str_replace("\"", "", $sql_with_bindings);
            //var_dump($sql_with_bindings); die("confere");
            ->get()
            ->map(function ($nota) use ($pagamento) {
                return (object)[
                    "Identificador" => $nota->c70_codlan,
                    'NumeroLiquidacaoEmpenho' => $nota->e69_codnota,
                    'AnoLiquidacaoEmpenho' => $nota->c70_anousu,
                    'CodigoOrgao' => $nota->o58_orgao,
                    'CodigoUnidadeOrcamentaria' => $nota->o58_unidade,
                    'ValorPagamentoLiquidacao' => $nota->c70_valor,
                    'ContasPagadoras' => [
                        (object)['ContaPagadora' => $pagamento]
                    ]
                ];
            })
            ->toArray();
    }

    
}

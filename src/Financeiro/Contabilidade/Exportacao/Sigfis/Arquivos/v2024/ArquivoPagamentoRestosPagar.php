<?php

namespace ECidade\Financeiro\Contabilidade\Exportacao\Sigfis\Arquivos\v2024;

use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Database\Query\JoinClause;
use stdClass;

class ArquivoPagamentoRestosPagar extends ArquivoBase
{
    protected $sNomeArquivo = 'PagamentoRestosPagar';

    public function testa($var){
        echo "<pre>";
        print_r($var);
        echo "</pre>";
    }

    public function buscaCPFordenador($cgm){
        $sql = pg_query("SELECT z01_cgccpf FROM cgm WHERE z01_numcgm = {$cgm}");
        $resultado = pg_fetch_all($sql);
        return $resultado[0]["z01_cgccpf"];
    }

    public function buscaPags($empenho, $lancamento){
        $sql = pg_query("SELECT c70_codlan as Identificador, e60_anousu as AnoEmpenho, e60_codemp as NumeroEmpenho, e69_codnota as NumeroLiquidacaoRestosPagar, c70_anousu as AnoLiquidacaoRestosPagar, o58_orgao AS CodigoOrgao, o58_unidade as CodigoUnidadeOrcamentaria, c70_valor AS ValorPagamentoLiquidacao from conlancamemp inner join conlancam on c70_codlan = c75_codlan inner join empempenho on c75_numemp = e60_numemp inner join conlancamordem on conlancamordem.c03_codlan = conlancam.c70_codlan left outer join conlancampag on c82_codlan = c70_codlan inner join conlancamdoc on c71_codlan = c70_codlan inner join conhistdoc on c53_coddoc = c71_coddoc left join conlancamcompl on c72_codlan =c70_codlan left join conlancamnota on c66_codlan =c70_codlan left join conlancamord on c80_codlan =c70_codlan left join empnota on c66_codnota = e69_codnota left join conplanoreduz on c61_reduz = conlancampag.c82_reduz and c61_anousu=c70_anousu left join conplano on c60_codcon = conplanoreduz.c61_codcon and c60_anousu=c61_anousu left join pagordem on e50_codord = c80_codord inner join conlancaminstit on c02_codlan = c70_codlan INNER JOIN orcdotacao ON o58_anousu = e60_anousu AND o58_coddot = e60_coddot inner join db_config on c02_instit = db_config.codigo left join conlancamretencao on c127_conlancam = c70_codlan where c75_numemp = {$empenho} AND c70_codlan = {$lancamento} AND c53_coddoc = 35 order by c75_data, c03_ordem, c75_codlan");

        $resultado = pg_fetch_all($sql);
        return $resultado;
    }

    public function pegaValor($codnota, $valor, $codlan){
        $sql = pg_query("SELECT c70_valor, c70_codlan, c70_data FROM empempenho inner join empnota on e69_numemp = e60_numemp inner join orcdotacao on o58_anousu = e60_anousu and o58_coddot = e60_coddot inner join conlancamemp on c75_numemp = e60_numemp inner join conlancam on c75_codlan = c70_codlan inner join conlancamdoc on c71_codlan = c70_codlan inner join conlancamcompl on c72_codlan = c70_codlan inner join lancamentoscontabeislog as log on codlan = c70_codlan and tipo_movimento = 1 inner join db_usuacgm on db_usuacgm.id_usuario = log.id_usuario inner join cgm as cgmusu on cgmusu.z01_numcgm = cgmlogin where c71_coddoc in (35, 37) and e69_codnota = {$codnota} AND c70_valor = {$valor} AND c70_codlan = {$codlan}");
        $resultado = pg_fetch_all($sql);
        return $resultado;
    }

    public function buscaPorNota($numnota){
        $inst = $this->instit;
        $di = $this->dtDataInicial;
        $df = $this->dtDataFinal; 
        
        $sql = pg_query("SELECT DISTINCT e60_numemp, e60_codemp, e60_anousu, e69_codnota, e69_numero, c70_codlan, c70_data, c70_valor, c70_anousu, c72_complem, e69_anousu, z01_nome, z01_cgccpf, db90_codban, db89_codagencia, db89_digito, db83_conta, db83_dvconta, o58_orgao, c80_codord, c80_data from empresto inner join empempenho on e60_numemp = e91_numemp inner join empnota on e69_numemp = e60_numemp inner join orcdotacao on o58_anousu = e60_anousu and o58_coddot = e60_coddot inner join pagordemnota on e71_codnota = e69_codnota inner join conlancamemp on c75_numemp = e60_numemp inner join conlancam on c75_codlan = c70_codlan inner join conlancamdoc on c71_codlan = c70_codlan inner join conlancamcompl on c72_codlan = c70_codlan inner join conlancamord on c80_codlan = c70_codlan and c80_codord = e71_codord inner join lancamentoscontabeislog as log on codlan = c70_codlan and tipo_movimento = 1 inner join db_usuacgm on db_usuacgm.id_usuario = log.id_usuario inner join cgm as cgmusu on cgmusu.z01_numcgm = cgmlogin inner join conlancampag on c82_codlan = c70_codlan inner join contabilidade.conplanoreduz on c61_reduz = c82_reduz and c61_anousu = c82_anousu inner join contabilidade.conplanocontabancaria on c56_reduz = c61_reduz and c56_anousu = c61_anousu inner join configuracoes.contabancaria on c56_contabancaria = db83_sequencial inner join configuracoes.bancoagencia on db89_sequencial = db83_bancoagencia inner join configuracoes.db_bancos on db90_codban = db89_db_bancos where c71_coddoc in (35, 37) and e60_instit = {$inst} and c70_data between '{$di}' and '{$df}' AND e69_codnota = '{$numnota}' order by e69_numero, e60_numemp asc");
        $resultado = pg_fetch_all($sql);
        return $resultado;
    }


    public function voltaLiquidacaoes(){
        $inst = $this->instit;
        $di = $this->dtDataInicial;
        $df = $this->dtDataFinal;
        $ano = $this->iAnoUsu;
        

        $sql = pg_query("SELECT empempenho.e60_numemp::integer as e60_numemp, e60_resumo, e60_destin, e60_codemp, e60_emiss, e60_numcgm, z01_nome, z01_cgccpf, z01_munic, e60_vlremp, e60_vlranu, e60_vlrliq, e63_codhist, e40_descr, e60_vlrpag, e60_anousu, e60_coddot, o58_coddot, o58_orgao, o40_orgao, o40_descr, o58_unidade, o41_descr, o15_codigo, o15_descr, fc_estruturaldotacao(e60_anousu,e60_coddot) as dl_estrutural, e60_codcom, pc50_descr, sum(c70_valor) as c70_valor, c70_data, c70_anousu, c70_codlan, c53_tipo, c53_descr, (select o56_elemento||'-'||o56_descr from orcelemento where o56_codele = c67_codele and o56_anousu = c70_anousu) as desdobramento, c72_complem, z01_incest, z01_cgccpf, c66_codnota, e91_numemp from empempenho inner join conlancamemp on c75_numemp = empempenho.e60_numemp inner join conlancam on c70_codlan = c75_codlan left join conlancamnota on c66_codlan = c70_codlan inner join conlancamdoc on c71_codlan = c70_codlan inner join conhistdoc on c53_coddoc = c71_coddoc inner join cgm on cgm.z01_numcgm = empempenho.e60_numcgm inner join db_config on db_config.codigo = empempenho.e60_instit inner join orcdotacao on orcdotacao.o58_anousu = empempenho.e60_anousu and orcdotacao.o58_coddot = empempenho.e60_coddot and orcdotacao.o58_instit = empempenho.e60_instit inner join emptipo on emptipo.e41_codtipo = empempenho.e60_codtipo inner join db_config as a on a.codigo = orcdotacao.o58_instit INNER JOIN origemcomplementorecurso ON origemcomplementorecurso.o206_numero = empempenho.e60_numemp AND origemcomplementorecurso.o206_origem = 1 INNER JOIN orctiporec ON orctiporec.o15_codigo = origemcomplementorecurso.o206_recurso inner join orcfuncao on orcfuncao.o52_funcao = orcdotacao.o58_funcao inner join orcsubfuncao on orcsubfuncao.o53_subfuncao = orcdotacao.o58_subfuncao inner join orcprograma on orcprograma.o54_anousu = orcdotacao.o58_anousu and orcprograma.o54_programa = orcdotacao.o58_programa inner join orcelemento on orcelemento.o56_codele = orcdotacao.o58_codele and orcdotacao.o58_anousu = orcelemento.o56_anousu inner join orcprojativ on orcprojativ.o55_anousu = orcdotacao.o58_anousu and orcprojativ.o55_projativ = orcdotacao.o58_projativ inner join orcorgao on orcorgao.o40_anousu = orcdotacao.o58_anousu and orcorgao.o40_orgao = orcdotacao.o58_orgao inner join orcunidade on orcunidade.o41_anousu = orcdotacao.o58_anousu and orcunidade.o41_orgao = orcdotacao.o58_orgao and orcunidade.o41_unidade = orcdotacao.o58_unidade left join empemphist on empemphist.e63_numemp = empempenho.e60_numemp left join emphist on emphist.e40_codhist = empemphist.e63_codhist inner join pctipocompra on pctipocompra.pc50_codcom = empempenho.e60_codcom left join empresto on e60_numemp = e91_numemp and e60_anousu = e91_anousu left join conlancamcompl on c72_codlan = c70_codlan inner join conlancamele on c67_codlan = c70_codlan where c53_coddoc in(35, 37) and e60_anousu < $ano and c70_data between '{$di}' and '{$df}' and 1=1 and e60_instit in ({$inst}) group by e60_numemp, e60_resumo, e60_destin, e60_codemp, e60_emiss, e60_numcgm, z01_nome, z01_cgccpf, z01_munic, e60_vlremp, e60_vlranu, e60_vlrliq, e63_codhist, e40_descr, e60_vlrpag, e60_anousu, e60_coddot, o58_coddot, o58_orgao, o40_orgao, o40_descr, o58_unidade, o41_descr, o15_codigo, o15_descr, e60_codcom, pc50_descr, c70_data, c70_anousu,c70_codlan, c53_tipo, c53_descr, desdobramento, c72_complem, z01_incest, z01_cgccpf, c66_codnota, e91_numemp order by e60_numemp, c70_codlan");
        $resultado = array(); //c53_coddoc in(35, 37, 36, 38

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
        

        $sql = pg_query("SELECT empempenho.e60_numemp::integer as e60_numemp, e60_resumo, e60_destin, e60_codemp, e60_emiss, e60_numcgm, z01_nome, z01_cgccpf, z01_munic, e60_vlremp, e60_vlranu, e60_vlrliq, e63_codhist, e40_descr, e60_vlrpag, e60_anousu, e60_coddot, o58_coddot, o58_orgao, o40_orgao, o40_descr, o58_unidade, o41_descr, o15_codigo, o15_descr, fc_estruturaldotacao(e60_anousu,e60_coddot) as dl_estrutural, e60_codcom, pc50_descr, sum(c70_valor) as c70_valor, c70_data, c70_anousu, c70_codlan, c53_tipo, c53_descr, (select o56_elemento||'-'||o56_descr from orcelemento where o56_codele = c67_codele and o56_anousu = c70_anousu) as desdobramento, c72_complem, z01_incest, z01_cgccpf, c66_codnota, e91_numemp from empempenho inner join conlancamemp on c75_numemp = empempenho.e60_numemp inner join conlancam on c70_codlan = c75_codlan left join conlancamnota on c66_codlan = c70_codlan inner join conlancamdoc on c71_codlan = c70_codlan inner join conhistdoc on c53_coddoc = c71_coddoc inner join cgm on cgm.z01_numcgm = empempenho.e60_numcgm inner join db_config on db_config.codigo = empempenho.e60_instit inner join orcdotacao on orcdotacao.o58_anousu = empempenho.e60_anousu and orcdotacao.o58_coddot = empempenho.e60_coddot and orcdotacao.o58_instit = empempenho.e60_instit inner join emptipo on emptipo.e41_codtipo = empempenho.e60_codtipo inner join db_config as a on a.codigo = orcdotacao.o58_instit INNER JOIN origemcomplementorecurso ON origemcomplementorecurso.o206_numero = empempenho.e60_numemp AND origemcomplementorecurso.o206_origem = 1 INNER JOIN orctiporec ON orctiporec.o15_codigo = origemcomplementorecurso.o206_recurso inner join orcfuncao on orcfuncao.o52_funcao = orcdotacao.o58_funcao inner join orcsubfuncao on orcsubfuncao.o53_subfuncao = orcdotacao.o58_subfuncao inner join orcprograma on orcprograma.o54_anousu = orcdotacao.o58_anousu and orcprograma.o54_programa = orcdotacao.o58_programa inner join orcelemento on orcelemento.o56_codele = orcdotacao.o58_codele and orcdotacao.o58_anousu = orcelemento.o56_anousu inner join orcprojativ on orcprojativ.o55_anousu = orcdotacao.o58_anousu and orcprojativ.o55_projativ = orcdotacao.o58_projativ inner join orcorgao on orcorgao.o40_anousu = orcdotacao.o58_anousu and orcorgao.o40_orgao = orcdotacao.o58_orgao inner join orcunidade on orcunidade.o41_anousu = orcdotacao.o58_anousu and orcunidade.o41_orgao = orcdotacao.o58_orgao and orcunidade.o41_unidade = orcdotacao.o58_unidade left join empemphist on empemphist.e63_numemp = empempenho.e60_numemp left join emphist on emphist.e40_codhist = empemphist.e63_codhist inner join pctipocompra on pctipocompra.pc50_codcom = empempenho.e60_codcom left join empresto on e60_numemp = e91_numemp and e60_anousu = e91_anousu left join conlancamcompl on c72_codlan = c70_codlan inner join conlancamele on c67_codlan = c70_codlan where c53_coddoc in(35, 37) and e60_anousu < $ano and c70_data between '{$di}' and '{$df}' and 1=1 and e60_instit in ({$inst}) AND e60_numemp = {$seqempenho} group by e60_numemp, e60_resumo, e60_destin, e60_codemp, e60_emiss, e60_numcgm, z01_nome, z01_cgccpf, z01_munic, e60_vlremp, e60_vlranu, e60_vlrliq, e63_codhist, e40_descr, e60_vlrpag, e60_anousu, e60_coddot, o58_coddot, o58_orgao, o40_orgao, o40_descr, o58_unidade, o41_descr, o15_codigo, o15_descr, e60_codcom, pc50_descr, c70_data, c70_anousu,c70_codlan, c53_tipo, c53_descr, desdobramento, c72_complem, z01_incest, z01_cgccpf, c66_codnota, e91_numemp order by e60_numemp, c70_codlan");
        $resultado = array();
        //c53_coddoc in(35, 37, 36, 38)

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

    public function buscaaconta($codlan){
    $sql = pg_query("SELECT e60_numemp, e69_codnota, e69_numero, c70_codlan, c70_data, c70_valor, db90_codban, db89_codagencia, db89_digito, db83_conta, db83_dvconta from empempenho inner join empnota on e69_numemp = e60_numemp inner join pagordemnota on e71_codnota = e69_codnota inner join conlancamemp on c75_numemp = e60_numemp inner join conlancam on c75_codlan = c70_codlan inner join conlancamdoc on c71_codlan = c70_codlan inner join conlancamcompl on c72_codlan = c70_codlan inner join conlancamord on c80_codlan = c70_codlan and c80_codord = e71_codord inner join lancamentoscontabeislog as log on codlan = c70_codlan and tipo_movimento = 1 inner join db_usuacgm on db_usuacgm.id_usuario = log.id_usuario inner join cgm as cgmusu on cgmusu.z01_numcgm = cgmlogin inner join conlancampag on c82_codlan = c70_codlan inner join contabilidade.conplanoreduz on c61_reduz = c82_reduz and c61_anousu = c82_anousu inner join contabilidade.conplanocontabancaria on c56_reduz = c61_reduz and c56_anousu = c61_anousu inner join configuracoes.contabancaria on c56_contabancaria = db83_sequencial inner join configuracoes.bancoagencia on db89_sequencial = db83_bancoagencia inner join configuracoes.db_bancos on db90_codban = db89_db_bancos where c70_codlan = {$codlan}");
    $resultado = pg_fetch_all($sql);
    return $resultado[0];
}

public function buscaEmpenhosPagos(){
    $inst = $this->instit;
    $di = $this->dtDataInicial;
    $df = $this->dtDataFinal;
    $ano = $this->iAnoUsu;
    $sql = pg_query("SELECT empempenho.e60_numemp::integer as e60_numemp, e60_resumo, e60_destin, e60_codemp, e60_emiss, e60_numcgm, z01_nome, z01_cgccpf, z01_munic, e60_vlremp, e60_vlranu, e60_vlrliq, e63_codhist, e40_descr, e60_vlrpag, e60_anousu, e60_coddot, o58_coddot, o58_orgao, o40_orgao, o40_descr, o58_unidade, o41_descr, o15_codigo, o15_descr, fc_estruturaldotacao(e60_anousu,e60_coddot) as dl_estrutural, e60_codcom, pc50_descr, sum(c70_valor) as c70_valor, c70_data, c70_codlan, c53_tipo, c53_descr, (select o56_elemento||'-'||o56_descr from orcelemento where o56_codele = c67_codele and o56_anousu = c70_anousu) as desdobramento, c72_complem, z01_incest, z01_cgccpf, c66_codnota, e91_numemp from empempenho inner join conlancamemp on c75_numemp = empempenho.e60_numemp inner join conlancam on c70_codlan = c75_codlan left join conlancamnota on c66_codlan = c70_codlan inner join conlancamdoc on c71_codlan = c70_codlan inner join conhistdoc on c53_coddoc = c71_coddoc inner join cgm on cgm.z01_numcgm = empempenho.e60_numcgm inner join db_config on db_config.codigo = empempenho.e60_instit inner join orcdotacao on orcdotacao.o58_anousu = empempenho.e60_anousu and orcdotacao.o58_coddot = empempenho.e60_coddot and orcdotacao.o58_instit = empempenho.e60_instit inner join emptipo on emptipo.e41_codtipo = empempenho.e60_codtipo inner join db_config as a on a.codigo = orcdotacao.o58_instit INNER JOIN origemcomplementorecurso ON origemcomplementorecurso.o206_numero = empempenho.e60_numemp AND origemcomplementorecurso.o206_origem = 1 INNER JOIN orctiporec ON orctiporec.o15_codigo = origemcomplementorecurso.o206_recurso inner join orcfuncao on orcfuncao.o52_funcao = orcdotacao.o58_funcao inner join orcsubfuncao on orcsubfuncao.o53_subfuncao = orcdotacao.o58_subfuncao inner join orcprograma on orcprograma.o54_anousu = orcdotacao.o58_anousu and orcprograma.o54_programa = orcdotacao.o58_programa inner join orcelemento on orcelemento.o56_codele = orcdotacao.o58_codele and orcdotacao.o58_anousu = orcelemento.o56_anousu inner join orcprojativ on orcprojativ.o55_anousu = orcdotacao.o58_anousu and orcprojativ.o55_projativ = orcdotacao.o58_projativ inner join orcorgao on orcorgao.o40_anousu = orcdotacao.o58_anousu and orcorgao.o40_orgao = orcdotacao.o58_orgao inner join orcunidade on orcunidade.o41_anousu = orcdotacao.o58_anousu and orcunidade.o41_orgao = orcdotacao.o58_orgao and orcunidade.o41_unidade = orcdotacao.o58_unidade left join empemphist on empemphist.e63_numemp = empempenho.e60_numemp left join emphist on emphist.e40_codhist = empemphist.e63_codhist inner join pctipocompra on pctipocompra.pc50_codcom = empempenho.e60_codcom left join empresto on e60_numemp = e91_numemp and e60_anousu = e91_anousu left join conlancamcompl on c72_codlan = c70_codlan inner join conlancamele on c67_codlan = c70_codlan where  e60_numemp not in (select e91_numemp from empresto where e91_anousu = {$ano}) and c53_coddoc in(35, 37, 36, 38) and c70_data between '{$di}' and '{$df}' and 1=1 and e60_instit in ({$inst}) group by e60_numemp, e60_resumo, e60_destin, e60_codemp, e60_emiss, e60_numcgm, z01_nome, z01_cgccpf, z01_munic, e60_vlremp, e60_vlranu, e60_vlrliq, e63_codhist, e40_descr, e60_vlrpag, e60_anousu, e60_coddot, o58_coddot, o58_orgao, o40_orgao, o40_descr, o58_unidade, o41_descr, o15_codigo, o15_descr, e60_codcom, pc50_descr, c70_data, c70_codlan, c53_tipo, c53_descr, desdobramento, c72_complem, z01_incest, z01_cgccpf, c66_codnota, e91_numemp order by e60_numemp, c70_codlan");

        
    $resultado = array();

    while ($linha = pg_fetch_object($sql)) {
        $resultado[] = $linha;
    }
        
    return $resultado;
}

public function buscaEmpenhao2($seqempenho){
    $di = $this->dtDataInicial;
    $df = $this->dtDataFinal;
    $sql = pg_query("SELECT c70_codlan, c70_data, e60_coddot, c53_coddoc, c53_descr, case when c127_conlancam is not null then 'Sim' else 'Não' end as dl_Lançamento_Retenção, c70_valor, c82_reduz, c60_descr, c72_complem, e69_numero as dl_Nota_Fiscal, e50_codord, e50_data, nomeinstabrev as dl_Ente from conlancamemp inner join conlancam on c70_codlan = c75_codlan inner join empempenho on c75_numemp = e60_numemp inner join conlancamordem on conlancamordem.c03_codlan = conlancam.c70_codlan left outer join conlancampag on c82_codlan = c70_codlan inner join conlancamdoc on c71_codlan = c70_codlan inner join conhistdoc on c53_coddoc = c71_coddoc left join conlancamcompl on c72_codlan =c70_codlan left join conlancamnota on c66_codlan =c70_codlan left join conlancamord on c80_codlan =c70_codlan left join empnota on c66_codnota = e69_codnota left join conplanoreduz on c61_reduz = conlancampag.c82_reduz and c61_anousu=c70_anousu left join conplano on c60_codcon = conplanoreduz.c61_codcon and c60_anousu=c61_anousu left join pagordem on e50_codord = c80_codord inner join conlancaminstit on c02_codlan = c70_codlan inner join db_config on c02_instit = db_config.codigo left join conlancamretencao on c127_conlancam = c70_codlan where c75_numemp = {$seqempenho} AND c70_data BETWEEN '{$di}' AND '{$df}' AND c53_coddoc IN(35, 37, 36, 38) order by c75_data, c03_ordem, c75_codlan");
    $resultado = pg_fetch_all($sql);
    return $resultado[0];
}

public function buscaNotaDoEmpenhao2($seqempenho){
    $di = $this->dtDataInicial;
    $df = $this->dtDataFinal;
    $sql = pg_query("SELECT c70_codlan, c70_data, e60_coddot, c53_coddoc, c53_descr, case when c127_conlancam is not null then 'Sim' else 'Não' end as dl_Lançamento_Retenção, c70_valor, c82_reduz, c60_descr, c72_complem, e69_numero, e69_codnota, e50_codord, e50_data, nomeinstabrev as dl_Ente from conlancamemp inner join conlancam on c70_codlan = c75_codlan inner join empempenho on c75_numemp = e60_numemp inner join conlancamordem on conlancamordem.c03_codlan = conlancam.c70_codlan left outer join conlancampag on c82_codlan = c70_codlan inner join conlancamdoc on c71_codlan = c70_codlan inner join conhistdoc on c53_coddoc = c71_coddoc left join conlancamcompl on c72_codlan =c70_codlan left join conlancamnota on c66_codlan =c70_codlan left join conlancamord on c80_codlan =c70_codlan left join empnota on c66_codnota = e69_codnota left join conplanoreduz on c61_reduz = conlancampag.c82_reduz and c61_anousu=c70_anousu left join conplano on c60_codcon = conplanoreduz.c61_codcon and c60_anousu=c61_anousu left join pagordem on e50_codord = c80_codord inner join conlancaminstit on c02_codlan = c70_codlan inner join db_config on c02_instit = db_config.codigo left join conlancamretencao on c127_conlancam = c70_codlan where c75_numemp = {$seqempenho} AND c70_data BETWEEN '{$di}' AND '{$df}' AND c53_coddoc IN(35, 37, 36, 38) order by c75_data, c03_ordem, c75_codlan");
    $resultado = pg_fetch_all($sql);
    //return ($resultado[0]["e69_numero"]) ? $resultado[0]["e69_numero"] : false;
    return $resultado[0];
}

public function buscaDadosBancarios4($seqempenho, $codord){
    $sql = pg_query("SELECT c70_codlan, c70_data, c53_descr, case when c127_conlancam is not null then 'Sim' else 'Não' end as dl_Lançamento_Retenção, c70_valor, c82_reduz, c60_descr, c72_complem, e69_numero as dl_Nota_Fiscal, e50_codord, e50_data, nomeinstabrev as dl_Ente from conlancamemp inner join conlancam on c70_codlan = c75_codlan inner join empempenho on c75_numemp = e60_numemp inner join conlancamordem on conlancamordem.c03_codlan = conlancam.c70_codlan left outer join conlancampag on c82_codlan = c70_codlan inner join conlancamdoc on c71_codlan = c70_codlan inner join conhistdoc on c53_coddoc = c71_coddoc left join conlancamcompl on c72_codlan =c70_codlan left join conlancamnota on c66_codlan =c70_codlan left join conlancamord on c80_codlan =c70_codlan left join empnota on c66_codnota = e69_codnota left join conplanoreduz on c61_reduz = conlancampag.c82_reduz and c61_anousu=c70_anousu left join conplano on c60_codcon = conplanoreduz.c61_codcon and c60_anousu=c61_anousu left join pagordem on e50_codord = c80_codord inner join conlancaminstit on c02_codlan = c70_codlan inner join db_config on c02_instit = db_config.codigo left join conlancamretencao on c127_conlancam = c70_codlan where c75_numemp = {$seqempenho} AND e50_codord = {$codord} AND c82_reduz is not null order by c75_data, c03_ordem, c75_codlan");
                    $resultado = pg_fetch_all($sql);
                    $codlan = $resultado[0]["c70_codlan"];

                    $sql2 = pg_query("SELECT db90_codban, db89_codagencia, db89_digito, db83_conta, db83_dvconta from empempenho inner join empnota on e69_numemp = e60_numemp inner join pagordemnota on e71_codnota = e69_codnota inner join conlancamemp on c75_numemp = e60_numemp inner join conlancam on c75_codlan = c70_codlan inner join conlancamdoc on c71_codlan = c70_codlan inner join conlancamcompl on c72_codlan = c70_codlan inner join conlancamord on c80_codlan = c70_codlan and c80_codord = e71_codord inner join lancamentoscontabeislog as log on codlan = c70_codlan and tipo_movimento = 1 inner join db_usuacgm on db_usuacgm.id_usuario = log.id_usuario inner join cgm as cgmusu on cgmusu.z01_numcgm = cgmlogin inner join conlancampag on c82_codlan = c70_codlan inner join contabilidade.conplanoreduz on c61_reduz = c82_reduz and c61_anousu = c82_anousu inner join contabilidade.conplanocontabancaria on c56_reduz = c61_reduz and c56_anousu = c61_anousu inner join configuracoes.contabancaria on c56_contabancaria = db83_sequencial inner join configuracoes.bancoagencia on db89_sequencial = db83_bancoagencia inner join configuracoes.db_bancos on db90_codban = db89_db_bancos where e60_numemp = {$seqempenho} AND c70_codlan = {$codlan}");
                    $resultado2 = pg_fetch_all($sql2);
                    return $resultado2[0];
                }

    public function gerarDados()
    {

        $ugs = $this->pegaCgmOrdenador();
        $ugs = $this->buscaCPFordenador($ugs);

        $campos = [
            'e60_numemp',
            'e60_codemp',
            'e60_anousu',
            'e69_codnota',
            'e69_numero',
            'c70_codlan',
            'c70_data',
            'c70_valor',
            'c70_anousu',
            'c72_complem',
            'e69_anousu',
            'z01_nome',
            'z01_cgccpf',
            'db90_codban',
            'db89_codagencia',
            'db89_digito',
            'db83_conta',
            'db83_dvconta',
            'o58_orgao',
            'c80_codord',
            'c80_data'
        ];

        $empenhosx = DB::table('empresto')
            ->select($campos)
            ->join('empempenho', 'e60_numemp', 'e91_numemp')
            ->join('empnota', 'e69_numemp', 'e60_numemp')
            ->join('orcdotacao', function (JoinClause $join) {
                $join->on('o58_anousu', 'e60_anousu')
                    ->on('o58_coddot', 'e60_coddot');
            })
            ->join('pagordemnota', 'e71_codnota', 'e69_codnota')
            ->join('conlancamemp', 'c75_numemp', 'e60_numemp')
            ->join('conlancam', 'c75_codlan', 'c70_codlan')
            ->join('conlancamdoc', 'c71_codlan', 'c70_codlan')
            ->join('conlancamcompl', 'c72_codlan', 'c70_codlan')
            ->join('conlancamord', function (JoinClause $join) {
                $join->on('c80_codlan', 'c70_codlan')
                    ->on('c80_codord', 'e71_codord');
            })
            ->join('lancamentoscontabeislog AS log', function (JoinClause $join) {
                $join->on('codlan', 'c70_codlan')
                    ->where('tipo_movimento', 1);
            })
            ->join('db_usuacgm', 'db_usuacgm.id_usuario', 'log.id_usuario')
            ->join('cgm AS cgmusu', 'cgmusu.z01_numcgm', 'cgmlogin')

            // conta do pagamento
            ->join('conlancampag', 'c82_codlan', 'c70_codlan')
            ->join('contabilidade.conplanoreduz', function (JoinClause $join) {
                $join->on('c61_reduz', 'c82_reduz')
                    ->on('c61_anousu', 'c82_anousu');
            })
            ->join('contabilidade.conplanocontabancaria', function (JoinClause $join) {
                $join->on('c56_reduz', 'c61_reduz')
                    ->on('c56_anousu', 'c61_anousu');
            })
            ->join('configuracoes.contabancaria', 'c56_contabancaria', 'db83_sequencial')
            ->join('configuracoes.bancoagencia', 'db89_sequencial', 'db83_bancoagencia')
            ->join('configuracoes.db_bancos', 'db90_codban', 'db89_db_bancos')
            ->whereIn('c71_coddoc', [35,37])
            //->whereIn('c71_coddoc', [35])
            ->where('e60_instit', $this->instit)
            ->whereBetween('c70_data', [$this->dtDataInicial, $this->dtDataFinal])
            ->orderBy('e69_numero')
            ->orderBy('e60_numemp')
            ->distinct()
            ->get();

            //$sql_with_bindings = str_replace_array('?', $empenhos->getBindings(), $empenhos->toSql());
            //$sql_with_bindings = str_replace("\"", "", $sql_with_bindings);
            //var_dump($sql_with_bindings); die("confere");

            $empenhos = $this->voltaLiquidacaoes();
            //$empenhos = $this->buscaEmpenhosPagos;
            //$this->testa($empenhos); die("Confere");

        if(count($empenhos) == 0){
            throw new \Exception(sprintf(
                'Não foi encontrado nenhum registro para o arquivo da Remessa de %s',
                'Pagamento de Empenho'
            ));
        }

        $obj = new stdClass();
        $obj->PagamentosRestosPagar = [];
        $ix = 1;
        $ixsub = 1;
        $ixsub2 = 3;
        
        $guardador = array();
        foreach ($empenhos as $empenho) {
            
            //if($empenho->e60_numemp != 4347){continue;}
            $empenhao = $this->buscaEmpenhao2($empenho->e60_numemp);            
            if(empty($empenhao)){continue;}

            $xpagamentos = $this->buscaEmpenhosPagosPorEmpenho($empenho->e60_numemp);

            $notafiscal = $this->buscaNotaDoEmpenhao2($empenho->e60_numemp);

            if(!$notafiscal){
                $notafiscal = "S/N";
            }

            $codord = $this->buscaCodord2($empenho->c70_codlan);
            $valorao = 0;
            foreach ($xpagamentos as $sp) {
                $valorao += $sp->c70_valor;
            }

            /*
            $chave = $empenho->e60_numemp . $codord . $valorao;
            if(in_array($chave, $guardador)){
                continue;
            }
            array_push($guardador, $chave);
            */
            $chave = $empenho->e60_numemp . $valorao;
            if(in_array($chave, $guardador)){
                continue;
            }
            array_push($guardador, $chave);
            
            $numeropagamento = $this->sCodigoTribunal . $empenho->e60_codemp . $empenho->e60_anousu;

            $data = (object)[
                "Identificador" => $ix, //."". $empenho->c70_codlan,
                "CodigoUnidadeGestora" => $this->sCodigoTribunal,
                "Competencia" => $this->competencia,
                "NumeroNotaPagamento" => $numeropagamento,//$empenho->c70_codlan,
                "AnoPagamento" => $empenho->c70_anousu,
                "DataPagamento" => $empenho->c70_data,
                "ValorPagamento" => $valorao, //$empenho->c70_valor,
                "CPFResponsavel" => $ugs,
                //"CodigoOrgao" => $empenho->o58_orgao,
                //"CodigoUnidadeOrcamentaria" => $empenho->o58_unidade,
                //"LiquidacoesPagamentos" => []
            ];

            $xpagamentos2 = array();
            $indice = 1;
            $xconta = array();
            
            
            foreach ($xpagamentos as $pagamento) {
                $codord2 = $this->buscaCodord2($pagamento->c70_codlan);
                //$dadosordem = $this->buscaDadosOrdem($codord2);
                //$dadosempnota = $this->buscaDadosEmpNota($empenho->c66_codnota, $empenho->e60_numemp);

                $xpagamentos2[$indice]["Identificador"] = $pagamento->c70_codlan;
                $xpagamentos2[$indice]["AnoEmpenho"] = $pagamento->e60_anousu;
                $xpagamentos2[$indice]["NumeroEmpenho"] = $pagamento->e60_codemp;
                $xpagamentos2[$indice]["NumeroLiquidacaoRestosPagar"] = $codord2;
                $xpagamentos2[$indice]["AnoLiquidacaoRestosPagar"] = $pagamento->e60_anousu;
                $xpagamentos2[$indice]["CodigoOrgao"] = $pagamento->o58_orgao;
                $xpagamentos2[$indice]["CodigoUnidadeOrcamentaria"] = $pagamento->o58_unidade;
                $xpagamentos2[$indice]["ValorPagamentoLiquidacao"] = $pagamento->c70_valor;

                //$oiaaconta = $this->buscaaconta($empenho->c70_codlan);
                $oiaaconta = $this->buscaaconta($pagamento->c70_codlan);
                $agencia = $oiaaconta["db89_codagencia"];
                $conta = $oiaaconta["empenho->db83_conta"];
                //$xdadosbancarios2 = $this->buscaDadosBancarios4($pagamento->e60_numemp, $codord2);
                

                $xconta = (object)[
                    'Identificador' => $pagamento->c70_codlan,
                    'ValorContaPagadora' => $pagamento->c70_valor,
                    'Banco' => $oiaaconta["db90_codban"],
                    'Agencia' => $oiaaconta["db89_codagencia"],
                    'ContaBancaria' => $oiaaconta["db83_conta"]
                ];

                $xpagamentos2[$indice]["ContasPagadoras"] = (object)['ContaPagadora' => $xconta];
                $indice++;
            }
            
            foreach($xpagamentos2 as $liquidacao){
                $data->LiquidacoesPagamentos[] = (object) ['LiquidacaoPagamento' => $liquidacao];
            }


            $obj->PagamentosRestosPagar[] = (object) ['PagamentoRestosPagar' => $data];            
            $ix++;
        }

        $this->aDados = $obj;
        
    }

    
    private function liquidacao($codnota, $pagamento, $idx, $codlan){        
        
        $campos = [
            'o58_orgao',
            'o58_unidade',
            'e69_codnota',
            'e69_anousu',
            'c70_codlan',
            'e60_codemp',
            'e60_anousu',
            'c70_data',
            'c70_valor',
            'c70_anousu',
            'e69_anousu',
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
            ->join('lancamentoscontabeislog AS log', function (JoinClause $join) {
                $join->on('codlan', 'c70_codlan')
                    ->where('tipo_movimento', 1);
            })
            ->join('db_usuacgm', 'db_usuacgm.id_usuario', 'log.id_usuario')
            ->join('cgm AS cgmusu', 'cgmusu.z01_numcgm', 'cgmlogin')
            ->whereIn('c71_coddoc', [35,37])
            //->whereIn('c71_coddoc', [35])
            ->where('e69_codnota', $codnota)
            ->where('c70_codlan', $codlan)
            ->whereBetween('c70_data', [$this->dtDataInicial, $this->dtDataFinal])
            ->orderBy('e60_numemp')            
            ->get()
            ->map(function ($nota) use ($pagamento, $idx) {
                $identificador = $idx;
                //$identificador = rand(0, 50000);// . $nota->c70_codlan; 

                
                return (object)[
                    "Identificador" => $identificador,
                    "AnoEmpenho" => $nota->e60_anousu,
                    "NumeroEmpenho" => $nota->e60_codemp,
                    'NumeroLiquidacaoRestosPagar' => $nota->e69_codnota,
                    'AnoLiquidacaoRestosPagar' => $nota->e69_anousu,
                    'CodigoOrgao' => $nota->o58_orgao,
                    'CodigoUnidadeOrcamentaria' => $nota->o58_unidade,
                    'ValorPagamentoLiquidacao' => $nota->c70_valor,
                    'ContasPagadoras' => [(object)['ContaPagadora' => $pagamento]]
                ];
            })
            ->toArray();
    }



private function liquidacaosoma($codnota, $pagamento, $codlan){
        $campos = [
            'o58_orgao',
            'o58_unidade',
            'e69_codnota',
            'e69_anousu',
            'c70_codlan',
            'e60_codemp',
            'e60_anousu',
            'c70_data',
            'c70_valor',
            'c70_anousu',
            'e69_anousu',
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
            ->join('lancamentoscontabeislog AS log', function (JoinClause $join) {
                $join->on('codlan', 'c70_codlan')
                    ->where('tipo_movimento', 1);
            })
            ->join('db_usuacgm', 'db_usuacgm.id_usuario', 'log.id_usuario')
            ->join('cgm AS cgmusu', 'cgmusu.z01_numcgm', 'cgmlogin')
            ->whereIn('c71_coddoc', [35,37])
            //->whereIn('c71_coddoc', [35])
            ->where('e69_codnota', $codnota)
            ->where('c70_codlan', $codlan)
            ->whereBetween('c70_data', [$this->dtDataInicial, $this->dtDataFinal])
            ->orderBy('e60_numemp')            
            ->get()
            ->map(function ($nota) use ($pagamento) {                
                
                return (object)[
                    "Identificador" => $nota->c70_codlan,
                    "AnoEmpenho" => $nota->e60_anousu,
                    "NumeroEmpenho" => $nota->e60_codemp,
                    'NumeroLiquidacaoRestosPagar' => $nota->e69_codnota,
                    'AnoLiquidacaoRestosPagar' => $nota->e69_anousu,
                    'CodigoOrgao' => $nota->o58_orgao,
                    'CodigoUnidadeOrcamentaria' => $nota->o58_unidade,
                    'ValorPagamentoLiquidacao' => $nota->c70_valor,
                    'ContasPagadoras' => [(object)['ContaPagadora' => $pagamento]]
                ];
            })
            ->toArray();
    }


    private function liquidacao3($valor, $codnota, $pagamento){
        $campos = [
            'o58_orgao',
            'o58_unidade',
            'e69_codnota',
            'e69_anousu',
            'c70_codlan',
            'e60_codemp',
            'e60_anousu',
            'c70_data',
            'c70_valor',
            'c70_anousu',
            'e69_anousu',
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
            ->join('lancamentoscontabeislog AS log', function (JoinClause $join) {
                $join->on('codlan', 'c70_codlan')
                    ->where('tipo_movimento', 1);
            })
            ->join('db_usuacgm', 'db_usuacgm.id_usuario', 'log.id_usuario')
            ->join('cgm AS cgmusu', 'cgmusu.z01_numcgm', 'cgmlogin')
            ->whereIn('c71_coddoc', [35,37])
            //->whereIn('c71_coddoc', [35])
            ->where('e69_codnota', $codnota)
            ->where('c70_valor', $valor)
            ->whereBetween('c70_data', [$this->dtDataInicial, $this->dtDataFinal])
            ->orderBy('e60_numemp')
            
            //$sql_with_bindings = str_replace_array('?', $ixx->getBindings(), $ixx->toSql());
            //$sql_with_bindings = str_replace("\"", "", $sql_with_bindings);
            //var_dump($sql_with_bindings); die("confere");
            ->get()
            ->map(function ($nota) use ($pagamento) {                
                
                return (object)[
                    "Identificador" => $nota->c70_codlan,
                    "AnoEmpenho" => $nota->e60_anousu,
                    "NumeroEmpenho" => $nota->e60_codemp,
                    'NumeroLiquidacaoRestosPagar' => $nota->e69_codnota,
                    'AnoLiquidacaoRestosPagar' => $nota->e69_anousu,
                    'CodigoOrgao' => $nota->o58_orgao,
                    'CodigoUnidadeOrcamentaria' => $nota->o58_unidade,
                    'ValorPagamentoLiquidacao' => $nota->c70_valor,
                    'ContasPagadoras' => [(object)['ContaPagadora' => $pagamento]]
                ];
            })
            ->toArray();
            
    }



}
    







    //ORIGINAL
    /*private function liquidacao($codnota, $pagamento)
    {
        $campos = [
            'o58_orgao',
            'o58_unidade',
            'e69_codnota',
            'e69_anousu',
            'c70_codlan',
            'e60_codemp',
            'e60_anousu',
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
            ->whereIn('c71_coddoc', [33, 39])
            ->where('e69_codnota', $codnota)
            ->orderBy('e60_numemp')            
            ->get()
            ->map(function ($nota) use ($pagamento) {
                return (object)[
                    "Identificador" => $nota->c70_codlan,
                    "AnoEmpenho" => $nota->e60_anousu,
                    "NumeroEmpenho" => $nota->e60_codemp,
                    'NumeroLiquidacaoRestosPagar' => $nota->e69_codnota,
                    'AnoLiquidacaoRestosPagar' => $nota->c70_anousu,
                    'CodigoOrgao' => $nota->o58_orgao,
                    'CodigoUnidadeOrcamentaria' => $nota->o58_unidade,
                    'ValorPagamentoLiquidacao' => $nota->c70_valor,
                    'ContasPagadoras' => [(object)['ContaPagadora' => $pagamento]]
                ];
            })
            ->toArray();
    }
}*/

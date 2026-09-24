<?php

namespace ECidade\Financeiro\Contabilidade\Exportacao\Sigfis\Arquivos\v2024;

use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Database\Query\JoinClause;
use stdClass;

class ArquivoRPLiquidacao extends ArquivoBase
{
    protected $sNomeArquivo = 'LiquidacaoRestosPagar';

    public function testa($var){
        echo "<pre>";
        print_r($var);
        echo "</pre>";
    }

    public function buscaDadosPorEmpenho($codemp, $codnota){
        $inst = $this->instit;
        $di = $this->dtDataInicial;
        $df = $this->dtDataFinal;
        $ano = $this->iAnoUsu;
        
        //$sql = pg_query("SELECT e60_numemp, e60_anousu, e60_codemp, e91_anousu, o58_orgao, o58_unidade, e69_codnota, e69_dtinclusao, e69_numero, e70_valor, e50_codord, e178_sigfistipodocliquidacao, c60_estrut, z01_numcgm as cgm_responsaval, z01_cgccpf as cpf_responsaval from empresto inner join empenho.empempenho on e60_numemp = e91_numemp inner join orcamento.orcdotacao on e60_anousu = o58_anousu and e60_coddot = o58_coddot inner join empnota on empnota.e69_numemp = empempenho.e60_numemp inner join empnotaele on e70_codnota = e69_codnota inner join pagordem on e50_numemp = e69_numemp inner join pagordemnota on e71_codnota = e69_codnota and e71_codord = e50_codord inner join db_usuacgm on id_usuario = e50_id_usuario inner join cgm on z01_numcgm = cgmlogin inner join empelemento on e64_numemp = e60_numemp inner join conplanoorcamento on c60_codcon = e64_codele and c60_anousu = (case when e60_anousu < 2023 then 2023 else e60_anousu end) left join empnotasigfistipodocliquidacao on empnotasigfistipodocliquidacao.e178_empnota = empnota.e69_codnota where e91_anousu = {$ano} and e60_instit = {$inst} and e69_dtinclusao between '{$di}' and '{$df}' AND e60_numemp = '{$codemp}' AND e69_codnota = '{$codnota}'");

        $sql = pg_query("SELECT e60_numemp, e60_anousu, e60_codemp, e91_anousu, o58_orgao, o58_unidade, e69_codnota, e69_dtinclusao, e69_numero, e70_valor, e50_codord, e178_sigfistipodocliquidacao, c60_estrut, z01_numcgm as cgm_responsaval, z01_cgccpf as cpf_responsaval from empresto inner join empenho.empempenho on e60_numemp = e91_numemp inner join orcamento.orcdotacao on e60_anousu = o58_anousu and e60_coddot = o58_coddot inner join empnota on empnota.e69_numemp = empempenho.e60_numemp inner join empnotaele on e70_codnota = e69_codnota inner join pagordem on e50_numemp = e69_numemp inner join pagordemnota on e71_codnota = e69_codnota and e71_codord = e50_codord inner join db_usuacgm on id_usuario = e50_id_usuario inner join cgm on z01_numcgm = cgmlogin inner join empelemento on e64_numemp = e60_numemp inner join conplanoorcamento on c60_codcon = e64_codele and c60_anousu = (case when e60_anousu < 2023 then 2023 else e60_anousu end) left join empnotasigfistipodocliquidacao on empnotasigfistipodocliquidacao.e178_empnota = empnota.e69_codnota where e91_anousu = {$ano} and e60_instit = {$inst} and e50_data between '{$di}' and '{$df}' AND e60_numemp = '{$codemp}' AND e69_codnota = '{$codnota}'");
        
        $resultado = pg_fetch_all($sql);
        return $resultado;
    }

    public function tabelaSigfis($tipo){
        switch ($tipo) {
            case '1':
                return 1;
            case '2':
            case '3':
                return 2;
            case '4':
                return 4;
            case '5':
                return 3;
            case '6':
                return 4;
            case '7':
                return 5;
        }
    }





    public function voltaLiquidacaoes(){
        $inst = $this->instit;
        $di = $this->dtDataInicial;
        $df = $this->dtDataFinal;
        $ano = $this->iAnoUsu;
        

        $sql = pg_query("SELECT empempenho.e60_numemp::integer as e60_numemp, e60_resumo, e60_destin, e60_codemp, e60_emiss, e60_numcgm, z01_nome, z01_cgccpf, z01_munic, e60_vlremp, e60_vlranu, e60_vlrliq, e63_codhist, e40_descr, e60_vlrpag, e60_anousu, e60_coddot, o58_coddot, o58_codigo, o58_orgao, o40_orgao, o40_descr, o58_unidade, o41_descr, o15_codigo, o15_descr, fc_estruturaldotacao(e60_anousu,e60_coddot) as dl_estrutural, e60_codcom, pc50_descr, sum(c70_valor) as c70_valor, c70_data, c70_codlan, c53_tipo, c53_descr, (select o56_elemento||'-'||o56_descr from orcelemento where o56_codele = c67_codele and o56_anousu = c70_anousu) as desdobramento, c72_complem, z01_incest, z01_cgccpf, c66_codnota, e91_numemp from empempenho inner join conlancamemp on c75_numemp = empempenho.e60_numemp inner join conlancam on c70_codlan = c75_codlan left join conlancamnota on c66_codlan = c70_codlan inner join conlancamdoc on c71_codlan = c70_codlan inner join conhistdoc on c53_coddoc = c71_coddoc inner join cgm on cgm.z01_numcgm = empempenho.e60_numcgm inner join db_config on db_config.codigo = empempenho.e60_instit inner join orcdotacao on orcdotacao.o58_anousu = empempenho.e60_anousu and orcdotacao.o58_coddot = empempenho.e60_coddot and orcdotacao.o58_instit = empempenho.e60_instit inner join emptipo on emptipo.e41_codtipo = empempenho.e60_codtipo inner join db_config as a on a.codigo = orcdotacao.o58_instit INNER JOIN origemcomplementorecurso ON origemcomplementorecurso.o206_numero = empempenho.e60_numemp AND origemcomplementorecurso.o206_origem = 1 INNER JOIN orctiporec ON orctiporec.o15_codigo = origemcomplementorecurso.o206_recurso inner join orcfuncao on orcfuncao.o52_funcao = orcdotacao.o58_funcao inner join orcsubfuncao on orcsubfuncao.o53_subfuncao = orcdotacao.o58_subfuncao inner join orcprograma on orcprograma.o54_anousu = orcdotacao.o58_anousu and orcprograma.o54_programa = orcdotacao.o58_programa inner join orcelemento on orcelemento.o56_codele = orcdotacao.o58_codele and orcdotacao.o58_anousu = orcelemento.o56_anousu inner join orcprojativ on orcprojativ.o55_anousu = orcdotacao.o58_anousu and orcprojativ.o55_projativ = orcdotacao.o58_projativ inner join orcorgao on orcorgao.o40_anousu = orcdotacao.o58_anousu and orcorgao.o40_orgao = orcdotacao.o58_orgao inner join orcunidade on orcunidade.o41_anousu = orcdotacao.o58_anousu and orcunidade.o41_orgao = orcdotacao.o58_orgao and orcunidade.o41_unidade = orcdotacao.o58_unidade left join empemphist on empemphist.e63_numemp = empempenho.e60_numemp left join emphist on emphist.e40_codhist = empemphist.e63_codhist inner join pctipocompra on pctipocompra.pc50_codcom = empempenho.e60_codcom left join empresto on e60_numemp = e91_numemp and e60_anousu = e91_anousu left join conlancamcompl on c72_codlan = c70_codlan inner join conlancamele on c67_codlan = c70_codlan where c53_tipo in (20,21) and c53_coddoc != 34 AND e60_anousu < {$ano} and c70_data between '{$di}' and '{$df}' and 1=1 and e60_instit in ({$inst}) group by e60_numemp, e60_resumo, e60_destin, e60_codemp, e60_emiss, e60_numcgm, z01_nome, z01_cgccpf, z01_munic, e60_vlremp, e60_vlranu, e60_vlrliq, e63_codhist, e40_descr, e60_vlrpag, e60_anousu, e60_coddot, o58_coddot, o58_codigo, o58_orgao, o40_orgao, o40_descr, o58_unidade, o41_descr, o15_codigo, o15_descr, e60_codcom, pc50_descr, c70_data, c70_codlan, c53_tipo, c53_descr, desdobramento, c72_complem, z01_incest, z01_cgccpf, c66_codnota, e91_numemp order by e60_numemp, c70_codlan");
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
        return ($resultado[0]["e177_codigo"]) ? $resultado[0]["e177_codigo"] : 1;
    }

    public function voltaRetencoes($codord){
        $di = $this->dtDataInicial;
        $df = $this->dtDataFinal;
        $sql = pg_query("SELECT distinct e48_cgm, tabrec.*, retencaotiporec.*, retencaoreceitas.*, e27_empagemov, e27_principal, retencaoreceitasadicionais.*, tiposerviconotafiscal.e18_descricao, retencaoreceitasprodutorrural.*, emptiposervicoobra.* from retencaoreceitas inner join retencaotiporec on retencaotiporec.e21_sequencial = retencaoreceitas.e23_retencaotiporec inner join retencaopagordem on retencaopagordem.e20_sequencial = retencaoreceitas.e23_retencaopagordem inner join tabrec on tabrec.k02_codigo = retencaotiporec.e21_receita inner join retencaotipocalc on retencaotipocalc.e32_sequencial = retencaotiporec.e21_retencaotipocalc inner join pagordem on pagordem.e50_codord = retencaopagordem.e20_pagordem inner join pagordemnota on pagordem.e50_codord = pagordemnota.e71_codord inner join empnota on pagordemnota.e71_codnota = empnota.e69_codnota inner join retencaoempagemov on e23_sequencial = e27_retencaoreceitas left join empagemovslips on e27_empagemov = k107_empagemov left join slipempagemovslips on k107_sequencial = k108_empagemovslips left join retencaoreceitasadicionais on e23_sequencial = e19_retencaoreceitas left join tiposerviconotafiscal on e19_tiposerviconotafiscal = e18_sequencial left join retencaotiporeccgm on e48_retencaotiporec = retencaotiporec.e21_sequencial left join retencaoreceitasprodutorrural on e23_sequencial = e158_retencaoreceitas left join emptiposervicoobra on empnota.e69_numemp = e154_numemp where e20_pagordem = {$codord} and e23_ativo = true and e71_anulado = false and e27_principal is true AND e23_dtcalculo between '{$di}' AND '{$df}' order by e21_sequencial");
        $resultado = pg_fetch_all($sql);
        return $resultado;
    }

    public function dadosRetencao($codord, $valor){
        $di = $this->dtDataInicial;
        $df = $this->dtDataFinal;
        $sql = pg_query("SELECT distinct e48_cgm, tabrec.*, retencaotiporec.*, retencaoreceitas.*, e27_empagemov, e27_principal, retencaoreceitasadicionais.*, tiposerviconotafiscal.e18_descricao, retencaoreceitasprodutorrural.*, emptiposervicoobra.* from retencaoreceitas inner join retencaotiporec on retencaotiporec.e21_sequencial = retencaoreceitas.e23_retencaotiporec inner join retencaopagordem on retencaopagordem.e20_sequencial = retencaoreceitas.e23_retencaopagordem inner join tabrec on tabrec.k02_codigo = retencaotiporec.e21_receita inner join retencaotipocalc on retencaotipocalc.e32_sequencial = retencaotiporec.e21_retencaotipocalc inner join pagordem on pagordem.e50_codord = retencaopagordem.e20_pagordem inner join pagordemnota on pagordem.e50_codord = pagordemnota.e71_codord inner join empnota on pagordemnota.e71_codnota = empnota.e69_codnota inner join retencaoempagemov on e23_sequencial = e27_retencaoreceitas left join empagemovslips on e27_empagemov = k107_empagemov left join slipempagemovslips on k107_sequencial = k108_empagemovslips left join retencaoreceitasadicionais on e23_sequencial = e19_retencaoreceitas left join tiposerviconotafiscal on e19_tiposerviconotafiscal = e18_sequencial left join retencaotiporeccgm on e48_retencaotiporec = retencaotiporec.e21_sequencial left join retencaoreceitasprodutorrural on e23_sequencial = e158_retencaoreceitas left join emptiposervicoobra on empnota.e69_numemp = e154_numemp where e20_pagordem = {$codord} AND e23_valorretencao = {$valor} and e23_ativo = true and e71_anulado = false and e27_principal is true AND e23_dtcalculo between '{$di}' AND '{$df}' order by e21_sequencial");
        $resultado = pg_fetch_all($sql);
        return $resultado[0];
    }

    public function voltaRetencoes2($seqempenho, $codord){
        $di = $this->dtDataInicial;
        $df = $this->dtDataFinal;
        $sql = pg_query("SELECT c53_coddoc, c70_codlan, c70_data, c53_descr, case when c127_conlancam is not null then 'Sim' else 'Não' end as dl_Lançamento_Retenção, c70_valor, c82_reduz, c60_descr, c72_complem, e69_numero as dl_Nota_Fiscal, e50_codord, e50_data, nomeinstabrev as dl_Ente from conlancamemp inner join conlancam on c70_codlan = c75_codlan inner join empempenho on c75_numemp = e60_numemp inner join conlancamordem on conlancamordem.c03_codlan = conlancam.c70_codlan left outer join conlancampag on c82_codlan = c70_codlan inner join conlancamdoc on c71_codlan = c70_codlan inner join conhistdoc on c53_coddoc = c71_coddoc left join conlancamcompl on c72_codlan =c70_codlan left join conlancamnota on c66_codlan =c70_codlan left join conlancamord on c80_codlan =c70_codlan left join empnota on c66_codnota = e69_codnota left join conplanoreduz on c61_reduz = conlancampag.c82_reduz and c61_anousu=c70_anousu left join conplano on c60_codcon = conplanoreduz.c61_codcon and c60_anousu=c61_anousu left join pagordem on e50_codord = c80_codord inner join conlancaminstit on c02_codlan = c70_codlan inner join db_config on c02_instit = db_config.codigo left join conlancamretencao on c127_conlancam = c70_codlan where c75_numemp = {$seqempenho} AND c53_coddoc in (6000, 6008, 161, 6011, 6009) AND c70_data between '{$di}' AND '{$df}' AND e50_codord = {$codord} order by c75_data, c03_ordem, c75_codlan");
        //c53_coddoc in (6000, 6008, 6010, 161, 6011, 6009)
        $resultado = pg_fetch_all($sql);
        return $resultado;
    }

    public function voltaRetencoes50($seqempenho, $codord){
        $di = $this->dtDataInicial;
        $df = $this->dtDataFinal;
        $sql = pg_query("SELECT c53_coddoc, c70_codlan, c70_data, c53_descr, case when c127_conlancam is not null then 'Sim' else 'Não' end as dl_Lançamento_Retenção, c70_valor, c82_reduz, c60_descr, c72_complem, e69_numero as dl_Nota_Fiscal, e50_codord, e50_data, nomeinstabrev as dl_Ente from conlancamemp inner join conlancam on c70_codlan = c75_codlan inner join empempenho on c75_numemp = e60_numemp inner join conlancamordem on conlancamordem.c03_codlan = conlancam.c70_codlan left outer join conlancampag on c82_codlan = c70_codlan inner join conlancamdoc on c71_codlan = c70_codlan inner join conhistdoc on c53_coddoc = c71_coddoc left join conlancamcompl on c72_codlan =c70_codlan left join conlancamnota on c66_codlan =c70_codlan left join conlancamord on c80_codlan =c70_codlan left join empnota on c66_codnota = e69_codnota left join conplanoreduz on c61_reduz = conlancampag.c82_reduz and c61_anousu=c70_anousu left join conplano on c60_codcon = conplanoreduz.c61_codcon and c60_anousu=c61_anousu left join pagordem on e50_codord = c80_codord inner join conlancaminstit on c02_codlan = c70_codlan inner join db_config on c02_instit = db_config.codigo left join conlancamretencao on c127_conlancam = c70_codlan where c75_numemp = {$seqempenho} AND c53_coddoc in (6000, 6008, 6010) AND c70_data between '{$di}' AND '{$df}' AND e50_codord = {$codord} order by c75_data, c03_ordem, c75_codlan"); //saiu o 161 e o 6011 e 6009
        $resultado = pg_fetch_all($sql);
        return $resultado;
    }
    

    public function buscaDadosCredorRetencao($cgm){
        $sql = pg_query("SELECT z01_nome, z01_cgccpf FROM cgm WHERE z01_numcgm = {$cgm}");
        $resultado = pg_fetch_all($sql);
        return $resultado[0];
    }

    public function voltaContaContabil($coddot, $seqempenho){
        $di = $this->dtDataInicial;
        $df = $this->dtDataFinal;
        
        //c71_coddoc = 6002
        /*
        $sql = pg_query("SELECT c70_codlan, c70_data as data, c70_valor as valor, c71_coddoc as documento, c53_descr as descricaoevento, c80_codord as ordempagamento, c75_numemp as empenho, c76_numcgm as cgm, z01_nome as nome, e69_numero as notafiscal, e69_codnota as codigonotafiscal, c72_complem as complemento, c73_coddot as dotacao, c74_codrec as receita, c70_anousu as anolancamento, c53_tipo as tipoevento, c67_codele as codigoelemento from conlancam inner join conlancamdoc on c71_codlan = c70_codlan inner join conhistdoc on c71_coddoc = c53_coddoc left join conlancamord on c70_codlan = c80_codlan left join conlancamemp on c75_codlan = c70_codlan left join conlancamcgm on c70_codlan = c76_codlan left join cgm on z01_numcgm = c76_numcgm left join conlancamnota on c70_codlan = c66_codlan left join empnota on c66_codnota = e69_codnota left join conlancamcompl on c70_codlan = c72_codlan left join conlancamdot on c73_codlan = c70_codlan and c73_anousu = c70_anousu left join conlancamele on c67_codlan = c70_codlan left join conlancamrec on c74_codlan = c70_codlan and c74_anousu = c70_anousu where c73_coddot = {$coddot} AND c71_coddoc = 161 AND c75_numemp = {$seqempenho} AND c70_data between '{$di}' AND '{$df}'");
        $codlan = pg_fetch_all($sql);
        $codlan = $codlan[0]["c70_codlan"];
        */
        if(empty($codlan)){
            //c71_coddoc = 6004
            $sql = pg_query("SELECT c70_codlan, c70_data as data, c70_valor as valor, c71_coddoc as documento, c53_descr as descricaoevento, c80_codord as ordempagamento, c75_numemp as empenho, c76_numcgm as cgm, z01_nome as nome, e69_numero as notafiscal, e69_codnota as codigonotafiscal, c72_complem as complemento, c73_coddot as dotacao, c74_codrec as receita, c70_anousu as anolancamento, c53_tipo as tipoevento, c67_codele as codigoelemento from conlancam inner join conlancamdoc on c71_codlan = c70_codlan inner join conhistdoc on c71_coddoc = c53_coddoc left join conlancamord on c70_codlan = c80_codlan left join conlancamemp on c75_codlan = c70_codlan left join conlancamcgm on c70_codlan = c76_codlan left join cgm on z01_numcgm = c76_numcgm left join conlancamnota on c70_codlan = c66_codlan left join empnota on c66_codnota = e69_codnota left join conlancamcompl on c70_codlan = c72_codlan left join conlancamdot on c73_codlan = c70_codlan and c73_anousu = c70_anousu left join conlancamele on c67_codlan = c70_codlan left join conlancamrec on c74_codlan = c70_codlan and c74_anousu = c70_anousu where c73_coddot = {$coddot} AND c71_coddoc = 6000 AND c75_numemp = {$seqempenho} AND c70_data between '{$di}' AND '{$df}'");
        $codlan = pg_fetch_all($sql);
        $codlan = $codlan[0]["c70_codlan"];
        }

        /*
        if(empty($codlan)){
            $sql = pg_query("SELECT c70_codlan, c70_data as data, c70_valor as valor, c71_coddoc as documento, c53_descr as descricaoevento, c80_codord as ordempagamento, c75_numemp as empenho, c76_numcgm as cgm, z01_nome as nome, e69_numero as notafiscal, e69_codnota as codigonotafiscal, c72_complem as complemento, c73_coddot as dotacao, c74_codrec as receita, c70_anousu as anolancamento, c53_tipo as tipoevento, c67_codele as codigoelemento from conlancam inner join conlancamdoc on c71_codlan = c70_codlan inner join conhistdoc on c71_coddoc = c53_coddoc left join conlancamord on c70_codlan = c80_codlan left join conlancamemp on c75_codlan = c70_codlan left join conlancamcgm on c70_codlan = c76_codlan left join cgm on z01_numcgm = c76_numcgm left join conlancamnota on c70_codlan = c66_codlan left join empnota on c66_codnota = e69_codnota left join conlancamcompl on c70_codlan = c72_codlan left join conlancamdot on c73_codlan = c70_codlan and c73_anousu = c70_anousu left join conlancamele on c67_codlan = c70_codlan left join conlancamrec on c74_codlan = c70_codlan and c74_anousu = c70_anousu where c73_coddot = {$coddot} AND c71_coddoc = 6008 AND c75_numemp = {$seqempenho} AND c70_data between '{$di}' AND '{$df}'");
        $codlan = pg_fetch_all($sql);
        $codlan = $codlan[0]["c70_codlan"];
        }

        if(empty($codlan)){
            $sql = pg_query("SELECT c70_codlan, c70_data as data, c70_valor as valor, c71_coddoc as documento, c53_descr as descricaoevento, c80_codord as ordempagamento, c75_numemp as empenho, c76_numcgm as cgm, z01_nome as nome, e69_numero as notafiscal, e69_codnota as codigonotafiscal, c72_complem as complemento, c73_coddot as dotacao, c74_codrec as receita, c70_anousu as anolancamento, c53_tipo as tipoevento, c67_codele as codigoelemento from conlancam inner join conlancamdoc on c71_codlan = c70_codlan inner join conhistdoc on c71_coddoc = c53_coddoc left join conlancamord on c70_codlan = c80_codlan left join conlancamemp on c75_codlan = c70_codlan left join conlancamcgm on c70_codlan = c76_codlan left join cgm on z01_numcgm = c76_numcgm left join conlancamnota on c70_codlan = c66_codlan left join empnota on c66_codnota = e69_codnota left join conlancamcompl on c70_codlan = c72_codlan left join conlancamdot on c73_codlan = c70_codlan and c73_anousu = c70_anousu left join conlancamele on c67_codlan = c70_codlan left join conlancamrec on c74_codlan = c70_codlan and c74_anousu = c70_anousu where c73_coddot = {$coddot} AND c71_coddoc = 6009 AND c75_numemp = {$seqempenho} AND c70_data between '{$di}' AND '{$df}'");
        $codlan = pg_fetch_all($sql);
        $codlan = $codlan[0]["c70_codlan"];
        }

        if(empty($codlan)){
            $sql = pg_query("SELECT c70_codlan, c70_data as data, c70_valor as valor, c71_coddoc as documento, c53_descr as descricaoevento, c80_codord as ordempagamento, c75_numemp as empenho, c76_numcgm as cgm, z01_nome as nome, e69_numero as notafiscal, e69_codnota as codigonotafiscal, c72_complem as complemento, c73_coddot as dotacao, c74_codrec as receita, c70_anousu as anolancamento, c53_tipo as tipoevento, c67_codele as codigoelemento from conlancam inner join conlancamdoc on c71_codlan = c70_codlan inner join conhistdoc on c71_coddoc = c53_coddoc left join conlancamord on c70_codlan = c80_codlan left join conlancamemp on c75_codlan = c70_codlan left join conlancamcgm on c70_codlan = c76_codlan left join cgm on z01_numcgm = c76_numcgm left join conlancamnota on c70_codlan = c66_codlan left join empnota on c66_codnota = e69_codnota left join conlancamcompl on c70_codlan = c72_codlan left join conlancamdot on c73_codlan = c70_codlan and c73_anousu = c70_anousu left join conlancamele on c67_codlan = c70_codlan left join conlancamrec on c74_codlan = c70_codlan and c74_anousu = c70_anousu where c73_coddot = {$coddot} AND c71_coddoc = 6010 AND c75_numemp = {$seqempenho} AND c70_data between '{$di}' AND '{$df}'");
        $codlan = pg_fetch_all($sql);
        $codlan = $codlan[0]["c70_codlan"];
        }

        if(empty($codlan)){
            $sql = pg_query("SELECT c70_codlan, c70_data as data, c70_valor as valor, c71_coddoc as documento, c53_descr as descricaoevento, c80_codord as ordempagamento, c75_numemp as empenho, c76_numcgm as cgm, z01_nome as nome, e69_numero as notafiscal, e69_codnota as codigonotafiscal, c72_complem as complemento, c73_coddot as dotacao, c74_codrec as receita, c70_anousu as anolancamento, c53_tipo as tipoevento, c67_codele as codigoelemento from conlancam inner join conlancamdoc on c71_codlan = c70_codlan inner join conhistdoc on c71_coddoc = c53_coddoc left join conlancamord on c70_codlan = c80_codlan left join conlancamemp on c75_codlan = c70_codlan left join conlancamcgm on c70_codlan = c76_codlan left join cgm on z01_numcgm = c76_numcgm left join conlancamnota on c70_codlan = c66_codlan left join empnota on c66_codnota = e69_codnota left join conlancamcompl on c70_codlan = c72_codlan left join conlancamdot on c73_codlan = c70_codlan and c73_anousu = c70_anousu left join conlancamele on c67_codlan = c70_codlan left join conlancamrec on c74_codlan = c70_codlan and c74_anousu = c70_anousu where c73_coddot = {$coddot} AND c71_coddoc = 6011 AND c75_numemp = {$seqempenho} AND c70_data between '{$di}' AND '{$df}'");
        $codlan = pg_fetch_all($sql);
        $codlan = $codlan[0]["c70_codlan"];
        }
        */

        $sql2 = pg_query("SELECT c69_debito as contadebito, c1.c60_descr as descricaodebito, c1.c60_estrut as estruturaldebito, c69_credito as contacredito, c2.c60_descr as descricaocredito, c2.c60_estrut as estruturalcredito, c69_valor as valor, c69_ordem as ordem, fc_atributo_conta_corrente({$codlan}, c69_debito, 'D') as atributos_cc_debito, fc_atributo_conta_corrente({$codlan}, c69_credito, 'C') as atributos_cc_credito from conlancam inner join conlancamval on c70_codlan = c69_codlan inner join conplanoreduz red1 on red1.c61_reduz = conlancamval.c69_debito and red1.c61_anousu = conlancamval.c69_anousu inner join conplano c1 on c1.c60_codcon = red1.c61_codcon and c1.c60_anousu = red1.c61_anousu inner join conplanoreduz red2 on red2.c61_reduz = conlancamval.c69_credito and red2.c61_anousu = conlancamval.c69_anousu inner join conplano c2 on c2.c60_codcon = red2.c61_codcon and c2.c60_anousu = red2.c61_anousu where c70_codlan={$codlan} order by c69_ordem, c69_sequen");
        $resultado = pg_fetch_all($sql2);
        //return $resultado[0]["estruturalcredito"];
        return $resultado[0]["estruturaldebito"];
    }

    public function voltaContaContabil3($seqempenho, $coddoc, $codlan){
        $di = $this->dtDataInicial;
        $df = $this->dtDataFinal;


        $sql0 = pg_query("SELECT c69_debito as contadebito, c1.c60_descr as descricaodebito, c1.c60_estrut as estruturaldebito, c69_credito as contacredito, c2.c60_descr as descricaocredito, c2.c60_estrut as estruturalcredito, c69_valor as valor, c69_ordem as ordem, fc_atributo_conta_corrente({$codlan}, c69_debito, 'D') as atributos_cc_debito, fc_atributo_conta_corrente({$codlan}, c69_credito, 'C') as atributos_cc_credito from conlancam inner join conlancamval on c70_codlan = c69_codlan inner join conplanoreduz red1 on red1.c61_reduz = conlancamval.c69_debito and red1.c61_anousu = conlancamval.c69_anousu inner join conplano c1 on c1.c60_codcon = red1.c61_codcon and c1.c60_anousu = red1.c61_anousu inner join conplanoreduz red2 on red2.c61_reduz = conlancamval.c69_credito and red2.c61_anousu = conlancamval.c69_anousu inner join conplano c2 on c2.c60_codcon = red2.c61_codcon and c2.c60_anousu = red2.c61_anousu where c70_codlan={$codlan} order by c69_ordem, c69_sequen");
        $resultado = pg_fetch_all($sql0);
        $estrut = $resultado[0]["estruturalcredito"];
        //$estrut = $resultado[0]["estruturaldebito"];
        

        if(substr($estrut, 0, 3) == 411){
            $sql = pg_query("SELECT c70_codlan, c70_data as data, c70_valor as valor, c71_coddoc as documento, c53_descr as descricaoevento, c80_codord as ordempagamento, c75_numemp as empenho, c76_numcgm as cgm, z01_nome as nome, e69_numero as notafiscal, e69_codnota as codigonotafiscal, c72_complem as complemento, c73_coddot as dotacao, c74_codrec as receita, c70_anousu as anolancamento, c53_tipo as tipoevento, c67_codele as codigoelemento from conlancam inner join conlancamdoc on c71_codlan = c70_codlan inner join conhistdoc on c71_coddoc = c53_coddoc left join conlancamord on c70_codlan = c80_codlan left join conlancamemp on c75_codlan = c70_codlan left join conlancamcgm on c70_codlan = c76_codlan left join cgm on z01_numcgm = c76_numcgm left join conlancamnota on c70_codlan = c66_codlan left join empnota on c66_codnota = e69_codnota left join conlancamcompl on c70_codlan = c72_codlan left join conlancamdot on c73_codlan = c70_codlan and c73_anousu = c70_anousu left join conlancamele on c67_codlan = c70_codlan left join conlancamrec on c74_codlan = c70_codlan and c74_anousu = c70_anousu where c71_coddoc = 6000 AND c75_numemp = {$seqempenho} AND c70_data between '{$di}' AND '{$df}'");
            $codlanx = pg_fetch_all($sql);
            $codlanx = $codlanx[0]["c70_codlan"];
            

            $sql2 = pg_query("SELECT c69_debito as contadebito, c1.c60_descr as descricaodebito, c1.c60_estrut as estruturaldebito, c69_credito as contacredito, c2.c60_descr as descricaocredito, c2.c60_estrut as estruturalcredito, c69_valor as valor, c69_ordem as ordem, fc_atributo_conta_corrente({$codlanx}, c69_debito, 'D') as atributos_cc_debito, fc_atributo_conta_corrente({$codlanx}, c69_credito, 'C') as atributos_cc_credito from conlancam inner join conlancamval on c70_codlan = c69_codlan inner join conplanoreduz red1 on red1.c61_reduz = conlancamval.c69_debito and red1.c61_anousu = conlancamval.c69_anousu inner join conplano c1 on c1.c60_codcon = red1.c61_codcon and c1.c60_anousu = red1.c61_anousu inner join conplanoreduz red2 on red2.c61_reduz = conlancamval.c69_credito and red2.c61_anousu = conlancamval.c69_anousu inner join conplano c2 on c2.c60_codcon = red2.c61_codcon and c2.c60_anousu = red2.c61_anousu where c70_codlan={$codlanx} order by c69_ordem, c69_sequen");
            $resultado = pg_fetch_all($sql2);
            $estrut = $resultado[0]["estruturaldebito"];
            
            return substr($estrut, 0, 9);
        }else{
            return substr($estrut, 0, 9);
        }
    }

    public function voltaContaContabilCredito($seqempenho, $coddoc, $codlan){
        $di = $this->dtDataInicial;
        $df = $this->dtDataFinal;


        $sql0 = pg_query("SELECT c69_debito as contadebito, c1.c60_descr as descricaodebito, c1.c60_estrut as estruturaldebito, c69_credito as contacredito, c2.c60_descr as descricaocredito, c2.c60_estrut as estruturalcredito, c69_valor as valor, c69_ordem as ordem, fc_atributo_conta_corrente({$codlan}, c69_debito, 'D') as atributos_cc_debito, fc_atributo_conta_corrente({$codlan}, c69_credito, 'C') as atributos_cc_credito from conlancam inner join conlancamval on c70_codlan = c69_codlan inner join conplanoreduz red1 on red1.c61_reduz = conlancamval.c69_debito and red1.c61_anousu = conlancamval.c69_anousu inner join conplano c1 on c1.c60_codcon = red1.c61_codcon and c1.c60_anousu = red1.c61_anousu inner join conplanoreduz red2 on red2.c61_reduz = conlancamval.c69_credito and red2.c61_anousu = conlancamval.c69_anousu inner join conplano c2 on c2.c60_codcon = red2.c61_codcon and c2.c60_anousu = red2.c61_anousu where c70_codlan={$codlan} order by c69_ordem, c69_sequen");
        $resultado = pg_fetch_all($sql0);
        $estrut = $resultado[0]["estruturalcredito"];
        //$estrut = $resultado[0]["estruturaldebito"];
        

        if(substr($estrut, 0, 3) == 411){
            $sql = pg_query("SELECT c70_codlan, c70_data as data, c70_valor as valor, c71_coddoc as documento, c53_descr as descricaoevento, c80_codord as ordempagamento, c75_numemp as empenho, c76_numcgm as cgm, z01_nome as nome, e69_numero as notafiscal, e69_codnota as codigonotafiscal, c72_complem as complemento, c73_coddot as dotacao, c74_codrec as receita, c70_anousu as anolancamento, c53_tipo as tipoevento, c67_codele as codigoelemento from conlancam inner join conlancamdoc on c71_codlan = c70_codlan inner join conhistdoc on c71_coddoc = c53_coddoc left join conlancamord on c70_codlan = c80_codlan left join conlancamemp on c75_codlan = c70_codlan left join conlancamcgm on c70_codlan = c76_codlan left join cgm on z01_numcgm = c76_numcgm left join conlancamnota on c70_codlan = c66_codlan left join empnota on c66_codnota = e69_codnota left join conlancamcompl on c70_codlan = c72_codlan left join conlancamdot on c73_codlan = c70_codlan and c73_anousu = c70_anousu left join conlancamele on c67_codlan = c70_codlan left join conlancamrec on c74_codlan = c70_codlan and c74_anousu = c70_anousu where c71_coddoc = 6000 AND c75_numemp = {$seqempenho} AND c70_data between '{$di}' AND '{$df}'");
            $codlanx = pg_fetch_all($sql);
            $codlanx = $codlanx[0]["c70_codlan"];
            

            $sql2 = pg_query("SELECT c69_debito as contadebito, c1.c60_descr as descricaodebito, c1.c60_estrut as estruturaldebito, c69_credito as contacredito, c2.c60_descr as descricaocredito, c2.c60_estrut as estruturalcredito, c69_valor as valor, c69_ordem as ordem, fc_atributo_conta_corrente({$codlanx}, c69_debito, 'D') as atributos_cc_debito, fc_atributo_conta_corrente({$codlanx}, c69_credito, 'C') as atributos_cc_credito from conlancam inner join conlancamval on c70_codlan = c69_codlan inner join conplanoreduz red1 on red1.c61_reduz = conlancamval.c69_debito and red1.c61_anousu = conlancamval.c69_anousu inner join conplano c1 on c1.c60_codcon = red1.c61_codcon and c1.c60_anousu = red1.c61_anousu inner join conplanoreduz red2 on red2.c61_reduz = conlancamval.c69_credito and red2.c61_anousu = conlancamval.c69_anousu inner join conplano c2 on c2.c60_codcon = red2.c61_codcon and c2.c60_anousu = red2.c61_anousu where c70_codlan={$codlanx} order by c69_ordem, c69_sequen");
            $resultado = pg_fetch_all($sql2);
            $estrut = $resultado[0]["estruturalcredito"];
            
            return substr($estrut, 0, 9);
        }else{
            return substr($estrut, 0, 9);
        }
    }

    public function voltaContaContabilDebito($seqempenho, $coddoc, $codlan){
        $di = $this->dtDataInicial;
        $df = $this->dtDataFinal;


        $sql0 = pg_query("SELECT c69_debito as contadebito, c1.c60_descr as descricaodebito, c1.c60_estrut as estruturaldebito, c69_credito as contacredito, c2.c60_descr as descricaocredito, c2.c60_estrut as estruturalcredito, c69_valor as valor, c69_ordem as ordem, fc_atributo_conta_corrente({$codlan}, c69_debito, 'D') as atributos_cc_debito, fc_atributo_conta_corrente({$codlan}, c69_credito, 'C') as atributos_cc_credito from conlancam inner join conlancamval on c70_codlan = c69_codlan inner join conplanoreduz red1 on red1.c61_reduz = conlancamval.c69_debito and red1.c61_anousu = conlancamval.c69_anousu inner join conplano c1 on c1.c60_codcon = red1.c61_codcon and c1.c60_anousu = red1.c61_anousu inner join conplanoreduz red2 on red2.c61_reduz = conlancamval.c69_credito and red2.c61_anousu = conlancamval.c69_anousu inner join conplano c2 on c2.c60_codcon = red2.c61_codcon and c2.c60_anousu = red2.c61_anousu where c70_codlan={$codlan} order by c69_ordem, c69_sequen");
        $resultado = pg_fetch_all($sql0);
        //$estrut = $resultado[0]["estruturalcredito"];
        $estrut = $resultado[0]["estruturaldebito"];
        

        if(substr($estrut, 0, 3) == 411){
            $sql = pg_query("SELECT c70_codlan, c70_data as data, c70_valor as valor, c71_coddoc as documento, c53_descr as descricaoevento, c80_codord as ordempagamento, c75_numemp as empenho, c76_numcgm as cgm, z01_nome as nome, e69_numero as notafiscal, e69_codnota as codigonotafiscal, c72_complem as complemento, c73_coddot as dotacao, c74_codrec as receita, c70_anousu as anolancamento, c53_tipo as tipoevento, c67_codele as codigoelemento from conlancam inner join conlancamdoc on c71_codlan = c70_codlan inner join conhistdoc on c71_coddoc = c53_coddoc left join conlancamord on c70_codlan = c80_codlan left join conlancamemp on c75_codlan = c70_codlan left join conlancamcgm on c70_codlan = c76_codlan left join cgm on z01_numcgm = c76_numcgm left join conlancamnota on c70_codlan = c66_codlan left join empnota on c66_codnota = e69_codnota left join conlancamcompl on c70_codlan = c72_codlan left join conlancamdot on c73_codlan = c70_codlan and c73_anousu = c70_anousu left join conlancamele on c67_codlan = c70_codlan left join conlancamrec on c74_codlan = c70_codlan and c74_anousu = c70_anousu where c71_coddoc = 6000 AND c75_numemp = {$seqempenho} AND c70_data between '{$di}' AND '{$df}'");
            $codlanx = pg_fetch_all($sql);
            $codlanx = $codlanx[0]["c70_codlan"];
            

            $sql2 = pg_query("SELECT c69_debito as contadebito, c1.c60_descr as descricaodebito, c1.c60_estrut as estruturaldebito, c69_credito as contacredito, c2.c60_descr as descricaocredito, c2.c60_estrut as estruturalcredito, c69_valor as valor, c69_ordem as ordem, fc_atributo_conta_corrente({$codlanx}, c69_debito, 'D') as atributos_cc_debito, fc_atributo_conta_corrente({$codlanx}, c69_credito, 'C') as atributos_cc_credito from conlancam inner join conlancamval on c70_codlan = c69_codlan inner join conplanoreduz red1 on red1.c61_reduz = conlancamval.c69_debito and red1.c61_anousu = conlancamval.c69_anousu inner join conplano c1 on c1.c60_codcon = red1.c61_codcon and c1.c60_anousu = red1.c61_anousu inner join conplanoreduz red2 on red2.c61_reduz = conlancamval.c69_credito and red2.c61_anousu = conlancamval.c69_anousu inner join conplano c2 on c2.c60_codcon = red2.c61_codcon and c2.c60_anousu = red2.c61_anousu where c70_codlan={$codlanx} order by c69_ordem, c69_sequen");
            $resultado = pg_fetch_all($sql2);
            $estrut = $resultado[0]["estruturaldebito"];
            
            return substr($estrut, 0, 9);
        }else{
            return substr($estrut, 0, 9);
        }
    }

        function buscaContaContabil2($codlan){
            $sql = pg_query("SELECT c69_debito as contadebito, c1.c60_descr as descricaodebito, c1.c60_estrut as estruturaldebito, c69_credito as contacredito, c2.c60_descr as descricaocredito, c2.c60_estrut as estruturalcredito, c69_valor as valor, c69_ordem as ordem from conlancam inner join conlancamval on c70_codlan = c69_codlan inner join conplanoreduz red1 on red1.c61_reduz = conlancamval.c69_debito and red1.c61_anousu = conlancamval.c69_anousu inner join conplano c1 on c1.c60_codcon  = red1.c61_codcon and c1.c60_anousu   = red1.c61_anousu inner join conplanoreduz red2 on red2.c61_reduz  = conlancamval.c69_credito and red2.c61_anousu = conlancamval.c69_anousu inner join conplano c2 on c2.c60_codcon = red2.c61_codcon and c2.c60_anousu   = red2.c61_anousu where c70_codlan= {$codlan} order by c69_ordem, c69_sequen");
            $resultado = pg_fetch_all($sql);
            $escred = substr($resultado[0]["estruturalcredito"], 0, 9);
            $esdeb = substr($resultado[0]["estruturaldebito"], 0, 9);

            if(substr($escred, 0, 4) != 2188 && substr($escred, 0, 4) != 2288){
                $estrutural = $esdeb;
            }else{
                $estrutural = $escred;
            }

            /*if(substr($estrutural, 0, 4) != 2188 && substr($estrutural, 0, 4) != 2288){
                return false;
            }*/

            //return substr($resultado[0]["estruturalcredito"], 0, 9);
            return $estrutural;
        }

        function buscaDadosRetencao($codlan){
            $sql = pg_query("SELECT c70_codlan as codigo, c70_data as data, c70_valor as valor, c71_coddoc as documento, c53_descr as descricaoevento, c80_codord as ordempagamento, c75_numemp as empenho, c76_numcgm as cgm, z01_nome as nome, z01_cgccpf, e69_numero as notafiscal, e69_codnota as codigonotafiscal, c72_complem as complemento, c73_coddot as dotacao, c74_codrec as receita, c70_anousu as anolancamento, c53_tipo as tipoevento, c67_codele  as codigoelemento from conlancam inner join conlancamdoc on c71_codlan = c70_codlan inner join conhistdoc   on c71_coddoc = c53_coddoc left  join conlancamord on c70_codlan = c80_codlan left join conlancamemp on c75_codlan = c70_codlan left join conlancamcgm on c70_codlan = c76_codlan left join cgm on z01_numcgm = c76_numcgm left join conlancamnota on c70_codlan = c66_codlan left join empnota on c66_codnota = e69_codnota left join conlancamcompl on c70_codlan = c72_codlan left join conlancamdot on c73_codlan = c70_codlan and c73_anousu = c70_anousu left join conlancamele on c67_codlan = c70_codlan left join conlancamrec on c74_codlan = c70_codlan and c74_anousu = c70_anousu where c70_codlan = {$codlan}");
            $resultado = pg_fetch_all($sql);
            return $resultado[0];
        }

        function buscaCodFonRec($codlan){
                    $sql = pg_query("select distinct o15_codigo, o15_recurso, o15_descr, o200_sequencial, o200_descricao from conlancamrecurso join orctiporec on orctiporec.o15_codigo = conlancamrecurso.c130_orctiporec join complementofonterecurso on complementofonterecurso.o200_sequencial = orctiporec.o15_complemento where c130_conlancam = {$codlan}");
                    $resultado = pg_fetch_all($sql);

                    return ($resultado[1]["o15_codigo"]) ? $resultado[1]["o15_codigo"] : $resultado[0]["o15_codigo"];
                }

    public function gerarDados()
    {   

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
            33904501 => 33904500,
            33904004 => 33904001,
            33903981 => 33903906,
            //44905235 => 44905200,
            //33903054 => 33903099,
            44905235 => 44905299,
            33904501 => 33904599,
            33903937 => 33903936,
            33903981 => 33903999,
            46907106 => 46907101,
            46907104 => 46907101,
            46907301 => 46907399,
            32902104 => 32902101,
            32902106 => 32902101,
            33901499 => 33901401,
            33904713 => 33904705,
            33903004 => 33903001,
            33903096 => 33903094,
            33904002 => 33904001,
            33903058 => 33903024,
            33909600 => 33909601,
            33903054 => 33903024,
            33904011 => 33904012,
            33904003 => 33904012,
            33903022 => 33903021,
            33903992 => 33903990,
            33903057 => 33903024,
            44905234 => 44905299,
            33904603 => 33904601,
            33904602 => 33904601,
            33903055 => 33903024,
            33903056 => 33903024,
            33504301 => 33504306,
            44905237 => 44905299,
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
            33903938 => 33903936
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
    6002 => 1600,
    6594 => 1659,
    1500 => 1500,
    1700 => 1500,
    1704 => 1500,
    101 => 1500,
    108 => 1500,
    111 => 1500,
    157 => 1500,
    1703 => 1500,
    29 => 1500,
    104 => 1500,
    78 => 1500,
    139 => 1500,
    136 => 1500,
    149 => 1500,
    161 => 1500,
    1569 => 1500,
    150 => 1500,
    68 => 1500,
    131 => 1500,
    145 => 1500,
    138 => 1500,
    10 => 1500
    );

        $fontes50 = array(
    6000 => 1600,
    6001 => 1600,
    6002 => 1600,
    6003 => 1600,
    6004 => 1600,
    6005 => 1600,
    6012 => 1601,
    6021 => 1602,
    6031 => 1603,
    6032 => 1600,
    6041 => 1604,
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
    6312 => 1632,
    6351 => 1635,
    6591 => 1501,
    6592 => 1501,
    6593 => 1600,
    6593 => 1500,
    6594 => 1600,
    6594 => 1500,
    6595 => 1500,
    6596 => 1600,
    6597 => 1600,
    6219 => 1621,
    200 => 1500
    );

        $campos = [
            'e60_numemp', 'e60_anousu', 'e60_codemp',
            'e91_anousu',
            'o58_orgao', 'o58_unidade',
            'e69_codnota', 'e69_dtinclusao', 'e69_numero',
            'e70_valor', 'e50_codord',
            'e178_sigfistipodocliquidacao',
            'c60_estrut',
            'z01_numcgm as cgm_responsaval', 'z01_cgccpf as cpf_responsaval'
        ];
        $empenhosx = DB::table('empresto')
            ->select($campos)
            ->join('empenho.empempenho', 'e60_numemp', 'e91_numemp')
            ->join('orcamento.orcdotacao', function (JoinClause $join) {
                $join->on('e60_anousu', 'o58_anousu')
                    ->on('e60_coddot', 'o58_coddot');
            })
            ->join('empnota', 'empnota.e69_numemp', 'empempenho.e60_numemp')
            ->join('empnotaele', 'e70_codnota', 'e69_codnota')
            ->join('pagordem', 'e50_numemp', 'e69_numemp')
            ->join('pagordemnota', function (JoinClause $join) {
                $join->on('e71_codnota', 'e69_codnota')
                    ->on('e71_codord', 'e50_codord');
            })
            ->join('db_usuacgm', 'id_usuario', 'e50_id_usuario')
            ->join('cgm', 'z01_numcgm', 'cgmlogin')
            ->join('empelemento', 'e64_numemp', 'e60_numemp')
            ->join('conplanoorcamento', function (JoinClause $join) {
                $join->on('c60_codcon', 'e64_codele')
                    ->whereRaw('c60_anousu = (case when e60_anousu < 2023 then 2023 else e60_anousu end)');
            })
            /*->join('planodespesaconplanoorcamento', 'conplanoorcamento_codigo', 'c60_codigo')
            ->join('planodespesa', function (JoinClause $join) {
                $join->on('planodespesa.id', 'planodespesa_id')
                    ->on('exercicio', 'c60_anousu')
                    ->where('uniao', '=', 't');
            })*/
            ->leftJoin(
                'empnotasigfistipodocliquidacao',
                'empnotasigfistipodocliquidacao.e178_empnota',
                'empnota.e69_codnota'
            )
            ->where('e91_anousu', $this->iAnoUsu)
            ->where('e60_instit', $this->instit)
            ->whereBetween('e69_dtinclusao', [$this->dtDataInicial, $this->dtDataFinal])
            ->get();
            
            //$sql_with_bindings = str_replace_array('?', $empenhos->getBindings(), $empenhos->toSql());
            //$sql_with_bindings = str_replace("\"", "", $sql_with_bindings);
            //var_dump($sql_with_bindings); die("confere");

            $empenhos = $this->voltaLiquidacaoes();
            //$this->testa($empenhos); die("Confere");

        
        if(count($empenhos) == 0){
            throw new \Exception(sprintf(
                'Não foi encontrado nenhum registro para o arquivo da Remessa de %s',
                'Restos a Pagar - Inscrição'
            ));
        }

        $obj = new stdClass();
        $obj->LiquidacoesRestosPagar = [];
        $guarda = array();
        $guardaempenho = array();
        $ix = 1;
        
        
        foreach ($empenhos as $empenho) {
            $codord = $this->buscaCodord2($empenho->c70_codlan);
            $dadosordem = $this->buscaDadosOrdem($codord);
            $dadosempnota = $this->buscaDadosEmpNota($empenho->c66_codnota, $empenho->e60_numemp);
            $vt = $this->validaTipo($empenho->c66_codnota);
            $dados = $this->buscaDadosPorEmpenho($empenho->e60_numemp, $empenho->c66_codnota);
            $tipoDocumento = $vt;

            $data = (object)[
                'Identificador' => $ix,
                'CodigoUnidadeGestora' => $this->sCodigoTribunal,
                'Competencia' => $this->competencia,
                'NumeroEmpenho' => $empenho->e60_codemp,
                'AnoEmpenho' => $empenho->e60_anousu,
                'NumeroNotaLiquidacao' => $codord, //$dadosempnota["e69_codnota"],
                'AnoLiquidacao' => substr($empenho->c70_data, 0, 4),
                'DataLiquidacao' => $dadosempnota["e69_dtinclusao"],
                'ValorLiquidacao' => $empenho->c70_valor,
                'TipoDocumento' => $tipoDocumento,
                'CodigoOrgao' => $empenho->o58_orgao,
                'CodigoUnidadeOrcamentaria' => $empenho->o58_unidade
                //'ResponsaveisLiquidacao' => [(object)['Responsavel' => $responsavel]],
                //'SubElementos' => [(object)['LiquidacaoRestosPagarSubelemento' => $subelemento]],
                //'ConsignacoesRetencoes' => null
            ];

            $data->ResponsaveisLiquidacao = [];
            $data->SubElementos = [];
            //$data->ConsignacoesRetencoes = [];

            $ix++;
            $vv = 0;
            foreach ($dados as $linha){
                $subelenatdes = substr($linha["c60_estrut"], 1, 8);
                if($fontessubelemento[substr($linha["c60_estrut"], 1, 8)]){
                    $subelenatdes = $fontessubelemento[substr($linha["c60_estrut"], 1, 8)];
                }
                $vv += $linha["e70_valor"];
            }

            //$verificaretencoes = $this->voltaRetencoes($codord);
            if($this->instit == 50){
                $verificaretencoes = $this->voltaRetencoes50($empenho->e60_numemp, $codord);    
            }else{
                $verificaretencoes = $this->voltaRetencoes2($empenho->e60_numemp, $codord);
            }
            //$this->testa($verificaretencoes); die("Confere");
            
            //$this->testa($empenho); die("Empenho");
            if($verificaretencoes){
                
                $dadoscredor = $this->buscaDadosCredorRetencao($verificaretencoes[0]["e48_cgm"]);                
                $nomec = trim($dadoscredor["z01_nome"]);
                $cpfc = trim($dadoscredor["z01_cgccpf"]);
                $tipoc = (strlen($cpfc) == 11) ? 2 : 1;
                
                $contacontabil = substr($contacontabil, 0, 9);
                if($contacontabil == 411120430){$contacontabil = 228810104;}
                if($contacontabil == 411180211){$contacontabil = 228810108;}
                if($contacontabil == 411180211){$contacontabil = 228810108;}
                if($contacontabil == 499619901){$contacontabil = 228810105;}
                if($contacontabil == 111111902){$contacontabil = 218810108;}
                if($contacontabil == 111112002){$contacontabil = 111110200;}
                if($contacontabil == 111111903){$contacontabil = 111111900;}
                if($contacontabil == 228810119){$contacontabil = 218810102;}
                
                $consignacaoRetencoes = [];
                $indice = 0;

                $codigofonterecurso = $empenho->o58_codigo;
                if(strlen($codigofonterecurso) == 3){
                    $codigofonterecurso = "0".$codigofonterecurso;
                }
                $xconsignacao = array();

                //$contacontabilx = $this->buscaContaContabil2($empenho->c70_codlan);


                foreach ($verificaretencoes as $retencao) {
                    /*if($retencao["c53_coddoc"] != 6010){
                    echo $retencao["c53_coddoc"] . " - " . $retencao["c70_codlan"]; echo "<br>";

                    }*/
                    $dadosretencoes = $this->dadosRetencao($retencao["e50_codord"], $retencao["c70_valor"]);
                    //var_dump($retencao["e50_codord"], $retencao["c70_valor"]); echo "<br>";
                    //$this->testa($dadosretencoes); die("ConfereX");
                    $codlan = $retencao["c70_codlan"];
                    //$contacontabilx = $this->buscaContaContabil2($codlan);
                    $contacontabilx = $this->voltaContaContabil3($empenho->e60_numemp, $retencao["c53_coddoc"], $codlan);
                    if($retencao["c53_coddoc"] == 6002 || $retencao["c53_coddoc"] == 6009){
                        $contacontabilx = $this->voltaContaContabilCredito($empenho->e60_numemp, $retencao["c53_coddoc"], $codlan);    
                    }
                    if($retencao["c53_coddoc"] == 161 || $retencao["c53_coddoc"] == 6010){
                        $contacontabilx = $this->voltaContaContabilDebito($empenho->e60_numemp, $retencao["c53_coddoc"], $codlan);    
                    }
                    
                    //if(!$contacontabilx){continue;}
                    $dadosretencao = $this->buscaDadosRetencao($codlan);
                    $xcodfon = $this->buscaCodFonRec($codlan);

                    if($contacontabilx == 411120430){$contacontabilx = 228810104;}
                    if($contacontabilx == 411180211){$contacontabilx = 228810108;}
                    if($contacontabilx == 411180211){$contacontabilx = 228810108;}
                    if($contacontabilx == 499619901){$contacontabilx = 228810105;}
                    if($contacontabilx == 111111902){$contacontabilx = 218810108;}

                    if($this->instit == 50){
                        if($fontes50[$xcodfon]){
                            $xcodfon = $fontes50[$xcodfon];
                        }
                    }else{
                        if($fontes0[$xcodfon]){
                            $xcodfon = $fontes0[$xcodfon];
                        }    
                    }

                    if($contacontabilx == 499910101){
                        $contacontabilx = 218810199;
                        $dadosretencao["z01_cgccpf"] = "39563911000162";
                        $dadosretencao["nome"] = "FUNDO MUNICIPAL DE SAÚDE";
                    }
                    

                    //$this->testa($retencao); die("Um");
                    
                    $consignacaoRetencoes[] = (object)[
                        'Identificador' => $retencao["c70_codlan"],
                        'ContaContabil' => $contacontabilx,
                        'TipoConsignacaoRetencao' => $dadosretencoes["e21_tiporet"],
                        'CodigoFonteRecurso' => $xcodfon,
                        'CpfCnpjCredor' => $dadosretencao["z01_cgccpf"],
                        'NaturezaCredor' => (strlen($dadosretencao["z01_cgccpf"]) == 11) ? 2 : 1,
                        'NomeCredor' => utf8_encode($dadosretencao["nome"]),
                        'Valor' => $dadosretencao["valor"]
                    ];
                    
                    /*
                    $consignacaoRetencoes[] = (object)[
                        'Identificador' => $retencao["e23_sequencial"],
                        'ContaContabil' => $contacontabil,
                        'TipoConsignacaoRetencao' => $retencao["e21_tiporet"],
                        'CodigoFonteRecurso' => $codigofonterecurso,
                        'CpfCnpjCredor' => $cpfc,
                        'NaturezaCredor' => $tipoc,
                        'NomeCredor' => $nomec,
                        'Valor' => $retencao["e23_valorretencao"]
                    ];
                    */
                }//fim do foreach verificaretencoes
                
                $consignacaoRetencoesXML = [];
                foreach ($consignacaoRetencoes as $retencao) {
                    $consignacaoRetencoesXML[] = [
                        'ConsignacaoRetencao' => (array) $retencao
                    ];
                }
                
            }else{
                $consignacaoRetencoesXML = null;
            }
            
            $responsavel = new stdClass();

            $responsavel->Identificador = $linha['cgm_responsaval'];
            if(strlen($linha['cpf_responsaval']) > 11){
                $linha['cpf_responsaval'] = "81885520700";
            }
            $responsavel->CPFResponsavel = $linha['cpf_responsaval'];

            $subelemento = new stdClass();
            $subelemento->Identificador = $linha['e69_codnota'];
            $subelemento->Valor = $vv;
            $subelemento->NaturezaDespesa = $subelenatdes;
                
            $data->ResponsaveisLiquidacao[] = (object)['Responsavel' => $responsavel];
            $data->SubElementos[] = (object)['LiquidacaoRestosPagarSubelemento' => $subelemento];
            $data->ConsignacoesRetencoes = $consignacaoRetencoesXML;
            //'ResponsaveisLiquidacao' => [(object)['Responsavel' => $responsavel]],
            //'SubElementos' => [(object)['LiquidacaoRestosPagarSubelemento' => $subelemento]],
            //'ConsignacoesRetencoes' => null
            $obj->LiquidacoesRestosPagar[] = (object)['LiquidacaoRestosPagar' => $data];
        }
        
        //$this->testa($obj); die("Maoe");

        $this->aDados = $obj;
    }
}

<?php

namespace ECidade\Financeiro\Contabilidade\Exportacao\Sigfis\Arquivos\v2024;

use BusinessException;
use ECidade\Financeiro\Contabilidade\Exportacao\Sigfis\Common\Helper;
use Illuminate\Database\Capsule\Manager as DB;
use stdClass;

class ArquivoPagamentoDeEmpenhoConsignacaoRetencao extends ArquivoBase
{
    protected $sNomeArquivo = 'PagamentoDeEmpenhoConsignacaoRetencao';

    public function buscaCPFordenador($cgm){
        $sql = pg_query("SELECT z01_cgccpf FROM cgm WHERE z01_numcgm = {$cgm}");
        $resultado = pg_fetch_all($sql);
        return $resultado[0]["z01_cgccpf"];
    }

    public function testa($var){
        echo "<pre>";
        print_r($var);
        echo "</pre>";
    }

        public function buscaRetencoesPorEmpenho($numemp, $numnota){
        $inst = $this->instit;
        $ano = $this->iAnoUsu;
        $di = $this->dtDataInicial;
        $df = $this->dtDataFinal;

        //$sql = pg_query("SELECT e60_numemp, e23_sequencial as IdentificadorRetencao, empempenho.e60_codemp as NumeroEmpenho, empempenho.e60_anousu as AnoEmpenho, orcdotacao.o58_orgao as CodigoOrgao, orcdotacao.o58_unidade as CodigoUnidadeOrcamentaria, empnota.e69_codnota as NumeroNota, k12_data as DataPagamento, e32_sequencial as Tipo, e23_valorretencao as ValorPago, cgm.z01_nome as NomeCredor, cgm.z01_cgccpf as CNPJCredor, cgmusu.z01_cgccpf as CPFResponsavel, empnota.e69_codnota as IdentificadorLiquidacao, empnota.e69_anousu as AnoLiquidacaoEmpenho, db89_db_bancos as Banco, db89_codagencia as Agencia, db83_conta as ContaBancaria from retencaoreceitas inner join retencaotiporec on e21_sequencial = e23_retencaotiporec inner join retencaotipocalc on e32_sequencial = e21_retencaotipocalc inner join retencaotiporeccgm on e48_retencaotiporec = e21_sequencial inner join retencaopagordem on e23_retencaopagordem = e20_sequencial inner join retencaoempagemov on e27_retencaoreceitas = e23_sequencial inner join empagemov on e81_codmov = e27_empagemov inner join pagordem on e50_codord = e20_pagordem inner join pagordemnota on e71_codord = e50_codord inner join empempenho on e60_numemp = e50_numemp inner join orcdotacao on o58_anousu = e60_anousu and o58_coddot = e60_coddot inner join empnota on e69_codnota = e71_codnota inner join db_usuacgm on db_usuacgm.id_usuario = e50_id_usuario inner join cgm as cgmusu on cgmlogin = cgmusu.z01_numcgm inner join cgm on cgm.z01_numcgm = empempenho.e60_numcgm left join empagemovslips on k107_retencao = e23_sequencial left join slipempagemovslips on k108_empagemovslips = k107_sequencial left join slip on k17_codigo = k108_slip left join empageformacgm on e28_numcgm = empempenho.e60_numcgm left join empagetipo on e83_codtipo = empageformacgm.e28_empagetipo inner join saltes on saltes.k13_conta = coalesce(slip.k17_credito, e83_conta) inner join conplanocontabancaria on c56_reduz = saltes.k13_reduz and c56_anousu = e60_anousu inner join contabancaria on contabancaria.db83_sequencial = conplanocontabancaria.c56_contabancaria inner join bancoagencia on bancoagencia.db89_sequencial = contabancaria.db83_bancoagencia inner join retencaocorgrupocorrente on e47_retencaoreceita = e23_sequencial inner join corgrupocorrente on k105_sequencial = e47_corgrupocorrente inner join corrente on k105_sequencial = e47_corgrupocorrente and k105_id = k12_id and k105_autent = k12_autent and k105_data = k12_data where e23_ativo = true and e60_instit = {$inst} and e60_anousu = {$ano} and k12_estorn = false and k12_data between '{$di}' and '{$df}' AND e60_codemp = '{$numemp}'");

        $sql = pg_query("SELECT e60_numemp, e23_sequencial as IdentificadorRetencao, empempenho.e60_codemp as NumeroEmpenho, empempenho.e60_anousu as AnoEmpenho, orcdotacao.o58_orgao as CodigoOrgao, orcdotacao.o58_unidade as CodigoUnidadeOrcamentaria, empnota.e69_codnota as NumeroNota, k12_data as DataPagamento, e32_sequencial as Tipo, e23_valorretencao as ValorPago, cgm.z01_nome as NomeCredor, cgm.z01_cgccpf as CNPJCredor, cgmusu.z01_cgccpf as CPFResponsavel, empnota.e69_codnota as IdentificadorLiquidacao, empnota.e69_anousu as AnoLiquidacaoEmpenho, db89_db_bancos as Banco, db89_codagencia as Agencia, db83_conta as ContaBancaria from retencaoreceitas inner join retencaotiporec on e21_sequencial = e23_retencaotiporec inner join retencaotipocalc on e32_sequencial = e21_retencaotipocalc inner join retencaotiporeccgm on e48_retencaotiporec = e21_sequencial inner join retencaopagordem on e23_retencaopagordem = e20_sequencial inner join retencaoempagemov on e27_retencaoreceitas = e23_sequencial inner join empagemov on e81_codmov = e27_empagemov inner join pagordem on e50_codord = e20_pagordem inner join pagordemnota on e71_codord = e50_codord inner join empempenho on e60_numemp = e50_numemp inner join orcdotacao on o58_anousu = e60_anousu and o58_coddot = e60_coddot inner join empnota on e69_codnota = e71_codnota inner join db_usuacgm on db_usuacgm.id_usuario = e50_id_usuario inner join cgm as cgmusu on cgmlogin = cgmusu.z01_numcgm inner join cgm on cgm.z01_numcgm = empempenho.e60_numcgm left join empagemovslips on k107_retencao = e23_sequencial left join slipempagemovslips on k108_empagemovslips = k107_sequencial left join slip on k17_codigo = k108_slip left join empageformacgm on e28_numcgm = empempenho.e60_numcgm left join empagetipo on e83_codtipo = empageformacgm.e28_empagetipo inner join saltes on saltes.k13_conta = coalesce(slip.k17_credito, e83_conta) inner join conplanocontabancaria on c56_reduz = saltes.k13_reduz and c56_anousu = e60_anousu inner join contabancaria on contabancaria.db83_sequencial = conplanocontabancaria.c56_contabancaria inner join bancoagencia on bancoagencia.db89_sequencial = contabancaria.db83_bancoagencia inner join retencaocorgrupocorrente on e47_retencaoreceita = e23_sequencial inner join corgrupocorrente on k105_sequencial = e47_corgrupocorrente inner join corrente on k105_sequencial = e47_corgrupocorrente and k105_id = k12_id and k105_autent = k12_autent and k105_data = k12_data where e60_instit = {$inst} and e60_anousu = {$ano} and k12_estorn = false and k12_data between '{$di}' and '{$df}' AND e60_codemp = '{$numemp}' AND e69_numero = '{$numnota}' ");

        $resultado = pg_fetch_all($sql);
        return $resultado;
    }

    public function verificaDatas($numemp){
        $di = $this->dtDataInicial;
        $df = $this->dtDataFinal;
        $sql = pg_query("SELECT c53_coddoc, c70_codlan, c70_data, c53_descr, case when c127_conlancam is not null then 'Sim' else 'Não' end as dl_Lançamento_Retenção, c70_valor, c82_reduz, c60_descr, c72_complem, e69_numero as dl_Nota_Fiscal, e50_codord, e50_data, nomeinstabrev as dl_Ente from conlancamemp inner join conlancam on c70_codlan = c75_codlan inner join empempenho on c75_numemp = e60_numemp inner join conlancamordem on conlancamordem.c03_codlan = conlancam.c70_codlan left outer join conlancampag on c82_codlan = c70_codlan inner join conlancamdoc on c71_codlan = c70_codlan inner join conhistdoc on c53_coddoc = c71_coddoc left join conlancamcompl on c72_codlan =c70_codlan left join conlancamnota on c66_codlan =c70_codlan left join conlancamord on c80_codlan =c70_codlan left join empnota on c66_codnota = e69_codnota left join conplanoreduz on c61_reduz = conlancampag.c82_reduz and c61_anousu=c70_anousu left join conplano on c60_codcon = conplanoreduz.c61_codcon and c60_anousu=c61_anousu left join pagordem on e50_codord = c80_codord inner join conlancaminstit on c02_codlan = c70_codlan inner join db_config on c02_instit = db_config.codigo left join conlancamretencao on c127_conlancam = c70_codlan where c75_numemp = {$numemp} AND c70_data between '{$di}' AND '{$df}' AND c53_coddoc in(161, 6004)order by c75_data, c03_ordem, c75_codlan");
        $resultado = pg_fetch_all($sql);
        return $resultado;
        //in(6004, 161)
    }

    public function buscaBanco($seqempenho, $codord){
        $sql1 = pg_query("SELECT c82_reduz from conlancamemp inner join conlancam on c70_codlan = c75_codlan inner join empempenho on c75_numemp = e60_numemp inner join conlancamordem on conlancamordem.c03_codlan = conlancam.c70_codlan left outer join conlancampag on c82_codlan = c70_codlan inner join conlancamdoc on c71_codlan = c70_codlan inner join conhistdoc on c53_coddoc = c71_coddoc left join conlancamcompl on c72_codlan =c70_codlan left join conlancamnota on c66_codlan =c70_codlan left join conlancamord on c80_codlan =c70_codlan left join empnota on c66_codnota = e69_codnota left join conplanoreduz on c61_reduz = conlancampag.c82_reduz and c61_anousu=c70_anousu left join conplano on c60_codcon = conplanoreduz.c61_codcon and c60_anousu=c61_anousu left join pagordem on e50_codord = c80_codord inner join conlancaminstit on c02_codlan = c70_codlan inner join db_config on c02_instit = db_config.codigo left join conlancamretencao on c127_conlancam = c70_codlan where c75_numemp = {$seqempenho} AND e50_codord = {$codord} AND c82_reduz is not null order by c75_data, c03_ordem, c75_codlan");
        $reduzido = pg_fetch_all($sql1);
        $reduzido = $reduzido[0]["c82_reduz"];
        

        $sql = pg_query("SELECT db89_db_bancos, db89_codagencia, db83_conta, db89_digito FROM conplanocontabancaria INNER JOIN contabancaria ON c56_contabancaria = db83_sequencial INNER JOIN bancoagencia ON db83_bancoagencia = db89_sequencial WHERE c56_reduz = {$reduzido} AND c56_anousu = {$this->iAnoUsu}");
        $resultado = pg_fetch_all($sql);
        return $resultado[0];
    }

    public function buscaRetencoes(){
        $instit = $this->instit;
        $ano = $this->iAnoUsu;
        $dtinicio = $this->dtDataInicial;
        $dtfim = $this->dtDataFinal;

        $sql = pg_query("SELECT empempenho.e60_numemp::integer as e60_numemp, e60_resumo, e60_destin, e60_codemp, e60_emiss, e60_numcgm, z01_nome, z01_cgccpf, z01_munic, e60_vlremp, e60_vlranu, e60_vlrliq, e63_codhist, e40_descr, e60_vlrpag, e60_anousu, e60_coddot, o58_coddot, o58_orgao, o40_orgao, o40_descr, o58_unidade, o41_descr, o15_codigo, o15_descr, fc_estruturaldotacao(e60_anousu,e60_coddot) as dl_estrutural, e60_codcom, pc50_descr, sum(c70_valor) as c70_valor, c70_data, c70_codlan, c53_tipo, c53_descr, (select o56_elemento||'-'||o56_descr from orcelemento where o56_codele = c67_codele and o56_anousu = c70_anousu) as desdobramento, c72_complem, z01_incest, z01_cgccpf, c66_codnota, e91_numemp from empempenho inner join conlancamemp on c75_numemp = empempenho.e60_numemp inner join conlancam on c70_codlan = c75_codlan left join conlancamnota on c66_codlan = c70_codlan inner join conlancamdoc on c71_codlan = c70_codlan inner join conhistdoc on c53_coddoc = c71_coddoc inner join cgm on cgm.z01_numcgm = empempenho.e60_numcgm inner join db_config on db_config.codigo = empempenho.e60_instit inner join orcdotacao on orcdotacao.o58_anousu = empempenho.e60_anousu and orcdotacao.o58_coddot = empempenho.e60_coddot and orcdotacao.o58_instit = empempenho.e60_instit inner join emptipo on emptipo.e41_codtipo = empempenho.e60_codtipo inner join db_config as a on a.codigo = orcdotacao.o58_instit INNER JOIN origemcomplementorecurso ON origemcomplementorecurso.o206_numero = empempenho.e60_numemp AND origemcomplementorecurso.o206_origem = 1 INNER JOIN orctiporec ON orctiporec.o15_codigo = origemcomplementorecurso.o206_recurso inner join orcfuncao on orcfuncao.o52_funcao = orcdotacao.o58_funcao inner join orcsubfuncao on orcsubfuncao.o53_subfuncao = orcdotacao.o58_subfuncao inner join orcprograma on orcprograma.o54_anousu = orcdotacao.o58_anousu and orcprograma.o54_programa = orcdotacao.o58_programa inner join orcelemento on orcelemento.o56_codele = orcdotacao.o58_codele and orcdotacao.o58_anousu = orcelemento.o56_anousu inner join orcprojativ on orcprojativ.o55_anousu = orcdotacao.o58_anousu and orcprojativ.o55_projativ = orcdotacao.o58_projativ inner join orcorgao on orcorgao.o40_anousu = orcdotacao.o58_anousu and orcorgao.o40_orgao = orcdotacao.o58_orgao inner join orcunidade on orcunidade.o41_anousu = orcdotacao.o58_anousu and orcunidade.o41_orgao = orcdotacao.o58_orgao and orcunidade.o41_unidade = orcdotacao.o58_unidade left join empemphist on empemphist.e63_numemp = empempenho.e60_numemp left join emphist on emphist.e40_codhist = empemphist.e63_codhist inner join pctipocompra on pctipocompra.pc50_codcom = empempenho.e60_codcom left join empresto on e60_numemp = e91_numemp and e60_anousu = e91_anousu left join conlancamcompl on c72_codlan = c70_codlan inner join conlancamele on c67_codlan = c70_codlan where c53_ in (30,31) and e60_anousu < {$ano} and c70_data between '{$dtinicio}' and '{$dtfim}' and 1=1 and e60_instit in ({$instit}) group by e60_numemp, e60_resumo, e60_destin, e60_codemp, e60_emiss, e60_numcgm, z01_nome, z01_cgccpf, z01_munic, e60_vlremp, e60_vlranu, e60_vlrliq, e63_codhist, e40_descr, e60_vlrpag, e60_anousu, e60_coddot, o58_coddot, o58_orgao, o40_orgao, o40_descr, o58_unidade, o41_descr, o15_codigo, o15_descr, e60_codcom, pc50_descr, c70_data, c70_codlan, c53_tipo, c53_descr, desdobramento, c72_complem, z01_incest, z01_cgccpf, c66_codnota, e91_numemp order by e60_numemp, c70_codlan");
        //$resultado = pg_fetch_all($sql);

        $resultado = array();
        while ($linha = pg_fetch_object($sql)) {
            $resultado[] = $linha;
        }
        
        return $resultado;
    }

    public function buscaRetPorEmpenho($seqempenho){
        $instit = $this->instit;
        $ano = $this->iAnoUsu;
        $dtinicio = $this->dtDataInicial;
        $dtfim = $this->dtDataFinal;

        $sql = pg_query("SELECT empempenho.e60_numemp::integer as e60_numemp, e60_resumo, e60_destin, e60_codemp, e60_emiss, e60_numcgm, z01_nome, z01_cgccpf, z01_munic, e60_vlremp, e60_vlranu, e60_vlrliq, e63_codhist, e40_descr, e60_vlrpag, e60_anousu, e60_coddot, o58_coddot, o58_orgao, o40_orgao, o40_descr, o58_unidade, o41_descr, o15_codigo, o15_descr, fc_estruturaldotacao(e60_anousu,e60_coddot) as dl_estrutural, e60_codcom, pc50_descr, sum(c70_valor) as c70_valor, c70_data, c70_codlan, c53_tipo, c53_descr, (select o56_elemento||'-'||o56_descr from orcelemento where o56_codele = c67_codele and o56_anousu = c70_anousu) as desdobramento, c72_complem, z01_incest, z01_cgccpf, c66_codnota, e91_numemp from empempenho inner join conlancamemp on c75_numemp = empempenho.e60_numemp inner join conlancam on c70_codlan = c75_codlan left join conlancamnota on c66_codlan = c70_codlan inner join conlancamdoc on c71_codlan = c70_codlan inner join conhistdoc on c53_coddoc = c71_coddoc inner join cgm on cgm.z01_numcgm = empempenho.e60_numcgm inner join db_config on db_config.codigo = empempenho.e60_instit inner join orcdotacao on orcdotacao.o58_anousu = empempenho.e60_anousu and orcdotacao.o58_coddot = empempenho.e60_coddot and orcdotacao.o58_instit = empempenho.e60_instit inner join emptipo on emptipo.e41_codtipo = empempenho.e60_codtipo inner join db_config as a on a.codigo = orcdotacao.o58_instit INNER JOIN origemcomplementorecurso ON origemcomplementorecurso.o206_numero = empempenho.e60_numemp AND origemcomplementorecurso.o206_origem = 1 INNER JOIN orctiporec ON orctiporec.o15_codigo = origemcomplementorecurso.o206_recurso inner join orcfuncao on orcfuncao.o52_funcao = orcdotacao.o58_funcao inner join orcsubfuncao on orcsubfuncao.o53_subfuncao = orcdotacao.o58_subfuncao inner join orcprograma on orcprograma.o54_anousu = orcdotacao.o58_anousu and orcprograma.o54_programa = orcdotacao.o58_programa inner join orcelemento on orcelemento.o56_codele = orcdotacao.o58_codele and orcdotacao.o58_anousu = orcelemento.o56_anousu inner join orcprojativ on orcprojativ.o55_anousu = orcdotacao.o58_anousu and orcprojativ.o55_projativ = orcdotacao.o58_projativ inner join orcorgao on orcorgao.o40_anousu = orcdotacao.o58_anousu and orcorgao.o40_orgao = orcdotacao.o58_orgao inner join orcunidade on orcunidade.o41_anousu = orcdotacao.o58_anousu and orcunidade.o41_orgao = orcdotacao.o58_orgao and orcunidade.o41_unidade = orcdotacao.o58_unidade left join empemphist on empemphist.e63_numemp = empempenho.e60_numemp left join emphist on emphist.e40_codhist = empemphist.e63_codhist inner join pctipocompra on pctipocompra.pc50_codcom = empempenho.e60_codcom left join empresto on e60_numemp = e91_numemp and e60_anousu = e91_anousu left join conlancamcompl on c72_codlan = c70_codlan inner join conlancamele on c67_codlan = c70_codlan where c53_tipo in (30,31) and e60_anousu < {$ano} and c70_data between '{$dtinicio}' and '{$dtfim}' and 1=1 and e60_instit in ({$instit}) AND e60_numemp = {$seqempenho} group by e60_numemp, e60_resumo, e60_destin, e60_codemp, e60_emiss, e60_numcgm, z01_nome, z01_cgccpf, z01_munic, e60_vlremp, e60_vlranu, e60_vlrliq, e63_codhist, e40_descr, e60_vlrpag, e60_anousu, e60_coddot, o58_coddot, o58_orgao, o40_orgao, o40_descr, o58_unidade, o41_descr, o15_codigo, o15_descr, e60_codcom, pc50_descr, c70_data, c70_codlan, c53_tipo, c53_descr, desdobramento, c72_complem, z01_incest, z01_cgccpf, c66_codnota, e91_numemp order by e60_numemp, c70_codlan");
        //$resultado = pg_fetch_all($sql);

        $resultado = array();
        while ($linha = pg_fetch_object($sql)) {
            $resultado[] = $linha;
        }
        
        return $resultado;
    }

    public function buscaDadosBancarios($codlan, $seqempenho){
        $dtinicio = $this->dtDataInicial;
        $dtfim = $this->dtDataFinal;

        $sql = pg_query("SELECT e60_numemp, e69_codnota, e69_numero, c70_codlan, c70_data, c70_valor, db90_codban, db89_codagencia, db89_digito, db83_conta, db83_dvconta from empempenho inner join empnota on e69_numemp = e60_numemp inner join pagordemnota on e71_codnota = e69_codnota inner join conlancamemp on c75_numemp = e60_numemp inner join conlancam on c75_codlan = c70_codlan inner join conlancamdoc on c71_codlan = c70_codlan inner join conlancamcompl on c72_codlan = c70_codlan inner join conlancamord on c80_codlan = c70_codlan and c80_codord = e71_codord inner join lancamentoscontabeislog as log on codlan = c70_codlan and tipo_movimento = 1 inner join db_usuacgm on db_usuacgm.id_usuario = log.id_usuario inner join cgm as cgmusu on cgmusu.z01_numcgm = cgmlogin inner join conlancampag on c82_codlan = c70_codlan inner join contabilidade.conplanoreduz on c61_reduz = c82_reduz and c61_anousu = c82_anousu inner join contabilidade.conplanocontabancaria on c56_reduz = c61_reduz and c56_anousu = c61_anousu inner join configuracoes.contabancaria on c56_contabancaria = db83_sequencial inner join configuracoes.bancoagencia on db89_sequencial = db83_bancoagencia inner join configuracoes.db_bancos on db90_codban = db89_db_bancos where c70_codlan = {$codlan} order by e60_numemp asc");
        $resultado = pg_fetch_all($sql);

        if(empty($resultado)){
            $sql = pg_query("SELECT e60_numemp, e69_codnota, e69_numero, c70_codlan, c70_data, c70_valor, db90_codban, db89_codagencia, db89_digito, db83_conta, db83_dvconta from empempenho inner join empnota on e69_numemp = e60_numemp inner join pagordemnota on e71_codnota = e69_codnota inner join conlancamemp on c75_numemp = e60_numemp inner join conlancam on c75_codlan = c70_codlan inner join conlancamdoc on c71_codlan = c70_codlan inner join conlancamcompl on c72_codlan = c70_codlan inner join conlancamord on c80_codlan = c70_codlan and c80_codord = e71_codord inner join lancamentoscontabeislog as log on codlan = c70_codlan and tipo_movimento = 1 inner join db_usuacgm on db_usuacgm.id_usuario = log.id_usuario inner join cgm as cgmusu on cgmusu.z01_numcgm = cgmlogin inner join conlancampag on c82_codlan = c70_codlan inner join contabilidade.conplanoreduz on c61_reduz = c82_reduz and c61_anousu = c82_anousu inner join contabilidade.conplanocontabancaria on c56_reduz = c61_reduz and c56_anousu = c61_anousu inner join configuracoes.contabancaria on c56_contabancaria = db83_sequencial inner join configuracoes.bancoagencia on db89_sequencial = db83_bancoagencia inner join configuracoes.db_bancos on db90_codban = db89_db_bancos where e60_numemp = {$seqempenho} AND c71_coddoc = 35 AND c70_data between '{$dtinicio}' AND '{$dtfim}' order by e60_numemp asc");
            $resultado = pg_fetch_all($sql);
        }

        if(empty($resultado)){
            $sql01 = pg_query("SELECT c80_codord from conlancam inner join conlancamdoc on c71_codlan = c70_codlan inner join conhistdoc on c71_coddoc = c53_coddoc left join conlancamord on c70_codlan = c80_codlan left join conlancamemp on c75_codlan = c70_codlan left join conlancamcgm on c70_codlan = c76_codlan left join cgm on z01_numcgm = c76_numcgm left join conlancamnota on c70_codlan = c66_codlan left join empnota on c66_codnota = e69_codnota left join conlancamcompl on c70_codlan = c72_codlan left join conlancamdot on c73_codlan = c70_codlan and c73_anousu = c70_anousu left join conlancamele on c67_codlan = c70_codlan left join conlancamrec on c74_codlan = c70_codlan and c74_anousu = c70_anousu where c80_codlan = {$codlan}");
            $resultado01 = pg_fetch_all($sql01);
            $codord01 = $resultado01[0]["c80_codord"];

            $sql02 = pg_query("SELECT c70_codlan from conlancam inner join conlancamdoc on c71_codlan = c70_codlan inner join conhistdoc on c71_coddoc = c53_coddoc left join conlancamord on c70_codlan = c80_codlan left join conlancamemp on c75_codlan = c70_codlan left join conlancamcgm on c70_codlan = c76_codlan left join cgm on z01_numcgm = c76_numcgm left join conlancamnota on c70_codlan = c66_codlan left join empnota on c66_codnota = e69_codnota left join conlancamcompl on c70_codlan = c72_codlan left join conlancamdot on c73_codlan = c70_codlan and c73_anousu = c70_anousu left join conlancamele on c67_codlan = c70_codlan left join conlancamrec on c74_codlan = c70_codlan and c74_anousu = c70_anousu where c80_codord = {$codord01} AND c71_coddoc = 161"); //antes c71_coddoc = 161 // 6008
            $resultado02 = pg_fetch_all($sql02);
            $codlan02 = $resultado02[0]["c70_codlan"];

            $xql = pg_query("SELECT e60_numemp, e69_codnota, e69_numero, c70_codlan, c70_data, c70_valor, db90_codban, db89_codagencia, db89_digito, db83_conta, db83_dvconta from empempenho inner join empnota on e69_numemp = e60_numemp inner join pagordemnota on e71_codnota = e69_codnota inner join conlancamemp on c75_numemp = e60_numemp inner join conlancam on c75_codlan = c70_codlan inner join conlancamdoc on c71_codlan = c70_codlan inner join conlancamcompl on c72_codlan = c70_codlan inner join conlancamord on c80_codlan = c70_codlan and c80_codord = e71_codord inner join lancamentoscontabeislog as log on codlan = c70_codlan and tipo_movimento = 1 inner join db_usuacgm on db_usuacgm.id_usuario = log.id_usuario inner join cgm as cgmusu on cgmusu.z01_numcgm = cgmlogin inner join conlancampag on c82_codlan = c70_codlan inner join contabilidade.conplanoreduz on c61_reduz = c82_reduz and c61_anousu = c82_anousu inner join contabilidade.conplanocontabancaria on c56_reduz = c61_reduz and c56_anousu = c61_anousu inner join configuracoes.contabancaria on c56_contabancaria = db83_sequencial inner join configuracoes.bancoagencia on db89_sequencial = db83_bancoagencia inner join configuracoes.db_bancos on db90_codban = db89_db_bancos where c70_codlan = {$codlan02} order by e60_numemp asc");
            $xresultado = pg_fetch_all($xql);
            return $xresultado[0];
        }
        return $resultado[0];
    }

    public function buscaNoNota($seqempenho){
        $dtinicio = $this->dtDataInicial;
        $dtfim = $this->dtDataFinal;
        $sql = pg_query("select e69_codnota, e69_numero, e69_numemp, e69_dtnota FROM empnota WHERE e69_numemp = {$seqempenho} AND e69_dtnota between '{$dtinicio}' AND '{$dtfim}'");
        $resultado = pg_fetch_all($sql);
        return $resultado[0]["e69_numero"];

    }

    public function buscaCodLanQuandoVazio($seqempenho, $valor){
        $sql = pg_query("SELECT e60_numemp, e69_codnota, e69_numero, c70_codlan, c70_data, c70_valor, db90_codban, db89_codagencia, db89_digito, db83_conta, db83_dvconta from empempenho inner join empnota on e69_numemp = e60_numemp inner join pagordemnota on e71_codnota = e69_codnota inner join conlancamemp on c75_numemp = e60_numemp inner join conlancam on c75_codlan = c70_codlan inner join conlancamdoc on c71_codlan = c70_codlan inner join conlancamcompl on c72_codlan = c70_codlan inner join conlancamord on c80_codlan = c70_codlan and c80_codord = e71_codord where c70_codlan = {$codlan} order by e60_numemp asc");
        $resultado = pg_fetch_all($sql);
        return $resultado;


        //, $codnota
        /*$sql = pg_query("SELECT c70_codlan, e60_numemp, e69_codnota, e69_numero, c70_codlan, c70_data, c70_valor, db90_codban, db89_codagencia, db89_digito, db83_conta, db83_dvconta from empempenho left join empnota on e69_numemp = e60_numemp left join pagordemnota on e71_codnota = e69_codnota left join conlancamemp on c75_numemp = e60_numemp left join conlancam on c75_codlan = c70_codlan left join conlancamdoc on c71_codlan = c70_codlan left join conlancamcompl on c72_codlan = c70_codlan left join conlancamord on c80_codlan = c70_codlan and c80_codord = e71_codord left join conlancampag on c82_codlan = c70_codlan left join contabilidade.conplanoreduz on c61_reduz = c82_reduz and c61_anousu = c82_anousu left join contabilidade.conplanocontabancaria on c56_reduz = c61_reduz and c56_anousu = c61_anousu left join configuracoes.contabancaria on c56_contabancaria = db83_sequencial left join configuracoes.bancoagencia on db89_sequencial = db83_bancoagencia left join configuracoes.db_bancos on db90_codban = db89_db_bancos where e60_numemp = {$seqempenho} AND c70_valor = {$valor} AND e69_codnota = {$codnota} AND c71_coddoc = 161 ORDER BY c70_data DESC");
        */
    }







    public function buscaReceitaExtraOrcamentaria(){
        $inst = $this->instit;
        $di = $this->dtDataInicial;
        $df = $this->dtDataFinal;

        $sql = pg_query("SELECT * FROM (SELECT c71_codlan, c71_data, c71_coddoc, c53_descr, c02_instit, nomeinstabrev, (SELECT z01_cgccpf FROM conlancamemp INNER JOIN empempenho ON c75_numemp = e60_numemp INNER JOIN cgm ON z01_numcgm = e60_numcgm WHERE c75_codlan = c70_codlan ) AS cpf_cnpj, (SELECT e60_codemp FROM conlancamemp INNER JOIN empempenho ON c75_numemp = e60_numemp INNER JOIN cgm ON z01_numcgm = e60_numcgm WHERE c75_codlan = c70_codlan ) AS empenho, (SELECT e60_numemp FROM conlancamemp INNER JOIN empempenho ON c75_numemp = e60_numemp INNER JOIN cgm ON z01_numcgm = e60_numcgm WHERE c75_codlan = c70_codlan ) AS seqempenho, (SELECT e60_anousu FROM conlancamemp INNER JOIN empempenho ON c75_numemp = e60_numemp INNER JOIN cgm ON z01_numcgm = e60_numcgm WHERE c75_codlan = c70_codlan ) AS ano_empenho, CASE WHEN (SELECT c60_estrut FROM conlancamretencao INNER JOIN retencaotiporec ON e21_sequencial = c127_retencaotiporec INNER JOIN tabrec a ON e21_receita = a.k02_codigo INNER JOIN tabplan b ON b.k02_anousu = c70_anousu AND a.k02_codigo = b.k02_codigo INNER JOIN conplanoreduz ON b.k02_anousu = c61_anousu AND b.k02_reduz = c61_reduz INNER JOIN conplano ON c60_anousu = c61_anousu AND c60_codcon = c61_codcon WHERE c127_conlancam = c71_codlan ) IS NOT NULL THEN (SELECT c60_estrut FROM conlancamretencao INNER JOIN retencaotiporec ON e21_sequencial = c127_retencaotiporec INNER JOIN tabrec a ON e21_receita = a.k02_codigo INNER JOIN tabplan b ON b.k02_anousu = c70_anousu AND a.k02_codigo = b.k02_codigo INNER JOIN conplanoreduz ON b.k02_anousu = c61_anousu AND b.k02_reduz = c61_reduz INNER JOIN conplano ON c60_anousu = c61_anousu AND c60_codcon = c61_codcon WHERE c127_conlancam = c71_codlan ) ELSE CASE WHEN (SELECT c60_estrut FROM conlancamcorrente INNER JOIN cornump ON c86_data = k12_data AND c86_id = k12_id AND c86_autent = k12_autent INNER JOIN tabplan ON k02_codigo = k12_receit AND k02_anousu = c70_anousu INNER JOIN conplanoreduz ON c61_anousu = c70_anousu AND c61_reduz = k02_reduz INNER JOIN conplano ON c61_anousu = c60_anousu AND c61_codcon = c60_codcon WHERE c86_conlancam = c71_codlan ) IS NOT NULL THEN (SELECT c60_estrut FROM conlancamcorrente INNER JOIN cornump ON c86_data = k12_data AND c86_id = k12_id AND c86_autent = k12_autent INNER JOIN tabplan ON k02_codigo = k12_receit AND k02_anousu = c70_anousu INNER JOIN conplanoreduz ON c61_anousu = c70_anousu AND c61_reduz = k02_reduz INNER JOIN conplano ON c61_anousu = c60_anousu AND c61_codcon = c60_codcon WHERE c86_conlancam = c71_codlan ) ELSE (SELECT c60_estrut FROM conlancamslip INNER JOIN slip ON c84_slip = k17_codigo INNER JOIN conplanoreduz ON c61_anousu = c70_anousu AND c61_reduz = k17_credito INNER JOIN conplano ON c61_anousu = c60_anousu AND c61_codcon = c60_codcon WHERE c84_conlancam = c71_codlan ) END END AS estrutural, (SELECT count(*) FROM conlancampag WHERE c82_codlan = c71_codlan ) AS ctabancaria, CASE WHEN c53_tipo = 31 THEN c70_valor*-1 WHEN c71_coddoc IN (152, 162) THEN c70_valor*-1 ELSE c70_valor END AS c70_valor FROM conlancamdoc JOIN conhistdoc ON c71_coddoc = c53_coddoc JOIN conlancam ON c70_codlan = c71_codlan JOIN conlancaminstit ON c71_codlan = c02_codlan JOIN db_config ON codigo = c02_instit WHERE c71_coddoc in(161, 6002) c71_data BETWEEN '{$di}' AND '{$df}' AND c02_instit = {$inst} ) AS x WHERE estrutural IS NOT NULL ORDER BY c71_data, c70_valor");
        $resultado = pg_fetch_all($sql);
        return $resultado;
    }
    

    public function buscaaconta($codlan){
    $sql = pg_query("SELECT e60_numemp, e69_codnota, e69_numero, c70_codlan, c70_data, c70_valor, db90_codban, db89_codagencia, db89_digito, db83_conta, db83_dvconta from empempenho inner join empnota on e69_numemp = e60_numemp inner join pagordemnota on e71_codnota = e69_codnota inner join conlancamemp on c75_numemp = e60_numemp inner join conlancam on c75_codlan = c70_codlan inner join conlancamdoc on c71_codlan = c70_codlan inner join conlancamcompl on c72_codlan = c70_codlan inner join conlancamord on c80_codlan = c70_codlan and c80_codord = e71_codord inner join lancamentoscontabeislog as log on codlan = c70_codlan and tipo_movimento = 1 inner join db_usuacgm on db_usuacgm.id_usuario = log.id_usuario inner join cgm as cgmusu on cgmusu.z01_numcgm = cgmlogin inner join conlancampag on c82_codlan = c70_codlan inner join contabilidade.conplanoreduz on c61_reduz = c82_reduz and c61_anousu = c82_anousu inner join contabilidade.conplanocontabancaria on c56_reduz = c61_reduz and c56_anousu = c61_anousu inner join configuracoes.contabancaria on c56_contabancaria = db83_sequencial inner join configuracoes.bancoagencia on db89_sequencial = db83_bancoagencia inner join configuracoes.db_bancos on db90_codban = db89_db_bancos where c70_codlan = {$codlan}");
    $resultado = pg_fetch_all($sql);
    return $resultado[0];
}

public function buscaCodord2($codlan){
    $di = $this->dtDataInicial;
    $df = $this->dtDataFinal;

    $sql = pg_query("SELECT c70_codlan, c70_data, c70_valor, c71_coddoc, c53_descr, c80_codord, c75_numemp, c76_numcgm, z01_nome, e69_numero, e69_codnota, c72_complem, c73_coddot, c74_codrec, c70_anousu, c53_tipo, c67_codele from conlancam inner join conlancamdoc on c71_codlan = c70_codlan inner join conhistdoc on c71_coddoc = c53_coddoc left join conlancamord on c70_codlan = c80_codlan left join conlancamemp on c75_codlan = c70_codlan left join conlancamcgm on c70_codlan = c76_codlan left join cgm on z01_numcgm = c76_numcgm left join conlancamnota on c70_codlan = c66_codlan left join empnota on c66_codnota = e69_codnota left join conlancamcompl on c70_codlan = c72_codlan left join conlancamdot on c73_codlan = c70_codlan and c73_anousu = c70_anousu left join conlancamele on c67_codlan = c70_codlan left join conlancamrec on c74_codlan = c70_codlan and c74_anousu = c70_anousu where c70_codlan = {$codlan}");
    $resultado = pg_fetch_all($sql);
    
    return $resultado[0]["c80_codord"];
}

/*
public function buscaDotacao($codlan){
    $di = $this->dtDataInicial;
    $df = $this->dtDataFinal;

    $sql = pg_query("SELECT c70_codlan, c70_data, c70_valor, c71_coddoc, c53_descr, c80_codord, c75_numemp, c76_numcgm, z01_nome, e69_numero, e69_codnota, c72_complem, c73_coddot, c74_codrec, c70_anousu, c53_tipo, c67_codele from conlancam inner join conlancamdoc on c71_codlan = c70_codlan inner join conhistdoc on c71_coddoc = c53_coddoc left join conlancamord on c70_codlan = c80_codlan left join conlancamemp on c75_codlan = c70_codlan left join conlancamcgm on c70_codlan = c76_codlan left join cgm on z01_numcgm = c76_numcgm left join conlancamnota on c70_codlan = c66_codlan left join empnota on c66_codnota = e69_codnota left join conlancamcompl on c70_codlan = c72_codlan left join conlancamdot on c73_codlan = c70_codlan and c73_anousu = c70_anousu left join conlancamele on c67_codlan = c70_codlan left join conlancamrec on c74_codlan = c70_codlan and c74_anousu = c70_anousu where c70_codlan = {$codlan}");
    $resultado = pg_fetch_all($sql);
    
    return $resultado[0]["c73_coddot"];
}*/

public function buscaDotacao($seqempenho){    

    $sql = pg_query("SELECT e60_coddot FROM empempenho WHERE e60_numemp = {$seqempenho}");    
    $resultado = pg_fetch_all($sql);    
    return $resultado[0]["e60_coddot"];
}

public function buscaDadosDotacao($coddot){
    $ano = $this->iAnoUsu;
    //$sql = pg_query("SELECT * from orcdotacao inner join db_config on db_config.codigo = orcdotacao.o58_instit inner join orctiporec on orctiporec.o15_codigo = orcdotacao.o58_codigo left join complementofonterecurso on orctiporec.o15_complemento = o200_sequencial inner join orcfuncao on orcfuncao.o52_funcao = orcdotacao.o58_funcao inner join orcsubfuncao on orcsubfuncao.o53_subfuncao = orcdotacao.o58_subfuncao inner join orcprograma on orcprograma.o54_anousu = orcdotacao.o58_anousu and orcprograma.o54_programa = orcdotacao.o58_programa inner join orcelemento on orcelemento.o56_codele = orcdotacao.o58_codele and orcelemento.o56_anousu = orcdotacao.o58_anousu inner join orcprojativ on orcprojativ.o55_anousu = orcdotacao.o58_anousu and orcprojativ.o55_projativ = orcdotacao.o58_projativ inner join orcorgao on orcorgao.o40_anousu = orcdotacao.o58_anousu and orcorgao.o40_orgao = orcdotacao.o58_orgao inner join orcunidade on orcunidade.o41_anousu = orcdotacao.o58_anousu and orcunidade.o41_orgao = orcdotacao.o58_orgao and orcunidade.o41_unidade = orcdotacao.o58_unidade inner join concarpeculiar on concarpeculiar.c58_sequencial = orcdotacao.o58_concarpeculiar inner join ppasubtitulolocalizadorgasto on ppasubtitulolocalizadorgasto.o11_sequencial = orcdotacao.o58_localizadorgastos inner join cgm on cgm.z01_numcgm = db_config.numcgm inner join db_tipoinstit on db_tipoinstit.db21_codtipo = db_config.db21_tipoinstit inner join orcproduto on orcproduto.o22_codproduto = orcprojativ.o55_orcproduto inner join orcorgao as a on a.o40_anousu = orcunidade.o41_anousu and a.o40_orgao = orcunidade.o41_orgao where o58_coddot = {$coddot} and o58_anousu = {$ano}");
    $sql = pg_query("SELECT * from orcdotacao inner join db_config on db_config.codigo = orcdotacao.o58_instit inner join orctiporec on orctiporec.o15_codigo = orcdotacao.o58_codigo left join complementofonterecurso on orctiporec.o15_complemento = o200_sequencial inner join orcfuncao on orcfuncao.o52_funcao = orcdotacao.o58_funcao inner join orcsubfuncao on orcsubfuncao.o53_subfuncao = orcdotacao.o58_subfuncao inner join orcprograma on orcprograma.o54_anousu = orcdotacao.o58_anousu and orcprograma.o54_programa = orcdotacao.o58_programa inner join orcelemento on orcelemento.o56_codele = orcdotacao.o58_codele and orcelemento.o56_anousu = orcdotacao.o58_anousu inner join orcprojativ on orcprojativ.o55_anousu = orcdotacao.o58_anousu and orcprojativ.o55_projativ = orcdotacao.o58_projativ inner join orcorgao on orcorgao.o40_anousu = orcdotacao.o58_anousu and orcorgao.o40_orgao = orcdotacao.o58_orgao inner join orcunidade on orcunidade.o41_anousu = orcdotacao.o58_anousu and orcunidade.o41_orgao = orcdotacao.o58_orgao and orcunidade.o41_unidade = orcdotacao.o58_unidade inner join concarpeculiar on concarpeculiar.c58_sequencial = orcdotacao.o58_concarpeculiar inner join ppasubtitulolocalizadorgasto on ppasubtitulolocalizadorgasto.o11_sequencial = orcdotacao.o58_localizadorgastos inner join cgm on cgm.z01_numcgm = db_config.numcgm inner join db_tipoinstit on db_tipoinstit.db21_codtipo = db_config.db21_tipoinstit inner join orcproduto on orcproduto.o22_codproduto = orcprojativ.o55_orcproduto inner join orcorgao as a on a.o40_anousu = orcunidade.o41_anousu and a.o40_orgao = orcunidade.o41_orgao where o58_coddot = {$coddot} ORDER BY o58_anousu DESC");
    $resultado = pg_fetch_all($sql);
    return $resultado[0];
}


public function buscaDadosCredor($codlan){
    $sql = pg_query("SELECT c70_codlan as codigo, c70_data as data, c70_valor as valor, c71_coddoc as documento, c53_descr as descricaoevento, c80_codord as ordempagamento, c75_numemp as empenho, c76_numcgm as cgm, z01_nome as nome, e69_numero as notafiscal, e69_codnota as codigonotafiscal, c72_complem as complemento, c73_coddot as dotacao, c74_codrec as receita, c70_anousu as anolancamento, c53_tipo as tipoevento, c67_codele as codigoelemento from conlancam inner join conlancamdoc on c71_codlan = c70_codlan inner join conhistdoc on c71_coddoc = c53_coddoc left join conlancamord on c70_codlan = c80_codlan left join conlancamemp on c75_codlan = c70_codlan left join conlancamcgm on c70_codlan = c76_codlan left join cgm on z01_numcgm = c76_numcgm left join conlancamnota on c70_codlan = c66_codlan left join empnota on c66_codnota = e69_codnota left join conlancamcompl on c70_codlan = c72_codlan left join conlancamdot on c73_codlan = c70_codlan and c73_anousu = c70_anousu left join conlancamele on c67_codlan = c70_codlan left join conlancamrec on c74_codlan = c70_codlan and c74_anousu = c70_anousu where c70_codlan = {$codlan}");
    $resultado = pg_fetch_all($sql);
    $cgm = $resultado[0]["cgm"];

    $sql1 = pg_query("SELECT z01_nome, z01_cgccpf FROM cgm WHERE z01_numcgm = {$cgm}");
    $resultado1 = pg_fetch_all($sql1);
    return $resultado1[0];
}

public function buscaDadosBancarios3($codord){
    $sql = pg_query("SELECT db89_db_bancos, db89_codagencia, db83_conta, e60_numemp, e50_codord from retencaoreceitas inner join retencaotiporec on e21_sequencial = e23_retencaotiporec inner join retencaotipocalc on e32_sequencial = e21_retencaotipocalc inner join retencaotiporeccgm on e48_retencaotiporec = e21_sequencial inner join retencaopagordem on e23_retencaopagordem = e20_sequencial inner join retencaoempagemov on e27_retencaoreceitas = e23_sequencial inner join empagemov on e81_codmov = e27_empagemov inner join pagordem on e50_codord = e20_pagordem inner join pagordemnota on e71_codord = e50_codord inner join empempenho on e60_numemp = e50_numemp inner join orcdotacao on o58_anousu = e60_anousu and o58_coddot = e60_coddot inner join empnota on e69_codnota = e71_codnota inner join db_usuacgm on db_usuacgm.id_usuario = e50_id_usuario inner join cgm as cgmusu on cgmlogin = cgmusu.z01_numcgm inner join cgm on cgm.z01_numcgm = empempenho.e60_numcgm left join empagemovslips on k107_retencao = e23_sequencial left join slipempagemovslips on k108_empagemovslips = k107_sequencial left join slip on k17_codigo = k108_slip left join empageformacgm on e28_numcgm = empempenho.e60_numcgm left join empagetipo on e83_codtipo = empageformacgm.e28_empagetipo inner join saltes on saltes.k13_conta = coalesce(slip.k17_credito, e83_conta) inner join conplanocontabancaria on c56_reduz = saltes.k13_reduz and c56_anousu = e60_anousu inner join contabancaria on contabancaria.db83_sequencial = conplanocontabancaria.c56_contabancaria inner join bancoagencia on bancoagencia.db89_sequencial = contabancaria.db83_bancoagencia inner join retencaocorgrupocorrente on e47_retencaoreceita = e23_sequencial inner join corgrupocorrente on k105_sequencial = e47_corgrupocorrente inner join corrente on k105_sequencial = e47_corgrupocorrente and k105_id = k12_id and k105_autent = k12_autent and k105_data = k12_data where e50_codord = {$codord}");
    $resultado = pg_fetch_all($sql);
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


public function buscaNovasRetencoes(){
    $di = $this->dtDataInicial;
    $df = $this->dtDataFinal;
    $inst = $this->instit;
    $ano = $this->iAnoUsu;

    $sql = pg_query("SELECT empempenho.e60_numemp::integer as e60_numemp, e60_resumo, e60_destin, e60_codemp, e60_emiss, e60_numcgm, z01_nome, z01_cgccpf, z01_munic, e60_vlremp, e60_vlranu, e60_vlrliq, e63_codhist, e40_descr, e60_vlrpag, e60_anousu, e60_coddot, o58_coddot, o58_orgao, o40_orgao, o40_descr, o58_unidade, o41_descr, o15_codigo, o15_descr, fc_estruturaldotacao(e60_anousu,e60_coddot) as dl_estrutural, e60_codcom, pc50_descr, sum(c70_valor) as c70_valor, c70_data, c70_codlan, c53_tipo, c53_descr, (select o56_elemento||'-'||o56_descr from orcelemento where o56_codele = c67_codele and o56_anousu = c70_anousu) as desdobramento, c72_complem, z01_incest, z01_cgccpf, c66_codnota, e91_numemp from empempenho inner join conlancamemp on c75_numemp = empempenho.e60_numemp inner join conlancam on c70_codlan = c75_codlan left join conlancamnota on c66_codlan = c70_codlan inner join conlancamdoc on c71_codlan = c70_codlan inner join conhistdoc on c53_coddoc = c71_coddoc inner join cgm on cgm.z01_numcgm = empempenho.e60_numcgm inner join db_config on db_config.codigo = empempenho.e60_instit inner join orcdotacao on orcdotacao.o58_anousu = empempenho.e60_anousu and orcdotacao.o58_coddot = empempenho.e60_coddot and orcdotacao.o58_instit = empempenho.e60_instit inner join emptipo on emptipo.e41_codtipo = empempenho.e60_codtipo inner join db_config as a on a.codigo = orcdotacao.o58_instit INNER JOIN origemcomplementorecurso ON origemcomplementorecurso.o206_numero = empempenho.e60_numemp AND origemcomplementorecurso.o206_origem = 1 INNER JOIN orctiporec ON orctiporec.o15_codigo = origemcomplementorecurso.o206_recurso inner join orcfuncao on orcfuncao.o52_funcao = orcdotacao.o58_funcao inner join orcsubfuncao on orcsubfuncao.o53_subfuncao = orcdotacao.o58_subfuncao inner join orcprograma on orcprograma.o54_anousu = orcdotacao.o58_anousu and orcprograma.o54_programa = orcdotacao.o58_programa inner join orcelemento on orcelemento.o56_codele = orcdotacao.o58_codele and orcdotacao.o58_anousu = orcelemento.o56_anousu inner join orcprojativ on orcprojativ.o55_anousu = orcdotacao.o58_anousu and orcprojativ.o55_projativ = orcdotacao.o58_projativ inner join orcorgao on orcorgao.o40_anousu = orcdotacao.o58_anousu and orcorgao.o40_orgao = orcdotacao.o58_orgao inner join orcunidade on orcunidade.o41_anousu = orcdotacao.o58_anousu and orcunidade.o41_orgao = orcdotacao.o58_orgao and orcunidade.o41_unidade = orcdotacao.o58_unidade left join empemphist on empemphist.e63_numemp = empempenho.e60_numemp left join emphist on emphist.e40_codhist = empemphist.e63_codhist inner join pctipocompra on pctipocompra.pc50_codcom = empempenho.e60_codcom left join empresto on e60_numemp = e91_numemp and e60_anousu = e91_anousu left join conlancamcompl on c72_codlan = c70_codlan inner join conlancamele on c67_codlan = c70_codlan where e60_numemp not in (select e91_numemp from empresto where e91_anousu < {$ano}) and c53_coddoc in(161, 6002) and c70_data between '{$di}' and '{$df}' and 1=1 and e60_instit in ({$inst}) group by e60_numemp, e60_resumo, e60_destin, e60_codemp, e60_emiss, e60_numcgm, z01_nome, z01_cgccpf, z01_munic, e60_vlremp, e60_vlranu, e60_vlrliq, e63_codhist, e40_descr, e60_vlrpag, e60_anousu, e60_coddot, o58_coddot, o58_orgao, o40_orgao, o40_descr, o58_unidade, o41_descr, o15_codigo, o15_descr, e60_codcom, pc50_descr, c70_data, c70_codlan, c53_tipo, c53_descr, desdobramento, c72_complem, z01_incest, z01_cgccpf, c66_codnota, e91_numemp order by e60_numemp, c70_codlan");

    $resultado = array();
    while ($linha = pg_fetch_object($sql)) {
        $resultado[] = $linha;
    }
        
    return $resultado;
}

public function voltaDadosRetencao($codord){
    $di = $this->dtDataInicial;
    $df = $this->dtDataFinal;
    $inst = $this->instit;
    $ano = $this->iAnoUsu;

    $sql = pg_query("SELECT e23_sequencial as IdentificadorRetencao, empempenho.e60_codemp as NumeroEmpenho, empempenho.e60_anousu as AnoEmpenho, orcdotacao.o58_orgao as CodigoOrgao, orcdotacao.o58_unidade as CodigoUnidadeOrcamentaria, empnota.e69_numero as NumeroNota, k12_data as DataPagamento, e32_sequencial as Tipo, e23_valorretencao as ValorPago, cgm.z01_nome as NomeCredor, cgm.z01_cgccpf as CNPJCredor, cgmusu.z01_cgccpf as CPFResponsavel, empnota.e69_codnota as IdentificadorLiquidacao, empnota.e69_anousu as AnoLiquidacaoEmpenho, db89_db_bancos as Banco, db89_codagencia as Agencia, db83_conta as ContaBancaria, e60_numemp, e50_codord from retencaoreceitas inner join retencaotiporec on e21_sequencial = e23_retencaotiporec inner join retencaotipocalc on e32_sequencial = e21_retencaotipocalc inner join retencaotiporeccgm on e48_retencaotiporec = e21_sequencial inner join retencaopagordem on e23_retencaopagordem = e20_sequencial inner join retencaoempagemov on e27_retencaoreceitas = e23_sequencial inner join empagemov on e81_codmov = e27_empagemov inner join pagordem on e50_codord = e20_pagordem inner join pagordemnota on e71_codord = e50_codord inner join empempenho on e60_numemp = e50_numemp inner join orcdotacao on o58_anousu = e60_anousu and o58_coddot = e60_coddot inner join empnota on e69_codnota = e71_codnota inner join db_usuacgm on db_usuacgm.id_usuario = e50_id_usuario inner join cgm as cgmusu on cgmlogin = cgmusu.z01_numcgm inner join cgm on cgm.z01_numcgm = empempenho.e60_numcgm left join empagemovslips on k107_retencao = e23_sequencial left join slipempagemovslips on k108_empagemovslips = k107_sequencial left join slip on k17_codigo = k108_slip left join empageformacgm on e28_numcgm = empempenho.e60_numcgm left join empagetipo on e83_codtipo = empageformacgm.e28_empagetipo inner join saltes on saltes.k13_conta = coalesce(slip.k17_credito, e83_conta) inner join conplanocontabancaria on c56_reduz = saltes.k13_reduz and c56_anousu = e60_anousu inner join contabancaria on contabancaria.db83_sequencial = conplanocontabancaria.c56_contabancaria inner join bancoagencia on bancoagencia.db89_sequencial = contabancaria.db83_bancoagencia inner join retencaocorgrupocorrente on e47_retencaoreceita = e23_sequencial inner join corgrupocorrente on k105_sequencial = e47_corgrupocorrente inner join corrente on k105_sequencial = e47_corgrupocorrente and k105_id = k12_id and k105_autent = k12_autent and k105_data = k12_data where e60_instit = {$inst} and e60_anousu = {$ano} and k12_estorn is false and k12_data between '{$di}' and '{$df}' AND e50_codord = {$codord} order by e60_numemp asc, e69_codnota asc");
    $resultado = pg_fetch_all($sql);
    return $resultado;
}


    

    public function gerarDados()
    {
        $ugs = $this->pegaCgmOrdenador();
        $ugs = $this->buscaCPFordenador($ugs);        

        //$retencoes = $this->getRetencoes(); //anterior
        //$retencoes = $this->buscaRetencoes();
        //$retencoes = $this->buscaReceitaExtraOrcamentaria();
        $retencoes = $this->buscaNovasRetencoes();        
        //$this->testa($retencoes); die("Confere II");
        

        if (count($retencoes) == 0) {
            throw new BusinessException('Não há retenções para a competência informada.');
        }

        $EmpenhoConsignacaoRetencao = new stdClass();
        $EmpenhoConsignacaoRetencao->PagamentosDeEmpenhoConsignacoesRetencoes = [];
        
        $guardaempenho = array();


        
        $ix = 1;
        
        foreach ($retencoes as $retencao) {
            //if($retencao["empenho"] != "2106"){continue;}
            
            if($this->iAnoUsu != $retencao->e60_anousu){continue;}

            $dadoscredor = $this->buscaDadosCredor($retencao->c70_codlan);
            $nomecredor = $dadoscredor["z01_nome"];
            $cpfcnpj = $dadoscredor["z01_cgccpf"];
            $dotacao = $this->buscaDotacao($retencao->e60_numemp);
            $dadosdotacao = $this->buscaDadosDotacao($dotacao);

            $codord = $this->buscaCodord2($retencao->c70_codlan);
            $dadosretencao2 = $this->voltaDadosRetencao($codord);
            if(!$dadosretencao2){continue;}
            
            $chave = $retencao->e60_numemp;
            if(in_array($chave, $guardaempenho)){
                continue;
            }
            array_push($guardaempenho, $chave);
            
            
            $valor = 0;
            foreach ($dadosretencao2 as $linha) {                
                $valor += $linha["valorpago"];
            }
            
            
            $ConsigRet = new stdClass();
            $ConsigRet->Identificador = $ix;
            $ConsigRet->CodigoUnidadeGestora = $this->sCodigoTribunal;
            $ConsigRet->Competencia = $this->competencia;
            $ConsigRet->NumeroEmpenho = $retencao->e60_codemp;
            $ConsigRet->AnoEmpenho = $retencao->e60_anousu;
            $ConsigRet->CodigoOrgao = $dadosdotacao["o58_orgao"];
            $ConsigRet->CodigoUnidadeOrcamentaria = $dadosdotacao["o58_unidade"];
            $ConsigRet->NumeroNotaPagamentoDeEmpenhoConsignacaoRetencao = $retencao->c70_codlan;
            $ConsigRet->DataPagamento = $retencao->c70_data;
            $ConsigRet->TipoConsignacaoRetencaoPaga = $this->convertTipoRetencao($dadosretencao2[0]["tipo"]);
            $ConsigRet->ValorPago = $valor;//$retencao->ValorPago;
            $ConsigRet->NomeCredor = utf8_encode($nomecredor);
            $ConsigRet->CpfCnpjCredor = $cpfcnpj;
            $ConsigRet->NaturezaCredor = (strlen($cpfcnpj) == 11) ? 2 : 1;
            $ConsigRet->CPF = $ugs;//$retencao->CPFResponsavel;
            $ConsigRet->LiquidacoesDePagamento = [];
            
            $idx = 1;
            
            foreach ($dadosretencao2 as $linha) {                                
                
                //$dadosbancarios = $this->buscaDadosBancarios4($retencao["seqempenho"], $codord);                

                $Liquidacao = new stdClass();
                $Liquidacao->Identificador = $idx;
                $Liquidacao->NumeroLiquidacaoEmpenho = $linha["e50_codord"];
                $Liquidacao->AnoLiquidacaoEmpenho = $linha["anoliquidacaoempenho"];
                $Liquidacao->ValorConsignadoRetidoLiquidacao = $linha["valorpago"];
                
                //$Liquidacao->ContasPagadoras = [];
                $Conta = new stdClass();
                $Conta->Identificador = $linha["identificadorliquidacao"];
                $Conta->ValorContaPagadora = $linha["valorpago"];
                $Conta->Banco = $linha["banco"];//$dadosbancarios["db90_codban"]; 
                $Conta->Agencia = $linha["agencia"]; //$dadosbancarios["db89_codagencia"]; 
                $Conta->ContaBancaria = $linha["contabancaria"];    //$dadosbancarios["db83_conta"]; 
                
                /*
                if(empty($dadosbancarios["db90_codban"]) && empty($dadosbancarios["db89_codagencia"]) && empty($dadosbancarios["db83_conta"])){
                    $Conta->Banco = "";
                    $Conta->Agencia = "";
                    $Conta->ContaBancaria = "";
                }else{
                    $Conta->Banco = $dadosbancarios["db90_codban"]; //$linha["banco"];
                    $Conta->Agencia = $dadosbancarios["db89_codagencia"]; //$linha["agencia"];
                    $Conta->ContaBancaria = $dadosbancarios["db83_conta"]; //$linha["contabancaria"];    
                }
                */

                
                
                

                $ConsigRet->LiquidacoesDePagamento[] = (object) [
                    'PagamentoDeEmpenhoConsignacaoRetencaoLiquidacaoPagamento' => $Liquidacao
                ];


                $Liquidacao->ContasPagadoras[] = (object) [
                    'PagamentoDeEmpenhoConsignacaoRetencaoLiquidacaoPagamentoContaPagadora' => $Conta
                ];
                $idx++;
                $ix++;
            }
            
            //$this->testa($ConsigRet); die("Foi?2");
            $EmpenhoConsignacaoRetencao->PagamentosDeEmpenhoConsignacoesRetencoes[] = (object) [
                'PagamentoDeEmpenhoConsignacaoRetencao' => $ConsigRet
            ];
        }

        
        $this->aDados = $EmpenhoConsignacaoRetencao;
    }

    private function getRetencoes()
    {
        $query = DB::table('retencaoreceitas')
            ->select([
                'e23_sequencial as IdentificadorRetencao',
                'empempenho.e60_codemp as NumeroEmpenho',
                'empempenho.e60_anousu as AnoEmpenho',
                'orcdotacao.o58_orgao as CodigoOrgao',
                'orcdotacao.o58_unidade as CodigoUnidadeOrcamentaria',
                'empnota.e69_numero as NumeroNota',
                'k12_data as DataPagamento',
                'e32_sequencial as Tipo',
                'e23_valorretencao as ValorPago',
                'cgm.z01_nome as NomeCredor',
                'cgm.z01_cgccpf as CNPJCredor',
                'cgmusu.z01_cgccpf as CPFResponsavel',
                'empnota.e69_codnota as IdentificadorLiquidacao',
                'empnota.e69_anousu as AnoLiquidacaoEmpenho',
                'db89_db_bancos as Banco',
                'db89_codagencia as Agencia',
                'db83_conta as ContaBancaria',
                'e60_numemp',
                'e50_codord',
            ])
            ->join('retencaotiporec', 'e21_sequencial', 'e23_retencaotiporec')
            ->join('retencaotipocalc', 'e32_sequencial', 'e21_retencaotipocalc')
            ->join('retencaotiporeccgm', 'e48_retencaotiporec', 'e21_sequencial')
            ->join('retencaopagordem', 'e23_retencaopagordem', 'e20_sequencial')
            ->join('retencaoempagemov', 'e27_retencaoreceitas', 'e23_sequencial')
            ->join('empagemov', 'e81_codmov', 'e27_empagemov')
            ->join('pagordem', 'e50_codord', 'e20_pagordem')
            ->join('pagordemnota', 'e71_codord', 'e50_codord')
            ->join('empempenho', 'e60_numemp', 'e50_numemp')
            ->join('orcdotacao', function ($join) {
                $join->on('o58_anousu', 'e60_anousu')->on('o58_coddot', 'e60_coddot');
            })
            ->join('empnota', 'e69_codnota', 'e71_codnota')
            ->join('db_usuacgm', 'db_usuacgm.id_usuario', 'e50_id_usuario')
            ->join('cgm as cgmusu', 'cgmlogin', 'cgmusu.z01_numcgm')
            ->join('cgm', 'cgm.z01_numcgm', 'empempenho.e60_numcgm')
            ->leftJoin('empagemovslips', 'k107_retencao', 'e23_sequencial')
            ->leftJoin('slipempagemovslips', 'k108_empagemovslips', 'k107_sequencial')
            ->leftJoin('slip', 'k17_codigo', 'k108_slip')
            ->leftJoin('empageformacgm', 'e28_numcgm', 'empempenho.e60_numcgm')
            ->leftJoin('empagetipo', 'e83_codtipo', 'empageformacgm.e28_empagetipo')
            ->join('saltes', 'saltes.k13_conta', '=', DB::raw("coalesce(slip.k17_credito, e83_conta)"))
            ->join('conplanocontabancaria', function ($join) {
                $join->on('c56_reduz', 'saltes.k13_reduz')->on('c56_anousu', 'e60_anousu');
            })
            ->join('contabancaria', 'contabancaria.db83_sequencial', 'conplanocontabancaria.c56_contabancaria')
            ->join('bancoagencia', 'bancoagencia.db89_sequencial', 'contabancaria.db83_bancoagencia')
            ->join('retencaocorgrupocorrente', 'e47_retencaoreceita', 'e23_sequencial')
            ->join('corgrupocorrente', 'k105_sequencial', 'e47_corgrupocorrente')
            ->join('corrente', function ($join) {
                $join->on('k105_sequencial', 'e47_corgrupocorrente')
                    ->on('k105_id', 'k12_id')
                    ->on('k105_autent', 'k12_autent')
                    ->on('k105_data', 'k12_data');
            })
            //->where('e23_ativo', true)
            ->where('e60_instit', $this->instit)
            ->where('e60_anousu', $this->iAnoUsu)
            ->where('k12_estorn', false)
            ->whereBetween('k12_data', [$this->dtDataInicial, $this->dtDataFinal])
            ->orderBy('e60_numemp')
            ->orderBy('e69_codnota');

            //$sql_with_bindings = str_replace_array('?', $query->getBindings(), $query->toSql());
            //$sql_with_bindings = str_replace("\"", "", $sql_with_bindings);
            //var_dump($sql_with_bindings); die("confere");

        return $query->get();
    }

    private function convertTipoRetencao($tipo)
    {
        switch ($tipo) {
            // Imposto de Renda Retido na Fonte (IRRF)
            case '1':
            case '2':
                return 6;
                // Contribuições Previdenciárias
            case '3':
            case '4':
            case '7':
                return 3;
                // Imposto Sobre Serviços
            case '5':
                return 7;
                // Outras Retenções
            default:
                return 8;
        }
    }
}

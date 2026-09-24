<?php

namespace ECidade\Financeiro\Contabilidade\Exportacao\Sigfis\Arquivos\v2024;

use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Database\Query\JoinClause;

class ArquivoLiquidacaoEmpenhoAdiantamento extends ArquivoBase
{
    protected $sNomeArquivo = 'LiquidacaoEmpenhoAdiantamento';

    public function testa($var){
        echo "<pre>";
        print_r($var);
        echo "</pre>";
    }

    /**
     * Busca os dados para gerar o Arquivo de Unidade Orçamentária
     */
    public function validaTipo($codnota){
        //$sql = pg_query("SELECT * FROM sigfistipodocliquidacao INNER JOIN empnotasigfistipodocliquidacao ON e178_sigfistipodocliquidacao = e177_sequencial WHERE e178_empnota = {$codnota}");
        $sql = pg_query("SELECT e177_codigo, e177_descr, e178_sigfistipodocliquidacao FROM sigfistipodocliquidacao INNER JOIN empnotasigfistipodocliquidacao ON e178_sigfistipodocliquidacao = e177_sequencial WHERE e178_empnota = {$codnota}");
        $resultado = pg_fetch_all($sql);

        if($resultado[0]["e178_sigfistipodocliquidacao"] == 7){
            return true;
        }
        return false;
        //return $resultado;
    }

    public function buscaCPFordenador($cgm){
        $sql = pg_query("SELECT z01_cgccpf FROM cgm WHERE z01_numcgm = {$cgm}");
        $resultado = pg_fetch_all($sql);
        return $resultado[0]["z01_cgccpf"];
    }


    public function voltaLiquidacaoes(){
        $inst = $this->instit;
        $di = $this->dtDataInicial;
        $df = $this->dtDataFinal;
        $ano = $this->iAnoUsu;


        $sql = pg_query("SELECT empempenho.e60_numemp::integer as e60_numemp, e60_resumo, e60_destin, e60_codemp, e60_emiss, e60_numcgm, z01_nome, z01_cgccpf, z01_munic, e60_vlremp, e60_vlranu, e60_vlrliq, e63_codhist, e40_descr, e60_vlrpag, e60_anousu, e60_coddot, o58_coddot, o58_orgao, o40_orgao, o40_descr, o58_unidade, o41_descr, o15_codigo, o15_descr, fc_estruturaldotacao(e60_anousu,e60_coddot) as dl_estrutural, e60_codcom, pc50_descr, sum(c70_valor) as c70_valor, c70_data, c70_codlan, c53_tipo, c53_descr, (select o56_elemento||'-'||o56_descr from orcelemento where o56_codele = c67_codele and o56_anousu = c70_anousu) as desdobramento, c72_complem, z01_incest, z01_cgccpf, c66_codnota, e91_numemp from empempenho inner join conlancamemp on c75_numemp = empempenho.e60_numemp inner join conlancam on c70_codlan = c75_codlan left join conlancamnota on c66_codlan = c70_codlan inner join conlancamdoc on c71_codlan = c70_codlan inner join conhistdoc on c53_coddoc = c71_coddoc inner join cgm on cgm.z01_numcgm = empempenho.e60_numcgm inner join db_config on db_config.codigo = empempenho.e60_instit inner join orcdotacao on orcdotacao.o58_anousu = empempenho.e60_anousu and orcdotacao.o58_coddot = empempenho.e60_coddot and orcdotacao.o58_instit = empempenho.e60_instit inner join emptipo on emptipo.e41_codtipo = empempenho.e60_codtipo inner join db_config as a on a.codigo = orcdotacao.o58_instit INNER JOIN origemcomplementorecurso ON origemcomplementorecurso.o206_numero = empempenho.e60_numemp AND origemcomplementorecurso.o206_origem = 1 INNER JOIN orctiporec ON orctiporec.o15_codigo = origemcomplementorecurso.o206_recurso inner join orcfuncao on orcfuncao.o52_funcao = orcdotacao.o58_funcao inner join orcsubfuncao on orcsubfuncao.o53_subfuncao = orcdotacao.o58_subfuncao inner join orcprograma on orcprograma.o54_anousu = orcdotacao.o58_anousu and orcprograma.o54_programa = orcdotacao.o58_programa inner join orcelemento on orcelemento.o56_codele = orcdotacao.o58_codele and orcdotacao.o58_anousu = orcelemento.o56_anousu inner join orcprojativ on orcprojativ.o55_anousu = orcdotacao.o58_anousu and orcprojativ.o55_projativ = orcdotacao.o58_projativ inner join orcorgao on orcorgao.o40_anousu = orcdotacao.o58_anousu and orcorgao.o40_orgao = orcdotacao.o58_orgao inner join orcunidade on orcunidade.o41_anousu = orcdotacao.o58_anousu and orcunidade.o41_orgao = orcdotacao.o58_orgao and orcunidade.o41_unidade = orcdotacao.o58_unidade left join empemphist on empemphist.e63_numemp = empempenho.e60_numemp left join emphist on emphist.e40_codhist = empemphist.e63_codhist inner join pctipocompra on pctipocompra.pc50_codcom = empempenho.e60_codcom left join empresto on e60_numemp = e91_numemp and e60_anousu = e91_anousu left join conlancamcompl on c72_codlan = c70_codlan inner join conlancamele on c67_codlan = c70_codlan where c53_tipo in (20) and e60_numemp not in (select e91_numemp from empresto where e91_anousu = {$ano}) and c53_coddoc in (3, 23,204, 206, 412) and c70_data between '{$di}' and '{$df}' and 1=1 and e60_instit in ({$inst}) group by e60_numemp, e60_resumo, e60_destin, e60_codemp, e60_emiss, e60_numcgm, z01_nome, z01_cgccpf, z01_munic, e60_vlremp, e60_vlranu, e60_vlrliq, e63_codhist, e40_descr, e60_vlrpag, e60_anousu, e60_coddot, o58_coddot, o58_orgao, o40_orgao, o40_descr, o58_unidade, o41_descr, o15_codigo, o15_descr, e60_codcom, pc50_descr, c70_data, c70_codlan, c53_tipo, c53_descr, desdobramento, c72_complem, z01_incest, z01_cgccpf, c66_codnota, e91_numemp order by e60_numemp, c70_codlan;");
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

public function buscaDadosOrdemProcesso($codord){
    $sql = pg_query("SELECT * FROM pagordem INNER JOIN pagordemprocesso ON e03_pagordem = e50_codord WHERE e50_codord = {$codord}");
    $resultado = pg_fetch_all($sql);
    return $resultado[0];
}

public function buscaDadosOrdemConta($codord){
    $inst = $this->instit;
        $di = $this->dtDataInicial;
        $df = $this->dtDataFinal;
        $ano = $this->iAnoUsu;

    $sql = pg_query("SELECT e03_numeroprocesso, e50_data, e50_anousu, e50_obs, e50_codord, o58_orgao, o58_unidade, e60_codemp, e60_anousu, e60_numemp, e60_numcgm, e69_codnota, e70_valor, cgm_empenho.z01_cgccpf as cpf_responsavel, cgm_empenho.z01_nome as nome_responsavel, cgm_conta.z01_cgccpf as cpf_servidor, cgm_conta.z01_nome as nome_servidor from empempenho inner join empnota on empnota.e69_numemp = empempenho.e60_numemp inner join empnotasigfistipodocliquidacao on e178_empnota = e69_codnota inner join sigfistipodocliquidacao on e177_sequencial = e178_sigfistipodocliquidacao inner join empnotaele on e70_codnota = e69_codnota inner join pagordem on e50_numemp = e69_numemp left join pagordemprocesso on e03_pagordem = e50_codord left join pagordemconta on e49_codord = e50_codord inner join pagordemnota on e71_codnota = e69_codnota and e71_codord = e50_codord left join cgm as cgm_empenho on cgm_empenho.z01_numcgm = e60_numcgm left join cgm as cgm_conta on cgm_conta.z01_numcgm = e49_numcgm inner join orcdotacao on e60_anousu = o58_anousu and e60_coddot = o58_coddot where e60_anousu = {$ano} and e60_instit = {$inst} and e177_codigo = 5 and e69_dtinclusao between '{$di}' and '{$df}' AND e50_codord = {$codord}");
        
        $resultado = pg_fetch_all($sql);
        return $resultado[0];
}

public function buscaDadosEmpNota($codnota, $seqempenho){
    $sql = pg_query("SELECT * FROM empnota WHERE e69_numemp = {$seqempenho} AND e69_codnota = {$codnota}");
    $resultado = pg_fetch_all($sql);
    return $resultado[0];
}

public function buscaDadosEmpNotaEle($codnota, $seqempenho){
    $sql = pg_query("SELECT * FROM empnota INNER JOIN empnotaele ON e70_codnota = e69_codnota WHERE e69_numemp = {$seqempenho} AND e69_codnota = {$codnota}");
    $resultado = pg_fetch_all($sql);
    return $resultado[0];
}


    public function gerarDados()
    {       
        $ugs = $this->pegaCgmOrdenador();
        $ugs = $this->buscaCPFordenador($ugs);
        
        //var_dump($this->dtDataInicial, $this->dtDataFinal); die("Confere");
        $campos = [
            'e03_numeroprocesso',
            'e50_data', 'e50_anousu', 'e50_obs', 'e50_codord',
            'o58_orgao', 'o58_unidade',
            'e60_codemp', 'e60_anousu', 'e60_numemp', 'e60_numcgm', 'e69_codnota',
            'e70_valor',
            'cgm_empenho.z01_cgccpf as cpf_responsavel',
            'cgm_empenho.z01_nome as nome_responsavel',
            'cgm_conta.z01_cgccpf as cpf_servidor',
            'cgm_conta.z01_nome as nome_servidor'
        ];

        /*
        $empenhosx = DB::table('empempenho')
            ->select($campos)
            ->join('empnota', 'empnota.e69_numemp', 'empempenho.e60_numemp')
            ->join('empnotasigfistipodocliquidacao', 'e178_empnota', 'e69_codnota')
            ->join('sigfistipodocliquidacao', 'e177_sequencial', 'e178_sigfistipodocliquidacao')
            ->join('empnotaele', 'e70_codnota', 'e69_codnota')
            ->join('pagordem', 'e50_numemp', 'e69_numemp')
            ->leftJoin('pagordemprocesso', 'e03_pagordem', 'e50_codord')
            ->leftJoin('pagordemconta', 'e49_codord', 'e50_codord')
            ->join('pagordemnota', function (JoinClause $join) {
                $join->on('e71_codnota', 'e69_codnota')
                    ->on('e71_codord', 'e50_codord');
            })
            ->leftJoin('cgm AS cgm_empenho', 'cgm_empenho.z01_numcgm', 'e60_numcgm')
            ->leftJoin('cgm AS cgm_conta', 'cgm_conta.z01_numcgm', 'e49_numcgm')
            ->join('orcdotacao', function ($join) {
                $join->on('e60_anousu', 'o58_anousu')
                    ->on('e60_coddot', 'o58_coddot');
            })
            ->where('e60_anousu', $this->iAnoUsu)
            ->where('e60_instit', $this->instit)
            ->where('e177_codigo', '5')
            ->whereBetween('e69_dtinclusao', [$this->dtDataInicial, $this->dtDataFinal])
            ->get();

            $sql_with_bindings = str_replace_array('?', $empenhosx->getBindings(), $empenhosx->toSql());            
            $sql_with_bindings = str_replace('"', '', $sql_with_bindings);
            var_dump($sql_with_bindings);
            die("Confere");
            */

            $empenhos = $this->voltaLiquidacaoes();

        //if ($empenhos->isEmpty()) {
        if(count($empenhos) == 0){
            throw new \Exception('Não foi encontrardo nenhum empenho para o arquivo de Remessa ');
        }

        $obj = new \stdClass();
        $obj->LiquidacoesEmpenhoAdiantamentos = [];

        foreach ($empenhos as $empenho) {
            
            //$vt = $this->validaTipo($empenho->e69_codnota);
            $vt = $this->validaTipo($empenho->c66_codnota);            
            if(!$vt){continue;}

            $codord = $this->buscaCodord2($empenho->c70_codlan);
            
            //if($empenho->e60_codemp != 487){continue;}

            $dadosordem = $this->buscaDadosOrdem($codord);
            $dadosordemprocesso = $this-> buscaDadosOrdemProcesso($codord);
            $dadosordemconta = $this->buscaDadosOrdemConta($codord);
            $dadosempnota = $this->buscaDadosEmpNota($empenho->c66_codnota, $empenho->e60_numemp);
            $dadosempnotaelemento = $this->buscaDadosEmpNotaEle($dadosempnota["e69_codnota"], $empenho->e60_numemp);
            
            /*
            $this->testa($dadosordem); 
            $this->testa($dadosordemprocesso); 
            $this->testa($dadosordemconta); 
            $this->testa($dadosempnota); 
            $this->testa($dadosempnotaelemento); 
            die("Am");
            */

            $dadosLiquidacaoEmpenho = (object)[
                'Identificador' => $dadosempnota["e69_codnota"],
                'CodigoUnidadeGestora' => $this->sCodigoTribunal,
                'NumeroEmpenho' => $empenho->e60_codemp,
                'AnoEmpenho' => $empenho->e60_anousu,
                'NumeroLiquidacaoEmpenho' => $codord,
                'Competencia' => $this->competencia,
                'CpfResponsavel' => $ugs,//$empenho->cpf_responsavel,
                'CpfServidor' => (empty($dadosordemconta["cpf_servidor"])) ? $dadosordemconta["cpf_responsavel"] : $dadosordemconta["cpf_servidor"],
                'NomeServidor' => (empty($dadosordemconta["nome_servidor"])) ? $dadosordemconta["nome_responsavel"] : $dadosordemconta["nome_servidor"],
                'NumeroProcessoAdministrativo' => $dadosordemprocesso["e03_numeroprocesso"],
                'ValorConcedido' => $dadosempnotaelemento["e70_valor"],
                'DataConcessao' => $dadosordem["e50_data"],
                'DataLimite' => $dadosordem["e50_data"],
                'Finalidade' => utf8_encode(substr($dadosordem["e50_obs"], 0, 254)),
                'AnoLiquidacaoDeEmpenho' => $dadosordem["e50_anousu"],
                'CodigoOrgao' => $empenho->o58_orgao,
                'CodigoUnidadeOrcamentaria' => $empenho->o58_unidade
            ];

            $obj->LiquidacoesEmpenhoAdiantamentos[] = (object)[
                'LiquidacaoEmpenhoAdiantamento' => $dadosLiquidacaoEmpenho
            ];
        }

        $this->aDados = $obj;
    }
}

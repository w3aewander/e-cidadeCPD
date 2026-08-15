<?php


namespace ECidade\Financeiro\Contabilidade\Exportacao\Sigfis\Arquivos\v2024;

use ECidade\Financeiro\Contabilidade\Exportacao\Sigfis\Common\Helper;
use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Database\Query\JoinClause;
use stdClass;

class ArquivoLiquidacaoEmpenhoDiaria extends ArquivoBase
{   
    protected $sNomeArquivo = 'LiquidacaoEmpenhoDiaria';

    public function validaTipo($codnota){
        $sql = pg_query("SELECT e177_codigo, e177_descr FROM sigfistipodocliquidacao INNER JOIN empnotasigfistipodocliquidacao ON e178_sigfistipodocliquidacao = e177_sequencial WHERE e178_empnota = {$codnota}");
        $resultado = pg_fetch_all($sql);
        return ($resultado[0]["e177_codigo"] == 4) ? true : false;
    }

    public function buscaPessoa($codord){
        $sql = pg_query("SELECT z01_nome, e49_codord, e49_numcgm, z01_cgccpf FROM pagordemconta INNER JOIN cgm ON e49_numcgm = z01_numcgm WHERE e49_codord = {$codord}");
        $resultado = pg_fetch_all($sql);
        return $resultado[0];
    }

    public function buscaDadosDiaria($codord){
        $sql = pg_query("SELECT e60_numemp, e60_codemp, e60_anousu, o58_orgao, o58_unidade, e69_codnota, e69_anousu, e70_vlrliq, e50_obs, e69_anousu, e82_codmov, (SELECT z01_cgccpf FROM pagordemconta INNER JOIN cgm ON e49_numcgm = z01_numcgm WHERE e49_codord = e50_codord) as cpf, (SELECT z01_nome FROM pagordemconta INNER JOIN cgm ON e49_numcgm = z01_numcgm WHERE e49_codord = e50_codord) as nome, e69_dtinclusao, e60_instit from empempenho inner join empnota on e69_numemp = e60_numemp inner join empnotaele on e70_codnota = e69_codnota inner join pagordem on e50_numemp = e60_numemp inner join pagordemnota on e71_codnota = e69_codnota and e71_codord = e50_codord inner join empenho.empord on e82_codord = e50_codord inner join db_usuacgm on id_usuario = e50_id_usuario inner join cgm as cgmusu on cgmusu.z01_numcgm = cgmlogin inner join orcdotacao on o58_anousu = e60_anousu and o58_coddot = e60_coddot inner join empelemento on e64_numemp = e60_numemp inner join conplanoorcamento on c60_codcon = e64_codele and c60_anousu = e60_anousu where e50_codord = {$codord}");
        $resultado = pg_fetch_all($sql);
        return $resultado[0];
    }

    public function buscaMatricula($cpf){
        $ano = $this->iAnoUsu;
        $mes = substr($this->competencia, 4, 2);        
        
        $sql = pg_query("SELECT distinct rhpessoal.rh01_regist, rhpessoal.rh01_numcgm, cgm.z01_nome, cgm.z01_cgccpf, rh01_admiss, rh05_recis, rh37_descr, rh30_vinculoemprego::int from rhpessoal left join rhpessoalmov on rhpessoalmov.rh02_regist = rhpessoal.rh01_regist and rhpessoalmov.rh02_anousu = {$ano} and rhpessoalmov.rh02_mesusu = {$mes} left join rhlota on rhlota.r70_codigo = rhpessoalmov.rh02_lota and rhlota.r70_instit = rhpessoalmov.rh02_instit inner join cgm on cgm.z01_numcgm = rhpessoal.rh01_numcgm left join rhregime on rh30_codreg = rh02_codreg left join rhfuncao on rhfuncao.rh37_funcao = rhpessoalmov.rh02_funcao and rhfuncao.rh37_instit = rhpessoalmov.rh02_instit left join rhpesrescisao on rh02_seqpes = rh05_seqpes left join rhcontratoemergencial on rhcontratoemergencial.rh163_matricula = rhpessoal.rh01_regist where z01_cgccpf = '{$cpf}' AND rh37_descr is not null ORDER BY rh05_recis desc, rh01_admiss desc LIMIT 1");
        $resultado = pg_fetch_all($sql);
        return substr($resultado[0]["rh01_regist"], 2);
        //return $resultado[0]["rh01_regist"];
    }

    public function buscaDadosPlanilha($matricula, $mes){        
        $sql = pg_query("SELECT * FROM auxdiariaantigo WHERE FUN_MATRICULA = '{$matricula}' AND GUIA_DATA <= '{$mes}'");
        $resultado = pg_fetch_all($sql);
        return $resultado[0];
    }

    //public function buscaDadosPlanilha2($matricula, $mes, $valor, $datainclusao){
    public function buscaDadosPlanilha2($matricula, $mes, $valor){
        //$sql = pg_query("SELECT * FROM auxdiariaantigo WHERE FUN_MATRICULA = '{$matricula}' AND GUIA_DATA <= '{$mes}' AND guia_valor = '{$valor}' AND guia_data_inclusao = '{$datainclusao}'  ");
        $sql = pg_query("SELECT * FROM auxdiariaantigo WHERE FUN_MATRICULA = '{$matricula}' AND GUIA_DATA <= '{$mes}' AND guia_valor = '{$valor}'");
        $resultado = pg_fetch_all($sql);
        return $resultado[0];
    }

    public function buscaDadosPlanilha45($matricula, $mes){
        $sql = pg_query("SELECT * FROM auxdiariaantigo45 WHERE FUN_MATRICULA = '{$matricula}' AND GUIA_DATA2 <= '{$mes}'");
        $resultado = pg_fetch_all($sql);
        return $resultado[0];
    }

    public function buscaCPFnovo($cpf, $valor){
        $sql = pg_query("SELECT * FROM auxdiariaantigo45 WHERE cpf = '{$cpf}' AND guia_valor = '{$valor}'");
        $resultado = pg_fetch_all($sql);
        return $resultado[0];
    }

    public function buscaOutrosDadosPlanilha($matricula, $mes){
        $sql = pg_query("SELECT * FROM auxdiariasetembro WHERE MATRICULA = '{$matricula}' AND DATA <= '{$mes}'");
        $resultado = pg_fetch_all($sql);
        return $resultado[0];
    }

    public function buscaDadosPlanilhaNov($matricula, $mes){
        $sql = pg_query("SELECT * FROM auxdiariatodosnov WHERE MATRICULA = '{$matricula}' AND DATA <= '{$mes}'");
        $resultado = pg_fetch_all($sql);
        return $resultado[0];
    }

    public function buscaDadosPlanilhaDez($matricula, $mes){
        $sql = pg_query("SELECT * FROM auxdiariadezembro WHERE MATRICULA = '{$matricula}' AND DATA <= '{$mes}'");
        $resultado = pg_fetch_all($sql);
        return $resultado[0];
    }


    public function buscaPlanilha45Setembro($matricula, $mes){
        $sql = pg_query("SELECT * FROM auxdiaria45setembro WHERE FUN_MATRICULA = '{$matricula}' AND GUIA_DATA <= '{$mes}'");
        $resultado = pg_fetch_all($sql);
        return $resultado[0];
    }

    public function buscaTudoPlanilha45SetembroCPF($cpf){
        $sql = pg_query("SELECT * FROM auxdiaria45setembro WHERE cpf = '{$cpf}' ORDER BY id");
        $resultado = pg_fetch_all($sql);
        return $resultado;
    }

    public function buscaTudoPlanilha45OutubroCPF($cpf){
        $sql = pg_query("SELECT * FROM auxdiaria45outubro WHERE cpf = '{$cpf}' ORDER BY id");
        $resultado = pg_fetch_all($sql);
        return $resultado;
    }

    public function buscaTudoPlanilha45NovembroCPF($cpf){
        $sql = pg_query("SELECT * FROM auxdiaria45novembro WHERE cpf = '{$cpf}' ORDER BY id");
        $resultado = pg_fetch_all($sql);
        return $resultado;
    }

    public function buscaTudoPlanilha45DezembroCPF($cpf){
        $sql = pg_query("SELECT * FROM auxdiaria45dezembro WHERE cpf = '{$cpf}' ORDER BY id");
        $resultado = pg_fetch_all($sql);
        return $resultado;
    }
    
    

    public function testa($var){
        echo "<pre>";
        print_r($var);
        echo "</pre>";
    }

    public function arrumaUF($estado) {
        switch (strtoupper($estado)) {
            case 'ACRE':
                return 'AC';
            case 'ALAGOAS':
                return 'AL';
            case 'AMAPÁ':
            case 'AMAPA':
                return 'AP';
            case 'AMAZONAS':
                return 'AM';
            case 'BAHIA':
                return 'BA';
            case 'CEARÁ':
            case 'CEARA':
                return 'CE';
            case 'DISTRITO FEDERAL':
                return 'DF';
            case 'ESPÍRITO SANTO':
            case 'ESPiRITO SANTO':
                return 'ES';
            case 'GOIÁS':
            case 'GOIAS':
                return 'GO';
            case 'MARANHÃO':
            case 'MARANHAO':
                return 'MA';
            case 'MATO GROSSO':
                return 'MT';
            case 'MATO GROSSO DO SUL':
                return 'MS';
            case 'MINAS GERAIS':
                return 'MG';
            case 'PARÁ':
            case 'PARA':
                return 'PA';
            case 'PARAÍBA':
            case 'PARAIBA':
                return 'PB';
            case 'PARANÁ':
            case 'PARANA':
                return 'PR';
            case 'PERNAMBUCO':
                return 'PE';
            case 'PIAUÍ':
            case 'PIAUI':
                return 'PI';
            case 'RIO DE JANEIRO':
                return 'RJ';
            case 'RIO GRANDE DO NORTE':
                return 'RN';
            case 'RIO GRANDE DO SUL':
                return 'RS';
            case 'RONDÔNIA':
            case 'RONDONIA':
                return 'RO';
            case 'RORAIMA':
                return 'RR';
            case 'SANTA CATARINA':
                return 'SC';
            case 'SÃO PAULO':
            case 'SAO PAULO':
                return 'SP';
            case 'SERGIPE':
                return 'SE';
            case 'TOCANTINS':
                return 'TO';
            default:
                return 'RJ';
    }
}




public function voltaLiquidacaoes(){
        $inst = $this->instit;
        $di = $this->dtDataInicial;
        $df = $this->dtDataFinal;
        $ano = $this->iAnoUsu;
        
        $sql = pg_query("SELECT empempenho.e60_numemp::integer as e60_numemp, e60_resumo, e60_destin, e60_codemp, e60_emiss, e60_numcgm, z01_nome, z01_cgccpf, z01_munic, e60_vlremp, e60_vlranu, e60_vlrliq, e63_codhist, e40_descr, e60_vlrpag, e60_anousu, e60_coddot, o58_coddot, o58_orgao, o40_orgao, o40_descr, o58_unidade, o41_descr, o15_codigo, o15_descr, fc_estruturaldotacao(e60_anousu,e60_coddot) as dl_estrutural, e60_codcom, pc50_descr, sum(c70_valor) as c70_valor, c70_data, c70_codlan, c53_tipo, c53_descr, (select o56_elemento||'-'||o56_descr from orcelemento where o56_codele = c67_codele and o56_anousu = c70_anousu) as desdobramento, c72_complem, z01_incest, z01_cgccpf, c66_codnota, e91_numemp from empempenho inner join conlancamemp on c75_numemp = empempenho.e60_numemp inner join conlancam on c70_codlan = c75_codlan left join conlancamnota on c66_codlan = c70_codlan inner join conlancamdoc on c71_codlan = c70_codlan inner join conhistdoc on c53_coddoc = c71_coddoc inner join cgm on cgm.z01_numcgm = empempenho.e60_numcgm inner join db_config on db_config.codigo = empempenho.e60_instit inner join orcdotacao on orcdotacao.o58_anousu = empempenho.e60_anousu and orcdotacao.o58_coddot = empempenho.e60_coddot and orcdotacao.o58_instit = empempenho.e60_instit inner join emptipo on emptipo.e41_codtipo = empempenho.e60_codtipo inner join db_config as a on a.codigo = orcdotacao.o58_instit INNER JOIN origemcomplementorecurso ON origemcomplementorecurso.o206_numero = empempenho.e60_numemp AND origemcomplementorecurso.o206_origem = 1 INNER JOIN orctiporec ON orctiporec.o15_codigo = origemcomplementorecurso.o206_recurso inner join orcfuncao on orcfuncao.o52_funcao = orcdotacao.o58_funcao inner join orcsubfuncao on orcsubfuncao.o53_subfuncao = orcdotacao.o58_subfuncao inner join orcprograma on orcprograma.o54_anousu = orcdotacao.o58_anousu and orcprograma.o54_programa = orcdotacao.o58_programa inner join orcelemento on orcelemento.o56_codele = orcdotacao.o58_codele and orcdotacao.o58_anousu = orcelemento.o56_anousu inner join orcprojativ on orcprojativ.o55_anousu = orcdotacao.o58_anousu and orcprojativ.o55_projativ = orcdotacao.o58_projativ inner join orcorgao on orcorgao.o40_anousu = orcdotacao.o58_anousu and orcorgao.o40_orgao = orcdotacao.o58_orgao inner join orcunidade on orcunidade.o41_anousu = orcdotacao.o58_anousu and orcunidade.o41_orgao = orcdotacao.o58_orgao and orcunidade.o41_unidade = orcdotacao.o58_unidade left join empemphist on empemphist.e63_numemp = empempenho.e60_numemp left join emphist on emphist.e40_codhist = empemphist.e63_codhist inner join pctipocompra on pctipocompra.pc50_codcom = empempenho.e60_codcom left join empresto on e60_numemp = e91_numemp and e60_anousu = e91_anousu left join conlancamcompl on c72_codlan = c70_codlan inner join conlancamele on c67_codlan = c70_codlan where c53_tipo in (20) and e60_numemp not in (select e91_numemp from empresto where e91_anousu = {$ano}) and c53_coddoc in (3, 23,204, 206, 412) and c70_data between '{$di}' and '{$df}' and 1=1 and e60_instit in ({$inst}) group by e60_numemp, e60_resumo, e60_destin, e60_codemp, e60_emiss, e60_numcgm, z01_nome, z01_cgccpf, z01_munic, e60_vlremp, e60_vlranu, e60_vlrliq, e63_codhist, e40_descr, e60_vlrpag, e60_anousu, e60_coddot, o58_coddot, o58_orgao, o40_orgao, o40_descr, o58_unidade, o41_descr, o15_codigo, o15_descr, e60_codcom, pc50_descr, c70_data, c70_codlan, c53_tipo, c53_descr, desdobramento, c72_complem, z01_incest, z01_cgccpf, c66_codnota, e91_numemp order by e60_numemp, c70_codlan");
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

    public function buscaDadosDiaria2($codord){
        $inst = $this->instit;
        $di = $this->dtDataInicial;
        $df = $this->dtDataFinal;
        $ano = $this->iAnoUsu;

        $sql = pg_query("SELECT e60_numemp, e60_codemp, e60_anousu, o58_orgao, o58_unidade, e69_codnota, e69_anousu, e70_vlrliq, e50_obs, e50_codord, e69_anousu, cgmusu.z01_cgccpf, e82_codmov, e46_nome, e46_cpf, e446_sequencial, e446_quantidade, e446_datainicio, e446_datafim, e446_tipodiaria, e446_estadodestino, e446_destino, e446_paisdestino, e446_motivo from empempenho inner join empnota on e69_numemp = e60_numemp inner join empnotaele on e70_codnota = e69_codnota inner join pagordem on e50_numemp = e60_numemp inner join pagordemnota on e71_codnota = e69_codnota and e71_codord = e50_codord inner join empenho.empord on e82_codord = e50_codord inner join emppresta on e45_codmov = e82_codmov inner join empprestaitem on e46_numemp = e60_numemp and e46_emppresta = e45_sequencial inner join empprestaitemdiaria on e446_empprestaitem = e46_codigo inner join db_usuacgm on id_usuario = e50_id_usuario inner join cgm as cgmusu on cgmusu.z01_numcgm = cgmlogin left join empnotasigfistipodocliquidacao on empnotasigfistipodocliquidacao.e178_empnota = empnota.e69_codnota left join sigfistipodocliquidacao on sigfistipodocliquidacao.e177_sequencial = empnotasigfistipodocliquidacao.e178_sigfistipodocliquidacao inner join orcdotacao on o58_anousu = e60_anousu and o58_coddot = e60_coddot inner join empelemento on e64_numemp = e60_numemp inner join conplanoorcamento on c60_codcon = e64_codele and c60_anousu = e60_anousu where e177_codigo = 4 and e70_vlranu = 0 and e60_anousu = {$ano} and e60_instit = {$inst} and e69_dtinclusao between '{$di}' and '{$df}' and e70_vlrliq != 0 AND e50_codord = {$codord} order by e60_numemp asc");
        $resultado = pg_fetch_all($sql);
        return $resultado[0];
    }

    



    

    public function gerarDados(){
       $velho = false;
        
        if($this->competencia == 202401 || $this->competencia == 202402 || $this->competencia == 202403 || $this->competencia == 202404 || $this->competencia == 202405 || $this->competencia == 202406 || $this->competencia == 202407 || $this->competencia == 202408 || $this->competencia == 202409 || $this->competencia == 202410 || $this->competencia == 202411 || $this->competencia == 202412 ){
       
            $velho = true;            
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
            ->where('e177_codigo', 4)
            ->where('e70_vlranu', '=', 0)
            ->where('e60_instit', $this->instit)
            ->where('e60_anousu', $this->iAnoUsu)
            ->whereBetween('e69_dtinclusao', [$this->dtDataInicial, $this->dtDataFinal])
            ->orderBy('e69_dtinclusao')
            ->get();
            //$sql_with_bindings = str_replace_array('?', $empenhos->getBindings(), $empenhos->toSql());
            //$sql_with_bindings = str_replace("\"", "", $sql_with_bindings);
            //var_dump($sql_with_bindings); die("confere");

        }else{            
            
            $campos = [
                'e60_numemp',
                'e60_codemp',
                'e60_anousu',
                'o58_orgao',
                'o58_unidade',
                'e69_codnota',
                'e69_anousu',
                'e70_vlrliq',
                'e50_obs',
                'e50_codord',
                'e69_anousu',            
                'cgmusu.z01_cgccpf',            
                'e82_codmov',
                'e46_nome',
                'e446_sequencial',
                'e446_quantidade',
                'e446_datainicio',
                'e446_datafim',
                'e446_tipodiaria',
                'e446_estadodestino',
                'e446_destino',
                'e446_paisdestino',
                'e446_motivo'
            ];
            
            $empenhosx = DB::table('empempenho')
                ->select($campos)
                ->join('empnota', 'e69_numemp', 'e60_numemp')
                ->join('empnotaele', 'e70_codnota', 'e69_codnota')
                ->join('pagordem', 'e50_numemp', 'e60_numemp')
                ->join('pagordemnota', function (JoinClause $join) {
                    $join->on('e71_codnota', 'e69_codnota')
                        ->on('e71_codord', 'e50_codord');
                })
                ->join('empenho.empord', 'e82_codord', 'e50_codord')
                ->join('emppresta', 'e45_codmov', 'e82_codmov')
                ->join('empprestaitem', function (JoinClause $join) {
                    $join->on('e46_numemp', 'e60_numemp')
                        ->on('e46_emppresta', 'e45_sequencial');
                })
                ->join('empprestaitemdiaria', 'e446_empprestaitem', 'e46_codigo')            
                ->join('db_usuacgm', 'id_usuario', 'e50_id_usuario')
                ->join('cgm AS cgmusu', 'cgmusu.z01_numcgm', 'cgmlogin')
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
                ->join('orcdotacao', function (JoinClause $join) {
                    $join->on('o58_anousu', 'e60_anousu')
                        ->on('o58_coddot', 'e60_coddot');
                })
                ->join('empelemento', 'e64_numemp', 'e60_numemp')
                ->join('conplanoorcamento', function (JoinClause $join) {
                    $join->on('c60_codcon', 'e64_codele')
                        ->on('c60_anousu', 'e60_anousu');
                })
                
                //->join('planodespesaconplanoorcamento', 'conplanoorcamento_codigo', 'c60_codigo')
                //->join('planodespesa', function (JoinClause $join) {
                //    $join->on('planodespesa.id', 'planodespesa_id')
                //        ->on('exercicio', 'e60_anousu')
                //        ->where('uniao', '=', 't')
                //        ->whereRaw("
                //            ( elemento in ('14', '15') or
                //              conta in ('33903646', '33909214', '33909215', '44909214', '44909215'))
                //        ");
                //})
                ->where('e177_codigo', 4)
                ->where('e70_vlranu', '=', 0)
                ->where('e60_anousu', $this->iAnoUsu)
                ->where('e60_instit', $this->instit)
                ->whereBetween('e69_dtinclusao', [$this->dtDataInicial, $this->dtDataFinal])
                ->where('e70_vlrliq', '!=', 0)
                ->orderBy('e60_numemp')
                ->get();
                //$sql_with_bindings = str_replace_array('?', $empenhosx->getBindings(), $empenhosx->toSql());
                //$sql_with_bindings = str_replace("\"", "", $sql_with_bindings);
                //var_dump($sql_with_bindings); die("confere");
                
                

                $empenhos = $this->voltaLiquidacaoes();
                //$this->testa($empenhos); die("Foi");



        }//else da competência

            

        if(count($empenhos) == 0){
            throw new \Exception('Não foi encontrado nenhum empenho para o arquivo da Remessa.');
        }

        $obj = new stdClass();
        $obj->LiquidacoesEmpenhoDiarias = [];

        //$this->testa($empenhos); die("Confere 78266785791");
        $ix = 1;
        $guarda45set = array();
        
        foreach ($empenhos as $empenho) {
            
            //if($empenho->e60_codemp == 462){continue;}
            
            $dadospessoa = $this->buscaPessoa($empenho->e50_codord);
            $dadosdiaria = $this->buscaDadosDiaria($empenho->e50_codord);

            if($velho){

                if($this->instit == 45){
                    $matriculax = $this->buscaMatricula($dadospessoa["z01_cgccpf"]);                    
                    //if($dadospessoa["z01_cgccpf"] != "03308918714"){continue;}                    
                
                    if(substr($matriculax, 0, 3) == "000"){
                        $matriculax = substr($matriculax, 3);
                    }elseif(substr($matriculax, 0, 2) == "00"){
                        $matriculax = substr($matriculax, 2);
                    }elseif(substr($matriculax, 0, 1) == "0"){                    
                        $matriculax = substr($matriculax, 1);
                    }
                
                    if(substr($this->competencia, 4, 2) == "02"){
                        $diax = "29";
                    }elseif(substr($this->competencia, 4, 2) == "01" ||substr($this->competencia, 4, 2) == "03" ||substr($this->competencia, 4, 2) == "05" ||substr($this->competencia, 4, 2) == "07"){
                        $diax = "31";
                    }else{
                        $diax = "30";
                    }

                    $mesx = substr($this->competencia, 0, 4) ."-". substr($this->competencia, 4, 2) . "-" . $diax;
                    
                    $planilha = $this->buscaDadosPlanilha45($matriculax, $mesx);
                    $planilha = $this->buscaCPFnovo($dadosdiaria["cpf"], $dadosdiaria["e70_vlrliq"]);                    
                    $empenho->e446_sequencial = $planilha["guia_numero"].$planilha["guia_ano"];
                    $empenho->e446_quantidade = 1;
                    $empenho->e446_datainicio = $planilha["guia_data"];
                    $empenho->e446_datafim = $planilha["guia_dataretorno"];
                    $empenho->e446_estadodestino = $planilha["guia_ufdestino"];
                    $empenho->e446_destino = $planilha["guia_destino"];
                    $empenho->e446_paisdestino = "Brasil";
                    $empenho->e446_motivo = $planilha["guia_motivo"];
                    $empenho->z01_cgccpf = $dadospessoa["z01_cgccpf"];
                    $empenho->e46_nome = $dadospessoa["z01_nome"];
                    $empenho->e70_vlrliq = $dadosdiaria["e70_vlrliq"];

                    if($this->competencia == 202409){
                        
                        //$planilha2 = $this->buscaPlanilha45Setembro($matriculax, $mesx);
                        //$planilha2 = $this->buscaTudoPlanilha45Setembro();
                        array_push($guarda45set, $dadospessoa["z01_cgccpf"]);
                        $conta = array_count_values($guarda45set);
                        $chave = $conta[$dadospessoa["z01_cgccpf"]] - 1;
                        $preplanilha = $this->buscaTudoPlanilha45SetembroCPF($dadospessoa["z01_cgccpf"]);
                        $planilha2 = $preplanilha[$chave];
                                                
                        $empenho->e446_sequencial = $planilha2["guia_numero"];
                        $empenho->e446_quantidade = 1; //$planilha2["diarias"];
                        $empenho->e446_datainicio = $planilha2["guia_data"];
                        $empenho->e446_datafim = $planilha2["guia_dataretorno"];
                        $empenho->e446_estadodestino = $planilha2["uf"];
                        $empenho->e446_destino = $planilha2["guia_destino"];
                        $empenho->e446_paisdestino = "Brasil";
                        $empenho->e446_motivo = $planilha2["guia_motivo"];
                        $empenho->z01_cgccpf = $dadospessoa["z01_cgccpf"];
                        $empenho->e46_nome = $dadospessoa["z01_nome"];
                        $empenho->e70_vlrliq = $planilha2["guia_valor"];
                    }//IF DE SETEMBRO

                    if($this->competencia == 202410){
                        
                        //$planilha2 = $this->buscaPlanilha45Setembro($matriculax, $mesx);
                        //$planilha2 = $this->buscaTudoPlanilha45Setembro();
                        array_push($guarda45set, $dadospessoa["z01_cgccpf"]);
                        $conta = array_count_values($guarda45set);
                        $chave = $conta[$dadospessoa["z01_cgccpf"]] - 1;

                        if(strlen($dadospessoa["z01_cgccpf"]) == 8){
                            $dadospessoa["z01_cgccpf"] = "000" . $dadospessoa["z01_cgccpf"];
                        }elseif(strlen($dadospessoa["z01_cgccpf"]) == 9){
                            $dadospessoa["z01_cgccpf"] = "00" . $dadospessoa["z01_cgccpf"];
                        }elseif(strlen($dadospessoa["z01_cgccpf"]) == 10){
                            $dadospessoa["z01_cgccpf"] = "0" . $dadospessoa["z01_cgccpf"];
                        }

                        $preplanilha = $this->buscaTudoPlanilha45OutubroCPF($dadospessoa["z01_cgccpf"]);
                        $planilha2 = $preplanilha[$chave];

                                                                        
                        $empenho->e446_sequencial = $planilha2["guia_numero"];
                        $empenho->e446_quantidade = $planilha2["qtd_diaris"];
                        $empenho->e446_datainicio = $planilha2["guia_data"];
                        $empenho->e446_datafim = $planilha2["guia_dataretorno"];
                        $empenho->e446_estadodestino = $planilha2["uf"];
                        $empenho->e446_destino = $planilha2["guia_destino"];
                        $empenho->e446_paisdestino = "Brasil";
                        $empenho->e446_motivo = $planilha2["guia_motivo"];
                        $empenho->z01_cgccpf = $dadospessoa["z01_cgccpf"];
                        $empenho->e46_nome = $dadospessoa["z01_nome"];
                        $empenho->e70_vlrliq = $planilha2["guia_valor"];
                    }//IF DE OUTUBRO

                    if($this->competencia == 202411){
                        
                        //$planilha2 = $this->buscaPlanilha45Setembro($matriculax, $mesx);
                        //$planilha2 = $this->buscaTudoPlanilha45Setembro();
                        array_push($guarda45set, $dadospessoa["z01_cgccpf"]);
                        $conta = array_count_values($guarda45set);
                        $chave = $conta[$dadospessoa["z01_cgccpf"]] - 1;

                        if(strlen($dadospessoa["z01_cgccpf"]) == 8){
                            $dadospessoa["z01_cgccpf"] = "000" . $dadospessoa["z01_cgccpf"];
                        }elseif(strlen($dadospessoa["z01_cgccpf"]) == 9){
                            $dadospessoa["z01_cgccpf"] = "00" . $dadospessoa["z01_cgccpf"];
                        }elseif(strlen($dadospessoa["z01_cgccpf"]) == 10){
                            $dadospessoa["z01_cgccpf"] = "0" . $dadospessoa["z01_cgccpf"];
                        }

                        $preplanilha = $this->buscaTudoPlanilha45NovembroCPF($dadospessoa["z01_cgccpf"]);
                        $planilha2 = $preplanilha[$chave];

                                                                        
                        $empenho->e446_sequencial = $planilha2["guia_numero"];
                        $empenho->e446_quantidade = $planilha2["qtd_diaris"];
                        $empenho->e446_datainicio = $planilha2["guia_data"];
                        $empenho->e446_datafim = $planilha2["guia_dataretorno"];
                        $empenho->e446_estadodestino = $planilha2["uf"];
                        $empenho->e446_destino = $planilha2["guia_destino"];
                        $empenho->e446_paisdestino = "Brasil";
                        $empenho->e446_motivo = $planilha2["guia_motivo"];
                        $empenho->z01_cgccpf = $dadospessoa["z01_cgccpf"];
                        $empenho->e46_nome = $dadospessoa["z01_nome"];
                        $empenho->e70_vlrliq = $planilha2["guia_valor"];
                    }//IF DE NOVEMBRO

                    if($this->competencia == 202412){
                        
                        //$planilha2 = $this->buscaPlanilha45Setembro($matriculax, $mesx);
                        //$planilha2 = $this->buscaTudoPlanilha45Setembro();
                        array_push($guarda45set, $dadospessoa["z01_cgccpf"]);
                        $conta = array_count_values($guarda45set);
                        $chave = $conta[$dadospessoa["z01_cgccpf"]] - 1;

                        if(strlen($dadospessoa["z01_cgccpf"]) == 8){
                            $dadospessoa["z01_cgccpf"] = "000" . $dadospessoa["z01_cgccpf"];
                        }elseif(strlen($dadospessoa["z01_cgccpf"]) == 9){
                            $dadospessoa["z01_cgccpf"] = "00" . $dadospessoa["z01_cgccpf"];
                        }elseif(strlen($dadospessoa["z01_cgccpf"]) == 10){
                            $dadospessoa["z01_cgccpf"] = "0" . $dadospessoa["z01_cgccpf"];
                        }

                        $preplanilha = $this->buscaTudoPlanilha45DezembroCPF($dadospessoa["z01_cgccpf"]);
                        $planilha2 = $preplanilha[$chave];

                                                                        
                        $empenho->e446_sequencial = $planilha2["guia_numero"];
                        $empenho->e446_quantidade = $planilha2["qtd_diaris"];
                        $empenho->e446_datainicio = $planilha2["guia_data"];
                        $empenho->e446_datafim = $planilha2["guia_dataretorno"];
                        $empenho->e446_estadodestino = $planilha2["uf"];
                        $empenho->e446_destino = $planilha2["guia_destino"];
                        $empenho->e446_paisdestino = "Brasil";
                        $empenho->e446_motivo = $planilha2["guia_motivo"];
                        $empenho->z01_cgccpf = $dadospessoa["z01_cgccpf"];
                        $empenho->e46_nome = $dadospessoa["z01_nome"];
                        $empenho->e70_vlrliq = $planilha2["guia_valor"];
                    }//IF DE DEZEMBRO
                    //if da 45
                }else{
                    $matriculax = $this->buscaMatricula($dadospessoa["z01_cgccpf"]);

                    if(!$matriculax){
                        //Dados inexistentes na planilha
                    }
                    
                    if(substr($matriculax, 0, 3) == "000"){
                        $matriculax = substr($matriculax, 3);
                    }elseif(substr($matriculax, 0, 2) == "00"){
                        $matriculax = substr($matriculax, 2);
                    }elseif(substr($matriculax, 0, 1) == "0"){                    
                        $matriculax = substr($matriculax, 1);
                    }
                
                    if(substr($this->competencia, 4, 2) == "02"){
                        $diax = "29";
                    }elseif(substr($this->competencia, 4, 2) == "01" ||substr($this->competencia, 4, 2) == "03" ||substr($this->competencia, 4, 2) == "05" ||substr($this->competencia, 4, 2) == "07"){
                        $diax = "31";
                    }else{
                        $diax = "30";
                    }
                    $mesx = substr($this->competencia, 0, 4) ."-". substr($this->competencia, 4, 2) . "-" . $diax;

                    
                    //$planilha = $this->buscaDadosPlanilha($matriculax, $mesx);
                    
                    


                    if($this->competencia == 202411){
                        $planilha = $this->buscaDadosPlanilhaNov($matriculax, $mesx);    
                        $empenho->e446_sequencial = $planilha["numero"].$planilha["ano"];
                        $empenho->e446_quantidade = $planilha["diarias"];
                        $empenho->e446_datainicio = $planilha["data"];
                        $empenho->e446_datafim = $planilha["dataretorno"];
                        $empenho->e446_estadodestino = $planilha["uf"];
                        $empenho->e446_destino = $planilha["destino"];
                        $empenho->e446_paisdestino = "Brasil";
                        $empenho->e446_motivo = $planilha["motivo"];
                        $empenho->z01_cgccpf = $dadospessoa["z01_cgccpf"];
                        $empenho->e46_nome = $dadospessoa["z01_nome"];
                        $empenho->e70_vlrliq = $dadosdiaria["e70_vlrliq"];
                        //$empenho->e69_anousu = $dadosdiaria["ano"];
                        $empenho->e69_anousu = $dadosdiaria["e69_anousu"];
                    }else if($this->competencia == 202412){
                        $planilha = $this->buscaDadosPlanilhaDez($matriculax, $mesx);
                        $empenho->e446_sequencial = $planilha["numero"].$planilha["ano"];
                        $empenho->e446_quantidade = $planilha["diarias"];
                        $empenho->e446_datainicio = $planilha["data"];
                        $empenho->e446_datafim = $planilha["dataretorno"];
                        $empenho->e446_estadodestino = $planilha["uf"];
                        $empenho->e446_destino = $planilha["destino"];
                        $empenho->e446_paisdestino = "Brasil";
                        $empenho->e446_motivo = $planilha["motivo"];
                        $empenho->z01_cgccpf = $dadospessoa["z01_cgccpf"];
                        $empenho->e46_nome = $dadospessoa["z01_nome"];
                        $empenho->e70_vlrliq = $dadosdiaria["e70_vlrliq"];
                        //$empenho->e69_anousu = $dadosdiaria["ano"];
                        $empenho->e69_anousu = $dadosdiaria["e69_anousu"];
                    }else{                        
                        //$planilha = $this->buscaDadosPlanilha2($matriculax, $mesx, $empenho->e70_valor, $empenho->e69_dtinclusao);
                        $planilha = $this->buscaDadosPlanilha2($matriculax, $mesx, $empenho->e70_valor);
                        
                        /*
                        //if(empty($planilha)){
                        if(empty($dadospessoa)){
                            var_dump($matriculax, $mesx, $empenho->e70_valor, $empenho->e69_dtinclusao);
                            var_dump($empenho->e50_codord);
                            $this->testa($empenho);
                            $this->testa($dadospessoa);
                            $this->testa($dadosdiaria);
                            die("Foi");
                            SELECT z01_nome, e49_codord, e49_numcgm, z01_cgccpf FROM pagordemconta INNER JOIN cgm ON e49_numcgm = z01_numcgm WHERE e49_codord = 549144;
                            //codord 549144
                        }
                        */
                        
                        $montadataano = substr($this->competencia, 0, 4);
                        $montadatames = substr($this->competencia, 4, 2);
                        $montadatadia = date("d");
                        $datavazia = $montadataano . "-" . $montadatames . "-" . $montadatadia;
                        
                        
                        
                        $empenho->e446_sequencial = $planilha["guia_numero"].$planilha["guia_ano"];
                        $empenho->e446_quantidade = 1; //$planilha["diarias"];
                        $empenho->e446_datainicio = (empty($planilha["guia_data"])) ? $datavazia : $planilha["guia_data"];
                        $empenho->e446_datafim = (empty($planilha["guia_dataretorno"])) ? $datavazia : $planilha["guia_dataretorno"];
                        $empenho->e446_estadodestino = (empty($planilha["guia_ufdestino"])) ? "RJ" : $planilha["guia_ufdestino"];
                        $empenho->e446_destino = (empty($planilha["guia_destino"])) ? "RIO DE JANEIRO" : $planilha["guia_destino"];
                        $empenho->e446_paisdestino = "Brasil";
                        $empenho->e446_motivo = (empty($planilha["guia_motivo"])) ? "A serviço da secretaria." : $planilha["guia_motivo"];
                        $empenho->z01_cgccpf = $dadospessoa["z01_cgccpf"];
                        $empenho->e46_nome = $dadospessoa["z01_nome"];
                        $empenho->e70_vlrliq = $dadosdiaria["e70_vlrliq"];
                        //$empenho->e69_anousu = $dadosdiaria["ano"];
                        $empenho->e69_anousu = $dadosdiaria["e69_anousu"];
                    }
                    
                    
                    
                    
                    

                    if($this->competencia == 2024099){
                        $planilha2 = $this->buscaOutrosDadosPlanilha($matriculax, $mesx);

                        $empenho->e446_sequencial = $planilha2["guia"];
                        $empenho->e446_quantidade = $planilha2["diarias"];
                        $empenho->e446_datainicio = $planilha2["data"];
                        $empenho->e446_datafim = $planilha2["dataretorno"];
                        $empenho->e446_estadodestino = $planilha2["uf"];
                        $empenho->e446_destino = $planilha2["destino"];
                        $empenho->e446_paisdestino = "Brasil";
                        $empenho->e446_motivo = $planilha2["motivo"];
                        $empenho->z01_cgccpf = $dadospessoa["z01_cgccpf"];
                        $empenho->e46_nome = $dadospessoa["z01_nome"];
                        $empenho->e70_vlrliq = $planilha2["valor"];
                    }//IF DE SETEMBRO
                    
                                            
                        
                        
                    
                 
                } //fim if/else 45
            }//fim velho
            
            if($velho){
                $vt = $this->validaTipo($empenho->e69_codnota);
            }else{
                $vt = $this->validaTipo($empenho->c66_codnota);
            }
            if(!$vt){continue;}


            if($velho){
                $codord = $empenho->e50_codord;
                $dadosempnota = $this->buscaDadosEmpNota($empenho->e69_codnota, $empenho->e60_numemp);
                
            }else{
                $codord = $this->buscaCodord2($empenho->c70_codlan);
                $dadosempnota = $this->buscaDadosEmpNota($empenho->c66_codnota, $empenho->e60_numemp);
                $dadosdiaria = $this->buscaDadosDiaria2($codord);
            }
            
            if(empty($dadosdiaria)){continue;}
            
            if($velho){
                
                $data = (object)[
                "Identificador" => $ix, //$empenho->e69_codnota,
                "CodigoUnidadeGestora" => $this->sCodigoTribunal,
                "NumeroEmpenho" => $empenho->e60_codemp,
                "AnoEmpenho" => $empenho->e60_anousu,
                "AnoLiquidacaoDeEmpenho" => $dadosempnota["e69_anousu"],
                "Competencia" => $this->competencia,
                "CPFServidor" => $empenho->z01_cgccpf,
                "NumeroDiaria" => $empenho->e446_sequencial,
                "NumeroLiquidacaoEmpenho" => $codord,
                "Nome" => $empenho->e46_nome,
                "Valor" => $dadosdiaria["e70_vlrliq"],
                "QuantidadeDiarias" => $empenho->e446_quantidade,
                "DataSaida" => $empenho->e446_datainicio,
                "DataRetorno" => $empenho->e446_datafim,
                "IndicadorDestino" => 1,
                "EstadoDestino" => $empenho->e446_estadodestino,
                "CidadeDestino" => Helper::convertAndLimit($empenho->e446_destino, 50),
                "PaisDestino" => Helper::convertAndLimit($empenho->e446_paisdestino, 50),
                "ObjetoDiaria" => substr(utf8_encode($empenho->e446_motivo), 0, 4000),
                "CodigoOrgao" => $empenho->o58_orgao,
                "CodigoUnidadeOrcamentaria" => $empenho->o58_unidade,
            ];

            }else{
                $data = (object)[
                "Identificador" => $ix, //$empenho->e69_codnota,
                "CodigoUnidadeGestora" => $this->sCodigoTribunal,
                "NumeroEmpenho" => $empenho->e60_codemp,
                "AnoEmpenho" => $empenho->e60_anousu,
                "AnoLiquidacaoDeEmpenho" => $dadosempnota["e69_anousu"],
                "Competencia" => $this->competencia,
                "CPFServidor" => $dadosdiaria["e46_cpf"],//$empenho->z01_cgccpf,
                "NumeroDiaria" => $dadosdiaria["e446_sequencial"],
                "NumeroLiquidacaoEmpenho" => $codord,
                "Nome" => Helper::convertAndLimit($dadosdiaria["e46_nome"], 255),
                "Valor" => $dadosdiaria["e70_vlrliq"],
                "QuantidadeDiarias" => $dadosdiaria["e446_quantidade"],
                "DataSaida" => $dadosdiaria["e446_datainicio"],
                "DataRetorno" => $dadosdiaria["e446_datafim"],
                "IndicadorDestino" => $dadosdiaria["e446_tipodiaria"] === 'internacional' ? 2 : 1,
                "EstadoDestino" => $this->arrumaUF(Helper::convertAndLimit($dadosdiaria["e446_estadodestino"], 50)),
                "CidadeDestino" => Helper::convertAndLimit($dadosdiaria["e446_destino"], 50),
                "PaisDestino" => Helper::convertAndLimit($dadosdiaria["e446_paisdestino"], 50),
                "ObjetoDiaria" => substr($dadosdiaria["e446_motivo"], 0, 4000),
                "CodigoOrgao" => $empenho->o58_orgao,
                "CodigoUnidadeOrcamentaria" => $empenho->o58_unidade,
            ];
            }            
            $ix++;
            $obj->LiquidacoesEmpenhoDiarias[] = (object)['LiquidacaoEmpenhoDiaria' => $data];

        }
        
        $this->aDados = $obj;
    }
}

<?php

namespace ECidade\Financeiro\Contabilidade\Exportacao\Sigfis\Arquivos\v2024;

use Illuminate\Database\Capsule\Manager as DB;
use stdClass;

class ArquivoRPInscricao extends ArquivoBase
{
    protected $sNomeArquivo = 'InscricaoRestosPagar';

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

        $sql = pg_query("SELECT empempenho.e60_numemp::integer as e60_numemp, e60_resumo, e60_destin, e60_codemp, e60_emiss, e60_numcgm, z01_nome, z01_cgccpf, z01_munic, e60_vlremp, e60_vlranu, e60_vlrliq, e63_codhist, e40_descr, e60_vlrpag, e60_anousu, e60_coddot, o58_coddot, o58_orgao, o40_orgao, o40_descr, o58_unidade, o41_descr, o15_codigo, o15_descr, fc_estruturaldotacao(e60_anousu,e60_coddot) as dl_estrutural, e60_codcom, pc50_descr, sum(c70_valor) as c70_valor, c70_data, c70_codlan, c53_tipo, c53_descr, (select o56_elemento||'-'||o56_descr from orcelemento where o56_codele = c67_codele and o56_anousu = c70_anousu) as desdobramento, c72_complem, z01_incest, z01_cgccpf, c66_codnota, e91_numemp from empempenho inner join conlancamemp on c75_numemp = empempenho.e60_numemp inner join conlancam on c70_codlan = c75_codlan left join conlancamnota on c66_codlan = c70_codlan inner join conlancamdoc on c71_codlan = c70_codlan inner join conhistdoc on c53_coddoc = c71_coddoc inner join cgm on cgm.z01_numcgm = empempenho.e60_numcgm inner join db_config on db_config.codigo = empempenho.e60_instit inner join orcdotacao on orcdotacao.o58_anousu = empempenho.e60_anousu and orcdotacao.o58_coddot = empempenho.e60_coddot and orcdotacao.o58_instit = empempenho.e60_instit inner join emptipo on emptipo.e41_codtipo = empempenho.e60_codtipo inner join db_config as a on a.codigo = orcdotacao.o58_instit INNER JOIN origemcomplementorecurso ON origemcomplementorecurso.o206_numero = empempenho.e60_numemp AND origemcomplementorecurso.o206_origem = 1 INNER JOIN orctiporec ON orctiporec.o15_codigo = origemcomplementorecurso.o206_recurso inner join orcfuncao on orcfuncao.o52_funcao = orcdotacao.o58_funcao inner join orcsubfuncao on orcsubfuncao.o53_subfuncao = orcdotacao.o58_subfuncao inner join orcprograma on orcprograma.o54_anousu = orcdotacao.o58_anousu and orcprograma.o54_programa = orcdotacao.o58_programa inner join orcelemento on orcelemento.o56_codele = orcdotacao.o58_codele and orcdotacao.o58_anousu = orcelemento.o56_anousu inner join orcprojativ on orcprojativ.o55_anousu = orcdotacao.o58_anousu and orcprojativ.o55_projativ = orcdotacao.o58_projativ inner join orcorgao on orcorgao.o40_anousu = orcdotacao.o58_anousu and orcorgao.o40_orgao = orcdotacao.o58_orgao inner join orcunidade on orcunidade.o41_anousu = orcdotacao.o58_anousu and orcunidade.o41_orgao = orcdotacao.o58_orgao and orcunidade.o41_unidade = orcdotacao.o58_unidade left join empemphist on empemphist.e63_numemp = empempenho.e60_numemp left join emphist on emphist.e40_codhist = empemphist.e63_codhist inner join pctipocompra on pctipocompra.pc50_codcom = empempenho.e60_codcom left join empresto on e60_numemp = e91_numemp and e60_anousu = e91_anousu left join conlancamcompl on c72_codlan = c70_codlan inner join conlancamele on c67_codlan = c70_codlan where c53_coddoc in (2009, 2005) and e60_anousu < {$ano} and c70_data between '{$di}' and '{$df}' and 1=1 and e60_instit in ({$inst}) group by e60_numemp, e60_resumo, e60_destin, e60_codemp, e60_emiss, e60_numcgm, z01_nome, z01_cgccpf, z01_munic, e60_vlremp, e60_vlranu, e60_vlrliq, e63_codhist, e40_descr, e60_vlrpag, e60_anousu, e60_coddot, o58_coddot, o58_orgao, o40_orgao, o40_descr, o58_unidade, o41_descr, o15_codigo, o15_descr, e60_codcom, pc50_descr, c70_data, c70_codlan, c53_tipo, c53_descr, desdobramento, c72_complem, z01_incest, z01_cgccpf, c66_codnota, e91_numemp order by e60_numemp, c70_codlan");
        
        /*
        $sql = pg_query("SELECT empempenho.e60_numemp::integer as e60_numemp, e60_resumo, e60_destin, e60_codemp, e60_emiss, e60_numcgm, z01_nome, z01_cgccpf, z01_munic, e60_vlremp, e60_vlranu, e60_vlrliq, e63_codhist, e40_descr, e60_vlrpag, e60_anousu, e60_coddot, o58_coddot, o58_orgao, o40_orgao, o40_descr, o58_unidade, o41_descr, o15_codigo, o15_descr, fc_estruturaldotacao(e60_anousu,e60_coddot) as dl_estrutural, e60_codcom, pc50_descr, sum(c70_valor) as c70_valor, c70_data, c70_codlan, c53_tipo, c53_descr, (select o56_elemento||'-'||o56_descr from orcelemento where o56_codele = c67_codele and o56_anousu = c70_anousu) as desdobramento, c72_complem, z01_incest, z01_cgccpf, c66_codnota, e91_numemp from empempenho inner join conlancamemp on c75_numemp = empempenho.e60_numemp inner join conlancam on c70_codlan = c75_codlan left join conlancamnota on c66_codlan = c70_codlan inner join conlancamdoc on c71_codlan = c70_codlan inner join conhistdoc on c53_coddoc = c71_coddoc inner join cgm on cgm.z01_numcgm = empempenho.e60_numcgm inner join db_config on db_config.codigo = empempenho.e60_instit inner join orcdotacao on orcdotacao.o58_anousu = empempenho.e60_anousu and orcdotacao.o58_coddot = empempenho.e60_coddot and orcdotacao.o58_instit = empempenho.e60_instit inner join emptipo on emptipo.e41_codtipo = empempenho.e60_codtipo inner join db_config as a on a.codigo = orcdotacao.o58_instit INNER JOIN origemcomplementorecurso ON origemcomplementorecurso.o206_numero = empempenho.e60_numemp AND origemcomplementorecurso.o206_origem = 1 INNER JOIN orctiporec ON orctiporec.o15_codigo = origemcomplementorecurso.o206_recurso inner join orcfuncao on orcfuncao.o52_funcao = orcdotacao.o58_funcao inner join orcsubfuncao on orcsubfuncao.o53_subfuncao = orcdotacao.o58_subfuncao inner join orcprograma on orcprograma.o54_anousu = orcdotacao.o58_anousu and orcprograma.o54_programa = orcdotacao.o58_programa inner join orcelemento on orcelemento.o56_codele = orcdotacao.o58_codele and orcdotacao.o58_anousu = orcelemento.o56_anousu inner join orcprojativ on orcprojativ.o55_anousu = orcdotacao.o58_anousu and orcprojativ.o55_projativ = orcdotacao.o58_projativ inner join orcorgao on orcorgao.o40_anousu = orcdotacao.o58_anousu and orcorgao.o40_orgao = orcdotacao.o58_orgao inner join orcunidade on orcunidade.o41_anousu = orcdotacao.o58_anousu and orcunidade.o41_orgao = orcdotacao.o58_orgao and orcunidade.o41_unidade = orcdotacao.o58_unidade left join empemphist on empemphist.e63_numemp = empempenho.e60_numemp left join emphist on emphist.e40_codhist = empemphist.e63_codhist inner join pctipocompra on pctipocompra.pc50_codcom = empempenho.e60_codcom left join empresto on e60_numemp = e91_numemp and e60_anousu = e91_anousu left join conlancamcompl on c72_codlan = c70_codlan inner join conlancamele on c67_codlan = c70_codlan where e60_numemp not in (select e91_numemp from empresto where e91_anousu <= {$ano}) and c53_coddoc in (2009, 2005) and c70_data between '{$di}' and '{$df}' and 1=1 and e60_instit in ({$inst}) group by e60_numemp, e60_resumo, e60_destin, e60_codemp, e60_emiss, e60_numcgm, z01_nome, z01_cgccpf, z01_munic, e60_vlremp, e60_vlranu, e60_vlrliq, e63_codhist, e40_descr, e60_vlrpag, e60_anousu, e60_coddot, o58_coddot, o58_orgao, o40_orgao, o40_descr, o58_unidade, o41_descr, o15_codigo, o15_descr, e60_codcom, pc50_descr, c70_data, c70_codlan, c53_tipo, c53_descr, desdobramento, c72_complem, z01_incest, z01_cgccpf, c66_codnota, e91_numemp order by e60_numemp, c70_codlan");
        */
        $resultado = array();

        while ($linha = pg_fetch_object($sql)) {
            $resultado[] = $linha;
        }
        
        return $resultado;
    }

    public function voltaRP(){    
        
        $inst = $this->instit;
        $ano = $this->iAnoUsu;
        $dtini = $ano."-01-01";
        $dtfin = $ano."-12-31";
        
        $sql = pg_query("SELECT e60_numemp, e60_codemp, e60_anousu, e91_numemp, o15_codigo, o15_loaespecificacao, e91_vlremp, e91_vlranu, e91_vlrliq, e91_vlrpag, e91_recurso, gestao, e91_anousu, o15_descr, o15_recurso, vlranu, vlrliq, vlrpag, vlrpagnproc, e91_codtipo, e90_descr, z01_numcgm, z01_nome, z01_cgccpf, e60_numemp, e60_codemp, e60_emiss, e60_anousu, o58_orgao, o58_unidade, o58_codigo, o58_funcao, o58_subfuncao, o56_elemento, o58_programa, o58_projativ, o52_descr, o54_descr, o55_descr, o56_descr, o40_descr, o41_descr, o53_descr, vlranuliq, vlranuliqnaoproc, c70_anousu, e64_codele, db21_tipoinstit, e60_instit, nomeinst from ( select e91_numemp, o15_codigo, o15_loaespecificacao, e91_anousu, e91_codtipo, e90_descr, o15_descr, o15_recurso, e91_recurso, fonterecurso.gestao, c70_anousu, o15_complemento, coalesce(e91_vlremp,0) as e91_vlremp, coalesce(e91_vlranu,0) as e91_vlranu, coalesce(e91_vlrliq,0) as e91_vlrliq, coalesce(e91_vlrpag,0) as e91_vlrpag, coalesce(vlranu,0) as vlranu, coalesce(vlranuliq,0) as vlranuliq, coalesce(vlranuliqnaoproc,0) as vlranuliqnaoproc, coalesce(vlrliq,0) as vlrliq, coalesce(vlrpag,0) as vlrpag, coalesce(vlrpagnproc,0) as vlrpagnproc from empresto inner join emprestotipo on e91_codtipo = e90_codigo inner join orctiporec on e91_recurso = o15_codigo inner join fonterecurso on fonterecurso.orctiporec_id = orctiporec.o15_codigo and fonterecurso.exercicio = {$ano} left outer join ( select c75_numemp, c70_anousu, sum( round( case when c53_tipo = 11 then c70_valor else 0 end,2) ) as vlranu, sum( round(case when c71_coddoc = 31 then c70_valor else 0 end,2) ) as vlranuliq, sum( round(case when c71_coddoc = 32 then c70_valor else 0 end,2) ) as vlranuliqnaoproc, sum( round(case when c53_tipo = 20 then c70_valor else ( case when c53_tipo = 21 then c70_valor*-1 else 0 end) end,2) ) as vlrliq, sum( round(case when c71_coddoc in (35, 6008) then c70_valor else ( case when c71_coddoc in (36, 6009) then c70_valor*-1 else 0 end) end,2) ) as vlrpag, sum( round( case when c71_coddoc in (37, 6010) then c70_valor else ( case when c71_coddoc in (38, 6011) then c70_valor*-1 else 0 end) end ,2) ) as vlrpagnproc from conlancamemp inner join conlancamdoc on c71_codlan = c75_codlan inner join conhistdoc on c53_coddoc = c71_coddoc inner join conlancam on c70_codlan = c75_codlan inner join empempenho on e60_numemp = c75_numemp where e60_anousu < {$ano} and c75_data between '{$dtini}' and '{$dtfin}' and e60_instit in ({$inst}) and c70_anousu = {$ano} group by c75_numemp,c70_anousu ) as x on x.c75_numemp = e91_numemp where e91_anousu = {$ano} ) as x inner join empempenho on e60_numemp = e91_numemp and e60_instit in ({$inst}) inner join db_config on db_config.codigo = empempenho.e60_instit inner join db_tipoinstit on db_tipoinstit.db21_codtipo = db_config.db21_tipoinstit inner join empelemento on e64_numemp = e60_numemp inner join cgm on z01_numcgm = e60_numcgm inner join orcdotacao on o58_coddot = e60_coddot and o58_anousu = e60_anousu and o58_instit = e60_instit inner join orcorgao on o40_orgao = o58_orgao and o40_anousu = o58_anousu inner join orcunidade on o41_anousu = o58_anousu and o41_orgao = o58_orgao and o41_unidade = o58_unidade inner join orcfuncao on o52_funcao = orcdotacao.o58_funcao inner join orcsubfuncao on o53_subfuncao = orcdotacao.o58_subfuncao inner join orcprograma on o54_programa = o58_programa and o54_anousu = orcdotacao.o58_anousu inner join orcprojativ on o55_projativ = o58_projativ and o55_anousu = orcdotacao.o58_anousu inner join orcelemento on e64_codele = o56_codele and o58_anousu = o56_anousu left join origemcomplementorecurso on o15_codigo = o206_recurso and o206_numero = e60_numemp and o206_origem = 10 where 1 = 1 and 1=1 order by o58_orgao,e60_anousu,e60_codemp::integer");

        $resultado = array();

        while ($linha = pg_fetch_object($sql)) {
            $resultado[] = $linha;
        }        
        return $resultado;
    }

    public function gerarDados()
    {
        $poste = $_POST["json"];
        $expl1ode = explode(":", $poste);
        $expl2ode = explode("\\\"", $expl1ode[2]);
        $arquivosdo = $expl2ode[1];
        if($arquivosdo == "0"){
            $this->competencia = $this->iAnoUsu . "00"; //"202400";
        }
        
        
        
        $anousuario = db_getsession("DB_anousu") - 1;
        //$anousuario = $anousuario."-12-31";
        $anousuario = $this->anousu."-12-31";
        
        

        $campos = [
            'e60_numemp',
            'e60_anousu',
            'e91_anousu',
            'e60_codemp',
            'o58_orgao',
            'o58_unidade',

            'e91_vlremp', 'e91_vlranu', 'e91_vlrliq',
            'e91_vlrliq', 'e91_vlrpag'
        ];

        if($arquivosdo == "13"){
            $empenhos = $this->voltaRP();
            $this->competencia = $this->iAnoUsu . "13";
            $anousuario = "2024-12-31";            
            
            /*
            //$this->competencia = "202413";
            $empenhos = DB::table('empresto')
            ->select($campos)
            ->join('empenho.empempenho', 'e60_numemp', 'e91_numemp')
            ->join('orcamento.orcdotacao', function ($join) {
                $join->on('e60_anousu', 'o58_anousu')
                    ->on('e60_coddot', 'o58_coddot');
            })
            //->where('e60_anousu', $this->iAnoUsu)
            ->where('e60_anousu', 2024)
            ->where('e60_instit', $this->instit)
            ->get();
            */
            
            //$sql_with_bindings = str_replace_array('?', $empenhosx->getBindings(), $empenhosx->toSql());
            //$sql_with_bindings = str_replace("\"", "", $sql_with_bindings);
            //var_dump($sql_with_bindings); die("confere");

        }else{
            /*
            $empenhosx = DB::table('empresto')
            ->select($campos)
            ->join('empenho.empempenho', 'e60_numemp', 'e91_numemp')
            ->join('orcamento.orcdotacao', function ($join) {
                $join->on('e60_anousu', 'o58_anousu')
                    ->on('e60_coddot', 'o58_coddot');
            })
            ->where('e91_anousu', $this->iAnoUsu)
            ->where('e60_instit', $this->instit)
            ->get();
            */
            //$sql_with_bindings = str_replace_array('?', $empenhos->getBindings(), $empenhos->toSql());
            //$sql_with_bindings = str_replace("\"", "", $sql_with_bindings);
            //var_dump($sql_with_bindings); die("confere");    
            $empenhos = $this->voltaLiquidacaoes();
        }

        //$empenhos = $this->voltaLiquidacaoes();
        

        if(count($empenhos) == 0){
            throw new \Exception(sprintf(
                'Não foi encontrado nenhum registro para o arquivo da Remessa de %s',
                'Restos a Pagar - Inscrição'
            ));
        }

        $obj = new stdClass();
        $obj->InscricoesRestosPagar = [];



        foreach ($empenhos as $empenho) {


            if($arquivosdo == 13){
                
                
                $e91_vlremp = $empenho->e91_vlremp;
                $e91_vlranu = $empenho->e91_vlranu;
                $vlranu = $empenho->vlranu;
                $vlrliq = $empenho->vlrliq;
                $e91_vlrliq = $empenho->e91_vlrliq;
                $vlranuliq = $empenho->vlranuliq;
                $e91_vlrpag = $empenho->e91_vlrpag;
                $vlrpag = $empenho->vlrpag;
                $vlrpagnproc = $empenho->vlrpagnproc;


                
                $aliquidargeral = $e91_vlremp - (($e91_vlranu + $vlranu) + ($vlrliq + $e91_vlrliq - $vlranuliq));
                $liquidado_anterior = ($e91_vlremp - $e91_vlranu - $e91_vlrliq) + ($e91_vlrliq - $e91_vlrpag);
                $apagargeral = ($liquidado_anterior - $vlranu - $vlrpag - $vlrpagnproc);

                $liquidados = ($apagargeral - $aliquidargeral);
            
                $valorRPNP = round($aliquidargeral, 2);
                $vaorRPP = round($liquidados, 2);
                
                $data  = (object) [
                    'Identificador' => $empenho->e60_numemp,
                    'CodigoUnidadeGestora' => $this->sCodigoTribunal,
                    'CodigoOrgao' => $empenho->o58_orgao,
                    'CodigoUnidadeOrcamentaria' => $empenho->o58_unidade,
                    'Exercicio' => $this->iAnoUsu,
                    'NumeroEmpenho' => $empenho->e60_codemp,
                    'AnoEmpenho' => $empenho->e60_anousu,
                    'DataInscricao' => $anousuario,
                    'Competencia' => $this->competencia,
                    'ValorRestosPagarProcessado' => $vaorRPP,
                    'ValorRestosPagarNaoProcessado' => $valorRPNP,
                    'Justificativa' => '',
                ];

                $obj->InscricoesRestosPagar[] = (object)[
                    'InscricaoRestosPagar' => $data
                ];
                
            }else{
                //$valorRPNP = round($empenho->e91_vlremp - $empenho->e91_vlranu - $empenho->e91_vlrliq, 2);
                //$vaorRPP = round($empenho->e91_vlrliq - $empenho->e91_vlrpag, 2);
                $valorRPNP = round($empenho->e60_vlremp - $empenho->e60_vlranu - $empenho->e60_vlrliq, 2);
                $vaorRPP = round($empenho->e60_vlrliq - $empenho->e60_vlrpag, 2);

                $data  = (object) [
                    'Identificador' => $empenho->e60_numemp,
                    'CodigoUnidadeGestora' => $this->sCodigoTribunal,
                    'CodigoOrgao' => $empenho->o58_orgao,
                    'CodigoUnidadeOrcamentaria' => $empenho->o58_unidade,
                    'Exercicio' => $this->iAnoUsu,
                    'NumeroEmpenho' => $empenho->e60_codemp,
                    'AnoEmpenho' => $empenho->e60_anousu,
                    'DataInscricao' => $anousuario,//(date("Y")-1) ."-12-31", //"{$empenho->e91_anousu}-12-31",
                    'Competencia' => $this->competencia,
                    'ValorRestosPagarProcessado' => $vaorRPP,
                    'ValorRestosPagarNaoProcessado' => $valorRPNP,
                    'Justificativa' => '',
                ];

                $obj->InscricoesRestosPagar[] = (object)[
                    'InscricaoRestosPagar' => $data
                ];
            }
        }

        $this->aDados =  $obj;
    }
}

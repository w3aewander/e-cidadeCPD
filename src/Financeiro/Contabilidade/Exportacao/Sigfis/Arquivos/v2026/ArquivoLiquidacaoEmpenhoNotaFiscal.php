<?php

namespace ECidade\Financeiro\Contabilidade\Exportacao\Sigfis\Arquivos\v2024;

use ECidade\RecursosHumanos\ESocial\Configuracao\JSON;
use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Database\Query\JoinClause;

class ArquivoLiquidacaoEmpenhoNotaFiscal extends ArquivoBase
{
    protected $sNomeArquivo = 'LiquidacaoEmpenhoNotaFiscal';

    public function testa($var){
        echo "<pre>";
        print_r($var);
        echo "</pre>";
    }

    public function validaTipo($codnota){
        //$sql = pg_query("SELECT * FROM sigfistipodocliquidacao INNER JOIN empnotasigfistipodocliquidacao ON e178_sigfistipodocliquidacao = e177_sequencial WHERE e178_empnota = {$codnota}");
        $sql = pg_query("SELECT e177_codigo, e177_descr, e178_sigfistipodocliquidacao FROM sigfistipodocliquidacao INNER JOIN empnotasigfistipodocliquidacao ON e178_sigfistipodocliquidacao = e177_sequencial WHERE e178_empnota = {$codnota}");
        $resultado = pg_fetch_all($sql);

        if($resultado[0]["e178_sigfistipodocliquidacao"] == 1){
            return true;
        }
        return false;
        //return $resultado;
    }

    public function buscaValor($seqempenho, $seqnota){
        $sql = pg_query("SELECT e70_vlrliq, e70_valor from empnota inner join empnotaele on e70_codnota = e69_codnota left join pagordemnota on e70_codnota = e71_codnota and e71_anulado is false left join pagordemele on e71_codord = e53_codord where e69_numemp = {$seqempenho} AND e69_codnota = {$seqnota} order by e69_dtnota");
        $resultado = pg_fetch_all($sql);
        return $resultado[0]["e70_valor"];
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

    public function voltaValor($seqempenho, $codnota){
        $sql = pg_query("SELECT e70_vlrliq, e70_valor from empnota inner join empnotaele on e70_codnota = e69_codnota left join pagordemnota on e70_codnota = e71_codnota and e71_anulado is false left join pagordemele on e71_codord = e53_codord where e69_numemp = {$seqempenho} AND e69_codnota = {$codnota} order by e69_dtnota");
        $resultado = pg_fetch_all($sql);

        if($resultado[0]["e70_vlrliq"] == 0){
            return $resultado[0]["e70_valor"];
        }
        return $resultado[0]["e70_vlrliq"];

    }

    public function gerarDados()
    {
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
            'e69_outrosdados',
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
            ->join('empnotasigfistipodocliquidacao', 'e178_empnota', 'e69_codnota')
            ->join('orcdotacao', function (JoinClause $join) {
                $join->on('o58_anousu', 'e60_anousu')
                    ->on('o58_coddot', 'e60_coddot');
            })
            ->where('e60_anousu', $this->iAnoUsu)
            ->where('e178_sigfistipodocliquidacao', 1)
            ->where('e60_instit', $this->instit)
            ->whereBetween('e69_dtinclusao', [$this->dtDataInicial, $this->dtDataFinal])
            ->where('e60_vlrliq', '!=', 0)
            //->orWhereIn('e69_codnota', [455307, 454192, 454504, 454099, 454577, 453259, 454357, 452881, 454477, 451214, 454484, 454605, 454420, 452720, 453162, 454258, 454585, 454603, 454600, 454601, 454989, 454968])
            ->orderBy('e60_numemp')
            ->get();

            $empenhos = $this->voltaLiquidacaoes();

        if(count($empenhos) == 0){
            throw new \Exception('Não foi encontrado nenhum empenho para o arquivo da Remessa.');
        }
        
        //$cnotas = [455307, 454192, 454504, 454099, 454577, 453259, 454357, 452881, 454477, 451214, 454484, 454605, 454420, 452720, 453162, 454258, 454585, 454603, 454600, 454601, 454989, 454968];
        $obj = new \stdClass();
        $obj->LiquidacoesEmpenhoNotasFiscais = [];
        //$this->testa($empenhos); die("Confere");
        foreach ($empenhos as $empenho){
            $vt = $this->validaTipo($empenho->c66_codnota);
            if(!$vt){continue;}

            //if($empenho->e60_codemp != 821){continue;}

            $codord = $this->buscaCodord2($empenho->c70_codlan);
            $dadosordem = $this->buscaDadosOrdem($codord);
            $dadosempnota = $this->buscaDadosEmpNota($empenho->c66_codnota, $empenho->e60_numemp);
            //$dadostipo = $this->retornaTipo($empenho->c66_codnota);
            $novovalor = $this->voltaValor($empenho->e60_numemp, $empenho->c66_codnota);


            //$novovalor = $this->buscaValor($empenho->e60_numemp, $empenho->e69_codnota);
            //echo "<pre>"; print_r($empenho); echo "</pre>";
            $atestadores = DB::table('empnotaatestador')
                ->select(['z01_numcgm', 'z01_nome', 'z01_cgccpf'])
                ->join('cgm', 'z01_numcgm', 'e169_numcgm')
                ->where('e169_empnota', $empenho->e69_codnota)
                ->limit(1)
                ->get()
                ->map(function ($atestador) {
                    return (object)[
                        'Identificador' => $atestador->z01_numcgm,
                        'NomeAtestador' => utf8_encode($atestador->z01_nome),
                        //'Nome' => $atestador->z01_nome,
                        'CPF' => $atestador->z01_cgccpf,
                    ];
                })->toArray();

            $dadosNota = \JSON::create()->parse($empenho->e69_outrosdados);            
            //ALTERA A DATA E INSERE EMPENHOS DE OUTROS MESES
            /*if(in_array($empenho->e69_codnota, $cnotas)){
                $alteradata = explode("-", $empenho->e69_dtnota);
                $alteradata = $alteradata[0]."-09-".$alteradata[2];
                $empenho->e69_dtnota = $alteradata;

                $alteradata2 = explode("-", $empenho->e69_dtrecebe);
                $alteradata2 = $alteradata2[0]."-09-".$alteradata2[2];
                $empenho->e69_dtrecebe = $alteradata2;
            }*/

            if($novovalor == 0){continue;}
            
            $data = (object)[
                'Identificador' => $dadosempnota["e69_codnota"],
                'CodigoUnidadeGestora' => $this->sCodigoTribunal,
                'NumeroEmpenho' => $empenho->e60_codemp,
                'AnoEmpenho' => $empenho->e60_anousu,
                'Competencia' => $this->competencia,
                'NumeroLiquidacaoEmpenho' => $codord,
                'AnoLiquidacaoEmpenho' => $dadosempnota["e69_anousu"],
                'CodigoOrgao' => $empenho->o58_orgao,
                'CodigoUnidadeOrcamentaria' => $empenho->o58_unidade,
                //'DANFE' => (empty($dadosNota->danfe->numero)) ? "false" : "true",                
                'DANFE' => (empty($dadosNota->danfe->numero)) ? "false" : "false",
                'ChaveDANFE' => $dadosNota->danfe->chave,
                'NumeroNF' => $dadosempnota["e69_numero"],
                'Serie' => $dadosempnota["e69_serienota"],
                'Data' => $dadosempnota["e69_dtnota"],
                'DataAtestacao' => $dadosempnota["e69_dtrecebe"],
                'Valor' => $novovalor,//$empenho->e60_vlrliq,
                //'Atestadores' => (object)['Atestador' => $atestadores],
                'Atestadores' => ($atestadores) ? (object)['Atestador' => $atestadores] : NULL
            ];

            $obj->LiquidacoesEmpenhoNotasFiscais[] = (object)['LiquidacaoEmpenhoNotaFiscal' => $data];
        }
        
        $this->aDados = $obj;
    }
}

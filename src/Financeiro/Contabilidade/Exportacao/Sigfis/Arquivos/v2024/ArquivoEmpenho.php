<?php

namespace ECidade\Financeiro\Contabilidade\Exportacao\Sigfis\Arquivos\v2024;

use BusinessException;
use ECidade\Financeiro\Contabilidade\Exportacao\Sigfis\Common\Helper;
use Illuminate\Database\Capsule\Manager as DB;
use stdClass;

//use ECidade\Financeiro\Orcamento\Recurso\Origem;

class ArquivoEmpenho extends ArquivoBase
{
    protected $sNomeArquivo  = 'Empenho';

    public function buscaCamposAuxiliares($empenho){        
        $sql2 = pg_query("SELECT * FROM empenhoauxsigfis WHERE seqempenho = {$empenho}");
        $resultado = pg_fetch_all($sql2);
        $aux = $resultado[0];

        if(!$aux){
            $sql1 = pg_query("SELECT e61_autori FROM empempaut WHERE e61_numemp = {$empenho}");
            $autorizacao = pg_fetch_all($sql1);
            $autorizacao = $autorizacao[0]["e61_autori"];

            $sql = pg_query("SELECT * FROM empenhoauxsigfis WHERE noautorizacaoempenho = {$autorizacao}");
            $resultado = pg_fetch_all($sql);
            return $resultado[0];    
        }else{
            return $aux;
        }
    }

    public function codeleIdentificador($seqempenho){
        $sql = pg_query("SELECT e64_codele from empelemento where e64_numemp = {$seqempenho}");
        $resultado = pg_fetch_all($sql);
        return $resultado[0]["e64_codele"];
    }

    public function buscaRecursoAusente($seqempenho){
        $sql = pg_query("SELECT o206_recurso FROM origemcomplementorecurso WHERE o206_numero = {$seqempenho}");
        $resultado = pg_fetch_all($sql);
        return $resultado[0]["o206_recurso"];
    }

    public function gerarDados()
    {

        $fonteselemento = array(
            319008 => 339008,
            319009 => 339008,
            339018 => 339018,
            339040 => 339040,
            339014 => 339014,
            339030 => 339030,
            339036 => 339036,
            449052 => 449052,
            339039 => 339039,
            469071 => 469071,
            469073 => 469073,
            329021 => 329021,
            339047 => 339047,
            339096 => 339096,
            339092 => 339092,
            449040 => 449040,
            339045 => 339045,
            337270 => 337239
        );

        $fontessubelemento = array(            
            31900805 => 33900805,
            31900901 => 33900856,
            33901899 => 33901801,
            33904022 => 33904014,
            33901414 => 33901401,
            33903006 => 33903007,
            33903696 => 33903699,
            44905212 => 44905208,
            44905206 => 44905208,
            33903981 => 33903999,            
            33909218 => 33901801,
            //33909299 => 33901801,
            33903937 => 33903936,
            33901499 => 33901401,
            33904713 => 33904705,
            46907301 => 46907399,
            46907101 => 46907101,
            32902104 => 32902101,
            32902106 => 32902101,
            46907106 => 46907101,
            46907104 => 46907101,
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
            33909299 => 33909240,
            33903003 => 33903001,
            44904005 => 44904099,
            33904501 => 33904599,
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
            33903938 => 33903936
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
    6219 => 1621
    );

        $empenhos = $this->getEmpenhos();

        if ($empenhos->isEmpty()) {
            throw new BusinessException('Não há empenhos para a competência informada.');
        }

        $RemessaEmpenho = new stdClass();
        $RemessaEmpenho->Empenhos = [];
        $xi = 1;
        foreach ($empenhos as $item) {
            //if($item->e60_numemp != 967899){continue;}    
            /*if($item->e60_numemp == 967899){
                echo "<pre>";
                print_r($item);
                echo "</pre>";
                die("COnfere");
            }*/
            /*if($item->e60_codemp == 343){
                //$origemRecurso = Origem::getEmpenho($item->e60_numemp, $item->e60_anousu);
                //var_dump($origemRecurso);
                //echo "<pre>";
                //print_r($item);
                //echo "</pre>";
                //die("Parou a bodega");
            }*/
            /*if($item->e60_codemp == 343){
                echo "<pre>";
                print_r($item);
                echo "</pre>";
            }*/
            //die("Maoe");
            $dadosextras = $this->buscaCamposAuxiliares($item->e60_numemp);
            //echo "<pre>";
            //print_r($dadosextras);
            //echo "<pre>";
            //die("Teste");
            $item->natureza = substr($item->c60_estrut,1, 8);

            if(empty($item->fonte)){
                $item->fonte = $this->buscaRecursoAusente($item->e60_numemp);
            }
            
            
            // validacoes
            if (empty($item->fonte)) {                
                $this->addLog("[Empenho {$item->e60_numemp}]: Recurso sem vinculo com Fonte Recurso padrão STN");
                continue;
            }

            if (empty($item->natureza)) {                
                $this->addLog("[Empenho {$item->e60_numemp}]: ND sem vinculo com Plano de Contas Governo");
                continue;
            }

            $Empenho = new stdClass();
            $Empenho->Identificador = $item->e60_numemp;
            $Empenho->CodigoUnidadeGestora = $this->sCodigoTribunal;
            $Empenho->CpfOrdenador = $this->unidadeGestora->ordenadorDespesa->z01_cgccpf;
            $Empenho->Competencia = $this->competencia;
            $Empenho->NumeroEmpenho = $item->e60_codemp;
            $Empenho->AnoEmpenho = $item->e60_anousu;
            $Empenho->Data = $item->e60_emiss;
            $Empenho->Tipo = $item->e60_codtipo;
            $Empenho->CodigoFuncao = $item->o52_siconfi;
            $Empenho->CodigoSubFuncao = $item->o53_siconfi;

            if(strlen($item->o55_projativ) == 3 && substr($item->o55_projativ, 0, 1) != 0){
                $codacao = $item->o55_projativ;
            }elseif(strlen($item->o55_projativ) == 4){
                $codacao = $item->o55_projativ;
            }else{
                $codacao = "0".$item->o55_projativ;
            }
            //$Empenho->CodigoAcao = (strlen($item->o55_projativ) == 3) ? "0".$item->o55_projativ : $item->o55_projativ;
            $Empenho->CodigoAcao = $codacao;
            $Empenho->TipoAcao = $item->o55_tipo;
            $Empenho->CodigoPrograma = $item->o54_programa;
            $Empenho->NaturezaDespesa = substr($item->natureza, 0, 6);
            if($this->instit == 50){
                $Empenho->FonteRecurso = $item->fonte;
                if($fontes50[$item->fonte]){
                    $Empenho->FonteRecurso = $fontes50[$item->fonte];
                }
            }else{
                $Empenho->FonteRecurso = $item->fonte;
            }
            //$Empenho->FonteRecurso = $item->fonte;
            $Empenho->ValorEmpenho = $item->e60_vlremp;

            
                $texto = str_replace('&#13;', ' ', $item->e60_resumo);
                $texto = str_replace(["\r", "\n"], ' ', $texto);
                $texto = trim($texto);


            $historico = $item->e40_descr . " - " . $texto;
            $historico = substr($historico, 0, 255);
            $historico = utf8_encode($historico);


            $Empenho->Historico = !empty($historico) ? $historico : 'PADRÃO';

            $Empenho->NomeCredor = Helper::convertAndLimit($item->z01_nome, 255);
            $Empenho->CnpjCpfNifCredor = $item->z01_cgccpf;
            $Empenho->TipoPessoa = ($item->tipo_pessoa == 14) ? 1 : 2;
            $Empenho->CodigoOrgao = $item->o58_orgao;
            $Empenho->CodigoUnidadeOrcamentaria = $item->o58_unidade;

            // CodigoAcompanhamentoExecucaoOrcamentaria
            $Empenho->CodigoAcompanhamentoExecucaoOrcamentaria = $item->o15_complemento;
            if ($item->o15_complemento == 0 || empty($item->o15_complemento)) {
                $Empenho->CodigoAcompanhamentoExecucaoOrcamentaria = '0000';
            }

            // CodigoJustificativaAusenciaAtoJuridico
            $Empenho->CodigoJustificativaAusenciaAtoJuridico = $dadosextras["jaaj"]; //$item->ausenciaatojuridico;
            if (empty($Empenho->CodigoJustificativaAusenciaAtoJuridico)) {
                $Empenho->CodigoJustificativaAusenciaAtoJuridico = null;
            }

            // CodigoJustificativaAusenciaInstrumentoPrevio
            $Empenho->CodigoJustificativaAusenciaInstrumentoPrevio = $dadosextras["jaip"];//$item->ausenciainstrumentoprevio;
            if (empty($Empenho->CodigoJustificativaAusenciaAtoJuridico)) {
                $Empenho->CodigoJustificativaAusenciaInstrumentoPrevio = null;
            }

            $Empenho->SubElementos = [];
            $EmpenhoSubElemento = new stdClass();
            $EmpenhoSubElemento->Identificador = $this->codeleIdentificador($item->e60_numemp); //$xi;//$item->planodespesa;
            $EmpenhoSubElemento->Valor = $item->e60_vlremp;
            $EmpenhoSubElemento->NaturezaDespesa = $item->natureza;

            if($fonteselemento[$Empenho->NaturezaDespesa]){
                $Empenho->NaturezaDespesa = $fonteselemento[$Empenho->NaturezaDespesa];
            }
            //var_dump($EmpenhoSubElemento->NaturezaDespesa); die("Confere");
            if($fontessubelemento[$EmpenhoSubElemento->NaturezaDespesa]){
                $EmpenhoSubElemento->NaturezaDespesa = $fontessubelemento[$EmpenhoSubElemento->NaturezaDespesa];
            }

            $Empenho->SubElementos[] = (object) ['EmpenhoSubElemento' => $EmpenhoSubElemento];
            $RemessaEmpenho->Empenhos[] = (object) ['Empenho' => $Empenho];
            $xi++;
        }
        

        $this->aDados = $RemessaEmpenho;
    }

    private function getEmpenhos()
    {
        $query = DB::table('empempenho')
            ->select(['*',
                'empempenho.e60_emiss',
                'empempenho.e60_codemp',
                'empempenho.e60_numemp',
                'empempenho.e60_anousu',
                'orcfuncao.o52_siconfi',
                'orcsubfuncao.o53_siconfi',
                'orcprojativ.o55_projativ',
                'orcprojativ.o55_tipo',
                'orcprograma.o54_programa',
                'orctiporec.o15_complemento',
                'empempenho.e60_vlremp',
                'emphist.e40_descr',
                'cgm.z01_nome',
                'planodespesa.id AS planodespesa',
                'cgm.z01_cgccpf',
                'orcdotacao.o58_orgao',
                'orcdotacao.o58_unidade',
                'e173_sequencial as ausenciaatojuridico',
                'e174_sequencial as ausenciainstrumentoprevio',
                'empempenho.e60_resumo'
            ])
            ->selectRaw("
                CASE
                    WHEN empempenho.e60_codtipo = 1 THEN 3
                    WHEN empempenho.e60_codtipo = 2 THEN 2
                    WHEN empempenho.e60_codtipo = 3 THEN 3
                    WHEN empempenho.e60_codtipo = 4 THEN 1
                END AS e60_codtipo
            ")
            ->selectRaw("char_length(cgm.z01_cgccpf) as tipo_pessoa")
            ->selectRaw("substring(planodespesa.conta, 1, 8) as natureza")
            ->selectRaw("substring(fonterecurso.codigo_siconfi, 1, 4) as fonte")

            // empenho
            ->leftJoin('empemphist', 'empemphist.e63_numemp', 'empempenho.e60_numemp')
            ->leftJoin('emphist', 'emphist.e40_codhist', 'empemphist.e63_codhist')
            ->join('cgm', 'cgm.z01_numcgm', 'empempenho.e60_numcgm')

            // orcamento
            ->join('orcdotacao', function ($join) {
                $join->on('orcdotacao.o58_coddot', 'empempenho.e60_coddot');
                $join->on('orcdotacao.o58_anousu', 'empempenho.e60_anousu');
            })
            ->join('orcprojativ', function ($join) {
                $join->on('orcprojativ.o55_projativ', 'orcdotacao.o58_projativ');
                $join->on('orcprojativ.o55_anousu', 'orcdotacao.o58_anousu');
            })
            ->join('orcprograma', function ($join) {
                $join->on('orcprograma.o54_programa', 'orcdotacao.o58_programa');
                $join->on('orcprograma.o54_anousu', 'orcdotacao.o58_anousu');
            })
            ->join('orctiporec', 'orctiporec.o15_codigo', 'orcdotacao.o58_codigo')
            ->join('orcfuncao', 'o52_funcao', 'o58_funcao')
            ->join('orcsubfuncao', 'o53_subfuncao', 'o58_subfuncao')
            ->leftJoin('fonterecurso', function ($join) {
                $join->on('fonterecurso.orctiporec_id', 'orctiporec.o15_codigo');
                $join->on('fonterecurso.exercicio', 'orcdotacao.o58_anousu');
            })

            // elemento
            ->join('empelemento', 'empelemento.e64_numemp', 'empempenho.e60_numemp')
            ->leftJoin('conplanoorcamento', function ($join) {
                $join->on('conplanoorcamento.c60_codcon', 'empelemento.e64_codele');
                $join->on('conplanoorcamento.c60_anousu', 'empempenho.e60_anousu');
            })
            ->leftJoin(
                'planodespesaconplanoorcamento AS plandesp',
                'plandesp.conplanoorcamento_codigo',
                'conplanoorcamento.c60_codigo'
            )
            ->leftJoin('planodespesa', function ($join) {
                $join->on('planodespesa.id', 'plandesp.planodespesa_id')->where('uniao', true);
            })

            // Ato juridico / Instrumento Previo
            ->leftJoin(
                'empjustificativaatojuridico',
                'empjustificativaatojuridico.e175_empempenho',
                'empempenho.e60_numemp'
            )
            ->leftJoin(
                'empjustificativainstrumentoprevio',
                'empjustificativainstrumentoprevio.e176_empempenho',
                'empempenho.e60_numemp'
            )
            ->leftJoin(
                'justificativaatojuridico',
                'justificativaatojuridico.e173_sequencial',
                'empjustificativaatojuridico.e175_justificativaatojuridico'
            )
            ->leftJoin(
                'justificativainstrumentoprevio',
                'justificativainstrumentoprevio.e174_sequencial',
                'empjustificativainstrumentoprevio.e176_justificativainstrumentoprevio'
            )
            ->whereBetween('empempenho.e60_emiss', [$this->dtDataInicial, $this->dtDataFinal])
            ->where('empempenho.e60_instit', $this->instit)
            //->where('empempenho.e60_vlranu', '=', 0)            
            ->get();
            

            //$sql_with_bindings = str_replace_array('?', $query->getBindings(), $query->toSql());            
            //$sql_with_bindings = str_replace('"', '', $sql_with_bindings);
            //var_dump($sql_with_bindings);
            //die("Confere");

            

        return $query;
    }
}

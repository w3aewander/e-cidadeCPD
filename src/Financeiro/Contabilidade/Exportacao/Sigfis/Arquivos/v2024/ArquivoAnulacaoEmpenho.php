<?php

namespace ECidade\Financeiro\Contabilidade\Exportacao\Sigfis\Arquivos\v2024;

use ECidade\Financeiro\Contabilidade\Exportacao\Sigfis\Common\Helper;
use ECidade\Financeiro\Contabilidade\Exportacao\Sigfis\Xsd\XSDFactory;
use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Database\Query\JoinClause;
use stdClass;

class ArquivoAnulacaoEmpenho extends ArquivoBase
{
    protected $sNomeArquivo = 'AnulacaoDeEmpenho';

    public function testa($var){
        echo "<pre>";
        print_r($var);
        echo "</pre>";
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
            33904800 => 33904899,
            32902105 => 32902101,
            33903929 => 33903923,
            46907107 => 46907101,
            33909205 => 33909299,
            44905233 => 44905208,
            33903926 => 33903999,
            33904716 => 33903936,
            44905239 => 44905208,
            33903938 => 33903936
        );

        $campos = [
            'e60_numemp',
            'e60_codemp',
            'e60_anousu',
            'o58_orgao',
            'o58_unidade',
            'e60_vlranu',
            'e94_codanu',
            'e94_data',
            'e94_valor',
            'z01_cgccpf',
            'e94_codanu',
            'e94_motivo',
        ];

        $empenhos = DB::table('empempenho')
            ->select($campos)
            ->join('empanulado', 'e94_numemp', '=', 'e60_numemp')
            ->join('db_usuarios', 'id_usuario', 'e94_usuario')
            ->join('db_usuacgm', 'db_usuacgm.id_usuario', 'db_usuarios.id_usuario')
            ->join('cgm', 'z01_numcgm', '=', 'cgmlogin')
            ->join('orcdotacao', function (JoinClause $join) {
                $join->on('e60_anousu', 'o58_anousu')
                    ->on('e60_coddot', 'o58_coddot');
            })
            ->where('e60_anousu', $this->iAnoUsu)
            ->where('e60_instit', $this->instit)
            ->whereBetween('e94_data', [$this->dtDataInicial, $this->dtDataFinal])
            ->get();

        if ($empenhos->isEmpty()) {
            throw new \Exception('Não foi encontrardo nenhum empenho para o arquivo de Remessa ');
        }

        $obj = new stdClass();
        $obj->AnulacoesDeEmpenho = [];

        foreach ($empenhos as $empenho) {
            $elementos = DB::table('empanuladoele')
                ->select(['e95_codele', 'e95_valor', 'c60_estrut'])
                ->join('conplanoorcamento', 'c60_codcon', 'e95_codele')
                /*->join('planodespesaconplanoorcamento', 'conplanoorcamento_codigo', 'c60_codigo')
                ->join('planodespesa', function (JoinClause $join) {
                    $join->on('planodespesa.id', 'planodespesa_id')
                        ->where('uniao', '=', 't');
                })*/
                ->where('e95_codanu', $empenho->e94_codanu)
                ->where('c60_anousu', $empenho->e60_anousu)
                //->where('exercicio', $empenho->e60_anousu)
                //->where('uniao', '=', 't');
                ->get()                
                ->map(function ($elemento) {
                    return (object)[
                        'Identificador' => $elemento->e95_codele,
                        'Valor' => $elemento->e95_valor,
                        'NaturezaDespesa' =>  substr($elemento->c60_estrut, 1, 8),
                    ];
                })
                ->toArray();

                

                if($fontessubelemento[$elementos[0]->NaturezaDespesa]){
                $elementos[0]->NaturezaDespesa = $fontessubelemento[$elementos[0]->NaturezaDespesa];

                

            }

            $data = (object) [
                'Identificador' => $empenho->e94_codanu,
                'CodigoUnidadeGestora' => $this->sCodigoTribunal,
                'NumeroEmpenho' => $empenho->e60_codemp,
                'AnoEmpenho' => $empenho->e60_anousu,
                'Competencia' => $this->competencia,
                'CodigoOrgao' => $empenho->o58_orgao,
                'CodigoUnidadeOrcamentaria' => $empenho->o58_unidade,
                'NumeroAnulacao' => $empenho->e94_codanu,
                'DataAnulacao' => $empenho->e94_data,
                'ValorAnulacao' => $empenho->e94_valor,
                'CpfResponsavel' => $empenho->z01_cgccpf,
                'JustificativaAnulacao' => utf8_encode(Helper::convertAndLimit($empenho->e94_motivo, 255)),
                'SubElementos' => (object)['AnulacaoEmpenhoSubElemento'=>$elementos],
            ];

            $obj->AnulacoesDeEmpenho[] = (object)['AnulacaoDeEmpenho' => $data];
        }

        $this->aDados = $obj;
    }
}

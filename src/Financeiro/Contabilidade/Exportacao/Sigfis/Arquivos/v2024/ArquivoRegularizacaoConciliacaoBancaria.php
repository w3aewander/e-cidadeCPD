<?php

namespace ECidade\Financeiro\Contabilidade\Exportacao\Sigfis\Arquivos\v2024;

use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Database\Eloquent\Builder;
use \stdClass;

class ArquivoRegularizacaoConciliacaoBancaria extends ArquivoBase
{
    protected $sNomeArquivo  = 'RegularizacaoConciliacaoBancaria';
    /**
    * Busca os dados para gerar o Arquivo de Unidade Orçamentária
    */
    public function gerarDados()
    {

        $regularizacoesConciliacao = [];

        $camposRegularizacaoTesouraria = [
            DB::raw('conciliapendcorrente.k89_concilia as conciliacao_pendencia'),
            DB::raw('k84_sequencial as identificador'),
            DB::raw("extract(year from k68_data) as ano_conciliacao"),
            DB::raw('k68_data as data_regularizacao'),
            DB::raw('k12_valor as valor_regularizacao'),
            DB::raw("
                case when k12_histcor is not null then k12_histcor
                     when e60_resumo is not null then e60_resumo
                     when k17_texto is not null then k17_texto
                     else '' END as justificativa_regularizacao
            ")
        ];

        $regularizacoesTesouraria = db::table('concilia')
        ->select($camposRegularizacaoTesouraria)
        ->join('conciliaitem', 'k83_concilia', 'k68_sequencial')
        ->join('conciliacor', 'k84_conciliaitem', 'k83_sequencial')
        ->join('conciliapendcorrente', function ($join) {
            $join->on('conciliapendcorrente.k89_id', 'conciliacor.k84_id')
            ->on('conciliapendcorrente.k89_data', 'conciliacor.k84_data')
            ->on('conciliapendcorrente.k89_autent', 'conciliacor.k84_autent');
        })
        ->join('corrente', function ($join) {
            $join->on('corrente.k12_id', 'conciliacor.k84_id')
            ->on('corrente.k12_data', 'conciliacor.k84_data')
            ->on('corrente.k12_autent', 'conciliacor.k84_autent');
        })
        ->leftJoin('corhist', function ($join) {
            $join->on('corhist.k12_id', 'conciliacor.k84_id')
            ->on('corhist.k12_data', 'conciliacor.k84_data')
            ->on('corhist.k12_autent', 'conciliacor.k84_autent');
        })
        ->leftJoin('corlanc', function ($join) {
            $join->on('corlanc.k12_id', 'conciliacor.k84_id')
            ->on('corlanc.k12_data', 'conciliacor.k84_data')
            ->on('corlanc.k12_autent', 'conciliacor.k84_autent');
        })
        ->leftJoin('slip', 'slip.k17_codigo', 'corlanc.k12_codigo')
        ->leftJoin('coremp', function ($join) {
            $join->on('coremp.k12_id', 'conciliacor.k84_id')
            ->on('coremp.k12_data', 'conciliacor.k84_data')
            ->on('coremp.k12_autent', 'conciliacor.k84_autent');
        })
        ->leftJoin('empempenho', 'e60_numemp', 'coremp.k12_empen')
        ->where('k84_data', '<', $this->dtDataInicial)
        ->whereBetween('k68_data', [$this->dtDataInicial,$this->dtDataFinal]);

        $camposRegularizacaoExtrato = [
            DB::raw('conciliapendextrato.k88_concilia as conciliacao_pendencia'),
            DB::raw('k87_extratolinha as identificador'),
            DB::raw("extract(year from k68_data) as ano_conciliacao"),
            DB::raw('k68_data as data_regularizacao'),
            DB::raw('k86_valor as valor_regularizacao'),
            DB::raw('k86_observacao as justificativa_regularizacao')
        ];

        $regularizacoesExtrato = db::table('concilia')
        ->select($camposRegularizacaoExtrato)
        ->join('conciliaitem', 'k83_concilia', 'k68_sequencial')
        ->join('conciliaextrato', 'k87_conciliaitem', 'k83_sequencial')
        ->join('extratolinha', 'k86_sequencial', 'k87_extratolinha')
        ->join('conciliapendextrato', 'k88_extratolinha', 'k86_sequencial')
        ->where('k86_data', '<', $this->dtDataInicial)
        ->whereBetween('k68_data', [$this->dtDataInicial,$this->dtDataFinal]);
        
        $regularizacoesGerais = $regularizacoesTesouraria->unionAll($regularizacoesExtrato);

        $regularizacoesConciliacao = DB::table(DB::raw("({$regularizacoesGerais->toSql()}) as sub"))
        ->mergeBindings($regularizacoesGerais)
        ->selectRaw("
            conciliacao_pendencia,
            identificador,
            ano_conciliacao,
            data_regularizacao,
            valor_regularizacao,
            justificativa_regularizacao
        ")
        
        ->orderBy('conciliacao_pendencia')
        ->orderBy('data_regularizacao')
        ->get();

        $obj = new stdClass();
        $obj->RegularizacoesConciliacaoBancaria = [];

        foreach ($regularizacoesConciliacao as $regularizacaoConciliacao) {
            $dadosRegularizacao = (object)[
                'Identificador' => $regularizacaoConciliacao->identificador,
                'CodigoUnidadeGestora' => $this->sCodigoTribunal,
                'Exercicio' => $this->iAnoUsu,
                'Competencia' => $this->competencia,
                'AnoConciliacaoBancaria' =>  $regularizacaoConciliacao->ano_conciliacao,
                'NumeroConciliacaoBancaria' => $regularizacaoConciliacao->conciliacao_pendencia,
                'DataRegularizacao' => $regularizacaoConciliacao->data_regularizacao,
                'ValorRegularizacao' => $regularizacaoConciliacao->valor_regularizacao,
                'Descricao' => $regularizacaoConciliacao->justificativa_regularizacao
            ];

            $obj->RegularizacoesConciliacaoBancaria[] = (object)[
                'RegularizacaoConciliacaoBancaria' => $dadosRegularizacao
            ];
        }

        $this->aDados =  $obj;
    }
}

<?php

namespace ECidade\Financeiro\Contabilidade\Exportacao\Sigfis\Arquivos\v2024;

use ECidade\Financeiro\Contabilidade\Exportacao\Sigfis\Common\Helper;
use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Database\Query\JoinClause;
use stdClass;

class ArquivoLiquidacaoRestosPagarEstorno extends ArquivoBase
{
    //protected $sNomeArquivo = 'LiquidacaoRestosPagarEstorno';
    protected $sNomeArquivo = 'LiquidacaoRestosPagarAnulacao';

    public function gerarDados()
    {

        $empenhos = [];

        $campos = [
            'e60_numemp', 'e60_anousu', 'e60_codemp',
            'e50_anousu',
            'o58_orgao',
            'o58_unidade',
            'e69_codnota', 'e69_dtinclusao', 'e69_numero',
            'c72_complem',
            'c70_valor','c70_data',
            'z01_cgccpf as cpf_responsavel'
        ];

        $empenhos = DB::table('empresto')
            ->select($campos)
            ->join('empenho.empempenho', 'e60_numemp', 'e91_numemp')
            ->join('empnota', 'e69_numemp', 'e60_numemp')
            ->join('orcamento.orcdotacao', function (JoinClause $join) {
                $join->on('e60_anousu', 'o58_anousu')
                    ->on('e60_coddot', 'o58_coddot');
            })
            ->join('pagordemnota', 'e71_codnota', 'e69_codnota')
            ->join('pagordem', 'e50_codord', 'e71_codord')
            ->join('conlancamord', 'c80_codord', 'e71_codord')
            ->join('conlancam', 'c70_codlan', 'c80_codlan')
            ->join('conlancamdoc', 'c71_codlan', 'c70_codlan')
            ->join('conhistdoc', 'c53_coddoc', 'c71_coddoc')
            ->join('conlancamcompl', 'c72_codlan', 'c70_codlan')
            ->join('lancamentoscontabeislog AS log', function (JoinClause $join) {
                $join->on('codlan', 'c70_codlan')
                    ->where('tipo_movimento', 1);
            })
            ->join('db_usuacgm', 'db_usuacgm.id_usuario', 'log.id_usuario')
            ->join('cgm AS cgmusu', 'cgmusu.z01_numcgm', 'cgmlogin')
            ->where('c53_tipo', '21')
            ->where('e91_anousu', $this->iAnoUsu)
            ->where('e60_instit', $this->instit)
            ->whereBetween('c70_data', [$this->dtDataInicial, $this->dtDataFinal])
            ->get();


        $obj = new stdClass();
        $obj->LiquidacoesRestosPagarEstornos = [];

        foreach ($empenhos as $empenho) {
            $dadosLiquidacaoRPEstorno = (object)[
                "Identificador" => $empenho->e69_codnota,
                "CodigoUnidadeGestora" => $this->sCodigoTribunal,
                "NumeroEmpenho" => $empenho->e60_codemp,
                "AnoEmpenho" => $empenho->e60_anousu,
                "Competencia" => $this->competencia,
                "NumeroLiquidacaoRestosPagar" => $empenho->e69_codnota,
                "AnoLiquidacao" => $empenho->e50_anousu,
                "NumeroDocumentoAnulacao" => $empenho->e69_codnota,
                "DataEstorno" => $empenho->c70_data,
                "CPFResponsavel" => $empenho->cpf_responsavel,
                "Justificativa" => Helper::convertAndLimit($empenho->c72_complem, 4000),
                "Valor" => $empenho->c70_valor,
                "CodigoOrgao" => $empenho->o58_orgao,
                "CodigoUnidadeOrcamentaria" => $empenho->o58_unidade
            ];

            $obj->LiquidacoesRestosPagarEstornos[] = (object)[
                'LiquidacaoRestosPagarEstorno' => $dadosLiquidacaoRPEstorno
            ];
        }
        $this->aDados = $obj;
    }
}

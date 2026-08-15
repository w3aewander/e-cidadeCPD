<?php

namespace ECidade\Financeiro\Contabilidade\Exportacao\Sigfis\Arquivos\v2024;

use BusinessException;
use ECidade\Financeiro\Contabilidade\Exportacao\Sigfis\Common\Helper;
use Illuminate\Database\Capsule\Manager as DB;
use stdClass;

class ArquivoAnulacaoPagamentoDeEmpenhoConsignacaoRetencao extends ArquivoBase
{
    protected $sNomeArquivo = 'AnulacaoPagamentoDeEmpenhoConsignacaoRetencao';

    public function testa($var){
        echo "<pre>";
        print_r($var);
        echo "</pre>";
    }

    
    public function retencoesAnuladas(){
        $inst = $this->instit;
        $di = $this->dtDataInicial;
        $df = $this->dtDataFinal;
        $ano = $this->iAnoUsu;

        $sql = pg_query("SELECT DISTINCT c70_codlan, e60_codemp, c84_slip, c70_data, c70_valor, cgmusu.z01_cgccpf AS cpfusu, o58_orgao, o58_unidade, c80_codord, e60_anousu, c72_complem, retencaotipocalc.e32_sequencial, EXTRACT(YEAR FROM k17_data) AS anoslip FROM conlancam JOIN conlancaminstit ON conlancam.c70_codlan = conlancaminstit.c02_codlan JOIN conlancamdoc ON conlancam.c70_codlan = conlancamdoc.c71_codlan JOIN conhistdoc ON conlancamdoc.c71_coddoc = conhistdoc.c53_coddoc JOIN conlancamslip ON conlancam.c70_codlan = conlancamslip.c84_conlancam JOIN slip ON slip.k17_codigo = conlancamslip.c84_slip JOIN conlancamcompl ON c72_codlan = c84_conlancam JOIN slipretencaoreceitas ON slipretencaoreceitas.k206_slip = slip.k17_codigo JOIN retencaoreceitas ON retencaoreceitas.e23_sequencial = slipretencaoreceitas.k206_retencaoreceitas JOIN retencaotiporec ON retencaotiporec.e21_sequencial = retencaoreceitas.e23_retencaotiporec JOIN retencaotipocalc ON retencaotipocalc.e32_sequencial = retencaotiporec.e21_retencaotipocalc JOIN conlancamemp ON conlancamemp.c75_codlan = conlancam.c70_codlan JOIN empempenho ON e60_numemp = c75_numemp JOIN orcdotacao ON empempenho.e60_anousu = orcdotacao.o58_anousu AND empempenho.e60_coddot = orcdotacao.o58_coddot JOIN lancamentoscontabeislog ON lancamentoscontabeislog.codlan = conlancam.c70_codlan AND lancamentoscontabeislog.tipo_movimento = 1 JOIN db_usuacgm ON db_usuacgm.id_usuario = lancamentoscontabeislog.id_usuario JOIN cgm cgmusu ON cgmusu.z01_numcgm = db_usuacgm.cgmlogin JOIN conlancamord ON conlancam.c70_codlan = conlancamord.c80_codlan WHERE e60_anousu = {$ano} AND c02_instit = {$inst} AND c53_tipo = 163 AND c70_data BETWEEN '{$di}' AND '{$df}'");
        $resultado = array();
        while ($linha = pg_fetch_object($sql)) {
            $resultado[] = $linha;
        }
        return $resultado;
    }

    public function gerarDados(){
        //$retencoes = $this->getRetencoes();
        $retencoes = $this->retencoesAnuladas();

        if (count($retencoes) == 0) {
            throw new BusinessException('Não há retenções para a competência informada.');
        }

        $EmpenhoAnuConsignacaoRetencao = new stdClass();
        $EmpenhoAnuConsignacaoRetencao->AnulacoesPagamentosDeEmpenhoConsignacoesRetencoes = [];
        $ix = 1;
        //$this->testa($retencoes); die("Confere");
        foreach ($retencoes as $retencao) {            
            $AnuConsigRet = new stdClass();
            $AnuConsigRet->Identificador = $ix;
            $AnuConsigRet->CodigoUnidadeGestora = $this->sCodigoTribunal;
            $AnuConsigRet->Competencia = $this->competencia;
            $AnuConsigRet->NumeroEmpenho = $retencao->e60_codemp;
            $AnuConsigRet->AnoEmpenho = $retencao->e60_anousu;            
            $AnuConsigRet->NumeroNotaPagamentoDeEmpenhoConsignacaoRetencao = $retencao->c84_slip;
            $AnuConsigRet->AnoNotaPagamentoDeEmpenhoConsignacaoRetencao = $retencao->anoslip;            
            $AnuConsigRet->NumeroNotaAnulacao = $retencao->c70_codlan;            
            $AnuConsigRet->DataAnulacao = $retencao->c70_data;
            $AnuConsigRet->Justificativa = $retencao->c72_complem;
            $AnuConsigRet->CpfResponsavel = $retencao->cpfusu;
            $AnuConsigRet->TipoConsignacaoRetencaoPaga = $this->convertTipoRetencao($retencao->e32_sequencial);
            
            $AnuConsigRet->Valor = $retencao->c70_valor;
            $AnuConsigRet->TipoAnulacaoMovimento = 2;

            $EmpenhoAnuConsignacaoRetencao->AnulacoesPagamentosDeEmpenhoConsignacoesRetencoes[] = (object) [
                'AnulacaoPagamentoDeEmpenhoConsignacaoRetencao' => $AnuConsigRet
            ];
            $ix++;
        }

        if($ix == 100000){
            throw new BusinessException('Não há retenções para a competência informada.');
        }

        $this->aDados = $EmpenhoAnuConsignacaoRetencao;
    }

    private function getRetencoes()
    {
        $query = DB::table('empempenho')
            ->select([
                'c70_codlan as Identificador',
                'empempenho.e60_codemp as NumeroEmpenho',
                'empempenho.e60_anousu as AnoEmpenho',
                'orcdotacao.o58_orgao as CodigoOrgao',
                'orcdotacao.o58_unidade as CodigoUnidadeOrcamentaria',
                'empnota.e69_numero as NumeroNota',
                'c70_data as DataAnulacao',
                'e32_sequencial as Tipo',
                'c70_valor as Valor',
                'cgmusu.z01_cgccpf as CPFResponsavel',
                'empnota.e69_codnota as IdentificadorLiquidacao',
                'empnota.e69_anousu as AnoLiquidacaoEmpenho',
                'c72_complem as Justificativa',
                'empempenho.e60_numemp',
                'e50_codord',
                'e21_tiporet',
                'e69_numero'
            ])
            ->join('orcdotacao', function ($join) {
                $join
                ->on('o58_anousu', 'e60_anousu')
                ->on('o58_coddot', 'e60_coddot');
            })
            ->join('empnota', 'e69_numemp', 'e60_numemp')
            ->join('pagordemnota', 'e71_codnota', 'e69_codnota')
            ->join('pagordem', 'e50_codord', 'e71_codord')
            ->join('db_usuacgm', 'db_usuacgm.id_usuario', 'e50_id_usuario')
            ->join('cgm AS cgmusu', 'cgmlogin', 'cgmusu.z01_numcgm')
            ->join('conlancamemp', 'c75_numemp', 'e60_numemp')
            ->join('conlancam', 'c75_codlan', 'c70_codlan')
            ->join('conlancamcompl', 'c72_codlan', 'c70_codlan')
            ->join('conlancamdoc', 'c71_codlan', 'c70_codlan')
            ->join('conlancamord AS ord_ret', function ($join) {
                $join
                ->on('ord_ret.c80_codord', 'e71_codord')
                ->on('ord_ret.c80_codlan', 'c70_codlan');
            })
            ->join('conlancamretencao', 'c127_conlancam', 'c70_codlan')
            ->join('retencaotiporec', 'e21_sequencial', 'c127_retencaotiporec')
            ->join('retencaotipocalc', 'e32_sequencial', 'e21_retencaotipocalc')
            //->whereIn('c71_coddoc', [6003, 6005])
            ->whereIn('c71_coddoc', [163, 6005])
            //->whereIn('c71_coddoc', [163, 6004, 6005, 6000, 6001])
            //->whereIn('c71_coddoc', [6005, 6003])
            ->where('e60_instit', $this->instit)
            ->whereBetween('c70_data', [$this->dtDataInicial, $this->dtDataFinal])
            ->get();

            //$sql_with_bindings = str_replace_array('?', $query->getBindings(), $query->toSql());
            //$sql_with_bindings = str_replace("\"", "", $sql_with_bindings);
            //var_dump($sql_with_bindings); die("confere");

        return $query;
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

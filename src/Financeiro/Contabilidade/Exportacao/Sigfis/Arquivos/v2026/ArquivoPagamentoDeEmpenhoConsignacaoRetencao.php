<?php

namespace ECidade\Financeiro\Contabilidade\Exportacao\Sigfis\Arquivos\v2024;

use BusinessException;
use ECidade\Financeiro\Contabilidade\Exportacao\Sigfis\Common\Helper;
use Illuminate\Database\Capsule\Manager as DB;
use stdClass;

class ArquivoPagamentoDeEmpenhoConsignacaoRetencao extends ArquivoBase
{
    protected $sNomeArquivo = 'PagamentoDeEmpenhoConsignacaoRetencao';

    public function testa($var){
        echo "<pre>";
        print_r($var);
        echo "</pre>";
    }


//====================================================================================================
//====================================================================================================
//====================================================================================================
//====================================================================================================

public function todosNotaFiscal($seqempenho, $codlan, $data, $valor){
    $inst = $this->instit;
    $sql = pg_query("SELECT c71_coddoc, o58_orgao, o58_unidade, e60_numemp, e60_codemp, e60_anousu, e69_codnota, e69_anousu, e69_numero, c70_codlan, c70_data, c70_valor, c72_complem, z01_nome, z01_cgccpf as cpf from empempenho inner join empnota on e69_numemp = e60_numemp inner join orcdotacao on o58_anousu = e60_anousu and o58_coddot = e60_coddot inner join pagordemnota on e71_codnota = e69_codnota inner join pagordem on e50_codord = e71_codord inner join conlancamord on c80_codord = e71_codord inner join conlancam on c70_codlan = c80_codlan inner join conlancamdoc on c71_codlan = c70_codlan inner join conlancamcompl on c72_codlan = c70_codlan inner join lancamentoscontabeislog as log on codlan = c70_codlan and tipo_movimento = 1 inner join db_usuacgm on db_usuacgm.id_usuario = log.id_usuario inner join cgm as cgmusu on cgmusu.z01_numcgm = cgmlogin where e60_numemp = {$seqempenho} AND e60_instit = {$inst} and c70_data = '{$data}' AND c70_valor = {$valor} order by e60_numemp asc");
    $resultado = pg_fetch_all($sql);
    return $resultado[0];
}

public function buscaEmpenhosRP3(){
    $inst = $this->instit;
    $di = $this->dtDataInicial;
    $df = $this->dtDataFinal;
    $ano = $this->iAnoUsu;

    $sql = pg_query("SELECT DISTINCT c70_codlan, e60_codemp, e60_numemp, c84_slip, c70_data, c70_valor, o58_orgao, o58_unidade, c80_codord, e60_anousu, retencaotipocalc.e32_sequencial, cgmretencao.z01_nome as nomecgmretencao, cgmretencao.z01_cgccpf as cnpjcgmretencao, cgmusu.z01_numcgm AS cgmusu, cgmusu.z01_cgccpf AS cpfusu, c82_reduz, db89_db_bancos, db89_codagencia, db89_digito, concat(db83_conta, '-', db83_dvconta) as db83_conta, db83_sequencial, e23_sequencial, e21_sequencial FROM conlancam LEFT JOIN conlancaminstit ON conlancam.c70_codlan = conlancaminstit.c02_codlan LEFT JOIN conlancamdoc ON conlancam.c70_codlan = conlancamdoc.c71_codlan LEFT JOIN conhistdoc ON conlancamdoc.c71_coddoc = conhistdoc.c53_coddoc LEFT JOIN conlancampag on conlancampag.c82_codlan = conlancam.c70_codlan LEFT JOIN conplanocontabancaria on c56_reduz = c82_reduz and c56_anousu = c82_anousu LEFT JOIN contabancaria ON contabancaria.db83_sequencial = conplanocontabancaria.c56_contabancaria LEFT JOIN bancoagencia ON bancoagencia.db89_sequencial = contabancaria.db83_bancoagencia LEFT JOIN conlancamslip ON conlancamslip.c84_conlancam = conlancam.c70_codlan LEFT JOIN slipretencaoreceitas ON k206_slip = c84_slip LEFT JOIN retencaoreceitas ON retencaoreceitas.e23_sequencial = slipretencaoreceitas.k206_retencaoreceitas AND retencaoreceitas.e23_valorretencao = conlancam.c70_valor LEFT JOIN retencaotiporec ON retencaotiporec.e21_sequencial = retencaoreceitas.e23_retencaotiporec LEFT JOIN retencaotipocalc ON retencaotipocalc.e32_sequencial = retencaotiporec.e21_retencaotipocalc LEFT JOIN retencaotiporeccgm ON retencaotiporeccgm.e48_retencaotiporec = retencaotiporec.e21_sequencial LEFT JOIN conlancamemp ON conlancamemp.c75_codlan = conlancam.c70_codlan LEFT JOIN empempenho ON e60_numemp = c75_numemp LEFT JOIN orcdotacao on empempenho.e60_anousu = orcdotacao.o58_anousu and empempenho.e60_coddot = orcdotacao.o58_coddot LEFT JOIN cgm as cgmretencao ON z01_numcgm = e48_cgm LEFT JOIN lancamentoscontabeislog ON lancamentoscontabeislog.codlan = conlancam.c70_codlan AND lancamentoscontabeislog.tipo_movimento = 1 LEFT JOIN db_usuacgm ON db_usuacgm.id_usuario = lancamentoscontabeislog.id_usuario LEFT JOIN cgm cgmusu ON cgmusu.z01_numcgm = db_usuacgm.cgmlogin LEFT JOIN conlancamord ON conlancam.c70_codlan = conlancamord.c80_codlan WHERE e60_anousu = {$ano} AND c02_instit in ({$inst}) AND c53_tipo = 161 AND c70_data BETWEEN '{$di}' and '{$df}' UNION select distinct 0 as c70_codlan, e60_codemp, e60_numemp, COALESCE(k206_slip, e71_codord) as c84_slip, k12_data, e23_valorretencao, o58_orgao, o58_unidade, e71_codord, e60_anousu, retencaotipocalc.e32_sequencial, cgmretencao.z01_nome as nomecgmretencao, cgmretencao.z01_cgccpf as cnpjcgmretencao, cgmusu.z01_numcgm AS cgmusu, cgmusu.z01_cgccpf AS cpfusu, 0, null, null, null, null, 0, e23_sequencial, e21_sequencial FROM retencaoreceitas JOIN retencaopagordem on retencaopagordem.e20_sequencial = retencaoreceitas.e23_retencaopagordem JOIN pagordem on pagordem.e50_codord = retencaopagordem.e20_pagordem JOIN pagordemele on pagordemele.e53_codord = pagordem.e50_codord JOIN empempenho on e60_numemp = pagordem.e50_numemp JOIN pagordemnota ON pagordemnota.e71_codord = pagordem.e50_codord and e71_anulado is false JOIN empnota on empnota.e69_numemp = empempenho.e60_numemp JOIN retencaotiporec on retencaotiporec.e21_sequencial = retencaoreceitas.e23_retencaotiporec JOIN retencaotipocalc on retencaotiporec.e21_retencaotipocalc = retencaotipocalc.e32_sequencial JOIN tabrec ON tabrec.k02_codigo = retencaotiporec.e21_receita JOIN orcdotacao on empempenho.e60_anousu = orcdotacao.o58_anousu and empempenho.e60_coddot = orcdotacao.o58_coddot JOIN retencaocorgrupocorrente on e47_retencaoreceita = e23_sequencial JOIN corgrupocorrente on k105_sequencial = e47_corgrupocorrente jOIN corrente on k105_id = corrente.k12_id and k105_autent = corrente.k12_autent and k105_data = corrente.k12_data and corrente.k12_instit = 1 JOIN retencaotiporeccgm ON retencaotiporeccgm.e48_retencaotiporec = retencaotiporec.e21_sequencial JOIN cgm as cgmretencao ON z01_numcgm = e48_cgm JOIN db_usuacgm ON id_usuario = e50_id_usuario JOIN cgm as cgmusu ON cgmlogin = cgmusu.z01_numcgm LEFT JOIN slipretencaoreceitas ON retencaoreceitas.e23_sequencial = slipretencaoreceitas.k206_retencaoreceitas WHERE e60_anousu = {$ano} AND e60_instit in ({$inst}) AND k12_estorn is FALSE AND k12_data BETWEEN '{$di}' and '{$df}' AND e23_ativo IS TRUE AND k02_tipo = 'O'");

    /*
    $sql = pg_query("SELECT DISTINCT c70_codlan, e60_codemp, e60_numemp, c84_slip, c70_data, c70_valor, o58_orgao, o58_unidade, c80_codord, e60_anousu, retencaotipocalc.e32_sequencial, cgmretencao.z01_nome as nomecgmretencao, cgmretencao.z01_cgccpf as cnpjcgmretencao, cgmusu.z01_numcgm AS cgmusu, cgmusu.z01_cgccpf AS cpfusu, c82_reduz, db89_db_bancos, db89_codagencia, db89_digito, concat(db83_conta, '-', db83_dvconta) as db83_conta, db83_sequencial, e23_sequencial, e21_sequencial FROM conlancam JOIN conlancaminstit ON conlancam.c70_codlan = conlancaminstit.c02_codlan JOIN conlancamdoc ON conlancam.c70_codlan = conlancamdoc.c71_codlan JOIN conhistdoc ON conlancamdoc.c71_coddoc = conhistdoc.c53_coddoc JOIN conlancampag on conlancampag.c82_codlan = conlancam.c70_codlan JOIN conplanocontabancaria on c56_reduz = c82_reduz and c56_anousu = c82_anousu JOIN contabancaria ON contabancaria.db83_sequencial = conplanocontabancaria.c56_contabancaria JOIN bancoagencia ON bancoagencia.db89_sequencial = contabancaria.db83_bancoagencia JOIN conlancamslip ON conlancamslip.c84_conlancam = conlancam.c70_codlan JOIN slipretencaoreceitas ON k206_slip = c84_slip JOIN retencaoreceitas ON retencaoreceitas.e23_sequencial = slipretencaoreceitas.k206_retencaoreceitas AND retencaoreceitas.e23_valorretencao = conlancam.c70_valor JOIN retencaotiporec ON retencaotiporec.e21_sequencial = retencaoreceitas.e23_retencaotiporec JOIN retencaotipocalc ON retencaotipocalc.e32_sequencial = retencaotiporec.e21_retencaotipocalc JOIN retencaotiporeccgm ON retencaotiporeccgm.e48_retencaotiporec = retencaotiporec.e21_sequencial JOIN conlancamemp ON conlancamemp.c75_codlan = conlancam.c70_codlan JOIN empempenho ON e60_numemp = c75_numemp JOIN orcdotacao on empempenho.e60_anousu = orcdotacao.o58_anousu and empempenho.e60_coddot = orcdotacao.o58_coddot JOIN cgm as cgmretencao ON z01_numcgm = e48_cgm JOIN lancamentoscontabeislog ON lancamentoscontabeislog.codlan = conlancam.c70_codlan AND lancamentoscontabeislog.tipo_movimento = 1 JOIN db_usuacgm ON db_usuacgm.id_usuario = lancamentoscontabeislog.id_usuario JOIN cgm cgmusu ON cgmusu.z01_numcgm = db_usuacgm.cgmlogin JOIN conlancamord ON conlancam.c70_codlan = conlancamord.c80_codlan WHERE e60_anousu = {$ano} AND c02_instit in ({$inst}) AND c53_tipo = 161 AND c70_data BETWEEN '{$di}' and '{$df}' UNION select distinct 0 as c70_codlan, e60_codemp, e60_numemp, COALESCE(k206_slip, e71_codord) as c84_slip, k12_data, e23_valorretencao, o58_orgao, o58_unidade, e71_codord, e60_anousu, retencaotipocalc.e32_sequencial, cgmretencao.z01_nome as nomecgmretencao, cgmretencao.z01_cgccpf as cnpjcgmretencao, cgmusu.z01_numcgm AS cgmusu, cgmusu.z01_cgccpf AS cpfusu, 0, null, null, null, null, 0, e23_sequencial, e21_sequencial FROM retencaoreceitas JOIN retencaopagordem on retencaopagordem.e20_sequencial = retencaoreceitas.e23_retencaopagordem JOIN pagordem on pagordem.e50_codord = retencaopagordem.e20_pagordem JOIN pagordemele on pagordemele.e53_codord = pagordem.e50_codord JOIN empempenho on e60_numemp = pagordem.e50_numemp JOIN pagordemnota ON pagordemnota.e71_codord = pagordem.e50_codord and e71_anulado is false JOIN empnota on empnota.e69_numemp = empempenho.e60_numemp JOIN retencaotiporec on retencaotiporec.e21_sequencial = retencaoreceitas.e23_retencaotiporec JOIN retencaotipocalc on retencaotiporec.e21_retencaotipocalc = retencaotipocalc.e32_sequencial JOIN tabrec ON tabrec.k02_codigo = retencaotiporec.e21_receita JOIN orcdotacao on empempenho.e60_anousu = orcdotacao.o58_anousu and empempenho.e60_coddot = orcdotacao.o58_coddot JOIN retencaocorgrupocorrente on e47_retencaoreceita = e23_sequencial JOIN corgrupocorrente on k105_sequencial = e47_corgrupocorrente jOIN corrente on k105_id = corrente.k12_id and k105_autent = corrente.k12_autent and k105_data = corrente.k12_data and corrente.k12_instit = 1 JOIN retencaotiporeccgm ON retencaotiporeccgm.e48_retencaotiporec = retencaotiporec.e21_sequencial JOIN cgm as cgmretencao ON z01_numcgm = e48_cgm JOIN db_usuacgm ON id_usuario = e50_id_usuario JOIN cgm as cgmusu ON cgmlogin = cgmusu.z01_numcgm LEFT JOIN slipretencaoreceitas ON retencaoreceitas.e23_sequencial = slipretencaoreceitas.k206_retencaoreceitas WHERE e60_anousu = {$ano} AND e60_instit in ({$inst}) AND k12_estorn is FALSE AND k12_data BETWEEN '{$di}' and '{$df}' AND e23_ativo IS TRUE AND k02_tipo = 'O'");
    */



    $resultado = array();
    while ($linha = pg_fetch_object($sql)) {
        $resultado[] = $linha;
    }
    return $resultado;
}

public function buscaCredor($slip){    
    $sql = pg_query("SELECT DISTINCT cgm.z01_numcgm, cgm.z01_nome, cgm.z01_cgccpf from slip left join slipnum on slipnum.k17_codigo = slip.k17_codigo left join cgm on cgm.z01_numcgm = slipnum.k17_numcgm where slip.k17_codigo = {$slip}");
    $resultado = pg_fetch_all($sql);
    return $resultado[0];
}


public function getContaBancaria($sequencialRetencao){
    $contaBancaria = new stdClass();
    $contaBancaria->reduzido = null;
    $contaBancaria->db83_sequencial = null;
    $contaBancaria->Banco = null;
    $contaBancaria->Agencia = null;
        
    $reduzido = $this->getContaSlip($sequencialRetencao);        
    if (empty($reduzido)) {
        $reduzido = $this->getContaOP($sequencialRetencao);
    }
    if (empty($reduzido)) {
        $reduzido = $this->getContaEmpenho($sequencialRetencao);
    }
    if (empty($reduzido)) {
        $reduzido = $this->getContaCredor($sequencialRetencao);
    }
    if (!empty($reduzido)) {
        $dadosContaBancaria = $this->getDadosContaBancaria($reduzido);
        
        if (!empty($dadosContaBancaria)) {
            return $dadosContaBancaria;
        }
    }

    return $contaBancaria;
}

private function getContaSlip($sequencialRetencao)
    {
        return DB::table("empagemovslips")
            ->select("slip.k17_credito as reduzido")
            ->join(
                "slipempagemovslips",
                "k108_empagemovslips",
                "k107_sequencial"
            )
            ->join(
                "slip",
                "slip.k17_codigo",
                "k108_slip"
            )
            ->where(
                "k107_retencao",
                $sequencialRetencao
            )
            ->first()->reduzido;
    }

    private function getContaOP($sequencialRetencao)
    {
        return DB::table("retencaoreceitas")
            ->select("c82_reduz as reduzido")
            ->join(
                "retencaopagordem",
                "e20_sequencial",
                "e23_retencaopagordem"
            )
            ->join(
                "conlancamord",
                "c80_codord",
                "e20_pagordem"
            )
            ->join(
                "conlancampag",
                "c82_codlan",
                "c80_codlan"
            )
            ->join(
                "conlancamdoc",
                "c71_codlan",
                "c82_codlan"
            )
            ->join(
                "conhistdoc",
                "c53_coddoc",
                "c71_coddoc"
            )
            ->where(
                "c53_tipo",
                30
            )
            ->where(
                "e23_sequencial",
                $sequencialRetencao
            )
            ->orderBy(
                "c80_data",
                "desc"
            )
            ->first()->reduzido;
    }

    private function getContaEmpenho($sequencialRetencao)
    {
        return DB::table("retencaoreceitas")
            ->select("c82_reduz as reduzido")
            ->join(
                "retencaopagordem",
                "e20_sequencial",
                "e23_retencaopagordem"
            )
            ->join(
                "pagordem",
                "e50_codord",
                "e20_pagordem"
            )
            ->join(
                "conlancamemp",
                "c75_numemp",
                "e50_numemp"
            )
            ->join(
                "conlancampag",
                "c82_codlan",
                "c75_codlan"
            )
            ->join(
                "conlancamdoc",
                "c71_codlan",
                "c82_codlan"
            )
            ->join(
                "conhistdoc",
                "c53_coddoc",
                "c71_coddoc"
            )
            ->where(
                "c53_tipo",
                30
            )
            ->where(
                "e23_sequencial",
                $sequencialRetencao
            )
            ->orderBy(
                "c75_data",
                "desc"
            )
            ->first()->reduzido;
    }

    private function getContaCredor($sequencialRetencao)
    {
        return DB::table("retencaoreceitas")
            ->select("e83_conta as reduzido")
            ->join(
                "retencaopagordem",
                "e20_sequencial",
                "e23_retencaopagordem"
            )
            ->join(
                "pagordem",
                "e50_codord",
                "e20_pagordem"
            )
            ->join(
                "empempenho",
                "e60_numemp",
                "e50_numemp"
            )
            ->join(
                'empageformacgm',
                'e28_numcgm',
                'empempenho.e60_numcgm'
            )
            ->join(
                'empagetipo',
                'e83_codtipo',
                'empageformacgm.e28_empagetipo'
            )
            ->where(
                "e23_sequencial",
                $sequencialRetencao
            )
            ->first()->reduzido;
    }

    private function getDadosContaBancaria($reduzido)
    {
        $contaBancaria = DB::table("conplanocontabancaria")
            ->select(
                'c56_reduz as reduzido',
                'db83_sequencial',
                'db89_db_bancos as Banco',
                'db89_codagencia as Agencia',
                'db83_conta',
                'db83_dvconta'
            )
            ->join(
                'contabancaria',
                'contabancaria.db83_sequencial',
                'conplanocontabancaria.c56_contabancaria'
            )
            ->join(
                'bancoagencia',
                'bancoagencia.db89_sequencial',
                'contabancaria.db83_bancoagencia'
            )
            ->where(
                "c56_reduz",
                $reduzido
            )
            ->where("c56_anousu", $this->iAnoUsu)
            ->first();

        return $contaBancaria;
    }






//====================================================================================================
//====================================================================================================
//====================================================================================================
//====================================================================================================

    public function gerarDados(){
        


        
        
        $retencoes = $this->buscaEmpenhosRP3();
        $zoma = 0;
        foreach ($retencoes as $r) {
            $zoma += $r->c70_valor;
        }


        //$this->testa($retencoes); die("Confere");
        //$antigo = $this->getRetencoes();
        //$this->testa($antigo); die("Confere Antigo");

        if(count($retencoes) == 0){throw new \Exception(sprintf('Não foi encontrado nenhum registro para o arquivo da Remessa de %s', 'Pagamento de Empenho - Consig e Retenção'));}
        

        $EmpenhoConsignacaoRetencao = new stdClass();
        $EmpenhoConsignacaoRetencao->PagamentosDeEmpenhoConsignacoesRetencoes = [];

        
        $ix = 1;        
        foreach ($retencoes as $retencao){
            //if($retencao->e60_codemp != 47){continue;}
            //if($retencao->c84_slip != 237211){continue;}

            $xbanco = $retencao->db89_db_bancos;
            $xagencia = $retencao->db89_codagencia;
            $xconta = $retencao->db83_conta;
            $xconta = explode("-", $xconta);
            $xconta = $xconta[0];
            
            $codord = $retencao->c80_codord;
            
            $nfdados = $this->todosNotaFiscal($retencao->e60_numemp, $retencao->c70_codlan, $retencao->c70_data, $retencao->c70_valor);
            
            $notafiscal = $nfdados["e69_codnota"];
            $numeronota = $nfdados["e69_numero"];
            if(empty($numeronota)){
                $numeronota = "S/N";
            }
            $contaBancaria = $this->getContaBancaria($retencao->e23_sequencial);            
            $tiporetencao = $this->convertTipoRetencao($retencao->e32_sequencial);
            $nomecredor = trim($retencao->nomecgmretencao);
            $cnpjcredor = trim($retencao->cnpjcgmretencao);
            

            if(empty($nomecredor)){
                $dadoscredor = $this->buscaCredor($retencao->c84_slip);
                $nomecredor = trim($dadoscredor["z01_nome"]);
                $cnpjcredor = trim($dadoscredor["z01_cgccpf"]);
            }
            
            $ConsigRet = new stdClass();
            $ConsigRet->Identificador = $ix;
            $ConsigRet->CodigoUnidadeGestora = $this->sCodigoTribunal;
            $ConsigRet->Competencia = $this->competencia;
            $ConsigRet->NumeroEmpenho = $retencao->e60_codemp;
            $ConsigRet->AnoEmpenho = $retencao->e60_anousu;
            $ConsigRet->CodigoOrgao = $retencao->o58_orgao;
            $ConsigRet->CodigoUnidadeOrcamentaria = $retencao->o58_unidade;
            $ConsigRet->NumeroNotaPagamentoDeEmpenhoConsignacaoRetencao = $retencao->c84_slip; //$numeronota;
            $ConsigRet->DataPagamento = $retencao->c70_data;
            $ConsigRet->TipoConsignacaoRetencaoPaga = $tiporetencao;
            $ConsigRet->ValorPago = $retencao->c70_valor;
            $ConsigRet->NomeCredor = Helper::convertAndLimit($nomecredor, 255);
            $ConsigRet->CpfCnpjCredor = $cnpjcredor;
            $ConsigRet->NaturezaCredor = (strlen($cnpjcredor)) == 11 ? 2 : 1;
            $ConsigRet->CPF = $retencao->cpfusu;

            $ConsigRet->LiquidacoesDePagamento = [];
            $Liquidacao = new stdClass();
            $Liquidacao->Identificador = $retencao->c70_codlan;
            $Liquidacao->NumeroLiquidacaoEmpenho = $retencao->c80_codord;
            $Liquidacao->AnoLiquidacaoEmpenho = $retencao->e60_anousu;
            $Liquidacao->ValorConsignadoRetidoLiquidacao = $retencao->c70_valor;

            $Liquidacao->ContasPagadoras = [];
            $Conta = new stdClass();
            $Conta->Identificador = $contaBancaria->db83_sequencial;
            $Conta->ValorContaPagadora = $retencao->c70_valor;
            $Conta->Banco = ($xbanco) ? $xbanco : $contaBancaria->Banco;
            $Conta->Agencia = ($xagencia) ? $xagencia : $contaBancaria->Agencia;
            $Conta->ContaBancaria = ($xconta) ? $xconta : $contaBancaria->db83_conta;


            $ConsigRet->LiquidacoesDePagamento[] = (object) [
            'PagamentoDeEmpenhoConsignacaoRetencaoLiquidacaoPagamento' => $Liquidacao
            ];

            $Liquidacao->ContasPagadoras[] = (object) [
            'PagamentoDeEmpenhoConsignacaoRetencaoLiquidacaoPagamentoContaPagadora' => $Conta
            ];

            $EmpenhoConsignacaoRetencao->PagamentosDeEmpenhoConsignacoesRetencoes[] = (object) [
            'PagamentoDeEmpenhoConsignacaoRetencao' => $ConsigRet
            ];

            $ix++;
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
                'e50_codord',
                'e60_numemp',
                'e21_tiporet',
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
            ->where('e23_ativo', true)
            ->where('e60_instit', $this->instit)
            ->where('e60_anousu', $this->iAnoUsu)
            ->where('k12_estorn', false)
            ->whereBetween('k12_data', [$this->dtDataInicial, $this->dtDataFinal])
            //->whereNotNull('db89_db_bancos')
            ->orderBy('e60_numemp');

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
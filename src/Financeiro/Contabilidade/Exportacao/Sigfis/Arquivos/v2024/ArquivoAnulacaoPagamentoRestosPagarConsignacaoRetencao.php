<?php

namespace ECidade\Financeiro\Contabilidade\Exportacao\Sigfis\Arquivos\v2024;

use BusinessException;
use ECidade\Financeiro\Contabilidade\Exportacao\Sigfis\Common\Helper;
use Illuminate\Database\Capsule\Manager as DB;
use stdClass;

class ArquivoAnulacaoPagamentoRestosPagarConsignacaoRetencao extends ArquivoBase
{
    protected $sNomeArquivo = 'AnulacaoPagamentoRestosPagarConsignacaoRetencao';

    public function buscaRetencoesPorEmpenho($numemp){
        $inst = $this->instit;
        $di = $this->dtDataInicial;
        $df = $this->dtDataFinal;

        $sql = pg_query("SELECT e50_codord, c70_codlan as Identificador, empempenho.e60_codemp as NumeroEmpenho, empempenho.e60_anousu as AnoEmpenho, orcdotacao.o58_orgao as CodigoOrgao, orcdotacao.o58_unidade as CodigoUnidadeOrcamentaria, empnota.e69_numero as NumeroNota, c70_data as DataAnulacao, e32_sequencial as Tipo, c70_valor as Valor, cgmusu.z01_cgccpf as CPFResponsavel, empnota.e69_codnota as IdentificadorLiquidacao, empnota.e69_anousu as AnoLiquidacaoEmpenho, c72_complem as Justificativa from empempenho inner join orcdotacao on o58_anousu = e60_anousu and o58_coddot = e60_coddot inner join empnota on e69_numemp = e60_numemp inner join pagordemnota on e71_codnota = e69_codnota inner join pagordem on e50_codord = e71_codord inner join db_usuacgm on db_usuacgm.id_usuario = e50_id_usuario inner join cgm as cgmusu on cgmlogin = cgmusu.z01_numcgm inner join conlancamemp on c75_numemp = e60_numemp inner join conlancam on c75_codlan = c70_codlan inner join conlancamcompl on c72_codlan = c70_codlan inner join conlancamdoc on c71_codlan = c70_codlan inner join conlancamord as ord_ret on ord_ret.c80_codord = e71_codord and ord_ret.c80_codlan = c70_codlan inner join conlancamretencao on c127_conlancam = c70_codlan inner join retencaotiporec on e21_sequencial = c127_retencaotiporec inner join retencaotipocalc on e32_sequencial = e21_retencaotipocalc where c71_coddoc in (6009, 6011) and e60_instit = {$inst} and c70_data between '{$di}' and '{$df}' AND e60_codemp = '{$numemp}' order by e60_codemp asc");

        $resultado = pg_fetch_all($sql);
        return $resultado;
    }

    public function testa($var){
        echo "<pre>";
        print_r($var);
        echo "</pre>";
    }

    public function isValid($str) {
        return !preg_match('/[^A-Za-z0-9.#\\-$]/', $str);
    }

    public function buscaCPFordenador($cgm){
        $sql = pg_query("SELECT z01_cgccpf FROM cgm WHERE z01_numcgm = {$cgm}");
        $resultado = pg_fetch_all($sql);
        return $resultado[0]["z01_cgccpf"];
    }

    public function verifica161($seqempenho){
        $di = $this->dtDataInicial;
        $df = $this->dtDataFinal;

        $sql = pg_query("SELECT c70_codlan, c70_data, e60_coddot, c53_coddoc, c53_descr, case when c127_conlancam is not null then 'Sim' else 'Não' end as dl_Lançamento_Retenção, c70_valor, c82_reduz, c60_descr, c72_complem, e69_numero, e50_codord, e50_data, nomeinstabrev as dl_Ente from conlancamemp inner join conlancam on c70_codlan = c75_codlan inner join empempenho on c75_numemp = e60_numemp inner join conlancamordem on conlancamordem.c03_codlan = conlancam.c70_codlan left outer join conlancampag on c82_codlan = c70_codlan inner join conlancamdoc on c71_codlan = c70_codlan inner join conhistdoc on c53_coddoc = c71_coddoc left join conlancamcompl on c72_codlan =c70_codlan left join conlancamnota on c66_codlan =c70_codlan left join conlancamord on c80_codlan =c70_codlan left join empnota on c66_codnota = e69_codnota left join conplanoreduz on c61_reduz = conlancampag.c82_reduz and c61_anousu=c70_anousu left join conplano on c60_codcon = conplanoreduz.c61_codcon and c60_anousu=c61_anousu left join pagordem on e50_codord = c80_codord inner join conlancaminstit on c02_codlan = c70_codlan inner join db_config on c02_instit = db_config.codigo left join conlancamretencao on c127_conlancam = c70_codlan where c75_numemp = {$seqempenho} AND c70_data BETWEEN '{$di}' AND '{$df}' AND c53_coddoc = 161 order by c75_data, c03_ordem, c75_codlan");
        $resultado = pg_fetch_all($sql);
        return ($resultado) ? true : false;
    }

    public function gerarDados()
    {   
        $ugs = $this->pegaCgmOrdenador();
        $ugs = $this->buscaCPFordenador($ugs);

        $retencoes = $this->getRetencoes();

        if ($retencoes->isEmpty()) {
            throw new BusinessException('Não há retenções para a competência informada.');
        }

        $RPAnuConsignacaoRetencao = new stdClass();
        $RPAnuConsignacaoRetencao->AnulacoesPagamentosRestosPagarConsignacoesRetencoes = [];

        $guardaempenho = array();
        foreach ($retencoes as $retencao) {
            //if($retencao->NumeroEmpenho != 4396){continue;}
            $verificaisso = $this->verifica161($retencao->e60_numemp);
            if(!$verificaisso){continue;}
            if(in_array($retencao->NumeroEmpenho, $guardaempenho)){
                continue;
            }
            array_push($guardaempenho, $retencao->NumeroEmpenho);

            $dados = $this->buscaRetencoesPorEmpenho($retencao->NumeroEmpenho);
            
            $textofinal = "";
            $valorfinal = 0;
            foreach ($dados as $linha) {                
                $valorfinal += $linha["valor"];
                $textobase = explode(" ", $linha["justificativa"]);
                $textofinal0 = "";                
                foreach ($textobase as $texto){
                    if(strlen($texto) > 0){
                        $textofinal0 .= $texto . " ";
                    }
                }
                $textofinal .= $textofinal0;
                
                //$textofinal .= $linha['justificativa'] . " ";
                
            }
            
                /*$novotexto = explode(" ", $retencao->Justificativa);
                $textofinal = "";
                foreach ($novotexto as $texto) {
                    if(strlen($texto) > 0){
                        $textofinal .= $texto . " ";
                    }
                }*/
        
            
            $AnuConsigRet = new stdClass();
            //var_dump($retencao->Justificativa); echo "<br>";
            $AnuConsigRet->Identificador = $retencao->Identificador;
            $AnuConsigRet->CodigoUnidadeGestora = $this->sCodigoTribunal;
            $AnuConsigRet->Competencia = $this->competencia;
            $AnuConsigRet->NumeroRestosPagarEmpenho = $retencao->NumeroEmpenho;
            $AnuConsigRet->AnoRestosPagarEmpenho = $retencao->AnoEmpenho;
            $AnuConsigRet->NumeroNotaPagamentoDeEmpenhoConsignacaoRetencao = $retencao->NumeroNota; //$retencao->e50_codord;
            $AnuConsigRet->AnoNotaPagamentoDeEmpenhoConsignacaoRetencao = substr($this->competencia, 0, 4); //$retencao->AnoLiquidacaoEmpenho;            
            $AnuConsigRet->NumeroNotaAnulacao = substr($retencao->IdentificadorLiquidacao, 0, 6);
            $AnuConsigRet->DataAnulacao = $retencao->DataAnulacao;
            $AnuConsigRet->Justificativa = utf8_encode(trim(substr($textofinal, 0, 4000))); //utf8_encode(trim(substr($retencao->Justificativa, 0, 4000)));
            $AnuConsigRet->CpfResponsavel = $ugs;//$retencao->CPFResponsavel;
            $AnuConsigRet->TipoConsignacaoRetencaoPaga = $this->convertTipoRetencao($retencao->Tipo);
            $AnuConsigRet->TipoAnulacaoMovimento = 2;
            $AnuConsigRet->Valor = $valorfinal;//$retencao->Valor;
            //$AnuConsigRet->CodigoOrgao = $retencao->CodigoOrgao;
            //$AnuConsigRet->CodigoUnidadeOrcamentaria = $retencao->CodigoUnidadeOrcamentaria;


            $RPAnuConsignacaoRetencao->AnulacoesPagamentosRestosPagarConsignacoesRetencoes[] = (object) [
                'AnulacaoPagamentoRestosPagarConsignacaoRetencao' => $AnuConsigRet
            ];
        }
        
        $this->aDados = $RPAnuConsignacaoRetencao;
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
                'e50_codord',
                'empempenho.e60_numemp'
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
            ->whereIn('c71_coddoc', [6009, 6011])
            ->where('e60_instit', $this->instit)
            ->whereBetween('c70_data', [$this->dtDataInicial, $this->dtDataFinal])
            ->orderBy('e60_codemp');

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

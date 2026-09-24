<?php

namespace ECidade\Financeiro\Contabilidade\Exportacao\Sigfis\Arquivos\v2024;

use BusinessException;
use ECidade\Financeiro\Contabilidade\Exportacao\Sigfis\Common\Helper;
use Illuminate\Database\Capsule\Manager as DB;
use stdClass;

class ArquivoPagamentoRestosPagarAnulacao extends ArquivoBase
{
    protected $sNomeArquivo = 'PagamentoRestosPagarAnulacao';

    public function testa($var){
        echo "<pre>";
        print_r($var);
        echo "</pre>";
    }

    public function buscaCPFordenador($cgm){
        $sql = pg_query("SELECT z01_cgccpf FROM cgm WHERE z01_numcgm = {$cgm}");
        $resultado = pg_fetch_all($sql);
        return $resultado[0]["z01_cgccpf"];
    }

    public function buscaCodord2($codlan){
        $di = $this->dtDataInicial;
        $df = $this->dtDataFinal;

        $sql = pg_query("SELECT c70_codlan, c70_data, c70_valor, c71_coddoc, c53_descr, c80_codord, c75_numemp, c76_numcgm, z01_nome, e69_numero, e69_codnota, c72_complem, c73_coddot, c74_codrec, c70_anousu, c53_tipo, c67_codele from conlancam inner join conlancamdoc on c71_codlan = c70_codlan inner join conhistdoc on c71_coddoc = c53_coddoc left join conlancamord on c70_codlan = c80_codlan left join conlancamemp on c75_codlan = c70_codlan left join conlancamcgm on c70_codlan = c76_codlan left join cgm on z01_numcgm = c76_numcgm left join conlancamnota on c70_codlan = c66_codlan left join empnota on c66_codnota = e69_codnota left join conlancamcompl on c70_codlan = c72_codlan left join conlancamdot on c73_codlan = c70_codlan and c73_anousu = c70_anousu left join conlancamele on c67_codlan = c70_codlan left join conlancamrec on c74_codlan = c70_codlan and c74_anousu = c70_anousu where c70_codlan = {$codlan}");
        $resultado = pg_fetch_all($sql);
    
        return $resultado[0]["c80_codord"];
    }

    public function buscaLancamentoDoPagamento($op, $data){
        $sql = pg_query("SELECT c70_codlan, c70_data, c70_valor, c71_coddoc, c53_descr, c80_codord, c75_numemp, c76_numcgm, z01_nome, e69_numero, e69_codnota, c72_complem, c73_coddot, c74_codrec, c70_anousu, c53_tipo, c67_codele from conlancam inner join conlancamdoc on c71_codlan = c70_codlan inner join conhistdoc on c71_coddoc = c53_coddoc left join conlancamord on c70_codlan = c80_codlan left join conlancamemp on c75_codlan = c70_codlan left join conlancamcgm on c70_codlan = c76_codlan left join cgm on z01_numcgm = c76_numcgm left join conlancamnota on c70_codlan = c66_codlan left join empnota on c66_codnota = e69_codnota left join conlancamcompl on c70_codlan = c72_codlan left join conlancamdot on c73_codlan = c70_codlan and c73_anousu = c70_anousu left join conlancamele on c67_codlan = c70_codlan left join conlancamrec on c74_codlan = c70_codlan and c74_anousu = c70_anousu where c80_codord = {$op} AND c71_coddoc = 37 AND c70_data = '{$data}'");
        $resultado = pg_fetch_all($sql);
        return $resultado[0]["c70_codlan"];
    }

    public function gerarDados()
    {
        $estornos = $this->getEstornos();
        $ugs = $this->pegaCgmOrdenador();
        $ugs = $this->buscaCPFordenador($ugs);
        

        if ($estornos->isEmpty()) {
            throw new BusinessException('Não há dados para a competência informada.');
        }

        $data = new stdClass();
        $data->PagamentosRestosPagarAnulacoes = [];
        //$this->testa($estornos); die("Confere");

        $guardalancamento = array();

        $ix = 1;
        foreach ($estornos as $estorno) {
            //if($estorno->NumeroEmpenho != 4347){continue;}
            if(in_array($estorno->Identificador, $guardalancamento)){
                continue;
            }
            array_push($guardalancamento, $estorno->Identificador);
            $numeropagamento = $this->sCodigoTribunal . $estorno->NumeroEmpenho . $estorno->AnoEmpenho;
            
            $codlanpag = $this->buscaLancamentoDoPagamento($estorno->e71_codord, $estorno->DataAnulacao);
            
            $rpAnulacao = new stdClass();
            $rpAnulacao->Identificador = $ix;
            $rpAnulacao->CodigoUnidadeGestora = $this->sCodigoTribunal;
            $rpAnulacao->NumeroEmpenho = $estorno->NumeroEmpenho;
            $rpAnulacao->AnoEmpenho = $estorno->AnoEmpenho;
            $rpAnulacao->Competencia = $this->competencia;
            $rpAnulacao->NumeroAnulacao = $estorno->NumeroNotaPagamento;//$estorno->Identificador;
            $rpAnulacao->DataAnulacao = $estorno->DataAnulacao;
            $rpAnulacao->NumeroNotaPagamento = $numeropagamento; //$codlanpag;//$estorno->Identificador;//$estorno->e71_codord;//$estorno->NumNota; //$estorno->NumeroNotaPagamento;
            $rpAnulacao->AnoPagamentoRestosPagar = $estorno->c70_anousu;//$estorno->AnoPagamentoRestosPagar;
            $rpAnulacao->CPFResponsavel = $ugs;//$estorno->CPFResponsavel;
            $rpAnulacao->Justificativa = utf8_decode(Helper::convertAndLimit($estorno->Justificativa, 255));
            $rpAnulacao->Valor = $estorno->Valor;
            $rpAnulacao->CodigoOrgao = $estorno->CodigoOrgao;
            $rpAnulacao->CodigoUnidadeOrcamentaria = $estorno->CodigoUnidadeOrcamentaria;

            $data->PagamentosRestosPagarAnulacoes[] = (object) ['PagamentoRestosPagarAnulacao' => $rpAnulacao];
            $ix++;
        }

        $this->aDados = $data;
    }

    private function getEstornos()
    {
        $query = DB::table('empresto')
            ->select(
                'conlancam.c70_codlan as Identificador',
                'empempenho.e60_codemp as NumeroEmpenho',
                'empempenho.e60_anousu as AnoEmpenho',
                'conlancam.c70_data as DataAnulacao',
                'empnota.e69_codnota as NumeroNotaPagamento',
                'empnota.e69_numero as NumNota',
                'empnota.e69_anousu as AnoPagamentoRestosPagar',
                'cgmusu.z01_cgccpf as CPFResponsavel',
                'conlancamcompl.c72_complem as Justificativa',
                'conlancam.c70_valor as Valor',
                'orcdotacao.o58_orgao as CodigoOrgao',
                'orcdotacao.o58_unidade as CodigoUnidadeOrcamentaria',
                "c70_anousu",
                "e71_codord"
            )
            ->join('empempenho', 'empempenho.e60_numemp', 'empresto.e91_numemp')
            ->join('orcdotacao', function ($join) {
                $join->on('orcdotacao.o58_coddot', 'empempenho.e60_coddot')
                    ->on('orcdotacao.o58_anousu', 'empempenho.e60_anousu');
            })
            ->join('empnota', 'empnota.e69_numemp', 'empempenho.e60_numemp')
            ->join('pagordemnota', 'pagordemnota.e71_codnota', 'empnota.e69_codnota')
            ->join('pagordem', 'pagordem.e50_codord', 'pagordemnota.e71_codord')
            ->join('db_usuacgm', 'db_usuacgm.id_usuario', 'pagordem.e50_id_usuario')
            ->join('cgm as cgmusu', 'cgmusu.z01_numcgm', 'db_usuacgm.cgmlogin')
            ->join('conlancamemp', 'conlancamemp.c75_numemp', 'empempenho.e60_numemp')
            ->join('conlancam', 'conlancam.c70_codlan', 'conlancamemp.c75_codlan')
            ->join('conlancamord', function ($join) {
                $join->on('conlancamord.c80_codlan', 'conlancam.c70_codlan')
                    ->on('conlancamord.c80_codord', 'pagordemnota.e71_codord');
            })
            ->join('conlancamcompl', 'c72_codlan', 'conlancam.c70_codlan')
            ->join('conlancamdoc', 'conlancamdoc.c71_codlan', 'conlancam.c70_codlan')
            ->whereIn('conlancamdoc.c71_coddoc', [36, 38])
            ->whereBetween('conlancam.c70_data', [$this->dtDataInicial, $this->dtDataFinal])
            ->where('empempenho.e60_instit', $this->instit);

            //$sql_with_bindings = str_replace_array('?', $query->getBindings(), $query->toSql());
            //$sql_with_bindings = str_replace("\"", "", $sql_with_bindings);
            //var_dump($sql_with_bindings); die("confere");

        return $query->get();
    }
}

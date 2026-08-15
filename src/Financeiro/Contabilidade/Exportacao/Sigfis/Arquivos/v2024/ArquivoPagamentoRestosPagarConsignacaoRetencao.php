<?php

namespace ECidade\Financeiro\Contabilidade\Exportacao\Sigfis\Arquivos\v2024;

use BusinessException;
use ECidade\Financeiro\Contabilidade\Exportacao\Sigfis\Common\Helper;
use Illuminate\Database\Capsule\Manager as DB;
use stdClass;

class ArquivoPagamentoRestosPagarConsignacaoRetencao extends ArquivoBase
{
    protected $sNomeArquivo = 'PagamentoRestosPagarConsignacaoRetencao';

    public function buscaRetencoesPorEmpenho($numemp){
        $inst = $this->instit;
        $di = $this->dtDataInicial;
        $df = $this->dtDataFinal;

        $sql = pg_query("SELECT e60_numemp, e50_codord, e23_sequencial as IdentificadorRetencao, empempenho.e60_codemp as NumeroEmpenho, empempenho.e60_anousu as AnoEmpenho, orcdotacao.o58_orgao as CodigoOrgao, orcdotacao.o58_unidade as CodigoUnidadeOrcamentaria, empnota.e69_numero as NumeroNota, empnota.e69_codnota as xcodnota, k12_data as DataPagamento, e32_sequencial as Tipo, e23_valorretencao as ValorPago, cgm.z01_nome as NomeCredor, cgm.z01_cgccpf as CNPJCredor, cgmusu.z01_cgccpf as CPFResponsavel, empnota.e69_codnota as IdentificadorLiquidacao, empnota.e69_anousu as AnoLiquidacaoEmpenho, db89_db_bancos as Banco, db89_codagencia as Agencia, db83_conta as ContaBancaria from retencaoreceitas inner join retencaotiporec on e21_sequencial = e23_retencaotiporec inner join retencaotipocalc on e32_sequencial = e21_retencaotipocalc inner join retencaotiporeccgm on e48_retencaotiporec = e21_sequencial inner join retencaopagordem on e23_retencaopagordem = e20_sequencial inner join retencaoempagemov on e27_retencaoreceitas = e23_sequencial inner join empagemov on e81_codmov = e27_empagemov inner join pagordem on e50_codord = e20_pagordem inner join pagordemnota on e71_codord = e50_codord inner join empresto on e91_numemp = e50_numemp inner join empempenho on e60_numemp = e91_numemp inner join orcdotacao on o58_anousu = e60_anousu and o58_coddot = e60_coddot inner join empnota on e69_codnota = e71_codnota inner join db_usuacgm on db_usuacgm.id_usuario = e50_id_usuario inner join cgm as cgmusu on cgmlogin = cgmusu.z01_numcgm inner join cgm on cgm.z01_numcgm = empempenho.e60_numcgm left join empagemovslips on k107_retencao = e23_sequencial left join slipempagemovslips on k108_empagemovslips = k107_sequencial left join slip on k17_codigo = k108_slip left join empageformacgm on e28_numcgm = empempenho.e60_numcgm left join empagetipo on e83_codtipo = empageformacgm.e28_empagetipo inner join saltes on saltes.k13_conta = coalesce(slip.k17_credito, e83_conta) inner join conplanocontabancaria on c56_reduz = saltes.k13_reduz and c56_anousu = e60_anousu inner join contabancaria on contabancaria.db83_sequencial = conplanocontabancaria.c56_contabancaria inner join bancoagencia on bancoagencia.db89_sequencial = contabancaria.db83_bancoagencia inner join retencaocorgrupocorrente on e47_retencaoreceita = e23_sequencial inner join corgrupocorrente on k105_sequencial = e47_corgrupocorrente inner join corrente on k105_sequencial = e47_corgrupocorrente and k105_id = k12_id and k105_autent = k12_autent and k105_data = k12_data where e23_ativo = true and e60_instit = {$inst} and k12_estorn = false and k12_data between '{$di}' and '{$df}' AND e60_codemp = '{$numemp}'");

        $resultado = pg_fetch_all($sql);
        return $resultado;
    }


    public function listaBancos(){
        $ano = $this->iAnoUsu;
        $inst = $this->instit;
        $di = $this->dtDataInicial;
        $df = $this->dtDataFinal;

        $sql = pg_query("SELECT x.siconfi,x.codigo_conplano,sum(x.saldo_anterior) as saldo_anterior,sum(x.saldo_debito) as saldo_debito,sum(x.saldo_credito) as saldo_credito,sum(x.saldo_final) as saldo_final,db90_codban,db90_descr,db89_codagencia,db89_digito,db83_conta,db83_tipoconta,db83_dvconta,db83_identificador,db83_descricao,k13_dtimplantacao,k13_limite,k13_reduz,pcasp.conta from balancete_verificacao_por_recurso({$ano}, '{$di}', '{$df}', false, (
                    SELECT array_agg(c61_reduz)
                        FROM
                        (SELECT c61_reduz
                           FROM contabilidade.conplano
                           JOIN contabilidade.conplanoreduz ON (c61_codcon, c61_anousu) = (c60_codcon, c60_anousu)
                          WHERE c61_instit IN ({$inst})
                            and c61_anousu = {$ano}
                            and c60_estrut like '111%'
                        ) AS x
                )::int[]
            ) x
            left join pcaspconplano on pcasp_id = codigo_conplano
            left join pcasp on pcasp.id = pcasp_id
            join contabancaria on db83_sequencial = conta_bancaria_id
            join bancoagencia on db89_sequencial = db83_bancoagencia
            join db_bancos on db90_codban =  db89_db_bancos
            join saltes on k13_conta = reduzido
            GROUP BY
                x.siconfi,
                x.codigo_conplano,
                db90_codban,
                db90_descr,
                db89_codagencia,
                db89_digito,
                db83_conta,
                db83_tipoconta,
                db83_dvconta,
                db83_identificador,
                db83_descricao,
                k13_dtimplantacao,
                k13_limite,
                k13_reduz,
                pcasp.conta
           ORDER BY x.siconfi");

        $resultado = pg_fetch_all($sql);
        return $resultado;
    }

    public function buscaDadosBancarios2($codnota){
        $di = $this->dtDataInicial;
        $df = $this->dtDataFinal;

        $sql = pg_query("SELECT e60_numemp, e69_codnota, e69_numero, c70_codlan, c70_data, c70_valor, db90_codban, db89_codagencia, db89_digito, db83_conta, db83_dvconta from empempenho inner join empnota on e69_numemp = e60_numemp inner join pagordemnota on e71_codnota = e69_codnota inner join conlancamemp on c75_numemp = e60_numemp inner join conlancam on c75_codlan = c70_codlan inner join conlancamdoc on c71_codlan = c70_codlan inner join conlancamcompl on c72_codlan = c70_codlan inner join conlancamord on c80_codlan = c70_codlan and c80_codord = e71_codord inner join lancamentoscontabeislog as log on codlan = c70_codlan and tipo_movimento = 1 inner join db_usuacgm on db_usuacgm.id_usuario = log.id_usuario inner join cgm as cgmusu on cgmusu.z01_numcgm = cgmlogin inner join conlancampag on c82_codlan = c70_codlan inner join contabilidade.conplanoreduz on c61_reduz = c82_reduz and c61_anousu = c82_anousu inner join contabilidade.conplanocontabancaria on c56_reduz = c61_reduz and c56_anousu = c61_anousu inner join configuracoes.contabancaria on c56_contabancaria = db83_sequencial inner join configuracoes.bancoagencia on db89_sequencial = db83_bancoagencia inner join configuracoes.db_bancos on db90_codban = db89_db_bancos where e69_codnota = {$codnota} AND c70_data between '{$di}' AND '{$df}' order by e60_numemp asc");
        $resultado = pg_fetch_all($sql);
        return $resultado[0];
    }

    public function buscaDadosBancarios4($seqempenho, $codord){
        $sql = pg_query("SELECT c70_codlan, c70_data, c53_descr, case when c127_conlancam is not null then 'Sim' else 'Não' end as dl_Lançamento_Retenção, c70_valor, c82_reduz, c60_descr, c72_complem, e69_numero as dl_Nota_Fiscal, e50_codord, e50_data, nomeinstabrev as dl_Ente from conlancamemp inner join conlancam on c70_codlan = c75_codlan inner join empempenho on c75_numemp = e60_numemp inner join conlancamordem on conlancamordem.c03_codlan = conlancam.c70_codlan left outer join conlancampag on c82_codlan = c70_codlan inner join conlancamdoc on c71_codlan = c70_codlan inner join conhistdoc on c53_coddoc = c71_coddoc left join conlancamcompl on c72_codlan =c70_codlan left join conlancamnota on c66_codlan =c70_codlan left join conlancamord on c80_codlan =c70_codlan left join empnota on c66_codnota = e69_codnota left join conplanoreduz on c61_reduz = conlancampag.c82_reduz and c61_anousu=c70_anousu left join conplano on c60_codcon = conplanoreduz.c61_codcon and c60_anousu=c61_anousu left join pagordem on e50_codord = c80_codord inner join conlancaminstit on c02_codlan = c70_codlan inner join db_config on c02_instit = db_config.codigo left join conlancamretencao on c127_conlancam = c70_codlan where c75_numemp = {$seqempenho} AND e50_codord = {$codord} AND c82_reduz is not null order by c75_data, c03_ordem, c75_codlan");
            $resultado = pg_fetch_all($sql);
            $codlan = $resultado[0]["c70_codlan"];

            $sql2 = pg_query("SELECT db90_codban, db89_codagencia, db89_digito, db83_conta, db83_dvconta from empempenho inner join empnota on e69_numemp = e60_numemp inner join pagordemnota on e71_codnota = e69_codnota inner join conlancamemp on c75_numemp = e60_numemp inner join conlancam on c75_codlan = c70_codlan inner join conlancamdoc on c71_codlan = c70_codlan inner join conlancamcompl on c72_codlan = c70_codlan inner join conlancamord on c80_codlan = c70_codlan and c80_codord = e71_codord inner join lancamentoscontabeislog as log on codlan = c70_codlan and tipo_movimento = 1 inner join db_usuacgm on db_usuacgm.id_usuario = log.id_usuario inner join cgm as cgmusu on cgmusu.z01_numcgm = cgmlogin inner join conlancampag on c82_codlan = c70_codlan inner join contabilidade.conplanoreduz on c61_reduz = c82_reduz and c61_anousu = c82_anousu inner join contabilidade.conplanocontabancaria on c56_reduz = c61_reduz and c56_anousu = c61_anousu inner join configuracoes.contabancaria on c56_contabancaria = db83_sequencial inner join configuracoes.bancoagencia on db89_sequencial = db83_bancoagencia inner join configuracoes.db_bancos on db90_codban = db89_db_bancos where e60_numemp = {$seqempenho} AND c70_codlan = {$codlan}");
                    $resultado2 = pg_fetch_all($sql2);
                    return $resultado2[0];
    }

    public function testa($var){
        echo "<pre>";
        print_r($var);
        echo "</pre>";
    }

    public function gerarDados()
    {   
        $anoexercicio = db_getsession("DB_anousu");
        $retencoes = $this->getRetencoes();

        if ($retencoes->isEmpty()) {
            throw new BusinessException('Não há retenções para a competência informada.');
        }

        $EmpenhoRPConsignacaoRetencao = new stdClass();
        $EmpenhoRPConsignacaoRetencao->PagamentosRestosPagarConsignacoesRetencoes = [];
        $guardaempenho = array();
        
        $guardacontas = array();
        $listabancos = $this->listaBancos();
            foreach($listabancos as $bankos){
                $bonca = $bankos["db90_codban"] . $bankos["db89_codagencia"] . $bankos["db83_conta"];
                array_push($guardacontas, $bonca);
            }

        
        $bancopadrao["codban"] = $listabancos[0]["db90_codban"];
        $bancopadrao["agencia"] = $listabancos[0]["db89_codagencia"];
        $bancopadrao["conta"] = $listabancos[0]["db83_conta"];
        
        foreach ($retencoes as $retencao) {
            //if($retencao->NumeroEmpenho != "3563"){continue;}
            //if($retencao->NumeroEmpenho != "1141"){continue;}
            
            if($retencao->AnoEmpenho == $anoexercicio){continue;}
            //$this->testa($retencao); die("Confere");
            
            
            if(in_array($retencao->NumeroEmpenho, $guardaempenho)){
                continue;
            }
            array_push($guardaempenho, $retencao->NumeroEmpenho);
            
            
            
            $dados = $this->buscaRetencoesPorEmpenho($retencao->NumeroEmpenho);
            //var_dump($dados);
            //echo count($dados); == 1
            
            $konta = 0;
            $kdados = count($dados);
            $pula = false;
            foreach ($dados as $linha) {
                $dadosbancarios2 = $this->buscaDadosBancarios2($linha["xcodnota"]);
                //echo "Dados Bancários: " . count($dadosbancarios2);
                //echo "<br>";
                //var_dump($konta);
                
                if(empty($dadosbancarios2)){
                    $konta++;
                }
                //var_dump($konta);


                if($konta == $kdados){$pula = true;}
            }
            
            if($pula){continue;}


            
            $somavalor = 0;
            foreach ($dados as $linha) {
                $dadosbancarios2 = $this->buscaDadosBancarios2($linha["xcodnota"]);
                if(empty($dadosbancarios2)){continue;}
                $somavalor += $linha['valorpago'];
            }
            
            
            $ConsigRet = new stdClass();
            $ConsigRet->Identificador = $retencao->IdentificadorRetencao;
            $ConsigRet->CodigoUnidadeGestora = $this->sCodigoTribunal;
            $ConsigRet->Competencia = $this->competencia;
            $ConsigRet->NumeroRestosPagarEmpenho = $retencao->NumeroEmpenho;
            $ConsigRet->AnoRestosPagarEmpenho = $retencao->AnoEmpenho;
            $ConsigRet->AnoNotaPagamentoRestosPagarConsignacaoRetencao = substr($this->competencia, 0, 4); //$retencao->AnoLiquidacaoEmpenho; //AQUI
            $ConsigRet->NumeroNotaPagamentoRestosPagarConsignacaoRetencao = $retencao->NumeroNota;
            $ConsigRet->TipoConsignacaoRetencaoPaga = $this->convertTipoRetencao($retencao->Tipo);
            $ConsigRet->DataPagamento = $retencao->DataPagamento;
            $ConsigRet->ValorPago = $somavalor; //$retencao->ValorPago;
            $ConsigRet->NomeCredor = Helper::convertAndLimit($retencao->NomeCredor, 255);
            $ConsigRet->NaturezaCredor = (strlen($retencao->CNPJCredor) == 11) ? 2 : 1;
            $ConsigRet->CpfCnpjCredor = $retencao->CNPJCredor;
            $ConsigRet->CPF = $retencao->CPFResponsavel;
            $ConsigRet->CodigoOrgao = $retencao->CodigoOrgao;
            $ConsigRet->CodigoUnidadeOrcamentaria = $retencao->CodigoUnidadeOrcamentaria;            
            
            //$ConsigRet->LiquidacoesDePagamento = [];
            //$Liquidacao->ContasPagadoras = [];
            //$this->testa($retencao);
            //$this->testa($dados);
            //die("Confere");
            foreach ($dados as $linha) {
                //$xdadosbancarios = $this->buscaDadosBancarios4($retencao->e60_numemp, $linha["e50_codord"]);
                //var_dump($retencao->e60_numemp);
                //var_dump($linha["e50_codord"]);
                //$this->testa($xdadosbancarios);
                //die("Olha agora2");

                $dadosbancarios2 = $this->buscaDadosBancarios2($linha["xcodnota"]);
                if(empty($dadosbancarios2)){continue;}
                $codigobanco = $dadosbancarios2["db90_codban"];
                $codigoagencia = $dadosbancarios2["db89_codagencia"];
                $codigoconta = $dadosbancarios2["db83_conta"];
                
                $Liquidacao = new stdClass();    
                $Liquidacao->Identificador = $linha['identificadorliquidacao'];
                $Liquidacao->NumeroLiquidacaoRestosPagar = $linha['e50_codord'];
                $Liquidacao->AnoLiquidacaoRestosPagar = $linha['anoempenho'];
                $Liquidacao->ValorConsignadoRetidoLiquidacao = $linha['valorpago'];

                $continha = $linha["banco"] . $linha["agencia"] . $linha["contabancaria"];
                if(!in_array($continha, $guardacontas)){
                    $linha['banco'] = $bancopadrao["codban"];
                    $linha['agencia'] = $listabancos[0]["db89_codagencia"];
                    $linha['contabancaria'] = $listabancos[0]["db83_conta"];
                }

                $Conta = new stdClass();
                $Conta->Identificador = $linha['identificadorretencao'];
                $Conta->ValorContaPagadora = $linha['valorpago'];
                $Conta->Banco = $codigobanco;//$linha['banco'];
                $Conta->Agencia = $codigoagencia; //$linha['agencia'];
                $Conta->ContaBancaria = $codigoconta; //$linha['contabancaria'];


                $ConsigRet->LiquidacoesDePagamento[] = (object) [
                    'PagamentoRestosPagarConsignacaoRetencaoLiquidacaoPagamento' => $Liquidacao
                ];

                $Liquidacao->ContasPagadoras[] = (object) [
                    'PagamentoRestosPagarConsignacaoRetencaoLiquidacaoPagamentoContaPagadora' => $Conta
                ];

                
                
            }//foreach
            $EmpenhoRPConsignacaoRetencao->PagamentosRestosPagarConsignacoesRetencoes[] = (object) [
                'PagamentoRestosPagarConsignacaoRetencao' => $ConsigRet
            ];
            //$EmpenhoRPConsignacaoRetencao->PagamentosRestosPagarConsignacoesRetencoes = array_slice($EmpenhoRPConsignacaoRetencao->PagamentosRestosPagarConsignacoesRetencoes, 0, 1)[0];

            
            
            

            
            
        }
        
        
        $this->aDados = $EmpenhoRPConsignacaoRetencao;
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
                'empnota.e69_codnota as xcodnota',
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
                'empempenho.e60_numemp'
            ])
            ->join('retencaotiporec', 'e21_sequencial', 'e23_retencaotiporec')
            ->join('retencaotipocalc', 'e32_sequencial', 'e21_retencaotipocalc')
            ->join('retencaotiporeccgm', 'e48_retencaotiporec', 'e21_sequencial')
            ->join('retencaopagordem', 'e23_retencaopagordem', 'e20_sequencial')
            ->join('retencaoempagemov', 'e27_retencaoreceitas', 'e23_sequencial')
            ->join('empagemov', 'e81_codmov', 'e27_empagemov')
            ->join('pagordem', 'e50_codord', 'e20_pagordem')
            ->join('pagordemnota', 'e71_codord', 'e50_codord')
            ->join('empresto', 'e91_numemp', 'e50_numemp')
            ->join('empempenho', 'e60_numemp', 'e91_numemp')
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
            ->where('k12_estorn', false)
            ->whereBetween('k12_data', [$this->dtDataInicial, $this->dtDataFinal])
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


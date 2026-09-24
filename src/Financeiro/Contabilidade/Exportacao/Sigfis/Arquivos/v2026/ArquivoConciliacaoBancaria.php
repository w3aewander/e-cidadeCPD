<?php

namespace ECidade\Financeiro\Contabilidade\Exportacao\Sigfis\Arquivos\v2024;

use Illuminate\Database\Capsule\Manager as DB;
use ECidade\Financeiro\Contabilidade\Exportacao\Sigfis\Common\Helper;
use stdClass;

class ArquivoConciliacaoBancaria extends ArquivoBase
{
    protected $sNomeArquivo  = 'ConciliacaoBancaria';
    protected $objetoConciliacao;
    protected $conciliacoesProcessadas;

    public function voltaDataConciliacao($competencia, $ano){
        $mc = substr($competencia, -2);
        
        if($mc == "02"){
            $dia = ($ano % 4 == 0) ? 29 : 28;
            $data = $ano . "-" . $mc . "-" . $dia;
            return $data;
        }        

        if($mc == "01" || $mc == "03" || $mc == "05" || $mc == "07" || $mc == "08" || $mc == "10" || $mc == "12"){
            $data = $ano ."-". $mc ."-". "31";
        }else{
            $data = $ano ."-". $mc ."-". "30";
        }        

        if($mc == "00"){
            $data = $ano - 1 ."-12-31";
        }

        return $data;
        
    }

    

    public function gerarDados()
    {
        
        $poste = $_POST["json"];
        $expl1ode = explode(":", $poste);
        $expl2ode = explode("\\\"", $expl1ode[2]);
        $arquivosdo = $expl2ode[1];
        if($arquivosdo == "0"){
            //$this->competencia = "202400";            
            $this->competencia = $this->iAnoUsu . "00"; //"202400";
        }
        
        
        $camposContasBancarias = [
            'x.siconfi',
            'x.codigo_conplano',
            'sum(x.saldo_anterior) as saldo_anterior',
            'sum(x.saldo_debito) as saldo_debito',
            'sum(x.saldo_credito) as saldo_credito',
            'sum(x.saldo_final) as saldo_final',
            'db90_codban',
            'db90_descr',
            'db89_codagencia',
            'db89_digito',
            'db83_conta',
            'db83_tipoconta',
            'db83_dvconta',
            'db83_identificador',
            'db83_descricao',
            'k13_dtimplantacao',
            'k13_limite',
            'k13_reduz',
            'pcasp.conta'
        ];

        $camposContasBancarias = implode(',', $camposContasBancarias);

        $contasBancarias =  DB::select(DB::raw("
            select $camposContasBancarias from balancete_verificacao_por_recurso(
                $this->iAnoUsu,
                '$this->dtDataInicial',
                '$this->dtDataFinal',
                false,
                (
                    SELECT array_agg(c61_reduz)
                        FROM
                        (SELECT c61_reduz
                           FROM contabilidade.conplano
                           JOIN contabilidade.conplanoreduz ON (c61_codcon, c61_anousu) = (c60_codcon, c60_anousu)
                          WHERE c61_instit IN ($this->instit)
                            and c61_anousu = $this->iAnoUsu
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
           ORDER BY k13_reduz
        "));


        if (empty($contasBancarias)) {
            throw new \Exception('Não foi encontrardo nenhuma conta bancária para o arquivo de Remessa ');
        }



        //echo "<pre>";
        //print_r($contasBancarias);
        //echo "</pre>";
        //die("Confere");

        $obj = new \stdClass();
        $obj->ConciliacoesBancarias = [];

        $fontes50 = array(
          200 => 1500,
          6000 => 1500,
          6001 => 1600,
          6002 => 1600,
          6003 => 1600,
          6004 => 1600,
          6005 => 1600,
          6012 => 1601,
          6021 => 1602,
          6031 => 1603,
          6032 => 1659,
          6041 => 1600,
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
          6312 => 1631,
          6351 => 1635,
          6591 => 1659,
          6592 => 1659,
          6593 => 1659,
          6594 => 1659,
          6595 => 1659,
          6596 => 1659,
          6597 => 1659
      );

    $fontes65 = array(
      200 => 1501,
      163 => 1661,
      164 => 1660
    );

    $fontes96 = array(
      200 => 1500,
      23 => 1540,
      28 => 1550,
      11 => 1551,
      12 => 1552,
      45 => 1553,
      5 => 1569,
      211 => 1570,
      8 => 1573,
      34 => 1569
    );

    $fontes0 = array(
      5 => 1569,
      28 => 1550,
      200 => 1500,
      201 => 1501, 
      202 => 1501, 
      203 => 1501, 
      204 => 1501, 
      205 => 1501, 
      206 => 1501, 
      207 => 1501, 
      208 => 1501, 
      209 => 1501, 
      210 => 1501, 
      212 => 1501, 
      225 => 1501, 
      219 => 1704, 
      8 => 1704, 
      219 => 1705, 
      8 => 1705, 
      211 => 1700, 
      211 => 1701, 
      211 => 1702, 
      211 => 1703, 
      6 => 1750, 
      173 => 1751, 
      24 => 1708, 
      92 => 1700, 
      220 => 1749, 
      160 => 1749, 
      98 => 1701, 
      21 => 1701, 
      97 => 1755, 
      97 => 1756, 
      215 => 1801, 
      216 => 1800, 
      80 => 1501, 
      50 => 1799, 
      213 => 1754, 
      224 => 1899, 
      214 => 1802, 
      178 => 1752, 
      117 => 1701,
      23 => 1540,
11 => 1551,
45 => 1553,
34 => 1569,
164 => 1660,
148 => 1700,
22 => 1700,
105 => 1700,
18 => 1700,
44 => 1700,
14 => 1700,
124 => 1700,
158 => 1700,
151 => 1700,
6212 => 1621,
171 => 1701,
    170 => 1700
    );  

        $contasBancarias2 = [];
        foreach ($contasBancarias as $conta) {
            $k13_reduz = $conta->k13_reduz;
    
            if (isset($contasBancarias2[$k13_reduz])) {
                $contasBancarias2[$k13_reduz]->saldo_anterior += $conta->saldo_anterior;
                $contasBancarias2[$k13_reduz]->saldo_debito += $conta->saldo_debito;
                $contasBancarias2[$k13_reduz]->saldo_credito += $conta->saldo_credito;
                $contasBancarias2[$k13_reduz]->saldo_final += $conta->saldo_final;
            } else {
                $contasBancarias2[$k13_reduz] = clone $conta;
            }
        }
        $contasBancarias2 = array_values($contasBancarias2);
        
        
        
        if(db_getsession("DB_instit") == 1 || db_getsession("DB_instit") == 30 || db_getsession("DB_instit") == 75){
            $verifica = array();
        foreach ($contasBancarias2 as $contaBancaria) {
            if (empty($contaBancaria->conta)) {
                $consulta = pg_query("SELECT distinct o15_recurso, c60_estrut from saltes join conplanoreduz on conplanoreduz.c61_reduz = saltes.k13_reduz and c61_anousu= 2024 join conplanoexe on conplanoexe.c62_reduz = conplanoreduz.c61_reduz and c61_anousu=c62_anousu join conplano on conplanoreduz.c61_codcon = conplano.c60_codcon and c61_anousu=c60_anousu left join conplanoconta on conplanoconta.c63_codcon = conplanoreduz.c61_codcon and conplanoconta.c63_anousu = conplanoreduz.c61_anousu and conplanoconta.c63_reduz = conplanoreduz.c61_reduz left join empagetipo on empagetipo.e83_conta = saltes.k13_conta join orctiporec on o15_codigo = c61_codigo join fonterecurso on orctiporec_id = o15_codigo and exercicio = c61_anousu where k13_reduz = {$contaBancaria->k13_reduz}");
                $resultado = pg_fetch_all($consulta);                
                $contaBancaria->siconfi = $resultado[0]["o15_recurso"];
                $contaBancaria->conta = $resultado[0]["c60_estrut"];
                
                if(substr($contaBancaria->conta, 0,6) == 111112){
                    $contaBancaria->conta = 111110200;
                }elseif(substr($contaBancaria->conta, 0,7) == 1111119){
                    $contaBancaria->conta = 111111900;
                }elseif(substr($contaBancaria->conta, 0,6) == 111115){
                    $contaBancaria->conta = 111115000;
                }
            }



            if (floatval($contaBancaria->saldo_debito) != 0 && floatval($contaBancaria->saldo_credito != 0)) {

                if(db_getsession('DB_instit') == 50){
                    if($fontes50[$contaBancaria->siconfi]){
                      $contaBancaria->siconfi = $fontes50[$contaBancaria->siconfi];
                    }
                }elseif(db_getsession('DB_instit') == 65){
                    if($fontes65[$contaBancaria->siconfi]){
                      $contaBancaria->siconfi = $fontes65[$contaBancaria->siconfi];
                    }
                }elseif(db_getsession('DB_instit') == 96){
                    if($fontes96[$contaBancaria->siconfi]){
                      $contaBancaria->siconfi = $fontes96[$contaBancaria->siconfi];
                    }
                }else{
                    if($fontes0[$contaBancaria->siconfi]){
                      $contaBancaria->siconfi = $fontes0[$contaBancaria->siconfi];
                    }
                }
                

                if($verifica[$contaBancaria->db83_conta]){continue;}
                $novadata = $this->voltaDataConciliacao($this->competencia, $this->iAnoUsu);
                
                $s1 = (float)$contaBancaria->saldo_anterior;
                $s2 = (float)$contaBancaria->saldo_debito;
                $s3 = (float)$contaBancaria->saldo_credito;                    
                if($s1 < 0){$s1 *= -1;}
                if($s3 < 0){$s3 *= -1;}
                $novovalor = $s1 - $s2 + $s3;

                $dataAberturaConta = (new \Datetime($contaBancaria->k13_dtimplantacao))->format('Y-m-d');
                $dataFechamentoConta = (new \Datetime($contaBancaria->k13_limite))->format('Y-m-d');

                $dadosLiquidacaoEmpenho = (object)[
                    'Identificador' => $contaBancaria->k13_reduz, //$contaBancaria->codigo_conplano.$contaBancaria->siconfi,
                    'CodigoUnidadeGestora' => $this->sCodigoTribunal,
                    'Exercicio' => $this->iAnoUsu,                    
                    'Competencia' => $this->competencia,
                    'NumeroConciliacao' => $contaBancaria->k13_reduz . $this->iAnoUsu . $this->competencia,
                    'DataConciliacao' => $novadata,
                    'Banco' => $contaBancaria->db90_codban,
                    'Agencia' => $contaBancaria->db89_codagencia,
                    'ContaBancaria' => $contaBancaria->db83_conta,
                    'Tipo' => 3,
                    'Descricao' => substr(Helper::convertAndLimit($contaBancaria->db83_descricao), 0, 20),
                    'Valor' => $novovalor
                ];
                $verifica[$contaBancaria->db83_conta] = $contaBancaria->db83_tipoconta;

                $obj->ConciliacoesBancarias[] = (object)['ConciliacaoBancaria' => $dadosLiquidacaoEmpenho];
            }//foreach
        }

        }else{
            $verifica = array();
        foreach ($contasBancarias2 as $contaBancaria) {
            if (empty($contaBancaria->conta)) {
                $consulta = pg_query("SELECT distinct o15_recurso, c60_estrut from saltes join conplanoreduz on conplanoreduz.c61_reduz = saltes.k13_reduz and c61_anousu= 2024 join conplanoexe on conplanoexe.c62_reduz = conplanoreduz.c61_reduz and c61_anousu=c62_anousu join conplano on conplanoreduz.c61_codcon = conplano.c60_codcon and c61_anousu=c60_anousu left join conplanoconta on conplanoconta.c63_codcon = conplanoreduz.c61_codcon and conplanoconta.c63_anousu = conplanoreduz.c61_anousu and conplanoconta.c63_reduz = conplanoreduz.c61_reduz left join empagetipo on empagetipo.e83_conta = saltes.k13_conta join orctiporec on o15_codigo = c61_codigo join fonterecurso on orctiporec_id = o15_codigo and exercicio = c61_anousu where k13_reduz = {$contaBancaria->k13_reduz}");
                $resultado = pg_fetch_all($consulta);                
                $contaBancaria->siconfi = $resultado[0]["o15_recurso"];
                $contaBancaria->conta = $resultado[0]["c60_estrut"];
                
                if(substr($contaBancaria->conta, 0,6) == 111112){
                    $contaBancaria->conta = 111110200;
                }elseif(substr($contaBancaria->conta, 0,7) == 1111119){
                    $contaBancaria->conta = 111111900;
                }elseif(substr($contaBancaria->conta, 0,6) == 111115){
                    $contaBancaria->conta = 111115000;
                }
                
            }
            if (floatval($contaBancaria->saldo_debito) == 0 && floatval($contaBancaria->saldo_credito == 0)) {continue;}
            //if (floatval($contaBancaria->saldo_debito) != 0 && floatval($contaBancaria->saldo_credito != 0)) {
                /*
                if(db_getsession('DB_instit') == 50){
                    if($fontes50[$contaBancaria->siconfi]){
                      $contaBancaria->siconfi = $fontes50[$contaBancaria->siconfi];
                    }
                }elseif(db_getsession('DB_instit') == 65){
                    if($fontes65[$contaBancaria->siconfi]){
                      $contaBancaria->siconfi = $fontes65[$contaBancaria->siconfi];
                    }
                }elseif(db_getsession('DB_instit') == 96){
                    if($fontes96[$contaBancaria->siconfi]){
                      $contaBancaria->siconfi = $fontes96[$contaBancaria->siconfi];
                    }
                }else{
                    if($fontes0[$contaBancaria->siconfi]){
                      $contaBancaria->siconfi = $fontes0[$contaBancaria->siconfi];
                    }
                }
                */
                if($fontes0[$contaBancaria->siconfi]){
                    $contaBancaria->siconfi = $fontes0[$contaBancaria->siconfi];
                }

                if($verifica[$contaBancaria->db83_conta]){continue;}
                $novadata = $this->voltaDataConciliacao($this->competencia, $this->iAnoUsu);
                $s1 = (float)$contaBancaria->saldo_anterior;
                    $s2 = (float)$contaBancaria->saldo_debito;
                    $s3 = (float)$contaBancaria->saldo_credito;
                    
                    if($s1 < 0){
                        $s1 *= -1;
                    }
                    if($s3 < 0){
                        $s3 *= -1;
                    }
                        
                    $novovalor = $s1 - $s2 + $s3;
                
                
                    $xb = $contaBancaria->db90_codban;
                    $xa = $contaBancaria->db89_codagencia;
                    $xc = $contaBancaria->db83_conta; 
                    
                    if(
                        ($xb == "389" && $xa == "016" && $xc == "02010140") ||
                        ($xb == "399" && $xa == "025" && $xc == "000026") ||
                        ($xb == "275" && $xa == "536" && $xc == "8705566") ||
                        ($xb == "237" && $xa == "7138" && $xc == "13349") ||
                        ($xb == "389" && $xa == "0056" && $xc == "02083251")
                    ){continue;}
                        

                $dataAberturaConta = (new \Datetime($contaBancaria->k13_dtimplantacao))->format('Y-m-d');
                $dataFechamentoConta = (new \Datetime($contaBancaria->k13_limite))->format('Y-m-d');
                if($arquivosdo == "0"){
                    $novadata = "2025-01-02";
                }
                //if($contaBancaria->k13_reduz != 15235){continue;}

                $dadosLiquidacaoEmpenho = (object)[
                    'Identificador' => $contaBancaria->k13_reduz, //$contaBancaria->codigo_conplano.$contaBancaria->siconfi,
                    'CodigoUnidadeGestora' => $this->sCodigoTribunal,
                    'Exercicio' => $this->iAnoUsu,                    
                    'Competencia' => $this->competencia,
                    'NumeroConciliacao' => $contaBancaria->k13_reduz . $this->iAnoUsu . $this->competencia,
                    'DataConciliacao' => $novadata,
                    'Banco' => $contaBancaria->db90_codban,
                    'Agencia' => $contaBancaria->db89_codagencia,
                    'ContaBancaria' => $contaBancaria->db83_conta,
                    'Tipo' => 3,
                    'Descricao' => Helper::convertAndLimit($contaBancaria->db83_descricao),
                    'Valor' => $contaBancaria->saldo_final //$novovalor
                ];
                $verifica[$contaBancaria->db83_conta] = $contaBancaria->db83_tipoconta;

                $obj->ConciliacoesBancarias[] = (object)['ConciliacaoBancaria' => $dadosLiquidacaoEmpenho];
            }//foreach
        }
        $this->aDados =  $obj;
    }

    
}//clase

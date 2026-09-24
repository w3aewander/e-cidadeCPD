<?php

namespace ECidade\Financeiro\Contabilidade\Exportacao\Sigfis\Arquivos\v2024;

use ECidade\Financeiro\Contabilidade\Exportacao\Sigfis\Common\Helper;
use Illuminate\Database\Capsule\Manager as DB;

//require_once modification("../../../../../../../libs/db_libcontabilidade.php");
require_once modification('libs/db_libcontabilidade.php');

class ArquivoBalancete extends ArquivoBase
{
    protected $sNomeArquivo = 'Balancete';

    public function testa($var){
        echo "<pre>";
        print_r($var);
        echo "</pre>";
    }

    public function buscaDados($mes){                   
        $sql = pg_query("SELECT competencia, sum(CASE WHEN tipo = 'C' THEN case when tipo <> tipo_abertura and tipo_abertura <> '' and tipo_movimento = 1 then 0 else valor end ELSE 0 END) AS valor_credito, sum(CASE WHEN tipo = 'D' THEN case when tipo <> tipo_abertura and tipo_abertura <> '' and tipo_movimento = 1 then 0 else valor end ELSE 0 END) AS valor_debito, conta, reduzido, tipo_movimento, estrutural, tipo_abertura from (SELECT to_char(c70_data,'YYYYmm') as competencia, (case c53_tipo when 1000 then 2 when 2000 then 1 else 3 end ) as tipo_movimento, planocredito.c60_codcon as conta, sum(c69_valor) as valor, 'C' as tipo, planocredito.c60_estrut as estrutural, reduzcredito.c61_reduz as reduzido, reduzcredito.c61_anousu, (select CASE WHEN c62_vlrcre <> 0 and c62_vlrdeb = 0 THEN 'C' WHEN c62_vlrcre = 0 and c62_vlrdeb <> 0 THEN 'D' ELSE '' END as teste from conplanoexe where conplanoexe.c62_reduz = reduzcredito.c61_reduz and conplanoexe.c62_anousu = reduzcredito.c61_anousu) AS tipo_abertura from conlancamval inner join conlancam on c69_codlan = c70_codlan inner join conlancamdoc on c71_codlan = c70_codlan inner join conhistdoc on c71_coddoc = c53_coddoc inner join conplanoreduz reduzcredito on reduzcredito.c61_reduz = c69_credito and reduzcredito.c61_anousu = c69_anousu and reduzcredito.c61_instit = {$this->instit} inner join conplano planocredito on planocredito.c60_codcon = reduzcredito.c61_codcon and planocredito.c60_anousu = reduzcredito.c61_anousu where c70_data between cast('$this->dtDataInicial' as date) and cast('$this->dtDataFinal' as date) and c70_anousu = {$this->iAnoUsu} GROUP BY 3,1,2,5,6,7,8 union SELECT to_char(c70_data,'YYYYmm') as competencia, (case c53_tipo when 1000 then 2 when 2000 then 1 else 3 end ) as tipo_movimento, planodebito.c60_codcon as conta, sum(c69_valor) as valor, 'D' as tipo, planodebito.c60_estrut as estrutural, reduzdebito.c61_reduz as reduzido, reduzdebito.c61_anousu, (select CASE WHEN c62_vlrcre <> 0 and c62_vlrdeb = 0 THEN 'C' WHEN c62_vlrcre = 0 and c62_vlrdeb <> 0 THEN 'D' ELSE '' END as teste from conplanoexe where conplanoexe.c62_reduz = reduzdebito.c61_reduz and conplanoexe.c62_anousu = reduzdebito.c61_anousu) AS tipo_abertura from conlancamval inner join conlancam on c69_codlan = c70_codlan inner join conlancamdoc on c71_codlan = c70_codlan inner join conhistdoc on c71_coddoc = c53_coddoc inner join conplanoreduz reduzdebito on reduzdebito.c61_reduz = c69_debito and reduzdebito.c61_anousu = c69_anousu and reduzdebito.c61_instit = {$this->instit} inner join conplano planodebito on planodebito.c60_codcon = reduzdebito.c61_codcon and planodebito.c60_anousu = reduzdebito.c61_anousu where c70_data between cast('{$this->dtDataInicial}' as date) and cast('{$this->dtDataFinal}' as date) and c70_anousu = 2024 GROUP BY 1,2,3,5,6,7,8 UNION SELECT Cast(c62_anousu AS TEXT) || '01' AS competencia, 1 AS tipo_movimento, c61_codcon AS conta, (CASE WHEN c62_vlrcre <> 0 THEN c62_vlrcre ELSE c62_vlrdeb END) AS c69_valor, (CASE WHEN c62_vlrcre <> 0 THEN 'C' ELSE 'D' END) AS tipo, c60_estrut AS estrutural, conplanoreduz.c61_reduz as reduzido, c61_anousu, (CASE WHEN c62_vlrcre <> 0 and c62_vlrdeb = 0 THEN 'C' WHEN c62_vlrcre = 0 and c62_vlrdeb <> 0 THEN 'D' ELSE '' END) AS tipo_abertura FROM contabilidade.conplanoexe, contabilidade.conplanoreduz, contabilidade.conplano WHERE c62_anousu = {$this->iAnoUsu} AND c61_instit = {$this->instit} AND c62_anousu = c61_anousu AND c62_reduz = c61_reduz AND c61_anousu = c60_anousu AND c61_codcon = c60_codcon AND c62_vlrcre + c62_vlrdeb <> 0 AND To_char(Cast('{$this->dtDataInicial}' AS DATE),'mm') = '{$mes}' ) lanc group by conta, competencia, tipo_movimento, reduzido, estrutural, tipo_abertura order by estrutural");
        $resultado = pg_fetch_all($sql);
        return $resultado;    
    }


    public function buscaDados13(){        
        $sql = pg_query("SELECT competencia, sum(CASE WHEN tipo = 'C' THEN case when tipo <> tipo_abertura and tipo_abertura <> '' and tipo_movimento = 1 then 0 else valor end ELSE 0 END) AS valor_credito, sum(CASE WHEN tipo = 'D' THEN case when tipo <> tipo_abertura and tipo_abertura <> '' and tipo_movimento = 1 then 0 else valor end ELSE 0 END) AS valor_debito, conta, reduzido, tipo_movimento, estrutural, tipo_abertura from (SELECT to_char(c70_data,'YYYYmm') as competencia, (case c53_tipo when 1000 then 2 when 2000 then 1 else 3 end ) as tipo_movimento, planocredito.c60_codcon as conta, sum(c69_valor) as valor, 'C' as tipo, planocredito.c60_estrut as estrutural, reduzcredito.c61_reduz as reduzido, reduzcredito.c61_anousu, (select CASE WHEN c62_vlrcre <> 0 and c62_vlrdeb = 0 THEN 'C' WHEN c62_vlrcre = 0 and c62_vlrdeb <> 0 THEN 'D' ELSE '' END as teste from conplanoexe where conplanoexe.c62_reduz = reduzcredito.c61_reduz and conplanoexe.c62_anousu = reduzcredito.c61_anousu) AS tipo_abertura from conlancamval inner join conlancam on c69_codlan = c70_codlan inner join conlancamdoc on c71_codlan = c70_codlan inner join conhistdoc on c71_coddoc = c53_coddoc inner join conplanoreduz reduzcredito on reduzcredito.c61_reduz = c69_credito and reduzcredito.c61_anousu = c69_anousu and reduzcredito.c61_instit = {$this->instit} inner join conplano planocredito on planocredito.c60_codcon = reduzcredito.c61_codcon and planocredito.c60_anousu = reduzcredito.c61_anousu where c70_data between '2024-01-01' and '2024-12-31' and c70_anousu = {$this->iAnoUsu} GROUP BY 3,1,2,5,6,7,8 union SELECT to_char(c70_data,'YYYYmm') as competencia, (case c53_tipo when 1000 then 2 when 2000 then 1 else 3 end ) as tipo_movimento, planodebito.c60_codcon as conta, sum(c69_valor) as valor, 'D' as tipo, planodebito.c60_estrut as estrutural, reduzdebito.c61_reduz as reduzido, reduzdebito.c61_anousu, (select CASE WHEN c62_vlrcre <> 0 and c62_vlrdeb = 0 THEN 'C' WHEN c62_vlrcre = 0 and c62_vlrdeb <> 0 THEN 'D' ELSE '' END as teste from conplanoexe where conplanoexe.c62_reduz = reduzdebito.c61_reduz and conplanoexe.c62_anousu = reduzdebito.c61_anousu) AS tipo_abertura from conlancamval inner join conlancam on c69_codlan = c70_codlan inner join conlancamdoc on c71_codlan = c70_codlan inner join conhistdoc on c71_coddoc = c53_coddoc inner join conplanoreduz reduzdebito on reduzdebito.c61_reduz = c69_debito and reduzdebito.c61_anousu = c69_anousu and reduzdebito.c61_instit = {$this->instit} inner join conplano planodebito on planodebito.c60_codcon = reduzdebito.c61_codcon and planodebito.c60_anousu = reduzdebito.c61_anousu where c70_data between '2024-01-01' and '2024-12-31' and c70_anousu = 2024 GROUP BY 1,2,3,5,6,7,8 UNION SELECT Cast(c62_anousu AS TEXT) || '01' AS competencia, 1 AS tipo_movimento, c61_codcon AS conta, (CASE WHEN c62_vlrcre <> 0 THEN c62_vlrcre ELSE c62_vlrdeb END) AS c69_valor, (CASE WHEN c62_vlrcre <> 0 THEN 'C' ELSE 'D' END) AS tipo, c60_estrut AS estrutural, conplanoreduz.c61_reduz as reduzido, c61_anousu, (CASE WHEN c62_vlrcre <> 0 and c62_vlrdeb = 0 THEN 'C' WHEN c62_vlrcre = 0 and c62_vlrdeb <> 0 THEN 'D' ELSE '' END) AS tipo_abertura FROM contabilidade.conplanoexe, contabilidade.conplanoreduz, contabilidade.conplano WHERE c62_anousu = {$this->iAnoUsu} AND c61_instit = {$this->instit} AND c62_anousu = c61_anousu AND c62_reduz = c61_reduz AND c61_anousu = c60_anousu AND c61_codcon = c60_codcon AND c62_vlrcre + c62_vlrdeb <> 0 AND To_char(Cast('{$this->dtDataInicial}' AS DATE),'YYYY') = '2024' ) lanc group by conta, competencia, tipo_movimento, reduzido, estrutural, tipo_abertura order by estrutural");
        $resultado = pg_fetch_all($sql);
        return $resultado;    
    }

    public function buscaRec($reduzido, $ano){
        $sql = pg_query("SELECT c62_codrec FROM conplanoexe WHERE c62_anousu = {$ano} AND c62_reduz = {$reduzido}");
        $resultado = pg_fetch_all($sql);
        return $resultado[0]["c62_codrec"];
    }

    public function buscaCodigoSuperavit($estrutural, $ano){
        $sql = pg_query("SELECT conplano.c60_codcon,conplano.c60_estrut,conplanoreduz.c61_reduz,conplanoreduz.c61_instit, conplano.c60_identificadorfinanceiro from conplano inner join conclass on conclass.c51_codcla = conplano.c60_codcla inner join consistema on consistema.c52_codsis = conplano.c60_codsis left join conplanoreduz on conplanoreduz.c61_codcon = conplano.c60_codcon and c61_anousu = c60_anousu where c60_anousu = {$ano} AND c60_estrut = '{$estrutural}'");
        $resultado = pg_fetch_all($sql);
        return $resultado[0]["c60_identificadorfinanceiro"];
    }

    public function buscaFonteRecurso($reduzido){
        $ano = db_getsession("DB_anousu");
        //$sql = pg_query("select c61_codigo from conplanoreduz WHERE c61_reduz = {$reduzido} AND c61_anousu = 2024");
        $sql = pg_query("select c61_codigo from conplanoreduz WHERE c61_reduz = {$reduzido} AND c61_anousu = {$ano}");
        $resultado = pg_fetch_all($sql);
        return $resultado[0]["c61_codigo"];
    }

    public function gerarDados(){

        $poste = $_POST["json"];
        $expl1ode = explode(":", $poste);
        $expl2ode = explode("\\\"", $expl1ode[2]);
        $arquivosdo = $expl2ode[1];
        if($arquivosdo == "13"){
            $this->competencia = "202413";
            $consultaBalancete = db_planocontassaldo_matriz(db_getsession("DB_anousu"), '2024-01-01', '2024-12-31', true, " c61_instit in(".$this->instit.") AND c61_reduz > 0 ", '', true, 'true');
            $resultudo = db_query($consultaBalancete);
            $tudao = pg_fetch_all($resultudo);
        }else{
            $consultaBalancete = db_planocontassaldo_matriz(db_getsession("DB_anousu"), $this->dtDataInicial, $this->dtDataFinal, true, " c61_instit in(".$this->instit.") AND c61_reduz > 0", '', true, 'false');
        }
        
        
        $resultudo = db_query($consultaBalancete);
        $tudao = pg_fetch_all($resultudo);
        $novotudao = array_filter($tudao, function($item) {
            return $item['c61_reduz'] != 0;
        });
        $novotudao = array_values($novotudao);

        
        /*
        foreach ($novotudao as $tudo) {                        
            $init = substr($tudo["estrutural"], 0, 1);
            if($init == "4"){
                $this->testa($tudo);
            }
        }
        die("COnfere");
        */

        $xmes = substr($this->competencia, 4, 2);
        $iAnoSessao = db_getsession('DB_anousu');
        if($arquivosdo == 13){
            $dados = $this->buscaDados13();    
        }else{
            $dados = $this->buscaDados($xmes);    
        }
        
        

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
    170 => 1700,
    12 => 1552,
    134 => 6002,
    167 => 1569,
    16 => 1569,
    147 => 1660,
    62 => 1660,
    102 => 1700,
    6002 => 1600,
    6594 => 1659,
    1500 => 1500,
    1700 => 1500,
    1704 => 1500,
    101 => 1500,
    108 => 1500,
    111 => 1500,
    157 => 1500,
    1703 => 1500,
    29 => 1500,
    104 => 1500,
    78 => 1500,
    139 => 1500,
    136 => 1500,
    149 => 1500,
    161 => 1500,
    1569 => 1500,
    150 => 1500,
    68 => 1500,
    131 => 1500,
    145 => 1500,
    138 => 1500,
    10 => 1500
    );
        
        if (empty($dados)) {
            throw new \Exception('Não foi encontrado dado para emissão do arquivo da Remessa "Balancete".');
        }

        $obj = new \stdClass();
        $obj->Balancetes = [];
        $ix = 1;

        $novosdados = array();
        $indice = 0;
        
        $pegavalor = 0;
        foreach ($dados as $novo) {
            if($indice % 2 == 0){
                $novo["tipo_movimento"] = 1;
                $novo["novovalorx"] = $novo["valor_debito"];
                $novo["tipo_contax"] = "D";
                $pegavalor = (float)-$novo["valor_debito"];                
                array_push($novosdados, $novo);
            }else{
                $xdados = array();
                $xdados["competencia"] = $novo["competencia"];
                $xdados["valor_credito"] = $novo["valor_credito"];
                $xdados["valor_debito"] = $novo["valor_debito"];
                $xdados["conta"] = $novo["conta"];
                $xdados["reduzido"] = $novo["reduzido"];
                $xdados["tipo_movimento"] = 2;
                $xdados["estrutural"] = $novo["estrutural"];
                $xdados["tipo_abertura"] = $novo["tipo_abertura"];
                $xdados["tipo_contax"] = "D";
                $xdados["novovalorx"] = $novo["valor_debito"];

                $pegavalor += (float)-$novo["valor_debito"];                
                array_push($novosdados, $xdados);

                $xdados2 = array();
                $xdados2["competencia"] = $novo["competencia"];
                $xdados2["valor_credito"] = $novo["valor_credito"];
                $xdados2["valor_debito"] = $novo["valor_debito"];
                $xdados2["conta"] = $novo["conta"];
                $xdados2["reduzido"] = $novo["reduzido"];
                $xdados2["tipo_movimento"] = 2;
                $xdados2["estrutural"] = $novo["estrutural"];
                $xdados2["tipo_abertura"] = $novo["tipo_abertura"];
                $xdados2["tipo_contax"] = "C";
                $xdados2["novovalorx"] = $novo["valor_credito"];

                $pegavalor += (float)$novo["valor_credito"];
                array_push($novosdados, $xdados2);

                $xdados3 = array();
                $xdados3["competencia"] = $novo["competencia"];
                $xdados3["valor_credito"] = $novo["valor_credito"];
                $xdados3["valor_debito"] = $novo["valor_debito"];
                $xdados3["conta"] = $novo["conta"];
                $xdados3["reduzido"] = $novo["reduzido"];
                $xdados3["tipo_movimento"] = 3;
                $xdados3["estrutural"] = $novo["estrutural"];
                $xdados3["tipo_abertura"] = $novo["tipo_abertura"];
                $xdados3["tipo_contax"] = ($pegavalor < 0) ? "D" : "C";
                $xdados3["novovalorx"] = abs($pegavalor);
                array_push($novosdados, $xdados3);
                
                
                $pegavalor = 0;
                
            }
            
            
            
            
            $indice++;
        }
        
        
        $xontador = 0;
        $guardanapo = array();
        foreach ($novosdados as $dado) {
            //if($dado["reduzido"] != 14425){continue;}
            

            if(in_array($dado["reduzido"], $guardanapo)){
                continue;
            }
            array_push($guardanapo, $dado["reduzido"]);

                
            

            
            $sIndice = $dado['estrutural'] . $dado['competencia'] . $dado['tipo_movimento'] . $dado['reduzido'];
            
            $sContaCorrente  = "select (case when {$iAnoSessao} < 2016 then c56_sequencial else c56_contabancaria end) as seq_conta_corrente ";
            $sContaCorrente .= "  from contabilidade.conplano ";
            $sContaCorrente .= "       left join conplanocontabancaria  on conplano.c60_codcon = conplanocontabancaria.c56_codcon ";
            $sContaCorrente .= "                                       and conplano.c60_anousu = conplanocontabancaria.c56_anousu ";
            $sContaCorrente .= "       left join configuracoes.contabancaria on db83_sequencial = c56_contabancaria ";
            $sContaCorrente .= " where c60_anousu = {$this->iAnoUsu} ";
            $sContaCorrente .= "   and c60_codcon = {$dado['conta']} ";
            $sContaCorrente .= "   and c56_reduz = {$dado['reduzido']}";

            $rsContaCorrente = db_query($sContaCorrente);
            $re0 = pg_fetch_object($rsContaCorrente);            
            $oDadosContaCorrente = $re0;
            
            
            if ($dado['tipo_movimento'] == "") {
                $dado['tipo_movimento'] = 3;
            }

            if ($dado['competencia'] == "") {
                $dado['competencia'] = substr($this->dtDataInicial, 0, 4) . substr($this->dtDataInicial, 5, 2);
            }

            if($this->instit == 91){
                $cpo = 20231;
            }elseif($this->instit == 85){
                $cpo = 10132;
            }else{
                $cpo = 10131;
            }
            
            
            //if (substr($dado['competencia'], -2) == "01") {
                if ($dado['valor_credito'] > 0 || $dado['valor_debito'] > 0) {
                    $novovalor = 0;
                    if($dado['tipo_movimento'] == 1 || $dado['tipo_movimento'] == 3){
                        $novovalor = $dado["valor_debito"];
                    }else{
                        $novovalor = $dado["valor_credito"];
                    }

                    if(empty($dado['tipo_abertura'])){
                        $concon = substr($dado['estrutural'], 0, 7);
                        if(substr($concon, 0, 1) % 2 == 0){
                            $tipoconta = "D";
                        }else{
                            $tipoconta = "C";
                        }    
                    }else{
                        $tipoconta = $dado['tipo_abertura'];
                    }

                    $novafonte = $this->buscaRec($dado['reduzido'],$this->iAnoUsu);
                    /*if(substr($dado['estrutural'], 0, 7) == 1111119){
                        echo $dado['estrutural'] . "*****";
                        echo $ix . " - " . $dado["reduzido"] . " - ". $novafonte; echo "<br>";
                    }*/
                    
                    if($fontes0[$novafonte]){
                      $novafonte2 = $fontes0[$novafonte];
                    }else{
                        $novafonte2 = 1700;
                    }
                    

                    if(substr($dado['estrutural'], 0, 7) == 4522400){
                        $deducao = 3;
                    }else{
                        $deducao = 1;
                    }

                    $caeo = "0000";
                    if($this->instit == 96){
                        if(  (substr($dado['estrutural'], 0, 2) == 33 || substr($dado['estrutural'], 0, 4) == 2131) && $novafonte2 == 1540){
                            $caeo = 1001;
                        }

                        if(  (substr($dado['estrutural'], 0, 2) == 31 || substr($dado['estrutural'], 0, 2) == 32 || substr($dado['estrutural'], 0, 4) == 2111) && $novafonte2 == 1540){
                            $caeo = 1070;
                        }
                    }

                    if($this->instit == 20){
                        if(  (substr($dado['estrutural'], 0, 2) == 31 || substr($dado['estrutural'], 0, 2) == 32 || substr($dado['estrutural'], 0, 4) == 2188) && $novafonte2 == 1540){
                            $caeo = 1070;
                        }
                    }

                    /*if($this->instit == 50){
                        if( substr($dado['estrutural'], 0, 2) == 33 || substr($dado['estrutural'], 0, 2) == 32 || substr($dado['estrutural'], 0, 2) == 21) {
                            $caeo = 1070;
                        }
                    }*/

                    /*if($this->instit == 80){
                        var_dump($dado["estrutural"]); die("Confere");
                        if( substr($dado['estrutural'], 0, 2) == 33 || substr($dado['estrutural'], 0, 2) == 32 || substr($dado['estrutural'], 0, 2) == 21) {
                            $caeo = 1070;
                        }
                    }*/
                    if($this->instit == 80){
                     if(substr($dado['estrutural'], 0, 9) == 123810280){$contacontabilx = 123810299;}   
                    }

                    if($this->instit == 85){

                        if(substr($dado['estrutural'], 0, 4) == 6317){$contacontabilx = 631710000;}
                        elseif(substr($dado['estrutural'], 0, 8) == 44905192){$contacontabilx = 44905199;}
                        elseif(substr($dado['estrutural'], 0, 9) == 821110000){$contacontabilx = 821110100;}
                        elseif(substr($dado['estrutural'], 0, 9) == 821120000){$contacontabilx = 821120100;}
                        elseif(substr($dado['estrutural'], 0, 9) == 821130000){$contacontabilx = 821130100;}
                        elseif(substr($dado['estrutural'], 0, 9) == 821140000){$contacontabilx = 821140100;}
                        elseif(substr($dado['estrutural'], 0, 9) == 321110100){$contacontabilx = 321110101;}
                        elseif(substr($dado['estrutural'], 0, 9) == 211410901){$contacontabilx = 211410900;}
                        elseif(substr($dado['estrutural'], 0, 9) == 355010000){$contacontabilx = 35511000;}
                        elseif(substr($dado['estrutural'], 0, 9) == 211430100){$contacontabilx = 211430101;}
                        elseif(substr($dado['estrutural'], 0, 9) == 218819901){$contacontabilx = 218810199;}
                        elseif(substr($dado['estrutural'], 0, 15) == 111110602450100){$contacontabilx = 111110602;}
                        elseif(substr($dado['estrutural'], 0, 15) == 111110602460100){$contacontabilx = 111110602;}
                        elseif(substr($dado['estrutural'], 0, 15) == 111110602470100){$contacontabilx = 111110602;}
                        elseif(substr($dado['estrutural'], 0, 15) == 111110603450100){$contacontabilx = 111110603;}
                        elseif(substr($dado['estrutural'], 0, 15) == 111110603460100){$contacontabilx = 111110603;}
                        elseif(substr($dado['estrutural'], 0, 15) == 111110603460200){$contacontabilx = 111110603;}
                        elseif(substr($dado['estrutural'], 0, 15) == 111110603470200){$contacontabilx = 111110603;}
                        elseif(substr($dado['estrutural'], 0, 15) == 111110603480100){$contacontabilx = 111110603;}
                        elseif(substr($dado['estrutural'], 0, 15) == 111110604010000){$contacontabilx = 111110604;}
                        elseif(substr($dado['estrutural'], 0, 15) == 111111903016000){$contacontabilx = 111115300;}
                        elseif(substr($dado['estrutural'], 0, 15) == 111111903017000){$contacontabilx = 111115300;}
                        elseif(substr($dado['estrutural'], 0, 15) == 111111903018000){$contacontabilx = 111115300;}
                        elseif(substr($dado['estrutural'], 0, 15) == 111111922020000){$contacontabilx = 111115300;}
                        elseif(substr($dado['estrutural'], 0, 15) == 111112015052000){$contacontabilx = 111115300;}
                        elseif(substr($dado['estrutural'], 0, 15) == 111115002023547){$contacontabilx = 111115300;}
                        elseif(substr($dado['estrutural'], 0, 15) == 111115002023553){$contacontabilx = 111115300;}
                        elseif(substr($dado['estrutural'], 0, 15) == 111115002023554){$contacontabilx = 111115300;}
                        elseif(substr($dado['estrutural'], 0, 15) == 111115002032200){$contacontabilx = 111115300;}
                        elseif(substr($dado['estrutural'], 0, 15) == 111115003028500){$contacontabilx = 111115300;}
                        elseif(substr($dado['estrutural'], 0, 15) == 111115003028600){$contacontabilx = 111115300;}
                        elseif(substr($dado['estrutural'], 0, 15) == 111115003043107){$contacontabilx = 111115100;}
                        elseif(substr($dado['estrutural'], 0, 15) == 111115003043117){$contacontabilx = 111115300;}
                        elseif(substr($dado['estrutural'], 0, 15) == 111115003043118){$contacontabilx = 111115300;}
                        elseif(substr($dado['estrutural'], 0, 15) == 111115003043119){$contacontabilx = 111115300;}
                        elseif(substr($dado['estrutural'], 0, 15) == 111115003043122){$contacontabilx = 111115300;}
                        elseif(substr($dado['estrutural'], 0, 15) == 111115003043124){$contacontabilx = 111115300;}
                        elseif(substr($dado['estrutural'], 0, 15) == 111115003043125){$contacontabilx = 111115300;}
                        elseif(substr($dado['estrutural'], 0, 15) == 111115003043129){$contacontabilx = 111115300;}
                        elseif(substr($dado['estrutural'], 0, 15) == 111115003043178){$contacontabilx = 111115300;}
                        elseif(substr($dado['estrutural'], 0, 15) == 111115003050200){$contacontabilx = 111115300;}
                        elseif(substr($dado['estrutural'], 0, 15) == 111115003050300){$contacontabilx = 111115300;}
                        elseif(substr($dado['estrutural'], 0, 15) == 111115003150700){$contacontabilx = 111115300;}
                        elseif(substr($dado['estrutural'], 0, 15) == 111115003150800){$contacontabilx = 111115300;}
                        elseif(substr($dado['estrutural'], 0, 15) == 111115003150900){$contacontabilx = 111115300;}
                        elseif(substr($dado['estrutural'], 0, 15) == 111115003151000){$contacontabilx = 111115300;}
                        elseif(substr($dado['estrutural'], 0, 15) == 111115003151100){$contacontabilx = 111115300;}
                        elseif(substr($dado['estrutural'], 0, 15) == 111115003151300){$contacontabilx = 111115300;}
                        elseif(substr($dado['estrutural'], 0, 15) == 111115003151500){$contacontabilx = 111115300;}
                        elseif(substr($dado['estrutural'], 0, 15) == 111115003151600){$contacontabilx = 111115300;}
                        elseif(substr($dado['estrutural'], 0, 15) == 111115003151800){$contacontabilx = 111115300;}
                        elseif(substr($dado['estrutural'], 0, 15) == 111115003152000){$contacontabilx = 111115300;}
                        elseif(substr($dado['estrutural'], 0, 15) == 111115003152300){$contacontabilx = 111115300;}
                        elseif(substr($dado['estrutural'], 0, 15) == 111115003152500){$contacontabilx = 111115300;}
                        elseif(substr($dado['estrutural'], 0, 15) == 111115003152600){$contacontabilx = 111115300;}
                        elseif(substr($dado['estrutural'], 0, 15) == 111115003152700){$contacontabilx = 111115300;}
                        elseif(substr($dado['estrutural'], 0, 15) == 111115003152900){$contacontabilx = 111115300;}
                        elseif(substr($dado['estrutural'], 0, 15) == 111115003153100){$contacontabilx = 111115300;}
                        elseif(substr($dado['estrutural'], 0, 15) == 111115003153200){$contacontabilx = 111115300;}
                        elseif(substr($dado['estrutural'], 0, 15) == 111115003153500){$contacontabilx = 111115300;}
                        elseif(substr($dado['estrutural'], 0, 15) == 111115003153700){$contacontabilx = 111115300;}
                        elseif(substr($dado['estrutural'], 0, 15) == 111115003153800){$contacontabilx = 111115300;}
                        elseif(substr($dado['estrutural'], 0, 15) == 111115003153900){$contacontabilx = 111115300;}
                        elseif(substr($dado['estrutural'], 0, 15) == 111115003154100){$contacontabilx = 111115300;}
                        elseif(substr($dado['estrutural'], 0, 15) == 111115003154200){$contacontabilx = 111115300;}
                        elseif(substr($dado['estrutural'], 0, 15) == 111115003154300){$contacontabilx = 111115300;}
                        elseif(substr($dado['estrutural'], 0, 15) == 113620201010000){$contacontabilx = 113620201;}
                        elseif(substr($dado['estrutural'], 0, 15) == 113620201020000){$contacontabilx = 113620201;}
                        elseif(substr($dado['estrutural'], 0, 15) == 121120604010000){$contacontabilx = 121120604;}
                        elseif(substr($dado['estrutural'], 0, 15) == 121120604020000){$contacontabilx = 121120604;}
                        elseif(substr($dado['estrutural'], 0, 15) == 123110202000000){$contacontabilx = 123110202;}
                        elseif(substr($dado['estrutural'], 0, 15) == 211110101000000){$contacontabilx = 211110199;}
                        elseif(substr($dado['estrutural'], 0, 15) == 211110102000000){$contacontabilx = 211110199;}
                        elseif(substr($dado['estrutural'], 0, 15) == 213110101000000){$contacontabilx = 213110101;}
                        elseif(substr($dado['estrutural'], 0, 15) == 218810101860000){$contacontabilx = 218810115;}
                        elseif(substr($dado['estrutural'], 0, 15) == 218810101880000){$contacontabilx = 218810101;}
                        elseif(substr($dado['estrutural'], 0, 15) == 218810101900000){$contacontabilx = 218810115;}
                        elseif(substr($dado['estrutural'], 0, 15) == 218810103070000){$contacontabilx = 218810115;}
                        elseif(substr($dado['estrutural'], 0, 15) == 218810104010000){$contacontabilx = 218810104;}
                        elseif(substr($dado['estrutural'], 0, 15) == 218810104130000){$contacontabilx = 218810104;}
                        elseif(substr($dado['estrutural'], 0, 15) == 218810113010200){$contacontabilx = 218810113;}
                        elseif(substr($dado['estrutural'], 0, 15) == 218810113010300){$contacontabilx = 218810113;}
                        elseif(substr($dado['estrutural'], 0, 15) == 218810113010500){$contacontabilx = 218810113;}
                        elseif(substr($dado['estrutural'], 0, 15) == 218810113010600){$contacontabilx = 218810113;}
                        elseif(substr($dado['estrutural'], 0, 15) == 218810114010100){$contacontabilx = 218810114;}
                        elseif(substr($dado['estrutural'], 0, 15) == 218810114010200){$contacontabilx = 218810114;}
                        elseif(substr($dado['estrutural'], 0, 15) == 218810115010100){$contacontabilx = 218810115;}
                        elseif(substr($dado['estrutural'], 0, 15) == 218810115010400){$contacontabilx = 218810115;}
                        elseif(substr($dado['estrutural'], 0, 15) == 218810115010700){$contacontabilx = 218810115;}
                        elseif(substr($dado['estrutural'], 0, 15) == 218810199010100){$contacontabilx = 218810101;}
                        elseif(substr($dado['estrutural'], 0, 15) == 218810199010200){$contacontabilx = 218810101;}
                        elseif(substr($dado['estrutural'], 0, 15) == 218810199010400){$contacontabilx = 218810101;}
                        elseif(substr($dado['estrutural'], 0, 15) == 218810199010700){$contacontabilx = 218810101;}
                        elseif(substr($dado['estrutural'], 0, 15) == 218810199010800){$contacontabilx = 218810101;}
                        elseif(substr($dado['estrutural'], 0, 15) == 218810199010900){$contacontabilx = 218810101;}
                        elseif(substr($dado['estrutural'], 0, 15) == 218810199011000){$contacontabilx = 218810101;}
                        elseif(substr($dado['estrutural'], 0, 15) == 218810199011100){$contacontabilx = 218810101;}
                        elseif(substr($dado['estrutural'], 0, 15) == 218810199011200){$contacontabilx = 218810101;}
                        elseif(substr($dado['estrutural'], 0, 15) == 218810199011400){$contacontabilx = 218810101;}
                        elseif(substr($dado['estrutural'], 0, 15) == 218810199011800){$contacontabilx = 218810101;}
                        elseif(substr($dado['estrutural'], 0, 15) == 218810199012000){$contacontabilx = 218810101;}
                        elseif(substr($dado['estrutural'], 0, 15) == 218810199012300){$contacontabilx = 218810101;}
                        elseif(substr($dado['estrutural'], 0, 15) == 218810199012800){$contacontabilx = 218810101;}
                        elseif(substr($dado['estrutural'], 0, 15) == 218810199012900){$contacontabilx = 218810101;}
                        elseif(substr($dado['estrutural'], 0, 15) == 218810199013300){$contacontabilx = 218810101;}
                        elseif(substr($dado['estrutural'], 0, 15) == 218810199013600){$contacontabilx = 218810101;}
                        elseif(substr($dado['estrutural'], 0, 15) == 218810199015300){$contacontabilx = 218810101;}
                        elseif(substr($dado['estrutural'], 0, 15) == 218810199018700){$contacontabilx = 218810301;}
                        elseif(substr($dado['estrutural'], 0, 15) == 218810199019400){$contacontabilx = 218810115;}
                        elseif(substr($dado['estrutural'], 0, 15) == 218810199020400){$contacontabilx = 218810101;}
                        elseif(substr($dado['estrutural'], 0, 15) == 218810199023200){$contacontabilx = 218810101;}
                        elseif(substr($dado['estrutural'], 0, 15) == 218810199023900){$contacontabilx = 218810101;}
                        elseif(substr($dado['estrutural'], 0, 15) == 218810199024000){$contacontabilx = 218810115;}
                        elseif(substr($dado['estrutural'], 0, 15) == 218810199024100){$contacontabilx = 218810101;}
                        elseif(substr($dado['estrutural'], 0, 15) == 218810199024200){$contacontabilx = 218810104;}
                        elseif(substr($dado['estrutural'], 0, 15) == 218810199024300){$contacontabilx = 218810115;}
                        elseif(substr($dado['estrutural'], 0, 15) == 218810199024400){$contacontabilx = 218810113;}
                        elseif(substr($dado['estrutural'], 0, 15) == 218810199024500){$contacontabilx = 218810113;}
                        elseif(substr($dado['estrutural'], 0, 15) == 218810499010119){$contacontabilx = 218810115;}
                        elseif(substr($dado['estrutural'], 0, 15) == 218819901010602){$contacontabilx = 218810301;}
                        elseif(substr($dado['estrutural'], 0, 15) == 218819901028500){$contacontabilx = 218810105;}
                        elseif(substr($dado['estrutural'], 0, 15) == 218819901028600){$contacontabilx = 218810115;}
                        elseif(substr($dado['estrutural'], 0, 15) == 227210101000000){$contacontabilx = 227210101;}
                        elseif(substr($dado['estrutural'], 0, 15) == 227210103000000){$contacontabilx = 227210103;}
                        elseif(substr($dado['estrutural'], 0, 15) == 227210104000000){$contacontabilx = 227210104;}
                        elseif(substr($dado['estrutural'], 0, 15) == 227210107000000){$contacontabilx = 227220101;}
                        elseif(substr($dado['estrutural'], 0, 15) == 227210201000000){$contacontabilx = 227210201;}
                        elseif(substr($dado['estrutural'], 0, 15) == 227210202000000){$contacontabilx = 227210202;}
                        elseif(substr($dado['estrutural'], 0, 15) == 227210203000000){$contacontabilx = 227210203;}
                        elseif(substr($dado['estrutural'], 0, 15) == 227210206000000){$contacontabilx = 227220203;}
                        elseif(substr($dado['estrutural'], 0, 15) == 227210301000000){$contacontabilx = 227210301;}
                        elseif(substr($dado['estrutural'], 0, 15) == 227210303020000){$contacontabilx = 227210303;}
                        elseif(substr($dado['estrutural'], 0, 15) == 227210304020000){$contacontabilx = 227210304;}
                        elseif(substr($dado['estrutural'], 0, 15) == 227210305000000){$contacontabilx = 227210305;}
                        elseif(substr($dado['estrutural'], 0, 15) == 227210401000000){$contacontabilx = 227210401;}
                        elseif(substr($dado['estrutural'], 0, 15) == 227210402000000){$contacontabilx = 227210402;}
                        elseif(substr($dado['estrutural'], 0, 15) == 227210403000000){$contacontabilx = 227210403;}
                        elseif(substr($dado['estrutural'], 0, 15) == 227210404000000){$contacontabilx = 227210404;}
                        elseif(substr($dado['estrutural'], 0, 15) == 237110100000000){$contacontabilx = 237110100;}
                        elseif(substr($dado['estrutural'], 0, 15) == 237110200000000){$contacontabilx = 237110200;}
                        elseif(substr($dado['estrutural'], 0, 15) == 237110301000000){$contacontabilx = 237110300;}
                        elseif(substr($dado['estrutural'], 0, 15) == 237110302000000){$contacontabilx = 237110300;}
                        //elseif(substr($dado['estrutural'], 0, 15) == 321110108010000){$contacontabilx = 321110100;}
                        elseif(substr($dado['estrutural'], 0, 15) == 322110201000000){$contacontabilx = 322110100;}
                        elseif(substr($dado['estrutural'], 0, 15) == 332110500000000){$contacontabilx = 332110100;}
                        elseif(substr($dado['estrutural'], 0, 15) == 332213100000000){$contacontabilx = 332213100;}
                        elseif(substr($dado['estrutural'], 0, 15) == 332310100010000){$contacontabilx = 332315100;}
                        elseif(substr($dado['estrutural'], 0, 15) == 332311100000000){$contacontabilx = 332311100;}
                        elseif(substr($dado['estrutural'], 0, 15) == 361710701010000){$contacontabilx = 361710800;}
                        elseif(substr($dado['estrutural'], 0, 15) == 361710701030000){$contacontabilx = 361710800;}
                        elseif(substr($dado['estrutural'], 0, 15) == 421110201100100){$contacontabilx = 421110201;}
                        elseif(substr($dado['estrutural'], 0, 15) == 421110201100200){$contacontabilx = 421110201;}
                        elseif(substr($dado['estrutural'], 0, 15) == 421110201100300){$contacontabilx = 421110201;}
                        elseif(substr($dado['estrutural'], 0, 15) == 421110201100400){$contacontabilx = 421110201;}
                        elseif(substr($dado['estrutural'], 0, 15) == 421110201100500){$contacontabilx = 421110201;}
                        elseif(substr($dado['estrutural'], 0, 15) == 421110201100600){$contacontabilx = 421110201;}
                        elseif(substr($dado['estrutural'], 0, 15) == 421110201100700){$contacontabilx = 421110201;}
                        elseif(substr($dado['estrutural'], 0, 15) == 421110202100100){$contacontabilx = 421110202;}
                        elseif(substr($dado['estrutural'], 0, 15) == 421110202100200){$contacontabilx = 421110202;}
                        elseif(substr($dado['estrutural'], 0, 15) == 421110202110100){$contacontabilx = 421110203;}
                        elseif(substr($dado['estrutural'], 0, 15) == 421110203100100){$contacontabilx = 421110203;}
                        elseif(substr($dado['estrutural'], 0, 15) == 421110203100200){$contacontabilx = 421110203;}
                        elseif(substr($dado['estrutural'], 0, 15) == 421120101010100){$contacontabilx = 421120101;}
                        elseif(substr($dado['estrutural'], 0, 15) == 421120101010200){$contacontabilx = 421120101;}
                        elseif(substr($dado['estrutural'], 0, 15) == 421120101010300){$contacontabilx = 421120199;}
                        elseif(substr($dado['estrutural'], 0, 15) == 421120101020100){$contacontabilx = 421120101;}
                        elseif(substr($dado['estrutural'], 0, 15) == 421120101020200){$contacontabilx = 421120101;}
                        elseif(substr($dado['estrutural'], 0, 15) == 421120101020300){$contacontabilx = 421120101;}
                        elseif(substr($dado['estrutural'], 0, 15) == 421120101020400){$contacontabilx = 421120199;}
                        elseif(substr($dado['estrutural'], 0, 15) == 442520101200100){$contacontabilx = 442520101;}
                        elseif(substr($dado['estrutural'], 0, 15) == 442520101200200){$contacontabilx = 442520101;}
                        elseif(substr($dado['estrutural'], 0, 15) == 442520101200300){$contacontabilx = 442520101;}
                        elseif(substr($dado['estrutural'], 0, 15) == 445210300000000){$contacontabilx = 445210000;}
                        elseif(substr($dado['estrutural'], 0, 15) == 451020111000000){$contacontabilx = 451320101;}
                        elseif(substr($dado['estrutural'], 0, 15) == 499150000000000){$contacontabilx = 499150000;}
                        elseif(substr($dado['estrutural'], 0, 15) == 522120100000000){$contacontabilx = 522120100;}
                        elseif(substr($dado['estrutural'], 0, 15) == 522130300000000){$contacontabilx = 522130300;}
                        elseif(substr($dado['estrutural'], 0, 15) == 522130900000000){$contacontabilx = 522130900;}
                        elseif(substr($dado['estrutural'], 0, 15) == 522139900000000){$contacontabilx = 522139900;}
                        elseif(substr($dado['estrutural'], 0, 15) == 522190400000000){$contacontabilx = 522190400;}
                        elseif(substr($dado['estrutural'], 0, 15) == 531701000000000){$contacontabilx = 531700000;}
                        elseif(substr($dado['estrutural'], 0, 15) == 532701000000000){$contacontabilx = 532700000;}
                        elseif(substr($dado['estrutural'], 0, 15) == 621101000000000){$contacontabilx = 621100000;}
                        elseif(substr($dado['estrutural'], 0, 15) == 621201000000000){$contacontabilx = 621200000;}
                        elseif(substr($dado['estrutural'], 0, 15) == 622110000000000){$contacontabilx = 622110000;}
                        elseif(substr($dado['estrutural'], 0, 15) == 622130100000000){$contacontabilx = 622130100;}
                        elseif(substr($dado['estrutural'], 0, 15) == 622130300000000){$contacontabilx = 622130300;}
                        elseif(substr($dado['estrutural'], 0, 15) == 622130400000000){$contacontabilx = 622130400;}
                        elseif(substr($dado['estrutural'], 0, 15) == 622920103000000){$contacontabilx = 622920103;}
                        elseif(substr($dado['estrutural'], 0, 15) == 622920104000000){$contacontabilx = 622920104;}
                        elseif(substr($dado['estrutural'], 0, 15) == 631101900000000){$contacontabilx = 631100000;}
                        elseif(substr($dado['estrutural'], 0, 15) == 631308100000000){$contacontabilx = 631300000;}
                        elseif(substr($dado['estrutural'], 0, 15) == 631408100000000){$contacontabilx = 631400000;}
                        //elseif(substr($dado['estrutural'], 0, 15) == 631708100000000){$contacontabilx = 631700000;}
                        elseif(substr($dado['estrutural'], 0, 15) == 632701000000000){$contacontabilx = 632700000;}
                        elseif(substr($dado['estrutural'], 0, 15) == 721110100000000){$contacontabilx = 721110000;}
                        elseif(substr($dado['estrutural'], 0, 15) == 721130000000000){$contacontabilx = 721130000;}
                        //elseif(substr($dado['estrutural'], 0, 15) == 821110001000000){$contacontabilx = 821110000;}
                        //elseif(substr($dado['estrutural'], 0, 15) == 821120001000000){$contacontabilx = 821120000;}
                        elseif(substr($dado['estrutural'], 0, 15) == 821120100000000){$contacontabilx = 821120100;}
                        //elseif(substr($dado['estrutural'], 0, 15) == 821130001000000){$contacontabilx = 821130000;}
                        elseif(substr($dado['estrutural'], 0, 15) == 821130100000000){$contacontabilx = 821130100;}
                        elseif(substr($dado['estrutural'], 0, 15) == 821130201000000){$contacontabilx = 821130200;}
                        elseif(substr($dado['estrutural'], 0, 15) == 821130800000000){$contacontabilx = 821139900;}
                        //elseif(substr($dado['estrutural'], 0, 15) == 821140100000000){$contacontabilx = 821140000;}
                        //elseif(substr($dado['estrutural'], 0, 15) == 821140800000000){$contacontabilx = 821140000;}

                    }else{
                        if(substr($dado['estrutural'], 0, 4) == 6321){$contacontabilx = 632100000;}
                        elseif(substr($dado['estrutural'], 0, 4) == 6322){$contacontabilx = 632200000;}
                        elseif(substr($dado['estrutural'], 0, 4) == 6327){$contacontabilx = 632700000;}
                        elseif(substr($dado['estrutural'], 0, 4) == 5311){$contacontabilx = 531100000;}
                        elseif(substr($dado['estrutural'], 0, 4) == 5317){$contacontabilx = 531700000;}
                        elseif(substr($dado['estrutural'], 0, 4) == 5321){$contacontabilx = 532100000;}
                        elseif(substr($dado['estrutural'], 0, 4) == 5327){$contacontabilx = 532700000;}
                        elseif(substr($dado['estrutural'], 0, 4) == 6211){$contacontabilx = 621100000;}
                        elseif(substr($dado['estrutural'], 0, 4) == 6212){$contacontabilx = 621200000;}
                        elseif(substr($dado['estrutural'], 0, 4) == 6311){$contacontabilx = 631100000;}
                        elseif(substr($dado['estrutural'], 0, 4) == 6313){$contacontabilx = 631300000;}
                        elseif(substr($dado['estrutural'], 0, 4) == 6314){$contacontabilx = 631400000;}
                        elseif(substr($dado['estrutural'], 0, 4) == 4199){$contacontabilx = 411950000;}
                        elseif(substr($dado['estrutural'], 0, 4) == 4247){$contacontabilx = 459130000;}
                        elseif(substr($dado['estrutural'], 0, 5) == 41931){$contacontabilx = 411210200;}
                        elseif(substr($dado['estrutural'], 0, 5) == 42301){$contacontabilx = 423110100;}
                        elseif(substr($dado['estrutural'], 0, 5) == 49951){$contacontabilx = 499510000;}
                        elseif(substr($dado['estrutural'], 0, 5) == 49961){$contacontabilx = 499610100;}
                        elseif(substr($dado['estrutural'], 0, 5) == 49991){$contacontabilx = 499910000;}
                        elseif(substr($dado['estrutural'], 0, 5) == 72111){$contacontabilx = 721110000;}
                        elseif(substr($dado['estrutural'], 0, 5) == 82111){$contacontabilx = 821110100;}
                        elseif(substr($dado['estrutural'], 0, 5) == 62211){$contacontabilx = 622110000;}
                        elseif(substr($dado['estrutural'], 0, 6) == 452430){$contacontabilx = 452430000;}
                        elseif(substr($dado['estrutural'], 0, 6) == 413250){$contacontabilx = 413250000;}                    
                        elseif(substr($dado['estrutural'], 0, 7) == 1111102){$contacontabilx = 111110200;}
                        elseif(substr($dado['estrutural'], 0, 7) == 1111119){$contacontabilx = 111111900;}
                        elseif(substr($dado['estrutural'], 0, 7) == 1111150){$contacontabilx = 111115000;}
                        elseif(substr($dado['estrutural'], 0, 7) == 1111120){$contacontabilx = 111110200;}
                        elseif(substr($dado['estrutural'], 0, 7) == 1138199){$contacontabilx = 113819900;}
                        elseif(substr($dado['estrutural'], 0, 7) == 1211504){$contacontabilx = 121150400;}
                        elseif(substr($dado['estrutural'], 0, 7) == 1231102){$contacontabilx = 123110202;}
                        elseif(substr($dado['estrutural'], 0, 7) == 1231110){$contacontabilx = 123111000;}
                        elseif(substr($dado['estrutural'], 0, 7) == 1231112){$contacontabilx = 123110499;}
                        elseif(substr($dado['estrutural'], 0, 7) == 1232106){$contacontabilx = 123210601;}
                        elseif(substr($dado['estrutural'], 0, 7) == 2114298){$contacontabilx = 211429900;}
                        elseif(substr($dado['estrutural'], 0, 7) == 2371103){$contacontabilx = 237110300;}
                        elseif(substr($dado['estrutural'], 0, 7) == 3199101){$contacontabilx = 319910100;}
                        elseif(substr($dado['estrutural'], 0, 7) == 3411303){$contacontabilx = 341130200;}
                        elseif(substr($dado['estrutural'], 0, 7) == 3510201){$contacontabilx = 351120100;}
                        elseif(substr($dado['estrutural'], 0, 7) == 3511202){$contacontabilx = 351120200;}
                        elseif(substr($dado['estrutural'], 0, 7) == 4451101){$contacontabilx = 445110000;}
                        elseif(substr($dado['estrutural'], 0, 7) == 8211200){$contacontabilx = 821120100;}
                        elseif(substr($dado['estrutural'], 0, 7) == 8211208){$contacontabilx = 821120200;}
                        elseif(substr($dado['estrutural'], 0, 7) == 8211300){$contacontabilx = 821130100;}
                        elseif(substr($dado['estrutural'], 0, 7) == 8211302){$contacontabilx = 821130200;}
                        elseif(substr($dado['estrutural'], 0, 7) == 8211308){$contacontabilx = 821130200;}
                        elseif(substr($dado['estrutural'], 0, 7) == 8211408){$contacontabilx = 821140100;}
                        elseif(substr($dado['estrutural'], 0, 7) == 1232101){$contacontabilx = 123210198;}
                        elseif(substr($dado['estrutural'], 0, 7) == 1232102){$contacontabilx = 123210198;}
                        elseif(substr($dado['estrutural'], 0, 7) == 1232103){$contacontabilx = 123210198;}
                        elseif(substr($dado['estrutural'], 0, 7) == 1212104){$contacontabilx = 121210499;}
                        elseif(substr($dado['estrutural'], 0, 7) == 7211101){$contacontabilx = 721110000;}
                        elseif(substr($dado['estrutural'], 0, 7) == 7211300){$contacontabilx = 721130000;}
                        elseif(substr($dado['estrutural'], 0, 7) == 8910000){$contacontabilx = 891110000;}
                        elseif(substr($dado['estrutural'], 0, 8) == 17181211){$contacontabilx = 17165001;}
                        elseif(substr($dado['estrutural'], 0, 8) == 44905192){$contacontabilx = 44905199;}
                        elseif(substr($dado['estrutural'], 0, 8) == 35511000){$contacontabilx = 355110000;}
                        elseif(substr($dado['estrutural'], 0, 9) == 221410101){$contacontabilx = 221410100;}
                        elseif(substr($dado['estrutural'], 0, 9) == 372110600){$contacontabilx = 372110500;}
                        elseif(substr($dado['estrutural'], 0, 9) == 399911000){$contacontabilx = 399910000;}
                        elseif(substr($dado['estrutural'], 0, 9) == 411120430){$contacontabilx = 411210301;}
                        elseif(substr($dado['estrutural'], 0, 9) == 411210101){$contacontabilx = 411210200;}
                        elseif(substr($dado['estrutural'], 0, 9) == 417610390){$contacontabilx = 452130800;}
                        elseif(substr($dado['estrutural'], 0, 9) == 417220101){$contacontabilx = 452140100;}
                        elseif(substr($dado['estrutural'], 0, 9) == 433110102){$contacontabilx = 433110200;}
                        elseif(substr($dado['estrutural'], 0, 9) == 452140801){$contacontabilx = 452149900;}
                        elseif(substr($dado['estrutural'], 0, 9) == 631708100){$contacontabilx = 631710000;}
                        elseif(substr($dado['estrutural'], 0, 9) == 631901000){$contacontabilx = 631910000;}
                        elseif(substr($dado['estrutural'], 0, 9) == 218819901){$contacontabilx = 218819900;}
                        elseif(substr($dado['estrutural'], 0, 9) == 452131599){$contacontabilx = 452139900;}
                        elseif(substr($dado['estrutural'], 0, 9) == 452139600){$contacontabilx = 452139900;}
                        elseif(substr($dado['estrutural'], 0, 9) == 791000000){$contacontabilx = 791110000;}
                        elseif(substr($dado['estrutural'], 0, 9) == 221420200){$contacontabilx = 221420201;}
                        elseif(substr($dado['estrutural'], 0, 9) == 445110201){$contacontabilx = 445110000;}
                        elseif(substr($dado['estrutural'], 0, 9) == 451020102){$contacontabilx = 451120100;}
                        elseif(substr($dado['estrutural'], 0, 9) == 632910100){$contacontabilx = 632910000;}
                        elseif(substr($dado['estrutural'], 0, 9) == 445219601){$contacontabilx = 445210000;}
                        elseif(substr($dado['estrutural'], 0, 9) == 445219602){$contacontabilx = 445210000;}
                        elseif(substr($dado['estrutural'], 0, 9) == 445219603){$contacontabilx = 445210000;}
                        elseif(substr($dado['estrutural'], 0, 9) == 451020101){$contacontabilx = 451120100;}
                        elseif(substr($dado['estrutural'], 0, 9) == 531200800){$contacontabilx = 531200000;}
                        elseif(substr($dado['estrutural'], 0, 9) == 532200800){$contacontabilx = 532200000;}
                        elseif(substr($dado['estrutural'], 0, 9) == 797110100){$contacontabilx = 791210000;}
                        elseif(substr($dado['estrutural'], 0, 9) == 897110100){$contacontabilx = 891210100;}
                        elseif(substr($dado['estrutural'], 0, 9) == 111110103){$contacontabilx = 111110100;}
                        elseif(substr($dado['estrutural'], 0, 9) == 123810280){$contacontabilx = 123810204;}
                        //elseif(substr($dado['estrutural'], 0, 9) == 124108000){$contacontabilx = 124100000;}
                        elseif(substr($dado['estrutural'], 0, 9) == 124810180){$contacontabilx = 124810100;}
                        elseif(substr($dado['estrutural'], 0, 9) == 218918001){$contacontabilx = 218919800;}
                        elseif(substr($dado['estrutural'], 0, 9) == 236910180){$contacontabilx = 236910000;}
                        elseif(substr($dado['estrutural'], 0, 9) == 311210136){$contacontabilx = 311210133;}
                        elseif(substr($dado['estrutural'], 0, 9) == 433119902){$contacontabilx = 433119900;}
                        elseif(substr($dado['estrutural'], 0, 9) == 451020110){$contacontabilx = 451120100;}
                        elseif(substr($dado['estrutural'], 0, 9) == 451120201){$contacontabilx = 451120100;}
                        elseif(substr($dado['estrutural'], 0, 9) == 324910000){$contacontabilx = 325110000;}
                        elseif(substr($dado['estrutural'], 0, 9) == 891210100){$contacontabilx = 891210300;}
                        elseif(substr($dado['estrutural'], 0, 9) == 228910000){$contacontabilx = 228919800;}
                        elseif(substr($dado['estrutural'], 0, 9) == 233910000){$contacontabilx = 233919900;}
                        elseif(substr($dado['estrutural'], 0, 9) == 112610100){$contacontabilx = 112610300;}
                        elseif(substr($dado['estrutural'], 0, 9) == 112610100){$contacontabilx = 112610300;}
                        elseif(substr($dado['estrutural'], 0, 9) == 113814501){$contacontabilx = 113819900;}
                        elseif(substr($dado['estrutural'], 0, 9) == 113814502){$contacontabilx = 113819900;}
                        elseif(substr($dado['estrutural'], 0, 9) == 121110590){$contacontabilx = 121110502;}
                        elseif(substr($dado['estrutural'], 0, 9) == 121310106){$contacontabilx = 121310102;}
                        elseif(substr($dado['estrutural'], 0, 9) == 312910700){$contacontabilx = 312120100;}
                        elseif(substr($dado['estrutural'], 0, 9) == 332110500){$contacontabilx = 332110100;}
                        elseif(substr($dado['estrutural'], 0, 9) == 333110300){$contacontabilx = 333110101;}
                        elseif(substr($dado['estrutural'], 0, 9) == 352410100){$contacontabilx = 352410000;}
                        elseif(substr($dado['estrutural'], 0, 9) == 442919901){$contacontabilx = 442910000;}
                        elseif(substr($dado['estrutural'], 0, 9) == 371111300){$contacontabilx = 371110500;}
                        elseif(substr($dado['estrutural'], 0, 9) == 433113401){$contacontabilx = 431119900;}
                        elseif(substr($dado['estrutural'], 0, 9) == 111110102){$contacontabilx = 111110100;}
                        elseif(substr($dado['estrutural'], 0, 9) == 451020109){$contacontabilx = 451120100;}
                        elseif(substr($dado['estrutural'], 0, 9) == 312910700){$contacontabilx = 312910200;}
                        elseif(substr($dado['estrutural'], 0, 9) == 332110500){$contacontabilx = 332110100;}
                        elseif(substr($dado['estrutural'], 0, 9) == 445110202){$contacontabilx = 445110000;}
                        elseif(substr($dado['estrutural'], 0, 9) == 451020104){$contacontabilx = 451120100;}
                        elseif(substr($dado['estrutural'], 0, 9) == 452130701){$contacontabilx = 452130700;}
                        elseif(substr($dado['estrutural'], 0, 9) == 452130701){$contacontabilx = 452130700;}
                        elseif(substr($dado['estrutural'], 0, 9) == 452130701){$contacontabilx = 452130700;}
                        elseif(substr($dado['estrutural'], 0, 9) == 452130701){$contacontabilx = 452130700;}
                        elseif(substr($dado['estrutural'], 0, 9) == 452140601){$contacontabilx = 452140600;}
                        elseif(substr($dado['estrutural'], 0, 9) == 113210000){$contacontabilx = 113219900;}
                        elseif(substr($dado['estrutural'], 0, 9) == 211410100){$contacontabilx = 211410101;}
                        elseif(substr($dado['estrutural'], 0, 9) == 311210100){$contacontabilx = 311210101;}
                        elseif(substr($dado['estrutural'], 0, 9) == 211430601){$contacontabilx = 211430500;}
                        //elseif(substr($dado['estrutural'], 0, 9) == 211430101){$contacontabilx = 211430100;}
                        elseif(substr($dado['estrutural'], 0, 9) == 223130200){$contacontabilx = 223130000;}
                        elseif(substr($dado['estrutural'], 0, 9) == 521210000){$contacontabilx = 521210100;}
                        elseif(substr($dado['estrutural'], 0, 9) == 451020114){$contacontabilx = 521210100;}
                        elseif(substr($dado['estrutural'], 0, 9) == 452131501){$contacontabilx = 452130800;}
                        elseif(substr($dado['estrutural'], 0, 9) == 341119800){$contacontabilx = 341119900;}
                        elseif(substr($dado['estrutural'], 0, 9) == 451020112){$contacontabilx = 451120100;}
                        elseif(substr($dado['estrutural'], 0, 9) == 228810119){$contacontabilx = 228810101;}
                        elseif(substr($dado['estrutural'], 0, 9) == 522130202){$contacontabilx = 522130200;}
                        elseif(substr($dado['estrutural'], 0, 9) == 371210300){$contacontabilx = 371210200;}
                        elseif(substr($dado['estrutural'], 0, 9) == 311210100){$contacontabilx = 311210101;}
                        elseif(substr($dado['estrutural'], 0, 9) == 112530000){$contacontabilx = 112530101;}
                        elseif(substr($dado['estrutural'], 0, 9) == 361719801){$contacontabilx = 361750500;}
                        elseif(substr($dado['estrutural'], 0, 9) == 451120901){$contacontabilx = 451120900;}
                        elseif(substr($dado['estrutural'], 0, 9) == 451020107){$contacontabilx = 451120100;}
                        elseif(substr($dado['estrutural'], 0, 9) == 464010100){$contacontabilx = 464120000;}
                        elseif(substr($dado['estrutural'], 0, 9) == 491010100){$contacontabilx = 491110100;}
                        elseif(substr($dado['estrutural'], 0, 9) == 522130102){$contacontabilx = 522130200;}
                        elseif(substr($dado['estrutural'], 0, 9) == 451020106){$contacontabilx = 451120100;}
                        elseif(substr($dado['estrutural'], 0, 9) == 311210100){$contacontabilx = 311210101;}
                        elseif(substr($dado['estrutural'], 0, 9) == 218810303){$contacontabilx = 218810301;}
                        elseif(substr($dado['estrutural'], 0, 9) == 421120190){$contacontabilx = 421110202;}
                        elseif(substr($dado['estrutural'], 0, 9) == 365010700){$contacontabilx = 365110700;}
                        elseif(substr($dado['estrutural'], 0, 9) == 124108000){$contacontabilx = 124110100;}
                        elseif(substr($dado['estrutural'], 0, 9) == 311210100){$contacontabilx = 311210101;}
                        elseif(substr($dado['estrutural'], 0, 9) == 311210100){$contacontabilx = 311210101;}
                        elseif(substr($dado['estrutural'], 0, 9) == 124100000){$contacontabilx = 124110100;}
                        elseif(substr($dado['estrutural'], 0, 9) == 321110100){$contacontabilx = 321110101;}
                        elseif(substr($dado['estrutural'], 0, 9) == 631700000){$contacontabilx = 631710000;}
                        elseif(substr($dado['estrutural'], 0, 9) == 821110000){$contacontabilx = 821110100;}
                        elseif(substr($dado['estrutural'], 0, 9) == 821120000){$contacontabilx = 821120100;}
                        elseif(substr($dado['estrutural'], 0, 9) == 821130000){$contacontabilx = 821130100;}
                        elseif(substr($dado['estrutural'], 0, 9) == 821140000){$contacontabilx = 821140100;}
                        elseif(substr($dado['estrutural'], 0, 9) == 211410901){$contacontabilx = 211410900;}
                        elseif(substr($dado['estrutural'], 0, 9) == 355010000){$contacontabilx = 355110000;}
                        elseif(substr($dado['estrutural'], 0, 9) == 211430100){$contacontabilx = 211430101;}
                        elseif(substr($dado['estrutural'], 0, 9) == 399919001){$contacontabilx = 399910000;}
                        elseif(substr($dado['estrutural'], 0, 9) == 451020103){$contacontabilx = 451000000;}
                        elseif(substr($dado['estrutural'], 0, 9) == 351120990){$contacontabilx = 351120900;}
                        elseif(substr($dado['estrutural'], 0, 9) == 451120202){$contacontabilx = 451120200;}
                        elseif(substr($dado['estrutural'], 0, 9) == 123810204){$contacontabilx = 123810299;}
                        elseif(substr($dado['estrutural'], 0, 9) == 123810280){$contacontabilx = 123810299;}
                        elseif(substr($dado['estrutural'], 0, 9) == 331210900){$contacontabilx = 331219900;}
                        elseif(substr($dado['estrutural'], 0, 9) == 821140299){$contacontabilx = 821140200;}
                        elseif(substr($dado['estrutural'], 0, 9) == 112330900){$contacontabilx = 112339900;}
                        elseif(substr($dado['estrutural'], 0, 9) == 218110100){$contacontabilx = 218110000;}
                        elseif(substr($dado['estrutural'], 0, 9) == 342410000){$contacontabilx = 342419900;}
                        elseif(substr($dado['estrutural'], 0, 9) == 442411700){$contacontabilx = 442411600;}
                        elseif(substr($dado['estrutural'], 0, 9) == 452130501){$contacontabilx = 452130700;}
                        elseif(substr($dado['estrutural'], 0, 9) == 522130104){$contacontabilx = 522130300;}
                        elseif(substr($dado['estrutural'], 0, 9) == 218850301){$contacontabilx = 218850300;}
                        elseif(substr($dado['estrutural'], 0, 9) == 821140698){$contacontabilx = 821149900;}
                        elseif(substr($dado['estrutural'], 0, 9) == 218819901){$contacontabilx = 218810199;}
                        elseif(substr($dado['estrutural'], 0, 15) == 111110602450100){$contacontabilx = 111110602;}
                        elseif(substr($dado['estrutural'], 0, 15) == 111110602460100){$contacontabilx = 111110602;}
                        elseif(substr($dado['estrutural'], 0, 15) == 111110602470100){$contacontabilx = 111110602;}
                        elseif(substr($dado['estrutural'], 0, 15) == 111110603450100){$contacontabilx = 111110603;}
                        elseif(substr($dado['estrutural'], 0, 15) == 111110603460100){$contacontabilx = 111110603;}
                        elseif(substr($dado['estrutural'], 0, 15) == 111110603460200){$contacontabilx = 111110603;}
                        elseif(substr($dado['estrutural'], 0, 15) == 111110603470200){$contacontabilx = 111110603;}
                        elseif(substr($dado['estrutural'], 0, 15) == 111110603480100){$contacontabilx = 111110603;}
                        elseif(substr($dado['estrutural'], 0, 15) == 111110604010000){$contacontabilx = 111110604;}
                        elseif(substr($dado['estrutural'], 0, 15) == 111111903016000){$contacontabilx = 111115300;}
                        elseif(substr($dado['estrutural'], 0, 15) == 111111903017000){$contacontabilx = 111115300;}
                        elseif(substr($dado['estrutural'], 0, 15) == 111111903018000){$contacontabilx = 111115300;}
                        elseif(substr($dado['estrutural'], 0, 15) == 111111922020000){$contacontabilx = 111115300;}
                        elseif(substr($dado['estrutural'], 0, 15) == 111112015052000){$contacontabilx = 111115300;}
                        elseif(substr($dado['estrutural'], 0, 15) == 111115002023547){$contacontabilx = 111115300;}
                        elseif(substr($dado['estrutural'], 0, 15) == 111115002023553){$contacontabilx = 111115300;}
                        elseif(substr($dado['estrutural'], 0, 15) == 111115002023554){$contacontabilx = 111115300;}
                        elseif(substr($dado['estrutural'], 0, 15) == 111115002032200){$contacontabilx = 111115300;}
                        elseif(substr($dado['estrutural'], 0, 15) == 111115003028500){$contacontabilx = 111115300;}
                        elseif(substr($dado['estrutural'], 0, 15) == 111115003028600){$contacontabilx = 111115300;}
                        elseif(substr($dado['estrutural'], 0, 15) == 111115003043107){$contacontabilx = 111115100;}
                        elseif(substr($dado['estrutural'], 0, 15) == 111115003043117){$contacontabilx = 111115300;}
                        elseif(substr($dado['estrutural'], 0, 15) == 111115003043118){$contacontabilx = 111115300;}
                        elseif(substr($dado['estrutural'], 0, 15) == 111115003043119){$contacontabilx = 111115300;}
                        elseif(substr($dado['estrutural'], 0, 15) == 111115003043122){$contacontabilx = 111115300;}
                        elseif(substr($dado['estrutural'], 0, 15) == 111115003043124){$contacontabilx = 111115300;}
                        elseif(substr($dado['estrutural'], 0, 15) == 111115003043125){$contacontabilx = 111115300;}
                        elseif(substr($dado['estrutural'], 0, 15) == 111115003043129){$contacontabilx = 111115300;}
                        elseif(substr($dado['estrutural'], 0, 15) == 111115003043178){$contacontabilx = 111115300;}
                        elseif(substr($dado['estrutural'], 0, 15) == 111115003050200){$contacontabilx = 111115300;}
                        elseif(substr($dado['estrutural'], 0, 15) == 111115003050300){$contacontabilx = 111115300;}
                        elseif(substr($dado['estrutural'], 0, 15) == 111115003150700){$contacontabilx = 111115300;}
                        elseif(substr($dado['estrutural'], 0, 15) == 111115003150800){$contacontabilx = 111115300;}
                        elseif(substr($dado['estrutural'], 0, 15) == 111115003150900){$contacontabilx = 111115300;}
                        elseif(substr($dado['estrutural'], 0, 15) == 111115003151000){$contacontabilx = 111115300;}
                        elseif(substr($dado['estrutural'], 0, 15) == 111115003151100){$contacontabilx = 111115300;}
                        elseif(substr($dado['estrutural'], 0, 15) == 111115003151300){$contacontabilx = 111115300;}
                        elseif(substr($dado['estrutural'], 0, 15) == 111115003151500){$contacontabilx = 111115300;}
                        elseif(substr($dado['estrutural'], 0, 15) == 111115003151600){$contacontabilx = 111115300;}
                        elseif(substr($dado['estrutural'], 0, 15) == 111115003151800){$contacontabilx = 111115300;}
                        elseif(substr($dado['estrutural'], 0, 15) == 111115003152000){$contacontabilx = 111115300;}
                        elseif(substr($dado['estrutural'], 0, 15) == 111115003152300){$contacontabilx = 111115300;}
                        elseif(substr($dado['estrutural'], 0, 15) == 111115003152500){$contacontabilx = 111115300;}
                        elseif(substr($dado['estrutural'], 0, 15) == 111115003152600){$contacontabilx = 111115300;}
                        elseif(substr($dado['estrutural'], 0, 15) == 111115003152700){$contacontabilx = 111115300;}
                        elseif(substr($dado['estrutural'], 0, 15) == 111115003152900){$contacontabilx = 111115300;}
                        elseif(substr($dado['estrutural'], 0, 15) == 111115003153100){$contacontabilx = 111115300;}
                        elseif(substr($dado['estrutural'], 0, 15) == 111115003153200){$contacontabilx = 111115300;}
                        elseif(substr($dado['estrutural'], 0, 15) == 111115003153500){$contacontabilx = 111115300;}
                        elseif(substr($dado['estrutural'], 0, 15) == 111115003153700){$contacontabilx = 111115300;}
                        elseif(substr($dado['estrutural'], 0, 15) == 111115003153800){$contacontabilx = 111115300;}
                        elseif(substr($dado['estrutural'], 0, 15) == 111115003153900){$contacontabilx = 111115300;}
                        elseif(substr($dado['estrutural'], 0, 15) == 111115003154100){$contacontabilx = 111115300;}
                        elseif(substr($dado['estrutural'], 0, 15) == 111115003154200){$contacontabilx = 111115300;}
                        elseif(substr($dado['estrutural'], 0, 15) == 111115003154300){$contacontabilx = 111115300;}
                        elseif(substr($dado['estrutural'], 0, 15) == 113620201010000){$contacontabilx = 113620201;}
                        elseif(substr($dado['estrutural'], 0, 15) == 113620201020000){$contacontabilx = 113620201;}
                        elseif(substr($dado['estrutural'], 0, 15) == 121120604010000){$contacontabilx = 121120604;}
                        elseif(substr($dado['estrutural'], 0, 15) == 121120604020000){$contacontabilx = 121120604;}
                        elseif(substr($dado['estrutural'], 0, 15) == 123110202000000){$contacontabilx = 123110202;}
                        elseif(substr($dado['estrutural'], 0, 15) == 211110101000000){$contacontabilx = 211110199;}
                        elseif(substr($dado['estrutural'], 0, 15) == 211110102000000){$contacontabilx = 211110199;}
                        elseif(substr($dado['estrutural'], 0, 15) == 213110101000000){$contacontabilx = 213110101;}                        
                        else($contacontabilx = substr($dado['estrutural'], 0, 9));
                    }

                    if($this->instit == 80){
                     if(substr($dado['estrutural'], 0, 9) == 123810280){$contacontabilx = 123810299;}
                    }

                    
                    //if($contacontabilx != 111111900){continue;}

                    if($contacontabilx == 511000000){continue;}
                    if($this->instit == 80 && $contacontabilx == 233919900){continue;}
                    
                    

                    $csf = $this->buscaCodigoSuperavit($dado['estrutural'], $this->iAnoUsu);
                    if(substr($dado['estrutural'], 0, 1) == 3 || substr($dado['estrutural'], 0, 1) == 4 ||substr($dado['estrutural'], 0, 1) == 7 || substr($dado['estrutural'], 0, 1) == 8){
                        $csf = null;
                    }
                    if($csf == "P"){
                        $csf = "2";
                    }elseif($csf == "F"){
                        $csf = "1";
                    }else{
                        $csf = null;
                    }

                    

                $tem = 0;
                foreach ($novotudao as $xbalancete) {
                    if (isset($xbalancete['c61_reduz']) && $xbalancete['c61_reduz'] === $dado["reduzido"]) {                        
                        if(($xbalancete["saldo_anterior"] + $xbalancete["saldo_anterior_debito"] + $xbalancete["saldo_anterior_credito"] + $xbalancete["saldo_final"]) != 0 ){
                            $tem++;
                            
                            
                            for ($i=0; $i < 4; $i++) { 
                                if($i == 0){
                                    //SALDO ANTERIOR
                                    $xvalor = $xbalancete["saldo_anterior"];
                                    $xtipo = 1;
                                    
                                    if((empty($xbalancete["sinal_anterior"]) || trim($xbalancete["sinal_anterior"]) == "") && $xvalor == 0){
                                        if(substr($xbalancete["estrutural"], 0, 1) == 1){
                                            $xnatureza = "D";
                                        }elseif(substr($xbalancete["estrutural"], 0, 1) == 2){
                                            $xnatureza = "C";
                                        }
                                    }else{
                                        $xnatureza = $xbalancete["sinal_anterior"];    
                                    }
                                }elseif($i == 1){
                                    //SALDO ANTERIOR DÉBITO
                                    $xvalor = $xbalancete["saldo_anterior_debito"];
                                    $xtipo = 2;
                                    $xnatureza = "D";
                                }elseif($i == 2){
                                    //SALDO ANTERIOR CRÉDITO
                                    $xvalor = $xbalancete["saldo_anterior_credito"];
                                    $xtipo = 2;
                                    $xnatureza = "C";
                                }else{
                                    //SALDO FINAL
                                    $xvalor = $xbalancete["saldo_final"];
                                    $xtipo = 3;
                                    if((empty($xbalancete["saldo_final"]) || trim($xbalancete["saldo_final"]) == "") && $xvalor == 0){
                                        if(substr($xbalancete["estrutural"], 0, 1) == 1){
                                            $xnatureza = "D";
                                        }elseif(substr($xbalancete["estrutural"], 0, 1) == 2){
                                            $xnatureza = "C";
                                        }
                                    }else{
                                        $xnatureza = $xbalancete["sinal_final"];    
                                    }
                                }
                                
                                
                                $cfr = $this->buscaFonteRecurso($dado["reduzido"]);
                                //if($arquivosdo == "13"){
                                    if(substr($contacontabilx, 0, 4) != "1111"){
                                        $novafonte2 = null;
                                    }
                                    if($contacontabilx == "111111900"){
                                        //$novafonte2 = 1501;
                                    }
                                    if($this->instit == 80 && substr($contacontabilx, 0, 7) == 1111101){
                                        $novafonte2 = 1501;
                                    }

                                    if($dado["estrutural"] == 111111915001100){
                                        $novafonte2 = 1500;
                                    }

                                    if($dado["estrutural"] == 111111915013000){
                                        $novafonte2 = 1540;
                                    }
                                    
                                //}

                                /*if($ix == 841){
                                    $this->testa($dado);
                                    var_dump($novafonte);
                                    var_dump($novafonte2);
                                    die("Confere II");
                                }*/

                                $oConta = (object)[
                                    'Identificador' => $ix, //$dado["reduzido"],
                                    //'Identificador' => $dado["reduzido"],
                                    'CodigoUnidadeGestora' => $this->sCodigoTribunal,
                                    'Exercicio' => $this->iAnoUsu,
                                    'Competencia' => $this->competencia,
                                    'ContaContabil' => $contacontabilx,
                                    'CodigoPoderOrgao' => $cpo,
                                    'CodigoSuperavitFinanceiro' => ($csf) ? $csf : null,
                                    'DividaConsolidada' => null,
                                    //'FonteDestinacaoRecursos' => ($arquivosdo == "13") ? $novafonte2 : $cfr, //$cfr, //$novafonte2, //$this->buscaRec($dado['reduzido'],$this->iAnoUsu),
                                    'FonteDestinacaoRecursos' => $novafonte2,
                                    'CodigoAcompanhamentoExecucaoOrcamentaria' => $caeo,
                                    'NaturezaReceita' => null,
                                    'NaturezaDespesa' => null,                        
                                    'Funcao' => null,
                                    'SubFuncao' => null,
                                    'AnoInscricaoRestosPagar' => null,
                                    'Valor' => $xvalor, //$dado["novovalorx"], //$novovalor,
                                    'Tipo' => $xtipo,//$dado['tipo_movimento'],
                                    'NaturezaSaldo' => $xnatureza, //$dado["tipo_contax"], //$tipoconta,//$dado['tipo_abertura'],                        
                                    'CodigoDetalhamentoSubfuncao' => null,
                                    'Deducao' => $deducao,
                                ];
                                $ix++;
                                $obj->Balancetes[] = (object)['Balancete' => $oConta];
                            }
                            //die("conrre");
                        }
                    }
                }
                
                if($tem == 0){continue;}
                    
                }
        }//foreach
        //$this->testa($obj); die("Confere");
        
        $this->aDados = $obj;
    }//função





}//classe

<?php

namespace ECidade\Financeiro\Contabilidade\Exportacao\Sigfis\Arquivos\v2024;


use db_utils;
use stdClass;

require_once modification("libs/db_liborcamento.php");
require_once modification("libs/ReceitaSaldo.php");
require_once modification("model/contabilidade/arquivos/sigfis/SigfisVinculoRecurso.model.php");


class ArquivoPrevisaoDeReceita extends ArquivoBase
{
    protected $sNomeArquivo  = 'PrevisaoDeReceita';

    public function testa($var){
        echo "<pre>";
        print_r($var);
        echo "</pre>";
    }

    public function gerarDados()
    {   
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
            6041 => 1600,
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
            1660 => 164
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
            1740 => 1721,
            6002 => 1600,
            6021 => 1602,
            6212 => 1621,
            6597 => 1659,
            6051 => 1600
        );
        

        $sWhere          = "o70_instit = ".db_getsession("DB_instit");
        //$rsReceitaSaldo = db_receitasaldo(11, 1, 2, true, $sWhere, $this->iAnoUsu, $this->dtDataInicial, $this->dtDataFinal);
        
        $rsReceitaSaldo = ReceitaSaldo(11, 1, "1", true, " o70_instit in (".db_getsession("DB_instit").")", $this->iAnoUsu, $this->dtDataInicial, $this->dtDataFinal, false, ' * ', true, "0", "");
                            
        $aReceitas = db_utils::getColectionByRecord($rsReceitaSaldo);
        //$this->testa($aReceitas); die("Confere");

        $aReceitas = array_filter($aReceitas, function($item) {
            if (is_object($item)) {
                return isset($item->o70_codrec, $item->saldo_inicial) && !empty($item->o70_codrec) && ($item->saldo_inicial > 0 || $item->saldo_inicial < 0);
            }            
            return isset($item['o70_codrec'], $item['saldo_inicial']) && !empty($item['o70_codrec']) && ($item['saldo_inicial'] > 0 || $item['saldo_inicial'] < 0);
        });

        $ix = 1;
        $aReceitas2 = array();

        

        foreach ($aReceitas as $item) {
            $xoDaoOrcFontes = db_utils::getDao('orcfontes');
            $xsWhereFontes  = "o57_anousu = {$this->iAnoUsu} and o57_fonte = '$item->o57_fonte'";
            $xsSqlOrcFontes = $xoDaoOrcFontes->sql_query_file(null, null, "*, ( select count(*) from orcreceita where o57_codfon = o70_codfon and o57_anousu = o70_anousu ) as quant_rec ", null, $xsWhereFontes);
            $xrsOrcFontes   = $xoDaoOrcFontes->sql_record($xsSqlOrcFontes);
            $xsCodFon       = db_utils::fieldsmemory($xrsOrcFontes, 0)->o57_codfon;
            $xVinculo = \SigfisVinculoReceita::getVinculoReceita($xsCodFon);
            $xfonte = $xVinculo->receitatce;

            if($xfonte == "13031101"){
                $xfonte = "11130311";
            }

            if(db_getsession('DB_instit') == 50){
                if($fontes50[$item->o70_codigo]){
                    $item->o70_codigo = $fontes50[$item->o70_codigo];
                }
            }elseif(db_getsession('DB_instit') == 65){
                if($fontes65[$item->o70_codigo]){
                    $item->o70_codigo = $fontes65[$item->o70_codigo];
                }
            }elseif(db_getsession('DB_instit') == 96){
                if($fontes96[$item->o70_codigo]){
                    $item->o70_codigo = $fontes96[$item->o70_codigo];
                }
            }else{
                if($fontes0[$item->o70_codigo]){
                    $item->o70_codigo = $fontes0[$item->o70_codigo];
                }
            }

            $fonte = (substr($item->o57_fonte, 0, 1) == 9) ? substr($item->o57_fonte, 1, 9) : substr($item->o57_fonte, 1, 8);
            //$chave = $fonte . '-' . $xfonte;
            $chave = $xfonte . '-' . $item->o70_codigo;
            //var_dump($xfonte, $xsCodFon); echo "<br>";

            if (isset($aReceitas2[$chave])) {
                $aReceitas2[$chave]->saldo_inicial += $item->saldo_inicial;
            } else {
                $aReceitas2[$chave] = $item;
            }
            $aReceitas2[$chave]->fontex = $xfonte; 
            $aReceitas2[$chave]->codex = $item->o70_codigo; 
        }
        //$this->testa($aReceitas2); die("Confere");
        foreach ($aReceitas2 as $receita) {
            /*
            $oDaoOrcFontes = db_utils::getDao('orcfontes');
            $sWhereFontes  = "o57_anousu = {$this->iAnoUsu} and o57_fonte = '$receita->o57_fonte'";
            $sSqlOrcFontes = $oDaoOrcFontes->sql_query_file(null, null, "*, ( select count(*) from orcreceita where o57_codfon = o70_codfon and o57_anousu = o70_anousu ) as quant_rec ", null, $sWhereFontes);
            $rsOrcFontes   = $oDaoOrcFontes->sql_record($sSqlOrcFontes);
            $sCodFon       = db_utils::fieldsmemory($rsOrcFontes, 0)->o57_codfon;
            $oVinculo = \SigfisVinculoReceita::getVinculoReceita($sCodFon);
            
            $fontex = $oVinculo->receitatce;
            */
            $fontex = $receita->fontex;
            $oDados      = new stdClass();
            $oDadosPrevisaoReceita = new stdClass();

            $oDadosPrevisaoReceita->Identificador = $ix;
            $oDadosPrevisaoReceita->CodigoUnidadeGestora = $this->sCodigoTribunal;
            $oDadosPrevisaoReceita->Ano = $this->iAnoUsu;
            
            $oDadosPrevisaoReceita->CodigoItemReceita = $fontex;
            //$oDadosPrevisaoReceita->FonteRecursos = $receita->o70_codigo;
            $oDadosPrevisaoReceita->FonteRecursos = $receita->codex;
                //$oDadosPrevisaoReceita->CodigoItemReceita = (substr($receita->o57_fonte, 0, 1) == 9) ? substr($receita->o57_fonte, 1, 9) : substr($receita->o57_fonte, 1, 8);
                
            
            $oDadosPrevisaoReceita->ValorPrevisaoReceita = (substr($oDadosPrevisaoReceita->CodigoItemReceita, 0, 1) == 9) ? abs(number_format($receita->saldo_inicial, 2, '.','')) :  number_format($receita->saldo_inicial, 2, '.','');
            $oDadosPrevisaoReceita->Deducao = (substr($oDadosPrevisaoReceita->CodigoItemReceita, 0, 1) == 9) ? 3 : 1;
            $ix++;

            $previsaoReceitas[] = (object) ['PrevisaoDeReceita' => $oDadosPrevisaoReceita];            
            
        }



        /*
        
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
          6041 => 1600,
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
            1660 => 164
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
              117 => 1701
        );

        $guardacodigo = array();
        
        foreach ($aReceitas as $iInd => $aReceita) {
            
            //if(substr($aReceita->o57_fonte, 0, 5) != "91728"){continue;}

            $oDaoOrcFontes = db_utils::getDao('orcfontes');
            $sWhereFontes  = "o57_anousu = {$this->iAnoUsu} and o57_fonte = '$aReceita->o57_fonte'";
            $sSqlOrcFontes = $oDaoOrcFontes->sql_query_file(null, null, "*, ( select count(*) from orcreceita where o57_codfon = o70_codfon and o57_anousu = o70_anousu ) as quant_rec ", null, $sWhereFontes);

            $rsOrcFontes   = $oDaoOrcFontes->sql_record($sSqlOrcFontes);

            

                $sCodFon       = db_utils::fieldsmemory($rsOrcFontes, 0)->o57_codfon;
                $sEstrut       = db_utils::fieldsmemory($rsOrcFontes, 0)->o57_fonte;
                $iQuantReceita = db_utils::fieldsmemory($rsOrcFontes, 0)->quant_rec;

                if ( $iQuantReceita == 0 ) {
                    continue;
                }                
                    
                $oVinculo = \SigfisVinculoReceita::getVinculoReceita($sCodFon);

                $sEstrut = $oVinculo->receitatce;
                
                //var_dump($aReceita->saldo_inicial);
                //var_dump($aReceita->o57_fonte);
                //var_dump($sCodFon); var_dump($sEstrut); echo "<br>";

                if(db_getsession("DB_instit") == 85){
                    $guardacodigo[$sEstrut] = $aReceita->o70_codigo;
                }
                

                if (!isset($aReceitaSoma[$sEstrut])) {
                    $aReceitaSoma[$sEstrut] = $aReceita->saldo_inicial ;
                } else {
                    $aReceitaSoma[$sEstrut] += $aReceita->saldo_inicial ;
                }

            
        }//foreach

        if (count($aReceitaSoma) > 0) {

            $xi = 1;
            $guardaconta = "";
            $guardavalor = 0;
            $guardatudo = array();

            //$this->testa($aReceitaSoma); die("Confere");    
            
            $RemessaPrevisaoDeReceita = new stdClass();
            $previsaoReceitas = [];
            foreach ($aReceitaSoma as $sFonte => $nValor) {

                $oDados      = new stdClass();
                $oDadosPrevisaoReceita = new stdClass();

                $oDadosPrevisaoReceita->Identificador = $xi;
                $oDadosPrevisaoReceita->CodigoUnidadeGestora = $this->sCodigoTribunal;
                $oDadosPrevisaoReceita->Ano = $this->iAnoUsu;
                $oDadosPrevisaoReceita->CodigoItemReceita = (substr($sFonte, 0, 1) == 9) ? substr($sFonte, 0, 9) : substr($sFonte, 0, 8);
                
                if(db_getsession("DB_instit") == 85){
                    $oDadosPrevisaoReceita->FonteRecursos = $guardacodigo[$oDadosPrevisaoReceita->CodigoItemReceita];
                }else{
                    $oDadosPrevisaoReceita->FonteRecursos = $aReceita->o70_codigo;
                }

                if(db_getsession('DB_instit') == 50){
                    if($fontes50[$oDadosPrevisaoReceita->FonteRecursos]){
                        $oDadosPrevisaoReceita->FonteRecursos = $fontes50[$oDadosPrevisaoReceita->FonteRecursos];
                    }
                }elseif(db_getsession('DB_instit') == 65){
                    if($fontes65[$oDadosPrevisaoReceita->FonteRecursos]){
                        $oDadosPrevisaoReceita->FonteRecursos = $fontes65[$oDadosPrevisaoReceita->FonteRecursos];
                    }
                }elseif(db_getsession('DB_instit') == 96){
                    if($fontes96[$oDadosPrevisaoReceita->FonteRecursos]){
                        $oDadosPrevisaoReceita->FonteRecursos = $fontes96[$oDadosPrevisaoReceita->FonteRecursos];
                    }
                }else{
                    if($fontes0[$oDadosPrevisaoReceita->FonteRecursos]){
                        $oDadosPrevisaoReceita->FonteRecursos = $fontes0[$oDadosPrevisaoReceita->FonteRecursos];
                    }
                }


                $oDadosPrevisaoReceita->ValorPrevisaoReceita = (substr($oDadosPrevisaoReceita->CodigoItemReceita, 0, 1) == 9) ? abs(number_format($nValor, 2, '.','')) :  number_format($nValor, 2, '.','');
                $oDadosPrevisaoReceita->Deducao = (substr($oDadosPrevisaoReceita->CodigoItemReceita, 0, 1) == 9) ? 3 : 1;

                $guardaconta = substr($sFonte, 0, 8);
                $xi++;
                $previsaoReceitas[] = (object) ['PrevisaoDeReceita' => $oDadosPrevisaoReceita];
                            
            }

        
        }
        */





        /*
        $oDaoReceita = new \cl_orcreceita;

        $campos = "sum(receita.o70_valor) as valor";
        $where = " where substr(plrec.conta,1,8) = substr(planoreceita.conta,1,8)";
        $where .= " and receita.o70_anousu = {$this->iAnoUsu}";
        $where .= " and receita.o70_instit = {$this->instit}";
        $where .= " and fontrec.codigo_siconfi = fonterecurso.codigo_siconfi";

        $where1 = $where . " and substr(o57_fonte,1,1) = '4'";
        $where2 = $where . " and substr(o57_fonte,1,1) = '9' and substr(o57_fonte,1,3) <> '917'";
        $where3 = $where . " and substr(o57_fonte,1,3) = '917'";

        $groupBy = "group by fontrec.codigo_siconfi,plrec.conta";

        $sqlSubQueryReceitaSigfis = "
              select $campos
                  from orcreceita receita
            inner join orcfontes fontes on fontes.o57_codfon = receita.o70_codfon
                                       and fontes.o57_anousu = receita.o70_anousu
            inner join conplanoorcamento orcamento on orcamento.c60_codcon = fontes.o57_codfon
                                                  and orcamento.c60_anousu = fontes.o57_anousu
            inner join orctiporec recurso on recurso.o15_codigo = receita.o70_codigo
            inner join fonterecurso fontrec on fontrec.orctiporec_id = recurso.o15_codigo
                                   and fontrec.exercicio = receita.o70_anousu

        ";
        $sqlSubQueryReceitaSigfis1 = $sqlSubQueryReceitaSigfis.$where1.$groupBy;
        $sqlSubQueryReceitaSigfis2 = $sqlSubQueryReceitaSigfis.$where2.$groupBy;
        $sqlSubQueryReceitaSigfis3 = $sqlSubQueryReceitaSigfis.$where3.$groupBy;

        $campos = "distinct substr(planoreceita.conta,1,8) as codigo_item_receita,o70_anousu,";
        $campos .= "RANK() OVER (ORDER BY substr(planoreceita.conta,1,8),codigo_siconfi) AS grupo_receita,";
        $campos .= "substr(codigo_siconfi,1,4) as codigo_siconfi,";
        $campos .= "($sqlSubQueryReceitaSigfis1) as valor_deducao1,";
        $campos .= "($sqlSubQueryReceitaSigfis2) as valor_deducao2,";
        $campos .= "($sqlSubQueryReceitaSigfis3) as valor_deducao3,";
        $campos .= "case when substr(o57_fonte,1,1) = '4' then 1
                        when substr(o57_fonte,1,1) = '9' and substr(o57_fonte,1,3) <> '917' then 2
                         when  substr(o57_fonte,1,3) = '917' then 3 else 0 end as deducao";

        $where  = "o70_instit = ".$this->instit;
        $where  .= " and o70_anousu = ".$this->iAnoUsu;

        $orderBy = "codigo_item_receita,deducao";


        $sqlReceitaPlano =  $oDaoReceita->sql_query_receita_planoreceita($campos, $orderBy, $where, null);
        $rsReceitaPlano = db_query($sqlReceitaPlano);

        $RemessaPrevisaoDeReceita = new stdClass();
        $previsaoReceitas = [];

        for ($i=0; $i<pg_num_rows($rsReceitaPlano); $i++) {
            $receita = db_utils::fieldsMemory($rsReceitaPlano, $i);

            $oDadosPrevisaoReceita = new stdClass();

            $identificador = substr($receita->o70_anousu, 2, 2);
            $identificador .= date('m', db_getsession('DB_datausu'));
            $identificador .= $receita->grupo_receita;
            $identificador .= $receita->deducao;


            $oDadosPrevisaoReceita->Identificador = $identificador;

            $oDadosPrevisaoReceita->CodigoUnidadeGestora = $this->sCodigoTribunal;
            $oDadosPrevisaoReceita->Ano = $receita->o70_anousu;
            $oDadosPrevisaoReceita->CodigoItemReceita = $receita->codigo_item_receita;
            $oDadosPrevisaoReceita->FonteRecursos = $receita->codigo_siconfi;

            //Tipo 1 nao eh deducao

            if ($receita->deducao == 1) {
                $valor = $receita->valor_deducao1;
            } elseif ($receita->deducao == 2) {
                $valor = abs($receita->valor_deducao2);
            } elseif ($receita->deducao == 3) {
                $valor = abs($receita->valor_deducao3);
            }

            $oDadosPrevisaoReceita->ValorPrevisaoReceita = $valor;
            $oDadosPrevisaoReceita->Deducao = $receita->deducao;

            if ($valor > 0) {
                $previsaoReceitas[] = (object) ['PrevisaoDeReceita' => $oDadosPrevisaoReceita];
            }
        }*/
        $RemessaPrevisaoDeReceita->PrevisaoDeReceitas = $previsaoReceitas;
        $this->aDados = $RemessaPrevisaoDeReceita;
    }
}

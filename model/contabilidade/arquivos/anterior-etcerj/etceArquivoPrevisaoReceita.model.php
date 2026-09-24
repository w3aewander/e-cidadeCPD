<?php
/*
 *     E-cidade Software Publico para Gestao Municipal                
 *  Copyright (C) 2013  DBselller Servicos de Informatica             
 *                            www.dbseller.com.br                     
 *                         e-cidade@dbseller.com.br                   
 *                                                                    
 *  Este programa e software livre; voce pode redistribui-lo e/ou     
 *  modifica-lo sob os termos da Licenca Publica Geral GNU, conforme  
 *  publicada pela Free Software Foundation; tanto a versao 2 da      
 *  Licenca como (a seu criterio) qualquer versao mais nova.          
 *                                                                    
 *  Este programa e distribuido na expectativa de ser util, mas SEM   
 *  QUALQUER GARANTIA; sem mesmo a garantia implicita de              
 *  COMERCIALIZACAO ou de ADEQUACAO A QUALQUER PROPOSITO EM           
 *  PARTICULAR. Consulte a Licenca Publica Geral GNU para obter mais  
 *  detalhes.                                                         
 *                                                                    
 *  Voce deve ter recebido uma copia da Licenca Publica Geral GNU     
 *  junto com este programa; se nao, escreva para a Free Software     
 *  Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA          
 *  02111-1307, USA.                                                  
 *  
 *  Copia da licenca no diretorio licenca/licenca_en.txt 
 *                                licenca/licenca_pt.txt 
 */


require_once  modification("interfaces/iPadArquivoTxtBase.interface.php");
require_once  modification("model/contabilidade/arquivos/sigfis/SigfisArquivoBase.model.php");
require_once  modification("libs/db_liborcamento.php");


class etceArquivoPrevisaoReceita extends SigfisArquivoBase implements iPadArquivoTXTBase {

    protected $iCodigoLayout     = 119;
    protected $sNomeArquivo      = 'PrevRec';

    public function nomexml(){
        return "RemessaPrevisaoDeReceita";
      }
    
      public function abretag(){
        return "PrevisaoDeReceitas";
      }
    
      public function segundatag(){
        return "PrevisaoDeReceita";
      }
    
      public function getNomeElementos(){
        $elementos = array("Identificador", "CodigoUnidadeGestora", "Ano", "CodigoItemReceita", "FonteRecursos", "ValorPrevisaoReceita", "Deducao");
        return $elementos;
      }

      public function testax($var){
        echo "<pre>";
        print_r($var);
        echo "</pre>";
      }

    /**
     * Busca os dados para gerar o Arquivo do Previsao da Receita
     */
    public function gerarDados() {

        /**
         * Busca os dados da db_config
         */
        $oDbConfig       = new db_stdClass();
        $oDadoConfig     = $oDbConfig->getDadosInstit();
        $sWhere          = "o70_instit = ".db_getsession("DB_instit");
        $rsReceitaSaldo = db_receitasaldo(11, 1, 2, true, $sWhere,
            $this->iAnoUso,
            $this->dtDataInicial, $this->dtDataFinal);


        $aReceitas = db_utils::getColectionByRecord($rsReceitaSaldo);
        
        //$this->testax($aReceitas);
        //die("Confere");
        
        
        $aReceitaSoma = array();
        if (empty($this->sCodigoTribunal)) {
            throw new Exception("O código do tribunal deve ser informado para geração do arquivo");
        }

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
        
        //Percorre o array retornado pelo metodo db_receitasaldo        
        foreach ($aReceitas as $iInd => $aReceita) {  
        
            //Como o metodo db_receitasaldo não retorna o campo orcfontes.o57_codfon precisamos fazer uma consulta para descobri-lo             
            $oDaoOrcFontes = db_utils::getDao('orcfontes');
            $sWhereFontes  = "o57_anousu = {$this->iAnoUso} and o57_fonte = '$aReceita->o57_fonte'";
            $sSqlOrcFontes = $oDaoOrcFontes->sql_query_file(null, null, "*, ( select count(*) from orcreceita where o57_codfon = o70_codfon and o57_anousu = o70_anousu ) as quant_rec ", null, $sWhereFontes);
            $rsOrcFontes   = $oDaoOrcFontes->sql_record($sSqlOrcFontes);

            if ($oDaoOrcFontes->numrows == 1) {

                $sCodFon       = db_utils::fieldsmemory($rsOrcFontes, 0)->o57_codfon;
                $sEstrut       = db_utils::fieldsmemory($rsOrcFontes, 0)->o57_fonte;
                $iQuantReceita = db_utils::fieldsmemory($rsOrcFontes, 0)->quant_rec;


                if ( $iQuantReceita == 0 ) {
                    continue;
                }

                
                //Para cada o57_codfon retornado verificamos se este possui vinculo com Recita Sigfis.
                
                $oVinculo = SigfisVinculoReceita::getVinculoReceita($sCodFon);
                if (empty($oVinculo)) {                    

                    $sErroLog  = "Receita {$aReceita->o57_fonte} do ano de {$this->iAnoUso} ";
                    $sErroLog .= "não tem vinculo com Receita Sigfis.\n";
                    $this->addLog($sErroLog);
                    continue;
                }

                $sEstrut = $oVinculo->receitatce;
                
            
                /*if (substr($sEstrut, 0,  1) == '9' ) {
                    $sEstrut = '9' . substr($sEstrut, 2, 12);
                } else {
                    //$sEstrut = substr($sEstrut, 0, 13);
 		             //AlteraÃ§Ã£o - Tirando o primeiro caracter da Fonte e pegando mais um carater
		              $sEstrut = substr($sEstrut, 1, 14);
		              $sEstrut = substr($sEstrut, 0, 13);
                }*/

                if (!isset($aReceitaSoma[$sEstrut])) {
                    $aReceitaSoma[$sEstrut] = $aReceita->saldo_inicial ;
                } else {
                    $aReceitaSoma[$sEstrut] += $aReceita->saldo_inicial ;
                }

            } else {
                $sErroLog  = "Receita {$aReceita->o57_fonte} do ano de {$this->iAnoUso} retornou mais de um registro.($sSqlOrcFontes)\n";
                $this->addLog($sErroLog);
            }
        }//foreach
        
        
        //$this->testax($aReceitaSoma);        
        //die("Confere");

        if (count($aReceitaSoma) > 0) {

            $xi = 1;
            $guardaconta = "";
            $guardavalor = 0;
            $guardatudo = array();
            foreach ($aReceitaSoma as $sFonte => $nValor) {

                $oDados      = new stdClass();

                //$oDados->dt_Ano             = $this->iAnoUso;
                //$oDados->Cd_Unidade         = str_pad($this->sCodigoTribunal, 4, ' ', STR_PAD_LEFT);
                //$oDados->Cd_ItemReceita     = str_pad(substr($sFonte, 0, 13), 13, ' ', STR_PAD_RIGHT);                               
 		        //$oDados->vl_Receita         = str_pad(number_format(abs($nValor), 2, '',''), 16, ' ', STR_PAD_LEFT);
                //$oDados->codigolinha        = 406;

                $oDados->Identificador = $xi;
                $oDados->CodigoUnidadeGestora = $this->sCodigoTribunal;
                $oDados->Ano = $this->iAnoUso;
                $oDados->CodigoItemReceita = (substr($sFonte, 0, 1) == 9) ? substr($sFonte, 0, 9) : substr($sFonte, 0, 8);
                $oDados->FonteRecursos = $aReceita->o70_codigo; //substr($aReceita->o57_fonte,1,4);

                if(db_getsession('DB_instit') == 50){
                    if($fontes50[$oDados->FonteRecursos]){
                        $oDados->FonteRecursos = $fontes50[$oDados->FonteRecursos];
                    }
                }elseif(db_getsession('DB_instit') == 65){
                    if($fontes65[$oDados->FonteRecursos]){
                        $oDados->FonteRecursos = $fontes65[$oDados->FonteRecursos];
                    }
                }elseif(db_getsession('DB_instit') == 96){
                    if($fontes96[$oDados->FonteRecursos]){
                        $oDados->FonteRecursos = $fontes96[$oDados->FonteRecursos];
                    }
                }else{
                    if($fontes0[$oDados->FonteRecursos]){
                        $oDados->FonteRecursos = $fontes0[$oDados->FonteRecursos];
                    }
                }


                $oDados->ValorPrevisaoReceita = (substr($oDados->CodigoItemReceita, 0, 1) == 9) ? abs(number_format($nValor, 2, '.','')) :  number_format($nValor, 2, '.','');
                $oDados->Deducao = (substr($oDados->CodigoItemReceita, 0, 1) == 9) ? 3 : 1;
                

                /*if(substr($sFonte, 0, 8) == $guardaconta){
                    $guardatudo[substr($sFonte, 0, 8)] += $oDados->ValorPrevisaoReceita;
                    $guardaconta = substr($sFonte, 0, 8);
                    continue;
                }*/

                $guardaconta = substr($sFonte, 0, 8);
                $xi++;
                $this->aDados[] = $oDados;
                //echo "<pre>";
                //print_r($oDados);
                //echo "</pre>";
                
            }

            //echo "<pre>";
            //print_r($this->aDados);            
            //echo "</pre>";

            /*$indice = 0;
            foreach ($this->aDados as $linha) {
                if(array_key_exists($linha->CodigoItemReceita, $guardatudo)){                    
                    $this->aDados[$indice]->ValorPrevisaoReceita += $guardatudo["$linha->CodigoItemReceita"];
                    //$this->aDados[$indice]->ValorPrevisaoReceita = 99995;
                }
                $indice++;
            }*/

            //echo "<hr>";
            //echo "<pre>";
            //print_r($this->aDados);
            //print_r($guardatudo);
            //echo "</pre>";
            

            //die("Confere");
            //echo "<pre>";
            //print_r($this->aDados);            
            //echo "</pre>";
            //die("Confere");
        }
    }
}

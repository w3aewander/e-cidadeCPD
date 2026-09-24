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

/**
 *
 * Classe Responsável pela geração dos dados necessários para o arquivo Itens da Receita
 * @author Andrio Costa
 * @package contabilidade
 * @subpackage sigfis
 *
 */
class SigfisArquivoItemReceita extends SigfisArquivoBase implements iPadArquivoTXTBase {

  protected $iCodigoLayout     = 116;
  protected $sNomeArquivo      = 'EspRec';

  /**
   * Busca os dados para gerar o Arquivo do Programa do Orçamento
   */
  public function gerarDados() {

    /**
     * Busca os dados da db_config
     */
    $oDbConfig     = new db_stdClass();
    $oDadoConfig   = $oDbConfig->getDadosInstit();

    $clOrcFontes   = db_utils::getDao('orcfontes');

    $sCampos       = "distinct orcfontes.o57_fonte, orcfontes.o57_descr, orcfontes.o57_anousu, orcfontes.o57_codfon, ";
    $sCampos      .= "case when o70_codrec is not null then '1' else '2' end as reduz ";
    $sOrder        = "orcfontes.o57_fonte";
    $sSqlOrcFontes = $clOrcFontes->sql_query_previsao(null, $this->iAnoUso, $sCampos, $sOrder, " o70_anousu = " . db_getsession("DB_anousu") . " and o70_instit = " . db_getsession("DB_instit"));

    $sSqlOrcFontes = "select substr(o57_fonte,1,9) as o57_fonte, max(o57_descr) as o57_descr, o57_anousu, max(o57_codfon) as o57_codfon, min(reduz) as reduz
      from (".$sSqlOrcFontes.") as x group by substr(o57_fonte,1,9), o57_anousu order by o57_fonte ";

    $sSqlOrcFontes = "select case when substr(o57_fonte,1,1) = '9' then substr(o57_fonte,1,8) else substr(o57_fonte,2,8) end as o57_fonte, o57_descr, o57_anousu, o57_codfon, reduz from ( $sSqlOrcFontes ) as x group by case when substr(o57_fonte,1,1) = '9' then substr(o57_fonte,1,8) else substr(o57_fonte,2,8) end, o57_descr, o57_anousu, o57_codfon, reduz order by case when substr(o57_fonte,1,1) = '9' then substr(o57_fonte,1,8) else substr(o57_fonte,2,8) end";

//    die( $sSqlOrcFontes );
    $rsOrcFontes   = $clOrcFontes->sql_record($sSqlOrcFontes);

    $this->addLog("=====Arquivo: ".$this->getNomeArquivo()." Erros:\n");
    if ($clOrcFontes->numrows > 0) {

      if (empty($this->sCodigoTribunal)) {
        throw new Exception("O código do tribunal deve ser informado para geração do arquivo");
      }

      $lDebug=0;

      for($i = 0; $i < $clOrcFontes->numrows; $i++) {

        $oDadosQuery = new stdClass();
        $oDadosQuery = db_utils::fieldsMemory($rsOrcFontes, $i);
        $oDados      = new stdClass();

        if (substr($oDadosQuery->o57_fonte, 1,  8) == '00000000') continue;

        $oVinculo = SigfisVinculoReceita::getVinculoReceita($oDadosQuery->o57_codfon);
        //        if ($oVinculo = SigfisVinculoReceita::getVinculoReceita($oDadosQuery->o57_codfon)) {

        if (true) {

          $oDados->cd_Unidade           = str_pad($this->sCodigoTribunal,                  4, ' ', STR_PAD_LEFT);
          $oDados->cd_ItemReceitaGestor = str_pad($oDadosQuery->o57_fonte, 8, " ", STR_PAD_LEFT);
//          if(substr($oDadosQuery->o57_fonte, 0,  1) == '9' ) {
//            $oDados->cd_ItemReceitaGestor = str_pad( '9'.substr($oDadosQuery->o57_fonte, 2,  7),  8, " ", STR_PAD_LEFT);
//          }else{
//            $oDados->cd_ItemReceitaGestor = str_pad(substr($oDadosQuery->o57_fonte, 1,  8),  8, " ", STR_PAD_LEFT);
//          }
          $oDados->de_ItemReceita       = str_pad(substr($oDadosQuery->o57_descr, 0, 50), 50, ' ', STR_PAD_RIGHT);

          $aVinculo = array();




/*


          $aVinculo[4111202] 	= 4111201;
          $aVinculo[411120801] 	= 4111208;
          $aVinculo[411120802] 	= 4111208;
          $aVinculo[411120803] 	= 4111208;
          $aVinculo[4111208] 	= 41112;
          $aVinculo[41121] 	= 0;
          $aVinculo[41122] 	= 0;
          $aVinculo[412103] 	= 4121099;
          $aVinculo[4131] 	= 0;
          $aVinculo[411130501] 	= 4111305;
          $aVinculo[411130502] 	= 4111305;
          $aVinculo[411130503] 	= 4111305;
          $aVinculo[41113051] 	= 4111305;
          $aVinculo[4111305] 	= 4111300;
          $aVinculo[4113] 	= 0;
          $aVinculo[41329] 	= 0;
          $aVinculo[4133] 	= 0;
          $aVinculo[41919] 	= 0;
          $aVinculo[423003] 	= 423;
          $aVinculo[4259] 	= 0;
          $aVinculo[472100]	= 4721099;
          $aVinculo[479100]	= 4799099;
          $aVinculo[479120]	= 4799099;
          $aVinculo[4799]	= 4799099;
          $aVinculo[41413] 	= 41913;
          $aVinculo[419123] 	= 41913;




*/





          $aVinculo[111202] 	= 111201;
          $aVinculo[11120801] 	= 111208;
          $aVinculo[11120802] 	= 111208;
          $aVinculo[11120803] 	= 111208;
          $aVinculo[111208] 	= 1112;
          $aVinculo[1121] 	= 0;
          $aVinculo[1122] 	= 0;
          $aVinculo[12103] 	= 121099;
          $aVinculo[131] 	= 0;
          $aVinculo[11130501] 	= 111305;
          $aVinculo[11130502] 	= 111305;
          $aVinculo[11130503] 	= 111305;
          $aVinculo[1113051] 	= 111305;
          $aVinculo[111305] 	= 111300;
          $aVinculo[113] 	= 0;
          $aVinculo[1329] 	= 0;
          $aVinculo[133] 	= 0;
          $aVinculo[1919] 	= 0;
          $aVinculo[23003] 	= 23;
          $aVinculo[259] 	= 0;
          $aVinculo[72100]	= 721099;
          $aVinculo[79100]	= 799099;
          $aVinculo[79120]	= 799099;
          $aVinculo[799]	= 799099;
          $aVinculo[1413] 	= 1913;
          $aVinculo[19123] 	= 1913;

          $aVinculo[9721] 	= 97210102;
          $aVinculo[9112] 	= 9122;
          $aVinculo[9722] 	= 97220104;
          $aVinculo[99] 	= 0;

          $oDados->cd_ItemReceita = substr($oDadosQuery->o57_fonte, 0, 8);

          //echo $oDadosQuery->o57_fonte . "\n";

//          if ($oDadosQuery->o57_fonte == "911212100" and false?$lDebug=1:$lDebug=0);

//          if (substr($oDadosQuery->o57_fonte, 0, 7) == "4111204" or substr($oDadosQuery->o57_fonte, 0, 7) == "4111202") {
//            $oDados->cd_ItemReceita = substr($oDadosQuery->o57_fonte, 1, 8);
//          } else {
//            $oDados->cd_ItemReceita = substr($oDadosQuery->o57_fonte, 1, 6) . "00";
//          }

//          echo "<pre>";
//          var_dump($aVinculo);
//          exit;

          foreach ($aVinculo as $a => $b) {
            if ($lDebug==1) { echo("aaa: $a - $b === " . $oDadosQuery->o57_fonte . "\n"); }
            if (substr($oDadosQuery->o57_fonte, 0, strlen($a)) == $a) {

//echo "aaaaaa - " . $oDadosQuery->o57_fonte . "bbbbb<br><br><br>";

              if ($b == 0) {
                $oDados->cd_ItemReceita = str_pad(substr($oDadosQuery->o57_fonte, 0, strlen($a)),8, '0', STR_PAD_RIGHT);
              } else {
                if (substr($b,0,1) == '9') {
                  if ($lDebug==1) { echo("ok\n"); }
                  $oDados->cd_ItemReceita = str_pad(substr($b, 0, strlen($b)),8, '0', STR_PAD_RIGHT);
                } else {
                  $oDados->cd_ItemReceita = str_pad(substr($b, 0, strlen($b)),8, '0', STR_PAD_RIGHT);
                }
              }
              if ($lDebug==1) { echo("parou!\n"); }
              break;
            }
            //            die("aaa: $a - $b");
         }
//          exit;

          if ( $oVinculo->receitatce != "" ) {
            $oDados->cd_ItemReceita       = substr($oVinculo->receitatce,0,8);
          }

//          die("x: $oDados->cd_ItemReceita");

          $oDados->dt_ano               = $oDadosQuery->o57_anousu;
          $oDados->Cd_receblanc         = $oDadosQuery->reduz;
          $oDados->codigolinha          = 403;

          $this->aDados[] = $oDados;

        } else {
          $sErroLog  = "Receita {$oDadosQuery->o57_fonte} do ano de {$this->iAnoUso} ";
          $sErroLog .= "não tem vinculo com Recita Sigfis.\n";
          $this->addLog($sErroLog);
        }
      }
      }
      $this->addLog("===== Fim do Arquivo: ".$this->getNomeArquivo()."\n");
    }
  }

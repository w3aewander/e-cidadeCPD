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

require_once ("interfaces/iPadArquivoTxtBase.interface.php");
require_once ("model/contabilidade/arquivos/sigfis/SigfisArquivoBase.model.php");

/**
 * 
 * Classe Responsável pela geração dos dados necessários para o arquivo Plano de Contas
 * @author Andrio Costa
 *
 */
class SigfisArquivoPlanoConta extends SigfisArquivoBase implements iPadArquivoTXTBase {

  protected $iCodigoLayout     = 204;
  protected $sNomeArquivo      = 'ContaCont';
  protected $sIndicadorEmpresa = 'N';
  
  /**
   * 
   * Busca os dados para gerar o Arquivo de Plano de Conta
   * 
   */
  public function gerarDados() {
    
    $oDbConfig    = new db_stdClass();
    $clConPlano   = db_utils::getDao('conplano');
    $oDadoConfig  = $oDbConfig->getDadosInstit();
    
    if ($oDadoConfig->db21_tipoinstit == 9 || $oDadoConfig->db21_tipoinstit == 10) {
      $this->sIndicadorEmpresa = 'S';
    } else {
      $this->sIndicadorEmpresa = 'N';
    }

    $this->setCodigoLayout(204);
    if( $iAnoSessao < 2013 ){
  	  $this->setCodigoLayout(109);
    }
    
    $sCampos = " conplano.c60_anousu, 				                                                      ";
    $sCampos.= " conplano.c60_estrut,                                                                                 ";
    $sCampos.= " conplano.c60_codcon,                                                                                 "; 
    $sCampos.= " conplano.c60_codsis,                                                                                 ";
    $sCampos.= " conplano.c60_descr,                                                                                  ";
    $sCampos.= " conplanoreduz.c61_codigo,                                                                            ";
    $sCampos.= " coalesce(conplanoreduz.c61_reduz,0) as c61_reduz,                                                    ";
    $sCampos.= " conplanoreduz.c61_instit,                                                                            ";
    $sCampos.= " fc_nivel_plano2005(conplano.c60_estrut) as nivel, 						      ";
    $sCampos.= " case when conplanoreduz.c61_reduz is not null then 1 else 2 end as recebe_lancamento,  	      ";
    $sCampos.= " case when conplano.c60_codsis = 6 then 1                                                             ";
    $sCampos.= "      when orcelemento.o56_codele is not null then 2                                                  ";
    $sCampos.= "      when orcfontes.o57_codfon is not null then 3                                                    ";
    $sCampos.= "      else 9 end as tipo_conta, ";
    $sCampos.= " case when c63_banco   is null then '0' else c63_banco   end as banco,                                  ";
    $sCampos.= " case when c63_agencia is null then '' else c63_agencia end as agencia,                                ";
    $sCampos.= " case when c63_conta   is null then '' else c63_conta   end as conta,                                   ";
    $sCampos.= " c56_sequencial as seq_conta_corrente, ";
    $sCampos.= " db83_descricao as descr_conta_corrente ";
    
    $sSqlConPlano = $clConPlano->sql_query_planocontas($this->iAnoUso, $sCampos, 'conplano.c60_estrut');
    //die( $sSqlConPlano );
    $rsConPlano   = $clConPlano->sql_record($sSqlConPlano);
    
    /*
     * Variáveis de contrele da Classe;
     */
    $ReservadoTCE = " ";
    $iMes         = substr($this->dtDataFinal, 5, 2);
    $this->addLog("=====Arquivo".$this->getNomeArquivo()." Erros:\n");
    if ($clConPlano->numrows > 0) {
      
      if (empty($this->sCodigoTribunal)) {
        throw new Exception("O código do tribunal deve ser informado para geração do arquivo");
      }
      
      for($i = 0; $i < $clConPlano->numrows; $i++) {
        $oDados      = new stdClass();
        $oDadosPlano = db_utils::fieldsMemory($rsConPlano, $i);

        if ( $oDadosPlano->c61_reduz > 0 and $oDadosPlano->c61_instit <> $oDadoConfig->codigo ) {
          continue;
        }
        
        $iCodigoContaTCE = '';
        $sNaturezaSaldo  = 'M';
        if ($oVinculo = SigfisVinculoConta::getVinculoConta($oDadosPlano->c60_codcon) or true) {
          
          //$iCodigoContaTCE = $oVinculo->contatce;
          //$sNaturezaSaldo  = $oVinculo->naturezasaldo;

          $iCodigoContaTCE = 1276;
          $sNaturezaSaldo  = "D";

          if ($oDadosPlano->recebe_lancamento == 1) {
            $iCodigoContaTCE = substr($oDadosPlano->c60_estrut,0,4);

/*

             $aContas = array();

	     $aContas[1111102010] = 4;
	     $aContas[1111102020] = 8;
//	     $aContas[11111060] = 10;
	     $aContas[111115001] = 1220;
	     $aContas[111115002] = 1277;
	     $aContas[11221]	= 159;
	     $aContas[11231]	= 159;
	     $aContas[11241]	= 160;
	     $aContas[11311] 	= 166;
	     $aContas[12311] 	= 154;
	     $aContas[2121102] 	= 140;
	     $aContas[2131101] = 	118;
	     $aContas[2188101] = 	1294;
	     $aContas[2188104] = 	1277;
	     $aContas[2189101] = 	1301;
	     $aContas[218910101] = 	136;
	     $aContas[218910102] = 	135;
	     $aContas[218910104] = 	137;
	     $aContas[218910105] = 	134;
	     $aContas[218910106] = 	136;
	     $aContas[218910107] = 	137;
	     $aContas[218910108] = 	134;
	     $aContas[218910109] = 	133;
	     $aContas[218910110] = 	134;
	     $aContas[218910112] = 	136;
	     $aContas[218910115] = 	133;
	     $aContas[218910116] = 	137;
	     $aContas[218910117] = 	137;
	     $aContas[21891010201] = 	136;
	     $aContas[21891010207] = 	137;
	     $aContas[21891010210] = 	134;
	     $aContas[21893] = 	140;
	     $aContas[223130201] = 	91;
	     $aContas[237110301] = 	246;
	     $aContas[31121] = 	568;
	     $aContas[31311] = 	568;
	     $aContas[31921] = 	568;
	     $aContas[32911] = 	568;
	     $aContas[33111] = 	571;
	     $aContas[33221] = 	571;
	     $aContas[33231] = 	571;
	     $aContas[35102] = 	608;
	     $aContas[35132] = 	608;
	     $aContas[35241] = 	608;
	     $aContas[37211] = 	608;
	     $aContas[39991] = 	608;
	     $aContas[4112101] = 	530;
	     $aContas[4112197] = 	530;
	     $aContas[4113101] = 	530;
	     $aContas[4121101] = 	530;
	     $aContas[4122101] = 	530;
	     $aContas[43311] = 	530;
	     $aContas[44241] = 	537;
	     $aContas[4429101] = 	537;
	     $aContas[4521301] = 	536;
	     $aContas[4521401] = 	536;
	     $aContas[4996101] = 	537;
	     $aContas[4999101] = 	537;

//	     $aContas[1120]	= 1234;
//	     $aContas[1125]	= 166;
//	     $aContas[1126]	= 172;
//	     $aContas[1129]	= 179;
//	     $aContas[11312] = 1224;
//	     $aContas[1132]	= 1222;
//	     $aContas[1133]	= 1222;
//	     $aContas[1134]	= 1222;
//	     $aContas[1135]	= 1222;
//	     $aContas[1136]	= 161;
//	     $aContas[1138]	= 1222;
//	     $aContas[1139]	= 165;
//	     $aContas[11411] = 586;
//	     $aContas[11420] = 586;
//	     $aContas[1149]	= 165;
//	     $aContas[1150]	= 1142;
//	     $aContas[1157]	= 1245;
//	     $aContas[1158]	= 1242;
//	     $aContas[1190]	= 254;
//	     $aContas[12111] = 1221;
//	     $aContas[1214]	= 1242;
//	     $aContas[1219]	= 254;
//	     $aContas[1220]	= 573;
//	     $aContas[1231]	= 154;
//	     $aContas[1232]	= 153;
//	     $aContas[1238]	= 366;

             $iCodigoContaTCE = 1276;
             $sNaturezaSaldo  = "D";

	     foreach ($aContas as $a => $b) {

               if (substr($oDadosPlano->c60_estrut, 0, strlen($a)) == $a) {
                 $iCodigoContaTCE = $b;
               }

             }

*/

          } else {
            $iCodigoContaTCE = "";
            $sNaturezaSaldo  = "";
          }

          $aContasPCASP = array();

	  $aContasPCASP["100000000000000"] = 2000;
	  $aContasPCASP["110000000000000"] = 2005;
	  $aContasPCASP["111000000000000"] = 2010;
	  $aContasPCASP["111100000000000"] = 2015;
	  $aContasPCASP["111110000000000"] = 2020;
	  $aContasPCASP["111110100000000"] = 2025;
	  $aContasPCASP["111110200000000"] = 2030;
	  $aContasPCASP["111110201000000"] = 2035;
	  $aContasPCASP["111110202000000"] = 2185;
	  $aContasPCASP["111110600000000"] = 2190;
	  $aContasPCASP["111110601000000"] = 2195;
	  $aContasPCASP["111110602000000"] = 2200;
	  $aContasPCASP["111110603000000"] = 2210;
	  $aContasPCASP["111110604000000"] = 2195;
	  $aContasPCASP["111110605000000"] = 2220;
	  $aContasPCASP["111113000000000"] = 2230;
	  $aContasPCASP["111115000000000"] = 2235;
	  $aContasPCASP["111200000000000"] = 2405;

	  $iSeqPCASP = "";
	  foreach ($aContasPCASP as $a => $b) {
            $a = rtrim($a, "0");

	    if (substr($oDadosPlano->c60_estrut, 0, strlen($a)) == $a) {
	      $iSeqPCASP = $b;
              break;
	    }

	  }

        } else {
          $sErroLog  = "Conta {$oDadosPlano->c60_codcon} - {$oDadosPlano->c60_estrut} - {$oDadosPlano->c60_descr} ";
          $sErroLog .= "sem Vinculo com plano do SIGFIS\n";
          $this->addLog($sErroLog);
        }

        $iCodigoRecursoTCE = '';
        if ($oDadosPlano->recebe_lancamento == 1) {
          
          if ($oRecursoTCE = SigfisVinculoRecurso::getVinculoRecurso($oDadosPlano->c61_codigo)) {
            $iCodigoRecursoTCE = $oRecursoTCE->recursotce;
          } else {
  
           $sErroLog  = "Conta {$oDadosPlano->c60_codcon} - {$oDadosPlano->c60_estrut} - {$oDadosPlano->c60_descr} ";
           $sErroLog .= "possui recurso de código {$oDadosPlano->c61_codigo} sem Vinculo com os recursos do SIGFIS.\n";
           $this->addLog($sErroLog);

          }

        }
        
        //// nÃo listar contar que nÃo possuam vinculo com o SIGFIS
        if(trim($iCodigoContaTCE) == '') {
          continue;
        }

        $oDados->dt_AnoCriacao          = $oDadosPlano->c60_anousu;
        $oDados->tp_OrigemSaldo         = $sNaturezaSaldo; // 'M'; // Vem do XML;
        $oDados->cd_RecebeLanc          = $oDadosPlano->recebe_lancamento;
        $oDados->ST_EMPRESA             = $this->sIndicadorEmpresa;
        $oDados->dt_AnoMes              = $oDadosPlano->c60_anousu.$iMes;
        $oDados->nu_SequencialTC        = str_pad($iCodigoContaTCE,          4, ' ', STR_PAD_LEFT); // Vem do XML 
        $oDados->cd_Unidade             = str_pad($this->sCodigoTribunal,    4, ' ', STR_PAD_LEFT);
        $oDados->cd_ContaContabil       = str_pad($oDadosPlano->c60_estrut, 34, ' ', STR_PAD_RIGHT);
        $oDados->tp_ContaContabil       = str_pad($oDadosPlano->tipo_conta,  1, ' ', STR_PAD_LEFT);
        $oDados->nm_ContaContabil       = str_pad($oDadosPlano->c60_descr,  50, " ", STR_PAD_RIGHT);
        $oDados->nu_Nivel               = str_pad($oDadosPlano->nivel,       4, " ", STR_PAD_LEFT);
        $oDados->cd_Banco               = str_pad(substr($oDadosPlano->banco,0,4),   4, ' ', STR_PAD_LEFT);
	$oDados->cd_AgenciaBancaria     = str_pad(trim(substr($oDadosPlano->agencia,0,12)),12, ' ', STR_PAD_RIGHT);
	$oDados->cd_ContaBancaria       = str_pad(trim(substr($oDadosPlano->conta,( $oDadosPlano->banco == 104?2:0),10)),  10, ' ', STR_PAD_RIGHT);
        $oDados->Reservado_tce1         = str_pad($ReservadoTCE,            34, " ", STR_PAD_LEFT);
        $oDados->Reservado_tce2         = str_pad($ReservadoTCE,             4, " ", STR_PAD_LEFT);
        $oDados->cd_FonteGestor         = str_pad($oDadosPlano->c61_codigo,  4, " ", STR_PAD_LEFT);

       if($iAnoSessao < 2013 ) {
         $oDados->codigolinha            = 396;
       } else {

         $oDados->Cd_Atrib_ContaCorrente = '0';
         $oDados->Cd_ContaCorrente       = str_pad(str_repeat(' ', 30),  30, ' ', STR_PAD_RICHT);
         $oDados->de_ContaCorrente       = str_pad(str_repeat(' ', 100),  100, ' ', STR_PAD_RICHT);
         $oDados->nu_Sequencial_PCASP    = str_pad(str_repeat(' ', 5),  5, " ", STR_PAD_LEFT);
         if ( $oDadosPlano->tipo_conta == 1 ) {
           $oDados->Cd_Atrib_ContaCorrente = '1';
           $oDados->Cd_Conta_Corrente      = str_pad($oDadosPlano->seq_conta_corrente,     30, ' ', STR_PAD_RIGHT);
           $oDados->de_ContaCorrente       = str_pad($oDadosPlano->descr_conta_corrente,  100, ' ', STR_PAD_RIGHT);
         }
         $oDados->nu_Sequencial_PCASP    = str_pad($iSeqPCASP, 5, " ", STR_PAD_LEFT);
         $oDados->codigolinha            = 669;

       }

//        if ($oDadosPlano->agencia == '0' ) {
//          $oDados->cd_AgenciaBancaria  = str_pad($oDadosPlano->agencia, 12, ' ', STR_PAD_LEFT);
//        }
//        if ($oDadosPlano->conta == '0') {
//          $oDados->cd_ContaBancaria    = str_pad($oDadosPlano->conta, 10, ' ', STR_PAD_LEFT);
//        }
        
        $this->aDados[] = $oDados; 
        
      }
    }
    $this->addLog("===== Fim do Arquivo: ".$this->getNomeArquivo()."\n");
    
    return $this->aDados;
  }
  
}

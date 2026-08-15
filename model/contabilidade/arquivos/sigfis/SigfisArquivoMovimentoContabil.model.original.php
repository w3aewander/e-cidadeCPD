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
 * Classe Responsável pela geração dos dados necessários para o arquivo Movimento Contabil
 * @author Andrio Costa
 * @package contabilidade
 * @subpackage sigfis
 *
 */

class SigfisArquivoMovimentoContabil extends SigfisArquivoBase implements iPadArquivoTXTBase {
  
  protected $iCodigoLayout     = 205;
  protected $sNomeArquivo      = 'MovConta';
  protected $aMovimentoContabil = array();
  
  
  /**
  * Busca os dados para gerar o Arquivo do Movimento Contabil
  */
  public function gerarDados() {
  
    /**
     * Busca os dados da db_config
     */
    $oDadoConfig    = db_stdClass::getDadosInstit();

    $iInstituicaoSessao = db_getsession('DB_instit');

    $iAnoSessao = 2014;

    $this->setCodigoLayout(205);
    if( $iAnoSessao < 2013 ){
      $this->setCodigoLayout(124);
    }
                    
    $clConLanCam    = db_utils::getDao('conlancam');
    $sSqlConLanCam  = $clConLanCam->sql_movimentoContabilSigfis($this->iAnoUso, $iInstituicaoSessao, $this->dtDataInicial, $this->dtDataFinal);
// die($sSqlConLanCam);
// $this->addLog($sSqlConLanCam);
    $rsConLanCam    = $clConLanCam->sql_record($sSqlConLanCam);
//db_criatabela($rsConLanCam);exit;
    
    $this->addLog("=====Arquivo".$this->getNomeArquivo()." Erros:\n");
    if ($clConLanCam->numrows > 0) {
      
      if (empty($this->sCodigoTribunal)) {
        throw new Exception("O código do tribunal deve ser informado para geração do arquivo");
      }
      
      for ($i = 0; $i < $clConLanCam->numrows; $i++) {
        $oDadosQuery = new stdClass();
        $oDadosQuery = db_utils::fieldsMemory($rsConLanCam, $i);
        
        /**
         * Verifica se a Conta retornada possui vinculo com a conta do Sigfis
         */
        $indice = 0;
        //if ($oVinculo = SigfisVinculoConta::getVinculoConta($oDadosQuery->conta) or true) {
        if (true) {
          
          //$sIndice = $oVinculo->contatce . $oDadosQuery->competencia . $oDadosQuery->tipo_movimento;
          $sIndice = $oDadosQuery->estrutural . $oDadosQuery->competencia . $oDadosQuery->tipo_movimento;
          //$sIndice ++; 
          /**
           * Se a conta existir, temos que agrupar os valores de crédito e débito
           * Para isso foi criado um indice único para cada conta concatenando:
           * Conta do TCE + Ano e mes (competencia) + tipo do movimento  
          */
       //   if (!isset($aMovimentoContabil[$sIndice])) {
            
            $sContaCorrente = "select c56_sequencial as seq_conta_corrente from contabilidade.conplano left join conplanocontabancaria on conplano.c60_codcon = conplanocontabancaria.c56_codcon and conplano.c60_anousu   = conplanocontabancaria.c56_anousu left join configuracoes.contabancaria on db83_sequencial = c56_contabancaria where c60_anousu = $this->iAnoUso and c60_codcon = $oDadosQuery->conta ";
            $rsContaCorrente = db_query($sContaCorrente);
            $oDadosContaCorrente = db_utils::fieldsMemory($rsContaCorrente, 0);

            if ( $oDadosQuery->tipo_movimento == "") $oDadosQuery->tipo_movimento = 3;
            if ( $oDadosQuery->competencia == "" ) $oDadosQuery->competencia = substr($this->dtDataInicial,0,4) . substr($this->dtDataInicial,5,2);

            $oConta                       = new stdClass();
            $oConta->conta                = $oDadosQuery->estrutural;
            $oConta->competencia          = $oDadosQuery->competencia;   
            $oConta->tipo_movimento       = $oDadosQuery->tipo_movimento;
            $oConta->valor_credito        = $oDadosQuery->valor_credito; 
            $oConta->valor_debito         = $oDadosQuery->valor_debito;
            $oConta->seq_conta_corrente   = $oDadosContaCorrente->seq_conta_corrente;

//            if ($oDadosQuery->estrutural == "111110201012400") {
//              echo "<pre>";
//              var_dump($oConta);
//              exit;
//            }
            
          if ( $oDadosQuery->valor_credito > 0 or $oDadosQuery->valor_debito > 0 ) {
            $aMovimentoContabil[$sIndice] = $oConta;
          }

          if ( substr($oDadosQuery->competencia,-2) == "01" ) {

            if ( $oDadosQuery->c62_vlrcre > 0 or $oDadosQuery->c62_vlrdeb > 0 ) {

              // saldo abertura
              $oConta                       = new stdClass();
              $oDadosQuery->tipo_movimento  = 1;

              $oConta->conta                = $oDadosQuery->estrutural;
              $oConta->competencia          = $oDadosQuery->competencia;   
              $oConta->tipo_movimento       = $oDadosQuery->tipo_movimento;
              $oConta->valor_credito        = $oDadosQuery->c62_vlrcre; 
              $oConta->valor_debito         = $oDadosQuery->c62_vlrdeb;
              $oConta->seq_conta_corrente   = $oDadosContaCorrente->seq_conta_corrente;

              $sIndice = $oDadosQuery->estrutural . $oDadosQuery->competencia . $oDadosQuery->tipo_movimento;

              $aMovimentoContabil[$sIndice] = $oConta;

            }

          }

//          echo "<pre>";
//          var_dump( $aMovimentoContabil );
//          exit;

        //  } else {
           
        //   echo "   ".$oDadosQuery->estrutural."   credito --> ".$oDadosQuery->valor_credito."   debito --> $oDadosQuery->valor_debito ";
        //    $aMovimentoContabil[$sIndice]->valor_credito += $oDadosQuery->valor_credito;
        //    $aMovimentoContabil[$sIndice]->valor_debito  += $oDadosQuery->valor_debito;
        //  }

        } else {
          
          $sErroLog  = "Estrutural {$oDadosQuery->estrutural} - Conta{$oDadosQuery->conta} -> ";
          $sErroLog .= "sem Vinculo com plano do SIGFIS - Conta *NÃO* Adicionada ao Arquivo.\n";
//          echo "    ".$sErroLog;
          $this->addLog($sErroLog);

        }
      }

    } else {
      throw new Exception("Nenhum registro retornado para o ano {$this->iAnoUso}");
    }
    $this->addLog("===== Fim do Arquivo: ".$this->getNomeArquivo()."\n");
    
    foreach ($aMovimentoContabil as $oMovimento) {
      $oDados      = new stdClass();
      
      $oDados->dt_AnoCriacao     = $this->iAnoUso;
      $oDados->cd_Unidade        = str_pad($this->sCodigoTribunal,             4, ' ', STR_PAD_LEFT);
      $oDados->cd_ContaContabil  = str_pad(substr($oMovimento->conta, 0, 34), 34, ' ', STR_PAD_RIGHT);
      $oDados->tp_MovContabil    = $oMovimento->tipo_movimento;
      $oDados->dt_AnoMes         = $oMovimento->competencia;
      $oDados->vl_Debito         = str_pad(number_format($oMovimento->valor_debito, 2, '',''),  16, '0', STR_PAD_LEFT);
      $oDados->vl_Credito        = str_pad(number_format($oMovimento->valor_credito, 2, '',''), 16, '0', STR_PAD_LEFT);
      $oDados->Cd_Conta_Corrente = str_pad($oMovimento->seq_conta_corrente, 30, ' ', STR_PAD_RIGHT);
      if($iAnoSessao < 2013 ){ 
        $oDados->codigolinha     = 411;
      }else{
        $oDados->Cd_ContaCorrente  = str_pad(str_repeat(' ', 30),  30, ' ', STR_PAD_LEFT);
        $oDados->codigolinha     = 670;
      }
      
      $this->aDados[] = $oDados;

    }
  }
}
?>

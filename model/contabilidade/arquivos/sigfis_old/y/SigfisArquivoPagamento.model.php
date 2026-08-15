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

require_once(modification("interfaces/iPadArquivoTxtBase.interface.php"));
require_once(modification("model/contabilidade/arquivos/sigfis/SigfisArquivoBase.model.php"));

/**
 *
 * Classe Responsável pela geração dos dados necessários para o arquivo Pagamento
 * @author Andrio Costa
 * @package contabilidade
 * @subpackage sigfis
 *
 */
class SigfisArquivoPagamento extends SigfisArquivoBase implements iPadArquivoTXTBase {

  protected $iCodigoLayout     = 206;
  protected $sNomeArquivo      = 'PagEmp';
  protected $aMovimentoContabil = array();
  
  
  /**
  * Busca os dados para gerar o Arquivo do Pagamento
  */
  public function gerarDados() {
  
    /**
     * Busca os dados da db_config
     */

		$iInstituicaoSessao = db_getsession('DB_instit');

    $oDadoConfig    = db_stdClass::getDadosInstit();
    $clConLanCamEmp = db_utils::getDao('conlancamemp');

	  $this->setCodigoLayout(206);
    $iAnoSessao = db_getsession("DB_anousu");
    if( $iAnoSessao < 2013 ){
  	  $this->setCodigoLayout(129);
    }

    $sCampos  = "array_to_string(array_accum(distinct c60_estrut),',') as c60_estrut, array_to_string(array_accum(distinct c60_codcon),',') as c60_codcon,
                 empempenho.e60_codemp, empempenho.e60_anousu, conlancam.c70_data, orcdotacao.o58_orgao,           ";
    $sCampos .= "min(pagordem.e50_codord) as e50_codord, to_char(max(conlancam.c70_data),'YYYYmm') as competencia, orcdotacao.o58_unidade,   	         ";
    $sCampos .= "sum(case c53_tipo when 30 then conlancam.c70_valor  																	                         ";
    $sCampos .= "							 when 31 then (conlancam.c70_valor * -1) end) as valor_pago                         ";
    
    $sWhere   = " conlancam.c70_anousu = {$this->iAnoUso} and empempenho.e60_instit = {$iInstituicaoSessao}                    ";
    $sWhere  .= " and empempenho.e60_anousu = {$this->iAnoUso}                                                                 ";
    $sWhere  .= " and conhistdoc.c53_tipo in (30,31)                                                                           ";
    $sWhere  .= " and conlancam.c70_data between cast('{$this->dtDataInicial}' as date) and cast('{$this->dtDataFinal}' as date) ";
    $sWhere  .= " group by empempenho.e60_codemp, empempenho.e60_anousu, c70_data, orcdotacao.o58_orgao, ";
    $sWhere  .= " orcdotacao.o58_unidade";

    //$sWhere  .= " having max(conlancam.c70_data) between cast('{$this->dtDataInicial}' as date) and cast('{$this->dtDataFinal}' as date) ";
    
    $sOrdem   = "c60_estrut";
    
    $sSqlConLanCamEmp = $clConLanCamEmp->sql_query_pagamentoEmpenho(null , $sCampos, $sOrdem, $sWhere);

//die($sSqlConLanCamEmp);

    // = $clConLanCamEmp->sql_query_pagamentoEmpenho(null , $sCampos, $sOrdem, $sWhere);
    $rsConLanCamEmp    = $clConLanCamEmp->sql_record($sSqlConLanCamEmp);
    
    $this->addLog("=====Arquivo".$this->getNomeArquivo()." Erros:\n");
    
    if ($clConLanCamEmp->numrows > 0) {
      
      if (empty($this->sCodigoTribunal)) {
        throw new Exception("O código do tribunal deve ser informado para geração do arquivo");
      }
      
      for ($i = 0; $i < $clConLanCamEmp->numrows; $i++) {
        
        $oDadosQuery = new stdClass();
        $oDadosQuery = db_utils::fieldsMemory($rsConLanCamEmp, $i);
        
        if ($oDadosQuery->valor_pago == 0 ){
           continue;
        }

        /**
         * Verifica se a Conta retornada possui vinculo com a conta do Sigfis
         */
//        if ($oVinculo = SigfisVinculoConta::getVinculoConta($oDadosQuery->c61_codcon)) {
          
          $oDados                = new stdClass();
//          $sUnidadeOrcamentaria  = str_pad($oDadosQuery->o58_orgao, 2, '0', STR_PAD_LEFT);
          $sUnidadeOrcamentaria = str_pad($oDadosQuery->o58_unidade,4, ' ', STR_PAD_LEFT);
          $dtPagamento           = $this->formataData($oDadosQuery->c70_data);
          
          $oDados->cd_Unidade             = str_pad($this->sCodigoTribunal,    4, ' ', STR_PAD_LEFT);
          $oDados->cd_UnidadeOrcamentaria = str_pad($sUnidadeOrcamentaria,     4, ' ', STR_PAD_LEFT); 
          $oDados->nu_Empenho             = str_pad($oDadosQuery->e60_codemp, 10, ' ', STR_PAD_RIGHT);
          $oDados->dt_PagamentoEmpenho    = $dtPagamento;
          $oDados->dt_Ano                 = $oDadosQuery->e60_anousu;
          $oDados->vl_Pagamento           = str_pad($this->formataValor($oDadosQuery->valor_pago), 16, ' ', STR_PAD_LEFT);
          
          $aContas = array_unique((explode(',',$oDadosQuery->c60_estrut)));

          $aContasNovas = array();
          foreach ($aContas as $a) {
            if (!empty($a)) {            
              $aContasNovas[] = $a;
            }
          }
          $aContas = $aContasNovas;

          $aContasCodcon = array_unique((explode(',',$oDadosQuery->c60_codcon)));

          $aContasCodconNovas = array();
          foreach ($aContasCodcon as $a) {
            if (!empty($a)) {

              $sContaCorrente = "select c56_sequencial from contabilidade.conplano a left join conplanocontabancaria b on a.c60_codcon = b.c56_codcon and a.c60_anousu = b.c56_anousu left join configuracoes.contabancaria c on c.db83_sequencial = b.c56_contabancaria where a.c60_anousu = $iAnoSessao and a.c60_codcon = $a";
              $rsContaCorrente = db_query($sContaCorrente);
              $oDadosContaCorrente = db_utils::fieldsMemory($rsContaCorrente, 0);

              $aContasCodconNovas[] = $oDadosContaCorrente->c56_sequencial;

            }
          }
          $aContasCodcon = $aContasCodconNovas;

          $oDados->cd_ContaContabil1      = str_pad($aContas[0], 34, ' ', STR_PAD_RIGHT);
          $oDados->cd_ContaContabil2      = str_pad($aContas[1], 34, ' ', STR_PAD_RIGHT); // str_repeat(' ', 34); // Não usado no e-cidada
          $oDados->cd_ContaContabil3      = str_pad($aContas[2], 34, ' ', STR_PAD_RIGHT); // str_repeat(' ', 34); // Não usado no e-cidada


          
          $oDados->dt_AnoMes              = $oDadosQuery->competencia;
          $oDados->cd_Orgao               = str_pad($oDadosQuery->o58_orgao,   4, ' ', STR_PAD_LEFT);
          $oDados->nu_EmpenhoSup          = str_pad(str_repeat(' ', 10), 10, ' ', STR_PAD_LEFT);
          $oDados->Reservado_tce          = str_repeat(' ', 41);

          if( $iAnoSessao < 2013 ){
            $oDados->codigolinha            = 416;
          }else{
            $oDados->Reservado_tce1         = str_repeat(' ', 10);
            $oDados->Cd_ContaCorrente1      = str_pad($aContasCodcon[0],  30, ' ', STR_PAD_LEFT);
            $oDados->Cd_ContaCorrente2      = str_pad($aContasCodcon[1],  30, ' ', STR_PAD_LEFT);
            $oDados->Cd_ContaCorrente3      = str_pad($aContasCodcon[2],  30, ' ', STR_PAD_LEFT);
            $oDados->codigolinha            = 671;
          }
  
          $this->aDados[] = $oDados;
/*
        } else {
          $sErroLog  = "Estrutural {$oDadosQuery->c60_estrut} - Conta{$oDadosQuery->e50_codord} -> ";
          $sErroLog .= "sem Vinculo com plano do SIGFIS - Conta *NÃO* Adicionada ao Arquivo.\n";
          $this->addLog($sErroLog);
        } */
      }
    }
    
    $this->addLog("===== Fim do Arquivo: ".$this->getNomeArquivo()."\n");
  }
}
?>

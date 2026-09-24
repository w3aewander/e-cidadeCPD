<?php
/*
 *     E-cidade Software Publico para Gestao Municipal                
 *  Copyright (C) 2012  DBselller Servicos de Informatica             
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

require_once modification ("interfaces/iPadArquivoTxtBase.interface.php");
require_once modification ("model/contabilidade/arquivos/sigfis/SigfisArquivoBase.model.php");

/**
 *
 * Classe Responsável pela geração dos dados necessários para o arquivo Programa Orçamento
 * @author Andrio Costa
 * @package contabilidade
 * @subpackage sigfis
 *
 */
class etceArquivoProgramaOrcamento extends SigfisArquivoBase implements iPadArquivoTXTBase {
  
  protected $iCodigoLayout     = 114;
  protected $sNomeArquivo      = 'Programa';

  public function nomexml(){
    return "RemessaPrograma";
  }

  public function abretag(){
    return "Programas";
  }

  public function segundatag(){
    return "Programa";
  }

  public function getNomeElementos(){
    //$elementos = array("Identificador", "CodigoPrograma", "Descricao", "CodigoUnidadeGestora", "Ano", "Objetivo");
    $elementos = array("Identificador", "CodigoUnidadeGestora", "Ano", "CodigoPrograma", "Descricao", "Objetivo");
    return $elementos;
  }
  

  /**
  * Busca os dados para gerar o Arquivo do Programa do Orçamento
  */
  public function gerarDados() {
    $xprograma = array(
      "0", "1101", "1110", "1120", "1113", "1111", "1109", "1105", "1116", "1114", "1107", "1106", "1104", "1119"
    );
    /**
     * Busca os dados da db_config
     */
    $oDbConfig        = new db_stdClass();
    $oDadoConfig      = $oDbConfig->getDadosInstit();

    $iInstituicaoSessao = db_getsession('DB_instit');
                      
    $clOrcPrograma    = db_utils::getDao('orcprograma');
    $sCampos          = " orcprograma.o54_programa, orcprograma.o54_descr, orcprograma.o54_anousu, orcprograma.o54_finali ";
    $sCampos2         = ", sum(coalesce(o58_valor, 0)) as total_dotacao ";
    $sWhereOrcUnidade = "o54_anousu = {$this->iAnoUso} and o58_instit = {$iInstituicaoSessao} ";
    $sSqlOrcUnidade   = $clOrcPrograma->sql_query_programaOrcamento($sCampos, $sCampos2, null, $sWhereOrcUnidade, $sCampos);
    
    $rsOrcPrograma  = $clOrcPrograma->sql_record($sSqlOrcUnidade);
  
    if ($clOrcPrograma->numrows > 0) {
  
      if (empty($this->sCodigoTribunal)) {
        throw new Exception("O código do tribunal deve ser informado para geração do arquivo");
      }
      $xi = 1;
      for($i = 0; $i < $clOrcPrograma->numrows; $i++) {
        
        $oDadosQuery = db_utils::fieldsMemory($rsOrcPrograma, $i);
        $oDados      = new stdClass();

        /*
        $oDados->codigolinha     = 5008;
        $oDados->cd_SubPrograma  = $oDadosQuery->o54_programa;
        $oDados->de_SubPrograma  = substr($oDadosQuery->o54_descr, 0, 200);
        $oDados->cd_Unidade      = $this->sCodigoTribunal;
        $oDados->dt_Ano          = $oDadosQuery->o54_anousu;
        $oDados->de_Objetivo     = substr($oDadosQuery->o54_finali, 0, 2000);
        $oDados->vl_SubPrograma  = number_format($oDadosQuery->total_dotacao, 2, ".", "");
        */
        //$oDados->Identificador     = 5008;
        $oDados->Identificador = $xi;
        $oDados->CodigoUnidadeGestora = $this->sCodigoTribunal;
        $oDados->Ano = $oDadosQuery->o54_anousu;
        $oDados->CodigoPrograma  = $oDadosQuery->o54_programa;
        $oDados->Descricao  = utf8_decode(substr($oDadosQuery->o54_descr, 0, 200));
        $oDados->Objetivo = utf8_decode(substr($oDadosQuery->o54_finali, 0, 2000));        
        if(in_array($oDados->CodigoPrograma, $xprograma)){continue;}
        //$oDados->Valor  = number_format($oDadosQuery->total_dotacao, 2, ".", "");
                
        $this->aDados[] = $oDados;
        $xi++;
      }
    } 
  }
}

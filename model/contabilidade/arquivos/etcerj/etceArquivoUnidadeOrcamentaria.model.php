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


class etceArquivoUnidadeOrcamentaria extends SigfisArquivoBase implements iPadArquivoTXTBase {
  
  protected $iCodigoLayout     = 113;
  protected $sNomeArquivo      = 'UnidOrca';

  public function nomexml(){
    return "RemessaUnidadeOrcamentaria";
  }

  public function abretag(){
    return "UnidadesOrcamentarias";
  }

  public function segundatag(){
    return "UnidadeOrcamentaria";
  }

  public function getNomeElementos(){
    $elementos = array("Identificador", "CodigoUnidadeGestora", "Ano", "CodigoOrgao", "CodigoUnidadeOrcamentaria", "DescricaoUnidadeOrcamentaria");
    return $elementos;
  }

  public function buscaOrgaos($inst, $ano){    
    $sql = pg_query("SELECT distinct orcorgao.o40_orgao from orcorgao inner join db_config on db_config.codigo = orcorgao.o40_instit inner join orcdotacao on orcdotacao.o58_anousu = orcorgao.o40_anousu and orcdotacao.o58_orgao = orcorgao.o40_orgao where o40_anousu = {$ano} and o58_instit = {$inst}");
    $resultado = pg_fetch_all($sql);
    $orgaros = array();
    foreach ($resultado as $linha) {
      array_push($orgaros, $linha["o40_orgao"]);
    }

    return $orgaros;
  }

  /**
  * Busca os dados para gerar o Arquivo de Unidade Orçamentária
  */
  public function gerarDados() {
  
    /**
     * Busca os dados da db_config
     */
    $oDbConfig    = new db_stdClass();
    $oDadoConfig  = $oDbConfig->getDadosInstit();
    
    $clOrcUnidade   = db_utils::getDao('orcunidade');
    $sCampos        = "orcunidade.o41_anousu, orcunidade.o41_unidade, orcunidade.o41_descr, orcunidade.o41_orgao";
    $sWhere         = "     o41_instit = " .db_getsession("DB_instit");
    $sWhere        .= " and o41_anousu = {$this->iAnoUso}";
    $sSqlOrcUnidade = $clOrcUnidade->sql_query_file(null, null, null, $sCampos, null, $sWhere);
    $rsOrcUnidade   = $clOrcUnidade->sql_record($sSqlOrcUnidade);

    $confereorgao = $this->buscaOrgaos(db_getsession("DB_instit"), $this->iAnoUso);
    //var_dump($confereorgao); die("confere");
      //var_dump($sSqlOrcUnidade); die("Sequel");
    //select orcunidade.o41_anousu, orcunidade.o41_unidade, orcunidade.o41_descr, orcunidade.o41_orgao from orcunidade where o41_instit = 1 and o41_anousu = 2024
      //$xxx = pg_fetch_all($rsOrcUnidade);
      //echo "<pre>";
      //print_r($xxx);
      //echo "</pre>";
      //die("Confere");
    
    if ($clOrcUnidade->numrows > 0) {
      
      if (empty($this->sCodigoTribunal)) {
        throw new Exception("O código do tribunal deve ser informado para geração do arquivo");
      }
      $ix = 1;
      for($i = 0; $i < $clOrcUnidade->numrows; $i++) {
        $oDadosQuery = new stdClass();
        $oDadosQuery = db_utils::fieldsMemory($rsOrcUnidade, $i);
        $oDados      = new stdClass();
        if(!in_array($oDadosQuery->o41_orgao, $confereorgao)){continue;}
        //if($oDadosQuery->o41_orgao != 5 && $oDadosQuery->o41_orgao != 10){continue;}
        $oDados->Identificador = $ix;
        $oDados->CodigoUnidadeGestora = $this->sCodigoTribunal;        
        $oDados->Ano = $oDadosQuery->o41_anousu;
        $oDados->CodigoOrgao = $oDadosQuery->o41_orgao;      
        $oDados->CodigoUnidadeOrcamentaria = $oDadosQuery->o41_unidade;
        $oDados->DescricaoUnidadeOrcamentaria = utf8_decode(substr($oDadosQuery->o41_descr, 0, 250));
        $ix++;
        $this->aDados[] = $oDados;
      }      

      
    } 
  }
}

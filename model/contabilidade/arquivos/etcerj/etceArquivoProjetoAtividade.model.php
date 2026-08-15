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


require_once  modification("interfaces/iPadArquivoTxtBase.interface.php");
require_once  modification("model/contabilidade/arquivos/sigfis/SigfisArquivoBase.model.php");

/**
 *
 * Classe Responsável pela geração dos dados necessários para o arquivo Projeto/Atividade
 * @author Andrio Costa
 * @package contabilidade
 * @subpackage sigfis
 *
 */
class etceArquivoProjetoAtividade extends SigfisArquivoBase implements iPadArquivoTXTBase {
  
  protected $iCodigoLayout     = 115;
  protected $sNomeArquivo      = 'ProjAtv';

  public function nomexml(){
    return "RemessaAcaoOrcamentaria";
  }

  public function abretag(){
    return "AcoesOrcamentarias";
  }

  public function segundatag(){
    return "AcaoOrcamentaria";
  }

  public function getNomeElementos(){
    //$elementos = array("Identificador", "UnidadeGestora", "Ano", "TipodeAcao", "CodigoAcao", "Descricao", "Produto", "Programa",  "Funcao", "SubFuncao", "UnidadeMedida", "Meta");
    //$elementos = array("Identificador", "UnidadeGestora", "Ano", "TipodeAcao", "CodigoAcao", "Descricao", "Produto", "Programa", "Funcao", "SubFuncao", "UnidadeMedida", "Meta");

    $elementos = array("Identificador", "CodigoUnidadeGestora", "Ano", "TipoAcao", "CodigoAcao", "Descricao", "Produto", "Programa", "Funcao", "SubFuncao", "UnidadeMedida", "Meta");

    
    return $elementos;
  }
  
  /**
  * Busca os dados para gerar o Arquivo do Projeto/Atividade
  */
  public function gerarDados() {

    $xprojativ = array(
    "0300", "5020", "5020", "5020", "5020", "5021", "5030", "5031", "5033", "5040", "5043", "5048", "5049", "5070", "5072", "5073", "5074", "5076", "5080", "5081", "5082", "5090", "5093", "5110", "5119", "5120", "5122", "5123", "5132", "5133", "5135", "5136", "5137", "5138", "5139", "5140", "5141", "5143", "5144", "5145", "5146", "5147", "5160", "5162", "5165", "5166", "5170", "5171", "5190", "5206", "5208", "5210", "5217", "5219", "5220", "5243", "5245", "5246", "5247", "5282", "5283", "5285", "5310", "5314", "5410", "5411", "5468", "5473", "5508", "5512", "5513", "5514", "5515", "5516", "5517", "5518", "5523", "5525", "5527", "5531", "5538", "5543", "5567", "5573", "5574", "5589", "5590", "5598", "5617", "5618", "5619", "5622", "5623", "5624", "5628", "5629", "5630", "5631", "5634", "5637", "5638", "5756", "5763", "5766", "5774", "5775", "5778", "5779", "5780", "5781", "5782", "5783", "5784", "5787", "5790", "5792", "5963", "6010", "6020", "6021", "6021", "6030", "6031", "6032", "6033", "6039", "6040", "6041", "6042", "6043", "6044", "6080", "6081", "6082", "6083", "6084", "6085", "6086", "6088", "6089", "6090", "6091", "6092", "6093", "6094", "6095", "6096", "6097", "6098", "6099", "6110", "6111", "6112", "6113", "6113", "6120", "6121", "6129", "6129", "6150", "6151", "6152", "6153", "6154", "6155", "6156", "6157", "6158", "6161", "6163", "6165", "6166", "6167", "6170", "6171", "6172", "6173", "6174", "6175", "6219", "6221", "6222", "6240", "6241", "6241", "6241", "6242", "6243", "6244", "6247", "6260", "6260", "6261", "6263", "6264", "6265", "6266", "6267", "6268", "6274", "6280", "6281", "6283", "6284", "6285", "6286", "6287", "6288", "6289", "6311", "6312", "6317", "6319", "6333", "6334", "6460", "6601", "6612", "6676", "6678", "6864", "6910", "6911", "6913", "6915"
);


$xprograma = array(
"0", "1101", "1110", "1120", "1113", "1111", "1109", "1105", "1116", "1114", "1107", "1106", "1104", "1119"
);
  
    /**
     * Busca os dados da db_config
     */

    $iInstituicaoSessao = db_getsession('DB_instit');

    $oDbConfig    = new db_stdClass();
    $oDadoConfig  = $oDbConfig->getDadosInstit();
                 
    $clProjAtiv   = db_utils::getDao('orcprojativ');
    $clDotacao    = db_utils::getDao('orcdotacao');
                 
    $sCampos      = "distinct orcprojativ.o55_anousu, orcprojativ.o55_tipo, orcprojativ.o55_projativ, orcprojativ.o55_descr, ";
    $sCampos     .= "orcprojativ.o55_descrunidade, orcprojativ.o55_especproduto, orcproduto.o22_descrprod, ";
    $sCampos     .= "orcdotacao.o58_funcao, orcdotacao.o58_subfuncao, orcdotacao.o58_programa ";
    $sOrder       = "orcprojativ.o55_projativ";
    $sWhere       = "orcprojativ.o55_anousu = {$this->iAnoUso} and o58_instit = {$iInstituicaoSessao}";
    $sSqlProjAtiv = $clProjAtiv->sql_query_projetoAtividade($this->iAnoUso, null, $sCampos, $sOrder, $sWhere);

//die($sSqlProjAtiv);
    
    $rsProjAtiv   = $clProjAtiv->sql_record($sSqlProjAtiv);
    //$xxx = pg_fetch_all($rsProjAtiv);
    //echo "<pre>";
    //print_r($xxx);
    //echo "</pre>";
    //die("Confere");
    
    if ($clProjAtiv->numrows > 0) {
      
      if (empty($this->sCodigoTribunal)) {
        throw new Exception("O código do tribunal deve ser informado para geração do arquivo");
      }
      $xi = 1;
      for ($i = 0; $i < $clProjAtiv->numrows; $i++) {
        
        $oDadoFuncao     = new stdClass();
        $oDadoSubFuncao  = new stdClass();
        $oDadoPrograma   = new stdClass();
        $oDadosQuery     = new stdClass();
        $oDadosQuery     = db_utils::fieldsMemory($rsProjAtiv, $i);
                         
        $oDados          = new stdClass();
        
        /**
         * Busca todas Funções para o projeto de Atividade
         */
        $sSqlFuncao    = $clDotacao->sql_query_file($oDadosQuery->o55_anousu,
                                                    null,
                                                    "o58_funcao, count(*)",
                                                    " 2 desc, 1 asc",
                                                    "o58_funcao = {$oDadosQuery->o58_funcao} group by 1"); 
        $rsFuncao      = $clDotacao->sql_record($sSqlFuncao);
        $oDadoFuncao   = db_utils::fieldsMemory($rsFuncao, 0);
        
        /**
        * Busca todas SubFunções para o projeto de Atividade
        */
        $sSqlSubFuncao = $clDotacao->sql_query_file($oDadosQuery->o55_anousu,
                                                    null,
                                                    "o58_subfuncao, count(*)",
                                                    " 2 desc, 1 asc",
                                                    "o58_subfuncao = {$oDadosQuery->o58_subfuncao} group by 1");
        $rsSubFuncao   = $clDotacao->sql_record($sSqlSubFuncao);
        $oDadoSubFuncao  = db_utils::fieldsMemory($rsSubFuncao, 0);
        /**
        * Busca todos Programas para o projeto de Atividade
        */
        $sSqlPrograma  = $clDotacao->sql_query_file($oDadosQuery->o55_anousu,
                                                    null,
                                                    "o58_programa, count(*)",
                                                    " 2 desc, 1 asc",
                                                    "o58_programa = {$oDadosQuery->o58_programa} group by 1");
        $rsPrograma    = $clDotacao->sql_record($sSqlPrograma);
        $oDadoPrograma  = db_utils::fieldsMemory($rsPrograma, 0);
        
        if ($oDadosQuery->o55_tipo == 0 or $oDadosQuery->o55_tipo == 9) $oDadosQuery->o55_tipo = 3;

        /*
        $oDados->Identificador = $xi;
        $oDados->UnidadeGestora = $this->sCodigoTribunal;
        $oDados->Ano = $oDadosQuery->o55_anousu;
        $oDados->Tipodeacao = 9;//$oDadosQuery->o55_tipo;        
        $oDados->CodigoAcao = (strlen($oDadosQuery->o55_projativ) == 3 ) ? "0".$oDadosQuery->o55_projativ : $oDadosQuery->o55_projativ;
        $oDados->Descricao = substr($oDadosQuery->o55_descr, 0, 250);
        $oDados->Produto = substr($oDadosQuery->o22_descrprod, 0, 250);
        $oDados->Programa = $oDadoPrograma->o58_programa;
        $oDados->Funcao = $oDadoFuncao->o58_funcao;
        $oDados->SubFuncao = $oDadoSubFuncao->o58_subfuncao;        
        $oDados->UnidadeMedida = 1; 
        $oDados->Meta = "xxx";//substr($oDadosQuery->o55_especproduto, 0, 250);
        */

        $oDados->Identificador = $xi;
        //$oDados->UnidadeGestora = $this->sCodigoTribunal;
        $oDados->CodigoUnidadeGestora = $this->sCodigoTribunal;
        $oDados->Ano = $oDadosQuery->o55_anousu;
        $oDados->TipoAcao = $oDadosQuery->o55_tipo;
        $oDados->CodigoAcao = (strlen($oDadosQuery->o55_projativ) == 3 ) ? "0".$oDadosQuery->o55_projativ : $oDadosQuery->o55_projativ;
        $oDados->Descricao = substr($oDadosQuery->o55_descr, 0, 250);
        $oDados->Produto = substr($oDadosQuery->o22_descrprod, 0, 250);
        $oDados->Programa = $oDadoPrograma->o58_programa;
        $oDados->Funcao = (strlen($oDadoFuncao->o58_funcao) == 1) ? "0".$oDadoFuncao->o58_funcao : $oDadoFuncao->o58_funcao;
        $oDados->SubFuncao = (strlen($oDadoSubFuncao->o58_subfuncao) == 2) ? "0".$oDadoSubFuncao->o58_subfuncao : $oDadoSubFuncao->o58_subfuncao;
        $oDados->UnidadeMedida = 1;
        $oDados->Meta = (empty($oDadosQuery->o55_especproduto)) ? "Sem Meta" : substr($oDadosQuery->o55_especproduto, 0, 250);
        if(in_array($oDados->CodigoAcao, $xprojativ) && in_array($oDados->Programa, $xprograma)){continue;}


        $xi++;    
        //$oDados->Reservado_TCE               = str_pad("", 6, '0', STR_PAD_BOTH);         
        //$oDados->codigolinha     = 402;        
        $this->aDados[] = $oDados;
      }
        //echo "<pre>";
        //print_r($this->aDados);
        //echo "</pre>";
        //die("Confere");
    }
  }
}
<?php
/*
 *     E-cidade Software Publico para Gestao Municipal
 *  Copyright (C) 2014  DBSeller Servicos de Informatica
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

require_once  modification("fpdf151/pdf.php");
require_once  modification("libs/db_sql.php");
require_once  modification("libs/db_utils.php");
require_once  modification("std/DBArray.php");
require_once  modification("model/pessoal/ServidorRepository.model.php");
require_once  modification("libs/JSON.php");

$oJson        = new services_json();
$oGet         = db_utils::postMemory($HTTP_GET_VARS);
$iInstituicao = db_getsession("DB_instit");
$oParametros  = $oJson->decode(str_replace("\\","",$oGet->json));

try {

  /**
   * constantes para o Relatorio
   */
  define( "TIPO_RELATORIO_GERAL"              , "0" );
  define( "TIPO_RELATORIO_ORGAO"              , "1" );
  define( "TIPO_RELATORIO_LOTACAO"            , "2" );
  define( "TIPO_RELATORIO_MATRICULA"          , "3" );
  define( "TIPO_RELATORIO_LOCAIS_TRABALHO"    , "4" );
  define( "TIPO_RELATORIO_CARGO"              , "5" );

  define( "TIPO_FILTRO_GERAL"                 , 0 );
  define( "TIPO_FILTRO_INTERVALO"             , 1 );
  define( "TIPO_FILTRO_SELECIONADOS"          , 2 );

  define( "TIPO_VINCULO_GERAL"                , "g" );
  define( "TIPO_VINCULO_ATIVOS"               , "a" );
  define( "TIPO_VINCULO_INATIVOS"             , "i" );
  define( "TIPO_VINCULO_PENSIONISTAS"         , "p" );
  define( "TIPO_VINCULO_INATIVOS_PENSIONISTAS", "ip");


  define( "ORDENACAO_RELATORIO_NOME"       , "n" );
  define( "ORDENACAO_RELATORIO_MATRICULA" , "m" );
  define( "ORDENACAO_RELATORIO_CARGO" , "c" );


  $aWhere            = array();
  $sDescricaoSelecao = '';

  /**
   * Valida se existe seleção
   */
  if ( !empty($oParametros->iSelecao) ) {

    $sSelecao = trim( db_utils::getDao( "selecao" )->getCondicaoSelecao( $oParametros->iSelecao ) );

    if ( !empty($sSelecao) ) {
      $aWhere['selecao'] = $sSelecao;

      $sDescricaoSelecao = trim( db_utils::getDao( "selecao" )->getDescricaoSelecao( $oParametros->iSelecao ) );
      $sDescricaoSelecao = "\nSELEÇÃO : " . $sDescricaoSelecao;
    }
  }


  /**
   * Valida se existe Regime
   */
  if ( !empty($oParametros->iRegime) ) {

    $aWhere['regime']  = "rh30_regime = {$oParametros->iRegime}";
  }

  $head6 = 'TIPO FILTRO : ';

  switch ( $oParametros->iTipoRelatorio ) {

    default:

      $sLabelTipoRelatorio           = "Geral";
      $sCampoCondicaoTipoRelatorio   = 1;
      $sCampoEstruturalTipoRelatorio = 1;
      $sCampoDescricaoTipoRelatorio  = "'GERAL'";
      $head6                        .= 'GERAL';

    break;

    case TIPO_RELATORIO_CARGO:

      $sLabelTipoRelatorio           = "Cargos:";
      $sCampoCondicaoTipoRelatorio   = "rh37_funcao";
      $sCampoEstruturalTipoRelatorio = "rh37_funcao";
      $sCampoDescricaoTipoRelatorio  = "rh37_descr";
      $head6                        .= 'CARGOS';

    break;

    case TIPO_RELATORIO_LOTACAO:

      $sLabelTipoRelatorio             = "Lotações:";
      $sCampoCondicaoTipoRelatorio     = "r70_codigo";
      $sCampoEstruturalTipoRelatorio   = "r70_estrut";
      $sCampoDescricaoTipoRelatorio    = "r70_descr";
      $head6                          .= 'LOTAÇÕES';

    break;

    case TIPO_RELATORIO_ORGAO:

      $sLabelTipoRelatorio          = "Órgãos:";
      $sCampoCondicaoTipoRelatorio  = "rh26_orgao";
      $sCampoEstruturalTipoRelatorio= "rh26_orgao";
      $sCampoDescricaoTipoRelatorio = "o40_descr";
      $head6                       .= 'ORGÃOS';

    break;

    case TIPO_RELATORIO_LOCAIS_TRABALHO:

      $sLabelTipoRelatorio          = "Locais de Trabalho:";
      $sCampoCondicaoTipoRelatorio  = "rh55_codigo";
      $sCampoEstruturalTipoRelatorio= "rh55_estrut";
      $sCampoDescricaoTipoRelatorio = "rh55_descr";
      $head6                       .= 'LOCAIS DE TRABALHO';

    break;

    case TIPO_RELATORIO_MATRICULA:

      $sLabelTipoRelatorio          = "Matrículas:";
      $sCampoCondicaoTipoRelatorio  = "rh02_regist";
      $sCampoEstruturalTipoRelatorio= "rh02_regist";
      $sCampoDescricaoTipoRelatorio = "z01_nome";
      $head6                       .= 'MATRICULAS';

    break;

  }

  if ( $oParametros->iTipoRelatorio <> TIPO_RELATORIO_GERAL ) {

    switch ( $oParametros->iTipoFiltro ) {

      case TIPO_FILTRO_GERAL:
        //Sem Filtros
      break;
      case TIPO_FILTRO_INTERVALO:
        $aWhere['tipo_filtro' . $oParametros->iTipoRelatorio] = "{$sCampoCondicaoTipoRelatorio} between $oParametros->iIntervaloInicial and $oParametros->iIntervaloFinal";
      break;
      case TIPO_FILTRO_SELECIONADOS:
        $aWhere['tipo_filtro' . $oParametros->iTipoRelatorio] = "{$sCampoCondicaoTipoRelatorio} in (" . implode(", ", $oParametros->iRegistros) . ")";
      break;
    }
  }

  if( $oParametros->iSexo == 1 ){
    $aWhere['sexo']  = "z01_sexo = 'M'";
  }elseif($oParametros->iSexo == 2){
    $aWhere['sexo']  = "z01_sexo = 'F'";
  }

  if(!empty($oParametros->iIdade)){
    $aWhere['idade'] = "fc_idade(z01_nasc,CURRENT_DATE) {$oParametros->iIdade} ";
  }

  /**
   * Definições Sobre Vinculo
   */
  if ( !empty($oParametros->sVinculo) ) {

    switch ( $oParametros->sVinculo ) {

      default:
        $sTituloVinculo   = "GERAL";
        $sCondicaoVinculo = null;
      break;

      case TIPO_VINCULO_ATIVOS:
        $sTituloVinculo   = "ATIVOS";
        $sCondicaoVinculo = " rh30_vinculo = 'A' ";
      break;

      case TIPO_VINCULO_INATIVOS:
        $sTituloVinculo   = "INATIVOS";
        $sCondicaoVinculo = " rh30_vinculo = 'I' ";
      break;

      case TIPO_VINCULO_PENSIONISTAS:
        $sTituloVinculo   = "PENSIONISTAS";
        $sCondicaoVinculo = " rh30_vinculo = 'P' ";
      break;

      case TIPO_VINCULO_INATIVOS_PENSIONISTAS:
        $sTituloVinculo   = "INATIVOS / PENSIONISTAS";
        $sCondicaoVinculo = " rh30_vinculo in ('I','P') ";
      break;
    }

    if (!empty($sCondicaoVinculo) ) {
      $aWhere['vinculo'] = $sCondicaoVinculo;
    }
  }

  if( !empty($sDescricaoSelecao) ){
      $head6 .= $sDescricaoSelecao;
  }

  if ( !empty($oParametros->sOrdem) ) {

    switch ( $oParametros->sOrdem ) {

      case ORDENACAO_RELATORIO_NOME:
        $sOrdenar = "z01_nome";
      break;

      case ORDENACAO_RELATORIO_CARGO:
        $sOrdenar = "rh37_descr";
      break;

      case ORDENACAO_RELATORIO_MATRICULA:
        $sOrdenar = "rh01_regist";
      break;
    }

  }

  $aWhere['ativos'] = "rh05_seqpes is null";

  $oDaoRhPessoalMov = db_utils::getDao("rhpessoalmov");
  $iInstituicao     = db_getsession('DB_instit');
  $sWhere           = implode(' and ', $aWhere);
  $sCampos          = "distinct rh01_regist, ";
  $sCampos         .= "z01_nome, ";
  $sCampos         .= "rh02_funcao, ";
  $sCampos         .= "rh37_descr, ";
  $sCampos         .= "rh04_descr, ";
  $sCampos         .= "z01_telef ";

  $sSqlServidores   = $oDaoRhPessoalMov->sql_query_baseServidores($oParametros->iMes,
                                                                  $oParametros->iAno,
                                                                  $iInstituicao,
                                                                  $sCampos,
                                                                  $sWhere,
                                                                  $sOrdenar,
                                                                  "");

  $rsServidores = db_query($sSqlServidores);
  if ( !$rsServidores ) {
    throw new DBException( "Erro ao Buscar os Servidores pelos filtros selecionados. \n" . pg_last_error() );
  }

  if ( pg_num_rows( $rsServidores ) == 0 ) {
    throw new BusinessException("Nenhum Servidor encontrado nos Filtros Selecionados");
  }

  $oDadosRelatorio = db_utils::getCollectionByRecord($rsServidores);

  // $sTipoFolhas = count($oParametros->aTiposFolhas) > 1 ? 'Vários' : nomeFolhaAtual($oParametros->aTiposFolhas[0]);


  $head1      = "Relação Cargo e Função";
  $head3      = "DATA DE EMISSÃO : ".date('d/m/Y');
  $head4      = "PERÍODO : {$oParametros->iMes} / {$oParametros->iAno}";
  $head5      = "VINCULO : {$sTituloVinculo}";

  /**
   * Configurações do PDF
   */
  $oPdf = new PDF();
  $oPdf->Open();
  $oPdf->AliasNbPages();
  $oPdf->setfillcolor(235);
 

  /**
   * Altura da célula
   */
  $iAlt = 4;
  $total = 0;
  /**
   * Percorre array com os dados do filtro
   */
  cabecalho($oPdf);
  foreach ( $oDadosRelatorio as $oDadosServidores) {
    $total++;
    if($oPdf->Gety() > $oPdf->h - 30){
      cabecalho($oPdf);
    }
    $oPdf->setfont('arial','',8);
    $oPdf->cell(20, $iAlt, $oDadosServidores->rh01_regist , 0, 0, "L", 0);
    $oPdf->cell(70, $iAlt, $oDadosServidores->z01_nome    , 0, 0, "L", 0);
    $oPdf->cell(30, $iAlt, $oDadosServidores->rh02_funcao , 0, 0, "L", 0);
    $oPdf->cell(60, $iAlt, $oDadosServidores->rh37_descr  , 0, 0, "L", 0);
    $oPdf->cell(60, $iAlt, $oDadosServidores->rh04_descr  , 0, 0, "L", 0);
    $oPdf->cell(40, $iAlt, $oDadosServidores->z01_telef   , 0, 1, "L", 0);

  }
  rodape($oPdf,$total);
 $oPdf->Output();

} catch ( Exception $eErro ) {
  db_redireciona('db_erros.php?fechar=true&db_erro='. $eErro->getMessage() );
  exit;
}


function cabecalho($oPdf){
  
  $oPdf->AddPage('L');

  $oPdf->setfont('arial','b',8);
  $oPdf->cell(20, 4, 'Matricula'    , 1, 0, "C", 1);
  $oPdf->cell(70, 4, 'Nome'         , 1, 0, "C", 1);
  $oPdf->cell(30, 4, 'Código Cargo' , 1, 0, "C", 1);
  $oPdf->cell(60, 4, 'Descr. Cargo' , 1, 0, "C", 1);
  $oPdf->cell(60, 4, 'Função'       , 1, 0, "C", 1);
  $oPdf->cell(40, 4, 'Telefone'     , 1, 1, "C", 1);
}


function rodape($oPdf,$total){
  
  $oPdf->setfont('arial','b',8);
  $oPdf->cell(250, 4, 'TOTAL'  , 1, 0, "L", 1);
  $oPdf->cell(30, 4, "$total" , 1, 1, "L", 1);
}
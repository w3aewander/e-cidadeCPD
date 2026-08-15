<?php
/*
 *     E-cidade Software Publico para Gestao Municipal                
 *  Copyright (C) 2009  DBSeller Servicos de Informatica             
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
 
//require_once modification("libs/db_sql.php");
//require_once modification("libs/db_utils.php");
//require_once modification("libs/db_app.utils.php");
require_once modification("fpdf151/pdf.php");
//require_once modification("std/DBDate.php");
//require_once modification("std/DBNumber.php");

/**
 * Configurações para o pdf
 */
$iAlturaLinha      = 5;
$iCorPreenchimento = 235;

$aPontos = Array("salario"      => "Salário",
								 "rescisao"     => "Rescisão",
								 "decimo13"     => "13o Salario",
								 "complementar" => "Complementar");
$sTipoFolha = '';

foreach ($oParametros->aTiposFolha as $iIndice => $sFolha) {
  
  if ($iIndice != 0) {
    $sTipoFolha .= ", ";
  }
  $sTipoFolha .= $aPontos[$sFolha];
}

$oPDF = new pdf();
$oPDF->Open();
$oPDF->AliasNbPages();
$oPDF->SetFillColor($iCorPreenchimento);

$head2 = 'RELATÓRIO DE FAIXAS SALARIAIS';
$head4 = "Quebra: {$sAgrupador}";
$head5 = "Periodo: {$oParametros->iAnoFolha} / {$oParametros->iMesFolha}";
$head6 = "Folha(s): {$sTipoFolha}";

if ( isset($oParametros->iComplementar) && $oParametros->iComplementar != '' ) {
	$head7 = "Complementar: {$oParametros->iComplementar}";
}

$oPDF->AddPage();

foreach( $aDadosRelatorio as $oAgrupador ) {

	$oPDF->SetFont('Arial', 'B', 8);
	$oPDF->Cell(190, $iAlturaLinha, $oAgrupador->iCodigoAgrupador . ' - ' . $oAgrupador->sDescricaoAgrupador, 1, 1, "l", 1);

	/**
	 * Faixas salariais
	 */	 

	$nValorAcum = 0;
	$nPercentualAcum = 0;
	$iFuncionariosAcum = 0;
	$iFuncionariosPercAcum = 0;

	for ( $iIndice = 0; $iIndice < $iQuantidadeFaixas; $iIndice++ ) {
	  
		$iNumero            = $iIndice + 1;
		$sDescricao         = $aFaixas[$iIndice]['sDescricaoFaixa'];
		$nValor             = db_formatar($oAgrupador->aFaixas[$iIndice]['nValorFaixa'], 'f');
		$nValorAcum        += $oAgrupador->aFaixas[$iIndice]['nValorFaixa'];

		$iFuncionarios      = $oAgrupador->aFaixas[$iIndice]['iTotalFuncionarios'];
		$iFuncionariosAcum += $oAgrupador->aFaixas[$iIndice]['iTotalFuncionarios'];

		$nPercentual        = db_formatar($oAgrupador->aFaixas[$iIndice]['nPercentual'], 'f') . "%";
		$nPercentualAcum   += $oAgrupador->aFaixas[$iIndice]['nPercentual'];

		$iFuncionariosPerc      = $oAgrupador->aFaixas[$iIndice]['nPercentualFunc'];
		$iFuncionariosPercAcum += $oAgrupador->aFaixas[$iIndice]['nPercentualFunc'];

		if ($iIndice == 0 || $oPDF->GetY() > $oPDF->h - 25) {
		  $oPDF->SetFont('Arial', 'B', 8);
		  $oPDF->Cell(40, $iAlturaLinha, "Faixa" ,      1, 0, "C", 1);
		  $oPDF->Cell(30, $iAlturaLinha, "Valor" ,      1, 0, "C", 1);
		  $oPDF->Cell(30, $iAlturaLinha, "Valor AC" ,   1, 0, "C", 1);
		  $oPDF->Cell(15, $iAlturaLinha, "%" ,          1, 0, "C", 1);
		  $oPDF->Cell(15, $iAlturaLinha, "% AC" ,       1, 0, "C", 1);
		  $oPDF->Cell(15, $iAlturaLinha, "Func" ,       1, 0, "C", 1);
		  $oPDF->Cell(15, $iAlturaLinha, "Func AC" ,    1, 0, "C", 1);
		  $oPDF->Cell(15, $iAlturaLinha, "% Func" ,     1, 0, "C", 1);
		  $oPDF->Cell(15, $iAlturaLinha, "% Func AC" ,  1, 1, "C", 1);
		}
		
		$oPDF->SetFont('Arial', '', 8);
		$oPDF->Cell(40, $iAlturaLinha, "{$iNumero}: {$sDescricao}", 1, 0, "L", 0);
		$oPDF->Cell(30,  $iAlturaLinha, $nValor,                           1, 0, "R", 0);
		$oPDF->Cell(30,  $iAlturaLinha, db_formatar($nValorAcum,'f'),      1, 0, "R", 0);

		$oPDF->Cell(15,  $iAlturaLinha, $nPercentual,                      1, 0, "R", 0);
		$oPDF->Cell(15,  $iAlturaLinha, $nPercentualAcum,                  1, 0, "R", 0);

		$oPDF->Cell(15,  $iAlturaLinha, "$iFuncionarios",                  1, 0, "R", 0);
		$oPDF->Cell(15,  $iAlturaLinha, "$iFuncionariosAcum",              1, 0, "R", 0);

		$oPDF->Cell(15,  $iAlturaLinha, "$iFuncionariosPerc",                  1, 0, "R", 0);
		$oPDF->Cell(15,  $iAlturaLinha, "$iFuncionariosPercAcum",              1, 1, "R", 0);
		
		/**
		 * Verificamos se opcao de listar servidores esta como 'Sim'
		 */
		if ($iOpcaoServidor == 2) {
		  
  		/**
  		 * Imprimimos os servidores por faixa
  		 */
  		if ($oAgrupador->aFaixas[$iIndice]['iTotalFuncionarios'] > 0) {
  		  
  		  $oPDF->SetFont('Arial', 'b', 8);
  		  $oPDF->Cell(20,  $iAlturaLinha, "Matrícula",        1, 0, "C", 0);
  		  $oPDF->Cell(150, $iAlturaLinha, "Nome do Servidor", 1, 0, "C", 0);
  		  $oPDF->Cell(20,  $iAlturaLinha, "Valor",            1, 1, "C", 0);
  		  
  		  foreach($oAgrupador->aFaixas[$iIndice]['aServidores'] as $oDadosServidor) {
  		    $oServidor = new Servidor($oDadosServidor->iMatricula);
  		    $oPDF->SetFont('Arial', '', 8);
  		    $oPDF->Cell(20,  $iAlturaLinha, $oServidor->getMatricula(),                1, 0, "C", 0);
  		    $oPDF->Cell(150, $iAlturaLinha, "{$oServidor->getCgm()->getNome()}",       1, 0, "L", 0);
  		    $oPDF->Cell(20,  $iAlturaLinha, db_formatar($oDadosServidor->nValor, 'f'), 1, 1, "C", 0);
  		    unset($oServidor);
		  }

  		}
		}
	}

	$oPDF->SetFont('Arial', 'B', 8);
	$oPDF->Cell(40, $iAlturaLinha, "Total {$oAgrupador->sDescricaoAgrupador}",  1, 0, "L", 1);
	$oPDF->Cell(30,  $iAlturaLinha, db_formatar($oAgrupador->nTotal, 'f'),      1, 0, "R", 1);
	$oPDF->Cell(30,  $iAlturaLinha, "",                                         1, 0, "R", 1);
	$oPDF->Cell(15,  $iAlturaLinha, "100%",                                     1, 0, "R", 1);
	$oPDF->Cell(15,  $iAlturaLinha, "",                                         1, 0, "R", 1);
	$oPDF->Cell(15,  $iAlturaLinha, "$oAgrupador->iTotalFuncionarios",          1, 0, "R", 1);
	$oPDF->Cell(15,  $iAlturaLinha, "",                                         1, 0, "R", 1);
	$oPDF->Cell(15,  $iAlturaLinha, "$oAgrupador->iTotalFuncionarios",          1, 0, "R", 1);
	$oPDF->Cell(15,  $iAlturaLinha, "",                                         1, 1, "R", 1);
	
	$oPDF->Ln(4);
}

ob_start();
$oPDF->Output($sArquivo);
ob_clean();

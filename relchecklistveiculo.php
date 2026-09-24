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

include ("fpdf151/pdf.php");
include ("libs/db_utils.php");
include ("dbforms/db_funcoes.php");
include ("model/custoPlanilha.model.php");
include ("classes/db_custoplanilhaorigem_classe.php");
include ("classes/db_custoplano_classe.php");

$placa = $_GET["placa"];

$head1 = "CHECKLIST VEÍCULO";
$head3 = "Placa: {$placa}";
$head4 = "Data: " . date("d/m/Y");


$pdf = new PDF("L");
$pdf->open();
$pdf->aliasNbPages();
$pdf->setFillColor(235);
$pdf->setFont("arial", "b", 17);
$pdf->addPage("L");
$pdf->SetAutoPageBreak(false);
$alt = 4;
$largura = $pdf->w; //297.00008333333
$largura = 270;
$pdf->setLeftMargin(17);
//$pdf->setRightMargin(18);
$pdf->setFont("arial", "b", 16);

$head2 = "Placa: ";
$head3 = "Data: " . date("d/m/Y");
$pdf->cell($largura, $alt + 5, "Checklist Veículo", 0, 1, "C");



$pdf->setFont("arial", "b", 10);
$pdf->Cell(130,8,"A) EQUIPAMENTOS OBRIGATÓRIOS",'LTBR',0,'L');
$pdf->Cell(130,8,"B) FUNCIONAMENTO",'LTBR',0,'L');
$pdf->Ln();

$pdf->setFont("arial", "", 10);
$pdf->Cell(70,6,"EQUIPAMENTO",'LTBR',0,'L');
$pdf->Cell(20,6,"S",'LTBR',0,'C');
$pdf->Cell(20,6,"N",'LTBR',0,'C');
$pdf->Cell(20,6,"N.A.",'LTBR',0,'C');
$pdf->Cell(70,6,"EQUIPAMENTO",'LTBR',0,'L');
$pdf->Cell(20,6,"S",'LTBR',0,'C');
$pdf->Cell(20,6,"N",'LTBR',0,'C');
$pdf->Cell(20,6,"N.A.",'LTBR',0,'C');
$pdf->Ln();

$pdf->Cell(70,6,"EXTINTOR",'LTR',0,'L');
$pdf->Cell(20,6,"",'LTR',0,'C');
$pdf->Cell(20,6,"",'LTR',0,'C');
$pdf->Cell(20,6,"",'LTR',0,'C');
$pdf->Cell(70,6,"FARÓIS",'LTR',0,'L');
$pdf->Cell(20,6,"",'LTR',0,'C');
$pdf->Cell(20,6,"",'LTR',0,'C');
$pdf->Cell(20,6,"",'LTR',0,'C');
$pdf->Ln();

$pdf->Cell(70,6,"MACACO",'LTR',0,'L');
$pdf->Cell(20,6,"",'LTR',0,'C');
$pdf->Cell(20,6,"",'LTR',0,'C');
$pdf->Cell(20,6,"",'LTR',0,'C');
$pdf->Cell(70,6,"FARÓIS AUXILIARES",'LTR',0,'L');
$pdf->Cell(20,6,"",'LTR',0,'C');
$pdf->Cell(20,6,"",'LTR',0,'C');
$pdf->Cell(20,6,"",'LTR',0,'C');
$pdf->Ln();

$pdf->Cell(70,6,"CHAVE DE RODA",'LTR',0,'L');
$pdf->Cell(20,6,"",'LTR',0,'C');
$pdf->Cell(20,6,"",'LTR',0,'C');
$pdf->Cell(20,6,"",'LTR',0,'C');
$pdf->Cell(70,6,"LUZ DE FREIO",'LTR',0,'L');
$pdf->Cell(20,6,"",'LTR',0,'C');
$pdf->Cell(20,6,"",'LTR',0,'C');
$pdf->Cell(20,6,"",'LTR',0,'C');
$pdf->Ln();

$pdf->Cell(70,6,"TRIÂNGULO",'LTR',0,'L');
$pdf->Cell(20,6,"",'LTR',0,'C');
$pdf->Cell(20,6,"",'LTR',0,'C');
$pdf->Cell(20,6,"",'LTR',0,'C');
$pdf->Cell(70,6,"LUZ DE RÉ",'LTR',0,'L');
$pdf->Cell(20,6,"",'LTR',0,'C');
$pdf->Cell(20,6,"",'LTR',0,'C');
$pdf->Cell(20,6,"",'LTR',0,'C');
$pdf->Ln();

$pdf->Cell(70,6,"ESTEPE",'LTRB',0,'L');
$pdf->Cell(20,6,"",'LTRB',0,'C');
$pdf->Cell(20,6,"",'LTRB',0,'C');
$pdf->Cell(20,6,"",'LTRB',0,'C');
$pdf->Cell(70,6,"LUZ DA PLACA",'LTR',0,'L');
$pdf->Cell(20,6,"",'LTR',0,'C');
$pdf->Cell(20,6,"",'LTR',0,'C');
$pdf->Cell(20,6,"",'LTR',0,'C');
$pdf->Ln();


$pdf->Cell(70,6,"",'',0,'L');
$pdf->Cell(20,6,"",'',0,'C');
$pdf->Cell(20,6,"",'',0,'C');
$pdf->Cell(20,6,"",'',0,'C');
$pdf->setFont("arial", "", 11);
$pdf->Cell(70,6,"LIMPADOR/LAVADOR PARA-BRISA",'LBTR',0,'L');
$pdf->Cell(20,6,"",'LTRB',0,'C');
$pdf->Cell(20,6,"",'LTRB',0,'C');
$pdf->Cell(20,6,"",'LTRB',0,'C');
$pdf->Ln(10);

//$pdf->Ln();
$pdf->setFont("arial", "b", 10);
$pdf->Cell(260,8,"C) DOCUMENTAÇÃO",'LTBR',0,'C');
$pdf->Ln();

$pdf->setFont("arial", "", 10);
$pdf->Cell(70,6,"C1) MOTORISTA",'LTBR',0,'L');
$pdf->Cell(20,6,"S",'LTBR',0,'C');
$pdf->Cell(20,6,"N",'LTBR',0,'C');
$pdf->Cell(20,6,"N.A.",'LTBR',0,'C');
$pdf->Cell(70,6,"C2) VEÍCULO",'LTBR',0,'L');
$pdf->Cell(20,6,"S",'LTBR',0,'C');
$pdf->Cell(20,6,"N",'LTBR',0,'C');
$pdf->Cell(20,6,"N.A.",'LTBR',0,'C');
$pdf->Ln();

$pdf->Cell(70,6,"CNH EM DIA",'LTR',0,'L');
$pdf->Cell(20,6,"",'LTR',0,'C');
$pdf->Cell(20,6,"",'LTR',0,'C');
$pdf->Cell(20,6,"",'LTR',0,'C');
$pdf->Cell(70,6,"CRLV EM DIA",'LTR',0,'L');
$pdf->Cell(20,6,"",'LTR',0,'C');
$pdf->Cell(20,6,"",'LTR',0,'C');
$pdf->Cell(20,6,"",'LTR',0,'C');
$pdf->Ln();

$pdf->Cell(70,6,"RESOLUÇÃO 168",'LTR',0,'L');
$pdf->Cell(20,6,"",'LTR',0,'C');
$pdf->Cell(20,6,"",'LTR',0,'C');
$pdf->Cell(20,6,"",'LTR',0,'C');
$pdf->Cell(70,6,"REBOQUE/SEMI-REBOQUE",'LTR',0,'L');
$pdf->Cell(20,6,"",'LTR',0,'C');
$pdf->Cell(20,6,"",'LTR',0,'C');
$pdf->Cell(20,6,"",'LTR',0,'C');
$pdf->Ln();

$pdf->Cell(70,6,"MOPP",'BLTR',0,'L');
$pdf->Cell(20,6,"",'BLTR',0,'C');
$pdf->Cell(20,6,"",'BLTR',0,'C');
$pdf->Cell(20,6,"",'BLTR',0,'C');
$pdf->Cell(70,6,"OUTROS",'BLTR',0,'L');
$pdf->Cell(20,6,"",'BLTR',0,'C');
$pdf->Cell(20,6,"",'BLTR',0,'C');
$pdf->Cell(20,6,"",'BLTR',0,'C');
$pdf->Ln(10);

//$pdf->Ln();
$pdf->setFont("arial", "b", 10);
$pdf->Cell(260,8,"D) OCORRÊNCIAS/OBSERVAÇÕES",'LTBR',0,'C');
$pdf->Ln();

$pdf->Cell(260,6," ",'LTBR',0,'C');
$pdf->Ln();
$pdf->Cell(260,6," ",'LTBR',0,'C');
$pdf->Ln();
$pdf->Cell(260,6," ",'LTBR',0,'C');
$pdf->Ln();
$pdf->Cell(260,6," ",'LTBR',0,'C');
$pdf->Ln();
$pdf->Cell(260,6," ",'LTBR',0,'C');
$pdf->Ln(20);


$pdf->setFont("arial", "b", 12);
$pdf->cell($largura, $alt, "____________________________________________", 0, 1, "C");
$pdf->cell($largura, $alt + 2, "Assinatura Motorista e Matrícula", 0, 0, "C");


$pdf->Output();


?>
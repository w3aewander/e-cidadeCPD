<?php

require_once("libs/db_stdlib.php");
require_once("libs/db_conecta.php");
require_once("libs/db_sessoes.php");
require_once("libs/db_usuariosonline.php");

$nome_responsavel = "";
if (isset($_GET['nome_responsavel'])){
    $nome_responsavel = $_GET['nome_responsavel'];
}

$matricula = "";
if (isset($_GET['matricula'])){
    $matricula = $_GET['matricula'];
}

$exercicio = "";

if (isset($_GET['dDataInicial']) && isset($_GET['dDataFinal'])){
if (($_GET['dDataInicial'] != "") && ($_GET['dDataFinal'] != "")) {
     	$exercicio = date_format(date_create($_GET['dDataInicial']), "d/m/Y")." - ".date_format(date_create($_GET['dDataFinal']),"d/m/Y");

}}


if (isset($_GET['exercicio']) && ($exercicio == "")){
    $exercicio = $_GET['exercicio'];
}


function table_create($result)
{
    $texto_retorno = "";

    $numrows = pg_num_rows($result);
    $fnum = pg_num_fields($result);

    $texto_retorno .= "<table border width='100%'>";
    $texto_retorno .= "<tr>";

    for ($x = 0; $x < $fnum; $x++) {
        $texto_retorno .= "<td><b>";
        $texto_retorno .= strtoupper(pg_field_name($result, $x));
        $texto_retorno .= "</b></td>";
    }

    $texto_retorno. "</tr>";

    for ($i = 0; $i < $numrows; $i++) {
        $row = pg_fetch_object($result, $i);
        $texto_retorno .= "<tr align='center'>";
        for ($x = 0; $x < $fnum; $x++) {
    $fieldname = pg_field_name($result, $x);
    $texto_retorno .= "<td>";
    $texto_retorno .= $row->$fieldname;
    $texto_retorno .= "</td>";
        }
        $texto_retorno .="</tr>";
    }
    $texto_retorno .= "</table>";
   
    return $texto_retorno;
}

require_once('libs/db_conn.php');
$con_string = "host=".$DB_SERVIDOR." port=".$DB_PORTA." dbname=".$DB_BASE." user=".$DB_USUARIO." password=".$DB_SENHA;
$conexao = pg_connect($con_string);


$instituicao = "";
$municipio = "";
$result = pg_exec ($conexao, "select nomeinst, nomeinstabrev, munic from db_config where codigo=".db_getsession("DB_instit"));
$numrows = pg_numrows($result);
        if ($numrows) {
            $row = pg_fetch_array($result, 0);
            $instituicao = $row["nomeinstabrev"]." - ".$row['nomeinst'];
            $municipio = $row["munic"];
        }
pg_close ($conexao);




//============================================================+
// File name   : example_006.php
// Begin       : 2008-03-04
// Last Update : 2013-05-14
//
// Description : Example 006 for TCPDF class
//               WriteHTML and RTL support
//
// Author: Nicola Asuni
//
// (c) Copyright:
//               Nicola Asuni
//               Tecnick.com LTD
//               www.tecnick.com
//               info@tecnick.com
//============================================================+

/**
 * Creates an example PDF TEST document using TCPDF
 * @package com.tecnick.tcpdf
 * @abstract TCPDF - Example: WriteHTML and RTL support
 * @author Nicola Asuni
 * @since 2008-03-04
 */

// Include the main TCPDF library (search for installation path).
require_once('tcpdf_include.php');

// create new PDF document
$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// set document information
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Nicola Asuni');
$pdf->SetTitle('Relatório Legal XIX');
$pdf->SetSubject('Relatório Legal XIX');
$pdf->SetKeywords('Patrimônio, Relatório, Legal, XIX');

// set default header data
$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE.' 006', PDF_HEADER_STRING);

// set header and footer fonts
$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

// set default monospaced font
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

// set margins
$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

// set auto page breaks
$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

// set image scale factor
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

// set some language-dependent strings (optional)
if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
    require_once(dirname(__FILE__).'/lang/eng.php');
    $pdf->setLanguageArray($l);
}

// remove default header/footer
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);

// ---------------------------------------------------------

// set font
$pdf->SetFont('dejavusans', '', 9);

// add a page
$pdf->AddPage();

// writeHTML($html, $ln=true, $fill=false, $reseth=false, $cell=false, $align='')
// writeHTMLCell($w, $h, $x, $y, $html='', $border=0, $ln=0, $fill=0, $reseth=true, $align='', $autopadding=true)


$html = '
<table><tr style="text-align:center;">
    <td>MODELO 19</td>
</tr></table>
<br /><br />
<table border="1" cellpadding="4"><tr style="text-align:center;">
    <td>TERMO DE TRANSFERÊNCIA DE RESPONSABILIDADE - BENS PATRIMONIAIS</td>
</tr></table>
<br /><br />
<table border="1" cellpadding="4">
<tr style="text-align:left;">
    <td>Órgão: '.$instituicao.'</td>
    <td>Município: '.$municipio.'</td>
    <td>Exercício: '.$exercicio.'</td>
</tr>
</table>';
$pdf->writeHTML($html, true, false, true, false, '');


$pdf->SetFont('dejavusans', '', 8);


$html = '<p>Aos _____ dias do mês de __________ foi promovida a transferência de responsabilidade, relativa à guarda e controle dos Bens Patrimoniais, do(a) Sr. (a). __________________, para o (a) Sr. (a) ____________________________,  verificando-se:
<br /><br />
<br />Valor em Bens Móveis:  R$ ________________
<br />Valor em Bens Imóveis: R$ ________________
<br /><br />
<br />[  ] No momento de passagem da responsabilidade não foi detectada nenhuma impropriedade ou irregularidade
<br />
<br />[  ] No momento de passagem da responsabilidade foram detectadas as impropriedades e/ou irregularidades descritas em notas explicativas
<br />
<br />Notas Explicativas:
</p>';
$pdf->writeHTML($html, true, false, true, false, '');


$pdf->writeHTML(table_create($ResId), true, false, true, false, '');



$html = '
<table border="1">
<tr><td></td></tr><tr><td></td></tr>
<tr><td></td></tr><tr><td></td></tr>
<tr><td></td></tr><tr><td></td></tr>
<tr><td></td></tr><tr><td></td></tr>
<tr><td></td></tr><tr><td></td></tr>
<tr><td></td></tr><tr><td></td></tr>
<tr><td></td></tr><tr><td></td></tr>
<tr><td></td></tr><tr><td></td></tr>
<tr><td></td></tr><tr><td></td></tr>
<tr><td></td></tr><tr><td></td></tr>
<tr><td></td></tr><tr><td></td></tr>
</table>';
$pdf->writeHTML($html, true, false, true, false, '');




$html = '
<table border="1" cellpadding="4">
<tr>
    <td colspan="3">Nome:<br />'.$nome_responsavel.'</td> 
    <td colspan="2" align="center">Responsável pelos Bens<br /> Patrimoniais - Substituído</td>
</tr>
<tr>
    <td>Matrícula:<br />'.$matricula.'</td>
    <td>Data:<br />'.date('d/m/Y').'</td>
    <td colspan="3">Assinatura:</td>
</tr>
<tr>
    <td colspan="3">Nome:</td>
    <td colspan="2" rowspan="2" align="center">Responsável pelos Bens<br /> Patrimoniais - Substituto</td>
</tr>
<tr>
    <td>Matrícula:</td>
    <td>Data: __/__/____</td>
    <td>Assinatura:</td>
</tr>
<tr>
    <td colspan="3">Declaro que os valores acima descritos guardam paridade com o constante nos registros contábeis OU não guardam paridade com o constante nos registros contábeis, conforme apontado em Notas Explicativas.</td>
    <td rowspan="2" align="center">Responsável pelo<br />Setor Contábil</td>
    <td rowspan="2" align="center">CRC-RJ<br />nº _____</td>
</tr>
<tr>
    <td colspan="3">Nome:</td>
</tr>
<tr>
    <td>Matrícula:</td>
    <td>Data: __/__/____</td>
    <td colspan="3">Assinatura:</td>
</tr>
</table>';
$pdf->writeHTML($html, true, false, true, false, '');

// - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

// reset pointer to the last page
$pdf->lastPage();

// ---------------------------------------------------------

//Close and output PDF document
$pdf->Output('pat2_relatoriolegalmodeloxix.pdf', 'I');

//============================================================+
// END OF FILE
//============================================================+

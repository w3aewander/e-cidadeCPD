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

$PDF_PAGE_ORIENTATION_LOCAL = "L";

// create new PDF document
$pdf = new TCPDF($PDF_PAGE_ORIENTATION_LOCAL, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// set document information
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Nicola Asuni');
$pdf->SetTitle('Relatório Legal XXV');
$pdf->SetSubject('Relatório Legal XXV');
$pdf->SetKeywords('Patrimônio, Relatório, Legal, XXV');

// set default header data
$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE.' 006', PDF_HEADER_STRING);

// set header and footer fonts
$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

// set default monospaced font
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

// set margins
$margin_top = 10;
$pdf->SetMargins(PDF_MARGIN_LEFT, $margin_top, PDF_MARGIN_RIGHT);
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
$pdf->SetFont('dejavusans', '', 10);

// add a page
$pdf->AddPage();

// writeHTML($html, $ln=true, $fill=false, $reseth=false, $cell=false, $align='')
// writeHTMLCell($w, $h, $x, $y, $html='', $border=0, $ln=0, $fill=0, $reseth=true, $align='', $autopadding=true)


$html = '
<table><tr style="text-align:center;">
    <td>MODELO 25</td>
</tr></table>
<br /><br />
<table border="1" cellpadding="4"><tr style="text-align:center;">
    <td>DEMONSTRATIVO DE IMÓVEIS NÃO INVENTARIADO</td>
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



$html = '
<br />
<table border="1" cellpadding="4">
<tr style="text-align:center;">
    <td width="10%">Procedência do<br />Bem</td>
    <td width="15%">Data de<br />Aquisição/Recebimento</td>
    <td width="40%">Descrição do Bem</td>
    <td width="15%">Nº Processo Administrativo,<br />Termo de Doação ou Outro<br />Instrumento</td>
    <td width="10%">Valor Total<br />(R$)</td>
    <td width="10%">Justificativa/Providências</td>
</tr>
<tr><td></td><td></td><td></td><td></td><td></td><td></td></tr>
<tr><td></td><td></td><td></td><td></td><td></td><td></td></tr>
<tr><td></td><td></td><td></td><td></td><td></td><td></td></tr>
<tr><td></td><td></td><td></td><td></td><td></td><td></td></tr>
<tr><td></td><td></td><td></td><td></td><td></td><td></td></tr>
<tr><td></td><td></td><td></td><td></td><td></td><td></td></tr>
<tr><td></td><td></td><td></td><td></td><td></td><td></td></tr>
<tr><td></td><td></td><td></td><td></td><td></td><td></td></tr>
<tr><td></td><td></td><td></td><td></td><td></td><td></td></tr>
<tr><td></td><td></td><td></td><td></td><td></td><td></td></tr>
<tr><td></td><td></td><td></td><td></td><td></td><td></td></tr>
</table>';
$pdf->writeHTML($html, true, false, true, false, '');




$html = '
<table border="1" cellpadding="4">
<tr>
    <td colspan="3">Nome:<br />'.$nome_responsavel.'</td>
    <td colspan="3" align="center">Responsável pelos Bens Patrimoniais</td>
</tr>
<tr>
    <td>Matrícula:<br />'.$matricula.'</td>
    <td colspan="2">Data:<br />'.date('d/m/Y').'</td>
    <td colspan="3">Assinatura:</td>
</tr>
<tr>
    <td width="60%" colspan="3">Declaro que os valores acima descritos guardam paridade com os valores de aquisição dos bens imóveis, não considerando as reavalizações realizadas OU não guardam paridade com os valores de aquisição dos bens imóveis, sendo que as diferenças não se referem as reavalizações realizadas, conforme apontado em Notas Explicativas.</td>
    <td width="40%" colspan="3" rowspan="2" align="center">Responsável pelo Setor Contábil</td>
</tr>
<tr>
    <td colspan="3">Nome:</td>
</tr>
<tr>
    <td>Matrícula:</td>
    <td colspan="2">Data: __/__/____</td>
    <td colspan="3">Assinatura:</td>
</tr>
</table>';
$pdf->writeHTML($html, true, false, true, false, '');


// add a page
//$pdf->AddPage();

//$html = '
//<hr />
//<h3 style="text-align:center">Tutorial Modelo 25</h3>
//<p><u><b>Procedência:</u></b>  Neste campo, indicar, entre as opções existentes se o bem foi proveniente de Aquisição Direta, Procedimento Licitatório, Dispensa ou Inexigibilidade, Doação ou Outros Casos de Superviniências Ativas.</p>
//<p><u><b>Justificativa/Providências:</u></b>  Neste campo indicar os fatos que deram causa a ausência de inventariação do bem, bem como as providências adotadas para o saneamento.
//</p>

//<hr />';
//$pdf->writeHTML($html, true, false, true, false, '');


// - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

// reset pointer to the last page
$pdf->lastPage();

// ---------------------------------------------------------

//Close and output PDF document
$pdf->Output('pat2_relatoriolegalmodeloxxv.pdf', 'I');

//============================================================+
// END OF FILE
//============================================================+
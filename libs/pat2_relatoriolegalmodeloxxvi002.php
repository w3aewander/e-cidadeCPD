<?php

//require 'libs/PhpSpreadsheet/autoload.php'; //autoload do projeto
//use PhpOffice\PhpSpreadsheet\Spreadsheet; //classe responsável pela manipulação da planilha
//use PhpOffice\PhpSpreadsheet\Writer\Xlsx; //classe que salvará a planilha em .xlsx

require_once("libs/db_stdlib.php");
require_once("libs/db_conecta.php");
require_once("libs/db_sessoes.php");
require_once("libs/db_usuariosonline.php");

function formatNumero($numero, $casas = 2){
   return number_format($numero, $casas, ',', '.');;
}

$format_rel = "pdf";
if (isset($_GET['format'])){
    $format_rel = $_GET['format'];
}

$nome_responsavel = "";
if (isset($_GET['nome_responsavel'])){
    $nome_responsavel = $_GET['nome_responsavel'];
}

$matricula = "";
if (isset($_GET['matricula'])){
    $matricula = $_GET['matricula'];
}

$exercicio = "";
$exercicio_anterior = "";
$dt_ini = date("Y-m-d H:i:s");
$dt_fin = date("Y-m-d H:i:s");
if (isset($_GET['dDataInicial']) && isset($_GET['dDataFinal'])){
if (($_GET['dDataInicial'] != "") && ($_GET['dDataFinal'] != "")) {
    $dt_ini = DateTime::createFromFormat('Y-m-d H:i:s', $_GET['dDataInicial']." 00:00:00");
    $dt_ini = $dt_ini->format('Y-m-d H:i:s'); 
    
    $dt_fin = DateTime::createFromFormat('Y-m-d H:i:s', $_GET['dDataFinal']." 23:59:59");
    $dt_fin = $dt_fin->format('Y-m-d H:i:s');

    $exercicio = date_format(date_create($_GET['dDataInicial']), "d/m/Y")." - ".date_format(date_create($_GET['dDataFinal']),"d/m/Y");
}}


if (isset($_GET['exercicio']) && ($exercicio == "")){
    $dt_ini = DateTime::createFromFormat('Y-m-d H:i:s', $_GET['exercicio']."-01-01 00:00:00");
    $dt_ini = $dt_ini->format('Y-m-d H:i:s'); 
    
    $dt_fin = DateTime::createFromFormat('Y-m-d H:i:s', $_GET['exercicio']."-12-31 23:59:59");
    $dt_fin = $dt_fin->format('Y-m-d H:i:s');
    $exercicio = $_GET['exercicio'];

    $exercicio_anterior = ((int) $exercicio) - 1;
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


// Total
$bens_ini = 0;
$bens_aqui = 0;
$bens_reav_aqui = 0;
$bens_baixa = 0;
$bens_reav_baixa = 0;
$bens_fin = 0;



function calculaMovimetosBens($tipo, $inst, $dtini, $dtfin, $conn){


    $result = pg_exec ($conn, 
    " select t52_ident, t52_descr, t52_dtaqu,  t52_valaqu, t52_codcla, t52_obs ".

    ", COALESCE((select t44_valoratual from BENSDEPRECIACAO vl where vl.t44_sequencial=(select max(t44_sequencial) ".
                 " from BENSDEPRECIACAO sq where sq.t44_bens=t52_bem)),t52_valaqu) t58_valoratual ".

    " from Bens inner join clabens on t64_codcla=t52_codcla and t64_instit=t52_instit ".
    " where t52_instit = ". $inst ." and t64_bemtipos=".$tipo.
    " and t52_dtaqu >= '".$dtini."' and t52_dtaqu <= '".$dtfin."'".
    " order by t52_descr");
    $qtd_linhas = pg_numrows($result);
    $id_row = 0;

    

    $aqui = 0;
    $reav_aqui = 0;
    if ($qtd_linhas){
        while ($id_row < $qtd_linhas){
            $row = pg_fetch_array($result, $id_row);
            $aqui += floatval($row['t52_valaqu']);
            if (floatval($row['t58_valoratual']) > floatval($row['t52_valaqu'])){
                $reav_aqui += floatval($row['t58_valoratual']) - floatval($row['t52_valaqu']);
            }else{
                $reav_aqui += floatval($row['t52_valaqu']) - floatval($row['t58_valoratual']);
            }
            //$bens_aqui += $row['t52_valaqu'];

            $id_row += 1;
        }
    }


    $result = pg_exec ($conn, 
    " select t52_ident, t52_descr, t55_baixa,  t52_valaqu, t52_codcla, t51_descr ".
    
    ", COALESCE((select t44_valoratual from BENSDEPRECIACAO vl where vl.t44_sequencial=(select max(t44_sequencial) ".
                 " from BENSDEPRECIACAO sq where sq.t44_bens=t52_bem)),t52_valaqu) t58_valoratual ".

    " from Bens inner join clabens on t64_codcla=t52_codcla and t64_instit=t52_instit ".
    "      inner join BENSBAIX on t55_codbem = t52_bem ".
    "      inner join BENSMOTBAIXA on t51_motivo = t55_motivo ".
    
    " where t52_instit = ". $inst ." and t64_bemtipos=".$tipo.
    " and t55_baixa >= '".$dtini."' and t55_baixa <= '".$dtfin."'".
    " order by t52_descr");
    $qtd_linhas = pg_numrows($result);
    $id_row = 0;

    $baixa = 0;
    $reav_baixa = 0;
    if ($qtd_linhas){
        while ($id_row < $qtd_linhas){
            $row = pg_fetch_array($result, $id_row);
            $baixa += floatval($row['t52_valaqu']);
            if (floatval($row['t58_valoratual']) > floatval($row['t52_valaqu'])){
                $reav_baixa += floatval($row['t58_valoratual']) - floatval($row['t52_valaqu']);
            }else{
                $reav_baixa += floatval($row['t52_valaqu']) - floatval($row['t58_valoratual']);
            }
            //$bens_baixa += $row['t58_valoratual'];

            $id_row += 1;
        }
    }

    //var_dump($reav_aqui);
    return array( $aqui , $reav_aqui , $baixa , $reav_baixa );

}



    // Moveis Inicial
    list($bens_mov_aqui, $bens_mov_reav_aqui, $bens_mov_baixa, $bens_mov_reav_baixa) = calculaMovimetosBens(1, db_getsession("DB_instit"), "1500-01-01 00:00:00", $exercicio_anterior."-12-31 23:59:59", $conexao);
    $bens_mov_ini = $bens_mov_ini + $bens_mov_aqui + $bens_mov_reav_aqui - $bens_mov_baixa - $bens_mov_reav_baixa;
    // Imoveis Inicial
    list($bens_imov_aqui, $bens_imov_reav_aqui, $bens_imov_baixa, $bens_imov_reav_baixa) = calculaMovimetosBens(2, db_getsession("DB_instit"), "1500-01-01 00:00:00", $exercicio_anterior."-12-31 23:59:59", $conexao);
    $bens_imov_ini = $bens_imov_ini + $bens_imov_aqui + $bens_imov_reav_aqui - $bens_imov_baixa - $bens_imov_reav_baixa;






    // Moveis
    list($bens_mov_aqui, $bens_mov_reav_aqui, $bens_mov_baixa, $bens_mov_reav_baixa) = calculaMovimetosBens(1, db_getsession("DB_instit"), $dt_ini, $dt_fin, $conexao);
    $bens_mov_fin = $bens_mov_ini + $bens_mov_aqui + $bens_mov_reav_aqui - $bens_mov_baixa - $bens_mov_reav_baixa;
    // Imoveis
    list($bens_imov_aqui, $bens_imov_reav_aqui, $bens_imov_baixa, $bens_imov_reav_baixa) = calculaMovimetosBens(2, db_getsession("DB_instit"), $dt_ini, $dt_fin, $conexao);
    $bens_imov_fin = $bens_imov_ini + $bens_imov_aqui + $bens_imov_reav_aqui - $bens_imov_baixa - $bens_imov_reav_baixa;


    pg_close ($conexao);



if(db_getsession("DB_instit") == 45){

if($_GET['exercicio'] == 2018){
    $valor18 = (float)656119.01;
    $bens_imov_aqui = $bens_imov_aqui - $valor18;
    $bens_imov_fin = $bens_imov_fin - $valor18;
}

if($_GET['exercicio'] == 2019){
    $valor19 = (float)656119.01;
    $bens_imov_ini = $bens_imov_ini - $valor19;
    $bens_imov_fin = $bens_imov_fin - $valor19;
}


if($_GET['exercicio'] == 2020){
    $valor20 = (float)656119.01;
    $bens_imov_ini = $bens_imov_ini - $valor20;
    $bens_imov_fin = $bens_imov_fin - $valor20;
}

if($_GET['exercicio'] > 2020){
    $novoValor = (float)656119.01;
    $bens_imov_ini = $bens_imov_ini - $novoValor;
    $bens_imov_fin = $bens_imov_fin - $novoValor;
}

}

if ($format_rel == "pdf"){
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
    $pdf->SetTitle('Relatório Legal XXVI');
    $pdf->SetSubject('Relatório Legal XXVI');
    $pdf->SetKeywords('Patrimônio, Relatório, Legal, XXVI');

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
        <td>MODELO 26</td>
    </tr></table>
    <br /><br />
    <table border="1" cellpadding="4"><tr style="text-align:center;">
        <td>DEMONSTRATIVO DA MOVIMENTAÇÃO DOS BENS PATRIMONIAIS NO EXERCÍCIO</td>
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

    $pdf->Ln(3);

    $html = '
    <br />
    <table border="1" cellpadding="4">
    <tr style="text-align:center;">
        <td rowspan="4">Tipo</td>
        <td rowspan="3">Valor Líquido Inicial no Ano<br />Correspondente ao Registro<br />Contábil (R$)</td>
        <td colspan="4">Movimentação do Período no Ano</td>
        <td rowspan="3">Valor Líquido Final no Ano<br />Correspondente ao<br />Registro Contábil<br />(R$)</td>
    </tr>
    <tr style="text-align:center;">
        <td colspan="2">Entradas (R$)</td>
        <td colspan="2">Saídas (R$)</td>
    </tr>
    <tr style="text-align:center;">
        <td>Aquisições</td>
        <td>Reavaliações</td>
        <td>Baixas</td>
        <td>Reavaliações</td>
    </tr>
    <tr style="text-align:center;">
        <td>(A)</td>
        <td>(B)</td>
        <td>(C)</td>
        <td>(D)</td>
        <td>(E)</td>
        <td>(F = A+B+C-D-E)</td>
    </tr>
    <tr>
        <td>Bens Móveis</td><td align="right">'.formatNumero($bens_mov_ini, 2).'</td><td align="right">'.formatNumero($bens_mov_aqui,2).'</td><td align="right">'.formatNumero($bens_mov_reav_aqui,2).'</td><td align="right">'.formatNumero($bens_mov_baixa,2).'</td><td align="right">'.formatNumero($bens_mov_reav_baixa,2).'</td><td align="right">'.formatNumero($bens_mov_fin,2).'</td>
    </tr>
    <tr>
    <td>Bens Imóveis</td><td align="right">'.formatNumero($bens_imov_ini,2).'</td><td align="right">'.formatNumero($bens_imov_aqui,2).'</td><td align="right">'.formatNumero($bens_imov_reav_aqui,2).'</td><td align="right">'.formatNumero($bens_imov_baixa,2).'</td><td align="right">'.formatNumero($bens_imov_reav_baixa,2).'</td><td align="right">'.formatNumero($bens_imov_fin,2).'</td>
    </tr>
    <tr>
    <td>Total</td><td align="right">'.formatNumero($bens_mov_ini+$bens_imov_ini,2).'</td><td align="right">'.formatNumero($bens_mov_aqui+$bens_imov_aqui,2).'</td><td align="right">'.formatNumero($bens_mov_reav_aqui+$bens_imov_reav_aqui,2).'</td><td align="right">'.formatNumero($bens_mov_baixa+$bens_imov_baixa,2).'</td><td align="right">'.formatNumero($bens_mov_reav_baixa+$bens_imov_reav_baixa,2).'</td><td align="right">'.formatNumero($bens_mov_fin+$bens_imov_fin,2).'</td>
    </tr>
    </table>';
    $pdf->writeHTML($html, true, false, true, false, '');

    $pdf->Ln(6);


    $html = '
    <table border="1" cellpadding="4">
    <tr>
        <td colspan="3">Nome: '.$nome_responsavel.'</td>
        <td colspan="2" align="center">Responsável pelos Bens Patrimoniais</td>
    </tr>
    <tr>
        <td>Matrícula: '.$matricula.'</td>
        <td>Data: '.date('d/m/Y').'</td>
        <td colspan="3">Assinatura:</td>
    </tr>
    <tr>
        <td width="60%" colspan="3">Declaro que os valores acima descritos guardam paridade com o constante nos registros contábeis OU não guardam paridade com o constante nos registros </td>
        <td width="20%" rowspan="2" align="center">Responsável pelo<br />Setor Contábil</td>
        <td width="20%" rowspan="2" align="center">CRC/RJ nº _____</td>
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


    // add a page
    //$pdf->AddPage();

    //$html = '
    //<hr />
    //<h3 style="text-align:center">Tutorial Modelo 26</h3>
    //<p><b><u>Valor Líquido Inicial no Ano Correspondente ao Registro Contábil (R$):</b></u>  Neste campo indicar o valor pelo qual o ativo foi contabilizado no exercício anterior após a dedução de qualquer depreciação acumulada e das perdas acumuladas por redução ao valor recuperável.</p>
    //<p><b><u>Entradas/Aquisições:</b></u>  Neste campo indicar o valor total das aquisições ocorridas durante o exercício de referência.</p>
    //<p><b><u>Entradas/Reavaliações:</b></u> Neste campo indicar o valor total das reavaliações positivas ocorridas durante o exercício de referência, tais como custo subsequente adicional e ajustes, dentre outros.</p>
    //<p><b><u>Saídas/Baixas:</b></u> Neste campo indicar o valor total das baixas ocorridas durante o exercício de referência.</p>
    //<p><b><u>Saídas/Reavaliações:</b></u> Neste campo indicar o valor total das reavaliações negativas ocorridas durante o exercício de referência, tais como ajustes, depreciação, amortização e exaustão, dentre outros.</p>
    //<p><b><u>Valor Líquido Final no Ano Correspondente ao Registro Contábil (R$):</b></u>  Neste campo indicar o  resultado do cálculo algébrico das colunas A, B, C, D e E.</p>
    //<hr />';
    //$pdf->writeHTML($html, true, false, true, false, '');


    // - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

    // reset pointer to the last page
    $pdf->lastPage();

    // ---------------------------------------------------------

    //Close and output PDF document
    $pdf->Output('pat2_relatoriolegalmodeloxxvi.pdf', 'I');

    //============================================================+
    // END OF FILE
    //============================================================+

}else{
    
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setCellValue('F1', 'TRIBUNAL DE CONTAS DO ESTADO DO RIO DE JANEIRO');
    $sheet->setCellValue('F3', 'RELAÇÃO DE DOCUMENTOS -ARTIGO 12 DA DELIBERAÇÃO TCE-RJ  N. 277/17');
    $sheet->setCellValue('C4', 'DEMONSTRATIVO DA MOVIMENTAÇÃO DOS BENS PATRIMONIAIS NO EXERCÍCIO');

    $sheet->setCellValue('A7', 'Orgão:');
    $sheet->setCellValue('B7', $instituicao);
    $sheet->setCellValue('J7', 'Município:');
    $sheet->setCellValue('L7', $municipio);
    $sheet->setCellValue('N7', 'Exercício:');
    $sheet->setCellValue('O7', $exercicio);

    $sheet->setCellValue('A10', 'Tipo');
    $sheet->setCellValue('D10', 'Valor Líquido Inicial no Ano');
    $sheet->setCellValue('J10', 'Movimentação do Período no Ano');
    $sheet->setCellValue('Q10', 'Saldo em');

    $sheet->setCellValue('D11', 'Correspondente ao Registro Contábil (R$)');
    $sheet->setCellValue('I11', 'ENTRADAS (R$)');
    $sheet->setCellValue('N11', 'SAÍDAS (R$)');

    $sheet->setCellValue('E14', '(A)');
    $sheet->setCellValue('H14', '(B)');
    $sheet->setCellValue('I14', '(C)');
    $sheet->setCellValue('L14', '(D)');
    $sheet->setCellValue('N14', '(E)');
    $sheet->setCellValue('P14', '(F = A+B+C-D-E)');

    $sheet->setCellValue('A18', 'MOVEIS');
    $sheet->setCellValue('E18', formatNumero($bens_mov_ini,2));
    $sheet->setCellValue('H18', formatNumero($bens_mov_aqui,2));
    $sheet->setCellValue('I18', formatNumero($bens_mov_reav_aqui,2));
    $sheet->setCellValue('L18', formatNumero($bens_mov_baixa,2));
    $sheet->setCellValue('N18', formatNumero($bens_mov_reav_baixa,2));
    $sheet->setCellValue('P18', formatNumero($bens_mov_fin,2));

    $sheet->setCellValue('A19', 'IMOVEIS');
    $sheet->setCellValue('E19', formatNumero($bens_imov_ini,2));
    $sheet->setCellValue('H19', formatNumero($bens_imov_aqui,2));
    $sheet->setCellValue('I19', formatNumero($bens_imov_reav_aqui,2));
    $sheet->setCellValue('L19', formatNumero($bens_imov_baixa,2));
    $sheet->setCellValue('N19', formatNumero($bens_imov_reav_baixa,2));
    $sheet->setCellValue('P19', formatNumero($bens_imov_fin,2));

    $sheet->setCellValue('A20', 'TOTAL');
    $sheet->setCellValue('E20', formatNumero($bens_imov_ini+$bens_mov_ini,2));
    $sheet->setCellValue('H20', formatNumero($bens_imov_aqui+$bens_mov_aqui,2));
    $sheet->setCellValue('I20', formatNumero($bens_imov_reav_aqui+$bens_mov_reav_aqui,2));
    $sheet->setCellValue('L20', formatNumero($bens_imov_baixa+$bens_mov_baixa,2));
    $sheet->setCellValue('N20', formatNumero($bens_imov_reav_baixa+$bens_mov_reav_baixa,2));
    $sheet->setCellValue('P20', formatNumero($bens_imov_fin+$bens_mov_fin,2));

    $sheet->setCellValue('A28', 'Nome:');
    $sheet->setCellValue('B28', $nome_responsavel);
    $sheet->setCellValue('N28', 'Responsável pelos bens patrimoniais');

    $sheet->setCellValue('A30', 'Matricula:');
    $sheet->setCellValue('B30', $matricula);
    $sheet->setCellValue('G30', 'Data:');
    $sheet->setCellValue('H30', date('d/m/Y'));
    $sheet->setCellValue('I30', 'Assinatura:');

    $sheet->setCellValue('A32', 'Declaro que os valores acima descritos guardam paridade com o constante nos registros contábeis OU não guardam paridade ');
    $sheet->setCellValue('A33', 'com o constante  nos registros.');
    $sheet->setCellValue('M33', 'Responsável pelo valor Contábil');
    $sheet->setCellValue('P33', 'CRC-RJ n. _________');

    $sheet->setCellValue('A35', 'Nome:');
    $sheet->setCellValue('A37', 'Matricula:');
    $sheet->setCellValue('B37', '');
    $sheet->setCellValue('G37', 'Data:');
    $sheet->setCellValue('H37', '___/____/______');
    $sheet->setCellValue('I37', 'Assinatura:');



    $writer = new Xlsx($spreadsheet);
    $writer->save('xsl_modeloxxvi.xlsx');
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment; filename="xsl_modeloxxvi.xlsx"');
    $writer->save("php://output");

}

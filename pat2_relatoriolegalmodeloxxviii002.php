<?php

//https://tcpdf.org/
//https://github.com/tecnickcom/tcpdf
//https://github.com/tecnickcom/TCPDF.git
//cd libs/TCPDF [tcpdf.php, tcpdf_autoconfig.php]

//https://getcomposer.org/download/
// Composer
//php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
//php -r "if (hash_file('sha384', 'composer-setup.php') === 'e0012edf3e80b6978849f5eff0d4b4e4c79ff1609dd1e613307e16318854d24ae64f26d17af3ef0bf7cfb710ca74755a') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"
//php composer-setup.php
//php -r "unlink('composer-setup.php');"

//https://imasters.com.br/back-end/criando-planilhas-em-php-com-o-phpspreadsheet
// cd libs/PhpSpreadsheet [autoload.php]
// composer require phpoffice/phpspreadsheet


//require 'libs/PhpSpreadsheet/autoload.php'; //autoload do projeto
//use PhpOffice\PhpSpreadsheet\Spreadsheet; //classe responsável pela manipulação da planilha
//use PhpOffice\PhpSpreadsheet\Writer\Xlsx; //classe que salvará a planilha em .xlsx

function formatNumero($numero, $casas = 2){
    return number_format($numero, $casas, ',', '.');
}

$format_rel = "pdf";
if (isset($_GET['format'])){
    $format_rel = $_GET['format'];
}


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



$result = pg_exec ($conexao, 
" select t52_ident, t52_descr, t55_baixa,  t52_valaqu, t52_codcla, t51_descr, t55_obs ".

", COALESCE((select t44_valoratual from BENSDEPRECIACAO vl where vl.t44_sequencial=(select max(t44_sequencial) ".
" from BENSDEPRECIACAO sq where sq.t44_bens=t52_bem)),t52_valaqu) t58_valoratual ".

" from Bens inner join clabens on t64_codcla=t52_codcla and t64_instit=t52_instit ".
"      inner join BENSBAIX on t55_codbem = t52_bem ".
"      inner join BENSMOTBAIXA on t51_motivo = t55_motivo ".

" where t52_instit = ". db_getsession("DB_instit") .
" and t55_baixa >= '".$dt_ini."' and t55_baixa <= '".$dt_fin."'".
" order by t52_descr");
$qtd_linhas = pg_numrows($result);
$max_linhas = 8;
//$max_linhas = 7;
//$id_linha = $max_linhas +1;
$id_linha = $max_linhas +1;
$id_row = 0;
$total_custos = 0;

//$xxx = pg_fetch_all($result);
//echo "<pre>";
//print_r($xxx);
//echo "</pre>";
//die("Confere");

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
    $pdf->SetTitle('Relatório Legal XXVIII');
    $pdf->SetSubject('Relatório Legal XXVIII');
    $pdf->SetKeywords('Patrimônio, Relatório, Legal, XXVIII');

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

    $consulta = pg_fetch_all($result);
    $contador = 0;
    $conta_linhas = 0;
    $registros_por_pagina = 7;

    if ($qtd_linhas) {
        foreach ($consulta as $linha){
            if ($contador == 0){
                $pdf->SetFont('dejavusans', '', 10);                
                $pdf->AddPage();

                $html = '
                <table><tr style="text-align:center;">
                    <td>MODELO 28</td>
                </tr></table>
                <br /><br />
                <table border="1" cellpadding="4"><tr style="text-align:center;">
                    <td>TERMO DE BAIXA DEFINITIVA DE BENS PATRIMONIAIS</td>
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
                    <td width="10%">Número Inventariação</td>
                    <td width="40%">Descrição do Bem</td>
                    <td width="10%">Data da Baixa</td>
                    <td width="30%">Motivo da Baixa</td>
                    <td width="10%">Valor (R$)</td>
                </tr>';
            }

                
            $nome_bem = "-";
            if (!is_null($linha["t52_descr"])){
                if (trim($linha["t52_descr"]) != ""){
                    $nome_bem = trim($linha["t52_descr"]);
                }
            }
            $data_bx= date_format(date_create($linha["t55_baixa"]), 'd/m/Y');
            //$custo_atual = formatNumero($linha["t58_valoratual"], 2);
            $custo_atual = formatNumero($linha["t52_valaqu"], 2);
            $html .= utf8_encode('
                <tr><td align="center" height="30">'.$linha["t52_ident"].'</td><td width="40%">'.$nome_bem.'</td><td align="center">'.$data_bx.'</td><td>'.$linha["t51_descr"].' - '.$linha["t55_obs"].'</td><td align="center">'.$custo_atual.'</td></tr>');
            //$total_custos += $linha["t58_valoratual"];
            $total_custos += $linha["t52_valaqu"];
            $contador++;
            $conta_linhas++;


            if($contador == $registros_por_pagina || $conta_linhas == $qtd_linhas){
                if($conta_linhas == $qtd_linhas){
                    $html .= '
                    <tr><td>Total</td><td bgcolor="#f0f0f0"></td><td bgcolor="#f0f0f0"></td><td bgcolor="#f0f0f0"></td><td align="right">'. formatNumero($total_custos, 2) .'</td></tr>
                    </table>';
                    $pdf->writeHTML($html, true, false, true, false, '');
                } else {
                    $html .= '
                    </table>';
                    $pdf->writeHTML($html, true, false, true, false, '');
                }
                $html = '
                <table border="1" cellpadding="4">
                <tr>
                    <td width="50%" colspan="2">Nome:<br />'.$nome_responsavel.'</td>
                    <td width="50%" colspan="3" align="center">Responsável pelos Bens Patrimoniais</td>
                </tr>
                <tr>
                    <td width="25%">Matrícula:<br />'.$matricula.'</td>
                    <td width="25%">Data:<br />'.date('d/m/Y').'</td>
                    <td width="50%" colspan="3">Assinatura:</td>
                </tr>
                </table>';
                $pdf->writeHTML($html, true, false, true, false, '');
                $contador = 0;
            }
        }//Fim foreach
    }else{
        $pdf->AddPage();
    }
    pg_close ($conexao);


    // add a page
    //$pdf->AddPage();

    //$html = '
    //<hr />
    //<h3 style="text-align:center">Tutorial Modelo 28</h3>
    //<p><u><b>Motivo da Baixa</b></u> -  Neste campo indicar o motivo da baixa, através das opções: alienação por venda, doação, inutilização ou abandono, extravo/perda, furto/roubo, lançamentos indevidos e outros.</p>
    //<hr />';
    //$pdf->writeHTML($html, true, false, true, false, '');


    // - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

    // reset pointer to the last page
    $pdf->lastPage();

    // ---------------------------------------------------------

    //Close and output PDF document
    $pdf->Output('pat2_relatoriolegalmodeloxxviii.pdf', 'I');

    //============================================================+
    // END OF FILE
    //============================================================+

}else{

$consulta = pg_fetch_all($result);
$contador = 0;
$conta_linhas = 0;
$registros_por_pagina = 7;



header("Content-Type:   application/vnd.ms-excel; charset=utf-8");
header("Content-type:   application/x-msexcel; charset=utf-8");
header("Content-Disposition: attachment; filename=relmodeloxxviii.xls");
header("Expires: 0");
header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
header("Cache-Control: private",false);



if ($qtd_linhas) {
    foreach ($consulta as $linha){
        if ($contador == 0){
            print "<table>";
            print "<tr style='text-align:center'>";
            print "<center>";            
            print "<td colspan='12' rowspan='2' style='text-align:center'>MODELO 28</td>";
            print "</center>";
            print "</tr>";
            print "</table>";

            print "<br>";

            print "<table border='1' cellpadding='4'>";
            print "<tr style='text-align:center'>";
            print "<center>";            
            print "<td colspan='12' rowspan='2'>TERMO DE BAIXA DEFINITIVA DE BENS PATRIMONIAIS</td>";
            print "</center>";            
            print "</tr>";
            print "</table>";

            print "<br>";

            print "<table>";
            print "<tr>";
            print "<td> </td>";
            print "</tr>";
            print "</table>";
            
            print "<table border='1' cellpadding='4'>";
            print "<tr style='text-align:left'>";
            print "<td colspan=6>". utf8_decode("Órgão: " .$instituicao)."</td>";
            print "<td colspan=3>".utf8_decode("Município: " .$municipio)."</td>";
            print "<td colspan=3>". utf8_decode("Exercício: " . $exercicio) ."</td>";
            print "</tr>";
            print "</table>";

            print "<br>";
            
            print "<table border='1' cellpadding='4'>";
            print "<tr style='text-align:center'>";
            print "<td>N&deg; Inventaria&ccedil;&atilde;o</td>";
            print "<td colspan='5'>Descri&ccedil;&atilde;o do Bem</td>";
            print "<td>Data da Baixa</td>";
            print "<td colspan='4'>Motivo da Baixa</td>";
            print "<td>Valor (R$)</td>";
            print "</tr>";
        }
                
        $nome_bem = "-";
        if (!is_null($linha["t52_descr"])){
            if (trim($linha["t52_descr"]) != ""){
                $nome_bem = trim($linha["t52_descr"]);
                $nome_bem1 = substr($nome_bem, 0, 50);
                $nome_bem2 = substr($nome_bem, 51);
            }
        }
        $data_bx= date_format(date_create($linha["t55_baixa"]), 'd/m/Y');        
        $custo_atual = formatNumero($linha["t52_valaqu"], 2);
        
        print "<tr>";
        print "<td>".$linha["t52_ident"]."</td>";
        //print "<td colspan='5'>".$nome_bem."</td>";
        print "<td colspan='5'>".$nome_bem1."<br>".$nome_bem2."</td>";
        print "<td>".$data_bx."</td>";
        print "<td colspan='4'>".$linha["t51_descr"]." <br> ".$linha["t55_obs"]."</td>";
        print "<td>".$custo_atual."</td>";
        print "</tr>";        
        
        $total_custos += $linha["t52_valaqu"];
        $contador++;
        $conta_linhas++;

        if($contador == $registros_por_pagina || $conta_linhas == $qtd_linhas){
            if($conta_linhas == $qtd_linhas){
                print "<tr>";
                print "<td>TOTAL</td>";
                print "<td colspan='10' bgcolor='#f0f0f0'></td>";
                //print "<td bgcolor='#f0f0f0'></td>";
                //print "<td bgcolor='#f0f0f0'></td>";
                print "<td align='right'>". formatNumero($total_custos, 2) ."</td>";
                print "</tr>";
                print "</table>";
                } else {
                    print "</table>";
                } 

                print "<table>";
                print "<tr>";
                print "<td> </td>";
                print "</tr>";
                print "</table>";
                
                print "<table border='1' cellpadding='4'>";
                print "<tr>";
                print "<td colspan='6'>Nome:<br />".$nome_responsavel."</td>";
                print "<td colspan='6' align='center'>Respons&aacute;vel pelos Bens Patrimoniais</td>";
                print "</tr>";
                print "<tr>";
                print "<td colspan='3'>Matr&iacute;cula:<br />".$matricula."</td>";
                print "<td colspan='3'>Data:<br />".date('d/m/Y')."</td>";
                print "<td colspan='6'>Assinatura:</td>";
                print "</tr>";
                print "</table>";
                
                $contador = 0;
            }
        }//Fim foreach
}
}//FIM XLS

pg_close ($conexao);
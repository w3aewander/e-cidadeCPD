<?php

//require 'libs/PhpSpreadsheet/autoload.php'; //autoload do projeto
//use PhpOffice\PhpSpreadsheet\Spreadsheet; //classe responsável pela manipulação da planilha
//use PhpOffice\PhpSpreadsheet\Writer\Xlsx; //classe que salvará a planilha em .xlsx

function formatNumero($numero, $casas = 2){
    return number_format($numero, $casas, ',', '.');;
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
" select t52_ident, t52_descr, t52_dtaqu,  t52_valaqu, t52_codcla, t52_obs ".
" from Bens inner join clabens on t64_codcla=t52_codcla and t64_instit=t52_instit ".
" where t52_instit = ". db_getsession("DB_instit") ." and t64_bemtipos=2 ".
" and t52_dtaqu >= '".$dt_ini."' and t52_dtaqu <= '".$dt_fin."'".
" and not exists (select * from bensbaix where t55_codbem = t52_bem) order by t52_ident::numeric");

/*
if(db_getsession("DB_instit") == 30){
    $result = pg_exec ($conexao, "select t52_ident, t52_descr, t52_dtaqu, t52_valaqu, t52_obs from bens inner join clabens on t64_codcla = t52_codcla and t52_dtaqu between '".$dt_ini."' and '".$dt_fin."' and t52_instit = 30 AND t64_bemtipos = 2");
}elseif(db_getsession("DB_instit") == 96)){
    $result = pg_exec ($conexao, "select t52_ident, t52_descr, t52_dtaqu, t52_valaqu, t52_obs from bens inner join clabens on t64_codcla = t52_codcla and t52_dtaqu between '".$dt_ini."' and '".$dt_fin."' and t52_instit = 96 AND t64_bemtipos = 2");
}
*/

$result = pg_exec ($conexao, "select t52_ident, t52_descr, t52_dtaqu, t52_valaqu, t52_obs from bens inner join clabens on t64_codcla = t52_codcla and t52_dtaqu between '".$dt_ini."' and '".$dt_fin."' and t52_instit = ".db_getsession("DB_instit")." AND t64_bemtipos = 2");


$qtd_linhas = pg_numrows($result);
$max_linhas = 4;
$id_linha = $max_linhas +1;
$id_row = 0;
$total_custos = 0;



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
    $pdf->SetTitle('Relatório Legal XXIV');
    $pdf->SetSubject('Relatório Legal XXIV');
    $pdf->SetKeywords('Patrimônio, Relatório, Legal, XXIV');

    // set default header data
    $pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE.' 006', PDF_HEADER_STRING);

    // set header and footer fonts
    $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
    $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

    // set default monospaced font
    $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

    // set margins
    $margin_top = 9;
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


    $ano = explode("-", $dt_ini);
    $ano = $ano[0];
    
    
    $verificaInstituicao = db_getsession("DB_instit");
    if($verificaInstituicao == 45){
        if($ano < 2020){
            $registros_por_pagina = 4;    
        } else {
            $registros_por_pagina = 6;    
        }
        
    } else{
        $registros_por_pagina = 3;
    }

    $consulta = pg_fetch_all($result);    
    $contador = 0;
    $conta_linhas = 0;
    $nro_pagina = 1;
    $valor_pagina = 0;
    
    // ---------------------------------------------------------

    // havendo imóveis para a instituição
if ($qtd_linhas) {
    foreach ($consulta as $linha) {
        if($ano == 2018 && $linha["t52_ident"] == 16){                
            $linha["t52_valaqu"] = "1272686.57";
        }
            
        if($contador == 0) {
            $pdf->SetFont('dejavusans', '', 8);
            $pdf->AddPage();

            $html = '
            <table><tr style="text-align:center;">                    
                <td width="5%"></td><td width="90%">MODELO 24</td><td  align="right" width="5%">'.$nro_pagina.'</td>
            </tr></table>
            <br /><br />
            <table border="1" cellpadding="4"><tr style="text-align:center;">
                <td>ARROLAMENTO DOS BENS IMÓVEIS</td>
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
                <td width="10%">Número de<br />Inventariação</td>
                <td width="30%">Descrição do Bem</td>
                <td width="10%">Data da<br />Aquisição</td>
                <td width="10%">Valor de<br />Custo (R$)</td>
                <td width="40%">Observações</td>
            </tr>';
            $nro_pagina++;
        }
        
        $nome_bem = "-";
        if (!is_null($linha["t52_descr"])){
            if (trim($linha["t52_descr"]) != ""){
                $nome_bem = trim($linha["t52_descr"]);
            }
        }
                
        $data_aqui = date_format(date_create($linha["t52_dtaqu"]), 'd/m/Y');
        $custo_aqui = formatNumero($linha["t52_valaqu"], 2);
        $html .= utf8_encode('
        <tr>
        <td align="center" height="30">'.$linha["t52_ident"].'</td>
        <td width="30%">'.$nome_bem.'</td>
        <td align="center">'.$data_aqui.'</td>
        <td align="center">'.$custo_aqui.'</td>');

        if(strlen(trim($linha["t52_obs"])) > 290){
            $linha["t52_obs"] = substr($linha["t52_obs"], 0, 285) . "(...)";
        }
        $html .="<td>".utf8_encode(trim($linha["t52_obs"]))."</td>";
        //$html .= '<td>'.$linha["t52_obs"].'</td>';

        $html .="</tr>";
        
        $total_custos += $linha["t52_valaqu"];

        $contador++;
        $conta_linhas++;

        if($contador == $registros_por_pagina || $conta_linhas == $qtd_linhas){
            if($conta_linhas == $qtd_linhas){
                $html .= '
        <tr><td>Total</td><td bgcolor="#f0f0f0"></td><td bgcolor="#f0f0f0"></td><td align="right">'. formatNumero($total_custos, 2) .'</td><td bgcolor="#f0f0f0"></td></tr>
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
            <td colspan="3">Nome:<br />'.$nome_responsavel.'</td>
            <td colspan="2" align="center">Responsável pelos Bens Patrimoniais</td>
        </tr>
        <tr>
            <td>Matrícula:<br />'.$matricula.'</td>
            <td>Data:<br/>'.date('d/m/Y').'</td>
            <td colspan="3">Assinatura:</td>
        </tr>
        <tr>
            <td width="60%" colspan="2">Declaro que os valores acima descritos guardam paridade com os valores de aquisição dos bens imóveis, não considerando as reavalizações realizadas OU não guardam paridade com os valores de aquisição dos bens imóveis, sendo que as diferenças não se referem as reavalizações realizadas, conforme apontado em Notas Explicativas.</td>
            <td width="20%" colspan="2" rowspan="2" align="center">Responsável pelo<br />Setor Contábil</td>
            <td width="20%" rowspan="2" align="center">CRC/RJ<br />nº _____</td>
        </tr>
        <tr>
            <td colspan="2">Nome:</td>
        </tr>
        <tr>
            <td>Matrícula:</td>
            <td>Data: __/__/____</td>
            <td colspan="3">Assinatura:</td>
        </tr>
        </table>';
            $pdf->writeHTML($html, true, false, true, false, '');
            $contador = 0;
        }//Fim do if
    }//Fim do foreach
        

    }else{
       $pdf->writeHTML('<p>Não foi encontrato conteúdo</p>', true, false, true, false, ''); 
       $pdf->AddPage();
    }

   

    

    // reset pointer to the last page
    $pdf->lastPage();

    // ---------------------------------------------------------

    //Close and output PDF document
    $pdf->Output('pat2_relatoriolegalmodeloxxiv.pdf', 'I');

    //============================================================+
    // END OF FILE
    //============================================================+

}else{
    $ano = explode("-", $dt_ini);
    $ano = $ano[0];
    
    
    $verificaInstituicao = db_getsession("DB_instit");
    if($verificaInstituicao == 45){
        if($ano < 2020){
            $registros_por_pagina = 4;    
        } else {
            $registros_por_pagina = 6;    
        }
        
    } else{
        $registros_por_pagina = 3;
    }

    $consulta = pg_fetch_all($result);    
    $contador = 0;
    $conta_linhas = 0;
    $nro_pagina = 1;
    $valor_pagina = 0;

header("Content-Type:   application/vnd.ms-excel; charset=utf-8");
header("Content-type:   application/x-msexcel; charset=utf-8");
header("Content-Disposition: attachment; filename=relmodeloxxiv.xls");
header("Expires: 0");
header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
header("Cache-Control: private",false);


if ($qtd_linhas) {
    foreach ($consulta as $linha) {
        if($ano == 2018 && $linha["t52_ident"] == 16){                
            $linha["t52_valaqu"] = "1272686.57";
        }
            
        if($contador == 0) {
            print "<table>";
            print "<tr style='text-align:center'>";
            print "<center>";            
            print "<td colspan='12' rowspan='2' style='text-align:center'>MODELO 24</td>";
            print "</center>";
            print "</tr>";
            print "</table>";

            print "<br>";

            print "<table border='1' cellpadding='4'>";
            print "<tr style='text-align:center'>";
            print "<center>";            
            print "<td colspan='12' rowspan='2'>ARROLAMENTO DOS BENS IM&Oacute;VEIS </td>";
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
            print "<td colspan='4'>Descri&ccedil;&atilde;o do Bem</td>";
            print "<td>Data da Aquisi&ccedil;&atilde;o</td>";
            print "<td>Valor de Custo (R$)</td>";
            print "<td colspan='5'>Observa&ccedil;&otilde;es</td>";
            print "</tr>";
            
            $nro_pagina++;
        }
        
        $nome_bem = "-";
        if (!is_null($linha["t52_descr"])){
            if (trim($linha["t52_descr"]) != ""){
                $nome_bem = trim($linha["t52_descr"]);
                $nome_bem1 = substr($nome_bem, 0, 50);
                $nome_bem2 = substr($nome_bem, 51);
            }
        }
        $obs1 = substr($linha["t52_obs"], 0, 50);
        $obs2 = substr($linha["t52_obs"], 51, 50);
        $obs3 = substr($linha["t52_obs"], 101);
                
        $data_aqui = date_format(date_create($linha["t52_dtaqu"]), 'd/m/Y');
        $custo_aqui = formatNumero($linha["t52_valaqu"], 2);
        
        print "<tr>";
        print "<td>".$linha["t52_ident"]."</td>";
        //print "<td colspan='5'>".$nome_bem."</td>";
        print "<td colspan='4'>".$nome_bem1."<br>".$nome_bem2."</td>";
        print "<td>".$data_aqui."</td>";
        print "<td>".$custo_aqui."</td>";
        //print "<td colspan='4'>".$linha["t52_obs"]."</td>";
        print "<td colspan='5'>".$obs1."<br>".$obs2."<br>".$obs3."</td>";
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
            print "<td colspan='8'>Nome:<br />".$nome_responsavel."</td>";
            print "<td colspan='4' align='center'>Respons&aacute;vel pelos Bens Patrimoniais</td>";
            print "</tr>";
            print "<tr>";
            print "<td colspan='2'>Matr&iacute;cula:<br />".$matricula."</td>";
            print "<td colspan='2'>Data:<br />".date('d/m/Y')."</td>";
            print "<td colspan='8'>Assinatura:</td>";
            print "</tr>";

            print "<tr>";
            print "<td colspan='8'>Declaro que os valores acima descritos guardam paridade com os valores de aquisi&ccedil;&atilde;o dos bens im&oacute;veis, n&atilde;o<br>
            considerando as reavaliza&ccedil;&otilde;es realizadas OU n&atilde;o guardam paridade com os valores de aquisi&ccedil;&atilde;o dos bens<br>
            im&oacute;veis, sendo que as diferen&ccedil;as n&atilde;o se referem as reavaliza&ccedil;&otilde;es realizadas, conforme apontado em Notas<br>
            Explicativas.
            </td>";
            print "<center>";
            print "<td colspan='2'>Respons&aacute;vel pelo<br>Setor Cont&aacute;bil</td>";
            print "<td colspan='2'>CRC/RJ<br>N&deg;______</td>";
            print "</center>";
            print "</tr>";

            print "<tr>";
            print "<td colspan='8'>Nome:</td>";
            print "</tr>";

            print "<tr>";
            print "<td colspan='3'>Matr&iacute;cula:</td>";
            print "<td colspan='3'>Data: __/__/____</td>";
            print "<td colspan='6'>Assinatura:</td>";
            print "</tr>";
            print "</table>";



            /*$html = '
        <table border="1" cellpadding="4">
        <tr>
            <td colspan="3">Nome:<br />'.$nome_responsavel.'</td>
            <td colspan="2" align="center">Responsável pelos Bens Patrimoniais</td>
        </tr>
        <tr>
            <td>Matrícula:<br />'.$matricula.'</td>
            <td>Data:<br/>'.date('d/m/Y').'</td>
            <td colspan="3">Assinatura:</td>
        </tr>
        <tr>
            <td width="60%" colspan="2">Declaro que os valores acima descritos guardam paridade com os valores de aquisição dos bens imóveis, não considerando as reavalizações realizadas OU não guardam paridade com os valores de aquisição dos bens imóveis, sendo que as diferenças não se referem as reavalizações realizadas, conforme apontado em Notas Explicativas.</td>
            <td width="20%" colspan="2" rowspan="2" align="center">Responsável pelo<br />Setor Contábil</td>
            <td width="20%" rowspan="2" align="center">CRC/RJ<br />nº _____</td>
        </tr>
        <tr>
            <td colspan="2">Nome:</td>
        </tr>
        <tr>
            <td>Matrícula:</td>
            <td>Data: __/__/____</td>
            <td colspan="3">Assinatura:</td>
        </tr>
        </table>';*/
            
            $contador = 0;
        }//Fim do if
    }//Fim do foreach
}//if da quantidade de linhas

}

pg_close ($conexao);

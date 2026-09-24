<?php


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



/*$result = pg_exec ($conexao, 
" select t52_ident, t52_descr, t52_dtaqu,  t52_valaqu, t52_codcla, t52_obs ".
" from Bens inner join clabens on t64_codcla=t52_codcla and t64_instit=t52_instit ".
" where t52_instit = ". db_getsession("DB_instit") ." and t64_bemtipos=1 ".
" and t52_dtaqu >= '".$dt_ini."' and t52_dtaqu <= '".$dt_fin."'".
" order by t52_ident");*/

/*$result = pg_exec ($conexao, 
"select t52_ident, t52_descr, t52_dtaqu,  t52_valaqu, t52_codcla, t52_obs from bens inner join db_depart on db_depart.coddepto = bens.t52_depart left join bensdiv on t52_bem = t33_bem left join departdiv on t33_divisao = t30_codigo inner join clabens on clabens.t64_codcla = bens.t52_codcla inner join clabensconplano on clabensconplano.t86_clabens = clabens.t64_codcla and clabensconplano.t86_anousu = 2021 inner join conplano on conplano.c60_codcon = clabensconplano.t86_conplano and conplano.c60_anousu = 2021 inner join db_departorg on db_departorg.db01_coddepto = db_depart.coddepto and db_departorg.db01_anousu = 2021 left join benscedente on t09_bem = t52_bem left join benscadcedente on t09_benscadcedente = t04_sequencial left join bensmater on t53_codbem = t52_bem left join bensimoveis on t54_codbem = t52_bem left join bensbaix on t55_codbem = t52_bem left join bensdepreciacao on t44_bens = t52_bem where t52_instit = ". db_getsession("DB_instit") ." and t55_codbem is null and t52_dtaqu between '".$dt_ini."' and '".$dt_fin."' order by db01_orgao, db01_coddepto, t52_depart, t30_codigo, t64_class, t52_ident::numeric");*/
$result = pg_exec ($conexao, 
"select DISTINCT t52_ident::numeric, t52_descr, t52_dtaqu,  t52_valaqu, t52_valaquhist, t52_codcla, t52_obs from bens inner join db_depart on db_depart.coddepto = bens.t52_depart left join bensdiv on t52_bem = t33_bem left join departdiv on t33_divisao = t30_codigo inner join clabens on clabens.t64_codcla = bens.t52_codcla inner join clabensconplano on clabensconplano.t86_clabens = clabens.t64_codcla and clabensconplano.t86_anousu = 2021 inner join conplano on conplano.c60_codcon = clabensconplano.t86_conplano and conplano.c60_anousu = 2021 inner join db_departorg on db_departorg.db01_coddepto = db_depart.coddepto and db_departorg.db01_anousu = 2021 left join benscedente on t09_bem = t52_bem left join benscadcedente on t09_benscadcedente = t04_sequencial left join bensmater on t53_codbem = t52_bem left join bensimoveis on t54_codbem = t52_bem left join bensbaix on t55_codbem = t52_bem left join bensdepreciacao on t44_bens = t52_bem where t52_instit = ". db_getsession("DB_instit") ." and t55_codbem is null AND t64_bemtipos = 1 and t52_dtaqu between '".$dt_ini."' and '".$dt_fin."' and not exists (select * from bensbaix where t55_codbem = t52_bem) order by t52_ident::numeric");
$qtd_linhas = pg_numrows($result);


$max_linhas = 5;
if($verificaInstituicao == 75){    
    $max_linhas = 12;
}

$id_linha = $max_linhas;
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
    define('K_TCPDF_THROW_EXCEPTION_ERROR', true);

    // set document information
    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetAuthor('...');
    $pdf->SetTitle('Relatório Legal XXIII');
    $pdf->SetSubject('Relatório Legal XXIII');
    $pdf->SetKeywords('Patrimônio, Relatório, Legal, XXIII');

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

    // ---------------------------------------------------------

    // havendo móveis para a instituição
    $paginaatual = 1;
    $totalpaginas = 1;
    $linhasNaPagina = 5;
    if($verificaInstituicao == 75){    
        $linhasNaPagina = 12;
    }
    if ($qtd_linhas / $linhasNaPagina > round($qtd_linhas / $linhasNaPagina)){
        $totalpaginas = round($qtd_linhas / $linhasNaPagina) + 1;
    }else{
        $totalpaginas = round($qtd_linhas / $linhasNaPagina);
    }

    
    $ano = explode("-", $dt_ini);
    $ano = $ano[0];
    

    function testa($var){
        echo "<pre>";
        print_r($var);
        echo "</pre>";
    }
    $verificaInstituicao = db_getsession("DB_instit");
    if($verificaInstituicao == 45){
        if($ano < 2020){
            //$registros_por_pagina = 4;    
            $registros_por_pagina = 3;
        } else {
            //$registros_por_pagina = 6;    
            $registros_por_pagina = 5;
        }
        
    } elseif($verificaInstituicao == 75){
        $registros_por_pagina = 12;
    } elseif($verificaInstituicao == 1){
        $registros_por_pagina = 3;
    } else{
        $registros_por_pagina = 6;
    }

    
    $consulta = pg_fetch_all($result);
    //testa($consulta); die("Verifica");
    $contador = 0;
    $conta_linhas = 0;
    $nro_pagina = 1;
    $valor_pagina = 0;
    

    if ($qtd_linhas) {

                
        foreach ($consulta as $linha) {
            if($contador == 0) {
                $pdf->SetFont('dejavusans', '', 8);
                $pdf->AddPage();

                $html = '
                <table><tr style="text-align:center;">
                    <td width="5%"></td><td width="90%">MODELO 23</td><td  align="right" width="5%">'.$nro_pagina.'</td>
                </tr></table>
                <br /><br />
                <table border="1" cellpadding="4"><tr style="text-align:center;">
                    <td>ARROLAMENTO DOS BENS MÓVEIS</td>
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
                    <td width="20%">Descrição do Bem</td>
                    <td width="10%">Data da<br />Aquisição</td>
                    <td width="10%">Valor de<br />Custo (R$)</td>
                    <td width="50%">Observações</td>
                </tr>';
                $nro_pagina++;
            }

            if(substr($linha['t52_dtaqu'], 0, 4) >= 2021){
                $novovalor = $linha["t52_valaqu"];
            } else {
                $novovalor = $linha["t52_valaquhist"];
            }
            
            $html .="<tr>";            
            $html .='<td align="center">'.$linha['t52_ident'].'</td>';
            $html .="<td width='20%'>".utf8_encode(trim($linha['t52_descr']))."</td>";
            $html .='<td align="center">'.implode("/", array_reverse(explode("-", $linha['t52_dtaqu']))).'</td>';
            //$html .='<td align="center">'.formatNumero($linha["t52_valaqu"], 2).'</td>';
            $html .='<td align="center">'.formatNumero($novovalor, 2).'</td>';
            if($verificaInstituicao == 75){
                $html .="<td></td>";    
            } else{
            	//$pdf->SetFont('dejavusans', '', 8);
            	if(strlen(trim($linha["t52_obs"])) > 290){
            		//$pdf->SetFont('dejavusans', '', 7);
            		$linha["t52_obs"] = substr($linha["t52_obs"], 0, 285) . "(...)";
            	}
                $html .="<td>".utf8_encode(trim($linha["t52_obs"]))."</td>";    
            }
             
            $html .="</tr>";
            //$valortotal += $linha["t52_valaqu"];
            //$valor_pagina += $linha["t52_valaqu"];
            $valortotal += $novovalor;
            $valor_pagina += $novovalor;
            
            $contador++;
            $conta_linhas++;

            if($contador == $registros_por_pagina || $conta_linhas == $qtd_linhas){

                if($conta_linhas == $qtd_linhas){
                    $html .= '<tr><td>Total</td><td colspan=4></td><td bgcolor="#f0f0f0"></td><td align="right">'. formatNumero($valortotal, 2) .'</td><td bgcolor="#f0f0f0" colspan=5></td></tr></table>';
                    $pdf->writeHTML($html, true, false, true, false, '');                    
                } else {
                    //$html .= '<tr><td>Total</td><td colspan=4></td><td bgcolor="#f0f0f0"></td><td align="right">'. formatNumero($valor_pagina, 2) .'</td><td bgcolor="#f0f0f0" colspan=5></td></tr>';
                    $html .= '
                    </table>';
                    //$valor_pagina = 0;
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
                    <td>Data:<br />'.date('d/m/Y').'</td>
                    <td colspan="3">Assinatura:</td>
                </tr>
                <tr>
                    <td width="60%" colspan="2">Declaro que os valores acima descritos guardam paridade com os valores de aquisição dos bens móveis, não considerando as reavalizações realizadas OU não guardam paridade com os valores de aquisição dos bens móveis, sendo que as diferenças não se referem as reavalizações realizadas, conforme apontado em Notas Explicativas.</td>
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
                
            }
            
        }
        


    }else{
        $pdf->AddPage();
        $pdf->writeHTML('<html><body>Sem resposta</body></html>', true, false, true, false, '');  
    }



    

    // - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

    // reset pointer to the last page
    $pdf->lastPage();

    // ---------------------------------------------------------

    //Close and output PDF document
    ob_end_clean();
    $pdf->Output('arrolamento_dos_bens_moveis.pdf', 'I');

    //============================================================+
    // END OF FILE
    //============================================================+

}else{

/*
* Criando e exportando planilhas do Excel
* /
*/

// Definimos o nome do arquivo que será exportado
$arquivo = 'relatoriolegalxxiii.xls';

// Criamos uma tabela HTML com o formato da planilha
$html = '<table><tr style="text-align:center;">
                    <td colspan=12>MODELO 23</td><td  align="right" width="5%">'.$paginaatual.'/'.$totalpaginas.'</td>
                </tr></table>
                <br /><br />
                <table border="1" cellpadding="4"><tr style="text-align:center;">
                    <td colspan=12>ARROLAMENTO DOS BENS MOVEIS</td>
                </tr></table>
                <br /><br />
                <table border="1" cellpadding="4">
                <tr style="text-align:left;">
                <td colspan=4>Orgao: '.$instituicao.'</td>
                <td colspan=4>Municipio: '.$municipio.'</td>
                <td colspan=4>Exercicio: '.$exercicio.'</td>
                </tr>
                </table>';

$html .= '<table><tr><td></td></tr></table>';


$html .= '<table border="1" cellpadding="4">
                <tr style="text-align:center;">
                    <td >Numero de<br />Inventariacao</td>
                    <td colspan=4>Descricao do Bem</td>
                    <td >Data da<br />Aquisicao</td>
                    <td >Valor de<br />Custo (R$)</td>
                    <td colspan=5>Observacoes</td>
                </tr>';

if ($qtd_linhas){
    $id_row = 0;

    while ($id_row < $qtd_linhas){

        $row = pg_fetch_array($result, $id_row);

        $nome_bem = "-";
                if (!is_null($row["t52_descr"])){
                    if (trim($row["t52_descr"]) != ""){
                        $nome_bem = trim($row["t52_descr"]);
                    }
        }

                $data_aqui = date_format(date_create($row["t52_dtaqu"]), 'd/m/Y');
                $custo_aqui = formatNumero($row["t52_valaqu"], 2);
        
        $html .= '<tr><td align="center">'.
        $row["t52_ident"].'</td><td colspan=4>'.
        $nome_bem.'</td><td align="center">'.
        $data_aqui.'</td><td align="center">'.
        $custo_aqui.'</td><td colspan=5>'.
        $row["t52_obs"].'</td></tr>';
        
        $total_custos += $row["t52_valaqu"];

        $id_row += 1;
    }
}

$html .= '<tr><td>Total</td><td colspan=4></td><td bgcolor="#f0f0f0"></td><td align="right">'. formatNumero($total_custos, 2) .'</td><td bgcolor="#f0f0f0" colspan=5></td></tr></table>';


$html .= '<table><tr><td></td></tr></table>';

$html .= '<table border="1" cellpadding="4">
                <tr>
                    <td colspan="6">Nome:<br />'.$nome_responsavel.'</td>
                    <td colspan="6" align="center">Responsavel pelos Bens Patrimoniais</td>
                </tr>
                <tr>
                    <td colspan=4>Matricula:<br />'.$matricula.'</td>
                    <td colspan=4>Data:<br />'.date('d/m/Y').'</td>
                    <td colspan="4">Assinatura:</td>
                </tr>
                <tr>
                    <td width="60%" colspan="6">Declaro que os valores acima descritos guardam paridade com os valores de aquisicao dos bens moveis, nao considerando as reavalizacoes realizadas OU nao guardam paridade com os valores de aquisicao dos bens moveis, sendo que as diferencas nao se referem as reavalizacoes realizadas, conforme apontado em Notas Explicativas.</td>
                    <td width="20%" colspan="3" rowspan="2" align="center">Responsavel pelo<br />Setor Contabil</td>
                    <td width="20%" colspan="3" rowspan="2" align="center">CRC/RJ<br />No _____</td>
                </tr>
                <tr>
                    <td colspan="6">Nome:</td>
                </tr>
                <tr>
                    <td colspan=4>Matricula:</td>
                    <td colspan=4>Data: __/__/____</td>
                    <td colspan="4">Assinatura:</td>
                </tr>
                </table>';

// Configurações header para forçar o download
header ("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header ("Last-Modified: " . gmdate("D,d M YH:i:s") . " GMT");
header ("Cache-Control: no-cache, must-revalidate");
header ("Pragma: no-cache");
header ("Content-type: application/x-msexcel");
header ("Content-Disposition: attachment; filename=\"{$arquivo}\"" );
header ("Content-Description: PHP Generated Data" );

// Envia o conteúdo do arquivo
echo ($html);

}

pg_close ($conexao);

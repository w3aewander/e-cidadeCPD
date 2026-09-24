<?php

require_once("libs/db_stdlib.php");
require_once("libs/db_conecta.php");
require_once("libs/db_sessoes.php");
require_once("libs/db_usuariosonline.php");

$ninst = db_getsession('DB_instit');

function formatNumero($numero, $casas = 2){
   return number_format($numero, $casas, ',', '.');;
}

function testa($var){
    echo "<pre>";
    print_r($var);
    echo "</pre>";
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


$anorelatorio = $_GET["exercicio"];
$anoanterior = $anorelatorio - 1;



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
                 " from BENSDEPRECIACAO sq where sq.t44_bens=t52_bem)),t52_valaqu) t58_valoratual, t52_bem ".

    " from Bens inner join clabens on t64_codcla=t52_codcla and t64_instit=t52_instit ".
    " where t52_instit = ". $inst ." and t64_bemtipos=".$tipo.
    " and t52_dtaqu >= '".$dtini."' and t52_dtaqu <= '".$dtfin."'".
    " order by t52_descr");
    $qtd_linhas = pg_numrows($result);
    $id_row = 0;

    

    $aqui = 0;
    $reav_aqui = 0;
    (float)$colunac = 0;
    if ($qtd_linhas){
        while ($id_row < $qtd_linhas){
            $row = pg_fetch_array($result, $id_row);
            $aqui += floatval($row['t52_valaqu']);

    
    
    $anoagora = explode("-", $dtfin);
    $anoagora = $anoagora[0];           

            

            $id_row += 1;
        }//Fim do while
        //NOVO CAMPO DE REAVALIAÇÃO
        $conferedata = explode("-", $dtini);
        $conferedata = $conferedata[0];
        
        if($conferedata == "1500"){
            $sqlCimoveis = pg_query($conn, "SELECT sum(t63_agregarvalor) as soma FROM benscorr INNER JOIN bens on t63_codbem = t52_bem INNER JOIN clabens ON t52_codcla = t64_codcla WHERE t63_exercicio <= {$anoagora} AND t63_dataprocessamento IS NOT NULL AND t64_bemtipos = {$tipo} AND t64_instit = {$ninst} ");
        } else{
            $sqlCimoveis = pg_query($conn, "SELECT sum(t63_agregarvalor) as soma FROM benscorr INNER JOIN bens on t63_codbem = t52_bem INNER JOIN clabens ON t52_codcla = t64_codcla WHERE t63_exercicio = {$anoagora} AND t63_dataprocessamento IS NOT NULL AND t64_bemtipos = {$tipo} AND t64_instit = {$ninst} ");
        }
        
        $resultadoimoveis = pg_fetch_all($sqlCimoveis);        
        $reav_aqui = $resultadoimoveis[0]["soma"];        
        
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
            

            $id_row += 1;
        }
    }

    
    return array( $aqui , $reav_aqui , $baixa , $reav_baixa );

}



//var_dump($totalcolunab); die("confere");


$novototal = 0;
    // Moveis Inicial
    list($bens_mov_aqui, $bens_mov_reav_aqui, $bens_mov_baixa, $bens_mov_reav_baixa) = calculaMovimetosBens(1, db_getsession("DB_instit"), "1500-01-01 00:00:00", $exercicio_anterior."-12-31 23:59:59", $conexao);
    
    $bens_mov_ini = $bens_mov_ini + $bens_mov_aqui + $bens_mov_reav_aqui - $bens_mov_baixa - $bens_mov_reav_baixa;
    //$bens_mov_ini = $totalcolunab;
    // Imoveis Inicial
    list($bens_imov_aqui, $bens_imov_reav_aqui, $bens_imov_baixa, $bens_imov_reav_baixa) = calculaMovimetosBens(2, db_getsession("DB_instit"), "1500-01-01 00:00:00", $exercicio_anterior."-12-31 23:59:59", $conexao);
    $bens_imov_ini = $bens_imov_ini + $bens_imov_aqui + $bens_imov_reav_aqui - $bens_imov_baixa - $bens_imov_reav_baixa;






    // Moveis
    list($bens_mov_aqui, $bens_mov_reav_aqui, $bens_mov_baixa, $bens_mov_reav_baixa) = calculaMovimetosBens(1, db_getsession("DB_instit"), $dt_ini, $dt_fin, $conexao);

    
//Coluna B - Bens móveis
$slqnovacolunaB = pg_query($conn, "SELECT case when t54_codbem is null then 'Material' else 'Imóvel' end as tipobem,case when t55_codbem is null then 'Não' else 'Sim' end as situacaobem,(select sum(t58_valorcalculado) from benshistoricocalculobem inner join benshistoricocalculo on t57_sequencial = t58_benshistoricocalculo where t58_bens = t52_bem and t57_ativo is true and t57_processado is true and t58_benstipodepreciacao <> 6) as valordepreciado,(t44_valoratual + t44_valorresidual) as valoratual,bens.*, db_depart.*, bensdiv.*, clabens.*, conplano.*, departdiv.*, benscedente.*, benscadcedente.*, bensmater.*, bensimoveis.*, bensbaix.*, bensdepreciacao.*,( select t70_descr from histbem inner join situabens on t70_situac = t56_situac where t56_codbem = t52_bem order by t56_histbem desc limit 1 ) as estadobem from bens inner join db_depart on db_depart.coddepto = bens.t52_depart left join bensdiv on t52_bem = t33_bem left join departdiv on t33_divisao = t30_codigo inner join clabens on clabens.t64_codcla = bens.t52_codcla inner join clabensconplano on clabensconplano.t86_clabens = clabens.t64_codcla and clabensconplano.t86_anousu = 2021 inner join conplano on conplano.c60_codcon = clabensconplano.t86_conplano and conplano.c60_anousu = 2021 inner join db_departorg on db_departorg.db01_coddepto = db_depart.coddepto and db_departorg.db01_anousu = 2021 left join benscedente on t09_bem = t52_bem left join benscadcedente on t09_benscadcedente = t04_sequencial left join bensmater on t53_codbem = t52_bem left join bensimoveis on t54_codbem = t52_bem left join bensbaix on t55_codbem = t52_bem left join bensdepreciacao on t44_bens = t52_bem where t52_instit = ". db_getsession("DB_instit") ." and t55_codbem is null and t52_dtaqu between '".$dt_ini."' and '".$dt_fin."' order by db01_orgao, db01_coddepto, t52_depart, t30_codigo, t64_class, t52_ident::numeric");
$novacolunaB = pg_fetch_all($slqnovacolunaB);
$totalcolunab = 0;

foreach ($novacolunaB as $linha) {
    $totalcolunab += $linha["t52_valaqu"];
}
$bens_mov_aqui = $totalcolunab;



if(db_getsession("DB_instit") == 30){
    $slqnovacolunaB = pg_query($conn, "SELECT SUM(t52_valaqu) as total from BENS inner join clabens on t64_codcla = t52_codcla where t52_instit = 30 and t52_dtaqu between '".$dt_ini."' and '".$dt_fin."' and t64_bemtipos = 1");
    $novacolunaB = pg_fetch_all($slqnovacolunaB);
    
    $bens_mov_aqui = $novacolunaB[0]["total"];
    
}




    $bens_mov_fin = $bens_mov_ini + $bens_mov_aqui + $bens_mov_reav_aqui - $bens_mov_baixa - $bens_mov_reav_baixa;
    // Imoveis
    list($bens_imov_aqui, $bens_imov_reav_aqui, $bens_imov_baixa, $bens_imov_reav_baixa) = calculaMovimetosBens(2, db_getsession("DB_instit"), $dt_ini, $dt_fin, $conexao);

    $bens_imov_fin = $bens_imov_ini + $bens_imov_aqui + $bens_imov_reav_aqui - $bens_imov_baixa - $bens_imov_reav_baixa;


    pg_close ($conexao);





/*
//$ninst = db_getsession('DB_instit');
//Coluna C - Bens móveis
//bens_mov_reav_aqui
$sqlCmoveis = pg_query($conn, "SELECT sum(t63_agregarvalor) as soma FROM benscorr INNER JOIN bens on t63_codbem = t52_bem INNER JOIN clabens ON t52_codcla = t64_codcla WHERE t63_exercicio = {$anorelatorio} AND t63_dataprocessamento IS NOT NULL AND t64_bemtipos = 1 AND t64_instit = {$ninst} ");
$resultadomoveis = pg_fetch_all($sqlCmoveis);
$novovalormoveis = $resultadomoveis[0]["soma"];
$bens_mov_reav_aqui = $novovalormoveis;


//Coluna C - Bens Imóveis
//bens_imov_reav_aqui
$sqlCimoveis = pg_query($conn, "SELECT sum(t63_agregarvalor) as soma FROM benscorr INNER JOIN bens on t63_codbem = t52_bem INNER JOIN clabens ON t52_codcla = t64_codcla WHERE t63_exercicio = {$anorelatorio} AND t63_dataprocessamento IS NOT NULL AND t64_bemtipos = 2 AND t64_instit = {$ninst} ");
$resultadoimoveis = pg_fetch_all($sqlCimoveis);
$novovalorimoveis = $resultadoimoveis[0]["soma"];
$bens_imov_reav_aqui = $novovalorimoveis;

*/








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
    
    header("Content-Type:   application/vnd.ms-excel; charset=utf-8");
header("Content-type:   application/x-msexcel; charset=utf-8");
header("Content-Disposition: attachment; filename=relmodeloxxvi.xls");
header("Expires: 0");
header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
header("Cache-Control: private",false);


print "<table>";
print "<tr style='text-align:center'>";
print "<center>";
print "<td colspan='11' rowspan='2' style='text-align:center'>MODELO 26</td>";
print "</center>";
print "</tr>";
print "</table>";

print "<table border='1'>";
print "<tr style='text-align:center'>";
print "<center>";
print "<td colspan='11' rowspan='2'>".utf8_decode("DEMONSTRATIVO DA MOVIMENTAÇÃO DOS BENS PATRIMONIAIS NO EXERCÍCIO")."</td>";
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
print "<td colspan=2>". utf8_decode("Exercício: " . $exercicio) ."</td>";
print "</tr>";
print "</table>";


print "<table border='1' cellpadding='4'>";
print "<tr style='text-align:center'>";
print "<td rowspan='4' colspan='2'>Tipo</td>";
print "<center>";
print "<td rowspan='3' colspan='2'>Valor ".utf8_decode("Líquido")." Inicial no Ano<br />Correspondente ao Registro<br />".utf8_decode("Contábil")." (R$)</td>";

print "<td colspan='4'>".utf8_decode("Movimentação do Período")." no Ano</td>";
print "<td rowspan='3' colspan='3'>Valor ".utf8_decode("Líquido")." Final no Ano<br />Correspondente ao<br />Registro ".utf8_decode("Contábil")."<br />(R$)</td>";
print "</center>";
print "</tr>";


print "<tr style='text-align:center'>";
print "<center>";
print "<td colspan='2'>Entradas (R$)</td>";
print "<td colspan='2'>Sa&iacute;das (R$)</td>";
print "</tr>";
print "<tr style='text-align:center'>";
print "<td>Aquisi&ccedil;&otilde;es</td>";
print "<td>Reavalia&ccedil;&otilde;es</td>";
print "<td>Baixas</td>";
print "<td>Reavalia&ccedil;&otilde;es</td>";
print "</tr>";
print "<tr style='text-align:center'>";
print "<td colspan='2'>(A)</td>";
print "<td>(B)</td>";
print "<td>(C)</td>";
print "<td>(D)</td>";
print "<td>(E)</td>";
print "<td colspan='3'>(F = A+B+C-D-E)</td>";
print "</center>";
print "</tr>";

print "<tr>";
print "<td colspan=2>Bens M&oacute;veis</td>";
print "<td colspan='2' align='right'>".formatNumero($bens_mov_ini, 2)."</td>";
print "<td align='right'>".formatNumero($bens_mov_aqui,2)."</td>";
print "<td align='right'>".formatNumero($bens_mov_reav_aqui,2)."</td>";
print "<td align='right'>".formatNumero($bens_mov_baixa,2)."</td>";
print "<td align='right'>".formatNumero($bens_mov_reav_baixa,2)."</td>";
print "<td colspan='3' align='right'>".formatNumero($bens_mov_fin,2)."</td>";
print "</tr>";    
    
print "<tr>";
print "<td colspan='2'>Bens Im&oacute;veis</td>";
print "<td colspan='2' align='right'>".formatNumero($bens_imov_ini,2)."</td>";
print "<td align='right'>".formatNumero($bens_imov_aqui,2)."</td>";
print "<td align='right'>".formatNumero($bens_imov_reav_aqui,2)."</td>";
print "<td align='right'>".formatNumero($bens_imov_baixa,2)."</td>";
print "<td align='right'>".formatNumero($bens_imov_reav_baixa,2)."</td>";
print "<td colspan='3' align='right'>".formatNumero($bens_imov_fin,2)."</td>";
print "</tr>";
    
print "<tr>";
print "<td colspan='2'>Total</td>";
print "<td colspan='2' align='right'>".formatNumero($bens_mov_ini+$bens_imov_ini,2)."</td>";
print "<td align='right'>".formatNumero($bens_mov_aqui+$bens_imov_aqui,2)."</td>";
print "<td align='right'>".formatNumero($bens_mov_reav_aqui+$bens_imov_reav_aqui,2)."</td>";
print "<td align='right'>".formatNumero($bens_mov_baixa+$bens_imov_baixa,2)."</td>";
print "<td align='right'>".formatNumero($bens_mov_reav_baixa+$bens_imov_reav_baixa,2)."</td>";
print "<td colspan='3' align='right'>".formatNumero($bens_mov_fin+$bens_imov_fin,2)."</td>";

print "</tr>";
print "</table>";


print "<br>";

print "<table>";
print "<tr>";
print "<td> </td>";
print "</tr>";
print "</table>";

print "<table border='1' cellpadding='4'>";
print "<tr>";
print "<td colspan='7'>Nome: ".utf8_decode($nome_responsavel)."</td>";
print "<center>";
print "<td colspan='4' align='center'>Respons&aacute;vel pelos Bens Patrimoniais</td>";
print "</center>";
print "</tr>";

print "<tr>";
print "<td colspan='2'>Matr&iacute;cula: ".$matricula."</td>";
print "<td colspan='2'>Data: ".date('d/m/Y')."</td>";
print "<td colspan='7'>Assinatura:</td>";
print "</tr>";

print "<tr>";
print "<td width='60%'' colspan='7'>Declaro que os valores acima descritos guardam paridade com o constante nos registros cont&aacute;beis OU n&atilde;o <br>guardam paridade com o constante nos registros </td>";
print "<td width='20%' rowspan='2' colspan='2' align='center'>Respons&aacute;vel pelo<br />Setor Cont&aacute;bil</td>";
print "<td width='20%' rowspan='2' colspan='2' align='center'>CRC/RJ n&ordm; _____</td>";
print "</tr>";

print "<tr>";
print "<td colspan='7'>Nome:</td>";
print "</tr>";

print "<tr>";
print "<td colspan='2'>Matr&iacute;cula:</td>";
print "<td colspan='2'>Data: __/__/____</td>";
print "<td colspan='7'>Assinatura:</td>";
print "</tr>";
print "</table>";

}

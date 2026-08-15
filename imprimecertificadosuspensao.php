<?php

require_once("libs/db_stdlib.php");
require_once("libs/db_conecta.php");
require_once("libs/db_sessoes.php");
require_once("libs/db_usuariosonline.php");
require_once('libs/db_conn.php');



//$hora = date("d/m/Y H:s");

//var_dump($hora);
//die("Confere");

function testa($var){
    echo "<pre>";
    print_r($var);
    echo "</pre>";
}



$jusu = $_GET["jusu"];
$jusuret = $_GET["jusuret"];

$idscertificado = $_GET["idatabela"];


function retornaCertificado($id){
    $sql = pg_query("SELECT * FROM certificacaoconformidade WHERE id in ({$id})");
    $resultado = pg_fetch_all($sql);
    return $resultado;
}


function buscaFonteRecurso($seqempenho){
    $sql = pg_query("SELECT o15_codigo, o15_descr from empempenho  inner join orcdotacao on orcdotacao.o58_anousu = empempenho.e60_anousu and orcdotacao.o58_coddot = empempenho.e60_coddot inner join pctipocompra on pctipocompra.pc50_codcom = empempenho.e60_codcom inner join emptipo on emptipo.e41_codtipo = empempenho.e60_codtipo inner join concarpeculiar on concarpeculiar.c58_sequencial = empempenho.e60_concarpeculiar inner join db_config as a on a.codigo = orcdotacao.o58_instit inner join orctiporec on orctiporec.o15_codigo = orcdotacao.o58_codigo inner join orcfuncao on orcfuncao.o52_funcao = orcdotacao.o58_funcao inner join orcsubfuncao on orcsubfuncao.o53_subfuncao = orcdotacao.o58_subfuncao inner join orcprograma on orcprograma.o54_anousu = orcdotacao.o58_anousu and orcprograma.o54_programa = orcdotacao.o58_programa inner join orcelemento on orcelemento.o56_codele = orcdotacao.o58_codele and orcelemento.o56_anousu = orcdotacao.o58_anousu inner join orcprojativ on orcprojativ.o55_anousu = orcdotacao.o58_anousu and orcprojativ.o55_projativ = orcdotacao.o58_projativ inner join orcorgao on orcorgao.o40_anousu = orcdotacao.o58_anousu and orcorgao.o40_orgao = orcdotacao.o58_orgao inner join orcunidade on orcunidade.o41_anousu = orcdotacao.o58_anousu and orcunidade.o41_orgao = orcdotacao.o58_orgao and orcunidade.o41_unidade = orcdotacao.o58_unidade where empempenho.e60_numemp = {$seqempenho}");
    $resultado = pg_fetch_all($sql);
    return $resultado[0];
}


$dados = retornaCertificado($idscertificado);
$idusuario = db_getsession("DB_id_usuario");

$hoje = date("Y-m-d", db_getsession("DB_datausu"));
$hora = date("H:i");
 


foreach ($dados as $op) {    
    if(!empty($jusu) && empty($jusuret)){
        pg_query("UPDATE certificacaoconformidade SET suspensao = 1, justificativasuspensao = '{$jusu}', justificativaretiradasuspensao = '{$jusuret}' WHERE id = {$op['id']}");   
    } else {
        pg_query("UPDATE certificacaoconformidade SET suspensao = 2, justificativasuspensao = '{$jusu}', justificativaretiradasuspensao = '{$jusuret}' WHERE id = {$op['id']}");
    }
    //pg_query("UPDATE certificacaoconformidade SET justificativasuspensao = '{$jusu}', justificativaretiradasuspensao = '{$jusuret}' WHERE id = {$op['id']}");
    //echo "UPDATE certificacaoconformidade SET justificativasuspensao = '{$jusu}', justificativaretiradasuspensao = '{$jusuret}' WHERE id = {$op['id']}"; echo "<br>";
    
}

//testa($dados);




setlocale(LC_TIME, 'pt_BR', 'pt_BR.utf-8', 'pt_BR.utf-8', 'portuguese');
date_default_timezone_set('America/Sao_Paulo');
//$geracao = strftime('%d de %B de %Y');
$geracao = strftime('%d de %B de %Y', strtotime(implode("", explode("-", $hoje))));
$geracao = $geracao . " - " . $hora;



        
$result_nomeusu = db_query($conn, "select nome as nomeusu from db_usuarios where id_usuario =".db_getsession("DB_id_usuario"));
if (pg_numrows($result_nomeusu)>0){
    $nomeusu = pg_result($result_nomeusu,0,0);
}
if (isset($nomeusu)&&$nomeusu!=""){
    $emissor = $nomeusu;
}else{
    $emissor = @$GLOBALS["DB_login"];
}

$txtrodape = "Emissor: ".substr(ucwords(strtolower($emissor)),0,30)."  Exerc: ".db_getsession("DB_anousu"). " Data: ".date("d-m-Y",db_getsession("DB_datausu"))." - ".date("H:i:s");




    // Include the main TCPDF library (search for installation path).
    require_once('tcpdf_include.php');

    //$PDF_PAGE_ORIENTATION_LOCAL = "L";
    $PDF_PAGE_ORIENTATION_LOCAL = "P";

    // create new PDF document
    $pdf = new TCPDF($PDF_PAGE_ORIENTATION_LOCAL, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
    define('K_TCPDF_THROW_EXCEPTION_ERROR', true);

    // set document information
    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetAuthor('...');
    $pdf->SetTitle('Relatório');
    $pdf->SetSubject('Relatório');
    $pdf->SetKeywords('Relatório');

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
    //$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
    $pdf->SetFooterMargin(1);
    //10

    // set auto page breaks
    //$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
    $pdf->SetAutoPageBreak(false);

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
       
    $pdf->SetFont('dejavusans', '', 8);
    $pdf->AddPage();
/*
<tr>
            <td width="60%">Justificativa de quebra de ordem cronológica exarada pelo ordenado de despesa.</td>
            <td width="10%" style="text-align:center">'.($radio1 == 1) ? ' X ' : ' '. '</td>
            <td width="10%" style="text-align:center">'.($radio1 == 2) ? ' X ' : ' '. '</td>
            <td width="20%">'.utf8_encode($folha1). '</td>            
        </tr>
*/

//<h2>Certificado nº: <span style="font-weight:normal">'. $dados["nocertificado"] .'</span></h2>
    $html = '
        <table>
            <tr style="text-align:center;">
                <td width="70%"><img src="imagens/bannercertificadoretirada2.png"></td>
            
                <td width="30%">                
                    <table border="1">
                        <tr style="text-align:center;">
                            <td width="75%" colspan="3">PROCESSO</td>
                            <td width="25%">RUBRICA</td>
                        </tr>
                        <tr style="text-align:center;font-size:8px">
                            <td width="25%">Número</td>
                            <td width="25%">Exercício</td>
                            <td width="25%">Folha</td>
                            <td width="25%" rowspan="2"> </td>
                        </tr>
                        <tr>
                        <td> </td>
                        <td> </td>
                        <td> </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

                       

        <h3 style="text-align:center">ANÁLISE</h3>

        <table cellpadding="4" border="1" style="font-size:10px">
        <tr style="text-align:left;">
            <td width="100%" style="text-align:center"><h5>Justificativa da Suspensão</h5></td>                        
        </tr>
        <tr>
            <td width="100%" rowspan="3" style="font-size:10px">'.utf8_encode($jusu).'</td>
        </tr>
        </table>

        <table cellpadding="4" border="1" style="font-size:10px">
        <tr style="text-align:left;">
            <td width="100%" style="text-align:center"><h5>Justificativa da Retirada de Suspensão</h5></td>                        
        </tr>
        <tr>
            <td width="100%" rowspan="3" style="font-size:10px">'.utf8_encode($jusuret).'</td>
        </tr>
        </table>

        <br> <br>

        <p><h3 style="text-align:center"><u>SUSPENSÃO DA ORDEM CRONOLÓGICA</u></h3></p>
        <p style="font-size:11px;margin-right:10px">    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b>Art. 13</b> - Se o Setor de Tesouraria identificar alguma pendência que impeça a realização do pagamento, a ordem de pagamento será imediatamente suspensa, saindo temporariamente da fila de ordem cronológica de pagamentos, e o órgão ou entidade de origem deverá ser comunicado em até 03 dias úteis para sanear a pendência.</p>

        <p style="font-size:11px"><b>§ 1º</b> - O órgão ou entidade de origem deverá providenciar a notificação do credor no prazo e na forma do Art. 9º, §1º deste Decreto.</p>
        <p style="font-size:11px"><b>§ 2º</b> - Quando o credor sanear a pendência, o órgão ou entidade de origem terá o prazo de até 03 dias úteis para remeter os autos ao Setor de Tesouraria.</p>
        <p style="font-size:11px"><b>§ 3º</b> - O Setor de Tesouraria terá o prazo de até 03 dias úteis para baixar a suspensão da ordem de pagamento e reingressar o credor na fila da ordem cronológica de pagamento na mesma posição que se encontrava quando a pendência foi identificada.</p>
        <p style="font-size:11px"><b>§ 4º</b> - Realizado o pagamento, o Setor de Tesouraria providenciará a imediata atualização da listagem pública da ordem cronológica de pagamentos, retirando aquele credor da fila, ato contínuo, a comprovação de pagamento será anexada aos autos que será remetido, em até 03 dias úteis, ao órgão ou entidade de origem.</p>

        <br>
        
        <table cellpadding="5" border="1" style="font-size:10px">
        <tr style="text-align:left;">
            <td width="20%" style="text-align:center"><h5>Órgão</h5></td>
            <td width="10%" style="text-align:center"><h5>Nota de Empenho</h5></td>
            <td width="10%" style="text-align:center"><h5>Ordem de Pagamento</h5></td>
            <td width="20%" style="text-align:center"><h5>Fonte de Recurso</h5></td>
            <td width="30%" style="text-align:center"><h5>Credor</h5></td>
            <td width="10%" style="text-align:center"><h5>Valor</h5></td>
        </tr>';

        //<td width="20%">'.$linha["corgao"]. ' - ' . $linha["ordenadordadespesa"] . '</td>
        foreach ($dados as $linha){
            $fonte = buscaFonteRecurso($linha["e60_numemp"]);

            $html .= '<tr>
            <td width="20%">'.$linha["corgao"]. ' - ' . utf8_encode($linha["ordenadordadespesa"]) . '</td>
            <td width="10%" style="text-align:center">'.$linha["noempenho"]. '</td>
            <td width="10%" style="text-align:center">'.$linha["e50_codord"]. '</td>
            <td width="20%">'.$fonte["o15_codigo"]. ' - ' . utf8_encode($fonte["o15_descr"]) . '</td>
            <td width="30%">'.utf8_encode($linha["z01_nome"]). '</td>
            <td width="10%" style="text-align:center">'. number_format($linha["valornotafiscal"], 2, ",", "."). '</td>
            </tr>';
        }
        
        $html .= "</table>
        <br> <br> <br> <p> </p> <br> <br> <br>
        ";



        $html .= '
        <div style="font-size:8px;position:absolute;bottom:0">
        
<span style="text-aling:center">
<h3 style="text-align:center">
Volta Redonda, '.$geracao.'
</h3>
</span><br>

<br> <br> <br><p> </p>

<span style="font-size:12px;text-align:center">
<b>______________________________</b><br>
<b>'.substr(ucwords(strtolower($emissor)),0,30).'</b><br>
Secretaria Municipal de Fazenda
</span>
<br> <br> <br>

        </div>
        ';
    

    $pdf->writeHTML($html, true, false, true, false, '');        
    $pdf->lastPage();
    
    ob_end_clean();
    $pdf->Output('certificadosuspensao.pdf', 'I');
    
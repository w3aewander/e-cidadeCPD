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





$radio1 = substr($_GET["c1"], 0, 1);
$folha1 = substr($_GET["c1"], 1);
if(!$folha1){
    $folha1 = "";
}
$radio2 = substr($_GET["c2"], 0, 1);
$folha2 = substr($_GET["c2"], 1);
if(!$folha2){
    $folha2 = "";
}
$radio3 = substr($_GET["c3"], 0, 1);
$folha3 = substr($_GET["c3"], 1);
if(!$folha3){
    $folha3 = "";
}


/*
var_dump($radio1);
var_dump($folha1);
echo "<br>";

var_dump($radio2);
var_dump($folha2);
echo "<br>";

var_dump($radio3);
var_dump($folha3);
echo "<br>";
*/




$idscertificado = $_GET["idatabela"];


function retornaCertificado($id){
    $sql = pg_query("SELECT * FROM certificacaoconformidade WHERE id in ({$id})");
    $resultado = pg_fetch_all($sql);
    return $resultado;
}

function buscaDataRetirada($id){
    $sql = pg_query("SELECT e50_dataquebraordem FROM certificadoretirada WHERE idtabelacertliq = {$id}");
    $resultado = pg_fetch_all($sql);
    return $resultado[0]["e50_dataquebraordem"];
}

function buscaFonteRecurso($seqempenho){
    $sql = pg_query("SELECT o15_codigo, o15_descr from empempenho  inner join orcdotacao on orcdotacao.o58_anousu = empempenho.e60_anousu and orcdotacao.o58_coddot = empempenho.e60_coddot inner join pctipocompra on pctipocompra.pc50_codcom = empempenho.e60_codcom inner join emptipo on emptipo.e41_codtipo = empempenho.e60_codtipo inner join concarpeculiar on concarpeculiar.c58_sequencial = empempenho.e60_concarpeculiar inner join db_config as a on a.codigo = orcdotacao.o58_instit inner join orctiporec on orctiporec.o15_codigo = orcdotacao.o58_codigo inner join orcfuncao on orcfuncao.o52_funcao = orcdotacao.o58_funcao inner join orcsubfuncao on orcsubfuncao.o53_subfuncao = orcdotacao.o58_subfuncao inner join orcprograma on orcprograma.o54_anousu = orcdotacao.o58_anousu and orcprograma.o54_programa = orcdotacao.o58_programa inner join orcelemento on orcelemento.o56_codele = orcdotacao.o58_codele and orcelemento.o56_anousu = orcdotacao.o58_anousu inner join orcprojativ on orcprojativ.o55_anousu = orcdotacao.o58_anousu and orcprojativ.o55_projativ = orcdotacao.o58_projativ inner join orcorgao on orcorgao.o40_anousu = orcdotacao.o58_anousu and orcorgao.o40_orgao = orcdotacao.o58_orgao inner join orcunidade on orcunidade.o41_anousu = orcdotacao.o58_anousu and orcunidade.o41_orgao = orcdotacao.o58_orgao and orcunidade.o41_unidade = orcdotacao.o58_unidade where empempenho.e60_numemp = {$seqempenho}");
    $resultado = pg_fetch_all($sql);
    return $resultado[0];
}

function buscaProximoNumero(){
    $sql = pg_query("SELECT max(numeroretirada) FROM certificadoretirada");
    $resultado = pg_fetch_all($sql);
    return $resultado[0]["max"] + 1;
}

$dados = retornaCertificado($idscertificado);
$idusuario = db_getsession("DB_id_usuario");

$hoje = date("Y-m-d", db_getsession("DB_datausu"));
$hora = date("H:i");
 

$numeroretirada = buscaProximoNumero();
if(empty($numeroretirada)){
    $numeroretirada = 1;
} 
//$numerocertificado = $dados[0]["nocertificado"];
//var_dump($numerocertificado); echo "<br>";
//$novonum = explode(".", $numerocertificado);
//var_dump($novonum); echo "<br>";
//$novoseq2 = explode("/", $novonum[3]);
//var_dump($novoseq2);

//$dataretirada = "";
foreach ($dados as $op) {
    $numerocertificado = $op["nocertificado"];    
    $novonum = explode(".", $numerocertificado);    
    $c1 = $novonum[0];
    $c2 = $novonum[1];
    $c3 = $novonum[2];    
    $novoseq2 = explode("/", $novonum[3]);    
    $c5 = $novoseq2[1];    
    $sequencialdois = $op["cseq2"] + 1;
    $vseq2 = (string)$sequencialdois;
    if(strlen($vseq2) == 1){
        $vseq2 = "0".$vseq2;
    }    
    $c4 = $vseq2;
    $novocertificado = $c1 . "." . $c2 . "." . $c3 . "." . $c4 . "/" . $c5;
    
    pg_query("UPDATE pagordem SET e50_dataquebraordem = '{$hoje}', e50_numedi = 0 WHERE e50_codord = {$op['e50_codord']}");
    pg_query("UPDATE certificacaoconformidade SET cseq2 = {$vseq2}, nocertificado = '{$novocertificado}' WHERE id = {$op['id']}");
    pg_query("INSERT INTO certificadoretirada(idtabelacertliq, e50_codord, e50_dataquebraordem, horaquebra, idusuario, numeroretirada, g11, g11f, g22, g22f, g33, g33f) VALUES({$op['id']}, {$op['e50_codord']}, '{$hoje}', '{$hora}', {$idusuario}, {$numeroretirada}, {$radio1}, '{$folha1}', {$radio2}, '{$folha2}', {$radio3}, '{$folha3}') ");
    
    //$dataretirada = buscaDataRetirada($op["id"]);
    //echo "UPDATE pagordem SET e50_dataquebraordem = '{$hoje}', e50_numedi = 0 WHERE e50_codord = {$op['e50_codord']}"; echo "<br>";
    //echo "UPDATE certificacaoconformidade SET cseq2 = {$vseq2}, nocertificado = '{$novocertificado}' WHERE id = {$op['id']} "; echo "<br>";
    //echo "INSERT INTO certificadoretirada(idtabelacertliq, e50_codord, e50_dataquebraordem, horaquebra, idusuario, numeroretirada, g11, g11f, g22, g22f, g33, g33f) VALUES({$op['id']}, {$op['e50_codord']}, '{$hoje}', '{$hora}', {$idusuario}, {$numeroretirada}, {$radio1}, '{$folha1}', {$radio2}, '{$folha2}', {$radio3}, '{$folha3}') "; echo "<br>";
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

if($radio1 == 1){
    $radio1s = " X ";
    $radio1n = " ";
} else {
    $radio1s = " ";
    $radio1n = " X ";
}


if($radio2 == 1){
    $radio2s = " X ";
    $radio2n = " ";
} else {
    $radio2s = " ";
    $radio2n = " X ";
}

if($radio3 == 1){
    $radio3s = " X ";
    $radio3n = " ";
} else {
    $radio3s = " ";
    $radio3n = " X ";
}
//$geracao = date("d/m/Y");


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
            <td width="60%" style="text-align:center"><h5>Verificação</h5></td>
            <td width="10%" style="text-align:center"><h5>SIM</h5></td>
            <td width="10%" style="text-align:center"><h5>NÃO</h5></td>
            <td width="20%" style="text-align:center"><h5>Folha</h5></td>            
        </tr>
        <tr>
            <td width="60%">Justificativa de quebra de ordem cronológica exarada pelo ordenado de despesa.</td>
            <td width="10%" style="text-align:center">'.$radio1s.'</td>
            <td width="10%" style="text-align:center">'.$radio1n.'</td>
            <td width="20%">'.$folha1. '</td>
        </tr> 

        <tr>
            <td width="60%">Acolhimento do órgão de controle interno prévia justificativa para alteração da ordem cronológica de pagamentos.</td>
            <td width="10%" style="text-align:center">'.$radio2s.'</td>
            <td width="10%" style="text-align:center">'.$radio2n.'</td>
            <td width="20%">'.$folha2. '</td>
        </tr>

        <tr>
            <td width="60%">Publicação da quebra de ordem cronológica.</td>
            <td width="10%" style="text-align:center">'.$radio3s.'</td>
            <td width="10%" style="text-align:center">'.$radio3n.'</td>
            <td width="20%">'.$folha3. '</td>
        </tr>
        </table>

        <br> <br>

        <p><h3 style="text-align:center"><u>CERTIDÃO DE CONFORMIDADE DA ALTERAÇÃO DA ORDEM CRONOLÓGICA</u></h3></p>
        <p style="font-size:14px;margin-right:10px">    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Conforme disposto no artigo 3º, §4º do Decreto nº 16.901/2021, CERTIFICAMOS que foram preenchidos todos os requisitos para alteração da ordem cronológica da despesa a seguir discriminada:</p>

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
            <td width="30%">'.$linha["z01_nome"]. '</td>
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
Controladoria Geral do Município
</span>
<br> <br> <br>
<p><h3>SMF / DF</h3><br>
<p style="font-size:14px;margin-right:10px">    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Para prosseguimento.</p>
        </div>
        ';
    

    $pdf->writeHTML($html, true, false, true, false, '');        
    $pdf->lastPage();
    
    ob_end_clean();
    $pdf->Output('certificacaoretirada.pdf', 'I');
    
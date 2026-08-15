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

function retornaCertificado($id){
    $sql = pg_query("SELECT * FROM certificacaoconformidade WHERE id = {$id}");
    $resultado = pg_fetch_all($sql);
    return $resultado[0];
}



$idcertificado = $_GET["idatabela"];
//var_dump($idcertificado); die("confere id");
$dados = retornaCertificado($idcertificado);

//testa($dados); die("Confere");
setlocale(LC_TIME, 'pt_BR', 'pt_BR.utf-8', 'pt_BR.utf-8', 'portuguese');
date_default_timezone_set('America/Sao_Paulo');
$geracao = strftime('%d de %B de %Y', strtotime('today'));




//testa(db_getsession());
//die("Confere");

        
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




$geracao = strftime('%d de %B de %Y', strtotime(implode("", explode("-", $dados["datageracao"]))));



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
    $pdf->SetTitle('Certificado de Conformidade');
    

    // set default header data
    $pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE.' 006', PDF_HEADER_STRING);

    // set header and footer fonts
    $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
    $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

    // set default monospaced font
    $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

    // set margins
    $margin_top = 1;
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
//<h2>Certificado nº: <span style="font-weight:normal">'. $dados["nocertificado"] .'</span></h2>
    $html = '
        <table>
            <tr style="text-align:center;">
                <td width="95%"><img src="imagens/bannercertificado.png"></td>
            </tr>
        </table>
        
        <br>
        
        <table cellpadding="4">
        <tr style="text-align:left;">
            <td width="45%"  border="1" style="text-align:center"><h1>EXAME DA LIQUIDAÇÃO DA DESPESA</h1></td>
            <td width="10%"> </td>
            <td width="45%" rowspan="4" border="1"><h2> Certificado: <span style="font-weight:normal">'. $dados["nocertificado"] .'</span></h2>
            <br>
            <span style="display:inline-block; margin:0;font-size:16px;text-align:left"><b>Processo:</b> <span style="font-weight:normal">'. $dados["noprocesso"] .'</span>                       fls.: 1</span>
            </td>
        </tr>
        </table>

        <br><br>

        <table cellpadding="5" border="1">
        <tr style="text-align:center;">
                <td width="25%" height="57"><h4>Ordenador da Despesa</h4>'.utf8_encode($dados["ordenadordadespesa"]).'</td>
                <td width="25%" style="text-align:center"><h4>Instrumento Jurídico</h4>'.utf8_encode($dados["instrumentojuridico"]).'</td>
                <td width="16%"><h4>Nota de Empenho</h4>'.$dados["noempenho"].'</td>
                <td width="16%"><h4>Nota Fiscal</h4>'.utf8_encode($dados["nonotafiscal"]).'</td>
                <td width="18%"><h4>Valor R$</h4>'.number_format($dados["valornotafiscal"], 2, ",", ".").'</td>
        </tr>
        </table>

        <br>
        
        <table>
        <tr style="text-align:center;">
            <td width="5%"></td><td width="90%"><h3>DADOS DO FORNECEDOR</h3></td>
        </tr>
        </table>

        <br><br>

        <table cellpadding="2" border="1">
        <tr style="text-align:left;">
            <td width="70%" height="10"><span><b>NOME DO FORNECEDOR/BENEFICIÁRIO</b></span><br><span style="font-size:10px">'.utf8_encode($dados["z01_nome"]).'</span></td>
            <td width="30%"><span><b>CNPJ/CPF</b></span><br><span style="font-size:10px">'.$dados["z01_cgccpf"].'</span></td>
        </tr>
        </table>

        <br>
        
        <table>
        <tr style="text-align:center;">
            <td width="5%"></td><td width="90%"><h3>ANÁLISE</h3></td>
        </tr>
        </table>

        <br><br>

        <table cellpadding="5" border="1" style="font-size:9px">
        <tr style="text-align:left;">
            <td width="70%"><h5>1 - GERAL</h5></td>
            <td width="5%"><h5>SIM</h5></td>
            <td width="5%"><h5>NÃO</h5></td>
            <td width="5%"><h5>N / A</h5></td>
            <td width="15%" style="text-align:center"><h5>FOLHA</h5></td>
        </tr>

        <tr style="text-align:left;">
            <td width="70%">1.1 - A nota fiscal foi emitida contra a Município/Prefeitura Municipal de Volta Redonda?</td>';

          if($dados["g11"] == 1){
            $html .= 
            '<td width="5%" style="text-align:center">X</td>
            <td width="5%" style="text-align:center"> </td>
            <td width="5%" style="text-align:center"> </td>';
          }elseif($dados["g11"] == 2){
            $html .= 
            '<td width="5%" style="text-align:center"> </td>
            <td width="5%" style="text-align:center">X</td>
            <td width="5%" style="text-align:center"> </td>';
          }elseif($dados["g11"] == 3){
            $html .= 
            '<td width="5%" style="text-align:center"> </td>
            <td width="5%" style="text-align:center"> </td>
            <td width="5%" style="text-align:center">X</td>';
          }
          $html .= '            
            <td width="15%" style="text-align:center">'.utf8_encode($dados["g11f"]).'</td>
        </tr>

        <tr style="text-align:left;">
            <td width="70%">1.2 - O credor informado no empenho é o mesmo dos demais documentos constantes do processo?</td>';
            if($dados["g12"] == 1){
            $html .= 
            '<td width="5%" style="text-align:center">X</td>
            <td width="5%" style="text-align:center"> </td>
            <td width="5%" style="text-align:center"> </td>';
          }elseif($dados["g12"] == 2){
            $html .= 
            '<td width="5%" style="text-align:center"> </td>
            <td width="5%" style="text-align:center">X</td>
            <td width="5%" style="text-align:center"> </td>';
          }elseif($dados["g12"] == 3){
            $html .= 
            '<td width="5%" style="text-align:center"> </td>
            <td width="5%" style="text-align:center"> </td>
            <td width="5%" style="text-align:center">X</td>';
          }
          $html .= '            
            <td width="15%" style="text-align:center">'.utf8_encode($dados["g12f"]).'</td>
        </tr>

        <tr style="text-align:left;">
            <td width="70%">1.3 - A Nota de Empenho foi emitida até a data de início da realização da despesa e assinada pelo ordenador da despesa?</td>';
            if($dados["g13"] == 1){
            $html .= 
            '<td width="5%" style="text-align:center">X</td>
            <td width="5%" style="text-align:center"> </td>
            <td width="5%" style="text-align:center"> </td>';
          }elseif($dados["g13"] == 2){
            $html .= 
            '<td width="5%" style="text-align:center"> </td>
            <td width="5%" style="text-align:center">X</td>
            <td width="5%" style="text-align:center"> </td>';
          }elseif($dados["g13"] == 3){
            $html .= 
            '<td width="5%" style="text-align:center"> </td>
            <td width="5%" style="text-align:center"> </td>
            <td width="5%" style="text-align:center">X</td>';
          }
          $html .= '            
            <td width="15%" style="text-align:center">'.utf8_encode($dados["g13f"]).'</td>
        </tr>

        <tr style="text-align:left;">
            <td width="70%">1.4 – A despesa foi classificada na natureza de despesa adequada ao objeto do contrato?</td>';
            if($dados["g14"] == 1){
            $html .= 
            '<td width="5%" style="text-align:center">X</td>
            <td width="5%" style="text-align:center"> </td>
            <td width="5%" style="text-align:center"> </td>';
          }elseif($dados["g14"] == 2){
            $html .= 
            '<td width="5%" style="text-align:center"> </td>
            <td width="5%" style="text-align:center">X</td>
            <td width="5%" style="text-align:center"> </td>';
          }elseif($dados["g14"] == 3){
            $html .= 
            '<td width="5%" style="text-align:center"> </td>
            <td width="5%" style="text-align:center"> </td>
            <td width="5%" style="text-align:center">X</td>';
          }
          $html .= '            
            <td width="15%" style="text-align:center">'.utf8_encode($dados["g14f"]).'</td>
        </tr>

        <tr style="text-align:left;">
            <td width="70%">1.5 - O credor da(s) nota(s) de empenho(s) coincide com o do emitente do(s) documento(s) comprobatório(s)?</td>';
            if($dados["g15"] == 1){
            $html .= 
            '<td width="5%" style="text-align:center">X</td>
            <td width="5%" style="text-align:center"> </td>
            <td width="5%" style="text-align:center"> </td>';
          }elseif($dados["g15"] == 2){
            $html .= 
            '<td width="5%" style="text-align:center"> </td>
            <td width="5%" style="text-align:center">X</td>
            <td width="5%" style="text-align:center"> </td>';
          }elseif($dados["g15"] == 3){
            $html .= 
            '<td width="5%" style="text-align:center"> </td>
            <td width="5%" style="text-align:center"> </td>
            <td width="5%" style="text-align:center">X</td>';
          }
          $html .= '            
            <td width="15%" style="text-align:center">'.utf8_encode($dados["g15f"]).'</td>
        </tr>

        <tr style="text-align:left;">
            <td width="70%">1.6 - Os itens descritos na Nota Fiscal guardam paridade com os itens que constam na(s) Nota(s) de Empenho e NRM (Nota de Recebimento de Material)?</td>';
            if($dados["g16"] == 1){
            $html .= 
            '<td width="5%" style="text-align:center">X</td>
            <td width="5%" style="text-align:center"> </td>
            <td width="5%" style="text-align:center"> </td>';
          }elseif($dados["g16"] == 2){
            $html .= 
            '<td width="5%" style="text-align:center"> </td>
            <td width="5%" style="text-align:center">X</td>
            <td width="5%" style="text-align:center"> </td>';
          }elseif($dados["g16"] == 3){
            $html .= 
            '<td width="5%" style="text-align:center"> </td>
            <td width="5%" style="text-align:center"> </td>
            <td width="5%" style="text-align:center">X</td>';
          }
          $html .= '            
            <td width="15%" style="text-align:center">'.utf8_encode($dados["g16f"]).'</td>
        </tr>

        <tr style="text-align:left;">
            <td width="70%">1.7 - A Nota Fiscal está devidamente atestada por dois funcionários e se for o caso, autorizada pelo Ordenador da Despesa?</td>';
            if($dados["g17"] == 1){
            $html .= 
            '<td width="5%" style="text-align:center">X</td>
            <td width="5%" style="text-align:center"> </td>
            <td width="5%" style="text-align:center"> </td>';
          }elseif($dados["g17"] == 2){
            $html .= 
            '<td width="5%" style="text-align:center"> </td>
            <td width="5%" style="text-align:center">X</td>
            <td width="5%" style="text-align:center"> </td>';
          }elseif($dados["g17"] == 3){
            $html .= 
            '<td width="5%" style="text-align:center"> </td>
            <td width="5%" style="text-align:center"> </td>
            <td width="5%" style="text-align:center">X</td>';
          }
          $html .= '            
            <td width="15%" style="text-align:center">'.utf8_encode($dados["g17f"]).'</td>
        </tr>

        <tr style="text-align:left;">
            <td width="70%">1.8 - Consta calculo de ISS efetuado pela Fiscalização Municipal?</td>';
            if($dados["g18"] == 1){
            $html .= 
            '<td width="5%" style="text-align:center">X</td>
            <td width="5%" style="text-align:center"> </td>
            <td width="5%" style="text-align:center"> </td>';
          }elseif($dados["g18"] == 2){
            $html .= 
            '<td width="5%" style="text-align:center"> </td>
            <td width="5%" style="text-align:center">X</td>
            <td width="5%" style="text-align:center"> </td>';
          }elseif($dados["g18"] == 3){
            $html .= 
            '<td width="5%" style="text-align:center"> </td>
            <td width="5%" style="text-align:center"> </td>
            <td width="5%" style="text-align:center">X</td>';
          }
          $html .= '            
            <td width="15%" style="text-align:center">'.utf8_encode($dados["g18f"]).'</td>
        </tr>

        <tr style="text-align:left;">
            <td width="70%">1.9 - Em se tratando de Nota Fiscal Eletrônica a sua autenticidade foi verificada?</td>';
            if($dados["g19"] == 1){
            $html .= 
            '<td width="5%" style="text-align:center">X</td>
            <td width="5%" style="text-align:center"> </td>
            <td width="5%" style="text-align:center"> </td>';
          }elseif($dados["g19"] == 2){
            $html .= 
            '<td width="5%" style="text-align:center"> </td>
            <td width="5%" style="text-align:center">X</td>
            <td width="5%" style="text-align:center"> </td>';
          }elseif($dados["g19"] == 3){
            $html .= 
            '<td width="5%" style="text-align:center"> </td>
            <td width="5%" style="text-align:center"> </td>
            <td width="5%" style="text-align:center">X</td>';
          }
          $html .= '            
            <td width="15%" style="text-align:center">'.utf8_encode($dados["g19f"]).'</td>
        </tr>

        <tr style="text-align:left;">
            <td width="70%">1.10 - Consta do processo uma cópia do termo de contrato/aditivo/convênio/ajuste/rescisão entre o Governo Municipal e o Fornecedor?</td>';
            if($dados["g110"] == 1){
            $html .= 
            '<td width="5%" style="text-align:center">X</td>
            <td width="5%" style="text-align:center"> </td>
            <td width="5%" style="text-align:center"> </td>';
          }elseif($dados["g110"] == 2){
            $html .= 
            '<td width="5%" style="text-align:center"> </td>
            <td width="5%" style="text-align:center">X</td>
            <td width="5%" style="text-align:center"> </td>';
          }elseif($dados["g110"] == 3){
            $html .= 
            '<td width="5%" style="text-align:center"> </td>
            <td width="5%" style="text-align:center"> </td>
            <td width="5%" style="text-align:center">X</td>';
          }
          $html .= '            
            <td width="15%" style="text-align:center">'.utf8_encode($dados["g110f"]).'</td>
        </tr>

        <tr style="text-align:left;">
            <td width="70%">1.11 - Foram observadas as regras previstas no Edital/Ata de registro de preço e no contrato?</td>';
            if($dados["g111"] == 1){
            $html .= 
            '<td width="5%" style="text-align:center">X</td>
            <td width="5%" style="text-align:center"> </td>
            <td width="5%" style="text-align:center"> </td>';
          }elseif($dados["g111"] == 2){
            $html .= 
            '<td width="5%" style="text-align:center"> </td>
            <td width="5%" style="text-align:center">X</td>
            <td width="5%" style="text-align:center"> </td>';
          }elseif($dados["g111"] == 3){
            $html .= 
            '<td width="5%" style="text-align:center"> </td>
            <td width="5%" style="text-align:center"> </td>
            <td width="5%" style="text-align:center">X</td>';
          }
          $html .= '            
            <td width="15%" style="text-align:center">'.utf8_encode($dados["g111f"]).'</td>
        </tr>

        <tr style="text-align:left;">
            <td width="70%">1.12 - Se o objeto do processo for referente a recurso vinculado (convênio), consta uma cópia do Contrato de Repasse?</td>';
            if($dados["g112"] == 1){
            $html .= 
            '<td width="5%" style="text-align:center">X</td>
            <td width="5%" style="text-align:center"> </td>
            <td width="5%" style="text-align:center"> </td>';
          }elseif($dados["g112"] == 2){
            $html .= 
            '<td width="5%" style="text-align:center"> </td>
            <td width="5%" style="text-align:center">X</td>
            <td width="5%" style="text-align:center"> </td>';
          }elseif($dados["g112"] == 3){
            $html .= 
            '<td width="5%" style="text-align:center"> </td>
            <td width="5%" style="text-align:center"> </td>
            <td width="5%" style="text-align:center">X</td>';
          }
          $html .= '            
            <td width="15%" style="text-align:center">'.utf8_encode($dados["g112f"]).'</td>
        </tr>

        <tr style="text-align:left;">
            <td width="70%">1.13 - A entrega do bem ou serviço está de acordo com o cronograma previsto? (semanalmente; quinzenalmente; mensalmente ou entrega imediata)?</td>';
            if($dados["g113"] == 1){
            $html .= 
            '<td width="5%" style="text-align:center">X</td>
            <td width="5%" style="text-align:center"> </td>
            <td width="5%" style="text-align:center"> </td>';
          }elseif($dados["g113"] == 2){
            $html .= 
            '<td width="5%" style="text-align:center"> </td>
            <td width="5%" style="text-align:center">X</td>
            <td width="5%" style="text-align:center"> </td>';
          }elseif($dados["g113"] == 3){
            $html .= 
            '<td width="5%" style="text-align:center"> </td>
            <td width="5%" style="text-align:center"> </td>
            <td width="5%" style="text-align:center">X</td>';
          }
          $html .= '            
            <td width="15%" style="text-align:center">'.utf8_encode($dados["g113f"]).'</td>
        </tr>

        <tr style="text-align:left;">
            <td width="70%">1.14 - Consta do processo a Regularidade Fiscal (FGTS - INSS - CNDT)?</td>';
            if($dados["g114"] == 1){
            $html .= 
            '<td width="5%" style="text-align:center">X</td>
            <td width="5%" style="text-align:center"> </td>
            <td width="5%" style="text-align:center"> </td>';
          }elseif($dados["g114"] == 2){
            $html .= 
            '<td width="5%" style="text-align:center"> </td>
            <td width="5%" style="text-align:center">X</td>
            <td width="5%" style="text-align:center"> </td>';
          }elseif($dados["g114"] == 3){
            $html .= 
            '<td width="5%" style="text-align:center"> </td>
            <td width="5%" style="text-align:center"> </td>
            <td width="5%" style="text-align:center">X</td>';
          }
          $html .= '            
            <td width="15%" style="text-align:center">'.utf8_encode($dados["g114f"]).'</td>
        </tr>

        <tr style="text-align:left;">
            <td width="70%">1.15 - Consta Portaria de nomeação de fiscal?</td>';
            if($dados["g115"] == 1){
            $html .= 
            '<td width="5%" style="text-align:center">X</td>
            <td width="5%" style="text-align:center"> </td>
            <td width="5%" style="text-align:center"> </td>';
          }elseif($dados["g115"] == 2){
            $html .= 
            '<td width="5%" style="text-align:center"> </td>
            <td width="5%" style="text-align:center">X</td>
            <td width="5%" style="text-align:center"> </td>';
          }elseif($dados["g115"] == 3){
            $html .= 
            '<td width="5%" style="text-align:center"> </td>
            <td width="5%" style="text-align:center"> </td>
            <td width="5%" style="text-align:center">X</td>';
          }
          $html .= '            
            <td width="15%" style="text-align:center">'.utf8_encode($dados["g115f"]).'</td>
        </tr>

        <tr style="text-align:left;">
            <td width="70%">1.16 - Consta cópia da Ordem de Serviço/fornecimento para o objeto contratado?</td>';
            if($dados["g116"] == 1){
            $html .= 
            '<td width="5%" style="text-align:center">X</td>
            <td width="5%" style="text-align:center"> </td>
            <td width="5%" style="text-align:center"> </td>';
          }elseif($dados["g116"] == 2){
            $html .= 
            '<td width="5%" style="text-align:center"> </td>
            <td width="5%" style="text-align:center">X</td>
            <td width="5%" style="text-align:center"> </td>';
          }elseif($dados["g116"] == 3){
            $html .= 
            '<td width="5%" style="text-align:center"> </td>
            <td width="5%" style="text-align:center"> </td>
            <td width="5%" style="text-align:center">X</td>';
          }
          $html .= '            
            <td width="15%" style="text-align:center">'.utf8_encode($dados["g116f"]).'</td>
        </tr>

        <tr style="text-align:left;">
            <td width="70%">1.17 - Se tratando de aquisição de material permanente consta o tombamento e o número patrimonial?</td>';
            if($dados["g117"] == 1){
            $html .= 
            '<td width="5%" style="text-align:center">X</td>
            <td width="5%" style="text-align:center"> </td>
            <td width="5%" style="text-align:center"> </td>';
          }elseif($dados["g117"] == 2){
            $html .= 
            '<td width="5%" style="text-align:center"> </td>
            <td width="5%" style="text-align:center">X</td>
            <td width="5%" style="text-align:center"> </td>';
          }elseif($dados["g117"] == 3){
            $html .= 
            '<td width="5%" style="text-align:center"> </td>
            <td width="5%" style="text-align:center"> </td>
            <td width="5%" style="text-align:center">X</td>';
          }
          $html .= '
            <td width="15%" style="text-align:center">'.utf8_encode($dados["g117f"]).'</td>
        </tr>
       

        </table>
        <div style="font-size:8px;position:absolute;bottom:0">
        <div>
        <i>
<b>Art. 63, da Lei 4.320/64</b><br>
 &emsp;&emsp;&emsp;&emsp;A liquidação da despesa consiste na verificação do direito adquirido pelo credor tendo por base os títulos e documentos comprobatórios do<br> &emsp;&emsp;&emsp;&emsp; respectivo crédito.<br>
        &emsp;&emsp;&emsp;&emsp;&emsp;§ 1° Essa verificação tem por fim apurar:<br>
            &emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;I - a origem e o objeto do que se deve pagar;<br>
            &emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;II - a importância exata a pagar;<br>
            &emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;III - a quem se deve pagar a importância, para extinguir a obrigação.<br>
        &emsp;&emsp;&emsp;&emsp;&emsp;§ 2º A liquidação da despesa por fornecimentos feitos ou serviços prestados terá por base:<br>
            &emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;I - o contrato, ajuste ou acordo respectivo;<br>
            &emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;II - a nota de empenho;<br>
            &emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;III - os comprovantes da entrega de material ou da prestação efetiva do serviço.<br>
</i>


</div>
<span style="text-aling:center">
<h3 style="text-align:center">
Volta Redonda, '.utf8_encode($geracao).'
</h3>
</span><br>
<i style="font-size:6px;text-align:center">'.$txtrodape.'</i>
<br> <br> <br>

<span style="font-size:12px;text-align:center">
<b>______________________________</b><br>
<b>'.substr(ucwords(strtolower($emissor)),0,30).'</b><br>
Secretaria de Fazenda
</span>
        </div>
        ';
    
    $pdf->writeHTML($html, true, false, true, false, '');    
    
    $pdf->lastPage();
    
    ob_end_clean();
    $pdf->Output('certificacao.pdf', 'I');
    
<?php
/*
 *     E-cidade Software Publico para Gestao Municipal                
 *  Copyright (C) 2012  DBselller Servicos de Informatica             
 *                            www.dbseller.com.br                     
 *                         e-cidade@dbseller.com.br                   
 *                                                                    
 *  Este programa e software livre; voce pode redistribui-lo e/ou     
 *  modifica-lo sob os termos da Licenca Publica Geral GNU, conforme  
 *  publicada pela Free Software Foundation; tanto a versao 2 da      
 *  Licenca como (a seu criterio) qualquer versao mais nova.          
 *                                                                    
 *  Este programa e distribuido na expectativa de ser util, mas SEM   
 *  QUALQUER GARANTIA; sem mesmo a garantia implicita de              
 *  COMERCIALIZACAO ou de ADEQUACAO A QUALQUER PROPOSITO EM           
 *  PARTICULAR. Consulte a Licenca Publica Geral GNU para obter mais  
 *  detalhes.                                                         
 *                                                                    
 *  Voce deve ter recebido uma copia da Licenca Publica Geral GNU     
 *  junto com este programa; se nao, escreva para a Free Software     
 *  Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA          
 *  02111-1307, USA.                                                  
 *  
 *  Copia da licenca no diretorio licenca/licenca_en.txt 
 *                                licenca/licenca_pt.txt 
 */

/**
 * Carregamos as libs necessárias
 */
require_once("libs/db_stdlib.php");
require_once("libs/db_conecta.php");
require_once("libs/db_sessoes.php");
require_once("libs/db_usuariosonline.php");
require_once("libs/db_utils.php");
require_once('libs/db_conn.php');
require_once("dbforms/db_funcoes.php");
require_once(modification("libs/db_liborcamento.php"));




function testa($var){
	echo "<pre>";
	print_r($var);
	echo "</pre>";
}

function buscaDadosNoSolicitacao($nosolicitacao){
	$sql = pg_query("SELECT distinct fc_estruturaldotacao(pc13_anousu,pc13_coddot) as estrutural, o55_projativ, o55_descr, o15_codigo, o15_descr, b.o56_descr as descrestrutural, pc13_codigo, pc13_anousu, pc13_coddot, pc13_quant, pc13_valor, b.o56_elemento as do56_elemento, pc01_servico, pc11_seq, pc11_codigo, pc11_quant, pc11_vlrun, pc11_prazo, pc11_pgto, pc11_resum, pc11_just, a.o56_descr as descrele,o41_descr, m61_abrev, m61_descr, pc17_quant, pc01_codmater, pc01_descrmater, case when pc11_vlrun is not null and pc11_vlrun > 0 then pc11_vlrun else (pc13_valor / pc13_quant) end as pc13_valtot, (pc11_vlrun*pc11_quant) as pc11_valtot, m61_usaquant,a.o56_elemento as so56_elemento from solicitem left join solicitemunid on solicitemunid.pc17_codigo = solicitem.pc11_codigo left join matunid on matunid.m61_codmatunid = solicitemunid.pc17_unid left join solicitempcmater on solicitempcmater.pc16_solicitem = solicitem.pc11_codigo left join pcmater on pcmater.pc01_codmater = solicitempcmater.pc16_codmater left join pcsubgrupo on pcsubgrupo.pc04_codsubgrupo = pcmater.pc01_codsubgrupo left join pctipo on pctipo.pc05_codtipo = pcsubgrupo.pc04_codtipo left join solicitemele on solicitemele.pc18_solicitem = solicitem.pc11_codigo left join orcelemento a on a.o56_codele = solicitemele.pc18_codele and a.o56_anousu=2022 left join pcdotac on pcdotac.pc13_codigo = solicitem.pc11_codigo left join orcreservasol on orcreservasol.o82_pcdotac = pcdotac.pc13_sequencial left join orcreserva on orcreserva.o80_coddot = pcdotac.pc13_coddot and orcreserva.o80_codres = orcreservasol.o82_codres left join orcdotacao on orcdotacao.o58_coddot = pcdotac.pc13_coddot and orcdotacao.o58_anousu = pcdotac.pc13_anousu left join orcprojativ on orcprojativ.o55_projativ = orcdotacao.o58_projativ and orcprojativ.o55_anousu = orcdotacao.o58_anousu left join orcelemento b on b.o56_codele = orcdotacao.o58_codele and b.o56_anousu = 2022 left join orctiporec on orctiporec.o15_codigo = orcdotacao.o58_codigo left join solicitemregistropreco on pc57_solicitem = pc11_codigo left join orcunidade on orcdotacao.o58_orgao = orcunidade.o41_orgao and orcdotacao.o58_anousu = orcunidade.o41_anousu and orcdotacao.o58_unidade = orcunidade.o41_unidade where pc11_numero = {$nosolicitacao} order by pc13_coddot,pc13_codigo");
	$resultado = pg_fetch_all($sql);
	return $resultado;
}

/*function buscaValoresReservados($solicitacao){
	$sql = pg_query("SELECT solicitem.* from solicitem inner join pcprocitem on pcprocitem.pc81_solicitem = solicitem.pc11_codigo inner join empautitempcprocitem on empautitempcprocitem.e73_pcprocitem = pcprocitem.pc81_codprocitem inner join empautitem on empautitem.e55_autori = empautitempcprocitem.e73_autori and empautitem.e55_sequen = empautitempcprocitem.e73_sequen inner join empautoriza on e54_autori = e55_autori where pc11_numero = {$solicitacao} and e54_anulad is null");
	$resultado = pg_fetch_all($sql);
	return $resultado;
}*/


function buscaValoresReservados($solicitacao){
    $sql = pg_query("SELECT pc11_codigo as codsol,pc11_vlrun, pc11_quant from solicitem inner join solicitempcmater on solicitempcmater.pc16_solicitem = solicitem.pc11_codigo inner join pcmater on pcmater.pc01_codmater = solicitempcmater.pc16_codmater inner join pcsubgrupo on pcsubgrupo.pc04_codsubgrupo = pcmater.pc01_codsubgrupo inner join pctipo on pctipo.pc05_codtipo = pcsubgrupo.pc04_codtipo where pc11_numero = {$solicitacao} order by pc11_codigo");
    $resultado = pg_fetch_all($sql);
    return $resultado;
}



function buscaLogo(){
    $sql = pg_query("SELECT nomeinst, db21_compl, trim(ender)||', '||trim(cast(numero as text)) as ender, trim(ender) as rua, munic, numero, uf, cgc, cep, telef, email, url, logo from db_config where codigo = " . db_getsession("DB_instit"));
    $resultado = pg_fetch_all($sql);
    return $resultado[0];
}

function buscaProcesso($solicitacao){
    $sql = pg_query("SELECT pc90_numeroprocesso from solicitaprotprocesso where pc90_solicita = {$solicitacao}");
    $resultado = pg_fetch_all($sql);
    return $resultado[0]["pc90_numeroprocesso"];
}


function contaDotacoes($solicitacao){
    $sql = pg_query("SELECT distinct pc13_coddot from solicitem left join solicitemunid on solicitemunid.pc17_codigo = solicitem.pc11_codigo left join matunid on matunid.m61_codmatunid = solicitemunid.pc17_unid left join solicitempcmater on solicitempcmater.pc16_solicitem = solicitem.pc11_codigo left join pcmater on pcmater.pc01_codmater = solicitempcmater.pc16_codmater left join pcsubgrupo on pcsubgrupo.pc04_codsubgrupo = pcmater.pc01_codsubgrupo left join pctipo on pctipo.pc05_codtipo = pcsubgrupo.pc04_codtipo left join solicitemele on solicitemele.pc18_solicitem = solicitem.pc11_codigo left join orcelemento a on a.o56_codele = solicitemele.pc18_codele and a.o56_anousu=2022 left join pcdotac on pcdotac.pc13_codigo = solicitem.pc11_codigo left join orcreservasol on orcreservasol.o82_pcdotac = pcdotac.pc13_sequencial left join orcreserva on orcreserva.o80_coddot = pcdotac.pc13_coddot and orcreserva.o80_codres = orcreservasol.o82_codres left join orcdotacao on orcdotacao.o58_coddot = pcdotac.pc13_coddot and orcdotacao.o58_anousu = pcdotac.pc13_anousu left join orcprojativ on orcprojativ.o55_projativ = orcdotacao.o58_projativ and orcprojativ.o55_anousu = orcdotacao.o58_anousu left join orcelemento b on b.o56_codele = orcdotacao.o58_codele and b.o56_anousu = 2022 left join orctiporec on orctiporec.o15_codigo = orcdotacao.o58_codigo left join solicitemregistropreco on pc57_solicitem = pc11_codigo left join orcunidade on orcdotacao.o58_orgao = orcunidade.o41_orgao and orcdotacao.o58_anousu = orcunidade.o41_anousu and orcdotacao.o58_unidade = orcunidade.o41_unidade where pc11_numero = {$solicitacao} order by pc13_coddot");
    $resultado = pg_fetch_all($sql);
    return $resultado;
}

function formatar ($tipo = "", $string, $tamanho = 10){
    $string = ereg_replace("[^0-9]", "", $string);
    
    switch ($tipo){
        case 'fone':
            if($tamanho === 10){
             $string = '(' . substr($string, 0, 2) . ') ' . substr($string, 2, 4) 
             . '-' . substr($string, 6);
         }else
         if($tamanho === 11){
             $string = '(' . substr($string, 0, 2) . ') ' . substr($string, 2, 5) 
             . '-' . substr($string, 7);
         }
         break;
        case 'cep':
            $string = substr($string, 0, 5) . '-' . substr($string, 5, 3);
         break;
        case 'cpf':
            $string = substr($string, 0, 3) . '.' . substr($string, 3, 3) . 
                '.' . substr($string, 6, 3) . '-' . substr($string, 9, 2);
         break;
        case 'cnpj':
            $string = substr($string, 0, 2) . '.' . substr($string, 2, 3) . 
                '.' . substr($string, 5, 3) . '/' . 
                substr($string, 8, 4) . '-' . substr($string, 12, 2);
         break;
        
    }
    return $string;
}


$nosolicitacao = $_GET["pc10_numero"];
$dadossolicitacao = buscaDadosNoSolicitacao($nosolicitacao);

//testa($dadossolicitacao); die("Confere");



//$unidadeorcamentaria = $dadossolicitacao[0]["o41_descr"];
//$elementodadespesa = $dadossolicitacao[0]["do56_elemento"] . "." . $dadossolicitacao[0]["o15_codigo"];

//$pegafuncional = explode($elementodadespesa, $dadossolicitacao[0]["estrutural"]);
//$funcionalprogramatica = rtrim($pegafuncional[0], ".");

//$codigodespesa = $dadossolicitacao[0]["pc13_coddot"];
//$fontederecursos = $dadossolicitacao[0]["o15_descr"];
//$titulodespesa = $dadossolicitacao[0]["descrestrutural"];
//$titulofuncional = $dadossolicitacao[0]["o55_descr"];


$nivel = 8;
$anousu = db_getsession("DB_anousu");
$dPeriodoIni = date("Y-m") . "-01";
$dPeriodoFim = date("Y-m-d");

//$consultasaldos = db_dotacaosaldo($nivel, 2, 2, true, " o58_coddot = {$codigodespesa} and o58_anousu = {$anousu} ", $anousu, $dPeriodoIni, $dPeriodoFim);
//$saldos = pg_fetch_all($consultasaldos);

$npa = buscaProcesso($nosolicitacao);

/*
$reservados = buscaValoresReservados($nosolicitacao);
$totalreservado = 0;

foreach ($reservados as $linha) {
	$vl = $linha["pc11_quant"] * $linha["pc11_vlrun"];	
	$totalreservado += $vl;
}
$totalreservado = $totalreservado;
$saldoanterior = $saldos[0]["atual_menos_reservado"];
$saldoatual = $saldoanterior - $totalreservado;


$totalreservado = number_format($totalreservado, 2, ",", ".");
$saldoanterior = number_format($saldos[0]["atual_menos_reservado"], 2, ",", ".");
$saldoatual = number_format($saldoatual, 2, ",", ".");
*/





//BUSCA LOGO

    //$url = @pg_result($dados,0,"url");
    //$this->SetXY(1,1);
    //$this->Image('imagens/files/'.pg_result($dados,0,"logo"),7,3,20);
$urlimagem = buscaLogo();
$nomedept = db_getsession("DB_nomedepto");
//testa($urlimagem); die("Confere");

$pegacep = $urlimagem["cep"];
$pegacnpj = $urlimagem["cgc"];
$pegatel = $urlimagem["telef"];


$cep = formatar("cep", $pegacep);
$cnpj = formatar("cnpj", $pegacnpj);
$telefone = formatar("fone", $pegatel);


$bannerimagem = "imagens/files/".$urlimagem["logo"];
$clinha1 = $urlimagem["rua"] . " - " . $urlimagem["munic"] . "/" . $urlimagem["uf"];
$clinha2 = "CEP: " . $cep . " - CNPJ: " . $cnpj . "TEL.: " . $telefone;
$clinha3 = "email: " . $urlimagem["email"] . " - " . $urlimagem["url"];








//$nofolha = 1;

$titulorel = "CONTROLE ORÇAMENTÁRIO";
$hoje = date("Y-m-d");
$datanormal = date("d/m/Y");

setlocale(LC_TIME, 'pt_BR', 'pt_BR.utf-8', 'pt_BR.utf-8', 'portuguese');
date_default_timezone_set('America/Sao_Paulo');
$geracao = strftime('%d de %B de %Y', strtotime(implode("", explode("-", $hoje))));
$geracao = utf8_encode($geracao);

$textodeclaracao = "Declaro de acordo com o Inciso II, Art. 16, da Lei Complementar nº 101 de 04 de maio de 2000. L.R.F., que a presente despesa possui adequação orçamentária e financeira com a Lei Orçamentária Anual e compatibilidade com o Plano Plurianual e com a Lei de Diretrizes Orçamentárias.";

require_once('tcpdf_include.php');
//$PDF_PAGE_ORIENTATION_LOCAL = "L";
$PDF_PAGE_ORIENTATION_LOCAL = "P";

$pdf = new TCPDF($PDF_PAGE_ORIENTATION_LOCAL, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);


define('K_TCPDF_THROW_EXCEPTION_ERROR', true);

$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('...');
$pdf->SetTitle($titulorel);
$pdf->SetSubject('Relatório');


$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE.' 006', PDF_HEADER_STRING);
$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
$margin_top = 1;
$pdf->SetMargins(PDF_MARGIN_LEFT - 5, $margin_top, PDF_MARGIN_RIGHT - 5);
$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
//$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
$pdf->SetFooterMargin(1);
//10
//$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
$pdf->SetAutoPageBreak(false);
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);


if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
    require_once(dirname(__FILE__).'/lang/eng.php');
    $pdf->setLanguageArray($l);
}

// remove default header/footer
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);

$guarda_dotacao = "";
foreach ($dadossolicitacao as $linha) {    
    
    if($linha["pc13_coddot"] == $guarda_dotacao){continue;}
        
    
    $guarda_dotacao = $linha["pc13_coddot"];
    $unidadeorcamentaria = $linha["o41_descr"];    
    $elementodadespesa = $linha["do56_elemento"] . "." . $linha["o15_codigo"];
    $pegafuncional = explode($elementodadespesa, $linha["estrutural"]);
    $funcionalprogramatica = rtrim($pegafuncional[0], ".");
    $codigodespesa = $linha["pc13_coddot"];
    $fontederecursos = $linha["o15_descr"];
    $titulodespesa = $linha["descrestrutural"];
    $titulofuncional = $linha["o55_descr"];



    $consultasaldos = db_dotacaosaldo($nivel, 2, 2, true, " o58_coddot = {$codigodespesa} and o58_anousu = {$anousu} ", $anousu, $dPeriodoIni, $dPeriodoFim);
    $saldos = pg_fetch_all($consultasaldos);


    $reservados = buscaValoresReservados($nosolicitacao);
    $totalreservado = 0;
    

    foreach ($reservados as $linha2) {
        $vl = $linha2["pc11_quant"] * $linha2["pc11_vlrun"];  
        $totalreservado += $vl;
    }
    
    $totalreservado = $totalreservado;
    $saldoanterior = $saldos[0]["atual_menos_reservado"];
    $saldoatual = $saldoanterior - $totalreservado;

    $totalreservado = number_format($totalreservado, 2, ",", ".");
    $saldoanterior = number_format($saldos[0]["atual_menos_reservado"], 2, ",", ".");
    $saldoatual = number_format($saldoatual, 2, ",", ".");




//$elementodadespesa = $linha["do56_elemento"] . "." . $linha["o15_codigo"];
   
$pdf->SetFont('dejavusans', '', 10);
$pdf->AddPage();
//<h2>Certificado nº: <span style="font-weight:normal">'. $dados["nocertificado"] .'</span></h2>
    $html = '
        <table cellpadding="5">
        <br><br>
            <tr style="text-align:left;">
                <td width="10%"><img src="'.$bannerimagem.'"></td>
                <td width="50%" style="text-align:center">
                    <span><b>'.$urlimagem["nomeinst"].'</b></span>
                    <h5>'.$nomedept.'</h5>
                    <h6>'.utf8_encode($clinha1).'</h6>
                    <h6>'.$clinha2.'</h6>
                    <h6>'.$clinha3.'</h6>
                </td>
                <td width="40%">
                    <table cellpadding="4" border="1">
                    <tr style="text-align:center;">
                        <td width="30%" style="text-align:center;font-size:9px">Processo</td>
                        <td width="25%" style="text-align:center;font-size:9px">Data</td>
                        <td width="25%" style="text-align:center;font-size:9px">Documento</td>
                        <td width="20%" style="text-align:center;font-size:9px">Nº Folha</td>
                    </tr>
                    <tr style="text-align:center;">
                        <td width="30%" style="text-align:center;font-size:9px">'.$npa.' </td>
                        <td width="25%" style="text-align:center;font-size:9px">'.$datanormal.'</td>
                        <td width="25%" style="text-align:center;font-size:9px"> </td>
                        <td width="20%" style="text-align:center;font-size:9px"> </td>
                    </tr>
                    </table>
                </td>
            </tr>
        </table>


        <table>
        
        
        <br><br><br>
            <tr style="text-align:center;">
                <td width="95%"><h1><u>'.$titulorel.'</u></h1></td>
            </tr>
        </table>';

        $html .= '

        <br><br><br><br>

        <table cellpadding="5" border="1">
        <tr style="text-align:center;">
                <td width="25%"><h4>Unidade Orçamentária.</h4></td>
                <td width="25%"><h4>Funcional Programática</h4></td>
                <td width="25%"><h4>Elemento da Despesa</h4></td>
                <td width="25%"><h4>Código da Despesa e-Cidade</h4></td>
        </tr>

        <tr style="text-align:center;">
            <td width="25%">'.$unidadeorcamentaria.'</td>
            <td width="25%">'.$funcionalprogramatica.'</td>
            <td width="25%">'.$elementodadespesa.'</td>
            <td width="25%">'.$codigodespesa.'</td>            
        </tr>
        </table>

        <br><br>

        <table cellpadding="5" border="1">
        <tr style="text-align:center;">
            <td width="25%"><h4>Saldo Anterior</h4></td>
	        <td width="25%"><h4>Valor Provável da Despesa</h4></td>
	        <td width="25%"><h4>Saldo Atual</h4></td>
	        <td width="25%"><h4>Solicitação de Compras Nº</h4></td>
        </tr>

        <tr style="text-align:center;">
            <td width="25%">R$ '.$saldoanterior.' </td>
            <td width="25%">R$ '.$totalreservado.'</td>
            <td width="25%">R$ '.$saldoatual.'</td>
            <td width="25%">'.$nosolicitacao.'</td>            
        </tr>
        </table>

        <br><br>
        <table cellpadding="5" border="1">
        <tr>
        	<td width="25%"><h4>Fonte de Recursos</h4></td>
        	<td width="75%">'.utf8_encode($fontederecursos).'</td>
        </tr>

        <tr>
        	<td width="25%"><h4>Título da Despesa</h4></td>
        	<td width="75%">'.utf8_encode($titulodespesa).'</td>
        </tr>

        <tr>
        	<td width="25%"><h4>Título da Funcional</h4></td>
        	<td width="75%">'.utf8_encode($titulofuncional).'</td>
        </tr>        
        </table>
        <br>

        <h3 style="text-align:center">Volta Redonda, '.$geracao.'</h3>
        <br>


        <table cellpadding="5">
        <tr style="text-align:center;">
        	<td width="50%">Emitido por</td>
        	<td width="50%">Responsável</td>
        </tr>
        <br><br><br>
        <tr style="text-align:center;">
        	<td width="50%">Finanças/Contabilidade</td>
        	<td width="50%">Maria do Rosário da Silva Costa</td>
        </tr>

        <tr style="text-align:center;">
        	<td width="50%"> </td>
        	<td width="50%">Contadora CRC RJ 053592/0-3</td>
        </tr>
        </table>

        <br><br>
        
        <h1 style="text-align:center">DECLARAÇÃO DO ORDENADOR DE DESPESA</h1>
        <p align="justify">'.$textodeclaracao.'</span>

        <br><br><br>


        <h3 style="text-align:center">Volta Redonda, '.$geracao.'</h3>
        

        <table cellpadding="5">
        <br><br><br>
        <tr style="text-align:center;"><td>Sebastião Faria de Souza</td></tr>
        <tr style="text-align:center;"><td>Diretor Geral</td></tr>
        </table>
        ';    
    $pdf->writeHTML($html, true, false, true, false, '');    
    //$nofolha++;
} //Fim do foreach
    $pdf->lastPage();
    
    ob_end_clean();
    $pdf->Output('controle-orcamentario.pdf', 'I');

?>




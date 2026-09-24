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

require_once('libs/db_stdlib.php');
require_once('libs/db_conecta.php');
require_once('dbforms/db_funcoes.php');
require_once('libs/db_sessoes.php');
require_once('libs/db_usuariosonline.php');
require_once('libs/db_utils.php');
require_once('std/db_stdClass.php');
require_once('libs/db_libsys.php');
require_once('dbagata/classes/core/AgataAPI.class');
require_once('model/documentoTemplate.model.php');
require_once("classes/db_parjuridico_classe.php");
include("mpdf60/mpdf.php");

function buscaDados($codigoTermo){
  $sql = pg_query("SELECT * FROM bensguarda INNER JOIN bensguardaitem ON t21_codigo = t22_bensguarda WHERE t21_codigo = {$codigotermo} ");
  $resultado = pg_fetch_all($sql);
  return $resultado;
}


$oGet = db_utils::postMemory($_GET);

$dados = buscaDados($oGet->iCodigoTermo);




function testa($var){
  echo "<pre>";
  print_r($var);
  echo "</pre>";
}




function retornaLogo(){
  $instituicao = db_getsession("DB_instit");
  $sql = pg_query("SELECT logo FROM db_config WHERE codigo = {$instituicao}");
  $resultado = pg_fetch_all($sql);
  $url = "imagens/files/" . $resultado[0]["logo"];
  return $url;
}

function retornaLogoPrefeitura(){
  $instituicao = db_getsession("DB_instit");
  $sql = pg_query("SELECT logo FROM db_config WHERE codigo = 1");
  $resultado = pg_fetch_all($sql);
  $url = "imagens/files/" . $resultado[0]["logo"];
  return $url;
}

function dadosInstituicao(){
  $instituicao = db_getsession("DB_instit");
  $sql = pg_query("SELECT * FROM db_config WHERE codigo = {$instituicao}");
  $resultado = pg_fetch_all($sql);
  return $resultado[0];  
}

function buscaInformacoes($codigo){
  $sql = pg_query("SELECT * FROM bensguarda INNER JOIN bensguardaitem ON t21_codigo = t22_bensguarda INNER JOIN bens ON t22_bem = t52_bem WHERE t21_codigo = {$codigo}");
  $resultado = pg_fetch_all($sql);
  return $resultado[0];
}

function buscaNomeResponsavel($cgm){
  $sql = pg_query("SELECT z01_nome FROM cgm WHERE z01_numcgm = {$cgm}");
  $resultado = pg_fetch_all($sql);
  return $resultado[0]["z01_nome"];
}

function buscaNomeInst($inst){
  $sql = pg_query("SELECT nomeinst FROM db_config WHERE codigo = {$inst}");
  $resultado = pg_fetch_all($sql);
  return $resultado[0]["nomeinst"];
}

function buscaNomeDivisao($cgm){
  $sql = pg_query("SELECT t30_descr FROM departdiv WHERE t30_numcgm = {$cgm}");
  $resultado = pg_fetch_all($sql);
  return $resultado[0]["t30_descr"];
}

function buscaNomeUsuarioLogado($id){
  $sql = pg_query("SELECT nome FROM db_usuarios WHERE id_usuario = {$id}");
  $resultado = pg_fetch_all($sql);
  return $resultado[0]["nome"];
}

function buscaProdutos($codigo){
  //$sql = pg_query("SELECT t52_ident, t52_descr, t21_obs, t22_obs FROM bensguarda INNER JOIN bensguardaitem ON t21_codigo = t22_bensguarda INNER JOIN bens ON t22_bem = t52_bem WHERE t21_codigo = {$codigo}");
  $sql = pg_query("SELECT t52_ident, t52_descr, t21_obs, t22_obs, t23_responsavel from bensguardaitem inner join db_usuarios on db_usuarios.id_usuario = bensguardaitem.t22_usuario inner join bens on bens.t52_bem = bensguardaitem.t22_bem inner join bensguarda on bensguarda.t21_codigo = bensguardaitem.t22_bensguarda inner join cgm on cgm.z01_numcgm = bens.t52_numcgm inner join db_depart on db_depart.coddepto = bens.t52_depart inner join clabens on clabens.t64_codcla = bens.t52_codcla inner join cgm as a on a.z01_numcgm = bensguarda.t21_numcgm inner join bensplaca on bensplaca.t41_bem = bens.t52_bem left join bensguardaitemdev on bensguardaitemdev.t23_guardaitem = bensguardaitem.t22_codigo where t22_bensguarda={$codigo} and t23_guardaitem is null");

   
  $resultado = pg_fetch_all($sql);
  return $resultado;
}


$logo = retornaLogo();
$logoprefeitura = retornaLogoPrefeitura();
$di = dadosInstituicao();
$endereco = $di["ender"];
$municipio = $di["munic"];
$uf = $di["uf"];
$cep = $di["cep"];
$cep = substr($cep, 0, 5) .'-'. substr($cep, 5, 3);
//$cgc = $di["cgc"];
$cgc = "28.307.379/0001-04";
$telefone = $di["telef"];
$telefone = '('.substr($telefone, 0, 2).') ' . substr($telefone, 2, 9);
$email = $di["email"];
setlocale(LC_TIME, 'pt_BR', 'pt_BR.utf-8', 'pt_BR.utf-8', 'portuguese');
date_default_timezone_set('America/Sao_Paulo');
$diadehoje = strftime('%d de %B de %Y', strtotime('today'));
$nomeusuario = buscaNomeUsuarioLogado(db_getsession("DB_id_usuario"));





$dados = buscaInformacoes($oGet->iCodigoTermo);
//$nomeresponsavel = buscaNomeResponsavel($dados["t21_numcgm"]);
$nomeinstituicao = buscaNomeInst($dados["t21_instit"]);
//$nomedivisao = buscaNomeDivisao($dados["t21_numcgm"]);
$nomedivisao = buscaNomeResponsavel($dados["t21_numcgm"]);
$produtos = buscaProdutos($oGet->iCodigoTermo);
$nomeresponsavel = $produtos[0]["t23_responsavel"];
//$produtos = buscaDados($oGet->iCodigoTermo);
//var_dump($produtos); die("Confere");
$produtostb = "";
$produtostb2 = "";
$obs1 = "";

$textocurto = substr($dados["t21_obs"], 0, 200);
//<td colspan=6><b>'.$dados["t21_obs"].'</b></td>
if(!empty($dados["t21_obs"])){
  $obs1 = '
  <tr>
    <td colspan=6 style="text-align:center">Notas Diversas</td>
  </tr>
  <tr>
    <td colspan=6><b>'.$textocurto.'</b></td>
  </tr>
  <hr>
  ';
}

$i = 1;
$c = 1;

if(empty($nomeresponsavel)){
  $nomeresponsavel = utf8_decode($oGet->nomeResponsavel);
}

$nummatricula = $oGet->numMatricula;

//testa($produtos); die("Confere");
//12 por página
foreach ($produtos as $linha){  
    //[t52_ident] => 04172
    //[t52_descr] => ROTEADOR
    //[t21_obs] => TESTE EDIÇÃO
    //[t22_obs] => OBSERVAÇÃO 2
    if($c <= 12) {
      $produtostb .='
  <tr>
    <td style="width:5%;text-align:center"><i><b>'.$c.'</b></i></td>
    <td style="width:20%;text-align:center"><i><b>'.$linha["t52_ident"].'</b></i></td>
    <td style="width:30%;text-align:center"><i><b>'.$linha["t52_descr"].'</b></i></td>
    <td style="width:45%;text-align:center"><i><b>'.substr($linha["t22_obs"], 0, 50) .'</b></i></td>
  </tr>
  ';  
    } else {
      $produtostb2 .='
  <tr>
    <td style="width:5%;text-align:center"><i><b>'.$c.'</b></i></td>
    <td style="width:20%;text-align:center"><i><b>'.$linha["t52_ident"].'</b></i></td>
    <td style="width:30%;text-align:center"><i><b>'.$linha["t52_descr"].'</b></i></td>
    <td style="width:45%;text-align:center"><i><b>'.substr($linha["t22_obs"], 0, 50) .'</b></i></td>
  </tr>
  ';
    }
    
    /*$produtostb .='
  <tr>
    <td style="width:5%;text-align:center"><i><b>'.$c.'</b></i></td>
    <td style="width:20%;text-align:center"><i><b>'.$linha["t52_ident"].'</b></i></td>
    <td style="width:30%;text-align:center"><i><b>'.$linha["t52_descr"].'</b></i></td>
    <td style="width:45%;text-align:center"><i><b>'.substr($linha["t22_obs"], 0, 50) .'</b></i></td>
  </tr>
  ';*/
  
  $c++;
  $i++;
}



 
$mpdf=new mPDF('en-GB-x','A4','','',10,10,10,10,6,3); 
$mpdf->charset_in='windows-1252';
$mpdf->SetDisplayMode('fullpage');


$html2 = '
<div style="width: 800px;height: 1200px; border: 1px solid black;position:relative">
  
  <div style="width:20%;display: inline-block;float:left"><img src="'.$logo.'"></div>
  <div style="width:80%;text-align:left;display: inline-block;float:right">
  '.$endereco.$municipio.'/'.$uf.'
  <br>
  CEP: '.$cep.' - '.$telefone.' - '.$email.'
  <br>
  CNPJ: '.$cgc.'
  </div>
  
  
  <div style="clear: both; margin: 0pt; padding: 0pt; "></div>

  


  <div style="width: 100%;text-align: center; border-top: 1px solid black; border-bottom: 1px solid black;padding: 5px 0 5px 0"><i><b>TERMO DE DEVOLUÇÃO DE BENS PATRIMONIAIS</b></i></div>

  <div style="width: 95%; padding: 20px;border-bottom: 1px solid black">
    Eu, '.$nomeresponsavel.' , retiro da minha responsabilidade<br>
      perante a '.$nomeinstituicao.', o(s) patrimônio(s) abaixo<br>
      relacionado(s), que doravante estavam sob responsabilidade do '.$nomedivisao.'.
  </div>

<div style="height:500px">
  <table cellspacing="0" border="0" style="width:750px;">  
  
  <tr>
    <td style="border-bottom: 1px solid black;width:5%;text-align:center">Nº</td>
    <td style="border-bottom: 1px solid black;width:20%;text-align:center">Patrimônio</td>
    <td style="border-bottom: 1px solid black;width:30%;text-align:center">Discriminação</td>
    <td style="border-bottom: 1px solid black;width:45%;text-align:center">Observações</td>
  </tr>
  
  
  '.$produtostb.'
  



  </table>
</div>






<div style="width:100%; text-align:center;margin-top:90px">
<p><i><b>Por ser verdade, firmo o presente</b></i></p>
<p>Volta Redonda, '.$diadehoje.'</p>
</div>


<div style="width:59%; display:inline-block; border:2px solid black;height:30px;float:left;height:88px;">
  <div style="width:30%;display:inline-block;border-right: 1px solid black;float:left;text-align:center;">
    <p>De Acordo:</p>
    <p>__/__/____</p>
  </div>

  <div style="width:69%;display:inline-block;float:right;font-size:12px;vertical-allign:bottom">
    <br> 
    <br>
    <hr>
    '.$nummatricula .' - '.$nomeresponsavel.'
  </div>
</div>

<div style="width:39%; display:inline-block; border:2px solid black; height:30px;float:right; font-size:12px; height:92px;text-align:center">
    <br> 
    <br>
    <hr>
    '.$nomeusuario.'
</div>


</div>




';
$mpdf->WriteHTML($html2);

if($c > 12){  

//Se passar de 15
$mpdf->addPage();
$html3 = '
<div style="width: 800px;height: 1200px; border: 1px solid black;position:relative">
  <div style="width:20%;display: inline-block;float:left"><img src="'.$logo.'"></div>
  <div style="width:80%;text-align:left;display: inline-block;float:right">
  '.$endereco.$municipio.'/'.$uf.'
  <br>
  CEP: '.$cep.' - '.$telefone.' - '.$email.'
  <br>
  CNPJ: '.$cgc.'
  </div>
  
  
  <div style="clear: both; margin: 0pt; padding: 0pt; "></div>

  


  <div style="width: 100%;text-align: center; border-top: 1px solid black; border-bottom: 1px solid black;padding: 5px 0 5px 0"><i><b>TERMO DE DEVOLUÇÃO DE BENS PATRIMONIAIS</b></i></div>

  <div style="width: 95%; padding: 20px;border-bottom: 1px solid black">
    Eu, '.$nomeresponsavel.' , retiro da minha responsabilidade<br>
      perante a '.$nomeinstituicao.', o(s) patrimônio(s) abaixo<br>
      relacionado(s), que doravante estavam sob responsabilidade do '.$nomedivisao.'.
  </div>

<div style="height:500px">
  <table cellspacing="0" border="0" style="width:750px;">  
  
  <tr>
    <td style="border-bottom: 1px solid black;width:5%;text-align:center">Nº</td>
    <td style="border-bottom: 1px solid black;width:20%;text-align:center">Patrimônio</td>
    <td style="border-bottom: 1px solid black;width:30%;text-align:center">Discriminação</td>
    <td style="border-bottom: 1px solid black;width:45%;text-align:center">Observações</td>
  </tr>
  
  
  '.$produtostb2.'
  



  </table>
</div>






<div style="width:100%; text-align:center;margin-top:90px">
<p><i><b>Por ser verdade, firmo o presente</b></i></p>
<p>Volta Redonda, '.$diadehoje.'</p>
</div>


<div style="width:59%; display:inline-block; border:2px solid black;height:30px;float:left;height:88px;">
  <div style="width:30%;display:inline-block;border-right: 1px solid black;float:left;text-align:center;">
    <p>De Acordo:</p>
    <p>__/__/____</p>
  </div>

  <div style="width:69%;display:inline-block;float:right;font-size:12px;vertical-allign:bottom">
    <br> 
    <br>
    <hr>
    '.$nummatricula .' - '.$nomeresponsavel.'
  </div>
</div>

<div style="width:39%; display:inline-block; border:2px solid black; height:30px;float:right; font-size:12px; height:92px;text-align:center">
    <br> 
    <br>
    <hr>
    '.$nomeusuario.'
</div>

  
</div>
';
$mpdf->WriteHTML($html3);
}

$mpdf->Output();
exit;

 




/*
$oDaoParjuridico  = new cl_parjuridico;

$iModeloImpressao = $oGet->iModeloImpressao;

ini_set("error_reporting","E_ALL & ~NOTICE");

$oAgata           = new cl_dbagata("patrimonio/devolucao_termo_guarda.agt");
$oApiAgata        = $oAgata->api;
$sCaminhoSalvoSxw = "tmp/__autorizacaoCompras" . date('YmdHis') . "_" . db_getsession("DB_id_usuario") . ".sxw";

$oApiAgata->setOutputPath($sCaminhoSalvoSxw);
$oApiAgata->setParameter('$iCodigoTermo', $oGet->iCodigoTermo);
$oApiAgata->setParameter('$sFuncao', $oGet->sFuncao);
$oApiAgata->setParameter('$iInstituicao', db_getsession("DB_instit"));

try {
  
  $oDocumentoTemplate = new documentoTemplate(32, $iModeloImpressao);

} catch (Exception $eException){

  $sErroMsg  = $eException->getMessage();
  db_redireciona("db_erros.php?fechar=true&db_erro={$sErroMsg}");
}

if ( $oApiAgata->parseOpenOffice( $oDocumentoTemplate->getArquivoTemplate() ) ) {

  $sNomeRelatorio   = "tmp/autorizacao_processo_compras" . date('YmdHis') . "_" . db_getsession("DB_id_usuario") . ".pdf";
  $lConversao       = db_stdClass::ex_oo2pdf($sCaminhoSalvoSxw, $sNomeRelatorio);

  if (!$lConversao) {
    db_redireciona("db_erros.php?fechar=true&db_erro=[3]Falha ao gerar PDF !!!");
  } else {
    db_redireciona($sNomeRelatorio);
  }
}
*/
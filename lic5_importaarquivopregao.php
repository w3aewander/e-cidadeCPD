<?php
/*
 *     E-cidade Software Publico para Gestao Municipal                
 *  Copyright (C) 2013  DBselller Servicos de Informatica             
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

require_once("libs/db_stdlib.php");
require_once("libs/db_conecta.php");
require_once("libs/db_sessoes.php");
require_once("libs/db_usuariosonline.php");
require_once("libs/db_utils.php");
require_once("dbforms/db_funcoes.php");
require_once("classes/db_pcorcam_classe.php");
require_once("classes/db_pcorcamval_classe.php");
require_once("classes/db_pcorcamitem_classe.php");
require_once("classes/db_pcorcamdescla_classe.php");

db_postmemory($_POST);

$clrotulo = new rotulocampo;
$clrotulo->label("l20_codigo");

$erro   = false;
$instit = db_getsession("DB_instit");

$clpcorcam   = new cl_pcorcam;
$clpcorcamval = new cl_pcorcamval;
$clpcorcamitem  = new cl_pcorcamitem;
$clpcorcamdescla  = new cl_pcorcamdescla;
  
$db_opcao = 1;
$db_botao = true;
$situacao = 0;
$taman    = 55;
$iInstitSessao = db_getsession("DB_instit");

if (isset($processar)) {

  db_postmemory($_FILES["arqret"]);

  $arq_name    = basename($name);
  $arq_type    = $type;
  $arq_tmpname = basename($tmp_name);
  $arq_size    = $size;
  $arq_array = file($tmp_name);

  system("cp -f ".$tmp_name." ".$DOCUMENT_ROOT."/tmp");
  system("rename ".$DOCUMENT_ROOT."/tmp/".$arq_tmpname." ".$DOCUMENT_ROOT."/tmp/".$arq_name);
  
    $_tamanprilinha = $arq_array[0];
    $atipo          = substr($arq_array[0],0,3);
    $totalproc      = sizeof($arq_array)-1;
    $codlicit =  (int) substr($arq_array[$i],0,4);   


    if ($codlicit != $l20_codigo || $l20_codigo == "" || $codlicit == ""){
       $erro_msg =  "Codigo da licitacao não confere com o arquivo!";
       if($codlicit == ""){
         $erro_msg =  "Aquivo com codigo da licitacao invalido!";
        }else{
         $erro_msg =  "Digite o codigo da licitacao! ";
        }
      $erro = true;
    }  

    if ( $erro != true ){

     for ($i=0; $i <= $totalproc; $i++) {
 
      $descla = false;      

      $arr = explode(";",$arq_array[$i]);

      $codlicit =  (int) $arr[0];
      $numlicit =  (int) $arr[1];
      $coditem  =  (int) $arr[2];
      $seqitem  =  (int) $arr[3];
      $marca    =  $arr[4];
      $modelo   =  $arr[5];
      $codforne =  (int) $arr[6];
      $valor    =  (float) $arr[7] ; 
    

     if (substr(strtoupper(trim($arr[8])),0,4) == "DESC" ||substr(strtoupper(trim($arr[8])),0,3) == "NAO" ){
        $descla = true;
     }

      if (strtoupper(trim($arr[7])) == "FRACASSADA" || strtoupper(trim($arr[7]) == "DESERTA") || strtoupper(trim($arr[7]) == "ANULADA")){

         $erro_msg =  "ESTA LICITAÇÃO FOI ".strtoupper(trim($arr[7]));
         $erro = true;
 
      } else{

             $sSqlItens     = $clpcorcamitem->sql_query_pcmaterlic(null,
                                                          "pcorcamforne.pc21_orcamforne, 
                                                           pcorcamitem.pc22_orcamitem, solicitem.pc11_quant 
                                                           ",
                                                           "",
                                                           "l21_codpcprocitem ={$coditem} and l20_codigo = {$codlicit} 
                                                             and pc21_numcgm = {$codforne} and l21_situacao = 0");
              $result_itens  = $clpcorcamitem->sql_record($sSqlItens);
              $numrows_itens = $clpcorcamitem->numrows;

         if ($numrows_itens>0){   

               db_fieldsmemory($result_itens,0);

             if (isset($pc21_orcamforne)&&trim($pc21_orcamforne)!=""&&isset($pc22_orcamitem)&&trim($pc22_orcamitem)!=""){

                  
                  
                  $clpcorcamval->pc23_valor = ($valor * $pc11_quant);
                  $clpcorcamval->pc23_quant = $pc11_quant;
                  $clpcorcamval->pc23_obs = "MARCA: ".$marca." MODELO: ".$modelo;
                  $clpcorcamval->pc23_vlrun = $valor;
                  $clpcorcamval->pc23_validmin = null;
                  $clpcorcamval->incluir($pc21_orcamforne,$pc22_orcamitem);

                 if ($descla == true ){

                  $clpcorcamdescla->pc32_motivo = $arr[8];
                  $clpcorcamdescla->incluir($pc22_orcamitem,$pc21_orcamforne);

                  }
                 
             }else {

                $erro_msg =  "Fornecedor não encontrado para este item: ".$coditem;
                $erro = true;

             }

        }else{
            
              $erro_msg =  "Não foram encontrados itens para esta licitação: ".$codlicit;
              $erro = true;
        }

      }

    } 
 
 } 
  if($erro == true) {

    $alert = "alert('Ocorreu algum erro durante o processamento!\\nErro: $erro_msg')";
    echo "<script>$alert;</script>";
    db_redireciona();
  }  else {

    $alert = "alert('Arquivo importado com sucesso!!')";
    echo "<script>$alert;</script>";
    header('location:lic1_orcamlancval001.php?l20_codigo='.$codlicit.'&lic=true');
   }
}
?>
<html>
<head>
<title>Microsist</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<meta http-equiv="Expires" CONTENT="0">
<script language="JavaScript" type="text/javascript" src="scripts/scripts.js"></script>
<link href="estilos.css" rel="stylesheet" type="text/css">
</head>
<body bgcolor=#CCCCCC leftmargin="0" topmargin="0" marginwidth="0" marginheight="0" onLoad="a=1" style="margin-top: 10px;">
<table width="790" border="0" cellpadding="0" cellspacing="0">
  <tr>
    <td width="360" height="15">&nbsp;</td>
    <td width="263">&nbsp;</td>
    <td width="25">&nbsp;</td>
    <td width="140">&nbsp;</td>
  </tr>
</table>
<table width="790" border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td height="430" align="left" valign="top" bgcolor="#CCCCCC">
    <center>
	    <?php
        include("forms/db_frmprocessaarquivopregao.php");
      ?>
    </center>
	</td>
  </tr>
</table>
<?php
db_menu(db_getsession("DB_id_usuario"),db_getsession("DB_modulo"),db_getsession("DB_anousu"),db_getsession("DB_instit"));
?>
</body>
</html>
<?php
/*
    db_fim_transacao(true);        
   $alert = "alert('Ocorreu algum erro durante o processamento!\\nErro: $erro_msg')";
    echo "<script>$alert;</script>";
    db_redireciona();
*/
?>

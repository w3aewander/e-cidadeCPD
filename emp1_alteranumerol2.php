<?
/*
 *     E-cidade Software Publico para Gestao Municipal               
 *  Copyright (C) 2014  DBSeller Servicos de Informatica             
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
require_once("dbforms/db_funcoes.php");
require_once("libs/db_liborcamento.php");
require_once("classes/db_empempenho_classe.php");
require_once("classes/db_orcdotacao_classe.php");
require_once("classes/db_pcmater_classe.php");
require_once("classes/db_cgm_classe.php");
require_once("libs/db_app.utils.php");

function testa($var){
  echo "<pre>";
  print_r($var);
  echo "</pre>";
}

function buscaNumerolCod($empenho){  
  $sql = pg_query("SELECT e60_numerol FROM empempenho WHERE e60_numemp = {$empenho}");
  $resultado = pg_fetch_all($sql);
  return $resultado[0]["e60_numerol"];
}


$empenho = $_POST["e60_numemp"];



if(empty($e60_numemp)){
  header("Location: emp1_alteranumerol.php?volta=vazio");
} 

$numerol = buscaNumerolCod($empenho);
$numerol = trim($numerol);

if(isset($_POST["e60_numerol"])){
  
  if(empty($_POST["e60_numerol"]) || trim($_POST["e60_numerol"]) == ""){
    echo "<script>alert('Número da Licitação não pode ser vazio.');</script>";    
  }else{
    $numerol = trim($_POST["e60_numerol"]);
    pg_query("UPDATE empempenho SET e60_numerol = '{$numerol}' WHERE e60_numemp = {$empenho}");
    header("Location: emp1_alteranumerol.php?volta=alterado");    
    //var_dump("UPDATE empempenho SET e60_numerol = '{$numerol}' WHERE e60_numemp = {$empenho}");
  }
}
 
//testa($_POST); die("Confere");
?>

<html>
<head>
<title>Microsist</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<meta http-equiv="Expires" CONTENT="0">
<script language="JavaScript" type="text/javascript" src="scripts/scripts.js"></script>


<link href="estilos.css" rel="stylesheet" type="text/css">
<script>
    function js_mascara(evt){
      var evt = (evt) ? evt : (window.event) ? window.event : "";
      
      if( (evt.charCode >46 && evt.charCode <58) || evt.charCode ==0 ){//8:backspace|46:delete|190:. 
	return true;
      }else{
	return false;
      }  
    }
</script>
</head>
<body bgcolor=#CCCCCC leftmargin="0" topmargin="0" marginwidth="0" marginheight="0" onLoad="a=1" bgcolor="#cccccc">

<center>
<div style="margin-top: 20px; width: 450px;">
<form name="faltera" method="post" action="emp1_alteranumerol2.php">
  <fieldset>
    <legend><strong>Consulta Empenho</strong></legend>
    <table border='0'>
      <tr> 
        <td  align="left" nowrap>
        	<b>Número do Empenho:</b>
        </td>
  	    <td  nowrap="nowrap"> 
          <input name="e60_codemp" size="10" type='text' readonly style="background-color:#DEB887;" value="<?=$_POST['e60_codemp']?>" >
        </td>
      </tr> 
      <tr> 
        <td align="left" nowrap> 
          <b>Seq. Empenho:</b>
        </td>
        <td align="left" nowrap>
        <input name="e60_numemp" size="10" type='text' readonly style="background-color:#DEB887;" value="<?=$_POST['e60_numemp']?>" >
        </td>
      </tr>

      <tr> 
        <td align="left" nowrap> 
          <b>Número da Licitação:</b>
        </td>
        <td align="left" nowrap>
        <input name="e60_numerol" size="10" maxlength="8" type='text' required value="<?=$numerol?>">
        </td>
      </tr>
      
      
      
    </table>
  </fieldset>
  <input type="submit" value="Alterar">  
  <input name="voltar" type="button" onclick='js_volta();'  value="Voltar">
</form>
</div>
</center>
<? 
db_menu(db_getsession("DB_id_usuario"),db_getsession("DB_modulo"),db_getsession("DB_anousu"),db_getsession("DB_instit"));
?>
<script>
  function js_volta(){
   location.href='emp1_alteranumerol.php'; 
}

//--------------------------------
function js_pesquisae60_codemp(mostra){
  if(mostra==true){
    js_OpenJanelaIframe('top.corpo','db_iframe_empempenho','func_empempenho.php?funcao_js=parent.js_mostraempempenho2|e60_codemp|e60_anousu','Pesquisa',true);
  }else{
   // js_OpenJanelaIframe('top.corpo','db_iframe_empempenho02','func_empempenho.php?pesquisa_chave='+document.form1.e60_numemp.value+'&funcao_js=parent.js_mostraempempenho','Pesquisa',false);
  }
}
function js_mostraempempenho2(chave1, chave2){
  document.form1.e60_codemp.value = chave1 + '/' + chave2;
  db_iframe_empempenho.hide();
}

function js_pesquisa_cgm(mostra){
  if(mostra==true){
    js_OpenJanelaIframe('top.corpo','db_iframe_cgm','func_cgm_empenho.php?funcao_js=parent.js_mostracgm1|e60_numcgm|z01_nome','Pesquisa',true);
  }else{
     if(document.form1.z01_numcgm.value != ''){ 
        js_OpenJanelaIframe('top.corpo','db_iframe_cgm','func_cgm_empenho.php?pesquisa_chave='+document.form1.z01_numcgm.value+'&funcao_js=parent.js_mostracgm','Pesquisa',false);
     }else{
       document.form1.z01_nome2.value = ''; 
     }
  }
}
function js_mostracgm(chave,erro){
  document.form1.z01_nome2.value = chave; 
  if(erro==true){ 
    document.form1.z01_nome2.value = ''; 
    document.form1.z01_numcgm.focus(); 
  }
}
function js_mostracgm1(chave1,chave2){
   document.form1.z01_numcgm.value = chave1;  
   document.form1.z01_nome2.value = chave2;
   db_iframe_cgm.hide();
}
//--------------------------------
function js_pesquisa_pcmater(mostra){
  if(mostra==true){
    js_OpenJanelaIframe('top.corpo','db_iframe_pcmater','func_pcmater.php?funcao_js=parent.js_mostrapcmater1|pc01_codmater|pc01_descrmater','Pesquisa',true);
  }else{
     if(document.form1.pc01_codmater.value != ''){ 
        js_OpenJanelaIframe('top.corpo','db_iframe_pcmater','func_pcmater.php?pesquisa_chave='+document.form1.pc01_codmater.value+'&funcao_js=parent.js_mostrapcmater','Pesquisa',false);
     }else{
       document.form1.pc01_descrmater.value = ''; 
     }
  }
}
function js_mostrapcmater(chave,erro){
  document.form1.pc01_descrmater.value = chave; 
  if(erro==true){ 
    document.form1.pc01_codmater.focus(); 
    document.form1.pc01_descrmater.value = ''; 
  }
}
function js_mostrapcmater1(chave1,chave2){
   document.form1.pc01_codmater.value = chave1;  
   document.form1.pc01_descrmater.value = chave2;
   db_iframe_pcmater.hide();
}
//--------------------------------
function js_pesquisa_dotacao(mostra){
  if(mostra==true){
    js_OpenJanelaIframe('top.corpo','db_iframe_orcdotacao','func_orcdotacao.php?funcao_js=parent.js_mostradotacao1|o58_coddot|o56_descr','Pesquisa',true);
  }else{
     if(document.form1.o58_coddot.value != ''){ 
        js_OpenJanelaIframe('top.corpo','db_iframe_orcdotacao','func_orcdotacao.php?pesquisa_chave='+document.form1.o58_coddot.value+'&funcao_js=parent.js_mostradotacao','Pesquisa',false);
     }else{
       document.form1.o40_descr.value = ''; 
     }
  }
}
function js_mostradotacao(chave,erro){
  document.form1.o40_descr.value = chave; 
  if(erro==true){ 
    document.form1.o58_coddot.focus(); 
    document.form1.o58_coddot.value = ''; 
  }
}
function js_mostradotacao1(chave1,chave2){
  document.form1.o58_coddot.value = chave1;  
  document.form1.o40_descr.value = chave2;
  db_iframe_orcdotacao.hide();
}
//--------------------------------
function js_pesquisa_empenho(mostra){
  if(mostra==true){
    js_OpenJanelaIframe('top.corpo','db_iframe_empempenho','func_empempenho.php?funcao_js=parent.js_mostraempenho1|e60_numemp','Pesquisa',true);
  }else{
     if(document.form1.e60_numemp.value != ''){ 
        js_OpenJanelaIframe('top.corpo','db_iframe_empempenho','func_empempenho.php?pesquisa_chave='+document.form1.e60_numemp.value+'&funcao_js=parent.js_mostraempenho','Pesquisa',false);
     }else{
       document.form1.z01_nome1.value = ''; 
     }
  }
}
function js_mostraempenho(erro,chave){
  document.form1.z01_nome1.value = chave; 
  if(erro==true){ 
    document.form1.e60_numemp.focus(); 
    document.form1.z01_nome1.value = ''; 
  }
}
function js_mostraempenho1(chave1){
  document.form1.e60_numemp.value = chave1;
  // document.form1.z01_nome1.value = chave2;
  db_iframe_empempenho.hide();
}


//--------------------------------


function js_buscae53_codord(mostra){
  if(mostra==true){
    js_OpenJanelaIframe('top.corpo','db_iframe_pagordemele','func_pagordemele.php?funcao_js=parent.js_mostracodord1|e53_codord','Pesquisa',true);
  }else{
     if(document.form1.e53_codord.value != ''){ 
        js_OpenJanelaIframe('top.corpo','db_iframe_pagordemele','func_pagordemele.php?pesquisa_chave='+document.form1.e53_codord.value+'&funcao_js=parent.js_mostracodord','Pesquisa',false);
     }else{
       document.form1.e53_codord.value = ''; 
     }
  }
}

function js_mostracodord(chave,erro){
  if(erro==true){ 
    document.form1.e53_codord.value = ''; 
    document.form1.e53_codord.focus(); 
  }
}

function js_mostracodord1(chave1){
   document.form1.e53_codord.value = chave1;  
   //document.form1.z01_nome2.value = chave2;
   db_iframe_pagordemele.hide();
}

</script>
</body>
</html>
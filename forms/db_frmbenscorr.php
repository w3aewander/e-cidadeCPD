<?
/*
 *     E-cidade Software Publico para Gestao Municipal                
 *  Copyright (C) 2009  DBSeller Servicos de Informatica             
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

//MODULO: patrim
include(modification("dbforms/db_classesgenericas.php"));
$cliframe_alterar_excluir = new cl_iframe_alterar_excluir;
$clbenscorr->rotulo->label();
$clrotulo = new rotulocampo;
$clrotulo->label("t52_descr");

if(isset($db_opcaoal)){
  $db_opcao=33;
  $db_botao=false;
}else if(isset($opcao) && $opcao=="alterar"){
  $db_botao=true;
  $db_opcao = 2;
}else if(isset($opcao) && $opcao=="excluir"){
  $db_opcao = 3;
  $db_botao=true;
}else{  
  $db_opcao = 1;
  $db_botao=true;
}
if(isset($novo) || isset($alterar) ||   isset($excluir) || (isset($incluir) && $sqlerro==false ) ){
  $t63_codbem = "";
  $t63_valcor = "";
  $t63_deprec = "";
  $t52_descr = "";
}
/*
if(isset($opcao)){
  $result = $clbenscorr->sql_record($clbenscorr->sql_query($t63_codcor,$t63_codbem));
//  db_criatabela($result);
//  die($clbenscorr->sql_query($t63_codcor,$t63_codbem));
  if($result!=false && $clbenscorr->numrows>0){
    db_fieldsmemory($result,0);
  }
}*/
 
if(empty($t63_codcor)){
  $t63_codcor = $_GET["t63_codcor"];
}
?>
<form name="form1" method="post" action="">
<center>
<table border="0">
  <tr>
    <td nowrap title="<?=@$Tt63_codcor?>">
       <?=@$Lt63_codcor?>
    </td>
    <td> 
      <?
        //db_input('t63_codcor',8,$It63_codcor,true,'text',3,"")
      ?>
      
      <input title="Sequencial do lançamento de correção

Campo:t63_codcor                              " name="t63_codcor" type="text" id="t63_codcor" value="<?=$t63_codcor;?>" size="8" maxlength="10" readonly="" style="background-color:#DEB887;" autocomplete="off">

    </td>
  </tr>
  <tr>
    <td nowrap title="<?=@$Tt63_codbem?>">
       <?
       //db_ancora(@$Lt63_codbem,"js_pesquisat63_codbem(true);",$db_opcao);
       db_ancora("Placa do bem: ","js_pesquisat63_codbem(true);",$db_opcao);
       ?>
    </td>
    <td> 
<?
//db_input('t63_codbem',8,$It63_codbem,true,'text',$db_opcao," onchange='js_pesquisat63_codbem(false);'")
db_input('t52_ident',8,$It63_codbem,true,'text',$db_opcao," onchange='js_pesquisat63_codbem(false);'")
?>
<?
db_input('t52_descr',40,$It52_descr,true,'text',3,"")
?>
    </td>
  </tr>
  <input type="hidden" name="t63_codbem" name="codbem" id="codbem">
  <tr>
    <input type="hidden" name="t63_agregarvalor" value="0">
    <td nowrap="" title="Agregar Valor"><strong>Agregar Valor:</strong></td>
    <td><input type="text" name="t63_agregarvalor" id="t63_agregarvalor" onchange="recebeagregar();" oninput="js_ValidaCampos(this, 4, 'Agregar Valor', 'f', 'f', event);" value="0">


      <b>Exercício:</b>
      <input required type="text" name="t63_exercicio" id="t63_exercicio" size="5" maxlength="4" oninput="js_ValidaCampos(this, 4, 'Exercício', 'f', 'f', event);" >

      

      <b>Total Valor Exercício:</b>
      <input type="text" name="t63_totalexercicio" id="t63_totalexercicio" readonly style="background-color: rgb(222, 184, 135);">
    </td>
  </tr>


  <script>
    var campo = document.getElementById("t63_exercicio");
    campo.addEventListener("focusout", function(){
      var valor = campo.value;
      var xhr = new XMLHttpRequest();
      xhr.onreadystatechange = function(){         
        if((xhr.readyState === 4) && (xhr.status === 200)){          
          document.getElementById("t63_totalexercicio").value = xhr.responseText;
        }
      }                  
    xhr.open("GET", "forms/buscatotal.php?ano="+valor, true);
    xhr.send(null);
 });




  </script>

  <tr>
    <input type="hidden" name="t63_corrigirvalor" value="0">
    <td nowrap title="Estornar Valor"><strong>Estornar Valor:</strong></td>
    <td><input type="text" name="t63_corrigirvalor" id="t63_corrigirvalor" onchange="recebecorrigir();" oninput="js_ValidaCampos(this, 4, 'Estornar Valor', 'f', 'f', event);" value="0"></td>
  </tr>

  <tr>
    <td nowrap title="Processo Adm."><strong>Processo Adm:</strong></td>
    <td><input type="text" name="t63_processoadm" id="t63_processoadm" maxlength="10" required></td>
  </tr>

<tr>
  <td colspan="2">
      <fieldset>
        <legend><b>Justificativa</b></legend> 
        <textarea id="t63_justificativa" name="t63_justificativa" rows="5" cols="80" maxlength="500" required></textarea>
      </fieldset>
    </td>
</tr>

<input type="hidden" name="t63_valcor" value="0">
<input type="hidden" name="t63_deprec" value="0">
</tr>
  <?php /* ?>
  <tr>
    <td nowrap title="<?=@$Tt63_valcor?>">
       <?=@$Lt63_valcor?>
    </td>
    <td> 
<?
db_input('t63_valcor',15,$It63_valcor,true,'text',$db_opcao,"")
?>
    </td>
  </tr>
  <tr>
    <td nowrap title="<?=@$Tt63_deprec?>">
       <?=@$Lt63_deprec?>
    </td>
    <td> 
<?
db_input('t63_deprec',15,$It63_deprec,true,'text',$db_opcao,"")
?>
    </td>
  </tr>
  <?php */ ?>
  </tr>
    <td colspan="2" align="center">
 <input name="<?=($db_opcao==1?"incluir":($db_opcao==2||$db_opcao==22?"alterar":"excluir"))?>" type="submit" id="db_opcao" value="<?=($db_opcao==1?"Incluir":($db_opcao==2||$db_opcao==22?"Alterar":"Excluir"))?>" <?=($db_botao==false?"disabled":"")?>  >
 <input name="novo" type="button" id="cancelar" value="Novo" onclick="js_cancelar();" <?=($db_opcao==1||isset($db_opcaoal)?"style='visibility:hidden;'":"")?> >
    </td>
  </tr>
  </table>
  <?php 
  function confereProcessados($codigo){
    $sql = pg_query("SELECT * FROM benscorr WHERE t63_codcor = {$codigo}");
    $resultado = pg_fetch_all($sql);
    $processo = 0;
    foreach ($resultado as $registro){
      if(empty($registro["t63_dataprocessamento"])){
        return $processo = 1;
      }
    }
  }
  $conferencia = confereProcessados($t63_codcor);
   ?>

<?php if($conferencia == 1) : ?>
  <h1>Há itens que ainda não foram processados.</h1>
<?php endif; ?>
 <table>
  <tr>
    <td valign="top"  align="center">  
    <?
	 $chavepri= array("t63_codcor"=>@$t63_codcor,"t63_codbem"=>null);
	 $cliframe_alterar_excluir->chavepri=$chavepri;
	 $cliframe_alterar_excluir->sql     = $clbenscorr->sql_query_file($t63_codcor,null);
	 //$cliframe_alterar_excluir->campos  ="t63_codcor,t63_codbem,t63_valcor,t63_deprec";
   $cliframe_alterar_excluir->campos  ="t63_codcor,t63_codbem,t63_agregarvalor,t63_corrigirvalor,t63_processoadm,t63_justificativa,t63_exercicio,t63_dataprocessamento";
   $cliframe_alterar_excluir->sql_disabled = "SELECT t63_codcor,t63_codbem,t63_agregarvalor,t63_corrigirvalor,t63_processoadm,t63_justificativa,t63_exercicio,t63_dataprocessamento FROM benscorr WHERE t63_codcor = {$t63_codcor} AND t63_dataprocessamento IS NOT NULL";
	 $cliframe_alterar_excluir->legenda="ITENS LANÇADAS";
	 $cliframe_alterar_excluir->iframe_height ="160";
	 $cliframe_alterar_excluir->iframe_width ="900";
	 $cliframe_alterar_excluir->iframe_alterar_excluir($db_opcao);
    ?>
    </td>
   </tr>
 </table>
  </center>
</form>

<form method="post" action="">
  <?php db_input('t63_codcor',8,$It63_codcor,true,'hidden',3,"") ?>
  
   <input type="hidden" name="idusuario" value="<?=$_SESSION['DB_id_usuario']?>">

<?php $libera = libera($t63_codcor); ?>

<?php if(!$libera) : ?>
  <tr>
     <td colspan="2" align="center">
       <input name="processar" type="submit" id="db_opcao" value="Processar">
     </td>
   </tr>
<?php endif; ?>
</form>

<script>
  function recebeagregar(){
  var valor = document.getElementById("t63_agregarvalor");
  if (valor.value != "" || valor.value.length > 0) {
      document.getElementById("t63_corrigirvalor").disabled = true;      
   } else {
      document.getElementById("t63_corrigirvalor").disabled = false;
   }
}

function recebecorrigir(){
  var valor = document.getElementById("t63_corrigirvalor");
  var codigobem = document.getElementById("t63_codbem").value;
  
  var xhr = new XMLHttpRequest();
  xhr.onreadystatechange = function(){
    if((xhr.readyState === 4) && (xhr.status === 200)){          
      //document.getElementById("t63_totalexercicio").value = xhr.responseText;
      console.log(xhr.responseText);
      if(xhr.responseText == 3){
        alert("NÃO É POSSÍVEL ESTORNAR REGISTRO NÃO AGREGADO.");
        valor.value = '';
        valor.disabled = true;
        document.getElementById("t63_agregarvalor").disabled = false;
      }
    }
  }                  
  xhr.open("GET", "regraestorno.php?codbem="+codigobem, true);
  xhr.send(null);


  if (valor.value != "" || valor.value.length > 0) {
      document.getElementById("t63_agregarvalor").disabled = true;
   } else {
      document.getElementById("t63_agregarvalor").disabled = false;
   }
}

function js_cancelar(){
  var opcao = document.createElement("input");
  opcao.setAttribute("type","hidden");
  opcao.setAttribute("name","novo");
  opcao.setAttribute("value","true");
  document.form1.appendChild(opcao);
  document.form1.submit();
}
function js_pesquisat63_codbem(mostra){
  if(mostra==true){
    //js_OpenJanelaIframe('CurrentWindow.corpo.iframe_benscorr','db_iframe_bens','func_bens.php?funcao_js=parent.js_mostrabens1|t52_bem|t52_descr','Pesquisa',true);
    js_OpenJanelaIframe('top.corpo.iframe_benscorr','db_iframe_bens','func_bens.php?funcao_js=parent.js_mostrabens1|t52_ident|t52_descr|t52_bem','Pesquisa',true);
  }else{
     if(document.form1.t63_codbem.value != ''){ 
        //js_OpenJanelaIframe('CurrentWindow.corpo.iframe_benscorr','db_iframe_bens','func_bens.php?pesquisa_chave='+document.form1.t63_codbem.value+'&funcao_js=parent.js_mostrabens','Pesquisa',false);
      js_OpenJanelaIframe('top.corpo.iframe_benscorr','db_iframe_bens','func_bens.php?pesquisa_chave='+document.form1.t52_ident.value+'&funcao_js=parent.js_mostrabens','Pesquisa',false);
     }else{
       document.form1.t52_descr.value = '';
     } 
  }
}
function js_mostrabens(chave, chave2, erro){    
  document.form1.t52_descr.value = chave;
  document.form1.t63_codbem.value = chave2;
  if(erro==true){ 
    document.form1.t63_codbem.focus(); 
    document.form1.t63_codbem.value = ''; 
  }
}
function js_mostrabens1(chave1,chave2, chave3){  
  document.form1.t63_codbem.value = chave3;
  document.form1.t52_descr.value = chave2;
  document.form1.t52_ident.value = chave1;
  db_iframe_bens.hide();
}

window.addEventListener("load", function(event) {
  setTimeout(function(){ console.log("..."); }, 3000);

    var iframe = document.getElementById("ativ");
    var element = iframe.contentWindow.document.getElementsByClassName('cabec');
    
    
    element[3].textContent = "Valor Agregado";
    element[3].style.fontWeight = "bold";

    element[4].textContent = "Valor Estornado";
    element[4].style.fontWeight = "bold";

    element[5].textContent = "Processo Adm.";
    element[5].style.fontWeight = "bold";

    element[6].textContent = "Justificativa";
    element[6].style.fontWeight = "bold";

    element[7].textContent = "Exercício";
    element[7].style.fontWeight = "bold";

    element[8].textContent = "Processamento";
    element[8].style.fontWeight = "bold";

    

    var linhas = iframe.contentWindow.document.getElementsByTagName("table")[0].rows;
    

});
</script>
<?php


require_once("libs/db_stdlib.php");
require_once("libs/db_conecta.php");
require_once("libs/db_sessoes.php");
require_once("libs/db_usuariosonline.php");
require_once("dbforms/db_funcoes.php");
require_once("dbforms/db_classesgenericas.php");
db_postmemory($HTTP_POST_VARS);


$contasmsc = db_query("SELECT * FROM receitasmsc ORDER BY id");
$resultado = pg_fetch_all($contasmsc);


if(!empty($_POST)){
  if(empty($_POST["receitasmsc"]) || empty($_POST["c60_codcon"])){
    echo "<script>alert('Preencha os campos antes de salvar.');</script>";    
  } else {
    $contamsc = $_POST["receitasmsc"];
    $codigofonte = (int)$_POST["c60_codcon"];  
    $consultaestrutural = db_query("SELECT DISTINCT c60_estrut FROM conplanoorcamento WHERE c60_codcon = {$codigofonte}");
    $estrutural = pg_fetch_result($consultaestrutural, 0);
    $instituicao = (int)$_POST["instituicao"];
    
    if(db_query('INSERT INTO receitasvinculadas(contamsc, codigofonte, estrutural, instituicao) VALUES('.$contamsc.', '.$codigofonte.', '.$estrutural.', '.$instituicao.')')){
      echo "<script>alert('Vinculação feita com sucesso.');</script>";
    } else {
      echo "<script>alert('Vinculação não foi feita. Contate o suporte');</script>";
    }    
  }
}


?>
<html>
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
  <meta http-equiv="Expires" CONTENT="0">
  <script language="JavaScript" type="text/javascript" src="scripts/scripts.js"></script>
  <link href="estilos.css" rel="stylesheet" type="text/css"></head>
<body bgcolor=#CCCCCC leftmargin="0" topmargin="0" marginwidth="0" marginheight="0">
<table width="790" border="0" cellpadding="0" cellspacing="0" bgcolor="#5786B2">
  <tr> 
    <td width="360" height="18">&nbsp;</td>
    <td width="263">&nbsp;</td>
    <td width="25">&nbsp;</td>
    <td width="140">&nbsp;</td>
  </tr>
</table>
<table border="0" align="center" cellspacing="0" cellpadding="0" >
  <tr>
    <td>&nbsp;</td>
  </tr>
  <tr> 
    <td valign="top" bgcolor="#CCCCCC"> 
    <center>
      <form name="form1" method="post" action="">
<fieldset>
<legend><b>Vincular Receitas</b></legend>
<table border="0">
  <tr>
    <td nowrap title="Receita MSC">
       Receita MSC
    </td>
    <td> 
      <?php foreach ($resultado as $linha) : ?>
        <div style="display:none" id="<?=$linha['nr']?>"><?=$linha['especificacao'];?></div>
      <?php endforeach;  ?>
      <input required size ="10" list="contas" name="receitasmsc" id="receitasmsc" onchange="js_verifica();">
      <datalist id="contas">
<?php foreach ($resultado as $linha) :  ?>
        <option value="<?=$linha['nr'];?>">
<?php endforeach; ?>
      </datalist>

      
      <input title="" name="descmsc" type="text" id="descmsc" value="" size="41" maxlength="" readonly="" style="background-color:#DEB887;" autocomplete="off">
    </td>
  </tr>



<tr>
  <td>
    <? db_ancora ("Código Fonte", "pesquisaconta(true)", 1 )?>
  </td>
  <td colspan="2">
  <?php              
    db_input ( "c60_codcon", 10, $Ic60_codcon, true, "text", 1, " onchange='pesquisaconta(false);' " );
    db_input ( "c60_descr", 41, $Ic60_descr, true, "text", 3 );    
  ?>  
  </td>
</tr>


  
  
  </table>
</fieldset>
<table>
  <tr>
    <td colspan="2">&nbsp;</td>
  </tr>
  <tr>    
    <td>
      <input name="salvar" type="submit" id="salvar" value="Salvar">
    </td>
    <td>
      <input onclick="js_vinculos();" name="visualizar" type="button" id="visualizar" value="Visualizar Vínculos">
    </td>
  </tr>
</table>
<input type="hidden" name="instituicao" value="<?=db_getsession("DB_instit");?>">
</form>
    </center>
  </td>
  </tr>
</table>
<? db_menu(db_getsession("DB_id_usuario"),db_getsession("DB_modulo"),db_getsession("DB_anousu"),db_getsession("DB_instit")); ?>



<script>
  document.getElementById("c60_codcon").value = "";
  document.getElementById("c60_descr").value = "";

  function js_vinculos(){
    js_OpenJanelaIframe('',
                        'db_iframe_db_vinculos',
                        'func_db_vinculos.php',
                        'Pesquisa Vínculos',
                        true, 20,200,800,500);
  }

  function js_verifica(){
    var nr = document.getElementById("receitasmsc").value;
    var desc = document.getElementById(nr).innerHTML;
    document.getElementById("descmsc").value = desc;
    document.getElementById("descmsc").title = desc;
  }

function pesquisaconta(lMostra){
 if (lMostra) {
    js_OpenJanelaIframe('',
                        'db_iframe_db_usuarios',
                        'func_db_msc.php?funcao_js=parent.mostraconta|c60_descr|c60_codcon',
                        'Pesquisa Código Fonte',
                        true);
 } else {
   if ( $F('c60_codcon') != '' ) {
      js_OpenJanelaIframe('',
                          'db_iframe_db_usuarios',
                          'func_db_msc.php?pesquisa_chave='+$F('c60_codcon')+
                          '&funcao_js=parent.digitaconta',
                          'Pesquisa',
                          false);
   } else {
     $('c60_codcon').value = '';
     $('c60_descr').value       = '';
   }
 }
}

function digitaconta(sc60_descr,lErro, chave){
  if ( arguments[1] === false ){
    $('c60_codcon').value = '';
  }
  $('c60_descr').value       = arguments[0];
  $('c60_codcon').value = arguments[2];
  


}

function mostraconta(sc60_descr, ic60_codcon){
  $('c60_codcon').value = arguments[1];
  $('c60_descr').value = arguments[0];
  db_iframe_db_usuarios.hide();
}





</script>


</body>
</html>
<?php
//Novo

require_once("libs/db_stdlib.php");
require_once("libs/db_conecta.php");
require_once("libs/db_sessoes.php");
require_once("libs/db_usuariosonline.php");
require_once("dbforms/db_funcoes.php");
require_once("dbforms/db_classesgenericas.php");
db_postmemory($HTTP_POST_VARS);


$receitasmsc = db_query("SELECT * FROM contasmsc ORDER BY id");
$resultado = pg_fetch_all($receitasmsc);



if(!empty($_POST)){
  
  if(empty($_POST["receitasmsc"]) || empty($_POST["c61_reduz"])){
    echo "<script>alert('Preencha os campos antes de salvar.');</script>";    
  } else {
    $contamsc = $_POST["descmsc"];
    $reduzido = (int)$_POST["c61_reduz"];
    $estrutural = $_POST["c60_estrut"];
    $natureza = (int)$_POST["natureza"];
    $instituicao = (int)$_POST["instituicao"];
        

    if(db_query('INSERT INTO contasvinculadas(contamsc, reduzido, estrutural, natureza, instituicao) VALUES('.$contamsc.', '.$reduzido.', '.$estrutural.', '.$natureza.', '.$instituicao.')')){
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
<legend><b>Vincular Contas</b></legend>
<table border="0">
  <tr>
    <td nowrap title="Conta MSC">
       <b>Conta MSC</b>
    </td>
    <td> 
      <?php foreach ($resultado as $linha) : ?>
        <div style="display:none" id="<?=$linha['conta']?>"><?=$linha['conta'];?></div>
      <?php endforeach;  ?>
      <input required size ="10" autocomplete="off" list="contasmsc" name="receitasmsc" id="receitasmsc" onchange="js_verifica();">
      <datalist id="contasmsc">
<?php foreach ($resultado as $linha) :  ?>
        <option value="<?=$linha['conta'];?>">
<?php endforeach; ?>
      </datalist>

      
      <input title="" name="descmsc" type="text" id="descmsc" value="" size="41" maxlength="" readonly="" style="background-color:#DEB887;" autocomplete="">
    </td>
  </tr>



<tr>
  <td>
    <? db_ancora ("Código PCASP", "js_pesquisacontapcasp(true)", 1 )?>
  </td>
  <td colspan="2">
  <?php              
    db_input ( "c61_reduz", 10, $Ic61_reduz, true, "text", 1, " onchange='js_pesquisacontapcasp(false);' " );
    db_input ( "c60_estrut", 41, $Ic60_descr, true, "text", 3 );    
  ?>  
  </td>
</tr>

<tr>
  <td><b>Natureza do Saldo</b></td>
  <td>
    <select name="natureza" id="natureza">
      <option value="2">Crédito</option>
      <option value="1">Débito</option>
      <option value="3">Mista</option>
    </select>
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
  document.getElementById("c61_reduz").value = "";
  document.getElementById("c60_estrut").value = "";

  function js_vinculos(){
    js_OpenJanelaIframe('',
                        'db_iframe_db_vinculospcasp',
                        'func_db_vinculospcasp.php',
                        'Pesquisa Vínculos',
                        true, 20,200,800,500);
  }

  function js_verifica(){
    var nr = document.getElementById("receitasmsc").value;
    var desc = document.getElementById(nr).innerHTML;
    document.getElementById("descmsc").value = desc;
    document.getElementById("descmsc").title = desc;
  }

function js_pesquisacontapcasp(lMostra){
 if (lMostra) {
    js_OpenJanelaIframe('',
                        'db_iframe_db_contaspcasp',
                        'func_db_mscpcasp.php?funcao_js=parent.js_mostraconta|c60_estrut|c61_reduz',
                        'Pesquisa Código Fonte',
                        true);
 } else {
   if ( $F('c61_reduz') != '' ) {
      js_OpenJanelaIframe('',
                          'db_iframe_db_contaspcasp',
                          'func_db_mscpcasp.php?pesquisa_chave='+$F('c61_reduz')+
                          '&funcao_js=parent.js_digitaconta',
                          'Pesquisa',
                          false);
   } else {
     $('c61_reduz').value = '';
     $('c60_estrut').value = '';
   }
 }
}

function js_digitaconta(sc60_estrut,lErro, chave){
  if ( arguments[1] === false ){
    $('c61_reduz').value = '';
  }
  $('c60_estrut').value       = arguments[0];
  $('c61_reduz').value = arguments[2];
  


}

function js_mostraconta(sc60_estrut, ic60_codcon){
  $('c61_reduz').value = arguments[1];
  $('c60_estrut').value = arguments[0];
  db_iframe_db_contaspcasp.hide();
}





</script>


</body>
</html>
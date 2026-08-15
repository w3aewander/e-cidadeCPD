<?php


require_once(modification("libs/db_stdlib.php"));
require_once(modification("libs/db_conecta.php"));
require_once(modification("libs/db_sessoes.php"));
require_once(modification("libs/db_usuariosonline.php"));
require_once(modification("dbforms/db_funcoes.php"));


db_postmemory($HTTP_POST_VARS);
parse_str($HTTP_SERVER_VARS["QUERY_STRING"]);

$sGnome = "t";


?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link href="estilos.css" rel="stylesheet" type="text/css">
<script language="JavaScript" type="text/javascript" src="scripts/scripts.js"></script>
</head>
<body bgcolor=#CCCCCC leftmargin="0" topmargin="0" marginwidth="0" marginheight="0">
<table height="100%" border="0"  align="center" cellspacing="0" bgcolor="#CCCCCC">
  <tr> 
    <td height="63" align="center" valign="top">
        <table width="35%" border="0" align="center" cellspacing="0">
	     <form name="form2" method="post" action="" >

          <tr> 
            <td width="4%" align="left" nowrap title="<?=$Tid_usuario?>">
              <b>Código Fonte</b>
            </td>
            <td width="96%" align="left" nowrap> 
              <?php db_input("c60_codcon",10,$Ic60_codcon,true,"text",4,"","pCodigo"); ?>
            </td>
          </tr>

          <tr> 
            <td width="4%" align="left" nowrap title="<?=$Tnome?>">
              <b>Estrutural</b>
            </td>
            <td width="96%" align="left" nowrap> 
              <?php db_input("c60_estrut",60,$Ic60_estrut,true,"text",4,"","pEstrutural"); ?>
            </td>
          </tr>

          <tr> 
            <td colspan="2" align="center"> 
              <input name="pesquisar" type="submit" id="pesquisar2" value="Pesquisar"> 
              <input name="limpar" type="reset" id="limpar" value="Limpar" >
              <input name="Fechar" type="button" id="fechar" value="Fechar" onClick="parent.db_iframe_db_usuarios.hide();">
             </td>
          </tr>

        </form>
        </table>
      </td>
  </tr>
  <tr> 
    <td align="center" valign="top"> 
<?php
$ano = db_getsession("DB_anousu");
//Pesquisa pela âncora
if(!isset($pesquisa_chave)){

$sql = "select distinct c60_codcon, c60_estrut, c60_descr, c60_finali from conplanoorcamento inner join conclass on conclass.c51_codcla = conplanoorcamento.c60_codcla inner join consistema on consistema.c52_codsis = conplanoorcamento.c60_codsis left join conplanoorcamentoanalitica on conplanoorcamentoanalitica.c61_codcon = conplanoorcamento.c60_codcon and conplanoorcamentoanalitica.c61_anousu = c60_anousu where c60_anousu = {$ano} and c60_estrut like '4%' order by c60_estrut";



if(!empty($pEstrutural) && !empty($pCodigo)){
  if($pEstrutural[0] != 4){
    $pEstrutural = 1;
  }
  $sql = "select distinct c60_codcon, c60_estrut, c60_descr, c60_finali from conplanoorcamento inner join conclass on conclass.c51_codcla = conplanoorcamento.c60_codcla inner join consistema on consistema.c52_codsis = conplanoorcamento.c60_codsis left join conplanoorcamentoanalitica on conplanoorcamentoanalitica.c61_codcon = conplanoorcamento.c60_codcon and conplanoorcamentoanalitica.c61_anousu = c60_anousu where c60_codcon = {$pCodigo} c60_estrut = '{$pEstrutural}' and c60_anousu = {$ano} order by c60_estrut";
} elseif(!empty($pEstrutural) && empty($pCodigo)){
  if($pEstrutural[0] != 4){
    $pEstrutural = 1;
  }
  
  $sql = "select distinct c60_codcon, c60_estrut, c60_descr, c60_finali from conplanoorcamento inner join conclass on conclass.c51_codcla = conplanoorcamento.c60_codcla inner join consistema on consistema.c52_codsis = conplanoorcamento.c60_codsis left join conplanoorcamentoanalitica on conplanoorcamentoanalitica.c61_codcon = conplanoorcamento.c60_codcon and conplanoorcamentoanalitica.c61_anousu = c60_anousu where c60_estrut = '{$pEstrutural}' and c60_anousu = {$ano} order by c60_estrut";
  
} elseif(!empty($pCodigo) && empty($pEstrutural)){  
  $sql = "select distinct c60_codcon, c60_estrut, c60_descr, c60_finali from conplanoorcamento inner join conclass on conclass.c51_codcla = conplanoorcamento.c60_codcla inner join consistema on consistema.c52_codsis = conplanoorcamento.c60_codsis left join conplanoorcamentoanalitica on conplanoorcamentoanalitica.c61_codcon = conplanoorcamento.c60_codcon and conplanoorcamentoanalitica.c61_anousu = c60_anousu where c60_codcon = {$pCodigo} and c60_anousu = {$ano} and c60_estrut like '4%' order by c60_estrut";    
}


db_lovrot($sql,50,"()","",$funcao_js,"","NoMe");

//Pesquisa pela digitação
} else {
  if($pesquisa_chave!=null && $pesquisa_chave!=""){
    $sql = "select distinct c60_codcon, c60_estrut, c60_descr, c60_finali from conplanoorcamento inner join conclass on conclass.c51_codcla = conplanoorcamento.c60_codcla inner join consistema on consistema.c52_codsis = conplanoorcamento.c60_codsis left join conplanoorcamentoanalitica on conplanoorcamentoanalitica.c61_codcon = conplanoorcamento.c60_codcon and conplanoorcamentoanalitica.c61_anousu = c60_anousu where c60_anousu = {$ano} and c60_codcon = {$pesquisa_chave}  and c60_estrut like '4%' order by c60_estrut";

    $result = db_query($sql);
    $linhas = pg_num_rows($result);

    if($result && $linhas!=0){
		  db_fieldsmemory($result,0);
		  echo "<script>".$funcao_js."('$c60_descr',false, '$pesquisa_chave');</script>";
    } else {
      echo "<script>".$funcao_js."('Chave(".$pesquisa_chave.") não encontrada',false, '$pesquisa_chave');</script>";
    }
  }
}
?>
     </td>
   </tr>
</table>
</body>
</html>
<script type="text/javascript">
js_tabulacaoforms("form2","pCodigo",true,1,"pCodigo",true);
</script>

<script type="text/javascript">
(function() {
  var query = frameElement.getAttribute('name').replace('IF', ''), input = document.querySelector('input[value="Fechar"]');
  input.onclick = parent[query] ? parent[query].hide.bind(parent[query]) : input.onclick;
})();
</script>

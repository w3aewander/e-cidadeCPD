<?php

//Novo
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
    <td height="63" align="center" valign="top">
        <table width="35%" border="0" align="center" cellspacing="0">
          <tr> 
            <td width="4%" align="right" nowrap title="<?=$Tc60_codcon?>">
              <b>Código</b>
            </td>
            <td width="96%" align="left" nowrap> 
              <?
           db_input("c60_codcon",10,$Ic60_codcon,true,"text",4,"","pCodigo");
           ?>
            </td>
            <td width="4%" align="right" nowrap title="<?=$Tc60_estrut?>">
              <b>Estrutural</b>
            </td>
            <td width="96%" align="left" nowrap> 
              <?
           db_input("c60_estrut",15,$Ic60_estrut,true,"text",4,"","pEstrutural");
           ?>
            </td>
          </tr>
          <tr> 
            <td width="4%" align="right" nowrap title="<?=$Tc61_reduz?>">
              <b>Reduzido</b>
      </td>
            <td width="96%" align="left" nowrap> 
              <?
           db_input("c61_reduz",10,$Ic61_reduz,true,"text",4,"","pReduzido");
           ?>
            </td>
            <td width="4%" align="right" nowrap title="<?=$Tc60_descr?>">
              <b>Descrição da Conta</b>
            </td>
            <td width="96%" align="left" nowrap> 
              <?
           db_input("c60_descr",50,$Ic60_descr,true,"text",4,"","pDescricao");
           ?>
            </td>
          </tr>
          
          <tr> 
            <td colspan="4" align="center"> 
              <input name="pesquisar" type="submit" id="pesquisar2" value="Pesquisar"> 
              <input name="limpar" type="reset" id="limpar" value="Limpar" >
              <input name="Fechar" type="button" id="fechar" value="Fechar" onClick="parent.db_iframe_db_vinculos.hide();">
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
$instituicao = db_getsession("DB_instit");
//Pesquisa pela âncora
if(!isset($pesquisa_chave)){

$sql = "select conplano.c60_codcon,conplano.c60_estrut,conplanoreduz.c61_reduz,fc_nivel_plano2005(conplano.c60_estrut) as dl_Nivel,conplanoreduz.c61_instit,conplano.c60_descr,conplano.c60_finali,consistema.c52_descr,conclass.c51_descr,conplano.c60_codsis as DB_codsis,conplano.c60_codcla as DB_codcla from conplano inner join conclass on conclass.c51_codcla = conplano.c60_codcla inner join consistema on consistema.c52_codsis = conplano.c60_codsis left join conplanoreduz on conplanoreduz.c61_codcon = conplano.c60_codcon and c61_anousu = c60_anousu where c60_anousu={$ano} order by c60_estrut";




if(!empty($pEstrutural) && !empty($pCodigo)){
  //Todos preenchidos
  $sql = "select distinct c60_codcon, c60_estrut, c60_descr, c60_finali from conplanoorcamento inner join conclass on conclass.c51_codcla = conplanoorcamento.c60_codcla inner join consistema on consistema.c52_codsis = conplanoorcamento.c60_codsis left join conplanoorcamentoanalitica on conplanoorcamentoanalitica.c61_codcon = conplanoorcamento.c60_codcon and conplanoorcamentoanalitica.c61_anousu = c60_anousu where c60_codcon = {$pCodigo} c60_estrut = '{$pEstrutural}' and c60_anousu = {$ano} order by c60_estrut";

} elseif(!empty($pEstrutural) && empty($pCodigo) && empty($pReduzido) && empty($pDescricao)){
  //Somente estrutural
  $sql = "select conplano.c60_codcon,conplano.c60_estrut,conplanoreduz.c61_reduz,fc_nivel_plano2005(conplano.c60_estrut) as dl_Nivel,conplanoreduz.c61_instit,conplano.c60_descr,conplano.c60_finali,consistema.c52_descr,conclass.c51_descr,conplano.c60_codsis as DB_codsis,conplano.c60_codcla as DB_codcla from conplano inner join conclass on conclass.c51_codcla = conplano.c60_codcla inner join consistema on consistema.c52_codsis = conplano.c60_codsis left join conplanoreduz on conplanoreduz.c61_codcon = conplano.c60_codcon and c61_anousu = c60_anousu where c60_estrut = '{$pEstrutural}' and c60_anousu={$ano} order by c60_estrut";    

} elseif(empty($pEstrutural) && !empty($pCodigo) && empty($pReduzido) && empty($pDescricao)){
  //Somente código
  $sql = "select conplano.c60_codcon,conplano.c60_estrut,conplanoreduz.c61_reduz,fc_nivel_plano2005(conplano.c60_estrut) as dl_Nivel,conplanoreduz.c61_instit,conplano.c60_descr,conplano.c60_finali,consistema.c52_descr,conclass.c51_descr,conplano.c60_codsis as DB_codsis,conplano.c60_codcla as DB_codcla from conplano inner join conclass on conclass.c51_codcla = conplano.c60_codcla inner join consistema on consistema.c52_codsis = conplano.c60_codsis left join conplanoreduz on conplanoreduz.c61_codcon = conplano.c60_codcon and c61_anousu = c60_anousu where c60_anousu={$ano} and c60_codcon = {$pCodigo} order by c60_estrut";
} elseif(empty($pEstrutural) && empty($pCodigo) && !empty($pReduzido) && empty($pDescricao)){
  $sql = "select conplano.c60_codcon,conplano.c60_estrut,conplanoreduz.c61_reduz,fc_nivel_plano2005(conplano.c60_estrut) as dl_Nivel,conplanoreduz.c61_instit,conplano.c60_descr,conplano.c60_finali,consistema.c52_descr,conclass.c51_descr,conplano.c60_codsis as DB_codsis,conplano.c60_codcla as DB_codcla from conplano inner join conclass on conclass.c51_codcla = conplano.c60_codcla inner join consistema on consistema.c52_codsis = conplano.c60_codsis left join conplanoreduz on conplanoreduz.c61_codcon = conplano.c60_codcon and c61_anousu = c60_anousu where c60_anousu={$ano} and c61_reduz = {$pReduzido} order by c60_estrut";
} elseif(empty($pEstrutural) && empty($pCodigo) && empty($pReduzido) && !empty($pDescricao)){
  //Somente descrição
  $descricao = strtoupper($pDescricao);
  $sql = "select conplano.c60_codcon,conplano.c60_estrut,conplanoreduz.c61_reduz,fc_nivel_plano2005(conplano.c60_estrut) as dl_Nivel,conplanoreduz.c61_instit,conplano.c60_descr,conplano.c60_finali,consistema.c52_descr,conclass.c51_descr,conplano.c60_codsis as DB_codsis,conplano.c60_codcla as DB_codcla from conplano inner join conclass on conclass.c51_codcla = conplano.c60_codcla inner join consistema on consistema.c52_codsis = conplano.c60_codsis left join conplanoreduz on conplanoreduz.c61_codcon = conplano.c60_codcon and c61_anousu = c60_anousu where c60_anousu={$ano} and c60_descr like '%{$descricao}%' order by c60_estrut";
} 


db_lovrot($sql,50,"()","",$funcao_js,"","NoMe");

//Pesquisa pela digitação
} else {
  if($pesquisa_chave!=null && $pesquisa_chave!=""){

    $sql = "select conplano.c60_codcon,conplano.c60_estrut,conplanoreduz.c61_reduz,fc_nivel_plano2005(conplano.c60_estrut) as dl_Nivel,conplanoreduz.c61_instit,conplano.c60_descr,conplano.c60_finali,consistema.c52_descr,conclass.c51_descr,conplano.c60_codsis as DB_codsis,conplano.c60_codcla as DB_codcla from conplano inner join conclass on conclass.c51_codcla = conplano.c60_codcla inner join consistema on consistema.c52_codsis = conplano.c60_codsis left join conplanoreduz on conplanoreduz.c61_codcon = conplano.c60_codcon and c61_anousu = c60_anousu where c61_reduz = {$pesquisa_chave} and c60_anousu={$ano} order by c60_estrut";

    $result = db_query($sql);
    $linhas = pg_num_rows($result);

    if($result && $linhas!=0){
		  db_fieldsmemory($result,0);
		  echo "<script>".$funcao_js."('$c60_estrut',false, '$pesquisa_chave');</script>";
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

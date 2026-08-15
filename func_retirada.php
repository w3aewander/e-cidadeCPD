<?

require("libs/db_stdlib.php");
require("libs/db_conecta.php");
include("libs/db_sessoes.php");
include("libs/db_usuariosonline.php");
include("dbforms/db_funcoes.php");
include("classes/db_orcdotacao_classe.php");
require("libs/db_liborcamento.php");
include("classes/db_orcparametro_classe.php");
db_postmemory($HTTP_POST_VARS);
parse_str($HTTP_SERVER_VARS["QUERY_STRING"]);
$clorcdotacao = new cl_orcdotacao;
$clorcdotacao->rotulo->label("o58_anousu");
$clorcdotacao->rotulo->label("o58_coddot");
$clorcdotacao->rotulo->label("o58_orgao");
$clestrutura = new cl_estrutura;
$clorcparametro = new cl_orcparametro;
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
    <td align="center" valign="top"> 
<?



if(!isset($pesquisa_chave)){
  
  
    if(db_getsession("DB_instit") == 1){
      $sql = "SELECT certificadoretirada.id, certificadoretirada.e50_codord, e60_numemp, noempenho, e50_dataquebraordem, e60_instit FROM certificadoretirada INNER JOIN certificacaoconformidade on idtabelacertliq = certificacaoconformidade.id ORDER BY numeroretirada, certificadoretirada.id, e60_instit";
    } else {
      $sql = "SELECT certificadoretirada.id, certificadoretirada.e50_codord, e60_numemp, noempenho, e50_dataquebraordem, e60_instit FROM certificadoretirada INNER JOIN certificacaoconformidade on idtabelacertliq = certificacaoconformidade.id WHERE e60_instit = '".db_getsession("DB_instit")."' ORDER BY numeroretirada, certificadoretirada.id, e60_instit";
    }
    
    
    db_lovrot($sql,15,"()","",$funcao_js);
  
  
  
}else{

  if($pesquisa_chave!=null && $pesquisa_chave!=""){
      // Dim result as RecordSet
      $result = $clorcdotacao->sql_record($clorcdotacao->sql_query(db_getsession("DB_anousu"),$pesquisa_chave));
      if($clorcdotacao->numrows!=0){
         db_fieldsmemory($result,0);
         echo "<script>".$funcao_js."('$o56_descr',false);</script>";
      }else{
         echo "<script>".$funcao_js."('Chave(".$pesquisa_chave.") não Encontrado',true);</script>";
      }
  }else{
       echo "<script>".$funcao_js."('',false);</script>";
  }
}
?>



     </td>
   </tr>
</table>
        </form>
</body>
</html>
<?
if(!isset($pesquisa_chave)){
  ?>

<script>
   
  var campo1 = document.getElementsByName("id");  
  var campo4 = document.getElementsByName("noempenho");
  var campo5 = document.getElementsByName("e50_dataquebraordem");
  
  campo1[0].value = "Id";  
  campo4[0].value = "Empenho";
  campo5[0].value = "Data da Retirada";
  
  
</script>
  <?
}

?>

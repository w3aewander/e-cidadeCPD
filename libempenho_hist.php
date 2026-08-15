<?
/*
 *     E-cidade Software Publico para Gestao Municipal                
 *  Copyright (C) 2009  DBselller Servicos de Informatica             
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

if($_GET){  
  pg_query("DELETE FROM controleautorizacoes WHERE id = " . $_GET["id"]);
  echo "<script>alert('Liberação cancelada.');</script>";
}

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
  
  
    
    //$sql = "SELECT , usuarioliberado, numautorizacao, dataliberacao FROM controleautorizacoes ORDER BY dataliberacao";
    $sql = "SELECT  (SELECT nome FROM db_usuarios WHERE id_usuario = usuarioautoriza) as Autorizante, (SELECT nome FROM db_usuarios WHERE id_usuario = usuarioliberado) as Autorizado, numautorizacao, dataliberacao, id FROM controleautorizacoes ORDER BY dataliberacao";
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
  var campo1 = document.getElementsByName("autorizante");
  var campo2 = document.getElementsByName("autorizado");
  var campo3 = document.getElementsByName("numautorizacao");
  var campo4 = document.getElementsByName("dataliberacao");
  var campo5 = document.getElementsByName("id");
    
  campo1[0].value = "Autorizante";
  campo2[0].value = "Autorizado";
  campo3[0].value = "Autorização de Empenho";
  campo4[0].value = "Data da Liberação";
  campo5[0].value = "Ação";

  var tabela = document.getElementById("TabDbLov");
  var linhas = tabela.getElementsByTagName("tr");
  //console.log(linhas[3].lastElementChild.firstChild.innerText);
  //console.log("============");
  //console.log(linhas[2].lastElementChild.value);
  //console.log(linhas[2].lastElementChild.innerHTML);
  //linhas[2].lastElementChild.innerHTML = "<button><a style='text-decoration:none' href=''>Cancelar</a></button>"; 
  //console.log("============");
  //innerHTML
  for (var i = 2; i < linhas.length - 1; i++) {
    var idc = linhas[i].lastElementChild.firstChild.innerText;
    linhas[i].lastElementChild.innerHTML = "<button><a style='text-decoration:none;color:black' href='libempenho_hist.php?id="+idc+"'>Cancelar</a></button>"; 

    linhas[i].setAttribute("onclick", "mostraid("+linhas[i].firstChild.innerText+")");
  }
  


</script>
  <?
}

?>


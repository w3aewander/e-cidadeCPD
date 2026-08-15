<?

require("libs/db_stdlib.php");
require("libs/db_conecta.php");
include("libs/db_sessoes.php");
include("libs/db_usuariosonline.php");
include("dbforms/db_funcoes.php");
//include("classes/db_liclocal_classe.php");
db_postmemory($HTTP_POST_VARS);
parse_str($HTTP_SERVER_VARS["QUERY_STRING"]);
//$clliclocal = new cl_liclocal;
//$clliclocal->rotulo->label("l26_codigo");
//$clliclocal->rotulo->label("l26_codigo");

?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link href="estilos.css" rel="stylesheet" type="text/css">
<script language="JavaScript" type="text/javascript" src="scripts/scripts.js"></script>
</head>
<body bgcolor=#CCCCCC leftmargin="0" topmargin="0" marginwidth="0" marginheight="0">
<table height="100%" border="0"  align="center" cellspacing="0" bgcolor="#CCCCCC">
  <?php /* ?>
  <tr> 
    <td height="63" align="center" valign="top">
        <table width="35%" border="0" align="center" cellspacing="0">
	     <form name="form2" method="post" action="" >
          <tr> 
            <td width="4%" align="right" nowrap title="<?=$Tl26_codigo?>">
              <?=$Ll26_codigo?>
            </td>
            <td width="96%" align="left" nowrap> 
              <?
		       db_input("l26_codigo",8,$Il26_codigo,true,"text",4,"","chave_l26_codigo");
		       ?>
            </td>
          </tr>
          <tr> 
            <td colspan="2" align="center"> 
              <input name="pesquisar" type="submit" id="pesquisar2" value="Pesquisar"> 
              <input name="limpar" type="reset" id="limpar" value="Limpar" >
              <input name="Fechar" type="button" id="fechar" value="Fechar" onClick="parent.db_iframe_liclocal.hide();">
             </td>
          </tr>
        </form>
        </table>
      </td>
  </tr>
  <?php */ ?>
  <tr> 
    <td align="center" valign="top"> 
      <?
      if(!isset($pesquisa_chave)){
        
        /*if(isset($chave_l26_codigo) && (trim($chave_l26_codigo)!="") ){
	         $sql = $clliclocal->sql_query($chave_l26_codigo,$campos,"l26_codigo");        
        }else{
           $sql = $clliclocal->sql_query("",$campos,"l26_codigo","");
        }*/
        $sql = "SELECT * FROM tiposituacao ORDER BY sequencial";
        db_lovrot($sql,15,"()","",$funcao_js);
      }else{
        if($pesquisa_chave!=null && $pesquisa_chave!=""){
          //var_dump($pesquisa_chave); die("Confere");
          //$result = $clliclocal->sql_record($clliclocal->sql_query($pesquisa_chave));
          /*if($clliclocal->numrows!=0){
            db_fieldsmemory($result,0);
            echo "<script>".$funcao_js."('$l26_codigo',false);</script>";
          }else{
	         echo "<script>".$funcao_js."('Chave(".$pesquisa_chave.") não Encontrado',true);</script>";
          }*/
        }else{
	       echo "<script>".$funcao_js."('',false);</script>";
        }
      }
      ?>
     </td>
   </tr>
</table>
</body>
</html>
<?
if(!isset($pesquisa_chave)){
  ?>
  <script>
  </script>
  <?
}
?>
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
$anocorrente = db_getsession("DB_anousu");
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
        <table width="35%" border="0" align="center" cellspacing="0" style="width: 260px;float:left">
        <form name="form2" method="post" action="" >
          
          <tr>
            <td width="4%" align="right" nowrap title="Código do Órgão"><b>Código do Órgão</b></td>
            <td width="96%" align="left" nowrap>
         <? db_input("o58_orgao",6,$Io58_orgao,true,"text",4,"","chave_o58_orgao"); ?>
            </td>
          </tr>

          <tr>
            <td width="4%" align="right" nowrap title="Sequencial"><b>ID</b></td>
            <td width="96%" align="left" nowrap>
         <? db_input("id",6,$Iid,true,"text",4,"","chave_id"); ?>
            </td>
          </tr>
          <?php  /*?>
          <tr> 
            <td width="4%" align="right" nowrap title="<?=$To58_coddot?>">
            <?=$Lo58_coddot?>
            </td>
            <td width="96%" align="left" nowrap> 
            <? db_input("o58_coddot",6,$Io58_coddot,true,"text",4,"","chave_o58_coddot"); ?>
            </td>
          </tr>
          <?php */ ?>
          <?
           //$clestrutura->nomeform="form2";//o nome do campo é DB_txtdotacao
	         //$clestrutura->estrutura('o50_estrutdespesa')
          ?>	  
          <tr> 
            <td colspan="2" align="center"> 
              <input name="pesquisar" type="submit" id="pesquisar2" value="Pesquisar"> 
              <input name="limpar" type="reset" id="limpar" value="Limpar" >
              <input name="Fechar" type="button" id="fechar" value="Fechar" onClick="parent.db_iframe_orcdotacao.hide();">
             </td>
          </tr>
        
        </form>
        </table>
      </td>
  </tr>
  <tr> 
    <td align="center" valign="top"> 
<?

if(!isset($pesquisa_chave)){
  
  if(isset($chave_o58_orgao) && (trim($chave_o58_orgao)!="") ){
    
    if(db_getsession("DB_instit") == 1){
      $sql = "SELECT id, o50_estrutdespesa, o56_elemento, o55_descr, o56_descr, o58_coddot, o58_instit, o58_anousu, nomeinst, o58_orgao, o40_descr, o58_unidade, o41_descr, o58_funcao, o52_descr, o58_subfuncao, o53_descr, o58_programa, o54_descr, o58_projativ, o58_codigo, o15_descr, o58_localizadorgastos, o11_descricao, o58_concarpeculiar, c58_descr, o58_valor FROM previadespesas WHERE o58_orgao = '{$chave_o58_orgao}' AND o58_anousu = '{$anocorrente}' ORDER BY o50_estrutdespesa";
      
      db_lovrot($sql,15,"()","",$funcao_js);
    } else {
      $sql = "SELECT id, o50_estrutdespesa, o56_elemento, o55_descr, o56_descr, o58_coddot, o58_instit, o58_anousu, nomeinst, o58_orgao, o40_descr, o58_unidade, o41_descr, o58_funcao, o52_descr, o58_subfuncao, o53_descr, o58_programa, o54_descr, o58_projativ, o58_codigo, o15_descr, o58_localizadorgastos, o11_descricao, o58_concarpeculiar, c58_descr, o58_valor FROM previadespesas WHERE o58_orgao = {$chave_o58_orgao} AND o58_instit = '".db_getsession("DB_instit")."' AND o58_anousu = '{$anocorrente}' ORDER BY o50_estrutdespesa";
      
      db_lovrot($sql,15,"()","",$funcao_js);
    }

      
  } else if(isset($chave_id) && (trim($chave_id)!="")){
    if(db_getsession("DB_instit") == 1){
      $sql = "SELECT id, o50_estrutdespesa, o56_elemento, o55_descr, o56_descr, o58_coddot, o58_instit, o58_anousu, nomeinst, o58_orgao, o40_descr, o58_unidade, o41_descr, o58_funcao, o52_descr, o58_subfuncao, o53_descr, o58_programa, o54_descr, o58_projativ, o58_codigo, o15_descr, o58_localizadorgastos, o11_descricao, o58_concarpeculiar, c58_descr, o58_valor FROM previadespesas WHERE id = '{$chave_id}' ORDER BY o50_estrutdespesa";      
      db_lovrot($sql,15,"()","",$funcao_js);
    } else {
      $sql = "SELECT id, o50_estrutdespesa, o56_elemento, o55_descr, o56_descr, o58_coddot, o58_instit, o58_anousu, nomeinst, o58_orgao, o40_descr, o58_unidade, o41_descr, o58_funcao, o52_descr, o58_subfuncao, o53_descr, o58_programa, o54_descr, o58_projativ, o58_codigo, o15_descr, o58_localizadorgastos, o11_descricao, o58_concarpeculiar, c58_descr, o58_valor FROM previadespesas WHERE id = {$chave_id} AND o58_instit = '".db_getsession("DB_instit")."' AND o58_anousu = '{$anocorrente}' ORDER BY o50_estrutdespesa";      
      db_lovrot($sql,15,"()","",$funcao_js);
    }


  } else {
    $sql = "SELECT id, o50_estrutdespesa, o56_elemento, o55_descr, o56_descr, o58_coddot, o58_instit, o58_anousu, nomeinst, o58_orgao, o40_descr, o58_unidade, o41_descr, o58_funcao, o52_descr, o58_subfuncao, o53_descr, o58_programa, o54_descr, o58_projativ, o58_codigo, o15_descr, o58_localizadorgastos, o11_descricao, o58_concarpeculiar, c58_descr, o58_valor FROM previadespesas WHERE o58_instit = '".db_getsession("DB_instit")."' AND o58_anousu = '{$anocorrente}' ORDER BY o50_estrutdespesa";

    db_lovrot($sql,15,"()","",$funcao_js);
  }
  
  
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
  var campoid = document.getElementsByName("id");
  campoid[0].value = "ID";
</script>
  <?
}

?>

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
include("classes/db_matestoqueini_classe.php");

function sql_query_maternrm ( $m80_codigo=null,$campos="*",$ordem=null,$dbwhere=""){
     $sql = "select ";
     if($campos != "*" ){
       $campos_sql = split("#",$campos);
       $virgula = "";
       for($i=0;$i<sizeof($campos_sql);$i++){
         $sql .= $virgula.$campos_sql[$i];
         $virgula = ",";
       }
     }else{
       $sql .= $campos;
     }
     $sql .= " from matestoqueini ";
     $sql .= "      inner join matestoqueinimei  on  matestoqueinimei.m82_matestoqueini = matestoqueini.m80_codigo";
     $sql .= "       left join matestoquetransf  on  matestoquetransf.m83_matestoqueini = matestoqueini.m80_codigo";
     $sql .= "      inner join matestoqueitem  on  matestoqueitem.m71_codlanc = matestoqueinimei.m82_matestoqueitem";
     $sql .= "      left join  matestoqueitemlote on  matestoqueitem.m71_codlanc = matestoqueitemlote.m77_matestoqueitem";
     $sql .= "      left join  matestoqueitemfabric on  matestoqueitem.m71_codlanc = matestoqueitemfabric.m78_matestoqueitem";
     $sql .= "      left join  matfabricante on matestoqueitemfabric.m78_matfabricante = matfabricante.m76_sequencial";
     $sql .= "      inner join matestoque  on  matestoque.m70_codigo = matestoqueitem.m71_codmatestoque";
     $sql .= "      inner join matmater    on  matmater.m60_codmater =  matestoque.m70_codmatmater";
     $sql .= "      inner join db_usuarios  on  db_usuarios.id_usuario = matestoqueini.m80_login";
     $sql .= "      inner join db_depart   on  db_depart.coddepto =  matestoque.m70_coddepto";
     $sql .= "       left join matestoqueinil   on  matestoqueinil.m86_matestoqueini = matestoqueini.m80_codigo";
     $sql .= "       left join matestoqueinill  on  matestoqueinill.m87_matestoqueinil = matestoqueinil.m86_codigo";
     $sql .= "       left join matestoqueini b  on  b.m80_codigo = matestoqueinill.m87_matestoqueini";
     $sql .= "       left join matestoqueitemnotafiscalmanual on matestoqueitemnotafiscalmanual.m79_matestoqueitem = matestoqueitem.m71_codlanc";
     $sql .= " INNER JOIN materiaisnrm ON materiaisnrm.m80_codigo = matestoqueini.m80_codigo INNER JOIN controleentradanota ON controleentradanota.id = idnrm";
     $sql2 = "";
     if($dbwhere==""){
       if($m80_codigo!=null ){
         $sql2 .= " where matestoqueini.m80_codigo = $m80_codigo ";
       }
     }else if($dbwhere != ""){
       $sql2 = " where $dbwhere";
     }
     $sql .= $sql2;
     if($ordem != null ){
       $sql .= " order by ";
       $campos_sql = split("#",$ordem);
       $virgula = "";
       for($i=0;$i<sizeof($campos_sql);$i++){
         $sql .= $virgula.$campos_sql[$i];
         $virgula = ",";
       }
     }
     return $sql;
  }

db_postmemory($HTTP_POST_VARS);
parse_str($HTTP_SERVER_VARS["QUERY_STRING"]);
$clmatestoqueini = new cl_matestoqueini;
$clmatestoqueini->rotulo->label("m80_codigo");
$clmatestoqueini->rotulo->label("m80_matestoqueitem");
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
            <td width="4%" align="right" nowrap title="<?=$Tm80_codigo?>">
              <?=$Lm80_codigo?>
            </td>
            <td width="96%" align="left" nowrap> 
              <?
		       db_input("m80_codigo",10,$Im80_codigo,true,"text",4,"","chave_m80_codigo");
		       ?>
            </td>
          </tr>
          <tr> 
            <td colspan="2" align="center"> 
              <input name="pesquisar" type="submit" id="pesquisar2" value="Pesquisar"> 
              <input name="limpar" type="reset" id="limpar" value="Limpar" >
              <input name="Fechar" type="button" id="fechar" value="Fechar" onClick="parent.db_iframe_matestoqueini.hide();">
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
      $where_parametro = "";
      if(isset($chave_m80_codtipo)){
        $where_parametro .= " and matestoqueini.m80_codtipo in ($chave_m80_codtipo) ";
      }
      if(isset($chave_m80_coddepto)){
        $where_parametro .= " and matestoqueini.m80_coddepto in ($chave_m80_coddepto) ";
      }
      if(isset($naoatendido)){
        $where_parametro .= " and matestoqueitem.m71_quant>m71_quantatend ";
      }
      if(isset($naoinill)){
        $where_parametro .= " and (b.m80_codtipo in ($naoinill) or b.m80_codigo is null) ";
      }
      
      if(!isset($pesquisa_chave)){
        if(isset($campos)==false){
           if(file_exists("funcoes/db_func_matestoqueini.php")==true){
             include("funcoes/db_func_matestoqueini.php");
           }else{
           $campos = "matestoqueini.*";
           }
        }
        $campos = "matestoqueini.m80_codigo,matmater.m60_codmater||' - '||matmater.m60_descr as codname,idnrm, (SELECT sequencial||'/'||anousuario as nrm FROM controleentradanota WHERE id = idnrm) as nrm, (SELECT e60_codemp||'/'||e60_anousu as empenho FROM empempenho INNER JOIN controleentradanota ON sequencialempenho = e60_numemp WHERE id = idnrm) as empenho,  nome,matestoqueini.m80_data,matestoqueini.m80_hora,matestoqueini.m80_obs, sequencialempenho";
        
	      $campos = " distinct ".$campos;
        if(isset($chave_m80_codigo) && (trim($chave_m80_codigo)!="") ){
           $sql = sql_query_maternrm(null,$campos,"matestoqueini.m80_codigo desc"," matestoqueini.m80_codigo=$chave_m80_codigo $where_parametro");
        }else{
           $sql = sql_query_maternrm("",$campos,"matestoqueini.m80_codigo desc","1=1 $where_parametro");
        }
        //$sql = explode("where 1=1", $sql);
        //INNER JOIN materiaisnrm ON materiaisnrm.m80_codigo = matestoqueini.m80_codigo INNER JOIN controleentradanota ON controleentradanota.id = idnrm

        //var_dump($sql);
        db_lovrot($sql,15,"()","",$funcao_js);
      }else{
        if($pesquisa_chave!=null && $pesquisa_chave!=""){
          $result = $clmatestoqueini->sql_record($clmatestoqueini->sql_query_mater(null," distinct *","","matestoqueini.m80_codigo=$pesquisa_chave $where_parametro"));
          if($clmatestoqueini->numrows!=0){
            db_fieldsmemory($result,0);
            echo "<script>".$funcao_js."('$m80_matestoqueitem',false);</script>";
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
<script>
  document.getElementById("TabDbLov").style.marginTop = "30px";
  var idnrm = document.getElementsByName("idnrm");
  var nrm = document.getElementsByName("nrm");
  var empenho = document.getElementsByName("empenho");
  var codname = document.getElementsByName("codname");
  idnrm[0].value = "ID NRM";
  nrm[0].value = "NRM";
  empenho[0].value = "Empenho";
  codname[0].value = "Material";
</script>
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
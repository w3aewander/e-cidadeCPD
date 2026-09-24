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
  
  if(isset($chave_o58_orgao) && (trim($chave_o58_orgao)!="") ){
    
    if(db_getsession("DB_instit") == 1){      
      $sql = "SELECT id, e60_numemp, noempenho, nocertificado, e50_codord, CASE WHEN cseq2 = 0 THEN 'Não' ELSE 'Sim' END as retiradooc, e60_instit, CASE WHEN excluido = 1 THEN 'Sim' ELSE 'Não' END as excluido, CASE WHEN suspensao = 1 THEN 'Sim' ELSE 'Não' END as suspensao FROM certificacaoconformidade WHERE e60_numemp = '{$chave_o58_orgao}' AND (suspensao = 1 OR suspensao = 2)ORDER BY e60_instit, id";      
      db_lovrot($sql,15,"()","",$funcao_js);
    } else {
      $sql = "SELECT id, e60_numemp, noempenho, nocertificado, e50_codord, CASE WHEN cseq2 = 0 THEN 'Não' ELSE 'Sim' END as retiradooc, e60_instit, CASE WHEN excluido = 1 THEN 'Sim' ELSE 'Não' END as excluido, CASE WHEN suspensao = 1 THEN 'Sim' ELSE 'Não' END as suspensao FROM certificacaoconformidade WHERE e60_numemp = '{$chave_o58_orgao}' AND e60_instit = '".db_getsession("DB_instit")."' AND (suspensao = 1 OR suspensao = 2) ORDER BY id";      
      db_lovrot($sql,15,"()","",$funcao_js);
    }

      
  } /*else if(isset($chave_id) && (trim($chave_id)!="")){
    if(db_getsession("DB_instit") == 1){
      $sql = "SELECT id, o50_estrutdespesa, o56_elemento, o55_descr, o56_descr, o58_coddot, o58_instit, o58_anousu, nomeinst, o58_orgao, o40_descr, o58_unidade, o41_descr, o58_funcao, o52_descr, o58_subfuncao, o53_descr, o58_programa, o54_descr, o58_projativ, o58_codigo, o15_descr, o58_localizadorgastos, o11_descricao, o58_concarpeculiar, c58_descr, o58_valor FROM previadespesas WHERE id = '{$chave_id}' ORDER BY o50_estrutdespesa";      
      db_lovrot($sql,15,"()","",$funcao_js);
    } else {
      $sql = "SELECT id, o50_estrutdespesa, o56_elemento, o55_descr, o56_descr, o58_coddot, o58_instit, o58_anousu, nomeinst, o58_orgao, o40_descr, o58_unidade, o41_descr, o58_funcao, o52_descr, o58_subfuncao, o53_descr, o58_programa, o54_descr, o58_projativ, o58_codigo, o15_descr, o58_localizadorgastos, o11_descricao, o58_concarpeculiar, c58_descr, o58_valor FROM previadespesas WHERE id = {$chave_id} AND o58_instit = '".db_getsession("DB_instit")."' AND o58_anousu = '2022' ORDER BY o50_estrutdespesa";      
      db_lovrot($sql,15,"()","",$funcao_js);
    }


  }*/ else {
    if(db_getsession("DB_instit") == 1){
      $sql = "SELECT id, e60_numemp, noempenho, nocertificado, e50_codord, CASE WHEN cseq2 = 0 THEN 'Não' ELSE 'Sim' END as retiradooc, e60_instit, CASE WHEN excluido = 1 THEN 'Sim' ELSE 'Não' END as excluido, CASE WHEN suspensao = 1 THEN 'Sim' ELSE 'Não' END as suspensao FROM certificacaoconformidade WHERE suspensao = 1 OR suspensao = 2 ORDER BY e60_instit, id";
    } else {
      $sql = "SELECT id, e60_numemp, noempenho, nocertificado, e50_codord, CASE WHEN cseq2 = 0 THEN 'Não' ELSE 'Sim' END as retiradooc, e60_instit, CASE WHEN excluido = 1 THEN 'Sim' ELSE 'Não' END as excluido, CASE WHEN suspensao = 1 THEN 'Sim' ELSE 'Não' END as suspensao FROM certificacaoconformidade WHERE e60_instit = '".db_getsession("DB_instit")."' AND (suspensao = 1 OR suspensao = 2) ORDER BY e60_instit, id";
    }
    
    
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
  var campo1 = document.getElementsByName("id");
  var campo2 = document.getElementsByName("e60_numemp");
  var campo3 = document.getElementsByName("noempenho");
  var campo4 = document.getElementsByName("nocertificado");
  var campo5 = document.getElementsByName("e50_codord");
  var campo6 = document.getElementsByName("retiradooc");
  var campo7 = document.getElementsByName("e60_instit");
  var campo8 = document.getElementsByName("excluido");
  var campo9 = document.getElementsByName("suspensao");
  console.log("=========");
  console.log(campo2[0].value);
  console.log("=========");
  campo1[0].value = "Id";
  campo3[0].value = "Nº Empenho";
  campo4[0].value = "Certificado";
  campo6[0].value = "Retirado da OC";
  campo7[0].value = "Instituição";
  campo8[0].value = "Excluído";
  campo9[0].value = "Suspensão";
  //campo2[0].value = "Sequencial";
//Consultar variável $campos
//func_departdiv.php e /funcoes/db_func_departdiv


</script>
  <?
}

?>

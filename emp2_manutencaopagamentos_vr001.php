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

require_once("std/db_stdClass.php");
require("libs/db_stdlib.php");
require("libs/db_conecta.php");
include("libs/db_sessoes.php");
include("libs/db_usuariosonline.php");
include("dbforms/db_funcoes.php");
$clrotulo = new rotulocampo;
db_postmemory($HTTP_POST_VARS);
?>

<html>
<head>
<title>Microsist</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<meta http-equiv="Expires" CONTENT="0">
<script language="JavaScript" type="text/javascript" src="scripts/scripts.js"></script>

<script>
function js_verifica(){
  var anoi = new Number(document.form1.datai_ano.value);
  var anof = new Number(document.form1.dataf_ano.value);
  if(anoi.valueOf() > anof.valueOf()){
    alert('Intervalo de data invalido. Velirique !.');
    return false;
  }
  return true;
}

function js_emite() {

  var oParam           = new Object();
//  oParam.iOrdemIni     = $F('e82_codord');
//  oParam.iOrdemFim     = $F('e82_codord02');
//  oParam.iCodEmp       = $F('e60_codemp');
//  oParam.dtDataIni     = $F('dataordeminicial');
//  oParam.dtDataFim     = $F('dataordemfinal');
//  oParam.iNumCgm       = $F('z01_numcgm');
    iRecurso      = document.form1.recursos.value;
//  oParam.sDtAut        = $F('e42_dtpagamento');
//  oParam.iOPauxiliar   = $F('e42_sequencial');
//  oParam.iAutorizadas  = $F('ordensautorizadas');
//  oParam.lAtualizadas  = $('configuradas').checked;
//  oParam.lNormais      = $('normais').checked;
//  oParam.lChequeArq    = $('comMovs').checked;
//  oParam.orderBy       = $F('orderby');
  sUrl = "emp2_manutencaopagamentos_vr002.php?recurso="+iRecurso;
  window.open(sUrl, '', 'location=0');

}


</script>  
<link href="estilos.css" rel="stylesheet" type="text/css">
</head>
<body bgcolor=#CCCCCC leftmargin="0" topmargin="0" marginwidth="0" marginheight="0" onLoad="a=1" bgcolor="#cccccc">
  <table width="790" border="0" cellpadding="0" cellspacing="0" bgcolor="#5786B2">
  <tr>
    <td width="360" height="18">&nbsp;</td>
    <td width="263">&nbsp;</td>
    <td width="25">&nbsp;</td>
    <td width="140">&nbsp;</td>
  </tr>
</table>

  <table  align="center">
    <form name="form1" method="post" action="" onsubmit="return js_verifica();">
      <tr>
         <td >&nbsp;</td>
         <td >&nbsp;</td>
      </tr>
      <tr >
        <td align="right" ><strong>Fonte :&nbsp;&nbsp;</strong>
        </td>
        <td align="left">
          <?
            $arr_recursos = array("100"=>"100 - Próprio",
                                "28"=>"28 - Salário Educação",
                                "23"=>"23 - Fundeb",
                                "40"=>"40 - Assitência Médica");
            db_select('recursos',$arr_recursos,true,4,"");
	        ?>
	      </td>
      </tr>
      <tr>
        <td >&nbsp;</td>
        <td >&nbsp;</td>
      </tr>
      <tr>
        <td colspan="2" align = "center"> 
          <input  name="emite2" id="emite2" type="button" value="Processar" onclick="js_emite();" >
        </td>
      </tr>

  </form>
    </table>
<?
  db_menu(db_getsession("DB_id_usuario"),db_getsession("DB_modulo"),db_getsession("DB_anousu"),db_getsession("DB_instit"));
?>
</body>
</html>

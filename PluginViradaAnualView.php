<?php

require_once(modification("libs/db_stdlib.php"));
require_once(modification("libs/db_conecta_plugin.php"));
require_once(modification("libs/db_sessoes.php"));
require_once(modification("libs/db_app.utils.php"));
require_once(modification("dbforms/db_funcoes.php"));
require_once(modification("dbforms/db_classesgenericas.php"));

$cliframe_seleciona = new cl_iframe_seleciona;
db_postmemory($HTTP_POST_VARS);

if (!isset($anodestino)){
	$anodestino = (int)db_getsession("DB_anousu");	
}

$anoorigem  = $anodestino - 1;

$sqlitem  = " select distinct         "; 
$sqlitem .= "        c33_sequencial,  ";
$sqlitem .= "        c33_descricao    ";
$sqlitem .= "   from db_viradacaditem ";
$sqlitem .= "  order by c33_sequencial";
/*
 * Desabilitar os itens quando o usuário utilizar o pcasp
 * Variavel da sessão ou anodestino igual ao ano da implantação do pcasp (pcasp.txt)     
 */
$sqlitem_disabled  = "  select distinct                                                                 ";
$sqlitem_disabled .= "         c33_sequencial,                                                          ";
$sqlitem_disabled .= "         c33_descricao                                                            ";
$sqlitem_disabled .= "    from db_viradacaditem                                                         ";
$sqlitem_disabled .= "         left join db_viradaitem on c31_db_viradacaditem = c33_sequencial         ";
$sqlitem_disabled .= "         left join db_virada     on  c31_db_virada       = c30_sequencial         ";
$sqlitem_disabled .= "                                and c30_anoorigem        = $anoorigem             ";
$sqlitem_disabled .= "                                and c30_anodestino       = $anodestino            ";
$sqlitem_disabled .= "   where case                                                                     ";
$sqlitem_disabled .= "           when $anoorigem = 2012 and c33_sequencial in (11,14,26,27)             ";
$sqlitem_disabled .= "             then true                                                            ";
$sqlitem_disabled .= "           else c30_sequencial is not null and c33_sequencial not in (13, 14, 23) ";
$sqlitem_disabled .= "         end"; //acrescentado o item 23 CONFIGURAÇÕES PADRÃO DOS RELATÓRIOS LEGAIS

$aPcasp    = array();
$aPcasp[0] = "";
if ( file_exists("config/pcasp.txt") ) {	
	$aPcasp = file("config/pcasp.txt");
}

if ( USE_PCASP ) {

/*
  $sqlitem_disabled .= "  union                          ";
  $sqlitem_disabled .= " select c33_sequencial,          ";
  $sqlitem_disabled .= "        c33_descricao            ";
  $sqlitem_disabled .= "   from db_viradacaditem         ";      
  $sqlitem_disabled .= " where c33_sequencial in (26,27) ";
*/

  /*
   * Caso seja utilizado pcasp, incluimos os dados da virada do item 14.
   */
  $sSqlValidaViradaItem14 = "select db_virada.c30_sequencial 
  		                         from db_virada 
  		                              inner join db_viradaitem on db_viradaitem.c31_db_virada = db_virada.c30_sequencial 
  		                        where c30_anodestino = {$anoorigem} 
  		                          and c31_db_viradacaditem = 14;";
  $rsValidaViradaItem14   = db_query($sSqlValidaViradaItem14);
  if (pg_num_rows($rsValidaViradaItem14) == 0) {
  	$sSqlInsertViradaItem14 = "insert into db_viradaitem select nextval('db_viradaitem_c31_sequencial_seq'),
  	                                                            max(c30_sequencial),
  	                                                            14,
  	                                                            1
  	                                                       from db_virada
  	                                                      where c30_anodestino = {$anoorigem}";
  	$rsInsertViradaItem14 = db_query($sSqlInsertViradaItem14);
  }		                                 
  
} 

/*
 * Desabilitamos os itens quando o cliente não é PCASP em 2013.
 */

/*
if ( $aPcasp[0] != 2013 ) {
	$sqlitem_disabled .= "  union                       ";
	$sqlitem_disabled .= " select c33_sequencial,       ";
	$sqlitem_disabled .= "        c33_descricao         ";
	$sqlitem_disabled .= "   from db_viradacaditem      ";
	$sqlitem_disabled .= " where c33_sequencial in (11,14,26,27) ";
}
 */


?>
<html>
<head>
<title>Microsist</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<meta http-equiv="Expires" CONTENT="0">
<script language="JavaScript" type="text/javascript" src="scripts/scripts.js"></script>
<script language="JavaScript" type="text/javascript" src="scripts/prototype.js"></script>
<script language="JavaScript" type="text/javascript" src="scripts/AjaxRequest.js"></script>

<script>

function js_onlyNumbers(oObj) {
   oObj.value = oObj.value.replace(/[^0-9]/g, '');

}

function js_reload() {
	var page = window.location = window.location.href.split("?")[0];
	if (anodestino.value != "") {
		location.href = page + "?anodestino="+anodestino.value;
	}
}

function js_processa(){
  objForm = caracteristicas.document.getElementsByTagName('input');
  document.form1.itensprocessa.value ='';
  for (i = 0; i < objForm.length; i++) {
     if (objForm[i].type == 'checkbox') {
       if(objForm[i].checked == true){
         document.form1.itensprocessa.value = document.form1.itensprocessa.value+'_'+objForm[i].value

       }
     }    
  }
}
 
function js_submit(){
  if (anodestino.value == "") {
      alert("Informe o ano destino.");
      return false;
  } else if (itensprocessa.value == "") {
      alert("Selecione um item.");
      return false;
  } else {
    document.form1.submit();
  }
}

</script>  
<link href="estilos.css" rel="stylesheet" type="text/css">
</head>
<body bgcolor=#CCCCCC leftmargin="0" topmargin="0" marginwidth="0" marginheight="0" onLoad="a=1" bgcolor="#cccccc">
  <table width="100%" border="0" cellpadding="0" cellspacing="0" bgcolor="#5786B2">
  <tr>
    <td width="360" height="18">&nbsp;</td>
    <td width="263">&nbsp;</td>
    <td width="25">&nbsp;</td>
    <td width="140">&nbsp;</td>
  </tr>
</table>

  <table  align="center" border="0" width="800" >
    <form name="form1" method="post" action="PluginViradaAnual.php">
      <tr>
         <td > <? db_input("itensprocessa", 20, "", "", "hidden", 1)?> </td>
         <td >&nbsp; </td>
      </tr>
      <tr>
		    <td><b> Ano Destino :<? db_input("anodestino", 10, "", true, "text",1,' onBlur="js_reload()" onKeyUp="js_onlyNumbers(this)" ')?> </b></td>		
      </tr>
      <tr>
        <td colspan="2">
        	<div id=listaItens>
	          <?
	            $cliframe_seleciona->campos        = "c33_sequencial,c33_descricao";
	            $cliframe_seleciona->legenda       = "Itens";
	            $cliframe_seleciona->sql           = $sqlitem;
	            $cliframe_seleciona->sql_disabled  = $sqlitem_disabled;
	            $cliframe_seleciona->iframe_height = "400";
	            $cliframe_seleciona->iframe_width  = "700";
	            $cliframe_seleciona->iframe_nome   = "caracteristicas";
	            $cliframe_seleciona->chaves        = "c33_sequencial";
	            $cliframe_seleciona->dbscript      = "onClick='parent.js_processa()'";
	            $cliframe_seleciona->marcador      = true;
	            $cliframe_seleciona->js_marcador   = "parent.js_processa()";
	            $cliframe_seleciona->iframe_seleciona(null);    
	          ?>
          </div>
        </td>
      </tr>
      <tr>
        <td colspan="0" align = "center"> 
          <input id="btnProcessar" type="button" value="Processar" onClick="js_submit()">
        </td>        
      </tr>
    </form>
  </table>
  <?
  db_menu(db_getsession("DB_id_usuario"),db_getsession("DB_modulo"),db_getsession("DB_anousu"),db_getsession("DB_instit"));
?>
</body>
</html>
<?php


require_once(modification("libs/db_stdlib.php"));
require_once(modification("libs/db_conecta.php"));
require_once(modification("libs/db_sessoes.php"));
require_once(modification("libs/db_usuariosonline.php"));
require_once(modification("dbforms/db_funcoes.php"));


db_postmemory($HTTP_POST_VARS);
parse_str($HTTP_SERVER_VARS["QUERY_STRING"]);

$sGnome = "t";



if(!empty($_POST)){
  $codigos = $_POST["marcacao"];

  foreach($codigos as $id){
    db_query("DELETE FROM receitasvinculadas WHERE codvinculo = {$id}");
  }
  echo "<script>alert('Vínculo excluído');</script>";
}






$ano = db_getsession("DB_anousu");
$instituicao = db_getsession("DB_instit");
$consulta = db_query("SELECT codvinculo, codigofonte, contamsc, estrutural, instituicao, c60_descr FROM receitasvinculadas INNER JOIN conplanoorcamento on receitasvinculadas.codigofonte = conplanoorcamento.c60_codcon WHERE c60_anousu = {$ano} AND instituicao = {$instituicao} ORDER BY contamsc");
$resultado = pg_fetch_all($consulta);
$nrolinhas = pg_num_rows($consulta);





/*

echo "<pre>";
print_r($x);
echo "</pre>";
die("Resultados");
*/


?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link href="estilos.css" rel="stylesheet" type="text/css">
<script language="JavaScript" type="text/javascript" src="scripts/scripts.js"></script>
</head>
<style>
  div.gridcontainer {
  border: 2px inset #FFF;
  background-color: #eeeeee;
  width: 100%;
}

div.header-container div.grid-resize {
  float: right;
  z-index: 998;
  cursor: pointer;
  border: 1px outset #FFF;
}

div.header-container table.table-header {
  background-color: #eeeeee;
  font-weight: bold;
  text-align: center;
  width: 98%;
  border-collapse: collapse;
}

div.header-container table.table-header tr {
  border-bottom: 3px outset #FFF;
  height: 20px;
}

div.header-container table.table-header tr td {
  /*border-right: 1px outset #D3D3D3;*/
  padding: 0;
  margin: 0;
  white-space: nowrap;
  overflow: hidden;
  text-align: center !important;
  padding: 1;
  padding-left: 3;
  position: relative;
  background-clip: padding-box;
}

div.body-container {
  width: 100%;
  height: 100px;
  overflow-y: scroll;
  background-color: #FFF;
}

div.body-container table.table-body {
  /* width: 975px; */
  background-color: #FFF;
  width: 100%;
  border-collapse: collapse;
  overflow: auto;
}

div.footer-container {
  width: 100%;
}


div.body-container table.table-body tr {
  border-bottom: 1px outset #D3D3D3;
  padding: 0;
  margin: 0;
  height: 1em;
}


div.body-container table.table-body tr td {
  border-right: 1px outset #D3D3D3;
  padding: 0;
  margin: 0;
  white-space: nowrap;
  overflow: hidden;
  padding: 1;
  padding-left: 3;
}

input {
  font-family: Arial, Helvetica, sans-serif, verdana;
  font-size: 12px;
  height: 18px;
  border: 1px solid #999999;
}
</style>
<body bgcolor=#CCCCCC leftmargin="0" topmargin="0" marginwidth="0" marginheight="0">


<fieldset>
<legend style="font-weight: bold">Vínculos Realizados</legend>

<div id="campogrid">
<div class="gridcontainer" id="gridgridmsc">
<div class="header-container">
<div class="grid-resize"><img src="imagens/espaco.gif" onclick="return false" border="0">
<div style="clear: both;"></div>
</div>

<table class="table-header" rel="ignore-css" id="tablegridmscheader" style="width: 741px;">
<tbody>
<tr>
<td class="table_header cell" id="col1" title="M" gridcolnumber="0" style="width:5%" nowrap="">M</td>
<td class="table_header cell" id="col2" title="Receita MSC" gridcolnumber="1" style="width:10%" nowrap="">Receita MSC</td>
<td class="table_header cell" id="col3" title="Cód. Fonte" gridcolnumber="2" style="width:10%" nowrap="">Cód. Fonte</td>
<td class="table_header cell" id="col4" title="Descrição" gridcolnumber="3" style="width:50%" nowrap="">Descrição</td>
</tr></tbody></table></div>

<div id="body-container-gridmsc" class="body-container" style="height:auto;">
<table class="table-body" id="gridmscbody" style="width: 741px;">
<form method="post" action="">

<?php foreach($resultado as $linha): ?>
<td class="linhagrid checkbox" style="width: 5%; text-align: center;" nowrap="">
<input type="checkbox" name="marcacao[]" style="height: 12px" value="<?=$linha['codvinculo']?>">
</td>

  <td class="linhagrid cell" title="" style="width: 10%; text-align: center;" nowrap="">
  <?=$linha['contamsc'];?>
  </td>

  <td class="linhagrid cell" title="" style="width: 10%; text-align: center;" nowrap="">
  <?=$linha['codigofonte'];?>
  </td>

  <td class="linhagrid cell" title="" style="width: 50%; text-align: left;" nowrap="">
  <?=$linha['c60_descr'];?>
  </td>
</tr>

<?php endforeach; ?>
  
</tr>

</table>
</div>

<div class="footer-container">
<table class="table-footer" id="tablegridmscfooter" style="width: 741px;">
<tbody>
<tr style="text-align:left;">
<td colspan="6">
<div style="border:1px inset white;height:100%;padding:2px">
<span> Total de Registros:</span>
<span style="color:blue;padding:3px" id="gridmscnumrows"><?=$nrolinhas;?></span>
<span style="border-left:1px inset #eeeee2" id="gridmscstatus">&nbsp;</span>&nbsp;</div></td></tr></tbody></table></div></div>
</div>


<div style="text-align: center">
<tr>    
    <td>
      <input name="salvar" type="submit" id="salvar" value="Excluir">
    <td>

    <td>
      
      <input name="Fechar" type="button" id="fechar" value="Fechar" onClick="parent.db_iframe_db_vinculos.hide();">
    <td>
</div>
      </form>
</fieldset>

<script>
  function fechar(){
    window.close();
  }

</script>

</body>
</html>
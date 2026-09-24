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



if(!empty($_POST)){
  $codigos = $_POST["marcacao"];
  

  foreach($codigos as $id){
    db_query("DELETE FROM contasvinculadas WHERE codvinculo = {$id}");
  }
  echo "<script>alert('Vínculo excluído');</script>";
}

function buscaDescricao($reduzido){
  $consulta = db_query("select distinct conplano.c60_descr from conplano inner join conclass on conclass.c51_codcla = conplano.c60_codcla inner join consistema on consistema.c52_codsis = conplano.c60_codsis left join conplanoreduz on conplanoreduz.c61_codcon = conplano.c60_codcon and c61_anousu = c60_anousu where c61_reduz = {$reduzido}");
  $resultado = pg_fetch_all($consulta);    
  $descricao = $resultado[0]["c60_descr"];
  return $descricao;
}

$ano = db_getsession("DB_anousu");
$instituicao = db_getsession("DB_instit");

$blocao = array();

$primeiraconsulta = db_query("SELECT codvinculo, contamsc, codigo, reduzido, estrutural, instituicao FROM contasvinculadas INNER JOIN contasmsc ON contasvinculadas.contamsc = contasmsc.conta WHERE instituicao = {$instituicao} ORDER BY contamsc");
$primeiroresultado = pg_fetch_all($primeiraconsulta);;

foreach ($primeiroresultado as $linha) {
  $desc = buscaDescricao($linha["reduzido"]);
  array_push($linha, $desc);
  array_push($blocao, $linha);
}


/*echo "<pre>";
print_r($blocao);
//print_r($primeiroresultado);
echo "</pre>";
echo count($blocao);
die("Confere");
*/

$nrolinhas = count($blocao);



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
<td class="table_header cell" id="col2" title="Receita MSC" gridcolnumber="1" style="width:10%" nowrap="">Código MSC</td>
<td class="table_header cell" id="col3" title="Cód. Fonte" gridcolnumber="2" style="width:20%" nowrap="">Reduzido e-Cidade</td>
<td class="table_header cell" id="col3" title="Cód. Fonte" gridcolnumber="2" style="width:10%" nowrap="">Estrutural</td>
<td class="table_header cell" id="col4" title="Descrição" gridcolnumber="3" style="width:50%" nowrap="">Descrição</td>
</tr></tbody></table></div>

<div id="body-container-gridmsc" class="body-container" style="height:auto;">
<table class="table-body" id="gridmscbody" style="width: 741px;">
<form method="post" action="">


<?php foreach($blocao as $linha): ?>
<td class="linhagrid checkbox" style="width: 5%; text-align: center;" nowrap="">
<input type="checkbox" name="marcacao[]" style="height: 12px" value="<?=$linha['codvinculo']?>">
</td>

  <td class="linhagrid cell" title="" style="width: 10%; text-align: center;" nowrap="">
  <?=$linha['contamsc'];?>
  </td>

  <td class="linhagrid cell" title="" style="width: 20%; text-align: center;" nowrap="">
  <?=$linha['reduzido'];?>
  </td>

  <td class="linhagrid cell" title="" style="width: 10%; text-align: center;" nowrap="">
  <?=$linha['estrutural'];?>
  </td>

  <td class="linhagrid cell" title="" style="width: 50%; text-align: left;" nowrap="">
  <?=$linha['0'];?>
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
      
      <input name="Fechar" type="button" id="fechar" value="Fechar" onClick="parent.db_iframe_db_vinculospcasp.hide();">
    <td>
</div>
      </form>
</fieldset>



</body>
</html>
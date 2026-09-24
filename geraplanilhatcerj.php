<?


require("libs/db_stdlib.php");
require("libs/db_conecta.php");
include("libs/db_sessoes.php");
include("libs/db_usuariosonline.php");
include("dbforms/db_funcoes.php");
include("classes/db_pcorcam_classe.php");
$clpcorcam = new cl_pcorcam;
$clpcorcam->rotulo->label();
db_postmemory($HTTP_POST_VARS);

function testa($var){
  echo "<pre>";
  print_r($var);
  echo "</pre>";
}


function buscaDados($codigo){
  $sql = pg_query("SELECT pc23_orcamitem, pc23_fonteref, pc23_codref, pc23_dataref, pc01_descrmater, pc23_quant, m61_abrev, pc23_vlrun, pc22_codorc from pcorcamitem INNER JOIN pcorcamval ON pc22_orcamitem = pc23_orcamitem inner join pcorcam on pcorcam.pc20_codorc = pcorcamitem.pc22_codorc inner join pcorcamitemsol on pcorcamitemsol.pc29_orcamitem = pcorcamitem.pc22_orcamitem inner join solicitem on solicitem.pc11_codigo = pcorcamitemsol.pc29_solicitem left join solicitempcmater on solicitempcmater.pc16_solicitem = solicitem.pc11_codigo left join pcmater on pcmater.pc01_codmater = solicitempcmater.pc16_codmater left join solicitemunid on solicitemunid.pc17_codigo = solicitem.pc11_codigo left join matunid on matunid.m61_codmatunid = solicitemunid.pc17_unid where pc20_codorc={$codigo} order by pc22_orcamitem");
  $resultado = pg_fetch_all($sql);
  return $resultado;
}


if($_POST){
  header('Location: planilhatcerj.php?codigo='.$_POST["pc20_codorc"]);

  /*$dados = buscaDados($_POST["pc20_codorc"]);
  
  header("Content-Type:   application/vnd.ms-excel; charset=utf-8");
  header("Content-type:   application/x-msexcel; charset=utf-8");
  header("Content-Disposition: attachment; filename=planilha_tcerj.xls");
  header("Expires: 0");
  header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
  header("Cache-Control: private",false);

  print "<table border=1>";
  print "<thead>";
  print "<tr>";
  print "<th>Número Item</th>";
  print "<th>Fonte de Referência</th>";
  print "<th>Código de Referência</th>";
  print "<th>Data de Referência</th>";
  print "<th colspan='5'>Descrição</th>";
  print "<th>Quantidade</th>";
  print "<th>Unidade de Medida</th>";
  print "<th>Valor Unitário</th>";  
  print '</tr>';

  foreach ($dados as $linha){  
    $data = implode("/", array_reverse(explode("-", $linha["pc23_dataref"])));
    $valor = number_format($linha["pc23_vlrun"], 2, ',', '');

    print "<tr>";
      print "<td>" . $linha["pc23_orcamitem"] . "</td>";
      print "<td>" . $linha["pc23_fonteref"] . "</td>";
      print "<td>" . $linha["pc23_codref"] . "</td>";
      print "<td>" . $data . "</td>";
      print "<td colspan='5'>" . $linha["pc01_descrmater"] . "</td>";
      print "<td>" . $linha["pc23_quant"] . "</td>";
      print "<td>" . $linha["m61_abrev"] . "</td>";
      print "<td>" . $valor . "</td>";
    print "</tr>";
    
  }*/
}


?>
<html>
<head>
<title>Microsist</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<meta http-equiv="Expires" CONTENT="0">
<script language="JavaScript" type="text/javascript" src="scripts/scripts.js"></script>

<script>
function js_abre(){
  if(document.form1.pc20_codorc.value == ""){
    document.form1.pc20_codorc.focus();
    alert("Informe o código do orçamento");
  }else{
    document.form1.submit();    
  }
}
</script>  
<link href="estilos.css" rel="stylesheet" type="text/css">
</head>
<body bgcolor=#CCCCCC leftmargin="0" topmargin="0" marginwidth="0" marginheight="0" onLoad="document.form1.pc20_codorc.focus();" bgcolor="#cccccc">
  <table width="790" border="0" cellpadding="0" cellspacing="0" bgcolor="#5786B2">
  <tr>
    <td width="360" height="18">&nbsp;</td>
    <td width="263">&nbsp;</td>
    <td width="25">&nbsp;</td>
    <td width="140">&nbsp;</td>
  </tr>
</table>
<center>
<form name="form1" method="post" action="">
<table border='0'>
  <tr height="20px">
    <td ></td>
    <td ></td>
  </tr>
  <tr> 
    <td  align="left" nowrap title="<?=$Tpc20_codorc?>"> <? db_ancora(@$Lpc20_codorc,"js_pesquisa_pcorcam(true);",1);?>  </td>
    <td align="left" nowrap>
      <?
         db_input("pc20_codorc",8,$Ipc20_codorc,true,"text",4,"onchange='js_pesquisa_pcorcam(false);'"); 
	 db_input('sol',6,0,true,'hidden',3);
      ?>
    </td>
  </tr>
  <tr height="20px">
    <td ></td>
    <td ></td>
  </tr>
  <tr>
    <td colspan="2" align="center">
      <input name="lancar" type="button" onclick='js_abre();'  value="Enviar dados">
    </td>
  </tr>
</table>
</form>
</center>
<? db_menu(db_getsession("DB_id_usuario"),db_getsession("DB_modulo"),db_getsession("DB_anousu"),db_getsession("DB_instit"));?>
<script>
//---------------------------------
function js_pesquisa_pcorcam(mostra){
  qry = "";
  <?
  if(isset($sol) && $sol=='true'){
    echo "qry='&sol=true&sel=true';";
    echo "qry+='&departamento=".db_getsession("DB_coddepto")."';";
    echo "qry+='&numero=numero';";
  }else if(isset($sol) && $sol=='false'){
    echo "qry='&sol=false&sel=true';";
  }
  if(isset($julg) && trim($julg)!=""){
    echo "qry+='&julg=true';";
  }
  ?>
  if(mostra==true){
    js_OpenJanelaIframe('top.corpo','db_iframe_pcorcam','func_pcorcamlancval.php?funcao_js=parent.js_mostrapcorcam1|pc20_codorc'+qry,'Pesquisa',true);
  }else{
     if(document.form1.pc20_codorc.value != ''){ 
        js_OpenJanelaIframe('top.corpo','db_iframe_pcorcam','func_pcorcamlancval.php?pesquisa_chave='+document.form1.pc20_codorc.value+'&funcao_js=parent.js_mostrapcorcam'+qry,'Pesquisa',false);
     }
  }
}
function js_mostrapcorcam(chave,erro){
  if(erro==true){ 
    document.form1.pc20_codorc.focus(); 
    document.form1.pc20_codorc.value = ''; 
  }
}
function js_mostrapcorcam1(chave1,chave2){
  document.form1.pc20_codorc.value = chave1;
  db_iframe_pcorcam.hide();
}
//--------------------------------
</script>
</body>
</html>
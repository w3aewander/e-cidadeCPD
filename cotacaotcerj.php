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
 

function buscaDados($licitacao){
  $sql = pg_query("SELECT pc23_orcamforne, pc21_numcgm, pc23_vlrun, pc23_tipo, pc11_seq from pcorcamitem inner join pcorcam on pcorcam.pc20_codorc = pcorcamitem.pc22_codorc left join pcorcamforne on pcorcamforne.pc21_codorc = pcorcam.pc20_codorc inner join pcorcamitemlic on pcorcamitemlic.pc26_orcamitem = pcorcamitem.pc22_orcamitem inner join liclicitem on pcorcamitemlic.pc26_liclicitem = liclicitem.l21_codigo inner join liclicita on liclicita.l20_codigo = liclicitem.l21_codliclicita inner join pcprocitem on pcprocitem.pc81_codprocitem = liclicitem.l21_codpcprocitem inner join solicitem on solicitem.pc11_codigo = pcprocitem.pc81_solicitem inner join solicita on solicita.pc10_numero = solicitem.pc11_numero left join solicitaregistropreco on solicitaregistropreco.pc54_solicita = solicita.pc10_numero left join solicitemunid on solicitemunid.pc17_codigo = solicitem.pc11_codigo left join matunid on matunid.m61_codmatunid = solicitemunid.pc17_unid left join solicitempcmater on solicitempcmater.pc16_solicitem = solicitem.pc11_codigo left join pcmater on pcmater.pc01_codmater = solicitempcmater.pc16_codmater left join pcorcamval on pcorcamval.pc23_orcamitem = pcorcamitem.pc22_orcamitem and pcorcamval.pc23_orcamforne = pcorcamforne.pc21_orcamforne left join pcorcamdescla on pcorcamdescla.pc32_orcamitem = pcorcamitem.pc22_orcamitem and pcorcamdescla.pc32_orcamforne = pcorcamforne.pc21_orcamforne left join liclicitemlote on liclicitemlote.l04_liclicitem = liclicitem.l21_codigo left join licsituacao on liclicita.l20_licsituacao = licsituacao.l08_sequencial where l20_codigo = {$licitacao} and l20_licsituacao = 1 order by pc23_orcamforne,pc11_seq");
  $resultado = pg_fetch_all($sql);
  return $resultado;
}

function buscaCNPJ($cgm){
  $sql = pg_query("SELECT z01_cgccpf FROM cgm WHERE z01_numcgm = {$cgm}");
  $resultado = pg_fetch_all($sql);
  return $resultado[0]["z01_cgccpf"];
}

$planilha = array();
$indice = 0;
$dados = buscaDados($_GET["codigo"]);


  foreach ($dados as $linha) {
    $planilha[$indice]["cnpj"] = buscaCNPJ($linha["pc21_numcgm"]);    
    $planilha[$indice]["valor"] =  number_format($linha["pc23_vlrun"], 2, ',', '');
    $planilha[$indice]["tipo"] = (isset($linha["pc23_tipo"])) ? $linha["pc23_tipo"] : 2;
    $planilha[$indice]["seq"] = (int)$linha["pc11_seq"];
    $indice++;
  }
 
  
  header("Content-Type:   application/vnd.ms-excel; charset=utf-8");
  header("Content-type:   application/x-msexcel; charset=utf-8");
  header("Content-Disposition: attachment; filename=ImportacaoItens.xls");
  header("Expires: 0");
  header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
  header("Cache-Control: private",false);

  print "<table>";  
  print "<tr>";
  print "<td>Participante</td>";
  print "<td>PrecoUnitario</td>";
  print "<td>Situacao</td>";
  print "<td>Número Item</td>";  
  print '</tr>';

  foreach ($planilha as $linha){
    
    print "<tr>";
    if(substr($linha["cnpj"], 0, 1) == "0"){
      print "<td>'" . $linha["cnpj"] . "</td>"; 
    } else {
      print "<td>" . $linha["cnpj"] . "</td>";
    }
      print "<td>" . $linha["valor"] . "</td>";
      print "<td>" . $linha["tipo"] . "</td>";
      print "<td>" . $linha["seq"] . "</td>";
    print "</tr>";    
  } 

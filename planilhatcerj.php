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
  $sql = pg_query("SELECT pc11_seq, pc23_fonteref, pc23_codref, pc23_dataref, pc01_descrmater, pc23_quant, m61_abrev, pc23_vlrun, pc22_codorc from pcorcamitem INNER JOIN pcorcamval ON pc22_orcamitem = pc23_orcamitem inner join pcorcam on pcorcam.pc20_codorc = pcorcamitem.pc22_codorc inner join pcorcamitemsol on pcorcamitemsol.pc29_orcamitem = pcorcamitem.pc22_orcamitem inner join solicitem on solicitem.pc11_codigo = pcorcamitemsol.pc29_solicitem left join solicitempcmater on solicitempcmater.pc16_solicitem = solicitem.pc11_codigo left join pcmater on pcmater.pc01_codmater = solicitempcmater.pc16_codmater left join solicitemunid on solicitemunid.pc17_codigo = solicitem.pc11_codigo left join matunid on matunid.m61_codmatunid = solicitemunid.pc17_unid where pc20_codorc={$codigo} order by pc22_orcamitem");
  $resultado = pg_fetch_all($sql);
  return $resultado;
}

$dados = buscaDados($_GET["codigo"]);

//testa($dados); die("Confere"); 


/*
foreach ($dados as $linha){  
    $data = implode("/", array_reverse(explode("-", $linha["pc23_dataref"])));
    $valor = number_format($linha["pc23_vlrun"], 4, ',', '');    
    //echo $valor;
    var_dump($valor);
    echo "<br>";       
  }
die("Confere");
*/
  
  header("Content-Type:   application/vnd.ms-excel; charset=utf-8");
  header("Content-type:   application/x-msexcel; charset=utf-8");
  header("Content-Disposition: attachment; filename=planilha_tcerj.xls");
  header("Expires: 0");
  header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
  header("Cache-Control: private",false);

  print "<table>";
  print "<thead>";
  print "<tr>";
  print "<td>Número Item</td>";
  print "<td>Fonte de Referência</td>";
  print "<td>Código de Referência</td>";
  print "<td>Data de Referência</td>";
  print "<td>Descrição</td>";
  print "<td>Quantidade</td>";
  print "<td>Unidade de Medida</td>";
  print "<td>Valor Unitário</td>";  
  print '</tr>';

  

  foreach ($dados as $linha){  
    $data = implode("/", array_reverse(explode("-", $linha["pc23_dataref"])));
    $valor = number_format($linha["pc23_vlrun"], 4, '.', ''); 
    //$valor = number_format($linha["pc23_vlrun"], 4, ',', ''); 
    //var_dump($valor);
    //testa($valor);

    print "<tr>";
      print "<td>" . $linha["pc11_seq"] . "</td>";
      print "<td>" . $linha["pc23_fonteref"] . "</td>";
      print "<td>" . $linha["pc23_codref"] . "</td>";
      print "<td>" . $data . "</td>";
      print "<td>" . $linha["pc01_descrmater"] . "</td>";
      print "<td>" . $linha["pc23_quant"] . "</td>";
      print "<td>" . strtolower($linha["m61_abrev"]) . "</td>";
      print "<td>" . $valor . "</td>";
    print "</tr>";

  }
  

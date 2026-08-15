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



$contagem = count($_POST) - 1;
$insere = array();

foreach ($_POST as $chave => $valor) {  
  $indice = explode("_", $chave);
  if(count($indice) > 1){
    $insere[$indice[2]][$indice[1]] = $valor;
  }
}

foreach ($insere as $chave => $valor){  
  if(!$valor["dataref"]){
    $sql = "UPDATE solicitem SET pc11_fonteref = '{$valor['fonteref']}', pc11_codref = '{$valor['codref']}', pc11_dataref = null WHERE pc11_codigo = {$chave}";
    //echo "UPDATE solicitem SET pc11_fonteref = '{$valor['fonteref']}', pc11_codref = '{$valor['codref']}' WHERE pc11_codigo = {$chave}"; echo "<br>";
  } else {
    $sql = "UPDATE solicitem SET pc11_fonteref = '{$valor['fonteref']}', pc11_codref = '{$valor['codref']}', pc11_dataref = '{$valor['dataref']}' WHERE pc11_codigo = {$chave}";
    //echo "UPDATE solicitem SET pc11_fonteref = '{$valor['fonteref']}', pc11_codref = '{$valor['codref']}', pc11_dataref = '{$valor['dataref']}' WHERE pc11_codigo = {$chave}"; echo "<br>";
  }
  pg_query($sql);
}

$nrosolicitacao = $_POST["nosolicitacao"];

function buscaDados($nrosolicitacao){
  $instituicao = db_getsession("DB_instit");
  $sql = pg_query("SELECT distinct solicitem.pc11_numero, solicitem.pc11_codigo, solicitem.pc11_quant, solicitem.pc11_seq, solicitem.pc11_fonteref, solicitem.pc11_codref, solicitem.pc11_dataref, matunid.m61_abrev, solicitem.pc11_vlrun as valor_antigo, case when pc11_vlrun = 0 then (select pcorcamval.pc23_vlrun as pc11_vlrun from solicitem s inner join pcorcamitemsol on s.pc11_codigo = pcorcamitemsol.pc29_solicitem inner join pcorcamitem on pcorcamitem.pc22_orcamitem = pcorcamitemsol.pc29_orcamitem inner join pcorcam on pcorcam.pc20_codorc = pcorcamitem.pc22_codorc inner join pcorcamval on pcorcamval.pc23_orcamitem = pcorcamitem.pc22_orcamitem inner join pcorcamforne on pcorcamforne.pc21_codorc = pcorcam.pc20_codorc and pcorcamforne.pc21_orcamforne = pcorcamval.pc23_orcamforne inner join pcorcamjulg on pcorcamjulg.pc24_orcamforne = pcorcamforne.pc21_orcamforne and pcorcamjulg.pc24_orcamitem = pcorcamitem.pc22_orcamitem and pcorcamjulg.pc24_pontuacao = 1 where s.pc11_codigo = solicitem.pc11_codigo) else pc11_vlrun end as pc11_vlrun, solicitem.pc11_resum, pcmater.pc01_codmater, pcmater.pc01_descrmater, pcmater.pc01_servico, solicitemunid.pc17_unid, solicitemunid.pc17_quant, matunid.m61_descr, matunid.m61_usaquant from solicitem inner join solicita on solicita.pc10_numero = solicitem.pc11_numero inner join db_depart on db_depart.coddepto = solicita.pc10_depto left join db_usuarios on solicita.pc10_login = db_usuarios.id_usuario left join solicitempcmater on solicitempcmater.pc16_solicitem = solicitem.pc11_codigo left join pcmater on pcmater.pc01_codmater = solicitempcmater.pc16_codmater left join pcprocitem on pcprocitem.pc81_solicitem = solicitem.pc11_codigo left join solicitemunid on solicitemunid.pc17_codigo = solicitem.pc11_codigo left join matunid on matunid.m61_codmatunid = solicitemunid.pc17_unid left join pcsubgrupo on pcsubgrupo.pc04_codsubgrupo = pcmater.pc01_codsubgrupo left join pctipo on pctipo.pc05_codtipo = pcsubgrupo.pc04_codtipo left join pcdotac on pcdotac.pc13_codigo = solicitem.pc11_codigo left join pcdotaccontrapartida on pcdotac.pc13_sequencial = pc19_pcdotac where 1=1 and pc11_numero = {$nrosolicitacao} and pc10_instit = {$instituicao} order by pc11_codigo");
    $resultado = pg_fetch_all($sql);
  return $resultado;
}


$dados = buscaDados($nrosolicitacao);

  
  header("Content-Type:   application/vnd.ms-excel; charset=utf-8");
  header("Content-type:   application/x-msexcel; charset=utf-8");  
  header("Content-Disposition: attachment; filename=AtosImportacaoItens.xls");
  header("Expires: 0");
  header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
  header("Cache-Control: private",false);

  print "<table>";  
  print "<tr>";
  print "<td>Número Item</td>";
  print "<td>Fonte de Referência</td>";
  print "<td>Código de Referência</td>";
  print "<td>Data de Referencia</td>";
  print "<td>Descrição</td>";
  print "<td>Quantidade</td>";
  print "<td>Unidade de Medida</td>";
  print "<td>Valor Unitário</td>";  
  print '</tr>';

  foreach ($dados as $linha){
    $item = $linha["pc11_seq"]  ;
    $material = ucfirst(mb_strtolower($linha["pc01_descrmater"]));
    //$unidade = (empty($linha["m61_descr"]) ? " " : $linha["m61_descr"]);
    $unidade = strtolower($linha["m61_abrev"]);
    $fonteref = $linha["pc11_fonteref"];
    $codref = $linha["pc11_codref"];
    $dataref = implode("/", array_reverse(explode("-", $linha["pc11_dataref"])));
    $quantidade = $linha["pc11_quant"];
    
    $vunitario = db_formatar($linha["pc11_vlrun"], "f");        
    $vunitario = str_replace(".", "", $vunitario);
    $vunitario = str_replace(",", ".", $vunitario);    

    $vtotal = db_formatar(($linha["pc11_vlrun"]*$linha["pc11_quant"]),"f");
    $vtotal = number_format($vtotal, 2, '.', '');

    if((isset($linha["pc01_servico"]) && (trim($linha["pc01_servico"])=="f" || trim($linha["pc01_servico"])=="")) || !isset($linha["pc01_servico"])){
          $referencia = trim(substr($linha["m61_descr"],0,10));
          if($linha["m61_usaquant"]=="t"){
            $referencia .= " ({$linha['pc17_quant']} UNIDADES)";
          }
        }else{
          $referencia = "SERVIÇO";
        }

    $codigo = $linha["pc01_codmater"];

    if(isset($linha["pc11_resum"]) && trim($linha["pc11_resum"])==""){
      $resumo = " ";
    } else {
      $resumo = substr(stripslashes($linha["pc11_resum"]),0,40);
    }
  
    //$valor = number_format($linha["pc23_vlrun"], 2, ',', '');

    print "<tr>";      
      //print "<td>" . $nrosolicitacao . "</td>";
      print "<td>" . $item . "</td>";
      print "<td>" . $fonteref . "</td>";
      print "<td>" . $codref . "</td>";
      print "<td>" . $dataref . "</td>";
      print "<td>" . $material . "</td>";
      print "<td>" . $quantidade . "</td>";
      print "<td>" . $unidade . "</td>";
      print "<td>" . $vunitario . "</td>";
      //print "<td>" . $vtotal . "</td>";
      //print "<td>" . $referencia . "</td>";
      //print "<td>" . $codigo . "</td>";
      //print "<td>" . $resumo . "</td>";      
    print "</tr>";
    
  }
  

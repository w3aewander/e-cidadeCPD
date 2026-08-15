<? 
//error_reporting(E_ALL); 
//ini_set('display_errors', '1');

require_once(modification("libs/db_stdlib.php"));
require_once(modification("libs/db_utils.php"));
require_once(modification("libs/db_conecta.php"));
require_once(modification("libs/db_sessoes.php"));
require_once(modification("libs/db_usuariosonline.php"));
require_once(modification("dbforms/db_funcoes.php"));
require_once(modification("libs/db_libdicionario.php"));
require_once(modification("libs/db_app.utils.php"));
require_once(modification("dbforms/db_classesgenericas.php"));


function testa($var){
  echo "<pre>";
  print_r($var);
  echo "</pre>";
}
//testa($_GET);
function buscaObs($c, $t, $p, $d){
	
  $sql = pg_query("SELECT obs FROM regocorr WHERE calendario = {$c} AND turma = {$t} AND periodo = {$p} and disciplina = {$d}");
  $resultado = pg_fetch_all($sql);
  return $resultado[0]["obs"];
}

function buscaObs2($c, $t, $p, $d){
	
  $sql = pg_query("SELECT obs2 FROM regocorr WHERE calendario = {$c} AND turma = {$t} AND periodo = {$p} and disciplina = {$d}");
  $resultado = pg_fetch_all($sql);
  return $resultado[0]["obs2"];
}
// alterar para buscar as 2 observações ao mesmo tempo
$obs  = buscaObs($_GET["xc"], $_GET["xt"], $_GET["xp"],$_GET["di"]);
$obs2 = buscaObs2($_GET["xc"], $_GET["xt"], $_GET["xp"],$_GET["di"]);

//ver como fazer para colocar 2 textarea em linhas diferentes
//aumentar aqui a quantidade de linhas, porque as observações deveram ser impressas em relatorio separados
//demanda 16794
?>
<td><b>Observações:</b></td>
<textarea name="xobs"  id="xobs"  cols="180" rows="5" maxlength="900" ><?=($obs) ? trim($obs) : ""?></textarea>
<textarea name="xobs2" id="xobs2" cols="180" rows="5" maxlength="900" ><?=($obs2) ? trim($obs2) : ""?></textarea>



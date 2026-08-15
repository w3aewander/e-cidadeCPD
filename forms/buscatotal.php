<?
require("libs/db_stdlib.php");
require("libs/db_conecta.php");
include("libs/db_sessoes.php");
include("libs/db_usuariosonline.php");
include("classes/db_benscorr_classe.php");
include("dbforms/db_funcoes.php");


function buscaTotal($ano){
  $sql = pg_query("SELECT sum(t63_agregarvalor) as soma FROM benscorr WHERE t63_exercicio = {$ano} AND t63_dataprocessamento IS NOT NULL");
  $resultado = pg_fetch_all($sql);
  return $resultado[0]["soma"];
}


$valortotal = buscaTotal($_GET["ano"]);


echo ($valortotal) ? $valortotal : 0;

//echo $valortotal;

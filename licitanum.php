<?php

require_once(modification("libs/db_stdlib.php"));
require_once(modification("libs/db_utils.php"));
require_once(modification("libs/db_conecta.php"));
require_once(modification("libs/db_sessoes.php"));
require_once(modification("libs/db_usuariosonline.php"));
require_once(modification("dbforms/db_funcoes.php"));
require_once(modification("libs/db_libdicionario.php"));
require_once(modification("libs/db_app.utils.php"));
require_once(modification("classes/db_placaixa_classe.php"));
require_once(modification("classes/db_placaixarec_classe.php"));
require_once(modification("dbforms/db_classesgenericas.php"));

function testa($var){
  echo "<pre>";
  print_r($var);
  echo "</pre>";
}

$ano = db_getsession("DB_anousu");
$tipo = $_GET["tipo"];



function retornaUltimoNumero($tipo, $ano){
  $sql = pg_query("SELECT max(l20_nopregao) FROM liclicita WHERE l20_codtipocom = {$tipo} AND l20_anousu = {$ano}");
  $resultado = pg_fetch_all($sql);
  return $resultado[0]["max"];
}


$pxnumero = retornaUltimoNumero($tipo, $ano);
$pxnumero = ($pxnumero) ? $pxnumero+1 : 1;

echo $pxnumero;
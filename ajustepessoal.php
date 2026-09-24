<?

require("libs/db_stdlib.php");
require("libs/db_conecta.php");
include("libs/db_sessoes.php");
include("libs/db_usuariosonline.php");
include("classes/db_empresto_classe.php");
include("dbforms/db_funcoes.php");


function testa($var){
  echo "<pre>";
  print_r($var);
  echo "</pre>";
}

function buscaEmpenhosAno(){
  $anof = db_getsession("DB_anousu");
  $sql = pg_query("SELECT e91_numemp from empresto inner join orctiporec on orctiporec.o15_codigo = empresto.e91_recurso inner join emprestotipo on emprestotipo.e90_codigo = empresto.e91_codtipo where empresto.e91_anousu = {$anof}");
  $resultado = pg_fetch_all($sql);
  return $resultado;
}


function verifica3319($empenho){
  $sql = pg_query("SELECT o56_elemento from empempenho inner join orcdotacao on orcdotacao.o58_anousu = empempenho.e60_anousu and orcdotacao.o58_coddot = empempenho.e60_coddot inner join orcelemento on orcelemento.o56_codele = orcdotacao.o58_codele and orcelemento.o56_anousu = orcdotacao.o58_anousu where empempenho.e60_numemp = {$empenho} AND o56_elemento like '3319%'");
  $resultado = pg_fetch_all($sql);
  return $resultado;
}


function verificaElementoAntigo(){
  $anof = db_getsession("DB_anousu");
  $sql = pg_query("SELECT * from empresto where e91_anousu = {$anof} and e91_elemento = ''");
  $resultado = pg_fetch_all($sql);
  return $resultado;
}

function contaElementos(){
  $anof = db_getsession("DB_anousu");
  $sql = pg_query("SELECT count(e91_numemp) from empempenho inner join orcdotacao on orcdotacao.o58_anousu = empempenho.e60_anousu and orcdotacao.o58_coddot = empempenho.e60_coddot inner join orcelemento on orcelemento.o56_codele = orcdotacao.o58_codele and orcelemento.o56_anousu = orcdotacao.o58_anousu INNER JOIN empresto ON e60_numemp = e91_numemp where o56_elemento like '3319%' AND e91_elemento = '2131101' AND e91_anousu = {$anof}");
  $resultado = pg_fetch_all($sql);
  return $resultado[0]["count"];
}

if($_POST){
  $empenhos = buscaEmpenhosAno();  
  $ano = db_getsession("DB_anousu");

foreach ($empenhos as $empenho){
  $confere3319 = verifica3319($empenho["e91_numemp"]);
  
  if($confere3319){
    pg_query("UPDATE empresto SET e91_elemento = '2111101', e91_codtipo = 2, e91_rpcorreto = 't' WHERE e91_anousu = {$ano} and e91_numemp = {$empenho['e91_numemp']}");
    //UPDATE empresto SET e91_elemento = '2111101', e91_codtipo = 2, e91_rpcorreto = 't' WHERE e91_anousu = {$ano} and e91_numemp = {$empenho["e91_numemp"]};
  }
}

$vazios = verificaElementoAntigo();

foreach ($vazios as $linha){
  pg_query("UPDATE empresto SET e91_elemento = '2131101', e91_codtipo = 1, e91_rpcorreto = 't' WHERE e91_anousu = {$ano} AND e91_numemp = {$linha['e91_numemp']}");
  //UPDATE empresto SET e91_elemento = '2131101', e91_codtipo = 1, e91_rpcorreto = 't' WHERE e91_anousu = {$ano}; AND e91_numemp = $linha["e91_numemp"];  
}
echo "<script>alert('Dados alterados.');</script>";
} else {
  $tempenhos = buscaEmpenhosAno();
  $tano = db_getsession("DB_anousu");
  
  $total3319 = contaElementos(); 

  $vazios = verificaElementoAntigo();

  
  $nrovazios = ($vazios) ? count($vazios) : 0;  
  
}



?>
<html>
<head>
<title>Microsist</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<meta http-equiv="Expires" CONTENT="0">
<script language="JavaScript" type="text/javascript" src="scripts/scripts.js"></script>
<link href="estilos.css" rel="stylesheet" type="text/css">
</head>
<body bgcolor=#CCCCCC leftmargin="0" topmargin="0" marginwidth="0" marginheight="0" onLoad="a=1" >


      <div style="width: 300px; margin: 0 auto;margin-top: 20px">
      <fieldset>
        <legend>Dados:</legend>
        <ul style="list-style: none">
          <li><b>Ano: </b><?=db_getsession("DB_anousu");?></li>
          <li><b>Registros Incorretos: </b><?=($total3319) ? $total3319 : 0;?></li>
          <li><b>Registros sem Elemento Antigo: </b><?=($nrovazios) ? $nrovazios : 0?></li>
        </ul>
      </fieldset>

      <form method="post" action="">
        <input type="submit" value="Corrigir" name="formulario">
      </form>
      </div>
	
    
<?
db_menu(db_getsession("DB_id_usuario"),db_getsession("DB_modulo"),db_getsession("DB_anousu"),db_getsession("DB_instit"));
?>
</body>
</html>

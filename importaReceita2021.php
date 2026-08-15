<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

function testa($var){
  echo "<pre>";
  print_r($var);
  echo "</pre>";
}

function conectaLoa(){
    try {
      $pdo = new PDO("pgsql:dbname='voltaredonda'; host='10.1.0.51'; user='postgres'; password=''; port='6432'"); 
      $pdo->exec( "select fc_startsession();" );
      $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
      return $pdo;
    } catch (PDOException $e){
      echo "Erro: " . $e->getMessage();
    }
}


function buscaReduzidos($pdo, $instituicao){
    try {
      $stmt = $pdo->prepare("SELECT orcreceita.o70_codrec,fc_estruturalreceita(2021,orcreceita.o70_codrec) as o50_estrutreceita,o57_descr,c58_descr as DL_Caracteristica_Peculiar,orcreceita.o70_codigo,o15_descr,orcreceita.o70_valor,orcreceita.o70_reclan,nomeinst,orcreceita.o70_anousu as db_o70_anousu,o57_fonte as db_o57_fonte,o57_descr as db_o57_descr from orcreceita inner join db_config on db_config.codigo = orcreceita.o70_instit inner join orctiporec on orctiporec.o15_codigo = orcreceita.o70_codigo inner join orcfontes on orcfontes.o57_codfon = orcreceita.o70_codfon and orcfontes.o57_anousu = orcreceita.o70_anousu inner join concarpeculiar on concarpeculiar.c58_sequencial = orcreceita.o70_concarpeculiar inner join cgm on cgm.z01_numcgm = db_config.numcgm inner join db_tipoinstit on db_tipoinstit.db21_codtipo = db_config.db21_tipoinstit where o70_anousu = 2021 and o70_instit = :instituicao and 1=1 order by o70_anousu,o70_codrec");
      $stmt->execute(array(
        "instituicao" => $instituicao
      ));
      $resultado = $stmt->fetchAll(PDO::FETCH_OBJ);
      return $resultado ? $resultado : false;
    } catch (PDOException $e){
      echo $e->getMessage();
    }
  }


function buscaDadosPorReduzido($pdo, $reduzido){
    try {
      $stmt = $pdo->prepare("SELECT o70_anousu, o70_codrec, o70_codfon, o70_codigo, o70_valor, o70_reclan, o70_instit, o70_concarpeculiar, o70_datacriacao FROM orcreceita WHERE o70_anousu = 2021 AND o70_codrec = :reduzido;");
      $stmt->execute(array(
        "reduzido" => $reduzido
      ));
      $resultado = $stmt->fetchAll(PDO::FETCH_OBJ);
      return $resultado ? $resultado : false;
    } catch (PDOException $e){
      echo $e->getMessage();
    }
  }



function conectaHomolog(){
    try {
      $pdo = new PDO("pgsql:dbname='voltaredonda'; host='10.1.0.51'; user='postgres'; password=''; port='6432'"); 
      $pdo->exec( "select fc_startsession();" );
      $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
      return $pdo;
    } catch (PDOException $e){
      echo "Erro: " . $e->getMessage();
    }
}

function insereHomolog($pdo, $dados){  
  try{    
    $stmt = $pdo->prepare("INSERT INTO orcreceita(o70_anousu, o70_codrec, o70_codfon, o70_codigo, o70_valor, o70_reclan, o70_instit, o70_concarpeculiar) VALUES(:o70_anousu, :o70_codrec, :o70_codfon, :o70_codigo, :o70_valor, :o70_reclan, :o70_instit, :o70_concarpeculiar)");
    $stmt->execute(array(
      "o70_anousu" => 2021,
      "o70_codrec" => $dados->o70_codrec,
      "o70_codfon" => $dados->o70_codfon,
      "o70_codigo" => $dados->o70_codigo,
      "o70_valor" => $dados->o70_valor,
      "o70_reclan" => ($dados->o70_reclan == 1 ? 'true' : 'false'),
      "o70_instit" => $dados->o70_instit,
      "o70_concarpeculiar" => $dados->o70_concarpeculiar
      ));
    
    return ($stmt->rowCount() > 0) ? $stmt : false;
  } catch(PDOExpcetion $e){
    echo "Erro: " . $e->getMessage();
  }
}

$pdoLoa = conectaLoa();
$pdoHomolog = conectaHomolog();

if(!$pdoLoa){
   die("Não foi possível estabelecer uma conexão com o banco de dados.");
};

if(!$pdoHomolog){
   die("Não foi possível estabelecer uma conexão com o banco de dados.");
};

$todasinstituicoes = array("60", "20", "25", "35", "65", "75", "80", "90", "85", "45", "96", "50", "1");

foreach($todasinstituicoes as $numinst){

  $inst = $numinst;
  $reduzidos = buscaReduzidos($pdoLoa, $inst);
  $guardadados = array();


  foreach ($reduzidos as $reduzido) {
    $dr = buscaDadosPorReduzido($pdoLoa, $reduzido->o70_codrec);
    array_push($guardadados, $dr[0]);
  }

  echo "Dados salvos. Inserindo agora.";
  echo "<br>";

  foreach ($guardadados as $dados){
    if(insereHomolog($pdoHomolog, $dados)){
      echo "Inserindo " . $dados->o70_codrec;
      echo "<br>";
    } else {
      echo "Erro no " . $dados->o70_codrec;
      echo "<br>";
    }
  }
  echo "----------------------------------------------------------------";
  echo "<br>";
}

echo "Fim";

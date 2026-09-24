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
      $stmt = $pdo->prepare("SELECT fc_estruturaldotacao(2021,o58_coddot) as dl_estrutural, o56_elemento, o55_descr::text, o56_descr, o58_coddot, o58_instit from orcdotacao d inner join orcprojativ p on p.o55_anousu = 2021 and p.o55_projativ = d.o58_projativ inner join orcelemento e on e.o56_codele = d.o58_codele and o56_anousu = o58_anousu where o58_instit=:instituicao and o58_anousu=2021 order by dl_estrutural");
      $stmt->execute(array(
        "instituicao" => $instituicao
      ));
      $resultado = $stmt->fetchAll(PDO::FETCH_OBJ);
      return $resultado ? $resultado : false;
    } catch (PDOException $e){
      echo $e->getMessage();
    }
  }


function buscaDadosPorReduzido($pdo, $reduzido, $instituicao){
    try {
      $stmt = $pdo->prepare("SELECT o58_coddot, o58_instit, o58_orgao, o58_unidade, o58_funcao, o58_subfuncao, o58_programa, o58_projativ, o56_elemento, o58_codigo, o58_localizadorgastos, o58_concarpeculiar, o56_codele, o58_valor FROM orcdotacao INNER JOIN orcelemento ON orcelemento.o56_codele = orcdotacao.o58_codele WHERE o58_coddot = :reduzido AND o58_instit = :instituicao AND o56_anousu = 2021;");
      $stmt->execute(array(
        "reduzido" => $reduzido,
        "instituicao" => $instituicao
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
    $stmt = $pdo->prepare("INSERT INTO orcdotacao(o58_anousu, o58_coddot, o58_orgao, o58_unidade, o58_subfuncao, o58_projativ, o58_codigo, o58_funcao, o58_programa, o58_codele, o58_valor, o58_instit, o58_localizadorgastos, o58_concarpeculiar) VALUES(:o58_anousu, :o58_coddot, :o58_orgao, :o58_unidade, :o58_subfuncao, :o58_projativ, :o58_codigo, :o58_funcao, :o58_programa, :o58_codele, :o58_valor, :o58_instit, :o58_localizadorgastos, :o58_concarpeculiar)");
    $stmt->execute(array(
      "o58_anousu" => 2021,
      "o58_coddot" => $dados->o58_coddot,
      "o58_orgao" => $dados->o58_orgao,
      "o58_unidade" => $dados->o58_unidade,
      "o58_subfuncao" => $dados->o58_subfuncao,
      "o58_projativ" => $dados->o58_projativ,
      "o58_codigo" => $dados->o58_codigo,
      "o58_funcao" => $dados->o58_funcao,
      "o58_programa" => $dados->o58_programa,
      "o58_codele" => $dados->o56_codele,
      "o58_valor" => $dados->o58_valor,
      "o58_instit" => $dados->o58_instit,
      "o58_localizadorgastos" => $dados->o58_localizadorgastos,
      "o58_concarpeculiar" => $dados->o58_concarpeculiar
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


$todasinstituicoes = array("60", "20", "25", "30", "35", "55", "65", "75", "80", "90", "91", "85", "45", "96", "50", "70", "1");


foreach($todasinstituicoes as $numinst){

  $inst = $numinst;  
  $reduzidos = buscaReduzidos($pdoLoa, $inst);
  $guardadados = array();

  foreach ($reduzidos as $reduzido) {
    $dr = buscaDadosPorReduzido($pdoLoa, $reduzido->o58_coddot, $inst);
    array_push($guardadados, $dr[0]);
  }



  echo utf8_decode("Instituição {$inst}. Dados salvos. Inserindo agora.");
  echo "<br>";


  foreach ($guardadados as $dados){
    if(insereHomolog($pdoHomolog, $dados)){
      echo "Inserindo " . $dados->o58_coddot;
      echo "<br>";
    } else {
      echo "Erro no " . $dados->o58_coddot;
      echo "<br>";
    }  
  }
  echo "----------------------------------------------------------------";
  echo "<br>";
}

echo "Fim";

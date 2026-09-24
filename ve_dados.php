<?php
error_reporting(E_ALL); 
ini_set('display_errors', '1');

set_time_limit(0);
ini_set('memory_limit', '1G');


function conecta(){  
    try {      
      $pdo = new PDO("pgsql:dbname='dbpadrao'; host='127.0.0.1'; user='ecidade'; password='EC1d@d3CPD!2026%#'; port='5432'"); 
      $pdo->exec( "select fc_startsession();" );                                                                       
      $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);                                                   
      return $pdo;                                                                                                     
    } catch (PDOException $e){
      echo "Erro: " . $e->getMessage();
    }
}

$pdo = conecta();
if(!$pdo) die ("Não foi possível conectar ao banco. Tente novamente.");

function testa($var){
  echo "<pre>";
  print_r($var);
  echo "</pre>";
}

//INSTITUIÇÕES
//1, 20, 25, 30, 35, 40 45, 50, 55, 60, 65, 70, 75, 80, 85, 90, 95, 96

function veTudo($pdo){
    try {
      $stmt = $pdo->prepare("SELECT z01_numcgm, z01_nome, z01_login, z01_email, z01_cgccpf FROM cgm WHERE z01_numcgm >= 3 ORDER BY z01_numcgm");
      $stmt->execute();
      $resultado = $stmt->fetchAll(PDO::FETCH_OBJ);
      return $resultado ? $resultado : false;
    } catch (PDOException $e){
      echo $e->getMessage();
    }
}

function gerarCPF() {
    $n = [];
    for ($i = 0; $i < 9; $i++) {
        $n[] = rand(0, 9);
    }

    $soma = 0;
    for ($i = 0; $i < 9; $i++) {
        $soma += $n[$i] * (10 - $i);
    }
    $resto = $soma % 11;
    $d1 = ($resto < 2) ? 0 : 11 - $resto;
    $n[] = $d1;

    $soma = 0;
    for ($i = 0; $i < 10; $i++) {
        $soma += $n[$i] * (11 - $i);
    }
    $resto = $soma % 11;
    $d2 = ($resto < 2) ? 0 : 11 - $resto;
    $n[] = $d2;

    return implode('', $n);
}

function gerarCNPJ() {
    $n = [];
    for ($i = 0; $i < 12; $i++) {
        $n[] = rand(0, 9);
    }

    $pesos1 = [5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
    $soma = 0;
    for ($i = 0; $i < 12; $i++) {
        $soma += $n[$i] * $pesos1[$i];
    }
    $resto = $soma % 11;
    $d1 = ($resto < 2) ? 0 : 11 - $resto;
    $n[] = $d1;

    $pesos2 = [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
    $soma = 0;
    for ($i = 0; $i < 13; $i++) {
        $soma += $n[$i] * $pesos2[$i];
    }
    $resto = $soma % 11;
    $d2 = ($resto < 2) ? 0 : 11 - $resto;
    $n[] = $d2;

    return implode('', $n);
}


$dados = veTudo($pdo);
$contadorcpf = 2;
$contadorcnpj = 2;
$contadorentidade = 2;
foreach ($dados as $linha) {
  if(strlen($linha->z01_cgccpf) == 11){
    $z01_cgccpf = gerarCPF();
    $nome = "PESSOA " . $contadorcpf;
    $contadorcpf++;
  }elseif(strlen($linha->z01_cgccpf) == 14){
    $z01_cgccpf = gerarCNPJ();
    $nome = "EMPRESA " . $contadorcnpj;
    $contadorcnpj++;
  }else{
    $z01_cgccpf = $linha->z01_cgccpf;
    $nome = "ENTIDADE " . $contadorentidade;
    $contadorentidade++;
  }
  echo "UPDATE cgm SET z01_nome = '{$nome}', z01_cgccpf = '{$z01_cgccpf}' WHERE z01_numcgm = {$linha->z01_numcgm};"; echo "<br>";
}




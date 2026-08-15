<?
require_once("libs/db_stdlib.php");
require_once("libs/db_conecta.php");
require_once("libs/db_sessoes.php");
require_once("libs/db_utils.php");
require_once("libs/db_usuariosonline.php");
require_once("classes/db_tabrec_classe.php");
require_once("classes/db_tabrectipo_classe.php");
require_once("classes/db_tabrecregrasjm_classe.php");
require_once("classes/db_taborc_classe.php");
require_once("classes/db_tabplan_classe.php");
require_once("classes/db_numpref_classe.php");
require_once("dbforms/db_funcoes.php");
require_once("classes/db_tabrecarretipo_classe.php");

//var_dump($_POST);

$instit = db_getsession("DB_instit");

function testa($var){
  echo "<pre>";
  print_r($var);
  echo "</pre>";
}


function buscaReceita2021($instit){
      $anoatual = Date("Y");
      //$sql = pg_query("SELECT * from orcreceita where o70_anousu = 2022 and o70_instit = {$instit}");
      $sql = pg_query("SELECT * from orcreceita where o70_anousu = {$anoatual} and o70_instit = {$instit}");
      $resultado = pg_fetch_all($sql);
      return $resultado;
  }


function buscaEstrut($codfon){
  $anoatual = Date("Y");
  $sql = pg_query("SELECT o57_fonte FROM orcfontes WHERE o57_codfon = {$codfon} AND o57_anousu = {$anoatual}");
  //$sql = pg_query("SELECT o57_fonte FROM orcfontes WHERE o57_codfon = {$codfon} AND o57_anousu = 2022");
  $resultado = pg_fetch_all($sql);
  return $resultado[0]["o57_fonte"];    
}

  function buscaTaborc($estrut){
    $anopassado = Date("Y") - 1;
    //$sql = pg_query("SELECT * FROM taborc WHERE k02_estorc = '{$estrut}' AND k02_anousu = 2021");
    $sql = pg_query("SELECT * FROM taborc WHERE k02_estorc = '{$estrut}' AND k02_anousu = {$anopassado}");
    $resultado = pg_fetch_all($sql);
    return $resultado;
  }

  function proximocodigo(){
    $sql = pg_query("SELECT max(k02_codigo) FROM taborc");
    $resultado = pg_fetch_all($sql);
    return $resultado[0]["max"] + 1;
  }


function verificaTaborc2021($codigo, $codrec){  
  $anoatual = Date("Y");
  //$sql = pg_query("SELECT * FROM taborc WHERE k02_codigo = {$codigo} AND k02_codrec = {$codrec} AND k02_anousu = 2022");
  $sql = pg_query("SELECT * FROM taborc WHERE k02_codigo = {$codigo} AND k02_codrec = {$codrec} AND k02_anousu = {$anoatual}");
  $resultado = pg_fetch_all($sql);
  return $resultado;
}

function buscaDescricao($estrut){
  $anoatual = Date("Y");
  //$sql = pg_query("SELECT o57_descr FROM orcfontes WHERE o57_anousu = 2022 AND o57_fonte = '{$estrut}'");
  $sql = pg_query("SELECT o57_descr FROM orcfontes WHERE o57_anousu = {$anoatual} AND o57_fonte = '{$estrut}'");
  $resultado = pg_fetch_all($sql);
  return $resultado[0]["o57_descr"];
}

function buscaSeqRegras(){
  $sql = pg_query("SELECT max(k04_sequencial) FROM tabrecregrasjm");
  $resultado = pg_fetch_all($sql);
  return $resultado[0]["max"] + 1;
}

function buscaSeqAr(){
  $sql = pg_query("SELECT max(k79_sequencial) FROM tabrecarretipo");
  $resultado = pg_fetch_all($sql);
  return $resultado[0]["max"] + 1;
}

$receitas21 = buscaReceita2021($instit);
//testa($receitas21); die("Verifica");


$inst = (int)db_getsession("DB_instit");
$ano = Date("Y");





foreach($receitas21 as $receita){
  $estrutural = buscaEstrut($receita["o70_codfon"]);    
  $taborc = buscaTaborc($estrutural);


  if($taborc){    
    foreach($taborc as $linha){
      $dados["k02_codigo"] = $linha["k02_codigo"];
      //$dados["k02_anousu"] = 2022;
      $dados["k02_anousu"] = Date("Y");
      $dados["k02_codrec"] = $receita["o70_codrec"];
      $dados["k02_estorc"] = $linha["k02_estorc"];
      
      $vt21 = verificaTaborc2021((int)$dados["k02_codigo"], (int)$dados["k02_codrec"]);

      if(!$vt21){
        pg_query("INSERT INTO taborc(k02_codigo, k02_anousu, k02_codrec,k02_estorc) VALUES(".(int)$dados['k02_codigo'].", {$ano}, ".(int)$dados['k02_codrec'].", '".$dados['k02_estorc']."')");
        //pg_query("INSERT INTO taborc(k02_codigo, k02_anousu, k02_codrec,k02_estorc) VALUES(".(int)$dados['k02_codigo'].", 2022, ".(int)$dados['k02_codrec'].", '".$dados['k02_estorc']."')");
      
        //echo "INSERT INTO taborc(k02_codigo, k02_anousu, k02_codrec,k02_estorc) VALUES(".(int)$dados['k02_codigo'].", 2021, ".(int)$dados['k02_codrec'].", '".$dados['k02_estorc']."')";
        //echo "<br>";
      }
      
      //insere tabrec
      $descricao = buscaDescricao($dados["k02_estorc"]);
      $descr = substr($descricao, 0, 15);
      $drecei = substr($descricao, 0, 40);

      if ($inst == 1) {
        $codjuro = 61;
      } elseif ($inst == 20) {
        $codjuro = 84;
      } elseif ($inst == 25) {
        $codjuro = 70;
      } elseif ($inst == 30) {
        $codjuro = 85;
      } elseif ($inst == 35) {
        $codjuro = 80;
      } elseif ($inst == 45) {
        $codjuro = 83;
      } elseif ($inst == 50) {
        $codjuro = 86;
      } elseif ($inst == 55) {
        $codjuro = 64;
      } elseif ($inst == 60) {
        $codjuro = 87;
      } elseif ($inst == 65) {
        $codjuro = 81;
      } elseif ($inst == 75) {
        $codjuro = 89;
      } elseif ($inst == 80) {
        $codjuro = 90;
      } elseif ($inst == 85) {
        $codjuro = 91;
      } elseif ($inst == 90) {
        $codjuro = 93;
      } elseif ($inst == 96) {
        $codjuro = 95;
      }

      
      pg_query("INSERT INTO tabrec(k02_codigo ,k02_tipo ,k02_descr ,k02_drecei ,k02_codjm ,k02_recjur ,k02_recmul ,k02_limite ,k02_tabrectipo) VALUES(".(int)$dados['k02_codrec'].", 'O', '".$descr."', '".$drecei."', ".$codjuro.", 999, 999, null, 1 )");        
      //echo "INSERT INTO tabrec(k02_codigo ,k02_tipo ,k02_descr ,k02_drecei ,k02_codjm ,k02_recjur ,k02_recmul ,k02_limite ,k02_tabrectipo)";
      //var_dump((int)$dados['k02_codrec']);
      
      //echo "INSERT INTO tabrec(k02_codigo ,k02_tipo ,k02_descr ,k02_drecei ,k02_codjm ,k02_recjur ,k02_recmul ,k02_limite ,k02_tabrectipo) VALUES(".(int)$dados['k02_codrec'].", 'O', '".$descr."', '".$drecei."', 83, 999, 999, 'null', 1 )";
      //echo "<br>";
      
      

      //insere tabrecregrasjm
      $sequencial = buscaSeqRegras();
      
      pg_query("INSERT INTO tabrecregrasjm(k04_sequencial ,k04_receit ,k04_codjm ,k04_dtini ,k04_dtfim) VALUES(".$sequencial.", ".(int)$dados['k02_codigo'].", 83, '1900-01-01', '2099-12-31')");
      //echo "INSERT INTO tabrecregrasjm(k04_sequencial ,k04_receit ,k04_codjm ,k04_dtini ,k04_dtfim) VALUES(".$sequencial.", ".(int)$dados['k02_codigo'].", 83, '1900-01-01', '2099-12-31')";
      //echo "<br>";


      //insere tabrecarretipo
      //$sequencialar = buscaSeqAr();
      $arretipo = 39;
      pg_query("INSERT INTO tabrecarretipo(k79_sequencial, k79_arretipo, k79_receit) VALUES(".$sequencialar.", ".$arretipo.", ".(int)$dados['k02_codigo']." )");
      //echo "INSERT INTO tabrecarretipo(k79_sequencial, k79_arretipo, k79_receit) VALUES(".$sequencialar.", ".$arretipo.", ".(int)$dados['k02_codigo']." )";
      //echo "<br>";

      
    }//foreach interno
  }else{
    $novocodigo = proximocodigo();
    $dados["k02_codigo"] = $novocodigo;
    //$dados["k02_anousu"] = 2022;
    $dados["k02_anousu"] = Date("Y");
    $dados["k02_codrec"] = $receita["o70_codrec"];
    $dados["k02_estorc"] = $estrutural;

    $vt21 = verificaTaborc2021((int)$dados["k02_codigo"], (int)$dados["k02_codrec"]);

    if(!$vt21){        
      pg_query("INSERT INTO taborc(k02_codigo, k02_anousu, k02_codrec,k02_estorc) VALUES(".(int)$dados['k02_codigo'].", {$ano}, ".(int)$dados['k02_codrec'].", '".$dados['k02_estorc']."')");
      //pg_query("INSERT INTO taborc(k02_codigo, k02_anousu, k02_codrec,k02_estorc) VALUES(".(int)$dados['k02_codigo'].", 2022, ".(int)$dados['k02_codrec'].", '".$dados['k02_estorc']."')");
      
        //echo "INSERT INTO taborc(k02_codigo, k02_anousu, k02_codrec,k02_estorc) VALUES(".(int)$dados['k02_codigo'].", 2021, ".(int)$dados['k02_codrec'].", '".$dados['k02_estorc']."')";
        //echo "<br>";
      } 
      
      //insere tabrec
      $descricao = buscaDescricao($dados["k02_estorc"]);
      $descr = substr($descricao, 0, 15);
      $drecei = substr($descricao, 0, 40);

      
      if ($inst == 1) {
        $codjuro = 61;
      } elseif ($inst == 20) {
        $codjuro = 84;
      } elseif ($inst == 25) {
        $codjuro = 70;
      } elseif ($inst == 30) {
        $codjuro = 85;
      } elseif ($inst == 35) {
        $codjuro = 80;
      } elseif ($inst == 45) {
        $codjuro = 83;
      } elseif ($inst == 50) {
        $codjuro = 86;
      } elseif ($inst == 55) {
        $codjuro = 64;
      } elseif ($inst == 60) {
        $codjuro = 87;
      } elseif ($inst == 65) {
        $codjuro = 81;
      } elseif ($inst == 75) {
        $codjuro = 89;
      } elseif ($inst == 80) {
        $codjuro = 90;
      } elseif ($inst == 85) {
        $codjuro = 91;
      } elseif ($inst == 90) {
        $codjuro = 93;
      } elseif ($inst == 96) {
        $codjuro = 95;
      }
      
      pg_query("INSERT INTO tabrec(k02_codigo ,k02_tipo ,k02_descr ,k02_drecei ,k02_codjm ,k02_recjur ,k02_recmul ,k02_limite ,k02_tabrectipo) VALUES(".(int)$dados['k02_codrec'].", 'O', '".$descr."', '".$drecei."', ".$codjuro.", 999, 999, null, 1 )");        
      //echo "INSERT INTO tabrec(k02_codigo ,k02_tipo ,k02_descr ,k02_drecei ,k02_codjm ,k02_recjur ,k02_recmul ,k02_limite ,k02_tabrectipo)";
      //var_dump((int)$dados['k02_codrec']);
      
      //echo "INSERT INTO tabrec(k02_codigo ,k02_tipo ,k02_descr ,k02_drecei ,k02_codjm ,k02_recjur ,k02_recmul ,k02_limite ,k02_tabrectipo) VALUES(".(int)$dados['k02_codrec'].", 'O', '".$descr."', '".$drecei."', 83, 999, 999, 'null', 1 )";
      //echo "<br>";
      
      

      //insere tabrecregrasjm
      $sequencial = buscaSeqRegras();
      
      pg_query("INSERT INTO tabrecregrasjm(k04_sequencial ,k04_receit ,k04_codjm ,k04_dtini ,k04_dtfim) VALUES(".$sequencial.", ".(int)$dados['k02_codigo'].", 83, '1900-01-01', '2099-12-31')");
      //echo "INSERT INTO tabrecregrasjm(k04_sequencial ,k04_receit ,k04_codjm ,k04_dtini ,k04_dtfim) VALUES(".$sequencial.", ".(int)$dados['k02_codigo'].", 83, '1900-01-01', '2099-12-31')";
      //echo "<br>";


      //insere tabrecarretipo
      //$sequencialar = buscaSeqAr();
      $arretipo = 39;
            
      pg_query("INSERT INTO tabrecarretipo(k79_sequencial, k79_arretipo, k79_receit) VALUES(".$sequencialar.", ".$arretipo.", ".(int)$dados['k02_codigo']." )");
      //echo "INSERT INTO tabrecarretipo(k79_sequencial, k79_arretipo, k79_receit) VALUES(".$sequencialar.", ".$arretipo.", ".(int)$dados['k02_codigo']." )";
      //echo "<br>";
  }//else
  
}//foreach

//pg_query("SELECT setval('tabrec_k02_codigo_seq', (SELECT max(k02_codigo) + 1 FROM tabrec), true)");
//pg_query("SELECT setval('tabrecregrasjm_k04_sequencial_seq', (select max(k04_sequencial) + 1 from tabrecregrasjm), true)");
//pg_query("SELECT setval('orcreceita_o70_codrec_seq', (select max(o70_codrec) + 1 from orcreceita), true)");

pg_query("INSERT INTO confereinclusaoreceita(instituicao, ano) VALUES({$inst}, {$ano})");





header('Location: cai1_receita004.php');

exit();

?>
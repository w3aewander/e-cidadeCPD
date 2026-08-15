<?php
 
require_once(modification("libs/db_stdlib.php"));
require_once(modification("libs/db_conecta.php"));
require_once(modification("libs/db_sessoes.php"));
require_once(modification("libs/db_usuariosonline.php"));
require_once(modification("libs/db_liborcamento.php"));
require_once(modification("dbforms/db_funcoes.php"));
require_once(modification("classes/db_conplanoorcamentoanalitica_classe.php"));
require_once(modification("classes/db_orcreceita_classe.php"));
require_once(modification("classes/db_orcparametro_classe.php"));
require_once(modification("classes/db_orcfontes_classe.php"));
require_once(modification("classes/db_orcfontesdes_classe.php"));
require_once(modification("classes/db_concarpeculiar_classe.php"));

db_postmemory($_POST);
$iAnoUsu  = db_getsession("DB_anousu");
$anonovo = $iAnoUsu;

if($_GET["t"] == 1){
  echo "<script>alert('Inclusão realizada.');</script>";
}

if($_GET["t"] == 2){
  pg_query("DELETE FROM orcreceita WHERE o70_anousu = {$anonovo} and o70_instit = {$bins}");
  echo "<script>alert('Houve um erro. Contate o suporte.');</script>";
}

if($_GET["e"] == 1){
  echo "<script>alert('Registro excluído com sucesso.');</script>";
}

/*
if($_GET["t"] == 1 || $_GET["t"] == 2 || $_GET["e"] == 1){
  header("Location:preorcrece.php");
}*/

function testa($var){
  echo "<pre>";
  print_r($var);
  echo "</pre>";
}

function retornaProximoId(){
  $sql = pg_query("SELECT max(id)+1 as idtipo FROM previareceitas");
  $resultado = pg_fetch_all($sql);
  return $resultado[0]["idtipo"];
}

function retornaUltimoId(){
  $sql = pg_query("SELECT max(id) as ultimo FROM previareceitas");
  $resultado = pg_fetch_all($sql);
  return $resultado[0]["ultimo"];
}

$clconplanoorcamentoanalitica = new cl_conplanoorcamentoanalitica;
$clorcreceita                 = new cl_orcreceita;
$clorcfontes                  = new cl_orcfontes;
$clorcfontesdes               = new cl_orcfontesdes;
$clorcparametro               = new cl_orcparametro;
$clestrutura                  = new cl_estrutura;
$clconcarpeculiar             = new cl_concarpeculiar;


$db_opcao = 1;
$db_botao = true;


$instituicoes = pg_query("SELECT DISTINCT o70_instit FROM previareceitas order by o70_instit");
$instituicoes = pg_fetch_all($instituicoes);

if ((isset ($HTTP_POST_VARS["i2021"]) && $HTTP_POST_VARS["i2021"]) == "Incluir {$anonovo}"){
  $bins = db_getsession("DB_instit");
  $tudo = pg_query("SELECT * FROM previareceitas WHERE o70_instit = '$bins' AND o70_anousu = '{$anonovo}' ORDER BY id");  
  $resultudo = pg_fetch_all($tudo);
  //testa($resultudo); die("Prévia Receitas");

  $errofonte = array();
  $erroreceita = array();

  pg_query("DELETE FROM orcreceita WHERE o70_anousu = {$anonovo} and o70_instit = {$bins}");
  foreach($resultudo as $linha){  
    $o70_anousu = $linha["o70_anousu"];
    $o70_codrec = $linha["o70_codrec"];
    //$o50_estrutreceita = $linha["o50_estrutreceita"];    
    $o50_estrutreceita = implode("", explode(".", $linha["o50_estrutreceita"]));
    $o57_descr = $linha["o57_descr"];
    $o70_codigo = trim($linha["o70_codigo"]);
    $o15_descr = $linha["o15_descr"];    
    $o70_valor = ($linha["o70_valor"]) ? $linha["o70_valor"] : 0;
    if(!floatval($o70_valor)){
      $o70_valor = 0;
    }
    $o70_reclan = $linha["o70_reclan"];
    $o70_concarpeculiar = trim($linha["o70_concarpeculiar"]);
    $c58_descr = $linha["c58_descr"];    
    $o70_instit = $linha["o70_instit"];

    $o70_orcorgao = $linha["o70_orcorgao"];
    $o70_orcunidade = $linha["o70_orcunidade"];
    $o70_esferaorcamentaria = $linha["o70_esferaorcamentaria"];

    $id = $linha["id"];
    
    //Tem que voltar o valor pro codfon. Se não tiver, guarda a o57_fonte e pular
    $sqlverificafonte = pg_query("SELECT o57_codfon FROM orcfontes WHERE o57_fonte='$o50_estrutreceita' AND o57_anousu = '$o70_anousu'");
    $verificafonte = pg_fetch_all($sqlverificafonte);
    if($verificafonte){      
      $codfon = $verificafonte[0]["o57_codfon"]; 
    } else {      
      array_push($errofonte, $o50_estrutreceita);
      continue;
    }
        
    //Tem que voltar vazio. Se voltar resultado, guardar codfon e pular
    $sqlverificareceita = pg_query("SELECT * FROM orcreceita WHERE o70_anousu = '$o70_anousu' AND o70_codfon = '$codfon' AND o70_concarpeculiar = '000' AND o70_instit = {$o70_instit} ORDER BY o70_instit");
    $verificareceita = pg_fetch_all($sqlverificareceita);
    /*if($verificareceita){
      array_push($erroreceita, $codfon);
      continue;
    }*/

    $sqlo70_codrec = pg_query("select nextval('orcreceita_o70_codrec_seq')");
    $o70_codrec = pg_fetch_all($sqlo70_codrec);
    $o70_codrec = $o70_codrec[0]["nextval"];
    
    $inserereceita = pg_query($conn, "INSERT INTO orcreceita(o70_anousu ,o70_codrec ,o70_codfon ,o70_codigo ,o70_valor ,o70_reclan ,o70_instit ,o70_concarpeculiar ,o70_datacriacao, o70_orcorgao, o70_orcunidade, o70_esferaorcamentaria) VALUES ({$o70_anousu}, {$o70_codrec}, {$codfon}, {$o70_codigo}, {$o70_valor}, '$o70_reclan', {$o70_instit}, '$o70_concarpeculiar', null, {$o70_orcorgao}, {$o70_orcunidade}, {$o70_esferaorcamentaria})");

  
    if(!$inserereceita){
      //var_dump("INSERT INTO orcreceita(o70_anousu ,o70_codrec ,o70_codfon ,o70_codigo ,o70_valor ,o70_reclan ,o70_instit ,o70_concarpeculiar ,o70_datacriacao) VALUES ({$o70_anousu}, {$o70_codrec}, {$codfon}, {$o70_codigo}, {$o70_valor}, '$o70_reclan', {$o70_instit}, '$o70_concarpeculiar', null)");
      //var_dump($id);
      //die("Erro 0");
      header("Location:preorcrece.php?t=2");
      $error = pg_last_error($conn);
      //var_dump($error);     
      //echo "<br>";
      //var_dump($id);
      //echo "<br>";
      //$sqlinsere = "INSERT INTO orcreceita(o70_anousu ,o70_codrec ,o70_codfon ,o70_codigo ,o70_valor ,o70_reclan ,o70_instit ,o70_concarpeculiar ,o70_datacriacao) VALUES ({$o70_anousu}, {$o70_codrec}, {$codfon}, {$o70_codigo}, {$o70_valor}, '$o70_reclan', {$o70_instit}, '$o70_concarpeculiar', null)";
      //var_dump($sqlinsere);
      //echo "<hr>"; 
      //echo "<br>";
    }

    if(!empty($errofonte)){
      //die("Erro 1");
      header("Location:preorcrece.php?t=2");      
    }

    if(!empty($erroreceita)){      
      //die("Erro 2");
      header("Location:preorcrece.php?t=2");      
    }
  }//fim do foreach
  
  if(!empty($erroreceita) || !empty($errofonte) || !$inserereceita){
    header("Location:preorcrece.php?t=2");
  }else{
    header("Location:preorcrece.php?t=1");
  }
  //header("Location:preorcrece.php?t=1");
}//Fim do if de 2022




if($_POST["apagar"]){
  $result = pg_query($conn, "DELETE FROM previareceitas WHERE id = '$id' ");
  header("Location:preorcrece.php?e=1");
  /*if($result){
    echo "<script>alert('Registro excluído com sucesso.');</script>";   
    $avisa = "s";
  } else{
    echo "<script>alert('Registro não excluído. Contate o suporte.');</script>";
    $error = pg_last_error($conn);
  }*/
}

if(isset($incluir)) {
    $o70_anousu = (empty($o70_anousu) ? '' : $o70_anousu);
    $o70_codrec = (empty($o70_codrec) ? '' : $o70_codrec);
    $o50_estrutreceita = (empty($o50_estrutreceita) ? '' : $o50_estrutreceita);
    $o57_descr = (empty($o57_descr) ? '' : $o57_descr);
    $o70_codigo = (empty($o70_codigo) ? '' : $o70_codigo);
    $o15_descr = (empty($o15_descr) ? '' : $o15_descr);
    $o70_valor = (empty($o70_valor) ? '' : $o70_valor);
    $o70_reclan = (empty($o70_reclan) ? '' : $o70_reclan);
    $o70_concarpeculiar = (empty($o70_concarpeculiar) ? '' : $o70_concarpeculiar);
    $c58_descr = (empty($c58_descr) ? '' : $c58_descr);
    $o70_instit = (empty($o70_instit) ? '' : $o70_instit);
    
    $unidade = substr($codtrib, 2, strlen($codtrib) - 1);
    $orgao = substr($codtrib, 0, 2);
    $o70_orcorgao = $orgao;
    $o70_orcunidade = $unidade;
    $o70_esferaorcamentaria = $o70_esferaorcamentaria;

    //var_dump($id);
    //die("Ver id");

    $consultaUltimo = pg_query("SELECT max(id) FROM previareceitas");
    $ultimo = pg_fetch_all($consultaUltimo);
    
    if($ultimo[0]["max"] == null){
      $o70_codrec = 1;      
    } else {
      $o70_codrec = $ultimo[0]["max"] + 1;
    }
    

    if(empty($id)){
      //Incluir      
      $result = pg_query($conn, "INSERT INTO previareceitas(o70_anousu, o70_codrec, o50_estrutreceita, o57_descr, o70_codigo, o15_descr, o70_valor, o70_reclan, o70_concarpeculiar, c58_descr, o70_instit, o70_orcorgao, o70_orcunidade, o70_esferaorcamentaria) VALUES('$o70_anousu', '$o70_codrec', '$o50_estrutreceita', '$o57_descr', '$o70_codigo', '$o15_descr', '$o70_valor', '$o70_reclan', '$o70_concarpeculiar', '$c58_descr', '$o70_instit', '$o70_orcorgao', '$o70_orcunidade', '$o70_esferaorcamentaria')");
      $acaousuario = "Incluir";
      //$iddaprevia = retornaProximoId();
      $iddaprevia = retornaUltimoId();
      
    } else{
      //Alterar
      $result = pg_query($conn, "UPDATE previareceitas SET o70_anousu = '$o70_anousu', o70_codrec = '$o70_codrec', o50_estrutreceita = '$o50_estrutreceita', o57_descr = '$o57_descr', o70_codigo = '$o70_codigo', o15_descr = '$o15_descr', o70_valor = '$o70_valor', o70_reclan = '$o70_reclan', o70_concarpeculiar = '$o70_concarpeculiar', c58_descr = '$c58_descr', o70_instit = '$o70_instit', o70_orcorgao = '$o70_orcorgao', o70_orcunidade = '$o70_orcunidade', o70_esferaorcamentaria = '$o70_esferaorcamentaria' WHERE id = '$id'");
      $acaousuario = "Alterar";
      $iddaprevia = $id;
    }

    $tipo = "receita";
    $idusuario = db_getsession("DB_id_usuario");
    $loginusuario = db_getsession("DB_login");
    $ipusuario = db_getsession("DB_ip");
    $datausuario = date("Y-m-d");
    $horausuario = date("H:i");

    if($result){
      if($acaousuario == "Incluir"){
        pg_query("INSERT INTO historicopreviareceitas(o70_anousu, o70_codrec, o50_estrutreceita, o57_descr, o70_codigo, o15_descr, o70_valor, o70_reclan, o70_concarpeculiar, c58_descr, o70_instit, idusuario, loginusuario, ipusuario, datausuario, horausuario, acaousuario,iddaprevia, o70_orcorgao, o70_orcunidade, o70_esferaorcamentaria) VALUES('$o70_anousu', '$o70_codrec', '$o50_estrutreceita', '$o57_descr', '$o70_codigo', '$o15_descr', '$o70_valor', '$o70_reclan', '$o70_concarpeculiar', '$c58_descr', '$o70_instit', {$idusuario}, '{$loginusuario}', '{$ipusuario}', '{$datausuario}', '{$horausuario}', 'incluir', {$iddaprevia}, '$o70_orcorgao', '$o70_orcunidade', '$o70_esferaorcamentaria')");
      }else{
        pg_query("INSERT INTO historicopreviareceitas(o70_anousu, o70_codrec, o50_estrutreceita, o57_descr, o70_codigo, o15_descr, o70_valor, o70_reclan, o70_concarpeculiar, c58_descr, o70_instit, idusuario, loginusuario, ipusuario, datausuario, horausuario, acaousuario,iddaprevia, o70_orcorgao, o70_orcunidade, o70_esferaorcamentaria) VALUES('$o70_anousu', '$o70_codrec', '$o50_estrutreceita', '$o57_descr', '$o70_codigo', '$o15_descr', '$o70_valor', '$o70_reclan', '$o70_concarpeculiar', '$c58_descr', '$o70_instit', {$idusuario}, '{$loginusuario}', '{$ipusuario}', '{$datausuario}', '{$horausuario}', 'alterar', {$iddaprevia}, '$o70_orcorgao', '$o70_orcunidade', '$o70_esferaorcamentaria')");
      }
      echo "<script>alert('Prévia inserida com sucesso.');</script>";   
      $avisa = "s";
    } else{
      echo "<script>alert('Prévia não foi feita. Contate o suporte.');</script>";
      $error = pg_last_error($conn);
    }


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
    <table width="790" border="0" cellpadding="0" cellspacing="0" bgcolor="#5786B2">
        <tr>
            <td width="360" height="18">&nbsp;</td>
            <td width="263">&nbsp;</td>
            <td width="25">&nbsp;</td>
            <td width="140">&nbsp;</td>
        </tr>
    </table>
    <table width="790" border="0" cellspacing="0" cellpadding="0">
        <tr>
            <td height="430" align="left" valign="top" bgcolor="#CCCCCC">
                <center>
                  <?
                  include("forms/frm_preorcrece.php");
                  ?>
                </center>
            </td>
        </tr>
    </table>
    <?
    db_menu(db_getsession("DB_id_usuario"),db_getsession("DB_modulo"),db_getsession("DB_anousu"),db_getsession("DB_instit"));
    ?>
    </body>
    </html>
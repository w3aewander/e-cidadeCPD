<?php
  require_once("libs/db_stdlib.php");
  require_once("libs/db_utils.php");
  require_once("libs/db_app.utils.php");
  require_once("libs/db_conecta.php");
  require_once("libs/db_sessoes.php");
  require_once("dbforms/db_funcoes.php");

  db_app::load("scripts.js");
  db_app::load("prototype.js");
  db_app::load("datagrid.widget.js");
  db_app::load("strings.js");
  db_app::load("grid.style.css");
  db_app::load("estilos.css");
  db_app::load("classes/dbViewAvaliacoes.classe.js");
  db_app::load("widgets/windowAux.widget.js");
  db_app::load("widgets/dbmessageBoard.widget.js");
  db_app::load("dbcomboBox.widget.js");

function testa($var){
  echo "<pre>";
  print_r($var);
  echo "</pre>";
}

function verificaCgm1($conta){
  $sql = pg_query("SELECT c22_numcgm,z01_nome from conplanoreduzcgm inner join cgm on cgm.z01_numcgm = conplanoreduzcgm.c22_numcgm inner join conplanoreduz on conplanoreduz.c61_anousu = conplanoreduzcgm.c22_anousu and conplanoreduz.c61_reduz = conplanoreduzcgm.c22_reduz inner join db_config on db_config.codigo = conplanoreduz.c61_instit inner join orctiporec on orctiporec.o15_codigo = conplanoreduz.c61_codigo inner join conplano as a on a.c60_codcon = conplanoreduz.c61_codcon and a.c60_anousu = conplanoreduz.c61_anousu inner join db_config as b on b.codigo = conplanoreduz.c61_instit inner join orctiporec as c on c.o15_codigo = conplanoreduz.c61_codigo inner join conplano as d on d.c60_codcon = conplanoreduz.c61_codcon and d.c60_anousu = conplanoreduz.c61_anousu where c22_reduz={$conta} and c22_anousu =  ".db_getsession("DB_anousu")."   ");
  $resultado = pg_fetch_all($sql);
  return $resultado[0]["c22_numcgm"];  
}

function verificaCgm2(){
  $sql = pg_query("SELECT z01_numcgm, z01_nome from db_config inner join cgm on numcgm = z01_numcgm where codigo = ".db_getsession("DB_instit")." ");
  $resultado = pg_fetch_all($sql);
  return $resultado[0]["z01_numcgm"];  
}


function verificaConta($conta){
  $hoje = date("Y-m-d");
  $sql = pg_query("SELECT k13_conta, k13_descr, c61_codigo from saltes inner join conplanoreduz on conplanoreduz.c61_reduz = saltes.k13_reduz and c61_anousu=".db_getsession("DB_anousu")." inner join conplanoexe on conplanoexe.c62_reduz = conplanoreduz.c61_reduz and c61_anousu=c62_anousu inner join conplano on conplanoreduz.c61_codcon = conplano.c60_codcon and c61_anousu=c60_anousu left join conplanoconta on conplanoconta.c63_codcon = conplanoreduz.c61_codcon and conplanoconta.c63_anousu=conplanoreduz.c61_anousu left join empagetipo on empagetipo.e83_conta = saltes.k13_conta where (k13_limite is null or k13_limite >= '{$hoje}') and k13_conta={$conta} and c61_instit = ".db_getsession("DB_instit")." and c62_anousu = ".db_getsession("DB_anousu")." ");
  $resultado = pg_fetch_all($sql);
  return $resultado[0];
}

function verificaArquivo($nome){
  $sql = pg_query("SELECT nome FROM confereplanilha WHERE nome = '".$nome."' ");
  $resultado = pg_fetch_all($sql);
  return $resultado[0]["nome"];  
}

$y2 = "";

if($_FILES){
  $nomearquivo = $_FILES['uploadedFile']['name'];
  
  if(verificaArquivo($nomearquivo)){
    echo "<script>alert('Arquivo importado anteriormente.');</script>";
  } else {



  
  $upload = fopen($_FILES['uploadedFile']['tmp_name'], 'r');
  $dados = array();
  while (($linha = fgets($upload)) !== false){
    array_push($dados, $linha);
  }
  
  $dadosprontos = array();
  $contasComErro = array();
  $c = 1;
  //testa($dados); die("Teste");
  foreach ($dados as $linha) {
    if($c == 1){
      $c++;
      continue;
    }

    $info = explode(";", $linha);

    if(verificaConta($info[1])){
      $recurso = verificaConta($info[1]);
      $recurso = $recurso["c61_codigo"];
    } else {
      array_push($contasComErro, $info[1]);
      continue;
    }
    
    $guarda["iReceitaPlanilha"] = "";
    $guarda["iOrigem"] = "1";
    if(verificaCgm1($info[1])){
      $guarda["iCgm"] = verificaCgm1($info[1]);
    } else {
      $guarda["iCgm"] = verificaCgm2();
    }
    $guarda["iInscricao"] = "";
    $guarda["iMatricula"] = "";
    $guarda["iCaracteriscaPeculiar"] = "000";
    $guarda["iContaTesouraria"] = trim($info[1]);
    $guarda["sObservacao"] = "";
    $guarda["nValor"] = str_replace(",", ".", $info[2]);
    $guarda["iRecurso"] =  $recurso;
    $guarda["iReceita"] = trim($info[0]);
    $data = explode("/", $info[3]);
    $ddia = $data[0];
    $dmes = $data[1];
    $dano = $data[2];
    //var_dump($ddia);
    //var_dump($dmes);
    //var_dump($dano);
    $ndano = substr($dano, 0, 4);
    //$x = urlencode($info[3]);
    
    $datafinal = $ddia . "/" . $dmes . "/" . $ndano;
    //$datafinal =  $dano."-".$dmes."-".$ddia;
    //$datafinal = trim($datafinal, "%0D%0A");
    //$guarda["dtRecebimento"] = substr($info[3], 0, 2) ."/".substr($info[3], 2, 2) . "/" . substr($info[3], 4, 4);
    //$guarda["dtRecebimento"] = $info[3];
    $guarda["dtRecebimento"] = $datafinal;
    $guarda["sOperacaoBancaria"] = "";
    //var_dump(urlencode($ndano));
    //echo "<br>";

    array_push($dadosprontos, $guarda);    
  }

  $inserenome = pg_query($conn, "INSERT INTO confereplanilha(nome) VALUES ('{$nomearquivo}')");

  if(!$inserenome){
    $error = pg_last_error($conn);
      var_dump($error);
  }
  
  if($contasComErro){
    echo "A(s) seguinte(s) conta(s) não existem e suas linhas não foram importadas";
    echo "<br>";
    foreach ($contasComErro as $conta) {
      echo $conta . ", ";
    }
  }
 
echo "<script>var x = 1;</script>";
//testa($dadosprontos); die("Verificar datas"); 
}//Vim do if de verificação
}//fim do IF do upload




?>
<script>
if(x == 1){
    console.log("Começou");
    var aReceitasPlanilha = <?php echo json_encode($dadosprontos); ?>;

    var oParametro                 = new Object();
    oParametro.exec                = "salvarPlanilha";
    oParametro.k144_numeroprocesso = "";
    oParametro.iCodigoPlanilha = "";
    oParametro.aReceitas = aReceitasPlanilha;

    sRPC = 'cai4_planilhaarrecadacao.RPC.php';

    var oAjax = new Ajax.Request(sRPC,
              {
               method: 'post',
               parameters: 'json='+Object.toJSON(oParametro),
               onComplete: js_completaSalvar
               });

  function js_completaSalvar(oAjax){
    var oRetorno = eval("("+oAjax.responseText+")");
    if (oRetorno.status == 1) {
        alert("Arquivo importado com sucesso. Planilha "+oRetorno.iCodigoPlanilha);
    } else {
      alert(oRetorno.message.urlDecode());
    }
  }
    
}
</script>
<html>
  <head>
    <title>Microsist</title>
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
    <meta http-equiv="Expires" CONTENT="0">
    <?php
      db_app::load("scripts.js, strings.js, prototype.js, estilos.css");
    ?>
  </head>
  <body style="background-color: #ccc; margin-top: 30px">
    <div id="div_container" style="width: 300px; margin: auto;">
      <form method="post" action="" enctype="multipart/form-data">
      <fieldset>
        <legend style="font-weight: bold;">Importar Arquivo</legend>
        <table>
          <tr>
            <td style="font-weight: bold;">
              Arquivo:
            </td>
            <td>
              <input type="file" name="uploadedFile" />
            </td>
          </tr>
        </table>
      </fieldset>

      <p align="center">
        <input type="submit" name="gerar" value="Importar">
      </p>
      
    </form>
    </div>

  <?php db_menu(db_getsession("DB_id_usuario"),db_getsession("DB_modulo"),db_getsession("DB_anousu"),db_getsession("DB_instit"));?>

  </body>
</html>
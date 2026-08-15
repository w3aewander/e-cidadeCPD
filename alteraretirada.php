<?


require_once("libs/db_stdlib.php");
require_once("libs/db_conecta.php");
require_once("libs/db_sessoes.php");
require_once("libs/db_usuariosonline.php");
require_once("dbforms/db_funcoes.php");
require_once("libs/db_liborcamento.php");
require_once("classes/db_empempenho_classe.php");
require_once("classes/db_orcdotacao_classe.php");
require_once("classes/db_pcmater_classe.php");
require_once("classes/db_cgm_classe.php");
require_once("libs/db_app.utils.php");
$clempempenho = new cl_empempenho;
$clorcdotacao = new cl_orcdotacao;
$clpcmater  = new cl_pcmater;
$clcgm    = new cl_cgm;

$clrotulo = new rotulocampo;
$clrotulo->label("o40_descr");
$clrotulo->label("e53_codord");
$clpcmater->rotulo->label();
$clcgm->rotulo->label();

$clempempenho->rotulo->label();
$clorcdotacao->rotulo->label();

db_postmemory($HTTP_POST_VARS);
db_app::load("prototype.js");

function testa($var){
  echo "<pre>";
  print_r($var);
  echo "</pre>";
}

function retornaOutros($id){
    $sql = pg_query("SELECT numeroretirada FROM certificadoretirada WHERE id = {$id}");
    $numero = pg_fetch_all($sql);
    $numero = $numero[0]["numeroretirada"];

    $sql2 = pg_query("SELECT certificadoretirada.id, certificacaoconformidade.e60_numemp, certificacaoconformidade.corgao, certificacaoconformidade.ordenadordadespesa, certificacaoconformidade.noempenho, certificadoretirada.e50_codord, certificacaoconformidade.z01_nome, certificacaoconformidade.valornotafiscal, certificadoretirada.horaquebra, certificadoretirada.e50_dataquebraordem, certificadoretirada.g11, certificadoretirada.g11f, certificadoretirada.g22, certificadoretirada.g22f, certificadoretirada.g33, certificadoretirada.g33f, certificacaoconformidade.nocertificado, certificadoretirada.e50_dataquebraordem, certificacaoconformidade.cseq2, certificadoretirada.numeroretirada, certificadoretirada.justificativaretirada FROM certificadoretirada INNER JOIN certificacaoconformidade on idtabelacertliq = certificacaoconformidade.id WHERE numeroretirada = {$numero} ORDER BY certificadoretirada.id");
    $resultado = pg_fetch_all($sql2);
    return $resultado;
}

function voltaCodord($numeroretirada){
  $sql = pg_query("SELECT id, e50_codord, idtabelacertliq FROM certificadoretirada WHERE numeroretirada = {$numeroretirada}");
  $resultado = pg_fetch_all($sql);
  return $resultado;
}

function voltaNocertificado($id){
  $sql = pg_query("SELECT nocertificado FROM certificacaoconformidade WHERE id = {$id}");
  $resultado = pg_fetch_all($sql);
  return $resultado[0]["nocertificado"];
}

$idcertificado = $_POST["idcertificado"];
$dados = retornaOutros($idcertificado);

//testa($dados);
//die("Confere dados");

if(isset($_POST["excluir"])){
  $numeroretirada = $_POST["numeroretirada"];
  $codords = voltaCodord($numeroretirada);

  $idusuario = db_getsession("DB_id_usuario");
  $justificativa = $_POST["justificativaretirada"];  
  $dataexclusao = date("Y-m-d");

  foreach ($codords as $op){
    $numcertificado = voltaNocertificado($op["idtabelacertliq"]);
    $alteranum = explode(".", $numcertificado);
    $seq2 = explode("/", $alteranum[3]);
    $aseq2 = "00/".$seq2[1];
    $novocertificado = $alteranum[0] .".".$alteranum[1].".".$alteranum[2].".".$aseq2;

    pg_query("DELETE FROM certificadoretirada WHERE e50_codord = {$op['e50_codord']}");
    pg_query("UPDATE pagordem SET e50_dataquebraordem = null, e50_justificativaquebraordem = '', e50_numedi = 0 WHERE e50_codord = {$op['e50_codord']}");
    pg_query("UPDATE certificacaoconformidade SET cseq2 = 0, nocertificado = '{$novocertificado}' WHERE e50_codord = {$op['e50_codord']}");
    pg_query("INSERT INTO exclusaocertificadoretirada(idtabelaretirada, idusuario, justificativaretirada, dataexclusao) VALUES({$op['id']}, {$idusuario}, '{$justificativa}', '{$dataexclusao}')");

    //echo "<br>";echo "<br>";echo "<br>";echo "<br>";echo "<br>";echo "<br>";echo "<br>";echo "<br>";    
    //echo "DELETE FROM certificadoretirada WHERE e50_codord = {$op['e50_codord']}"; echo "<br>";
    //echo "UPDATE pagordem SET e50_dataquebraordem = null, e50_justificativaquebraordem = '', e50_numedi = 0 WHERE e50_codord = {$op['e50_codord']}"; echo "<br>";
    //echo "UPDATE certificacaoconformidade SET cseq2 = 0, nocertificado = '{$novocertificado}' WHERE e50_codord = {$op['e50_codord']}"; echo "<br>";
    //echo "INSERT INTO exclusaocertificadoretirada(idtabelaretirada, idusuario, justificativaretirada, dataexclusao) VALUES({$op['id']}, {$idusuario}, '{$justificativa}', '{$dataexclusao}')"; echo "<br>";
  }

  $idcertificado = $_POST["idcertificado"];
  $dados = retornaOutros($idcertificado);

  echo "<script>alert('Retirada da OC excluída.');</script>";
}

if(isset($_POST["alterar"])){
  $idusuario = db_getsession("DB_id_usuario");
  $justificativa = $_POST["justificativaretirada"];
  $numeroretirada = $_POST["numeroretirada"];
  $novadata = $_POST["e50_dataquebraordem"];
  $codords = voltaCodord($numeroretirada);
  
  //$sql = "UPDATE certificadoretirada SET g11 = {$g11}, g11f = '{$g11f}', g22 = {$g22}, g22f = '{$g22f}', g33 = {$g33}, g33f = '{$g33f}', e50_dataquebraordem = '{$novadata}', justificativaretirada = '{$justificativa}', idusuario = {$idusuario} WHERE numeroretirada = {$numeroretirada}";
  pg_query("UPDATE certificadoretirada SET g11 = {$g11}, g11f = '{$g11f}', g22 = {$g22}, g22f = '{$g22f}', g33 = {$g33}, g33f = '{$g33f}', e50_dataquebraordem = '{$novadata}', justificativaretirada = '{$justificativa}', idusuario = {$idusuario} WHERE numeroretirada = {$numeroretirada}");
  
  foreach ($codords as $op){        
    //$sql2 = "UPDATE pagordem SET e50_dataquebraordem = '{$novadata}' WHERE e50_codord = {$op['e50_codord']}";    
    pg_query("UPDATE pagordem SET e50_dataquebraordem = '{$novadata}' WHERE e50_codord = {$op['e50_codord']}");
  }

  $idcertificado = $_POST["idcertificado"];
  $dados = retornaOutros($idcertificado);

  echo "<script>alert('Certificado alterado.');</script>";
  
  
  //pg_query("UPDATE certificadoretirada SET WHERE numeroretirada = {$numeroretirada}");
}

?>

<html>
<head>
<title>Microsist</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<meta http-equiv="Expires" CONTENT="0">
<script language="JavaScript" type="text/javascript" src="scripts/scripts.js"></script>
</head>
<body bgcolor=#CCCCCC leftmargin="0" topmargin="0" marginwidth="0" marginheight="0" onLoad="a=1" bgcolor="#cccccc">
<link href="estilos.css" rel="stylesheet" type="text/css">


<style>
#k81_origem{width:95px}.tamanho-primeira-col{width:150px}.input-menor{width:100px}.input-maior{width:400px}#k81_codigo{width:95px}#k81_codigodescr{width:77%}#k81_obs{width:100%;height:50px}div.gridcontainer{border:2px inset #fff;background-color:#eee;width:100%}div.header-container div.grid-resize{float:right;z-index:998;cursor:pointer;border:1px outset #fff}div.header-container table.table-header{background-color:#eee;font-weight:700;text-align:center;width:98%;border-collapse:collapse}div.header-container table.table-header tr{border-bottom:3px outset #fff;height:20px}div.header-container table.table-header tr td{padding:0;margin:0;white-space:nowrap;overflow:hidden;text-align:center!important;padding:1;padding-left:3;position:relative;background-clip:padding-box}div.body-container{width:80%;margin:0 auto ;height:100px;}div.body-container table.table-body{background-color:#fff;width:100%;border-collapse:collapse;overflow:auto}div.footer-container{width:100%}div.body-container table.table-body tr{border-bottom:1px outset #d3d3d3;padding:0;margin:0;height:1em}div.body-container table.table-body tr td{border-right:1px outset #d3d3d3;padding:0;margin:0;white-space:nowrap;overflow:hidden;padding:1;padding-left:3}input{font-family:Arial,Helvetica,sans-serif,verdana;font-size:12px;height:18px;border:1px solid #999}.cabecalho{font-weight:700;background-color:#eeeff2}  
</style>


<div style="margin-top: 20px;">
  <button type="button">
    <a href="certificadoretirada.php" style="text-decoration: none">Voltar</a>
    </button>
</div>


<fieldset style="width:80%;margin: 5px auto 0">
  <legend>Alteração/Exclusão de Certificado de Retirada</legend>

<div id="body-container-gridmsc" class="body-container" style="height:auto;">
<form method="post" action="">

<input type="hidden" name="idcertificado" value="<?=$idcertificado;?>">
<input type="hidden" name="numeroretirada" value="<?=$dados[0]['numeroretirada']?>">

<table class="table-body" id="gridmscbody">


<tr>  
<td class="linhagrid cell cabecalho" title="" style="width: 5%; text-align: center;" nowrap="">
  Sequencial do Empenho
  </td>

  <td class="linhagrid cell cabecalho" title="" style="width: 5%; text-align: center;" nowrap="">
  Nº do Empenho
  </td>

  <td class="linhagrid cell cabecalho" title="" style="width: 5%; text-align: center;" nowrap="">
  Ordem de Pagamento
  </td>

  <td class="linhagrid cell cabecalho" title="" style="width: 5%; text-align: center;" nowrap="">
  Nº Certificado
  </td>  

  <td class="linhagrid cell cabecalho" title="" style="width: 5%; text-align: center;" nowrap="">
  Retirado da OC
  </td>

  <td class="linhagrid cell cabecalho" title="" style="width: 5%; text-align: center;" nowrap="">
  Data da Retirada
  </td>
  
  
</tr>
  <?php foreach($dados as $linha) : ?>
    <tr>
    

    <td class="linhagrid cell" title="" style="width: 5%; text-align: left;" nowrap="">
      <?=$linha["e60_numemp"];?>
    </td>

    <td class="linhagrid cell" title="" style="width: 5%; text-align: left;" nowrap="">
      <?=$linha["noempenho"];?>
    </td>

    <td class="linhagrid cell" title="" style="width: 5%; text-align: left;" nowrap="">
      <?=$linha["e50_codord"];?>
    </td>

    <td class="linhagrid cell" title="" style="width: 5%; text-align: left;" nowrap="">
      <?=$linha["nocertificado"];?>
    </td>

    <td class="linhagrid cell" title="" style="width: 5%; text-align: left;" nowrap="">
      <?=($linha["cseq2"] == 0) ? "Não" : "Sim";?>
    </td>    

    <td class="linhagrid cell" title="" style="width: 5%; text-align: left;" nowrap="">
      <?= implode("/", array_reverse(explode("-", $linha["e50_dataquebraordem"])));?>
    </td>

    
  </tr>
  <?php endforeach; ?>
</table>

<div style="text-align: left">
<p>
  <label>Justificativa de quebra de ordem cronológica exarada pelo ordenado de despesa.</label><br>
  <input type="radio" id="g11" name="g11" value="1" <?=($dados[0]["g11"] == 1) ? "checked" : ""?>>Sim
  <input type="radio" id="g11" name="g11" value="2" <?=($dados[0]["g11"] == 2) ? "checked" : ""?>>Não  
   Folha: <input type="text" name="g11f" id="g11f" size="30" maxlength="30" value="<?=$dados[0]['g11f']?>"><br>
</p>

<p>
  <label>Acolhimento do órgão de controle interno prévia justificativa para alteração da ordem cronológica de pagamentos</label><br>
  <input type="radio" id="g22" name="g22" value="1" <?=($dados[0]["g22"] == 1) ? "checked" : ""?>>Sim
  <input type="radio" id="g22" name="g22" value="2" <?=($dados[0]["g22"] == 2) ? "checked" : ""?>>Não  
   Folha: <input type="text" name="g22f" id="g22f" size="30" maxlength="30" value="<?=$dados[0]['g22f']?>"><br>
</p>

<p>
  <label>Publicação da quebra de ordem cronológica</label><br>
  <input type="radio" id="g33" name="g33" value="1" <?=($dados[0]["g33"] == 1) ? "checked" : ""?>>Sim
  <input type="radio" id="g33" name="g33" value="2" <?=($dados[0]["g33"] == 2) ? "checked" : ""?>>Não  
   Folha: <input type="text" name="g33f" id="g33f" size="30" maxlength="30" value="<?=$dados[0]['g33f']?>"><br>
</p>
<p>
  <label>Alterar Data da Retirada</label><br>
  <input type="date" name="e50_dataquebraordem" value="<?=$dados[0]['e50_dataquebraordem']?>">
</p>
<p>
  <label>Justificativa</label><br>
  <input type="text" name="justificativaretirada" size="100" maxlength="100" required value="<?=$dados[0]['justificativaretirada']?>">
</p>
</div>


<input type="submit" name="alterar" value="Alterar">
<input type="submit" name="excluir" value="Excluir">

</form>

</div>
</fieldset>


<? 
db_menu(db_getsession("DB_id_usuario"),db_getsession("DB_modulo"),db_getsession("DB_anousu"),db_getsession("DB_instit"));
?>
</body>
</html>
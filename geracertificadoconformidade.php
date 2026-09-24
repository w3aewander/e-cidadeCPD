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


db_postmemory($HTTP_POST_VARS);
db_app::load("prototype.js");


function testa($var){
  echo "<pre>";
  print_r($var);
  echo "</pre>";
}

function buscaCertificado($nocertificado){
  $sql = pg_query("SELECT * FROM certificacaoconformidade WHERE nocertificado = '{$nocertificado}'");
  $resultado = pg_fetch_all($sql);
  return $resultado[0];
}


function buscaPorId($id){
  $sql = pg_query("SELECT * FROM certificacaoconformidade WHERE id = '{$id}'");
  $resultado = pg_fetch_all($sql);
  return $resultado[0];
}

function buscaDataLancamento($op, $valor){
  $sql = pg_query("SELECT c70_data FROM conlancamemp inner join conlancam on c70_codlan = c75_codlan left join conlancamordem on conlancamordem.c03_codlan = conlancam.c70_codlan left outer join conlancampag on c82_codlan = c70_codlan inner join conlancamdoc on c71_codlan = c70_codlan inner join conhistdoc on c53_coddoc = c71_coddoc left join conlancamcompl on c72_codlan  =c70_codlan left join conlancamnota on c66_codlan  =c70_codlan left join conlancamord on c80_codlan = c70_codlan left join empnota on c66_codnota = e69_codnota left join conplanoreduz on c61_reduz = conlancampag.c82_reduz and c61_anousu=c70_anousu left join conplano on c60_codcon = conplanoreduz.c61_codcon and c60_anousu=c61_anousu left join pagordem on e50_codord = c80_codord WHERE e50_codord = {$op} AND c70_valor = '{$valor}'");
  $resultado = pg_fetch_all($sql);
  return $resultado[0]["c70_data"];
}

function buscaOP($id){
  $sql = pg_query("SELECT e50_codord FROM certificacaoconformidade WHERE id = {$id}");
  $resultado = pg_fetch_all($sql);
  return $resultado[0]["e50_codord"];
}


if(isset($_POST["prosseguir"])){


$dados = array();
$dados["e60_numemp"] = $_POST["e60_numemp"];
$dados["e60_codemp"] = $_POST["e60_codemp"];
$dados["e60_anousu"] = $_POST["e60_anousu"];
$dados["e60_instit"] = $_POST["e60_instit"];
$dados["o58_orgao"] = $_POST["o58_orgao"];
$dados["cempenho"] = $_POST["cempenho"];
$dados["cseq1"] = $_POST["cseq1"];
$dados["cseq2"] = $_POST["cseq2"];
$dados["cano"] = $_POST["cano"];
$dados["nocertificado"] = $_POST["nocertificado"];
$dados["noprocesso"] = $_POST["noprocesso"];
$dados["datageracao"] = $_POST["datageracao"];
$dados["ordenadordadespesa"] = $_POST["ordenadordadespesa"];
$dados["instrumentojuridico"] = $_POST["instrumentojuridico"];
$dados["noempenho"] = $_POST["noempenho"];
$dados["nonotafiscal"] = $_POST["nonotafiscal"];

$dados["valornotafiscal"] = $_POST["valornotafiscal"];
$dados["valornotafiscal"] = str_replace(".", "", $dados["valornotafiscal"]);
$dados["valornotafiscal"] = str_replace(",", ".", $dados["valornotafiscal"]);

$dados["z01_numcgm"] = $_POST["z01_numcgm"];
$dados["fornecedor"] = $_POST["fornecedor"];
$dados["cpffornecedor"] = $_POST["cpffornecedor"];
$dados["g11"] = $_POST["g11"];
$dados["g11f"] = $_POST["g11f"];
$dados["g12"] = $_POST["g12"];
$dados["g12f"] = $_POST["g12f"];
$dados["g13"] = $_POST["g13"];
$dados["g13f"] = $_POST["g13f"];
$dados["g14"] = $_POST["g14"];
$dados["g14f"] = $_POST["g14f"];
$dados["g15"] = $_POST["g15"];
$dados["g15f"] = $_POST["g15f"];
$dados["g16"] = $_POST["g16"];
$dados["g16f"] = $_POST["g16f"];
$dados["g17"] = $_POST["g17"];
$dados["g17f"] = $_POST["g17f"];
$dados["g18"] = $_POST["g18"];
$dados["g18f"] = $_POST["g18f"];
$dados["g19"] = $_POST["g19"];
$dados["g19f"] = $_POST["g19f"];
$dados["g110"] = $_POST["g110"];
$dados["g110f"] = $_POST["g110f"];
$dados["g111"] = $_POST["g111"];
$dados["g111f"] = $_POST["g111f"];
$dados["g112"] = $_POST["g112"];
$dados["g112f"] = $_POST["g112f"];
$dados["g113"] = $_POST["g113"];
$dados["g113f"] = $_POST["g113f"];
$dados["g114"] = $_POST["g114"];
$dados["g114f"] = $_POST["g114f"];
$dados["g115"] = $_POST["g115"];
$dados["g115f"] = $_POST["g115f"];
$dados["g116"] = $_POST["g116"];
$dados["g116f"] = $_POST["g116f"];
$dados["g117"] = $_POST["g117"];
$dados["g117f"] = $_POST["g117f"];




$sqlinsercao = "INSERT INTO certificacaoconformidade(e60_numemp, e60_codemp, e60_anousu, e60_instit, corgao, cempenho, cseq1, cseq2,cano, nocertificado, noprocesso, datageracao, ordenadordadespesa, instrumentojuridico, noempenho, nonotafiscal, valornotafiscal, z01_numcgm, z01_nome, z01_cgccpf, g11, g11f, g12, g12f, g13, g13f, g14, g14f, g15, g15f, g16, g16f, g17, g17f, g18, g18f, g19, g19f, g110, g110f, g111, g111f, g112, g112f, g113, g113f, g114, g114f, g115, g115f, g116, g116f, g117, g117f) VALUES({$dados["e60_numemp"]}, '{$dados["e60_codemp"]}', {$dados["e60_anousu"]}, {$dados["e60_instit"]}, {$dados["o58_orgao"]}, {$dados["cempenho"]}, {$dados["cseq1"]}, {$dados["cseq2"]}, {$dados["cano"]}, '{$dados["nocertificado"]}', '{$dados["noprocesso"]}', '{$dados["datageracao"]}', '{$dados["ordenadordadespesa"]}', '{$dados["instrumentojuridico"]}', '{$dados["noempenho"]}', '{$dados["nonotafiscal"]}', {$dados["valornotafiscal"]}, {$dados["z01_numcgm"]}, '{$dados["fornecedor"]}', '{$dados["cpffornecedor"]}', {$dados["g11"]}, '{$dados["g11f"]}', {$dados["g12"]}, '{$dados["g12f"]}', {$dados["g13"]}, '{$dados["g13f"]}', {$dados["g14"]}, '{$dados["g14f"]}', {$dados["g15"]}, '{$dados["g15f"]}', {$dados["g16"]}, '{$dados["g16f"]}', {$dados["g17"]}, '{$dados["g17f"]}', {$dados["g18"]}, '{$dados["g18f"]}', {$dados["g19"]}, '{$dados["g19f"]}', {$dados["g110"]}, '{$dados["g110f"]}', {$dados["g111"]}, '{$dados["g111f"]}', {$dados["g112"]}, '{$dados["g112f"]}', {$dados["g113"]}, '{$dados["g113f"]}', {$dados["g114"]}, '{$dados["g114f"]}', {$dados["g115"]}, '{$dados["g115f"]}', {$dados["g116"]}, '{$dados["g116f"]}', {$dados["g117"]}, '{$dados["g117f"]}')";


$sql = pg_query($conn, $sqlinsercao);

if(!$sql){
     $error = pg_last_error($conn);
    //var_dump($error);
  } else {
    echo "<script>alert('Certificado criado com sucesso.');</script>";

    $dadoscertificado = buscaCertificado($dados["nocertificado"]);
    //testa($dadoscertificado);
  }
 
}//Fim do if post para inserção


if(isset($_POST["alterar"])){
  $op = buscaOP($_POST["idtabela"]);
  $adatageracao = $_POST["datageracao"];
  $datacompara = buscaDataLancamento($op, $_POST["valornotafiscal"]);  
  
  $d1 = new DateTime($adatageracao);
  $d2 = new DateTime($datacompara);
  $kompara = $d1 > $d2;
   
  
  $aid = $_POST["idtabela"];
  $ajustificativa = $_POST["justificativa"];
  $ausuarioid = $_POST["usuarioid"];
  $adata = $_POST["dataalteracao"];
  $anoprocesso = $_POST["noprocesso"];  
  $ainstrumentojuridico = $_POST["instrumentojuridico"];
  $anonotafiscal = $_POST["nonotafiscal"];
  $avalor = $_POST["valornotafiscal"];
  $avalor = str_replace(".", "", $avalor);
  $avalor = str_replace(",", ".", $avalor);
  $ag11 = $_POST["g11"];
  $ag11f = $_POST["g11f"];
  $ag12 = $_POST["g12"];
  $ag12f = $_POST["g12f"];
  $ag13 = $_POST["g13"];
  $ag13f = $_POST["g13f"];
  $ag14 = $_POST["g14"];
  $ag14f = $_POST["g14f"];
  $ag15 = $_POST["g15"];
  $ag15f = $_POST["g15f"];
  $ag16 = $_POST["g16"];
  $ag16f = $_POST["g16f"];
  $ag17 = $_POST["g17"];
  $ag17f = $_POST["g17f"];
  $ag18 = $_POST["g18"];
  $ag18f = $_POST["g18f"];
  $ag19 = $_POST["g19"];
  $ag19f = $_POST["g19f"];
  $ag110 = $_POST["g110"];
  $ag110f = $_POST["g110f"];
  $ag111 = $_POST["g111"];
  $ag111f = $_POST["g111f"];
  $ag112 = $_POST["g112"];
  $ag112f = $_POST["g112f"];
  $ag113 = $_POST["g113"];
  $ag113f = $_POST["g113f"];
  $ag114 = $_POST["g114"];
  $ag114f = $_POST["g114f"];
  $ag115 = $_POST["g115"];
  $ag115f = $_POST["g115f"];
  $ag116 = $_POST["g116"];
  $ag116f = $_POST["g116f"];
  $ag117 = $_POST["g117"];
  $ag117f = $_POST["g117f"];

  $sqlAlteracao = "UPDATE certificacaoconformidade SET alterado = 1, justificativa = '{$ajustificativa}', usuarioid = $ausuarioid, dataalteracao = '{$adata}', datageracao = '{$adatageracao}', noprocesso = '{$anoprocesso}', instrumentojuridico = '{$ainstrumentojuridico}', nonotafiscal = '{$anonotafiscal}', valornotafiscal = '{$avalor}', g11 = {$ag11}, g11f = '{$ag11f}', g12 = {$ag12}, g12f = '{$ag12f}', g13 = {$ag13}, g13f = '{$ag13f}', g14 = {$ag14}, g14f = '{$ag14f}', g15 = {$ag15}, g15f = '{$ag15f}', g16 = {$ag16}, g16f = '{$ag16f}', g17 = {$ag17}, g17f = '{$ag17f}', g18 = {$ag18}, g18f = '{$ag18f}', g19 = {$ag19}, g19f = '{$ag19f}', g110 = {$ag110}, g110f = '{$ag110f}', g111 = {$ag111}, g111f = '{$ag111f}', g112 = {$ag112}, g112f = '{$ag112f}', g113 = {$ag113}, g113f = '{$ag113f}', g114 = {$ag114}, g114f = '{$ag114f}', g115 = {$ag115}, g115f = '{$ag115f}', g116 = {$ag116}, g116f = '{$ag116f}', g117 = {$ag117}, g117f = '{$ag117f}' WHERE id = {$aid}";
  

  if($kompara == true){
    echo "<script>alert('Data da geração maior que data do lançamento.')</script>";
  } else {    
    $rodaSqlA = pg_query($conn, $sqlAlteracao);
    if(!$sqlAlteracao){
      $error = pg_last_error($conn);
      //var_dump($error);
    } else {
      echo "<script>alert('Certificado alterado com sucesso.');</script>";
      $dadoscertificado = buscaPorId($aid);
    }
  }


  
}// Fim if para alteração


if(isset($_POST["excluir"])){  
  $eid = $_POST["idtabela"];
  $ejustificativa = $_POST["justificativa"];
  $eusuarioid = $_POST["usuarioid"];
  $edata = $_POST["dataalteracao"];

  $sqlExclusao = "UPDATE certificacaoconformidade SET excluido = 1, justificativa = '{$ejustificativa}', usuarioid = $eusuarioid, dataalteracao = '{$edata}' WHERE id = {$eid}";
  $rodaSqlE = pg_query($conn, $sqlExclusao);
  if(!$sqlExclusao){
     $error = pg_last_error($conn);
    //var_dump($error);
  } else {
    echo "<script>alert('Certificado excluído com sucesso.');</script>";

    $dadoscertificado = buscaPorId($eid);
    //testa($dadoscertificado);
  }
  
}// Fim if para exclusão


if(isset($_POST["imprimir"])){
  testa($_POST);
  die("Imprimir");
}// Fim if para impressão



if(isset($_POST["consulta"])){
  $dadoscertificado = buscaPorId($_POST["idcertificado"]);
}// Fim if para consulta



?>

<html>
<head>
<title>Microsist</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<meta http-equiv="Expires" CONTENT="0">
<script language="JavaScript" type="text/javascript" src="scripts/scripts.js"></script>
<link href="estilos.css" rel="stylesheet" type="text/css">
</head>
<body bgcolor=#CCCCCC leftmargin="0" topmargin="0" marginwidth="0" marginheight="0" onLoad="a=1" bgcolor="#cccccc">
<style>
  .soleitura{background-color: #DEB887}
  .containerfrm{width: 500px; clear: both;margin:25px auto 0;}
  .primeiro{width: 100%; clear: both;margin-bottom: 10px;}
  .containerfrm label{font-weight: bold}
</style>

<div class="containerfrm">
  <button><a href="certificadoconformidade.php" style="text-decoration: none">Voltar</a></button>
  
  <input type="button" name="imprimir" value="Imprimir" onclick="imprime();">
<form name="form1" method="post" action="geracertificadoconformidade.php">
  <fieldset>
    <legend><strong>Dados Certificado</strong></legend>
  

    <input type="hidden" name="idtabela" id="idtabela" value="<?=$dadoscertificado['id'];?>">
    
    <label>Nº Certificado</label>
    <input class="primeiro soleitura" type="text" name="nocertificado" readonly value="<?=$dadoscertificado['nocertificado'];?>"><br>

    <label>Nº Processo</label>
    <input class="primeiro" type="text" name="noprocesso" id="noprocesso" required maxlength="13" value="<?=$dadoscertificado['noprocesso']?>" onkeypress="return valida1(event)"><br>
<?php 
$datagerada =implode("/", array_reverse(explode("-", $dadoscertificado['datageracao']))); 
?>
    <label>Data da Geração</label>
    
    <?php  /* ?>
    <input class="primeiro soleitura" type="text" name="datageracao" readonly value="<?=$datagerada;?>"><br>
    <?php */ ?>

    <input class="primeiro" type="date" onchange="limpa()" name="datageracao" value="<?=$dadoscertificado['datageracao'];?>"><br>
    
    <label>Ordenador da Despesa</label>
    <input class="primeiro soleitura" type="text" name="ordenadordadespesa" required readonly value="<?=$dadoscertificado['ordenadordadespesa'];?>"><br>

    <label>Instrumento Jurídico</label>
    <input class="primeiro" type="text" name="instrumentojuridico" maxlength="40" required value="<?=$dadoscertificado['instrumentojuridico'];?>"><br>

    <label>Nº do Empenho</label>
    <input class="primeiro soleitura" type="text" name="noempenho" readonly value="<?=$dadoscertificado['noempenho'];?>"><br>

    <label>Nº da Nota Fiscal</label>
    <input class="primeiro" type="text" name="nonotafiscal" required maxlength="10" value="<?=$dadoscertificado['nonotafiscal'];?>"><br>

    <label>Valor da Nota Fiscal</label>
    <input class="primeiro" type="text" name="valornotafiscal" id="valornotafiscal" required maxlength="13" onkeyup="mascaraMoeda(this, event)" value="<?=$dadoscertificado['valornotafiscal'];?>"><br>

    <label>Fornecedor/Beneficiário</label>
    <input class="primeiro soleitura" type="text" name="fornecedor" readonly required value="<?=$dadoscertificado['z01_nome'];?>"><br>

    <label>CNPJ/CPF - Fornecedor/Beneficiário</label>
    <input class="primeiro soleitura" type="text" name="cpffornecedor" readonly required value="<?=$dadoscertificado['z01_cgccpf'];?>">
  
<?php 
/*
<?=($dadoscertificado['g11'] == 1) ? 'checked' : ''; ?>
<?=($dadoscertificado['g11'] == 2) ? 'checked' : ''; ?>
<?=($dadoscertificado['g11'] == 3) ? 'checked' : ''; ?>

*/


 ?>

  <p>
    <label>1.1 – A nota fiscal foi emitida contra a Município/Prefeitura Municipal de volta Redonda?</label><br>
    <input type="radio" name="g11" value="1" onclick="travaNA(1)" required <?=($dadoscertificado['g11'] == 1) ? 'checked' : ''; ?>>Sim
    <input type="radio" name="g11" value="2" onclick="travaNA(1)" <?=($dadoscertificado['g11'] == 2) ? 'checked' : ''; ?>>Não
    <input type="radio" name="g11" value="3" onclick="liberaNA(1)" <?=($dadoscertificado['g11'] == 3) ? 'checked' : ''; ?>>N/A
    Folha: <input type="text" name="g11f" id="g11f" size="30" maxlength="30"  value="<?=$dadoscertificado['g11f'];?>"><br>
</p>

<p>
    <label>1.2 – O credor informado no empenho é o mesmo dos demais documentos constantes do processo?</label><br>
    <input type="radio" name="g12" value="1" onclick="travaNA(2)" required <?=($dadoscertificado['g12'] == 1) ? 'checked' : ''; ?>>Sim
    <input type="radio" name="g12" value="2" onclick="travaNA(2)" <?=($dadoscertificado['g12'] == 2) ? 'checked' : ''; ?>>Não
    <input type="radio" name="g12" value="3" onclick="liberaNA(2)" <?=($dadoscertificado['g12'] == 3) ? 'checked' : ''; ?>>N/A
    Folha: <input type="text" name="g12f" id="g12f" size="30" maxlength="30"  value="<?=$dadoscertificado['g12f'];?>"><br>
</p>

<p>
    <label>1.3 – A Nota de Empenho foi emitida até a data de início da realização da despesa e assinada pelo ordenador da despesa?</label><br>
    <input type="radio" name="g13" value="1" onclick="travaNA(3)" required <?=($dadoscertificado['g13'] == 1) ? 'checked' : ''; ?>>Sim
    <input type="radio" name="g13" value="2" onclick="travaNA(3)" <?=($dadoscertificado['g13'] == 2) ? 'checked' : ''; ?>>Não
    <input type="radio" name="g13" value="3" onclick="liberaNA(3)" <?=($dadoscertificado['g13'] == 3) ? 'checked' : ''; ?>>N/A
    Folha: <input type="text" name="g13f" id="g13f" size="30" maxlength="30"  value="<?=$dadoscertificado['g13f'];?>"><br>
</p>

<p>
    <label>1.4 – A despesa foi classificada na natureza de despesa adequada ao objeto do contrato?</label><br>
    <input type="radio" name="g14" value="1" onclick="travaNA(4)" required <?=($dadoscertificado['g14'] == 1) ? 'checked' : ''; ?>>Sim
    <input type="radio" name="g14" value="2" onclick="travaNA(4)" <?=($dadoscertificado['g14'] == 2) ? 'checked' : ''; ?>>Não
    <input type="radio" name="g14" value="3" onclick="liberaNA(4)" <?=($dadoscertificado['g14'] == 3) ? 'checked' : ''; ?>>N/A
    Folha: <input type="text" name="g14f" id="g14f" size="30" maxlength="30"  value="<?=$dadoscertificado['g14f'];?>"><br>
</p>

<p>
    <label>1.5 - O credor da(s) nota(s) de empenho(s) coincide com o do emitente do(s) documento(s) comprobatório(s) ?</label><br>
    <input type="radio" name="g15" value="1" onclick="travaNA(5)" required <?=($dadoscertificado['g15'] == 1) ? 'checked' : ''; ?>>Sim
    <input type="radio" name="g15" value="2" onclick="travaNA(5)" <?=($dadoscertificado['g15'] == 2) ? 'checked' : ''; ?>>Não
    <input type="radio" name="g15" value="3" onclick="liberaNA(5)" <?=($dadoscertificado['g15'] == 3) ? 'checked' : ''; ?>>N/A
    Folha: <input type="text" name="g15f" id="g15f" size="30" maxlength="30"  value="<?=$dadoscertificado['g15f'];?>"><br>
</p>

<p>
    <label>1.6 - Os itens descritos na Nota Fiscal guardam paridade com os itens que constam na(s) Nota(s) de Empenho e NRM (Nota de Recebimento de Material)?</label><br>
    <input type="radio" name="g16" value="1" onclick="travaNA(6)" required <?=($dadoscertificado['g16'] == 1) ? 'checked' : ''; ?>>Sim
    <input type="radio" name="g16" value="2" onclick="travaNA(6)" <?=($dadoscertificado['g16'] == 2) ? 'checked' : ''; ?>>Não
    <input type="radio" name="g16" value="3" onclick="liberaNA(6)" <?=($dadoscertificado['g16'] == 3) ? 'checked' : ''; ?>>N/A
    Folha: <input type="text" name="g16f" id="g16f" size="30" maxlength="30"  value="<?=$dadoscertificado['g16f'];?>"><br>
</p>

<p>
    <label>1.7 - A Nota Fiscal está devidamente atestada por dois funcionários e se for o caso, autorizada pelo Ordenador da Despesa?</label><br>
    <input type="radio" name="g17" value="1" onclick="travaNA(7)" required <?=($dadoscertificado['g17'] == 1) ? 'checked' : ''; ?>>Sim
    <input type="radio" name="g17" value="2" onclick="travaNA(7)" <?=($dadoscertificado['g17'] == 2) ? 'checked' : ''; ?>>Não
    <input type="radio" name="g17" value="3" onclick="liberaNA(7)" <?=($dadoscertificado['g17'] == 3) ? 'checked' : ''; ?>>N/A
    Folha: <input type="text" name="g17f" id="g17f" size="30" maxlength="30"  value="<?=$dadoscertificado['g17f'];?>"><br>
</p>

<p>
    <label>1.8 - Consta calculo de ISS efetuado pela Fiscalização Municipal?</label><br>
    <input type="radio" name="g18" value="1" onclick="travaNA(8)" required <?=($dadoscertificado['g18'] == 1) ? 'checked' : ''; ?>>Sim
    <input type="radio" name="g18" value="2" onclick="travaNA(8)" <?=($dadoscertificado['g18'] == 2) ? 'checked' : ''; ?>>Não
    <input type="radio" name="g18" value="3" onclick="liberaNA(8)" <?=($dadoscertificado['g18'] == 3) ? 'checked' : ''; ?>>N/A
    Folha: <input type="text" name="g18f" id="g18f" size="30" maxlength="30"  value="<?=$dadoscertificado['g18f'];?>"><br>
</p>

<p>
    <label>1.9 – Em se tratando de Nota Fiscal Eletrônica a sua autenticidade foi verificada?</label><br>
    <input type="radio" name="g19" value="1" onclick="travaNA(9)" required <?=($dadoscertificado['g19'] == 1) ? 'checked' : ''; ?>>Sim
    <input type="radio" name="g19" value="2" onclick="travaNA(9)" <?=($dadoscertificado['g19'] == 2) ? 'checked' : ''; ?>>Não
    <input type="radio" name="g19" value="3" onclick="liberaNA(9)" <?=($dadoscertificado['g19'] == 3) ? 'checked' : ''; ?>>N/A
    Folha: <input type="text" name="g19f" id="g19f" size="30" maxlength="30"  value="<?=$dadoscertificado['g19f'];?>"><br>
</p>

<p>
    <label>1.10 - Consta do processo uma cópia do termo de contrato/aditivo/convênio/ajuste/rescisão entre o Governo Municipal e o Fornecedor? </label><br>
    <input type="radio" name="g110" value="1" onclick="travaNA(10)" required <?=($dadoscertificado['g110'] == 1) ? 'checked' : ''; ?>>Sim
    <input type="radio" name="g110" value="2" onclick="travaNA(10)" <?=($dadoscertificado['g110'] == 2) ? 'checked' : ''; ?>>Não
    <input type="radio" name="g110" value="3" onclick="liberaNA(10)" <?=($dadoscertificado['g110'] == 3) ? 'checked' : ''; ?>>N/A
    Folha: <input type="text" name="g110f" id="g110f" size="30" maxlength="30"  value="<?=$dadoscertificado['g110f'];?>"><br>
</p>

<p>
    <label>1.11 - Foram observadas as regras previstas no Edital/Ata de registro de preço e no contrato?</label><br>
    <input type="radio" name="g111" value="1" onclick="travaNA(11)" required <?=($dadoscertificado['g111'] == 1) ? 'checked' : ''; ?>>Sim
    <input type="radio" name="g111" value="2" onclick="travaNA(11)" <?=($dadoscertificado['g111'] == 2) ? 'checked' : ''; ?>>Não
    <input type="radio" name="g111" value="3" onclick="liberaNA(11)" <?=($dadoscertificado['g111'] == 3) ? 'checked' : ''; ?>>N/A
    Folha: <input type="text" name="g111f" id="g111f" size="30" maxlength="30"  value="<?=$dadoscertificado['g111f'];?>"><br>
</p>

<p>
    <label>1.12 – Se o objeto do processo for referente a recurso vinculado (convênio), consta uma cópia do Contrato de Repasse?</label><br>
    <input type="radio" name="g112" value="1" onclick="travaNA(12)" required <?=($dadoscertificado['g112'] == 1) ? 'checked' : ''; ?>>Sim
    <input type="radio" name="g112" value="2" onclick="travaNA(12)" <?=($dadoscertificado['g112'] == 2) ? 'checked' : ''; ?>>Não
    <input type="radio" name="g112" value="3" onclick="liberaNA(12)" <?=($dadoscertificado['g112'] == 3) ? 'checked' : ''; ?>>N/A
    Folha: <input type="text" name="g112f" id="g112f" size="30" maxlength="30"  value="<?=$dadoscertificado['g112f'];?>"><br>
</p>

<p>
    <label>1.13 - A entrega do bem ou serviço está de acordo com o cronograma previsto? (semanalmente; quinzenalmente; mensalmente ou entrega imediata)?</label><br>
    <input type="radio" name="g113" value="1" onclick="travaNA(13)" required <?=($dadoscertificado['g113'] == 1) ? 'checked' : ''; ?>>Sim
    <input type="radio" name="g113" value="2" onclick="travaNA(13)" <?=($dadoscertificado['g113'] == 2) ? 'checked' : ''; ?>>Não
    <input type="radio" name="g113" value="3" onclick="liberaNA(13)" <?=($dadoscertificado['g113'] == 3) ? 'checked' : ''; ?>>N/A
    Folha: <input type="text" name="g113f" id="g113f" size="30" maxlength="30"  value="<?=$dadoscertificado['g113f'];?>"><br>
</p>

<p>
    <label>1.14 - Consta do processo a Regularidade Fiscal (FGTS - INSS - CNDT)? </label><br>
    <input type="radio" name="g114" value="1" onclick="travaNA(14)" required <?=($dadoscertificado['g114'] == 1) ? 'checked' : ''; ?>>Sim
    <input type="radio" name="g114" value="2" onclick="travaNA(14)" <?=($dadoscertificado['g114'] == 2) ? 'checked' : ''; ?>>Não
    <input type="radio" name="g114" value="3" onclick="liberaNA(14)" <?=($dadoscertificado['g114'] == 3) ? 'checked' : ''; ?>>N/A
    Folha: <input type="text" name="g114f" id="g114f" size="30" maxlength="30"  value="<?=$dadoscertificado['g114f'];?>"><br>
</p>

<p>
    <label>1.15 - Consta Portaria de nomeação de fiscal?</label><br>
    <input type="radio" name="g115" value="1" onclick="travaNA(15)" required <?=($dadoscertificado['g115'] == 1) ? 'checked' : ''; ?>>Sim
    <input type="radio" name="g115" value="2" onclick="travaNA(15)" <?=($dadoscertificado['g115'] == 2) ? 'checked' : ''; ?>>Não
    <input type="radio" name="g115" value="3" onclick="liberaNA(15)" <?=($dadoscertificado['g115'] == 3) ? 'checked' : ''; ?>>N/A
    Folha: <input type="text" name="g115f" id="g115f" size="30" maxlength="30"  value="<?=$dadoscertificado['g115f'];?>"><br>
</p>

<p>
    <label>1.16 - Consta cópia da Ordem de Serviço/fornecimento para o objeto contratado?</label><br>
    <input type="radio" name="g116" value="1" onclick="travaNA(16)" required <?=($dadoscertificado['g116'] == 1) ? 'checked' : ''; ?>>Sim
    <input type="radio" name="g116" value="2" onclick="travaNA(16)" <?=($dadoscertificado['g116'] == 2) ? 'checked' : ''; ?>>Não
    <input type="radio" name="g116" value="3" onclick="liberaNA(16)" <?=($dadoscertificado['g116'] == 3) ? 'checked' : ''; ?>>N/A
    Folha: <input type="text" name="g116f" id="g116f" size="30" maxlength="30"  value="<?=$dadoscertificado['g116f'];?>"><br>
</p>

<p>
    <label>1.17 - Se tratando de aquisição de material permanente consta o tombamento e o numero patrimonial?</label><br>
    <input type="radio" name="g117" value="1" onclick="travaNA(17)" required <?=($dadoscertificado['g117'] == 1) ? 'checked' : ''; ?>>Sim
    <input type="radio" name="g117" value="2" onclick="travaNA(17)" <?=($dadoscertificado['g117'] == 2) ? 'checked' : ''; ?>>Não
    <input type="radio" name="g117" value="3" onclick="liberaNA(17)" <?=($dadoscertificado['g117'] == 3) ? 'checked' : ''; ?>>N/A
    Folha: <input type="text" name="g117f" id="g117f" size="30" maxlength="30"  value="<?=$dadoscertificado['g117f'];?>"><br>
</p>

<ul><li><b><i>Em caso de alteração ou exclusão, o campos abaixo é obrigatório.</i></b></li></ul>

<label>Justificativa</label>
<input class="primeiro" type="text" name="justificativa" id="justificativa" required maxlength="200" value="<?=$dadoscertificado['justificativa'];?>"><br>

<input type="hidden" name="usuarioid" value="<?=db_getsession("DB_id_usuario")?>">
<input type="hidden" name="dataalteracao" value="<?=date('Y-m-d');?>">

<input type="submit" name="alterar" value="Alterar">
<input type="submit" name="excluir" value="Excluir">


</fieldset>
</form>
</div>


<script>
  function liberaNA(id){
    console.log("Libera");    
    document.getElementById("g1"+id+"f").required = false;
  }

  function travaNA(id){
    console.log("Trava");
    document.getElementById("g1"+id+"f").required = true;
  }

function imprime(){
  var id_da_certificao = document.getElementById("idtabela").value;
    //console.log(id_da_certificao);
  
  //js_OpenJanelaIframe('top.corpo','imp_cert','imprimecertificado.php?idatabela='+id_da_certificao, "Certificado", true);
  //js_OpenJanelaIframe('top.corpo','imp_cert','imprimecertificado.php?idatabela='+id_da_certificao, "Certificado", true);

  var jan     = window.open('imprimecertificado.php?idatabela='+id_da_certificao,'','width='+(screen.availWidth-5)+',height='+(screen.availHeight-40)+',scrollbars=1,location=0 ');
  jan.moveTo(0,0);
}


  function valida1(event){    
    var key = window.event ? event.keyCode : event.which;
    if (event.keyCode >= 48 && event.keyCode <= 57 || event.keyCode == 47) {
      return true;
    } else {
      return false;
    }
  }

 var noprocesso = document.getElementById("noprocesso") ;

 noprocesso.addEventListener("change", function(){
  var dado = noprocesso.value;
  dado = dado.split("/");
  if(dado.length != 2){
    alert("Nº incorreto. Formato xxxxx/xxxx");
    noprocesso.value = '';
  } else{
    if(dado[1].length != 4){
      alert("Nº incorreto. Formato xxxxx/xxxx");
      noprocesso.value = '';
    } else {
      console.log(dado);
    }
  }
  
 }, false);




function valida2(){  
  var valor = document.getElementById("valornotafiscal").value;
  valor = valor + '';
  valor = parseInt(valor.replace(/[\D]+/g, ''));
  valor = valor + '';
  valor = valor.replace(/([0-9]{2})$/g, ",$1");

  if (valor.length > 6) {
      valor = valor.replace(/([0-9]{3}),([0-9]{2}$)/g, ".$1,$2");
  }

  document.getElementById("valornotafiscal").value = valor;
  if(valor == 'NaN') document.getElementById("valornotafiscal").value = '';
}

function mascaraMoeda(campo,evento){
  var tecla = (!evento) ? window.event.keyCode : evento.which;
  var valor  =  campo.value.replace(/[^\d]+/gi,'').reverse();
  var resultado  = "";
  var mascara = "###.###.###,##".reverse();
  for (var x=0, y=0; x<mascara.length && y<valor.length;) {
    if (mascara.charAt(x) != '#') {
      resultado += mascara.charAt(x);
      x++;
    } else {
      resultado += valor.charAt(y);
      y++;
      x++;
    }
  }
  campo.value = resultado.reverse();
}


function limpa(){
  document.getElementById("justificativa").value = "";  
}
</script>

<? 
db_menu(db_getsession("DB_id_usuario"),db_getsession("DB_modulo"),db_getsession("DB_anousu"),db_getsession("DB_instit"));
?>

</body>
</html>
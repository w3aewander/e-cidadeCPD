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

/*function verificaSequencia($sequencial){
  $sql = pg_query();
  $resultado;
}*/

function buscaDados($sequencial){
  $sql = pg_query("SELECT *, (select rh76_rhempenhofolha from rhempenhofolhaempenho where rh76_numemp = 922020) as empenho_folha from empempenho inner join cgm on cgm.z01_numcgm = empempenho.e60_numcgm inner join db_config on db_config.codigo = empempenho.e60_instit inner join orcdotacao on orcdotacao.o58_anousu = empempenho.e60_anousu and orcdotacao.o58_coddot = empempenho.e60_coddot inner join pctipocompra on pctipocompra.pc50_codcom = empempenho.e60_codcom inner join emptipo on emptipo.e41_codtipo = empempenho.e60_codtipo inner join concarpeculiar on concarpeculiar.c58_sequencial = empempenho.e60_concarpeculiar inner join db_config as a on a.codigo = orcdotacao.o58_instit inner join orctiporec on orctiporec.o15_codigo = orcdotacao.o58_codigo inner join orcfuncao on orcfuncao.o52_funcao = orcdotacao.o58_funcao inner join orcsubfuncao on orcsubfuncao.o53_subfuncao = orcdotacao.o58_subfuncao inner join orcprograma on orcprograma.o54_anousu = orcdotacao.o58_anousu and orcprograma.o54_programa = orcdotacao.o58_programa inner join orcelemento on orcelemento.o56_codele = orcdotacao.o58_codele and orcelemento.o56_anousu = orcdotacao.o58_anousu inner join orcprojativ on orcprojativ.o55_anousu = orcdotacao.o58_anousu and orcprojativ.o55_projativ = orcdotacao.o58_projativ inner join orcorgao on orcorgao.o40_anousu = orcdotacao.o58_anousu and orcorgao.o40_orgao = orcdotacao.o58_orgao inner join orcunidade on orcunidade.o41_anousu = orcdotacao.o58_anousu and orcunidade.o41_orgao = orcdotacao.o58_orgao and orcunidade.o41_unidade = orcdotacao.o58_unidade where empempenho.e60_numemp = {$sequencial}");
  $resultado = pg_fetch_all($sql);
  return $resultado[0];
}

function verificaSeq1($sequencial){
  $sql = pg_query("SELECT max(cseq1) as seq1 FROM certificacaoconformidade WHERE e60_numemp = {$sequencial}");
  $resultado = pg_fetch_all($sql);
  return $resultado[0]["seq1"];
}

//testa($_POST); die("COnfere");

$dados = buscaDados($_POST["sequencialemp"]);
//testa($dados);
//die("Confere dados");



$dnoempenho = $dados["e60_codemp"]."/".$dados["e60_anousu"];
$dordenador = $dados["o40_descr"];
$dfornecedor = $dados["z01_nome"];
$dorgao = $dados["o58_orgao"];

$vseq1 = verificaSeq1($dados["e60_numemp"]);
//var_dump($vseq1); die("Confere");

//$vseq2 = verificaSeq2();

//Certificado XX.XXXXX.XX.XX/XXXX, 
//$corgao . '.' . $cemp . '.' . $vseq1 . '.' . $vseq2 . '/' . $dados["e60_anousu"]
if(strlen($dorgao) == 1){
  $corgao = "0".$dorgao;
}else{
  $corgao = $dorgao;
}

//var_dump($dados["e60_codemp"]); 
//echo strlen($dados["e60_codemp"]);

//die("Confere");

if(strlen($dados["e60_codemp"]) == 1){
  $cemp = "0000" . $dados["e60_codemp"];
} elseif(strlen($dados["e60_codemp"]) == 2){
  $cemp = "000" . $dados["e60_codemp"];
} elseif(strlen($dados["e60_codemp"]) == 3){  
  $cemp = "00" . $dados["e60_codemp"];
} elseif(strlen($dados["e60_codemp"]) == 4){
  $cemp = "0" . $dados["e60_codemp"];
} elseif(strlen($dados["e60_codemp"]) == 5){
  $cemp = $dados["e60_codemp"];
}


if(isset($vseq1)){  
  $vseq1 = $vseq1 + 1;
  $vseq1 = (string)$vseq1;
  if(strlen($vseq1) == 1){
    $vseq1 = "0".$vseq1;
  }
} else {
  $vseq1 = "01";
}


$vseq2 = "00";
$certificado = $corgao . '.' . $cemp . '.' . $vseq1 . '.' . $vseq2 . '/' . $dados["e60_anousu"];


$cgc = preg_replace("/[^0-9]/", "", $dados["z01_cgccpf"]);
$qtd = strlen($cgc);

if($qtd == 11 ) { 
  $dcpf = substr($cgc, 0, 3) . '.' .substr($cgc, 3, 3) . '.' . substr($cgc, 6, 3) . '.' . substr($cgc, 9, 2);
} elseif($qtd == 14) {
  $dcpf = substr($cgc, 0, 2) . '.' . substr($cgc, 2, 3) . '.' . substr($cgc, 5, 3) . '/' . substr($cgc, 8, 4) . '-' . substr($cgc, -2);
} else {
  $cpf = "";
}


$e60_numemp = $dados["e60_numemp"];
$e60_codemp = $dados["e60_codemp"];
$e60_anousu = $dados["e60_anousu"];
$e60_instit = $dados["e60_instit"];
$o58_orgao = $dados["o58_orgao"];
$cano = $dados["e60_anousu"];
$z01_numcgm = $dados["z01_numcgm"];

 


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
  .containerfrm{
    width: 500px; clear: both;margin:25px auto 0;
  }

  .primeiro{
    width: 100%; clear: both;margin-bottom: 10px;
  }

  .containerfrm label{font-weight: bold}
  <?php /* ?>
  form p {text-align: left}
  <?php  */?>
</style>

<div class="containerfrm">
  <button><a href="certificadoconformidade.php" style="text-decoration: none">Voltar</a></button>
<form name="form1" method="post" action="geracertificadoconformidade.php">
  <fieldset>
    <legend><strong>Dados Certificado</strong></legend>

    <input type="hidden" name="e60_numemp" value="<?=$e60_numemp;?>">
    <input type="hidden" name="e60_codemp" value="<?=$e60_codemp;?>">
    <input type="hidden" name="e60_anousu" value="<?=$e60_anousu;?>">
    <input type="hidden" name="e60_instit" value="<?=$e60_instit;?>">
    <input type="hidden" name="o58_orgao" value="<?=$o58_orgao;?>">
    <input type="hidden" name="cempenho" value="<?=$cemp?>">
    <input type="hidden" name="cseq1" value="<?=$vseq1;?>">
    <input type="hidden" name="cseq2" value="<?=$vseq2;?>">
    <input type="hidden" name="cano" value="<?=$cano;?>">
    <input type="hidden" name="z01_numcgm" value="<?=$z01_numcgm;?>">

    <label>Nº Certificado</label>
    <input class="primeiro" type="text" name="nocertificado" readonly value="<?=$certificado;?>" style="background-color:#DEB887"><br>

    <label>Nº Processo</label>
    <input class="primeiro" type="text" name="noprocesso" id="noprocesso" required maxlength="25"><br>

    <label>Data da Geração</label>
    <input class="primeiro" type="date" name="datageracao" required><br>

    <label>Ordenador da Despesa</label>
    <input class="primeiro" type="text" name="ordenadordadespesa" required readonly value="<?=$dordenador;?>"><br>

    <label>Instrumento Jurídico</label>
    <input class="primeiro" type="text" name="instrumentojuridico" maxlength="40" required><br>

    <label>Nº do Empenho</label>
    <input class="primeiro" type="text" name="noempenho" readonly value="<?=$dnoempenho;?>"><br>

    <label>Nº da Nota Fiscal</label>
    <input class="primeiro" type="text" name="nonotafiscal" required maxlength="10"><br>

    <label>Valor da Nota Fiscal</label>
    <input class="primeiro" type="text" name="valornotafiscal" id="valornotafiscal" required maxlength="13" onkeyup="mascaraMoeda(this, event)"><br>

    <label>Fornecedor/Beneficiário</label>
    <input class="primeiro" type="text" name="fornecedor" readonly required value="<?=$dfornecedor;?>"><br>

    <label>CNPJ/CPF - Fornecedor/Beneficiário</label>
    <input class="primeiro" type="text" name="cpffornecedor" readonly required value="<?=$dcpf;?>">
  
  <p>
    <label>1.1 – A nota fiscal foi emitida contra a Município/Prefeitura Municipal de volta Redonda?</label><br>
    <input type="radio" name="g11" value="1" onclick="travaNA(1)" required>Sim
    <input type="radio" name="g11" value="2" onclick="travaNA(1)">Não
    <input type="radio" name="g11" value="3" onclick="liberaNA(1)">N/A
    Anexo: <input type="text" id="g11f" name="g11f" size="30" maxlength="30" required><br>
</p>

<p>
    <label>1.2 – O credor informado no empenho é o mesmo dos demais documentos constantes do processo?</label><br>
    <input type="radio" name="g12" value="1" onclick="travaNA(2)"  required>Sim
    <input type="radio" name="g12" value="2" onclick="travaNA(2)">Não
    <input type="radio" name="g12" value="3" onclick="liberaNA(2)">N/A
    Anexo: <input type="text" id="g12f" name="g12f" size="30" maxlength="30" required><br>
</p>

<p>
    <label>1.3 – A Nota de Empenho foi emitida até a data de início da realização da despesa e assinada pelo ordenador da despesa?</label><br>
    <input type="radio" name="g13" value="1" onclick="travaNA(3)" required>Sim
    <input type="radio" name="g13" value="2" onclick="travaNA(3)">Não
    <input type="radio" name="g13" value="3" onclick="liberaNA(3)">N/A
    Anexo: <input type="text" id="g13f" name="g13f" size="30" maxlength="30" required><br>
</p>

<p>
    <label>1.4 – A despesa foi classificada na natureza de despesa adequada ao objeto do contrato?</label><br>
    <input type="radio" name="g14" value="1" onclick="travaNA(4)" required>Sim
    <input type="radio" name="g14" value="2" onclick="travaNA(4)">Não
    <input type="radio" name="g14" value="3" onclick="liberaNA(4)">N/A
    Anexo: <input type="text" id="g14f" name="g14f" size="30" maxlength="30" required><br>
</p>

<p>
    <label>1.5 - O credor da(s) nota(s) de empenho(s) coincide com o do emitente do(s) documento(s) comprobatório(s) ?</label><br>
    <input type="radio" name="g15" value="1" onclick="travaNA(5)" required>Sim
    <input type="radio" name="g15" value="2" onclick="travaNA(5)">Não
    <input type="radio" name="g15" value="3" onclick="liberaNA(5)">N/A
    Anexo: <input type="text" id="g15f" name="g15f" size="30" maxlength="30" required><br>
</p>

<p>
    <label>1.6 - Os itens descritos na Nota Fiscal guardam paridade com os itens que constam na(s) Nota(s) de Empenho e NRM (Nota de Recebimento de Material)?</label><br>
    <input type="radio" name="g16" value="1" onclick="travaNA(6)" required>Sim
    <input type="radio" name="g16" value="2" onclick="travaNA(6)">Não
    <input type="radio" name="g16" value="3" onclick="liberaNA(6)">N/A
    Anexo: <input type="text" id="g16f" name="g16f" size="30" maxlength="30" required><br>
</p>

<p>
    <label>1.7 - A Nota Fiscal está devidamente atestada por dois funcionários e se for o caso, autorizada pelo Ordenador da Despesa?</label><br>
    <input type="radio" name="g17" value="1" onclick="travaNA(7)" required>Sim
    <input type="radio" name="g17" value="2" onclick="travaNA(7)">Não
    <input type="radio" name="g17" value="3" onclick="liberaNA(7)">N/A
    Anexo: <input type="text" id="g17f" name="g17f" size="30" maxlength="30" required><br>
</p>

<p>
    <label>1.8 - Consta calculo de ISS efetuado pela Fiscalização Municipal?</label><br>
    <input type="radio" name="g18" value="1" onclick="travaNA(8)" required>Sim
    <input type="radio" name="g18" value="2" onclick="travaNA(8)">Não
    <input type="radio" name="g18" value="3" onclick="liberaNA(8)">N/A
    Anexo: <input type="text" id="g18f" name="g18f" size="30" maxlength="30" required><br>
</p>

<p>
    <label>1.9 – Em se tratando de Nota Fiscal Eletrônica a sua autenticidade foi verificada?</label><br>
    <input type="radio" name="g19" value="1" onclick="travaNA(9)" required>Sim
    <input type="radio" name="g19" value="2" onclick="travaNA(9)">Não
    <input type="radio" name="g19" value="3" onclick="liberaNA(9)">N/A
    Anexo: <input type="text" id="g19f" name="g19f" size="30" maxlength="30" required><br>
</p>

<p>
    <label>1.10 - Consta do processo uma cópia do termo de contrato/aditivo/convênio/ajuste/rescisão entre o Governo Municipal e o Fornecedor? </label><br>
    <input type="radio" name="g110" value="1" onclick="travaNA(10)" required>Sim
    <input type="radio" name="g110" value="2" onclick="travaNA(10)">Não
    <input type="radio" name="g110" value="3" onclick="liberaNA(10)">N/A
    Anexo: <input type="text" id="g110f" name="g110f" size="30" maxlength="30" required><br>
</p>

<p>
    <label>1.11 - Foram observadas as regras previstas no Edital/Ata de registro de preço e no contrato?</label><br>
    <input type="radio" name="g111" value="1"  onclick="travaNA(11)"required>Sim
    <input type="radio" name="g111" value="2" onclick="travaNA(11)">Não
    <input type="radio" name="g111" value="3" onclick="liberaNA(11)">N/A
    Anexo: <input type="text" id="g111f" name="g111f" size="30" maxlength="30" required><br>
</p>

<p>
    <label>1.12 – Se o objeto do processo for referente a recurso vinculado (convênio), consta uma cópia do Contrato de Repasse?</label><br>
    <input type="radio" name="g112" value="1" onclick="travaNA(12)" required>Sim
    <input type="radio" name="g112" value="2" onclick="travaNA(12)">Não
    <input type="radio" name="g112" value="3" onclick="liberaNA(12)">N/A
    Anexo: <input type="text" id="g112f" name="g112f" size="30" maxlength="30" required><br>
</p>

<p>
    <label>1.13 - A entrega do bem ou serviço está de acordo com o cronograma previsto? (semanalmente; quinzenalmente; mensalmente ou entrega imediata)?</label><br>
    <input type="radio" name="g113" value="1" onclick="travaNA(13)" required>Sim
    <input type="radio" name="g113" value="2" onclick="travaNA(13)">Não
    <input type="radio" name="g113" value="3" onclick="liberaNA(13)">N/A
    Anexo: <input type="text" id="g113f" name="g113f" size="30" maxlength="30" required><br>
</p>

<p>
    <label>1.14 - Consta do processo a Regularidade Fiscal (FGTS - INSS - CNDT)? </label><br>
    <input type="radio" name="g114" value="1" onclick="travaNA(14)" required>Sim
    <input type="radio" name="g114" value="2" onclick="travaNA(14)">Não
    <input type="radio" name="g114" value="3" onclick="liberaNA(14)">N/A
    Anexo: <input type="text" id="g114f" name="g114f" size="30" maxlength="30" required><br>
</p>

<p>
    <label>1.15 - Consta Portaria de nomeação de fiscal?</label><br>
    <input type="radio" name="g115" value="1" onclick="travaNA(15)" required>Sim
    <input type="radio" name="g115" value="2" onclick="travaNA(15)">Não
    <input type="radio" name="g115" value="3" onclick="liberaNA(15)">N/A
    Anexo: <input type="text" id="g115f" name="g115f" size="30" maxlength="30" required><br>
</p>

<p>
    <label>1.16 - Consta cópia da Ordem de Serviço/fornecimento para o objeto contratado?</label><br>
    <input type="radio" name="g116" value="1"  onclick="travaNA(16)"required>Sim
    <input type="radio" name="g116" value="2" onclick="travaNA(16)">Não
    <input type="radio" name="g116" value="3" onclick="liberaNA(16)">N/A
    Anexo: <input type="text" id="g116f" name="g116f" size="30" maxlength="30" required><br>
</p>

<p>
    <label>1.17 - Se tratando de aquisição de material permanente consta o tombamento e o numero patrimonial?</label><br>
    <input type="radio" name="g117" value="1" onclick="travaNA(17)" required>Sim
    <input type="radio" name="g117" value="2" onclick="travaNA(17)">Não
    <input type="radio" name="g117" value="3" onclick="liberaNA(17)">N/A
    Anexo: <input type="text" id="g117f" name="g117f" size="30" maxlength="30" required><br>
</p>



    





  

  
  <!--<input name="pesquisa" type="button" onclick='js_abre();'  value="Pesquisa">-->
  <p>
    <input name="prosseguir" type="submit" value="Gera Certificado">
  </p>
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


  function valida1(event){    
    var key = window.event ? event.keyCode : event.which;
    if (event.keyCode >= 48 && event.keyCode <= 57 || event.keyCode == 47) {
      return true;
    } else {
      return false;
    }
  }

 var noprocesso = document.getElementById("noprocesso") ;
/*
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
*/



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




</script>


<? 
db_menu(db_getsession("DB_id_usuario"),db_getsession("DB_modulo"),db_getsession("DB_anousu"),db_getsession("DB_instit"));
?>

</body>
</html>
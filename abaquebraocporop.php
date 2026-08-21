<?php

require_once(modification("libs/db_stdlib.php"));
require_once(modification("libs/db_conecta.php"));
require_once(modification("libs/db_usuariosonline.php"));
require_once(modification("libs/db_liborcamento.php"));
require_once(modification("classes/db_lote_classe.php"));
require_once(modification("dbforms/db_funcoes.php"));
require_once(modification("dbforms/db_classesgenericas.php"));
require_once(modification("classes/db_empempenho_classe.php"));
include("classes/db_orcdotacao_classe.php");

function testa($var){
  echo "<pre>";
  print_r($var);
  echo "</pre>";
}

function temCertificacao($op){
  $sql = pg_query("SELECT * FROM certificacaoconformidade WHERE e50_codord = {$op} ORDER BY id DESC LIMIT 1");
  $resultado = pg_fetch_all($sql);
  return $resultado[0];
}

function retiraDados($op){
  $sql = pg_query("SELECT e50_codord, e50_dataquebraordem, e50_justificativaquebraordem, e50_numedi FROM pagordem WHERE e50_codord = {$op}");
  $resultado = pg_fetch_all($sql);
  return $resultado[0];
}
 
//---  parser POST/GET
parse_str($_SERVER["QUERY_STRING"]);
db_postmemory($_POST);

//---- instancia classes
$clempempenho = new cl_empempenho;
$clselorcdotacao = new cl_selorcdotacao;
$clorcdotacao = new cl_orcdotacao;
$clorcdotacao->rotulo->label();
$aux = new cl_arquivo_auxiliar;

//--- cria rotulos e labels
$clempempenho->rotulo->label();

$cllote = new cl_lote;
$cliframe_seleciona = new cl_iframe_seleciona;

$cllote->rotulo->label();
$clrotulo = new rotulocampo;
$clrotulo->label("z01_nome");

$anousu = db_getsession("DB_anousu");

if(isset($_POST["salvar"])){
    
    if(empty($_POST["datapublicacao"]) || empty($_POST["numedi"]) ){
      if(isset($_POST["numedi2"])){
        $sequencial_empenho = $_POST["empenho"][0];
        $data_publicacao = implode("-", array_reverse(explode("/", $_POST["datapublicacao2"])));
        $link_publicacao = $_POST["link2"];
        $edicao_publicacao = $_POST["numedi2"];
        $codord = $_POST["op"];
      }else {
        echo "<script>alert('Preencha todos os campos');</script>";        
      }
    } else{
      $sequencial_empenho = $_POST["empenho"][0];
      $data_publicacao = implode("-", array_reverse(explode("/", $_POST["datapublicacao"])));
      $link_publicacao = $_POST["link"];
      $edicao_publicacao = $_POST["numedi"];
      $codord = $_POST["op"];
    }
    
    //Inserção
    $sql = pg_query($conn, "UPDATE pagordem SET e50_dataquebraordem = '{$data_publicacao}', e50_justificativaquebraordem = '{$link_publicacao}', e50_numedi = {$edicao_publicacao} WHERE e50_codord = $codord");

    if(!$sql){
      $error = pg_last_error($conn);
    } else {
      //Busca a certificação pela OP
      $certificacao = temCertificacao($codord);

      if($certificacao){
        //Altera nocertificado e cseq2 (incrementa 1)
        $dados["e60_numemp"] = $certificacao["e60_numemp"];
        $dados["e60_codemp"] = $certificacao["e60_codemp"];
        $dados["e60_anousu"] = $certificacao["e60_anousu"];
        $dados["e60_instit"] = $certificacao["e60_instit"];
        $dados["e50_codord"] = $certificacao["e50_codord"];
        $dados["o58_orgao"] = $certificacao["corgao"];
        $dados["cempenho"] = $certificacao["cempenho"];
        $dados["cseq1"] = $certificacao["cseq1"];
        $dados["cseq2"] = $certificacao["cseq2"] + 1;    
        $dados["cano"] = $certificacao["cano"];
        $dados["nocertificado"] = $certificacao["nocertificado"];
        $vseq2 = $dados["cseq2"];
        $vseq2 = (string)$vseq2;
        if(strlen($vseq2) == 1){$vseq2 = "0".$vseq2;}  
        
        $dados["nocertificado"] = substr_replace($dados["nocertificado"], $vseq2, 12, 2);
        $dados["noprocesso"] = $certificacao["noprocesso"];
        $dados["datageracao"] = $certificacao["datageracao"];
        $dados["ordenadordadespesa"] = $certificacao["ordenadordadespesa"];
        $dados["instrumentojuridico"] = $certificacao["instrumentojuridico"];
        $dados["noempenho"] = $certificacao["noempenho"];
        $dados["nonotafiscal"] = $certificacao["nonotafiscal"];
        $dados["valornotafiscal"] = $certificacao["valornotafiscal"];
        $dados["z01_numcgm"] = $certificacao["z01_numcgm"];
        $dados["fornecedor"] = $certificacao["z01_nome"];
        $dados["cpffornecedor"] = $certificacao["z01_cgccpf"];
        $dados["g11"] = $certificacao["g11"];
        $dados["g11f"] = $certificacao["g11f"];
        $dados["g12"] = $certificacao["g12"];
        $dados["g12f"] = $certificacao["g12f"];
        $dados["g13"] = $certificacao["g13"];
        $dados["g13f"] = $certificacao["g13f"];
        $dados["g14"] = $certificacao["g14"];
        $dados["g14f"] = $certificacao["g14f"];
        $dados["g15"] = $certificacao["g15"];
        $dados["g15f"] = $certificacao["g15f"];
        $dados["g16"] = $certificacao["g16"];
        $dados["g16f"] = $certificacao["g16f"];
        $dados["g17"] = $certificacao["g17"];
        $dados["g17f"] = $certificacao["g17f"];
        $dados["g18"] = $certificacao["g18"];
        $dados["g18f"] = $certificacao["g18f"];
        $dados["g19"] = $certificacao["g19"];
        $dados["g19f"] = $certificacao["g19f"];
        $dados["g110"] = $certificacao["g110"];
        $dados["g110f"] = $certificacao["g110f"];
        $dados["g111"] = $certificacao["g111"];
        $dados["g111f"] = $certificacao["g111f"];
        $dados["g112"] = $certificacao["g112"];
        $dados["g112f"] = $certificacao["g112f"];
        $dados["g113"] = $certificacao["g113"];
        $dados["g113f"] = $certificacao["g113f"];
        $dados["g114"] = $certificacao["g114"];
        $dados["g114f"] = $certificacao["g114f"];
        $dados["g115"] = $certificacao["g115"];
        $dados["g115f"] = $certificacao["g115f"];
        $dados["g116"] = $certificacao["g116"];
        $dados["g116f"] = $certificacao["g116f"];
        $dados["g117"] = $certificacao["g117"];
        $dados["g117f"] = $certificacao["g117f"];

        $sqlinsercao = "INSERT INTO certificacaoconformidade(e60_numemp, e60_codemp, e60_anousu, e60_instit, e50_codord, corgao, cempenho, cseq1, cseq2,cano, nocertificado, noprocesso, datageracao, ordenadordadespesa, instrumentojuridico, noempenho, nonotafiscal, valornotafiscal, z01_numcgm, z01_nome, z01_cgccpf, g11, g11f, g12, g12f, g13, g13f, g14, g14f, g15, g15f, g16, g16f, g17, g17f, g18, g18f, g19, g19f, g110, g110f, g111, g111f, g112, g112f, g113, g113f, g114, g114f, g115, g115f, g116, g116f, g117, g117f) VALUES({$dados["e60_numemp"]}, '{$dados["e60_codemp"]}', {$dados["e60_anousu"]}, {$dados["e60_instit"]}, {$dados["e50_codord"]}, {$dados["o58_orgao"]}, {$dados["cempenho"]}, {$dados["cseq1"]}, {$dados["cseq2"]}, {$dados["cano"]}, '{$dados["nocertificado"]}', '{$dados["noprocesso"]}', '{$dados["datageracao"]}', '{$dados["ordenadordadespesa"]}', '{$dados["instrumentojuridico"]}', '{$dados["noempenho"]}', '{$dados["nonotafiscal"]}', {$dados["valornotafiscal"]}, {$dados["z01_numcgm"]}, '{$dados["fornecedor"]}', '{$dados["cpffornecedor"]}', {$dados["g11"]}, '{$dados["g11f"]}', {$dados["g12"]}, '{$dados["g12f"]}', {$dados["g13"]}, '{$dados["g13f"]}', {$dados["g14"]}, '{$dados["g14f"]}', {$dados["g15"]}, '{$dados["g15f"]}', {$dados["g16"]}, '{$dados["g16f"]}', {$dados["g17"]}, '{$dados["g17f"]}', {$dados["g18"]}, '{$dados["g18f"]}', {$dados["g19"]}, '{$dados["g19f"]}', {$dados["g110"]}, '{$dados["g110f"]}', {$dados["g111"]}, '{$dados["g111f"]}', {$dados["g112"]}, '{$dados["g112f"]}', {$dados["g113"]}, '{$dados["g113f"]}', {$dados["g114"]}, '{$dados["g114f"]}', {$dados["g115"]}, '{$dados["g115f"]}', {$dados["g116"]}, '{$dados["g116f"]}', {$dados["g117"]}, '{$dados["g117f"]}')";        

        $sqlc = pg_query($conn, $sqlinsercao);
        if(!$sqlc){
          $error = pg_last_error($conn);
        }
      }
      //Repete os dados e muda o sequencial 2 em uma nova inserção
      echo "<script>alert('Justificativa cadastrada com sucesso.');</script>";
    }
 //post 
}elseif(isset($_POST["retirar"])){
  $codord = $_POST["op"];
  $dados = retiraDados($codord);
  $dq = $dados["e50_dataquebraordem"];
  $jq = $dados["e50_justificativaquebraordem"];
  $ne = $dados["e50_numedi"];

  $sql1 = pg_query($conn, "INSERT INTO dadosretirada(e50_codord, e50_dataquebraordem, e50_justificativaquebraordem, e50_numedi) VALUES({$codord}, '{$dq}', '{$jq}', {$ne})");
    
  if(!$sql1){
    $error = pg_last_error($conn);
  } else{
    $atualiza = pg_query($conn, "UPDATE pagordem SET e50_dataquebraordem = null, e50_justificativaquebraordem = '', e50_numedi = 0 WHERE e50_codord = $codord");
    if(!$atualiza){
      $error = pg_last_error($conn);
    }
  }
  echo "<script>alert('OP retirada da quebra.');</script>";
}

?>
<html>
<head>
  <title>Microsist</title>
  <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
  <meta http-equiv="Expires" CONTENT="0">
  <script language="JavaScript" type="text/javascript" src="scripts/scripts.js"></script>
  <script language="JavaScript" type="text/javascript" src="scripts/AjaxRequest.js"></script>
    <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
  <link href="estilos.css" rel="stylesheet" type="text/css">
  <style>

  </style>
</head>
<body bgcolor=#CCCCCC bgcolor="#CCCCCC">
<br/>
<center>
  <form name="form1" method="post" action="abaquebraocporop.php">
    <input  name="filtra_despesa" id="filtra_despesa" type="hidden" value="" >
    <fieldset style="width: 800px;position: relative;min-height: 200px">
      <legend><b>Empenho</b></legend>
      <table style="width: 100%" border='0'>
          <tr>
              <td nowrap width="50%">
                  <?
                  // $aux = new cl_arquivo_auxiliar;
                  $aux->cabecalho = "<strong>Empenhos</strong>";
                  $aux->codigo = "e60_numemp"; //chave de retorno da func
                  $aux->descr  = "z01_nome";   //chave de retorno
                  $aux->nomeobjeto = 'empenho';
                  $aux->funcao_js = 'js_mostra';
                  $aux->funcao_js_hide = 'js_mostra1';
                  $aux->sql_exec  = "";
                  $aux->func_arquivo = "func_empempenho.php";  //func a executar
                  $aux->nomeiframe = "db_iframe_empempenho";
                  $aux->localjan = "";
                  $aux->onclick = "";
                  $aux->db_opcao = 2;
                  $aux->tipo = 2;
                  $aux->top = 1;
                  $aux->linhas = 1;
                  $aux->vwhidth = 400;
                  $aux->funcao_gera_formulario();
                  ?>
              </td>
          </tr>

                   <div id="vazio" style="position: absolute;bottom: 0;margin-bottom: 15px"></div> 
          
            <tr id="linha1">
              <td style="width: 120px;display: inline-block;"><b>Data da Publicação:</b></td>
              <td style="display: inline-block;margin-right: 70px">
                <!--<input type="text" name="datapublicacao" id="datapublicacao" readonly>-->
                <?php db_inputdata("datapublicacao", null, null, null, true, "text", 1); ?>
              </td>
              <td style="width: 80px;display: inline-block;"><b>Nº da Edição:</b></td>
              <td style="display: inline-block;">
                <input style="background-color: #DEB887" type="text" name="numedi" id="numedi" readonly>
              </td>
            </tr>
          
            <tr id="linha2">
              <td style="width: 30px;display: inline-block;"><b>Link:</b></td>
              <td style="display: inline-block;">
                <input type="text" name="link" id="link" readonly size="80">
              </td>
            </tr>
        </table>
    <div style="position: absolute; top: 0;right: 15px">
      <p><b>Nº da OP a ser quebrada:</b></p>
      <p><input type="text" name="op" id="op" onKeyPress="return js_mascara(event);"></p>
      <p><button type="button" id="vop">Verifica OP</button></p>
    </div>
    </fieldset>

    <br>
      <br>
      <input type="submit" name="salvar" value="Salvar Dados da Quebra" id="salvar">
      <input type="submit" name="retirar" value="Retirar Dados da Quebra" id="retirar" style="display: none">
      
</center>
</form>


<script>  

  document.getElementById('op').readOnly = true;
  document.getElementById('op').style.backgroundColor = '#DEB887';

  document.getElementById('datapublicacao').readOnly = true;
  document.getElementById('datapublicacao').style.backgroundColor = '#DEB887';
  //document.getElementById('numedi').readOnly = false;  
  //document.getElementById('numedi').style.backgroundColor = '#DEB887';
  //document.getElementById('numedi').value = 0;

  document.getElementById('link').style.backgroundColor = '#DEB887';
  document.getElementById('link').value = "";

var bt_lanca = document.getElementById("db_lanca");

  bt_lanca.addEventListener("click", function(){
    var sequencial = document.getElementById("e60_numemp").value;    
    document.getElementById('op').readOnly = false;
    document.getElementById('op').style.backgroundColor = '#FFFFFF';

    var bvop = document.getElementById("vop");

    bvop.addEventListener("click", function(){
      var op = document.getElementById("op").value;
      var xhr = new XMLHttpRequest();
      xhr.onreadystatechange = function(){
        if((xhr.readyState === 4) && (xhr.status === 200)){
          //Não tem justificativa
          if(xhr.responseText.length > 1600){
            console.log("Sem justificativa");
            document.getElementById('datapublicacao').readOnly = false;
            document.getElementById('datapublicacao').style.backgroundColor = '#FFFFFF';
            document.getElementById('numedi').readOnly = false;
            document.getElementById('numedi').style.backgroundColor = '#FFFFFF';
            document.getElementById("vazio").style.display = "none";
          } else if(xhr.responseText.length < 100){
            alert("O Número da OP digitada não corresponde ao empenho escolhido!");
            window.location.href = "abaquebraocporop.php";
          } else {
            console.log("Com justificativa");
            document.getElementById("linha1").style.display = "none";
            document.getElementById("linha2").style.display = "none";
            document.getElementById("vazio").innerHTML = xhr.responseText;
            //Tem Justificativa
          }
      }
    }
    xhr.open("GET","verificajustificativa.php?sequencial="+sequencial+"&op="+op , true);
    xhr.send(null);      
    }, false);

    
  }, false);
  

function js_mascara(evt){
    var evt = (evt) ? evt : (window.event) ? window.event : "";

    if((evt.charCode >46 && evt.charCode <58) || evt.charCode ==0){
      return true;
    }else{
      return false;
    }
  }


document.getElementById("numedi").addEventListener("blur", mpreencherjustificativa);
document.getElementById("datapublicacao").addEventListener("blur", mpreencherjustificativa);
document.getElementById("datapublicacao").addEventListener("change", mpreencherjustificativa);

  function mpreencherjustificativa() {    
      var dataquebra = document.getElementById('datapublicacao').value;      
      if(dataquebra.length > 0) {
          var ano = dataquebra.substring(6, 10);
          dataquebra = dataquebra.substring(6, 10) + "-" + dataquebra.substring(3, 5) + "-" + dataquebra.substring(0, 2);
          var numedi = document.getElementById('numedi').value;
          if(numedi.length > 0)
            document.getElementById('link').value = "http://new.voltaredonda.rj.gov.br/images/Documentos/VRDestaques/" + ano + "/" + dataquebra + "_" + numedi + ".pdf";
      }
  }


function libera(){
  document.getElementById('numedi').readOnly = false;
  document.getElementById('numedi').style.backgroundColor = '#FFFFFF';
  document.getElementById('datapublicacao').readOnly = false;
  document.getElementById('datapublicacao').style.backgroundColor = '#FFFFFF';
  document.getElementById('retirar').style.display = 'none';
  document.getElementById('salvar').style.display = 'inline-block';
}

function libera2(){
  document.getElementById('retirar').style.display = 'inline-block';
  document.getElementById('salvar').style.display = 'none';
}

function fecha(){  
  document.getElementById('numedi').readOnly = true;
  document.getElementById('numedi').style.backgroundColor = '#DEB887';  
  document.getElementById('datapublicacao').readOnly = true;
  document.getElementById('datapublicacao').style.backgroundColor = '#DEB887';  

  document.getElementById('retirar').style.display = 'none';
  document.getElementById('salvar').style.display = 'inline-block';
}

function arrumadata(val){
  var pass = val.value;
  var expr = /[0123456789]/;

  for (i = 0; i < pass.length; i++) {
    var lchar = val.value.charAt(i);
    var nchar = val.value.charAt(i + 1);
    if (i == 0) {
      if ((lchar.search(expr) != 0) || (lchar > 3)) {
        val.value = "";
      }
    } else if (i == 1) {
      if (lchar.search(expr) != 0) {
        var tst1 = val.value.substring(0, (i));
        val.value = tst1;
        continue;
      }

      if ((nchar != '/') && (nchar != '')) {
        var tst1 = val.value.substring(0, (i) + 1);
        if (nchar.search(expr) != 0)
          var tst2 = val.value.substring(i + 2, pass.length);
        else
          var tst2 = val.value.substring(i + 1, pass.length);

        val.value = tst1 + '/' + tst2;
      }

    } else if (i == 4) {

      if (lchar.search(expr) != 0) {
        var tst1 = val.value.substring(0, (i));
        val.value = tst1;
        continue;
      }

      if ((nchar != '/') && (nchar != '')) {
        var tst1 = val.value.substring(0, (i) + 1);

        if (nchar.search(expr) != 0)
          var tst2 = val.value.substring(i + 2, pass.length);
        else
          var tst2 = val.value.substring(i + 1, pass.length);

        val.value = tst1 + '/' + tst2;
      }
    }

    if (i >= 6) {
      if (lchar.search(expr) != 0) {
        var tst1 = val.value.substring(0, (i));
        val.value = tst1;
      }
    }
  }

  if (pass.length > 10)
    val.value = val.value.substring(0, 10);
  return true;
}

  function js_alterarempenho(){

      var F = document.getElementById("empenho").options;
      var ops = document.form1.ops.value;

      var strempenhos = "";

      for(var i = 0;i < F.length;i++)
      {
          if(i == 0)
              strempenhos = F[i].value;
          else
              strempenhos += "," +  F[i].value;
      }
      
      if(F.length > 0) {
          $.ajax({
              url: 'emp2_empliqpag07_novo.php',
              type: 'POST',
              data: ({
                  ops: ops,
                  strempenhos: strempenhos
              }),
              success: function (results) {
                  alert("Empenhos alterados com sucesso.")
              }
          });
      }
      else
      {
          alert("Selecione pelo menos um empenho.")
      }
  }

  function js_pesquisa_empenho(mostra){
      if(mostra==true){
          js_OpenJanelaIframe('top.corpo','db_iframe_empempenho','func_empempenho.php?funcao_js=parent.js_mostraempenho1|e60_numemp','Pesquisa',true);
      }else{
          if(document.form1.e60_numemp.value != ''){
              js_OpenJanelaIframe('top.corpo','db_iframe_empempenho','func_empempenho.php?pesquisa_chave='+document.form1.e60_numemp.value+'&funcao_js=parent.js_mostraempenho','Pesquisa',false);
          }else{
              document.form1.z01_nome1.value = '';
          }
      }
  }
  function js_mostraempenho(erro,chave){
      document.form1.z01_nome1.value = chave;
      document.getElementById('op').readOnly = false;
      document.getElementById('op').style.backgroundColor = '#FFFFFF';
      if(erro==true){
          document.form1.e60_numemp.focus();
          document.form1.z01_nome1.value = '';
          document.getElementById('op').readOnly = true;
          document.getElementById('op').style.backgroundColor = '#DEB887';
      }
  }
  function js_mostraempenho1(chave1){
      document.form1.e60_numemp.value = chave1;
      db_iframe_empempenho.hide();
  }
</script>

</center>
</body>
</html>
<?php

require_once("libs/db_stdlib.php");
require_once("libs/db_utils.php");
require_once("std/db_stdClass.php");
require_once("libs/db_libdicionario.php");
require_once("libs/db_app.utils.php");
require_once("libs/db_conecta.php");
require_once("libs/db_sessoes.php");
require_once("libs/db_usuariosonline.php");
require_once("classes/db_acordodocumento_classe.php");
require_once("classes/db_acordo_classe.php");
require_once("dbforms/db_funcoes.php");

$oRotulo = new rotulocampo();
$oRotulo->label("ve13_sequencial");
$oRotulo->label("ve13_veiculo");
$oRotulo->label("ve13_motorista");
$oRotulo->label("ve13_datainicial");
$oRotulo->label("ve13_datafinal");
$oRotulo->label("ve13_observacao");

function testa($var){
  echo "<pre>";
  print_r($var);
  echo "</pre>";
}

function buscaDocumentos(){
  $sql = pg_query("SELECT * FROM vdocumentos ORDER BY idveiculo");
  $resultado = pg_fetch_all($sql);
  return $resultado;
}


function buscaDocumentoPorId($id){
  $sql = pg_query("SELECT * FROM vdocumentos WHERE id = {$id}");
  $resultado = pg_fetch_all($sql);
  return $resultado[0];
}

function buscaDocumentosPorVeiculo($id){
  $sql = pg_query("SELECT * FROM vdocumentos WHERE excluido = 2 AND idveiculo = {$id}");
  $resultado = pg_fetch_all($sql);
  return $resultado;
}

if(isset($_GET["excluir"])){
  $id = $_GET["excluir"];
  $usuario = db_getsession("DB_login");
  $ip = db_getsession("DB_ip");
  $datahora = date_create();
  $datahora = date_timestamp_get($datahora);
  
  $dado = buscaDocumentoPorId($id);
  $nome = "tmp/".$dado["nomearquivo"];  
  unlink($nome);
  pg_query("UPDATE vdocumentos SET excluido = 1, usuario = '{$usuario}', ip = '{$ip}', datahora = '{$datahora}' WHERE id = {$id}");
  //pg_query("DELETE FROM vdocumentos WHERE id = {$id}");
  echo "<script>alert('Documento excluído.');</script>";
}

if($_GET["s"] == "s"){
  echo "<script>alert('Documento anexado.');</script>";
}

if(isset($_GET["codigo"]) && isset($_GET["placa"])){  
  $dados = buscaDocumentosPorVeiculo($_GET["codigo"]);
}else{
  //$dados = buscaDocumentos();
  $dados = "";
}

//$dados = buscaDocumentos();
//testa($dados[1]);


if($_POST){
  if(isset($_POST["salvar"])){

    

    $idveiculo = $_POST["ve13_veiculo"];
    $placaveiculo = $_POST["descricao_veiculo"];
    $descricao = $_POST["descricao"]; 

    $usuario = db_getsession("DB_login");
    $ip = db_getsession("DB_ip");
    $datahora = date_create();
    $datahora = date_timestamp_get($datahora);

    $extensao = $_FILES["documento"]["name"];
    $extensao = explode(".", $extensao);
    $extensao = $extensao[1];
    
    $nomepdf = explode("/", $_FILES["documento"]["tmp_name"]);    
    //$nomepdf = $nomepdf[2].".pdf"; 
    $nomepdf = $nomepdf[2].".".$extensao; 
    
    
    $pasta = "tmp/";
    $destino = $pasta . $nomepdf;

    
    if(move_uploaded_file($_FILES["documento"]["tmp_name"], $destino)){
      pg_query("INSERT INTO vdocumentos(idveiculo, placaveiculo, descricao, nomearquivo, usuario, ip, datahora, excluido) VALUES({$idveiculo}, '{$placaveiculo}', '{$descricao}', '{$nomepdf}', '{$usuario}', '{$ip}', '{$datahora}', 2)");
      header('Location: vanexardocumentos.php?s=s');
    } else {
      echo "<script>alert('Houve um erro. Tente novamente.');</script>";
    }
  }
  
}

?>
<html>
  <head>
    <title>Microsist</title>
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
    <meta http-equiv="Expires" CONTENT="0">
    <?php
      db_app::load("scripts.js, prototype.js, widgets/windowAux.widget.js,strings.js");
      db_app::load("widgets/dbtextField.widget.js, dbViewCadEndereco.classe.js");
      db_app::load("dbmessageBoard.widget.js, dbautocomplete.widget.js,dbcomboBox.widget.js, datagrid.widget.js");
      db_app::load("estilos.css,grid.style.css");
    ?>
  </head>
  <body bgcolor=#CCCCCC leftmargin="0" topmargin="0" marginwidth="0" marginheight="0">
  <style>
    .botao{text-decoration: none; height: 18px; background-color: #d9d5d5; border-radius: 2px; font-size: 12px; border: 1px solid #999}
  </style>
    <div style="margin-top: 30px;"></div>
    
    
    <form name="form1" id='form1' method="post" action="" enctype="multipart/form-data">
      <center>
        <div style="width: 600px;">
          <fieldset>
            <legend><b>Adicionar Documento:</b></legend>
            <table>

              
            <tr>
              <td>
                <label class="bold" for="">
                  <?php db_ancora($Lve13_veiculo, "buscarVeiculo(true);", $iOpcao); ?>
                </label>
              </td>
              <td>
                <?php
                if(isset($_GET["codigo"]) && isset($_GET["placa"])){
                  $ve13_veiculo = $_GET["codigo"];
                  $descricao_veiculo = $_GET["placa"];
                }
                
                  db_input("ve13_veiculo", 10, $Ive13_veiculo, true, "text", $iOpcao, 'onChange="buscarVeiculo(false);"');
                  db_input("descricao_veiculo", 10, 0, true, "text", 3);
                ?>
              </td>
            </tr>
            
              
              
              


            
              <tr>
                <td valign="top">
                  <b>Documento: </b>
                </td>
                <td valign='top' style="height: 25px;">
                  <input type="file" name="documento" id="documento" required>
                  
                </td>
              </tr>
              

              <tr>
                <td><b>Descrição</b></td>
                <td>
                  <input type="text" name="descricao" size="55">
                </td>
              </tr>
            </table>
          </fieldset>
        </div>
        <input type="submit" name="salvar" value="Salvar">
        

        <div style="width: 600px;">
          <fieldset>
            <legend><b>Documentos Cadastrados</b></legend>
            <div id='ctnDbGridDocumentos'></div>
          </fieldset>
        </div>
      </center>
    </form>
    
    <?php
      db_menu(db_getsession("DB_id_usuario"),db_getsession("DB_modulo"),db_getsession("DB_anousu"),db_getsession("DB_instit"));
    ?> 
  </body>
  <div id='teste' style='display:none'></div>
</html>

<script type="text/javascript">

oGridDocumento     = new DBGrid('gridDocumento');
oGridDocumento.nameInstance = "oGridDocumento";
oGridDocumento.setHeight(200);
oGridDocumento.setCellAlign(new Array("center","center","left","center", "center"));
oGridDocumento.setHeader(new Array("Código","Veículo","Descrição","Download", "Ação"));
oGridDocumento.show($('ctnDbGridDocumentos'));



var oCodigoVeiculo      = $('ve13_veiculo');
var oDescricaoVeiculo   = $('descricao_veiculo');

function buscarVeiculo(lMostrar) {
    var sArquivo     = "func_veiculosalt.php";
    var sTituloTela  = "Pesquisar Veículos";
    var sQueryString = "funcao_js=parent.retornoVeiculos|ve01_codigo|ve01_placa";

    if (!lMostrar) {
      sQueryString = 'pesquisa_chave=' + oCodigoVeiculo.value + '&funcao_js=parent.retornoVeiculosChave';
    }

    js_OpenJanelaIframe('', 'db_iframe_veiculos', sArquivo + '?' + sQueryString, sTituloTela, lMostrar);
  }

  
  function retornoVeiculos(iCodigo, sPlaca) {

    oCodigoVeiculo.value    = iCodigo;
    oDescricaoVeiculo.value = sPlaca;
    //js_retornoGetDocumento(iCodigo);
    db_iframe_veiculos.hide();
    window.location.href = "vanexardocumentos.php?codigo="+iCodigo+"&placa="+sPlaca;
  }

  function retornoVeiculosChave(sPlaca, lErro) {

    var iCodigo = oCodigoVeiculo.value;
    if (lErro) {
      iCodigo = '';
    }
    retornoVeiculos(iCodigo, sPlaca);
  }

  function js_retornoGetDocumento(codigo = 0) {
  oGridDocumento.clearAll(true);
  
    <?php foreach($dados as $l) : ?>
    var aLinha = new Array();
    aLinha[0]  = "<?=$l['idveiculo']?>";
    aLinha[1]  = "<?=$l['placaveiculo']?>";
    aLinha[2]  = "<?=$l['descricao']?>";
    //aLinha[3]  = '<input type="button" value="Dowload" onclick="js_documentoDownload()">';
    //aLinha[4]  = '<input type="button" value="E" onclick="js_excluirDocumento()">';
    //aLinha[3]  = '<button type="button" class="botao"><a download="<?=$l["nomearquivo"]?>" href="tmp/<?=$l["nomearquivo"]?>">Download</a></button>';
    <?php if($l["excluido"] == 1) : ?>
      aLinha[3]  = '';
    <?php else: $ext = $l["nomearquivo"]; $ext = explode(".", $ext); $ext = $ext[1]; ?>
    aLinha[3]  = '<a download="documento.<?=$ext?>" href="tmp/<?=$l["nomearquivo"]?>">Download</a>';
    <?php endif; ?>
    aLinha[4]  = '<a class="botao" style="text-decoration:none;color:darkslategray" href="vanexardocumentos.php?excluir=<?=$l['id']?>">E</a>';
    
    oGridDocumento.addRow(aLinha);
  <?php endforeach ?>
  

  oGridDocumento.renderRows();

}

js_retornoGetDocumento();

</script>

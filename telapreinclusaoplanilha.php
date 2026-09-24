<?php


require_once(modification("libs/db_stdlib.php"));
require_once(modification("libs/db_conecta.php"));
require_once(modification("libs/db_sessoes.php"));
require_once(modification("libs/db_utils.php"));
require_once(modification("libs/db_usuariosonline.php"));
require_once(modification("dbforms/db_funcoes.php"));
require_once(modification("libs/db_sql.php"));

function testa($var){
	echo "<pre>";
	print_r($var);
	echo "</pre>";
}


function buscaDados($codigo){
	$sql = pg_query("SELECT * FROM pcproc INNER JOIN db_depart ON pc80_depto = coddepto INNER JOIN pcprocitem ON pc80_codproc = pc81_codproc INNER JOIN solicitem ON pc81_solicitem = pc11_codigo WHERE pc80_codproc = {$codigo}");
	$resultado = pg_fetch_all($sql);
	return $resultado[0];
}

//testa($_POST);
//die("Foi?");

$codprocessocompra = $_POST["iProcessoCompra"];
db_postmemory($HTTP_POST_VARS);
db_postmemory($HTTP_GET_VARS);


$dados = buscaDados($codprocessocompra);
$datasolicitacao = implode("/", array_reverse(explode("-", $dados["pc80_data"])));
$situacao = ($dados['pc80_situacao'] == 1) ? 'Análise' : 'Autorizado';
$tipoprocesso = ($dados['pc80_tipoprocesso'] == 1) ? 'Item' : 'Lote';


$clsolicita        = new cl_solicita;
$clsolicitem       = new cl_solicitem;
$clpcprocitem      = new cl_pcprocitem;
$clpcproc          = new cl_pcproc;
$clpcparam         = new cl_pcparam;
$clpcorcam         = new cl_pcorcam;
$clpcorcamitem     = new cl_pcorcamitem;
$clpcorcamitemproc = new cl_pcorcamitemproc;
$clpcorcamforne    = new cl_pcorcamforne;
$clpcorcamforne2   = new cl_pcorcamforne;
$clpcorcamval      = new cl_pcorcamval;
$clpcorcamjulg     = new cl_pcorcamjulg;
$clpcorcamtroca    = new cl_pcorcamtroca;
$db_botao          = true;
$db_opcao          = 1;
$result_pcparam = $clpcparam->sql_record($clpcparam->sql_query_file(db_getsession("DB_instit"),"pc30_horas,pc30_dias,pc30_contrandsol"));
db_fieldsmemory($result_pcparam,0);

 
?>
<html>
  <head>
    <title>Microsist</title>
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
    <meta http-equiv="Expires" CONTENT="0">
    <script language="JavaScript" type="text/javascript" src="scripts/scripts.js"></script>
    <script language="JavaScript" type="text/javascript" src="scripts/arrays.js"></script>
    <script language="JavaScript" type="text/javascript" src="scripts/prototype.js"></script>
    <link href="estilos.css" rel="stylesheet" type="text/css">
  </head>
  <body class="body-default" >
    <div class="container">

      <?php
        //include(modification("forms/db_frmpcproc.php"));
      $clrotulo = new rotulocampo;
$clrotulo->label("pc10_numero");
$clrotulo->label("pc10_data");
$clrotulo->label("pc10_resumo");
$clrotulo->label("pc80_resumo");
$clrotulo->label("pc80_codproc");
$clrotulo->label("pc80_tipoprocesso");
$clrotulo->label("descrdepto");

$val = false;

?>

<form name="form1" method="post" action="inclusaoplanilha.php">
  <fieldset style="width:775px;">
    <legend>Dados do Processo de Compras</legend>
    <table border="0"  width="100%">

      <table border="0"  width="100%">
        <tr>
          <td align="left" nowrap title="Solicitação">
            <strong>Solicitação: </strong>
          </td>
          <td align="left">
            <input readonly type="text" size="10" id="pc11_numero" name="pc11_numero" value="<?=$dados['pc11_numero'];?>" style="background-color:#DEB887">
          </td>
          <td align="left" nowrap title="Processo de Compra">
            <strong>Processo de Compra: </strong>
          </td>
          <td align="left" nowrap>
            <input name="pc80_codproc" type="text" id="pc80_codproc" value="<?=$dados['pc80_codproc'];?>" size="8" maxlength="10" readonly style="background-color:#DEB887;">
          </td>
        </tr>
        <tr>
          <td align="left" nowrap title="Data da Solicitação">
            <label class="bold" for="pc10_data" id="lbl_pc10_data">Data da Solicitação:</label>
          </td>
          <td align="left" nowrap>          	
            <input name="pc10_data" style="background-color:#DEB887" type="text" id="pc10_data" readonly value="<?=$datasolicitacao;?>" size="10" maxlength="10">
          </td>
          <td align="left" nowrap title="Departamento">
            <strong>Departamento: </strong>
          </td>
          <td align="left" nowrap>
            <input name="descrdepto" type="text" id="descrdepto" value="<?=$dados['descrdepto']?>" size="40" maxlength="40" readonly style="background-color:#DEB887;text-transform:uppercase;">
          </td>
        </tr>
        <tr>
          <td align="left">
            <strong>Situação: </strong>
          </td>
          <td>          	
          	<input readonly type="text" size="10" id="pc80_situacao" name="pc80_situacao" value="<?=$situacao;?>" style="background-color:#DEB887">            
          </td>
          <td colspan="2"></td>
        </tr>
        <tr>
          <td align="left">
            <label class="bold" for="pc80_tipoprocesso" id="lbl_pc80_tipoprocesso">Tipo de Processo</label>
          </td>
          <td>
            <input readonly type="text" size="10" id="pc80_tipoprocesso" name="pc80_tipoprocesso" value="<?=$tipoprocesso;?>" style="background-color:#DEB887">
          </td>
          <td colspan="2"></td>
        </tr>
        <tr>
          <td align="left" nowrap title="<?=@$Tpc10_resumo?>" colspan="4">
          	
            <fieldset>
              <legend>Resumo do Processo de Compras</legend>
              <textarea name="pc10_resumo" type="text" id="pc10_resumo" rows="5" cols="70" style="background-color:#E6E4F1"><?=$dados['pc80_resumo'];?></textarea>
            </fieldset>
          </td>
        </tr>
      </table>
    </table>
  </fieldset>

  <table border="0" align="center" width="800">
    <tr align="center">
      <td nowrap colspan="10">
        <fieldset>
          <legend><strong>Itens da Solicitação</strong></legend>
          <iframe name="iframe_solicitem" id="solicitem" marginwidth="0" marginheight="0"
                  frameborder="0" src="com1_gerasolicitemplanilha.php" width="100%" style="min-height: 200px"></iframe>
        </fieldset>
      </td>
    </tr>
  </table>

  <?php
  db_input('valores',50,0,true,'hidden',3);
  db_input('importa',50,0,true,'hidden',3);
  ?>

  <?php  /* ?>
  <input type="submit" value="Gerar Planilha TCERJ">
  <?php  */ ?>

  <?php

  /**
   * Buscamos o parâmetro pc30_liberado
   * Caso este parâmetro esteja TRUE, o usuário pode cadastrar pendências para uma solicitação de compras, do contrários
   * o botão que permite o cadastro não irá aparecer.
   */
  $sSqlLiberadoPcParam = $clpcparam->sql_query_file(db_getsession('DB_instit'), "pc30_liberado");
  $rsPcParam           = $clpcparam->sql_record($sSqlLiberadoPcParam);
  $lDadoLiberado       = false;
  if ($clpcparam->numrows > 0) {
    $lDadoLiberado = db_utils::fieldsMemory($rsPcParam, 0)->pc30_liberado == "f" ? true : false;
  }

  if ($lDadoLiberado) {
    echo "<input type='button' name='btnPendenciaSolicitacao' id='btnPendenciaSolicitacao' value='Pendência' onclick='js_openWindowPendencia();'>";
  }


  $result_pcproc = $clpcproc->sql_record($clpcproc->sql_query_file(null,"pc80_codproc"));
  $enviadados = false;
  
  ?>
</form>
<script>
  var nosolicitacao = document.getElementById("pc11_numero").value;

  if (nosolicitacao!=""){
    (window.CurrentWindow || parent.CurrentWindow).corpo.iframe_solicitem.location.href= 'com1_gerasolicitemplanilha.php?solicita='+nosolicitacao+'&pc10_numero='+nosolicitacao;
  }
  <?
    if($desabilita==true){
    echo "
      numele = parent.document.form1.length;
      cont = 0;
      for(i=0;i<numele;i++){
        if((window.CurrentWindow || parent.CurrentWindow).corpo.document.form1.elements[i].type=='submit' || (window.CurrentWindow || parent.CurrentWindow).corpo.document.form1.elements[i].type=='button'){
          (window.CurrentWindow || parent.CurrentWindow).corpo.document.form1.elements[i].disabled=true;
        }
      }
      ";
    }else{
    echo "
      numele = (window.CurrentWindow || parent.CurrentWindow).corpo.document.form1.length;
      cont = 0;
      for(i=0;i<numele;i++){
        if((window.CurrentWindow || parent.CurrentWindow).corpo.document.form1.elements[i].type=='submit' || (window.CurrentWindow || parent.CurrentWindow).corpo.document.form1.elements[i].type=='button'){
          (window.CurrentWindow || parent.CurrentWindow).corpo.document.form1.elements[i].disabled=false;
        }
      }
      ";
    }
   ?>

  document.getElementById('pc10_numero').style.width = '100%';
  document.getElementById('pc10_resumo').style.width = '100%';
  document.getElementById('pc10_data').style.width = '100%';

  /**
   * Função que abre a janela para cadastro de pendência para uma solicitação de compras.
   */
  function js_openWindowPendencia() {

    var iCodigoSolicitacao  = $F('pc10_numero');
    // a flag 'cadastroprocessodecompras' foi adiciona para indicar ao programa que essa é a origem da opreração
    var sUrlPendencia       = "com4_cadpendencias002.php?pc10_numero="+iCodigoSolicitacao+"&cadastroprocessodecompras=true";
    var sTituloJanelaIframe = "Cadastro de Pendência da Solicitação: "+iCodigoSolicitacao;
    js_OpenJanelaIframe('CurrentWindow.corpo', 'db_iframe_cadpendencia', sUrlPendencia, sTituloJanelaIframe ,true);
  }
</script>
      




    </div>
    <?php
      db_menu( db_getsession("DB_id_usuario"),
               db_getsession("DB_modulo"),
               db_getsession("DB_anousu"),
               db_getsession("DB_instit") );
    ?>
  </body>
  <script>
    arr_dados = new Array();
    arr_impor = new Array();
  </script>
</html>
<?php
  if (isset($incluir)) {

    if (!$sqlerro) {

     echo "<script>
      if (confirm('Processo de Compras {$pc80_codproc} incluído com sucesso. Deseja emitir o documento?')){
        jan = window.open('com2_emiteprocessocompra002.php?pc80_codproc_inicial={$pc80_codproc}&pc80_codproc_final={$pc80_codproc}',
                           '',
                           'width='+(screen.availWidth - 5),
                           'height='+(screen.availHeight-40)+',scrollbars=1, location=0'
                          );
        jan.moveTo(0, 0);
      }";

      if ($pc80_tipoprocesso == 2) {
        echo "window.location = \"com4_processocompra001.php?acao=2&iCodigo={$pc80_codproc}\";";
      }

      echo "</script>";
    } else {
      db_msgbox($erro_msg);
    }
    if($sqlerro==true){
      if($clpcproc->erro_campo!=""){
        echo "<script> document.form1.".$clpcproc->erro_campo.".style.backgroundColor='#99A9AE';</script>";
        echo "<script> document.form1.".$clpcproc->erro_campo.".focus();</script>";
      };
    }
  }
?>
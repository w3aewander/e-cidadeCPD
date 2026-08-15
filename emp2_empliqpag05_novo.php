<?
/*
 *     E-cidade Software Publico para Gestao Municipal
 *  Copyright (C) 2014  DBselller Servicos de Informatica
 *                            www.dbseller.com.br
 *                         e-cidade@dbseller.com.br
 *
 *  Este programa e software livre; voce pode redistribui-lo e/ou
 *  modifica-lo sob os termos da Licenca Publica Geral GNU, conforme
 *  publicada pela Free Software Foundation; tanto a versao 2 da
 *  Licenca como (a seu criterio) qualquer versao mais nova.
 *
 *  Este programa e distribuido na expectativa de ser util, mas SEM
 *  QUALQUER GARANTIA; sem mesmo a garantia implicita de
 *  COMERCIALIZACAO ou de ADEQUACAO A QUALQUER PROPOSITO EM
 *  PARTICULAR. Consulte a Licenca Publica Geral GNU para obter mais
 *  detalhes.
 *
 *  Voce deve ter recebido uma copia da Licenca Publica Geral GNU
 *  junto com este programa; se nao, escreva para a Free Software
 *  Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA
 *  02111-1307, USA.
 *
 *  Copia da licenca no diretorio licenca/licenca_en.txt
 *                                licenca/licenca_pt.txt
 */

require_once(modification("libs/db_stdlib.php"));
require_once(modification("libs/db_conecta.php"));
require_once(modification("libs/db_usuariosonline.php"));
require_once(modification("libs/db_liborcamento.php"));
require_once(modification("classes/db_lote_classe.php"));
require_once(modification("dbforms/db_funcoes.php"));
require_once(modification("dbforms/db_classesgenericas.php"));
require_once(modification("classes/db_empempenho_classe.php"));
include("classes/db_orcdotacao_classe.php");

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

//----
//----
$cllote = new cl_lote;
$cliframe_seleciona = new cl_iframe_seleciona;

$cllote->rotulo->label();
$clrotulo = new rotulocampo;
$clrotulo->label("z01_nome");


$anousu = db_getsession("DB_anousu");

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
  <form name="form1" method="post" action="emp2_empliqpag05_novo.php">
    <input  name="filtra_despesa" id="filtra_despesa" type="hidden" value="" >
    <fieldset style="width: 800px">
      <legend><b>Movimentação de Empenho</b></legend>
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
                  $aux->linhas = 4;
                  $aux->vwhidth = 400;
                  $aux->funcao_gera_formulario();
                  ?>
              </td>
          </tr>
          <tr>
              <td>
                  <b>Ação:</b>
                  <?php
                  $aMostrarEmpenho = array("e" => "Excluir", "i" => "Incluir");
                  db_select("ops",$aMostrarEmpenho,true,2);
                  ?>
              </td>
          </tr>
        </table>
    </fieldset>
    <br>
      <br>
      <input type="button" value="Alterar Empenho" onClick="js_alterarempenho()">
</center>
</form>
<script>

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
      //document.form1.submit();
      //alert(strempenhos);

      /*var oParam                = new Object();
      oParam.strempenhos        = strempenhos;
      oParam.ops = ops;

      new Ajax.Request('emp2_empliqpag07_novo.php',
          {
              method:'post',
              parameters:'json='+Object.toJSON(oParam),
              onComplete: alert("Empenhos alterados com sucesso.")

          });*/

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
      if(erro==true){
          document.form1.e60_numemp.focus();
          document.form1.z01_nome1.value = '';
      }
  }
  function js_mostraempenho1(chave1){
      document.form1.e60_numemp.value = chave1;
      // document.form1.z01_nome1.value = chave2;
      db_iframe_empempenho.hide();
  }

</script>

</center>
</body>
</html>
<?php
/*
 *     E-cidade Software Publico para Gestao Municipal
 *  Copyright (C) 2014  DBSeller Servicos de Informatica
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

require(modification("libs/db_stdlib.php"));
require(modification("libs/db_conecta."."php"));
include(modification("libs/db_sessoes.php"));
include(modification("libs/db_usuariosonline.php"));
include(modification("dbforms/db_funcoes.php"));
require_once(modification("libs/db_app.utils.php"));
require_once(modification("dbforms/db_classesgenericas.php"));
$oRotulo = new rotulocampo();
$oRotulo->label("e60_codemp");
$oRotulo->label("z01_nome");
$oRotulo->label("m60_codmater");
$oRotulo->label("m60_descr");

?>
<html>
<head>
  <title>Microsist</title>
  <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
  <meta http-equiv="Expires" CONTENT="0">
  <script language="JavaScript" type="text/javascript" src="scripts/scripts.js"></script>
  <script language="JavaScript" type="text/javascript" src="scripts/strings.js"></script>
  <script language="JavaScript" type="text/javascript" src="scripts/prototype.js"></script>
  <script language="JavaScript" type="text/javascript" src="scripts/widgets/DBLookUp.widget.js"></script>
  <script language="JavaScript" type="text/javascript" src="scripts/datagrid.widget.js"></script>
  <script language="JavaScript" type="text/javascript" src="scripts/widgets/datagrid/plugins/DBHint.plugin.js"></script>
  <script language="JavaScript" type="text/javascript" src="scripts/widgets/Collection.widget.js"></script>
  <script language="JavaScript" type="text/javascript" src="scripts/widgets/DatagridCollection.widget.js"></script>
  <script language="JavaScript" type="text/javascript" src="scripts/widgets/windowAux.widget.js"></script>
  <script language="JavaScript" type="text/javascript" src="scripts/widgets/dbmessageBoard.widget.js"></script>
  <script language="JavaScript" type="text/javascript" src="scripts/AjaxRequest.js"></script>
  <link href="estilos.css" rel="stylesheet" type="text/css">
</head>
<body class="body-default">
<div class="container" style="width:800px;">
  <fieldset>
    <legend>Ordem auxiliar de Empenho</legend>
    <table>
      <tr>
        <td>
          <b>
            <label id="sLabelEmpenho" for='e60_codemp'>Empenho:</label>
          </b>
        </td>
        <td>
          <?php
          db_input('e60_codemp', 10, $Ie60_codemp, true, 'text', 3);
          db_input('z01_nome', 40, '', true, 'text', 3);
          ?>
        </td>
      </tr>
      <tr>
        <td>
          <b>Valor da Ordem:</b>
        </td>
        <td>
          <?php
          db_input('saldo', 15, '', true, 'text', 3);
          ?>
        </td>
      </tr>
      <tr>
        <td>
          <b>Data de Emissão:</b>
        </td>
        <td>
          <?php
          db_input('saldo', 15, '', true, 'text', 3);
          ?>
        </td>
      </tr>
      <tr>
        <td>
          <b>Departamento de Entrada:</b>
        </td>
        <td>
          <?php
          $sWhere           = "instit = ".db_getsession("DB_instit");
          $oDaoAlmoxarifado = new cl_db_almox();
          $sSqlAmoxarifados = $oDaoAlmoxarifado->sql_query(null, "coddepto, descrdepto", "descrdepto", $sWhere);
          $rsDepartamentos  = db_query($sSqlAmoxarifados);
          $aDepartamentos   = array();
          if (!$rsDepartamentos) {
            echo "Erro ao pesquisar dados do departamento";
          }
          $iTotalDepto = pg_num_rows($rsDepartamentos);
          for ($iDepto = 0; $iDepto < $iTotalDepto; $iDepto++) {

            $oDadosDepto = db_utils::fieldsMemory($rsDepartamentos, $iDepto);
            $aDepartamentos[$oDadosDepto->coddepto] = $oDadosDepto->descrdepto;
          }
          db_select("departamento", $aDepartamentos, true, 1);
          ?>
        </td>
      </tr>
    </table>

    <fieldset class="separator">
      <legend>Itens na Ordem</legend>
      <div id="container-grid">

      </div>
    </fieldset>
  </fieldset>
  <input type="button" value="Salvar"    id="btnSalvar">
  <input type="button" value="Excluir"   id="btnExcluir" style="display: none;">
  <input type="button" value="Reemitir" disabled id="btnReemitir">
  <input type="button" value="Pesquisar" onclick="pesquisarOrdem()" id="btnPesquisar">
</div>
</body>
</html>
<?php db_menu(); ?>
<script>

  var oGet           = js_urlToObject();

  const URL_RPC      = 'mat4_ordemauxiliarempenho.RPC.php';
  var oItem          = $('m60_codmater');
  var oEmpenho       = $('e60_codemp');
  var iCodigoOrdem   = '';


  var oEmpenhosCollection = new Collection().setId('codigo');
  var oGridEmpenhos = DatagridCollection.create(oEmpenhosCollection).configure("order", false);

  oGridEmpenhos.addColumn("item",   {label : "Material",   "width" : "30px"});
  oGridEmpenhos.addColumn("descricao", {label : "Descrição", "width" : "300px"});
  oGridEmpenhos.addColumn("quantidade",  {label : "Quantidade",  "width" : "100px"});
  oGridEmpenhos.addColumn("valor_unitario",   {label : "Unitário",   "width" : "100px", "align" : "center"});
  oGridEmpenhos.addColumn("valor_total",   {label : "Total",   "width" : "100px", "align" : "center"});

  oGridEmpenhos.show($('container-grid'));
  var iCodigoEmAlteracao = '';
  function limparEmpenho() {

    oEmpenho.value      = '';
    $('z01_nome').value = '';
    $('saldo').value    = '';

  }

  $('btnSalvar').observe('click', function() {

    var aItens      = oEmpenhosCollection.get();
    for (var oItem of aItens) {

      oItem.observacao = encodeURIComponent(tagString(oItem.observacao));
    }

    if (aItens.length == 0) {

      alert('não foram informado itens');
      return false;
    }


    var oParametro = {
      exec               :'processar',
      iCodigoOrdem       : iCodigoOrdem,
      iCodigoAlmoxarifado: $F('departamento')
    };

    new AjaxRequest(URL_RPC, oParametro, function(oRetorno, lErro) {

      alert(oRetorno.mensagem.urlDecode());
      if (lErro) {
        return false;
      }
      limparEmpenho();
      oEmpenhosCollection.clear();
      oGridEmpenhos.reload();
    })
      .setMessage("Aguarde,salvar os dados.")
      .execute();
  });

  function pesquisarOrdem(iSituacao) {

    if (empty(iSituacao)) {
      iSituacao = oGet.acao;
    }
    var sFuncao = '?funcao_js=parent.carregarOrdem|sequencial&situacao='+iSituacao
    js_OpenJanelaIframe('', 'db_iframe_ordemauxiliar',
      'func_ordemauxiliarempenho.php'+sFuncao, 'Pesquisar ordem Auxiliar', true);
  }

  function carregarOrdem(iOrdem) {

    iCodigoOrdem = '';
    var oParametro = {
      exec :'getDadosOrdem',
      iCodigoOrdem : iOrdem
    };

    new AjaxRequest(URL_RPC, oParametro, function(oRetorno, lErro) {

      db_iframe_ordemauxiliar.hide();
      if (lErro) {

        alert(oRetorno.message.urlDecode());
        return false;
      }

      var oOrdem = oRetorno.ordem;
      $('saldo').value    = oOrdem.saldo;
      oEmpenho.value      = oOrdem.empenho;
      if (oOrdem.credor != '') {
        $('z01_nome').value = oOrdem.credor.urlDecode();
      }
      iCodigoOrdem  = oOrdem.codigo;
      oEmpenhosCollection.clear();
      for (oItemOrdem of oOrdem.itens) {

        oItemOrdem.descricao = oItemOrdem.descricao.urlDecode();
        oEmpenhosCollection.add(oItemOrdem);
      }
      oGridEmpenhos.reload();
      if (oGet.acao == 3) {
        bloquearTela();
      }
      $('btnReemitir').disabled = false;
    })
      .setMessage("Aguarde, pesquisando Dados da Ordem.")
      .execute();
  }

  $('btnExcluir').observe('click', function() {

    if (empty(iCodigoOrdem)) {

      alert('Escolha uma ordem auxiliar.');
      return false;
    }
    if (!confirm('Confirma o cancelamento do processamento da ordem auxiliar?')) {
      return false;
    }
    var oParametro = {

      exec :'cancelarProcessamento',
      iCodigoOrdem : iCodigoOrdem
    };

    new AjaxRequest(URL_RPC, oParametro, function(oRetorno, lErro) {

      db_iframe_ordemauxiliar.hide();
      alert(oRetorno.mensagem.urlDecode());
      if (lErro) {

        return false;
      }
      limparEmpenho();
      iCodigoOrdem  = '';
      oEmpenhosCollection.clear();
      oGridEmpenhos.reload();

    })
      .setMessage("Aguarde, removendo dados da ordem.")
      .execute();
  });

  $('btnReemitir').observe('click', function() {

    if (empty(iCodigoOrdem)) {

      alert('antes de emitir a ordem, selecione uma.');
      return false;
    }
    var sUrl =  "mat4_emitedocumentoordemauxiliar.php?codigo_ordem="+iCodigoOrdem;
    window.open(sUrl, '', 'location=0');
  });
  function bloquearTela() {

    for (oInput of $$('input')) {
      oInput.disabled = true;
      oInput.readOnly = true;
      if (oInput.type == 'text') {
        oInput.addClassName('disabled');
      }
    }
    $('btnExcluir').disabled   = false;
    $('btnPesquisar').disabled = false;
  }

  switch (oGet.acao) {

    case '1':
      pesquisarOrdem(1);
      break;

    case '2':

      $('btnSalvar').style.display    = 'none';
      $('btnPesquisar').style.display = '';
      $('btnExcluir').style.display   = '';
      bloquearTela();
      pesquisarOrdem(2);
      break;
  }
</script>
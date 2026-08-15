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
        <td colspan="4">
          <?php
          db_input('e60_codemp', 10, $Ie60_codemp, true, 'text', 1);
          db_input('z01_nome', 40, '', true, 'text', 3);
          ?>
        </td>
        <td>
          <b>Saldo:</b>
          <?php
          db_input('saldo', 15, '', true, 'text', 3);
          ?>
        </td>
      </tr>
      <tr>
        <td>
          <label for="numero_nota_fiscal"><b>Número da Nota Fiscal:</b></label>
        </td>
        <td>
          <?php
          db_input('numero_nota_fiscal', 15, '', true, 'text', 1);
          ?>
        </td>
        <td>
          <label for="data_nota_fiscal"><b>Data da Nota Fiscal:</b></label>
        </td>
        <td>
          <?php
          db_inputdata('data_nota_fiscal', null, null, null, true, 'text', 1);
          ?>
        </td>
      </tr>
      <tr>  
        <td>
          <label for="data_entrega"><b>Data da Entrega:</b></label>
        </td>
        <td>
          <?php
          db_inputdata('data_entrega', null, null, null, true, 'text', 1);
          ?>
        </td>
      </tr>
    </table>
    <fieldset>
      <legend>
        Itens
      </legend>
      <table style="width: 100%">
        <tr>
          <td>
            <label id='sLabelMaterial' for="m60_codmater">Material:</label>
          </td>
          <td colspan="6">
            <?
            db_input('m60_codmater',10,$Im60_codmater,true,'text',1);
            db_input('m60_descr', 55,$Im60_descr,true,'text',3,'');
            ?>
          </td>
        </tr>
        <tr>
          <td>
            <label for="quantidade">
              <b>Quantidade</b>
            </label>
          </td>
          <td>
            <?php
            db_input('quantidade', 8, 4,true,'text',1);
            ?>
          </td>
          <td>
            <label for="valor_unitario">
              <b>Valor Unitário:</b>
            </label>
          </td>
          <td>
            <?php
            db_input('valor_unitario',10, 4,true,'text',1);
            ?>
          </td>
          <td>
            <label for="valor_total">
              <b>Valor Total:</b>
            </label>
          </td>
          <td>
            <?php
            db_input('valor_total',10, 4,true,'text', 3);
            ?>
          </td>
        <tr>
        <tr>
          <td colspan="6">
            <fieldset>
              <legend>Observações</legend>
              <textarea style="width: 100%;resize: none" id="observacao"></textarea>
            </fieldset>
          </td>
        </tr>
      </table>
    </fieldset>
    <input type="button" value="Salvar Item" id="btnSalvarItem">
    <fieldset class="separator">
      <legend>Itens na Ordem</legend>
      <div id="container-grid">

      </div>
    </fieldset>
  </fieldset>
  <input type="button" value="Salvar"    id="btnSalvar">
  <input type="button" value="Excluir"   id="btnExcluir" style="display: none;">
  <input type="button" value="Pesquisar" onclick="pesquisarOrdem()" id="btnPesquisar" style="display: none;">
</div>
</body>
</html>
<?php db_menu(); ?>
<script>

  var oGet           = js_urlToObject();

  const URL_RPC      = 'mat4_ordemauxiliarempenho.RPC.php';
  var oItem          = $('m60_codmater');
  var oDescricao     = $('m60_descr');
  var oQuantidade    = $('quantidade');
  var oValorUnitario = $('valor_unitario');
  var oValorTotal    = $('valor_total');
  var oEmpenho       = $('e60_codemp');
  var oObservacao    = $('observacao');
  var oObservacao    = $('observacao');
  var oNumeroNota    = $('numero_nota_fiscal');
  var oDataNota      = $('data_nota_fiscal');
  var oDataEntrega   = $('data_entrega');
  var iCodigoOrdem   = '';
  var oLookUpEmpenho = new DBLookUp($('sLabelEmpenho'), $('e60_codemp'), $('z01_nome'), {
    "sArquivo" : "func_empempenho.php",
    "sObjetoLookUp" : "db_iframe_empempenho",
    "sLabel" : "Pesquisar Empenhos",

  });

  oLookUpEmpenho.callBackClick = function() {

    oLookUpEmpenho.oInputID.value = arguments[0]+"/"+arguments[2];
    $('z01_nome').value           = arguments[1];
    getSaldoEmpenho();
    var oObjetoLookUp = eval(oLookUpEmpenho.oParametros.sObjetoLookUp);
    oObjetoLookUp.hide();
    return;
  }

  oLookUpEmpenho.callBackChange  = function() {


    var aArgumentos = arguments,
      lErro       = null,
      sDescricao  = null;

    for (var iArgumento = 0; iArgumento < aArgumentos.length; iArgumento++) {

      if (typeof(aArgumentos[iArgumento]) == "boolean") {
        lErro = aArgumentos[iArgumento];
      };

      if (typeof(aArgumentos[iArgumento]) == 'string' && sDescricao == null ) {
        sDescricao = aArgumentos[iArgumento];
      };
    };

    if (!lErro) {

      sDescricao = arguments[1];
      getSaldoEmpenho();
    }
    this.oInputDescricao.value = sDescricao;
    if (lErro) {
      this.oInputID.value        = '';
    }

    if (this.oParametros.oBotaoParaDesabilitar != '') {
      this.oParametros.oBotaoParaDesabilitar.disabled = false;
    }

    return;
  };

  oLookUpEmpenho.getQueryStringChange = function() {

    var iEmpenho = oLookUpEmpenho.oInputID.value;
    var iAnoUsu  = 0;
    if (oLookUpEmpenho.oInputID.value.indexOf("/") !== false) {

      var aPartesEmpenho = iEmpenho.split("/");
      iEmpenho = aPartesEmpenho[0];
      iAnoUsu  = aPartesEmpenho[1];
    }
    var sQuery  = "";
    sQuery += oLookUpEmpenho.oParametros.sArquivo;
    sQuery += "?";
    sQuery += "pesquisa_chave=" + iEmpenho;
    sQuery += "&lPesquisaPorCodigoEmpenho=1";
    if (iAnoUsu > 0) {
      sQuery += "&iAnoEmpenho=" + iAnoUsu;
    }
    sQuery += "&";

    if ( oLookUpEmpenho.oParametros.aParametrosAdicionais.length > 0 ){
      sQuery += oLookUpEmpenho.oParametros.aParametrosAdicionais.join("&");
      sQuery += "&";
    }

    sQuery += "funcao_js=parent.DBLookUp.repository.getInstance("+oLookUpEmpenho.iReferencia+").callBackChange";
    sQuery += oLookUpEmpenho.oParametros.sQueryString;

    return sQuery;
  }
  oLookUpEmpenho.setCamposAdicionais(['e60_anousu', 'z01_nome']);

  var oLookUpMaterial = new DBLookUp($('sLabelMaterial'), $('m60_codmater'), $('m60_descr'), {
    "sArquivo" : "func_matmater.php",
    "sObjetoLookUp" : "db_iframe_matmate",
    "sLabel" : "Pesquisar Material",

  });
  var oEmpenhosCollection = new Collection().setId('codigo');
  var oGridEmpenhos = DatagridCollection.create(oEmpenhosCollection).configure("order", false);

  oGridEmpenhos.addColumn("item",   {label : "Material",   "width" : "30px"});
  oGridEmpenhos.addColumn("descricao", {label : "Descrição", "width" : "300px"});
  oGridEmpenhos.addColumn("quantidade",  {label : "Quantidade",  "width" : "100px"});
  oGridEmpenhos.addColumn("valor_unitario",   {label : "Unitário",   "width" : "100px", "align" : "center"});
  oGridEmpenhos.addColumn("valor_total",   {label : "Total",   "width" : "100px", "align" : "center"});

  var iCodigoEmAlteracao = '';
  oGridEmpenhos.addAction("A", null, function(oEvento, oItemGrid) {

    iCodigoEmAlteracao   = oItemGrid.codigo;
    oItem.value          = oItemGrid.item;
    oDescricao.value     = oItemGrid.descricao;
    oQuantidade.value    = oItemGrid.quantidade;
    oValorUnitario.value = oItemGrid.valor_unitario;
    oValorTotal.value    = oItemGrid.valor_total;
    oObservacao.value    = oItemGrid.observacao;

  });

  oGridEmpenhos.addAction("E", null, function(oEvento, oItem) {

    if (!confirm("Deseja remover o Item")) {
      return false;
    }


    oEmpenhosCollection.remove(oItem.ID);
    oGridEmpenhos.reload();

  });

  oGridEmpenhos.show($("container-grid"));

  function calcularValorTotal () {

    $('valor_total').value = 0;
    if ($F('quantidade') != '' && $F('valor_unitario') != '') {
      $('valor_total').value = js_round($F('quantidade') * $F('valor_unitario'), 2);
    }
  }

  $('valor_unitario').observe('change', calcularValorTotal);
  $('quantidade').observe('change', calcularValorTotal);

  $('btnSalvarItem').observe('click', function() {

    if (empty(oItem.value)) {

      alert('Item deve ser informado:'+oItem.value);
      return
    }
    if (empty(oQuantidade.value)) {

      alert('Quantidade deve ser informada');
      return
    }
    if (empty(oValorUnitario.value)) {

      alert('o Valor unitário deve ser informado');
      return
    }
    codigo    = oEmpenhosCollection.get().length+1;
    if (!empty(iCodigoEmAlteracao)) {
      codigo = iCodigoEmAlteracao;
    }
    var oItemAdicionar = {

      codigo         : codigo,
      item           : oItem.value,
      descricao      : oDescricao.value,
      quantidade     : oQuantidade.value,
      valor_unitario : oValorUnitario.value,
      valor_total    : oValorTotal.value,
      observacao     : oObservacao.value,
      item_novo      : true
    }
    oEmpenhosCollection.add(oItemAdicionar);
    oGridEmpenhos.reload();
    limparFormularioItens();
    iCodigoEmAlteracao = '';
  });

  function limparFormularioItens() {

    oItem.value          = '';
    oDescricao.value     = '';
    oQuantidade.value    = '';
    oValorUnitario.value = '';
    oValorTotal.value    = '';
    oObservacao.value    = '';



  }

  function limparEmpenho() {

    oEmpenho.value      = '';
    $('z01_nome').value = '';
    $('saldo').value    = '';
    oNumeroNota.value   = '';
    oDataNota.value     = '';
    oDataEntrega.value  = '';

  }

  $('btnSalvar').observe('click', function() {

    if (empty(oEmpenho)) {
      alert('oEmpenho deve ser informado');
    }

    if (empty(oDataNota.value)) {
      alert('A Data da Nota deve ser informado');
      return false;
    }
    
    if (empty(oNumeroNota.value)) {
      alert('O numero da Nota deve ser informado');
      return false;
    }

    if (empty(oDataEntrega.value)) {
      alert('A data da entrega deve ser informado');
      return false;
    }

    var aItens      = oEmpenhosCollection.get();
    var nValorItens = 0;
    var aItensEnvio = [];
    for (var oItem of aItens) {

      oItem.observacao = encodeURIComponent(tagString(oItem.observacao));
      nValorItens += new Number(oItem.valor_total);
      aItensEnvio.push(oItem.build());
    }

    if (aItens.length == 0) {

      alert('não foram informado itens');
      return false;
    }

    if (nValorItens > new Number($F('saldo'))) {

      alert('Não existe mais saldo para efetuar o lançamentopesquisarOrdem da ordem auxiliar ');
      return false;

    }
    var oParametro = {
      exec :'salvar',
      iCodigoEmpenho : oEmpenho.value,
      iCodigoOrdem   : iCodigoOrdem,
      nNumeroNota    : oNumeroNota.value,
      sDataNota      : oDataNota.value,
      sDataEntrega   : oDataEntrega.value,
      itens: aItensEnvio
    };
    new AjaxRequest(URL_RPC, oParametro, function(oRetorno, lErro) {

      alert(oRetorno.mensagem.urlDecode());
      if (lErro) {
        return false;
      }

      limparFormularioItens();
      limparEmpenho();
      oEmpenhosCollection.clear();
      oGridEmpenhos.reload();
      emitirOrdem(oRetorno.codigo_ordem);
    })
      .setMessage("Aguarde,salvar os dados.")
      .execute();
  });

  /**
   * Retorna o saldo do empenmho
   */
  function getSaldoEmpenho() {

    var oParametro = {
      exec :'getSaldo',
      iCodigoEmpenho : oEmpenho.value
    };
    new AjaxRequest(URL_RPC, oParametro, function(oRetorno, lErro) {

      if (lErro) {

        alert(oRetorno.message.urlDecode());
        return false;
      }
      $('saldo').value = oRetorno.saldo;
    })
      .setMessage("Aguarde, pesquisando saldo do empenho.")
      .execute();
  }

  function pesquisarOrdem() {

    var sFuncao = '?funcao_js=parent.carregarOrdem|sequencial'
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
      oNumeroNota.value   = oOrdem.numero_nota;
      oDataNota.value     = oOrdem.data_nota
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
    })
      .setMessage("Aguarde, pesquisando Dados da Ordem.")
      .execute();
  }

  $('btnExcluir').observe('click', function() {

    if (empty(iCodigoOrdem)) {

      alert('Escolha uma ordem auxiliar.');
      return false;
    }
    if (!confirm('Confirma a exclusão da Ordem Auxiliar')) {
      return false;
    }
    var oParametro = {

      exec :'excluirOrdem',
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

    case '2':
      $('btnPesquisar').style.display='';
      pesquisarOrdem();
      break;

    case '3':

      $('btnSalvar').style.display    = 'none';
      $('btnPesquisar').style.display = '';
      $('btnExcluir').style.display   = '';
      $('btnSalvarItem').disabled    = true;
      oLookUpEmpenho.desabilitar();
      oLookUpMaterial.desabilitar();
      bloquearTela();
      pesquisarOrdem();
      break;
  }

  emitirOrdem = function(iCodigoOrdem) {

    if (empty(iCodigoOrdem)) {

      alert('antes de emitir a ordem, selecione uma.');
      return false;
    }
    var sUrl =  "mat4_emitedocumentoordemauxiliar.php?codigo_ordem="+iCodigoOrdem;
    window.open(sUrl, '', 'location=0');
  }
</script>
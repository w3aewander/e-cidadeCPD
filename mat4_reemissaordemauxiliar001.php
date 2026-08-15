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
  <div class="container">
    <fieldset>
      <legend>Ordem Auxiliar de Empenho</legend>
      <table>
        <tr>
          <td>
            <label id="lblOrdemAuxiliar" for="sequencial">
              <?php
              db_ancora("Ordem Auxiliar:", 'pesquisarOrdem()', 1);
              ?>
            </label>
          </td>
          <td>
            <?php
            db_input("sequencial", 10, "4", true, 'text', 3);
            ?>
          </td>
        </tr>
      </table>
    </fieldset>
    <input type="button" value="Reemitir" id="btnReemitir">
  </div>
</body>
</html>
<?php db_menu(); ?>
<script>

  var oOrdemAuxiliar = $(sequencial);
  function pesquisarOrdem() {

    var sFuncao = '?funcao_js=parent.carregarOrdem|sequencial'
    js_OpenJanelaIframe('', 'db_iframe_ordemauxiliar','func_ordemauxiliarempenho.php'+sFuncao, 'Pesquisar ordem Auxiliar', true);
  }

  function carregarOrdem(iOrdem) {

    oOrdemAuxiliar.value = iOrdem;
    db_iframe_ordemauxiliar.hide();
  }

  $('btnReemitir').observe("click", function() {


    if (empty(oOrdemAuxiliar.value)) {

      alert('antes de emitir a ordem, selecione uma.');
      return false;
    }
    var sUrl =  "mat4_emitedocumentoordemauxiliar.php?codigo_ordem="+oOrdemAuxiliar.value;
    window.open(sUrl, '', 'location=0');
  });
</script>
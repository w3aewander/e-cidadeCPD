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

require_once modification("libs/db_stdlib.php");
require_once modification("libs/db_conecta.php");
require_once modification("libs/db_sessoes.php");
require_once modification("dbforms/db_funcoes.php");
?>
<html>
<head>
  <title>Microsist</title>
  <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
  <meta http-equiv="Expires" content="0">
  <script language="JavaScript" type="text/javascript" src="scripts/scripts.js"></script>
  <script language="JavaScript" type="text/javascript" src="scripts/strings.js"></script>
  <script language="JavaScript" type="text/javascript" src="scripts/prototype.js"></script>
  <script language="JavaScript" type="text/javascript" src="scripts/EmissaoRelatorio.js"></script>
  <link href="estilos.css" rel="stylesheet" type="text/css">
</head>
<body class="body-default">

  <div class="container">

    <form name="form1" method="post">

      <fieldset>
        <legend>Movimentos por Recurso</legend>

        <table>

          <tr>
            <td>
              <label for="datainicial" class="bold">Data Inicial:</label>
            </td>
            <td>
              <?php
                db_inputdata("datainicial", null, null, null, true, 'text', 1);
              ?>
            </td>
          </tr>

          <tr>
            <td>
              <label for="datafinal" class="bold">Data Final:</label>
            </td>
            <td>
              <?php
                db_inputdata("datafinal", null, null, null, true, 'text', 1);
              ?>
            </td>
          </tr>

          <tr>
            <td>
              <label for="orgao" class="bold">Órgão:</label>
            </td>
            <td>
              <input type="text" name="orgao" id="orgao">
            </td>
          </tr>

        </table>

      </fieldset>

      <input type="button" id="emitir" value="Emitir" />
    </form>

  </div>

  <?php db_menu(); ?>

  <script type="text/javascript">
  (function(){
    $('emitir').observe('click', function(){

      var oEmissao = new EmissaoRelatorio("cai2_movimentosrecurso002.php", {
        iOrgao       : $F('orgao'),
        sDataInicial : $F('datainicial'),
        sDataFinal   : $F('datafinal')
      });
      oEmissao.open();
    });
  })();
  </script>
</body>
</html>

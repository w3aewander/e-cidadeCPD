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
require_once("libs/db_stdlib.php");
require_once("libs/db_utils.php");
require_once("libs/db_conecta.php");
require_once("libs/db_sessoes.php");
require_once("dbforms/db_funcoes.php");
?>
<html>
  <head>
    <title>Microsist</title>
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
    <meta http-equiv="Expires" CONTENT="0">

    <script language="JavaScript" type="text/javascript" src="scripts/scripts.js"></script>
    <script language="JavaScript" type="text/javascript" src="scripts/strings.js"></script>
    <script language="JavaScript" type="text/javascript" src="scripts/prototype.js"></script>
    <script language="JavaScript" type="text/javascript" src="scripts/widgets/DBLancador.widget.js"></script>
    <script language="JavaScript" type="text/javascript" src="scripts/widgets/DBDownload.widget.js"></script>
    <script language="JavaScript" type="text/javascript" src="scripts/AjaxRequest.js"></script>

    <link href="estilos.css" rel="stylesheet" type="text/css">

  </head>

  <body bgcolor=#CCCCCC leftmargin="0" topmargin="0" marginwidth="0" marginheight="0" bgcolor="#cccccc">

    <div class="container">
      <form id="frmControleHodometro" method="post" action="">
        <table>
          <tr>
            <td align="center">
              <fieldset>
                <legend>Retiradas e Devoluções</legend>
                <table style="width: 100%;">
                  <tr>
                    <td><label class="bold" for="periodo_inicio">Período:</label></td>
                    <td>
                      <?php
                      db_inputdata("periodo_inicio", "", "", "", true, "text", 1);
                      ?> <b>até</b>
                      <?php
                      db_inputdata("periodo_fim", "", "", "", true, "text", 1);
                      ?>
                    </td>
                  </tr>
                  
                  <tr>
                    <td><b>Placa:</b></td>
                    <td><input type="text" name="placa" id="placa"></td>
                  </tr>

                  <tr>
                    <td><b>VR:</b></td>
                    <td><input type="text" name="vr" id="vr"></td>
                  </tr>
                </table>

                

              </fieldset>
              <input type="button" name="btnEmitir" id="btnEmitir" value="Emitir" onclick="js_emitir()">
            </td>
          </tr>
        </table>
      </form>
    </div>
    <?php db_menu(); ?>
  <script>
    function js_emitir(){
      var inicio = document.getElementById("periodo_inicio").value;
      var fim = document.getElementById("periodo_fim").value;
      var placa = document.getElementById("placa").value;
      var vr = document.getElementById("vr").value;

      if(inicio == "" || fim == ""){
        alert("Período é obrigatório.");
        return false;
      }

      if(placa.trim() == "" && vr.trim() == ""){
        alert("Preencha o campo Placa ou VR");
      }

      

      var sUrl = 'relatorioretiradasavulso.php?inicio='+inicio+'&fim='+fim+'&placa='+placa+'&vr='+vr;
      var jan  = window.open(sUrl,'', 'width='+(screen.availWidth-5)+',height='+ (screen.availHeight-40)+',scrollbars=1,location=0 ');
                         
    }

  </script>
  </body>
</html>

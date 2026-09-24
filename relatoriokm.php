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
                <legend>Controle de Hodômetro de Veículos</legend>
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
                    <td nowrap title="<?=@$Tve40_veiccadcentral?>">
                      <?php $Lve40_veiccadcentral = "Cód. Central:"; ?>
                      <?  db_ancora(@$Lve40_veiccadcentral,"js_pesquisacentral(true);",$db_opcao); ?>
                    </td>
                    <td> 
                      <? db_input('ve40_veiccadcentral',10,$Ive40_veiccadcentral,true, 'text',$db_opcao," onchange='js_pesquisacentral(false);'") ?>
                      <? db_input('descrdepto',40,$Idescrdepto,true,'text',3,'') ?>
                    </td>
                  </tr>

                  <tr>
                    <td>
                      <a href="#" class="dbancora" style="text-decoration:underline;" onclick="js_pesquisaveiculos(true);">Veículo:</a>
                    </td>

                    <td>
                      <input title="" name="codveiculo" type="text" id="codveiculo" value="" size="10" maxlength="" onchange="js_pesquisaveiculos(false);" onblur="js_ValidaMaiusculo(this,'',event);" oninput="js_ValidaCampos(this,0,'','','',event);" onkeydown="return js_controla_tecla_enter(this,event);" autocomplete="">                        
                      <input title="" name="placaveiculo" type="text" id="placaveiculo" value="" size="40" maxlength="" readonly="" style="background-color:#DEB887;" autocomplete="">
                      </td>

                  </tr>


                  <tr>
                    <td>
                      <b>VR:</b>
                    </td>

                    <td>
                      <input name="vr" type="text" id="vr" size="10" maxlength="10" style="text-transform: uppercase;">
                      </td>

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
    document.getElementById("periodo_inicio").required = true;
    document.getElementById("periodo_fim").required = true;
    document.getElementById("ve40_veiccadcentral").required = true;

    function js_pesquisacentral(mostra) {
      if (mostra) {  
        js_OpenJanelaIframe('top.corpo', 
                        'db_iframe_central', 
                        'func_veiccadcentral.php?funcao_js=parent.js_mostracentral1|ve36_sequencial|descrdepto',
                        'Pesquisa de Central', 
                        true);
      } else {  
        if (document.getElementById("ve40_veiccadcentral").value != '') { 
          js_OpenJanelaIframe('top.corpo',
                            'db_iframe_central',
                            'func_veiccadcentral.php?pesquisa_chave='+document.getElementById("ve40_veiccadcentral").value+
                            '&funcao_js=parent.js_mostracentral', 
                            'Pesquisa de Central',
                            false);
        } else {
       document.getElementById("descrdepto").value = ''; 
     }
  }
}

function js_mostracentral(chave,erro, descrdepto) {
  console.log("Erro - " + erro);
  document.getElementById("descrdepto").value = descrdepto;
  if (erro == true) {    
    document.getElementById("ve40_veiccadcentral").value = "";
    document.getElementById("descrdepto").value = "Código Inválido";
    document.getElementById("ve40_veiccadcentral").focus();
  }
}
function js_mostracentral1(chave1, chave2) {
  document.getElementById("ve40_veiccadcentral").value = chave1;
  document.getElementById("descrdepto").value = chave2;
  db_iframe_central.hide();
}



function js_pesquisaveiculos(mostra) {
      if (mostra) {  
        js_OpenJanelaIframe('top.corpo', 
                        'db_iframe_central', 
                        'func_veiculoskm.php?funcao_js=parent.js_mostraveiculos1|ve01_codigo|ve01_placa',
                        'Pesquisa de Central', 
                        true);
      } else {  
        if (document.getElementById("codveiculo").value != '') { 
          js_OpenJanelaIframe('top.corpo',
                            'db_iframe_central',
                            'func_veiculoskm.php?pesquisa_chave='+document.getElementById("codveiculo").value+
                            '&funcao_js=parent.js_mostraveiculos', 
                            'Pesquisa de Central',
                            false);
        } else {
       document.getElementById("placaveiculo").value = ''; 
     }
  }
}

function js_mostraveiculos(chave,erro, placaveiculo) {
  console.log("Erro - " + erro);
  document.getElementById("placaveiculo").value = placaveiculo;
  if (erro == true) {    
    document.getElementById("codveiculo").value = "";
    document.getElementById("placaveiculo").value = "Código Inválido";
    document.getElementById("codveiculo").focus();
  }
}
function js_mostraveiculos1(chave1, chave2) {
  document.getElementById("codveiculo").value = chave1;
  document.getElementById("placaveiculo").value = chave2;
  db_iframe_central.hide();
}


    const URL_RELATORIO   = "gerarelatoriokm.php";
  
    var oPeriodoInicio    = $('periodo_inicio');
    var oPeriodoInicioDia = $('periodo_inicio_dia');
    var oPeriodoInicioMes = $('periodo_inicio_mes');
    var oPeriodoInicioAno = $('periodo_inicio_ano');
    var oPeriodoFim       = $('periodo_fim');
    var oPeriodoFimDia    = $('periodo_fim_dia');
    var oPeriodoFimMes    = $('periodo_fim_mes');
    var oPeriodoFimAno    = $('periodo_fim_ano');
    

    function js_emitir() {

      var sPeriodoInico = "";
      var sPeriodoFim   = "";
      var codCentral = document.getElementById("ve40_veiccadcentral").value;
      var veiculo = document.getElementById("codveiculo").value;
      var vr = document.getElementById("vr").value;

      if (!empty(oPeriodoInicio.value)) {
        sPeriodoInico = oPeriodoInicioAno.value + "-" + oPeriodoInicioMes.value + "-" + oPeriodoInicioDia.value;
      }

      if (!empty(oPeriodoFim.value)) {
        sPeriodoFim = oPeriodoFimAno.value + "-" + oPeriodoFimMes.value + "-" + oPeriodoFimDia.value;
      }
      var oDataInicial = new Date(oPeriodoInicioAno.value, oPeriodoInicioMes.value, oPeriodoInicioDia.value, 0, 0, 0);
      var oDataFinal   = new Date(oPeriodoFimAno.value, oPeriodoFimMes.value, oPeriodoFimDia.value, 0, 0, 0);

      if (empty(oPeriodoInicio.value)) {
        alert("A Data Inicial do campo Período é de preenchimento obrigatório.");
        return false;
      }

      if (empty(oPeriodoFim.value)) {
        alert("A Data Final do campo Período é de preenchimento obrigatório.");
        return false;
      }

      if (oDataInicial.valueOf() > oDataFinal.valueOf()) {
        alert("A Data Final do campo Período deve ser maior ou igual a Data Inicial.");
        return false;
      }

      if(empty(codCentral)){
        alert("O Código Central é obrigatório.");
        return false;
      }

      if(empty(veiculo) && empty(vr)){
        alert("Preencha o campo Veículo ou Vr..");
        return false;
      }      
      

      var sQuery = '?';
      sQuery += 'periodo_inicial=' + sPeriodoInico;
      sQuery += '&periodo_final='  + sPeriodoFim;
      sQuery += '&codigo_central='  + codCentral;
      sQuery += '&aVeiculos=' + veiculo;
      sQuery += '&vr=' + vr;
           

      var iHeight = (screen.availHeight - 40);
      var iWidth  = (screen.availWidth - 5);
      var sOpcoes = 'width=' + iWidth + ',height=' + iHeight + ',scrollbars=1,location=0';
      var oJanela = window.open(URL_RELATORIO + sQuery, '', sOpcoes);
      oJanela.moveTo(0, 0);
    }

  </script>
  </body>
</html>

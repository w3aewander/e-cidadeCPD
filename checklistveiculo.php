<?php
/*
 *     E-cidade Software Publico para Gestao Municipal
 *  Copyright (C) 2014  DBseller Servicos de Informatica
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
require_once("libs/db_conecta.php");
require_once("libs/db_sessoes.php");
require_once("libs/db_utils.php");
require_once("libs/db_app.utils.php");
require_once("dbforms/db_funcoes.php");
?>
<html xmlns="http://www.w3.org/1999/html">
<head>
  <title>Microsist</title>
  <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
  <script type="text/javascript" src="scripts/scripts.js"></script>
  <script type="text/javascript" src="scripts/strings.js"></script>
  <script type="text/javascript" src="scripts/prototype.js"></script>
  <script type="text/javascript" src="scripts/AjaxRequest.js"></script>
  <link href="estilos.css" rel="stylesheet" type="text/css">
</head>
<body class="body-default">
<div class="container">
  <form id="formReemissaoOrdemServico">
    
    <fieldset>
      <legend><strong>Emissão Checklist Veículo</strong></legend>
      <tr>          
        <td>
          <label class="bold" for="">
            <a href="#" class="dbancora" style="text-decoration:underline;" onclick="buscarVeiculo(true);"><strong>Veículo:</strong></a>
          </label>
        </td>
        <td>            
          <input title="Veículo" name="ve13_veiculo" type="text" id="ve13_veiculo" value="" size="10" maxlength="10" onchange="buscarVeiculo(false);" onblur="js_ValidaMaiusculo(this,'f',event);" oninput="js_ValidaCampos(this,1,'Veículo','f','f',event);" onkeydown="return js_controla_tecla_enter(this,event);" autocomplete="off">  
          <input title="" name="descricao_veiculo" type="text" id="descricao_veiculo" value="" size="10" maxlength="" readonly="" style="background-color:#DEB887;" autocomplete="">
        </td>        
      </tr>

    <input type="button" id="btnReemitir" onClick="relchecklist();" value="Emitir" />            
    </fieldset>
  </form>
</div>
<script>
var oCodigoVeiculo      = $('ve13_veiculo');
  var oDescricaoVeiculo   = $('descricao_veiculo');
  var oCodigoMotorista    = $('ve13_motorista');
  var oDescricaoMotorista = $('descricao_motorista');
  
  function relchecklist() {
    

    var sUrl = "relchecklistveiculo.php?placa="+oDescricaoVeiculo.value;
    jan = window.open(sUrl,'','width=' + (screen.availWidth - 5 ) + ',height=' + (screen.availHeight - 40) + ',scrollbars=1,location=0 ');
    jan.moveTo(0,0);
  }

  function buscarVeiculo(lMostrar) {

    var sArquivo     = "func_veiculosalt.php";
    var sTituloTela  = "Pesquisar Veículos";
    var sQueryString = "funcao_js=parent.retornoVeiculos|ve01_codigo|ve01_placa";

    if (!lMostrar) {
      sQueryString = 'pesquisa_chave=' + oCodigoVeiculo.value + '&funcao_js=parent.retornoVeiculosChave';
    }

    js_OpenJanelaIframe('', 'db_iframe_veiculos', sArquivo + '?' + sQueryString, sTituloTela, lMostrar);
  }

  /**
   * Função de retorno para a busca de veículos ao clicar na âncora.
   * @param {int}    iCodigo Código do veículo selecionado.
   * @param {string} sPlaca  Placa do veículo selecionado.
   */
  function retornoVeiculos(iCodigo, sPlaca) {

    oCodigoVeiculo.value    = iCodigo;
    oDescricaoVeiculo.value = sPlaca;
    db_iframe_veiculos.hide();
  }

  /**
   * Função de retorno para busca de veículo digitando na âncora.
   * @param {string } sPlaca Placa do veículo encontrado.
   * @param {boolean} lErro  Caso não tenha encontrado registro para o código dado.
   */
  function retornoVeiculosChave(sPlaca, lErro) {

    var iCodigo = oCodigoVeiculo.value;
    if (lErro) {
      iCodigo = '';
    }
    retornoVeiculos(iCodigo, sPlaca);
  }
</script>
<?php db_menu(); ?>
</body>
</html>

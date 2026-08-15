<?php
/**
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

require_once(modification("libs/db_stdlib.php"));
require_once(modification("libs/db_conecta".".php"));
require_once(modification("libs/db_sessoes.php"));
require_once(modification("libs/db_utils.php"));
require_once(modification("libs/db_app.utils.php"));
require_once(modification("dbforms/db_funcoes.php"));
require_once(modification("classes/db_gerfcom_classe.php"));

$clgerfcom = new cl_gerfcom;

$clrotulo = new rotulocampo;
$clrotulo->label('DBtxt23');
$clrotulo->label('DBtxt25');
$clrotulo->label('DBtxt27');
$clrotulo->label('DBtxt28');

db_utils::postMemory($_POST);
$db_opcao = 1;

if (!isset($tipo)) {
  $tipo = "l";
}

if (!isset($filtro)) {
  $filtro = "i";
}

if (isset($anofolha) && !empty($anofolha)) {
  $anofolha = DBPessoal::getAnoFolha();
}

if (isset($mesfolha) && !empty($mesfolha)) {
  $mesfolha = DBPessoal::getMesFolha();
}

$sJavaScriptInput = 'class="field-size1" onchange="js_buscaComplementar();"';
try {
  if (DBPessoal::verificarUtilizacaoEstruturaSuplementar()) {
    $sJavaScriptInput = 'class="field-size1" onchange="js_buscaComplementar(); js_buscaSuplementar();"';
  }
} catch (Exception $eErro) {

}
$DBtxt23 = DBPessoal::getAnoFolha();
$DBtxt25 = DBPessoal::getMesFolha();
?>
<html>
<head>
  <title>Microsist - Página Inicial</title>
  <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
  <meta http-equiv="Expires" content="0">
  <link rel="stylesheet" href="estilos.css">
  <link rel="stylesheet" href="estilos/grid.style.css">
</head>
<body onload="a=1">
  <form action="" name="form1" method="" class="container" style="width: 700px">
    <fieldset>
      <legend>Relação Cargo e Função:</legend>
      <fieldset>
        <legend>Filtros do Relatório:</legend>
        <table class="form-container">
          <tr>
            <td nowrap width="130" title="Ano / Mês de competência">
              <label>Ano / Mês:</label>
            </td>
            <td>
              <?php db_input('DBtxt23', 4, $IDBtxt23, true, 'text', 2, $sJavaScriptInput); ?>
              <?php db_input('DBtxt25', 2, $IDBtxt25, true, 'text', 2, $sJavaScriptInput); ?>
            </td>
          </tr>     
          <tr title="Seleção">
            <td>
              <?php db_ancora("Seleção:", "js_pesquisaSelecao(true)", 1); ?>
            </td>
            <td> 
              <?php db_input('r44_selec', 8, 1, true, 'text', "", 'class="field-size2" onchange="js_pesquisaSelecao(false)"'); ?>
              <?php db_input('r44_des', 30, "", true, 'text', 3, 'class="field-size7"'); ?> 
            </td>
          </tr>
          <tr title="Regime">
            <td>
              <label>Regime:</label>
            </td>
            <td id="ContainerRegime"></td>
          </tr>
          <tr>
            <td colspan="2" id="containnerTipoFiltrosFolha"></td>
          </tr>
          <tr title="Vinculo">
            <td width="130">
              <label>Vínculo:</label>
            </td>
            <td id="ContainerVinculo"></td>
          </tr>
        </table>
      </fieldset>
<table class="form-container" style="width: 100%">

              <tr title="Tipo de ordem">
                <td width="130"><b>Ordenação:</b></td>
                <td>
                 <?
                   $aTipoOrdem = array(
                    "n"  => "Nome",
                    "m"  => "Matrícula",
                    "c"  => "Cargo"
                    );
                   db_select('tipo_filtro',$aTipoOrdem,true,4,"");
                 ?>
                </td>
              </tr>

          </table>
    </fieldset>
    <input name="emite2" id="emite2" type="button" value="Processar" onclick="js_emite();"/>
  </form>
  <?php db_menu(); ?>
</body>
<script src="scripts/scripts.js"></script>
<script src="scripts/strings.js"></script>
<script src="scripts/prototype.js"></script>
<script src="scripts/arrays.js"></script>
<script src="scripts/datagrid.widget.js"></script>
<script src="scripts/widgets/dbcomboBox.widget.js"></script>
<script src="scripts/widgets/dbtextField.widget.js"></script>
<script src="scripts/classes/DBViewTipoFiltrosFolha.js"></script>
<script src="scripts/classes/DBViewFormularioFolha/ComboRegime.js"></script>
<script src="scripts/classes/DBViewFormularioFolha/ComboPrevidencia.js"></script>
<script src="scripts/classes/DBViewFormularioFolha/ComboVinculo.js"></script>
<script>

  var oTiposFiltrosFolha;

  /**
   * Realiza a busca de Lotação;
   */
  function js_pesquisaLotacao(oInput) {

    var sFuncaoJS;
    
    if ( oInput == 'lotai' ) {
      sFuncaoJS = 'js_mostralotacaoInicio';
    } else {
      sFuncaoJS = 'js_mostralotacaoFinal';
    }
      
    js_OpenJanelaIframe(
      'CurrentWindow.corpo',
      'db_iframe_selecao',
      'func_rhlota.php?funcao_js=parent.' + sFuncaoJS + '|r70_codigo&instit<?=db_getsession("DB_instit")?>',
      'Pesquisa',
      true
    );
  } 

 /**
  * Trata o retorno da função js_pesquisaLotacao();
  */
  function js_mostralotacaoInicio(sChave1) {
    $(lotai).setValue(sChave1);
    db_iframe_selecao.hide();
  }

  function js_mostralotacaoFinal(sChave1) {
    $(lotaf).setValue(sChave1);
    db_iframe_selecao.hide();
  }

  /**
   * Realiza a busca de seleções retornando o código e descrição da rubrica escolhida;
   */
  function js_pesquisaSelecao(lMostra) {
          
    if (lMostra) {
      js_OpenJanelaIframe(
        'CurrentWindow.corpo',
        'db_iframe_selecao',
        'func_selecao.php?funcao_js=parent.js_geraform_mostraselecao1|r44_selec|r44_descr&instit=<?=db_getsession("DB_instit")?>',
        'Pesquisa',
        true
      );
    } else {
      if ($F(r44_selec) != "") {
        js_OpenJanelaIframe(
          'CurrentWindow.corpo',
          'db_iframe_selecao',
          'func_selecao.php?pesquisa_chave=' + $F(r44_selec) + '&funcao_js=parent.js_geraform_mostraselecao&instit=<?=db_getsession("DB_instit")?>',
          'Pesquisa',
          false
        );
      } else {
        $(r44_des).setValue(""); 
      }
    }
  }

  /**
   * Trata o retorno da função js_pesquisaSelecao().
   */
  function js_geraform_mostraselecao(sDescricao, lErro) {
    
    if (lErro) { 

      $(r44_selec).setValue('');
      $(r44_selec).focus(); 
    }
    
    $(r44_des).setValue(sDescricao); 
  }

  /**
   * Trata o retorno da função js_pesquisaSelecao();
   */
  function js_geraform_mostraselecao1(sChave1, sChave2) {
      
    $(r44_selec).setValue(sChave1);
    
    if($(r44_des)) {
      $(r44_des).setValue(sChave2);
    }
    
    db_iframe_selecao.hide();
  }

  /**
   * Funcao responsavel por realizar a validaçãoo dos dados e tratar os dados para a geração do relatório  
   */
  function js_emite() {

    /**
     * Validar ano/mes vazio
     */
    if ($F('DBtxt23') == '' || $F('DBtxt25') == '') {
      
      alert('Por favor, informe Ano/Mês para a emissão do relatório.');
      return false;
    }


    if ($F('DBtxt23') <= 0) {

      alert('Ano da folha informado é invalido.');
      return false;
    }

    if ($F('DBtxt25') <= 0 || $F('DBtxt25') > 12) {

      alert('Mês da folha informado é invalido.');
      return false;
    }
      
    /**
     * Se o tipo de relatorio dif  rente de geral e tipo de filtro igual a selecionado,
     * obrigar o lançamento de 1 registro no respectivo lançador.
     */
    var oTipoRelatorio = $F('oCboTipoRelatorio');
    var oTipoFiltro    = $F('oCboTipoFiltro');

    if (oTipoRelatorio != 0 && oTipoFiltro == 2) {
      
      var oLancadorSelecionado = oTiposFiltrosFolha.getLancadorAtivo().getRegistros();
      if (oLancadorSelecionado.length == 0) {

        alert('Por Favor, realize pelo menos o lançamento de 1 registro.');
        return false 
      }
    }

    /**
     * Se o tipo de relatorio diferente de geral e tipo de filtro igual a Intervalo,
     * obrigar o preenchimento de intervalo.
     */
    if (oTipoRelatorio != 0 && oTipoFiltro == 1) {

      if ($F('InputIntervaloInicial') == '' || $F('InputIntervaloFinal') == '') {

        alert('Por favor, informe o intervalo para geração do relatório.');
        return false;
      }
     }

    /**
     * Envia os dados para a geração do relatório.
     */       
    var oQuery = {};
    oQuery.iAno           = $F('DBtxt23');
    oQuery.iMes           = $F('DBtxt25');
    oQuery.iSelecao       = $F('r44_selec');
    oQuery.iRegime        = $F('Regime');
    oQuery.iTipoRelatorio = $F('oCboTipoRelatorio');
    oQuery.iTipoFiltro    = $F('oCboTipoFiltro');
     oQuery.sVinculo      = $F('Vinculo');
    /**
     * Verifica se o tipo escolhido foi intervalo 
     */
    if (oTipoFiltro == 1) {
       
      oQuery.iIntervaloInicial = $F('InputIntervaloInicial');
      oQuery.iIntervaloFinal   = $F('InputIntervaloFinal');
    }

    /**
     * Verifica se o tipo escolhido foi seleção
     */
    if (oTipoFiltro == 2) {
       
      var aSelecionados = [];
      var oTipoFiltros  = oTiposFiltrosFolha.getLancadorAtivo().getRegistros();

      /**
       * Percorre os itens selecionados no lancador
       */
      oTipoFiltros.each (function(oFiltro, iIndice) {
        aSelecionados[iIndice] = oFiltro.sCodigo;
      });
       
      oQuery.iRegistros = aSelecionados;
    }

    oQuery.sOrdem = $F('tipo_filtro');


    var oJanela = window.open(
       'rec2_relcargfunc002.php?json=' + Object.toJSON(oQuery),
       '',
       'width=' + (screen.availWidth - 5) + ',height=' + (screen.availHeight - 40) + ',scrollbars=1,location=0 '
     );

    oJanela.moveTo(0, 0);
    return false;
  }    

  /**
   * Realiza a busca dos Complementar usando Ano e Mes.
   */
  function js_buscaComplementar() {
    
    var iAno = $F('DBtxt23');
    var iMes = $F('DBtxt25');
    var sUrl = 'pes1_rhempenhofolhaRPC.php';
    
    var sQuery  = 'sMethod=consultaPontoComplementar';
        sQuery += '&iAnoFolha='+iAno;
        sQuery += '&iMesFolha='+iMes;
        sQuery += '&sSigla="Complementar"';
        
    
    var oAjax   = new Ajax.Request( sUrl, {
                                            method: 'post', 
                                            parameters: sQuery, 
                                            onComplete: js_retornoComplementar
                                          }
                                  );
  }

  /**
   * Trata o Retorno do complementar
   */
  function js_retornoComplementar(oComplementar) {

    $('selectComplementar').options.length = 0;
    var aRetorno = eval("("+oComplementar.responseText+")");
    
    if (aRetorno.aSemestre.length > 0) {

      var oOptionDefault = new Option('Todos', '');
      $('selectComplementar').add(oOptionDefault);

      /**
       * Percorre o Array de retorno montando os Options
       */
      for (var iIndiceComplementar = 0; iIndiceComplementar < aRetorno.aSemestre.length; iIndiceComplementar++) {

        var iComplementar       = aRetorno.aSemestre[iIndiceComplementar].semestre;
        var oOptionComplementar = new Option(iComplementar, iComplementar);
        $('selectComplementar').add(oOptionComplementar);
      }
    }
  }

<?php if (DBPessoal::verificarUtilizacaoEstruturaSuplementar()) { ?>
  /**
   * Realiza a busca dos Suplementar usando Ano e Mes.
   */
  function js_buscaSuplementar() {
    
    var iAno = $F('DBtxt23');
    var iMes = $F('DBtxt25');
    var sUrl = 'pes1_rhempenhofolhaRPC.php';
    
    var sQuery = 'sMethod=consultaPontoSuplementar'
               + '&iAnoFolha=' + iAno
               + '&iMesFolha=' + iMes
               + '&sSigla="Suplementar"';
        
    
    var oAjax   = new Ajax.Request(sUrl, {
      method: 'POST',
      parameters: sQuery,
      onComplete: js_retornoSuplementar
    });
  }

  /**
   * Trata o Retorno do complementar
   */
  function js_retornoSuplementar(oSuplementar) {

    $('selectSuplementar').options.length = 0;
    var aRetorno = eval("("+oSuplementar.responseText+")");
    
    if (aRetorno.aSemestre.length > 0) {

      var oOptionDefault = new Option('Todos', '');
      $('selectSuplementar').add(oOptionDefault);

      /**
       * Percorre o Array de retorno montando os Options
       */
      for (var iIndiceSuplementar = 0; iIndiceSuplementar < aRetorno.aSemestre.length; iIndiceSuplementar++) {

        var iSuplementar       = aRetorno.aSemestre[iIndiceSuplementar].semestre;
        var oOptionSuplementar = new Option(iSuplementar, iSuplementar);
        $('selectSuplementar').add(oOptionSuplementar);
      }
    }
  }
<?php } ?>
  
  (function() {

    /**
     * Monta os componentes para o formulario
     */
    var oComboRegime       = new DBViewFormularioFolha.ComboRegime();
    var oComboPrevidencia  = new DBViewFormularioFolha.ComboPrevidencia();
    var oComboVinculo      = new DBViewFormularioFolha.ComboVinculo();
    oTiposFiltrosFolha     = new DBViewFormularioFolha.DBViewTipoFiltrosFolha(<?=db_getsession("DB_instit")?>);
    oTiposFiltrosFolha.sInstancia     = 'oTiposFiltrosFolha';
    
    /**
     * Renderiza os componentes em seus respectivos contaners
     */
    oComboRegime.show($('ContainerRegime'));
    oComboVinculo.show($('ContainerVinculo'));
    oTiposFiltrosFolha.show($('containnerTipoFiltrosFolha'));

    $('oCboTipoRelatorio').remove(6);
    js_buscaComplementar();
    <?php if (DBPessoal::verificarUtilizacaoEstruturaSuplementar()) { ?>
    js_buscaSuplementar();
    <?php } ?>
    
    $('selectComplementar').observe("change", function() {
      oGridTipoFolha.aRows[1].select(true);
    })
  })();
</script>
</html>

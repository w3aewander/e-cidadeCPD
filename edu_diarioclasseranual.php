<?
/*
 *     E-cidade Software Publico para Gestao Municipal
 *  Copyright (C) 2013  DBselller Servicos de Informatica
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

include("libs/db_stdlibwebseller.php");
require("libs/db_stdlib.php");
require("libs/db_conecta.php");
include("libs/db_sessoes.php");
include("libs/db_usuariosonline.php");
include("classes/db_turma_classe.php");
include("classes/db_procavaliacao_classe.php");
include("dbforms/db_funcoes.php");

$sMsgTitle = "Para selecionar mais de uma turma, mantenha pressionada a tecla CTRL e clique sobre o nome das turmas. ";
?>
<html>
<head>
<title>DBSeller Inform&aacute;tica Ltda</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<meta http-equiv="Expires" CONTENT="0">
<script language="JavaScript" type="text/javascript" src="scripts/scripts.js"></script>
<script language="JavaScript" type="text/javascript" src="scripts/prototype.js"></script>
<script language="JavaScript" type="text/javascript" src="scripts/strings.js"></script>
<script language="JavaScript" type="text/javascript" src="scripts/DBFormCache.js"></script>
<script language="JavaScript" type="text/javascript" src="scripts/DBFormSelectCache.js"></script>
<script language="JavaScript" type="text/javascript" src="scripts/widgets/dbcomboBox.widget.js"></script>
<link href="estilos.css" rel="stylesheet" type="text/css">
</head>

<body bgcolor="#CCCCCC">
<div class='container'>
  <?MsgAviso(db_getsession("DB_coddepto"),"escola");?>
  <form action="" ></form>
  <fieldset>
    <legend>Relatório de Conselho de Classe</legend>
    <table class='form-container'>
      <tr>
        <td class='bold field-size4' nowrap="nowrap">Calendário:</td>
        <td nowrap="nowrap">
          <select id='cntCalendario' onchange="js_buscaPeriodos(); js_buscaTurmas();">
            <option value=''>Selecione</option>
          </select>
        </td>
      </tr>

      <tr style="display:none">
        <td class='bold' nowrap="nowrap">Período:</td>
        <td nowrap="nowrap">
          <select id='cntPeriodo' >
            <option value=''>Selecione</option>
          </select>
        </td>
      </tr>
    </table>
    <fieldset class="separator">
      <legend>Turmas:</legend>
      <select id='cntTurmas' title="<?php echo $sMsgTitle;?>" multiple="multiple" >
      </select>
    </fieldset>

    <table>

           <tr>
            <td>
              <?
                db_ancora("<b>Assinatura Adicional: </b>", "js_pesquisaRecHumano(true);", 1);
              ?>
            </td>
            <td>
              <?
                db_input("ed20_i_codigo", 6, $Ied20_i_codigo, true, "text", 1, "onChange='js_pesquisaRecHumano(false);'");
                db_input("z01_numcgm", 6, $Iz01_numcgm, true, "hidden", 3);
                db_input("z01_nome", 34, $Iz01_nome, true, "text", 3);
              ?>
            </td>
          </tr>
          <tr>
            <td style="width: 150px"><b>Atividades: </b></td>
            <td id='ctnAtividades'></td>
          </tr>
    </table>
    <?php /* ?>
    <table class='form-container'>
      <tr>
        <td class='bold field-size4' nowrap="nowrap">Exibir Troca de Turma:</td>
        <td nowrap="nowrap">
          <select id='exibeTrocaTurma'>
            <option value='Sim' >Sim</option>
            <option value='Não' selected="selected">Não</option>
          </select>
        </td>
      </tr>
      <tr>
        <td class='bold' nowrap="nowrap">Exibir Classificação Aluno:</td>
        <td nowrap="nowrap">
          <select id='exibeClassificacao'>
            <option value='Sim' selected="selected">Sim</option>
            <option value='Não' >Não</option>
          </select>
          </select>
        </td>
      </tr>
      <tr>
        <td class='bold' nowrap="nowrap">Fonte Quadro de Notas:</td>
        <td nowrap="nowrap">
          <select id='tamanhoFonte'>
            <option value='6' selected="selected">6</option>
            <option value='7' >7</option>
            <option value='8' >8</option>
            <option value='9' >9</option>
          </select>
          </select>
        </td>
      </tr>
      <tr>
        <td class='bold' nowrap="nowrap">Mostrar Legenda:</td>
        <td nowrap="nowrap">
          <select id='comLegenda'>
            <option value='Sim' >Sim</option>
            <option value='Não' selected="selected">Não</option>
          </select>
          </select>
        </td>
      </tr>
    </table>
<?php */ ?>

  </fieldset>
  <input type="button" value='Imprimir' name='imprimir' id='imprimir'>
</div>
<?php
db_menu(db_getsession("DB_id_usuario"),db_getsession("DB_modulo"),db_getsession("DB_anousu"),db_getsession("DB_instit"));
?>
</body>
<script type="text/javascript">

var oDBFormCache = new DBFormCache('oDBFormCache', 'edu_diarioclasseranual.php');

//oDBFormCache.setElements(new Array($('exibeTrocaTurma')));
//oDBFormCache.setElements(new Array($('exibeClassificacao')));
//oDBFormCache.setElements(new Array($('tamanhoFonte')));
//oDBFormCache.setElements(new Array($('comLegenda')));

oDBFormCache.load();


(function () {

  $('cntTurmas').style.height    = '180px';
  $('cntTurmas').style.width     = '300px';
  $('cntTurmas').options.length  = 0;

  var oParametro = {};
  oParametro.exec = 'pesquisaCalendario';

  var oObjeto        = new Object();
  oObjeto.method     = 'post';
  oObjeto.parameters = 'json='+Object.toJSON(oParametro);
  oObjeto.onComplete = function(oAjax) {
                         js_retornoCalendarios(oAjax);
                       };
  js_divCarregando("Aguarde, buscando calendários.", "msgBox");
  new Ajax.Request('edu_educacaobase.RPC.php', oObjeto);

})();

function js_retornoCalendarios(oAjax) {

  js_removeObj('msgBox');
  var oRetorno = eval('('+oAjax.responseText+')');

  if (oRetorno.dados.length == 0) {
    alert('Nenhum calendário para escola');
    return false;
  }
  oRetorno.dados.each( function (oCalendario) {

    $('cntCalendario').add(new Option(oCalendario.ed52_c_descr.urlDecode(), oCalendario.ed52_i_codigo));
  });
}

function js_buscaPeriodos() {

  $('cntTurmas').options.length  = 0;
  $('cntPeriodo').options.length = 0;
  if ($F('cntCalendario') == '') {
    return false;
  }
  $('cntPeriodo').add(new Option("Selecione", ""));

  var oParametro                 = {};
  oParametro.exec                = 'buscaPeriodosAvaliacaoEscola';
  oParametro.lFiltraEscolaLogada = true;
  oParametro.iCalendario         = $F('cntCalendario');

  var oObjeto        = new Object();
  oObjeto.method     = 'post';
  oObjeto.parameters = 'json='+Object.toJSON(oParametro);
  oObjeto.onComplete = function(oAjax) {
                         js_retornoPeriodos(oAjax);
                       };
  js_divCarregando("Aguarde, buscando períodos.", "msgBox");
  new Ajax.Request('edu_educacaobase.RPC.php', oObjeto);
}

function js_retornoPeriodos(oAjax) {

  js_removeObj('msgBox');
  var oRetorno = eval('('+oAjax.responseText+')');

  if (oRetorno.dados.length == 0) {

    alert('Nenhum período cadastrado para escola.');
    return false;
  }
  oRetorno.dados.each( function (oPeriodos) {

    $('cntPeriodo').add(new Option(oPeriodos.descricao_periodo.urlDecode(), oPeriodos.codigo_periodo));
  });
}

function js_buscaTurmas() {

  $('cntTurmas').options.length  = 0;
  if ($F('cntCalendario') == '') {
    return false;
  }

  var oParametro                    = {};
  oParametro.exec                   = 'pesquisaTurmaEtapa';
  oParametro.lComAlunosMatriculados = true;
  oParametro.iCalendario            = $F('cntCalendario');

  var oObjeto        = new Object();
  oObjeto.method     = 'post';
  oObjeto.parameters = 'json='+Object.toJSON(oParametro);
  oObjeto.onComplete = function(oAjax) {
                         js_retornoTurmas(oAjax);
                       };
  js_divCarregando("Aguarde, buscando turmas.", "msgBox");
  new Ajax.Request('edu_educacaobase.RPC.php', oObjeto);
}

function js_retornoTurmas(oAjax) {

  js_removeObj('msgBox');
  var oRetorno = eval('('+oAjax.responseText+')');

  if (oRetorno.dados.length == 0) {

    alert('Nenhuma turma vínculada ao calendário ou turmas sem alunos matriculados.');
    return false;
  }
  oRetorno.dados.each( function (oTurma) {

    var oOption = new Option(oTurma.ed57_c_descr.urlDecode(), oTurma.ed57_i_codigo);
    oOption.setAttribute('etapa', oTurma.codigo_etapa);
    $('cntTurmas').add(oOption);
  });
}


$('imprimir').observe('click', function () {

  if ($F('cntCalendario') == '') {

    alert('Selecione um calendario.');
    return false;
  }
    /*if ($F('cntPeriodo') == '') {
      alert('Selecione um periodo.');
      return false;
    }*/

  var aTurmas = new Array();
  var iTurmas = $('cntTurmas').options.length;

  for (var i = 0; i < iTurmas; i++) {

    if ($('cntTurmas').options[i].selected) {
      var oTurma = {iTurma: $('cntTurmas').options[i].value, iEtapa : $('cntTurmas').options[i].getAttribute('etapa')};
      aTurmas.push(oTurma);
    }
  }

  if (aTurmas.length == 0) {

    alert('Selecione ao menos uma turma para imprimir o relatório.');
    return false;
  }
  var xcalendario = document.getElementById("cntCalendario").value;
//  var disciplina  = document.getElementById("unicaDisciplina").value;
  var xtexto = document.getElementById("cntCalendario").options[document.getElementById("cntCalendario").selectedIndex].text;

//  alert(xtexto.substring(0,13));

  if(xtexto.substring(0,13) == "ANOS INICIAIS" || xtexto.substring(0,12) == "EJA INICIAIS" || xtexto.substring(0,12) == "ED. INFANTIL"){
	var sUrl  = 'edu_diarioclasseresumoanualai002.php?periodo='+$F('cntPeriodo');
  }else{
    var sUrl  = 'edu_diarioclasseresumoanual002.php?periodo='+$F('cntPeriodo');
  }

  /**
   * Autor: Uemerson Santana
   * Data: 07/10/2025
   * Demanda: 17874
   */
  var assadi = document.getElementById("z01_nome").value;
  var assadia = document.getElementById("cboAtividades").options[document.getElementById("cboAtividades").selectedIndex].text;
  var cgmAssadi = document.getElementById("z01_numcgm").value;



  //sUrl += '&trocaTurma='+$F('exibeTrocaTurma');
  sUrl += '&trocaTurma=Sim';
  //sUrl += '&classificacaoAlunoTurma='+$F('exibeClassificacao');
  sUrl += '&classificacaoAlunoTurma=Sim';
  sUrl += '&oTurmas='+Object.toJSON(aTurmas);
  //sUrl += '&tamanhoFonte='+$F('tamanhoFonte');
  sUrl += '&tamanhoFonte=9';
  //sUrl += '&comLegenda='+$F('comLegenda');
  sUrl += '&comLegenda=Não';
  sUrl += '&calendario='+xcalendario;
//  sUrl += '&ed232_c_descr='+ed232_c_descr;
  if(assadi != ""){
    sUrl += '&aa='+assadi+'&at='+assadia+'&cgmaa='+cgmAssadi;
  }else{
    sUrl += '&aa=nao';
  }
//  sUrl += '&disciplina='+disciplina;

  jan = window.open(sUrl,'','width='+(screen.availWidth-5)+',height='+(screen.availHeight-40)+',scrollbars=1,location=0 ');
  jan.moveTo(0,0);
  oDBFormCache.save();

});

oCboAtividades = new DBComboBox("cboAtividades", "oCboAtividades", null, "330px");
oCboAtividades.addItem("", "");
oCboAtividades.setDisable(true);
oCboAtividades.show($('ctnAtividades'));


function js_pesquisaRecHumano(lMostra) {


if (lMostra) {

  js_OpenJanelaIframe('CurrentWindow.corpo',
                      'db_iframe_rechumano',
                      'func_rechumanoescolanovo2.php?funcao_js=parent.js_mostraRecHumano|ed20_i_codigo|z01_nome|z01_numcgm',
                      'Pesquisa Recurso Humano',
                      true
                     );
} else if ($F('ed20_i_codigo') != '') {

  js_OpenJanelaIframe('CurrentWindow.corpo',
                      'db_iframe_rechumano',
                      'func_rechumanoescolanovo2.php?funcao_js=parent.js_mostraRecHumano1&pesquisa_chave='+$F('ed20_i_codigo'),
                      'Pesquisa Recurso Humano',
                      false
                     );
} else {

  $('ed20_i_codigo').value = '';
  $('z01_nome').value      = '';
  $('z01_numcgm').value    = '';
  oCboAtividades.clearItens();
  oCboAtividades.setDisable(true);
}
}

function js_mostraRecHumano() {

$('ed20_i_codigo').value = arguments[0];
$('z01_nome').value      = arguments[1];
$('z01_numcgm').value    = arguments[2];
db_iframe_rechumano.hide();
js_atividadesDocente();
}

function js_mostraRecHumano1() {

$('z01_nome').value   = arguments[0];
$('z01_numcgm').value = arguments[1];

if (arguments[1] == true) {

  $('ed20_i_codigo').value = '';
  $('z01_nome').value      = arguments[0];
  $('z01_numcgm').value    = '';
  oCboAtividades.setDisable(true);
} else {
  js_atividadesDocente();
}
}

/**
* Buscamos as atividades do docente na escola
*/
function js_atividadesDocente() {

 var oParametro     = new Object();
 oParametro.exec    = 'buscaAtividadesServidor';
 oParametro.iNumCgm = $F('z01_numcgm');

 js_divCarregando("Aguarde, carregando as atividades do funcionário.", "msgBox");
 var oAjax = new Ajax.Request(
                              'edu_educacaobase.RPC.php',
                              {
                                method: 'post',
                                parameters: 'json='+Object.toJSON(oParametro),
                                onComplete: js_retornaAtividadesDocente
                              }
                             );
}

function js_retornaAtividadesDocente(oResponse) {

oCboAtividades.setEnable(true);
oCboAtividades.clearItens();
oCboAtividades.addItem("", "");
js_removeObj("msgBox");
var oRetorno = eval('('+oResponse.responseText+')');

if (oRetorno.aAtividades.length > 0) {

  oRetorno.aAtividades.each(function(oLinha, iSeq) {

    oCboAtividades.addItem(oLinha.iCodigo, oLinha.sDescricao.urlDecode());
    if (oRetorno.aAtividades.length == 1) {
      oCboAtividades.setValue(oLinha.iCodigo);
    }
  });
}

}

function utf8Decode(utf8String) {
      if (typeof utf8String != 'string')
      throw new TypeError('parameter ‘utf8String’ is not a string');

      const unicodeString = utf8String
      .replace(/[\u00e0-\u00ef][\u0080-\u00bf][\u0080-\u00bf]/g,(c) => {
        return String.fromCharCode(((c.charCodeAt(0)&0x0f)<<12)
        | ((c.charCodeAt(1)&0x3f)<<6)
        | ( c.charCodeAt(2)&0x3f)); })
      .replace(/[\u00c0-\u00df][\u0080-\u00bf]/g, (c) => {
        return String.fromCharCode((c.charCodeAt(0)&0x1f)<<6
        | c.charCodeAt(1)&0x3f);
      });
      return unicodeString;
  }

  function utf8Encode(unicodeString) {
      if (typeof unicodeString != 'string')
      throw new TypeError('parameter ‘unicodeString’ is not a string');

      const utf8String = unicodeString
      .replace(/[\u0080-\u07ff]/g,(c) => {
        let cc = c.charCodeAt(0);
        return String.fromCharCode(0xc0 | cc>>6, 0x80 | cc&0x3f); })
      .replace(/[\u0800-\uffff]/g,(c) => {
        let cc = c.charCodeAt(0);
        return String.fromCharCode(0xe0 | cc>>12, 0x80 | cc>>6&0x3F, 0x80 | cc&0x3f);
      });
      return utf8String;
  }

function encode_utf8(s) {
  return unescape(encodeURIComponent(s));
}

function decode_utf8(s) {
  return decodeURIComponent(escape(s));
}

</script>
</html>

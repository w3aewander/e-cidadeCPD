<?
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
require_once("libs/db_app.utils.php");
require_once("libs/db_conecta.php");
require_once("libs/db_sessoes.php");
require_once("libs/db_usuariosonline.php");
require_once("dbforms/db_funcoes.php");

$iEscola = db_getsession("DB_coddepto");
//pg_query("alter table regocorr add disciplina integer");

function testa($var){
  echo "<pre>";
  print_r($var);
  echo "</pre>";
}

function confereObs($c, $t, $p, $d){
  $sql = pg_query("SELECT id FROM regocorr WHERE calendario = {$c} AND turma = {$t} AND periodo = {$p} and disciplina = {$d}");
  $resultado = pg_fetch_all($sql);
  return $resultado[0]["id"];
}


if($_POST){
  $calendario = $_POST["xcalendario"];
  $turma      = $_POST["xturma"];
  $periodo    = $_POST["xperiodo"];
  $obs        = $_POST["xobs"];
  $obs2       = $_POST["xobs2"];
  $codigo     = $_POST["codigo"];



  $confere = confereObs($calendario, $turma, $periodo,$codigo);
  if($confere){
    pg_query("DELETE FROM regocorr WHERE id = {$confere}");
  }  
		
  pg_query("INSERT INTO regocorr(calendario, turma, periodo, obs, obs2, disciplina) VALUES({$calendario}, {$turma}, {$periodo}, '{$obs}','{$obs2}', {$codigo})");
  echo "<script>alert('Observações salvas.');</script>";
  
}

?>

<html>
  <head>
    <title>Microsist</title>
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
    <meta http-equiv="Expires" CONTENT="0">
    <link rel="stylesheet" type="text/css" href="estilos.css">
    <script type="text/javascript" src="scripts/scripts.js"></script>
    <script type="text/javascript" src="scripts/prototype.js"></script>
    <script type="text/javascript" src="scripts/classes/educacao/escola/ListaCalendario.classe.js"></script>
    <script type="text/javascript" src="scripts/classes/educacao/escola/ListaTurma.classe.js"></script>
    <script type="text/javascript" src="scripts/classes/educacao/escola/ListaPeriodoAvaliacao.classe.js"></script>
    <script type="text/javascript" src="scripts/classes/educacao/escola/ListaDisciplinas.classe.js"></script>
    <script type="text/javascript" src="scripts/widgets/DBToggleList.widget.js"></script>
  </head>
  <body bgcolor="#cccccc">

    <div class="container" id="cntRegistroOcorrencia">
      <form id="frmRegistroOcorrencia" method="post" name="form1">
        <fieldset style="width: 500px">
          <legend>Diário de Classe - Observação</legend>
          <table class="form-container">
            <tr>
              <td nowrap="nowrap" class="field-size3">Calendário:</td>
              <td nowrap="nowrap" id='listaCalendarios' ></td>
            </tr>
            <tr>
              <td nowrap="nowrap">Turma:</td>
              <td nowrap="nowrap" id='listaTurmas' ></td>
            </tr>
			
			<tr>
				<td>
				   <a href="#" onclick="js_buscadisciplina();">Buscar disciplina</a>
				</td>
				<td>
				   <input type='text' name='codigo' id='codigo' size="10" style="background: #dcc594;" readonly>
				   <input type='text' name='descricao' id='descricao' size="50" style="background: #dcc594;" readonly>
				</td>   
			</tr>
<!--			
		</table>	
		<table class="form-container">	
-->		
            <tr>
              <td nowrap="nowrap">Período:</td>
              <td nowrap="nowrap" id='listaPeriodos' ></td>
            </tr>

            
            <tr style="visibility:hidden;">
              <td nowrap="nowrap">Páginas</td>
              <td nowrap="nowrap" >
                <select id='numeroPaginas' >
                  <option value='1' selected="selected">1</option>
                  <option value='2'>2</option>
                  <option value='3'>3</option>
                  <option value='4'>4</option>
                  <option value='5'>5</option>
                </select>
              </td> 
            </tr>

            <tr id="vazio"></tr>
          </table>
          
          
          
        </fieldset>
        <? ?>
        <input type="hidden" value=<?php echo $iEscola ?> id="iEscola">
        <?php /* ?><input type="button" disabled='disabled' id='imprimir' value='Imprimir' name='imprimir' /> <?php */ ?>
        <input type="submit" value="Salvar" id="bsalvar" disabled>
      </form>
    </div>
  </body>
  <?
    db_menu(db_getsession("DB_id_usuario"),db_getsession("DB_modulo"),db_getsession("DB_anousu"),db_getsession("DB_instit"));
  ?>
</html>
<script>

  var iEscola   = $F("iEscola");
  var oTurma    = new DBViewFormularioEducacao.ListaTurma();
  var oPeriodo  = new DBViewFormularioEducacao.ListaPeriodoAvaliacao();
  //var oRegencia = new DBViewFormularioEducacao.ListaDisciplinas();
  //oRegencia.show( $('listaRegencias') ) ;

  var oCalendario = new DBViewFormularioEducacao.ListaCalendario();
      oCalendario.setEscola(iEscola);
      oCalendario.getCalendarios();

  /**
   * Função realizada ao alterar o calendário
   * @return {function}
   */
  var fFunctionChangeCalendario = function() {

    var oCalendarioSelecionado = oCalendario.getSelecionados();
    
    oTurma.limpar();
    oPeriodo.limpaElemento();
    //oRegencia.clear();
    //$('imprimir').setAttribute('disabled', 'disabled');

    if ( oCalendarioSelecionado.iCalendario != "" ) {

      oTurma.setEscola(iEscola);
      oTurma.setCalendario(oCalendarioSelecionado.iCalendario);
      oTurma.getTurmas();
    } 
  };

  /**
   * Função de callBack após seleção para turma
   * @return {function}
   */
  var fFunctionCallbackOnChangeTurma = function() {

    var oTurmaSelecionado = oTurma.getSelecionados();

    if (oTurmaSelecionado.codigo_turma == "") {
      //$('imprimir').setAttribute('disabled', 'disabled');
      oPeriodo.limpaElemento();
      oRegencia.clear();
      return;
    } 
    oPeriodo.getPeriodos(oTurmaSelecionado.codigo_turma, oTurmaSelecionado.codigo_etapa, 2);
    //oRegencia.getDisciplinas(oTurmaSelecionado.codigo_turma, oTurmaSelecionado.codigo_etapa, false);
  }

  /**
   * Função de callBack após o carregamento para turma
   * @return {function} 
   */
  var fFunctionCallBackLoadTurma = function() {

    var oTurmaSelecionado = oTurma.getSelecionados();
    if (oTurmaSelecionado.codigo_turma == "") {
      return;
    }    
    oPeriodo.getPeriodos(oTurmaSelecionado.codigo_turma, oTurmaSelecionado.codigo_etapa, 2);
    //oRegencia.getDisciplinas(oTurmaSelecionado.codigo_turma, oTurmaSelecionado.codigo_etapa, false);
  }

  /**
   * Função realizada ao alterar período
   * @return {function}
   */
  var fFunctionChangePeriodo = function()  {

    var oPeriodoSelecionado = oPeriodo.getSelecionado();

    //$('imprimir').setAttribute('disabled', 'disabled');
    
    if( oPeriodoSelecionado.iCodigo != "" ) {

      //$('imprimir').removeAttribute('disabled');
      return;
    }
  }

  /**
   * Função realizada após carregamento dos períodos
   * @return {function}
   */
  var fFunctionLoadPeriodo = function() {

    var oPeriodoSelecionado = oPeriodo.getSelecionado();

    if( oPeriodoSelecionado.iCodigo != "" ) {

      //$('imprimir').removeAttribute('disabled');
      return;
    }

    //$('imprimir').setAttribute('disabled', 'disabled');
  }

  /**
  * seta os callback do calendário
  */
  oCalendario.setOnChangeCallBack(fFunctionChangeCalendario);
  oCalendario.show($('listaCalendarios'));

  /**
  * Seta callback na turma
  */
  oTurma.setCallbackOnChange(fFunctionCallbackOnChangeTurma);
  oTurma.setCallBackLoad(fFunctionCallBackLoadTurma);
  oTurma.show($('listaTurmas'));

  /**
   * Seta callback no periodo
   */
  oPeriodo.setCallBackChange(fFunctionChangePeriodo);
  oPeriodo.setCallBackLoad(fFunctionLoadPeriodo);
  oPeriodo.show($('listaPeriodos'));  

 /**
 * Função para imprimir os dados do formulário
 * @return
 */
/*
  $('imprimir').observe("click", function () {

    var oCalendarioSelecionado  = oCalendario.getSelecionados();
    var oTurmaSelecionada       = oTurma.getSelecionados();
    var oPeriodoSelecionado     = oPeriodo.getSelecionado();
    var aRegencias              = [];

    oRegencia.getSelecionados().each( function(oRegenciaSelecionada) {

      aRegencias.push( oRegenciaSelecionada.iRegencia );
    });

    var sUrlRelatorio = 'edu2_diarioclasseconteudodesenvolvido002.php';
    sUrlRelatorio    += '?escola='      + iEscola;
    sUrlRelatorio    += '&calendario='  + oCalendarioSelecionado.iCalendario;
    sUrlRelatorio    += '&turma='       + oTurmaSelecionada.codigo_turma;
    sUrlRelatorio    += '&periodo='     + oPeriodoSelecionado.iCodigo;
    sUrlRelatorio    += '&disciplinas=' + aRegencias;
    sUrlRelatorio    += '&paginas='     + $F('numeroPaginas');
    sUrlRelatorio    += '&preenchimento=manual';
    sUrlRelatorio    += '&lRegistroOcorrencia=true';
        
    jan = window.open(sUrlRelatorio,'','width='+(screen.availWidth-5)+',height='+(screen.availHeight-40)+',scrollbars=1,location=0');
    jan.moveTo(0,0);
  });
  */

  var bvop = document.getElementById("cboPeriodoAvaliacao");
  
//******************************************************************************************************************
  bvop.addEventListener("change", function(){  
    document.getElementById("bsalvar").disabled = false;
    var xc = document.getElementById("cboCalendario").value;
    var xt = document.getElementById("cboTurma").value;
    var xp = document.getElementById("cboPeriodoAvaliacao").value;
	var di = document.getElementById("codigo").value;
    var xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function(){
      if((xhr.readyState === 4) && (xhr.status === 200)){
        document.getElementById("vazio").innerHTML = xhr.responseText;
      }
    }
		
  xhr.open("GET","edu_salvaocorrencia.php?xc="+xc+"&xt="+xt+"&xp="+xp+"&di="+di, true);
  //xhr.open("GET","edu_salvaocorrencia.php", true);
  xhr.send(null);      
  }, false);
//******************************************************************************************************************

function show_disciplina(){
    alert('estou aqui');	
	var Disciplina1 = document.getElementById('listaPeriodos').value;
}

function js_buscadisciplina(){
	var calendario = document.getElementById("cboCalendario").value;
	var turma      = document.getElementById("cboTurma").value;
	if(calendario==='')
	{
		alert('É necessário escolher um calendário!!');
		return;
	}	
    js_OpenJanelaIframe('top.corpo','db_iframe_disciplina','func_disciplinas.php?funcao_js=parent.js_mostra2|ed232_i_codigo|ed232_c_descr'+'&turma='+turma,'Pesquisa',true);
}

function js_mostra2(chave2,chave3){
  document.form1.codigo.value    = chave2;
  document.form1.descricao.value = chave3;
  db_iframe_disciplina.hide();
}

function seldisciplina(){
//    var turma      = document.getElementById("cboTurma").value;
//	const dados    = await fetch('func_buscardisciplina.php?turma='+turma);
//	const discip   = await dados.json();
//	console.log(discip);
}
</script>
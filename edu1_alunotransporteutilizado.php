<?php
/*
 *     E-cidade Software Publico para Gestao Municipal
 *  Copyright (C) 2009  DBSeller Servicos de Informatica
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

require_once(modification("libs/db_stdlibwebseller.php"));
require_once(modification("libs/db_stdlib.php"));
require_once(modification("libs/db_app.utils.php"));
require_once(modification("libs/db_conecta.php"));
require_once(modification("libs/db_sessoes.php"));
require_once(modification("libs/db_usuariosonline.php"));
require_once(modification("libs/db_utils.php"));
require_once(modification("dbforms/db_funcoes.php"));

$clrotulo = new rotulocampo;
$clrotulo->label("ed47_i_transpublico");
$clrotulo->label("ed47_c_transporte");
$opcoesSimNao = ['f' => 'NÃO','t' => 'SIM'];

$escolaAluno = null;
$where = " ed56_i_aluno = $iAluno";
$daoAlunoCurso = new cl_alunocurso;
$sqlAlunoCurso = $daoAlunoCurso->sql_query_file(null,"ed56_i_escola",null,$where);
$rsAlunoCurso = db_query($sqlAlunoCurso);
if(pg_num_rows($rsAlunoCurso) > 0){
  $escolaAluno = db_utils::fieldsMemory($rsAlunoCurso,0)->ed56_i_escola;
}

$desabilitaBotaoSalvar = "false";
if($escolaAluno != null && db_getsession("DB_coddepto") != $escolaAluno){
  $desabilitaBotaoSalvar = "true";
}

?>
<html>
  <head>
    <title>Microsist</title>
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
    <meta http-equiv="Expires" CONTENT="0">
    <?php
    db_app::load("scripts.js, 
                  prototype.js, 
                  strings.js, 
                  arrays.js,
                  dbcomboBox.widget.js");
    
    db_app::load("estilos.css");
    ?>
  </head>
  <body class="container">

        <div class="form-container">
            <fieldset>
                <legend>
                  <b>Transporte Escolar</b>
                </legend>
                <table>
                    <tr>
                        <td>
                            <?=$Led47_i_transpublico?>
                        </td>
                        <td>
                            <?php
                            $x = ["0"=>"Não Utiliza","1"=>"Utiliza"];
                            db_select('ed47_i_transpublico', $x, true, 1, "onchange=habilitaFormulario()");
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <?=$Led47_c_transporte?>
                        </td>
                        <td>
                            <?php
                            $x = [''=>'','1'=>'Estadual','2'=>'Municipal'];
                            db_select('ed47_c_transporte', $x, true, 1, "");
                            ?>
                        </td>
                    </tr>

                    <tr>
                      <td colspan="4">
                        <fieldset><legend>Dificuldades de acesso à residência</legend>                        
                        <table>
                          <tr>
                              
                              <td><b>Tem porteira?</b></td>
                              <td>
                                  <?php db_select('ed355_porteira', $opcoesSimNao, true, 1, ""); ?>
                              </td>
                          </tr>
                          <tr>   
                              <td><b>Tem mata-burro?</b></td>
                              <td>
                                  <?php db_select('ed355_mataburro', $opcoesSimNao, true, 1, ""); ?>
                              </td>
                          </tr>
                          <tr>
                              <td><b>Tem colchete?</b></td>
                              <td>
                                  <?php db_select('ed355_colchete', $opcoesSimNao, true, 1, ""); ?>
                              </td>
                          </tr>
                          <tr>
                              <td><b>Tem atoleiro?</b></td>
                              <td>
                                  <?php db_select('ed355_atoleiro', $opcoesSimNao, true, 1, ""); ?>
                              </td>
                          </tr>            
                          <tr>
                              <td><b>Tem ponte rústica?</b></td>
                              <td>
                                  <?php db_select('ed355_ponterustica', $opcoesSimNao, true, 1, ""); ?>
                              </td>
                          </tr>                         
                         </table> 
                        </fieldset> 
                      </td>                    
                    </tr>
                    
                    <tr>
                        <td><b>Meios de Transportes:</b></td>
                        <td id='ctnTransportes'></td>
                        <td>
                          <button type='button' id='btnMoveOneRightToLeft' style='border:1px solid #999999; width: 40px'>&gt;</button><br>
                          <button type='button' id='btnMoveOneLeftToRight' style='border:1px solid #999999;width: 40px'>&lt;</button><br>
                          <button type='button' id='btnMoveAllLeftToRight' style='border:1px solid #999999;width: 40px'>&lt;&lt;</button>
                        </td>
                        <td id='ctnTransportesSelecionados'></td>
                    </tr>
                </table>
            </fieldset>  
        </div>

        <div id='mensagem' style="font-size: 14px; display: none;" >
            <br>
            <b>Aluno(a) informou que não utiliza Transporte Escola Público, não sendo possível selecionar nenhum tipo de transporte.</b>
            <br>
        </div>
        <br>
        <input type="button" value='Salvar' id='btnSalvar' onclick="js_salvar()">

  </body>
</html>

<script>
var sUrlRPC                  = 'edu_dadosaluno.RPC.php';
var oGet                     = js_urlToObject(location.search);
var iTransportesSelecionados = 0;
let desabilitaBotaoSalvar = '<?php echo $desabilitaBotaoSalvar; ?>';
function js_init() {

    oCboTransporte  = new DBComboBox("cboTransporte", "oCboTransporte", null,"500px", 10);
    oCboTransporte.setMultiple(true);
    oCboTransporte.addEvent("onDblClick", "moveSelected(oCboTransporte, oCboTransporteSelecionados)");
    oCboTransporte.show($('ctnTransportes'));
    
    oCboTransporteSelecionados  = new DBComboBox("cboTransporteSelecionados", "oCboTransporteSelecionados",null,"500px", 10);
    oCboTransporteSelecionados.setMultiple(true);
    oCboTransporteSelecionados.addEvent("onDblClick", "moveSelected(oCboTransporteSelecionados, oCboTransporte)");
    oCboTransporteSelecionados.show($('ctnTransportesSelecionados'));

    if(desabilitaBotaoSalvar == 'false'){
      $('btnSalvar').disabled = false;
    }  else {
      $('btnSalvar').disabled = true;
    }         
    habilitaFormulario();
    
    js_pesquisar();
}


function habilitaFormulario() {
  if ($F('ed47_i_transpublico') == 0) {
    desabilitaMeioTransporte();  
  } else {
    habilitaMeioTransporte();
  }
}

function habilitaMeioTransporte(){
  oCboTransporte.setEnable();
  oCboTransporteSelecionados.setEnable();           
  $('mensagem').style.display = 'none';  
}

function desabilitaMeioTransporte(){
  oCboTransporte.setDisable();
  oCboTransporteSelecionados.setDisable();          
  $('mensagem').style.display = 'inline';  
}

$('btnMoveOneRightToLeft').observe("click", function() {
   moveSelected(oCboTransporte, oCboTransporteSelecionados);
});
 
 $('btnMoveOneLeftToRight').observe("click", function() {
   moveSelected(oCboTransporteSelecionados, oCboTransporte);
});
 
 $('btnMoveAllLeftToRight').observe("click", function() {
   moveAll(oCboTransporteSelecionados, oCboTransporte);
});
  
function moveSelected(oComboOrigin, oComboDestiny) {
  if (oComboDestiny.sName == 'cboTransporteSelecionados') {
    iTransportesSelecionados += oComboOrigin.getValue().length;
  } else {
    iTransportesSelecionados -= oComboOrigin.getValue().length;
  }
  
  if (js_verificaTransportesSelecionados(oComboOrigin)) {
    if (oComboOrigin.getValue() != null) {
      
      var aItens = oComboOrigin.getValue();
      aItens.each(function(oItem, iSeq) {      
        
        oItem = oComboOrigin.aItens[oItem];
        oComboDestiny.addItem(oItem.id, oItem.descricao);
        oComboOrigin.removeItem(oItem.id);
      });
    }
  }
}
  
 function moveAll(oComboOrigin, oComboDestiny) {
   iTransportesSelecionados = 0;
    oComboOrigin.aItens.each(function(oItem, iSeq) {
       oComboDestiny.addItem(oItem.id, oItem.descricao);
       oComboOrigin.removeItem(oItem.id);
     });
 }

function js_pesquisar() {
  var oParametro  = new Object();
  
  oParametro.exec         = 'getTransportesAluno';
  oParametro.iCodigoAluno = oGet.iAluno;
  js_divCarregando('Aguarde, carregando os tipos de transporte público', 'msgBox');
  var oAjax = new Ajax.Request (
                                sUrlRPC,
                                {
                                 method     : 'post',
                                 parameters : 'json='+js_objectToJson(oParametro),
                                 onComplete : js_retornaTransporte
                                }
                               );

}

function js_retornaTransporte (oResponse) {
  js_removeObj('msgBox');

  var oRetorno = JSON.parse(oResponse.responseText);
  oCboTransporte.clearItens();
  oCboTransporteSelecionados.clearItens();
  oRetorno.aTransportes.each(function(oTransporte, iSeq) {
     
     if (oTransporte.possui == 'f') {
      oCboTransporte.addItem(oTransporte.codigo, oTransporte.descricao.urlDecode());
     } else {
       
      oCboTransporteSelecionados.addItem(oTransporte.codigo, oTransporte.descricao.urlDecode());
      iTransportesSelecionados++;
     }
  });

  if('porteira' in oRetorno.aDificuldades &&
     'mataburro' in oRetorno.aDificuldades &&
     'colchete' in oRetorno.aDificuldades &&
     'atoleiro' in oRetorno.aDificuldades &&
     'ponterustica' in oRetorno.aDificuldades 
  ){
    $('ed355_porteira').value = oRetorno.aDificuldades.porteira;
    $('ed355_mataburro').value = oRetorno.aDificuldades.mataburro;
    $('ed355_colchete').value = oRetorno.aDificuldades.colchete;
    $('ed355_atoleiro').value = oRetorno.aDificuldades.atoleiro;
    $('ed355_ponterustica').value = oRetorno.aDificuldades.ponterustica;
  }

  $('ed47_i_transpublico').value = oRetorno.ed47_i_transpublico;
  $('ed47_c_transporte').value = oRetorno.ed47_c_transporte;
  habilitaFormulario();
}

js_salvar = function() {
  var oParametro = new Object();
  
  oParametro.exec         = 'inserirTransporteAluno';
  oParametro.aTransporte  = new Array();
  oParametro.iCodigoAluno = oGet.iAluno;
  oParametro.ed47_i_transpublico = $('ed47_i_transpublico').value;
  oParametro.ed47_c_transporte = $('ed47_c_transporte').value;  
  oCboTransporteSelecionados.aItens.each(function (oItem, iSeq) {
     
     if (oItem.descricao != "") {
       oParametro.aTransporte.push(oItem.id);
     }
  
  });

  var oDificuldades = new Object();
  oDificuldades.ed355_porteira = $('ed355_porteira').value;
  oDificuldades.ed355_mataburro = $('ed355_mataburro').value;
  oDificuldades.ed355_colchete = $('ed355_colchete').value;
  oDificuldades.ed355_atoleiro = $('ed355_atoleiro').value;
  oDificuldades.ed355_ponterustica = $('ed355_ponterustica').value;
  oParametro.aDificuldades = Object.toJSON(oDificuldades);

  js_divCarregando('Aguarde, salvando os dados', 'msgBox');
  var oAjax = new Ajax.Request (
                                sUrlRPC,
                                {
                                 method     : 'post',
                                 parameters : 'json='+Object.toJSON(oParametro),
                                 onComplete : js_retornaInclusao
                                }
                               );

}

function js_retornaInclusao (oResponse) {
  js_removeObj('msgBox') ;
  
  var oRetorno = JSON.parse(oResponse.responseText);
  if (oRetorno.status == 1) {
    alert('Transportes salvos com sucesso');
  } else {
    alert(oRetorno.message.urlDecode());
  }

}

function js_verificaTransportesSelecionados(oComboOrigin) {
  var lErro = false;

  if (oComboOrigin.getValue().length > 3) {
    lErro = true;
  } else if (iTransportesSelecionados > 3) {
    lErro = true;
  }
  
  if (lErro) {
    
    alert('É permitido selecionar no máximo 3 tipos de transportes.');
    iTransportesSelecionados -= oComboOrigin.getValue().length;
    return false;
  }
  
  return true;
}

js_init();

</script>
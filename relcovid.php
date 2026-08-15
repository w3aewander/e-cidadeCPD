<?php

require_once("libs/db_stdlib.php");
require_once("libs/db_conecta.php");
require_once("libs/db_sessoes.php");
require_once("libs/db_usuariosonline.php");
require_once("dbforms/db_funcoes.php");
require_once("dbforms/db_classesgenericas.php");

//$oRotuloSaltes = new rotulo('saltes');
//$oRotuloSaltes->label();

?>
<html>
  <head>
    <title>Microsist</title>
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
    <meta http-equiv="Expires" CONTENT="0">
    <script language="JavaScript" type="text/javascript" src="scripts/scripts.js"></script>
    <script language="JavaScript" type="text/javascript" src="scripts/prototype.js"></script>
    <script language="JavaScript" type="text/javascript" src="scripts/strings.js"></script>
    </script>  
    <link href="estilos.css" rel="stylesheet" type="text/css">
    <style>
      #ctnEmpenhosPagos select {
        width: 150px;
      }
    </style>
  </head>
  <body style="margin-top: 25px; background-color: #cccccc;">
  <div id="ctnEmpenhosPagos">
  <form name="form1" id="form1">
    <center>
      <fieldset style="width: 500px;">
        <legend><b>Empenhos</b></legend>
        <table>
          
            
          
            <td><b>Período:</b></td>
            <td>
              <?php 
                $aPeriodoDatas = explode('-', date('Y-m-d', db_getsession('DB_datausu')));
                list($iAnoInicial, $iMesInicial, $iDiaInicial) = $aPeriodoDatas;
                echo "<b>De: </b>";
                db_inputdata("dtDataInicial", $iDiaInicial, $iMesInicial, $iAnoInicial, true, 'text', 1);
                echo "<b> até </b>";
                db_inputdata("dtDataFinal", $iDiaInicial, $iMesInicial, $iAnoInicial, true, 'text', 1);
              ?>
            </td>
          </tr>
          <tr>
            <td><b>Ordem:</b></td>
            <td>
              <?php 
                $aOrdem = array("empenho" => "Empenho", "data" => "Data");
                 $aOrdem = array("empenho" => "Empenho");
                db_select("sTipoOrdem", $aOrdem, true, 1);
              ?>
            </td>
          </tr>
          <tr>
            <td><b>Quebra COVID-19:</b></td>
            <td>
              <?php 
                $aQuebraConta = array("f" => "Não", "t" => "Sim");
                db_select("lQuebraConta", $aQuebraConta, true, 1);
              ?>
            </td>
          </tr>
          <tr>
            <td><b>Lista Empenho:</b></td>
            <td>
              <?php 
                $aListaEmpenho = array(0 => "Todos", 1 => "Só COVID-19", 2 => "Sem COVID-19");
                db_select("iListaEmpenho", $aListaEmpenho, true, 1);
              ?>
            </td>
          </tr>
          
        </table>      
      </fieldset>
      <br />
      <input type="button" id="btnImprimir" value="Imprimir" />
    </center>
  </form>
  </div>
<?
  db_menu(db_getsession("DB_id_usuario"),db_getsession("DB_modulo"),db_getsession("DB_anousu"),db_getsession("DB_instit"));
?>
  </body>
</html>
<script>
  $('btnImprimir').observe('click', function() {

    var sDataInicialBanco = js_formatar($F('dtDataInicial'), 'd');
    var sDataFinalBanco   = js_formatar($F('dtDataFinal'), 'd');

    if (sDataInicialBanco > sDataFinalBanco) {
      alert("A data inicial é maior que a data final. Verifique!");
      return false;
    }

    var sQueryLocation  = "relcovid002.php?";    
    sQueryLocation     += "&dtDataInicial="+$F('dtDataInicial');
    sQueryLocation     += "&dtDataFinal="+$F('dtDataFinal');
    sQueryLocation     += "&sTipoOrdem="+$F('sTipoOrdem');
    sQueryLocation     += "&lQuebraConta="+$F('lQuebraConta');
    sQueryLocation     += "&iListaEmpenho="+$F('iListaEmpenho');
    

    var oJanela = window.open(sQueryLocation,'','width='+(screen.availWidth-5)+',height='+(screen.availHeight-40)+',scrollbars=1,location=0');
    oJanela.moveTo(0,0);   
  });


  
  
</script>
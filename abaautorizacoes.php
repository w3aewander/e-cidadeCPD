<?php 


require_once "libs/db_stdlib.php";
require_once "libs/db_utils.php";
require_once "libs/db_conecta.php";
require_once "libs/db_sessoes.php";
require_once "libs/db_usuariosonline.php";
require_once "classes/db_veicabast_classe.php";
$oGet = db_utils::postMemory($_GET);



function buscaDados($id){
  $sql = pg_query("SELECT * FROM veicretirada INNER JOIN autorizacaocirculacaoveiculo ON idautorizacao = ve13_sequencial WHERE ve60_veiculo = {$id}");
  $resultado = pg_fetch_all($sql);
  return $resultado;
}
$dados = buscaDados($veiculo);
//autorização 727
//Veículo 84
//Motorista 69
//Código Retirada 1940
?>
<html>
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
    <link href="estilos.css" rel="stylesheet" type="text/css">
    
    <?php
      db_app::load("scripts.js, prototype.js, widgets/windowAux.widget.js,strings.js");
      db_app::load("widgets/dbtextField.widget.js, dbViewCadEndereco.classe.js");
      db_app::load("dbmessageBoard.widget.js, dbautocomplete.widget.js,dbcomboBox.widget.js, datagrid.widget.js");
      db_app::load("estilos.css,grid.style.css");
    ?>
  </head>
  <body>
    <style>
    .botao{text-decoration: none; height: 18px; background-color: #d9d5d5; border-radius: 2px; font-size: 12px; border: 1px solid #999}
  </style>
    <center>
     <div style="width: 800px;">
          
            <div id='ctnDbGridDocumentos'></div>
          
        </div>
    </center>
  </body>
</html>
<script>

function emitir() {
    emitirDocumento(oCodigoAutorizacao.value);
  }

  /**
   * Faz a emissão do documento de autorização de circulação de veículo.
   * @param {int} iCodigoAutorizacao Código da autorização de circulação de veículos para emissão.
   */
  function emitirDocumento(iCodigoAutorizacao) {

    if (iCodigoAutorizacao == "") {

      alert("Autorização de Circulação de Veículo não informada.");
      return false;
    }

    var sUrl = "vei4_emiteautorizacaocirculacaoveiculo.php?iCodigoAutorizacao=" + iCodigoAutorizacao;
    jan = window.open(sUrl,'','width=' + (screen.availWidth - 5 ) + ',height=' + (screen.availHeight - 40) + ',scrollbars=1,location=0 ');
    jan.moveTo(0,0);
  }



oGridDocumento     = new DBGrid('gridDocumento');
oGridDocumento.nameInstance = "oGridDocumento";
oGridDocumento.setHeight(200);
oGridDocumento.setCellAlign(new Array("center","center"));
oGridDocumento.setHeader(new Array("Data Emissão","Autorização"));
oGridDocumento.show($('ctnDbGridDocumentos'));

function js_retornoGetDocumento() {
  oGridDocumento.clearAll(true);
  
    <?php foreach($dados as $l) : $dtemissao = implode("/", array_reverse(explode("-", $l["ve13_dataemissao"]))); ?>
    var aLinha = new Array();
    aLinha[0]  = "<?=$dtemissao?>";    
    aLinha[1]  = '<a href="#" onclick="emitirDocumento(<?=$l["idautorizacao"]?>)">Emitir</a>';

    
    
    oGridDocumento.addRow(aLinha);
  <?php endforeach ?>
  

  oGridDocumento.renderRows();

}

js_retornoGetDocumento();
</script>



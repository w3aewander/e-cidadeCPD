<?php 


require_once "libs/db_stdlib.php";
require_once "libs/db_utils.php";
require_once "libs/db_conecta.php";
require_once "libs/db_sessoes.php";
require_once "libs/db_usuariosonline.php";
require_once "classes/db_veicabast_classe.php";
$oGet = db_utils::postMemory($_GET);


function buscaDocumentosPorVeiculo($id){
  $sql = pg_query("SELECT * FROM vdocumentos WHERE idveiculo = {$id}");
  $resultado = pg_fetch_all($sql);
  return $resultado;
}
$dados = buscaDocumentosPorVeiculo($veiculo);

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
oGridDocumento     = new DBGrid('gridDocumento');
oGridDocumento.nameInstance = "oGridDocumento";
oGridDocumento.setHeight(200);
oGridDocumento.setCellAlign(new Array("center","left","center", "center", "center"));
oGridDocumento.setHeader(new Array("Código","Descrição","Download", "Situação", "Usuário"));
oGridDocumento.show($('ctnDbGridDocumentos'));

function js_retornoGetDocumento() {
  oGridDocumento.clearAll(true);
  
    <?php foreach($dados as $l) : ?>
    var aLinha = new Array();
    aLinha[0]  = "<?=$l['idveiculo']?>";
    aLinha[1]  = "<?=$l['descricao']?>";        
<?php if($l["excluido"] == 1) : ?>
    aLinha[2] = '';  
  <?php else: $ext = $l["nomearquivo"]; $ext = explode(".", $ext); $ext = $ext[1]; ?>
    aLinha[2]  = '<a download="documento.<?=$ext?>" href="tmp/<?=$l["nomearquivo"]?>">Download</a>';
<?php endif; ?>
    aLinha[3] = "<?=($l['excluido']) == 1 ? 'Excluído' : 'Incluído'  ?>";
    aLinha[4] = "<?=$l['usuario']?>";
    
    oGridDocumento.addRow(aLinha);
  <?php endforeach ?>
  

  oGridDocumento.renderRows();

}

js_retornoGetDocumento();
</script>
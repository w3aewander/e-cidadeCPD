<?php
  require_once("libs/db_stdlib.php");
  require_once("libs/db_utils.php");
  require_once("libs/db_app.utils.php");
  require_once("libs/db_conecta.php");
  require_once("libs/db_sessoes.php");
  require_once("dbforms/db_funcoes.php");
 

if($_FILES){
  
  $nome = "total".$_FILES["uploadedFile"]["name"];
  $upload = fopen($_FILES['uploadedFile']['tmp_name'], 'r');
  $dados = array();
  
  while (($linha = fgets($upload)) !== false){
    array_push($dados, $linha);
  }
  
  $total = 0;
  foreach ($dados as $linha){
    $explode = explode(";", $linha);
    $total += $explode[2];
  }
  array_push($dados, ";Total;{$total}");
  

  $arquivo = fopen("tmp/".$nome, "w");

foreach ($dados as $linhaA) {  
  fwrite($arquivo, $linhaA);
}
fclose($arquivo);


$file_url = 'tmp/'.$nome;
header('Content-Type: application/octet-stream');
header("Content-Transfer-Encoding: Binary"); 
header("Content-disposition: attachment; filename=\"" . basename($file_url) . "\""); 
readfile($file_url);



unlink($file_url);
exit();
}

db_app::load("scripts.js");
  db_app::load("prototype.js");
  db_app::load("datagrid.widget.js");
  db_app::load("strings.js");
  db_app::load("grid.style.css");
  db_app::load("estilos.css");
  db_app::load("classes/dbViewAvaliacoes.classe.js");
  db_app::load("widgets/windowAux.widget.js");
  db_app::load("widgets/dbmessageBoard.widget.js");
  db_app::load("dbcomboBox.widget.js");


?>

<html>
  <head>
    <title>Microsist</title>
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
    <meta http-equiv="Expires" CONTENT="0">
    <?php
      db_app::load("scripts.js, strings.js, prototype.js, estilos.css");
    ?>
  </head>
  <body style="background-color: #ccc; margin-top: 30px">
    <div id="div_container" style="width: 300px; margin: auto;">
      <form method="post" action="" enctype="multipart/form-data">
      <fieldset>
        <legend style="font-weight: bold;">Importar Arquivo</legend>
        <table>
          <tr>
            <td style="font-weight: bold;">
              Arquivo:
            </td>
            <td>
              <input type="file" name="uploadedFile" />
            </td>
          </tr>
        </table>
        
      </fieldset>
      <span><i>Essa função irá retornar um CSV com o valor totalizado das receitas.</i></span>
      <p align="center">        
        <input type="submit" name="gerar" value="Importar">
      </p>

      <?php if($tota) : ?>
        <h4>Total das receitas do arquivo: <?=$total;?></h4>
      <?php endif; ?>
      
    </form>
    </div>

  <?php db_menu(db_getsession("DB_id_usuario"),db_getsession("DB_modulo"),db_getsession("DB_anousu"),db_getsession("DB_instit"));?>

  </body>
</html>
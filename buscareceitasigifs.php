<?php

require_once 'libs/db_stdlib.php';
require_once 'libs/db_conecta.php';
require_once 'libs/db_sessoes.php';
require_once 'libs/db_usuariosonline.php';
require_once 'libs/JSON.php';
require_once 'libs/db_utils.php';
require_once 'dbforms/db_funcoes.php';




?>



<?php 
$tabtemp = "CREATE TEMPORARY TABLE temprecsigifs(id serial, cod varchar(50),label varchar(400));";
pg_query($tabtemp);

$oJson         = new services_json();
$sName         = $_POST["string"];
$oDomXml       = new DOMDocument();
$oDomXml->load('config/sigfis/receitasigfis.xml');


function buscaTabela2(){
  $sql = pg_query("SELECT codigoconta, descricaoconta FROM cadastroreceitastce ORDER BY codigoconta");
  $resultado = pg_fetch_all($sql);
  return $resultado;
}

$aContas = $oDomXml->getElementsByTagName("receita");

$aReceitaRetorno = array();
foreach ($aContas as $oConta) {
  
  $iCodigo     = $oConta->getAttribute("codigo");
  $sDescricao  = $oConta->getAttribute("descricao");
  
  $iTamanhoPesquisa = strlen($sName);
  if (strpos(strtolower($sDescricao), strtolower($sName)) !== false ||
     substr($iCodigo, 0, $iTamanhoPesquisa) == $sName) {
    
    $oReceitaRetorno         = new stdClass();
    $oReceitaRetorno->cod    = $iCodigo;
    $oReceitaRetorno->label  = "{$iCodigo} - {$sDescricao}";
    $aReceitaRetorno[]       = $oReceitaRetorno;   
  }
  
}

$novasContas = buscaTabela2();
foreach ($novasContas as $oConta) {  
  $iCodigo     = $oConta["codigoconta"];
  $sDescricao  = $oConta["descricaoconta"];
  
  $iTamanhoPesquisa = strlen($sName);
  if (strpos(strtolower($sDescricao), strtolower($sName)) !== false ||
     substr($iCodigo, 0, $iTamanhoPesquisa) == $sName) {
    
    $oReceitaRetorno         = new stdClass();
    $oReceitaRetorno->cod    = $iCodigo;
    $oReceitaRetorno->label  = "{$iCodigo} - {$sDescricao}";
    $aReceitaRetorno[]       = $oReceitaRetorno;   
  }
  
}


foreach ($aReceitaRetorno as $linha){
  pg_query("INSERT INTO temprecsigifs(cod, label) VALUES('".$linha->cod."', '".utf8_decode($linha->label)."') ");
}

//$x = pg_query("SELECT id, cod, label FROM temprecsigifs WHERE cod = '1112000000000'");
//$x2 = pg_fetch_all($x);
//var_dump($x2);
//testa($aReceitaRetorno);
// array(1) { [0]=> array(3) { ["id"]=> string(1) "4" ["cod"]=> string(13) "1112000000000" ["label"]=> string(43) "1112000000000 - Impostos sobre o Patrimônio" } } 

$sqltudo = pg_query("SELECT id, cod, label FROM temprecsigifs");
$resultado = pg_fetch_all($sqltudo);
//testa($resultado);

?>
<form name='form1' id='form1'>
<div style="display: table">
        <fieldset>
          <legend><b>Vincular Receitas Sigfis (NOVO)</b></legend>
          <table>
            
            <tr>
              <td>
                <b>Receita SigFis:</b>
              </td>
              <td>
                 <?
                  db_input('codigoreceitatce', 10, $Io57_fonte, true, "text", 3);
                  //db_input('descricaoreceitatce2',  40, $Io57_descr, true, "text", 1);                  
                ?>
                <input list="listarec" name="descricaoreceitatce" id="descricaoreceitatce" size="56" onchange="separa()">
                 <datalist id="listarec">
                    <?php foreach($resultado as $linha) : ?>
                      <option value="<?=$linha['label'];?>">
                    <?php endforeach; ?>
                 </datalist>
              </td>
            </tr> 

            <tr>
              <td>
                <b><a href="#" class="dbancora" style="text-decoration:underline;" onclick="js_pesquisa_receita(true);"><strong><?= utf8_decode("Código Fonte")?>:</strong></a></b>
              </td>
              <td>
                <input title="Código Fonte da receita do orcamento

Campo:o57_codfon                              " name="o57_codfon" type="text" id="o57_codfon" value="" size="10" maxlength="6" onblur="js_ValidaMaiusculo(this,'f',event);" oninput="js_ValidaCampos(this,1,'Código Fonte','f','f',event);" onkeydown="return js_controla_tecla_enter(this,event);" autocomplete="off" onchange="chamapr()">
                 <?
                  //db_input('o57_codfon', 10, $Io57_codfon, true, "text", 1);
                  //db_input('o57_descr',  40, $Io57_descr, true, "text", 3);
                  db_input('o57_descr',  56, $Io57_descr, true, "text", 3);
                ?>
              </td>
            </tr> 

            
            
          </table>
        </fieldset>
      </div>    
<input type="button" value='Salvar' id='btnSalvarVinculo' onclick="salvarvinculo()">
<input type="button" value='<?=utf8_decode("Visualizar Vínculos");?>' id='btnVisualizar' onclick="js_visualizarVinculos()">
</form>



<?php /* ?>
<input title="" name="descricaoreceitatce" type="text" id="descricaoreceitatce" value="" size="40" maxlength="" onblur="js_ValidaMaiusculo(this,'',event);" oninput="js_ValidaCampos(this,0,'','','',event);" onkeydown="return js_controla_tecla_enter(this,event);" autocomplete="off">
<?php */ ?>


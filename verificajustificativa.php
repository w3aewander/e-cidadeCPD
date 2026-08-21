<?php

require_once(modification("libs/db_stdlib.php"));
require_once(modification("libs/db_utils.php"));
require_once(modification("libs/db_conecta.php"));
require_once(modification("libs/db_sessoes.php"));
require_once(modification("libs/db_usuariosonline.php"));
require_once(modification("dbforms/db_funcoes.php"));
require_once(modification("libs/db_libdicionario.php"));
require_once(modification("libs/db_app.utils.php"));
require_once(modification("classes/db_placaixa_classe.php"));
require_once(modification("classes/db_placaixarec_classe.php"));
require_once(modification("dbforms/db_classesgenericas.php"));



function testa($var){
  echo "<pre>";
  print_r($var);
  echo "</pre>";
}

function verificaJustificativa($ordem){
  $sql = pg_query("SELECT * FROM pagordem WHERE e50_codord = {$ordem}");
  $resultado = pg_fetch_all($sql);
  return $resultado[0];
}

function buscaSeqPorOp($op){
  $sql = pg_query("SELECT e50_numemp FROM pagordem WHERE e50_codord = {$op}");
  $resultado = pg_fetch_all($sql);
  return $resultado[0];
}


$seqempenho = $_GET["sequencial"];
$ordem = $_GET["op"];

$conferencia = buscaSeqPorOp($ordem);
$rs = verificaJustificativa($ordem);


if($conferencia["e50_numemp"] == $seqempenho){
  if(!empty($rs["e50_justificativaquebraordem"])){
    $datapb = implode("/", array_reverse(explode("-", $rs["e50_dataquebraordem"])));
    ?>
    <span><h3>Justificativa já existente. Alterar?
      <input type="radio" id="rn" name="chama" onclick="fecha();" checked>Não
      <input type="radio" id="rs" name="chama" onclick="libera();">Sim
      <input type="radio" id="rs" name="chama" onclick="libera2();">Retirar e Salvar Dados da Quebra
      
      </h3>
    </span>
    

    <td style="width: 120px;display: inline-block;"><b>Data da Publicação:</b></td>
    <td style="display: inline-block;margin-right: 70px"><input style="background-color: #DEB887" type="text" name="datapublicacao2" id="datapublicacao" maxlength="10" value="<?=$datapb?>" onkeypress="return arrumadata(this)" readonly></td>


    <td style="width: 80px;display: inline-block;"><b>Nº da Edição:</b></td>
    <td style="display: inline-block;"><input style="background-color: #DEB887" type="text" id="numedi" name="numedi2" value="<?=$rs['e50_numedi'];?>" onKeyPress="return js_mascara(event);" onblur="mpreencherjustificativa();" readonly></td>
    <br>
    <tr>
    <td style="width: 30px;display: inline-block;"><b>Link:</b></td>
    <td style="display: inline-block;"><input style="background-color: #DEB887" type="text" id="link" name="link2" size="80" value="<?=$rs["e50_justificativaquebraordem"]?>" readonly></td>
    </tr>
    <input type="hidden" name="existente" value="sim">  
    <br>
  <?php } elseif(!empty($rs["e50_codord"])) { ?>
      <td style="width: 120px;display: inline-block;"><b>Data da Publicação:</b></td>
              <td style="display: inline-block;margin-right: 70px">
                <!--<input type="text" id="datap" name="datap" required >-->
                <?php db_inputdata("datapublicacao", null, null, null, true, "text", 1); ?>
              </td>

              <td style="width: 80px;display: inline-block;"><b>Nº da Edição:</b></td>
              <td style="display: inline-block;"><input type="text" name="numedi" required onKeyPress="return js_mascara(event);"></td>
            <br>
          
            <tr>
              <td style="width: 30px;display: inline-block;"><b>Link:</b></td>
              <td style="display: inline-block;"><input type="text" name="link" size="80" readonly></td>
            </tr>
  <?php } else { ?>
    <h4>Não há registros.</h4>
  <?php } 
  
} else { ?>  
  <h4>O Número da OP digitada não corresponde ao empenho escolhido!</h4>  
<?php }
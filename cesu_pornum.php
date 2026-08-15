<?
//error_reporting(E_ALL); 
//ini_set('display_errors', '1');

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




$numemp = $_GET["sequencial"];
$tamanho = explode("/", $numemp);
if(count($tamanho) == 1){
  $numemp = $numemp ."/". db_getsession("DB_anousu");
}


function busca($sequencial){
  $sql = pg_query("SELECT id, e60_numemp, noempenho, nocertificado, e50_codord, CASE WHEN suspensao = 1 THEN 'Sim' ELSE 'Não' END as suspensao, e60_instit, justificativasuspensao, justificativaretiradasuspensao FROM certificacaoconformidade WHERE noempenho = '{$sequencial}' AND e50_codord is not null ORDER BY e60_instit, id");
  $resultado = pg_fetch_all($sql);
  return $resultado;
}



$dados = busca($numemp);

?>

<?php if($dados) : ?>
  <style>
#k81_origem{width:95px}.tamanho-primeira-col{width:150px}.input-menor{width:100px}.input-maior{width:400px}#k81_codigo{width:95px}#k81_codigodescr{width:77%}#k81_obs{width:100%;height:50px}div.gridcontainer{border:2px inset #fff;background-color:#eee;width:100%}div.header-container div.grid-resize{float:right;z-index:998;cursor:pointer;border:1px outset #fff}div.header-container table.table-header{background-color:#eee;font-weight:700;text-align:center;width:98%;border-collapse:collapse}div.header-container table.table-header tr{border-bottom:3px outset #fff;height:20px}div.header-container table.table-header tr td{padding:0;margin:0;white-space:nowrap;overflow:hidden;text-align:center!important;padding:1;padding-left:3;position:relative;background-clip:padding-box}div.body-container{width:100%;height:100px;overflow-y:scroll;background-color:#fff}div.body-container table.table-body{background-color:#fff;width:100%;border-collapse:collapse;overflow:auto}div.footer-container{width:100%}div.body-container table.table-body tr{border-bottom:1px outset #d3d3d3;padding:0;margin:0;height:1em}div.body-container table.table-body tr td{border-right:1px outset #d3d3d3;padding:0;margin:0;white-space:nowrap;overflow:hidden;padding:1;padding-left:3}input{font-family:Arial,Helvetica,sans-serif,verdana;font-size:12px;height:18px;border:1px solid #999}.cabecalho{font-weight:700;background-color:#eeeff2}  
</style>

<div id="body-container-gridmsc" class="body-container" style="height:auto;">
<form method="post" action="">
<table class="table-body" id="gridmscbody">


<tr>
  <td class="linhagrid cell cabecalho" title="" style="width: 3%; text-align: center;" nowrap="">
   
  </td>
<td class="linhagrid cell cabecalho" title="" style="width: 5%; text-align: center;" nowrap="">
  Sequencial do Empenho
  </td>

  <td class="linhagrid cell cabecalho" title="" style="width: 5%; text-align: center;" nowrap="">
  Nº do Empenho
  </td>

  <td class="linhagrid cell cabecalho" title="" style="width: 5%; text-align: center;" nowrap="">
  Ordem de Pagamento
  </td>

  <td class="linhagrid cell cabecalho" title="" style="width: 5%; text-align: center;" nowrap="">
  Nº Certificado
  </td>

  <td class="linhagrid cell cabecalho" title="" style="width: 5%; text-align: center;" nowrap="">
  Instituição
  </td>

  <td class="linhagrid cell cabecalho" title="" style="width: 5%; text-align: center;" nowrap="">
  Suspensão
  </td>
  
  
</tr>
  <?php foreach($dados as $linha) : ?>
    <tr <?=($linha["cseq2"]) != 0 ? "style='background-color:#DEB887' " : ""?>>
    <td class="linhagrid cell" title="" style="width: 3%; text-align: left;" nowrap="">
      <?php if($linha["cseq2"] == 0) : ?>
        <input type="checkbox" name="id" value="<?=$linha["id"];?>">
        <?php endif; ?>
    </td>

    <td class="linhagrid cell" title="" style="width: 5%; text-align: left;" nowrap="">
      <?=$linha["e60_numemp"];?>
    </td>

    <td class="linhagrid cell" title="" style="width: 5%; text-align: left;" nowrap="">
      <?=$linha["noempenho"];?>
    </td>

    <td class="linhagrid cell" title="" style="width: 5%; text-align: left;" nowrap="">
      <?=$linha["e50_codord"];?>
    </td>

    <td class="linhagrid cell" title="" style="width: 5%; text-align: left;" nowrap="">
      <?=$linha["nocertificado"];?>
    </td>

    <td class="linhagrid cell" title="" style="width: 5%; text-align: left;" nowrap="">
      <?=$linha["e60_instit"];?>
    </td>

    <td class="linhagrid cell" title="" style="width: 5%; text-align: left;" nowrap="">
      <?=$linha["suspensao"];?>
    </td>    
  </tr>
  <?php endforeach; ?>
</table>

 
<div style="text-align: left">

<p> 
  <label><b>Justificativa da Suspensão:</b></label><br>  
  <center>
  <textarea id="justsus" name="justsus" maxlength="200" rows="4" cols="80" required><?=($dados[0]['justificativasuspensao']) ? utf8_decode($dados[0]['justificativasuspensao']) : "" ?></textarea>
  </center>
</p>

<p> 
  <label><b>Justificativa da Retirada de Suspensão:</b></label><br> 
  <center>
  <textarea id="justsusret" name="justsusret" maxlength="200" rows="4" cols="80" required><?=($dados[0]['justificativaretiradasuspensao']) ? utf8_decode($dados[0]['justificativaretiradasuspensao']) : ""?></textarea>
  </center>
</p>

</div>

<input type="button" value="Gera/Retira Suspensão" id="botaozao" onclick="imprime()">

</form>

</div>

<?php else : ?>
  <h4>Não há OPs para o empenho buscado</h4>
<?php endif; ?>


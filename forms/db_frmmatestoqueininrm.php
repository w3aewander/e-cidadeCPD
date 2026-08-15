<? 

//MODULO: material
require_once("classes/db_matmater_classe.php");
require_once("classes/db_matmaterunisai_classe.php");
require_once("classes/db_matestoqueitemnotafiscalmanual_classe.php");

$clmatmater = new cl_matmater;
$clmatmaterunisai = new cl_matmaterunisai;
$clmatestoqueini->rotulo->label();
$oDaoMatEstoqueItemNotaFiscal = new cl_matestoqueitemnotafiscalmanual();
$oDaoMatEstoqueItemNotaFiscal->rotulo->label();
$clrotulo = new rotulocampo;
$clrotulo->label("m60_codmater");
$clrotulo->label("m60_descr");
$clrotulo->label("m61_descr");
$clrotulo->label("m70_codigo");
$clrotulo->label("m71_codlanc");
$clrotulo->label("m71_quant");
$clrotulo->label("m71_valor");
$clrotulo->label("m77_lote");
$clrotulo->label("m77_dtvalidade");
$clrotulo->label("m78_matfabricante");
$clrotulo->label("m76_nome");
$clrotulo->label("coddepto");
$clrotulo->label("descrdepto");
$clrotulo->label("m66_codcon");

function buscaNomeFabricante($seq){
  $sql = pg_query("SELECT matfabricante.m76_sequencial,matfabricante.m76_nome,matfabricante.m76_numcgm from matfabricante left join cgm on cgm.z01_numcgm = matfabricante.m76_numcgm WHERE m76_sequencial = {$seq}");
  $resultado = pg_fetch_all($sql);
  return $resultado[0]["m76_nome"];
}

/*
$fabinicial = 1;
$nomefabricante = buscaNomeFabricante($fabinicial);

$m78_matfabricante = $fabinicial;
$m76_nome = $nomefabricante;
*/


$tranca = 1;
$m60_controlavalidade = 3;

if($db_opcao==2 || $db_opcao==22 || $db_opcao==3 || $db_opcao==33){
  $tranca = 3;
}

if (isset($m60_codmater) && trim($m60_codmater) != "" && ( USE_PCASP && db_getsession("DB_anousu") > 2012) ) {

  $oDaoMaterialEstoqueGrupo = db_utils::getDao('matmatermaterialestoquegrupo');
  $sWhere                   = "    m68_matmater = {$m60_codmater} ";
  $sWhere                  .= "and m60_ativo    = true ";
  $sSqlValidaContaContabil  = $oDaoMaterialEstoqueGrupo->sql_query_grupo_conta(null, "m66_codcon", null, $sWhere);
  $rsValidaContaContabil    = $oDaoMaterialEstoqueGrupo->sql_record($sSqlValidaContaContabil);

  if ($oDaoMaterialEstoqueGrupo->numrows == 0) {

    $sMsgErro  = 'Material '.$m60_codmater.' sem vínculo com grupo/subgrupo. \n';
    $sMsgErro .= 'Para vincular acesse o menu: Cadastro > Cadastro de Material > Alteracao';
    db_msgbox($sMsgErro);

    $iRedirecionaMenu = 1;
    switch ($db_opcao) {

      case 2:
      case 22:
        $iRedirecionaMenu = 2;
      case 3:
      case 33:
        $iRedirecionaMenu = 3;
    }
    db_redireciona("mat1_matestoqueini00{$iRedirecionaMenu}.php");
    return true;
  }

  $m66_codcon = db_utils::fieldsMemory($rsValidaContaContabil, 0)->m66_codcon;
}


?>
<script language="JavaScript" type="text/javascript" src="scripts/scripts.js"></script>
<script language="JavaScript" type="text/javascript" src="scripts/prototype.js"></script>
<form name="form1" method="post" action="" id="formulario">
<center>
<table border="0">
  <tr>
    <td>
    <fieldset><legend><b>Entrada Manual</b></legend>
    <table border='0'>

      <tr> 
        <td  align="left" nowrap title="<?=$Te60_codemp?>">
          <? db_ancora("Nº Empenho","js_pesquisae60_codemp(true);",1);  ?>
        </td>
        <td  nowrap="nowrap" title='<?=$Te60_codemp?>' > 
          <input name="e60_codemp" size="10" type='text' onKeyPress="return js_mascara(event);" required readonly style="background-color:#DEB887">
        </td>
      </tr> 



      <tr>
        <td nowrap title="<?=@$Tcoddepto?>">
          <?
            db_ancora(@$Lcoddepto, "js_pesquisacoddepto(true);", $db_opcao);
          ?>
        </td>
        <td colspan="3">
          <?
            db_input('coddepto', 10, $Icoddepto,true,'text',$tranca," onchange='js_pesquisacoddepto(false);'");
            db_input('descrdepto', 40, $Idescrdepto,true,'text',3,'');
            db_input('m66_codcon', 10, $Im66_codcon,true,'hidden',3,'');
          ?>
        </td>
      </tr>
      <tr>
        <td nowrap title="<?=@$Tm60_codmater?>">
          <?
            db_ancora(@$Lm60_codmater,"js_pesquisam60_codmater(true);",$tranca);
          ?>
        </td>
        <td colspan="3">
          <?
            //db_input('m60_codmater',10,$Im60_codmater,true,'text',$tranca," onchange='js_pesquisam60_codmater(false);'");
          db_input('m60_codmater',10,$Im60_codmater,true,'text',3," onchange='js_pesquisam60_codmater(false);'");
            db_input('m60_descr',40,$Im60_descr,true,'text',3,'');
          ?>
        </td>
      </tr>

      <tr>
        <td nowrap title="Unid. Entrada:"><strong>Unid. Entrada:</strong></td>
        <td>
          <?
            db_input('m61_descr',20,$Im61_descr,true,'text',3)
          ?>
        </td>
        <td nowrap title="Unid. Saída" align="right"><strong>Unid. Saída:</strong></td>
        <td align="right">
          <?
            db_input('m61_descrsai',20,$Im61_descr,true,'text',3)
          ?>
        </td>
      </tr>
        
        <? /*
          if (isset($m60_codmater) && trim($m60_codmater) != "" && $db_opcao != 3 && $db_opcao != 33) {
            $result = $clmatmater->sql_record($clmatmater->sql_query($m60_codmater,"m61_descr,m60_controlavalidade"));
            if ($clmatmater->numrows>0){
              db_fieldsmemory($result,0);
            }
            $result_unisai = $clmatmaterunisai->sql_record($clmatmaterunisai->sql_query($m60_codmater,null,"matunid.m61_descr as m61_descrsai"));
            if ($clmatmaterunisai->numrows>0){
              db_fieldsmemory($result_unisai,0);
            }
        ?>
      <tr>
        <td nowrap title="Unid. Entrada:"><strong>Unid. Entrada:</strong></td>
        <td>
          <?
            db_input('m61_descr',20,$Im61_descr,true,'text',3)
          ?>
        </td>
        <td nowrap title="Unid. Saída" align="right"><strong>Unid. Saída:</strong></td>
        <td align="right">
          <?
            db_input('m61_descrsai',20,$Im61_descr,true,'text',3)
          ?>
        </td>
      </tr>
      <?
          } */



      $colspan = 3;
      $onchange= "";
      if(isset($m80_codigo) && trim($m80_codigo)!="" && $db_opcao==2){
        $colspan = 1;
        $onchange= "js_verificaquant(this.value);";
      }
      ?>
<tr>
        <td nowrap title="<?=@$Tm71_quant?>">
<?=@$Lm71_quant?>
</td>
        <td nowrap colspan="<?=$colspan?>">
<?
db_input('m71_quant',10,$Im71_quant,true,'text',$tranca,"onchange='js_calculavalortotal(this.value,\"quant\");$onchange'")
?>
</td>
<?
if(isset($m80_codigo) && trim($m80_codigo)!="" && $db_opcao==2){
  echo "
  <td nowrap title='Quantidade já solicitada' align='right'>
  <strong>Qtd. já solicitada:</strong>
  </td>
  <td align='right'>";
  // if(isset($m71_quantatend) && isset($m70_quant)){
    if(isset($m71_quantatend)){
      // $quantrest = ($m71_quant-$m71_quantatend)." / ".$m70_quant;
      // $quantrest = ($m71_quant-$m71_quantatend);
      $quantrest = ($m71_quantatend);
    }
    db_input('quantrest',10,$Im71_quant,true,'text',3);
    echo "
    </td>
    ";
  }
  ?>
  </tr>
  <?
  if(isset($m60_codmater) && trim($m60_codmater)!="" && $db_opcao!=3 && $db_opcao!=33){
    $result_transmater = $cltransmater->sql_record($cltransmater->sql_query_file(null,"distinct m63_codpcmater",null,"m63_codmatmater=$m60_codmater"));
    if($cltransmater->numrows>0){
      db_fieldsmemory($result_transmater,0);
    }
    if(isset($m63_codpcmater) && trim($m63_codpcmater) == "") {
      $mostrar = 'ok';
      echo '
      <tr>
      <td nowrap title="Média de valor deste material no empenho">
      <strong>Média de valor no empenho:</strong>
      </td>
      <td colspan="3">
      ';
      $media = 3;
      db_input('media',10,$Im71_quant,true,'text',$tranca);
      echo '
      <input name="calcular" type="button" id="calcula" value="Calcular média" onclick="js_calcularmedia();" onchange="js_calcularmedia();">
      </td>
      </tr>
      ';

      $result_valoritens = $clempempitem->sql_record($clempempitem->sql_query_file(null,null,"e62_vltot/e62_quant as valorunit","e62_numemp desc","e62_item=$m63_codpcmater"));
    }else{
      //$m71_valorunit = "";
      //$m71_valor = "";
    }
  }
  ?>
  <tr>
        <td nowrap title="Valor unitário do item"><strong>Valor unitário do item:</strong></td>
        <td>
  <?
  db_input('m71_valorunit',10,$Im71_valor,true,'text',$tranca,"onchange='js_calculavalortotal(this.value,\"unit\");'")
  ?>
  </td>
        <td nowrap title="Valor total" align="right"><strong>Valor total:</strong></td>
        <td align="left">
  <?
  db_input('m71_valor',10,$Im71_valor,true,'text',$tranca,"onchange='js_calculavalortotal(this.value,\"total\");'")
  ?>
  </td>
      </tr>
      <tr>
        <td><b>Lote:</b></td>
        <td>
      <? db_input('m77_lote',10,$Im77_lote,true,'text',$tranca);?>
    </td>
        <td align="right"><b>Validade:</b></td>
        <td>
      <?

      if (!isset($m77_dtvalidade)) {
        $m77_dtvalidade_dia = "";
        $m77_dtvalidade_mes = "";
        $m77_dtvalidade_ano = "";
      }
      db_inputdata('m77_dtvalidade',$m77_dtvalidade_dia,$m77_dtvalidade_mes,$m77_dtvalidade_ano,true,'text',$tranca);?>
       </td>
      </tr>
      <tr>
        <td nowrap title="<?=@$Tm78_matfabricante?>">
          <b>Fabricante:</b>
        <?
        //db_ancora(@$Lm78_matfabricante,"js_pesquisam78_matfabricante(true);",$tranca);
        ?>
       </td>
       <td colspan="3">
       <?
       //db_input('m78_matfabricante',10,$Im78_matfabricante,true,'text',$tranca," onchange='js_pesquisam78_matfabricante(false);'");
       db_input('m78_matfabricante',10,$Im78_matfabricante,true,'text',3,'');
       db_input('m76_nome',40,$Im76_nome,true,'text',3,'')
       ?>
    </td>
  <tr>
    <td nowrap title="<?=@$Tm79_notafiscal?>"><b><?=@$Lm79_notafiscal?></b></td>
    <td>
      <?php
        db_input("m79_sequencial", 10, null, true, 'hidden', 3);
        db_input("m79_notafiscal", 10, $Im79_notafiscal, true, 'text', $db_opcao, '');
      ?>
    </td>
    <td nowrap title="<?=@$Tm79_data?>" align="right"><b><?=@$Lm79_data?></b></td>
    <td>
      <?php
        if (!isset($m79_data)) {
          $m79_data_dia = "";
          $m79_data_mes = "";
          $m79_data_ano = "";
        }
        db_inputdata("m79_data", $m79_data_dia, $m79_data_mes, $m79_data_ano, true, 'text', $db_opcao);
      ?>
    </td>
  </tr>



<?php  
  function buscaDadosSerie($numnota){
    $sql = pg_query("SELECT * FROM notafiscalcoc WHERE nota = '{$numnota}'");  
    $resultado = pg_fetch_all($sql);
    return $resultado;
  }
  $dados = buscaDadosSerie($m79_notafiscal);
  
  if($dados){
    $numserie = $dados[0]["serienf"];
    $numsubserie = $dados[0]["subserienf"];
    $numprocessolicit = $dados[0]["numprocessolicit"];
  } else {
    $numserie = "";
    $numsubserie = "";
    $numprocessolicit = "";
  }

?>





  <tr>
    <td nowrap title="Série NF"><b>Série da NF:</b></td>
    <td>
      <input type="text" maxlength="3" name="e69_serienota" id="e69_serienota" size="10" value="<?=$numserie?>">
    </td>
    <td nowrap title="SubSérie NF" align="right"><b>SubSérie da NF:</b></td>
    <td>
      <input type="text" maxlength="3" name="e69_subserienota" id="e69_subserienota" size="10" value="<?=$numsubserie;?>">
    </td>
  </tr>

  <tr>
    <td nowrap title="Proc. Licit." align="right"><b>Nº do Processo Licitatório:</b></td>
    <td>
      <input type="text" name="numprocessolicit" id="numprocessolicit" value="<?=$numprocessolicit;?>">
    </td>    
  </tr>

  <tr>
    <td><b>Ordem de Compra:</b></td>
    <td>
      <input type="text" name="m51_codordem" id="m51_codordem" size="10" oninput="js_ValidaCampos(this,1,'Ordem de Compra','f','t',event);">
    </td>
    <td><b>Data de Recebimento:</b></td>
        <td>
          <?
          db_inputdata('e69_dtrecebe', '', '', '', true, 'text', 1, "");
          ?>
        </td>
  </tr>

 


  </tr>


  </tr>
      <tr>
        <td nowrap title="<?=@$Tm80_obs?>" colspan="4">
          <fieldset>
            <legend><b><?=@$Lm80_obs?></b></legend>
            <?php
              if($db_opcao==3 || $db_opcao==33){
                $m80_obs = "";
              }
              //db_textarea('m80_obs',4,70,$Im80_obs,true,'text',1,"");
            ?>
            <input type="text" name="m80_obs" id="m80_obs" maxlength="300" size="75">
          </fieldset>
        </td>
      </tr>
    </table>
    </fieldset>
    </td>
  </tr>
</table>
</center>

<input type="hidden" name="seqempenho">
<input type="hidden" name="valorempenho" id="valorempenho">



<button type="button" id="incluiritem" style="height: 18px !important;background-color: #d9d5d5;border-radius: 2px;font-size: 12px; border: 1px solid #999999">Incluir Item</button>
<br>


  <table border="1" id="tabelao">
    <tr>
      <th>Nota Fiscal</th>
      <th>Data Nota</th>
      <th>Valor Unitário</th>
      <th>Valor Total</th>
      <th>Quantidade</th>
      <th>Nº Empenho</th>
    </tr>
    <tr>
      <td colspan="3" style="text-align: center">TOTAL DOS ITENS</td>
      <td colspan="3" id="vtdi"></td>
    </tr>



    
    
  </table>
  <br>

  

<input name="<?=($db_opcao==1?"incluir":($db_opcao==2||$db_opcao==22?"alterar":"excluir"))?>" type="submit"
  id="db_opcao" value="<?=($db_opcao==1?"Gerar NRM":($db_opcao==2||$db_opcao==22?"Alterar":"Excluir"))?>"
  <?=($db_botao==false?"disabled":"")?> onclick="return js_controlavalidadade()" disabled> 

  <h5>É necessário incluir um item para gerar a NRM.</h5>

<?php if($db_opcao != 1) : ?>
  <input name="pesquisar" type="button" id="pesquisar" value="Pesquisar" onclick="js_pesquisainicio();">
<?php endif; ?>

  <?
  db_input('m60_controlavalidade',10,$Im80_codigo,true,'hidden',3);
  if(isset($m80_codigo) && trim($m80_codigo)!=""){
    db_input('m80_codigo',10,$Im80_codigo,true,'hidden',3);
    db_input('m70_codigo',10,$Im70_codigo,true,'hidden',3);
    db_input('m71_codlanc',10,$Im71_codlanc,true,'hidden',3);
  }
  $m80_codtipo = "1";
  $naoinill = "1,14" ;
  if(isset($entrada) && $entrada == true){
    $m80_codtipo = "3";
    $naoinill = "3,15" ;
  }
  db_input('m80_codtipo',10,$Im80_codtipo,true,'hidden',3);
  ?>
  </form>

  

<script>
  function js_calculavalortotal(valor,opcao){
    if(document.form1.m71_quant.value!=""){
      if(opcao!="quant"){
        pos = valor.indexOf('.');
        if(pos!=-1){
          tam = valor.length;
          qts = valor.slice((pos+1),tam);
          dec = qts.length;
          if(dec==1){
            dec   = 2;
          }
        }else{
          dec = 2;
        }
      }
      if(opcao=="unit"){
        valor = new Number(valor);
        quant = new Number(document.form1.m71_quant.value);
        VALOR = valor*quant;
        document.form1.m71_valorunit.value = valor.toFixed(dec);
        document.form1.m71_valor.value     = VALOR.toFixed(2);
      }else if(opcao=="total"){
        valor = new Number(valor);
        quant = new Number(document.form1.m71_quant.value);
        VALOR = valor/quant;
        document.form1.m71_valorunit.value = VALOR.toFixed(dec);
        document.form1.m71_valor.value     = valor.toFixed(2);
      }else if(opcao=="quant"){
        if(document.form1.m71_valorunit.value!=""){
          val = document.form1.m71_valorunit.value;
          pos = val.indexOf('.');
          if(pos!=-1){
            tam = val.length;
            qts = val.slice((pos+1),tam);
            dec = qts.length;
            if(dec==1){
              dec   = 2;
            }
          }else{
            dec = 2;
          }
          quant = new Number(valor);
          valor = new Number(document.form1.m71_valorunit.value);
          VALOR = valor*quant;
          document.form1.m71_valor.value = VALOR.toFixed(2);
        }
      }
    }
  }
  function js_pesquisam60_codmater(mostra) {

    if(mostra==true){
      js_OpenJanelaIframe('top.corpo','db_iframe_matmater','func_matmaternrm.php?funcao_js=parent.js_mostramatmater1|m60_codmater|m60_descr|m61_descr','Pesquisa',true);
    }else{
      if(document.form1.m60_codmater.value != ''){
        js_OpenJanelaIframe('top.corpo','db_iframe_matmater','func_matmaternrm.php?pesquisa_chave='+document.form1.m60_codmater.value+'&funcao_js=parent.js_mostramatmater','Pesquisa',false);
      }else{
        document.form1.m60_descr.value = '';
        document.form1.submit();
      }
    }
  }
  function js_mostramatmater(chave,erro) {
    document.form1.m60_descr.value = chave;
    if(erro==true){
      document.form1.m60_codmater.focus();
      document.form1.m60_codmater.value = '';
    }else{
      document.form1.submit();
    }
  }
  function js_mostramatmater1(chave1,chave2,chave3) {
    document.form1.m60_codmater.value = chave1;
    document.form1.m60_descr.value = chave2;
    document.form1.m61_descr.value = chave3;
    document.form1.m61_descrsai.value = chave3;    
    db_iframe_matmater.hide();
    //document.form1.submit();
  }
  function js_pesquisacoddepto(mostra){
    if(mostra==true){
      js_OpenJanelaIframe('top.corpo','db_iframe_depart','func_db_depart_material.php?funcao_js=parent.js_mostradepart1|coddepto|descrdepto','Pesquisa',true);
    }else{
      if(document.form1.coddepto.value != ''){
        js_OpenJanelaIframe('top.corpo','db_iframe_depart','func_db_depart_material.php?pesquisa_chave='+document.form1.coddepto.value+'&funcao_js=parent.js_mostradepart','Pesquisa',false);
      }else{
        document.form1.descrdepto.value = '';
      }
    }
  }
  function js_mostradepart(chave,erro){
    document.form1.descrdepto.value = chave;
    if(erro==true){
      document.form1.coddepto.focus();
      document.form1.coddepto.value = '';
    }
  }
  function js_mostradepart1(chave1,chave2){
    document.form1.coddepto.value = chave1;
    document.form1.descrdepto.value = chave2;
    db_iframe_depart.hide();
  }
  function js_pesquisainicio(){
    qry  = "&chave_m80_codtipo=<?=$naoinill?>";
    qry += "&chave_m80_coddepto=<?=db_getsession("DB_coddepto")?>";
    qry += "&naoinill=<?=($naoinill)?>";
    <?
    if($db_opcao!=3 && $db_opcao!=33){
      echo "qry += '&naoatendido=true';";
    }
    ?>
    js_OpenJanelaIframe('top.corpo','db_iframe_matestoqueini','func_matestoqueini.php?funcao_js=parent.js_preenchepesquisa|m80_codigo'+qry,'Pesquisa',true);
  }
  function js_preenchepesquisa(chave){
    db_iframe_matestoqueini.hide();
    qry = "";
    <?
    if((isset($entrada) && $entrada == true) || $m80_codtipo==3 || $m80_codtipo==15 ){
      echo "qry = '&entrada=true';\n";
    }
    ?>
    <?
    if($db_opcao!=1){
      echo " location.href = '".basename($GLOBALS["HTTP_SERVER_VARS"]["PHP_SELF"])."?chavepesquisa='+chave+qry";
    }
    ?>
  }
  <?
  if(isset($m71_valor) && trim($m71_valor)!="" && isset($m71_quant) && trim($m71_quant)!=""){
    echo "
    js_calculavalortotal('$m71_valor','total');
    ";
  }
  ?>
  function js_pesquisaimplanta(){
    qry = "&chave_m80_codtipo=<?=$m80_codtipo?>";
    js_OpenJanelaIframe('top.corpo','db_iframe_matestoqueini','func_matestoqueini.php?funcao_js=parent.js_preenchepesquisa|m60_codmater|m80_codigo'+qry,'Pesquisa',true);
  }
  function js_verificaquant(valor){
    //  splitar = document.form1.quantrest.value.split(" / ");
    valor = new Number(valor);
    //  soli  = new Number(splitar[0]);
    //  rest  = new Number(splitar[1]);
    soli  = 0;
    rest  = new Number(document.form1.quantrest.value);
    erro  = 0;
    if(valor <= 0){
      alert("Usuário:\n\nA quantidade deve ser superior à informada.\n\nAdministrador:");
      erro++;
    }else if(valor<rest){
      alert("Usuário:\n\nA quantidade informada não deve ser menor que a quantidade restante.\n\nAdministrador:");
      erro++;
    }else if(valor<soli){
      alert("Usuário:\n\nA quantidade informada não deve ser menor que a quantidade solicitada.\n\nAdministrador:");
      erro++;
    }
    if(erro>0){
      document.form1.m71_valor.value = "";
      document.form1.m71_valorunit.value = "";
      document.form1.m71_quant.value = "";
      document.form1.m71_quant.focus();
    }
  }
  <?
  if(isset($mostrar)){
    echo '
    function js_calcularmedia(){
      arr_valores = new Array();
      ';
      $index = 1;
      $valortotal = 0;
      for($i=0;$i<$clempempitem->numrows;$i++){
        db_fieldsmemory($result_valoritens,$i);
        $valortotal += $valorunit;
        $valormedia  = $valortotal/$index;
        echo '
        arr_valores['.$index.'] = new Number('.$valormedia.');
        ';
        $index++;
      }
      echo '
      indexmaximo = new Number('.($clempempitem->numrows).');
      valormedia  = new Number(document.form1.media.value);
      if(document.form1.m71_quant.value!=""){
        quantidade  = new Number(document.form1.m71_quant.value);
      }else{
        quantidade  = new Number(1);
      }
      if(document.form1.media.value!=""){
        if(valormedia<=indexmaximo){
          valor = new Number(arr_valores[valormedia]);
          valvezesqtd = new Number(quantidade*valor);
        }else{
          valor = new Number(arr_valores['.($index-1).']);
          valvezesqtd = new Number(quantidade*valor);
          document.form1.media.value = '.($index-1).';
          alert("Quantidade máxima de empenhos com este item: '.($index-1).'");
        }

        document.form1.m71_valor.value = valvezesqtd.toFixed(2);
        document.form1.m71_valorunit.value = valor.toFixed(2);

      }else{
        document.form1.m71_valorunit.value = "";
        document.form1.m71_valor.value = "";
      }
    }';
    if ($db_opcao == 2 || $db_opcao == 22) {
    } else {
      echo 'js_calcularmedia();';
    }
  }

  ?>
  function js_controlavalidadade() {
    var e60_codemp = document.getElementsByName("e60_codemp")[0].value;
    if (e60_codemp == "") {
      alert("Preenchimento do Empenho é obrigatório");
      return false;
    }

    /*if ($F("m80_obs") == "") {
      alert("Necessário preenchimento da observação");
      return false;
    }*/

    

    iControleValidade = $F('m60_controlavalidade');
    if (iControleValidade == 1 || iControleValidade == 2) {

      if ($F('m77_lote') == '' || $F('m77_dtvalidade') == '') {

        if (!confirm('Não foi informado o lote/data de validade do item.\nDeseja Prosseguir?')) {
          return false;
        } else {
          return true;
        }
      }
    }
   }
  function js_pesquisam78_matfabricante(mostra){
  if(mostra==true){
    js_OpenJanelaIframe('top.corpo','db_iframe_matfabricante','func_matfabricante.php?funcao_js=parent.js_mostramatfabricante1|m76_sequencial|m76_nome','Pesquisa',true);
  }else{
     if(document.form1.m78_matfabricante.value != ''){
        js_OpenJanelaIframe('top.corpo','db_iframe_matfabricante','func_matfabricante.php?pesquisa_chave='+document.form1.m78_matfabricante.value+'&funcao_js=parent.js_mostramatfabricante','Pesquisa',false);
     }else{
       document.form1.m76_nome.value = '';
     }
  }
}
function js_mostramatfabricante(chave,erro){
  document.form1.m76_nome.value = chave;
  if(erro==true){
    document.form1.m78_matfabricante.focus();
    document.form1.m78_matfabricante.value = '';
  }
}
function js_mostramatfabricante1(chave1,chave2){
  document.form1.m78_matfabricante.value = chave1;
  document.form1.m76_nome.value = chave2;
  db_iframe_matfabricante.hide();
}



document.getElementById("m79_notafiscal").required = true;
document.getElementById("m79_data").required = true;




function js_pesquisae60_codemp(mostra){
  if(mostra==true){
    //js_OpenJanelaIframe('top.corpo','db_iframe_empempenho','func_empempenhonrm.php?funcao_js=parent.js_mostraempempenho2|e60_codemp|e60_anousu|e60_numemp|e60_vlremp|z01_numcgm|z01_nome','Pesquisa',true);
    js_OpenJanelaIframe('top.corpo','db_iframe_empempenho','func_empempenhonrm.php?funcao_js=parent.js_mostraempempenho2|e60_codemp|e60_anousu|e60_numemp|e60_vlremp|z01_numcgm|z01_nome|e60_vlranu|e60_vlrpag','Pesquisa',true);
  }else{
   // js_OpenJanelaIframe('top.corpo','db_iframe_empempenho02','func_empempenho.php?pesquisa_chave='+document.form1.e60_numemp.value+'&funcao_js=parent.js_mostraempempenho','Pesquisa',false);
  }
}
function js_mostraempempenho2(chave1, chave2, chave3, chave4, chave5, chave6, chave7, chave8){
  
  document.form1.e60_codemp.value = chave1 + '/' + chave2;
  document.form1.seqempenho.value = chave3;
  //document.form1.valorempenho.value = chave4;
  document.form1.valorempenho.value = chave4 - chave7 - chave8;
  document.form1.m78_matfabricante.value = chave5;
  document.form1.m76_nome.value = chave6;  
  db_iframe_empempenho.hide();  
}



function js_mascara(evt){
      var evt = (evt) ? evt : (window.event) ? window.event : "";
      
      if( (evt.charCode >46 && evt.charCode <58) || evt.charCode ==0 ){//8:backspace|46:delete|190:. 
  return true;
      }else{
  return false;
      }  
    }



    var guardatotal = 0;

    document.getElementById('incluiritem').addEventListener("click", function(){
      var valortotalempenho = document.getElementById("valorempenho").value;
      var nf, dn, vu, vt, qt, ne, fc;
      nf = document.getElementById("m79_notafiscal").value;
      dn = document.getElementById("m79_data").value;
      vu = document.getElementById("m71_valorunit").value;
      vt = document.getElementById("m71_valor").value;
      qt = document.getElementById("m71_quant").value;
      ne = document.getElementsByName("e60_codemp")[0].value;
      fc = document.getElementById("m78_matfabricante").value;

      if(ne == ""){
        alert("Nº do Empenho é obrigatório");
        return false;
      }

      if(document.getElementById("m60_codmater").value == ""){
        alert("Código do Material é obrigatório");
        return false;
      }

      if(qt == ""){
        alert("Quantidade de entrada é obrigatório");
        return false;
      }

      if(vu == ""){
        alert("Valor unitário é obrigatório");
        return false;
      }

      if(vt == ""){
        alert("Valor total é obrigatório");
        return false;
      }

      if(fc == ""){
        alert("Fabricante é obrigatório");
        return false;
      }

      if(nf == ""){
        alert("Nota Fiscal é obrigatória");
        return false;
      }

      if(dn == ""){
        alert("Data da Nota Fiscal é obrigatória");
        return false;
      }

      document.getElementById("db_opcao").disabled = false;

      /*if(document.getElementById("m80_obs").value == ""){
        alert("Observação é obrigatória");
        return false;
      }*/

      

      

      

      guardatotal += parseFloat(vt);
      valortotalempenho = Math.round(valortotalempenho * 100) / 100;
      //console.log("==================");
      //console.log(guardatotal);
      //console.log(parseFloat(valortotalempenho));
      //console.log("======================");
      //return false;
      if(guardatotal > parseFloat(valortotalempenho)){
        alert("Somatório dos itens ultrapassa o valor total do empenho.");
        guardatotal -= parseFloat(vt).toFixed(2);
        return false;
      }

      
      //return false;


      document.getElementById("vtdi").innerHTML = guardatotal;

      var inputnf = document.createElement("input");
      inputnf.setAttribute("type", "hidden");
      inputnf.setAttribute("name", "nf[]");
      inputnf.setAttribute("value", nf);
      document.getElementById("formulario").appendChild(inputnf);

      var inputdn = document.createElement("input");
      inputdn.setAttribute("type", "hidden");
      inputdn.setAttribute("name", "dn[]");
      inputdn.setAttribute("value", dn);
      document.getElementById("formulario").appendChild(inputdn);

      var inputvu = document.createElement("input");
      inputvu.setAttribute("type", "hidden");
      inputvu.setAttribute("name", "vu[]");
      inputvu.setAttribute("value", vu);
      document.getElementById("formulario").appendChild(inputvu);

      var inputvt = document.createElement("input");
      inputvt.setAttribute("type", "hidden");
      inputvt.setAttribute("name", "vt[]");
      inputvt.setAttribute("value", vt);
      document.getElementById("formulario").appendChild(inputvt);

      var inputqt = document.createElement("input");
      inputqt.setAttribute("type", "hidden");
      inputqt.setAttribute("name", "qt[]");
      inputqt.setAttribute("value", qt);
      document.getElementById("formulario").appendChild(inputqt);

      var inputne = document.createElement("input");
      inputne.setAttribute("type", "hidden");
      inputne.setAttribute("name", "ne[]");
      inputne.setAttribute("value", ne);
      document.getElementById("formulario").appendChild(inputne);


      var tabelao = document.getElementById("tabelao");
      linha = tabelao.insertRow(1);
      tdnf = linha.insertCell(0);
      tddn = linha.insertCell(1);
      tdvu = linha.insertCell(2);
      tdvt = linha.insertCell(3);
      tdqt = linha.insertCell(4);
      tdne = linha.insertCell(5);

      tdnf.innerHTML = nf;
      tddn.innerHTML = dn;
      tdvu.innerHTML = vu;
      tdvt.innerHTML = vt;
      tdqt.innerHTML = qt;
      tdne.innerHTML = ne;

      var hm60_codmater, hcoddepto, hm71_quant, hm71_valor, hm80_obs, hm80_codtipo, hm79_notafiscal, hm79_data, hm77_lote, hm77_dtvalidade, hm78_matfabricante, hm66_codcon, he69_serienota, he69_subserienota, hnumprocessolicit;

      hm60_codmater = document.getElementById("m60_codmater").value;
      hcoddepto = document.getElementById("coddepto").value;
      hm71_quant = document.getElementById("m71_quant").value;
      hm71_valor = document.getElementById("m71_valor").value;
      hm80_obs = document.getElementById("m80_obs").value;
      hm80_codtipo = document.getElementById("m80_codtipo").value;
      hm79_notafiscal = document.getElementById("m79_notafiscal").value;
      hm79_data = document.getElementById("m79_data").value;
      hm77_lote = document.getElementById("m77_lote").value;
      hm77_dtvalidade = document.getElementById("m77_dtvalidade").value;
      hm78_matfabricante = document.getElementById("m78_matfabricante").value;
      hm66_codcon = document.getElementById("m66_codcon").value;
      he69_serienota = document.getElementById("e69_serienota").value;
      he69_subserienota = document.getElementById("e69_subserienota").value;
      hnumprocessolicit = document.getElementById("numprocessolicit").value;

      var input_hm60_codmater = document.createElement("input");
input_hm60_codmater.setAttribute("type", "hidden");
input_hm60_codmater.setAttribute("name", "hm60_codmater[]");
input_hm60_codmater.setAttribute("value", hm60_codmater);
document.getElementById("formulario").appendChild(input_hm60_codmater);
var input_hcoddepto = document.createElement("input");
input_hcoddepto.setAttribute("type", "hidden");
input_hcoddepto.setAttribute("name", "hcoddepto[]");
input_hcoddepto.setAttribute("value", hcoddepto);
document.getElementById("formulario").appendChild(input_hcoddepto);
var input_hm71_quant = document.createElement("input");
input_hm71_quant.setAttribute("type", "hidden");
input_hm71_quant.setAttribute("name", "hm71_quant[]");
input_hm71_quant.setAttribute("value", hm71_quant);
document.getElementById("formulario").appendChild(input_hm71_quant);
var input_hm71_valor = document.createElement("input");
input_hm71_valor.setAttribute("type", "hidden");
input_hm71_valor.setAttribute("name", "hm71_valor[]");
input_hm71_valor.setAttribute("value", hm71_valor);
document.getElementById("formulario").appendChild(input_hm71_valor);
var input_hm80_obs = document.createElement("input");
input_hm80_obs.setAttribute("type", "hidden");
input_hm80_obs.setAttribute("name", "hm80_obs[]");
input_hm80_obs.setAttribute("value", hm80_obs);
document.getElementById("formulario").appendChild(input_hm80_obs);
var input_hm80_codtipo = document.createElement("input");
input_hm80_codtipo.setAttribute("type", "hidden");
input_hm80_codtipo.setAttribute("name", "hm80_codtipo[]");
input_hm80_codtipo.setAttribute("value", hm80_codtipo);
document.getElementById("formulario").appendChild(input_hm80_codtipo);
var input_hm79_notafiscal = document.createElement("input");
input_hm79_notafiscal.setAttribute("type", "hidden");
input_hm79_notafiscal.setAttribute("name", "hm79_notafiscal[]");
input_hm79_notafiscal.setAttribute("value", hm79_notafiscal);
document.getElementById("formulario").appendChild(input_hm79_notafiscal);
var input_hm79_data = document.createElement("input");
input_hm79_data.setAttribute("type", "hidden");
input_hm79_data.setAttribute("name", "hm79_data[]");
input_hm79_data.setAttribute("value", hm79_data);
document.getElementById("formulario").appendChild(input_hm79_data);
var input_hm77_lote = document.createElement("input");
input_hm77_lote.setAttribute("type", "hidden");
input_hm77_lote.setAttribute("name", "hm77_lote[]");
input_hm77_lote.setAttribute("value", hm77_lote);
document.getElementById("formulario").appendChild(input_hm77_lote);
var input_hm77_dtvalidade = document.createElement("input");
input_hm77_dtvalidade.setAttribute("type", "hidden");
input_hm77_dtvalidade.setAttribute("name", "hm77_dtvalidade[]");
input_hm77_dtvalidade.setAttribute("value", hm77_dtvalidade);
document.getElementById("formulario").appendChild(input_hm77_dtvalidade);
var input_hm78_matfabricante = document.createElement("input");
input_hm78_matfabricante.setAttribute("type", "hidden");
input_hm78_matfabricante.setAttribute("name", "hm78_matfabricante[]");
input_hm78_matfabricante.setAttribute("value", hm78_matfabricante);
document.getElementById("formulario").appendChild(input_hm78_matfabricante);
var input_hm66_codcon = document.createElement("input");
input_hm66_codcon.setAttribute("type", "hidden");
input_hm66_codcon.setAttribute("name", "hm66_codcon[]");
input_hm66_codcon.setAttribute("value", hm66_codcon);
document.getElementById("formulario").appendChild(input_hm66_codcon);
var input_he69_serienota = document.createElement("input");
input_he69_serienota.setAttribute("type", "hidden");
input_he69_serienota.setAttribute("name", "he69_serienota[]");
input_he69_serienota.setAttribute("value", he69_serienota);
document.getElementById("formulario").appendChild(input_he69_serienota);
var input_he69_subserienota = document.createElement("input");
input_he69_subserienota.setAttribute("type", "hidden");
input_he69_subserienota.setAttribute("name", "he69_subserienota[]");
input_he69_subserienota.setAttribute("value", he69_subserienota);
document.getElementById("formulario").appendChild(input_he69_subserienota);
var input_hnumprocessolicit = document.createElement("input");
input_hnumprocessolicit.setAttribute("type", "hidden");
input_hnumprocessolicit.setAttribute("name", "hnumprocessolicit[]");
input_hnumprocessolicit.setAttribute("value", hnumprocessolicit);
document.getElementById("formulario").appendChild(input_hnumprocessolicit);



      

      document.getElementById("m60_codmater").value = "";
      document.getElementById("m60_descr").value = "";
      document.getElementById("m61_descr").value = "";
      document.getElementById("m61_descrsai").value = "";
      document.getElementById("m71_quant").value = "";
      document.getElementById("m71_valorunit").value = "";
      document.getElementById("m71_valor").value = "";
      document.getElementById("m77_lote").value = "";
      document.getElementById("m77_dtvalidade").value = "";
      



    }, false);
  </script>
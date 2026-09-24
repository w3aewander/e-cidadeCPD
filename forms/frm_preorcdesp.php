<?


//MODULO: orcamento
$clorcdotacao->rotulo->label();
$clrotulo = new rotulocampo;
$clrotulo->label("o54_anousu");
$clrotulo->label("o55_descr");
$clrotulo->label("o41_descr");
$clrotulo->label("o40_descr");
$clrotulo->label("o41_descr");
$clrotulo->label("o52_descr");
$clrotulo->label("o53_descr");
$clrotulo->label("o56_elemento");
$clrotulo->label("o56_descr");
$clrotulo->label("o15_descr");
$clrotulo->label("o61_codigo");
$clrotulo->label("o11_descricao");
$clrotulo->label("DB_txtdotacao");
$clrotulo->label("o58_concarpeculiar");
$clrotulo->label("c58_descr");

$clestrutura = new cl_estrutura;
if(isset($atualizar)){
  $o58_orgao='';
  $o58_unidade='';
  $o58_funcao='';
  $o58_subfuncao='';
  $o58_programa='';
  $o58_projativ='';
  $o56_elemento='';
  $o61_codigo='';
  $o58_codigo='';
  $o40_descr='';
  $o41_descr='';
  $o52_descr='';
  $o53_descr='';
  $o54_descr='';
  $o56_descr='';
  $o55_descr='';
  $o15_descr='';
  $o15_contra_recurso='';

 if($o50_estrutdespesa==""){
       $tot='0';
 }else{
   $matriz=split('\.',$o50_estrutdespesa); 
   $tot=count($matriz);
 }
 for($i=0; $i<$tot; $i++){
   switch($i){
     case 0://orgao
          $result = $clorcorgao->sql_record($clorcorgao->sql_query_file(db_getsession("DB_anousu"),$matriz[$i],'o40_descr,o40_orgao as o58_orgao'));
          if($clorcorgao->numrows>0){
            db_fieldsmemory($result,0);
	  }else{
	    $o40_descr='Chave ('.$matriz[$i].') não encontrado';
	    $o58_orgao='';
	  }  
	  break;
     case 1://unidade
          if($o58_orgao!=''){  
	    $result = $clorcunidade->sql_record($clorcunidade->sql_query_file(db_getsession("DB_anousu"),$o58_orgao,$matriz[$i],'o41_descr,o41_unidade as o58_unidade,o41_instit'));
	    if($clorcunidade->numrows>0){
	      db_fieldsmemory($result,0);
        $o58_instit  = $o41_instit;
        $sql         = "select nomeinst from db_config where codigo = $o41_instit";
        $nomeinst    = @pg_result(pg_query($sql),0,"nomeinst");
	    }else{
	      $o58_unidade='';
	      $o41_descr='Chave ('.$matriz[$i].') não encontrado';
	    }  
	  }else{
	    $o58_unidade='';
	    $o41_descr='Chave ('.$matriz[$i].') não encontrado';
	  }  
 	  break;
     case 2://funcao
	    $result = $clorcfuncao->sql_record($clorcfuncao->sql_query_file($matriz[$i],'o52_descr,o52_funcao as o58_funcao'));
	    if($clorcfuncao->numrows>0){
	      db_fieldsmemory($result,0);
	    }else{
	      $o58_funcao='';
	      $o52_descr='Chave ('.$matriz[$i].') não encontrado';
	    }  
 	  break;
     case 3://subfuncao	
	    $result = $clorcsubfuncao->sql_record($clorcsubfuncao->sql_query_file($matriz[$i],'o53_descr,o53_subfuncao as o58_subfuncao'));
	    if($clorcsubfuncao->numrows>0){
	      db_fieldsmemory($result,0);
	    }else{
	      $o58_subfuncao='';
	      $o53_descr='Chave ('.$matriz[$i].') não encontrado';
	      }  
 	  break;
     case 4://programa
	    $result = $clorcprograma->sql_record($clorcprograma->sql_query_file(db_getsession("DB_anousu"),$matriz[$i],'o54_descr,o54_programa as o58_programa'));
	    if($clorcprograma->numrows>0){
	      db_fieldsmemory($result,0);
	    }else{
	      $o58_programa='';
	      $o54_descr='Chave ('.$matriz[$i].') não encontrado';
	    }  
	  break;
     case 5://projativ
	    $result = $clorcprojativ->sql_record($clorcprojativ->sql_query_file(db_getsession("DB_anousu"),$matriz[$i],'o55_descr,o55_projativ as o58_projativ'));
	    if($clorcprojativ->numrows>0){
	      db_fieldsmemory($result,0);
	    }else{
	      $o58_projativ='';
	      $o55_descr='Chave ('.$matriz[$i].') não encontrado';
	    }  
	  break;
     case 6://elemento de despesa	
        $result = $clorcelemento->sql_record($clorcelemento->sql_query(null,null,"o56_descr, o56_elemento ","","o56_anousu = ".db_getsession("DB_anousu")." and o56_elemento like '".substr($matriz[$i],0,12)."%' "));
	    if($clorcelemento->numrows>0){
	      db_fieldsmemory($result,0);
	    }else{
	      $o56_elemento='';
	      $o56_descr=' Chave ('.$matriz[$i].') não encontrado';
	    }  
	  break;
     case 7://tipo de  recurso
          $result = $clorctiporec->sql_record($clorctiporec->sql_query_file($matriz[$i],"o15_descr ,o15_codigo as o58_codigo"));
          if($clorctiporec->numrows!=0){
	      db_fieldsmemory($result,0);
	  }else{
	      $o58_codigo='';
	      $o15_descr='Chave ('.$matriz[$i].') não encontrado';
	  }  
	  break;
     case 8://contra recurso
          $result = $clorctiporec->sql_record($clorctiporec->sql_query_file($matriz[$i],"o15_descr as  o15_contra_recurso,o15_codigo as o61_codigo"));
          if($clorctiporec->numrows!=0){
	      db_fieldsmemory($result,0);
	  }else{
	      $o61_codigo='';
	      $o15_contra_recurso='Chave ('.$matriz[$i].') não encontrado';
	  } 
	  break;
     case 9://localiador gastos
       $oDaoLocalizador = db_utils::getDao("ppasubtitulolocalizadorgasto");
       $sSqlSubtitulo = $oDaoLocalizador->sql_query_file($matriz[$i]);
       $rsLocalizador = $oDaoLocalizador->sql_record($rsLocalizador);
       if($oDaoLocalizador->numrows!=0){
         db_fieldsmemory($rsLocalizador,0);
       } else {
         $o58_localiadorgastos ='';
         $o11_descriacao ='Chave ('.$matriz[$i].') não encontrado';
      }
   } 
 }

 
}
?>
<form name="form1" method="post" action="" id="form1">
<table border="0">
 <tr>
  <td>
  <fieldset><legend><b>Dotação</b></legend>
  <table>
  <tr>
    <td nowrap title="<?=@$To58_anousu?>">
       <?
       echo $Lo58_anousu;
       ?>
    </td>
    <td> 
<?
//$o58_instit = db_getsession('DB_instit');
//db_input('o58_instit',4,$Io58_instit,true,'hidden',3,"")
?>
<?
//$o58_anousu = db_getsession('DB_anousu');
//$o58_anousu = 2022;
$o58_anousu = $anonovo;
db_input('o58_anousu',4,$Io58_anousu,true,'text',3,"")
?>
    </td>
  </tr>
    <?
	 $clestrutura->reload=true;
	 $clestrutura->size=70;
	 $clestrutura->estrutura("o50_estrutdespesa");    
    ?>
    <!--<input type="text" name="o50_estrutdespesa" maxlength="46" size="70">-->
  <tr>
    <td nowrap title="<?=@$To58_coddot?>">
    <?=@$Lo58_coddot?>
    </td>
    <td> 
    <?
    db_input('o58_coddot',11,$Io58_coddot,true,'text',3,"")
    ?>
    </td>
  </tr>
  <tr>
    <td nowrap title="<?=@$To58_instit?>">
    <?=@$Lo58_instit?>
    </td>
    <td> 
    <?
	//	if (!isset($o58_intit)){
  //		$nomeinst   = pg_result(pg_query("select nomeinst from db_config where codigo = ".db_getsession("DB_instit")),0,0);
	//  	$o58_instit = db_getsession("DB_instit");
	//	}
		db_input('o58_instit',11,$Io58_instit,true,'text',3,'');
    db_input('nomeinst',55,$Io58_instit,true,'text',3,"")
    ?>
    </td>
  </tr>


  <tr>
    <td nowrap title="<?=@$To58_orgao?>">
       <?
	       db_ancora(@$Lo58_orgao,"js_pesquisao58_orgao(true);",$db_opcao);
       ?>
    </td>
    <td> 
			<?
				db_input('o58_orgao',11,$Io58_orgao,true,'text',$db_opcao," onchange='js_pesquisao58_orgao(false);'");
				db_input('o40_descr',55,$Io40_descr,true,'text',3,'');        
      ?>
    </td>
  </tr>
	<?
   $mostraUni = 1;
   //$mostraUni = 3;
	 if (isset($o58_orgao) && @$o58_orgao != ''){
		 $mostraUni = $db_opcao;
		 $filtrar   = "orgao=$o58_orgao&";
	 }   
	?>
  <tr>
    <td nowrap title="<?=@$To58_unidade?>">
       <?
       db_ancora(@$Lo58_unidade,"js_pesquisao58_unidade(true);",$mostraUni);
       ?>
    </td>
    <td> 
<?
db_input('o58_unidade',11,$Io58_unidade,true,'text',$mostraUni," onchange='js_pesquisao58_unidade(false);'")
?>
       <?
db_input('o41_descr',55,$Io41_descr,true,'text',3,'')
       ?>
    </td>
  </tr>
  <tr>
    <td nowrap title="<?=@$To58_funcao?>">
       <?
       db_ancora(@$Lo58_funcao,"js_pesquisao58_funcao(true);",$db_opcao);
       ?>
    </td>
    <td> 
<?
db_input('o58_funcao',11,$Io58_funcao,true,'text',$db_opcao," onchange='js_pesquisao58_funcao(false);'")
?>
       <?
db_input('o52_descr',55,$Io52_descr,true,'text',3,'')
       ?>
    </td>
  </tr>
  <tr>
    <td nowrap title="<?=@$To58_subfuncao?>">
       <?
       db_ancora(@$Lo58_subfuncao,"js_pesquisao58_subfuncao(true);",$db_opcao);
       ?>
    </td>
    <td> 
<?
db_input('o58_subfuncao',11,$Io58_subfuncao,true,'text',$db_opcao," onchange='js_pesquisao58_subfuncao(false);'")
?>
       <?
db_input('o53_descr',55,$Io53_descr,true,'text',3,'')
       ?>
    </td>
  </tr>
  <tr>
    <td nowrap title="<?=@$To58_programa?>">
       <?
       db_ancora(@$Lo58_programa,"js_pesquisao58_programa(true);",$db_opcao);
       ?>
    </td>
    <td> 
<?
db_input('o58_programa',11,$Io58_programa,true,'text',$db_opcao," onchange='js_pesquisao58_programa(false);'")
?>
       <?
db_input('o54_descr',55,$Io54_anousu,true,'text',3,'');
       ?>
    </td>
  </tr>
  <tr>
    <td nowrap title="<?=@$To58_projativ?>">
       <?
       db_ancora(@$Lo58_projativ,"js_pesquisao58_projativ(true);",$db_opcao);
       ?>
    </td>
    <td> 
<?
db_input('o58_projativ',11,$Io58_projativ,true,'text',$db_opcao," onchange='js_pesquisao58_projativ(false);'")
?>
       <?
db_input('o55_descr',55,$Io55_descr,true,'text',3,'')
       ?>
    </td>
  </tr>
  <tr>
    <td nowrap title="<?=@$To58_elemento?>">
       <?
       db_ancora(@$Lo56_elemento,"js_pesquisao58_codele(true);",$db_opcao);
       ?>
    </td>
    <td> 
<?
db_input('o56_elemento',11,$Io56_elemento,true,'text',$db_opcao," onchange='js_pesquisao58_codele(false);'")
?>
       <?
db_input('o56_descr',55,$Io56_descr,true,'text',3,'')
       ?>
    </td>
  </tr>
  

  <tr>
    <td nowrap title="<?=@$To58_codigo?>">
       <?
       db_ancora(@$Lo58_codigo,"js_pesquisao58_codigo(true);",$db_opcao);
       ?>
    </td>
    <td> 
<?
db_input('o58_codigo',11,$Io58_codigo,true,'text',$db_opcao," onchange='js_pesquisao58_codigo(false);'")
?>
       <?
db_input('o15_descr',55,$Io15_descr,true,'text',3,'')
       ?>
    </td>
  </tr>



  <tr>
    <td nowrap title="<?=@$To58_localizadorgastos?>">
       <?
       db_ancora(@$Lo58_localizadorgastos,"js_pesquisao58_localizadorgastos(true);",$db_opcao,"","o58_gastosancora");
       ?>
    </td>
    <td> 
   <?
   db_input('o58_localizadorgastos',11,$Io58_localizadorgastos,true,'text',$db_opcao,"onchange='js_pesquisao58_localizadorgastos(false);'");  
   db_input('o11_descricao',55,$Io11_descricao,true,'text',3,'');
    ?>
    </td>
  </tr>
  <tr>
    <td nowrap title="<?=@$To58_concarpeculiar?>">
       <?
       db_ancora(@$Lo58_concarpeculiar,"js_pesquisao58_concarpeculiar(true);",$db_opcao,"","o58_concarpeculiarancora");
       ?>
    </td>
    <td> 
   <?
   db_input('o58_concarpeculiar',11,$Io58_concarpeculiar,true,'text',$db_opcao,"onchange='js_pesquisao58_concarpeculiar(false);'");  
   db_input('c58_descr',55,$Ic58_descr,true,'text',3,'');
    ?>
    </td>
    </tr>
  
<tr>
      <td class="bold"><label for="esferaOrcamentaria">Esfera Orçamentária:</label></td>
      <td>
          <?php
          if (empty($o58_esferaorcamentaria)) {
              $o58_esferaorcamentaria = '';
          }
          ?>
          <select name="esferaOrcamentaria" id="esferaOrcamentaria" class="field-size10">
              <option selected value="">Selecionar</option>
              <option <?php echo $o58_esferaorcamentaria  == '10' ? 'selected' : '' ?> value="10">10 - F - Orçamento Fiscal</option>
              <option <?php echo $o58_esferaorcamentaria  == '20' ? 'selected' : '' ?> value="20">20 - S - Orçamento da Seguridade Social</option>
              <option <?php echo $o58_esferaorcamentaria  == '30' ? 'selected' : '' ?> value="30">30 - I - Orçamento de Investimento</option>
          </select>
      </td>
  </tr>

  <tr>
    <td nowrap title="<?=@$To58_valor?>">
       <?=$Lo58_valor?>
    </td>
    <td> 
    <?
    db_input('o58_valor',11,$Io58_valor,true,'text',$db_opcao," ")
    ?>
    </td>
    <input type="hidden" name="id" id="id" value="">

  </tr>


  <tr id="td">
    <td><b>Valor Total Fixado:</b></td>
    <td>
      <input type="text" name="vtf" id="vtf" style="margin-right: 10px;background-color: #DEB887;" readonly>      
    <b>Somatória das Dotações:</b>  
      <input type="text" name="sdd" id="sdd" style="margin-left: 10px;background-color: #DEB887;" readonly>
    </td>
  </tr>

  <tr id="td1" style="display: none;"></tr>
<?php /* ?>

  <tr id="td2">
    <td><b>Somatória das Dotações:</b></td>
    <td>
      <input type="text" name="sdd" id="sdd" style="margin-left: 10px;background-color: #DEB887;" readonly>
    </td>
  </tr>
  <?php */ ?>

  </table>
  </fieldset>
  </td>
  </tr>
  </table>

  <fieldset style="width: 400px">
    <legend>Valores Previstos</legend>
    <b>2026: </b><input type="text" name="a26" id="a26" readonly style="background-color: #DEB887;"><br>
    <b>2027: </b><input type="text" name="a27" id="a27" readonly style="background-color: #DEB887;"><br>
    <b>2028: </b><input type="text" name="a28" id="a28" readonly style="background-color: #DEB887;"><br>
    <b>2029: </b><input type="text" name="a29" id="a29" readonly style="background-color: #DEB887;"><br>
  </fieldset>

  
<input name="db_opcao" type="submit" id="db_opcao" value="<?=($db_opcao==1?"Incluir Prévia":($db_opcao==2||$db_opcao==22?"Alterar":"Excluir"))?>" <?=($db_botao==false?"disabled":"")?> >


<input type="submit" name="apagar" id="apagar" value="Excluir" style="display: none">

<input name="pesquisar" type="button" id="pesquisar" value="Pesquisar" onclick="js_pesquisa22();" >

<?php $msgbtn = "Incluir {$anonovo}"; ?>
<?php if(db_getsession("DB_id_usuario") == 1 || db_getsession("DB_id_usuario") == 370 || db_getsession("DB_id_usuario") == 37) : ?>
<input name="i2021" type="submit" id="i2021" value="<?=$msgbtn?>">
<?php else: ?>
<input name="i2021" type="submit" id="i2021" value="<?=$msgbtn?>" disabled>
<?php endif; ?>

<!--<a href="./relprevdes.php" type="button">Relatório</a>-->
<?php if(db_getsession("DB_instit") == 1) : ?>
<input name="relatorio" type="button" id="relatorio" value="Relatório" onclick="imprimeTudo()">
<input name="csv" type="button" id="csv" value="CSV" onclick="imprimeTudoCsv()">
<?php else : ?>
<input name="relatorio" type="button" id="relatorio" value="Relatório" onclick="imprime()">
<input name="csv" type="button" id="csv" value="CSV" onclick="imprimeCsv()">
<?php endif; ?>

<?php /* ?>
<?php if(db_getsession("DB_id_usuario") == 1) : ?>
  <a href="historicopreviasd.php">Histórico de Alterações</a>
<?php endif; ?>
<?php */ ?>

</form>
<?php if(db_getsession("DB_instit") == 1) : ?>

<div style="width:500px">
<fieldset><legend>Instituições para o relatório</legend>
<!--<input checked type="checkbox" name="checai" value="1">1-->
<!--<input type="checkbox" name="checai" value="96">96-->



<?php foreach ($instituicoes as $ins) :  ?>
<input <?= ($ins['o58_instit'] == 1) ? "checked" : "";?> type="checkbox" name="checai" value="<?=$ins['o58_instit'];?>"><?=$ins['o58_instit'];?>
<?php endforeach; ?>
<input type="checkbox" id="tudo" onclick="marca(this);">Todos
</fieldset>
</div>
<?php endif; ?>
  
    

<script>


  //Quando marcar tudo, marca as outroas. Name do todas é diferente e não vai pro get.
  function imprime(){
    //var jan = window.open('relprevdes.php','width='+(screen.availWidth-5)+',height='+(screen.availHeight-40)+',scrollbars=1,location=0 ');    
    jan = window.open("relprevdes.php", "Relatório", "about:blank");  
    jan.moveTo(0,0);
  }

  function imprimeCsv(){    
    jan = window.open("relprevdes.php?tipo=csv", "Relatório", "about:blank");  
    jan.moveTo(0,0);
  }

  function imprimeTudo(){
    var caixas = document.getElementsByName("checai");
    //console.log(caixas);
    //console.log(caixas[0].value);
    var inst = "";
    for(var i = 0; i < caixas.length; i++){
      if(caixas[i].checked == true){        
        inst += caixas[i].value + ",";
      }
    }

    if(!inst){
      inst = '1,';
    }
    
    
    //var jan = window.open('relprevdes.php?inst='+inst+'','width='+(screen.availWidth-5)+',height='+(screen.availHeight-40)+',scrollbars=1,location=0 ');    
    jan = window.open("relprevdes.php?inst="+inst+"", "Relatório", "about:blank");  
    jan.moveTo(0,0);
  }

  function imprimeTudoCsv(){
    var caixas = document.getElementsByName("checai");
    //console.log(caixas);
    //console.log(caixas[0].value);
    var inst = "";
    for(var i = 0; i < caixas.length; i++){
      if(caixas[i].checked == true){        
        inst += caixas[i].value + ",";
      }
    }

    if(!inst){
      inst = '1,';
    }
    
    
    //var jan = window.open('relprevdes.php?inst='+inst+'','width='+(screen.availWidth-5)+',height='+(screen.availHeight-40)+',scrollbars=1,location=0 ');    
    jan = window.open("relprevdes.php?tipo=csv&inst="+inst+"", "Relatório", "about:blank");  
    jan.moveTo(0,0);
  }

  function marca(x){
     var caixas = document.querySelectorAll('input[type="checkbox"]');
    for (var i = 0; i < caixas.length; i++) {
        if (caixas[i] != x)
            caixas[i].checked = x.checked;
    }
  }


function js_pesquisao58_orgao(mostra){
  if(mostra==true){    
    js_OpenJanelaIframe('','db_iframe_orcorgao','func_orcorgaopreorcdesp.php?funcao_js=parent.js_mostraorcorgao1|o40_orgao|o40_descr','Pesquisa',true);
  }else{
    js_OpenJanelaIframe('','db_iframe_orcorgao','func_orcorgaopreorcdesp.php?pesquisa_chave='+document.form1.o58_orgao.value+'&funcao_js=parent.js_mostraorcorgao','Pesquisa',false);
  }
}
function js_mostraorcorgao(chave,erro){  
  document.form1.o40_descr.value = chave; 
  if(erro==true){ 
    document.form1.o58_orgao.focus(); 
    document.form1.o58_orgao.value = ''; 
  }else{
     document.form1.o58_unidade.value = '';
     document.form1.o41_descr.value   = '';
     //document.form1.submit();
	}

}
function js_mostraorcorgao1(chave1,chave2){	
  document.form1.o58_orgao.value   = chave1;
  document.form1.o58_orgao.dispatchEvent(new Event('change'));
  document.form1.o40_descr.value   = chave2;
  document.form1.o58_unidade.value = '';
  document.form1.o41_descr.value   = '';
  db_iframe_orcorgao.hide();
	//document.form1.submit();
}
function js_pesquisao58_unidade(mostra){
  if(mostra==true){
    js_OpenJanelaIframe('','db_iframe_orcunidade','func_orcunidadepd.php?<?=@$filtrar;?>funcao_js=parent.js_mostraorcunidade1|o41_unidade|o41_descr|nomeinst|o41_instit','Pesquisa',true);
  }else{
    js_OpenJanelaIframe('','db_iframe_orcunidade','func_orcunidadepd.php?<?=@$filtrar;?>pesquisa_chave='+document.form1.o58_unidade.value+'&funcao_js=parent.js_mostraorcunidade','Pesquisa',false);
  }
}
/*function js_mostraorcunidade(chave,erro){
  document.form1.o41_descr.value = chave; 
  if(erro==true){ 
    document.form1.o58_unidade.focus(); 
    document.form1.o58_unidade.value = ''; 
  }
}
function js_mostraorcunidade1(chave1,chave2){
  document.form1.o58_unidade.value = chave1;
  document.form1.o41_descr.value = chave2;
  db_iframe_orcunidade.hide();
}

function js_pesquisao58_unidade(mostra){
  if(mostra==true){
    js_OpenJanelaIframe('top.corpo','db_iframe_orcunidade','func_orcunidade.php?funcao_js=parent.js_mostraorcunidade1|o41_orgao|o41_descr','Pesquisa',true);
  }else{
    js_OpenJanelaIframe('top.corpo','db_iframe_orcunidade','func_orcunidade.php?pesquisa_chave='+document.form1.o58_unidade.value+'&funcao_js=parent.js_mostraorcunidade','Pesquisa',false);
  }
}
*/
function js_mostraorcunidade(chave,erro,chave2,chave3){
  document.form1.o41_descr.value   = chave; 
	if (chave2 != undefined){
    document.form1.nomeinst.value    = chave2;
    document.form1.o58_instit.value  = chave3;
	}
  if(erro==true){ 
    document.form1.o58_unidade.focus(); 
    document.form1.o58_unidade.value = ''; 
  }
}
function js_mostraorcunidade1(chave1,chave2,chave3,chave4){
  document.form1.o58_unidade.value = chave1;
  document.form1.o41_descr.value   = chave2;
  document.form1.nomeinst.value    = chave3;
  document.form1.o58_instit.value  = chave4;
  db_iframe_orcunidade.hide();
}
function js_pesquisao58_funcao(mostra){
  if(mostra==true){
    js_OpenJanelaIframe('','db_iframe_orcfuncao','func_orcfuncao.php?funcao_js=parent.js_mostraorcfuncao1|o52_funcao|o52_descr','Pesquisa',true);
  }else{
    js_OpenJanelaIframe('','db_iframe_orcfuncao','func_orcfuncao.php?pesquisa_chave='+document.form1.o58_funcao.value+'&funcao_js=parent.js_mostraorcfuncao','Pesquisa',false);
  }
}
function js_mostraorcfuncao(chave,erro){
  document.form1.o52_descr.value = chave; 
  if(erro==true){ 
    document.form1.o58_funcao.focus(); 
    document.form1.o58_funcao.value = ''; 
  }
}
function js_mostraorcfuncao1(chave1,chave2){
  document.form1.o58_funcao.value = chave1;
  document.form1.o52_descr.value = chave2;
  db_iframe_orcfuncao.hide();
}
function js_pesquisao58_subfuncao(mostra){
  if(mostra==true){
    js_OpenJanelaIframe('','db_iframe_orcsubfuncao','func_orcsubfuncao.php?funcao_js=parent.js_mostraorcsubfuncao1|o53_subfuncao|o53_descr','Pesquisa',true);
  }else{
    js_OpenJanelaIframe('','db_iframe_orcsubfuncao','func_orcsubfuncao.php?pesquisa_chave='+document.form1.o58_subfuncao.value+'&funcao_js=parent.js_mostraorcsubfuncao','Pesquisa',false);
  }
}
function js_mostraorcsubfuncao(chave,erro){
  document.form1.o53_descr.value = chave; 
  if(erro==true){ 
    document.form1.o58_subfuncao.focus(); 
			document.form1.o58_subfuncao.value = ''; 
  }
}
function js_mostraorcsubfuncao1(chave1,chave2){
  document.form1.o58_subfuncao.value = chave1;
  document.form1.o53_descr.value = chave2;
  db_iframe_orcsubfuncao.hide();
}
function js_pesquisao58_programa(mostra){
  if(mostra==true){
    js_OpenJanelaIframe('','db_iframe_orcprograma','func_orcprograma.php?funcao_js=parent.js_mostraorcprograma1|o54_programa|o54_descr&situacao=ppa','Pesquisa',true);
  }else{
    js_OpenJanelaIframe('','db_iframe_orcprograma','func_orcprograma.php?situacao=ppa&pesquisa_chave='+document.form1.o58_programa.value+'&funcao_js=parent.js_mostraorcprograma','Pesquisa',false);
  }
}
function js_mostraorcprograma(chave,erro){
  
  document.form1.o54_descr.value = chave; 
  if(erro==true){ 
    document.form1.o58_programa.focus(); 
    document.form1.o54_descr.value = ''; 
  }
}
function js_mostraorcprograma1(chave1,chave2){
  document.form1.o58_programa.value = chave1;
  document.form1.o54_descr.value = chave2;
  db_iframe_orcprograma.hide();
}
function js_pesquisao58_projativ(mostra){
  if(mostra==true){
    js_OpenJanelaIframe('','db_iframe_orcprojativ','func_orcprojativ.php?insti=1&situacao=ppa&funcao_js=parent.js_mostraorcprojativ1|o55_projativ|o55_descr','Pesquisa',true);
  }else{
    js_OpenJanelaIframe('','db_iframe_orcprojativ','func_orcprojativ.php?insti=1situacao=ppa&&pesquisa_chave='+document.form1.o58_projativ.value+'&funcao_js=parent.js_mostraorcprojativ','Pesquisa',false);
  }
}
function js_mostraorcprojativ(chave,erro){
  document.form1.o55_descr.value = chave; 
  if(erro==true){ 
    document.form1.o58_projativ.focus(); 
    document.form1.o58_projativ.value = ''; 
  }
}
function js_mostraorcprojativ1(chave1,chave2){
  document.form1.o58_projativ.value = chave1;
  document.form1.o55_descr.value = chave2;
  db_iframe_orcprojativ.hide();
}
function js_mostraorcprojativ(chave,erro){
  document.form1.o55_descr.value = chave; 
  if(erro==true){ 
    document.form1.o58_projativ.focus(); 
    document.form1.o58_projativ.value = ''; 
  }
}
function js_mostraorcprojativ1(chave1,chave2){
  document.form1.o58_projativ.value = chave1;
  document.form1.o55_descr.value = chave2;
  db_iframe_orcprojativ.hide();
}
function js_pesquisao58_codele(mostra){
  if(mostra==true){
    js_OpenJanelaIframe('','db_iframe_orcelemento','func_orcelemento.php?funcao_js=parent.js_mostraorcelemento1|o56_elemento|o56_descr&analitica=1','Pesquisa',true);
  }else{
    js_OpenJanelaIframe('','db_iframe_orcelemento','func_orcelemento.php?pesquisa_chave='+document.form1.o56_elemento.value+'&funcao_js=parent.js_mostraorcelemento&tipo_pesquisa=1&analitica=1','Pesquisa',false);
  }
}
function js_mostraorcelemento(chave,erro){
  document.form1.o56_descr.value = chave; 
  if(erro==true){ 
    document.form1.o56_elemento.focus(); 
    document.form1.o56_elemento.value = ''; 
  }
}
function js_mostraorcelemento1(chave1,chave2){
  document.form1.o56_elemento.value = chave1;
  document.form1.o56_descr.value = chave2;
   //document.form1.o56_elemento.dispatchEvent(new Event('change'));
  db_iframe_orcelemento.hide();
}
function js_pesquisao58_codigo(mostra){
  if(mostra==true){
    js_OpenJanelaIframe('','db_iframe_orctiporec','func_orctiporecdesp.php?funcao_js=parent.js_mostraorctiporec1|o15_codigo|o15_descr','Pesquisa',true);
  }else{
    js_OpenJanelaIframe('','db_iframe_orctiporec','func_orctiporecdesp.php?pesquisa_chave='+document.form1.o58_codigo.value+'&funcao_js=parent.js_mostraorctiporec','Pesquisa',false);
  }
}
function js_mostraorctiporec(chave,erro){
  document.form1.o15_descr.value = chave; 
  if(erro==true){ 
    document.form1.o58_codigo.focus(); 
    document.form1.o58_codigo.value = ''; 
  }
}
function js_mostraorctiporec1(chave1,chave2){
  document.form1.o58_codigo.value = chave1;
  document.form1.o15_descr.value = chave2;
  db_iframe_orctiporec.hide();
}

function js_pesquisao61_codigo(mostra){
  if(mostra==true){
    js_OpenJanelaIframe('','db_iframe_orctiporec','func_orctiporecdesp.php?funcao_js=parent.js_mostraorctiporec2|o15_codigo|o15_descr','Pesquisa',true);
  }else{
    js_OpenJanelaIframe('','db_iframe_orctiporec','func_orctiporecdesp.php?pesquisa_chave='+document.form1.o61_codigo.value+'&funcao_js=parent.js_mostraorctiporec3','Pesquisa',false);
  }
}
function js_mostraorctiporec3(chave,erro){
  document.form1.o15_contra_recurso.value = chave; 
  if(erro==true){ 
    document.form1.o61_codigo.focus(); 
    document.form1.o61_codigo.value = ''; 
  }
}

function js_mostraorctiporec2(chave1,chave2){
  document.form1.o61_codigo.value = chave1;
  document.form1.o15_contra_recurso.value = chave2;
  db_iframe_orctiporec.hide();
}

function js_pesquisao58_localizadorgastos(mostra){
  
  if(mostra==true){
    js_OpenJanelaIframe('',
                        'db_iframe_ppasubtitulolocalizadorgasto',
                        'func_ppasubtitulolocalizadorgasto.php?funcao_js=parent.js_mostrappasubtitulolocalizadorgasto1|o11_sequencial|o11_descricao',
                        'Pesquisa',true);
  }else{
     if(document.form1.o58_localizadorgastos.value != ''){ 
        js_OpenJanelaIframe('','db_iframe_ppasubtitulolocalizadorgasto','func_ppasubtitulolocalizadorgasto.php?pesquisa_chave='+document.form1.o58_localizadorgastos.value+'&funcao_js=parent.js_mostrappasubtitulolocalizadorgasto','Pesquisa',false);
     }else{
       document.form1.o11_descricao.value = ''; 
     }
  }
}


function js_pesquisao58_concarpeculiar(mostra){
  
  if(mostra==true){
    js_OpenJanelaIframe('',
                        'db_iframe_concarpeculiar',
                        'func_concarpeculiar.php?funcao_js=parent.js_mostraconcarpeculiar1|c58_sequencial|c58_descr',
                        'Pesquisa',true);
  }else{
     if(document.form1.o58_concarpeculiar.value != ''){ 
         js_OpenJanelaIframe('','db_iframe_ppasubtitulolocalizadorgasto','func_concarpeculiar.php?pesquisa_chave='+document.form1.o58_concarpeculiar.value.trim()+'&funcao_js=parent.js_mostraconcarpeculiar','Pesquisa',false);
     }else{
       document.form1.c58_descr.value = ''; 
     }
  }
}

function js_mostraconcarpeculiar(chave,erro){
  document.form1.c58_descr.value = chave; 
  if(erro==true){ 
    document.form1.o58_concarpeculiar.focus(); 
    document.form1.o58_concarpeculiar.value = ''; 
  }
}

function js_mostraconcarpeculiar1(chave1,chave2){
  document.form1.o58_concarpeculiar.value =chave1; 
  document.form1.c58_descr.value = chave2; 
  db_iframe_concarpeculiar.hide();
}



function js_mostrappasubtitulolocalizadorgasto(chave,erro){
  document.form1.o11_descricao.value = chave; 
  if(erro==true){ 
    document.form1.o58_localizadorgastos.focus(); 
    document.form1.o58_localizadorgastos.value = ''; 
  }
}
function js_mostrappasubtitulolocalizadorgasto1(chave1,chave2){
  document.form1.o58_localizadorgastos.value = chave1;
  document.form1.o11_descricao.value = chave2;
  db_iframe_ppasubtitulolocalizadorgasto.hide();
}

  function js_pesquisa(){
  //js_OpenJanelaIframe('','db_iframe_orcdotacao','func_preorcdesp.php?funcao_js=parent.js_preenchepesquisa|id|o50_estrutdespesa|o56_elemento','Pesquisa',true);
  js_OpenJanelaIframe('','db_iframe_orcdotacao','func_preorcdesp.php?funcao_js=parent.js_preenchepesquisa|id|o50_estrutdespesa|o56_elemento|o55_descr|o56_descr|o58_coddot|o58_instit|o58_anousu|nomeinst|o58_orgao|o40_descr|o58_unidade|o41_descr|o58_funcao|o52_descr|o58_subfuncao|o53_descr|o58_programa|o54_descr|o58_projativ|o58_codigo|o15_descr|o58_localizadorgastos|o11_descricao|o58_concarpeculiar|c58_descr|o58_valor','Pesquisa',true);
}

function js_pesquisa22(){  
  js_OpenJanelaIframe('','db_iframe_orcdotacao','func_preorcdesp22.php?funcao_js=parent.js_preenchepesquisa|id|o50_estrutdespesa|o56_elemento|o55_descr|o56_descr|o58_coddot|o58_instit|o58_anousu|nomeinst|o58_orgao|o40_descr|o58_unidade|o41_descr|o58_funcao|o52_descr|o58_subfuncao|o53_descr|o58_programa|o54_descr|o58_projativ|o58_codigo|o15_descr|o58_localizadorgastos|o11_descricao|o58_concarpeculiar|c58_descr|o58_valor','Pesquisa',true);
}

function js_preenchepesquisa(chave1){
  

  document.getElementById("id").value = arguments[0];
  document.getElementsByName("o50_estrutdespesa")[0].value = arguments[1];
  document.getElementsByName("o56_elemento")[0].value = arguments[2];
  document.getElementsByName("o55_descr")[0].value = arguments[3];
  document.getElementsByName("o56_descr")[0].value = arguments[4];
  document.getElementsByName("o58_coddot")[0].value = arguments[5];
  document.getElementsByName("o58_instit")[0].value = arguments[6];
  document.getElementsByName("o58_anousu")[0].value = arguments[7];
  document.getElementsByName("nomeinst")[0].value = arguments[8];
  document.getElementsByName("o58_orgao")[0].value = arguments[9];
  document.getElementsByName("o40_descr")[0].value = arguments[10];
  document.getElementsByName("o58_unidade")[0].value = arguments[11];
  document.getElementsByName("o41_descr")[0].value = arguments[12];
  document.getElementsByName("o58_funcao")[0].value = arguments[13];
  document.getElementsByName("o52_descr")[0].value = arguments[14];
  document.getElementsByName("o58_subfuncao")[0].value = arguments[15];
  document.getElementsByName("o53_descr")[0].value = arguments[16];
  document.getElementsByName("o58_programa")[0].value = arguments[17];
  document.getElementsByName("o54_descr")[0].value = arguments[18];
  document.getElementsByName("o58_projativ")[0].value = arguments[19];
  document.getElementsByName("o58_codigo")[0].value = arguments[20];
  document.getElementsByName("o15_descr")[0].value = arguments[21];
  document.getElementsByName("o58_localizadorgastos")[0].value = arguments[22];
  document.getElementsByName("o11_descricao")[0].value = arguments[23];
  document.getElementsByName("o58_concarpeculiar")[0].value = arguments[24];
  document.getElementsByName("c58_descr")[0].value = arguments[25];
  document.getElementsByName("o58_valor")[0].value = arguments[26];
  document.getElementById("db_opcao").value = "Alterar";
  document.getElementById("apagar").style.display = "";


  var zorgao = arguments[9];
  var zano = arguments[7];
  var zunidade = arguments[11];
  var zfuncao = arguments[13];
  var zsubfuncao = arguments[15];
  var zprograma = arguments[17];
  var zprojativ = arguments[19];
  var zelemento = arguments[2];
  var zrecurso = arguments[20];
  var zlocalizador = arguments[22];
  var zpeculiar = arguments[24];

  var zvalorantigo = arguments[26];

  var xhr = new XMLHttpRequest();

//ALTERNATIVO
/*
  xhr.onreadystatechange = function() {
  if (xhr.readyState === 4 && xhr.status === 200) {
    document.getElementById("td1").innerHTML = xhr.responseText;

    setTimeout(function() {
      var v1 = document.getElementById("zxvalorfixado").value;
      var v2 = document.getElementById("zsomatorio").value;
      var v3 = v2 - v1;

      document.getElementById("sdd").value = v2;
      document.getElementById("vtf").value = v1;

      // Cria os campos a26–a29 se não existirem
      if (!document.getElementById("a26")) {
        var container = document.createElement("fieldset");
        container.style.width = "400px";

        container.innerHTML = `
          <legend>Valores Previstos</legend>
          <b>2026: </b><input type="text" name="a26" id="a26" readonly style="background-color: #DEB887;"><br>
          <b>2027: </b><input type="text" name="a27" id="a27" readonly style="background-color: #DEB887;"><br>
          <b>2028: </b><input type="text" name="a28" id="a28" readonly style="background-color: #DEB887;"><br>
          <b>2029: </b><input type="text" name="a29" id="a29" readonly style="background-color: #DEB887;"><br>
        `;
        
        document.getElementById("td1").appendChild(container);
      }

      
      const v2026 = document.getElementById("v2026");
      const v2027 = document.getElementById("v2027");
      const v2028 = document.getElementById("v2028");
      const v2029 = document.getElementById("v2029");

      if (v2026 && v2027 && v2028 && v2029) {
        document.getElementById("a26").value = parseFloat(v2026.value).toFixed(2);
        document.getElementById("a27").value = parseFloat(v2027.value).toFixed(2);
        document.getElementById("a28").value = parseFloat(v2028.value).toFixed(2);
        document.getElementById("a29").value = parseFloat(v2029.value).toFixed(2);
      } else {
        console.warn("Um ou mais campos v2026 ~ v2029 não foram encontrados no DOM.");
      }

    }, 50);
  }
};
*/


xhr.onreadystatechange = function() {
  if (xhr.readyState === 4 && xhr.status === 200) {
    document.getElementById("td1").innerHTML = xhr.responseText;

    setTimeout(function() {
      var v1 = document.getElementById("zxvalorfixado").value;
      var v2 = document.getElementById("zsomatorio").value;
      var v3 = v2 - v1;
      
      document.getElementById("sdd").value = v2;
      document.getElementById("vtf").value = v1;
      document.getElementById("zvalorantigo").value = zvalorantigo;

      document.getElementById("a26").value = parseFloat(document.getElementById("v2026").value).toFixed(2);
      document.getElementById("a27").value = parseFloat(document.getElementById("v2027").value).toFixed(2);
      document.getElementById("a28").value = parseFloat(document.getElementById("v2028").value).toFixed(2);
      document.getElementById("a29").value = parseFloat(document.getElementById("v2029").value).toFixed(2);
    }, 50);
  }
};


xhr.open("GET", "preorcdespajax3.php?orgao=" + zorgao + 
         "&ano=" + zano +
         "&unidade=" + zunidade + 
         "&funcao=" + zfuncao + 
         "&subfuncao=" + zsubfuncao + 
         "&programa=" + zprograma + 
         "&projativ=" + zprojativ + 
         "&elemento=" + zelemento +
         "&recurso=" + zrecurso +
         "&localizador=" + zlocalizador +
         "&cpeculiar=" + zpeculiar,
         true);

xhr.send(null);

  
chave = '<?=db_getsession('DB_anousu')?>';
  db_iframe_orcdotacao.hide();
  <?
    if($db_opcao!=1){
      echo " location.href = '".basename($GLOBALS["HTTP_SERVER_VARS"]["PHP_SELF"])."?chavepesquisa='+chave+'&chavepesquisa1='+chave1";
    }
  ?>


} //fim preenche pesquisa



<?php if($avisa == "s") : ?>
  document.getElementById("id").value = "";
  document.getElementsByName("o50_estrutdespesa")[0].value = "";
  document.getElementsByName("o56_elemento")[0].value = "";
  document.getElementsByName("o55_descr")[0].value = "";
  document.getElementsByName("o56_descr")[0].value = "";
  document.getElementsByName("o58_coddot")[0].value = "";
  document.getElementsByName("o58_instit")[0].value = "";  
  document.getElementsByName("nomeinst")[0].value = "";
  document.getElementsByName("o58_orgao")[0].value = "";
  document.getElementsByName("o40_descr")[0].value = "";
  document.getElementsByName("o58_unidade")[0].value = "";
  document.getElementsByName("o41_descr")[0].value = "";
  document.getElementsByName("o58_funcao")[0].value = "";
  document.getElementsByName("o52_descr")[0].value = "";
  document.getElementsByName("o58_subfuncao")[0].value = "";
  document.getElementsByName("o53_descr")[0].value = "";
  document.getElementsByName("o58_programa")[0].value = "";
  document.getElementsByName("o54_descr")[0].value = "";
  document.getElementsByName("o58_projativ")[0].value = "";
  document.getElementsByName("o58_codigo")[0].value = "";
  document.getElementsByName("o15_descr")[0].value = "";
  document.getElementsByName("o58_localizadorgastos")[0].value = "";
  document.getElementsByName("o11_descricao")[0].value = "";
  document.getElementsByName("o58_concarpeculiar")[0].value = "";
  document.getElementsByName("c58_descr")[0].value = "";
  document.getElementsByName("o58_valor")[0].value = "";
<?php endif; ?>

document.getElementsByName("o50_estrutdespesa")[0].disabled = true;


var og = document.getElementById("o58_orgao");
var an = document.getElementById("o58_anousu");
og.addEventListener("change", function () {
  var xhr = new XMLHttpRequest();
  xhr.onreadystatechange = function () {
    if ((xhr.readyState === 4) && (xhr.status === 200)) {
      document.getElementById("td").innerHTML = xhr.responseText;
      inicializarPrevisaoListener();
    }
  };
  xhr.open("GET", "preorcdespajax.php?orgao=" + og.value + "&ano=" + an.value, true);
  xhr.send(null);
}, false);


document.addEventListener("DOMContentLoaded", function () {
  
  const inputValor = document.getElementById("o58_valor");
  const botaoOpcao = document.getElementById("db_opcao");
  const formulario = botaoOpcao.closest("form");
  

  function validarLimite() {
    const valorAntigo = parseFloat(document.getElementById("zvalorantigo").value);
    const sddAntigo = parseFloat(document.getElementById("zsomatorio").value);

    const vtotal = parseFloat(document.getElementById("vtf").value) || 0;
    let sdd = parseFloat(document.getElementById("sdd").value) || 0;
    const previsao = parseFloat(document.getElementById("o58_valor").value) || 0;
    
    let soma;
    if(document.getElementById("db_opcao").value == "Alterar"){
      let diferenca;

      if(previsao > valorAntigo){
        diferenca = previsao - valorAntigo;
        sdd = sddAntigo + diferenca;
        document.getElementById("sdd").value = sdd;
        soma = previsao; //+ sdd;
      }else{        
        diferenca = valorAntigo - previsao;        
        sdd = sddAntigo - diferenca;
        document.getElementById("sdd").value = sdd;
        soma = previsao; //+ sdd;
      }
    }
    console.log("***************");
    console.log("Soma: " + soma);
    console.log("Vtotal: " + vtotal);
    console.log("***************");
    
    
    if(document.getElementById("o58_anousu").value >= 2026){      
      if (soma > vtotal){
        console.log("Soma: " + soma);
        console.log("Vtotal: " + vtotal);
        alert("Valor Limite Ultrapassado. Favor entrar em contato com a Seplag para a liberação.");
        botaoOpcao.disabled = true;
        return false;
      } else {
        botaoOpcao.disabled = false;
        return true;
      }
    }
  }//fim da função
  
  inputValor.addEventListener("blur", validarLimite);
  
  formulario.addEventListener("submit", function (event) {
    /*if (!validarLimite()) {
      event.preventDefault(); // Bloqueia envio
    }*/
  });
});


function inicializarPrevisaoListener() {
  const o58_valor = document.getElementById('o58_valor');
  const sdd = document.getElementById('sdd');
  const fixado = document.getElementById("vtf");

  if (!o58_valor || !sdd || !fixado) {
    console.warn("Campos ainda não carregados no DOM.");
    return;
  }

  o58_valor.addEventListener('input', function () {
    const previsao = parseFloat(o58_valor.value.replace(',', '.')) || 0;
    const somatoriaAtual = parseFloat(sdd.getAttribute('data-original')) || 0;
    const totalfixado = parseFloat(fixado.value.replace(',', '.')) || 0;

    const novaSomatoria = previsao + somatoriaAtual;

    if (novaSomatoria > totalfixado) {
      alert("Valor Limite Ultrapassado. Favor entrar em contato com a Seplag para a liberação.");
      o58_valor.value = "";
      sdd.value = somatoriaAtual.toFixed(2);
      o58_valor.focus();
      return;
    }

    sdd.value = novaSomatoria.toFixed(2);
  });
  
  const somatoriaInicial = parseFloat(sdd.value.replace(',', '.')) || 0;
  sdd.setAttribute('data-original', somatoriaInicial);
}

</script>
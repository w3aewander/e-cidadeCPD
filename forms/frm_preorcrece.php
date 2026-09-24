<?


//MODULO: orcamento
$clorcreceita->rotulo->label();
$clrotulo = new rotulocampo;
$clrotulo->label("o57_fonte");
$clrotulo->label("o57_descr");
$clrotulo->label("o15_descr");
$clrotulo->label("nomeinst");
$clrotulo->label("c58_descr");

$anousu = db_getsession("DB_anousu");

$formatarEmentario = 'receita_int';
if (EMENTARIO_RECEITA) {
    $formatarEmentario = 'ementario_receita';
}

if(isset($chavepesquisa)){
    $o50_estrutreceita=db_formatar($o50_estrutreceita,$formatarEmentario);
}
if((isset($atualizar) || isset($o50_estrutreceita)) && empty($incluir)&& empty($alterar)&& empty($excluir) && empty($chavepesquisa)){
    $matriz= split("\.",$o50_estrutreceita);
    $inicia=false;//variavel que indica que o nivel não tem mais filhos
    $tam=(count($matriz)-1);
    $codigos='';
    for($i=$tam; $i>=0; $i--){
        $codigo='';//monta os codigos para a pesquisa
        if($matriz[$i]!="0" || $inicia==true){
            $inicia=true;
            for($x=$i; $x>=0; $x--){
                $codigo=$matriz[$x].$codigo;
            }
        }
        if($inicia==true){
            break;
        }
    }
    if ($anousu > 2007){
        $campo_concarpeculiar = " and o70_concarpeculiar = '$o70_concarpeculiar'";
    } else {
        $campo_concarpeculiar = "";
    }
    $taman=strlen($codigo);
    $clorcfontes->sql_record($clorcfontes->sql_query(null,null,"o57_fonte",'',"substr(o57_fonte,1,$taman)='$codigo' and o57_anousu = $anousu"));
    $result01 = $clorcreceita->sql_record($clorcreceita->sql_query(null,null,"o70_codrec as codrec",'',
        "o70_anousu=".db_getsession('DB_anousu')." 
                   and o57_fonte='".str_replace(".","",$o50_estrutreceita) ."'

                   $campo_concarpeculiar"));

    if($clorcfontes->numrows>1){
        $negado=true;
    }else if($clorcreceita->numrows>0){
        db_fieldsmemory($result01,0);
        if(isset($o70_codrec) && $o70_codrec!=$codrec){
            $cadastrado = "O código da fonte já foi cadastrado!";
        }else if(empty($o70_codrec)){
            $cadastrado = "O código da fonte já foi cadastrado!";
        }
    }
}
?>
<style>
    .cabec{
        text-align: center;
        font-size: 10px;
        font-weight: bold;
        background-color:#aacccc ;
        color: darkblue;

    }
    .corpo {
        background-color:#ccddcc;
        text-align: center;
    }
</style>
<?
if($db_opcao==1){
    $pg="preorcrece.php";
}else if($db_opcao==2 || $db_opcao==22){
    $pg="preorcrece.php";
}else{
    $pg="preorcrece.php";
}
?>
<form name="form1" method="post" action="<?=$pg?>">
    <center>
        <?
        //  exit;
        ?>
        <table border="0">
            <tr>
                <td nowrap title="<?=@$To70_anousu?>">
                    <?=@$Lo70_anousu?>
                </td>
                <td>
                    <?
                    //$o70_anousu = db_getsession('DB_anousu');
                    $o70_anousu = $anonovo;
                    db_input('o70_anousu',4,$Io70_anousu,true,'text',3);
                    ?>
                </td>
            </tr>
            <tr>
                <td nowrap title="<?=@$To70_codrec?>">
                    <?=@$Lo70_codrec?>
                </td>
                <td>
                    <?
                    db_input('o70_codrec',6,$Io70_codrec,true,'text',3)
                    ?>
                </td>
            </tr>
            <tr>
                <td nowrap title="<?=@$To70_codfon?>">
                    <?
                    db_ancora(@$Lo70_codfon,"js_pesquisao70_codfon(true);",$db_opcao);
                    ?>
                </td>
                <td colspan='2'>
                    <?
                    $clestrutura->funcao_onchange  = "js_pesquisao70_codfon(false);";
                    $clestrutura->autocompletar = true;
                    $clestrutura->mascara = false;
                    $clestrutura->input   = true;
                    $clestrutura->size    = 22;
                    $clestrutura->db_opcao= $db_opcao;
                    $clestrutura->estrutura('o50_estrutreceita');

                    db_input('o57_descr',40,$Io57_descr,true,'text',3,'');
                    ?>
                </td>
            </tr>
            <tr>
<td colspan='2' align='center'>
<?
    if((isset($atualizar) || isset($o50_estrutreceita)) && empty($cadastrado)&& empty($negado)){
        $matriz= split("\.",$o50_estrutreceita);
        $inicia=false;//variavel que indica que o nivel não tem mais filhos
        $tam=(count($matriz)-1);
        $codigos='';
        
        for($i=$tam; $i>=0; $i--){
            $codigo='';//monta os codigos para a pesquisa
            
            if($matriz[$i]!="0" || $inicia==true){
                $inicia=true;
                for($x=$i; $x>=0; $x--){
                    $codigo=$matriz[$x].$codigo;
                }
                for($y=strlen($codigo); $y<15; $y++){
                    $codigo=$codigo."0";
                }
            }
            
            if($inicia==true){
                $codigos=$codigo."#".$codigos;
            }
        }
        
        $matriz02= split("#",$codigos);
        $tam=count($matriz02);
        $espaco=3;
        $esp='';
        for($i=0; $i<$tam; $i++){
            if($matriz02[$i]==''){
                continue;
            }

            for($s=0; $s<$espaco; $s++){
                $esp=$esp."&nbsp;";
            }
            
            $result=$clorcfontes->sql_record($clorcfontes->sql_query_file(null,null,'o57_fonte,o57_descr','',"o57_fonte='".$matriz02[$i]."' and o57_anousu = ".db_getsession("DB_anousu")));
            
            if($clorcfontes->numrows>0){
                db_fieldsmemory($result,0);
                if(empty($prim)){
                    echo"  
	    <tr class='desaparece'>
	      <td  align='left'><b>Detalhamento:</b></td>
	      <td><small>".db_formatar($o57_fonte,$formatarEmentario)."</small></td>
	      <td><small>$esp $o57_descr</small></td>
	    </tr>
	   ";
                                    $prim="false";
                                }else{
                                    echo "
		 <tr class='desaparece'>
		  <td>&nbsp;</td>
		  <td><small>".db_formatar($o57_fonte,$formatarEmentario)."</small></td>
		  <td><small>$esp $o57_descr</small></td>
		</tr> 
	    ";
                                }
                            }else{
                                $nops=true;
                                if(empty($prim)){
                                    echo"  
	    <tr class='desaparece'>
	      <td  align='left'><b>Detalhamento:</b></td>
	      <td><small> ".db_formatar($matriz02[$i],$formatarEmentario)."</small></td>
	      <td><small>$esp Não encontrado</small></td>
	    </tr>
	   ";
                                    $prim="false";
                                }else{
                                    echo "
	       <tr class='desaparece'>
		<td>&nbsp;</td>
		<td><small> ".db_formatar($matriz02[$i],$formatarEmentario)."</small></td>
		<td><small>$esp Não encontrado</small></td>
	      </tr> 
	  ";
                                }
                            }
                        }
                    }
                    ?>
            <tr>
                <td nowrap title="<?=@$To70_codigo?>">
                    <?
                    db_ancora(@$Lo70_codigo,"js_pesquisao70_codigo(true);",$db_opcao);
                    ?>
                </td>
                <td colspan='2'>
                    <?
                    db_input('o70_codigo',4,$Io70_codigo,true,'text',$db_opcao," onchange='js_pesquisao70_codigo(false);'");
                    db_input('o15_descr',30,$Io15_descr,true,'text',3,'');
                    ?>
                </td>
            </tr>
            <tr>
                <td nowrap title="<?=@$To70_valor?>">
                    <?=@$Lo70_valor?>
                </td>
                <td>
                    <?
                    db_input('o70_valor',15,$Io70_valor,true,'text',$db_opcao,"")
                    ?>
                </td>
            </tr>
            <tr>
                <td nowrap title="<?=@$To70_reclan?>">
                    <?=@$Lo70_reclan?>
                </td>
                <td>
                    <?
                    $x = array("f"=>"NAO","t"=>"SIM");
                    db_select('o70_reclan',$x,true,$db_opcao,"");
                    ?>
                </td>
            </tr>
            <?
            if ($anousu > 2007){
                ?>
                <tr>
                    <td nowrap title="<?=@$To70_concarpeculiar?>"><?
                        db_ancora(@$Lo70_concarpeculiar,"js_pesquisao70_concarpeculiar(true);",$db_opcao);
                        ?></td>
                    <td colspan="2">
                        <?
                        db_input("o70_concarpeculiar",10,$Io70_concarpeculiar,true,"text",$db_opcao,"onChange='js_pesquisao70_concarpeculiar(false);'");
                        db_input("c58_descr",50,0,true,"text",3);
                        ?>
                    </td>
                </tr>
                <?
            } else {
                $o70_concarpeculiar = 0;
                db_input("o70_concarpeculiar",10,0,true,"hidden",3,"");
            }

            $o70_instit=db_getsession('DB_instit');
            db_input('o70_instit',2,$Io70_instit,true,'hidden',$db_opcao);
            ?>

            <tr>
                <td nowrap title="Unidade Orçamentária">
                <?
                    db_ancora("Unidade Orçamentária","js_pesquisa_orgaounidade(true);",$db_opcao);
                ?>
                </td>
                <td colspan="2"> 
                <?
                    db_input('codtrib',4,$Icodtrib,true,'text',3,"onChange=js_pesquisa_orgaounidade(true);");
                    db_input('orgaounid',70,0,true,'text',3,"");
                ?>
                </td>
            </tr>

            <tr>
                <td class="bold"><label for="esferaOrcamentaria">Esfera Orçamentária:</label></td>            

                <td>
                    <?php 
                        if (empty($o70_esferaorcamentaria)) {
                            $o70_esferaorcamentaria = '';
                        }
                    ?>

                    <select name="o70_esferaorcamentaria" id="o70_esferaorcamentaria">
                        <option value="0">Selecione</option>
                        <option <?php echo $o70_esferaorcamentaria  == '10' ? 'selected' : '' ?> value="10">F - Orçamento Fiscal</option>
                        <option <?php echo $o70_esferaorcamentaria  == '20' ? 'selected' : '' ?> value="20">S - Orçamento da Seguridade Social</option>
                        <option <?php echo $o70_esferaorcamentaria  == '30' ? 'selected' : '' ?> value="30">I - Orçamento de Investimento</option>                        
                    </select>
                </td>
            </tr>

            <tr>
                <td colspan='3' align='center'>
                    <?
                    $disa='';
                    if(isset($nops)){
                        $disa= " disabled ";
                    }
                    ?>
<input name="<?=($db_opcao==1?"incluir":($db_opcao==2||$db_opcao==22?"alterar":"excluir"))?>" type="submit" id="db_opcao" value="<?=($db_opcao==1?"Incluir":($db_opcao==2||$db_opcao==22?"Alterar":"Excluir"))?>" <?=($db_botao==false?"disabled":"")?> <?=$disa?> >

<input type="submit" name="apagar" id="apagar" value="Excluir" style="display: none">


<input name="pesquisar" type="button" id="pesquisar" value="Pesquisar" onclick="js_pesquisa22();" >
<?php $msgbtn = "Incluir {$anonovo}"; ?>

<?php if(db_getsession("DB_id_usuario") == 1 || db_getsession("DB_id_usuario") == 370 || db_getsession("DB_id_usuario") == 37) : ?>

<input name="i2021" type="submit" id="i2021" value="<?=$msgbtn?>">
<?php else: ?>
<input name="i2021" type="submit" id="i2021" value="<?=$msgbtn?>" disabled>
<?php endif; ?>

<?php if(db_getsession("DB_instit") == 1) : ?>
<input name="relatorio" type="button" id="relatorio" value="Relatório" onclick="imprimeTudo()">
<input name="csv" type="button" id="csv" value="CSV" onclick="imprimeTudoCsv()">
<?php else : ?>
<input name="relatorio" type="button" id="relatorio" value="Relatório" onclick="imprime()">
<input name="csv" type="button" id="csv" value="CSV" onclick="imprimeCsv()">
<?php endif; ?>

                </td>
            </tr>
        </table>
    </center>
<input type="hidden" name="id" id="id" value="">

<?php /* ?>
<?php if(db_getsession("DB_id_usuario") == 1) : ?>
  <a href="historicopreviasr.php">Histórico de Alterações</a>
<?php endif; ?>
<?php */ ?>

</form>
<?php if(db_getsession("DB_instit") == 1) : ?>

<div style="width:500px">
<fieldset><legend>Instituições para o relatório</legend>
<!--<input checked type="checkbox" name="checai" value="1">1-->
<!--<input type="checkbox" name="checai" value="96">96-->



<?php foreach ($instituicoes as $ins) :  ?>
<input <?= ($ins['o70_instit'] == 1) ? "checked" : "";?> type="checkbox" name="checai" value="<?=$ins['o70_instit'];?>"><?=$ins['o70_instit'];?>
<?php endforeach; ?>
<input type="checkbox" id="tudo" onclick="marca(this);">Todos
</fieldset>
</div>
<?php endif; ?>
<script>
    function imprime(){
    //jan = window.open('relprevrec.php','width='+(screen.availWidth-5)+',height='+(screen.availHeight-40)+',scrollbars=1,location=0 ');
    jan = window.open("relprevrec.php", "Relatório", "about:blank");
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
        
    //window.open('relprevrec.php?inst='+inst+'','width='+(screen.availWidth-5)+',height='+(screen.availHeight-40)+',scrollbars=1,location=0 ');    
    jan = window.open("relprevrec.php?inst="+inst+"", "Relatório", "about:blank");  
    jan.moveTo(0,0);
  }

  function imprimeCsv(){    
    jan = window.open("relprevrec.php?tipo=csv", "Relatório", "about:blank");  
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
    jan = window.open("relprevrec.php?tipo=csv&inst="+inst+"", "Relatório", "about:blank");  
    jan.moveTo(0,0);
  }

  function marca(x){
     var caixas = document.querySelectorAll('input[type="checkbox"]');
    for (var i = 0; i < caixas.length; i++) {
        if (caixas[i] != x)
            caixas[i].checked = x.checked;
    }
  }
    <?
    if(isset($nops)){
    ?>
    alert('Inclusão não permitida. Pois existe um nivel que não foi encontrado!');
    <?
    }
    ?>
    function js_pesquisao70_concarpeculiar(mostra){
      if (mostra==true) {

        js_OpenJanelaIframe('CurrentWindow.corpo','db_iframe_concarpeculiar',
          'func_concarpeculiar.php?funcao_js=parent.js_mostraconcarpeculiar1|c58_sequencial|c58_descr',
          'Pesquisa',true);
      }else{
        if(document.form1.o70_concarpeculiar.value != ''){
          js_OpenJanelaIframe('CurrentWindow.corpo','db_iframe_concarpeculiar',
            'func_concarpeculiar.php?pesquisa_chave='+document.form1.o70_concarpeculiar.value+
            '&funcao_js=parent.js_mostraconcarpeculiar',
            'Pesquisa',false);
        }else{
          document.form1.c58_descr.value = '';
        }
      }
    }
    function js_mostraconcarpeculiar(chave,erro){
      document.form1.c58_descr.value = chave;
      if(erro==true){
        document.form1.o70_concarpeculiar.focus();
        document.form1.o70_concarpeculiar.value = '';
      }
    }
    function js_mostraconcarpeculiar1(chave1,chave2){
      document.form1.o70_concarpeculiar.value = chave1;
      document.form1.c58_descr.value          = chave2;
      db_iframe_concarpeculiar.hide();
    }
    function js_pesquisao70_codfon(mostra){
      if(mostra==true){
        js_OpenJanelaIframe('CurrentWindow.corpo','db_iframe_orcfontes','func_orcfontes.php?funcao_js=parent.js_mostraorcfontes1|o57_fonte|o57_descr','Pesquisa',true);
      }else{
        fonte=document.form1.o50_estrutreceita.value;
        while(fonte.search(/\./)!='-1'){
          fonte=fonte.replace(/\./,'');
        }
        if(fonte!=''){
          js_OpenJanelaIframe('CurrentWindow.corpo','db_iframe_orcfontes','func_orcfontes.php?pesquisa_chave='+fonte+'&funcao_js=parent.js_mostraorcfontes','Pesquisa',false);
        }else{
          document.form1.o50_estrutreceita.value='';
        }
      }
    }
    function js_atualiza(){
      obj=document.createElement('input');
      obj.setAttribute('name','atualizar');
      obj.setAttribute('type','hidden');
      obj.setAttribute('value',"atualizar");
      document.form1.appendChild(obj);
      document.form1.submit();
    }
    function js_mostraorcfontes(chave,erro){
      document.form1.o57_descr.value = chave;
      if(erro==true){
        document.form1.o50_estrutreceita.focus();
        //document.form1.o50_estrutreceita.value = '';
        js_atualiza();
      }else{
        js_atualiza();
      }
    }
    function js_mostraorcfontes1(chave1,chave2){
      db_iframe_orcfontes.hide();
      document.form1.o50_estrutreceita.value = chave1;
      document.form1.o57_descr.value = chave2;
      js_mascara02_o50_estrutreceita(chave1);
      js_atualiza();
    }

    function js_pesquisao70_codigo(mostra){
      if(mostra==true){
        js_OpenJanelaIframe('CurrentWindow.corpo','db_iframe_orctiporec','func_orctiporec.php?funcao_js=parent.js_mostraorctiporec1|o15_codigo|o15_descr','Pesquisa',true);
      }else{
        js_OpenJanelaIframe('CurrentWindow.corpo','db_iframe_orctiporec','func_orctiporec.php?pesquisa_chave='+document.form1.o70_codigo.value+'&funcao_js=parent.js_mostraorctiporec','Pesquisa',false);
      }
    }
    function js_mostraorctiporec(chave,erro){
      document.form1.o15_descr.value = chave;
      if(erro==true){
        document.form1.o70_codigo.focus();
        document.form1.o70_codigo.value = '';
      }
    }
    function js_mostraorctiporec1(chave1,chave2){
      document.form1.o70_codigo.value = chave1;
      document.form1.o15_descr.value = chave2;
      db_iframe_orctiporec.hide();
    }
    function js_pesquisa(){
      js_OpenJanelaIframe('','db_iframe_orcreceita','func_preorcrece.php?funcao_js=parent.js_preenchepesquisa|id|o70_anousu|o70_codrec|o50_estrutreceita|o57_descr|o70_codigo|o15_descr|o70_valor|o70_reclan|o70_concarpeculiar|c58_descr|o70_instit|o70_orcorgao|o70_orcunidade|o70_esferaorcamentaria','Pesquisa',true);
    }
    function js_pesquisa22(){
      js_OpenJanelaIframe('','db_iframe_orcreceita','func_preorcrece22.php?funcao_js=parent.js_preenchepesquisa|id|o70_anousu|o70_codrec|o50_estrutreceita|o57_descr|o70_codigo|o15_descr|o70_valor|o70_reclan|o70_concarpeculiar|c58_descr|o70_instit|o70_orcorgao|o70_orcunidade|o70_esferaorcamentaria','Pesquisa',true);
    }
    function js_preenchepesquisa(chave,chave1){
      db_iframe_orcreceita.hide(); 
      var a12, a13;
      document.getElementById("id").value = arguments[0];
      document.getElementsByName("o70_anousu")[0].value = arguments[1];
      document.getElementsByName("o70_codrec")[0].value = arguments[2];
      document.getElementsByName("o50_estrutreceita")[0].value = arguments[3];
      document.getElementsByName("o57_descr")[0].value = arguments[4];
      document.getElementsByName("o70_codigo")[0].value = arguments[5];
      document.getElementsByName("o15_descr")[0].value = arguments[6];
      document.getElementsByName("o70_valor")[0].value = arguments[7];
      document.getElementsByName("o70_reclan")[0].value = arguments[8];
      document.getElementsByName("o70_concarpeculiar")[0].value = arguments[9];
      document.getElementsByName("c58_descr")[0].value = arguments[10];
      console.log("================");
      console.log(arguments[12].length);
      //console.log(arguments[13].length);
      if(arguments[12].length == 1){
        a12 = "0"+arguments[12];
      }else{
        a12 = arguments[12];
      }
      if(arguments[13].length == 1){
        a13 = "0"+arguments[13];
      }else{
        a13 = arguments[13];
      }
      console.log(a12);
      console.log("================");
      document.getElementsByName("codtrib")[0].value = a12 + a13;
      //document.getElementsByName("o70_orcunidade")[0].value = arguments[13];
      document.getElementsByName("o70_esferaorcamentaria")[0].value = arguments[14];


      document.getElementById("db_opcao").value = "Alterar";
      document.getElementById("apagar").style.display = "";
      
        <?
        if($db_opcao!=1){
            echo " location.href = '".basename($GLOBALS["HTTP_SERVER_VARS"]["PHP_SELF"])."?chavepesquisa='+chave+'&chavepesquisa1='+chave1";
        }
        ?>
    }
    <?
    if(isset($chavepesquisa)){

        echo "js_mascara02_o50_estrutreceita(document.form1.o50_estrutreceita.value);\n";
    }
    if(isset($cadastrado)){
        echo "
   document.form1.o50_estrutreceita.value='';\n
   document.form1.o57_descr.value='';\n
   alert('Fonte já cadastrada!');\n
  ";
    }
    if(isset($negado)){
        echo "
   document.form1.o50_estrutreceita.value='';
   document.form1.o57_descr.value='';
   alert('Selecione o último nível!');\n
   js_OpenJanelaIframe('CurrentWindow.corpo','db_iframe_orcfontes','func_orcfontes.php?chave_o57_fonte=$codigo&funcao_js=parent.js_mostraorcfontes1|o57_fonte|o57_descr','Pesquisa',true);
  ";
    }
    ?>

    <?php if($avisa == "s") : ?>
        document.getElementById("id").value = "";
        
        document.getElementsByName("o70_codrec")[0].value = "";
        document.getElementsByName("o50_estrutreceita")[0].value = "";
        //document.getElementsByName("o50_estrutreceita")[0].onchange();
        document.getElementsByName("o57_descr")[0].value = "";
        document.getElementsByName("o70_codigo")[0].value = "";
        document.getElementsByName("o15_descr")[0].value = "";
        document.getElementsByName("o70_valor")[0].value = "";
        document.getElementsByName("o70_reclan")[0].value = "";
        document.getElementsByName("o70_concarpeculiar")[0].value = "";
        document.getElementsByName("c58_descr")[0].value = "";

        var sumir = document.getElementsByClassName("desaparece");
        //console.log(sumir[1]);
        //sumir[1].style.display = 'none';
        //console.log(sumir.length);

        for (var i = sumir.length - 1; i >= 0; i--) {
            sumir[i].style.display = 'none';
        }
    <?php endif; ?>

function js_pesquisa_orgaounidade(mostra){
        if(mostra==true){
            js_OpenJanelaIframe('', 'db_iframe_orcunidade', 'func_db_config_orcunidade.php?funcao_js=parent.js_mostraorcunidade1|o41_orgao|o41_unidade|o40_descr|o41_descr','Pesquisa',true);
        }
    }

    function js_mostraorcunidade(chave,erro){
        document.form1.o40_descr.value = chave; 
        if(erro==true){ 
            document.form1.o41_orgao.focus(); 
            document.form1.o41_orgao.value = ''; 
        }
    }
    
    function js_mostraorcunidade1(chave1,chave2,chave3,chave4){
        var strOrgao    = chave1;
        var strUnidade  = chave2;
        var iOrgao    = parseInt(chave1);
        var iUnidade  = parseInt(chave2);
        if(iOrgao < 10){
            strOrgao = '0'+strOrgao;
        }
        if(iUnidade < 10){
            strUnidade = '0'+strUnidade;
        }  
        var codtrib   = strOrgao+strUnidade;
        var orgaounid = chave3+' / '+chave4;
        document.form1.codtrib.value = codtrib;
        document.form1.orgaounid.value = orgaounid;
        db_iframe_orcunidade.hide();
}

</script>
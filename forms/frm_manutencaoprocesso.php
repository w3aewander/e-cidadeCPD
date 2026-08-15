<?


//MODULO: protocolo
$clprotprocesso->rotulo->label();
$clprotpro = new cl_protprocesso;
$clrotulo = new rotulocampo;

$clrotulo->label("p51_descr");
$clrotulo->label("z01_nome");
$clrotulo->label("descrdepto");

?>
<script>
function js_testa() {
  document.form1.btnalterar.value = '2';
}


</script>
<fieldset>
<legend><b>Dados Processo</b></legend>
<center>
<table border="0">
  <tr>
    <td nowrap title="Usuário">
      <b>Usuário:</b>
    </td>
    <td>
     <?
       $sql = "select nome from db_usuarios where id_usuario = ".db_getsession("DB_id_usuario");
       echo pg_result(db_query($sql),0,"nome");
     ?>
    </td>
  </tr>
  <tr>
    <td nowrap title="Usuário">
      <b>Departamento:</b>
    </td>
    <td>
     <?
       $sql = "select descrdepto from db_depart where coddepto = ".db_getsession("DB_coddepto");
       echo pg_result(db_query($sql),0,"descrdepto");
     ?>
    </td>
  </tr>
  <tr>
    <td nowrap title="<?=@$Tp58_codproc?>">
       <?=@$Lp58_codproc; ?>
    </td>
    <td>
    <?
      db_input('p58_codproc',12,$Ip58_codproc,true,'text',3,"");
    ?>
  </td>
  </tr>
  <tr>
    <td nowrap title="<?=@$Tp58_numero?>">
       <?=@$Lp58_numero; ?>
    </td>
    <td>
    <?
      db_input('p58_numero',12,$Ip58_numero,true,'text',3,"");
    ?>
  </td>
  </tr>
  <tr>
    <td nowrap title="<?=@$Tp58_dtproc;?>">
    <?=@$Lp58_dtproc;?>
    </td>
    <td>
  <?
    if ($db_opcao==3){
      db_inputdata('p58_dtproc',@$p58_dtproc_dia,@$p58_dtproc_mes,@$p58_dtproc_ano,false,'text',2,"","p58_dtproc");
    }else{
      db_inputdata('p58_dtproc',@$p58_dtproc_dia,@$p58_dtproc_mes,@$p58_dtproc_ano,false,'text',$db_opcao,"","p58_dtproc");
    }
  ?>
   </td>
  </tr>
  <tr>
    <td nowrap title="<?=@$Tp58_hora;?>">
    <?=@$Lp58_hora;?>
    </td>
    <td>
  <?
  if($db_opcao == 1){
    $p58_hora = db_hora();
    db_input('p58_hora',7,@$Ip58_hora,true,'text','3','');
  }else
      db_input('p58_hora',7,$Ip58_hora,true,'text',3);
  ?>
   </td>
  </tr>


<?
  $op_tip = 1;
  $pesq_p58_codigo1 = "js_pesquisap58_codigo(true)";
  $pesq_p58_codigo2 = "js_pesquisap58_codigo(false)";
  //$db_opcao=2;
  if($db_opcao==2){
    $op_tip = 2;
    if(isset($p58_codproc) && trim($p58_codproc)!=""){
      $sql_tipo = " select p61_codproc as processo1,
                           p63_codproc as processo2,
                           p67_codproc as processo3
                      from protprocesso
                           left join procandam        on procandam.p61_codproc        = protprocesso.p58_codproc
                           left join proctransferproc on proctransferproc.p63_codproc = protprocesso.p58_codproc
                           left join procarquiv       on procarquiv.p67_codproc       = protprocesso.p58_codproc
                     where protprocesso.p58_codproc = {$p58_codproc}
                       and procandam.p61_codproc is null
                       and proctransferproc.p63_codproc is null
                       and procarquiv.p67_codproc is null ";

      $result_tipo = $clprotpro->sql_record($sql_tipo);
      if($clprotpro->numrows==0){
        $op_tip = 3;
      }
    }
  }else if($db_opcao==3){
    $op_tip = 3;
  }
?>

  <tr>
    <td nowrap title="<?=@$Tp58_codigo?>">
       <?//=db_ancora(@$Lp58_codigo,"$pesq_p58_codigo1",$op_tip); ?>
       <?=db_ancora(@$Lp58_codigo,"$pesq_p58_codigo1",2); ?>
    </td>
    <td>
<?
db_input('p58_codigo',5,$Ip58_codigo,true,'text',2," onchange='$pesq_p58_codigo2'")
?>
       <?
db_input('p51_descr',40,$Ip51_descr,true,'text',3,'');
if($db_opcao == 1){
  $p58_hora = db_hora();
  db_input('p58_hora',60,@$Ip58_hora,true,'hidden','','');
}
       ?>
    </td>
  </tr>







  <tr>
    <td nowrap title="<?=@$Tp58_numcgm?>">
       <?
         db_ancora(@$Lp58_numcgm,"js_pesquisap58_numcgm(true);",2);
       ?>
    </td>
    <td>
    <?
       db_input('p58_numcgm',5,$Ip58_numcgm,true,'text',2," onchange='js_pesquisap58_numcgm(false);'");

       db_input('z01_nome',40,$Iz01_nome,true,'text',3,'');
    ?>
    </td>
  </tr>
  <tr>
    <td nowrap title="<?=@$Tp58_requer?>">
       <?=@$Lp58_requer?>
    </td>
    <td>
    <?
      db_input('p58_requer',50,$Ip58_requer,true,'text',$db_opcao,"")
    ?>
    </td>
  </tr>
  <tr>
    <td nowrap title="<?=$Tp58_obs?>" colspan='2'>
      <fieldset class="separator">
        <legend><?=$Lp58_obs?></legend>

        <?php
          //db_textarea('p58_obs',5,80,$Ip58_obs,true,'text',$db_opcao,"")
        db_textarea('p58_obs',5,80,$Ip58_obs,true,'text',2,"")
        ?>
      </fieldset>
    </td>
  </tr>
 
  <tr>
    <td colspan="3" valign='top'>
    <?
//    include(modification("classes/db_procdoctipo_classe.php"));
    $cldoc = new cl_procdoctipo;
    $res   = $cldoc->sql_record($cldoc->sql_query(@$p58_codigo,"","p56_coddoc,p56_descr"));

    if ($cldoc->numrows > 0) {
      echo "<fieldset>";
      if ($db_opcao == 1) {
        if (@$p58_codigo != "") {
//          include(modification("classes/db_procdoctipo_classe.php"));
//          $cldoc = new cl_procdoctipo;
//          $res = $cldoc->sql_record($cldoc->sql_query($p58_codigo,"","p56_coddoc,p56_descr"));
          if ($cldoc->numrows > 0) {
            echo "<b>DOCUMENTOS</b><br>";
            $ndocs = "";
            for ($x=0; $x<$cldoc->numrows; $x++) {
              db_fieldsmemory($res,$x);
              echo "<input type='checkbox' name='doc$x' onClick='js_valor()' value='$p56_coddoc'><b>$p56_descr</b><br>";
              $ndocs .= $p56_coddoc . "#";
            }
          }
        }
      } else if ($db_opcao == 2) {

        if (isset($btnalterar) && $btnalterar==2) {
          if (@$p58_codproc != "") {
            $sqldoc  = "select coalesce(p81_doc, false) as p81_doc, ";
            $sqldoc .= "       p56_coddoc, ";
            $sqldoc .= "       p56_descr ";
            $sqldoc .= "  from procdoctipo ";
            $sqldoc .= "       inner join procdoc          on p56_coddoc  = p57_coddoc ";
            $sqldoc .= "       left  join procprocessodoc  on p81_coddoc  = p57_coddoc ";
            $sqldoc .= "                                  and p81_codproc = $p58_codproc ";
            $sqldoc .= " where p57_codigo = $p58_codigo " ;

            $res = $cldoc->sql_record($sqldoc);

            if ($cldoc->numrows > 0) {
              echo "<b>DOCUMENTOS</b><br>";
              $docs = "";
              $ndocs = "";
              for ($x=0; $x<$cldoc->numrows; $x++) {
                db_fieldsmemory($res,$x);
                echo "<input type='checkbox' name='doc$x' ".($p81_doc == 't'?'checked':'')." onClick='js_valor()'
                             value='$p56_coddoc'><b>$p56_descr</b><br>";
                if ($p81_doc == 't') {
                  $docs .= $p56_coddoc."#";
                } else {
                  $ndocs .= $p56_coddoc."#";
                }
              }
            }
          }

        } else {

          if (@$p58_codigo != "") {
            $res = $cldoc->sql_record($cldoc->sql_query($p58_codigo,"","p56_coddoc,p56_descr"));
            if ($cldoc->numrows > 0) {
              echo "<b>DOCUMENTOS</b><br>";
              for ($x=0; $x<$cldoc->numrows; $x++) {
                db_fieldsmemory($res,$x);
                echo "<input type='checkbox' name='doc$x' onClick='js_valor()' value='$p56_coddoc'>
                        <b>$p56_descr</b><br>";
              }
            }
          }
        }
      }
      echo "</fieldset>";
    }
      db_input('docs',50,$Ip58_codproc,true,'hidden',3,"");
      db_input('ndocs',50,$Ip58_codproc,true,'hidden',3,"");
      db_input('btnalterar',10,"",true,'hidden',3);
    ?>
    </td>
  </tr>
</table>
</center>
<input type="submit" name="altera" id="altera" value="Alterar"> 

<input name="pesquisar" type="button" id="pesquisar" value="Pesquisar" onclick="js_pesquisa();"
       <?=($db_opcao == 1 ? "disabled" : "")?>>


</fieldset>
<script>



function js_valor(){
 var cods  = '';
 var ncods = '';

  for(i=0;i<document.form1.length;i++){
     if(document.form1.elements[i].type == "checkbox"){
       if(document.form1.elements[i].checked == true){
          cods += document.form1.elements[i].value + "#";
       } else {
          ncods += document.form1.elements[i].value + '#';
       }
     }
  }
  document.form1.docs.value = cods;
  document.form1.ndocs.value = ncods;
}

function js_pesquisap58_codigo(mostra){
  if(mostra==true){
    db_iframe.jan.location.href = 'func_tipoproc.php?grupo=1&funcao_js=parent.js_mostratipoproc1|0|1';
    db_iframe.mostraMsg();
    db_iframe.show();
    db_iframe.focus();
  }else{
    var p58_codigo = document.form1.p58_codigo.value;
    var sUrl = 'func_tipoproc.php?grupo=1&pesquisa_chave='+p58_codigo+'&funcao_js=parent.js_mostratipoproc';
    db_iframe.jan.location.href = sUrl;
  }
  parent.document.formaba.dadosprocesso.disabled      = false;
  parent.document.formaba.processosapensados.disabled = true;
}
function js_mostratipoproc(chave,erro){
  document.form1.p51_descr.value = chave;
  if(erro==true){
    document.form1.p58_codigo.focus();
    document.form1.p58_codigo.value = '';
  }
  parent.document.formaba.dadosprocesso.disabled      = false;
  parent.document.formaba.processosapensados.disabled = false;
}
function js_mostratipoproc1(chave1,chave2){
  document.form1.p58_codigo.value = chave1;
  document.form1.p51_descr.value = chave2;
  document.form1.btnalterar.value ='' ;
  //document.form1.submit();
  db_iframe.hide();
  parent.document.formaba.dadosprocesso.disabled      = false;
  parent.document.formaba.processosapensados.disabled = false;
}
function js_pesquisap58_numcgm(mostra){
  if(mostra==true){
    db_iframe.jan.location.href = 'func_nome.php?funcao_js=parent.js_mostracgm1|0|1&testanome=true&incproc=true';
    db_iframe.mostraMsg();
    db_iframe.show();
    db_iframe.focus();
  }else{
    var p58_numcgm = document.form1.p58_numcgm.value;
    var sUrl = 'func_nome.php?pesquisa_chave='+p58_numcgm+'&funcao_js=parent.js_mostracgm';
    db_iframe.jan.location.href = sUrl;
  }
  parent.document.formaba.dadosprocesso.disabled      = false;
  parent.document.formaba.processosapensados.disabled = true;
}
function js_mostracgm(erro,chave){
  document.form1.z01_nome.value = chave;
  document.form1.p58_requer.value = chave2;
  if(erro==true){
    document.form1.p58_numcgm.focus();
    document.form1.p58_numcgm.value = '';
  }
  parent.document.formaba.dadosprocesso.disabled      = false;
  parent.document.formaba.processosapensados.disabled = false;
}
function js_mostracgm1(chave1,chave2){
  document.form1.p58_numcgm.value = chave1;
  document.form1.z01_nome.value = chave2;
  document.form1.p58_requer.value = chave2;
  db_iframe.hide();
  parent.document.formaba.dadosprocesso.disabled      = false;
  parent.document.formaba.processosapensados.disabled = false;
}
function js_pesquisap58_coddepto(mostra){
  if(mostra==true){
    db_iframe.jan.location.href = 'func_db_depart.php?funcao_js=parent.js_mostradb_depart1|0|z01_nome';
    db_iframe.mostraMsg();
    db_iframe.show();
    db_iframe.focus();
  }else{
    var p58_coddepto = document.form1.p58_coddepto.value;
    var sUrl = 'func_db_depart.php?pesquisa_chave='+p58_coddepto+'&funcao_js=parent.js_mostradb_depart';
    db_iframe.jan.location.href = sUrl;
  }
  parent.document.formaba.dadosprocesso.disabled      = false;
  parent.document.formaba.processosapensados.disabled = true;
}
function js_mostradb_depart(chave,erro){
  document.form1.descrdepto.value = chave;
  if(erro==true){
    document.form1.p58_coddepto.focus();
    document.form1.p58_coddepto.value = '';
  }
  parent.document.formaba.dadosprocesso.disabled      = false;
  parent.document.formaba.processosapensados.disabled = false;
}
function js_mostradb_depart1(chave1,chave2){
  document.form1.p58_coddepto.value = chave1;
  document.form1.descrdepto.value = chave2;
  db_iframe.hide();
  parent.document.formaba.dadosprocesso.disabled      = false;
  parent.document.formaba.processosapensados.disabled = false;
}
function js_pesquisa(){
  db_iframe.jan.location.href = 'func_protprocessodeptoatual.php?grupo=1&funcao_js=parent.js_preenchepesquisa|0|1';
  db_iframe.mostraMsg();
  db_iframe.show();
  db_iframe.focus();
  parent.document.formaba.dadosprocesso.disabled      = false;
  parent.document.formaba.processosapensados.disabled = true;
}
function js_preenchepesquisa(chave1,chave2){
  db_iframe.hide();
  location.href = 'manutencaoprocesso.php?chavepesquisa='+chave1+'&p58_numcgm='+chave2;
  js_processosapensados(chave1);
  parent.document.formaba.dadosprocesso.disabled      = false;
  parent.document.formaba.processosapensados.disabled = false;
}


<?
if (isset($p58_ano)) {
  echo "document.form1.p58_numero.value = '".$p58_numero."/".$p58_ano."'";
}
?>

function js_validaObservacao() {

  var sMensagem = 'Aviso:\n Você informou no campo observação mais de 500 caracteres, pode ser que na capa de processo não conste todas informações.\n';
  sMensagem    += 'Deseja salvar assim mesmo?';
  if ($F('p58_obs').length > 500 && !confirm(sMensagem) ) {
    return false;
  }

  return true;
}

document.getElementById("p58_dtproc").readOnly = true;
document.getElementById("p58_dtproc").style.backgroundColor = "#DEB887";
document.getElementById("dtjs_p58_dtproc").style.display = "none";


</script>
<?
$func_iframe = new janela('db_iframe','');
$func_iframe->posX    = 0;
$func_iframe->posY    = 2;
$func_iframe->largura = 780;
$func_iframe->altura  = 430;
$func_iframe->titulo  = 'Pesquisa';
$func_iframe->iniciarVisivel = false;
$func_iframe->mostrar();

if($db_opcao == 22){
  echo "<script>
        onload = js_pesquisa();
        </script>";
  $chamacgm = false;
}
?>


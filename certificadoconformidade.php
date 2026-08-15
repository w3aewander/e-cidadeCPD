<?


require_once("libs/db_stdlib.php");
require_once("libs/db_conecta.php");
require_once("libs/db_sessoes.php");
require_once("libs/db_usuariosonline.php");
require_once("dbforms/db_funcoes.php");
require_once("libs/db_liborcamento.php");
require_once("classes/db_empempenho_classe.php");
require_once("classes/db_orcdotacao_classe.php");
require_once("classes/db_pcmater_classe.php");
require_once("classes/db_cgm_classe.php");
require_once("libs/db_app.utils.php");
$clempempenho = new cl_empempenho;
$clorcdotacao = new cl_orcdotacao;
$clpcmater  = new cl_pcmater;
$clcgm    = new cl_cgm;

$clrotulo = new rotulocampo;
$clrotulo->label("o40_descr");
$clrotulo->label("e53_codord");
$clpcmater->rotulo->label();
$clcgm->rotulo->label();

$clempempenho->rotulo->label();
$clorcdotacao->rotulo->label();

db_postmemory($HTTP_POST_VARS);
db_app::load("prototype.js");

function testa($var){
  echo "<pre>";
  print_r($var);
  echo "</pre>";
}

if($_GET["lote"] == "sim"){
  //var_dump($_GET);
  echo "<script>alert('Certificado criado com sucesso.');</script>";
}

function listaInstituicoes(){
  $sql = pg_query("SELECT * FROM certificacaoinstituicoes ORDER BY id");
  $resultado = pg_fetch_all($sql);
  return $resultado;
}

$insts = listaInstituicoes();



if($_POST["salva"]){


if($_POST["id1"]){
  pg_query("UPDATE certificacaoinstituicoes SET usando = 1 WHERE id = 1");
}else{
  pg_query("UPDATE certificacaoinstituicoes SET usando = 2 WHERE id = 1");
}
if($_POST["id2"]){
  pg_query("UPDATE certificacaoinstituicoes SET usando = 1 WHERE id = 2");
}else{
  pg_query("UPDATE certificacaoinstituicoes SET usando = 2 WHERE id = 2");
}
if($_POST["id3"]){
  pg_query("UPDATE certificacaoinstituicoes SET usando = 1 WHERE id = 3");
}else{
  pg_query("UPDATE certificacaoinstituicoes SET usando = 2 WHERE id = 3");
}
if($_POST["id4"]){
  pg_query("UPDATE certificacaoinstituicoes SET usando = 1 WHERE id = 4");
}else{
  pg_query("UPDATE certificacaoinstituicoes SET usando = 2 WHERE id = 4");
}
if($_POST["id5"]){
  pg_query("UPDATE certificacaoinstituicoes SET usando = 1 WHERE id = 5");
}else{
  pg_query("UPDATE certificacaoinstituicoes SET usando = 2 WHERE id = 5");
}
if($_POST["id6"]){
  pg_query("UPDATE certificacaoinstituicoes SET usando = 1 WHERE id = 6");
}else{
  pg_query("UPDATE certificacaoinstituicoes SET usando = 2 WHERE id = 6");
}
if($_POST["id7"]){
  pg_query("UPDATE certificacaoinstituicoes SET usando = 1 WHERE id = 7");
}else{
  pg_query("UPDATE certificacaoinstituicoes SET usando = 2 WHERE id = 7");
}
if($_POST["id8"]){
  pg_query("UPDATE certificacaoinstituicoes SET usando = 1 WHERE id = 8");
}else{
  pg_query("UPDATE certificacaoinstituicoes SET usando = 2 WHERE id = 8");
}
if($_POST["id9"]){
  pg_query("UPDATE certificacaoinstituicoes SET usando = 1 WHERE id = 9");
}else{
  pg_query("UPDATE certificacaoinstituicoes SET usando = 2 WHERE id = 9");
}
if($_POST["id10"]){
  pg_query("UPDATE certificacaoinstituicoes SET usando = 1 WHERE id = 10");
}else{
  pg_query("UPDATE certificacaoinstituicoes SET usando = 2 WHERE id = 10");
}
if($_POST["id11"]){
  pg_query("UPDATE certificacaoinstituicoes SET usando = 1 WHERE id = 11");
}else{
  pg_query("UPDATE certificacaoinstituicoes SET usando = 2 WHERE id = 11");
}
if($_POST["id12"]){
  pg_query("UPDATE certificacaoinstituicoes SET usando = 1 WHERE id = 12");
}else{
  pg_query("UPDATE certificacaoinstituicoes SET usando = 2 WHERE id = 12");
}
if($_POST["id13"]){
  pg_query("UPDATE certificacaoinstituicoes SET usando = 1 WHERE id = 13");
}else{
  pg_query("UPDATE certificacaoinstituicoes SET usando = 2 WHERE id = 13");
}
if($_POST["id14"]){
  pg_query("UPDATE certificacaoinstituicoes SET usando = 1 WHERE id = 14");
}else{
  pg_query("UPDATE certificacaoinstituicoes SET usando = 2 WHERE id = 14");
}
if($_POST["id15"]){
  pg_query("UPDATE certificacaoinstituicoes SET usando = 1 WHERE id = 15");
}else{
  pg_query("UPDATE certificacaoinstituicoes SET usando = 2 WHERE id = 15");
}
if($_POST["id16"]){
  pg_query("UPDATE certificacaoinstituicoes SET usando = 1 WHERE id = 16");
}else{
  pg_query("UPDATE certificacaoinstituicoes SET usando = 2 WHERE id = 16");
}
if($_POST["id17"]){
  pg_query("UPDATE certificacaoinstituicoes SET usando = 1 WHERE id = 17");
}else{
  pg_query("UPDATE certificacaoinstituicoes SET usando = 2 WHERE id = 17");
}
if($_POST["id18"]){
  pg_query("UPDATE certificacaoinstituicoes SET usando = 1 WHERE id = 18");
}else{
  pg_query("UPDATE certificacaoinstituicoes SET usando = 2 WHERE id = 18");
}
if($_POST["id19"]){
  pg_query("UPDATE certificacaoinstituicoes SET usando = 1 WHERE id = 19");
}else{
  pg_query("UPDATE certificacaoinstituicoes SET usando = 2 WHERE id = 19");
}
header("Location: certificadoconformidade.php");


}



?>

<html>
<head>
<title>Microsist</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<meta http-equiv="Expires" CONTENT="0">
<script language="JavaScript" type="text/javascript" src="scripts/scripts.js"></script>

<script>
function js_abre(){
   // js_OpenJanelaIframe('','db_iframe_orgao','func_saldoorcdotacao.php?coddot='+coddot,'pesquisa',true);
   obj = document.form1;
   if (    (obj.e60_emiss1_dia.value !='') 
        && (obj.e60_emiss2_dia.value !='')
        && (obj.e60_emiss1_mes.value !='')
        && (obj.e60_emiss2_mes.value !='')
        && (obj.e60_emiss1_ano.value !='')
        && (obj.e60_emiss1_ano.value !='')) {
    dt1 = obj.e60_emiss1_ano.value+'-'+obj.e60_emiss1_mes.value+'-'+obj.e60_emiss1_dia.value ;
    dt2 = obj.e60_emiss2_ano.value+'-'+obj.e60_emiss2_mes.value+'-'+obj.e60_emiss2_dia.value ;
   } else {
      dt1='';
      dt2='';
   }

        codemp = document.form1.e60_codemp.value ;  
    var sProcesso = encodeURIComponent($F('e150_numeroprocesso'));  
    js_OpenJanelaIframe('top.corpo','db_iframe_empconsulta002','emp1_empconsulta002.php?e150_numeroprocesso='+sProcesso+'&e60_codemp='+codemp+'&e60_numemp='+document.form1.e60_numemp.value+'&o58_coddot='+document.form1.o58_coddot.value+'&pc01_codmater='+document.form1.pc01_codmater.value+'&z01_numcgm='+document.form1.z01_numcgm.value+'&dt1='+dt1+'&dt2='+dt2+'&e53_codord='+document.form1.e53_codord.value+'&funcao_js=parent.js_consulta002|e60_numemp','Pesquisa',true);
  }
  
function js_consulta002(chave1){
   js_OpenJanelaIframe('top.corpo','db_iframe_empempenho001','func_empempenho001.php?e60_numemp='+chave1,'Pesquisa',true);
//   db_iframe_empconsulta002.hide(); 
   
}

function js_limpa(){
   location.href='emp1_empconsulta001.php'; 
}
</script>  
<link href="estilos.css" rel="stylesheet" type="text/css">
<script>
    function js_mascara(evt){
      var evt = (evt) ? evt : (window.event) ? window.event : "";
      
      if( (evt.charCode >46 && evt.charCode <58) || evt.charCode ==0 ){//8:backspace|46:delete|190:. 
	return true;
      }else{
	return false;
      }  
    }
</script>
</head>
<body bgcolor=#CCCCCC leftmargin="0" topmargin="0" marginwidth="0" marginheight="0" onLoad="a=1" bgcolor="#cccccc">

<center>
<div style="margin-top: 20px; width: 450px;">
<form name="form1" method="post" action="certificadoconformidade2.php" id="formulario">
  <fieldset>
    <legend><strong>Consulta Empenho</strong></legend>
    <table border='0'> 
      <tr> 
        <td  align="left" nowrap title="<?=$Te60_codemp?>">
        	<? db_ancora(@$Le60_codemp,"js_pesquisae60_codemp(true);",1);  ?>
        </td>
  	    <td  nowrap="nowrap" title='<?=$Te60_codemp?>' > 
          <input readonly name="e60_codemp" size="10" type='text' onKeyPress="return js_mascara(event);" style="background-color:#DEB887" required>
        </td>

        <td><input type="checkbox" name="porlote" id="porlote" value="lote" onclick="conferelote(this)">Gerar por Lote</td>

      </tr> 
      <input type="hidden" name="instituicao" value="<?=db_getsession("DB_instit")?>">
      <input type="hidden" name="sequencialemp">


      
    </table>
  </fieldset>
  <?php  /* ?>
  <input name="pesquisa" type="button" onclick='js_abre();'  value="Pesquisa">
  <?php */ ?>
  <input name="prosseguir" type="submit" value="Prosseguir">
</form>
</div>

<form method="post" action="geracertificadoconformidade.php">
<input name="pesquisar" type="button" id="pesquisar" value="Consulta Certificações" onclick="js_pesquisa22();" >
<p>
  <input type="text" name="numerocertificado" id="numerocertificado" readonly style="background-color:#DEB887">
  <input type="submit" name="consulta" value="Ir">
</p>
<input type="hidden" name="idcertificado" id="idcertificado">
</form>
<?php if(db_getsession("DB_id_usuario") == 1) : ?>
<button id="bajuste">Preencher OPs vazias</button>
<span id="bajspan"></span>
<?php endif; ?>
</center>


<?php if(db_getsession("DB_instit") == 1) : ?>
<?php //testa($insts);  ?>
<div style="width: 600px; margin: 0 auto">
  <fieldset>
    <legend>Instituições Usando o Certificado</legend>
    <form action="certificadoconformidade.php" method="post">
      <?php foreach($insts as $linha) : ?>
        <input type="checkbox" name="id<?=$linha['id']?>" value="Sim" <?=($linha['usando'] == 1) ? 'checked' : ''?> >
        <?=$linha['codigo'] . " - " . $linha['nomeinst']?><br>        
      <?php endforeach; ?>

      
      <?php if(db_getsession("DB_id_usuario") == 1) : ?>
      <input type="submit" name="salva" value="Redefinir">
      <?php else : ?>
        <input type="submit" name="salva" value="Redefinir" disabled>
      <?php endif; ?>
    </form>
  </fieldset>  
</div>

<?php endif; ?>



<? 
db_menu(db_getsession("DB_id_usuario"),db_getsession("DB_modulo"),db_getsession("DB_anousu"),db_getsession("DB_instit"));
?>
<script>
//--------------------------------
function js_pesquisae60_codemp(mostra){
  if(mostra==true){
    js_OpenJanelaIframe('top.corpo','db_iframe_empempenho','func_empempenho.php?funcao_js=parent.js_mostraempempenho2|e60_codemp|e60_anousu|e60_numemp','Pesquisa',true);
  }else{
   // js_OpenJanelaIframe('top.corpo','db_iframe_empempenho02','func_empempenho.php?pesquisa_chave='+document.form1.e60_numemp.value+'&funcao_js=parent.js_mostraempempenho','Pesquisa',false);
  }
}
function js_mostraempempenho2(chave1, chave2, chave3){
  document.form1.e60_codemp.value = chave1 + '/' + chave2;
  document.form1.sequencialemp.value = chave3;
  db_iframe_empempenho.hide();
}

function js_pesquisa_cgm(mostra){
  if(mostra==true){
    js_OpenJanelaIframe('top.corpo','db_iframe_cgm','func_cgm_empenho.php?funcao_js=parent.js_mostracgm1|e60_numcgm|z01_nome','Pesquisa',true);
  }else{
     if(document.form1.z01_numcgm.value != ''){ 
        js_OpenJanelaIframe('top.corpo','db_iframe_cgm','func_cgm_empenho.php?pesquisa_chave='+document.form1.z01_numcgm.value+'&funcao_js=parent.js_mostracgm','Pesquisa',false);
     }else{
       document.form1.z01_nome2.value = ''; 
     }
  }
}
function js_mostracgm(chave,erro){
  document.form1.z01_nome2.value = chave; 
  if(erro==true){ 
    document.form1.z01_nome2.value = ''; 
    document.form1.z01_numcgm.focus(); 
  }
}
function js_mostracgm1(chave1,chave2){
   document.form1.z01_numcgm.value = chave1;  
   document.form1.z01_nome2.value = chave2;
   db_iframe_cgm.hide();
}
//--------------------------------
function js_pesquisa_pcmater(mostra){
  if(mostra==true){
    js_OpenJanelaIframe('top.corpo','db_iframe_pcmater','func_pcmater.php?funcao_js=parent.js_mostrapcmater1|pc01_codmater|pc01_descrmater','Pesquisa',true);
  }else{
     if(document.form1.pc01_codmater.value != ''){ 
        js_OpenJanelaIframe('top.corpo','db_iframe_pcmater','func_pcmater.php?pesquisa_chave='+document.form1.pc01_codmater.value+'&funcao_js=parent.js_mostrapcmater','Pesquisa',false);
     }else{
       document.form1.pc01_descrmater.value = ''; 
     }
  }
}
function js_mostrapcmater(chave,erro){
  document.form1.pc01_descrmater.value = chave; 
  if(erro==true){ 
    document.form1.pc01_codmater.focus(); 
    document.form1.pc01_descrmater.value = ''; 
  }
}
function js_mostrapcmater1(chave1,chave2){
   document.form1.pc01_codmater.value = chave1;  
   document.form1.pc01_descrmater.value = chave2;
   db_iframe_pcmater.hide();
}
//--------------------------------
function js_pesquisa_dotacao(mostra){
  if(mostra==true){
    js_OpenJanelaIframe('top.corpo','db_iframe_orcdotacao','func_orcdotacao.php?funcao_js=parent.js_mostradotacao1|o58_coddot|o56_descr','Pesquisa',true);
  }else{
     if(document.form1.o58_coddot.value != ''){ 
        js_OpenJanelaIframe('top.corpo','db_iframe_orcdotacao','func_orcdotacao.php?pesquisa_chave='+document.form1.o58_coddot.value+'&funcao_js=parent.js_mostradotacao','Pesquisa',false);
     }else{
       document.form1.o40_descr.value = ''; 
     }
  }
}
function js_mostradotacao(chave,erro){
  document.form1.o40_descr.value = chave; 
  if(erro==true){ 
    document.form1.o58_coddot.focus(); 
    document.form1.o58_coddot.value = ''; 
  }
}
function js_mostradotacao1(chave1,chave2){
  document.form1.o58_coddot.value = chave1;  
  document.form1.o40_descr.value = chave2;
  db_iframe_orcdotacao.hide();
}
//--------------------------------
function js_pesquisa_empenho(mostra){
  if(mostra==true){
    js_OpenJanelaIframe('top.corpo','db_iframe_empempenho','func_empempenho.php?funcao_js=parent.js_mostraempenho1|e60_numemp','Pesquisa',true);
  }else{
     if(document.form1.e60_numemp.value != ''){ 
        js_OpenJanelaIframe('top.corpo','db_iframe_empempenho','func_empempenho.php?pesquisa_chave='+document.form1.e60_numemp.value+'&funcao_js=parent.js_mostraempenho','Pesquisa',false);
     }else{
       document.form1.z01_nome1.value = ''; 
     }
  }
}
function js_mostraempenho(erro,chave){
  document.form1.z01_nome1.value = chave; 
  if(erro==true){ 
    document.form1.e60_numemp.focus(); 
    document.form1.z01_nome1.value = ''; 
  }
}
function js_mostraempenho1(chave1){
  document.form1.e60_numemp.value = chave1;
  // document.form1.z01_nome1.value = chave2;
  db_iframe_empempenho.hide();
}


//--------------------------------


function js_buscae53_codord(mostra){
  if(mostra==true){
    js_OpenJanelaIframe('top.corpo','db_iframe_pagordemele','func_pagordemele.php?funcao_js=parent.js_mostracodord1|e53_codord','Pesquisa',true);
  }else{
     if(document.form1.e53_codord.value != ''){ 
        js_OpenJanelaIframe('top.corpo','db_iframe_pagordemele','func_pagordemele.php?pesquisa_chave='+document.form1.e53_codord.value+'&funcao_js=parent.js_mostracodord','Pesquisa',false);
     }else{
       document.form1.e53_codord.value = ''; 
     }
  }
}

function js_mostracodord(chave,erro){
  if(erro==true){ 
    document.form1.e53_codord.value = ''; 
    document.form1.e53_codord.focus(); 
  }
}

function js_mostracodord1(chave1){
   document.form1.e53_codord.value = chave1;  
   //document.form1.z01_nome2.value = chave2;
   db_iframe_pagordemele.hide();
}





function js_pesquisa22(){  
  js_OpenJanelaIframe('','db_iframe_orcdotacao','func_certificacao.php?funcao_js=parent.js_preenchepesquisa|id|nocertificado','Pesquisa',true);
}

function js_preenchepesquisa(chave1, chave2){
  
  document.getElementById("numerocertificado").value = arguments[1];
  document.getElementById("idcertificado").value = arguments[0];
  db_iframe_orcdotacao.hide();
  //document.getElementById("db_opcao").value = "Alterar";
  //document.getElementById("apagar").style.display = "";  

}


function conferelote(checkbox){
  var formulario = document.getElementById("formulario");
  
  if(checkbox.checked == true){    
    formulario.action = "certificadoconformidadelote.php";    
  }else{
    formulario.action = "certificadoconformidade2.php";    
  }
}

var baj = document.getElementById("bajuste");
baj.addEventListener("click", function(){
  var xhr = new XMLHttpRequest();
  xhr.onreadystatechange = function(){
    if((xhr.readyState === 4) && (xhr.status === 200)){
      document.getElementById("bajspan").innerHTML = xhr.responseText;
      alert('OPs preenchidas');
    }
  }
  xhr.open("GET","ajustaops24.php", true);
  xhr.send(null);
}, false);



</script>
</body>
</html>
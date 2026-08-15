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


    function js_mascara2(evt){
      var evt = (evt) ? evt : (window.event) ? window.event : "";
      
      if( (evt.charCode >47 && evt.charCode <58)){
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


<fieldset>
  <legend><strong>Pesquisa por Número do Empenho</strong></legend>
<p>
  <input type="text" id="pne" onKeyPress="return js_mascara(event);">
  <input type="button" id="consulta" value="Buscar">
</p>
</fieldset>


<fieldset>
  <legend><strong>Pesquisa por Sequencial do Empenho</strong></legend>
<p>
  <input type="text" id="pse" onKeyPress="return js_mascara2(event);">
  <input type="button" id="consultaseq" value="Buscar">
</p>
</fieldset>


</div>


<div id="resultados" style="margin-top: 20px; width: 705px;"></div>

</center>


<center>
  <div style="margin-top: 20px; width: 450px;">


<fieldset>
  <legend><strong>Certificados Retirados (Reimpressão e Alterações)</strong></legend>


<form method="post" action="alteraretirada.php">
<input name="pesquisar" type="button" id="pesquisar" value="Consulta Retiradas" onclick="js_pesquisa22();" >
<p>
  <input type="text" name="numerocertificado" id="numerocertificado" readonly style="background-color:#DEB887">
  <input type="button" name="consulta" value="Imprimir" onclick="imprimeavulso()">
  <input type="submit" name="ir" value="Alterar/Excluir">
</p>
<input type="hidden" name="idcertificado" id="idcertificado">
</form>


</fieldset>
</div>
</center>




<? 
db_menu(db_getsession("DB_id_usuario"),db_getsession("DB_modulo"),db_getsession("DB_anousu"),db_getsession("DB_instit"));
?>
<script>
var btnseq = document.getElementById("consultaseq");
btnseq.addEventListener("click", function(){
  var sequencial = document.getElementById("pse").value;
  var xhr = new XMLHttpRequest();
  xhr.onreadystatechange = function(){
    if((xhr.readyState === 4) && (xhr.status === 200)){          
      document.getElementById("resultados").innerHTML = xhr.responseText;
    }
  }
  xhr.open("GET","cere_porseq.php?sequencial="+sequencial, true);
  xhr.send(null);      
}, false);

var btnemp = document.getElementById("consulta");
btnemp.addEventListener("click", function(){
  var sequencial = document.getElementById("pne").value;
  var xhr = new XMLHttpRequest();
  xhr.onreadystatechange = function(){
    if((xhr.readyState === 4) && (xhr.status === 200)){          
      document.getElementById("resultados").innerHTML = xhr.responseText;
    }
  }
  xhr.open("GET","cere_pornum.php?sequencial="+sequencial, true);
  xhr.send(null);      
}, false);



function imprime(ids){
  var cb = document.querySelectorAll('input[name=id]:checked');
  if(cb.length == 0){
    alert("Escolha um empenho.");
    return false;
  }
  
  var ids = [];
  
  var k = 0;
  Array.from(cb).forEach(node => {
    ids.push(cb[k].value);
    k++;
  });
  var passa = ids.toString().split();
  var idt = passa[0];

  var c1, c2, c3;
  r1 = document.querySelector('input[name="g11"]:checked').value;
  r2 = document.querySelector('input[name="g22"]:checked').value;
  r3 = document.querySelector('input[name="g33"]:checked').value;

  t1 = document.getElementById("g11f").value;
  t2 = document.getElementById("g22f").value;
  t3 = document.getElementById("g33f").value;

  if(r1 == 1 && !t1){
    alert("Campo 'Folha' é obrigatório.");
    return false;
  }

  if(r2 == 1 && !t2){
    alert("Campo 'Folha' é obrigatório.");
    return false;
  }

  if(r3 == 1 && !t3){
    alert("Campo 'Folha' é obrigatório.");
    return false;
  }
  
  c1 = r1 + t1;
  c2 = r2 + t2;
  c3 = r3 + t3;
  
  
  
  //Anterior
  //var jan = window.open('imprimecertificadoretirada.php?idatabela='+idt,'','width='+(screen.availWidth-5)+',height='+(screen.availHeight-40)+',scrollbars=1,location=0 ');  
  
  document.location.reload(true);
  var jan = window.open('imprimecertificadoretirada.php?idatabela='+idt+'&c1='+c1+'&c2='+c2+'&c3='+c3,'','width='+(screen.availWidth-5)+',height='+(screen.availHeight-40)+',scrollbars=1,location=0 ');  
  jan.moveTo(0,0);
}



function js_pesquisa22(){  
  js_OpenJanelaIframe('','db_iframe_orcdotacao','func_retirada.php?funcao_js=parent.js_preenchepesquisa|id|noempenho|e50_codord','Pesquisa',true);
} 

function js_preenchepesquisa(chave1, chave2, chave3){    
  document.getElementById("idcertificado").value = arguments[0];
  document.getElementById("numerocertificado").value = arguments[1];
  //document.getElementById("numeroordem").value = arguments[2];
  db_iframe_orcdotacao.hide();
}

function imprimeavulso(){
  var id_da_certificao = document.getElementById("idcertificado").value;    

  var jan     = window.open('imprimecertificadoretiradaavulso.php?idatabela='+id_da_certificao,'','width='+(screen.availWidth-5)+',height='+(screen.availHeight-40)+',scrollbars=1,location=0 ');
  jan.moveTo(0,0);
}

function libera(id){
    console.log("Libera");    
    document.getElementById("g"+id+"f").required = false;
  }

  function trava(id){
    console.log("Trava");
    document.getElementById("g"+id+"f").required = true;
  }

</script>

<?php 
if($_POST){
  $ids = $_POST["id"];
  $ids = implode(",", $_POST["id"]);  
  
  echo "<script>imprime(".$ids.");</script>";
}
 ?>
</body>
</html>
<?
/*
 *     E-cidade Software Publico para Gestao Municipal                
 *  Copyright (C) 2009  DBselller Servicos de Informatica             
 *                            www.dbseller.com.br                     
 *                         e-cidade@dbseller.com.br                   
 *                                                                    
 *  Este programa e software livre; voce pode redistribui-lo e/ou     
 *  modifica-lo sob os termos da Licenca Publica Geral GNU, conforme  
 *  publicada pela Free Software Foundation; tanto a versao 2 da      
 *  Licenca como (a seu criterio) qualquer versao mais nova.          
 *                                                                    
 *  Este programa e distribuido na expectativa de ser util, mas SEM   
 *  QUALQUER GARANTIA; sem mesmo a garantia implicita de              
 *  COMERCIALIZACAO ou de ADEQUACAO A QUALQUER PROPOSITO EM           
 *  PARTICULAR. Consulte a Licenca Publica Geral GNU para obter mais  
 *  detalhes.                                                         
 *                                                                    
 *  Voce deve ter recebido uma copia da Licenca Publica Geral GNU     
 *  junto com este programa; se nao, escreva para a Free Software     
 *  Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA          
 *  02111-1307, USA.                                                  
 *  
 *  Copia da licenca no diretorio licenca/licenca_en.txt 
 *                                licenca/licenca_pt.txt 
 */

require("libs/db_stdlib.php");
require("libs/db_conecta.php");
include("libs/db_sessoes.php");
include("libs/db_usuariosonline.php");
include("dbforms/db_classesgenericas.php");
include("dbforms/db_funcoes.php");

?>

<html>
<head>
<title>Microsist</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<meta http-equiv="Expires" CONTENT="0">
<script language="JavaScript" type="text/javascript" src="scripts/scripts.js"></script>

<script>
function js_verifica(){
  var anoi = new Number(document.form1.datai_ano.value);
  var anof = new Number(document.form1.dataf_ano.value);
  if(anoi.valueOf() > anof.valueOf()){
    alert('Intervalo de data invalido. Velirique !.');
    return false;
  }
  return true;
}



</script>  
<link href="estilos.css" rel="stylesheet" type="text/css">
</head>
<body bgcolor=#CCCCCC leftmargin="0" topmargin="0" marginwidth="0" marginheight="0" onLoad="a=1" bgcolor="#cccccc">
  <table width="790" border="0" cellpadding="0" cellspacing="0" bgcolor="#5786B2">
  <tr>
    <td width="360" height="18">&nbsp;</td>
    <td width="263">&nbsp;</td>
    <td width="25">&nbsp;</td>
    <td width="140">&nbsp;</td>
  </tr>
</table>
<br><br><br>

<center>
  <form name="form1" method="post" action="" onsubmit="return js_verifica();">
    <fieldset style="width:410px; padding:10px;">
      <legend>Arquivos Processados (LOG)</legend>
      <table  align="center">    
      <?php /* ?>
      <tr>
        <td align="left" ><strong>Data Inicial :</strong></td>
        <td>
        <?=db_inputdata('datai','01','01',db_getsession("DB_anousu"),true,'text',4)?>
        </td>
      </tr>
      <tr>
        <td align="left" ><strong>Data Final :</strong></td>
        <td>
        <?
         $datausu = date("Y/m/d",db_getsession("DB_datausu"));
         $dataf_ano = substr($datausu,0,4);
         $dataf_mes = substr($datausu,5,2);
         $dataf_dia = substr($datausu,8,2);

        ?>
        <?=db_inputdata('dataf',$dataf_dia,$dataf_mes,$dataf_ano,true,'text',4)?>
        </td>
      </tr>
      <?php */ ?>
      <tr>
        <td><input type="radio" name="opcao" id="stodos" value="todos"><b>Todos</b></td>
      </tr>

      <tr>
        <td>
          <input type="radio" name="opcao" id="sexercicio" value="exercicio"><b>Exercício</b>
          <input type="text" name="exercicio" id="exercicio" size="5" maxlength="4">
          </td>
      </tr>

      <tr>
        <td>
          <input type="radio" name="opcao" id="sdatap" value="processamento"><b>Data Processamento:</b>
            <?=db_inputdata('pi',"","","",true,'text',4)?> a 
            <?=db_inputdata('pf',"","","",true,'text',4)?>
          </td>
      </tr>
      

      <?php /* ?>
      <tr>
        <td>
          <b>Período: </b>
          <select id="filtro" name="filtro" onchange="troca();">
            <option vallue="t">Todos</option>
            <option value='e'>Exercício</option>
            <option value='dp'>Data de Processamento</option>
        </td>
      </tr>

    
      <tr id="aexercicio" style="display: none;">
        <td><b>Exercício: </b><input type="text" name="exercicio" id="exercicio" size="5" maxlength="4"></td>
      </tr>
    
    
      <tr id="datap" style="display: none">
        <td>
        <b>Data Processamento:</b>
        <?=db_inputdata('pi',"","","",true,'text',4)?> a 
        <?=db_inputdata('pf',"","","",true,'text',4)?>
        
        </td>
      </tr>

      <?php */ ?>
      
      <tr><td></td></tr>
      <tr><td></td></tr>
      <tr>
        <td colspan="2" align = "center"> 
          <input name="relatorio" id="relatorio" type="button" value="Relatório" onclick="js_emite();">
        </td>
      </tr>
      


    </table>
    </fieldset>
      </form>
    </center>
<?
  db_menu(db_getsession("DB_id_usuario"),db_getsession("DB_modulo"),db_getsession("DB_anousu"),db_getsession("DB_instit"));
?>
</body>
</html>
<script>
function js_emite(){
  var opcao = document.querySelector('input[name="opcao"]:checked').value;
  var periodo;

  if(opcao == "todos"){
    periodo = "p=todos";
  }

  if(opcao == "exercicio"){       
    var ano = document.getElementById("exercicio").value;
    if(ano == ""){
      alert("Preencha o ano");
      return false;
    }
    periodo = "p="+ano;
  }

  if(opcao == "processamento"){
    var pi = document.getElementById("pi").value;
    var pf = document.getElementById("pf").value;
    if(pi == "" || pf == ""){
      alert("Preencha o período");
      return false;
    }
    periodo = "pi="+pi+"&pf="+pf;
  }

  /*
  var periodo;
  //var opcao = document.getElementById("filtro").value;
  if(opcao == "t"){
    periodo = "periodo=todos";
  }else if(opcao == "e"){
    var ano = document.getElementById("exercicio").value;
    periodo = "periodo="+ano;
  }else{
    var pi = document.getElementById("pi").value;
    var pf = document.getElementById("pf").value;
    periodo = "pi="+pi+"&pf="+pf;
  }
  */
  var url = "gera_relconciliacaobancaria.php?"+periodo;


  jan = window.open(url,'','width='+(screen.availWidth-5)+',height='+(screen.availHeight-40)+',scrollbars=1,location=0 ');
  jan.moveTo(0,0);
}

function troca(){
  var opcao = document.getElementById("filtro").value;
  if(opcao == "t"){
    document.getElementById("aexercicio").style.display = "none";
    document.getElementById("datap").style.display = "none";
  }else if(opcao == "dp"){
    document.getElementById("aexercicio").style.display = "none";
    document.getElementById("datap").style.display = "inline-block";
  }else{
    document.getElementById("datap").style.display = "none";
    document.getElementById("aexercicio").style.display = "inline-block";
  }

}


function troca2(){
  var opcao = document.getElementById("filtro").value;
}
</script>



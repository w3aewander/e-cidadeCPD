<?
/*
 *     E-cidade Software Publico para Gestao Municipal                
 *  Copyright (C) 2009  DBSeller Servicos de Informatica             
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

require(modification("libs/db_stdlib.php"));
require(modification("libs/db_conecta.php"));
include(modification("libs/db_sessoes.php"));
include(modification("libs/db_usuariosonline.php"));
include(modification("dbforms/db_funcoes.php"));
$clrotulo = new rotulocampo;
$clrotulo->label("k02_codigo");
$clrotulo->label("k02_drecei");
?>
<html>
<head>
  <title>Microsist</title>
  <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
  <meta http-equiv="Expires" CONTENT="0">
  <script language="JavaScript" type="text/javascript" src="scripts/scripts.js"></script>
  <link href="estilos.css" rel="stylesheet" type="text/css">
</head>
<body bgcolor=#CCCCCC bgcolor="#cccccc" leftmargin="0" topmargin="0" marginwidth="0" marginheight="0" onLoad="a=1" >

<table width="790" height='18'  border="0" cellpadding="0" cellspacing="0" bgcolor="#5786B2">
  <tr> 
    <td width="360">&nbsp;</td>
    <td width="263">&nbsp;</td>
    <td width="25">&nbsp;</td>
    <td width="140">&nbsp;</td>
  </tr>
</table>
<br>
<table border="0" cellpadding="0" align="center" cellspacing="0" bgcolor="#cccccc"><br><br>
  <form name="form1" method="post">
  
  <tr>
    <td align="right">
      <br>
      <b> Período:  </b> 
    </td>
    <td>
      <br>
      <?
      db_inputdata('data1','','','',true,'text',1,"");
      echo "<b> a</b>";
      db_inputdata('data2','','','',true,'text',1,"");
      ?>
      &nbsp;
    </td>
  </tr>
  <input type="hidden" name="inst" id="inst" value="<?=db_getsession("DB_instit")?>">
  
  <?php if(db_getsession("DB_instit") == 1) : ?>
    <tr>
      <td>
        <input type="radio" id="ipref" name="instituicao" value="pref" checked>
        <label for="ipref">Só a prefeitura</label><br>
        <input type="radio" id="itodas" name="instituicao" value="tudo">
        <label for="itodas">Todas instituições</label><br>
      </td>
      <td> </td>
    </tr>
  <?php endif; ?>
  
  <tr>

    <td height="40" align="center" colspan="2">
      <input name="csv" type="button" id="csv" value="Gera CSV" onclick="imprimeTudoCsv()">
    </td>
  </tr>
  </form>
  <center>
    <span><i>Quanto maior o período, mais demorada a geração do CSV.</i></span>
  </center>

</table>  

<script>
  function imprimeTudoCsv(){
    const instituicao = document.getElementById("inst").value;    
    let escolha;

    if(instituicao == 1){
      escolha = document.querySelector('input[name="instituicao"]:checked').value;
    }

    if(instituicao != 1 && escolha == "null"){
      escolha = instituicao;
    }
    
    
    var datainicio, datafim;
    datainicio = document.getElementById("data1").value;
    datafim = document.getElementById("data2").value;
    if(datainicio == "" || datafim == ""){
      alert("Escolha um período.");
    }
    
    
    //var jan = window.open('relprevdes.php?inst='+inst+'','width='+(screen.availWidth-5)+',height='+(screen.availHeight-40)+',scrollbars=1,location=0 ');    
    //jan = window.open('relprevdes.php?inst='+inst+'','width='+(screen.availWidth-5)+',height='+(screen.availHeight-40)+',scrollbars=1,location=0 ');    
    jan = window.open("rel_confretencoescsv.php?tipo=csv&inst="+escolha+"&di="+datainicio+"&df="+datafim+"&instituicao="+instituicao, "Relatório de Conferência de Retenções", "about:blank");  
    jan.moveTo(0,0);
  }
</script>

</body>
</html>
<? 
db_menu(db_getsession("DB_id_usuario"),db_getsession("DB_modulo"),db_getsession("DB_anousu"),db_getsession("DB_instit"));
?>
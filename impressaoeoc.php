<? 
/*
 *     E-cidade Software Publico para Gestao Municipal                
 *  Copyright (C) 2012  DBselller Servicos de Informatica             
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

//echo ($HTTP_SERVER_VARS['QUERY_STRING']);exit;
require_once("libs/db_stdlib.php");
require_once("libs/db_conecta.php");
require_once("libs/db_sessoes.php");
require_once("libs/db_usuariosonline.php");
require_once("dbforms/db_funcoes.php");
require_once("classes/db_empnotaord_classe.php");
require_once("classes/db_empnota_classe.php");
require_once("classes/db_empnotaele_classe.php");
require_once("classes/db_db_usuarios_classe.php");
require_once("classes/db_matordem_classe.php");
require_once("classes/db_matordemitem_classe.php");
require_once("classes/db_matestoque_classe.php");
require_once("classes/db_matestoqueitem_classe.php");
require_once("classes/db_matestoqueitemnota_classe.php");
require_once("classes/db_matestoqueitemoc_classe.php");
require_once("classes/db_matestoqueini_classe.php");
require_once("classes/db_matestoqueinimei_classe.php");
require_once("classes/db_matestoqueitemunid_classe.php");
require_once("classes/db_matestoqueinil_classe.php");
require_once("classes/db_matestoqueinill_classe.php");
$clusuarios = new cl_db_usuarios;
$clempnotaord = new cl_empnotaord;
$clempnota = new cl_empnota;
$clempnotaele = new cl_empnotaele;
$clmatordemitem = new cl_matordemitem;
$clmatordem = new cl_matordem;
$clmatestoque = new cl_matestoque;
$clmatestoqueitem = new cl_matestoqueitem;
$clmatestoqueitemnota = new cl_matestoqueitemnota;
$clmatestoqueitemoc = new cl_matestoqueitemoc;
$clmatestoqueini = new cl_matestoqueini;
$clmatestoqueinimei = new cl_matestoqueinimei;
$clmatestoqueitemunid = new cl_matestoqueitemunid;
$clmatestoqueinil = new cl_matestoqueinil;
$clmatestoqueinill = new cl_matestoqueinill;
$clempnota->rotulo->label();
$clempnotaele->rotulo->label();
?>
<html>
<head>
<title>Microsist</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<meta http-equiv="Expires" CONTENT="0">
<script language="JavaScript" type="text/javascript" src="scripts/scripts.js"></script>
<script language="JavaScript" type="text/javascript" src="scripts/strings.js"></script>
<script language="JavaScript" type="text/javascript" src="scripts/prototype.js"></script>
<link href="estilos.css" rel="stylesheet" type="text/css">
<link href="empenho.css" rel="stylesheet" type="text/css">
</head>
<body bgcolor=#CCCCCC leftmargin="0" marginwidth="0" marginheight="0" onLoad="a=1" style="margin-top:30px;">
<center>
<?
$clrotulo = new rotulocampo;
$clrotulo->label("nome");
$clrotulo->label("m51_codordem");
$clrotulo->label("z01_nome");
?>
<form name="form1" method="post">
  <center>
    <table>
      <tr>
        <td>
          <fieldset><legend><b>Impressão da Entrada de Ordem de Compra</b></legend>
            <table border='0'>
              <tr align = 'left'>
                <td align="left">
                  <table align="center">
                    
                    <tr>
                      <td><b><a href="#" onclick="js_pesquisa(true)"> Sequencial</b> </a></td>
                      <input type="hidden" name="idtabela" id="idtabela">
                      <td><input type="text" name="sequencial" id="sequencial" readonly style="background-color:#DEB887"></td>
                      
                    </tr>
                  </table>
                </td>
              </tr>
              <tr>
                <td>
                </td>
              </tr>
            </table>
          </fieldset> 
        </td>
      </tr>
      
    </table>
    <input name="imprimir" type="button" value="Imprimir" onclick="imprime()">
    <?php /* ?>
    <input name="anula" id='anular' type="button"  disabled value="Anular" onclick='if (confirm("Confirma a anulação?" )){js_verificaItensBaixados()}'>
    <input name="pesquisar" type="button" value="Pesquisar" onclick="js_pesquisa_empnota(true)">
    <input name="e69_codnota" type="hidden" id='e69_codnota' value="">
    <?php */ ?>
  </center>
</form>
</body>
<script>
  
  function js_pesquisa(mostra){
    //js_reset();
    if(mostra==true){
      js_OpenJanelaIframe('CurrentWindow.corpo','db_iframe_ieoc','func_eoc.php?funcao_js=parent.js_mostra|id|anousuario|sequencial','Pesquisa',true);
    }
    

  }
  function js_mostra(chave1,chave2, chave3){
    //js_reset();
    db_iframe_ieoc.hide();
    //js_getDadosNota(chave2,chave1);
    document.getElementById("idtabela").value = chave1;
    document.getElementById("sequencial").value = chave3 +"/"+chave2;    
  }

  function imprime(){
  	var id = document.getElementById("idtabela").value;

  	jan = window.open('rel_entradanotaavulsa.php?id='+id,'','width='+(screen.availWidth-5)+',height='+(screen.availHeight-40)+',scrollbars=1,location=0 ');
    jan.moveTo(0,0);
  	
  }


  js_pesquisa(true);
</script>
</html>
</center>
<?
db_menu(db_getsession("DB_id_usuario"),db_getsession("DB_modulo"),db_getsession("DB_anousu"),db_getsession("DB_instit"));
?>
</body>
</html>
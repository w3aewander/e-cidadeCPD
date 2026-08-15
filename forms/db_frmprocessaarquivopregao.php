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

//MODULO: caixa
$clrotulo = new rotulocampo;
$clrotulo->label('arqret');
$clrotulo->label("pc10_numero");
$clrotulo->label("l20_codigo");
$clrotulo->label("pc80_codproc");
?>
<form name="form1" enctype="multipart/form-data" method="post" action="">
<p><p><fieldset>
      <legend><b>Importar Arquivo Pregao</legend>
<center>
<table border="0">
      <?
      if (isset($arq_name)) {
      ?>
      <tr> 
        <td nowrap><?=$Larqret?> </td>
        <td> 
	<?
	db_input('arq_name',50,"",true,'text',3,"");
        ?>	
      </tr>

      <?
      } else {
      ?>
	<tr> 
	  <td nowrap><?=$Larqret?> </td>
	  <td> 
	  <?
	  db_input("arqret",50,$Iarqret,true,"file",4)
	  ?>	
	</tr>
  
      <?
      }
      ?>
 <tr>
    <td  align="right" nowrap title="<?=$Tl20_codigo?>">
    <b>
    <?db_ancora('Licitacao:',"js_pesquisa_liclicita(true);",1);?>
    </b>
    </td>

    <td align="left" nowrap>
      <? db_input("l20_codigo",8,$Il20_codigo,true,"text",1,"onchange='js_pesquisa_liclicita(false);'");
         ?></td>

  </tr>
  
  </table>
  </center>
	<?
	if(isset($processar)) {
	?>
<input name="arq_tmpname" type="hidden" id="arq_tmpname" value="<?=$DOCUMENT_ROOT."/tmp/".$arq_tmpname?>">
<input name="geradebcta" type="submit" id="geradebcta" value="Processar">

  <?
	} else {
	?>
<p><input name="processar" type="submit" id="processar" value="Processar">
  <?
	}
	?>

</form>
</fieldset>
<script>
function js_pesquisa_liclicita(mostra){
  if(mostra==true){
    js_OpenJanelaIframe('top.corpo','db_iframe_liclicita','func_liclicita.php?funcao_js=parent.js_mostraliclicita1|l20_codigo','Pesquisa',true);
  }else{
     if(document.form1.l20_codigo.value != ''){ 
        js_OpenJanelaIframe('top.corpo','db_iframe_liclicita','func_liclicita.php?pesquisa_chave='+document.form1.l20_codigo.value+'&funcao_js=parent.js_mostraliclicita','Pesquisa',false);
     }else{
       document.form1.l20_codigo.value = ''; 
     }
  }
}
function js_mostraliclicita(chave,erro){
  document.form1.l20_codigo.value = chave; 
  if(erro==true){ 
    document.form1.l20_codigo.value = ''; 
    document.form1.l20_codigo.focus(); 
  }
}
function js_mostraliclicita1(chave1,chave2){
  document.form1.l20_codigo.value = chave1;
  db_iframe_liclicita.hide();
}
</script>

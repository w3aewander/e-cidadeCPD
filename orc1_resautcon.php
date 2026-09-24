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
include("classes/db_orcreserva_classe.php");
include("dbforms/db_funcoes.php");
include("classes/db_orcdotacao_classe.php");
include("libs/db_liborcamento.php");      // funções do orçamento
include ("classes/db_orcprojativ_classe.php");
db_postmemory($HTTP_POST_VARS);

$clorcreserva = new cl_orcreserva;
$clorcdotacao = new cl_orcdotacao;  //instancia dotação

function testa($var){
  echo "<pre>";
  print_r($var);
  echo "</pre>";
}

function buscaDados($ano, $fonte, $estrutural){
  //$sql = pg_query("SELECT o58_coddot, o58_projativ, o58_valor, o56_elemento, o58_codigo FROM orcdotacao INNER JOIN orcelemento ON o56_codele = o58_codele AND o56_anousu = o58_anousu WHERE o58_anousu = {$ano} AND o58_codigo = {$fonte} AND o56_elemento LIKE '{$estrutural}' ORDER BY o58_projativ");
  $sql = pg_query("SELECT o58_coddot, o58_projativ, o58_valor, o56_elemento, o58_codigo, o58_orgao, o58_unidade, o58_funcao, o58_subfuncao, o58_programa, o58_codele, o58_concarpeculiar FROM orcdotacao INNER JOIN orcelemento ON o56_codele = o58_codele AND o56_anousu = o58_anousu WHERE o58_anousu = {$ano} AND o58_codigo = {$fonte} AND o56_elemento LIKE '{$estrutural}' ORDER BY o58_projativ");
  $resultado = pg_fetch_all($sql);
  return $resultado;
}

function retornaSequencial(){
  $sql = pg_query("SELECT nextval('orcreserva_o80_codres_seq')");
  $resultado = pg_fetch_all($sql);
  return $resultado[0]["nextval"];
}

function buscaReserva($coddot, $ano){
  $sql = pg_query("SELECT * FROM orcreserva INNER JOIN orcreservager ON o84_codres = o80_codres WHERE o80_coddot = {$coddot} AND o80_anousu = {$ano} AND o80_descr = 'Reserva Automática de Contingenciamento.'");
  $resultado = pg_fetch_all($sql);
  return $resultado[0];
  
}

if($_POST){

  $ano = $_POST["xexe"];
  $fonte = $_POST["xo58_codigo"];
  $estrutural = $_POST["dotas"] . "%";
  $perc = $_POST["preserva"];
  
  $datasistema = date("Y-m-d", db_getsession("DB_datausu"));
  $anosistema = db_getsession("DB_anousu");

  if(empty($ano) || empty($fonte) || empty($estrutural) || empty($perc)){
    echo "<script>alert('Todos os campos são obrigatórios');</script>";
  }else{
    if($_POST["tipo"] == "atual"){
      $idusuario = db_getsession("DB_id_usuario");
      $datafinal = $ano . "-12-31";    
      //$datahoje = "2025-01-01";
      $datahoje = $ano . "-01-01";
      $dados = buscaDados($ano, $fonte, $estrutural);
      
    
      $guardaprojativ = "";
      //testa($dados); die("Confere");
      foreach($dados as $linha){
        $coddot = $linha["o58_coddot"];
        $chave = $linha["o58_orgao"] . $linha["o58_unidade"] . $linha["o58_funcao"] . $linha["o58_subfuncao"] . $linha["o58_programa"] . $linha["o58_projativ"] . $linha["o58_codele"] . $linha["o58_codigo"] . $linha["o58_concarpeculiar"];
        //if($coddot != 677299){continue;}
        
        $elemento = $linha["o56_elemento"];
        //if($guardaprojativ != $linha["o58_projativ"]){
        if($guardaprojativ != $chave){
          pg_query("delete from orcreservaatual where o99_anousu = {$ano} and o99_projativ = {$linha['o58_projativ']} and o99_codigo = {$fonte} AND o99_coddot = {$coddot} AND o99_elemento = '{$elemento}'");
          //echo "delete from orcreservaatual where o99_anousu = {$ano} and o99_projativ = {$linha['o58_projativ']} and o99_codigo = {$fonte} AND o99_coddot = {$coddot} AND o99_elemento = '{$elemento}'"; echo "<br>";

          pg_query("insert into orcreservaatual( o99_anousu ,o99_projativ ,o99_codigo, o99_perc, o99_coddot, o99_elemento) values({$ano} ,{$linha['o58_projativ']} ,{$fonte},0{$perc}, {$coddot}, '{$elemento}')");
          //echo "insert into orcreservaatual( o99_anousu ,o99_projativ ,o99_codigo, o99_perc, o99_coddot, o99_elemento) values({$ano} ,{$linha['o58_projativ']} ,{$fonte},0{$perc}, {$coddot}, '{$elemento}')"; echo "<br>";
          $guardaprojativ = $chave;
        }//fim do if
        
        $valorcalculado = (float)$linha["o58_valor"] * ($perc/100);      
        $tipo = "Mês:01";
        


        $sqlpg = pg_query("SELECT substr(fc_dotacaosaldo($ano, $coddot, 2, '{$datafinal}', '{$datafinal}'), 137, 12)::float8");
        $vatual = pg_fetch_all($sqlpg);
        $vatual = $vatual[0]["substr"];
        
        //Buscar a reserva
        $dadosreserva = buscaReserva($coddot, $ano);
        
        //Somar com o vatual
        $novovalor = $dadosreserva["o80_valor"] + $vatual;
        $seqreserva = $dadosreserva["o80_codres"];
        if($seqreserva){
          //Update na reserva
          pg_query("DELETE FROM orcreservager WHERE o84_codres = {$seqreserva}");
          pg_query("DELETE FROM orcreserva WHERE o80_codres = {$seqreserva}");

          //pg_query("UPDATE orcreserva SET o80_valor = {$novovalor} WHERE o80_codres = {$seqreserva}");
          //echo "UPDATE orcreserva SET o80_valor = {$novovalor} WHERE o80_codres = {$seqreserva}"; echo "<br>";

        }
        
        $sequencial = retornaSequencial();

        pg_query("insert into orcreserva( o80_codres ,o80_anousu ,o80_coddot ,o80_dtfim ,o80_dtini ,o80_dtlanc ,o80_valor ,o80_descr ) values ( {$sequencial} ,{$ano} ,{$linha['o58_coddot']} ,'{$datafinal}' ,'{$datahoje}' ,'{$datahoje}' ,{$valorcalculado} ,'Reserva Automática de Contingenciamento.')");
        //echo "insert into orcreserva( o80_codres ,o80_anousu ,o80_coddot ,o80_dtfim ,o80_dtini ,o80_dtlanc ,o80_valor ,o80_descr ) values ( {$sequencial} ,{$ano} ,{$linha['o58_coddot']} ,'{$datafinal}' ,'{$datahoje}' ,'{$datahoje}' ,{$valorcalculado} ,'Reserva Automática de Contingenciamento.')"; echo "<br>";

        pg_query("insert into orcreservager( o84_codres ,o84_data ,o84_id_usuario ,o84_tipo ,o84_perc ) values ( {$sequencial} ,'{$datahoje}' ,{$idusuario} ,'{$tipo}',{$perc} )");
        //echo "insert into orcreservager( o84_codres ,o84_data ,o84_id_usuario ,o84_tipo ,o84_perc ) values ( {$sequencial} ,'{$datahoje}' ,{$idusuario} ,'{$tipo}',{$perc} )"; echo "<br>";
          //echo "<hr>";
        
        
        
        
        
        
      }//fim do foreach  
      
      echo "<script>alert('Dados Processados');</script>";

    }else{
      
      $idusuario = db_getsession("DB_id_usuario");
      $datafinal = $ano . "-12-31";    
      $datahoje = "2025-01-01";
      $dados = buscaDados($ano, $fonte, $estrutural);
    
      $guardaprojativ = "";    
      foreach($dados as $linha){
        $sqlapaga = "";
        $chave = $linha["o58_orgao"] . $linha["o58_unidade"] . $linha["o58_funcao"] . $linha["o58_subfuncao"] . $linha["o58_programa"] . $linha["o58_projativ"] . $linha["o58_codele"] . $linha["o58_codigo"] . $linha["o58_concarpeculiar"];
        //if($guardaprojativ != $linha["o58_projativ"]){
        if($guardaprojativ != $chave){
          $sqlapaga .= "delete from orcreserprev where o33_anousu = {$ano} and o33_projativ = {$linha['o58_projativ']} and o33_codigo = {$fonte};";
          pg_query("delete from orcreserprev where o33_anousu = {$ano} and o33_projativ = {$linha['o58_projativ']} and o33_codigo = {$fonte}");
          pg_query("insert into orcreserprev( o33_anousu ,o33_projativ ,o33_codigo ,o33_mes ,o33_perc ,o33_valor ) values ( {$ano} ,{$linha['o58_projativ']} ,{$fonte} ,1 ,0{$perc} ,0 )");
          pg_query("insert into orcreserprev( o33_anousu ,o33_projativ ,o33_codigo ,o33_mes ,o33_perc ,o33_valor ) values ( {$ano} ,{$linha['o58_projativ']} ,{$fonte} ,2 ,00 ,0 )");
          pg_query("insert into orcreserprev( o33_anousu ,o33_projativ ,o33_codigo ,o33_mes ,o33_perc ,o33_valor ) values ( {$ano} ,{$linha['o58_projativ']} ,{$fonte} ,3 ,00 ,0 )");
          pg_query("insert into orcreserprev( o33_anousu ,o33_projativ ,o33_codigo ,o33_mes ,o33_perc ,o33_valor ) values ( {$ano} ,{$linha['o58_projativ']} ,{$fonte} ,4 ,00 ,0 )");
          pg_query("insert into orcreserprev( o33_anousu ,o33_projativ ,o33_codigo ,o33_mes ,o33_perc ,o33_valor ) values ( {$ano} ,{$linha['o58_projativ']} ,{$fonte} ,5 ,00 ,0 )");
          pg_query("insert into orcreserprev( o33_anousu ,o33_projativ ,o33_codigo ,o33_mes ,o33_perc ,o33_valor ) values ( {$ano} ,{$linha['o58_projativ']} ,{$fonte} ,6 ,00 ,0 )");
          pg_query("insert into orcreserprev( o33_anousu ,o33_projativ ,o33_codigo ,o33_mes ,o33_perc ,o33_valor ) values ( {$ano} ,{$linha['o58_projativ']} ,{$fonte} ,7 ,00 ,0 )");
          pg_query("insert into orcreserprev( o33_anousu ,o33_projativ ,o33_codigo ,o33_mes ,o33_perc ,o33_valor ) values ( {$ano} ,{$linha['o58_projativ']} ,{$fonte} ,8 ,00 ,0 )");
          pg_query("insert into orcreserprev( o33_anousu ,o33_projativ ,o33_codigo ,o33_mes ,o33_perc ,o33_valor ) values ( {$ano} ,{$linha['o58_projativ']} ,{$fonte} ,9 ,00 ,0 )");
          pg_query("insert into orcreserprev( o33_anousu ,o33_projativ ,o33_codigo ,o33_mes ,o33_perc ,o33_valor ) values ( {$ano} ,{$linha['o58_projativ']} ,{$fonte} ,10 ,00 ,0 )");
          pg_query("insert into orcreserprev( o33_anousu ,o33_projativ ,o33_codigo ,o33_mes ,o33_perc ,o33_valor ) values ( {$ano} ,{$linha['o58_projativ']} ,{$fonte} ,11 ,00 ,0 )");
          pg_query("insert into orcreserprev( o33_anousu ,o33_projativ ,o33_codigo ,o33_mes ,o33_perc ,o33_valor ) values ( {$ano} ,{$linha['o58_projativ']} ,{$fonte} ,12 ,00 ,0 )");
        
          $sqlapaga .= "delete from orcprevdesp where o35_anousu = {$ano} and o35_projativ = {$linha['o58_projativ']} and o35_codigo = {$fonte};";
          pg_query("delete from orcprevdesp where o35_anousu = {$ano} and o35_projativ = {$linha['o58_projativ']} and o35_codigo = {$fonte}");
          pg_query("insert into orcprevdesp( o35_anousu ,o35_projativ ,o35_codigo ,o35_mes ,o35_perc ,o35_valor ) values ( {$ano} ,{$linha['o58_projativ']} ,{$fonte} ,1 ,0{$perc} ,0 )");
          pg_query("insert into orcprevdesp( o35_anousu ,o35_projativ ,o35_codigo ,o35_mes ,o35_perc ,o35_valor ) values ( {$ano} ,{$linha['o58_projativ']} ,{$fonte} ,2 ,00 ,0 )");
          pg_query("insert into orcprevdesp( o35_anousu ,o35_projativ ,o35_codigo ,o35_mes ,o35_perc ,o35_valor ) values ( {$ano} ,{$linha['o58_projativ']} ,{$fonte} ,3 ,00 ,0 )");
          pg_query("insert into orcprevdesp( o35_anousu ,o35_projativ ,o35_codigo ,o35_mes ,o35_perc ,o35_valor ) values ( {$ano} ,{$linha['o58_projativ']} ,{$fonte} ,4 ,00 ,0 )");
          pg_query("insert into orcprevdesp( o35_anousu ,o35_projativ ,o35_codigo ,o35_mes ,o35_perc ,o35_valor ) values ( {$ano} ,{$linha['o58_projativ']} ,{$fonte} ,5 ,00 ,0 )");
          pg_query("insert into orcprevdesp( o35_anousu ,o35_projativ ,o35_codigo ,o35_mes ,o35_perc ,o35_valor ) values ( {$ano} ,{$linha['o58_projativ']} ,{$fonte} ,6 ,00 ,0 )");
          pg_query("insert into orcprevdesp( o35_anousu ,o35_projativ ,o35_codigo ,o35_mes ,o35_perc ,o35_valor ) values ( {$ano} ,{$linha['o58_projativ']} ,{$fonte} ,7 ,00 ,0 )");
          pg_query("insert into orcprevdesp( o35_anousu ,o35_projativ ,o35_codigo ,o35_mes ,o35_perc ,o35_valor ) values ( {$ano} ,{$linha['o58_projativ']} ,{$fonte} ,8 ,00 ,0 )");
          pg_query("insert into orcprevdesp( o35_anousu ,o35_projativ ,o35_codigo ,o35_mes ,o35_perc ,o35_valor ) values ( {$ano} ,{$linha['o58_projativ']} ,{$fonte} ,9 ,00 ,0 )");
          pg_query("insert into orcprevdesp( o35_anousu ,o35_projativ ,o35_codigo ,o35_mes ,o35_perc ,o35_valor ) values ( {$ano} ,{$linha['o58_projativ']} ,{$fonte} ,10 ,00 ,0 )");
          pg_query("insert into orcprevdesp( o35_anousu ,o35_projativ ,o35_codigo ,o35_mes ,o35_perc ,o35_valor ) values ( {$ano} ,{$linha['o58_projativ']} ,{$fonte} ,11 ,00 ,0 )");
          pg_query("insert into orcprevdesp( o35_anousu ,o35_projativ ,o35_codigo ,o35_mes ,o35_perc ,o35_valor ) values ( {$ano} ,{$linha['o58_projativ']} ,{$fonte} ,12 ,00 ,0 )");
        
          $guardaprojativ = $chave; //$linha["o58_projativ"];      
        }//fim do if
      
        $sequencial = retornaSequencial();
        $valorcalculado = (float)$linha["o58_valor"] * ($perc/100);      
        $tipo = "Mês:01";

        pg_query("insert into orcreserva( o80_codres ,o80_anousu ,o80_coddot ,o80_dtfim ,o80_dtini ,o80_dtlanc ,o80_valor ,o80_descr ) values ( {$sequencial} ,{$ano} ,{$linha['o58_coddot']} ,'{$datafinal}' ,'{$datahoje}' ,'{$datahoje}' ,{$valorcalculado} ,'Reserva Automática de Contingenciamento.')");
        pg_query("insert into orcreservager( o84_codres ,o84_data ,o84_id_usuario ,o84_tipo ,o84_perc ) values ( {$sequencial} ,'{$datahoje}' ,{$idusuario} ,'{$tipo}',{$perc} )");
        $sqlapaga .= "DELETE FROM orcreservager WHERE o84_codres = {$sequencial}; DELETE FROM orcreserva WHERE o80_codres = {$sequencial};";      
        $hoje = date("Y-m-d");
        $hora = date("H:i");
        pg_query("INSERT INTO historicoconau(dia, hora, idusuario, comandos) VALUES('{$hoje}', '{$hora}', {$idusuario}, '{$sqlapaga}' )");
      }//fim do foreach  
      echo "<script>alert('Dados Processados');</script>";
    }//fim do else saldo inicial
  }//fim do else campos preenchidos
}//if do post

?>
<html>
<head>
<title>Microsist</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<meta http-equiv="Expires" CONTENT="0">
<script language="JavaScript" type="text/javascript" src="scripts/scripts.js"></script>
<link href="estilos.css" rel="stylesheet" type="text/css">


</head>
<body bgcolor=#CCCCCC leftmargin="0" topmargin="0" marginwidth="0" marginheight="0" >
<table width="790" border="0" cellpadding="0" cellspacing="0" bgcolor="#5786B2">
  <tr> 
    <td width="360" height="18">&nbsp;</td>
    <td width="263">&nbsp;</td>
    <td width="25">&nbsp;</td>
    <td width="140">&nbsp;</td>
  </tr>
</table>

    <center>
      <div style="width:600px;margin-top:25px"> 
    <fieldset>
        <legend>Parâmetros a serem usados para efetuar Reserva Automática de Contingenciamento</legend>
        <form action="" method="post">
        <table>
          
        <?php /* ?>
        <tr>
          <td nowrap>
            <? db_ancora("Ação: Atividade/Projeto:","js_pesquisao58_projativ(true);",$db_opcao);?>
          </td>
          <td>
            <? db_input('xo58_projativ',11,$Io58_projativ,true,'text',$db_opcao," onchange='js_pesquisao58_projativ(false);'") ?>
            <? db_input('xo55_descr',55,$Io55_descr,true,'text',3,'') ?>
          </td>
        </tr>
        <?php */ ?>

        <tr>
          <td nowrap>
            <? db_ancora("Fonte de Recursos:","js_pesquisao58_codigo(true);",$db_opcao); ?>
          </td>
          <td> 
            <? db_input('xo58_codigo',11,$Io58_codigo,true,'text',$db_opcao," onchange='js_pesquisao58_codigo(false);'") ?>
            <? db_input('xo15_descr',55,$Io15_descr,true,'text',3,'') ?>
          </td>
        </tr>

        <tr>
          <td nowrap><b>Natureza da Despesa:</b></td>
          <td colspan="2"><input type="text" name="dotas" id="dotas" min="5" max="7" size="69" required></td>
        </tr>
        
        

        <tr>
          <td nowrap><b>% a ser reservado:</b></td>
          <td colspan="2"><input type="text" name="preserva" id="preserva" size="5" required>%</td>
        </tr>

        

        <tr>
          <td nowrap><b>Exercício:</b></td>
          <td colspan="2"><input type="text" name="xexe" id="xexe" size="5" required>          
          </td>
        </tr>

        <tr>
          <td><b>Reservar com base no:</b></td>
          <td>
            <?php /* ?><input type="radio" name="tipo" value="inicial" required>Saldo Inicial <?php */ ?>
            <input type="radio" name="tipo" value="atual" required>Saldo Atual
          </td>
        </tr>
        
        </table>

        <input type="submit" value="Processar">
        <h5><i>O processamento demora alguns segundos.</i></h5>
        </form>
    </fieldset>
</div>
	
    </center>
	 



<?php 
if($_POST){
  echo "<script>document.getElementById('xo58_codigo').value = ''</script>";
  echo "<script>document.getElementById('xo15_descr').value = ''</script>";
}

?>
<?
db_menu(db_getsession("DB_id_usuario"),db_getsession("DB_modulo"),db_getsession("DB_anousu"),db_getsession("DB_instit"));
?>
</body>
</html>

<script>







function js_pesquisao58_projativ(mostra){
  if(mostra==true){
    js_OpenJanelaIframe('','db_iframe_orcprojativ','func_orcprojativ.php?insti=1&situacao=ppa&funcao_js=parent.js_mostraorcprojativ1|o55_projativ|o55_descr','Pesquisa',true);
  }else{
    js_OpenJanelaIframe('','db_iframe_orcprojativ','func_orcprojativ.php?insti=1situacao=ppa&&pesquisa_chave='+document.getElementById("xo58_projativ").value+'&funcao_js=parent.js_mostraorcprojativ','Pesquisa',false);
  }
}
function js_mostraorcprojativ(chave,erro){
  document.getElementById("xo55_descr").value = chave; 
  if(erro==true){ 
    document.getElementById("xo58_projativ").focus(); 
    document.getElementById("xo58_projativ").value = ''; 
  }
}
function js_mostraorcprojativ1(chave1,chave2){
  document.getElementById("xo58_projativ").value = chave1;
  document.getElementById("xo55_descr").value = chave2;
  db_iframe_orcprojativ.hide();
}
function js_mostraorcprojativ(chave,erro){
  document.form1.o55_descr.value = chave; 
  if(erro==true){ 
    document.getElementById("xo58_projativ").focus(); 
    document.getElementById("xo58_projativ").value = ''; 
  }
}
function js_mostraorcprojativ1(chave1,chave2){
  document.getElementById("xo58_projativ").value = chave1;
  document.getElementById("xo55_descr").value = chave2;
  db_iframe_orcprojativ.hide();
}

function js_pesquisao58_codigo(mostra){
  if(mostra==true){
    js_OpenJanelaIframe('','db_iframe_orctiporec','func_orctiporec.php?funcao_js=parent.js_mostraorctiporec1|o15_codigo|o15_descr','Pesquisa',true);
  }else{
    js_OpenJanelaIframe('','db_iframe_orctiporec','func_orctiporec.php?pesquisa_chave='+document.getElementById("xo58_codigo").value+'&funcao_js=parent.js_mostraorctiporec','Pesquisa',false);
  }
}
function js_mostraorctiporec(chave,erro){
  document.getElementById("xo15_descr").value = chave; 
  if(erro==true){ 
    document.getElementById("xo58_codigo").focus(); 
    document.getElementById("xo58_codigo").value = ''; 
  }
}
function js_mostraorctiporec1(chave1,chave2){
  document.getElementById("xo58_codigo").value = chave1;
  document.getElementById("xo15_descr").value = chave2;
  db_iframe_orctiporec.hide();
}

var input = document.getElementById('dotas');
input.addEventListener('input', function() {
  //this.value = this.value.replace(/[^0-9 \,]/, '');
  this.value = this.value.replace(/[^0-9]/, '');
});


var input = document.getElementById('preserva');
input.addEventListener('input', function() {
  this.value = this.value.replace(/[^0-9]/, '');
});

</script>

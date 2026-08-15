<?
/*
 *     E-cidade Software Publico para Gestao Municipal
 *  Copyright (C) 2014  DBselller Servicos de Informatica
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

require_once(modification("libs/db_stdlib.php"));
require_once(modification("libs/db_conecta.php"));
require_once(modification("libs/db_usuariosonline.php"));
require_once(modification("libs/db_liborcamento.php"));
require_once(modification("classes/db_lote_classe.php"));
require_once(modification("dbforms/db_funcoes.php"));
require_once(modification("dbforms/db_classesgenericas.php"));
require_once(modification("classes/db_empempenho_classe.php"));
include("classes/db_orcdotacao_classe.php");

//---  parser POST/GET
parse_str($_SERVER["QUERY_STRING"]);
db_postmemory($_POST);

//---- instancia classes
$clempempenho = new cl_empempenho;
$clselorcdotacao = new cl_selorcdotacao;
$clorcdotacao = new cl_orcdotacao;
$clorcdotacao->rotulo->label();
$aux = new cl_arquivo_auxiliar;

//--- cria rotulos e labels
$clempempenho->rotulo->label();

//----
//----
$cllote = new cl_lote;
$cliframe_seleciona = new cl_iframe_seleciona;

$cllote->rotulo->label();
$clrotulo = new rotulocampo;
$clrotulo->label("z01_nome");

if (!isset($testdt)){
  $testdt='sem';
}

if (!isset($desdobramento)){
  $desdobramento = "true";
}

$anousu = db_getsession("DB_anousu");
?>
<html>
<head>
  <title>Microsist</title>
  <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
  <meta http-equiv="Expires" CONTENT="0">
  <script language="JavaScript" type="text/javascript" src="scripts/scripts.js"></script>
  <link href="estilos.css" rel="stylesheet" type="text/css">
  <style>

  </style>
</head>
<body bgcolor=#CCCCCC bgcolor="#CCCCCC">
<br/>
<center>
  <?php if($anousu >= 2021) : ?>
      <form id="form1" name="form1" method="post" action="emp2_empliqpag032022_novo.php">
  <?php else:  ?>
      <form id="form1" name="form1" method="post" action="emp2_empliqpag03_novo.php">
  <?php endif; ?>
    <input  name="filtra_despesa" id="filtra_despesa" type="hidden" value="" >
    <fieldset style="width: 800px">
      <legend><b>Movimentação de Empenho</b></legend>
      <?php
      /*
       * Campos HIDDEN
       */
      db_input('testdt',10,"",true,"hidden",1);
      //db_input('listacredor',10,"",true,"hidden",1);
      //db_input('listahist',10,"",true,"hidden",1);
      //db_input('listaevento',10,"",true,"hidden",1);
      //db_input('listaitem',10,"",true,"hidden",1);
      //db_input('ver',10,"",true,"hidden",1);
      //db_input('verhist',10,"",true,"hidden",1);
      //db_input('veritem',10,"",true,"hidden",1);
      //db_input('vercom',10,"",true,"hidden",1);
      //db_input('listacom',10,"",true,"hidden",1);
      db_input('datacredor',10,"",true,"hidden",1);
      db_input('datacredor1',10,"",true,"hidden",1);
      db_input('dataesp11',10,"",true,"hidden",1);
      db_input('dataesp22',10,"",true,"hidden",1);
      //db_input('hist',10,"",true,"hidden",1);
      //db_input('mostraritem',10,"",true,"hidden",1);
      //db_input('mostrarobs',10,"",true,"hidden",1);
      //db_input('mostralan',10,"",true,"hidden",1);
      //db_input('agrupar',10,"",true,"hidden",1);
      //db_input('listasub',10,"",true,"hidden",1);
      db_input("desdobramento",10,0,true,"hidden",3);
      //db_input("listaconcarpeculiar",10,0,true,"hidden",3);
      //db_input("verconcarpeculiar",  10,0,true,"hidden",3);
      db_input("orgaos",  10,0,true,"hidden",3);
      db_input("vernivel",  10,0,true,"hidden",3);
      ?>
      <table style="width: 100%" border='0'>
        <tr>
          <td colspan="4" align="center">
            <fieldset style="width: 98%; border-left: none; border-bottom: none; border-right: none;">
              <legend><b>Instituições</b></legend>
              <?php
              db_selinstit('', 500, 130);
              ?>
            </fieldset>
          </td>
        </tr>
        <tr>
          <td width="140">
            <b>Data de Emissão:</b>
          </td>
          <td colspan="3">
            <?php
            $resultmin = db_query("select e60_emiss from empempenho order by e60_emiss limit 1");
            db_fieldsmemory($resultmin,0);
            $dia=substr($e60_emiss,8,2);
            $dia="01";
            $mes=substr($e60_emiss,5,2);
            $mes="01";
            $ano=substr($e60_emiss,0,4);
            $ano= db_getsession("DB_anousu");
            $dia2=date("d",db_getsession("DB_datausu"));
            $mes2=date("m",db_getsession("DB_datausu"));
            $ano2= db_getsession("DB_anousu");
            db_inputdata('data1',@$dia,@$mes,@$ano,true,'text',1,"");
            echo " a ";
            db_inputdata('data11',@$dia2,@$mes2,@$ano2,true,'text',1,"");
            ?>
          </td>
        </tr>
          <tr>
              <td><b>Faixa de Valor:</b></td>
              <td colspan="3">
                  <?php
                  db_input("nValorEmpenhoInicial", 10, false, true, 'text', 1, "onkeypress='return js_mask(event,\"0-9|,|-\");'");
                  echo " <b>até</b> ";
                  db_input("nValorEmpenhoFinal", 10, false,   true, 'text', 1, "onkeypress='return js_mask(event,\"0-9|,|-\");'");
                  ?>
              </td>
          </tr>
          <tr>
              <td><b>Mostrar OP's:</b></td>
              <td>
                  <?php
                  $aMostrarEmpenho = array("t" => "Todas", "p" => "Somente pagas", "n" => "Somente não pagas", "gnp" => "Geral Não Pago");  
                  
                  /*if($anousu >= 2021){
                    $aMostrarEmpenho = array("t" => "Todas", "p" => "Somente pagas", "n" => "Somente não pagas", "gnp" => "Geral Não Pago");  
                  }else {
                    $aMostrarEmpenho = array("t" => "Todas", "p" => "Somente pagas", "n" => "Somente não pagas");
                  }*/
                  //$aMostrarEmpenho = array("t" => "Todas", "p" => "Somente pagas", "n" => "Somente não pagas", "gnp" => "Geral Não Pago");
                  db_select("ops",$aMostrarEmpenho,true,2);
                  ?>
              </td>
          </tr>
          <tr>
              <td><?=$Lo58_codigo?></td>
              <td>
                  <?
                  $txtinstit = " ";
                  //echo(db_getsession("DB_instit"));
                  if(db_getsession("DB_instit") != "1")
                      $txtinstit =  " and o58_instit=".db_getsession("DB_instit");
                  $sSqlBuscaELemento = $clorcdotacao->sql_query(null,null,"distinct o15_codigo,o15_descr","o15_codigo","o58_anousu=".db_getsession("DB_anousu")." ".$txtinstit." ".$dbwhere);
                  //echo($sSqlBuscaELemento);
                  $result = $clorcdotacao->sql_record($sSqlBuscaELemento);
                  db_selectrecord("o58_codigo",$result,true,2,"","","",($clorcdotacao->numrows>1?"0":""));
                  ?>
              </td>
          </tr>

          <?php if($anousu >= 2021) : ?>
          <tr>
            <td><b>Certificação:</b></td>
            <td>
              <input type="radio" name="certificado" value="com">Com Certificado
              <input type="radio" name="certificado" value="sem" checked>Sem Certificado
            </td>
          </tr>
        <?php endif; ?>
        </table>
    </fieldset>
    <br>
    <input type="button" value="Relatório" onClick="js_emite(<?=$anousu?>)">
    <?php /* ?><input type="button" value="Relatório Novo (2022)" onClick="js_emite2(<?=$anousu?>)"><?php */ ?>
      <br>
      <br>
      <input type="button" value="Exportar arquivo em CSV" onClick="js_exportar()">
</center>
</form>
<script>

  /*
  var datafim = document.getElementById("data11");
  var acao = document.getElementById("form1").action;
  datafim.addEventListener("change", function(){
    var ano = datafim.value.split("/");
    ano = ano[2];
    if(ano) < 2021{
      acao = "emp2_empliqpag03_novo.php";
    }
    console.log(ano);
    console.log(acao);
  }, false);
  */

  variavel = 1;

  function js_exportar(){

      var instits = document.form1.db_selinstit.value;
      document.form1.orgaos.value = parent.iframe_filtro.js_atualiza_variavel_retorno();

      jan = window.open('emp2_empliqpag06_novo.php?&instits='+instits.replace("-",",")+'&datacredor='+document.form1.data1_ano.value+'-'+document.form1.data1_mes.value+'-'+document.form1.data1_dia.value
          +'&datacredor1='+document.form1.data11_ano.value+'-'+document.form1.data11_mes.value+'-'+document.form1.data11_dia.value
          +'&orgaos='+document.form1.orgaos.value
          +'&ops='+document.form1.ops.value
          +'&o58_codigo='+document.form1.o58_codigo.value
          +'&nValorEmpenhoInicial='+document.form1.nValorEmpenhoInicial.value
          +'&nValorEmpenhoFinal='+document.form1.nValorEmpenhoFinal.value
          ,'','width='+(screen.availWidth-5)+',height='+(screen.availHeight-40)+',scrollbars=1,location=0 ');

      jan.moveTo(0,0);
  }
  
  
  function js_emite(anousu){
    //console.log(document.form1.data11_ano.value);    
    //console.log(document.form1.action);
    //return false;
    if(document.form1.data11_ano.value < 2021){
      document.form1.action = "emp2_empliqpag03_novo.php";
    }
    document.form1.datacredor.value=document.form1.data1_ano.value+'-'+document.form1.data1_mes.value+'-'+document.form1.data1_dia.value;
    document.form1.datacredor1.value=document.form1.data11_ano.value+'-'+document.form1.data11_mes.value+'-'+document.form1.data11_dia.value;
    document.form1.dataesp11.value=document.form1.data1_ano.value+'-'+document.form1.data1_mes.value+'-'+document.form1.data1_dia.value;
    document.form1.dataesp22.value=document.form1.data11_ano.value+'-'+document.form1.data11_mes.value+'-'+document.form1.data11_dia.value;    
    document.form1.orgaos.value = parent.iframe_filtro.js_atualiza_variavel_retorno();

    jan = window.open('','safo' + variavel,'width='+(screen.availWidth-5)+',height='+(screen.availHeight-40)+',scrollbars=1,location=0 ');
    document.form1.target = 'safo' + variavel++;
    setTimeout("document.form1.submit()",1000);
    return true;
  }

  function js_emite2(anousu){
    document.form1.datacredor.value=document.form1.data1_ano.value+'-'+document.form1.data1_mes.value+'-'+document.form1.data1_dia.value;
    document.form1.datacredor1.value=document.form1.data11_ano.value+'-'+document.form1.data11_mes.value+'-'+document.form1.data11_dia.value;
    document.form1.dataesp11.value=document.form1.data1_ano.value+'-'+document.form1.data1_mes.value+'-'+document.form1.data1_dia.value;
    document.form1.dataesp22.value=document.form1.data11_ano.value+'-'+document.form1.data11_mes.value+'-'+document.form1.data11_dia.value;    
    document.form1.orgaos.value = parent.iframe_filtro.js_atualiza_variavel_retorno();

    jan = window.open('','safo' + variavel,'width='+(screen.availWidth-5)+',height='+(screen.availHeight-40)+',scrollbars=1,location=0 ');
    document.form1.target = 'safo' + variavel++;
    setTimeout("document.form1.submit()",1000);
    return true;
  }


  function js_mandadados(){

    jan = window.open('emp2_empliqpag03_novo.php?datacredor='+document.form1.data1_ano.value+'-'+document.form1.data1_mes.value+'-'+document.form1.data1_dia.value
      +'&datacredor1='+document.form1.data11_ano.value+'-'+document.form1.data11_mes.value+'-'+document.form1.data11_dia.value
      +'width='+(screen.availWidth-5)+',height='+(screen.availHeight-40)+',scrollbars=1,location=0 ');
   
    jan.moveTo(0,0);

  }


</script>

</center>
</body>
</html>
<?php
/*
 *     E-cidade Software Publico para Gestao Municipal                
 *  Copyright (C) 2013  DBselller Servicos de Informatica             
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

require_once("libs/db_stdlib.php");
require_once("libs/db_conecta.php");
require_once("libs/db_sessoes.php");
require_once("libs/db_usuariosonline.php");
require_once("libs/db_liborcamento.php");
require_once("dbforms/db_funcoes.php");
require_once("classes/db_orcreserva_classe.php"); // classe da reserva

$clorcreserva = new cl_orcreserva;
$clorcreserva->rotulo->label();
$clrotulo     = new rotulocampo;
$clrotulo->label("o58_coddot");
$clrotulo->label("o83_autori");
$clrotulo->label("DBtxtmes");
$clrotulo->label("DBtxtmesacumulado");
$clrotulo->label("DBtxtperiodoini");
$clrotulo->label("DBtxtperiodofim");

parse_str($HTTP_SERVER_VARS["QUERY_STRING"]);
db_postmemory($_POST);
db_postmemory($_GET);

if (isset($anousu) and $anousu!="") {
  $anousu = $anousu;
} else {
  $anousu = db_getsession("DB_anousu");
}


$DBtxtperiodoini = date("Y-m-d",db_getsession("DB_datausu"));
$DBtxtperiodofim = date("Y-m-d",db_getsession("DB_datausu"));

$result = db_dotacaosaldo(8, 2, 2, true, " o58_anousu = {$anousu}", $anousu, $DBtxtperiodoini, $DBtxtperiodofim);

$sdot_ini = 0;
$ssaldo_anterior = 0;
$ssuplementado_acumulado = 0;
$sreduzido_acumulado = 0;
$sempenhado_acumulado = 0;
$sanulado_acumulado = 0;
$sliquidado_acumulado = 0;
$spago_acumulado = 0;
$sreservado = 0;

for($i=0;$i<pg_num_rows($result);$i++){

  db_fieldsmemory($result,$i);

  //<td> Saldo Inicial:</td>
  $sdot_ini += $dot_ini;
  //<td> Saldo Anterior:</td>
  $ssaldo_anterior += $saldo_anterior;
  //<td> Suplementação:</td>
  $ssuplementado_acumulado += $suplementado_acumulado;
  //<td> Redução      :</td>
  $sreduzido_acumulado += $reduzido_acumulado;
  //<td> Empenhado    :</td>
  $sempenhado_acumulado += $empenhado_acumulado;
  //<td> Anulado      :</td>
  $sanulado_acumulado += $anulado_acumulado;
  //<td> Liquidado    :</td>
  $sliquidado_acumulado += $liquidado_acumulado;
  //<td> Pago         :</td>
  $spago_acumulado += $pago_acumulado;
  //<td> A Pagar Liquidado:</td>
  //<td align="right"> <?php echo db_formatar($liquidado_acumulado-$pago_acumulado,'f')
  //<td> A Pagar Emp.:</td>
  //<td align="right"> <?php echo db_formatar(($empenhado_acumulado-$anulado_acumulado-$liquidado_acumulado),'f')
  //<td> Saldo Dotação:</td>
  //<td align="right"> <?php echo db_formatar(($dot_ini+$suplementado_acumulado-$reduzido_acumulado)-$empenhado_acumulado+$anulado_acumulado,'f')
  //<td> Reservado :</td>
  $sreservado += $reservado;
  //<td> Saldo Disponível:</td>
  //<td align="right"> <?php echo db_formatar(($dot_ini+$suplementado_acumulado-$reduzido_acumulado)-$empenhado_acumulado+$anulado_acumulado-$reservado,'f')

}

?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link href="estilos.css" rel="stylesheet" type="text/css">
<style>
.descricao {
   height : 40px
}
</style>
<script language="JavaScript" type="text/javascript" src="scripts/scripts.js"></script>
</head>
<body bgcolor=#CCCCCC leftmargin="0" topmargin="0" marginwidth="0" marginheight="0">
<form name="form1" method="post">
<table border="0"  align="center" cellspacing="0" bgcolor="#CCCCCC">
<tr>
<td valign="top">
</td>
<td>
</td>

<td valign="top">

<table border="1"  align="center" cellspacing="0" >

<tr class="descricao">
<td colspan="2" align="center" > Saldo Orcamentario</td>
</tr>


<tr>
<td> Saldo Inicial:</td>
<td align="right"> <?php echo db_formatar($sdot_ini,'f')?></td>
</tr>

<tr>
<td> Suplementação:</td>
<td align="right"> <?php echo db_formatar($ssuplementado_acumulado,'f')?>
</td>

</tr>
<tr>
<td> Redução      :</td>
<td align="right"> <?php echo db_formatar($sreduzido_acumulado,'f')?></td>

</tr>
<tr>
<td> Empenhado    :</td>
<td align="right"> <?php echo db_formatar($sempenhado_acumulado,'f')?></td>

</tr>
<tr>
<td> Anulado      :</td>
<td align="right"> <?php echo db_formatar($sanulado_acumulado,'f')?></td>

</tr>
<tr>
<td> Liquidado    :</td>
<td align="right"> <?php echo db_formatar($sliquidado_acumulado,'f')?></td>

</tr>
<tr>
<td> Pago         :</td>
<td align="right"> <?php echo db_formatar($spago_acumulado,'f')?></td>

</tr>
<tr>
<td> A Pagar Liquidado:</td>
<td align="right"> <?php echo db_formatar($sliquidado_acumulado-$spago_acumulado,'f')?></td>

</tr>
<tr>
<td> A Pagar Emp.:</td>
<td align="right"> <?php echo db_formatar(($sempenhado_acumulado-$sanulado_acumulado-$sliquidado_acumulado),'f')?></td>

</tr>
<tr>
<td> Saldo Dotação:</td>
<td align="right"> <?php echo db_formatar(($sdot_ini+$ssuplementado_acumulado-$sreduzido_acumulado)-$sempenhado_acumulado+$sanulado_acumulado,'f')?></td>

</tr>
<tr>
<td> Reservado :</td>
<td align="right"> <?php echo db_formatar($sreservado,'f')?></td>

</tr>
<tr>
<td> Saldo Disponível:</td>
<td align="right"> <?php echo db_formatar(($sdot_ini+$ssuplementado_acumulado-$sreduzido_acumulado)-$sempenhado_acumulado+$sanulado_acumulado-$sreservado,'f')?></td>
</tr>
</table>
</td>
</tr>

</table>

</form>
</body>
</html>

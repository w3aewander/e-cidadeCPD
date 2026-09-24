<?php
/*
 *     E-cidade Software Publico para Gestao Municipal                
 *  Copyright (C) 2014  DBSeller Servicos de Informatica             
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

/*
   FUNÇÃO PARA LIBERAR A COTA MENSAL DO ORÇAMENTO PARA O CLIENTE VOLTA REDONDA 
   Autor:  Flávio Henrique
   Versão: 1.0.0
   Data:   29/06/2015
 */

require_once modification("libs/db_stdlib.php");
require_once modification("libs/db_conecta.php");
require_once modification("libs/db_utils.php");
require_once modification("libs/db_sessoes.php");
require_once modification("libs/db_usuariosonline.php");
require_once modification("libs/db_app.utils.php");
require_once modification("dbforms/db_funcoes.php");
require_once modification("libs/db_libcontabilidade.php");

$iAnoUsu   = db_getsession("DB_anousu");
$iUser	   = db_getsession("DB_id_usuario");
if ($iAnoUsu == 2015){
	$aListaPeriodos = array(0=>"Selecione", 
			1=>"JANEIRO", 
			2=>"FEVEREIRO",
			3=>"MARÇO",
			4=>"ABRIL",
			5=>"MAIO",
			6=>"JUNHO",
			7=>"JULHO",
			8=>"AGOSTO",
			9=>"SETEMBRO",
			10=>"OUTUBRO",
			11=>"NOVEMBRO",
			12=>"DEZEMBRO");
} else if ($iAnoUsu == 2016){
	$aListaPeriodos = array(0=>"Selecione",
		
}
$debug = true;
$bErro = false;
$sqlerro = false;

if (isset ($processar)) {
	db_query("begin");
	$bCotaLib  = "select to_char(o999_data,'dd/mm/YYYY') as datalanc, nome as usuario from orcamento.orccotamensal inner join db_usuarios ON o999_id_usuario = id_usuario  where o999_ano = $iAnoUsu and o999_mes = $periodo";
	$rsCotaLib = db_query($bCotaLib) or die("ERRO: $bCotaLib");
	if(pg_num_rows($rsCotaLib)==0){
		$tabela    = "w_cota_$periodo"._."$iAnoUsu"; // GERA NOME DA TABELA DA COTA QUE SERÁ PROCESSADA
		// ARRAY DA COTA PELO MÊS SELECIONADO
		if($iAnoUsu > '2015'){
			$cota = array(7=>10,8=>10,9=>10,10=>10,11=>10,12=>10);
			//EXECUTA A ROTINA PARA GERAÇÃO DA COTA
			$iGeraCota= "insert into orcamento.orcreservahistcota (select o80_codres, o80_anousu, o80_coddot, o80_dtfim, o80_dtini, o80_dtlanc, round(o80_valor,2), o80_descr,$periodo,$iAnoUsu from orcreserva where o80_descr = 'CONTINGENCIAMENTO' and o80_anousu = $iAnoUsu);
			create table $tabela as SELECT codres AS o80_codres,
			       coddot AS o80_coddot,
			       valor AS o80_valor,
			       descr AS o80_descr,
			       substr(fc_dotacaosaldo($iAnoUsu,coddot,2,'$iAnoUsu-01-01','$iAnoUsu-12-31'),133,12)::float8 as saldo,
			       (SELECT coalesce(hist_valor,valor)/$cota[$periodo]
				FROM orcreserva
				LEFT JOIN orcreservahistcota ON (o80_coddot = hist_coddot
					AND o80_anousu = hist_anousu)
				WHERE o80_anousu = anousu
				AND o80_coddot = coddot
				AND o80_descr = 'CONTINGENCIAMENTO'
				ORDER BY hist_mes LIMIT 1) AS mensal
				       FROM
				       (SELECT o80_codres AS codres,
					o80_coddot AS coddot,
					round(o80_valor,2) AS valor,
					o80_descr AS descr,
					o80_anousu AS anousu
					FROM orcreserva
					INNER JOIN orcdotacao ON o58_anousu = o80_anousu
					AND o58_coddot = o80_coddot
					/*INNER JOIN orcreservager ON o80_codres = o84_codres*/
					WHERE o80_anousu = $iAnoUsu
					AND o80_valor > 0
					AND o80_descr = 'CONTINGENCIAMENTO') AS x;
			update orcreserva set o80_valor = round(orcreserva.o80_valor - round ( $tabela.mensal,2 ),2) from $tabela where $tabela.o80_codres = orcreserva.o80_codres and orcreserva.o80_coddot not in (665222,630063,640085,640095,640100,665006,680110,640090,640165,640175,640180,640185,675100,660090,635060,640155,640160,608040,680085,625055,616035,617030,640145,645195,660115,640020);
			update orcreservager set o84_perc = $cota[$periodo] from $tabela where orcreservager.o84_codres = $tabela.o80_codres;";
		} else {
			$cota = array(1=>100,2=>90,3=>90,4=>80,5=>70,6=>60,7=>50,8=>42,9=>34,10=>26,11=>18,12=>10);
			//EXECUTA A ROTINA PARA GERAÇÃO DA COTA
			$iGeraCota= "insert into orcamento.orcreservahistcota (select o80_codres, o80_anousu, o80_coddot, o80_dtfim, o80_dtini, o80_dtlanc, round(o80_valor,2), o80_descr,$periodo,$iAnoUsu from orcreserva);
			create table $tabela as select o80_codres, o80_coddot, round(o80_valor,2) as o80_valor, o80_descr from orcreserva inner join orcdotacao on o58_anousu = o80_anousu and o58_coddot = o80_coddot inner join orcreservager on o80_codres = o84_codres where o80_anousu = $iAnoUsu and o80_valor > 0;
			update orcreserva set o80_valor = round(orcreserva.o80_valor - round ( $tabela.o80_valor / $cota[$periodo] * 10 ,2 ),2) from $tabela where $tabela.o80_codres = orcreserva.o80_codres;
			update orcreservager set o84_perc = $cota[$periodo] from $tabela where orcreservager.o84_codres = $tabela.o80_codres;";
		}
		$rsGeraCota= db_query($iGeraCota) or die("ERRO: $iGeraCota - " . pg_last_error());
		if (!$rsGeraCota) {
			$bErro = true;
			$sqlerro = true;
			$aviso = "Erro ao gerar a cota mensal!";
		}

//db_criatabela(db_query("select * from $tabela inner join orcreserva on $tabela.o80_codres = orcreserva.o80_codres where orcreserva.o80_valor - round($tabela.mensal,2) < 0"));exit;

		//REGISTRA HISTÓRICO DAS EXECUÇÕES DA COTA
		$iOrcCota  = "insert into orcamento.orccotamensal values ($periodo,$iAnoUsu,now(),$iUser);";		
		$rsOrcCota = db_query($iOrcCota) or die("ERRO: $iOrcCota");
		$aviso = "Cota liberada com sucesso.";
	} else {
		$dtlanc  = pg_result($rsCotaLib,0);
		$usrlanc = pg_result($rsCotaLib,1);
		$bErro = true;
		$sqlerro = true;
		$aviso = "Cota já liberada no dia $dtlanc pelo usuário $usrlanc.";
	}

	if ($bErro) {
		$sOperFim = "rollback";
	}else{
		$sOperFim = "commit";
	}
	db_query($sOperFim);
}
?>
<html>
<head>
<title>Microsist</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<meta http-equiv="Expires" CONTENT="0">
<script language="JavaScript" type="text/javascript" src="scripts/scripts.js"></script>
<script language="JavaScript" type="text/javascript" src="scripts/prototype.js"></script>
<link href="estilos.css" rel="stylesheet" type="text/css">
<script>

function js_verifica() {

	if(document.form1.periodo.value == '0') {
		alert('Indique o Mês para liberação.');
		return false;
	}

	var sMensagemConfirm = "Você está prestes a liberar a cota para o Mês selecionado ";
	sMensagemConfirm    += "\n\nConfirma esta operação?";
	if (!confirm(sMensagemConfirm)) {
		return false;
	}
	return true;
}

function js_focarCampo() {
	$("periodo").focus();
}
</script>
</head>

<body bgcolor="#CCCCCC" style="margin-top:30px" onLoad="js_focarCampo();">
<center>
<form name="form1" id="form1"method="post" action="">
<fieldset style="width: 600px;">
<legend><b>Libera Cota Mensal</b></legend>
<table border="0" cellspacing="0" cellpadding="0">
<tr>
<td>
<b>Período:</b>
<?php db_select("periodo", $aListaPeriodos, true, 1); ?>
</td>
</tr>
</table>
</fieldset>
<input style="margin-top: 10px;" name="processar" type="submit" id="db_opcao" value="Processar" onclick='return js_verifica();'>
<br>
<br>
<fieldset style="width: 600px;">
<legend><b>Cotas já liberadas <? echo $iAnoUsu; ?> </b></legend>
<table border="1" cellspacing="0" cellpadding="0" width=90%>
<?
$rs_cota = db_query("select o999_mes as mes, to_char(o999_data,'dd/mm/YYYY') as datalanc, nome as usuario from orcamento.orccotamensal inner join db_usuarios ON o999_id_usuario = id_usuario  where o999_ano = $iAnoUsu order by 1");
if(pg_num_rows($rs_cota)==0){
	?>
		<tr><td>Não existem cotas liberadas para o ano atual!</td></tr>
		<?
} else {
	?>
		<tr><td width="30%" align="center"><b>MÊS</b></td><td width="30%" align="center"><b>DATA</b></td><td width="40%" align="center"><b>USUÁRIO</b></td></b></tr>
		<?
		for($abc=0;$abc<pg_num_rows($rs_cota);$abc++){
			db_fieldsmemory($rs_cota, $abc);	
			?>
				<tr><td width="30%" align="center"><? echo $mes; ?></td><td width="30%" align="center"><? echo $datalanc; ?> </td><td width="30%" align="center"><? echo $usuario; ?></td></tr>
				<?
		}
}
?>
</table>
</fieldset>

</form>
</center>
<?php db_menu(db_getsession("DB_id_usuario"), db_getsession("DB_modulo"), db_getsession("DB_anousu"), db_getsession("DB_instit"));?>
</body>
</html>
<script>

</script>
<?php
if (isset ($processar)) {
	if ($sqlerro == false) {
		db_msgbox($aviso);
	} else {
		db_msgbox($aviso);
	}
}
?>

<?php
session_start();
require_once("libs/db_stdlib.php");
//require_once("libs/db_conecta_plugin.php");
require_once("libs/db_conecta.php");
require_once("libs/db_conn.php");
require_once('libs/db_sql.php');

if($_SERVER['REQUEST_METHOD'] == 'POST'){
	$update = " create temp table w_dataconf as select * from contabilidade.condataconf;

				delete from contabilidade.condataconf;

				create temp table w_conlancamval as
				select ".trim($_POST['codlan'])." as max;

				delete from contabilidade.conlancamcompl            using w_conlancamval where c72_codlan = max;
				delete from contabilidade.conlancamcgm              using w_conlancamval where c76_codlan = max;
				delete from contabilidade.conlancamdoc              using w_conlancamval where c71_codlan = max;
				delete from contabilidade.conlancamdot              using w_conlancamval where c73_codlan = max;
				delete from contabilidade.conlancamele              using w_conlancamval where c67_codlan = max;
				delete from empenho.pagordemdescontolanc            using w_conlancamval where e33_conlancam = max;
				delete from contabilidade.conlancamnota             using w_conlancamval where c66_codlan = max;
				delete from contabilidade.conlancamemp              using w_conlancamval where c75_codlan = max;
				delete from contabilidade.conlancampag              using w_conlancamval where c82_codlan = max;
				delete from contabilidade.conlancamcorgrupocorrente using w_conlancamval where c23_conlancam = max;
				delete from contabilidade.conlancamordem            using w_conlancamval where c03_codlan = max;
				delete from contabilidade.conlancamconcarpeculiar   using w_conlancamval where c08_codlan = max;
				delete from contabilidade.conlancamcorrente         using w_conlancamval where c86_conlancam = max;
				delete from contabilidade.conlancamaberturaexercicioorcamento       using w_conlancamval where c105_codlan = max;
				delete from contabilidade.conlancamval              using w_conlancamval where c69_codlan = max;
				delete from contabilidade.conlancaminstit           using w_conlancamval where c02_codlan = max;
				delete from contabilidade.conlancamord              using w_conlancamval where c80_codlan = max;
				delete from contabilidade.conlancam                 using w_conlancamval where c70_codlan = max;

				insert into contabilidade.condataconf select * from w_dataconf;";

	//echo $update; exit;

	$retorno = db_query($update);
	if($retorno == false){
		$erro = 'Erro ao deletar, reveja os dados ou procure o suporte.';
	}
	else{
		$erro = 'Operação feita com sucesso.';
	}
}
?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<title>Acerto em Base</title>
	<link href="estilos.css" rel="stylesheet" type="text/css">
	<script language="JavaScript" type="text/javascript" src="scripts/scripts.js"></script>
	<script type="text/javascript" src="scripts/jquery-2.1.1.min.js"></script>
	<script type="text/javascript">
		<?php if($_SERVER['REQUEST_METHOD'] == 'POST'){ ?>

		jQuery(document).ready(function($) {
			alert('<?php echo $erro; ?>');
			window.location.href = 'sys1_deletalancamentocontabil001.php';
		});

		<?php }else{ ?>

		jQuery(document).ready(function($) {
			$("input[type='text']").bind("keyup blur focus", function(e) {
				e.preventDefault();
				var expre = /[^0-9.]/g;
				if ($(this).val().match(expre))
				$(this).val($(this).val().replace(expre,''));
			});

			$('#enviar').click(function(event) {
				var codlan  = $('#codlan').val();
				if(codlan == ''){
					alert('Digite o sequencial do empenho!');
					$('#codlan').focus();

       			}else{
					var update = confirm("Deletar o lançamento "+codlan+"?");
					if(update){
						$('#form1').submit();
					}else{
						window.location.href = 'sys1_deletalancamentocontabil001.php';
					}
				}
				return false;
			});
		});

		<?php } ?>
	</script>
	<style>
		fieldset {
			width : 400px;
			padding: 10px;
		}
		.conteiner {
			margin: 10px;
		}
		fieldset p {
			padding: 0;
			margin: 0;
		}
		fieldset div {
			margin-bottom: 10px;
		}
		fieldset label {
			display: block;
			float: left;
			padding-top: 4px;
		}
		.clear {
			clear: both;
		}
	</style>
</head>
<body class="body-default">
	<?
		db_menu(db_getsession("DB_id_usuario"),db_getsession("DB_modulo"),db_getsession("DB_anousu"),db_getsession("DB_instit"));
	?>
	<div class="conteiner">
		<p>
			<b>Deleta Lançamento Contabil</b>
		</p>
		<form id="form1" action="" method="post">
		<fieldset>
			<div>
				<p>
					<strong>Código do Lançamento:</strong>
				</p>

				<p>
					<input id="codlan" name="codlan" type="text">
				</p>
			</div>

			<div class="clear"><!-- --></div>
			<div>
				<input id="enviar" type="button" value="Excluir">
			</div>
		</fieldset>
		</form>
	</div>
</body>
</html>

<?php
session_start();
require_once("libs/db_stdlib.php");
//require_once("libs/db_conecta_plugin.php");
require_once("libs/db_conecta.php");
require_once("libs/db_conn.php");
require_once('libs/db_sql.php');

if($_SERVER['REQUEST_METHOD'] == 'POST'){
	$update = " create temp table w_param as select ".trim($_POST['numemp'])." as numemp,  ".trim($_POST['coddoc'])." as coddoc;
               create temp table w_lancamento90 as

                select nextval('conlancam_c70_codlan_seq') as c70_codlan,
                       e60_numemp,
                       case when ( select coddoc from w_param ) = 5 then '90'::int else '91'::int end as c71_coddoc,
                       c70_anousu,
                       c70_valor,
                       e64_codele,
                       e60_coddot,
                       c82_reduz,
                       c70_data,
                       e60_numcgm,
                       nextval('conlancamordem_c03_sequencial_seq') as seq
                       from empempenho
                       inner join empelemento on e60_numemp = e64_numemp
                       inner join conlancamemp on e60_numemp = c75_numemp
                       inner join conlancamdoc on c75_codlan = c71_codlan
                       inner join conlancampag on c75_codlan = c82_codlan
                       inner join conlancam on c75_codlan = c70_codlan
                       where e60_numemp in ( select numemp from w_param )
                         and c71_coddoc = ( select coddoc from w_param )
                         and ( select count(*)
                                 from conlancamemp e
                           inner join conlancamdoc d on e.c75_codlan = d.c71_codlan
                           inner join conlancam c on c.c70_codlan = e.c75_codlan where e.c75_numemp = conlancamemp.c75_numemp
                                                 and d.c71_coddoc = case when ( select coddoc from w_param ) = 5 then '90'::int
                                                                    else '91'::int end and c.c70_valor = conlancam.c70_valor
                                                 and c.c70_data = conlancam.c70_data ) = 0;

       insert into conlancam    select c70_codlan,c70_anousu,c70_data,c70_valor  from w_lancamento90;
       insert into conlancamcgm select c70_codlan,e60_numcgm,c70_data            from w_lancamento90;
       insert into conlancamemp select c70_codlan,e60_numemp,c70_data            from w_lancamento90;
       insert into conlancamdot select c70_codlan,c70_anousu,e60_coddot,c70_data from w_lancamento90;
       insert into conlancamdoc select c70_codlan,c71_coddoc,c70_data            from w_lancamento90;
       insert into conlancampag select c70_codlan,c70_anousu,c82_reduz           from w_lancamento90;
       insert into conlancamele select c70_codlan,e64_codele                     from w_lancamento90;
       insert into conlancamordem select seq, c70_codlan, seq from w_lancamento90;";

	//echo $update; exit;

	$retorno = db_query($update);
	if($retorno == false){
		$erro = 'Erro ao inserir, reveja os dados ou procure o suporte.';
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
			window.location.href = 'sys1_inserelancamentosuprimento001.php';
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
				var numemp  = $('#numemp').val();
				var coddoc  = $('input[name=coddoc]:checked').val();
				console.log(coddoc);
				if(numemp == ''){
					alert('Digite o sequencial do empenho!');
					$('#numemp').focus();

       			}else if(!coddoc){
					alert('Escolha o código do documento!');
					$('input[name=coddoc]').focus();

				}else{
					if(coddoc == 5){
						var doc = 90;
					}else if(coddoc == 6){
						var doc = 91;
					}
					var update = confirm("Confirma inserir lançamento "+doc+"?");
					if(update){
						$('#form1').submit();
					}else{
						window.location.href = 'sys1_inserelancamentosuprimento001.php';
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
		#coddoc5, #coddoc6 {
			float: left;
			margin-right: 10px;
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
			<b>Insere lançamento 90 e 91.</b>
		</p>
		<form id="form1" action="" method="post">
		<fieldset>
			<div>
				<p>
					<strong>Sequencial do Empenho:</strong>
				</p>

				<p>
					<input id="numemp" name="numemp" type="text">
				</p>
			</div>

			<div>
				<p>
					<strong>Documento 5 para 90 e 6 para 91:</strong>
				</p>

				<p>
					<label for="coddoc5">5</label>
					<input type="radio" id="coddoc5" name="coddoc" value="5">

					<label for="coddoc6">6</label>
					<input type="radio" id="coddoc6" name="coddoc" value="6">
				</p>
			</div>
			<div class="clear"><!-- --></div>
			<div>
				<input id="enviar" type="button" value="Inserir">
			</div>
		</fieldset>
		</form>
	</div>
</body>
</html>

<?php 
require_once("libs/db_stdlib.php");
require_once("libs/db_conecta.php");
require_once("libs/db_sessoes.php");
require_once("libs/db_usuariosonline.php");
require_once("dbforms/db_funcoes.php");

require_once("libs/db_app.utils.php");

function testa($var){
	echo "<pre>";
	print_r($var);
	echo "</pre>";
}

function buscaVazias(){
	 $sql = pg_query("SELECT id, e60_numemp, nonotafiscal, valornotafiscal, e50_codord, nocertificado, CASE WHEN cseq2 = 0 THEN 'Não' ELSE 'Sim' END as retiradooc, e60_instit, CASE WHEN excluido = 1 THEN 'Sim' ELSE 'Não' END as excluido FROM certificacaoconformidade WHERE datageracao >= '2024-01-01' AND e50_codord is null ORDER BY e60_instit, id");
	 $resultado = pg_fetch_all($sql);
	 return $resultado;
}

function buscaOp($emp, $nota, $valor){
	//var_dump("SELECT e50_numemp, e69_numero, e50_codord as codord,c70_valor,c53_tipo,c53_descr from pagordem inner join empempenho on empempenho.e60_numemp = pagordem.e50_numemp inner join cgm on cgm.z01_numcgm = empempenho.e60_numcgm inner join db_config on db_config.codigo = empempenho.e60_instit inner join orcdotacao on orcdotacao.o58_anousu = empempenho.e60_anousu and orcdotacao.o58_coddot = empempenho.e60_coddot inner join pctipocompra on pctipocompra.pc50_codcom = empempenho.e60_codcom inner join emptipo on emptipo.e41_codtipo = empempenho.e60_codtipo left join conlancamord on c80_codord = pagordem.e50_codord left join conlancampag on c82_codlan = conlancamord.c80_codlan left join conlancam on c70_codlan = conlancamord.c80_codlan left join conlancamdoc on c71_codlan = conlancam.c70_codlan left join conhistdoc on c53_coddoc = conlancamdoc.c71_coddoc left join conplanoreduz on c61_reduz = conlancampag.c82_reduz and c82_anousu = c61_anousu left join conplano on c60_codcon = conplanoreduz.c61_codcon and c60_anousu=c61_anousu left join pagordemconta on pagordemconta.e49_codord = pagordem.e50_codord INNER JOIN pagordemnota ON e71_codord = e50_codord INNER JOIN empnota ON e71_codnota = e69_codnota where e50_numemp={$emp} AND e69_numero = '{$nota}' AND c70_valor = {$valor} AND c53_tipo = 20 order by e50_codord"); die("Mosta");
	$sql = pg_query("SELECT e50_numemp, e69_numero, e50_codord as codord,c70_valor,c53_tipo,c53_descr from pagordem inner join empempenho on empempenho.e60_numemp = pagordem.e50_numemp inner join cgm on cgm.z01_numcgm = empempenho.e60_numcgm inner join db_config on db_config.codigo = empempenho.e60_instit inner join orcdotacao on orcdotacao.o58_anousu = empempenho.e60_anousu and orcdotacao.o58_coddot = empempenho.e60_coddot inner join pctipocompra on pctipocompra.pc50_codcom = empempenho.e60_codcom inner join emptipo on emptipo.e41_codtipo = empempenho.e60_codtipo left join conlancamord on c80_codord = pagordem.e50_codord left join conlancampag on c82_codlan = conlancamord.c80_codlan left join conlancam on c70_codlan = conlancamord.c80_codlan left join conlancamdoc on c71_codlan = conlancam.c70_codlan left join conhistdoc on c53_coddoc = conlancamdoc.c71_coddoc left join conplanoreduz on c61_reduz = conlancampag.c82_reduz and c82_anousu = c61_anousu left join conplano on c60_codcon = conplanoreduz.c61_codcon and c60_anousu=c61_anousu left join pagordemconta on pagordemconta.e49_codord = pagordem.e50_codord INNER JOIN pagordemnota ON e71_codord = e50_codord INNER JOIN empnota ON e71_codnota = e69_codnota where e50_numemp={$emp} AND e69_numero = '{$nota}' AND c70_valor = {$valor} AND c53_tipo = 20 order by e50_codord");
	$resultado = pg_fetch_all($sql);
	return $resultado[0]["codord"];
}

$vazios = buscaVazias();

foreach($vazios as $lista){
	$id = $lista["id"];
	$seqempenho = $lista["e60_numemp"];
	$nf = $lista["nonotafiscal"];
	$valor = (float)$lista["valornotafiscal"];

	$op = buscaOp($seqempenho, $nf, $valor);
	if($op){
		//echo $id . "==" .$op . " - " . $lista["nocertificado"]; echo "<br>";	
		//echo "UPDATE certificacaoconformidade SET e50_codord = {$op} WHERE id = {$id}"; echo "<br>";
		pg_query("UPDATE certificacaoconformidade SET e50_codord = {$op} WHERE id = {$id}");
	}
	

}

//testa($vazios);


//select e50_numemp, e69_numero, e50_codord as codord,c70_valor,c53_tipo,c53_descr from pagordem inner join empempenho on empempenho.e60_numemp = pagordem.e50_numemp inner join cgm on cgm.z01_numcgm = empempenho.e60_numcgm inner join db_config on db_config.codigo = empempenho.e60_instit inner join orcdotacao on orcdotacao.o58_anousu = empempenho.e60_anousu and orcdotacao.o58_coddot = empempenho.e60_coddot inner join pctipocompra on pctipocompra.pc50_codcom = empempenho.e60_codcom inner join emptipo on emptipo.e41_codtipo = empempenho.e60_codtipo left join conlancamord on c80_codord = pagordem.e50_codord left join conlancampag on c82_codlan = conlancamord.c80_codlan left join conlancam on c70_codlan = conlancamord.c80_codlan left join conlancamdoc on c71_codlan = conlancam.c70_codlan left join conhistdoc on c53_coddoc = conlancamdoc.c71_coddoc left join conplanoreduz on c61_reduz = conlancampag.c82_reduz and c82_anousu = c61_anousu left join conplano on c60_codcon = conplanoreduz.c61_codcon and c60_anousu=c61_anousu left join pagordemconta on pagordemconta.e49_codord = pagordem.e50_codord INNER JOIN pagordemnota ON e71_codord = e50_codord INNER JOIN empnota ON e71_codnota = e69_codnota where e50_numemp=954489 AND e69_numero = '1086253' AND c70_valor = 272.44 AND c53_tipo = 20 order by e50_codord;
//UPDATE certificacaoconformidade SET e50_codord = 528721 WHERE id = 5765
?>





<?php
//error_reporting(E_ALL); 
//ini_set('display_errors', '1');

require ("libs/db_stdlib.php");
require ("libs/db_utils.php");
require ("libs/db_conecta.php");
include ("libs/db_sessoes.php");
include ("libs/db_usuariosonline.php");
include ("dbforms/db_funcoes.php");

function buscaValorDentro($orgao, $ano){
	$sql = pg_query("SELECT * FROM controlepreorcdesp WHERE orgao = {$orgao} AND ano = {$ano}");
	$resultado = pg_fetch_all($sql);
	return $resultado[0];
}


/*
function buscaTotal(){
	$inst = db_getsession("DB_instit");
	$sql = pg_query("SELECT o40_orgao, o40_descr FROM orcorgao WHERE o40_instit = {$inst} AND o40_anousu = 2026 ORDER BY o40_orgao");
	$resultado = pg_fetch_all($sql);
	return $resultado;
}*/

//var_dump($_GET);

/*

\d orcdotacao
(o58_anousu, o58_orgao) REFERENCES orcorgao(o40_anousu, o40_orgao

SELECT o40_orgao, o40_descr FROM orcorgao WHERE o40_instit = 1 AND o40_anousu = 2026 ORDER BY o40_orgao;

*/
$orgao = $_GET["orgao"];
$ano = $_GET["ano"];

$verifica = buscaValorDentro($orgao, $ano);
if($verifica){
	//$verifica["valor"] = str_replace(".", ",", $verifica["valor"];
}

?>

<?php if($verifica) : ?>
<td><b>Total da Despesa:</b></td>
		<td><input type="text" name="totaldespesa" id="totaldespesa" value="<?=$verifica["valor"]?>"></td>
<?php else: ?>
<td><b>Total da Despesa:</b></td>
		<td><input type="text" name="totaldespesa" id="totaldespesa"></td>
<?php endif; ?>
	

		<?php /* ?>
		<td><b>Total da Despesa:</b></td>
		<td><input type="text" name="totaldespesa" id="totaldespesa"></td>
		<?php */ ?>
	
	<script>
		 const input = document.getElementById('totaldespesa');

  	input.addEventListener('input', function (e) {
    const valor = input.value;
    const regexValido = /^[0-9,]*$/;

    if (!regexValido.test(valor)) {
      alert('Digite apenas números e ponto. Letras ou outros caracteres não são permitidos!');
      input.value = valor.replace(/[^0-9,]/g, '');
    }
  });
	</script>




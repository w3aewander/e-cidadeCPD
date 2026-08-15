<?php

require ("libs/db_stdlib.php");
require ("libs/db_utils.php");
require ("libs/db_conecta.php");
include ("libs/db_sessoes.php");
include ("libs/db_usuariosonline.php");
include ("dbforms/db_funcoes.php");
include ("classes/db_orcdotacao_classe.php");
include ("classes/db_orcdotacaocontr_classe.php");
include ("classes/db_orcelemento_classe.php");
include ("classes/db_orcparametro_classe.php");
include ("classes/db_orcorgao_classe.php");
include ("classes/db_orcunidade_classe.php");
include ("classes/db_orcfuncao_classe.php");
include ("classes/db_orcsubfuncao_classe.php");
include ("classes/db_orcprograma_classe.php");
include ("classes/db_orcprojativ_classe.php");

require("libs/db_app.utils.php");
include("dbforms/db_classesgenericas.php");



include ("classes/db_orctiporec_classe.php");
require ("libs/db_liborcamento.php");

parse_str($HTTP_SERVER_VARS["QUERY_STRING"]);
db_postmemory($HTTP_POST_VARS);

$clorcdotacao      = new cl_orcdotacao;
$clorcdotacaocontr = new cl_orcdotacaocontr;
$clorcelemento     = new cl_orcelemento;
$clorcparametro    = new cl_orcparametro;
$clorcorgao        = new cl_orcorgao;
$clorcunidade      = new cl_orcunidade;
$clorcfuncao       = new cl_orcfuncao;
$clorcsubfuncao    = new cl_orcsubfuncao;
$clorcprograma     = new cl_orcprograma;
$clorcprojativ     = new cl_orcprojativ;
$clorctiporec      = new cl_orctiporec;
$db_opcao          = 1;
$db_botao          = true;
$anousu            = db_getsession("DB_anousu");

$anonovo = $anousu;

if($_GET["t"] == 1){
	echo "<script>alert('Inclusão realizada.');</script>";
}

if($_GET["t"] == 2){
	pg_query("DELETE FROM orcdotacao WHERE o58_anousu = {$anonovo} AND o58_instit = {$bins}");
	echo "<script>alert('Houve um erro. Contate o suporte.');</script>";
}

/*if($_GET["t"] == "repetido"){
	$idr = $_GET["id"];
	echo "<script>alert('Prévia já cadastrada. ID: {$id} ');</script>";
	$_GET["t"] = "";
	//sleep(2);
	//header("Location:preorcdesp.php");
}*/

function testa($var){
	echo "<pre>";
	print_r($var);
	echo "</pre>";
}


function verificaAtualizado($id){
	$sql = pg_query("SELECT * FROM vadotacaodesp WHERE idprevia = {$id}");
	$resultado = pg_fetch_all($sql);
	return $resultado;
}

function retornaProximoId(){
	$sql = pg_query("SELECT max(id)+1 as idtipo FROM previadespesas");
	$resultado = pg_fetch_all($sql);
	return $resultado[0]["idtipo"];
}

function retornaUltimoId(){
  $sql = pg_query("SELECT max(id) as ultimo FROM previadespesas");
  $resultado = pg_fetch_all($sql);
  return $resultado[0]["ultimo"];
}

function confereRepetido($estrutural, $ano, $cpec){
	$sql = pg_query("SELECT * FROM previadespesas WHERE o50_estrutdespesa = '{$estrutural}' AND o58_concarpeculiar = '{$cpec}' AND o58_anousu = '{$ano}'");
	$resultado = pg_fetch_all($sql);
	return $resultado;
}

function buscaIdPrevisto($ano, $orgao, $unidade, $funcao, $subfuncao, $programa, $projativ, $codele, $recurso, $localizador, $cpeculiar){
	$sql = pg_query("SELECT id FROM valoresprevistos WHERE ano = {$ano} AND orgao = {$orgao} AND unidade = {$unidade} AND funcao = {$funcao} AND subfuncao = {$subfuncao} AND programa = {$programa} AND projativ = {$projativ} AND elemento = '{$codele}' AND recurso = {$recurso} AND localizador = {$localizador} AND cpeculiar = '{$cpeculiar}' ");
				$resultado = pg_fetch_all($sql);
				return $resultado[0]["id"];
}

function buscaXdados($id){
	$sql = pg_query("SELECT * FROM previadespesas WHERE id = {$id}");
	$resultado = pg_fetch_all($sql);
	return $resultado[0];
}


if ((isset ($HTTP_POST_VARS["i2021"]) && $HTTP_POST_VARS["i2021"]) == "Incluir {$anonovo}"){
	
	//Busca tudo
	$bins = db_getsession("DB_instit");
	$tudo = pg_query("SELECT * FROM previadespesas WHERE o58_instit = '$bins' AND o58_anousu = '{$anonovo}' ORDER BY id");
	$resultudo = pg_fetch_all($tudo);

	
	
	pg_query("DELETE FROM orcdotacao WHERE o58_anousu = {$anonovo} AND o58_instit = {$bins}");
	foreach($resultudo as $linha){		

		$o58_anousu = $linha["o58_anousu"];
		$o50_estrutdespesa = $linha["o50_estrutdespesa"];
		$o58_coddot = $linha["o58_coddot"];		
		$o58_instit = $linha["o58_instit"];
		$nomeinst = $linha["nomeinst"];
		$o58_orgao = $linha["o58_orgao"];
		$o40_descr = $linha["o40_descr"];
		$o58_unidade = $linha["o58_unidade"];
		$o41_descr = $linha["o41_descr"];
		$o58_funcao = $linha["o58_funcao"];
		$o52_descr = $linha["o52_descr"];
		$o58_subfuncao = $linha["o58_subfuncao"];
		$o53_descr = $linha["o53_descr"];
		$o58_programa = $linha["o58_programa"];
		$o54_descr = $linha["o54_descr"];
		$o58_projativ = $linha["o58_projativ"];
		$o55_descr = $linha["o55_descr"];
		$o56_elemento = $linha["o56_elemento"];
		$o56_descr = $linha["o56_descr"];
		$o58_codigo = trim($linha["o58_codigo"]);
		$o15_descr = $linha["o15_descr"];
		$o58_localizadorgastos = $linha["o58_localizadorgastos"];
		$o11_descricao = $linha["o11_descricao"];
		$o58_concarpeculiar =  trim($linha["o58_concarpeculiar"]);
		$c58_descr = $linha["c58_descr"];
		$o58_esferaorcamentaria = $linha["o58_esferaorcamentaria"];
		$o58_valor = ($linha["o58_valor"]) ? $linha["o58_valor"] : 0;
		if(!floatval($o58_valor)){
			$o58_valor = 0;
		}
		$id = (int)$linha["id"];
		
		$sqlcodele = pg_query("SELECT o56_codele FROM orcelemento WHERE o56_anousu = '$o58_anousu' AND o56_elemento = '$o56_elemento'");
		$codele = pg_fetch_all($sqlcodele);
		$o58_codele = $codele[0]["o56_codele"];
		
		$sqlultparam = pg_query("SELECT o50_coddot FROM orcparametro WHERE o50_anousu = {$anonovo}");
		$codot = pg_fetch_all($sqlultparam);
		$o58_coddot = $codot[0]["o50_coddot"] + 1;
		
		$atualiza_coddot = pg_query("UPDATE orcparametro SET o50_coddot = {$o58_coddot}");


		$inseredotacao = pg_query($conn, "INSERT INTO orcdotacao(o58_anousu ,o58_coddot ,o58_orgao ,o58_unidade ,o58_funcao ,o58_subfuncao ,o58_programa ,o58_projativ ,o58_codele ,o58_codigo ,o58_valor ,o58_instit ,o58_localizadorgastos ,o58_datacriacao ,o58_concarpeculiar, o58_esferaorcamentaria) values ({$o58_anousu}, {$o58_coddot}, {$o58_orgao}, {$o58_unidade}, {$o58_funcao}, {$o58_subfuncao}, {$o58_programa}, {$o58_projativ}, {$o58_codele}, {$o58_codigo}, {$o58_valor}, {$o58_instit}, {$o58_localizadorgastos} ,null ,'$o58_concarpeculiar', {$o58_esferaorcamentaria})");
		//$sqlinsere = "INSERT INTO orcdotacao(o58_anousu ,o58_coddot ,o58_orgao ,o58_unidade ,o58_funcao ,o58_subfuncao ,o58_programa ,o58_projativ ,o58_codele ,o58_codigo ,o58_valor ,o58_instit ,o58_localizadorgastos ,o58_datacriacao ,o58_concarpeculiar) values ({$o58_anousu}, {$o58_coddot}, {$o58_orgao}, {$o58_unidade}, {$o58_funcao}, {$o58_subfuncao}, {$o58_programa}, {$o58_projativ}, {$o58_codele}, {$o58_codigo}, {$o58_valor}, {$o58_instit}, {$o58_localizadorgastos} ,null ,'$o58_concarpeculiar')";
		//var_dump($sqlinsere); echo "<br>"; //die("Confere"); 

		if(!$inseredotacao){
			$erro = "Sim";
			//header("Location:preorcdesp.php?t=2");
			$error = pg_last_error($conn);
			
			//var_dump($error);
			//echo "<br>";
			//var_dump($id);
			//echo "<br>";
			
			//$sqlinsere = "INSERT INTO orcdotacao(o58_anousu ,o58_coddot ,o58_orgao ,o58_unidade ,o58_funcao ,o58_subfuncao ,o58_programa ,o58_projativ ,o58_codele ,o58_codigo ,o58_valor ,o58_instit ,o58_localizadorgastos ,o58_datacriacao ,o58_concarpeculiar, o58_esferaorcamentaria) values ({$o58_anousu}, {$o58_coddot}, {$o58_orgao}, {$o58_unidade}, {$o58_funcao}, {$o58_subfuncao}, {$o58_programa}, {$o58_projativ}, {$o58_codele}, {$o58_codigo}, {$o58_valor}, {$o58_instit}, {$o58_localizadorgastos} ,null ,'$o58_concarpeculiar', {$o58_esferaorcamentaria})";
			//var_dump($sqlinsere);
			//echo "<br>";
			//echo "<hr>";
			}

		}//Fim do foreach
		//die("Confere erro");
		if($erro == "Sim"){
			header("Location:preorcdesp.php?t=2");
		}else{
			header("Location:preorcdesp.php?t=1");	
		}
	
		
}//Fim do Incluir ano novo
//die("Confere");

$instituicoes = pg_query("SELECT DISTINCT o58_instit FROM previadespesas order by o58_instit");
$instituicoes = pg_fetch_all($instituicoes);


if($_POST["apagar"]){
	$eisosdados = buscaXdados($id);
	//testa($eisosdados); die("Confere");
	$qo58_orgao = $eisosdados["o58_orgao"];
	$qo58_unidade = $eisosdados["o58_unidade"];
	$qo58_funcao = $eisosdados["o58_funcao"];
	$qo58_subfuncao = $eisosdados["o58_subfuncao"];
	$qo58_programa = $eisosdados["o58_programa"];
	$qo58_projativ = $eisosdados["o58_projativ"];
	$qo56_elemento = $eisosdados["o56_elemento"];
	$qo58_codigo = $eisosdados["o58_codigo"];
	$qo58_localizadorgastos = $eisosdados["o58_localizadorgastos"];
	$qo58_concarpeculiar = $eisosdados["o58_concarpeculiar"];

	$result = pg_query($conn, "DELETE FROM previadespesas WHERE id = '$id' ");
	$result2 = pg_query($conn, "DELETE FROM valoresprevistos WHERE orgao = {$qo58_orgao} AND unidade = {$qo58_unidade} AND funcao = {$qo58_funcao} AND subfuncao = {$qo58_subfuncao} AND programa = {$qo58_programa} AND projativ = {$qo58_projativ} AND elemento = '{$qo56_elemento}' AND recurso = {$qo58_codigo} AND localizador = {$qo58_localizadorgastos} AND cpeculiar = '{$qo58_concarpeculiar}' ");

  if($result){
    echo "<script>alert('Registro excluído com sucesso.');</script>";   
    $avisa = "s";
  } else{
    echo "<script>alert('Registro não excluído. Contate o suporte.');</script>";
    $error = pg_last_error($conn);
  }
}

  
//INCLUIR PRÉVIA
if ((isset ($HTTP_POST_VARS["db_opcao"]) && $HTTP_POST_VARS["db_opcao"]) == "Incluir") {
	$o58_anousu = (empty($o58_anousu) ? ' ' : $o58_anousu);
	//$o50_estrutdespesa = (empty($o50_estrutdespesa) ? ' ' : $o50_estrutdespesa);
	$o58_coddot = (empty($o58_coddot) ? 0 : $o58_coddot);
	$o58_instit = (empty($o58_instit) ? ' ' : $o58_instit);
	$nomeinst = (empty($nomeinst) ? ' ' : $nomeinst);
	$o58_orgao = (empty($o58_orgao) ? ' ' : $o58_orgao);
	$o40_descr = (empty($o40_descr) ? ' ' : $o40_descr);
	$o58_unidade = (empty($o58_unidade) ? ' ' : $o58_unidade);
	$o41_descr = (empty($o41_descr) ? ' ' : $o41_descr);
	$o58_funcao = (empty($o58_funcao) ? ' ' : $o58_funcao);
	$o52_descr = (empty($o52_descr) ? ' ' : $o52_descr);
	$o58_subfuncao = (empty($o58_subfuncao) ? ' ' : $o58_subfuncao);
	$o53_descr = (empty($o53_descr) ? ' ' : $o53_descr);
	$o58_programa = (empty($o58_programa) ? ' ' : $o58_programa);
	$o54_descr = (empty($o54_descr) ? ' ' : $o54_descr);
	$o58_projativ = (empty($o58_projativ) ? ' ' : $o58_projativ);
	$o55_descr = (empty($o55_descr) ? ' ' : $o55_descr);
	$o56_elemento = (empty($o56_elemento) ? ' ' : $o56_elemento);
	$o56_descr = (empty($o56_descr) ? ' ' : $o56_descr);
	$o58_codigo = (empty($o58_codigo) ? ' ' : $o58_codigo);
	$o15_descr = (empty($o15_descr) ? ' ' : $o15_descr);
	$o58_localizadorgastos = (empty($o58_localizadorgastos) ? ' ' : $o58_localizadorgastos);
	$o11_descricao = (empty($o11_descricao) ? ' ' : $o11_descricao);
	$o58_concarpeculiar = (empty($o58_concarpeculiar) ? ' ' : trim($o58_concarpeculiar));
	$c58_descr = (empty($c58_descr) ? ' ' : $c58_descr);
	$o58_esferaorcamentaria = (empty($esferaOrcamentaria) ? ' ' : $esferaOrcamentaria);
	$o58_valor = (empty($o58_valor) ? ' ' : $o58_valor);








//VALORES PREVISTOS
/*
	$jorgao = $o58_orgao;
	$jano = $o58_anousu;
	$junidade = $o58_unidade;
	$jfuncao = $o58_funcao;
	$jsubfuncao = $o58_subfuncao;
	$jprograma = $o58_programa;
	$jprojativ = $o58_projativ;
	$jcodele = $o56_elemento;
	$jvalor = $o58_valor;
	




	var_dump($jorgao);
	var_dump($jano);
	var_dump($junidade);
	var_dump($jfuncao);
	var_dump($jsubfuncao);
	var_dump($jprograma);
	var_dump($jprojativ);
	var_dump($jcodele);

	testa($_POST);

	die("Para tudo");
	*/

	if(strlen($o58_orgao) == 1){
		$e1 = "0".$o58_orgao;
	} else {
		$e1 = $o58_orgao;	
	}

	if(strlen($o58_unidade) == 1){
		$e2 = "0".$o58_unidade;
	} else {
		$e2 = $o58_unidade;	
	}

	if(strlen($o58_funcao) == 1){
		$e3 = "0".$o58_funcao;
	} else {
		$e3 = $o58_funcao;	
	}
	
	if(strlen($o58_subfuncao) == 1){
		$e4 = "00".$o58_subfuncao;
	} elseif (strlen($o58_subfuncao) == 2) {
		$e4 = "0".$o58_subfuncao;
	} else { 
		$e4 = $o58_subfuncao;
	}	
	
	$e5 = $o58_programa;	
	$e6 = $o58_projativ;	
	$e7 = $o56_elemento;


	if(strlen($o58_codigo) == 1){
		$e8 = "000".$o58_codigo;
	} elseif (strlen($o58_codigo) == 2) {
		$e8 = "00".$o58_codigo;
	} elseif(strlen($o58_codigo) == 3) { 
		$e8 = "0".$o58_codigo;
	} else{
		$e8 = $o58_codigo;
	}

	//value="00.00.00.000.0000.0000.0000000000000.0000.0000"
	$o50_estrutdespesa = $e1.".". $e2.".". $e3.".". $e4.".". $e5."." . $e6."." . $e7."." . $e8;
	
	$repetido = confereRepetido($o50_estrutdespesa, $o58_anousu, $o58_concarpeculiar);
	
	if($repetido && empty($id)){
		$idrep = $repetido[0]["id"];
		echo "<script>alert('Prévia já cadastrada. ID: {$idrep} ');</script>";
		//header("Location:preorcdesp.php?t=repetido&id={$idrep}");
	}else{
			if(empty($id)){ 
		//Incluir
		$result = pg_query($conn, "INSERT INTO previadespesas(o58_anousu, o50_estrutdespesa, o58_coddot, o58_instit, nomeinst, o58_orgao, o40_descr, o58_unidade, o41_descr, o58_funcao, o52_descr, o58_subfuncao, o53_descr, o58_programa, o54_descr, o58_projativ, o55_descr, o56_elemento, o56_descr, o58_codigo, o15_descr, o58_localizadorgastos, o11_descricao, o58_concarpeculiar, c58_descr, o58_valor, o58_esferaorcamentaria) VALUES('$o58_anousu', '$o50_estrutdespesa', '$o58_coddot', '$o58_instit', '$nomeinst', '$o58_orgao', '$o40_descr', '$o58_unidade', '$o41_descr', '$o58_funcao', '$o52_descr', '$o58_subfuncao', '$o53_descr', '$o58_programa', '$o54_descr', '$o58_projativ', '$o55_descr', '$o56_elemento', '$o56_descr', '$o58_codigo', '$o15_descr', '$o58_localizadorgastos', '$o11_descricao', '$o58_concarpeculiar', '$c58_descr', '$o58_valor', '$o58_esferaorcamentaria')");
		
		//INSERIR VALORES PREVISTOS AQUI
		$valorBase = $o58_valor;
		for ($i = $o58_anousu; $i < $o58_anousu + 4; $i++) {    
    	$jano = $i;
    	$jvalor = $valorBase;    
    	$jorgao = $o58_orgao;
    	$junidade = $o58_unidade;
    	$jfuncao = $o58_funcao;
    	$jsubfuncao = $o58_subfuncao;
    	$jprograma = $o58_programa;
    	$jprojativ = $o58_projativ;
    	$jcodele = $o56_elemento;

    	$jrecurso = $o58_codigo;
    	$jlocalizador = $o58_localizadorgastos;
    	$jcpeculiar = $o58_concarpeculiar;
    	
    	$result2 = pg_query("INSERT INTO valoresprevistos(ano, orgao, unidade, funcao, subfuncao, programa, projativ, elemento, recurso, localizador, cpeculiar, valor) VALUES({$jano}, {$jorgao}, {$junidade}, {$jfuncao}, {$jsubfuncao}, {$jprograma}, {$jprojativ}, '{$jcodele}', {$jrecurso}, {$jlocalizador}, '{$jcpeculiar}', {$jvalor});");
    	//echo "INSERT INTO valoresprevistos(ano, orgao, unidade, funcao, subfuncao, programa, projativ, elemento, valor) VALUES({$jano}, {$jorgao}, {$junidade}, {$jfuncao}, {$jsubfuncao}, {$jprograma}, {$jprojativ}, '{$jcodele}', {$jvalor}  );  "; echo "<br>";
    
    	$valorBase *= 1.05;
		}
		//$result2 = pg_query($conn, "INSERT")
		$acaousuario = "Incluir";
		//$iddaprevia = retornaProximoId();
		$iddaprevia = retornaUltimoId();
	} else{ 
		//Alterar
		$result = pg_query($conn, "UPDATE previadespesas SET o58_anousu = '$o58_anousu', o50_estrutdespesa = '$o50_estrutdespesa', o58_coddot = '$o58_coddot', o58_instit = '$o58_instit', nomeinst = '$nomeinst', o58_orgao = '$o58_orgao', o40_descr = '$o40_descr', o58_unidade = '$o58_unidade', o41_descr = '$o41_descr', o58_funcao = '$o58_funcao', o52_descr = '$o52_descr', o58_subfuncao = '$o58_subfuncao', o53_descr = '$o53_descr', o58_programa = '$o58_programa', o54_descr = '$o54_descr', o58_projativ = '$o58_projativ', o55_descr = '$o55_descr', o56_elemento = '$o56_elemento', o56_descr = '$o56_descr', o58_codigo = '$o58_codigo', o15_descr = '$o15_descr', o58_localizadorgastos = '$o58_localizadorgastos', o11_descricao = '$o11_descricao', o58_concarpeculiar = '$o58_concarpeculiar', c58_descr = '$c58_descr', o58_valor = '$o58_valor', o58_esferaorcamentaria = '$o58_esferaorcamentaria' WHERE id = '$id'");
		$acaousuario = "Alterar";
		$iddaprevia = $id;

		//INSERIR VALORES PREVISTOS AQUI
		$valorBase = $o58_valor;
		
		for ($i = $o58_anousu; $i < $o58_anousu + 4; $i++) {
    	$jano = $i;
    	$jvalor = $valorBase;    
    	$jorgao = $o58_orgao;
    	$junidade = $o58_unidade;
    	$jfuncao = $o58_funcao;
    	$jsubfuncao = $o58_subfuncao;
    	$jprograma = $o58_programa;
    	$jprojativ = $o58_projativ;
    	$jcodele = $o56_elemento;

    	$jrecurso = $o58_codigo;
			$jlocalizador = $o58_localizadorgastos;
			$jcpeculiar = $o58_concarpeculiar;

    	$jid = buscaIdPrevisto($jano, $jorgao, $junidade, $jfuncao, $jsubfuncao, $jprograma, $jprojativ, $jcodele, $jrecurso, $jlocalizador, $jcpeculiar);
    	
    	$result2 = pg_query("UPDATE valoresprevistos SET orgao = {$jorgao}, unidade = {$junidade}, funcao = {$jfuncao}, subfuncao = {$jsubfuncao}, programa = {$jprograma}, projativ = {$jprojativ}, elemento = '{$jcodele}', recurso = {$jrecurso}, localizador = {$jlocalizador}, cpeculiar = '{$jcpeculiar}', valor = {$jvalor} WHERE id = {$jid}");
    	//echo "UPDATE valoresprevistos SET orgao = {$jorgao}, unidade = {$junidade}, funcao = {$jfuncao}, subfuncao = {$jsubfuncao}, programa = {$jprograma}, projativ = {$jprojativ}, elemento = '{$jcodele}', valor = {$jvalor} WHERE id = {$jid}"; echo "<br>";
    	
    
    	$valorBase *= 1.05;
		}
	}
	
	$idusuario = db_getsession("DB_id_usuario");
   	$loginusuario = db_getsession("DB_login");
   	$ipusuario = db_getsession("DB_ip");
   	$datausuario = date("Y-m-d");
   	$horausuario = date("H:i");   

	if($result){
		if($acaousuario == "Incluir"){
			pg_query("INSERT INTO historicopreviadespesas(o58_anousu, o50_estrutdespesa, o58_coddot, o58_instit, nomeinst, o58_orgao, o40_descr, o58_unidade, o41_descr, o58_funcao, o52_descr, o58_subfuncao, o53_descr, o58_programa, o54_descr, o58_projativ, o55_descr, o56_elemento, o56_descr, o58_codigo, o15_descr, o58_localizadorgastos, o11_descricao, o58_concarpeculiar, c58_descr, o58_valor, idusuario, loginusuario, ipusuario, datausuario, horausuario, acaousuario,iddaprevia, o58_esferaorcamentaria) VALUES('$o58_anousu', '$o50_estrutdespesa', '$o58_coddot', '$o58_instit', '$nomeinst', '$o58_orgao', '$o40_descr', '$o58_unidade', '$o41_descr', '$o58_funcao', '$o52_descr', '$o58_subfuncao', '$o53_descr', '$o58_programa', '$o54_descr', '$o58_projativ', '$o55_descr', '$o56_elemento', '$o56_descr', '$o58_codigo', '$o15_descr', '$o58_localizadorgastos', '$o11_descricao', '$o58_concarpeculiar', '$c58_descr', '$o58_valor', {$idusuario}, '{$loginusuario}', '{$ipusuario}', '{$datausuario}', '{$horausuario}', 'incluir', {$iddaprevia}, {$o58_esferaorcamentaria})");			
		}else{
			pg_query("INSERT INTO historicopreviadespesas(o58_anousu, o50_estrutdespesa, o58_coddot, o58_instit, nomeinst, o58_orgao, o40_descr, o58_unidade, o41_descr, o58_funcao, o52_descr, o58_subfuncao, o53_descr, o58_programa, o54_descr, o58_projativ, o55_descr, o56_elemento, o56_descr, o58_codigo, o15_descr, o58_localizadorgastos, o11_descricao, o58_concarpeculiar, c58_descr, o58_valor, idusuario, loginusuario, ipusuario, datausuario, horausuario, acaousuario,iddaprevia, o58_esferaorcamentaria) VALUES('$o58_anousu', '$o50_estrutdespesa', '$o58_coddot', '$o58_instit', '$nomeinst', '$o58_orgao', '$o40_descr', '$o58_unidade', '$o41_descr', '$o58_funcao', '$o52_descr', '$o58_subfuncao', '$o53_descr', '$o58_programa', '$o54_descr', '$o58_projativ', '$o55_descr', '$o56_elemento', '$o56_descr', '$o58_codigo', '$o15_descr', '$o58_localizadorgastos', '$o11_descricao', '$o58_concarpeculiar', '$c58_descr', '$o58_valor', {$idusuario}, '{$loginusuario}', '{$ipusuario}', '{$datausuario}', '{$horausuario}', 'alterar', {$iddaprevia}, {$o58_esferaorcamentaria})");
		}
		echo "<script>alert('Prévia inserida com sucesso.');</script>";		
		$avisa = "s";
	} else{
		echo "<script>alert('Prévia não foi feita. Contate o suporte.');</script>";
		$error = pg_last_error($conn);
		var_dump($error);
	}

	}


	

    	
	

	
}
?>
<html>
<head>
<title>Microsist</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<meta http-equiv="Expires" CONTENT="0">
<script language="JavaScript" type="text/javascript" src="scripts/scripts.js"></script>
<link href="estilos.css" rel="stylesheet" type="text/css">
</head>
<body bgcolor=#CCCCCC leftmargin="0" topmargin="0" marginwidth="0" marginheight="0" onLoad="document.form1.o50_estrutdespesa.focus();" style="margin-top: 30px">

<center>
<?

include ("forms/frm_preorcdesp.php");
?>
</center>

<script>
	document.getElementsByName('o50_estrutdespesa')[0].value ='';
	//document.getElementsByName('o56_elemento')[0].value ='';

</script>

</body>
</html>
<?
if ((isset ($HTTP_POST_VARS["db_opcao"]) && $HTTP_POST_VARS["db_opcao"]) == "Incluir") {
  if ($clorcdotacao->erro_status == "0") {
    $clorcdotacao->erro(true, false);
    $db_botao = true;
    echo "<script> document.form1.db_opcao.disabled=false;
                     document.form1.o58_coddot.value = '';
            </script>  ";
    if ($clorcdotacao->erro_campo != "") {
      echo "<script> document.form1.".$clorcdotacao->erro_campo.".style.backgroundColor='#99A9AE';</script>";
      echo "<script> document.form1.".$clorcdotacao->erro_campo.".focus();</script>";
    }
  } else {
    $clorcdotacao->erro(true,true);
  }
}
	
 db_menu(db_getsession("DB_id_usuario"),db_getsession("DB_modulo"),db_getsession("DB_anousu"),db_getsession("DB_instit")); ?>
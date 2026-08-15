<?php
error_reporting(E_ALL); 
ini_set('display_errors', '1');

/**
 * Script migra_bens.php
 * @author Sérgio Navarro
 * Navarro Tecnologia
 * @date 13/09/2022
 * @version 1.0
 */

/**
 * Conexao com o banco de dados...
 * @param nenhum
 * @return ponteiro para pdo
 */

//processo


function conecta(){
  try {
    $pdo = new PDO("pgsql:dbname='voltaredonda'; host=10.1.0.51;  port=5432; user=ecidade; password='db#vltrdnd12';");
    $pdo->exec( "select fc_startsession();" );

    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    return $pdo;
  } catch (PDOException $e){
    echo "Erro: " . $e->getMessage();
  }
}

//Conecta no banco de dados
$pdo = conecta();
if(!$pdo) {
  die ("Não foi possível conectar ao banco de origem! Tente novamente.");
}else{
  //pg_set_client_encoding($pdo, 'LATIN1');
}

/**
 *
 * Remove os caracteres "-/." dos
 * @param $text string
 * @return $str_tratado string
*/
function trata_campo($text) {
     $str_tratado = trim($text);
     $str_tratado = str_replace('.','',$text);
     $str_tratado = str_replace('-','',$str_tratado);
     $str_tratado = str_replace('/','',$str_tratado);

    return $str_tratado;
}

function rec_instit($pdo){
  try {
    $stm = $pdo->prepare("SELECT codigo AS instit FROM configuracoes.db_config WHERE codigo not IN(SELECT t64_instit FROM patrimonio.clabens)");
    $stm->execute();
    $res = $stm->fetchAll(PDO::FETCH_OBJ);
    return $res ? $res : false;

  } catch (PDOException $e){
    echo "Erro: " . $e->getMessage();
  }
}

function seqcodcla($pdo){
  try {
    $stm = $pdo->prepare("SELECT MAX(t64_codcla) + 1 AS codcla FROM patrimonio.clabens");
    $stm->execute();
    $res = $stm->fetchAll(PDO::FETCH_OBJ);
    return $res ? $res[0]->codcla : false;

  } catch (PDOException $e){
    echo "Erro: " . $e->getMessage();
  }
}

function Recregistros($pdo){
  try {
    $stmt = $pdo->prepare("
													SELECT
														t64_class,
														t64_descr,
														t64_obs,
														t64_analitica,
														t64_bemtipos,
														t64_benstipodepreciacao,
														t64_vidautil
													FROM patrimonio.clabens
													WHERE t64_instit = 45
													ORDER BY t64_codcla
											");
    $stmt->execute();
    $res = $stmt->fetchAll(PDO::FETCH_OBJ);
    return $res ? $res : false;
  } catch (PDOException $e){
    echo "Erro: " . $e->getMessage();
  }
}

function insert_clabens($pdo,$dados){
  try{
    $stmt = $pdo->prepare("INSERT INTO patrimonio.clabens VALUES (:t64_codcla, :t64_class, :t64_descr, :t64_obs, :t64_analitica, :t64_bemtipos, :t64_benstipodepreciacao, :t64_vidautil, :t64_instit)");
    $stmt->execute(array(
        't64_codcla'    => $dados['t64_codcla'],
        't64_class'     => $dados['t64_class'],
        't64_descr'     => $dados['t64_descr'],
        't64_obs'       => $dados['t64_obs'],
        't64_analitica' => $dados['t64_analitica'],
        't64_bemtipos'  => $dados['t64_bemtipos'],
        't64_benstipodepreciacao'  => $dados['t64_benstipodepreciacao'],
        't64_vidautil'  => $dados['t64_vidautil'],
        't64_instit'    => $dados['t64_instit']
      ));
    return ($stmt->rowCount() > 0) ? $stmt : false;
  } catch(PDOExpcetion $e){
    echo "Erro: " . $e->getMessage();
  }
} 

$varlog = array();
$varlog2 = array();
$i = 0;
$reg = 0;
$registros = Recregistros($pdo); 
$codigos_instit= rec_instit($pdo);

foreach($codigos_instit as $codigo_instit){
	
	foreach($registros as $registro){

		$codigo_cla= seqcodcla($pdo);
		$dados['t64_codcla']  = $codigo_cla;
		$dados['t64_class']   = $registro->t64_class;
		$dados['t64_descr']   = trim($registro->t64_descr);
		$dados['t64_obs']     = trim($registro->t64_obs);
		$dados['t64_analitica'] = ($registro->t64_analitica == 1) ? 'true' : 'false';	
		$dados['t64_bemtipos']  = $registro->t64_bemtipos;
		$dados['t64_benstipodepreciacao'] = $registro->t64_benstipodepreciacao;
		$dados['t64_vidautil'] = $registro->t64_vidautil;
		$dados['t64_instit']   = $codigo_instit->instit;

		print("-------------------------------------------------------------------------------\n\n");
		$linha = 't64_codcla: ' . $dados['t64_codcla'] .  ' - ' . 't64_class: ' . $dados['t64_class'] .  ' - '  . 't64_descr: ' . $dados['t64_descr'] . ' - ' . 't64_obs: ' . $dados['t64_obs'] .  ' - ' . 't64_analitica: ' . $dados['t64_analitica'] .  ' - '  . 't64_bemtipos: ' . $dados['t64_bemtipos'] .  ' - ' . 't64_benstipodepreciacao: ' . $dados['t64_benstipodepreciacao'] .  ' - ' . 't64_vidautil: ' . $dados['t64_vidautil'] .  ' - '  . 't64_instit: ' . $dados['t64_instit'];
		
		echo  $linha . "\n";
		echo "\n";

		if (insert_clabens($pdo,$dados))
		{
	  
	  	$varlog[$i] = $linha;
		  $reg++;
		
		} else {    
    
    	$varlog2[$i] = $linha;

	  }

  } 

}

$dir=dirname(__FILE__);

$arqlog = $dir . '/arq_log_migrados.txt';
$pfilelog = fopen($arqlog, 'a');
fwrite($pfilelog,  print_r($varlog, TRUE));
fclose($pfilelog);

$arqlog2 = $dir . '/arq_log2_nao_migrados.txt';
$pfilelog = fopen($arqlog2, 'a');
fwrite($pfilelog,  print_r($varlog2, TRUE));
fclose($pfilelog);

echo "\n";
echo "Total de registros atualizados: $reg";
echo "\n";
echo "\n";
echo "Arquivos de Logs:  $arqlog    e    $arqlog2";
echo "\n";
echo "\n";
echo 'FIM DA MIGRAÇÃO!';
echo "\n";

?>

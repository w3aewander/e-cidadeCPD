<?php
  require_once("libs/db_stdlib.php");
  require_once("libs/db_conecta.php");
  require_once("libs/db_sessoes.php");
  require_once("libs/db_usuariosonline.php");
  require_once("dbforms/db_funcoes.php");


  require_once('libs/db_conn.php');
  $con_string = "host=".$DB_SERVIDOR." port=".$DB_PORTA." dbname=".$DB_BASE." user=".$DB_USUARIO." password=".$DB_SENHA;
  $conexao = pg_connect($con_string);

 
  
  if ((isset($_POST['db_banco']))&&(isset($_POST['db_agencia']))&&(isset($_POST['db_digagencia']))&&(isset($_POST['db_conta']))&&(isset($_POST['db_digconta']))){
    
     $sql_where =
     " where INSTITUICAO= '".db_getsession("DB_instit")."' AND ".
     " BANCO= '".$_POST['db_banco']."' AND NUMERO_AGENCIA= '".$_POST['db_agencia']."' AND DIGITO_AGENCIA= '".$_POST['db_digagencia']."' AND ".
     " NUMERO_CONTA= '".$_POST['db_conta']."' AND DIGITO_CONTA='".$_POST['db_digconta']."' ";
   
     $sql_cad =
     " insert into FIN_CONTA_FORA_CONCILIACAO (INSTITUICAO, ".
     " BANCO, NUMERO_AGENCIA, DIGITO_AGENCIA, ".
     " NUMERO_CONTA, DIGITO_CONTA, TIPO_CONTA) ".
     " select ".db_getsession("DB_instit").", '".$_POST['db_banco']."', '".$_POST['db_agencia']."', '".$_POST['db_digagencia']."', '".$_POST['db_conta']."', '".$_POST['db_digconta']."', '".$_POST['db_tipoconta']."' ".
     " from dual ".
     " where not exists (select 1 from FIN_CONTA_FORA_CONCILIACAO ". $sql_where .") ";
    
     $sql_exclu =
     " delete from FIN_CONTA_FORA_CONCILIACAO ". $sql_where ;
   
     if (isset($_POST['salvar'])){
	     pg_exec ($conexao, $sql_exclu);

             pg_exec ($conexao, $sql_cad);
     }
   
     if (isset($_POST['excluir'])){
             pg_exec ($conexao, $sql_exclu);
     }
   }



   echo "<table>";
   echo "<caption>Contas Cadastradas</caption>";
   echo "<tr><th>Banco</th><th>Agencia</th><th>Conta</th><th>Tipo</th></tr>";

   $result = pg_exec ($conexao, "select * from FIN_CONTA_FORA_CONCILIACAO  where INSTITUICAO =0".db_getsession("DB_instit"));
   $numrows = pg_numrows($result);
   $id_row = 0;
   while ($id_row < $numrows) {
      $row = pg_fetch_array($result, $id_row);
      echo "<tr><td>".$row["banco"]."</td><td>".$row["numero_agencia"]."-".$row["digito_agencia"]."</td>";
      echo "<td>".$row["numero_conta"]."-".$row["digito_conta"]."</td><td>".$row["tipo_conta"]."</td></tr>";
      $id_row = $id_row + 1;
  }

   echo "</table>";


   pg_close ($conexao);
?>

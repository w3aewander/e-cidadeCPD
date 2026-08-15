<?php
//error_reporting(E_ALL); 
//ini_set('display_errors', '1');
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

require_once modification("libs/db_stdlib.php");
require_once modification("libs/db_conecta.php");
require_once modification("libs/db_sessoes.php");
require_once modification("libs/db_usuariosonline.php");
require_once modification("dbforms/db_funcoes.php");

//require_once 'SimpleXLSX.php';

function testa($var){
  echo "<pre>";
  print_r($var);
  echo "</pre>";
}

//[DB_uol_hora] => 1706106807
//[DB_datausu] => 1706106807
$diasistema = date("Y-m-d", db_getsession("DB_datausu"));
$horasistema = date("H:i:s", db_getsession("DB_uol_hora"));

function voltaCodmater($descricao){
  $sql = pg_query("SELECT * FROM matmater WHERE m60_descr = '{$descricao}'");
  $resultado = pg_fetch_all($sql);
  return $resultado;
}

function proximoSequencial($classe){
  $sql = pg_query("SELECT nextval('{$classe}')");
  $resultado = pg_fetch_all($sql);
  return $resultado[0]["nextval"];    
}

function verificamatmaterunisai($codmater){
  $sql = pg_query("SELECT * FROM matmaterunisai WHERE m62_codmater = {$codmater}");
  $resultado = pg_fetch_all($sql);
  return $resultado[0];
}
    
function matmatermaterialestoquegrupo($codmater){
  $sql = pg_query("SELECT * FROM matmatermaterialestoquegrupo WHERE m68_matmater = {$codmater}");
  $resultado = pg_fetch_all($sql);
  return $resultado[0];
}


if($_FILES){
  $nomearquivo = $_FILES['uploadedFile']['name'];

  $upload = fopen($_FILES['uploadedFile']['tmp_name'], 'r');
  $acsv = array();
  while (($linha = fgets($upload)) !== false){
    array_push($acsv, $linha);
  }

  $indice = 0;
  $dados = array();
  foreach ($acsv as $linha){
    $info = explode(";", $linha);
    $dados[$indice][0] = trim($info[0]);
    $dados[$indice][1] = trim(utf8_decode($info[1]));
    $dados[$indice][2] = trim($info[2]);
    $dados[$indice][3] = trim(utf8_decode($info[3]));
    $dados[$indice][4] = trim($info[4]);
    $dados[$indice][5] = trim($info[5]);
    $dados[$indice][6] = trim($info[6]);
    $dados[$indice][7] = trim($info[7]);
    $dados[$indice][8] = trim($info[8]);
    $dados[$indice][9] = trim($info[9]);
    $dados[$indice][10] = trim($info[10]);
    $indice++;
  }

//testa($dados); die("Confere");
/*
[0] => coddepto
[1] => descrdepto
[2] => m60_codant
[3] => m60_descr
[4] => m65_sequencial
[5] => GRUPO_DESCRICAO
[6] => m60_codmatunid
[7] => m61_descr
[8] => m71_quant
[9] => VALOR_UNITARIO
[10] => VALOR_TOTAL
*/


  $c = 1;
  foreach ($dados as $linha){
    if($c == 1){
      $c++;
      continue;
    }    
    
    $descricao = trim($linha[3]);
    $confere = voltaCodmater($descricao);        
    //echo "=====================" . $descricao . "========================"; echo "<br>";
    if($confere){      
      $m60_codmater = $confere[0]["m60_codmater"];
      
    }else{
      $m60_codmater = proximoSequencial("matmater_m60_codmater_seq");

      
      $m60_descr = substr($descricao, 0, 80);    
      $m60_codmatunid = $linha[6];
      $m60_quantent = 1;
      //$m60_codant = $linha[3];
      $m60_codant = $linha[2];
      $m60_ativo = "t";
      $m60_controlavalidade = 3;

      $m62_codmater = $m60_codmater;
      $m62_codmatunid = $linha[6];
    
      $m68_sequencial = proximoSequencial("matmatermaterialestoquegrupo_m68_sequencial_seq");    
      $m68_matmater = $m60_codmater;      
      $m68_materialestoquegrupo = $linha[4];

      $sql = "INSERT INTO matmater VALUES({$m60_codmater}, '{$m60_descr}', {$m60_codmatunid}, {$m60_quantent}, '{$m60_codant}', 't', 3);";
      $sql2 = "INSERT INTO matmaterunisai VALUES ({$m62_codmater}, {$m62_codmatunid});";
      $sql3 = "INSERT INTO matmatermaterialestoquegrupo VALUES({$m68_sequencial}, {$m68_matmater}, {$m68_materialestoquegrupo});";
      //$sql = pg_query($conn, "INSERT INTO matmater VALUES({$m60_codmater}, '{$m60_descr}', {$m60_codmatunid}, {$m60_quantent}, '{$m60_codant}', 't', 3)");
      //echo "INSERT INTO matmater VALUES({$m60_codmater}, '{$m60_descr}', {$m60_codmatunid}, {$m60_quantent}, '{$m60_codant}', 't', 3)"; echo "<br>";
      //$sql2 = pg_query($conn,"INSERT INTO matmaterunisai VALUES ({$m62_codmater}, {$m62_codmatunid})");
      //$sql3 = pg_query($conn,"INSERT INTO matmatermaterialestoquegrupo VALUES({$m68_sequencial}, {$m68_matmater}, {$m68_materialestoquegrupo})");
      pg_query($sql);
      pg_query($sql2);
      pg_query($sql3);

      
      
      //echo $sql; echo "<br>"; echo $sql2; echo "<br>"; echo $sql3; echo "<br>";
    }

    
    
    $conf2 = verificamatmaterunisai($m60_codmater);
    if(empty($conf2)){
      $m62_codmater = $m60_codmater;
      $m62_codmatunid = $linha[6];
      //$usql2 = "UPDATE matmaterunisai SET m62_codmatunid = {$m62_codmatunid} WHERE $m62_codmater = {$m62_codmater};";  
      $sql2 = "INSERT INTO matmaterunisai VALUES ({$m62_codmater}, {$m62_codmatunid});";
      pg_query($sql2);
      //echo $sql2; echo "<br>";
    }

    $conf3 = matmatermaterialestoquegrupo($m60_codmater);
    if(empty($conf3)){
      //Insere
      $m68_sequencial = proximoSequencial("matmatermaterialestoquegrupo_m68_sequencial_seq");    
      $m68_matmater = $m60_codmater;
      $m68_materialestoquegrupo = $linha[4];
      $sql3 = "INSERT INTO matmatermaterialestoquegrupo VALUES({$m68_sequencial}, {$m68_matmater}, {$m68_materialestoquegrupo});";
      pg_query($sql3);
      //echo $sql3; echo "<br>";      
    }else{
      //Atualiza
      $m68_matmater = $m60_codmater;
      $m68_materialestoquegrupo = $linha[4];
      $usql3 = "UPDATE matmatermaterialestoquegrupo SET m68_materialestoquegrupo = {$m68_materialestoquegrupo} WHERE m68_matmater = {$m68_matmater};";  
      pg_query($usql3);
      //echo $usql3; echo "<br>";
    }
    
    

    //INSERE MATESTOQUE
    $m70_codigo = proximoSequencial("matestoque_m70_codigo_seq");  
    $m70_codmatmater = $m60_codmater;
    $m70_coddepto = $linha[0];
    
        
    

    //$m70_quant = number_format($linha[8], 2, '.', '');
    $m70_quant = str_replace(",", ".", $linha[8]);
    
    //$m70_valor = number_format($linha[9], 2, '.', '');
    $m70_valor = str_replace(",", ".", $linha[9]);

    $sqlme = "INSERT INTO matestoque VALUES({$m70_codigo}, {$m70_codmatmater}, {$m70_coddepto}, {$m70_quant}, {$m70_valor});";
    pg_query($sqlme);
    //echo $sqlme; echo "<br>";

    //INSERE MATESTOQUEINI   
    $m80_codigo = proximoSequencial("matestoqueini_m80_codigo_seq");   
    $m80_login = 1;
    $m80_data = $diasistema; //date("Y-m-d");
    $m80_obs = utf8_decode('IMPLANTAÇÃO DE ESTOQUE');
    $m80_codtipo = 3; //1;
    $m80_coddepto = $linha[0];    
    $m80_hora = $horasistema; //date("H:i:s");

    $sqlmeini = "INSERT INTO matestoqueini VALUES({$m80_codigo}, {$m80_login}, '{$m80_data}', '{$m80_obs}', {$m80_codtipo}, {$m80_coddepto}, '{$m80_hora}');";
    pg_query($sqlmeini);
    //echo $sqlmeini; echo "<br>";
  
    //INSERETE MATESTOQUEITEM  
    $m71_codlanc = proximoSequencial("matestoqueitem_m71_codlanc_seq");  
    $m71_codmatestoque = $m70_codigo;
    $m71_data = $diasistema; //date("Y-m-d");
    
    //$m71_quant = floatval($linha[8]);
    $m71_quant = str_replace(",", ".", $linha[8]);
    
    //$m71_valor = number_format($linha[10], 2, '.', '');
    $m71_valor = str_replace(",", ".", $linha[10]);;
    $m71_quantatend = 0;
    $m71_servico = 'f';

    $sqlmei = "INSERT INTO matestoqueitem VALUES({$m71_codlanc}, {$m71_codmatestoque}, '{$m71_data}', {$m71_quant}, {$m71_valor}, {$m71_quantatend}, '{$m71_servico}');";
    pg_query($sqlmei);
    //echo $sqlmei; echo "<br>";

    //INSERE MATESTOQUEINIMEI  
    $m82_codigo = proximoSequencial("matestoqueinimei_m82_codigo_seq");
    $m82_matestoqueini = $m80_codigo;
    $m82_matestoqueitem = $m71_codlanc;
    $m82_quant = $m70_quant;

    $sqlmeinimei = "INSERT INTO matestoqueinimei VALUES({$m82_codigo}, {$m82_matestoqueini}, {$m82_matestoqueitem}, {$m82_quant});";
    pg_query($sqlmeinimei);
    //echo $sqlmeinimei; echo "<br>";
    //echo "<hr>";
  }//Fim do foreach
  
  
echo "<script>alert('Dados Migrados');</script>";



}//Fim do post




?>
<html>
  <head>
    <title>Microsist</title>
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
    <meta http-equiv="Expires" CONTENT="0">
    <script language="JavaScript" type="text/javascript" src="scripts/scripts.js"></script>
    <link href="estilos.css" rel="stylesheet" type="text/css">
  </head>
  <body class="body-default">
  	<div class="container">      
        

      <form method="post" action="" enctype="multipart/form-data">
      <fieldset>
        <legend style="font-weight: bold;">Importar Arquivo</legend>
        <table>
          <tr>
            <td style="font-weight: bold;">
              Arquivo:
            </td>
            <td>
              <input type="file" name="uploadedFile" />
            </td>
          </tr>
        </table>
      </fieldset>

      
      <p align="center">
        <input type="submit" name="gerar" value="Importar">
      </p>
    
      
    </form>


    </div>
<?
db_menu(db_getsession("DB_id_usuario"),db_getsession("DB_modulo"),db_getsession("DB_anousu"),db_getsession("DB_instit"));
?>
  </body>
</html>









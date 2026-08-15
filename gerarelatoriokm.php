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
require_once("libs/db_stdlib.php");
require_once("libs/db_utils.php");
require_once("std/db_stdClass.php");
require_once("libs/db_conecta.php");
require_once("libs/db_app.utils.php");
require_once("libs/db_sessoes.php");
require_once("libs/JSON.php");
require_once("fpdf151/PDFDocument.php");

$oJson  = new services_json();

$oParam = db_utils::postMemory($_GET);

function retornaNomeSecretaria($codigo){
  $sql = pg_query("SELECT descrdepto from veiccadcentral inner join db_depart on db_depart.coddepto = veiccadcentral.ve36_coddepto inner join db_config on db_config.codigo = db_depart.instit WHERE ve36_sequencial = {$codigo}");
  $resultado = pg_fetch_all($sql);
  return $resultado[0]["descrdepto"];
}

function buscaDados($consulta){
  $sql = pg_query($consulta);
  $resultado = pg_fetch_all($sql);
  return $resultado;
}


$vr = strtoupper($vr);

if(isset($aVeiculos) && !empty($aVeiculos)){
  $sql = "SELECT ve01_codigo, ve01_placa, ve60_medidasaida, ve61_medidadevol, vr, ve60_datasaida, ve61_datadevol FROM veiculos inner join veicretirada on ve60_veiculo = ve01_codigo inner join veicdevolucao on ve61_veicretirada = ve60_codigo inner join veiccentral on ve40_veiculos = ve01_codigo  where ve40_veiccadcentral = {$codigo_central} and ve60_datasaida between '{$periodo_inicial}' and '{$periodo_final}' and ve61_datadevol between '{$periodo_inicial}' and '{$periodo_final}' AND ve01_codigo in({$aVeiculos}) ORDER BY ve60_datasaida";
}else{  
  $sql = "SELECT ve01_codigo, ve01_placa, ve60_medidasaida, ve61_medidadevol, vr, ve60_datasaida, ve61_datadevol FROM veiculos inner join veicretirada on ve60_veiculo = ve01_codigo inner join veicdevolucao on ve61_veicretirada = ve60_codigo inner join veiccentral on ve40_veiculos = ve01_codigo  where ve40_veiccadcentral = {$codigo_central} and ve60_datasaida between '{$periodo_inicial}' and '{$periodo_final}' and ve61_datadevol between '{$periodo_inicial}' and '{$periodo_final}' AND vr = '{$vr}' ORDER BY ve60_datasaida";
}


$relini = implode("/", array_reverse(explode("-", $periodo_inicial)));
$relfin = implode("/", array_reverse(explode("-", $periodo_final)));
$secretaria = retornaNomeSecretaria($codigo_central);
$dados = buscaDados($sql);
$totalfinal = 0;
$veiculo = $dados[0]["ve01_placa"] . " - " . $dados[0]["vr"];


header("Content-Type:   application/vnd.ms-excel; charset=utf-8");
header("Content-type:   application/x-msexcel; charset=utf-8");
header("Content-Disposition: attachment; filename=relatoriokm.xls");
header("Expires: 0");
header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
header("Cache-Control: private",false);


print "<table>";
print "<tr><td colspan='5'><b>RELATÓRIO DE KM RODADOS NO PERÍODO</b></td></tr>";

print "<tr><td colspan='5'><span style='font-weight:bold'><b>SECRETARIA:</b></span> </b>" .$secretaria . "</td></tr>";
print "<tr><td colspan='5'><b>PERÍODO:</b> {$relini} a {$relfin}</td></tr>";
print "<tr><td colspan='5'><b>VEÍCULO:</b> {$veiculo}</td></tr>";
print "</table>";

print "<table>";
print "<tr><td> </td></tr>";
print "</table>";



print "<table border=1>";

print "<thead>";
print "<tr>";
  print "<th colspan='2'>Retirada</th>";
  print "<th colspan='2'>Devolução</th>";
  print "<th colspan='2' rowspan='2'>KM RODADOS (D-R)</th>";
print "</tr>";

print "<tr>";
print "<th>Data</th>";
print "<th>Km</th>";
print "<th>Data</th>";
print "<th>Km</th>";
print "</tr>";


$total = 0;

foreach ($dados as $linha){
  $diferenca = (int)$linha["ve61_medidadevol"] - (int)$linha["ve60_medidasaida"];
  $total += $diferenca;
  $dataretirada = implode("/", array_reverse(explode("-", $linha["ve60_datasaida"])));
  $datadevolucao = implode("/", array_reverse(explode("-", $linha["ve61_datadevol"])));
  print "<tr>";
  print "<td>".$dataretirada."</td>";
  print "<td>".$linha["ve60_medidasaida"]."</td>";
  print "<td>".$datadevolucao."</td>";
  print "<td>".$linha["ve61_medidadevol"]."</td>";
  print "<td colspan='2'>". $diferenca ."</td>";
  print "</tr>";
}
print "<tr>";
print "<center>";
print "<td colspan='5'>Total</td>";
print "</center>";
print "<td>".$total."</td>";
print "</tr>";
print "</table>";



exit();
<?php
/*
 *     E-cidade Software Publico para Gestao Municipal                
 *  Copyright (C) 2012  DBselller Servicos de Informatica             
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
require_once("libs/db_conecta.php");
require_once("libs/db_sessoes.php");
require_once("libs/db_usuariosonline.php");

require_once("model/patrimonio/Bem.model.php");
require_once("model/patrimonio/BemCedente.model.php");
require_once("model/patrimonio/BemClassificacao.model.php");
require_once("model/patrimonio/PlacaBem.model.php");
require_once("model/patrimonio/BemHistoricoMovimentacao.model.php");
require_once("model/patrimonio/BemDadosMaterial.model.php");
require_once("model/patrimonio/BemDadosImovel.model.php");
require_once("model/patrimonio/BemTipoAquisicao.php");
require_once("model/patrimonio/BemTipoDepreciacao.php");
require_once("model/CgmFactory.model.php");


function buscaUsuario($id){
  $sql = pg_query("SELECT nome FROM db_usuarios WHERE id_usuario = {$id}");
  $resultado = pg_fetch_all($sql);
  return $resultado[0]["nome"];
}

function buscaDadosAgregacao($codbem){
  $sql = pg_query("SELECT * FROM bensdepreciacao INNER JOIN benscorr ON t44_bens = t63_codbem WHERE t63_codbem = {$codbem}");
  $resultado = pg_fetch_all($sql);
  return $resultado;
}



$oGet = db_utils::postMemory($_GET, false);
$oBem = new Bem($oGet->t52_bem);



$oMaterial = $oBem->getDadosCompra();


$consulta = buscaDadosAgregacao($oGet->t52_bem);

/*
$codigo = $consulta["t63_codbem"];
$usuario = buscaUsuario($consulta["t44_usuario"]);
$valoranterior = number_format($consulta["t44_valoranterior"], 2, ',', '.');
$processo = $consulta["t63_processoadm"];
$dataprocessamento = implode("/", array_reverse(explode("-", $consulta["t44_dataprocessamento"])));

if($consulta["t63_agregarvalor"] != 0){
     $valoralterado = "+ " . number_format($consulta["t63_agregarvalor"], 2, ',', '.');
} else {
     $valoralterado = "- " . number_format($consulta["t63_corrigirvalor"], 2, ',', '.');
}

$valortotal = number_format($consulta["t44_valoratual"], 2, ',', '.');
$justificativa = $consulta["t63_justificativa"];



var_dump($codigo);
echo "<br>";
var_dump($usuario);
echo "<br>";
var_dump($valoranterior);
echo "<br>";
var_dump($processo);
echo "<br>";
var_dump($dataprocessamento);
echo "<br>";
var_dump($valoralterado);
echo "<br>";
var_dump($valortotal);
echo "<br>";
var_dump($justificativa);
echo "<br>";

*/

//echo "<pre>";
//print_r($consulta);
//echo "</pre>";
//die("Confere Valores");



?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link href="estilos.css" rel="stylesheet" type="text/css">
<style type='text/css'>
	.valores {background-color:#FFFFFF}
</style>
</head>
  <body>
    <center>
      <fieldset>
        <legend><strong>Histórico Agregação</strong></legend>
          <?php foreach($consulta as $dados) : ?>
          <table width="100%">
          
          <?php  
               $codigo = $dados["t63_codcor"];
               $usuario = buscaUsuario($dados["t63_usuario"]);
               //$valoranterior = number_format($dados["t63_valoranterior"], 2, ',', '.');
               $valoranterior = $dados["t63_valoranterior"];               
               $processo = $dados["t63_processoadm"];
               $dataprocessamento = implode("/", array_reverse(explode("-", $dados["t63_dataprocessamento"])));

               if($dados["t63_agregarvalor"] != 0){
                    //$valoralterado = number_format($dados["t63_agregarvalor"], 2, ',', '.');
                    $valoralterado = $dados["t63_agregarvalor"];                    
                    $valortotal = $valoranterior + $valoralterado;
                    $valoralterado = number_format($dados["t63_agregarvalor"], 2, ',', '.');
               } else {
                    //$valoralterado = number_format($dados["t63_corrigirvalor"], 2, ',', '.');
                      $valoralterado = $dados["t63_corrigirvalor"];
                    //var_dump($valoranterior); var_dump($valoralterado);
                    $valortotal = $valoranterior - $valoralterado;
                    //1015133.75 - 
                    //var_dump($valortotal);
                    //die("Total");
                    $valoralterado = number_format($dados["t63_corrigirvalor"], 2, ',', '.');
               }

               $valortotal = number_format($valortotal, 2, ',', '.');
               $valoranterior = number_format($dados["t63_valoranterior"], 2, ',', '.');
               
               $justificativa = $dados["t63_justificativa"];
               //var_dump($dados);
          ?>
          
          	<tr>          	
          		<td width="10%"><b>Código: </b></td>
          		<td width="20%" class="valores"><?=($codigo) ? $codigo : ""?></td>
          		
          		<td width="10%"><b>Usuário: </b></td>
          		<td width="20%" class="valores"><?=($usuario) ? $usuario : ""?></td>

                    <td width="10%"><b>Valor Anterior: </b></td>
                    <td width="20%" class="valores"><?=($valoranterior) ? $valoranterior : ""?></td>          	
          	</tr>

               <tr>           
                    <td width="10%"><b>Processo Adm: </b></td>
                    <td width="20%" class="valores"><?=($processo) ? $processo : ""?></td>
                    
                    <td width="10%"><b>Data Processamento: </b></td>
                    <td width="20%" class="valores"><?=($dataprocessamento) ? $dataprocessamento : ""?></td>

                    <td width="10%"><b>Valor Agregado/Estornado: </b></td>
                    <td width="20%" class="valores"><?=($valoralterado) ? $valoralterado : ""?></td>             
               </tr>

               <tr>           
                    <td width="10%"><b></b></td>
                    <td width="20%"></td>
                    
                    <td width="10%"><b></b></td>
                    <td width="20%"></td>

                    <td width="10%"><b>Valor Total: </b></td>
                    <td width="20%" class="valores"><?=($valortotal) ? $valortotal : ""?></td>
               </tr>

               
               <?php /* ?>
               <tr>
                    <td width="10%"><b>Justificativa: </b></td>
                    <td>
                    <textarea rows="5" cols="80" maxlength="500" readonly><?=($valortotal) ? $valortotal : ""?></textarea>
                    </td>                    
               </tr>
               <?php */ ?>
               
               
               
          </table>
          <div style="width:80%;float:left;margin-bottom:25px">
          <span><b>Justificativa:</b></span><br>
          <textarea rows="5" cols="80" maxlength="500" readonly><?=($justificativa) ? $justificativa : ""?></textarea>
          </div>
          <?php endforeach; ?>
      </fieldset>
    </center>
  </body>
</html>
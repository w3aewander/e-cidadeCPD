<?
/*
 *     E-cidade Software Publico para Gestao Municipal                
 *  Copyright (C) 2009  DBselller Servicos de Informatica             
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

include("fpdf151/pdf.php");
include("libs/db_sql.php");
include("classes/db_matmater_classe.php");
$clrotulo = new rotulocampo;
$clmatmater = new cl_matmater;
$clrotulo->label('m60_codmater');
$clrotulo->label('m60_descr');
$clrotulo->label('m60_quantent');
$clrotulo->label('pc01_codmater');
$clrotulo->label('pc01_descrmater');
//$clrotulo->label('m61_abrev');

parse_str($HTTP_SERVER_VARS['QUERY_STRING']);
//db_postmemory($HTTP_SERVER_VARS,2);exit;

$instituicao = db_getsession("DB_instit");



$xordem  = '';
//$dbwhere = "1=1 ";
$dbwhere = "1=1 ";

if ($ordem == 'n') {
  if ($tipo_ordem == 'a') {
    $xordem = 'm60_codmater asc ';
    $head5 = " ORDEM : NUMÉRICA - ASCENDENTE";
  } else {
    $xordem = 'm60_codmater desc ';
    $head5 = " ORDEM : NUMÉRICA - DESCENDENTE";
  }
} else if ($ordem == 'a') {
  if ($tipo_ordem == 'a') {
    $xordem = 'm60_descr asc ';
    $head5 = " ORDEM : ALFABÉTICA - ASCENDENTE";
  } else {
    $xordem = ' m60_descr desc ';
    $head5 = " ORDEM : ALFABÉTICA - DESCENDENTE";
  }
}

if (isset($listar_mat)&&trim($listar_mat)!="") {
  if (trim($listar_mat) == "I") {
    $head6    = " MATERIAIS: INATIVOS";
    $dbwhere .= "and m60_ativo is false";
  }
  
  if (trim($listar_mat) == "A") {
    $head6    = " MATERIAIS: ATIVOS";
    $dbwhere .= "and m60_ativo is true";
  }
  
  if (trim($listar_mat) == "T") {
    $head6   = " MATERIAIS: TODOS";
  }
}

$info_listar_serv = "";

if ($listar_serv == "M") {
  
  $dbwhere          .= " and (pc01_servico is false or pc01_servico is null) ";
  $info_listar_serv .= " LISTAR: SOMENTE MATERIAIS";
  
} else if ($listar_serv == "S") {
  
  $dbwhere          .= " and pc01_servico is true ";
  $info_listar_serv .= " LISTAR: SOMENTE SERVIÇOS";
  
} else {
  $info_listar_serv = " LISTAR: TODOS";
}

$dbwhere .= " AND instit = {$instituicao}";

$campos = " distinct m60_codmater,trim(m60_descr) as m60_descr,pc01_codmater,trim(pc01_descrmater) as pc01_descrmater";

if ($listar_serv == "T") {   
  $sql = $clmatmater->sql_query_com2(null, $campos, $xordem, $dbwhere);  
} else {    
  $sql = $clmatmater->sql_query_com_pcmater2(null, $campos, $xordem, $dbwhere);  
}



$result =$clmatmater->sql_record($sql);
$dados = pg_fetch_all($result);




header("Content-Type:   application/vnd.ms-excel; charset=utf-8");
header("Content-type:   application/x-msexcel; charset=utf-8");
header("Content-Disposition: attachment; filename=materiais_interligados.xls");
header("Expires: 0");
header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
header("Cache-Control: private",false);

print "<table border=1>";
print "<thead>";
print "<tr>";
print "<th colspan='6'>Almoxarifado</th>";
print "<th colspan='6'>Compras</th>";
print "</tr>";
print "<tr>";
print "<th>Cod. Material</th>";
print "<th colspan='5'>Descrição</th>";
print "<th>Cod. Material</th>";
print "<th colspan='5'>Descrição</th>";
print "</thead>";
print '</tr>';



foreach ($dados as $linha){

  print "<tr>";    
    print "<td>" . $linha["m60_codmater"] . "</td>";
    print "<td colspan='5'>" . $linha["m60_descr"] . "</td>";
    print "<td>" . $linha{"pc01_codmater"} . "</td>";
    print "<td colspan='5'>" . $linha["pc01_descrmater"] . "</td>";    
  print "</tr>";
    
  }




?>
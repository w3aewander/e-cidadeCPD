<?
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




 
require_once(modification("fpdf151/pdf.php"));
require_once(modification("libs/db_sql.php"));
require_once(modification("libs/db_utils.php"));
require_once(modification("classes/db_matestoque_classe.php"));
require_once(modification("classes/db_matestoqueitem_classe.php"));
require_once(modification("classes/db_db_almox_classe.php"));
require_once(modification("classes/materialestoque.model.php"));
require_once(modification("classes/db_empparametro_classe.php"));
require_once modification("libs/db_app.utils.php");

/*db_app::import("contabilidade.contacorrente.ContaCorrenteFactory");
db_app::import("Acordo");
db_app::import("AcordoComissao");
db_app::import("CgmFactory");
db_app::import("financeiro.*");
db_app::import("contabilidade.*");
db_app::import("contabilidade.lancamento.*");
db_app::import("Dotacao");
db_app::import("contabilidade.planoconta.*");
db_app::import("contabilidade.contacorrente.*");
*/
$oParametros      = db_utils::postMemory($_GET);
$clmatestoque     = new cl_matestoque;
$clmatestoqueitem = new cl_matestoqueitem;
$cldb_almox       = new cl_db_almox;




function testa($var){
  echo "<pre>";
  print_r($var);
  echo "</pre>";
}



function buscaId($id){
  $sql = pg_query("SELECT * FROM controleentradanota WHERE id = {$id}");
  $resultado = pg_fetch_all($sql);
  return $resultado[0];
}

function buscaDados2($nuinst, $e69_numero, $e69_dtnota, $e69_dtrecebe, $e69_dtvencimento, $e70_valor, $m51_codordem, $m51_numcgm){
  $sql = pg_query("SELECT * from matestoque inner join db_depart on db_depart.coddepto = matestoque.m70_coddepto inner join matmater on matmater.m60_codmater = matestoque.m70_codmatmater inner join matunid on matunid.m61_codmatunid = matmater.m60_codmatunid inner join matestoqueitem on m71_codmatestoque = m70_codigo inner join matestoqueitemnota on m74_codmatestoqueitem = m71_codlanc inner join matestoqueitemoc on m73_codmatestoqueitem = m71_codlanc inner join matordemitem on m52_codlanc = m73_codmatordemitem inner join empnota on e69_codnota = m74_codempnota inner join matordem on m52_codordem = m51_codordem inner join empnotaele on e69_codnota = e70_codnota inner join cgm on z01_numcgm = m51_numcgm where instit = {$nuinst} AND e69_numero = '{$e69_numero}' AND e69_dtnota = '{$e69_dtnota}' AND e69_dtrecebe = '{$e69_dtrecebe}' AND e69_dtvencimento = '{$e69_dtvencimento}' AND e70_valor = '{$e70_valor}' AND m51_codordem = {$m51_codordem} AND m51_numcgm = {$m51_numcgm}");
  $resultado = pg_fetch_all($sql);
  return $resultado;
}

function buscaDados3($idnrm){
  $sql = pg_query("SELECT * FROM materiaisnrm WHERE idnrm = {$idnrm}");  
  $resultado = pg_fetch_all($sql);
  return $resultado;
}



/*function buscaDados($nuinst, $e69_numero, $e69_dtnota, $e69_dtrecebe, $e69_dtvencimento, $e70_valor, $m51_codordem, $m51_numcgm){
  $sql = pg_query("SELECT matmater.m60_codmater, matmater.m60_descr, db_depart.instit, empnota.e69_codnota, empnota.e69_numero, empnota.e69_dtnota, empnota.e69_numemp, matordem.m51_numcgm, matordem.m51_codordem, cgm.z01_numcgm, cgm.z01_nome, matestoqueitem.m71_quant, matestoqueitem.m71_valor, matestoqueitem.m71_data, m61_abrev, m52_vlruni from matestoque inner join db_depart on db_depart.coddepto = matestoque.m70_coddepto inner join matmater on matmater.m60_codmater = matestoque.m70_codmatmater inner join matunid on matunid.m61_codmatunid = matmater.m60_codmatunid inner join matestoqueitem on m71_codmatestoque = m70_codigo inner join matestoqueitemnota on m74_codmatestoqueitem = m71_codlanc inner join matestoqueitemoc on m73_codmatestoqueitem = m71_codlanc inner join matordemitem on m52_codlanc = m73_codmatordemitem inner join empnota on e69_codnota = m74_codempnota inner join matordem on m52_codordem = m51_codordem inner join empnotaele on e69_codnota = e70_codnota inner join cgm on z01_numcgm = m51_numcgm where instit = {$nuinst} AND e69_numero = '{$e69_numero}' AND e69_dtnota = '{$e69_dtnota}' AND e69_dtrecebe = '{$e69_dtrecebe}' AND e69_dtvencimento = '{$e69_dtvencimento}' AND e70_valor = '{$e70_valor}' AND m51_codordem = {$m51_codordem} AND m51_numcgm = {$m51_numcgm}");
  $resultado = pg_fetch_all($sql);
  return $resultado;
}*/

function buscaDados($nuinst, $e69_numero, $e69_dtnota, $e70_valor, $m51_codordem, $m51_numcgm){
  $kql = "SELECT matmater.m60_codmater, matmater.m60_descr, db_depart.instit, empnota.e69_codnota, empnota.e69_numero, empnota.e69_dtnota, empnota.e69_numemp, matordem.m51_numcgm, matordem.m51_codordem, cgm.z01_numcgm, cgm.z01_nome, matestoqueitem.m71_quant, matestoqueitem.m71_valor, matestoqueitem.m71_data, m61_abrev, m52_vlruni from matestoque inner join db_depart on db_depart.coddepto = matestoque.m70_coddepto inner join matmater on matmater.m60_codmater = matestoque.m70_codmatmater inner join matunid on matunid.m61_codmatunid = matmater.m60_codmatunid inner join matestoqueitem on m71_codmatestoque = m70_codigo inner join matestoqueitemnota on m74_codmatestoqueitem = m71_codlanc inner join matestoqueitemoc on m73_codmatestoqueitem = m71_codlanc inner join matordemitem on m52_codlanc = m73_codmatordemitem inner join empnota on e69_codnota = m74_codempnota inner join matordem on m52_codordem = m51_codordem inner join empnotaele on e69_codnota = e70_codnota inner join cgm on z01_numcgm = m51_numcgm where instit = {$nuinst} AND e69_numero = '{$e69_numero}' AND e69_dtnota = '{$e69_dtnota}' AND e70_valor = '{$e70_valor}' AND m51_codordem = {$m51_codordem} AND m51_numcgm = {$m51_numcgm}";



  $sql = pg_query("SELECT matmater.m60_codmater, matmater.m60_descr, db_depart.instit, empnota.e69_codnota, empnota.e69_numero, empnota.e69_dtnota, empnota.e69_numemp, matordem.m51_numcgm, matordem.m51_codordem, cgm.z01_numcgm, cgm.z01_nome, matestoqueitem.m71_quant, matestoqueitem.m71_valor, matestoqueitem.m71_data, m61_abrev, m52_vlruni from matestoque inner join db_depart on db_depart.coddepto = matestoque.m70_coddepto inner join matmater on matmater.m60_codmater = matestoque.m70_codmatmater inner join matunid on matunid.m61_codmatunid = matmater.m60_codmatunid inner join matestoqueitem on m71_codmatestoque = m70_codigo inner join matestoqueitemnota on m74_codmatestoqueitem = m71_codlanc inner join matestoqueitemoc on m73_codmatestoqueitem = m71_codlanc inner join matordemitem on m52_codlanc = m73_codmatordemitem inner join empnota on e69_codnota = m74_codempnota inner join matordem on m52_codordem = m51_codordem inner join empnotaele on e69_codnota = e70_codnota inner join cgm on z01_numcgm = m51_numcgm where instit = {$nuinst} AND e69_numero = '{$e69_numero}' AND e69_dtnota = '{$e69_dtnota}' AND e70_valor = '{$e70_valor}' AND m51_codordem = {$m51_codordem} AND m51_numcgm = {$m51_numcgm}");
  $resultado = pg_fetch_all($sql);
  return $resultado;
}

function retornaNome($idusuario){
  $sql = pg_query("SELECT nome FROM db_usuarios WHERE id_usuario = {$idusuario}");
  $resultado = pg_fetch_all($sql);
  return $resultado[0]["nome"];
}

function buscaDadosCgm($cgm){
  $sql = pg_query("SELECT z01_numcgm, z01_nome, z01_ender, z01_numero, z01_compl, z01_telef, z01_cgccpf FROM cgm WHERE z01_numcgm = {$cgm}");
  $resultado = pg_fetch_all($sql);
  return $resultado[0];
}

function buscaEmpenho($sequencial, $inst){
  $sql = pg_query("SELECT e60_numemp, e60_codemp, e60_anousu, e60_emiss FROM empempenho WHERE e60_numemp = {$sequencial} AND e60_instit = {$inst}");
  $resultado = pg_fetch_all($sql);
  return $resultado[0];
}

function buscaDescricaoOrgao($seqempenho){
  $sql = pg_query("SELECT e60_numemp, e60_codemp, o58_orgao, o40_descr FROM empempenho INNER JOIN orcdotacao ON e60_anousu = o58_anousu AND e60_coddot = o58_coddot INNER JOIN orcorgao ON o58_anousu = o40_anousu AND o58_orgao = o40_orgao WHERE e60_numemp =  {$seqempenho}");
  $resultado = pg_fetch_all($sql);
  return $resultado[0]["o40_descr"];
}

function retornaSequencial($ano, $instituicao){
  $sql = pg_query("SELECT max(sequencial) as sequencial FROM controleentradanota WHERE anousuario = {$ano} AND instituicao = {$instituicao}");
  $resultado = pg_fetch_all($sql);
  return $resultado[0]["sequencial"] + 1;
}

function formatar ($tipo = "", $string, $tamanho = 10){
    $string = ereg_replace("[^0-9]", "", $string);
    
    switch ($tipo){
        case 'fone':
            if($tamanho === 10){
             $string = '(' . substr($string, 0, 2) . ') ' . substr($string, 2, 4) 
             . '-' . substr($string, 6);
         }else
         if($tamanho === 11){
             $string = '(' . substr($string, 0, 2) . ') ' . substr($string, 2, 5) 
             . '-' . substr($string, 7);
         }
         break;
        case 'cep':
            $string = substr($string, 0, 5) . '-' . substr($string, 5, 3);
         break;
        case 'cpf':
            $string = substr($string, 0, 3) . '.' . substr($string, 3, 3) . 
                '.' . substr($string, 6, 3) . '-' . substr($string, 9, 2);
         break;
        case 'cnpj':
            $string = substr($string, 0, 2) . '.' . substr($string, 2, 3) . 
                '.' . substr($string, 5, 3) . '/' . 
                substr($string, 8, 4) . '-' . substr($string, 12, 2);
         break;
        
    }
    return $string;
}

$btabela = buscaId($_GET["id"]);





$nuinst = $btabela["instituicao"];
$e69_numero = $btabela["e69_numero"];
$e69_dtnota = $btabela["e69_dtnota"];
$e69_dtrecebe = $btabela["e69_dtrecebe"];
$e69_dtvencimento = $btabela["e69_dtvencimento"];
$e70_valor = $btabela["e70_valor"];
$m51_codordem = $btabela["m51_codordem"];
$m51_numcgm = $btabela["m51_numcgm"];
$codigopa = $btabela["codigopa"];
$iddousuario = $btabela["usuario"];
$anousuario = $btabela["anousuario"];
$novonrm = $btabela["sequencial"] . "/" . $btabela["anousuario"];
$obs =  $btabela["obs"];

$datainclusao = $btabela["datainclusao"];
$anulada = $btabela["anulada"];



$dadoscgm = buscaDadosCgm($m51_numcgm);
$dfornecedor = $dadoscgm["z01_numcgm"] . " - " . $dadoscgm["z01_nome"];
$dendereco = $dadoscgm["z01_ender"] . ", " . $dadoscgm["z01_numero"] . " Compl: " . $dadoscgm["z01_compl"];
$dtelefone = formatar("fone", $dadoscgm["z01_telef"]);

if(strlen($dadoscgm["z01_cgccpf"]) == 11){
  $tipo = "cpf";
}else{
  $tipo = "cnpj";
}
$dcnpj = formatar($tipo, $dadoscgm["z01_cgccpf"]);

//var_dump($nuinst, $e69_numero, $e69_dtnota, $e69_dtrecebe, $e70_valor, $m51_codordem, $m51_numcgm);
//$dados = buscaDados($nuinst, $e69_numero, $e69_dtnota, $e69_dtrecebe, $e70_valor, $m51_codordem, $m51_numcgm);
//$dados = buscaDados($nuinst, $e69_numero, $e69_dtnota, $e70_valor, $m51_codordem, $m51_numcgm);
$dados = buscaDados3($_GET["id"]);
//testa($dados);
//var_dump($dados3);
//die("Confere");
$dadosempenho = buscaEmpenho($dados[0]["e69_numemp"], $dados[0]["instit"]);
$numempenho = $dadosempenho["e60_codemp"] . "/" . $dadosempenho["e60_anousu"];
$dataempenho = implode("/", array_reverse(explode("-", $dadosempenho["e60_emiss"])));
$sequencialempenho = $dadosempenho["e60_numemp"];

$descorgao = buscaDescricaoOrgao($dadosempenho["e60_numemp"]);

$valortotal = number_format($e70_valor, 2, ",", "."); 



$nomeusuario = retornaNome(db_getsession("DB_id_usuario"));




$pdf = new PDF("P");
$pdf->Open();
$pdf->AliasNbPages();
$pdf->setfillcolor(235);
$pdf->SetAutoPageBreak(false);
$lEscreveHeader = true;

$head1 = "NRM: " . $novonrm;
$head2 = "Consumo";
$head3 = "Data Inclusão NRM: " . $datainclusao;
if($anulada == "sim"){
  $head4 = "ANULADA";
}




  /*
  if ($pdf->gety() > $pdf->h -20 || $lEscreveHeader) {
    $pdf->AddPage();
    $pdf->setfont('arial', 'b', 8);
    $pdf->Cell(15, $iAlt, "Material","RTB", 0, "C", 1);
    $pdf->Cell(75, $iAlt, "Descrição do Material", 1, 0, "C", 1);
    $pdf->Cell(32, $iAlt, "Depto Origem", 1, 0, "C", 1);
    $pdf->Cell(32, $iAlt, "Depto Destino", 1, 0, "C", 1);
    $pdf->Cell(50, $iAlt, "Lançamento", 1, 0, "C", 1);
    $pdf->Cell(18, $iAlt, "Data", 1, 0, "C", 1);
    $pdf->Cell(18, $iAlt, "Preço Médio", 1, 0, "C", 1);
    $pdf->Cell(20, $iAlt, "Quantidade", 1, 0, "C", 1);
    $pdf->Cell(20, $iAlt, "Valor Total", "LTB", 1, "C", 1);
    $lEscreveHeader = false;
    $pdf->setfont('arial', '', 6);
  }
  */
  $pdf->setfont('arial', '', 8);
  $pdf->AddPage();
  $pdf->Cell(17, 4, "Fornecedor:", 0, 0, "L");
  $pdf->setfont('arial', 'b', 8);
  $pdf->Cell(180, 4, $dfornecedor, 0, 1, "L");
  $pdf->setfont('arial', '', 8);

  $pdf->Cell(17, 4, "          ", 0, 0, "L");
  $pdf->Cell(180, 4, $dendereco, 0, 1, "L");

  $pdf->Cell(17, 4, "          ", 0, 0, "L");
  $pdf->Cell(100, 4, "Telefone: " . $dtelefone, 0, 0, "L");
  $pdf->Cell(70, 4, "CNPJ/CPF: " . $dcnpj, 0, 1, "L");
  $pdf->ln();

  $pdf->Cell(190, 4, " ", "T", 1, "L");

  $pdf->Cell(180, 4, "O material constante da nota fiscal anexo a este formulário e abaixo relacionada foi entregue e está de acordo com o pedido, podendo ser ", 0, 1, "L");
  $pdf->Cell(180, 4, "providenciado o pagamento no valor de: ", 0, 1, "L");
  $pdf->setfont('arial', 'b', 8);
  $pdf->Cell(180, 4, "R$ " . $valortotal, 0, 1, "L");
  $pdf->ln();

  $pdf->setfont('arial', '', 8);
  $pdf->Cell(6, 4, "N.F: ", 0, 0, "L");
  $pdf->setfont('arial', 'b', 8);
  $pdf->Cell(15, 4, $e69_numero, 0, 0, "L");

  
  $pdf->setfont('arial', '', 8);
  $pdf->Cell(13, 4, "Data N.F.: ", 0, 0, "L");
  $pdf->setfont('arial', 'b', 8);
  $pdf->Cell(20, 4, implode("/", array_reverse(explode("-", $e69_dtnota))), 0, 0, "L");

  $pdf->setfont('arial', '', 8);
  $pdf->Cell(18, 4, "Data Entrega: ", 0, 0, "L");
  $pdf->setfont('arial', 'b', 8);
  $pdf->Cell(20, 4, implode("/", array_reverse(explode("-", $e69_dtrecebe))), 0, 0, "L");

  $pdf->setfont('arial', '', 8);
  $pdf->Cell(8, 4, "Emp.: ", 0, 0, "L");
  $pdf->setfont('arial', 'b', 8);
  $pdf->Cell(20, 4, $numempenho, 0, 0, "L");

  $pdf->setfont('arial', '', 8);
  $pdf->Cell(15, 4, "Data Emp.: ", 0, 0, "L");
  $pdf->setfont('arial', 'b', 8);
  $pdf->Cell(20, 4, $dataempenho, 0, 0, "L");


  $pdf->setfont('arial', '', 8);
  $pdf->Cell(30, 4, "PCs: ", 0, 1, "L");

  $pdf->Cell(11, 4, "Órgão: ", 0, 0, "L");
  $pdf->setfont('arial', 'b', 8);
  $pdf->Cell(150, 4, $descorgao, 0, 1, "L");

  $pdf->setfont('arial', '', 8);
  $pdf->Cell(11, 4, "OBS.: ", 0, 0, "L");  
  //$pdf->Cell(150, 4, $obs, 0, 1, "L");
  //$pdf->Write(4, utf8_decode($obs));
  $pdf->Write(4, $obs);

  $pdf->ln();

  $pdf->setfont('arial', 'b', 8);
  $pdf->Cell(190, 1, " ", "T", 1, "C");

  $pdf->Cell(15, 4, "Código", 0, 0, "L");
  $pdf->Cell(10, 4, "Qtde", 0, 0, "L");
  $pdf->Cell(10, 4, "Und", 0, 0, "L");
  $pdf->Cell(110, 4, "Descrição", 0, 0, "L");
  $pdf->Cell(25, 4, "Vlr. Unitário", 0, 0, "L");  
  $pdf->Cell(15, 4, "Vlr. Total do Item", 0, 1, "L");

  $pdf->Cell(190, 1, " ", "T", 1, "C");



$pdf->setfont('arial', '', 8);
$cp = 0;
foreach ($dados as $linha) {
  if($cp == 42 || $cp == 48 || $cp == 96 || $cp == 144 ||$cp == 192){
    $pdf->AddPage();
  }
  $vlunitario = number_format($linha["m52_vlruni"], 2, ",", ".");
  $vltotal = number_format($linha["m71_valor"], 2, ",", ".");
  $pdf->Cell(15, 4, $linha["m60_codmater"], 0, 0, "L");
  $pdf->Cell(10, 4, $linha["m71_quant"], 0, 0, "L");
  $pdf->Cell(10, 4, $linha["m61_abrev"], 0, 0, "L");
  
  

  if(strlen($linha["m60_descr"]) > 60){
    $pdf->setfont('arial', '', 6.5);
    $pdf->Cell(110, 4, $linha["m60_descr"], 0, 0, "L");
    $pdf->setfont('arial', '', 8);
  }else{
    $pdf->setfont('arial', '', 8);
    $pdf->Cell(110, 4, $linha["m60_descr"], 0, 0, "L");
  }
  
  
  $pdf->Cell(25, 4, $vlunitario, 0, 0, "L");  
  $pdf->Cell(15, 4, $vltotal, 0, 1, "L");  
  $cp++;
}//Fim do foreach
$pdf->setfont('arial', '', 7);
$pdf->Cell(190, 4, "DEMONSTRATIVO", 0, 1, "C", true);
$pdf->Cell(30, 4, "Tipo Nº.", 0, 0, "L", true);
$pdf->Cell(30, 4, "Data Doc", 0, 0, "L", true);
$pdf->Cell(30, 4, "NRM.", 0, 0, "L", true);
$pdf->Cell(30, 4, "Débito", 0, 0, "L", true);
$pdf->Cell(30, 4, "Crédito", 0, 0, "L", true);
$pdf->Cell(30, 4, "Saldo", 0, 0, "L", true);
$pdf->Cell(10, 4, "", 0, 1, "L", true);

$pdf->Cell(190, 4, "Saldo....:   " . $valortotal . "          " . $valortotal . "         0,00", "B", 1, "R", true);


$pdf->setfont('arial', '', 7);
$pdf->rect(4,240,120,15,'D');
$pdf->text(7, 243,'Observação:');
$pdf->rect(124,240,83,15,'D');
$pdf->text(152, 253,'Carimbo e Assinatura');

//Segunda Linha
$pdf->rect(4,255,51,12,'D');
$pdf->text(7, 258,'Recepção de Material');
$pdf->text(7, 265,$nomeusuario);

$pdf->rect(55,255,23,12,'D');
$pdf->text(58, 258,'Fornecimento');
$pdf->text(58, 265,'Total');

$pdf->rect(78,255,23,12,'D');
$pdf->text(81, 258,'Proc. Compra');
//$pdf->text(81, 265,'xxxxx/2022');
//$pdf->text(81, 265,db_formatar(pg_result($this->recorddositens,0,$this->Snumeroproc),'s','0',6,'e'));
$pdf->text(81, 265, $codigopa);


$pdf->rect(101,255,23,12,'D');
$pdf->text(104, 258,'Proc. Pagto');
$pdf->text(104, 265,'');

$pdf->rect(124,255,33,12,'D');
$pdf->text(127, 258,'Elemento Despesa');
$pdf->text(127, 261,'(Código Reduzido)');
$pdf->text(127, 265,'.....0');

$pdf->rect(157,255,50,12,'D');
$pdf->text(160, 258,'Somente será admitido a 1ª Via da NF.');
$pdf->text(160, 261,'Anexa a 1ª via NE se fornecimento for');
$pdf->text(160, 264,'total.     PORTARIA nº. 013176-SP');

//$pdf->setfont('arial', 'b', 6);

//die("Confere");
$pdf->Output();

/*
<script>
  if ( window.history.replaceState ) {
  window.history.replaceState( null, null, window.location.href );
}
</script>
*/
?>
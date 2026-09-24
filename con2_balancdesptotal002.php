<?
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


include("libs/db_liborcamento.php");

$tipo_mesini = 1;
$tipo_mesfim = 1;

// $tipo_impressao = 1;
// 1 = orcamento
// 2 = balanco
// $tipo_agrupa = 1;
// 1 = geral
// 2 = orgao
// 3 = unidade
// $tipo_nivel = 6;
// 1 = funcao
// 2 = subfuncao
// 3 = programa
// 4 = projeto/atividade
// 5 = elemento
// 6 = recurso

$tipo_agrupa = 3;
$tipo_nivel = 6;

$qorgao = 0;
$qunidade = 0;


include("fpdf151/pdf.php");
include("libs/db_sql.php");

include("fpdf151/assinatura.php");
$classinatura = new cl_assinatura;

//db_postmemory($HTTP_SERVER_VARS,2);exit;
parse_str($HTTP_SERVER_VARS["QUERY_STRING"]);
db_postmemory($HTTP_POST_VARS);

if ($orgaos == "") {
  db_redireciona('db_erros.php?fechar=true&db_erro=Selecione orgao/unidade!');   
}

$xtipo = 0;
if($origem == "O"){
  $xtipo = "ORÇAMENTO";
}else{
  $xtipo = "BALANÇO";
  if($opcao == 3)
  $head6 = "PERÍODO : ".db_formatar($perini,'d')." A ".db_formatar($perfin,'d') ;
  else
  $head6 = "PERÍODO : ".strtoupper(db_mes(substr($perini,5,2)))." A ".strtoupper(db_mes(substr($perfin,5,2)));
}
$head1 = "DEMONSTRATIVO DA DESPESA";
$head3 = "EXERCÍCIO: ".db_getsession("DB_anousu");

$xinstit = split("-",$db_selinstit);
$resultinst = pg_exec("select codigo,nomeinst,nomeinstabrev from db_config where codigo in (".str_replace('-',', ',$db_selinstit).") ");
$descr_inst = '';
$xvirg = '';
$flag_abrev = false;
for($xins = 0; $xins < pg_numrows($resultinst); $xins++){
  db_fieldsmemory($resultinst,$xins);
  if (strlen(trim($nomeinstabrev)) > 0){
       $descr_inst .= $xvirg.$nomeinstabrev; 
       $flag_abrev  = true;
  } else {
       $descr_inst .= $xvirg.$nomeinst; 
  }

  $xvirg = ', ';
}

if ($flag_abrev == false){
     if (strlen($descr_inst) > 42){
          $descr_inst = substr($descr_inst,0,100);
     }
}

$head5 = "INSTITUIÇÕES : ".$descr_inst;



$nivela = substr($vernivel,0,1);
$sele_work = ' w.o58_instit in ('.str_replace('-',', ',$db_selinstit).') ';
if($nivela >= 1){
  $sele_work .= " and exists (select 1 from t where t.o58_orgao = w.o58_orgao) ";
}
if($nivela >= 2){
  $sele_work .= "  and exists (select 1 from t where t.o58_unidade = w.o58_unidade) ";
}
if($recurso!=0){
  $resrec = pg_exec("select o15_descr from orctiporec where o15_codigo = $recurso");
  $head2 = "Recurso: ".$recurso."-".substr(pg_result($resrec,0,0),0,30);
  $sele_work .= " and o58_codigo = $recurso";
}   
pg_exec("begin");
pg_exec("create temp table t(o58_orgao int8,o58_unidade int8,o58_funcao int8,o58_subfuncao int8,o58_programa int8,o58_projativ int8,o58_elemento int8,o58_codigo int8)");

$xcampos = split("-",$orgaos);
//print_r($xcampos);
for($i=0;$i < sizeof($xcampos);$i++){
  $where = '';
  $virgula = ''; 
  $xxcampos = split("_",$xcampos[$i]);
  for($ii=0;$ii<sizeof($xxcampos);$ii++){
    if($ii > 0){
      $where .= $virgula.$xxcampos[$ii];
      $virgula = ', ';
    }
  }
  if($nivela == 1)
  $where .= ",0,0,0,0,0,0,0";
  if($nivela == 2)
  $where .= ",0,0,0,0,0,0";
  pg_exec("insert into t values($where)");
}
$anousu = db_getsession("DB_anousu");
$dataini = $perini;
$datafin = $perfin;

//die($sele_work);
//db_criatabela(pg_exec("select * from t"));exit;

$sqlprinc = db_dotacaosaldo(2,2,4,true,$sele_work,$anousu,$dataini,$datafin, 0, 0, true);

//echo $sqlprinc;
//exit;
// funcao para gerar work
// db_criatabela(pg_exec("select * from work w inner join temporario t on $sele_work "));exit;

pg_exec("commit");

$result = pg_exec($sqlprinc) or die($sqlprinc);
if (pg_num_rows($result) == 0 ){
  db_redireciona('db_erros.php?fechar=true&db_erro=Nenhum registro encontrado, verifique as datas e tente novamente');   
} 
//db_criatabela($result);
$pdf = new PDF();
$pdf->Open();
$pdf->AliasNbPages();
$total = 0;
$pdf->setfillcolor(235);
$pdf->setfont('arial','b',7);


$sdot_ini = 0;
$ssaldo_anterior = 0;
$ssuplementado_acumulado = 0;
$sreduzido_acumulado = 0;
$sempenhado_acumulado = 0;
$sanulado_acumulado = 0;
$sliquidado_acumulado = 0;
$spago_acumulado = 0;
$sreservado = 0;

for($i=0;$i<pg_num_rows($result);$i++){

  db_fieldsmemory($result,$i);

  //<td> Saldo Inicial:</td>
  $sdot_ini += $dot_ini;
  //<td> Saldo Anterior:</td>
  $ssaldo_anterior += $saldo_anterior;
  //<td> Suplementação:</td>
  $ssuplementado_acumulado += $suplementado_acumulado;
  //<td> Redução      :</td>
  $sreduzido_acumulado += $reduzido_acumulado;
  //<td> Empenhado    :</td>
  $sempenhado_acumulado += $empenhado_acumulado;
  //<td> Anulado      :</td>
  $sanulado_acumulado += $anulado_acumulado;
  //<td> Liquidado    :</td>
  $sliquidado_acumulado += $liquidado_acumulado;
  //<td> Pago         :</td>
  $spago_acumulado += $pago_acumulado;
  //<td> A Pagar Liquidado:</td>
  //<td align="right"> <?php echo db_formatar($liquidado_acumulado-$pago_acumulado,'f')
  //<td> A Pagar Emp.:</td>
  //<td align="right"> <?php echo db_formatar(($empenhado_acumulado-$anulado_acumulado-$liquidado_acumulado),'f')
  //<td> Saldo Dotação:</td>
  //<td align="right"> <?php echo db_formatar(($dot_ini+$suplementado_acumulado-$reduzido_acumulado)-$empenhado_acumulado+$anulado_acumulado,'f')
  //<td> Reservado :</td>
  $sreservado += $reservado;
  //<td> Saldo Disponível:</td>
  //<td align="right"> <?php echo db_formatar(($dot_ini+$suplementado_acumulado-$reduzido_acumulado)-$empenhado_acumulado+$anulado_acumulado-$reservado,'f')

}


      $pdf->addpage();
      $pdf->setfont('arial','b',12);
      
      $pdf->ln(10);

      $pdf->cell(50,$alt,'',0,0,"L",0);
      $pdf->cell(40,$alt,"Saldo Inicial:",0,0,"L",0);
      $pdf->cell(50,$alt,db_formatar($sdot_ini,'f'),0,1,"R",0);
      
      $pdf->ln(5);
      $pdf->cell(50,$alt,'',0,0,"L",0);
      $pdf->cell(40,$alt,"Suplementação:",0,0,"L",0);
      $pdf->cell(50,$alt,db_formatar($ssuplementado_acumulado,'f'),0,1,"R",0);

      $pdf->ln(5);
      $pdf->cell(50,$alt,'',0,0,"L",0);
      $pdf->cell(40,$alt,"Redução:",0,0,"L",0);
      $pdf->cell(50,$alt,db_formatar($sreduzido_acumulado,'f'),0,1,"R",0);

      $pdf->ln(5);
      $pdf->cell(50,$alt,'',0,0,"L",0);
      $pdf->cell(40,$alt,"Empenhado:",0,0,"L",0);
      $pdf->cell(50,$alt,db_formatar($sempenhado_acumulado,'f'),0,1,"R",0);

      $pdf->ln(5);
      $pdf->cell(50,$alt,'',0,0,"L",0);
      $pdf->cell(40,$alt,"Anulado:",0,0,"L",0);
      $pdf->cell(50,$alt,db_formatar($sanulado_acumulado,'f'),0,1,"R",0);

      $pdf->ln(5);
      $pdf->cell(50,$alt,'',0,0,"L",0);
      $pdf->cell(40,$alt,"Liquidado:",0,0,"L",0);
      $pdf->cell(50,$alt,db_formatar($sliquidado_acumulado,'f'),0,1,"R",0);

      $pdf->ln(5);
      $pdf->cell(50,$alt,'',0,0,"L",0);
      $pdf->cell(40,$alt,"Pago:",0,0,"L",0);
      $pdf->cell(50,$alt,db_formatar($spago_acumulado,'f'),0,1,"R",0);

      $pdf->ln(5);
      $pdf->cell(50,$alt,'',0,0,"L",0);
      $pdf->cell(40,$alt,"A Pagar Liquidado:",0,0,"L",0);
      $pdf->cell(50,$alt,db_formatar($sliquidado_acumulado-$spago_acumulado,'f'),0,1,"R",0);

      $pdf->ln(5);
      $pdf->cell(50,$alt,'',0,0,"L",0);
      $pdf->cell(40,$alt,"A Pagar Empenhado:",0,0,"L",0);
      $pdf->cell(50,$alt,db_formatar(($sempenhado_acumulado-$sanulado_acumulado-$sliquidado_acumulado),'f'),0,1,"R",0);

      $pdf->ln(5);
      $pdf->cell(50,$alt,'',0,0,"L",0);
      $pdf->cell(40,$alt,"Saldo Dotação:",0,0,"L",0);
      $pdf->cell(50,$alt,db_formatar(($sdot_ini+$ssuplementado_acumulado-$sreduzido_acumulado)-$sempenhado_acumulado+$sanulado_acumulado,'f'),0,1,"R",0);

      $pdf->ln(5);
      $pdf->cell(50,$alt,'',0,0,"L",0);
      $pdf->cell(40,$alt,"Reserva:",0,0,"L",0);
      $pdf->cell(50,$alt,db_formatar($sreservado,'f'),0,1,"R",0);

      $pdf->ln(5);
      $pdf->cell(50,$alt,'',0,0,"L",0);
      $pdf->cell(40,$alt,"Saldo Disponível:",0,0,"L",0);
      $pdf->cell(50,$alt,db_formatar(($sdot_ini+$ssuplementado_acumulado-$sreduzido_acumulado)-$sempenhado_acumulado+$sanulado_acumulado-$sreservado,'f'),0,1,"R",0);

$pdf->Output();
?>

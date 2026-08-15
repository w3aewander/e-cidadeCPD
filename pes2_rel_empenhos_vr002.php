<?
/*
 *     E-cidade Software Publico para Gestao Municipal                
 *  Copyright (C) 2009  DBSeller Servicos de Informatica             
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

include(modification("fpdf151/pdf.php"));
include(modification("libs/db_sql.php"));

$clrotulo = new rotulocampo;
$clrotulo->label('r01_regist');
$clrotulo->label('z01_nome');
$clrotulo->label('r01_funcao');
$clrotulo->label('r37_descr');

parse_str($HTTP_SERVER_VARS['QUERY_STRING']);
// db_postmemory($HTTP_SERVER_VARS,2);exit;

if($tipo == 1){
  $head7   = 'Tipo : Educacao ';
  $xfiltro = " and trim(r01_clas1) in ('2','8') ";
}elseif($clas == 2){
  $head7 = 'Tipo : Saude ';
  $xfiltro = " and trim(r01_clas1) in ('1','9') ";
}elseif($clas == 3){
  $head7 = 'Tipo : Demais Secretarias';
  $xclas = " and trim(r01_clas1) in ('3','4','6','12') ";
}

$head3 = "RELATÓRIO DEEMPENHOS";
$head5 = "PERÍODO : ".$mes." / ".ano;


$sql = " select distinct elemento, descr_elemento from (  
select case when rh55_estrut is null then '00000'     else rh55_estrut end as estrut,
       case when rh55_estrut is null then 'SEM LOCAL' else rh55_descr  end as local,
       case when o56_elemento is not null then o56_elemento
            when rh75_retencaotiporec is not null then rh75_retencaotiporec::char(5)
            else '0000'
       end as elemento,
       case when o56_elemento         is not null then o56_descr
            when rh75_retencaotiporec is not null then e21_descricao
            else 'SEM CLASSIFICACAO'
       end as descr_elemento,
       round(sum(case when r14_pd = 1 then r14_valor else r14_valor * (-1) end),2) as valor
from gerfsal
     inner join rhpessoalmov    on rh02_anousu    = r14_anousu
                               and rh02_mesusu    = r14_mesusu
                               and rh02_regist    = r14_regist
     inner join rhrubricas      on r14_rubric     = rh27_rubric
                               and rh27_instit    = r14_instit
     left  join rhpeslocaltrab  on rh02_seqpes    = rh56_seqpes
     left  join rhlocaltrab     on rh56_localtrab = rh55_codigo
     left  join rhrubelemento   on rh23_instit    = rh27_instit
                               and rh23_rubric    = rh27_rubric
     left  join orcelemento     on o56_codele     = rh23_codele
                               and o56_anousu     = r14_anousu
     left  join rhrubretencao   on rh75_rubric    = rh27_rubric
                               and rh27_instit    = rh75_instit
     left  join retencaotiporec on e21_sequencial = rh75_retencaotiporec
                               and rh75_instit    = e21_instit
where r14_anousu = $ano
  and r14_mesusu = $mes
  and r14_pd <> 3
  and r14_instit = ".db_getsession("DB_instit")."
group by rh55_estrut, rh55_descr, o56_elemento, o56_descr, rh75_retencaotiporec, e21_descricao
order by rh55_estrut, rh55_descr, o56_elemento
) as xxx order by elemento
       ";
//echo $sql ; exit;

$result = db_query($sql);

db_criatabela($result);exit;
$xxnum = pg_numrows($result);
if ($xxnum == 0){
   db_redireciona('db_erros.php?fechar=true&db_erro=Não existem funcionários no período de '.$mes.' / '.$ano);

}

$pdf = new PDF(); 
$pdf->Open(); 
$pdf->AliasNbPages(); 
$func   = 0;
$func_c = 0;
$tot_c  = 0;
$total  = 0;
$pdf->setfillcolor(235);
$pdf->setfont('arial','b',8);
$troca = 1;
$alt = 4;

for($x = 0; $x < pg_numrows($result);$x++){
   db_fieldsmemory($result,$x);
   if ($pdf->gety() > $pdf->h - 30 || $troca != 0 ){
      $pdf->addpage();
      $pdf->setfont('arial','b',8);
      $pdf->cell(15,$alt,'MATRÍC.',1,0,"C",1);
      $pdf->cell(60,$alt,'NOME',1,1,"C",1);
      $funcao = '';
      $troca = 0;
   }
   if ( $funcao != $r37_descr ){
      if($funcao != ''){
        $pdf->ln(1);
        $pdf->cell(75,$alt,'Total de cargos  :  '.$func_c,0,0,"L",0);
	$func_c = 0;
	$tot_c  = 0;
      }
      $pdf->setfont('arial','b',9);
      $pdf->ln(10);
      $pdf->cell(100,$alt,$r37_descr.'    Vagas : '.$r37_vagas,0,1,"L",1);
      $funcao = $r37_descr;
   }
   if($funcion == 't'){
     $pdf->setfont('arial','',7);
     $pdf->cell(15,$alt,$r01_regist,0,0,"C",0);
     $pdf->cell(60,$alt,$z01_nome,0,1,"L",0);
   }
   $func   += 1;
   $func_c += 1;
}
$pdf->ln(1);
$pdf->cell(115,$alt,'Total de cargos  :  '.$func_c,0,0,"L",0);

$pdf->ln(5);
$pdf->cell(115,$alt,'Total da Geral  :  '.$func,0,0,"L",0);

$pdf->Output();
   
?>

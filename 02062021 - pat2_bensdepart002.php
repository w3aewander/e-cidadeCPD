<?
/*
 *     E-cidade Software Publico para Gestao Municipal                
 *  Copyright (C) 2013  DBselller Servicos de Informatica             
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



require_once("fpdf151/pdf.php");
require_once("libs/db_sql.php");
require_once("libs/db_utils.php");
require_once("classes/db_bens_classe.php");
require_once("classes/db_bensmater_classe.php");
require_once("classes/db_bensimoveis_classe.php");
require_once("classes/db_bensbaix_classe.php");
require_once("classes/db_db_depart_classe.php");
require_once("classes/db_histbem_classe.php");
require_once("classes/db_cfpatriplaca_classe.php");

function testa($var){
  echo "<pre>";
  print_r($var);
  echo "</pre>";
}
$dadosxls = array();
$novalinha = array();

$clbens         = new cl_bens;
$clbensmater    = new cl_bensmater;
$clbensimoveis  = new cl_bensimoveis;
$clbensbaix     = new cl_bensbaix;
$cldb_depart    = new cl_db_depart;
$clhistbem      = new cl_histbem;
$clcfpatriplaca = new cl_cfpatriplaca;
$clrotulo       = new rotulocampo;

$clbens->rotulo->label();
$clbensmater->rotulo->label();
$clbensimoveis->rotulo->label();
$clbensbaix->rotulo->label();
$cldb_depart->rotulo->label();

$clrotulo->label("t64_class");
//classificação

parse_str($HTTP_SERVER_VARS['QUERY_STRING']);
//db_postmemory($HTTP_SERVER_VARS,2);exit;

//var_dump($HTTP_SERVER_VARS['QUERY_STRING']);
//die("Verifica novo");
//bens=&contas=&situabens=&t52_baixainicio=&t52_baixafim=&opcao_obs=N&opcao_baixados=t&ordem=1&quebra=1&descricao=&dtaquini=&dtaquifim=&t52_depart=&t30_depto=&usardivisao=0&tipobem=todos&opcoescedentes=1&listacedentes=&t64_class=&imp_valor=N


$where = "t52_instit = ".db_getsession("DB_instit");


if($tipobem != "0"){
  if($tipobem == "moveis"){
    $where .= " AND t64_bemtipos = 1 ";
  }
  if($tipobem == "imoveis"){
    $where .= " AND t64_bemtipos = 2 ";
  }
}
//var_dump($where); die("Testa where");


if (isset($t52_depart) && trim($t52_depart)!="") {
  $where_div = "and db_depart.instit = ".db_getsession("DB_instit");
  if (isset($t30_depto) && $t30_depto  != "") {
    $where .= " and t33_divisao in ($t30_depto)";
    if ($usardivisao == 1) {
      $where_div .= " and t30_codigo in ($t30_depto)";   
    }
  }
  $result_depart = $cldb_depart->sql_record($cldb_depart->sql_query_div(null,"*",null,"coddepto in ($t52_depart) $where_div"));
  //die($cldb_depart->sql_query_div(null,"*",null,"coddepto in ($t52_depart) $where_div"));
  $flag_todos    = 0;
} else {
  $result_depart = $cldb_depart->sql_record($cldb_depart->sql_query_div(null,"*",null,"o40_instit = ".db_getsession("DB_instit")));
  $flag_todos    = 1;
}



if ($cldb_depart->numrows == 0) {
  db_redireciona("db_erros.php?fechar=true&db_erro=Departamento $t52_depart não encontrado.");
} else {
  if ($flag_todos == 0) {
    db_fieldsmemory($result_depart, 0);
  }
}

$res_cfpatriplaca = $clcfpatriplaca->sql_record($clcfpatriplaca->sql_query_file(db_getsession("DB_instit")));
if ($clcfpatriplaca->numrows > 0){
     db_fieldsmemory($res_cfpatriplaca,0);
}

$head3 = "DEPARTAMENTO";

//if ($flag_todos == 0) {
//	
//	$sQueryOrgao  = "select db01_orgao,
//	                        db01_unidade,
//	                        o40_descr,
//	                        o41_descr
//								     from db_departorg
//								    inner join db_depart    on db01_coddepto = coddepto    
//								    inner join orcorgao     on db01_orgao    = o40_orgao
//								                           and db01_anousu   = o40_anousu 
//								    inner join orcunidade   on db01_unidade  = o41_unidade
//								                           and db01_orgao    = o41_orgao
//								                           and db01_anousu   = o41_anousu  
//									  where db01_coddepto in ($t52_depart)
//									    and db01_anousu   = ".db_getsession("DB_anousu")."
//									    and (limite  is null or limite >= '".date("Y-m-d", db_getsession("DB_datausu"))."')";
//
////die($sQueryOrgao);
//	$resQueryOrgao = pg_query($sQueryOrgao);
//   if(pg_num_rows($resQueryOrgao)>0){
//	   db_fieldsmemory($resQueryOrgao,0);
//	   
//	   $head3 = "ORGÃO:	$db01_orgao - $o40_descr";
//	   $head4 = "UNIDADE: $db01_unidade - $o41_descr";
//	   $head5 = "DEPARTAMENTO: $descrdepto";
//   }else{
//	   $head3 = "ORGÃO: ";
//	   $head4 = "UNIDADE: ";
//	   $head5 = "DEPARTAMENTO: ";
//   }
//
//	
//}else {
//	//$head2 = "TODOS OS ORGÃOS";   
//}


$ordena= "t52_codcla,t52_bem";
if ($imp_valor == "S"){
     $flag_valor = true;
     $cols       = 0;
}

if ($imp_valor == "N"){
     //$flag_valor = false;
     $flag_valor = true;
     $cols       = 15;
}

$where_class = " ";
if (isset($t64_class) && trim($t64_class)!="") {
  if ($flag_todos == 0) {
    $where_class = " and t64_class='$t64_class' ";
  }
  
  if ($flag_todos == 1) {
    $where_class = " and t64_class='$t64_class' ";
  }
}

//$sWhere = "";

if(isset($bens) && trim($bens) != ""){
  if($where != ""){
    $where .= " and t52_bem in $bens ";
  }else{
    $where .= " t52_bem in $bens ";
  }
}

//Filtro por situação do bem
//procura os bens na filtrando na histbem que é a tabela de ligação entre bens e situabens
//t56 é sigla da histbem
//t56_situac é o campo que contem a sequencial da situabens
if(isset($situabens) && trim($situabens) != ""){
	

    $where .= " and  ( select t56_situac 
                  from histbem 
                 where t56_codbem = t52_bem  
                 order by t56_data desc,t56_histbem desc 
                 limit 1 ) in $situabens ";
                   
} else {
  $where .= "";
}


if (isset($contas) && trim($contas) != ""){

  if ($where != "") {
    $where .= " and c60_codcon in {$contas} ";
  } else {
    $where .= " c60_codcon in {$contas}";
  }
  $oDaoConplano = db_utils::getDao("conplano");
  $sSqlConplano = $oDaoConplano->sql_query_file(null, null, "*", null,
                                                "c60_codcon in {$contas} and c60_anousu = ".db_getsession("DB_anousu")
                                               );
  $rsConplano  = $oDaoConplano->sql_record($sSqlConplano);

  if ($oDaoConplano->numrows == 1) {

    $oConta  = db_utils::fieldsMemory($rsConplano, 0); 
    $head6   = "Conta: {$oConta->c60_estrut} - {$oConta->c60_descr}";
  }
}




if (isset($dtaquini) && isset($dtaquifim)) {
  
   
  if ($dtaquini != ""  && $dtaquifim == "") {
    
    $sdtIni = implode("-", array_reverse(explode("/", $dtaquini)));
    if ($where != "") {
      $where .= " and ";
    }
    
    $where .= " t52_dtaqu >= '{$sdtIni}'";
  } else if ($dtaquini != ""  && $dtaquifim != "") {
    
    $sdtIni = implode("-", array_reverse(explode("/", $dtaquini)));
    $sdtFim = implode("-", array_reverse(explode("/", $dtaquifim)));
    if ($where != "") {
      $where .= " and ";
    }
    $where .= " t52_dtaqu between '{$sdtIni}' and '{$sdtFim}'";
  } else if ($dtaquini = ""  && $dtaquifim != "") {
    
    $sdtFim = implode("-", array_reverse(explode("/", $dtaquifim)));
    if ($where != "") {
      $where .= " and ";
    }
    
    $where .= " t52_dtaqu <= '{$sdtFim}'";
  }

}
if(isset($descricao) && trim($descricao) != ""){
if($where != ""){
		$where .= " and t52_descr ilike '%$descricao%' ";
	}else{
		$where .= " t52_descr ilike '%$descricao%' ";
	}
}
if ($where != "") {
  
  $where .= " and ";  
}
$where  .= " (limite  is null or limite >= '".date("Y-m-d", db_getsession("DB_datausu"))."')";



//die($where);

if ($flag_todos == 0) {
	$result_bens = $clbens->sql_record($clbens->sql_querybensdepto(null,"*","$ordena"," t52_depart in ($t52_depart) and $where $where_class"));
} else {
  $result_bens = $clbens->sql_record($clbens->sql_querybensdepto(null,"*","$ordena","{$where} {$where_class}"));
}

$ordenar = ",t52_bem";
if (isset($ordem) && trim($ordem) != "") {
	switch ($ordem){
		case 1: //$ordenar = " , t52_codcla, t52_ident";
			      //$ordenar = " , t64_class, t52_ident";
    $ordenar = " , t52_ident::int, t64_class";
			break;
		case 2: $ordenar = " , t52_bem";
			break;
		case 3: $ordenar = " , t52_descr";
			break;
	}
}

switch ($opcoescedentes) {
  
  case 2 :
    
    if ($listacedentes != "") {
      $where .= " and t04_sequencial in({$listacedentes})";
    } else {
      $where .= " and t04_sequencial is not null";
    }
    break;
    
  case 3:
   
    $where .= " and t04_sequencial is null"; 
    break;
}

if ($flag_todos == 0) {
	
  $sql  = "select * ";
  $sql .= "       from bens ";
  $sql .= "            inner join cgm             on cgm.z01_numcgm = bens.t52_numcgm ";
  $sql .= "            inner join db_depart       on db_depart.coddepto = bens.t52_depart ";
  $sql .= "            left  join bensdiv         on t52_bem = t33_bem ";
  $sql .= "            left  join departdiv       on t33_divisao = t30_codigo ";
  $sql .= "            inner join clabens         on clabens.t64_codcla = bens.t52_codcla ";
  $sql .= "            inner join clabensconplano on clabensconplano.t86_clabens = clabens.t64_codcla ";
  $sql .= "                                      and clabensconplano.t86_anousu  = ".db_getsession("DB_anousu");
  $sql .= "            inner join conplano        on conplano.c60_codcon = clabensconplano.t86_conplano ";
  $sql .= " 	                                   and conplano.c60_anousu = ".db_getsession("DB_anousu");
  $sql .= "  left join benscedente    on t09_bem                = t52_bem";
  $sql .= "  left join benscadcedente on t09_benscadcedente     = t04_sequencial";
  $sql .= " where t52_depart in ($t52_depart) and $where $where_class ";
  $sql .= "order by t52_depart,t30_codigo {$ordenar} ";
}
if ($flag_todos == 1) {
	
	if (strlen($where_class) > 0) {
    $where.= " $where_class";
  }
	
  $sql  = "select * ";
  $sql .= "       from bens ";
  $sql .= "            inner join cgm       on cgm.z01_numcgm = bens.t52_numcgm ";
  $sql .= "            inner join db_depart on db_depart.coddepto = bens.t52_depart ";
  $sql .= "            left  join bensdiv   on t52_bem = t33_bem ";
  $sql .= "            left  join departdiv on t33_divisao = t30_codigo ";
  $sql .= "            inner join clabens   on clabens.t64_codcla = bens.t52_codcla ";
  $sql .= "            inner join clabensconplano on clabensconplano.t86_clabens = clabens.t64_codcla ";
  $sql .= "                                      and clabensconplano.t86_anousu  = ".db_getsession("DB_anousu");
  $sql .= "            inner join conplano        on conplano.c60_codcon = clabensconplano.t86_conplano ";
  $sql .= "                                and conplano.c60_anousu = ".db_getsession("DB_anousu");
  $sql .= "            inner join db_departorg  on db_departorg.db01_coddepto = db_depart.coddepto ";
  $sql .= "                                    and db_departorg.db01_anousu = ".db_getsession("DB_anousu");
  $sql .= "  left join benscedente    on t09_bem                = t52_bem";
  $sql .= "  left join benscadcedente on t09_benscadcedente     = t04_sequencial";
  $sql .= " where $where";
  $sql .= " order by db01_orgao,db01_coddepto,t52_depart, t30_codigo {$ordenar}";
}
//var_dump($ordenar); die("Testa");
//die($sql);
//var_dump($sql); die("Consulta principal");
$result_bens = $clbens->sql_record($sql);

//t52_ident
//echo "<pre>";
//$x = pg_fetch_all($result_bens);
//print_r($x);
//echo "</pre>";
//die("Mostra");

$numrows = $clbens->numrows;
if ($clbens->numrows == 0) {
  db_redireciona("db_erros.php?fechar=true&db_erro=Não existem bens cadastrados para os filtros selecionados.");
}

//echo $numrows;

$pdf = new PDF();
$pdf->Open();
$pdf->AliasNbPages();
$pdf->SetAutoPageBreak(false);
$pdf->setfillcolor(235);
$total          = 0;
$troca          = 1;
$alt            = 4;
$totalclas      = 0;

$total_valor    = 0;
$total_valor_cl = 0;
$total_valor_dv = 0;
$total_valor_dp = 0;

$totaldiv       = 0;
$t64_class_ant  = '';
$t30_codigo_ant = '';
$depto_ant      = "";
$temdiv = false;

//echo "<pre>";
//$zyx = pg_fetch_all($result_bens);
//print_r($zyx);
//echo "</pre>";
//die("Verifica");
$novoc = 0;
for ($x = 0; $x<$numrows; $x++) {
  db_fieldsmemory($result_bens,$x);
//  if ($flag_todos == 1) {
    if ($depto_ant != $t30_codigo) {
      if ($depto_ant=="") {
      	
     	  $sQueryOrgao  = "select db01_orgao,
                          db01_unidade,
                          o40_descr,
                          o41_descr
                     from db_departorg 
                    inner join orcorgao    on db01_orgao   = o40_orgao
                                          and db01_anousu  = o40_anousu 
                    inner join orcunidade  on db01_unidade = o41_unidade
                                          and db01_orgao   = o41_orgao
                                          and db01_anousu  = o41_anousu  
                    where db01_coddepto in ($t52_depart) 
                      and db01_anousu   = ".db_getsession("DB_anousu");
		   
       $resQueryOrgao = pg_query($sQueryOrgao);
		   if(pg_num_rows($resQueryOrgao)>0){
		   	db_fieldsmemory($resQueryOrgao,0);
//		   	$head2 = "TODOS OS ORGÃOS";
		   	//$head3 = "ORGÃO:		 	$db01_orgao - $o40_descr";
		   	//$head4 = "UNIDADE: 		$db01_unidade - $o41_descr";
		   	//$head5 = "DEPARTAMENTO:	$t52_depart - $descrdepto";
        $head3 = "DEPARTAMENTO: $t52_depart - $descrdepto";
        $head5 = "DIVISÃO: $t30_codigo - $t30_descr";
        array_push($dadosxls, $head3);
        array_push($dadosxls, $head5);
        array_push($dadosxls, "Placa|Descrição|Aquisição|Valor|Processo|Baixado|Linha");
		   }else{
		   	//$head3 = "ORGÃO: "; 
		   	//$head4 = "UNIDADE: ";
		   	//$head5 = "DEPARTAMENTO: ";
        $head3 = "DEPARTAMENTO: ";
        $head5 = "DIVISÃO: ";
		   }
       //
       //t30_codigo.' - '.$t30_descr
		    /*     	
        $head4     = "CÓDIGO:    $t52_depart";
        $head5     = "DESCRIÇÃO: $descrdepto";
				*/
        $depto_ant = $t30_codigo;
      }
    }
//  }

  
  $result_bensmater = $clbensmater->sql_record($clbensmater->sql_query_file($t52_bem));
  if ($clbensmater->numrows==0) {
    $result_bensimoveis = $clbensimoveis->sql_record($clbensimoveis->sql_query_file($t52_bem,null));
    if ($clbensimoveis->numrows==0) {
      $definicao="Material";
    } else {
      $definicao="Imóvel";
    }
  } else {
    $definicao="Material";
  }

  $bPeriodoBaixa = false;
  if(($t52_baixainicio != '') and ($t52_baixafim != '')) {
    
    $dBaixaInicio    = implode('-', array_reverse(explode('/', $t52_baixainicio)));
    $dBaixaFim       = implode('-', array_reverse(explode('/', $t52_baixafim)));
    $bPeriodoBaixa   = true;
      
  } elseif($t52_baixainicio != '' and $t52_baixafim == '') {
    
    $dBaixaInicio    = implode('-', array_reverse(explode('/', $t52_baixainicio)));
    $dBaixaFim       = date('Y-m-d', db_getsession('DB_datausu'));
    $bPeriodoBaixa   = true;
    
  } elseif($t52_baixainicio == '' and $t52_baixafim != '') {
    
    $result_bensbaix = $clbensbaix->sql_record($clbensbaix->sql_query_file(null, 't55_baixa as t52_baixainicio', 't55_baixa', null). ' limit 1');
    $dBaixaInicio    = $clbensbaix->numrows > 0 ? db_fieldsmemory($result_bensbaix, 0) : date('Y-m-d', db_getsession('DB_datausu'));
    $dBaixaFim       = implode('-', array_reverse(explode('/', $t52_baixafim)));
    $bPeriodoBaixa   = true;
      
  }
  if($opcao_baixados != 't') {
    
    if($bPeriodoBaixa) {
      $sWhereBaixa     = "t55_codbem = {$t52_bem} and t55_baixa between '{$dBaixaInicio}' and '{$dBaixaFim}'";
      $result_bensbaix = $clbensbaix->sql_record($clbensbaix->sql_query_file(null, '*', null, $sWhereBaixa));
      if($opcao_baixados == 'b') {
        if($clbensbaix->numrows == 0) {
          continue;
        }
      }elseif($opcao_baixados == 'n') {
        if($clbensbaix->numrows > 0) {
          continue;
        }
      }
    
    }else {
      $result_bensbaix = $clbensbaix->sql_record($clbensbaix->sql_query_file($t52_bem));
      if($clbensbaix->numrows > 0) {
        if($opcao_baixados == 'n') {
          continue;
        }
      }else {
        if($opcao_baixados == 'b') {
          continue;
        }
      }
    }
    //$baix = $opcao_baixados == 'n' ? 'Não baixado' : 'Baixado';
    $baix = $opcao_baixados == 'n' ? 'Não' : 'Sim';
    
  } else {
    
    if($bPeriodoBaixa) {
      $sWhereBaixa   = "t55_codbem = {$t52_bem} and t55_baixa between '{$dBaixaInicio}' and '{$dBaixaFim}'";
      $sSqlBensBaixa = $clbensbaix->sql_query_file(null, '*', null, $sWhereBaixa);
    } else {
      $sSqlBensBaixa = $clbensbaix->sql_query_file($t52_bem);
    }
    $result_bensbaix = $clbensbaix->sql_record($sSqlBensBaixa);
    //$baix = $clbensbaix->numrows > 0 ? "Baixado" : "Não baixado"; 
    $baix = $clbensbaix->numrows > 0 ? "Sim" : "Não"; 
  }

  $res_situacaobem = $clhistbem->sql_record($clhistbem->sql_query(null,"t70_descr","t56_histbem desc","t52_bem = $t52_bem "));
  if ($clhistbem->numrows > 0) {
    db_fieldsmemory($res_situacaobem,0);
    $situacao_bem = $t70_descr;
  } else {

     $situacao_bem = "Material";  

  }

  
  if ($pdf->gety() > $pdf->h - 30 || $troca != 0 ) {
    $pdf->addpage();
    $pdf->setfont('arial','b',8);    
    $pdf->cell(20,$alt,$RLt52_ident,1,0,"C",1);        
    $pdf->cell(85,$alt,"Descrição",1,0,"C",1);
    $pdf->cell(15,$alt,$RLt52_dtaqu,1,0,"C",1);
    

    if ($flag_valor == true){
         $pdf->cell(15,$alt,"Valor",1,0,"C",1);
    }

    //$pdf->cell(20,$alt,"Situação",1,0,"C",1);
    $pdf->cell(20,$alt,"Processo",1,0,"C",1);
    //$pdf->cell(30,$alt,"Bem",1,1,"C",1);
    $pdf->cell(30,$alt,"Baixado",1,1,"C",1);
    //$pdf->cell(15,$alt,"Pl. Ident.",1,1,"C",1);
    
    if ($opcao_obs == "S") {
      $pdf->cell((195-$cols),$alt,"Características adicionais do bem",1,1,"L",1);
    }
    $troca = 0;
  }
  
  if ($t30_codigo != $t30_codigo_ant && trim($t30_codigo) != "") {
    if($novoc > 0){
      //var_dump($t30_codigo_ant);
      //var_dump($t30_codigo);
      //die("Segunda página");
      $head5 = "DIVISÃO: $t30_codigo - $t30_descr";      
      //array_push($dadosxls, $head5);
      $pdf->addpage();
    }
    $novoc++;
    if ($x != 0) {      
      $pdf->setfont('arial','b',8);
    }
    $temdiv = true;
    $pdf->setfont('arial','b',10);
    $pdf->ln(2);
    
//    if ($flag_todos == 1) {
      if ($depto_ant!=$t30_codigo) {
      	
      	 $sQueryOrgao  = "select db01_orgao,
                          db01_unidade,
                          o40_descr,
                          o41_descr
                     from db_departorg 
                    inner join orcorgao    on db01_orgao   = o40_orgao
                                          and db01_anousu  = o40_anousu 
                    inner join orcunidade  on db01_unidade = o41_unidade
                                          and db01_orgao   = o41_orgao
                                          and db01_anousu  = o41_anousu  
                    where db01_coddepto in ($t52_depart) 
                      and db01_anousu   = ".db_getsession("DB_anousu");
		   
		   	$resQueryOrgao = pg_query($sQueryOrgao);
		   	if(pg_num_rows($resQueryOrgao)>0){
		   		db_fieldsmemory($resQueryOrgao,0);
          $head3 = "DEPARTAMENTO: $t52_depart - $descrdepto";
          $head5 = "DIVISÃO: $t30_codigo - $t30_descr";          
          array_push($dadosxls, $head3);
          array_push($dadosxls, $head5);
          array_push($dadosxls, "Placa|Descrição|Aquisição|Valor|Processo|Baixado|Linha");
		   	}else{		   		
          $head3 = "DEPARTAMENTO: ";
          $head5 = "DIVISÃO: ";
          array_push($dadosxls, $head3);
          array_push($dadosxls, $head5);
          array_push($dadosxls, "Placa|Descrição|Aquisição|Valor|Processo|Baixado|Linha");
		   	}
      	
        $depto_ant = $t30_codigo;
       if ($quebra == 2 || ($pdf->gety() > $pdf->h - 30)) {
          $pdf->addpage();
        }
        $pdf->setfont('arial','b',8);
        //$pdf->cell(20,$alt,"Código",1,0,"C",1);
        $pdf->cell(20,$alt,$RLt52_ident,1,0,"C",1);
        //$pdf->cell(65,$alt,$RLt52_descr,1,0,"C",1);
        $pdf->cell(85,$alt,"Descrição",1,0,"C",1);
        $pdf->cell(15,$alt,$RLt52_dtaqu,1,0,"C",1);

        if ($flag_valor == true){
             $pdf->cell(20,$alt,"Valor",1,0,"C",1);
        }

        //$pdf->cell(20,$alt,"Situação",1,0,"C",1);
        //$pdf->cell(20,$alt,"Definição",1,0,"C",1);
        $pdf->cell(20,$alt,"Processo",1,0,"C",1);
        //$pdf->cell(30,$alt,"Bem",1,1,"C",1);
        $pdf->cell(10,$alt,"Baixado",1,1,"C",1);
        //$pdf->cell(15,$alt,"Pl. Ident.",1,1,"C",1);
        
        if ($opcao_obs == "S") {
          $pdf->cell((205-$cols),$alt,"Características adicionais do bem",1,1,"L",1);
        }
      }
//    }
    
    //$pdf->cell((205-$cols),$alt,'DIVISÃO :   '.$t30_codigo.' - '.$t30_descr,0,1,"L",0);
    $t30_codigo_ant = $t30_codigo;
    $totaldiv       = 0;
    $total_valor_dv = 0;
    $totalclas      = 0;
    $total_valor_cl = 0;
    $total_valor_dv = 0;
    $pdf->setfont('arial','b',7);
    //$pdf->cell((205-$cols),$alt,$t64_class.' - '.$t64_descr,"T",1,"L",0);
    $t64_class_ant  = $t64_class;
  } else {
//    if ($flag_todos == 1) {
      if ($depto_ant!=$t30_codigo) {
      	
      	 $sQueryOrgao  = "select db01_orgao,
                          db01_unidade,
                          o40_descr,
                          o41_descr
                     from db_departorg 
                    inner join orcorgao    on db01_orgao   = o40_orgao
                                          and db01_anousu  = o40_anousu 
                    inner join orcunidade  on db01_unidade = o41_unidade
                                          and db01_orgao   = o41_orgao
                                          and db01_anousu  = o41_anousu  
                    where db01_coddepto in ($t52_depart) 
                      and db01_anousu   = ".db_getsession("DB_anousu");
		   
		   	$resQueryOrgao = pg_query($sQueryOrgao);
		   	if(pg_num_rows($resQueryOrgao)>0){
		   		db_fieldsmemory($resQueryOrgao,0);
          $head3 = "DEPARTAMENTO: $t52_depart - $descrdepto";
          $head5 = "DIVISÃO: $t30_codigo - $t30_descr";
          array_push($dadosxls, $head3);
          array_push($dadosxls, $head5);
          array_push($dadosxls, "Placa|Descrição|Aquisição|Valor|Processo|Baixado|Linha");

		   	}else{		   		
          $head3 = "DEPARTAMENTO: ";
          $head5 = "DIVISÃO: ";
          array_push($dadosxls, $head3);
          array_push($dadosxls, $head5);
          array_push($dadosxls, "Placa|Descrição|Aquisição|Valor|Processo|Baixado|Linha");
		   	}
      	
        $totaldiv  = 0;
        $total_valor_dv = 0;
        $totalclas = 0;
        $total_valor_cl = 0;
        $total_valor_dv = 0;
        $t64_class_ant = $t64_class;
        //$depto_ant = $t52_depart;
        $depto_ant = $t30_codigo;
        if ($quebra == 2) {
          $pdf->addpage();
        }
        $pdf->addpage();
        $pdf->setfont('arial','b',7);
        //$pdf->cell(20,$alt,"Código",1,0,"C",1);
        $pdf->cell(20,$alt,$RLt52_ident,1,0,"C",1);
        $pdf->cell(85,$alt,$RLt52_descr,1,0,"C",1);
        $pdf->cell(15,$alt,$RLt52_dtaqu,1,0,"C",1);

        if ($flag_valor == true){
             $pdf->cell(15,$alt,"Valor",1,0,"C",1);
        }

        //$pdf->cell(20, $alt,"Situação",1,0,"C",1);
        $pdf->cell(20, $alt,"Processo",1,0,"C",1);
        $pdf->cell(30, $alt,"Bem",1,1,"C",1);
        //$pdf->cell(15,$alt,"Pl. Indent.",1,1,"C",1);
        
        if ($opcao_obs == "S") {
          $pdf->cell((195-$cols),$alt,"Características adicionais do bem",1,1,"L",1);
        }
        
        $pdf->setfont('arial','b',7);    
      }
//    }
  }
  
  

  // Mostra BENS SEM DIVISAO quando bens nao esta vinculada a divisao de departamento
  if (trim($t30_codigo)=="") {
       if (trim($t30_codigo_ant)!=""){
            
            $pdf->setfont('arial','b',10);
            $pdf->ln(2);
            //$pdf->cell((195-$cols),$alt,'BENS SEM DIVISÃO',0,1,"L",0);
            $pdf->setfont('arial','b',7);
            //$pdf->cell((195-$cols),$alt,$t64_class.' - '.$t64_descr,"T",1,"L",0);
            $t30_codigo_ant = "";
            $totaldiv       = 0;
            $total_valor_dv = 0;
       }    
  }     
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

  $pdf->setfont('arial','',6);
  //$pdf->cell(20,$alt,$t52_bem,0,0,"C",0);

  if ($t07_confplaca == 1 || $t07_confplaca == 4){
       $t52_ident = db_formatar($t52_ident,"s","0",$t07_digseqplaca,"e",0);
  }

  //$pdf->cell(20,$alt,$t52_ident,0,0,"L",0);
  //$pdf->cell(85,$alt,substr($t52_descr,0,50),0,0,"L",0);
  //$pdf->multicell((195-$cols),$alt,$t52_descr,0,"L",$p);
  //$pdf->cell(85,$alt,$t52_descr,0,0,"L",0);
  //$pdf->cell(15,$alt,db_formatar($t52_dtaqu,"d"),0,0,"C",0);

  if ($flag_valor == true){
      // Valor aqui. Colocar R$
       //$pdf->cell(15,$alt,db_formatar($t52_valaqu,"f"),0,0,"R",0);
  }

  //$pdf->cell(20,$alt,$situacao_bem,0,0,"L",0);
  //$pdf->cell(20,$alt,$definicao,0,0,"L",0);
  //$pdf->cell(20,$alt,$num_processo_adm,0,0,"L",0);
  //$pdf->cell(30,$alt,$baix,0,1,"L",0);
//  $placaIdentificacao = $totaletiquetas >= 1 ? "Sim" : "Não"; 
//  $pdf->cell(15,$alt,$placaIdentificacao,0,1,"L",0);
    //$linha = $t52_ident, $t52_descr, $t52_dtaqu, $t52_valaqu, $num_processo_adm, $baix
    $linha = $t52_ident ."|". $t52_descr ."|". $t52_dtaqu ."|". $t52_valaqu ."|". $num_processo_adm ."|". $baix;
    
    $linha2 = $t52_bem ."|". $t52_ident ."|". $t52_descr ."|". $t52_dtaqu ."|". $t52_valaqu ."|". $num_processo_adm ."|". $baix ."|". $t52_depart ."|". $descrdepto ."|". $t30_codigo ."|". $t30_descr . "|" . $t52_obs;
    array_push($dadosxls, $linha);
    array_push($novalinha, $linha2);

    $current_y = $pdf->GetY();
    $current_x = $pdf->GetX();
    $pdf->MultiCell(20, 4, $t52_ident, 0, 'L');
    $end_y = $pdf->GetY();

    $current_x = $current_x + 20;
    $pdf->SetXY($current_x, $current_y);
    $pdf->MultiCell(85, 4, $t52_descr, 0, 'L');
    $end_y = ($pdf->GetY() > $end_y)?$pdf->GetY() : $end_y;

    $current_x = $current_x + 85;
    $pdf->SetXY($current_x, $current_y); 
    $pdf->MultiCell(15, 4, db_formatar($t52_dtaqu,"d"), 0, 'L');
    $end_y = ($pdf->GetY() > $end_y)?$pdf->GetY() : $end_y;

    $current_x = $current_x + 15;
    $pdf->SetXY($current_x, $current_y); 
    $pdf->MultiCell(20, 4, db_formatar($t52_valaqu,"f"), 0, 'L');
    $end_y = ($pdf->GetY() > $end_y)?$pdf->GetY() : $end_y;

    $current_x = $current_x + 20;
    $pdf->SetXY($current_x, $current_y);
    $pdf->MultiCell(20, 4, $num_processo_adm, 0, 'L');
    $end_y = ($pdf->GetY() > $end_y)?$pdf->GetY() : $end_y;

    $current_x = $current_x + 20;
    $pdf->SetXY($current_x, $current_y);
    $pdf->MultiCell(10, 4, $baix, 0, 'L');
    $end_y = ($pdf->GetY() > $end_y)?$pdf->GetY() : $end_y;
    
    $i++;
    $pdf->SetY($end_y);
    
  
  if ($opcao_obs == "S") {
    if ($x % 2 == 0) {
      $p = 1;
    } else {
      $p = 0;
    }
    
    if (trim($t52_obs)!="") {
      $pdf->multicell((195-$cols),$alt,$t52_obs,0,"L",$p);
    }
  }

  $total++;
  $totalclas++;
  $totaldiv++;

  $total_valor    += $t52_valaqu;
  $total_valor_cl += $t52_valaqu;
  $total_valor_dv += $t52_valaqu;
  $total_valor_dp += $t52_valaqu;
}


if ($pdf->gety() > $pdf->h - 30 ) {
	$pdf->addpage();
}
/*
if ($flag_valor == true){
   $pdf->cell((110-$cols),$alt,'Total da Classificação  : '.$totalclas,"T",0,"L",0);
   $pdf->cell(25,$alt,db_formatar($total_valor_cl,'f'),"T",0,"R",0);
   $pdf->cell(70,$alt,'',"T",1,"R",0);
}else{
   $pdf->cell((205-$cols),$alt,'Total da Classificação  : '.$totalclas,"T",1,"L",0);
}
*/
if ($temdiv == true||$totaldiv > 0) {
  $pdf->setfont('arial','b',8);
  /*if ($flag_valor == true){
    $pdf->cell((110-$cols),$alt,'Total da Divisão  : '.$totaldiv,"T",0,"L",0);
    $pdf->cell(25,$alt,db_formatar($total_valor_dv,'f'),"T",0,"R",0);
    $pdf->cell(70,$alt,'',"T",1,"R",0);
    if ($pdf->gety() > $pdf->h - 36 || $troca != 0 ) {
      $pdf->addpage();
    }    
  }else{
    $pdf->cell((205-$cols),$alt,'Total da Divisão  : '.$totaldiv,"T",1,"L",0);
    if ($pdf->gety() > $pdf->h - 36 || $troca != 0 ) {
      $pdf->addpage();
    }    
  }*/
}

$pdf->setfont('arial','b',8);
  if ($flag_valor == true){
    $pdf->cell((110-$cols),$alt,'TOTAL DE REGISTROS  :  '.$total,"T",0,"L",0);
    $pdf->cell(25,$alt,db_formatar($total_valor,'f'),"T",0,"R",0);
    $pdf->cell(70,$alt,'',"T",1,"R",0);
    $linhafinal = "Total de Registros: " . $total . "|" . "Valor Total: " . $total_valor;
    array_push($dadosxls, $linhafinal);
  }else{
    $pdf->cell((205-$cols),$alt,'TOTAL DE REGISTROS  :  '.$total,"T",0,"L",0);
    $linhafinal = "Total de Registros: " . $total;
    array_push($dadosxls, $linhafinal);

  }
//$pdf->Output();



/*
header("Content-Type:   application/vnd.ms-excel; charset=utf-8");
header("Content-type:   application/x-msexcel; charset=utf-8");
header("Content-Disposition: attachment; filename=bens_por_departamento.xls");
header("Expires: 0");
header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
header("Cache-Control: private",false);

print "<table border=1>";
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
*/





/*
  foreach ($dadosxls as $linhona) {
  $linhafinal = explode("|", $linhona);
  
  if(count($linhafinal) == 1){
    //print "<tr>";
    //print "<td colspan='5'>".$linhafinal[0]."</td>";
    //print "<tr>";
  }elseif(count($linhafinal) == 6){
    $dinheiro =  number_format($linhafinal[3], 2, ',', '.');
    var_dump($dinheiro); die("Mosr");
  }elseif(count($linhafinal) == 2){
    //print "<tr>";
    //print "<td>".$linhafinal[0]."</td>";
    //print "<tr>";
    //print "<tr>";
    //print "<td>".$linhafinal[1]."</td>";
    //print "<tr>";
  }
}
die("Confere");
*/


//testa($novalinha);

header("Content-Type:   application/vnd.ms-excel; charset=utf-8");
header("Content-type:   application/x-msexcel; charset=utf-8");
header("Content-Disposition: attachment; filename=bens_por_departamento.xls");
header("Expires: 0");
header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
header("Cache-Control: private",false);

print "<table border=1>";
print "<thead>";
print "<tr>";
print "<th>Código</th>";
print "<th>Placa</th>";
print "<th colspan='5'>Descrição</th>";
print "<th>Data Aquisição</th>";
print "<th>Valor Aquisição</th>";
print "<th>Cod. Departamento</th>";
print "<th colspan='5'>Desc. Departamento</th>";
print "<th>Cod. Divisão</th>";
print "<th colspan='5'>Desc. Divisão</th>";
print "<th colspan='5'>OBS.:</th>";
print "<th>Baixado</th>";
print "<th>Processo</th>";
print '</tr>';




foreach ($novalinha as $linhazinha){  
$linhadentro = explode("|", $linhazinha);
$data = implode("/", array_reverse(explode("-", $linhadentro[3])));
$valor = number_format($linhadentro[4], 2, '.', '');

//testa($linhadentro);

  print "<tr>";
    print "<td>" . $linhadentro[0] . "</td>";
    print "<td>" . $linhadentro[1] . "</td>";
    print "<td colspan='5'>" . $linhadentro[2] . "</td>";
    print "<td>" . $data . "</td>";
    print "<td>" . $valor . "</td>";
    print "<td>" . $linhadentro[7] . "</td>";
    print "<td colspan='5'>" . $linhadentro[8] . "</td>";
    print "<td>" . $linhadentro[9] . "</td>";
    print "<td colspan='5'>" . $linhadentro[10] . "</td>";
    print "<td colspan='5'>" . $linhadentro[11] . "</td>";
    print "<td>" . $linhadentro[6]. "</td>";
    print "<td>" . $linhadentro[5]. "</td>";
  print "</tr>";
    
  }


/*
header("Content-Type:   application/vnd.ms-excel; charset=utf-8");
header("Content-type:   application/x-msexcel; charset=utf-8");
header("Content-Disposition: attachment; filename=bens_por_departamento.xls");
header("Expires: 0");
header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
header("Cache-Control: private",false);


print "<table>";
foreach ($dadosxls as $linhona) {  
  $linhafinal = explode("|", $linhona);
  if(count($linhafinal) == 1){
    print "<tr>";
    print "<td colspan='5'>".$linhafinal[0]."</td>";
    print "</tr>";
  }elseif(count($linhafinal) == 7){
    print "<tr>";
    print "<td>".$linhafinal[0]."</td>";
    print "<td colspan='7'>".$linhafinal[1]."</td>";
    print "<td>".$linhafinal[2]."</td>";
    print "<td>".$linhafinal[3]."</td>";
    print "<td>".$linhafinal[4]."</td>";
    print "<td>".$linhafinal[5]."</td>";
    print "</tr>";
  }elseif(count($linhafinal) == 6){
    $dataaq = implode("/", array_reverse(explode("-", $linhafinal[2])));
    $dinheiro =  number_format($linhafinal[3], 2, ',', '.');
    
    print "<tr>";
    print "<td>".$linhafinal[0]."</td>";
    print "<td colspan='7'>".$linhafinal[1]."</td>";
    print "<td>".$dataaq."</td>";
    print "<td>".$dinheiro."</td>";
    print "<td>".(string)$linhafinal[4]."</td>";
    print "<td>".$linhafinal[5]."</td>";
    print "</tr>";
  }elseif(count($linhafinal) == 2){
    print "<tr>";
    print "<td>".$linhafinal[0]."</td>";
    print "</tr>";
    print "<tr>";
    print "<td>".$linhafinal[1]."</td>";
    print "</tr>";
  }    
}
print "</table>";
*

//testa($dadosxls);
//die("Verifica");


?>
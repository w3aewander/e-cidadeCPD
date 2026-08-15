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
require_once("libs/db_stdlib.php");
require_once("libs/db_conecta".".php");
require_once("libs/db_sessoes.php");
require_once("libs/db_libpessoal.php");
require_once("fpdf151/pdf.php");
require_once("libs/db_utils.php");

parse_str($HTTP_SERVER_VARS['QUERY_STRING']);

$oDaoAssenta  = db_utils::getDao("assenta");
$oDaoCertidao = db_utils::getDao("certidaotemposervico");

$rsDadosCertidao =   $oDaoCertidao->sql_record ( $oDaoCertidao->sql_query_file($codigocertidao) );
db_fieldsmemory( $rsDadosCertidao, 0);
$anousu = db_anofolha();
$mesusu = db_mesfolha();
$where = 'where 1 = 1';
if($regist != ''){
  $where .= " and rh01_regist = $regist";
}


$sWhere2          = " h16_dtterm is not null ";
$sWhere2         .= " and h16_regist = {$regist} ";
$sWhere2         .= " and h16_assent in ( 306,332,340,300 )";
$sCampos     = " *, extract(year from h16_dtterm) as anofim";
$sSql        = $oDaoAssenta->sql_query(null, $sCampos, "", $sWhere2);
$rsDemissao  = db_query($sSql);
for ( $i=0; $i < pg_num_rows($rsDemissao); $i++ ) {

    db_fieldsmemory($rsDemissao,$i);
    $iAnoFim    = $anofim;
    $iMotivo    = $h12_descr;
    $iDataFinal = $h16_dtterm;
    $resci      = $h16_dtterm;
}


//$iExonera = $resci;
if(!empty($resci)){
  $iExonera = $resci;
  $resci    = date('Y-m-d',strtotime("-1 day",strtotime($resci)));
}

$sql = "
            SELECT rh02_regist,
                   z01_nome,
                   z01_nasc,
                   CASE
                       WHEN z01_sexo = 'M' THEN 'Masculino'
                       WHEN z01_sexo = 'F' THEN 'Feminino'
                       ELSE 'Não Definido'
                   END AS z01_sexo,
                   rh37_descr,
                   rh01_admiss AS admi,
                   z01_ender||','||z01_numero||' '||z01_compl ||' - '||z01_bairro||'-'|| z01_munic||' - '||z01_uf as z01_ender,
                   z01_cgccpf,
                   z01_pis,
                   z01_ident,
                   z01_identorgao,
                   nomeinst,
                   z01_pai,
                   z01_mae,
                   cgc,
                   r70_descr,
                   rh04_descr
            FROM rhpessoal
            INNER jOIN db_config    on codigo = rh01_instit
            INNER JOIN rhpessoalmov ON rh02_regist = rh01_regist
            AND rh02_anousu = $anousu
            AND rh02_mesusu = $mesusu
            AND rh02_instit = ".db_getsession('DB_instit')."
            left join rhlota on  r70_codigo = rh02_lota and r70_instit = rh02_instit
            INNER JOIN rhfuncao ON rh37_funcao = rh02_funcao
            AND rh37_instit = ".db_getsession('DB_instit')."
            left join rhpescargo on rh20_seqpes = rh02_seqpes and rh20_instit = rh02_instit
            left join rhcargo on rh20_cargo = rh04_codigo and rh20_instit = rh04_instit
            INNER JOIN cgm ON rh01_numcgm = z01_numcgm $where
             $xordem ";

$result = pg_exec($sql);
$xxnum  = pg_numrows($result);

db_fieldsmemory($result,0);
$head3 = "Certidão de Tempo de Contribuição";
$head5 = "PERIODO : ".db_formatar($admi,'d')." a ".db_formatar($resci,'d');
if ($xxnum == 0){
   db_redireciona('db_erros.php?fechar=true&db_erro=Não existem Códigos cadastrados no período de '.$mes.' / '.$ano);

}

$pdf = new PDF(); 
$pdf->Open(); 
$pdf->AliasNbPages(); 
$pdf->setfillcolor(235);
$pdf->setfont('arial','b',8);

$lAltura = 4;
$pdf->addpage('P'); 
$pdf->cell( 180,$lAltura, " CERTIDÃO DE TEMPO DE CONTRIBUIÇÃO", 0 , 1, "C", 0);

$pdf->ln();
$pdf->setfont('arial','',8);
// Linha 1
$pdf->cell(140,$lAltura,'ÓRGÃO DESTINATÁRIO :',"TLR",0,"L",0);
$pdf->cell(50, $lAltura,'PROCESSO Nº:',"TR",1,"L",0);

$pdf->cell(140,$lAltura,$orgao,"BLR",0,"L",0);
$pdf->cell(50, $lAltura,$processo,"BR",1,"L",0);

// Linha 2
$pdf->cell(140,$lAltura,'ÓRGÃO EXPEDIDOR :',"LR",0,"L",0);
$pdf->cell(50, $lAltura,'CNPJ :',"R",1,"L",0);

$pdf->cell(140,$lAltura,$nomeinst,"BLR",0,"L",0);
$pdf->cell(50, $lAltura,db_formatar($cgc,"cnpj"),"BR",1,"L",0);


// Linha 3
$pdf->cell(120,$lAltura,'NOME DO SERVIDOR :',"LR",0,"L",0);
$pdf->cell(30 ,$lAltura,'SEXO :',"R",0,"L",0);
$pdf->cell(40, $lAltura,'MATRÍCULA :',"R",1,"L",0);

$pdf->setfont('arial','B',8);
$pdf->cell(120,$lAltura,$z01_nome,"BLR",0,"L",0);
$pdf->setfont('arial','',8);
$pdf->cell(30 ,$lAltura,$z01_sexo,"BR",0,"L",0);
$pdf->cell(40, $lAltura,$rh02_regist,"BR",1,"L",0);

// Linha 4
$pdf->cell(120,$lAltura,'RG/ORGÃO EXPEDIDOR :',"LR",0,"L",0);
$pdf->cell(30 ,$lAltura,'CPF :',"R",0,"L",0);
$pdf->cell(40, $lAltura,'PIS/PASEP :',"R",1,"L",0);

$pdf->cell(120,$lAltura,$z01_ident."/".$z01_identorgao,"BLR",0,"L",0);
$pdf->cell(30 ,$lAltura,db_formatar($z01_cgccpf,"cpf"),"BR",0,"L",0);
$pdf->cell(40, $lAltura,$z01_pis,"BR",1,"L",0);

// Linha 5
$pdf->cell(120,$lAltura,'FILIAÇÃO :',"LR",0,"L",0);
$pdf->cell(70, $lAltura,'DATA DE NASCIMENTO :',"R",1,"L",0);

$pdf->cell(120,$lAltura,$z01_pai,"LR",0,"L",0);
$pdf->cell(70, $lAltura,'',"R",1,"L",0);
$pdf->cell(120,$lAltura,$z01_mae,"BLR",0,"L",0);
$pdf->cell(70, $lAltura,db_formatar($z01_nasc,"d"),"BR",1,"L",0);

// Linha 6
$pdf->cell(190, $lAltura,'ENDEREÇO:',"LR",1,"L",0);

$pdf->cell(190, $lAltura,$z01_ender,"BLR",1,"L",0);

// Linha 7
$pdf->cell(190, $lAltura,'CARGO EFETIVO:',"LR",1,"L",0);

$pdf->cell(190, $lAltura,$rh37_descr."/".$rh04_descr,"BLR",1,"L",0);

// Linha 8
$pdf->cell(190, $lAltura,'ÓRGÃO DE LOTAÇÃO:',"LR",1,"L",0);
$pdf->setfont('arial','b',8);
$pdf->cell(190, $lAltura,(trim($resci) != "" ? 'EXONERADO': $r70_descr ),"BLR",1,"L",0);
$pdf->setfont('arial','',8);

// linha 9

$pdf->cell(120,$lAltura,'DATA DE ADMISSÃO :',"LR",0,"L",0);
$pdf->cell(70, $lAltura,'DATA DE EXONERAÇÃO :',"R",1,"L",0);

$pdf->cell(120,$lAltura,db_formatar($admi,"d"),"BLR",0,"L",0);
$pdf->cell(70, $lAltura,db_formatar($iExonera,"d"),"BR",1,"L",0);

// Linha 10
$pdf->cell(190, $lAltura,'PERÍODO DE CONTRIBUIÇÃO COMPREENDIDO NESTA CERTIDÃO :',"LR",1,"L",0);

$pdf->cell(190, $lAltura,db_formatar($admi,"d")." A ".db_formatar($resci,"d") ,"BLR",1,"L",0);


// Linha 11
$pdf->cell(190, $lAltura,'FONTE DE INFORMAÇÃO :',"LR",1,"L",0);

$pdf->cell(190, $lAltura,'DEPARTAMENTO DE GESTÃO DE PESSOAS DA FME',"BLR",1,"L",0);

// Linha 12
$pdf->cell(190, $lAltura,'DESTINAÇÃO DO TEMPO DE CONTRIBUIÇÃO :',"LR",1,"L",0);

$pdf->cell(190, $lAltura,$orgao,"BLR",1,"L",0);

$pdf->ln();
$pdf->setfont('arial','',14);
$pdf->cell(190, $lAltura,'Frequência',"",1,"L",0);
$pdf->ln();

// Inicio Tempo de serviço  

$iMatriculaServidor = $regist;
$sWhere  = " h16_dtterm is not null ";
$sWhere .= " and h16_regist = {$iMatriculaServidor} ";

$sCondicao = "";
$sOrdem    = "h16_dtconc";
$sCampos   = "distinct h16_dtconc, h16_dtlanc, h16_assent ,h12_codigo,h12_descr, h16_regist, h16_dtterm,h16_histor";


/**
 * Executa consulta dos afastamentos
 */


$sSql = $oDaoAssenta->sql_query(null, $sCampos, $sOrdem, $sWhere);

$rsServidoresAfastados = db_query($sSql);


//Situaçoes dos tipos de afastamentos
$aSituacoes = Array(
  294,
  312,
  330,
  323,
  348,
  338,
  372,
  340
);

// Array para armazenar os dados
$iDadosAfasta = array();
$aDados     = array();
// die(pg_num_rows($rsServidoresAfastados)."asd");
for ( $i=0; $i < pg_num_rows($rsServidoresAfastados); $i++ ) { 
   db_fieldsmemory($rsServidoresAfastados,$i);
   
  if(!in_array(trim($h12_codigo),$aSituacoes)){
    continue;
  }

   $ainicio     = explode("-", $h16_dtconc); // Pega o Ano Inicial do Afastamento
   $afim        = explode("-", $h16_dtterm); // Pega o Ano Final do Afastamento

   /**
   * Executa loop do tempo afastado, vinculando os dias por ano de afastamento
   */ 

   for ( $a=$ainicio[0]; $a <=$afim[0]; $a++ ) {
     
    $dataInicial = $a."-01-01";
    $dataFinal   = $a."-12-31";
    
    if( $a == $ainicio[0] ){
    $dataInicial = $h16_dtconc;
    }
   if( $a == $afim[0] ){
    $dataFinal = $h16_dtterm;
   }
  $iDadosAfasta[$a]['dtInicio'] = $dataInicial;
  $iDadosAfasta[$a]['dtFim']    = $dataFinal;
  $iDadosAfasta[$a]['nDias']   += Dias_de_Afasmento($dataInicial,$dataFinal);
  $iDadosAfasta[$a]['sDescr']   = $h12_descr;
  $sDescr = explode("/", $h12_descr);
  if(sizeof($sDescr) > 0){
    $h12_descr = $sDescr[0];
  }
  if($h12_codigo == 294  || $h12_codigo == 330 ){

  $iDadosAfasta[$a]['iSituDescr']    .= trim(Mais_func($h12_descr))." de ".db_formatar($dataInicial,"d")." a ".db_formatar($dataFinal,"d").". " ;
}
   $iDadosAfasta[$a]['iSitu']    .= trim(Mais_func($h12_descr))." de ".db_formatar($dataInicial,"d")." a ".db_formatar($dataFinal,"d").". " ;
  if( $h12_codigo == 294 ){
    $iDadosAfasta[$a]['iFaltas'] += Dias_de_Afasmento($dataInicial,$dataFinal);
  }else{
    $iDadosAfasta[$a]['iNormal'] += Dias_de_Afasmento($dataInicial,$dataFinal);
  }
   }
}

$rsAdmissao = db_query("select distinct  rh01_admiss
                    ,rh01_regist
                    ,rh01_admiss
                    ,z01_nome
                    ,rh37_descr
                    ,r59_descr
                    ,extract (year from rh05_recis) as rh05_recis
                        from rhpessoal 
                        inner join cgm          on rh01_numcgm = z01_numcgm
                        inner join rhpessoalmov on rh01_regist = rh02_regist 
                                  and    rh01_instit = rh02_instit
                                  and    rh02_anousu = fc_anofolha(".db_getsession('DB_instit').")
                                  and    rh02_mesusu = fc_mesfolha(".db_getsession('DB_instit').")
                        inner join rhfuncao on rh37_funcao = rh02_funcao 
                                                and rh37_instit = rh02_instit   
left join rhpesrescisao on rh02_seqpes = rh05_seqpes
  left join rescisao  on  rescisao.r59_anousu  = rhpessoalmov.rh02_anousu 
                                        and  rescisao.r59_mesusu  = rhpessoalmov.rh02_mesusu 
                                        and  rescisao.r59_causa   = rhpesrescisao.rh05_causa
                                        and  rescisao.r59_caub    = rhpesrescisao.rh05_caub::char(2) 
                                        and  rescisao.r59_instit  = rhpessoalmov.rh02_instit
                          where rh01_regist = {$iMatriculaServidor}");

db_fieldsmemory($rsAdmissao,0);


if( $resci != ""){
  $resci = $resci;
  $datafim = $resci;
}else{
  $datafim = date('Y-m-d');
  $resci = $datafim;
}
$dtAdmissao    = $admi; // Data de Admissao
$dtResci       = $datafim; // Data de rescisao / data de entrada da aposentadoria


if ( $dtResci == "") {
  db_redireciona('db_erros.php?fechar=true&db_erro=Não existem Códigos cadastrados no período');
}

$sInicio = explode("-", $dtAdmissao);
$sFim    = explode("-", $resci);
for ( $i=$sInicio[0]; $i <=$sFim[0]; $i++ ) { 

  if( $i == $sInicio[0] && $i == $sFim[0] ){
    $diasTrabalhados =  Dias_de_Afasmento($dtAdmissao,$resci);
  }elseif( $i == $sFim[0] ){
    $diasTrabalhados =  Dias_de_Afasmento($sFim[0]."-01-01",$resci);
  }elseif( $i == $sInicio[0] ){
    $diasTrabalhados =  Dias_de_Afasmento($dtAdmissao,$sInicio[0]."-12-31");
  }else{
    unset($diasTrabalhados);
  }
  $aDados[$i]['iDataIni'] = $iDadosAfasta[$i]['dtInicio'];
  $aDados[$i]['iDataFim'] = $iDadosAfasta[$i]['dtFim'];
  $aDados[$i]['iDiasDes'] = $iDadosAfasta[$i]['nDias'];
  $aDados[$i]['iDescric'] = $iDadosAfasta[$i]['sDescr'];
  $aDados[$i]['iSituati'] = $iDadosAfasta[$i]['iSitu'];
  $aDados[$i]['iSituatiDescr'] = $iDadosAfasta[$i]['iSituDescr'];
  $aDados[$i]['iDiasano'] = (isset($diasTrabalhados) ? $diasTrabalhados : dias_no_ano($i));
  $aDados[$i]['iDiasliq'] = ($aDados[$i]['iDiasano']-$aDados[$i]['iDiasDes']);
  $aDados[$i]['iDiasFaltas'] =  $iDadosAfasta[$i]['iFaltas'];
  $aDados[$i]['iDiasNormais'] =  $iDadosAfasta[$i]['iNormal'];
}


// Busca Averbaçao

$aDadosAverbados = array();
$sWhere     .= " and h16_assent in ( 356 , 288 , 547 )";
$sCampos    .= ",h16_quant";  
$sSql        = $oDaoAssenta->sql_query(null, $sCampos, $sOrdem, $sWhere);
$rsAverbados = db_query($sSql);

for ( $i=0; $i < pg_num_rows($rsAverbados); $i++ ) { 
    
    db_fieldsmemory($rsAverbados,$i);
  $aDadosAverbados[$h16_assent]['iDescric']  = $h12_descr;
  $aDadosAverbados[$h16_assent]['iDiasliq'] += $h16_quant;

}


$sWhere    = " h16_dtterm is not null ";
$sWhere   .= " and h16_regist = {$iMatriculaServidor} ";
$sWhere   .= " and h16_assent in ( 306,332,340,300 )";
$sCampos    .= " ,extract(year from h16_dtterm) as anofim";
$sSql        = $oDaoAssenta->sql_query(null, $sCampos, $sOrdem, $sWhere);
$rsDemissao  = db_query($sSql);

for ( $i=0; $i < pg_num_rows($rsDemissao); $i++ ) { 
    
    db_fieldsmemory($rsDemissao,$i);
    $iAnoFim  = $anofim;
    $iMotivo  = $h12_descr;
    $iDataFinal = $h16_dtterm; 
}

  $sTotalTrabalhados     = 0;
  $sDiasDeveriaTrabalhar = 0;
  $pdf->SetFont('Arial','B',8);
  $pdf->SetX(10);
  $pdf->Cell(20,15,"ANO",1,0,"C");
  $pdf->MultiCell(15,5,"TEMPO BRUTO (DIAS)",1,"C");
  $pdf->SetXY(45,155);
  $pdf->SetFont('Arial','B',9);
  $pdf->Cell(131,5,"Deduções","T",1,"C");
  $pdf->SetXY(45,161);
  $pdf->SetFont('Arial','',9);
  $pdf->Cell(12,9,"Faltas",1,0,"C");
  $pdf->MultiCell(24,3,"Licenças / Outros Afastamentos",1,"C");
  $pdf->SetXY(81,161);
  $pdf->Cell(10,9,'Soma',1,0,"C");
  $pdf->Cell(85,9,'Observação',1,1,"L");
  $pdf->SetXY(176,155);

  $pdf->SetFont('Arial','B',8);
  $pdf->MultiCell(24,7.5,"TEMPO LÍQUIDO (DIAS)",1,"C");

 foreach ($aDadosAverbados as $oAverba) {
   $pdf->SetFont('Arial','',8);
   $pdf->SetX(10);
   $pdf->Cell(20,6,"####",1,0,"C");
   $pdf->Cell(15,6,"####",1,0,"C");
   $pdf->Cell(12,6,"####",1,0,"C");
   $pdf->Cell(24,6,"####",1,0,"C");
   $pdf->Cell(10,6,"-",1,0,"C");
   $pdf->Cell(85,6,$oAverba['iDescric'],1,0,"L");
   $pdf->Cell(24,6,$oAverba['iDiasliq'],1,1,"C");
   $sTotalTrabalhados     += $oAverba['iDiasliq'];
 }
$iTotalFaltas = 0;
$iTotalLicens = 0;
foreach ($aDados as $iDados => $iValores) {
  $pdf->SetFont('Arial','',8);
  
  $iPosYAntes  = $pdf->GetY();
  $pdf->SetX(10);
  $pdf->Cell(20,6,$iDados,1,0,"C");
  $pdf->Cell(15,6,(trim($iValores['iDiasano'])  != "" ? $iValores['iDiasano']  : '0' ),1,0,"C");
  $pdf->Cell(12,6,(trim($iValores['iDiasFaltas'])  != "" ? $iValores['iDiasFaltas']  : '0' ),1,0,"C");
  $pdf->Cell(24,6,(trim($iValores['iDiasNormais']) != "" ? $iValores['iDiasNormais'] : '0' ),1,0,"C");
//  if($iAnoFim == $iDados){ $iValores['iDiasliq'] = ($iValores['iDiasliq'] - 1); }
  $pdf->Cell(10,6,"-",1,0,"C");
  $pdf->Cell(85,6,' --------------------------- ',1,0,"C");
  $pdf->Cell(24,6,$iValores['iDiasliq'],1,1,"C");
  
  $pdf->SetFont('Arial','',5);

  $sTotalTrabalhados     += $iValores['iDiasliq'];
  $sDiasDeveriaTrabalhar += $iValores['iDiasano'];
  $iTotalFaltas          += $iValores['iDiasFaltas'];
  $iTotalLicens          += $iValores['iDiasNormais'];
  $iAltura = $pdf->getY();
  if($iAltura > 265)
    $pdf->addpage('P'); 
  
  if($iAnoFim == $iDados){
    break;
  }

}
 
$pdf->SetFont('Arial','',8);
$pdf->SetX(10);
$pdf->Cell(20,6,'TOTAL',1,0,"C");
$pdf->Cell(15,6,$sDiasDeveriaTrabalhar,1,0,"C");
$pdf->Cell(12,6,$iTotalFaltas,1,0,"C");
$pdf->Cell(24,6,$iTotalLicens,1,0,"C");
$pdf->Cell(10,6,"-",1,0,"C");
$pdf->Cell(85,6,' --------------------------- ',1,0,"C");
$pdf->Cell(24,6,$sTotalTrabalhados,1,1,"C");
$pdf->SetFont('Arial','B',8);
$pdf->SetX(10);
$pdf->MultiCell(190,4,"Certifico, em face do apurado, que a interessado (a) conta, de efetivo exercício prestado neste Órgão, o tempo de contribuição de ".strtoupper(valorPorExtenso($sTotalTrabalhados))." dias, correspondente a ".time2text(($sTotalTrabalhados*86400)).".");

$pdf->SetFont('Arial','',8);
$pdf->MultiCell(190,4,$texto,1);

$oAltura = $pdf->getY();
$pdf->Rect(10, $oAltura+2, 120, 40, 'D');
$pdf->Rect(130, $oAltura+2, 70, 40, 'D');
// $pdf->ln();
$pdf->Cell(120,10,"Lavrei a Certidão que não contém emendas nem rasuras.",0,0,"L");
$pdf->Cell(120,10,"Visto do Dirigente do Órgão",0,1,"L");

if(isset($localdata) && !empty($localdata)){
  $pdf->Cell(120,5,"Local e Data: ".$localdata,0,0,"L");
  $pdf->Cell(120,5,"Data: ".$localdata,0,1,"L");
}else{
  $pdf->Cell(120,5,"Local e Data: Niterói,".data_extenso(),0,0,"L");
  $pdf->Cell(120,5,"Data: Niterói,".data_extenso(),0,1,"L");  
}

$pdf->ln(22);
$pdf->Cell(120,5,"Assinatura e carimbo do servidor",0,0,"C");
$pdf->Cell(70,5,"Assinatura e carimbo",0,1,"C");

$pdf->SetFont('Arial','B',8);
$pdf->Cell(70,5,"UNIDADE GESTORA DO RPPS",0,1,"L");

$oAltura = $pdf->getY();
$pdf->Rect(10, $oAltura+2, 190, 30, 'D');

$pdf->SetFont('Arial','',8);
$pdf->Cell(120,10,"HOMOLOGO a presente Certidão de Tempo de Contribuição e declaro que as informações nela constantes correspondem com a verdade.",0,1,"L");
$pdf->Cell(20,10,"Local e Data: ",0,0,"L");
$pdf->Cell(49,7,"","B",1,"L");
$pdf->ln(8);
$pdf->setX(125);
$pdf->Cell(70,7,"Assinatura e carimbo do Dirigente da UG ","T",1,"C");

$pdf->SetFont('Arial','B',8);
$pdf->MultiCell(190,4," Os espaços deverão ser anulados com linhas. Depois da última linha da discriminação, deverão ser apostas as mesmas assinaturas constantes no anverso e anulado o espaço em branco restantes ate a linha que limita o presente formulário. \n\n Esta Certidão não contém emendas nem rasuras.",0,"C");
$pdf->Output();

function time2text($time){        
    $response=array();
    $years = floor($time/(86400*365));
    $time=$time%(86400*365);
    $months = floor($time/(86400*30));
    $time=$time%(86400*30);
    $days = floor($time/86400);
    $time=$time%86400;
    $hours = floor($time/(3600));
    $time=$time%3600;
    $minutes = floor($time/60);
    $seconds=$time%60;
    if($months == 12){
      $years = $years+1;
      $months = 0;
    }
    if($years>0) $response[]=$years.' ano'. ($years>1?'s':' ');
    if($months>0) $response[]=$months.' mes'.($months>1?'es':' ');
    if($days>0) $response[]=$days.' dia' .($days>1?'s':' ');
    if($hours>0) $response[]=$hours.' hora'.($hours>1?'s':' ');
    if($minutes>0) $response[]=$minutes.' minuto' . ($minutes>1?'s':' ');
    if($seconds>0)         $response[]=$seconds.' segundo' . ($seconds>1?'s':' ');
    return implode(', ',$response);
}
function Mais_func($var =''){
  $var = explode(" ", $var);
  for ($i=0; $i < count($var); $i++) { 
    $return .= ucfirst(strtolower($var[$i]))." ";
  }
  return $return;
}
function Dias_de_Afasmento($dtInicio,$dtFim){

  $oRetorno = 1;
  $data1 = new DateTime( $dtFim );
  $data2 = new DateTime( $dtInicio );

  $intervalo = $data1->diff( $data2 );
  $oRetorno += $intervalo->days;

  // Retorna o resultado de dias de afastamento
  return $oRetorno;
}

function dias_no_ano($ano) {
  
  // Verifica ano Bisexto
    if(date('L', mktime(0, 0, 0, 1, 1, $ano))) {
    return 366;
  }else {
    return 365;
  }

}

function data_extenso(){

  $dia = date('d');
  $mes = date('m');
  $ano = date('Y');
   
  // configuração mes
   
  switch ($mes){
   
  case 1: $mes = "Janeiro"; break;
  case 2: $mes = "Fevereiro"; break;
  case 3: $mes = "Março"; break;
  case 4: $mes = "Abril"; break;
  case 5: $mes = "Maio"; break;
  case 6: $mes = "Junho"; break;
  case 7: $mes = "Julho"; break;
  case 8: $mes = "Agosto"; break;
  case 9: $mes = "Setembro"; break;
  case 10: $mes = "Outubro"; break;
  case 11: $mes = "Novembro"; break;
  case 12: $mes = "Dezembro"; break;
   
  }

//Agora basta imprimir na tela...
return $dia." de ".$mes. " de ".$ano;
}


function valorPorExtenso( $valor = 0, $bolExibirMoeda = false, $bolPalavraFeminina = false )
    {
 
 
        $singular = null;
        $plural = null;
 
        if ( $bolExibirMoeda )
        {
            $singular = array("centavo", "real", "mil", "milhão", "bilhão", "trilhão", "quatrilhão");
            $plural = array("centavos", "reais", "mil", "milhões", "bilhões", "trilhões","quatrilhões");
        }
        else
        {
            $singular = array("", "", "mil", "milhão", "bilhão", "trilhão", "quatrilhão");
            $plural = array("", "", "mil", "milhões", "bilhões", "trilhões","quatrilhões");
        }
 
        $c = array("", "cem", "duzentos", "trezentos", "quatrocentos","quinhentos", "seiscentos", "setecentos", "oitocentos", "novecentos");
        $d = array("", "dez", "vinte", "trinta", "quarenta", "cinquenta","sessenta", "setenta", "oitenta", "noventa");
        $d10 = array("dez", "onze", "doze", "treze", "quatorze", "quinze","dezesseis", "dezesete", "dezoito", "dezenove");
        $u = array("", "um", "dois", "três", "quatro", "cinco", "seis","sete", "oito", "nove");
 
 
        if ( $bolPalavraFeminina )
        {
        
            if ($valor == 1) 
            {
                $u = array("", "uma", "duas", "três", "quatro", "cinco", "seis","sete", "oito", "nove");
            }
            else 
            {
                $u = array("", "um", "duas", "três", "quatro", "cinco", "seis","sete", "oito", "nove");
            }
            
            
            $c = array("", "cem", "duzentas", "trezentas", "quatrocentas","quinhentas", "seiscentas", "setecentas", "oitocentas", "novecentas");
            
            
        }
 
 
        $z = 0;
 
        $valor = number_format( $valor, 2, ".", "." );
        $inteiro = explode( ".", $valor );
 
        for ( $i = 0; $i < count( $inteiro ); $i++ ) 
        {
            for ( $ii = mb_strlen( $inteiro[$i] ); $ii < 3; $ii++ ) 
            {
                $inteiro[$i] = "0" . $inteiro[$i];
            }
        }
 
        // $fim identifica onde que deve se dar junção de centenas por "e" ou por "," ;)
        $rt = null;
        $fim = count( $inteiro ) - ($inteiro[count( $inteiro ) - 1] > 0 ? 1 : 2);
        for ( $i = 0; $i < count( $inteiro ); $i++ )
        {
            $valor = $inteiro[$i];
            $rc = (($valor > 100) && ($valor < 200)) ? "cento" : $c[$valor[0]];
            $rd = ($valor[1] < 2) ? "" : $d[$valor[1]];
            $ru = ($valor > 0) ? (($valor[1] == 1) ? $d10[$valor[2]] : $u[$valor[2]]) : "";
 
            $r = $rc . (($rc && ($rd || $ru)) ? " e " : "") . $rd . (($rd && $ru) ? " e " : "") . $ru;
            $t = count( $inteiro ) - 1 - $i;
            $r .= $r ? " " . ($valor > 1 ? $plural[$t] : $singular[$t]) : "";
            if ( $valor == "000")
                $z++;
            elseif ( $z > 0 )
                $z--;
                
            if ( ($t == 1) && ($z > 0) && ($inteiro[0] > 0) )
                $r .= ( ($z > 1) ? " de " : "") . $plural[$t];
                
            if ( $r )
                $rt = $rt . ((($i > 0) && ($i <= $fim) && ($inteiro[0] > 0) && ($z < 1)) ? ( ($i < $fim) ? ", " : " e ") : " ") . $r;
        }
 
        $rt = mb_substr( $rt, 1 );
 
        return($rt ? trim( $rt ) : "zero");
 
    }

?>

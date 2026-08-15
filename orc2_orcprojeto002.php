<?
/*
 *     E-cidade Software Publico para Gestao Municipal                
 *  Copyright (C) 2014  DBselller Servicos de Informatica             
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

 include("fpdf151/pdf1.php");
 include("fpdf151/assinatura.php");
 include("libs/db_app.utils.php");
 include("libs/db_utils.php");
 include("classes/db_orcsuplem_classe.php");
 include("libs/db_liborcamento.php");
 include("classes/db_db_config_classe.php");
 include("classes/db_db_paragrafo_classe.php");
 db_app::import("orcamento.suplementacao.*");
 
function testa($var){
  echo "<pre>";
  print_r($var);
  echo "</pre>";
}



function buscaInfo($coddot){
  $ano = date("Y");  

  $sql = pg_query("SELECT o52_funcao, o52_descr, o53_subfuncao, o53_descr, o54_programa, o54_descr from orcdotacao inner join db_config on db_config.codigo = orcdotacao.o58_instit inner join orctiporec on orctiporec.o15_codigo = orcdotacao.o58_codigo inner join orcfuncao on orcfuncao.o52_funcao = orcdotacao.o58_funcao inner join orcsubfuncao on orcsubfuncao.o53_subfuncao = orcdotacao.o58_subfuncao inner join orcprograma on orcprograma.o54_anousu = orcdotacao.o58_anousu and orcprograma.o54_programa = orcdotacao.o58_programa inner join orcelemento on orcelemento.o56_codele = orcdotacao.o58_codele and orcelemento.o56_anousu = orcdotacao.o58_anousu inner join orcprojativ on orcprojativ.o55_anousu = orcdotacao.o58_anousu and orcprojativ.o55_projativ = orcdotacao.o58_projativ inner join orcorgao on orcorgao.o40_anousu = orcdotacao.o58_anousu and orcorgao.o40_orgao = orcdotacao.o58_orgao inner join orcunidade on orcunidade.o41_anousu = orcdotacao.o58_anousu and orcunidade.o41_orgao = orcdotacao.o58_orgao and orcunidade.o41_unidade = orcdotacao.o58_unidade inner join concarpeculiar on concarpeculiar.c58_sequencial = orcdotacao.o58_concarpeculiar inner join ppasubtitulolocalizadorgasto on ppasubtitulolocalizadorgasto.o11_sequencial = orcdotacao.o58_localizadorgastos inner join cgm on cgm.z01_numcgm = db_config.numcgm inner join db_tipoinstit on db_tipoinstit.db21_codtipo = db_config.db21_tipoinstit inner join orcproduto on orcproduto.o22_codproduto = orcprojativ.o55_orcproduto inner join orcorgao as a on a.o40_anousu = orcunidade.o41_anousu and a.o40_orgao = orcunidade.o41_orgao where orcdotacao.o58_anousu = {$ano} and orcdotacao.o58_coddot = {$coddot}");
  $resultado = pg_fetch_all($sql);
  return $resultado[0];
}


function retornaOrgao($projeto){
  $sql = pg_query("SELECT  o46_tiposup,
                o48_descr,
                o47_coddot,
                o47_anousu,
                o58_orgao,
                o40_descr,
                o58_unidade,
                o56_elemento,
                o56_descr,
                o58_projativ,
                o55_descr,
                o41_descr,
                o15_codigo,
                o15_descr,
                sum(o47_valor) as o47_valor
           from orcprojeto
                inner join orcsuplem on o46_codlei = orcprojeto.o39_codproj
                inner join orcsuplemval on o47_codsup = orcsuplem.o46_codsup 
                                       and orcsuplemval.o47_valor > 0
                inner join orcdotacao  on o58_coddot  = o47_coddot and o58_anousu = ".db_getsession("DB_anousu")."
                inner join orcelemento on o58_codele  = o56_codele and o56_anousu = ".db_getsession("DB_anousu")."
                inner join orcorgao    on o58_orgao   = o40_orgao  and o40_anousu = ".db_getsession("DB_anousu")."
                inner join orcunidade  on o58_unidade = o41_unidade and o41_anousu = ".db_getsession("DB_anousu")."
                                      and o41_orgao   = o58_orgao
                inner join orctiporec on o15_codigo   = o58_codigo                                       
                inner join orcprojativ on o58_projativ  = o55_projativ  and o55_anousu = ".db_getsession("DB_anousu")."
                inner join orcsuplemtipo on o48_tiposup = orcsuplem.o46_tiposup
                                          and orcsuplemtipo.o48_coddocsup >  0                      
          where o39_codproj=$projeto
         group by o47_coddot,
                 o46_tiposup,
                 o48_descr,
                 o40_descr,
                 o47_anousu,
                 o58_projativ,
                 o55_descr,
                 o56_descr,
                 o58_orgao,
                 o58_unidade,
                 o56_elemento,
                 o41_descr,
                o15_codigo,
                o15_descr");
  $resultado = pg_fetch_all($sql);
  //return $resultado[0]["o40_descr"];
  return $resultado;
}

function buscaDescricao($codigo){
  $sql = pg_query("SELECT o15_descr FROM orctiporec WHERE o15_codigo = {$codigo}");
  $resultado = pg_fetch_all($sql);
  return $resultado[0]["o15_descr"];
}


 $classinatura = new cl_assinatura;
 $cldbconfig    = new cl_db_config;
 $cldbparagrafo = new cl_db_paragrafo;
 $clorcsuplem    = new cl_orcsuplem;
 $auxiliar = new cl_orcsuplem;
 $aux      = new cl_orcsuplem; 
  
 parse_str($HTTP_SERVER_VARS["QUERY_STRING"]);
 
 $anousu = db_getsession("DB_anousu");
 $projeto = (isset($o46_codlei)&&!empty($o46_codlei))?$o46_codlei:'null';
 $ano_anterior = ($anousu -1);
 $tem_superavit = false;
 $nomeorgao = retornaOrgao($projeto);
 
 $novonomeorgao = "";
 $guarda = "";
 $konta = 0;
 foreach ($nomeorgao as $no) {
  if($guarda == $no["o40_descr"]){
    $guarda = $no["o40_descr"];  
    continue;
  }
  $novonomeorgao .= $no["o40_descr"] . " e ";
  $guarda = $no["o40_descr"];
  $konta++;
 }
 
 $novonomeorgao = rtrim($novonomeorgao, " e ");
 
 



///////////////////////////////////////////
// defini a classe abaixo pra poder tirar o timpre conforme o caso 
class PDF_TIMBRE extends pdf1 { 
    function Header() {        
    //$this->Ln(45);
    $sql = "select nomeinst,
                 bairro,
                 cgc,
                 trim(ender)||','||trim(cast(numero as text)) as ender,
                 upper(munic) as munic,
                 uf,
                 telef,
                 email,
                 url,
                 logo, 
                 db12_extenso
          from db_config 
                 inner join db_uf on db12_uf = uf
          where codigo = ".db_getsession("DB_instit");
  $result = db_query($sql);
  global $nomeinst;
  global $ender;
  global $munic;
  global $cgc;
  global $bairro;
  global $uf;
  global $db12_extenso;
  global $logo;
  //echo $sql;
  db_fieldsmemory($result,0);
  $db12_extenso = pg_result($result,0,"db12_extenso");
  /// seta a margem esquerda que veio do relatorio
  $S = $this->lMargin;
  $this->SetLeftMargin(10);
  $Letra = 'Times';

  //$posini = ($this->w/6)-15;
  $posini = ($this->w/2)-7;

  //$this->Image("imagens/files/logo_boleto.png",$posini,8,20);
  //$this->Image('imagens/files/'.$logo,$posini,8,20);
  $this->Image('imagens/files/'.$logo,$posini,8,10);
  $this->Ln(10);
  $this->SetFont($Letra,'B',10);
  $this->MultiCell(0,2,"PREFEITURA MUNICIPAL DE VOLTA REDONDA",0,"C",0);


  
  $this->SetFont($Letra,'',8);
  $this->MultiCell(0,6,"GABINETE DO PREFEITO",0,"C",0);

  $this->setX(70);
  $this->SetFont($Letra,'',7);
  $this->MultiCell(70,4,"Volta Redonda - Sede do Governo do antigo Povoado de Santo Antônio, inicialmente Distrito de Paz, emancipada aos 17 dias do  mês de Julho de 1954, berço da Siderurgia no Brasil.",0,"J",0);

  $this->sety(5);
  $this->cell(140, 4, " ", 0, 0, "C");
  $this->cell(54, 4, "Processo", 1, 1, "C");
  $this->cell(140, 4, " ", 0, 0, "C");
  $this->cell(18, 4, "Número", 1, 0, "C");
  $this->cell(18, 4, "Exercício", 1, 0, "C");
  $this->cell(18, 4, "Folha", 1, 1, "C");
  $this->cell(140, 4, " ", 0, 0, "C");
  $this->cell(18, 8, " ", 1, 0, "C");
  $this->cell(18, 8, " ", 1, 0, "C");
  $this->cell(18, 8, " ", 1, 1, "C");
  
  
  
  $this->Ln(10);
  $this->SetLeftMargin($S);

  if($this->PageNo() > 1){
    $projeto = (isset($_GET["o46_codlei"])&&!empty($_GET["o46_codlei"]))?$_GET["o46_codlei"]:'null';
    
    $sqlX = "select sum(0) as total_suplementado,
                 case when o139_orcprojeto is null then '1' else '2' end as projeto_tipo,
                 o39_numero, 
                 o39_data,
                 o39_lei,
                 o39_leidata,
                 exists(select 1 
                          from orcsuplem b
                              inner join orcsuplemlan on b.o46_codsup = o49_codsup
                        where b.o46_codlei={$projeto}) as processado,
                 o39_compllei,
                 o45_numlei,      
                 date_part('year',o45_dataini)  as ano_lei     
           from orcprojeto
                inner join orclei on  o45_codlei   = orcprojeto.o39_codlei
                inner join orcsuplem on o46_codlei = orcprojeto.o39_codproj
                left  join orcprojetoorcprojetolei on o39_codproj = o139_orcprojeto
                inner join orcsuplemtipo on o48_tiposup = orcsuplem.o46_tiposup
                                      and orcsuplemtipo.o48_coddocsup >  0                      
         where o39_codproj={$projeto}
         group by o139_orcprojeto,o39_numero,o39_data,o39_lei,o39_compllei,o39_leidata,o45_numlei, ano_lei 
         ";
    $resX= pg_query($sqlX);
    $x = pg_fetch_all($resX);
    $numX = $x[0]["o39_numero"];
    
    

    $px ="DECRETO Nº ".$numX;
    //$this->Cell(0,10,$projeto_tipo_texto.$this->PageNo().' de {nb}',0,1,'R');
    $this->Cell(0,10,$px,0,1,'L');
    $this->Cell(0,10,$this->PageNo(),0,1,'R');
    //$this->Cell(0,10,$px.' .0{nb}',0,1,'R');
  }
         
    }
    function Footer() {        
    $this->Ln(45);                  
    }
}
///////////////////////////////////////////





if ($timbre =='s'){
   $pdf = new PDF1();
} else {  
    // a classe abaixo sobrescreve a funcao Header() sem implementacao
   $pdf = new PDF_TIMBRE();

}    
 $pdf->Open();
 $pdf->AliasNbPages();
 $pdf->AddPage("P");
 // monta cabecalho do relatório    
 $pdf->SetFillColor(235);
 $pdf->SetFont('Arial','',9);
 $pdf->setY(60);
 $pdf->setX(5);
 $artigo = 0;


 /**
   * executa select para saber se é suplementação ou crédito especial 
   *
   */
  $sql = "select  
                o48_tiposup,
                o46_data,
                o39_data
          from orcprojeto
                inner join orcsuplem on o46_codlei = orcprojeto.o39_codproj
                inner join orcsuplemtipo on o48_tiposup = orcsuplem.o46_tiposup
                                               and orcsuplemtipo.o48_coddocsup >  0                           
          where o39_codproj=$projeto 
      order by o46_data
      limit 1
         ";
  $res= $auxiliar->sql_record($sql); 
  db_fieldsmemory($res,0); 
//  db_criatabela($res);exit;
  $xtipo = $o48_tiposup;
  // $xdata = $o46_data;
  $xdata = $o39_data;
 
  if($xtipo < 1006 ||  $xtipo > 1014 ){
    $tipo_sup = 'Crédito Suplementar';
  }elseif ($xtipo == 1014){  
    $tipo_sup = 'Crédito de Transferência';
  }else{
    $tipo_sup = 'Crédito Especial';
  }
  


 /**
   * executa select para pegar o total da suplementação 
   *
   */
  $sql = "select sum(0) as total_suplementado,
                 case when o139_orcprojeto is null then '1' else '2' end as projeto_tipo,
                 o39_numero, 
                 o39_data,
                 o39_lei,
                             o39_leidata,
                             exists(select 1 
                                      from orcsuplem b
                                          inner join orcsuplemlan on b.o46_codsup = o49_codsup
                                    where b.o46_codlei={$projeto}) as processado,
                 o39_compllei,
                 o45_numlei,      
                 date_part('year',o45_dataini)  as ano_lei       
           from orcprojeto
                inner join orclei on  o45_codlei   = orcprojeto.o39_codlei
                inner join orcsuplem on o46_codlei = orcprojeto.o39_codproj
                left  join orcprojetoorcprojetolei on o39_codproj = o139_orcprojeto
                inner join orcsuplemtipo on o48_tiposup = orcsuplem.o46_tiposup
                                      and orcsuplemtipo.o48_coddocsup >  0                            
         where o39_codproj=$projeto
           group by o139_orcprojeto,o39_numero,o39_data,o39_lei,o39_compllei,o39_leidata,o45_numlei, ano_lei 
         ";
  $res= $auxiliar->sql_record($sql);
  //var_dump($sql); die("confere");
  //db_criatabela($res);exit;
   
  if ($auxiliar->numrows > 0 ){
       db_fieldsmemory($res,0,true); 
       global $projeto_tipo,$total_suplementado,$o39_numero,$o39_data,$o39_descr,$o39_lei,$o39_leidata,$o45_numlei;
  } else {
       db_redireciona('db_erros.php?fechar=true&db_erro=(Ln:115) Nenhum registro encontrado.');
  }
  if ($processado == 't') {
    $projeto_tipo = 1;
  }
  
  $sSqlSuplementacoes   = $clorcsuplem->sql_query(null,"*","o46_codsup","orcprojeto.o39_codproj= {$projeto}");
  $rsSuplementacoes     = $clorcsuplem->sql_record($sSqlSuplementacoes);
  //var_dump($sSqlSuplementacoes); die("Teste");
  $aSuplementacao       = db_utils::getCollectionByRecord($rsSuplementacoes);
  $valorutilizado       = 0;

  foreach ($aSuplementacao as $oSuplem) {
      
    $oSuplementacao = new Suplementacao($oSuplem->o46_codsup);
    $total_suplementado += $oSuplementacao->getvalorSuplementacao();  
  }
  unset($oSuplementacao);
 /////////////////////////////////////////////////////////   
         
    if ($projeto_tipo == "1"){
          //$projeto_tipo_texto ="DECRETO";
      $projeto_tipo_texto ="DECRETO Nº";
          $txt="Abre $tipo_sup na importancia de ".
         "R$ ".db_formatar($total_suplementado,'f')." (".db_extenso($total_suplementado,true).") e da outras providências. ";
          
    }else if ($projeto_tipo == "2") {
       $projeto_tipo_texto ="PROJETO DE LEI";
       $txt="Autoriza o Poder Executivo Municipal a abrir $tipo_sup na importancia de ".
         "R$ ".db_formatar($total_suplementado,'f')." (".db_extenso($total_suplementado,true).") e da outras providências. ";
    }else {
        // tipo 3 = retificador
        if   (strlen(trim($o39_lei))>0) {
              $projeto_tipo_texto ="PROJETO DE LEI";
              $txt="Autoriza o Poder Executivo Municipal a abrir $tipo_sup na importancia de ".
              "R$ ".db_formatar($total_suplementado,'f')." (".db_extenso($total_suplementado,true).") e da outras providências. ";
        } else {
              //$projeto_tipo_texto ="DECRETO ".$o39_numero;
              $projeto_tipo_texto ="DECRETO Nº ".$o39_numero;
              $txt="Abre $tipo_sup na importancia de ".
              "R$ ".db_formatar($total_suplementado,'f')." (".db_extenso($total_suplementado,true).") e da outras providências. ";
        }
    }   

    if($timbre !='s'){
      

    }
   
    $pdf->setX(20);         
    $pdf->SetFont('Arial','b',9);
    $pdf->Cell(50, 4, "", 0, 0, "C");
    $pdf->Cell(70,4,$projeto_tipo_texto." ".($projeto_tipo == 1?$o39_numero:''),"B",1,"C");
    $pdf->SetFont('Arial','',9);
    $pdf->ln(2);
    $pdf->Cell(60, 4, "", 0, 0, "C");
    $pdf->Cell(70,6,"Abre Crédito Adicional Suplementar.","B",1,"C");
    $pdf->ln(6);
    $pdf->SetFont('Arial','b',9);
    $pdf->Cell(35, 4, "", 0, 0, "C");
    $pdf->Cell(70,4,"O Prefeito Municipal de Volta Redonda, no uso de suas atribuições legais, e em ",0,1,"J");
    $pdf->Cell(35, 4, "", 0, 0, "C");
    $pdf->Cell(70,4,"conformidade com o art. 09 da Lei Municipal nº 6.137 de 10 de janeiro de 2023,",0,1,"J");
    $pdf->ln(8);
    $pdf->Cell(85, 4, "", 0, 0, "C");
    $pdf->Cell(20,4,"DECRETA:","B",1,"C");
    $pdf->SetFont('Arial','',9);

    $pdf->Ln(7);    
    
  
    
    //caso este projeto tenha sido reretificado por algum outro , coloca esta informação aqui   
    $sql = "select o48_projeto,o48_data,o39_numero,o39_data
                 from orcsuplemretif
                        inner join orcprojeto on o48_projeto =o39_codproj 
                 where o48_retificado = $projeto
                ";
     $res_retif = db_query($sql);
     if (pg_numrows($res_retif)>0){
         db_fieldsmemory($res_retif,0,true);   
         $pdf->setX(20);     
         $pdf->multicell(170,4,"Este projeto foi retificado pelo projeto $o48_projeto em $o48_data referente ao Decreto/Lei $o39_numero de $o39_data",'B','J','0',20);
         $pdf->Ln(4); 
     }
  
   
    //caso este projeto tenha sido reretificado por algum outro , coloca esta informação aqui   
    $sql = "select o48_texto
                 from orcsuplemretif
                      inner join orcprojeto on o48_retificado =o39_codproj 
                 where o48_projeto = $projeto
                ";
    $res_retif = db_query($sql);
    if (pg_numrows($res_retif)>0){
      db_fieldsmemory($res_retif,0,true);
        if (strlen($o48_texto) >1 ){
        $pdf->setX(20);  
        $pdf->multicell(170,4,"$o48_texto",'B','J','0',20);
        $pdf->Ln(4); 
        }
    }
   
      
    
//    $txt="Autoriza o Poder Executivo Municipal a abrir $tipo_sup na importancia de ".
//         "R$ ".db_formatar($total_suplementado,'f')." (".db_extenso($total_suplementado,true).") e da outras providências. ";
    $pdf->setX(100);
    //$pdf->multicell(90,4,$txt,'0','J','0',20); 
    //$pdf->Ln(7);
     
      
    if ($projeto_tipo == "1"){ // decreto    
      
       $res= $cldbconfig->sql_record($cldbconfig->sql_query(db_getsession("DB_instit")));
       db_fieldsmemory($res,0);
       $pdf->setX(20);
       $pref = ucfirst($pref);
      // $pref = 'VIVIAN LITIA FLORES DA SILVA';
       
     //  $txt="$pref, PREFEITA MUNICIPAL EM EXERCÍCIO DE $munic, $uf, no uso de suas atribuições legais e de conformidade com a Lei Municipal $o45_numlei";
       if ( $db21_codcli == 34 ) {
         $txt="$pref, PRESIDENTE DA CAMARA MUNICIPAL DE VEREADORES DE $munic, $uf, no uso de suas atribuições legais e de conformidade com a Lei Municipal n" . chr(186) ." $o45_numlei";
       } else {         
          $txt = "O Prefeito Municipal de Volta Redonda, no uso de suas atribuições legais, e em conformidade com o art. 08 da Lei Municipal nº 5.765 de 30 de dezembro de 2020.";
       }
       if($o39_compllei != ""){
         $txt .= ", $o39_compllei, DECRETA:";
       }else{
         $txt .= " DECRETA:";
       }
       //$pdf->multicell(170,4,$txt,'0','J','0');
       $pdf->Ln(7);      
       //var_dump($o58_orgao);    die("Confere órgão");
       $artigo = $artigo +1;
       $txt="Art $artigo. - Fica aberto Crédito Adicional Suplementar no valor de R$ ".trim(db_formatar($total_suplementado,'f'))." (".db_extenso($total_suplementado,true)." ) ".
              "visando atender a seguinte despesa da ". $novonomeorgao .", a saber:";
       /*$txt="Art $artigo. - Fica aberto $tipo_sup ".
                "na importância de  R$ ".db_formatar($total_suplementado,'f')." (".db_extenso($total_suplementado,true)." ) ".
                "sob a seguinte classificação econômica e programática ";*/
    } else {   // quando for lei

       $res = $cldbconfig->sql_record($cldbconfig->sql_query(db_getsession("DB_instit")));
       db_fieldsmemory($res,0);       
       
       $pdf->setX(20);
       $pref = strtoupper($pref);
       if ( $db21_codcli == 34 ) {
         $txt="$pref, PREFEITO MUNICIPAL DE $munic, $uf.";
       } else {
         $txt="$pref, PRESIDENTE DA CAMARA MUNICIPAL DE VEREADORES DE $munic, $uf.";
       }
       $pdf->multicell(170,4,$txt,'0','J','0');
       $pdf->Ln(7);      
       $pdf->setX(20);
       $txt="FAÇO SABER, que a Camara Municipal aprovou e eu sanciono a seguinte Lei: ";
       $pdf->multicell(170,4,$txt,'0','J','0');
       $pdf->Ln(7);      
       $artigo = $artigo +1;
       $txt="Art $artigo. -  Fica o Poder Executivo Municipal autorizado a abrir $tipo_sup ".
            "na importância de  R$ ".db_formatar($total_suplementado,'f')." (".db_extenso($total_suplementado,true)." ) ".
        "sob a seguinte classificação econômica e programática ";
    }
    
    
////////// primeiro artigo, das suplementações
//       $artigo = $artigo +1;
//    $txt="Art $artigo. -  Fica o Poder Executivo Municipal autorizado a abrir $tipo_sup ".
//         "na importância de  R$ ".db_formatar($total_suplementado,'f')." (".db_extenso($total_suplementado,true)." ) ".
//   "sob a seguinte classificação econônica e programatica ";
    $pdf->setX(20);  
    $pdf->multicell(170,4,"$txt",'0','J','0',20);
    $pdf->Ln(4);


  // seleciona suplementacoes do projeto
  // executa o mesmo select, só que agora pra listar as suplementações
  
/*ORIGINAL
  $sql="select  o46_tiposup,
                      o48_descr,
                      o47_coddot,
                      o47_anousu,
                        o58_orgao,
                        o40_descr,
                        o58_unidade,
                o56_elemento,
                o56_descr,
                o58_projativ,
                o55_descr,
                o41_descr,
                o15_codigo,
                o15_descr,
                        sum(o47_valor) as o47_valor
           from orcprojeto
                    inner join orcsuplem on o46_codlei = orcprojeto.o39_codproj
                    inner join orcsuplemval on o47_codsup = orcsuplem.o46_codsup 
                                           and orcsuplemval.o47_valor > 0
                      inner join orcdotacao  on o58_coddot  = o47_coddot and o58_anousu = ".db_getsession("DB_anousu")."
                inner join orcelemento on o58_codele  = o56_codele and o56_anousu = ".db_getsession("DB_anousu")."
                inner join orcorgao    on o58_orgao   = o40_orgao  and o40_anousu = ".db_getsession("DB_anousu")."
                inner join orcunidade  on o58_unidade = o41_unidade and o41_anousu = ".db_getsession("DB_anousu")."
                                      and o41_orgao   = o58_orgao
                inner join orctiporec on o15_codigo   = o58_codigo                                       
                inner join orcprojativ on o58_projativ  = o55_projativ  and o55_anousu = ".db_getsession("DB_anousu")."
                    inner join orcsuplemtipo on o48_tiposup = orcsuplem.o46_tiposup
                                              and orcsuplemtipo.o48_coddocsup >  0                            
            where o39_codproj=$projeto
             group by o47_coddot,
                   o46_tiposup,
                               o48_descr,
                               o40_descr,
                               o47_anousu,
                 o58_projativ,
                 o55_descr,
                 o56_descr,
                               o58_orgao,
                               o58_unidade,
                 o56_elemento,
                 o41_descr,
                o15_codigo,
                o15_descr ";
            
         $sSqlDotacaoPPA = "select  o46_tiposup,
                o48_descr,
                0 as coddot,
                o08_ano,
                o08_orgao,
                o40_descr,
                o08_unidade,
                o56_elemento,
                o56_descr,
                o08_projativ,
                o55_descr,
                o41_descr,
                o15_codigo,
                o15_descr,
                sum(o136_valor) as o47_valor
           from orcprojeto
                inner join orcsuplem on o46_codlei = orcprojeto.o39_codproj
                inner join orcsuplemdespesappa on o136_orcsuplem = orcsuplem.o46_codsup 
                inner join ppaestimativadespesa on o07_sequencial = o136_ppaestimativadespesa 
                inner join ppadotacao  on o07_coddot   = o08_sequencial 
                inner join orcelemento on o08_elemento = o56_codele and o56_anousu = o08_ano
                inner join orcorgao    on o08_orgao    = o40_orgao  and o40_anousu = o08_ano
                inner join orcunidade  on o08_unidade = o41_unidade and o41_anousu = o08_ano
                                      and o41_orgao   = o08_orgao
                inner join orctiporec on o15_codigo   = o08_recurso
                inner join orcprojativ on o08_projativ  = o55_projativ  and o55_anousu = o08_ano
                inner join orcsuplemtipo on o48_tiposup = orcsuplem.o46_tiposup
                                          and orcsuplemtipo.o48_coddocsup >  0                      
          where o39_codproj=$projeto
         group by 3,
                 o46_tiposup,
                 o48_descr,
                 o40_descr,
                 o56_descr,
                 o08_ano,
                 o08_projativ,
                 o55_descr,
                 o08_orgao,
                 o08_unidade,
                 o56_elemento,
                 o41_descr,
                o15_codigo,
                o15_descr
        order by o58_orgao,o58_unidade,o58_projativ,o56_elemento ";
  */
        $sql="select  o46_tiposup,
                      o48_descr,
                      o47_coddot,
                      o47_anousu,
                        o58_orgao,
                        o40_descr,
                        o58_unidade,
                o56_elemento,
                o56_descr,
                o58_projativ,
                o55_descr,
                o41_descr,
                o15_codigo,
                o15_codigosiconfi,
                o15_descr,
                        sum(o47_valor) as o47_valor
           from orcprojeto
                    inner join orcsuplem on o46_codlei = orcprojeto.o39_codproj
                    inner join orcsuplemval on o47_codsup = orcsuplem.o46_codsup 
                                           and orcsuplemval.o47_valor > 0
                      inner join orcdotacao  on o58_coddot  = o47_coddot and o58_anousu = ".db_getsession("DB_anousu")."
                inner join orcelemento on o58_codele  = o56_codele and o56_anousu = ".db_getsession("DB_anousu")."
                inner join orcorgao    on o58_orgao   = o40_orgao  and o40_anousu = ".db_getsession("DB_anousu")."
                inner join orcunidade  on o58_unidade = o41_unidade and o41_anousu = ".db_getsession("DB_anousu")."
                                      and o41_orgao   = o58_orgao
                inner join orctiporec on o15_codigo   = o58_codigo                                       
                inner join orcprojativ on o58_projativ  = o55_projativ  and o55_anousu = ".db_getsession("DB_anousu")."
                    inner join orcsuplemtipo on o48_tiposup = orcsuplem.o46_tiposup
                                              and orcsuplemtipo.o48_coddocsup >  0                            
            where o39_codproj=$projeto
             group by o47_coddot,
                   o46_tiposup,
                               o48_descr,
                               o40_descr,
                               o47_anousu,
                 o58_projativ,
                 o55_descr,
                 o56_descr,
                               o58_orgao,
                               o58_unidade,
                 o56_elemento,
                 o41_descr,
                 o15_codigo,
                o15_codigosiconfi,
                o15_descr ";
            
         $sSqlDotacaoPPA = "select  o46_tiposup,
                o48_descr,
                0 as coddot,
                o08_ano,
                o08_orgao,
                o40_descr,
                o08_unidade,
                o56_elemento,
                o56_descr,
                o08_projativ,
                o55_descr,
                o41_descr,
                o15_codigo,
                o15_codigosiconfi,
                o15_descr,
                sum(o136_valor) as o47_valor
           from orcprojeto
                inner join orcsuplem on o46_codlei = orcprojeto.o39_codproj
                inner join orcsuplemdespesappa on o136_orcsuplem = orcsuplem.o46_codsup 
                inner join ppaestimativadespesa on o07_sequencial = o136_ppaestimativadespesa 
                inner join ppadotacao  on o07_coddot   = o08_sequencial 
                inner join orcelemento on o08_elemento = o56_codele and o56_anousu = o08_ano
                inner join orcorgao    on o08_orgao    = o40_orgao  and o40_anousu = o08_ano
                inner join orcunidade  on o08_unidade = o41_unidade and o41_anousu = o08_ano
                                      and o41_orgao   = o08_orgao
                inner join orctiporec on o15_codigo   = o08_recurso
                inner join orcprojativ on o08_projativ  = o55_projativ  and o55_anousu = o08_ano
                inner join orcsuplemtipo on o48_tiposup = orcsuplem.o46_tiposup
                                          and orcsuplemtipo.o48_coddocsup >  0                      
          where o39_codproj=$projeto
         group by 3,
                 o46_tiposup,
                 o48_descr,
                 o40_descr,
                 o56_descr,
                 o08_ano,
                 o08_projativ,
                 o55_descr,
                 o08_orgao,
                 o08_unidade,
                 o56_elemento,
                 o41_descr,
                 o15_codigo,
                o15_codigosiconfi,
                o15_descr
        order by o58_orgao,o58_unidade,o58_projativ,o56_elemento ";
  $res= $auxiliar->sql_record($sql." union all {$sSqlDotacaoPPA}");  
   // db_criatabela($res);exit;
  
  //var_dump($sql." union all {$sSqlDotacaoPPA}");
  //$xxx = pg_fetch_all($res);
  //echo "<pre>";
  //print_r($xxx);
  //echo "</pre>";
  //die("Confere");
  $total = 0;
  if ($auxiliar->numrows > 0 ){
      for ($x=0;$x < $auxiliar->numrows ;$x++){

        db_fieldsmemory($res,$x);
        if($timbre =="n"){
          $dados = buscaInfo($o47_coddot);
          
          $pdf->setX(20);
          $pdf->Cell(150,4,db_formatar($o58_orgao,'orgao')."00 - $o40_descr",0,1,"L",'0');  
          $pdf->setX(20);    
          $pdf->Cell(150,4,db_formatar($o58_orgao,'orgao').db_formatar($o58_unidade,'orgao')." -  $o41_descr",0,1,"L",'0');  

          

          
          $numantes = db_formatar($o58_orgao,'orgao').db_formatar($o58_unidade,'orgao');
          
          $linha1 = $numantes . "." . $dados["o52_funcao"] . " - " . $dados["o52_descr"];
          $pdf->setX(20);     
          $pdf->Cell(150,4,$linha1,0,1,"L",'0');  

          $linha2 = $numantes . "." . $dados["o52_funcao"] . "." . $dados["o53_subfuncao"] . " - " . $dados["o53_descr"];
          $pdf->setX(20);
          $pdf->Cell(150,4,$linha2,0,1,"L",'0');  

          $linha3 = $dados["o54_programa"] . " - " . $dados["o54_descr"];

          $pdf->setX(20);
          $pdf->Cell(150,4,$linha3,0,1,"L",'0');

          $pdf->setX(20);     
          $pdf->Cell(150,4,"$o58_projativ - $o55_descr",0,1,"L",'0');  

          $pdf->setX(20);   
          $pdf->Cell(150,4,db_formatar($o56_elemento,'elemento')." - ".$o56_descr,0,1,"L",'0');     
          $pdf->setX(20);         
          

          //$pdf->Cell(120,4,db_formatar($o15_codigosiconfi,'recurso')." - ".trim($o15_descr)." ( $o47_coddot ) ",0,0,"L",'0');  
          $pdf->Cell(120,4,db_formatar($o15_codigo,'recurso')." - ".trim($o15_descr)." ( $o47_coddot ) ",0,0,"L",'0');  


          $pdf->Cell(50,4,db_formatar($o47_valor,'f'),0,1,"R",'0');  
          $pdf->setX(20);   
          $total += $o47_valor;
          $pdf->Ln();
        } else {
          $pdf->setX(20);
        $pdf->Cell(150,4,db_formatar($o58_orgao,'orgao')."00 - $o40_descr",0,1,"L",'0');  
        $pdf->setX(20);    
        $pdf->Cell(150,4,db_formatar($o58_orgao,'orgao').db_formatar($o58_unidade,'orgao')." -  $o41_descr",0,1,"L",'0');  
        $pdf->setX(20);     
        $pdf->Cell(150,4,"$o58_projativ - $o55_descr",0,1,"L",'0');  
        $pdf->setX(20);   
        $pdf->Cell(150,4,db_formatar($o56_elemento,'elemento')." - ".$o56_descr,0,1,"L",'0');     
        $pdf->setX(20);         
        

        //$pdf->Cell(120,4,db_formatar($o15_codigosiconfi,'recurso')." - ".trim($o15_descr)." ( $o47_coddot ) ",0,0,"L",'0');  
        $pdf->Cell(120,4,db_formatar($o15_codigo,'recurso')." - ".trim($o15_descr)." ( $o47_coddot ) ",0,0,"L",'0');  

        $pdf->Cell(50,4,db_formatar($o47_valor,'f'),0,1,"R",'0');  
        $pdf->setX(20);   
        $total += $o47_valor;
        $pdf->Ln();  
        }
        
        
     }      
      $pdf->Cell(130,4,'',0,0,"L",'0');  
      $pdf->setX(160);
      $pdf->Cell(30,4,db_formatar($total,'f'),"T",1,"R",'0');  
      $pdf->setX(20);   
  }

 /// reducoes
 /// entram como reduções as reduções, receitas e o texto do projeto quando superávit
 /// 
 //-- texto do artigo 2
 $sql = "select o39_texto
         from orcprojeto
         where o39_codproj=$projeto ";
 $res= $auxiliar->sql_record($sql);
 db_fieldsmemory($res,0); 
 $nomeorgao2 = retornaOrgao($projeto);
 //var_dump($nomeorgao2); die("Confere Fazenda");
   $pdf->Ln(4);
   //$txt= pg_result($res,0,"o39_texto");
   
   //LOCALIZAÇÃO ORIGINAL
   //$txt = "Art 2º. - Para permitir a abertura do Crédito Adicional Suplementar mencionado no artigo anterior, será utilizada como fonte de recurso, o cancelamento parcial da seguinte dotação da ".  $nomeorgao .", a saber:";
   //$pdf->setX(20);     
   //$pdf->multicell(170,4,$txt,'0','J','0',20);
   //$pdf->Ln(4);

 //-------
 $sql = "select 
              o39_codproj,
          o39_texto,
              o48_descr,
          o58_orgao,
          o58_unidade,
          o58_projativ,
              o47_coddot, 
              o47_anousu,
          sum(o47_valor) as o47_valor
         from orcprojeto
              inner join orcsuplem on o46_codlei = orcprojeto.o39_codproj
              inner join orcsuplemval on o47_codsup = orcsuplem.o46_codsup 
                                      and orcsuplemval.o47_valor < 0
              inner join orcdotacao on o58_coddot=o47_coddot and 
                                   o58_anousu=o47_anousu
              inner join orcsuplemtipo on o48_tiposup = orcsuplem.o46_tiposup
                                      and orcsuplemtipo.o48_coddocred >  0
         where o39_codproj=$projeto
     group by o39_codproj,
                  o39_texto,
              o48_descr,

          o58_orgao,
          o58_unidade,
                  o58_projativ,
              o47_coddot,
              o47_anousu
         order by o58_orgao,o58_unidade,o58_projativ
         ";
 $res= $auxiliar->sql_record($sql);
 $pegasec2 = pg_fetch_all($res);
 $x2dot = $pegasec2[0]["o47_coddot"];
 $x2ano = $pegasec2[0]["o47_anousu"];
 $x2sec = db_dotacaosaldo(8,2,2,true," o58_coddot = $x2dot and o58_anousu =$x2ano ");
 $nomesec2 = pg_fetch_all($x2sec);
 $nomesec2 = $nomesec2[0]["o40_descr"];
 
  if($konta > 1){    
    $txt = " Art 2º. - Para permitir a abertura do Crédito Adicional Suplementar mencionado no artigo anterior, será utilizada como fonte de recurso, o cancelamento parcial da seguinte dotação da ".  $novonomeorgao .", a saber:";
  }else{    
    $txt = " Art 2º. - Para permitir a abertura do Crédito Adicional Suplementar mencionado no artigo anterior, será utilizada como fonte de recurso, o cancelamento parcial da seguinte dotação da ".  $nomesec2 .", a saber:";
  }
   //$txt = " Art 2º. - Para permitir a abertura do Crédito Adicional Suplementar mencionado no artigo anterior, será utilizada como fonte de recurso, o cancelamento parcial da seguinte dotação da ".  $novonomeorgao .", a saber:";
   $pdf->setX(20);   
   $pdf->multicell(170,4,$txt,'0','J','0',20);
   $pdf->Ln(4);
 
 $tem_reduz = 0;

 if ($auxiliar->numrows>0 ) {
  
     //////////  artigo 2, paragrafo das reduções
          ////////////////////////////////////////////////      
    /////// imprime reduções  ///////////////////////////////////////////////    
  $total = 0;
  $tem_reduz = 1;
  for ($x=0;$x < $auxiliar->numrows ;$x++){
    db_fieldsmemory($res,$x);
    db_query("BEGIN");
    $r_dot = db_dotacaosaldo(8,2,2,true," o58_coddot = $o47_coddot and o58_anousu =$o47_anousu ");
    
      db_query("ROLLBACK");
    if(pg_numrows($r_dot)>0){      
      db_fieldsmemory($r_dot,0,true);
      if($timbre == "n"){
        
        $dados = buscaInfo($o47_coddot);        
        
        $pdf->setX(20);
        $pdf->Cell(150,4,db_formatar($o58_orgao,'orgao')."00 - $o40_descr",0,1,"L",'0');  
        $pdf->setX(20);    
        $pdf->Cell(150,4,db_formatar($o58_orgao,'orgao').db_formatar($o58_unidade,'orgao')." - $o41_descr",0,1,"L",'0');
        $numantes = db_formatar($o58_orgao,'orgao').db_formatar($o58_unidade,'orgao');
          
        $linha1 = $numantes . "." . $dados["o52_funcao"] . " - " . $dados["o52_descr"];
        //var_dump(db_formatar($o58_orgao,'orgao')."00 - $o40_descr");
        //var_dump(db_formatar($o58_orgao,'orgao').db_formatar($o58_unidade,'orgao')." - $o41_descr");
        //var_dump($linha1);
        $pdf->setX(20);     
        $pdf->Cell(150,4,$linha1,0,1,"L",'0');  

        $linha2 = $numantes . "." . $dados["o52_funcao"] . "." . $dados["o53_subfuncao"] . " - " . $dados["o53_descr"];
        $pdf->setX(20);
        $pdf->Cell(150,4,$linha2,0,1,"L",'0');  

        $linha3 = $dados["o54_programa"] . " - " . $dados["o54_descr"];
        $pdf->setX(20);
        $pdf->Cell(150,4,$linha3,0,1,"L",'0');  
        $pdf->setX(20);     
        $pdf->Cell(150,4,"$o58_projativ - $o55_descr",0,1,"L",'0');  
        $pdf->setX(20);   
        $pdf->Cell(150,4,db_formatar($o58_elemento,'elemento')." - ".$o56_descr,0,1,"L",'0');     
        $pdf->setX(20);         
        $xdesc = buscaDescricao(db_formatar($o58_codigo,'recurso'));
        $pdf->Cell(120,4,db_formatar($o58_codigo,'recurso')." - ".trim($xdesc)." ( $o47_coddot ) ",0,0,"L",'0'); 
        //$pdf->Cell(120,4,db_formatar($o58_codigo,'recurso')." - ".trim($o15_descr)." ( $o47_coddot ) ",0,0,"L",'0'); 
        //REPETIÇÃO
        
        $o47_valor =$o47_valor*-1;
        $pdf->Cell(50,4,db_formatar($o47_valor,'f'),0,1,"R",'0');  
        $pdf->setX(20);  
        $total += $o47_valor;
        $pdf->Ln();

      } else {
        $pdf->setX(20);
      $pdf->Cell(150,4,db_formatar($o58_orgao,'orgao')."00 - $o40_descr",0,1,"L",'0');  
      $pdf->setX(20);    
      $pdf->Cell(150,4,db_formatar($o58_orgao,'orgao').db_formatar($o58_unidade,'orgao')." - $o41_descr",0,1,"L",'0');  
      $pdf->setX(20);     
      $pdf->Cell(150,4,"$o58_projativ - $o55_descr",0,1,"L",'0');  
      $pdf->setX(20);   
      $pdf->Cell(150,4,db_formatar($o58_elemento,'elemento')." - ".$o56_descr,0,1,"L",'0');     
      $pdf->setX(20);         
      $pdf->Cell(120,4,db_formatar($o58_codigo,'recurso')." - ".trim($o15_descr)." ( $o47_coddot ) ",0,0,"L",'0');  
      $o47_valor =$o47_valor*-1;
      $pdf->Cell(50,4,db_formatar($o47_valor,'f'),0,1,"R",'0');  
      $pdf->setX(20);  
      $total += $o47_valor;
      $pdf->Ln();
      }
          
    }//if
  }//for
 
  }  
  /// arrecadacao a maior, lista receitas
  $sql = "select 
              o39_codproj,
              o46_codsup,
              o46_tiposup,
              o48_descr,
                o57_descr,
              o85_codrec,
              o85_anousu,
          o85_valor
         from orcprojeto
              inner join orcsuplem on o46_codlei = orcprojeto.o39_codproj
              inner join orcsuplemrec on o85_codsup = orcsuplem.o46_codsup 
          inner join orcreceita   on o70_codrec = orcsuplemrec.o85_codrec
                                 and o70_anousu = orcsuplemrec.o85_anousu
              inner join orcfontes on o57_codfon  =   orcreceita.o70_codfon and o57_anousu = orcsuplemrec.o85_anousu                     
              inner join orcsuplemtipo on o48_tiposup = orcsuplem.o46_tiposup
                                      and orcsuplemtipo.o48_arrecadmaior >  0
          where o39_codproj=$projeto
         ";
          
   $sSqlPPA = "select 
              o39_codproj,
              o46_codsup,
              o46_tiposup,
              o48_descr,
              o57_descr,
              0 as o85_codrec,
              o06_anousu,
              o137_valor
         from orcprojeto
              inner join orcsuplem on o46_codlei = orcprojeto.o39_codproj
              inner join orcsuplemreceitappa  on o137_orcsuplem = orcsuplem.o46_codsup 
              inner join ppaestimativareceita on o137_ppaestimativareceita = o06_sequencial
              inner join orcfontes on o57_codfon  =   o06_codrec and o57_anousu = o06_anousu             
              inner join orcsuplemtipo on o48_tiposup = orcsuplem.o46_tiposup
                                      and orcsuplemtipo.o48_arrecadmaior >  0
          where o39_codproj=$projeto
         ";
   $res= $auxiliar->sql_record($sql." union all {$sSqlPPA}"); 
   
   if ($auxiliar->numrows > 0 ) {
       
       for ($x=0;$x < $auxiliar->numrows ;$x++){
           db_fieldsmemory($res,$x);
           $pdf->setX(20);
         $pdf->Cell(120,4,"$o85_codrec - $o57_descr (arrecadação à maior)",0,0,"L",'0');  
           
         $pdf->Cell(50,4,db_formatar($o85_valor,'f'),0,1,"R",'0');  
         $total += $o85_valor;
           $pdf->setX(20);   
           $pdf->Ln();
       }      
   }
   if($tem_reduz == 1){
      
      $pdf->Cell(130,4,'',0,0,"L",'0');  
      $pdf->Cell(50,4,db_formatar($total,'f'),"T",1,"R",'0');  
      $pdf->setX(20);   
   }
   
   /*$pdf->Ln(7);   
   $artigo = 2;
   $artigo = $artigo +1;
   $txt="Art $artigo. - Revogam-se as disposições em contrário.";
   //$txt="Art. 3º - Este Decreto entrará em vigor na data de sua publicação.";
   $pdf->setX(40);   
   $pdf->multicell(170,4,$txt,'0','J','0',20);
   */
 
   $pdf->Ln(7); 
   $artigo = $artigo +2;
   $txt="Art $artigo. - Est".($projeto_tipo == 1?'e DECRETO':'a lei')." entrará em vigor na data de sua publicação.";
   $pdf->setX(40);   
   $pdf->multicell(170,4,$txt,'0','J','0',20);
 
   if ($projeto_tipo == "1" && strtoupper(trim($munic)) == "VOLTA REDONDA"){  

      $sec =  "";
      $ass_sec = $classinatura->assinatura(1006,$sec);      
      $pdf->Ln(6);          
      $txt = "Palácio 17 de Julho ".", ".substr($xdata,8,2)." de ".strtoupper(db_mes(substr($xdata,5,2)))." de ".substr($xdata,0,4).".";
      $pdf->setX(50);
      $pdf->multicell(120,4,$txt,'0','J','0',20);
      $pdf->Ln(2);    
      $pdf->multicell(0,4,"Antônio Francisco Neto"."\n"."Prefeito Municipal",'0','C','0');
      
   }else if ($projeto_tipo == "1" && strtoupper(trim($munic)) == "BAGE"){   
      $pdf->Ln(10);
      $artigo = $artigo +1;
      $txt="Art $artigo. - Este Decreto entrara em vigor na data de sua publicação.";
      $artigo += 1;
      $pdf->setX(40);
      $pdf->multicell(170,4,$txt,'0','J','0',20);

      $sec =  "";
      $ass_sec = $classinatura->assinatura(1002,$sec);

      $pdf->Ln(5);    
      $txt = "GABINETE DO PREFEITO MUNICIPAL DE ".strtoupper($munic).", ".substr($xdata,8,2)." DE ".strtoupper(db_mes(substr($xdata,5,2)))." DE ".substr($xdata,0,4).".";
      $pdf->cell(30,4,'','0','J','0');
      $pdf->multicell(180,4,$txt,'0','J','0');
      $pdf->Ln(10);    
      $pdf->multicell(0,4,$pref."\n"."PREFEITO MUNICIPAL",'0','C','0');  
      $pdf->multicell(0,3,"\n\n\n".strtoupper($ass_sec),'0','L','0');
      $pdf->Ln(10);    
      $pdf->multicell(0,4,"Registre-se e cumpra-se",'0','L','0');
   }else if ($projeto_tipo == "1" && strtoupper(trim($munic)) == "ARROIO DO SAL"){

      $sec =  "";
      $ass_sec = $classinatura->assinatura(1002,$sec);

      $pdf->Ln(5);    
      $txt = "GABINETE DO PREFEITO MUNICIPAL DE ".strtoupper($munic).", ".substr($xdata,8,2)." DE ".strtoupper(db_mes(substr($xdata,5,2)))." DE ".substr($xdata,0,4).".";
      $pdf->cell(30,4,'','0','J','0');
      $pdf->multicell(180,4,$txt,'0','J','0');
      $pdf->Ln(10);    
      $pdf->multicell(0,4,$pref."\n"."PREFEITO MUNICIPAL ",'0','C','0');
      $pdf->multicell(0,3,"\n\n\n".strtoupper($ass_sec),'0','L','0');

   }elseif ($projeto_tipo == "1" && strtoupper(trim($munic)) == "ELDORADO DO SUL"){

      $faz = "";
      $adm = "";
      $ass_faz = $classinatura->assinatura(1002,$faz);
      $ass_adm = $classinatura->assinatura(1003,$adm);

      $pdf->Ln(5);    
      if ( $db21_codcli == 34 ) {
        $txt = "GABINETE DO PRESIDENTE DA CAMARA MUNICIPAL DE VEREADORES DE ".strtoupper($munic)." AOS ".substr($xdata,8,2)." DIAS DO MÊS DE ".strtoupper(db_mes(substr($xdata,5,2)))." DE ".substr($xdata,0,4).".";
      } else {
        $txt = "GABINETE DO PREFEITO MUNICIPAL DE ".strtoupper($munic)." AOS ".substr($xdata,8,2)." DIAS DO MÊS DE ".strtoupper(db_mes(substr($xdata,5,2)))." DE ".substr($xdata,0,4).".";
      }
      $pdf->multicell(180,4,$txt,'0','J','0',10);
      $pdf->Ln(5);    
      if ($pdf->gety() > $pdf->h - 60 ){
        $pdf->addpage();
      }
      $pdf->multicell(180,4,"REGISTRE-SE E PUBLIQUE-SE:",'0','L','0',10);
      $pdf->Ln(10);   
      $pdf->setx(30);       
      if ( $db21_codcli == 34 ) {
        $pdf->multicell(0,4,$pref."\n"."Presidente da Camara Municipal de Vereadores",'0','C','0');
      } else {
        $pdf->multicell(160,4,$pref."\n"."Prefeito Municipal ",'0','C','0');
      }     
      $linha = $pdf->gety();
      $pdf->multicell(100,4,"\n\n".ucfirst($ass_adm),'0','C','0');
      $pdf->sety($linha);       
      $pdf->setx(100);       
      $pdf->multicell(100,4,"\n\n".ucfirst($ass_faz),'0','C','0');
   }

 $pdf->ln();   
 //die("Confere");
 $pdf->Output();
?>
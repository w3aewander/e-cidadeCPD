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

function testa($var){
  echo "<pre>";
  print_r($var);
  echo "</pre>";
}


function buscaDados(){
  $inst = db_getsession("DB_instit");
  $ano = db_getsession("DB_anousu");
  $sql = pg_query("SELECT distinct empempenho.e60_numemp, empempenho.e60_codemp, empempenho.e60_anousu, empempenho.e60_emiss as DB_e60_emiss, cgm.z01_nome, cgm.z01_cgccpf, empempenho.e60_coddot, e60_vlremp, e60_vlrliq, e60_vlrpag, e60_vlranu from empempenho inner join cgm on cgm.z01_numcgm = empempenho.e60_numcgm inner join db_config on db_config.codigo = empempenho.e60_instit inner join orcdotacao on orcdotacao.o58_anousu = empempenho.e60_anousu and orcdotacao.o58_coddot = empempenho.e60_coddot inner join pctipocompra on pctipocompra.pc50_codcom = empempenho.e60_codcom inner join emptipo on emptipo.e41_codtipo = empempenho.e60_codtipo inner join concarpeculiar on concarpeculiar.c58_sequencial = empempenho.e60_concarpeculiar inner join db_config as a on a.codigo = orcdotacao.o58_instit inner join orctiporec on orctiporec.o15_codigo = orcdotacao.o58_codigo inner join orcfuncao on orcfuncao.o52_funcao = orcdotacao.o58_funcao inner join orcsubfuncao on orcsubfuncao.o53_subfuncao = orcdotacao.o58_subfuncao inner join orcprograma on orcprograma.o54_anousu = orcdotacao.o58_anousu and orcprograma.o54_programa = orcdotacao.o58_programa inner join orcelemento on orcelemento.o56_codele = orcdotacao.o58_codele and orcelemento.o56_anousu = orcdotacao.o58_anousu inner join orcprojativ on orcprojativ.o55_anousu = orcdotacao.o58_anousu and orcprojativ.o55_projativ = orcdotacao.o58_projativ inner join orcorgao on orcorgao.o40_anousu = orcdotacao.o58_anousu and orcorgao.o40_orgao = orcdotacao.o58_orgao inner join orcunidade on orcunidade.o41_anousu = orcdotacao.o58_anousu and orcunidade.o41_orgao = orcdotacao.o58_orgao and orcunidade.o41_unidade = orcdotacao.o58_unidade where e60_instit = {$inst} and e60_anousu = {$ano} AND empempenho.e60_numemp NOT IN(SELECT seqempenho FROM empenhoauxsigfis WHERE seqempenho = empempenho.e60_numemp AND folhadiaria != 'sim') order by e60_numemp");
  $resultado = pg_fetch_all($sql);
  return $resultado;
}

function buscaDados50($unidade){
  $inst = db_getsession("DB_instit");
  $ano = db_getsession("DB_anousu");
  if($unidade == "todos"){
    $sql = pg_query("SELECT distinct empempenho.e60_numemp, empempenho.e60_codemp, empempenho.e60_anousu, empempenho.e60_emiss as DB_e60_emiss, cgm.z01_nome, cgm.z01_cgccpf, empempenho.e60_coddot, e60_vlremp, e60_vlrliq, e60_vlrpag, e60_vlranu, o41_unidade from empempenho inner join cgm on cgm.z01_numcgm = empempenho.e60_numcgm inner join db_config on db_config.codigo = empempenho.e60_instit inner join orcdotacao on orcdotacao.o58_anousu = empempenho.e60_anousu and orcdotacao.o58_coddot = empempenho.e60_coddot inner join pctipocompra on pctipocompra.pc50_codcom = empempenho.e60_codcom inner join emptipo on emptipo.e41_codtipo = empempenho.e60_codtipo inner join concarpeculiar on concarpeculiar.c58_sequencial = empempenho.e60_concarpeculiar inner join db_config as a on a.codigo = orcdotacao.o58_instit inner join orctiporec on orctiporec.o15_codigo = orcdotacao.o58_codigo inner join orcfuncao on orcfuncao.o52_funcao = orcdotacao.o58_funcao inner join orcsubfuncao on orcsubfuncao.o53_subfuncao = orcdotacao.o58_subfuncao inner join orcprograma on orcprograma.o54_anousu = orcdotacao.o58_anousu and orcprograma.o54_programa = orcdotacao.o58_programa inner join orcelemento on orcelemento.o56_codele = orcdotacao.o58_codele and orcelemento.o56_anousu = orcdotacao.o58_anousu inner join orcprojativ on orcprojativ.o55_anousu = orcdotacao.o58_anousu and orcprojativ.o55_projativ = orcdotacao.o58_projativ inner join orcorgao on orcorgao.o40_anousu = orcdotacao.o58_anousu and orcorgao.o40_orgao = orcdotacao.o58_orgao inner join orcunidade on orcunidade.o41_anousu = orcdotacao.o58_anousu and orcunidade.o41_orgao = orcdotacao.o58_orgao and orcunidade.o41_unidade = orcdotacao.o58_unidade where e60_instit = {$inst} and e60_anousu = {$ano} AND empempenho.e60_numemp NOT IN(SELECT seqempenho FROM empenhoauxsigfis WHERE seqempenho = empempenho.e60_numemp AND folhadiaria != 'sim') order by e60_numemp");  
  }else{
    $sql = pg_query("SELECT distinct empempenho.e60_numemp, empempenho.e60_codemp, empempenho.e60_anousu, empempenho.e60_emiss as DB_e60_emiss, cgm.z01_nome, cgm.z01_cgccpf, empempenho.e60_coddot, e60_vlremp, e60_vlrliq, e60_vlrpag, e60_vlranu, o41_unidade from empempenho inner join cgm on cgm.z01_numcgm = empempenho.e60_numcgm inner join db_config on db_config.codigo = empempenho.e60_instit inner join orcdotacao on orcdotacao.o58_anousu = empempenho.e60_anousu and orcdotacao.o58_coddot = empempenho.e60_coddot inner join pctipocompra on pctipocompra.pc50_codcom = empempenho.e60_codcom inner join emptipo on emptipo.e41_codtipo = empempenho.e60_codtipo inner join concarpeculiar on concarpeculiar.c58_sequencial = empempenho.e60_concarpeculiar inner join db_config as a on a.codigo = orcdotacao.o58_instit inner join orctiporec on orctiporec.o15_codigo = orcdotacao.o58_codigo inner join orcfuncao on orcfuncao.o52_funcao = orcdotacao.o58_funcao inner join orcsubfuncao on orcsubfuncao.o53_subfuncao = orcdotacao.o58_subfuncao inner join orcprograma on orcprograma.o54_anousu = orcdotacao.o58_anousu and orcprograma.o54_programa = orcdotacao.o58_programa inner join orcelemento on orcelemento.o56_codele = orcdotacao.o58_codele and orcelemento.o56_anousu = orcdotacao.o58_anousu inner join orcprojativ on orcprojativ.o55_anousu = orcdotacao.o58_anousu and orcprojativ.o55_projativ = orcdotacao.o58_projativ inner join orcorgao on orcorgao.o40_anousu = orcdotacao.o58_anousu and orcorgao.o40_orgao = orcdotacao.o58_orgao inner join orcunidade on orcunidade.o41_anousu = orcdotacao.o58_anousu and orcunidade.o41_orgao = orcdotacao.o58_orgao and orcunidade.o41_unidade = orcdotacao.o58_unidade where e60_instit = {$inst} and e60_anousu = {$ano} AND o41_unidade = {$unidade} AND empempenho.e60_numemp NOT IN(SELECT seqempenho FROM empenhoauxsigfis WHERE seqempenho = empempenho.e60_numemp AND folhadiaria != 'sim') order by e60_numemp");
    
  }
  $resultado = pg_fetch_all($sql);
  return $resultado;  
}

function verificaDiaria($seqempenho){
  $sql2 = pg_query("SELECT * FROM empenhoauxsigfis WHERE seqempenho = {$seqempenho}");
  $resultado2 = pg_fetch_all($sql2);
  if($resultado2){
    return $resultado2;
  }

  $sql1 = pg_query("SELECT e61_autori FROM empempaut WHERE e61_numemp = {$seqempenho}");
  $autorizacao = pg_fetch_all($sql1);
  $autorizacao = $autorizacao[0]["e61_autori"];
  
  $sql = pg_query("SELECT * FROM empenhoauxsigfis WHERE noautorizacaoempenho = {$autorizacao} AND folhadiaria = 'sim'");
  $resultado = pg_fetch_all($sql);
  return $resultado;
}

function verificaAutorizacao($seqempenho){
  $sql1 = pg_query("SELECT e61_autori FROM empempaut WHERE e61_numemp = {$seqempenho}");
  $autorizacao = pg_fetch_all($sql1);
  $autorizacao = $autorizacao[0]["e61_autori"];

  $sql = pg_query("SELECT * FROM empenhoauxsigfis WHERE noautorizacaoempenho = {$autorizacao}");
  $resultado = pg_fetch_all($sql);
  return $resultado[0];    
}


function verificaDadados($seqempenho){
  $sql2 = pg_query("SELECT * FROM empenhoauxsigfis WHERE seqempenho = {$seqempenho}");
  $resultado2 = pg_fetch_all($sql2);
  if($resultado2){
    return $resultado2[0];
  }

  $sql1 = pg_query("SELECT e61_autori FROM empempaut WHERE e61_numemp = {$seqempenho}");
  $autorizacao = pg_fetch_all($sql1);
  $autorizacao = $autorizacao[0]["e61_autori"];
  
  $sql = pg_query("SELECT * FROM empenhoauxsigfis WHERE noautorizacaoempenho = {$autorizacao}");
  $resultado = pg_fetch_all($sql);
  return $resultado[0];
}

function buscaCPFporCGM($cgm){
  $sql = pg_query("SELECT z01_cgccpf FROM cgm WHERE z01_numcgm = {$cgm}");
  $resultado = pg_fetch_all($sql);
  return $resultado[0]["z01_cgccpf"];
}

function mascaraCpf($cpf){
        return substr($cpf, 0, 3)
            . '.'
            . substr($cpf, 3, 3)
            . '.'
            . substr($cpf, 6, 3)
            . '-'
            . substr($cpf, 9, 2);
    }

function mascaraCnpj($cnpj) {
        return substr($cnpj, 0, 2)
            . '.'
            . substr($cnpj, 2, 3)
            . '.'
            . substr($cnpj, 5, 3)
            . '/'
            . substr($cnpj, 8, 4)
            . '-'
            . substr($cnpj, 12, 2);
    }








if(db_getsession("DB_instit") == 50){
  $uni = $_GET["unidade"];
  $dados = buscaDados50($uni);  
  function sortByOrder($a, $b) {
    if ($a['o41_unidade'] > $b['o41_unidade']) {
        return 1;
    } elseif ($a['o41_unidade'] < $b['o41_unidade']) {
        return -1;
    }
    return 0;
  }
  usort($dados, 'sortByOrder');
}else{
  $dados = buscaDados();
}
//testa($dados);die("Confere");
//[o41_unidade] => 1
//[o41_unidade] => 3

$tipo = $_GET["tipo"];


if($tipo == "pdf"){
  $head3 = "EMPENHOS SEM DADOS DO SIGFIS";
  $head4 = "DATA DA CONSULTA: " . date("d/m/Y");

  if(db_getsession("DB_instit") == 50){
    if($unidade == 1){
      $head5 = "UNIDADE: FUNDO MUNICIPAL DE SAÚDE";
    }
    if($unidade == 3){
      $head5 = "UNIDADE: SERVIÇO AUTÔNOMO HOSPITALAR";
    }
    if($unidade == "todos"){
      $head5 = "TODAS UNIDADES";
    }
  }

  $pdf = new PDF();
  $pdf->Open();
  $pdf->AliasNbPages();  
  $pdf->setfillcolor(235);  
  $alt = 4;
  
  $contador = 0;
  foreach($dados as $linha){
    
      $verifica = verificaDiaria($linha["e60_numemp"]);      
      if($verifica){continue;}

      $verificadados = verificaDadados($linha["e60_numemp"]);
      if(($verificadados["jaip"] == 0 && $verificadados["tipro"] == 0) || ($verificadados["jaaj"] == 0 && $verificadados["tajuo"] == 0)){
        //mostra
      }else{
        continue;
      }

      //$verificaAut = verificaAutorizacao($linha["e60_numemp"]);
      //if($verificaAut){continue;}
    
    if($contador % 35 == 0){
      $pdf->addpage("L");    
      $pdf->setfont('arial','b',8);
      $pdf->cell(20,$alt,"Seq. Empenho",1,0,"C",1);
      $pdf->cell(25,$alt,"Nº do Empenho",1,0,"C",1);
      $pdf->cell(90,$alt,"Nome/Razão Social",1,0,"C",1);
      $pdf->cell(30,$alt,"CNPJ/CPF",1,0,"C",1);
      $pdf->cell(15,$alt,"Dotação",1,0,"C",1);
      $pdf->cell(25,$alt,"Valor Empenho",1,0,"C",1);
      $pdf->cell(25,$alt,"Valor Liquidado",1,0,"C",1);
      $pdf->cell(25,$alt,"Valor Pago",1,0,"C",1);
      $pdf->cell(25,$alt,"Valor Anulado",1,1,"C",1);
    }
    
    $pdf->cell(20,$alt,$linha["e60_numemp"],1,0,"C",0);
    $pdf->cell(25,$alt,$linha["e60_codemp"]."/".$linha["e60_anousu"],1,0,"C",0);
    $pdf->cell(90,$alt,$linha["z01_nome"],1,0,"L",0);
    $pdf->cell(30,$alt,$linha["z01_cgccpf"],1,0,"C",0);
    $pdf->cell(15,$alt,$linha["e60_coddot"],1,0,"C",0);
    $pdf->cell(25,$alt, number_format($linha["e60_vlremp"], 2, ",", "."),1,0,"C",0);
    $pdf->cell(25,$alt, number_format($linha["e60_vlrliq"], 2, ",", "."),1,0,"C",0);
    $pdf->cell(25,$alt, number_format($linha["e60_vlrpag"], 2, ",", "."),1,0,"C",0);
    $pdf->cell(25,$alt, number_format($linha["e60_vlranu"], 2, ",", "."),1,1,"C",0);
    $contador++;
  }  
  
  $pdf->Output();  
}elseif($tipo == "especial"){
  $ajustado = array();
  $cabecalho = "SEQ. EMPENHO;Nº DO EMPENHO;ANO EMPENHO;DATA EMISSÃO;NOME/RAZÃO SOCIAL;CNPJ/CPF;DOTAÇÃO;VALOR EMPENHO;VALOR LIQUIDADO;VALOR PAGO;VALOR ANULADO;JUSTIFICATIVA DA AUSÊNCIA DE INSTRUMENTO PRÉVIO;JUSTIFICATIVA DA AUSÊNCIA DE ATO JURÍDICO;CPF ORDENADOR;TIPO DE INSTRUMENTO;Nº DE INSTRUMENTO;TIPO DE ATO;Nº DO ATO;UNIDADE GESTORA";  
  array_push($ajustado, $cabecalho);
  
  foreach ($dados as $linha){
    $cpf = "";
    if(strlen($linha["z01_cgccpf"]) == 11){$cpf = mascaraCpf($linha["z01_cgccpf"]);}else{$cpf = mascaraCnpj($linha["z01_cgccpf"]);}    
    
    $xdados = verificaDadados($linha["e60_numemp"]);
    $cpfordenador =  buscaCPFporCGM($xdados["cgmordenador"]);
    if(strlen($cpfordenador) == 11){$cpfordenador = mascaraCpf($cpfordenador);}else{$cpfordenador = mascaraCnpj($cpfordenador);}

    
    $l1 = $linha["e60_numemp"];
    $l2 = $linha["e60_codemp"];
    $l3 = $linha["e60_anousu"];
    $l4 = implode("/", array_reverse(explode("-", $linha["db_e60_emiss"])));
    $l5 = $linha["z01_nome"];
    $l6 = $cpf;
    $l7 = $linha["e60_coddot"];
    $l8 = number_format($linha["e60_vlremp"], 2, ",", ".");
    $l9 = number_format($linha["e60_vlrliq"], 2, ",", ".");
    $l10 = number_format($linha["e60_vlrpag"], 2, ",", ".");
    $l11 = number_format($linha["e60_vlranu"], 2, ",", ".");
    $l12 = $xdados["jaip"];
    $l13 = $xdados["jaaj"];
    $l14 = $cpfordenador;
    $l15 = $xdados["tipro"];
    $l16 = $xdados["noinpr"];
    $l17 = $xdados["tajuo"];
    $l18 = $xdados["noatoju"];
    $l19 = $xdados["ugaj"];
      
    $linhazona = $l1 .";". $l2 .";". $l3 .";". $l4 .";". $l5.";".$l6.";".$l7.";".$l8.";".$l9.";".$l10.";".$l11.";".$l12.";".$l13.";".$l14.";".$l15.";".$l16.";".$l17.";".$l18.";".$l19;

    array_push($ajustado, $linhazona);
  }
  $arquivo = fopen("auxsigfis2024.csv", "w");
  foreach ($ajustado as $linha) {
    fwrite($arquivo, $linha . "\n");
  }
  fclose($arquivo);

  $file_url = 'auxsigfis2024.csv';
  header('Content-Type: application/octet-stream');
  header("Content-Transfer-Encoding: Binary"); 
  header("Content-disposition: attachment; filename=\"" . basename($file_url) . "\""); 
  readfile($file_url); 
  unlink($file_url);
  exit();

  
  
}else{

  $ajustado = array();

  if(db_getsession("DB_instit") == 50){
    $cabecalho = "SEQ. EMPENHO;Nº DO EMPENHO;NOME/RAZÃO SOCIAL;CNPJ/CPF;DOTAÇÃO;DATA EMISSÃO;UNIDADE ORÇAMENTÁRIA;ACOMPANHAMENTO DA EXECUÇÃO ORÇAMENTÁRIA;JUSTIFICATIVA DA AUSÊNCIA DE INSTRUMENTO PRÉVIO;JUSTIFICATIVA DA AUSÊNCIA DE ATO JURÍDICO;CPF ORDENADOR;TIPO DE INSTRUMENTO;Nº DE INSTRUMENTO;TIPO DE ATO;Nº DO ATO;UNIDADE GESTORA";
  }else{
    $cabecalho = "SEQ. EMPENHO;Nº DO EMPENHO;NOME/RAZÃO SOCIAL;CNPJ/CPF;DOTAÇÃO;DATA EMISSÃO;ACOMPANHAMENTO DA EXECUÇÃO ORÇAMENTÁRIA;JUSTIFICATIVA DA AUSÊNCIA DE INSTRUMENTO PRÉVIO;JUSTIFICATIVA DA AUSÊNCIA DE ATO JURÍDICO;CPF ORDENADOR;TIPO DE INSTRUMENTO;Nº DE INSTRUMENTO;TIPO DE ATO;Nº DO ATO;UNIDADE GESTORA";  
  }
  
  

  array_push($ajustado, $cabecalho);

  foreach ($dados as $linha){    
    $verifica = verificaDiaria($linha["e60_numemp"]);
    if($verifica){continue;}
    //$verificaAut = verificaAutorizacao($linha["e60_numemp"]);
    //if($verificaAut){continue;}

    $verificadados = verificaDadados($linha["e60_numemp"]);
      if(($verificadados["jaip"] == 0 && $verificadados["tipro"] == 0) || ($verificadados["jaaj"] == 0 && $verificadados["tajuo"] == 0)){
        //mostra
      }else{
        continue;
      }
    
    $cpf = "";
    if(strlen($linha["z01_cgccpf"]) == 11){
      $cpf = mascaraCpf($linha["z01_cgccpf"]);
    }else{
      $cpf = mascaraCnpj($linha["z01_cgccpf"]);
    }
    if(db_getsession("DB_instit") == 50){
      $l1 = $linha["e60_numemp"];
      $l2 = $linha["e60_codemp"];
      $l3 = $linha["z01_nome"];    
      $l4 = $cpf;
      $l5 = $linha["e60_coddot"];
      $l6 = implode("/", array_reverse(explode("-", $linha["db_e60_emiss"])));    
      $l7 = ($linha["o41_unidade"] == 1) ? "Fundo Municipal de Saúde" : "Serviço Autônomo Hospitalar";
      $l8 = "";
      $l9 = "";
      $l10 = "";
      $l11 = "";
      $l12 = "";
      $l13 = "";
      $l14 = "";
      $l15 = "";
      $l16 = "";
      $linhazona = $l1 .";". $l2 .";". $l3 .";". $l4 .";". $l5.";".$l6.";".$l7.";".$l8.";".$l9.";".$l10.";".$l11.";".$l12.";".$l13.";".$l14.";".$l15.";".$l16;

    }else{
      $l1 = $linha["e60_numemp"];
      $l2 = $linha["e60_codemp"];
      $l3 = $linha["z01_nome"];    
      $l4 = $cpf;
      $l5 = $linha["e60_coddot"];
      $l6 = implode("/", array_reverse(explode("-", $linha["db_e60_emiss"])));    
      $l7 = "";
      $l8 = "";
      $l9 = "";
      $l10 = "";
      $l11 = "";
      $l12 = "";
      $l13 = "";
      $l14 = "";
      $l15 = "";
      $linhazona = $l1 .";". $l2 .";". $l3 .";". $l4 .";". $l5.";".$l6.";".$l7.";".$l8.";".$l9.";".$l10.";".$l11.";".$l12.";".$l13.";".$l14.";".$l15;  
    }
    
    array_push($ajustado, $linhazona);
  }
  
  $arquivo = fopen("auxsigfis.csv", "w");
  foreach ($ajustado as $linha) {
    fwrite($arquivo, $linha . "\n");
  }
  fclose($arquivo);

  $file_url = 'auxsigfis.csv';
  header('Content-Type: application/octet-stream');
  header("Content-Transfer-Encoding: Binary"); 
  header("Content-disposition: attachment; filename=\"" . basename($file_url) . "\""); 
  readfile($file_url); 
  unlink($file_url);
  exit();

}

?>
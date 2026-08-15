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
require_once(modification("fpdf151/pdfwebseller.php"));
require_once(modification("libs/db_utils.php"));
require_once(modification("libs/db_app.utils.php"));
require_once(modification("libs/db_stdlibwebseller.php"));
require_once(modification("libs/db_libdocumento.php"));
require_once(modification("dbforms/db_funcoes.php"));

function testa($var){
  echo "<pre>";
  print_r($var);
  echo "</pre>";
}

function buscaNomeMatricula($codtrans){
  $sql = pg_query("SELECT ed47_i_codigo, ed47_v_nome FROM aluno INNER JOIN matricula ON ed60_i_aluno = ed47_i_codigo INNER JOIN transfescolarede ON ed103_i_matricula = ed60_i_codigo WHERE ed103_i_codigo = {$codtrans}");
  $resultado = pg_fetch_all($sql);
  return $resultado;
}

function buscaNomeMatriculaFora($codtrans){
  $sql = pg_query("SELECT ed47_i_codigo, ed47_v_nome FROM aluno INNER JOIN matricula ON ed60_i_aluno = ed47_i_codigo INNER JOIN transfescolafora ON ed104_i_matricula = ed60_i_codigo WHERE ed104_i_codigo = {$codtrans}");
  $resultado = pg_fetch_all($sql);
  return $resultado;
}



function retornaCodDiario($ed60_i_codigo, $ed60_i_aluno, $ed11_i_codigo, $ed60_i_turma){  
  $sql = pg_query("SELECT diario.*, ed59_i_codigo from diario inner join aluno on ed47_i_codigo = ed95_i_aluno inner join matricula on ed60_i_aluno = ed47_i_codigo inner join matriculaserie on ed60_i_codigo = ed221_i_matricula inner join regencia on ed59_i_codigo = ed95_i_regencia and ed59_i_serie = ed221_i_serie where ed60_i_codigo = {$ed60_i_codigo} and ed95_i_aluno = {$ed60_i_aluno} and ed95_i_regencia = ed59_i_codigo and ed95_i_serie = {$ed11_i_codigo} and ed59_i_turma = {$ed60_i_turma} order by ed95_i_codigo");
  $resultado = pg_fetch_all($sql);
  return $resultado[0]["ed95_i_codigo"];
}

function dadosDiario($codiario){
  $sql = pg_query("SELECT ed72_i_codigo as codigo, ed72_i_procavaliacao as codigo_elemento, ed72_i_numfaltas as numero_faltas, ed80_i_codigo as codigo_faltas_abonadas, ed72_i_valornota as valor_nota, ed72_i_valornota as valor_nota_real, ed72_c_valorconceito as valor_conceito, ed72_t_parecer as parecer, ed72_c_aprovmin as minimo, ed72_c_amparo as amparo, ed41_i_sequencia as sequencia, trim(ed93_t_parecer) as parecerpadronizado, ed72_i_escola as escola, ed72_c_tipo as origem, ed72_c_convertido as convertido, (select ed39_i_sequencia from conceito where conceito.ed39_i_formaavaliacao = ed41_i_formaavaliacao and conceito.ed39_c_conceito = ed72_c_valorconceito) as ordem_conceito, 'A' as tipo_elemento, ed72_t_obs as observacao, false as em_recuperacao from diarioavaliacao inner join procavaliacao on ed41_i_codigo = ed72_i_procavaliacao left join pareceraval on ed93_i_diarioavaliacao = ed72_i_codigo left join abonofalta on ed80_i_diarioavaliacao = ed72_i_codigo where ed72_i_diario = {$codiario} order by sequencia");
  $resultado = pg_fetch_all($sql);
  return $resultado;
}

function buscaCodigoRegencia($ed57_i_codigo){
  $sql = pg_query("SELECT ed59_i_codigo, ed59_i_turma, ed59_i_disciplina FROM regencia WHERE ed59_i_turma = {$ed57_i_codigo}");
  $resultado = pg_fetch_all($sql);
  return $resultado[0]["ed59_i_codigo"];
}

function dadosAulas($codregencia){
  $sql = pg_query("SELECT ed78_i_codigo, ed78_i_regencia, ed78_i_procavaliacao, ed78_i_aulasdadas from regenciaperiodo inner join procavaliacao on procavaliacao.ed41_i_codigo = regenciaperiodo.ed78_i_procavaliacao inner join regencia on regencia.ed59_i_codigo = regenciaperiodo.ed78_i_regencia inner join periodoavaliacao on periodoavaliacao.ed09_i_codigo = procavaliacao.ed41_i_periodoavaliacao inner join formaavaliacao on formaavaliacao.ed37_i_codigo = procavaliacao.ed41_i_formaavaliacao inner join procedimento on procedimento.ed40_i_codigo = procavaliacao.ed41_i_procedimento inner join disciplina on disciplina.ed12_i_codigo = regencia.ed59_i_disciplina inner join caddisciplina on ed232_i_codigo= ed12_i_caddisciplina inner join turma on turma.ed57_i_codigo = regencia.ed59_i_turma where ed78_i_regencia = {$codregencia} and ed09_c_somach = 'S' ORDER BY ed78_i_procavaliacao");
  $resultado = pg_fetch_all($sql);
  return $resultado;
}

function buscaCidade($codigo){
  $sql = pg_query("SELECT ed261_c_nome FROM censomunic WHERE ed261_i_codigo = {$codigo}");
  $resultado = pg_fetch_all($sql);
  return trim($resultado[0]["ed261_c_nome"]);
}

//testa($_GET); die("Confere");

$cltransfescolarede       = new cl_transfescolarede;
$cltransfescolafora       = new cl_transfescolafora;
$clescoladiretor          = new cl_escoladiretor;
$cldiarioavaliacao        = new cl_diarioavaliacao;
$clprocavaliacao          = new cl_procavaliacao;
$clmatricula              = new cl_matricula;
$clprogressaoparcialaluno = new cl_progressaoparcialaluno();
$escola                   = db_getsession("DB_coddepto");
$resultedu                = eduparametros(db_getsession("DB_coddepto"));

if($_GET["tipo"] == "TF"){
  $nomeematricula = buscaNomeMatriculaFora($_GET["alunos"]);  
}else{
  $nomeematricula = buscaNomeMatricula($_GET["alunos"]);
}

//$diretor = "DIRETOR|VIVIANE DA SILVA GOMES PIRES|PORTARIA n°: 1";
$diretor = "";
if ($diretor != "") {

  $arr_assinatura = explode("|",$diretor);
  $nome           = $arr_assinatura[1];
  $atividade      = $arr_assinatura[0]." desta escola,";
  //$funcao         = $arr_assinatura[0].(trim($arr_assinatura[2])!=""?" ($arr_assinatura[2])":"");
  $funcao         = $arr_assinatura[0];
} else {

  //$nome      = "......................................................................................";
  $nome = "Assinatura do Responsável";
  $atividade = "";
  $funcao    = "";
}

if ($tipo == "TR") {

  $campos  = "ed47_c_bolsafamilia,ed47_i_codigo,ed47_v_nome,ed47_d_nasc,";
  $campos .= "censomunicnat.ed261_c_nome as ed47_i_censomunicnat,censoufnat.ed260_c_sigla as ed47_i_censoufnat,";
  $campos .= "ed47_v_pai,ed47_v_mae,transfescolarede.ed103_d_data as data_transf,serie.ed11_c_descr as descr_serie_destino,";
  $campos .= "ensino.ed10_c_descr as descr_ensino_destino,ensino.ed10_c_abrev as abrev_ensino_destino,transfescolarede.ed103_t_obs as obs_transf,";
  $campos .= "escola.ed18_c_nome as escola_origem,";
  $campos .= "matricula.ed60_i_turma as turma_origem_codigo,";
  $campos .= "censomunic.ed261_c_nome as cidade_origem,matricula.ed60_c_parecer as neeparecer,transfescolarede.ed103_i_matricula as codigomatricula,";
  
  
    $campos .= "(SELECT en.ed10_c_descr
                 FROM turma t
                 INNER JOIN base ba ON ba.ed31_i_codigo = t.ed57_i_base
                 INNER JOIN cursoedu ce ON ce.ed29_i_codigo = ba.ed31_i_curso
                 INNER JOIN ensino en ON en.ed10_i_codigo = ce.ed29_i_ensino
                 WHERE t.ed57_i_codigo = matricula.ed60_i_turma
                 LIMIT 1
                ) as descr_ensino,";


    $campos .= "(SELECT string_agg(s.ed11_c_descr, ', ')
                 FROM turma t
                 INNER JOIN turmaserieregimemat tsm ON tsm.ed220_i_turma = t.ed57_i_codigo
                 INNER JOIN serieregimemat sm ON sm.ed223_i_codigo = tsm.ed220_i_serieregimemat
                 INNER JOIN serie s ON s.ed11_i_codigo = sm.ed223_i_serie
                 WHERE t.ed57_i_codigo = matricula.ed60_i_turma
                ) as descr_serie,";
            


          
  $campos .= "calendario.ed52_i_codigo, escoladestino.ed18_c_nome as escola_destino";
  $result  = $cltransfescolarede->sql_record($cltransfescolarede->sql_query("",
  $campos,
  "to_ascii(ed47_v_nome)",
  " ed103_i_codigo in ($alunos)"
 )
);
$linhas  = $cltransfescolarede->numrows;
} else if ($tipo == "TF") {
  $campos  = " ed47_c_bolsafamilia,ed47_i_codigo,ed47_v_nome,ed47_d_nasc, ";
  $campos .= " censomunicnat.ed261_c_nome as ed47_i_censomunicnat,censoufnat.ed260_c_sigla as ed47_i_censoufnat, ";
  $campos .= " ed47_v_pai,ed47_v_mae,ed104_d_data as data_transf,ed104_t_obs as obs_transf, ";
  $campos .= " censomunic.ed261_c_nome as cidade,ed104_i_matricula as codigomatricula, ";
  $campos .= " escola.ed18_c_nome as escola_origem, ";
  $campos .= " escolaproc.ed82_c_nome as escola_destino, "; // <<< VÍRGULA CRÍTICA ADICIONADA AQUI
  
  // Subquery para buscar o TIPO DE ENSINO anterior
  $campos .= " (SELECT en.ed10_c_descr
               FROM turma t
               INNER JOIN base ba ON ba.ed31_i_codigo = t.ed57_i_base
               INNER JOIN cursoedu ce ON ce.ed29_i_codigo = ba.ed31_i_curso
               INNER JOIN ensino en ON en.ed10_i_codigo = ce.ed29_i_ensino
               WHERE t.ed57_i_codigo = matricula.ed60_i_turma
               LIMIT 1
              ) as descr_ensino,";
  
  // Subquery para buscar a ETAPA/SÉRIE anterior
  $campos .= " (SELECT string_agg(s.ed11_c_descr, ', ')
               FROM turma t
               INNER JOIN turmaserieregimemat tsm ON tsm.ed220_i_turma = t.ed57_i_codigo
               INNER JOIN serieregimemat sm ON sm.ed223_i_codigo = tsm.ed220_i_serieregimemat
               INNER JOIN serie s ON s.ed11_i_codigo = sm.ed223_i_serie
               WHERE t.ed57_i_codigo = matricula.ed60_i_turma
              ) as descr_etapa"; // Sem vírgula, pois é o último campo

  $result  = $cltransfescolafora->sql_record($cltransfescolafora->sql_query("",
                                                                            $campos,
                                                                            "to_ascii(ed47_v_nome)",
                                                                            " ed104_i_codigo in ($alunos)"
                                                                           )
                                            );
  $linhas  = $cltransfescolafora->numrows;
}

if ($linhas == 0) {?>

 <table width='100%'>
  <tr>
   <td align='center'>
    <font color='#FF0000' face='arial'>
     <b>Nenhuma registro encontrado.<br>
     <input type='button' value='Fechar' onclick='window.close()'></b>
    </font>
   </td>
  </tr>
 </table>
 <?
 exit;
}

$head1 = "GUIA DE TRANSFERÊNCIA";
$head2 = "";
$head3 = "Nome: " . trim($nomeematricula[0]["ed47_v_nome"]);
$head4 = "Matrícula: " . trim($nomeematricula[0]["ed47_i_codigo"]);
$pdf   = new PDF();
$pdf->Open();
$pdf->AliasNbPages();
$pdf->imprime_rodape = false;



for ($x=0;$x<$linhas;$x++) {

  db_fieldsmemory($result,$x);
  $pdf->addpage("P");  
  
    
  
  if ($tipo == "TF") {

  	$campos      = " ed47_i_codigo, ed47_c_bolsafamilia, serie.ed11_c_descr as descr_serie, ";
  	$campos     .= " ensino.ed10_c_descr as descr_ensino, ed10_c_abrev as abrev_ensino, ";
  	$campos     .= " matricula.ed60_i_turma as turmaorigem, turma.ed57_c_descr as descr_turma, ";
  	$campos     .= " matricula.ed60_c_parecer as neeparecer,";
  	$campos     .= " calendario.ed52_i_codigo";
    $result_matr = $clmatricula->sql_record($clmatricula->sql_query("",
                                                                    $campos,
                                                                    "",
                                                                    " ed60_i_codigo = $codigomatricula"
                                                                   )
                                                                 );
    db_fieldsmemory($result_matr,0);
  }

  $dia_nasc             = substr($ed47_d_nasc,8,2);
  $mes_nasc             = substr($ed47_d_nasc,5,2);
  $ano_nasc             = substr($ed47_d_nasc,0,4);
  $dia_transf           = substr($data_transf,8,2);
  $mes_transf           = substr($data_transf,5,2);
  $ano_transf           = substr($data_transf,0,4);
  $ed47_i_censomunicnat = $ed47_i_censomunicnat!=""?$ed47_i_censomunicnat:".........................................
                                                                           ........................";
  $ed47_i_censoufnat    = $ed47_i_censoufnat!=""?$ed47_i_censoufnat:".........";  

  $aFiliacao = array();

  if ($ed47_v_mae != '') {
    $aFiliacao[] = $ed47_v_mae;
  }
  if ($ed47_v_pai != '') {
    $aFiliacao[] = $ed47_v_pai;
  }

  $sEtapa  = $descr_serie;
  $sEnsino = $descr_ensino;
  $sEtapaDest = $descr_serie_destino;
  $sEnsinoDest = $descr_ensino_destino;


  unset($sTexto);
  $oParagrafo                         = new libdocumento(5010);

  $oParagrafo->nome_aluno             = $ed47_v_nome;
  $oParagrafo->municipio_naturalidade = $ed47_i_censomunicnat;
  $oParagrafo->estado_naturalidade    = $ed47_i_censoufnat;
  $oParagrafo->dia_nascimento         = $dia_nasc;
  $oParagrafo->mes_nascimento         = db_mes($mes_nasc,1);
  $oParagrafo->ano_nascimento         = $ano_nasc;
  $oParagrafo->filiacao               = implode(' e ', $aFiliacao);
  $oParagrafo->dia_transferencia      = $dia_transf;
  $oParagrafo->mes_transferencia      = db_mes($mes_transf,1);
  $oParagrafo->ano_transferencia      = $ano_transf;
  $oParagrafo->etapa                  = $sEtapa;
  $oParagrafo->ensino                 = $sEnsino;
  $dnascimento = $dia_nasc . "/" . $mes_nasc . "/" . $ano_nasc;
  $oDadosAlunos                       = new stdClass();
  $oDadosAlunos->aParagrafo           = $oParagrafo->getDocParagrafos();
  $sTexto = $oDadosAlunos->aParagrafo[1]->oParag->db02_texto;

  //var_dump($sEnsino, $sEtapa); die("Confere creche");
  

  if($sEnsino == "EDUCAÇÃO INFANTIL CRECHE") /*&& $sEtapa == "MATERNAL III")*/{
    $sSqlDiarioAvaliacao    = $cldiarioavaliacao->sql_query_guia("", "*", "ed41_i_sequencia ASC", "ed60_i_codigo=$codigomatricula");
    $result_diarioavaliacao = $cldiarioavaliacao->sql_record($sSqlDiarioAvaliacao);
    $zzz = pg_fetch_all($result_diarioavaliacao);
    $xed60_i_codigo = $zzz[0]["ed60_i_codigo"];
    $xed60_i_aluno = $zzz[0]["ed60_i_aluno"];
    $xed11_i_codigo = $zzz[0]["ed11_i_codigo"];
    $xed60_i_turma = $zzz[0]["ed60_i_turma"];
    $xed59_i_turma = $zzz[0]["ed59_i_turma"];
    $xcodiario = retornaCodDiario($xed60_i_codigo, $xed60_i_aluno, $xed11_i_codigo, $xed60_i_turma);
    $xdiario = dadosDiario($xcodiario);    
    $codreg = buscaCodigoRegencia($xed59_i_turma);    
    $xaulas = dadosAulas($codreg);
    

    $pdf->setfont('arial', 'b', 10);
    $pdf->multicell(192, 1,  "",                      "LRT", "C", 0, 0);
    $pdf->multicell(192, 10, "DECLARAÇÃO DE TRANSFERÊNCIA", "LR",  "C", 0, 0);
    $pdf->setfont('arial', '', 9);
    
    $nomeresponsavel = ($ed47_v_mae) ? $ed47_v_mae : $ed47_v_pai;    
    $sTexto = "Declaro para os devidos fins que o(a) aluno(a) {$ed47_v_nome}, nascido(a) em {$dnascimento}, na cidade de {$ed47_i_censomunicnat}, filho(a) de {$oParagrafo->filiacao} cursa {$sEtapa} (EDUCAÇÃO INFANTIL), nesta Unidade Escolar, no ano letivo de " . date(Y) . ".";          
    $altY   = $pdf->getY();

    $pdf->cell(6, 30, "", "L", 0, "R", 0);
    $pdf->setXY(16,$altY);
    $pdf->multicell(180, 4, $sTexto, 0, "J", 0, 0);
    
  
    $pdf->setXY(196, $altY);
    $pdf->cell(6, 30, "", "R", 1, "R", 0);

    $altY = $pdf->getY();

    $pdf->cell(6, 15, "", "L", 0, "R", 0);
    $pdf->setXY(16,$altY);

    $pdf->cell( 45, 4, "", 0, 0, "C", 0 );
    $pdf->cell( 32.5, 4, "Trimestres", "LTB", 0, "C", 0 );
    $pdf->cell( 32.5, 4, "Dias Letivos", "LTB", 0, "C", 0 );
    $pdf->cell( 25, 4, "Faltas do Aluno", "LTRB", 1, "C", 0 );

    if(count($xdiario) == 3){
      $xconta = 1;
      $xindice = 0;
      $xdl = 0;
      $xfa = 0;
      foreach ($xdiario as $linha){        
        $pdf->cell( 51, 4, "", 0, 0, "C", 0 );
        $pdf->cell( 32.5, 4, $xconta . "ª Trimestre", "LB", 0, "C", 0 );
        $pdf->cell( 32.5, 4, $xaulas[$xindice]["ed78_i_aulasdadas"], "LB", 0, "C", 0 );
        $pdf->cell( 25, 4, (isset($xdiario[$xindice]["numero_faltas"])) ? $xdiario[$xindice]["numero_faltas"] : "0", "LRB", 1, "C", 0 );
        
        $xdl += $xaulas[$xindice]["ed78_i_aulasdadas"];
        $xfa += (int)$xdiario[$xindice]["numero_faltas"];
        $xconta++;
        $xindice++;        
      }
      
      $pdf->cell( 51, 4, "", 0, 0, "C", 0 );
      $pdf->cell( 32.5, 4, "TOTAL", "LB", 0, "C", 0 );
      $pdf->cell( 32.5, 4, $xdl, "LB", 0, "C", 0 );
      $pdf->cell( 25, 4, $xfa, "LRB", 1, "C", 0 );
      $pdf->cell( 51, 4, "", 0, 0, "C", 0 );
      $xfreq = ($xdl - $xfa) / $xdl * 100;
      $pdf->cell( 65, 4, "FREQUÊNCIA % ", "LB", 0, "R", 0 );
      $pdf->cell( 25, 4, str_replace(".", ",", $xfreq), "RB", 1, "C", 0 );
    }
    
    //$pdf->setfont('arial', 'B', 9);
    $pdf->ln();
    $pdf->cell(200, 6, "O(a) aluno(a) deverá ser matriculado(a) no: {$sEtapaDest} do(a) {$sEnsinoDest}.", 0, 1, "L", 0);
    //$pdf->setfont('arial', '', 9);
    

  }elseif($sEnsino == "EDUCAÇÃO INFANTIL PRÉ-ESCOLA" /*&& $sEtapa == "1º PERÍODO"*/){     
    $regencias = array();
    $sSqlDiarioAvaliacao    = $cldiarioavaliacao->sql_query_guia("", "*", "ed41_i_sequencia ASC", "ed60_i_codigo=$codigomatricula");
    $result_diarioavaliacao = $cldiarioavaliacao->sql_record($sSqlDiarioAvaliacao);
    $zzz = pg_fetch_all($result_diarioavaliacao);    
    $xed60_i_codigo = $zzz[0]["ed60_i_codigo"];
    $xed60_i_aluno = $zzz[0]["ed60_i_aluno"];
    $xed11_i_codigo = $zzz[0]["ed11_i_codigo"];
    $xed60_i_turma = $zzz[0]["ed60_i_turma"];
    $xed59_i_turma = $zzz[0]["ed59_i_turma"];
    $xcodiario = retornaCodDiario($xed60_i_codigo, $xed60_i_aluno, $xed11_i_codigo, $xed60_i_turma);
    $xdiario = dadosDiario($xcodiario);
    //$ed57_i_codigo = $xed59_i_turma
    $codreg = buscaCodigoRegencia($xed59_i_turma);    
    $xaulas = dadosAulas($codreg);
    $pdf->setfont('arial', 'b', 10);
    $pdf->multicell(192, 1,  "",                      "LRT", "C", 0, 0);
    $pdf->multicell(192, 10, "REQUERIMENTO DE TRANSFERÊNCIA", "LR",  "C", 0, 0);
    $pdf->setfont('arial', '', 9);    
    $nomeresponsavel = ($ed47_v_mae) ? $ed47_v_mae : $ed47_v_pai;    
    $sTexto = "Eu, {$nomeresponsavel}, responsável pelo(a) aluno(a) {$ed47_v_nome} matriculado(a) no {$sEtapa}, da Educação Infantil, venho requerer sua transferência.";
    $altY   = $pdf->getY();
    $pdf->cell(6, 30, "", "L", 0, "R", 0);
    $pdf->setXY(16,$altY);
    $pdf->multicell(180, 4, $sTexto, 0, "J", 0, 0);
    $pdf->setfont('arial', 'B', 9);
    $pdf->cell(200, 6, "Declaro estar ciente de que, a partir desta data, sua matrícula será CANCELADA.", 0, 1, "C", 0);
    $pdf->setfont('arial', '', 9);
    $pdf->setXY(196, $altY);
    //$pdf->cell(6, 30, "", "R", 1, "R", 0);
    $pdf->cell(6, 20, "", "R", 1, "R", 0);
    $altY = $pdf->getY();
    $pdf->cell(6, 15, "", "L", 0, "R", 0);
    $pdf->setXY(16,$altY);
  }elseif(($sEnsino == "ENSINO FUNDAMENTAL" && ($sEtapa == "1º ANO" || $sEtapa == "2º ANO" || $sEtapa == "3º ANO" || $sEtapa == "4º ANO" || $sEtapa == "5º ANO"))){
    $pdf->setfont('arial', 'b', 10);
    $pdf->multicell(192, 1,  "",                      "LRT", "C", 0, 0);
    $pdf->multicell(192, 10, "REQUERIMENTO DE TRANSFERÊNCIA", "LR",  "C", 0, 0);
    $pdf->setfont('arial', '', 9);    
    $nomeresponsavel = ($ed47_v_mae) ? $ed47_v_mae : $ed47_v_pai;        
    $sTexto = "Eu, {$nomeresponsavel}, responsável pelo(a) aluno(a) {$ed47_v_nome} matriculado(a) no {$sEtapa}, do Ensino Fundamental Anos Iniciais, venho requerer sua transferência.";    
    $altY   = $pdf->getY();
    $pdf->cell(6, 30, "", "L", 0, "R", 0);
    $pdf->setXY(16,$altY);
    $pdf->multicell(180, 4, $sTexto, 0, "J", 0, 0);
    $pdf->setfont('arial', 'B', 9);
    $pdf->cell(200, 6, "Declaro estar ciente de que, a partir desta data, sua matrícula será CANCELADA.", 0, 1, "C", 0);
    $pdf->setfont('arial', '', 9);      
    $pdf->setXY(196, $altY);    
    $pdf->cell(6, 20, "", "R", 1, "R", 0);
    $altY = $pdf->getY();
    $pdf->cell(6, 15, "", "L", 0, "R", 0);
    $pdf->setXY(16,$altY);
  }elseif(($sEnsino == "ENSINO FUNDAMENTAL" && ($sEtapa == "6º ANO" || $sEtapa == "7º ANO" || $sEtapa == "8º ANO" || $sEtapa == "9º ANO"))){
    $pdf->setfont('arial', 'b', 10);
    $pdf->multicell(192, 1,  "",                      "LRT", "C", 0, 0);
    $pdf->multicell(192, 10, "REQUERIMENTO DE TRANSFERÊNCIA", "LR",  "C", 0, 0);
    $pdf->setfont('arial', '', 9);    
    $nomeresponsavel = ($ed47_v_mae) ? $ed47_v_mae : $ed47_v_pai;        
    $sTexto = "Eu, {$nomeresponsavel}, responsável pelo(a) aluno(a) {$ed47_v_nome} matriculado(a) no {$sEtapa}, do Ensino Fundamental Anos Finais, venho requerer sua transferência.";
    $altY   = $pdf->getY();
    $pdf->cell(6, 30, "", "L", 0, "R", 0);
    $pdf->setXY(16,$altY);
    $pdf->multicell(180, 4, $sTexto, 0, "J", 0, 0);
    $pdf->setfont('arial', 'B', 9);
    $pdf->cell(200, 6, "Declaro estar ciente de que, a partir desta data, sua matrícula será CANCELADA.", 0, 1, "C", 0);
    $pdf->setfont('arial', '', 9);
    $pdf->setXY(196, $altY);
    $pdf->cell(6, 20, "", "R", 1, "R", 0);
    $altY = $pdf->getY();
    $pdf->cell(6, 15, "", "L", 0, "R", 0);
    $pdf->setXY(16,$altY);
  }else{
    $pdf->setfont('arial', 'b', 10);
    $pdf->multicell(192, 1,  "",                      "LRT", "C", 0, 0);
    $pdf->multicell(192, 10, "REQUERIMENTO DE TRANSFERÊNCIA", "LR",  "C", 0, 0);
    $pdf->setfont('arial', '', 9);

    $altY   = $pdf->getY();
    $pdf->cell(6, 30, "", "L", 0, "R", 0);
    $pdf->setXY(16,$altY);
    $pdf->multicell(180, 4, $sTexto, 0, "J", 0, 0);    
    $pdf->setXY(196, $altY);
    $pdf->cell(6, 30, "", "R", 1, "R", 0);
    $altY = $pdf->getY();
    $pdf->cell(6, 15, "", "L", 0, "R", 0);
    $pdf->setXY(16,$altY);
  }



  

  if ($bolsafamilia == 1) {
    $bolsa = "";
  } else {

   if ($ed47_c_bolsafamilia == "S") {
     $bolsa = "Bolsa Família Ativa";
   }else{
     $bolsa = "";
   }
 }

 $veraprovnulo = "";
 $campos       = "ed43_c_minimoaprov,";
 $campos      .= "ed72_i_valornota, ";
 $campos      .= "ed72_c_valorconceito,";
 $campos      .= "ed72_t_parecer,";
 $campos      .= "ed37_c_tipo";
 $where        = "ed60_i_codigo=$codigomatricula";
 
 //$sSqlDiarioAvaliacao    = $cldiarioavaliacao->sql_query_guia("", $campos, "ed41_i_sequencia ASC", $where);
 $sSqlDiarioAvaliacao    = $cldiarioavaliacao->sql_query_guia("", "*", "ed41_i_sequencia ASC", $where);
 $result_diarioavaliacao = $cldiarioavaliacao->sql_record($sSqlDiarioAvaliacao);
 $aprov                  = null;
 

 if ($cldiarioavaliacao->numrows > 0) {

   for ($v = 0; $v < $cldiarioavaliacao->numrows; $v++) {

     db_fieldsmemory($result_diarioavaliacao,$v);
     if (trim($ed37_c_tipo) == "NOTA") {

       if ($resultedu == 'S'){
         $aproveitamento = $ed72_i_valornota != "" ? number_format($ed72_i_valornota, 2, ",", ".") : "";
       } else {
         $aproveitamento = $ed72_i_valornota != "" ? number_format($ed72_i_valornota, 0) : "";
       }
     } else if (trim($ed37_c_tipo) == "NIVEL") {
       $aproveitamento = $ed72_c_valorconceito;
     } else {
       $aproveitamento = $ed72_t_parecer != "" ? "Parecer" : "";
     }

     $veraprovnulo .= $aproveitamento;
     $aprov         = $ed43_c_minimoaprov;
   }
 }

 $pdf->SetX(10);
 $pdf->setfillcolor(225);

 /*if ($tipo == "TR") {
   $sDescricaoEnsino = explode(" - ", $descr_ensino_anterior);
   $pdf->cell(192, 4, "Aproveitamento na Turma {$descr_turma} - {$sDescricaoEnsino[0]}", 1, 1, "C", 1);
 } else {
   $pdf->cell(192, 4, "Aproveitamento na Turma {$descr_turma} - {$descr_serie}", 1, 1, "C", 1);
 }*/

 if ($aprov == 0) {

   $a = "";
   $b = "";
 } else {

   $a = $aprov;
   $b = "Mínimo para Aprovação:";
 }

 $iAno              = db_getsession("DB_anousu");
 $sSqlAnoCalendario = $clmatricula->sql_query($codigomatricula, "calendario.ed52_i_ano");
 $rsAnoCalendario   = $clmatricula->sql_record($sSqlAnoCalendario);

 if ($rsAnoCalendario && $clmatricula->numrows > 0) {
   $iAnoCalendario = db_utils::fieldsMemory($rsAnoCalendario, 0)->ed52_i_ano;
 }

 //$xsql = GradeAproveitamentoSQL($codigomatricula, "S", $iAno ); 
 //$xresult = db_query( $xsql );
 //$xrsql = pg_fetch_all($xresult);
 //testa($xrsql);
 //die("Foi?");

 //GradeAproveitamentoPDF($codigomatricula,192, $pdf,"S", $iAno);
 //$pdf->cell(192, 4, "{$b} {$a}", 1, 1, "C", 1);

 
  //Variáveis para controle da impressão da observação, limitando os caracteres impressos até determinado limite da
  //página
 
 $iPosicaoYObservacao = 76;
 $iDiferencaLinhas    = $pdf->GetY() - $iPosicaoYObservacao;
 $iLimiteLinhas       = 38 - ($iDiferencaLinhas / 4);
 $iCaracteresLinha    = 82;
 $iLimiteCaracteres   = $iCaracteresLinha * $iLimiteLinhas;

 
   //Busca informações sobre progressão parcial caso exista para jogar junto as observações.
  
  $sCampoProgressaoParcialAluno  = 'ed232_c_descr, ed114_ano, ed11_c_descr';
  $sWhereProgressaoParcialAluno  = "     ed114_aluno = {$ed47_i_codigo}";
  $sWhereProgressaoParcialAluno .= " and ed114_situacaoeducacao = " . ProgressaoParcialAluno::ATIVA;
  $sSqlProgressaoParcialAluno = $clprogressaoparcialaluno->sql_query_aluno_em_progressao(
                                                                                          null,
                                                                                          $sCampoProgressaoParcialAluno,
                                                                                          'ed114_ano',
                                                                                          $sWhereProgressaoParcialAluno
                                                                                        );

  $rsProgressaoParcialAluno = $clprogressaoparcialaluno->sql_record($sSqlProgressaoParcialAluno);

  $aDisciplinas = array();
  for ($iContProgressaoAluno = 0; $iContProgressaoAluno < $clprogressaoparcialaluno->numrows; $iContProgressaoAluno++) {

    $oProgressaoParcialAluno = db_utils::fieldsMemory($rsProgressaoParcialAluno, $iContProgressaoAluno);

    $sIndice = $oProgressaoParcialAluno->ed114_ano.'->'.$oProgressaoParcialAluno->ed11_c_descr;

    if (array_key_exists($sIndice, $aDisciplinas)) {
      $aDisciplinas[$sIndice] .= $oProgressaoParcialAluno->ed232_c_descr.'   ';
    } else {
      $aDisciplinas[$sIndice] = $oProgressaoParcialAluno->ed232_c_descr.'   ';
    }
  }

  $sObsProgressaoParcialAluno = '';
  $lQuebraLinha               = true;

  foreach ($aDisciplinas as $sIndice => $sDisciplina) {

    $aIndice = explode("->", $sIndice);
    $sDisciplina = trim($sDisciplina);
    $sDisciplina = str_replace('   ', ', ', $sDisciplina);

    if ($lQuebraLinha) {

      $lQuebraLinha                = false;
      $sObsProgressaoParcialAluno  = "O aluno possui progressão na etapa {$aIndice[1]} no ano {$aIndice[0]}";
      $sObsProgressaoParcialAluno .= " nas disciplinas {$sDisciplina}.";
    } else {
      $sObsProgressaoParcialAluno .= "\nna etapa {$aIndice[1]} no ano {$aIndice[0]} nas disciplinas {$sDisciplina}.";
    }
  }

  $oCalendario = CalendarioRepository::getCalendarioByCodigo($ed52_i_codigo);
  $sDataInicio = $oCalendario->getDataInicio()->getDate('d/m/Y');
  $sDataFim    = $oCalendario->getDataFinal()->getDate('d/m/Y');

  $sEscolaDestino = $escola_destino;
  if ($tipo == 'TF') {
    $oEscola        = new EscolaProcedencia($escola_destino);
    $sEscolaDestino = $oEscola->getNome();
  }

  $sObsFixa = "Período Letivo: {$sDataInicio} até {$sDataFim} Escola de Destino: {$sEscolaDestino}";
  $obs      = DBString::utf8_decode_all($obs);
  //$obs = "";
  if (empty($obs) && empty($sObsProgressaoParcialAluno) && empty($obs_transf) && empty($bolsa)) {
   //$sObservacao = ".......................................................................";
   $sObservacao = "";
  } else {
     $sObservacao = "OBS: ".(trim($obs_transf) != '' ? $obs_transf."\n" : '').
                            (trim($sObsProgressaoParcialAluno) != '' ? $sObsProgressaoParcialAluno."\n" : '').
                            (trim($obs) != '' ? $obs."\n" : '').
                            (trim($bolsa) != '' ? $bolsa."\n" : '');

    $iTotalCaracteres = ceil(strlen($sObservacao));
    $iTotalLinhas     = ceil($iTotalCaracteres / $iCaracteresLinha);  
    if ($iTotalLinhas > $iLimiteLinhas) {
      $sObservacao = substr($sObservacao, 0, $iLimiteCaracteres);
    }

    $pdf->setfont('arial', '', 9);
    $pdf->SetXY(16, $pdf->GetY() + 4);
    $pdf->multicell(180, 4, $sObservacao, 0, "J", 0, 0);
    $pdf->setXY(196, $altY);
    $pdf->cell(6, 15, "", "R", 1, "R", 0);
    //$pdf->multicell(192, 4, "", "LR", "J", 0, 0);
    //$pdf->setfillcolor(225);
    //$pdf->multicell(192, 20, "", "LR"," J", 0, 0);
      
  }

  
  //$iTotalCaracteres = ceil(strlen($sObservacao));
  //$iTotalLinhas     = ceil($iTotalCaracteres / $iCaracteresLinha);

  //if ($iTotalLinhas > $iLimiteLinhas) {
  //  $sObservacao = substr($sObservacao, 0, $iLimiteCaracteres);
  //}

  //$pdf->setfont('arial', '', 9);
  //$pdf->SetXY(16, $pdf->GetY() + 4);
  //$pdf->multicell(180, 4, $sObservacao, 0, "J", 0, 0);
  //$pdf->setXY(196, $altY);
  //$pdf->cell(6, 15, "", "R", 1, "R", 0);
  //$pdf->multicell(192, 4, "", "LR", "J", 0, 0);
  //$pdf->setfillcolor(225);
  //$pdf->multicell(192, 20, "", "LR"," J", 0, 0);

  $completar = 240 - $pdf->getY();
  //$pdf->multicell(192, $completar, "", "LR", "J", 0, 0);
  //$pdf->multicell(192, 4, "{$cidade}, {$dia_transf} de ".db_mes($mes_transf,1)." de {$ano_transf}.           ", "LR", "R", 0, 0);
  //$pdf->multicell(192, 8, "", "LR", "C", 0, 0);
  //$pdf->multicell(192, 4, "___________________________________________________", "LR", "C", 0, 0);
  //$pdf->multicell(192, 4, $nome, "LR", "C", 0, 0);
  //$pdf->multicell(192, 4, $funcao, "LR", "C", 0, 0);
  //$pdf->multicell(192, 6, "", "LRB", "C", 0, 0);
  $ed47_i_censomunicnat = buscaCidade($ed47_i_censomunicnat);

  if($sEnsino == "EDUCAÇÃO INFANTIL PRÉ-ESCOLA"){
    $pdf->multicell(192, 4, "{$cidade}, {$dia_transf} de ".db_mes($mes_transf,1)." de {$ano_transf}.           ", "LR", "R", 0, 0);
    $pdf->multicell(192, 8, "", "LR", "C", 0, 0);
    $pdf->multicell(192, 4, "___________________________________________________", "LR", "C", 0, 0);
    $pdf->multicell(192, 4, $nome, "LR", "C", 0, 0);
    $pdf->multicell(192, 4, $funcao, "LR", "C", 0, 0);
    $pdf->multicell(192, 6, "", "LRB", "C", 0, 0);
    $pdf->multicell(220, 4, str_repeat(".", 217), 0, "J", 0, 0);
    $pdf->Hextra($pdf->getX(), $pdf->getY());
    
    $pdf->setfont('arial', 'b', 10);
    $pdf->multicell(192, 1,  "",                      "LRT", "C", 0, 0);
    $pdf->multicell(192, 5, "DECLARAÇÃO DE TRANSFERÊNCIA", "LR",  "C", 0, 0);
    $pdf->multicell(192, 5, "ENSINO INFANTIL - PRÉ-ESCOLA", "LR",  "C", 0, 0);    
    $pdf->setfont('arial', '', 9);
    $sTexto = "Declaro para os devidos fins que o(a) aluno(a) {$ed47_v_nome}, nascido(a) em {$dnascimento}, na cidade de  {$ed47_i_censomunicnat}, filho(a) de {$oParagrafo->filiacao} cursa {$sEtapa} (EDUCAÇÃO INFANTIL) nesta Unidade Escolar, no ano letivo de " . date(Y) . ".";    
    $altY   = $pdf->getY();
    $pdf->cell(6, 30, "", "L", 0, "R", 0);
    $pdf->setXY(16,$altY + 5);
    $pdf->multicell(180, 4, $sTexto, 0, "J", 0, 0);    
    $pdf->ln();
    $pdf->cell(6, 30, "", "L", 0, "R", 0);
    $pdf->multicell(180, 4, "Obs.: O histórico escolar deverá ser expedido no prazo máximo de 20 (vinte) dias úteis, a partir da data do requerimento, de acordo com o Requerimento Escolar Único da Rede Municipal de Ensino.", 0, "J", 0, 0);    
    $pdf->setXY(196, $altY);
    $pdf->cell(6, 30, "", "R", 1, "R", 0);
    $altY = $pdf->getY();
    $pdf->cell(6, 15, "", "L", 0, "R", 0);
    $pdf->setXY(16,$altY);
    $pdf->cell(100, 4, "", 0, 0, "R", 0);
    $pdf->cell(80, 4, "{$cidade}, {$dia_transf} de ".db_mes($mes_transf,1)." de {$ano_transf}.", 0, 0, "R", 0);
    $pdf->cell(6, 4, "", "R", 1, "R", 0);    
    $pdf->multicell(192, 8, "", "LR", "C", 0, 0);
    $pdf->multicell(192, 4, "___________________________________________________", "LR", "C", 0, 0);
    $pdf->multicell(192, 4, "Aux. Secretaria e/ou Diretor", "LR", "C", 0, 0);    
    $pdf->multicell(192, 6, "", "LRB", "C", 0, 0);
  }elseif(($sEnsino == "ENSINO FUNDAMENTAL" && ($sEtapa == "1º ANO" || $sEtapa == "2º ANO" || $sEtapa == "3º ANO" || $sEtapa == "4º ANO" || $sEtapa == "5º ANO"))){
    $pdf->multicell(192, 4, "{$cidade}, {$dia_transf} de ".db_mes($mes_transf,1)." de {$ano_transf}.           ", "LR", "R", 0, 0);
    $pdf->multicell(192, 8, "", "LR", "C", 0, 0);
    $pdf->multicell(192, 4, "___________________________________________________", "LR", "C", 0, 0);
    $pdf->multicell(192, 4, $nome, "LR", "C", 0, 0);
    $pdf->multicell(192, 4, $funcao, "LR", "C", 0, 0);
    $pdf->multicell(192, 6, "", "LRB", "C", 0, 0);
    $pdf->multicell(220, 4, str_repeat(".", 217), 0, "J", 0, 0);
    $pdf->Hextra($pdf->getX(), $pdf->getY());

    $pdf->setfont('arial', 'b', 10);
    $pdf->multicell(192, 1,  "",                      "LRT", "C", 0, 0);
    $pdf->multicell(192, 5, "DECLARAÇÃO DE TRANSFERÊNCIA", "LR",  "C", 0, 0);
    $pdf->multicell(192, 5, "ENSINO FUNDAMENTAL - ANOS INICIAIS", "LR",  "C", 0, 0);    
    $pdf->setfont('arial', '', 9);
    $sTexto = "Declaro para os devidos fins que o(a) aluno(a) {$ed47_v_nome}, nascido(a) em {$dnascimento}, na cidade de  {$ed47_i_censomunicnat}, filho(a) de {$oParagrafo->filiacao} cursa {$sEtapa} do ENSINO FUNDAMENTAL ANOS INICIAIS nesta Unidade Escolar, no ano letivo de " . date(Y) . ".";
    $altY   = $pdf->getY();
    $pdf->cell(6, 30, "", "L", 0, "R", 0);
    $pdf->setXY(16,$altY + 5);
    $pdf->multicell(180, 4, $sTexto, 0, "J", 0, 0);    
    $pdf->ln();
    $pdf->cell(6, 30, "", "L", 0, "R", 0);
    //$pdf->setXY(16,$altY + 5);
    $pdf->multicell(180, 4, "O(a) aluno(a) deverá ser matriculado(a) no: {$sEtapaDest} do(a) {$sEnsinoDest}.", 0, "J", 0, 0);    
    $pdf->ln();
    $pdf->cell(6, 30, "", "L", 0, "R", 0);
    $pdf->multicell(180, 4, "Obs.: O histórico escolar deverá ser expedido no prazo máximo de 20 (vinte) dias úteis, a partir da data do requerimento, de acordo com o Requerimento Escolar Único da Rede Municipal de Ensino.", 0, "J", 0, 0);    
    $pdf->multicell(192, 16, "", "LR", "C", 0, 0);
    $pdf->setXY(196, $altY);
    $pdf->cell(6, 30, "", "R", 1, "R", 0);
    //$pdf->cell(6, 15, "", "L", 0, "R", 0);
    $altY = $pdf->getY();    
    //$pdf->setY($altY);
    $pdf->setXY(16,$altY + 8);
    //$pdf->cell(100, 6, "", 0, 1, "LR", 0);
    $pdf->cell(100, 4, "", 0, 0, "R", 0);
    $pdf->cell(80, 4, "{$cidade}, {$dia_transf} de ".db_mes($mes_transf,1)." de {$ano_transf}.", 0, 0, "R", 0);
    $pdf->cell(6, 4, "", "R", 1, "R", 0);    
    $pdf->multicell(192, 8, "", "LR", "C", 0, 0);
    $pdf->multicell(192, 4, "___________________________________________________", "LR", "C", 0, 0);
    $pdf->multicell(192, 4, "Aux. Secretaria e/ou Diretor", "LR", "C", 0, 0);    
    $pdf->multicell(192, 6, "", "LRB", "C", 0, 0);
  }elseif(($sEnsino == "ENSINO FUNDAMENTAL" && ($sEtapa == "6º ANO" || $sEtapa == "7º ANO" || $sEtapa == "8º ANO" || $sEtapa == "9º ANO"))){
    $pdf->multicell(192, 4, "{$cidade}, {$dia_transf} de ".db_mes($mes_transf,1)." de {$ano_transf}.           ", "LR", "R", 0, 0);
    $pdf->multicell(192, 8, "", "LR", "C", 0, 0);
    $pdf->multicell(192, 4, "___________________________________________________", "LR", "C", 0, 0);
    $pdf->multicell(192, 4, $nome, "LR", "C", 0, 0);
    $pdf->multicell(192, 4, $funcao, "LR", "C", 0, 0);
    $pdf->multicell(192, 6, "", "LRB", "C", 0, 0);
    $pdf->multicell(220, 4, str_repeat(".", 217), 0, "J", 0, 0);
    $pdf->Hextra($pdf->getX(), $pdf->getY());

    $pdf->setfont('arial', 'b', 10);
    $pdf->multicell(192, 1,  "",                      "LRT", "C", 0, 0);
    $pdf->multicell(192, 5, "DECLARAÇÃO DE TRANSFERÊNCIA", "LR",  "C", 0, 0);
    $pdf->multicell(192, 5, "ENSINO FUNDAMENTAL - ANOS FINAIS", "LR",  "C", 0, 0);    
    $pdf->setfont('arial', '', 9);
    $sTexto = "Declaro para os devidos fins que o(a) aluno(a) {$ed47_v_nome}, nascido(a) em {$dnascimento}, na cidade de  {$ed47_i_censomunicnat}, filho(a) de {$oParagrafo->filiacao} cursa {$sEtapa} do ENSINO FUNDAMENTAL ANOS FINAIS nesta Unidade Escolar, no ano letivo de " . date(Y) . ".";
    $altY   = $pdf->getY();
    $pdf->cell(6, 30, "", "L", 0, "R", 0);
    $pdf->setXY(16,$altY + 5);
    $pdf->multicell(180, 4, $sTexto, 0, "J", 0, 0);    
    $pdf->ln();
    $pdf->cell(6, 30, "", "L", 0, "R", 0);
    //$pdf->setXY(16,$altY + 5);
    $pdf->multicell(180, 4, "O(a) aluno(a) deverá ser matriculado(a) no: {$sEtapaDest} do(a) {$sEnsinoDest}.", 0, "J", 0, 0);
    $pdf->ln();
    $pdf->cell(6, 30, "", "L", 0, "R", 0);
    $pdf->multicell(180, 4, "Obs.: O histórico escolar deverá ser expedido no prazo máximo de 20 (vinte) dias úteis, a partir da data do requerimento, de acordo com o Requerimento Escolar Único da Rede Municipal de Ensino.", 0, "J", 0, 0);    
    $pdf->multicell(192, 16, "", "LR", "C", 0, 0);
    $pdf->setXY(196, $altY);
    $pdf->cell(6, 33, "", "R", 1, "R", 0);
    //$pdf->cell(6, 15, "", "L", 0, "R", 0);
    $altY = $pdf->getY();    
    //$pdf->setY($altY);
    $pdf->setXY(16,$altY + 8);
    //$pdf->cell(100, 6, "", 0, 1, "LR", 0);
    $pdf->cell(100, 4, "", 0, 0, "R", 0);
    $pdf->cell(80, 4, "{$cidade}, {$dia_transf} de ".db_mes($mes_transf,1)." de {$ano_transf}.", 0, 0, "R", 0);
    $pdf->cell(6, 4, "", "R", 1, "R", 0);    
    $pdf->multicell(192, 8, "", "LR", "C", 0, 0);
    $pdf->multicell(192, 4, "___________________________________________________", "LR", "C", 0, 0);
    $pdf->multicell(192, 4, "Aux. Secretaria e/ou Diretor", "LR", "C", 0, 0);    
    $pdf->multicell(192, 6, "", "LRB", "C", 0, 0);
  }elseif($sEnsino == "EDUCAÇÃO INFANTIL CRECHE"){
    $pdf->multicell(192, 4, "{$cidade}, {$dia_transf} de ".db_mes($mes_transf,1)." de {$ano_transf}.           ", "LR", "R", 0, 0);
    $pdf->multicell(192, 8, "", "LR", "C", 0, 0);
    $pdf->multicell(192, 4, "___________________________________________________", "LR", "C", 0, 0);
    $pdf->multicell(192, 4, $nome, "LR", "C", 0, 0);
    $pdf->multicell(192, 4, $funcao, "LR", "C", 0, 0);
    $pdf->multicell(192, 6, "", "LRB", "C", 0, 0);

    $pdf->setXY(196, $altY);
    $pdf->cell(6, 38, "", "R", 1, "R", 0);
  }
  
  
}

$pdf->Output();
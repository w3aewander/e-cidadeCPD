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

require_once(modification("libs/db_utils.php"));
//require_once(modification("fpdf151/FpdfMultiCellBorder.php"));
require_once(modification("fpdf151/pdfwebseller.php"));


//require_once(modification("libs/db_utils.php"));
require_once(modification("libs/db_app.utils.php"));
require_once(modification("libs/db_stdlibwebseller.php"));
require_once(modification("libs/db_libdocumento.php"));
require_once(modification("dbforms/db_funcoes.php"));

function testa($var){
  echo "<pre>";
  print_r($var);
  echo "</pre>";
}

function retornaCodigoAluno($codmatricula){
  $sql = pg_query("SELECT ed60_i_aluno FROM matricula WHERE ed60_i_codigo = {$codmatricula}");
  $resultado = pg_fetch_all($sql);
  return $resultado[0]["ed60_i_aluno"];
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

function retornaEtapaSeguinte($etapaatual){
  
  $etapa["MATERNAL I"] = "MATERNAL II.";
  $etapa["MATERNAL II"] = "MATERNAL III.";
  $etapa["MATERNAL III"] = "1º PERÍODO da Pré-escola.";
  $etapa["1º PERÍODO"] = "2º PERÍODO da Pré-escola.";
  $etapa["2º PERÍODO"] = "1º ANO do Ensino Fundamental Anos Iniciais.";
  $etapa["1º ANO"] = "2º ANO do Ensino Fundamental Anos Iniciais.";
  $etapa["2º ANO"] = "3º ANO do Ensino Fundamental Anos Iniciais.";
  $etapa["3º ANO"] = "4º ANO do Ensino Fundamental Anos Iniciais.";
  $etapa["4º ANO"] = "5º ANO do Ensino Fundamental Anos Iniciais.";
  $etapa["5º ANO"] = "6º ANO do Ensino Fundamental Anos Finais.";
  $etapa["6º ANO"] = "7º ANO do Ensino Fundamental Anos Finais.";
  $etapa["7º ANO"] = "8º ANO do Ensino Fundamental Anos Finais.";
  $etapa["8º ANO"] = "9º ANO do Ensino Fundamental Anos Finais.";
  $etapa["9º ANO"] = "";
    
  return $etapa[$etapaatual];
  
}

function buscaCidade($codigo){
  $sql = pg_query("SELECT ed261_c_nome FROM censomunic WHERE ed261_i_codigo = {$codigo}");
  $resultado = pg_fetch_all($sql);
  return trim($resultado[0]["ed261_c_nome"]);
}

$cldiarioavaliacao = new cl_diarioavaliacao;


$oDadosRelatorio = new stdClass();
$oDadosRelatorio->oData   = new DBDate(date("Y-m-d"));
$oDadosRelatorio->sData   = $oDadosRelatorio->oData->dataPorExtenso();
$oDadosRelatorio->aAlunos = array();
try {

  if ( empty($_GET['iTransferencia']) ) {
    throw new Exception("Não foi informado a transferência.");
  }
  $iMatricula = null;
  if ( !empty($_GET['iMatricula']) ) {
    $iMatricula = $_GET['iMatricula'];
  }

  $iTransferencia = $_GET['iTransferencia'];
  $oTransferencia = new TransferenciaLote($iTransferencia);
  $aMatriculas    = $oTransferencia->getMatriculas();

  $sTexto  = "Atesto que [sAluno] natural de [sNaturalidade], no estado de [sUf], nascido(a) no dia [sDataNascimento], ";
  $sTexto .= "filho(a) de [sFiliacao], cursou até [sDataResultadoFinal], a(o) [sEtapa] do(a) [sEnsino] nesta escola, ";
  $sTexto .= "estando apto a continuar seus estudos em qualquer instituição de ensino, conforme legislação vigente.";

  $aVariaveis = array('sAluno', 'sNaturalidade', 'sUf', 'sDataNascimento', 'sFiliacao',
                      'sDataResultadoFinal', 'sEtapa', 'sEnsino');

  foreach ($aMatriculas as $oMatricula) {

    $oDados = new stdClass();
    if ( !is_null($iMatricula) && $iMatricula != $oMatricula->getCodigo()) {
      continue;
    }

    $oAluno                = $oMatricula->getAluno();
    $oDados->sAluno        = $oAluno->getNome();
    $oDados->sNaturalidade = ".................................................................";
    $oDados->sUf           = ".........";
    if ( !is_null($oAluno->getNaturalidade()->getCodigo()) ) {

      $oDados->sNaturalidade = $oAluno->getNaturalidade()->getNome();
      $oDados->sUf           = $oAluno->getNaturalidade()->getUF()->getUF();
    }

    $oDataNascinmento        = new DBDate($oAluno->getDataNascimento());
    $oDados->sDataNascimento = $oDataNascinmento->dataPorExtenso();
    $oDados->sNomeMae        = $oAluno->getNomeMae();
    $oDados->sNomePai        = $oAluno->getNomePai();
    $oDados->sResponsavel    = $oAluno->getNomeResponsavelLegal();
    $oDados->sEtapa          = $oMatricula->getEtapaDeOrigem()->getNome();
    $oDados->sEtapaAbreviado = $oMatricula->getEtapaDeOrigem()->getNomeAbreviado();
    $oDados->sEnsino         = $oMatricula->getEtapaDeOrigem()->getEnsino()->getNome();
    $oDados->matricula = retornaCodigoAluno($oMatricula->getCodigo());
    $oDados->codmatricula = $oMatricula->getCodigo();
    $oDados->novadatanscimento = $oDataNascinmento->getDia() . "/" . $oDataNascinmento->getMes() . "/" . $oDataNascinmento->getAno();

    $aFiliacao = array();
    if ( $oAluno->getNomeMae() != '') {
      $aFiliacao[] = $oAluno->getNomeMae();
    }
    if ( $oAluno->getNomePai() != '') {
      $aFiliacao[] = $oAluno->getNomePai();
    }

    $oDados->sFiliacao = implode(' e ', $aFiliacao);
    $oCalendario       = $oMatricula->getTurma()->getCalendario();

    $oDados->oDataResultadoFinal = $oCalendario->getDataResultadoFinal();
    $oDados->sDataResultadoFinal = $oDados->oDataResultadoFinal->dataPorExtenso();

    /**
     * Monta o texto de transferencia
     */
    $oDados->sMensagem = $sTexto;
    foreach ($aVariaveis as $sVariavel) {
      $oDados->sMensagem = str_replace("[{$sVariavel}]", $oDados->$sVariavel, $oDados->sMensagem);
    }

    /**
     * Busca as progressões parciais ATIVAS que o aluno possui.
     */
    $aProgressoes             =  ProgressaoParcialAlunoRepository::getProgressoesAtivas($oAluno);
    $oDados->aProgressoes     = array();
    $aDisciplinasEmProgressao = array();
    foreach ( $aProgressoes as $oProgressao ) {

      $iAno   = $oProgressao->getAno();
      $sEtapa = $oProgressao->getEtapa()->getNome();
      $sIndex = "{$iAno}#{$sEtapa}";

      $aDisciplinasEmProgressao[$sIndex][] = trim($oProgressao->getDisciplina()->getNomeDisciplina());
    }

    foreach ($aDisciplinasEmProgressao as $sIndice => $aDisciplina) {

      $aIndice     = explode("#", $sIndice);
      $sDisciplina = implode(", ", $aDisciplina);

      $sObsProgressao  = "O aluno possui progressão na etapa {$aIndice[1]} no ano {$aIndice[0]}";
      $sObsProgressao .= " na(s) disciplina(s) {$sDisciplina}.";
      $oDados->aProgressoes[] = $sObsProgressao;
    }

    /**
     * Observacao fixa
     */
    $sDataInicio    = $oCalendario->getDataInicio()->getDate(DBDate::DATA_PTBR);
    $sDataFim       = $oCalendario->getDataFinal()->getDate(DBDate::DATA_PTBR);
    $sEscolaDestino = $oTransferencia->getEscolaDestino()->getNome();
    $sTipoEscola    = "fora da rede";
    if ( $oTransferencia->isEscolaDestinoRede() ) {
      $sTipoEscola = "na rede";
    }

    $oDados->sObservacaoFixa  = "Período Letivo: {$sDataInicio} até {$sDataFim}. Escola de Destino {$sTipoEscola}: ";
    $oDados->sObservacaoFixa .= $sEscolaDestino;

    $oDadosRelatorio->aAlunos[] = $oDados;
  }

  $sAssinatura = str_repeat('.', '95');
  $sFuncao     = '';

  if ( isset($_GET['sEmissor'])) {

    $sAto = base64_decode($_GET['sAtoLegal']);
    $sAssinatura = base64_decode($_GET['sEmissor']);
    $sFuncao     = base64_decode($_GET['sFuncao']);
    if ( !empty($sAto) ) {
      $sFuncao .= " ( {$sAto})";
    }
  }

} catch(Exception $e) {

  $sMsg = urlencode($e->getMessage());
  db_redireciona('db_erros.php?fechar=true&db_erro=' . $sMsg);
}


//testa($oDadosRelatorio->aAlunos);
//die("Busca dados");
//codigomatricula

$head1 = "GUIA DE TRANSFERÊNCIA";
$head2 = "";
$head3 = "Nome: " . trim($oDadosRelatorio->aAlunos[0]->sAluno);
$head4 = "Matrícula: " . trim($oDadosRelatorio->aAlunos[0]->matricula);

$pdf   = new PDF();
$pdf->Open();
$pdf->AliasNbPages();
$pdf->imprime_rodape = false;



//$oPdf = new FpdfMultiCellBorder('P');
//$oPdf   = new PDF();
//$oPdf->Open();
//$oPdf->AliasNbPages();
//$oPdf->setExibeBrasao(true);
//$oPdf->exibeHeader(true);
//$oPdf->SetAutoPageBreak(false, 10);
//$oPdf->SetFillColor(225);
//$oPdf->SetMargins(10, 10);
//$oPdf->mostrarRodape(true);
//$oPdf->mostrarEmissor(true);
//$oPdf->mostrarTotalDePaginas(false);




foreach ($oDadosRelatorio->aAlunos as $oAluno) {  
  $sEnsino = $oAluno->sEnsino;
  $sEtapa = $oAluno->sEtapa;
  $codigomatricula = $oAluno->codmatricula;
  $ed47_v_mae = trim($oAluno->sNomeMae);
  $ed47_v_pai = trim($oAluno->sNomePai);
  $ed47_v_nome = trim($oAluno->sAluno);
  $dnascimento = trim($oAluno->novadatanascimento);
  $ed47_i_censomunicnat = trim($oAluno->sNaturalidade);
  //$ed47_i_censomunicnat = buscaCidade($ed47_i_censomunicnat);
  //testa($oAluno);
  
  //Falta creche
    //var_dump($sEnsino) ;
    //var_dump($sEtapa);
    //die("Verifica anos finais");
  

  $pdf->AddPage();


  
  
  if($sEnsino == "EDUCAÇÃO INFANTIL CRECHE - EIC"){
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
    $proximaetapa = retornaEtapaSeguinte(($sEtapa));   
    

    $pdf->setfont('arial', 'b', 10);
    $pdf->multicell(192, 1,  "",                      "LRT", "C", 0, 0);
    $pdf->multicell(192, 10, "DECLARAÇÃO DE TRANSFERÊNCIA", "LR",  "C", 0, 0);
    $pdf->setfont('arial', '', 9);
    
    $nomeresponsavel = ($ed47_v_mae) ? $ed47_v_mae : $ed47_v_pai;    
    $sTexto = "Declaro para os devidos fins que o(a) aluno(a) {$ed47_v_nome}, nascido(a) em {$dnascimento}, na cidade de {$ed47_i_censomunicnat}, filho(a) de {$oParagrafo->filiacao} cursou {$sEtapa} (EDUCAÇÃO INFANTIL), nesta Unidade Escolar, no ano letivo de " . date(Y) . ".";          
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
    $pdf->cell(6, 30, "", "L", 0, "R", 0);
    $pdf->cell(200, 6, "O(a) aluno(a) deverá ser matriculado(a) no: {$proximaetapa}", 0, 1, "L", 0);
    //$pdf->setfont('arial', '', 9);
    

  }elseif($sEnsino == "EDUCAÇÃO INFANTIL PRÉ-ESCOLA"){     
    //OK
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
    $proximaetapa = retornaEtapaSeguinte(($sEtapa));
    $pdf->setfont('arial', 'b', 10);
    $pdf->multicell(192, 1,  "",                      "LRT", "C", 0, 0);
    $pdf->multicell(192, 5, "DECLARAÇÃO DE TRANSFERÊNCIA", "LR",  "C", 0, 0);
    $pdf->multicell(192, 5, "ENSINO INFANTIL - PRÉ-ESCOLA", "LR",  "C", 0, 0);    
    $pdf->setfont('arial', '', 9);    
    $nomeresponsavel = ($ed47_v_mae) ? $ed47_v_mae : $ed47_v_pai;
    $sTexto = "Declaro para os devidos fins que o(a) aluno(a) {$ed47_v_nome}, nascido(a) em {$dnascimento}, na cidade de  {$ed47_i_censomunicnat}, filho(a) de {$oParagrafo->filiacao} cursou {$sEtapa} (EDUCAÇÃO INFANTIL) nesta Unidade Escolar, no ano letivo de " . date(Y) . " tendo sido considerado: RESULTADO FINAL.";    
    $altY   = $pdf->getY();
    $pdf->cell(6, 30, "", "L", 0, "R", 0);
    $pdf->setXY(16,$altY + 5);
    $pdf->multicell(180, 4, $sTexto, 0, "J", 0, 0);
    $pdf->ln();
    $pdf->cell(6, 30, "", "L", 0, "R", 0);
    $pdf->multicell(180, 4, "O(a) aluno(a) deverá ser matriculado(a) no: {$proximaetapa}", 0, "J", 0, 0);
    $pdf->ln();
    $pdf->cell(6, 30, "", "L", 0, "R", 0);
    $pdf->multicell(180, 4, "Obs.: O histórico escolar deverá ser expedido no prazo máximo de 20 (vinte) dias úteis, a partir da data do requerimento, de acordo com o Requerimento Escolar Único da Rede Municipal de Ensino", 0, "J", 0, 0);
    $pdf->ln();  
    $pdf->setXY(196, $altY);
    $pdf->cell(6, 30, "", "R", 1, "R", 0);
    $altY = $pdf->getY();
    $pdf->cell(6, 15, "", "L", 0, "R", 0);
    $pdf->setXY(16,$altY);

    $pdf->SetXY(16, $pdf->GetY() + 20);
    $pdf->multicell(180, 4, "", 0, "J", 0, 0);
    $pdf->setXY(196, $altY);
    $pdf->cell(6, 15, "", "R", 1, "R", 0);    
    $sMunicipio = $oTransferencia->getEscola()->getMunicipio();
    $pdf->multicell(192, 4, "{$sMunicipio}, {$oDadosRelatorio->sData}          ", "LR", "R", 0, 0);    
    $pdf->multicell(192, 8, "", "LR", "C", 0, 0);
    $pdf->multicell(192, 4, "___________________________________________________", "LR", "C", 0, 0);
    $pdf->multicell(192, 4, "Assinatura do Responsável", "LR", "C", 0, 0);    
    $pdf->multicell(192, 6, "", "LRB", "C", 0, 0);


  }elseif(($sEnsino == "ENSINO FUNDAMENTAL" && ($sEtapa == "1º ANO" || $sEtapa == "2º ANO" || $sEtapa == "3º ANO" || $sEtapa == "4º ANO" || $sEtapa == "5º ANO"))){    
    //OK
    $sSqlDiarioAvaliacao    = $cldiarioavaliacao->sql_query_guia("", "*", "ed41_i_sequencia ASC", "ed60_i_codigo=$codigomatricula");
    $result_diarioavaliacao = $cldiarioavaliacao->sql_record($sSqlDiarioAvaliacao);
    
    $zzz = pg_fetch_all($result_diarioavaliacao);
    $xed60_i_codigo = $zzz[0]["ed60_i_codigo"];
    $xed60_i_aluno = $zzz[0]["ed60_i_aluno"];
    $xed11_i_codigo = $zzz[0]["ed11_i_codigo"];
    $xed60_i_turma = $zzz[0]["ed60_i_turma"];
    $xed59_i_turma = $zzz[0]["ed59_i_turma"];
    $xed60_c_situacao = $zzz[0]["ed60_c_situacao"];
    $xcodiario = retornaCodDiario($xed60_i_codigo, $xed60_i_aluno, $xed11_i_codigo, $xed60_i_turma);
    $xdiario = dadosDiario($xcodiario);    
    $codreg = buscaCodigoRegencia($xed59_i_turma);    
    $xaulas = dadosAulas($codreg);
    $proximaetapa = retornaEtapaSeguinte(($sEtapa));    
    $sResultadoFinal = ResultadoFinal( $xed60_i_codigo, $xed60_i_aluno, $xed60_i_turma, trim( $xed60_c_situacao ), trim( $ed60_c_concluida), $sEtapa );
    
    if($sResultadoFinal == "EVADIDO" || $sResultadoFinal == "REPROVADO"){
      $proximaetapa = $sEtapa;
    }elseif($sResultadoFinal == "APROVADO" || $sResultadoFinal == "EM ANDAMENTO"){
      $proximaetapa = retornaEtapaSeguinte(($sEtapa));
    }    
    $pdf->setfont('arial', 'b', 10);
    $pdf->multicell(192, 1,  "",                      "LRT", "C", 0, 0);
    $pdf->multicell(192, 5, "DECLARAÇÃO DE TRANSFERÊNCIA", "LR",  "C", 0, 0);
    $pdf->multicell(192, 5, "ENSINO FUNDAMENTAL ANOS INICIAIS", "LR",  "C", 0, 0);    
    $pdf->setfont('arial', '', 9);    
    $nomeresponsavel = ($ed47_v_mae) ? $ed47_v_mae : $ed47_v_pai;        
    $sTexto = "Declaro para os devidos fins que o(a) aluno(a) {$ed47_v_nome}, nascido(a) em {$dnascimento}, na cidade de  {$ed47_i_censomunicnat}, filho(a) de {$oAluno->sFiliacao} cursou {$sEtapa} do ENSINO FUNDAMENTAL ANOS INICIAIS nesta Unidade Escolar, no ano letivo de " . date(Y) . " tendo sido considerado: {$sResultadoFinal}.";    
    $altY   = $pdf->getY();
    $pdf->cell(6, 30, "", "L", 0, "R", 0);
    $pdf->setXY(16,$altY + 5);
    $pdf->multicell(180, 4, $sTexto, 0, "J", 0, 0);
    $pdf->ln();
    $pdf->cell(6, 30, "", "L", 0, "R", 0);
    $pdf->multicell(180, 4, "O(a) aluno(a) deverá ser matriculado(a) no: {$proximaetapa}", 0, "J", 0, 0);    
    $pdf->ln();
    $pdf->cell(6, 30, "", "L", 0, "R", 0);
    $pdf->multicell(180, 4, "Obs.: O histórico escolar deverá ser expedido no prazo máximo de 20 (vinte) dias úteis, a partir da data do requerimento, de acordo com o Requerimento Escolar Único da Rede Municipal de Ensino.", 0, "J", 0, 0);    
    $pdf->ln();  
    $pdf->setXY(196, $altY);
    $pdf->cell(6, 30, "", "R", 1, "R", 0);
    $altY = $pdf->getY();
    $pdf->cell(6, 35, "", "L", 0, "R", 0);
    $pdf->setXY(16,$altY + 15);

    $pdf->SetXY(16, $pdf->GetY() + 20);
    $pdf->multicell(180, 4, "", 0, "J", 0, 0);
    $pdf->setXY(196, $altY);
    $pdf->cell(6, 15, "", "R", 1, "R", 0);    
    $sMunicipio = $oTransferencia->getEscola()->getMunicipio();
    $pdf->multicell(192, 4, "{$sMunicipio}, {$oDadosRelatorio->sData}          ", "LR", "R", 0, 0);    
    $pdf->multicell(192, 8, "", "LR", "C", 0, 0);
    $pdf->multicell(192, 4, "___________________________________________________", "LR", "C", 0, 0);
    $pdf->multicell(192, 4, "Assinatura do Responsável", "LR", "C", 0, 0);    
    $pdf->multicell(192, 6, "", "LRB", "C", 0, 0);
    
  }elseif(($sEnsino == "ENSINO FUNDAMENTAL - EF" && ($sEtapa == "6º ANO" || $sEtapa == "7º ANO" || $sEtapa == "8º ANO" || $sEtapa == "9º ANO"))){
    $sSqlDiarioAvaliacao    = $cldiarioavaliacao->sql_query_guia("", "*", "ed41_i_sequencia ASC", "ed60_i_codigo=$codigomatricula");
    $result_diarioavaliacao = $cldiarioavaliacao->sql_record($sSqlDiarioAvaliacao);
    $zzz = pg_fetch_all($result_diarioavaliacao);
    $xed60_i_codigo = $zzz[0]["ed60_i_codigo"];
    $xed60_i_aluno = $zzz[0]["ed60_i_aluno"];
    $xed11_i_codigo = $zzz[0]["ed11_i_codigo"];
    $xed60_i_turma = $zzz[0]["ed60_i_turma"];
    $xed59_i_turma = $zzz[0]["ed59_i_turma"];
    $xed60_c_situacao = $zzz[0]["ed60_c_situacao"];
    $xcodiario = retornaCodDiario($xed60_i_codigo, $xed60_i_aluno, $xed11_i_codigo, $xed60_i_turma);
    $xdiario = dadosDiario($xcodiario);    
    $codreg = buscaCodigoRegencia($xed59_i_turma);    
    $xaulas = dadosAulas($codreg);
    $proximaetapa = retornaEtapaSeguinte(($sEtapa));
    $sResultadoFinal = ResultadoFinal( $xed60_i_codigo, $xed60_i_aluno, $xed60_i_turma, trim( $xed60_c_situacao ), trim( $ed60_c_concluida), $sEtapa );
    if($sResultadoFinal == "EVADIDO" || $sResultadoFinal == "REPROVADO" || $sResultadoFinal == "DESISTENTE"){
      $proximaetapa = $sEtapa;
    }elseif($sResultadoFinal == "APROVADO" || $sResultadoFinal == "EM ANDAMENTO" || $sResultadoFinal == "APROVADO COM PROGRESSAO PARCIAL /DEPENDÊNCIA" || $sResultadoFinal == "APROVADO PARCIAL"){
      $proximaetapa = retornaEtapaSeguinte(($sEtapa));
    }

    $pdf->setfont('arial', 'b', 10);
    $pdf->multicell(192, 1,  "",                      "LRT", "C", 0, 0);
    $pdf->multicell(192, 5, "DECLARAÇÃO DE TRANSFERÊNCIA", "LR",  "C", 0, 0);
    $pdf->multicell(192, 5, "ENSINO FUNDAMENTAL ANOS FINAIS", "LR",  "C", 0, 0);
    $pdf->setfont('arial', '', 9);
    
    $nomeresponsavel = ($ed47_v_mae) ? $ed47_v_mae : $ed47_v_pai;    
    
    $sTexto = "Declaro para os devidos fins que o(a) aluno(a) {$ed47_v_nome}, nascido(a) em {$dnascimento}, na cidade de  {$ed47_i_censomunicnat}, filho(a) de {$oParagrafo->filiacao} cursou {$sEtapa} do ENSINO FUNDAMENTAL ANOS INICIAIS nesta Unidade Escolar, no ano letivo de " . date(Y) . " tendo sido considerado: {$sResultadoFinal}.";
    
    $altY   = $pdf->getY();

    $pdf->cell(6, 30, "", "L", 0, "R", 0);
    $pdf->setXY(16,$altY + 5);
    $pdf->multicell(180, 4, $sTexto, 0, "J", 0, 0);
    $pdf->ln();
    $pdf->cell(6, 30, "", "L", 0, "R", 0);
    $pdf->multicell(180, 4, "O(a) aluno(a) deverá ser matriculado(a) no: {$proximaetapa}", 0, "J", 0, 0);    
    $pdf->ln();
    $pdf->cell(6, 30, "", "L", 0, "R", 0);
    $pdf->multicell(180, 4, "Obs.: O histórico escolar deverá ser expedido no prazo máximo de 20 (vinte) dias úteis, a partir da data do requerimento, de acordo com o Requerimento Escolar Único da Rede Municipal de Ensino.", 0, "J", 0, 0);    
    $pdf->ln();
  
    $pdf->setXY(196, $altY);
    $pdf->cell(6, 30, "", "R", 1, "R", 0);

    $altY = $pdf->getY();

    $pdf->cell(6, 15, "", "L", 0, "R", 0);
    $pdf->setXY(16,$altY);
  }

  

  

  /*
  ORIGINAL
  $oPdf->SetY( $oPdf->GetY() + 10 );

  $oPdf->SetFont('Arial', 'B', 10);
  $oPdf->Cell( 192, 4, "Guia de Transferência", 0, 1, 'C');
  $oPdf->ln();
  $oPdf->SetFont('Arial', '', 8);
  $oPdf->SetX(20);
  $oPdf->MultiCell( 173, 4, $oAluno->sMensagem);
  $oPdf->SetY( 100 );

  $oPdf->SetFont('Arial', 'B', 9);
  $oPdf->Cell( 192, 4, 'Observações', 1, 1, 'C', 1);
  $oPdf->Ln();
  $oPdf->SetFont('Arial', '', 8);

  foreach ($oAluno->aProgressoes as $sMsg ) {

    $oPdf->SetX(20);
    $oPdf->MultiCell( 173, 4, $sMsg);
  }
  $oPdf->SetX(20);
  $oPdf->MultiCell( 173, 4, $oAluno->sObservacaoFixa);

  $oPdf->SetY( 240 );

  $sMunicipio = $oTransferencia->getEscola()->getMunicipio();
  $oPdf->Cell( 192, 4, "{$sMunicipio}, {$oDadosRelatorio->sData}", 0, 1, 'C');
  $oPdf->ln(12);
  $oPdf->Line(60, $oPdf->GetY(), 160, $oPdf->GetY() );

  $oPdf->ln();

  $oPdf->Cell( 192, 4, $sAssinatura, 0, 1, 'C');
  if ( !empty($sFuncao) ) {
    $oPdf->Cell( 192, 4, $sFuncao, 0, 1, 'C');
  }

  $iYFinal = $oPdf->GetY() + 10;
  $oPdf->Line(10, 40, 202, 40 );
  $oPdf->Line(10, 40, 10, $iYFinal );
  $oPdf->Line(10, $iYFinal, 202, $iYFinal );
  $oPdf->Line(202, 40, 202, $iYFinal );
  */
}//foreach principal

$pdf->output();
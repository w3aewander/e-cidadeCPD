<?php
/*
 * E-cidade Software Publico para Gestao Municipal
 * Copyright (C) 2014  DBSeller Servicos de Informatica
 * www.dbseller.com.br
 * e-cidade@dbseller.com.br
 *
 * Este programa e software livre; voce pode redistribui-lo e/ou
 * modifica-lo sob os termos da Licenca Publica Geral GNU, conforme
 * publicada pela Free Software Foundation; tanto a versao 2 da
 * Licenca como (a seu criterio) qualquer versao mais nova.
 *
 * Este programa e distribuido na expectativa de ser util, mas SEM
 * QUALQUER GARANTIA; sem mesmo a garantia implicita de
 * COMERCIALIZACAO ou de ADEQUACAO A QUALQUER PROPOSITO EM
 * PARTICULAR. Consulte a Licenca Publica Geral GNU para obter mais
 * detalhes.
 *
 * Voce deve ter recebido uma copia da Licenca Publica Geral GNU
 * junto com este programa; se nao, escreva para a Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA
 * 02111-1307, USA.
 *
 * Copia da licenca no diretorio licenca/licenca_en.txt
 * licenca/licenca_pt.txt
 */

require_once(modification("fpdf151/pdfwebseller.php"));
require_once(modification("libs/db_stdlibwebseller.php"));
require_once(modification("libs/db_utils.php"));
require_once(modification("libs/JSON.php"));
require_once(modification("libs/db_libdocumento.php"));
require_once(modification("std/db_stdClass.php"));
require_once(modification("libs/db_stdlib.php"));
require_once(modification("libs/db_app.utils.php"));
require_once(modification("model/educacao/MatriculaRepository.model.php"));
require_once(modification("model/educacao/avaliacao/DiarioClasse.model.php"));
require_once(modification("model/educacao/DBEducacaoTermo.model.php"));
require_once(modification("dbforms/db_funcoes.php"));


function buscaResultadoFinalEncerramento($iCodigoMatricula, $iCodigoEnsino, $iAnoCalendario){
  
  // Iniciamos a transação para satisfazer a exigência da classe DiarioClasse
  db_inicio_transacao();

  try {

    if (!class_exists('MatriculaRepository')) {
      db_fim_transacao(false); // Fecha antes de retornar
      return '';
    }
    $oMatricula = MatriculaRepository::getMatriculaByCodigo($iCodigoMatricula);
    if (!$oMatricula) {
      db_fim_transacao(false); // Fecha antes de retornar
      return '';
    }
    // Se não está matriculado, retorna a situação
    if ($oMatricula->getSituacao() != 'MATRICULADO') {
      db_fim_transacao(false); // Fecha antes de retornar
      return strtoupper($oMatricula->getSituacao());
    }

    /**
    * Autor: Uemerson Santana
    * Data: 18/11/2025
    * Demanda: 17412
    */
    
    $temAprovacaoConselho = false;
    if (class_exists('AprovacaoConselho')) {

      // Método 1: Verificar via OOP
      try {
        // Esta chamada exige transação ativa:
        $aDisciplinas = $oMatricula->getDiarioDeClasse()->getDisciplinas();

        foreach ($aDisciplinas as $oDisciplina) {
          if (!$oDisciplina->getRegencia()->isObrigatoria()) {
            continue; 
          }
          $oResultadoFinal = $oDisciplina->getResultadoFinal();
          $oAprovadoConselho = $oResultadoFinal->getFormaAprovacaoConselho();
          
          if (!is_null($oAprovadoConselho)) {
            $iFormaAprovacao = $oAprovadoConselho->getFormaAprovacao();
            if ($iFormaAprovacao != 2) {
              $temAprovacaoConselho = true;
              break;
            }
          }
        }
      } catch (Exception $e) {
         // Se der erro no método OOP, continua para o fallback
         // Não damos rollback aqui para não cancelar a transação principal
      }

      // Método 2: Fallback - verificar diretamente no banco
      if (!$temAprovacaoConselho) {
        $sSqlConselho = "SELECT COUNT(*) as total
        FROM aprovconselho ac
        INNER JOIN diario d ON d.ed95_i_codigo = ac.ed253_i_diario
        INNER JOIN regencia r ON r.ed59_i_codigo = d.ed95_i_regencia
        WHERE d.ed95_i_aluno = (SELECT ed60_i_aluno FROM matricula WHERE ed60_i_codigo = {$iCodigoMatricula})
        AND r.ed59_c_condicao = 'OB'
        AND ac.ed253_aprovconselhotipo IN (1, 3)";

        $rsConselho = db_query($sSqlConselho);

        if ($rsConselho && pg_num_rows($rsConselho) > 0) {
          $oDadosConselho = db_utils::fieldsMemory($rsConselho, 0);
          if ($oDadosConselho->total > 0) {
            $temAprovacaoConselho = true;
          }
        }
      }
    }

    // Se tem aprovação pelo conselho, resultado final é SEMPRE 'A'
    if ($temAprovacaoConselho) {
      $resultadoFinal = 'A';  
    } else {
      // Esta chamada exige transação ativa:
      $diarioAlunoService = $oMatricula->getDiarioDeClasse()->getDiarioAlunoService();
      $areaProcedimento = $oMatricula->getDiarioDeClasse()->getAreaProcedimento();
      $resultadoFinal = $oMatricula->getDiarioDeClasse()->getResultadoFinal();

      // Se tem área de procedimento, busca resultado da área
      if (!is_null($areaProcedimento)) {
        $resultadoFinal = $diarioAlunoService->getDiarioAluno()->getResultadoFinal()->getResultadoFinal();
      }
    }

    // Se não tem resultado, retorna vazio
    if (empty($resultadoFinal)) {
      db_fim_transacao(false); // Fecha antes de retornar
      return '';
    }

    // Verifica progressão parcial para EJA
    if (!empty($iCodigoEnsino) && !empty($iAnoCalendario)) {
      $aTermos = DBEducacaoTermo::getTermoEncerramento($iCodigoEnsino, $resultadoFinal, $iAnoCalendario);

      if (count($aTermos) > 0) {
        $resultadoFinal = $aTermos[0]->sDescricao;
      } else {
        // Fallback: mapear siglas para texto
        $mapaResultados = array(
          'A' => 'APROVADO',
          'R' => 'REPROVADO',
          'D' => 'APROVADO COM PROGRESSÃO PARCIAL / DEPENDÊNCIA',
          'N' => 'NÃO AVALIADO',
          'P' => 'APROVADO COM PROGRESSÃO PARCIAL'
        );
        $resultadoFinal = isset($mapaResultados[$resultadoFinal]) ? $mapaResultados[$resultadoFinal] : $resultadoFinal;
      }
    }

    // Verifica progressão parcial
    if (is_null($areaProcedimento) && $oMatricula->getDiarioDeClasse()->aprovadoComProgressaoParcial()) {
      $aTermosAprovado = DBEducacaoTermo::getTermoEncerramento($iCodigoEnsino, 'A', $iAnoCalendario);
      $sLabelAprovado = count($aTermosAprovado) > 0 ? $aTermosAprovado[0]->sDescricao : 'APROVADO';
      $resultadoFinal = $sLabelAprovado . " (Progressão Parcial / Dependência)";
    }

    // Verifica recuperação
    if ($oMatricula->getDiarioDeClasse()->temRecuperacao()) {
      $resultadoFinal = 'EM RECUPERAÇÃO';
    }
    
    // Sucesso completo, fecha transação (commit)
    db_fim_transacao(false);
    return $resultadoFinal;

  } catch (Exception $e) {
    // Em caso de erro, faz Rollback e retorna vazio
    db_fim_transacao(true);
    return dd($e);
  }
}


function getProximaEtapaTexto($sEtapaAtual) {
  // Limpa espa os e coloca em mai sculo para garantir que encontre no array
  $sEtapa = trim(mb_strtoupper($sEtapaAtual, 'ISO-8859-1'));

  $aFluxo = array(
// --- Educação Infantil ---

      "BERÇARIO"     => "MATERNAL I",
      "MATERNAL I"   => "MATERNAL II",
      "MATERNAL II"  => "MATERNAL III",
      "MATERNAL III" => "1º PERÍODO",    
      "1º PERÍODO"   => "2º PERÍODO",
      "2º PERÍODO"   => "1º ANO",        

      // --- Ensino Fundamental ---
      "1º ANO" => "2º ANO",
      "2º ANO" => "3º ANO",
      "3º ANO" => "4º ANO",
      "4º ANO" => "5º ANO",
      "5º ANO" => "6º ANO",
      "6º ANO" => "7º ANO",
      "7º ANO" => "8º ANO",
      "8º ANO" => "9º ANO",
      "9º ANO" => "1º ANO DO ENSINO MÉDIO",

      // --- EJA (Ciclos e Alfabetização) ---
      // Conforme a imagem: CBA -> 1º Ciclo até 4º Ciclo
      "CIC BÁS DE ALFABET" => "1º CICLO",
      "1º CICLO"           => "2º CICLO",
      "2º CICLO"           => "3º CICLO",
      "3º CICLO"           => "4º CICLO",
      "4º CICLO"           => "1º ANO DO ENSINO MÉDIO" // 
  );

  // Se a etapa existir no mapa, retorna a pr xima. Se n o, retorna texto gen rico.
  if (isset($aFluxo[$sEtapa])) {
      return $aFluxo[$sEtapa];
  }
  
  return "ETAPA SEGUINTE";
} 

function ajustaNome($nome){  
  $nomemin = mb_strtolower($nome, 'ISO-8859-1');
  $novonome = explode(" ", $nomemin);
  $nome = "";
  foreach ($novonome as $palavra){
    if(strlen($palavra) > 2){
      $nome .= ucfirst($palavra) . " ";
    }else{
      $nome .= $palavra . " ";
    }
  }
  return trim($nome);
}

function testa($var){
  echo "<pre>";
  print_r($var);
  echo "</pre>";
}

$oDaoPeriodoEscola = new cl_periodoescola;

$oJson       = new services_json();
$oParametros = new stdClass();
$oGet        = db_utils::postMemory($_GET);

$oParametros->aMatriculas      = $oJson->decode(str_replace("\\", "", $oGet->aMatriculas));
$oGet->sDiretor                = base64_decode($oGet->sDiretor);
$aDiretor                      = explode('|', $oGet->sDiretor);
$oParametros->sDiretor         = '';
$oParametros->sCargo           = '';
$oParametros->lTemDiretor      = false;

$oParametros->dtEmissao    =$oGet->dtAtual; 

/**
 * Verifica se foi informado diretor
 */
if (count($aDiretor) > 1) {
  $oParametros->sDiretor = $aDiretor[1];
  $oParametros->sCargo   = $aDiretor[0];
  /*if (isset($aDiretor[2]) && !empty($aDiretor[2])) {
    $oParametros->sCargo .= "({$aDiretor[2]})";
  }*/
  $oParametros->lTemDiretor = true;
}

//$oParametros->lExibeGradeAluno = $oGet->lExibeGradeAluno == 'S' ? true : false; 
$oParametros->lExibeGradeAluno = false;
$oParametros->iAlturaLinha     = 4;

$oParametros->sObservacao = "";


if (isset($oGet->sObservacao) && !empty($oGet->sObservacao)) {


    $sObservacaoDecoded = base64_decode(str_replace(' ', '+', $oGet->sObservacao));


    $sObservacaoLimpa = db_stdClass::db_stripTagsJsonSemEscape($sObservacaoDecoded);


    if (mb_detect_encoding($sObservacaoLimpa . 'x', 'UTF-8', 'ISO-8859-1') == 'UTF-8') {
        $oParametros->sObservacao = utf8_decode($sObservacaoLimpa);
    } else {
        $oParametros->sObservacao = $sObservacaoLimpa;
    }
}

$oTurma = TurmaRepository::getTurmaByCodigo($oGet->iTurma);
$aTurno = array();

$aTurno[] = $oTurma->getTurno()->getCodigoTurno();
if ($oTurma->temTurnoAdicional() != "") {
  $aTurno[] = $oTurma->getTurnoAdicional()->getCodigoTurno();
}

$sCamposHorarioTurno = "min(ed17_h_inicio) as hora_inicio, max(ed17_h_fim) as hora_fim";
$sWhereHorarioTurno  = "     ed17_i_escola = {$oTurma->getEscola()->getCodigo()}";
$sWhereHorarioTurno .= " and ed17_i_turno in(".implode(',', $aTurno).")";
$sSqlHorarioTurno    = $oDaoPeriodoEscola->sql_query(null, $sCamposHorarioTurno, null, $sWhereHorarioTurno);
$rsHorarioTurno      = $oDaoPeriodoEscola->sql_record($sSqlHorarioTurno);

if ($oDaoPeriodoEscola->numrows == 0) {
  db_redireciona("db_erros.php?fechar=true&db_erro="._M('educacao.escola.edu2_atestadofrequencia.horario_turma_nao_encontrado'));
}
$oDadosHorarioTurno = db_utils::fieldsMemory($rsHorarioTurno, 0);

$aGradeHorario = array();

if ($oParametros->lExibeGradeAluno) {
  $sCamposGradeHorario = "ed17_i_turno, ed17_i_periodoaula, ed17_h_inicio, ed17_h_fim, ed15_c_nome ";
  $sSqlGradeHorario    = $oDaoPeriodoEscola->sql_query("", $sCamposGradeHorario, "ed15_i_sequencia,ed08_i_sequencia", $sWhereHorarioTurno);
  $rsGradeHorario      = $oDaoPeriodoEscola->sql_record($sSqlGradeHorario);
  $iLinhas             = $oDaoPeriodoEscola->numrows;

  if ($iLinhas == 0) {
    db_redireciona("db_erros.php?fechar=true&db_erro="._M('educacao.escola.edu2_atestadofrequencia.grade_horario_nao_encontrada'));
  }

  for ($i = 0; $i < $iLinhas; $i++) {
    $oDadosGradeHorario = db_utils::fieldsMemory($rsGradeHorario, $i);
    $oGradeHorario      = new stdClass();

    $oGradeHorario->iPeriodo    = $oDadosGradeHorario->ed17_i_periodoaula;
    $oGradeHorario->sTurno      = $oDadosGradeHorario->ed15_c_nome;
    $oGradeHorario->sHoraInicio = $oDadosGradeHorario->ed17_h_inicio;
    $oGradeHorario->sHoraFim    = $oDadosGradeHorario->ed17_h_fim;
    $oGradeHorario->lPrincipal  = true;

    if ($oTurma->getTurno()->getCodigoTurno() != $oDadosGradeHorario->ed17_i_turno) {
      $oGradeHorario->lPrincipal = false;
    }
    $aGradeHorario[] = $oGradeHorario;
  }
}

$aParagrafos  = array();
$aDadosAlunos = array();

foreach ($oParametros->aMatriculas as $oMat) {

  $oParagrafo                         = new libdocumento(5009);
  $oMatricula                         = new Matricula($oMat->iMatricula);

  try {
    $oDataNascimento                    = new DBDate($oMatricula->getAluno()->getDataNascimento());
    $oParagrafo->dia_nascimento         = $oDataNascimento->getDia();
    $oParagrafo->mes_extenso_nascimento = DBDate::getMesExtenso((int)$oDataNascimento->getMes());
    $oParagrafo->mes_numeral_nascimento = $oDataNascimento->getMes();
    $oParagrafo->ano_nascimento         = $oDataNascimento->getAno();
  } catch( Exception $oErro ) {
    $oParagrafo->dia_nascimento         = "";
    $oParagrafo->mes_extenso_nascimento = "";
    $oParagrafo->mes_numeral_nascimento = "";
    $oParagrafo->ano_nascimento         = "";
  }
  $aFiliacao                          = array();

  if ($oMatricula->getAluno()->getNomeMae() != '') {    
    $aFiliacao[] = ajustaNome($oMatricula->getAluno()->getNomeMae());
  }
  if ($oMatricula->getAluno()->getNomePai() != '') {
    $aFiliacao[] = ajustaNome($oMatricula->getAluno()->getNomePai());
  }

  $oParagrafo->naturalidade         = ajustaNome($oMatricula->getAluno()->getNaturalidade()->getNome());
  $oParagrafo->estado_naturalidade  = "";
  $oParagrafo->uf_naturalidade      = "";

  if ( !empty($oParagrafo->naturalidade) ) {
    $oParagrafo->estado_naturalidade  = $oMatricula->getAluno()->getNaturalidade()->getUF()->getNomeEstado();
    $oParagrafo->uf_naturalidade      = $oMatricula->getAluno()->getNaturalidade()->getUF()->getUF();
  }

  $oParagrafo->aluno                  = $oMatricula->getAluno()->getNome();
  
  // <<< IMPORTANTE: SALVANDO A DATA DE NASCIMENTO PARA USAR NO TEXTO DEPOIS >>>
  $oParagrafo->nascimento             = $oMatricula->getAluno()->getDataNascimento();
  
  $oParagrafo->filiacao               = implode(' e ', $aFiliacao);
  $oParagrafo->etapa                  = $oMatricula->getEtapaDeOrigem()->getNome();
  $oParagrafo->turma                  = $oMatricula->getTurma()->getDescricao();
  $oParagrafo->curso                  = $oMatricula->getTurma()->getBaseCurricular()->getCurso()->getNome();
  $oParagrafo->turno                  = $oMatricula->getTurma()->getTurno()->getDescricao();
  $oParagrafo->hora_inicial           = $oDadosHorarioTurno->hora_inicio;
  $oParagrafo->hora_final             = $oDadosHorarioTurno->hora_fim;
  $aParagrafos[]                      = $oParagrafo->getDocParagrafos();

  $oDadosAlunos                       = new stdClass();
  
  // <<< IMPORTANTE: CLONANDO O OBJETO PARA ELE N O SER SOBRESCRITO NO LOOP >>>
  $oDadosAlunos->oParagrafoObjeto     = clone $oParagrafo; 
  
  $oDadosAlunos->aParagrafo           = $oParagrafo->getDocParagrafos();
  $oDadosAlunos->sObservacaoMatricula = $oMatricula->getObservacao();

  $oDadosAlunos->xetapa = trim($oMatricula->getEtapaDeOrigem()->getNome());
  $oDadosAlunos->xurma = trim($oMatricula->getTurma()->getDescricao());
  $oDadosAlunos->xurso = trim($oMatricula->getTurma()->getBaseCurricular()->getCurso()->getNome());
  $oDadosAlunos->xurno = trim($oMatricula->getTurma()->getTurno()->getDescricao());
  
  $oDadosAlunos->sSituacao = $oMatricula->getSituacao();
  $oDadosAlunos->iMatricula = $oMat->iMatricula;

  // <<< AQUI EST  A CORRE  O PRINCIPAL: CAPTURANDO A SITUA  O >>>
  $oDadosAlunos->sSituacao = $oMatricula->getSituacao();
  
  $aDadosAlunos[]                     = $oDadosAlunos;
}

if (count($aParagrafos) == 0) {
  db_redireciona("db_erros.php?fechar=true&db_erro="._M('educacao.escola.edu2_atestadofrequencia.matricula_nao_encontrada'));
}

$oPdf = new PDF();
$oPdf->AliasNbPages();
$oPdf->setFillColor(220);
$oPdf->Open();
$oPdf->SetAutoPageBreak(false, 10);

if (db_getsession("DB_modulo") != 1100747) {
  $aTelefones = $oTurma->getEscola()->getTelefones();
  $head2      = "Escola: {$oTurma->getEscola()->getNome()}";
  if (count($aTelefones) > 0) {
    $head3 = "Telefone: {$aTelefones[0]->iDDD} {$aTelefones[0]->iNumero}";
  }
}

$sObservacao  = $oParametros->sObservacao;

// =============================================================================
// SEGUNDO LOOP (IMPRESS O)
// =============================================================================
foreach ($aDadosAlunos as $oDadosAlunos) {  

  $oPdf->addpage("P");

  // Recuperamos os dados espec ficos deste aluno que salvamos no objeto
  $oParag = $oDadosAlunos->oParagrafoObjeto;

  // Formata a data de nascimento
  $sNascimento = new DBDate($oParag->nascimento);
  $sDataNascFormatada = $sNascimento->convertTo('d/m/Y');
  $iAnoTurma = $oTurma->getCalendario()->getAnoExecucao();

  // Texto base com os dados corretos deste aluno
  $sTextoBase = "Declaro para devidos fins, que o(a) aluno(a) {$oParag->aluno}, nascido(a) em {$sDataNascFormatada}, filho(a) de {$oParag->filiacao}, concluiu o(a) {$oDadosAlunos->xetapa} do(a) {$oDadosAlunos->xurso}, turno {$oDadosAlunos->xurno} nesta escola, no ano de {$iAnoTurma}.";

  $complemento = "";
  
  $codigomatricula = $oDadosAlunos->iMatricula;

  if (!isset($iAnoCalendario)) {
    // Se não tiver, busca rápido
    $sSqlDadosExtras = "SELECT c.ed52_i_ano, ce.ed29_i_ensino
                        FROM matricula m
                        INNER JOIN turma t ON t.ed57_i_codigo = m.ed60_i_turma
                        INNER JOIN calendario c ON c.ed52_i_codigo = t.ed57_i_calendario
                        INNER JOIN base b ON b.ed31_i_codigo = t.ed57_i_base
                        INNER JOIN cursoedu ce ON ce.ed29_i_codigo = b.ed31_i_curso
                        WHERE m.ed60_i_codigo = {$codigomatricula}";
    $rsDadosExtras = db_query($sSqlDadosExtras);    // Validação extra para evitar erro se o SQL falhar
    if ($rsDadosExtras && pg_num_rows($rsDadosExtras) > 0) {
      $oDadosExtras = db_utils::fieldsMemory($rsDadosExtras, 0);
      $iAnoCalendario = $oDadosExtras->ed52_i_ano;
      $iCodigoEnsino = $oDadosExtras->ed29_i_ensino;
    } else {
      // Fallback caso não ache dados (evita erro na função abaixo)
      $iAnoCalendario = null;
      $iCodigoEnsino = null;
    }
  }
    // 2. Chama a função 
  $sResultadoFinalCalculado = buscaResultadoFinalEncerramento($codigomatricula, $iCodigoEnsino, $iAnoCalendario);
  // 3. Fallback: Se a função retornar vazio (ex: aluno evadido ou erro), usa o do banco
  // dd($sResultadoFinalCalculado);
  if (empty($sResultadoFinalCalculado)) {
      $sResultadoFinalCalculado = $ed60_c_situacao;
  }
  
  // Verifica a situa  o que salvamos no primeiro loop
  if ($sResultadoFinalCalculado == "APROVADO") {
      $sProximaEtapa = getProximaEtapaTexto($oDadosAlunos->xetapa);
      $complemento = " Foi aprovado(a) e está apto(a) a cursar o(a): {$sProximaEtapa}.";

  } elseif ($sResultadoFinalCalculado == "REPROVADO") {
      
      $complemento = " Foi reprovado(a) deverá repetir o(a): {$oDadosAlunos->xetapa}.";
  }

  $sTextoFinal = $sTextoBase . $complemento;

  $oPdf->setfont('arial','b',20);
  $oPdf->SetY($oPdf->getY() + 10);
  $oPdf->Cell(192, $oParametros->iAlturaLinha, "Declaração de Conclusão", 0, 1, "C");
  $oPdf->Ln($oParametros->iAlturaLinha * 2);
  $oPdf->Ln(6);

  $oPdf->setfont('arial','',14);
  $oPdf->setXY(16, $oPdf->GetY());
  $oPdf->multicell(180, $oParametros->iAlturaLinha + 4, $sTextoFinal, 0, "J", 0, 0);
  $oPdf->Ln($oParametros->iAlturaLinha * 2);
  $oPdf->setXY(16, $oPdf->GetY());

  $oParametros->sObservacao = '';
  if (!empty($oDadosAlunos->sObservacaoMatricula)) {
    $oParametros->sObservacao = "{$oDadosAlunos->sObservacaoMatricula}\n{$sObservacao}";
  } else if (empty($sObservacao)) {
    $oParametros->sObservacao = "..........................................................";
  } else {
    $oParametros->sObservacao = $sObservacao;
  }

  $oPdf->multicell(180, $oParametros->iAlturaLinha, "OBS.: {$oParametros->sObservacao}", 0, "J", 0, 0);

  $oPdf->Ln($oParametros->iAlturaLinha * 2);
  // if ($oParametros->lExibeGradeAluno) {

  //   if (((count($aGradeHorario) * $oParametros->iAlturaLinha) + $oPdf->GetY() +10 ) > $oPdf->h - 20) {
  //     $oPdf->AddPage();
  //   }

  //   $oPdf->setX(85);
  //   $oPdf->setfont('arial','b',9);
  //   $oPdf->Cell(50, $oParametros->iAlturaLinha, "TURNO PRINCIPAL", 1, 1, "C", 1);
  //   $lImprimeTurno = true;
  //   foreach ($aGradeHorario as $oGradeHorario) {

  //     $sString = "{$oGradeHorario->iPeriodo}  - {$oGradeHorario->sHoraInicio} / {$oGradeHorario->sHoraFim}";
  //     $oPdf->setfont('arial','',9);

  //     if ($oGradeHorario->lPrincipal) {

  //       if ($lImprimeTurno) {

  //           $oPdf->setX(85);
  //           $oPdf->Cell(50, $oParametros->iAlturaLinha, $oGradeHorario->sTurno, 1, 1, "C", 1);
  //           $lImprimeTurno = false;
  //       }
  //       $oPdf->setX(85);
  //       $oPdf->Cell(50, $oParametros->iAlturaLinha, $sString, 1, 1, "C");
  //     }
  //   }

    $oPdf->Ln();

  //   if ($oTurma->temTurnoAdicional() != "") {

  //     $oPdf->setX(85);
  //     $oPdf->setfont('arial','b',9);
  //     $oPdf->Cell(50, $oParametros->iAlturaLinha, "TURNO ADICIONAL", 1, 1, "C", 1);
  //     $lImprimeTurno = true;
  //     foreach ($aGradeHorario as $oGradeHorario) {

  //       $sString = "{$oGradeHorario->iPeriodo}  - {$oGradeHorario->sHoraInicio} / {$oGradeHorario->sHoraFim}";
  //       $oPdf->setfont('arial','',9);

  //       if (!$oGradeHorario->lPrincipal) {

  //         if ($lImprimeTurno) {

  //           $oPdf->setX(85);
  //           $oPdf->Cell(50, $oParametros->iAlturaLinha, $oGradeHorario->sTurno, 1, 1, "C", 1);
  //           $lImprimeTurno = false;
  //         }
  //         $oPdf->setX(85);
  //         $oPdf->Cell(50, $oParametros->iAlturaLinha, $sString, 1, 1, "C");
  //       }
  //     }

  //   }
  // }

  if ($oPdf->GetY() + 40 > $oPdf->h - 15) {
    $oPdf->AddPage();
  }


$aDataPartes = explode("/", $oParametros->dtEmissao);
$sDia = $aDataPartes[0];
$sMes = $aDataPartes[1];
$sAno = $aDataPartes[2];

$sMunicipio = ajustaNome($oTurma->getEscola()->getDepartamento()->getInstituicao()->getMunicipio());
$oPdf->Ln(6);

$DiaExtenso  = " {$sMunicipio}, " . $sDia . " de " . trim(DBDate::getMesExtenso((int)$sMes));
$DiaExtenso .= " de " . $sAno;

  $oPdf->Cell("192", $oParametros->iAlturaLinha + 6, $DiaExtenso, 0, 1, "C");
  $oPdf->ln($oParametros->iAlturaLinha * 3);
  $oPdf->Line(50, $oPdf->GetY(), 152, $oPdf->GetY());
  $oPdf->ln($oParametros->iAlturaLinha);

  if ($oParametros->lTemDiretor) {
    $oPdf->setfont('arial','',12);
    $oPdf->Cell("192", $oParametros->iAlturaLinha, ajustaNome($oParametros->sDiretor), 0, 1, "C");
    $oPdf->Cell("192", $oParametros->iAlturaLinha + 4, ajustaNome($oParametros->sCargo),   0, 1, "C");    
  }

}

$oPdf->Output();
?>
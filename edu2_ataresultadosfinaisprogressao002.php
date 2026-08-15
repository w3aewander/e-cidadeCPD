<?php
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


require_once(modification("libs/db_utils.php"));
require_once(modification("fpdf151/FpdfMultiCellBorder.php"));
/*PLUGIN DIARIO PROGRESSAO PARCIAL - Require db_funcoes*/
/*
 * Autor: Uemerson Santana
 * Data: 18/11/2025
 * Demanda: 17785
 * Razao: Adicionado require db_funcoes.php para permitir uso de funções de transação (db_inicio_transacao/db_fim_transacao)
 *        necessárias para validação de alunos evadidos via DiarioProgressaoParcial
 */
require_once(modification("dbforms/db_funcoes.php"));

define ('MSG_ATARESULTADOSFINAISPROGRESSAO002', "educacao.escola.edu2_ataresultadosfinaisprogressao002.");

$oGet                         = db_utils::postmemory($_GET);
$oGet->aTurmas                = str_replace('\\', "", $oGet->aTurmas);
$oGet->aTurmas                = JSON::create()->parse($oGet->aTurmas);
$oGet->sDiretor               = base64_decode($oGet->sDiretor);
$oGet->sSecretario            = base64_decode($oGet->sSecretario);
$oGet->sAssinaturaAdicicional = base64_decode($oGet->sAssinaturaAdicicional);

$oConfig                         = new stdClass();
$oConfig->sDiretor               = $oGet->sDiretor;
$oConfig->sSecretario            = $oGet->sSecretario;
$oConfig->sAssinaturaAdicicional = $oGet->sAssinaturaAdicicional;
$oConfig->sCargoAdicional        = $oGet->sCargoAdicional;
$oConfig->sTipoFrequencia        = $oGet->sTipoFrequencia;
$oConfig->iLimiteAlunosPagina    = 45;     // limita quebra de pagina
$oConfig->iLimiteRegenciaPagina  = 6;      // limita quebra de pagina
$oConfig->sColunaAvaliacao       = "AVAL"; // descrição da coluna de avaliação
/*
 * autor: Uemerson Santana
 * demanda: #17785
 * data: 03/09/2025
 * Alteração: Sempre mostrar percentual de frequência
 */
$oConfig->sColunaFaltas          = "% F";   // descrição da coluna de frequencia sempre percentual
$oConfig->iAlturaInicioAlunos    = 43;  // eixo y onde os alunos começaram a serem impressos
$oConfig->iAlturaLimiteAlunos    = 258; // altura limite do eixo y que devemos quebrar a pagina dos alunos
$oConfig->iAlturaRetanguloAlunos = 216; // Height para desenho dos retangulos
$oConfig->iLarguraRetangulo      = 194; // Width para desenho dos retangulos
$oConfig->iAlturaRodape          = 261; // eixo y que devemos escrever rodapé

$oConfig->iLarguraColunaAluno    = 55; // largura coluna aluno (reduzida para acomodar ano)
$oConfig->iColunaDisciplina      = 20; // largura coluna disciplina
$oConfig->iColunaAvaliacao       = 12; // largura coluna avaliação
$oConfig->iColunaFrequencia      = 8;  // largura coluna frequencia
$oConfig->iColunaAno             = 12;  // largura coluna ano (ajustada de 8 para 12)
$oConfig->iColunaRF              = 9;  // largura coluna resultado final


$aDadosRelatorio = array();

try {

  $oMsgErro = new stdClass();
  if ( empty($oGet->iEscola) ) {
    throw new Exception( _M(MSG_ATARESULTADOSFINAISPROGRESSAO002 . "informe_escola") );
  }

  if ( empty($oGet->iCalendario) ) {
    throw new Exception( _M(MSG_ATARESULTADOSFINAISPROGRESSAO002 . "informe_calendario") );
  }

  if ( empty($oGet->aTurmas) ) {
    throw new Exception( _M(MSG_ATARESULTADOSFINAISPROGRESSAO002 . "informe_turmas") );
  }

  $oEscola     = EscolaRepository::getEscolaByCodigo($oGet->iEscola);
  $oCalendario = CalendarioRepository::getCalendarioByCodigo($oGet->iCalendario);
  /*
   * autor: Uemerson Santana
   * demanda: #17785
   * data: 03/09/2025
   * Alteração: Usar data final do calendário em vez da data atual
   */
  $oData       = $oCalendario->getDataFinal();
  $iAno        = $oCalendario->getAnoExecucao();

  $oConfig->sMunicipio = $oEscola->getDepartamento()->getInstituicao()->getMunicipio();
  $oConfig->sData = $oData->dataPorExtenso();
  $oConfig->oData = $oData;

  foreach ($oGet->aTurmas as $oStdTurmaEtapa) {

    $oTurma  = TurmaRepository::getTurmaByCodigo($oStdTurmaEtapa->iTurma);
    $oEtapa  = EtapaRepository::getEtapaByCodigo($oStdTurmaEtapa->iEtapa);
    $oEnsino = $oEtapa->getEnsino();
    $iEnsino = $oEnsino->getCodigo(); // código do ensino - usado para buscar Termo de Encerramento

    $aTermosAprovado  = DBEducacaoTermo::getTermoEncerramento($iEnsino, 'A', $iAno);
    $aTermosReprovado = DBEducacaoTermo::getTermoEncerramento($iEnsino, 'R', $iAno);
    $aTermos          =  array_merge($aTermosAprovado, $aTermosReprovado);

    $oDadosTurma                     = new stdClass();
    $oDadosTurma->sTurma             = $oTurma->getDescricao();
    $oDadosTurma->sEtapa             = $oEtapa->getNome();
    $oDadosTurma->sTipoEnsino        = $oEnsino->getNome();
    $oDadosTurma->iAno               = $iAno;
    $oDadosTurma->aRegenciasPagina   = array(); // controle das regencias que serão impressas por pagina
    $oDadosTurma->aRegencias         = array();
    $oDadosTurma->aTermoEncerramento = $aTermos;

    $aAlunosTurma = array();
    $aRegencias   = $oTurma->getDisciplinasPorEtapa($oEtapa);

    $iPagina   = 1;
    $iContador = 0;
    foreach ($aRegencias as $oRegencia) {

      $aProgressoes = ProgressaoParcialAlunoRepository::getProgressoesVinculadasRegencia($oRegencia, 1);

      if ( count($aProgressoes) == 0 ) {
        continue;
      }

      $oDadosRegencia               = new stdClass();
      $oDadosRegencia->iRegencia    = $oRegencia->getCodigo();
      $oDadosRegencia->sDescricao   = $oRegencia->getDisciplina()->getNomeDisciplina();
      $oDadosRegencia->sAbreviatura = $oRegencia->getDisciplina()->getAbreviatura();
      $oDadosTurma->aRegencias[]    = $oDadosRegencia;

      if ( $iContador == $oConfig->iLimiteRegenciaPagina ) {

        $iPagina  ++;
        $iContador = 1;
      }
      $iContador ++;
      $oDadosTurma->aRegenciasPagina[$iPagina][] = $oRegencia->getCodigo();
    }

    foreach ($oDadosTurma->aRegencias as $oDadosRegenciaComAlunos) {

      $oRegencia    = RegenciaRepository::getRegenciaByCodigo( $oDadosRegenciaComAlunos->iRegencia );
      $aProgressoes = ProgressaoParcialAlunoRepository::getProgressoesVinculadasRegencia($oRegencia, 1);

      foreach ($aProgressoes as $oProgressaoParcial) {

        $sResultadoFinal      = "";
        $iTotalFalta         = "";
        $mAvaliacao          = "";
        $nPercentualFrequencia = "";

        $oVinculoRegencia = $oProgressaoParcial->getVinculoPorRegencia($oRegencia);
        $oResultadoProgressao = $oVinculoRegencia->getResultadoFinal();
        if ( $oResultadoProgressao instanceof ProgressaoParcialAlunoResultadoFinal ) {

          $sResultadoFinal = retornarTermoResultadoFinal($aTermos, $oResultadoProgressao->getResultado() );
          $iTotalFalta     = $oResultadoProgressao->getTotalFalta();
          $mAvaliacao      = $oResultadoProgressao->getNota();

          /*
           * autor: Uemerson Santana
           * demanda: #17785
           * data: 03/09/2025
           * Alteração: Calcular percentual de frequência baseado nas aulas efetivamente dadas pelo professor
           */
          // Calcular percentual de frequência baseado nas aulas lançadas
          $iTotalAulas = $oRegencia->getTotalDeAulas();
          $nPercentualFrequencia = $iTotalAulas > 0 ? floor(($iTotalAulas - $iTotalFalta) / $iTotalAulas * 100) : 0;
        }

        /*
         * Autor: Uemerson Santana
         * Data: 18/11/2025
         * Demanda: 17785
         * Razao: Adicionada validação de alunos evadidos. Alunos marcados como evadidos no diário devem aparecer
         *        com resultado "Eva" (em vez de "Reprovado"), frequência "-" (em vez de 100%) e avaliação "-".
         *        Corrige problema onde alunos evadidos apareciam como reprovados com 100% de frequência na ATA.
         */
        /*PLUGIN DIARIO PROGRESSAO PARCIAL - Validação alunos evadidos*/

        /*
         * Autor: Uemerson Santana
         * Data: 18/11/2025
         * Demanda: 17785
         * Razao: Logs temporários para análise detalhada - ponto de leitura da ATA
         */
        $iCodigoAluno = $oProgressaoParcial->getAluno()->getCodigoAluno();
        $sNomeAluno = $oProgressaoParcial->getAluno()->getNome();

        db_inicio_transacao();
        $oDiarioProgressaoParcial = new DiarioProgressaoParcial( $oProgressaoParcial, $oRegencia );
        db_fim_transacao(false);

        if ( $oDiarioProgressaoParcial instanceof DiarioProgressaoParcial ) {

          $oResultadoDiario = $oDiarioProgressaoParcial->getResultadoFinal();

          if ( $oConfig->sTipoFrequencia == 'P' ) {
            $nPercentualFrequencia = $oResultadoDiario->getPercentualFrequencia();
          }

          $oAmparo = $oDiarioProgressaoParcial->getAmparo();

          if ( !is_null($oAmparo->getCodigo()) && !is_null($oAmparo->getCodigoConvencaoAmparo()) ) {
            $nPercentualFrequencia = '-';
            $mAvaliacao           = 'SUP';
          }

          if ( $oDiarioProgressaoParcial->isEvadido() ) {
            $sResultadoFinal      = 'Eva';
            $nPercentualFrequencia = '-';
            $mAvaliacao            = '-';
          }

          /*
           * Autor: Uemerson Santana
           * Data: 19/11/2025
           * Demanda: 17785
           * Razao: Detectar e corrigir inconsistência: alunos com frequência < 75% mas resultado = A.
           *        REGRA ESPECIAL: Se percentual = 0% E total de faltas = 0 (sem lançamento),
           *        considera-se 100% de presença e mantém aprovação.
           *        Caso contrário, força reprovação quando frequência real < 75%.
           */
          $nPercentualFreqDiario = $oResultadoDiario->getPercentualFrequencia();
          $sResultadoFinalDiario = $oResultadoDiario->getResultadoFinal();
          $sResultadoFreqDiario = $oResultadoDiario->getResultadoFrequencia();
          $nValorAprovDiario = $oResultadoDiario->getValorAprovacao();

          // Busca o total de faltas lançadas para verificar se há lançamentos
          $iTotalFaltasLancadas = $oDiarioProgressaoParcial->getTotalFaltas();

          /*
           * Autor: Uemerson Santana
           * Data: 19/11/2025
           * Demanda: 17785
           * Razao: Correção cirúrgica para frequência 0%:
           *        - Se percentual = 0% E total de faltas = 0: considera 100% de presença (sem faltas = presente)
           *        - Caso contrário: mantém o percentual calculado
           */
          if ($nPercentualFreqDiario == 0 && $iTotalFaltasLancadas == 0) {
            // Sem lançamento de faltas = 100% de frequência
            $nPercentualFrequencia = 100;
          }

          // Detecta inconsistência: frequência < 75% mas resultado final = A
          // APÓS ajuste para casos sem lançamento de faltas
          $lInconsistencia = ($nPercentualFreqDiario < 75 && $nPercentualFreqDiario > 0 && $sResultadoFinalDiario == 'A');

          if ($lInconsistencia) {

            // /*
            //  * Autor: Uemerson Santana
            //  * Data: 19/11/2025
            //  * Demanda: 17785
            //  * Razao: Log pontual para diagnosticar inconsistências de frequência/aprovação diretamente na ATA.
            //  */
            // $sLogInconsistencia = modification("resultado_final_progressao_inconsistencia_{$iCodigoAluno}_" . date('YmdHis') . ".txt");
            // $oLogInconsistencia = fopen($sLogInconsistencia, 'a');
            // if ($oLogInconsistencia) {
            //   fwrite($oLogInconsistencia, "========================================\n");
            //   fwrite($oLogInconsistencia, "Registro de inconsistência - ATA Progressão Parcial\n");
            //   fwrite($oLogInconsistencia, "========================================\n");
            //   fwrite($oLogInconsistencia, "Data/Hora: " . date('d/m/Y H:i:s') . "\n");
            //   fwrite($oLogInconsistencia, "Aluno: {$sNomeAluno} (Código: {$iCodigoAluno})\n");
            //   fwrite($oLogInconsistencia, "Regência: " . $oRegencia->getCodigo() . "\n");
            //   fwrite($oLogInconsistencia, "Disciplina: " . $oRegencia->getDisciplina()->getNomeDisciplina() . "\n");
            //   fwrite($oLogInconsistencia, "Percentual Frequência: {$nPercentualFreqDiario}%\n");
            //   fwrite($oLogInconsistencia, "Total Faltas Lançadas: {$iTotalFaltasLancadas}\n");
            //   fwrite($oLogInconsistencia, "Resultado Final (Diário): {$sResultadoFinalDiario}\n");
            //   fwrite($oLogInconsistencia, "Resultado Frequência (Diário): {$sResultadoFreqDiario}\n");
            //   fwrite($oLogInconsistencia, "Valor Avaliação (Diário): {$nValorAprovDiario}\n");
            //   fwrite($oLogInconsistencia, "========================================\n\n");
            //   fclose($oLogInconsistencia);
            // }

            /*
             * Autor: Uemerson Santana
             * Data: 19/11/2025
             * Demanda: 17785
             * Razao: Correção cirúrgica apenas na exibição do relatório. Força resultado final = 'R' quando
             *        frequência < 75% (com faltas lançadas), mantendo o percentual real de frequência
             *        para transparência. Não altera dados no banco de dados.
             */
            // Força resultado final = 'R' apenas na exibição do relatório
            $sResultadoFinal = retornarTermoResultadoFinal($aTermos, 'R');
          }
        }

        $oDadosAluno                   = new stdClass();
        $oDadosAluno->iCodigo          = $iCodigoAluno;
        $oDadosAluno->sNome            = $oProgressaoParcial->getAluno()->getNome();
        $oDadosAluno->iRegencia        = $oRegencia->getCodigo();
        $oDadosAluno->sResultadoFinal  = $sResultadoFinal;
        /*
         * autor: Uemerson Santana
         * demanda: #17785
         * data: 03/09/2025
         * Alteração: Armazenar percentual de frequência em vez de faltas absolutas
         */
        $oDadosAluno->iTotalFalta      = $nPercentualFrequencia; // Agora armazena percentual
        $oDadosAluno->mAvaliacao       = $mAvaliacao;

        $aAlunosTurma[$iCodigoAluno][] = $oDadosAluno;
      }

    }

    uasort($aAlunosTurma, 'ordenaAlunoNome');
    $oDadosTurma->aAlunosTurma = $aAlunosTurma;
    $aDadosRelatorio[]         = $oDadosTurma;
  }

} catch ( Exception $e) {

  $sMsg = urlencode($e->getMessage());
  db_redireciona('db_erros.php?fechar=true&db_erro=' . $sMsg);
}

function retornarTermoResultadoFinal($aTermos, $sValorAproveitamento ) {

  foreach ($aTermos as $oTermo) {

    if ($sValorAproveitamento == $oTermo->sReferencia) {
      return $oTermo->sAbreviatura;
    }
  }
}

TurmaRepository::removeAll();
EtapaRepository::removeAll();
ProgressaoParcialAlunoRepository::removeAll();



/** ******************************************************************************************************* *
 ** ************************************** INICIO ESCRITA DO PDF ****************************************** *
 ********************************************************************************************************** */

$oPdf = new FpdfMultiCellBorder('P');
$oPdf->Open();
$oPdf->AliasNbPages();
$oPdf->setExibeBrasao(true);
$oPdf->exibeHeader(true);
$oPdf->SetAutoPageBreak(false, 10);
$oPdf->SetFillColor(225);
$oPdf->SetMargins(8, 10);
$oPdf->mostrarRodape(true);
$oPdf->mostrarTotalDePaginas(true);

foreach ($aDadosRelatorio as $oDadosRelatorio) {

  $sMsg  = "Aos {$oConfig->oData->getDia()} dias do mês de {$oConfig->oData->getMesExtenso($oConfig->oData->getMes())}";
  $sMsg .= " de {$oConfig->oData->getAno()}, encerrou-se a apuração de resultados dos alunos de progressão parcial da turma";
  $sMsg .= " {$oDadosRelatorio->sTurma} do {$oDadosRelatorio->sTipoEnsino} deste estabelecimento, com os seguintes resultados:";

  $head1 = 'Ata de Resultados Finais de Progressão Parcial';
  $head3 = $sMsg;

  foreach ($oDadosRelatorio->aRegenciasPagina as $aListaRegencias) {

    imprimirCabecalho($oPdf, $oConfig, $oDadosRelatorio, $aListaRegencias);
    imprimirAluno($oPdf, $oConfig, $oDadosRelatorio, $aListaRegencias);
    imprimirRodape($oPdf, $oConfig, $oDadosRelatorio->aTermoEncerramento);
  }

}

/**
 * Imprime colunas em branco
 * @param  FPDF     $oPdf              instância de fpdf
 * @param  stdClass $oConfig           dados padrão do relatorio
 * @param  integer  $iImprimirEmBranco numero de colunas
 * @param  boolean  $lPinta            se deve pintar alinha
 * @param  integer  $iAlturaLinha      altura que a linha deve ter
 */
function imprimirColunaDisciplinaEmBranco($oPdf, $oConfig, $iImprimirEmBranco, $lPinta = false, $iAlturaLinha = 4, $lDividir = false) {

  for ($i = 1; $i <= $iImprimirEmBranco; $i++) {

    if ( $lDividir ) {

      $oPdf->Cell($oConfig->iColunaAvaliacao,  $iAlturaLinha, "", 1 ,0, 0 , $lPinta);
      $oPdf->Cell($oConfig->iColunaFrequencia, $iAlturaLinha, "", 1 ,0, 0 , $lPinta);
    } else {
      $oPdf->Cell($oConfig->iColunaDisciplina, $iAlturaLinha, "", 1 ,0, 0 , $lPinta);
    }
  }
}

/**
 * Imprime o cabeçalho do relatorio
 * @param  FPDF     $oPdf              instância de fpdf
 * @param  stdClass $oConfig           dados padrão do relatorio
 * @param  stdClass $oDadosRelatorio   objeto com os dados da turma sendo impressa
 * @param  array    $aListaRegencias   lista das disciplinas da turma na pagina impressa
 */
function imprimirCabecalho($oPdf, $oConfig, $oDadosRelatorio, $aListaRegencias) {

  $iColunaDisciplina = $oConfig->iColunaDisciplina;
  $iColunaAvaliacao  = $oConfig->iColunaAvaliacao;
  $iColunaFrequencia = $oConfig->iColunaFrequencia;
  $iColunaRF         = $oConfig->iColunaRF;

  // Calcula se precisará colocar colunas em branco
  $iImprimirEmBranco = $oConfig->iLimiteRegenciaPagina - count($aListaRegencias);

  $oPdf->SetFont('Arial', 'B', 7);
  $oPdf->addPage();

  /*
   * autor: Uemerson Santana
   * demanda: #17785
   * data: 03/09/2025
   * Alteração: Ajustar largura do cabeçalho para incluir coluna do ano
   */
  // Primeira linha do cabeçalho
  $oPdf->Cell($oConfig->iLarguraColunaAluno + $oConfig->iColunaAno + 5, 4, "", 1, 0, 'R');
  foreach ($oDadosRelatorio->aRegencias as $oDadosRegencia) {

    if (in_array($oDadosRegencia->iRegencia, $aListaRegencias) ) {
      $oPdf->Cell($iColunaDisciplina, 4, "$oDadosRegencia->sAbreviatura", 1 ,0, 'C' );
    }
  }

  if ( $iImprimirEmBranco > 0 ) {
    imprimirColunaDisciplinaEmBranco($oPdf, $oConfig, $iImprimirEmBranco, false, 4);
  }
  $oPdf->Cell($iColunaRF, 4, "", "TRL" ,1, 'C' );

  /*
   * autor: Uemerson Santana
   * demanda: #17785
   * data: 03/09/2025
   * Alteração: Adicionar coluna "ANO" no cabeçalho
   */
  // Segunda Linha do cabeçalho
  $oPdf->Cell(5, 4, "Nº", 1, 0);
  $oPdf->Cell($oConfig->iLarguraColunaAluno, 4, "Nome do Aluno ", 1, 0, 'C');
  $oPdf->Cell($oConfig->iColunaAno, 4, "ANO", 1, 0, 'C');
  foreach ($oDadosRelatorio->aRegencias as $oDadosRegencia) {

    if (in_array($oDadosRegencia->iRegencia, $aListaRegencias) ) {

      $oPdf->Cell($iColunaAvaliacao,  4, $oConfig->sColunaAvaliacao, 1 ,0, 'C' );
      $oPdf->Cell($iColunaFrequencia, 4, $oConfig->sColunaFaltas,    1 ,0, 'C' );
    }
  }

  if ( $iImprimirEmBranco > 0 ) {
    imprimirColunaDisciplinaEmBranco($oPdf, $oConfig, $iImprimirEmBranco, false, 4, true);
  }

  $oPdf->Cell($iColunaRF, 4, "RF", "RLB" ,1, 'C' );

  /*
   * autor: Uemerson Santana
   * demanda: #17785
   * data: 03/09/2025
   * Alteração: Ajustar largura do retângulo para incluir coluna do ano
   */
  /*
   * Imprime o retangulo envolta dos alunos
   */
  $oPdf->Rect($oPdf->GetX(), $oPdf->GetY(), $oConfig->iLarguraRetangulo + $oConfig->iColunaAno, $oConfig->iAlturaRetanguloAlunos);
  $oPdf->SetFont('Arial', '', 7);

}

/**
 * Imprime os alunos da turma
 * @param  FPDF     $oPdf              instância de fpdf
 * @param  stdClass $oConfig           dados padrão do relatorio
 * @param  stdClass $oDadosRelatorio   objeto com os dados da turma sendo impressa
 * @param  array    $aListaRegencias   lista das disciplinas da turma na pagina impressa
 */
function imprimirAluno($oPdf, $oConfig, $oDadosRelatorio, $aListaRegencias) {

  $iImprimirEmBranco = $oConfig->iLimiteRegenciaPagina - count($aListaRegencias);
  $iNumero           = 1;
  $aNumerosImpressos = array();

  $oPdf->SetFont('Arial', '', 7);
  foreach ($oDadosRelatorio->aAlunosTurma as $aAlunoDisciplina) {

    $lPinta = (($iNumero % 2) == 0);

    $iNumeroAuxiliar = $iNumero;
    foreach ( $aAlunoDisciplina as $oDadosAluno ) {

      $iLinhasNomeAluno = $oPdf->NbLines($oConfig->iLarguraColunaAluno, $oDadosAluno->sNome);
      $iAlturaLinha     = 4;

      $iYInicio = $oPdf->GetY();

      if ($iLinhasNomeAluno > 1) {
        $iAlturaLinha = 4 * $iLinhasNomeAluno;
      }

      if (in_array($iNumeroAuxiliar, $aNumerosImpressos) ) {
        $iNumeroAuxiliar = '';
      }

      // capture starting X to compute relative positions (avoids hardcoded offsets)
      $iXStart = $oPdf->GetX();
      $oPdf->Cell(5, $iAlturaLinha, "{$iNumeroAuxiliar}", 1, 0, 0, $lPinta);
      $oPdf->MultiCell($oConfig->iLarguraColunaAluno, 4, $oDadosAluno->sNome, 1, 'L', $lPinta);
      /*
       * autor: Uemerson Santana
       * demanda: #17785
       * data: 03/09/2025
       * Alteração: Ajustar posição X para incluir coluna do ano (usar posição relativa)
       */
      $oPdf->SetXY($iXStart + 5 + $oConfig->iLarguraColunaAluno, $iYInicio);
      /*
       * autor: Uemerson Santana
       * demanda: #17785
       * data: 03/09/2025
       * Alteração: Imprimir coluna do ano de escolaridade
       */
      $oPdf->Cell($oConfig->iColunaAno, $iAlturaLinha, $oDadosRelatorio->sEtapa, 1, 0, 'C', $lPinta);

      $lImprimeResultadoFinal = false;
      foreach ($aListaRegencias as $iRegencia) {

        if ( $oDadosAluno->iRegencia == $iRegencia) {

          $lImprimeResultadoFinal = true;
          $oPdf->Cell( $oConfig->iColunaAvaliacao,  $iAlturaLinha,$oDadosAluno->mAvaliacao,  1, 0, 'C', $lPinta);
          /*
           * Autor: Uemerson Santana
           * Data: 18/11/2025
           * Demanda: 17785
           * Razao: Ajuste na impressão da frequência para não adicionar símbolo % quando o valor for "-" (alunos evadidos).
           *        Evita exibir "-%" no PDF, mostrando apenas "-" para casos de evasão ou amparo.
           */
          $sFrequencia = ($oDadosAluno->iTotalFalta === '-' || $oDadosAluno->iTotalFalta === '')
                         ? '-'
                         : "{$oDadosAluno->iTotalFalta}%";
          $oPdf->Cell( $oConfig->iColunaFrequencia, $iAlturaLinha, $sFrequencia, 1, 0, 'C', $lPinta);
        } else {
          imprimirColunaDisciplinaEmBranco($oPdf, $oConfig, 1, $lPinta, $iAlturaLinha, true);
        }
      }

      imprimirColunaDisciplinaEmBranco($oPdf, $oConfig, $iImprimirEmBranco, $lPinta, $iAlturaLinha, true);

      $sResultadoFinal = $oDadosAluno->sResultadoFinal;
      if (!$lImprimeResultadoFinal) {
        $sResultadoFinal = "";
      }

      $oPdf->Cell( $oConfig->iColunaRF, $iAlturaLinha, $sResultadoFinal, 1, 1, 'C', $lPinta);

      if ( $oPdf->getY() >= $oConfig->iAlturaLimiteAlunos) {

        imprimirRodape($oPdf, $oConfig, $oDadosRelatorio->aTermoEncerramento);
        imprimirCabecalho($oPdf, $oConfig, $oDadosRelatorio, $aListaRegencias);
      }
      $aNumerosImpressos[] = $iNumero;
    }
    $iNumero ++;
  }

  /*
   * Imprime linhas em branco
   */
  while ($oPdf->getY() <= $oConfig->iAlturaLimiteAlunos) {
    imprimirLinhasAlunosEmBranco($oPdf, $oConfig);
  }

}

/**
 * Imprime linhas em branco
 * @param  FPDF     $oPdf              instância de fpdf
 * @param  stdClass $oConfig           dados padrão do relatorio
 */
function imprimirLinhasAlunosEmBranco($oPdf, $oConfig) {

  $oPdf->Cell(5, 4, "", 1, 0, 0);
  $oPdf->Cell($oConfig->iLarguraColunaAluno, 4, "", 1, 0);
  /*
   * autor: Uemerson Santana
   * demanda: #17785
   * data: 03/09/2025
   * Alteração: Adicionar coluna em branco para ano nas linhas vazias
   */
  $oPdf->Cell($oConfig->iColunaAno, 4, "", 1, 0);
  imprimirColunaDisciplinaEmBranco($oPdf, $oConfig, $oConfig->iLimiteRegenciaPagina, false, 4, true);
  $oPdf->Cell( $oConfig->iColunaRF, 4, "", 1, 1);
}

/**
 * Imprime o rodapé da pagina
 * @param  FPDF     $oPdf                 instância de fpdf
 * @param  stdClass $oConfig              dados padrão do relatorio
 * @param  array    $aTermoEncerramento   Termos de encerramento
 */
function imprimirRodape($oPdf, $oConfig, $aTermoEncerramento) {

  /*
   * autor: Uemerson Santana
   * demanda: #17785
   * data: 03/09/2025
   * Alteração: Ajustar largura do rodapé para incluir coluna do ano
   */
  $iLargura       = $oConfig->iLarguraRetangulo + $oConfig->iColunaAno; //
  $iAltura        = 25;  // altura do quadro das assinaturas
  $iMeiaPagina    = 105; // metade da pagina contando as bordas
  $iTercoPagina   = 73;  // um terço da pagina

  $iLarguraQuadro1 = 65;  // quadro das legendas
  $iLarguraQuadro2 = 125; // quadro das assinaturas

  // Desenha os quadros
  $oPdf->SetY($oConfig->iAlturaRodape);
  $oPdf->Rect($oPdf->GetX(), $oPdf->GetY(), $iLargura, $iAltura);
  $oPdf->Rect($oPdf->GetX(), $oPdf->GetY(), $iLargura, $iAltura);
  $oPdf->Line($iTercoPagina, $oPdf->GetY(), $iTercoPagina, $oPdf->GetY() + 25 );

  /* ************************************************************************************** *
   * ******************************** QUADRO DAS LEGENDAS ********************************* *
   **************************************************************************************** */
  $oPdf->SetFont('Arial', 'B', 7);
  $oPdf->Cell($iLarguraQuadro1, 4, 'Legendas:', 0, 1);
  $oPdf->SetFont('Arial', '', 7);
  foreach ($aTermoEncerramento as $oTermo) {
    $oPdf->Cell($iLarguraQuadro1, 4, "{$oTermo->sAbreviatura} - {$oTermo->sDescricao}", 0, 1);
  }

  /*PLUGIN DIARIO PROGRESSAO PARCIAL - Legenda Eva - EVADIDO*/

  $sLegendaFaltas = $oConfig->sTipoFrequencia == 'T' ? 'F - Faltas' : 'F - Frequência (%)';
  $oPdf->Cell($iLarguraQuadro1, 4, "AVAL - Avaliação | {$sLegendaFaltas}", 0, 1);
  $oPdf->Cell($iLarguraQuadro1, 4, "RF - Resultado Final",   0, 1);


  /* ************************************************************************************** *
   * ******************************* QUADRO DAS ASSINATURAS ******************************* *
   **************************************************************************************** */
  $oPdf->SetXY(75, $oConfig->iAlturaRodape);
  $oPdf->SetFont('Arial', '', 6);
  $sTermoFinal = "E, para constar, foi lavrada esta ata.    {$oConfig->sMunicipio}, {$oConfig->sData}.";
  $oPdf->Cell($iLarguraQuadro2, 4, $sTermoFinal, 0, 0, 'C');

  $oPdf->ln(16);

  $aNomeDiretor = array();
  if ( !empty($oConfig->sDiretor) ) {
    $aNomeDiretor[] = $oConfig->sDiretor;
  }
  $aNomeDiretor[]  = "Diretor";
  $sNomeDiretor    = implode("\n", $aNomeDiretor);

  $aNomeSecretario = array();
  if ( !empty( $oConfig->sSecretario ) ) {
    $aNomeSecretario[] = $oConfig->sSecretario;
  }
  $aNomeSecretario[] = "Secretário";
  $sNomeSecretario   = implode("\n", $aNomeSecretario);
  $oPdf->SetFont('Arial', '', 5.5);
  if ( empty($oConfig->sAssinaturaAdicicional) ) {

    $oPdf->Line(75, $oPdf->GetY(), 136, $oPdf->GetY() );
    $oPdf->Line(138, $oPdf->GetY(), 200, $oPdf->GetY() );
    $oPdf->ln(0.5);

    $iAlturaAssinatura = $oPdf->getY();
    $oPdf->SetXY(75, $iAlturaAssinatura);
    $oPdf->MultiCell(60, 3, "{$sNomeDiretor}", 0, 'C');
    $oPdf->SetXY(138, $iAlturaAssinatura);
    $oPdf->MultiCell(60, 3, "{$sNomeSecretario}", 0, 'C');

  } else {

    $oPdf->Line(75,  $oPdf->GetY(), 115, $oPdf->GetY() );
    $oPdf->Line(117, $oPdf->GetY(), 157, $oPdf->GetY() );
    $oPdf->Line(159, $oPdf->GetY(), 200, $oPdf->GetY() );
    $oPdf->ln(0.5);

    $iAlturaAssinatura = $oPdf->getY();

    $oPdf->SetXY(75, $iAlturaAssinatura);
    $oPdf->MultiCell(40, 3, "{$sNomeDiretor}", 0, 'C');
    $oPdf->SetXY(117, $iAlturaAssinatura);
    $oPdf->MultiCell(40, 3, "{$sNomeSecretario}", 0, 'C');
    $oPdf->SetXY(159, $iAlturaAssinatura);

    $sAssinaturaAdicional = "{$oConfig->sAssinaturaAdicicional}\n{$oConfig->sCargoAdicional}";
    $oPdf->MultiCell(40, 3, "{$sAssinaturaAdicional}", 0, 'C');
  }

}

$oPdf->output();

/*
 * autor: Uemerson Santana
 * demanda: #17785
 * data: 03/09/2025
 * Alteração: Função para calcular total de aulas baseado no calendário
 */
function percfrequencia($calendar) {
    $sqla = "select ed52_c_descr, ed15_c_nome
             from calendario
             inner join turma on ed57_i_calendario = ed52_i_codigo
             inner join turno on ed15_i_codigo = ed57_i_turno
             where ed52_i_codigo = " . $calendar;

    $sql = pg_query($sqla);
    $resultado = pg_fetch_all($sql);
    $nome = $resultado[0]["ed52_c_descr"];
    $turno = substr($resultado[0]["ed15_c_nome"], 0, 5);
    $turno_completo = trim($resultado[0]["ed15_c_nome"]);

    if (substr($nome, 0, 12) == 'ED. INFANTIL' or substr($nome, 0, 17) == 'EDUCAÇÃO INFANTIL') {
        $auladadas = 200;
    } elseif (substr($nome, 0, 13) == 'ANOS INICIAIS' or substr($nome, 0, 20) == 'EN FUN ANOS INICIAIS') {
        $auladadas = 200;
    } elseif (substr($nome, 0, 11) == 'ANOS FINAIS' or substr($nome, 0, 18) == 'EN FUN ANOS FINAIS') {
        // Autor: Uemerson Santana | Data: 19/01/2026 | Demanda: 18059
        // Razão: Para Anos Finais, verificar se o turno é INTEGRAL para aplicar 1522 horas/aula
        //        ao invés de 1000 horas/aula fixas. Turno INTEGRAL tem carga horária maior.
        if (strtoupper($turno_completo) == 'INTEGRAL') {
            $auladadas = 1522;
        } else {
            $auladadas = 1000;
        }
    } elseif (substr($nome, 0, 17) == 'EJA ANOS INICIAIS' or substr($nome, 0, 12) == 'EJA INICIAIS') {
        $auladadas = 170;
    }

    if ($turno <> 'NOITE' and (substr($nome, 0, 15) == 'EJA ANOS FINAIS' or substr($nome, 0, 10) == 'EJA FINAIS')) {
        $auladadas = 1200;
    }

    if ($turno == 'NOITE' and (substr($nome, 0, 15) == 'EJA ANOS FINAIS' or substr($nome, 0, 10) == 'EJA FINAIS')) {
        $auladadas = 1000;
    }
    return $auladadas;
}

function ordenaAlunoNome($aArrayAtual, $aProximoArray) {
  return strcasecmp($aArrayAtual[0]->sNome, $aProximoArray[0]->sNome);
}

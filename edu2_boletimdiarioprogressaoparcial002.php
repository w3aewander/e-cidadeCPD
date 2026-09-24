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

require_once(modification("fpdf151/scpdf.php"));
require_once(modification("libs/db_utils.php"));
require_once(modification("libs/db_app.utils.php"));
require_once(modification("libs/db_stdlib.php"));
require_once(modification("libs/db_libdocumento.php"));
require_once(modification("libs/db_stdlibwebseller.php"));
require_once(modification("dbforms/db_funcoes.php"));
require_once(modification("std/DBDate.php"));

$oGet = db_utils::postMemory($_GET);

try{

  if ( empty($oGet->alunos) ) {
    throw new ParameterException( "Matrícula(s) não informada(s)." );
  }

  $oPdf = new scpdf();
  $oPdf->Open();
  $oPdf->AliasNbPages();
  $oPdf->SetMargins( 8, 8 );
  $oPdf->SetAutoPageBreak(false);
  $oPdf->SetFillColor(225,225,225);

  $oDepartamento = DBDepartamentoRepository::getDBDepartamentoByCodigo( db_getsession( "DB_coddepto" ) );

  /* Configurações padrões utilizadas para a impressão da grid de aproveitamentos */
  $oConfig                     = new stdClass();
  $oConfig->iLimiteLinha       = 194;
  $oConfig->sObservacao        = trim( $oGet->obs1 );
  $oConfig->lAssinaturaRegente = $oGet->assinaturaregente == 'S';
  $oConfig->aLegendas          = array();
  $oConfig->aLegendas["TF"]    = "TF - Total de Faltas";
  $oConfig->iTamanhoFonte      = 6;
  $oConfig->sImagem            = "imagens/files/" . $oDepartamento->getInstituicao()->getImagemLogo();

  $aMatriculasRecebidas = explode(',',  $oGet->alunos);
  $aAlunosComProgressao = array();

  /* Percorre as matrículas informadas da tela para montar a grade de aproveitamento do aluno */
  foreach ($aMatriculasRecebidas as $i => $iMatricula) {

    $oMatricula      = MatriculaRepository::getMatriculaByCodigo( $iMatricula );

    if ( $oMatricula->alunoPossuiProgressaoParcialNoAnoCursado() ) {

      $aAlunosComProgressao[] = $oMatricula;
      continue;
    }

    /* Controle para saber se imprime o segundo registro na mesma página */
    $lPrimeiRegistro = false;
    if ( ($i % 2) == 0 ) {
      $lPrimeiRegistro = true;
    }

    if ( $lPrimeiRegistro ) {
      $oPdf->AddPage('P');
    }

    montaCabecalho( $oPdf, $oMatricula, $oConfig, $lPrimeiRegistro );
    montaCorpo($oPdf, $oMatricula, $oConfig);
  }


  foreach ($aAlunosComProgressao as $oMatricula) {

    $oPdf->AddPage('P');
    montaCabecalho( $oPdf, $oMatricula, $oConfig, true );
    montaCorpo($oPdf, $oMatricula, $oConfig);

    montaGradeProgressao( $oPdf, $oMatricula, $oConfig);
  }

  $oPdf->output();

} catch( Exception $oErro ){
  db_redireciona("db_erros.php?fechar=true&db_erro={$oErro->getMessage()}");
}

/**
 * Monta o cabeçalho de acordo com o cabeçalho padrão 2 por página.
 * @param  FPDF      $oPdf
 * @param  Matricula $oMatricula
 * @param  stdClass  $oConfiguracao
 */
function montaCabecalho( $oPdf, $oMatricula, $oConfiguracao, $lPrimeiRegistro = true ){

  $oTurma     = $oMatricula->getTurma();
  $oEtapa     = $oMatricula->getEtapaDeOrigem();
  $sEtapa     = $oEtapa->getNome();
  $iAno       = $oTurma->getCalendario()->getAnoExecucao();
  $iAluno     = $oMatricula->getAluno()->getCodigoAluno();
  $iMatricula = $oMatricula->getCodigo();
  $oEscola    = $oTurma->getEscola();
  $oCurso     = $oTurma->getBaseCurricular()->getCurso();

  $iPosicaoYImagem  = 8;
  $iPosicaoXInicial = 35;

  $oPdf->SetFont('Arial', 'BI', 8);

  /**
   * Se for para adicionar o segundo boletim na mesma página, altera o eixo x para o meio da página
   * caso o primeiro boletim tenha ultrapassado o meio da página, adiciona uma nova página
   */
  if ( !$lPrimeiRegistro ) {

    if ($oPdf->getY() > 160 ) {
      $oPdf->AddPage('P');
    } else {

      $oPdf->setY( 160 );
      $iPosicaoYImagem = 160;
    }
  }

  $iYInicial     = $oPdf->getY();
  $iAlturaQuadro = 28;

  $oPdf->RoundedRect( 122, $oPdf->getY(), 80, $iAlturaQuadro, 2, 'DF', '123' );
  $oPdf->Image( $oConfiguracao->sImagem, 8, $iPosicaoYImagem, 25, 25 );
  $oPdf->Line( $oPdf->lMargin, ($iYInicial + $iAlturaQuadro), 200, ($iYInicial + $iAlturaQuadro) );

  $oPdf->setX( $iPosicaoXInicial );
  $oPdf->Cell( 80, 4, $oEscola->getDepartamento()->getInstituicao()->getDescricao(), 0, 1, 'L');
  $oPdf->setX( $iPosicaoXInicial );
  $oPdf->Cell( 80, 4, $oEscola->getNome(), 0, 1, 'L');
  $oPdf->setX( $iPosicaoXInicial );

  $oPdf->SetFont('Arial', '', 6);
  $sEndereco  = "{$oEscola->getEndereco()}, {$oEscola->getNumeroEndereco()}";
  $sEndereco .= $oEscola->getComplementoEndereco() == null ? '' : " - {$oEscola->getComplementoEndereco()}";
  $sEndereco .= " - {$oEscola->getBairro()}";

  $oPdf->Cell( 80, 4, $sEndereco, 0, 1, 'L');
  $oPdf->setX( $iPosicaoXInicial );
  $oPdf->Cell( 80, 4, "{$oEscola->getMunicipio()} - {$oEscola->getEstado()}", 0, 1, 'L');
  $oPdf->setX( $iPosicaoXInicial );

  $aTelefones = array();
  foreach ($oEscola->getTelefones() as $oTelefone ) {
    $aTelefones[] = "({$oTelefone->iDDD}) {$oTelefone->iNumero}";
  }
  $oPdf->Cell( 80, 4, implode('/', $aTelefones), 0, 1, 'L');

  $oPdf->SetX( $iPosicaoXInicial );
  $oPdf->Cell( 80, 4, $oEscola->getHomePage(), 0, 1, 'L');

  $oPdf->SetY($iYInicial);
  $oPdf->SetFont('Arial', '', 6);

  $oPdf->setX(122);
  $oPdf->Cell( 80,  3, "BOLETIM DE DESEMPENHO", 0, 1, 'C');
  $oPdf->setX(122);
  $oPdf->Cell( 80,  3, "Nome: {$oMatricula->getAluno()->getNome()}", 0, 1, 'L');
  $oPdf->setX(122);
  $oPdf->Cell( 80,  3, "Curso: {$oCurso->getCodigo()} - {$oCurso->getNome()}", 0, 1, 'L');
  $oPdf->setX(122);
  $oPdf->Cell( 80,  3, "Código Aluno: {$iAluno} Matrícula: {$iMatricula}", 0, 1, 'L');
  $oPdf->setX(122);
  $oPdf->Cell( 80,  3, "Etapa: {$sEtapa} Ano: {$iAno}", 0, 1, 'L');
  $oPdf->setX(122);
  $oPdf->Cell( 80,  3, "Turma: {$oMatricula->getTurma()->getDescricao()}", 0, 1, 'L');
  $oPdf->setX(122);

  $sSituacao = $oMatricula->getSituacao();

  if( $sSituacao == 'MATRICULADO' && $oMatricula->getTipo() == 'R' ) {
    $sSituacao = 'REMATRICULADO';
  }

  if( $oMatricula->isConcluida() ) {
    $sSituacao = "CONCLUÍDO";
  }
  $oPdf->Cell( 80, 3, "Situação: {$sSituacao}", 0, 1, 'L');
  $oPdf->SetY($iYInicial + 30);
}

function montaCorpo( $oPdf, $oMatricula, $oConfig ) {

  $oGrade = new RelatorioGradeAproveitamento($oPdf, $oMatricula, $oConfig->iLimiteLinha);
  $oGrade->montarGrade();
  $oGrade->imprimirMinimoParaAprovacao();
  $oGrade->imprimeResultadoFinal();
  $oGrade->imprimirLegendas();
  $oGrade->imprimeObservacoes($oConfig->sObservacao, true, true, true, true, true, true);

  if ( $oConfig->lAssinaturaRegente ) {

    $oPdf->Cell( $oConfig->iLimiteLinha, 10, '', 'LR', 1, 'C' );
    $oPdf->Line( 60, $oPdf->GetY(), 150, $oPdf->GetY() );
    $oPdf->SetFont('Arial', 'B', 6);
    $oPdf->Cell( $oConfig->iLimiteLinha, 4, "Professor Conselheiro", 'LRB', 1, 'C' );
  }
}

/**
 * Monta a grade contendo as avaliações e resultados do diário da progressão
 * @param  scpdf     $oPdf
 * @param  Matricula $oMatricula
 */
function montaGradeProgressao( $oPdf, Matricula $oMatricula, $oConfig ) {

  $iAno        = $oMatricula->getTurma()->getCalendario()->getAnoExecucao();
  $aProgressao = $oMatricula->getAluno()->getProgressaoParcial();

  $aTurmasProgressao = array();
  foreach ($aProgressao as $oProgressao) {

    foreach ($oProgressao->getVinculosProgressao() as $oProgressaoParcialVinculoDisciplina ) {

      if ( $oProgressaoParcialVinculoDisciplina->getAno() == $iAno  ) {

        $oTurma = $oProgressaoParcialVinculoDisciplina->getRegencia()->getTurma();

        $oDadosVinvulo              = new stdClass();
        $oDadosVinvulo->oRegencia   = $oProgressaoParcialVinculoDisciplina->getRegencia();
        $oDadosVinvulo->oProgressao = $oProgressao;
        
        $aTurmasProgressao[$oTurma->getCodigo()][] = $oDadosVinvulo;
      }
    }
  }

  $iNumeroTurmasProgressao = count($aTurmasProgressao);
  $iNumeroTurmasImpressa   = 1;

  $oPdf->ln(8);
  $oPdf->Cell( $oConfig->iLimiteLinha, 5, "PROGRESSÕES PARCIAIS", 1, 1, 'C' );
  foreach ($aTurmasProgressao as $aProgressoesTurma) {

    $oGrade = new RelatorioGradeAproveitamentoProgressao($oPdf, $aProgressoesTurma, $oMatricula, $oConfig->iLimiteLinha);
    $oGrade->montarGrade();
    $oGrade->imprimirMinimoParaAprovacao();
    $oGrade->imprimirLegendas();
    $oGrade->imprimirObservacao();

    if ( $iNumeroTurmasImpressa < $iNumeroTurmasProgressao ) {
      $oPdf->Cell( $oConfig->iLimiteLinha, 4, "", 1, 1, 'C', 1);
    }
    $iNumeroTurmasImpressa ++;
  }

}

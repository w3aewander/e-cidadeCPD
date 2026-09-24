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
require_once(modification("fpdf151/FpdfMultiCellBorder.php"));
require_once(modification("libs/db_utils.php"));
require_once(modification("libs/db_stdlibwebseller.php" ));
require_once(modification("dbforms/db_funcoes.php"));
require_once(modification("libs/JSON.php"));
require_once(modification("libs/db_libdocumento.php"));

function testa($var){
  echo "<pre>";
  print_r($var);
  echo "</pre>";
}

$oGet   = db_utils::postMemory( $_GET );
$oEtapa = EtapaRepository::getEtapaByCodigo( $oGet->iEtapa );
$oTurma = TurmaRepository::getTurmaByCodigo( $oGet->iTurma );

/**
 * Objeto com dados a serem utilizados para validações e impressão do relatório
 */
$oDadosRelatorio                        = new stdClass();
$oDadosRelatorio->aDisciplinasImpressao = explode( ",", $oGet->aRegencias );
$oDadosRelatorio->lTemNotaParcial       = VerParametroNota( db_getsession("DB_coddepto") ) == 'S';
$oDadosRelatorio->iMaximoPeriodosPagina = 9;
$oDadosRelatorio->iMinimoAlunosPagina   = 20;
$oDadosRelatorio->iColunaNumero         = 5;
$oDadosRelatorio->iColunaAluno          = 65;
$oDadosRelatorio->iColunaPeriodo        = 20;
$oDadosRelatorio->iColunaAvaliacao      = 15;
$oDadosRelatorio->iColunaFalta          = 5;
$oDadosRelatorio->iColunaResultadoFinal = 30;
$oDadosRelatorio->iAltura               = 4;
/**
 * Autor: Uemerson Santana
 *
 * Data: 27/03/2025
 *
 * Implementação:
 *    O relatório "Turma > Resumo do Aproveitamento" foi
 *    inicialmente preparado para utilizar a regra
 *    (Quantidade de horas × Semanas Letivas) como padrão.
 *    Ou seja, a modalidade "Dias Letivos", utilizada nos
 *    Anos Iniciais e na Educação Infantil, não seria
 *    compatível com esse relatório.
 *
 *    Para corrigir isso, criei uma opção no formulário de geração
 *    do relatório onde é possível definir a regra de geração das
 *    Aulas Previstas. Dessa forma, o relatório poderá contemplar
 *    ambas as modalidades.
 */
$oDadosRelatorio->lCalcAulasPrevistas      = $oGet->lCalcAulasPrevistas;
$oDadosRelatorio->lExibeTrocaTurma      = $oGet->lExibirTrocaTurma == 'S';
$oDadosRelatorio->aAprovadosConselho    = array();
$oDadosRelatorio->iDisciplinaAtual      = '';
$oDadosRelatorio->sSituacaoAlunoAtual   = '';
$oDadosRelatorio->aSituacoesAluno       = array(
                                                 'AVANÇADO'             => 'AVANÇADO',
                                                 'CANCELADO'            => 'CANCELADO',
                                                 'EVADIDO'              => 'EVADIDO',
                                                 'FALECIDO'             => 'FALECIDO',
                                                 'CLASSIFICADO'         => 'CLASSIFICADO',
                                                 'RECLASSIFICADO'       => 'RECLASSIFI',
                                                 'MATRICULA TRANCADA'   => 'MT',
                                                 'TRANSFERIDO REDE'     => 'TR',
                                                 'TRANSFERIDO FORA'     => 'TF',
                                                 'TROCA DE TURMA'       => 'TT',
                                                 'TROCA DE MODALIDADE'  => 'TM',
                                                 'MATRICULA INDEVIDA'   => 'MI',
                                                 'MATRICULA INDEFERIDA' => 'IN'
                                               );
$oDadosRelatorio->aEstruturaGeral = montaEstruturaGeral( $oTurma, $oEtapa, $oDadosRelatorio );

$oPdf = new FpdfMultiCellBorder( 'L' );
$oPdf->Open();
$oPdf->exibeHeader( true );
$oPdf->setExibeBrasao( true );

/**
 * Dados do cabeçalho
 */
$sCurso = $oTurma->getBaseCurricular()->getCurso()->getCodigo() . ' - ' . $oTurma->getBaseCurricular()->getCurso()->getNome();

$head1 = "FICHA DE RESUMO DE APROVEITAMENTO";
$head2 = "Curso: {$sCurso}";
$head3 = "Turno: {$oTurma->getTurno()->getDescricao()}";
$head4 = "Calendário: {$oTurma->getCalendario()->getDescricao()}";
$calen = $oTurma->getCalendario()->getDescricao();

$head5 = "Turma: {$oTurma->getDescricao()}";
$zturma=$oTurma->getCodigo();
$head6 = "Etapa: {$oEtapa->getNome()}";

/**
 * Percorre a estrutura criada, quebrando primeiramente por disciplina
 */
 $linhaPorPagina = 0;
 $discip = '';
foreach( $oDadosRelatorio->aEstruturaGeral as $oDisciplina ) {
  if( $discip <> $oDisciplina->sDisciplina)   // verifica se mudou de disciplina
  {
	  $ed59_i_codigo = buscaRegenteD1($zturma,$oDisciplina->sDisciplina);  // busca o codigo do regente ou regentes
	  $regente       = buscaRegenteD($oDisciplina->sDisciplina, $ed59_i_codigo); // busca p mome do regente ou regentes
	  $regentes = explode("-", $regente); // se for mais de um regente explode em array
	  $head8 = "Regente: " . $regentes[0]; // imprime o regente um
	  $head9 = "                " . $regentes[1]; // imprime o segundo regente se houver
      $head7 = "Disciplina: {$oDisciplina->sDisciplina}";
	  $linhaPorPagina = 0;
	  $oPdf->AddPage('L');  // muda de pagina
  }
  $discip =  $disciplina['ed232_c_descr'];


//  $head8 = "Regente: {$oDisciplina->sRegente}";


  /**
   * Dentro da disciplina, percorre os períodos configurados para a turma
   */
  foreach( $oDisciplina->aPeriodos as $iPeriodo => $aPaginaPeriodo ) {
  if ($oDadosRelatorio->lTemNotaParcial && (count($aPaginaPeriodo))) {
      $oDadosRelatorio->iColunaPeriodo        = 18;
      $oDadosRelatorio->iColunaAvaliacao      = 13;
  }
    $iContadorPaginasAluno = 0;
    $iTotalPaginasAluno    = count( $oDisciplina->aAlunos );

    /**
     * Percorre o array dos alunos organizado por página matriculados na turma
     */
	$novaPrimeira = true;
    foreach( $oDisciplina->aAlunos as $aPaginaAluno ) {
      if( $linhaPorPagina == 38 and $novaPrimeira)
	  {
          $oPdf->AddPage( 'L' );
		  $linhaPorPagina = 0;
		  $novaPrimeira   = false;
	  }

      $oPdf->SetAutoPageBreak( false );
      $oPdf->SetFont( 'arial', 'b', 7 );

      /**
       * Imprime as 2 primeiras linhas da grade: Períodos e SubCabeçalho
       */
      linhaPeriodos( $oPdf, $aPaginaPeriodo, $oDadosRelatorio );
      linhaSubCabecalho( $oPdf, $aPaginaPeriodo, $oDadosRelatorio, 1 );

      $iContadorPaginasAluno++;
      $iTotalAlunosPagina = count( $aPaginaAluno );

      /**
       * Percorre os alunos da página atual
       */
      foreach( $aPaginaAluno as $oAluno ) {

        /**
         * Guarda a situação do aluno percorrido atualmente, para validação de impressão das avaliações ou situação
         */
        if( $linhaPorPagina == 38 and !$novaPrimeira)
	    {
            $oPdf->AddPage( 'L' );
			$linhaPorPagina = 0;
			$novaPrimeira   = true;
	    }

        $oDadosRelatorio->sSituacaoAlunoAtual = $oAluno->sSituacao;

        $oPdf->SetFont( 'arial', '', 7 );
        $oPdf->Cell( $oDadosRelatorio->iColunaNumero, $oDadosRelatorio->iAltura, $oAluno->iNumero, 1, 0 );
		if(strlen(trim( $oAluno->sAluno )) > 37)
		{
			$oPdf->SetFont( 'arial', '', 5.5 );
		}
        $oPdf->Cell( $oDadosRelatorio->iColunaAluno,  $oDadosRelatorio->iAltura, $oAluno->sAluno,  1, 0 );
        $oPdf->SetFont( 'arial', '', 7 );
        /**
         * Percorre as disciplinas do aluno, para impressão das avaliações e resultados do mesmo
         */
        foreach( $oAluno->aDisciplinas as $iDisciplina => $oGrade ) {
//echo "<pre>";
//print_r($oDisciplina);
//echo "</pre>";

          if( $iDisciplina != $oDisciplina->iDisciplina ) {
            continue;
          }

          $oDadosRelatorio->iDisciplinaAtual = $iDisciplina;

          /**
           * Percorre as avaliações do aluno na disciplina, imprimindo o resultado de cada período
           */


		  $colunaA = 1;
          foreach( $oGrade->aAvaliacoes as $oAvaliacao ) {
			/*
			A disciplina abaixo causou uma grande quantidade de problemas, porque a principio não tinha as colunas de recuperação
			e o sistema trabalha por indice inteiro para todas as disciplina avaliadas por nota conforme abaixo:

			explicação 1  (demais disciplinas)
			indice   período
			   0        1º bimestre
			   1        2º bimestre
			   2        Recuperação semestral
			   3        3º bimestre
			   4        4º bimestre
			   5        Recuperação final

            explicação 2
			Mas para a disciplina TECNOLOGIA E INOVAÇÃO, não funciona assim, muda conforme abaixo:
			   0        1º bimestre
			   1        2º bimestre
			   2        3º bimestre
			   3        4º bimestre

			Os relatorios do sistema foram todos feitos para seguir a explicação 1, mas quando criaram a disciplina TECNOLOGIA E INOVAÇÃO diferente
			os problemas começaram a aparecer, porque o indice 2 nessa disciplina é o (1)3º bimestre e não a Recuperação semestral, o mesmo para
			o 4º bimestre. e para complicar mais ainda, em algumas escolas que estavam tendo problemas na transferencia de alunos, (2)foram criadas essas
			colunas

			Para solucionar o problema fiz as condições abaixo
			*/

			if( $oDisciplina->sDisciplina == 'TECNOLOGIA E INOVAÇÃO' and substr($calen,0,11) == 'ANOS FINAIS')  // se for a disciplina
			{
		        $emBranco = false; // variavel para imprimir ou não a coluna em branco
		        if( $colunaA == 3 and  $oAvaliacao->mAproveitamento <> null)  // se estiver na coluna 3 e a coluna de recuperação não foi criada(2)
				{
			        $emBranco = true; // vai imprimir coluna em branco
//ver a	necessidade de enviar a disciplina TECNOLOGIA E INOVAÇÃO como parametro para a funcao abaixo
				    imprimeAvaliacao( $oPdf, $oAvaliacao, $oDadosRelatorio, $emBranco,$colunaA ); // imprime a coluna que seria REC S em branco
				}else if($colunaA == 4){ // imprime a coluna que seria REC F em branco, isso para fechar a impressão da grade correta
			        $emBranco = true;
				    imprimeAvaliacao( $oPdf, $oAvaliacao, $oDadosRelatorio, $emBranco,$colunaA );
				}else{ // se as colunas de recuperação foram criadas, imprime normalmente como as outras disciplinas
					imprimeAvaliacao( $oPdf, $oAvaliacao, $oDadosRelatorio );
				}
				$colunaA++; // conta as colunas, conforme indice do array de notas e faltas
			}else{ // a disciplina não é TECNOLOGIA E INOVAÇÃO
				imprimeAvaliacao( $oPdf, $oAvaliacao, $oDadosRelatorio );
			}
          }

          $iColunasEmBranco = $oDadosRelatorio->iMaximoPeriodosPagina - count( $aPaginaPeriodo );

          if( $oDadosRelatorio->lTemNotaParcial ) {

            if( $oDadosRelatorio->sSituacaoAlunoAtual == 'MATRICULADO' ) {
              $iColunasEmBranco = $iColunasEmBranco - 1;
            }

            imprimeNotaParcial( $oPdf, $oGrade, $oDadosRelatorio );
          }

          /**
           * Preenche as demais colunas em branco
           */
            if ($oDadosRelatorio->lTemNotaParcial) {
                $iColunasEmBranco++;
            }
          colunasEmBranco( $oPdf, $oDadosRelatorio, $iColunasEmBranco );

          /**
           * Imprime as informações do resultado final do aluno na disciplina
           */
          imprimeResultado( $oPdf, $oGrade, $oDadosRelatorio, $oDisciplina->sDisciplina, substr($calen,0,11) );
        }
		$linhaPorPagina++;
      }

      /**
       * Caso tenha sido impresso o último aluno:
       * 1º Verifica se o mínimo de linhas de alunos foi impresso
       */
      if( $iContadorPaginasAluno == $iTotalPaginasAluno ) {

        if( $iTotalAlunosPagina < $oDadosRelatorio->iMinimoAlunosPagina - 1 ) {

          $iLinhasEmBranco = $oDadosRelatorio->iMinimoAlunosPagina - $iContadorPaginasAluno - 2;

          for( $iContador = 0; $iContador < $iLinhasEmBranco; $iContador++ ) {
            linhaSubCabecalho( $oPdf, $aPaginaPeriodo, $oDadosRelatorio, 2 );
          }
        }

        /**
         * 2º Imprime as linhas com as aulas previstas e aulas dadas
         */
        linhaAulas( $oPdf, $aPaginaPeriodo, $oDadosRelatorio, 1 );
        linhaAulas( $oPdf, $aPaginaPeriodo, $oDadosRelatorio, 2 );

        $iContadorPaginasAluno = 0;

        /**
         * Caso existam alunos com alteração do resultado final na disciplina atual, imprime estas por último
         */
        if( count( $oDadosRelatorio->aAprovadosConselho ) > 0 ) {

          foreach( $oDadosRelatorio->aAprovadosConselho as $iDisciplinaConselho => $aAprovadosConselho ) {

            if( $iDisciplinaConselho != $oDadosRelatorio->iDisciplinaAtual ) {
              continue;
            }

            quadroObservacoes( $oPdf, $aAprovadosConselho );
          }
        }

        /**
         * Legendas referentes as situações dos alunos que não estejam com situação de MATRICULADO
         */
        $sLegenda  = "MT = Matrícula Trancada, MI = Matrícula Indevida, IN = Matricula Indeferida, TR = Transferido Rede";
        $sLegenda .= ", TF = Transferido Fora, TM = Troca de Modalidade, TT = Troca de Turma";

        $oPdf->SetFont( 'arial', '', 7 );
        $oPdf->Cell( 280, 4, $sLegenda, 'T', 1, 'L' );
      }
    }
  }
}

/**
 * Imprime a primeira linha com os dados dos períodos de avaliação da turma
 * @param FpdfMultiCellBorder $oPdf
 * @param array $aPaginaPeriodo
 * @param stdClass $oDadosRelatorio
 */
function linhaPeriodos( FpdfMultiCellBorder $oPdf, $aPaginaPeriodo, $oDadosRelatorio ) {

  $iColunasEmBranco = $oDadosRelatorio->iMaximoPeriodosPagina - count( $aPaginaPeriodo );

  $oPdf->Cell( $oDadosRelatorio->iColunaNumero, $oDadosRelatorio->iAltura, '', 1, 0 );
  $oPdf->Cell( $oDadosRelatorio->iColunaAluno,  $oDadosRelatorio->iAltura, '', 1, 0 );

  foreach( $aPaginaPeriodo as $oPeriodo ) {
    $oPdf->Cell( $oDadosRelatorio->iColunaPeriodo, $oDadosRelatorio->iAltura, $oPeriodo->sPeriodo, 1, 0, 'C' );
  }

  if ($oDadosRelatorio->lTemNotaParcial) {
      $oPdf->Cell($oDadosRelatorio->iColunaPeriodo, $oDadosRelatorio->iAltura, 'NP', 1, 0, 'C');
  }
  colunasEmBranco( $oPdf, $oDadosRelatorio, $iColunasEmBranco, false );

  $oPdf->Cell( $oDadosRelatorio->iColunaResultadoFinal, $oDadosRelatorio->iAltura, '', 1, 1 );
}

/**
 * Função para imprimir a segunda linha do cabeçalho, sendo utilizada também para imprimir as linhas em branco dos alunos,
 * quando não for atingido o limite mínimo
 * @param FpdfMultiCellBorder $oPdf
 * @param $aPaginaPeriodo
 * @param $oDadosRelatorio
 * @param $sTipoImpressao
 *        1 - Imprime os textos padrões do subcabeçalho
 *        2 - Imprime somente as colunas sem texto
 */
function linhaSubCabecalho( FpdfMultiCellBorder $oPdf, $aPaginaPeriodo, $oDadosRelatorio, $iTipoImpressao ) {

  $sNumero         = '';
  $sNomeAluno      = '';
  $sFormaAvaliacao = '';
  $sFalta          = '';
  $sAproveitamento = '';
  $sFrequencia     = '';
  $sResultadoFinal = '';

  if( $iTipoImpressao == 1 ) {

    $sNumero         = 'Nº';
    $sNomeAluno      = 'Nome do Aluno';
    $sFalta          = 'Ft';
    $sAproveitamento = 'Aprov';
    $sFrequencia     = '% Freq';
    $sResultadoFinal = 'RF';
    $sFormaAvaliacao = 'AVAL.';
  }

  $iColunasEmBranco = $oDadosRelatorio->iMaximoPeriodosPagina - count( $aPaginaPeriodo );

  $oPdf->Cell( $oDadosRelatorio->iColunaNumero, $oDadosRelatorio->iAltura, $sNumero,    1, 0, 'C' );
  $oPdf->Cell( $oDadosRelatorio->iColunaAluno,  $oDadosRelatorio->iAltura, $sNomeAluno, 1, 0, 'C' );

  for( $iContador = 0; $iContador < count( $aPaginaPeriodo ); $iContador++ ) {

    $oPdf->Cell( $oDadosRelatorio->iColunaAvaliacao, $oDadosRelatorio->iAltura, $sFormaAvaliacao, 1, 0, 'C' );
    $oPdf->Cell( $oDadosRelatorio->iColunaFalta,     $oDadosRelatorio->iAltura, $sFalta         , 1, 0, 'C' );
  }

  if ($oDadosRelatorio->lTemNotaParcial) {
      $iColunasEmBranco++;
  }
  colunasEmBranco( $oPdf, $oDadosRelatorio, $iColunasEmBranco );

  $oPdf->Cell( $oDadosRelatorio->iColunaResultadoFinal / 3, $oDadosRelatorio->iAltura, $sAproveitamento, 1, 0, 'C' );
  $oPdf->Cell( $oDadosRelatorio->iColunaResultadoFinal / 3, $oDadosRelatorio->iAltura, $sFrequencia,     1, 0, 'C' );
  $oPdf->Cell( $oDadosRelatorio->iColunaResultadoFinal / 3, $oDadosRelatorio->iAltura, $sResultadoFinal, 1, 1, 'C' );
}

/**
 * Imprime as linhas referentes as aulas previstas e aulas dadas em cada período
 * @param FpdfMultiCellBorder $oPdf
 * @param $aPaginaPeriodo
 * @param $oDadosRelatorio
 * @param $iTipo
 *        1 - Aulas Previstas
 *        2 - Aulas Dadas
 */
function linhaAulas( FpdfMultiCellBorder $oPdf, $aPaginaPeriodo, $oDadosRelatorio, $iTipo ) {

  $iTamanhoColunaAulas = $oDadosRelatorio->iColunaNumero + $oDadosRelatorio->iColunaAluno;
  $sAulas              = $iTipo == 1 ? 'Aulas Previstas' : 'Aulas Dadas';
  $iColunasEmBranco    = $oDadosRelatorio->iMaximoPeriodosPagina - count( $aPaginaPeriodo );

  $oPdf->SetFont( 'arial', 'b', 7 );
  $oPdf->Cell( $iTamanhoColunaAulas, $oDadosRelatorio->iAltura, $sAulas, 1, 0, 'R' );
  //testa($oDadosRelatorio); die("Confere");
  //Previstas = SELECT ed53_i_diasletivos FROM periodocalendario WHERE ed53_i_codigo = XXX
  //1o trimestre é 720
  foreach( $aPaginaPeriodo as $oPeriodo ) {

    foreach( $oPeriodo->aDisciplinas as $iDisciplina => $oDadosDisciplina ) {

      if( $oDadosRelatorio->iDisciplinaAtual != $iDisciplina ) {
        continue;
      }

      $oPdf->SetFont( 'arial', '', 7 );
      $iAulas = $iTipo == 1 ? "{$oDadosDisciplina->iAulasPrevistas}" : $oDadosDisciplina->iAulasDadas;
      $oPdf->Cell( $oDadosRelatorio->iColunaPeriodo, $oDadosRelatorio->iAltura, $iAulas, 1, 0, 'C' );
    }
  }
  if ($oDadosRelatorio->lTemNotaParcial) {
    $iColunasEmBranco++;
  }
  colunasEmBranco( $oPdf, $oDadosRelatorio, $iColunasEmBranco, false );

  $oPdf->Cell( $oDadosRelatorio->iColunaResultadoFinal, $oDadosRelatorio->iAltura, '', 1, 1 );
}

/**
 * Imprime colunas em branco
 * @param FpdfMultiCellBorder $oPdf
 * @param $oDadosRelatorio
 * @param $iTotalLinhas     - Total de linhas que devem ter as colunas em branco
 * @param bool $lComDivisao - Controla se a coluna deve ser dividida entre avaliação/falta
 */
function colunasEmBranco( FpdfMultiCellBorder $oPdf, $oDadosRelatorio, $iTotalLinhas, $lComDivisao = true ) {

  for( $iContador = 0; $iContador < $iTotalLinhas; $iContador++ ) {

    if( $lComDivisao ) {

      $oPdf->Cell( $oDadosRelatorio->iColunaAvaliacao, $oDadosRelatorio->iAltura, '', 1, 0 );
      $oPdf->Cell( $oDadosRelatorio->iColunaFalta,     $oDadosRelatorio->iAltura, '', 1, 0 );
    } else {
      $oPdf->Cell( $oDadosRelatorio->iColunaPeriodo, $oDadosRelatorio->iAltura, '', 1, 0 );
    }
  }
}

/**
 * Imprime as colunas com os dados das avaliações de cada período. Caso a matrícula do aluno esteja com situação diferente
 * de matriculado, imprime a situação em cada período
 * @param FpdfMultiCellBorder $oPdf
 * @param $oAvaliacao
 * @param $oDadosRelatorio
 */
function imprimeAvaliacao( FpdfMultiCellBorder $oPdf, $oAvaliacao, $oDadosRelatorio, $emBranco = false, $colunaA = 0) {

  $oPdf->SetFont( 'arial', '', 7 );

  if( $oDadosRelatorio->sSituacaoAlunoAtual == 'MATRICULADO' ) {

    if( !$oAvaliacao->lAproveitamentoMinimo && $oAvaliacao->mAproveitamento != 'Parecer' ) {
      $oPdf->SetFont( 'arial', 'b', 7 );
    }

    $mAproveitamento = $oAvaliacao->mAproveitamento;
    if ($oAvaliacao->lAvaliacaoExterna && $oAvaliacao->mAproveitamento != '') {
      $mAproveitamento = "*" . $oAvaliacao->mAproveitamento;
    }
	if( $emBranco and $colunaA == 3 ) //Não foi criada a coluna para RECUPERAÇÃO SEMESTRAL, imprime uma coluna em branco para fechar a grade
	{
		$oPdf->Cell( $oDadosRelatorio->iColunaAvaliacao, $oDadosRelatorio->iAltura, '', 1, 0, 'C' );
		$oPdf->Cell( $oDadosRelatorio->iColunaFalta, $oDadosRelatorio->iAltura, '', 1, 0, 'C' );

	}
    $oPdf->Cell( $oDadosRelatorio->iColunaAvaliacao, $oDadosRelatorio->iAltura, $mAproveitamento, 1, 0, 'C' );
    $oPdf->SetFont( 'arial', '', 7 );
    $oPdf->Cell( $oDadosRelatorio->iColunaFalta, $oDadosRelatorio->iAltura, $oAvaliacao->iFaltas, 1, 0, 'C' );
/*
   É necessario ter notas lançadas para que essa impressão fique correta, TECNOLOGIA E INOVAÇÃO por exemplo, houve casos
   que não havia nota lançada e a impressão fica com problemas
*/
	if( $emBranco and $colunaA == 4 ) //Não foi criada a coluna para RECUPERAÇÃO FINAL, imprime uma coluna em branco para fechar a grade
	{
		$oPdf->Cell( $oDadosRelatorio->iColunaAvaliacao, $oDadosRelatorio->iAltura, '', 1, 0, 'C' );
		$oPdf->Cell( $oDadosRelatorio->iColunaFalta, $oDadosRelatorio->iAltura, '', 1, 0, 'C' );
	}

	if( $colunaA == 3 or $colunaA == 4)  //Não foi criada a coluna para RECUPERAÇÃO SEMESTRAL, imprime uma coluna em branco para fechar a grade
	{
		if( $mAproveitamento == '')
		{
		    $oPdf->Cell( $oDadosRelatorio->iColunaAvaliacao, $oDadosRelatorio->iAltura, '', 1, 0, 'C' );
		    $oPdf->Cell( $oDadosRelatorio->iColunaFalta, $oDadosRelatorio->iAltura, '', 1, 0, 'C' );
		}
	}

  } else {
    $sSituacao = $oDadosRelatorio->aSituacoesAluno[$oDadosRelatorio->sSituacaoAlunoAtual];
    $oPdf->Cell( $oDadosRelatorio->iColunaPeriodo, $oDadosRelatorio->iAltura, $sSituacao, 1, 0, 'C' );
  }
}

/**
 * Imprime a coluna da nota parcial, caso o parâmetro para cálculo esteja sim
 * @param FpdfMultiCellBorder $oPdf
 * @param $oGrade
 * @param $oDadosRelatorio
 */
function imprimeNotaParcial( FpdfMultiCellBorder $oPdf, $oGrade, $oDadosRelatorio ) {

  $oPdf->SetFont( 'arial', '', 7 );
  if( $oDadosRelatorio->sSituacaoAlunoAtual == 'MATRICULADO' ) {

    $oPdf->Cell( $oDadosRelatorio->iColunaAvaliacao, $oDadosRelatorio->iAltura, $oGrade->sNotaParcial, 1, 0, 'C' );
    $oPdf->Cell( $oDadosRelatorio->iColunaFalta,     $oDadosRelatorio->iAltura, '',                    1, 0, 'C' );
  }
}

/**
 * Imprime o resultado da disciplina. Caso a matrícula do aluno esteja com situação diferente de matriculado, imprime
 * a situação nas colunas dos dados finais
 * @param FpdfMultiCellBorder $oPdf
 * @param $oGrade
 * @param $oDadosRelatorio
 */
function imprimeResultado( FpdfMultiCellBorder $oPdf, $oGrade, $oDadosRelatorio, $disciplina, $calend ) {

  if( $oDadosRelatorio->sSituacaoAlunoAtual == 'MATRICULADO' ) {

    $iAltura               = $oDadosRelatorio->iAltura;
    $iColunaResultadoFinal = $oDadosRelatorio->iColunaResultadoFinal;

    $notaajustada = number_format(floor($oGrade->sAproveitamentoFinal * 10) / 10, 1, ',', '');
    $oPdf->Cell( $iColunaResultadoFinal / 3, $iAltura, $notaajustada, 1, 0, 'C' );

    //$oPdf->Cell( $iColunaResultadoFinal / 3, $iAltura, $oGrade->sAproveitamentoFinal, 1, 0, 'C' );
    $oPdf->Cell( $iColunaResultadoFinal / 3, $iAltura, $oGrade->sFrequencia,          1, 0, 'C' );
    $oPdf->Cell( $iColunaResultadoFinal / 3, $iAltura, $oGrade->sResultadoFinal,      1, 1, 'C' );
  } else {
	if( $disciplina == 'TECNOLOGIA E INOVAÇÃO' and $calend == 'ANOS FINAIS') // para fechar a impressão da grade
    {
        $oPdf->Cell( $oDadosRelatorio->iColunaResultadoFinal+10, $oDadosRelatorio->iAltura, $sSituacao, 1, 0, 'C' );
	}
    $sSituacao = $oDadosRelatorio->aSituacoesAluno[$oDadosRelatorio->sSituacaoAlunoAtual];
    $oPdf->Cell( $oDadosRelatorio->iColunaResultadoFinal, $oDadosRelatorio->iAltura, $sSituacao, 1, 1, 'C' );
  }
}

/**
 * Monta a estrutura com as informações padrão para cada período. Guarda as informações do período, e as aulas previstas
 * e dadas para cada disciplina em cada período
 * Estrutura separada por página
 * @param Turma $oTurma
 * @param Etapa $oEtapa
 * @param $oDadosRelatorio
 * @return array
 */
function montaEstruturaImpressaoPeriodos( Turma $oTurma, Etapa $oEtapa, $oDadosRelatorio ) {

  $aEstruturaPeriodos     = array();
  $iContadorPagina        = 0;
  $iContadorPeriodos      = 0;
  $oProcedimentoAvaliacao = $oTurma->getProcedimentoDeAvaliacaoDaEtapa( $oEtapa );
  $aDisciplinas           = $oTurma->getDisciplinasPorEtapa( $oEtapa );

  /**
   * Percorre os períodos de avaliação da turma, guardando somente os dados de períodos que não sejam um Resultado
   */
  foreach( $oProcedimentoAvaliacao->getElementos() as $oElementoAvaliacao ) {

    if( $oElementoAvaliacao instanceof ResultadoAvaliacao ) {
      continue;
    }

    $oDadosPeriodo                  = new stdClass();
    $oDadosPeriodo->sPeriodo        = $oElementoAvaliacao->getPeriodoAvaliacao()->getDescricaoAbreviada();
    $oDadosPeriodo->sFormaAvaliacao = $oElementoAvaliacao->getFormaDeAvaliacao()->getTipo();
    $oDadosPeriodo->aDisciplinas    = array();

    /**
     * Percorre as disciplinas, armazenando o total de aulas previstas e dadas em cada período
     */
    foreach( $aDisciplinas as $oRegencia ) {

      $oDadosAulas                   = new stdClass();
      $oDadosAulas->iAulasPrevistas  = 0;

      foreach( $oRegencia->getTurma()->getCalendario()->getPeriodos() as $oPeriodoCalendario ) {

        if( $oPeriodoCalendario->getPeriodoAvaliacao()->getCodigo() != $oElementoAvaliacao->getPeriodoAvaliacao()->getCodigo() ) {
          continue;
        }
        //var_dump($oRegencia->getHorasAula());
        //var_dump($oPeriodoCalendario->getSemanasLetivas());
        //var_dump($oPeriodoCalendario->getDiasLetivos());
        //echo "<br>";
        //die("Confere");

        /**
         * Data: 27/03/2025
         *
         * Legenda:
         *  qs = QUANTIDADE DE HORAS x SEMANAS LETIVAS
         *  dl = DIAS LETIVOS
         *  ad = AULAS DADAS
         */
        if ( 'qs' == $oDadosRelatorio->lCalcAulasPrevistas ) {
          $oDadosAulas->iAulasPrevistas = $oPeriodoCalendario->getSemanasLetivas() * $oRegencia->getHorasAula();
        } else if ( 'dl' == $oDadosRelatorio->lCalcAulasPrevistas ) {
          $oDadosAulas->iAulasPrevistas = $oPeriodoCalendario->getDiasLetivos();
        }
      }

      $oDadosAulas->iAulasDadas = $oRegencia->getTotalDeAulasNoPeriodo( $oElementoAvaliacao->getPeriodoAvaliacao() );

      if ( 'ad' == $oDadosRelatorio->lCalcAulasPrevistas ) {
          $oDadosAulas->iAulasPrevistas = $oDadosAulas->iAulasDadas;
      }

      $oDadosPeriodo->aDisciplinas[ $oRegencia->getCodigo() ] = $oDadosAulas;
    }

    /**
     * Caso o total de períodos permitidos por página tenha sido atingido, armazena os demais em uma nova página
     */
    if( $iContadorPeriodos > $oDadosRelatorio->iMaximoPeriodosPagina ) {

      $iContadorPagina++;
      $iContadorPeriodos = 0;
    }

    $aEstruturaPeriodos[ $iContadorPagina ][ $oElementoAvaliacao->getPeriodoAvaliacao()->getCodigo() ] = $oDadosPeriodo;
    $iContadorPeriodos++;
  }
  //die("COnfere");
  return $aEstruturaPeriodos;
}

/**
 * Monta a estrutura dos alunos com as informações referentes ao mesmo, necessárias para impressão do relatório
 * Estrutura separada por página
 * @param Turma $oTurma
 * @param Etapa $oEtapa
 * @param $oDadosRelatorio
 * @return array
 */
function montaEstruturaImpressaoAlunos( Turma $oTurma, Etapa $oEtapa, $oDadosRelatorio ) {

  $aEstruturaAlunos       = array();
  $iMaximoAlunosPorPagina = 38;
  $iContadorPagina        = 0;
  $iContadorAlunos        = 0;
  $iAnoCalendario         = $oTurma->getCalendario()->getAnoExecucao();
  $iEnsino                = $oTurma->getBaseCurricular()->getCurso()->getEnsino()->getCodigo();

  /**
   * Percorre os alunos matriculados na turma e série selecionados
   */
  foreach( $oTurma->getAlunosMatriculadosNaTurmaPorSerie( $oEtapa ) as $oMatricula ) {

    /**
     * Pula alunos que tenham situação de TROCA DE TURMA, caso tenha sido selecionado para não exibir os mesmos
     */
    if( !$oDadosRelatorio->lExibeTrocaTurma && $oMatricula->getSituacao() == 'TROCA DE TURMA' ) {
      continue;
    }

    /**
     * Objeto com os dados do aluno
     */
    $oDadosAluno               = new stdClass();
    $oDadosAluno->iAluno       = $oMatricula->getAluno()->getCodigoAluno();
    $oDadosAluno->iMatricula   = $oMatricula->getCodigo();
    $oDadosAluno->sAluno       = $oMatricula->getAluno()->getNome();
    $oDadosAluno->iNumero      = $oMatricula->getNumeroOrdemAluno();
    $oDadosAluno->sSituacao    = $oMatricula->getSituacao();
    $oDadosAluno->aDisciplinas = array();

    db_inicio_transacao();

    $oDiario = $oMatricula->getDiarioDeClasse();

    /**
     * Percorre as avaliações de cada disciplina do diário do aluno para buscar os aproveitamentos
     */
    foreach( $oDiario->getDisciplinas() as $oDiarioAvaliacaoDisciplina ) {

      $oDadosDisciplina                       = new stdClass();
      $oDadosDisciplina->sAproveitamentoFinal = '';
      $oDadosDisciplina->sFrequencia          = '';
      $oDadosDisciplina->sResultadoFinal      = '';
      $oDadosDisciplina->sNotaParcial         = '';
      $oDadosDisciplina->aAvaliacoes          = array();

      /**
       * Percorre as avaliações configuradas no procedimento da turma
       */
      foreach( $oDiarioAvaliacaoDisciplina->getAvaliacoes() as $oElementosAvaliacao ) {

        /**
         * Caso o elemento atual seja uma instância de ResultadoAvaliacao:
         * 1º Armazena o aproveitamento final, alterando o valor impresso quando forma de avaliação for PARECER, aluno
         * amparado na disciplina por Convenção( SUP ) ou aluno amparado na disciplina em todos os períodos( Amp )
         * Para os casos de amparo, a frequência fica vazia
         */
        if( $oElementosAvaliacao->getElementoAvaliacao() instanceof ResultadoAvaliacao ) {

          $oElementoResultadoFinal                = $oDiarioAvaliacaoDisciplina->getResultadoFinal();
          $oDadosDisciplina->sAproveitamentoFinal = $oElementoResultadoFinal->getValorAprovacao();
          
          if (!is_null($oElementoResultadoFinal->getResultadoAvaliacao())){
            if ($oElementoResultadoFinal->getResultadoAvaliacao()->getFormaDeObtencao() == 'AP') {
                $oDadosDisciplina->sAproveitamentoFinal = '-';
            }
          }else{
            $oDadosDisciplina->sAproveitamentoFinal = 'null';
          }
          if( $oElementosAvaliacao->getElementoAvaliacao()->getFormaDeAvaliacao()->getTipo() == 'PARECER' ) {
            $oDadosDisciplina->sAproveitamentoFinal = 'Parec';
          }

          $oDadosDisciplina->sFrequencia = $oElementoResultadoFinal->getPercentualFrequencia();

          if(    $oDiarioAvaliacaoDisciplina->reclassificadoPorBaixaFrequencia()
              || ( $oDiario->reclassificadoPorBaixaFrequencia() && $oDiario->getProcedimentoDeAvaliacao()->getFormaCalculoFrequencia() == 2 ) ) {
            $oDadosDisciplina->sFrequencia = '--';
          }

          if( $oDiarioAvaliacaoDisciplina->getAmparo() instanceof AmparoDisciplina ) {

            if( $oDiarioAvaliacaoDisciplina->getAmparo()->getTipoAmparo() == AmparoDisciplina::AMPARO_CONVENCAO ) {

              $oDadosDisciplina->sAproveitamentoFinal = 'SUP';
              $oDadosDisciplina->sFrequencia          = '';
            }

              if(    $oDiarioAvaliacaoDisciplina->getAmparo()->getTipoAmparo() == AmparoDisciplina::AMPARO_JUSTIFICATIVA
                  && $oDiarioAvaliacaoDisciplina->getAmparo()->isTotal()
              ) {

                  $oDadosDisciplina->sAproveitamentoFinal = 'Amp';
                  $oDadosDisciplina->sFrequencia          = '';
              }
              if(    $oDiarioAvaliacaoDisciplina->getAmparo()->getTipoAmparo() == AmparoDisciplina::AMPARO_JUSTIFICATIVA
                  && $oDiarioAvaliacaoDisciplina->getAmparo()->isTotal()
              ) {

                  $oDadosDisciplina->sAproveitamentoFinal = 'Amp';
                  $oDadosDisciplina->sFrequencia          = '';
              }
          }

          /**
           * Guarda o resultado final do aluno, e em seguida busca o termo referente ao ensino e ano do calendário da
           * turma, caso o aluno já tenha resultado na disciplina
           */
          $oDadosDisciplina->sResultadoFinal = $oElementoResultadoFinal->getResultadoFinal();

          if( $oDadosDisciplina->sResultadoFinal != '' ) {

            $aTermosEnsino = DBEducacaoTermo::getTermoEncerramento(
                                                                    $iEnsino,
                                                                    $oDadosDisciplina->sResultadoFinal,
                                                                    $iAnoCalendario
                                                                  );
            $oDadosDisciplina->sResultadoFinal = $aTermosEnsino[0]->sAbreviatura;
          }


          /**
           * Caso o resultado final do aluno tenha sido alterado, busca as informações referentes a esta alteração,
           * armazenando em um array indexado pelo código da Regência, com os alunos alterados na disciplina atual
           */
          if( $oElementoResultadoFinal->getFormaAprovacaoConselho() instanceof AprovacaoConselho ) {

            $oAprovacaoConselho = $oElementoResultadoFinal->getFormaAprovacaoConselho();
            $iRegencia          = $oDiarioAvaliacaoDisciplina->getRegencia()->getCodigo();

            switch ( $oAprovacaoConselho->getFormaAprovacao() ) {

              /**
               * Valida se a aprovação foi por conselho
               */
              case 1:

                $oDocumento                = new libdocumento( 5013 );
                $oDocumento->disciplina    = $oDiarioAvaliacaoDisciplina->getDisciplina()->getNomeDisciplina();
                $oDocumento->etapa         = $oEtapa->getNome();
                $oDocumento->justificativa = $oAprovacaoConselho->getJustificativa();
                $oDocumento->nota          = $oAprovacaoConselho->getAvaliacaoConselho();
                $oDocumento->anomatricula  = $iAnoCalendario;

                $oDadosObservacao              = new stdClass();
                $oDadosObservacao->aParagrafos = $oDocumento->getDocParagrafos();

                if( trim( $oDadosObservacao->aParagrafos[1]->oParag->db02_texto ) != '' ) {

                  $sObservacao  = "- {$oMatricula->getAluno()->getNome()}: ";
                  $sObservacao .= $oDadosObservacao->aParagrafos[1]->oParag->db02_texto;

                  $oDadosRelatorio->aAprovadosConselho[$iRegencia][] = $sObservacao;
                }
                break;

              /**
               * Valida se a aprovação não foi por baixa frequencia
               */
              case 2:

                $oDocumento             = new libdocumento( 5006 );
                $oDocumento->nome_aluno = $oMatricula->getAluno()->getNome();
                $oDocumento->ano        = $iAnoCalendario;
                $oDocumento->nome_etapa = $oEtapa->getNome();

                $aParagrafos                                       = $oDocumento->getDocParagrafos();
                $oDadosRelatorio->aAprovadosConselho[$iRegencia][] = "- {$aParagrafos[1]->oParag->db02_texto}";

                break;

              /**
               * Valida se a aprovação foi por regimento escolar
               */
              case 3:

                $sObservacao  = "- {$oMatricula->getAluno()->getNome()}: ";
                $sObservacao .= "Disciplina {$oDiarioAvaliacaoDisciplina->getDisciplina()->getNomeDisciplina()} na etapa";
                $sObservacao .= " {$oEtapa->getNome()} foi aprovado pelo regimento escolar. ";
                $sObservacao .= "Justificativa: {$oAprovacaoConselho->getJustificativa()}";

                $oDadosRelatorio->aAprovadosConselho[$iRegencia][] = $sObservacao;

                break;
            }

            if( $oAprovacaoConselho->getFormaAprovacao() == 1 && $oAprovacaoConselho->getAlterarNotaFinal() == 2 ) {
              $oDadosDisciplina->sAproveitamentoFinal = $oAprovacaoConselho->getAvaliacaoConselho();
            }
          }

          continue;
        }

        /**
         * Sendo o elemento uma instância de AvaliacaoPeriodica, guarda o aproveitamento( já convertido para regra de
         * arredondamento configurada para o ano ) e as faltas no período.
         * Para os casos de amparo e parecer, o valor é alterado para:
         * - Amparo: SUP( Convenção ); Amparado;
         * - PARECER: Parecer
         */
        $oDadosAvaliacao                  = new stdClass();
        $oDadosAvaliacao->iPeriodo        = $oElementosAvaliacao->getElementoAvaliacao()->getPeriodoAvaliacao()->getCodigo();
        $oDadosAvaliacao->mAproveitamento = $oElementosAvaliacao->getValorAproveitamento()->getAproveitamento();
        $oDadosAvaliacao->mAproveitamento = ArredondamentoNota::formatar( $oDadosAvaliacao->mAproveitamento, $iAnoCalendario );

        if( $oElementosAvaliacao->isAmparado() ) {

          $oDadosAvaliacao->mAproveitamento = 'Amparado';
          if( $oDiarioAvaliacaoDisciplina->getAmparo()->getTipoAmparo() == 1 ) {
            $oDadosAvaliacao->mAproveitamento = 'SUP';
          }
        }

        if(    $oElementosAvaliacao->getElementoAvaliacao()->getFormaDeAvaliacao()->getTipo() == 'PARECER'
            || $oMatricula->isAvaliadoPorParecer()
        ) {
          $oDadosAvaliacao->mAproveitamento = 'Parecer';
        }

        $oDadosAvaliacao->lAproveitamentoMinimo = $oElementosAvaliacao->temAproveitamentoMinimo();
        $oDadosAvaliacao->lAvaliacaoExterna     = $oElementosAvaliacao->isAvaliacaoExterna();
        $oDadosAvaliacao->iFaltas               = $oElementosAvaliacao->getNumeroFaltas();

        if( empty( $oDadosAvaliacao->iFaltas ) ) {
          $oDadosAvaliacao->iFaltas = '';
        }

        if( $oDiarioAvaliacaoDisciplina->getRegencia()->getFrequenciaGlobal() == 'A' ) {
          $oDadosAvaliacao->iFaltas = '-';
        }

        if( $oDadosRelatorio->lTemNotaParcial ) {
          $oDadosDisciplina->sNotaParcial = $oDiarioAvaliacaoDisciplina->getNotaParcial( $oElementosAvaliacao->getElementoAvaliacao() );
        }

        $oDadosDisciplina->aAvaliacoes[] = $oDadosAvaliacao;
        $oDadosAluno->aDisciplinas[$oDiarioAvaliacaoDisciplina->getRegencia()->getCodigo()] = $oDadosDisciplina;
      }
    }

    db_fim_transacao();

    /**
     * Controla a quebra de página por aluno, caso atinja o limite permitido
     */
    if( $iContadorAlunos > $iMaximoAlunosPorPagina - 1 ) {

      $iContadorPagina++;
      $iContadorAlunos = 0;
    }

    $aEstruturaAlunos[ $iContadorPagina ][ $iContadorAlunos ] = $oDadosAluno;
    $iContadorAlunos++;
  }

  return $aEstruturaAlunos;
}

/**
 * Monta a estrutura final do relatório para impressão dos dados
 * @param Turma $oTurma
 * @param Etapa $oEtapa
 * @param $oDadosRelatorio
 * @return array
 */
function montaEstruturaGeral( Turma $oTurma, Etapa $oEtapa, $oDadosRelatorio ) {

  $aEstruturaGeral    = array();
  $aDisciplinasTurma  = $oTurma->getDisciplinasPorEtapa( $oEtapa );
  $aEstruturaPeriodos = montaEstruturaImpressaoPeriodos( $oTurma, $oEtapa, $oDadosRelatorio );
  $aEstruturaAlunos   = montaEstruturaImpressaoAlunos( $oTurma, $oEtapa, $oDadosRelatorio );

  foreach( $aDisciplinasTurma as $oRegencia ) {

    if( !in_array( $oRegencia->getCodigo(), $oDadosRelatorio->aDisciplinasImpressao ) ) {
      continue;
    }

    $oDadosDisciplina              = new stdClass();
    $oDadosDisciplina->iDisciplina = $oRegencia->getCodigo();
    $oDadosDisciplina->sDisciplina = $oRegencia->getDisciplina()->getNomeDisciplina();
    $oDadosDisciplina->sRegente    = "";

    foreach( $oRegencia->getDocentes() as $oDocente ) {
      $oDadosDisciplina->sRegente = $oDocente->getNome();
    }

    $oDadosDisciplina->aPeriodos = $aEstruturaPeriodos;
    $oDadosDisciplina->aAlunos   = $aEstruturaAlunos;
    $aEstruturaGeral[]           = $oDadosDisciplina;
  }

  return $aEstruturaGeral;
}

/**
 * Imprime o quadro com as observações de alunos que tiveram resultado final alterado na disciplina impressa
 * @param FpdfMultiCellBorder $oPdf
 * @param $aAprovadosConselho
 */
function quadroObservacoes( FpdfMultiCellBorder $oPdf, $aAprovadosConselho ) {

  $sObservacoes = '';

  foreach( $aAprovadosConselho as $sAprovadoConselho ) {
    $sObservacoes .= $sAprovadoConselho . "\n";
  }

  $oPdf->SetAutoPageBreak( true );
  $oPdf->SetFont( 'arial', 'b', 7 );
  $oPdf->MultiCell( 280, 4, $sObservacoes, 1, 'L' );
}

function buscaRegenteD($disciplina, $regente){
  $doc  ="select
          distinct on (z01_nome)
	  	  z01_numcgm,
		  z01_nome
		  from
		  regenciahorario
		  inner join regencia         on ed58_i_regencia   = ed59_i_codigo
		  inner join rechumano        on ed20_i_codigo     = ed58_i_rechumano
		  inner join rechumanopessoal on ed284_i_rechumano = ed20_i_codigo
		  inner join rhpessoal        on rh01_regist       = ed284_i_rhpessoal
		  inner join cgm              on z01_numcgm        = rh01_numcgm
		  inner join disciplina       on ed12_i_codigo     = ed59_i_disciplina
		  inner join caddisciplina    on ed232_i_codigo    = ed12_i_caddisciplina
		  where
          ed232_c_descr  = '".$disciplina."'
		  and
		  ed58_ativo is true
		  and
		  ed59_i_codigo = ".$regente;

// verificar porque o regente esta retornando vazio
    $sql1 = pg_query($doc);
    $prof = '';

    if( pg_num_rows($sql1) > 1 )
    {
	    for($x=0;$x<pg_num_rows($sql1);$x++)
	    {
		    if( $x > 0)
		    {
			    $prof .= "-";
		    }
		    $reg  = db_utils::fieldsMemory($sql1,$x);
		    $prof .=  $reg->z01_nome;
	    }
    }else{

	    $reg = db_utils::fieldsMemory($sql1,0);
	    $prof =  $reg->z01_nome;
    }
    $instit = db_getsession('DB_instit');
    if( !pg_num_rows($sql1) )
    {
        $sql = "
	        select
			z01_nome
			from
			cgm
			where
			z01_numcgm = (
							select
							distinct (case when rh01_numcgm is null then  ed285_i_cgm else rh01_numcgm end) as cgm
							from rechumano
							left join rechumanopessoal  on  rechumanopessoal.ed284_i_rechumano = rechumano.ed20_i_codigo
							left join rhpessoal  on  rhpessoal.rh01_regist = rechumanopessoal.ed284_i_rhpessoal
							left join cgm as cgmrh on  cgmrh.z01_numcgm = rhpessoal.rh01_numcgm
							left join db_config  on  db_config.codigo = rhpessoal.rh01_instit
							left join rhpessoalmov on rhpessoalmov.rh02_anousu  = 0 and rhpessoalmov.rh02_mesusu  = 0  and rhpessoalmov.rh02_regist  = rhpessoal.rh01_regist and rhpessoalmov.rh02_instit  = {$instit}
							left join rhregime as regimerh on  regimerh.rh30_codreg = rhpessoalmov.rh02_codreg
							left join rhlota  on  rhlota.r70_codigo = rhpessoal.rh01_lotac
							left join rhpesdoc  on  rhpesdoc.rh16_regist = rhpessoal.rh01_regist
							left join rhestcivil  on  rhestcivil.rh08_estciv = rhpessoal.rh01_estciv
							left join rhraca  on  rhraca.rh18_raca = rhpessoal.rh01_raca
							left join rhfuncao  on  rhfuncao.rh37_funcao = rhpessoal.rh01_funcao and rh37_instit  = rh02_instit
							left join rhinstrucao  on  rhinstrucao.rh21_instru = rhpessoal.rh01_instru
							left join rhnacionalidade  on  rhnacionalidade.rh06_nacionalidade = rhpessoal.rh01_nacion
							left join rechumanocgm  on  rechumanocgm.ed285_i_rechumano = rechumano.ed20_i_codigo
							left join cgm as cgmcgm on  cgmcgm.z01_numcgm = rechumanocgm.ed285_i_cgm
							left join cgmdoc on  cgmdoc.z02_i_cgm = cgmcgm.z01_numcgm
							left join rhregime as regimecgm on  regimecgm.rh30_codreg = rechumano.ed20_i_rhregime
							left join cgmfisico on cgmfisico.z04_numcgm = rhpessoal.rh01_numcgm and cgmfisico.z04_nomesocial <> ''
							inner join rechumanoescola  on  rechumanoescola.ed75_i_rechumano = rechumano.ed20_i_codigo
							inner join escola  on  escola.ed18_i_codigo = rechumanoescola.ed75_i_escola
							left join relacaotrabalho  on  relacaotrabalho.ed23_i_rechumanoescola = rechumanoescola.ed75_i_codigo
							left join tipohoratrabalho on  tipohoratrabalho.ed128_codigo = ed23_tipohoratrabalho
							left join rechumanoativ  on  rechumanoativ.ed22_i_rechumanoescola = rechumanoescola.ed75_i_codigo
							left join atividaderh  on  atividaderh.ed01_i_codigo = rechumanoativ.ed22_i_atividade
							left join disciplina  on  disciplina.ed12_i_codigo = relacaotrabalho.ed23_i_disciplina
							left join caddisciplina on ed232_i_codigo= ed12_i_caddisciplina
							left join ensino  on  ensino.ed10_i_codigo = disciplina.ed12_i_ensino
							inner join pais  on  pais.ed228_i_codigo = rechumano.ed20_i_pais
							left  join censouf as censoufident on  censoufident.ed260_i_codigo = rechumano.ed20_i_censoufident
							left  join censouf as censoufnat on  censoufnat.ed260_i_codigo = rechumano.ed20_i_censoufnat
							left  join censouf as censoufcert on  censoufcert.ed260_i_codigo = rechumano.ed20_i_censoufcert
							left  join censouf as censoufender on  censoufender.ed260_i_codigo = rechumano.ed20_i_censoufender
							left  join censomunic as censomunicnat on  censomunicnat.ed261_i_codigo = rechumano.ed20_i_censomunicnat
							left  join censomunic as censomunicender on  censomunicender.ed261_i_codigo = rechumano.ed20_i_censomunicender
							left  join censoorgemissrg  on  censoorgemissrg.ed132_i_codigo = rechumano.ed20_i_censoorgemiss
							left  join censocartorio  on  censocartorio.ed291_i_codigo = rechumano.ed20_i_censocartorio
							left  join regimetrabalho on  regimetrabalho.ed24_i_codigo = relacaotrabalho.ed23_i_regimetrabalho
							where
							ed20_i_codigo = (
											select
											distinct ed58_i_rechumano
											from
											regenciahorario
											where
											ed58_i_regencia = {$regente}
											and ed58_ativo is true
											order by ed58_i_rechumano
											)
						  )
	    ";
		$sql2 = pg_query($sql);
		$prof = '';
		if( pg_num_rows($sql2) > 1 )
		{
			for($x=0;$x<pg_num_rows($sql2);$x++)
			{
				if( $x > 0)
				{
					$prof .= "-";
				}
				$reg  = db_utils::fieldsMemory($sql2,$x);
				$prof .=  $reg->z01_nome;
			}
		}else{

			$reg = db_utils::fieldsMemory($sql2,0);
			$prof =  $reg->z01_nome;
		}
    }
    return $prof;
}

function buscaRegenteD1($turma,$disciplina){
   $sqla = "
          select
		  ed59_i_codigo
		  from regencia
		  inner join disciplina    on ed59_i_disciplina    = ed12_i_codigo
		  inner join caddisciplina on ed12_i_caddisciplina = ed232_i_codigo
		  where
		  ed59_i_turma = {$turma}
		  and
		  ed232_c_descr = '".$disciplina."'
          ";

  $sql = pg_query($sqla);
  $resultado = pg_fetch_all($sql);
  $regente   = $resultado[0]['ed59_i_codigo'];
  return $regente;
}

$oPdf->Output();

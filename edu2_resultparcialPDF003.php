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

require_once(modification("fpdf151/pdfwebseller.php"));
require_once(modification("std/DBDate.php"));
require_once(modification("std/db_stdClass.php"));
require_once(modification("dbforms/db_funcoes.php"));
require_once(modification("libs/db_sql.php"));
require_once(modification("libs/db_stdlib.php"));
require_once(modification("libs/db_conecta.php"));
require_once(modification("libs/db_sessoes.php"));
require_once(modification("libs/db_utils.php"));
require_once(modification("libs/JSON.php"));
require_once(modification("libs/db_usuariosonline.php"));
require_once(modification("libs/db_app.utils.php"));
require_once(modification("libs/exceptions/BusinessException.php"));
require_once(modification("libs/exceptions/ParameterException.php"));
require_once(modification("libs/exceptions/DBException.php"));

/**
 * Relatório de Conselho de Classe
 * filtros passados na url
 *  - periodo
 *  - trocaTurma
 *  - classificacaoAlunoTurma
 *  - turmas = pode ser informado mais de um código que será separado por virgula.
 *             OBS.: não é o código da turma e sim o código da: turmaserieregimemat
 * relatório
 * - quebra página por turma
 * - possui as seguintes colunas:
 * -- Nº = classificação do aluno (opcional, ver filtro $oConfigRelatorio->lClassificacaoAlunoTurma)
 * -- Aluno = nome do aluno
 * -- S = situacao do aluno
 * -- Parecere
 * -- [Disciplinas] = todas disciplinas da turma (Config Padrão: $oConfigRelatorio->iMaximoDisciplinaPagina = 10)
 * -- TF            = Total de faltas
 */


    $sql = "select ed18_c_nome,
                                   j14_nome,
                                   ed18_i_numero,
                                   j13_descr,
                                   ed261_c_nome,
                                   ed260_c_sigla,
                                   ed18_c_email,
                                   ed18_c_logo,
                                   ed18_codigoreferencia
                             from escola
                              inner join bairro  on  bairro.j13_codi = escola.ed18_i_bairro
                              inner join ruas  on  ruas.j14_codigo = escola.ed18_i_rua
                              inner join db_depart  on  db_depart.coddepto = escola.ed18_i_codigo
                              inner join censouf  on  censouf.ed260_i_codigo = escola.ed18_i_censouf
                              inner join censomunic  on  censomunic.ed261_i_codigo = escola.ed18_i_censomunic
                              left join ruascep on ruascep.j29_codigo = ruas.j14_codigo
                              left join logradcep on logradcep.j65_lograd = ruas.j14_codigo
                              left join ceplogradouros on ceplogradouros.cp06_codlogradouro = logradcep.j65_ceplog
                              left join ceplocalidades on ceplocalidades.cp05_codlocalidades = ceplogradouros.cp06_codlocalidade
                             where ed18_i_codigo = ".db_getsession("DB_coddepto");
   $resultado = db_query($sql);
   db_fieldsmemory($resultado,0);



$oGet  = db_utils::postMemory($_GET);
$oJson = new Services_JSON();
unlink('arquivo.csv');
$aTurmasSelecionadas = $oJson->decode(str_replace("\\", "", $oGet->oTurmas));

$aFiltroParametro = array();
$aFiltroParametro[] = null;
$aFiltroParametro[] = "ed233_c_notabranca";
$aFiltroParametro[] = null;
$aFiltroParametro[] = " ed233_i_escola = " . db_getsession("DB_coddepto");
$aParametroGlobal   = db_stdClass::getParametro("edu_parametros", $aFiltroParametro, "ed233_c_notabranca");

/**
 * Objeto com a configuração do relatório
 */
$oConfigRelatorio = new stdClass();
$oConfigRelatorio->lTrocaTurma              = $oGet->trocaTurma == 'Sim' ? true : false;
$oConfigRelatorio->lClassificacaoAlunoTurma = $oGet->classificacaoAlunoTurma == 'Sim' ? true : false;
$oConfigRelatorio->comLegenda               = $oGet->comLegenda == 'Sim' ? true : false;
$oConfigRelatorio->iFonteAvaliacao          = $oGet->tamanhoFonte;
$oConfigRelatorio->iMaximoDisciplinaPagina  = 10; // Nº disciplina por página  (Máximo 10)
$oConfigRelatorio->iAlunosPorPagina         = 35; // Nº de alunos por página
$oConfigRelatorio->iAlturaLinha             = 4;  // Altura da linha
$oConfigRelatorio->iColunaNome              = 34; // Largura da coluna Aluno
$oConfigRelatorio->iColunaNumero            = 5;  // Largura da coluna Nº, S (Situação) e TF (Total de Faltas)
$oConfigRelatorio->iColunaCodigo            = 9;  // CódigoAluno
$oConfigRelatorio->iColunaPareceres         = 18; // Coluna Pareceres
$oConfigRelatorio->iAlturaLine              = 183; //


$iSomaColunasDadosAluno  = ($oConfigRelatorio->iColunaNome + $oConfigRelatorio->iColunaCodigo);
$iSomaColunasDadosAluno += $oConfigRelatorio->iColunaPareceres;

/**
 * Por que o calculo abaixo?
 * Como visto no comentário da variável ($oConfigRelatorio->iColunaNumero) ela é utilizada para definir o tamanho
 * de três colunas.
 * -- Nº, S (Situação) e TF (Total de Faltas)
 * Sendo assim calculamos quantas vezes ela será descontada da $oConfigRelatorio->iLarguraTotalDisciplinas
 */
if ($oConfigRelatorio->lTrocaTurma) {
  $iSomaColunasDadosAluno += ($oConfigRelatorio->iColunaNumero * 3);
} else {
  $iSomaColunasDadosAluno += ($oConfigRelatorio->iColunaNumero * 2);
}


$oConfigRelatorio->iLarguraTotalDisciplinas  = 282 - $iSomaColunasDadosAluno;
$oConfigRelatorio->iLarguraTotalDisciplinas -= (0.3 * $oConfigRelatorio->iMaximoDisciplinaPagina);
$oConfigRelatorio->lCalculaMediaParcial      = $aParametroGlobal[0]->ed233_c_notabranca == 'S' ? true : false;

$aTurmas = array();
/**
 * Cria a instancia de todas turmas selecionas no filtro
 * Organizamos os dados a serem impressos no relatório
 */
foreach ($aTurmasSelecionadas as $oTurmaSelecionada) {

  $oTurma = TurmaRepository::getTurmaByCodigo($oTurmaSelecionada->iTurma);
  $oEtapa = EtapaRepository::getEtapaByCodigo($oTurmaSelecionada->iEtapa);

  if (empty($oTurma)) {
    continue;
  }

  $oTurmaEtapa           = new stdClass();
  $oTurmaEtapa->aAlunos  = array();
  $oTurmaEtapa->aPaginas = array();
  $iContDisciplinas      = 0;

  /**
   * Verificamos quantas páginas terá o relatório
   */
  foreach ($oTurma->getDisciplinasPorEtapa($oEtapa) as $oRegencia) {

    $iContDisciplinas ++;

    if ($iContDisciplinas <= $oConfigRelatorio->iMaximoDisciplinaPagina) {
      $oTurmaEtapa->aPaginas[0][$oRegencia->getCodigo()] = $oRegencia;
    } else if ($iContDisciplinas > $oConfigRelatorio->iMaximoDisciplinaPagina &&
               $iContDisciplinas <= ($oConfigRelatorio->iMaximoDisciplinaPagina * 2)) {
      $oTurmaEtapa->aPaginas[1][$oRegencia->getCodigo()] = $oRegencia;
    } else {
      $oTurmaEtapa->aPaginas[2][$oRegencia->getCodigo()] = $oRegencia;
    }
  }


  /**
   * Informações referente a turma
   */
  $oTurmaEtapa->sTurma          = $oTurma->getDescricao();
  $oTurmaEtapa->sEtapa          = $oEtapa->getNome();
  $oTurmaEtapa->sTurno          = $oTurma->getTurno()->getDescricao();
  $oTurmaEtapa->sCalendario     = $oTurma->getCalendario()->getDescricao();
  $oTurmaEtapa->iAnoCalendario  = $oTurma->getCalendario()->getAnoExecucao();
  $oTurmaEtapa->sCurso          = $oTurma->getBaseCurricular()->getCurso()->getNome();
  $oTurmaEtapa->sFormaAvaliacao = null;
  $oTurmaEtapa->sPeriodo        = null;
  $oTurmaEtapa->lUltimoPeriodo  = false;
  $oTurmaEtapa->lJaCalculado    = false; // Controle utilizado quando $oTurmaEtapa->lUltimoPeriodo = true
  $oTurmaEtapa->iColunaNome     = $oConfigRelatorio->iColunaNome;

  /**
   * Localizamos qual forma de avaliacao para o período selecionado
   */
  $oAvaliacaoPeriodica = null;

  $iOrdemPeriodoSelecionado = 0;
  $iOrdemUltimoPeriodoTurma = 0;
  foreach ($oTurma->getProcedimentoDeAvaliacaoDaEtapa($oEtapa)->getElementos() as $oAvaliacaoPeriodicaTurma) {


    if ($oAvaliacaoPeriodicaTurma->isResultado()) {
      continue;
    }
    $iOrdemUltimoPeriodoTurma = $oAvaliacaoPeriodicaTurma->getPeriodoAvaliacao()->getOrdemPeriodo();

    if ($oAvaliacaoPeriodicaTurma->getPeriodoAvaliacao()->getCodigo() == $oGet->periodo) {

      $iOrdemPeriodoSelecionado         = $oAvaliacaoPeriodicaTurma->getPeriodoAvaliacao()->getOrdemPeriodo();
      $oTurmaEtapa->sNomeFormaAvaliacao = $oAvaliacaoPeriodicaTurma->getFormaDeAvaliacao()->getDescricao();
      $oTurmaEtapa->sFormaAvaliacao     = $oAvaliacaoPeriodicaTurma->getFormaDeAvaliacao()->getTipo();
      $oTurmaEtapa->sPeriodo            = $oAvaliacaoPeriodicaTurma->getPeriodoAvaliacao()->getDescricao();
      $oAvaliacaoPeriodica              = $oAvaliacaoPeriodicaTurma;
    }
  }

  if ($iOrdemUltimoPeriodoTurma == $iOrdemPeriodoSelecionado) {
    $oTurmaEtapa->lUltimoPeriodo = true;
  }

  /**
   * Buscamos os dados do aluno e suas avalições para o periodo selecionado.
   * Organizamos a estrutura das avaliações dos alunos pelo código da regencia
   */
  foreach ($oTurma->getAlunosMatriculadosNaTurmaPorSerie($oEtapa) as $oMatricula) {


    $oDadosAluno                      = new stdClass();
    $oDadosAluno->iMatricula          = $oMatricula->getCodigo();
    $oDadosAluno->sNome               = abreviar($oMatricula->getAluno()->getNome(), 22, true);
    $oDadosAluno->iCodigoAluno        = $oMatricula->getAluno()->getCodigoAluno();
    $oDadosAluno->sSituacao           = $oMatricula->getSituacao();
    $oDadosAluno->oDtMatricula        = $oMatricula->getDataMatricula();
    $oDadosAluno->iClassificacao      = $oMatricula->getNumeroOrdemAluno();
    $oDadosAluno->aAvaliacao          = array();
    $oDadosAluno->iTotalFaltas        = 0;
    $oDadosAluno->lAvaliadoPorParecer = $oMatricula->isAvaliadoPorParecer();

    db_inicio_transacao();

    $oDiarioDeClasse = $oMatricula->getDiarioDeClasse();

    $iContDisciplinas = 0;

    foreach ($oTurma->getDisciplinasPorEtapa($oEtapa) as $oRegencia) {

      $iContDisciplinas ++;
      $oDisciplinaDiario        = $oDiarioDeClasse->getDisciplinasPorRegencia($oRegencia, $oAvaliacaoPeriodica);
      $oAvaliacaoAproveitamento = $oDisciplinaDiario->getAvaliacoesPorOrdem($oAvaliacaoPeriodica->getOrdemSequencia());

      $oAvaliacao = new stdClass();
      $oAvaliacao->iRegencia       = $oRegencia->getCodigo();
      $oAvaliacao->sRegencia       = $oRegencia->getDisciplina()->getNomeDisciplina();
      $oAvaliacao->sRegenciaAbrev  = $oRegencia->getDisciplina()->getAbreviatura();
      $oAvaliacao->iFaltas         = $oAvaliacaoAproveitamento->getTotalFaltas() + $oAvaliacaoAproveitamento->getFaltasAbonadas();
      $oAvaliacao->mAproveitamento = $oAvaliacaoAproveitamento->getValorAproveitamento();
      $oAvaliacao->lAtingiuMinimo  = $oAvaliacaoAproveitamento->temAproveitamentoMinimo();
      $oAvaliacao->lNotaExterna    = $oAvaliacaoAproveitamento->isAvaliacaoExterna();
      $oAvaliacao->lAmparado       = $oAvaliacaoAproveitamento->isAmparado();
      $oAvaliacao->sTipoAmparo     = 'AMP';

      if ($oAvaliacao->lAmparado && $oDisciplinaDiario->getAmparo()->getCodigoConvencaoAmparo() != '') {
        $oAvaliacao->sTipoAmparo = $oDisciplinaDiario->getAmparo()->getConvencao()->getAbreviatura();
      }


      $oAvaliacao->mNotaParcial    = $oDisciplinaDiario->getNotaParcial($oAvaliacaoPeriodica);
      $oAvaliacao->sTipoAvaliacao  = $oAvaliacaoAproveitamento->getElementoAvaliacao()->getFormaDeAvaliacao()->getTipo();

      unset($oAvaliacaoAproveitamento);
      /**
       * Como uma turma pode ter mais de 10 disciplinas, devemos quebrar página e continuar imprimindo as disciplinas
       * restantes. Neste bloco definimos até três páginas de para uma turma com até 30 disciplinas (Não sendo alterada
       * a configuração padrão de 10 disciplinas por página)
       * - 1º página : de 1  á 10 disciplinas
       * - 2º página : de 11 á 20 disciplinas
       * - 3º página : de 21 á 30 disciplinas
       */
      if ($iContDisciplinas <= $oConfigRelatorio->iMaximoDisciplinaPagina) {
        $oDadosAluno->aAvaliacao[0][$oRegencia->getCodigo()] = $oAvaliacao;
      } else if ($iContDisciplinas > $oConfigRelatorio->iMaximoDisciplinaPagina &&
                 $iContDisciplinas <= ($oConfigRelatorio->iMaximoDisciplinaPagina * 2)) {
        $oDadosAluno->aAvaliacao[1][$oRegencia->getCodigo()] = $oAvaliacao;
      } else {
        $oDadosAluno->aAvaliacao[2][$oRegencia->getCodigo()] = $oAvaliacao;
      }

      $oDadosAluno->iTotalFaltas  += $oAvaliacao->iFaltas;
    }

    $oTurmaEtapa->aAlunos[] = $oDadosAluno;
    MatriculaRepository::removerMatricula($oMatricula);
    db_fim_transacao();

  }

  $aTurmas[] = $oTurmaEtapa;
  TurmaRepository::removerTurma($oTurma);
  EtapaRepository::removerEtapa($oEtapa);
}

//$oPdf = new PDF("L");
//$oPdf->Open();
//$oPdf->AliasNbPages();
//$oPdf->SetAutoPageBreak(true);
//$oPdf->SetFillColor(215);
//$oPdf->SetMargins(8, 10);
//$oPdf->SetLineWidth(0);

 
foreach ($aTurmas as $oTurmaEtapa) {

  $head1 = "Conselho de Classe";
  $head2 = "Curso: {$oTurmaEtapa->sCurso}";
  $head3 = "Turma: {$oTurmaEtapa->sTurma}";
  $head4 = "Calendário: {$oTurmaEtapa->sCalendario}";
  $head5 = "Etapa: {$oTurmaEtapa->sEtapa}";
  $head6 = "Turno: {$oTurmaEtapa->sTurno}";
  $head7 = "Período: {$oTurmaEtapa->sPeriodo}";
  $head8 = "Forma de Avaliação: {$oTurmaEtapa->sNomeFormaAvaliacao}";

  $lPrimeiraPagina = true;
  $iPaginas        = count($oTurmaEtapa->aPaginas);

  for ($iPagina = 0; $iPagina < $iPaginas; $iPagina ++) {
    $iAlunosImpressoPagina = 0;
    $iAlunosTurma          = count($oTurmaEtapa->aAlunos);

    $iTotalDisciplina      = $oTurmaEtapa->iTotalDisciplina;
    $iLarguraCelulaParecer = $oTurmaEtapa->iLarguraCelulaParecer;
    $iLarguraDisciplina    = $oTurmaEtapa->iLarguraDisciplina;
    $iLarguraAvaliacao     = $iLarguraDisciplina - 5;
    $linhaimprimir         = "";
    foreach ($oTurmaEtapa->aAlunos as $oAluno) {//****************************************************************************************************************************************

      if ( $lPrimeiraPagina ) {
		  
		$arq = fopen("arquivo.csv","a+");
		fwrite($arq, 'PREFEITURA MUNICIPAL DE VOLTA REDONDA;;;;;Conselho de classe');
		fwrite($arq,"\r\n");
		fclose($arq); 		
		
		$arq = fopen("arquivo.csv","a+");
		fwrite($arq, $ed18_c_nome.' ;;;;;'.$head2);
		fwrite($arq,"\r\n");
		fclose($arq); 		
		
		$arq = fopen("arquivo.csv","a+");
		fwrite($arq, $j14_nome.', '.$ed18_i_numero.';;;;;'.$head3);
		fwrite($arq,"\r\n");
		fclose($arq); 		
		
		$arq = fopen("arquivo.csv","a+");
		fwrite($arq, 'VOLTA REDONDA -RJ;;;;;'.$head4);
		fwrite($arq,"\r\n");
		fclose($arq); 		
		  
        $lPrimeiraPagina = false;
		$linhaimprimirCabecalho = 'Nº;Nome do Aluno;S;Código;par;par;par;'.adicionaHeader($oTurmaEtapa, $oConfigRelatorio, $iPagina).'TF';
		$arq = fopen("arquivo.csv","a+");
		fwrite($arq, $linhaimprimirCabecalho);
		fwrite($arq,"\r\n");
		fclose($arq); 		
      }

      if (!$oConfigRelatorio->lTrocaTurma && $oAluno->sSituacao == 'TROCA DE TURMA') {
        continue;
      }

      $iAlunosImpressoPagina ++;
      $iLarguraCelulaNome = $oConfigRelatorio->iColunaNumero + $oTurmaEtapa->iColunaNome;
      if ($oConfigRelatorio->lClassificacaoAlunoTurma) {
        $iLarguraCelulaNome = $oTurmaEtapa->iColunaNome;
		$linhaimprimir .= $oAluno->iClassificacao.';';
      }
      $linhaimprimir .= $oAluno->sNome.';';
      $sSituacaoAbreviada = buscaAbreviaturaSituacao($oAluno->sSituacao);
	  $linhaimprimir .= $sSituacaoAbreviada.';';
	  $linhaimprimir .= $oAluno->iCodigoAluno.';;;;';
	  
      foreach ($oAluno->aAvaliacao[$iPagina] as $oAvaliacao) {

        $mAvaliacao = $oAvaliacao->mAproveitamento->getAproveitamento();
        if ($oAvaliacao->sTipoAvaliacao == 'PARECER') {
          $mAvaliacao = 'PARECER';
          $oAvaliacao->lAtingiuMinimo = true;
        }

        $mAvaliacao = ArredondamentoNota::formatar($mAvaliacao, $oTurmaEtapa->iAnoCalendario);
        if ($oAvaliacao->lNotaExterna && $mAvaliacao != '') {
          $mAvaliacao .= "*";
        }
        if ( $oAluno->lAvaliadoPorParecer && !empty($mAvaliacao)) {
          $mAvaliacao .= "PD;";
        }

        if ($oAvaliacao->lAmparado) {

          $mAvaliacao = $oAvaliacao->sTipoAmparo;
          $oAvaliacao->lAtingiuMinimo = true;
        }


        if ($oAvaliacao->sTipoAvaliacao == 'NOTA' && $oConfigRelatorio->lCalculaMediaParcial) {
		  $linhaimprimir .= $mAvaliacao.';';
		  $linhaimprimir .= $oAvaliacao->mNotaParcial.';';		  
        } else {
		  $linhaimprimir .= $mAvaliacao.';';
        }
		$linhaimprimir .= $oAvaliacao->iFaltas.';';
      }

	  $linhaimprimir .= $oAluno->iTotalFaltas.';';

      $arq = fopen("arquivo.csv","a+");
      fwrite($arq, $linhaimprimir);
      fwrite($arq,"\r\n");
      fclose($arq); 		
      $linhaimprimir = "";
    }//*************************************************************************************************************************************************
download("arquivo.csv");
    /**
     * Imprime linhas em branco para fechar o total de aluno por página
     */
  }
  unset($oTurmaEtapa);
  //header("Location: edu2_resultparcial001.php");
?>
<script>
   window.close();
</script>
<?

}


/**
 * Renderiza quadro das legendas
 * @param FPDF $oPdf
 * @param stdClass $oConfigRelatorio
 */
function montaQuadroLegendaAssinatura(FPDF $oPdf, $oConfigRelatorio, $lUltimoPeriodo, $oTurmaEtapa) {

  /**
   * Soma com base na configuração do layout a largura do quadro
   */
  $iLarguraQuadro  = $oConfigRelatorio->iLarguraTotalDisciplinas;
  $iLarguraQuadro += $oConfigRelatorio->iColunaCodigo ;
  $iLarguraQuadro += $oTurmaEtapa->iColunaNome;
  $iLarguraQuadro += ($oConfigRelatorio->iColunaNumero*3);
  if (!$lUltimoPeriodo) {
    $iLarguraQuadro += $oConfigRelatorio->iColunaPareceres;
  }

  $iXInicial = $oPdf->GetX();
  $iYInicial = $oPdf->GetY();
  $oPdf->Rect($oPdf->GetX(), $oPdf->GetY(), $iLarguraQuadro, 13);

  if ($oConfigRelatorio->comLegenda) {

    $sLegendasCabeçalho  = "Nº: Número da classificação do aluno;  S: Saída;  Ft.: Nº de faltas na disciplina;";
    $sLegendasCabeçalho .= "  NT: Nota;  NP: Nota parcial;  TF: Total de faltas no período.";

    $sLegendasSituacao  = montaLegendaSituacoes();

    $oPdf->SetY($iYInicial+1);
    $oPdf->SetFont("arial", 'b', 5);

    $iAlturaLinhaLegenda = $oConfigRelatorio->iAlturaLinha - 1.5;

    $oPdf->Cell(20, $iAlturaLinhaLegenda, "Legendas Cabeçalho: ");
    $oPdf->SetFont("arial", '', 5);
    $oPdf->MultiCell(175, $iAlturaLinhaLegenda, $sLegendasCabeçalho);

    $oPdf->SetFont("arial", 'b', 5);
    $oPdf->Cell(20, $iAlturaLinhaLegenda, "Legendas Situação: ");
    $oPdf->SetFont("arial", '', 5);
    $oPdf->MultiCell(170, $iAlturaLinhaLegenda, $sLegendasSituacao);
  }

  /**
   * Assinatura
  */
  $oPdf->SetFont("arial", 'b', 5);
  $oPdf->SetXY($iXInicial+200, $iYInicial+9);
  $oPdf->Cell(81, $oConfigRelatorio->iAlturaLinha, "Regente Conselheiro", 0, 0, 'C');
  $oPdf->Line($iXInicial+200, $iYInicial+9, $iXInicial+$iLarguraQuadro, $iYInicial+9);
}


/**
 * Realiza o calculo, em tempo de execução, de variáveis de controle
 * @param FPDF     $oPdf
 * @param stdClass $oTurmaEtapa
 * @param stdClass $oConfigRelatorio
 * @param integer  $iPagina
 */
function calculaTamanhoDeCelulasDinamicas(FPDF $oPdf, $oTurmaEtapa, $oConfigRelatorio, $iPagina) {

  $oTurmaEtapa->iTotalDisciplina   = count($oTurmaEtapa->aPaginas[$iPagina]);
  $oTurmaEtapa->iLarguraDisciplina = $oConfigRelatorio->iLarguraTotalDisciplinas / $oConfigRelatorio->iMaximoDisciplinaPagina;

  $oTurmaEtapa->iLarguraCelulaParecer = $oConfigRelatorio->iColunaPareceres / 3;

  if ($oTurmaEtapa->lUltimoPeriodo && !$oTurmaEtapa->lJaCalculado) {

    $oTurmaEtapa->lJaCalculado          = true;
    $oTurmaEtapa->iColunaNome     += $oConfigRelatorio->iColunaPareceres;
    $oTurmaEtapa->iLarguraCelulaParecer = 0;
  }

}

/**
 * Adiciona o cabeçalho
 * @param FPDF     $oPdf
 * @param stdClass $oTurmaEtapa
 * @param stdClass $oConfigRelatorio
 * @param integer  $iPagina
 */
function adicionaHeader($oTurmaEtapa, $oConfigRelatorio, $iPagina) {

  /**
   * Primeira linha do cabeçalho
   */
   $linha1 ='';
  $iLargura = ($oConfigRelatorio->iColunaNumero * 2) + $oTurmaEtapa->iColunaNome + $oConfigRelatorio->iColunaCodigo;
  if ($oConfigRelatorio->lClassificacaoAlunoTurma) {

    $iLargura = $oConfigRelatorio->iColunaNumero + $oTurmaEtapa->iColunaNome + $oConfigRelatorio->iColunaCodigo;
  }

  $iTotalDisciplina   = $oTurmaEtapa->iTotalDisciplina;
  $iLarguraDisciplina = $oTurmaEtapa->iLarguraDisciplina;

  foreach ($oTurmaEtapa->aPaginas[$iPagina] as $oRegencia) {

    $sAbreviatura = $oRegencia->getDisciplina()->getAbreviatura();
    $linha1 .= $sAbreviatura.' Nota;Falta;';

  }

  /**
   * Imprime colunas de avaliação vazia
   */
  /**
   * Segunda linha do cabeçalho
   */
  $iLargura = $oConfigRelatorio->iColunaNumero  + $oTurmaEtapa->iColunaNome;
  if ($oConfigRelatorio->lClassificacaoAlunoTurma) {
    $iLargura = $oTurmaEtapa->iColunaNome;
  }


  foreach ($oTurmaEtapa->aPaginas[$iPagina] as $oRegencia) {

    $sFormaAvaliacao   = $oRegencia->getProcedimentoAvaliacao()->getFormaAvaliacao()->getTipo();
    $iLarguraAvaliacao = $iLarguraDisciplina - 5;

  }

  /**
   * Imprime colunas de avaliação vazia 2º linha do cabeçalho
   */
   
   return $linha1;
}


/**
 * Imprime vazio os quadros das avaliações
 * @param FPDF     $oPdf
 * @param stdClass $oTurmaEtapa      -> Dados da turma
 * @param integer  $iTotalDisciplina -> Número de disciplina da página atual
 * @param stdClass $oConfigRelatorio -> Objeto de configuração
 * @param boolean  $lPrimeiraLinha   -> Se é a primeira linha do cabeçalho
 */
function imprimeQuadroAvaliacaoVazio (FPDF $oPdf, $oTurmaEtapa, $iTotalDisciplina, $oConfigRelatorio,
                                      $lPrimeiraLinha = true) {

  for ($i = $iTotalDisciplina; $i < $oConfigRelatorio->iMaximoDisciplinaPagina; $i++) {

    if ($lPrimeiraLinha) {

      $oPdf->Cell($oTurmaEtapa->iLarguraDisciplina, $oConfigRelatorio->iAlturaLinha, '', 1, 0, "C");
      imprimeLinhaSeparadora ($oPdf, $oConfigRelatorio);
    } else {

      $iLarguraAvaliacao = $oTurmaEtapa->iLarguraDisciplina - 5;

      if ($oTurmaEtapa->sFormaAvaliacao == 'NOTA' && $oConfigRelatorio->lCalculaMediaParcial) {

        $oPdf->Cell($iLarguraAvaliacao/2, $oConfigRelatorio->iAlturaLinha, '', 1);
        $oPdf->Cell($iLarguraAvaliacao/2, $oConfigRelatorio->iAlturaLinha, '', 1);
      } else {
        $oPdf->Cell($iLarguraAvaliacao, $oConfigRelatorio->iAlturaLinha, '', 1);
      }
      $oPdf->Cell(5,  $oConfigRelatorio->iAlturaLinha, '', 1);
    }
  }
}

/**
 * Imprime a linha vertical que separa as disciplinas
 * @param FPDF     $oPdf
 * @param stdClass $oConfigRelatorio
 */
function imprimeLinhaSeparadora (FPDF $oPdf, $oConfigRelatorio) {

  $oPdf->SetLineWidth(0.3);
  $oPdf->Line($oPdf->GetX(), $oPdf->GetY(), $oPdf->GetX(), $oConfigRelatorio->iAlturaLine);
  $oPdf->SetLineWidth(0);
}

/**
 * Imprime celulas para lançar o parecer
 * @param FPDF     $oPdf
 * @param integer  $iLarguraCelulaParecer
 * @param stdClass $oConfigRelatorio
 */
function imprimeCelulasParecer(FPDF $oPdf, $iLarguraCelulaParecer, $oConfigRelatorio) {

  $oPdf->Cell($iLarguraCelulaParecer, $oConfigRelatorio->iAlturaLinha, '', 1);
  $oPdf->Cell($iLarguraCelulaParecer, $oConfigRelatorio->iAlturaLinha, '', 1);
  $oPdf->Cell($iLarguraCelulaParecer, $oConfigRelatorio->iAlturaLinha, '', 1);
}

/**
 * Imprime linhas em branco para fechar o numero maximo de alunos
 * @param FPDF     $oPdf
 * @param stdClass $oConfigRelatorio
 * @param stdClass  $oTurmaEtapa
 */
function imprimeLinhaEmBranco(FPDF $oPdf, $oConfigRelatorio, $oTurmaEtapa) {

  $iLarguraCelulaNome = $oConfigRelatorio->iColunaNumero + $oTurmaEtapa->iColunaNome;
  if ($oConfigRelatorio->lClassificacaoAlunoTurma) {

    $iLarguraCelulaNome = $oTurmaEtapa->iColunaNome;
    $oPdf->Cell($oConfigRelatorio->iColunaNumero, $oConfigRelatorio->iAlturaLinha, '', 1);
  }
  $oPdf->Cell($iLarguraCelulaNome, $oConfigRelatorio->iAlturaLinha, '', 1);
  $oPdf->Cell($oConfigRelatorio->iColunaNumero, $oConfigRelatorio->iAlturaLinha, '',   1);
  $oPdf->Cell($oConfigRelatorio->iColunaCodigo, $oConfigRelatorio->iAlturaLinha, '', 1);

  if (!$oTurmaEtapa->lUltimoPeriodo) {
    imprimeCelulasParecer($oPdf, $oTurmaEtapa->iLarguraCelulaParecer, $oConfigRelatorio);
  }
  imprimeQuadroAvaliacaoVazio ($oPdf, $oTurmaEtapa, 0, $oConfigRelatorio, false);
  $oPdf->Cell($oConfigRelatorio->iColunaNumero, $oConfigRelatorio->iAlturaLinha, '', 1, 1);

}


/**
 * Retorna uma abreviatura para a situacao do aluno
 * @param string $sSituacaoBusca
 * @return string $sSituacaoRetorno
 */
function buscaAbreviaturaSituacao($sSituacaoBusca) {

  $sSituacaoRetorno = '';

  foreach (getSituacoes() as $sAbrev => $sSituacao) {

    if ($sSituacaoBusca == $sSituacao) {

      $sSituacaoRetorno = $sAbrev;
      break;
    }
  }
  return $sSituacaoRetorno;
}

/**
 * Retorna a legenda das Situações tratadas no relatório
 * @return string
 */
function montaLegendaSituacoes() {

  $sLegenda = '';
  foreach (getSituacoes() as $sAbrev => $sSituacao) {

    $sLegenda .= "{$sAbrev}: $sSituacao;  ";
  }
  return $sLegenda;
}

/**
 * Array com as situações tratadas no relatório
 * @return multitype:string
 */
function getSituacoes() {

  /**
   * Array com as situaçõe da matricula do aluno indexado pela abreviatura
   */
  $aSituacoes       = array();
  $aSituacoes['MT'] = 'MATRICULA TRANCADA';
  $aSituacoes['IN'] = 'MATRICULA INDEFERIDA';
  $aSituacoes['MI'] = 'MATRICULA INDEVIDA';
  $aSituacoes['TR'] = 'TRANSFERIDO REDE';
  $aSituacoes['TF'] = 'TRANSFERIDO FORA';
  $aSituacoes['TT'] = 'TROCA DE TURMA';
  $aSituacoes['TM'] = 'TROCA DE MODALIDADE';
  $aSituacoes['C']  = 'CANCELADO';
  $aSituacoes['E']  = 'EVADIDO';
  $aSituacoes['F']  = 'FALECIDO';

  return $aSituacoes;
}

/**
 * Abrevia o nome do aluno
 * @todo Mover para aluno
 * @param string $nome
 * @param integer $max
 * @param string $substr
 */
function abreviar($nome, $max, $substr=false) {

  if(strlen(trim($nome))>$max){

    $strinv = strrev(trim($nome));
    $ultnome = substr($strinv,0,strpos($strinv," "));
    $ultnome = strrev($ultnome);
    $nome = strrev($strinv);
    $prinome = substr($nome,0,strpos($nome," "));
    $nomes = strtok($nome, " ");
    $iniciais = "";

    while($nomes):
    if(($nomes == 'E') || ($nomes == 'DE') || ($nomes == 'DOS') ||
    ($nomes == 'DAS') || ($nomes == 'DA') || ($nomes == 'DO')){
      $iniciais .= " ".$nomes;
      $nomes = strtok(" ");
    }elseif (($nomes == $ultnome) || ($nomes == $prinome)){
      $nome = "";
      $nomes = strtok(" ");
    }else{
      $iniciais .= " ".$nomes[0].".";
      $nomes = strtok(" ");
    }
    endwhile;

    $nome =  $prinome;
    $nome .= $iniciais;
    $nome .= " ".$ultnome;
  }

  if (!$substr){
    return trim($nome);
  }else{
    return substr(trim($nome),0,20);
  }
}
function download($arquivo){
      header("Content-Type: application/force-download");
      header("Content-Type: application/octet-stream;");
      header("Content-Length:".filesize($arquivo));
      header("Content-disposition: attachment; filename=".$arquivo);
      header("Pragma: no-cache");
      header("Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0");
      header("Expires: 0");
      readfile($arquivo);
      flush();
}
//$oPdf->Output();
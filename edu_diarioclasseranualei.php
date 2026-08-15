<?php 
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

require_once ("fpdf151/pdfwebseller.php");
require_once ("std/DBDate.php");
require_once ("std/db_stdClass.php");
require_once ("dbforms/db_funcoes.php");
require_once ("libs/db_sql.php");
require_once ("libs/db_stdlib.php");
require_once ("libs/db_conecta.php");
require_once ("libs/db_sessoes.php");
require_once ("libs/db_utils.php");
require_once ("libs/JSON.php");
require_once ("libs/db_usuariosonline.php");
require_once ("libs/db_app.utils.php");
require_once ("libs/exceptions/BusinessException.php");
require_once ("libs/exceptions/ParameterException.php");
require_once ("libs/exceptions/DBException.php");

function testa($var){
  echo "<pre>";
  print_r($var);
  echo "</pre>";
}

function buscaPeriodos($calendario, $escola){
  $sql = pg_query("SELECT distinct ed09_i_codigo as codigo_periodo, ed09_c_descr as descricao_periodo from periodocalendario inner join periodoavaliacao on periodoavaliacao.ed09_i_codigo = periodocalendario.ed53_i_periodoavaliacao inner join calendario on calendario.ed52_i_codigo = periodocalendario.ed53_i_calendario inner join calendarioescola on calendarioescola.ed38_i_calendario = calendario.ed52_i_codigo inner join duracaocal on duracaocal.ed55_i_codigo = calendario.ed52_i_duracaocal where ed53_i_calendario in ({$calendario}) and ed38_i_escola in ({$escola}) order by ed09_i_codigo");
  $resultado = pg_fetch_all($sql);
  return $resultado;
}

function voltaFaltas($matricula){
  $sql1 = pg_query("SELECT ed59_i_serie, ed59_i_turma FROM matricula INNER JOIN turma ON ed60_i_turma = ed57_i_codigo INNER JOIN regencia ON ed57_i_codigo = ed59_i_turma WHERE ed60_i_codigo = {$matricula}");
  $r1 = pg_fetch_all($sql1);
  $serie = $r1[0]["ed59_i_serie"];
  $turma = $r1[0]["ed59_i_turma"];

  $sql2 = pg_query("select ed95_i_codigo from diario inner join aluno on ed47_i_codigo = ed95_i_aluno inner join matricula on ed60_i_aluno = ed47_i_codigo inner join matriculaserie on ed60_i_codigo = ed221_i_matricula inner join regencia on ed59_i_codigo = ed95_i_regencia and ed59_i_serie = ed221_i_serie where ed60_i_codigo = {$matricula} and ed95_i_regencia = ed59_i_codigo and ed95_i_serie = {$serie} and ed59_i_turma = {$turma} order by ed95_i_codigo");
  $r2 = pg_fetch_all($sql2);
  $codigo = $r2[0]["ed95_i_codigo"];

  $sql3 = pg_query("select ed72_i_numfaltas from diarioavaliacao inner join procavaliacao on ed41_i_codigo = ed72_i_procavaliacao left join pareceraval on ed93_i_diarioavaliacao = ed72_i_codigo left join abonofalta on ed80_i_diarioavaliacao = ed72_i_codigo where ed72_i_diario = {$codigo} ORDER BY ed72_i_procavaliacao");
  $resultado = pg_fetch_all($sql3);
  return $resultado;
}

function trataNome($nome){
  $tratanome = explode(" ", mb_strtolower($nome));
    $nometratado = "";
    foreach ($tratanome as $particula) {
      if(strlen($particula) > 2){
        $nometratado .= ucfirst($particula) . " ";
      }else{
        $nometratado .= $particula . " ";
      }
    }
    $nometratado = trim($nometratado);    
    $nometratado = mb_strtoupper(mb_substr($nometratado, 0, 1)).mb_substr($nometratado, 1);
    return $nometratado;
}

function retornaDiretor(){
  $sql = pg_query("select 'DIRETOR' as funcao, case when ed20_i_tiposervidor = 1 then cgmrh.z01_nome else cgmcgm.z01_nome end as nome, ed83_c_descr||' n°: '||ed05_c_numero::varchar as descricao,'D' as tipo FROM escoladiretor INNER JOIN turno ON turno.ed15_i_codigo = escoladiretor.ed254_i_turno LEFT JOIN atolegal ON atolegal.ed05_i_codigo = escoladiretor.ed254_i_atolegal LEFT JOIN tipoato ON tipoato.ed83_i_codigo = atolegal.ed05_i_tipoato INNER JOIN rechumano ON rechumano.ed20_i_codigo = escoladiretor.ed254_i_rechumano LEFT JOIN rechumanopessoal ON rechumanopessoal.ed284_i_rechumano = rechumano.ed20_i_codigo LEFT JOIN rhpessoal ON rhpessoal.rh01_regist = rechumanopessoal.ed284_i_rhpessoal LEFT JOIN cgm AS cgmrh ON cgmrh.z01_numcgm = rhpessoal.rh01_numcgm LEFT JOIN rechumanoescola ON rechumanoescola.ed75_i_rechumano = rechumano.ed20_i_codigo LEFT JOIN rechumanoativ ON rechumanoativ.ed22_i_rechumanoescola = rechumanoescola.ed75_i_codigo LEFT JOIN atividaderh ON atividaderh.ed01_i_codigo = rechumanoativ.ed22_i_atividade LEFT JOIN rhpessoalmov ON rh02_anousu = 2018 AND rh02_mesusu = 02 AND rh02_regist = rh01_regist AND rh02_instit = 96 LEFT JOIN rhfuncao ON rhfuncao.rh37_funcao = rhpessoal.rh01_funcao AND rh37_instit = rh02_instit LEFT JOIN rechumanocgm ON rechumanocgm.ed285_i_rechumano = rechumano.ed20_i_codigo LEFT JOIN cgm AS cgmcgm ON cgmcgm.z01_numcgm = rechumanocgm.ed285_i_cgm where ed254_i_escola = ".db_getsession("DB_coddepto")." AND ed254_c_tipo = 'A' AND ed01_i_funcaoadmin = 2");
  $resultado = pg_fetch_all($sql);
  return $resultado[0]["nome"];
}

function buscaobs($calendario, $turma){  
  $sql = pg_query("SELECT obs FROM regocorr WHERE calendario = {$calendario} AND turma = {$turma} ORDER BY calendario, turma, periodo");
  $resultado = pg_fetch_all($sql);
  $obs = "";
  foreach ($resultado as $texto){
    $obs .= $texto["obs"] . " ";
  }

  return rtrim($obs, "");;
}

$escola = db_getsession("DB_coddepto");
$calendario = $_GET["calendario"];
$periodos = buscaPeriodos($calendario, $escola);

$assinaturaadicional = "nao";
if($_GET["aa"] != "nao"){
  $nomea = $_GET["aa"];
  $ativaa = trataNome($_GET["at"]);
  $assinaturaadicional = "sim";
}

//testa($_GET); die("Confere"); 

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

$oGet  = db_utils::postMemory($_GET);
$oJson = new Services_JSON();
//testa($oGet);
$aTurmasSelecionadas = $oJson->decode(str_replace("\\", "", $oGet->oTurmas));
$oGet->periodo = $periodos[0]["codigo_periodo"];
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
//$oConfigRelatorio->iAlunosPorPagina         = 35; // Nº de alunos por página
$oConfigRelatorio->iAlunosPorPagina         = 32; // Nº de alunos por página
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
//testa($aTurmasSelecionadas); die("confere");
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
  $oTurmaEtapa->diasLetivos = $oTurma->getCalendario()->getDiasLetivos();
  $oTurmaEtapa->Regente = $oTurma->getProfessorConselheiro()->getNome();
  
  $oTurmaEtapa->xcalendario = $oTurma->getCalendario()->getCodigo();
  $oTurmaEtapa->xturma = $oTurma->getCodigo();
  

  /**
   * Localizamos qual forma de avaliacao para o período selecionado
   */
  $oAvaliacaoPeriodica = null;

  $iOrdemPeriodoSelecionado = 0;
  $iOrdemUltimoPeriodoTurma = 0;
  //testa($oTurma->getProcedimentoDeAvaliacaoDaEtapa($oEtapa)->getElementos()); die("confere");
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

  //testa($oAvaliacaoPeriodica); 

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
    //$oDadosAluno->sNome               = abreviar($oMatricula->getAluno()->getNome(), 22, true);
    $oDadosAluno->sNome               = $oMatricula->getAluno()->getNome();
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

$oPdf = new PDF("L");
$oPdf->Open();
$oPdf->AliasNbPages();
$oPdf->SetAutoPageBreak(true);
$oPdf->SetFillColor(215);
$oPdf->SetMargins(8, 10);
$oPdf->SetLineWidth(0);
$oPdf->imprime_rodape = false;

foreach ($aTurmas as $oTurmaEtapa) {
  
  $head1 = "Resumo Anual";
  $head2 = "Curso: {$oTurmaEtapa->sCurso}";
  $head3 = "Turma: {$oTurmaEtapa->sTurma}";
  $head4 = "Calendário: {$oTurmaEtapa->sCalendario}";
  $head5 = "Etapa: {$oTurmaEtapa->sEtapa}";
  $head6 = "Turno: {$oTurmaEtapa->sTurno}";  

  $lPrimeiraPagina = true;
  $iPaginas        = count($oTurmaEtapa->aPaginas);
  $diasletivos = $oTurmaEtapa->diasLetivos;

  for ($iPagina = 0; $iPagina < $iPaginas; $iPagina ++) {

    /**
     * A cada página, calcula em tempo de excucao o tamanho de variáveis necessárias para o calculo
     * de algumas colunas do relatório. Essas variáveis serão recalculádas a cada turma e pagina emitida
     */
    calculaTamanhoDeCelulasDinamicas($oPdf, $oTurmaEtapa, $oConfigRelatorio, $iPagina);

    $iAlunosImpressoPagina = 0;
    $iAlunosTurma          = count($oTurmaEtapa->aAlunos);

    $iTotalDisciplina      = $oTurmaEtapa->iTotalDisciplina;
    $iLarguraCelulaParecer = $oTurmaEtapa->iLarguraCelulaParecer;
    $iLarguraDisciplina    = $oTurmaEtapa->iLarguraDisciplina;
    $iLarguraAvaliacao     = $iLarguraDisciplina - 5;
    
    foreach ($oTurmaEtapa->aAlunos as $oAluno) {      
      $xfaltas = voltaFaltas($oAluno->iMatricula);      
      
      if ( $lPrimeiraPagina ) {
        $lPrimeiraPagina = false;
        adicionaHeader($oPdf, $oTurmaEtapa, $oConfigRelatorio, $iPagina);
      }

      if (!$oConfigRelatorio->lTrocaTurma && $oAluno->sSituacao == 'TROCA DE TURMA') {
        continue;
      }

      $iAlunosImpressoPagina ++;
      if ($iAlunosImpressoPagina > $oConfigRelatorio->iAlunosPorPagina) {
        $iAlunosImpressoPagina = 1;
        if($assinaturaadicional == "sim"){
          montaQuadroLegendaAssinatura($oPdf, $oConfigRelatorio, $oTurmaEtapa->lUltimoPeriodo, $oTurmaEtapa, "sim", $nomea, $ativaa);
        }else{
          montaQuadroLegendaAssinatura($oPdf, $oConfigRelatorio, $oTurmaEtapa->lUltimoPeriodo, $oTurmaEtapa);
        }
        //montaQuadroLegendaAssinatura($oPdf, $oConfigRelatorio, $oTurmaEtapa->lUltimoPeriodo, $oTurmaEtapa);
        if ($iAlunosImpressoPagina < $iAlunosTurma) {
          adicionaHeader($oPdf, $oTurmaEtapa, $oConfigRelatorio, $iPagina);
        }
      }

      $oPdf->SetFont("arial", '', 7);      
      $oPdf->Cell(5, $oConfigRelatorio->iAlturaLinha, $oAluno->iClassificacao, 1, 0, 'C');      
      $oPdf->Cell(100, $oConfigRelatorio->iAlturaLinha, $oAluno->sNome, 1);
      $oPdf->Cell(10, $oConfigRelatorio->iAlturaLinha, $xfaltas[0]["ed72_i_numfaltas"], 1, 0, 'C');
      $oPdf->Cell(10, $oConfigRelatorio->iAlturaLinha, $xfaltas[1]["ed72_i_numfaltas"], 1, 0, 'C');
      $oPdf->Cell(10, $oConfigRelatorio->iAlturaLinha, $xfaltas[2]["ed72_i_numfaltas"], 1, 0, 'C');
      $oPdf->Cell(20, $oConfigRelatorio->iAlturaLinha, $xfaltas[0]["ed72_i_numfaltas"] + $xfaltas[1]["ed72_i_numfaltas"] + $xfaltas[2]["ed72_i_numfaltas"], 1, 0, 'C');
      /**
       * Autor: Uemerson Santana
       * Data: 02/03/2026
       * Demanda: 18250
       * Razao: Corrigido ordem das operacoes para evitar erro de precisao
       *        de ponto flutuante IEEE 754 (ex: (int)(57.999...) = 57 ao
       *        inves de 58). Multiplicar por 100 antes de dividir.
       */
      $cfreq = (($diasletivos - ($xfaltas[0]["ed72_i_numfaltas"] + $xfaltas[1]["ed72_i_numfaltas"] + $xfaltas[2]["ed72_i_numfaltas"])) * 100) / $diasletivos;      
      $cfreq = (int)$cfreq;
      //$cfreq = number_format($cfreq, 0, ",", ",");
      //$cfreq = round($cfreq);

      $oPdf->Cell(20, $oConfigRelatorio->iAlturaLinha, $cfreq, 1, 1, 'C');
      
      //$oPdf->Cell($oConfigRelatorio->iColunaCodigo, $oConfigRelatorio->iAlturaLinha, $oAluno->iCodigoAluno, 1, 0, 'C');
    }//foreach do aluno

    /**
     * Imprime linhas em branco para fechar o total de aluno por página
     */
    if ($iAlunosImpressoPagina < $oConfigRelatorio->iAlunosPorPagina) {
      for ($i = $iAlunosImpressoPagina; $i < $oConfigRelatorio->iAlunosPorPagina; $i++) {
        imprimeLinhaEmBranco($oPdf, $oConfigRelatorio, $oTurmaEtapa);
      }
    }
    $ztextoobs = buscaobs($oTurmaEtapa->xcalendario, $oTurmaEtapa->xturma);
    
    $guarday = $oPdf->getY();
    $oPdf->setY(47);
    $oPdf->setX(183);
    $oPdf->Cell(110, $oConfigRelatorio->iAlturaLinha, "OBS.:", 1, 1);
    $oPdf->setX(183);
    $oPdf->Cell(110, $oConfigRelatorio->iAlturaLinha, substr($ztextoobs, 0, 90), 1, 1);

    $oPdf->setX(183);
    $oPdf->Cell(110, $oConfigRelatorio->iAlturaLinha, substr($ztextoobs, 90, 90), 1, 1);

    $oPdf->setX(183);
    $oPdf->Cell(110, $oConfigRelatorio->iAlturaLinha, substr($ztextoobs, 180, 90), 1, 1);

    $oPdf->setX(183);
    $oPdf->Cell(110, $oConfigRelatorio->iAlturaLinha, substr($ztextoobs, 270, 90), 1, 1);

    $oPdf->setX(183);
    $oPdf->Cell(110, $oConfigRelatorio->iAlturaLinha, substr($ztextoobs, 360, 90), 1, 1);

    for ($i=0; $i < 27; $i++) { 
      $oPdf->setX(183);
      $oPdf->Cell(110, $oConfigRelatorio->iAlturaLinha, "", 1, 1);
    }
    //$oPdf->MultiCell(110, $oConfigRelatorio->iAlturaLinha, $ztextoobs, 1);
    $oPdf->setY($guarday);




    if($assinaturaadicional == "sim"){
      montaQuadroLegendaAssinatura($oPdf, $oConfigRelatorio, $oTurmaEtapa->lUltimoPeriodo, $oTurmaEtapa, "sim", $nomea, $ativaa);
    }else{
      montaQuadroLegendaAssinatura($oPdf, $oConfigRelatorio, $oTurmaEtapa->lUltimoPeriodo, $oTurmaEtapa);
    }
    //montaQuadroLegendaAssinatura($oPdf, $oConfigRelatorio, $oTurmaEtapa->lUltimoPeriodo, $oTurmaEtapa);
    $lPrimeiraPagina = true;
  }
  unset($oTurmaEtapa);
}


/**
 * Renderiza quadro das legendas
 * @param FPDF $oPdf
 * @param stdClass $oConfigRelatorio
 */
function montaQuadroLegendaAssinatura(FPDF $oPdf, $oConfigRelatorio, $lUltimoPeriodo, $oTurmaEtapa, $aadicional = null, $anome = null, $aativ = null) {
  
  
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
  //$oPdf->Rect($oPdf->GetX(), $oPdf->GetY(), $iLarguraQuadro, 12);
  //$oPdf->Rect($oPdf->GetX(), $oPdf->GetY(), $iLarguraQuadro, 16);
  //$oPdf->Rect($oPdf->GetX(), $oPdf->GetY(), 135, 16);
  $oPdf->Rect($oPdf->GetX()+135, $oPdf->GetY(), 144, 24);

  $textoobs = buscaobs($oTurmaEtapa->xcalendario, $oTurmaEtapa->xturma);
  //var_dump($textoobs);
  //$oGet  = db_utils::postMemory($_GET);
  //$oJson = new Services_JSON();
  //$turmas = $oJson->decode(str_replace("\\", "", $oGet->oTurmas));
  //testa($oTurmaEtapa);
  //var_dump($_GET["oTurmas"]);
  //$listaturmas = $_GET["oTurmas"];  
  //$turmas = json_encode($listaturmas);
  //var_dump($turmas);  
  //$calendario = $_GET['calendario'];
  //var_dump($calendario);
  //die("Confere"); 

  /**
   * Assinatura
  */
  
  if($aadicional == "sim"){
    $oPdf->SetFont("arial", '', 6);
    $nomeadicional = $anome;
    $nomeatividade = trataNome($aativ);

    $oPdf->Cell(135, $oConfigRelatorio->iAlturaLinha, "", 0, 0, 'L');
    $oPdf->Cell(144, $oConfigRelatorio->iAlturaLinha, "Encerrado em: ____/____/______", "B", 1, 'L');
  
    $oPdf->Cell(135, $oConfigRelatorio->iAlturaLinha, "", 0, 0, 'L');
    $oPdf->Cell(144, $oConfigRelatorio->iAlturaLinha, "", 0, 1, 'L');

    $oPdf->Cell(135, $oConfigRelatorio->iAlturaLinha, "", 0, 0, 'L');
    $oPdf->Cell(144, $oConfigRelatorio->iAlturaLinha, "", 0, 1, 'L');

    $oPdf->Cell(135, $oConfigRelatorio->iAlturaLinha, "", 0, 0, 'L');
    $oPdf->Cell(2, $oConfigRelatorio->iAlturaLinha, "", 0, 0, 'C');
    $oPdf->Cell(45, $oConfigRelatorio->iAlturaLinha, "______________________________________", 0, 0, 'C');
    $oPdf->Cell(2, $oConfigRelatorio->iAlturaLinha, "", 0, 0, 'C');
    $oPdf->Cell(45, $oConfigRelatorio->iAlturaLinha, "______________________________________", 0, 0, 'C');
    $oPdf->Cell(2, $oConfigRelatorio->iAlturaLinha, "", 0, 0, 'C');
    $oPdf->Cell(45, $oConfigRelatorio->iAlturaLinha, "______________________________________", 0, 1, 'C');

    $oPdf->Cell(135, $oConfigRelatorio->iAlturaLinha, "", 0, 0, 'L');
    $oPdf->Cell(2, $oConfigRelatorio->iAlturaLinha, "", 0, 0, 'C');    
    $oPdf->Cell(45, $oConfigRelatorio->iAlturaLinha, $oTurmaEtapa->Regente, 0, 0, 'C');
    $oPdf->Cell(2, $oConfigRelatorio->iAlturaLinha, "", 0, 0, 'C');
    $oPdf->Cell(45, $oConfigRelatorio->iAlturaLinha, $nomeadicional, 0, 0, 'C');
    $oPdf->Cell(2, $oConfigRelatorio->iAlturaLinha, "", 0, 0, 'C');
    $oPdf->Cell(45, $oConfigRelatorio->iAlturaLinha, retornaDiretor(), 0, 1, 'C');

    $oPdf->Cell(135, $oConfigRelatorio->iAlturaLinha, "", 0, 0, 'L');
    $oPdf->Cell(2, $oConfigRelatorio->iAlturaLinha, "", 0, 0, 'C');
    $oPdf->Cell(45, $oConfigRelatorio->iAlturaLinha, "Regente", 0, 0, 'C');
    $oPdf->Cell(2, $oConfigRelatorio->iAlturaLinha, "", 0, 0, 'C');
    $oPdf->Cell(45, $oConfigRelatorio->iAlturaLinha, $nomeatividade, 0, 0, 'C');
    $oPdf->Cell(2, $oConfigRelatorio->iAlturaLinha, "", 0, 0, 'C');
    $oPdf->Cell(45, $oConfigRelatorio->iAlturaLinha, "Diretora Geral", 0, 1, 'C');
  }else{
    $oPdf->Cell(135, $oConfigRelatorio->iAlturaLinha, "", 0, 0, 'L');
    $oPdf->Cell(144, $oConfigRelatorio->iAlturaLinha, "Encerrado em: ____/____/______", "B", 1, 'L');
  
    $oPdf->Cell(135, $oConfigRelatorio->iAlturaLinha, "", 0, 0, 'L');
    $oPdf->Cell(144, $oConfigRelatorio->iAlturaLinha, "", 0, 1, 'L');

    $oPdf->Cell(135, $oConfigRelatorio->iAlturaLinha, "", 0, 0, 'L');
    $oPdf->Cell(144, $oConfigRelatorio->iAlturaLinha, "", 0, 1, 'L');

    $oPdf->Cell(135, $oConfigRelatorio->iAlturaLinha, "", 0, 0, 'L');
    $oPdf->Cell(70, $oConfigRelatorio->iAlturaLinha, "_______________________________________________", 0, 0, 'C');
    $oPdf->Cell(70, $oConfigRelatorio->iAlturaLinha, "_______________________________________________", 0, 1, 'C');

    $oPdf->Cell(135, $oConfigRelatorio->iAlturaLinha, "", 0, 0, 'L');
    $oPdf->Cell(70, $oConfigRelatorio->iAlturaLinha, $oTurmaEtapa->Regente, 0, 0, 'C');
    $oPdf->Cell(70, $oConfigRelatorio->iAlturaLinha, retornaDiretor(), 0, 1, 'C');
    $oPdf->Cell(135, $oConfigRelatorio->iAlturaLinha, "", 0, 0, 'L');

    $oPdf->Cell(135, $oConfigRelatorio->iAlturaLinha, "", 0, 0, 'L');
    $oPdf->Cell(70, $oConfigRelatorio->iAlturaLinha, "Regente", 0, 0, 'C');
    $oPdf->Cell(70, $oConfigRelatorio->iAlturaLinha, "Diretora Geral", 0, 1, 'C');


  }

  
 

  //PEGAR DADOS DA ATA DE RESULTADOS FINAIS
  /*if($adicional){

  }else{
    //DIRETOR
    // string(1674) "select 'DIRETOR' as funcao, case when ed20_i_tiposervidor = 1 then cgmrh.z01_nome else cgmcgm.z01_nome end as nome, ed83_c_descr||' n°: '||ed05_c_numero::varchar as descricao,'D' as tipo FROM escoladiretor INNER JOIN turno ON turno.ed15_i_codigo = escoladiretor.ed254_i_turno LEFT JOIN atolegal ON atolegal.ed05_i_codigo = escoladiretor.ed254_i_atolegal LEFT JOIN tipoato ON tipoato.ed83_i_codigo = atolegal.ed05_i_tipoato INNER JOIN rechumano ON rechumano.ed20_i_codigo = escoladiretor.ed254_i_rechumano LEFT JOIN rechumanopessoal ON rechumanopessoal.ed284_i_rechumano = rechumano.ed20_i_codigo LEFT JOIN rhpessoal ON rhpessoal.rh01_regist = rechumanopessoal.ed284_i_rhpessoal LEFT JOIN cgm AS cgmrh ON cgmrh.z01_numcgm = rhpessoal.rh01_numcgm LEFT JOIN rechumanoescola ON rechumanoescola.ed75_i_rechumano = rechumano.ed20_i_codigo LEFT JOIN rechumanoativ ON rechumanoativ.ed22_i_rechumanoescola = rechumanoescola.ed75_i_codigo LEFT JOIN atividaderh ON atividaderh.ed01_i_codigo = rechumanoativ.ed22_i_atividade LEFT JOIN rhpessoalmov ON rh02_anousu = 2018 AND rh02_mesusu = 02 AND rh02_regist = rh01_regist AND rh02_instit = 96 LEFT JOIN rhfuncao ON rhfuncao.rh37_funcao = rhpessoal.rh01_funcao AND rh37_instit = rh02_instit LEFT JOIN rechumanocgm ON rechumanocgm.ed285_i_rechumano = rechumano.ed20_i_codigo LEFT JOIN cgm AS cgmcgm ON cgmcgm.z01_numcgm = rechumanocgm.ed285_i_cgm where ed254_i_escola = 20100 AND ed254_c_tipo = 'A' AND ed01_i_funcaoadmin = 2 " Diretor
    $oPdf->SetFont("arial", 'b', 5);
    $oPdf->Cell(81, $oConfigRelatorio->iAlturaLinha, "Regente", 0, 0, 'C');
    //$oPdf->SetXY($iXInicial+200, $iYInicial+9);    
    //$oPdf->Cell(81, $oConfigRelatorio->iAlturaLinha, "Regente", 0, 0, 'C');
    //$oPdf->Line($iXInicial+200, $iYInicial+9, $iXInicial+$iLarguraQuadro, $iYInicial+9);
  }*/

  
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
function adicionaHeader(FPDF $oPdf, $oTurmaEtapa, $oConfigRelatorio, $iPagina) {

  $oPdf->AddPage();
  
  $oPdf->SetFont("arial", 'b', 12);
  $oPdf->Cell($oPdf->w, $oConfigRelatorio->iAlturaLinha, "RESUMO ANUAL", 0, 1, "C");
  $oPdf->ln();
  
  
  $oPdf->SetFont("arial", 'b', 7);
    
  $iEixoY = $oPdf->GetY();  
  $oPdf->Cell(105, $oConfigRelatorio->iAlturaLinha, '', 0, 0, "C");
  $oPdf->Cell(30, $oConfigRelatorio->iAlturaLinha, 'FALTAS por Trimestre', 1, 0, "C");
  $oPdf->ln();  
  
  $oPdf->Cell(5, $oConfigRelatorio->iAlturaLinha, 'Nº', 1, 0, 'C');
  $oPdf->Cell(100, $oConfigRelatorio->iAlturaLinha, 'Nome do Aluno', 1);
  $oPdf->Cell(10, $oConfigRelatorio->iAlturaLinha, '1º', 1, 0, "C");
  $oPdf->Cell(10, $oConfigRelatorio->iAlturaLinha, '2º', 1, 0, "C");
  $oPdf->Cell(10, $oConfigRelatorio->iAlturaLinha, '3º', 1, 0, "C");
  $oPdf->Cell(20, $oConfigRelatorio->iAlturaLinha, 'Total de Faltas', 1, 0, "C");
  $oPdf->Cell(20, $oConfigRelatorio->iAlturaLinha, 'Frequência %', 1, 0, "C");
  
  

  $oPdf->ln();
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

  
  $oPdf->Cell(5, $oConfigRelatorio->iAlturaLinha, '', 1, 0, 'C');
  $oPdf->Cell(100, $oConfigRelatorio->iAlturaLinha, '', 1);
  $oPdf->Cell(10, $oConfigRelatorio->iAlturaLinha, '', 1, 0, "C");
  $oPdf->Cell(10, $oConfigRelatorio->iAlturaLinha, '', 1, 0, "C");
  $oPdf->Cell(10, $oConfigRelatorio->iAlturaLinha, '', 1, 0, "C");
  $oPdf->Cell(20, $oConfigRelatorio->iAlturaLinha, '', 1, 0, "C");
  $oPdf->Cell(20, $oConfigRelatorio->iAlturaLinha, '', 1, 1, "C");
  

  $iLarguraCelulaNome = $oConfigRelatorio->iColunaNumero + $oTurmaEtapa->iColunaNome;
  /*
  if ($oConfigRelatorio->lClassificacaoAlunoTurma) {
    $iLarguraCelulaNome = $oTurmaEtapa->iColunaNome;
    $oPdf->Cell($oConfigRelatorio->iColunaNumero, $oConfigRelatorio->iAlturaLinha, '', 1);
  }
  $oPdf->Cell($iLarguraCelulaNome, $oConfigRelatorio->iAlturaLinha, '', 1);
  $oPdf->Cell($oConfigRelatorio->iColunaNumero, $oConfigRelatorio->iAlturaLinha, '',   1);
  $oPdf->Cell($oConfigRelatorio->iColunaCodigo, $oConfigRelatorio->iAlturaLinha, '', 1);
  */

  /*if (!$oTurmaEtapa->lUltimoPeriodo) {
    imprimeCelulasParecer($oPdf, $oTurmaEtapa->iLarguraCelulaParecer, $oConfigRelatorio);
  }*/
  //imprimeQuadroAvaliacaoVazio ($oPdf, $oTurmaEtapa, 0, $oConfigRelatorio, false);
  //$oPdf->Cell($oConfigRelatorio->iColunaNumero, $oConfigRelatorio->iAlturaLinha, '', 1, 1);
  
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
$oPdf->Output();
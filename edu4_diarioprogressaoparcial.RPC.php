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

require_once (modification("libs/db_stdlib.php"));
require_once (modification("libs/db_utils.php"));
require_once (modification("libs/db_app.utils.php"));
require_once (modification("libs/db_con"."ecta.php"));
require_once (modification("libs/db_sessoes.php"));
require_once (modification("dbforms/db_funcoes.php"));
require_once (modification("libs/JSON.php"));

function testa($var){
  echo "<pre>";
  print_r($var);
  echo "</pre>";
}

function busca2($xcod){
  $sql = pg_query("SELECT ed150_sequencial from progressaoparcialalunoturmaregencia inner join progressaoparcialalunomatricula on progressaoparcialalunomatricula.ed150_sequencial = progressaoparcialalunoturmaregencia.ed115_progressaoparcialalunomatricula where ed150_progressaoparcialaluno = {$xcod} order by ed115_sequencial desc limit 1");
  $resultado = pg_fetch_all($sql);
  return $resultado[0]["ed150_sequencial"];
}

function busca3($xcod2){
  $sql = pg_query("SELECT ed993_sequencial FROM plugins.diarioprogressao WHERE ed993_progressaoparcialalunoturmaregencia = {$xcod2}");
  $resultado = pg_fetch_all($sql);
  return $resultado[0]["ed993_sequencial"];
}

function buscaDiario($coddiario){
  $sql = pg_query("SELECT * FROM plugins.diarioprogressaoavaliacao WHERE ed999_diarioprogressao = {$coddiario}");
  $resultado = pg_fetch_all($sql);
  return $resultado;
}

function buscaDisciplinaDaProgressao($codprogressao){
  $sql = pg_query("SELECT ed114_disciplina, ed114_aluno FROM progressaoparcialaluno WHERE ed114_sequencial = {$codprogressao}");
  $resultado = pg_fetch_all($sql);
  return $resultado[0];
}

function buscaNotasAntigas($codalu, $coddip){
  $sql = pg_query("SELECT * FROM transferedependencia WHERE xcodaluno = {$codalu} AND xdisciplina = {$coddip}");
  $resultado = pg_fetch_all($sql);
  return $resultado;
}

define("MSG_EDU4_DIARIOPROGRESSAOPARCIAL", "educacao.escola.edu4_diarioprogressaoparcialRPC.");

$oParam             = JSON::create()->parse(str_replace("\\","",$_POST["json"]));
$oRetorno           = new stdClass();
$oRetorno->iStatus  = 1;
$oRetorno->sMessage = '';

try {

  db_inicio_transacao();

  switch ($oParam->exec) {

    case "buscarDadosTurma":
      //testa($oParam); die("TT");
      validaParametros($oParam);
      
      $aRegencias = buscaDisciplinas($oParam);
      $oTurma = TurmaRepository::getTurmaByCodigo($oParam->iTurma);
      $oEtapa = EtapaRepository::getEtapaByCodigo($oParam->iEtapa);
      $oDados = new stdClass();

      $oRetorno->sTurma  = $oTurma->getDescricao();
      $oRetorno->sEtapa  = $oEtapa->getNome();
      $oRetorno->sAluno  = '';

      $oRetorno->aTermosEncerramento  = DBEducacaoTermo::getTermoEncerramentoDoEnsinoToJSON($oTurma);
      $oRetorno->lAprovacaoAutomatica = false;

      $oRetorno->aElementos = array();
      
      
      foreach ($oParam->aProgressoes as $iProgressao) {

        $oProgressao = ProgressaoParcialAlunoRepository::getProgressaoParcialAlunoByCodigo($iProgressao);        
        $oRegencia   = identificaRegenciaIgualDisciplina($aRegencias, $oProgressao->getDisciplina());        
        //ERRO NA CLASSE ABAIXO
        $oDiario     = new DiarioProgressaoParcial($oProgressao, $oRegencia);
        
        

        $oRetorno->sAluno = $oProgressao->getAluno()->getNome();
        
        
        /**
         * busca os dados do procedimento de avaliação da regencia
         * @todo mesmo com a possibilidade de haver procedimentos diferentes nas disciplinas em progressão do aluno,
         *       o sistema so usa os dados da primeira regencia em progressão. Pode haver necessidade de melhorar isso
         *       futuramente e talvez montar duas grades
         */
        
        if ( empty($oDados->aElementos) ) {
          
          $oRegencia            = $oDiario->getRegencia();
          $oProcedimento        = $oRegencia->getProcedimentoAvaliacao();
          $oRetorno->aElementos = ProcedimentoAvaliacao::getElementosToJson( $oProcedimento->getElementos() );

          // verifica se turma esta configurada com aprovação automatica
          foreach ( $oRegencia->getTurma()->getEtapas() as $oEtapaTurma ) {

            if ($oEtapaTurma->getEtapa()->getCodigo() == $oRegencia->getEtapa()->getCodigo()
                 && $oEtapaTurma->temAprovacaoAutomatica() ) {
              $oRetorno->lAprovacaoAutomatica = true;
            }
          }
        }

        break;
      }
      break;

    case 'buscarAvaliacoesAluno':
      //testa($oParam); die("Confere");
      validaParametros($oParam);

      $aRegencias = buscaDisciplinas($oParam);
      $oRetorno->aAvaliacaoDisciplina = array();
      $oRetorno->sMascaraFormatacao   = '';

      foreach ($oParam->aProgressoes as $iProgressao) {

        $oProgressao = ProgressaoParcialAlunoRepository::getProgressaoParcialAlunoByCodigo($iProgressao);
        $oRegencia   = identificaRegenciaIgualDisciplina($aRegencias, $oProgressao->getDisciplina());

        $oDiario     = new DiarioProgressaoParcial($oProgressao, $oRegencia);
        $oRegencia   = $oDiario->getRegencia();
        $iAno        = $oRegencia->getTurma()->getCalendario()->getAnoExecucao();

        $oRetorno->sMascaraFormatacao = ArredondamentoNota::getMascara($iAno);

        $oDadosDisciplina                    = new stdClass();
        $oDadosDisciplina->iProgressao       = $iProgressao;
        $oDadosDisciplina->iRegencia         = $oRegencia->getCodigo();
        $oDadosDisciplina->sRegencia         = $oRegencia->getDisciplina()->getNomeDisciplina();
        $oDadosDisciplina->sRegenciaAbrev    = $oRegencia->getDisciplina()->getAbreviatura();
        $oDadosDisciplina->lEncerrado        = $oDiario->isEncerrado();
        $oDadosDisciplina->lEvadido          = $oDiario->isEvadido();
        $oDadosDisciplina->sFrequenciaGlobal = $oRegencia->getFrequenciaGlobal();

        $oDadosDisciplina->aAvaliacoes     = array();
        //TRANSFERIR NOTAS DEPENDÊNCIA aqui
        //=====================================================================================================================================
        //testa($oParam->aProgressoes);
        //die("Confere");
        //foreach ($oParam->aProgressoes as $iProgressao) {
          $validaad = buscaDisciplinaDaProgressao($iProgressao);
          $coddip = $validaad["ed114_disciplina"];
          $codalu = $validaad["ed114_aluno"];
          $notasantigas = buscaNotasAntigas($codalu, $coddip);
          if($notasantigas){

            $xcod2 = busca2(trim($iProgressao));
            $coddiario = busca3($xcod2);
            $gradenova = buscaDiario($coddiario);
            
            $indice = 0;
            foreach ($gradenova as $nova) {
              $cid = $nova["ed999_sequencial"];
              $xfaltas = ($notasantigas[$indice]["xed999_faltas"] == "") ? 0 : $notasantigas[$indice]["xed999_faltas"];
              $xnotas = ($notasantigas[$indice]["xed999_nota"] == "") ? 0 : $notasantigas[$indice]["xed999_nota"];
              $xmin = $notasantigas[$indice]["xed999_atingiominimo"];
              $xamp = $notasantigas[$indice]["xed999_amparado"];

              pg_query("UPDATE plugins.diarioprogressaoavaliacao SET ed999_faltas = {$xfaltas}, ed999_nota = {$xnotas}, ed999_atingiominimo = '{$xmin}', ed999_amparado = '{$xamp}' WHERE ed999_sequencial = {$cid}");
              //echo "UPDATE plugins.diarioprogressaoavaliacao SET ed999_faltas = {$xfaltas}, ed999_nota = {$xnotas}, ed999_atingiominimo = '{$xmin}', ed999_amparado = '{$xamp}' WHERE ed999_sequencial = {$cid}"; echo "<br>";
              
              $indice++;
            }
            pg_query("DELETE FROM transferedependencia WHERE xcodaluno = {$codalu} AND xdisciplina = {$coddip}");
          }          
        //}
        

        $lTemRecuperacao = false;
        foreach ($oDiario->getAvaliacoes() as $oAvaliacao) {

          $oElementoAvaliacao = $oAvaliacao->getElementoAvaliacao();
          $sFormaAvaliacao    = $oElementoAvaliacao->getFormaDeAvaliacao()->getTipo();

          // Se for conceito, identifica a ordem da avaliação
          $iOrdemConceito = '';
          if ($oAvaliacao->getValorAproveitamento()->hasOrdem()) {
            $iOrdemConceito = $oAvaliacao->getValorAproveitamento()->getOrdem();
          }

          $sTipoAvaliacao  = 'A';
          $iFaltasPeriodo  = $oAvaliacao->getNumeroFaltas();
          if ($oElementoAvaliacao->isResultado()) {

            $iFaltasPeriodo = $oDiario->getTotalFaltas();
            $sTipoAvaliacao = 'R';
          }

          $nNota = $oAvaliacao->getValorAproveitamento()->getAproveitamento();
          if ( $oElementoAvaliacao->isResultado() && $sFormaAvaliacao == 'NOTA' ) {
            $nNota = $oAvaliacao->getValorAproveitamento()->getAproveitamentoReal();
          }

          if ($oElementoAvaliacao->isResultado() || $sFormaAvaliacao == 'NOTA') {
            $nNota = ArredondamentoNota::formatar($nNota, $iAno);
          }

          $sFormaObtencao = '';
          if ( $oElementoAvaliacao instanceof ResultadoAvaliacao ) {
            $sFormaObtencao = $oElementoAvaliacao->getFormaDeObtencao();
          }

          $oDadosAvaliacao             = new stdClass();
          $oDadosAvaliacao->lEditado   = false; // usado no salvar para identificar os períodos que foram alterados
          $oDadosAvaliacao->lEncerrado = $oDiario->isEncerrado();
          $oDadosAvaliacao->iOrdem     = $oElementoAvaliacao->getOrdemSequencia(); // ordem do período de avaliacao
          $oDadosAvaliacao->iPeriodo   = $oElementoAvaliacao->getCodigo();
          $oDadosAvaliacao->lAmparado  = $oAvaliacao->isAmparado();
          $oDadosAvaliacao->lResultado = $oElementoAvaliacao->isResultado();

          $oDadosAvaliacao->nNota           = $nNota;
          $oDadosAvaliacao->iOrdemConceito  = $iOrdemConceito;
          $oDadosAvaliacao->lMinimoAtingido = $oAvaliacao->temAproveitamentoMinimo();
          $oDadosAvaliacao->iFaltas         = $iFaltasPeriodo;
          $oDadosAvaliacao->iAulasPeriodo   = 0;

          $oDadosAvaliacao->isRecuperacao = false; // se o periodo é uma recuperação
          /**
           * Para periodos que sejam recuperação, da direito ao aluno realizar o lançamento de a avalição
           */
          $oDadosAvaliacao->lTemDireitoRecuperacao = false;
          if ( $oElementoAvaliacao instanceof AvaliacaoPeriodica ){

            $oElementoDependente            = $oElementoAvaliacao->getElementoAvaliacaoVinculado();
            if ( !is_null($oElementoDependente) ) {

              $oDadosAvaliacao->isRecuperacao          = true;
              $oAvaliacaoAproveitamentoRecuperacao     = $oDiario->getAvaliacoesPorOrdem($oElementoDependente->getOrdemSequencia());
              $oDadosAvaliacao->lTemDireitoRecuperacao = $oAvaliacaoAproveitamentoRecuperacao->emRecuperacao();
              $lTemRecuperacao                         = $oDadosAvaliacao->lTemDireitoRecuperacao;
            }
          }

          // somente para resultadados... informa que o resultado esta em recuperação
          $oDadosAvaliacao->emRecuperacao         = $oAvaliacao->emRecuperacao();
          $oDadosAvaliacao->sFormaObtencao        = $sFormaObtencao;
          $oDadosAvaliacao->lNotaBloqueada        = false;
          $oDadosAvaliacao->lFaltaBloqueada       = false;
          $oDadosAvaliacao->iAulasPerido          = 0;
          $oDadosAvaliacao->sFormaAvaliacao       = $sFormaAvaliacao;
          $oDadosAvaliacao->mAproveitamentoMinino = $oElementoAvaliacao->getFormaDeAvaliacao()->getAproveitamentoMinino();
          $oDadosAvaliacao->nMenorValor           = '';
          $oDadosAvaliacao->nMaiorValor           = '';
          $oDadosAvaliacao->nVariacao             = '';
          $oDadosAvaliacao->aConceito             = array(); // quando avaliado por conceito

          if ($sFormaAvaliacao == 'NIVEL') {
            $oDadosAvaliacao->aConceito = $oElementoAvaliacao->getFormaDeAvaliacao()->getConceitos();
          }

          if ($sFormaAvaliacao == 'NOTA') {

            $oDadosAvaliacao->nMenorValor = $oElementoAvaliacao->getFormaDeAvaliacao()->getMenorValor();
            $oDadosAvaliacao->nMaiorValor = $oElementoAvaliacao->getFormaDeAvaliacao()->getMaiorValor();
            $oDadosAvaliacao->nVariacao   = $oElementoAvaliacao->getFormaDeAvaliacao()->getVariacao();
          }

          // carrega o total de aulas informadas para o período
          if ( !$oElementoAvaliacao->isResultado() ) {

            $iAulasPeriodo = $oRegencia->getTotalDeAulasNoPeriodo( $oElementoAvaliacao->getPeriodoAvaliacao() );
            if ( empty($iAulasPeriodo) ) {
              $iAulasPeriodo = 0;
            }
            $oDadosAvaliacao->lFaltaBloqueada = $iAulasPeriodo == 0;
            $oDadosAvaliacao->iAulasPeriodo   = $iAulasPeriodo;
          }

          if ($oRegencia->getFrequenciaGlobal() == 'A') {
            $oDadosAvaliacao->lFaltaBloqueada = true;
          }

          /**
           * Quando true não será possível lançar avaliação e ou faltas no período
           * @var boolean
           */
          $oDadosAvaliacao->lBloqueiaPeriodo = false;

          if ( $oDadosAvaliacao->isRecuperacao ) {

//            $oDadosAvaliacao->lBloqueiaPeriodo = true;
// demanda 16771 - Divaldo 08/01/2025
// com a variavel lBloqueiaPeriodo= true não estava sendo permitido o lançamento de notas de recuperação
// liberei o acesso ao lançamento de recuperação
// testado por Suellem em 09/01/2025

            $oDadosAvaliacao->lBloqueiaPeriodo = false;
            $oDadosAvaliacao->lFaltaBloqueada  = false;
          }

          if ( $oDadosAvaliacao->lAmparado ||  $oDadosAvaliacao->lResultado ) {

            $oDadosAvaliacao->lBloqueiaPeriodo = true;
            $oDadosAvaliacao->lFaltaBloqueada  = true;
          }

          if ( !$oDadosAvaliacao->lResultado && $oDadosAvaliacao->isRecuperacao ) {

            if ($oDadosAvaliacao->lTemDireitoRecuperacao || $oDadosAvaliacao->nNota !== '') {
              $oDadosAvaliacao->lBloqueiaPeriodo = false;
            }
          }

          if ($oDadosDisciplina->lEvadido || $oDadosDisciplina->lEncerrado) {

            $oDadosAvaliacao->lBloqueiaPeriodo = true;
            $oDadosAvaliacao->lFaltaBloqueada  = true;
          }

          $oDadosDisciplina->aAvaliacoes[] = $oDadosAvaliacao;
        }


        $oResultadoFinalRegencia = $oDiario->getResultadoFinal();

        $oDadosDisciplina->oResultadoFinal                        = new stdClass();
        $oDadosDisciplina->oResultadoFinal->nValor                = '';
        $oDadosDisciplina->oResultadoFinal->sResultadoFinal       = '';
        $oDadosDisciplina->oResultadoFinal->lPossuiResultadoFinal = false;
        $oDadosDisciplina->oResultadoFinal->iAprovadoPeloConselho = 0;
        $oDadosDisciplina->oResultadoFinal->lAprovadoAvaliacao    = $oResultadoFinalRegencia->getResultadoAprovacao() == 'A';
        $oDadosDisciplina->oResultadoFinal->lAprovadoFrequencia   = $oResultadoFinalRegencia->getResultadoFrequencia() == 'A';

        if ( !empty($oResultadoFinalRegencia) ) {

          $mAproveitamentoFinal = ArredondamentoNota::formatar($oResultadoFinalRegencia->getValorAprovacao(), $iAno);

          $oDadosDisciplina->oResultadoFinal->nValor                = $mAproveitamentoFinal;
          $oDadosDisciplina->oResultadoFinal->sResultadoFinal       = urlencode($oResultadoFinalRegencia->getResultadoFinal());
          $oDadosDisciplina->oResultadoFinal->lPossuiResultadoFinal = true;

          /**
           * Se aluno aprovado pelo conselho
           */
          $oAprovadoConselho = $oResultadoFinalRegencia->getFormaAprovacaoConselho();
          if ($oAprovadoConselho != null) {

            $oDadosDisciplina->oResultadoFinal->iAprovadoPeloConselho = $oAprovadoConselho->getFormaAprovacao();
            $oDadosDisciplina->oResultadoFinal->iAlterarNotaFinal     = $oAprovadoConselho->getAlterarNotaFinal();
            $oDadosDisciplina->oResultadoFinal->sAvaliacaoConselho    = $oAprovadoConselho->getAvaliacaoConselho();

            /**
             * Reclassificação por baixa frequência não aprova o aluno, pois o mesmo pode estar com o aproveitamento
             * abaixo da média, e a reclassificação aprova apenas no quesito frequência
             */
            if( $oAprovadoConselho->getFormaAprovacao() != AprovacaoConselho::RECLASSIFICACAO_BAIXA_FREQUENCIA ) {
              $oDadosDisciplina->oResultadoFinal->sResultadoFinal = 'A';
            }
          }
        }


        $oAmparo = $oDiario->getAmparo();
        if ( $oAmparo->getCodigo() != '' && $oAmparo->isTotal() ) {
          $oDadosDisciplina->oResultadoFinal->nValor = 'AMP';
        }

        if ($lTemRecuperacao) {
          $oDadosDisciplina->oResultadoFinal->sResultadoFinal = 'REC';
        }
        if ($oDadosDisciplina->lEvadido) {
          $oDadosDisciplina->oResultadoFinal->sResultadoFinal = 'EVA';
        }

        $oRetorno->aAvaliacaoDisciplina[] = $oDadosDisciplina;

        //TRANSFERIR NOTAS DEPENDÊNCIA aqui
        /*
        foreach ($oParam->aProgressoes as $iProgressao) {
          $validaad = buscaDisciplinaDaProgressao($iProgressao);
          $coddip = $validaad["ed114_disciplina"];
          $codalu = $validaad["ed114_aluno"];
          $notasantigas = buscaNotasAntigas($codalu, $coddip);
          if($notasantigas){

            $xcod2 = busca2(trim($iProgressao));
            $coddiario = busca3($xcod2);
            $gradenova = buscaDiario($coddiario);
            
            $indice = 0;
            foreach ($gradenova as $nova) {
              $cid = $nova["ed999_sequencial"];
              $xfaltas = ($notasantigas[$indice]["xed999_faltas"] == "") ? 0 : $notasantigas[$indice]["xed999_faltas"];
              $xnotas = ($notasantigas[$indice]["xed999_nota"] == "") ? 0 : $notasantigas[$indice]["xed999_nota"];
              $xmin = $notasantigas[$indice]["xed999_atingiominimo"];
              $xamp = $notasantigas[$indice]["xed999_amparado"];

              //pg_query("UPDATE plugins.diarioprogressaoavaliacao SET ed999_faltas = {$xfaltas}, ed999_nota = {$xnotas}, ed999_atingiominimo = '{$xmin}', ed999_amparado = '{$xamp}' WHERE ed999_sequencial = {$cid}");
              //echo "UPDATE plugins.diarioprogressaoavaliacao SET ed999_faltas = {$xfaltas}, ed999_nota = {$xnotas}, ed999_atingiominimo = '{$xmin}', ed999_amparado = '{$xamp}' WHERE ed999_sequencial = {$cid}"; echo "<br>";
              
              $indice++;
            }
          }          
        }
        */
        

        
      }
      break;
    case 'salvarAvaliacoes':

      if ( empty($oParam->aProgressoes) ) {
        throw new ParameterException( _M( MSG_EDU4_DIARIOPROGRESSAOPARCIAL . 'informe_progressao') );
      }

      foreach ($oParam->aProgressoes as $oDadosProgressao) {

        $oRegencia   = RegenciaRepository::getRegenciaByCodigo($oDadosProgressao->iRegencia);
        $oProgressao = ProgressaoParcialAlunoRepository::getProgressaoParcialAlunoByCodigo($oDadosProgressao->iProgressao);
        $oDiario     = new DiarioProgressaoParcial($oProgressao, $oRegencia);
        $oTurma      = $oDiario->getRegencia()->getTurma();

        $iEscola     = $oTurma->getEscola()->getCodigo();
        $iAno        = $oTurma->getCalendario()->getAnoExecucao();

        $oDiario->setEvadido( $oDadosProgressao->lEvadido );
        foreach ($oDadosProgressao->aAvaliacoes as $oDadosAvaliacao) {

          // Ignora períodos de avaliação que não foram editados
          if ( !$oDadosAvaliacao->lEditado ) {
            continue;
          }

          $oAvaliavaoAproveitamento = $oDiario->getAproveitamentosDoPeriodo($oDadosAvaliacao->iPeriodo);
          $oValorAproveitamento     = $oAvaliavaoAproveitamento->getValorAproveitamento();

          $oValorAproveitamento->setAproveitamento($oDadosAvaliacao->nNota);
          $oValorAproveitamento->setAproveitamentoReal($oDadosAvaliacao->nNota);

          if ($oValorAproveitamento->hasOrdem()) {
            $oValorAproveitamento->setOrdem($oDadosAvaliacao->iOrdemConceito);
          }

          $oElementoAvaliacao   = $oAvaliavaoAproveitamento->getElementoAvaliacao();

          $nValorMinimo         = $oElementoAvaliacao->getAproveitamentoMinimo();
          $nValorAproveitamento = $oValorAproveitamento->getAproveitamento();

          /**
           * Quando possuimos avaliacao por conceito, devemos verificar o nivel do mesmo;
           */
          if ($oElementoAvaliacao->getFormaDeAvaliacao()->getTipo() == 'NIVEL') {

            $oConceitoMinimo      = $oElementoAvaliacao->getFormaDeAvaliacao()->getConceitoMinimo();
            $nValorMinimo         = $oConceitoMinimo->iOrdem;
            $nValorAproveitamento = $oValorAproveitamento->getOrdem();
          }

          $oAvaliavaoAproveitamento->setNumeroFaltas($oDadosAvaliacao->iFaltas);

          $oAvaliavaoAproveitamento->setAproveitamentoMinimo(true);
          if ($nValorAproveitamento < $nValorMinimo) {
            $oAvaliavaoAproveitamento->setAproveitamentoMinimo(false);
          }

          $oAvaliavaoAproveitamento->setValorAproveitamento($oValorAproveitamento);


          $oUltimoResultado = null;
          $aResultados      = $oDiario->getResultados();
          foreach ($aResultados as $oResultado) {

            $oUltimoResultado = $oUltimoResultado;
            if ($oResultado->getElementoAvaliacao()->getOrdemSequencia() < $oElementoAvaliacao->getOrdemSequencia()) {
              continue;
            }

            /**
             * Calcula o valor do Resultado final o Retorna uma instancia de ValorResultado
             */
            $oValorResultado = $oResultado->getElementoAvaliacao()->getResultado( $oDiario->getAvaliacoes(), false, $iAno );
            if ( is_null( $oValorResultado->getAproveitamentoReal() ) ) {
              $oValorResultado->setAproveitamentoReal($oValorResultado->getAproveitamento());
            }
            /* calula a nota real */
            $mNotaReal = DiarioProgressaoParcial::calcularResultadoReal( $oResultado->getElementoAvaliacao(), $iEscola, $oDiario->getAvaliacoes(), $iAno);
            if ( !is_null($mNotaReal) ) {
              $oValorResultado->setAproveitamentoReal($mNotaReal);
            }

            $oResultado->setValorAproveitamento($oValorResultado);

            $nResultadoVerificar = $oValorResultado->getAproveitamento();
            $oFormaAvaliacao     = $oResultado->getElementoAvaliacao()->getFormaDeAvaliacao();
            if ($oFormaAvaliacao->getTipo() == 'NIVEL') {

              $oConceitoMinimo     = $oFormaAvaliacao->getConceitoMinimo();
              $nValorMinimo        = $oConceitoMinimo->iOrdem;
              $nResultadoVerificar = $oValorAproveitamento->getOrdem();
            }

            $oResultado->setAproveitamentoMinimo(true);
            if ($nResultadoVerificar < $nValorMinimo) {
              $oResultado->setAproveitamentoMinimo(false);
            }

            $oPeriodoDependente = $oDiario->getAvaliacaoDependentesDoPeriodo($oResultado->getElementoAvaliacao());
            if ($oPeriodoDependente) {

              if ( $oResultado->temAproveitamentoMinimo()) {
                $oPeriodoDependente->getValorAproveitamento()->setAproveitamento('');
              }
            }
          }
        }

        $oDiario->salvar();
      }

      $oRetorno->sMessage = _M( MSG_EDU4_DIARIOPROGRESSAOPARCIAL . 'avaliacoes_salva');
      break;
  }

  db_fim_transacao(false);


} catch (Exception $eErro){

  db_fim_transacao(true);
  $oRetorno->iStatus  = 2;
  $oRetorno->sMessage = $eErro->getMessage();
}
$oRetorno->erro = $oRetorno->iStatus == 2;
echo JSON::create()->stringify($oRetorno);

function validaParametros($oParam) {

  if ( empty($oParam->iTurma) ) {
    throw new ParameterException( _M( MSG_EDU4_DIARIOPROGRESSAOPARCIAL . 'informe_turma') );
  }

  if ( empty($oParam->iEtapa) ) {
    throw new ParameterException( _M( MSG_EDU4_DIARIOPROGRESSAOPARCIAL . 'informe_etapa') );
  }

  if ( empty($oParam->aProgressoes) ) {
    throw new ParameterException( _M( MSG_EDU4_DIARIOPROGRESSAOPARCIAL . 'informe_progressao') );
  }
}

function buscaDisciplinas($oParam) {
 
  $oTurma = TurmaRepository::getTurmaByCodigo($oParam->iTurma);
  $oEtapa = EtapaRepository::getEtapaByCodigo($oParam->iEtapa); 

  return $oTurma->getDisciplinasPorEtapa($oEtapa);
}

function identificaRegenciaIgualDisciplina($aRegencias, Disciplina $oDisciplina) {

  $oRegenciaMatricula = null;
  foreach ($aRegencias as $oRegencia) {
    
    if ( $oRegencia->getDisciplina()->getCodigoDisciplina() != $oDisciplina->getCodigoDisciplina()) {
      continue;
    }
    $oRegenciaMatricula = $oRegencia;
  } 
  
  return $oRegenciaMatricula;
}
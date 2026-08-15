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

require_once("fpdf151/pdfwebseller.php");
require_once("std/DBDate.php");
require_once("std/db_stdClass.php");
require_once("dbforms/db_funcoes.php");
require_once("libs/db_sql.php");
require_once("libs/db_stdlib.php");
require_once("libs/db_conecta.php");
require_once("libs/db_sessoes.php");
require_once("libs/db_utils.php");
require_once("libs/JSON.php");
require_once("libs/db_usuariosonline.php");
require_once("libs/db_app.utils.php");
require_once("libs/exceptions/BusinessException.php");
require_once("libs/exceptions/ParameterException.php");
require_once("libs/exceptions/DBException.php");
require_once("model/educacao/DocenteRepository.model.php");
require_once("model/educacao/Docente.model.php");
require_once(modification("libs/db_stdlib.php"));
require_once(modification("libs/db_stdlibwebseller.php"));
require_once(modification("libs/db_app.utils.php"));
require_once(modification("libs/JSON.php"));
require_once(modification("std/db_stdClass.php"));
require_once(modification("std/DBDate.php"));
require_once(modification("dbforms/db_funcoes.php"));
require_once(modification("libs/db_conecta.php"));
require_once(modification("libs/db_utils.php"));
require_once(modification("libs/db_sessoes.php"));
require_once(modification("libs/db_usuariosonline.php"));

/**
 * Calcula a média anual usando objetos do sistema (mesmo padrão do db_stdlibwebseller.php)
 *
 * @author Uemerson Santana
 * @date 05/05/2025
 * Demanda: 17356
 *
 * @param Matricula $oMatricula - Objeto matrícula
 * @param Regencia $oRegencia - Objeto regência da disciplina
 * @param int $iAno - Ano para arredondamento
 * @return array ['media' => float, 'media_formatada' => string, 'tem_nota' => bool]
 */
function calcularMediaAnualSistema($oMatricula, $oRegencia, $iAno) {

    try {
        // Usa os mesmos objetos que já estão sendo utilizados no código
        $oDiarioDeClasse = $oMatricula->getDiarioDeClasse();
        $oDisciplinasDiario = $oDiarioDeClasse->getDisciplinasPorRegencia($oRegencia);

        // Pega o elemento de resultado final (mesmo do db_stdlibwebseller.php)
        $oElementoResultadoFinal = $oDisciplinasDiario->getElementoResultadoFinal();

        if ($oElementoResultadoFinal == null) {
            return [
                'media' => 0,
                'media_formatada' => '',
                'tem_nota' => false
            ];
        }

        // Usa a mesma função que o db_stdlibwebseller.php usa para calcular nota parcial
        $notaParcial = $oDisciplinasDiario->getNotaParcial($oElementoResultadoFinal->getElementoAvaliacao());

        // Verifica se tem notas lançadas
        $temNotas = false;
        foreach ($oDisciplinasDiario->getAvaliacoes() as $oAvaliacao) {
            if (!$oAvaliacao->getElementoAvaliacao()->isResultado() &&
                $oAvaliacao->getValorAproveitamento()->getAproveitamento() !== '' &&
                !$oAvaliacao->isAmparado()) {
                $temNotas = true;
                break;
            }
        }

        // Se não tem nota parcial calculada, retorna vazio
        if ($notaParcial === null || $notaParcial === '' || $notaParcial === 0) {
            return [
                'media' => 0,
                'media_formatada' => '',
                'tem_nota' => $temNotas
            ];
        }

        // Formata usando ArredondamentoNota (mesmo do sistema)
        $notaFormatada = ArredondamentoNota::formatar($notaParcial, $iAno);

        // Converte para formato brasileiro (vírgula)
        $notaFormatadaBR = str_replace('.', ',', $notaFormatada);

        return [
            'media' => floatval($notaParcial),
            'media_formatada' => $notaFormatadaBR,
            'tem_nota' => $temNotas
        ];

    } catch (Exception $e) {
        // Em caso de erro, retorna vazio
        return [
            'media' => 0,
            'media_formatada' => '',
            'tem_nota' => false
        ];
    }
}


/**
 * Calcula a descrição final de um aluno com base nos dados fornecidos.
 *
 * @author Uemerson Santana
 * @date 05/05/2025
 * Demanda: 17356
 * @param array $aRetorno Dados do aluno com situação e disciplina
 * @return string Descrição final formatada
 */
function calcularDescricaoFinal($aRetorno)
{
    $sDescricao = '';

    // Compatibilidade manual para os campos
    $sSituacaoAluno         = isset($aRetorno['sSituacaoAluno']) ? $aRetorno['sSituacaoAluno'] : '';
    $sSituacaoAbreviada     = isset($aRetorno['sSituacaoAbreviada']) ? $aRetorno['sSituacaoAbreviada'] : '';
    $oDisciplina            = isset($aRetorno['oDisciplina']) ? $aRetorno['oDisciplina'] : array();
    $oResultadoFinal        = isset($oDisciplina['oResultadoFinal']) ? $oDisciplina['oResultadoFinal'] : array();
    $sFrequenciaGlobal      = isset($oDisciplina['sFrequenciaGlobal']) ? $oDisciplina['sFrequenciaGlobal'] : '';
    $lCaractReprobat        = isset($oDisciplina['lCaracterReprobatorio']) ? $oDisciplina['lCaracterReprobatorio'] : false;
    $lProgressaoParcial     = isset($oDisciplina['lProgressaoParcial']) ? $oDisciplina['lProgressaoParcial'] : false;
    $lProgressaoAnterior    = isset($aRetorno['lProgressaoParcialAnterior']) ? $aRetorno['lProgressaoParcialAnterior'] : false;

    // Aluno não está matriculado/rematriculado
    if ($sSituacaoAluno !== 'MATRICULADO' && $sSituacaoAluno !== 'REMATRICULADO') {
        $sDescricao = $sSituacaoAbreviada;
        $oResultadoFinal['nValor'] = '-';
        $oResultadoFinal['sResultadoFinal'] = '';
    }

    // Resultado final REC sempre sobrescreve
    if (isset($oResultadoFinal['sResultadoFinal']) && $oResultadoFinal['sResultadoFinal'] === 'REC') {
        $sDescricao = 'REC';
    }

    // Caso esteja aprovado pelo conselho ou nota vazia e frequência ? F
    if (
        (!isset($oResultadoFinal['iAprovadoPeloConselho']) || $oResultadoFinal['iAprovadoPeloConselho'] == 0) &&
        (isset($oResultadoFinal['nValor']) && $oResultadoFinal['nValor'] === '' && $sFrequenciaGlobal !== 'F') &&
        $lCaractReprobat
    ) {
        $sDescricao = '';
    } else if (empty(trim($sDescricao))) {
        $sDescricao = $oResultadoFinal['sResultadoFinal'];
    }

    // Situação especial de progressão parcial atual, sem progressão anterior (deve vir por último)
    if ($lProgressaoParcial && !$lProgressaoAnterior) {
        $sDescricao = 'AP/DP';
    }

    $sDescricao = pegarNomeDescricao($sDescricao);

    return $sDescricao;
}

/**
 * Retorna o nome completo da sigla de situação do aluno.
 *
 * @author Uemerson Santana
 * @date 05/05/2025
 * Demanda: 17356
 * @param string $sigla Sigla da situação
 * @return string Nome completo da situação
 */
function pegarNomeDescricao($sigla)
{
    $aSituacoes = array(
        'MT'  => 'MATRÍCULA TRANCADA',
        'IN'  => 'MATRÍCULA INDEFERIDA',
        'MI'  => 'MATRÍCULA INDEVIDA',
        'TR'  => 'TRANSFERÊNCIA REDE',
        'TF'  => 'TRANSFERÊNCIA FORA',
        'TT'  => 'TROCA DE TURMA',
        'TM'  => 'TROCA DE MODALIDADE',
        'C'   => 'CANCELADO',
        'E'   => 'EVADIDO',
        'F'   => 'FALECIDO',
        'A'   => 'APROVADO',
        'R'   => 'REPROVADO',
        // 'REC' => 'RECUPERAÇÃO'
        // 'AP/DP' => 'APROVAÇÃO COM DEPENDÊNCIA'
    );

    return isset($aSituacoes[$sigla]) ? $aSituacoes[$sigla] : $sigla;
}


/**
 * Gera os dados de um aluno para exportação em JSON, a partir da matrícula e da regência.
 *
 * @author Uemerson Santana
 * @date 05/05/2025
 * Demanda: 17356
 *
 * @param Matricula $oMatricula                             Instância de Matrícula do aluno.
 * @param Regencia  $oRegencia                              Instância de Regência referente à disciplina.
 * @param int       $iAno                                   Ano corrente (para arredondamento de notas).
 * @param bool      $lTurmaUtilizaProporcionalidade         Indica se a turma utiliza proporcionalidade.
 * @param bool      $lBloqueiaAlteracaoAvaliacao            Indica se bloqueia alteração de avaliação.
 * @param bool      $lProfessorLogado                       Indica se o professor está logado.
 * @param object    $oParam                                  Parâmetros adicionais (ex: iRegencia, iMaiorPermissao).
 *
 * @return array                                           Array associativo [$matriculaCodigo => dadosAluno].
 */
function gerarDadosResultadoFinal(
    /**
     *
     * Legenda de saida:
     *
     */

    //  {
    //     "iSequencia": "14",
    //     "sNomeAluno": "LORRAYNE QUEIROZ VIEIRA",
    //     "iCodigoAluno": "137073",
    //     "iMatricula": "61414",
    //     "sSituacaoReal": "MATRICULADO",
    //     "sSituacaoAluno": "MATRICULADO",
    //     "sSituacaoAbreviada": "MATR",
    //     "dtMatricula": "05/02/2024",
    //     "lAvaliadoParecer": false,
    //     "dtSaida": "",
    //     "lTemAbonoFalta": false,
    //     "lTemObservacao": false,
    //     "lTemParecer": false,
    //     "oDisciplina": {
    //       "iCodigoRegencia": "13727",
    //       "sDescricao": "LINGUA PORTUGUESA",
    //       "sFrequenciaGlobal": "I",
    //       "aAproveitamentos": "OMITIDO_POR_TAMANHO",
    //       "lObrigatoria": true,
    //       "lCaracterReprobatorio": true,
    //       "lProgressaoParcial": false,
    //       "oResultadoFinal": {
    //         "nValor": "6.2",
    //         "sResultadoFinal": "A",
    //         "lPossuiResultadoFinal": true,
    //         "iAprovadoPeloConselho": 0
    //       }
    //     },
    //     "lProgressaoParcialNaEtapa": false,
    //     "lProgressaoParcialAnterior": false,
    //     "aDisciplinasProgressao": []
    //   }

    Matricula $oMatricula,
    Regencia $oRegencia,
    $iAno,
    $lTurmaUtilizaProporcionalidade,
    $lBloqueiaAlteracaoAvaliacao = false,
    $lProfessorLogado = false,
    $oParam = null
) {
    $oDadosAluno2 = new stdClass();
    $oDiario2     =   $oMatricula->getDiarioDeClasse();

    $oDadosAluno2->iSequencia     = $oMatricula->getNumeroOrdemAluno();
    $oDadosAluno2->sNomeAluno     = $oMatricula->getAluno()->getNome();
    $oDadosAluno2->iCodigoAluno   = $oMatricula->getAluno()->getCodigoAluno();
    $oDadosAluno2->iMatricula     = $oMatricula->getCodigo();

    $sSituacaoReal = Situacao($oMatricula->getSituacao(), $oMatricula->getCodigo());

    $oDadosAluno2->sSituacaoReal      = $sSituacaoReal;
    $oDadosAluno2->sSituacaoAluno     = $oMatricula->getSituacao();
    $oDadosAluno2->sSituacaoAbreviada =  $oMatricula->getAbreviaturaSituacao();
    $oDadosAluno2->dtMatricula        = $oMatricula->getDataMatricula()->convertTo(DBDate::DATA_PTBR);
    $oDadosAluno2->lAvaliadoParecer   = $oMatricula->isAvaliadoPorParecer();
    $oDadosAluno2->dtSaida            = "";
    if ($oMatricula->getDataEncerramento() != "") {
        $oDadosAluno2->dtSaida = $oMatricula->getDataEncerramento()->convertTo(DBDate::DATA_PTBR);
    }

    $oDadosAluno2->lTemAbonoFalta = false;
    $oDadosAluno2->lTemObservacao = false;
    $oDadosAluno2->lTemParecer    = false;
    $lValidouAbonoFalta          = false;
    $lValidouObservacao          = false;
    $lValidouParecer             = false;

    $oDadosAluno2->oDisciplina = new stdClass();

    /**
     * Buscamos as avaliações da regencia selecionada
     */
    // $oRegencia = RegenciaRepository::getRegenciaByCodigo($oParam->iRegencia);

    $oDadosAluno2->lProgressaoParcialNaEtapa  = false;
    $oDadosAluno2->lProgressaoParcialAnterior = false;
    $oDadosAluno2->aDisciplinasProgressao     = array();

    $oDadoRegencia                        = new stdClass();
    $oDadoRegencia->iCodigoRegencia       = $oRegencia->getCodigo();

    $oDadoRegencia->sDescricao            = $oRegencia->getDisciplina()->getNomeDisciplina();
    $oDadoRegencia->sFrequenciaGlobal     = $oRegencia->getFrequenciaGlobal();
    $oDadoRegencia->aAproveitamentos      = array();
    $oDadoRegencia->lObrigatoria          = $oRegencia->isObrigatoria();
    $oDadoRegencia->lCaracterReprobatorio = $oRegencia->possuiCaracterReprobatorio();
    $oDadoRegencia->lProgressaoParcial    = false;

    if (count($oMatricula->getAluno()->getProgressaoParcial()) > 0) {

        foreach ($oMatricula->getAluno()->getProgressaoParcial() as $oProgressaoParcialAluno) {

            if (
                $oProgressaoParcialAluno->getEtapa()->getOrdem() < $oRegencia->getEtapa()->getOrdem()
                && $oProgressaoParcialAluno->isAtiva()
            ) {

                $oDadosAluno2->lProgressaoParcialAnterior = true;
                $sDisciplinaProgressao = $oProgressaoParcialAluno->getDisciplina()->getNomeDisciplina();
                $sEtapaProgressao      = $oProgressaoParcialAluno->getEtapa()->getNome();

                $oDadosAluno2->aDisciplinasProgressao[]   = "{$sDisciplinaProgressao} ({$sEtapaProgressao})";
            }

            if (
                $oProgressaoParcialAluno->getEtapa()->getOrdem() == $oRegencia->getEtapa()->getOrdem()
                && $oProgressaoParcialAluno->getAno() == $oRegencia->getTurma()->getCalendario()->getAnoExecucao()
                && $oProgressaoParcialAluno->getDisciplina()->getCodigoDisciplina() == $oRegencia->getDisciplina()->getCodigoDisciplina()
            ) {
                $oDadoRegencia->lProgressaoParcial = true;
            }
        }
    }

    // $oDadosAproveitamento é o DiarioAvaliacaoDisciplina
    $oDadosAproveitamento                 = $oMatricula->getDiarioDeClasse()->getDisciplinasPorRegencia($oRegencia);
    $aOrdemPeriodoAplicaProporcionalidade = $oDadosAproveitamento->getOrdemPeriodosAplicaProporcionalidade();

    /**
     * Verifica se foi configurado um resultado como
     */
    foreach ($aOrdemPeriodoAplicaProporcionalidade as $iOrdemPeriodo) {

        $oElemento = $oDadosAproveitamento->getPeriodoAvaliacaoPorOrdemSequencial($iOrdemPeriodo);
        if ($oElemento instanceof ResultadoAvaliacao) {
            $aOrdemPeriodoAplicaProporcionalidade = buscaOrdemElementos($oElemento, $aOrdemPeriodoAplicaProporcionalidade);
        }
    }

    $aAvaliacoesDependentesReprovadas = array();
    $lTemRecuperacao                  = false;
    $aElementosEmRecuperacao          = array();
    $aElementosDeRecuperacao          = array();

    foreach ($oDadosAproveitamento->getAvaliacoes() as $oAvaliacao) {

        $sFormaAvaliacao = $oAvaliacao->getElementoAvaliacao()->getFormaDeAvaliacao()->getTipo();

        /**
         * Esta validação é para quando um aluno especifico é avaliado por parecer
         */
        if ($oMatricula->isAvaliadoPorParecer()) {
            $sFormaAvaliacao = 'PARECER';
        }

        $nNota = $oAvaliacao->getValorAproveitamento()->getAproveitamento();

        if ($oAvaliacao->getElementoAvaliacao()->isResultado() && $sFormaAvaliacao == 'NOTA') {
            $nNota = $oAvaliacao->getValorAproveitamento()->getAproveitamentoReal();
        }

        if ($oAvaliacao->getElementoAvaliacao()->isResultado() && $sFormaAvaliacao == 'PARECER') {
            if (!empty($oAvaliacao->getParecer())) {
                $nNota = $oAvaliacao->getParecer();
            }
        }

        $sTipoAvaliacao  = 'A';
        $iOrdem          = '';
        $lFaltasAbonadas = false;
        $iFaltasAbonadas = 0;
        $iFaltasPeriodo  = $oAvaliacao->getNumeroFaltas();

        $oElementoAvaliacao = $oAvaliacao->getElementoAvaliacao();
        if ($oAvaliacao->getValorAproveitamento()->hasOrdem()) {
            $iOrdem = $oAvaliacao->getValorAproveitamento()->getOrdem();
        }

        if ($oAvaliacao->getElementoAvaliacao()->isResultado()) {

            $iFaltasPeriodo = $oDadosAproveitamento->getTotalFaltas();
            $sTipoAvaliacao = 'R';
        }

        if (!$oElementoAvaliacao->isResultado()) {

            $iFaltasAbonadas             = $oAvaliacao->getFaltasAbonadas();
            $lFaltasAbonadas             = $iFaltasAbonadas > 0 ? true : false;

            if ($lFaltasAbonadas && !$lValidouAbonoFalta) {

                $lValidouAbonoFalta          = true;
                $oDadosAluno2->lTemAbonoFalta = $lFaltasAbonadas;
            }

            if (!$lValidouObservacao && $oAvaliacao->getObservacao() != '') {

                $lValidouObservacao          = true;
                $oDadosAluno2->lTemObservacao = true;
            }

            if (!$lValidouParecer) {

                if ($sFormaAvaliacao != 'PARECER' && $oAvaliacao->hasParecer()) {

                    $lValidouParecer          = true;
                    $oDadosAluno2->lTemParecer = true;
                } else if (
                    $sFormaAvaliacao == 'PARECER' &&
                    ($oAvaliacao->getParecerPadronizado() != '' || $oAvaliacao->getValorAproveitamento()->getAproveitamento() != '')
                ) {

                    $lValidouParecer          = true;
                    $oDadosAluno2->lTemParecer = true;
                }
            }
        }

        if (
            $oElementoAvaliacao->isResultado() && $sFormaAvaliacao == 'PARECER'
            && ($oAvaliacao->getParecerPadronizado() != ''
                || $oAvaliacao->getValorAproveitamento()->getAproveitamento() != '')
        ) {

            $lValidouParecer          = true;
            $oDadosAluno2->lTemParecer = true;
        }

        if ($oAvaliacao->getElementoAvaliacao()->isResultado() && $sFormaAvaliacao == 'NOTA') {
            $nNota = ArredondamentoNota::formatar($nNota, $iAno);
        }
        $lAmparado      = $oAvaliacao->isAmparado();
        $sFormaObtencao = '';

        if ($oElementoAvaliacao instanceof ResultadoAvaliacao) {

            $sFormaObtencao = $oElementoAvaliacao->getFormaDeObtencao();

            if ($oDadosAproveitamento->proporcionalidadeComAmparoTotal()) {
                $lAmparado = true;
            }
        }

        $oDadosAvaliacao                 = new stdClass();
        $oDadosAvaliacao->iPeriodo       = $oAvaliacao->getElementoAvaliacao()->getOrdemSequencia();
        $oDadosAvaliacao->iCodigoPeriodo = null;
        /**
         * Proporcionalidade, validações para bloqueio dos campos
         * - quando turma possui proporcionalidade e aluno tem proporcionalidade configurada
         * - valida bloqueio dos períodos que não fazem parte do calculo do resultado final
         */
        $oDadosAvaliacao->lAlunoComProporcionalidade = false;
        $oDadosAvaliacao->lBloqueiaPeriodo           = false; // inicializa como false para não quebrar outros procedimentos

        if ($lTurmaUtilizaProporcionalidade && count($aOrdemPeriodoAplicaProporcionalidade) > 0) {

            $oDadosAvaliacao->lAlunoComProporcionalidade = true;
            /**
             * Só bloqueia o período se o período de avaliação, faz parte do calculo de soma
             */
            if (
                !in_array($oDadosAvaliacao->iPeriodo, $aOrdemPeriodoAplicaProporcionalidade) &&
                in_array($oDadosAvaliacao->iPeriodo, $aOrdemElementosCompoeResultadoComProporcionalidade)
            ) {
                $oDadosAvaliacao->lBloqueiaPeriodo = true; // caso não esteja configurado para aplicar proporcionalidade deve bloquear
            }
        }

        /**
         * Identifica que exite uma avaliação alternativa configurada para o período e que este não pode receber avaliação
         */
        $oDadosAvaliacao->lPeriodoComAvaliacaoAlternativaSemAvaliacao = false;

        $oDadosAvaliacao->lPeriodoControlaFrequencia = false;
        if ($oAvaliacao->getElementoAvaliacao() instanceof AvaliacaoPeriodica) {

            $oDadosAvaliacao->iCodigoPeriodo = $oAvaliacao->getElementoAvaliacao()->getPeriodoAvaliacao()->getCodigo();
            $oDadosAvaliacao->lPeriodoControlaFrequencia = $oAvaliacao->getElementoAvaliacao()->getPeriodoAvaliacao()->hasControlaFrequencia();
        }

        /**
         * Em caso de Forma de Obtenção igual a Aprovação por Periodo não exibe a nota final
         */
        if ($sFormaObtencao == 'AP') {
            $nNota = '';
        }

        $oDadosAvaliacao->nNota                 = "{$nNota}";
        $oDadosAvaliacao->sTipoAvaliacao        = $sTipoAvaliacao;
        $oDadosAvaliacao->iOrdem                = $iOrdem; // Ordem do conceito
        $oDadosAvaliacao->lEncerrado            = $oDadosAproveitamento->isEncerrado();
        $oDadosAvaliacao->lMinimoAtingido       = $oAvaliacao->temAproveitamentoMinimo();
        $oDadosAvaliacao->iFalta                = $iFaltasPeriodo;
        $oDadosAvaliacao->lFaltaBloqueada       = false;
        $oDadosAvaliacao->lNotaBloqueada        = false;
        $oDadosAvaliacao->iAulasPerido          = 0;
        $oDadosAvaliacao->lFaltasAbonadas       = $lFaltasAbonadas;
        $oDadosAvaliacao->iFaltasAbonadas       = $iFaltasAbonadas;
        $oDadosAvaliacao->sTipoEscola           = "";
        $oDadosAvaliacao->sTipoAbreviado        = "";
        $oDadosAvaliacao->sEscola               = "";
        $oDadosAvaliacao->sMunicipio            = "";
        $oDadosAvaliacao->sFormaObtencao        = $sFormaObtencao;
        $oDadosAvaliacao->lRecuperacao          = $oAvaliacao->emRecuperacao();
        $oDadosAvaliacao->lAvaliacaoExterna     = $oAvaliacao->isAvaliacaoExterna();
        $oDadosAvaliacao->lAmparado             = $lAmparado;
        $oDadosAvaliacao->lConvertido           = $oAvaliacao->isConvertido();
        $oDadosAvaliacao->sFormaAvaliacao       = $sFormaAvaliacao;
        $oDadosAvaliacao->iFormaAvaliacao       = $oAvaliacao->getElementoAvaliacao()
            ->getFormaDeAvaliacao()
            ->getCodigo();
        $oDadosAvaliacao->sTipoFormaAvaliacao   = $sFormaAvaliacao;
        $oDadosAvaliacao->nMenorValor           = "";
        $oDadosAvaliacao->nMaiorValor           = "";
        $oDadosAvaliacao->nVariacao             = "";
        $oDadosAvaliacao->mAproveitamentoMinino = $oAvaliacao->getElementoAvaliacao()
            ->getFormaDeAvaliacao()
            ->getAproveitamentoMinino();
        $oDadosAvaliacao->aConceito             = array();

        if ($sFormaAvaliacao == 'NIVEL') {
            $oDadosAvaliacao->aConceito = $oAvaliacao->getElementoAvaliacao()->getFormaDeAvaliacao()->getConceitos();
        }

        if ($sFormaAvaliacao == 'NOTA') {

            $oDadosAvaliacao->nMenorValor = $oAvaliacao->getElementoAvaliacao()->getFormaDeAvaliacao()->getMenorValor();
            $oDadosAvaliacao->nMaiorValor = $oAvaliacao->getElementoAvaliacao()->getFormaDeAvaliacao()->getMaiorValor();
            $oDadosAvaliacao->nVariacao   = $oAvaliacao->getElementoAvaliacao()->getFormaDeAvaliacao()->getVariacao();

            if ($oDadosAproveitamento->hasAvaliacaoAlternativa()) {

                $oAvaliacaoAlternativa = $oDadosAproveitamento->getAvaliacaoAlternativa();

                $oRegra = $oAvaliacaoAlternativa->getConfiguracaoPorOrdem($oAvaliacao->getElementoAvaliacao()->getOrdemSequencia());
                if (!is_null($oRegra)) {

                    $oDadosAvaliacao->nMenorValor           = $oRegra->iMenorValor;
                    $oDadosAvaliacao->nMaiorValor           = $oRegra->iMaiorValor;
                    $oDadosAvaliacao->nVariacao             = $oRegra->nVariacao;
                    $oDadosAvaliacao->mAproveitamentoMinino = $oRegra->iMinimoAprovacao;
                    if (empty($oRegra->sFormaAvaliacao)) {

                        $oDadosAvaliacao->lBloqueiaPeriodo                            = true;
                        $oDadosAvaliacao->lPeriodoComAvaliacaoAlternativaSemAvaliacao = true;
                    }
                }
            }
        }

        if ($oAvaliacao->getEscola() != "") {

            $oDadosAvaliacao->sTipoAbreviado = $oAvaliacao->getTipo();
            $oDadosAvaliacao->sTipoEscola    = $oAvaliacao->getTipo() == "M" ? 'ESCOLA DA REDE' : 'FORA DA REDE';
            $oDadosAvaliacao->sEscola        = $oAvaliacao->getEscola()->getNome();
            $oDadosAvaliacao->sMunicipio     = $oAvaliacao->getEscola()->getMunicipio();
        }

        if ($sFormaAvaliacao != 'PARECER' && $oDadosAvaliacao->nNota == '' && !$oDadosAvaliacao->lMinimoAtingido) {
            $oDadosAvaliacao->lMinimoAtingido = true;
        }

        /**
         * Quando possuir avaliação externa, buscamos a origem para verificar se já foi migrado
         */
        $oDadosAvaliacao->oAvaliacaoOrigem                  = new stdClass();
        $oDadosAvaliacao->oAvaliacaoOrigem->sFormaAvaliacao = "";
        $oDadosAvaliacao->oAvaliacaoOrigem->iFormaAvaliacao = "";
        $oDadosAvaliacao->oAvaliacaoOrigem->nMenorValor     = "";
        $oDadosAvaliacao->oAvaliacaoOrigem->nMaiorValor     = "";
        $oDadosAvaliacao->oAvaliacaoOrigem->sTipoEscola     = "";
        $oDadosAvaliacao->oAvaliacaoOrigem->sEscola         = "";
        $oDadosAvaliacao->oAvaliacaoOrigem->sMunicipio      = "";

        if ($oAvaliacao->isAvaliacaoExterna()) {

            $oAvaliacaoOrigem = $oAvaliacao->getAproveitamentoOrigem();

            if (!empty($oAvaliacaoOrigem)) {

                $oDadosAvaliacao->oAvaliacaoOrigem->sFormaAvaliacao = $oAvaliacaoOrigem->getElementoAvaliacao()
                    ->getFormaDeAvaliacao()
                    ->getDescricao();
                $oDadosAvaliacao->oAvaliacaoOrigem->iFormaAvaliacao = $oAvaliacaoOrigem->getElementoAvaliacao()
                    ->getFormaDeAvaliacao()
                    ->getCodigo();

                $sTipoEscola = $oAvaliacaoOrigem->getTipo() == "M" ? 'ESCOLA DA REDE' : 'FORA DA REDE';

                $oDadosAvaliacao->oAvaliacaoOrigem->sTipoEscola = $sTipoEscola;

                $oDadosAvaliacao->oAvaliacaoOrigem->iEscola    = "";
                $oDadosAvaliacao->oAvaliacaoOrigem->sEscola    = "";
                $oDadosAvaliacao->oAvaliacaoOrigem->sMunicipio = "";
                $oDadosAvaliacao->oAvaliacaoOrigem->sEstado    = "";

                $oEscolaOrigem = null;
                $oEscolaOrigem = $oAvaliacaoOrigem->getEscola();
                if (!empty($oEscolaOrigem)) {

                    $oDadosAvaliacao->oAvaliacaoOrigem->iEscola    = $oEscolaOrigem->getCodigo();
                    $oDadosAvaliacao->oAvaliacaoOrigem->sEscola    = $oEscolaOrigem->getNome();
                    $oDadosAvaliacao->oAvaliacaoOrigem->sMunicipio = $oEscolaOrigem->getMunicipio();
                    $oDadosAvaliacao->oAvaliacaoOrigem->sEstado    = $oEscolaOrigem->getUf();
                }

                $oDadosAvaliacao->oAvaliacaoOrigem->nMenorValor     = "";
                $oDadosAvaliacao->oAvaliacaoOrigem->nMaiorValor     = "";

                if ($sFormaAvaliacao == 'NOTA') {

                    $oDadosAvaliacao->oAvaliacaoOrigem->nMenorValor = $oAvaliacaoOrigem->getElementoAvaliacao()
                        ->getFormaDeAvaliacao()
                        ->getMenorValor();
                    $oDadosAvaliacao->oAvaliacaoOrigem->nMaiorValor = $oAvaliacaoOrigem->getElementoAvaliacao()
                        ->getFormaDeAvaliacao()
                        ->getMaiorValor();
                }
            }
        }

        /**
         * Quando não for Resultado Final
         */
        if (!$oAvaliacao->getElementoAvaliacao()->isResultado()) {

            $iChaveAulasNoPeriodo = "aulasPeriodo{$oAvaliacao->getElementoAvaliacao()->getPeriodoAvaliacao()->getCodigo()}";
            $iAulasPeriodo        = DBRegistry::get($iChaveAulasNoPeriodo);
            if ($iAulasPeriodo === null) {

                $iAulasPeriodo = $oRegencia->getTotalDeAulasNoPeriodo(
                    $oAvaliacao->getElementoAvaliacao()
                        ->getPeriodoAvaliacao()
                );
                if ($iAulasPeriodo == null) {
                    $iAulasPeriodo = 0;
                }

                DBRegistry::add($iChaveAulasNoPeriodo, $iAulasPeriodo);
            }
            $oDadosAvaliacao->lFaltaBloqueada = $iAulasPeriodo > 0 ? true : false;
            $oDadosAvaliacao->iAulasPerido    = $iAulasPeriodo;

            if (
                trim($oDadosAvaliacao->nNota) != '' && $lBloqueiaAlteracaoAvaliacao && $lProfessorLogado &&
                $oParam->iMaiorPermissao == 0
            ) {
                $oDadosAvaliacao->lNotaBloqueada  = true;
            }
        }

        if (
            $oAvaliacao->getElementoAvaliacao()->isResultado() &&
            $oAvaliacao->getElementoAvaliacao()->geraResultadoFinal()
        ) {

            /**
             * Adicionado validação para só calcular nota parcial se resultado não possui avaliação lançada/calculada
             */
            if (
                $oRetorno->lGeraResultadoParcial && $sFormaAvaliacao == 'NOTA'
                && $oAvaliacao->getValorAproveitamento()->getAproveitamento() === ''
            ) {
                // dd('aqui');
                $oDadosAvaliacao->nNota = (string)$oDadosAproveitamento->getNotaParcial($oAvaliacao->getElementoAvaliacao());
            }
        }
        if ($lAmparado) {
            $oDadosAvaliacao->nNota = 'AMP';
        }

        $oResultadoFinalRegencia = $oDadosAproveitamento->getResultadoFinal();

        $oDadoRegencia->oResultadoFinal                        = new stdClass();
        $oDadoRegencia->oResultadoFinal->nValor                = '';
        $oDadoRegencia->oResultadoFinal->sResultadoFinal       = '';
        $oDadoRegencia->oResultadoFinal->lPossuiResultadoFinal = false;
        $oDadoRegencia->oResultadoFinal->iAprovadoPeloConselho = 0;

        if (!empty($oResultadoFinalRegencia)) {

            if (!$lValidouObservacao && $oResultadoFinalRegencia->getObservacao() != '') {
                $lValidouObservacao          = true;
                $oDadosAluno2->lTemObservacao = true;
            }

            $mAproveitamentoFinal = ArredondamentoNota::formatar($oResultadoFinalRegencia->getValorAprovacao(), $iAno);
            if (
                !is_null($oResultadoFinalRegencia->getResultadoAvaliacao())
                && $oResultadoFinalRegencia->getResultadoAvaliacao()->getFormaDeObtencao() == 'AP'
            ) {
                $mAproveitamentoFinal = '-';
            }
            $oDadoRegencia->oResultadoFinal->nValor                = $mAproveitamentoFinal;
            $oDadoRegencia->oResultadoFinal->sResultadoFinal       = $oResultadoFinalRegencia->getResultadoFinal();
            $oDadoRegencia->oResultadoFinal->lPossuiResultadoFinal = true;

            $iDiario = $oResultadoFinalRegencia->getCodigoDiario();
            // if( !in_array( $iDiario, $aDiarioConselho ) ) {

            // $aDiarioConselho[]                       = $iDiario;
            // $_SESSION['diario_conselho'][ $iDiario ] = $oResultadoFinalRegencia->getFormaAprovacaoConselho();
            // }

            $oAprovadoConselho = $_SESSION['diario_conselho'][$iDiario];

            if ($oAprovadoConselho != null) {

                $oDadoRegencia->oResultadoFinal->iAprovadoPeloConselho = $oAprovadoConselho->getFormaAprovacao();
                $oDadoRegencia->oResultadoFinal->iAlterarNotaFinal     = $oAprovadoConselho->getAlterarNotaFinal();
                $oDadoRegencia->oResultadoFinal->sAvaliacaoConselho    = $oAprovadoConselho->getAvaliacaoConselho();

                /**
                 * Reclassificação por baixa frequência não aprova o aluno, pois o mesmo pode estar com o aproveitamento
                 * abaixo da média, e a reclassificação aprova apenas no quesito frequência
                 */
                if ($oAprovadoConselho->getFormaAprovacao() != AprovacaoConselho::RECLASSIFICACAO_BAIXA_FREQUENCIA) {
                    $oDadoRegencia->oResultadoFinal->sResultadoFinal = 'A';
                }
            }
        }

        if (($oDadosAproveitamento->getAmparo() != null && $oDadosAproveitamento->getAmparo()->isTotal())
            || ($oDadosAproveitamento->proporcionalidadeComAmparoTotal())
        ) {
            $oDadoRegencia->oResultadoFinal->nValor = 'AMP';
        }

        /**
         * Pega os elementos de recuperação
         */
        if (
            !$oAvaliacao->getElementoAvaliacao() instanceof ResultadoAvaliacao
            && $oAvaliacao->getElementoAvaliacao()->getElementoAvaliacaoVinculado() != null
            && $oAvaliacao->getElementoAvaliacao()->quantidadeMaximaDisciplinasParaRecuperacao()
        ) {
            $aElementosDeRecuperacao[] = $oAvaliacao;
        }

        /**
         * Pega os elementos que geraram uma recuperação
         */
        if ($oDadosAvaliacao->lRecuperacao) {
            $aElementosEmRecuperacao[$oDadosAvaliacao->iPeriodo] = $oDadosAvaliacao;
        }

        /**
         * Somamos todas as disciplinas com o elemento de avaliacoa reprovado no periodo
         * @todo Refatorar
         */
        $oDadosAvaliacao->iTotalDisciplinasReprovadas = count($oDiario2->getDisciplinasReprovadasNoPeriodo($oElementoAvaliacao, false));
        $oDadoRegencia->aAproveitamentos[] = $oDadosAvaliacao;
    }

    /**
     * Percorre os elementos de recuperação, para validar se o mesmo não encontra-se amparado e se o elemento
     * vinculado a ele, é o mesmo elemento que gerou uma recuperação
     */
    foreach ($aElementosDeRecuperacao as $oAvaliacaoAproveitamento) {

        if ($oAvaliacaoAproveitamento->isAmparado()) {
            continue;
        }

        $oElementoVinculado = $oAvaliacaoAproveitamento->getElementoAvaliacao()->getElementoAvaliacaoVinculado();

        if (!array_key_exists($oElementoVinculado->getOrdemSequencia(), $aElementosEmRecuperacao)) {
            continue;
        }

        $lTemRecuperacao = true;
    }

    // if ($lTemRecuperacao) {
    // $oDadoRegencia->oResultadoFinal->sResultadoFinal = 'REC';
    // }

    $oDadosAluno2->oDisciplina = $oDadoRegencia;



    // Exemplo de uso:
    $dadosConvertidos = mapearObjeto($oDadosAluno2);


    return $dadosConvertidos;
}

/**
 * Converte qualquer objeto ou array aninhado em array associativo
 * de forma recursiva, aplicando urldecode em strings.
 *
 * @author Uemerson Santana
 * @date 05/05/2025
 * Demanda: 17356
 *
 * @param mixed $data Objeto ou array a ser convertido.
 * @return mixed      Array associativo com dados decodificados.
 */
function mapearObjeto($data)
{
    if (is_object($data)) {
        $data = get_object_vars($data); // Converte stdClass para array
    }

    if (is_array($data)) {
        $resultado = [];
        foreach ($data as $chave => $valor) {
            if (is_object($valor) || is_array($valor)) {
                $resultado[$chave] = mapearObjeto($valor);
            } elseif (is_string($valor)) {
                $resultado[$chave] = urldecode($valor); // Decodifica nomes com +
            } else {
                $resultado[$chave] = $valor;
            }
        }
        return $resultado;
    }

    return $data; // Caso seja um valor simples
}


function testa($var)
{
    echo "<pre>";
    print_r($var);
    echo "</pre>";
}

function buscaPeriodos($calendario, $escola)
{
    $sql = pg_query("SELECT distinct ed09_i_codigo as codigo_periodo, ed09_c_descr as descricao_periodo from periodocalendario inner join periodoavaliacao on periodoavaliacao.ed09_i_codigo = periodocalendario.ed53_i_periodoavaliacao inner join calendario on calendario.ed52_i_codigo = periodocalendario.ed53_i_calendario inner join calendarioescola on calendarioescola.ed38_i_calendario = calendario.ed52_i_codigo inner join duracaocal on duracaocal.ed55_i_codigo = calendario.ed52_i_duracaocal where ed53_i_calendario in ({$calendario}) and ed38_i_escola in ({$escola}) order by ed09_i_codigo");
    $resultado = pg_fetch_all($sql);
    return $resultado;
}

function voltaFaltas($matricula, $disciplina, $turma)
{
    $sql1 = pg_query("SELECT ed59_i_serie, ed59_i_turma FROM matricula INNER JOIN turma ON ed60_i_turma = ed57_i_codigo INNER JOIN regencia ON ed57_i_codigo = ed59_i_turma WHERE ed60_i_codigo = {$matricula}");
    $r1 = pg_fetch_all($sql1);
    $serie = $r1[0]["ed59_i_serie"];
    $turma = $r1[0]["ed59_i_turma"];

    $sqla = " select
			ed95_i_codigo
			from
			diario
			inner join aluno on ed47_i_codigo = ed95_i_aluno
			inner join matricula on ed60_i_aluno = ed47_i_codigo
			inner join matriculaserie on ed60_i_codigo = ed221_i_matricula
			inner join regencia on ed59_i_codigo = ed95_i_regencia and ed59_i_serie = ed221_i_serie
			where
			ed60_i_codigo = {$matricula}
			and
			ed95_i_regencia = ed59_i_codigo
			and
			ed59_i_disciplina = {$disciplina}";
    if ($serie <> 29 and $serie <> 30 and $serie <> 31) {
        $sqla .= "
			    and
			    ed95_i_serie = {$serie}";
    }
    $sqla .= "
			and
			ed59_i_turma = {$turma}
			order by ed95_i_codigo";

    $sql2 = pg_query($sqla);
    $r2 = pg_fetch_all($sql2);
    $codigo = $r2[0]["ed95_i_codigo"];
    $sql3a = "
                    select
					distinct on (ed72_i_procavaliacao)
					ed232_c_descr as disciplina,
                    ed09_c_descr  as bimestre,
					ed72_i_valornota,
					ed72_c_valorconceito,
                    ed72_i_numfaltas
					from
					diarioavaliacao
					inner join diario           on ed95_i_codigo          = ed72_i_diario
					inner join procavaliacao    on ed41_i_codigo          = ed72_i_procavaliacao
					left join  pareceraval      on ed93_i_diarioavaliacao = ed72_i_codigo
					left join  abonofalta       on ed80_i_diarioavaliacao = ed72_i_codigo
	                inner join periodoavaliacao on ed09_i_codigo          = ed41_i_periodoavaliacao
	                inner join aluno            on ed47_i_codigo          = ed95_i_aluno
	                inner join matricula        on ed60_i_aluno           = ed47_i_codigo
	                inner join matriculaserie   on ed60_i_codigo          = ed221_i_matricula
	                inner join regencia         on ed59_i_codigo          = ed95_i_regencia and ed59_i_serie = ed221_i_serie
	                inner join disciplina       on ed12_i_codigo          = ed59_i_disciplina
	                inner join caddisciplina    on ed232_i_codigo         = ed12_i_caddisciplina
					where ed72_i_diario = {$codigo}
					ORDER BY ed72_i_procavaliacao";
    /*
$result = pg_query($sql3a);
$dados = db_utils::fieldsmemory($result,0);

if( $aluno == 122869 )
{
$arq = fopen("/dados/www/homologacao.epdvr.com.br/busca.sql","a+");
fwrite($arq, $sql3a);
fwrite($arq,"\r\n");
fclose($arq);
}
*/
    $sql3 = pg_query($sql3a);
    $resultado = pg_fetch_all($sql3);
    return $resultado;
}

function voltaFaltas2($matricula, $turma)
{ // para anos iniciais
    $sql1 = pg_query("SELECT ed59_i_serie, ed59_i_turma FROM matricula INNER JOIN turma ON ed60_i_turma = ed57_i_codigo INNER JOIN regencia ON ed57_i_codigo = ed59_i_turma WHERE ed60_i_codigo = {$matricula}");
    $r1 = pg_fetch_all($sql1);
    $serie = $r1[0]["ed59_i_serie"];
    $turma = $r1[0]["ed59_i_turma"];


    // as faltas de anos iniciais só são lançadas na linha PORTUGUESA
    $sqlD   = " SELECT
	            distinct on (ed12_i_codigo)
				ed12_i_codigo
				FROM
				regencia
				inner join disciplina          on disciplina.ed12_i_codigo = regencia.ed59_i_disciplina
				inner join caddisciplina       on ed232_i_codigo           = ed12_i_caddisciplina
				inner join turma               on turma.ed57_i_codigo      = regencia.ed59_i_turma
				inner join turmaserieregimemat on ed220_i_turma            = ed57_i_codigo
				inner join serieregimemat      on ed223_i_codigo           = ed220_i_serieregimemat
				inner join serie               on ed11_i_codigo            = ed223_i_serie
				inner join calendario          on ed52_i_codigo            = ed57_i_calendario
				WHERE
				ed232_c_descr        =  'LINGUA PORTUGUESA'
				AND ed57_i_codigo    = {$turma}
				AND ed59_c_freqglob != 'F'
				AND ed223_i_serie    = ed59_i_serie";

    $result_D = db_query($sqlD);
    $odados = db_utils::fieldsmemory($result_D, 0);

    $sql2 = pg_query("select
                    ed95_i_codigo
					from
					diario
					inner join aluno on ed47_i_codigo = ed95_i_aluno
					inner join matricula on ed60_i_aluno = ed47_i_codigo
					inner join matriculaserie on ed60_i_codigo = ed221_i_matricula
					inner join regencia on ed59_i_codigo = ed95_i_regencia and ed59_i_serie = ed221_i_serie
					where
					ed60_i_codigo = {$matricula}
					and
					ed95_i_regencia = ed59_i_codigo
					and
					ed59_i_disciplina = {$odados->ed12_i_codigo}
					and
					ed95_i_serie = {$serie}
					and
					ed59_i_turma = {$turma}
					order by ed95_i_codigo");

    $r2 = pg_fetch_all($sql2);
    $codigo = $r2[0]["ed95_i_codigo"];

    $sql3 = pg_query("
                    select
					distinct on (ed72_i_procavaliacao)
					ed232_c_descr as disciplina,
                    ed09_c_descr  as bimestre,
					ed72_i_valornota,
					ed72_c_valorconceito,
                    ed72_i_numfaltas
					from
					diarioavaliacao
					inner join diario           on ed95_i_codigo          = ed72_i_diario
					inner join procavaliacao    on ed41_i_codigo          = ed72_i_procavaliacao
					left join  pareceraval      on ed93_i_diarioavaliacao = ed72_i_codigo
					left join  abonofalta       on ed80_i_diarioavaliacao = ed72_i_codigo
	                inner join periodoavaliacao on ed09_i_codigo          = ed41_i_periodoavaliacao
	                inner join aluno            on ed47_i_codigo          = ed95_i_aluno
	                inner join matricula        on ed60_i_aluno           = ed47_i_codigo
	                inner join matriculaserie   on ed60_i_codigo          = ed221_i_matricula
	                inner join regencia         on ed59_i_codigo          = ed95_i_regencia and ed59_i_serie = ed221_i_serie
	                inner join disciplina       on ed12_i_codigo          = ed59_i_disciplina
	                inner join caddisciplina    on ed232_i_codigo         = ed12_i_caddisciplina
					where ed72_i_diario = {$codigo}
					ORDER BY ed72_i_procavaliacao");




    $resultado = pg_fetch_all($sql3);
    return $resultado;
}


function buscaCodDisciplinas($calendario, $turma)
{

    $disciplina = utf8_decode($disciplina);

    $sqlD   = " SELECT
	            distinct on (ed12_i_codigo)
				ed12_i_codigo,
				ed232_c_descr
				FROM
				regencia
				inner join disciplina          on disciplina.ed12_i_codigo = regencia.ed59_i_disciplina
				inner join caddisciplina       on ed232_i_codigo           = ed12_i_caddisciplina
				inner join turma               on turma.ed57_i_codigo      = regencia.ed59_i_turma
				inner join turmaserieregimemat on ed220_i_turma            = ed57_i_codigo
				inner join serieregimemat      on ed223_i_codigo           = ed220_i_serieregimemat
				inner join serie               on ed11_i_codigo            = ed223_i_serie
				inner join calendario          on ed52_i_codigo            = ed57_i_calendario
				WHERE
				ed52_i_codigo        =  " . $calendario . "
				AND ed57_i_codigo    = {$turma}
				AND ed59_c_freqglob != 'F'
				AND ed223_i_serie    = ed59_i_serie";


    $result_D = db_query($sqlD);
    return $result_D;
}


$escola = db_getsession("DB_coddepto");
$calendario = $_GET["calendario"];
$disciplina = $_GET["disciplina"];
$assAdicion = $_GET["aa"];
$assAtivid  = $_GET["at"];

$periodos = buscaPeriodos($calendario, $escola);
//testa($periodos);
//[periodo] => 6
//die("Confere");
//string(596) "select distinct ed09_i_codigo as codigo_periodo, ed09_c_descr as descricao_periodo from periodocalendario inner join periodoavaliacao on periodoavaliacao.ed09_i_codigo = periodocalendario.ed53_i_periodoavaliacao inner join calendario on calendario.ed52_i_codigo = periodocalendario.ed53_i_calendario inner join calendarioescola on calendarioescola.ed38_i_calendario = calendario.ed52_i_codigo inner join duracaocal on duracaocal.ed55_i_codigo = calendario.ed52_i_duracaocal where ed53_i_calendario in (17) and ed38_i_escola in (20100) order by ed09_i_codigo" Confere
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
//testa($oGet); // testar as variaveis que estão vindo aqui

$aTurmasSelecionadas = $oJson->decode(str_replace("\\", "", $oGet->oTurmas));
//$aTurmasSelecionadas
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
$oConfigRelatorio->iAlunosPorPagina         = 27; // Nº de alunos por página
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
foreach ($aTurmasSelecionadas as $oTurmaSelecionada) { //aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa

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

        //testa($oRegencia);
        $iContDisciplinas++;

        if ($iContDisciplinas <= $oConfigRelatorio->iMaximoDisciplinaPagina) {
            $oTurmaEtapa->aPaginas[0][$oRegencia->getCodigo()] = $oRegencia;
        } else if (
            $iContDisciplinas > $oConfigRelatorio->iMaximoDisciplinaPagina &&
            $iContDisciplinas <= ($oConfigRelatorio->iMaximoDisciplinaPagina * 2)
        ) {
            $oTurmaEtapa->aPaginas[1][$oRegencia->getCodigo()] = $oRegencia;
        } else {
            $oTurmaEtapa->aPaginas[2][$oRegencia->getCodigo()] = $oRegencia;
        }
    }


    /**
     * Informações referente a turma
     */
    $codTurma                     = $oTurma->getCodigo();
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
        $oDadosAluno->sNome               = $oMatricula->getAluno()->getNome(); // a pedido de suellem foi tirada a abreviação
        $oDadosAluno->iCodigoAluno        = $oMatricula->getAluno()->getCodigoAluno();
        $oDadosAluno->sSituacao           = $oMatricula->getSituacao();
        /**
         * Autor: Uemerson Santana
         * Data: 23/04/2025
         * Demanda: 17301
         */
        $oDadosAluno->sSituacaoAbreviada  = buscaAbreviaturaSituacao($oDadosAluno->sSituacao);

        $oDadosAluno->oDtMatricula        = $oMatricula->getDataMatricula();
        $oDadosAluno->iClassificacao      = $oMatricula->getNumeroOrdemAluno();
        $oDadosAluno->aAvaliacao          = array();
        $oDadosAluno->iTotalFaltas        = 0;
        $oDadosAluno->lAvaliadoPorParecer = $oMatricula->isAvaliadoPorParecer();

        db_inicio_transacao();

        $oDiarioDeClasse = $oMatricula->getDiarioDeClasse();

        $iContDisciplinas = 0;


        foreach ($oTurma->getDisciplinasPorEtapa($oEtapa) as $oRegencia) {

            // if (  "ARTE" != $oRegencia->getDisciplina()->getNomeDisciplina() ) {
            //     continue;
            // }

            $iContDisciplinas++;
            $oDisciplinaDiario        = $oDiarioDeClasse->getDisciplinasPorRegencia($oRegencia, $oAvaliacaoPeriodica);
            $oAvaliacaoAproveitamento = $oDisciplinaDiario->getAvaliacoesPorOrdem($oAvaliacaoPeriodica->getOrdemSequencia());

            $oAvaliacao = new stdClass();
            $oAvaliacao->iRegencia       = $oRegencia->getCodigo();
            $oAvaliacao->sRegencia       = $oRegencia->getDisciplina()->getNomeDisciplina();
            $regenc                      = $oRegencia->getCodigo();


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
            } else if (
                $iContDisciplinas > $oConfigRelatorio->iMaximoDisciplinaPagina &&
                $iContDisciplinas <= ($oConfigRelatorio->iMaximoDisciplinaPagina * 2)
            ) {
                $oDadosAluno->aAvaliacao[1][$oRegencia->getCodigo()] = $oAvaliacao;
            } else {
                $oDadosAluno->aAvaliacao[2][$oRegencia->getCodigo()] = $oAvaliacao;
            }

            $oDadosAluno->iTotalFaltas  += $oAvaliacao->iFaltas;

            /**
             * Verifica se turma utiliza proporcionalidade
             */
            $lTurmaUtilizaProporcionalidade = false;
            $oProcedimentoAvaliacao         = $oTurma->getProcedimentoDeAvaliacaoDaEtapa($oEtapa);

            $aOrdemElementosCompoeResultadoComProporcionalidade = array();
            foreach ($oProcedimentoAvaliacao->getResultados() as $oResultadoAvaliacao) {

                if (
                    $oResultadoAvaliacao->getFormaDeObtencao() == 'SO' &&
                    $oResultadoAvaliacao->utilizaProporcionalidade()
                ) {

                    $lTurmaUtilizaProporcionalidade                     = true;
                    $aOrdemElementosCompoeResultadoComProporcionalidade = buscaOrdemElementos($oResultadoAvaliacao);
                }
            }
            // if ( '118165' != $oMatricula->getAluno()->getCodigoAluno() ) {
            //     continue;
            // }

            $aRetorno = gerarDadosResultadoFinal(
                $oMatricula,
                $oRegencia,
                $oTurmaEtapa->iAnoCalendario,
                $lTurmaUtilizaProporcionalidade,                 // $lTurmaUtilizaProporcionalidade
                false,                 // $lBloqueiaAlteracaoAvaliacao
                false                 // $lProfessorLogado
            );


            $oDadosAluno->resultado_final[$oRegencia->getDisciplina()->getNomeDisciplina()] = calcularDescricaoFinal($aRetorno);

            // var_dump($aRetorno['oDisciplina']['oResultadoFinal']['sResultadoFinal']);exit();
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

foreach ($aTurmas as $oTurmaEtapa) {  //aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa

    $head1 = "Resumo Anual";
    $head2 = "Curso: {$oTurmaEtapa->sCurso}";
    $head3 = "Turma: {$oTurmaEtapa->sTurma}";
    $head4 = "Calendário: {$oTurmaEtapa->sCalendario}";
    $head5 = "Etapa: {$oTurmaEtapa->sEtapa}";
    $head6 = "Turno: {$oTurmaEtapa->sTurno}";
    $discip = utf8_decode($disciplina);



    $lPrimeiraPagina = true;
    $iPaginas        = count($oTurmaEtapa->aPaginas);
    $diasletivos     = $oTurmaEtapa->diasLetivos;

    $disciplinas           = buscaCodDisciplinas($calendario, $codTurma);
    for ($y = 0; $y < pg_num_rows($disciplinas); $y++) {
        $discipl = db_utils::fieldsMemory($disciplinas, $y);
        $head7 = "Disciplina: {$discipl->ed232_c_descr}";
        $disciplina = $discipl->ed232_c_descr;
        for ($iPagina = 0; $iPagina < $iPaginas; $iPagina++) {

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
            foreach ($oTurmaEtapa->aAlunos as $oAluno) { //bbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbb
                $xfaltas = voltaFaltas($oAluno->iMatricula, $discipl->ed12_i_codigo, $oTurmaEtapa->sTurma);


                //       $oDadosAluno                      = new stdClass();
                // $oDadosAluno->iMatricula          = $oAluno->sSituacaoAbreviada;
                // //$oDadosAluno->sNome               = abreviar($oMatricula->getAluno()->getNome(), 22, true);
                // $oDadosAluno->sNome               = $oMatricula->getAluno()->getNome(); // a pedido de suellem foi tirada a abreviação
                // $oDadosAluno->iCodigoAluno        = $oMatricula->getAluno()->getCodigoAluno();
                // $oDadosAluno->sSituacao           = $oMatricula->getSituacao();
                // $sSituacaoAbreviada               = buscaAbreviaturaSituacao($oDadosAluno->sSituacao);

                $sql = "
				 select
				 ed52_c_descr as anos
				 from
				 calendario
				 where
				 ed52_i_codigo = " . $_GET["calendario"];
                $resultado = db_query($sql);
                $calDados  = db_utils::fieldsMemory($resultado, 0);

                if ($lPrimeiraPagina) {
                    $lPrimeiraPagina = false;
                    adicionaHeader($oPdf, $oTurmaEtapa, $oConfigRelatorio, $iPagina);
                }

                if (!$oConfigRelatorio->lTrocaTurma && $oAluno->sSituacao == 'TROCA DE TURMA') {
                    continue;
                }

                $iAlunosImpressoPagina++;
                if ($iAlunosImpressoPagina > $oConfigRelatorio->iAlunosPorPagina) {
                    $iAlunosImpressoPagina = 1;
                    //montaQuadroLegendaAssinatura($oPdf, $oConfigRelatorio, $oTurmaEtapa->lUltimoPeriodo, $oTurmaEtapa);
                    montaQuadroLegendaAssinatura($oPdf, $oConfigRelatorio, $oTurmaEtapa->lUltimoPeriodo, $oTurmaEtapa, $oTurmaSelecionada->iTurma, $oTurmaSelecionada->iEtapa, $regenc, $assAdicion, $assAtivid, $disciplina);
                    if ($iAlunosImpressoPagina < $iAlunosTurma) {
                        adicionaHeader($oPdf, $oTurmaEtapa, $oConfigRelatorio, $iPagina);
                    }
                }
                $media = 0;
                if (substr($calDados->anos, 0, 11) == 'ANOS FINAIS') //******************************** Anos finais ******************************************
                {
                    $oPdf->SetFont("arial", '', 7);
                    $oPdf->Cell(5, $oConfigRelatorio->iAlturaLinha, $oAluno->iClassificacao, 1, 0, 'C');
                    $oPdf->Cell(100, $oConfigRelatorio->iAlturaLinha, $oAluno->sNome, 1);
                    // já existia o array $xfaltas, então eu utilizei para buscar as notas - Divaldo 11/10/2024
                    $media = 0;
                    if ($xfaltas[0]["disciplina"] <> 'TECNOLOGIA E INOVAÇÃO') {
                        if ($xfaltas[0]["ed72_i_valornota"] <> null) {
                            if ($xfaltas[0]["ed72_i_valornota"] < 5) // estas condições coloca em negrito/destaque notas inferior a 5 (reprovação)
                            {
                                $oPdf->SetFont("arial", 'B', 7);
                            } else {
                                $oPdf->SetFont("arial", '', 7);
                            }
                            // traçar a nota se ela for a menor que o 2º bimestre e a recuperação
                            if ($xfaltas[0]["ed72_i_valornota"] < $xfaltas[1]["ed72_i_valornota"] and $xfaltas[0]["ed72_i_valornota"] < $xfaltas[2]["ed72_i_valornota"]) {
                                $oPdf->Line($oPdf->getX() + 3, $oPdf->getY() + 2, $oPdf->getX() + 7, $oPdf->getY() + 2);
                            }
                            $oPdf->Cell(10, $oConfigRelatorio->iAlturaLinha, number_format($xfaltas[0]["ed72_i_valornota"], 1, ',', '.'), 1, 0, "C");        //1º
                        } else {
                            $oPdf->Cell(10, $oConfigRelatorio->iAlturaLinha, '', 1, 0, "C");        //1º
                        }
                        if ($xfaltas[1]["ed72_i_valornota"] <> null) {

                            if ($xfaltas[1]["ed72_i_valornota"] < 5) {
                                $oPdf->SetFont("arial", 'B', 7);
                            } else {
                                $oPdf->SetFont("arial", '', 7);
                            }
                            // traçar a nota se ela for a menor que o 1º bimestre e a recuperação
                            if ($xfaltas[1]["ed72_i_valornota"] < $xfaltas[0]["ed72_i_valornota"] and $xfaltas[1]["ed72_i_valornota"] < $xfaltas[2]["ed72_i_valornota"]) {
                                $oPdf->Line($oPdf->getX() + 3, $oPdf->getY() + 2, $oPdf->getX() + 7, $oPdf->getY() + 2);
                            }
                            $oPdf->Cell(10, $oConfigRelatorio->iAlturaLinha, number_format($xfaltas[1]["ed72_i_valornota"], 1, ',', '.'), 1, 0, "C");        //2º
                        } else {
                            $oPdf->Cell(10, $oConfigRelatorio->iAlturaLinha, '', 1, 0, "C");        //2º
                        }
                        if ($xfaltas[2]["ed72_i_valornota"] <> null) {
                            if ($xfaltas[2]["ed72_i_valornota"] < 5) {
                                $oPdf->SetFont("arial", 'B', 7);
                            } else {
                                $oPdf->SetFont("arial", '', 7);
                            }
                            $oPdf->Cell(10, $oConfigRelatorio->iAlturaLinha, number_format($xfaltas[2]["ed72_i_valornota"], 1, ',', '.'), 1, 0, "C");        //REC S
                        } else {
                            $oPdf->Cell(10, $oConfigRelatorio->iAlturaLinha, '', 1, 0, "C");        //REC S
                        }
                        if ($xfaltas[3]["ed72_i_valornota"] <> null) {
                            if ($xfaltas[3]["ed72_i_valornota"] < 5) {
                                $oPdf->SetFont("arial", 'B', 7);
                            } else {
                                $oPdf->SetFont("arial", '', 7);
                            }
                            $oPdf->Cell(10, $oConfigRelatorio->iAlturaLinha, number_format($xfaltas[3]["ed72_i_valornota"], 1, ',', '.'), 1, 0, "C");        //3º
                        } else {
                            $oPdf->Cell(10, $oConfigRelatorio->iAlturaLinha, '', 1, 0, "C");        //3º
                        }
                        if ($xfaltas[4]["ed72_i_valornota"] <> null) {
                            if ($xfaltas[4]["ed72_i_valornota"] < 5) {
                                $oPdf->SetFont("arial", 'B', 7);
                            } else {
                                $oPdf->SetFont("arial", '', 7);
                            }
                            $oPdf->Cell(10, $oConfigRelatorio->iAlturaLinha, number_format($xfaltas[4]["ed72_i_valornota"], 1, ',', '.'), 1, 0, "C");        //4º
                        } else {
                            $oPdf->Cell(10, $oConfigRelatorio->iAlturaLinha, '', 1, 0, "C");        //4º
                        }
                    } else {
                        if ($xfaltas[0]["ed72_c_valorconceito"] == null or trim($xfaltas[0]["ed72_c_valorconceito"]) <> '') {
                            $oPdf->Cell(10, $oConfigRelatorio->iAlturaLinha, $xfaltas[0]["ed72_c_valorconceito"], 1, 0, "C");        //1º
                        } else {
                            $oPdf->Cell(10, $oConfigRelatorio->iAlturaLinha, '', 1, 0, "C");        //1º
                        }
                        if ($xfaltas[1]["ed72_c_valorconceito"] == null or trim($xfaltas[1]["ed72_c_valorconceito"]) <> '') {
                            $oPdf->Cell(10, $oConfigRelatorio->iAlturaLinha, $xfaltas[1]["ed72_c_valorconceito"], 1, 0, "C");        //2º
                        } else {
                            $oPdf->Cell(10, $oConfigRelatorio->iAlturaLinha, '', 1, 0, "C");        //2º
                        }
                        $oPdf->Cell(10, $oConfigRelatorio->iAlturaLinha, '', 1, 0, "C"); //REC S
                        //na linha acima estava acontecendo um erro, o 3º bimestre estava sendo mostrado na coluna de REC S porque TECNOLOGIA E INOVAÇÃO não tem a coluna REC S e nem REC F
                        //pois no caso de conceito não existe recuperação e o indice do array muda de valor, indice 2 que mostrava dados de REC S passa a mostrar 3º bimestre
                        //acredito que este seja o mesmo erro que esta acontecendo no boletim,
                        if ($xfaltas[2]["ed72_c_valorconceito"] == null or trim($xfaltas[2]["ed72_c_valorconceito"]) <> '') {
                            $oPdf->Cell(10, $oConfigRelatorio->iAlturaLinha, $xfaltas[2]["ed72_c_valorconceito"], 1, 0, "C");        //3º
                        } else {
                            $oPdf->Cell(10, $oConfigRelatorio->iAlturaLinha, '', 1, 0, "C");        //3º
                        }
                        if ($xfaltas[2]["ed72_c_valorconceito"] == null or trim($xfaltas[2]["ed72_c_valorconceito"]) <> '') {
                            $oPdf->Cell(10, $oConfigRelatorio->iAlturaLinha, $xfaltas[3]["ed72_c_valorconceito"], 1, 0, "C");        //4º
                        } else {
                            $oPdf->Cell(10, $oConfigRelatorio->iAlturaLinha, '', 1, 0, "C");        //4º
                        }
                    }

                    $media = 0;
                    if (
                        $xfaltas[0]["ed72_i_valornota"] <> null and    // se só tiver a nota do 1º bimestre
                        $xfaltas[1]["ed72_i_valornota"] == null and // 2º bimestre
                        $xfaltas[2]["ed72_i_valornota"] == null and // recup
                        $xfaltas[3]["ed72_i_valornota"] == null and // 3º bimestre
                        $xfaltas[4]["ed72_i_valornota"] == null     // 4º bimestre

                    ) // 1º bimestre
                    {
                        $media = $xfaltas[0]["ed72_i_valornota"];
                    }


                    if (
                        $xfaltas[1]["ed72_i_valornota"] <> null and  // se só tiver a nota do 1º bimestre 2º bimestre
                        $xfaltas[0]["ed72_i_valornota"] <> null and
                        $xfaltas[2]["ed72_i_valornota"] == null and // recup
                        $xfaltas[3]["ed72_i_valornota"] == null and // 3º bimestre
                        $xfaltas[4]["ed72_i_valornota"] == null     // 4º bimestre
                    ) {
                        $media = ($xfaltas[0]["ed72_i_valornota"] + $xfaltas[1]["ed72_i_valornota"]) / 2;
                    }
                    if (
                        $xfaltas[3]["ed72_i_valornota"] <> null and // se só tiver a nota do 1º, 2º e 3º bimestre
                        $xfaltas[0]["ed72_i_valornota"] <> null and
                        $xfaltas[1]["ed72_i_valornota"] <> null and
                        $xfaltas[2]["ed72_i_valornota"] == null and // recup
                        $xfaltas[4]["ed72_i_valornota"] == null     // 4º bimestre
                    ) {
                        $media = ($xfaltas[0]["ed72_i_valornota"] + $xfaltas[1]["ed72_i_valornota"] + $xfaltas[3]["ed72_i_valornota"]) / 3;
                    }


                    // todos os bimestre foram feitos mas não foi feito a recuperação
                    if (
                        $xfaltas[0]["ed72_i_valornota"] <> null and
                        $xfaltas[1]["ed72_i_valornota"] <> null and
                        $xfaltas[3]["ed72_i_valornota"] <> null and
                        $xfaltas[4]["ed72_i_valornota"] <> null and
                        $xfaltas[2]["ed72_i_valornota"] ==   null // recup
                    ) {
                        $media = ($xfaltas[0]["ed72_i_valornota"] + $xfaltas[1]["ed72_i_valornota"] + $xfaltas[3]["ed72_i_valornota"] + $xfaltas[4]["ed72_i_valornota"]) / 4;
                    }


                    if (
                        $xfaltas[2]["ed72_i_valornota"] <> null and  // se o aluno fez a recuperação e o 1º e 2º bimestre
                        $xfaltas[0]["ed72_i_valornota"] <> null and
                        $xfaltas[1]["ed72_i_valornota"] <> null and
                        $xfaltas[3]["ed72_i_valornota"] == null and // 3º bimestre
                        $xfaltas[4]["ed72_i_valornota"] == null     // 4º bimestre
                    ) {
                        if (($xfaltas[0]["ed72_i_valornota"] > $xfaltas[1]["ed72_i_valornota"])) // se o 1º bimestre for maior que o 2º
                        {
                            if ($xfaltas[1]["ed72_i_valornota"] < $xfaltas[2]["ed72_i_valornota"]) // se o 2º bimestre for menor que a recuperação
                            {
                                $media = ($xfaltas[0]["ed72_i_valornota"] + $xfaltas[2]["ed72_i_valornota"]) / 2; // descarta o 2º bimestre e usa o  (1º+recup)/2
                            } else {
                                $media = ($xfaltas[0]["ed72_i_valornota"] + $xfaltas[1]["ed72_i_valornota"]) / 2; // se a recuperação for menor que o 2º bimestre, descarta a recuperação
                            }
                        } else { // o primeiro bimestre é menor que o segundo
                            if ($xfaltas[0]["ed72_i_valornota"] < $xfaltas[2]["ed72_i_valornota"]) // se o 1º bimestre for menor que a recuperação e menor que o segundo
                            {
                                $media = ($xfaltas[2]["ed72_i_valornota"] + $xfaltas[1]["ed72_i_valornota"]) / 2;
                            } else { // o primeiro bimestre é menor que o segundo, mas é maior que recuperação, descarta a recuperação
                                $media = ($xfaltas[0]["ed72_i_valornota"] + $xfaltas[1]["ed72_i_valornota"]) / 2;
                            }
                        }
                    }

                    if (
                        $xfaltas[2]["ed72_i_valornota"] <> null and  // se o aluno fez a recuperação e o 1º, 2º e 3º bimestre
                        $xfaltas[0]["ed72_i_valornota"] <> null and
                        $xfaltas[1]["ed72_i_valornota"] <> null and
                        $xfaltas[3]["ed72_i_valornota"] <> null and
                        $xfaltas[4]["ed72_i_valornota"] == null     // 4º bimestre
                    ) {


                        if (($xfaltas[0]["ed72_i_valornota"] > $xfaltas[1]["ed72_i_valornota"])) // se o 1º bimestre for maior que o 2º
                        {
                            if ($xfaltas[1]["ed72_i_valornota"] < $xfaltas[2]["ed72_i_valornota"]) // se o 2º bimestre for menor que a recuperação
                            {
                                $media = ($xfaltas[0]["ed72_i_valornota"] + $xfaltas[2]["ed72_i_valornota"] + $xfaltas[3]["ed72_i_valornota"]) / 3; // descarta o 2º bimestre e usa o  (1º+3º+recup)/3
                            } else {
                                $media = ($xfaltas[0]["ed72_i_valornota"] + $xfaltas[1]["ed72_i_valornota"] + $xfaltas[3]["ed72_i_valornota"]) / 3; // se a recuperação for menor que o
                            }
                        } else { // o primeiro bimestre é menor que o segundo
                            if ($xfaltas[0]["ed72_i_valornota"] < $xfaltas[2]["ed72_i_valornota"]) // se o 1º bimestre for menor que a recuperação e menor que o segundo
                            {
                                $media = ($xfaltas[2]["ed72_i_valornota"] + $xfaltas[1]["ed72_i_valornota"] + $xfaltas[3]["ed72_i_valornota"]) / 3;
                            } else { // o primeiro bimestre é menor que o segundo, mas é maior que recuperação, descarta a recuperação
                                $media = ($xfaltas[0]["ed72_i_valornota"] + $xfaltas[1]["ed72_i_valornota"] + $xfaltas[3]["ed72_i_valornota"]) / 3;
                            }
                        }
                    }

                    if (
                        $xfaltas[2]["ed72_i_valornota"] <> null and // se o aluno fez a recuperação e todos bimestre
                        $xfaltas[0]["ed72_i_valornota"] <> null and
                        $xfaltas[1]["ed72_i_valornota"] <> null and
                        $xfaltas[3]["ed72_i_valornota"] <> null and
                        $xfaltas[4]["ed72_i_valornota"] <> null      // 4º bimestre
                    ) {
                        if (($xfaltas[0]["ed72_i_valornota"] > $xfaltas[1]["ed72_i_valornota"])) // se o 1º bimestre for maior que o 2º
                        {
                            if ($xfaltas[1]["ed72_i_valornota"] < $xfaltas[2]["ed72_i_valornota"]) // se o 2º bimestre for menor que a recuperação
                            {
                                $media = ($xfaltas[0]["ed72_i_valornota"] + $xfaltas[2]["ed72_i_valornota"] + $xfaltas[3]["ed72_i_valornota"] + $xfaltas[4]["ed72_i_valornota"]) / 4; // descarta o 2º bimestre
                            } else {
                                $media = ($xfaltas[0]["ed72_i_valornota"] + $xfaltas[1]["ed72_i_valornota"] + $xfaltas[3]["ed72_i_valornota"] + $xfaltas[4]["ed72_i_valornota"]) / 4; // se a recuperação for menor que o
                                //2º bimestre, descarta a recuperação
                            }
                        } else { // o primeiro bimestre é menor que o segundo
                            if ($xfaltas[0]["ed72_i_valornota"] < $xfaltas[2]["ed72_i_valornota"]) // se o 1º bimestre for menor que a recuperação e menor que o segundo
                            {
                                $media = ($xfaltas[2]["ed72_i_valornota"] + $xfaltas[1]["ed72_i_valornota"] + $xfaltas[3]["ed72_i_valornota"] + $xfaltas[4]["ed72_i_valornota"]) / 4;
                            } else { // o primeiro bimestre é menor que o segundo, mas é maior que recuperação, descarta a recuperação
                                $media = ($xfaltas[0]["ed72_i_valornota"] + $xfaltas[1]["ed72_i_valornota"] + $xfaltas[3]["ed72_i_valornota"] + $xfaltas[4]["ed72_i_valornota"]) / 4;
                            }
                        }
                    }



                    //		  $oPdf->Cell(10, $oConfigRelatorio->iAlturaLinha, number_format($media, 1, ',', '.'), 1, 0, "C");        //MÉDIA

                    /*
                        Explicação Suellem para médias anos finais 16/10/2024
                        ANOS FINAIS @Divaldo para a Média Anual o cálculo é feito somando e dividindo as notas.
                        Exemplo: 3 notas lançadas, soma e divide por 3
                        4 notas lançadas soma e divide por 4.
                        A Média Final é obtida através da média aritmética entre a média anual e a nota da recuperação final.
                        Exemplo: 4,0 (média anual) + 3,0 (recuperação final) somando (7,0) divide por 2 - 3,5 Média Final desse aluno

                        */
                    if ($media > 0) {
                        $mediaSF = $media; // media antes da formatacao para 1 casa decimal com virgula
                        $nm = explode(".", $media);
                        if (substr($nm[1], 0, 1) == '' or substr($nm[1], 0, 1) == ' ') {
                            $nm[1] = '0';
                        }
                        $media = $nm[0] . "," . substr($nm[1], 0, 1);
                        $mediaA = $media;     // media anual igual a média
                        if ($mediaSF < 5) {
                            $oPdf->SetFont("arial", 'B', 7);
                        } else {
                            $oPdf->SetFont("arial", '', 7);
                        }
                        if (
                            $xfaltas[0]["ed72_i_valornota"] <> null or $xfaltas[1]["ed72_i_valornota"] <> null or $xfaltas[2]["ed72_i_valornota"] <> null or
                            $xfaltas[3]["ed72_i_valornota"] <> null or $xfaltas[4]["ed72_i_valornota"] <> null
                        ) {
                            $oPdf->Cell(10, $oConfigRelatorio->iAlturaLinha, $mediaA, 1, 0, "C");        //M A
                        }
                    } else {
                        /**
                         * Autor: Uemerson Santana
                         * Data: 23/04/2025
                         * Demanda: 17301
                         */
                        if ($xfaltas[0]["disciplina"] == 'TECNOLOGIA E INOVAÇÃO') {

                            // Pega o conceito do 4º bimestre (posição 4)
                            $mediaA  = $xfaltas[3]["ed72_c_valorconceito"];
                            $mediaSF = $mediaA;

                            // Se tiver valor, exibe
                            if (trim($mediaA) <> '') {
                                $oPdf->Cell(10, $oConfigRelatorio->iAlturaLinha, $mediaA, 1, 0, "C"); // M A
                            } else {
                                $oPdf->Cell(10, $oConfigRelatorio->iAlturaLinha, '', 1, 0, "C"); // M A vazio
                            }
                        } else {
                            $mediaA  = '';
                            $mediaSF = '';
                            $oPdf->Cell(10, $oConfigRelatorio->iAlturaLinha, $mediaA, 1, 0, "C"); // M A vazio
                        }        //M A
                    }


                    /*
                    @autor Uemerson Santana
                    @data 10/06/2025
                    @demanda: 17356
                    */
                    if (
                        stripos(trim($oAluno->sSituacao), 'TRANSFERIDO') !== false ||
                        stripos(trim($oAluno->sSituacao), 'TROCA DE TURMA') !== false ||
                        stripos(trim($oAluno->sSituacao), 'EVADIDO') !== false ||
                        stripos(trim($oAluno->sSituacao), 'CANCELADO') !== false
                    ) {
                        $mediaF = '';
                    } else {

                        if ($xfaltas[5]["ed72_i_valornota"] > 0) // Se o aluno fez a recuperação Final
                        {
                            $mediaF = ($mediaSF + $xfaltas[5]["ed72_i_valornota"]) / 2;       // média final conforme suellem (media+ rec final)/2
                            $nm = explode(".", $mediaF);
                            if (substr($nm[1], 0, 1) == '' or substr($nm[1], 0, 1) == ' ') {
                                $nm[1] = '0';
                            }
                            $mediaF = $nm[0] . "," . substr($nm[1], 0, 1);
                        } else {
                            $mediaF = $mediaA;  //Não havendo recuperação final continua como a media anual
                        }
                    }

                    // verificar com Suellem se a media final só deverá aparecer se a recuperação tiver valor ou se ficara igual a media anual caso não haja
                    if ($xfaltas[5]["ed72_i_valornota"] <> null) {
                        if ($xfaltas[5]["ed72_i_valornota"] < 5) {
                            $oPdf->SetFont("arial", 'B', 7);
                        } else {
                            $oPdf->SetFont("arial", '', 7);
                        }
                        $oPdf->Cell(10, $oConfigRelatorio->iAlturaLinha, number_format($xfaltas[5]["ed72_i_valornota"], 1, ',', '.'), 1, 0, "C");        //REC F
                    } else {
                        $oPdf->Cell(10, $oConfigRelatorio->iAlturaLinha, '', 1, 0, "C");        //REC F
                    }

                    if ($mediaF < 5) {
                        $oPdf->SetFont("arial", 'B', 7);
                    } else {
                        $oPdf->SetFont("arial", '', 7);
                    }


                    if (
                        $xfaltas[0]["ed72_i_valornota"] <> null or $xfaltas[1]["ed72_i_valornota"] <> null or $xfaltas[2]["ed72_i_valornota"] <> null or
                        $xfaltas[3]["ed72_i_valornota"] <> null or $xfaltas[4]["ed72_i_valornota"] <> null
                    ) {
                        $oPdf->Cell(10, $oConfigRelatorio->iAlturaLinha, $mediaF, 1, 0, "C");        //M F
                    } else {
                        /*
                        @autor Uemerson Santana
                        @data 05/06/2025
                        @demanda: 17356
                        */
                        $oPdf->Cell(10, $oConfigRelatorio->iAlturaLinha, $mediaF, 1, 0, "C");        //M F
                    }

                    $oPdf->Cell(10, $oConfigRelatorio->iAlturaLinha, $xfaltas[0]["ed72_i_numfaltas"], 1, 0, "C");        //1º
                    $oPdf->Cell(10, $oConfigRelatorio->iAlturaLinha, $xfaltas[1]["ed72_i_numfaltas"], 1, 0, "C");        //2º
                    $oPdf->Cell(10, $oConfigRelatorio->iAlturaLinha, $xfaltas[3]["ed72_i_numfaltas"], 1, 0, "C");        //3º
                    $oPdf->Cell(10, $oConfigRelatorio->iAlturaLinha, $xfaltas[4]["ed72_i_numfaltas"], 1, 0, "C");        //4º


                    if (
                        stripos(trim($oAluno->sSituacao), 'TRANSFERIDO') !== false ||
                        stripos(trim($oAluno->sSituacao), 'TROCA DE TURMA') !== false ||
                        stripos(trim($oAluno->sSituacao), 'EVADIDO') !== false ||
                        stripos(trim($oAluno->sSituacao), 'CANCELADO') !== false
                    ) {
                        $cfreq = '';
                    } else {
                        $aulasdadas =  percfrequencia($_GET["calendario"]);
                        $cfreq = (($aulasdadas - ($xfaltas[0]["ed72_i_numfaltas"] + $xfaltas[1]["ed72_i_numfaltas"] + $xfaltas[3]["ed72_i_numfaltas"] + $xfaltas[4]["ed72_i_numfaltas"])) / $aulasdadas) * 100;
                        $nm = explode(".", $cfreq);
                        $cfreq = $nm[0];

                        if ($xfaltas[0]["disciplina"] == 'TECNOLOGIA E INOVAÇÃO') {
                            $cfreq = '';
                        }
                    }

                    $oPdf->Cell(10, $oConfigRelatorio->iAlturaLinha, $cfreq, 1, 0, 'C');    //Frequência %

                    /*
                    @autor Uemerson Santana
                    @data 10/06/2025
                    @demanda: 17356
                    */
                    if (
                        stripos(trim($oAluno->sSituacao), 'TRANSFERIDO') !== false ||
                        stripos(trim($oAluno->sSituacao), 'TROCA DE TURMA') !== false
                    ) {
                        $totalF = '';
                    } else {
                        $totalF = $xfaltas[0]["ed72_i_numfaltas"] + $xfaltas[1]["ed72_i_numfaltas"] + $xfaltas[3]["ed72_i_numfaltas"] + $xfaltas[4]["ed72_i_numfaltas"];
                    }

                    $oPdf->Cell(20, $oConfigRelatorio->iAlturaLinha, $totalF, 1, 0, "C");        //Total de Faltas



                    /**
                     * Autor: Uemerson Santana
                     * Data: 30/04/2025
                     * Demanda: 17356
                     */
                    $oPdf->Cell(30, $oConfigRelatorio->iAlturaLinha, $oAluno->resultado_final[$discipl->ed232_c_descr], 1, 1, 'C');
                }

                $media = 0;
                if (substr($calDados->anos, 0, 10) == 'EJA FINAIS') //***************************************************************************************************************
                {
                    $oPdf->SetFont("arial", '', 7);
                    $oPdf->Cell(5, $oConfigRelatorio->iAlturaLinha, $oAluno->iClassificacao, 1, 0, 'C');
                    $oPdf->Cell(100, $oConfigRelatorio->iAlturaLinha, $oAluno->sNome, 1);
                    // já existia o array $xfaltas, então eu utilizei para buscar as notas - Divaldo 11/10/2024
                    //			  if( $xfaltas[0]["ed72_i_valornota"] <> null )
                    if ($xfaltas[0]["ed72_i_valornota"] <> null or $xfaltas[1]["ed72_i_valornota"] <> null or $xfaltas[2]["ed72_i_valornota"] <> null or $xfaltas[3]["ed72_i_valornota"] <> null) {      // se a disciplina for por nota
                        if ($xfaltas[0]["ed72_i_valornota"] < 5) {
                            $oPdf->SetFont("arial", 'B', 7);
                        } else {
                            $oPdf->SetFont("arial", '', 7);
                        }
                        $oPdf->Cell(10, $oConfigRelatorio->iAlturaLinha, number_format($xfaltas[0]["ed72_i_valornota"], 1, ',', '.'), 1, 0, "C");        //1º

                        if ($xfaltas[1]["ed72_i_valornota"] < 5) {
                            $oPdf->SetFont("arial", 'B', 7);
                        } else {
                            $oPdf->SetFont("arial", '', 7);
                        }
                        $oPdf->Cell(10, $oConfigRelatorio->iAlturaLinha, number_format($xfaltas[1]["ed72_i_valornota"], 1, ',', '.'), 1, 0, "C");        //2º

                        if ($xfaltas[2]["ed72_i_valornota"] < 5) {
                            $oPdf->SetFont("arial", 'B', 7);
                        } else {
                            $oPdf->SetFont("arial", '', 7);
                        }
                        $oPdf->Cell(10, $oConfigRelatorio->iAlturaLinha, number_format($xfaltas[2]["ed72_i_valornota"], 1, ',', '.'), 1, 0, "C");        //3º

                        if ($xfaltas[3]["ed72_i_valornota"] < 5) {
                            $oPdf->SetFont("arial", 'B', 7);
                        } else {
                            $oPdf->SetFont("arial", '', 7);
                        }
                        $oPdf->Cell(10, $oConfigRelatorio->iAlturaLinha, number_format($xfaltas[3]["ed72_i_valornota"], 1, ',', '.'), 1, 0, "C");        //4º
                    } else if (
                        $xfaltas[0]["ed72_c_valorconceito"] <> null or $xfaltas[1]["ed72_c_valorconceito"] <> null or $xfaltas[2]["ed72_c_valorconceito"] <> null or $xfaltas[3]["ed72_c_valorconceito"] <> null
                        or trim($xfaltas[0]["ed72_c_valorconceito"]) <> '' or trim($xfaltas[1]["ed72_c_valorconceito"]) <> '' or trim($xfaltas[3]["ed72_c_valorconceito"]) <> '' or trim($xfaltas[0]["ed72_c_valorconceito"]) <> ''
                    ) {      // se a disciplina for por conceito

                        $oPdf->Cell(10, $oConfigRelatorio->iAlturaLinha, $xfaltas[0]["ed72_c_valorconceito"], 1, 0, "C");        //1º
                        $oPdf->Cell(10, $oConfigRelatorio->iAlturaLinha, $xfaltas[1]["ed72_c_valorconceito"], 1, 0, "C");        //2º
                        $oPdf->Cell(10, $oConfigRelatorio->iAlturaLinha, $xfaltas[2]["ed72_c_valorconceito"], 1, 0, "C");        //3º
                        $oPdf->Cell(10, $oConfigRelatorio->iAlturaLinha, $xfaltas[3]["ed72_c_valorconceito"], 1, 0, "C");        //4º
                    } else { // se não tiver valores lançados
                        $oPdf->Cell(10, $oConfigRelatorio->iAlturaLinha, '', 1, 0, "C");        //1º
                        $oPdf->Cell(10, $oConfigRelatorio->iAlturaLinha, '', 1, 0, "C");        //2º
                        $oPdf->Cell(10, $oConfigRelatorio->iAlturaLinha, '', 1, 0, "C");        //3º
                        $oPdf->Cell(10, $oConfigRelatorio->iAlturaLinha, '', 1, 0, "C");        //4º
                    }


                    //			  $media = ($xfaltas[0]["ed72_i_valornota"]+$xfaltas[1]["ed72_i_valornota"]+$xfaltas[3]["ed72_i_valornota"]+$xfaltas[4]["ed72_i_valornota"])/4;
                    if ($xfaltas[0]["ed72_i_valornota"] > 0) {
                        $media = $xfaltas[0]["ed72_i_valornota"];
                    }
                    if ($xfaltas[1]["ed72_i_valornota"] > 0) {
                        $media = ($xfaltas[0]["ed72_i_valornota"] + $xfaltas[1]["ed72_i_valornota"]) / 2;
                    }
                    if ($xfaltas[2]["ed72_i_valornota"] > 0) {
                        $media = ($xfaltas[0]["ed72_i_valornota"] + $xfaltas[1]["ed72_i_valornota"] + $xfaltas[2]["ed72_i_valornota"]) / 3;
                    }
                    if ($xfaltas[3]["ed72_i_valornota"] > 0) {
                        $media = ($xfaltas[0]["ed72_i_valornota"] + $xfaltas[1]["ed72_i_valornota"] + $xfaltas[2]["ed72_i_valornota"] + $xfaltas[3]["ed72_i_valornota"]) / 4;
                    }

                    $nm = explode(".", $media);
                    if (substr($nm[1], 0, 1) == '' or substr($nm[1], 0, 1) == ' ') {
                        $nm[1] = '0';
                    }
                    $mediaA = $nm[0] . "," . substr($nm[1], 0, 1);
                    if ($media < 5) {
                        $oPdf->SetFont("arial", 'B', 7);
                    } else {
                        $oPdf->SetFont("arial", '', 7);
                    }
                    $oPdf->Cell(10, $oConfigRelatorio->iAlturaLinha, $mediaA, 1, 0, "C");        //M A


                    $oPdf->Cell(10, $oConfigRelatorio->iAlturaLinha, $xfaltas[0]["ed72_i_numfaltas"], 1, 0, "C");        //1º
                    $oPdf->Cell(10, $oConfigRelatorio->iAlturaLinha, $xfaltas[1]["ed72_i_numfaltas"], 1, 0, "C");        //2º
                    $oPdf->Cell(10, $oConfigRelatorio->iAlturaLinha, $xfaltas[2]["ed72_i_numfaltas"], 1, 0, "C");        //3º
                    $oPdf->Cell(10, $oConfigRelatorio->iAlturaLinha, $xfaltas[3]["ed72_i_numfaltas"], 1, 0, "C");        //4º

                    /*
                    @autor Uemerson Santana
                    @data 10/06/2025
                    @demanda: 17356
                    */
                    if (
                        stripos(trim($oAluno->sSituacao), 'TRANSFERIDO') !== false ||
                        stripos(trim($oAluno->sSituacao), 'TROCA DE TURMA') !== false
                    ) {
                        $totalF = '';
                    } else {
                        $totalF = $xfaltas[0]["ed72_i_numfaltas"] + $xfaltas[1]["ed72_i_numfaltas"] + $xfaltas[2]["ed72_i_numfaltas"] + $xfaltas[3]["ed72_i_numfaltas"];
                    }

                    $oPdf->Cell(20, $oConfigRelatorio->iAlturaLinha, $totalF, 1, 1, "C");        //Total de Faltas

                    $cfreq = (($diasletivos - ($xfaltas[0]["ed72_i_numfaltas"] + $xfaltas[1]["ed72_i_numfaltas"] + $xfaltas[2]["ed72_i_numfaltas"] + $xfaltas[3]["ed72_i_numfaltas"])) / $diasletivos) * 100;
                    $cfreq = number_format($cfreq, 0, ",", ",");
                    $cfreq = round($cfreq);

                    //			  $oPdf->Cell(20, $oConfigRelatorio->iAlturaLinha, $cfreq, 1, 1, 'C');    //Frequência %
                }      //***************************************************************************************************************
                $media = 0;
                if (substr($calDados->anos, 0, 12) == 'EJA INICIAIS') //***************************************************************************************************************
                {
                    $oPdf->SetFont("arial", '', 7);
                    $oPdf->Cell(5, $oConfigRelatorio->iAlturaLinha, $oAluno->iClassificacao, 1, 0, 'C');
                    $oPdf->Cell(100, $oConfigRelatorio->iAlturaLinha, $oAluno->sNome, 1);
                    // já existia o array $xfaltas, então eu utilizei para buscar as notas - Divaldo 11/10/2024
                    if ($xfaltas[0]["ed72_i_valornota"] <> null or $xfaltas[1]["ed72_i_valornota"] <> null or $xfaltas[2]["ed72_i_valornota"] <> null or $xfaltas[3]["ed72_i_valornota"] <> null) {
                        $oPdf->Cell(10, $oConfigRelatorio->iAlturaLinha, number_format($xfaltas[0]["ed72_i_valornota"], 1, ',', '.'), 1, 0, "C");        //1º
                        $oPdf->Cell(10, $oConfigRelatorio->iAlturaLinha, number_format($xfaltas[1]["ed72_i_valornota"], 1, ',', '.'), 1, 0, "C");        //2º
                        $oPdf->Cell(10, $oConfigRelatorio->iAlturaLinha, number_format($xfaltas[2]["ed72_i_valornota"], 1, ',', '.'), 1, 0, "C");        //3º
                        $oPdf->Cell(10, $oConfigRelatorio->iAlturaLinha, number_format($xfaltas[3]["ed72_i_valornota"], 1, ',', '.'), 1, 0, "C");        //4º
                    } else if (
                        $xfaltas[0]["ed72_c_valorconceito"] <> null or $xfaltas[1]["ed72_c_valorconceito"] <> null or $xfaltas[2]["ed72_c_valorconceito"] <> null or $xfaltas[3]["ed72_c_valorconceito"] <> null
                        or trim($xfaltas[0]["ed72_c_valorconceito"]) <> '' or trim($xfaltas[1]["ed72_c_valorconceito"]) <> '' or trim($xfaltas[3]["ed72_c_valorconceito"]) <> '' or trim($xfaltas[0]["ed72_c_valorconceito"]) <> ''
                    ) {
                        $oPdf->Cell(10, $oConfigRelatorio->iAlturaLinha, $xfaltas[0]["ed72_c_valorconceito"], 1, 0, "C");        //1º
                        $oPdf->Cell(10, $oConfigRelatorio->iAlturaLinha, $xfaltas[1]["ed72_c_valorconceito"], 1, 0, "C");        //2º
                        $oPdf->Cell(10, $oConfigRelatorio->iAlturaLinha, $xfaltas[2]["ed72_c_valorconceito"], 1, 0, "C");        //3º
                        $oPdf->Cell(10, $oConfigRelatorio->iAlturaLinha, $xfaltas[3]["ed72_c_valorconceito"], 1, 0, "C");        //4º
                    } else { // se não tiver valores lançados
                        $oPdf->Cell(10, $oConfigRelatorio->iAlturaLinha, '', 1, 0, "C");        //1º
                        $oPdf->Cell(10, $oConfigRelatorio->iAlturaLinha, '', 1, 0, "C");        //2º
                        $oPdf->Cell(10, $oConfigRelatorio->iAlturaLinha, '', 1, 0, "C");        //3º
                        $oPdf->Cell(10, $oConfigRelatorio->iAlturaLinha, '', 1, 0, "C");        //4º
                    }


                    if ($xfaltas[0]["ed72_i_valornota"] > 0) {
                        $media = $xfaltas[0]["ed72_i_valornota"];
                    }
                    if ($xfaltas[1]["ed72_i_valornota"] > 0) {
                        $media = ($xfaltas[0]["ed72_i_valornota"] + $xfaltas[1]["ed72_i_valornota"]) / 2;
                    }
                    if ($xfaltas[2]["ed72_i_valornota"] > 0) {
                        $media = ($xfaltas[0]["ed72_i_valornota"] + $xfaltas[1]["ed72_i_valornota"] + $xfaltas[2]["ed72_i_valornota"]) / 3;
                    }
                    if ($xfaltas[3]["ed72_i_valornota"] > 0) {
                        $media = ($xfaltas[0]["ed72_i_valornota"] + $xfaltas[1]["ed72_i_valornota"] + $xfaltas[2]["ed72_i_valornota"] + $xfaltas[3]["ed72_i_valornota"]) / 4;
                    }
                    $nm = explode(".", $media);
                    if (substr($nm[1], 0, 1) == '' or substr($nm[1], 0, 1) == ' ') {
                        $nm[1] = '0';
                    }
                    $mediaA = $nm[0] . "," . substr($nm[1], 0, 1);

                    $oPdf->Cell(10, $oConfigRelatorio->iAlturaLinha, $mediaA, 1, 0, "C");        //M A

                    $oPdf->Cell(10, $oConfigRelatorio->iAlturaLinha, $xfaltas[0]["ed72_i_numfaltas"], 1, 0, "C");        //1º
                    $oPdf->Cell(10, $oConfigRelatorio->iAlturaLinha, $xfaltas[1]["ed72_i_numfaltas"], 1, 0, "C");        //2º
                    $oPdf->Cell(10, $oConfigRelatorio->iAlturaLinha, $xfaltas[2]["ed72_i_numfaltas"], 1, 0, "C");        //3º
                    $oPdf->Cell(10, $oConfigRelatorio->iAlturaLinha, $xfaltas[3]["ed72_i_numfaltas"], 1, 0, "C");        //4º

                    /*
                    @autor Uemerson Santana
                    @data 10/06/2025
                    @demanda: 17356
                    */
                    if (
                        stripos(trim($oAluno->sSituacao), 'TRANSFERIDO') !== false ||
                        stripos(trim($oAluno->sSituacao), 'TROCA DE TURMA') !== false
                    ) {
                        $totalF = '';
                    } else {
                        $totalF = $xfaltas[0]["ed72_i_numfaltas"] + $xfaltas[1]["ed72_i_numfaltas"] + $xfaltas[2]["ed72_i_numfaltas"] + $xfaltas[3]["ed72_i_numfaltas"];
                    }

                    $oPdf->Cell(20, $oConfigRelatorio->iAlturaLinha, $totalF, 1, 1, "C");        //Total de Faltas

                    $cfreq = (($diasletivos - ($xfaltas[0]["ed72_i_numfaltas"] + $xfaltas[1]["ed72_i_numfaltas"] + $xfaltas[2]["ed72_i_numfaltas"] + $xfaltas[3]["ed72_i_numfaltas"])) / $diasletivos) * 100;
                    $cfreq = number_format($cfreq, 0, ",", ",");
                    $cfreq = round($cfreq);

                    //			  $oPdf->Cell(20, $oConfigRelatorio->iAlturaLinha, $cfreq, 1, 1, 'C');    //Frequência %
                }      //***************************************************************************************************************


                if (substr($calDados->anos, 0, 13) == 'ANOS INICIAIS') //******************************** Anos iniciais ******************************************
                {
                    $oPdf->SetFont("arial", '', 7);
                    $oPdf->Cell(5, $oConfigRelatorio->iAlturaLinha, $oAluno->iClassificacao, 1, 0, 'C');
                    $oPdf->Cell(100, $oConfigRelatorio->iAlturaLinha, $oAluno->sNome, 1);
                    if ($xfaltas[0]["ed72_i_valornota"] <> null) {
                        $oPdf->Cell(10, $oConfigRelatorio->iAlturaLinha, number_format($xfaltas[0]["ed72_i_valornota"], 1, ',', '.'), 1, 0, 'C');
                        $oPdf->Cell(10, $oConfigRelatorio->iAlturaLinha, number_format($xfaltas[1]["ed72_i_valornota"], 1, ',', '.'), 1, 0, 'C');
                        $oPdf->Cell(10, $oConfigRelatorio->iAlturaLinha, number_format($xfaltas[2]["ed72_i_valornota"], 1, ',', '.'), 1, 0, 'C');

                        if ($xfaltas[0]["ed72_i_valornota"] > 0 and $xfaltas[1]["ed72_i_valornota"] == null) {
                            $mediaI = $xfaltas[0]["ed72_i_valornota"];
                        }
                        if ($xfaltas[1]["ed72_i_valornota"] > 0 and $xfaltas[2]["ed72_i_valornota"] == null) {
                            $mediaI = ($xfaltas[0]["ed72_i_valornota"] + $xfaltas[1]["ed72_i_valornota"]) / 2;
                        }
                        if ($xfaltas[2]["ed72_i_valornota"] > 0) {
                            $mediaI = ($xfaltas[0]["ed72_i_valornota"] + $xfaltas[1]["ed72_i_valornota"] + $xfaltas[2]["ed72_i_valornota"]) / 3;
                        }

                        $nm = explode(".", $mediaI);
                        if (substr($nm[1], 0, 1) == '' or substr($nm[1], 0, 1) == ' ') {
                            $nm[1] = '0';
                        }
                        $mediaI = $nm[0] . "," . substr($nm[1], 0, 1);
                        $oPdf->Cell(10, $oConfigRelatorio->iAlturaLinha, $mediaI, 1, 0, 'C');
                    } else {
                        $oPdf->Cell(10, $oConfigRelatorio->iAlturaLinha, $xfaltas[0]["ed72_c_valorconceito"], 1, 0, 'C');
                        $oPdf->Cell(10, $oConfigRelatorio->iAlturaLinha, $xfaltas[1]["ed72_c_valorconceito"], 1, 0, 'C');
                        $oPdf->Cell(10, $oConfigRelatorio->iAlturaLinha, $xfaltas[2]["ed72_c_valorconceito"], 1, 0, 'C');
                        $oPdf->Cell(10, $oConfigRelatorio->iAlturaLinha, '', 1, 0, 'C');
                    }
                    $xfaltas2 = voltaFaltas2($oAluno->iMatricula, $oTurmaEtapa->sTurma);
                    $oPdf->Cell(10, $oConfigRelatorio->iAlturaLinha, $xfaltas2[0]["ed72_i_numfaltas"], 1, 0, 'C');
                    $oPdf->Cell(10, $oConfigRelatorio->iAlturaLinha, $xfaltas2[1]["ed72_i_numfaltas"], 1, 0, 'C');
                    $oPdf->Cell(10, $oConfigRelatorio->iAlturaLinha, $xfaltas2[2]["ed72_i_numfaltas"], 1, 0, 'C');
                    $oPdf->Cell(20, $oConfigRelatorio->iAlturaLinha, $xfaltas2[0]["ed72_i_numfaltas"] + $xfaltas2[1]["ed72_i_numfaltas"] + $xfaltas2[2]["ed72_i_numfaltas"], 1, 0, 'C');

                    $cfreq = (($diasletivos - ($xfaltas2[0]["ed72_i_numfaltas"] + $xfaltas2[1]["ed72_i_numfaltas"] + $xfaltas2[2]["ed72_i_numfaltas"])) / $diasletivos) * 100;
                    $cfreq = number_format($cfreq, 0, ",", ",");
                    $cfreq = round($cfreq);
                    $oPdf->Cell(20, $oConfigRelatorio->iAlturaLinha, $cfreq, 1, 1, 'C');
                }


                //$oPdf->Cell($oConfigRelatorio->iColunaCodigo, $oConfigRelatorio->iAlturaLinha, $oAluno->iCodigoAluno, 1, 0, 'C');



                /**
                 * Imprime colunas de avaliação vazia
                 */
                if ($iTotalDisciplina < $oConfigRelatorio->iMaximoDisciplinaPagina) {
                    //imprimeQuadroAvaliacaoVazio ($oPdf, $oTurmaEtapa, $oTurmaEtapa->iTotalDisciplina, $oConfigRelatorio, false);
                }
                //$oPdf->Cell($oConfigRelatorio->iColunaNumero, $oConfigRelatorio->iAlturaLinha, $oAluno->iTotalFaltas, 1, 1, 'C');

            } //foreach do aluno //bbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbb

            /**
             * Imprime linhas em branco para fechar o total de aluno por página
             */
            //	$oPdf->getX();
            /*
                if ($iAlunosImpressoPagina < $oConfigRelatorio->iAlunosPorPagina) {

                for ($i = $iAlunosImpressoPagina; $i < $oConfigRelatorio->iAlunosPorPagina; $i++) {
                    imprimeLinhaEmBranco($oPdf, $oConfigRelatorio, $oTurmaEtapa);
                }
                }
            */

            montaQuadroLegendaAssinatura($oPdf, $oConfigRelatorio, $oTurmaEtapa->lUltimoPeriodo, $oTurmaEtapa, $oTurmaSelecionada->iTurma, $oTurmaSelecionada->iEtapa, $regenc, $assAdicion, $assAtivid, $disciplina);
            $lPrimeiraPagina = true;
        }
    }
    unset($oTurmaEtapa);
} //aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa


// /**
//  * Retorna o termo final de acordo com o Resultado
//  * @param string $sResultadoFinal
//  * @return string
//  */
// function termoFinal($sResultadoFinal, $lDisciplina = false, $aTermos = null)
// {
//     $sSituacaoFinal = '';
//     switch (trim($sResultadoFinal)) {
//         case 'A':
//             $sSituacaoFinal = 'APR';
//             if (!empty($aTermos)) {
//                 $sSituacaoFinal = $this->getTermoByReferencia($aTermos, $sResultadoFinal);
//             }
//             break;

//         case 'D':
//             $sSituacaoFinal = 'AP/DP';
//             if ($lDisciplina) {
//                 $sSituacaoFinal = 'APR*';
//                 $this->lAlunoTeveAprovacaoComProgressao = true;
//             }
//             break;

//         case 'R':
//             $sSituacaoFinal = 'REP';
//             if (!empty($aTermos)) {
//                 foreach ($aTermos as $oTermo) {
//                     if ($oTermo->sReferencia == 'R') {
//                         $sSituacaoFinal = $this->getTermoByReferencia($aTermos, $sResultadoFinal);
//                         ;
//                         break;
//                     }
//                 }
//             }
//             break;
//     }

//     return $sSituacaoFinal;
// }

/**
 * Renderiza quadro das legendas
 * @param FPDF $oPdf
 * @param stdClass $oConfigRelatorio
 */
function montaQuadroLegendaAssinatura(FPDF $oPdf, $oConfigRelatorio, $lUltimoPeriodo, $oTurmaEtapa, $turma, $etapa, $regencia, $assAdic, $Ativ, $disciplina)
{

    //testa($oTurmaEtapa);
    /**
     * Soma com base na configuração do layout a largura do quadro
     */;
    $iLarguraQuadro  = $oConfigRelatorio->iLarguraTotalDisciplinas;
    $iLarguraQuadro += $oConfigRelatorio->iColunaCodigo;
    $iLarguraQuadro += $oTurmaEtapa->iColunaNome;
    $iLarguraQuadro += ($oConfigRelatorio->iColunaNumero * 3);
    if (!$lUltimoPeriodo) {
        $iLarguraQuadro += $oConfigRelatorio->iColunaPareceres;
    }

    $iXInicial = $oPdf->GetX();
    $iYInicial = $oPdf->GetY();
    $oPdf->Rect($oPdf->GetX(), $oPdf->GetY(), $iLarguraQuadro, 21);

    if ($oConfigRelatorio->comLegenda) {

        $sLegendasCabeçalho  = "Nº: Número da classificação do aluno;  S: Saída;  Ft.: Nº de faltas na disciplina;";
        $sLegendasCabeçalho .= "  NT: Nota;  NP: Nota parcial;  TF: Total de faltas no período.";

        $sLegendasSituacao  = montaLegendaSituacoes();

        $oPdf->SetY($iYInicial + 1);
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
    $oPdf->SetY($iYInicial + 2);
    //  $oPdf->Cell($iXInicial+100, 4, "","LR",0,"L");
    //  $oPdf->Cell(100, 4, "","R",1,"L");

    $oPdf->Cell($iXInicial + 97, 3, "Obs: ", "RB", 0, "L");
    $oPdf->Cell(40, 3, "", "B", 0);
    $oPdf->Cell(40, 3, "Encerrado em: ___/___/_____", "B", 0, "L");
    $oPdf->Cell(40, 3, "Aulas previstas: _____________", "B", 0, "L");
    $oPdf->Cell(54, 3, "Aulas dadas: _____________", "B", 1, "L");

    $sqlreg = "
            select
			ed59_i_codigo
			from
			regencia
			inner join disciplina          on disciplina.ed12_i_codigo = regencia.ed59_i_disciplina
			inner join caddisciplina       on ed232_i_codigo           = ed12_i_caddisciplina
			inner join turma               on turma.ed57_i_codigo      = regencia.ed59_i_turma
			inner join turmaserieregimemat on ed220_i_turma            = ed57_i_codigo
			inner join serieregimemat      on ed223_i_codigo           = ed220_i_serieregimemat
			inner join serie               on ed11_i_codigo            = ed223_i_serie
			inner join calendario          on ed52_i_codigo            = ed57_i_calendario
			WHERE
			ed232_c_descr        =  '" . $disciplina . "'
			AND ed57_i_codigo    = {$turma}
			AND ed59_c_freqglob != 'F'
			AND ed223_i_serie    = ed59_i_serie";
    $sql12 = pg_query($sqlreg);
    $regenciaD  = db_utils::fieldsMemory($sql12, 0);



    $doc  = "select
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
          ed232_c_descr  = '" . $disciplina . "'
		  and
		  ed58_ativo is true
		  and
		  ed59_i_codigo = " . $regenciaD->ed59_i_codigo;

    // verificar porque o regente esta retornando vazio
    $sql1 = pg_query($doc);
    $prof = '';

    if (pg_num_rows($sql1) > 1) {
        for ($x = 0; $x < pg_num_rows($sql1); $x++) {
            if ($x > 0) {
                $prof .= "-";
            }
            $reg  = db_utils::fieldsMemory($sql1, $x);
            $prof .=  $reg->z01_nome;
        }
    } else {

        $reg = db_utils::fieldsMemory($sql1, 0);
        $prof =  $reg->z01_nome;
    }
    /*
if( $disciplina == 'MATEMÁTICA' )
{
	$arq = fopen("/dados/www/homologacao.epdvr.com.br/backup/regente.txt","w+");
	fwrite($arq, $prof);
	fwrite($arq,"\r\n");
	fclose($arq);
}
*/

    $esc  = "select
	  	  z01_numcgm,
		  z01_nome
		  from
		  rechumano
		  inner join rechumanopessoal on ed284_i_rechumano = ed20_i_codigo
		  inner join rhpessoal        on rh01_regist       = ed284_i_rhpessoal
		  inner join cgm              on z01_numcgm        = rh01_numcgm
		  inner join escoladiretor    on ed254_i_rechumano = ed20_i_codigo
		  where
          ed254_i_escola = " . db_getsession("DB_coddepto");
    $sql2 = pg_query($esc);
    $dir  = db_utils::fieldsMemory($sql2, 0);



    $oPdf->SetFont("arial", 'b', 5);
    $oPdf->Cell($iXInicial + 97, 4, "", "RB", 1, "L");
    //  $oPdf->Cell(30, 4, "","",1);
    $oPdf->Cell($iXInicial + 97, 4, "", "RB", 1, "L");
    //  $oPdf->Cell(30, 4, "","",1);

    $oPdf->Line($iXInicial + 110, $iYInicial + 12, $iXInicial + 160, $iYInicial + 12);
    $oPdf->Line($iXInicial + 165, $iYInicial + 12, $iXInicial + 215, $iYInicial + 12);
    $oPdf->Line($iXInicial + 220, $iYInicial + 12, $iXInicial + 270, $iYInicial + 12);


    $oPdf->Cell($iXInicial + 97, 4, "", "RB", 0, "L");

    $profs = explode('-', $prof);
    if (strlen($prof) > 37) {
        $oPdf->SetFont("arial", 'b', 4);
    }
    $oPdf->Cell(60, 4, $prof, "", 0, "C");
    $oPdf->SetFont("arial", 'b', 5);
    $oPdf->Cell(50, 4,      $assAdic, "", 0, "C");
    $oPdf->Cell(50, 4, $dir->z01_nome, "", 1, "C");
    $oPdf->Cell($iXInicial + 97, 4, "", "RB", 0, "L");
    /*
  if( $profs[1] )
  {
      $oPdf->Cell(60, 4,$profs[1],"",1,"C");
      $oPdf->Cell($iXInicial+97, 4, "","RB",0,"L");
  }
*/
    $oPdf->Cell(60, 4, "             Regente               ", "", 0, "C");
    $oPdf->Cell(50, 4, $Ativ, "", 0, "C");
    $oPdf->Cell(50, 4, "         Diretor(a) Geral       	 ", "", 1, "C");
}


/**
 * Realiza o calculo, em tempo de execução, de variáveis de controle
 * @param FPDF     $oPdf
 * @param stdClass $oTurmaEtapa
 * @param stdClass $oConfigRelatorio
 * @param integer  $iPagina
 */
function calculaTamanhoDeCelulasDinamicas(FPDF $oPdf, $oTurmaEtapa, $oConfigRelatorio, $iPagina)
{

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
function adicionaHeader(FPDF $oPdf, $oTurmaEtapa, $oConfigRelatorio, $iPagina)
{
    $sql = "
         select
		 ed52_c_descr as anos
		 from
		 calendario
		 where
		 ed52_i_codigo = " . $_GET["calendario"];
    $resultado = db_query($sql);
    $calDados  = db_utils::fieldsMemory($resultado, 0);

    $oPdf->AddPage();
    if (substr($calDados->anos, 0, 11) == 'ANOS FINAIS') {
        $oPdf->SetFont("arial", 'b', 12);
        $oPdf->Cell($oPdf->w, $oConfigRelatorio->iAlturaLinha, "RESUMO ANUAL", 0, 1, "C");
        $oPdf->ln();

        $oPdf->SetFont("arial", 'b', 7);

        $iEixoY = $oPdf->GetY();
        $oPdf->Cell(105, $oConfigRelatorio->iAlturaLinha, '', 0, 0, "C");
        $oPdf->Cell(80, $oConfigRelatorio->iAlturaLinha, 'RESULTADOS', 1, 0, "C");
        $oPdf->Cell(40, $oConfigRelatorio->iAlturaLinha, 'FALTAS', 1, 0, "C");
        $oPdf->Cell(10, $oConfigRelatorio->iAlturaLinha, 'Freq', 1, 0, "C");
        $oPdf->ln();

        $oPdf->Cell(5, $oConfigRelatorio->iAlturaLinha, 'Nº', 1, 0, 'C');
        $oPdf->Cell(100, $oConfigRelatorio->iAlturaLinha, 'Nome do Aluno', 1);

        $oPdf->Cell(10, $oConfigRelatorio->iAlturaLinha, '1º', 1, 0, "C");
        $oPdf->Cell(10, $oConfigRelatorio->iAlturaLinha, '2º', 1, 0, "C");
        $oPdf->Cell(10, $oConfigRelatorio->iAlturaLinha, 'REC S', 1, 0, "C");
        $oPdf->Cell(10, $oConfigRelatorio->iAlturaLinha, '3º', 1, 0, "C");
        $oPdf->Cell(10, $oConfigRelatorio->iAlturaLinha, '4º', 1, 0, "C");
        $oPdf->Cell(10, $oConfigRelatorio->iAlturaLinha, 'M A', 1, 0, "C");
        $oPdf->Cell(10, $oConfigRelatorio->iAlturaLinha, 'REC F', 1, 0, "C");
        $oPdf->Cell(10, $oConfigRelatorio->iAlturaLinha, 'M F', 1, 0, "C");


        $oPdf->Cell(10, $oConfigRelatorio->iAlturaLinha, '1º', 1, 0, "C");
        $oPdf->Cell(10, $oConfigRelatorio->iAlturaLinha, '2º', 1, 0, "C");
        $oPdf->Cell(10, $oConfigRelatorio->iAlturaLinha, '3º', 1, 0, "C");
        $oPdf->Cell(10, $oConfigRelatorio->iAlturaLinha, '4º', 1, 0, "C");

        $oPdf->Cell(10, $oConfigRelatorio->iAlturaLinha, '%', 1, 0, "C");

        $oPdf->Cell(20, $oConfigRelatorio->iAlturaLinha, 'Total de Faltas', 1, 0, "C");
        $oPdf->Cell(30, $oConfigRelatorio->iAlturaLinha, 'Resultado Final', 1, 0, "C");
    }

    if (substr($calDados->anos, 0, 10) == 'EJA FINAIS') {
        $oPdf->SetFont("arial", 'b', 12);
        $oPdf->Cell($oPdf->w, $oConfigRelatorio->iAlturaLinha, "RESUMO ANUAL", 0, 1, "C");
        $oPdf->ln();

        $oPdf->SetFont("arial", 'b', 7);

        $iEixoY = $oPdf->GetY();
        $oPdf->Cell(105, $oConfigRelatorio->iAlturaLinha, '', 0, 0, "C");
        $oPdf->Cell(50, $oConfigRelatorio->iAlturaLinha, 'RESULTADOS', 1, 0, "C");
        $oPdf->Cell(40, $oConfigRelatorio->iAlturaLinha, 'FALTAS', 1, 0, "C");
        $oPdf->ln();

        $oPdf->Cell(5, $oConfigRelatorio->iAlturaLinha, 'Nº', 1, 0, 'C');
        $oPdf->Cell(100, $oConfigRelatorio->iAlturaLinha, 'Nome do Aluno', 1);

        $oPdf->Cell(10, $oConfigRelatorio->iAlturaLinha, '1º', 1, 0, "C");
        $oPdf->Cell(10, $oConfigRelatorio->iAlturaLinha, '2º', 1, 0, "C");
        $oPdf->Cell(10, $oConfigRelatorio->iAlturaLinha, '3º', 1, 0, "C");
        $oPdf->Cell(10, $oConfigRelatorio->iAlturaLinha, '4º', 1, 0, "C");
        $oPdf->Cell(10, $oConfigRelatorio->iAlturaLinha, 'M F', 1, 0, "C");


        $oPdf->Cell(10, $oConfigRelatorio->iAlturaLinha, '1º', 1, 0, "C");
        $oPdf->Cell(10, $oConfigRelatorio->iAlturaLinha, '2º', 1, 0, "C");
        $oPdf->Cell(10, $oConfigRelatorio->iAlturaLinha, '3º', 1, 0, "C");
        $oPdf->Cell(10, $oConfigRelatorio->iAlturaLinha, '4º', 1, 0, "C");

        $oPdf->Cell(20, $oConfigRelatorio->iAlturaLinha, 'Total de Faltas', 1, 0, "C");
        //	  $oPdf->Cell(20, $oConfigRelatorio->iAlturaLinha, 'Frequência %', 1, 0, "C");

    }

    if (substr($calDados->anos, 0, 12) == 'EJA INICIAIS') {
        $oPdf->SetFont("arial", 'b', 12);
        $oPdf->Cell($oPdf->w, $oConfigRelatorio->iAlturaLinha, "RESUMO ANUAL", 0, 1, "C");
        $oPdf->ln();

        $oPdf->SetFont("arial", 'b', 7);

        $iEixoY = $oPdf->GetY();
        $oPdf->Cell(105, $oConfigRelatorio->iAlturaLinha, '', 0, 0, "C");
        $oPdf->Cell(50, $oConfigRelatorio->iAlturaLinha, 'RESULTADOS', 1, 0, "C");
        $oPdf->Cell(40, $oConfigRelatorio->iAlturaLinha, 'FALTAS', 1, 0, "C");
        $oPdf->ln();

        $oPdf->Cell(5, $oConfigRelatorio->iAlturaLinha, 'Nº', 1, 0, 'C');
        $oPdf->Cell(100, $oConfigRelatorio->iAlturaLinha, 'Nome do Aluno', 1);

        $oPdf->Cell(10, $oConfigRelatorio->iAlturaLinha, '1º', 1, 0, "C");
        $oPdf->Cell(10, $oConfigRelatorio->iAlturaLinha, '2º', 1, 0, "C");
        $oPdf->Cell(10, $oConfigRelatorio->iAlturaLinha, '3º', 1, 0, "C");
        $oPdf->Cell(10, $oConfigRelatorio->iAlturaLinha, '4º', 1, 0, "C");
        $oPdf->Cell(10, $oConfigRelatorio->iAlturaLinha, 'M F', 1, 0, "C");


        $oPdf->Cell(10, $oConfigRelatorio->iAlturaLinha, '1º', 1, 0, "C");
        $oPdf->Cell(10, $oConfigRelatorio->iAlturaLinha, '2º', 1, 0, "C");
        $oPdf->Cell(10, $oConfigRelatorio->iAlturaLinha, '3º', 1, 0, "C");
        $oPdf->Cell(10, $oConfigRelatorio->iAlturaLinha, '4º', 1, 0, "C");

        $oPdf->Cell(20, $oConfigRelatorio->iAlturaLinha, 'Total de Faltas', 1, 0, "C");
        //	  $oPdf->Cell(20, $oConfigRelatorio->iAlturaLinha, 'Frequência %', 1, 0, "C");

    }

    if (substr($calDados->anos, 0, 13) == 'ANOS INICIAIS') {
        $oPdf->SetFont("arial", 'b', 12);
        $oPdf->Cell($oPdf->w, $oConfigRelatorio->iAlturaLinha, "RESUMO ANUAL", 0, 1, "C");
        $oPdf->ln();

        $oPdf->SetFont("arial", 'b', 7);

        $iEixoY = $oPdf->GetY();
        $oPdf->Cell(105, $oConfigRelatorio->iAlturaLinha, '', 0, 0, "C");
        $oPdf->Cell(40, $oConfigRelatorio->iAlturaLinha, 'RESULTADOS', 1, 0, "C");
        $oPdf->Cell(30, $oConfigRelatorio->iAlturaLinha, 'FALTAS por Trimestre', 1, 0, "C");
        $oPdf->ln();

        $oPdf->Cell(5, $oConfigRelatorio->iAlturaLinha, 'Nº', 1, 0, 'C');
        $oPdf->Cell(100, $oConfigRelatorio->iAlturaLinha, 'Nome do Aluno', 1);
        $oPdf->Cell(10, $oConfigRelatorio->iAlturaLinha, '1º', 1, 0, "C");
        $oPdf->Cell(10, $oConfigRelatorio->iAlturaLinha, '2º', 1, 0, "C");
        $oPdf->Cell(10, $oConfigRelatorio->iAlturaLinha, '3º', 1, 0, "C");
        $oPdf->Cell(10, $oConfigRelatorio->iAlturaLinha, 'Média', 1, 0, "C");
        $oPdf->Cell(10, $oConfigRelatorio->iAlturaLinha, '1º', 1, 0, "C");
        $oPdf->Cell(10, $oConfigRelatorio->iAlturaLinha, '2º', 1, 0, "C");
        $oPdf->Cell(10, $oConfigRelatorio->iAlturaLinha, '3º', 1, 0, "C");
        $oPdf->Cell(20, $oConfigRelatorio->iAlturaLinha, 'Total de Faltas', 1, 0, "C");
        $oPdf->Cell(20, $oConfigRelatorio->iAlturaLinha, 'Frequência %', 1, 0, "C");
    }


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
function imprimeQuadroAvaliacaoVazio(
    FPDF $oPdf,
    $oTurmaEtapa,
    $iTotalDisciplina,
    $oConfigRelatorio,
    $lPrimeiraLinha = true
) {

    for ($i = $iTotalDisciplina; $i < $oConfigRelatorio->iMaximoDisciplinaPagina; $i++) {

        if ($lPrimeiraLinha) {

            $oPdf->Cell($oTurmaEtapa->iLarguraDisciplina, $oConfigRelatorio->iAlturaLinha, '', 1, 0, "C");
            imprimeLinhaSeparadora($oPdf, $oConfigRelatorio);
        } else {

            $iLarguraAvaliacao = $oTurmaEtapa->iLarguraDisciplina - 5;

            if ($oTurmaEtapa->sFormaAvaliacao == 'NOTA' && $oConfigRelatorio->lCalculaMediaParcial) {

                $oPdf->Cell($iLarguraAvaliacao / 2, $oConfigRelatorio->iAlturaLinha, '', 1);
                $oPdf->Cell($iLarguraAvaliacao / 2, $oConfigRelatorio->iAlturaLinha, '', 1);
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
function imprimeLinhaSeparadora(FPDF $oPdf, $oConfigRelatorio)
{

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
function imprimeCelulasParecer(FPDF $oPdf, $iLarguraCelulaParecer, $oConfigRelatorio)
{

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
function imprimeLinhaEmBranco(FPDF $oPdf, $oConfigRelatorio, $oTurmaEtapa)
{
    $sql = "
         select
		 ed52_c_descr as anos
		 from
		 calendario
		 where
		 ed52_i_codigo = " . $_GET["calendario"];
    $resultado = db_query($sql);
    $calDados  = db_utils::fieldsMemory($resultado, 0);


    $oPdf->AddPage();
    if (substr($calDados->anos, 0, 11) == 'ANOS FINAIS') {
        $oPdf->SetFont("arial", 'b', 7);
        $oPdf->Cell(5, $oConfigRelatorio->iAlturaLinha, '', 1, 0, 'C');
        $oPdf->Cell(100, $oConfigRelatorio->iAlturaLinha, '', 1);

        $oPdf->Cell(10, $oConfigRelatorio->iAlturaLinha, '', 1, 0, "C");
        $oPdf->Cell(10, $oConfigRelatorio->iAlturaLinha, '', 1, 0, "C");
        $oPdf->Cell(10, $oConfigRelatorio->iAlturaLinha, '', 1, 0, "C");
        $oPdf->Cell(10, $oConfigRelatorio->iAlturaLinha, '', 1, 0, "C");
        $oPdf->Cell(10, $oConfigRelatorio->iAlturaLinha, '', 1, 0, "C");
        $oPdf->Cell(10, $oConfigRelatorio->iAlturaLinha, '', 1, 0, "C");
        $oPdf->Cell(10, $oConfigRelatorio->iAlturaLinha, '', 1, 0, "C");



        $oPdf->Cell(10, $oConfigRelatorio->iAlturaLinha, '', 1, 0, "C");
        $oPdf->Cell(10, $oConfigRelatorio->iAlturaLinha, '', 1, 0, "C");
        $oPdf->Cell(10, $oConfigRelatorio->iAlturaLinha, '', 1, 0, "C");
        $oPdf->Cell(10, $oConfigRelatorio->iAlturaLinha, '', 1, 0, "C");

        $oPdf->Cell(20, $oConfigRelatorio->iAlturaLinha, '', 1, 0, "C");
        $oPdf->Cell(20, $oConfigRelatorio->iAlturaLinha, '', 1, 0, "C");
    }

    if (substr($calDados->anos, 0, 10) == 'EJA FINAIS') {
        $oPdf->SetFont("arial", 'b', 7);
        $oPdf->Cell(5, $oConfigRelatorio->iAlturaLinha, '', 1, 0, 'C');
        $oPdf->Cell(100, $oConfigRelatorio->iAlturaLinha, '', 1);

        $oPdf->Cell(10, $oConfigRelatorio->iAlturaLinha, '', 1, 0, "C");
        $oPdf->Cell(10, $oConfigRelatorio->iAlturaLinha, '', 1, 0, "C");
        $oPdf->Cell(10, $oConfigRelatorio->iAlturaLinha, '', 1, 0, "C");
        $oPdf->Cell(10, $oConfigRelatorio->iAlturaLinha, '', 1, 0, "C");

        $oPdf->Cell(10, $oConfigRelatorio->iAlturaLinha, '', 1, 0, "C");

        $oPdf->Cell(10, $oConfigRelatorio->iAlturaLinha, '', 1, 0, "C");
        $oPdf->Cell(10, $oConfigRelatorio->iAlturaLinha, '', 1, 0, "C");
        $oPdf->Cell(10, $oConfigRelatorio->iAlturaLinha, '', 1, 0, "C");
        $oPdf->Cell(10, $oConfigRelatorio->iAlturaLinha, '', 1, 0, "C");

        $oPdf->Cell(20, $oConfigRelatorio->iAlturaLinha, '', 1, 0, "C");
        $oPdf->Cell(20, $oConfigRelatorio->iAlturaLinha, '', 1, 0, "C");
    }

    if (substr($calDados->anos, 0, 12) == 'EJA INICIAIS') {
        $oPdf->SetFont("arial", 'b', 7);
        $oPdf->Cell(5, $oConfigRelatorio->iAlturaLinha, '', 1, 0, 'C');
        $oPdf->Cell(100, $oConfigRelatorio->iAlturaLinha, '', 1);

        $oPdf->Cell(10, $oConfigRelatorio->iAlturaLinha, '', 1, 0, "C");
        $oPdf->Cell(10, $oConfigRelatorio->iAlturaLinha, '', 1, 0, "C");
        $oPdf->Cell(10, $oConfigRelatorio->iAlturaLinha, '', 1, 0, "C");
        $oPdf->Cell(10, $oConfigRelatorio->iAlturaLinha, '', 1, 0, "C");

        $oPdf->Cell(10, $oConfigRelatorio->iAlturaLinha, '', 1, 0, "C");

        $oPdf->Cell(10, $oConfigRelatorio->iAlturaLinha, '', 1, 0, "C");
        $oPdf->Cell(10, $oConfigRelatorio->iAlturaLinha, '', 1, 0, "C");
        $oPdf->Cell(10, $oConfigRelatorio->iAlturaLinha, '', 1, 0, "C");
        $oPdf->Cell(10, $oConfigRelatorio->iAlturaLinha, '', 1, 0, "C");

        $oPdf->Cell(20, $oConfigRelatorio->iAlturaLinha, '', 1, 0, "C");
        $oPdf->Cell(20, $oConfigRelatorio->iAlturaLinha, '', 1, 0, "C");
    }

    if (substr($calDados->anos, 0, 13) == 'ANOS INICIAIS') {
        $oPdf->SetFont("arial", 'b', 7);
        $iEixoY = $oPdf->GetY();

        $oPdf->Cell(5, $oConfigRelatorio->iAlturaLinha, '', 1, 0, 'C');
        $oPdf->Cell(100, $oConfigRelatorio->iAlturaLinha, '', 1);

        $oPdf->Cell(10, $oConfigRelatorio->iAlturaLinha, '', 1, 0, "C");
        $oPdf->Cell(10, $oConfigRelatorio->iAlturaLinha, '', 1, 0, "C");
        $oPdf->Cell(10, $oConfigRelatorio->iAlturaLinha, '', 1, 0, "C");
        $oPdf->Cell(10, $oConfigRelatorio->iAlturaLinha, '', 1, 0, "C");
        $oPdf->Cell(10, $oConfigRelatorio->iAlturaLinha, '', 1, 0, "C");
        $oPdf->Cell(10, $oConfigRelatorio->iAlturaLinha, '', 1, 0, "C");
        $oPdf->Cell(10, $oConfigRelatorio->iAlturaLinha, '', 1, 0, "C");
        $oPdf->Cell(20, $oConfigRelatorio->iAlturaLinha, '', 1, 0, "C");
        $oPdf->Cell(20, $oConfigRelatorio->iAlturaLinha, '', 1, 1, "C");
    }



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
function buscaAbreviaturaSituacao($sSituacaoBusca)
{

    $sSituacaoRetorno = '';

    foreach (getSituacoes() as $sAbrev => $sSituacao) {

        if ($sSituacaoBusca == $sSituacao) {

            $sSituacaoRetorno = $sSituacaoBusca;
            break;
        }
    }
    return $sSituacaoRetorno;
}

/**
 * Retorna a legenda das Situações tratadas no relatório
 * @return string
 */
function montaLegendaSituacoes()
{

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
function getSituacoes()
{

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
function abreviar($nome, $max, $substr = false)
{

    if (strlen(trim($nome)) > $max) {

        $strinv = strrev(trim($nome));
        $ultnome = substr($strinv, 0, strpos($strinv, " "));
        $ultnome = strrev($ultnome);
        $nome = strrev($strinv);
        $prinome = substr($nome, 0, strpos($nome, " "));
        $nomes = strtok($nome, " ");
        $iniciais = "";

        while ($nomes):
            if (($nomes == 'E') || ($nomes == 'DE') || ($nomes == 'DOS') ||
                ($nomes == 'DAS') || ($nomes == 'DA') || ($nomes == 'DO')
            ) {
                $iniciais .= " " . $nomes;
                $nomes = strtok(" ");
            } elseif (($nomes == $ultnome) || ($nomes == $prinome)) {
                $nome = "";
                $nomes = strtok(" ");
            } else {
                $iniciais .= " " . $nomes[0] . ".";
                $nomes = strtok(" ");
            }
        endwhile;

        $nome =  $prinome;
        $nome .= $iniciais;
        $nome .= " " . $ultnome;
    }

    if (!$substr) {
        return trim($nome);
    } else {
        return substr(trim($nome), 0, 20);
    }
}

function percfrequencia($calendar)
{
    $sql   = pg_query("select
	                   ed52_c_descr,
					   ed15_c_nome
					   from
					   calendario
					   inner join turma on ed57_i_calendario = ed52_i_codigo
					   inner join turno on ed15_i_codigo     = ed57_i_turno
					   where ed52_i_codigo = " . $calendar);
    $resultado = pg_fetch_all($sql);
    $nome  = $resultado[0]["ed52_c_descr"];
    $turno = substr($resultado[0]["ed15_c_nome"], 0, 5);
    if (substr($nome, 0, 12) == 'ED. INFANTIL' or substr($nome, 0, 17) == 'EDUCAÇÃO INFANTIL') {
        $auladadas = 200;
    } elseif (substr($nome, 0, 13) == 'ANOS INICIAIS' or substr($nome, 0, 20) == 'EN FUN ANOS INICIAIS') {
        $auladadas = 200;
    } elseif (substr($nome, 0, 11) == 'ANOS FINAIS' or substr($nome, 0, 18) == 'EN FUN ANOS FINAIS') {
        $auladadas = 1000;
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

$oPdf->Output();

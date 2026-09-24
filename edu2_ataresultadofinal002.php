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


// error_reporting(E_ALL);           // ou 32767
// ini_set('display_errors', 1);     // mostra erros na tela
// ini_set('display_startup_errors', 1);

/**
 * @author Uemerson Santana
 * @date 01/07/2025
 * @version 2.0 (Versão refatorada)
 * @description Relatório de Resultados Finais com código otimizado e organizado
 */

// Inclusão de bibliotecas necessárias
require_once(modification("libs/db_utils.php"));
require_once(modification("libs/db_app.utils.php"));
require_once(modification("dbforms/db_funcoes.php"));
require_once(modification("std/DBDate.php"));
require_once(modification("libs/JSON.php"));
require_once(modification("classes/db_edu_parametros_classe.php"));
require_once(modification("libs/db_libdocumento.php"));
require_once(modification("libs/db_stdlibwebseller.php"));
require_once(modification("libs/db_libparagrafo.php"));
require_once(modification("model/educacao/avaliacao/iFormaObtencao.interface.php"));
require_once(modification("model/educacao/avaliacao/iElementoAvaliacao.interface.php"));
require_once(modification("model/educacao/TurmaRepository.model.php"));
require_once(modification("model/CgmFactory.model.php"));
require_once(modification("std/db_stdClass.php"));
require_once(modification("model/educacao/avaliacao/GradeAproveitamentoAluno.model.php"));
require_once(modification("model/educacao/RegenciaRepository.model.php"));

/**
 * Constantes para tipos de ensino
 */
define('ENSINO_EDUCACAO_INFANTIL', 'EDUCAÇÃO INFANTIL PRÉ-ESCOLA');
define('ENSINO_EF_ANOS_INICIAIS', 'E F ANOS INICIAIS');
define('ENSINO_EF_ANOS_FINAIS', 'E.F. ANOS FINAIS');
define('ENSINO_EJA_ANOS_INICIAIS', 'EJA ANOS INICIAIS');
define('ENSINO_EJA_ANOS_FINAIS', 'EJA ANOS FINAIS');

/*
## 1. Educação Infantil (`educacao_infantil`)
Segmento: Pré-escola
- Aplicação: EDUCAÇÃO INFANTIL PRÉ-ESCOLA
- Características:
  - Relatório simples sem disciplinas específicas
  - Campos: Nº, Nome do Aluno, Data de Nascimento, Frequência %
  - Foco na frequência e desenvolvimento geral

## 2. EF Anos Iniciais - 1º Ano (`ef_anos_iniciais_1ano`)
Segmento: Ensino Fundamental I - Ciclo Básico de Alfabetização
- Aplicação: 1º ANO do Ensino Fundamental
- Características:
  - Relatório simples com foco na alfabetização
  - Campos: Nº, Nome do Aluno, Frequência Anual %, Resultado
  - Considera progressão no ciclo de alfabetização

## 3. EF Anos Iniciais - 2º ao 5º Ano (`ef_anos_iniciais_2a5`)
Segmento: Ensino Fundamental I - Anos posteriores
- Aplicação: 2º, 3º, 4º e 5º anos do Ensino Fundamental
- Características:
  - Relatório complexo com disciplinas
  - Avaliação por disciplinas específicas
  - Controle de aprovação/reprovação por matéria

## 4. EF Anos Finais (`ef_anos_finais`)
Segmento: Ensino Fundamental II
- Aplicação: E.F. ANOS FINAIS (6º ao 9º ano)
- Características:
  - Relatório complexo com múltiplas disciplinas
  - Sistema de avaliação por notas/conceitos
  - Controle rigoroso de frequência por disciplina
  - Método especial: getAlunosMatriculadosNaTurmaPorSerieAlfa

## 5. EJA Iniciais - Ciclo Básico (`eja_iniciais_ciclo_basico`)
Segmento: Educação de Jovens e Adultos - Alfabetização
- Aplicação: CIC BÁS DE ALFABET (Ciclo Básico de Alfabetização)
- Características:
  - Relatório simples focado na alfabetização de adultos
  - Campos: Nº, Nome do Aluno, Frequência Anual %, Resultado
  - Adaptado para metodologia EJA

## 6. EJA Iniciais - Ciclos (`eja_iniciais_ciclos`)
Segmento: EJA Anos Iniciais com disciplinas
- Aplicação: 1º CICLO e 2º CICLO da EJA Anos Iniciais
- Características:
  - Relatório complexo com disciplinas
  - Organização por ciclos em vez de séries
  - Metodologia adaptada para jovens e adultos

## 7. EJA Finais - Ciclos (`eja_finais_ciclos`)
Segmento: EJA Anos Finais
- Aplicação: 3º CICLO e 4º CICLO da EJA Anos Finais
- Características:
  - Relatório complexo com múltiplas disciplinas
  - Organização por ciclos (equivalente ao fundamental II)
  - Carga horária diferenciada (diurno/noturno)

## 8. Padrão (`padrao`)
Segmento: Casos não especificados
- Aplicação: Qualquer situação que não se encaixe nos tipos anteriores
- Características:
  - Relatório genérico
  - Funcionalidades básicas de aprovação/reprovação

## Diferenças Principais:

### Relatórios Simples:
- Educação Infantil
- EF 1º Ano
- EJA Ciclo Básico

### Relatórios Complexos (com disciplinas):
- EF 2º ao 5º Ano
- EF Anos Finais
- EJA Ciclos

### Funcionalidades Especiais:
- Progressão Parcial/Dependência: Principalmente nos ensinos complexos
- Amparo: Sistema de justificativas para situações especiais
- Frequência Global: Cálculos diferenciados por tipo
- Necessidades Especiais: Marcação "PD" para alunos inclusos

Cada tipo tem seu próprio método de processamento, campos específicos e regras de negócio adaptadas às particularidades pedagógicas de cada segmento educacional.
*/

/**
 * Verifica se um aluno possui necessidades especiais
 *
 * @author Uemerson Santana
 * @date 22/05/2025
 * Demanda: 17299
 *
 * @param int $iCodigoAluno - Código do aluno
 * @return bool - True se tem necessidades especiais
 */
function isAlunoComNecessidadesEspeciais($iCodigoAluno) {
    $sSql = "SELECT ed214_i_aluno
             FROM alunonecessidade
             WHERE ed214_i_aluno = {$iCodigoAluno}";

    $rsResult = db_query($sSql);
    return (pg_num_rows($rsResult) > 0);
}

function isAvaliadoPorParecer($iCodigoMatriculaTmp) {
    $sSql = "SELECT * FROM matricula WHERE ed60_i_codigo = '$iCodigoMatriculaTmp' AND ed60_c_parecer = 'S'";

    $rsResult = db_query($sSql);

    return (pg_num_rows($rsResult) > 0);
}

/**
 * Busca código da regência baseado na série de origem do aluno (origem 'S')
 * Similar à função buscaCodigoRegenciaComoAta da Ficha Individual
 *
 * @author Uemerson Santana
 * @date 2025-01-XX
 * @param int $iCodigoTurma Código da turma
 * @param int $iCodigoMatricula Código da matrícula
 * @return int|null Código da regência ou null se não encontrar
 */
function buscaCodigoRegenciaPorMatricula($iCodigoTurma, $iCodigoMatricula) {
    // Buscar a série com origem 'S' (Sistema) da matrícula
    $sqlSerie = "SELECT ms.ed221_i_serie
                 FROM matriculaserie ms
                 WHERE ms.ed221_i_matricula = {$iCodigoMatricula}
                 AND ms.ed221_c_origem = 'S'
                 LIMIT 1";

    $resultadoSerie = db_query($sqlSerie);

    if (pg_num_rows($resultadoSerie) == 0) {
        // Fallback: buscar primeira regência da turma
        $sqlRegenciaFallback = "SELECT ed59_i_codigo
                               FROM regencia
                               WHERE ed59_i_turma = {$iCodigoTurma}
                               ORDER BY ed59_i_codigo
                               LIMIT 1";
        $resultadoFallback = db_query($sqlRegenciaFallback);
        if (pg_num_rows($resultadoFallback) > 0) {
            $dadosFallback = db_utils::fieldsMemory($resultadoFallback, 0);
            return $dadosFallback->ed59_i_codigo;
        }
        return null;
    }

    $dadosSerie = db_utils::fieldsMemory($resultadoSerie, 0);
    $codigoSerie = $dadosSerie->ed221_i_serie;

    // Buscar a primeira regência da turma para a série específica
    $sqlRegencia = "SELECT ed59_i_codigo
                    FROM regencia
                    WHERE ed59_i_turma = {$iCodigoTurma}
                    AND ed59_i_serie = {$codigoSerie}
                    ORDER BY ed59_i_codigo
                    LIMIT 1";

    $resultadoRegencia = db_query($sqlRegencia);

    if (pg_num_rows($resultadoRegencia) > 0) {
        $dadosRegencia = db_utils::fieldsMemory($resultadoRegencia, 0);
        return $dadosRegencia->ed59_i_codigo;
    }

    return null;
}

/**
 * Calcula frequência diretamente do diário sem usar GradeAproveitamentoAluno
 * Usado para casos onde GradeAproveitamentoAluno não encontra o diário corretamente
 *
 * @author Uemerson Santana
 * @date 04/11/2025
 * @demanda 17412
 *
 * MOTIVO DA ALTERAÇÃO:
 * Alunos de educação infantil estavam aparecendo com 0% de frequência na ATA de Resultados Finais,
 * mesmo tendo frequência correta na Ficha Individual do Aluno.
 *
 * CAUSA RAIZ:
 * - GradeAproveitamentoAluno->getDadosFrequenciaDaDiscplina() usa getDisciplinasPorRegencia()
 * - getDisciplinasPorRegencia() depende de getDisciplinas() que busca diários por etapa de origem
 * - Quando a regência não corresponde exatamente à busca de getDisciplinas(), retorna null
 * - Com null, o percentual de frequência fica 0%
 *
 * SOLUÇÃO:
 * Buscar o diário diretamente pela matrícula, série de origem e regência, calculando
 * a frequência manualmente: ((total_aulas - faltas_sem_abono) / total_aulas) * 100
 *
 * @param int $iCodigoMatricula Código da matrícula
 * @param int $iCodigoRegencia Código da regência
 * @return stdClass Objeto com dados de frequência
 */
function calcularFrequenciaDireta($iCodigoMatricula, $iCodigoRegencia) {
    $oDadosFrequencia = new stdClass();
    $oDadosFrequencia->iTotalAulas = 0;
    $oDadosFrequencia->iTotalFaltas = 0;
    $oDadosFrequencia->iFaltasAbonadas = 0;
    $oDadosFrequencia->nPercentualFrequencia = 0;
    $oDadosFrequencia->lReclassificadoBaixaFrequencia = false;

    try {
        // Buscar série de origem da matrícula
        $sqlSerie = "SELECT ms.ed221_i_serie
                     FROM matriculaserie ms
                     WHERE ms.ed221_i_matricula = {$iCodigoMatricula}
                     AND ms.ed221_c_origem = 'S'
                     LIMIT 1";
        $rsSerie = db_query($sqlSerie);

        if (pg_num_rows($rsSerie) == 0) {
            return $oDadosFrequencia;
        }

        $dadosSerie = db_utils::fieldsMemory($rsSerie, 0);
        $iSerieOrigem = $dadosSerie->ed221_i_serie;

        // Buscar diário pela matrícula, série de origem e regência
        $sqlDiario = "SELECT d.ed95_i_codigo
                      FROM diario d
                      INNER JOIN matricula m ON m.ed60_i_aluno = d.ed95_i_aluno
                      INNER JOIN regencia r ON r.ed59_i_codigo = d.ed95_i_regencia
                      WHERE m.ed60_i_codigo = {$iCodigoMatricula}
                      AND d.ed95_i_regencia = {$iCodigoRegencia}
                      AND d.ed95_i_serie = {$iSerieOrigem}
                      AND r.ed59_i_serie = {$iSerieOrigem}
                      LIMIT 1";
        $rsDiario = db_query($sqlDiario);

        if (pg_num_rows($rsDiario) == 0) {
            return $oDadosFrequencia;
        }

        $dadosDiario = db_utils::fieldsMemory($rsDiario, 0);
        $iCodigoDiario = $dadosDiario->ed95_i_codigo;

        // Buscar total de aulas dadas da regência (somando períodos com ed09_c_somach = 'S')
        $sqlAulas = "SELECT SUM(rp.ed78_i_aulasdadas) as total_aulas
                     FROM regenciaperiodo rp
                     INNER JOIN procavaliacao pa ON pa.ed41_i_codigo = rp.ed78_i_procavaliacao
                     INNER JOIN periodoavaliacao p ON p.ed09_i_codigo = pa.ed41_i_periodoavaliacao
                     WHERE rp.ed78_i_regencia = {$iCodigoRegencia}
                     AND p.ed09_c_somach = 'S'";
        $rsAulas = db_query($sqlAulas);
        $iTotalAulas = 0;
        if (pg_num_rows($rsAulas) > 0) {
            $dadosAulas = db_utils::fieldsMemory($rsAulas, 0);
            $iTotalAulas = intval($dadosAulas->total_aulas);
        }

        // Buscar total de faltas e faltas abonadas do diário
        $sqlFaltas = "SELECT
                         COALESCE(SUM(dav.ed72_i_numfaltas), 0) as total_faltas,
                         COALESCE(SUM(CASE WHEN ab.ed80_i_codigo IS NOT NULL THEN dav.ed72_i_numfaltas ELSE 0 END), 0) as faltas_abonadas
                      FROM diarioavaliacao dav
                      INNER JOIN procavaliacao pa ON pa.ed41_i_codigo = dav.ed72_i_procavaliacao
                      INNER JOIN periodoavaliacao p ON p.ed09_i_codigo = pa.ed41_i_periodoavaliacao
                      LEFT JOIN abonofalta ab ON ab.ed80_i_diarioavaliacao = dav.ed72_i_codigo
                      WHERE dav.ed72_i_diario = {$iCodigoDiario}
                      AND p.ed09_c_somach = 'S'";
        $rsFaltas = db_query($sqlFaltas);
        $iTotalFaltas = 0;
        $iFaltasAbonadas = 0;
        if (pg_num_rows($rsFaltas) > 0) {
            $dadosFaltas = db_utils::fieldsMemory($rsFaltas, 0);
            $iTotalFaltas = intval($dadosFaltas->total_faltas);
            $iFaltasAbonadas = intval($dadosFaltas->faltas_abonadas);
        }

        /**
         * Autor: Uemerson Santana
         * Data: 02/03/2026
         * Demanda: 18250
         * Razao: Corrigido ordem das operacoes para evitar erro de precisao
         *        de ponto flutuante IEEE 754 (ex: floor(57.999...) = 57 ao
         *        inves de 58). Multiplicar por 100 antes de dividir.
         */
        // Calcular percentual de frequência
        $iFaltasSemAbono = $iTotalFaltas - $iFaltasAbonadas;
        $nPercentualFrequencia = 0;

        if ($iTotalAulas > 0) {
            $nPercentualFrequencia = floor((($iTotalAulas - $iFaltasSemAbono) * 100) / $iTotalAulas);
        }

        $oDadosFrequencia->iTotalAulas = $iTotalAulas;
        $oDadosFrequencia->iTotalFaltas = $iFaltasSemAbono;
        $oDadosFrequencia->iFaltasAbonadas = $iFaltasAbonadas;
        $oDadosFrequencia->nPercentualFrequencia = $nPercentualFrequencia;

    } catch (Exception $e) {
        error_log("Erro em calcularFrequenciaDireta: " . $e->getMessage());
    }

    return $oDadosFrequencia;
}

/**
 * Calcula a frequência do aluno utilizando o método GradeAproveitamentoAluno
 *
 * @author Uemerson Santana
 * @date 16/05/2025
 * Demanda: 17338
 *
 * @param int $iCodigoMatricula - Código da matrícula do aluno
 * @param int $iCodigoRegencia - Código da regência
 * @return stdClass Objeto contendo os dados de frequência do aluno
 */
function calcularFrequenciaAluno($iCodigoMatricula, $iCodigoRegencia) {
    try {
        $oMatricula = MatriculaRepository::getMatriculaByCodigo($iCodigoMatricula);
        $oRegencia = RegenciaRepository::getRegenciaByCodigo($iCodigoRegencia);

        if (!$oMatricula || !$oRegencia) {
            throw new Exception("Não foi possível encontrar a matrícula ou regência.");
        }

        // CORREÇÃO: Verificar se a matrícula possui etapa de origem
        $oEtapaOrigem = $oMatricula->getEtapaDeOrigem();
        if (!$oEtapaOrigem) {
            throw new Exception("Matrícula não possui etapa de origem definida.");
        }

        $oGradeAproveitamento = new GradeAproveitamentoAluno($oMatricula);
        $oDadosFrequencia = $oGradeAproveitamento->getDadosFrequenciaDaDiscplina($oRegencia);

        return $oDadosFrequencia;
    } catch (Exception $e) {
        // Log do erro para debug (opcional)
        error_log("Erro em calcularFrequenciaAluno: " . $e->getMessage());

        $oDadosFrequencia = new stdClass();
        $oDadosFrequencia->iTotalAulas = 0;
        $oDadosFrequencia->iTotalFaltas = 0;
        $oDadosFrequencia->iFaltasAbonadas = 0;
        $oDadosFrequencia->nPercentualFrequencia = 0;
        $oDadosFrequencia->lReclassificadoBaixaFrequencia = false;

        return $oDadosFrequencia;
    }
}

/**
 * Calcula o percentual de frequência utilizando o método GradeAproveitamentoAluno
 */
function calcularPercentualFrequencia($iCodigoMatricula, $iCodigoRegencia) {
    $oDadosFrequencia = calcularFrequenciaAluno($iCodigoMatricula, $iCodigoRegencia);
    return $oDadosFrequencia->nPercentualFrequencia;
}

/**
 * Obtém o total de faltas utilizando o método GradeAproveitamentoAluno
 */
function obterTotalFaltas($iCodigoMatricula, $iCodigoRegencia) {
    $oDadosFrequencia = calcularFrequenciaAluno($iCodigoMatricula, $iCodigoRegencia);
    return $oDadosFrequencia->iTotalFaltas;
}

/**
 * Obtém o total de aulas utilizando o método GradeAproveitamentoAluno
 */
function obterTotalAulas($iCodigoRegencia) {
    try {
        $oRegencia = RegenciaRepository::getRegenciaByCodigo($iCodigoRegencia);
        if (!$oRegencia) {
            throw new Exception("Não foi possível encontrar a regência.");
        }
        return $oRegencia->getTotalDeAulas();
    } catch (Exception $e) {
        return 0;
    }
}

/**
 * Verifica se o aluno foi reclassificado devido a baixa frequência
 */
function verificarReclassificacaoPorBaixaFrequencia($iCodigoMatricula, $iCodigoRegencia) {
    $oDadosFrequencia = calcularFrequenciaAluno($iCodigoMatricula, $iCodigoRegencia);
    return $oDadosFrequencia->lReclassificadoBaixaFrequencia;
}

/**
 * Determina o tipo de ensino e ciclo da turma
 *
 * @param Turma $oTurma
 * @param int $iCodigoEtapa
 * @return array Array com informações do ensino
 */
function determinarTipoEnsino($oTurma, $iCodigoEtapa) {
    $xnomeetapa = $oTurma->getBaseCurricular()->getDescricao();
    $xiclo = '';

    // Correção para casos específicos
    if ($xnomeetapa == "EF ANOS INICIAIS") {
        $xnomeetapa = "E F ANOS INICIAIS";
    }

    /*
     * Autor: Uemerson Santana
     * Data: 18/11/2025
     * Demanda: 17412
     * Razão: Correção para identificar corretamente o tipo de relatório (simples ou complexo)
     *        para turmas de EJA Anos Iniciais e E F Anos Iniciais que possuem múltiplas etapas.
     *
     *        PROBLEMA IDENTIFICADO:
     *        - A função sempre pegava a primeira etapa da lista ($oTurma->getEtapas()[0]),
     *          ignorando qual etapa foi especificada no parâmetro $iCodigoEtapa da URL.
     *        - Turmas de EJA podem ter múltiplas etapas (CBA, 1º CICLO, 2º CICLO).
     *        - Quando a URL especificava etapa=31 (2º CICLO), o código buscava a primeira
     *          etapa (CBA), resultando em relatório simples sem disciplinas.
     *        - Os nomes das etapas vinham com espaços extras do banco de dados, fazendo
     *          com que comparações como "1º CICLO" falhassem.
     *
     *        SOLUÇÃO IMPLEMENTADA:
     *        - Buscar a etapa específica usando EtapaRepository::getEtapaByCodigo($iCodigoEtapa)
     *          em vez de sempre pegar a primeira etapa da lista.
     *        - Aplicar trim() no nome da etapa para remover espaços extras do banco.
     *        - Manter fallback para primeira etapa caso a específica não seja encontrada.
     *
     *        RESULTADO:
     *        - Turmas de 1º e 2º CICLO da EJA Anos Iniciais agora geram relatório complexo
     *          com notas por disciplina (Língua Portuguesa, Matemática, História,
     *          Geografia, Ciências), além de frequência e resultado final.
     *        - Turmas de CBA continuam gerando relatório simples (apenas frequência e resultado).
     */
    // Obter ciclo para ensinos específicos
    if (in_array($xnomeetapa, array("EJA ANOS INICIAIS", "EJA ANOS FINAIS", "E F ANOS INICIAIS"))) {
        // Buscar a etapa específica passada como parâmetro
        $oEtapaEspecifica = EtapaRepository::getEtapaByCodigo($iCodigoEtapa);
        if ($oEtapaEspecifica) {
            $xiclo = trim($oEtapaEspecifica->getNome());
        } else {
            // Fallback: pegar primeira etapa se não encontrar a específica
            $xurma = $oTurma->getEtapas();
            if (!empty($xurma)) {
                $xurma = $xurma[0];
                $xiclo = trim($xurma->getEtapa()->getNome());
            }
        }
    }

    return array(
        'nome_etapa' => $xnomeetapa,
        'ciclo' => $xiclo,
        'tipo' => determinarTipoRelatorio($xnomeetapa, $xiclo)
    );
}

/**
 * Determina o tipo de relatório baseado no ensino
 */
function determinarTipoRelatorio($xnomeetapa, $xiclo) {
    if ($xnomeetapa == ENSINO_EDUCACAO_INFANTIL) {
        return 'educacao_infantil';
    }

    if ($xnomeetapa == ENSINO_EF_ANOS_INICIAIS) {
        if ($xiclo == "1º ANO") {
            return 'ef_anos_iniciais_1ano';
        }
        if (in_array($xiclo, array("2º ANO", "3º ANO", "4º ANO", "5º ANO"))) {
            return 'ef_anos_iniciais_2a5';
        }
    }

    if ($xnomeetapa == ENSINO_EF_ANOS_FINAIS) {
        return 'ef_anos_finais';
    }

    if ($xnomeetapa == ENSINO_EJA_ANOS_INICIAIS) {
        if ($xiclo == "CIC BÁS DE ALFABET") {
            return 'eja_iniciais_ciclo_basico';
        }
        if (in_array($xiclo, array("1º CICLO", "2º CICLO"))) {
            return 'eja_iniciais_ciclos';
        }
    }

    if ($xnomeetapa == ENSINO_EJA_ANOS_FINAIS && in_array($xiclo, array("3º CICLO", "4º CICLO"))) {
        return 'eja_finais_ciclos';
    }

    return 'padrao';
}

/**
 * Gera texto do cabeçalho específico para cada tipo de ensino
 */
function gerarTextoTipo($oTurma, $iCodigoEtapa, $tipoEnsino) {
    $xetapa = EtapaRepository::getEtapaByCodigo($iCodigoEtapa)->getEnsino()->getNome();
    $xserie = EtapaRepository::getEtapaByCodigo($iCodigoEtapa)->getNome();

    $professorConselheiro = $oTurma->getProfessorConselheiro();
    $xnomeprofessor = $professorConselheiro ? trataNome($professorConselheiro->getNome()) : "";

    $mesaqui = ucfirst($oTurma->oDadosEscola->iMes);
    $etapaabreviada = ($xetapa == "EDUCAÇÃO INFANTIL PRÉ-ESCOLA") ? "Educação Infantil" : $xetapa;

    $textoBase = "Aos {$oTurma->oDadosEscola->iDia} dias do mês de {$mesaqui} de {$oTurma->oDadosEscola->iAno}";

    switch ($tipoEnsino['tipo']) {
        case 'educacao_infantil':
            return $textoBase . ", encerrou-se a avaliação pedagógica do {$xserie} da {$etapaabreviada}, Turma: {$oTurma->getDescricao()}, turno: {$oTurma->getTurno()->getDescricao()}, Professor(a) {$xnomeprofessor}, deste Estabelecimento de Ensino, com os seguintes resultados:";

        case 'ef_anos_iniciais_1ano':
            return $textoBase . ", encerrou-se a apuração de resultados do Ciclo Básico de Alfabetização - {$xserie} do Ensino Fundamental, Turma: {$oTurma->getDescricao()}, turno: {$oTurma->getTurno()->getDescricao()}, Professor(a) {$xnomeprofessor}, deste Estabelecimento de Ensino, com os seguintes resultados:";

        case 'ef_anos_iniciais_2a5':
        case 'ef_anos_finais':
            return $textoBase . ", encerrou-se a apuração de resultados do {$xserie} do Ensino Fundamental, Turma: {$oTurma->getDescricao()}, turno: {$oTurma->getTurno()->getDescricao()}, Professor(a) {$xnomeprofessor}, deste Estabelecimento de Ensino, com os seguintes resultados:";

        case 'eja_iniciais_ciclo_basico':
        case 'eja_iniciais_ciclos':
        case 'eja_finais_ciclos':
            $xurma = $oTurma->getEtapas();
            $xiclo = !empty($xurma) ? $xurma[0]->getEtapa()->getNome() : '';

            if ($xiclo == "CIC BÁS DE ALFABET") {
                return $textoBase . ", encerrou-se a apuração de resultados dos alunos do Ciclo Básico e Alfabetização da EJA - Educação de Jovens e Adultos, Turma: {$oTurma->getDescricao()}, turno: {$oTurma->getTurno()->getDescricao()}, Professor(a) {$xnomeprofessor}, deste Estabelecimento de Ensino, com os seguintes resultados:";
            }
            return $textoBase . ", encerrou-se a apuração de resultados dos alunos do {$xiclo} da EJA - Educação de Jovens e Adultos, Turma: {$oTurma->getDescricao()}, turno: {$oTurma->getTurno()->getDescricao()}, deste Estabelecimento de Ensino, com os seguintes resultados:";

        default:
            return $textoBase . ", encerrou-se a avaliação pedagógica, com os seguintes resultados:";
    }
}

/**
 * Gera cabeçalho simples para tipos específicos
 */
function gerarCabecalhoSimples($oPdf, $tipo) {
    $oPdf->Cell(10, 9, "", 0, 0, "C", 0);

    switch ($tipo) {
        case 'educacao_infantil':
            $oPdf->Cell(10, 9, "Nº", "LT", 0, "C", 0);
            $oPdf->Cell(110, 9, "Nome do Aluno", "LT", 0, "C", 0);
            $oPdf->Cell(30, 9, "Data de Nascimento", "LT", 0, "C", 0);
            $oPdf->Cell(22, 9, "Frequência %", "LTR", 1, "C", 0);
            break;

        case 'ef_anos_iniciais_1ano':
        case 'eja_iniciais_ciclo_basico':
            $oPdf->Cell(10, 9, "Nº", "LT", 0, "C", 0);
            $oPdf->Cell(110, 9, "Nome do Aluno", "LT", 0, "C", 0);
            $oPdf->Cell(30, 9, "Frequência Anual %", "LT", 0, "C", 0);
            $oPdf->Cell(22, 9, "Resultado", "LTR", 1, "C", 0);
            break;
    }
}

/**
 * Gera cabeçalho complexo com disciplinas
 */
function gerarCabecalhoComplexo($oPdf, $oFiltroRelatorio, $aDisciplinasPagina, $oTurma, $iEtapa) {
    $oFiltroRelatorio->iTotalDisciplinas = count($aDisciplinasPagina);
    $oPdf->setfont('arial', 'b', 10);

    // Cabeçalho principal
    $oPdf->Cell(5, 40, "Nº", "LRT", 0, "C", 0);
    $oPdf->Cell($oFiltroRelatorio->iTamanhoTotalColunaDisciplina + 14, 40, "Nomes", "LRT", 0, "C", 0);

    // Configurar disciplinas
    if ($oFiltroRelatorio->iTotalDisciplinas < $oFiltroRelatorio->iTotalDisciplinasPorPagina) {
        $oFiltroRelatorio->iContadorDisciplinasImpressas = $oFiltroRelatorio->iTotalDisciplinas;
    }

    $oFiltroRelatorio->iColunasEmBranco = 0;
    if ($oFiltroRelatorio->iContadorDisciplinasImpressas < $oFiltroRelatorio->iTotalDisciplinasPorPagina) {
        $oFiltroRelatorio->iColunasEmBranco = $oFiltroRelatorio->iTotalDisciplinasPorPagina - $oFiltroRelatorio->iContadorDisciplinasImpressas;
    }

    // Disciplinas
    $koluna = 9;
    $kamanho = 100;
    $reduzcoluna = 2;

    for ($iContadorDisciplinas = 0; $iContadorDisciplinas < $oFiltroRelatorio->iContadorDisciplinasImpressas; $iContadorDisciplinas++) {
        if (!array_key_exists($iContadorDisciplinas, $aDisciplinasPagina)) {
            break;
        }

        $oPdf->Cell($oFiltroRelatorio->iTamanhoColunaAbrevDisciplina - $reduzcoluna, 40, "", "LRT", 0, "C", 0);
        $oPdf->SetFillColor(0, 0, 0);

        // Obter nome da disciplina
        $nomeDisciplina = $aDisciplinasPagina[$iContadorDisciplinas]->getDisciplina()->getNomeDisciplina();

        // Ajuste dinâmico por largura real
        $larguraDisponivel = 35; // altura disponível para texto vertical (ajustar conforme necessário)
        $tamanhoFonte = 10; // fonte inicial

        // Ajustar fonte até o texto caber na largura disponível
        $oPdf->SetFont('Arial', 'b', $tamanhoFonte);
        while ($oPdf->GetStringWidth($nomeDisciplina) > $larguraDisponivel && $tamanhoFonte > 4) {
            $tamanhoFonte -= 0.5;
            $oPdf->SetFont('Arial', 'b', $tamanhoFonte);
        }

        // Se ainda não couber, truncar com "..."
        if ($oPdf->GetStringWidth($nomeDisciplina) > $larguraDisponivel) {
            while ($oPdf->GetStringWidth($nomeDisciplina . "...") > $larguraDisponivel && strlen($nomeDisciplina) > 5) {
                $nomeDisciplina = substr($nomeDisciplina, 0, -1);
            }
            $nomeDisciplina .= "...";
        }

        $alturaTexto = 73; // Correção da altura
        $oPdf->TextWithDirection($kamanho, $alturaTexto, $nomeDisciplina, 'U');

        $oPdf->SetTextColor(0, 0, 0);
        $oPdf->SetFillColor(225, 225, 225);
        $kamanho += $koluna;
        $oPdf->setfont('arial', 'b', 7);
    }

    // Colunas em branco
    for ($iContadorColunaBranco = 0; $iContadorColunaBranco < $oFiltroRelatorio->iColunasEmBranco; $iContadorColunaBranco++) {
        $oPdf->Cell($oFiltroRelatorio->iTamanhoColunaAbrevDisciplina - 2, 40, "", "LRT", 0, "C", 0);
    }

    // Coluna de resultado final
    gerarColunaResultadoFinal($oPdf, $oFiltroRelatorio);

    // Segunda linha do cabeçalho
    gerarSegundaLinhaCabecalho($oPdf, $oFiltroRelatorio, $aDisciplinasPagina);
}

/**
 * Gera coluna de resultado final no cabeçalho
 */
function gerarColunaResultadoFinal($oPdf, $oFiltroRelatorio) {
    if ($oFiltroRelatorio->lCalculaFrequencia == 2 && $oFiltroRelatorio->iFrequencia != 1) {
        switch ($oFiltroRelatorio->iFrequencia) {
            case 2:
                $oPdf->SetFillColor(0, 0, 0);
                $oPdf->SetFont('Arial', 'b', 10);
                $pegax = $oPdf->getX();
                $oPdf->Cell($oFiltroRelatorio->iTamanhoColunaResultado + 3, 40, "", "LRT", 0, "C", 0);
                $pegax2 = $oPdf->getX();
                $oPdf->Cell($oFiltroRelatorio->iTamanhoColunaResultado + 3, 40, "", "LTR", 1, "C", 0);
                $oPdf->TextWithDirection($pegax + $oFiltroRelatorio->iTamanhoColunaResultado, 73, "Frequência anual (%)", 'U');
                $oPdf->TextWithDirection($pegax2 + $oFiltroRelatorio->iTamanhoColunaResultado, 65, "Resultado", 'U');
                $oPdf->SetTextColor(0, 0, 0);
                $oPdf->SetFillColor(225, 225, 225);
                $oPdf->setfont('arial', 'b', 7);
                break;
            case 3:
                $oPdf->Cell($oFiltroRelatorio->iTamanhoColunaResultado + 3, 40, "FT", "LRT", 0, "C", 0);
                $oPdf->Cell($oFiltroRelatorio->iTamanhoColunaResultado + 3, 40, "RF", "LTR", 1, "C", 0);
                break;
            case 4:
                $oPdf->Cell($oFiltroRelatorio->iTamanhoColunaResultado + 3, 40, "FRE", "LRT", 0, "C", 0);
                $oPdf->Cell($oFiltroRelatorio->iTamanhoColunaResultado + 3, 40, "RF", "LTR", 1, "C", 0);
                break;
        }
    } else {
        $oPdf->Cell(12, 40, "RF", "RT", 1, "C", 0);
    }
}

/**
 * Gera segunda linha do cabeçalho
 */
function gerarSegundaLinhaCabecalho($oPdf, $oFiltroRelatorio, $aDisciplinasPagina) {
    $oPdf->Cell(5, 4, "", "LRT", 0, "C", 0);
    $oPdf->Cell($oFiltroRelatorio->iTamanhoTotalColunaDisciplina + 14, 4, "", "LRT", 0, "C", 0);

    for ($iContadorDisciplinas = 0; $iContadorDisciplinas < $oFiltroRelatorio->iContadorDisciplinasImpressas; $iContadorDisciplinas++) {
        if (!array_key_exists($iContadorDisciplinas, $aDisciplinasPagina)) {
            break;
        }

        if ($oFiltroRelatorio->iFrequencia != 1 && $oFiltroRelatorio->lCalculaFrequencia == 1) {
            $oPdf->Cell($oFiltroRelatorio->iTamanhoColunaAbrevDisciplina - 6, 4, "", "LRT", 0, "C", 0);

            $textoFrequencia = array(2 => "% F", 3 => "FT", 4 => "FRE");
            $texto = isset($textoFrequencia[$oFiltroRelatorio->iFrequencia]) ? $textoFrequencia[$oFiltroRelatorio->iFrequencia] : "";
            $oPdf->Cell($oFiltroRelatorio->iTamanhoColunaAbrevDisciplina - 10, 4, $texto, "LRT", 0, "C", 0);
        } else {
            $oPdf->Cell($oFiltroRelatorio->iTamanhoColunaAbrevDisciplina - 2, 4, "", "LRT", 0, "C", 0);
        }
    }

    // Colunas em branco
    for ($iContadorColunaBranco = 0; $iContadorColunaBranco < $oFiltroRelatorio->iColunasEmBranco; $iContadorColunaBranco++) {
        $oPdf->Cell($oFiltroRelatorio->iTamanhoColunaAbrevDisciplina - 2, 4, "", "LRT", 0, "C", 0);
    }

    if ($oFiltroRelatorio->lCalculaFrequencia == 2 && $oFiltroRelatorio->iFrequencia != 1) {
        $oPdf->Cell($oFiltroRelatorio->iTamanhoColunaResultado + 3, 4, "", "LRT", 0, "C", 0);
        $oPdf->Cell($oFiltroRelatorio->iTamanhoColunaResultado + 3, 4, "", "LRT", 1, "C", 0);
    } else {
        $oPdf->Cell(12, 4, "", "LRT", 1, "C", 0);
    }
}

/**
 * Processa um aluno simples (Educação Infantil, EF 1º ano, EJA Ciclo Básico)
 */
function processarAlunoSimples($oPdf, $aluno, $contador, $oFiltroRelatorio, $tipo, $dados = array(), $numeroSequencial = 1) {
    $enche = ($contador % 2 == 0) ? 0 : 1;
    $numero = ($numeroSequencial !== null) ? $numeroSequencial : $aluno->getNumeroOrdemAluno();
    $nomealuno = $aluno->getAluno()->getNome();

    // Verificar troca de turma
    if ($oFiltroRelatorio->iTrocaTurma == 1 && $aluno->getSituacao() == "TROCA DE TURMA" || $aluno->getSituacao() == "TROCA DE MODALIDADE") {
        return;
    }

    if ($aluno->getSituacao() != "MATRICULADO") {
        $oDtEncerramento = $aluno->getDataEncerramento();
        $oPdf->Cell(10, 6, "", 0, 0, "C", 0);
        $oPdf->Cell(10, 6, $numero, "L", 0, "C", $enche);
        $oPdf->Cell(110, 6, $nomealuno, "L", 0, "L", $enche);
        $oPdf->setfont('arial', '', 7);
        $oPdf->Cell(30, 6, $aluno->getSituacao() . ' em: ' . $oDtEncerramento, "L", 1, "");
        $oPdf->setfont('arial', '', 10);
    } else {
        $oPdf->setfont('arial', '', 10);
        $oPdf->Cell(10, 6, "", 0, 0, "C", 0);
        $oPdf->Cell(10, 6, $numero, "L", 0, "C", $enche);
        $oPdf->Cell(110, 6, $nomealuno, "L", 0, "L", $enche);

        switch ($tipo) {
            case 'educacao_infantil':
                $datanascimento = implode("/", array_reverse(explode("-", $aluno->getAluno()->getDataNascimento())));
                $frequencia = isset($dados['frequencia']) ? number_format($dados['frequencia'], 0, ",", ",") : '0';
                $oPdf->Cell(30, 6, $datanascimento, "L", 0, "C", $enche);
                $oPdf->Cell(22, 6, $frequencia, "LR", 1, "C", $enche);
                break;

            case 'ef_anos_iniciais_1ano':
            case 'eja_iniciais_ciclo_basico':
                $frequencia = isset($dados['frequencia']) ? number_format($dados['frequencia'], 0, ",", ",") : '0';
                $rf = isset($dados['resultado_final']) ? $dados['resultado_final'] : '';
                $oPdf->Cell(30, 6, $frequencia, "L", 0, "C", $enche);
                $oPdf->Cell(22, 6, $rf, "LR", 1, "C", $enche);
                break;
        }
    }
}

/**
 * Obtém resultado final para RPC
 */
function resultado_final_rpc($iEtapa, $iTurma, $iMatricula, $iAnoCalendario) {
    $oTurma = new Turma($iTurma);
    $oEtapa = EtapaRepository::getEtapaByCodigo($iEtapa);

    $aAlunosMatriculados = $oTurma->getAlunosMatriculadosNaTurmaPorSerie($oEtapa);

    $iCodigoEnsino = $oTurma->getBaseCurricular()->getCurso()->getEnsino()->getCodigo();
    $aTermosAprovado = DBEducacaoTermo::getTermoEncerramento($iCodigoEnsino, 'A', $iAnoCalendario);
    $sLabelAprovado = count($aTermosAprovado) > 0 ? $aTermosAprovado[0]->sDescricao : '';
    $lPermiteAprovacaoParcial = EncerramentoAvaliacao::permiteAprovacaoParcial($oTurma, $oEtapa);

    $oRetorno = new stdClass();
    $oRetorno->aDadosAlunos = array();

    foreach ($aAlunosMatriculados as $mat) {
        if ($mat->getCodigo() !== $iMatricula) {
            continue;
        }

        $oDados = new stdClass();
        $oDados->iCodigoAluno = $mat->getAluno()->getCodigoAluno();
        $oDados->iMatricula = $mat->getCodigo();
        $oDados->sNomeAluno = urlencode($mat->getAluno()->getNome());
        $oDados->iOrdemAluno = $mat->getNumeroOrdemAluno();
        $oDados->sSituacao = urlencode($mat->getSituacao());
        $oDados->lConcluido = $mat->isConcluida();

        db_inicio_transacao();

        if ($mat->getSituacao() !== 'MATRICULADO') {
            $oDados->sResultadoFinal = $oDados->sSituacao;
        } else {
            $diarioService = $mat->getDiarioDeClasse()->getDiarioAlunoService();
            $areaProcedimento = $mat->getDiarioDeClasse()->getAreaProcedimento();
            $resultadoFinal = $mat->getDiarioDeClasse()->getResultadoFinal();

            if (!is_null($areaProcedimento)) {
                $resultadoFinal = $diarioService
                    ->getDiarioAluno()
                    ->getResultadoFinal()
                    ->getResultadoFinal();
            }

            if (
                is_null($areaProcedimento)
                && $lPermiteAprovacaoParcial
                && $resultadoFinal === 'A'
                && EncerramentoAvaliacao::validaDiarioAlunoEja($mat, $oEtapa) === 'P'
            ) {
                $resultadoFinal = 'P';
            }

            if (!empty($resultadoFinal)) {
                $aTermos = DBEducacaoTermo::getTermoEncerramento(
                    $iCodigoEnsino,
                    $resultadoFinal,
                    $iAnoCalendario
                );
                if (count($aTermos) > 0) {
                    $resultadoFinal = $aTermos[0]->sDescricao;
                }
            }

            if (
                is_null($areaProcedimento)
                && $mat->getDiarioDeClasse()->aprovadoComProgressaoParcial()
            ) {
                $resultadoFinal = " {$sLabelAprovado} (Progressão Parcial / Dependência)";
            }

            if ($mat->getDiarioDeClasse()->temRecuperacao()) {
                $resultadoFinal = 'EM RECUPERAÇÃO';
            }

            $oDados->sResultadoFinal = $resultadoFinal;
        }

        db_fim_transacao(false);

        $oRetorno->sResultadoFinal = $oDados->sResultadoFinal;
        break;
    }

    return getSiglaSituacao($oRetorno->sResultadoFinal);
}

/**
 * Retorna a sigla da situação
 */
function getSiglaSituacao($resultadoFinal) {
    $aSituacoes = array(
        'REPROVADO' => 'REP',
        'APROVADO' => 'AP'
    );

    return isset($aSituacoes[$resultadoFinal]) ? $aSituacoes[$resultadoFinal] : $resultadoFinal;
}

/**
 * Gera assinaturas padrão
 */
function gerarAssinaturasPadrao($oPdf, $oFiltroRelatorio) {
    $nomediretor = isset($_GET["diretor"]) ? explode("|", $_GET["diretor"])[1] : '';
    $nomediretor = trataNome($nomediretor);

    $nomesecretario = isset($_GET["secretario"]) ? explode("|", $_GET["secretario"])[1] : '';
    $nomesecretario = trataNome($nomesecretario);

    $nomesupervisor = '';
    $cgmSupervisor = '';
    /**
     * Autor: Uemerson Santana
     * Data: 27/11/2025
     * Demanda: 17982
     * Razão: Adicionada verificação de null para evitar erro fatal quando iRegente está vazio ou quando
     *        o Docente retornado não possui CGM válido. O método getDocenteByCodigoRecursosHumano() pode
     *        retornar um Docente mesmo quando o código está vazio, mas o CGM pode ser null, causando
     *        erro fatal ao chamar getNome() que tenta acessar $this->oCgm->getNome().
     *        A correção verifica se iRegente não está vazio, se o Docente foi retornado corretamente
     *        e se o CGM existe antes de tentar acessar seus métodos.
     */
    if (isset($oFiltroRelatorio->iRegente) && !empty($oFiltroRelatorio->iRegente)) {
        $xDocente = DocenteRepository::getDocenteByCodigoRecursosHumano($oFiltroRelatorio->iRegente);
        if ($xDocente && $xDocente->getCgm()) {
        $nomesupervisor = trataNome($xDocente->getNome());
        $oCgm = $xDocente->getCgm();
        $cgmSupervisor = is_object($oCgm) ? $oCgm->getCodigo() : $oCgm;
        }
    }

    // Recebe CGMs via GET
    /**
     * Autor: Uemerson Santana
     * Data: 07/10/2025
     * Demanda: 17874
     */
    $cgmDiretor = isset($_GET["cgmDiretor"]) ? $_GET["cgmDiretor"] : '';
    $cgmSecretario = isset($_GET["cgmSecretario"]) ? $_GET["cgmSecretario"] : '';

    // Busca matrículas dos assinantes
    $matriculaDiretor = '';
    $matriculaSecretario = '';
    $matriculaSupervisor = '';

    if (!empty($cgmDiretor)) {
        $iEscola = db_getsession("DB_coddepto");
        $sqlDir = "SELECT ed284_i_rhpessoal as matricula
                   FROM rechumanopessoal
                   INNER JOIN rechumano ON rechumano.ed20_i_codigo = rechumanopessoal.ed284_i_rechumano
                   INNER JOIN rechumanoescola ON rechumanoescola.ed75_i_rechumano = rechumano.ed20_i_codigo
                   INNER JOIN rhpessoal ON rhpessoal.rh01_regist = rechumanopessoal.ed284_i_rhpessoal
                   WHERE rh01_numcgm = {$cgmDiretor}
                     AND rechumanoescola.ed75_i_escola = {$iEscola}
                   LIMIT 1";
        $rsDir = db_query($sqlDir);
        if (pg_num_rows($rsDir) > 0) {
            $oDadosDir = db_utils::fieldsMemory($rsDir, 0);
            // Exibe a matrícula completa vinculada à escola (sem remover prefixo)
            $matriculaDiretor = !empty($oDadosDir->matricula) ? $oDadosDir->matricula : '';
        }
    }

    if (!empty($cgmSecretario)) {
        $iEscola = db_getsession("DB_coddepto");
        $sqlSec = "SELECT ed284_i_rhpessoal as matricula
                   FROM rechumanopessoal
                   INNER JOIN rechumano ON rechumano.ed20_i_codigo = rechumanopessoal.ed284_i_rechumano
                   INNER JOIN rechumanoescola ON rechumanoescola.ed75_i_rechumano = rechumano.ed20_i_codigo
                   INNER JOIN rhpessoal ON rhpessoal.rh01_regist = rechumanopessoal.ed284_i_rhpessoal
                   WHERE rh01_numcgm = {$cgmSecretario}
                     AND rechumanoescola.ed75_i_escola = {$iEscola}
                   LIMIT 1";
        $rsSec = db_query($sqlSec);
        if (pg_num_rows($rsSec) > 0) {
            $oDadosSec = db_utils::fieldsMemory($rsSec, 0);
            // Exibe a matrícula completa vinculada à escola (sem remover prefixo)
            $matriculaSecretario = !empty($oDadosSec->matricula) ? $oDadosSec->matricula : '';
        }
    }

    if (!empty($cgmSupervisor)) {
        $iEscola = db_getsession("DB_coddepto");
        $sqlSup = "SELECT ed284_i_rhpessoal as matricula
                   FROM rechumanopessoal
                   INNER JOIN rechumano ON rechumano.ed20_i_codigo = rechumanopessoal.ed284_i_rechumano
                   INNER JOIN rechumanoescola ON rechumanoescola.ed75_i_rechumano = rechumano.ed20_i_codigo
                   INNER JOIN rhpessoal ON rhpessoal.rh01_regist = rechumanopessoal.ed284_i_rhpessoal
                   WHERE rh01_numcgm = {$cgmSupervisor}
                     AND rechumanoescola.ed75_i_escola = {$iEscola}
                   LIMIT 1";
        $rsSup = db_query($sqlSup);
        if (pg_num_rows($rsSup) > 0) {
            $oDadosSup = db_utils::fieldsMemory($rsSup, 0);
            // Exibe a matrícula completa vinculada à escola (sem remover prefixo)
            $matriculaSupervisor = !empty($oDadosSup->matricula) ? $oDadosSup->matricula : '';
        }
    }

    // ===== NOVA LÓGICA: Layout uniforme com 3 colunas =====
    $oPdf->Cell(192, 6, "", 0, 1, "L", 0); // Espaço antes das assinaturas

    if ($nomesecretario != '') {
        // LAYOUT COM 3 ASSINATURAS (64 unidades cada = 192 total)
        // Linhas das assinaturas
        $oPdf->Cell(64, 6, "______________________________", 0, 0, "C", 0);
        $oPdf->Cell(64, 6, "______________________________", 0, 0, "C", 0);
        $oPdf->Cell(64, 6, "______________________________", 0, 1, "C", 0);

        // CORREÇÃO: Títulos dos cargos (Secretário no centro)
        $oPdf->Cell(64, 5, "Supervisor Escolar", 0, 0, "C", 0);
        $oPdf->Cell(64, 5, "Secretário", 0, 0, "C", 0);
        $oPdf->Cell(64, 5, "Diretor", 0, 1, "C", 0);

        // CORREÇÃO: Nomes (Secretário no centro)
        $oPdf->Cell(64, 5, $nomesupervisor, 0, 0, "C", 0);
        $oPdf->Cell(64, 5, $nomesecretario, 0, 0, "C", 0);
        $oPdf->Cell(64, 5, $nomediretor, 0, 1, "C", 0);

        // Matrículas
        $textoMatriculaSup = !empty($matriculaSupervisor) ? "Matrícula: " . $matriculaSupervisor : "";
        $textoMatriculaSec = !empty($matriculaSecretario) ? "Matrícula: " . $matriculaSecretario : "";
        $textoMatriculaDir = !empty($matriculaDiretor) ? "Matrícula: " . $matriculaDiretor : "";

        $oPdf->Cell(64, 5, $textoMatriculaSup, 0, 0, "C", 0);
        $oPdf->Cell(64, 5, $textoMatriculaSec, 0, 0, "C", 0);
        $oPdf->Cell(64, 5, $textoMatriculaDir, 0, 1, "C", 0);

    } else {
        // LAYOUT COM 2 ASSINATURAS (96 unidades cada = 192 total)
        // Linhas das assinaturas
        $oPdf->Cell(96, 6, "______________________________", 0, 0, "C", 0);
        $oPdf->Cell(96, 6, "______________________________", 0, 1, "C", 0);

        // Títulos dos cargos
        $oPdf->Cell(96, 5, "Supervisor Escolar", 0, 0, "C", 0);
        $oPdf->Cell(96, 5, "Diretor", 0, 1, "C", 0);

        // Nomes
        $oPdf->Cell(96, 5, $nomesupervisor, 0, 0, "C", 0);
        $oPdf->Cell(96, 5, $nomediretor, 0, 1, "C", 0);

        // Matrículas
        $textoMatriculaSup = !empty($matriculaSupervisor) ? "Matrícula: " . $matriculaSupervisor : "";
        $textoMatriculaDir = !empty($matriculaDiretor) ? "Matrícula: " . $matriculaDiretor : "";

        $oPdf->Cell(96, 5, $textoMatriculaSup, 0, 0, "C", 0);
        $oPdf->Cell(96, 5, $textoMatriculaDir, 0, 1, "C", 0);
    }
}

/**
 * Processa observações da escola
 */
function processarObservacoesEscola($oPdf, $oFiltroRelatorio) {
    $sObservacoesEscola = $oFiltroRelatorio->sObservacao;
    if ($sObservacoesEscola != null) {
        $oPdf->setfont('arial', '', 6);
        $oPdf->Cell(192, 4, "Observações:", 1, 1, "C", 0);

        if (mb_detect_encoding($sObservacoesEscola . 'x', 'UTF-8', 'ISO-8859-1') == 'UTF-8') {
            $sObservacoesEscola = utf8_decode($sObservacoesEscola);
            $sObservacoesEscola = str_replace(",", ',', $sObservacoesEscola);
        } else {
            $sObservacoesEscola = str_replace(",", ',', $sObservacoesEscola);
        }

        // ======= MODIFICAÇÃO 1: Calcular altura necessária =======
        $sObservacoesLimpa = trim($sObservacoesEscola); // Remove espaços extras
        $alturaTexto = $oPdf->NbLines(192, $sObservacoesLimpa) * 3; // Altura real do texto
        $alturaTotal = 4 + $alturaTexto + 2; // Título + texto + margem

        // Verificar se cabe na página atual
        if (($oPdf->GetY() + $alturaTotal) > 250) { // 250 = limite da página
            $oPdf->AddPage(); // Quebra página se não couber
        }
        // ========================================================

        $oPdf->setfont('arial', '', 6);

        // ======= MODIFICAÇÃO 2: Multicell sem quebras excessivas =======
        $oPdf->Multicell(192, 3, $sObservacoesLimpa, 1, 'J'); // Remove quebras de linha excessivas
        // ===============================================================

        // ======= MODIFICAÇÃO 3: Posicionamento ajustado =======
        // $oPdf->SetY($oPdf->GetY() + 2); // Apenas 2 unidades de espaçamento
        // =====================================================
    }
}

/**
 * Gera célula da ata
 */
function gerarAtaCelula($oPdf, $ataRespNome) {
    $sublinhado = "_______________________________________";
    $nome = ($ataRespNome === null) ? $sublinhado : $ataRespNome;
    $texto = "E para constar, eu " . $nome . ", lavrei a presente Ata que vai assinada pelas";

    $larguraTexto = $oPdf->GetStringWidth($texto) + 5;
    $oPdf->Cell($larguraTexto, 6, $texto, 0, 1, "L", 0);
    $oPdf->Cell(172, 6, "autoridades competentes.", 0, 1, "L", 0);
}

/**
 * Obtém responsável pela ata
 */
function getResponsavelAta($oFiltroRelatorio, $ataResp) {
    $sNomeDiretor = isset($oFiltroRelatorio->aDiretor[1]) ? $oFiltroRelatorio->aDiretor[1] : '';
    $sNomeSecretario = isset($oFiltroRelatorio->aSecretarioAlt[1]) ? $oFiltroRelatorio->aSecretarioAlt[1] : '';

    switch ($ataResp) {
        case 1: // Diretor
            return $sNomeDiretor;
        case 2: // Secretário
            return $sNomeSecretario;
        default:
            return null;
    }
}

// Funções auxiliares existentes mantidas
function testa($var) {
    echo "<pre>";
    print_r($var);
    echo "</pre>";
}

function dadosRegencia($codregencia) {
    $sql = pg_query("SELECT * from regencia where regencia.ed59_i_codigo = {$codregencia}");
    $resultado = pg_fetch_all($sql);
    return $resultado;
}

function voltaFaltas($ed95_i_serie, $ed59_i_turma, $matriculaa) {
    $sql1 = pg_query("SELECT
                    diario.*,
					ed59_i_codigo
					from
					diario
					inner join aluno on ed47_i_codigo = ed95_i_aluno
					inner join matricula on ed60_i_aluno = ed47_i_codigo
					inner join matriculaserie on ed60_i_codigo = ed221_i_matricula
					inner join regencia on ed59_i_codigo = ed95_i_regencia and ed59_i_serie = ed221_i_serie
					where
					ed60_i_codigo = {$matriculaa}
					and
					ed95_i_regencia = ed59_i_codigo
					and
					ed95_i_serie = {$ed95_i_serie}
					and
					ed59_i_turma = {$ed59_i_turma}
					order by ed95_i_codigo");

    $r1 = pg_fetch_all($sql1);
    $r1 = $r1[0]["ed95_i_aluno"];

    $sql = pg_query("SELECT
                   sum(ed72_i_numfaltas) as numero_faltas
                   from
				   diarioavaliacao
				   inner join procavaliacao on ed41_i_codigo       = ed72_i_procavaliacao
				   inner join diario        on ed95_i_codigo       = ed72_i_diario
				   left join pareceraval on ed93_i_diarioavaliacao = ed72_i_codigo
				   left join abonofalta on ed80_i_diarioavaliacao  = ed72_i_codigo
				   where
				   ed95_i_aluno = {$r1}");

    $resultado = pg_fetch_all($sql);
    return $resultado[0]["numero_faltas"];
}

function trataNome($nome) {
    $tratanome = explode(" ", mb_strtolower($nome));
    $nometratado = "";
    foreach ($tratanome as $particula) {
        if (strlen($particula) > 2) {
            $nometratado .= ucfirst($particula) . " ";
        } else {
            $nometratado .= $particula . " ";
        }
    }
    $nometratado = trim($nometratado);
    $nometratado = mb_strtoupper(mb_substr($nometratado, 0, 1)) . mb_substr($nometratado, 1);
    return $nometratado;
}

/**
 * Autor: Uemerson Santana
 * Data: 06/02/2026
 * Demanda: 18250
 * Razao: Corrigido para consultar primeiro o resultado da tela de encerramento
 *        (diarioalunoresultadofinal.ed165_resultado_final) antes de usar o
 *        resultado individual por disciplina (diariofinal.ed74_c_resultadofinal).
 *        Isso corrige alunos reclassificados que apareciam como REP na ATA
 *        quando estavam APROVADOS no encerramento.
 */
function buscaRFeja($matricula, $turma, $serie) {
    // 1. Consultar resultado oficial do encerramento (diarioalunoresultadofinal)
    $sqlEncerramento = "SELECT darf.ed165_resultado_final
                        FROM diarioalunoresultadofinal darf
                        INNER JOIN diarioaluno da ON da.ed161_codigo = darf.ed165_diarioaluno
                        WHERE da.ed161_aluno = {$matricula}
                          AND da.ed161_turma = {$turma}
                          AND da.ed161_serie = {$serie}
                        LIMIT 1";
    $resultEncerramento = pg_query($sqlEncerramento);
    if ($resultEncerramento && pg_num_rows($resultEncerramento) > 0) {
        $dadosEncerramento = pg_fetch_assoc($resultEncerramento);
        $rfEncerramento = trim($dadosEncerramento['ed165_resultado_final']);
        if ($rfEncerramento == 'A') {
            return "AP";
        } elseif ($rfEncerramento == 'R') {
            return "REP";
        }
    }

    // 2. Fallback: consultar diariofinal (comportamento anterior)
    $sql1 = "SELECT
                   ed74_c_resultadofinal,
				   ed74_c_resultadofreq
				   FROM
				   diario
				   inner join aluno on ed47_i_codigo = ed95_i_aluno
				   inner join diariofinal on ed74_i_diario = ed95_i_codigo
				   WHERE
				   ed95_i_aluno = {$matricula}
				   AND
				   ed95_i_regencia
				   in (select
				       ed59_i_codigo
					   from
					   regencia
					   where
					   ed59_i_turma = {$turma}
					   and
					   ed59_i_serie = {$serie}
					   and
					   ed59_c_condicao = 'OB')";

    $sql = pg_query($sql1);
    $resultado = pg_fetch_all($sql);

    if (trim($resultado[0]["ed74_c_resultadofinal"]) == '' and $resultado[0]["ed74_c_resultadofreq"] == 'A') {
        $resultado[0]["ed74_c_resultadofinal"] = 'A';
    }

    if ($resultado[0]["ed74_c_resultadofinal"] == "A") {
        $resultadofinal = "AP";
    } elseif ($resultado[0]["ed74_c_resultadofinal"] == "R") {
        $resultadofinal = "REP";
    } else {
        $resultadofinal = "";
    }

    return $resultadofinal;
}

function buscaFaltasAnosIniciais($codregencia, $codaluno) {
    $sql = pg_query("SELECT
                   sum(ed72_i_numfaltas) as numero_faltas
				   FROM
				   diarioavaliacao
				   INNER JOIN diario ON ed72_i_diario = ed95_i_codigo
				   INNER JOIN regencia ON ed59_i_codigo = ed95_i_regencia
				   WHERE
				   ed59_i_codigo = {$codregencia}
				   AND
				   ed95_i_aluno = {$codaluno}");

    $resultado = pg_fetch_all($sql);
    return $resultado[0]["numero_faltas"];
}

// Configuração inicial
$oJson = new Services_JSON();
$oGet = db_utils::postMemory($_GET);

$aTurmas = $oJson->decode(str_replace("\\", "", $oGet->turmas));
$oFiltroRelatorio = new stdClass();
$oFiltroRelatorio->iModelo = $oGet->modelo;
$oFiltroRelatorio->iOrdenacao = $oGet->ordenacao;
$oFiltroRelatorio->iFrequencia = $oGet->frequencia;
$oFiltroRelatorio->iCodigoTipoModelo = $oGet->tipovar;
$oFiltroRelatorio->iTrocaTurma = $oGet->trocaTurma;
$oFiltroRelatorio->aDiretor = array();
$oFiltroRelatorio->aSecretario = array();
$oFiltroRelatorio->lTemDiretor = false;
$oFiltroRelatorio->lTemSecretario = false;
$oFiltroRelatorio->lBrasao = false;
$oFiltroRelatorio->lTransferencia = false;
$oFiltroRelatorio->lAssinatura = false;
$oFiltroRelatorio->aJustificativas = array();
$lObservacaoProgressaoParcial = false;
$oFiltroRelatorio->iTipoModelo = 1;
$oFiltroRelatorio->mCabecalho = '';
$oFiltroRelatorio->mRodape = '';
$oFiltroRelatorio->mObservacao = '';
$oFiltroRelatorio->sObservacao = $oGet->sObservacao;
$oFiltroRelatorio->iImprimirRegente = $oGet->imprimirNomeRegente;
$oFiltroRelatorio->sCabecalho = '';
$oFiltroRelatorio->iTamanhoColunaResultado = $oFiltroRelatorio->iCodigoTipoModelo == 4 ? 7 : 6;

// Configurações baseadas nos parâmetros GET
if ($oGet->transfer == 'yes') {
    $oFiltroRelatorio->lTransferencia = true;
}

if ($oGet->brasao == 'b1') {
    $oFiltroRelatorio->lBrasao = true;
}

if (!empty($oGet->diretor)) {
    $oGet->diretor = db_stdClass::normalizeStringJsonEscapeString($oGet->diretor);
    $oFiltroRelatorio->aDiretor = explode("|", $oGet->diretor);
    $oFiltroRelatorio->lTemDiretor = true;
}

if (!empty($oGet->secretario)) {
    $oGet->secretario = db_stdClass::normalizeStringJsonEscapeString($oGet->secretario);
    $oFiltroRelatorio->aSecretarioAlt = explode("|", $oGet->secretario);
    $oGet->secretario = "Nome Secretário";
    $oFiltroRelatorio->aSecretario = explode("|", $oGet->secretario);
    $oFiltroRelatorio->lTemSecretario = true;
}

if (!empty($oGet->iRegente)) {
    $oFiltroRelatorio->iRegente = $oGet->iRegente;
}

if (!empty($oGet->iAtividade)) {
    $oFiltroRelatorio->iAtividade = $oGet->iAtividade;
}

// Configuração do PDF baseado no modelo
if (in_array($oFiltroRelatorio->iModelo, array(1, 2))) {
    require_once(modification("fpdf151educacao/pdfwebseller.php"));
} else if (in_array($oFiltroRelatorio->iModelo, array(3, 4))) {
    require_once(modification("fpdf151educacao/scpdf.php"));
}

if ($oFiltroRelatorio->iModelo == 2 || $oFiltroRelatorio->iModelo == 4) {
    $oFiltroRelatorio->lAssinatura = true;
}

// Configuração do responsável pela ata
$oFiltroRelatorio->iAtaResp = $oGet->ataResp;
$oFiltroRelatorio->ataRespNome = getResponsavelAta($oFiltroRelatorio, $oFiltroRelatorio->iAtaResp);

// Verificação de parâmetros educacionais
$iEscola = db_getsession("DB_coddepto");
$oDaoEduParametros = new cl_edu_parametros();
$sCamposEduParametros = "ed233_c_decimais, ed233_c_limitemov";
$sWhereEduParametros = "ed233_i_escola = {$iEscola}";
$sSqlEduParametros = $oDaoEduParametros->sql_query_file(null, $sCamposEduParametros, null, $sWhereEduParametros);
$rsEduParametros = db_query($sSqlEduParametros);

if (is_resource($rsEduParametros) && pg_num_rows($rsEduParametros) > 0) {
    $oDadosEduParametro = db_utils::fieldsMemory($rsEduParametros, 0);
    $oFiltroRelatorio->sDecimais = $oDadosEduParametro->ed233_c_decimais;
    $oFiltroRelatorio->sLimiteMovimentacao = $oDadosEduParametro->ed233_c_limitemov;
}

// Busca dados de edu_relatmodel
if (is_numeric($oFiltroRelatorio->iCodigoTipoModelo)) {
    $oDaoRelatModel = new cl_edu_relatmodel();
    $sCampoRelatModel = "ed217_t_cabecalho, ed217_t_rodape, ed217_t_obs, ed217_i_tipomodelo";
    $sWhereRelatModel = "ed217_i_codigo = {$oFiltroRelatorio->iCodigoTipoModelo}";
    $sSqlRelatModel = $oDaoRelatModel->sql_query(null, $sCampoRelatModel, null, $sWhereRelatModel);
    $rsRelatModel = db_query($sSqlRelatModel);

    if (is_resource($rsRelatModel) && pg_num_rows($rsRelatModel) > 0) {
        $oDadosRelatModel = db_utils::fieldsMemory($rsRelatModel, 0);
        $oFiltroRelatorio->mCabecalho = $oDadosRelatModel->ed217_t_cabecalho;
        $oFiltroRelatorio->mRodape = $oDadosRelatModel->ed217_t_rodape;
        $oFiltroRelatorio->mObservacao = $oDadosRelatModel->ed217_t_obs;
        $oFiltroRelatorio->iTipoModelo = $oDadosRelatModel->ed217_i_tipomodelo;
    }
}

// Processamento principal baseado no modelo
switch ($oFiltroRelatorio->iModelo) {
    case ($oFiltroRelatorio->iModelo == 1 || $oFiltroRelatorio->iModelo == 2):
        $oPdf = new PDF();
        $oPdf->Open();
        $oPdf->AliasNbPages();
        $oPdf->SetAutoPageBreak(false);
        $oPdf->imprime_rodape = false;

        processarTurmasModelo12($oPdf, $aTurmas, $oFiltroRelatorio);
        break;

    case ($oFiltroRelatorio->iModelo == 3 || $oFiltroRelatorio->iModelo == 4):
        $oPdf = new scpdf();
        $oPdf->Open();
        $oPdf->AliasNbPages();
        $oPdf->SetAutoPageBreak(false);

        processarTurmasModelo34($oPdf, $aTurmas, $oFiltroRelatorio);
        break;
}

$oPdf->Output();

/**
 * Processa turmas para modelos 1 e 2
 */
function processarTurmasModelo12($oPdf, $aTurmas, $oFiltroRelatorio) {
    for ($iContadorTurma = 0; $iContadorTurma < count($aTurmas); $iContadorTurma++) {
        $oFiltroRelatorio->iTotalDisciplinasPorPagina = 7;
        $oFiltroRelatorio->iTotalAlunosPorPagina = 45;
        $aAlunosComBaixaFrequencia = array();
        $oTurma = TurmaRepository::getTurmaByCodigo($aTurmas[$iContadorTurma]->turma);

        $iCodigoEtapa = $aTurmas[$iContadorTurma]->etapa;
        $tipoEnsino = determinarTipoEnsino($oTurma, $iCodigoEtapa);

        dadosEscola($oTurma, $iCodigoEtapa);

        $novotexto = gerarTextoTipo($oTurma, $iCodigoEtapa, $tipoEnsino);

        global $head1;
        global $head2;

        $head1 = "ATA DE RESULTADOS FINAIS";
        $head2 = $novotexto;

        $oPdf->AddPage('P');
        $oPdf->SetFont('arial', 'b', 7);

        corpoPdf($oPdf, $oTurma, $oFiltroRelatorio, $aAlunosComBaixaFrequencia, $iCodigoEtapa);

        if ($oFiltroRelatorio->lAssinatura) {
            assinaturaDocente($oPdf, $oTurma, $oFiltroRelatorio, $iCodigoEtapa);
        }

        TurmaRepository::removerTurma($oTurma);
        unset($oTurma);
    }
}

/**
 * Processa turmas para modelos 3 e 4
 */
function processarTurmasModelo34($oPdf, $aTurmas, $oFiltroRelatorio) {
    for ($iContadorTurma = 0; $iContadorTurma < count($aTurmas); $iContadorTurma++) {
        $oPdf->AddPage();
        $oFiltroRelatorio->iTotalDisciplinasPorPagina = 7;
        $oFiltroRelatorio->iTotalAlunosPorPagina = 35;

        if ($oFiltroRelatorio->iCodigoTipoModelo == 3) {
            $oFiltroRelatorio->iTotalAlunosPorPagina = 45;
        }

        $aAlunosComBaixaFrequencia = array();
        $oTurma = TurmaRepository::getTurmaByCodigo($aTurmas[$iContadorTurma]->turma);
        $iCodigoEtapa = $aTurmas[$iContadorTurma]->etapa;

        dadosEscola($oTurma, $iCodigoEtapa);

        corpoPdf($oPdf, $oTurma, $oFiltroRelatorio, $aAlunosComBaixaFrequencia, $iCodigoEtapa);
        footerPadrao($oPdf, $oTurma, $oFiltroRelatorio, $iCodigoEtapa);

        if ($oFiltroRelatorio->lAssinatura) {
            assinaturaDocente($oPdf, $oTurma, $oFiltroRelatorio, $iCodigoEtapa);
        }

        TurmaRepository::removerTurma($oTurma);
        unset($oTurma);
    }
}

/**
 * Função principal do corpo do PDF - refatorada para reduzir repetição
 */
function corpoPdf(FPDF $oPdf, Turma $oTurma, $oFiltroRelatorio, $aAlunosComBaixaFrequencia, $iCodigoEtapa) {
    global $lObservacaoProgressaoParcial;

    $oEtapa = EtapaRepository::getEtapaByCodigo($iCodigoEtapa);
    $iAnoCalendario = $oTurma->getCalendario()->getAnoExecucao();
    $aDisciplinas = $oTurma->getDisciplinasPorEtapa($oEtapa);

    // Configurações iniciais
    $oFiltroRelatorio->lCalculaFrequencia = $oTurma->getProcedimentoDeAvaliacaoDaEtapa($oEtapa)->getFormaCalculoFrequencia();
    $oFiltroRelatorio->iTamanhoColunaAbrevDisciplina = 16;
    $oFiltroRelatorio->iAuxiliarTransferido = 7;
    $oFiltroRelatorio->iTamanhoTotalColunaDisciplina = 65;

    if ($oFiltroRelatorio->iFrequencia == 1 || $oFiltroRelatorio->lCalculaFrequencia == 2) {
        $oFiltroRelatorio->iTamanhoColunaAbrevDisciplina = 11;
        $oFiltroRelatorio->iTotalDisciplinasPorPagina = 10;
        $oFiltroRelatorio->iAuxiliarTransferido = 10;
    }

    $oFiltroRelatorio->iAltura = 4;
    $oPdf->SetFillColor(225, 225, 225);

    // Determinar tipo de ensino
    $tipoEnsino = determinarTipoEnsino($oTurma, $iCodigoEtapa);

    // Processar baseado no tipo
    switch ($tipoEnsino['tipo']) {
        case 'educacao_infantil':
        case 'ef_anos_iniciais_1ano':
        case 'eja_iniciais_ciclo_basico':
            processarEnsinoSimples($oPdf, $oTurma, $oFiltroRelatorio, $iCodigoEtapa, $tipoEnsino, $aDisciplinas, $oEtapa);
            break;

        case 'ef_anos_iniciais_2a5':
        case 'ef_anos_finais':
        case 'eja_iniciais_ciclos':
        case 'eja_finais_ciclos':
            processarEnsinoComplexo($oPdf, $oTurma, $oFiltroRelatorio, $iCodigoEtapa, $tipoEnsino, $aDisciplinas, $oEtapa, $iAnoCalendario);
            break;

        default:
            processarEnsinoPadrao($oPdf, $oTurma, $oFiltroRelatorio, $iCodigoEtapa, $aDisciplinas, $oEtapa, $iAnoCalendario);
            break;
    }
}

/**
 * Processa ensinos simples (Educação Infantil, EF 1º ano, EJA Ciclo Básico)
 */
function processarEnsinoSimples($oPdf, $oTurma, $oFiltroRelatorio, $iCodigoEtapa, $tipoEnsino, $aDisciplinas, $oEtapa) {
    $aDisciplinasPagina = array();
    cabecalhoPadrao($oPdf, $oFiltroRelatorio, $aDisciplinasPagina, $oTurma, $iCodigoEtapa);

    $aListaDeAlunos = $oTurma->getAlunosMatriculadosNaTurmaPorSerie($oEtapa);

    // Ordenação se necessário
    switch ($oFiltroRelatorio->iOrdenacao) {
        case 2:
        case 3:
            usort($aListaDeAlunos, "ordernarAlunosPorNome");
            break;
    }

    /*
     * Autor: Uemerson Santana
     * Demanda: 17826
     * Data: 15/09/2025
     */
    // FILTRAR ALUNOS TRANSFERIDOS QUE RETORNARAM - CORREÇÃO PARA EVITAR DUPLICATAS
    $aListaDeAlunosFiltrada = [];
    $aAlunosPorCodigo = [];

    // Agrupar matrículas por código do aluno
    foreach ($aListaDeAlunos as $oMatricula) {
        $iCodigoAluno = $oMatricula->getAluno()->getCodigoAluno();
        if (!isset($aAlunosPorCodigo[$iCodigoAluno])) {
            $aAlunosPorCodigo[$iCodigoAluno] = [];
        }
        $aAlunosPorCodigo[$iCodigoAluno][] = $oMatricula;
    }

    // Para cada aluno, verificar se há matrícula ativa
    foreach ($aAlunosPorCodigo as $iCodigoAluno => $aMatriculasAluno) {
        $lTemMatriculado = false;
        $oMatriculaAtiva = null;

        // Verificar se existe matrícula com situação "MATRICULADO"
        foreach ($aMatriculasAluno as $oMatricula) {
            if (trim($oMatricula->getSituacao()) == "MATRICULADO") {
                $lTemMatriculado = true;
                $oMatriculaAtiva = $oMatricula;
                break;
            }
        }

        if ($lTemMatriculado) {
            // Se há matrícula ativa, incluir apenas ela
            $aListaDeAlunosFiltrada[] = $oMatriculaAtiva;
        } else {
            // Se não há matrícula ativa, incluir todas (transferências)
            $aListaDeAlunosFiltrada = array_merge($aListaDeAlunosFiltrada, $aMatriculasAluno);
        }
    }

    // Substituir a lista original pela filtrada
    $aListaDeAlunos = $aListaDeAlunosFiltrada;

    if (!empty($aDisciplinas)) {
        $codregencia = $aDisciplinas[0]->getCodigo();
        $dadosregencia = dadosRegencia($codregencia);
        $xserie = $dadosregencia[0]["ed59_i_serie"];
        $xturma = $dadosregencia[0]["ed59_i_turma"];
        $diasletivos = $aDisciplinas[0]->getTurma()->getCalendario()->getDiasLetivos();

        if ($tipoEnsino['tipo'] == 'ef_anos_iniciais_1ano') {
            $diasletivos = retornaaulasdadas($codregencia);
        }
    }

    $contador = 1;
    $numeroSequencial = 1; // Contador sequencial para exibição
    foreach ($aListaDeAlunos as $aluno) {
        $dados = array();

        // ===== VERIFICAÇÃO INICIAL: Deve exibir este aluno? =====
        $deveExibirAluno = true;

        // Verificar filtro de troca de turma
        if ($oFiltroRelatorio->iTrocaTurma == 1 && $aluno->getSituacao() == "TROCA DE TURMA" || $aluno->getSituacao() == "TROCA DE MODALIDADE") {
            $deveExibirAluno = false;
        }

        // Se não deve exibir, pula para o próximo
        if (!$deveExibirAluno) {
            $contador++; // Incrementa contador interno mas não o sequencial
            continue;
        }

        // ===== PROCESSAMENTO DOS DADOS DO ALUNO =====
        // Calcular dados específicos baseado no tipo
        if (!empty($aDisciplinas)) {
            $matriculaaluno = $aluno->getMatricula();
            $codaluno = $aluno->getAluno()->getCodigoAluno();

            switch ($tipoEnsino['tipo']) {
                case 'educacao_infantil':
                    /**
                     * CORREÇÃO: Calcular frequência diretamente do diário
                     *
                     * @author Uemerson Santana
                     * @date 04/11/2025
                     * @demanda 17412
                     *
                     * MOTIVO DA ALTERAÇÃO:
                     * Alunos de educação infantil apareciam com 0% de frequência na ATA de Resultados Finais,
                     * mesmo tendo frequência correta na Ficha Individual do Aluno.
                     *
                     * PROBLEMA IDENTIFICADO:
                     * - GradeAproveitamentoAluno->getDadosFrequenciaDaDiscplina() usa getDisciplinasPorRegencia()
                     * - getDisciplinasPorRegencia() depende de getDisciplinas() que busca por etapa de origem
                     * - Quando a regência não corresponde exatamente à busca interna, retorna null
                     * - Com null, o percentual de frequência fica 0% mesmo com dados corretos no banco
                     *
                     * SOLUÇÃO IMPLEMENTADA:
                     * - Buscar o diário diretamente pela matrícula, série de origem e regência
                     * - Calcular frequência manualmente: floor(((total_aulas - faltas_sem_abono) / total_aulas) * 100)
                     * - Usar floor() para obter parte inteira (padrão educação infantil)
                     * - Manter cálculo original como fallback se método direto não encontrar dados
                     */
                    $iCodigoTurma = $oTurma->getCodigo();
                    $codregenciaAluno = buscaCodigoRegenciaPorMatricula($iCodigoTurma, $matriculaaluno);

                    // Se não encontrou regência específica, usar a padrão como fallback
                    if (empty($codregenciaAluno)) {
                        $codregenciaAluno = $codregencia;
                    }

                    // Calcular frequência diretamente do diário (sem usar GradeAproveitamentoAluno)
                    $oDadosFrequencia = calcularFrequenciaDireta($matriculaaluno, $codregenciaAluno);

                    // Se cálculo direto retornou 0%, tentar método original como fallback
                    if ($oDadosFrequencia->nPercentualFrequencia == 0 && $oDadosFrequencia->iTotalAulas == 0) {
                        try {
                            $oDadosFrequenciaFallback = calcularFrequenciaAluno($matriculaaluno, $codregenciaAluno);
                            if ($oDadosFrequenciaFallback->nPercentualFrequencia > 0) {
                                $oDadosFrequencia = $oDadosFrequenciaFallback;
                            }
                        } catch (Exception $e) {
                            // Manter resultado do cálculo direto
                        }
                    }

                    $dados['frequencia'] = $oDadosFrequencia->nPercentualFrequencia;
                    break;

                case 'ef_anos_iniciais_1ano':
                    /**
                     * Autor: Uemerson Santana
                     * Data: 13/02/2026
                     * Demanda: 18250
                     * Razao: calcularFrequenciaAluno() usa GradeAproveitamentoAluno que falha para
                     *        alguns alunos do 1o ano, retornando 0%. Como a frequencia ficava abaixo
                     *        de 75%, o codigo original forcava 75% para todos (quando freq_global='A'),
                     *        inclusive alunos com frequencia real acima de 75%.
                     *        Corrige usando calcularFrequenciaDireta() (SQL direto) como metodo primario,
                     *        com fallback em calcularFrequenciaAluno(). Somente alunos reclassificados
                     *        por baixa frequencia terao frequencia ajustada para 75%.
                     */
                    /**
                     * Autor: Uemerson Santana
                     * Data: 16/02/2026
                     * Demanda: 18250
                     * Razao: buscaCodigoRegenciaPorMatricula() retornava a primeira regencia por
                     *        codigo (ex: QUIMICA, freq_global='A'), que nao possui dados de
                     *        aulas em regenciaperiodo. Para calcular frequencia, e necessario usar
                     *        a regencia com freq_global='FA' (ex: HISTORIA), que e a unica
                     *        que armazena aulas dadas e faltas reais.
                     */
                    $iCodigoTurma1ano = $oTurma->getCodigo();
                    $sqlRegFA1ano = "SELECT ed59_i_codigo FROM regencia"
                                  . " WHERE ed59_i_turma = {$iCodigoTurma1ano}"
                                  . " AND trim(ed59_c_freqglob) = 'FA'"
                                  . " LIMIT 1";
                    $rsRegFA1ano = db_query($sqlRegFA1ano);
                    if ($rsRegFA1ano && pg_num_rows($rsRegFA1ano) > 0) {
                        $codregenciaAluno1ano = pg_fetch_result($rsRegFA1ano, 0, 0);
                    } else {
                        $codregenciaAluno1ano = buscaCodigoRegenciaPorMatricula($iCodigoTurma1ano, $matriculaaluno);
                        if (empty($codregenciaAluno1ano)) {
                            $codregenciaAluno1ano = $codregencia;
                        }
                    }
                    $oDadosFrequencia = calcularFrequenciaDireta($matriculaaluno, $codregenciaAluno1ano);
                    if ($oDadosFrequencia->nPercentualFrequencia == 0 && $oDadosFrequencia->iTotalAulas == 0) {
                        try {
                            $oDadosFrequenciaFallback = calcularFrequenciaAluno($matriculaaluno, $codregenciaAluno1ano);
                            if ($oDadosFrequenciaFallback->nPercentualFrequencia > 0) {
                                $oDadosFrequencia = $oDadosFrequenciaFallback;
                            }
                        } catch (Exception $e) {
                        }
                    }
                    $dados['frequencia'] = $oDadosFrequencia->nPercentualFrequencia;

                    $rf = buscaRFeja($codaluno, $xturma, $xserie);

                    // Somente reclassificados por baixa frequencia tem freq ajustada para 75%
                    if ($dados['frequencia'] < 75) {
                        $lReclassificado1ano = false;
                        try {
                            $lReclassificado1ano = $aluno->getDiarioDeClasse()->reclassificadoPorBaixaFrequencia();
                        } catch (Exception $e) {
                        }
                        if ($lReclassificado1ano) {
                            $dados['frequencia'] = 75;
                            $rf = buscaRFeja($codaluno, $xturma, $xserie);
                        }
                    }

                    $dados['resultado_final'] = $rf;
                    break;
                case 'eja_iniciais_ciclo_basico':
                    $rf = resultado_final_rpc($xserie, $xturma, $matriculaaluno, $oTurma->getCalendario()->getAnoExecucao());
                    $oDadosFrequencia = calcularFrequenciaAluno($matriculaaluno, $codregencia);
                    $dados['frequencia'] = $oDadosFrequencia->nPercentualFrequencia;
                    $dados['resultado_final'] = $rf;
                    break;
            }
        }

        // ===== CHAMADA DA FUNÇÃO DE PROCESSAMENTO =====
        processarAlunoSimples($oPdf, $aluno, $contador, $oFiltroRelatorio, $tipoEnsino['tipo'], $dados, $numeroSequencial);

        // ===== INCREMENTO DOS CONTADORES =====
        $contador++; // Contador interno sempre incrementa
        $numeroSequencial++; // Contador sequencial só incrementa quando aluno é exibido
    }

    // Observações e assinaturas
    processarObservacoesEscola($oPdf, $oFiltroRelatorio);

    $oPdf->Cell(10, 6, "", 0, 0, "C", 0);
    $oPdf->Cell(172, 6, "", "T", 1, "L", 0);
    gerarAtaCelula($oPdf, $oFiltroRelatorio->ataRespNome);

    gerarAssinaturasPadrao($oPdf, $oFiltroRelatorio);
}

/**
 * Processa ensinos complexos com disciplinas
 *
 * Esta função é responsável por gerar o conteúdo da ATA para tipos de ensino que possuem
 * disciplinas específicas (como Ensino Fundamental Anos Iniciais 2º-5º, Anos Finais,
 * EJA Iniciais/Finais por Ciclos). Diferente dos ensinos simples, estes exigem
 * processamento detalhado de múltiplas disciplinas por aluno.
 *
 * TIPOS DE ENSINO SUPORTADOS:
 * - ef_anos_iniciais_2a5: 2º, 3º, 4º e 5º anos do Ensino Fundamental
 * - ef_anos_finais: 6º ao 9º ano do Ensino Fundamental
 * - eja_iniciais_ciclos: 1º e 2º Ciclo da EJA Anos Iniciais
 * - eja_finais_ciclos: 3º e 4º Ciclo da EJA Anos Finais
 *
 * FUNCIONALIDADES PRINCIPAIS:
 * - Organização de disciplinas por página (limite configurável)
 * - Processamento de alunos matriculados e transferidos
 * - Cálculo de aproveitamento por disciplina
 * - Geração de resultado final consolidado
 * - Cálculo de frequência global ou por disciplina
 * - Suporte a progressão parcial/dependência
 * - Tratamento de amparo e necessidades especiais
 *
 * CORREÇÕES APLICADAS (Versão 2.1):
 *
 * 1. FILTRO DE TROCA DE MODALIDADE:
 *    - Exclusão de alunos com situação "TROCA DE MODALIDADE" além de "TROCA DE TURMA"
 *    - Aplicado quando parâmetro iTrocaTurma = 1
 *
 * 2. PRESERVAÇÃO DE CONCEITOS PARA DISCIPLINAS TIPO "NIVEL":
 *    - Detecção automática de turmas que utilizam conceitos (A, B, C, D)
 *    - Impedimento de conversão de conceitos em termos de encerramento ("Apr", "Rep")
 *    - Busca específica do Conceito Final (CF) nas avaliações de resultado
 *    - Preservação dos valores originais A, B, C, D na ATA
 *
 * 3. NUMERAÇÃO SEQUENCIAL CORRIGIDA:
 *    - Contador sequencial que só incrementa para alunos exibidos
 *    - Respeita filtros de exclusão (transferidos, troca de turma/modalidade)
 *    - Compatível com ordenação alfabética e por número de chamada
 *
 * FLUXO DE PROCESSAMENTO:
 * 1. Organização das disciplinas em páginas (máximo 7-10 por página)
 * 2. Obtenção da lista de alunos (alfabética ou numérica)
 * 3. Detecção do tipo de avaliação (NOTA, NIVEL, PARECER)
 * 4. Loop por página de disciplinas
 * 5. Loop por aluno com aplicação de filtros
 * 6. Processamento de cada disciplina do aluno
 * 7. Cálculo do resultado final consolidado
 * 8. Renderização no PDF com quebra de página automática
 *
 * @author Uemerson Santana
 * @demanda #17412
 * @version 2.1 (Corrigida - Conceitos e Troca de Modalidade e exibição de conceitos - NIVEL)
 * @date 01/09/2025
 */
function processarEnsinoComplexo($oPdf, $oTurma, $oFiltroRelatorio, $iCodigoEtapa, $tipoEnsino, $aDisciplinas, $oEtapa, $iAnoCalendario) {
    global $lObservacaoProgressaoParcial;

    $aDisciplinasPorPagina = array();
    $iContadorAux = 0;
    $iPagina = 0;
    $aListaDeAlunos = array();
    $lSequencialDiario = true;

    // Organizar disciplinas por página
    foreach ($aDisciplinas as $oDisciplina) {
        if (!$oDisciplina->isLancadaNoHistorico()) {
            continue;
        }
        $aDisciplinasPorPagina[$iPagina][$iContadorAux] = $oDisciplina;
        $iTotalContadorAux = 6;

        if ($oFiltroRelatorio->iFrequencia == 1 || $oFiltroRelatorio->lCalculaFrequencia == 2) {
            $iTotalContadorAux = 9;
        }
        if ($iContadorAux >= $iTotalContadorAux) {
            $iPagina++;
            $iContadorAux = -1;
        }
        $iContadorAux++;
    }

    $oFiltroRelatorio->iContadorDisciplinasImpressas = $oFiltroRelatorio->iTotalDisciplinasPorPagina;

    // Escolher método de busca baseado no tipo
    if ($tipoEnsino['tipo'] == 'ef_anos_finais') {
        $aListaDeAlunos = $oTurma->getAlunosMatriculadosNaTurmaPorSerieAlfa($oEtapa);
    } else {
        $aListaDeAlunos = $oTurma->getAlunosMatriculadosNaTurmaPorSerie($oEtapa);
    }

    // Ordenação se necessário
    switch ($oFiltroRelatorio->iOrdenacao) {
        case 2:
        case 3:
            usort($aListaDeAlunos, "ordernarAlunosPorNome");
            break;
    }

    /*
     * Autor: Uemerson Santana
     * Demanda: 17826
     * Data: 15/09/2025
     */
    // FILTRAR ALUNOS TRANSFERIDOS QUE RETORNARAM - CORREÇÃO PARA EVITAR DUPLICATAS
    $aListaDeAlunosFiltrada = [];
    $aAlunosPorCodigo = [];

    // Agrupar matrículas por código do aluno
    foreach ($aListaDeAlunos as $oMatricula) {
        $iCodigoAluno = $oMatricula->getAluno()->getCodigoAluno();
        if (!isset($aAlunosPorCodigo[$iCodigoAluno])) {
            $aAlunosPorCodigo[$iCodigoAluno] = [];
        }
        $aAlunosPorCodigo[$iCodigoAluno][] = $oMatricula;
    }

    // Para cada aluno, verificar se há matrícula ativa
    foreach ($aAlunosPorCodigo as $iCodigoAluno => $aMatriculasAluno) {
        $lTemMatriculado = false;
        $oMatriculaAtiva = null;

        // Verificar se existe matrícula com situação "MATRICULADO"
        foreach ($aMatriculasAluno as $oMatricula) {
            if (trim($oMatricula->getSituacao()) == "MATRICULADO") {
                $lTemMatriculado = true;
                $oMatriculaAtiva = $oMatricula;
                break;
            }
        }

        if ($lTemMatriculado) {
            // Se há matrícula ativa, incluir apenas ela
            $aListaDeAlunosFiltrada[] = $oMatriculaAtiva;
        } else {
            // Se não há matrícula ativa, incluir todas (transferências)
            $aListaDeAlunosFiltrada = array_merge($aListaDeAlunosFiltrada, $aMatriculasAluno);
        }
    }

    // Substituir a lista original pela filtrada
    $aListaDeAlunos = $aListaDeAlunosFiltrada;

    $iTotalAlunosMatriculados = count($aListaDeAlunos);

    // DEBUG: Verificar se há alunos
    if ($iTotalAlunosMatriculados == 0) {
        error_log("ERRO: Nenhum aluno encontrado para processamento. Tipo: " . $tipoEnsino['tipo']);
        return;
    }

    // Inicializar variáveis de controle
    $iAlunosImpressos = 0;
    $lPrimeiroLaco = true;
    $lQuebrouPagina = false;
    $contadorAluno = 0;
    $numeroSequencial = 1; // Contador sequencial
    $iContadorSequencial = 0;

    // Processar disciplinas por página
    foreach ($aDisciplinasPorPagina as $iDisciplina => $aDisciplinasPagina) {
        $iPreenchimento = 0;
        $lPulouAluno = false;
        $contadorAluno = 0;
        $numeroSequencial = 1; // Resetar a cada página

        // LOOP PRINCIPAL DOS ALUNOS - VERSÃO CORRIGIDA
        for ($iContadorAluno = 0; $iContadorAluno < $iTotalAlunosMatriculados; $iContadorAluno++) {

            // Validar limite de movimentação
            if (isset($oFiltroRelatorio->sLimiteMovimentacao) && !empty($oFiltroRelatorio->sLimiteMovimentacao) && $aListaDeAlunos[$iContadorAluno]->getDataEncerramento() != null) {
                $oFiltroRelatorio->sLimiteMovimentacao = $oFiltroRelatorio->sLimiteMovimentacao . "/" . $oTurma->getCalendario()->getAnoExecucao();
                $oDataLimiteMovimentacao = new DBDate($oFiltroRelatorio->sLimiteMovimentacao);
                if (DBDate::calculaIntervaloEntreDatas($oDataLimiteMovimentacao, $aListaDeAlunos[$iContadorAluno]->getDataEncerramento(), 'd') > 0) {
                    $lPulouAluno = true;
                    $iAlunosImpressos++;
                    continue;
                }
            }

            $situacaoaluno = $aListaDeAlunos[$iContadorAluno]->getSituacao();
            $sNome = trim($aListaDeAlunos[$iContadorAluno]->getAluno()->getNome());
            $sMatricula = $aListaDeAlunos[$iContadorAluno]->getAluno()->getCodigoAluno();
            $codAluno2 = $aListaDeAlunos[$iContadorAluno]->getCodigo();

            // ===== CORREÇÃO 1: VERIFICAR FILTRO DE TROCA DE TURMA E MODALIDADE =====
            if ($oFiltroRelatorio->iTrocaTurma == 1 && $situacaoaluno == "TROCA DE TURMA" || $situacaoaluno == "TROCA DE MODALIDADE" ) {
                continue; // Pula aluno SEM incrementar numeroSequencial
            }

            // Agora incrementa os contadores (só para alunos que serão exibidos)
            $contadorAluno++;

            // Tratar alunos transferidos primeiro
            if ($situacaoaluno != "MATRICULADO") {
                $oDtEncerramento = $aListaDeAlunos[$iContadorAluno]->getDataEncerramento();
                $sDtEncerramento = "";
                if (!empty($oDtEncerramento)) {
                    $sDtEncerramento = " em " . $oDtEncerramento->convertTo(DBDate::DATA_PTBR);
                }

                // Verificar se precisa imprimir cabeçalho
                if ($lQuebrouPagina || $lPrimeiroLaco) {
                    if ($oFiltroRelatorio->iModelo == 3 || $oFiltroRelatorio->iModelo == 4) {
                        cabecalhoScpf($oPdf, $oTurma, $oFiltroRelatorio, $iCodigoEtapa);
                    }
                    cabecalhoPadrao($oPdf, $oFiltroRelatorio, $aDisciplinasPagina, $oTurma, $iCodigoEtapa);
                    $lPrimeiroLaco = false;
                }

                $oPdf->setfont('arial', '', 10);

                // Numeração corrigida para transferidos
                $numeroExibir = ($oFiltroRelatorio->iOrdenacao == 2 || $oFiltroRelatorio->iOrdenacao == 3)
                    ? $numeroSequencial
                    : $aListaDeAlunos[$iContadorAluno]->getNumeroOrdemAluno();

                $oPdf->Cell(5, 6, $numeroExibir, "L", 0, "C");
                $sNomeAluno = trim($aListaDeAlunos[$iContadorAluno]->getAluno()->getNome());
                $oPdf->Cell(79, 6, $sNomeAluno, "L", 0, "L");
                $oPdf->setfont('arial', '', 7);
                $oPdf->Cell(108, 6, $aListaDeAlunos[$iContadorAluno]->getSituacao() . $sDtEncerramento, "LR", 1, "");
                $oPdf->setfont('arial', '', 10);

                $numeroSequencial++; // Incrementa para transferidos também
                continue;
            }

            // PROCESSAR ALUNOS MATRICULADOS
            $oPdf->setfont('arial', '', 6);
            $sNomeAluno = trim($aListaDeAlunos[$iContadorAluno]->getAluno()->getNome());
            $iLinhasAluno = $oPdf->NbLines($oFiltroRelatorio->iTamanhoTotalColunaDisciplina, $sNomeAluno);
            $oFiltroRelatorio->iAltura = $iLinhasAluno * 4;
            $iPreenchimento = $iContadorAluno;

            if ($lPulouAluno) {
                $iPreenchimento++;
            }

            $sBordaAluno = "LR";
            if ($oFiltroRelatorio->iModelo == 3 || $oFiltroRelatorio->iModelo == 4) {
                if ($iAlunosImpressos == $oFiltroRelatorio->iTotalAlunosPorPagina - 1) {
                    $sBordaAluno = "LRB";
                }
            }

            $oPdf->SetFillColor(225, 225, 225);
            $iCorLinha = 0;

            if ($iPreenchimento % 2 == 0) {
                $iCorLinha = 1;
            }

            // Verificar quebra de página
            if ($iAlunosImpressos == $oFiltroRelatorio->iTotalAlunosPorPagina) {
                $oPdf->AddPage();
                $lQuebrouPagina = true;
            }

            if ($iAlunosImpressos >= $iTotalAlunosMatriculados) {
                $oPdf->AddPage();
                $lQuebrouPagina = true;
                $iContadorSequencial = 0;
                $iAlunosImpressos = 0;
            }

            if ($oPdf->GetY() > 221) {
                $oPdf->AddPage();
                $lQuebrouPagina = true;
                $iAlunosImpressos = 0;
            }

            $iAlunosImpressos++;

            // Verificar se houve quebra de página ou primeiro laço
            if ($lQuebrouPagina || $lPrimeiroLaco) {
                if ($oFiltroRelatorio->iModelo == 3 || $oFiltroRelatorio->iModelo == 4) {
                    cabecalhoScpf($oPdf, $oTurma, $oFiltroRelatorio, $iCodigoEtapa);
                }
                cabecalhoPadrao($oPdf, $oFiltroRelatorio, $aDisciplinasPagina, $oTurma, $iCodigoEtapa);
                $lPrimeiroLaco = false;
                $lQuebrouPagina = false;
            }

            $oPdf->setfont('arial', '', 10);

            // Numeração corrigida para alunos matriculados
            $numeroExibir = ($oFiltroRelatorio->iOrdenacao == 2 || $oFiltroRelatorio->iOrdenacao == 3)
                ? $numeroSequencial
                : $aListaDeAlunos[$iContadorAluno]->getNumeroOrdemAluno();

            $oPdf->Cell(5, $oFiltroRelatorio->iAltura, $numeroExibir, $sBordaAluno, 0, "C", $iCorLinha);

            $iPosicaoY = $oPdf->GetY();
            $iPosicaoX = $oPdf->GetX();

            if (strlen($sNomeAluno) > 32) {
                $oPdf->setfont('arial', '', 7);
            }
            $oPdf->MultiCell($oFiltroRelatorio->iTamanhoTotalColunaDisciplina + 14, 4, $sNomeAluno, $sBordaAluno, 'L', $iCorLinha);
            $oPdf->setfont('arial', '', 10);
            $oPdf->SetXY($iPosicaoX + $oFiltroRelatorio->iTamanhoTotalColunaDisciplina + 14, $iPosicaoY);

            // Buscamos os dados do resultado final
            $sAproveitamento = '';
            $sPercentualFrequencia = '';
            $iNumeroFaltas = '';
            $sResultadoFinal = '';
            $iContadorDisciplinasImpressas = 0;
            $sResultadoGeral = 'A';

            // Verificar se o aluno foi aprovado com progressão parcial
            $lAprovadoProgressaoAno = false;
            foreach ($aListaDeAlunos[$iContadorAluno]->getAluno()->getProgressaoParcial() as $oProgressaoParcial) {
                if (
                    $oTurma->getCalendario()->getAnoExecucao() == $oProgressaoParcial->getAno()
                    && $oProgressaoParcial->getCodigoDiarioFinal() != null
                ) {
                    $lAprovadoProgressaoAno = true;
                }
            }

            $iTotalDeAulasDadas = 0;
            $nTotalDeFaltas = 0;
            $iTotalDeAulasDadasGeral = 0;

            // ===== CORREÇÃO 2: DETECTAR SE É AVALIAÇÃO POR CONCEITOS =====
            $lEhConceito = false;
            if (!empty($aDisciplinasPagina) && count($aDisciplinasPagina) > 0) {
                try {
                    $oPrimeiraRegencia = $aDisciplinasPagina[0];
                    $oProcedimento = $oPrimeiraRegencia->getProcedimentoAvaliacao();
                    $oFormaAvaliacao = $oProcedimento->getFormaAvaliacao();
                    $lEhConceito = ($oFormaAvaliacao->getTipo() == "NIVEL");
                } catch (Exception $e) {
                    // Se houver erro, assume como não sendo conceito
                    $lEhConceito = false;
                }
            }

            // PROCESSAR CADA DISCIPLINA DO ALUNO
            foreach ($aDisciplinasPagina as $oRegenciaTurma) {
                db_inicio_transacao();

                $oRegencia = $aListaDeAlunos[$iContadorAluno]->getDiarioDeClasse()
                    ->getDisciplinasPorRegencia($oRegenciaTurma);

                $sAmparado = '';
                if ($oRegencia->getAmparo() != null && $oRegencia->getAmparo()->isTotal()) {
                    if ($oRegencia->getAmparo()->getCodigoConvencaoAmparo()) {
                        $oDaoConvencaoAmparo = new cl_convencaoamp();
                        $sSqlConvencaoAmparo = $oDaoConvencaoAmparo->sql_query_file($oRegencia->getAmparo()->getCodigoConvencaoAmparo());
                        $rsConvencaoAmparo = $oDaoConvencaoAmparo->sql_record($sSqlConvencaoAmparo);
                        $oConvencaoAmparo = db_utils::fieldsMemory($rsConvencaoAmparo, 0);
                        $sAmparado = $oConvencaoAmparo->ed250_c_abrev;

                        $oFiltroRelatorio->aJustificativas[$oTurma->getCodigo()][] = $oConvencaoAmparo->ed250_c_abrev . ' - ' . $oConvencaoAmparo->ed250_c_descr;
                    }

                    if ($oRegencia->getAmparo()->getCodigoJustificativa()) {
                        $sAmparado = 'AMP ' . $oRegencia->getAmparo()->getCodigoJustificativa();

                        $oDaoJustificativa = new cl_justificativa();
                        $sSqlJustificativa = $oDaoJustificativa->sql_query_file($oRegencia->getAmparo()->getCodigoJustificativa());
                        $rsJustitificativa = $oDaoJustificativa->sql_record($sSqlJustificativa);
                        $oDadosJustificativa = db_utils::fieldsMemory($rsJustitificativa, 0);
                        $oFiltroRelatorio->aJustificativas[$oTurma->getCodigo()][] = $oRegencia->getAmparo()->getCodigoJustificativa() . ' - ' . $oDadosJustificativa->ed06_c_descr;
                    }
                }

                $iNumeroFaltas = $oRegencia->getTotalFaltas();
                $iTotalDeAulasDadas = $oRegencia->getTotalDeAulasParaCalculo();
                $iTotalDeAulasDadasGeral += $oRegencia->getTotalDeAulasParaCalculo();
                if ($oFiltroRelatorio->lCalculaFrequencia == 2) {
                    $nTotalDeFaltas += $iNumeroFaltas;
                }
                db_fim_transacao();

                $iCodigoEnsino = $oTurma->getBaseCurricular()->getCurso()->getEnsino()->getCodigo();
                $oResultadoFinal = $oRegencia->getResultadoFinal();

                // Valor do resultado de aprovação
                $nValorAproveitamento = $oResultadoFinal->getValorAprovacao();

                /**
                 * Autor: Uemerson Santana
                 * Data: 12/02/2026
                 * Demanda: 18250
                 * Razao: Quando ed74_c_valoraprov esta vazio (aluno nao cursou todos os bimestres),
                 *        a ATA nao exibia a media. Busca a Nota Final (NF) de diarioresultado
                 *        como fallback, pois ed74_c_valoraprov armazena o NF (valor pos-recuperacao),
                 *        nao o MA (media bruta). Isso garante consistencia com a coluna MF da
                 *        Ficha Individual. Se NF estiver vazio, tenta MF (Media Final) como
                 *        segundo fallback para anos iniciais.
                 */
                if (empty($nValorAproveitamento) || trim($nValorAproveitamento) === '') {
                    $iCodDiarioFallback = $oResultadoFinal->getCodigoDiario();
                    if (!empty($iCodDiarioFallback)) {
                        $sqlFallbackNF = "SELECT dr.ed73_i_valornota as valor_nf
                                          FROM diarioresultado dr
                                          INNER JOIN procresultado pr ON pr.ed43_i_codigo = dr.ed73_i_procresultado
                                          INNER JOIN resultado r ON r.ed42_i_codigo = pr.ed43_i_resultado
                                          WHERE dr.ed73_i_diario = {$iCodDiarioFallback}
                                            AND trim(r.ed42_c_abrev) = 'NF'
                                          LIMIT 1";
                        $rsFallbackNF = db_query($sqlFallbackNF);
                        if ($rsFallbackNF && pg_num_rows($rsFallbackNF) > 0) {
                            $nValorAproveitamento = pg_fetch_result($rsFallbackNF, 0, 0);
                        }
                        // Fallback MF (Media Final) para anos iniciais
                        if (empty($nValorAproveitamento) || trim($nValorAproveitamento) === '') {
                            $sqlFallbackMF = "SELECT dr.ed73_i_valornota as valor_mf
                                              FROM diarioresultado dr
                                              INNER JOIN procresultado pr ON pr.ed43_i_codigo = dr.ed73_i_procresultado
                                              INNER JOIN resultado r ON r.ed42_i_codigo = pr.ed43_i_resultado
                                              WHERE dr.ed73_i_diario = {$iCodDiarioFallback}
                                                AND trim(r.ed42_c_abrev) = 'MF'
                                              LIMIT 1";
                            $rsFallbackMF = db_query($sqlFallbackMF);
                            if ($rsFallbackMF && pg_num_rows($rsFallbackMF) > 0) {
                                $nValorAproveitamento = pg_fetch_result($rsFallbackMF, 0, 0);
                            }
                        }
                    }
                }
                if (
                    $oResultadoFinal->getFormaAprovacaoConselho() instanceof AprovacaoConselho
                    && $oResultadoFinal->getFormaAprovacaoConselho()->getFormaAprovacao() == 1
                    && $oResultadoFinal->getFormaAprovacaoConselho()->getAlterarNotaFinal() == 2
                ) {
                    $nValorAproveitamento = $oResultadoFinal->getFormaAprovacaoConselho()->getAvaliacaoConselho();
                }

                // ===== CORREÇÃO 2B: OBTER VALOR CORRETO PARA CONCEITOS (NIVEL) =====
                $oFormaAvaliacao = $oResultadoFinal->getResultadoAvaliacao()->getFormaDeAvaliacao();
                if (!empty($oFormaAvaliacao) && $oFormaAvaliacao->getTipo() == "NIVEL") {
                    // Para conceitos (NIVEL), usar o método específico que pega o conceito final
                    try {
                        $oDiarioAluno = $aListaDeAlunos[$iContadorAluno]->getDiarioDeClasse()->getDisciplinasPorRegencia($oRegenciaTurma);
                        $oAvaliacaoFinal = null;

                        // Buscar a avaliação de resultado (CF - Conceito Final)
                        foreach ($oDiarioAluno->getAvaliacoes() as $oAvaliacao) {
                            if ($oAvaliacao->getElementoAvaliacao()->isResultado()) {
                                $oAvaliacaoFinal = $oAvaliacao;
                                break;
                            }
                        }

                        if ($oAvaliacaoFinal !== null) {
                            // Pegar o conceito da avaliação final diretamente
                            $nValorAproveitamento = $oAvaliacaoFinal->getValorAproveitamento()->getAproveitamento();

                            // Se estiver vazio, tentar pegar do parecer
                            if (empty($nValorAproveitamento) && !empty($oAvaliacaoFinal->getParecer())) {
                                $nValorAproveitamento = $oAvaliacaoFinal->getParecer();
                            }
                        }
                    } catch (Exception $e) {
                        // Em caso de erro, usar o método padrão
                        $nValorAproveitamento = $oResultadoFinal->getResultadoAprovacao();
                    }
                } elseif (!empty($oFormaAvaliacao) && $oFormaAvaliacao->getTipo() == "PARECER") {
                    $nValorAproveitamento = $oResultadoFinal->getResultadoAprovacao();

                    // SÓ APLICAR TERMOS SE NÃO FOR CONCEITO
                    if (!$lEhConceito && !empty($iCodigoEnsino) && ($nValorAproveitamento == 'A' || $nValorAproveitamento == 'R')) {
                        $aDadosTermo = DBEducacaoTermo::getTermoEncerramento($iCodigoEnsino, $nValorAproveitamento, $iAnoCalendario);
                        if (isset($aDadosTermo[0])) {
                            $nValorAproveitamento = $aDadosTermo[0]->sAbreviatura;
                        }
                    }
                }

                // Se for uma nota o valor do aproveitamento devemos aplicar as regras de arredondamento
                if (is_numeric($nValorAproveitamento)) {
                    $nValorAproveitamento = ArredondamentoNota::formatar(
                        $nValorAproveitamento,
                        $oTurma->getCalendario()->getAnoExecucao()
                    );
                }

                $sPercentualFrequencia = $oRegencia->calcularPercentualFrequencia();

                // Buscar o resultado final de todas as avaliações
                $sResultadoAprovacao = $aListaDeAlunos[$iContadorAluno]->getDiarioDeClasse()->getResultadoFinal();

                // Verificar se o aluno foi reprovado em alguma disciplina
                if ($sResultadoAprovacao == 'R' && !$lAprovadoProgressaoAno) {
                    $sResultadoGeral = 'R';
                }

                if ($sAmparado != '') {
                    $nValorAproveitamento = $sAmparado;
                }

                // NOTA COM VÍRGULA
                $nValorAproveitamento = str_replace(".", ",", $nValorAproveitamento);

                if ($nValorAproveitamento == 'Parecer') {
                    $nValorAproveitamento = 'Rel';
                }

                $iCodigoAluno = $aListaDeAlunos[$iContadorAluno]->getAluno()->getCodigoAluno();
                $iCodigoMatriculaTmp = (string) $aListaDeAlunos[$iContadorAluno]->getMatricula();

                if (isAlunoComNecessidadesEspeciais($iCodigoAluno) and isAvaliadoPorParecer($iCodigoMatriculaTmp) ) {
                    $nValorAproveitamento = "PD";
                }

                // Preenchemos com o aproveitamento para cada disciplina
                if ($oFiltroRelatorio->lCalculaFrequencia == 1 && $oFiltroRelatorio->iFrequencia != 1) {
                    $oPdf->Cell($oFiltroRelatorio->iTamanhoColunaAbrevDisciplina - 6, $oFiltroRelatorio->iAltura, "{$nValorAproveitamento}", $sBordaAluno, 0, "C", $iCorLinha);

                    $nValorFalta = !empty($iNumeroFaltas) ? $iNumeroFaltas : "";
                    if ($oFiltroRelatorio->iFrequencia == 2) {
                        $nValorFalta = $sPercentualFrequencia;
                    }
                    if ($oFiltroRelatorio->iFrequencia == 4) {
                        $nValorFalta = $iTotalDeAulasDadas - $iNumeroFaltas;
                    }
                    if ($oRegencia->reclassificadoPorBaixaFrequencia()) {
                        if ($nValorFalta < '75') {
                            $nValorFalta = '75';
                        }
                    }
                    if ($oRegencia->getRegencia()->getFrequenciaGlobal() == 'A') {
                        if ($nValorFalta < '75') {
                            $nValorFalta = '75';
                        }
                    }

                    $oPdf->SetFontSize(5.5);
                    $oPdf->Cell($oFiltroRelatorio->iTamanhoColunaAbrevDisciplina - 10, $oFiltroRelatorio->iAltura, "{$nValorFalta}", $sBordaAluno, 0, "C", $iCorLinha);
                    $oPdf->SetFontSize(6);
                } else {
                    $oPdf->Cell($oFiltroRelatorio->iTamanhoColunaAbrevDisciplina - 2, $oFiltroRelatorio->iAltura, $nValorAproveitamento, $sBordaAluno, 0, "C", $iCorLinha);
                }

                $iContadorDisciplinasImpressas++;
            }

            // Imprimimos as demais colunas de aproveitamento, em branco
            for ($iContadorColunaBranco = 0; $iContadorColunaBranco < $oFiltroRelatorio->iColunasEmBranco; $iContadorColunaBranco++) {
                $oPdf->Cell($oFiltroRelatorio->iTamanhoColunaAbrevDisciplina - 2, $oFiltroRelatorio->iAltura, "", $sBordaAluno, 0, "C", $iCorLinha);
            }

            // ===== CORREÇÃO 2C: SÓ APLICAR TERMOS AO RESULTADO GERAL SE NÃO FOR CONCEITO =====
            if (!$lEhConceito) {
                // Busca o termo do ensino (apenas se não for conceito)
                if (!empty($iCodigoEnsino) && ($sResultadoGeral == 'A' || $sResultadoGeral == 'R')) {
                    $aDadosTermo = DBEducacaoTermo::getTermoEncerramento($iCodigoEnsino, $sResultadoGeral, $iAnoCalendario);
                    if (isset($aDadosTermo[0])) {
                        $sResultadoGeral = $aDadosTermo[0]->sAbreviatura;
                    }
                }

                if (($sResultadoGeral == "APR") or ($sResultadoGeral == "Apr")) {
                    $sResultadoGeral = "AP";
                }
            }

            if ($lAprovadoProgressaoAno) {
                $lObservacaoProgressaoParcial = true;
                $sResultadoGeral .= '/D';
            }

            // Calcular frequência final usando nova função aprimorada
            if ($oFiltroRelatorio->lCalculaFrequencia == 2 && $oFiltroRelatorio->iFrequencia != 1) {
                $iCodigoMatricula = $aListaDeAlunos[$iContadorAluno]->getCodigo();
                $iCodigoRegencia = $aDisciplinasPagina[0]->getCodigo(); // Usar primeira regência como referência

                /**
                 * Autor: Uemerson Santana
                 * Data: 13/02/2026
                 * Demanda: 18250
                 * Razao: calcularFrequenciaAluno() usa GradeAproveitamentoAluno que falha para
                 *        alguns alunos (2o ao 5o ano), retornando 0%. Usa calcularFrequenciaDireta()
                 *        (SQL direto) como metodo primario, com fallback em calcularFrequenciaAluno().
                 *        Para ef_anos_finais mantem calcularFrequenciaAnosFinaisATA() que ja funciona.
                 */
                if ($tipoEnsino['tipo'] == 'ef_anos_finais') {
                    $oDadosFrequencia = calcularFrequenciaAnosFinaisATA($aListaDeAlunos[$iContadorAluno], $oFiltroRelatorio);
                } else {
                    /**
                     * Autor: Uemerson Santana
                     * Data: 16/02/2026
                     * Demanda: 18250
                     * Razao: buscaCodigoRegenciaPorMatricula() retornava a primeira regencia por
                     *        codigo (ex: QUIMICA, freq_global='A'), que nao possui dados de
                     *        aulas em regenciaperiodo. Para calcular frequencia, e necessario usar
                     *        a regencia com freq_global='FA' (ex: HISTORIA), que e a unica
                     *        que armazena aulas dadas e faltas reais.
                     */
                    $iCodigoTurmaComplexo = $oTurma->getCodigo();
                    $sqlRegFAcomplexo = "SELECT ed59_i_codigo FROM regencia"
                                      . " WHERE ed59_i_turma = {$iCodigoTurmaComplexo}"
                                      . " AND trim(ed59_c_freqglob) = 'FA'"
                                      . " LIMIT 1";
                    $rsRegFAcomplexo = db_query($sqlRegFAcomplexo);
                    if ($rsRegFAcomplexo && pg_num_rows($rsRegFAcomplexo) > 0) {
                        $codregenciaComplexo = pg_fetch_result($rsRegFAcomplexo, 0, 0);
                    } else {
                        $codregenciaComplexo = buscaCodigoRegenciaPorMatricula($iCodigoTurmaComplexo, $iCodigoMatricula);
                        if (empty($codregenciaComplexo)) {
                            $codregenciaComplexo = $iCodigoRegencia;
                        }
                    }
                    $oDadosFrequencia = calcularFrequenciaDireta($iCodigoMatricula, $codregenciaComplexo);
                    if ($oDadosFrequencia->nPercentualFrequencia == 0 && $oDadosFrequencia->iTotalAulas == 0) {
                        try {
                            $oDadosFrequenciaFallback = calcularFrequenciaAluno($iCodigoMatricula, $codregenciaComplexo);
                            if ($oDadosFrequenciaFallback->nPercentualFrequencia > 0) {
                                $oDadosFrequencia = $oDadosFrequenciaFallback;
                            }
                        } catch (Exception $e) {
                        }
                    }
                }
                $nValorFaltas = $oDadosFrequencia->nPercentualFrequencia;

                if ($oFiltroRelatorio->iFrequencia == 3) {
                    $nValorFaltas = !empty($oDadosFrequencia->iTotalFaltas) ? $oDadosFrequencia->iTotalFaltas : "";
                }

                if ($oFiltroRelatorio->iFrequencia == 4) {
                    $nValorFaltas = $oDadosFrequencia->iTotalAulas - $oDadosFrequencia->iTotalFaltas;
                }

                /**
                 * Autor: Uemerson Santana
                 * Data: 13/02/2026
                 * Demanda: 18250
                 * Razao: A condicao anterior (freq_global='A' e resultado != 'REP') forcava 75%
                 *        para todos os alunos aprovados quando a frequencia calculada era incorreta.
                 *        Em turmas com conceito, $sResultadoGeral fica 'R' (nao convertido para 'REP'),
                 *        entao a condicao era verdadeira para todos. Agora somente reclassificados
                 *        por baixa frequencia terao frequencia ajustada para 75%.
                 */
                if ($aListaDeAlunos[$iContadorAluno]->getDiarioDeClasse()->reclassificadoPorBaixaFrequencia()) {
                    if ($nValorFaltas < '75') {
                        $nValorFaltas = '75';
                    }
                }
                $nValorFaltas = floor($nValorFaltas);

                $oPdf->Cell($oFiltroRelatorio->iTamanhoColunaResultado + 3, $oFiltroRelatorio->iAltura, "{$nValorFaltas}", $sBordaAluno, 0, "C", $iCorLinha);
                $oPdf->Cell($oFiltroRelatorio->iTamanhoColunaResultado + 3, $oFiltroRelatorio->iAltura, "{$sResultadoGeral}", $sBordaAluno, 1, "C", $iCorLinha);
            } else {
                $oPdf->Cell(12, $oFiltroRelatorio->iAltura, "{$sResultadoGeral}", $sBordaAluno, 1, "C", $iCorLinha);
            }

            // Incrementar numeração sequencial
            $numeroSequencial++;
        }

        // Finalizar seção
        finalizarSecaoCompleta($oPdf, $oFiltroRelatorio);
    }
}
/**
 * Finaliza o processamento de uma seção completa
 */
function finalizarSecaoCompleta($oPdf, $oFiltroRelatorio) {
    // Gerar linhas em branco se necessário
    if ($oFiltroRelatorio->iTipoModelo != 2) {
        if (ceil($oPdf->GetY()) < 227) {
            $iLinhasEmBranco = (227 - $oPdf->GetY()) / 4;
            for ($i = 0; $i < $iLinhasEmBranco; $i++) {
                $oPdf->Cell(5, 4, "", "LR", 0, "C", 0);
                $oPdf->Cell($oFiltroRelatorio->iTamanhoTotalColunaDisciplina + 14, 4, "", 0, 0, "LR", 0);

                for ($iContadorDisciplinas = 0; $iContadorDisciplinas < $oFiltroRelatorio->iContadorDisciplinasImpressas; $iContadorDisciplinas++) {
                    if ($oFiltroRelatorio->lCalculaFrequencia == 1 && $oFiltroRelatorio->iFrequencia != 1) {
                        $oPdf->Cell($oFiltroRelatorio->iTamanhoColunaAbrevDisciplina - 6, 4, "", "LR", 0, "C", 0);
                        $oPdf->Cell($oFiltroRelatorio->iTamanhoColunaAbrevDisciplina - 10, 4, "", "LR", 0, "C", 0);
                    } else {
                        $oPdf->Cell($oFiltroRelatorio->iTamanhoColunaAbrevDisciplina - 2, 4, "", "LR", 0, "C", 0);
                    }
                }

                for ($iContadorColunaBranco = 0; $iContadorColunaBranco < $oFiltroRelatorio->iColunasEmBranco; $iContadorColunaBranco++) {
                    $oPdf->Cell($oFiltroRelatorio->iTamanhoColunaAbrevDisciplina - 2, 4, "", "LR", 0, "C", 0);
                }

                if ($oFiltroRelatorio->iFrequencia != 1 && $oFiltroRelatorio->lCalculaFrequencia == 2) {
                    $oPdf->Cell($oFiltroRelatorio->iTamanhoColunaResultado + 3, 4, "", "LR", 0, "C", 0);
                    $oPdf->Cell($oFiltroRelatorio->iTamanhoColunaResultado + 3, 4, "", "LR", 1, "C", 0);
                } else {
                    $oPdf->Cell(12, 4, "", "LR", 1, "C", 0);
                }
            }
        }
    }

    // Processar observações
    processarObservacoesEscola($oPdf, $oFiltroRelatorio);

    // Linha separadora
    $oPdf->Cell(192, 4, "", "T", 0, "C", 0);
    $oPdf->SetFont('arial', '', 10);
    $oPdf->Cell(10, 6, "", 0, 0, "C", 0);
    $oPdf->Cell(172, 6, "", "T", 1, "L", 0);

    // Gerar célula da ata e assinaturas
    gerarAtaCelula($oPdf, $oFiltroRelatorio->ataRespNome);
    gerarAssinaturasPadrao($oPdf, $oFiltroRelatorio);
}
/**
 * Processa ensino padrão
 */
function processarEnsinoPadrao($oPdf, $oTurma, $oFiltroRelatorio, $iCodigoEtapa, $aDisciplinas, $oEtapa, $iAnoCalendario) {
    global $lObservacaoProgressaoParcial;

    $aDisciplinasPorPagina = array();
    $iContadorAux = 0;
    $iPagina = 0;
    $aListaDeAlunos = array();
    $lSequencialDiario = true;

    // Organizar disciplinas por página
    foreach ($aDisciplinas as $oDisciplina) {
        if (!$oDisciplina->isLancadaNoHistorico()) {
            continue;
        }
        $aDisciplinasPorPagina[$iPagina][$iContadorAux] = $oDisciplina;
        $iTotalContadorAux = 6;

        if ($oFiltroRelatorio->iFrequencia == 1 || $oFiltroRelatorio->lCalculaFrequencia == 2) {
            $iTotalContadorAux = 9;
        }
        if ($iContadorAux >= $iTotalContadorAux) {
            $iPagina++;
            $iContadorAux = -1;
        }
        $iContadorAux++;
    }

    $oFiltroRelatorio->iContadorDisciplinasImpressas = $oFiltroRelatorio->iTotalDisciplinasPorPagina;
    $aListaDeAlunos = $oTurma->getAlunosMatriculadosNaTurmaPorSerie($oEtapa);

    // Ordenação se necessário
    switch ($oFiltroRelatorio->iOrdenacao) {
        case 2:
        case 3:
            usort($aListaDeAlunos, "ordernarAlunosPorNome");
            break;
    }

    /*
     * Autor: Uemerson Santana
     * Demanda: 17826
     * Data: 15/09/2025
     */
    // FILTRAR ALUNOS TRANSFERIDOS QUE RETORNARAM - CORREÇÃO PARA EVITAR DUPLICATAS
    $aListaDeAlunosFiltrada = [];
    $aAlunosPorCodigo = [];

    // Agrupar matrículas por código do aluno
    foreach ($aListaDeAlunos as $oMatricula) {
        $iCodigoAluno = $oMatricula->getAluno()->getCodigoAluno();
        if (!isset($aAlunosPorCodigo[$iCodigoAluno])) {
            $aAlunosPorCodigo[$iCodigoAluno] = [];
        }
        $aAlunosPorCodigo[$iCodigoAluno][] = $oMatricula;
    }

    // Para cada aluno, verificar se há matrícula ativa
    foreach ($aAlunosPorCodigo as $iCodigoAluno => $aMatriculasAluno) {
        $lTemMatriculado = false;
        $oMatriculaAtiva = null;

        // Verificar se existe matrícula com situação "MATRICULADO"
        foreach ($aMatriculasAluno as $oMatricula) {
            if (trim($oMatricula->getSituacao()) == "MATRICULADO") {
                $lTemMatriculado = true;
                $oMatriculaAtiva = $oMatricula;
                break;
            }
        }

        if ($lTemMatriculado) {
            // Se há matrícula ativa, incluir apenas ela
            $aListaDeAlunosFiltrada[] = $oMatriculaAtiva;
        } else {
            // Se não há matrícula ativa, incluir todas (transferências)
            $aListaDeAlunosFiltrada = array_merge($aListaDeAlunosFiltrada, $aMatriculasAluno);
        }
    }

    // Substituir a lista original pela filtrada
    $aListaDeAlunos = $aListaDeAlunosFiltrada;

    $iTotalAlunosMatriculados = count($aListaDeAlunos);
    $iAlunosImpressos = 0;
    $lPrimeiroLaco = true;
    $lQuebrouPagina = false;
    $contadorAluno = 0;
    $iContadorSequencial = 0;

    foreach ($aDisciplinasPorPagina as $iDisciplina => $aDisciplinasPagina) {
        $iPreenchimento = 0;
        $lPulouAluno = false;

        for ($iContadorAluno = 0; $iContadorAluno < $iTotalAlunosMatriculados; $iContadorAluno++) {
            // Validar limite de movimentação
            if (isset($oFiltroRelatorio->sLimiteMovimentacao) && !empty($oFiltroRelatorio->sLimiteMovimentacao) && $aListaDeAlunos[$iContadorAluno]->getDataEncerramento() != null) {
                $oFiltroRelatorio->sLimiteMovimentacao = $oFiltroRelatorio->sLimiteMovimentacao . "/" . $oTurma->getCalendario()->getAnoExecucao();
                $oDataLimiteMovimentacao = new DBDate($oFiltroRelatorio->sLimiteMovimentacao);
                if (DBDate::calculaIntervaloEntreDatas($oDataLimiteMovimentacao, $aListaDeAlunos[$iContadorAluno]->getDataEncerramento(), 'd') > 0) {
                    $lPulouAluno = true;
                    $iAlunosImpressos++;
                    continue;
                }
            }

            $oPdf->setfont('arial', '', 6);
            $sNomeAluno = trim($aListaDeAlunos[$iContadorAluno]->getAluno()->getNome());
            $iLinhasAluno = $oPdf->NbLines($oFiltroRelatorio->iTamanhoTotalColunaDisciplina, $sNomeAluno);
            $oFiltroRelatorio->iAltura = $iLinhasAluno * 4;
            $iPreenchimento = $iContadorAluno;

            if ($lPulouAluno) {
                $iPreenchimento++;
            }

            $sBordaAluno = "LR";
            if ($oFiltroRelatorio->iModelo == 3 || $oFiltroRelatorio->iModelo == 4) {
                if ($iAlunosImpressos == $oFiltroRelatorio->iTotalAlunosPorPagina - 1) {
                    $sBordaAluno = "LRB";
                }
            }

            $lQuebrouPagina = false;
            $oPdf->SetFillColor(225, 225, 225);
            $iCorLinha = 0;

            if ($iPreenchimento % 2 == 0) {
                $iCorLinha = 1;
            }

            // Verificar quebra de página
            if ($iAlunosImpressos == $oFiltroRelatorio->iTotalAlunosPorPagina) {
                $oPdf->AddPage();
                $lQuebrouPagina = true;
            }

            if ($iAlunosImpressos >= $iTotalAlunosMatriculados) {
                $oPdf->AddPage();
                $lQuebrouPagina = true;
                $iContadorSequencial = 0;
                $iAlunosImpressos = 0;
            }

            if ($oPdf->GetY() > 221) {
                $oPdf->AddPage();
                $lQuebrouPagina = true;
                $iAlunosImpressos = 0;
            }

            $iAlunosImpressos++;

            // Verificar se houve quebra de página ou primeiro laço
            if ($lQuebrouPagina || $lPrimeiroLaco) {
                if ($oFiltroRelatorio->iModelo == 3 || $oFiltroRelatorio->iModelo == 4) {
                    cabecalhoScpf($oPdf, $oTurma, $oFiltroRelatorio, $iCodigoEtapa);
                }
                $lPrimeiroLaco = false;
                cabecalhoPadrao($oPdf, $oFiltroRelatorio, $aDisciplinasPagina, $oTurma, $iCodigoEtapa);
            }

            if ($oFiltroRelatorio->iTrocaTurma == 1 && $aListaDeAlunos[$iContadorAluno]->getSituacao() == "TROCA DE TURMA" || $aListaDeAlunos[$iContadorAluno]->getSituacao() == "TROCA DE MODALIDADE") {
                continue;
            }

            $oPdf->setfont('arial', '', 12);

            if ($lSequencialDiario) {
                $oPdf->Cell(5, $oFiltroRelatorio->iAltura + 2, $aListaDeAlunos[$iContadorAluno]->getNumeroOrdemAluno(), $sBordaAluno, 0, "C", $iCorLinha);
            } else {
                $oPdf->Cell(5, $oFiltroRelatorio->iAltura + 2, ++$iContadorSequencial, $sBordaAluno, 0, "C", $iCorLinha);
            }

            $iPosicaoY = $oPdf->GetY();
            $iPosicaoX = $oPdf->GetX();

            $oPdf->MultiCell($oFiltroRelatorio->iTamanhoTotalColunaDisciplina, 6, $sNomeAluno, $sBordaAluno, 'L', $iCorLinha);
            $oPdf->SetXY($iPosicaoX + $oFiltroRelatorio->iTamanhoTotalColunaDisciplina, $iPosicaoY + 2);

            // Buscamos os dados do resultado final
            $sAproveitamento = '';
            $sPercentualFrequencia = '';
            $iNumeroFaltas = '';
            $sResultadoFinal = '';
            $iContadorDisciplinasImpressas = 0;
            $sResultadoGeral = 'A';

            // Imprimimos a situação do aluno na linha, caso ele tenha sido transferido
            if ($aListaDeAlunos[$iContadorAluno]->getSituacao() != "MATRICULADO") {
                $oDtEncerramento = $aListaDeAlunos[$iContadorAluno]->getDataEncerramento();
                $sDtEncerramento = "";
                if (!empty($oDtEncerramento)) {
                    $sDtEncerramento = " em " . $oDtEncerramento->convertTo(DBDate::DATA_PTBR);
                }

                $sTransferido = $aListaDeAlunos[$iContadorAluno]->getSituacao();
                if ($sTransferido == "TRANSFERIDO FORA") {
                    $sTransferido = explode(" ", $sTransferido);
                    $sTransferido = $sTransferido[0];
                }
                $iLinha = ($oFiltroRelatorio->iTamanhoColunaAbrevDisciplina * $oFiltroRelatorio->iAuxiliarTransferido) + 12;
                $oPdf->Cell($iLinha, $oFiltroRelatorio->iAltura, "{$sTransferido}" . $sDtEncerramento, $sBordaAluno, 1, "L", $iCorLinha);
            } else {
                // Verificar se o aluno foi aprovado com progressão parcial
                $lAprovadoProgressaoAno = verificarProgressaoParcial($aListaDeAlunos[$iContadorAluno], $oTurma);

                $iTotalDeAulasDadas = 0;
                $nTotalDeFaltas = 0;
                $iTotalDeAulasDadasGeral = 0;

                foreach ($aDisciplinasPagina as $oRegenciaTurma) {
                    db_inicio_transacao();

                    $oRegencia = $aListaDeAlunos[$iContadorAluno]->getDiarioDeClasse()
                        ->getDisciplinasPorRegencia($oRegenciaTurma);

                    $sAmparado = processarAmparo($oRegencia, $oFiltroRelatorio, $oTurma);

                    $iNumeroFaltas = $oRegencia->getTotalFaltas();
                    $iTotalDeAulasDadas = $oRegencia->getTotalDeAulasParaCalculo();
                    $iTotalDeAulasDadasGeral += $oRegencia->getTotalDeAulasParaCalculo();
                    if ($oFiltroRelatorio->lCalculaFrequencia == 2) {
                        $nTotalDeFaltas += $iNumeroFaltas;
                    }
                    db_fim_transacao();

                    $nValorAproveitamento = obterValorAproveitamento($oRegencia, $oTurma, $iAnoCalendario, $sAmparado, $aListaDeAlunos[$iContadorAluno]);
                    $sPercentualFrequencia = $oRegencia->calcularPercentualFrequencia();

                    // Buscar o resultado final de todas as avaliações
                    $sResultadoAprovacao = $aListaDeAlunos[$iContadorAluno]->getDiarioDeClasse()->getResultadoFinal();

                    // Verificar se o aluno foi reprovado em alguma disciplina
                    if ($sResultadoAprovacao == 'R' && !$lAprovadoProgressaoAno) {
                        $sResultadoGeral = 'R';
                    }

                    // Preenchemos com o aproveitamento para cada disciplina
                    if ($oFiltroRelatorio->lCalculaFrequencia == 1 && $oFiltroRelatorio->iFrequencia != 1) {
                        $oPdf->Cell($oFiltroRelatorio->iTamanhoColunaAbrevDisciplina - 6, $oFiltroRelatorio->iAltura, "{$nValorAproveitamento}", $sBordaAluno, 0, "C", $iCorLinha);

                        $nValorFalta = !empty($iNumeroFaltas) ? $iNumeroFaltas : "";

                        if ($oFiltroRelatorio->iFrequencia == 2) {
                            $nValorFalta = $sPercentualFrequencia;
                        }

                        if ($oFiltroRelatorio->iFrequencia == 4) {
                            $nValorFalta = $iTotalDeAulasDadas - $iNumeroFaltas;
                        }

                        if ($oRegencia->reclassificadoPorBaixaFrequencia()) {
                            if ($nValorFalta < '75') {
                                $nValorFalta = '75';
                            }
                        }

                        if ($oRegencia->getRegencia()->getFrequenciaGlobal() == 'A') {
                            if ($nValorFalta < '75') {
                                $nValorFalta = '75';
                            }
                        }

                        $oPdf->SetFontSize(5.5);
                        $oPdf->Cell($oFiltroRelatorio->iTamanhoColunaAbrevDisciplina - 10, $oFiltroRelatorio->iAltura, "{$nValorFalta}", $sBordaAluno, 0, "C", $iCorLinha);
                        $oPdf->SetFontSize(6);
                    } else {
                        $oPdf->Cell(
                            $oFiltroRelatorio->iTamanhoColunaAbrevDisciplina,
                            $oFiltroRelatorio->iAltura,
                            $nValorAproveitamento,
                            $sBordaAluno,
                            0,
                            "C",
                            $iCorLinha
                        );
                    }

                    $iContadorDisciplinasImpressas++;
                }

                // Imprimimos as demais colunas de aproveitamento, em branco
                for ($iContadorColunaBranco = 0; $iContadorColunaBranco < $oFiltroRelatorio->iColunasEmBranco; $iContadorColunaBranco++) {
                    $oPdf->Cell($oFiltroRelatorio->iTamanhoColunaAbrevDisciplina, $oFiltroRelatorio->iAltura, "", $sBordaAluno, 0, "C", $iCorLinha);
                }

                $sResultadoGeral = ajustarResultadoFinal($sResultadoGeral, $lAprovadoProgressaoAno, $oTurma, $iAnoCalendario);

                if ($lAprovadoProgressaoAno) {
                    $lObservacaoProgressaoParcial = true;
                    $sResultadoGeral .= '/D';
                }

                // Verificamos a frequência para alinhamento do resultado final de cada aluno
                if ($oFiltroRelatorio->lCalculaFrequencia == 2 && $oFiltroRelatorio->iFrequencia != 1) {
                    $nValorFaltas = $sPercentualFrequencia;
                    if ($oFiltroRelatorio->iFrequencia == 3) {
                        $nValorFaltas = !empty($nTotalDeFaltas) ? $nTotalDeFaltas : "";
                    }

                    if ($oFiltroRelatorio->iFrequencia == 4) {
                        $nValorFaltas = $iTotalDeAulasDadasGeral - $nTotalDeFaltas;
                    }

                    if ($aListaDeAlunos[$iContadorAluno]->getDiarioDeClasse()->reclassificadoPorBaixaFrequencia()) {
                        if ($nValorFaltas < '75') {
                            $nValorFaltas = '75';
                        }
                    }

                    if ($oRegencia->getRegencia()->getFrequenciaGlobal() == 'A') {
                        if ($nValorFaltas < '75') {
                            $nValorFaltas = '75';
                        }
                    }

                    $oPdf->SetFontSize(5.5);
                    $oPdf->Cell($oFiltroRelatorio->iTamanhoColunaResultado, $oFiltroRelatorio->iAltura, "{$nValorFaltas}", $sBordaAluno, 0, "C", $iCorLinha);
                    $oPdf->SetFontSize(6);
                    $oPdf->Cell($oFiltroRelatorio->iTamanhoColunaResultado, $oFiltroRelatorio->iAltura, "{$sResultadoGeral}", $sBordaAluno, 1, "C", $iCorLinha);
                } else {
                    $oPdf->Cell(12, $oFiltroRelatorio->iAltura, "{$sResultadoGeral}", $sBordaAluno, 1, "C", $iCorLinha);
                }
            }
        }

        // Finalizar seção
        finalizarSecao($oPdf, $oFiltroRelatorio);
    }
}

// Manter funções auxiliares originais necessárias
function dadosEscola(Turma $oTurma, $iEtapa) {
    $oTurma->oDadosEscola = new stdClass();
    $oTurma->oDadosEscola->iTotalHoras = '';

    foreach ($oTurma->getEtapas() as $oEtapa) {
        if ($iEtapa == $oEtapa->getEtapa()->getCodigo()) {
            $oTurma->oDadosEscola->sEtapa = $oEtapa->getEtapa()->getNome();
        }
    }

    $oTurma->oDadosEscola->iDia = $oTurma->getCalendario()->getDataResultadoFinal()->getDia();
    $oTurma->oDadosEscola->iMes = db_mes($oTurma->getCalendario()->getDataResultadoFinal()->getMes());
    $oTurma->oDadosEscola->iAno = $oTurma->getCalendario()->getDataResultadoFinal()->getAno();

    return $oTurma;
}

function cabecalhoPadrao($oPdf, $oFiltroRelatorio, $aDisciplinasPagina, Turma $oTurma, $iEtapa) {
    $tipoEnsino = determinarTipoEnsino($oTurma, $iEtapa);

    $oPdf->setfont('arial', 'b', 8);

    switch ($tipoEnsino['tipo']) {
        case 'educacao_infantil':
        case 'ef_anos_iniciais_1ano':
        case 'eja_iniciais_ciclo_basico':
            gerarCabecalhoSimples($oPdf, $tipoEnsino['tipo']);
            break;

        default:
            gerarCabecalhoComplexo($oPdf, $oFiltroRelatorio, $aDisciplinasPagina, $oTurma, $iEtapa);
            break;
    }
}

function cabecalhoScpf($oPdf, Turma $oTurma, $oFiltroRelatorio, $iCodigoEtapa) {
    $oPdf->SetFont('arial', 'b', 8);

    if ($oFiltroRelatorio->lBrasao) {
        $oPdf->Image("imagens/files/" . $oTurma->getEscola()->getLogo(), $oPdf->GetX(), $oPdf->GetY() - 6, 15);
    }

    textoAtoCabecalho($oFiltroRelatorio, $oTurma);

    $tipoEnsino = determinarTipoEnsino($oTurma, $iCodigoEtapa);
    $sTermo = gerarTextoTipo($oTurma, $iCodigoEtapa, $tipoEnsino);

    $sNomeEscola = $oTurma->getEscola()->getNome();
    if ($oTurma->getEscola()->getCodigoReferencia() != null) {
        $sNomeEscola = "{$oTurma->getEscola()->getCodigoReferencia()} - $sNomeEscola";
    }

    if ($oFiltroRelatorio->iTipoModelo != 2) {
        $oPdf->setXY(24, 5);
        $oPdf->multicell(77, 4, $sTermo, 0, "C", 0, 0);
        $oPdf->setXY(115, 5);
        $sBairro = $oTurma->getEscola()->getBairro();

        $sCabecalhoEscola = "{$sNomeEscola}\n";
        $sCabecalhoEscola .= "Mantenedora: {$oTurma->getEscola()->getDepartamento()->getInstituicao()->getDescricao()}\n";
        $sCabecalhoEscola .= "Endereco: {$oTurma->getEscola()->getEndereco()}";
        $sCabecalhoEscola .= ", {$oTurma->getEscola()->getNumeroEndereco()} - {$sBairro}\n";
        $sCabecalhoEscola .= "CEP: {$oTurma->getEscola()->getCep()}";
        $sCabecalhoEscola .= " - {$oTurma->getEscola()->getMunicipio()} / {$oTurma->getEscola()->getUf()}\n";

        $oPdf->multicell(105, 3, $sCabecalhoEscola, 0, "L", 0, 0);
        $oPdf->setX(90);
    } else {
        $sBairro = $oTurma->getEscola()->getBairro();
        $sCabecalhoEscola = "{$oTurma->getEscola()->getDepartamento()->getInstituicao()->getDescricao()}\n";
        $sCabecalhoEscola .= "{$oFiltroRelatorio->mCabecalho}\n";
        $sCabecalhoEscola .= "{$sNomeEscola}\n";
        $sCabecalhoEscola .= "{$oTurma->getEscola()->getEndereco()}, {$oTurma->getEscola()->getNumeroEndereco()} - {$sBairro}\n";

        $oPdf->SetXY(30, 6);
        $oPdf->MultiCell(152, 3, $sCabecalhoEscola, 0, "C");

        $oPdf->SetXY(60, 25);
        $oPdf->SetFont('arial', 'b', 7);
        $oPdf->Cell(95, 2, "ATA DE RESULTADOS FINAIS", 0, 1, "C", 0);

        $oPdf->Ln(4);
        $oPdf->SetX(20);
        $oPdf->SetFont('arial', '', 8);
        $oPdf->MultiCell(180, 3, $sTermo, 0, "L");
        $oPdf->SetFont('arial', 'b', 7);
    }

    $oEtapaTurma = EtapaRepository::getEtapaByCodigo($iCodigoEtapa);

    $oPdf->ln();
    $oPdf->SetFont('arial', 'b', 7);
    $oPdf->Cell(10, 4, "Curso: ", 0, 0, "L", 0);
    $oPdf->Cell(15, 4, $oTurma->getBaseCurricular()->getCurso()->getNome(), 0, 1, "L", 0);
    $oPdf->Cell(10, 4, "Etapa: ", 0, 0, "L", 0);
    $oPdf->Cell(40, 4, $oTurma->oDadosEscola->sEtapa, 0, 0, "L", 0);
    $oPdf->Cell(7, 4, "Ano: ", 0, 0, "L", 0);
    $oPdf->Cell(27, 4, $oTurma->getCalendario()->getAnoExecucao(), 0, 0, "L", 0);
    $oPdf->Cell(20, 4, "Carga Horária: ", 0, 0, "L", 0);
    $oPdf->Cell(15, 4, DBNumber::truncate($oTurma->getCargaHoraria($oEtapaTurma)), 0, 0, "L", 0);
    $oPdf->Cell(17, 4, "Dias Letivos: ", 0, 0, "L", 0);
    $oPdf->Cell(17, 4, $oTurma->getCalendario()->getDiasLetivos(), 0, 1, "L", 0);
    $oPdf->Cell(10, 4, "Turma: ", 0, 0, "L", 0);
    $oPdf->Cell(40, 4, $oTurma->getDescricao(), 0, 0, "L", 0);
    $oPdf->Cell(10, 4, "Turno: ", 0, 0, "L", 0);
    $oPdf->Cell(24, 4, $oTurma->getTurno()->getDescricao(), 0, 0, "L", 0);

    if ($oFiltroRelatorio->iImprimirRegente == 2) {
        $sRegente = '';
        $oProfessorConselheiro = $oTurma->getProfessorConselheiro();

        if (!empty($oProfessorConselheiro) && $oProfessorConselheiro->getNome() != '') {
            $sRegente = $oTurma->getProfessorConselheiro()->getNome();
        }
        $oPdf->Cell(12, 4, "Regente: ", 0, 0, "L", 0);
        $oPdf->Cell(80, 4, $sRegente, 0, 0, "L", 0);
    }
    $oPdf->ln();
}

function footerPadrao(FPDF $oPdf, Turma $oTurma, $oFiltroRelatorio, $iCodigoEtapa) {
    // Implementação simplificada do rodapé
    // Mantém a lógica original mas utilizando as funções auxiliares
    processarObservacoesEscola($oPdf, $oFiltroRelatorio);
    gerarAssinaturasPadrao($oPdf, $oFiltroRelatorio);
}

function assinaturaDocente($oPdf, $oTurma, $oFiltroRelatorio, $iCodigoEtapa) {
    $oPdf->AddPage();
    $oPdf->Ln(5);

    $oDaoRegenciaHorario = new cl_regenciahorario();
    $sCamposRegenciaHorario = "distinct ed20_i_codigo, case when ed20_i_tiposervidor = 1 then cgmrh.z01_nome";
    $sCamposRegenciaHorario .= " else cgmcgm.z01_nome end as z01_nome, ed59_i_turma";
    $sWhereRegenciaHorario = "ed59_i_turma = {$oTurma->getCodigo()} and ed58_ativo is true ";
    $sWhereRegenciaHorario .= " and ed59_i_serie = {$iCodigoEtapa}";
    $sSqlRegenciaHorario = $oDaoRegenciaHorario->sql_query(null, $sCamposRegenciaHorario, null, $sWhereRegenciaHorario);
    $rsRegenciaHorario = db_query($sSqlRegenciaHorario);

    if (is_resource($rsRegenciaHorario) && pg_num_rows($rsRegenciaHorario) > 0) {
        $iTotalRegenciaHorario = pg_num_rows($rsRegenciaHorario);

        for ($iContadorRegencia = 0; $iContadorRegencia < $iTotalRegenciaHorario; $iContadorRegencia++) {
            $oDadosRegenciaHorario = db_utils::fieldsMemory($rsRegenciaHorario, $iContadorRegencia);
            $sProfessor = "{$oDadosRegenciaHorario->z01_nome} - {$oDadosRegenciaHorario->ed20_i_codigo}";

            $oPdf->cell(10, 8, "", 0, 1, "L", 0);
            $oPdf->line(10, $oPdf->getY(), 70, $oPdf->getY());
            $oPdf->cell(190, 4, "Professor", 0, 1, "L", 0);
            $oPdf->cell(190, 4, $sProfessor, 0, 1, "L", 0);
        }
    } else {
        $oPdf->cell(220, 10, "Nenhum Professor Informado!", 0, 1, "L", 0);
    }
}

function ordernarAlunosPorNome(Matricula $oMatriculaAnterior, Matricula $oProximaMatricula) {
    $sNomeAnterior = TiraAcento($oMatriculaAnterior->getAluno()->getNome());
    $sProximoNome = TiraAcento($oProximaMatricula->getAluno()->getNome());
    return strnatcasecmp($sNomeAnterior, $sProximoNome);
}

function textoAtoCabecalho($oFiltroRelatorio, $oTurma) {
    $oDocumento = new libdocumento(5012);
    $oDocumento->dia = $oTurma->oDadosEscola->iDia;
    $oDocumento->mes_extenso = ucfirst($oTurma->oDadosEscola->iMes);
    $oDocumento->ano = $oTurma->oDadosEscola->iAno;

    $oDadosCabecalho = new stdClass();
    $oDadosCabecalho->aParagrafo = $oDocumento->getDocParagrafos();
    $oFiltroRelatorio->sCabecalho = $oDadosCabecalho->aParagrafo[1]->oParag->db02_texto;
}

function retornafaltas($aluno) {
    $sql = "SELECT sum(ed72_i_numfaltas) as numero_faltas
            from diarioavaliacao
            inner join procavaliacao on ed41_i_codigo = ed72_i_procavaliacao
            inner join diario on ed95_i_codigo = ed72_i_diario
            left join pareceraval on ed93_i_diarioavaliacao = ed72_i_codigo
            left join abonofalta on ed80_i_diarioavaliacao = ed72_i_codigo
            where ed95_i_aluno = " . $aluno;

    $rsfaltas = db_query($sql);
    $nfaltas = db_utils::fieldsMemory($rsfaltas, 0);
    return $nfaltas->numero_faltas;
}

function retornaaulasdadas($regencia) {
    $sql = "SELECT sum(ed78_i_aulasdadas) as aulasdadas
            from regenciaperiodo
            inner join procavaliacao on procavaliacao.ed41_i_codigo = regenciaperiodo.ed78_i_procavaliacao
            inner join regencia on regencia.ed59_i_codigo = regenciaperiodo.ed78_i_regencia
            inner join periodoavaliacao on periodoavaliacao.ed09_i_codigo = procavaliacao.ed41_i_periodoavaliacao
            inner join formaavaliacao on formaavaliacao.ed37_i_codigo = procavaliacao.ed41_i_formaavaliacao
            inner join procedimento on procedimento.ed40_i_codigo = procavaliacao.ed41_i_procedimento
            inner join disciplina on disciplina.ed12_i_codigo = regencia.ed59_i_disciplina
            inner join caddisciplina on ed232_i_codigo= ed12_i_caddisciplina
            inner join turma on turma.ed57_i_codigo = regencia.ed59_i_turma
            where ed78_i_regencia = {$regencia} and ed09_c_somach = 'S'";

    $rsAulas = db_query($sql);
    $nAulas = db_utils::fieldsMemory($rsAulas, 0);
    return $nAulas->aulasdadas;
}

function buscafaltas($aluno) {
    $sql = "select sum(ed72_i_numfaltas) as faltas
            from diarioavaliacao
            inner join procavaliacao on ed41_i_codigo = ed72_i_procavaliacao
            inner join periodoavaliacao on ed09_i_codigo = ed41_i_periodoavaliacao
            inner join diario on ed95_i_codigo = ed72_i_diario
            inner join aluno on ed47_i_codigo = ed95_i_aluno
            inner join matricula on ed60_i_aluno = ed47_i_codigo
            inner join turma on ed57_i_codigo = ed60_i_turma
            inner join calendario on ed52_i_codigo = ed57_i_calendario
            inner join matriculaserie on ed60_i_codigo = ed221_i_matricula
            inner join regencia on ed59_i_codigo = ed95_i_regencia and ed59_i_serie = ed221_i_serie
            inner join disciplina on ed12_i_codigo = ed59_i_disciplina
            inner join caddisciplina on ed232_i_codigo = ed12_i_caddisciplina
            where ed47_i_codigo = {$aluno}
            and ed72_i_numfaltas is not null
            and ed232_i_codigo = 6
            and ed52_i_ano = " . db_getsession("DB_anousu");

    $result = db_query($sql);
    $odados = db_utils::fieldsMemory($result, 0);
    return $odados->faltas;
}

function faltasFinal($aluno, $escola, $calendario) {
    $sql2 = "select ed60_i_aluno from matricula where ed60_i_codigo = {$aluno}";
    $sqlA = pg_query($sql2);
    $oAluno = db_utils::fieldsMemory($sqlA, 0);

    $sql1 = "select sum(ed72_i_numfaltas) as numero_faltas
             from diarioavaliacao
             inner join procavaliacao on ed41_i_codigo = ed72_i_procavaliacao
             left join pareceraval on ed93_i_diarioavaliacao = ed72_i_codigo
             left join abonofalta on ed80_i_diarioavaliacao = ed72_i_codigo
             where ed72_i_diario IN (
                 SELECT ed95_i_codigo FROM diario
                 WHERE ed95_i_aluno = {$oAluno->ed60_i_aluno}
                 AND ed95_i_escola = {$escola}
                 AND ed95_i_calendario = {$calendario}
             )";

    $sql = pg_query($sql1);
    $resultado = pg_fetch_all($sql);
    return $resultado[0]["numero_faltas"];
}

function faltasAnosFinais($aluno, $ano) {
    $sql = "select sum(ed72_i_numfaltas) as faltas
            from diarioavaliacao
            inner join procavaliacao on ed41_i_codigo = ed72_i_procavaliacao
            inner join periodoavaliacao on ed09_i_codigo = ed41_i_periodoavaliacao
            inner join diario on ed95_i_codigo = ed72_i_diario
            inner join aluno on ed47_i_codigo = ed95_i_aluno
            inner join matricula on ed60_i_aluno = ed47_i_codigo
            inner join turma on ed57_i_codigo = ed60_i_turma
            inner join calendario on ed52_i_codigo = ed57_i_calendario
            inner join matriculaserie on ed60_i_codigo = ed221_i_matricula
            inner join regencia on ed59_i_codigo = ed95_i_regencia and ed59_i_serie = ed221_i_serie
            inner join disciplina on ed12_i_codigo = ed59_i_disciplina
            inner join caddisciplina on ed232_i_codigo = ed12_i_caddisciplina
            where ed60_i_codigo = {$aluno}
            and ed72_i_numfaltas is not null
            and ed52_i_ano = {$ano}";

    $result = db_query($sql);
    $odados = db_utils::fieldsMemory($result, 0);
    return $odados->faltas;
}

/**
 * Autor: Uemerson Santana
 * Data: 23/01/2025
 * Demanda: 18059
 * Razao: Ajustado para aceitar opcionalmente o código da turma e,
 *        quando informado, usar o turno da turma específica do aluno.
 *        Isso evita aplicar 1522h de turma INTEGRAL para alunos de
 *        turmas MANHÃ/TARDE no mesmo calendário.
 */
function percfrequencia($calendar, $iTurma = null) {
    $sqla = "select ed52_c_descr,
                    ed15_c_nome,
                    ed57_i_codigo
             from calendario
             inner join turma on ed57_i_calendario = ed52_i_codigo
             inner join turno on ed15_i_codigo = ed57_i_turno
             where ed52_i_codigo = " . $calendar;

    $sql = pg_query($sqla);
    $resultado = pg_fetch_all($sql);
    if (empty($resultado)) {
        return 200;
    }

    // Autor: Uemerson Santana
    // Data: 23/01/2025
    // Demanda: 18059
    // Razao: Quando a função receber a turma, priorizar a linha correspondente
    //        entre as turmas do calendário, garantindo que o turno reflita
    //        a turma real do aluno.
    $linhaCalendario = $resultado[0];
    if (!is_null($iTurma)) {
        foreach ($resultado as $linha) {
            if (isset($linha["ed57_i_codigo"]) && (int)$linha["ed57_i_codigo"] === (int)$iTurma) {
                $linhaCalendario = $linha;
                break;
            }
        }
    }

    $nome = $linhaCalendario["ed52_c_descr"];
    $turno = substr($linhaCalendario["ed15_c_nome"], 0, 5);
    $turno_completo = trim($linhaCalendario["ed15_c_nome"]);

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

/**
 * Processa alunos complexos para ensinos com disciplinas
 */
function processarAlunoComplexo($oPdf, $aluno, $contador, $oFiltroRelatorio, $aDisciplinasPagina, $oTurma, $tipoEnsino, $iAnoCalendario) {
    global $lObservacaoProgressaoParcial;

    $sNomeAluno = trim($aluno->getAluno()->getNome());
    $iLinhasAluno = $oPdf->NbLines($oFiltroRelatorio->iTamanhoTotalColunaDisciplina, $sNomeAluno);
    $oFiltroRelatorio->iAltura = $iLinhasAluno * 4;

    $iCorLinha = ($contador % 2 == 0) ? 1 : 0;
    $sBordaAluno = "LR";

    $oPdf->SetFillColor(225, 225, 225);
    $oPdf->setfont('arial', '', 10);

    // Verificar troca de turma
    if ($oFiltroRelatorio->iTrocaTurma == 1 && $aluno->getSituacao() == "TROCA DE TURMA" || $aluno->getSituacao() == "TROCA DE MODALIDADE") {
        return;
    }

    // Número do aluno
    $oPdf->Cell(5, $oFiltroRelatorio->iAltura, $aluno->getNumeroOrdemAluno(), $sBordaAluno, 0, "C", $iCorLinha);

    // Nome do aluno
    $iPosicaoY = $oPdf->GetY();
    $iPosicaoX = $oPdf->GetX();

    if (strlen($sNomeAluno) > 32) {
        $oPdf->setfont('arial', '', 7);
    }
    $oPdf->MultiCell($oFiltroRelatorio->iTamanhoTotalColunaDisciplina + 14, 4, $sNomeAluno, $sBordaAluno, 'L', $iCorLinha);
    $oPdf->setfont('arial', '', 10);
    $oPdf->SetXY($iPosicaoX + $oFiltroRelatorio->iTamanhoTotalColunaDisciplina + 14, $iPosicaoY);

    // Processar situação do aluno
    if ($aluno->getSituacao() != "MATRICULADO") {
        $oDtEncerramento = $aluno->getDataEncerramento();
        $sDtEncerramento = "";
        if (!empty($oDtEncerramento)) {
            $sDtEncerramento = " em " . $oDtEncerramento->convertTo(DBDate::DATA_PTBR);
        }

        $sTransferido = $aluno->getSituacao();
        if ($sTransferido == "TRANSFERIDO FORA") {
            $sTransferido = explode(" ", $sTransferido)[0];
        }

        $iLinha = ($oFiltroRelatorio->iTamanhoColunaAbrevDisciplina * $oFiltroRelatorio->iAuxiliarTransferido) + 12;
        $oPdf->Cell($iLinha, $oFiltroRelatorio->iAltura, "{$sTransferido}" . $sDtEncerramento, $sBordaAluno, 1, "L", $iCorLinha);
    } else {
        // Processar disciplinas do aluno
        $sResultadoGeral = processarDisciplinasAluno($oPdf, $aluno, $aDisciplinasPagina, $oFiltroRelatorio, $oTurma, $tipoEnsino, $iAnoCalendario, $sBordaAluno, $iCorLinha);

        // Resultado final
        exibirResultadoFinal($oPdf, $oFiltroRelatorio, $sResultadoGeral, $sBordaAluno, $iCorLinha, $aluno, $tipoEnsino);
    }
}

/**
 * Processa as disciplinas de um aluno
 */
function processarDisciplinasAluno($oPdf, $aluno, $aDisciplinasPagina, $oFiltroRelatorio, $oTurma, $tipoEnsino, $iAnoCalendario, $sBordaAluno, $iCorLinha) {
    global $lObservacaoProgressaoParcial;

    $sResultadoGeral = 'A';
    $lAprovadoProgressaoAno = verificarProgressaoParcial($aluno, $oTurma);
    $iTotalDeAulasDadasGeral = 0;
    $nTotalDeFaltas = 0;

    foreach ($aDisciplinasPagina as $oRegenciaTurma) {
        db_inicio_transacao();

        $oRegencia = $aluno->getDiarioDeClasse()->getDisciplinasPorRegencia($oRegenciaTurma);
        $sAmparado = processarAmparo($oRegencia, $oFiltroRelatorio, $oTurma);

        // Dados de frequência
        $iNumeroFaltas = $oRegencia->getTotalFaltas();
        $iTotalDeAulasDadas = $oRegencia->getTotalDeAulasParaCalculo();
        $iTotalDeAulasDadasGeral += $iTotalDeAulasDadas;

        if ($oFiltroRelatorio->lCalculaFrequencia == 2) {
            $nTotalDeFaltas += $iNumeroFaltas;
        }

        db_fim_transacao();

        // Resultado da disciplina
        $nValorAproveitamento = obterValorAproveitamento($oRegencia, $oTurma, $iAnoCalendario, $sAmparado, $aluno);

        // Verificar se foi reprovado
        $sResultadoAprovacao = $aluno->getDiarioDeClasse()->getResultadoFinal();
        if ($sResultadoAprovacao == 'R' && !$lAprovadoProgressaoAno) {
            $sResultadoGeral = 'R';
        }

        // Exibir aproveitamento na célula
        exibirAproveitamentoDisciplina($oPdf, $oFiltroRelatorio, $nValorAproveitamento, $iNumeroFaltas, $iTotalDeAulasDadas, $oRegencia, $sBordaAluno, $iCorLinha);
    }

    // Processar colunas em branco
    for ($iContadorColunaBranco = 0; $iContadorColunaBranco < $oFiltroRelatorio->iColunasEmBranco; $iContadorColunaBranco++) {
        $oPdf->Cell($oFiltroRelatorio->iTamanhoColunaAbrevDisciplina - 2, $oFiltroRelatorio->iAltura, "", $sBordaAluno, 0, "C", $iCorLinha);
    }

    // Ajustar resultado final
    $sResultadoGeral = ajustarResultadoFinal($sResultadoGeral, $lAprovadoProgressaoAno, $oTurma, $iAnoCalendario);

    if ($lAprovadoProgressaoAno) {
        $lObservacaoProgressaoParcial = true;
        $sResultadoGeral .= '/D';
    }

    return $sResultadoGeral;
}

/**
 * Verifica se aluno teve progressão parcial
 */
function verificarProgressaoParcial($aluno, $oTurma) {
    foreach ($aluno->getAluno()->getProgressaoParcial() as $oProgressaoParcial) {
        if (
            $oTurma->getCalendario()->getAnoExecucao() == $oProgressaoParcial->getAno()
            && $oProgressaoParcial->getCodigoDiarioFinal() != null
        ) {
            return true;
        }
    }
    return false;
}

/**
 * Processa amparo do aluno
 */
function processarAmparo($oRegencia, $oFiltroRelatorio, $oTurma) {
    $sAmparado = '';

    if ($oRegencia->getAmparo() != null && $oRegencia->getAmparo()->isTotal()) {
        if ($oRegencia->getAmparo()->getCodigoConvencaoAmparo()) {
            $oDaoConvencaoAmparo = new cl_convencaoamp();
            $sSqlConvencaoAmparo = $oDaoConvencaoAmparo->sql_query_file($oRegencia->getAmparo()->getCodigoConvencaoAmparo());
            $rsConvencaoAmparo = $oDaoConvencaoAmparo->sql_record($sSqlConvencaoAmparo);
            $oConvencaoAmparo = db_utils::fieldsMemory($rsConvencaoAmparo, 0);
            $sAmparado = $oConvencaoAmparo->ed250_c_abrev;

            $oFiltroRelatorio->aJustificativas[$oTurma->getCodigo()][] = $oConvencaoAmparo->ed250_c_abrev . ' - ' . $oConvencaoAmparo->ed250_c_descr;
        }

        if ($oRegencia->getAmparo()->getCodigoJustificativa()) {
            $sAmparado = 'AMP ' . $oRegencia->getAmparo()->getCodigoJustificativa();

            $oDaoJustificativa = new cl_justificativa();
            $sSqlJustificativa = $oDaoJustificativa->sql_query_file($oRegencia->getAmparo()->getCodigoJustificativa());
            $rsJustitificativa = $oDaoJustificativa->sql_record($sSqlJustificativa);
            $oDadosJustificativa = db_utils::fieldsMemory($rsJustitificativa, 0);
            $oFiltroRelatorio->aJustificativas[$oTurma->getCodigo()][] = $oRegencia->getAmparo()->getCodigoJustificativa() . ' - ' . $oDadosJustificativa->ed06_c_descr;
        }
    }

    return $sAmparado;
}

/**
 * Obtém valor de aproveitamento da disciplina
 */
function obterValorAproveitamento($oRegencia, $oTurma, $iAnoCalendario, $sAmparado, $aluno) {
    $oResultadoFinal = $oRegencia->getResultadoFinal();
    $iCodigoEnsino = $oTurma->getBaseCurricular()->getCurso()->getEnsino()->getCodigo();

    $nValorAproveitamento = $oResultadoFinal->getValorAprovacao();

    // Verificar aprovação por conselho
    if (
        $oResultadoFinal->getFormaAprovacaoConselho() instanceof AprovacaoConselho
        && $oResultadoFinal->getFormaAprovacaoConselho()->getFormaAprovacao() == 1
        && $oResultadoFinal->getFormaAprovacaoConselho()->getAlterarNotaFinal() == 2
    ) {
        $nValorAproveitamento = $oResultadoFinal->getFormaAprovacaoConselho()->getAvaliacaoConselho();
    }

    // Verificar se é parecer
    $oFormaAvaliacao = $oResultadoFinal->getResultadoAvaliacao()->getFormaDeAvaliacao();
    if (!empty($oFormaAvaliacao) && $oFormaAvaliacao->getTipo() == "PARECER") {
        $nValorAproveitamento = $oResultadoFinal->getResultadoAprovacao();

        if (!empty($iCodigoEnsino) && ($nValorAproveitamento == 'A' || $nValorAproveitamento == 'R')) {
            $aDadosTermo = DBEducacaoTermo::getTermoEncerramento($iCodigoEnsino, $nValorAproveitamento, $iAnoCalendario);
            if (isset($aDadosTermo[0])) {
                $nValorAproveitamento = $aDadosTermo[0]->sAbreviatura;
            }
        }
    }

    // Aplicar arredondamento se for nota
    if (is_numeric($nValorAproveitamento)) {
        $nValorAproveitamento = ArredondamentoNota::formatar(
            $nValorAproveitamento,
            $oTurma->getCalendario()->getAnoExecucao()
        );
    }

    // Verificar amparo
    if ($sAmparado != '') {
        $nValorAproveitamento = $sAmparado;
    }

    // Formatação
    $nValorAproveitamento = str_replace(".", ",", $nValorAproveitamento);

    if ($nValorAproveitamento == 'Parecer') {
        $nValorAproveitamento = 'Rel';
    }

    // Verificar necessidades especiais
    $iCodigoAluno = $aluno->getAluno()->getCodigoAluno();
    $iCodigoMatriculaTmp = $aluno->getMatricula();
    if (isAlunoComNecessidadesEspeciais($iCodigoAluno) and isAvaliadoPorParecer($iCodigoMatriculaTmp) ) {
        $nValorAproveitamento = "PD";
    }

    return $nValorAproveitamento;
}

/**
 * Exibe aproveitamento da disciplina
 */
function exibirAproveitamentoDisciplina($oPdf, $oFiltroRelatorio, $nValorAproveitamento, $iNumeroFaltas, $iTotalDeAulasDadas, $oRegencia, $sBordaAluno, $iCorLinha) {
    if ($oFiltroRelatorio->lCalculaFrequencia == 1 && $oFiltroRelatorio->iFrequencia != 1) {
        $oPdf->Cell($oFiltroRelatorio->iTamanhoColunaAbrevDisciplina - 6, $oFiltroRelatorio->iAltura, "{$nValorAproveitamento}", $sBordaAluno, 0, "C", $iCorLinha);

        $nValorFalta = !empty($iNumeroFaltas) ? $iNumeroFaltas : "";

        if ($oFiltroRelatorio->iFrequencia == 2) {
            $nValorFalta = $oRegencia->calcularPercentualFrequencia();
        }

        if ($oFiltroRelatorio->iFrequencia == 4) {
            $nValorFalta = $iTotalDeAulasDadas - $iNumeroFaltas;
        }

        // Verificar reclassificação
        if ($oRegencia->reclassificadoPorBaixaFrequencia() || $oRegencia->getRegencia()->getFrequenciaGlobal() == 'A') {
            if ($nValorFalta < '75') {
                $nValorFalta = '75';
            }
        }

        $oPdf->SetFontSize(5.5);
        $oPdf->Cell($oFiltroRelatorio->iTamanhoColunaAbrevDisciplina - 10, $oFiltroRelatorio->iAltura, "{$nValorFalta}", $sBordaAluno, 0, "C", $iCorLinha);
        $oPdf->SetFontSize(6);
    } else {
        $oPdf->Cell($oFiltroRelatorio->iTamanhoColunaAbrevDisciplina - 2, $oFiltroRelatorio->iAltura, $nValorAproveitamento, $sBordaAluno, 0, "C", $iCorLinha);
    }
}

/**
 * Ajusta resultado final baseado no ensino
 */
function ajustarResultadoFinal($sResultadoGeral, $lAprovadoProgressaoAno, $oTurma, $iAnoCalendario) {
    $iCodigoEnsino = $oTurma->getBaseCurricular()->getCurso()->getEnsino()->getCodigo();

    if (!empty($iCodigoEnsino) && ($sResultadoGeral == 'A' || $sResultadoGeral == 'R')) {
        $aDadosTermo = DBEducacaoTermo::getTermoEncerramento($iCodigoEnsino, $sResultadoGeral, $iAnoCalendario);
        if (isset($aDadosTermo[0])) {
            $sResultadoGeral = $aDadosTermo[0]->sAbreviatura;
        }
    }

    if (($sResultadoGeral == "APR") or ($sResultadoGeral == "Apr")) {
        $sResultadoGeral = "AP";
    }

    return $sResultadoGeral;
}

/**
 * Exibe resultado final do aluno
 */
function exibirResultadoFinal($oPdf, $oFiltroRelatorio, $sResultadoGeral, $sBordaAluno, $iCorLinha, $aluno, $tipoEnsino) {
    if ($oFiltroRelatorio->lCalculaFrequencia == 2 && $oFiltroRelatorio->iFrequencia != 1) {
        // Calcular frequência baseado no tipo
        $nValorFaltas = calcularFrequenciaFinal($aluno, $oFiltroRelatorio, $tipoEnsino);

        $oPdf->Cell($oFiltroRelatorio->iTamanhoColunaResultado + 3, $oFiltroRelatorio->iAltura, "{$nValorFaltas}", $sBordaAluno, 0, "C", $iCorLinha);
        $oPdf->Cell($oFiltroRelatorio->iTamanhoColunaResultado + 3, $oFiltroRelatorio->iAltura, "{$sResultadoGeral}", $sBordaAluno, 1, "C", $iCorLinha);
    } else {
        $oPdf->Cell(12, $oFiltroRelatorio->iAltura, "{$sResultadoGeral}", $sBordaAluno, 1, "C", $iCorLinha);
    }
}

/**
 * Calcula frequência final baseado no tipo de ensino
 */
function calcularFrequenciaFinal($aluno, $oFiltroRelatorio, $tipoEnsino) {
    $codAluno2 = $aluno->getCodigo();
    $escola = db_getsession("DB_coddepto");
    $iAnoCalendario = date('Y'); // ou obter do contexto apropriado

    switch ($tipoEnsino['tipo']) {
        case 'ef_anos_finais':
            $nValorFaltas = calcularFrequenciaAnosFinaisATA($aluno, $oFiltroRelatorio);
            break;

        case 'eja_finais_ciclos':
            $nValorFaltas = calcularFrequenciaEjaFinais($aluno, $oFiltroRelatorio);
            break;

        default:
            // Usar método padrão com GradeAproveitamentoAluno
            $iCodigoMatricula = $aluno->getCodigo();
            // Seria necessário obter a regência atual do contexto
            $oDadosFrequencia = calcularFrequenciaAluno($iCodigoMatricula, 0); // placeholder
            $nValorFaltas = $oDadosFrequencia->nPercentualFrequencia;
            break;
    }

    return $nValorFaltas;
}

/**
 * Calcula a frequência específica para Anos Finais usando valores fixos estimados
 *
 * Esta função implementa uma abordagem alternativa ao cálculo de frequência para
 * o segmento "Anos Finais", utilizando valores fixos predefinidos em vez do
 * cálculo real de aulas dadas por disciplina.
 *
 * CONTEXTO DO PROBLEMA:
 * - Anos Finais possui múltiplas disciplinas com diferentes cargas horárias
 * - O sistema tradicional soma aulas reais de cada disciplina (ex: 1150 aulas)
 * - Para padronização do relatório, usa-se valor fixo estimado (1000 aulas)
 * - Isso garante consistência entre diferentes turmas e períodos
 *
 * FLUXO DE CÁLCULO:
 * 1. Obtém dados básicos do aluno (código, escola)
 * 2. Busca total de aulas fixo via percfrequencia() baseado no calendário
 * 3. Busca total de faltas reais do aluno via faltasFinal()
 * 4. Calcula percentual: floor((aulas_fixas - faltas) / aulas_fixas * 100)
 * 5. Aplica ajustes baseados no tipo de frequência solicitada
 * 6. Verifica reclassificação por baixa frequência (mínimo 75%)
 * 7. Retorna objeto compatível com calcularFrequenciaAluno()
 *
 * TIPOS DE FREQUÊNCIA SUPORTADOS:
 * - Tipo 2 (padrão): Percentual de frequência (0-100%)
 * - Tipo 3: Número absoluto de faltas
 * - Tipo 4: Número de aulas frequentadas (aulas_totais - faltas)
 *
 * VALORES FIXOS POR SEGMENTO (via percfrequencia):
 * - Anos Finais: 1000 aulas
 * - EJA Anos Finais Diurno: 1200 aulas
 * - EJA Anos Finais Noturno: 1000 aulas
 * - Anos Iniciais: 200 aulas
 * - Educação Infantil: 200 aulas
 *
 * RECLASSIFICAÇÃO POR BAIXA FREQUÊNCIA:
 * - Se aluno foi reclassificado devido baixa frequência
 * - E a frequência calculada for < 75%
 * - Força frequência = 75% (valor mínimo legal)
 *
 * COMPATIBILIDADE DE RETORNO:
 * Retorna objeto stdClass no mesmo formato que calcularFrequenciaAluno():
 * - nPercentualFrequencia: Valor final calculado
 * - iTotalFaltas: Número real de faltas
 * - iTotalAulas: Total de aulas fixo
 * - iFaltasAbonadas: Sempre 0 (não calculado nesta função)
 * - lReclassificadoBaixaFrequencia: Sempre false (não calculado nesta função)
 *
 * EXEMPLO DE USO:
 * ```php
 * // Para aluno com 202 faltas em Anos Finais:
 * $oDados = calcularFrequenciaAnosFinaisATA($aluno, $filtroRelatorio);
 * // Resultado: (1000 - 202) / 1000 * 100 = 79.8% ? floor = 79%
 * echo $oDados->nPercentualFrequencia; // 79
 * echo $oDados->iTotalAulas;           // 1000
 * echo $oDados->iTotalFaltas;          // 202
 * ```
 *
 * INTEGRAÇÃO COM SISTEMA:
 * - Chamada via operador ternário baseado em $tipoEnsino['tipo']
 * - Se 'ef_anos_finais': usa esta função
 * - Caso contrário: usa calcularFrequenciaAluno() (cálculo real)
 * - Ambas retornam objetos compatíveis
 *
 * @author Uemerson Santana
 * @date 16/07/2025
 * @version 1.0
 *
 * @param Matricula $aluno Objeto da matrícula do aluno
 * @param stdClass $oFiltroRelatorio Objeto contendo configurações do relatório
 *        - $oFiltroRelatorio->iFrequencia: Tipo de frequência (2=%, 3=faltas, 4=presença)
 *
 * @return stdClass Objeto contendo dados de frequência:
 *         - nPercentualFrequencia: Valor calculado conforme tipo solicitado
 *         - iTotalFaltas: Número total de faltas do aluno
 *         - iTotalAulas: Total de aulas fixo para o segmento
 *         - iFaltasAbonadas: Sempre 0 (não implementado)
 *         - lReclassificadoBaixaFrequencia: Sempre false (não implementado)
 *
 * @throws Exception Se não conseguir acessar dados do aluno ou escola
 *
 * @see calcularFrequenciaAluno() Função alternativa para cálculo real
 * @see percfrequencia() Função que determina valores fixos por segmento
 * @see faltasFinal() Função que busca total de faltas do aluno
 *
 * @todo Implementar cálculo de iFaltasAbonadas
 * @todo Implementar lReclassificadoBaixaFrequencia
 * @todo Validar se $calendar deveria vir do objeto turma em vez de date('Y')
 */
 function calcularFrequenciaAnosFinaisATA($aluno, $oFiltroRelatorio) {
    $codAluno2 = $aluno->getCodigo();
    $escola = db_getsession("DB_coddepto");
    $calendar = $aluno->getTurma()->getCalendario()->getCodigo(); // ed52_i_codigo

    // Autor: Uemerson Santana
    // Data: 23/01/2025
    // Demanda: 18059
    // Razao: Passar a turma do aluno para percfrequencia(), garantindo que
    //        o turno usado (INTEGRAL ou não) seja o da turma real do aluno.
    $iCodigoTurma = $aluno->getTurma()->getCodigo();
    $iTotalDeAulasDadas = percfrequencia($calendar, $iCodigoTurma);
    $iNumeroFaltas = faltasFinal($codAluno2, $escola, $calendar);

    $nValorFaltas = floor(($iTotalDeAulasDadas - $iNumeroFaltas) / $iTotalDeAulasDadas * 100);

    if ($oFiltroRelatorio->iFrequencia == 3) {
        $nValorFaltas = !empty($iNumeroFaltas) ? $iNumeroFaltas : "";
    }
    if ($oFiltroRelatorio->iFrequencia == 4) {
        $nValorFaltas = $iTotalDeAulasDadas - $iNumeroFaltas;
    }

    if ($aluno->getDiarioDeClasse()->reclassificadoPorBaixaFrequencia()) {
        if ($nValorFaltas < '75') {
            $nValorFaltas = '75';
        }
    }

    // AJUSTE: Retornar objeto no formato esperado (código inteligente)
    return (object) [
        'nPercentualFrequencia' => $nValorFaltas,
        'iTotalFaltas' => $iNumeroFaltas,
        'iTotalAulas' => $iTotalDeAulasDadas,
        'iFaltasAbonadas' => 0,
        'lReclassificadoBaixaFrequencia' => false
    ];
}

if ($oFiltroRelatorio->lCalculaFrequencia == 2 && $oFiltroRelatorio->iFrequencia != 1) {
    $iCodigoMatricula = $aListaDeAlunos[$iContadorAluno]->getCodigo();

    // Usar função específica para Anos Finais
    $oDadosFrequencia = ($tipoEnsino['tipo'] == 'ef_anos_finais')
        ? calcularFrequenciaAnosFinaisATA($aListaDeAlunos[$iContadorAluno], $oFiltroRelatorio)
        : calcularFrequenciaAluno($iCodigoMatricula, $aDisciplinasPagina[0]->getCodigo());

    $nValorFaltas = $oDadosFrequencia->nPercentualFrequencia;

    if ($oFiltroRelatorio->iFrequencia == 3) {
        $nValorFaltas = !empty($oDadosFrequencia->iTotalFaltas) ? $oDadosFrequencia->iTotalFaltas : "";
    }

    if ($oFiltroRelatorio->iFrequencia == 4) {
        $nValorFaltas = $oDadosFrequencia->iTotalAulas - $oDadosFrequencia->iTotalFaltas;
    }

    if ($aListaDeAlunos[$iContadorAluno]->getDiarioDeClasse()->reclassificadoPorBaixaFrequencia()
        || ($oRegencia->getRegencia()->getFrequenciaGlobal() == 'A' && 'REP' != strtoupper($sResultadoGeral))) {
        if ($nValorFaltas < '75') {
            $nValorFaltas = '75';
        }
    }

    $nValorFaltas = floor($nValorFaltas);

    $oPdf->Cell($oFiltroRelatorio->iTamanhoColunaResultado + 3, $oFiltroRelatorio->iAltura, "{$nValorFaltas}", $sBordaAluno, 0, "C", $iCorLinha);
    $oPdf->Cell($oFiltroRelatorio->iTamanhoColunaResultado + 3, $oFiltroRelatorio->iAltura, "{$sResultadoGeral}", $sBordaAluno, 1, "C", $iCorLinha);
} else {
    $oPdf->Cell(12, $oFiltroRelatorio->iAltura, "{$sResultadoGeral}", $sBordaAluno, 1, "C", $iCorLinha);
}

/**
 * Calcula frequência para EJA finais
 */
function calcularFrequenciaEjaFinais($aluno, $oFiltroRelatorio) {
    // Implementação similar aos anos finais mas com especificidades do EJA
    return calcularFrequenciaAnosFinaisATA($aluno, $oFiltroRelatorio);
}

/**
 * Gera linhas em branco quando necessário
 */
function gerarLinhasEmBranco($oPdf, $oFiltroRelatorio) {
    if ($oFiltroRelatorio->iTipoModelo != 2) {
        if (ceil($oPdf->GetY()) < 227) {
            $iLinhasEmBranco = (227 - $oPdf->GetY()) / 4;
            for ($i = 0; $i < $iLinhasEmBranco; $i++) {
                $oPdf->Cell(5, 4, "", "LR", 0, "C", 0);
                $oPdf->Cell($oFiltroRelatorio->iTamanhoTotalColunaDisciplina + 14, 4, "", 0, 0, "LR", 0);

                for ($iContadorDisciplinas = 0; $iContadorDisciplinas < $oFiltroRelatorio->iContadorDisciplinasImpressas; $iContadorDisciplinas++) {
                    if ($oFiltroRelatorio->lCalculaFrequencia == 1 && $oFiltroRelatorio->iFrequencia != 1) {
                        $oPdf->Cell($oFiltroRelatorio->iTamanhoColunaAbrevDisciplina - 6, 4, "", "LR", 0, "C", 0);
                        $oPdf->Cell($oFiltroRelatorio->iTamanhoColunaAbrevDisciplina - 10, 4, "", "LR", 0, "C", 0);
                    } else {
                        $oPdf->Cell($oFiltroRelatorio->iTamanhoColunaAbrevDisciplina - 2, 4, "", "LR", 0, "C", 0);
                    }
                }

                for ($iContadorColunaBranco = 0; $iContadorColunaBranco < $oFiltroRelatorio->iColunasEmBranco; $iContadorColunaBranco++) {
                    $oPdf->Cell($oFiltroRelatorio->iTamanhoColunaAbrevDisciplina - 2, 4, "", "LR", 0, "C", 0);
                }

                if ($oFiltroRelatorio->iFrequencia != 1 && $oFiltroRelatorio->lCalculaFrequencia == 2) {
                    $oPdf->Cell($oFiltroRelatorio->iTamanhoColunaResultado + 3, 4, "", "LR", 0, "C", 0);
                    $oPdf->Cell($oFiltroRelatorio->iTamanhoColunaResultado + 3, 4, "", "LR", 1, "C", 0);
                } else {
                    $oPdf->Cell(12, 4, "", "LR", 1, "C", 0);
                }
            }
        }
    }
}

/**
 * Finaliza o processamento de uma seção
 */
function finalizarSecao($oPdf, $oFiltroRelatorio) {
    gerarLinhasEmBranco($oPdf, $oFiltroRelatorio);
    processarObservacoesEscola($oPdf, $oFiltroRelatorio);

    $oPdf->Cell(192, 4, "", "T", 0, "C", 0);
    $oPdf->SetFont('arial', '', 10);
    $oPdf->Cell(10, 6, "", 0, 0, "C", 0);
    $oPdf->Cell(172, 6, "", "T", 1, "L", 0);

    gerarAtaCelula($oPdf, $oFiltroRelatorio->ataRespNome);
    gerarAssinaturasPadrao($oPdf, $oFiltroRelatorio);
}

?>

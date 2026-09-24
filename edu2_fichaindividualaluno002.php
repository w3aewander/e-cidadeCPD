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

/**
 * @author Uemerson Santana
 * @date 11/09/2025
 * @version 2.0 (Versão refatorada)
 * @description Ficha individual do aluno 002 do aluno com código otimizado e organizado
 */

// error_reporting(E_ALL);           // ou 32767
// ini_set('display_errors', 1);     // mostra erros na tela
// ini_set('display_startup_errors', 1);

require_once(modification("libs/db_stdlibwebseller.php"));
require_once(modification("fpdf151educacao/pdfwebseller.php"));
require_once(modification("libs/db_utils.php"));
require_once(modification("libs/db_libdocumento.php"));
require_once(modification("libs/db_libparagrafo.php"));
require_once(modification("dbforms/db_funcoes.php"));
require_once(modification("model/educacao/ArredondamentoNota.model.php"));
require_once(modification("model/educacao/Turma.model.php"));
require_once(modification("model/educacao/TurmaRepository.model.php"));
require_once(modification("model/educacao/MatriculaRepository.model.php"));
require_once(modification("model/educacao/EtapaRepository.model.php"));

// ===================================================================================================
// SEÇÃO 0: FUNÇÕES DE INTEGRAÇÃO RPC
// ===================================================================================================

/**
 * Executa consulta SQL direta para obter dados das avaliações
 * Simplificação para evitar dependências complexas do RPC
 * @param object $parametros Parâmetros para busca
 * @return object Dados retornados
 */
function executarRPCLancamento($parametros) {
    $oRetorno = new stdClass();
    $oRetorno->aAlunos = array();

    // Busca dados da regência e disciplina
    $sql = "SELECT r.ed59_i_codigo as regencia,
                   d.ed232_c_descr as disciplina
            FROM regencia r
            INNER JOIN disciplina disc ON disc.ed12_i_codigo = r.ed59_i_disciplina
            INNER JOIN caddisciplina d ON d.ed232_i_codigo = disc.ed12_i_caddisciplina
            WHERE r.ed59_i_codigo = {$parametros->iRegencia}";

    $result = db_query($sql);
    if (pg_num_rows($result) == 0) {
        return $oRetorno;
    }

    $dadosRegencia = db_utils::fieldsMemory($result, 0);

    // Busca avaliações do aluno específico - incluindo disciplinas com freq_global='A'
    $sqlAvaliacoes = "SELECT da.ed72_i_valornota as nota,
                             da.ed72_c_valorconceito as conceito,
                             COALESCE(da.ed72_i_numfaltas, 0) as faltas,
                             da.ed72_c_aprovmin as minimo,
                             da.ed72_c_amparo as amparo,
                             pa.ed41_i_sequencia as sequencia,
                             r.ed59_c_freqglob as freq_global,
                             per.ed09_c_descr as periodo
                      FROM diario d
                      INNER JOIN regencia r ON r.ed59_i_codigo = d.ed95_i_regencia
                      INNER JOIN diarioavaliacao da ON da.ed72_i_diario = d.ed95_i_codigo
                      INNER JOIN procavaliacao pa ON pa.ed41_i_codigo = da.ed72_i_procavaliacao
                      INNER JOIN periodoavaliacao per ON per.ed09_i_codigo = pa.ed41_i_periodoavaliacao
                      WHERE d.ed95_i_regencia = {$parametros->iRegencia}
                        AND d.ed95_i_aluno = (SELECT ed60_i_aluno FROM matricula WHERE ed60_i_codigo = {$parametros->iMatricula})
                        AND d.ed95_i_serie = {$parametros->iEtapa}
                      ORDER BY pa.ed41_i_sequencia";

    $resultAvaliacoes = db_query($sqlAvaliacoes);

    $oDadosAluno = new stdClass();
    $oDadosAluno->iMatricula = $parametros->iMatricula;
    $oDadosAluno->oDisciplina = new stdClass();
    $oDadosAluno->oDisciplina->iCodigoRegencia = $dadosRegencia->regencia;
    $oDadosAluno->oDisciplina->sDescricao = urlencode($dadosRegencia->disciplina);
    $oDadosAluno->oDisciplina->aAproveitamentos = array();

    while ($linha = pg_fetch_object($resultAvaliacoes)) {
        $oAvaliacao = new stdClass();
        $oAvaliacao->nNota = $linha->nota;
        $oAvaliacao->sConceito = $linha->conceito; // Campo que estava faltando!
        $oAvaliacao->iFalta = isset($linha->faltas) ? $linha->faltas : 0;
        $oAvaliacao->iAulasDadas = isset($linha->aulas_dadas) ? $linha->aulas_dadas : 0;
        $oAvaliacao->lMinimoAtingido = ($linha->minimo == 'S');
        $oAvaliacao->lAmparado = ($linha->amparo == 'S');
        $oAvaliacao->iSequencia = isset($linha->sequencia) ? $linha->sequencia : 0;
        $oAvaliacao->sPeriodo = isset($linha->periodo) ? trim($linha->periodo) : ''; // Período de avaliação

        $oDadosAluno->oDisciplina->aAproveitamentos[] = $oAvaliacao;
    }

    /**
     * Autor: Uemerson Santana
     * Data: 06/02/2026
     * Demanda: 18250
     * Razao: Corrigido para buscar a Media Anual (MA) da tabela diarioresultado
     *        em vez de usar diariofinal.ed74_c_valoraprov. O campo ed74_c_valoraprov
     *        contem a Nota Final (NF) pos-avaliacao final, nao a Media Anual (MA).
     *        Isso causava dois problemas:
     *        1) A coluna MP exibia 5,4 (NF) ao inves de 4,8 (MA) quando o aluno
     *           tinha avaliacao final (ex: HADASSA - cod 117838).
     *        2) A coluna MP ficava vazia quando ed74_c_valoraprov era vazio,
     *           mesmo existindo MA calculada em diarioresultado (ex: ALICE - cod 121059,
     *           que tem apenas 3 bimestres).
     *        Agora busca MA de diarioresultado (via procresultado/resultado com
     *        abreviatura 'MA') e NF separadamente (abreviatura 'NF'), garantindo
     *        consistencia com o boletim.
     */
    // Busca resultado final e MA/NF de diarioresultado
    $iCodigoAluno = "(SELECT ed60_i_aluno FROM matricula WHERE ed60_i_codigo = {$parametros->iMatricula})";

    // Busca o codigo do diario para esta disciplina/aluno
    $sqlDiario = "SELECT d.ed95_i_codigo
                  FROM diario d
                  WHERE d.ed95_i_regencia = {$parametros->iRegencia}
                    AND d.ed95_i_aluno = {$iCodigoAluno}
                    AND d.ed95_i_serie = {$parametros->iEtapa}
                  LIMIT 1";
    $resultDiario = db_query($sqlDiario);

    if (pg_num_rows($resultDiario) > 0) {
        $iCodigoDiario = pg_fetch_result($resultDiario, 0, 0);

        // Busca MA (Media Anual) de diarioresultado
        $sqlMA = "SELECT dr.ed73_i_valornota as valor_ma
                  FROM diarioresultado dr
                  INNER JOIN procresultado pr ON pr.ed43_i_codigo = dr.ed73_i_procresultado
                  INNER JOIN resultado r ON r.ed42_i_codigo = pr.ed43_i_resultado
                  WHERE dr.ed73_i_diario = {$iCodigoDiario}
                    AND trim(r.ed42_c_abrev) = 'MA'
                  LIMIT 1";
        $resultMA = db_query($sqlMA);
        $valorMA = (pg_num_rows($resultMA) > 0) ? pg_fetch_result($resultMA, 0, 0) : '';

        // Demanda 18250: Anos iniciais (3º ao 5º) gravam a média em diarioresultado como MF (Média Final), não MA.
        // Se MA estiver vazio, usa MF como fallback para exibir a coluna MÉDIA na ficha individual.
        if ($valorMA === '' || $valorMA === null) {
            $sqlMF = "SELECT dr.ed73_i_valornota as valor_mf
                      FROM diarioresultado dr
                      INNER JOIN procresultado pr ON pr.ed43_i_codigo = dr.ed73_i_procresultado
                      INNER JOIN resultado r ON r.ed42_i_codigo = pr.ed43_i_resultado
                      WHERE dr.ed73_i_diario = {$iCodigoDiario}
                        AND trim(r.ed42_c_abrev) = 'MF'
                      LIMIT 1";
            $resultMF = db_query($sqlMF);
            $valorMA = (pg_num_rows($resultMF) > 0) ? pg_fetch_result($resultMF, 0, 0) : $valorMA;
        }

        // Busca NF (Nota Final) de diarioresultado
        $sqlNF = "SELECT dr.ed73_i_valornota as valor_nf
                  FROM diarioresultado dr
                  INNER JOIN procresultado pr ON pr.ed43_i_codigo = dr.ed73_i_procresultado
                  INNER JOIN resultado r ON r.ed42_i_codigo = pr.ed43_i_resultado
                  WHERE dr.ed73_i_diario = {$iCodigoDiario}
                    AND trim(r.ed42_c_abrev) = 'NF'
                  LIMIT 1";
        $resultNF = db_query($sqlNF);
        $valorNF = (pg_num_rows($resultNF) > 0) ? pg_fetch_result($resultNF, 0, 0) : '';

        // Busca resultado final (situacao) de diariofinal
        $sqlSituacao = "SELECT df.ed74_c_resultadofinal as situacao_final
                        FROM diariofinal df
                        WHERE df.ed74_i_diario = {$iCodigoDiario}
                        LIMIT 1";
        $resultSituacao = db_query($sqlSituacao);
        $situacaoFinal = (pg_num_rows($resultSituacao) > 0) ? pg_fetch_result($resultSituacao, 0, 0) : '';

        $oDadosAluno->oDisciplina->oResultadoFinal = new stdClass();
        $oDadosAluno->oDisciplina->oResultadoFinal->nValor = $valorMA;
        $oDadosAluno->oDisciplina->oResultadoFinal->nValorNF = $valorNF;
        $oDadosAluno->oDisciplina->oResultadoFinal->sResultadoFinal = $situacaoFinal;
    }
    $oRetorno->aAlunos[] = $oDadosAluno;

    return $oRetorno;
}

/**
 * Busca dados do aluno usando consultas SQL diretas
 * @param int $ed60_i_codigo Código da matrícula
 * @param int $ed57_i_codigo Código da turma
 * @param int $ed11_i_codigo Código da etapa
 * @return array Dados organizados por disciplina
 */
function buscarDadosAlunoIntegrado($ed60_i_codigo, $ed57_i_codigo, $ed11_i_codigo) {
    $dadosCompletos = array();

    try {
        // Busca regências da turma
        $sqlRegencias = "SELECT ed59_i_codigo as regencia
                         FROM regencia
                         WHERE ed59_i_turma = {$ed57_i_codigo}
                           AND ed59_i_serie = {$ed11_i_codigo}
                         ORDER BY ed59_i_ordenacao";

        $resultRegencias = db_query($sqlRegencias);

        while ($regencia = pg_fetch_object($resultRegencias)) {
            $parametros = new stdClass();
            $parametros->iMatricula = $ed60_i_codigo;
            $parametros->iEtapa = $ed11_i_codigo;
            $parametros->iRegencia = $regencia->regencia;

            $resposta = executarRPCLancamento($parametros);

            // Adiciona dados da disciplina
            if (isset($resposta->aAlunos) && count($resposta->aAlunos) > 0) {
                $dadosCompletos[] = adaptarDadosParaFicha($resposta->aAlunos[0]);
            }
        }
    } catch (Exception $e) {
        // Em caso de erro, retorna array vazio para não quebrar o relatório
        error_log("Erro ao buscar dados integrados: " . $e->getMessage());
        return array();
    }

    return $dadosCompletos;
}

/**
 * Adapta dados do RPC para formato esperado pela ficha
 * @param object $dadosRPC Dados vindos do RPC
 * @return array Dados adaptados
 */
function adaptarDadosParaFicha($dadosRPC) {
    if (!isset($dadosRPC->oDisciplina)) {
        return array();
    }

    $disciplina = $dadosRPC->oDisciplina;

    $disciplinaAdaptada = array(
        'disciplina' => urldecode($disciplina->sDescricao),
        'ed59_i_regencia' => $disciplina->iCodigoRegencia,
        'avaliacoes' => array()
    );

    if (isset($disciplina->aAproveitamentos)) {
        foreach ($disciplina->aAproveitamentos as $avaliacao) {
            $disciplinaAdaptada['avaliacoes'][] = array(
                'valor_nota' => isset($avaliacao->nNota) ? $avaliacao->nNota : '',
                'valor_conceito' => isset($avaliacao->sConceito) ? $avaliacao->sConceito : '',
                'numero_faltas' => isset($avaliacao->iFalta) ? $avaliacao->iFalta : 0,
                'aulas_dadas' => isset($avaliacao->iAulasDadas) ? $avaliacao->iAulasDadas : 0,
                'minimo' => $avaliacao->lMinimoAtingido ? 'S' : 'N',
                'amparo' => $avaliacao->lAmparado ? 'S' : 'N',
                'sequencia' => isset($avaliacao->iSequencia) ? $avaliacao->iSequencia : 0,
                'periodo' => isset($avaliacao->sPeriodo) ? trim($avaliacao->sPeriodo) : '' // Período de avaliação
            );
        }
    }

    // Adiciona resultado final
    /**
     * Autor: Uemerson Santana
     * Data: 06/02/2026
     * Demanda: 18250
     * Razao: Adicionado nValorNF (Nota Final) separado de nValor (Media Anual)
     *        para que a coluna MP exiba a MA e a coluna MF exiba a NF.
     */
    if (isset($disciplina->oResultadoFinal)) {
        $disciplinaAdaptada['resultado_final'] = array(
            'nValor' => $disciplina->oResultadoFinal->nValor,
            'nValorNF' => isset($disciplina->oResultadoFinal->nValorNF) ? $disciplina->oResultadoFinal->nValorNF : $disciplina->oResultadoFinal->nValor,
            'sResultadoFinal' => $disciplina->oResultadoFinal->sResultadoFinal
        );
    }
    return $disciplinaAdaptada;
}

// ===================================================================================================
// SEÇÃO 1: VARIÁVEIS GLOBAIS E INICIALIZAÇÃO
// ===================================================================================================
$aprovacao = true;
$imp2024 = false;
$aprovCons = false;

// ===================================================================================================
// SEÇÃO 2: FUNÇÕES AUXILIARES PARA FORMATAÇÃO E VALIDAÇÃO
// ===================================================================================================

/**
 * Formata nota para exibição no PDF
 * @param mixed $nota Nota a ser formatada
 * @return string Nota formatada
 *
 * Autor: Uemerson Santana
 * Data: 06/11/2025
 * Demanda: 17412
 * Razão: Corrigido para truncar em vez de arredondar (3,15?3,1, 5,75?5,7)
 */
function formatarNota($nota) {
    if ($nota === null || $nota === '' || $nota == 0) {
        return '';
    }

    // Se for conceito, retorna como está
    if (!is_numeric($nota)) {
        return $nota;
    }

    $nota = (float)$nota;
    // Trunca para 1 casa decimal em vez de arredondar
    $notaTruncada = floor($nota * 10) / 10;
    return number_format($notaTruncada, 1, ',', '');
}

/**
 * Ajusta nota adicionando .0 quando necessário
 * @param mixed $nota Nota a ser ajustada
 * @return string Nota ajustada
 */
function ajustaNota($nota) {
    if (strlen($nota) == 1) {
        $nota = $nota . ".0";
    } elseif ($nota == 10) {
        $nota = $nota . ".0";
    }
    return $nota;
}

/**
 * Verifica se é transferido
 * @param string $situacao Situação do aluno
 * @return bool
 */
function isTransferido($situacao) {
    return ($situacao == 'TRANSFERIDO FORA' || $situacao == 'TRANSFERIDO REDE');
}

/**
 * Desenha célula de nota no PDF
 * @param object $pdf Objeto PDF
 * @param mixed $valor Valor a ser exibido
 * @param bool $destacar Se deve destacar nota baixa
 * @param float $valorMinimo Valor mínimo para aprovação
 * @param int $quebrarLinha Se deve quebrar linha (0=não, 1=sim)
 * @param string $borda Bordas da célula (padrão "LB")
 */
/**
 * Autor: Uemerson Santana
 * Data: 30/10/2025
 * Demanda: 17412
 * razao: Permitir configurar a largura da célula para alinhar as colunas do corpo
 *        com o cabeçalho (12 no 3º-5º ano), corrigindo o desalinhamento sem
 *        impactar os demais segmentos que utilizam larguras diferentes.
 */
function desenharCelulaNota($pdf, $valor, $destacar = false, $valorMinimo = 5, $quebrarLinha = 0, $borda = "LB", $largura = 10) {
    $valorNumerico = is_numeric($valor) ? (float)$valor : 0;

    if ($destacar && $valorNumerico < $valorMinimo) {
        $pdf->setfont('arial', 'b', 7);
    } else {
        $pdf->setfont('arial', '', 7);
    }

    $valorFormatado = is_numeric($valor) ? str_replace(".", ",", $valor) : $valor;
    $pdf->cell($largura, 4, $valorFormatado, $borda, $quebrarLinha, "C", 0);
    $pdf->setfont('arial', 'b', 7);
}

/**
 * Verifica se aluno tem necessidades especiais
 * @param int $iCodigoAluno Código do aluno
 * @return bool
 */
function isAlunoComNecessidadesEspeciais($iCodigoAluno) {
    $sSql = "SELECT ed214_i_aluno FROM alunonecessidade WHERE ed214_i_aluno = {$iCodigoAluno}";
    $rsResult = db_query($sSql);
    return (pg_num_rows($rsResult) > 0);
}

/**
 * Retorna texto da nacionalidade
 * @param int $ed47_i_nacion Código da nacionalidade
 * @return string
 */
function retornaNacionalidade($ed47_i_nacion) {
    $nacionalidades = array(
        1 => "Brasileira",
        2 => "Brasileira no exterior ou naturalizado",
        3 => "Estrangeira"
    );
    return isset($nacionalidades[$ed47_i_nacion]) ? $nacionalidades[$ed47_i_nacion] : "Estrangeira";
}

/**
 * Retorna nome da naturalidade
 * @param int $ed47_i_censomunicnat Código do município
 * @return string
 */
function retornaNaturalidade($ed47_i_censomunicnat) {
    $sql = db_query("SELECT ed261_c_nome FROM censomunic WHERE ed261_i_codigo = {$ed47_i_censomunicnat}");
    $resultado = pg_fetch_all($sql);
    $nome = strtolower($resultado[0]["ed261_c_nome"]);
    return ucwords($nome);
}

// ===================================================================================================
// SEÇÃO 5: FUNÇÕES DE BUSCA DE DADOS
// ===================================================================================================

/**
 * Busca código da regência
 * @param int $ed57_i_codigo Código da turma
 * @return mixed
 */
function buscaCodigoRegencia($ed57_i_codigo) {
    $sql = db_query("SELECT ed59_i_codigo, ed59_i_turma, ed59_i_disciplina
                     FROM regencia
                     WHERE ed59_i_turma = {$ed57_i_codigo}");
    $resultado = pg_fetch_all($sql);
    return $resultado[0]["ed59_i_codigo"];
}

/**
 * Busca todos os códigos de regência
 * @param int $ed57_i_codigo Código da turma
 * @return array
 */
function buscaCodigoRegencia2($ed57_i_codigo) {
    $sql = db_query("SELECT ed59_i_codigo, ed59_i_turma, ed59_i_disciplina
                     FROM regencia
                     WHERE ed59_i_turma = {$ed57_i_codigo}");
    return pg_fetch_all($sql);
}

/**
 * Retorna código do diário
 * @param int $ed60_i_codigo
 * @param int $ed60_i_aluno
 * @param int $ed11_i_codigo
 * @param int $ed60_i_turma
 * @return mixed
 */
function retornaCodDiario($ed60_i_codigo, $ed60_i_aluno, $ed11_i_codigo, $ed60_i_turma) {
    $sql = "SELECT diario.*, ed59_i_codigo
            FROM diario
            INNER JOIN aluno ON ed47_i_codigo = ed95_i_aluno
            INNER JOIN matricula ON ed60_i_aluno = ed47_i_codigo
            INNER JOIN matriculaserie ON ed60_i_codigo = ed221_i_matricula
            INNER JOIN regencia ON ed59_i_codigo = ed95_i_regencia AND ed59_i_serie = ed221_i_serie
            WHERE ed60_i_codigo = {$ed60_i_codigo}
              AND ed95_i_aluno = {$ed60_i_aluno}
              AND ed95_i_regencia = ed59_i_codigo
              AND ed95_i_serie = {$ed11_i_codigo}
              AND ed59_i_turma = {$ed60_i_turma}
            ORDER BY ed59_i_ordenacao";

    $result = db_query($sql);
    $resultado = pg_fetch_all($result);
    return $resultado[0]["ed95_i_codigo"];
}



/**
 * Retorna nome da disciplina
 * @param int $ed59_i_disciplina Código da disciplina
 * @return string
 */
function retornaNomeDisciplina($ed59_i_disciplina) {
    $sql = db_query("SELECT ed232_c_descr
                     FROM caddisciplina
                     INNER JOIN disciplina ON ed232_i_codigo = ed12_i_caddisciplina
                     WHERE ed12_i_codigo = {$ed59_i_disciplina}");
    $resultado = pg_fetch_all($sql);
    return $resultado[0]["ed232_c_descr"];
}

// ===================================================================================================
// SEÇÃO 6: FUNÇÕES DE RESULTADO FINAL
// ===================================================================================================

/**
 * Busca data de transferência do aluno
 * @param int $aluno Código da matrícula
 * @return string Data de saída
 */
function transferenciadata($aluno) {
    $sql = "SELECT ed60_d_datasaida
            FROM matricula
            WHERE ed60_d_datasaida BETWEEN '2024-01-01' AND '2024-12-31'
              AND ed60_i_codigo = {$aluno}";

    $rstransf = db_query($sql);
    $otransf = db_utils::fieldsmemory($rstransf, 0);
    return $otransf->ed60_d_datasaida;
}

/**
 * Busca data do fim do ano letivo
 * @param int $escola
 * @param int $etapa
 * @param int $turma
 * @param int $aluno
 * @return string Data do resultado final
 */
function fimanoletivo($escola, $etapa, $turma, $aluno) {
    $sql = "SELECT DISTINCT ON (ed52_d_resultfinal) ed52_d_resultfinal
            FROM turma
            INNER JOIN escola ON escola.ed18_i_codigo = turma.ed57_i_escola
            INNER JOIN calendario ON calendario.ed52_i_codigo = turma.ed57_i_calendario
            INNER JOIN base ON base.ed31_i_codigo = turma.ed57_i_base
            INNER JOIN cursoedu ON cursoedu.ed29_i_codigo = base.ed31_i_curso
            INNER JOIN ensino ON ensino.ed10_i_codigo = cursoedu.ed29_i_ensino
            INNER JOIN turmaserieregimemat ON turmaserieregimemat.ed220_i_turma = turma.ed57_i_codigo
            INNER JOIN serieregimemat ON serieregimemat.ed223_i_codigo = turmaserieregimemat.ed220_i_serieregimemat
            INNER JOIN serie ON serie.ed11_i_codigo = serieregimemat.ed223_i_serie
            INNER JOIN matricula ON matricula.ed60_i_turma = turma.ed57_i_codigo
            INNER JOIN aluno ON aluno.ed47_i_codigo = matricula.ed60_i_aluno
            INNER JOIN regencia ON regencia.ed59_i_turma = turma.ed57_i_codigo
            INNER JOIN diario ON diario.ed95_i_escola = escola.ed18_i_codigo
                             AND diario.ed95_i_calendario = calendario.ed52_i_codigo
                             AND diario.ed95_i_aluno = aluno.ed47_i_codigo
                             AND diario.ed95_i_serie = serie.ed11_i_codigo
                             AND diario.ed95_i_regencia = regencia.ed59_i_codigo
            INNER JOIN diariofinal ON diariofinal.ed74_i_diario = diario.ed95_i_codigo
            WHERE escola.ed18_i_codigo = {$escola}
              AND ed11_i_codigo = {$etapa}
              AND ed57_i_codigo = {$turma}
              AND ed47_i_codigo = {$aluno}
              AND (ed74_c_resultadofinal <> '' OR ed74_c_resultadofinal <> ' ' OR ed74_c_resultadofinal IS NOT NULL)";

    $rsfimletivo = db_query($sql);
    if (pg_num_rows($rsfimletivo) > 0) {
        $resultado = pg_fetch_object($rsfimletivo);
        return $resultado->ed52_d_resultfinal;
    }
    return null;
}

/**
 * Código do diário para conselho
 * @param int $ed60_i_codigo
 * @param int $ed60_i_aluno
 * @param int $ed11_i_codigo
 * @param int $ed60_i_turma
 * @return resource
 */
function CodDiarioConselho($ed60_i_codigo, $ed60_i_aluno, $ed11_i_codigo, $ed60_i_turma) {
    $sql = "SELECT diario.ed95_i_codigo
            FROM diario
            INNER JOIN aluno ON ed47_i_codigo = ed95_i_aluno
            INNER JOIN matricula ON ed60_i_aluno = ed47_i_codigo
            INNER JOIN matriculaserie ON ed60_i_codigo = ed221_i_matricula
            INNER JOIN regencia ON ed59_i_codigo = ed95_i_regencia AND ed59_i_serie = ed221_i_serie
            WHERE ed60_i_codigo = {$ed60_i_codigo}
              AND ed95_i_aluno = {$ed60_i_aluno}
              AND ed95_i_regencia = ed59_i_codigo
              AND ed95_i_serie = {$ed11_i_codigo}
              AND ed59_i_turma = {$ed60_i_turma}
            ORDER BY ed59_i_ordenacao";

    $result = db_query($sql);
    return $result;
}

/**
 * Gera dados do resultado final adaptado
 * @param array $dadosMatricula
 * @param array $dadosRegencia
 * @param int $ano
 * @return array
 */
function gerarDadosResultadoFinalAdaptado($dadosMatricula, $dadosRegencia, $ano) {
    $aRetorno = array(
        'sSituacaoAluno' => $dadosMatricula['ed60_c_situacao'],
        'sSituacaoAbreviada' => '',
        'oDisciplina' => array(
            'sFrequenciaGlobal' => 'I',
            'lCaracterReprobatorio' => true,
            'lProgressaoParcial' => false,
            'oResultadoFinal' => array(
                'nValor' => '',
                'sResultadoFinal' => '',
                'iAprovadoPeloConselho' => 0
            )
        ),
        'lProgressaoParcialAnterior' => false
    );

    $oCodDiario = retornaCodDiario2($dadosMatricula['ed60_i_codigo'], $dadosMatricula['ed60_i_aluno'],
                                     $dadosMatricula['ed11_i_codigo'], $dadosMatricula['ed57_i_codigo']);

    if (!empty($oCodDiario)) {
        foreach ($oCodDiario as $linha) {
            $diarioCodigo = $linha['ed95_i_codigo'];
            $dadosDiario = dadosDiario2($diarioCodigo);

            $sqlResultado = "SELECT ed74_c_resultadofinal FROM diariofinal WHERE ed74_i_diario = {$diarioCodigo}";
            $resultResultado = db_query($sqlResultado);

            if (pg_num_rows($resultResultado) > 0) {
                $resultadoFinal = pg_fetch_result($resultResultado, 0, 0);
                $aRetorno['oDisciplina']['oResultadoFinal']['sResultadoFinal'] = $resultadoFinal;

                $sqlConselho = "SELECT ed253_aprovconselhotipo FROM aprovconselho WHERE ed253_i_diario = {$diarioCodigo}";
                $resultConselho = db_query($sqlConselho);

                if (pg_num_rows($resultConselho) > 0) {
                    $aRetorno['oDisciplina']['oResultadoFinal']['iAprovadoPeloConselho'] = pg_fetch_result($resultConselho, 0, 0);
                }

                if ($resultadoFinal == 'D') {
                    $aRetorno['oDisciplina']['lProgressaoParcial'] = true;
                }
            }
        }
    }

    $sqlProgressao = "SELECT COUNT(*) FROM progressaoparcialaluno
                      WHERE ed114_aluno = {$dadosMatricula['ed60_i_aluno']}
                        AND ed114_ano < {$ano}";
    $resultProgressao = db_query($sqlProgressao);

    if (pg_num_rows($resultProgressao) > 0 && pg_fetch_result($resultProgressao, 0, 0) > 0) {
        $aRetorno['lProgressaoParcialAnterior'] = true;
    }

    $sqlFrequencia = "SELECT ed59_c_freqglob FROM regencia WHERE ed59_i_codigo = {$dadosRegencia['ed59_i_codigo']}";
    $resultFrequencia = db_query($sqlFrequencia);

    if (pg_num_rows($resultFrequencia) > 0) {
        $aRetorno['oDisciplina']['sFrequenciaGlobal'] = pg_fetch_result($resultFrequencia, 0, 0);
    }

    return $aRetorno;
}



/**
 * Retorna resultado final RPC
 * @param int $iEtapa
 * @param int $iTurma
 * @param int $iMatricula
 * @param int $iAnoCalendario
 * @return string
 */
function resultado_final_rpc($iEtapa, $iTurma, $iMatricula, $iAnoCalendario) {
    $oTurma = new Turma($iTurma);
    $oEtapa = EtapaRepository::getEtapaByCodigo($iEtapa);
    $aAlunosMatriculados = $oTurma->getAlunosMatriculadosNaTurmaPorSerie($oEtapa);

    $iCodigoEnsino = $oTurma->getBaseCurricular()->getCurso()->getEnsino()->getCodigo();
    $aTermosAprovado = DBEducacaoTermo::getTermoEncerramento($iCodigoEnsino, 'A', $iAnoCalendario);
    $sLabelAprovado = count($aTermosAprovado) > 0 ? $aTermosAprovado[0]->sDescricao : '';
    $lPermiteAprovacaoParcial = EncerramentoAvaliacao::permiteAprovacaoParcial($oTurma, $oEtapa);

    foreach ($aAlunosMatriculados as $mat) {
        if ($mat->getCodigo() !== $iMatricula) {
            continue;
        }

        db_inicio_transacao();

        if ($mat->getSituacao() !== 'MATRICULADO') {
            db_fim_transacao(false);
            return $mat->getSituacao();
        }

        $diarioService = $mat->getDiarioDeClasse()->getDiarioAlunoService();
        $areaProcedimento = $mat->getDiarioDeClasse()->getAreaProcedimento();
        $resultadoFinal = $mat->getDiarioDeClasse()->getResultadoFinal();

        if (!is_null($areaProcedimento)) {
            $resultadoFinal = $diarioService->getDiarioAluno()->getResultadoFinal()->getResultadoFinal();
        }

        if (is_null($areaProcedimento) && $lPermiteAprovacaoParcial && $resultadoFinal === 'A' &&
            EncerramentoAvaliacao::validaDiarioAlunoEja($mat, $oEtapa) === 'P') {
            $resultadoFinal = 'P';
        }

        if (!empty($resultadoFinal)) {
            $aTermos = DBEducacaoTermo::getTermoEncerramento($iCodigoEnsino, $resultadoFinal, $iAnoCalendario);
            if (count($aTermos) > 0) {
                $resultadoFinal = $aTermos[0]->sDescricao;
            }
        }

        $lAprovadoComProgressaoParcial = false;
        if (is_null($areaProcedimento)) {
            $oDiarioClasse = $mat->getDiarioDeClasse();
            $lAprovadoComProgressaoParcial = $oDiarioClasse->aprovadoComProgressaoParcial();
        }

        if ($lAprovadoComProgressaoParcial) {
            $resultadoFinal = " {$sLabelAprovado} (Progressão Parcial / Dependência)";
        }

        $lTemRecuperacao = $mat->getDiarioDeClasse()->temRecuperacao();
        if ($lTemRecuperacao) {
            $resultadoFinal = 'EM RECUPERAÇÃO';
        }

        db_fim_transacao(false);
        return $resultadoFinal;
    }

    return '';
}

/**
 * Busca resultado final
 * @param int $escola
 * @param int $etapa
 * @param int $turma
 * @param int $aluno
 * @return int
 */
/**
 * Calcula percentual de frequência
 * @param int $calendar
 * @param int|null $iTurma Código da turma (opcional, para usar o turno correto do aluno)
 * @return int
 *
 * @author Uemerson Santana
 * @Demanda: 17412
 * @date 10/11/2025
 * @razao: Corrigido para priorizar valores fixos por segmento (lógica de negócio)
 *        sobre o valor do banco (ed52_i_diasletivos), evitando frequência negativa.
 *        Para ANOS FINAIS deve retornar 1000, não os dias letivos do calendário.
 *
 * Autor: Uemerson Santana
 * Data: 23/01/2025
 * Demanda: 18059
 * Razao: Ajustado para aceitar opcionalmente o código da turma e,
 *        quando informado, usar o turno da turma específica do aluno.
 *        Isso evita pegar o turno de uma turma INTEGRAL quando o aluno
 *        está em turma MANHÃ/TARDE no mesmo calendário.
 */
function percfrequencia($calendar, $iTurma = null) {
    // PRIMEIRO: Buscar nome do calendário para verificar se tem valor fixo
    $sql = db_query("SELECT ed52_c_descr,
                            ed15_c_nome,
                            ed52_i_diasletivos,
                            ed57_i_codigo
                     FROM calendario
                     LEFT JOIN turma ON ed57_i_calendario = ed52_i_codigo
                     LEFT JOIN turno ON ed15_i_codigo = ed57_i_turno
                     WHERE ed52_i_codigo = {$calendar}");
    $resultado = pg_fetch_all($sql);

    if (empty($resultado)) {
        return 200; // Valor padrão
    }

    // Autor: Uemerson Santana
    // Data: 23/01/2025
    // Demanda: 18059
    // Razao: Quando a função receber a turma, priorizar a linha correspondente
    //        na lista de turmas do calendário, garantindo que o turno usado
    //        para o cálculo reflita a turma real do aluno.
    $linhaCalendario = $resultado[0];
    if (!is_null($iTurma)) {
        foreach ($resultado as $linha) {
            if (isset($linha["ed57_i_codigo"]) && (int)$linha["ed57_i_codigo"] === (int)$iTurma) {
                $linhaCalendario = $linha;
                break;
            }
        }
    }

    $nome = trim($linhaCalendario["ed52_c_descr"]);
    $turno = isset($linhaCalendario["ed15_c_nome"]) ? substr($linhaCalendario["ed15_c_nome"], 0, 5) : '';
    $turno_completo = isset($linhaCalendario["ed15_c_nome"]) ? trim($linhaCalendario["ed15_c_nome"]) : '';
    $diasLetivos = isset($linhaCalendario["ed52_i_diasletivos"]) ? (int)$linhaCalendario["ed52_i_diasletivos"] : 0;

    // Valores fixos por segmento (prioridade sobre valor do banco)
    $aulasDadas = array(
        'ED. INFANTIL' => 200,
        'EDUCAÇÃO INFANTIL' => 200,
        'ANOS INICIAIS' => 200,
        'EN FUN ANOS INICIAIS' => 200,
        'ANOS FINAIS' => 1000,
        'EN FUN ANOS FINAIS' => 1000,
        'EJA ANOS INICIAIS' => 170,
        'EJA INICIAIS' => 170,
        'EJA ANOS FINAIS' => 1000,
        'EJA FINAIS' => 1000
    );

    // Inicializar variável para evitar erro
    $auladadas = null;

    // Verificar se o calendário corresponde a algum segmento com valor fixo
    foreach ($aulasDadas as $key => $value) {
        if (substr($nome, 0, strlen($key)) == $key) {
            $auladadas = $value;
            break;
        }
    }

    // Autor: Uemerson Santana | Data: 19/01/2026 | Demanda: 18059
    // Razão: Para Anos Finais, verificar se o turno é INTEGRAL para aplicar 1522 horas/aula
    //        ao invés de 1000 horas/aula fixas. Turno INTEGRAL tem carga horária maior.
    if ($auladadas === 1000 && (substr($nome, 0, 11) == 'ANOS FINAIS' || substr($nome, 0, 18) == 'EN FUN ANOS FINAIS')) {
        if (strtoupper($turno_completo) == 'INTEGRAL') {
            $auladadas = 1522;
        }
    }

    // Tratamento específico para EJA FINAIS baseado no turno
    if ($turno != 'NOITE' && (substr($nome, 0, 15) == 'EJA ANOS FINAIS' || substr($nome, 0, 10) == 'EJA FINAIS')) {
        $auladadas = 1200;
    }

    if ($turno == 'NOITE' && (substr($nome, 0, 15) == 'EJA ANOS FINAIS' || substr($nome, 0, 10) == 'EJA FINAIS')) {
        $auladadas = 1000;
    }

    // Fallback adicional para EJA se não encontrou correspondência específica
    if ($auladadas === null && strpos($nome, 'EJA') !== false) {
        $auladadas = ($turno == 'NOITE') ? 1000 : 1200;
    }

    // Se encontrou valor fixo, retornar ele (prioridade sobre banco)
    if ($auladadas !== null) {
        return $auladadas;
    }

    // FALLBACK: Se não encontrou valor fixo, usar valor do banco (dias letivos)
    if ($diasLetivos > 0) {
        return $diasLetivos;
    }

    // Último fallback: valor padrão
    return 200;
}

/**
 * Busca aulas dadas por trimestre de uma regência (similar ao old)
 * @param int $codregencia Código da regência
 * @return array Array com aulas dadas por trimestre
 */
function dadosAulas($codregencia) {
    $sql = db_query("SELECT
                   ed78_i_codigo,
                   ed78_i_regencia,
                   ed78_i_procavaliacao,
                   ed78_i_aulasdadas
                   FROM regenciaperiodo
                   INNER JOIN procavaliacao ON procavaliacao.ed41_i_codigo = regenciaperiodo.ed78_i_procavaliacao
                   INNER JOIN regencia ON regencia.ed59_i_codigo = regenciaperiodo.ed78_i_regencia
                   INNER JOIN periodoavaliacao ON periodoavaliacao.ed09_i_codigo = procavaliacao.ed41_i_periodoavaliacao
                   INNER JOIN formaavaliacao ON formaavaliacao.ed37_i_codigo = procavaliacao.ed41_i_formaavaliacao
                   INNER JOIN procedimento ON procedimento.ed40_i_codigo = procavaliacao.ed41_i_procedimento
                   INNER JOIN disciplina ON disciplina.ed12_i_codigo = regencia.ed59_i_disciplina
                   INNER JOIN caddisciplina ON ed232_i_codigo = ed12_i_caddisciplina
                   INNER JOIN turma ON turma.ed57_i_codigo = regencia.ed59_i_turma
                   WHERE ed78_i_regencia = {$codregencia}
                     AND ed09_c_somach = 'S'
                   ORDER BY ed78_i_procavaliacao");

    $resultado = pg_fetch_all($sql);
    return $resultado;
}

// ===================================================================================================
// SEÇÃO 7: FUNÇÕES AUXILIARES DO PDF
// ===================================================================================================

/**
 * Desenha cabeçalho da grade
 * @param object $pdf
 * @param string $tipoEnsino
 * @param array $dadosAluno
 */
function desenharCabecalhoGrade($pdf, $tipoEnsino, $dadosAluno) {
    $pdf->cell(191, 4, $tipoEnsino, "LTR", 1, "C", 0);
    $pdf->cell(191, 4, "Etapa: {$dadosAluno['etapa']}       Ano Letivo: {$dadosAluno['ano']}       Nome da Turma: {$dadosAluno['turma']}", "LRB", 1, "C", 0);
    $pdf->cell(191, 4, "", "LR", 1, "C", 0);
}



/**
 * Calcula total de faltas usando método da ATA para consistência
 * Copiado de edu2_ataresultadofinal002.php para garantir mesmo cálculo
 *
 * @author Uemerson Santana
 * @date 11/09/2025
 * @param int $aluno Código da matrícula
 * @param int $escola Código da escola
 * @param int $calendario Código do calendário
 * @return int Total de faltas
 */
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

/**
 * Autor: Uemerson Santana
 * Data: 09/12/2025
 * Demanda: 18031
 * Razao: Total de faltas apenas da turma atual da matrícula (evita somar turmas anteriores).
 *
 * @param int $aluno Código da matrícula
 * @param int $escola Código da escola
 * @param int $calendario Código do calendário
 * @return int Total de faltas da turma atual
 */
function faltasFinalTurmaAtual($aluno, $escola, $calendario) {

    $sSqlMatricula = "select ed60_i_aluno, ed60_i_turma from matricula where ed60_i_codigo = {$aluno}";
    $rsMatricula = pg_query($sSqlMatricula);
    if (pg_num_rows($rsMatricula) == 0) {
        return 0;
    }

    $oMatricula = db_utils::fieldsMemory($rsMatricula, 0);

    $sSqlFaltas = "select coalesce(sum(da.ed72_i_numfaltas), 0) as numero_faltas
                   from diarioavaliacao da
                   inner join diario d on d.ed95_i_codigo = da.ed72_i_diario
                   inner join regencia r on r.ed59_i_codigo = d.ed95_i_regencia
                   inner join procavaliacao p on p.ed41_i_codigo = da.ed72_i_procavaliacao
                   left join pareceraval pa on pa.ed93_i_diarioavaliacao = da.ed72_i_codigo
                   left join abonofalta af on af.ed80_i_diarioavaliacao = da.ed72_i_codigo
                   where d.ed95_i_aluno = {$oMatricula->ed60_i_aluno}
                     and d.ed95_i_escola = {$escola}
                     and d.ed95_i_calendario = {$calendario}
                     and r.ed59_i_turma = {$oMatricula->ed60_i_turma}";

    $rsFaltas = pg_query($sSqlFaltas);
    if (pg_num_rows($rsFaltas) == 0) {
        return 0;
    }

    $resultado = pg_fetch_all($rsFaltas);
    return $resultado[0]["numero_faltas"];
}

// ===================================================================================================
// SEÇÃO 8: FUNÇÕES ESPECÍFICAS PARA DEPENDÊNCIAS
// ===================================================================================================

/**
 * Valida aprovação de dependências de anos anteriores
 * @param int $aluno
 * @return bool
 */
function depanoant($aluno) {
    $sqlDisc = depanoantMat($aluno);
    $result = db_query($sqlDisc);
    $aprovado = true;

    for ($y = 0; $y < pg_num_rows($result); $y++) {
        $oDados2 = db_utils::fieldsMemory($result, $y);
                $sql1 = "SELECT DISTINCT ON (ed999_sequencial)
                        ed232_c_descr as disciplina,
                        ed114_ano as ano,
                        ed09_c_descr as bimestre,
                        ed09_c_descr as media,
                        ed999_nota as nota,
                        ed998_nota as notareal,
                        ed999_faltas as faltas
                 FROM plugins.diarioprogressaoavaliacao
                 INNER JOIN plugins.diarioprogressao ON ed993_sequencial = ed999_diarioprogressao
                 INNER JOIN progressaoparcialalunoturmaregencia ON ed115_sequencial = ed993_progressaoparcialalunoturmaregencia
                 INNER JOIN progressaoparcialalunomatricula ON ed150_sequencial = ed115_progressaoparcialalunomatricula
                 INNER JOIN progressaoparcialaluno ON ed114_sequencial = ed150_progressaoparcialaluno
                 INNER JOIN disciplina ON ed12_i_codigo = ed114_disciplina
                 INNER JOIN caddisciplina ON ed232_i_codigo = ed12_i_caddisciplina
                 INNER JOIN procavaliacao ON ed41_i_codigo = ed999_procavaliacao
                 INNER JOIN periodoavaliacao ON ed09_i_codigo = ed41_i_periodoavaliacao
                 INNER JOIN plugins.diarioprogressaoresultado ON ed998_diarioprogressao = ed993_sequencial
                 INNER JOIN procresultado ON ed43_i_codigo = ed998_procresultado
                 INNER JOIN matricula ON ed60_i_aluno = ed114_aluno
                 WHERE ed60_i_codigo = {$aluno}
                   AND ed232_i_codigo = {$oDados2->ed232_i_codigo}
                 ORDER BY ed999_sequencial";

        $sql = db_query($sql1);
        if (pg_num_rows($sql) > 0) {
            $notas = array();
            for ($x = 0; $x < pg_num_rows($sql); $x += 6) {
                $nota1 = db_utils::fieldsMemory($sql, $x)->nota;
                $nota2 = db_utils::fieldsMemory($sql, $x+1)->nota;
                $nota5 = db_utils::fieldsMemory($sql, $x+2)->nota; // Recuperação semestral
                $nota3 = db_utils::fieldsMemory($sql, $x+3)->nota;
                $nota4 = db_utils::fieldsMemory($sql, $x+4)->nota;
                $nota6 = db_utils::fieldsMemory($sql, $x+5)->nota; // Recuperação final

                // Aplica lógica de substituição de notas
                if ($nota1 < $nota2 && $nota1 < $nota5) {
                    $nota1 = $nota5;
                }
                if ($nota2 < $nota1 && $nota2 < $nota5) {
                    $nota2 = $nota5;
                }

                // Calcula média simples
                $notas = array($nota1, $nota2, $nota3, $nota4);
                $notasValidas = array_filter($notas, function($n) { return $n !== null && $n !== '' && is_numeric($n); });
                $media = count($notasValidas) > 0 ? array_sum($notasValidas) / count($notasValidas) : 0;

                if ($nota6 > 0 && $nota6 > $media) {
                    $mediaf = ($media + $nota6) / 2;
                } else {
                    $mediaf = $media;
                }

                if ($mediaf < 5.0) {
                    return false;
                }
            }
        }
    }

    return $aprovado;
}

/**
 * Retorna SQL para buscar disciplinas com dependência
 * @param int $aluno
 * @return string
 */
function depanoantMat($aluno) {
    return "SELECT DISTINCT ON (ed232_i_codigo) ed232_i_codigo
            FROM plugins.diarioprogressaoavaliacao
            INNER JOIN plugins.diarioprogressao ON ed993_sequencial = ed999_diarioprogressao
            INNER JOIN progressaoparcialalunoturmaregencia ON ed115_sequencial = ed993_progressaoparcialalunoturmaregencia
            INNER JOIN progressaoparcialalunomatricula ON ed150_sequencial = ed115_progressaoparcialalunomatricula
            INNER JOIN progressaoparcialaluno ON ed114_sequencial = ed150_progressaoparcialaluno
            INNER JOIN disciplina ON ed12_i_codigo = ed114_disciplina
            INNER JOIN caddisciplina ON ed232_i_codigo = ed12_i_caddisciplina
            INNER JOIN procavaliacao ON ed41_i_codigo = ed999_procavaliacao
            INNER JOIN periodoavaliacao ON ed09_i_codigo = ed41_i_periodoavaliacao
            INNER JOIN plugins.diarioprogressaoresultado ON ed998_diarioprogressao = ed993_sequencial
            INNER JOIN procresultado ON ed43_i_codigo = ed998_procresultado
            INNER JOIN matricula ON ed60_i_aluno = ed114_aluno
            WHERE ed60_i_codigo = {$aluno}";
}

// ===================================================================================================
// SEÇÃO 9: CÓDIGO PRINCIPAL - INÍCIO DA EXECUÇÃO
// ===================================================================================================

// Inicialização de variáveis e objetos
$resultedu = eduparametros(db_getsession("DB_coddepto"));
$permitenotaembranco = VerParametroNota(db_getsession("DB_coddepto"));
$escola = db_getsession("DB_coddepto");
$oGet = db_utils::postMemory($_GET);
$sObs = isset($sObs) ? $sObs : '';

// Extrai parâmetros da URL
$alunos = isset($oGet->alunos) ? $oGet->alunos : '';
$calendario = isset($oGet->calendario) ? $oGet->calendario : '';

// Instanciação de classes
$clmatricula = new cl_matricula;
$claluno = new cl_aluno;
$clturma = new cl_turma;
$clEscola = new cl_escola();
$cldiarioavaliacao = new cl_diarioavaliacao;
$clregenteconselho = new cl_regenteconselho;
$clrotulo = new rotulocampo;
$oDaoEscolaDiretor = new cl_escoladiretor();
$oDaoTipoSanguineo = new cl_tiposanguineo();

// Configuração de rótulos
$claluno->rotulo->label();
$clrotulo->label("ed76_i_escola");
$clrotulo->label("ed76_d_data");

// Busca dados da escola
$sSqlDadosEscola = $clEscola->sql_query("", "ed261_c_nome as mun_escola", "", "ed18_i_codigo = {$escola}");
$rsDadosEscola = db_query($sSqlDadosEscola);
$oDadosEscola = db_utils::fieldsMemory($rsDadosEscola, 0);
$mun_escola = $oDadosEscola->mun_escola;

// Busca dados do diretor
$sCamposDiretor = " 'DIRETOR' as funcao, ";
$sCamposDiretor .= "CASE WHEN ed20_i_tiposervidor = 1 THEN cgmrh.z01_nome ELSE cgmcgm.z01_nome END as nome,";
$sCamposDiretor .= " ed83_c_descr||' nº: '||ed05_c_numero::varchar as descricao,'D' as tipo";
$sWhereDiretor = " ed254_i_escola = {$escola} AND ed254_c_tipo = 'A' AND ed01_i_funcaoadmin = 2 LIMIT 1";
$sSqlDiretor = $oDaoEscolaDiretor->sql_query_resultadofinal("", $sCamposDiretor, "", $sWhereDiretor);
$rsDiretor = $oDaoEscolaDiretor->sql_record($sSqlDiretor);
$iLinhasDiretor = $oDaoEscolaDiretor->numrows;

if ($iLinhasDiretor > 0) {
    db_fieldsmemory($result, 0);
    $nome = trim(db_utils::fieldsmemory($rsDiretor, 0)->nome);
} else {
    $nome = "";
}

// Campos para busca de matrícula
$camp = " ed60_d_datasaida as datasaida, ";
$camp .= "CASE ";
$camp .= "  WHEN ed60_c_situacao = 'TRANSFERIDO REDE' THEN ";
$camp .= "    (SELECT escoladestino.ed18_c_nome FROM transfescolarede ";
$camp .= "     INNER JOIN atestvaga ON atestvaga.ed102_i_codigo = transfescolarede.ed103_i_atestvaga ";
$camp .= "     INNER JOIN escola as escoladestino ON escoladestino.ed18_i_codigo = atestvaga.ed102_i_escola ";
$camp .= "     WHERE ed103_i_matricula = ed60_i_codigo ORDER BY ed103_d_data DESC LIMIT 1) ";
$camp .= "  WHEN ed60_c_situacao = 'TRANSFERIDO FORA' THEN ";
$camp .= "    (SELECT escolaproc1.ed82_c_nome FROM transfescolafora ";
$camp .= "     INNER JOIN escolaproc as escolaproc1 ON escolaproc1.ed82_i_codigo = transfescolafora.ed104_i_escoladestino ";
$camp .= "     WHERE ed104_i_matricula = ed60_i_codigo ORDER BY ed104_d_data DESC LIMIT 1) ";
$camp .= "  ELSE null ";
$camp .= "END as destinosaida, ";
$camp .= "matricula.*, ";
$camp .= "turma.ed57_c_descr, ";
$camp .= "turma.ed57_i_codigo, ";
$camp .= "turmaserieregimemat.ed220_i_procedimento, ";
$camp .= "turma.ed57_c_medfreq, ";
$camp .= "calendario.ed52_c_descr, ";
$camp .= "calendario.ed52_i_ano, ";
$camp .= "CASE WHEN turma.ed57_i_tipoturma = 2 THEN ";
$camp .= "  fc_nomeetapaturma(ed60_i_turma) ELSE ";
$camp .= "  serie.ed11_c_descr ";
$camp .= "END as ed11_c_descr, ";
$camp .= "serie.ed11_i_codigo, ";
$camp .= "escola.ed18_c_nome, ";
$camp .= "turno.ed15_c_nome, ";
$camp .= "aluno.ed47_v_nome, ";
$camp .= "alunoprimat.ed76_i_codigo, ";
$camp .= "alunoprimat.ed76_i_escola, ";
$camp .= "alunoprimat.ed76_d_data, ";
$camp .= "alunoprimat.ed76_c_tipo, ";
$camp .= "CASE WHEN ed76_c_tipo = 'M' ";
$camp .= "  THEN escolaprimat.ed18_c_nome ELSE escolaproc.ed82_c_nome END as nomeescola, ";
$camp .= "aluno.* ";

// Busca matrículas ordenadas por nome do aluno
$sSqlMatricula = $clmatricula->sql_query("", $camp, "aluno.ed47_v_nome ASC", " ed60_i_codigo IN ({$alunos})");
$result1 = $clmatricula->sql_record($sSqlMatricula);

if ($clmatricula->numrows == 0) {
    db_redireciona("db_erros.php?fechar=true&db_erro=Nenhum registro encontrado.");
}

// Busca tipos sanguíneos
$sSqlTipoSanguineo = $oDaoTipoSanguineo->sql_query_file("", "*", "sd100_sequencial", "");
$rsTipoSanguineo = $oDaoTipoSanguineo->sql_record($sSqlTipoSanguineo);
$iLinhas = $oDaoTipoSanguineo->numrows;

$aTiposSanguineos = array();
if (isset($rsTipoSanguineo) && $iLinhas > 0) {
    for ($iContador = 0; $iContador < $iLinhas; $iContador++) {
        $oDados = db_utils::fieldsMemory($rsTipoSanguineo, $iContador);
        $aTiposSanguineos[$oDados->sd100_sequencial] = $oDados->sd100_tipo;
    }
}

// Criação do PDF
$pdf = new PDF();
$pdf->Open();
$pdf->AliasNbPages();
$pdf->setfillcolor(223);

// Loop principal - processa cada aluno
for ($ww = 0; $ww < $clmatricula->numrows; $ww++) {

    db_fieldsmemory($result1, $ww);
    $xnacionalidade = retornaNacionalidade($ed47_i_nacion);
    $xnaturalidade = retornaNaturalidade($ed47_i_censomunicnat);
    $xfotoaluno = trim($ed47_c_foto);

    /**
     * Autor: Uemerson Santana
     * Data: 02/03/2026
     * Demanda: 18250
     * Razao: Inicializar $ed60_situacao com $ed60_c_situacao no inicio do loop
     *        para garantir que alunos de Educacao Infantil (onde $tipoProcessamento
     *        fica vazio) tenham a situacao correta. Sem isso, $ed60_situacao ficava
     *        indefinido para EI, e a linha abaixo sobrescrevia $ed60_c_situacao,
     *        fazendo alunos EVADIDOS aparecerem como transferidos na Ficha Individual.
     */
    $ed60_situacao = $ed60_c_situacao;

    // Processa dados da grade conforme tipo de ensino
    $tipoProcessamento = '';

    // Processa dados da grade conforme tipo de ensino
    $tipoProcessamento = '';

    // Identifica tipo de ensino
    if (($ed52_c_descr == "EN FUN ANOS INICIAIS" || substr($ed52_c_descr, 0, 13) == "ANOS INICIAIS") &&
        $ed11_c_descr == "2º ANO") {
        $tipoProcessamento = 'INICIAIS_2ANO';
    } elseif (($ed52_c_descr == "EN FUN ANOS INICIAIS" || substr($ed52_c_descr, 0, 13) == "ANOS INICIAIS") &&
              ($ed11_c_descr == "3º ANO" || $ed11_c_descr == "4º ANO" || $ed11_c_descr == "5º ANO")) {
        $tipoProcessamento = 'INICIAIS_345ANO';
    } elseif ($ed52_c_descr == "EN FUN ANOS FINAIS" &&
              ($ed11_c_descr == "6º ANO" || $ed11_c_descr == "7º ANO" || $ed11_c_descr == "8º ANO" || $ed11_c_descr == "9º ANO")) {
        $tipoProcessamento = 'FINAIS_ENFUN';
    } elseif (substr($ed52_c_descr, 0, 11) == "ANOS FINAIS" &&
              ($ed11_c_descr == "6º ANO" || $ed11_c_descr == "7º ANO" || $ed11_c_descr == "8º ANO" || $ed11_c_descr == "9º ANO")) {
        $tipoProcessamento = 'FINAIS';
    } elseif ($ed52_c_descr == "EJA ANOS FINAIS" || substr($ed52_c_descr, 0, 10) == 'EJA FINAIS') {
        $tipoProcessamento = 'EJA_FINAIS';
    } elseif ($ed52_c_descr == "EJA ANOS INICIAIS" || substr($ed52_c_descr, 0, 12) == 'EJA INICIAIS') {
        $tipoProcessamento = 'EJA_INICIAIS';
    }

    // Processa dados da grade usando RPC integrado
    if ($tipoProcessamento != '') {
        $dadosgrade2 = buscarDadosAlunoIntegrado($ed60_i_codigo, $ed57_i_codigo, $ed11_i_codigo);
    }

    // Prepara data por extenso
    $data = date("Y-m-d", DB_getsession("DB_datausu"));
    $dia = date("d");
    $mes = date("m");
    $ano = date("Y");

    $mes_extenso = array(
        "01" => "janeiro", "02" => "fevereiro", "03" => "março", "04" => "abril",
        "05" => "maio", "06" => "junho", "07" => "julho", "08" => "agosto",
        "09" => "setembro", "10" => "outubro", "11" => "novembro", "12" => "dezembro"
    );

    $data_extenso = $mun_escola . ", " . $dia . " de " . $mes_extenso[$mes] . " de " . $ano . ".";
    $head1 = "FICHA INDIVIDUAL";
    $head2 = "{$ed47_i_codigo} - {$ed47_v_nome}";
    $pdf->addpage('P');

    // ===================================================================================================
    // SEÇÃO 10: DADOS PESSOAIS DO ALUNO
    // ===================================================================================================

    $pdf->setfont('arial', 'b', 7);
    $pdf->cell(191, 4, "DADOS PESSOAIS", "LBT", 1, "L", 1);
    $pdf->cell(3, 4, "", "L", 0, "C", 0);

    $pdf->setfont('arial', '', 7);
    $pdf->cell(35, 4, strip_tags($Led47_v_nome), 0, 0, "L", 0);

    $pdf->setfont('arial', 'b', 7);
    $pdf->cell(120, 4, $ed47_v_nome, 0, 1, "L", 0);
    $pdf->cell(3, 4, "", "L", 0, "C", 0);

    $pdf->setfont('arial', '', 7);
    $pdf->cell(35, 4, strip_tags($Led47_i_codigo), 0, 0, "L", 0);

    $pdf->setfont('arial', 'b', 7);
    $pdf->cell(20, 4, $ed47_i_codigo, 0, 0, "L", 0);

    $pdf->setfont('arial', '', 7);
    $pdf->cell(30, 4, strip_tags($Led47_c_codigoinep), 0, 0, "R", 0);

    $pdf->setfont('arial', 'b', 7);
    $pdf->cell(20, 4, $ed47_c_codigoinep, 0, 0, "L", 0);

    $pdf->setfont('arial', '', 7);
    $pdf->cell(25, 4, strip_tags($Led47_c_nis), 0, 0, "R", 0);

    // Foto do aluno
    if ($xfotoaluno) {
        db_query("begin");
        $lResultExport = pg_lo_export($ed47_o_oid, "tmp/".$xfotoaluno, $conn);
        db_query("end");
        $pdf->Image('tmp/' . $xfotoaluno, 170, 43, 25, 25);
    }

    $pdf->setfont('arial', 'b', 7);
    $pdf->cell(25, 4, $ed47_c_nis, 0, 1, "L", 0);
    $pdf->cell(3, 4, "", "L", 0, "C", 0);

    $pdf->setfont('arial', '', 7);
    $pdf->cell(35, 4, strip_tags($Led47_d_nasc), 0, 0, "L", 0);

    $pdf->setfont('arial', 'b', 7);
    $pdf->cell(20, 4, db_formatar($ed47_d_nasc, 'd'), 0, 0, "L", 0);

    $pdf->setfont('arial', '', 7);
    $pdf->cell(30, 4, strip_tags($Led47_v_sexo), 0, 0, "R", 0);

    $pdf->setfont('arial', 'b', 7);
    $pdf->cell(25.5, 4, $ed47_v_sexo == "M" ? "MASCULINO" : ($ed47_v_sexo == "F" ? "FEMININO" : "NÃO DECLARADO"), 0, 0, "L", 0);

    $pdf->setfont('arial', '', 7);
    $pdf->cell(25, 4, strip_tags($Led47_i_estciv), 0, 0, "R", 0);

    $estados_civis = array(
        1 => 'SOLTEIRO', 2 => 'CASADO', 3 => 'VIÚVO', 4 => 'DIVORCIADO'
    );
    $ed47_i_estciv = isset($estados_civis[$ed47_i_estciv]) ? $estados_civis[$ed47_i_estciv] : 'NÃO INFORMADO';

    $pdf->setfont('arial', 'b', 7);
    $pdf->cell(25, 4, $ed47_i_estciv, 0, 1, "L", 0);
    $pdf->cell(3, 4, "", "L", 0, "C", 0);

    $pdf->setfont('arial', '', 7);
    $pdf->cell(35, 4, strip_tags($Led47_tiposanguineo), 0, 0, "L", 0);

    $pdf->setfont('arial', 'b', 7);
    $pdf->cell(68, 4, $ed47_tiposanguineo == "" ? "NÃO INFORMADO" : $aTiposSanguineos[$ed47_tiposanguineo], 0, 0, "L", 0);

    $pdf->setfont('arial', '', 7);
    $pdf->cell(30, 4, "Raça/Cor:", 0, 0, "R", 0);

    $pdf->setfont('arial', 'b', 7);
    $pdf->cell(25, 4, $ed47_c_raca, 0, 1, "L", 0);
    $pdf->cell(3, 4, "", "L", 0, "C", 0);

    $pdf->setfont('arial', '', 7);
    $pdf->cell(35, 4, strip_tags($Led47_i_filiacao), 0, 0, "L", 0);

    $pdf->setfont('arial', 'b', 7);
    $pdf->cell(85, 4, $ed47_i_filiacao == "0" ? "NÃO DECLARADO / IGNORADO" : "PAI E/OU MÃE", 0, 0, "L", 0);

    $pdf->setfont('arial', '', 7);
    $pdf->cell(17, 4, "Nacionalidade: ", 0, 0, "L", 0);
    $pdf->setfont('arial', 'b', 7);
    $pdf->cell(20, 4, $xnacionalidade, 0, 1, "L", 0);
    $pdf->cell(3, 4, "", "L", 0, "C", 0);

    $pdf->setfont('arial', '', 7);
    $pdf->cell(35, 4, '', 0, 0, "L", 0);

    $pdf->setfont('arial', 'b', 7);
    $pdf->cell(85, 4, $ed47_v_mae, 0, 0, "L", 0);
    $pdf->setfont('arial', '', 7);
    $pdf->cell(17, 4, "Naturalidade: ", 0, 0, "L", 0);
    $pdf->setfont('arial', 'b', 7);
    $pdf->cell(20, 4, $xnaturalidade, 0, 1, "L", 0);
    $pdf->cell(3, 4, "", "L", 0, "C", 0);

    $pdf->setfont('arial', '', 7);
    $pdf->cell(35, 4, '', 0, 0, "L", 0);
    $pdf->setfont('arial', 'b', 7);
    $pdf->cell(85, 4, $ed47_v_pai, 0, 0, "L", 0);

    // UF de nascimento
    $res = db_query("SELECT ed261_i_censouf FROM censomunic WHERE ed261_i_codigo = {$ed47_i_censomunicnat}");
    $res = db_fieldsmemory($res, 0);
    $ufcenso = 'SELECT ed260_c_sigla FROM censouf WHERE ed260_i_codigo = ' . $ed261_i_censouf;
    $rsuf = db_query($ufcenso);
    db_fieldsmemory($rsuf, 0);

    $pdf->setfont('arial', '', 7);
    $pdf->cell(20, 4, 'UF/Nascimento: ', 0, 0, "L", 0);
    $pdf->setfont('arial', 'b', 7);
    $pdf->cell(15, 4, $ed260_c_sigla, 0, 1, "L", 0);
    $pdf->cell(3, 4, "", "L", 0, "C", 0);

    $pdf->setfont('arial', '', 7);
    $pdf->cell(35, 4, strip_tags($Led47_c_nomeresp), 0, 0, "L", 0);

    $pdf->setfont('arial', 'b', 7);
    $pdf->cell(121, 4, $ed47_c_nomeresp, 0, 1, "L", 0);
    $pdf->cell(3, 4, "", "L", 0, "C", 0);

    $pdf->setfont('arial', '', 7);
    $pdf->cell(35, 4, strip_tags($Led47_c_emailresp), 0, 0, "L", 0);

    $pdf->setfont('arial', 'b', 7);
    $pdf->cell(121, 4, $ed47_c_emailresp, 0, 1, "L", 0);

    $pdf->line(10, 75, 201, 75);

    $cont_geral = 0;
    $altini = $pdf->getY() + 10;

    $pdf->setY($altini);
    $dependencia = false;
    $quantdep = 0;
    $reprovado = false;

    // ===================================================================================================
    // SEÇÃO 11: RENDERIZAÇÃO DAS GRADES POR TIPO DE ENSINO
    // ===================================================================================================

    if ($tipoProcessamento == 'INICIAIS_2ANO') {
        // Anos Iniciais - 2º Ano
        $pdf->cell(191, 4, "Ensino Fundamental - 2º ano", "LTR", 1, "C", 0);
        $pdf->cell(191, 4, "Etapa: {$ed11_c_descr}       Ano Letivo: {$ed52_i_ano}       Nome da Turma: {$ed57_c_descr}", "LRB", 1, "C", 0);
        $pdf->cell(191, 4, "", "LR", 1, "C", 0);

        $pdf->cell(5, 4, "", "L", 0, "C", 0);
        $pdf->cell(100, 4, "Apuração dos Rendimentos", "LTBR", 1, "C", 0);
        $pdf->cell(5, 4, "", "L", 0, "C", 0);
        $pdf->cell(40, 4, "Componentes Curriculares", "LB", 0, "C", 0);
        $pdf->cell(15, 4, "1º TRI", "LB", 0, "C", 0);
        $pdf->cell(15, 4, "2º TRI", "LB", 0, "C", 0);
        $pdf->cell(15, 4, "3º TRI", "LBR", 0, "C", 0);
        $pdf->cell(15, 4, "MÉDIA", "LBR", 1, "C", 0);

        foreach ($dadosgrade2 as $disciplinaData) {
            $pdf->cell(5, 4, "", "L", 0, "C", 0);
            $pdf->cell(40, 4, $disciplinaData["disciplina"], "LB", 0, "L", 0);

            if (isAlunoComNecessidadesEspeciais($ed60_i_aluno) && $ed60_c_parecer == 'S') {
                $pdf->cell(15, 4, "PD", "LB", 0, "C", 0);
                $pdf->cell(15, 4, "PD", "LB", 0, "C", 0);
                $pdf->cell(15, 4, "PD", "LBR", 0, "C", 0);
                $notamedia = "PD";
            } else {
                // Acessa as avaliações corretamente
                $aval1 = isset($disciplinaData['avaliacoes'][0]) ? $disciplinaData['avaliacoes'][0]['valor_conceito'] : '';
                $aval2 = isset($disciplinaData['avaliacoes'][1]) ? $disciplinaData['avaliacoes'][1]['valor_conceito'] : '';
                $aval3 = isset($disciplinaData['avaliacoes'][2]) ? $disciplinaData['avaliacoes'][2]['valor_conceito'] : '';

                $pdf->cell(15, 4, $aval1, "LB", 0, "C", 0);
                $pdf->cell(15, 4, $aval2, "LB", 0, "C", 0);
                $pdf->cell(15, 4, $aval3, "LBR", 0, "C", 0);
                $notamedia = $aval3;
            }

            $ed60_situacao = $ed60_c_situacao;

            if (isTransferido($ed60_c_situacao)) {
                $pdf->cell(15, 4, "", "LBR", 1, "C", 0);
            } else {
                $pdf->cell(15, 4, $notamedia, "LBR", 1, "C", 0);
            }
        }

    } elseif ($tipoProcessamento == 'INICIAIS_345ANO') {
        // Anos Iniciais - 3º, 4º e 5º Anos
        $ed60_situacao = $ed60_c_situacao;
        $pdf->cell(191, 4, "Ensino Fundamental - {$ed11_c_descr}", "LTR", 1, "C", 0);
        $pdf->cell(191, 4, "Etapa: {$ed11_c_descr}       Ano Letivo: {$ed52_i_ano}       Nome da Turma: {$ed57_c_descr}", "LRB", 1, "C", 0);
        $pdf->cell(191, 4, "", "LR", 1, "C", 0);

        $pdf->cell(5, 4, "", "L", 0, "C", 0);
        $pdf->cell(83, 4, "Apuração dos Rendimentos", "LTBR", 1, "C", 0);
        $pdf->cell(5, 4, "", "L", 0, "C", 0);
        $pdf->cell(35, 4, "Componentes Curriculares", "LB", 0, "C", 0);
        $pdf->cell(12, 4, "1º TRI", "LB", 0, "C", 0);
        $pdf->cell(12, 4, "2º TRI", "LB", 0, "C", 0);
        $pdf->cell(12, 4, "3º TRI", "LBR", 0, "C", 0);
        $pdf->cell(12, 4, "MÉDIA", "LBR", 1, "C", 0);

        foreach ($dadosgrade2 as $disciplinaData) {
            $disciplinasConceito = array("ARTE", "TECNOLOGIA E INOVAÇÃO", "LÍNGUA INGLESA", "EDUCAÇÃO FISICA");

            // Acessa as avaliações corretamente
            $aval1 = isset($disciplinaData['avaliacoes'][0]) ? $disciplinaData['avaliacoes'][0] : null;
            $aval2 = isset($disciplinaData['avaliacoes'][1]) ? $disciplinaData['avaliacoes'][1] : null;
            $aval3 = isset($disciplinaData['avaliacoes'][2]) ? $disciplinaData['avaliacoes'][2] : null;

            if (in_array($disciplinaData["disciplina"], $disciplinasConceito)) {
                $n1 = $aval1 ? $aval1["valor_conceito"] : '';
                $n2 = $aval2 ? $aval2["valor_conceito"] : '';
                $n3 = $aval3 ? $aval3["valor_conceito"] : '';
            } else {
                $n1 = $aval1 ? ajustaNota($aval1["valor_nota"]) : '';
                $n2 = $aval2 ? ajustaNota($aval2["valor_nota"]) : '';
                $n3 = $aval3 ? ajustaNota($aval3["valor_nota"]) : '';
            }

            $pdf->cell(5, 4, "", "L", 0, "C", 0);
            $pdf->cell(35, 4, $disciplinaData["disciplina"], "LB", 0, "L", 0);

            if (isAlunoComNecessidadesEspeciais($ed60_i_aluno) && $ed60_c_parecer == 'S') {
                $pdf->setfont('arial', '', 7);
                $pdf->cell(12, 4, "PD", "LB", 0, "C", 0);
                $pdf->cell(12, 4, "PD", "LB", 0, "C", 0);
                $pdf->cell(12, 4, "PD", "LBR", 0, "C", 0);
                $pdf->cell(12, 4, "PD", "LBR", 1, "C", 0);
                $pdf->setfont('arial', 'b', 7);
            } else {
                /**
                 * Autor: Uemerson Santana
                 * Data: 30/10/2025
                 * Demanda: 17412
                 * razao: Alinhar as colunas dos trimestres (corpo) ao cabeçalho,
                 *        utilizando largura 12 nas células (antes 10), eliminando o
                 *        deslocamento visual no 3º-5º ano.
                 */
                desenharCelulaNota($pdf, $n1, true, 5, 0, "LB", 12);
                desenharCelulaNota($pdf, $n2, true, 5, 0, "LB", 12);
                desenharCelulaNota($pdf, $n3, true, 5, 0, "LBR", 12);

                // Usa dados já calculados vindos do RPC
                $notamedia = isset($disciplinaData['resultado_final']['nValor']) ? $disciplinaData['resultado_final']['nValor'] : '';
                $situacaoFinal = isset($disciplinaData['resultado_final']['sResultadoFinal']) ? $disciplinaData['resultado_final']['sResultadoFinal'] : '';

                if (in_array($disciplinaData["disciplina"], $disciplinasConceito)) {
                    $notamedia = $aval3 ? $aval3["valor_conceito"] : '';
                } else {
                    $notamedia = formatarNota($notamedia);
                }

                if (isTransferido($ed60_c_situacao)) {
                    $pdf->cell(12, 4, '', "LBR", 1, "C", 0);
                } else {
                    $pdf->cell(12, 4, $notamedia, "LBR", 1, "C", 0);
                }
            }
        }

        $anosiniciais = true;

    } elseif ($tipoProcessamento == 'FINAIS' || $tipoProcessamento == 'FINAIS_ENFUN') {
        // Anos Finais
        $ano_calendario = substr($ed52_c_descr, 12, 4);
        $ed60_situacao = $ed60_c_situacao;

        $pdf->cell(191, 4, "Ensino Fundamental - {$ed11_c_descr}", "LTR", 1, "C", 0);
        $pdf->cell(191, 4, "Etapa: {$ed11_c_descr}       Ano Letivo: {$ed52_i_ano}       Nome da Turma: {$ed57_c_descr}", "LRB", 1, "C", 0);
        $pdf->cell(191, 4, "", "LR", 1, "C", 0);
        $pdf->cell(191, 8, "", "LR", 1, "C", 0);

        $pdf->cell(5, 4, "", "L", 0, "C", 0);
        // Ajustar largura do cabeçalho para incluir AVA FINAL: 34 (disciplina) + colunas de notas
        $larguraCabecalho = 34 + (intval($ano_calendario) <= 2024 ? 90 : 80); // 9 ou 8 colunas de 10
        $pdf->cell($larguraCabecalho, 4, "Apuração dos Rendimentos", "LTBR", 1, "C", 0);
        $pdf->cell(5, 4, "", "L", 0, "C", 0);
        $pdf->cell(34, 4, "Componentes Curriculares", "LB", 0, "C", 0);
        $pdf->cell(10, 4, "1º BIM", "LB", 0, "C", 0);
        $pdf->cell(10, 4, "2º BIM", "LB", 0, "C", 0);
        /**
         * Autor: Uemerson Santana
         * Data: 18/11/2025
         * Demanda: 17412
         * Razao: Em 2025 a Avaliação Final substituiu a coluna de Recuperação Final.
         *        Mantemos as colunas de REC apenas para calendários até 2024.
         */
        $exibeRecSemestral = intval($ano_calendario) <= 2024;
        $exibeRecFinal = intval($ano_calendario) <= 2024;
        if ($exibeRecSemestral) {
            $pdf->cell(10, 4, "REC", "LB", 0, "C", 0);
        }
        $pdf->cell(10, 4, "3º BIM", "LB", 0, "C", 0);
        $pdf->cell(10, 4, "4º BIM", "LB", 0, "C", 0);
        $pdf->cell(10, 4, "MP", "LB", 0, "C", 0);
        if ($exibeRecFinal) {
            $pdf->cell(10, 4, "REC", "LB", 0, "C", 0);
        }
        $pdf->cell(10, 4, "AVA FINAL", "LB", 0, "C", 0);
        $pdf->cell(10, 4, "MF", "LBR", 1, "C", 0);

        foreach ($dadosgrade2 as $disciplinaData) {
            if (isAlunoComNecessidadesEspeciais($ed60_i_aluno) && $ed60_c_parecer == 'S') {
                // Código para alunos com necessidades especiais
                $pdf->cell(5, 4, "", "L", 0, "C", 0);
                $pdf->cell(34, 4, $disciplinaData["disciplina"], "LB", 0, "L", 0);
                $pdf->setfont('arial', '', 7);
                $pdf->cell(10, 4, "PD", "LB", 0, "C", 0);
                $pdf->cell(10, 4, "PD", "LB", 0, "C", 0);
                if ($exibeRecSemestral) {
                    $pdf->cell(10, 4, "PD", "LB", 0, "C", 0);
                }
                $pdf->cell(10, 4, "PD", "LB", 0, "C", 0);
                $pdf->cell(10, 4, "PD", "LB", 0, "C", 0);
                $pdf->cell(10, 4, "PD", "LB", 0, "C", 0);
                $pdf->cell(10, 4, "PD", "LB", 0, "C", 0);
                $pdf->cell(10, 4, "PD", "LB", 0, "C", 0);
                $pdf->cell(10, 4, "PD", "LBR", 1, "C", 0);
                $pdf->setfont('arial', 'b', 7);
            } else {
                /**
                 * Autor: Uemerson Santana
                 * Data: 11/12/2025
                 * Demanda: Correção de inconsistência entre Ficha Individual e Boletim
                 * razao: Corrigido mapeamento de avaliações para identificar corretamente
                 *        AVALIAÇÃO FINAL vs REC FINAL usando o período de avaliação.
                 *        Para 2024, sequência 9 é "AVALIAÇÃO FINAL" e não deve ir na coluna REC.
                 *
                 * Autor: Uemerson Santana
                 * Data: 18/12/2025
                 * Demanda: Correção do 4º bimestre não aparecendo em 2025
                 * razao: Ajustado mapeamento de sequências para considerar ano letivo.
                 *        Em 2025 (sem REC semestral): seq 3=3º bim, seq 4=4º bim, seq 6=AVALIAÇÃO FINAL.
                 *        Em 2024 (com REC semestral): seq 4=REC, seq 5=3º bim, seq 6=4º bim, seq 9=AVALIAÇÃO FINAL.
                 */
                // Mapear avaliações baseado no período, não apenas na posição do array
                $aval1 = null;  // 1º bimestre
                $aval2 = null;  // 2º bimestre
                $avalRec = null;  // REC semestral
                $aval3 = null;  // 3º bimestre
                $aval4 = null;  // 4º bimestre
                $avalRecFinal = null;  // REC final (não confundir com AVALIAÇÃO FINAL)
                $avalFinal = null;  // AVALIAÇÃO FINAL (sequência 9 para 2024)

                $temRecSemestral = false;

                // Mapear cada avaliação baseado no período
                foreach ($disciplinaData['avaliacoes'] as $aval) {
                    $periodo = isset($aval['periodo']) ? trim($aval['periodo']) : '';
                    $sequencia = isset($aval['sequencia']) ? (int)$aval['sequencia'] : 0;

                    if (stripos($periodo, '1º BIMESTRE') !== false || $sequencia == 1) {
                        $aval1 = $aval;
                    } elseif (stripos($periodo, '2º BIMESTRE') !== false || $sequencia == 2) {
                        $aval2 = $aval;
                    } elseif ($exibeRecSemestral) {
                        // Para 2024 ou anterior: há REC semestral
                        if (stripos($periodo, 'RECUPERAÇÃO SEMESTRAL') !== false ||
                              stripos($periodo, 'REC SEMESTRAL') !== false ||
                              $sequencia == 4) {
                        $avalRec = $aval;
                        $temRecSemestral = true;
                    } elseif (stripos($periodo, '3º BIMESTRE') !== false || $sequencia == 5) {
                        $aval3 = $aval;
                    } elseif (stripos($periodo, '4º BIMESTRE') !== false || $sequencia == 6) {
                        $aval4 = $aval;
                    } elseif (stripos($periodo, 'AVALIAÇÃO FINAL') !== false || $sequencia == 9) {
                        // AVALIAÇÃO FINAL não é recuperação - não deve ir na coluna REC
                        $avalFinal = $aval;
                    } elseif (stripos($periodo, 'REC FINAL') !== false ||
                              stripos($periodo, 'RECUPERAÇÃO FINAL') !== false) {
                        // REC FINAL vai na coluna REC
                        $avalRecFinal = $aval;
                        }
                    } else {
                        // Para 2025 ou posterior: não há REC semestral
                        if (stripos($periodo, '3º BIMESTRE') !== false || $sequencia == 3) {
                            $aval3 = $aval;
                        } elseif (stripos($periodo, '4º BIMESTRE') !== false || $sequencia == 4) {
                            $aval4 = $aval;
                        } elseif (stripos($periodo, 'AVALIAÇÃO FINAL') !== false || $sequencia == 6) {
                            // AVALIAÇÃO FINAL não é recuperação - não deve ir na coluna REC
                            $avalFinal = $aval;
                        } elseif (stripos($periodo, 'REC FINAL') !== false ||
                                  stripos($periodo, 'RECUPERAÇÃO FINAL') !== false) {
                            // REC FINAL vai na coluna REC
                            $avalRecFinal = $aval;
                        }
                    }
                }

                if ($disciplinaData["disciplina"] == "TECNOLOGIA E INOVAÇÃO") {
                    $n1 = $aval1 ? $aval1["valor_conceito"] : '';
                    $n2 = $aval2 ? $aval2["valor_conceito"] : '';
                    $n3 = $aval3 ? $aval3["valor_conceito"] : '';
                    $n4 = $aval4 ? $aval4["valor_conceito"] : '';
                    $nr = $aval3 ? $aval3["valor_conceito"] : '';
                } else {
                    $n1 = $aval1 ? ajustaNota($aval1["valor_nota"]) : '';
                    $n2 = $aval2 ? ajustaNota($aval2["valor_nota"]) : '';
                    $n3 = $aval3 ? ajustaNota($aval3["valor_nota"]) : '';
                    $n4 = $aval4 ? ajustaNota($aval4["valor_nota"]) : '';
                    $nr = $avalRec ? ajustaNota($avalRec["valor_nota"]) : '';
                }

                $pdf->cell(5, 4, "", "L", 0, "C", 0);
                $pdf->cell(34, 4, $disciplinaData["disciplina"], "LB", 0, "L", 0);

                // Desenha células das notas bimestrais
                desenharCelulaNota($pdf, $n1, true, 5);
                desenharCelulaNota($pdf, $n2, true, 5);

                if ($exibeRecSemestral) {
                    // Só mostra REC semestral se realmente existir dados
                    if ($temRecSemestral && $avalRec && $avalRec["valor_nota"]) {
                        $pdf->cell(10, 4, str_replace(".", ",", $nr), "LB", 0, "C", 0);
                    } else {
                        $pdf->cell(10, 4, "-", "LB", 0, "C", 0);
                    }
                }

                desenharCelulaNota($pdf, $n3, true, 5);
                desenharCelulaNota($pdf, $n4, true, 5);

                /**
                 * Autor: Uemerson Santana
                 * Data: 06/02/2026
                 * Demanda: 18250
                 * Razao: nValor agora contem a Media Anual (MA) vinda de diarioresultado,
                 *        e nValorNF contem a Nota Final (NF). Antes, ambos vinham de
                 *        ed74_c_valoraprov (que era a NF), causando exibicao incorreta
                 *        na coluna MP e ausencia de valor quando ed74_c_valoraprov era vazio.
                 */
                // Usa dados ja calculados vindos do RPC
                $notamedia = isset($disciplinaData['resultado_final']['nValor']) ? $disciplinaData['resultado_final']['nValor'] : '';
                $situacaoFinal = isset($disciplinaData['resultado_final']['sResultadoFinal']) ? $disciplinaData['resultado_final']['sResultadoFinal'] : '';
                $notafinal = isset($disciplinaData['resultado_final']['nValorNF']) ? $disciplinaData['resultado_final']['nValorNF'] : $notamedia;
                $notafinal2 = $notafinal;
                // Verifica dependências
                // Só verifica dependência se o valor final for numérico e menor que 5.0
                $valorFinalNumerico = is_numeric($notafinal) ? (float)$notafinal : null;
                if ($aval4 && $aval4["valor_nota"] !== null &&
                    $disciplinaData["disciplina"] != 'TECNOLOGIA E INOVAÇÃO' &&
                    $valorFinalNumerico !== null && $valorFinalNumerico < 5.0) {
                    $dependencia = true;
                    $quantdep++;
                }

                // REC final: só usar se for realmente REC FINAL, não AVALIAÇÃO FINAL
                // AVALIAÇÃO FINAL (sequência 9) não deve aparecer na coluna REC
                $notarec = '-';
                if ($avalRecFinal && isset($avalRecFinal["valor_nota"]) && $avalRecFinal["valor_nota"] !== null) {
                    $notarec = formatarNota($avalRecFinal["valor_nota"]);
                }

                // AVA FINAL: usar AVALIAÇÃO FINAL (sequência 9)
                $notaAvaFinal = '-';
                if ($avalFinal && isset($avalFinal["valor_nota"]) && $avalFinal["valor_nota"] !== null) {
                    $notaAvaFinal = formatarNota($avalFinal["valor_nota"]);
                } elseif ($avalFinal && isset($avalFinal["valor_conceito"]) && trim($avalFinal["valor_conceito"]) != '') {
                    // Se for conceito, usar o conceito
                    $notaAvaFinal = trim($avalFinal["valor_conceito"]);
                }

                $notamedia = formatarNota($notamedia);
                $notafinal = formatarNota($notafinal);

                if ($disciplinaData["disciplina"] == "TECNOLOGIA E INOVAÇÃO") {
                    $notamedia = $nr;
                    $notafinal = $nr;
                }

                if (isTransferido($ed60_c_situacao)) {
                    $pdf->cell(10, 4, "", "LB", 0, "C", 0);
                    if ($exibeRecFinal) {
                        $pdf->cell(10, 4, "", "LB", 0, "C", 0);
                    }
                    $pdf->cell(10, 4, "", "LB", 0, "C", 0); // AVA FINAL
                    $pdf->cell(10, 4, "", "LBR", 1, "C", 0);
                } else {
                    desenharCelulaNota($pdf, $notamedia, $notamedia < 5, 5);
                    if ($exibeRecFinal) {
                        desenharCelulaNota($pdf, $notarec, $notarec != '-' && $notarec < 5, 5);
                    }
                    // AVA FINAL: exibir após REC (quando existir) e antes de MF
                    desenharCelulaNota($pdf, $notaAvaFinal, $notaAvaFinal != '-' && is_numeric($notaAvaFinal) && $notaAvaFinal < 5, 5);
                    desenharCelulaNota($pdf, $notafinal, $notafinal < 5, 5, 1, "LBR");
                }
            }
        }

        $imp2024 = true;

    } elseif ($tipoProcessamento == 'EJA_INICIAIS') {
        $ed60_situacao = $ed60_c_situacao;
        $pdf->cell(191, 4, "EJA - Educação de Jovens e Adultos       Etapa: {$ed11_c_descr}       Ano Letivo: {$ed52_i_ano}       Nome da Turma: {$ed57_c_descr}", "LTRB", 1, "C", 0);
        $pdf->cell(191, 8, "", "LR", 1, "C", 0);

        $pdf->cell(5, 4, "", "L", 0, "C", 0);
        $pdf->cell(83, 4, "Apuração dos Rendimentos", "LTBR", 1, "C", 0);
        $pdf->cell(5, 4, "", "L", 0, "C", 0);
        $pdf->cell(33, 4, "Componentes Curriculares", "LB", 0, "C", 0);
        $pdf->cell(10, 4, "1º BIM", "LB", 0, "C", 0);
        $pdf->cell(10, 4, "2º BIM", "LB", 0, "C", 0);
        $pdf->cell(10, 4, "3º BIM", "LB", 0, "C", 0);
        $pdf->cell(10, 4, "4º BIM", "LB", 0, "C", 0);
        $pdf->cell(10, 4, "MÉDIA", "LBR", 1, "C", 0);

        foreach ($dadosgrade2 as $linha) {
            if (isAlunoComNecessidadesEspeciais($ed60_i_aluno) && $ed60_c_parecer == 'S') {
                $pdf->cell(5, 4, "", "L", 0, "C", 0);
                $pdf->cell(33, 4, $linha["disciplina"], "LB", 0, "L", 0);
                $pdf->setfont('arial', '', 7);
                $pdf->cell(10, 4, "PD", "LB", 0, "C", 0);
                $pdf->cell(10, 4, "PD", "LB", 0, "C", 0);
                $pdf->cell(10, 4, "PD", "LB", 0, "C", 0);
                $pdf->cell(10, 4, "PD", "LB", 0, "C", 0);
                $pdf->cell(10, 4, "PD", "LBR", 1, "C", 0);
                $pdf->setfont('arial', 'b', 7);
                continue;
            }

            /**
             * Autor: Uemerson Santana
             * Data: 18/11/2025
             * Demanda: 17412
             * Razao: Recriado quadro de notas para EJA Iniciais com quatro bimestres,
             *        igualando o comportamento ao relatório antigo e evitando páginas
             *        sem grade de notas/frequência.
             */
            $disciplinasConceito = array("INFORMÁTICA", "EMPREENDEDORISMO", "TECNOLOGIA E INOVAÇÃO");
            $aval1 = null;
            $aval2 = null;
            $aval3 = null;
            $aval4 = null;

            foreach ($linha['avaliacoes'] as $aval) {
                $periodo = isset($aval['periodo']) ? trim($aval['periodo']) : '';
                $sequencia = isset($aval['sequencia']) ? (int)$aval['sequencia'] : 0;

                if (stripos($periodo, '1º BIMESTRE') !== false || $sequencia == 1) {
                    $aval1 = $aval;
                } elseif (stripos($periodo, '2º BIMESTRE') !== false || $sequencia == 2) {
                    $aval2 = $aval;
                } elseif (stripos($periodo, '3º BIMESTRE') !== false || $sequencia == 3) {
                    $aval3 = $aval;
                } elseif (stripos($periodo, '4º BIMESTRE') !== false || $sequencia == 4) {
                    $aval4 = $aval;
                }
            }

            if (in_array($linha["disciplina"], $disciplinasConceito)) {
                $n1 = $aval1 ? (isset($aval1['valor_conceito']) ? trim($aval1['valor_conceito']) : '') : '';
                $n2 = $aval2 ? (isset($aval2['valor_conceito']) ? trim($aval2['valor_conceito']) : '') : '';
                $n3 = $aval3 ? (isset($aval3['valor_conceito']) ? trim($aval3['valor_conceito']) : '') : '';
                $n4 = $aval4 ? (isset($aval4['valor_conceito']) ? trim($aval4['valor_conceito']) : '') : '';
            } else {
                $n1 = $aval1 ? (isset($aval1['valor_nota']) ? ajustaNota($aval1['valor_nota']) : '') : '';
                $n2 = $aval2 ? (isset($aval2['valor_nota']) ? ajustaNota($aval2['valor_nota']) : '') : '';
                $n3 = $aval3 ? (isset($aval3['valor_nota']) ? ajustaNota($aval3['valor_nota']) : '') : '';
                $n4 = $aval4 ? (isset($aval4['valor_nota']) ? ajustaNota($aval4['valor_nota']) : '') : '';
            }

            $n1 = ($n1 == null || $n1 === '') ? '' : $n1;
            $n2 = ($n2 == null || $n2 === '') ? '' : $n2;
            $n3 = ($n3 == null || $n3 === '') ? '' : $n3;
            $n4 = ($n4 == null || $n4 === '') ? '' : $n4;

            $pdf->cell(5, 4, "", "L", 0, "C", 0);
            $pdf->cell(33, 4, $linha["disciplina"], "LB", 0, "L", 0);

            desenharCelulaNota($pdf, $n1, true, 5);
            desenharCelulaNota($pdf, $n2, true, 5);
            desenharCelulaNota($pdf, $n3, true, 5);
            desenharCelulaNota($pdf, $n4, true, 5);

            $notamedia = isset($linha['resultado_final']['nValor']) ? $linha['resultado_final']['nValor'] : '';
            $notamedia = in_array($linha["disciplina"], $disciplinasConceito) ? $n4 : formatarNota($notamedia);

            if (isTransferido($ed60_situacao)) {
                $pdf->cell(10, 4, "", "LBR", 1, "C", 0);
            } else {
                $pdf->cell(10, 4, $notamedia, "LBR", 1, "C", 0);
            }
        }

    } elseif ($tipoProcessamento == 'EJA_FINAIS') {
        // EJA Anos Finais
        $ed60_situacao = $ed60_c_situacao;
        $pdf->cell(191, 4, "EJA - Educação de Jovens e Adultos       Etapa: {$ed11_c_descr}       Ano Letivo: {$ed52_i_ano}       Nome da Turma: {$ed57_c_descr}", "LTRB", 1, "C", 0);
        $pdf->cell(191, 8, "", "LR", 1, "C", 0);

        $pdf->cell(25, 4, "", "L", 0, "C", 0);
        $pdf->cell(35, 4, "Componentes Curriculares", "LBT", 0, "C", 0);
        $pdf->cell(10, 4, "1º BIM", "TLB", 0, "C", 0);
        $pdf->cell(10, 4, "2º BIM", "TLB", 0, "C", 0);
        $pdf->cell(10, 4, "3º BIM", "TLB", 0, "C", 0);
        $pdf->cell(10, 4, "4º BIM", "TLB", 0, "C", 0);
        $pdf->setfont('arial', 'b', 6);
        $pdf->cell(12, 4, "Média Final", "TLBR", 1, "C", 0);
        $pdf->setfont('arial', 'b', 7);

        foreach ($dadosgrade2 as $linha) {
            if (isAlunoComNecessidadesEspeciais($ed60_i_aluno) && $ed60_c_parecer == 'S') {
                $pdf->cell(25, 4, "", "L", 0, "C", 0);
                $pdf->cell(35, 4, $linha["disciplina"], "LB", 0, "L", 0);
                $pdf->setfont('arial', '', 7);
                $pdf->cell(10, 4, "PD", "LB", 0, "C", 0);
                $pdf->cell(10, 4, "PD", "LB", 0, "C", 0);
                $pdf->cell(10, 4, "PD", "LB", 0, "C", 0);
                $pdf->cell(10, 4, "PD", "LB", 0, "C", 0);
                $pdf->cell(12, 4, "PD", "LBR", 1, "C", 0);
                $pdf->setfont('arial', 'b', 7);
            } else {
                /**
                 * Autor: Uemerson Santana
                 * Data: 14/11/2025
                 * Demanda: 17412
                 * Razao: Corrigido mapeamento de avaliações para EJA Finais baseado em sequência/período
                 *        em vez de índices fixos do array. Isso garante que os bimestres sejam
                 *        identificados corretamente mesmo quando há RECUPERAÇÃO SEMESTRAL (sequência 4)
                 *        entre o 2º e 3º bimestre, evitando que o 3º bimestre apareça no 4º.
                 */
                $disciplinasConceito = array("INFORMÁTICA", "EMPREENDEDORISMO", "TECNOLOGIA E INOVAÇÃO");

                // Mapear avaliações baseado no período/sequência, não apenas na posição do array
                $aval1 = null;  // 1º bimestre
                $aval2 = null;  // 2º bimestre
                $aval3 = null;  // 3º bimestre
                $aval4 = null;  // 4º bimestre

                // Mapear cada avaliação baseado no período e sequência
                foreach ($linha['avaliacoes'] as $aval) {
                    $periodo = isset($aval['periodo']) ? trim($aval['periodo']) : '';
                    $sequencia = isset($aval['sequencia']) ? (int)$aval['sequencia'] : 0;

                    if (stripos($periodo, '1º BIMESTRE') !== false || $sequencia == 1) {
                        $aval1 = $aval;
                    } elseif (stripos($periodo, '2º BIMESTRE') !== false || $sequencia == 2) {
                        $aval2 = $aval;
                    } elseif (stripos($periodo, '3º BIMESTRE') !== false || $sequencia == 3 || $sequencia == 5) {
                        // Sequência 5 é 3º bimestre quando há REC semestral (sequência 4)
                        $aval3 = $aval;
                    } elseif (stripos($periodo, '4º BIMESTRE') !== false) {
                        // 4º bimestre identificado pelo período
                        $aval4 = $aval;
                    } elseif ($sequencia == 4) {
                        // Sequência 4 pode ser 4º bimestre (disciplinas de conceito) ou REC semestral (disciplinas normais)
                        // Verificar se não é RECUPERAÇÃO SEMESTRAL
                        if (stripos($periodo, 'RECUPERAÇÃO SEMESTRAL') === false &&
                            stripos($periodo, 'REC SEMESTRAL') === false) {
                            $aval4 = $aval;
                        }
                    } elseif ($sequencia == 6) {
                        // Sequência 6 é 4º bimestre para disciplinas normais quando há REC semestral
                        $aval4 = $aval;
                    }
                }

                // Extrair valores baseado no tipo de disciplina
                if (in_array($linha["disciplina"], $disciplinasConceito)) {
                    $n1 = $aval1 ? (isset($aval1['valor_conceito']) ? trim($aval1['valor_conceito']) : '') : '';
                    $n2 = $aval2 ? (isset($aval2['valor_conceito']) ? trim($aval2['valor_conceito']) : '') : '';
                    $n3 = $aval3 ? (isset($aval3['valor_conceito']) ? trim($aval3['valor_conceito']) : '') : '';
                    $n4 = $aval4 ? (isset($aval4['valor_conceito']) ? trim($aval4['valor_conceito']) : '') : '';
                } else {
                    $n1 = $aval1 ? (isset($aval1['valor_nota']) ? ajustaNota($aval1['valor_nota']) : '') : '';
                    $n2 = $aval2 ? (isset($aval2['valor_nota']) ? ajustaNota($aval2['valor_nota']) : '') : '';
                    $n3 = $aval3 ? (isset($aval3['valor_nota']) ? ajustaNota($aval3['valor_nota']) : '') : '';
                    $n4 = $aval4 ? (isset($aval4['valor_nota']) ? ajustaNota($aval4['valor_nota']) : '') : '';
                }

                // Valores padrão para nulos
                $n1 = ($n1 == null || $n1 === '') ? '' : $n1;
                $n2 = ($n2 == null || $n2 === '') ? '' : $n2;
                $n3 = ($n3 == null || $n3 === '') ? '' : $n3;
                $n4 = ($n4 == null || $n4 === '') ? '' : $n4;

                $pdf->cell(25, 4, "", "L", 0, "C", 0);
                $pdf->cell(35, 4, $linha["disciplina"], "LB", 0, "L", 0);

                desenharCelulaNota($pdf, $n1, true, 5);
                desenharCelulaNota($pdf, $n2, true, 5);
                desenharCelulaNota($pdf, $n3, true, 5);
                desenharCelulaNota($pdf, $n4, true, 5);

                $pdf->setfont('arial', 'b', 7);

                // Usa dados já calculados vindos do RPC
                $notamedia = isset($linha['resultado_final']['nValor']) ? $linha['resultado_final']['nValor'] : '';
                $situacaoFinal = isset($linha['resultado_final']['sResultadoFinal']) ? $linha['resultado_final']['sResultadoFinal'] : '';

                // Verifica dependências usando o 4º bimestre mapeado corretamente
                $nota4Bim = $aval4 ? (isset($aval4['valor_nota']) ? $aval4['valor_nota'] : null) : null;
                if ($nota4Bim !== null && $notamedia < 5.0 && !in_array($linha["disciplina"], $disciplinasConceito)) {
                    $dependencia = true;
                    $quantdep++;
                }

                $notamedia = formatarNota($notamedia);

                if (in_array($linha["disciplina"], $disciplinasConceito)) {
                    $notamedia = $n4;
                }

                if (isTransferido($ed60_c_situacao)) {
                    $pdf->cell(12, 4, "", "LBR", 1, "C", 0);
                } else {
                    if ($notamedia == 0) {
                        if (trim($notamedia) == 'PA' || trim($notamedia) == 'PS' || trim($notamedia) == 'PI') {
                            $pdf->cell(12, 4, $notamedia, "LBR", 1, "C", 0);
                        } else {
                            $pdf->cell(12, 4, "", "LBR", 1, "C", 0);
                        }
                    } else {
                        if ($notamedia > 0) {
                            $pdf->cell(12, 4, $notamedia, "LBR", 1, "C", 0);
                        } else {
                            $pdf->cell(12, 4, "", "LBR", 1, "C", 0);
                        }
                    }
                }
            }
        }
    }

    // Renderização de faltas conforme tipo de ensino
    if ($tipoProcessamento != '') {
        /**
         * Autor: Uemerson Santana
         * Data: 30/10/2025
         * Demanda: 17412
         * razao: Atender à regra de negócio: ocultar apenas a porcentagem de
         *        frequência enquanto o ano letivo não estiver encerrado para o aluno,
         *        mantendo as faltas visíveis e preservando as exceções já existentes.
         *
         * Autor: Uemerson Santana
         * Data: 26/11/2025
         * Demanda: 18003
         * Razao: Passar a considerar o encerramento real (existência de resultado em diariofinal)
         *        para liberar a exibição da frequência, usando a data do calendário apenas
         *        como fallback quando ainda não houver resultado final gravado.
         */
        // Flag de encerramento do ano letivo: controla apenas a exibição da porcentagem de frequência
        $lEncerrado = false;

        // 1) Verifica se já existe resultado final lançado no diariofinal para este aluno/ano
        $sSqlEncerradoFreq  = "SELECT 1 ";
        $sSqlEncerradoFreq .= "FROM diario ";
        $sSqlEncerradoFreq .= "INNER JOIN diariofinal ON diariofinal.ed74_i_diario = diario.ed95_i_codigo ";
        $sSqlEncerradoFreq .= "WHERE diario.ed95_i_aluno      = {$ed47_i_codigo} ";
        $sSqlEncerradoFreq .= "  AND diario.ed95_i_escola     = {$escola} ";
        $sSqlEncerradoFreq .= "  AND diario.ed95_i_calendario = {$calendario} ";
        $sSqlEncerradoFreq .= "  AND trim(coalesce(diariofinal.ed74_c_resultadofinal, '')) <> '' ";
        $sSqlEncerradoFreq .= "LIMIT 1";

        $rsEncerradoFreq = db_query($sSqlEncerradoFreq);
        if ($rsEncerradoFreq && pg_num_rows($rsEncerradoFreq) > 0) {
            // Já há resultado final efetivo -> considerar encerrado para fins de frequência
            $lEncerrado = true;
        } else {
            // 2) Fallback: usar data do calendário (comportamento antigo)
            $dataFimAnoLetivoFreq = fimanoletivo($escola, $ed11_i_codigo, $ed60_i_turma, $ed47_i_codigo);
            if ($dataFimAnoLetivoFreq && date('Y-m-d') >= $dataFimAnoLetivoFreq) {
                $lEncerrado = true;
            }
        }
        $pdf->cell(191, 4, "", "LR", 1, "C", 0);

        if ($tipoProcessamento == 'INICIAIS_2ANO' || $tipoProcessamento == 'INICIAIS_345ANO') {
            // Faltas Anos Iniciais
            $pdf->setY($altini + 12);
            $pdf->cell($tipoProcessamento == 'INICIAIS_2ANO' ? 110 : 95, 4, "", 0, 0, "C", 0);
            $pdf->cell($tipoProcessamento == 'INICIAIS_2ANO' ? 50 : 65, 4, "Dias Letivos", "LTB", 0, "C", 0);
            $pdf->cell($tipoProcessamento == 'INICIAIS_2ANO' ? 29 : 25, 4, "Faltas do Aluno", "LTRB", 0, "C", 0);
            $pdf->cell($tipoProcessamento == 'INICIAIS_2ANO' ? 2 : 6, 4, "", "R", 1, "C", 0);

            // Inicializar arrays para somar por trimestre
            $trimestres = array(1 => array('aulas' => 0, 'faltas' => 0),
                               2 => array('aulas' => 0, 'faltas' => 0),
                               3 => array('aulas' => 0, 'faltas' => 0));

            // Somar faltas globalmente para Anos Iniciais (similar aos Anos Finais)
            // Para Anos Iniciais, as faltas são contabilizadas de forma global, não por disciplina
            $faltasGlobaisPorTrimestre = array(1 => 0, 2 => 0, 3 => 0);
            $disciplinaComFaltas = null;

            // Primeiro, identificar a disciplina que registra faltas (freq_global = 'FA')
            foreach ($dadosgrade2 as $linhona) {
                if (isset($linhona["avaliacoes"]) && !empty($linhona["avaliacoes"])) {
                    // Verificar se esta disciplina tem faltas registradas
                    $temFaltas = false;
                    foreach ($linhona["avaliacoes"] as $avaliacao) {
                        if (isset($avaliacao["numero_faltas"]) && $avaliacao["numero_faltas"] > 0) {
                            $temFaltas = true;
                            break;
                        }
                    }

                    if ($temFaltas) {
                        $disciplinaComFaltas = $linhona;
                        break;
                    }
                }
            }

            // Se encontrou disciplina com faltas, usar seus dados para frequência global
            if ($disciplinaComFaltas) {
                foreach ($disciplinaComFaltas["avaliacoes"] as $linhaIdx => $linha) {
                    $trimestre = $linhaIdx + 1;
                    if ($trimestre <= 3) {
                        $faltasGlobaisPorTrimestre[$trimestre] = isset($linha["numero_faltas"]) ? $linha["numero_faltas"] : 0;
                    }
                }
            }

            // CORREÇÃO CIRÚRGICA: Buscar aulas dadas reais por trimestre (como no old)
            // Buscar regência que registra faltas (freq_global = 'FA' ou primeira disciplina com faltas)
            $regenciaComFaltas = null;
            if ($disciplinaComFaltas && isset($disciplinaComFaltas["ed59_i_regencia"])) {
                $regenciaComFaltas = $disciplinaComFaltas["ed59_i_regencia"];
            } else {
                // Se não encontrou, buscar primeira regência obrigatória da turma
                $sqlRegencia = db_query("SELECT ed59_i_codigo
                                         FROM regencia
                                         WHERE ed59_i_turma = {$ed57_i_codigo}
                                           AND ed59_c_condicao = 'OB'
                                         LIMIT 1");
                if (pg_num_rows($sqlRegencia) > 0) {
                    $regenciaComFaltas = pg_fetch_result($sqlRegencia, 0, 0);
                }
            }

            // Buscar aulas dadas por trimestre da regência
            $xaulas = array();
            if ($regenciaComFaltas) {
                $xaulas = dadosAulas($regenciaComFaltas);
            }

            // Preencher arrays de trimestre com aulas reais ou padrão
            foreach ($dadosgrade2 as $linhona) {
                foreach ($linhona["avaliacoes"] as $linhaIdx => $linha) {
                    $trimestre = $linhaIdx + 1;
                    if ($trimestre <= 3) {
                        // Usar aulas reais se disponíveis (como no old)
                        if (!empty($xaulas) && isset($xaulas[$linhaIdx]["ed78_i_aulasdadas"])) {
                            $trimestres[$trimestre]['aulas'] = (int)$xaulas[$linhaIdx]["ed78_i_aulasdadas"];
                        } else {
                            // Fallback: usar valor do calendário dividido por 3
                            $aulasPorTrimestre = percfrequencia($calendario) / 3;
                            $trimestres[$trimestre]['aulas'] = round($aulasPorTrimestre);
                        }
                        // Usar faltas globais para todos os trimestres
                        $trimestres[$trimestre]['faltas'] = $faltasGlobaisPorTrimestre[$trimestre];
                    }
                }
                // Só processar uma vez (dados são globais)
                break;
            }

            $xdl = 0; // Total de aulas
            $xfa = 0; // Total de faltas

            // Renderizar apenas 3 linhas de trimestres
            for ($i = 1; $i <= 3; $i++) {
                $pdf->cell($tipoProcessamento == 'INICIAIS_2ANO' ? 110 : 95, 4, "", 0, 0, "C", 0);
                $pdf->cell($tipoProcessamento == 'INICIAIS_2ANO' ? 30 : 32.5, 4, $i . "º Trimestre", "LB", 0, "C", 0);

                if (isTransferido($ed60_situacao)) {
                    $pdf->cell($tipoProcessamento == 'INICIAIS_2ANO' ? 20 : 32.5, 4, "", "LB", 0, "C", 0);
                    $faltaValor = ($trimestres[$i]['faltas'] > 0) ? $trimestres[$i]['faltas'] : "";
                    $pdf->cell($tipoProcessamento == 'INICIAIS_2ANO' ? 29 : 25, 4, $faltaValor, "LRB", 0, "C", 0);
                } else {
                    $aulasDadas = ($trimestres[$i]['aulas'] > 0) ? $trimestres[$i]['aulas'] : "";
                    $pdf->cell($tipoProcessamento == 'INICIAIS_2ANO' ? 20 : 32.5, 4, $aulasDadas, "LB", 0, "C", 0);
                    $faltaValor = ($trimestres[$i]['faltas'] > 0) ? $trimestres[$i]['faltas'] : "";
                    $pdf->cell($tipoProcessamento == 'INICIAIS_2ANO' ? 29 : 25, 4, $faltaValor, "LRB", 0, "C", 0);
                }

                $pdf->cell($tipoProcessamento == 'INICIAIS_2ANO' ? 2 : 6, 4, "", "R", 1, "C", 0);

                // Somar totais
                $xdl += $trimestres[$i]['aulas'];
                $xfa += $trimestres[$i]['faltas'];
            }

            // Total
            $pdf->cell($tipoProcessamento == 'INICIAIS_2ANO' ? 110 : 95, 4, "", 0, 0, "C", 0);
            $pdf->cell($tipoProcessamento == 'INICIAIS_2ANO' ? 30 : 32.5, 4, "TOTAL", "LB", 0, "C", 0);

            if (isTransferido($ed60_situacao)) {
                $pdf->cell($tipoProcessamento == 'INICIAIS_2ANO' ? 20 : 32.5, 4, "", "LB", 0, "C", 0);
                $pdf->cell($tipoProcessamento == 'INICIAIS_2ANO' ? 29 : 25, 4, "", "LRB", 0, "C", 0);
            } else {
                $pdf->cell($tipoProcessamento == 'INICIAIS_2ANO' ? 20 : 32.5, 4, $xdl, "LB", 0, "C", 0);
                $faltaTotal = ($xfa > 0) ? $xfa : "";
                $pdf->cell($tipoProcessamento == 'INICIAIS_2ANO' ? 29 : 25, 4, $faltaTotal, "LRB", 0, "C", 0);
            }

            $pdf->cell($tipoProcessamento == 'INICIAIS_2ANO' ? 2 : 6, 4, "", "R", 1, "C", 0);

            // Frequência
            $pdf->cell($tipoProcessamento == 'INICIAIS_2ANO' ? 110 : 95, 4, "", 0, 0, "C", 0);
            $xfreq = ($xdl > 0) ? floor((($xdl - $xfa) * 100) / $xdl) : 0;

            /**
             * Autor: Uemerson Santana
             * Data: 30/10/2025
             * Demanda: 17412
             * razao: Exibir a porcentagem de frequência somente após o
             *        encerramento do ano letivo (via $lEncerrado), mantendo as
             *        faltas e as regras de exceção (transferido/evadido).
             */
            $pdf->cell($tipoProcessamento == 'INICIAIS_2ANO' ? 64 : 75, 4, "FREQUÊNCIA % ", "LB", 0, "R", 0);
            if (isTransferido($ed60_situacao) || $ed60_situacao == 'EVADIDO') {
                $pdf->cell(15, 4, "", "RB", 0, "L", 0);
            } else {
                $pdf->cell(15, 4, $lEncerrado ? str_replace(".", ",", $xfreq) : "", "RB", 0, "L", 0);
            }

            $pdf->cell($tipoProcessamento == 'INICIAIS_2ANO' ? 2 : 6, 4, "", "R", 1, "C", 0);

        } elseif ($tipoProcessamento == 'FINAIS' || $tipoProcessamento == 'FINAIS_ENFUN') {
            // Faltas Anos Finais
            $pdf->setY($altini + 20);
            $pdf->cell(135, 4, "", 0, 0, "C", 0);
            $pdf->cell(55, 4, "FALTAS", "LTBR", 1, "C", 0);
            $pdf->cell(135, 4, "", 0, 0, "C", 0);
            $pdf->cell(11, 4, "1º BIM", "LB", 0, "C", 0);
            $pdf->cell(11, 4, "2º BIM", "LB", 0, "C", 0);
            $pdf->cell(11, 4, "3º BIM", "LB", 0, "C", 0);
            $pdf->cell(11, 4, "4º BIM", "LB", 0, "C", 0);
            $pdf->cell(11, 4, "TOTAL", "LBR", 0, "C", 0);
            $pdf->cell(16, 4, "", "R", 1, "C", 0);

            /**
             * Autor: Uemerson Santana
             * Data: 09/12/2025
             * Demanda: 18031
             * Razao: Total exibido usa faltas da turma atual (evita duplicar turmas anteriores);
             *        frequência mantém cálculo original para não alterar regra vigente.
             */
            $gtDisplay = faltasFinalTurmaAtual($ed60_i_codigo, $escola, $calendario);
            $gtFreq    = $gtDisplay;
            $totalaulas = 0;

            foreach ($dadosgrade2 as $linha) {
                $f1 = isset($linha['avaliacoes'][0]['numero_faltas']) ? $linha['avaliacoes'][0]['numero_faltas'] : "0";
                $f2 = isset($linha['avaliacoes'][1]['numero_faltas']) ? $linha['avaliacoes'][1]['numero_faltas'] : "0";
                $f3 = isset($linha['avaliacoes'][2]['numero_faltas']) ? $linha['avaliacoes'][2]['numero_faltas'] : "0";
                $f4 = isset($linha['avaliacoes'][3]['numero_faltas']) ? $linha['avaliacoes'][3]['numero_faltas'] : "0";
                $ft = $f1 + $f2 + $f3 + $f4;
                if (!$ft) {
                    $ft = "0";
                }
                // Não somar mais faltas manualmente - usar valor correto da ATA

                $pdf->cell(135, 4, "", 0, 0, "C", 0);

                // Renderiza células de faltas
                for ($i = 0; $i < 5; $i++) {
                    $valor = "";
                    if ($i == 0) $valor = ($f1 > 0) ? $f1 : "";
                    elseif ($i == 1) $valor = ($f2 > 0) ? $f2 : "";
                    elseif ($i == 2) $valor = ($f3 > 0) ? $f3 : "";
                    elseif ($i == 3) $valor = ($f4 > 0) ? $f4 : "";
                    elseif ($i == 4) $valor = ($ft > 0) ? $ft : "";

                    $pdf->cell(11, 4, $valor, ($i == 4) ? "LBR" : "LB", 0, "C", 0);
                }

                $pdf->cell(16, 4, "", "R", 1, "C", 0);

                // CORREÇÃO: Usar mesmo método da ATA para consistência em Anos Finais
                // em vez de somar aulas reais de cada disciplina
                // Autor: Uemerson Santana
                // Data: 23/01/2025
                // Demanda: 18059
                // Razao: Passar a turma atual da matrícula para percfrequencia(),
                //        garantindo que o turno considerado (INTEGRAL ou não) seja
                //        o da turma real do aluno no calendário.
                $iTurmaAtual = null;
                if (isset($ed60_i_codigo)) {
                    $sSqlMatTurma = "select ed60_i_turma from matricula where ed60_i_codigo = {$ed60_i_codigo}";
                    $rsMatTurma = pg_query($sSqlMatTurma);
                    if ($rsMatTurma && pg_num_rows($rsMatTurma) > 0) {
                        $oMatTurma = db_utils::fieldsMemory($rsMatTurma, 0);
                        $iTurmaAtual = (int)$oMatTurma->ed60_i_turma;
                    }
                }
                $totalaulas = percfrequencia($calendario, $iTurmaAtual);
            }

            // Total de Faltas
            $pdf->cell(135, 4, "", 0, 0, "C", 0);
            $pdf->cell(44, 4, "Total de Faltas", "LB", 0, "C", 0);

            if (isTransferido($ed60_situacao)) {
                $pdf->cell(11, 4, "", "LBR", 0, "C", 0);
            } else {
                $pdf->cell(11, 4, $gtDisplay, "LBR", 0, "C", 0);
            }
            $pdf->cell(16, 4, "", "R", 1, "C", 0);

            // Frequência %
            // CORREÇÃO: Removido uso de percfrequencia para usar total real de aulas
            // if ($tipoProcessamento == 'FINAIS') {
            //     $totalaulas = percfrequencia($calendario);
            // }

            $pdf->cell(135, 4, "", 0, 0, "C", 0);
            $pdf->cell(44, 4, "Frequência %", "LB", 0, "C", 0);
            $xfreq = ($totalaulas > 0) ? floor((($totalaulas - $gtFreq) * 100) / $totalaulas) : 0;

            /**
             * Autor: Uemerson Santana
             * Data: 30/10/2025
             * Demanda: 17412
             * razao: Exibir a porcentagem de frequência somente após
             *        o encerramento do ano letivo, preservando faltas visíveis e
             *        exceções já existentes (transferido/evadido).
             */
            if (isTransferido($ed60_situacao) || $ed60_situacao == 'EVADIDO') {
                $pdf->cell(11, 4, "", "LBR", 1, "C", 0);
            } else {
                $pdf->cell(11, 4, $lEncerrado ? $xfreq : "", "LBR", 1, "C", 0);
            }

        } elseif ($tipoProcessamento == 'EJA_INICIAIS') {
            /**
             * Autor: Uemerson Santana
             * Data: 18/11/2025
             * Demanda: 17412
             * Razao: Reimplementado quadro de faltas/frequência para EJA Iniciais (4 bimestres),
             *        garantindo que o relatório apresente os mesmos dados exibidos nos demais segmentos.
             */
            $pdf->setY($altini + 8);
            $pdf->cell(115, 4, "", 0, 0, "C", 0);
            $pdf->cell(55, 4, "FALTAS", "LTBR", 1, "C", 0);
            $pdf->cell(115, 4, "", 0, 0, "C", 0);
            $pdf->cell(11, 4, "1º BIM", "LB", 0, "C", 0);
            $pdf->cell(11, 4, "2º BIM", "LB", 0, "C", 0);
            $pdf->cell(11, 4, "3º BIM", "LB", 0, "C", 0);
            $pdf->cell(11, 4, "4º BIM", "LB", 0, "C", 0);
            $pdf->cell(11, 4, "TOTAL", "LBR", 0, "C", 0);
            $pdf->cell(21, 4, "", "R", 1, "C", 0);

            /**
             * Autor: Uemerson Santana
             * Data: 09/12/2025
             * Demanda: 18031
             * Razao: Total exibido usa faltas da turma atual (evita duplicar turmas anteriores);
             *        frequência mantém cálculo original para não alterar regra vigente.
             */
            $gtDisplay = faltasFinalTurmaAtual($ed60_i_codigo, $escola, $calendario);
            $gtFreq    = $gtDisplay;
            $totalaulas = 0;

            foreach ($dadosgrade2 as $linha) {
                $f1 = isset($linha['avaliacoes'][0]['numero_faltas']) ? $linha['avaliacoes'][0]['numero_faltas'] : "0";
                $f2 = isset($linha['avaliacoes'][1]['numero_faltas']) ? $linha['avaliacoes'][1]['numero_faltas'] : "0";
                $f3 = isset($linha['avaliacoes'][2]['numero_faltas']) ? $linha['avaliacoes'][2]['numero_faltas'] : "0";
                $f4 = isset($linha['avaliacoes'][3]['numero_faltas']) ? $linha['avaliacoes'][3]['numero_faltas'] : "0";

                $ft = $f1 + $f2 + $f3 + $f4;

                if ($f1 == 0) $f1 = "";
                if ($f2 == 0) $f2 = "";
                if ($f3 == 0) $f3 = "";
                if ($f4 == 0) $f4 = "";
                if ($ft == 0) $ft = "";

                $pdf->cell(115, 4, "", 0, 0, "C", 0);

                $pdf->cell(11, 4, $f1, "LB", 0, "C", 0);
                $pdf->cell(11, 4, $f2, "LB", 0, "C", 0);
                $pdf->cell(11, 4, $f3, "LB", 0, "C", 0);
                $pdf->cell(11, 4, $f4, "LB", 0, "C", 0);
                $pdf->cell(11, 4, $ft, "LBR", 0, "C", 0);

                $pdf->cell(21, 4, "", "R", 1, "C", 0);

                $totalaulas = percfrequencia($calendario);
            }

            $pdf->cell(115, 4, "", 0, 0, "C", 0);
            $pdf->cell(44, 4, "Total de Faltas", "LB", 0, "C", 0);

            if (isTransferido($ed60_situacao)) {
                $pdf->cell(11, 4, "", "LBR", 0, "C", 0);
            } else {
                $pdf->cell(11, 4, $gtDisplay, "LBR", 0, "C", 0);
            }
            $pdf->cell(21, 4, "", "R", 1, "C", 0);

            $pdf->cell(115, 4, "", "L", 0, "C", 0);
            $pdf->cell(44, 4, "Frequência %", "LB", 0, "C", 0);
            $xfreq = ($totalaulas > 0) ? floor((($totalaulas - $gtFreq) * 100) / $totalaulas) : 0;

            if (isTransferido($ed60_situacao) || $ed60_situacao == 'EVADIDO') {
                $pdf->cell(11, 4, "", "LBR", 0, "C", 0);
            } else {
                $pdf->cell(11, 4, $lEncerrado ? $xfreq : "", "LBR", 0, "C", 0);
            }
            $pdf->cell(21, 4, "", "R", 1, "C", 0);

        } elseif ($tipoProcessamento == 'EJA_FINAIS') {
            // Faltas EJA
            $pdf->setY($altini + 8);
            $pdf->cell(115, 4, "", 0, 0, "C", 0);
            $pdf->cell(55, 4, "FALTAS", "LTBR", 1, "C", 0);
            $pdf->cell(115, 4, "", 0, 0, "C", 0);
            $pdf->cell(11, 4, "1º BIM", "LB", 0, "C", 0);
            $pdf->cell(11, 4, "2º BIM", "LB", 0, "C", 0);
            $pdf->cell(11, 4, "3º BIM", "LB", 0, "C", 0);
            $pdf->cell(11, 4, "4º BIM", "LB", 0, "C", 0);
            $pdf->cell(11, 4, "TOTAL", "LBR", 0, "C", 0);
            $pdf->cell(21, 4, "", "R", 1, "C", 0);

            /**
             * Autor: Uemerson Santana
             * Data: 09/12/2025
             * Demanda: 18031
             * Razao: Total exibido usa faltas da turma atual (evita duplicar turmas anteriores);
             *        frequência mantém cálculo original para não alterar regra vigente.
             */
            $gtDisplay = faltasFinalTurmaAtual($ed60_i_codigo, $escola, $calendario);
            $gtFreq    = $gtDisplay;
            $totalaulas = 0;

            foreach ($dadosgrade2 as $linha) {
                $f1 = isset($linha['avaliacoes'][0]['numero_faltas']) ? $linha['avaliacoes'][0]['numero_faltas'] : "0";
                $f2 = isset($linha['avaliacoes'][1]['numero_faltas']) ? $linha['avaliacoes'][1]['numero_faltas'] : "0";
                $f3 = isset($linha['avaliacoes'][2]['numero_faltas']) ? $linha['avaliacoes'][2]['numero_faltas'] : "0";
                $f4 = isset($linha['avaliacoes'][3]['numero_faltas']) ? $linha['avaliacoes'][3]['numero_faltas'] : "0";

                $ft = $f1 + $f2 + $f3 + $f4;

                // Converte zeros em vazio
                if ($f1 == 0) $f1 = "";
                if ($f2 == 0) $f2 = "";
                if ($f3 == 0) $f3 = "";
                if ($f4 == 0) $f4 = "";
                if ($ft == 0) $ft = "";

                // Não somar mais faltas manualmente - usar valor correto da ATA

                $pdf->cell(115, 4, "", 0, 0, "C", 0);

                if ($ed60_situacao == 'MATRICULADO' || $ed60_situacao != 'TRANSFERIDO FORA' && $ed60_situacao != 'TRANSFERIDO REDE') {
                    if ($f1 == "" && $f2 == "" && $f3 == "" && $f4 == "") {
                        $pdf->cell(11, 4, "", "LB", 0, "C", 0);
                        $pdf->cell(11, 4, "", "LB", 0, "C", 0);
                        $pdf->cell(11, 4, "", "LB", 0, "C", 0);
                        $pdf->cell(11, 4, "", "LB", 0, "C", 0);
                        $pdf->cell(11, 4, "", "LBR", 0, "C", 0);
                    } else {
                        $pdf->cell(11, 4, $f1, "LB", 0, "C", 0);
                        $pdf->cell(11, 4, $f2, "LB", 0, "C", 0);
                        $pdf->cell(11, 4, $f3, "LB", 0, "C", 0);
                        $pdf->cell(11, 4, $f4, "LB", 0, "C", 0);
                        $pdf->cell(11, 4, $ft, "LBR", 0, "C", 0);
                    }
                } else {
                    $pdf->cell(11, 4, $f1, "LB", 0, "C", 0);
                    $pdf->cell(11, 4, $f2, "LB", 0, "C", 0);
                    $pdf->cell(11, 4, $f3, "LB", 0, "C", 0);
                    $pdf->cell(11, 4, $f4, "LB", 0, "C", 0);
                    $pdf->cell(11, 4, $ft, "LBR", 0, "C", 0);
                }

                $pdf->cell(21, 4, "", "R", 1, "C", 0);

                // CORREÇÃO: Calcular total de aulas diretamente da base de dados
                // em vez de depender do array xaulas que pode estar vazio
                $sqlAulas = "SELECT SUM(rp.ed78_i_aulasdadas) as total_aulas
                            FROM regenciaperiodo rp
                            INNER JOIN regencia r ON r.ed59_i_codigo = rp.ed78_i_regencia
                            WHERE r.ed59_i_turma = {$ed57_i_codigo}
                              AND r.ed59_c_condicao = 'OB'";
                $resultAulas = db_query($sqlAulas);
                if (pg_num_rows($resultAulas) > 0) {
                    $totalaulas = pg_fetch_result($resultAulas, 0, 0);
                }
            }

            // Total de Faltas
            $pdf->cell(115, 4, "", 0, 0, "C", 0);
            $pdf->cell(44, 4, "Total de Faltas", "LB", 0, "C", 0);

            if (isTransferido($ed60_situacao)) {
                $pdf->cell(11, 4, "", "LBR", 1, "C", 0);
            } else {
                $pdf->cell(11, 4, $gtDisplay, "LBR", 1, "C", 0);
            }

            // Frequência %
            $pdf->cell(115, 4, "", "L", 0, "C", 0);

            // CORREÇÃO: Removido fallback para percfrequencia - usar sempre total real de aulas
            // if ($totalaulas == 0) {
            //     $totalaulas = percfrequencia($calendario);
            // }

            // SEMPRE calcular e exibir frequência, independente do resultado final
            $pdf->cell(44, 4, "Frequência %", "LB", 0, "C", 0);
            $xfreq = ($totalaulas > 0) ? floor((($totalaulas - $gtFreq) * 100) / $totalaulas) : 0;

            /**
             * Autor: Uemerson Santana
             * Data: 30/10/2025
             * Demanda: 17412
             * razao: ...
             */
            if (isTransferido($ed60_situacao) || $ed60_situacao == 'EVADIDO') {
                $pdf->cell(11, 4, "", "LBR", 0, "C", 0);
            } else {
                $pdf->cell(11, 4, $lEncerrado ? $xfreq : "", "LBR", 0, "C", 0);
            }
            $pdf->cell(21, 4, "", "R", 1, "C", 0);
            $pdf->cell(186, 4, "", "L", 0, "C", 0);
            $pdf->cell(5, 12, "", "R", 0, "C", 0);
        }

        // Desenha linhas laterais
        $pdf->line(201, 100, 201, $tipoProcessamento == 'EJA_FINAIS' ? 175 : ($imp2024 ? 165 : 160));
        $pdf->line(10, 100, 10, $tipoProcessamento == 'EJA_FINAIS' ? 175 : ($imp2024 ? 165 : 160));

        if ($tipoProcessamento == 'INICIAIS_2ANO' || $tipoProcessamento == 'INICIAIS_345ANO') {
            $pdf->line(201, 130, 201, 150);
            $pdf->line(10, 130, 10, 150);
        }
    }

    // ===================================================================================================
    // SEÇÃO 12: DEPENDÊNCIAS
    // ===================================================================================================

    $imprimiudep = false;
    $linhaDep = $pdf->getY();

    // FILTRO: Alunos da EJA (todos os tipos - INICIAIS e FINAIS) não possuem dependência
    // Quando um aluno troca de modalidade da turma regular para EJA,
    // ele perde a dependência e caso tenha nota abaixo da média reprova sem carregar dependência
    if ($tipoProcessamento != 'EJA_FINAIS' && $tipoProcessamento != 'EJA_INICIAIS') {

    /**
     * Autor: Uemerson Santana
     * Data: 10/11/2025
     * Demanda: 17412
     * razao: Corrigido para usar schema plugins. nas tabelas de progressão
     *        e ajustar filtro para incluir dependências válidas mesmo sem notas lançadas.
     *        A subquery agora verifica se há pelo menos uma avaliação registrada
     *        (mesmo sem nota) ou resultado final, evitando dependências vazias/duplicadas
     *        sem excluir dependências legítimas que ainda não tiveram notas lançadas.
     */
    /**
     * Autor: Uemerson Santana
     * Data: 10/11/2025
     * Demanda: 17412
     * razao: Corrigido para incluir ed41_i_sequencia e ordenar por disciplina e sequência,
     *        permitindo processamento correto baseado na sequência real das avaliações
     *        em vez de posições fixas no array.
     *        Removido DISTINCT ON e usado DISTINCT para evitar duplicatas do JOIN com resultado.
     */
    /**
     * Autor: Uemerson Santana
     * Data: 10/12/2025
     * Demanda: 18009
     * Razao: Usar o calendário vigente da turma (cal.ed52_i_ano) para buscar DPs,
     *        independentemente do ano de cadastro (ed114_ano), garantindo exibição
     *        de DPs antigas vinculadas ao ano letivo atual.
     */
    $sql1 = "SELECT DISTINCT
                    ed232_c_descr as disciplina,
                    ed114_ano as ano,
                    ed09_c_descr as bimestre,
                    ed09_c_descr as media,
                    ed999_nota as nota,
                    ed998_nota as notareal,
                    ed999_faltas as faltas,
                    ed993_evadido as evadido,
                    ed41_i_sequencia as sequencia,
                    /**
                     * Autor: Uemerson Santana
                     * Data: 09/02/2026
                     * Demanda: 18030
                     * Razao: Adicionado ed115_regencia para buscar aulas dadas reais da regencia
                     *        e calcular frequencia correta na secao DEPENDENCIAS da ficha individual.
                     */
                    ed115_regencia as regencia_dp,
                    ed999_sequencial as codigo_avaliacao
             FROM plugins.diarioprogressaoavaliacao
             INNER JOIN plugins.diarioprogressao ON ed993_sequencial = ed999_diarioprogressao
             INNER JOIN progressaoparcialalunoturmaregencia ON ed115_sequencial = ed993_progressaoparcialalunoturmaregencia
             INNER JOIN progressaoparcialalunomatricula ON ed150_sequencial = ed115_progressaoparcialalunomatricula
             INNER JOIN progressaoparcialaluno ON ed114_sequencial = ed150_progressaoparcialaluno
             INNER JOIN disciplina ON ed12_i_codigo = ed114_disciplina
             INNER JOIN caddisciplina ON ed232_i_codigo = ed12_i_caddisciplina
             INNER JOIN procavaliacao ON ed41_i_codigo = ed999_procavaliacao
             INNER JOIN periodoavaliacao ON ed09_i_codigo = ed41_i_periodoavaliacao
             LEFT JOIN plugins.diarioprogressaoresultado ON ed998_diarioprogressao = ed993_sequencial
             LEFT JOIN procresultado ON ed43_i_codigo = ed998_procresultado
             INNER JOIN regencia tr ON tr.ed59_i_codigo = progressaoparcialalunoturmaregencia.ed115_regencia
             INNER JOIN turma t ON t.ed57_i_codigo = tr.ed59_i_turma
             INNER JOIN calendario cal ON cal.ed52_i_codigo = t.ed57_i_calendario
             INNER JOIN matricula ON ed60_i_aluno = ed114_aluno
             WHERE ed60_i_codigo = {$ed60_i_codigo}
               AND cal.ed52_i_ano = {$ed52_i_ano}
               AND ed993_sequencial IN (
                   SELECT DISTINCT dp.ed993_sequencial
                   FROM plugins.diarioprogressao dp
                   WHERE EXISTS (
                       SELECT 1
                       FROM plugins.diarioprogressaoavaliacao dpa
                       WHERE dpa.ed999_diarioprogressao = dp.ed993_sequencial
                   )
                   OR EXISTS (
                       SELECT 1
                       FROM plugins.diarioprogressaoresultado dpr
                       WHERE dpr.ed998_diarioprogressao = dp.ed993_sequencial
                   )
               )
             ORDER BY ed232_c_descr, ed41_i_sequencia, ed999_sequencial";

    $sql = db_query($sql1);
    if (pg_num_rows($sql) > 0) {
        $pdf->SetFont("Arial", "B", 6);
        $pdf->Cell(191, 4, '', 'LR', 1, "");
        $imprimiudep = true;
        $pdf->Cell(191, 4, 'DEPENDENCIAS', 1, 1, "C");

        // Cabeçalho das dependências
        $pdf->Cell(31, 4, '', 'LR', 0);
        $pdf->Cell(10, 4, '', 'LR', 0);
        $pdf->Cell(10, 4, '', 'LR', 0);
        $pdf->Cell(10, 4, '', 'LR', 0);

        // Condicionar coluna REC semestral no cabeçalho superior também
        if ($ed52_i_ano < 2025) {
            $pdf->Cell(10, 4, '', 'LR', 0);
        }

        $pdf->Cell(10, 4, '', 'LR', 0);
        $pdf->Cell(10, 4, '', 'LR', 0);
        $pdf->Cell(10, 4, '', 'LR', 0);
        if ($ed52_i_ano < 2025) {
            $pdf->Cell(10, 4, '', 'LR', 0, "C");
        } else {
            $pdf->Cell(10, 4, 'AVAL.', 'LR', 0, "C");
        }


        $pdf->Cell(10, 4, 'MÉDIA', 'LR', 0, "C");

        $pdf->Cell(40, 4, 'FALTAS', 'LRB', 0, "C");
        $pdf->Cell(10, 4, 'TOTAL', 'LR', 0, "C");
        $pdf->Cell(10, 4, '', 'LR', 0, "C");
        $pdf->Cell(10, 4, 'RES.', 'LR', 1, "C");

        $pdf->Cell(31, 4, 'DISCIPLINA(S)', 'LRB', 0, "C");
        $pdf->Cell(10, 4, 'ANO(S)', 'LRB', 0, "C");
        $pdf->Cell(10, 4, '1º', 'LRB', 0, "C");
        $pdf->Cell(10, 4, '2º', 'LRB', 0, "C");

        // Condicionar coluna REC semestral baseado no ano letivo
        if ($ed52_i_ano < 2025) {
            $pdf->Cell(10, 4, 'REC', 'LRB', 0, "C");
        }

        $pdf->Cell(10, 4, '3º', 'LRB', 0, "C");
        $pdf->Cell(10, 4, '4º', 'LRB', 0, "C");
        $pdf->Cell(10, 4, 'MÉDIA', 'LRB', 0, "C");

        // Condicionar texto da coluna REC baseado no ano letivo
        if ($ed52_i_ano < 2025) {
            $pdf->Cell(10, 4, 'REC', 'LRB', 0, "C");
        } else {
            $pdf->Cell(10, 4, 'FINAL', 'LRB', 0, "C");
        }

        $pdf->Cell(10, 4, 'FINAL', 'LRB', 0, "C");
        $pdf->Cell(10, 4, '1º', 'LRB', 0, "C");
        $pdf->Cell(10, 4, '2º', 'LRB', 0, "C");
        $pdf->Cell(10, 4, '3º', 'LRB', 0, "C");
        $pdf->Cell(10, 4, '4º', 'LRB', 0, "C");
        $pdf->Cell(10, 4, 'FALTAS', 'LRB', "LR", "C");
        $pdf->Cell(10, 4, '%', 'LRB', 0, "C");
        $pdf->Cell(10, 4, 'FINAL', 'LRB', 1, "C");
        $pdf->SetFont("Arial", "", 6);

        /**
         * Autor: Uemerson Santana
         * Data: 10/11/2025
         * Demanda: 17412
         * razao: Processamento corrigido para agrupar por disciplina e processar
         *        baseado na sequência real (ed41_i_sequencia) em vez de posições fixas.
         *        Mapeamento correto: seq 1=1º, 2=2º, 3=3º (ou REC se < 2025), 4=4º, 6=REC final.
         */
        // Agrupar avaliações por disciplina
        $aDependencias = array();
        for ($i = 0; $i < pg_num_rows($sql); $i++) {
            $oDados = db_utils::fieldsMemory($sql, $i);
            $disciplina = $oDados->disciplina;
            $sequencia = (int)$oDados->sequencia;

            if (!isset($aDependencias[$disciplina])) {
                $aDependencias[$disciplina] = array(
                    'ano' => $oDados->ano,
                    'evadido' => $oDados->evadido,
                    /**
                     * Autor: Uemerson Santana
                     * Data: 09/02/2026
                     * Demanda: 18030
                     * Razao: Adicionado regencia_dp para calcular frequencia com aulas dadas reais.
                     */
                    'regencia_dp' => $oDados->regencia_dp,
                    'avaliacoes' => array(),
                    'faltas' => array()
                );
            }

            $aDependencias[$disciplina]['avaliacoes'][$sequencia] = array(
                'nota' => $oDados->nota,
                'faltas' => $oDados->faltas,
                'notareal' => $oDados->notareal,
                'periodo' => trim($oDados->bimestre) // Armazenar o período para mapeamento correto
            );
        }

        // Processar cada disciplina
        foreach ($aDependencias as $disciplina => $dados) {
            $ano = $dados['ano'];
            $evadido = $dados['evadido'];
            $aAvaliacoes = $dados['avaliacoes'];

            $pdf->Cell(31, 4, $disciplina, 'LRB', 0, "L");
            $pdf->Cell(10, 4, $ano, 'LRB', 0, "C");

            // Mapear sequências para posições corretas
            // Sequência 1 ? 1º bimestre
            $nota1 = isset($aAvaliacoes[1]) ? $aAvaliacoes[1]['nota'] : null;
            $faltas1 = isset($aAvaliacoes[1]) ? $aAvaliacoes[1]['faltas'] : null;
            $pdf->Cell(10, 4, $nota1 !== null ? number_format($nota1, 1, ',', '.') : '', 'LRB', 0, "C");

            // Sequência 2 ? 2º bimestre
            $nota2 = isset($aAvaliacoes[2]) ? $aAvaliacoes[2]['nota'] : null;
            $faltas2 = isset($aAvaliacoes[2]) ? $aAvaliacoes[2]['faltas'] : null;
            $pdf->Cell(10, 4, $nota2 !== null ? number_format($nota2, 1, ',', '.') : '', 'LRB', 0, "C");

            /**
             * Autor: Uemerson Santana
             * Data: 10/11/2025
             * Demanda: 17412
             * razao: Corrigido mapeamento de sequências para 2024.
             *        Em 2024, quando há REC semestral: seq 1=1º, 2=2º, 3=REC, 5=3º, 6=4º, 7+=REC final
             *        Mapeamento baseado no período de avaliação (ed09_c_descr) para identificar corretamente.
             */
            $nota5 = null; // REC semestral
            $nota3 = null; // 3º bimestre
            $nota4 = null; // 4º bimestre
            if ($ed52_i_ano < 2025) {
                // Para 2024, mapear baseado no período de avaliação
                // Buscar REC semestral por período
                foreach ($aAvaliacoes as $seq => $aval) {
                    $periodo = isset($aval['periodo']) ? trim($aval['periodo']) : '';
                    if (stripos($periodo, 'RECUPERAÇÃO SEMESTRAL') !== false ||
                        stripos($periodo, 'REC SEMESTRAL') !== false ||
                        stripos($periodo, 'RSS') !== false) {
                        $nota5 = $aval['nota'];
                        break;
                    }
                }

                // Buscar 3º bimestre - em 2024 com REC semestral, está na sequência 5
                foreach ($aAvaliacoes as $seq => $aval) {
                    $periodo = isset($aval['periodo']) ? trim($aval['periodo']) : '';
                    if (stripos($periodo, '3º BIMESTRE') !== false) {
                        // Se encontrou REC semestral, 3º bimestre deve estar na sequência 5
                        if ($nota5 !== null && $seq == 5) {
                            $nota3 = $aval['nota'];
                            $faltas3 = $aval['faltas'];
                            break;
                        } elseif ($nota5 === null && ($seq == 3 || $seq == 4)) {
                            // Se não há REC semestral, pode estar na sequência 3 ou 4
                            $nota3 = $aval['nota'];
                            $faltas3 = $aval['faltas'];
                            break;
                        }
                    }
                }

                // Buscar 4º bimestre - em 2024 com REC semestral, está na sequência 6
                foreach ($aAvaliacoes as $seq => $aval) {
                    $periodo = isset($aval['periodo']) ? trim($aval['periodo']) : '';
                    if (stripos($periodo, '4º BIMESTRE') !== false) {
                        // Se encontrou REC semestral, 4º bimestre deve estar na sequência 6
                        if ($nota5 !== null && $seq == 6) {
                            $nota4 = $aval['nota'];
                            $faltas4 = $aval['faltas'];
                            break;
                        } elseif ($nota5 === null && ($seq == 4 || $seq == 5)) {
                            // Se não há REC semestral, pode estar na sequência 4 ou 5
                            $nota4 = $aval['nota'];
                            $faltas4 = $aval['faltas'];
                            break;
                        }
                    }
                }

                // Exibir REC semestral se encontrado
                $pdf->Cell(10, 4, $nota5 !== null ? number_format($nota5, 1, ',', '.') : '', 'LRB', 0, "C");
            } else {
                // Para anos >= 2025, sequência 3 é 3º bimestre
                $nota3 = isset($aAvaliacoes[3]) ? $aAvaliacoes[3]['nota'] : null;
                $faltas3 = isset($aAvaliacoes[3]) ? $aAvaliacoes[3]['faltas'] : null;
                // Sequência 4 é 4º bimestre
                $nota4 = isset($aAvaliacoes[4]) ? $aAvaliacoes[4]['nota'] : null;
                $faltas4 = isset($aAvaliacoes[4]) ? $aAvaliacoes[4]['faltas'] : null;
            }

            /**
             * Autor: Uemerson Santana
             * Data: 09/12/2025
             * Demanda: 18009
             * Razao: Em dependências evadidas, exibir notas e faltas lançadas, mas ocultar
             *        média, média final, total de faltas e frequência, mantendo apenas o resultado EVA.
             */
            $lEvadidoDp = ($evadido && $evadido !== 'f');

            $pdf->Cell(10, 4, $nota3 !== null ? number_format($nota3, 1, ',', '.') : '', 'LRB', 0, "C");

            $pdf->Cell(10, 4, $nota4 !== null ? number_format($nota4, 1, ',', '.') : '', 'LRB', 0, "C");

            // Cálculo da média (usar notas dos 4 bimestres)
            $notasBimestrais = array();
            if ($nota1 !== null) $notasBimestrais[] = $nota1;
            if ($nota2 !== null) $notasBimestrais[] = $nota2;
            if ($nota3 !== null) $notasBimestrais[] = $nota3;
            if ($nota4 !== null) $notasBimestrais[] = $nota4;

            $media = count($notasBimestrais) > 0 ? array_sum($notasBimestrais) / count($notasBimestrais) : 0;
            $mediaM = $media;

            // Exibir média quando o 4º bimestre estiver lançado (mesmo que seja 0)
            /**
             * Autor: Uemerson Santana
             * Data: 09/12/2025
             * Demanda: 18009
             * Razao: Evitar exibir média quando a dependência estiver marcada como evadida.
             */
            if (!$lEvadidoDp && $nota4 !== null) {
                $pdf->Cell(10, 4, formatarNota($media), 'LRB', 0, "C");
            } else {
                $pdf->Cell(10, 4, '', 'LRB', 0, "C");
            }

            // REC final - buscar por período "AVALIAÇÃO FINAL" ou sequência 7+
            $nota6 = null;
            if ($ed52_i_ano < 2025) {
                // Para 2024, buscar REC final por período
                foreach ($aAvaliacoes as $seq => $aval) {
                    $periodo = isset($aval['periodo']) ? trim($aval['periodo']) : '';
                    if (stripos($periodo, 'AVALIAÇÃO FINAL') !== false ||
                        stripos($periodo, 'REC FINAL') !== false) {
                        $nota6 = $aval['nota'];
                        break;
                    }
                }
                // Se não encontrou por período, tentar sequência 7 ou superior
                if ($nota6 === null) {
                    for ($seq = 7; $seq <= 10; $seq++) {
                        if (isset($aAvaliacoes[$seq])) {
                            $nota6 = $aAvaliacoes[$seq]['nota'];
                            break;
                        }
                    }
                    // Se ainda não encontrou, verificar sequência 6 apenas se for avaliação final
                    if ($nota6 === null && isset($aAvaliacoes[6])) {
                        $periodo6 = isset($aAvaliacoes[6]['periodo']) ? trim($aAvaliacoes[6]['periodo']) : '';
                        // Só usar sequência 6 se for avaliação final, não 4º bimestre
                        if (stripos($periodo6, 'AVALIAÇÃO FINAL') !== false ||
                            stripos($periodo6, 'REC FINAL') !== false) {
                            $nota6 = $aAvaliacoes[6]['nota'];
                        }
                    }
                }
            } else {
                // Para 2025, sequência 6 é REC final
                $nota6 = isset($aAvaliacoes[6]) ? $aAvaliacoes[6]['nota'] : null;
            }
            $pdf->Cell(10, 4, $nota6 !== null ? number_format($nota6, 1, ',', '.') : '', 'LRB', 0, "C");

            // Média final
            $mediafi = ($nota6 !== null && $nota6 > 0) ? ($mediaM + $nota6) / 2 : $mediaM;

            // Exibir média final quando o 4º bimestre estiver lançado (mesmo que seja 0)
            /**
             * Autor: Uemerson Santana
             * Data: 09/12/2025
             * Demanda: 18009
             * Razao: Evitar exibir média final para dependências evadidas.
             */
            if (!$lEvadidoDp && $nota4 !== null) {
                $pdf->Cell(10, 4, formatarNota($mediafi), 'LRB', 0, "C");
            } else {
                $pdf->Cell(10, 4, '', 'LRB', 0, "C");
            }

            // Faltas
            $faltas1 = $faltas1 !== null ? $faltas1 : 0;
            $faltas2 = $faltas2 !== null ? $faltas2 : 0;
            $faltas3 = $faltas3 !== null ? $faltas3 : 0;
            $faltas4 = $faltas4 !== null ? $faltas4 : 0;

            // Faltas por bimestre continuam visíveis mesmo em evadido
            $pdf->Cell(10, 4, ($faltas1 > 0) ? $faltas1 : '', 'LRB', 0, "C");
            $pdf->Cell(10, 4, ($faltas2 > 0) ? $faltas2 : '', 'LRB', 0, "C");
            $pdf->Cell(10, 4, ($faltas3 > 0) ? $faltas3 : '', 'LRB', 0, "C");
            $pdf->Cell(10, 4, ($faltas4 > 0) ? $faltas4 : '', 'LRB', 0, "C");

            $totalFaltas = $faltas1 + $faltas2 + $faltas3 + $faltas4;
            $pdf->Cell(10, 4, (!$lEvadidoDp && $totalFaltas > 0) ? $totalFaltas : '', 'LRB', "LR", "C");

            /**
             * Autor: Uemerson Santana
             * Data: 09/02/2026
             * Demanda: 18030
             * Razao: Corrigido para calcular frequencia da dependencia com base nas
             *        aulas dadas reais da regencia da DP (regenciaperiodo.ed78_i_aulasdadas),
             *        em vez de usar percfrequencia() que retorna valor fixo do calendario
             *        (ex: 1000 para Anos Finais). O valor fixo eh o total de horas/aula
             *        de TODAS as disciplinas do ano letivo, enquanto as faltas da DP sao
             *        apenas de 1 disciplina, distorcendo o percentual (ex: 97% ao inves de 42%).
             */
            $iRegenciaDp = isset($dados['regencia_dp']) ? $dados['regencia_dp'] : null;
            $iTotalAulasDp = 0;
            if ($iRegenciaDp) {
                $sqlAulasDp = "SELECT COALESCE(SUM(ed78_i_aulasdadas), 0) as total_aulas
                               FROM regenciaperiodo WHERE ed78_i_regencia = {$iRegenciaDp}";
                $resultAulasDp = pg_query($sqlAulasDp);
                if ($resultAulasDp && pg_num_rows($resultAulasDp) > 0) {
                    $iTotalAulasDp = (int)pg_fetch_result($resultAulasDp, 0, 0);
                }
            }
            $sPercentualFrequencia = ($iTotalAulasDp > 0) ? floor((($iTotalAulasDp - $totalFaltas) * 100) / $iTotalAulasDp) : 0;
            /**
             * Autor: Uemerson Santana
             * Data: 09/12/2025
             * Demanda: 18009
             * Razao: Não exibir frequência em dependências evadidas.
             */
            $pdf->Cell(10, 4, !$lEvadidoDp ? $sPercentualFrequencia : '', 'LRB', 0, "C");

            // Resultado final
            if (!$evadido || 'f' == $evadido) {
                if ($mediafi >= 5) {
                    $pdf->Cell(10, 4, ($nota4 !== null && $nota4 > 0) ? 'APR' : '', 'LRB', 1, "C");
                } else {
                    $pdf->Cell(10, 4, ($nota4 !== null && $nota4 > 0) ? 'REP' : '', 'LRB', 1, "C");
                }
            } else {
                $pdf->Cell(10, 4, 'EVA', 'LRB', 1, "C");
            }

            $pdf->SetFont("Arial", "", 6);
        }

        $linhaDep = $pdf->getY();
    }

    } // Fim do filtro para EJA (INICIAIS e FINAIS) - não processa dependências

    // ===================================================================================================
    // SEÇÃO 13: RESULTADO FINAL
    // ===================================================================================================

    if ($tipoProcessamento == 'FINAIS' || $tipoProcessamento == 'FINAIS_ENFUN') {
        $pdf->setY($linhaDep);
    } else {
        $pdf->ln(4);
    }

    if ($clmatricula->numrows > 0) {
        $contador = 0;
        db_fieldsmemory($result1, $ww);

        $oTurma = TurmaRepository::getTurmaByCodigo($ed57_i_codigo);
        $oMatricula = MatriculaRepository::getMatriculaByCodigo($ed60_i_codigo);

        $oGrade = new RelatorioGradeAproveitamento($pdf, MatriculaRepository::getMatriculaByCodigo($ed60_i_codigo), 191);
        $mostrouresultado = false;

        /**
         * Autor: Uemerson Santana
         * Data: 26/11/2025
         * Demanda: 18003
         * Razao: Deixar de usar apenas a data do calendário para definir "EM ANDAMENTO"
         *        e passar a respeitar diretamente o resultado do encerramento.
         *        A Ficha passa a consultar sempre resultado_final_rpc(); se vier vazio
         *        (diário ainda não encerrado / sem resultado), exibe "EM ANDAMENTO".
         *        Se o diário já estiver encerrado, mostra o resultado real
         *        (APROVADO, REPROVADO, APROVADO COM PROGRESSÃO PARCIAL / DEPENDÊNCIA, etc.),
         *        alinhando o comportamento com a ATA e o encerramento oficial.
         */
        // Resultado final padronizado para todos os segmentos (usa regra de encerramento)
        $resultadofinal = resultado_final_rpc($ed11_i_codigo, $ed60_i_turma, $ed60_i_codigo, $ed52_i_ano);

        // Quando ainda não há resultado calculado, considerar "EM ANDAMENTO"
        if (trim($resultadofinal) === '') {
            $resultadofinal = 'EM ANDAMENTO';
        }

        /**
         * Autor: Uemerson Santana
         * Data: 14/11/2025
         * Demanda: 17412
         * Razao: Verificar progressão parcial diretamente no banco usando getProgressaoParcial(),
         *        garantindo que a Ficha use a mesma interpretação da ATA para consistência
         *        entre os relatórios.
         */
        $lAprovadoProgressaoAno = false;
        if ($oMatricula && $oMatricula->getAluno()) {
            $aProgressoesParciais = $oMatricula->getAluno()->getProgressaoParcial();

            foreach ($aProgressoesParciais as $oProgressaoParcial) {
                $iAnoProgressao = $oProgressaoParcial->getAno();
                $iCodigoDiarioFinal = $oProgressaoParcial->getCodigoDiarioFinal();

                if (
                    $ed52_i_ano == $iAnoProgressao
                    && $iCodigoDiarioFinal != null
                ) {
                    $lAprovadoProgressaoAno = true;
                }
            }

            // Se encontrou progressão parcial no banco, sobrescrever o resultado
            if ($lAprovadoProgressaoAno && ($resultadofinal == 'REPROVADO' || stripos($resultadofinal, 'REPROVADO') !== false)) {
                // Buscar termo de aprovação
                $iCodigoEnsino = $oTurma->getBaseCurricular()->getCurso()->getEnsino()->getCodigo();
                $aTermosAprovado = DBEducacaoTermo::getTermoEncerramento($iCodigoEnsino, 'A', $ed52_i_ano);
                $sLabelAprovado = count($aTermosAprovado) > 0 ? $aTermosAprovado[0]->sDescricao : 'APROVADO';

                $resultadofinal = " {$sLabelAprovado} (Progressão Parcial / Dependência)";
            }
        }

        // Ajuste de "NÃO AVALIADO" para alunos NE com Parecer Descritivo
        if (isAlunoComNecessidadesEspeciais($ed60_i_aluno) && isset($ed60_c_parecer) && $ed60_c_parecer == 'S') {
            $sqlNA = "SELECT df.ed74_c_resultadofinal
                      FROM diario d
                      INNER JOIN diariofinal df ON df.ed74_i_diario = d.ed95_i_codigo
                      WHERE d.ed95_i_aluno = {$ed60_i_aluno}
                        AND d.ed95_i_serie = {$ed11_i_codigo}
                        AND d.ed95_i_turma = {$ed60_i_turma}
                      LIMIT 1";
            $rsNA = db_query($sqlNA);
            if ($rsNA && pg_num_rows($rsNA) > 0) {
                $vNA = db_utils::fieldsMemory($rsNA, 0);
                if (isset($vNA->ed74_c_resultadofinal) && $vNA->ed74_c_resultadofinal == 'N') {
                    $resultadofinal = 'NÃO AVALIADO';
                }
            }
        }

        $pdf->setY($linhaDep + 4);

        // Renderiza resultado final baseado no tipo de ensino
        if ($tipoProcessamento == 'EJA_FINAIS') {
            $pdf->line(201, 150, 201, 175);
            $pdf->line(10, 150, 10, 175);

            $pdf->cell(2, 4, "", "L", 0, "L", 0);

            /**
             * Autor: Uemerson Santana
             * Data: 18/11/2025
             * Demanda: 17412
             * Razao: Para alunos transferidos em EJA Finais, não deve ser exibido resultado final
             *        (APROVADO/REPROVADO), apenas a informação da transferência que já é exibida
             *        mais abaixo no relatório. Comportamento deve ser igual ao de alunos evadidos.
             */
            if (isTransferido($ed60_c_situacao)) {
                // Aluno transferido - não exibe resultado, apenas informação de transferência abaixo
                $pdf->cell(189, 4, "", "R", 1, "L", 0);
            } else if ($ed60_c_situacao == 'MATRICULADO') {
                // Aluno matriculado - EJA não usa progressão parcial, sempre resultado do RPC
                $pdf->cell(189, 4, "À vistas dos Resultados Obtidos, o aluno foi considerado: " . strtoupper($resultadofinal), "R", 1, "L", 0);
            } else {
                // Outras situações (EVADIDO, DESISTENTE, etc.)
                if ($ed60_c_situacao != 'EVADIDO' && $ed60_c_situacao != 'TROCA DE TURMA' && $ed60_c_situacao != 'CANCELADO') {
                    $pdf->cell(189, 4, "À vistas dos Resultados Obtidos, o aluno foi considerado: " . strtoupper($resultadofinal), "R", 1, "L", 0);
                } else {
                    $pdf->cell(189, 4, "", "R", 1, "L", 0);
                }
            }
        } else {
            /**
             * Autor: Uemerson Santana
             * Data: 14/11/2025
             * Demanda: 17412
             * Razao: Verificar progressão parcial usando a mesma lógica da ATA, garantindo
             *        consistência entre os relatórios ao verificar diretamente no banco
             *        e considerar o resultado do RPC quando já indica progressão parcial.
             */
            // Verificar se o resultado já contém informação de progressão parcial/dependência
            $lTemProgressaoParcial = (stripos($resultadofinal, 'Progressão Parcial') !== false ||
                                     stripos($resultadofinal, 'Dependência') !== false ||
                                     stripos($resultadofinal, 'Dependencia') !== false);

            // Se encontrou progressão parcial no banco (mesma lógica da ATA), considerar como tendo progressão parcial
            if ($lAprovadoProgressaoAno) {
                $lTemProgressaoParcial = true;
            }

            // Verificar diretamente o método OOP (mantido para compatibilidade)
            $lTemProgressaoParcialOOP = false;
            /**
             * Autor: Uemerson Santana
             * Data: 18/11/2025
             * Demanda: 17412
             * Razao: getDiarioDeClasse() invoca DiarioClasse::criarDiarioClasseAluno(), que exige
             *        transação ativa. Envolvemos a chamada em db_inicio_transacao/db_fim_transacao
             *        para evitar o erro "Sem Transação com o banco de dados ativa".
             */
            if ($oMatricula) {
                db_inicio_transacao();
                try {
                    $oDiarioClasse = $oMatricula->getDiarioDeClasse();
                    if ($oDiarioClasse) {
                        $areaProcedimento = $oDiarioClasse->getAreaProcedimento();
                        if (is_null($areaProcedimento)) {
                            $lTemProgressaoParcialOOP = $oDiarioClasse->aprovadoComProgressaoParcial();
                        }
                    }
                    db_fim_transacao(false);
                } catch (Exception $oErroDiario) {
                    db_fim_transacao(true);
                    throw $oErroDiario;
                }
            }

            if ($dependencia || $lTemProgressaoParcial) {
                // Se o resultado do RPC já indica progressão parcial, usar diretamente (mesma lógica da ATA)
                if ($lTemProgressaoParcial) {
                    if (isTransferido($ed60_c_situacao)) {
                        $pdf->cell(191, 4, "", "LR", 1, "L", 0);
                    } else {
                        $textoExibido = "      À vistas dos Resultados Obtidos, o aluno foi considerado: " . strtoupper($resultadofinal);
                        $pdf->cell(191, 4, $textoExibido, "LR", 1, "L", 0);
                        $pdf->cell(191, 4, "", "LBR", 1, "L", 0);
                    }
                } elseif ($resultadofinal == 'Aprovado' || stripos($resultadofinal, 'Aprovado') !== false) {
                    if (isTransferido($ed60_c_situacao)) {
                        $pdf->cell(191, 4, "", "LR", 1, "L", 0);
                    } else {
                        $aprovado = depanoant($ed60_i_codigo);

                        if ($quantdep <= 2 && $aprovado) {
                            // Verifica aprovação pelo conselho
                            $oDaoAprovConselho = new cl_aprovconselho();
                            $sCamposAprovCons = " distinct ed11_c_descr as serie_conselho, ed52_i_ano, ed253_aprovconselhotipo, ed253_t_obs";
                            $sCamposAprovCons .= ", ed232_c_descr, ed253_alterarnotafinal, ed253_avaliacaoconselho";
                            $sWhereAprovCons = "     ed95_i_aluno = {$ed60_i_aluno} ";
                            $sWhereAprovCons .= " and ed52_i_codigo = {$oGet->calendario} ";
                            $sSqlAprovCons = $oDaoAprovConselho->sql_query("", $sCamposAprovCons, "ed11_c_descr, ed52_i_ano", $sWhereAprovCons);

                            $rsAprovConselho = $oDaoAprovConselho->sql_record($sSqlAprovCons);
                            $iLinhasAprovCons = $oDaoAprovConselho->numrows;
                            $formAprov = "";

                            if ($iLinhasAprovCons > 0) {
                                for ($iContObs = 0; $iContObs < $iLinhasAprovCons; $iContObs++) {
                                    $oDadosAprovConselho = db_utils::fieldsmemory($rsAprovConselho, $iContObs);
                                    switch ($oDadosAprovConselho->ed253_aprovconselhotipo) {
                                        case 1:
                                            $formAprov = "APROVADO PELO CONSELHO";
                                            break;
                                        case 2:
                                            $formAprov = "RECLASSIFICACAO POR BAIXA FREQUENCIA";
                                            break;
                                        case 3:
                                            $formAprov = "APROVADO CONFORME REGIMENTO ESCOLAR";
                                            break;
                                    }
                                }
                            }

                            if ($formAprov == "") {
                                $pdf->cell(191, 4, "      À vistas dos Resultados Obtidos, o aluno foi considerado: " . strtoupper($resultadofinal) . " COM DEPENDENCIA", "LR", 1, "L", 0);
                            } else {
                                $pdf->cell(191, 4, "      À vistas dos Resultados Obtidos, o aluno foi considerado: APROVADO ", "LR", 1, "L", 0);
                            }
                        } else {
                            if ($ed60_c_situacao == 'DESISTENTE') {
                                $pdf->cell(191, 4, "      À vistas dos Resultados Obtidos, o aluno foi considerado: " . $ed60_c_situacao, "LR", 1, "L", 0);
                            } else {
                                $pdf->cell(191, 4, "      À vistas dos Resultados Obtidos, o aluno foi considerado: REPROVADO", "LR", 1, "L", 0);
                            }
                        }
                        $pdf->cell(191, 4, "", "LBR", 1, "L", 0);
                    }
                } else {
                    if (isTransferido($ed60_c_situacao)) {
                        $pdf->cell(191, 4, " ", "LR", 1, "L", 0);
                    } else {
                        $textoExibido = "      À vistas dos Resultados Obtidos, o aluno foi considerado: " . strtoupper($resultadofinal);
                        $pdf->cell(191, 4, " ", "LR", 1, "L", 0);
                        $pdf->cell(191, 4, " ", "LR", 1, "L", 0);
                        $pdf->cell(191, 4, " ", "LR", 1, "L", 0);
                        $pdf->cell(191, 4, " ", "LR", 1, "L", 0);
                        $pdf->cell(191, 4, " ", "LR", 1, "L", 0);
                        $pdf->cell(191, 4, $textoExibido, "LR", 1, "L", 0);
                    }
                }
            } else {
                if (!isTransferido($ed60_c_situacao)) {
                    $linhaDep = $linhaDep + 20;
                    $pdf->setY($linhaDep);
                    $pdf->line(10, 100, 10, 180);
                    $pdf->line(201, 100, 201, 180);

                    if ($ed60_c_situacao != 'EVADIDO' && $ed60_c_situacao != 'TROCA DE TURMA' && $ed60_c_situacao != 'CANCELADO') {
                        $pdf->cell(191, 34, "      À vistas dos Resultados Obtidos, o aluno foi considerado: " . strtoupper($resultadofinal), "LR", 1, "L", 0);
                    } else {
                        $pdf->cell(191, 4, "", "LR", 1, "L", 0);
                    }
                    $pdf->cell(191, 4, "", "LR", 1, "L", 0);
                    $pdf->cell(191, 4, "", "LR", 1, "L", 0);
                    $pdf->cell(191, 4, "", "LR", 1, "L", 0);
                    $pdf->cell(191, 4, "", "LR", 1, "L", 0);
                    $pdf->cell(191, 4, "", "LBR", 1, "L", 0);
                    $mostrouresultado = true;
                }
            }
        }

        // Informações de transferência
        if (substr($ed52_c_descr, 0, 11) == "ANOS FINAIS") {
            $ed60_c_situacao = $ed60_situacao;
        }
        $ed60_c_situacao = $ed60_situacao;

        // Busca data de saída
        if ($ed52_c_descr == "EJA ANOS FINAIS" || $ed52_c_descr == 'EN FUN ANOS INICIAIS' || $ed52_c_descr == 'EN FUN ANOS FINAIS') {
            $sql = "SELECT ed60_d_datasaida FROM matricula WHERE ed60_d_datasaida BETWEEN '2023-01-01' AND '2023-12-31' AND ed60_i_codigo = {$ed60_i_codigo}";
        } else {
            db_fieldsmemory(db_query("SELECT date_part('Year', ed60_d_datamatricula) as anosaida FROM matricula WHERE ed60_i_codigo = {$ed60_i_codigo}"), 0);
            $sql = "SELECT ed60_d_datasaida FROM matricula WHERE ed60_d_datasaida BETWEEN '{$anosaida}-01-01' AND '{$anosaida}-12-31' AND ed60_i_codigo = {$ed60_i_codigo}";
        }

        $rstransf = db_query($sql);
        db_fieldsmemory($rstransf, 0);
        $datasaida = $ed60_d_datasaida;

        if ($ed60_c_situacao != null) {
            $datasaidaaluno = substr($datasaida, 8, 2) . '/' . substr($datasaida, 5, 2) . '/' . substr($datasaida, 0, 4);

            $pdf->cell(4, 4, "", "", 1, "", 0);

            if (substr($ed60_c_situacao, 0, 11) == 'TRANSFERIDO') {
                $pdf->line(10, 100, 10, 160);
                $pdf->line(201, 100, 201, 160);

                $currentY = $pdf->getY();
                $newY = max($currentY, $linhaDep) + 19;
                $pdf->setY($newY);

                $pdf->cell(191, 4, "Aluno transferido em: " . $datasaidaaluno, "LR", 1, "L", 0);
            } else {
                $pdf->line(10, 100, 10, 170);
                $pdf->line(201, 100, 201, 170);
                if ($ed60_c_situacao != 'MATRICULADO') {
                    $pdf->setY($linhaDep);
                    $pdf->line(201, $linhaDep, 201, $linhaDep + 10);
                    $pdf->cell(191, 4, $ed60_c_situacao . " em: " . $datasaidaaluno, "LR", 0, "", 0);
                }
            }
        }

        if ($ed60_situacao == 'MATRICULADO' && $ed11_c_descr == "2º ANO") {
            if (!$mostrouresultado) {
                $pdf->cell(191, 4, "      À vistas dos Resultados Obtidos, o aluno foi considerado: " . strtoupper($resultadofinal), "LR", 1, "L", 0);
            }
        }
    }

    // ===================================================================================================
    // SEÇÃO 14: OBSERVAÇÕES E PARECERES
    // ===================================================================================================

    if ($clmatricula->numrows > 0) {
        $contador = 0;
        db_fieldsmemory($result1, $ww);
        $oTurma = TurmaRepository::getTurmaByCodigo($ed57_i_codigo);
        $oMatricula = MatriculaRepository::getMatriculaByCodigo($ed60_i_codigo);

        $oGrade = new RelatorioGradeAproveitamento($pdf, MatriculaRepository::getMatriculaByCodigo($ed60_i_codigo), 191);
    }

    // Observações por período
    $campos = " ed95_i_regencia,ed232_c_descr,ed72_t_obs,ed72_i_codigo as codaval,ed72_t_parecer as parecer,ed09_c_descr, ";
    $campos .= " ed72_c_amparo as amparoum,ed81_c_todoperiodo as amparo,ed06_c_descr as justificativa,ed72_i_numfaltas,ed09_i_codigo, ";
    $campos .= " ed81_i_justificativa,ed81_i_convencaoamp,ed250_c_abrev,ed72_i_numfaltas,ed78_i_aulasdadas";

    $sql = "SELECT {$campos} ";
    $sql .= "FROM diarioavaliacao ";
    $sql .= "INNER JOIN diario ON ed95_i_codigo = ed72_i_diario ";
    $sql .= "INNER JOIN regencia ON ed59_i_codigo = ed95_i_regencia ";
    $sql .= "INNER JOIN disciplina ON ed12_i_codigo = ed59_i_disciplina ";
    $sql .= "INNER JOIN caddisciplina ON ed232_i_codigo = ed12_i_caddisciplina ";
    $sql .= "LEFT JOIN amparo ON ed81_i_diario = ed95_i_codigo ";
    $sql .= "LEFT JOIN justificativa ON ed06_i_codigo = ed81_i_justificativa ";
    $sql .= "LEFT JOIN convencaoamp ON ed250_i_codigo = ed81_i_convencaoamp ";
    $sql .= "INNER JOIN procavaliacao ON procavaliacao.ed41_i_codigo = diarioavaliacao.ed72_i_procavaliacao ";
    $sql .= "INNER JOIN periodoavaliacao ON periodoavaliacao.ed09_i_codigo = procavaliacao.ed41_i_periodoavaliacao ";
    $sql .= "INNER JOIN regenciaperiodo ON regenciaperiodo.ed78_i_procavaliacao = procavaliacao.ed41_i_codigo ";
    $sql .= "                           AND regenciaperiodo.ed78_i_regencia = regencia.ed59_i_codigo ";
    $sql .= "INNER JOIN diariofinal ON diariofinal.ed74_i_diario = diario.ed95_i_codigo ";
    $sql .= "WHERE ed95_i_aluno = {$ed60_i_aluno} ";
    $sql .= "  AND ed95_i_calendario = {$calendario} ";
    $sql .= "  AND ed59_c_condicao = 'OB' AND (trim(ed72_t_obs) != '' OR trim(ed72_t_parecer) != '') ";
    $sql .= "ORDER BY ed41_i_sequencia, ed59_i_ordenacao";

    $result = db_query($sql);
    $linhas0 = pg_num_rows($result);
    $u = 0;

    for ($r = 0; $r < $linhas0; $r++) {
        db_fieldsmemory($result, $r);
        $pdf->setfont('arial', 'b', 7);

        if ($u != $ed09_i_codigo) {
            $pdf->cell(191, 4, "Período de Avaliação: " . $ed09_c_descr, 1, 1, "C", 1);
            $u = $ed09_i_codigo;
        }

        if ($ed72_t_obs != "" || $parecer != "") {
            $pdf->cell(191, 4, "Disciplina: {$ed232_c_descr}", 1, 1, "L", 0);

            if ($ed72_t_obs != "") {
                $pdf->setfont('arial', 'b', 7);
                $pdf->cell(191, 4, "Observações:", 1, 1, "L", 1);

                $pdf->setfont('arial', '', 7);
                $pdf->multicell(191, 4, ($ed72_t_obs != "" ? $ed72_t_obs . "\n" : ""), 1, "L", 0, 0);
            }

            if ($parecer != "") {
                $pdf->setfont('arial', 'b', 7);
                $pdf->cell(191, 4, "Parecer:", 1, 1, "L", 1);

                $pdf->setfont('arial', '', 7);
                $pdf->multicell(191, 4, ($parecer != "" ? $parecer . "\n" : ""), 1, "L", 0, 0);
                $pdf->cell(191, 3, "", 0, 1, "L", 0);
            }
        }
    }

    // Observações do resultado final
    $campos = " ed95_i_regencia, ed232_c_descr, ed74_t_obs, ed125_codigo,";
    $campos .= " (SELECT 1 FROM progressaoparcialalunodiariofinalorigem ";
    $campos .= " WHERE progressaoparcialalunodiariofinalorigem.ed107_diariofinal = diariofinal.ed74_i_codigo ";
    $campos .= "   AND calendario.ed52_i_codigo = {$oGet->calendario}) as progressao";

    $sql = "SELECT {$campos} ";
    $sql .= "FROM diariofinal ";
    $sql .= "INNER JOIN diario ON ed95_i_codigo = diariofinal.ed74_i_diario ";
    $sql .= "INNER JOIN calendario ON calendario.ed52_i_codigo = diario.ed95_i_calendario ";
    $sql .= "INNER JOIN regencia ON ed59_i_codigo = ed95_i_regencia ";
    $sql .= "INNER JOIN disciplina ON ed12_i_codigo = ed59_i_disciplina ";
    $sql .= "INNER JOIN caddisciplina ON ed232_i_codigo = ed12_i_caddisciplina ";
    $sql .= "LEFT JOIN diarioregracalculo ON diarioregracalculo.ed125_diario = diario.ed95_i_codigo ";
    $sql .= "WHERE ed95_i_aluno = {$ed60_i_aluno} ";
    $sql .= "  AND ed59_c_condicao = 'OB' ";
    $sql .= "ORDER BY ed59_i_ordenacao";

    $result = db_query($sql);
    $linhas1 = pg_num_rows($result);
    $s = 0;

    $lUtilizaProporcionalidade = false;

    for ($r = 0; $r < $linhas1; $r++) {
        db_fieldsmemory($result, $r);

        if (!empty($ed125_codigo)) {
            $lUtilizaProporcionalidade = true;
        }

        if ($s != $ed95_i_regencia) {
            $s = $ed95_i_regencia;

            // Remover observação de progressão parcial - não deve aparecer mais
            // if (!empty($progressao)) {
            //     $sTextoProgressao = "Aluno aprovado nesta disciplina através de progressão parcial.";
            //     $ed74_t_obs .= !empty($ed74_t_obs) ? "\n{$sTextoProgressao}" : $sTextoProgressao;
            // }

            if ($ed74_t_obs != "") {
                $pdf->setfont('arial', 'b', 7);
                $pdf->cell(191, 4, "Disciplina: " . $ed232_c_descr, 1, 1, "C", 1);
                $pdf->cell(191, 4, "Observações Resultado Final:", 1, 1, "L", 1);

                $pdf->setfont('arial', '', 7);
                $pdf->multicell(191, 4, ($ed74_t_obs != "" ? $ed74_t_obs . "\n" : ""), 1, "L", 0, 0);
                $pdf->cell(191, 3, "", 0, 1, "L", 0);
            }
        }
    }

    // Observações do conselho de classe
    $oDaoAprovConselho = new cl_aprovconselho();
    $sCamposAprovCons = " distinct ed11_c_descr as serie_conselho, ed52_i_ano, ed253_aprovconselhotipo, ed253_t_obs";
    $sCamposAprovCons .= ", ed232_c_descr, ed253_alterarnotafinal, ed253_avaliacaoconselho";
    $sWhereAprovCons = "     ed95_i_aluno = {$ed60_i_aluno} ";
    $sWhereAprovCons .= " and ed52_i_codigo = {$oGet->calendario} ";
    $sSqlAprovCons = $oDaoAprovConselho->sql_query("", $sCamposAprovCons, "ed11_c_descr, ed52_i_ano", $sWhereAprovCons);

    $rsAprovConselho = $oDaoAprovConselho->sql_record($sSqlAprovCons);
    $iLinhasAprovCons = $oDaoAprovConselho->numrows;

    $aAprovadoBaixaFrequencia = array();
    $aAprovadoConselhoRegimento = array();

    if ($iLinhasAprovCons > 0) {
        for ($iContObs = 0; $iContObs < $iLinhasAprovCons; $iContObs++) {
            $oDadosAprovConselho = db_utils::fieldsmemory($rsAprovConselho, $iContObs);

            switch ($oDadosAprovConselho->ed253_aprovconselhotipo) {
                case 1:
                    /**
                     * Autor: Uemerson Santana
                     * Data: 21/10/2025
                     * Demana: 17872
                     *
                     * Comentado para remover mensagem padrão redundante na aprovação por conselho.
                     * Mantém apenas a justificativa personalizada do profissional.
                     * Para reativar, descomente o bloco abaixo.
                     *
                    */
                    // Aprovado pelo conselho
                    // $oDocumento = new libdocumento(5013);
                    // $oDocumento->disciplina = $oDadosAprovConselho->ed232_c_descr;
                    // $oDocumento->etapa = $oDadosAprovConselho->serie_conselho;
                    // $oDocumento->justificativa = $oDadosAprovConselho->ed253_t_obs;
                    // $oDocumento->nota = $oDadosAprovConselho->ed253_avaliacaoconselho;
                    // $oDocumento->anomatricula = $oDadosAprovConselho->ed52_i_ano;

                    // $oDadosObservacao = new stdClass();
                    // $oDadosObservacao->aParagrafos = $oDocumento->getDocParagrafos();

                    // if (trim($oDadosObservacao->aParagrafos[1]->oParag->db02_texto) != '') {
                    //     $aAprovadoConselhoRegimento[] = "- " . $oDadosObservacao->aParagrafos[1]->oParag->db02_texto;
                    //     $aprovCons = true;
                    // }
                    // Priorizar a justificativa digitada na tela, quando preenchida
                    // if (!empty($oDadosAprovConselho->ed253_t_obs)) {
                    //     $aAprovadoConselhoRegimento[] = "- Justificativa (" . $oDadosAprovConselho->ed232_c_descr . "): " . $oDadosAprovConselho->ed253_t_obs;
                    // }
                    $aAprovadoConselhoRegimento[] = "- Justificativa (" . $oDadosAprovConselho->ed232_c_descr . "): " . $oDadosAprovConselho->ed253_t_obs;
                    break;

                case 2:
                    /**
                     * Autor: Uemerson Santana
                     * Data: 21/10/2025
                     * Demanda: 17872
                     *
                     * Comentado para remover mensagem padrão redundante na reclassificação por baixa frequência.
                     * Mantém apenas a justificativa personalizada do profissional.
                     * Para reativar, descomente o bloco abaixo.
                     *
                    */
                    // Reclassificação por baixa frequência
                    // $sHashSerieAno = $oDadosAprovConselho->serie_conselho . $oDadosAprovConselho->ed52_i_ano;
                    // if (!isset($aAprovadoBaixaFrequencia[$sHashSerieAno])) {
                    //     $aAprovadoBaixaFrequencia[$sHashSerieAno] = $oDadosAprovConselho;
                    //     $aprovCons = true;
                    // }
                    // // Priorizar a justificativa digitada na tela, quando preenchida
                    // if (!empty($oDadosAprovConselho->ed253_t_obs)) {
                    //     $aAprovadoConselhoRegimento[] = "\n- Justificativa (" . $oDadosAprovConselho->ed232_c_descr . "): " . $oDadosAprovConselho->ed253_t_obs . "\n";
                    // }
                    $aAprovadoConselhoRegimento[] = "\n- Justificativa (" . $oDadosAprovConselho->ed232_c_descr . "): " . $oDadosAprovConselho->ed253_t_obs . "\n";
                    break;

                case 3: // Aprovado por regimento escolar
                    $sTipoAprovacao = "foi aprovado pelo regimento escolar.";
                    $sObservacao = "- Disciplina {$oDadosAprovConselho->ed232_c_descr} na etapa";
                    $sObservacao .= " {$oDadosAprovConselho->serie_conselho} {$sTipoAprovacao}";
                    $sObservacao .= "Justificativa: {$oDadosAprovConselho->ed253_t_obs}";

                    $aprovCons = true;
                    $aAprovadoConselhoRegimento[] = $sObservacao;
                    break;
            }
        }
    }

    $sObservacaoConselho = '';
    if (count($aAprovadoBaixaFrequencia) > 0) {
        $oDocumento = new libdocumento(5006);

        foreach ($aAprovadoBaixaFrequencia as $oBaixaFrequencia) {
            $oDocumento->nome_aluno = $ed47_v_nome;
            $oDocumento->ano = $oBaixaFrequencia->ed52_i_ano;
            $oDocumento->nome_etapa = $oBaixaFrequencia->serie_conselho;
            $aParagrafos = $oDocumento->getDocParagrafos();

            if (isset($aParagrafos[1])) {
                $sObservacaoConselho .= "- {$aParagrafos[1]->oParag->db02_texto}\n";
            }
        }
    }

    $sObservacaoConselho .= implode("\n", $aAprovadoConselhoRegimento);

    // Observações sobre proporcionalidade
    $oEnsino = $oTurma->getBaseCurricular()->getCurso()->getEnsino();
    $sObservacaoProporcionalidade = "";

    if ($lUtilizaProporcionalidade) {
        if ($oEnsino->getCodigoTipoEnsino() == $oEnsino::ENSINO_REGULAR) {
            $oDocumento = new libdocumento(5017);
            $aParagrafos = $oDocumento->getDocParagrafos();

            if (isset($aParagrafos[1])) {
                $sObservacaoProporcionalidade .= "- {$aParagrafos[1]->oParag->db02_texto}\n";
            }
        } else if ($oEnsino->getCodigoTipoEnsino() == $oEnsino::ENSINO_EJA) {
            $oDocumento = new libdocumento(5018);
            $aParagrafos = $oDocumento->getDocParagrafos();

            if (isset($aParagrafos[1])) {
                $sObservacaoProporcionalidade .= "- {$aParagrafos[1]->oParag->db02_texto}\n";
            }
        }
    }

    // Imprime observações gerais
    if ($sObs != "" || $sObservacaoConselho || $sObservacaoProporcionalidade != "") {
        $pdf->setfont('arial', 'b', 7);
        $pdf->cell(191, 4, "", 0, 1, "L", 0);
        $pdf->cell(191, 4, "Observações Gerais:", 1, 1, "L", 1);

        $pdf->setfont('arial', '', 7);
        $sObservacaoImprimir = $sObservacaoProporcionalidade;
        $sObservacaoImprimir .= "{$sObservacaoConselho}" . mb_strtoupper($sObs);

        $pdf->multicell(191, 4, $sObservacaoImprimir, 1, "L", 0, 0);
    }

    // ===================================================================================================
    // SEÇÃO 15: ASSINATURAS E RODAPÉ
    // ===================================================================================================

    $final = $pdf->getY();

    // Data
    $pdf->setY($final + 3);
    $pdf->setfont('arial', '', 7);
    $pdf->cell(25, 4, $data_extenso, 0, 1, "L", 0);

    $fim = $pdf->getY();

    // Linhas de fechamento
    $pdf->Line(10, 75, 10, $fim);
    $pdf->Line(201, 75, 201, $fim);
    $pdf->Line(10, $fim, 201, $fim);
    $pdf->setY($fim + 1);

    // Busca regente conselho
    $sCampos = "CASE WHEN ed20_i_tiposervidor = 1 THEN cgmrh.z01_nome ELSE cgmcgm.z01_nome END as regente";
    $sSqlRegenteConselho = $clregenteconselho->sql_query("", $sCampos, "", " ed235_i_turma = {$ed57_i_codigo}");
    $result5 = $clregenteconselho->sql_record($sSqlRegenteConselho);

    if ($clregenteconselho->numrows > 0) {
        db_fieldsmemory($result5, 0);
    } else {
        $regente = "";
    }

    $pdf->Ln(4);

    // Posições das assinaturas
    $iPosicaoXProfessor = 10;
    $iPosicaoXAdicional = 10;
    $iPosicaoXDiretor = 10;

    $iPosicaoYDiretor = $pdf->GetY();
    $iPosicaoYProfessor = $pdf->GetY();

    $lExibirAdicional = false;
    $lExibirAdicional2 = false;
    $lExibirProfessor = false;

    if (isset($oGet->iAssinaturaAdicional) && !empty($oGet->iAssinaturaAdicional)) {
        $lExibirAdicional = true;
    }

    if (isset($oGet->iAssinaturaAdicional2) && !empty($oGet->iAssinaturaAdicional2)) {
        $lExibirAdicional2 = true;
    }

    if (isset($oGet->lExibeAssinaturaProfessor) && $oGet->lExibeAssinaturaProfessor == "S") {
        $lExibirProfessor = true;
    }

    // Imprime linhas de assinatura
    if ($ed60_c_situacao == 'EVADIDO') {
        $pdf->cell(57, 3, '', 0, 1, "L", 0);
        $pdf->cell(57, 3, '', 0, 1, "L", 0);
    }

    $pdf->cell(12, 3, '', 0, 0, "L", 0);
    if ($iAssinaturaAdicional3 != null) {
        $pdf->cell(58, 3, '____________________________________', 0, 0, "L", 0);
    } else {
        $pdf->cell(58, 3, '', 0, 0, "L", 0);
    }
    if ($iAssinaturaAdicional2 != null) {
        $pdf->cell(56, 3, '____________________________________', 0, 0, "L", 0);
    } else {
        $pdf->cell(56, 3, '', 0, 0, "L", 0);
    }
    if ($iAssinaturaAdicional != null) {
        $pdf->cell(57, 3, '____________________________________', 0, 1, "L", 0);
    } else {
        $pdf->cell(57, 3, '', 0, 1, "L", 0);
    }

    // Nomes das assinaturas
    $pdf->cell(13, 3, '', 0, 0, '', 0);

    if ($iAssinaturaAdicional3 != null) {
        if (strpos($iAssinaturaAdicional3, 'ã') > 0) {
            $pdf->cell(58, 3, $iAssinaturaAdicional3, 0, 0, "L", 0);
        } else {
            $pdf->cell(58, 3, utf8_decode($iAssinaturaAdicional3), 0, 0, "L", 0);
        }
    } else {
        $pdf->cell(58, 3, '', 0, 0, "L", 0);
    }

    if ($iAssinaturaAdicional2 != null) {
        if (strpos($iAssinaturaAdicional2, 'ã') > 0) {
            $pdf->cell(58, 3, $iAssinaturaAdicional2, 0, 0, "L", 0);
        } else {
            $pdf->cell(58, 3, utf8_decode($iAssinaturaAdicional2), 0, 0, "L", 0);
        }
    } else {
        $pdf->cell(57, 3, '', 0, 0, "L", 0);
    }

    if ($iAssinaturaAdicional != null) {
        if (strpos($iAssinaturaAdicional, 'ã') > 0) {
            $pdf->cell(58, 3, $iAssinaturaAdicional, 0, 1, "L", 0);
        } else {
            $pdf->cell(58, 3, utf8_decode($iAssinaturaAdicional), 0, 1, "L", 0);
        }
    } else {
        $pdf->cell(64, 3, '', 0, 1, "L", 0);
    }

    // Cargos
    $pdf->cell(13, 3, '', 0, 0, '', 0);
    if ($iAssinaturaAdicional3 != null) {
        $pdf->cell(58, 3, 'SECRETÁRIO DE ESCOLA', 0, 0, "L", 0);
    } else {
        $pdf->cell(58, 3, '', 0, 0, "L", 0);
    }
    if ($iAssinaturaAdicional2 != null) {
        $pdf->cell(57, 3, 'SUPERVISOR ESCOLAR', 0, 0, "L", 0);
    } else {
        $pdf->cell(57, 3, '', 0, 0, "L", 0);
    }
    if ($iAssinaturaAdicional != null) {
        $pdf->cell(64, 3, 'DIRETOR(A)', 0, 1, "L", 0);
    } else {
        $pdf->cell(64, 3, '', 0, 1, "L", 0);
    }

    /**
     * Autor: Uemerson Santana
     * Data: 27/11/2025
     * Demanda: 17982
     * Razão: Corrigida busca das matrículas de secretário, supervisor e diretor para filtrar pela escola vinculada através de rechumanoescola,
     *        garantindo que quando o profissional possui múltiplas matrículas, seja retornada a correta vinculada à escola atual.
     *        Removido substr que removia os 2 primeiros dígitos na exibição, exibindo agora a matrícula completa.
     */
    // Busca matrículas dos assinantes (filtradas por escola para garantir a correta quando o profissional tem múltiplas matrículas)
    $iEscola = db_getsession("DB_coddepto");
    db_fieldsmemory(db_query("SELECT ed284_i_rhpessoal as matriculasec
                              FROM rechumanopessoal
                              INNER JOIN rechumano ON rechumano.ed20_i_codigo = rechumanopessoal.ed284_i_rechumano
                              INNER JOIN rechumanoescola ON rechumanoescola.ed75_i_rechumano = rechumano.ed20_i_codigo
                              INNER JOIN rhpessoal ON rhpessoal.rh01_regist = rechumanopessoal.ed284_i_rhpessoal
                              WHERE rh01_numcgm = {$matrisec}
                                AND rechumanoescola.ed75_i_escola = {$iEscola}
                              LIMIT 1"), 0);

    db_fieldsmemory(db_query("SELECT ed284_i_rhpessoal as matriculasup
                              FROM rechumanopessoal
                              INNER JOIN rechumano ON rechumano.ed20_i_codigo = rechumanopessoal.ed284_i_rechumano
                              INNER JOIN rechumanoescola ON rechumanoescola.ed75_i_rechumano = rechumano.ed20_i_codigo
                              INNER JOIN rhpessoal ON rhpessoal.rh01_regist = rechumanopessoal.ed284_i_rhpessoal
                              WHERE rh01_numcgm = {$matrisup}
                                AND rechumanoescola.ed75_i_escola = {$iEscola}
                              LIMIT 1"), 0);

    db_fieldsmemory(db_query("SELECT ed284_i_rhpessoal as matriculadir
                              FROM rechumanopessoal
                              INNER JOIN rechumano ON rechumano.ed20_i_codigo = rechumanopessoal.ed284_i_rechumano
                              INNER JOIN rechumanoescola ON rechumanoescola.ed75_i_rechumano = rechumano.ed20_i_codigo
                              INNER JOIN rhpessoal ON rhpessoal.rh01_regist = rechumanopessoal.ed284_i_rhpessoal
                              WHERE rh01_numcgm = {$matridir}
                                AND rechumanoescola.ed75_i_escola = {$iEscola}
                              LIMIT 1"), 0);

    // Matrículas
    $pdf->cell(13, 3, '', 0, 0, '', 0);

    if ($iAssinaturaAdicional3 != null) {
        if (empty($matriculasec)) {
            if ($matrisec == 163737) {
                $matriculasec = 40770;
            }
            $pdf->cell(58, 3, 'Matrícula: ' . $matriculasec, 0, 0, "L", 0);
        } else {
            $pdf->cell(58, 3, 'Matrícula: ' . $matriculasec, 0, 0, "L", 0);
        }
    } else {
        $pdf->cell(58, 3, '', 0, 0, "L", 0);
    }

    if ($iAssinaturaAdicional2 != null) {
        if (empty($matriculasup)) {
            $pdf->cell(57, 3, 'Matrícula: ', 0, 0, "L", 0);
        } else {
            $pdf->cell(57, 3, 'Matrícula: ' . $matriculasup, 0, 0, "L", 0);
        }
    } else {
        $pdf->cell(57, 3, '', 0, 0, "L", 0);
    }

    if ($iAssinaturaAdicional != null) {
        if (empty($matriculadir)) {
            $pdf->cell(64, 3, 'Matrícula: ', 0, 1, "L", 0);
        } else {
            $pdf->cell(64, 3, 'Matrícula: ' . $matriculadir, 0, 1, "L", 0);
        }
    } else {
        $pdf->cell(64, 3, '', 0, 1, "L", 0);
    }

    // Assinatura do professor
    if ($lExibirProfessor) {
        $iPosicaoXDiretor = 120;

        $pdf->setY($iPosicaoYProfessor);
        $pdf->setX($iPosicaoXProfessor);
        $pdf->cell(45, 5, str_repeat('_', 45), 0, 1, "L", 0);

        $pdf->setX($iPosicaoXProfessor);
        $pdf->multicell(70, 4, "", 0, "L", 0, 0);
        $pdf->setX($iPosicaoXProfessor);
        $pdf->cell(45, 3, "Professor", 0, 1, "L", 0);
    }
}

// Gera o PDF
$pdf->Output();
?>



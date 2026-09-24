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

//  error_reporting(E_ALL);           // ou 32767
// ini_set('display_errors', 1);     // mostra erros na tela
// ini_set('display_startup_errors', 1);


/**
 * @author Uemerson Santana
 * @date 11/09/2025
 * @version 2.0 (Versão refatorada)
 * @description Ficha individual do aluno 003 do aluno com código otimizado e organizado
 */

require_once(modification("libs/db_stdlibwebseller.php"));
require_once(modification("fpdf151educacao/pdfwebseller.php"));
require_once(modification("libs/db_utils.php"));
require_once(modification("dbforms/db_funcoes.php"));
require_once(modification("model/educacao/Turma.model.php"));
require_once(modification("model/educacao/AlunoRepository.model.php"));
require_once(modification("model/educacao/relatorio/RelatorioGradeAproveitamento.model.php"));
require_once(modification("model/educacao/avaliacao/GradeAproveitamentoAluno.model.php"));
require_once(modification("model/educacao/RegenciaRepository.model.php"));

// ================================================================================================
// CLASSE AUXILIAR PARA ORGANIZAÇÃO DO RELATÓRIO
// ================================================================================================
/**
 * Classe auxiliar para centralizar a lógica do relatório de ficha individual do aluno
 * Organiza funções de consulta, formatação e impressão
 */
class RelatorioFichaIndividualAluno
{
    private $pdf;
    private $escola;
    private $ano;

    /**
     * Construtor da classe
     * @param PDF $pdf Objeto PDF para impressão
     * @param int $escola Código da escola
     * @param int $ano Ano do exercício
     */
    public function __construct($pdf, $escola, $ano)
    {
        $this->pdf = $pdf;
        $this->escola = $escola;
        $this->ano = $ano;
    }

    // ================================================================================================
    // FUNÇÕES DE CONSULTA AO BANCO DE DADOS
    // ================================================================================================

    /**
     * Verifica se um aluno possui necessidades especiais
     * @author Uemerson Santana
     * @date 22/05/2025
     * @param int $iCodigoAluno - Código do aluno
     * @return bool - True se tem necessidades especiais
     */
    public function isAlunoComNecessidadesEspeciais($iCodigoAluno)
    {
        $sSql = "SELECT ed214_i_aluno FROM alunonecessidade WHERE ed214_i_aluno = {$iCodigoAluno}";
        $rsResult = db_query($sSql);
        return (pg_num_rows($rsResult) > 0);
    }

    /**
     * Retorna a descrição da nacionalidade
     * @param int $ed47_i_nacion Código da nacionalidade
     * @return string Descrição da nacionalidade
     */
    public function retornaNacionalidade($ed47_i_nacion)
    {
        $nacionalidades = array(
            1 => "Brasileira",
            2 => "Brasileira no exterior ou naturalizado",
            3 => "Estrangeira"
        );
        return isset($nacionalidades[$ed47_i_nacion]) ? $nacionalidades[$ed47_i_nacion] : "Estrangeira";
    }

    /**
     * Retorna a naturalidade do aluno
     * @param int $ed47_i_censomunicnat Código do município de nascimento
     * @return string Nome do município
     */
    public function retornaNaturalidade($ed47_i_censomunicnat)
    {
        $sql = pg_query("SELECT ed261_c_nome FROM censomunic WHERE ed261_i_codigo = {$ed47_i_censomunicnat}");
        $resultado = pg_fetch_all($sql);
        if ($resultado && isset($resultado[0]["ed261_c_nome"])) {
            $nome = strtolower($resultado[0]["ed261_c_nome"]);
            return ucwords($nome);
        }
        return "";
    }

    /**
     * Busca dados do diário de classe
     * @param int $codiario Código do diário
     * @return array Dados do diário
     */
    public function dadosDiario($codiario)
    {
        $sql = "SELECT
                   ed72_i_codigo as codigo,
                   ed72_i_procavaliacao as codigo_elemento,
                   ed72_i_numfaltas as numero_faltas,
                   ed80_i_codigo as codigo_faltas_abonadas,
                   ed72_i_valornota as valor_nota,
                   ed72_i_valornota as valor_nota_real,
                   ed72_c_valorconceito as valor_conceito,
                   ed72_t_parecer as parecer,
                   ed72_c_aprovmin as minimo,
                   ed72_c_amparo as amparo,
                   ed41_i_sequencia as sequencia,
                   trim(ed93_t_parecer) as parecerpadronizado,
                   ed72_i_escola as escola,
                   ed72_c_tipo as origem,
                   ed72_c_convertido as convertido,
                   (select ed39_i_sequencia
                    from conceito
                    where conceito.ed39_i_formaavaliacao = ed41_i_formaavaliacao
                    and conceito.ed39_c_conceito = ed72_c_valorconceito) as ordem_conceito,
                   'A' as tipo_elemento,
                   ed72_t_obs as observacao,
                   false as em_recuperacao
                FROM diarioavaliacao
                INNER JOIN procavaliacao on ed41_i_codigo = ed72_i_procavaliacao
                LEFT JOIN pareceraval on ed93_i_diarioavaliacao = ed72_i_codigo
                LEFT JOIN abonofalta on ed80_i_diarioavaliacao = ed72_i_codigo
                WHERE ed72_i_diario = {$codiario}
                ORDER BY sequencia";

        $resultado = pg_fetch_all(pg_query($sql));
        return $resultado ? $resultado : array();
    }

    /**
     * Busca dados das aulas dadas
     * @param int $codregencia Código da regência
     * @return array Dados das aulas
     */
    public function dadosAulas($codregencia)
    {
        $sql = "SELECT
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
                WHERE ed78_i_regencia = {$codregencia} AND ed09_c_somach = 'S'
                ORDER BY ed78_i_procavaliacao";

        $resultado = pg_fetch_all(pg_query($sql));
        return $resultado ? $resultado : array();
    }

    /**
     * Retorna o nome da disciplina
     * @param int $ed59_i_disciplina Código da disciplina
     * @return string Nome da disciplina
     */
    public function retornaNomeDisciplina($ed59_i_disciplina)
    {
        $sql = pg_query("SELECT ed232_c_descr
                         FROM caddisciplina
                         INNER JOIN disciplina ON ed232_i_codigo = ed12_i_caddisciplina
                         WHERE ed12_i_codigo = {$ed59_i_disciplina}");
        $resultado = pg_fetch_all($sql);
        return isset($resultado[0]["ed232_c_descr"]) ? $resultado[0]["ed232_c_descr"] : "";
    }

    /**
     * Busca código do diário
     * @param int $ed60_i_codigo Código da matrícula
     * @param int $ed60_i_aluno Código do aluno
     * @param int $ed11_i_codigo Código da série
     * @param int $ed60_i_turma Código da turma
     * @return mixed Código ou array de códigos do diário
     */
    public function retornaCodDiario($ed60_i_codigo, $ed60_i_aluno, $ed11_i_codigo, $ed60_i_turma, $retornaArray = false)
    {
        $sql = "SELECT diario.*, ed59_i_codigo, ed59_i_disciplina
                FROM diario
                INNER JOIN aluno ON ed47_i_codigo = ed95_i_aluno
                INNER JOIN matricula ON ed60_i_aluno = ed47_i_codigo
                INNER JOIN matriculaserie ON ed60_i_codigo = ed221_i_matricula
                INNER JOIN regencia ON ed59_i_codigo = ed95_i_regencia AND ed59_i_serie = ed221_i_serie
                INNER JOIN disciplina ON ed12_i_codigo = ed59_i_disciplina
                INNER JOIN censodisciplina ON ed265_i_codigo = ed12_i_caddisciplina
                WHERE ed60_i_codigo = {$ed60_i_codigo}
                  AND ed95_i_aluno = {$ed60_i_aluno}
                  AND ed95_i_regencia = ed59_i_codigo
                  AND ed95_i_serie = {$ed11_i_codigo}
                  AND ed59_i_turma = {$ed60_i_turma}
                ORDER BY ed59_i_ordenacao";

        $resultado = pg_fetch_all(pg_query($sql));

        if ($retornaArray) {
            return $resultado ? $resultado : array();
        }

        return $resultado && isset($resultado[0]["ed95_i_codigo"]) ? $resultado[0]["ed95_i_codigo"] : null;
    }

    /**
     * Busca código da regência
     * @param int $ed57_i_codigo Código da turma
     * @return mixed Código ou array de códigos da regência
     */
    public function buscaCodigoRegencia($ed57_i_codigo, $retornaArray = false)
    {
        $sql = pg_query("SELECT ed59_i_codigo, ed59_i_turma, ed59_i_disciplina
                         FROM regencia
                         WHERE ed59_i_turma = {$ed57_i_codigo}");
        $resultado = pg_fetch_all($sql);

        if ($retornaArray) {
            return $resultado ? $resultado : array();
        }

        return $resultado && isset($resultado[0]["ed59_i_codigo"]) ? $resultado[0]["ed59_i_codigo"] : null;
    }

    /**
     * Busca código da regência exatamente como a ATA faz
     * Para EJA anos iniciais, busca a primeira regência da turma para a série com origem 'S'
     * @param int $ed57_i_codigo Código da turma
     * @param int $ed60_i_matricula Código da matrícula
     * @return mixed Código da regência ou null
     */
    public function buscaCodigoRegenciaComoAta($ed57_i_codigo, $ed60_i_matricula)
    {
        // Buscar a série com origem 'S' (Sistema) da matrícula
        $sqlSerie = pg_query("SELECT ms.ed221_i_serie
                             FROM matriculaserie ms
                             WHERE ms.ed221_i_matricula = {$ed60_i_matricula}
                             AND ms.ed221_c_origem = 'S'");

        $resultadoSerie = pg_fetch_assoc($sqlSerie);

        if (!$resultadoSerie) {
            // Fallback: usar o método padrão se não encontrar série com origem 'S'
            return $this->buscaCodigoRegencia($ed57_i_codigo);
        }

        $codigoSerie = $resultadoSerie['ed221_i_serie'];

        // Buscar a primeira regência da turma para a série específica (exatamente como a ata)
        $sqlRegencia = pg_query("SELECT ed59_i_codigo
                                 FROM regencia
                                 WHERE ed59_i_turma = {$ed57_i_codigo}
                                 AND ed59_i_serie = {$codigoSerie}
                                 ORDER BY ed59_i_codigo
                                 LIMIT 1");

        $resultadoRegencia = pg_fetch_assoc($sqlRegencia);

        return $resultadoRegencia ? $resultadoRegencia['ed59_i_codigo'] : null;
    }

    // ================================================================================================
    // FUNÇÕES DE FORMATAÇÃO
    // ================================================================================================

    /**
     * Ajusta formatação da nota
     * @param float $nota Valor da nota
     * @return string Nota formatada
     */
    public function ajustaNota($nota)
    {
        if ($nota === null || $nota === '') {
            return '';
        }

        $nota = (string)$nota;
        if (strlen($nota) == 1 || $nota == "10") {
            $nota = $nota . ".0";
        }
        return $nota;
    }

    /**
     * Formata nota para exibição
     * @param float $nota Valor da nota
     * @param int $casasDecimais Número de casas decimais
     * @return string Nota formatada
     */
    public function formataNota($nota, $casasDecimais = 1)
    {
        if ($nota === null || $nota === '') {
            return '';
        }

        $notaFormatada = number_format($nota, $casasDecimais, ',', '');
        return $notaFormatada;
    }

    /**
     * Calcula a média das notas
     * @param array $notas Array com as notas
     * @return float Média calculada
     */
    public function calculaMedia($notas)
    {
        $soma = 0;
        $contador = 0;

        foreach ($notas as $nota) {
            if ($nota !== null && $nota !== '') {
                $soma += $nota;
                $contador++;
            }
        }

        return $contador > 0 ? $soma / $contador : 0;
    }

    /**
     * Calcula a frequencia do aluno
     * @param int $diasLetivos Total de dias letivos
     * @param int $faltas Total de faltas
     * @return float Percentual de frequencia
     *
     * Autor: Uemerson Santana
     * Data: 02/03/2026
     * Demanda: 18250
     * Razao: Corrigido ordem das operacoes aritmeticas para evitar erro de
     *        precisao de ponto flutuante do IEEE 754. A formula anterior
     *        ((diasLetivos - faltas) / diasLetivos) * 100 produzia valores
     *        como 57.9999999... em vez de 58.0 para certos dados (ex: 200
     *        aulas, 84 faltas), e floor() truncava para 57 ao inves de 58.
     *        Multiplicando por 100 antes de dividir, a aritmetica inteira
     *        preserva a precisao: (116 * 100) / 200 = 58.0 exato.
     */
    public function calculaFrequencia($diasLetivos, $faltas)
    {
        if ($diasLetivos <= 0) {
            return 0;
        }

        $frequencia = (($diasLetivos - $faltas) * 100) / $diasLetivos;
        return floor($frequencia);
    }

    // ================================================================================================
    // FUNÇÕES DE IMPRESSÃO DO PDF
    // ================================================================================================

    /**
     * Imprime cabeçalho dos dados pessoais
     * @param array $dadosAluno Dados do aluno
     * @param array $tiposSanguineos Array com tipos sanguíneos
     */
    public function imprimeDadosPessoais($dadosAluno, $tiposSanguineos)
    {
        $pdf = $this->pdf;

        // Cabeçalho
        $pdf->setfont('arial', 'b', 7);
        $pdf->cell(190, 4, "DADOS PESSOAIS", "LBT", 1, "C", 1);
        $pdf->cell(3, 4, "", "L", 0, "C", 0);

        // Nome
        $pdf->setfont('arial', '', 7);
        $pdf->cell(35, 4, strip_tags($dadosAluno['Led47_v_nome']), 0, 0, "L", 0);
        $pdf->setfont('arial', 'b', 7);
        $pdf->cell(120, 4, $dadosAluno['ed47_v_nome'], 0, 1, "L", 0);

        // Código e INEP
        $pdf->cell(3, 4, "", "L", 0, "C", 0);
        $pdf->setfont('arial', '', 7);
        $pdf->cell(35, 4, strip_tags($dadosAluno['Led47_i_codigo']), 0, 0, "L", 0);
        $pdf->setfont('arial', 'b', 7);
        $pdf->cell(20, 4, $dadosAluno['ed47_i_codigo'], 0, 0, "L", 0);
        $pdf->setfont('arial', '', 7);
        $pdf->cell(30, 4, strip_tags($dadosAluno['Led47_c_codigoinep']), 0, 0, "R", 0);
        $pdf->setfont('arial', 'b', 7);
        $pdf->cell(20, 4, $dadosAluno['ed47_c_codigoinep'], 0, 0, "L", 0);
        $pdf->setfont('arial', '', 7);
        $pdf->cell(25, 4, strip_tags($dadosAluno['Led47_c_nis']), 0, 0, "R", 0);

        // Foto do aluno (se existir)
        if (trim($dadosAluno['ed47_c_foto'])) {
            $pdf->Image('tmp/' . $dadosAluno['ed47_c_foto'], 170, 43, 25, 25);
        }

        $pdf->setfont('arial', 'b', 7);
        $pdf->cell(25, 4, $dadosAluno['ed47_c_nis'], 0, 1, "L", 0);

        // Data de nascimento, sexo e estado civil
        $pdf->cell(3, 4, "", "L", 0, "C", 0);
        $pdf->setfont('arial', '', 7);
        $pdf->cell(35, 4, strip_tags($dadosAluno['Led47_d_nasc']), 0, 0, "L", 0);
        $pdf->setfont('arial', 'b', 7);
        $pdf->cell(20, 4, db_formatar($dadosAluno['ed47_d_nasc'], 'd'), 0, 0, "L", 0);
        $pdf->setfont('arial', '', 7);
        $pdf->cell(30, 4, strip_tags($dadosAluno['Led47_v_sexo']), 0, 0, "R", 0);
        $pdf->setfont('arial', 'b', 7);

        // Sexo formatado
        $sexo = "NÃO DECLARADO";
        if ($dadosAluno['ed47_v_sexo'] == "M") {
            $sexo = "MASCULINO";
        } elseif ($dadosAluno['ed47_v_sexo'] == "F") {
            $sexo = "FEMININO";
        }
        $pdf->cell(25.5, 4, $sexo, 0, 0, "L", 0);

        // Estado civil
        $pdf->setfont('arial', '', 7);
        $pdf->cell(25, 4, strip_tags($dadosAluno['Led47_i_estciv']), 0, 0, "R", 0);

        $estadoCivil = array(
            1 => "SOLTEIRO",
            2 => "CASADO",
            3 => "VIÚVO",
            4 => "DIVORCIADO"
        );

        $pdf->setfont('arial', 'b', 7);
        $pdf->cell(25, 4, isset($estadoCivil[$dadosAluno['ed47_i_estciv']]) ? $estadoCivil[$dadosAluno['ed47_i_estciv']] : "", 0, 1, "L", 0);

        // Tipo sanguíneo e raça
        $pdf->cell(3, 4, "", "L", 0, "C", 0);
        $pdf->setfont('arial', '', 7);
        $pdf->cell(35, 4, strip_tags($dadosAluno['Led47_tiposanguineo']), 0, 0, "L", 0);
        $pdf->setfont('arial', 'b', 7);
        $tipoSang = $dadosAluno['ed47_tiposanguineo'] == "" ? "NÃO INFORMADO" : $tiposSanguineos[$dadosAluno['ed47_tiposanguineo']];
        $pdf->cell(66, 4, $tipoSang, 0, 0, "L", 0);
        $pdf->setfont('arial', '', 7);
        $pdf->cell(32, 4, "Raça/Cor:", 0, 0, "R", 0);
        $pdf->setfont('arial', 'b', 7);
        $pdf->cell(25, 4, $dadosAluno['ed47_c_raca'], 0, 1, "L", 0);

        // Filiação e nacionalidade
        $pdf->cell(3, 4, "", "L", 0, "C", 0);
        $pdf->setfont('arial', '', 7);
        $pdf->cell(35, 4, strip_tags($dadosAluno['Led47_i_filiacao']), 0, 0, "L", 0);
        $pdf->setfont('arial', 'b', 7);
        $filiacao = $dadosAluno['ed47_i_filiacao'] == "0" ? "NÃO DECLARADO / IGNORADO" : "PAI E/OU MÃE";
        $pdf->cell(85, 4, $filiacao, 0, 0, "L", 0);
        $pdf->setfont('arial', '', 7);
        $pdf->cell(17, 4, "Nacionalidade: ", 0, 0, "L", 0);
        $pdf->setfont('arial', 'b', 7);
        $pdf->cell(20, 4, $dadosAluno['xnacionalidade'], 0, 1, "L", 0);

        // Pai e naturalidade
        $pdf->cell(3, 4, "", "L", 0, "C", 0);
        $pdf->setfont('arial', '', 7);
        $pdf->cell(35, 4, '', 0, 0, "L", 0);
        $pdf->setfont('arial', 'b', 7);
        $pdf->cell(85, 4, $dadosAluno['ed47_v_pai'], 0, 0, "L", 0);
        $pdf->setfont('arial', '', 7);
        $pdf->cell(17, 4, "Naturalidade: ", 0, 0, "L", 0);
        $pdf->setfont('arial', 'b', 7);
        $pdf->cell(20, 4, $dadosAluno['xnaturalidade'], 0, 1, "L", 0);

        // Mãe e UF
        $pdf->cell(3, 4, "", "L", 0, "C", 0);
        $pdf->setfont('arial', '', 7);
        $pdf->cell(35, 4, '', 0, 0, "L", 0);
        $pdf->setfont('arial', 'b', 7);
        $pdf->cell(85, 4, $dadosAluno['ed47_v_mae'], 0, 0, "L", 0);

        // Busca UF
        $res = db_query("SELECT ed261_i_censouf FROM censomunic WHERE ed261_i_codigo = {$dadosAluno['ed47_i_censomunicnat']}");
        if ($res && pg_num_rows($res) > 0) {
            $resUf = db_fieldsmemory($res, 0);
            $ufSql = "SELECT ed260_c_sigla FROM censouf WHERE ed260_i_codigo = {$resUf->ed261_i_censouf}";
            $rsuf = db_query($ufSql);
            if ($rsuf && pg_num_rows($rsuf) > 0) {
                $ufData = db_fieldsmemory($rsuf, 0);
                $pdf->setfont('arial', '', 7);
                $pdf->cell(20, 4, 'UF/Nascimento: ', 0, 0, "L", 0);
                $pdf->setfont('arial', 'b', 7);
                $pdf->cell(15, 4, $ufData->ed260_c_sigla, 0, 1, "L", 0);
            } else {
                $pdf->cell(35, 4, '', 0, 1, "L", 0);
            }
        } else {
            $pdf->cell(35, 4, '', 0, 1, "L", 0);
        }

        // Responsável
        $pdf->cell(3, 4, "", "L", 0, "C", 0);
        $pdf->setfont('arial', '', 7);
        $pdf->cell(35, 4, strip_tags($dadosAluno['Led47_c_nomeresp']), 0, 0, "L", 0);
        $pdf->setfont('arial', 'b', 7);
        $pdf->cell(120, 4, $dadosAluno['ed47_c_nomeresp'], 0, 1, "L", 0);

        // Email responsável
        $pdf->cell(3, 4, "", "L", 0, "C", 0);
        $pdf->setfont('arial', '', 7);
        $pdf->cell(35, 4, strip_tags($dadosAluno['Led47_c_emailresp']), 0, 0, "L", 0);
        $pdf->setfont('arial', 'b', 7);
        $pdf->cell(120, 4, $dadosAluno['ed47_c_emailresp'], 0, 1, "L", 0);

        // Linhas de fechamento
        $pdf->line(200, 35, 200, 75);
        $pdf->line(10, 75, 200, 75);
    }

    /**
     * Imprime dados da educação infantil
     * @param array $dados Dados da matrícula e aluno
     */
    public function imprimeEducacaoInfantil($dados)
    {
        $pdf = $this->pdf;

        // Cabeçalho
        $pdf->cell(45, 4, "", 0, 0, "C", 0);
        $pdf->cell(90, 4, "EDUCAÇÃO INFANTIL", "LTR", 1, "C", 0);
        $pdf->cell(45, 4, "", 0, 0, "C", 0);
        $pdf->cell(90, 4, "Etapa: {$dados['ed11_c_descr']}       Ano Letivo: {$dados['ed52_i_ano']}       Nome da Turma: {$dados['ed57_c_descr']}", "LRB", 1, "C", 0);
        $pdf->cell(45, 4, "", 0, 0, "C", 0);
        $pdf->cell(65, 4, "Dias Letivos", "LB", 0, "C", 0);
        $pdf->cell(25, 4, "Faltas do Aluno", "LRB", 1, "C", 0);

        // Dados dos trimestres
        $xdl = 0;
        $xfa = 0;
        $isTransferido = (
            $dados['ed60_c_situacao'] == 'TRANSFERIDO FORA' ||
            $dados['ed60_c_situacao'] == 'TRANSFERIDO REDE' ||
            $dados['ed60_c_situacao'] == 'EVADIDO'
        );

        if (count($dados['xdiario']) == 3) {
            for ($i = 0; $i < 3; $i++) {
                $pdf->cell(45, 4, "", 0, 0, "C", 0);
                $pdf->cell(32.5, 4, ($i + 1) . "ª Trimestre", "LB", 0, "C", 0);

                if ($isTransferido) {
                    $pdf->cell(32.5, 4, "", "LB", 0, "C", 0);
                    $faltas = isset($dados['xdiario'][$i]["numero_faltas"]) ? $dados['xdiario'][$i]["numero_faltas"] : "";
                    $pdf->cell(25, 4, $faltas > 0 ? $faltas : "", "LRB", 1, "C", 0);
                } else {
                    $aulasDadas = isset($dados['xaulas'][$i]["ed78_i_aulasdadas"]) ? $dados['xaulas'][$i]["ed78_i_aulasdadas"] : 0;
                    $pdf->cell(32.5, 4, $aulasDadas, "LB", 0, "C", 0);

                    $faltas = isset($dados['xdiario'][$i]["numero_faltas"]) ? $dados['xdiario'][$i]["numero_faltas"] : 0;
                    $pdf->cell(25, 4, $faltas > 0 ? $faltas : "", "LRB", 1, "C", 0);

                    $xdl += $aulasDadas;
                    $xfa += $faltas;
                }
            }

            // Total
            $pdf->cell(45, 4, "", 0, 0, "C", 0);
            $pdf->cell(32.5, 4, "TOTAL", "LB", 0, "C", 0);
            $pdf->cell(32.5, 4, $xdl, "LB", 0, "C", 0);
            $pdf->cell(25, 4, $xfa > 0 ? $xfa : "", "LRB", 1, "C", 0);

        // Frequência (exibir % somente após encerramento)
            $pdf->cell(45, 4, "", 0, 0, "C", 0);
        /**
         * Autor: Uemerson Santana
         * Data: 30/10/2025
         * Demanda: 17412
         * razao: Ocultar apenas a porcentagem de frequência até o encerramento,
         *        mantendo faltas visíveis e exceções (transferido/evadido).
         */
        $pdf->cell(75, 4, "FREQUÊNCIA % ", "LB", 0, "R", 0);
            if ($isTransferido) {
                $pdf->cell(15, 4, "", "RB", 1, "L", 0); // Vazio para transferidos
            } else {
                $freq = $this->calculaFrequencia($xdl, $xfa);
            $pdf->cell(15, 4, ($dados['lEncerrado'] ? $freq : ''), "RB", 1, "L", 0);
            }

            // Após fechar a tabela completamente, adicionar mensagem de transferência
            if ($isTransferido && isset($dados['ed60_d_datasaida'])) {
                $datasaida = substr($dados['ed60_d_datasaida'], 8, 2) . '/' .
                            substr($dados['ed60_d_datasaida'], 5, 2) . '/' .
                            substr($dados['ed60_d_datasaida'], 0, 4);

                $pdf->ln(2); // Espaço após tabela
                $pdf->cell(45, 4, "", 0, 0, "C", 0);
                /**
                 * Autor: Uemerson Santana
                 * Data: 02/03/2026
                 * Demanda: 18250
                 * Razao: Exibir a situacao real do aluno (EVADIDO, TRANSFERIDO, etc.)
                 *        ao inves de sempre exibir "Aluno transferido em:".
                 */
                if (substr($dados['ed60_c_situacao'], 0, 11) == 'TRANSFERIDO') {
                    $pdf->cell(90, 4, "Aluno transferido em: " . $datasaida, 0, 1, "L", 0);
                } else {
                    $pdf->cell(90, 4, $dados['ed60_c_situacao'] . " em: " . $datasaida, 0, 1, "L", 0);
                }
            }
        }
    }

    /**
     * Imprime dados do primeiro ano fundamental
     * @param array $dados Dados da matrícula e aluno
     */
    public function imprimePrimeiroAno($dados)
    {
        $pdf = $this->pdf;

        // Cabeçalho
        $pdf->cell(45, 4, "", 0, 0, "C", 0);
        $pdf->cell(90, 4, "Ensino Fundamental - 1º ano", "LTR", 1, "C", 0);
        $pdf->cell(45, 4, "", 0, 0, "C", 0);
        $pdf->cell(90, 4, "Etapa: {$dados['ed11_c_descr']}       Ano Letivo: {$dados['ed52_i_ano']}       Nome da Turma: {$dados['ed57_c_descr']}", "LRB", 1, "C", 0);
        $pdf->cell(45, 4, "", 0, 0, "C", 0);
        $pdf->cell(65, 4, "Dias Letivos", "LB", 0, "C", 0);
        $pdf->cell(25, 4, "Faltas do Aluno", "LRB", 1, "C", 0);

        $xdl = 0;
        $xfa = 0;
        $xconta = 1;
        $xindice = 0;
        $isTransferido = ($dados['ed60_c_situacao'] == 'TRANSFERIDO FORA' || $dados['ed60_c_situacao'] == 'TRANSFERIDO REDE');

        foreach ($dados['dadosgrade'] as $linhona) {
            foreach ($linhona["xdiario"] as $linha) {
                $pdf->cell(45, 4, "", 0, 0, "C", 0);
                $pdf->cell(32.5, 4, $xconta . "ª Trimestre", "LB", 0, "C", 0);

                if ($isTransferido) {
                    $pdf->cell(32.5, 4, "", "LB", 0, "C", 0);
                    $faltas = isset($linhona["xdiario"][$xindice]["numero_faltas"]) ? $linhona["xdiario"][$xindice]["numero_faltas"] : 0;
                    $pdf->cell(25, 4, $faltas > 0 ? $faltas : "", "LRB", 1, "C", 0);
                } else {
                    $aulasDadas = isset($linhona["xaulas"][$xindice]["ed78_i_aulasdadas"]) ? $linhona["xaulas"][$xindice]["ed78_i_aulasdadas"] : 0;
                    $pdf->cell(32.5, 4, $aulasDadas, "LB", 0, "C", 0);

                    $faltas = isset($linhona["xdiario"][$xindice]["numero_faltas"]) ? $linhona["xdiario"][$xindice]["numero_faltas"] : 0;
                    $pdf->cell(25, 4, $faltas > 0 ? $faltas : "", "LRB", 1, "C", 0);

                    $xdl += $aulasDadas;
                    $xfa += $faltas;
                }

                $xconta++;
                $xindice++;
            }

            // Total
            $pdf->cell(45, 4, "", 0, 0, "C", 0);
            $pdf->cell(32.5, 4, "TOTAL", "LB", 0, "C", 0);

            if ($isTransferido) {
                $pdf->cell(32.5, 4, "", "LB", 0, "C", 0);
                $pdf->cell(25, 4, "", "LRB", 1, "C", 0);
            } else {
                $pdf->cell(32.5, 4, $xdl, "LB", 0, "C", 0);
                $pdf->cell(25, 4, $xfa > 0 ? $xfa : "", "LRB", 1, "C", 0);
            }

            // Frequência e resultado
            $pdf->cell(45, 4, "", 0, 0, "C", 0);
            $this->imprimeFrequenciaResultado($xdl, $xfa, $dados, $isTransferido);
        }
        /**
         * Autor: Uemerson Santana
         * Data: 06/02/2026
         * Demanda: 18250
         * Razao: Exibir resultado final no padrao de todas as turmas,
         *        com o texto A vistas dos Resultados abaixo do quadro.
         *        Antes do encerramento exibe EM ANDAMENTO.
         *        Prioriza resultado oficial do encerramento (diarioalunoresultadofinal).
         */
        if ($dados['ed60_c_situacao'] == 'MATRICULADO') {
            if (isset($dados['lEncerrado']) && $dados['lEncerrado']) {
                $resultado = isset($dados['resultadofinal']) ? strtoupper($dados['resultadofinal']) : "EM ANDAMENTO";
            } else {
                $resultado = "EM ANDAMENTO";
            }
            $pdf->cell(5, 4, "", 0, 0, "C", 0);
            $pdf->cell(180.2, 4, "      À vistas dos Resultados Obtidos, o aluno foi considerado: " . $resultado, 1, 0, "L", 0);
            $pdf->cell(4.7, 4, "", "R", 1, "", 0);
        }
    }

    /**
     * Imprime dados da EJA
     * @param array $dados Dados da matrícula e aluno
     * @param string $tipo Tipo de EJA (basico ou ciclo)
     */
    public function imprimeEJA($dados, $tipo = 'basico')
    {
        $pdf = $this->pdf;

        if ($tipo == 'basico') {
            $this->imprimeEJABasico($dados);
        } else {
            $this->imprimeEJACiclo($dados);
        }
    }

    /**
     * Imprime EJA básico de alfabetização
     *
     * Força 100% frequência para alunos matriculados em CIC BÁS DE ALFABET
     * e verifica situação real do aluno antes de definir resultado final.
     *
     * @param array $dados Dados da matrícula
     */
    private function imprimeEJABasico($dados)
    {
        $pdf = $this->pdf;

        // Cabeçalho
        $pdf->cell(45, 4, "", "", 0, "C", 0);
        $pdf->cell(96, 4, "EJA - Educação de Jovens e Adultos", "LTR", 1, "C", 0);
        $pdf->cell(45, 4, "", "", 0, "C", 0);
        $pdf->cell(96, 4, "Etapa: {$dados['ejaturma']}       Ano Letivo: {$dados['ed52_i_ano']}       Nome da Turma: {$dados['ed57_c_descr']}", "LRB", 1, "C", 0);
        $pdf->cell(45, 4, "", "", 0, "C", 0);
        $pdf->cell(32, 4, "FALTAS", "LRB", 0, "C", 0);
        $pdf->cell(32, 4, "FREQUENCIA%", "LRB", 0, "C", 0);
        $pdf->cell(32, 4, "RESULTADO", "LRB", 1, "C", 0);

        // Calcula totais - CORREÇÃO APLICADA
        $xdl = 0;
        $xfa = 0;

        $isTransferido = ($dados['ed60_c_situacao'] == 'TRANSFERIDO FORA' || $dados['ed60_c_situacao'] == 'TRANSFERIDO REDE');
        $isEvadido = ($dados['ed60_c_situacao'] == 'EVADIDO');

        // Para alunos matriculados em CIC BÁS DE ALFABET, forçar 100% frequência para consistência com ATA
        if ($dados['ed60_c_situacao'] == 'MATRICULADO') {
            $xdl = 200; // Valor padrão EJA
            $xfa = 0;   // Zero faltas = 100% frequência
        } else {
            // Cálculo manual para casos especiais (transferidos/evadidos)
            $xindice = 0;
            foreach ($dados['xdiario'] as $linha) {
                $xdl += isset($dados['xaulas'][$xindice]["ed78_i_aulasdadas"]) ? $dados['xaulas'][$xindice]["ed78_i_aulasdadas"] : 0;
                $xfa += isset($dados['xdiario'][$xindice]["numero_faltas"]) ? $dados['xdiario'][$xindice]["numero_faltas"] : 0;
                $xindice++;
            }
        }

        $xfreq = $this->calculaFrequencia($xdl, $xfa);

        // Correção do resultado final - verificar situação do aluno primeiro
        $resultadoFinal = '';
        if ($dados['ed60_c_situacao'] != 'MATRICULADO') {
            // Alunos evadidos/transferidos mostram sua situação real
            $resultadoFinal = strtoupper($dados['ed60_c_situacao']);
        } else {
            // Alunos matriculados usam resultado acadêmico
            $resultadoFinal = strtoupper($dados['resultadofinal']);
        }

        $pdf->cell(45, 4, "", "", 0, "C", 0);

        if ($isTransferido || $isEvadido) {
            // Transferidos/evadidos: mostrar faltas se houver, sem frequência, com situação real
            $pdf->cell(32, 4, $xfa > 0 ? $xfa : "", "LRB", 0, "C", 0);
            $pdf->cell(32, 4, "", "RB", 0, "C", 0);
            $pdf->cell(32, 4, $resultadoFinal, "LRB", 1, "C", 0);
        } else {
            // Matriculados: mostrar faltas, frequência e resultado acadêmico
            $pdf->cell(32, 4, $xfa > 0 ? $xfa : "", "LRB", 0, "C", 0);
            /**
             * Autor: Uemerson Santana
             * Data: 30/10/2025
             * Demanda: 17412
             * razao: Ocultar apenas a porcentagem de frequência até o encerramento.
             */
            $pdf->cell(32, 4, ($dados['lEncerrado'] && $xfreq > 0) ? str_replace(".", ",", $xfreq) : "", "RB", 0, "C", 0);
            $pdf->cell(32, 4, $resultadoFinal, "LRB", 1, "C", 0);
        }
    }

    /**
     * Imprime EJA Ciclos (1º e 2º)
     * @param array $dados Dados da matrícula
     */
    private function imprimeEJACiclo($dados)
    {
        $pdf = $this->pdf;

        // Cabeçalho
        $pdf->cell(190, 4, "EJA - Educação de Jovens e Adultos", "LTR", 1, "C", 0);
        $pdf->cell(190, 4, "Etapa: {$dados['ejaturma']}       Ano Letivo: {$dados['ed52_i_ano']}       Nome da Turma: {$dados['ed57_c_descr']}", "LRB", 1, "C", 0);
        $pdf->cell(190, 4, "", "LR", 1, "C", 0);

        $guardaY = $pdf->getY();

        // Grade de notas
        $pdf->cell(5, 4, "", "L", 0, "C", 0);
        $pdf->cell(83, 4, "Apuração dos Rendimentos", "LTBR", 1, "C", 0);
        $pdf->cell(5, 4, "", "L", 0, "C", 0);
        $pdf->cell(33, 4, "Componentes Curriculares", "LB", 0, "C", 0);
        $pdf->cell(10, 4, "1º BIM", "LB", 0, "C", 0);
        $pdf->cell(10, 4, "2º BIM", "LB", 0, "C", 0);
        $pdf->cell(10, 4, "3º BIM", "LB", 0, "C", 0);
        $pdf->cell(10, 4, "4º BIM", "LB", 0, "C", 0);
        $pdf->cell(10, 4, "MÉDIA", "LBR", 1, "C", 0);

        // Imprime disciplinas e notas
        $temNecessidadesEspeciais = $this->isAlunoComNecessidadesEspeciais($dados['ed60_i_aluno']);
        $temParecer = isset($dados['ed60_c_parecer']) && $dados['ed60_c_parecer'] == 'S';

        foreach ($dados['dadosgrade2'] as $linha) {
            $this->imprimeLinhaDisciplina($linha, $temNecessidadesEspeciais && $temParecer);
        }

        $pdf->setY($pdf->getY() - 2);
        $pdf->cell(1, 16, "", "L", 0, "C", 0);
        $pdf->setY($guardaY - 16);

        // Faltas e frequência
        $this->imprimeFaltasFrequenciaEJA($dados);

        // Resultado final
        $this->imprimeResultadoFinalEJA($dados);
    }

    /**
     * Imprime linha de disciplina na grade
     * @param array $linha Dados da disciplina
     * @param bool $temParecer Se aluno tem parecer descritivo
     */
    private function imprimeLinhaDisciplina($linha, $temParecer)
    {
        $pdf = $this->pdf;

        $nomeDisciplina = isset($linha["disciplina"]) ? $linha["disciplina"] : "";

        // Ajusta nome da disciplina se necessário
        if ($nomeDisciplina == "ESTUDO DA SOCIEDADE E DA NATUREZA") {
            $nomeDisciplina = "ESTUDO DA SOCIEDADE";
            $complemento = "E DA NATUREZA";
        } else {
            $complemento = null;
        }

        if ($temParecer) {
            // Aluno com parecer descritivo
            $this->imprimeLinhaParecer($nomeDisciplina, $complemento);
        } else {
            // Aluno com notas
            $this->imprimeLinhaNotas($linha, $nomeDisciplina, $complemento);
        }
    }

    /**
     * Imprime linha com parecer descritivo
     * @param string $disciplina Nome da disciplina
     * @param string $complemento Complemento do nome (se houver)
     */
    private function imprimeLinhaParecer($disciplina, $complemento = null)
    {
        $pdf = $this->pdf;

        $pdf->cell(5, 4, "", "L", 0, "C", 0);
        $pdf->cell(33, 4, $disciplina, $complemento ? "L" : "LB", 0, "L", 0);
        $pdf->cell(10, 4, "PD", $complemento ? "L" : "LB", 0, "C", 0);
        $pdf->cell(10, 4, "PD", $complemento ? "L" : "LB", 0, "C", 0);
        $pdf->cell(10, 4, "PD", $complemento ? "L" : "LB", 0, "C", 0);
        $pdf->cell(10, 4, "PD", $complemento ? "L" : "LB", 0, "C", 0);
        $pdf->cell(10, 4, "PD", $complemento ? "LR" : "LBR", 1, "C", 0);

        if ($complemento) {
            $pdf->cell(5, 4, "", "L", 0, "C", 0);
            $pdf->cell(33, 4, $complemento, "LB", 0, "L", 0);
            $pdf->cell(10, 4, "PD", "LB", 0, "C", 0);
            $pdf->cell(10, 4, "PD", "LB", 0, "C", 0);
            $pdf->cell(10, 4, "PD", "LB", 0, "C", 0);
            $pdf->cell(10, 4, "PD", "LB", 0, "C", 0);
            $pdf->cell(10, 4, "PD", "LBR", 1, "C", 0);
        }
    }

    /**
     * Imprime linha com notas
     * @param array $linha Dados das notas
     * @param string $disciplina Nome da disciplina
     * @param string $complemento Complemento do nome (se houver)
     */
    private function imprimeLinhaNotas($linha, $disciplina, $complemento = null)
    {
        $pdf = $this->pdf;

        // Prepara notas
        $notas = array();
        for ($i = 0; $i < 4; $i++) {
            $notas[$i] = isset($linha[$i]["valor_nota"]) ? $linha[$i]["valor_nota"] : null;
        }

        // Primeira linha (disciplina principal ou parte 1)
        $pdf->cell(5, 4, "", "L", 0, "C", 0);
        $pdf->cell(33, 4, $disciplina, $complemento ? "L" : "LB", 0, "L", 0);

        if ($complemento) {
            // Se tem complemento, primeira linha vazia
            $pdf->cell(10, 4, "", "L", 0, "L", 0);
            $pdf->cell(10, 4, "", "L", 0, "L", 0);
            $pdf->cell(10, 4, "", "L", 0, "L", 0);
            $pdf->cell(10, 4, "", "L", 0, "L", 0);
            $pdf->cell(10, 4, "", "LR", 1, "L", 0);

            // Segunda linha com complemento
            $pdf->cell(5, 4, "", "L", 0, "C", 0);
            $pdf->cell(33, 4, $complemento, "LB", 0, "L", 0);
        }

        // Imprime as notas
        for ($i = 0; $i < 4; $i++) {
            $nota = $this->ajustaNota($notas[$i]);

            // Destaca nota abaixo de 5
            if ($notas[$i] !== null && $notas[$i] < 5) {
                $pdf->setfont('arial', 'b', 7);
            } else {
                $pdf->setfont('arial', '', 7);
            }

            $pdf->cell(10, 4, str_replace(".", ",", $nota), "LB", 0, "C", 0);
        }

        // Calcula e imprime média
        $media = $this->calculaMedia($notas);
        $mediaFormatada = $this->formataNota($media);

        $pdf->setfont('arial', 'b', 7);
        $pdf->cell(10, 4, $mediaFormatada, "LBR", 1, "C", 0);
    }

    /**
     * Imprime faltas e frequência da EJA - VERSÃO CORRIGIDA
     *
     * Força 100% frequência para alunos matriculados em EJA Ciclos (1º/2º CICLO)
     * para garantir consistência com a ATA de Resultados Finais.
     *
     * @param array $dados Dados da matrícula
     */
    private function imprimeFaltasFrequenciaEJA($dados)
    {
        $pdf = $this->pdf;

        if (count($dados['xdiario']) != 4) {
            return;
        }

        $pdf->ln();
        $pdf->cell(95, 4, "", 0, 0, "C", 0);
        $pdf->cell(65, 4, "Dias Letivos", "TLB", 0, "C", 0);
        $pdf->cell(25, 4, "Faltas do Aluno", "TLRB", 0, "C", 0);
        $pdf->cell(5, 4, "", "R", 1, "C", 0);

        $isTransferido = ($dados['ed60_c_situacao'] == 'TRANSFERIDO FORA' || $dados['ed60_c_situacao'] == 'TRANSFERIDO REDE');

        // ===== CÁLCULO REAL DAS FALTAS E AULAS (baseado no arquivo original) =====
        $xdl = 0; // Total de dias letivos
        $cxt = 0; // Total de faltas do aluno

        // Coletando faltas reais por bimestre (como no arquivo original)
        $pfaltas = array();
        if (isset($dados['dadosgrade2'][0])) {
            foreach ($dados['dadosgrade2'][0] as $lx) {
                $pfaltas[] = isset($lx["numero_faltas"]) ? $lx["numero_faltas"] : 0;
            }
        }

        if (!$isTransferido) {
            // Calcular aulas dadas por período
            if (isset($dados['xaulas'])) {
                foreach ($dados['xaulas'] as $aula) {
                    $xdl += isset($aula["ed78_i_aulasdadas"]) ? $aula["ed78_i_aulasdadas"] : 0;
                }
            }

            // Calcular total de faltas reais
            $cxt = array_sum($pfaltas);
        }
        // ===== FIM DO CÁLCULO REAL =====

        // Imprime faltas por bimestre (como no arquivo original)
        for ($i = 0; $i < 4; $i++) {
            $pdf->cell(95, 4, "", 0, 0, "C", 0);
            $pdf->cell(32.5, 4, ($i + 1) . "º Bimestre", "LB", 0, "C", 0);

            if ($isTransferido) {
                $pdf->cell(32.5, 4, "", "LB", 0, "C", 0);
                $pdf->cell(25, 4, "", "LRB", 0, "C", 0);
            } else {
                // Aulas dadas por bimestre
                $aulasPorBimestre = isset($dados['xaulas'][$i]["ed78_i_aulasdadas"]) ?
                                   $dados['xaulas'][$i]["ed78_i_aulasdadas"] : 0;

                // Faltas reais por bimestre
                $faltasPorBimestre = isset($pfaltas[$i]) ? $pfaltas[$i] : 0;

                $pdf->cell(32.5, 4, $aulasPorBimestre, "LB", 0, "C", 0);

                // Exibir faltas só se houver faltas (como no arquivo original)
                if ($faltasPorBimestre == 0 || $faltasPorBimestre == null || $faltasPorBimestre == "") {
                    $pdf->cell(25, 4, '', "LRB", 0, "C", 0);
                } else {
                    $pdf->cell(25, 4, $faltasPorBimestre, "LRB", 0, "C", 0);
                }
            }

            $pdf->cell(5, 4, "", "R", 1, "C", 0);
        }

        // Total
        $pdf->cell(95, 4, "", 0, 0, "C", 0);
        $pdf->cell(32.5, 4, "TOTAL", "LB", 0, "C", 0);

        if ($isTransferido) {
            $pdf->cell(32.5, 4, "", "LB", 0, "C", 0);
            $pdf->cell(25, 4, "", "LRB", 0, "C", 0);
        } else {
            $pdf->cell(32.5, 4, $xdl, "LB", 0, "C", 0);
            $pdf->cell(25, 4, $cxt > 0 ? $cxt : "", "LRB", 0, "C", 0);
        }

        $pdf->cell(5, 4, "", "R", 1, "C", 0);

        // Frequência (exibir % somente após encerramento)
        $pdf->cell(95, 4, "", 0, 0, "C", 0);

        // CORREÇÃO: Para EJA Anos Iniciais, se não há faltas e dias letivos são 0, usar 100%
        $isEvadido = ($dados['ed60_c_situacao'] == 'EVADIDO');
        if ($cxt == 0 && $xdl == 0 && !$isTransferido && !$isEvadido) {
            $xfreq = 100; // Aluno sem faltas = 100% frequência
        } else {
            // Usar o mesmo método da ATA: GradeAproveitamentoAluno para consistência
            $xfreq = $this->calculaFrequencia($xdl, $cxt); // Fallback padrão
            try {
                if (class_exists('GradeAproveitamentoAluno') && class_exists('RegenciaRepository') && class_exists('MatriculaRepository')) {
                    $iCodigoMatricula = isset($dados['ed60_i_matricula']) ? $dados['ed60_i_matricula'] : null;

                    // CORREÇÃO: Para EJA anos iniciais, usar a mesma lógica da ATA
                    // A ata usa: $codregencia = $aDisciplinas[0]->getCodigo();
                    // Para EJA anos iniciais, sempre usar o método da ata
                    if (!empty($iCodigoMatricula)) {
                        $iCodigoRegencia = $this->buscaCodigoRegenciaComoAta($dados['ed57_i_codigo'], $iCodigoMatricula);
                    } else {
                        $iCodigoRegencia = $this->buscaCodigoRegencia($dados['ed57_i_codigo']);
                    }

                    if (!empty($iCodigoMatricula) && !empty($iCodigoRegencia)) {
                        $oMatricula = MatriculaRepository::getMatriculaByCodigo($iCodigoMatricula);
                        $oRegencia = RegenciaRepository::getRegenciaByCodigo($iCodigoRegencia);
                        if ($oMatricula && $oRegencia) {
                            $oGrade = new GradeAproveitamentoAluno($oMatricula);
                            $oFreq = $oGrade->getDadosFrequenciaDaDiscplina($oRegencia);
                            $xfreq = $oFreq->nPercentualFrequencia;
                        }
                    }
                }
            } catch (Exception $e) {
                // Mantém o cálculo manual como fallback
            }
        }
        /**
         * Autor: Uemerson Santana
         * Data: 30/10/2025
         * Demanda: 17412
         * razao: Exibir a porcentagem de frequência somente após encerramento.
         */
        $pdf->cell(75, 4, "FREQUÊNCIA % ", "LB", 0, "R", 0);

        if ($isTransferido || $isEvadido) {
            $pdf->cell(15, 4, "", "RB", 0, "L", 0);
        } else {
            $pdf->cell(15, 4, ($dados['lEncerrado'] ? str_replace(".", ",", $xfreq) : ''), "RB", 0, "L", 0);
        }

        $pdf->cell(5, 4, "", "R", 1, "C", 0);
        $pdf->cell(190, 4, "", "R", 1, "LR", 0);
    }

    /**
     * Imprime resultado final da EJA
     * @param array $dados Dados da matrícula
     */
    private function imprimeResultadoFinalEJA($dados)
    {
        $pdf = $this->pdf;

        $pdf->cell(5, 4, "", 0, 0, "C", 0);

        if ($dados['ed60_c_situacao'] != 'MATRICULADO') {
            $datasaida = isset($dados['ed60_d_datasaida']) ? $dados['ed60_d_datasaida'] : "";
            if ($datasaida) {
                $datasaidaFormatada = substr($datasaida, 8, 2) . '/' . substr($datasaida, 5, 2) . '/' . substr($datasaida, 0, 4);
                $pdf->cell(4, 4, "", "L", 0, "", 0);

                if (substr($dados['ed60_c_situacao'], 0, 11) == 'TRANSFERIDO') {
                    $pdf->cell(181, 4, "Aluno transferido em: " . $datasaidaFormatada, "b", 0, "LR", 0);
                } else {
                    $pdf->cell(181, 4, $dados['ed60_c_situacao'] . " em: " . $datasaidaFormatada, "b", 0, "LR", 0);
                }

                $pdf->cell(6, 4, "", "R", 1, "", 0);
            }
        } else {
            $resultado = isset($dados['resultadofinal']) ? strtoupper($dados['resultadofinal']) : "EM ANDAMENTO";
            $pdf->cell(180.2, 4, "      À vistas dos Resultados Obtidos, o aluno foi considerado: " . $resultado, 1, 0, "L", 0);
            $pdf->cell(4.7, 4, "", "R", 1, "", 0);
        }

        $pdf->cell(190, 4, "", "RB", 1, "C", 0);
    }

    /**
     * Imprime frequência e resultado
     * @param int $diasLetivos Total de dias letivos
     * @param int $faltas Total de faltas
     * @param array $dados Dados da matrícula
     * @param bool $isTransferido Se aluno foi transferido
     */
    private function imprimeFrequenciaResultado($diasLetivos, $faltas, $dados, $isTransferido)
    {
        $pdf = $this->pdf;

        if ($isTransferido) {
            $pdf->cell(75, 4, "FREQUÊNCIA % ", "LB", 0, "R", 0);
            $pdf->cell(15, 4, "", "RB", 1, "L", 0);

            // Data de transferência
            if (isset($dados['ed60_d_datasaida'])) {
                $datasaida = substr($dados['ed60_d_datasaida'], 8, 2) . '/' .
                           substr($dados['ed60_d_datasaida'], 5, 2) . '/' .
                           substr($dados['ed60_d_datasaida'], 0, 4);

                $pdf->cell(4, 4, "", "", 0, "", 0);
                /**
                 * Autor: Uemerson Santana
                 * Data: 02/03/2026
                 * Demanda: 18250
                 * Razao: Exibir a situacao real do aluno (EVADIDO, TRANSFERIDO, etc.)
                 *        ao inves de sempre exibir "Aluno transferido em:".
                 */
                if (substr($dados['ed60_c_situacao'], 0, 11) == 'TRANSFERIDO') {
                    $pdf->cell(181, 4, "Aluno transferido em: " . $datasaida, "b", 0, "LR", 0);
                } else {
                    $pdf->cell(181, 4, $dados['ed60_c_situacao'] . " em: " . $datasaida, "b", 0, "LR", 0);
                }
            }
        } else {
            $freq = $this->calculaFrequencia($diasLetivos, $faltas);
            /**
             * Autor: Uemerson Santana
             * Data: 30/10/2025
             * Demanda: 17412
             * razao: Ocultar % de frequência antes do encerramento do ano letivo.
             */
            $pdf->cell(75, 4, "FREQUÊNCIA % ", "LB", 0, "R", 0);
            $pdf->cell(15, 4, (isset($dados['lEncerrado']) && $dados['lEncerrado'] ? $freq : ''), "RB", 1, "L", 0);

            // Resultado final (removido conforme demanda 17300)
            // Mantido apenas espaço
            $pdf->cell(6, 4, "", "", 1, "", 0);
        }
    }

    /**
     * Imprime assinaturas
     * @param array $assinaturas Array com dados das assinaturas
     */
    public function imprimeAssinaturas($assinaturas)
    {
        $pdf = $this->pdf;

        /**
         * Autor: Uemerson Santana
         * Data: 30/10/2025
         * Demanda: 17412
         */
        // Mapear CGMs das assinaturas vindos por GET para variáveis locais
        $matridir = isset($_GET['matridir']) ? preg_replace('/\D+/', '', $_GET['matridir']) : '';
        $matrisup = isset($_GET['matrisup']) ? preg_replace('/\D+/', '', $_GET['matrisup']) : '';
        $matrisec = isset($_GET['matrisec']) ? preg_replace('/\D+/', '', $_GET['matrisec']) : '';

        $matridir = (int)$matridir;
        $matrisup = (int)$matrisup;
        $matrisec = (int)$matrisec;

        if (!empty($matrisec)) {
            $sqlSec = "SELECT ed284_i_rhpessoal as matriculasec
                       FROM rechumanopessoal
                       LEFT JOIN rhpessoal ON rhpessoal.rh01_regist = rechumanopessoal.ed284_i_rhpessoal
                       WHERE rh01_numcgm = {$matrisec};";
            $resSec = db_query($sqlSec);
            if (pg_num_rows($resSec) > 0) {

                $dadosSec = db_utils::fieldsMemory($resSec, 0);
                $assinaturas['secretario']['matricula'] = $dadosSec->matriculasec;
            }
        }
        // Supervisor
        if (!empty($matrisup)) {
            $sqlSup = "SELECT ed284_i_rhpessoal as matriculasup
                       FROM rechumanopessoal
                       LEFT JOIN rhpessoal ON rhpessoal.rh01_regist = rechumanopessoal.ed284_i_rhpessoal
                       WHERE rh01_numcgm = {$matrisup}";
            $resSup = db_query($sqlSup);
            if (pg_num_rows($resSup) > 0) {
                $dadosSup = db_utils::fieldsMemory($resSup, 0);
                $assinaturas['supervisor']['matricula'] = $dadosSup->matriculasup;
            }
        }
        // Diretor
        if (!empty($matridir)) {
            $sqlDir = "SELECT ed284_i_rhpessoal as matriculadir
                       FROM rechumanopessoal
                       LEFT JOIN rhpessoal ON rhpessoal.rh01_regist = rechumanopessoal.ed284_i_rhpessoal
                       WHERE rh01_numcgm = {$matridir}";
            $resDir = db_query($sqlDir);
            if (pg_num_rows($resDir) > 0) {
                $dadosDir = db_utils::fieldsMemory($resDir, 0);
                $assinaturas['diretor']['matricula'] = $dadosDir->matriculadir;
            }
        }

        $pdf->ln(15);

        // Linha de assinatura
        $pdf->cell(12, 3, '', 0, 0, "L", 0);

        if (!empty($assinaturas['secretario']['nome'])) {
            $pdf->cell(58, 3, '____________________________________', 0, 0, "L", 0);
        } else {
            $pdf->cell(58, 3, '', 0, 0, "L", 0);
        }

        if (!empty($assinaturas['supervisor']['nome'])) {
            $pdf->cell(56, 3, '____________________________________', 0, 0, "L", 0);
        } else {
            $pdf->cell(56, 3, '', 0, 0, "L", 0);
        }

        if (!empty($assinaturas['diretor']['nome'])) {
            $pdf->cell(57, 3, '____________________________________', 0, 1, "L", 0);
        } else {
            $pdf->cell(57, 3, '', 0, 1, "L", 0);
        }

        // Nomes
        $pdf->cell(13, 3, '', 0, 0, '', 0);

        foreach (array('secretario', 'supervisor', 'diretor') as $cargo) {
            $largura = ($cargo == 'diretor') ? 64 : ($cargo == 'supervisor' ? 57 : 58);

            if (!empty($assinaturas[$cargo]['nome'])) {
                $nome = $assinaturas[$cargo]['nome'];
                // Tratamento especial para caracteres
                if (strpos($nome, 'Ô') !== false) {
                    $pdf->cell($largura, 3, $nome, 0, 0, "L", 0);
                } else {
                    $pdf->cell($largura, 3, utf8_decode($nome), 0, 0, "L", 0);
                }
            } else {
                $pdf->cell($largura, 3, '', 0, 0, "L", 0);
            }
        }
        $pdf->ln();

        // Cargos
        $pdf->cell(13, 3, '', 0, 0, '', 0);

        $cargosLabel = array(
            'secretario' => 'SECRETÁRIO DE ESCOLA',
            'supervisor' => 'SUPERVISOR ESCOLAR',
            'diretor' => 'DIRETOR'
        );

        foreach (array('secretario', 'supervisor', 'diretor') as $cargo) {
            $largura = ($cargo == 'diretor') ? 64 : ($cargo == 'supervisor' ? 57 : 58);

            if (!empty($assinaturas[$cargo]['nome'])) {
                $pdf->cell($largura, 3, $cargosLabel[$cargo], 0, 0, "L", 0);
            } else {
                $pdf->cell($largura, 3, '', 0, 0, "L", 0);
            }
        }
        $pdf->ln();

        // Matrículas
        $pdf->cell(13, 3, '', 0, 0, '', 0);

        /**
         * Autor: Uemerson Santana
         * Data: 27/11/2025
         * Demanda: 17982
         * Razão: Removido substr que removia os 2 primeiros dígitos da matrícula na exibição, exibindo agora a matrícula completa.
         */
        foreach (array('secretario', 'supervisor', 'diretor') as $cargo) {
            $largura = ($cargo == 'diretor') ? 64 : ($cargo == 'supervisor' ? 57 : 58);

            if (!empty($assinaturas[$cargo]['matricula'])) {
                // Exibe a matrícula completa vinculada à escola (sem remover prefixo)
                $matricula = $assinaturas[$cargo]['matricula'];
                $pdf->cell($largura, 3, 'Matrícula: ' . $matricula, 0, 0, "L", 0);
            } else {
                $pdf->cell($largura, 3, '', 0, 0, "L", 0);
            }
        }
        $pdf->ln();
    }
}

// ================================================================================================
// FUNÇÕES AUXILIARES GLOBAIS
// ================================================================================================

/**
 * Função auxiliar para debug
 * @param mixed $var Variável a ser exibida
 */
function testa($var)
{
    echo "<pre>";
    print_r($var);
    echo "</pre>";
}

/**
 * Busca resultado final usando a estrutura OOP nova (mesma lógica da tela de encerramento)
 * Mesma lógica usada pela tela de Encerramento / Cancelamento de Avaliações e ATA
 *
 * @author Uemerson Santana
 * @date 18/11/2025
 * @demanda 17412
 *
 * @param int $iCodigoMatricula Código da matrícula
 * @param int $iCodigoEnsino Código do ensino
 * @param int $iAnoCalendario Ano do calendário
 * @return string Resultado final formatado ou string vazia
 */
function buscaResultadoFinalEncerramento($iCodigoMatricula, $iCodigoEnsino, $iAnoCalendario)
{
    try {
        if (!class_exists('MatriculaRepository')) {
            return '';
        }

        $oMatricula = MatriculaRepository::getMatriculaByCodigo($iCodigoMatricula);
        if (!$oMatricula) {
            return '';
        }

        // Se não está matriculado, retorna a situação
        if ($oMatricula->getSituacao() != 'MATRICULADO') {
            return strtoupper($oMatricula->getSituacao());
        }

        /**
         * Autor: Uemerson Santana
         * Data: 18/11/2025
         * Demanda: 17412
         * Razão: Verificar aprovação pelo conselho ANTES de buscar o resultado final,
         *        garantindo que alunos aprovados pelo conselho sempre apareçam como APROVADO,
         *        mesmo que tenham reprovação por frequência em alguma disciplina.
         */
        // SEMPRE verificar se há aprovação pelo conselho PRIMEIRO
        // Aprovação pelo conselho sobrescreve qualquer resultado (exceto reclassificação por baixa frequência)
        $temAprovacaoConselho = false;

        if (class_exists('AprovacaoConselho')) {
            // Método 1: Verificar via OOP
            try {
                $aDisciplinas = $oMatricula->getDiarioDeClasse()->getDisciplinas();

                foreach ($aDisciplinas as $oDisciplina) {
                    if (!$oDisciplina->getRegencia()->isObrigatoria()) {
                        continue;
                    }

                    $oResultadoFinal = $oDisciplina->getResultadoFinal();
                    $oAprovadoConselho = $oResultadoFinal->getFormaAprovacaoConselho();

                    // Se tem aprovação pelo conselho (qualquer tipo exceto reclassificação por baixa frequência)
                    if (!is_null($oAprovadoConselho)) {
                        $iFormaAprovacao = $oAprovadoConselho->getFormaAprovacao();
                        // Tipos que aprovam: 1=APROVADO_CONSELHO, 3=APROVADO_CONFORME_REGIMENTO_ESCOLAR
                        // Tipo que NÃO aprova automaticamente: 2=RECLASSIFICACAO_BAIXA_FREQUENCIA
                        // Usar valor 2 diretamente (RECLASSIFICACAO_BAIXA_FREQUENCIA = 2)
                        if ($iFormaAprovacao != 2) {
                            $temAprovacaoConselho = true;
                            break;
                        }
                    }
                }
            } catch (Exception $e) {
                // Se der erro no método OOP, continua para o fallback
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

        // Se tem aprovação pelo conselho, resultado final é SEMPRE 'A' (independente do resultado calculado)
        if ($temAprovacaoConselho) {
            $resultadoFinal = 'A';
        } else {
            // Só busca o resultado final se NÃO tem aprovação pelo conselho
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
            return '';
        }

        // Verifica progressão parcial para EJA (será tratado após buscar termos)

        // Busca o termo de encerramento para formatar o resultado
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

        return $resultadoFinal;
    } catch (Exception $e) {
        // Em caso de erro, retorna vazio para usar fallback
        return '';
    }
}

/**
 * Retorna o resultado final do aluno
 * @param int $escola Código da escola
 * @param int $etapa Código da etapa
 * @param int $turma Código da turma
 * @param int $aluno Código do aluno
 * @return int 1 se tem resultado final, 0 caso contrário
 */
function resultado_final($escola, $etapa, $turma, $aluno)
{
    global $resfinal;

    $sql = "SELECT ed47_i_codigo as aluno,
                   ed47_v_nome as alunonome,
                   ed18_i_codigo as codigo_escola,
                   ed18_c_nome as nome_escola,
                   ed10_i_codigo as ensino,
                   ed11_i_codigo as codigo_etapa,
                   ed11_c_descr as descricao_etapa,
                   ed57_i_codigo as codigo_turma,
                   ed60_i_codigo as matricula_aluno,
                   ed60_c_situacao as situacao,
                   ed95_i_codigo as diario,
                   ed74_c_resultadofinal as resultado_final,
                   1 as resfinal
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
              AND (ed74_c_resultadofinal <> '' OR ed74_c_resultadofinal <> ' ')
              AND ed52_d_resultfinal <= '" . date('Y-m-d') . "'";

    $rsfinal = db_query($sql);

    if (pg_num_rows($rsfinal) > 0) {
        db_fieldsmemory($rsfinal, 0);
    } else {
        $resfinal = 0;
    }

    return $resfinal;
}

/**
 * Retorna o resultado final 2 (aprovado/reprovado/em andamento)
 * @param int $escola Código da escola
 * @param int $etapa Código da etapa
 * @param int $turma Código da turma
 * @param int $aluno Código do aluno
 * @return string A=Aprovado, R=Reprovado, E=Em Andamento
 */
function resultado_final2($escola, $etapa, $turma, $aluno)
{
    $sql = "SELECT ed74_c_resultadoaprov as resultado_aprov,
                   ed74_c_resultadofreq as frequencia
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
              AND ed52_d_resultfinal <= '" . date('Y-m-d') . "'
              AND (ed74_c_resultadoaprov IS NOT NULL AND ed74_c_resultadoaprov != '')";

    $rsfinal = pg_query($sql);
    $numRows = pg_num_rows($rsfinal);

    if ($numRows == 0) {
        return 'E';
    }

    $notafinal = 'A';

    for ($x = 0; $x < $numRows; $x++) {
        $odados = db_utils::fieldsmemory($rsfinal, $x);

        if ($odados->resultado_aprov == 'R' || $odados->frequencia == 'R') {
            $notafinal = 'R';
            break;
        }
    }

    return $notafinal;
}

/**
 * Verifica situação de transferência do aluno
 * @param int $aluno Código do aluno
 * @return string Situação da matrícula
 */
function transferencia($aluno)
{
    $sql = "SELECT ed60_i_aluno,
                   ed60_d_datasaida,
                   ed60_c_situacao
            FROM matricula
            WHERE ed60_d_datasaida BETWEEN '" . db_getsession("DB_anousu") . "-01-01'
                                        AND '" . db_getsession("DB_anousu") . "-12-31'
              AND ed60_i_aluno = {$aluno}";

    $rstransf = db_query($sql);

    if (pg_num_rows($rstransf) > 0) {
        db_fieldsmemory($rstransf, 0);
        return $ed60_c_situacao;
    }

    return "";
}

// ================================================================================================
// INÍCIO DO PROCESSAMENTO PRINCIPAL
// ================================================================================================

// Variáveis globais
$resfinal = 0;

// Configurações iniciais
$resultedu = eduparametros(db_getsession("DB_coddepto"));
$permitenotaembranco = VerParametroNota(db_getsession("DB_coddepto"));
$escola = db_getsession("DB_coddepto");
$oGet = db_utils::postMemory($_GET);
$obs1 = base64_decode($obs1);


// Instância das classes necessárias
$clmatricula = new cl_matricula;
$claluno = new cl_aluno;
$clturma = new cl_turma;
$clEscola = new cl_escola();
$cldiarioavaliacao = new cl_diarioavaliacao;
$cldiarioresultado = new cl_diarioresultado;
$clregenteconselho = new cl_regenteconselho;
$clregencia = new cl_regencia;
$clrotulo = new rotulocampo;
$oDaoEscolaDiretor = new cl_escoladiretor();
$oDaoTipoSanguineo = new cl_tiposanguineo();
$oDaoAprovConselho = new cl_aprovconselho();

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
$sCamposDiretor .= " case when ed20_i_tiposervidor = 1 then cgmrh.z01_nome else cgmcgm.z01_nome end as nome,";
$sCamposDiretor .= " ed83_c_descr||' n°: '||ed05_c_numero::varchar as descricao,'D' as tipo";
$sWhereDiretor = " ed254_i_escola = {$escola} AND ed254_c_tipo = 'A' AND ed01_i_funcaoadmin = 2 limit 1 ";
$sSqlDiretor = $oDaoEscolaDiretor->sql_query_resultadofinal("", $sCamposDiretor, "", $sWhereDiretor);
$rsDiretor = $oDaoEscolaDiretor->sql_record($sSqlDiretor);
$iLinhasDiretor = $oDaoEscolaDiretor->numrows;

if ($iLinhasDiretor > 0) {
    db_fieldsmemory($result, 0);
    $nome = trim(db_utils::fieldsmemory($rsDiretor, 0)->nome);
} else {
    $nome = "";
}

// Campos para busca de matrículas
$camp = " ed60_d_datasaida as datasaida, ed10_i_codigo as ensino, ";
$camp .= " case ";
$camp .= "   when ed60_c_situacao = 'TRANSFERIDO REDE' then ";
$camp .= "    (select escoladestino.ed18_c_nome from transfescolarede ";
$camp .= "      inner join atestvaga on atestvaga.ed102_i_codigo = transfescolarede.ed103_i_atestvaga ";
$camp .= "      inner join escola as escoladestino on escoladestino.ed18_i_codigo = atestvaga.ed102_i_escola ";
$camp .= "     where ed103_i_matricula = ed60_i_codigo order by ed103_d_data desc limit 1) ";
$camp .= "   when ed60_c_situacao = 'TRANSFERIDO FORA' then ";
$camp .= "    (select escolaproc1.ed82_c_nome from transfescolafora ";
$camp .= "     inner join escolaproc as escolaproc1 on escolaproc1.ed82_i_codigo = transfescolafora.ed104_i_escoladestino ";
$camp .= "     where ed104_i_matricula = ed60_i_codigo order by ed104_d_data desc limit 1) ";
$camp .= "    else null ";
$camp .= "  end as destinosaida, ";
$camp .= "  matricula.*, ";
$camp .= "  turma.ed57_c_descr, ";
$camp .= "  turma.ed57_i_codigo, ";
$camp .= "  turmaserieregimemat.ed220_i_procedimento, ";
$camp .= "  turma.ed57_c_medfreq, ";
$camp .= "  calendario.ed52_c_descr, ";
$camp .= "  calendario.ed52_i_ano, ";
$camp .= "  case when turma.ed57_i_tipoturma = 2 then ";
$camp .= "   fc_nomeetapaturma(ed60_i_turma) else ";
$camp .= "   serie.ed11_c_descr ";
$camp .= "  end as ed11_c_descr, ";
$camp .= "  serie.ed11_c_descr as ejaturma, ";
$camp .= "  serie.ed11_i_codigo, ";
$camp .= "  escola.ed18_c_nome, ";
$camp .= "  turno.ed15_c_nome, ";
$camp .= "  aluno.ed47_v_nome, ";
$camp .= "  alunoprimat.ed76_i_codigo, ";
$camp .= "  alunoprimat.ed76_i_escola, ";
$camp .= "  alunoprimat.ed76_d_data, ";
$camp .= "  alunoprimat.ed76_c_tipo, ";
$camp .= "  case when ed76_c_tipo = 'M' ";
$camp .= "   then escolaprimat.ed18_c_nome else escolaproc.ed82_c_nome end as nomeescola, ";
$camp .= "   aluno.*  ";

// Busca matrículas (ordenado por nome)
$sSqlMatricula = $clmatricula->sql_query("", $camp, "aluno.ed47_v_nome asc", " ed60_i_codigo in ($alunos)");
$result1 = $clmatricula->sql_record($sSqlMatricula);

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

// Verifica se há matrículas
if ($clmatricula->numrows == 0) {
    db_redireciona("db_erros.php?fechar=true&db_erro=Nenhum registro encontrado.");
}

// Cria o PDF
$pdf = new PDF();
$pdf->Open();
$pdf->AliasNbPages();
$pdf->ln(5);
$pdf->imprime_rodape = false;

// Cria instância do relatório auxiliar
$relatorio = new RelatorioFichaIndividualAluno($pdf, $escola, db_getsession("DB_anousu"));

// Processa cada matrícula
db_fieldsmemory($result1, 0);

$head1 = "FICHA INDIVIDUAL";
$head2 = "{$ed47_i_codigo} - {$ed47_v_nome}";

$pdf->Addpage('P');
$pdf->setfillcolor(223);

$u = 0;
$iCodigo = 0;

$anos_iniciais = $ed52_c_descr;
$ejaturmaC = $ejaturma;

// Processa cada aluno
for ($ww = 0; $ww < $clmatricula->numrows; $ww++) {

    db_fieldsmemory($result1, $ww);

    // Prepara dados do aluno
    $dadosAluno = array();
    $campos = $clmatricula->sql_record($sSqlMatricula);

    // Copia todos os campos do resultado para o array
    $dadosAluno = (array)pg_fetch_array($result1, $ww);

    // Adiciona dados formatados
    $dadosAluno['xnacionalidade'] = $relatorio->retornaNacionalidade($ed47_i_nacion);
    $dadosAluno['xnaturalidade'] = $relatorio->retornaNaturalidade($ed47_i_censomunicnat);
    $dadosAluno['ufcenso'] = $relatorio->retornaNaturalidade($ed47_i_censoufnat);
    $dadosAluno['xfotoaluno'] = trim($ed47_c_foto);

    // Labels dos campos (Led47_...)
    $dadosAluno['Led47_v_nome'] = $Led47_v_nome;
    $dadosAluno['Led47_i_codigo'] = $Led47_i_codigo;
    $dadosAluno['Led47_c_codigoinep'] = $Led47_c_codigoinep;
    $dadosAluno['Led47_c_nis'] = $Led47_c_nis;
    $dadosAluno['Led47_d_nasc'] = $Led47_d_nasc;
    $dadosAluno['Led47_v_sexo'] = $Led47_v_sexo;
    $dadosAluno['Led47_i_estciv'] = $Led47_i_estciv;
    $dadosAluno['Led47_tiposanguineo'] = $Led47_tiposanguineo;
    $dadosAluno['Led47_i_filiacao'] = $Led47_i_filiacao;
    $dadosAluno['Led47_c_nomeresp'] = $Led47_c_nomeresp;
    $dadosAluno['Led47_c_emailresp'] = $Led47_c_emailresp;

    // Processa dados específicos por tipo de ensino
    $dadosEnsino = array();

    // EDUCAÇÃO INFANTIL
    if ($ed52_c_descr == "EDUCAÇÃO INFANTIL" || substr($ed52_c_descr, 0, 12) == "ED. INFANTIL") {

        $xcodiario = $relatorio->retornaCodDiario($ed60_i_codigo, $ed60_i_aluno, $ed11_i_codigo, $ed60_i_turma);
        $xdiario = $relatorio->dadosDiario($xcodiario);

        $codreg = $relatorio->buscaCodigoRegencia($ed57_i_codigo);
        $xaulas = $relatorio->dadosAulas($codreg);

        /**
         * Autor: Uemerson Santana
         * Data: 30/10/2025
         * Demanda: 17412
         * razao: Exibir a porcentagem de frequência somente após
         *        o encerramento do ano letivo, preservando faltas visíveis e
         *        exceções já existentes (transferido/evadido).
         */
        $final2Enc = resultado_final2($escola, $ed11_i_codigo, $ed60_i_turma, $ed47_i_codigo);
        $lEncerrado = ($final2Enc != 'E');

        $dadosEnsino = array(
            'tipo' => 'infantil',
            'xdiario' => $xdiario,
            'xaulas' => $xaulas,
            'ed11_c_descr' => $ed11_c_descr,
            'ed52_i_ano' => $ed52_i_ano,
            'ed57_c_descr' => $ed57_c_descr,
            'ed60_c_situacao' => $ed60_c_situacao,
            'ed60_d_datasaida' => isset($datasaida) ? $datasaida : null,
            'resultadofinal' => ResultadoFinal($ed60_i_codigo, $ed47_i_codigo, $ed60_i_turma, trim($ed60_c_situacao), trim($ed60_c_concluida), $ensino),
            'lEncerrado' => $lEncerrado
        );
    }
    // PRIMEIRO ANO FUNDAMENTAL
    elseif (($ed52_c_descr == "EN FUN ANOS INICIAIS" || substr($ed52_c_descr, 0, 13) == "ANOS INICIAIS") && $ed11_c_descr == "1º ANO") {

        $indice = 0;
        $dadosgrade = array();
        $xcodiarioArray = $relatorio->retornaCodDiario($ed60_i_codigo, $ed60_i_aluno, $ed11_i_codigo, $ed60_i_turma, true);
        $codreg = $relatorio->buscaCodigoRegencia($ed57_i_codigo, true);

        foreach ($xcodiarioArray as $linha) {
            $xcodiario = $linha["ed95_i_codigo"];
            $xdiario = $relatorio->dadosDiario($xcodiario);
            $xaulas = $relatorio->dadosAulas($linha["ed95_i_regencia"]);

            if ($xaulas) {
                $dadosgrade[$indice]["xdiario"] = $xdiario;
                $dadosgrade[$indice]["xaulas"] = $xaulas;
                $indice++;
            }
        }

        /**
         * Autor: Uemerson Santana
         * Data: 06/02/2026
         * Demanda: 18250
         * Razao: Priorizar resultado oficial do encerramento (diarioalunoresultadofinal)
         *        antes de usar diariofinal (que pode estar desatualizado apos reclassificacao).
         *        Mesmo padrao aplicado na ATA (edu2_ataresultadofinal002.php).
         */
        $resultadofinal = '';

        // 1. Consultar resultado oficial do encerramento
        if ($ed60_c_situacao != 'MATRICULADO') {
            $resultadofinal = strtoupper($ed60_c_situacao);
        } else {
            $resultadofinal = buscaResultadoFinalEncerramento($ed60_i_codigo, $ensino, $ed52_i_ano);

            // 2. Fallback: resultado_final2 (diariofinal)
            if (empty($resultadofinal)) {
                $final2 = resultado_final2($escola, $ed11_i_codigo, $ed60_i_turma, $ed47_i_codigo);
                if ($final2 == 'A') {
                    $resultadofinal = 'APROVADO';
                } elseif ($final2 == 'R') {
                    $resultadofinal = 'REPROVADO';
                } else {
                    $resultadofinal = 'EM ANDAMENTO';
                }
            }
        }
        /**
         * Autor: Uemerson Santana
         * Data: 30/10/2025
         * Demanda: 17412
         * razao: Exibir a porcentagem de frequência somente após o
         *        encerramento do ano letivo (via resultado_final2), mantendo faltas
         *        e exceções (transferido/evadido).
         */
        $lEncerrado = (resultado_final2($escola, $ed11_i_codigo, $ed60_i_turma, $ed47_i_codigo) != 'E');

        $dadosEnsino = array(
            'tipo' => 'primeiro_ano',
            'dadosgrade' => $dadosgrade,
            'ed11_c_descr' => $ed11_c_descr,
            'ed52_i_ano' => $ed52_i_ano,
            'ed57_c_descr' => $ed57_c_descr,
            'ed60_c_situacao' => $ed60_c_situacao,
            'ed60_d_datasaida' => isset($datasaida) ? $datasaida : null,
            'resultadofinal' => $resultadofinal,
            'lEncerrado' => $lEncerrado
        );
    }
    // EJA BÁSICO
    elseif (($ed52_c_descr == "EJA ANOS INICIAIS" || substr($ed52_c_descr, 0, 12) == "EJA INICIAIS") && $ejaturmaC == "CIC BÁS DE ALFABET") {

        $xcodiario = $relatorio->retornaCodDiario($ed60_i_codigo, $ed60_i_aluno, $ed11_i_codigo, $ed60_i_turma);
        $xdiario = $relatorio->dadosDiario($xcodiario);

        $codreg = $relatorio->buscaCodigoRegencia($ed57_i_codigo);
        $xaulas = $relatorio->dadosAulas($codreg);

        /**
         * Autor: Uemerson Santana
         * Data: 18/11/2025
         * Demanda: 17412
         * Razão: Aplicar busca do resultado da tela de encerramento para EJA Básico,
         *        garantindo que alunos aprovados no sistema apareçam corretamente na ficha.
         */
        $resultadofinal = '';

        // Verificar situação do aluno primeiro
        if ($ed60_c_situacao != 'MATRICULADO') {
            $resultadofinal = strtoupper($ed60_c_situacao);
        } else {
            // Buscar resultado usando a estrutura OOP nova (mesma lógica da tela de encerramento)
            $resultadofinal = buscaResultadoFinalEncerramento($ed60_i_codigo, $ensino, $ed52_i_ano);

            /**
             * Autor: Uemerson Santana
             * Data: 18/11/2025
             * Demanda: 17412
             * Razão: Verificar aprovação pelo conselho ANTES de usar fallback, garantindo que
             *        alunos aprovados pelo conselho sempre apareçam como APROVADO, mesmo que
             *        buscaResultadoFinalEncerramento() retorne vazio ou o fallback indique reprovação.
             */
            // Verificar aprovação pelo conselho diretamente antes de usar fallback
            $temAprovacaoConselhoFallback = false;
            if (class_exists('AprovacaoConselho')) {
                $sSqlConselhoFallback = "SELECT COUNT(*) as total
                                        FROM aprovconselho ac
                                        INNER JOIN diario d ON d.ed95_i_codigo = ac.ed253_i_diario
                                        INNER JOIN regencia r ON r.ed59_i_codigo = d.ed95_i_regencia
                                        WHERE d.ed95_i_aluno = {$ed60_i_aluno}
                                          AND r.ed59_c_condicao = 'OB'
                                          AND ac.ed253_aprovconselhotipo IN (1, 3)";
                $rsConselhoFallback = db_query($sSqlConselhoFallback);
                if ($rsConselhoFallback && pg_num_rows($rsConselhoFallback) > 0) {
                    $oDadosConselhoFallback = db_utils::fieldsMemory($rsConselhoFallback, 0);
                    if ($oDadosConselhoFallback->total > 0) {
                        $temAprovacaoConselhoFallback = true;
                    }
                }
            }

            // Se tem aprovação pelo conselho, resultado é SEMPRE APROVADO (não usar fallback)
            if ($temAprovacaoConselhoFallback) {
                $resultadofinal = 'APROVADO';
            } elseif (empty($resultadofinal)) {
                // Se não encontrou resultado na estrutura nova, usar fallback
                resultado_final($escola, $ed11_i_codigo, $ed60_i_turma, $ed47_i_codigo);
                $sResultadoFinal = ResultadoFinal($ed60_i_codigo, $ed47_i_codigo, $ed60_i_turma, trim($ed60_c_situacao), trim($ed60_c_concluida), $ensino);
                $resultadofinal = $sResultadoFinal;

                // Se ainda não tem resultado, verificar resultado_final2
                if (empty($resultadofinal) || $resultadofinal == 'MATRICULADO' || stripos($resultadofinal, 'MATRICULADO') !== false) {
                    $final2 = resultado_final2($escola, $ed11_i_codigo, $ed60_i_turma, $ed47_i_codigo);
                    if ($final2 == 'A') {
                        $resultadofinal = 'APROVADO';
                    } elseif ($final2 == 'R') {
                        $resultadofinal = 'REPROVADO';
                    } elseif ($final2 == 'E') {
                        $resultadofinal = 'EM ANDAMENTO';
                    }
                }
            }
        }

        /**
         * Autor: Uemerson Santana
         * Data: 30/10/2025
         * Demanda: 17412
         * razao: Exibir a porcentagem de frequência somente após o
         *        encerramento do ano letivo (via resultado_final2), mantendo faltas
         *        e exceções (transferido/evadido).
         */
        $lEncerrado = (resultado_final2($escola, $ed11_i_codigo, $ed60_i_turma, $ed47_i_codigo) != 'E');

        $dadosEnsino = array(
            'tipo' => 'eja_basico',
            'xdiario' => $xdiario,
            'xaulas' => $xaulas,
            'ejaturma' => $ejaturma,
            'ed52_i_ano' => $ed52_i_ano,
            'ed57_c_descr' => $ed57_c_descr,
            'ed60_c_situacao' => $ed60_c_situacao,
            'ed60_d_datasaida' => isset($datasaida) ? $datasaida : null,
            'resultadofinal' => $resultadofinal,
            'ed60_i_aluno' => $ed60_i_aluno,
            'ed60_c_parecer' => isset($ed60_c_parecer) ? $ed60_c_parecer : null,
            'lEncerrado' => $lEncerrado
        );

        // Ajuste de "NÃO AVALIADO" para NE com Parecer Descritivo (resultado final = 'N')
        if ($relatorio->isAlunoComNecessidadesEspeciais($ed60_i_aluno) && isset($ed60_c_parecer) && $ed60_c_parecer == 'S') {
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
                    $dadosEnsino['resultadofinal'] = 'NÃO AVALIADO';
                }
            }
        }
    }
    // EJA CICLOS
    elseif (($ed52_c_descr == "EJA ANOS INICIAIS" || substr($ed52_c_descr, 0, 12) == "EJA INICIAIS") && ($ejaturma == "1º CICLO" || $ejaturma == "2º CICLO")) {

        $xcodiario = $relatorio->retornaCodDiario($ed60_i_codigo, $ed60_i_aluno, $ed11_i_codigo, $ed60_i_turma);
        $xdiario = $relatorio->dadosDiario($xcodiario);

        /**
         * Autor: Uemerson Santana
         * Data: 18/11/2025
         * Demanda: 17412
         * Razão: Para EJA Anos Iniciais, deve usar buscaCodigoRegenciaComoAta() para buscar
         *        a regência da série com origem 'S' da matrícula, garantindo que os dias letivos
         *        sejam exibidos corretamente na ficha individual.
         */
        $codreg = $relatorio->buscaCodigoRegenciaComoAta($ed57_i_codigo, $ed60_i_codigo);
        if (empty($codreg)) {
            // Fallback para o método padrão se não encontrar regência específica
            $codreg = $relatorio->buscaCodigoRegencia($ed57_i_codigo);
        }
        $xaulas = $relatorio->dadosAulas($codreg);

        $indice2 = 0;
        $dadosgrade2 = array();
        $xcodiario2 = $relatorio->retornaCodDiario($ed60_i_codigo, $ed60_i_aluno, $ed11_i_codigo, $ed60_i_turma, true);
        $codreg2 = $relatorio->buscaCodigoRegencia($ed57_i_codigo, true);

        foreach ($xcodiario2 as $linha) {
            $xcodiario2Temp = $linha["ed95_i_codigo"];
            $xdiario2 = $relatorio->dadosDiario($xcodiario2Temp);
            $xdiario2["disciplina"] = $relatorio->retornaNomeDisciplina($linha["ed59_i_disciplina"]);
            $dadosgrade2[$indice2] = $xdiario2;
            $indice2++;
        }

        /**
         * Autor: Uemerson Santana
         * Data: 18/11/2025
         * Demanda: 17412
         * Razão: Aplicar busca do resultado da tela de encerramento para EJA Ciclos,
         *        garantindo que alunos aprovados no sistema apareçam corretamente na ficha.
         */
        $resultadofinal = '';

        // Verificar situação do aluno primeiro
        if ($ed60_c_situacao != 'MATRICULADO') {
            $resultadofinal = strtoupper($ed60_c_situacao);
        } else {
            // Buscar resultado usando a estrutura OOP nova (mesma lógica da tela de encerramento)
            $resultadofinal = buscaResultadoFinalEncerramento($ed60_i_codigo, $ensino, $ed52_i_ano);

            /**
             * Autor: Uemerson Santana
             * Data: 18/11/2025
             * Demanda: 17412
             * Razão: Verificar aprovação pelo conselho ANTES de usar fallback, garantindo que
             *        alunos aprovados pelo conselho sempre apareçam como APROVADO, mesmo que
             *        buscaResultadoFinalEncerramento() retorne vazio ou o fallback indique reprovação.
             */
            // Verificar aprovação pelo conselho diretamente antes de usar fallback
            $temAprovacaoConselhoFallback = false;
            if (class_exists('AprovacaoConselho')) {
                $sSqlConselhoFallback = "SELECT COUNT(*) as total
                                        FROM aprovconselho ac
                                        INNER JOIN diario d ON d.ed95_i_codigo = ac.ed253_i_diario
                                        INNER JOIN regencia r ON r.ed59_i_codigo = d.ed95_i_regencia
                                        WHERE d.ed95_i_aluno = {$ed60_i_aluno}
                                          AND r.ed59_c_condicao = 'OB'
                                          AND ac.ed253_aprovconselhotipo IN (1, 3)";
                $rsConselhoFallback = db_query($sSqlConselhoFallback);
                if ($rsConselhoFallback && pg_num_rows($rsConselhoFallback) > 0) {
                    $oDadosConselhoFallback = db_utils::fieldsMemory($rsConselhoFallback, 0);
                    if ($oDadosConselhoFallback->total > 0) {
                        $temAprovacaoConselhoFallback = true;
                    }
                }
            }

            // Se tem aprovação pelo conselho, resultado é SEMPRE APROVADO (não usar fallback)
            if ($temAprovacaoConselhoFallback) {
                $resultadofinal = 'APROVADO';
            } elseif (empty($resultadofinal)) {
                // Se não encontrou resultado na estrutura nova, usar fallback com verificação de dependência
                // IMPORTANTE: Só usar fallback se buscaResultadoFinalEncerramento() retornou vazio
                // Se retornou um resultado válido (APROVADO, REPROVADO, etc), NÃO sobrescrever
                $sResultadoFinal = ResultadoFinal($ed60_i_codigo, $ed60_i_aluno, $ed60_i_turma, trim($ed60_c_situacao), trim($ed60_c_concluida), $ensino);
                $resultadofinal = $sResultadoFinal;

                $final2 = resultado_final2($escola, $ed11_i_codigo, $ed60_i_turma, $ed47_i_codigo);

                // Verifica se há dependência
                $dependencia = false;
                foreach ($dadosgrade2 as $disciplina) {
                    $notas = array();
                    for ($i = 0; $i < 4; $i++) {
                        if (isset($disciplina[$i]["valor_nota"])) {
                            $notas[] = $disciplina[$i]["valor_nota"];
                        }
                    }

                    if (count($notas) == 4) {
                        $media = $relatorio->calculaMedia($notas);
                        if ($media < 5.0) {
                            $dependencia = true;
                            break;
                        }
                    }
                }

                /**
                 * Autor: Uemerson Santana
                 * Data: 18/11/2025
                 * Demanda: 17412
                 * Razão: Só aplicar lógica de fallback se o resultado ainda estiver vazio ou for "MATRICULADO".
                 *        NÃO sobrescrever se buscaResultadoFinalEncerramento() já retornou um resultado válido
                 *        (como "APROVADO" quando há aprovação pelo conselho).
                 */
                // Se ainda não tem resultado válido, usar lógica de dependência e resultado_final2
                if (empty($resultadofinal) || $resultadofinal == 'MATRICULADO' || stripos($resultadofinal, 'MATRICULADO') !== false) {
                    if (!$dependencia && $final2 != 'R') {
                        $resultadofinal = 'APROVADO';
                    } elseif ($final2 == 'R' || $dependencia) {
                        $resultadofinal = 'REPROVADO';
                    } else {
                        $resultadofinal = 'EM ANDAMENTO';
                    }
                }
            }
        }

        /**
         * Autor: Uemerson Santana
         * Data: 30/10/2025
         * Demanda: 17412
         * razao: Exibir a porcentagem de frequência somente após o
         *        encerramento do ano letivo (via resultado_final2), mantendo faltas
         *        e exceções (transferido/evadido).
         */
        $lEncerrado = (resultado_final2($escola, $ed11_i_codigo, $ed60_i_turma, $ed47_i_codigo) != 'E');

        $dadosEnsino = array(
            'tipo' => 'eja_ciclo',
            'xdiario' => $xdiario,
            'xaulas' => $xaulas,
            'dadosgrade2' => $dadosgrade2,
            'ejaturma' => $ejaturma,
            'ed52_i_ano' => $ed52_i_ano,
            'ed57_c_descr' => $ed57_c_descr,
            'ed60_c_situacao' => $ed60_c_situacao,
            'ed60_d_datasaida' => isset($datasaida) ? $datasaida : null,
            'resultadofinal' => $resultadofinal,
            'ed60_i_aluno' => $ed60_i_aluno,
            'ed60_c_parecer' => isset($ed60_c_parecer) ? $ed60_c_parecer : null,
            'ed60_i_matricula' => $ed60_i_codigo,
            'ed57_i_codigo' => $ed57_i_codigo,
            'lEncerrado' => $lEncerrado
        );

        // Ajuste de "NÃO AVALIADO" para NE com Parecer Descritivo (resultado final = 'N')
        if ($relatorio->isAlunoComNecessidadesEspeciais($ed60_i_aluno) && isset($ed60_c_parecer) && $ed60_c_parecer == 'S') {
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
                    $dadosEnsino['resultadofinal'] = 'NÃO AVALIADO';
                }
            }
        }
    }

    // ================================================================================================
    // IMPRESSÃO DOS DADOS
    // ================================================================================================

    // Prepara data por extenso
    $dia = date("d");
    $mes = date("m");
    $ano = date("Y");
    $mes_extenso = array(
        "01" => "janeiro", "02" => "fevereiro", "03" => "março", "04" => "abril",
        "05" => "maio", "06" => "junho", "07" => "julho", "08" => "agosto",
        "09" => "setembro", "10" => "outubro", "11" => "novembro", "12" => "dezembro"
    );
    $data_extenso = $mun_escola . ", " . $dia . " de " . $mes_extenso[$mes] . " de " . $ano . ".";

    // Cabeçalho da ficha
    $head1 = "FICHA DO ALUNO";
    $head2 = "{$ed47_i_codigo} - {$ed47_v_nome}";

    // Nova página se necessário
    if ($iCodigo != $ed60_i_codigo) {
        if ($ww != 0) {
            $pdf->ln(5);
            $pdf->Addpage('P');
        }
        $iCodigo = $ed60_i_codigo;
    }

    // Imprime dados pessoais
    $relatorio->imprimeDadosPessoais($dadosAluno, $aTiposSanguineos);

    // Ajusta posição Y
    $altini = $pdf->getY() + 5;
    $pdf->setY($altini);

    // Imprime dados do ensino conforme o tipo
    switch ($dadosEnsino['tipo']) {
        case 'infantil':
            $relatorio->imprimeEducacaoInfantil($dadosEnsino);
            break;

        case 'primeiro_ano':
            $relatorio->imprimePrimeiroAno($dadosEnsino);
            break;

        case 'eja_basico':
            $relatorio->imprimeEJA($dadosEnsino, 'basico');
            break;

        case 'eja_ciclo':
            $relatorio->imprimeEJA($dadosEnsino, 'ciclo');
            break;
    }

    $pdf->ln(8);

    // ================================================================================================
    // DADOS DA MATRÍCULA E OBSERVAÇÕES
    // ================================================================================================

    if ($clmatricula->numrows > 0) {

        db_fieldsmemory($result1, $ww);

        $oTurma = TurmaRepository::getTurmaByCodigo($ed57_i_codigo);

        // Busca observações da escola
        $clObsFichaIndAluno = new cl_obsfichaindaluno;
        $iEscola = $oTurma->getEscola()->getCodigo();
        $sSql = $clObsFichaIndAluno->sql_query("", "ed286_t_obs", "", " ed286_i_escola = $iEscola");
        $resultobs = $clObsFichaIndAluno->sql_record($sSql);

        if ($clObsFichaIndAluno->numrows > 0) {
            $oDadosObs = db_utils::fieldsmemory($resultobs, 0);
            $obs1 = $oDadosObs->ed286_t_obs;
        }

        $oMatricula = MatriculaRepository::getMatriculaByCodigo($ed60_i_codigo);
    }

    // Busca parecer final
    $sWhere = "ed95_i_aluno = {$ed60_i_aluno} AND ed95_i_regencia in({$disciplinas}) AND ed59_c_condicao = 'OB'";
    $sSqlDiarioResultado = $cldiarioresultado->sql_query("", "ed73_t_parecer", "", $sWhere);
    $result66 = $cldiarioresultado->sql_record($sSqlDiarioResultado);

    for ($e = 0; $e < $cldiarioresultado->numrows; $e++) {
        db_fieldsmemory($result66, $e);

        if ($ed73_t_parecer != "") {
            $pdf->setfont('arial', 'b', 7);
            $pdf->cell(190, 4, "Parecer Final", 1, 1, "C", 1);

            $pdf->setfont('arial', '', 7);
            $pdf->multicell(190, 3, "  " . trim($ed73_t_parecer), "LRB", "J", 0, 0);
            $pdf->cell(190, 4, "", 0, 1, "C", 0);
        }
    }

    // Observações do diário final
    $condicao = "AND ed95_i_regencia in({$disciplinas})";

    $campos = "ed95_i_regencia, ed232_c_descr, ed72_t_obs, ed72_i_codigo as codaval, ed72_t_parecer as parecer, ed09_c_descr";
    $campos .= ", ed72_c_amparo as amparoum, ed81_c_todoperiodo as amparo, ed06_c_descr as justificativa, ed72_i_numfaltas, ed09_i_codigo";
    $campos .= ", ed81_i_justificativa, ed81_i_convencaoamp, ed250_c_abrev, ed74_t_obs, ed78_i_aulasdadas";

    $sql = " SELECT {$campos} ";
    $sql .= "   FROM diarioavaliacao ";
    $sql .= "        inner join diario on ed95_i_codigo = ed72_i_diario ";
    $sql .= "        inner join regencia on ed59_i_codigo = ed95_i_regencia ";
    $sql .= "        inner join disciplina on ed12_i_codigo = ed59_i_disciplina ";
    $sql .= "        inner join caddisciplina on ed232_i_codigo = ed12_i_caddisciplina ";
    $sql .= "        left join amparo on ed81_i_diario = ed95_i_codigo ";
    $sql .= "        left join justificativa on ed06_i_codigo = ed81_i_justificativa ";
    $sql .= "        left join convencaoamp on ed250_i_codigo = ed81_i_convencaoamp ";
    $sql .= "        inner join procavaliacao on procavaliacao.ed41_i_codigo = diarioavaliacao.ed72_i_procavaliacao";
    $sql .= "        inner join periodoavaliacao on periodoavaliacao.ed09_i_codigo = procavaliacao.ed41_i_periodoavaliacao";
    $sql .= "        inner join regenciaperiodo on regenciaperiodo.ed78_i_procavaliacao = procavaliacao.ed41_i_codigo ";
    $sql .= "                                   and regenciaperiodo.ed78_i_regencia = regencia.ed59_i_codigo";
    $sql .= "        inner join diariofinal on diariofinal.ed74_i_diario = diario.ed95_i_codigo";
    $sql .= "  WHERE ed95_i_aluno = {$ed60_i_aluno} ";
    $sql .= "        {$condicao}";
    $sql .= "    AND ed59_c_condicao = 'OB' and (trim(ed72_t_obs) !='' or trim(ed72_t_parecer) !='')";
    $sql .= "  ORDER BY ed41_i_sequencia,ed59_i_ordenacao ";

    $result = db_query($sql);
    $linhas0 = pg_num_rows($result);

    if ($linhas0 > 0) {
        db_fieldsmemory($result, 0);

        if ($ed74_t_obs != "") {
            $pdf->setfont('arial', 'b', 7);
            $pdf->cell(190, 4, "Observações Diário final:", 1, 1, "L", 1);

            $pdf->setfont('arial', '', 7);
            $pdf->multicell(190, 4, mb_strtoupper($ed74_t_obs), 1, "L", 0, 0);
        }
    }

    // Observações gerais
    if ($obs1 != "") {
        $pdf->setfont('arial', 'b', 7);
        $pdf->cell(190, 4, "", 0, 1, "L", 0);
        $pdf->cell(190, 4, "Observações Gerais:", 1, 1, "L", 1);

        $pdf->setfont('arial', '', 7);
        $pdf->multicell(190, 4, mb_strtoupper($obs1), 1, "L", 0, 0);
    }

    // Observações do Conselho (Justificativa da Alteração do Resultado Final)
    $sCamposAprovCons = " distinct ed11_c_descr as serie_conselho, ed52_i_ano, ed253_aprovconselhotipo, ed253_t_obs, ed232_c_descr ";
    $sWhereAprovCons  = "     ed95_i_aluno = {$ed60_i_aluno} ";
    if (isset($oGet->calendario) && !empty($oGet->calendario)) {
        $sWhereAprovCons .= " and ed52_i_codigo = {$oGet->calendario} ";
    }
    $sSqlAprovCons = $oDaoAprovConselho->sql_query("", $sCamposAprovCons, "ed11_c_descr, ed52_i_ano", $sWhereAprovCons);
    $rsAprovConselho = $oDaoAprovConselho->sql_record($sSqlAprovCons);
    $iLinhasAprovCons = $oDaoAprovConselho->numrows;
    if ($iLinhasAprovCons > 0) {
        $sObsConselho = '';
        for ($iContObs = 0; $iContObs < $iLinhasAprovCons; $iContObs++) {
            $oDadosAprovConselho = db_utils::fieldsmemory($rsAprovConselho, $iContObs);
            if (!empty($oDadosAprovConselho->ed253_t_obs)) {
                $sObsConselho .= ($sObsConselho != '' ? "\n" : "");
                $sObsConselho .= "\n- Justificativa (" . $oDadosAprovConselho->ed232_c_descr . "): " . $oDadosAprovConselho->ed253_t_obs . "\n";
            }
        }
        if ($sObsConselho != '') {
            $pdf->setfont('arial', 'b', 7);
            $pdf->cell(190, 4, "", 0, 1, "L", 0);
            $pdf->cell(190, 4, "Observações do Conselho:", 1, 1, "L", 1);
            $pdf->setfont('arial', '', 7);
            $pdf->multicell(190, 4, $sObsConselho, 1, "L", 0, 0);
        }
    }

    // Data
    $final = $pdf->getY();
    $pdf->setY($final + 5);
    $pdf->cell(25, 4, $data_extenso, 0, 1, "L", 0);

    // Busca regente do conselho
    $sCampos = "case when ed20_i_tiposervidor = 1 then cgmrh.z01_nome else cgmcgm.z01_nome end as regente";
    $sSqlRegenteConselho = $clregenteconselho->sql_query("", $sCampos, "", " ed235_i_turma = {$ed57_i_codigo}");
    $result5 = $clregenteconselho->sql_record($sSqlRegenteConselho);

    if ($clregenteconselho->numrows > 0) {
        db_fieldsmemory($result5, 0);
    } else {
        $regente = "";
    }

    // Nova página se necessário para assinaturas
    if ($pdf->getY() >= $pdf->h - 30) {
        $pdf->Addpage('P');
    }

    // ================================================================================================
    // ASSINATURAS
    // ================================================================================================

    // Prepara dados das assinaturas
    $assinaturas = array();

    /**
     * Autor: Uemerson Santana
     * Data: 27/11/2025
     * Demanda: 17982
     * Razão: Corrigida busca das matrículas de secretário, supervisor e diretor para filtrar pela escola vinculada através de rechumanoescola,
     *        garantindo que quando o profissional possui múltiplas matrículas, seja retornada a correta vinculada à escola atual.
     *        Removido substr que removia os 2 primeiros dígitos na exibição, exibindo agora a matrícula completa.
     */
    // Secretário
    if (isset($iAssinaturaAdicional3) && $iAssinaturaAdicional3 != null) {
        // Busca matrícula do secretário (filtrada por escola para garantir a correta quando o profissional tem múltiplas matrículas)
        $iEscola = db_getsession("DB_coddepto");
        $sqlSec = "SELECT ed284_i_rhpessoal as matricula
                   FROM rechumanopessoal
                   INNER JOIN rechumano ON rechumano.ed20_i_codigo = rechumanopessoal.ed284_i_rechumano
                   INNER JOIN rechumanoescola ON rechumanoescola.ed75_i_rechumano = rechumano.ed20_i_codigo
                   INNER JOIN rhpessoal ON rhpessoal.rh01_regist = rechumanopessoal.ed284_i_rhpessoal
                   WHERE rh01_numcgm = " . (isset($matrisec) ? $matrisec : 0) . "
                     AND rechumanoescola.ed75_i_escola = {$iEscola}
                   LIMIT 1";
        $resSec = db_query($sqlSec);

        $matriculaSec = '';
        if (pg_num_rows($resSec) > 0) {
            $dadosSec = db_fieldsmemory($resSec, 0);
            $matriculaSec = $dadosSec->matricula;
        }

        $assinaturas['secretario'] = array(
            'nome' => $iAssinaturaAdicional3,
            'matricula' => $matriculaSec
        );
    } else {
        $assinaturas['secretario'] = array('nome' => '', 'matricula' => '');
    }

    // Supervisor
    if (isset($iAssinaturaAdicional2) && $iAssinaturaAdicional2 != null) {
        // Busca matrícula do supervisor (filtrada por escola para garantir a correta quando o profissional tem múltiplas matrículas)
        $iEscola = db_getsession("DB_coddepto");
        $sqlSup = "SELECT ed284_i_rhpessoal as matricula
                   FROM rechumanopessoal
                   INNER JOIN rechumano ON rechumano.ed20_i_codigo = rechumanopessoal.ed284_i_rechumano
                   INNER JOIN rechumanoescola ON rechumanoescola.ed75_i_rechumano = rechumano.ed20_i_codigo
                   INNER JOIN rhpessoal ON rhpessoal.rh01_regist = rechumanopessoal.ed284_i_rhpessoal
                   WHERE rh01_numcgm = " . (isset($matrisup) ? $matrisup : 0) . "
                     AND rechumanoescola.ed75_i_escola = {$iEscola}
                   LIMIT 1";
        $resSup = db_query($sqlSup);

        $matriculaSup = '';
        if (pg_num_rows($resSup) > 0) {
            $dadosSup = db_fieldsmemory($resSup, 0);
            $matriculaSup = $dadosSup->matricula;
        }

        $assinaturas['supervisor'] = array(
            'nome' => $iAssinaturaAdicional2,
            'matricula' => $matriculaSup
        );
    } else {
        $assinaturas['supervisor'] = array('nome' => '', 'matricula' => '');
    }

    // Diretor
    if (isset($iAssinaturaAdicional) && $iAssinaturaAdicional != null) {
        // Busca matrícula do diretor (filtrada por escola para garantir a correta quando o profissional tem múltiplas matrículas)
        $iEscola = db_getsession("DB_coddepto");
        $sqlDir = "SELECT ed284_i_rhpessoal as matricula
                   FROM rechumanopessoal
                   INNER JOIN rechumano ON rechumano.ed20_i_codigo = rechumanopessoal.ed284_i_rechumano
                   INNER JOIN rechumanoescola ON rechumanoescola.ed75_i_rechumano = rechumano.ed20_i_codigo
                   INNER JOIN rhpessoal ON rhpessoal.rh01_regist = rechumanopessoal.ed284_i_rhpessoal
                   WHERE rh01_numcgm = " . (isset($matridir) ? $matridir : 0) . "
                     AND rechumanoescola.ed75_i_escola = {$iEscola}
                   LIMIT 1";
        $resDir = db_query($sqlDir);

        $matriculaDir = '';
        if (pg_num_rows($resDir) > 0) {
            $dadosDir = db_fieldsmemory($resDir, 0);
            $matriculaDir = $dadosDir->matricula;
        }

        $assinaturas['diretor'] = array(
            'nome' => $iAssinaturaAdicional,
            'matricula' => $matriculaDir
        );
    } else {
        $assinaturas['diretor'] = array('nome' => '', 'matricula' => '');
    }

    // Imprime assinaturas
    $relatorio->imprimeAssinaturas($assinaturas);
}

// Gera o PDF
$pdf->Output();

<?php

/**
 *     E-cidade Software Publico para Gestao Municipal
 *  Copyright (C) 2009  DBSeller Servicos de Informatica
 *                            www.dbseller.com.br
 *                         e-cidadedbseller.com.br
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

require_once(modification("libs/db_stdlib.php"));
require_once(modification("libs/db_conecta.php"));
require_once(modification("libs/db_sessoes.php"));
require_once(modification("libs/db_usuariosonline.php"));
require_once(modification("dbforms/db_funcoes.php"));
require_once(modification("classes/db_rhteutri_classe.php"));
require_once(modification("classes/db_db_config_classe.php"));

$clrhteutri = new cl_rhteutri;
$cldbconfig = new cl_db_config;
$clrotulo   = new rotulocampo;

db_postmemory($_POST);

$rh67_rhtipovale = isset($_POST['rh67_rhtipovale']) ? $_POST['rh67_rhtipovale'] : '';
$rh68_descr      = $_POST['rh68_descr'];
$tipo_emissao    = $_POST['tipo_emissao'];
$ordem           = $_POST['ordem'];
$gera            = $_POST['gera'];

$iAno = DBPessoal::getAnoFolha();
$iMes = DBPessoal::getMesFolha();

if (isset($gera)) {
    $lEmitePorValor = ($tipo_emissao == "valor" ? true : false);

    if ($ordem == 'a') {
        $xordem = 'z01_nome';
    } else {
        $xordem = 'rh67_regist';
    }

    $where = " rh67_rhtipovale = $rh67_rhtipovale ";

    if ($tipo == 'a') {
        $where .= " and rh67_ativo = 't'";
    } elseif ($tipo == 'i') {
        $where .= " and rh67_ativo = 'f'";
    }

    $where .= " and rh67_anousu = $iAno ";
    $where .= " and rh67_mesusu = $iMes ";
    $where .= " and rh05_recis is null";
    $where .= " and rh02_instit = " . db_getsession('DB_instit') . " ";

    $instituicao = db_getsession('DB_instit');
    $sql = $cldbconfig->sql_query(null, "cgc", null, "codigo = {$instituicao}");
    $resultCnpj = pg_query($conn, $sql);

    if (!$resultCnpj) {
        throw new Exception("Erro na consulta: " . pg_last_error());
    }

    if (pg_num_rows($resultCnpj) > 0) {
        $row = pg_fetch_assoc($resultCnpj);
        $cgc = $row['cgc'];
    } else {
        echo "Nenhum registro encontrado.";
    }

    $result = $clrhteutri->sql_record($clrhteutri->sql_query(
                                                            null,
                                                            "rh67_regist,
                                                            z01_nome,
                                                            z01_cgccpf",
                                                            $xordem,
                                                            $where
    ));

    $totalRegistros = pg_num_rows($result);

    $constante = "PEDIDO";
    $versao    = "0100";
    $cnpj      = $cgc;
    $dataAtual = date("Ymd");
    $horaAtual = date("Hi");

    $arq = "tmp/{$constante}_{$versao}_{$cnpj}_{$dataAtual}_{$horaAtual}.txt";

    $arquivo = fopen($arq, 'w');

    if ($rh67_rhtipovale == 1) { // Calcula o mês seguinte (se for dezembro, será janeiro do próximo ano)
        $nextMonth = $iMes == 12 ? 1 : $iMes + 1;
        $nextYear = $iMes == 12 ? $iAno + 1 : $iAno;

        // Exclui os registros do próximo mês antes de inserir os novos
        $sSqlDelete = "DELETE FROM vtfdias WHERE r63_anousu = $nextYear AND r63_mesusu = $nextMonth;";
        $rsDelete = db_query($sSqlDelete);

        if (!$rsDelete) {
            throw new Exception("Erro ao deletar registros antigos da tabela vtfdias para o próximo mês: " . pg_last_error());
        }

        // Calcular o último dia do mês seguinte de forma dinâmica
        $lastDayOfNextMonth = "LAST_DAY('$nextYear-$nextMonth-01'::date)";

        // Query dinâmica com as variáveis
        $sSql = " WITH dias_atuais AS (
                        SELECT DISTINCT 
                            r63_regist, 
                            r63_dia, 
                            EXTRACT(dow FROM r63_dia) AS dia_semana,
                            r63_vale,
                            r63_difere,
                            r63_quant,
                            r63_obrig,
                            r63_quants
                        FROM vtfdias
                        WHERE r63_anousu = $iAno 
                        AND r63_mesusu = $iMes
                        AND r63_regist IN (
                            SELECT rh02_regist
                            FROM pessoal.rhpessoalmov
                            WHERE rh02_instit = " . db_getsession('DB_instit') . "
                        )
                    ),
                dias_proximos AS (
                    SELECT 
                        generate_series(
                            '$nextYear-$nextMonth-01'::date, 
                            (DATE_TRUNC('MONTH', '$nextYear-$nextMonth-01'::date) + INTERVAL '1 MONTH - 1 day')::date,
                            '1 day'::interval
                        )::date AS novo_dia,
                        EXTRACT(dow FROM generate_series(
                            '$nextYear-$nextMonth-01'::date, 
                            (DATE_TRUNC('MONTH', '$nextYear-$nextMonth-01'::date) + INTERVAL '1 MONTH - 1 day')::date,
                            '1 day'::interval
                        )) AS dia_semana
                )
                INSERT INTO vtfdias (
                    r63_anousu, r63_mesusu, r63_regist, r63_vale, 
                    r63_difere, r63_dia, r63_quant, r63_obrig, r63_quants
                )
                SELECT 
                    $nextYear, $nextMonth, da.r63_regist, da.r63_vale, 
                    da.r63_difere, dp.novo_dia, da.r63_quant, da.r63_obrig, da.r63_quants
                FROM dias_atuais da
                JOIN dias_proximos dp ON da.dia_semana = dp.dia_semana
                WHERE NOT EXISTS (
                    SELECT 1 
                    FROM vtfdias v
                    WHERE v.r63_anousu = $nextYear
                    AND v.r63_mesusu = $nextMonth
                    AND v.r63_regist = da.r63_regist
                    AND v.r63_dia = dp.novo_dia
                )
                ON CONFLICT (r63_anousu, r63_mesusu, r63_regist, r63_vale, r63_difere, r63_dia) 
                DO NOTHING";

        $rsWithVtfdias = db_query($sSql);

        if (!$rsWithVtfdias) {
            throw new Exception("Erro ao atualizar dados na tabela vtfdias: " . pg_last_error());
        }

        if (pg_affected_rows($rsWithVtfdias) == 0) {
            throw new Exception("Nenhuma linha foi atualizada na tabela vtfdias.");
        }

        $somaTotal = 0;
        $iContador = 1;  // Inicializa o contador

        $sHeader  = str_pad($iContador, 5, '0', STR_PAD_LEFT);         // Nr_seq_reg
        $sHeader .= '01';                                              // Tp_registro
        $sHeader .= 'PEDIDO';                                          // Nm_arquivo
        $sHeader .= '01.00';                                           // Nr_versão
        $sHeader .= db_formatar($cgc, 's', '0', 14, 'e', 0) . PHP_EOL; // Nr_doc_comprd (CPF/CNPJ/CEI)

        fputs($arquivo, $sHeader);

        for ($x = 0; $x < $totalRegistros; $x++) {
            $row = pg_fetch_assoc($result, $x);

            $rh67_regist = $row['rh67_regist'];
            $z01_nome   = $row['z01_nome'];
            $z01_cgccpf = $row['z01_cgccpf'];

            $oServidor = ServidorRepository::getInstanciaByCodigo(trim($rh67_regist));

            $F032 = DBPessoal::getVariaveisCalculo($oServidor, 'f032');
            $iDiasUteis = (int)$F032;

            $iMes = DBPessoal::getMesFolha();
            $iAno = DBPessoal::getAnoFolha();

            $sSql  = "SELECT ROUND(SUM(valor), 2) AS valor ";
            $sSql .= "  FROM ( ";
            $sSql .= "       SELECT r16_valor * r63_quant AS valor ";
            $sSql .= "       FROM vtfempr ";
            $sSql .= "       INNER JOIN vtffunc ON vtffunc.r17_codigo = vtfempr.r16_codigo ";
            $sSql .= "                         AND vtffunc.r17_anousu = vtfempr.r16_anousu ";
            $sSql .= "                         AND vtffunc.r17_mesusu = vtfempr.r16_mesusu ";
            $sSql .= "       INNER JOIN vtfdias ON vtfdias.r63_vale   = vtfempr.r16_codigo ";
            $sSql .= "                         AND vtfdias.r63_anousu = vtfempr.r16_anousu ";
            $sSql .= "                         AND vtfdias.r63_mesusu = vtfempr.r16_mesusu ";
            $sSql .= "       INNER JOIN rhteutri ON rhteutri.rh67_anousu = vtffunc.r17_anousu ";
            $sSql .= "                         AND rhteutri.rh67_mesusu = vtffunc.r17_mesusu ";
            $sSql .= "                         AND rhteutri.rh67_regist = vtffunc.r17_regist ";
            $sSql .= "       INNER JOIN rhpessoalmov ON rhpessoalmov.rh02_anousu = vtffunc.r17_anousu ";
            $sSql .= "                         AND rhpessoalmov.rh02_mesusu = vtffunc.r17_mesusu ";
            $sSql .= "                         AND rhpessoalmov.rh02_regist = vtffunc.r17_regist ";
            $sSql .= "       LEFT JOIN rhpesrescisao ON rhpesrescisao.rh05_seqpes = rhpessoalmov.rh02_seqpes ";
            $sSql .= "       WHERE r16_anousu = $iAno ";
            $sSql .= "         AND r16_mesusu = $iMes ";
            $sSql .= "         AND r17_regist = {$rh67_regist} ";
            $sSql .= "         AND r63_regist = {$rh67_regist} ";
            $sSql .= "         AND r16_empres = '1' ";
            $sSql .= "         AND rh05_recis is null";
            $sSql .= "         AND r16_instit = " . db_getsession('DB_instit') . " ";
            $sSql .= "   GROUP BY r16_codigo, r16_valor, r63_quant ";
            $sSql .= "  ) AS subquery ";

            $rsValorVtf = db_query($sSql);

            if (!$rsValorVtf || pg_num_rows($rsValorVtf) == 0) {
                continue;
            }

            $sSqlDiasAfastados = "select * from fc_valetransporte_dias_afasta_vr({$rh67_regist}, $iAno, $iMes)";
            $rsDiasAfastados = db_query($sSqlDiasAfastados);
            $oCalculoAfastamento = db_utils::fieldsMemory($rsDiasAfastados, 0);

            $iDiasAfastados = $oCalculoAfastamento->ridiasmes + $oCalculoAfastamento->ridiasmesant;
            $diasDeFerias = $oCalculoAfastamento->ridiasferias;
            $diasValidos = $oCalculoAfastamento->ridiasvalidos;

            $dataInicioFerias = $oCalculoAfastamento->ridtafas;
            $dataFimFerias = $oCalculoAfastamento->ridtreto;

            $anoAtual = $iAno;
            $mesAtual = $iMes;

            if ($iMes == 12) {
                $iAno = $iAno + 1;
                $iMes = 1;
            } else {
                $iMes = $iMes + 1;
            }

            $dataInicioMes = "{$iAno}-" . str_pad($iMes, 2, '0', STR_PAD_LEFT) . "-01";
            $dataFimMes = "{$iAno}-" . str_pad($iMes, 2, '0', STR_PAD_LEFT) . "-" . (new DateTime("$iAno-$iMes-01"))->modify('last day of this month')->format('d');
            $diasATrabalhar = 0;

            if ($dataInicioFerias && $dataFimFerias) {
                if ($dataInicioMes <= $dataInicioFerias) { // Se a data do mês de início for antes ou igual à data de início das férias
                    $dadosNoProximoMes = false;

                    $sSqlVerificaDadosEmProximoMes = " SELECT DISTINCT r63_regist
                            FROM vtfdias 
                                INNER JOIN rhpessoal ON rhpessoal.rh01_regist = vtfdias.r63_regist
                            WHERE r63_anousu = $iAno 
                                AND r63_mesusu = $iMes 
                                AND r63_regist = $rh67_regist
                                AND rh01_instit = " . db_getsession('DB_instit') . "
                    ";

                    $rsVerificaDadosEmProximoMes = db_query($sSqlVerificaDadosEmProximoMes);
                    $dadosNoProximoMes = pg_num_rows($rsVerificaDadosEmProximoMes) > 0;

                    $registros = [];
                    while ($reg = pg_fetch_object($rsVerificaDadosEmProximoMes)) {
                        $registros[] = $reg;
                    }

                    foreach ($registros as $oRegistro) {
                        $matriculaAtual = $oRegistro->r63_regist;

                        // Consultando os dias úteis antes das férias
                        $sSqlDiasUteisAntigos = "SELECT COUNT(DISTINCT r63_dia) AS dias_uteis
                                                    FROM (SELECT r63_dia
                                                                FROM vtfdias
                                                                    WHERE r63_regist = $matriculaAtual
                                                                        AND r63_anousu = $iAno
                                                                        AND r63_mesusu = $iMes
                                                                        AND r63_dia NOT BETWEEN '$dataInicioFerias' AND '$dataFimFerias'
                                                                        AND r63_dia NOT IN (
                                                        SELECT DISTINCT r62_data
                                                            FROM calendf
                                                                WHERE COALESCE(r62_calend, 0) IN (
                                                            SELECT DISTINCT rh53_calend
                                                                FROM rhcadcalend
                                                                    INNER JOIN rhlotacalend ON rh64_calend = rh53_calend
                                                                        WHERE rh53_instit = " . db_getsession('DB_instit') . "))
                                                    ) AS dias_uteis";

                        $rsDiasUteisAntigos = db_query($sSqlDiasUteisAntigos);
                        $diasATrabalhar = (int) db_utils::fieldsMemory($rsDiasUteisAntigos, 0)->dias_uteis;
                    }
                    foreach ($registros as $oRegistro) {
                        $matriculaAtual = $oRegistro->r63_regist;
                        // Consultando os dias úteis antes das férias
                        $sSqlDiasUteisAntigos = "SELECT COUNT(DISTINCT r63_dia) AS dias_uteis
                                                    FROM (SELECT r63_dia
                                                                FROM vtfdias
                                                                    WHERE r63_regist = $matriculaAtual
                                                                        AND r63_anousu = $iAno
                                                                        AND r63_mesusu = $iMes
                                                                        AND r63_dia NOT BETWEEN '$dataInicioFerias' AND '$dataFimFerias'
                                                                        AND r63_dia NOT IN (
                                                        SELECT DISTINCT r62_data
                                                            FROM calendf
                                                                WHERE COALESCE(r62_calend, 0) IN (
                                                            SELECT DISTINCT rh53_calend
                                                                FROM rhcadcalend
                                                                    INNER JOIN rhlotacalend ON rh64_calend = rh53_calend
                                                                        WHERE rh53_instit = " . db_getsession('DB_instit') . "))
                                                    ) AS dias_uteis";

                        $rsDiasUteisAntigos = db_query($sSqlDiasUteisAntigos);
                        $diasATrabalhar = (int) db_utils::fieldsMemory($rsDiasUteisAntigos, 0)->dias_uteis;
                    }
                    // Verifica se não há dados no próximo mês
                    if (!$dadosNoProximoMes) {
                        $sSqlDiasUteisAntesFerias = " SELECT COUNT(*) AS dias_uteis 
                                    FROM (
                                        SELECT generate_series('$dataInicioMes'::date, '$dataInicioFerias'::date, '1 day'::interval) AS data_mes
                                    ) AS datas
                                    WHERE EXTRACT(DOW FROM data_mes) NOT IN (0, 6)
                                        AND data_mes NOT IN (
                                    SELECT r62_data 
                                        FROM calendf 
                                    WHERE r62_calend IN ( 
                                        SELECT DISTINCT rh53_calend 
                                            FROM rhcadcalend 
                                                INNER JOIN rhlotacalend ON rh64_calend = rh53_calend 
                                            WHERE rh53_instit = " . db_getsession('DB_instit') . "))";

                        // Executa a consulta e pega os dias úteis antes das férias
                        $rsDiasUteisAntesFerias = db_query($sSqlDiasUteisAntesFerias);
                        $diasATrabalhar = (int) db_utils::fieldsMemory($rsDiasUteisAntesFerias, 0)->dias_uteis;
                    }
                } elseif ($dataFimFerias <= $dataFimMes) {
                    $dadosNoProximoMes = false;

                    $sSqlVerificaDadosEmProximoMes = "
                            SELECT DISTINCT r63_regist
                            FROM vtfdias 
                                INNER JOIN rhpessoal ON rhpessoal.rh01_regist = vtfdias.r63_regist
                            WHERE r63_anousu = $iAno 
                                AND r63_mesusu = $iMes 
                                AND r63_regist = $rh67_regist
                                AND rh01_instit = " . db_getsession('DB_instit') . "
                    ";

                    $rsVerificaDadosEmProximoMes = db_query($sSqlVerificaDadosEmProximoMes);
                    $dadosNoProximoMes = pg_num_rows($rsVerificaDadosEmProximoMes) > 0;

                    // Convertendo o resource para um array que pode ser percorrido com foreach
                    $registros = [];
                    while ($reg = pg_fetch_object($rsVerificaDadosEmProximoMes)) {
                        $registros[] = $reg;
                    }
                    foreach ($registros as $oRegistro) {
                        $matriculaAtual = $oRegistro->r63_regist;
                        // Consultando os dias úteis antes das férias
                        $sSqlDiasUteisAntigos = "SELECT COUNT(DISTINCT r63_dia) AS dias_uteis
                                                     FROM (SELECT r63_dia
                                                                 FROM vtfdias
                                                                     WHERE r63_regist = $matriculaAtual
                                                                         AND r63_anousu = $iAno
                                                                         AND r63_mesusu = $iMes
                                                                         AND r63_dia NOT BETWEEN '$dataInicioFerias' AND '$dataFimFerias'
                                                                         AND r63_dia NOT IN (
                                                         SELECT DISTINCT r62_data
                                                             FROM calendf
                                                                 WHERE COALESCE(r62_calend, 0) IN (
                                                             SELECT DISTINCT rh53_calend
                                                                 FROM rhcadcalend
                                                                     INNER JOIN rhlotacalend ON rh64_calend = rh53_calend
                                                                         WHERE rh53_instit = " . db_getsession('DB_instit') . "))
                                                     ) AS dias_uteis";

                        $rsDiasUteisAntigos = db_query($sSqlDiasUteisAntigos);
                        $diasATrabalhar = (int) db_utils::fieldsMemory($rsDiasUteisAntigos, 0)->dias_uteis;
                    }

                    // Dia seguinte ao término das férias
                    $dataInicioAposFerias = date('Y-m-d', strtotime($dataFimFerias . ' +1 day'));

                    // Obter mês e ano do fim das férias
                    $mesTerminoFerias = (int) date('m', strtotime($dataFimFerias));
                    $anoTerminoFerias = (int) date('Y', strtotime($dataFimFerias));

                    // Obter mês e ano da competência
                    $mesCompetencia = (int) date('m', strtotime($dataCompetencia));
                    $anoCompetencia = (int) date('Y', strtotime($dataCompetencia));

                    // Se o mês de término das férias for o mesmo da competência, calcular para o próximo mês
                    if ($mesTerminoFerias == $mesCompetencia && $anoTerminoFerias == $anoCompetencia) {
                        $mesCalculo = $mesCompetencia + 1;
                        $anoCalculo = $anoCompetencia;

                        if ($mesCalculo > 12) {
                            $mesCalculo = 1;
                            $anoCalculo++;
                        }
                    } else {
                        // Caso contrário, usar o mês de término das férias
                        $mesCalculo = $mesTerminoFerias;
                        $anoCalculo = $anoTerminoFerias;
                    }

                    $primeiroDiaCalculo = date('Y-m-d', strtotime("$anoCalculo-$mesCalculo-01"));
                    $ultimoDiaCalculo = date('Y-m-t', strtotime($primeiroDiaCalculo));

                    if (!$dadosNoProximoMes) {
                        $sSqlDiasUteisAposFerias = " SELECT COUNT(*) AS dias_uteis 
                                    FROM (
                                        SELECT generate_series('$dataInicioAposFerias'::date, '$ultimoDiaCalculo'::date, '1 day'::interval) AS data_mes

                                    ) AS datas
                                    WHERE EXTRACT(DOW FROM data_mes) NOT IN (0, 6)
                                        AND data_mes NOT IN (
                                    SELECT r62_data 
                                        FROM calendf 
                                    WHERE r62_calend IN ( 
                                        SELECT DISTINCT rh53_calend 
                                            FROM rhcadcalend 
                                                INNER JOIN rhlotacalend ON rh64_calend = rh53_calend 
                                            WHERE rh53_instit = " . db_getsession('DB_instit') . "))";

                        // Executa a consulta para calcular os dias úteis após as férias
                        $rsDiasUteisAposFerias = db_query($sSqlDiasUteisAposFerias);
                        $diasATrabalhar = (int) db_utils::fieldsMemory($rsDiasUteisAposFerias, 0)->dias_uteis;
                    }
                }
            } else {
                // Caso não haja férias ou as condições anteriores não sejam atendidas
                $diasATrabalhar = $iDiasUteis;
            }

            $diasUteisTrabalhados = $diasATrabalhar - $iDiasAfastados;
            $nValor = str_replace(".", "", db_utils::fieldsMemory($rsValorVtf, 0)->valor);

            // Atualiza a tabela mesmo que os valores sejam zero
            $totalNValor = $nValor * $diasUteisTrabalhados;

            $sSqlUpdate  = "UPDATE rhteutri SET rh67_dias = {$diasUteisTrabalhados}, ";
            $sSqlUpdate .= "rh67_valor = {$totalNValor}, ";
            $sSqlUpdate .= "rh67_vales = {$diasValidos}";
            $sSqlUpdate .= "WHERE rh67_regist = {$rh67_regist} ";
            $sSqlUpdate .= "AND rh67_anousu = {$anoAtual} ";
            $sSqlUpdate .= "AND rh67_mesusu = {$mesAtual} ";
            $sSqlUpdate .= "AND rh67_rhtipovale = 1 ";

            db_query($sSqlUpdate);

            if (!db_query($sSqlUpdate)) {
                throw new Exception("Erro ao atualizar dados na tabela rhteutri: " . pg_last_error());
            }

            if ($diasUteisTrabalhados > 0 && $totalNValor > 0) {
                $sLinha  = str_pad($iContador, 5, '0', STR_PAD_LEFT);  // Número do registro
                $sLinha .= db_formatar('02', 's', ' ', 2, 'd', 0);      // Formatação
                $sLinha .= db_formatar($row['rh67_regist'], 's', ' ', 15, 'd', 0);  // Matrícula
                $sLinha .= str_pad($totalNValor, 8, "0", STR_PAD_LEFT) . PHP_EOL;    // Valor

                fputs($arquivo, $sLinha);
                $iContador++;
                $somaTotal += $totalNValor;
            }
        }

        $sTrailer = str_pad($iContador, 5, '0', STR_PAD_LEFT);
        $sTrailer .= "99";
        $sTrailer .= str_pad($somaTotal, 10, '0', STR_PAD_LEFT) . PHP_EOL;

        fputs($arquivo, $sTrailer);
    }
    fclose($arquivo);
}

$oRetorno = (object)array(
    'erro' => false,
    'mensagem' => '',
    'arquivo' => $arq
);

echo JSON::create()->stringify($oRetorno);

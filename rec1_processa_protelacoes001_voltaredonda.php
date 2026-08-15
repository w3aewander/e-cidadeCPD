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

require(modification("libs/db_stdlib.php"));
require_once(modification("libs/db_conecta" . ".php"));
include(modification("libs/db_sessoes.php"));
include(modification("libs/db_usuariosonline.php"));
include(modification("classes/db_protelac_classe.php"));
include(modification("dbforms/db_funcoes.php"));

db_postmemory($_POST);

?>
<?php

function novaDataTrienio($iAnoCompetencia, $iMesCompetencia, $iInstituicao)
{
    $dias_direito_total_por_matricula = array();
    
    if ($iInstituicao == 45) {
        // Query para instituição 45 - verifica por matrícula se tem assentamento 176
        $queryMatriculas = "SELECT DISTINCT rh02_regist AS matricula
                 FROM rhpessoalmov
                 INNER JOIN rhregime ON rh02_codreg = rh30_codreg AND rh02_instit = rh30_instit
                 LEFT JOIN rhpesrescisao ON rh02_seqpes = rh05_seqpes
                 WHERE rh02_anousu = $iAnoCompetencia
                   AND rh02_mesusu = $iMesCompetencia
                   AND rh02_instit = $iInstituicao
                   AND rh02_codreg NOT IN (15,17,18,19,207,208,209,257,259,308,457,810,812,813,814,815,819,828,831,836,837)
                   AND rh30_vinculo = 'A'
                   AND rh05_recis IS NULL";

        $rsMatriculas = db_query($queryMatriculas);
        
        while ($rowMatricula = pg_fetch_assoc($rsMatriculas)) {
            $matricula = $rowMatricula['matricula'];
            
            // Verifica se ESTA matrícula específica tem assentamento 176
            $queryVerifica176 = "SELECT EXISTS (
                SELECT 1 FROM assenta 
                WHERE h16_assent = 176 
                AND h16_regist = $matricula
            ) AS tem_assentamento_176";

            $rsVerifica = db_query($queryVerifica176);
            $temAssentamento176 = pg_fetch_result($rsVerifica, 0, 0);

            if ($temAssentamento176 == 't') {
                // Query COM assentamento 176 para esta matrícula
                $queryTrienio = "SELECT 
                                COALESCE(MAX(p.h19_op01), '') AS operador, 
                                COALESCE(SUM(CASE WHEN p.h19_op01 IS NOT NULL THEN a.h16_quant ELSE 0 END), 0) AS quantidade,
                                rh01_admiss AS data_admissao, 
                                COALESCE(b.h16_dtconc, rh01_admiss) AS data_admissao_trienio, 
                                rh02_regist AS matricula
                            FROM rhpessoal
                            INNER JOIN rhpessoalmov
                                ON rh01_regist = rh02_regist
                               AND rh01_instit = rh02_instit
                            INNER JOIN rhregime
                                ON rh02_codreg = rh30_codreg
                               AND rh02_instit = rh30_instit
                            LEFT JOIN rhpesrescisao
                                ON rh02_seqpes = rh05_seqpes
                            LEFT JOIN (
                                SELECT h16_regist,
                                       MAX(h16_dtconc) AS h16_dtconc
                                FROM assenta
                                WHERE h16_assent = 176
                                AND h16_regist = $matricula
                                GROUP BY h16_regist
                            ) b
                                ON b.h16_regist = rh01_regist
                            LEFT JOIN assenta a
                                ON a.h16_regist = rh01_regist
                               AND (b.h16_dtconc IS NULL OR a.h16_dtconc > b.h16_dtconc)
                            LEFT JOIN tipoasse t
                                ON t.h12_codigo = a.h16_assent
                            LEFT JOIN protelac p
                                ON CAST(t.h12_codigo AS VARCHAR) = p.h19_assent
                               AND p.h19_instit = $iInstituicao
                               AND p.h19_tipo = 'T'
                            WHERE rh02_anousu = $iAnoCompetencia
                              AND rh02_mesusu = $iMesCompetencia
                              AND rh02_codreg NOT IN (15,17,18,19,207,208,209,257,259,308,457,810,812,813,814,815,819,828,831,836,837)
                              AND rh30_vinculo = 'A'
                              AND rh05_recis IS NULL
                              AND rh02_regist = $matricula
                            GROUP BY rh01_admiss, COALESCE(b.h16_dtconc, rh01_admiss), rh02_regist";

            } elseif ($temAssentamento176 == 'f') {
                // Query SEM assentamento 176 para esta matrícula
                $queryTrienio = "SELECT h19_op01 AS operador,
                                       SUM(h16_quant) AS quantidade,
                                       rh01_admiss AS data_admissao,
                                       rh02_regist AS matricula
                                FROM protelac
                                INNER JOIN tipoasse ON CAST(h12_codigo AS VARCHAR) = h19_assent
                                INNER JOIN assenta ON h12_codigo = h16_assent
                                INNER JOIN rhpessoal ON rh01_regist = h16_regist
                                INNER JOIN rhpessoalmov ON rh01_regist = rh02_regist AND rh01_instit = rh02_instit
                                INNER JOIN rhregime ON rh02_codreg = rh30_codreg AND rh02_instit = rh30_instit
                                LEFT JOIN rhpesrescisao ON rh02_seqpes = rh05_seqpes
                                WHERE rh02_anousu = $iAnoCompetencia
                                  AND rh02_mesusu = $iMesCompetencia
                                  AND h19_instit = $iInstituicao
                                  AND rh02_codreg NOT IN (15,17,18,19,207,208,209,257,259,308,457,810,812,813,814,815,819,828,831,836,837)
                                  AND rh30_vinculo = 'A'
                                  AND h19_tipo = 'T'
                                  AND rh05_recis IS NULL
                                  AND rh02_regist = $matricula
                                GROUP BY h19_op01, rh01_admiss, rh02_regist";
            } else {
                $queryTrienio = "SELECT h19_op01 AS operador,
                                       SUM(h16_quant) AS quantidade,
                                       rh01_admiss AS data_admissao,
                                       rh02_regist AS matricula
                                FROM protelac
                                INNER JOIN tipoasse ON CAST(h12_codigo AS VARCHAR) = h19_assent
                                INNER JOIN assenta ON h12_codigo = h16_assent
                                INNER JOIN rhpessoal ON rh01_regist = h16_regist
                                INNER JOIN rhpessoalmov ON rh01_regist = rh02_regist AND rh01_instit = rh02_instit
                                INNER JOIN rhregime ON rh02_codreg = rh30_codreg AND rh02_instit = rh30_instit
                                LEFT JOIN rhpesrescisao ON rh02_seqpes = rh05_seqpes
                                WHERE rh02_anousu = $iAnoCompetencia
                                  AND rh02_mesusu = $iMesCompetencia
                                  AND h19_instit = $iInstituicao
                                  AND rh02_codreg NOT IN (15,17,18,19,207,208,209,257,259,308,457,810,812,813,814,815,819,828,831,836,837)
                                  AND rh30_vinculo = 'A'
                                  AND h19_tipo = 'T'
                                  AND rh05_recis IS NULL
                                  AND rh02_regist = $matricula
                                GROUP BY h19_op01, rh01_admiss, rh02_regist";
            }

            // Executa a query para esta matrícula
            $rsTrienio = db_query($queryTrienio);
            $existeCalculo = (pg_num_rows($rsTrienio)) > 0;

            if ($existeCalculo) {
                $calculoTrienio = db_utils::getCollectionByRecord($rsTrienio);

                if (!empty($calculoTrienio)) {
                    foreach ($calculoTrienio as $trienio) {
                        $operador   = $trienio->operador;
                        $quantidade = $trienio->quantidade;
                        $matricula  = $trienio->matricula;

                        if (!isset($dias_direito_total_por_matricula[$matricula])) {
                            $dias_direito_total_por_matricula[$matricula] = 0;
                        }

                        if (!empty($trienio->data_admissao_trienio)) {
                            // data base é a data de trienio
                            $data_base = $trienio->data_admissao_trienio;

                            // só aplica postergação (+)
                            if ($operador === '+') {
                                $dias_direito_total_por_matricula[$matricula] += $quantidade;
                            }
                        } else {
                            // data base é a data de admissão
                            $data_base = $trienio->data_admissao;

                            // aplica postergacao (+) e averbacao (-)
                            if ($operador === '+') {
                                $dias_direito_total_por_matricula[$matricula] += $quantidade;
                            } else {
                                $dias_direito_total_por_matricula[$matricula] -= $quantidade;
                            }
                        }

                        // calcula a nova data
                        $nova_data_trienio = new DateTime($data_base);

                        if ($dias_direito_total_por_matricula[$matricula] !== 0) {
                            $nova_data_trienio->modify("{$dias_direito_total_por_matricula[$matricula]} days");
                        }

                        $nova_data_trienio_formatado = $nova_data_trienio->format('Y-m-d');

                        $atualiza_data_trienio = "UPDATE rhpessoal SET rh01_trienio = '$nova_data_trienio_formatado' WHERE rh01_instit = $iInstituicao AND rh01_regist = $matricula";
                        db_query($atualiza_data_trienio);
                    }
                }
            }
        } // Fim do while
    } // Fim do if ($iInstituicao == 45)
    // ... resto do código para outras instituições ...
}

function novaDataProgressao($iAnoCompetencia, $iMesCompetencia, $iInstituicao)
{
    $queryProgressao = " SELECT h19_op01       AS operador,
               SUM(h16_quant) AS quantidade,
               rh01_admiss    AS data_admissao,
               rh02_regist    AS matricula
          FROM protelac
          INNER JOIN tipoasse     ON CAST(h12_codigo AS VARCHAR) = h19_assent
          INNER JOIN assenta      ON h12_codigo = h16_assent
          INNER JOIN rhpessoal    ON rh01_regist = h16_regist
          INNER JOIN rhpessoalmov ON rh01_regist = rh02_regist AND rh01_instit = rh02_instit
          INNER JOIN rhregime     ON rh02_codreg = rh30_codreg AND rh02_instit = rh30_instit
          LEFT JOIN rhpesrescisao ON rh02_seqpes = rh05_seqpes
         WHERE rh02_anousu = $iAnoCompetencia
           AND rh02_mesusu = $iMesCompetencia
           AND h19_instit = $iInstituicao
           AND rh02_codreg NOT IN (15,17,18,19,207,208,209,257,259,308,457,810,812,813,814,815,819,828,831,836,837)
           AND rh30_vinculo = 'A'
           AND h19_tipo = 'P'
           AND rh05_recis IS NULL
      GROUP BY 1,3,4
    ";

    $rsProgressao = db_query($queryProgressao);

    if (!$rsProgressao) {
        throw new Exception("Erro na consulta SQL: " . pg_last_error());
    }

    if (pg_num_rows($rsProgressao) > 0) {

        $calculoProgressao = db_utils::getCollectionByRecord($rsProgressao);

        foreach ($calculoProgressao as $progressao) {

            $operador      = $progressao->operador;
            $quantidade    = $progressao->quantidade;
            $data_admissao = $progressao->data_admissao;
            $matricula     = $progressao->matricula;

            if (!isset($dias_direito_total_por_matricula[$matricula])) {
                $dias_direito_total_por_matricula[$matricula] = 0;
            }

            if ($operador === '+') {
                $dias_direito_total_por_matricula[$matricula] += $quantidade;
            } else {
                $dias_direito_total_por_matricula[$matricula] -= $quantidade;
            }

            $nova_data_progressao = new DateTime($data_admissao);
            $nova_data_progressao->modify("{$dias_direito_total_por_matricula[$matricula]} days");
            $nova_data_progressao_formatado = $nova_data_progressao->format('Y-m-d');

            $atualiza_data_progressao = "
                UPDATE rhpessoal
                   SET rh01_progres = '$nova_data_progressao_formatado'
                 WHERE rh01_instit = $iInstituicao
                   AND rh01_regist = $matricula
            ";

            if (!db_query($atualiza_data_progressao)) {
                throw new Exception("Erro ao atualizar progressão para matrícula {$matricula}");
            }
        }

    } else {
        // ?? Nenhuma progressão -> volta para a data de admissão
        $queryAdmissao = " SELECT rh01_regist, rh01_admiss
              FROM rhpessoal
                INNER JOIN rhpessoalmov 
                      ON rh01_regist = rh02_regist AND rh01_instit = rh02_instit
                LEFT JOIN rhpesrescisao ON rh02_seqpes = rh05_seqpes
             WHERE rh02_anousu = $iAnoCompetencia
               AND rh02_mesusu = $iMesCompetencia
               AND rh01_instit = $iInstituicao
               AND rh02_codreg NOT IN (15,17,18,19,207,208,209,257,259,308,457,810,812,813,814,815,819,828,831,836,837)
               AND rh30_vinculo = 'A'
               AND rh05_recis is null
        ";

        $rsAdmissao = db_query($queryAdmissao);

        var_dump($queryAdmissao);

        if (!$rsAdmissao) {
            throw new Exception("Erro na consulta SQL de admissão: " . pg_last_error());
        }

        if (pg_num_rows($rsAdmissao) == 0) {
            throw new Exception("Nenhum registro encontrado para admissão!");
        }

        while ($row = db_utils::fieldsMemory($rsAdmissao, 0)) {
            $matricula     = $row->rh01_regist;
            $data_admissao = $row->rh01_admiss;

            var_dump($matricula, $data_admissao);

            $atualiza_data_progressao = "
                UPDATE rhpessoal
                   SET rh01_progres = '$data_admissao'
                 WHERE rh01_instit = $iInstituicao
                   AND rh01_regist = $matricula
            ";

            if (!db_query($atualiza_data_progressao)) {
                throw new Exception("Erro ao atualizar progressão para matrícula {$matricula} com data de admissão!");
            }
        }
    }
}

function atualizaDatasVazias($iAnoCompetencia, $iMesCompetencia, $iInstituicao)
{
    // Atualiza os triênios vazios com a data de admissão
    $data_trienios_vazias = "UPDATE rhpessoal
                             SET rh01_trienio = rh01_admiss
                             WHERE rh01_instit = $iInstituicao
                               AND rh01_trienio IS NULL
                               AND EXISTS (
                                   SELECT 1
                                   FROM rhpessoalmov
                                   INNER JOIN rhregime
                                       ON rh02_codreg = rh30_codreg
                                       AND rh02_instit = rh30_instit
                                   LEFT JOIN rhpesrescisao
                                       ON rh02_seqpes = rh05_seqpes
                                   WHERE rh02_anousu = $iAnoCompetencia
                                     AND rh02_mesusu = $iMesCompetencia
                                     AND rh02_instit = $iInstituicao
                                     AND rh02_codreg NOT IN (15,17,18,19,810,836,207,208,209,812,813,257,259,814,815,308,819,457,837,828,831)
                                     AND rh30_vinculo = 'A'
                                     AND rh01_regist = rh02_regist
                                     AND rh01_instit = rh02_instit)";

    db_query($data_trienios_vazias);

    // Atualiza as progressões vazias com a data de admissão
    $data_progressoes_vazias = "UPDATE rhpessoal
                                SET rh01_progres = rh01_admiss
                                WHERE rh01_instit = $iInstituicao
                                  AND rh01_progres IS NULL
                                  AND EXISTS (
                                      SELECT 1
                                      FROM rhpessoalmov
                                      INNER JOIN rhregime
                                          ON rh02_codreg = rh30_codreg
                                          AND rh02_instit = rh30_instit
                                      LEFT JOIN rhpesrescisao
                                          ON rh02_seqpes = rh05_seqpes
                                      WHERE rh02_anousu = $iAnoCompetencia
                                        AND rh02_mesusu = $iMesCompetencia
                                        AND rh02_instit = $iInstituicao
                                        AND rh02_codreg NOT IN (15,17,18,19,810,836,207,208,209,812,813,257,259,814,815,308,819,457,837,828,831)
                                        AND rh30_vinculo = 'A'
                                        AND rh01_regist = rh02_regist
                                        AND rh01_instit = rh02_instit)";

    db_query($data_progressoes_vazias);
}

if (isset($_POST['processar'])) {
    $iAnoCompetencia = $_POST['ano'];
    $iMesCompetencia = $_POST['mes'];
    $iInstituicao    = db_getsession("DB_instit");

    $trienioProcessado = false;
    $progressaoProcessada = false;

    try {
        $trienioProcessado = novaDataTrienio($iAnoCompetencia, $iMesCompetencia, $iInstituicao);
    } catch (Exception $e) {
        echo "Não há Protelação do Tipo Triênio cadastrado. Verifique as configurações.";
    }

    try {
        $progressaoProcessada = novaDataProgressao($iAnoCompetencia, $iMesCompetencia, $iInstituicao);
    } catch (Exception $e) {
        echo "Não há Protelação do Tipo Progressão cadastrado. Verifique as configurações.";
    }

    try {
        atualizaDatasVazias($iAnoCompetencia, $iMesCompetencia, $iInstituicao);
        echo "Datas de triênios e progressões vazias atualizadas com sucesso.";
    } catch (Exception $e) {
        echo "Erro ao atualizar datas de triênios e progressões vazias: " . $e->getMessage();
    }
}

?>
<html>

<head>
    <title>Microsist</title>
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
    <meta http-equiv="Expires" CONTENT="0">
    <script language="JavaScript" type="text/javascript" src="scripts/prototype.js"></script>
    <script language="JavaScript" type="text/javascript" src="scripts/scripts.js"></script>
    <script language="JavaScript" type="text/javascript" src="scripts/strings.js"></script>
    <script language="JavaScript" type="text/javascript" src="scripts/classes/DBViewFormularioFolha/CompetenciaFolha.js"></script>
    <link href="estilos.css" rel="stylesheet" type="text/css">
</head>

<body>
    <div class="container">
        <fieldset>
            <legend>Processar Contagem de Tempo</legend>
            <table cellpadding="0" cellspacing="0" class="form-container">
                <tr>
                    <td width="100px">
                        <label>Competência: </label>
                    </td>
                    <td>
                        <input type="text" id="ano" name="ano" style="width: 50px; margin-left: -17px;" placeholder="Ano" />
                        /
                        <input type="text" id="mes" name="mes" style="width: 50px; margin-left: 1px;" placeholder="Mês" />
                    </td>
                </tr>

            </table>
        </fieldset>
        <input type="button" id="btn_processar" onclick="js_processar()" value="Processar" />
    </div>

    <div id="loading" style="display: none; 
                         position: fixed; 
                         top: 50%; 
                         left: 50%; 
                         transform: translate(-50%, -50%); 
                         background-color: #426c8c; 
                         color: #fff;  
                         padding: 10px; 
                         border-radius: 8px; 
                         font-size: 14px; 
                         box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.2); 
                         z-index: 1000;">
        Aguarde, processando...
    </div>

    <script type="text/javascript">
        function js_processar() {
            var ano = document.getElementById('ano').value;
            var mes = document.getElementById('mes').value;

            var loadingElement = document.getElementById('loading');
            loadingElement.style.display = 'block';

            var xhr = new XMLHttpRequest();
            xhr.open('POST', 'rec1_processa_protelacoes001_voltaredonda.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4) {
                    loadingElement.style.display = 'none';

                    if (xhr.status === 200) {
                        alert("Processamento das Protelações executado com sucesso!");
                    } else {
                        alert("Erro ao executar o Processamento das Protelações.");
                    }
                }
            };
            xhr.onerror = function() {
                loadingElement.style.display = 'none';
                alert("Erro ao tentar processar. Verifique sua conexão.");
            };
            xhr.send('processar=true&ano=' + ano + '&mes=' + mes);
        }
    </script>

    <?php db_menu(); ?>

</body>

</html>
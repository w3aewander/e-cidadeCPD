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

require_once(modification("libs/db_stdlib.php"));
require_once(modification("libs/db_conecta.php"));
require_once(modification("libs/db_sessoes.php"));
require_once(modification("libs/db_usuariosonline.php"));
require_once(modification("dbforms/db_funcoes.php"));

db_postmemory($_POST);

$permissao_para_excluir = (isset($_POST['acao']) && $_POST['acao'] == 'sim');

$clmovrel    = new cl_movrel;
$clconvenio  = new cl_convenio;
$clrelac     = new cl_relac;
$clrhpessoal = new cl_rhpessoal;
$clpontofs   = new cl_pontofs;

$db_opcao = 1;
$db_botao = true;
$alertar = false;

function testavalor($valor = null)
{
    if (is_numeric($valor) == true) {
        return $valor;
    } else {
        return 0;
    }
}

if (isset($incluir) || isset($confirma)) {
    try {
        db_inicio_transacao();

        $dadosInclusao = [];
        $qtdRegistrosIncluidos = 0;
        $qtdRegistrosAlterados = 0;

        $dbwhere = " trim(convenio.r56_codrel) = '" . trim($r54_codrel) . "' ";
        $dbwhere .= "and r56_instit = " . db_getsession("DB_instit");

        $instituicao = db_getsession("DB_instit");

        $sql_gerado = $clconvenio->sql_query_relac($r54_codrel, $instituicao);

        $result_dados_posicoes = $clconvenio->sql_record($sql_gerado);

        if ($clconvenio->numrows == 0) {
            throw new Exception("Erro ao buscar informações do convênio");
        }

        db_fieldsmemory($result_dados_posicoes, 0);

        $pos_ano01 = ((int)substr($r56_posano, 0, 3)) - 1; // 3 caracteres da primeira posição do ano
        $pos_ano02 = ((int)substr($r56_posano, 3, 3)) - 1; // 3 caracteres da posição limite do ano
        $cas_ano12 = 0;                                // Quantos caracteres
        // Busca quantidade de caracteres para usar no SUBSTR
        if ($pos_ano01 > -1) {
            for ($i = $pos_ano01; $i <= $pos_ano02; $i++) {
                $cas_ano12++;
            }
        }

        $pos_mes01 = ((int)substr($r56_posmes, 0, 3)) - 1; // 3 caracteres da primeira posição do mes
        $pos_mes02 = ((int)substr($r56_posmes, 3, 3)) - 1; // 3 caracteres da posição limite do mes
        $cas_mes12 = 0;                                // Quantos caracteres
        // Busca quantidade de caracteres para usar no SUBSTR
        if ($pos_mes01 > -1) {
            for ($i = $pos_mes01; $i <= $pos_mes02; $i++) {
                $cas_mes12++;
            }
        }

        $pos_reg01 = ((int)substr($r56_posreg, 0, 3)) - 1; // 3 caracteres da primeira posição do registro
        $pos_reg02 = ((int)substr($r56_posreg, 3, 3)) - 1; // 3 caracteres da posição limite do registro
        $cas_reg12 = 0;                                // Quantos caracteres
        // Busca quantidade de caracteres para usar no SUBSTR
        if ($pos_reg01 > -1) {
            for ($i = $pos_reg01; $i <= $pos_reg02; $i++) {
                $cas_reg12++;
            }
        }

        $pos_eve01 = ((int)substr($r56_poseve, 0, 3)) - 1; // 3 caracteres da primeira posição do relacionamento
        $pos_eve02 = ((int)substr($r56_poseve, 3, 3)) - 1; // 3 caracteres da posição limite do relacionamento
        $cas_eve12 = 0;                                // Quantos caracteres
        // Busca quantidade de caracteres para usar no SUBSTR
        if ($pos_eve01 > -1) {
            for ($i = $pos_eve01; $i <= $pos_eve02; $i++) {
                $cas_eve12++;
            }
        }

        $pos_q0101 = ((int)substr($r56_posq01, 0, 3)) - 1; // 3 caracteres da primeira posição do valor/quantidade 01
        $pos_q0102 = ((int)substr($r56_posq01, 3, 3)) - 1; // 3 caracteres da posição limite do valor/quantidade 01
        $cas_q0112 = 0;                                // Quantos caracteres
        // Busca quantidade de caracteres para usar no SUBSTR
        if ($pos_q0101 > -1) {
            for ($i = $pos_q0101; $i <= $pos_q0102; $i++) {
                $cas_q0112++;
            }
        }

        $pos_q0201 = ((int)substr($r56_posq02, 0, 3)) - 1; // 3 caracteres da primeira posição do valor/quantidade 02
        $pos_q0202 = ((int)substr($r56_posq02, 3, 3)) - 1; // 3 caracteres da posição limite do valor/quantidade 02
        $cas_q0212 = 0;                                // Quantos caracteres
        // Busca quantidade de caracteres para usar no SUBSTR
        if ($pos_q0201 > -1) {
            for ($i = $pos_q0201; $i <= $pos_q0202; $i++) {
                $cas_q0212++;
            }
        }

        $pos_q0301 = ((int)substr($r56_posq03, 0, 3)) - 1; // 3 caracteres da primeira posição do valor/quantidade 03
        $pos_q0302 = ((int)substr($r56_posq03, 3, 3)) - 1; // 3 caracteres da posição limite do valor/quantidade 03
        $cas_q0312 = 0;                                // Quantos caracteres
        // Busca quantidade de caracteres para usar no SUBSTR
        if ($pos_q0301 > -1) {
            for ($i = $pos_q0301; $i <= $pos_q0302; $i++) {
                $cas_q0312++;
            }
        }
        $pos_rubrica = ((int)substr($r56_posrubrica, 0, 3)) - 1; // 3 caracteres da primeira posição do valor/quantidade 03
        $pos_rubrica02 = ((int)substr($r56_posrubrica, 3, 3)) - 1; // 3 caracteres da posição limite do valor/quantidade 03
        $cas_rubrica = 0;                                // Quantos caracteres
        // Busca quantidade de caracteres para usar no SUBSTR
        if ($pos_rubrica > -1) {
            for ($i = $pos_rubrica; $i <= $pos_rubrica02; $i++) {
                $cas_rubrica++;
            }
        }

        $pos_cpf = ((int)substr($r56_poscpf, 0, 3)) - 1;
        $pos_cpf2 = ((int)substr($r56_poscpf, 3, 3)) - 1;
        $cas_cpf = 0;

        if ($pos_cpf > -1) {
            for ($i = $pos_cpf; $i <= $pos_cpf2; $i++) {
                $cas_cpf++;
            }
        }

        if (!isset($confirma)) {
            // Nome do novo arquivo
            $nomearq = $_FILES["r56_dirarq"]["name"];

            // Nome do arquivo temporário gerado no /tmp
            $nometmp = $_FILES["r56_dirarq"]["tmp_name"];

            // Seta o nome do arquivo destino do upload
            $arquivoprocessa = "tmp/ret$nomearq";

            // Faz um upload do arquivo para o local especificado
            if (!move_uploaded_file($nometmp, $arquivoprocessa)) {
                throw new Exception("Erro ao realizar upload do arquivo.");
            };
        } else {
            $arquivoprocessa = $confirma;
        }

        // Abre o arquivo
        if (!$ponteiro = fopen("$arquivoprocessa", "r")) {
            throw new Exception("Erro ao abrir o arquivo para leitura.");
        }

        if (!empty($arquivoprocessa)) {
            $linhas = file($arquivoprocessa);

            $linhas_filtradas = array_filter($linhas, function ($linha) {
                return trim($linha) !== '';
            });

            file_put_contents($arquivoprocessa, implode("", $linhas_filtradas));
        }

        $totalLinhasArq = count(file($arquivoprocessa));
        $totalLinhasArq -= $r56_linhastrailler;
        $contadorLinhas = 0;
        while (!feof($ponteiro)) {
            $poslinha = fgets($ponteiro, 4096);
            $contadorLinhas++;
            if ($poslinha == "" || $contadorLinhas <= $r56_linhasheader || $contadorLinhas > $totalLinhasArq) {
                continue;
            }

            $ano = trim(substr($poslinha, $pos_ano01, $cas_ano12));
            $mes = trim(substr($poslinha, $pos_mes01, $cas_mes12));
            $reg = trim(substr($poslinha, $pos_reg01, $cas_reg12));
            $cpf = trim(substr($poslinha, $pos_cpf, $cas_cpf));
            $eve = trim(substr($poslinha, $pos_eve01, $cas_eve12));

            if (empty($reg)) {
                $dbwhere = "cgm.z01_cgccpf = $1 and rh01_instit = $2";
                $params = array($cpf, db_getsession("DB_instit"));
                $sql = $clrhpessoal->sql_query(null, 'rh01_regist', null, $dbwhere);

                $rsReg = db_query_params($sql, $params);

                if (pg_num_rows($rsReg) > 0) {
                    $servidor = pg_fetch_object($rsReg);
                    $reg = $servidor->rh01_regist;
                } else {
                    continue;
                }
            }

            if (trim($eve) != trim($r54_codeve) && trim($eve) != "") {
                continue;
            }

            $q01 = trim(substr($poslinha, $pos_q0101, $cas_q0112));
            $q02 = trim(substr($poslinha, $pos_q0201, $cas_q0212));
            $q03 = trim(substr($poslinha, $pos_q0301, $cas_q0312));

            $rubrica = trim(substr($poslinha, $pos_rubrica, $cas_rubrica));

            if (trim($ano) == "") {
                $ano = DBPessoal::getAnoFolha();
            }
            if (trim($mes) == "") {
                $mes = DBPessoal::getMesFolha();
            }

            $where_exclui = "";
            if (trim($r54_codeve) != "") {
                $where_exclui = " and trim(r54_codeve) = '" . trim($r54_codeve) . "'";
            }
        
            /*
             * A lógica abaixo foi alterada para agora a rotina de importação do arquivo tem a opção de escolher se deve ou nao excluir o registro
             *
            */

            if ($permissao_para_excluir) {
                if (isset($confirma) && !isset($exclusao_confirma)) {
                    $clmovrel->excluir(
                        null,
                        "r54_anomes='" . $ano . $mes . "'
                        and r54_instit = " . db_getsession("DB_instit") . "
                        and trim(r54_codrel) = '" . trim($r54_codrel) . "'" . $where_exclui
                    );
                    if ($clmovrel->erro_status == 0) {
                        $erro_msg = $clmovrel->erro_msg;
                        $sqlerro = true;
                        break;
                    }
                    $exclusao_confirma = true;
                } elseif (!isset($confirma) && !isset($nao_alertar)) {
                    $result_ja_processado = $clmovrel->sql_record(
                        $clmovrel->sql_query_file(
                            null,
                            "*",
                            "",
                            "r54_anomes='" . $ano . $mes . "'
                            and r54_instit = " . db_getsession("DB_instit") . "
                            and trim(r54_codrel) = '" . trim($r54_codrel) . "'" . $where_exclui
                        )
                    );
                    if ($clmovrel->numrows > 0) {
                        $alertar = true;
                        if (isset($incluir)) {
                            unset($incluir);
                        }
                        throw new Exception("Encontrados registros importados");
                    }
                    $nao_alertar = true;
                }
            } 

            if (trim($q01) == "") {
                $q01 = 0;
            } else {
                $val = $q01;
                $q01 = substr($val, 0, -2) . "." . substr($val, -2);
            }
            if (trim($q02) == "") {
                $q02 = 0;
            } else {
                $val = $q02;
                $q02 = substr($val, 0, -2) . "." . substr($val, -2);
            }
            if (trim($q03) == "") {
                $q03 = 0;
            } else {
                $val = $q03;
                $q03 = substr($val, 0, -2) . "." . substr($val, -2);
            }

            /*
             * Verificamos se existe rubrica configurada
             * Setamos o valor de acordo com a rubrica configurada
             */
            if ($rubrica == $r55_rubr02 && !empty($rubrica)) {
                $q02 = $q01;
                $q01 = 0;
            }

            if ($rubrica == $r55_rubr03 && !empty($rubrica)) {
                $q03 = $q01;
                $q01 = 0;
                $q02 = 0;
            }

            $idRegistro = $ano . "/" . $mes . "-" . $reg;

            $dados = new stdClass();
            $dados->r54_anomes = $ano . $mes;
            $dados->r54_codrel = $r54_codrel;
            $dados->r54_regist = testavalor($reg);
            $dados->r54_codeve = $eve;
            $dados->r54_quant1 = testavalor($q01);
            $dados->r54_quant2 = testavalor($q02);
            $dados->r54_quant3 = testavalor($q03);
            $dados->r54_lancad = "false";
            $dados->r54_instit = db_getsession("DB_instit");

            if (key_exists($idRegistro, $dadosInclusao)) {
                if (!empty($q01)) {
                    $dadosInclusao[$idRegistro]->r54_quant1 += "$q01";
                }
                if (!empty($q02)) {
                    $dadosInclusao[$idRegistro]->r54_quant2 += "$q02";
                }
                if (!empty($q03)) {
                    $dadosInclusao[$idRegistro]->r54_quant3 += "$q03";
                }
            } else {
                $dadosInclusao[$idRegistro] = $dados;
            }
        }

        foreach ($dadosInclusao as $item) {
            $where = " r54_anomes = $1 and r54_codrel = $2 and r54_regist = $3 and r54_codeve = $4 ";
            $params = array(
                $item->r54_anomes,
                $item->r54_codrel,
                $item->r54_regist,
                $item->r54_codeve 
            );
            $clmovrel = new cl_movrel;
            $sql = $clmovrel->sql_query_file(null, "*", null, $where);
            $rsDadosIncluidos = db_query_params($sql, $params);

            if (pg_num_rows($rsDadosIncluidos) > 0) {
                $dadosIncluidos = db_utils::fieldsMemory($rsDadosIncluidos, 0);

                $clmovrel = new cl_movrel;
                if ($acao == "somar") {
                    $clmovrel->r54_quant1 = $dadosIncluidos->r54_quant1 + $item->r54_quant1;
                    $clmovrel->r54_quant2 = $dadosIncluidos->r54_quant2 + $item->r54_quant2;
                    $clmovrel->r54_quant3 = $dadosIncluidos->r54_quant3 + $item->r54_quant3;
                } else {
                    $clmovrel->r54_quant1 = $item->r54_quant1;
                    $clmovrel->r54_quant2 = $item->r54_quant2;
                    $clmovrel->r54_quant3 = $item->r54_quant3;
                }
                $clmovrel->r54_sequencial = $dadosIncluidos->r54_sequencial;
                $clmovrel->alterar($dadosIncluidos->r54_sequencial);
                if ($clmovrel->erro_status == 0) {
                    throw new Exception($clmovrel->erro_msg);
                }
                $qtdRegistrosAlterados++;
            } else {
                $clmovrel = new cl_movrel;
                $clmovrel->r54_anomes = "$item->r54_anomes";
                $clmovrel->r54_codrel = "$item->r54_codrel";
                $clmovrel->r54_regist = $item->r54_regist;
                $clmovrel->r54_codeve = "$item->r54_codeve";
                $clmovrel->r54_quant1 = "$item->r54_quant1";
                $clmovrel->r54_quant2 = "$item->r54_quant2";
                $clmovrel->r54_quant3 = "$item->r54_quant3";
                $clmovrel->r54_lancad = $item->r54_lancad;
                $clmovrel->r54_instit = $item->r54_instit;
                $clmovrel->incluir(null);
                if ($clmovrel->erro_status == 0) {
                    throw new Exception($clmovrel->erro_msg);
                }
                $qtdRegistrosIncluidos++;
            }
        }

        $msg = "{$qtdRegistrosIncluidos} registros incluídos.\n";
        $msg .= "{$qtdRegistrosAlterados} registros alterados.";
        db_msgbox($msg);

        db_fim_transacao(false);
    } catch (Exception $e) {
        db_fim_transacao(true);
        if (!$alertar) {
            db_msgbox($e->getMessage());
        }
    }
}

?>
<html>

<head>
    <title>Microsist</title>
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
    <meta http-equiv="Expires" CONTENT="0">
    <script language="JavaScript" type="text/javascript" src="scripts/scripts.js"></script>
    <link href="estilos.css" rel="stylesheet" type="text/css">
</head>

<body>
    <div class="container">
        <?php
        include(modification("forms/db_frmmovrel.php"));
        ?>
    </div>
    <?php

    if ($alertar) {
        echo "<script>
        if (confirm('Usuário:\\n\\nAlguns movimentos deste arquivo já foram processados.\\nReprocessá-lo excluindo dados anteriores?')) {
            obj=document.createElement('input');
            obj.setAttribute('name', 'confirma');
            obj.setAttribute('type', 'hidden');
            obj.setAttribute('value', '$arquivoprocessa');
            document.form1.appendChild(obj);
            document.form1.submit();
        }
        </script>";
    }

    db_menu();
    ?>
</body>

</html>
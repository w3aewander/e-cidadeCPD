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

require_once(modification("libs/db_stdlibwebseller.php"));
require_once(modification("libs/db_stdlib.php"));
require_once(modification("libs/db_conecta.php"));
require_once(modification("libs/db_sessoes.php"));
require_once(modification("libs/db_utils.php"));
require_once(modification("libs/db_app.utils.php"));
require_once(modification("libs/db_usuariosonline.php"));
require_once(modification("dbforms/db_funcoes.php"));
require_once(modification("libs/db_jsplibwebseller.php"));

parse_str($_SERVER["QUERY_STRING"]);
db_postmemory($_POST);

$iAnoEtapaCenso = null;

$clturma               = new cl_turma;
$clescola              = new cl_escola;
$clescolaestrutura     = new cl_escolaestrutura;
$clmatricula           = new cl_matricula;
$clregencia            = new cl_regencia;
$clalunotransfturma    = new cl_alunotransfturma;
$clregenteconselho     = new cl_regenteconselho;
$cltrocaserie          = new cl_trocaserie;
$clturmaturnoadicional = new cl_turmaturnoadicional;
$clparecerturma        = new cl_parecerturma;
$clregenciahorario     = new cl_regenciahorario;
$clregenciaperiodo     = new cl_regenciaperiodo;
$clturmaserieregimemat = new cl_turmaserieregimemat;
$clturmaturnoreferente = new cl_turmaturnoreferente;
$clturmalog            = new cl_turmalog;
$cldiarioclassebncc    = new cl_diario_classe_bncc;
$clturmacensoturma     = new cl_turmacensoturma;
$oDaoTurmaCensoEtapa   = new cl_turmacensoetapa;
$db_botao              = false;
$db_botao2             = true;
$db_opcao              = 33;
$db_opcao1             = 3;
$codigoescola          = db_getsession("DB_coddepto");
$lTemPermissao = db_permissaomenu(db_getsession("DB_anousu"), db_getsession('DB_modulo'), 228385);


$oDaoDiarioClasseRegenciaHorario = new cl_diarioclasseregenciahorario();
$oDaoDiarioClasseAlunoFalta      = new cl_diarioclassealunofalta();
$oDaoDiarioClasse                = new cl_diarioclasse();

if (isset($excluir)) {
    $db_opcao = 3;
    $db_opcao1= 3;
    $result1  = $clmatricula->sql_record($clmatricula->sql_query("", "ed60_i_codigo", "", " ed60_i_turma = $ed57_i_codigo"));

    if ($clmatricula->numrows > 0) {
        db_msgbox("Turma $ed57_c_descr não pode ser excluída, pois possui matrículas vinculadas!");
        $db_opcao = 33;
    } else {
        try {
            db_inicio_transacao();

          /**
           * Exclui o vínculo da turma com o censo
           */
            $sWhereTurmaCensoEtapa = " ed132_turma = {$ed57_i_codigo}";
            $oDaoTurmaCensoEtapa->excluir(null, $sWhereTurmaCensoEtapa);

            if ($oDaoTurmaCensoEtapa->erro_status == 0) {
                $sMensagemErro = "Erro ao excluir vínculo da turma com o censo.\\nErro técnico : {$oDaoTurmaCensoEtapa->erro_msg}";
                throw new BusinessException($sMensagemErro);
            }

          /**
           * Excluir dados das faltas dos alunos
           */
          /**
           * select na diarioclasseregenciahorario pelo codigo das regencias
           *  - excluir diarioclassealunofalta
           *  - excluir diarioclasseregenciahorario
           *  - diarioclasse
           */
            $sWhereDiarioClasse  = "ed59_i_turma = {$ed57_i_codigo}";
            $sSqlDiarioClasse    = $oDaoDiarioClasseRegenciaHorario->sql_query(
                null,
                "diarioclasseregenciahorario.*",
                null,
                $sWhereDiarioClasse
            );

            $rsDiarioClasse     = $oDaoDiarioClasseRegenciaHorario->sql_record($sSqlDiarioClasse);
            $iTotalLinhasDiario = $oDaoDiarioClasseRegenciaHorario->numrows;

            if ($iTotalLinhasDiario > 0) {
                  $aDiarioClasseExcluidos = array();
                for ($iDiario = 0; $iDiario < $iTotalLinhasDiario; $iDiario++) {
                    $oDadosDiarioClasse       = db_utils::fieldsMemory($rsDiarioClasse, $iDiario);
                    $aDiarioClasseExcluidos[] = $oDadosDiarioClasse->ed302_diarioclasse;

                  /**
                   * Excluir diarioalunofalta
                   *
                   */
                    $sWhereExcluirDiarioClasseAlunoFalta = "ed301_diarioclasseregenciahorario = {$oDadosDiarioClasse->ed302_sequencial}";
                    $oDaoDiarioClasseAlunoFalta->excluir(null, $sWhereExcluirDiarioClasseAlunoFalta);
                    if ($oDaoDiarioClasseAlunoFalta->erro_status == 0) {
                        $sMensagemErro = "Erro ao excluir faltas do aluno.\\nErro técnico : {$oDaoDiarioClasseAlunoFalta->erro_msg}";
                        throw new BusinessException($sMensagemErro);
                    }

                  /**
                   * Excluir da diarioclasseregenciahorario
                   */
                    $oDaoDiarioClasseRegenciaHorario->excluir($oDadosDiarioClasse->ed302_sequencial);
                    if ($oDaoDiarioClasseRegenciaHorario->erro_status == 0) {
                        $sMensagemErro  = "Erro ao excluir periodos de aula do aluno. \\n";
                        $sMensagemErro .= "Erro técnico : {$oDaoDiarioClasseRegenciaHorario->erro_msg}";
                        throw new BusinessException($sMensagemErro);
                    }

                    unset($oDadosDiarioClasse);
                }

                  $sDiarioClasseExcluir = implode(",", $aDiarioClasseExcluidos);
                  $oDaoDiarioClasse->excluir(null, "ed300_sequencial in ({$sDiarioClasseExcluir})");

                if ($oDaoDiarioClasse->erro_status == 0) {
                    $sMensagemErro  = "Erro ao excluir dados do diario de classe do professor.\\n";
                    $sMensagemErro .= "Erro técnico : {$oDaoDiarioClasse->erro_msg}";
                    throw new BusinessException($sMensagemErro);
                }
            }

            $clregenciahorario->excluir(
                "",
                " ed58_i_regencia in (select ed59_i_codigo from
                                                                           regencia where ed59_i_turma = $ed57_i_codigo)"
            );
            if ($clregenciahorario->erro_status == 0) {
                  $sMensagemErro   = "Períodos da regência nao excluídos.\\n ";
                  $sMensagemErro  .= "Erro Técnico : {$clregenciahorario->erro_msg}";
                  throw new BusinessException($sMensagemErro);
            }

            $clregenciaperiodo->excluir(
                "",
                " ed78_i_regencia  in (select ed59_i_codigo from
                                                                            regencia where ed59_i_turma = $ed57_i_codigo)"
            );

            if ($clregenciaperiodo->erro_status == 0) {
                  $sMensagemErro   = "Erro ao excluir aulas dadas da regência.\\n ";
                  $sMensagemErro  .= "Erro Técnico : {$clregenciaperiodo->erro_msg}";
                  throw new BusinessException($sMensagemErro);
            }

            /**
             * Excluir vínculo da regencia com a diario_classe_bncc
             */
            $cldiarioclassebncc->excluir(
                "",
                " ed155_codigo in (select ed155_codigo
                         from diario_classe_bncc
                         where ed155_regencia in (select ed59_i_codigo from regencia where ed59_i_turma = $ed57_i_codigo)) "
            );

            if ($cldiarioclassebncc->erro_status == 0) {
                $sMensagemErro   = "Erro ao excluir diários de classe BNCC.\\n ";
                $sMensagemErro  .= "Erro Técnico : {$cldiarioclassebncc->erro_msg}";
                throw new BusinessException($sMensagemErro);
            }


            /**
             * Autor: Uemerson Santana
             * Data: 17/02/2026
             * Demanda: 17597
             * Razao: Excluir dados academicos (diario e dependencias) vinculados as regencias
             *        da turma antes de excluir as regencias, evitando erro de FK quando existem
             *        registros orfaos de diarioaluno/diario que nao foram removidos ao transferir
             *        alunos para outra turma.
             */
            $sSqlDiarios = "SELECT ed95_i_codigo FROM diario WHERE ed95_i_regencia IN (SELECT ed59_i_codigo FROM regencia WHERE ed59_i_turma = {$ed57_i_codigo})";
            $rsDiarios = db_query($sSqlDiarios);
            if ($rsDiarios && pg_num_rows($rsDiarios) > 0) {
                $aDiarios = array();
                while ($rowDiario = pg_fetch_object($rsDiarios)) {
                    $aDiarios[] = $rowDiario->ed95_i_codigo;
                }
                $sDiariosIn = implode(",", $aDiarios);

                $sWhereAvaliacoes = "ed72_i_diario IN ({$sDiariosIn})";
                $sWhereResultados = "ed73_i_diario IN ({$sDiariosIn})";

                // 1. amparo (depende de diario)
                $clAmparo = new cl_amparo();
                $clAmparo->excluir("", "ed81_i_diario IN ({$sDiariosIn})");
                if ($clAmparo->erro_status == 0) {
                    throw new BusinessException("Erro ao excluir amparos.\\nErro Tecnico: {$clAmparo->erro_msg}");
                }

                // 2. diariofinal (depende de diario)
                $clDiarioFinal = new cl_diariofinal();
                $clDiarioFinal->excluir("", "ed74_i_diario IN ({$sDiariosIn})");
                if ($clDiarioFinal->erro_status == 0) {
                    throw new BusinessException("Erro ao excluir diario final.\\nErro Tecnico: {$clDiarioFinal->erro_msg}");
                }

                // 3. parecerresult (depende de diarioresultado)
                $clParecerResult = new cl_parecerresult();
                $clParecerResult->excluir("", "ed63_i_diarioresultado IN (SELECT ed73_i_codigo FROM diarioresultado WHERE {$sWhereResultados})");
                if ($clParecerResult->erro_status == 0) {
                    throw new BusinessException("Erro ao excluir parecer resultado.\\nErro Tecnico: {$clParecerResult->erro_msg}");
                }

                // 4. diarioresultadorecuperacao (depende de diarioresultado)
                $clDiarioResultadoRecup = new cl_diarioresultadorecuperacao();
                $clDiarioResultadoRecup->excluir("", "ed116_diarioresultado IN (SELECT ed73_i_codigo FROM diarioresultado WHERE {$sWhereResultados})");
                if ($clDiarioResultadoRecup->erro_status == 0) {
                    throw new BusinessException("Erro ao excluir diario resultado recuperacao.\\nErro Tecnico: {$clDiarioResultadoRecup->erro_msg}");
                }

                // 5. diarioresultado (depende de diario)
                $clDiarioResultado = new cl_diarioresultado();
                $clDiarioResultado->excluir("", $sWhereResultados);
                if ($clDiarioResultado->erro_status == 0) {
                    throw new BusinessException("Erro ao excluir diario resultado.\\nErro Tecnico: {$clDiarioResultado->erro_msg}");
                }

                // 6. transfaprov (depende de diarioavaliacao)
                $clTransfAprov = new cl_transfaprov();
                $clTransfAprov->excluir("", "ed251_i_diarioorigem IN (SELECT ed72_i_codigo FROM diarioavaliacao WHERE {$sWhereAvaliacoes}) OR ed251_i_diariodestino IN (SELECT ed72_i_codigo FROM diarioavaliacao WHERE {$sWhereAvaliacoes})");
                if ($clTransfAprov->erro_status == 0) {
                    throw new BusinessException("Erro ao excluir transferencias de aproveitamento.\\nErro Tecnico: {$clTransfAprov->erro_msg}");
                }

                // 7. pareceraval (depende de diarioavaliacao)
                $clParecerAval = new cl_pareceraval();
                $clParecerAval->excluir("", "ed93_i_diarioavaliacao IN (SELECT ed72_i_codigo FROM diarioavaliacao WHERE {$sWhereAvaliacoes})");
                if ($clParecerAval->erro_status == 0) {
                    throw new BusinessException("Erro ao excluir parecer avaliacao.\\nErro Tecnico: {$clParecerAval->erro_msg}");
                }

                // 8. abonofalta (depende de diarioavaliacao)
                $clAbonoFalta = new cl_abonofalta();
                $clAbonoFalta->excluir("", "ed80_i_diarioavaliacao IN (SELECT ed72_i_codigo FROM diarioavaliacao WHERE {$sWhereAvaliacoes})");
                if ($clAbonoFalta->erro_status == 0) {
                    throw new BusinessException("Erro ao excluir abonos de falta.\\nErro Tecnico: {$clAbonoFalta->erro_msg}");
                }

                // 9. diarioavaliacao (depende de diario)
                $clDiarioAvaliacao = new cl_diarioavaliacao();
                $clDiarioAvaliacao->excluir("", $sWhereAvaliacoes);
                if ($clDiarioAvaliacao->erro_status == 0) {
                    throw new BusinessException("Erro ao excluir diario avaliacao.\\nErro Tecnico: {$clDiarioAvaliacao->erro_msg}");
                }

                // 10. aprovconselho (depende de diario)
                $clAprovConselho = new cl_aprovconselho();
                $clAprovConselho->excluir("", "ed253_i_diario IN ({$sDiariosIn})");
                if ($clAprovConselho->erro_status == 0) {
                    throw new BusinessException("Erro ao excluir aprovacao conselho.\\nErro Tecnico: {$clAprovConselho->erro_msg}");
                }

                // 11. diarioregracalculo (depende de diario)
                $clDiarioRegraCalculo = new cl_diarioregracalculo();
                $clDiarioRegraCalculo->excluir("", "ed125_diario IN ({$sDiariosIn})");
                if ($clDiarioRegraCalculo->erro_status == 0) {
                    throw new BusinessException("Erro ao excluir regras de calculo.\\nErro Tecnico: {$clDiarioRegraCalculo->erro_msg}");
                }

                // 12. diarioavaliacaoalternativa (depende de diario)
                $clDiarioAvalAlt = new cl_diarioavaliacaoalternativa();
                $clDiarioAvalAlt->excluir("", "ed136_diario IN ({$sDiariosIn})");
                if ($clDiarioAvalAlt->erro_status == 0) {
                    throw new BusinessException("Erro ao excluir avaliacao alternativa.\\nErro Tecnico: {$clDiarioAvalAlt->erro_msg}");
                }

                // 13. avaliacaoparcial (depende de diario)
                $rsAvalParcial = db_query("DELETE FROM avaliacaoparcial WHERE ed341_diario IN ({$sDiariosIn})");
                if (!$rsAvalParcial) {
                    throw new BusinessException("Erro ao excluir avaliacao parcial.\\nErro Tecnico: " . pg_last_error());
                }
                // 14. resultadoparcial (depende de diario)
                $rsResultParcial = db_query("DELETE FROM resultadoparcial WHERE ed343_diario IN ({$sDiariosIn})");
                if (!$rsResultParcial) {
                    throw new BusinessException("Erro ao excluir resultado parcial.\\nErro Tecnico: " . pg_last_error());
                }
                // 15. diario (depende de regencia)
                $clDiario = new cl_diario();
                $clDiario->excluir("", "ed95_i_codigo IN ({$sDiariosIn})");
                if ($clDiario->erro_status == 0) {
                    throw new BusinessException("Erro ao excluir diarios.\\nErro Tecnico: {$clDiario->erro_msg}");
                }
            }

            // 16. diarioalunoresultadofinal (depende de diarioaluno)
            $clDiarioAlunoResFinal = new cl_diarioalunoresultadofinal();
            $clDiarioAlunoResFinal->excluir("", "ed165_diarioaluno IN (SELECT ed161_codigo FROM diarioaluno WHERE ed161_turma = {$ed57_i_codigo})");
            if ($clDiarioAlunoResFinal->erro_status == 0) {
                throw new BusinessException("Erro ao excluir resultado final do aluno.\\nErro Tecnico: {$clDiarioAlunoResFinal->erro_msg}");
            }

            // 17. diarioarea (depende de diarioaluno)
            $clDiarioArea = new cl_diarioarea();
            $clDiarioArea->excluir("", "ed162_diarioaluno IN (SELECT ed161_codigo FROM diarioaluno WHERE ed161_turma = {$ed57_i_codigo})");
            if ($clDiarioArea->erro_status == 0) {
                throw new BusinessException("Erro ao excluir diario area.\\nErro Tecnico: {$clDiarioArea->erro_msg}");
            }

            // 18. diarioaluno (depende de turma)
            $clDiarioAluno = new cl_diarioaluno();
            $clDiarioAluno->excluir("", "ed161_turma = {$ed57_i_codigo}");
            if ($clDiarioAluno->erro_status == 0) {
                throw new BusinessException("Erro ao excluir diario aluno.\\nErro Tecnico: {$clDiarioAluno->erro_msg}");
            }

            // 19. progressaoparcialalunoturmaregencia (depende de regencia)
            $clProgParcialTurmaReg = new cl_progressaoparcialalunoturmaregencia();
            $clProgParcialTurmaReg->excluir("", "ed115_regencia IN (SELECT ed59_i_codigo FROM regencia WHERE ed59_i_turma = {$ed57_i_codigo})");
            if ($clProgParcialTurmaReg->erro_status == 0) {
                throw new BusinessException("Erro ao excluir progressao parcial turma regencia.\\nErro Tecnico: {$clProgParcialTurmaReg->erro_msg}");
            }

            $clregencia->excluir("", " ed59_i_turma = $ed57_i_codigo");
            if ($clregencia->erro_status == 0) {
                $sMensagemErro   = "Erro ao Excluir regências da turma.\\n ";
                $sMensagemErro  .= "Erro Técnico : {$clregencia->erro_msg}";
                throw new BusinessException($sMensagemErro);
            }

            $rsAlunoPossib = db_query("UPDATE alunopossib SET ed79_i_turmaant = null WHERE ed79_i_turmaant = $ed57_i_codigo");
            if (!$rsAlunoPossib) {
                $sMensagemErro   = "Erro ao alterar turma anterior dos alunos.\\n ";
                $sMensagemErro  .= "Erro Técnico : ".pg_last_error();
                throw new BusinessException($sMensagemErro);
            }

            $rsMatricula = db_query("UPDATE matricula SET ed60_i_turmaant = null WHERE ed60_i_turmaant = $ed57_i_codigo");
            if (!$rsMatricula) {
                $sMensagemErro   = "Erro ao alterar turma anterior das matricula dos alunos.\\n ";
                $sMensagemErro  .= "Erro Técnico : ".pg_last_error();
                throw new BusinessException($sMensagemErro);
            }

            $clalunotransfturma->excluir("", "ed69_i_turmaorigem = $ed57_i_codigo or ed69_i_turmadestino = $ed57_i_codigo");
            if ($clalunotransfturma->erro_status == 0) {
                $sMensagemErro   = "Erro ao excluir transferencias de turma em qual a turma está envolvida.\\n ";
                $sMensagemErro  .= "Erro Técnico : {$clalunotransfturma->erro_msg}";
                throw new BusinessException($sMensagemErro);
            }

            $clparecerturma->excluir("", " ed105_i_turma = $ed57_i_codigo");
            if ($clparecerturma->erro_status == 0) {
                $sMensagemErro   = "Erro ao excluir pareceres vinculados a turma.\\n ";
                $sMensagemErro  .= "Erro Técnico : {$clparecerturma->erro_msg}";
                throw new BusinessException($sMensagemErro);
            }

            $clregenteconselho->excluir("", "ed235_i_turma = $ed57_i_codigo");
            if ($clregenteconselho->erro_status == 0) {
                $sMensagemErro   = "Erro ao excluir conselheiro da turma.\\n ";
                $sMensagemErro  .= "Erro Técnico : {$clregenteconselho->erro_msg}";
                throw new BusinessException($sMensagemErro);
            }

            $cltrocaserie->excluir("", "ed101_i_turmaorig = $ed57_i_codigo or ed101_i_turmadest = $ed57_i_codigo");
            if ($cltrocaserie->erro_status == 0) {
                $sMensagemErro   = "Erro ao excluir progressoes/avanços vínculados a turma.\\n ";
                $sMensagemErro  .= "Erro Técnico : {$cltrocaserie->erro_msg}";
                throw new BusinessException($sMensagemErro);
            }

            $clturmaturnoadicional->excluir("", "ed246_i_turma = $ed57_i_codigo");
            if ($clturmaturnoadicional->erro_status == 0) {
                $sMensagemErro   = "Erro ao excluir turno da turma.\\n ";
                $sMensagemErro  .= "Erro Técnico : {$clturmaturnoadicional->erro_msg}";
                throw new BusinessException($sMensagemErro);
            }

            $clturmaserieregimemat->excluir("", "ed220_i_turma = $ed57_i_codigo");
            if ($clturmaserieregimemat->erro_status == 0) {
                $sMensagemErro   = "Erro ao excluir etapas da turma.\\n ";
                $sMensagemErro  .= "Erro Técnico : {$clturmaserieregimemat->erro_msg}";
                throw new BusinessException($sMensagemErro);
            }

            $clturmalog->excluir("", "ed287_i_turma = $ed57_i_codigo");
            if ($clturmalog->erro_status == 0) {
                $sMensagemErro   = "Erro ao excluir logs da turma.\\n ";
                $sMensagemErro  .= "Erro Técnico : {$clturmalog->erro_msg}";
                throw new BusinessException($sMensagemErro);
            }

            $clturmaturnoreferente->excluir("", "ed336_turma = $ed57_i_codigo");
            if ($clturmaturnoreferente->erro_status == 0) {
                $sMensagemErro  = "Erro ao exlcuir vínculo com o turno referente a turma.";
                $sMensagemErro .= "Erro Técnico : {$clturmaturnoreferente->erro_msg}";
                throw new BusinessException($sMensagemErro);
            }

            /**
             * Excluir turmacensoturma
             */
            $clturmacensoturma->excluir(
                "",
                " ed343_turma = $ed57_i_codigo "
            );

            if ($clturmacensoturma->erro_status == 0) {
                $sMensagemErro   = "Erro ao excluir turmas do censo.\\n ";
                $sMensagemErro  .= "Erro Técnico : {$clturmacensoturma->erro_msg}";
                throw new BusinessException($sMensagemErro);
            }

            $clturma->excluir($ed57_i_codigo);
            if ($clturma->erro_status == 0) {
                $sMensagemErro   = "Erro ao excluir turma.\\n ";
                $sMensagemErro  .= "Erro Técnico : {$clturma->erro_msg}";
                throw new BusinessException($sMensagemErro);
            }

            db_fim_transacao(false);
        } catch (BusinessException $eBusinnes) {
            $clturma->erro_status = "0";
            $clturma->erro_msg = $eBusinnes->getMessage();
            db_fim_transacao(true);
        }
    }
} elseif (isset($chavepesquisa)) {
    $db_opcao  = 3;
    $db_opcao1 = 3;

    $sWhereTurmaCensoEtapa  = " ed132_turma = {$chavepesquisa}";
    $sSqlTurmaCensoEtapa    = $oDaoTurmaCensoEtapa->sql_query_file(null, "ed132_ano", null, $sWhereTurmaCensoEtapa);
    $rsTurmaCensoEtapa      = db_query($sSqlTurmaCensoEtapa);

    if (!$rsTurmaCensoEtapa) {
        throw new DBException("Não foi possivel buscar o vinculo da turma com o censo.");
    }

    if (pg_num_rows($rsTurmaCensoEtapa) == 0) {
        throw new DBException("Não há vinculos do censo com a turma.");
    }

    $iAnoEtapaCenso = db_utils::fieldsMemory($rsTurmaCensoEtapa, 0)->ed132_ano;

    $result    = $clturma->sql_record($clturma->sql_query_turma_etapa_censo($chavepesquisa, $iAnoEtapaCenso));

    db_fieldsmemory($result, 0);
    $db_botao  = true;
}
?>
<html>
<head>
<title>Microsist</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<meta http-equiv="Expires" CONTENT="0">
<script language="JavaScript" type="text/javascript" src="scripts/scripts.js"></script>
<script language="JavaScript" type="text/javascript" src="scripts/prototype.js"></script>
<script language="JavaScript" type="text/javascript" src="scripts/strings.js"></script>
<script type="text/javascript" src="scripts/arrays.js"></script>
<link href="estilos.css" rel="stylesheet" type="text/css">
</head>
<body bgcolor="#CCCCCC" leftmargin="0" topmargin="0" marginwidth="0" marginheight="0" onLoad="js_validaTipoTurma();" >
<table width="100%" border="0" cellspacing="0" cellpadding="0">
 <tr>
  <td height="430" align="left" valign="top" bgcolor="#CCCCCC">
   <br>
   <center>
   <fieldset style="width:95%"><legend><b>Exclusão de Turma</b></legend>
    <?php include(modification("forms/db_frmturma.php"));?>
   </fieldset>
   </center>
  </td>
 </tr>
</table>
</body>
</html>
<?php
if (isset($excluir)) {
    if ($clturma->erro_status == "0") {
        $clturma->erro(true, false);
    } else {
        $clturma->erro(true, true);
    }
}

if ($db_opcao == 33) {
    echo "<script>document.form1.pesquisar.click();</script>";
}

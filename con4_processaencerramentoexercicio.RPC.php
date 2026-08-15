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

use ECidade\Configuracao\Consistencia\Repository\Consistencia as ConsistenciaEncerramento;
use ECidade\Financeiro\Contabilidade\Encerramento\Exercicio\Encerramento;
use ECidade\Financeiro\Contabilidade\ExercicioContabil\Abertura;

require_once modification("libs/db_stdlib.php");
require_once modification("libs/db_conecta.php");
require_once modification("libs/db_sessoes.php");
require_once modification("libs/db_usuariosonline.php");
require_once modification("libs/db_libcontabilidade.php");
require_once modification("dbforms/db_funcoes.php");
require_once modification("classes/lancamentoContabil.model.php");

$oParam = JSON::create()->parse(str_replace("\\", "", $_POST["json"]));
$oRetorno = new stdClass();
$oRetorno->erro = false;
$oRetorno->mensagem = '';
db_putsession("DB_desativar_account", true);
$rsDesabilitarAuditoria = db_query("SELECT fc_putsession('__disable_audit__', 'on');");
$anoSessao = db_getsession("DB_anousu");
$data = "{$anoSessao}-12-31";
$dataEncerramento = new DBDate($data);

try {
    db_inicio_transacao();

    switch ($oParam->exec) {
        case "buscarLog":
            // log de negativos para o 1025
            $oRetorno->lRegistros = false;

            $sql = <<<SQL

              select e60_codemp || '/' || e60_anousu  as numero ,
                     x.*,
                     o15_recurso,
                     codigo_siconfi,
                     o15_complemento
                from ( select *
                         from fc_valores_encerramento_empenho_rp('6221302%', false)
                    ) as x
                inner join empempenho on e60_numemp = empenho
                join orctiporec on o15_codigo = codigo_recurso
                join fonterecurso on orctiporec_id = o15_codigo and exercicio = e60_anousu
              where valor != 0
              order by empenho;
SQL;

            $sHora = date('hms');
            $pArquivoLog = "tmp/Log_encerramento{$sHora}_.csv";

            $rs = db_query($sql);
            $aDadosLog = [];

            $aDadosLog[] = "Empenho";
            $aDadosLog[] = "Numero";
            $aDadosLog[] = "Siconfi";
            $aDadosLog[] = "Subrecurso";
            $aDadosLog[] = "Complemento";
            $aDadosLog[] = "Valor Credito";
            $aDadosLog[] = "Valor Debito";
            $aDadosLog[] = "Valor a Liquidar";
            $aDadosLog[] = "Valor";

            $sLinha = implode(";", $aDadosLog);
            file_put_contents($pArquivoLog, "$sLinha \n", FILE_APPEND);

            if (pg_num_rows($rs) > 0) {
                $oRetorno->lRegistros = true;

                for ($i = 0; $i < pg_num_rows($rs); $i++) {
                    $oDados = db_utils::fieldsMemory($rs, $i);
                    $aDadosLog = [];
                    $aDadosLog[] = $oDados->empenho;//"Empenho";
                    $aDadosLog[] = $oDados->numero;//"Numero";
                    $aDadosLog[] = $oDados->codigo_siconfi;
                    $aDadosLog[] = $oDados->o15_recurso;
                    $aDadosLog[] = $oDados->o15_complemento;
                    $aDadosLog[] = db_formatar($oDados->valor_credito, "f");
                    $aDadosLog[] = db_formatar($oDados->valor_debito, "f");
                    $aDadosLog[] = db_formatar($oDados->valor_a_liquidar_empenho, "f");
                    $aDadosLog[] = db_formatar($oDados->valor, "f");

                    $sLinha = implode(";", $aDadosLog);
                    file_put_contents($pArquivoLog, "$sLinha \n", FILE_APPEND);
                }
            }
            $oRetorno->arquivoLog = $pArquivoLog;
            break;

        case "processarEncerramento":

            $anoSessao = db_getsession("DB_anousu");
            $instSessao = db_getsession("DB_instit");
        
            $sqlVerificaRecurso = "
                SELECT DISTINCT c60_estrut, c61_reduz, c60_descr, COUNT(*) as qtd_lancamentos_sem_recurso FROM contabilidade.conplanoreduz JOIN contabilidade.conplano ON c60_codcon = c61_codcon AND c60_anousu = c61_anousu JOIN contabilidade.conlancamval ON (c69_debito = c61_reduz OR c69_credito = c61_reduz) AND c69_anousu = c61_anousu JOIN contabilidade.conlancam ON c70_codlan = c69_codlan WHERE c61_anousu = {$anoSessao} AND c61_instit = {$instSessao} AND substr(c60_estrut, 1, 1) IN ('3', '4') AND c69_data BETWEEN '{$anoSessao}-01-01' AND '{$anoSessao}-12-31'
          AND NOT EXISTS (
              SELECT 1 
              FROM contabilidade.conlancamrecurso 
              WHERE c130_conlancam = c69_codlan 
                AND c130_conta = c61_reduz
                AND c130_anousu = c69_anousu
                AND c130_natureza = CASE WHEN c69_debito = c61_reduz THEN 'D' ELSE 'C' END
          )
        GROUP BY c60_estrut, c61_reduz, c60_descr
        HAVING COUNT(*) > 0
        ORDER BY c60_estrut
    ";
    $rsVerificaRecurso = db_query($sqlVerificaRecurso);
    
    if (pg_num_rows($rsVerificaRecurso) > 0) {
        $mensagemErro = "ATENÇÃO: Existem contas com lançamentos sem recurso orçamentário cadastrado:\n\n";
        
        for ($i = 0; $i < pg_num_rows($rsVerificaRecurso); $i++) {
            $linha = db_utils::fieldsMemory($rsVerificaRecurso, $i);
            $mensagemErro .= "- Conta: " . $linha->c60_estrut . " (Reduzido: " . $linha->c61_reduz . ")\n";
            $mensagemErro .= "  " . $linha->c60_descr . "\n";
            $mensagemErro .= "  Lançamentos sem recurso: " . $linha->qtd_lancamentos_sem_recurso . "\n\n";
        }
        
        $mensagemErro .= "Cadastre o recurso orçamentário para esses lançamentos antes de processar.";
        
        throw new Exception($mensagemErro);
    }

            //ORIGINAL
            if ($oParam->encerramento) {
                $consistencia = ConsistenciaEncerramento::getInstance();
                $consistencias = $consistencia->getArquivosConsistenciaPorTipo(
                    ConsistenciaEncerramento::TIPO_ENCERRAMENTO
                );

                $consistenciasComProblema = array();
                foreach ($consistencias as $consistenciaExecutada) {
                    $registros = $consistencia->executarConsistencia($consistenciaExecutada->id);
                    if ($registros) {
                        $consistenciasComProblema[] = " - " . $consistenciaExecutada->jsonConsistencia->nome;
                    }
                }

                if (count($consistenciasComProblema)) {
                    $mensagem = "Foram encontrados possíveis problemas nas consistências abaixo.\n\n";
                    $mensagem .= implode("\n", $consistenciasComProblema);
                    $mensagem .= "\n\nVerifique estas consistências na rotina: \nProcedimentos > Utilitários ";
                    $mensagem .= "da Contabilidade > Consistência do Encerramento do Exercício";
                    throw new Exception($mensagem);
                }
            }

            $encerramento = new Encerramento(
                db_getsession("DB_anousu"),
                new DBDate($data),
                InstituicaoRepository::getInstituicaoSessao()
            );

            $encerramento->setTipoEncerramento($oParam->tipoProcessamento);
            $encerramento->encerrar($oParam->documentos);
            $oRetorno->encerrouTodosDocumentos = true;

            
             // Percorremos todos os documentos procurando os que nao foram encerrados
             // caso todos tenham sido encerrados setamos a variavel encerrouTodosDocumentos como
             // true para a tela perguntar para o usuario se deseja encerrar periodo contabil
            
            $documentos = getDadosDocumentos(array_keys($encerramento->getDocumentosParaProcessamento()));

            if ($oParam->tipoProcessamento == "ExecucaoOrcamentaria") {
                $documentos = getDadosDocumentos(
                    array_keys($encerramento->getDocumentosParaProcessamentoEncerramentoOrcamentario())
                );
            }

            $oRetorno->mensagem = "O encerramento foi processado com sucesso.";
            foreach ($documentos as $documento) {
                if ($documento['processado'] === false) {
                    $oRetorno->encerrouTodosDocumentos = false;
                    break;
                }
            }

            break;
            

        case "fecharPeriodoContabil":
            $usuario = UsuarioSistemaRepository::getUsuarioSessao();
            $periodoContabil = new \ECidade\Financeiro\Contabilidade\Encerramento\PeriodoContabil(
                InstituicaoRepository::getInstituicaoSessao(),
                $dataEncerramento,
                $usuario,
                $anoSessao
            );
            $periodoContabil->encerrar();
            $oRetorno->mensagem = "O período contábil foi fechado com sucesso.";
            break;


        case "cancelarEncerramento":
            $encerramento = new Encerramento(
                db_getsession("DB_anousu"),
                new DBDate($data),
                InstituicaoRepository::getInstituicaoSessao()
            );
            if (empty($oParam->documentos)) {
                throw new Exception("Documentos não informados.");
            }

            $encerramento->setTipoEncerramento($oParam->sTipo);
            $encerramento->setDocumentosCancelar($oParam->documentos);
            $encerramento->cancelar();

            $oRetorno->mensagem = "O encerramento foi cancelado com sucesso.";

            break;

        case 'getDocumentosEncerramento':
            $encerramento = new Encerramento(
                db_getsession("DB_anousu"),
                new DBDate($data),
                InstituicaoRepository::getInstituicaoSessao()
            );
            $documentos = getDadosDocumentos(array_keys($encerramento->getDocumentosParaProcessamento()));

            $oRetorno->documentos = $documentos;
            break;

        case 'getDocumentosEncerramentoOrcamentaria':
            $encerramento = new Encerramento(
                db_getsession("DB_anousu"),
                new DBDate($data),
                InstituicaoRepository::getInstituicaoSessao()
            );
            $documentos = getDadosDocumentos(
                array_keys($encerramento->getDocumentosParaProcessamentoEncerramentoOrcamentario())
            );

            //verifica se há encerramento para os doc, se existe não pode encerrar encerrar o 1024, 1025, 1026 antes
            $aDocumentosEncerramentoExercicio = getDadosDocumentos(
                array_keys($encerramento->getDocumentosParaProcessamento())
            );

            $lLiberarCancelamento = true;
            foreach ($aDocumentosEncerramentoExercicio as $aDocumentos) {
                if ($aDocumentos["processado"]) {
                    $lLiberarCancelamento = false;
                    break;
                }
            }

            $oRetorno->documentos = $documentos;
            $oRetorno->lLiberarCancelamento = $lLiberarCancelamento;
            break;
    }

    db_fim_transacao(false);
} catch (Exception $eErro) {
    db_fim_transacao(true);

    $oRetorno->erro = true;
    $oRetorno->mensagem = urlencode($eErro->getMessage());
}
unset($_SESSION["DB_desativar_account"]);
$rsDesabilitarAuditoria = db_query("SELECT fc_putsession('__disable_audit__', 'off');");
echo JSON::create()->stringify($oRetorno);

function getDadosDocumentos($documentos)
{
    $instituicao = db_getsession('DB_instit');
    $anousu = db_getsession('DB_anousu');
    $daoConhistDoc = new \cl_conhistdoc();
    $where = "c53_coddoc in(" . implode(', ', $documentos) . ")";
    $sqlTipoDocumento = $daoConhistDoc->sql_query_file(
        null,
        "c53_descr as descricao, c53_coddoc as codigo,
        exists(select 1 from conencerramento
                where c42_coddoc = c53_coddoc
                  and c42_anousu = {$anousu}
                  and c42_instit = {$instituicao}
                  ) as processado",
        '',
        $where
    );
    $rsTipoDocumento = db_query($sqlTipoDocumento);
    if (!$rsTipoDocumento || pg_num_rows($rsTipoDocumento) == 0) {
        return null;
    }
    $documentos = array_flip($documentos);
    $dados = \db_utils::getCollectionByRecord($rsTipoDocumento);
    foreach ($dados as $documentosConsulta) {
        $documentos[$documentosConsulta->codigo] = array();
        $documentos[$documentosConsulta->codigo]["codigo"] = $documentosConsulta->codigo;
        $documentos[$documentosConsulta->codigo]["descricao"] = $documentosConsulta->descricao;
        $documentos[$documentosConsulta->codigo]["processado"] = $documentosConsulta->processado == 't';
    }
    $documentosRetorno = [];
    foreach ($documentos as $documento) {
        if (!is_array($documento)) {
            continue;
        }
        $documentosRetorno[] = $documento;
    }
    $documentos = array_values($documentosRetorno);
    return $documentos;
}

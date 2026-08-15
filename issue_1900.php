<?php
require_once("libs/db_stdlib.php");
require_once("libs/db_utils.php");
require_once("libs/db_app.utils.php");

/**
 * Dados Servidor de Origem
 */
$oStdConexaoOrigem = new stdClass();
$oStdConexaoOrigem->servidor = "localhost";
$oStdConexaoOrigem->base     = "flavia_acerto_extras_20141206";
$oStdConexaoOrigem->porta    = 5432;
$oStdConexaoOrigem->usuario  = "ecidade";
$oStdConexaoOrigem->senha    = "";


/**
 * Dados do Servidor de Produção
 */
$oStdConexaoProducao = new stdClass();
$oStdConexaoProducao->servidor = "localhost";
$oStdConexaoProducao->base     = "ecidade_producao";
$oStdConexaoProducao->porta    = 5432;
$oStdConexaoProducao->usuario  = "ecidade";
$oStdConexaoProducao->senha    = "";

$hConexaoOrigem   = pg_connect("host={$oStdConexaoOrigem->servidor} dbname={$oStdConexaoOrigem->base} port={$oStdConexaoOrigem->porta} user={$oStdConexaoOrigem->usuario} password={$oStdConexaoOrigem->senha}");
if (!$hConexaoOrigem) {
  echo 'Nao foi possivel conectar-se ao servidor de origem.'; exit;
}

$hConexaoProducao = pg_connect("host={$oStdConexaoProducao->servidor} dbname={$oStdConexaoProducao->base} port={$oStdConexaoProducao->porta} user={$oStdConexaoProducao->usuario} password={$oStdConexaoProducao->senha}");
if (!$hConexaoProducao) {
  echo 'Nao foi possivel conectar-se ao servidor de origem.'; exit;
}

try {

  pg_query($hConexaoProducao, "select fc_putsession('db_instit', '1');");
  pg_query($hConexaoProducao, "select fc_putsession('DB_use_pcasp', '1');");
  pg_query($hConexaoProducao, "select fc_putsession('DB_anousu', '2014');");
  pg_query($hConexaoProducao, 'begin;');
  $sSqlBuscaCodigoSlip = "select k17_codigo from slip where k17_data = '2014-02-20' and k17_valor <= 0";
  $rsBuscaSlips = pg_query($hConexaoOrigem, "select * from slip where k17_data = '2014-02-20' and k17_valor <= 0");
  $iTotalSlips  = pg_num_rows($rsBuscaSlips);
  echo "\nProcessando SLIPS {$iTotalSlips} \n";
  for ($iRowSlip = 0; $iRowSlip < $iTotalSlips; $iRowSlip++) {

    $oStdSlip = db_utils::fieldsMemory($rsBuscaSlips, $iRowSlip);
    $sSqlSlip = "insert into slip values (
                                $oStdSlip->k17_codigo
                               ,".($oStdSlip->k17_data == "null" || $oStdSlip->k17_data == ""?"null":"'".$oStdSlip->k17_data."'")."
                               ,$oStdSlip->k17_debito
                               ,$oStdSlip->k17_credito
                               ,$oStdSlip->k17_valor
                               ,$oStdSlip->k17_hist
                               ,'$oStdSlip->k17_texto'
                               ,".($oStdSlip->k17_dtaut == "null" || $oStdSlip->k17_dtaut == ""?"null":"'".$oStdSlip->k17_dtaut."'")."
                               ,$oStdSlip->k17_autent
                               ,$oStdSlip->k17_instit
                               ,".($oStdSlip->k17_dtanu == "null" || $oStdSlip->k17_dtanu == ""?"null":"'".$oStdSlip->k17_dtanu."'")."
                               ,$oStdSlip->k17_situacao
                               ,$oStdSlip->k17_tipopagamento
                               ,".($oStdSlip->k17_dtestorno == "null" || $oStdSlip->k17_dtestorno == ""?"null":"'".$oStdSlip->k17_dtestorno."'")."
                               ,'$oStdSlip->k17_motivoestorno'
                      )";
    $rsInsertSlip = pg_query($hConexaoProducao, $sSqlSlip);
    if (!$rsInsertSlip) {
      throw new Exception("Impossivel incluir o slip {$oStdSlip->k17_codigo}.\nERRO ->".pg_last_error($hConexaoProducao));
    }
  }

  $rsBuscaCP   = pg_query($hConexaoOrigem, "select * from slipconcarpeculiar where k131_slip in ({$sSqlBuscaCodigoSlip})");
  $iTotalCP = pg_num_rows($rsBuscaSlips);
  echo "Processando Caracteristica Peculiar {$iTotalCP}\n";
  for ($iRowSlip = 0; $iRowSlip < $iTotalCP; $iRowSlip++) {

    $oStdSlip = db_utils::fieldsMemory($rsBuscaCP, $iRowSlip);

    $sSqlCP = "insert into slipconcarpeculiar
                values ($oStdSlip->k131_sequencial
                        ,$oStdSlip->k131_slip
                        ,$oStdSlip->k131_tipo
                        ,'$oStdSlip->k131_concarpeculiar')";
    $rsInsertSlip = pg_query($hConexaoProducao, $sSqlCP);
    if (!$rsInsertSlip) {
      throw new Exception("Impossivel CP para o slip {$oStdSlip->k17_codigo}.\nERRO ->".pg_last_error($hConexaoProducao));
    }
  }

  $rsBuscaCGM = pg_query($hConexaoOrigem, "select * from slipnum where k17_codigo in ({$sSqlBuscaCodigoSlip})");
  $iTotalCgm   = pg_num_rows($rsBuscaCGM);
  echo "Processando CGM {$iTotalCgm}\n";
  for ($iRowSlip = 0; $iRowSlip < $iTotalCgm; $iRowSlip++) {

    $oStdSlip = db_utils::fieldsMemory($rsBuscaCGM, $iRowSlip);

    if ($oStdSlip->k17_numcgm == 619) {
      $oStdSlip->k17_numcgm = 3905;
    }

    $sSql = "insert into slipnum
                values ($oStdSlip->k17_codigo
                        ,$oStdSlip->k17_numcgm)";
    $rsInsertSlip = pg_query($hConexaoProducao, $sSql);
    if (!$rsInsertSlip) {
      throw new Exception("Impossivel incluir CGM para o slip {$oStdSlip->k17_codigo}.\nERRO ->".pg_last_error($hConexaoProducao));
    }
  }

  $rsBusca = pg_query($hConexaoOrigem, "select * from sliprecurso where k29_slip in ({$sSqlBuscaCodigoSlip})");
  $iTotal   = pg_num_rows($rsBusca);
  echo "Processando Recurso {$iTotal}\n";
  for ($iRowSlip = 0; $iRowSlip < $iTotal; $iRowSlip++) {

    $oStdSlip = db_utils::fieldsMemory($rsBusca, $iRowSlip);

    $sSql = "insert into sliprecurso
                values ($oStdSlip->k29_sequencial
                        ,$oStdSlip->k29_slip
                        ,$oStdSlip->k29_recurso
                        ,$oStdSlip->k29_valor)";
    $rsInsertSlip = pg_query($hConexaoProducao, $sSql);
    if (!$rsInsertSlip) {
      throw new Exception("Impossivel incluir Recurso para o slip {$oStdSlip->k17_codigo}.\nERRO ->".pg_last_error($hConexaoProducao));
    }
  }

  $rsBusca = pg_query($hConexaoOrigem, "select * from sliptipooperacaovinculo where k153_slip in ({$sSqlBuscaCodigoSlip})");
  $iTotal   = pg_num_rows($rsBusca);
  echo "Processando Tipo de Operacao {$iTotal}\n";
  for ($iRowSlip = 0; $iRowSlip < $iTotal; $iRowSlip++) {

    $oStdSlip = db_utils::fieldsMemory($rsBusca, $iRowSlip);
    $sSql = "insert into sliptipooperacaovinculo
                values ($oStdSlip->k153_slip
                        ,$oStdSlip->k153_slipoperacaotipo)";
    $rsInsertSlip = pg_query($hConexaoProducao, $sSql);
    if (!$rsInsertSlip) {
      throw new Exception("Impossivel incluir Tipo de Operacao para o slip {$oStdSlip->k17_codigo}.\nERRO ->".pg_last_error($hConexaoProducao));
    }
  }



  $rsBuscaAgendaSlip = pg_query($hConexaoOrigem, "select * from empageslip where e89_codigo in ({$sSqlBuscaCodigoSlip})");
  $iTotalRegistros = pg_num_rows($rsBuscaAgendaSlip);
  echo "Processando empageslip {$iTotalRegistros}\n";
  for ($iRowAgenda = 0; $iRowAgenda < $iTotalRegistros; $iRowAgenda++) {

    $oStdAgenda = db_utils::fieldsMemory($rsBuscaAgendaSlip, $iRowAgenda);

    $sSql = "insert into empageslip
                  values ({$oStdAgenda->e89_codmov}, {$oStdAgenda->e89_codigo});";
    $rsExecuta  = pg_query($hConexaoProducao, $sSql);
    if (!$rsExecuta) {
      throw new Exception("Não foi possível inserir na tabela empageslip.");
    }
  }

  $sSqlUpdateMovimento = "update empagemov set e81_cancelado = null where e81_codmov in (select e89_codmov from empageslip where e89_codigo in ({$sSqlBuscaCodigoSlip}))";
  $rsAcertaDataMovimento = pg_query($hConexaoProducao, $sSqlUpdateMovimento);
  if (!$rsAcertaDataMovimento) {
    throw new Exception('Erro ao acertar a data do movimento.');
  }
  echo "Acertando Data Movimentos -> ";
  echo "Total Movimentos Ajustados : ".pg_affected_rows($rsAcertaDataMovimento)."\n";

  $sSqlCorrente = "select *
                     from corrente
                    where k12_id = 7
                      and k12_data = '2014-01-31'
                      and k12_autent in (select k112_autent from slipcorrente where k112_slip in ({$sSqlBuscaCodigoSlip}))";
  $rsBuscaCorrente = pg_query($hConexaoOrigem, $sSqlCorrente);
  $iTotal = pg_num_rows($rsBuscaCorrente);
  echo "Criando CORRENTE {$iTotal}\n";
  for ($iRow = 0; $iRow < $iTotal; $iRow++) {

    $oStdCorrente = db_utils::fieldsMemory($rsBuscaCorrente, $iRow);
    $sSql = "insert into corrente
                values ($oStdCorrente->k12_id
                        ,".($oStdCorrente->k12_data == "null" || $oStdCorrente->k12_data == ""?"null":"'".$oStdCorrente->k12_data."'")."
                        ,$oStdCorrente->k12_autent
                        ,'$oStdCorrente->k12_hora'
                        ,$oStdCorrente->k12_conta
                        ,$oStdCorrente->k12_valor
                        ,'$oStdCorrente->k12_estorn'
                        ,$oStdCorrente->k12_instit)";
    $rsInsert = pg_query($hConexaoProducao, $sSql);
    if (!$rsInsert) {
      throw new Exception("Erro: corrente");
    }
  }

  $rsBuscaSlipCorrente = pg_query($hConexaoOrigem, "select * from slipcorrente where k112_slip in ({$sSqlBuscaCodigoSlip})");
  $iTotal = pg_num_rows($rsBuscaSlipCorrente);
  echo "Criando SLIPCORRENTE {$iTotal}\n";
  for ($iRow = 0; $iRow < $iTotal; $iRow++) {

    $oStdDado = db_utils::fieldsMemory($rsBuscaSlipCorrente, $iRow);
    $sSql = "insert into slipcorrente
                values ($oStdDado->k112_sequencial
                       ,$oStdDado->k112_id
                       ,".($oStdDado->k112_data == "null" || $oStdDado->k112_data == ""?"null":"'".$oStdDado->k112_data."'")."
                       ,$oStdDado->k112_autent
                       ,$oStdDado->k112_slip
                       ,'$oStdDado->k112_ativo')";
    $rsInsert = pg_query($hConexaoProducao, $sSql);
    if (!$rsInsert) {
      throw new Exception("Erro: slipcorrente");
    }
  }

  $sSqlBuscaCorHist = "select * from corhist
                        where k12_id = 7
                          and k12_data = '2014-01-31'
                          and k12_autent in (select k112_autent from slipcorrente where k112_slip in ({$sSqlBuscaCodigoSlip}))";
  $rsBuscaCorHist = pg_query($hConexaoOrigem, $sSqlBuscaCorHist);
  $iTotal = pg_num_rows($rsBuscaCorHist);
  echo "Criando CORHIST {$iTotal}\n";
  for ($iRow = 0; $iRow < $iTotal; $iRow++) {

    $oStdDado = db_utils::fieldsMemory($rsBuscaCorHist, $iRow);
    $sSql = "insert into corhist
                values ($oStdDado->k12_id
                        ,".($oStdDado->k12_data == "null" || $oStdDado->k12_data == ""?"null":"'".$oStdDado->k12_data."'")."
                        ,$oStdDado->k12_autent
                        ,'$oStdDado->k12_histcor')";
    $rsInsert = pg_query($hConexaoProducao, $sSql);
    if (!$rsInsert) {
      throw new Exception("Erro: corhist");
    }
  }

  $sSqlBuscaCorNump = "select * from cornump
                        where k12_id = 7
                          and k12_data = '2014-01-31'
                          and k12_autent in (select k112_autent from slipcorrente where k112_slip in ({$sSqlBuscaCodigoSlip}))";
  $rsBuscaCorNump = pg_query($hConexaoOrigem, $sSqlBuscaCorNump);
  $iTotal = pg_num_rows($rsBuscaCorNump);
  echo "Criando CORNUMP {$iTotal}\n";
  for ($iRow = 0; $iRow < $iTotal; $iRow++) {

    $oStdDado = db_utils::fieldsMemory($rsBuscaCorNump, $iRow);
    $sSql = "insert into cornump
                values ($oStdDado->k12_id
                       ,".($oStdDado->k12_data == "null" || $oStdDado->k12_data == ""?"null":"'".$oStdDado->k12_data."'")."
                       ,$oStdDado->k12_autent
                       ,$oStdDado->k12_numpre
                       ,$oStdDado->k12_numpar
                       ,$oStdDado->k12_numtot
                       ,$oStdDado->k12_numdig
                       ,$oStdDado->k12_receit
                       ,$oStdDado->k12_valor
                       ,$oStdDado->k12_numnov
                      )";
    $rsInsert = pg_query($hConexaoProducao, $sSql);
    if (!$rsInsert) {
      throw new Exception("Erro: cornump");
    }
  }

  $sSqlCorGrupoCorrente = "select * from corgrupocorrente
                        where k105_id = 7
                          and k105_data = '2014-01-31'
                          and k105_autent in (select k112_autent from slipcorrente where k112_slip in ({$sSqlBuscaCodigoSlip}))";
  $rsBuscaCorGrupoCorrente = pg_query($hConexaoOrigem, $sSqlCorGrupoCorrente);
  $iTotal = pg_num_rows($rsBuscaCorGrupoCorrente);
  echo "Criando CORGRUPOCORRENTE {$iTotal}\n";
  for ($iRow = 0; $iRow < $iTotal; $iRow++) {

    $oStdDado = db_utils::fieldsMemory($rsBuscaCorGrupoCorrente, $iRow);
    $sSql = "insert into corgrupocorrente
                values ($oStdDado->k105_sequencial
                       ,$oStdDado->k105_corgrupo
                       ,".($oStdDado->k105_data == "null" || $oStdDado->k105_data == ""?"null":"'".$oStdDado->k105_data."'")."
                       ,$oStdDado->k105_autent
                       ,$oStdDado->k105_id
                      )";
    $rsInsert = pg_query($hConexaoProducao, $sSql);
    if (!$rsInsert) {
      throw new Exception("Erro: corgrupocorrente");
    }
  }

  $sSqlBuscaRetencaoCorrente = "select * from retencaocorgrupocorrente
                                 where e47_corgrupocorrente in (select k105_sequencial
                                                                  from corgrupocorrente
                                                                 where k105_id = 7
                                                                   and k105_data = '2014-01-31'
                                                                   and k105_autent in (select k112_autent from slipcorrente where k112_slip in ({$sSqlBuscaCodigoSlip})))";
  $rsBuscaRetencaoCorrente = pg_query($hConexaoOrigem, $sSqlBuscaRetencaoCorrente);
  $iTotal = pg_num_rows($rsBuscaRetencaoCorrente);
  echo "Criando retencaocorgrupocorrente {$iTotal}\n";
  for ($iRow = 0; $iRow < $iTotal; $iRow++) {

    $oStdDado = db_utils::fieldsMemory($rsBuscaRetencaoCorrente, $iRow);
    $sSql = "insert into retencaocorgrupocorrente
                values ($oStdDado->e47_sequencial
                        ,$oStdDado->e47_corgrupocorrente
                        ,$oStdDado->e47_retencaoreceita
                      )";
    $rsInsert = pg_query($hConexaoProducao, $sSql);
    if (!$rsInsert) {
      throw new Exception("Erro: retencaocorgrupocorrente");
    }
  }


  $sSqlBuscaSlips = "select * from bkp_conlancam_1764";
  $rsBuscaLancamentoSlips = pg_query($hConexaoProducao, $sSqlBuscaSlips);
  $iTotalLancamentos = pg_num_rows($rsBuscaLancamentoSlips);
  echo "Criando Lancamentos {$iTotalLancamentos}\n";
  for ($iRowLancamento = 0; $iRowLancamento < $iTotalLancamentos; $iRowLancamento++) {

    $oStdLancSlip = db_utils::fieldsMemory($rsBuscaLancamentoSlips, $iRowLancamento);
    $sSqlInserirLancamento = "insert into conlancam values ({$oStdLancSlip->c70_codlan}, {$oStdLancSlip->c70_anousu}, '{$oStdLancSlip->c70_data}', {$oStdLancSlip->c70_valor}) ";
    $rsInserirLancamento = pg_query($hConexaoProducao, $sSqlInserirLancamento);

    foreach (getTabelasContabilidade() as $sCampo => $sTabela) {

      $rsBuscaDetalhes = pg_query($hConexaoOrigem, "select * from {$sTabela} where {$sCampo} = {$oStdLancSlip->c70_codlan}");
      $iTotalLanc = pg_num_rows($rsBuscaDetalhes);

      echo "Criando {$sTabela} para Lancamento {$oStdLancSlip->c70_codlan}\n";
      for ($iRow = 0; $iRow < $iTotalLanc; $iRow++) {

        $oStdDetalhe = db_utils::fieldsMemory($rsBuscaDetalhes, $iRow);

        switch ($sTabela) {

          case 'conlancamcompl':

            $rsInsertDetalhe = pg_query($hConexaoProducao, "insert into {$sTabela} values ({$oStdDetalhe->c72_codlan}, '{$oStdDetalhe->c72_complem}')");
            if (!$rsInsertDetalhe) {
              throw new Exception("\n\n\nERRO {$sTabela}");
            }
            break;

          case 'conlancamconcarpeculiar':

            $rsInsertDetalhe = pg_query($hConexaoProducao, "insert into {$sTabela} values ({$oStdDetalhe->c08_sequencial}, {$oStdDetalhe->c08_codlan}, '{$oStdDetalhe->c08_concarpeculiar}')");
            if (!$rsInsertDetalhe) {
              throw new Exception("ERRO {$sTabela}");
            }
            break;

          case 'conlancamcorrente':

            $rsInsertDetalhe = pg_query(
              $hConexaoProducao,
              "insert into {$sTabela}
               values ({$oStdDetalhe->c86_sequencial}, {$oStdDetalhe->c86_id}, '{$oStdDetalhe->c86_data}', {$oStdDetalhe->c86_autent}, {$oStdDetalhe->c86_conlancam})"
            );
            if (!$rsInsertDetalhe) {
              throw new Exception("ERRO {$sTabela}");
            }
            break;

          case 'conlancamdoc':

            $rsInsertDetalhe = pg_query(
              $hConexaoProducao,
              "insert into {$sTabela}
               values ({$oStdDetalhe->c71_codlan}, {$oStdDetalhe->c71_coddoc}, '{$oStdDetalhe->c71_data}')"
            );
            if (!$rsInsertDetalhe) {
              throw new Exception("ERRO {$sTabela}");
            }
            break;

          case 'conlancaminstit':

            $rsInsertDetalhe = pg_query(
              $hConexaoProducao,
              "insert into {$sTabela}
               values ({$oStdDetalhe->c02_sequencial}, {$oStdDetalhe->c02_codlan}, {$oStdDetalhe->c02_instit})"
            );
            if (!$rsInsertDetalhe) {
              throw new Exception("ERRO {$sTabela}");
            }
            break;

          case 'conlancamordem':

            $rsInsertDetalhe = pg_query(
              $hConexaoProducao,
              "insert into {$sTabela}
               values ({$oStdDetalhe->c03_sequencial}, {$oStdDetalhe->c03_codlan}, {$oStdDetalhe->c03_ordem})"
            );
            if (!$rsInsertDetalhe) {
              throw new Exception("ERRO {$sTabela}");
            }
            break;

          case 'conlancamval':

            $rsInsertDetalhe = pg_query(
              $hConexaoProducao,
              "insert into {$sTabela}
               values ($oStdDetalhe->c69_sequen,
                        $oStdDetalhe->c69_anousu,
                        $oStdDetalhe->c69_codlan,
                        $oStdDetalhe->c69_codhist,
                        $oStdDetalhe->c69_credito,
                        $oStdDetalhe->c69_debito,
                        $oStdDetalhe->c69_valor,
                        '$oStdDetalhe->c69_data')");
            if (!$rsInsertDetalhe) {
              throw new Exception("ERRO {$sTabela}");
            }
            break;
        }
      }
    }
    echo "\nFIM\n\n";
  }
  echo "TOTAL Processado {$iRowLancamento}\n";



  pg_query($hConexaoProducao, 'commit');

} catch (Exception $e) {

  pg_query($hConexaoProducao, 'rollback');

  echo $e->getMessage()."\n";
}


function getTabelasContabilidade() {

  $aTabelasContabilidade = array(
    'c72_codlan' => 'conlancamcompl',
    'c08_codlan' => 'conlancamconcarpeculiar',
    'c86_conlancam' => 'conlancamcorrente',
    'c71_codlan' => 'conlancamdoc',
    'c84_conlancam' => 'conlancamslip',
    'c02_codlan' => 'conlancaminstit',
    'c03_codlan' => 'conlancamordem',
    'c69_codlan' => 'conlancamval'
  );

  return $aTabelasContabilidade;
}

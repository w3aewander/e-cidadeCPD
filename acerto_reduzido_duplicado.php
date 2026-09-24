<?php
require_once("libs/db_utils.php");
require_once("libs/db_conn.php");

/**
 * COMO UTILIZAR
 * Altere as variáveis abaixo para os dados de acesso do cliente
 *
 * Depois de ajustar, executar o programa: php acerto_instituicao_contas_instituicao.php
 */
$DB_SERVIDOR = 'localhost';
$DB_BASE     = 'ecidade_producao';
$DB_PORTA    = 5432;
$DB_USUARIO  = 'ecidade';
$DB_SENHA    = '';

/**
 * Variável de controle do processamento.
 * FALSE = ROLLBACK
 * TRUE  = COMMIT
 */
$lConcluirProcessamento = true;
$iParametroInstituicaoSolicitada = isset($argv[1]) ? $argv[1] : null;

$rsConectar = pg_connect("host=$DB_SERVIDOR dbname=$DB_BASE port=$DB_PORTA user=$DB_USUARIO password=$DB_SENHA");

if (!$rsConectar) {
  die("NAO CONECTADO A BASE DE DADOS\n");
}

$dtAtual     = date("Ymd_his");
$sNomeArquivoLog = "tmp/acerto_reduzido_duplicado_instituicao_{$dtAtual}.log";
$hArquivoLog = fopen($sNomeArquivoLog, "w");

echo "######################################################################";
echo "\nARQUIVO DE LOG {$sNomeArquivoLog}\n";
echo "######################################################################\n\n";

pg_query("select fc_startsession();");

try {

  pg_query("begin");
  $rsBuscaFechamentosContabilidade = pg_query("select * from condataconf where c99_data >= '2013-01-01' order by c99_data");
  $iLinhasFechamentosContabilidade = pg_num_rows($rsBuscaFechamentosContabilidade);
  $aFechamentosContabilidade = array();
  if ($rsBuscaFechamentosContabilidade && $iLinhasFechamentosContabilidade > 0){

    $aFechamentosContabilidade = db_utils::getCollectionByRecord($rsBuscaFechamentosContabilidade);
    $rsExcluiFechamentosContabilidade = pg_query("delete from condataconf where c99_data >= '2013-01-01'");
    if (!$rsExcluiFechamentosContabilidade) {
      throw new Exception("Nao foi possivel excluir as configurações de fechamento da contabilidade.");
    }
  }


  $sWhereInstituicao = "";
  if (!empty($iParametroInstituicaoSolicitada)) {
    $sWhereInstituicao = "where codigo = {$iParametroInstituicaoSolicitada}";
  }
  $rsBuscaInstituicao = pg_query("select codigo, nomeinst from db_config {$sWhereInstituicao} order by 1;");
  $iTotalInstituicao  = pg_num_rows($rsBuscaInstituicao);
  if ($iTotalInstituicao == 0) {
    throw new Exception("Instituicoes nao localizadas.");
  }

  for ($iRowInstituicao = 0; $iRowInstituicao < $iTotalInstituicao; $iRowInstituicao++) {

    $oStdInstituicao = db_utils::fieldsMemory($rsBuscaInstituicao, $iRowInstituicao);
    $iInstituicaoSolicitada = $oStdInstituicao->codigo;

    $rsSqlBuscaContasErradas = pg_query( "select distinct c61_codcon,
                                                          count(*)
                                            from (select distinct c61_codcon,
                                                                  c61_reduz,
                                                                  c61_instit
                                                    from conplanoreduz
                                                   where c61_anousu >= 2013 and c61_instit = {$iInstituicaoSolicitada} order by 1) as x
                                            group by c61_codcon having count(*) > 1;" );

    $iTotalRegistrosErrados = pg_num_rows($rsSqlBuscaContasErradas);

    if ($iTotalRegistrosErrados == 0) {

      db_log($hArquivoLog, "-> PROCESSANDO INSTITUICAO: {$oStdInstituicao->nomeinst} <-");
      db_log($hArquivoLog, "Total de Registros: {$iTotalRegistrosErrados} - CONTINUANDO\n\n");
      continue;
    }


    db_log($hArquivoLog, "-> PROCESSANDO INSTITUICAO: {$oStdInstituicao->nomeinst} <-");
    db_log($hArquivoLog, "Total de Registros: {$iTotalRegistrosErrados}");

    $iAnoBase     = 2015;
    $iAnoSequente = $iAnoBase+1;

    for ($iRowConta = 0; $iRowConta < $iTotalRegistrosErrados; $iRowConta++) {

      $iCodigoConta     = db_utils::fieldsMemory($rsSqlBuscaContasErradas, $iRowConta)->c61_codcon;
      $rsBuscaReduzidos = pg_query( "select *
                                       from conplanoreduz
                                      where c61_codcon = {$iCodigoConta}
                                        and c61_instit = {$iInstituicaoSolicitada}
                                        and c61_anousu = {$iAnoBase}
                                      order by c61_anousu" );

      if (!$rsBuscaReduzidos || !pg_num_rows($rsBuscaReduzidos)) {
        db_log($hArquivoLog, "Nenhum reduzido encontrado para a conta: {$iCodigoConta}");
        continue;
      }

      $iTotalReduzidosEncontrados = pg_num_rows($rsBuscaReduzidos);

      if ($iTotalReduzidosEncontrados > 1) {
        throw new Exception("O Ano {$iAnoBase} possui reduzidos duplicados na Instituição {$iInstituicaoSolicitada} para a Conta {$iCodigoConta}.");
      }

      $oStdReduzidoBase = db_utils::fieldsMemory($rsBuscaReduzidos, 0);

      $rsBuscaReduzidos = pg_query( "select *
                                       from conplanoreduz
                                      where c61_codcon =  {$iCodigoConta}
                                        and c61_reduz  <> {$oStdReduzidoBase->c61_reduz}
                                        and c61_instit =  {$iInstituicaoSolicitada}
                                        and c61_anousu >= {$iAnoSequente}
                                      order by c61_anousu" );

      if (!$rsBuscaReduzidos) {

        db_log($hArquivoLog, "Nenhum reduzido incorreto para a Conta: {$iCodigoConta}");
        continue;
      }

      $aReduzidosProcessados = array();

      for ($iRowErrado = 0; $iRowErrado < pg_num_rows($rsBuscaReduzidos); $iRowErrado++) {

        $oDadosReduzidoErrado = db_utils::fieldsMemory($rsBuscaReduzidos, $iRowErrado);
        db_log($hArquivoLog, "Corrigindo tabela: conplanoreduz DE: {$oDadosReduzidoErrado->c61_reduz} | PARA: {$oStdReduzidoBase->c61_reduz} ANO: {$oDadosReduzidoErrado->c61_anousu}");

        $rsExists = pg_query("select exists( select *
                                               from conplanoreduz
                                              where c61_anousu = {$oDadosReduzidoErrado->c61_anousu}
                                                and c61_reduz = {$oStdReduzidoBase->c61_reduz}) as existe " );

        if (db_utils::fieldsMemory($rsExists, 0)->existe == 'f') {

          $sSqlInserirReduzido = "insert into conplanoreduz
                                  values ({$oStdReduzidoBase->c61_codcon},
                                          {$oDadosReduzidoErrado->c61_anousu},
                                          {$oStdReduzidoBase->c61_reduz},
                                          {$oStdReduzidoBase->c61_instit},
                                          {$oStdReduzidoBase->c61_codigo},
                                          {$oStdReduzidoBase->c61_contrapartida})";
          $rsInserirReduzido = pg_query($sSqlInserirReduzido);

          if (!$rsInserirReduzido) {
            throw new Exception("Impossivel incluir o reduzido para o ano {$iAnoInserir}.");
          }
        }

        db_log($hArquivoLog, "  -- Removendo conplanoexe e conplanoexesaldo para o reduzido: {$oDadosReduzidoErrado->c61_reduz} ano: {$oDadosReduzidoErrado->c61_anousu}");

        $rsDeleteConplanoexesaldo = pg_query("delete from conplanoexesaldo where c68_reduz = {$oDadosReduzidoErrado->c61_reduz} and c68_anousu = {$oDadosReduzidoErrado->c61_anousu}");
        if (!$rsDeleteConplanoexesaldo) {
          throw new Exception("Não foi possível excluir conplanoexesaldo para o reduzido {$oDadosReduzidoErrado->c61_reduz}.");
        }

        $rsDeleteConplanoexe = pg_query("delete from conplanoexe where c62_reduz = {$oDadosReduzidoErrado->c61_reduz} and c62_anousu = {$oDadosReduzidoErrado->c61_anousu}");
        if (!$rsDeleteConplanoexe) {
          throw new Exception("Não foi possível excluir conplanoexe para o reduzido {$oDadosReduzidoErrado->c61_reduz}.");
        }

        if (in_array($oDadosReduzidoErrado->c61_reduz, $aReduzidosProcessados)) {
          continue;
        }

        db_log($hArquivoLog, "  -- Corrigindo conlancamval para o reduzido: {$oDadosReduzidoErrado->c61_reduz}");

        $sSqlBuscaLancamentoContabil = "create temp table bkp_conlancamval_{$oDadosReduzidoErrado->c61_reduz} as select * from conlancamval where c69_credito = {$oDadosReduzidoErrado->c61_reduz} or c69_debito = {$oDadosReduzidoErrado->c61_reduz}";
        $rsCriaTabelaTemporaria = pg_query($sSqlBuscaLancamentoContabil);
        $rsExcluirLancamentos   = pg_query("delete from conlancamval where c69_sequen in (select c69_sequen from bkp_conlancamval_{$oDadosReduzidoErrado->c61_reduz})");
        $rsBuscaBackupLancamento = pg_query("select * from bkp_conlancamval_{$oDadosReduzidoErrado->c61_reduz}");
        for ($iRowLancamento = 0; $iRowLancamento < pg_num_rows($rsBuscaBackupLancamento); $iRowLancamento++) {

          $oStdBackupLancamento = db_utils::fieldsMemory($rsBuscaBackupLancamento, $iRowLancamento);
          if ($oStdReduzidoErrado->c61_reduz == $oStdBackupLancamento->c69_credito) {

            $iContaDebito  = $oStdBackupLancamento->c69_debito;
            $iContaCredito = $oStdReduzidoBase->c61_reduz;
          } else {
            $iContaDebito  = $oStdReduzidoBase->c61_reduz;
            $iContaCredito = $oStdBackupLancamento->c69_credito;
          }

          $rsSqlInserirLancamentoContabil = pg_query(
            "insert into conlancamval
             values ({$oStdBackupLancamento->c69_sequen},
                     {$oStdBackupLancamento->c69_anousu},
                     {$oStdBackupLancamento->c69_codlan},
                     {$oStdBackupLancamento->c69_codhist},
                     {$iContaCredito},
                     {$iContaDebito},
                     {$oStdBackupLancamento->c69_valor},
                     '{$oStdBackupLancamento->c69_data}')"
          );
          if (!$rsSqlInserirLancamentoContabil) {
            throw new Exception("Nao foi possivel incluir na tabela conlancamval {$oStdBackupLancamento->c69_codlan}");
          }
        }

        $rsAcertaContaCorrenteDetalhe = pg_query("update contacorrentedetalhe set c19_reduz = {$oStdReduzidoBase->c61_reduz} where c19_reduz = {$oDadosReduzidoErrado->c61_reduz}");
        if (!$rsAcertaContaCorrenteDetalhe) {
          throw new Exception("Nao foi possivel alterar o reduzido {$oDadosReduzidoErrado->c61_reduz} da tabela contacorrentedetalhe.");
        }

        $rsDeletaConplanoReduz = pg_query("delete from conplanoreduz where c61_reduz = {$oDadosReduzidoErrado->c61_reduz}");
        if (!$rsDeletaConplanoReduz) {
          throw new Exception("Não foi possível excluir o reduzido {$oDadosReduzidoErrado->c61_reduz}.");
        }

        $aReduzidosProcessados[] = $oDadosReduzidoErrado->c61_reduz;
      }

    }
  }

  db_log($hArquivoLog, "Processando o fechamento da contabilidade");
  foreach ($aFechamentosContabilidade as $oStdConDataConf) {

    $sValores = "{$oStdConDataConf->c99_anousu}, {$oStdConDataConf->c99_instit}, '{$oStdConDataConf->c99_data}', {$oStdConDataConf->c99_usuario}";
    $rsInserirFechamento = pg_query("insert into condataconf values ({$sValores})");
    if (!$rsInserirFechamento) {
      throw new Exception("Nao foi possivel inserir o fechamento da contabilidade.\n".print_r($oStdConDataConf));
    }
  }

  if ($lConcluirProcessamento) {

    pg_query("commit");
    db_log($hArquivoLog, " -> COMMIT <-");
  } else {

    pg_query("rollback");
    db_log($hArquivoLog, " -> ROLLBACK <-");
  }

} catch (Exception $eErro) {

  pg_query('rollback');
  db_log($hArquivoLog, " -> ROLLBACK <-");
  db_log($hArquivoLog, $eErro->getMessage());
}

/**
 * @param $hArquivoLog
 * @param $sMensagem
 */
function db_log($hArquivoLog, $sMensagem) {

  echo "$sMensagem\n";
  fwrite($hArquivoLog, "$sMensagem\n");
}

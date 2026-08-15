<?php
require(modification("libs/db_stdlib.php"));
require(modification("libs/db_conecta."."php"));
include(modification("libs/db_sessoes.php"));
include(modification("libs/db_usuariosonline.php"));
include(modification("dbforms/db_funcoes.php"));
require (modification("classes/materialestoque.model.php"));
require_once modification("libs/db_utils.php");
require_once(modification("libs/JSON.php"));

$oJson  = new services_json();
$oParam = $oJson->decode(str_replace("\\","",$_POST["json"]));

$oRetorno           = new stdClass();
$oRetorno->erro     = false;
$oRetorno->mensagem = '';
$oInstituicao       = InstituicaoRepository::getInstituicaoSessao();
try {

  db_inicio_transacao();
  switch ($oParam->exec) {

  case 'salvar' :


    $oEmpenhoFinanceiro = getEmpenho($oParam->iCodigoEmpenho);
    $oOrdemAuxiliar = new OrdemAuxiliarEmpenho($oParam->iCodigoOrdem);
    $oOrdemAuxiliar->setInstituicao($oInstituicao);
    $oOrdemAuxiliar->setEmpenho($oEmpenhoFinanceiro);
    $oOrdemAuxiliar->setDataEmissao(New DBDate(date("Y-m-d", db_getsession("DB_datausu"))));
    $oOrdemAuxiliar->setNumeroNotaFiscal($oParam->nNumeroNota);
    $oOrdemAuxiliar->setDepartamento(new DBDepartamento($_SESSION["DB_coddepto"]));
    if (!empty($oParam->sDataNota)) {
      $oOrdemAuxiliar->setDataNota(New DBDate($oParam->sDataNota));
    }
    if (!empty($oParam->sDataEntrega)) {
      $oOrdemAuxiliar->setDataEntrega(New DBDate($oParam->sDataEntrega));
    }
    foreach ($oParam->itens as $oItem) {
      $oOrdemAuxiliar->adicionarItem(new MaterialAlmoxarifado((int)$oItem->item),
        (float) $oItem->quantidade,
        (float) $oItem->valor_unitario,
        (float) $oItem->valor_total);
    }
    $oOrdemAuxiliar->salvar();
    $oRetorno->codigo_ordem = $oOrdemAuxiliar->getCodigo();
    $oRetorno->mensagem = urlencode("Ordem auxiliar incluída com sucesso!");

  break;

  case 'getSaldo':
    $oRetorno->saldo = OrdemAuxiliarEmpenho::getSaldo(getEmpenho($oParam->iCodigoEmpenho));
  break;

  case 'getDadosOrdem':

    $oOrdemAuxiliar = new OrdemAuxiliarEmpenho($oParam->iCodigoOrdem);

    $oDadosOrdem               = new stdClass();
    $oDadosOrdem->codigo       = $oOrdemAuxiliar->getCodigo();
    $oDadosOrdem->empenho      = $oOrdemAuxiliar->getEmpenho()->getCodigo()."/".$oOrdemAuxiliar->getEmpenho()->getAnoUso();
    $oDadosOrdem->saldo        = OrdemAuxiliarEmpenho::getSaldo($oOrdemAuxiliar->getEmpenho());
    $oDadosOrdem->valor_ordem  = 0;
    $oDadosOrdem->credor       = urlencode($oOrdemAuxiliar->getEmpenho()->getCgm()->getNome());
    $oDadosOrdem->itens        = array();
    $oDadosOrdem->data_emissao = $oOrdemAuxiliar->getDataEmissao()->getDate(DBDate::DATA_PTBR);
    $oDadosOrdem->numero_nota  = $oOrdemAuxiliar->getNumeroNotaFiscal();
    $oDadosOrdem->data_nota    = '';
    if ($oOrdemAuxiliar->getDataNota() != '') {
      $oDadosOrdem->data_nota = $oOrdemAuxiliar->getDataNota()->getDate(DBDate::DATA_PTBR);
    }
    $nValorOrdem               = 0;
    foreach ($oOrdemAuxiliar->getItens() as $oItem) {

      $oItemOrdem                 = new stdClass();
      $oItemOrdem->codigo         = $oItem->getCodigo();
      $oItemOrdem->item           = $oItem->getItemAlmoxarifado()->getCodigo();
      $oItemOrdem->descricao      = urlencode($oItem->getItemAlmoxarifado()->getDescricao());
      $oItemOrdem->quantidade     = $oItem->getQuantidade();
      $oItemOrdem->valor_unitario = $oItem->getValorUnitario();
      $oItemOrdem->valor_total    = $oItem->getValorTotal();
      $oDadosOrdem->itens[]       = $oItemOrdem;
      $nValorOrdem += $oItem->getValorTotal();
    }
    $oDadosOrdem->valor_ordem = $nValorOrdem;
    /*
     * Ignorar o valor da ordem atual, para permitir alterar
     */
    $oDadosOrdem->saldo += $nValorOrdem;
    $oRetorno->ordem    = $oDadosOrdem;
  break;

  case 'excluirOrdem':

    $oOrdemAuxiliar = new OrdemAuxiliarEmpenho($oParam->iCodigoOrdem);
    $oOrdemAuxiliar->excluir();
    $oRetorno->mensagem = urlencode("Ordem Auxiliar removida com sucesso");
  break;

  case 'processar':

    $oOrdemAuxiliar = new OrdemAuxiliarEmpenho((int)$oParam->iCodigoOrdem);
    $oOrdemAuxiliar->processar(new Almoxarifado((int)$oParam->iCodigoAlmoxarifado));
    $oRetorno->mensagem = "Dados processados com sucesso";
  break;
  case 'cancelarProcessamento':

    $oOrdemAuxiliar = new OrdemAuxiliarEmpenho((int)$oParam->iCodigoOrdem);
    $oOrdemAuxiliar->setSituacao(OrdemAuxiliarEmpenho::ORDEM_INCLUIDA);
    $oOrdemAuxiliar->salvar(false);
    $oRetorno->mensagem = "Dados processados com sucesso";
  break;

  }
  db_fim_transacao(false);
} catch(Exception $e) {

  if(db_utils::inTransaction()) {
    db_fim_transacao(true);
  }

  $oRetorno->erro = true;
  $oRetorno->mensagem = urlencode($e->getMessage());
}

function getEmpenho($iCodigoEmpenho) {

  if (empty($iCodigoEmpenho)) {
    throw new DBException("Não foi informado o empenho!");
  }

  $aPartesEmpenho = explode("/", $iCodigoEmpenho);
  $iNumemp        = $aPartesEmpenho[0];
  $iAnoEmpenho    = db_getsession("DB_anousu");

  if (!empty($aPartesEmpenho[1])) {
    $iAnoEmpenho = $aPartesEmpenho[1];
  }

  $oInstituicao       = InstituicaoRepository::getInstituicaoSessao();
  $oEmpenhoFinanceiro = EmpenhoFinanceiroRepository::getEmpenhoFinanceiroPorCodigoAno($iNumemp, $iAnoEmpenho,$oInstituicao);

  if (empty($oEmpenhoFinanceiro)) {
    throw new BusinessException("empenho {$iNumemp}/{$iAnoEmpenho} não encontrado para a instituição");
  }
  return $oEmpenhoFinanceiro;
}
echo json_encode($oRetorno);
<?php

/**
 * Created by PhpStorm.
 * User: dbseller
 * Date: 26/04/16
 * Time: 13:03
 */
class OrdemAuxiliarEmpenhoItem {

  private $iCodigo;

  /**
   * @var MaterialAlmoxarifado
   */
  private $oItemAlmoxarifado;

  /**
   * @var float
   */
  private $nQuantidade = 0;
  /**
   * @var float
   */
  private $nValorUnitario = 0;
  /**
   * @var float
   */
  private $nValorTotal= 0;

  /**
   * @return mixed
   */
  public function getCodigo() {

    return $this->iCodigo;
  }

  /**
   * @param mixed $iCodigo
   */
  public function setCodigo($iCodigo) {

    $this->iCodigo =  $iCodigo;
  }

  /**
   * @return MaterialAlmoxarifado
   */
  public function getItemAlmoxarifado() {

    return $this->oItemAlmoxarifado;
  }

  /**
   * @param MaterialAlmoxarifado $oItemAlmoxarifado
   */
  public function setItemAlmoxarifado(MaterialAlmoxarifado $oItemAlmoxarifado) {

    $this->oItemAlmoxarifado = $oItemAlmoxarifado;
  }

  /**
   * @return float
   */
  public function getQuantidade() {

    return $this->nQuantidade;
  }

  /**
   * @param float $nQuantidade
   */
  public function setQuantidade($nQuantidade) {

    $this->nQuantidade = $nQuantidade;
  }

  /**
   * @return float
   */
  public function getValorUnitario() {

    return $this->nValorUnitario;
  }

  /**
   * @param float $nValorUnitario
   */
  public function setValorUnitario($nValorUnitario) {

    $this->nValorUnitario = $nValorUnitario;
  }

  /**
   * @return float
   */
  public function getValorTotal() {

    return $this->nValorTotal;
  }

  /**
   * @param float $nValorTotal
   */
  public function setValorTotal($nValorTotal) {

    $this->nValorTotal = $nValorTotal;
  }

  public function salvar($iCodigoOrdem = null) {

    $oDaoOrdemauxiliarItens                       = new cl_ordemauxiliarempenhoitens();
    $oDaoOrdemauxiliarItens->sequencial           = null;
    $oDaoOrdemauxiliarItens->ordemauxiliarempenho = $iCodigoOrdem;
    $oDaoOrdemauxiliarItens->matmater             = $this->getItemAlmoxarifado()->getCodigo();
    $oDaoOrdemauxiliarItens->quantidade           = $this->getQuantidade();
    $oDaoOrdemauxiliarItens->valorunitario        = $this->getValorUnitario();
    $oDaoOrdemauxiliarItens->valortotal           = $this->getValorTotal();
    $oDaoOrdemauxiliarItens->observacao           = '';
    $oDaoOrdemauxiliarItens->incluir(null);
    if ($oDaoOrdemauxiliarItens->erro_status == 0) {
      throw new BusinessException("Erro ao incluir dados do item");
    }
  }
}
<?php

class OrdemAuxiliarEmpenho {

  private $iCodigo = null;

  const ORDEM_INCLUIDA = 1;

  const ORDEM_PROCESSADA = 2;

  /**
   * @var DBDate
   */
  private $oDataEmissao = null;

  /**
   * @var OrdemAuxiliarEmpenhoItem[]
   */
  private $aItens = array();

  private $aDemonstrativos = array();

  private $iCodigoEmpenho;

  /**
   * @var Instituicao
   */
  private $oInstituicao;
  /**
   * @var EmpenhoFinanceiro
   */
  private $oEmpenho;

  /**
   * @var
   */
  private $iSituacao = 1;

  private $numero_nota;

  /**
   * @var DBDate
   */
  private $oDataNota;

  /**
   * @var DBDate
   */
  private $oDataEntrega;

  /**
   * @var DBDepartamento
   */
  private $oDepartamento;

  /**
   * OrdemAuxiliarEmpenho constructor.
   * @param null $iCodigo
   * @throws \BusinessException
   */
  public function __construct($iCodigo = null) {

    if (empty($iCodigo)) {
      return;
    }
    $oDaoOrdemAuxiliar = new cl_ordemauxiliarempenho();
    $oDados = db_utils::getRowFromDao($oDaoOrdemAuxiliar, array($iCodigo));
    if (empty($oDados)) {
      throw new BusinessException("Ordem Auxiliar de código {$iCodigo} não encontrado no sistema");
    }
    $this->oInstituicao = InstituicaoRepository::getInstituicaoByCodigo($oDados->instituicao);
    $this->oEmpenho     = EmpenhoFinanceiroRepository::getEmpenhoFinanceiroPorNumero($oDados->empempenho);
    $this->setDataEmissao(new DBDate($oDados->dataemissao));

    if (!empty($oDados->data_nota_fiscal)) {
      $this->setDataNota(new DBDate($oDados->data_nota_fiscal));
    }

    if (!empty($oDados->data_entrega)) {
      $this->setDataEntrega(new DBDate($oDados->data_entrega));
    }
    $this->numero_nota    = $oDados->numero_nota_fiscal;
    $this->iCodigoEmpenho = $oDados->empempenho;
    $this->iCodigo = $iCodigo;
    if (!empty($oDados->departamento)) {
      $this->oDepartamento = new DBDepartamento($oDados->departamento);
    }
  }

  /**
   * @param \EmpenhoFinanceiro $oEmpenhoFinanceiro
   * @return mixed
   * @throws \DBException
   */
  public static function getValorUtilizado(EmpenhoFinanceiro $oEmpenhoFinanceiro) {

    $oDaoOrdemAuxiliaItem = new cl_ordemauxiliarempenhoitens();
    $sCampos   = "coalesce(sum(valortotal), 0) as valor";
    $sWhere    = "empempenho = {$oEmpenhoFinanceiro->getNumero()}";
    $sSqlValor    = $oDaoOrdemAuxiliaItem->sql_query(null, "$sCampos", null, $sWhere);
    $rsSaldoTotal = db_query($sSqlValor);
    if (!$rsSaldoTotal) {
      throw new DBException("Erro ao pesquisar valor gasto do empenho");
    }
    return db_utils::fieldsMemory($rsSaldoTotal, 0)->valor;
  }

  /**
   * @param \EmpenhoFinanceiro $oEmpenhoFinanceiro
   * @return float
   * @throws \DBException
   */
  public static function getSaldo(EmpenhoFinanceiro $oEmpenhoFinanceiro) {

    $nSaldo    = ($oEmpenhoFinanceiro->getValorEmpenho() - $oEmpenhoFinanceiro->getValorAnulado());
    $nValorGasto = self::getValorUtilizado($oEmpenhoFinanceiro);
    return $nSaldo - $nValorGasto;

  }

  /**
   * @return null
   */
  public function getCodigo() {

    return $this->iCodigo;
  }

  /**
   * @param null $iCodigo
   */
  public function setCodigo($iCodigo) {

    $this->iCodigo = $iCodigo;
  }

  /**
   * @return DBDate
   */
  public function getDataEmissao() {

    return $this->oDataEmissao;
  }

  /**
   * @return Instituicao
   */
  public function getInstituicao() {

    return $this->oInstituicao;
  }

  /**
   * @param Instituicao $oInstituicao
   */
  public function setInstituicao(Instituicao $oInstituicao) {

    $this->oInstituicao = $oInstituicao;
  }


  /**
   * @param DBDate $oDataEmissao
   */
  public function setDataEmissao(DBDate $oDataEmissao) {

    $this->oDataEmissao = $oDataEmissao;
  }

  /**
   * @param \MaterialAlmoxarifado $oMaterial
   * @param                       $nQuantidade
   * @param                       $nValorUnitario
   * @param                       $nValorTotal
   * @return \OrdemAuxiliarEmpenhoItem
   */
  public function adicionarItem (MaterialAlmoxarifado $oMaterial, $nQuantidade, $nValorUnitario, $nValorTotal) {

    $oItem = new OrdemAuxiliarEmpenhoItem();
    $oItem->setItemAlmoxarifado($oMaterial);
    $oItem->setQuantidade($nQuantidade);
    $oItem->setValorUnitario($nValorUnitario);
    $oItem->setValorTotal($nValorTotal);

    $this->aItens[] = $oItem;
    return $oItem;
  }

  /**
   * @return \OrdemAuxiliarEmpenhoItem[] ;
   * @throws \DBException
   */
  public function getItens() {

    if (count($this->aItens) > 0) {
      return $this->aItens;
    }
    $oDaoItens = new cl_ordemauxiliarempenhoitens();
    $sSqlItens = $oDaoItens->sql_query_file(null, "*", 'sequencial', "ordemauxiliarempenho = {$this->getCodigo()}");
    $rsItens   = db_query($sSqlItens);
    if (!$rsItens) {
      throw new DBException("Erro ao pesquisar itens da ordem auxiliar");
    }
    $iTotalItens = pg_num_rows($rsItens);
    for ($iItem = 0; $iItem < $iTotalItens; $iItem++) {

      $oDadosItem = db_utils::fieldsMemory($rsItens, $iItem);

      $oItem      = new OrdemAuxiliarEmpenhoItem();
      $oItem->setCodigo($oDadosItem->sequencial);
      $oItem->setItemAlmoxarifado(new MaterialAlmoxarifado($oDadosItem->matmater));
      $oItem->setValorUnitario($oDadosItem->valorunitario);
      $oItem->setQuantidade($oDadosItem->quantidade);
      $oItem->setValorTotal($oDadosItem->valortotal);
      $this->aItens[] = $oItem;
    }
    return $this->aItens;
  }

  /**
   * @return \DadosDemonstrativo[] ;
   * @throws \DBException
   */
  public function getDemonstrativo(EmpenhoFinanceiro $oEmpenhoFinanceiro) {

    if (count($this->aDemonstrativos) > 0) {
      return $this->aDemonstrativos;
    }
    
    $sCampos  = "ordemauxiliarempenho.sequencial , ";
    $sCampos .= "ordemauxiliarempenho.numero_nota_fiscal , ";
    $sCampos .= "ordemauxiliarempenho.dataemissao , ";
    $sCampos .= "sum(ordemauxiliarempenhoitens.valortotal) as debito ";

    $sSqlDemonstrativo = "  SELECT  {$sCampos} FROM plugins.ordemauxiliarempenho
                                    INNER JOIN  plugins.ordemauxiliarempenhoitens ON  ordemauxiliarempenhoitens.ordemauxiliarempenho = ordemauxiliarempenho.sequencial
                                    WHERE empempenho = {$oEmpenhoFinanceiro->getNumero()}  GROUP BY 1,2,3";
    $rsDemontrativo    = db_query($sSqlDemonstrativo);
    if (!$rsDemontrativo) {
      throw new DBException("Erro ao pesquisar dados do demonstrativo");
    }
    $oDemonstrativo = new stdClass();

    $nSaldo = ($oEmpenhoFinanceiro->getValorEmpenho() - $oEmpenhoFinanceiro->getValorAnulado());
    $oDemonstrativo->tipo    = "NE:";
    $oDemonstrativo->numero  = $oEmpenhoFinanceiro->getCodigo();
    $oDemonstrativo->data    = $oEmpenhoFinanceiro->getDataEmissao();
    $oDemonstrativo->nrm     = '';
    $oDemonstrativo->credito = $nSaldo;
    $oDemonstrativo->debito  = 0;
    $oDemonstrativo->saldo   = $nSaldo;

    $this->aDemonstrativos[] = $oDemonstrativo;

    $iTotalDemonstrativo = pg_num_rows($rsDemontrativo);
    for ($iDem = 0; $iDem < $iTotalDemonstrativo; $iDem++) {

      $oDadosDem = db_utils::fieldsMemory($rsDemontrativo, $iDem);

      $oDemonstrativoItems = new stdClass();

      $nSaldo = ($nSaldo-$oDadosDem->debito);
      $oDemonstrativoItems->tipo    = "NF";
      $oDemonstrativoItems->numero  = $oDadosDem->numero_nota_fiscal;
      $oDemonstrativoItems->data    = $oDadosDem->dataemissao;
      $oDemonstrativoItems->nrm     = $oDadosDem->sequencial;
      $oDemonstrativoItems->credito = 0;
      $oDemonstrativoItems->debito  = $oDadosDem->debito;
      $oDemonstrativoItems->saldo   = $nSaldo;

      $this->aDemonstrativos[] = $oDemonstrativoItems;
    }
    return $this->aDemonstrativos;
  }

  /**
   *return \EmpenhoFinanceiro
   */
  public function getEmpenho() {

    return $this->oEmpenho;
  }

  /**
   * Persiste os dados da ordem auxiliar
   */
  public function salvar() {

    $oDaoOrdemauxiliar                     = new cl_ordemauxiliarempenho();
    $oDaoOrdemauxiliar->sequencial         = $this->iCodigo;
    $oDaoOrdemauxiliar->empempenho         = $this->getEmpenho()->getNumero();
    $oDaoOrdemauxiliar->dataemissao        = $this->getDataEmissao()->getDate();
    $oDaoOrdemauxiliar->instituicao        = $this->getInstituicao()->getCodigo();
    $oDaoOrdemauxiliar->situacao           = $this->getSituacao();
    $oDaoOrdemauxiliar->numero_nota_fiscal = $this->getNumeroNotaFiscal();
    if (!empty($this->oDataNota)) {
      $oDaoOrdemauxiliar->data_nota_fiscal = $this->getDataNota()->getDate();
    }
    if (!empty($this->oDataEntrega)) {
      $oDaoOrdemauxiliar->data_entrega = $this->getDataEntrega()->getDate();
    }
    if (empty($this->iCodigo)) {

      if (!empty($this->oDepartamento)) {
        $oDaoOrdemauxiliar->departamento = $this->oDepartamento->getCodigo();
      }

      $oDaoOrdemauxiliar->incluir();
      $this->iCodigo = $oDaoOrdemauxiliar->sequencial;
    } else {
      $oDaoOrdemauxiliar->alterar($this->iCodigo);
    }
    if ($oDaoOrdemauxiliar->erro_status == 0) {
      throw new DBException("Erro ao salvar dados da ordem auxiliar");
    }

    $oDaoOrdemauxiliarItens = new cl_ordemauxiliarempenhoitens();
    $oDaoOrdemauxiliarItens->excluir(null, "ordemauxiliarempenho = {$this->iCodigo}");
    foreach ($this->getItens() as $oItem) {
      $oItem->salvar($this->getCodigo());
    }
  }

  /**
   * Define o empenho
   * @param \EmpenhoFinanceiro $oEmpenho
   */
  public function setEmpenho(EmpenhoFinanceiro $oEmpenho) {

    $this->oEmpenho       = $oEmpenho;
    $this->iCodigoEmpenho = $oEmpenho->getNumero();
  }

  public function excluir() {

    if (!db_utils::inTransaction()) {
      throw new DBException("sem transacao com o banco de dados");
    }
    $oDaoOrdemauxiliar     = new cl_ordemauxiliarempenho();
    $oDaoOrdemauxiliarItem = new cl_ordemauxiliarempenhoitens();
    $oDaoOrdemauxiliarItem->excluir(null, "ordemauxiliarempenho = {$this->iCodigo}");
    $oDaoOrdemauxiliar->excluir($this->getCodigo());
  }

  /**
   * @param \Almoxarifado $oDepartamento
   * @throws \DBException
   */
  public function processar(Almoxarifado $oDepartamento) {

    if (!db_utils::inTransaction()) {
      throw new DBException("Transação com o banco de dados não encontrada. Processamento cancelado");
    }
    $aItens =  $this->getItens();
    foreach ($aItens as $oItem) {

      $this->processarEntrada($oItem->getItemAlmoxarifado(), $oDepartamento, $oItem->getQuantidade(), $oItem->getValorTotal());
      $oMaterialEstoque = new materialEstoque($oItem->getItemAlmoxarifado()->getCodigo());
      $oMaterialEstoque->setCodDepto($oDepartamento->getCodigo());
      $aObservacao = "Entrada por ordem auxiliar nº {$this->getCodigo()}";
      $oMaterialEstoque->saidaMaterial($oItem->getQuantidade(), $aObservacao, false);
    }
    $this->setSituacao(self::ORDEM_PROCESSADA);
    $this->salvar();
  }

  /**
   * @param \MaterialAlmoxarifado $oMaterial
   * @param \Almoxarifado         $oDepto
   * @param                       $nQuantidadeEntrada
   * @param                       $nValorTotal
   */
  private function processarEntrada(MaterialAlmoxarifado $oMaterial, Almoxarifado $oDepto, $nQuantidadeEntrada, $nValorTotal) {

    $oDataMovimentacao           = new DBDate(date('Y-m-d'), db_getsession("DB_datausu"));
    $oMaterialEstoquAlmoxarifado = MaterialEstoqueAlmoxarifado::getEstoquePorMaterialDepartamento($oMaterial, $oDepto);

    $oMovimentacaoEstoque        = new MaterialEstoqueMovimentacao();
    $oMovimentacaoEstoque->setData($oDataMovimentacao);
    $oMovimentacaoEstoque->setCodigoDepartamento($oDepto->getCodigo());
    $oMovimentacaoEstoque->setDepartamento($oDepto);
    $oMovimentacaoEstoque->setUsuario(UsuarioSistemaRepository::getPorCodigo(db_getsession("DB_id_usuario")));
    $oMovimentacaoEstoque->setObservacao("Entrada por ordem auxiliar nº {$this->getCodigo()}");
    $oMovimentacaoEstoque->setMovimento(TipoMovimentacaoEstoqueRepository::getTipoMovimentaoPorCodigo(3));
    $oMovimentacaoEstoque->setHora(db_hora());
    $oMovimentacaoEstoque->salvar();

    $oQuantidadesMovimentacao = new MaterialEstoqueItem();
    $oQuantidadesMovimentacao->setData($oDataMovimentacao);
    $oQuantidadesMovimentacao->setEstoque($oMaterialEstoquAlmoxarifado);
    $oQuantidadesMovimentacao->setServico(false);
    $oQuantidadesMovimentacao->setQuantidade($nQuantidadeEntrada);
    $oQuantidadesMovimentacao->setQuantidadeAtendida(0);
    $oQuantidadesMovimentacao->setValor($nValorTotal);
    $oQuantidadesMovimentacao->salvar();
    MaterialEstoqueItem::vincularMovimentacaoComItem($oQuantidadesMovimentacao, $oMovimentacaoEstoque, $nQuantidadeEntrada);
  }

  /**
   * @return mixed
   */
  public function getSituacao() {
    return $this->iSituacao;
  }

  /**
   * @param mixed $iSituacao
   */
  public function setSituacao($iSituacao) {

    $this->iSituacao = $iSituacao;
  }

  /**
   * @param $nNumeroNota
   */
  public function setNumeroNotaFiscal($nNumeroNota) {
    $this->numero_nota = $nNumeroNota;
  }

  public function getNumeroNotaFiscal() {
    return $this->numero_nota;
  }

  /**
   * @param \DBDate $oDataNota
   */
  public function setDataNota(DBDate $oDataNota) {
    $this->oDataNota = $oDataNota;
  }

  /**
   * @param \DBDate $oDataEntrega
   */
  public function setDataEntrega(DBDate $oDataEntrega) {
    $this->oDataEntrega = $oDataEntrega;
  }

  /**
   * @return \DBDate
   */
  public function getDataNota() {
    return $this->oDataNota;
  }

  /**
   * @return \DBDate
   */
  public function getDataEntrega() {
    return $this->oDataEntrega;
  }

  /**
   *
   */
  public function setDepartamento($oDepartamento) {
    $this->oDepartamento = $oDepartamento;
  }

  /**
   * @return \DBDepartamento
   */
  public function getDepartamento() {
    return $this->oDepartamento;
  }

  public function getValor() {

    $nValor = 0;
    foreach ($this->getItens() as $oItem) {
      $nValor = $oItem->getValorTotal();
    }
    return $nValor;
  }
}
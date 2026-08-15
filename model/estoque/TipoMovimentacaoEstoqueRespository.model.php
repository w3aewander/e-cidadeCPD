<?php
/**
 * Repositório para o Tipo de Movimentação do Estoque
 * @author Matheus Felini <matheus.felini@dbseller.com.br>
 * @package patrimonio
 * @subpackage material
 * @version $Revision: 1.1 $
 */
class TipoMovimentacaoEstoqueRespository {

  /**
   * Coleção com os tipos de movimentação do estoque
   * @var TipoMovimentacaoEstoque[]
   */
  private $aTipoMovimentacao = array();

  /**
   * Instancia de TipoMovimentacaoEstoqueRespository
   * @var TipoMovimentacaoEstoqueRespository
   */
  private static $oInstancia;

  /**
   * Método privado construtor
   */
  private function __construct() {}

  /**
   * Método mágico privado __clone
   */
  private function __clone() {}


  /**
   * Retorna a instancia de TipoMovimentacaoEstoqueRespository
   * @return TipoMovimentacaoEstoqueRespository
   */
  protected function getInstancia() {

    if(self::$oInstancia == null) {
      self::$oInstancia = new TipoMovimentacaoEstoqueRespository();
    }
    return self::$oInstancia;
  }

  /**
   * Retorna o tipo de movimentação do estoque de acordo com o código informado
   * @param $iCodigoMovimentacao
   * @return TipoMovimentacaoEstoque
   */
  public static function getTipoMovimentaoPorCodigo($iCodigoMovimentacao) {

    if ( ! array_key_exists($iCodigoMovimentacao, TipoMovimentacaoEstoqueRespository::getInstancia()->aTipoMovimentacao)) {
      TipoMovimentacaoEstoqueRespository::getInstancia()->aTipoMovimentacao[$iCodigoMovimentacao] = new TipoMovimentacaoEstoque($iCodigoMovimentacao);
    }
    return TipoMovimentacaoEstoqueRespository::getInstancia()->aTipoMovimentacao[$iCodigoMovimentacao];
  }
}
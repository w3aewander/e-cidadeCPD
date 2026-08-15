<?php

/**
 * Class cl_certidaotemposervico
 */
class cl_certidaotemposervico extends DAOBasica {

  /**
   * Nome do Esquema
   */
  const NOME_SCHEMA = 'plugins';

  /**
   * Nome da tabela
   */
  const NOME_TABELA = 'certidaotemposervico';

  public function __construct() {
    parent::__construct(self::NOME_SCHEMA.".".self::NOME_TABELA);
  }
}

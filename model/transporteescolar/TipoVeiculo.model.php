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

class TipoVeiculo{

  /**
   * Codigo do tipo de transporte
   * @var integer
   */
  protected $codigo = null;

  /**
   * Descrica do tipo
   * @var string
   */
  protected $descricao = '';

  /**
   * instancia um tipo de transporte
   * @param integer $iCodigo codigo do tipo de transporte que deve ser instanciado
   */
  public function __construct($iCodigo) {

    if (!empty($iCodigo)) {

      $daoTipoVeiculo = new cl_tipoveiculo();
      $sSqlTipoVeiculo = $daoTipoVeiculo->sql_query_file($iCodigo);
      $rsTipoVeiculo   = db_query($sSqlTipoVeiculo);
      if (pg_num_rows($rsTipoVeiculo) == 0) {
        throw new Exception('Não há tipo de veículo cadastrado');
      }

      $oDadosTipoVeiculo = db_utils::fieldsMemory($rsTipoVeiculo, 0);
      $this->codigo    = $oDadosTipoVeiculo->tre17_codigo;
      $this->descricao = $oDadosTipoVeiculo->tre17_descricao;
    }
  }

  /**
   * Retorna o codigo
   * @return integer
   */
  public function getCodigo() {
    return $this->codigo;
  }

  /**
   * Retorna a descricao do tipo de transporte
   * @return string
   */
  public function getDescricao() {
    return $this->descricao;
  }
}
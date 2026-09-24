<?php
/**
 * Classe do tipo Collection para DbModulos
 * @author  Fábio Esteves <fabio.esteves@dbseller.com.br>
 * @package configuracao
 *
 */
class DbModulosCollection {
  
  /**
   * Array de instâncias de DbModulos
   * @var array
   */
  protected static $aDbModulos = array();

  /**
   * Adiciona uma instância de DbModulos ao array
   * @param DbModulos $oDbModulos
   */
  public static function adicionaModulo( DbModulos $oDbModulos ) {
    
    if ( !array_key_exists( $oDbModulos->getIdItem(), $this->aDbModulos ) ) {
      self::$aDbModulos[ $oDbModulos->getIdItem() ] = $oDbModulos;
    }
  }
  
  /**
   * Deleta uma instância de DbModulos do array
   * @param DbModulos $oDbModulos
   */
  public static function deletaModulo( DbModulos $oDbModulos ) {
    
    if ( !array_key_exists( $oDbModulos->getIdItem(), $this->aDbModulos ) ) {
      unset( self::$aDbModulos[ $oDbModulos->getIdItem() ] );
    }
  }
  
  /**
   * Busca e retorna o array dos módulos existentes
   * @return DbModulos[]
   */
  public static function buscaModulos() {
    
    $oDaoDbModulos    = new cl_db_modulos();
    $sSqlDbModulos    = $oDaoDbModulos->sql_query_file( null, "*", "nome_modulo" );
    $rsDbModulos      = db_query( $sSqlDbModulos );
    $iLinhasDbModulos = pg_num_rows( $rsDbModulos );
    
    if ( $rsDbModulos && $iLinhasDbModulos > 0 ) {
      
      for ( $iContador = 0; $iContador < $iLinhasDbModulos; $iContador++ ) {
        
        $oRetornoDbModulos  = db_utils::fieldsMemory( $rsDbModulos, $iContador );
        $oDbModulos         = new DbModulos( $oRetornoDbModulos->id_item );
        
        if ( !array_key_exists( $oDbModulos->getIdItem(), self::$aDbModulos ) ) {
          self::$aDbModulos[ $oDbModulos->getIdItem() ] = $oDbModulos;
        }
      }
    }
    
    return self::$aDbModulos;
  }
} 
?>
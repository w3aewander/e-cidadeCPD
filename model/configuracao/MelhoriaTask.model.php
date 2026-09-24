<?php

use \ECidade\V3\Extension\Registry;

require_once ('model/configuracao/Task.model.php');
require_once ('interfaces/iTarefa.interface.php');

class MelhoriaTask extends Task implements iTarefa {

  /**
   * Inicia Execucao da Tarefa
   */
  public function iniciar() {

    parent::iniciar();

    require('libs/db_conn.php');
    $sConnection = "host=$DB_SERVIDOR dbname=$DB_BASE port=$DB_PORTA user=$DB_USUARIO password=$DB_SENHA";
    global $conn;
    $conn = pg_connect($sConnection);

    $sysConfig = Registry::get('app.config');

    try {

      // busca a ultima melhoria atualizada
      $iCodigoUltimaMelhoria  = Melhoria::getUltimaMelhoriaAtualizada();
      $oMelhoriaService       = new MelhoriaService(new Plugin(null, 'EntregaContinua'), $sysConfig);

      // busca as novas melhoria a partir da ultima atualizada
      $aAtualizacoes = $oMelhoriaService->getAtualizacoes($iCodigoUltimaMelhoria);
      
      if (count($aAtualizacoes) == 0) {
        throw new Exception('Não há novas melhorias para atualização.');
      }

      $oData = new DBDate(date('Y-m-d'));
      $oUsuario = UsuarioSistemaRepository::getPorCodigo(1);

      foreach ($aAtualizacoes as $oStdMelhoria) {

        db_inicio_transacao();

        Melhoria::salvar($oStdMelhoria->id, $oUsuario, $oData);
        $oMelhoriaService = new MelhoriaService(new Plugin(null, 'EntregaContinua'), $sysConfig);
        $oMelhoriaService->atualizar($oStdMelhoria);

        db_fim_transacao(false);
      }


    } catch (Exception $e) {
      $this->log($e->getMessage());
      db_fim_transacao(true);
    }

    parent::terminar();
  }

  /**
   * Para execução da Tarefa
   */
  public function cancelar(){

  }
  
  /**
   * Aborta a Execução da Tarefa
   */
  public function abortar(){
    
  }
}

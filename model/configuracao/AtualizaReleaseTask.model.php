<?php

require_once(modification('model/configuracao/Task.model.php'));
require_once(modification('interfaces/iTarefa.interface.php'));

class AtualizaReleaseTask extends Task implements iTarefa {

  /**
   * Inicia Execucao da Tarefa
   */
  public function iniciar() {

    parent::iniciar();


    try {

      require_once(modification("libs/db_conn.php"));

      session_register("DB_login"      , "dbseller");
      session_register("DB_id_usuario" , 1);
      session_register("DB_servidor" , $DB_SERVIDOR );
      session_register("DB_base"     , $DB_BASE     );
      session_register("DB_porta"    , $DB_PORTA    );
      session_register("DB_user"     , $DB_USUARIO  );
      session_register("DB_senha"    , $DB_SENHA    );

      global $HTTP_SESSION_VARS;

      $HTTP_SESSION_VARS["DB_servidor"] =  $DB_SERVIDOR ;
      $HTTP_SESSION_VARS["DB_base"]     =  $DB_BASE     ;
      $HTTP_SESSION_VARS["DB_porta"]    =  $DB_PORTA    ;
      $HTTP_SESSION_VARS["DB_user"]     =  $DB_USUARIO  ;
      $HTTP_SESSION_VARS["DB_senha"]    =  $DB_SENHA    ;

      db_putsession("DB_login"      , "dbseller");
      db_putsession("DB_id_usuario" , 1);
      db_putsession("DB_servidor" , $DB_SERVIDOR );
      db_putsession("DB_base"     , $DB_BASE     );
      db_putsession("DB_porta"    , $DB_PORTA    );
      db_putsession("DB_user"     , $DB_USUARIO  );
      db_putsession("DB_senha"    , $DB_SENHA    );


      require_once(modification("libs/db_conecta_plugin.php"));

      global $conn;

      $oReleaseAtual = new Release(null, true);

      require_once(modification("model/configuracao/ReleaseService.service.php"));

      $oReleaseService = new ReleaseService();

      if ( !$oReleaseService->verificaAtualizacao($oReleaseAtual) ) {

        $oReleaseService->downloadPacote($oReleaseAtual);
        $oReleaseService->atualiza($oReleaseAtual);

        $this->log("Sistema atualizado com sucesso.");
      } else {
        $this->log("O sistema não foi atualizado.");
      }

    } catch (Exception $e) {
      $this->log("Erro na execução da atualização:\n{$e->getMessage()}");
    }


    $this->log("Finalizando Task de Atualização da Release.");

    parent::terminar();

  }

  /**
   * Para execução da Tarefa
   */
  public function cancelar() {

  }

  /**
   * Aborta a Execução da Tarefa
   */
  public function abortar() {

  }

}


?>

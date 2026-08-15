<?php

use Classes\PostgresMigration;

/**
 * Class M9919CampoSiConfiInstituicao
 *
 * @author Leonardo Oliveira <leonardo.malia@dbseller.com.br>
 */
class M9919CampoSiConfiInstituicao extends PostgresMigration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $sSql = <<<SQL
	select 1;
SQL;

        $this->execute($sSql);
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $sSql = <<<SQL
          ALTER TABLE db_config DROP COLUMN db21_codsiconfi;
          DELETE FROM db_sysarqcamp WHERE codcam = 1009629;
          DELETE FROM db_syscampo WHERE codcam = 1009629;
SQL;

        $this->execute($sSql);
    }
}

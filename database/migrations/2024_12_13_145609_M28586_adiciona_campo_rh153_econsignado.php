<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class M28586AdicionaCampoRh153Econsignado extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->upCriaCampoEconsignado();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->downCriaCampoEconsignado();
    }

    private function upCriaCampoEconsignado()
    {
        $sql = <<<SQL
            ALTER EVENT TRIGGER evtg_dicionario_gatilho_ddl ENABLE;
            ALTER EVENT TRIGGER evtg_dicionario_gatilho_ddl_drop ENABLE;

            ALTER TABLE pessoal.rhconsignadomovimentoservidorrubrica ADD rh153_econsignado varchar(10);

            SELECT fc_atualiza_dicionario_apartir_comentario('table column',
            'pessoal.rhconsignadomovimentoservidorrubrica.rh153_econsignado',
            '{ "descricao": "Informações de desconto do empréstimo em folha",
            "rotulo": "Número eConsignado",
            "rotulorel": "Número eConsignado",
            "maiusculo": false,
            "autocompl": false,
            "aceitatipo": 0,
            "tamanho": 10,
            "tipoobj": "text"
            }') ;

            ALTER EVENT TRIGGER evtg_dicionario_gatilho_ddl DISABLE;
            ALTER EVENT TRIGGER evtg_dicionario_gatilho_ddl_drop DISABLE;
SQL;
        DB::connection()->getPdo()->exec($sql);
    }

    private function downCriaCampoEconsignado()
    {
        $sql = <<<SQL
        ALTER EVENT TRIGGER evtg_dicionario_gatilho_ddl ENABLE;
        ALTER EVENT TRIGGER evtg_dicionario_gatilho_ddl_drop ENABLE;

        ALTER TABLE pessoal.rhconsignadomovimentoservidorrubrica DROP COLUMN rh153_econsignado;

        ALTER EVENT TRIGGER evtg_dicionario_gatilho_ddl DISABLE;
        ALTER EVENT TRIGGER evtg_dicionario_gatilho_ddl_drop DISABLE;
SQL;
        DB::connection()->getPdo()->exec($sql);
    }
}

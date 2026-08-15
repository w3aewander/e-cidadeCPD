<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class M30002AuditoriaLiccomissao extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::unprepared(<<<SQL
            select configuracoes.fc_auditoria_cria_funcao('licitacao.liccomissao');
SQL
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::unprepared(<<<SQL
            select configuracoes.fc_auditoria_remove_funcao('licitacao.liccomissao');
SQL
        );
    }
}

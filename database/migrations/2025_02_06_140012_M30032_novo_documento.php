<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class M30032NovoDocumento extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::unprepared(<<<SQL
insert into contabilidade.conhistdoc values (2037, 'SALDOS DE RPNP INSCRITOS A LIQUIDAR', 2000);
insert into contabilidade.vinculoeventoscontabeis (c115_sequencial, c115_conhistdocinclusao, c115_conhistdocestorno)
values (nextval('contabilidade.vinculoeventoscontabeis_c115_sequencial_seq'), 2037, null)
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
delete from contabilidade.vinculoeventoscontabeis where c115_conhistdocinclusao = 2037;
delete from contabilidade.conhistdoc where c53_coddoc = 2037;
SQL
        );
    }
}

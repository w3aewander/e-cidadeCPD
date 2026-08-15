<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class M29773InsercaoTipoDocumentosPadraoAtas extends Migration
{
    private function insereDados()
    {
        DB::unprepared(<<<SQL
insert into db_tipodoc (db08_codigo, db08_descr) values (6001, 'ATA DE CLASSIFICAÇÃO');
insert into db_tipodoc (db08_codigo, db08_descr) values (6002, 'ATA DE AVANÇO');
insert into db_tipodoc (db08_codigo, db08_descr) values (6003, 'ATA DE RECLASSIFICAÇÃO');
insert into db_tipodoc (db08_codigo, db08_descr) values (6004, 'ATA DE FECHAMENTO DE LACUNA');
SQL
        );
    }

    private function removeDados()
    {
        DB::unprepared(<<<SQL
delete from db_tipodoc where db08_codigo in (6001, 6002, 6003, 6004);
SQL
        );
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->insereDados();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->removeDados();
    }
}

<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class M27159AddItemDeMenuConsultaAtendimento extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $sql = "
            insert into db_itensmenu( id_item ,descricao ,help ,funcao ,itemativo ,manutencao ,desctec ,libcliente ) values ( 229029 ,'Consulta de Atendimento' ,'Consulta de Atendimento' ,'web/patrimonial/protocolo/consulta-atendimento' ,'1' ,'1' ,'Consulta de Atendimento' ,'true' );
            insert into db_menu( id_item ,id_item_filho ,menusequencia ,modulo ) values ( 31 ,229029 ,196 ,604 );
        ";
        DB::connection()->getPdo()->exec($sql);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $sql = "
            delete from  db_menu where id_item_filho =  229029;
            delete from db_itensmenu where id_item  =  229029;
        ";
        DB::connection()->getPdo()->exec($sql);
    }
}

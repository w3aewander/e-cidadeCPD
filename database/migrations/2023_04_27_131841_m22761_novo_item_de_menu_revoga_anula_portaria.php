<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class M22761NovoItemDeMenuRevogaAnulaPortaria extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::connection()->getPdo()->exec(<<<SQL
insert into db_itensmenu( id_item ,descricao ,help ,funcao ,itemativo ,manutencao ,desctec ,libcliente ) values ( 228914 ,'Revogação / Anulação' ,'Revogação / Anulação' ,'rec1_portaria004.php' ,'1' ,'1' ,'item de menu para anular e revogar portarias' ,'true' );
insert into db_menu( id_item ,id_item_filho ,menusequencia ,modulo ) values ( 207954 ,228914 ,4 ,2323 );
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
        DB::connection()->getPdo()->exec(<<<SQL
delete from db_menu where id_item_filho = 228914 AND modulo = 2323;
delete from db_itensmenu where id_item = 228914;
SQL
        );
    }
}

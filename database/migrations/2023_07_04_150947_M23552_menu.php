<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class M23552Menu extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $sql = "
             insert into db_itensmenu( id_item ,descricao ,help ,funcao ,itemativo ,manutencao ,desctec ,libcliente ) values ( 228952 ,'Processo/Documento Inclusão' ,'Inclusão de processo e documentos' ,'web/patrimonial/protocolo/documentos' ,'1' ,'1' ,'Inclusão de processo e documentos utilizando a estrutura do processo eletrônico ' ,'true' );
             insert into db_menu( id_item ,id_item_filho ,menusequencia ,modulo ) values ( 32 ,228952 ,576 ,604 );
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
            delete from  db_menu where id_item_filho =  228952;
            delete from db_itensmenu where id_item  =  228952;
        ";
        DB::connection()->getPdo()->exec($sql);
    }
}

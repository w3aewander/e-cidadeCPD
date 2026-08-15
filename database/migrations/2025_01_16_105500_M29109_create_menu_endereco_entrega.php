<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class M29109CreateMenuEnderecoEntrega extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::unprepared(<<<SQL
            insert into db_itensmenu( id_item ,descricao ,help ,funcao ,itemativo ,manutencao ,desctec ,libcliente ,api ) values ( 229389 ,'Endereço de Entrega' ,'Endereço de Entrega' ,'' ,'1' ,'1' ,'Endereço de entrega do cadastro.' ,'true' ,'false' );
            insert into db_menu( id_item ,id_item_filho ,menusequencia ,modulo ) values ( 32 ,229389 ,592 ,40 );
            
            insert into db_itensmenu( id_item ,descricao ,help ,funcao ,itemativo ,manutencao ,desctec ,libcliente ,api ) values ( 229390 ,'Inclusão' ,'Inclusão do Endereço de Entrega' ,'web/tributario/issqn/procedimentos/cadastro-endereco/inclusao' ,'1' ,'1' ,'Inclusão do Endereço de Entrega.' ,'true' ,'false' );
            insert into db_menu( id_item ,id_item_filho ,menusequencia ,modulo ) values ( 229389 ,229390 ,1 ,40 );
            
            insert into db_itensmenu( id_item ,descricao ,help ,funcao ,itemativo ,manutencao ,desctec ,libcliente ,api ) values ( 229391 ,'Alteração' ,'Alteração do Endereço de Entrega' ,'web/tributario/issqn/procedimentos/cadastro-endereco/alteracao' ,'1' ,'1' ,'Alteração do Endereço de Entrega.' ,'true' ,'false' );
            insert into db_menu( id_item ,id_item_filho ,menusequencia ,modulo ) values ( 229389 ,229391 ,2 ,40 );
            
            insert into db_itensmenu( id_item ,descricao ,help ,funcao ,itemativo ,manutencao ,desctec ,libcliente ,api ) values ( 229392 ,'Exclusão' ,'Exclusão de Endereço de Entrega' ,'web/tributario/issqn/procedimentos/cadastro-endereco/exclusao' ,'1' ,'1' ,'Exclusão de Endereço de Entrega.' ,'true' ,'false' );
            insert into db_menu( id_item ,id_item_filho ,menusequencia ,modulo ) values ( 229389 ,229392 ,3 ,40 );
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
            delete from db_menu where id_item_filho = 229392 AND modulo = 40;
            delete from db_menu where id_item_filho = 229391 AND modulo = 40;
            delete from db_menu where id_item_filho = 229390 AND modulo = 40;
            delete from db_menu where id_item_filho = 229389 AND modulo = 40;

            delete from db_itensmenu where id_item = 229392;
            delete from db_itensmenu where id_item = 229391;
            delete from db_itensmenu where id_item = 229390;
            delete from db_itensmenu where id_item = 229389;
SQL
        );
    }
}

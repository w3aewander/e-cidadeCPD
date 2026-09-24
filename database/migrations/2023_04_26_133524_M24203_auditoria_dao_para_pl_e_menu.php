<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class M24203AuditoriaDaoParaPlEMenu extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::connection()->getPdo()->exec(<<<SQL
select configuracoes.fc_auditoria_cria_funcao('protocolo.processo_usuarios');
insert into db_itensmenu( id_item ,descricao ,help ,funcao ,itemativo ,manutencao ,desctec ,libcliente ) values ( 228915 ,'Andamento de Documento Manutenção de atividades' ,'Andamento de Documentos Manutenção de atividades' ,'web/patrimonial/protocolo/documento-andamento-manutencao-atividade' ,'1' ,'1' ,'Manutenção de atividades no andamento do documento' ,'true' );
insert into db_menu( id_item ,id_item_filho ,menusequencia ,modulo ) values ( 32 ,228915 ,573 ,604 );

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
select configuracoes.fc_auditoria_remove_funcao('protocolo.processo_usuarios');
delete from  db_itensmenu where  id_item = 228915;
delete from db_menu where  id_item_filho = 228915;
SQL
        );
    }
}

<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Migrations\Migration;

class M22761AdicionandoNovasAtividadesExecucao extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::connection()->getPdo()->exec(<<<SQL
alter table atividadesexecucao add column p114_descricao varchar;

update atividadesexecucao set p114_descricao = 'Gerar' where p114_codigo = 1;
update atividadesexecucao set p114_descricao = 'Conferir' where p114_codigo = 2;
update atividadesexecucao set p114_descricao = 'Primeira assinatura' where p114_codigo = 3;
update atividadesexecucao set p114_descricao = 'Arquivar' where p114_codigo = 4;

insert into atividadesexecucao values (5, 'Assinar', 'Assinado', 'Segunda assinatura');
insert into atividadesexecucao values (6, 'Assinar', 'Assinado', 'Terceira assinatura');
insert into atividadesexecucao values (7, 'Assinar', 'Assinado', 'Assinatura com certificado e-cidade');

alter table atividadesexecucao alter column p114_descricao set not null;
select setval('atividadesexecucao_p114_codigo_seq', 7);
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
alter table atividadesexecucao drop column p114_descricao;
delete from atividadesexecucao where p114_codigo > 4;
select setval('atividadesexecucao_p114_codigo_seq', 4);
SQL
        );
    }
}

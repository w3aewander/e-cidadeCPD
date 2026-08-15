<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class M29095Menus extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::unprepared(<<<SQL
insert into db_itensmenu( id_item ,descricao ,help ,funcao ,itemativo ,manutencao ,desctec ,libcliente ,api )
values (229317, 'MSC', 'MSC', '', '1', '1', 'Demonstrativos Fiscais pela MSC', 'true', 'false'),
       (229318, 'RREO', 'RREO', '', '1', '1', 'Anexos da RREO pela MSC', 'true', 'false'),
       (229336, 'Anexo I - Balanço Orçamentário', 'Anexo I - Balanço Orçamentário', 'web/financeiro/contabilidade/msc/lrf/anexo/rreo/1/0' ,'1' ,'1' ,'Anexo I - Balanço Orçamentário' ,'true' ,'false' ),
       (229319, 'Anexo III - Dem. Receita Corrente Líquida', 'Anexo III - Dem. Receita Corrente Líquida', 'web/financeiro/contabilidade/msc/lrf/anexo/rreo/3/1', '1', '1', 'Anexo III pela MSC', 'true', 'false'),
       (229333 ,'RGF' ,'RGF' ,'' ,'1' ,'1' ,'Anexos da RGF' ,'true' ,'false' ),
       (229334 ,'Anexo I - Despesa com Pessoal' ,'Anexo I - Despesa com Pessoal ' ,'web/financeiro/contabilidade/msc/lrf/anexo/rgf/1/0' ,'1' ,'1' ,'Novo processamento do Anexo I - Despesa com Pessoal com dados extraídos da MSC' ,'true' ,'false' );

insert into db_menu( id_item ,id_item_filho ,menusequencia ,modulo )
values (8032, 229317, 3, 209),
       (229317, 229318, 1, 209),
       (229318, 229336, 1, 209),
       (229318, 229319, 3, 209),
       (229317, 229333, 2, 209),
       (229333, 229334, 1, 209);
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
delete from db_menu where id_item_filho in (229317, 229318, 229319, 229333, 229334);
delete from db_itensmenu where id_item in (229317, 229318, 229319, 229333, 229334);
SQL
        );
    }
}

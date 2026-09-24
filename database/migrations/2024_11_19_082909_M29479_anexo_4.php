<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class M29479Anexo4 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->menu();
        $this->relatorio();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::unprepared(<<<SQL
delete from db_menu where id_item_filho in (229342);
delete from db_itensmenu where id_item in (229342);
SQL
        );

        DB::unprepared(<<<SQL
delete from orcparamrelperiodos where o113_orcparamrel = 281;
delete from orcparamrelnota where o42_codparrel = 281;
delete from lrfvalormanual where c180_relatorio = 281;
delete from orcparamrel where o42_codparrel = 281;
SQL
        );
    }

    private function relatorio()
    {
        DB::unprepared(<<<SQL
insert into orcparamrel (o42_codparrel, o42_descrrel, o42_orcparamrelgrupo, o42_notapadrao) values (281, 'ANEXO IV - ED 14 - DEM. DAS REC E DESP DO RPPS', 1, 'FONTE: Sistema E-Cidade, Unidade Responsável: [nome_departamento]. Emissão: [data_emissao], às [hora_emissao]. Assinado Digitalmente no dia [data_emissao], às [hora_emissao].');
insert into orcparamrelperiodos (o113_sequencial, o113_periodo, o113_orcparamrel) values (nextval('orcparamrelperiodos_o113_sequencial_seq'), 11, 281);
insert into orcparamrelperiodos (o113_sequencial, o113_periodo, o113_orcparamrel) values (nextval('orcparamrelperiodos_o113_sequencial_seq'), 10, 281);
insert into orcparamrelperiodos (o113_sequencial, o113_periodo, o113_orcparamrel) values (nextval('orcparamrelperiodos_o113_sequencial_seq'), 9, 281);
insert into orcparamrelperiodos (o113_sequencial, o113_periodo, o113_orcparamrel) values (nextval('orcparamrelperiodos_o113_sequencial_seq'), 8, 281);
insert into orcparamrelperiodos (o113_sequencial, o113_periodo, o113_orcparamrel) values (nextval('orcparamrelperiodos_o113_sequencial_seq'), 7, 281);
insert into orcparamrelperiodos (o113_sequencial, o113_periodo, o113_orcparamrel) values (nextval('orcparamrelperiodos_o113_sequencial_seq'), 6, 281);
SQL
        );
    }

    /**
     * @return void
     */
    public function menu()
    {
        DB::unprepared(<<<SQL
insert into db_itensmenu( id_item ,descricao ,help ,funcao ,itemativo ,manutencao ,desctec ,libcliente ,api )
  values (229342 ,'Anexo IV - Dem.das Rec e Desp do RPPS' ,'Anexo IV - Dem. das Rec e Desp do RPPS' ,'web/financeiro/contabilidade/msc/lrf/anexo/rreo/4/1' ,'1' ,'1' ,'Anexo IV - Dem.das Rec e Desp do RPPS' ,'true' ,'false' );

insert into db_menu(id_item, id_item_filho, menusequencia, modulo) values (229318, 229342, 4, 209);
SQL
        );
    }
}

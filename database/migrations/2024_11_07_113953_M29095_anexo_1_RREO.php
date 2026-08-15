<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class M29095Anexo1RREO extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::unprepared(<<<SQL
insert into orcparamrel (o42_codparrel, o42_descrrel, o42_orcparamrelgrupo, o42_notapadrao)
values (280, 'ANEXO I - ED 14 - BALANÇO ORÇAMENTÁRIO', 1, 'Fonte: Sistema E-Cidade, Unidade Responsável [nome_departamento], Data de emissão [data_emissao] e hora de emissão [hora_emissao]');

insert into orcparamrelperiodos (o113_sequencial, o113_periodo, o113_orcparamrel)
values (nextval('orcparamrelperiodos_o113_sequencial_seq'), 11, 280),
       (nextval('orcparamrelperiodos_o113_sequencial_seq'), 10, 280),
       (nextval('orcparamrelperiodos_o113_sequencial_seq'), 9, 280),
       (nextval('orcparamrelperiodos_o113_sequencial_seq'), 8, 280),
       (nextval('orcparamrelperiodos_o113_sequencial_seq'), 7, 280),
       (nextval('orcparamrelperiodos_o113_sequencial_seq'), 6, 280);
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
delete from orcparamrelperiodos where o113_orcparamrel = 280;
delete from orcparamrelnota where o42_codparrel = 280;
delete from lrfvalormanual where c180_relatorio = 280;
delete from orcparamrel where o42_codparrel = 280;
SQL
        );
    }
}

<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class M29941AdcFuncSistema extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::unprepared(<<<SQL

            insert into db_sysfuncoes( codfuncao ,nomefuncao ,nomearquivo ,obsfuncao ,corpofuncao ,triggerfuncao ) values ( 247 ,'fc_iptu_taxa_de_coleta_e_remocao_lixo_valenca' ,'fc_iptu_taxa_de_coleta_e_remocao_lixo_valenca' ,'Funчуo para Taxa de Serviчo de Coleta e de Remoчуo de Lixo' ,'a' ,'0' );
            insert into db_sysfuncoesparam( db42_sysfuncoesparam ,db42_funcao ,db42_ordem ,db42_nome ,db42_tipo ,db42_tamanho ,db42_precisao ,db42_valor_default ,db42_descricao ) values ( 1272 ,247 ,1 ,'iReceita' ,'int4' ,0 ,0 ,'0' ,'RECEITA' );
            insert into db_sysfuncoesparam( db42_sysfuncoesparam ,db42_funcao ,db42_ordem ,db42_nome ,db42_tipo ,db42_tamanho ,db42_precisao ,db42_valor_default ,db42_descricao ) values ( 1273 ,247 ,2 ,'nAliquota' ,'numeric' ,0 ,0 ,'0' ,'ALIQUOTA' );
            insert into db_sysfuncoesparam( db42_sysfuncoesparam ,db42_funcao ,db42_ordem ,db42_nome ,db42_tipo ,db42_tamanho ,db42_precisao ,db42_valor_default ,db42_descricao ) values ( 1274 ,247 ,3 ,'iHistcalc' ,'int4' ,0 ,0 ,'0' ,'HISTORICO DE CALCULO' );
            insert into db_sysfuncoesparam( db42_sysfuncoesparam ,db42_funcao ,db42_ordem ,db42_nome ,db42_tipo ,db42_tamanho ,db42_precisao ,db42_valor_default ,db42_descricao ) values ( 1275 ,247 ,4 ,'nPercIsen' ,'numeric' ,0 ,0 ,'0' ,'PERCENTUAL' );
            insert into db_sysfuncoesparam( db42_sysfuncoesparam ,db42_funcao ,db42_ordem ,db42_nome ,db42_tipo ,db42_tamanho ,db42_precisao ,db42_valor_default ,db42_descricao ) values ( 1276 ,247 ,5 ,'nValpar' ,'numeric' ,0 ,0 ,'0' ,'VALOR' );



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

            DELETE FROM db_sysfuncoesparam WHERE db42_sysfuncoesparam IN (1272, 1273, 1274, 1275, 1276) and db42_funcao = 247;
            DELETE FROM db_sysfuncoes WHERE codfuncao = 247;


SQL

        );
    }
}

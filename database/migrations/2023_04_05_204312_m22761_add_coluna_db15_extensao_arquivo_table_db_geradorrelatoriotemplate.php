<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class M22761AddColunaDb15ExtensaoArquivoTableDbGeradorrelatoriotemplate extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('db_geradorrelatoriotemplate', function (Blueprint $table) {
            $table->string('db15_extensao_arquivo')
                ->nullable()
                ->after('db15_documento');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('db_geradorrelatoriotemplate', function (Blueprint $table) {
           $table->dropColumn('db15_extensao_arquivo');
        });
    }
}

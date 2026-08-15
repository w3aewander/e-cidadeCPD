<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class M25423TabelaProcessoSolicitacaoAssinatura extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create("protocolo.documento_solicitacao_assinaturas", function (Blueprint $table) {
            $table->bigIncrements("id");
            $table->bigInteger("documento_id", false, true);
            $table->dateTime("data_assinatura")->nullable();
            $table->bigInteger("cgm_assinante", false, true);
            $table->bigInteger("cgm_solicitante", false, true);
            $table->timestamps();
            $table->foreign("documento_id")->references('p01_sequencial')->on('protocolo.protprocessodocumento');
            $table->foreign("cgm_assinante")->references('z01_numcgm')->on('protocolo.cgm');
            $table->foreign("cgm_solicitante")->references('z01_numcgm')->on('protocolo.cgm');
        });
        DB::statement('select public.fc_set_pg_search_path();');
        DB::statement("SELECT configuracoes.fc_auditoria_cria_funcao('protocolo.documento_solicitacao_assinaturas');");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop("protocolo.documento_solicitacao_assinaturas");
    }
}

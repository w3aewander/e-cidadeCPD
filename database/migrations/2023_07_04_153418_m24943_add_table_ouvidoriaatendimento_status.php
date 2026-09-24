<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class M24943AddTableOuvidoriaatendimentoStatus extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->upCriarTableStatus();
        $this->upAddColumnStatus();
        $this->upAtualizaStatusAtendimentos();
    }

    public function upCriarTableStatus() {
        Schema::create('ouvidoria.ouvidoriaatendimento_status', function (Blueprint $table) {
            $table->increments('ov35_id');
            $table->string('ov35_descricao', 100);
        });

        DB::table('ouvidoria.ouvidoriaatendimento_status')->insert([
            ['ov35_descricao' => 'Aguardando atendimento'],
            ['ov35_descricao' => 'Aprovado'],
            ['ov35_descricao' => 'Rejeitado']
        ]);

        DB::statement('select public.fc_set_pg_search_path();');
        DB::statement("SELECT configuracoes.fc_auditoria_cria_funcao('ouvidoria.ouvidoriaatendimento_status');");
        $this->upAddTableOuvidoriaatendimento_statusDicionarioDados();
    }

    public function upAddTableOuvidoriaatendimento_statusDicionarioDados() {
        DB::connection()->getPdo()->exec(<<<SQL
COMMENT ON TABLE ouvidoria.ouvidoriaatendimento_status IS
  '{
      "descricao": "Tabela para gerenciar o status dos atendimentos",
      "sigla": "ov35",
      "dataincl": "2023-08-09",
      "rotulo": "Tabela para gerenciar o status dos atendimentos",
      "tipotabela": 0,
      "naolibclass": false,
      "naolibfunc": false,
      "naolibprog": false,
      "naolibform": false
   }';

SQL
        );
    }

    public function upAddColumnStatus() {
        Schema::table('ouvidoria.ouvidoriaatendimento', function (Blueprint $table) {
            $table->bigInteger('ov01_status')
                ->default(1)
                ->after('ov01_executado');
            $table->foreign('ov01_status')->references('ov35_id')->on('ouvidoria.ouvidoriaatendimento_status');
        });
        $this->upAddCampoStatusDicionarioDados();
    }

    public function upAddCampoStatusDicionarioDados() {
        DB::connection()->getPdo()->exec(<<<SQL
COMMENT ON COLUMN ouvidoria.ouvidoriaatendimento.ov01_status IS
  '{
      "descricao": "Status do Atendimento",
      "rotulo": "Status do Atendimento	",
      "rotulorel": "Status do Atendimento	",
      "maiusculo": false,
      "autocompl": false,
      "aceitatipo": 1,
      "tamanho": 1,
      "tipoobj": "text"
   }';
SQL
        );
    }

    public function upAtualizaStatusAtendimentos() {
        DB::connection()->getPdo()->exec(<<<SQL
WITH MASSA_DE_DADOS AS (
SELECT
    ouvidoria.ouvidoriaatendimento.ov01_sequencial AS atendimento_id,
    protocolo.protprocesso.p58_codproc AS processo_id,
    protocolo.protprocesso.p58_obs,
    protocolo.procandam.p61_codandam,
    protocolo.procandam.p61_despacho AS despacho,
    TRANSLATE (protocolo.procandam.p61_despacho,
    '0123456789',
    '') AS status
FROM
    ouvidoria.ouvidoriaatendimento
INNER JOIN ouvidoria.processoouvidoria ON
    ouvidoria.processoouvidoria.ov09_ouvidoriaatendimento = ouvidoria.ouvidoriaatendimento.ov01_sequencial
INNER JOIN protocolo.protprocesso ON
    protocolo.protprocesso.p58_codproc = ouvidoria.processoouvidoria.ov09_protprocesso
INNER JOIN protocolo.procandam ON
    protocolo.procandam.p61_codproc = protocolo.protprocesso.p58_codproc
WHERE
    p61_despacho SIMILAR TO '%Processo *[0-9]* criado%'
    ),
    DADOS_UPDATE AS (
SELECT
    atendimento_id,
    CASE
        WHEN status = 'Processo  criado' THEN
        2
        WHEN status ILIKE '%Processo  criado e arquivado%'
            AND ( p58_obs ILIKE '%Primeiro acesso arquivado pelo sistema%'
                OR p58_obs ILIKE '%Recadastramento acesso arquivado pelo sistema%')
 THEN
  2
            ELSE
  3
        END AS status_atendimento
    FROM
        MASSA_DE_DADOS
   )
  UPDATE
    ouvidoria.ouvidoriaatendimento
SET
    ov01_status = DADOS_UPDATE.status_atendimento
FROM
    DADOS_UPDATE
WHERE
    ov01_sequencial = DADOS_UPDATE.atendimento_id;
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
        $this->downDeleteColumStatus();
        $this->downDeleteTableStatus();
    }

    public function downDeleteColumStatus() {
        Schema::table('ouvidoria.ouvidoriaatendimento', function (Blueprint $table) {
            $table->dropForeign(['ov01_status']);
            $table->dropColumn('ov01_status');
        });
    }

    public function downDeleteTableStatus() {
        DB::statement("SELECT configuracoes.fc_auditoria_remove_funcao('ouvidoria.ouvidoriaatendimento_status');");
        Schema::dropIfExists('ouvidoria.ouvidoriaatendimento_status');
    }
}

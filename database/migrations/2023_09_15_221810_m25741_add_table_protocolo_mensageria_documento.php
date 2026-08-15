<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class M25741AddTableProtocoloMensageriaDocumento extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->upTableMensageriaDocumento();
        $this->upForeignKey();
        $this->upAuditoria();
        $this->upDicionarioDeDados();
        $this->upImportarDocumentosMensagensAntigas();
    }

    public function upTableMensageriaDocumento()
    {
        Schema::create('protocolo.mensageria_documento', function (Blueprint $table){
            $table->increments('p124_id');
            $table->bigInteger('p124_codigo_storage');
            $table->string('p124_nome_documento');
            $table->bigInteger('p124_mensagem');
        });
    }

    public function upDicionarioDeDados()
    {
        DB::connection()->getPdo()->exec(<<<SQL
            ALTER EVENT TRIGGER evtg_dicionario_gatilho_ddl ENABLE;
            ALTER EVENT TRIGGER evtg_dicionario_gatilho_ddl_drop ENABLE;

            COMMENT ON TABLE protocolo.mensageria_documento IS
              '{
                  "descricao": "Tabela para os documentos do sistema de mensageria",
                  "sigla": "p124",
                  "dataincl": "2023-10-25",
                  "rotulo": "Tabela para os documentos do sistema de mensageria",
                  "tipotabela": 0,
                  "naolibclass": false,
                  "naolibfunc": false,
                  "naolibprog": false,
                  "naolibform": false
               }';

            COMMENT ON COLUMN protocolo.mensageria_documento.p124_id IS '
                        {
                            "descricao": "Chave primária da tabela",
                            "rotulo": "p124_id",
                            "rotulorel": "p124_id",
                            "maiusculo": false,
                            "autocompl": false,
                            "aceitatipo": 1,
                            "tamanho": 10,
                            "tipoobj": "text"
                        }';

            COMMENT ON COLUMN protocolo.mensageria_documento.p124_codigo_storage IS '
                        {
                            "descricao": "Id do arquivo no e-storage",
                            "rotulo": "p124_codigo_storage",
                            "rotulorel": "p124_codigo_storage",
                            "maiusculo": false,
                            "autocompl": false,
                            "aceitatipo": 1,
                            "tamanho": 10,
                            "tipoobj": "text"
                        }';

            COMMENT ON COLUMN protocolo.mensageria_documento.p124_nome_documento IS '
                        {
                            "descricao": "Id do arquivo no e-storage",
                            "rotulo": "p124_nome_documento",
                            "rotulorel": "p124_nome_documento",
                            "maiusculo": false,
                            "autocompl": false,
                            "aceitatipo": 3,
                            "tamanho": 50,
                            "tipoobj": "text"
                        }';


            COMMENT ON COLUMN protocolo.mensageria_documento.p124_mensagem IS '
                        {
                            "descricao": "Id da mensagem há qual o arquivo pertence",
                            "rotulo": "p124_mensagem",
                            "rotulorel": "p124_mensagem",
                            "maiusculo": false,
                            "autocompl": false,
                            "aceitatipo": 1,
                            "tamanho": 10,
                            "tipoobj": "text"
                        }';

            SELECT fc_gera_dicionario_apartir_tabela('protocolo', 'mensageria_documento');

            ALTER EVENT TRIGGER evtg_dicionario_gatilho_ddl DISABLE;
            ALTER EVENT TRIGGER evtg_dicionario_gatilho_ddl_drop DISABLE;
SQL
        );
    }

    public function upImportarDocumentosMensagensAntigas()
    {
        DB::connection()->getPdo()->exec(<<<SQL
        INSERT INTO protocolo.mensageria_documento (p124_codigo_storage, p124_nome_documento, p124_mensagem)
        SELECT
        protocolo.protprocessodocumento.p01_documento AS id_storage,
        protocolo.protprocessodocumento.p01_nomedocumento AS nome,
        protocolo.protprocessodocumento.p01_procandamint AS codigo_mensagem
        FROM protocolo.procandamint
        INNER JOIN protocolo.protprocessodocumento ON protocolo.protprocessodocumento.p01_procandamint = protocolo.procandamint.p78_sequencial
        WHERE protocolo.procandamint.p78_tipodespacho IN (1000, 1001, 1002, 1003);
SQL
        );
    }

    public function upForeignKey()
    {
        Schema::table('protocolo.mensageria_documento', function (Blueprint $table) {
            $table->foreign('p124_mensagem')
                ->references('p123_id')
                ->on('protocolo.mensageria');
        });
    }

    public function upAuditoria()
    {
        DB::statement('select public.fc_set_pg_search_path();');
        DB::statement("SELECT configuracoes.fc_auditoria_cria_funcao('protocolo.mensageria_documento');");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->downAuditoria();
        $this->downDicionario();
        $this->downForeignKey();
        $this->downTableMensageriaDocumento();
    }

    public function downAuditoria()
    {
        DB::statement("SELECT configuracoes.fc_auditoria_remove_funcao('protocolo.mensageria_documento');");
    }

    public function downDicionario()
    {
        DB::connection()->getPdo()->exec(<<<SQL

            SELECT fc_remove_dicionario_tabela('protocolo', 'mensageria_documento');
SQL
        );
    }

    public function downForeignKey()
    {
        Schema::table('protocolo.mensageria_documento', function (Blueprint $table) {
            $table->dropForeign(['p124_mensagem']);
        });
    }

    public function downTableMensageriaDocumento()
    {
        Schema::dropIfExists('protocolo.mensageria_documento');
    }
}

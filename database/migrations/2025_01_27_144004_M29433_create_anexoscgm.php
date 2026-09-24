<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class M29433CreateAnexoscgm extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        DB::unprepared(<<<SQL
            CREATE TABLE protocolo.anexoscgm (
                z34_sequencial SERIAL NOT NULL,
                z34_cgm integer,
                z34_usuario integer,
                z34_arquivo varchar(300),
                z34_idstorage integer,
                z34_descricao varchar(300),
                z34_observacao text,
                z34_data timestamp,
                z34_usuarioexclusao integer,
                z34_dataexclusao timestamp,
                
                PRIMARY KEY (z34_sequencial),
                FOREIGN KEY (z34_cgm) REFERENCES protocolo.cgm (z01_numcgm),
                FOREIGN KEY (z34_usuario) REFERENCES configuracoes.db_usuarios(id_usuario),
                FOREIGN KEY (z34_usuarioexclusao) REFERENCES configuracoes.db_usuarios(id_usuario)
            );
SQL
        );
        DB::unprepared(<<<SQL

            select configuracoes.fc_auditoria_cria_funcao('protocolo.anexoscgm');

            ALTER EVENT TRIGGER evtg_dicionario_gatilho_ddl ENABLE;
            ALTER EVENT TRIGGER evtg_dicionario_gatilho_ddl_drop ENABLE;

            SELECT fc_gera_dicionario_apartir_tabela('protocolo', 'anexoscgm');

            COMMENT ON TABLE protocolo.anexoscgm IS
            '{
                "descricao": "Anexos CGM",
                "sigla": "z34",
                "dataincl": "2024-11-28",
                "rotulo": "Anexos CGM",
                "tipotabela": 2,
                "naolibclass": false,
                "naolibfunc": false,
                "naolibprog": false,
                "naolibform": false
            }';

            COMMENT ON COLUMN protocolo.anexoscgm.z34_sequencial IS
            '{
                "descricao": "Sequencial da tabela",
                "rotulo": "Sequencial Anexo CGM",
                "rotulorel": "Sequencial Anexo CGM",
                "maiusculo": false,
                "autocompl": false,
                "aceitatipo": 1,
                "tamanho": 10,
                "tipoobj": "text"
            }';



            COMMENT ON COLUMN protocolo.anexoscgm.z34_arquivo IS 
            '{
              "descricao": "Arquivo Importado",
              "rotulo": "Arquivo Importado",
              "rotulorel": "Arquivo Importado",
              "maiusculo": false,
              "autocompl": false,
              "aceitatipo": 0,
              "tamanho": 300,
              "tipoobj": "text"
            }';

            COMMENT ON COLUMN protocolo.anexoscgm.z34_idstorage IS
            '{
                "descricao": "ID Storage",
                "rotulo": "ID Storage",
                "rotulorel": "ID Storage",
                "maiusculo": false,
                "autocompl": false,
                "aceitatipo": 1,
                "tamanho": 10,
                "tipoobj": "text"
            }';

            COMMENT ON COLUMN protocolo.anexoscgm.z34_descricao IS 
            '{
              "descricao": "Descriчуo do Arquivo",
              "rotulo": "Descriчуo do Arquivo",
              "rotulorel": "Descriчуo do Arquivo",
              "maiusculo": false,
              "autocompl": false,
              "aceitatipo": 0,
              "tamanho": 300,
              "tipoobj": "text"
            }';

            COMMENT ON COLUMN protocolo.anexoscgm.z34_observacao IS 
            '{
              "descricao": "Observaчуo Arquivo",
              "rotulo": "Observaчуo Arquivo",
              "rotulorel": "Observaчуo Arquivo",
              "maiusculo": false,
              "autocompl": false,
              "aceitatipo": 0,
              "tamanho": 300,
              "tipoobj": "text"
            }';

            COMMENT ON COLUMN protocolo.anexoscgm.z34_data IS 
            '{
              "descricao": "Data Upload",
              "rotulo": "Data Upload",
              "rotulorel": "Data Upload",
              "maiusculo": false,
              "autocompl": false,
              "aceitatipo": 0,
              "tamanho": 10,
              "tipoobj": "text"
            }';

            COMMENT ON COLUMN protocolo.anexoscgm.z34_usuario IS
            '{
                "descricao": "Usuсrio",
                "rotulo": "Usuсrio upload",
                "rotulorel": "Usuсrio upload",
                "maiusculo": false,
                "autocompl": false,
                "aceitatipo": 1,
                "tamanho": 10,
                "tipoobj": "text"
            }';

            COMMENT ON COLUMN protocolo.anexoscgm.z34_cgm IS
            '{
                "descricao": "CGM",
                "rotulo": "CGM",
                "rotulorel": "CGM",
                "maiusculo": false,
                "autocompl": false,
                "aceitatipo": 1,
                "tamanho": 10,
                "tipoobj": "text"
            }';

            COMMENT ON COLUMN protocolo.anexoscgm.z34_dataexclusao IS 
            '{
              "descricao": "Data Exclusуo",
              "rotulo": "Data Exclusуo",
              "rotulorel": "Data Exclusуo",
              "maiusculo": false,
              "autocompl": false,
              "aceitatipo": 0,
              "tamanho": 10,
              "tipoobj": "text"
            }';

            COMMENT ON COLUMN protocolo.anexoscgm.z34_usuarioexclusao IS
            '{
                "descricao": "Usuсrio Exclusуo",
                "rotulo": "Usuсrio Exclusуo upload",
                "rotulorel": "Usuсrio Exclusуo upload",
                "maiusculo": false,
                "autocompl": false,
                "aceitatipo": 1,
                "tamanho": 10,
                "tipoobj": "text"
            }';

            ALTER EVENT TRIGGER evtg_dicionario_gatilho_ddl DISABLE;
            ALTER EVENT TRIGGER evtg_dicionario_gatilho_ddl_drop DISABLE;
            
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
        select configuracoes.fc_auditoria_remove_funcao('protocolo.anexoscgm');
SQL
    );
        Schema::dropIfExists('protocolo.anexoscgm');
    }
}

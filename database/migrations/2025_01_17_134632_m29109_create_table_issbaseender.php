<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class M29109CreateTableissbaseender extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::unprepared(<<<SQL
            CREATE TABLE IF NOT EXISTS issqn.issbaseendereco(
                q205_codigo SERIAL PRIMARY KEY,
                q205_inscr INTEGER NOT NULL,
                q205_cep VARCHAR(8) NOT NULL,
                q205_bairro INTEGER,
                q205_rua INTEGER,
                q205_bairronome VARCHAR(30),
                q205_ruanome VARCHAR(100),
                q205_num VARCHAR(10) NOT NULL,
                q205_compl VARCHAR(100),
                q205_dest VARCHAR(50),
                q205_municipal BOOLEAN DEFAULT false NOT NULL,
                q205_atualizado TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                q205_usuario INTEGER NOT NULL,
                FOREIGN KEY(q205_inscr) REFERENCES issqn.issbase(q02_inscr) ON DELETE CASCADE,
                FOREIGN KEY(q205_bairro) REFERENCES cadastro.bairro(j13_codi),
                FOREIGN KEY(q205_rua) REFERENCES cadastro.ruas(j14_codigo),
                FOREIGN KEY(q205_usuario) REFERENCES configuracoes.db_usuarios(id_usuario)
            );

            CREATE INDEX IF NOT EXISTS in_issbaseendereco_inscr
            ON issqn.issbaseendereco(q205_inscr);

            CREATE INDEX IF NOT EXISTS in_issbaseendereco_bairro
            ON issqn.issbaseendereco(q205_bairro);

            CREATE INDEX IF NOT EXISTS in_issbaseendereco_rua
            ON issqn.issbaseendereco(q205_rua);

            CREATE INDEX IF NOT EXISTS in_issbaseendereco_usuario
            ON issqn.issbaseendereco(q205_usuario);

            SELECT configuracoes.fc_auditoria_cria_funcao('issqn.issbaseendereco');

            ALTER EVENT TRIGGER evtg_dicionario_gatilho_ddl ENABLE;
            ALTER EVENT TRIGGER evtg_dicionario_gatilho_ddl_drop ENABLE;

            COMMENT ON TABLE issqn.issbaseendereco IS
            '{
                "descricao": "Endereco de Entrega da Inscricao",
                "sigla": "q205",
                "dataincl": "2025-01-17",
                "rotulo": "issbaseendereco",
                "tipotabela": 0,
                "naolibclass": false,
                "naolibfunc": false,
                "naolibprog": false,
                "naolibform": false
            }';
    
            COMMENT ON COLUMN issqn.issbaseendereco.q205_inscr IS
            '{
                "descricao": "Inscricao Municipal",
                "rotulo": "Inscricao",
                "rotulorel": "Inscricao",
                "maiusculo": false,
                "autocompl": false,
                "aceitatipo": 1,
                "tamanho": 5,
                "tipoobj": "text"
            }';
    
            COMMENT ON COLUMN issqn.issbaseendereco.q205_cep IS
            '{
                "descricao": "Cep",
                "rotulo": "Cep",
                "rotulorel": "Cep",
                "maiusculo": false,
                "autocompl": false,
                "aceitatipo": 1,
                "tamanho": 5,
                "tipoobj": "text"
            }';
    
            COMMENT ON COLUMN issqn.issbaseendereco.q205_bairronome IS 
            '{
                "descricao": "Nome do Bairro",
                "rotulo": "Bairro",
                "rotulorel": "Bairro",
                "maiusculo": false,
                "autocompl": false,
                "aceitatipo": 3,
                "tamanho": 5,
                "tipoobj": "text"
            }';

            COMMENT ON COLUMN issqn.issbaseendereco.q205_bairro IS 
            '{
                "descricao": "Codigo do Bairro",
                "rotulo": "Bairro",
                "rotulorel": "Bairro",
                "maiusculo": false,
                "autocompl": false,
                "aceitatipo": 1,
                "tamanho": 5,
                "tipoobj": "text"
            }';

            COMMENT ON COLUMN issqn.issbaseendereco.q205_ruanome IS 
            '{
                "descricao": "Nome da rua",
                "rotulo": "Rua",
                "rotulorel": "Rua",
                "maiusculo": false,
                "autocompl": false,
                "aceitatipo": 3,
                "tamanho": 5,
                "tipoobj": "text"
            }';
    
            COMMENT ON COLUMN issqn.issbaseendereco.q205_rua IS 
            '{
                "descricao": "Codigo da rua",
                "rotulo": "Rua",
                "rotulorel": "Rua",
                "maiusculo": false,
                "autocompl": false,
                "aceitatipo": 1,
                "tamanho": 5,
                "tipoobj": "text"
            }';
    
            COMMENT ON COLUMN issqn.issbaseendereco.q205_num IS 
            '{
                "descricao": "Numero do endereco",
                "rotulo": "Numero",
                "rotulorel": "Numero",
                "maiusculo": false,
                "autocompl": false,
                "aceitatipo": 3,
                "tamanho": 5,
                "tipoobj": "text"
            }';
    
            COMMENT ON COLUMN issqn.issbaseendereco.q205_compl IS 
            '{
                "descricao": "Complemento do endereco",
                "rotulo": "Complemento",
                "rotulorel": "Complemento",
                "maiusculo": false,
                "autocompl": false,
                "aceitatipo": 3,
                "tamanho": 10,
                "tipoobj": "text"
            }';
    
            COMMENT ON COLUMN issqn.issbaseendereco.q205_dest IS 
            '{
                "descricao": "Nome do Destinatario",
                "rotulo": "Destinatario",
                "rotulorel": "Destinatario",
                "maiusculo": false,
                "autocompl": false,
                "aceitatipo": 2,
                "tamanho": 5,
                "tipoobj": "text"
            }';

            COMMENT ON COLUMN issqn.issbaseendereco.q205_atualizado IS 
            '{
                "descricao": "Data e hora da ultima atualizacao",
                "rotulo": "Data e Hora",
                "rotulorel": "Data e Hora",
                "maiusculo": false,
                "autocompl": false,
                "aceitatipo": 3,
                "tamanho": 10,
                "tipoobj": "text"
            }';

            COMMENT ON COLUMN issqn.issbaseendereco.q205_usuario IS 
            '{
                "descricao": "Usuário que atualizou o endereco",
                "rotulo": "Usuario",
                "rotulorel": "Usuario",
                "maiusculo": false,
                "autocompl": false,
                "aceitatipo": 2,
                "tamanho": 5,
                "tipoobj": "text"
            }';

            SELECT fc_gera_dicionario_apartir_tabela('issqn', 'issbaseendereco');

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
        DB::unprepared("DROP TABLE IF EXISTS issqn.issbaseendereco;");
    }
}
